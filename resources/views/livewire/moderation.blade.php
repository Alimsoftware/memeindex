<div class="page mod" x-data="{
        toast: '', showToast: false, timer: null,
        notify(msg) { this.toast = msg; this.showToast = true; clearTimeout(this.timer); this.timer = setTimeout(() => this.showToast = false, 2500); },
        key(e) {
            if (e.metaKey || e.ctrlKey || e.altKey || ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
            const k = e.key.toLowerCase();
            const reports = $wire.tab === 'denuncias';
            if (k === 'j') { e.preventDefault(); $wire.move(1); }
            else if (k === 'k') { e.preventDefault(); $wire.move(-1); }
            else if (k === 'a' && !reports) { e.preventDefault(); $wire.approve(); }
            else if (k === 'd' && reports) { e.preventDefault(); $wire.dismissReport(); }
            else if (k === 'r') { e.preventDefault(); $wire.startReject(); }
        }
    }" @notify.window="notify($event.detail.message)" @keydown.window="key($event)">
    @php
        $reasons = [
            'inappropriate' => 'Impróprio / ofensivo',
            'duplicated' => 'Duplicado',
            'offensive' => 'Ofensivo',
            'spam' => 'Spam',
            'copyright' => 'Direitos autorais',
            'other' => 'Outro',
        ];
        $presets = ['Conteúdo duplicado', 'Fora do escopo do índice', 'Baixa qualidade', 'Conteúdo proibido', 'Violação de direitos autorais'];
        $isReports = $tab === 'denuncias';
    @endphp

    <section class="hero">
        <p class="eyebrow">Moderação</p>
        <h1>Fila de revisão</h1>
    </section>

    <div class="chips" role="group" aria-label="Fila">
        <button type="button" aria-pressed="{{ !$isReports ? 'true' : 'false' }}" class="chip {{ !$isReports ? 'active' : '' }}"
            wire:click="setTab('pendentes')">Pendentes <span class="chip-n">{{ $pendingCount }}</span></button>
        <button type="button" aria-pressed="{{ $isReports ? 'true' : 'false' }}" class="chip {{ $isReports ? 'active' : '' }}"
            wire:click="setTab('denuncias')">Denúncias <span class="chip-n">{{ $reportCount }}</span></button>
        <span class="chip-sep" aria-hidden="true"></span>
        <p class="shortcuts" aria-label="Atalhos de teclado">
            <kbd>J</kbd><kbd>K</kbd> navegar
            @if($isReports) <kbd>D</kbd> dispensar @else <kbd>A</kbd> aprovar @endif
            <kbd>R</kbd> {{ $isReports ? 'remover' : 'rejeitar' }}
        </p>
    </div>

    @if($queue->isEmpty())
        <div class="state">
            <p class="state-code ok">Fila vazia</p>
            <p class="state-title">Nada para revisar.</p>
            <p class="state-text">{{ $isReports ? 'Nenhuma denúncia pendente.' : 'Todos os envios foram revisados.' }}</p>
        </div>
    @else
        <div class="mod-grid">
            <aside class="mod-queue" aria-label="Itens da fila">
                @foreach($queue as $item)
                    @php $m = $isReports ? $item->meme : $item; @endphp
                    <button type="button" class="mod-item {{ $selected && $selected->id === $item->id ? 'active' : '' }}"
                        wire:click="select({{ $item->id }})" wire:key="q-{{ $tab }}-{{ $item->id }}"
                        aria-current="{{ $selected && $selected->id === $item->id ? 'true' : 'false' }}"
                        @click="if (innerWidth < 900) $nextTick(() => $refs.detail?.scrollIntoView({ behavior: 'smooth', block: 'start' }))">
                        <img src="{{ Storage::url($m->image_path) }}" alt="" loading="lazy" onerror="this.classList.add('img-broken')">
                        <span class="mod-item-text">
                            <strong>{{ $m->title }}</strong>
                            <small>
                                @if($isReports) {{ $reasons[$item->reason] ?? $item->reason }} • @endif
                                {{ $isReports ? $item->user->name : $m->user->name }} • {{ $item->created_at->diffForHumans() }}
                            </small>
                        </span>
                        @if(!$isReports && $m->status === 'flagged')<span class="status st-flagged">Revisão</span>@endif
                    </button>
                @endforeach
            </aside>

            <section class="mod-detail" x-ref="detail" aria-label="Pré-visualização">
                @if($meme)
                    <div class="mod-media">
                        <img src="{{ Storage::url($meme->image_path) }}" alt="{{ $meme->title }}">
                    </div>

                    <div class="mod-info">
                        <div class="my-top">
                            <h2 class="my-title">{{ $meme->title }}</h2>
                            <span class="status st-{{ $meme->status }}">{{ ['pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado', 'flagged' => 'Em revisão'][$meme->status] ?? $meme->status }}</span>
                        </div>
                        <p class="card-meta wrap">
                            Enviado por <strong>{{ $meme->user->name }}</strong> {{ $meme->created_at->diffForHumans() }} • #{{ $meme->category }} • {{ $meme->is_gif ? 'GIF' : 'Imagem' }}
                        </p>
                        <p class="card-meta wrap">
                            Histórico do autor: {{ $uploader['total'] }} {{ $uploader['total'] === 1 ? 'envio' : 'envios' }},
                            {{ $uploader['approved'] }} {{ $uploader['approved'] === 1 ? 'aprovado' : 'aprovados' }},
                            {{ $uploader['rejected'] }} {{ $uploader['rejected'] === 1 ? 'rejeitado' : 'rejeitados' }}
                        </p>
                        @if($meme->description)<p class="modal-desc">{{ $meme->description }}</p>@endif

                        @if($isReports)
                            <div class="my-note reject">
                                <strong>{{ $reasons[$selected->reason] ?? $selected->reason }}</strong>
                                — denunciado por {{ $selected->user->name }} {{ $selected->created_at->diffForHumans() }}
                                @if($selected->comment)<br>“{{ $selected->comment }}”@endif
                                @if($sameMemeReports > 1)<br><small>{{ $sameMemeReports }} denúncias pendentes neste meme.</small>@endif
                            </div>
                        @endif

                        @if($rejecting)
                            <form class="mod-reject" wire:submit.prevent="confirmReject" wire:keydown.escape="cancelReject">
                                <label class="label" for="reason">{{ $isReports ? 'Motivo da remoção' : 'Motivo da rejeição' }} <span class="opt">(o autor vê)</span></label>
                                <div class="chips presets">
                                    @foreach($presets as $p)
                                        <button type="button" class="chip" wire:click="setReason(@js($p))">{{ $p }}</button>
                                    @endforeach
                                </div>
                                <input id="reason" class="field" type="text" wire:model="reason" maxlength="500" autocomplete="off" autofocus
                                    placeholder="Escreva o motivo e aperte Enter">
                                @error('reason') <span class="field-error" role="alert">{{ $message }}</span> @enderror
                                <div class="mod-actions">
                                    <button type="submit" class="submit danger">{{ $isReports ? 'Remover meme' : 'Rejeitar' }}</button>
                                    <button type="button" class="btn-ghost" wire:click="cancelReject">Cancelar <kbd>Esc</kbd></button>
                                </div>
                            </form>
                        @else
                            <div class="mod-actions">
                                @if($isReports)
                                    <button type="button" class="btn-primary" wire:click="dismissReport" wire:loading.attr="disabled">Dispensar denúncia <kbd>D</kbd></button>
                                    <button type="button" class="btn-ghost warn" wire:click="startReject">Remover meme <kbd>R</kbd></button>
                                @else
                                    <button type="button" class="btn-primary" wire:click="approve" wire:loading.attr="disabled">Aprovar <kbd>A</kbd></button>
                                    <button type="button" class="btn-ghost warn" wire:click="startReject">Rejeitar <kbd>R</kbd></button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    @endif

    <div class="toast" :class="{ 'show': showToast }" x-text="toast" role="status" aria-live="polite"></div>
</div>
