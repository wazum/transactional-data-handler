# Transactional Data Handler

This extension wraps all _DataHandler_ process calls (`process_datamap`, `process_cmdmap`) into one database transaction so that all changes are rolled back if an error (see below what that means) occurs during the process.

Of course, this also works if there is a timeout (e.g. PHP), since the database will not commit the changes to the tables in this case.

Use at your own risk!

## Installation

Require the package with composer installed:

    composer require "wazum/transactional-data-handler"

Add the following settings to your global setup (`AdditionalConfiguration.php`)
if you get error messages in the TYPO3 CMS log file that no suitable Connection could be instantiated:

    $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections']['Default']['wrapperClass'] = \Wazum\TransactionalDataHandler\Database\Connection::class;

## Errors and exceptions

If the corresponding setting is set (see below), any entries in the `DataHandler` error log that happened during the processing will cause a rollback in the database.

Otherwise only real PHP exceptions will lead to a rollback in the database.

## Extension settings

### Throw exception on DataHandler error log entries

    # cat=basic/enable; type=boolean; label=Throw exception when DataHandler's error log is not empty after processing
    throw_exception_on_error_log_entries = 0

Change this if you want to always rollback the transaction if the DataHandler's error log contains anything.

Default value is `0` (the transaction is reset only in case of actually raised (and not previously caught) PHP exceptions).

## Possible problems

### Error log

Take a look at the TYPO3 CMS error log in case of problems.

### Database locks

If you encounter any database locking errors, take a look at the transaction settings section below.
Also check for missing indexes on your tables that slow down any database transactions.

### Extending DataHandler

This extension extends the Core `DataHandler` class (_XCLASS_). If you use any other extension that does the same, you have to solve this yourself somehow.

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][DataHandler::class] = [
        'className' => TransactionalDataHandler::class
    ];

### MySQL Storage engine and transaction settings

InnoDB supports transactions, which means you can commit and rollback. MyISAM does not (even if you get no errors)!

It makes sense to set the following in your MySQL/MariaDB configuration if you are expecting a transaction to auto-rollback when it encounters an InnoDB lock wait error:

    innodb_rollback_on_timeout=1

Verify after server restart:

    mysql> SHOW GLOBAL VARIABLES LIKE 'innodb_rollback_on_timeout';
    +----------------------------+-------+
    | Variable_name              | Value |
    +----------------------------+-------+
    | innodb_rollback_on_timeout | ON    |
    +----------------------------+-------+

You may also want to decrease the value for `innodb_lock_wait_timeout`, you can read more about that [here](https://dev.mysql.com/doc/refman/8.0/en/innodb-parameters.html#sysvar_innodb_lock_wait_timeout)

Another configuration change to consider is setting the [transaction isolation level](https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_transaction_isolation) to `READ COMMITTED`,

    [mysqld] 
    transaction-isolation = READ-COMMITTED

You can read more about that [here](https://dev.mysql.com/doc/refman/8.0/en/innodb-transaction-isolation-levels.html)

More about InnoDB error handling [here](https://dev.mysql.com/doc/refman/8.0/en/innodb-error-handling.html).

### Implicit commit

[Certain SQL statements lead to an implicit commit](https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html) of the currently open transaction (e.g. a `TRUNCATE`).
The `TRUNCATE` case is already handled by this extension (the statements will be executed after the transaction was successfully committed).

Check your code and the code of used extensions if the transaction is committed and not rolled back even in case of a failure exception.

### Changes that do not affect the database

If any _DataHandler_ hooks or related parts change anything in the filesystem (e.g. moving files) or through an API, those changes are obviously not rolled back.

## Say thanks! and support me

You like this extension? Get something for me (surprise! surprise!) from my wishlist on [Amazon](https://smile.amazon.de/hz/wishlist/ls/307SIOOD654GF/) or [help me pay](https://www.paypal.me/wazum) the next pizza or Pho soup (mjam). Thanks a lot!
