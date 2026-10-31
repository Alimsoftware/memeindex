@php $email = config('app.contact_email'); @endphp
<x-layouts.app title="Política de privacidade - MemeIndex">
    <div class="page doc">
        <section class="hero">
            <p class="eyebrow">Privacidade</p>
            <h1>Política de privacidade</h1>
            <p class="help">Última atualização: 31 de outubro de 2026</p>
        </section>

        <nav class="chips" aria-label="Nesta página">
            <a class="chip" href="#quem">Quem somos</a>
            <a class="chip" href="#dados">Dados coletados</a>
            <a class="chip" href="#uso">Uso e base legal</a>
            <a class="chip" href="#publico">O que é público</a>
            <a class="chip" href="#terceiros">Terceiros</a>
            <a class="chip" href="#cookies">Cookies</a>
            <a class="chip" href="#guarda">Guarda</a>
            <a class="chip" href="#direitos">Seus direitos</a>
        </nav>

        <section id="quem" class="doc-section">
            <h2>Quem somos</h2>
            <p>O MemeIndex é mantido pela <strong>Alimsoft</strong>, responsável (controladora) pelos dados pessoais
                tratados no site, nos termos da Lei Geral de Proteção de Dados (Lei 13.709/2018, LGPD).</p>
            <p>Canal de privacidade:
                @if($email)<a href="mailto:{{ $email }}">{{ $email }}</a>@else<em>e-mail ainda não definido</em>@endif.
            </p>
        </section>

        <section id="dados" class="doc-section">
            <h2>Dados que coletamos</h2>
            <h3>Quando você cria uma conta</h3>
            <ul>
                <li><strong>Nome</strong>: pode ser um apelido. Ele aparece publicamente.</li>
                <li><strong>E-mail</strong>: usado para entrar na conta. Nunca é exibido publicamente.</li>
                <li><strong>Senha</strong>: guardada só como hash criptográfico. Ninguém, nem a moderação, consegue lê-la.</li>
            </ul>
            <h3>Quando você usa o site</h3>
            <ul>
                <li>Memes que você envia (imagem, título, descrição e categoria) e o resultado da moderação.</li>
                <li>Mensagens que você escreve no chat dos memes.</li>
                <li>Votos, favoritos e compartilhamentos.</li>
                <li>Denúncias que você faz (motivo e detalhes), vistas apenas pela moderação.</li>
            </ul>
            <h3>Dados técnicos</h3>
            <ul>
                <li>Endereço IP e identificação do navegador (user-agent), guardados junto com a sua sessão de login.</li>
                <li>Endereço IP usado por pouco tempo para limitar tentativas de login, cadastro e envio.</li>
            </ul>
            <p class="notice">As imagens são guardadas como foram enviadas. Fotos tiradas com celular podem conter
                metadados, como o local onde foram feitas. Remova esses dados antes de enviar.</p>
            <p>Não pedimos CPF, telefone, endereço ou dados sensíveis. Não usamos ferramentas de análise de audiência,
                anúncios ou rastreadores.</p>
        </section>

        <section id="uso" class="doc-section">
            <h2>Para que usamos e com qual base legal</h2>
            <ul>
                <li><strong>Manter sua conta e as funções do site</strong> (enviar, comentar, votar, favoritar):
                    execução do contrato com você, ou seja, os <a href="{{ route('terms') }}">Termos de uso</a>
                    (LGPD, art. 7º, V).</li>
                <li><strong>Segurança, moderação e prevenção de abuso</strong> (limites de tentativas, análise de
                    denúncias): legítimo interesse (art. 7º, IX).</li>
                <li><strong>Atender ordens judiciais ou de autoridades</strong>: cumprimento de obrigação legal
                    (art. 7º, II).</li>
            </ul>
            <p>Não vendemos dados pessoais e não os usamos para publicidade.</p>
        </section>

        <section id="publico" class="doc-section">
            <h2>O que fica público</h2>
            <p>Qualquer visitante pode ver o seu nome, os memes seus que foram aprovados e as suas mensagens no chat.
                O seu e-mail, as suas denúncias e os seus envios rejeitados ou pendentes não são públicos.</p>
        </section>

        <section id="terceiros" class="doc-section">
            <h2>Com quem compartilhamos</h2>
            <p>Usamos alguns fornecedores para o site funcionar. Eles só recebem o necessário para prestar o serviço:</p>
            <ul>
                <li><strong>Backblaze (B2)</strong>: armazena as imagens dos memes.</li>
                <li><strong>Cloudflare</strong>: entrega o site e o protege contra ataques. Todo acesso passa por ela,
                    incluindo o seu IP.</li>
                <li><strong>Google Fonts</strong>: entrega as fontes do site. O Google recebe o seu IP ao carregar a
                    página.</li>
                <li><strong>Provedor de hospedagem</strong>: onde ficam o servidor e o banco de dados.</li>
            </ul>
            <p>Alguns desses fornecedores ficam fora do Brasil, principalmente nos Estados Unidos. Essa transferência
                internacional é necessária para o serviço funcionar (LGPD, art. 33).</p>
            <p>Também podemos fornecer dados a autoridades quando houver ordem judicial ou obrigação legal.</p>
        </section>

        <section id="cookies" class="doc-section">
            <h2>Cookies e armazenamento local</h2>
            <p>Usamos apenas o necessário para o site funcionar, por isso não pedimos consentimento:</p>
            <ul>
                <li>Cookie de sessão: mantém você logado enquanto navega.</li>
                <li>Cookie XSRF-TOKEN: protege os formulários contra envios falsos.</li>
                <li>Cookie "manter conectado": só existe se você marcar essa opção ao entrar.</li>
                <li>Cookies de segurança da Cloudflare: identificam tráfego automatizado.</li>
                <li>Armazenamento local do navegador (<code>chatTheme</code>): lembra o tema do chat que você escolheu.</li>
            </ul>
            <p>Não usamos cookies de rastreamento, análise ou publicidade.</p>
        </section>

        <section id="guarda" class="doc-section">
            <h2>Por quanto tempo guardamos</h2>
            <ul>
                <li>Dados da conta e o que você publicou: enquanto a conta existir.</li>
                <li>Ao excluir a conta, apagamos junto os seus memes, mensagens, votos, favoritos, compartilhamentos e
                    denúncias.</li>
                <li>Sessão de login: até você sair ou a sessão expirar.</li>
                <li>IP usado para limitar tentativas: até 1 hora.</li>
            </ul>
            <p>Podemos manter dados por mais tempo quando a lei exigir ou para nos defender em processo.</p>
        </section>

        <section id="direitos" class="doc-section">
            <h2>Seus direitos</h2>
            <p>Pela LGPD (art. 18), você pode pedir a qualquer momento:</p>
            <ul>
                <li>confirmação de que tratamos seus dados e acesso a eles;</li>
                <li>correção de dados incompletos ou errados;</li>
                <li>anonimização, bloqueio ou eliminação de dados desnecessários;</li>
                <li>portabilidade dos dados;</li>
                <li>informação sobre com quem compartilhamos;</li>
                <li>exclusão da conta e dos dados.</li>
            </ul>
            <p>Para exercer qualquer um deles, escreva para
                @if($email)<a href="mailto:{{ $email }}">{{ $email }}</a>@else<em>o e-mail de privacidade (ainda não definido)</em>@endif
                usando o e-mail da sua conta. Respondemos em até 15 dias. Você também pode reclamar à Autoridade Nacional
                de Proteção de Dados (ANPD).</p>
        </section>

        <section class="doc-section">
            <h2>Idade mínima</h2>
            <p>O MemeIndex não é destinado a menores de 16 anos, que não devem criar conta.</p>
        </section>

        <section class="doc-section">
            <h2>Segurança</h2>
            <p>O site usa conexão criptografada (HTTPS), guarda senhas apenas como hash e restringe o acesso aos dados à
                moderação, na medida da função de cada pessoa. Nenhum sistema é totalmente seguro: se houver um incidente
                que possa causar risco a você, avisaremos.</p>
        </section>

        <section class="doc-section">
            <h2>Mudanças nesta política</h2>
            <p>Quando esta política mudar, a data no topo será atualizada. Mudanças importantes serão avisadas no site.</p>
        </section>
    </div>
</x-layouts.app>
