<?php

namespace App\Filament\Resources\Memes;

use App\Filament\Resources\Memes\Pages\CreateMeme;
use App\Filament\Resources\Memes\Pages\EditMeme;
use App\Filament\Resources\Memes\Pages\ListMemes;
use App\Models\Meme;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Utilities\Set;

class MemeResource extends Resource
{
    protected static ?string $model = Meme::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, string $state, Set $set) =>
                     $operation === 'create' ? $set('slug', Str::slug($state)) : null
                    ),
                TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(Meme::class, 'slug', ignoreRecord: true),
                    //->disabled(fn (string $operation) => $operation === 'edit'),

                FileUpload::make('image_path')
                    ->label('Imagem do Meme')
                    ->image()
                    ->disk('public')
                    ->directory('memes')
                    ->visibility('public') // Marca permissão de leitura
                    ->required(),

                Select::make('status')
                    ->label('Status de Moderação')
                    ->options([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                    ])
                    ->default('pending')
                    ->required(),

                Select::make('user_id')
                    ->label('Autor')
                    ->relationship('user', 'name')
                    ->default(fn () => auth()->id())
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Imagem'),

                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('Autor')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'approved' => 'success',
                        'pending' => 'warning',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Criado em')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendente',
                        'approved' => 'Aprovado',
                        'rejected' => 'Rejeitado',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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