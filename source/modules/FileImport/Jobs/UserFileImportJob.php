<?php

namespace App\Modules\FileImport\Jobs;

use App\Core\Module\ModuleScheduledJob;
use App\Modules\FileImport\Models\FileImport;
use App\Modules\FileImport\Services\FileImportService;
use Exception;

class UserFileImportJob extends ModuleScheduledJob
{
    protected int $fileImportId;

    protected string $path;

    private ?FileImport $fileImport;

    public function __construct(int  $fileImportId, string $path)
    {
        parent::__construct();

        $this->path = $path;
        $this->fileImportId = $fileImportId;

        $this->fileImport = FileImport::query()->find($this->fileImportId);
        $this->fileImport->path = $path;
    }

    /**
     * @throws Exception
     */
    public function work(): ?array
    {
        if (! $this->fileImport) {
            throw new Exception(__(
                'fileimport::messages.job.file import.errors.file import not found',
                ['id' => $this->fileImportId]
            ));
        } else if (! file_exists($this->path)) {
            throw new Exception(__(
                'fileimport::messages.job.file import.errors.file not exist',
                ['path' => $this->path]
            ));
        }

        return FileImportService::import($this->fileImport);
    }

    public function getDescription(): ?string
    {
        return __('fileimport::messages.job.file import.title', ['file' => $this->path]);
    }
}
