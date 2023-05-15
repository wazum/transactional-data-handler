<?php

declare(strict_types=1);

namespace Wazum\TransactionalDataHandler\Backend;

enum DataHandlerMapType: string
{
    case DATA = 'data';
    case COMMAND = 'cmd';
}
