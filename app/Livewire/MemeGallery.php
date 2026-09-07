<?php

namespace App\Livewire;

use App\Models\Meme;
use App\Models\Vote;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Storage;



class MemeGallery extends Component
{

    use WithPagination;
    public string $search = '';

    public bool $showModal = false;
    public ?Meme $selectedMeme = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openModal(int $memeId): void
    {
        $this->selectedMeme = Meme::with('user')->find($memeId);
        if ($this->selectedMeme) {
            $this->showModal = true;
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->selectedMeme = null;
    }


    public function vote(int $memeId, string $type): void
    {
        // Restringe os tipos aceitos pela enum da migration
        if (!in_array($type, ['upvote', 'downvote'])) {
            return;
        }

        // Exige usuário autenticado
        if (!auth()->check()) {
            $this->dispatch('notify', message: 'Você precisa estar logado para votar.');
            return;
        }

        $userId = auth()->id();
        $meme = Meme::find($memeId);

        if (!$meme) {
            return;
        }

        // Procura voto existente usando a chave única [user_id, meme_id]
        $existingVote = Vote::where('meme_id', $memeId)
            ->where('user_id', $userId)
            ->first();

        if ($existingVote) {
            if ($existingVote->vote_type === $type) {
                // Remove o voto se clicar na mesma opção
                $existingVote->delete();
            } else {
                // Altera de upvote para downvote ou vice-versa
                $existingVote->update(['vote_type' => $type]);
            }
        } else {
            // Cria novo registro
            Vote::create([
                'user_id' => $userId,
                'meme_id' => $memeId,
                'vote_type' => $type,
            ]);
        }

        // Atualiza o modal caso esteja aberto
        if ($this->selectedMeme && $this->selectedMeme->id === $memeId) {
            $this->selectedMeme->refresh();
        }
    }

    public function render()
    {
        $meme = Meme::query()
            ->where('status', 'approved')
            ->when($this->search !== '', fn($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->with('user')
            ->latest()
            ->paginate(12);

        return view('livewire.meme-gallery', [
            'memes' => $meme,
        ]);
    }
}
