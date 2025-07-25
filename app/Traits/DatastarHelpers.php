<?php

namespace App\Traits;

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
                abort(422);
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
}
