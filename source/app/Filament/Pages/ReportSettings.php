<?php

namespace App\Filament\Pages;

use App\Core\Reference\ReferenceFieldSchema;
use App\Core\Reference\ReferenceManager;
use App\Facades\Config;
use App\Filament\Components\ReferenceSelect;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Get;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class ReportSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.report-settings';

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
            ]),
        ];
    }

    protected function getColumnExcludeSection(): Section
    {
        return Section::make(__('admin.$report.$settings.$detailed.excluded fields'))
            ->description(__('admin.$report.$settings.$detailed.excluded fields help'))
            ->schema([
                Repeater::make('fields.exclude')
                    ->label(__('admin.common'))
                    ->helperText(new HtmlString(__('admin.$report.$settings.$detailed.common excluded fields help')))
                    ->addActionLabel(__('admin.add'))
                    ->reorderable(false)
                    ->simple(TextInput::make('value')->required()),

                Repeater::make('fields.references.exclude')
                    ->label(__('admin.$report.$settings.$detailed.excluded fields by reference'))
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

                                if (! $referenceName) {
                                    return [];
                                }

                                /** @var ReferenceManager $references */
                                $references = app('references');
                                $reference = $references->getByName($referenceName);

                                return collect($reference->getSchema())
                                    ->filter(fn (ReferenceFieldSchema $field) => ! $field->isHidden())
                                    ->mapWithKeys(fn (ReferenceFieldSchema $field, string $key) => [$key => $field->getLabel()])
                                    ->toArray();
                            }),
                    ]),
            ]);
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
