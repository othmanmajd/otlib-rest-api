<?php

declare(strict_types=1);

namespace Otlib\RestApi\Repository;

use TYPO3\CMS\Core\Crypto\PasswordHashing\PasswordHashFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FeUserRepository extends AbstractRepository
{
    protected string $tableName = 'fe_users';

    public function checkUserPassword(string $username, string $passwordToCheck): bool
    {
        $hashInstance = GeneralUtility::makeInstance(PasswordHashFactory::class)->getDefaultHashInstance('FE');
        $queryBuilder = $this->getQueryBuilderForTable($this->tableName);
        $passwordInDb = $queryBuilder->select('password')
            ->from($this->tableName)
            ->orWhere(
                $queryBuilder->expr()->eq('username', $queryBuilder->createNamedParameter($username)),
                $queryBuilder->expr()->eq('email', $queryBuilder->createNamedParameter($username)),
            )->executeQuery()
            ->fetchOne();
        return $hashInstance->checkPassword($passwordToCheck, (string)$passwordInDb);
    }
}
