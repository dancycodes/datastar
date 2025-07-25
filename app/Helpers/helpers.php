<?php

if (!function_exists('dspost')) {
    function dspost($route, $options = [])
    {

        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }


        $options['headers']['X-CSRF-TOKEN'] = csrf_token();


        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES);

        return "@post('{$route}', {$optionsJson})";
    }
}

if (!function_exists('dsput')) {
    function dsput($route, $options = [])
    {
        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }

        $options['headers']['X-CSRF-TOKEN'] = csrf_token();
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES);

        return "@put('{$route}', {$optionsJson})";
    }
}

if (!function_exists('dspatch')) {
    function dspatch($route, $options = [])
    {
        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }

        $options['headers']['X-CSRF-TOKEN'] = csrf_token();
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES);

        return "@patch('{$route}', {$optionsJson})";
    }
}

if (!function_exists('dsdelete')) {
    function dsdelete($route, $options = [])
    {
        if (!isset($options['headers'])) {
            $options['headers'] = [];
        }

        $options['headers']['X-CSRF-TOKEN'] = csrf_token();
        $optionsJson = json_encode($options, JSON_UNESCAPED_SLASHES);

        return "@delete('{$route}', {$optionsJson})";
    }
}