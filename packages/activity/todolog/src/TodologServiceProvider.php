<?php

namespace Activity\Todolog;

use Illuminate\Support\ServiceProvider;
use Activity\Todolog\Http\Middleware\TodoActionLogger;

class TodologServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        \Log::info('📦 TodologServiceProvider::register() called');
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/datastar-logger.php',
            'datastar-logger'
        );

        // Register services
        $this->app->singleton(ActionDetector::class);
        $this->app->singleton(StateCapture::class);
        $this->app->singleton(ActionLogger::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        // $this->loadMiddlewareFrom(__DIR__.'/../http/middleware');

        \Log::info('🚀 TodologServiceProvider::boot() called');

        // Auto-register middleware pour les routes Datastar
        $this->app['router']->pushMiddlewareToGroup('web', TodoActionLogger::class);
        \Log::info('✅ Middleware TodoActionLogger added to web group');

        // Publier la config
        $this->publishes([
            __DIR__ . '/config/datastar-logger.php' => config_path('datastar-logger.php'),
        ], 'config');

        // 🎯 NOUVEAU: Publier le fichier log initial
        $this->publishes([
            __DIR__ . '/storage/logs/datastar_actions.log' => storage_path('logs/datastar_actions.log'),
        ], 'logs');

        // 🎯 Publier tout en une fois
        $this->publishes([
            __DIR__ . '/config/datastar-logger.php' => config_path('datastar-logger.php'),
            __DIR__ . '/storage/logs/datastar_actions.log' => storage_path('logs/datastar_actions.log'),
        ], 'todolog');

        // 🎯 Auto-créer le fichier log si il n'existe pas
        $this->ensureLogFileExists();
    }
     /**
     * S'assurer que le fichier log existe
     */
    private function ensureLogFileExists(): void
    {
        $logPath = storage_path('logs/datastar_actions.log');

        if (!file_exists($logPath)) {
            // Créer le répertoire si nécessaire
            $logDir = dirname($logPath);
            if (!is_dir($logDir)) {
                mkdir($logDir, 0755, true);
            }

            // Copier le fichier template depuis le package
            $templatePath = __DIR__ . '/storage/logs/datastar_actions.log';
            if (file_exists($templatePath)) {
                copy($templatePath, $logPath);
            } else {
                // Créer un fichier vide avec un header
                file_put_contents($logPath, $this->getLogFileHeader());
            }

            // Définir les permissions appropriées
            chmod($logPath, 0644);
        }
    }

    /**
     * Header pour le fichier log
     */
    private function getLogFileHeader(): string
    {
        return json_encode([
            'created_at' => now()->toISOString(),
            'package' => 'activity/todolog',
            'version' => '1.0.0',
            'description' => 'Datastar Actions Log File',
        ]) . "\n";
    }
}
