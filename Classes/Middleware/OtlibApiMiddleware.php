<?php

declare(strict_types=1);

namespace Otlib\RestApi\Middleware;

use Otlib\RestApi\Api;
use Otlib\RestApi\Auth;
use Otlib\RestApi\Enumeration\AuthType;
use Otlib\RestApi\Event\BeforeResponseEvent;
use Otlib\RestApi\Exception\InvalidRequestMethodException;
use Otlib\RestApi\Exception\MethodNotFoundException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class OtlibApiMiddleware implements MiddlewareInterface
{
    /**
     * @var array<Api>|Api[]
     */
    private static array $apis = [];

    public function __construct(Api $api, protected Auth $auth, private readonly EventDispatcherInterface $eventDispatcher)
    {
        self::$apis = $api::getApis();
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        if (!$this->isApiRequest($uri)) {
            return $handler->handle($request);
        }
        foreach (self::$apis as $api) {
            if ($uri === '/' . $api->getPath()) {
                $authorized = true;
                switch ($api->getAuthType()->value) {
                    case AuthType::BEARER->value:
                        if ($api->getAuthType()->name !== AuthType::NONE->name) {
                            $request = $this->auth->validateBearerToken($request);
                            if (!$request->getAttribute('otlibApiUser', null)) {
                                $authorized = false;
                            }
                        }
                        break;
                    default:
                        if ($api->getAuthType()->name !== AuthType::NONE->name &&
                            !$this->auth->isUserAuthenticated($request, $api->getAuthType())) {
                            $authorized = false;
                        }
                        break;
                }
                // Adjust the current request using the BeforeResponseEvent
                $request = $this->initBeforeResponseEvent($request);

                if (!$authorized) {
                    return $this->createJsonResponseWithNoCache(
                        ['message' => '401 Unauthorized'],
                        401,
                        ['WWW-Authenticate' => ucfirst($api->getAuthType()->value) . ' realm="Access denied"']
                    );
                }
                if (strtolower($api->getRequestMethod()) !== strtolower($request->getMethod())) {
                    throw new InvalidRequestMethodException('Error: Invalid order method');
                }

                $controller = $api->getController();
                $method = $api->getMethod();
                $constructorArguments = $api->getConstructorArguments();
                $controllerInstance = GeneralUtility::makeInstance($controller, ...$constructorArguments); //@phpstan-ignore-line
                if (!method_exists($controllerInstance, $method)) {
                    throw new MethodNotFoundException('The method does not exist.', 1729100035);
                }
                $data = $controllerInstance->$method($request);
                if ($data instanceof JsonResponse) {
                    return $api->isHeaderWithNoCache() ? $this->addNoCacheHeaders($data) : $data;
                }

                return $api->isHeaderWithNoCache()
                    ? $this->addNoCacheHeaders(new JsonResponse(['message' => 'Invalid data format'], 400))
                    : new JsonResponse(['message' => 'Invalid data format'], 400);
            }
        }

        return $this->addNoCacheHeaders(new JsonResponse(['message' => 'Access denied!'], 403));
    }

    protected function isApiRequest(string $uri): bool
    {
        $isApiRequest = false;
        foreach (self::$apis as $api) {
            if (str_starts_with($uri, '/' . trim($api->getPathPrefix(), '/'))) {
                $isApiRequest = true;
                break;
            }
        }
        return $isApiRequest;
    }

    /**
     * Adds no-cache headers to a JsonResponse.
     */
    protected function addNoCacheHeaders(JsonResponse $response): JsonResponse
    {
        return $response
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Pragma', 'no-cache')
            ->withHeader('Expires', '0');
    }

    /**
     * Creates a JsonResponse with the specified data and status code, and applies no-cache headers.
     *
     * @param array<string,mixed> $data
     * @param array<string,string> $headers
     */
    protected function createJsonResponseWithNoCache(array $data, int $status = 200, array $headers = []): JsonResponse
    {
        $response = new JsonResponse($data, $status, $headers);
        return $this->addNoCacheHeaders($response);
    }

    private function initBeforeResponseEvent(ServerRequestInterface $request): ServerRequestInterface
    {
        $event = new BeforeResponseEvent($request);
        $event = $this->eventDispatcher->dispatch($event);

        $modifiedRequest = $event->getRequest();
        foreach ($event->getAttributes() as $key => $value) {
            $modifiedRequest = $modifiedRequest->withAttribute($key, $value);
        }

        return $modifiedRequest;
    }
}
