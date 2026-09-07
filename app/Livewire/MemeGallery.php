<?php

namespace App\Livewire;

use App\Models\Meme;
use Livewire\Component;
use Livewire\WithPagination;

class MemeGallery extends Component
{

    use WithPagination;
    public string $search = '';

    public function updatingSearch(): void 
    {
        $this->resetPage();
    }

    public function render()
    {
        $meme = Meme::query()
            ->where('status', 'approved')
            ->when($this->search !== '', fn ($query) => $query->where('title', 'like', "%{$this->search}%"))
            ->with('user')
            ->latest()
            ->paginate(12);

        return view('livewire.meme-gallery', [
            'memes' => $meme,
            ]);
    }
}
