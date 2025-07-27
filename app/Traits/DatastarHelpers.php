<?php

namespace App\Traits;

use App\Models\User;
use Putyourlightson\Datastar\DatastarEventStream;
use Illuminate\Support\Facades\Validator;

trait DatastarHelpers
{
    use DatastarEventStream;

    protected function validate($data, $rules, $messages = [], $attributes = [], $abortOnFailure = true)
    {
        // Use the Validator facade to validate the signals
        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            $this->patchSignals([
                'errors' => array_map(function ($error) {
                    return is_array($error) ? $error[0] : $error;
                }, $validator->errors()->toArray()),
            ]);

            $this->toastify('error', __('Check the form for errors.'));

            if ($abortOnFailure) {
                exit();
            }

            return $validator->errors()->toArray();
        }

        $this->resetValidationErrors();

        return $validator->validated();
    }

    protected function resetValidationErrors()
    {
        $this->patchSignals([
            'errors' => [],
        ]);
    }

    protected function toastify($type, $message)
    {
        $this->executeScript("showToast('{$type}', '{$message}');");
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
                $this->toastify(
                    'error',
                    __('Field Validation setup for :field is not valid.', ['field' => $field])
                );
                return;
            }
            $rules = $this->setRulesKey($key);
        }

        if (!isset($rules[$field])) {
            $this->toastify(
                'error',
                __('Field :field is not found in rules.', ['field' => $field])
            );
            return;
        }

        $signals = $this->readSignals();

        if (!isset($signals[$field])) {
            $this->toastify(
                'error',
                __('Field :field is not found in signals.', ['field' => $field])
            );
            return;
        }

        $this->validate(
            $signals,
            [$field => $rules[$field]]
        );
    }

    private function isSSERequest(): bool
    {
        return request()->header('Datastar-Request') === 'true';
    }

    private function authenticateForSSE(User $user, bool $remember = false): void
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
