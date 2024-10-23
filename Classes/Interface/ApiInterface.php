<?php

declare(strict_types=1);

namespace Otlib\RestApi\Interface;

use Otlib\RestApi\Enumeration\AuthType;

interface ApiInterface
{
    public function setController(string $controller): self;

    public function setMethod(string $method): self;

    public function setRequestMethod(string $method): self;

    public function setPath(string $path): self;

    public function setPathPrefix(string $pathPrefix): self;

    public function getController(): string;

    public function getMethod(): string;

    public function getRequestMethod(): string;

    public function getPath(): ?string;

    public function getPathPrefix(): string;

    /**
     * @return array<mixed>
     */
    public function getConstructorArguments(): array;

    public function setAuthType(AuthType $authType): self;

    public function getAuthType(): AuthType;
}
