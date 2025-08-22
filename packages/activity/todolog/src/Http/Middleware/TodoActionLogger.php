<?php

namespace Activity\Todolog\Http\Middleware;

use Closure;
use Activity\Todolog\Services\ActionDetector;
use Activity\Todolog\Services\StateCapture;
use Activity\Todolog\Services\ActionLogger;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TodoActionLogger
{
    public function __construct(
        private ActionDetector $detector,
        private StateCapture $stateCapture,
        private ActionLogger $logger
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('🔍 TodoActionLogger middleware called', [
            // 'donnée'=>$request->path(),
            'url' => $request->url(),
            'methode' => $request->method(),
            'path' => $request->path(),
        ]);

        // 1. Vérifier si c'est une action Datastar
        if (!$this->isDatastarAction($request)) {
            \Log::info('❌ C\'est pas une action Datastar, on passe');
            return $next($request);
        }

        // 2. Détecter le type d'action
        $actionInfo = $this->detector->detect($request);
        \Log::info('✅ Action détectée', $actionInfo);

        // 3. Capturer l'état AVANT (seulement si on a un target_id)
        $beforeState = null;
        if ($actionInfo['target_id']) {
            $beforeState = $this->stateCapture->captureBefore($actionInfo);
            \Log::info('📸 Capture du statut avant', ['before_state' => $beforeState]);
        }

        // 4. Exécuter l'action originale
        $response = $next($request);

        // 5. Si succès, capturer l'état APRÈS et logger
        if ($response->isSuccessful()) {
            \Log::info('✅ Action exécutée avec succès');

            // Capturer l'état APRÈS
            $afterState = null;
            if ($actionInfo['target_id'] && $actionInfo['type'] !== 'delete') {
                $afterState = $this->stateCapture->captureAfter($actionInfo);
                \Log::info('📸 After state captured', ['after_state' => $afterState]);
            }

            // Logger l'action
            $this->logger->log([
                'action_type' => $actionInfo['type'],
                'target_id' => $actionInfo['target_id'],
                'before_state' => $beforeState,
                'after_state' => $afterState,
                'user_id' => auth()->id(),
                'timestamp' => now(),
            ]);

            \Log::info('📝 Action Enregistrée avec succes');
        } else {
            \Log::warning('❌ Action Non enregistrée');
        }

        // 6. Retourner la réponse (UNE SEULE FOIS!)
        return $response;
    }

    private function isDatastarAction($request): bool
    {
        // Le plus simple : si le header datastar-request est présent
        if ($request->hasHeader('datastar-request')) {
            \Log::info('✅ Le header datastar-request est présent');
            return true;
        }

        // Backup : si le path contient datastar-controller
        // if (str_contains($request->path(), 'datastar-controller')) {
        //     \Log::info('✅ Datastar action detected via path');
        //     return true;
        // }

        \Log::info('❌Pas d\'action Datastar détectée');
        return false;
    }
}
