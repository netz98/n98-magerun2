<?php
/**
 * This file is part of the n98-magerun2 project.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

namespace N98\Util\Http;

use PHPUnit\Framework\TestCase;

class CurlHttpResponseTest extends TestCase
{
    public function testGettersAndSetters()
    {
        $response = new CurlHttpResponse();

        $this->assertSame($response, $response->setSuccess(true));
        $this->assertTrue($response->isSuccess());

        $this->assertSame($response, $response->setStatusCode(200));
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertSame($response, $response->setBody('foo'));
        $this->assertEquals('foo', $response->getBody());

        $headers = ['Content-Type' => 'text/plain'];
        $this->assertSame($response, $response->setHeaders($headers));
        $this->assertEquals($headers, $response->getHeaders());

        $this->assertSame($response, $response->setError('error'));
        $this->assertEquals('error', $response->getError());

        $this->assertSame($response, $response->setCurlErrno(7));
        $this->assertEquals(7, $response->getCurlErrno());

        $this->assertSame($response, $response->setContentLength(123));
        $this->assertEquals(123, $response->getContentLength());

        $this->assertSame($response, $response->setSizeDownload(456));
        $this->assertEquals(456, $response->getSizeDownload());

        $this->assertSame($response, $response->setFileSize(789));
        $this->assertEquals(789, $response->getFileSize());

        $this->assertSame($response, $response->setExpectedSize(1011));
        $this->assertEquals(1011, $response->getExpectedSize());

        $curlInfo = ['url' => 'http://example.com'];
        $this->assertSame($response, $response->setCurlInfo($curlInfo));
        $this->assertEquals($curlInfo, $response->getCurlInfo());
    }

    public function testMagicMethods()
    {
        $response = new CurlHttpResponse();

        // Test __set and __get with snake_case
        $response->status_code = 404;
        $this->assertEquals(404, $response->status_code);
        $this->assertEquals(404, $response->getStatusCode());

        $response->success = false;
        $this->assertFalse($response->success);
        $this->assertFalse($response->isSuccess());

        $response->body = 'not found';
        $this->assertEquals('not found', $response->body);

        $response->headers = ['X-Test' => 'value'];
        $this->assertEquals(['X-Test' => 'value'], $response->headers);

        $response->error = 'foo';
        $this->assertEquals('foo', $response->error);

        $response->curl_errno = 1;
        $this->assertEquals(1, $response->curl_errno);

        $response->content_length = 100;
        $this->assertEquals(100, $response->content_length);

        $response->size_download = 50;
        $this->assertEquals(50, $response->size_download);

        $response->file_size = 200;
        $this->assertEquals(200, $response->file_size);

        $response->expected_size = 300;
        $this->assertEquals(300, $response->expected_size);

        $response->curl_info = ['foo' => 'bar'];
        $this->assertEquals(['foo' => 'bar'], $response->curl_info);

        // Test __isset
        $this->assertTrue(isset($response->status_code));
        $this->assertTrue(isset($response->success));
        $this->assertTrue(isset($response->body));
        $this->assertTrue(isset($response->headers));
        $this->assertTrue(isset($response->error));
        $this->assertTrue(isset($response->curl_errno));
        $this->assertTrue(isset($response->content_length));
        $this->assertTrue(isset($response->size_download));
        $this->assertTrue(isset($response->file_size));
        $this->assertTrue(isset($response->expected_size));
        $this->assertTrue(isset($response->curl_info));

        $this->assertFalse(isset($response->non_existent));
    }

    public function testUndefinedPropertyGet()
    {
        $response = new CurlHttpResponse();

        $this->expectNotice();
        $this->expectNoticeMessage('Undefined property: N98\Util\Http\CurlHttpResponse::invalid');

        $value = $response->invalid;
        $this->assertNull($value);
    }

    public function testUndefinedPropertySet()
    {
        $response = new CurlHttpResponse();

        $this->expectNotice();
        $this->expectNoticeMessage('Undefined property: N98\Util\Http\CurlHttpResponse::invalid');

        $response->invalid = 'foo';
    }
}
