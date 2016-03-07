<?php namespace Iform\Auth;

use Iform\Auth\RequestHandler;
use Iform\Auth\Encoder;
use Iform\Auth\Jwt;


/**
 * @category Authentication
 * @package  iForm\Authentication
 * @author   Seth Salinas <ssalinas@zerionsoftware.com>
 * @license  http://opensource.org/licenses/MIT
 */
class iFormTokenResolver {

    /**
     * This value has a maximum of 10 minutes
     *
     * @var int
     */
    private static $exp = 600;
    /**
     * Credentials - secret.  See instructions for acquiring credentials
     *
     * @var string
     */
    private $secret;
    /**
     * Credentials - client key.  See instructions for acquiring credentials
     *
     * @var string
     */
    private $client;
    /**
     * oAuth - https://ServerName.iformbuilder.com/exzact/api/oauth/token
     *
     * @var string
     */
    private $endpoint;
    /**
     * Jwt class
     *
     * @var Jwt|null
     */
    private $jwt = null;
    /**
     * iForm instance
     *
     * @var RequestHandler |null
     */
    private $request = null;

    /**
     * @param string $url
     * @param string $client
     * @param string $secret
     * @param null   $requester Dependency
     * @param null   $jwt       Dependency
     *
     * @throws \Exception
     */
    function __construct($url, $client, $secret, $requester = null, $jwt = null)
    {
        $this->client = $client;
        $this->secret = $secret;
        $this->endpoint = trim($url);

        $this->request = $requester ?: new RequestHandler();
        $this->jwt = $jwt ?: new Jwt(new Encoder());
    }

    /**
     * @return mixed
     */
    private function getAssertion()
    {
        $iat = time();
        $exp = 600;
        $payload = array(
            "iss" => $this->client,
            "aud" => $this->endpoint,
            "exp" => $iat + $exp,
            "iat" => $iat
        );

        return $this->jwt->encode($payload, $this->secret);
    }

    /**
     * API OAuth endpoint
     *
     * @param string $url
     *
     * @return boolean
     */
    private function isZerionOauth($url)
    {
        return strpos($url, "exzact/api/oauth/token") !== false;
    }

    /**
     * @throws \Exception
     */
    private function validateEndpoint()
    {
        if (empty($this->endpoint) || ! $this->isZerionOauth($this->endpoint)) {
            throw new \Exception('Invalid url: Valid format https://SERVER_NAME.iformbuilder.com/exzact/api/oauth/token');
        }
    }

    /**
     * Build query parameter string
     * @return string
     */
    private function getTokenParams()
    {
        return http_build_query(
            array("grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
                  "assertion"  => $this->getAssertion())
        );
    }

    /**
     * Request/get token
     *
     * @return string
     */
    public function getToken()
    {
        try {
            $this->validateEndpoint();
            $result = $this->validate($this->request->create($this->endpoint)
                                                    ->with($this->getTokenParams()));
        } catch (\Exception $e) {
            $result = $e->getMessage();
        }

        return $result;
    }

    /**
     * @param $results
     *
     * @return string token || error msg
     */
    private function validate($results)
    {
        $token = json_decode($results, true);

        return isset($token['access_token']) ? $token['access_token'] : $token['error'];
    }
}

