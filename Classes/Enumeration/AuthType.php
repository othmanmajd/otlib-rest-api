<?php

declare(strict_types=1);

namespace Otlib\RestApi\Enumeration;

enum AuthType: string
{
    case NONE = 'none';
    case BASIC = 'basic';
    case BEARER = 'bearer';
    case FRONTEND_TYPO3_USER = 'fe_typo_user';

    public function getDescription(): string
    {
        return match ($this) {
            self::NONE => 'No authentication required.',
            self::BASIC => 'Basic Authentication using username and password.',
            self::BEARER => 'Bearer Token Authentication.',
            self::FRONTEND_TYPO3_USER => 'TYPO3 Frontend User.',
        };
    }
}
