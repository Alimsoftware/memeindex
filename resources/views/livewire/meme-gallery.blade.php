<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Campo de Busca em Tempo Real --}}
    <div class="search-box">
        <span class="search-icon" aria-hidden="true">⌕</span>
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Buscar memes..." 
        />
        @if(!empty($search))
            <button 
                wire:click="$set('search', '')" 
                class="search-clear" 
                type="button" 
                aria-label="Limpar busca"
                style="display: block;"
            >
                ✕
            </button>
        @endif
    </div>

    {{-- Grid de Memes --}}
    <div id="grid" class="grid" aria-live="polite">
        @forelse($memes as $meme)
            <div class="card">
                <div class="media">
                   <img src="{{ url('/media/' . $meme->image_path) }}" alt="{{ $meme->title }}" />
                </div>
                
                <div class="card-body">
                    <h3 class="title line-clamp-1">{{ $meme->title }}</h3>
                    
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

    {{-- Links de Paginação --}}
    <div class="mt-8">
        {{ $memes->links() }}
    </div>
</div>