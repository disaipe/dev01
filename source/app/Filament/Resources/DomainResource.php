<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DomainResource\Pages;
use App\Forms\Components\MarkdownContent;
use App\Models\Domain;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('form')
                    ->columnSpanFull()
                    ->tabs([
                        Forms\Components\Tabs\Tab::make(__('admin.configuration'))
                            ->columns(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label(__('admin.name'))
                                    ->helperText(__('admin.$domain.name_helper'))
                                    ->maxLength(64)
                                    ->required(),

                                Forms\Components\TextInput::make('code')
                                    ->label(__('admin.identity'))
                                    ->regex('/[a-zA-Z_-]/')
                                    ->helperText(__('admin.$domain.code_helper'))
                                    ->maxLength(8)
                                    ->required(),

                                Forms\Components\TextInput::make('host')
                                    ->label(__('admin.host'))
                                    ->maxLength(64)
                                    ->required(),

                                Forms\Components\TextInput::make('port')
                                    ->label(__('admin.port'))
                                    ->numeric()
                                    ->default(389)
                                    ->required(),

                                Forms\Components\TextInput::make('username')
                                    ->label(__('admin.$domain.username')),

                                Forms\Components\TextInput::make('password')
                                    ->label(__('admin.password'))
                                    ->password(),

                                Forms\Components\TextInput::make('base_dn')
                                    ->label(__('admin.$domain.base_dn'))
                                    ->helperText(__('admin.$domain.base_dn_helper'))
                                    ->columnSpanFull(),

                                Forms\Components\Repeater::make('filters')
                                    ->label(__('admin.$domain.filters'))
                                    ->columnSpanFull()
                                    ->createItemButtonLabel(__('admin.$domain.filter_add'))
                                    ->schema([
                                        Forms\Components\TextInput::make('value')
                                            ->label(__('admin.$domain.rule'))
                                            ->helperText(__('admin.$domain.rule_helper'))
                                            ->required(),

                                        Forms\Components\Textarea::make('name')
                                            ->label(__('admin.description'))
                                            ->rows(2),
                                    ]),

                                Forms\Components\TextInput::make('timeout')
                                    ->label(__('admin.timeout'))
                                    ->numeric()
                                    ->default(5),

                                Forms\Components\Checkbox::make('ssl')
                                    ->label('SSL')
                                    ->default(false)
                                    ->columnSpanFull(),

                                Forms\Components\Checkbox::make('tls')
                                    ->label('TLS')
                                    ->default(false)
                                    ->columnSpanFull(),

                                Forms\Components\Checkbox::make('enabled')
                                    ->label(__('admin.enabled'))
                                    ->default(false)
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make(__('admin.description'))
                            ->schema([
                                MarkdownContent::make('')
                                    ->fromFile(base_path('docs/domain/README.md')),
                            ]),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name')),

                Tables\Columns\TextColumn::make('code')
                    ->label(__('admin.identity')),

                Tables\Columns\CheckboxColumn::make('enabled')
                    ->label(__('admin.enabled'))
                    ->disabled(),
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
            'index' => Pages\ListDomains::route('/'),
            'create' => Pages\CreateDomain::route('/create'),
            'edit' => Pages\EditDomain::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return trans_choice('admin.domain', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('admin.domain', 2);
    }

    protected static function getNavigationGroup(): ?string
    {
        return __('admin.menu.access');
    }
}
