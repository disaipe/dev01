<?php

namespace App\Modules\FileImport\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\FileImport\Models\FileImport;
use App\Modules\FileImport\Services\FileImportService;

class FileImportJob extends ModuleScheduledJob
{
    protected int $fileImportId;

    private ?FileImport $fileImport;

    public function __construct(int $fileImportId)
    {
        parent::__construct();

        $this->fileImportId = $fileImportId;

        $this->fileImport = FileImport::query()->find($this->fileImportId);
    }

    /**
     * @throws \Exception
     */
    public function work(): ?array
    {
        if (! $this->fileImport) {
            throw new \Exception(__(
                'fileimport::messages.job.file import.errors.file import not found',
                ['id' => $this->fileImportId]
            ));
        }

        return FileImportService::import($this->fileImport);
    }

    public function getDescription(): ?string
    {
        return __('fileimport::messages.job.file import.title', ['file' => $this->fileImport->name]);
    }
}
