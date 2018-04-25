<?php declare(strict_types=1);

namespace Zwartpet\SwaggerMockerBundle\Service;

use League\JsonGuard\Validator;
use League\JsonReference\Dereferencer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use Zwartpet\SwaggerMockerBundle\Model\StubRequest;
use Zwartpet\SwaggerMockerBundle\Model\StubResponse;

class StubMatcher
{
    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var string
     */
    private $stubDirectory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array
     */
    private $stubDefinitions = [];

    /**
     * @param Filesystem      $fileSystem
     * @param string          $stubDirectory
     * @param LoggerInterface $logger
     */
    public function __construct(Filesystem $fileSystem, string $stubDirectory, LoggerInterface $logger)
    {
        $this->fileSystem    = $fileSystem;
        $this->stubDirectory = $stubDirectory;
        $this->logger        = $logger;

        $stubSchema = $this->loadDefinition(__DIR__ . '/../../app/config/schema/stubs.json');

        foreach (new \DirectoryIterator($stubDirectory) as $stubFile) {

            if ($stubFile->isDot() || $stubFile->isDir()) {
                continue;
            }

            $stubDefinitions = $this->loadDefinition($stubFile->getPathname());

            $this->logger->info(sprintf("Loaded %d stubs", count($this->stubDefinitions)));

            $validator = new Validator($stubDefinitions, $stubSchema);

            if ($validator->fails()) {
                foreach ($validator->errors() as $error) {
                    $this->logger->warning("{$error->getDataPath()}: {$error->getMessage()}");
                }
                throw new \UnexpectedValueException("Stub file '{$stubFile->getPathname()}' is invalid");
            }

            $this->stubDefinitions = array_merge($this->stubDefinitions, $stubDefinitions);
        }
    }

    /**
     * @param StubRequest $request
     *
     * @return StubResponse|null
     */
    public function getStubResponse(StubRequest $request)
    {
        $requestDefinition = $request->toDefinition();

        foreach ($this->stubDefinitions as $stubDefinition) {

            $validator = new Validator($requestDefinition, $stubDefinition->match);

            if (!$validator->fails()) {
                return StubResponse::fromDefinition($stubDefinition->response);
            }
        }

        return null;
    }

    /**
     * @param string $filePath
     *
     * @return \stdClass|array
     */
    public function loadDefinition(string $filePath)
    {
        $this->logger->info("Loading definition in file '$filePath'");
        return (Dereferencer::draft4())->dereference($this->parseDefinition($filePath), "file://$filePath");
    }

    /**
     * @param string $filePath
     *
     * @return \stdClass|array
     */
    public function parseDefinition(string $filePath)
    {
        if (preg_match('/\.(yml|yaml)$/', $filePath)) {
            $result = Yaml::parse(file_get_contents($filePath), Yaml::PARSE_OBJECT | Yaml::PARSE_OBJECT_FOR_MAP);
        } elseif (preg_match('/\.json$/', $filePath)) {
            $responseDefinition = json_decode(file_get_contents($filePath));

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException("Failed decoding $filePath");
            }
            $result = $responseDefinition;
        } else {
            throw new \InvalidArgumentException(
                "File path must be either JSOn or YAML (end in *.json, *.yaml or *.yml, got '$filePath')"
            );
        }

        if (!is_array($result) && !$result instanceof \stdClass) {
            throw  new \UnexpectedValueException("Parse result must be an array ");
        }

        return $result;
    }
}
