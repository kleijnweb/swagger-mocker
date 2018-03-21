<?php declare(strict_types=1);

namespace Zwartpet\SwaggerMockerBundle\Model;

abstract class StubMessage
{
    /**
     * @var \stdClass|null
     */
    private $headers;

    /**
     * @var mixed
     */
    private $body;

    /**
     * @return null|\stdClass
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param null|\stdClass $headers
     * @return static
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $body
     * @return static
     */
    public function setBody($body): StubMessage
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @param \stdClass $definition
     * @return static
     */
    public static function fromDefinition(\stdClass $definition)
    {
        $self = new static();

        if (isset($definition->body)) {
            $self->setBody($definition->body);
        }
        if (isset($definition->headers)) {
            $self->setHeaders($definition->headers);
        }

        return $self;
    }

    /**
     * @return \stdClass
     */
    public function toDefinition(): \stdClass
    {
        $definition = (object)[];
        foreach ($this as $propertyName => $value) {
            if ($value !== null) {
                $definition->$propertyName = $value;
            }
        }

        return $definition;
    }
}
