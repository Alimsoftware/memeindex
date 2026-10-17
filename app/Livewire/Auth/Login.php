<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Entrar - MemeIndex')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
        ];
    }

    protected function messages(): array
    {
        return [
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Esse e-mail não parece válido.',
            'password.required' => 'Informe sua senha.',
        ];
    }

    public function login()
    {
        $this->validate();

        $key = Str::lower($this->email) . '|' . request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => "Muitas tentativas. Tente de novo em {$seconds} segundos.",
            ]);
        }

        if (!Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($key, 60);
            // mesma mensagem para e-mail inexistente e senha errada: não revela quais e-mails existem
            throw ValidationException::withMessages(['email' => 'E-mail ou senha incorretos.']);
        }

        RateLimiter::clear($key);
        session()->regenerate();

        return $this->redirectIntended(default: route('home'));
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
