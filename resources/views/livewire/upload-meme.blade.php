<div class="page upload">
    <section class="hero">
        <p class="eyebrow">Contribua com o arquivo</p>
        <h1>Enviar meme</h1>
    </section>

    @guest
        <div class="state">
            <p class="state-code">Acesso restrito</p>
            <p class="state-title">Entre para enviar memes.</p>
            <p class="state-text">Só usuários logados podem sugerir memes para o índice.</p>
            <div class="state-actions">
                <a class="btn-primary" href="{{ route('login') }}">Entrar</a>
                <a class="btn-ghost" href="{{ route('register') }}">Criar conta</a>
            </div>
        </div>
    @else
        @if($sent)
            <div class="state success" role="status">
                <p class="state-code ok">Envio concluído</p>
                <p class="state-title">Meme enviado!</p>
                <p class="state-text">Ele entrou na fila de aprovação e aparece no índice assim que for revisado.</p>
                <div class="state-actions">
                    <button type="button" class="btn-primary" wire:click="another">Enviar outro</button>
                    <a class="btn-ghost" href="{{ route('home') }}">Voltar ao índice</a>
                </div>
            </div>
        @else
            <form class="upload-grid" wire:submit.prevent="save" novalidate>
                <div>
                    <div class="dropzone" x-data="{ over: false, up: false, progress: 0 }" :class="{ 'over': over }"
                        @dragover.prevent="over = true" @dragleave.prevent="over = false" @drop="over = false"
                        x-on:livewire-upload-start="up = true; progress = 0"
                        x-on:livewire-upload-finish="up = false"
                        x-on:livewire-upload-error="up = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress">
                        <input type="file" id="image" class="dz-input" wire:model="image"
                            accept="image/png,image/jpeg,image/gif,image/webp" aria-label="Escolher imagem ou GIF"
                            aria-describedby="image-help">

                        @if($image && !$errors->has('image') && $image->isPreviewable())
                            <img class="dz-preview" src="{{ $image->temporaryUrl() }}" alt="Pré-visualização do meme">
                            <span class="dz-change">Clique ou arraste para trocar</span>
                        @else
                            <div class="dz-empty">
                                <svg viewBox="0 0 24 24" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 16V4M7 9l5-5 5 5" /><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3" /></svg>
                                <p class="dz-title">Arraste o meme aqui</p>
                                <p class="dz-sub">ou clique para escolher</p>
                            </div>
                        @endif

                        <div class="dz-progress" x-show="up" x-cloak><span :style="`width: ${progress}%`"></span></div>
                    </div>
                    <p id="image-help" class="help">JPG, PNG, GIF ou WEBP, até 5 MB.</p>
                    @error('image') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="upload-fields">
                    <div>
                        <label class="label" for="title">Título</label>
                        <input id="title" class="field" type="text" wire:model="title" maxlength="200"
                            placeholder="Ex.: Gato de terno na segunda-feira" autocomplete="off">
                        @error('title') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label" for="category">Categoria</label>
                        <input id="category" class="field" type="text" wire:model="category" maxlength="40" list="cats"
                            autocomplete="off">
                        <datalist id="cats">
                            @foreach($categories as $cat)<option value="{{ $cat }}">@endforeach
                        </datalist>
                        @error('category') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="label" for="description">Descrição <span class="opt">(opcional)</span></label>
                        <textarea id="description" class="field" rows="4" wire:model="description" maxlength="1000"
                            placeholder="De onde vem, contexto, ano aproximado..."></textarea>
                        @error('description') <span class="field-error">{{ $message }}</span> @enderror
                    </div>

                    <p class="notice">Todo meme passa por aprovação antes de aparecer no índice.</p>

                    <button type="submit" class="btn-primary wide" wire:loading.attr="disabled" wire:target="save,image">
                        <span wire:loading.remove wire:target="save">Enviar para aprovação</span>
                        <span wire:loading wire:target="save">Enviando…</span>
                    </button>
                </div>
            </form>
        @endif
    @endguest
</div>
