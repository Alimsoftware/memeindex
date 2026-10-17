<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0A0A0F">
    <meta name="description" content="MemeIndex BR — arquivo pesquisável de memes antigos, brasileiros e de nicho.">
    <title>{{ $title ?? 'MemeIndex - O Seu Índice de Memes' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body>
    <a class="skip-link" href="#conteudo">Pular para o conteúdo</a>

    <header class="site-header" x-data="{
            q: new URLSearchParams(location.search).get('q') || '',
            focused: false,
            i: 0,
            hints: ['cachorro caramelo forró', 'gato de terno segunda-feira', 'tiozão do zap bom dia', 'meme de 2012 orkut'],
            timer: null,
            // sem rotação automática para quem pede menos movimento
            init() { if (!matchMedia('(prefers-reduced-motion: reduce)').matches) this.timer = setInterval(() => this.i = (this.i + 1) % this.hints.length, 3500); },
            // na Home filtra ao vivo; nas outras páginas, só o Enter leva para a Home com a busca
            send(go = false) {
                if (location.pathname === '/') Livewire.dispatch('search-changed', { value: this.q });
                else if (go) location.href = '/?q=' + encodeURIComponent(this.q);
            }
        }" @search-cleared.window="q = ''"
        @keydown.window="if ($event.key === '/' && !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) { $event.preventDefault(); $refs.q.focus() }">
        <div class="header-inner">
            <a class="brand" href="/"><span data-text="MEME">MEME</span> INDEX</a>

            <form class="header-search" role="search" @submit.prevent="send(true)">
                <svg class="search-ico" viewBox="0 0 24 24" aria-hidden="true" width="18" height="18" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round">
                    <circle cx="11" cy="11" r="7" />
                    <path d="M21 21l-4.3-4.3" />
                </svg>
                <input x-ref="q" type="search" x-model="q" @input.debounce.300ms="send()" @focus="focused = true"
                    @blur="focused = false" aria-label="Buscar memes"
                    :placeholder="focused ? 'Buscar memes...' : hints[i]" maxlength="100" autocomplete="off">
                <kbd class="search-key" aria-hidden="true" x-show="!q && !focused">/</kbd>
            </form>

            <nav class="nav" aria-label="Navegação">
                <a class="btn-primary" href="{{ route('upload') }}">Enviar<span class="hide-xs">&nbsp;meme</span></a>
                @auth
                    <div class="user-menu" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
                        <button type="button" class="user-chip" @click="open = !open" :aria-expanded="open" aria-haspopup="menu"
                            aria-label="Menu da conta">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</button>
                        <div class="menu" x-show="open" x-cloak role="menu">
                            <p class="menu-name">{{ auth()->user()->name }}<small>{{ auth()->user()->email }}</small></p>
                            <a href="{{ route('profile') }}" role="menuitem">Meu perfil</a>
                            @if(in_array(auth()->user()->role, ['moderator', 'admin'], true))
                                <a href="{{ route('moderation') }}" role="menuitem">Moderação</a>
                            @endif
                            @if(auth()->user()->canAccessPanel(\Filament\Facades\Filament::getPanel('admin')))
                                <a href="/admin" role="menuitem">Painel admin</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" role="menuitem">Sair</button>
                            </form>
                        </div>
                    </div>
                @else
                    <a class="btn-ghost" href="{{ route('login') }}">Entrar</a>
                @endauth
            </nav>
        </div>
    </header>

    <main id="conteudo">
        {{ $slot }}
    </main>

    @livewireScripts
    <footer id="footer">
        <div class="footer-links">
            <a href="{{ route('about') }}">Sobre</a>
            <a href="{{ route('about') }}#dmca">DMCA</a>
            <a href="https://twitter.com/SrAlisonMDL" target="_blank" rel="noopener noreferrer">Twitter</a>
            <a href="https://www.facebook.com/SrAlisonMDL" target="_blank" rel="noopener noreferrer">Facebook</a>
            <a href="https://www.instagram.com/alison_mdl/" target="_blank" rel="noopener noreferrer">Instagram</a>
            <a href="https://github.com/alimsoftware" target="_blank" rel="noopener noreferrer">GitHub</a>
        </div>
        <p>© 2026 Alimsoft. Todos os direitos reservados.</p>
    </footer>
</body>

</html>
