<?php namespace Iform\Tests\Auth;

use Iform\Auth\Encoder;
use Iform\Auth\Jwt;

class JwtTest extends \PHPUnit_Framework_TestCase {

    private $jwt;
    private $encoder;

    private $client = 'abd309ab9e923fa175c5f8dc30abe8e15dae704a';
    private $secret = '0d0d62cae09bacb9297a44788c8ed37bbd6a275f';
    private $endpoint = 'https://www.iformbuilder.com/exzact/api/oauth/token';

    public function setUp()
    {
        $this->encoder = new Encoder();
        $this->jwt = new Jwt($this->encoder);
    }

    public function testReturnsValidAssertionWithValidParameters()
    {
        $assertion = $this->jwt->encode($this->getPayload(), $this->secret, 'HS256');
        list($header, $claim, $signature) = explode(".", $assertion);
        $baseStr =  $header . "." . $claim;

        $this->assertTrue($this->validateHeader($header));
        $this->assertTrue($this->validateClaim($claim));
        $this->assertTrue($this->validateSignature($signature, $baseStr));
    }

    public function testFailsWithInvalidAlgorithm()
    {
        $token = $this->jwt->encode($this->getPayload(), $this->secret, 'HS958');
        $this->assertEquals($token, 'invalid algorithm passed');
    }

    public function testFailsWithInvalidClaimSet()
    {
        $token = $this->jwt->encode(array(), $this->secret, 'HS256');
        $this->assertEquals($token, 'invalid claim set');
    }

    private function validateSignature($sig, $base)
    {
        $test = $this->encoder->base64UrlEncode(hash_hmac('sha256', $base, $this->secret, true));

        return $test === $sig;
    }

    private function validateHeader($header)
    {
        $header = $this->decode($header);

        return ! $this->hasJsonError() && isset($header['typ']) && isset($header['alg']);
    }

    private function hasJsonError()
    {
        return json_last_error() !== JSON_ERROR_NONE;
    }

    private function validateClaim($claim)
    {
        $claim = $this->decode($claim);

        //not necessary to check all claims for simple test
        return ! $this->hasJsonError() && isset($claim['iss']) && $claim['iss'] == $this->client;
    }

    private function decode($encoded)
    {
        return json_decode($this->encoder->base64UrlDecode($encoded), true);;
    }

    private function getPayload()
    {
        $iat = time();
        return array(
            "iss" => $this->client,
            "aud" => $this->endpoint,
            "exp" => $iat + 600,
            "iat" => $iat
        );
    }

    public function tearDown()
    {
        unset($this->jwt);
        unset($this->encoder);
    }
}
