<?php

declare(strict_types=1);

namespace Otlib\RestApi\Repository;

use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;

abstract class AbstractRepository
{
    public function __construct(protected readonly ConnectionPool $connectionPool)
    {
    }

    protected function getConnection(string $tableName): Connection
    {
        return $this->connectionPool->getConnectionForTable($tableName);
    }

    protected function getQueryBuilderForTable(string $tableName): QueryBuilder
    {
        return $this->connectionPool->getQueryBuilderForTable($tableName);
    }
}
