<?php namespace Iform\Auth;
/**
 * Class RequestHandler
 *
 * @package     iForm\auth
 * @author      Seth Salinas<ssalinas@zerionsoftware.com>
 * @description iFormBuilder Class that uses curl library to make calls against api.  Flexible interface allows for
 *              chained parameters;
 */
class RequestHandler {

    /**
     * start curl object
     *
     * @var
     */
    private $ch = null;
    /**
     * oAuth Token
     *
     * @var string JWT
     */
    private $token;

    public function init()
    {
        if (gettype($this->ch) !== 'resource') $this->ch = curl_init();
    }

    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @param       $url
     * @param array $params
     *
     * @return $this|mixed
     */
    public function create($url, $params = array())
    {
        $this->setupCreate($url);
        if (! empty($params)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));

            return $this->execute();
        }

        return $this;
    }

    /**
     * GET Request
     *
     * @param       $url
     * @param array $params
     * @param null  $header
     *
     * @return mixed
     */
    public function read($url, $params = array(), $header = null)
    {
        $this->init();
        if (! empty($header)) curl_setopt($this->ch, CURLOPT_HEADER, 1);
        if (! empty($params)) $url = $url . "?" . http_build_query($params);
        $this->baseCurl($url);

        return $this->execute($header);
    }

    /**
     * PUT Request
     *
     * @param       $url
     * @param array $params
     *
     * @return $this|mixed
     */
    public function update($url, $params = array())
    {
        $this->setupUpdate($url);
        if (! empty($params)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));

            return $this->execute();
        }

        return $this;
    }

    /**
     * @param       $url
     * @param array $params
     *
     * @return $this|mixed
     */
    public function delete($url, $params = array())
    {
        $this->setupDelete($url);
        if (! empty($params)) {
            curl_setopt($this->ch, CURLOPT_POSTFIELDS, json_encode($params));

            return $this->execute();
        }

        return $this->execute();
    }

    /**
     * Execute call
     *
     * @param null $header
     *
     * @return mixed
     * @throws \Exception
     */
    public function execute($header = null)
    {
        try {
            $response = $this->handle($header);
        } catch (\Exception $e) {
            $response = $e->getMessage();
        }

        return $response;
    }

    private function handle($header)
    {
        $requestCount = 0;
        do {
            list ($httpStatus, $response) = $this->request($header);
            var_dump($response);
            $isRequestTimedOut = $httpStatus < 100 || $httpStatus == 503 || $httpStatus == 504; //api is busing | rate limiting
            $requestCount ++;

            if ($isRequestTimedOut) sleep(1);
        } while ($isRequestTimedOut && $requestCount < 5);

        $errorMsg = (is_array($response) && isset($response['body'])) ? $response['body'] : $response;
        if ($httpStatus < 200 || $httpStatus >= 400) throw new \Exception($errorMsg, $httpStatus);

        return $response;
    }

    /**
     * Send Curl request
     *
     * @param null $header
     *
     * @return array
     */
    private function request($header = null)
    {
        $response = curl_exec($this->ch);
        if (! is_null($header)) {
            $result = array('header' => '', 'body' => '');
            $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
            $result['header'] = substr($response, 0, $header_size);
            $result['body'] = substr($response, $header_size);
        } else {
            $result = $response;
        }

        $errorCode = curl_errno($this->ch);
        $httpStatus = ($errorCode) ? $errorCode : curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        curl_close($this->ch);

        return array($httpStatus, $result);
    }

    /**
     * @param array $params passed to method
     *
     * @throws \Exception
     * @return string
     */
    public function with($params)
    {
        if (! $this->ch) throw new \Exception('Invalid use of method.  Must declare request type before passing parameters');
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $params);

        return $this->execute();
    }

    /**
     * close any hanging resource
     */
    function __destruct()
    {
        if (gettype($this->ch) == 'resource') curl_close($this->ch);
    }
    /**
     * @param $url
     */
    private function setupDelete($url)
    {
        $this->init();
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array());
        $this->baseCurl($url);
    }

    /**
     * @param $url
     */
    private function setupUpdate($url)
    {
        $this->init();
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        $this->baseCurl($url);
    }

    /**
     * @param $url
     */
    private function setupCreate($url)
    {
        $this->init();
        curl_setopt($this->ch, CURLOPT_POST, true);
        $this->baseCurl($url);
    }

    /**
     * @param $url
     */
    private function baseCurl($url)
    {
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer ' . $this->token,
        ));
    }
}