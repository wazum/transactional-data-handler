<?php

declare(strict_types=1);

namespace Wazum\TransactionalDataHandler\Backend;

use Doctrine\DBAL\ConnectionException;
use Throwable;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\SysLog\Action\Database as SystemLogDatabaseAction;
use TYPO3\CMS\Core\SysLog\Error as SystemLogErrorClassification;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Wazum\TransactionalDataHandler\Database\Connection;

/**
 * @psalm-suppress InternalProperty
 * @psalm-suppress InternalMethod
 */
final class TransactionalDataHandler extends DataHandler
{
    /**
     * @throws ConnectionException|\Throwable
     */
    public function process_datamap(): void
    {
        $this->processWithTransaction(mapType: DataHandlerMapType::DATA);
    }

    /**
     * @throws ConnectionException|\Throwable
     */
    public function process_cmdmap(): void
    {
        $this->processWithTransaction(mapType: DataHandlerMapType::COMMAND);
    }

    /**
     * @throws \Throwable
     */
    private function processWithTransaction(DataHandlerMapType $mapType): void
    {
        $connection = $this->getConnection();
        $method = "process_{$mapType->value}map";

        if (null === $connection || !$this->shouldProcess("{$mapType->value}map")) {
            // Let the parent handle the processing wrapped in the open transaction
            $this->processWithParent($method);

            return;
        }

        try {
            $this->processTransactional($connection, $method);
            $connection->executeTruncateQueue();
        } catch (\Throwable $e) {
            $this->recordLogMessages($e);
        }
    }

    /**
     * @throws \Throwable
     */
    private function processTransactional(Connection $connection, string $method): void
    {
        $connection->setAutoCommit(false);
        try {
            $connection->transactional(function () use ($method): void {
                $this->logger->info('Execution of DataHandler started within a transaction.');

                $this->processWithParent($method);

                if (!empty($this->errorLog) && $this->shouldThrowExceptionOnErrors()) {
                    $message = 'Errors occurred while DataHandler processing. Rolling back the changes.';
                    $this->logger->error($message, $this->errorLog);
                    throw new \RuntimeException($message);
                }
            });
        } finally {
            $connection->setAutoCommit(true);
        }

        $this->logger->info('The execution of DataHandler within a transaction finished successful.');
    }

    private function processWithParent(string $method): void
    {
        parent::$method();
    }

    private function shouldProcess(string $property): bool
    {
        return
            $this->isOuterMostInstance()
            && !empty($this->{$property});
    }

    private function shouldThrowExceptionOnErrors(): bool
    {
        try {
            return (bool) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get(
                'transactional_data_handler',
                'throw_exception_on_error_log_entries'
            );
        } catch (Throwable) {
            // Ignore
        }

        return false;
    }

    private function getConnection(): ?Connection
    {
        try {
            return GeneralUtility::makeInstance(ConnectionPool::class)
                ->getConnectionByName(ConnectionPool::DEFAULT_CONNECTION_NAME);
        } catch (Throwable) {
            return null;
        }
    }

    /*
     * Writes all recorded log messages to the log (database) after the transaction is rolled back
     */
    private function recordLogMessages(\Throwable $e): void
    {
        foreach ($this->errorLog as $message) {
            $this->log('', 0, SystemLogDatabaseAction::CHECK, 0, SystemLogErrorClassification::SYSTEM_ERROR, $message);
        }
        $this->log('', 0, SystemLogDatabaseAction::CHECK, 0, SystemLogErrorClassification::SYSTEM_ERROR, $e->getMessage());
    }
}
