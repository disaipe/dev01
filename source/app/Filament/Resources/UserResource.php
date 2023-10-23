<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.$user.name'))
                    ->helperText(__('admin.$user.name help'))
                    ->columnSpanFull()
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->label(__('admin.email'))
                    ->helperText(__('admin.$user.email help'))
                    ->email()
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->label(__('admin.password'))
                    ->helperText(fn ($record) => $record ? __('admin.$user.password help') : null)
                    ->password()
                    ->required(fn ($record) => ! $record),
                Forms\Components\TextInput::make('domain')
                    ->label(trans_choice('admin.domain', 1))
                    ->helperText(__('admin.$user.domain help'))
                    ->visible(fn ($state) => (bool) $state)
                    ->disabled(),
                Forms\Components\Select::make('roles')
                    ->label(trans_choice('admin.role', 2))
                    ->relationship('roles', 'name')
                    ->multiple(),

                Forms\Components\Select::make('companies')
                    ->label(trans_choice('reference.Company', 2))
                    ->relationship('companies', 'name')
                    ->multiple(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.$user.name')),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('admin.email')),
                Tables\Columns\TextColumn::make('domain')
                    ->label(trans_choice('admin.domain', 1)),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return trans_choice('admin.user', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('admin.user', 2);
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.access');
    }
}
