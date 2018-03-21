<?php

namespace Zwartpet\SwaggerMockerBundle\Tests\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Zwartpet\SwaggerMockerBundle\Model\StubRequest;
use Zwartpet\SwaggerMockerBundle\Model\StubResponse;
use Zwartpet\SwaggerMockerBundle\Model\Url;
use Zwartpet\SwaggerMockerBundle\Service\StubMatcher;

class StubMatcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    /**
     * @var StubMatcher
     */
    private $matcher;

    protected function setUp()
    {
        /** @var LoggerInterface $logger */
        $this->loggerMock = $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $this->matcher    = new StubMatcher(
            new Filesystem(),
            __DIR__ . '/_assets/stubs.yml',
            $logger
        );

    }

    /**
     * @test
     */
    public function willValidateStubs()
    {
        $this->loggerMock->expects($this->exactly(1))
            ->method('warning');

        $this->expectException(\UnexpectedValueException::class);

        /** @var LoggerInterface $logger */
        $logger = $this->loggerMock;

        $this->matcher = new StubMatcher(
            new Filesystem(),
            __DIR__ . '/_assets/invalid_stubs.yml',
            $logger
        );
    }

    /**
     * @test
     */
    public function canGetStubResponse()
    {
        $request = (new StubRequest())
            ->setMethod('GET')
            ->setUrl(new Url('/foo'));

        $response = $this->matcher->getStubResponse($request);
        $this->assertNotNull($response);

        $this->assertInstanceOf(StubResponse::class, $response);
    }

    /**
     * @test
     */
    public function canFailToMatchRequests()
    {
        $request = (new StubRequest())
            ->setMethod('HEAD')
            ->setUrl(new Url('/foo'));

        $response = $this->matcher->getStubResponse($request);
        $this->assertNull($response);
    }

    /**
     * @test
     */
    public function willPickFirstResponseWithMatchingSchema()
    {
        $request = (new StubRequest())
            ->setMethod('POST')
            ->setBody((object)[])
            ->setUrl(new Url('/foo'));

        $response = $this->matcher->getStubResponse($request);
        $this->assertNotNull($response);

        $this->assertInstanceOf(StubResponse::class, $response);
        $this->assertInstanceOf(\stdClass::class, $response->getBody());

        $this->assertSame(1, $response->getBody()->id);
    }

    /**
     * @test
     */
    public function canMatchOnBody()
    {
        $request = (new StubRequest())
            ->setMethod('PUT')
            ->setBody((object)[
                'foo' => 'bar'
            ])
            ->setUrl(new Url('/foo'));

        $response = $this->matcher->getStubResponse($request);
        $this->assertNotNull($response);

        $this->assertInstanceOf(StubResponse::class, $response);
        $this->assertInstanceOf(\stdClass::class, $response->getBody());

        $this->assertSame(3, $response->getBody()->id);
    }

    /**
     * @test
     */
    public function canMismatchOnBody()
    {
        $request = (new StubRequest())
            ->setMethod('PUT')
            ->setBody((object)[
                'foo' => 'bar',
                'nope' => true
            ])
            ->setUrl(new Url('/foo'));

        $response = $this->matcher->getStubResponse($request);
        $this->assertNull($response);
    }

    /**
     * @test
     */
    public function canMatchOnHeaders()
    {
        $request = (new StubRequest())
            ->setMethod('PUT')
            ->setBody((object)[
                'foo' => 'bar'
            ])
            ->setHeaders((object)[
                'Authorization' => 'something'
            ])
            ->setUrl(new Url('/foo'));

        $response = $this->matcher->getStubResponse($request);
        $this->assertNotNull($response);

        $this->assertInstanceOf(StubResponse::class, $response);
        $this->assertInstanceOf(\stdClass::class, $response->getBody());

        $this->assertSame(3, $response->getBody()->id);
    }

    /**
     * @test
     */
    public function canMismatchOnHeaders()
    {
        $request = (new StubRequest())
            ->setMethod('PUT')
            ->setBody((object)[
                'foo' => 'bar'
            ])
            ->setHeaders((object)[
                'Fooo' => 'bar'
            ])
            ->setUrl(new Url('/foo'));

        $response = $this->matcher->getStubResponse($request);
        $this->assertNull($response);
    }
}
