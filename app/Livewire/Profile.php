<?php

namespace App\Livewire;

use App\Models\Meme;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Meu perfil - MemeIndex')]
class Profile extends Component
{
    private const STATUSES = ['pending', 'approved', 'rejected', 'flagged'];

    #[Url(as: 'status', except: '')]
    public string $status = '';

    public int $limit = 12;

    public function setStatus(string $status): void
    {
        $this->status = in_array($status, self::STATUSES, true) ? $status : '';
        $this->limit = 12;
    }

    public function more(): void
    {
        $this->limit += 12;
    }

    public function render()
    {
        $userId = auth()->id();
        $status = in_array($this->status, self::STATUSES, true) ? $this->status : '';

        $counts = Meme::where('user_id', $userId)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $total = $status === '' ? $counts->sum() : (int) ($counts[$status] ?? 0);

        $memes = Meme::where('user_id', $userId)
            ->when($status !== '', fn($q) => $q->where('status', $status))
            ->latest()
            ->limit($this->limit)
            ->get();

        return view('livewire.profile', [
            'user' => auth()->user(),
            'counts' => $counts,
            'allCount' => $counts->sum(),
            'memes' => $memes,
            'hasMore' => $total > $memes->count(),
            'activeStatus' => $status,
        ]);
    }
}
