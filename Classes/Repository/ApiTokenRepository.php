<?php

declare(strict_types=1);

namespace Otlib\RestApi\Repository;

class ApiTokenRepository extends AbstractRepository
{
    protected string $tableName = 'tx_otlib_api_tokens';

    /**
     * @return array<string,mixed>
     * @throws \Doctrine\DBAL\Exception
     */
    public function getTokenData(string $selector): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable($this->tableName);
        $row = $queryBuilder->select('*')
            ->from($this->tableName)
            ->where($queryBuilder->expr()->eq('selector', $queryBuilder->createNamedParameter($selector)))
            ->executeQuery()
            ->fetchAssociative();
        if (!$row) {
            return [];
        }
        return $row;
    }
}
