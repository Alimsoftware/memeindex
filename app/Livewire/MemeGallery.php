<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Meme;
use App\Models\Vote;
use App\Models\Report;
use App\Models\Favorite;
use App\Models\Share;
use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class MemeGallery extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';
    public string $filter = 'latest';
    public string $newComment = '';

    // link compartilhável: /?meme=slug abre o modal desse meme
    #[Url(as: 'meme', except: '')]
    public string $memeSlug = '';

    public bool $showModal = false;
    public ?Meme $selectedMeme = null;

    public ?int $replyingTo = null;
    public int $chatLimit = 100;

    public bool $showReportModal = false;
    public ?int $reportingMemeId = null;
    public string $reportReason = 'inappropriate';
    public string $reportDescription = '';

    public string $category = '';
    public string $format = ''; // '', 'gif' ou 'image'

    public function mount(): void
    {
        if ($this->memeSlug === '') {
            return;
        }

        $id = Meme::where('status', 'approved')->where('slug', $this->memeSlug)->value('id');
        $id ? $this->openModal($id) : $this->memeSlug = '';
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // A busca fica no header (fora deste componente) e avisa por evento.
    #[On('search-changed')]
    public function setSearch(string $value = ''): void
    {
        $this->search = mb_substr(trim($value), 0, 100);
        $this->resetPage();
    }

    public function setFilter(string $filter): void
    {
        if ($filter === 'favorites' && !auth()->check()) {
            $this->dispatch('notify', message: 'Faça login para ver seus favoritos.');
            return;
        }
        $this->filter = $filter;
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->category = $this->category === $category ? '' : $category;
        $this->resetPage();
    }

    public function setFormat(string $format): void
    {
        $this->format = $this->format === $format ? '' : $format;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'category', 'format', 'filter');
        $this->resetPage();
        $this->dispatch('search-cleared');
    }

    public function openModal(?int $memeId = null): void
    {
        if (!$memeId) {
            return;
        }

        // o id vem do cliente: só abre meme publicado
        $this->selectedMeme = Meme::with('user')
            ->withCount([
                'votes as upvotes_count' => fn($q) => $q->where('vote_type', 'upvote'),
                'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'downvote'),
                'allComments as comments_count',
            ])
            ->where('status', 'approved')
            ->find($memeId);

        if ($this->selectedMeme) {
            $this->showModal = true;
            $this->chatLimit = 100;
            $this->memeSlug = $this->selectedMeme->slug;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedMeme = null;
        $this->reset('newComment', 'replyingTo', 'chatLimit', 'memeSlug');
    }

    public function loadOlderMessages(): void
    {
        $this->chatLimit += 100;
        unset($this->chatMessages);
    }

    public function vote(int $memeId, string $type): void
    {
        if (!in_array($type, ['up', 'down']))
            return;


        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para votar.');
            return;
        }

        if ($this->throttled('vote', 30, 60)) {
            return;
        }

        $type .= 'vote'; // a coluna guarda 'upvote' / 'downvote'
        $userId = auth()->id();
        $meme = Meme::where('status', 'approved')->find($memeId);

        if (!$meme) {
            return;
        }

        $existingVote = Vote::where('meme_id', $memeId)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            if ($existingVote->vote_type === $type) {
                $existingVote->delete();
            } else {
                $existingVote->update(['vote_type' => $type]);
            }
        } else {
            Vote::create([
                'user_id' => $userId,
                'meme_id' => $memeId,
                'vote_type' => $type,
            ]);
        }

        auth()->user()->recalculateXp();
        if ($meme->user_id !== $userId) {
            $meme->user?->recalculateXp();
        }

        if ($this->selectedMeme && $this->selectedMeme->id === $memeId) {
            $this->dispatch('$refresh');
        }
    }

    public function addComment(): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para comentar.');
            return;
        }

        if ($this->throttled('comment', 10, 60)) {
            return;
        }

        $this->validate([
            'newComment' => 'required|string|max:500',
        ]);

        if (!$this->selectedMeme) {
            return;
        }

        // O id do pai vem do cliente: só vale se for deste meme.
        $parentId = null;
        if ($this->replyingTo) {
            $parent = Comment::where('meme_id', $this->selectedMeme->id)->find($this->replyingTo);
            $parentId = $parent?->id;
        }

        Comment::create([
            'user_id' => auth()->id(),
            'meme_id' => $this->selectedMeme->id,
            'parent_id' => $parentId,
            'content' => trim($this->newComment),
            'likes' => 0,
        ]);
        auth()->user()->recalculateXp();

        $this->newComment = '';
        $this->replyingTo = null;
        unset($this->chatMessages); // recalcula a lista
        $this->dispatch('chat-sent');
    }

    public function startReply(int $commentId): void
    {
        $this->replyingTo = $commentId;
    }

    public function cancelReply(): void
    {
        $this->replyingTo = null;
    }

    public function deleteComment(int $commentId): void
    {
        if (!$this->selectedMeme) {
            return;
        }

        $query = Comment::where('id', $commentId)
            ->where('meme_id', $this->selectedMeme->id);

        if (auth()->user()->role === 'user') {
            $query->where('user_id', auth()->id());
        }

        $authorId = (clone $query)->value('user_id');
        $deleted = $query->delete();

        if ($deleted) {
            User::find($authorId)?->recalculateXp();
            unset($this->chatMessages); // recalcula a lista
            $this->dispatch('notify', message: 'Mensagem apagada.');
            if ($this->replyingTo === $commentId) {
                $this->replyingTo = null;
            }
        }
    }

    public function toggleFavorite(int $memeId): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para favoritar.');
            return;
        }

        // mesmo balde dos votos
        if ($this->throttled('vote', 30, 60)) {
            return;
        }

        if (!Meme::where('status', 'approved')->whereKey($memeId)->exists()) {
            return;
        }

        $userId = auth()->id();

        $favorite = Favorite::where('meme_id', $memeId)
            ->where('user_id', $userId)
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            Favorite::create([
                'user_id' => $userId,
                'meme_id' => $memeId,
            ]);
        }

        if ($this->selectedMeme && $this->selectedMeme->id === $memeId) {
            $this->dispatch('$refresh');
        }
    }

    // chamado ao copiar link ou compartilhar; visitante compartilha sem ganhar XP
    public function shareMeme(int $memeId): void
    {
        if (!auth()->check() || !Meme::where('status', 'approved')->whereKey($memeId)->exists()) {
            return;
        }

        $created = Share::insertOrIgnore([
            'user_id' => auth()->id(),
            'meme_id' => $memeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($created) {
            auth()->user()->recalculateXp();
        }
    }

    // limite por usuário e ação; avisa e devolve true quando estourou
    private function throttled(string $action, int $max, int $seconds): bool
    {
        $key = "{$action}:" . auth()->id();

        if (RateLimiter::tooManyAttempts($key, $max)) {
            $this->dispatch('notify', message: 'Calma! Espere ' . RateLimiter::availableIn($key) . 's para tentar de novo.');
            return true;
        }

        RateLimiter::hit($key, $seconds);
        return false;
    }

    // um por sessão e meme: baixar de novo não infla o número
    public function countDownload(int $memeId): void
    {
        if (in_array($memeId, session('downloaded', []), true)) {
            return;
        }

        // toBase: não mexe no updated_at do meme
        $counted = Meme::where('status', 'approved')->whereKey($memeId)->toBase()->increment('downloads');

        if ($counted) {
            session()->push('downloaded', $memeId);
        }
    }

    public function openReportModal(int $memeId): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para reportar.');
            return;
        }

        $this->reportingMemeId = $memeId;
        $this->reportReason = 'inappropriate';
        $this->reportDescription = '';
        $this->showReportModal = true;
    }

    public function closeReportModal(): void
    {
        $this->showReportModal = false;
        $this->reportingMemeId = null;
    }

    public function submitReport(): void
    {
        if (!auth()->check() || !Meme::where('status', 'approved')->whereKey($this->reportingMemeId)->exists()) {
            return;
        }

        $this->validate([
            'reportReason' => 'required|in:' . implode(',', Report::REASONS),
            'reportDescription' => 'nullable|string|max:500',
        ]);

        $existingReport = Report::where('user_id', auth()->id())
            ->where('meme_id', $this->reportingMemeId)
            ->exists();

        if ($existingReport) {
            $this->dispatch('notify', message: 'Você já reportou este meme anteriormente.');
            $this->closeReportModal();
            return;
        }

        if ($this->throttled('report', 10, 3600)) {
            return;
        }

        try {
            Report::create([
                'user_id' => auth()->id(),
                'meme_id' => $this->reportingMemeId,
                'reason' => $this->reportReason,
                'comment' => trim($this->reportDescription), // Mapeado para a coluna 'comment'
                'status' => 'pending',
            ]);

        } catch (\Illuminate\Database\QueryException $e) {
            // MySQL/MariaDB: 23000 | PostgreSQL: 23505 | SQLite: 23000
            if (in_array($e->getCode(), ['23000', '23505'])) {
                $this->dispatch('notify', message: 'Você já reportou este meme.');
                $this->closeReportModal();
                return;
            }
            throw $e;
        }
        // ✅ Caminho de sucesso (só chega aqui se não houve exceção)
        $this->dispatch('notify', message: 'Denúncia enviada com sucesso para a moderação.');
        $this->closeReportModal();
    }

    #[Computed]
    public function chatMessages()
    {
        if (!$this->selectedMeme) {
            return collect();
        }

        // as N mais recentes, devolvidas em ordem cronológica
        return Comment::with(['user', 'parent.user'])
            ->where('meme_id', $this->selectedMeme->id)
            ->latest()
            ->latest('id')
            ->limit($this->chatLimit)
            ->get()
            ->reverse()
            ->values();
    }

    #[Computed]
    public function chatTotal(): int
    {
        return $this->selectedMeme
            ? Comment::where('meme_id', $this->selectedMeme->id)->count()
            : 0;
    }

    public function render()
    {
        $userId = auth()->id();

        $query = Meme::query()
            ->where('status', 'approved')
            ->with('user')
            ->withCount([
                'allComments as comments_count',
                'votes as upvotes_count' => fn($q) => $q->where('vote_type', 'upvote'),
                'votes as downvotes_count' => fn($q) => $q->where('vote_type', 'downvote'),
            ])
            ->withExists([
                'favorites as is_favorited' => function ($q) use ($userId) {
                    if ($userId) {
                        $q->where('user_id', $userId);
                    } else {
                        $q->whereRaw('1 = 0'); // força false
                    }
                }
            ])
            ->when($this->search !== '', fn($q) => $q->where(
                fn($w) => $w->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%")
            ))
            ->when($this->category !== '', fn($q) => $q->where('category', $this->category))
            ->when($this->format !== '', fn($q) => $q->where('is_gif', $this->format === 'gif'));

        match ($this->filter) {
            'popular' => $query->orderByDesc('upvotes_count'),
            'favorites' => $query->whereHas('favorites', fn($q) => $q->where('user_id', $userId)),
            default => $query->latest(),
        };

        return view('livewire.meme-gallery', [
            'messages' => $this->chatMessages,
            'categories' => collect(Meme::CATEGORIES),
            'memes' => $query->paginate(12),
        ]);
    }
}