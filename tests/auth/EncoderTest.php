<?php namespace Iform\Tests\Auth;

use Iform\Auth\Encoder;

class EncoderTest extends \PHPUnit_Framework_TestCase {

    private $encoder;

    function setUp()
    {
        $this->encoder = new Encoder();
    }

    function testBase64UrlEncode()
    {
        $test = 'any carnal pleasure.';
        $this->assertFalse(strpos($this->encoder->base64UrlEncode($test), '='));
    }

    function testBase64UrlDecode()
    {
        $test = 'any carnal pleasure.';
        $base64 = $this->encoder->base64UrlEncode($test);

        $this->assertEquals($test, $this->encoder->base64UrlDecode($base64));
    }

    function tearDown()
    {
        unset($this->encoder);
    }
}
