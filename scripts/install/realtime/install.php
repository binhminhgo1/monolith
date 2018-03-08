<?php

namespace go1\monolith;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use go1\util\DB;

return function (Connection $db) {
    DB::install($db, [
        function (Schema $schema) {
            if (!$schema->hasTable('notification')) {
                $notification = $schema->createTable('notification');
                $notification->addColumn('id', Type::INTEGER, ['unsigned' => true, 'autoincrement' => true]);
                $notification->addColumn('pid', Type::INTEGER, ['unsigned' => true]);
                $notification->addColumn('instance_id', Type::INTEGER, ['unsigned' => true]);
                $notification->addColumn('created', Type::INTEGER, ['unsigned' => true]);
                $notification->addColumn('updated', Type::INTEGER, ['unsigned' => true]);
                $notification->addColumn('data', Type::BLOB);
                $notification->setPrimaryKey(['id']);
                $notification->addIndex(['pid']);
                $notification->addIndex(['instance_id']);
                $notification->addIndex(['created']);
                $notification->addIndex(['updated']);
            }
        }
    ]);
};
