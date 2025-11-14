<?php

declare(strict_types=1);

namespace Otlib\RestApi\Event;

use Psr\Http\Message\ServerRequestInterface;

final class BeforeResponseEvent
{
    private ServerRequestInterface $request;

    private array $additionalAttributes = [];

    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
    }

    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function setRequest(ServerRequestInterface $request): void
    {
        $this->request = $request;
    }

    public function addAttribute(string $key, mixed $value): void
    {
        $this->additionalAttributes[$key] = $value;
    }

    public function getAttributes(): array
    {
        return $this->additionalAttributes;
    }
}
