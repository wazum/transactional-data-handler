<?php

declare(strict_types=1);

use TYPO3\CMS\Core\DataHandling\DataHandler;
use Wazum\TransactionalDataHandler\Backend\TransactionalDataHandler;
use Wazum\TransactionalDataHandler\Database\Connection;

(static function (): void {
    // Use a custom Connection class
    $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['wrapperClass'] = Connection::class;
    // Extend the Core DataHandler class
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][DataHandler::class] = [
        'className' => TransactionalDataHandler::class,
    ];
})();
