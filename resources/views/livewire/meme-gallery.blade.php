<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ showToast: false }">
    <section class="hero">
        <h1>MemeIndex BR</h1>
        <p>Seu índice pesquisável de memes.</p>
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
            <article class="card-item">
                <div class="card-image-wrapper" wire:click="openModal({{ $meme->id }})" style="cursor: pointer;">
                    <img src="{{ Storage::url($meme->image_path) }}" alt="{{ $meme->title }}" class="card-image" />
                </div>

                <div class="card-info p-3">
                    <h3 class="title line-clamp-1 mb-1" wire:click="openModal({{ $meme->id }})" style="cursor: pointer;">
                        {{ $meme->title }}
                    </h3>
                    <div class="meta text-xs text-gray-500 mb-2">
                        <span>Por <strong>{{ $meme->user->name }}</strong></span>
                        <span>•</span>
                        <span>{{ $meme->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <footer class="card-actions">
                    <div class="action-group main-actions">
                        <button type="button" class="action-btn upvote" wire:click="vote({{ $meme->id }}, 'upvote')"
                            aria-label="Upvote">
                            <svg viewBox="0 0 24 24" class="icon">
                                <path fill="currentColor" d="M12 4l-8 8h5v8h6v-8h5z" />
                            </svg>
                            <span class="count">{{ $meme->upvotes_count }}</span>
                        </button>

                        <button type="button" class="action-btn downvote" wire:click="vote({{ $meme->id }}, 'downvote')"
                            aria-label="Downvote">
                            <svg viewBox="0 0 24 24" class="icon">
                                <path fill="currentColor" d="M12 20l8-8h-5V4h-6v8H4z" />
                            </svg>
                            <span class="count">{{ $meme->downvotes_count }}</span>
                        </button>

                        <button type="button" class="action-btn favorite {{ $meme->is_favorited ? 'active' : '' }}"
                            wire:click="toggleFavorite({{ $meme->id }})" aria-label="Favoritar">
                            <svg viewBox="0 0 24 24" class="icon" fill="{{ $meme->is_favorited ? 'currentColor' : 'none' }}"
                                stroke="currentColor" stroke-width="2">
                                <path
                                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                            </svg>

                        </button>

                        <button type="button" class="action-btn comment" wire:click="openModal({{ $meme->id }})"
                            aria-label="Comentar">
                            <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                            </svg>
                            <span class="count">{{ $meme->comments->count() }}</span>
                        </button>
                    </div>

                    <div class="action-group utility-actions">
                        <a href="{{ Storage::url($meme->image_path) }}" download class="action-btn icon-only"
                            aria-label="Baixar">
                            <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" />
                            </svg>
                        </a>

<button type="button" class="action-btn icon-only"
    onclick="navigator.clipboard.writeText('{{ url(Storage::url($meme->image_path)) }}').then(() => window.dispatchEvent(new CustomEvent('notify', { detail: { message: 'Link copiado para a área de transferência!' } })))"
    aria-label="Compartilhar link">
    <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2">
        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" />
        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" />
    </svg>
</button>

                        <button type="button" class="action-btn icon-only" wire:click="openReportModal({{ $meme->id }})"
                            aria-label="Reportar">
                            <svg viewBox="0 0 24 24" class="icon" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                            </svg>
                        </button>
                    </div>
                </footer>
            </article>
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
                            onclick="navigator.clipboard.writeText('{{ url(Storage::url($selectedMeme->image_path)) }}').then(() => $dispatch('notify'))">
                            Copiar Link da Imagem
                        </button>
                        <a href="{{ Storage::url($selectedMeme->image_path) }}" target="_blank" download class="action pink"
                            style="display: inline-flex; align-items: center; justify-content: center; text-decoration: none;">
                            Baixar
                        </a>
                    </div>
                </div>
                {{-- Seção de Comentários --}}
                <div class="comments-section mt-4 pt-4" style="border-top: 1px solid var(--border);">
                    <h4 style="color: var(--cyan); font-weight: 700; margin-bottom: 12px;">
                        💬 Comentários ({{ $selectedMeme->comments->count() }})
                    </h4>

                    {{-- Formulário de Novo Comentário --}}
                    @auth
                        <form wire:submit.prevent="addComment" class="mb-4">
                            <div style="display: flex; gap: 8px;">
                                <input type="text" wire:model="newComment" placeholder="Escreva um comentário..." class="field"
                                    style="flex: 1;" />
                                <button type="submit" class="submit" style="width: auto; padding: 0 16px;">
                                    Enviar
                                </button>
                            </div>
                            @error('newComment')
                                <span style="color: var(--pink); font-size: 0.75rem;">{{ $message }}</span>
                            @enderror
                        </form>
                    @else
                        <p style="color: var(--muted); font-size: 0.85rem; margin-bottom: 12px;">
                            Faça login para deixar um comentário.
                        </p>
                    @endauth

                    {{-- Lista de Comentários --}}
                    <div class="comments-list"
                        style="max-height: 250px; overflow-y: auto; display: flex; flex-direction: column; gap: 12px;">
                        @forelse($selectedMeme->comments as $comment)
                            {{-- Comentário Principal --}}
                            <div
                                style="background: var(--surface); padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border);">
                                <div
                                    style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--muted); margin-bottom: 4px;">
                                    <strong style="color: var(--text);">{{ $comment->user->name }}</strong>
                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                <p style="margin: 0; font-size: 0.85rem; color: var(--text);">{{ $comment->content }}</p>

                                {{-- Lista de Respostas (Filhos) --}}
                                @if($comment->children->count() > 0)
                                    <div
                                        style="margin-top: 8px; padding-left: 12px; border-left: 2px solid var(--border); display: flex; flex-direction: column; gap: 6px;">
                                        @foreach($comment->children as $reply)
                                            <div style="font-size: 0.8rem;">
                                                <span style="font-weight: 600; color: var(--cyan);">{{ $reply->user->name }}</span>
                                                <span style="color: var(--muted); font-size: 0.7rem;">•
                                                    {{ $reply->created_at->diffForHumans() }}</span>
                                                <p style="margin: 2px 0 0 0; color: var(--text);">{{ $reply->content }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <p style="color: var(--muted); font-size: 0.82rem; text-align: center; margin: 10px 0;">
                                Nenhum comentário ainda. Seja o primeiro a comentar!
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($showReportModal)
        <div class="modal-backdrop"
            style="position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: flex; align-items: center; justify-content: center; z-index: 50;">
            <div class="modal-content"
                style="background: var(--surface); padding: 24px; border-radius: 12px; width: 100%; max-width: 450px; border: 1px solid var(--border);">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                    <h3 style="margin: 0; color: var(--text);">🚩 Reportar Meme</h3>
                    <button type="button" wire:click="closeReportModal"
                        style="background: none; border: none; color: var(--muted); cursor: pointer; font-size: 1.2rem;">✕</button>
                </div>

                <form wire:submit.prevent="submitReport">
                    <div style="margin-bottom: 14px;">
                        <label
                            style="display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 6px;">Motivo:</label>
                        <select wire:model="reportReason" class="field" style="width: 100%;">
                            <option value="inappropriate">Conteúdo Impróprio / Ofensivo</option>
                            <option value="duplicated">Conteúdo Duplicado</option>
                            <option value="offensive">Conteúdo Ofensivo</option>
                            <option value="spam">Spam ou Conteúdo Duplicado</option>
                            <option value="copyright">Violação de Direitos Autorais</option>
                            <option value="other">Outro Motivo</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label style="display: block; font-size: 0.85rem; color: var(--muted); margin-bottom: 6px;">Detalhes
                            adicionais (opcional):</label>
                        <textarea wire:model="reportDescription" class="field" rows="3"
                            placeholder="Explique o motivo da denúncia..."
                            style="width: 100%; resize: vertical;"></textarea>
                        @error('reportDescription')
                            <span style="color: var(--pink); font-size: 0.75rem;">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display: flex; gap: 8px; justify-content: flex-end;">
                        <button type="button" class="action" wire:click="closeReportModal">Cancelar</button>
                        <button type="submit" class="submit"
                            style="width: auto; padding: 0 16px; background: #ef4444;">Enviar Denúncia</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Toast de Notificação --}}
   <div class="toast" :class="{ 'show': showToast }" x-text="toastMessage"></div>
</div>