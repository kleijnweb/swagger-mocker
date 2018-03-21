<?php

namespace Zwartpet\SwaggerMockerBundle\Tests\Service;

use Symfony\Component\HttpFoundation\Request;
use Zwartpet\SwaggerMockerBundle\Model\StubRequest;
use Zwartpet\SwaggerMockerBundle\Model\StubResponse;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function canCreateFoundationResponseFromStubResponse()
    {

        $body = 'foobar';

        $response = (new StubResponse())
            ->setBody($body)
            ->setStatus(404);

        $foundation = $response->toHttpFoundation();

        $this->assertSame($body, $foundation->getContent());
        $this->assertSame(404, $foundation->getStatusCode());
    }

    /**
     * @test
     */
    public function canCreateStubRequestFromFoundationRequest()
    {
        $foundation = Request::create(
            $uri = 'http://foo.bar/blah?etc=x',
            $method = 'POST',
            $parameters = [],
            $cookies = [],
            $files = [],
            $server = [],
            $content = 'hello world'
        );

        $stubRequest = StubRequest::fromHttpFoundation($foundation);

        $this->assertSame($stubRequest->getBody(), $foundation->getContent());
        $this->assertSame($stubRequest->getMethod(), 'POST');

        $urlDefinition = $stubRequest->getUrl()->toDefinition();

        $this->assertSame($urlDefinition->path, '/blah');
        $this->assertSame($urlDefinition->query, 'etc=x');
    }
}
