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
            'category' => 'required|string|max:40',
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
        }
    }

    public function updatedImage(): void
    {
        $this->validateOnly('image');
    }

    public function save(): void
    {
        abort_unless(auth()->check(), 403);

        $data = $this->validate();

        $path = $this->image->store('memes', 'public');

        Meme::create([
            'user_id' => auth()->id(),
            'title' => trim($data['title']),
            // o slug é único: o sufixo evita colisão entre títulos iguais
            'slug' => Str::slug($data['title']) . '-' . Str::lower(Str::random(6)),
            'image_path' => $path,
            'description' => trim($data['description'] ?? '') ?: null,
            'category' => Str::slug($data['category']) ?: 'geral',
            'is_gif' => $this->image->getMimeType() === 'image/gif',
            'status' => 'pending',
        ]);

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
            'categories' => Meme::where('status', 'approved')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }
}
