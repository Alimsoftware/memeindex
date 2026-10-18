<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Meme;
use App\Models\Vote;
use App\Models\User;
use App\Models\Report;
use App\Models\Favorite;
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

    public bool $showModal = false;
    public ?Meme $selectedMeme = null;

    public ?int $replyingTo = null;

    public bool $showReportModal = false;
    public ?int $reportingMemeId = null;
    public string $reportReason = 'inappropriate';
    public string $reportDescription = '';

    public string $category = '';
    public string $format = ''; // '', 'gif' ou 'image'

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
        $this->selectedMeme = Meme::with('user')->where('status', 'approved')->find($memeId);

        if ($this->selectedMeme) {
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedMeme = null;
        $this->newComment = '';
        $this->cancelReply();
    }

    public function vote(int $memeId, string $type): void
    {
        if (!in_array($type, ['upvote', 'downvote'])) {
            return;
        }

        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para votar.');
            return;
        }

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

        if ($this->selectedMeme && $this->selectedMeme->id === $memeId) {
            $this->selectedMeme->refresh();
        }
    }

    public function addComment(): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para comentar.');
            return;
        }

        $this->validate([
            'newComment' => 'required|string|max:500',
        ]);

        if (!$this->selectedMeme) {
            return;
        }

        // O id do pai vem do cliente: só vale se for deste meme.
        $parentId = $this->replyingTo
            ? Comment::where('meme_id', $this->selectedMeme->id)->whereKey($this->replyingTo)->value('id')
            : null;

        Comment::create([
            'user_id' => auth()->id(),
            'meme_id' => $this->selectedMeme->id,
            'parent_id' => $parentId,
            'content' => trim($this->newComment),
            'likes' => 0,
        ]);

        $this->newComment = '';
        $this->replyingTo = null;
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

        $deleted = Comment::where('id', $commentId)
            ->where('meme_id', $this->selectedMeme->id)
            ->where('user_id', auth()->id())
            ->delete();

        if ($deleted) {
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
            $this->selectedMeme->refresh();
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
        'reportReason' => 'required|in:inappropriate,duplicated,offensive,spam,copyright,other',
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

    Report::create([
        'user_id' => auth()->id(),
        'meme_id' => $this->reportingMemeId,
        'reason' => $this->reportReason,
        'comment' => trim($this->reportDescription), // Mapeado para a coluna 'comment'
        'status' => 'pending',
    ]);

    $this->dispatch('notify', message: 'Denúncia enviada com sucesso para a moderação.');
    $this->closeReportModal();
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
            ->withExists(['favorites as is_favorited' => fn($q) => $q->where('user_id', $userId)])
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

        $messages = $this->showModal && $this->selectedMeme
            ? Comment::with(['user', 'parent.user'])->where('meme_id', $this->selectedMeme->id)->oldest()->oldest('id')->get()
            : collect();

        return view('livewire.meme-gallery', [
            'messages' => $messages,
            'categories' => Meme::where('status', 'approved')->distinct()->orderBy('category')->pluck('category'),
            'memes' => $query->paginate(12),
        ]);
    }
}