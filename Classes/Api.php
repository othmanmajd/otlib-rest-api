<?php

declare(strict_types=1);

namespace Otlib\RestApi;

use Otlib\RestApi\Enumeration\AuthType;
use Otlib\RestApi\Exception\ApiPathNotSetException;
use Otlib\RestApi\Interface\ApiInterface;

class Api implements ApiInterface
{
    /**
     * @var array<Api>
     */
    public static array $apis = [];

    protected string $pathPrefix = 'api';

    protected ?string $path = null;

    protected string $controller;

    /** @var array<mixed> */
    protected array $constructorArguments = [];

    protected string $method;

    protected string $requestMethod = 'GET';

    protected AuthType $authType = AuthType::NONE;

    protected bool $headerWithNoCache = true;

    public static function newApi(string $path): self
    {
        $instance = new self();
        $instance->setPath($path);
        self::$apis[] = $instance;
        return $instance;
    }

    /**
     * @return Api[]|array<Api>
     */
    public static function getApis(): array
    {
        return self::$apis;
    }

    public function setController(string $controller): self
    {
        $this->controller = $controller;
        return $this;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function setRequestMethod(string $method): self
    {
        $this->requestMethod = $method;
        return $this;
    }

    public function getController(): string
    {
        return $this->controller;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    public function getPathPrefix(): string
    {
        return $this->pathPrefix;
    }

    public function setPathPrefix(string $pathPrefix = 'api'): self
    {
        $this->pathPrefix = $pathPrefix;
        return $this;
    }

    public function getPath(): string
    {
        if ($this->path === null) {
            throw new ApiPathNotSetException();
        }

        return trim($this->getPathPrefix(), '/') . '/' . $this->path;
    }

    public function setPath(string $path): self
    {
        if (empty($path)) {
            throw new ApiPathNotSetException();
        }
        $this->path = $path;
        return $this;
    }

    public function getConstructorArguments(): array
    {
        return $this->constructorArguments;
    }

    /**
     * @param array<mixed> $constructorArguments
     */
    public function setConstructorArguments(array $constructorArguments): self
    {
        $this->constructorArguments = $constructorArguments;
        return $this;
    }

    public function setAuthType(AuthType $authType = AuthType::NONE): self
    {
        $this->authType = $authType;
        return $this;
    }

    public function getAuthType(): AuthType
    {
        return $this->authType;
    }

    public function isHeaderWithNoCache(): bool
    {
        return $this->headerWithNoCache;
    }

    public function setHeaderWithNoCache(bool $headerWithNoCache): void
    {
        $this->headerWithNoCache = $headerWithNoCache;
    }
}
