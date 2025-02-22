<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class ReportHelp extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?int $navigationSort = -100;

    protected static string $view = 'filament.pages.report-help';

    public function getTitle(): string|Htmlable
    {
        return __('admin.$report.$help.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('admin.$report.$help.title');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('admin.menu.report');
    }
}
