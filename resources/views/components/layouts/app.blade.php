<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#05050a">
    <meta name="description" content="MemeIndex BR — índice pesquisável de memes.">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MemeIndex - O Seu Índice de Memes' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="#">MemeIndex</a>
            <nav class="nav" aria-label="Navegação">
                <a href="/">Inicio</a>
                <!--a href="/admin">Painel Admin</a-->
                <a href="#sugestao">Sugerir</a>
                <a href="#footer">Contato</a>
            </nav>
        </div>
    </header>

    <main>

        {{ $slot }}

    </main>

    @livewireScripts
    <footer id="footer">
        <div>
            <a href="https://twitter.com/SrAlisonMDL" target="_blank" rel="noopener noreferrer">Twitter</a>
            <a href="https://www.facebook.com/SrAlisonMDL" target="_blank" rel="noopener noreferrer">Facebook</a>
            <a href="https://www.instagram.com/alison_mdl/" target="_blank" rel="noopener noreferrer">Instagram</a>
            <a href="https://github.com/alimsoftware" target="_blank" rel="noopener noreferrer">GitHub</a>
        </div>
        <p>© 2026 Alimsoft. Todos os direitos reservados.</p>
    </footer>
</body>

</html>