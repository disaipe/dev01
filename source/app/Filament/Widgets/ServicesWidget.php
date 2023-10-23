<?php

namespace App\Filament\Widgets;

use App\Services\SystemService;
use Filament\Widgets\Widget;

class ServicesWidget extends Widget
{
    protected static string $view = 'filament.widgets.services-widget';

    protected SystemService $systemService;

    protected array $services = [
        'laravel-queue:*' => 'admin.$service.queue',
    ];

    public function __construct()
    {
        $this->systemService = new SystemService();
    }

    protected function getViewData(): array
    {
        return [
            'title' => __('admin.$service.widget.title'),
            'subheading' => __('admin.$service.widget.description'),
            'columns' => [
                'name' => __('admin.$service.widget.name'),
                'status' => __('admin.$service.widget.status'),
                'actions' => __('admin.$service.widget.actions'),
            ],
            'rows' => $this->getRows(),
        ];
    }

    protected function getRows(): array
    {
        $rows = [];

        foreach ($this->services as $service => $label) {
            $status = $this->systemService->serviceStatus($service);
            $rows[] = [
                'name' => $service,
                'displayName' => __($label),
                'status' => $status == 0,
            ];
        }

        return $rows;
    }

    public function start($service)
    {
        $this->command($service, 'start');
    }

    public function stop($service)
    {
        $this->command($service, 'stop');
    }

    public function restart($service)
    {
        $this->command($service, 'restart');
    }

    protected function command(string $service, string $command)
    {
        $this->systemService->serviceCommand($service, $command);
    }
}
