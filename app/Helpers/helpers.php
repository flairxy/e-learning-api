<?php


if (!function_exists('abort_unless')) {
    /**
     * @param $boolean
     * @param $code
     * @param string $message
     */
    function abort_unless($boolean, $code, $message = '')
    {
        if (!$boolean) {
            abort($code, $message);
        }
    }
}

if (!function_exists('abort_if')) {
    /**
     * @param $boolean
     * @param $code
     * @param string $message
     */
    function abort_if($boolean, $code, $message = '')
    {
        if ($boolean) {
            abort($code, $message);
        }
    }
}


function setting($key, $default = null, $cast = null)
{
    try {
        if (is_array($key)) {
            foreach ($key as $s_key => $s_value) {
                \App\Models\Setting::set($s_key, $s_value);
            }
            return true;
        } else {
            $value = env($key);
            return $value ?: \App\Models\Setting::get($key, $default, $cast);
        }
    } catch (Exception $e) {
        return $default;
    }
}

if (!function_exists('request')) {
    /**
     * @return \Illuminate\Http\Request
     */
    function request()
    {
        return app('request');
    }
}

function usernameRegex()
{
    return '/^\w+([.\-_]\w*)*$/';
}

if (!function_exists('random_chars')) {
    /**
     * @param int $length
     * @param string $set Set to include: n for numbers, u for uppercase letters and l for lowercase letters
     * @return string
     */
    function random_chars($length = 8, $set = 'nul')
    {
        $chars['n'] = '0123456789';
        $chars['l'] = 'abcdefghijklmnopqrstuvwxyz';
        $chars['u'] = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $string = '';
        for ($i = 0; $i < strlen($set); $i++) {
            $string .= $chars[$set[$i]] ?? '';
        }

        $result = '';
        $strlen = strlen($string);
        for ($i = 1; $i <= $length; $i++) {
            $pos = rand(0, $strlen - 1);
            $result .= substr($string, $pos, 1);
        }
        return $result;
    }
}
