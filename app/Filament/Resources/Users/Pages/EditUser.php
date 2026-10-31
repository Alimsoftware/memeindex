<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['level'] = $this->record->level();

        return $data;
    }

    // só o nível é gravado; o resto do formulário é leitura
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $level = $data['level'] ?? null;

        abort_unless(array_key_exists($level, User::LEVELS), 422);
        abort_if($level === 'admin' && !auth()->user()->isOwner(), 403);

        return $this->record->attributesForLevel($level);
    }
}
