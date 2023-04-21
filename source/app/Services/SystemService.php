<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class SystemService
{
    protected array $allowedCommands = ['start', 'restart', 'stop', 'status'];

    public function serviceCommand(string $service, string $command): int
    {
        if (in_array($command, $this->allowedCommands)) {
            $process = Process::fromShellCommandline("supervisorctl $command $service");
            $process->run();

            $error = $process->getErrorOutput();

            if ($error) {
                Log::error("Service {$service} error: {$error}");
            }

            return $process->getExitCode();
        }

        return -1;
    }

    public function serviceStatus(string $service): int
    {
        return $this->serviceCommand($service, 'status');
    }
}
