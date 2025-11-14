<?php

declare(strict_types=1);

namespace Otlib\RestApi;

use Otlib\RestApi\Enumeration\AuthType;
use Otlib\RestApi\Repository\ApiTokenRepository;
use Otlib\RestApi\Repository\FeUserRepository;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

class Auth
{
    public function __construct(
        private readonly FeUserRepository $feUserRepository,
        private readonly ApiTokenRepository $apiTokenRepository
    ) {
    }

    public function isUserAuthenticated(ServerRequestInterface $request, AuthType $authType): bool|ServerRequestInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');
        switch ($authType) {
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

                return $this->feUserRepository->checkUserPassword($username, $password);

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

    public function validateBearerToken(ServerRequestInterface $request): ServerRequestInterface
    {
        $auth = $request->getHeaderLine('Authorization');
        if (empty($auth) || stripos($auth, 'Bearer ') !== 0) {
            return $request;
        }

        $bearer = substr($auth, 7);
        [$selector, $validator] = explode('.', $bearer . '.');

        if (empty($selector) || empty($validator)) {
            return $request;
        }

        $row = $this->apiTokenRepository->getTokenData($selector);

        if (!$row) {
            return $request;
        }

        // check revoked / expiry
        if ((int)$row['revoked'] === 1 || (int)$row['expires'] < time()) {
            return $request;
        }

        // verify validator via password_verify to compare with saved hash
        if (!password_verify($validator, $row['validator_hash'])) {
            return $request;
        }

        // token ok -> attach user info to request for downstream controllers
        return $request->withAttribute('otlibApiUser', [
            'user_uid' => (int)$row['user_uid'],
            'user' => $this->feUserRepository->getUserByUid(((int)$row['user_uid'])),
            'scopes' => array_filter(array_map('trim', explode(',', $row['scopes'] ?? ''))),
        ]);
    }
}
