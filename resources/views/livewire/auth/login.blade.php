<div class="page auth">
    <section class="hero">
        <p class="eyebrow">Acesso</p>
        <h1>Entrar</h1>
    </section>

    <form class="auth-card" wire:submit.prevent="login" novalidate>
        <div>
            <label class="label" for="email">E-mail</label>
            <input id="email" class="field" type="email" wire:model="email" autocomplete="email" autofocus>
            @error('email') <span class="field-error" role="alert">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="label" for="password">Senha</label>
            <input id="password" class="field" type="password" wire:model="password" autocomplete="current-password">
            @error('password') <span class="field-error" role="alert">{{ $message }}</span> @enderror
        </div>

        <label class="check">
            <input type="checkbox" wire:model="remember"> Manter conectado
        </label>

        <button type="submit" class="btn-primary wide" wire:loading.attr="disabled" wire:target="login">
            <span wire:loading.remove wire:target="login">Entrar</span>
            <span wire:loading wire:target="login">Entrando…</span>
        </button>

        <p class="auth-alt">Ainda não tem conta? <a href="{{ route('register') }}">Criar conta</a></p>
    </form>
</div>
