<?php

declare(strict_types=1);

namespace Wazum\TransactionalDataHandler\Database;

final class Connection extends \TYPO3\CMS\Core\Database\Connection
{
    /**
     * @var array<array-key, array{
     *     tableName: string,
     *     cascade: bool
     * }>
     */
    private array $truncateQueue = [];

    /**
     * Store all truncate statements in a queue,
     * as a TRUNCATE would commit the currently open transaction.
     */
    public function truncate(string $tableName, bool $cascade = false): int
    {
        $this->truncateQueue[] = [
            'tableName' => $tableName,
            'cascade' => $cascade,
        ];

        return 1;
    }

    public function executeTruncateQueue(): void
    {
        foreach ($this->truncateQueue as $item) {
            parent::truncate($item['tableName'], $item['cascade']);
        }
    }
}
