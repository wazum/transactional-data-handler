<?php

// Use a custom Connection class
$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['wrapperClass']
    = Wazum\TransactionalDataHandler\Database\Connection::class;
// Extend the Core DataHandler class
$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][TYPO3\CMS\Core\DataHandling\DataHandler::class] = [
    'className' => Wazum\TransactionalDataHandler\Backend\TransactionalDataHandler::class,
];
