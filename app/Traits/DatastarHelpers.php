<?php

namespace App\Traits;

use Putyourlightson\Datastar\DatastarEventStream;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\DatastarValidationException;

trait DatastarHelpers
{
    use DatastarEventStream;

    /**
     * Array to store SSE events before sending them
     */
    private array $sseEvents = [];

    protected function validate($data, $rules, $messages = [], $attributes = [], $abortOnFailure = true)
    {
        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            if ($abortOnFailure) {
                $this->addPatchSignals([
                    'errors' => array_map(function ($error) {
                        return is_array($error) ? $error[0] : $error;
                    }, $validator->errors()->toArray()),
                ])
                    ->addToastify('error', __('Check the form for errors.'));

                // Throw exception with the streamed response
                throw new DatastarValidationException($this->sendEvents());
            }

            return $validator->errors()->toArray();
        }

        $this->resetValidationErrors();

        return $validator->validated();
    }

    protected function resetValidationErrors()
    {
        $this->addPatchSignals([
            'errors' => [],
        ]);
    }

    protected function setRulesKey($key)
    {
        $newRules = [];
        foreach ($this->rules() as $field => $rule) {
            $newRules["{$field}_{$key}"] = $rule;
        }

        return $newRules;
    }

    public function fieldValidate($field, $key = null)
    {
        $rules = $this->rules();

        if ($key) {
            if (!str_ends_with($field, "_{$key}")) {
                $this->addToastify(
                    'error',
                    __('Field Validation setup for :field is not valid.', ['field' => $field])
                );
                return;
            }
            $rules = $this->setRulesKey($key);
        }

        if (!isset($rules[$field])) {
            $this->addToastify(
                'error',
                __('Field :field is not found in rules.', ['field' => $field])
            );
            return;
        }

        $signals = $this->readSignals();

        if (!isset($signals[$field])) {
            $this->addToastify(
                'error',
                __('Field :field is not found in signals.', ['field' => $field])
            );
            return;
        }

        $this->validate(
            $signals,
            [$field => $rules[$field]]
        );

        return $this->sendEvents();
    }

    /**
     * Add an SSE event to the events array
     */
    public function addEvent(string $method, ...$args) 
    {
        $this->sseEvents[] = [
            'method' => $method,
            'args' => $args
        ];

        return $this;
    }

    /**
     * Add a patch elements event
     */
    public function addPatchElements(string $data, array $options = []) 
    {
        return $this->addEvent('patchElements', $data, $options);
    }

    /**
     * Add a remove elements event
     */
    public function addRemoveElements(string $selector, array $options = []) 
    {
        return $this->addEvent('removeElements', $selector, $options);
    }

    /**
     * Add a patch signals event
     */
    public function addPatchSignals(array $signals, array $options = []) 
    {
        return $this->addEvent('patchSignals', $signals, $options);
    }

    /**
     * Add an execute script event
     */
    public function addExecuteScript(string $script, array $options = []) 
    {
        return $this->addEvent('executeScript', $script, $options);
    }

    /**
     * Add a location redirect event
     */
    public function addLocation(string $uri, array $options = []) 
    {
        return $this->addEvent('location', $uri, $options);
    }

    /**
     * Add a render Datastar view event
     */
    public function addRenderDatastarView(string $view, array $variables = []) 
    {
        return $this->addEvent('renderDatastarView', $view, $variables);
    }

    /**
     * Add a toast notification event
     */
    public function addToastify(string $type, string $message) 
    {
        return $this->addEvent('executeScript', "showToast('{$type}', '{$message}');");
    }

    /**
     * Clear all collected events
     */
    public function clearEvents(): void
    {
        $this->sseEvents = [];
    }

    /**
     * Get all collected events
     */
    public function getEvents(): array
    {
        return $this->sseEvents;
    }

    /**
     * Send all collected events via SSE
     */
    public function sendEvents()
    {
        if (empty($this->sseEvents)) {
            return $this->getStreamedResponse(function () {
                // Send empty response if no events
            });
        }

        return $this->getStreamedResponse(function () {
            foreach ($this->sseEvents as $event) {
                $method = $event['method'];
                $args = $event['args'];

                // Call the method with the stored arguments
                $this->$method(...$args);
            }

            // Clear events after sending
            $this->clearEvents();
        });
    }

    public function sendEventsSSE()
    {
        foreach ($this->sseEvents as $event) {
            $method = $event['method'];
            $args = $event['args'];

            // Call the method with the stored arguments
            $this->$method(...$args);
        }

        // Clear events after sending
        $this->clearEvents();
    }

    /**
     * Check if there are events to send
     */
    public function hasEvents(): bool
    {
        return !empty($this->sseEvents);
    }


    /**
     * Authenticate a user in Server-Sent Events (SSE)
     */
    private function authenticateForSSE(\App\Models\User $user, bool $remember = false): void
    {
        $guard = auth()->guard('web');
        $guard->setUser($user);

        // Set authentication session data
        $sessionKey = $guard->getName();
        session()->put($sessionKey, $user->getAuthIdentifier());

        // Handle remember token if requested
        if ($remember) {
            $user->setRememberToken(\Str::random(60));
            $user->save();
        }

        session()->save();
    }
}