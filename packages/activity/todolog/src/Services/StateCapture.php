<?php

namespace Activity\Todolog\Services;

class StateCapture
{
    public function captureBefore(array $actionInfo): ?array
    {
        $targetId = $actionInfo['target_id'];

        if (!$targetId) {
            return null;
        }

        // Pour toutes les actions qui modifient un objet existant
        return match($actionInfo['type']) {
            'delete', 'toggle', 'edit', 'edit-form' => $this->captureModel($targetId),
            'create' => null, // Pas d'état avant pour création
            'field-validation' => null, // Pas besoin de capturer pour validation
            default => null,
        };
    }

    public function captureAfter(array $actionInfo): ?array
    {
        $targetId = $actionInfo['target_id'];

        if (!$targetId) {
            return null;
        }

        return match($actionInfo['type']) {
            'toggle', 'edit' => $this->captureModel($targetId), // Objet modifié
            'create' => $this->captureModel($targetId), // Nouvel objet créé
            'delete' => null, // ❌ Objet supprimé, n'existe plus !
            'edit-form' => null, // Pas de changement d'état
            'field-validation' => null, // Pas de changement d'état
            default => null,
        };
    }

    private function captureModel($id): ?array
    {
        try {
            $modelClass = config('datastar-logger.target_model', \App\Models\Task::class);
            $model = $modelClass::find($id);
           
            if (!$model) {
                \Log::info('ℹ️ Aucun model trouvé (normal après suppression)', [
                    'id' => $id,
                    'model_class' => $modelClass,
                ]);
                return null;
            }

            return $model->toArray();
        } catch (\Exception $e) {
            \Log::error('❌ Erreur lors de la capture du model', [
                'error' => $e->getMessage(),
                'id' => $id,
            ]);
            return null;
        }
    }
}
