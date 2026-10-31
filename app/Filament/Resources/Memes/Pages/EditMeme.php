<?php

namespace App\Filament\Resources\Memes\Pages;

use App\Filament\Resources\Memes\MemeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMeme extends EditRecord
{
    protected static string $resource = MemeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['image_path'])) {
            $data['is_gif'] = str_ends_with(strtolower($data['image_path']), '.gif');
        }

        if ($data['status'] === 'approved' && !$this->record->published_at) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
