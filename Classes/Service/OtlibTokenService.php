<?php

declare(strict_types=1);

namespace Otlib\RestApi\Service;

use TYPO3\CMS\Core\Database\ConnectionPool;

readonly class OtlibTokenService
{
    public function __construct(private ConnectionPool $connectionPool)
    {
    }

    /**
     * @return array<string,mixed>
     * @throws \Random\RandomException
     */
    public function createTokenForUser(int $userUid, string $scopes = 'read,write', int $validDays = 30): array
    {
        $ttl = 60 * 60 * 24 * $validDays;
        $expires = time() + $ttl;

        // generate selector + validator
        $selector = bin2hex(random_bytes(12)); // 24 chars hex
        $validator = rtrim(strtr(base64_encode(random_bytes(48)), '+/', '-_'), '='); // url-safe
        // hash the validator (use sha256 then password_hash OR password_hash directly)
        $validatorHash = password_hash($validator, PASSWORD_DEFAULT);

        // save to DB
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('tx_otlib_api_tokens');
        $queryBuilder->insert('tx_otlib_api_tokens')
            ->values([
                'selector' => $selector,
                'validator_hash' => $validatorHash,
                'user_uid' => $userUid,
                'scopes' => $scopes,
                'expires' => $expires,
                'revoked' => 0,
                'crdate' => time(),
            ])->executeStatement();

        // return token as selector.validator
        $token = $selector . '.' . $validator;

        return [
            'token' => $token,
            'expires_at' => date('c', $expires),
            'scopes' => explode(',', $scopes),
        ];
    }
}
