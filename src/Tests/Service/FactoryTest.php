<?php

namespace Zwartpet\SwaggerMockerBundle\Tests\Routing;

use Symfony\Component\HttpFoundation\Request;
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
    public function canCreateStubRequestFromFountationRequest()
    {
        $foundation = new Request(
            $query = [],
            $request = [],
            $attributes = [],
            $cookies = [],
            $files = [],
            $server = [],
            $content = null
        );

        $body = 'foobar';

        $response = (new StubResponse())
            ->setBody($body)
            ->setStatus(404);

        $foundation = $response->toHttpFoundation();

        $this->assertSame($body, $foundation->getContent());
        $this->assertSame(404, $foundation->getStatusCode());
    }
}
