<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CompanyResource\Pages;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class CompanyResource extends Resource
{
    protected static ?string $model = Company::class;

    protected static ?string $navigationIcon = 'heroicon-o-home-modern';

    protected static ?int $navigationSort = -110;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(__('admin.name'))
                    ->helperText(new HtmlString('Наименование организации'))
                    ->maxLength(256)
                    ->required(),

                Forms\Components\TextInput::make('code')
                    ->label(__('reference.$company.code.label'))
                    ->helperText(new HtmlString(__('reference.$company.code.helper')))
                    ->maxLength(16)
                    ->required(),

                Forms\Components\TextInput::make('identity')
                    ->label(__('reference.$company.identity.label'))
                    ->maxLength(32),

                Forms\Components\TextInput::make('fullname')
                    ->label(__('reference.$company.fullname.label'))
                    ->maxLength(512)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('description')
                    ->label(__('reference.$company.description.label'))
                    ->columnSpanFull(),

                Forms\Components\Select::make('users')
                    ->label(__('reference.$company.users.label'))
                    ->helperText(new HtmlString(__('reference.$company.users.helper')))
                    ->relationship('users', 'name')
                    ->columnSpanFull()
                    ->preload()
                    ->multiple(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name')),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.code')),

                Tables\Columns\TextColumn::make('identity')
                    ->label(__('admin.identity')),

                Tables\Columns\TextColumn::make('users')
                    ->label(trans_choice('admin.user', 2))
                    ->state(function (Company $company) {
                        return $company->users()->count();
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCompanies::route('/'),
            'create' => Pages\CreateCompany::route('/create'),
            'edit' => Pages\EditCompany::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.common');
    }

    public static function getLabel(): ?string
    {
        return trans_choice('reference.Company', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('reference.Company', 2);
    }
}
