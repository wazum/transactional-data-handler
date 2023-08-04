<?php

$EM_CONF['transactional_data_handler'] = [
    'title' => 'TYPO3 CMS Transactional DataHandler',
    'description' => 'Wrap TYPO3 CMS DataHandler processing inside database transaction',
    'category' => 'backend',
    'author' => 'Wolfgang Klinger',
    'author_email' => 'wolfgang@wazum.com',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author_company' => 'wazum.com',
    'version' => '10.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-10.9.99',
        ],
    ],
];
