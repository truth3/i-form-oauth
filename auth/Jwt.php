<?php namespace Iform\Auth;

use iForm\Auth\Encoder;

class Jwt {

    /**
     * Valid iFormBuilder API algorithms
     * @var array
     */
    private $validAlgorithms = array(
        'HS256' => 'sha256',
        'HS384' => 'sha384',
        'HS512' => 'sha512'
    );
    /**
     * Base64 encoding
     * @var Encoder
     */
    private $encoder;

    function __construct(Encoder $encoder = null)
    {
        $this->encoder = $encoder ?: new Encoder();
    }

    /**
     * Class signature
     * @param        $payload
     * @param        $key
     * @param string $alg
     *
     * @return string
     */
    public function encode($payload, $key, $alg = 'HS256')
    {
        try {
            $formatted = $this->segments($payload, $key, $alg);
        } catch (\Exception $e) {
            $formatted = $e->getMessage();
        }

        return $formatted;
    }

    private function segments($payload, $key, $alg)
    {
        $segments = [];
        $segments[] = $this->header($alg);
        $segments[] = $this->claimSet($payload);
        $segments[] = $this->signature($segments, $key, $alg);

        return join(".", $segments);
    }

    private function header($alg)
    {
        return $this->encoder->base64UrlEncode(json_encode(array('typ' => 'JWT', 'alg' => $alg)));
    }

    private function claimSet($payload)
    {
        if (! $this->validateClaimSet($payload)) throw new \InvalidArgumentException("invalid claim set");

        return $this->encoder->base64UrlEncode(json_encode($payload));
    }

    private function validateClaimSet($payload)
    {
        $required = array("iss", "aud", "exp", "iat");
        $passed = array_keys($payload);

        foreach ($required as $key) {
            if (! in_array($key, $passed)) return false;
        }

        return true;
    }

    /**
     * Produce signature
     * @param $segments
     * @param $key
     * @param $alg
     *
     * @return mixed
     * @throws \Exception
     */
    private function signature($segments, $key, $alg)
    {
        $signingInput = join(".", $segments);
        $signature = $this->sign($signingInput, $key, $alg);

        return $this->encoder->base64UrlEncode($signature);
    }

    /**
     * Digest the algorithm
     * @param $msg
     * @param $key
     * @param $alg
     *
     * @return string
     * @throws \Exception
     */
    private function sign($msg, $key, $alg)
    {
        if (! isset($this->validAlgorithms[$alg])) throw new \InvalidArgumentException("invalid algorithm passed");

        return hash_hmac($this->validAlgorithms[$alg], $msg, $key, true);
    }

}