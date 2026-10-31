<?php

namespace App\Filament\Resources\Memes\Pages;

use App\Filament\Resources\Memes\MemeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeme extends CreateRecord
{
    protected static string $resource = MemeResource::class;
    // No CreateMeme.php (page)
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_gif'] = str_ends_with(strtolower($data['image_path']), '.gif');

        if ($data['status'] === 'approved') {
            $data['published_at'] = now();
        }

        return $data;
    }
}
