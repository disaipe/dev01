<?php

namespace App\Filament\Resources;

use App\Core\Enums\Visibility;
use App\Filament\Resources\ServiceResource\Pages;
use App\Forms\Components\Alert;
use App\Models\Indicator;
use App\Models\Service;
use App\Utils\ReferenceUtils;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        $indicators = Indicator::query()->with('group')->get();

        $indicatorGroups = $indicators
            ->groupBy(function (Indicator $item) {
                return $item->group?->name ?? 'Без группы';
            })
            ->mapWithKeys(function (Collection $indicators, string $group) {
                return [$group => Arr::pluck($indicators, 'name', 'code')];
            });

        return $form
            ->schema([
                /**
                 * SECTION "COMMON"
                 */
                Forms\Components\Section::make(__('admin.$service.common'))
                    ->icon('heroicon-o-bars-3')
                    ->columns()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label(__('admin.name'))
                            ->helperText(__('admin.$service.name help'))
                            ->maxLength(128)
                            ->required(),

                        Forms\Components\Select::make('service_provider_id')
                            ->label(trans_choice('reference.ServiceProvider', 1))
                            ->relationship('service_provider', 'name')
                            ->required(),

                        Forms\Components\TextInput::make('display_name')
                            ->label(__('admin.display name'))
                            ->helperText(__('admin.$service.display name help'))
                            ->maxLength(512)
                            ->columnSpan(2),

                        Forms\Components\Select::make('indicator_code')
                            ->label(trans_choice('reference.Indicator', 1))
                            ->helperText(__('admin.$service.indicator help'))
                            ->options($indicatorGroups)
                            ->columnSpan(2)
                            ->searchable()
                            ->required(),
                    ]),

                /**
                 * SECTION "REPORT"
                 */
                Forms\Components\Section::make(__('admin.$service.report'))
                    ->icon('heroicon-o-table-cells')
                    ->statePath('options.report')
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        Forms\Components\Toggle::make('enabled')
                            ->label(__('admin.$service.$report.enabled label'))
                            ->helperText(__('admin.$service.$report.enabled help'))
                            ->afterStateHydrated(function (Forms\Components\Toggle $comp) {
                                if ($comp->getState() === null) {
                                    $comp->state(true);
                                }
                            })
                            ->default(true)
                            ->reactive(),

                        Forms\Components\Group::make([
                            Alert::make(new HtmlString(__('admin.$service.$report.global settings alert')))
                                ->warn(),

                            Forms\Components\Select::make('visibility')
                                ->label(__('admin.$service.$report.visibility by default'))
                                ->options([
                                    Visibility::Visible->value => __('admin.$service.$report.$visibility.visible'),
                                    Visibility::Hidden->value => __('admin.$service.$report.$visibility.hidden'),
                                ])
                                ->default('visible')
                                ->selectablePlaceholder(false),

                            Forms\Components\Repeater::make('fields')
                                ->label(__('admin.$service.$report.fields'))
                                ->addActionLabel(__('admin.$service.$report.add field'))
                                ->itemLabel(fn (array $state): ?string => $state['field'] ?? null)
                                ->columns()
                                ->schema([
                                    Forms\Components\Select::make('field')
                                        ->label(trans_choice('admin.field', 1))
                                        ->options(function (Get $get) {
                                            $indicatorCode = $get('../../../../indicator_code');

                                            if ($indicatorCode) {
                                                return ReferenceUtils::getIndicatorReferenceFieldsOptions($indicatorCode);
                                            }

                                            return [];
                                        }),

                                    Forms\Components\Toggle::make('visible')
                                        ->label(__('admin.$service.$report.visibility'))
                                        ->inline(false)
                                        ->default(true),
                                ]),
                        ])
                            ->visible(fn (Get $get) => $get('enabled') !== false)

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->searchable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('admin.name'))
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('service_provider.name')
                    ->label(trans_choice('reference.ServiceProvider', 1))
                    ->wrap()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.report');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('reference.Service', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('reference.Service', 2);
    }
}
