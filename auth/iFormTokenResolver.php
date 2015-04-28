<?php namespace iForm\Auth;

use iForm\Auth\iFormCurl;

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
     * @param string $url
     * @param string $client
     * @param string $secret
     * @param null   $requester Can pass mock or dummy object for unit testing
     *
     * @throws \Exception
     */
    function __construct($url, $client, $secret, $requester = null)
    {
        $this->setEndpoint($url);
        $this->client = $client;
        $this->secret = $secret;
        $this->request = $requester ?: new iFormCurl();
    }

    /**
     * @param string $client_key
     * @param string $client_secret
     *
     * @return string
     */
    private function encode($client_key, $client_secret)
    {
        $iat = (new \DateTime())->getTimestamp();
        $payload = array(
            "iss" => $client_key,
            "aud" => $this->endpoint,
            "exp" => $iat + self::$exp,
            "iat" => $iat
        );

        return \JWT::encode($payload, $client_secret);
    }

    /**
     * URL must begin with secure https:// and end with api OAuth endpoint
     *
     * @param string $url
     *
     * @return int (1 or 0)
     */
    private function isValid($url)
    {
        return preg_match("/\/exzact\/api\/oauth\/token/D", $url);
    }

    /**
     * Set endpoint after check
     *
     * @param string $url
     *
     * @throws \Exception
     * @return null
     */
    public function setEndpoint($url)
    {
        if (empty($url) || ! $this->isValid($url)) {
            throw new \Exception('Invalid url: Valid format https://SERVER_NAME.iformbuilder.com/exzact/api/oauth/token');
        }

        $this->endpoint = $url;
    }

    /**
     * Request/get token
     *
     * @return string
     */
    public function getToken()
    {
        $params = array("grant_type" => "urn:ietf:params:oauth:grant-type:jwt-bearer",
                        "assertion"  => $this->encode($this->client, $this->secret));

        return $this->check($this->request->post($this->endpoint)
                                          ->with($params));
    }

    /**
     * Check results
     * @param $results
     *
     * @return string token || error msg
     */
    private function check($results)
    {
        $token = json_decode($results, true);

        if (isset($token['access_token'])) {
            return $token['access_token'];
        } else {
            return $token['error'];
        }

    }

}


