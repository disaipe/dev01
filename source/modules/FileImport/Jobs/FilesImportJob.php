<?php

namespace App\Modules\FileImport\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\FileImport\Models\FileImport;

class FilesImportJob extends ModuleScheduledJob
{
    public function work(): ?array
    {
        $fileImports = FileImport::query()
            ->enabled()
            ->get();

        $fileImports->each(fn (FileImport $fileImport) => FileImportJob::dispatch($fileImport->getKey()));

        return [
            'Result' => 'Started: '.$fileImports->count(),
        ];
    }

    public function getDescription(): ?string
    {
        return __('fileimport::messages.job.files import.title');
    }
}
