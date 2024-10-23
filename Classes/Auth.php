<?php

declare(strict_types=1);

namespace Otlib\RestApi;

use Otlib\RestApi\Enumeration\AuthType;
use Otlib\RestApi\Repository\FeUserRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class Auth
{
    public function __construct(private readonly FeUserRepository $feUserRepository)
    {
    }

    public function isUserAuthenticated(ServerRequestInterface $request, AuthType $authType): bool
    {
        $authHeader = $request->getHeaderLine('Authorization');
        switch ($authType) {
            case AuthType::BEARER:
                $token = substr($authHeader, 7);
                return $this->validateToken($token);
            case AuthType::BASIC:
                $base64Credentials = substr($authHeader, 6);
                $credentials = base64_decode($base64Credentials);
                if ((bool)$credentials === false) {
                    return false;
                }
                [$username, $password] = explode(':', $credentials, 2);
                return $this->validateCredentials($username, $password);
            case AuthType::FRONTEND_TYPO3_USER:
                /** @var FrontendUserAuthentication $frontendUser */
                $frontendUser = $request->getAttribute('frontend.user');
                $username = $request->getParsedBody()['user'] ?? '';
                $password = $request->getParsedBody()['pass'] ?? '';
                // Check if the user is logged in to the front-end, if not, check if the username and password are correct.
                if ($frontendUser->getUserId() > 0 && $frontendUser->getUserName() !== '') {
                    return true;
                }

                $loginStatus = $this->feUserRepository->checkUserPassword($username, $password);

                return $loginStatus;

            default:
                return false;
        }
    }

    protected function validateCredentials(string $username, string $password): bool
    {
        $validUsername = $_SERVER['OTLIB_AUTH_API_USERNAME'] ?? 'THERE IS NO USERNAME';
        $validPassword = $_SERVER['OTLIB_AUTH_API_PASSWORD'] ?? md5((string)time());

        return $username === $validUsername && $password === $validPassword;
    }

    protected function validateToken(string $token): bool
    {
        return $token === ($_SERVER['OTLIB_AUTH_API_TOKEN'] ?? 'THERE IS NO TOKEN' . md5((string)time()));
    }
}
