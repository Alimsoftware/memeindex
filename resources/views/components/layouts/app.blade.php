<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#05050a">
    <meta name="description" content="MemeIndex BR — índice pesquisável de memes, imagens e vídeos.">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'MemeIndex - O Seu Índice de Memes' }}</title>

    <!-- Tailwind CSS CDN (para testes rápidos) -->
    <!--script src="https://cdn.tailwindcss.com"></script-->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles

</head>

<body>
    <header class="site-header">
        <div class="header-inner">
            <a class="brand" href="#">MemeIndex</a>
            <nav class="nav" aria-label="Navegação">
                <a href="/">MemeIndex</a>
                <a href="/admin">Painel Admin</a>
                <a href="#sugestao">Sugerir</a>
                <a href="#footer">Contato</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="hero">
            <h1>MemeIndex BR</h1>
            <p>Seu índice pesquisável de memes, imagens e vídeos.</p>
        </section>
        {{ $slot }}
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