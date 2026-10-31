<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $modelLabel = 'usuário';

    protected static ?string $pluralModelLabel = 'usuários';

    protected static ?string $recordTitleAttribute = 'name';

    // mod plus entra no painel para editar posts, mas não vê usuários
    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user !== null && ($user->role === 'admin' || $user->isOwner());
    }

    // usuários se cadastram pelo site
    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return (bool) auth()->user()?->canManage($record);
    }

    public static function canDelete(Model $record): bool
    {
        return (bool) auth()->user()?->isOwner();
    }

    public static function canDeleteAny(): bool
    {
        return (bool) auth()->user()?->isOwner();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nome')
                    ->disabled(),

                TextInput::make('email')
                    ->label('E-mail')
                    ->disabled(),

                TextInput::make('xp_points')
                    ->label('XP')
                    ->disabled(),

                Select::make('level')
                    ->label('Nível')
                    // só o dono promove a admin
                    ->options(fn() => auth()->user()?->isOwner()
                        ? User::LEVELS
                        : array_diff_key(User::LEVELS, ['admin' => true]))
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                TextColumn::make('level')
                    ->label('Nível')
                    ->getStateUsing(fn(User $record) => User::LEVELS[$record->level()])
                    ->badge(),

                TextColumn::make('xp_points')
                    ->label('XP')
                    ->sortable(),

                TextColumn::make('memes_submitted')
                    ->label('Envios')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Cadastro')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->defaultSort('xp_points', 'desc')
            ->actions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
