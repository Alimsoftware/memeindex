@use('App\Support\Emojis')
<div class="page" x-data="{
        toast: '', showToast: false, timer: null,
        notify(msg) { this.toast = msg; this.showToast = true; clearTimeout(this.timer); this.timer = setTimeout(() => this.showToast = false, 2500); },
        copy(url) { navigator.clipboard.writeText(url).then(() => this.notify('Link copiado!')); },
        share(title, url) { navigator.share ? navigator.share({ title, url }).catch(() => {}) : this.copy(url); }
    }" @notify.window="notify($event.detail.message)">
    @php $hasFilters = $search !== '' || $category !== '' || $format !== '' || $filter !== 'latest'; @endphp

    <div class="offline" wire:offline role="alert">Sem conexão com o servidor. Verifique sua internet e tente de novo.</div>

    <section class="hero">
        <p class="eyebrow">Arquivo de memes brasileiros e de nicho</p>
        <h1>Memes que o Google esqueceu</h1>
    </section>

    {{-- Filtros --}}
    <section class="filters-wrap" aria-label="Filtros">
        <div class="chips" role="group" aria-label="Ordenar">
            @foreach(['latest' => 'Recentes', 'popular' => 'Populares', 'favorites' => 'Favoritos'] as $key => $label)
                <button type="button" class="chip {{ $filter === $key ? 'active' : '' }}" aria-pressed="{{ $filter === $key ? 'true' : 'false' }}"
                    wire:click="setFilter('{{ $key }}')">{{ $label }}</button>
            @endforeach
        </div>

        <div class="chips" role="group" aria-label="Categoria e formato">
            @foreach($categories as $cat)
                <button type="button" class="chip tag {{ $category === $cat ? 'active' : '' }}" aria-pressed="{{ $category === $cat ? 'true' : 'false' }}"
                    wire:click="setCategory(@js($cat))" wire:key="cat-{{ $cat }}">#{{ $cat }}</button>
            @endforeach
            @if($categories->isNotEmpty())<span class="chip-sep" aria-hidden="true"></span>@endif
            @foreach(['gif' => 'GIF', 'image' => 'Imagem'] as $key => $label)
                <button type="button" class="chip {{ $format === $key ? 'active' : '' }}" aria-pressed="{{ $format === $key ? 'true' : 'false' }}"
                    wire:click="setFormat('{{ $key }}')">{{ $label }}</button>
            @endforeach
            @if($hasFilters)
                <button type="button" class="chip clear" wire:click="clearFilters">Limpar filtros ✕</button>
            @endif
        </div>
    </section>

    <p class="result-line" aria-live="polite">
        <strong>{{ number_format($memes->total(), 0, ',', '.') }}</strong>
        {{ $memes->total() === 1 ? 'meme' : 'memes' }}
        @if($search !== '') para “{{ $search }}” @endif
    </p>

    <h2 class="sr-only">Memes</h2>

    {{-- Skeleton: aparece enquanto o Livewire busca --}}
    <div class="grid grid-skeleton" aria-hidden="true" wire:loading.grid.delay.shortest
        wire:target="setFilter,setCategory,setFormat,clearFilters,gotoPage,nextPage,previousPage,setSearch">
        @for($i = 0; $i < 8; $i++)
            <div class="card-item skeleton">
                <div class="card-media"></div>
                <div class="card-body"><span class="sk-line"></span><span class="sk-line short"></span></div>
            </div>
        @endfor
    </div>

    {{-- Grid de Memes --}}
    <div id="grid" class="grid" wire:loading.remove.delay.shortest
        wire:target="setFilter,setCategory,setFormat,clearFilters,gotoPage,nextPage,previousPage,setSearch">
        @forelse($memes as $meme)
            @php $url = Storage::url($meme->image_path); $up = $meme->upvotes_count; $down = $meme->downvotes_count; @endphp
            <article class="card-item" wire:key="meme-{{ $meme->id }}">
                <div class="card-media">
                    <button type="button" class="card-open" wire:click="openModal({{ $meme->id }})"
                        aria-label="Abrir {{ $meme->title }}">
                        <img src="{{ $url }}" alt="{{ $meme->title }}" loading="lazy"
                            onerror="this.classList.add('img-broken')" />
                    </button>
                    @if($meme->is_gif)<span class="badge">GIF</span>@endif

                    <div class="card-quick">
                        <button type="button" class="quick" @click="copy(@js(url($url)))" aria-label="Copiar link" title="Copiar link">
                            <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" /><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" /></svg>
                        </button>
                        <button type="button" class="quick" @click="share(@js($meme->title), @js(url($url)))" aria-label="Compartilhar" title="Compartilhar">
                            <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3" /><circle cx="6" cy="12" r="3" /><circle cx="18" cy="19" r="3" /><path d="M8.6 13.5l6.8 4M15.4 6.5l-6.8 4" /></svg>
                        </button>
                        <button type="button" class="quick" wire:click="openReportModal({{ $meme->id }})" aria-label="Reportar" title="Reportar">
                            <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22V4M4 4h13l-2 4 2 4H4" /></svg>
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <h3 class="card-title"><button type="button" wire:click="openModal({{ $meme->id }})">{{ $meme->title }}</button></h3>
                    <p class="card-meta">
                        {{ ($meme->published_at ?? $meme->created_at)->format('Y') }} • por {{ $meme->user->name }} • {{ $meme->is_gif ? 'GIF' : 'Imagem' }}
                    </p>
                    <ul class="tags"><li>#{{ $meme->category }}</li></ul>
                </div>

                <footer class="card-actions">
                    <button type="button" class="action-btn upvote" wire:click="vote({{ $meme->id }}, 'upvote')" aria-label="Upvote, {{ $up }}">
                        <svg viewBox="0 0 24 24" class="icon"><path fill="currentColor" d="M12 4l-8 8h5v8h6v-8h5z" /></svg>
                        <span class="count">{{ $up }}</span>
                    </button>
                    <button type="button" class="action-btn downvote" wire:click="vote({{ $meme->id }}, 'downvote')" aria-label="Downvote, {{ $down }}">
                        <svg viewBox="0 0 24 24" class="icon"><path fill="currentColor" d="M12 20l8-8h-5V4h-6v8H4z" /></svg>
                        <span class="count">{{ $down }}</span>
                    </button>
                    <button type="button" class="action-btn favorite {{ $meme->is_favorited ? 'active' : '' }}"
                        wire:click="toggleFavorite({{ $meme->id }})" aria-label="Favoritar" aria-pressed="{{ $meme->is_favorited ? 'true' : 'false' }}">
                        <svg viewBox="0 0 24 24" class="icon" fill="{{ $meme->is_favorited ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2"><path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" /></svg>
                    </button>
                    <button type="button" class="action-btn comment" wire:click="openModal({{ $meme->id }})" aria-label="Comentários, {{ $meme->comments_count }}">
                        <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" /></svg>
                        <span class="count">{{ $meme->comments_count }}</span>
                    </button>
                    <a href="{{ $url }}" download class="action-btn download" aria-label="Baixar" title="Baixar">
                        <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" /></svg>
                    </a>
                </footer>
            </article>
        @empty
            <div class="state">
                <p class="state-code">Sem resultados</p>
                <p class="state-title">Nenhum meme encontrado.</p>
                <p class="state-text">Tente outra frase ou envie esse meme para o índice.</p>
                <div class="state-actions">
                    @if($hasFilters)<button type="button" class="btn-ghost" wire:click="clearFilters">Limpar filtros</button>@endif
                    <a class="btn-primary" href="{{ route('upload') }}">Enviar meme</a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Paginação numerada --}}
    @php $cur = $memes->currentPage(); @endphp
    @php $last = $memes->lastPage(); @endphp
    <div class="pager-wrap" @click="if ($event.target.closest('.pager button')) $nextTick(() => document.getElementById('grid').scrollIntoView({ behavior: 'smooth', block: 'start' }))">
        @if($last > 1)
            <nav class="pager" aria-label="Paginação">
                <button type="button" wire:click="previousPage" @disabled($cur === 1) aria-label="Página anterior">‹</button>
                @php $gap = false; @endphp
                @foreach(range(1, $last) as $p)
                    @if($p === 1 || $p === $last || abs($p - $cur) <= 1)
                        <button type="button" wire:click="gotoPage({{ $p }})" wire:key="pg-{{ $p }}"
                            class="{{ $p === $cur ? 'active' : '' }}" @if($p === $cur) aria-current="page" @endif
                            aria-label="Página {{ $p }}">{{ $p }}</button>
                        @php $gap = false; @endphp
                    @elseif(!$gap)
                        <span class="pager-gap" aria-hidden="true">…</span>
                        @php $gap = true; @endphp
                    @endif
                @endforeach
                <button type="button" wire:click="nextPage" @disabled($cur === $last) aria-label="Próxima página">›</button>
            </nav>
        @endif
        <p class="pager-info">PÁGINA {{ $cur }} DE {{ $last }} • {{ number_format($memes->total(), 0, ',', '.') }} {{ $memes->total() === 1 ? 'MEME INDEXADO' : 'MEMES INDEXADOS' }}</p>
    </div>


    {{-- Modal Interativo --}}
    @if($showModal && $selectedMeme)
        <div class="modal open" wire:keydown.escape.window="closeModal" wire:click.self="closeModal" x-data x-trap.noscroll="true">
            <div class="modal-box" role="dialog" aria-modal="true" aria-labelledby="modal-title">
                <button type="button" class="modal-close" wire:click="closeModal" aria-label="Fechar">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18" /></svg>
                </button>

                <div class="modal-media">
                    <img src="{{ Storage::url($selectedMeme->image_path) }}" alt="{{ $selectedMeme->title }}" />
                </div>

                <div class="modal-side">
                    <div class="modal-info">
                        <p class="eyebrow">Meme #{{ $selectedMeme->id }}</p>
                        <h2 id="modal-title">{{ $selectedMeme->title }}</h2>
                        <p class="card-meta wrap">
                            {{ ($selectedMeme->published_at ?? $selectedMeme->created_at)->format('Y') }} • por {{ $selectedMeme->user->name }} • {{ $selectedMeme->is_gif ? 'GIF' : 'Imagem' }} • {{ $selectedMeme->created_at->diffForHumans() }}
                        </p>
                        @if($selectedMeme->description)
                            <p class="modal-desc">{{ $selectedMeme->description }}</p>
                        @endif
                        <ul class="tags"><li>#{{ $selectedMeme->category }}</li></ul>

                        <div class="modal-actions">
                            <button type="button" class="action" @click="copy(@js(url(Storage::url($selectedMeme->image_path))))">Copiar link</button>
                            <a href="{{ Storage::url($selectedMeme->image_path) }}" download class="action">Baixar</a>
                            <button type="button" class="action warn" wire:click="openReportModal({{ $selectedMeme->id }})">Reportar</button>
                        </div>
                    </div>

                    {{-- Chat: tema do site por padrão, WhatsApp como opção --}}
                    <section class="chat" aria-label="Comentários" :data-theme="theme" x-data="{
                            theme: (() => { try { return localStorage.getItem('chatTheme') === 'whatsapp' ? 'whatsapp' : 'site'; } catch (e) { return 'site'; } })(),
                            set(t) { this.theme = t; try { localStorage.setItem('chatTheme', t); } catch (e) {} }
                        }">
                        <header class="chat-head">
                            <div class="chat-title">
                                <strong>Conversa</strong>
                                <small>{{ $messages->count() }} {{ $messages->count() === 1 ? 'mensagem' : 'mensagens' }}</small>
                            </div>
                            <div class="theme-switch" role="group" aria-label="Tema do chat">
                                <button type="button" :aria-pressed="theme === 'site'" @click="set('site')">Site</button>
                                <button type="button" :aria-pressed="theme === 'whatsapp'" @click="set('whatsapp')">WhatsApp</button>
                            </div>
                        </header>

                    <div class="chat-body" x-data x-init="$nextTick(() => $el.scrollTop = $el.scrollHeight)"
                        @chat-sent.window="$nextTick(() => $el.scrollTop = $el.scrollHeight)">
                        @php $prevDay = null; @endphp
                        @forelse($messages as $msg)
                            @php
                                $day = $msg->created_at->toDateString();
                                $mine = auth()->id() === $msg->user_id;
                                $palette = ['#53bdeb', '#ffa726', '#ff7a77', '#d08be0', '#66bb6a', '#ffee58', '#26c6da', '#ff7aa5'];
                                $color = $palette[$msg->user_id % count($palette)];
                            @endphp

                            @if($day !== $prevDay)
                                <div class="chat-day">
                                    <span>{{ $msg->created_at->isToday() ? 'HOJE' : ($msg->created_at->isYesterday() ? 'ONTEM' : $msg->created_at->format('d/m/Y')) }}</span>
                                </div>
                                @php $prevDay = $day; @endphp
                            @endif

                            <div class="msg {{ $mine ? 'mine' : '' }}" wire:key="msg-{{ $msg->id }}">
                                @unless($mine)
                                    <span class="avatar" aria-hidden="true" style="background: {{ $color }}22; color: {{ $color }}">{{ mb_strtoupper(mb_substr($msg->user->name, 0, 1)) }}</span>
                                @endunless

                                <div class="bubble">
                                    @unless($mine)
                                        <span class="sender" style="color: {{ $color }}">{{ $msg->user->name }}</span>
                                    @endunless

                                    @if($msg->parent)
                                        <div class="quote">
                                            <span class="quote-name">{{ $msg->parent->user_id === auth()->id() ? 'Você' : $msg->parent->user->name }}</span>
                                            <span class="quote-text">{{ Emojis::render($msg->parent->content) }}</span>
                                        </div>
                                    @endif

                                    <p class="text {{ Emojis::isOnlyEmojis($msg->content) ? 'emoji-only' : '' }}">{{ Emojis::render($msg->content) }}<span class="time">{{ $msg->created_at->format('H:i') }}</span></p>

                                    @auth
                                        <div class="msg-tools">
                                            <button type="button" class="link-btn" wire:click="startReply({{ $msg->id }})">Responder</button>
                                            @if($mine)
                                                <button type="button" class="link-btn danger" wire:click="deleteComment({{ $msg->id }})"
                                                    wire:confirm="Apagar esta mensagem{{ $msg->parent_id ? '' : ' e suas respostas' }}?">Apagar</button>
                                            @endif
                                        </div>
                                    @endauth
                                </div>
                            </div>
                        @empty
                            <p class="chat-empty">Nenhuma mensagem ainda. Mande a primeira!</p>
                        @endforelse
                    </div>

                    @auth
                        @php $replyTarget = $replyingTo ? $messages->firstWhere('id', $replyingTo) : null; @endphp
                        @if($replyTarget)
                            <div class="chat-replying">
                                <div class="quote">
                                    <span class="quote-name">{{ $replyTarget->user_id === auth()->id() ? 'Você' : $replyTarget->user->name }}</span>
                                    <span class="quote-text">{{ Emojis::render($replyTarget->content) }}</span>
                                </div>
                                <button type="button" class="dialog-close" wire:click="cancelReply" aria-label="Cancelar resposta">✕</button>
                            </div>
                        @endif

                        <form wire:submit.prevent="addComment" class="chat-input" x-data="{
                                open: false,
                                items: @js(\App\Support\Emojis::items()),
                                q: '',
                                norm(s) { return s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase(); },
                                get list() { const q = this.norm(this.q.trim()); return q ? this.items.filter(i => this.norm(i.name).includes(q)) : this.items; },
                                insert(n) {
                                    const i = this.$refs.msg, t = ':e' + n + ':';
                                    const s = i.selectionStart ?? i.value.length, e = i.selectionEnd ?? s;
                                    if (i.value.length - (e - s) + t.length > 500) return;
                                    i.value = i.value.slice(0, s) + t + i.value.slice(e);
                                    i.dispatchEvent(new Event('input', { bubbles: true }));
                                    i.focus(); i.setSelectionRange(s + t.length, s + t.length);
                                }
                            }" @click.outside="open = false"
                            @keydown.escape="if (open) { $event.stopPropagation(); open = false; $refs.toggle.focus(); }">
                            <div class="emoji-picker" x-show="open" x-cloak role="group" aria-label="Escolher emoji">
                                <input type="search" class="emoji-search" x-model="q" x-ref="search" placeholder="Buscar emoji"
                                    aria-label="Buscar emoji" autocomplete="off" @keydown.enter.prevent>
                                <div class="emoji-grid">
                                    <template x-for="i in list" :key="i.n">
                                        <button type="button" class="emoji-btn" @click="insert(i.n)" :aria-label="i.name" :title="i.name">
                                            <img :src="'/emojis/expression_' + i.n + '.png'" alt="" width="28" height="28" loading="lazy">
                                        </button>
                                    </template>
                                </div>
                                <p class="emoji-none" x-show="list.length === 0">Nenhum emoji com esse nome.</p>
                            </div>
                            <button type="button" class="emoji-toggle" x-ref="toggle" @click="open = !open; if (open) $nextTick(() => $refs.search.focus())" :aria-expanded="open"
                                aria-label="Emojis">
                                <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9" /><path d="M8.5 14.5a4.5 4.5 0 0 0 7 0" /><path d="M9 9.5h.01M15 9.5h.01" stroke-width="2.4" /></svg>
                            </button>
                            <input type="text" x-ref="msg" wire:model="newComment" placeholder="Digite uma mensagem" maxlength="500"
                                autocomplete="off" aria-label="Mensagem" />
                            <button type="submit" class="send" aria-label="Enviar" wire:loading.attr="disabled" wire:target="addComment">
                                <svg viewBox="0 0 24 24" width="22" height="22"><path fill="currentColor" d="M2 21l21-9L2 3v7l15 2-15 2z" /></svg>
                            </button>
                        </form>
                        @error('newComment')
                            <span class="field-error chat-error">{{ $message }}</span>
                        @enderror
                    @else
                        <p class="chat-login"><a href="{{ route('login') }}">Entre</a> ou <a href="{{ route('register') }}">crie uma conta</a> para participar da conversa.</p>
                    @endauth
                </section>
                </div>
            </div>
        </div>
    @endif

    @if($showReportModal)
        <div class="modal open" wire:keydown.escape.window="closeReportModal" wire:click.self="closeReportModal" x-data x-trap.noscroll="true">
            <div class="dialog" role="dialog" aria-modal="true" aria-labelledby="report-title">
                <div class="dialog-head">
                    <div>
                        <p class="eyebrow warn">Denúncia</p>
                        <h3 id="report-title">Reportar meme</h3>
                    </div>
                    <button type="button" class="dialog-close" wire:click="closeReportModal" aria-label="Fechar">✕</button>
                </div>

                <form wire:submit.prevent="submitReport">
                    <label class="label" for="report-reason">Motivo</label>
                    <select id="report-reason" wire:model="reportReason" class="field">
                        <option value="inappropriate">Conteúdo Impróprio / Ofensivo</option>
                        <option value="duplicated">Conteúdo Duplicado</option>
                        <option value="offensive">Conteúdo Ofensivo</option>
                        <option value="spam">Spam ou Conteúdo Duplicado</option>
                        <option value="copyright">Violação de Direitos Autorais</option>
                        <option value="other">Outro Motivo</option>
                    </select>

                    <label class="label" for="report-desc">Detalhes adicionais (opcional)</label>
                    <textarea id="report-desc" wire:model="reportDescription" class="field" rows="3" maxlength="500"
                        placeholder="Explique o motivo da denúncia..."></textarea>
                    @error('reportDescription')
                        <span class="field-error">{{ $message }}</span>
                    @enderror

                    <div class="dialog-actions">
                        <button type="button" class="action" wire:click="closeReportModal">Cancelar</button>
                        <button type="submit" class="submit danger">Enviar Denúncia</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Toast de Notificação --}}
   <div class="toast" :class="{ 'show': showToast }" x-text="toast" role="status" aria-live="polite"></div>
</div>