<?php

namespace App\Filament\Resources\Memes;

use App\Filament\Resources\Memes\Pages\CreateMeme;
use App\Filament\Resources\Memes\Pages\EditMeme;
use App\Filament\Resources\Memes\Pages\ListMemes;
use App\Filament\Resources\Memes\Schemas\MemeForm;
use App\Filament\Resources\Memes\Tables\MemesTable;
use App\Models\Meme;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemeResource extends Resource
{
    protected static ?string $model = Meme::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Form $form): Form
    {
        return $form->schema([Forms\Components\TextInput::make('title')->label('Title')->required()->maxLength(255), Forms\Components\FileUpload::make('image_path')->label('Imagem do Meme')->image()->directory('memes')->required(), Forms\Components\Select::make('status')->label('Status da moderação')->options(['pending' => 'Pendente', 'approved' => 'Aprovado', 'rejected' => 'Rejeitado'])->default('pending')->required(), Forms\Components\Select::make('user_id')->label('Autor')->relationship('user', 'name')->required()]);
    }

    public static function table(Table $table): Table
    {
        return MemesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemes::route('/'),
            'create' => CreateMeme::route('/create'),
            'edit' => EditMeme::route('/{record}/edit'),
        ];
    }
}
