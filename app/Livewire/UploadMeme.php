<?php

namespace App\Livewire;

use App\Models\Meme;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Enviar meme - MemeIndex')]
class UploadMeme extends Component
{
    use WithFileUploads;

    public $image = null;
    public string $title = '';
    public string $category = 'geral';
    public string $description = '';
    public bool $sent = false;

    protected function rules(): array
    {
        return [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
            'title' => 'required|string|min:3|max:200',
            'category' => 'required|in:' . implode(',', Meme::CATEGORIES),
            'description' => 'nullable|string|max:1000',
        ];
    }

    protected function messages(): array
    {
        return [
            'image.required' => 'Escolha uma imagem ou GIF.',
            'image.image' => 'O arquivo precisa ser uma imagem.',
            'image.mimes' => 'Formatos aceitos: JPG, PNG, GIF ou WEBP.',
            'image.max' => 'O arquivo passa de 5 MB.',
            'image.uploaded' => 'O envio falhou. O arquivo deve ter no máximo 5 MB.',
            'title.required' => 'Dê um título ao meme.',
            'title.min' => 'O título precisa de pelo menos 3 caracteres.',
            'title.max' => 'O título passa de 200 caracteres.',
            'category.required' => 'Informe uma categoria.',
            'description.max' => 'A descrição passa de 1000 caracteres.',
        ];
    }

    public function mount(): void
    {
        // depois de entrar/cadastrar, volta para cá
        if (!auth()->check()) {
            session()->put('url.intended', route('upload'));
            $this->redirectRoute('login');
        }
    }

    public function updatedImage(): void
    {
        $this->validateOnly('image');
    }

    public function save(): void
    {
        abort_unless(auth()->check(), 403);

        $user = auth()->user();

        // Limite de 5 memes por dia; rejeitados não contam
        $memesHoje = Meme::where('user_id', $user->id)
            ->where('status', '!=', 'rejected')
            ->whereDate('created_at', today())
            ->count();

        if ($memesHoje >= 5) {
            $this->addError('image', 'Você atingiu o limite de 5 envios por dia.');
            return;
        }

        $data = $this->validate();

        try {
            $path = $this->image->store('memes', 'b2');
        } catch (\Exception $e) {
            \Log::error('Falha no upload B2: ' . $e->getMessage());
            $this->addError('image', 'Falha ao enviar a imagem. Tente novamente.');
            return;
        }

        $slug = Str::slug($data['title']) ?: 'meme';
        $canPostDirectly = $user->canPostDirectly();

        Meme::create([
            'user_id' => $user->id,
            'title' => trim($data['title']),
            // o slug é único: o sufixo evita colisão entre títulos iguais
            'slug' => $slug . '-' . Str::lower(Str::random(6)),
            'image_path' => $path,
            'description' => trim($data['description'] ?? '') ?: null,
            'category' => $data['category'],
            'is_gif' => $this->image?->getMimeType() === 'image/gif',
            'status' => $canPostDirectly ? 'approved' : 'pending',
            'published_at' => $canPostDirectly ? now() : null,
        ]);

        $user->increment('memes_submitted');

        $this->reset('image', 'title', 'description', 'category');
        $this->sent = true;
    }

    public function another(): void
    {
        $this->reset('image', 'title', 'description', 'sent');
        $this->category = 'geral';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.upload-meme', [
            'categories' => Meme::CATEGORIES,
        ]);
    }
}
