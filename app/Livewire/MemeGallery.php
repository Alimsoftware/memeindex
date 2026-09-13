<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Meme;
use App\Models\Vote;
use App\Models\User;
use App\Models\Report;
use App\Models\Favorite;
use Livewire\Component;
use Livewire\WithPagination;

class MemeGallery extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter = 'latest';
    public string $newComment = '';

    public bool $showModal = false;
    public ?Meme $selectedMeme = null;

    public bool $showReportModal = false;
    public ?int $reportingMemeId = null;
    public string $reportReason = 'inappropriate';
    public string $reportDescription = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }
    public function setFilter(string $filter): void // <- Adicione este método
    {
        $this->filter = $filter;
        $this->resetPage();
    }

    public function openModal(?int $memeId = null): void
    {
        if (!$memeId) {
            return;
        }

        $this->selectedMeme = Meme::with([
            'user',
            'comments' => fn($query) => $query->whereNull('parent_id')->with(['user', 'children.user'])->latest(),
        ])->find($memeId);

        if ($this->selectedMeme) {
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedMeme = null;
        $this->newComment = '';
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
        $meme = Meme::find($memeId);

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

    public function addComment(?int $parentId = null): void
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

        Comment::create([
            'user_id' => auth()->id(),
            'meme_id' => $this->selectedMeme->id,
            'parent_id' => $parentId,
            'content' => trim($this->newComment),
            'likes' => 0,
        ]);

        $this->newComment = '';

        // Recarrega o modal mantendo a estrutura de parent/children
        $this->openModal($this->selectedMeme->id);
    }

    public function toggleFavorite(int $memeId): void
    {
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para favoritar.');
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
    if (!auth()->check() || !$this->reportingMemeId) {
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
            ->withExists(['favorites as is_favorited' => fn($q) => $q->where('user_id', $userId)])
            ->when($this->search !== '', fn($q) => $q->where('title', 'like', "%{$this->search}%"));

        match ($this->filter) {
            'popular' => $query->withCount(['votes as upvotes_count' => fn($q) => $q->where('vote_type', 'upvote')])->orderByDesc('upvotes_count'),
            'favorites' => $query->whereHas('favorites', fn($q) => $q->where('user_id', $userId)),
            default => $query->latest(),
        };

        return view('livewire.meme-gallery', [
            'memes' => $query->paginate(12),
        ]);
    }
}