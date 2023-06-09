# Transactional Data Handler

This extension wraps all _DataHandler_ process calls (`process_datamap`, `process_cmdmap`) into one database transaction so that all changes are rolled back if an error (see below what that means) occurs during the process.

Of course, this also works if there is a timeout (e.g. PHP), since the database will not commit the changes to the tables in this case.

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

### Extending DataHandler

This extension extends the Core `DataHandler` class (_XCLASS_). If you use any other extension that does the same, you have to solve this yourself somehow.

    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][DataHandler::class] = [
        'className' => TransactionalDataHandler::class
    ];

### Implicit commit

[Certain SQL statements lead to an implicit commit](https://dev.mysql.com/doc/refman/8.0/en/implicit-commit.html) of the currently open transaction (e.g. a `TRUNCATE`).
The `TRUNCATE` case is already handled by this extension (the statements will be executed after the transaction was successfully committed).

Check your code and the code of used extensions if the transaction is committed and not rolled back even in case of a failure exception.

### Changes that do not affect the database

If any _DataHandler_ hooks or related parts change anything in the filesystem (e.g. moving files) or through an API, those changes are obviously not rolled back.

## Say thanks! and support me

You like this extension? Get something for me (surprise! surprise!) from my wishlist on [Amazon](https://smile.amazon.de/hz/wishlist/ls/307SIOOD654GF/) or [help me pay](https://www.paypal.me/wazum) the next pizza or Pho soup (mjam). Thanks a lot!
