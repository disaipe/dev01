<?php

namespace App\Filament\Pages;

use App\Facades\Config;
use App\Filament\Components\ReferenceSelect;
use App\Forms\Components\RawHtmlContent;
use App\Models\Service;
use App\Utils\ReferenceUtils;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ReportSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'filament.pages.report-settings';

    protected static ?int $navigationSort = PHP_INT_MAX;

    public ?array $data;

    public function getTitle(): string|Htmlable
    {
        return __('admin.$report.$settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.$report.$settings.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.report');
    }

    public static function getNavigationIcon(): string|Htmlable|null
    {
        return 'heroicon-o-table-cells';
    }

    public function mount(): void
    {
        $options = config('report');

        if ($options) {
            $options = json_decode($options, true);
        }

        $this->form->fill($options);
    }

    public function save(): void
    {
        $form = $this->getForm('form');
        $state = $form->getState();

        Config::set('report', json_encode($state));
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make()->tabs([
                Tabs\Tab::make(__('admin.$report.report details'))->schema([
                    $this->getColumnExcludeSection(),
                ]),

                Tabs\Tab::make(__('admin.$report.merging'))->schema([
                   $this->getMergingSection(),
                ]),
            ]),
        ];
    }

    protected function getColumnExcludeSection(): Section
    {
        return Section::make(__('admin.$report.$settings.$detailed.excluded fields'))
            ->description(__('admin.$report.$settings.$detailed.excluded fields help'))
            ->schema([
                Section::make(__('admin.$report.$settings.$detailed.global header'))
                    ->icon('heroicon-o-globe-alt')
                    ->schema([
                        RawHtmlContent::make(new HtmlString(__(
                            'admin.$report.$settings.$detailed.common excluded fields help'
                        ))),

                        Repeater::make('fields.exclude')
                            ->hiddenLabel()
                            ->addActionLabel(__('admin.add'))
                            ->reorderable(false)
                            ->simple(TextInput::make('value')->required()),
                    ]),

                Section::make(__('admin.$report.$settings.$detailed.by reference header'))
                    ->icon('heroicon-o-document-check')
                    ->schema([
                        RawHtmlContent::make(new HtmlString(__(
                            'admin.$report.$settings.$detailed.excluded fields by reference'
                        ))),

                        Repeater::make('fields.references.exclude')
                            ->hiddenLabel()
                            ->addActionLabel(__('admin.add'))
                            ->collapsible(false)
                            ->columns()
                            ->schema([
                                ReferenceSelect::make('reference')
                                    ->reactive(),

                                Select::make('fields')
                                    ->label(trans_choice('admin.field', 2))
                                    ->multiple()
                                    ->options(function (Get $get) {
                                        $referenceName = $get('reference');

                                        if ($referenceName) {
                                            return ReferenceUtils::getReferenceFieldsOptions($referenceName);
                                        }

                                        return [];
                                    }),
                            ]),
                    ]),
            ]);
    }

    protected function getMergingSection(): Section
    {
        $serviceOptions = Service::all()->pluck('name', 'id');

        return Section::make(__(''))
            ->statePath('merging')
            ->schema(([
                Toggle::make('enabled')
                    ->label(__('admin.$report.$merging.merge label'))
                    ->helperText(__('admin.$report.$merging.merge help'))
                    ->afterStateHydrated(function (Toggle $comp) {
                        if ($comp->getState() === null) {
                            $comp->state(true);
                        }
                    })
                    ->default(true),

                Repeater::make('groups')
                    ->label(__('admin.$report.$merging.merging groups label'))
                    ->addActionLabel(__('admin.$report.$merging.merging groups add label'))
                    ->persistCollapsed()

                    ->schema([
                        Select::make('service')
                            ->label(trans_choice('reference.Service', 2))
                            ->options($serviceOptions)
                            ->searchable()
                            ->multiple(),

                        TextInput::make('merged_name')
                            ->label(__('admin.$report.$merging.merged name label'))
                            ->helperText(__('admin.$report.$merging.merged name help'))
                            ->required(),
                    ]),
            ]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(__('admin.save'))
                ->action('save'),
        ];
    }
}
