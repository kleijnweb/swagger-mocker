<?php declare(strict_types=1);

namespace Zwartpet\SwaggerMockerBundle\Model;

use Symfony\Component\HttpFoundation\Request;

class StubRequest extends StubMessage
{
    const METHOD_OPTIONS = 'OPTIONS';
    const METHOD_GET     = 'GET';
    const METHOD_HEAD    = 'HEAD';
    const METHOD_POST    = 'POST';
    const METHOD_PUT     = 'PUT';
    const METHOD_DELETE  = 'DELETE';
    const METHOD_TRACE   = 'TRACE';
    const METHOD_CONNECT = 'CONNECT';

    /**
     * @var Url
     */
    private $url;

    /**
     * @var string
     */
    private $method;

    /**
     * @return Url
     */
    public function getUrl(): Url
    {
        return $this->url;
    }

    /**
     * @param Url $url
     * @return StubRequest
     */
    public function setUrl(Url $url): StubRequest
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param Request $httpFoundationRequest
     * @return StubRequest
     */
    public static function fromHttpFoundation(Request $httpFoundationRequest): StubRequest
    {
        $self = new self();
        $self->setMethod($httpFoundationRequest->getMethod());

        return $self;
    }

    /**
     * @param \stdClass $definition
     * @return StubRequest
     */
    public static function fromDefinition(\stdClass $definition): StubRequest
    {
        $base = parent::fromDefinition($definition);
        $base->setUrl(Url::fromDefinition($definition->url));

        return $base;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return StubRequest
     */
    public function setMethod(string $method): StubRequest
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @return \stdClass
     */
    public function toDefinition(): \stdClass
    {
        $definition = parent::toDefinition();

        foreach ($this as $propertyName => $value) {
            if ($value !== null) {
                $definition->$propertyName = $value;
            }
        }

        $definition->url = $definition->url->toDefinition();

        return $definition;
    }
}
