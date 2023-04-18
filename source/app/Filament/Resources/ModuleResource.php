<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ModuleResource\Pages;
use App\Filament\Tables\Columns\ModuleNameColumn;
use App\Models\Module;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Database\Eloquent\Model;

class ModuleResource extends Resource
{
    protected static ?string $model = Module::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ModuleNameColumn::make('name')
                    ->label(__('admin.name')),
                Tables\Columns\CheckboxColumn::make('enabled')
                    ->label(__('admin.enabled'))
                    ->toggle(),
                Tables\Columns\CheckboxColumn::make('system')
                    ->label(__('admin.system'))
                    ->disabled(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModules::route('/'),
            // 'create' => Pages\CreateModule::route('/create'),
            'edit' => Pages\EditModule::route('/{record}/edit'),
        ];
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('admin.menu.common');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('admin.module', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('admin.module', 2);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
