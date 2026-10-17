<div class="page profile">
    <section class="profile-head">
        <span class="avatar-lg" aria-hidden="true">{{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}</span>
        <div>
            <p class="eyebrow">Meu perfil</p>
            <h1>{{ $user->name }}</h1>
            <p class="profile-meta">{{ $user->email }} • membro desde {{ $user->created_at->translatedFormat('F \d\e Y') }}</p>
        </div>
    </section>

    @php
        $tabs = [
            '' => ['Todos', $allCount],
            'pending' => ['Pendentes', $counts['pending'] ?? 0],
            'approved' => ['Aprovados', $counts['approved'] ?? 0],
            'rejected' => ['Rejeitados', $counts['rejected'] ?? 0],
            'flagged' => ['Em revisão', $counts['flagged'] ?? 0],
        ];
        $labels = [
            'pending' => 'Pendente',
            'approved' => 'Aprovado',
            'rejected' => 'Rejeitado',
            'flagged' => 'Em revisão',
        ];
    @endphp

    <h2 class="section-title">Meus envios</h2>

    <div class="chips" role="group" aria-label="Filtrar por status">
        @foreach($tabs as $key => [$label, $n])
            <button type="button" class="chip {{ $activeStatus === (string) $key ? 'active' : '' }}"
                aria-pressed="{{ $activeStatus === (string) $key ? 'true' : 'false' }}" wire:click="setStatus('{{ $key }}')">
                {{ $label }} <span class="chip-n">{{ $n }}</span>
            </button>
        @endforeach
    </div>

    <div class="my-list">
        @forelse($memes as $meme)
            <article class="my-item" wire:key="my-{{ $meme->id }}">
                <img class="my-thumb" src="{{ Storage::url($meme->image_path) }}" alt="{{ $meme->title }}" loading="lazy"
                    onerror="this.classList.add('img-broken')">
                <div class="my-body">
                    <div class="my-top">
                        <h3 class="my-title">{{ $meme->title }}</h3>
                        <span class="status st-{{ $meme->status }}">{{ $labels[$meme->status] ?? $meme->status }}</span>
                    </div>
                    <p class="card-meta wrap">
                        Enviado {{ $meme->created_at->diffForHumans() }} • #{{ $meme->category }} • {{ $meme->is_gif ? 'GIF' : 'Imagem' }}
                    </p>

                    @if($meme->status === 'pending')
                        <p class="my-note">Aguardando revisão da moderação.</p>
                    @elseif($meme->status === 'flagged')
                        <p class="my-note">Recebeu denúncias e está sendo revisado.</p>
                    @elseif($meme->status === 'rejected')
                        <p class="my-note reject">
                            <strong>Motivo:</strong> {{ $meme->rejection_reason ?: 'Não informado pela moderação.' }}
                        </p>
                    @else
                        <a class="my-link" href="{{ route('home', ['q' => $meme->title]) }}">Ver no índice</a>
                    @endif
                </div>
            </article>
        @empty
            <div class="state">
                @if($allCount === 0)
                    <p class="state-code">Nada por aqui</p>
                    <p class="state-title">Você ainda não enviou memes.</p>
                    <p class="state-text">Mande o primeiro e acompanhe a aprovação aqui.</p>
                    <div class="state-actions">
                        <a class="btn-primary" href="{{ route('upload') }}">Enviar meme</a>
                    </div>
                @else
                    <p class="state-title">Nenhum meme com esse status.</p>
                    <div class="state-actions">
                        <button type="button" class="btn-ghost" wire:click="setStatus('')">Ver todos</button>
                    </div>
                @endif
            </div>
        @endforelse
    </div>

    @if($hasMore)
        <div class="more-wrap">
            <button type="button" class="btn-ghost" wire:click="more" wire:loading.attr="disabled" wire:target="more">
                <span wire:loading.remove wire:target="more">Mostrar mais</span>
                <span wire:loading wire:target="more">Carregando…</span>
            </button>
        </div>
    @endif
</div>
