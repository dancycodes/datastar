<?php

namespace App\Traits;

use App\Exceptions\DatastarValidationException;
use Putyourlightson\Datastar\DatastarEventStream;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DatastarHelpers
{
    use DatastarEventStream;

    public function fieldValidate($field, $key = null): StreamedResponse
    {
        // throw new \Exception(__('Only 1 task... TEST WORKS'));
        // abort(403, __('You cannot create a task when you already have one. Please delete the existing task first.'));
        // return redirect()->route('verification.notice');
        dump('Only 1 task... TEST WORKS');
        dd('Show with the previous');

        $rules = $this->rules();

        if ($key) {
            if (!str_ends_with($field, "_{$key}")) {
                $this->toastify(
                    'error',
                    __('Field Validation setup for :field is not valid.', ['field' => $field])
                );

                return $this->getEventStream();
            }
            $rules = $this->setRulesKey($key);
        }

        if (!isset($rules[$field])) {
            $this->toastify(
                'error',
                __('Field :field is not found in rules.', ['field' => $field])
            );

            return $this->getEventStream();
        }

        $signals = $this->readSignals();

        if (!isset($signals[$field])) {
            $this->toastify(
                'error',
                __('Field :field is not found in signals.', ['field' => $field])
            );

            return $this->getEventStream();
        }

        $this->validate(
            $signals,
            [$field => $rules[$field]]
        );

        return $this->getEventStream();
    }

    protected function validate($data, $rules, $messages = [], $attributes = [], $abortOnFailure = true): array
    {
        $validator = Validator::make($data, $rules, $messages, $attributes);

        if ($validator->fails()) {
            if ($abortOnFailure) {
                $this->patchSignals([
                    'errors' => array_map(function ($error) {
                        return is_array($error) ? $error[0] : $error;
                    }, $validator->errors()->toArray()),
                ])
                    ->toastify('error', __('Check the form for errors.'));

                // Throw exception with the streamed response
                throw new DatastarValidationException($this->getEventStream());
            }

            return $validator->errors()->toArray();
        }

        $this->patchSignals([
            'errors' => [],
        ]);

        return $validator->validated();
    }

    protected function setRulesKey($key): array
    {
        $newRules = [];
        foreach ($this->rules() as $field => $rule) {
            $newRules["{$field}_{$key}"] = $rule;
        }

        return $newRules;
    }

    protected function toastify(string $type, string $message)
    {
        return $this->executeScript("showToast('{$type}', '{$message}');");
    }
}
