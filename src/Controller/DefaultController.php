<?php declare(strict_types=1);

namespace Zwartpet\SwaggerMockerBundle\Controller;

use KleijnWeb\SwaggerBundle\Document\OperationObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Zwartpet\SwaggerMockerBundle\Model\StubRequest;
use Zwartpet\SwaggerMockerBundle\Service\StubMatcher;

class DefaultController
{
    /**
     * @var string|null
     */
    private $examplesDir;

    /**
     * @var null|StubMatcher
     */
    private $stubMatcher;

    /**
     * @var null|LoggerInterface
     */
    private $logger;

    /**
     * @param string               $rootDir
     * @param LoggerInterface|null $logger
     * @param StubMatcher|null     $stubMatcher
     */
    public function __construct(string $rootDir, LoggerInterface $logger, StubMatcher $stubMatcher = null)
    {
        $this->examplesDir = realpath($rootDir . '/../web/swagger/examples/') ?: null;
        $this->logger      = $logger;
        $this->stubMatcher = $stubMatcher
            ?: new StubMatcher(new Filesystem(), "$rootDir/../web/swagger/stubs", $logger);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Exception
     */
    public function getResponse(Request $request)
    {
        if (null !== $stubResponse = $this->stubMatcher->getStubResponse(StubRequest::fromHttpFoundation($request))) {
            $this->logger->info("Request matched from stub file");
            return $stubResponse->toHttpFoundation();
        }

        if (null !== $fileExamples = $this->getExamplesFromFile($request)) {
            return $fileExamples;
        }

        /** @var OperationObject $operation */
        $operation  = $request->get('_swagger_operation');
        $definition = $operation->getDefinition();
        $statusCode = $this->getStatusCode($definition->responses);

        if ($statusCode === 204) {
            return new Response(null, 204);
        }

        if (property_exists($definition->responses->{$statusCode}, 'examples') &&
            property_exists($definition->responses->{$statusCode}->{'examples'}, 'application/json')
        ) {
            return $definition->responses->{$statusCode}->{'examples'}->{'application/json'};
        }

        throw new \Exception('No stub data source found');
    }

    /**
     * @param Request $request
     *
     * @return null|\stdClass
     */
    private function getExamplesFromFile(Request $request)
    {
        $fs         = new Filesystem();
        $attributes = [];

        if ($request->attributes->get('_route_params')) {
            foreach ($request->attributes->get('_route_params') as $key => $value) {
                if (substr($key, 0, 1) !== '_') {
                    $attributes[$key] = $value;
                }
            }
        }

        $examplesPath = $request->get('_route') .
            $this->getQueryString($attributes) .
            $this->getQueryString($request->query->all());

        $pathBaseName = "{$this->examplesDir}/{$examplesPath}";

        if ($fs->exists($pathName = "$pathBaseName.json")) {
            return $this->stubMatcher->loadDefinition($pathName);
        }

        if ($fs->exists($pathName = "$pathBaseName.yaml")) {
            return $this->stubMatcher->parseDefinition($pathName);
        }

        return null;
    }

    /**
     * @param array $parameters
     *
     * @return string
     */
    private function getQueryString($parameters)
    {
        ksort($parameters);
        $requestParams = http_build_query($parameters);

        return ($requestParams) ? "&$requestParams" : '';
    }

    /**
     * @param $responses
     *
     * @return mixed
     * @throws \Exception
     */
    private function getStatusCode($responses)
    {
        $codes = [200, 201, 202, 204];
        foreach ($codes as $code) {
            if (property_exists($responses, (string)$code)) {
                return $code;
            }
        }

        throw new \Exception('Could not find a successful response in default.yml');
    }
}
