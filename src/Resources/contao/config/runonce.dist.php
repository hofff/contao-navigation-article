<?php

declare(strict_types=1);

use Contao\System;
use Doctrine\DBAL\Connection;

(function (Connection $connection) {
    $columns = $connection->getSchemaManager()->listTableColumns('tl_hofff_navi_art');

    if (!isset($columns['cssid'])) {
        return;
    }

    if ($columns['cssid']->getName() === 'cssID') {
        $connection->executeQuery('ALTER TABLE tl_hofff_navi_art DROP cssID');
    }

    \dump($connection->getSchemaManager()->listTableColumns('tl_hofff_navi_art'));
})(System::getContainer()->get('database_connection'));