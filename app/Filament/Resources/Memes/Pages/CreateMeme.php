<?php

namespace App\Filament\Resources\Memes\Pages;

use App\Filament\Resources\Memes\MemeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeme extends CreateRecord
{
    protected static string $resource = MemeResource::class;
}
