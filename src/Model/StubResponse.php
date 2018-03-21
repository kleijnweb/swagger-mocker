<?php declare(strict_types=1);

namespace Zwartpet\SwaggerMockerBundle\Model;

use Symfony\Component\HttpFoundation\Response;

class StubResponse extends StubMessage
{
    /**
     * @var integer
     */
    private $status;

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     * @return StubResponse
     */
    public function setStatus(int $status): StubResponse
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @param \stdClass $definition
     * @return StubResponse
     */
    public static function fromDefinition(\stdClass $definition): StubResponse
    {
        $base = parent::fromDefinition($definition);
        $base->setStatus($definition->status);

        return $base;
    }

    /**
     * @return \stdClass
     */
    public function toDefinition(): \stdClass
    {
        $definition = parent::toDefinition();

        foreach ($this as $propertyName => $value) {
            $definition->$propertyName = $value;
        }

        return $definition;
    }

    /**
     * @return Response
     */
    public function toHttpFoundation(): Response
    {
        $body = is_string($this->getBody()) ? $this->getBody() : json_encode($this->getBody());

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \UnexpectedValueException("JSON encoding error");
        }

        if (!$this->getHeaders()) {
            $this->setHeaders((object)[]);
        }
        if (!isset($this->getHeaders()->{'Content-Type'})) {
            $this->getHeaders()->{'Content-Type'} = 'application/json';
        }
        return new Response(
            $body,
            $this->getStatus(),
            (array)$this->getHeaders()
        );
    }
}
