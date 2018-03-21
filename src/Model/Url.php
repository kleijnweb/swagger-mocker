<?php declare(strict_types=1);

namespace Zwartpet\SwaggerMockerBundle\Model;

class Url
{
    /**
     * @var string
     */
    private $scheme;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $port;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $query;

    /**
     * @var string
     */
    private $fragment;

    /**
     * @param null|string $uri
     */
    public function __construct(string $uri = null)
    {
        if (null !== $uri) {
            $segments = parse_url($uri);

            foreach ($segments as $property => $value) {
                $this->$property = $value;
            }
        }
    }

    /**
     * @param \stdClass $definition
     * @return Url
     */
    public static function fromDefinition(\stdClass $definition): Url
    {

    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     * @return Url
     */
    public function setScheme(string $scheme): Url
    {
        $this->scheme = $scheme;
        return $this;
    }

    /**
     * @return string
     */
    public function getUser(): string
    {
        return $this->user;
    }

    /**
     * @param string $user
     * @return Url
     */
    public function setUser(string $user): Url
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return Url
     */
    public function setPassword(string $password): Url
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return Url
     */
    public function setHost(string $host): Url
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @param string $port
     * @return Url
     */
    public function setPort(string $port): Url
    {
        $this->port = $port;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return Url
     */
    public function setPath(string $path): Url
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @param string $query
     * @return Url
     */
    public function setQuery(string $query): Url
    {
        $this->query = $query;
        return $this;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $fragment
     * @return Url
     */
    public function setFragment(string $fragment): Url
    {
        $this->fragment = $fragment;
        return $this;
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
