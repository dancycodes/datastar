<?php

namespace Activity\Todolog\Services;

class ActionDetector
{
    private array $methodMappings;

    public function __construct()
    {
        $this->methodMappings =config('datastar-logger.method_mappings',
         [
            'store' => 'create',
            'destroy' => 'delete',
            'toggleComplete' => 'toggle',
            'update' => 'edit',
            'getForm' => 'edit-form',
            'fieldValidate' => 'field-validation',
        ]);
    }

    public function detect($request): array
    {
        // 🎯 NOUVEAU : Décoder la vraie action depuis le config Datastar
        //exemple de chaine: 432943ee90edc8dcbc6d4bdabe33fdf811d08a9d8550b38e7ba7a555b0afdf25{"route":["TaskController","store"]}
        $realAction = $this->extractDatastarAction($request);
        // \Log::info($realAction);

        $controller = $realAction['controller'] ?? $this->extractController($request);
        $method = $realAction['method'] ?? $this->extractMethod($request);
        $targetId = $this->extractTargetId($request, $realAction);

        \Log::info('🔍 ActionDetector détecté', [
            'real_controller' => $controller,
            'real_method' => $method,
            'target_id' => $targetId,
            'datastar_config' => $request->get('config'),
        ]);

        return [
            'type' => $this->mapMethodToAction($method),
            'controller' => $controller,
            'method' => $method,
            'target_id' => $targetId,
            'params' => $this->extractParams($request),
        ];
    }

    /**
     * 🎯 NOUVEAU : Extraire la vraie action depuis le config Datastar
     */
    private function extractDatastarAction($request): array
    {
        $config = $request->get('config', '');
        // \Log::info("message".$config);

        if (empty($config)) {
            return [];
        }

        // Le config contient un hash suivi du JSON
        // Format: "hash{\"route\":[\"Controller\",\"method\"],\"params\":{...}}"
        if (preg_match('/^[a-f0-9]+(.+)$/', $config, $matches)) {
            $jsonString = $matches[1] ?? '';

            try {
                $decoded = json_decode($jsonString, true);

                if (isset($decoded['route']) && is_array($decoded['route'])) {
                    return [
                        'controller' => $decoded['route'][0] ?? null,
                        'method' => $decoded['route'][1] ?? null,
                        'params' => $decoded['params'] ?? [],
                    ];
                }
            } catch (\Exception $e) {
                \Log::warning('❌ Failed to decode Datastar config', [
                    'config' => $config,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [];
    }

    private function extractTargetId($request, array $realAction = []): ?int
    {
        // 1. Depuis les paramètres de l'action Datastar
        $params = $realAction['params'] ?? [];

        if (isset($params['task']) && is_numeric($params['task'])) {
            return (int) $params['task'];
        }

        // 2. Depuis les paramètres de route (pour les routes directes comme /tasks/{task})
        $routeParams = $request->route() ? $request->route()->parameters() : [];

        foreach (['task', 'id'] as $param) {
            if (isset($routeParams[$param])) {
                $value = $routeParams[$param];

                if (is_object($value) && method_exists($value, 'getKey')) {
                    return $value->getKey();
                }

                if (is_numeric($value)) {
                    return (int) $value;
                }
            }
        }

        // 3. Depuis les données JSON
        $jsonData = $request->json()->all();
        foreach (['task', 'id'] as $param) {
            if (isset($jsonData[$param]) && is_numeric($jsonData[$param])) {
                return (int) $jsonData[$param];
            }
        }

        \Log::info('ℹ️ Pas de target ID (normal pour une actions de creation)', [
            'method' => $realAction['method'] ?? 'unknown',
            'datastar_params' => $params,
            'route_params' => $routeParams,
        ]);

        return null;
    }

    private function extractController($request): ?string
    {
        $route = $request->route();
        if (!$route) return null;

        $action = $route->getAction();
        if (isset($action['controller'])) {
            return class_basename($action['controller']);
        }

        return null;
    }

    private function extractMethod($request): ?string
    {
        $route = $request->route();
        return $route ? $route->getActionMethod() : null;
    }

    private function extractParams($request): array
    {
        $jsonData = $request->json()->all();
        $routeParams = $request->route() ? $request->route()->parameters() : [];

        return array_merge($jsonData, $routeParams);
    }

    private function mapMethodToAction($method): string
    {
        return $this->methodMappings[$method] ?? 'unknown';
    }
}
