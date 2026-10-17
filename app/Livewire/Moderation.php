<?php

namespace App\Livewire;

use App\Models\Meme;
use App\Models\Report;
use Illuminate\Support\Collection;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Moderação - MemeIndex')]
class Moderation extends Component
{
    #[Url(as: 'aba', except: 'pendentes')]
    public string $tab = 'pendentes'; // pendentes | denuncias

    public ?int $selectedId = null;
    public bool $rejecting = false;
    public string $reason = '';

    public function mount(): void
    {
        $this->guard();
    }

    // Toda ação confere o papel de novo: as propriedades públicas vêm do cliente.
    private function guard(): void
    {
        abort_unless(in_array(auth()->user()?->role, ['moderator', 'admin'], true), 403);
    }

    private function isReports(): bool
    {
        return $this->tab === 'denuncias';
    }

    private function queue(): Collection
    {
        if ($this->isReports()) {
            return Report::with(['meme.user', 'user'])->where('status', 'pending')->oldest()->limit(50)->get();
        }

        return Meme::with('user')->whereIn('status', ['pending', 'flagged'])->oldest()->limit(50)->get();
    }

    public function setTab(string $tab): void
    {
        $this->guard();
        $this->tab = $tab === 'denuncias' ? 'denuncias' : 'pendentes';
        $this->selectedId = null;
        $this->cancelReject();
    }

    public function select(int $id): void
    {
        $this->guard();
        $this->selectedId = $id;
        $this->cancelReject();
    }

    public function move(int $dir): void
    {
        $this->guard();
        $ids = $this->queue()->pluck('id')->values();
        if ($ids->isEmpty()) {
            return;
        }

        $i = $ids->search($this->selectedId ?? $ids->first());
        $i = max(0, min($ids->count() - 1, ($i === false ? 0 : $i) + $dir));
        $this->selectedId = $ids[$i];
        $this->cancelReject();
    }

    public function startReject(): void
    {
        $this->guard();
        $this->rejecting = true;
        $this->reason = '';
    }

    public function cancelReject(): void
    {
        $this->rejecting = false;
        $this->reason = '';
        $this->resetValidation();
    }

    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    public function approve(): void
    {
        $this->guard();
        if ($this->isReports()) {
            return;
        }

        $queue = $this->queue();
        $meme = $queue->firstWhere('id', $this->selectedId ?? $queue->first()?->id);
        if (!$meme) {
            return;
        }

        $meme->update([
            'status' => 'approved',
            'rejection_reason' => null,
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
            'published_at' => $meme->published_at ?? now(),
        ]);

        $this->advance($queue, $meme->id);
        $this->dispatch('notify', message: 'Meme aprovado.');
    }

    // Rejeita o meme da fila (aba Pendentes) ou remove o meme denunciado (aba Denúncias).
    public function confirmReject(): void
    {
        $this->guard();
        $this->validate(['reason' => 'required|string|min:3|max:500'], [
            'reason.required' => 'Escreva o motivo.',
            'reason.min' => 'O motivo precisa de pelo menos 3 caracteres.',
            'reason.max' => 'O motivo passa de 500 caracteres.',
        ]);

        $queue = $this->queue();
        $item = $queue->firstWhere('id', $this->selectedId ?? $queue->first()?->id);
        if (!$item) {
            return;
        }

        $meme = $this->isReports() ? $item->meme : $item;
        $meme->update([
            'status' => 'rejected',
            'rejection_reason' => trim($this->reason),
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);

        if ($this->isReports()) {
            Report::where('meme_id', $meme->id)->where('status', 'pending')->update(['status' => 'reviewed']);
        }

        $this->advance($queue, $item->id);
        $this->dispatch('notify', message: $this->isReports() ? 'Meme removido.' : 'Meme rejeitado.');
    }

    public function dismissReport(): void
    {
        $this->guard();
        if (!$this->isReports()) {
            return;
        }

        $queue = $this->queue();
        $report = $queue->firstWhere('id', $this->selectedId ?? $queue->first()?->id);
        if (!$report) {
            return;
        }

        $report->update(['status' => 'dismissed']);

        $this->advance($queue, $report->id);
        $this->dispatch('notify', message: 'Denúncia dispensada.');
    }

    // Depois de resolver um item, seleciona o vizinho (ou limpa se a fila acabou).
    private function advance(Collection $before, int $resolvedId): void
    {
        $ids = $before->pluck('id')->values();
        $i = $ids->search($resolvedId);
        $this->selectedId = $ids[$i + 1] ?? ($i > 0 ? $ids[$i - 1] : null);
        $this->cancelReject();
    }

    public function render()
    {
        $this->guard();

        $queue = $this->queue();
        $selected = $queue->firstWhere('id', $this->selectedId) ?? $queue->first();
        $meme = $selected ? ($this->isReports() ? $selected->meme : $selected) : null;

        return view('livewire.moderation', [
            'queue' => $queue,
            'selected' => $selected,
            'meme' => $meme,
            'pendingCount' => Meme::whereIn('status', ['pending', 'flagged'])->count(),
            'reportCount' => Report::where('status', 'pending')->count(),
            'uploader' => $meme ? [
                'total' => Meme::where('user_id', $meme->user_id)->count(),
                'approved' => Meme::where('user_id', $meme->user_id)->where('status', 'approved')->count(),
                'rejected' => Meme::where('user_id', $meme->user_id)->where('status', 'rejected')->count(),
            ] : null,
            'sameMemeReports' => $this->isReports() && $selected
                ? Report::where('meme_id', $selected->meme_id)->where('status', 'pending')->count()
                : 0,
        ]);
    }
}
