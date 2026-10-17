<div class="page auth">
    <section class="hero">
        <p class="eyebrow">Acesso</p>
        <h1>Criar conta</h1>
    </section>

    <form class="auth-card" wire:submit.prevent="register" novalidate>
        <div>
            <label class="label" for="name">Nome</label>
            <input id="name" class="field" type="text" wire:model="name" maxlength="80" autocomplete="nickname" autofocus>
            @error('name') <span class="field-error" role="alert">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="label" for="email">E-mail</label>
            <input id="email" class="field" type="email" wire:model="email" autocomplete="email">
            @error('email') <span class="field-error" role="alert">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="label" for="password">Senha</label>
            <input id="password" class="field" type="password" wire:model="password" autocomplete="new-password"
                aria-describedby="pw-help">
            <p id="pw-help" class="help">Pelo menos 8 caracteres.</p>
            @error('password') <span class="field-error" role="alert">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="label" for="password_confirmation">Confirmar senha</label>
            <input id="password_confirmation" class="field" type="password" wire:model="password_confirmation"
                autocomplete="new-password">
        </div>

        <button type="submit" class="btn-primary wide" wire:loading.attr="disabled" wire:target="register">
            <span wire:loading.remove wire:target="register">Criar conta</span>
            <span wire:loading wire:target="register">Criando…</span>
        </button>

        <p class="auth-alt">Já tem conta? <a href="{{ route('login') }}">Entrar</a></p>
    </form>
</div>
