<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Criar conta - MemeIndex')]
class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|min:2|max:80',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:100|confirmed',
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'Informe seu nome.',
            'name.min' => 'O nome precisa de pelo menos 2 caracteres.',
            'email.required' => 'Informe seu e-mail.',
            'email.email' => 'Esse e-mail não parece válido.',
            'email.unique' => 'Já existe uma conta com esse e-mail.',
            'password.required' => 'Crie uma senha.',
            'password.min' => 'A senha precisa de pelo menos 8 caracteres.',
            'password.confirmed' => 'A confirmação não é igual à senha.',
        ];
    }

    public function register()
    {
        $this->email = Str::lower(trim($this->email));
        $this->name = trim($this->name);
        $this->validate();

        $key = 'register|' . request()->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Muitos cadastros deste endereço. Tente mais tarde.',
            ]);
        }

        // só name/email/password: o papel (role) nunca vem do formulário
        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ]);
        RateLimiter::hit($key, 3600);

        Auth::login($user);
        session()->regenerate();

        return $this->redirectIntended(default: route('home'));
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
