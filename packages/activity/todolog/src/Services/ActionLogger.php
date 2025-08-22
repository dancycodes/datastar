<?php

namespace Activity\Todolog\Services;

class ActionLogger
{
    private string $logPath;
    private int $maxLogSize;

    public function __construct()
    {
        $this->logPath = config('datastar-logger.log_path');
        $this->maxLogSize = config('datastar-logger.max_log_size', 10 * 1024 * 1024);

        // S'assurer que le fichier existe
        $this->ensureLogFileExists();
    }

    public function log(array $data): void
    {
        // Vérifier la rotation si nécessaire
        $this->rotateIfNeeded();

        $logEntry = [
            'timestamp' => $data['timestamp']->format('d-m-Y H:i:s'),
            'user_id' => $data['user_id'],
            'action' => $data['action_type'],
            'target_id' => $data['target_id'],
            'before_state' => $data['before_state'],
            'after_state' => $data['after_state'],
            // 'can_undo' => $this->canUndo($data['action_type']),
        ];

        file_put_contents(
            $this->logPath,
            json_encode($logEntry) . "\n",
            FILE_APPEND | LOCK_EX
        );
    }

    private function ensureLogFileExists(): void
    {
        if (!file_exists($this->logPath)) {
            $dir = dirname($this->logPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            file_put_contents($this->logPath, $this->getLogFileHeader());
            chmod($this->logPath, 0644);
        }
    }

    private function rotateIfNeeded(): void
    {
        if (file_exists($this->logPath) && filesize($this->logPath) > $this->maxLogSize) {
            $timestamp = date('Y-m-d_H-i-s');
            $archivePath = str_replace('.log', "_{$timestamp}.log", $this->logPath);

            rename($this->logPath, $archivePath);

            // Créer un nouveau fichier
            file_put_contents($this->logPath, $this->getLogFileHeader());
            chmod($this->logPath, 0644);
        }
    }

    private function getLogFileHeader(): string
    {
        return json_encode([
            'created_at' => now()->toISOString(),
            'package' => 'activity/todolog',
            'description' => 'Datastar Actions Log File',
        ]) . "\n";
    }

    // private function canUndo(string $actionType): bool
    // {
    //     $undoableActions = config('datastar-logger.undoable_actions', []);
    //     return in_array($actionType, $undoableActions);
    // }
}
