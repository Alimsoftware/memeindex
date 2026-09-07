<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showToast: false }">
    <section class="hero">
        <h1>MemeIndex BR</h1>
        <p>Seu índice pesquisável de memes, imagens e vídeos.</p>
    </section>


    {{-- Campo de Busca em Tempo Real --}}
    <div class="search-box">
        <span class="search-icon" aria-hidden="true">⌕</span>
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Buscar memes..." />
        @if(!empty($search))
            <button wire:click="$set('search', '')" class="search-clear" type="button" aria-label="Limpar busca"
                style="display: block;">
                ✕
            </button>
        @endif
    </div>

    {{-- Grid de Memes --}}
    <div id="grid" class="grid" aria-live="polite">
        @forelse($memes as $meme)
            <div class="card">
                <div class="media" wire:click="openModal({{ $meme->id }})" style="cursor: pointer;">
                    <img src="{{ Storage::url($meme->image_path) }}" alt="{{ $meme->title }}" />
                </div>

                <div class="card-body">
                    <h3 class="title line-clamp-1" wire:click="openModal({{ $meme->id }})" style="cursor: pointer;">
                        {{ $meme->title }}
                    </h3>
                    {{-- Botões de Votação --}}
                    <div class="actions my-3">
                        <button type="button" class="chip cyan" wire:click="vote({{ $meme->id }}, 'upvote')">
                            ▲ {{ $meme->upvotes_count }}
                        </button>

                        <button type="button" class="chip pink" wire:click="vote({{ $meme->id }}, 'downvote')">
                            ▼ {{ $meme->downvotes_count }}
                        </button>
                    </div>
                    <div class="meta mt-auto">
                        <span>Por <strong>{{ $meme->user->name }}</strong></span>
                        <span>•</span>
                        <span>{{ $meme->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="state">
                Nenhum meme aprovado foi encontrado.
            </div>
        @endforelse
    </div>

    <section id="sugestao" class="suggest">
        <h2>Sugerir um novo meme</h2>
        <p>A sugestão fica salva neste navegador. Para receber sugestões de todos os visitantes, conecte este
            formulário a
            uma API/backend.</p>
        <form id="suggestForm">
            <div class="form-grid">
                <input class="field" name="title" placeholder="Título do meme" maxlength="120" required>
                <input class="field" name="url" type="url" placeholder="URL da mídia (GIF, MP4, JPG...)" required>
                <textarea class="field full" name="tags" placeholder="Tags ou contexto (ex: deboche, choque)"
                    maxlength="500"></textarea>
                <button class="submit full" type="submit">Salvar sugestão</button>
            </div>
        </form>
    </section>

    {{-- Links de Paginação --}}
    <div class="mt-8">
        {{ $memes->links() }}
    </div>
    {{-- Modal Interativo --}}
    @if($showModal && $selectedMeme)
        <div class="modal open" wire:keydown.escape.window="closeModal">
            <div class="modal-box">
                <button type="button" class="modal-close" wire:click="closeModal" aria-label="Fechar">✕</button>

                <div class="modal-media">
                    <img src="{{ Storage::url($selectedMeme->image_path) }}" alt="{{ $selectedMeme->title }}" />
                </div>

                <div class="modal-info">
                    <h2>{{ $selectedMeme->title }}</h2>
                    <p>Enviado por <strong>{{ $selectedMeme->user->name }}</strong> •
                        {{ $selectedMeme->created_at->diffForHumans() }}
                    </p>

                    <div class="actions mt-4" style="display: flex; gap: 10px;">
                        <button type="button" class="action"
                            onclick="navigator.clipboard.writeText('{{ url(Storage::url($selectedMeme->image_path)) }}'); $dispatch('notify');">
                            Copiar Link da Imagem
                        </button>
                        <a href="{{ Storage::url($selectedMeme->image_path) }}" target="_blank" download class="action pink"
                            style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            Baixar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Toast de Notificação --}}
    <div class="toast" :class="{ 'show': showToast }"
        @notify.window="showToast = true; setTimeout(() => showToast = false, 2500)">
        Link copiado para a área de transferência!
    </div>
</div>