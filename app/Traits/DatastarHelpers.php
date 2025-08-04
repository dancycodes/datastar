<?php

namespace App\Traits;

use Putyourlightson\Datastar\Services\Sse;
use Symfony\Component\HttpFoundation\StreamedResponse;

trait DatastarHelpers
{
    public function fieldValidate($field, $key = null): StreamedResponse
    {
        // throw new \Exception(__('Only 1 task... TEST WORKS'));
        // abort(403, __('You cannot create a task when you already have one. Please delete the existing task first.'));
        // return redirect()->route('verification.notice');
//        dump('Only 1 task... TEST WORKS');
//        dd('Show with the previous');

        $rules = $this->rules();

        if ($key) {
            if (!str_ends_with($field, "_{$key}")) {
                $this->toastify(
                    'error',
                    __('Field Validation setup for :field is not valid.', ['field' => $field])
                );

                return sse()->getEventStream();
            }
            $rules = $this->setRulesKey($key);
        }

        if (!isset($rules[$field])) {
            $this->toastify(
                'error',
                __('Field :field is not found in rules.', ['field' => $field])
            );

            return sse()->getEventStream();
        }

        $signals = sse()->readSignals();

        if (!isset($signals[$field])) {
            $this->toastify(
                'error',
                __('Field :field is not found in signals.', ['field' => $field])
            );

            return sse()->getEventStream();
        }

        sse()->validate([$field => $rules[$field]]);

        return sse()->getEventStream();
    }

    protected function setRulesKey($key): array
    {
        $newRules = [];
        foreach ($this->rules() as $field => $rule) {
            $newRules["{$field}_{$key}"] = $rule;
        }

        return $newRules;
    }

    protected function toastify(string $type, string $message): Sse
    {
        return sse()->executeScript("showToast('{$type}', '{$message}');");
    }
}
