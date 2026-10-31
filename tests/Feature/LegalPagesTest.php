<?php

namespace Tests\Feature;

use App\Livewire\Auth\Register;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_and_terms_pages_load(): void
    {
        $this->get('/privacidade')->assertOk()->assertSee('Política de privacidade');
        $this->get('/termos')->assertOk()->assertSee('Termos de uso');
    }

    public function test_register_requires_accepting_terms(): void
    {
        $form = Livewire::test(Register::class)
            ->set('name', 'Teste')
            ->set('email', 'teste@example.com')
            ->set('password', 'senha-segura')
            ->set('password_confirmation', 'senha-segura');

        $form->call('register')->assertHasErrors(['terms' => 'accepted']);
        $this->assertDatabaseMissing('users', ['email' => 'teste@example.com']);

        $form->set('terms', true)->call('register')->assertHasNoErrors();
        $this->assertTrue(User::where('email', 'teste@example.com')->exists());
    }
}
