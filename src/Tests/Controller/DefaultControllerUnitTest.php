<?php

namespace Zwartpet\SwaggerMockerBundle\Tests\Controller;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zwartpet\SwaggerMockerBundle\Controller\DefaultController;
use Zwartpet\SwaggerMockerBundle\Model\StubResponse;
use Zwartpet\SwaggerMockerBundle\Service\StubMatcher;

/**
 * @group unit
 */
class DefaultControllerUnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultController
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $stubMatcherMock;

    public function setUp()
    {
        /** @var LoggerInterface $logger */
        $logger = $this->getMockBuilder(LoggerInterface::class)->getMock();

        /** @var StubMatcher $stubMatcher */
        $this->stubMatcherMock = $stubMatcher = $this->getMockBuilder(StubMatcher::class)->disableOriginalConstructor()->getMock();
        $this->controller      = new DefaultController(__DIR__ . '/../..', $logger, $stubMatcher);
    }

    /**
     * @test
     */
    public function willTryToGetResponseFromStubLoader()
    {
        $foundation = Request::create('/faux');

        $this->stubMatcherMock
            ->expects($this->once())
            ->method('getStubResponse')
            ->willReturn(StubResponse::fromDefinition((object)[
                'status' => 200
            ]));

        $response = $this->controller->getResponse($foundation);

        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     */
    public function responseFromStubLoaderWillAutoAddApplicationJson()
    {
        $foundation = Request::create('/faux');

        $this->stubMatcherMock
            ->expects($this->once())
            ->method('getStubResponse')
            ->willReturn(StubResponse::fromDefinition((object)[
                'status' => 200
            ]));

        /** @var Response $response */
        $response = $this->controller->getResponse($foundation);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
    }

    /**
     * @test
     */
    public function canReturnExampleResponseFromSpec()
    {
        $request = $this->mockRequest([
            '200' => [
                'examples' => [
                    'application/json' => 'success'
                ]
            ]
        ]);

        $response = $this->controller->getResponse($request);

        $this->assertEquals('success', $response);
    }

    /**
     * @test
     */
    public function canReturnExampleResponseFromFile()
    {
        $request = $this->mockRequest([
            '200' => [
                'examples' => [
                    'application/json' => 'success'
                ]
            ]
        ], [
            '_route_params' => ['id' => 1]
        ]);

        $this->stubMatcherMock
            ->expects($this->once())
            ->method('loadDefinition')
            ->with(
                $this->matchesRegularExpression(
                    '#' . preg_quote('web/swagger/examples/swagger.default.pets.id.findPetById&id=1.json'
                        . '#'
                    )
                )
            )
            ->willReturn(
                (object)[
                    'id'   => 1,
                    'name' => 'Matt',
                    'tag'  => 'Cat',
                ]
            );

        $response = $this->controller->getResponse($request);

        $this->assertEquals(1, $response->id);
        $this->assertEquals('Matt', $response->name);
        $this->assertEquals('Cat', $response->tag);
    }

    /**
     * @test
     */
    public function canReturn204Response()
    {
        $request = $this->mockRequest([
            '204' => [
                'description' => 'empty response'
            ]
        ]);

        /** @var Response $response */
        $response = $this->controller->getResponse($request);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertEquals(null, $response->getContent());
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage Could not find a successful response in default.yml
     */
    public function willThrowExceptionWhenNoSuccessfullResponseIsFound()
    {
        $request = $this->mockRequest([
            '500' => [
                'description' => 'Errored'
            ]
        ]);

        $this->controller->getResponse($request);
    }

    /**
     * @test
     * @expectedException \Exception
     * @expectedExceptionMessage No stub data source found
     */
    public function willThrowExceptionWhenNoExampleIsFound()
    {
        $request = $this->mockRequest([
            '200' => [
                'description' => 'Errored'
            ]
        ]);

        $this->controller->getResponse($request);
    }

    /**
     * @param       $responses
     * @param array $attributes
     * @param array $query
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Request
     */
    private function mockRequest($responses, $attributes = [], $query = [])
    {
        $uriMethods = [
            'getPathInfo'    => '/foo',
            'getQueryString' => '?bar=baz'
        ];


        $request = $this->getMockBuilder(Request::class)->setMethods(array_keys($uriMethods) + [3 => 'get'])
            ->setConstructorArgs([
                $query,
                [],
                $attributes
            ])
            ->getMock();

        foreach ($uriMethods as $methodName => $value) {
            $request->expects($this->any())->method($methodName)->willReturn($value);
        }

        $request->expects($this->atLeastOnce())->method('get')->willReturnCallback(function ($key) use ($responses) {
            if ($key === '_route') {
                return 'swagger.default.pets.id.findPetById';
            }

            $definition = json_decode(json_encode([
                'responses' => $responses
            ]));
            $operation  = $this->getMockBuilder(OperationObject::class)->disableOriginalConstructor()->getMock();
            $operation->expects($this->once())->method('getDefinition')->willReturn($definition);

            return $operation;
        });

        return $request;
    }
}
