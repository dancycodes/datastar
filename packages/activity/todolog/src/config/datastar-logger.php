<?php

return [
    // 'enabled' => env('DATASTAR_LOGGER_ENABLED', true),

    // Le fichier sera automatiquement dans storage/logs après installation
    'log_path' => storage_path('logs/datastar_actions.log'),

    'target_model' => \App\Models\Task::class,

    'method_mappings' => [
        'store' => 'create',
        'destroy' => 'delete',
        'toggleComplete' => 'toggle',
        'update' => 'edit',
        'getForm' => 'edit-form',
    ],

    'undoable_actions' => [
        'create', 'delete', 'toggle', 'edit'
    ],

    // Rotation du fichier log
    'max_log_size' => 10 * 1024 * 1024, // 10MB
    'keep_logs' => 5, // Garder 5 fichiers d'archive
];
