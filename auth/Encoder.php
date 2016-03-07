<?php namespace Iform\Auth;

class Encoder {

    /**
     * Url encoding with well known replacement characters
     *
     * @param $input
     *
     * @return mixed
     */
    public function base64UrlEncode($input)
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
//        return str_replace("=", "", strtr(base64_encode($input), '+/', '+_'));
    }

    /**
     * Decode base64 url encoding - adjust for 24 bit strings
     *
     * @param $input
     *
     * @return string
     */
    public function base64UrlDecode($input)
    {
        if ($padding = strlen($input) % 4) {
            $input .= (str_repeat('=', 4 - $padding));
        }

        return strtr(base64_decode($input), '+_', '+/');
    }
}