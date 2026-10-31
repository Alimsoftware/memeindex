@php $email = config('app.contact_email'); @endphp
<x-layouts.app title="Termos de uso - MemeIndex">
    <div class="page doc">
        <section class="hero">
            <p class="eyebrow">Termos</p>
            <h1>Termos de uso</h1>
            <p class="help">Última atualização: 31 de outubro de 2026</p>
        </section>

        <nav class="chips" aria-label="Nesta página">
            <a class="chip" href="#servico">O serviço</a>
            <a class="chip" href="#conta">Sua conta</a>
            <a class="chip" href="#conteudo">Seu conteúdo</a>
            <a class="chip" href="#proibido">O que é proibido</a>
            <a class="chip" href="#remocao">Remoção</a>
            <a class="chip" href="#responsabilidade">Responsabilidade</a>
        </nav>

        <section class="doc-section">
            <h2>Aceitação</h2>
            <p>Estes termos valem entre você e a <strong>Alimsoft</strong>, mantenedora do MemeIndex. Ao criar uma conta
                você declara que leu e aceita estes termos e a <a href="{{ route('privacy') }}">Política de
                    privacidade</a>. Se não concorda, não use o site.</p>
        </section>

        <section id="servico" class="doc-section">
            <h2>O serviço</h2>
            <p>O MemeIndex é um arquivo gratuito e pesquisável de memes. Qualquer pessoa pode buscar e baixar. Enviar,
                comentar, votar, favoritar e denunciar exigem conta.</p>
            <p>O site é oferecido como está. Podemos mudar, suspender ou encerrar funções a qualquer momento, e não
                garantimos que ele esteja sempre no ar ou livre de erros.</p>
        </section>

        <section id="conta" class="doc-section">
            <h2>Sua conta</h2>
            <ul>
                <li>É preciso ter <strong>16 anos ou mais</strong> para criar uma conta.</li>
                <li>Use um e-mail seu. A conta é pessoal e você responde pelo que for feito com ela.</li>
                <li>Guarde sua senha. Se alguém usar sua conta sem permissão, avise a gente.</li>
                <li>Seu nome pode ser um apelido, mas não pode se passar por outra pessoa nem ser ofensivo.</li>
            </ul>
        </section>

        <section id="conteudo" class="doc-section">
            <h2>O conteúdo que você envia</h2>
            <ul>
                <li>Ao enviar um meme ou escrever no chat, você declara que tem o direito de publicá-lo ou que o uso se
                    enquadra nas exceções da Lei de Direitos Autorais (Lei 9.610/1998), como paródia e citação.</li>
                <li>O conteúdo continua sendo de quem é dono dele. Você só concede ao MemeIndex uma licença gratuita, não
                    exclusiva e mundial para armazenar, exibir, redimensionar e distribuir o conteúdo no site e nos links de
                    compartilhamento, enquanto ele estiver publicado.</li>
                <li>Todo meme passa por moderação antes de aparecer. A moderação pode rejeitar ou remover qualquer envio,
                    por exemplo um duplicado ou fora do escopo do arquivo.</li>
                <li>Há limite de envios por dia. Envios rejeitados não contam.</li>
            </ul>
        </section>

        <section id="proibido" class="doc-section">
            <h2>O que é proibido</h2>
            <ul>
                <li>Conteúdo gore, de violência real ou de crueldade com animais.</li>
                <li>Nudez ou conteúdo sexual explícito, especialmente de pessoas que não consentiram.</li>
                <li>Qualquer conteúdo sexual envolvendo menores de idade. Será removido e comunicado às autoridades.</li>
                <li>Discurso de ódio, discriminação, ameaças ou assédio.</li>
                <li>Publicar dados pessoais de outras pessoas, como endereço, telefone ou documentos.</li>
                <li>Conteúdo que viole direitos autorais, de imagem ou a honra de alguém.</li>
                <li>Spam, golpes, links maliciosos ou automação para burlar os limites do site.</li>
                <li>Qualquer outro conteúdo ilegal.</li>
            </ul>
            <p>Quem descumprir estas regras pode ter o conteúdo removido e a conta suspensa ou excluída.</p>
        </section>

        <section id="remocao" class="doc-section">
            <h2>Denúncias e pedidos de remoção</h2>
            <p>Usuários logados podem denunciar um meme pelo botão <strong>Reportar</strong>. Quem não tem conta, como o
                titular de um direito autoral ou uma pessoa que aparece em um meme, pode pedir a remoção pelo e-mail
                @if($email)<a href="mailto:{{ $email }}">{{ $email }}</a>@else<em>de contato (ainda não definido)</em>@endif.
                Veja o que incluir no pedido na página <a href="{{ route('about') }}#dmca">Direitos autorais</a>.</p>
            <p>Analisamos cada pedido e removemos o conteúdo quando ele for procedente. Nudez ou conteúdo sexual divulgado
                sem consentimento é removido assim que formos avisados.</p>
        </section>

        <section id="responsabilidade" class="doc-section">
            <h2>Responsabilidade</h2>
            <p>Cada pessoa é responsável pelo que envia e escreve. O MemeIndex não cria nem endossa o conteúdo dos
                usuários e responde por ele nos limites do Marco Civil da Internet (Lei 12.965/2014). Colaboramos com
                ordens judiciais e com as autoridades.</p>
        </section>

        <section class="doc-section">
            <h2>Encerramento da conta</h2>
            <p>Você pode pedir a exclusão da sua conta a qualquer momento pelo e-mail de contato. Podemos encerrar contas
                que violem estes termos.</p>
        </section>

        <section class="doc-section">
            <h2>Lei aplicável</h2>
            <p>Estes termos seguem a lei brasileira. Conflitos serão resolvidos no foro do domicílio do usuário, quando
                ele for consumidor.</p>
        </section>

        <section class="doc-section">
            <h2>Mudanças nestes termos</h2>
            <p>Quando estes termos mudarem, a data no topo será atualizada. Mudanças importantes serão avisadas no site.</p>
        </section>
    </div>
</x-layouts.app>
