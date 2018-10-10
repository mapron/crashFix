<?php

class m181010_180000_add_crashreport_username extends CDbMigration
{
    // Use safeUp/safeDown to do migration with transaction
    public function safeUp()
    {
        $this->addColumn(
            '{{crashreport}}',
            'username',
            'VARCHAR(128) DEFAULT NULL'
        );
        return true;
    }

    public function safeDown()
    {
        echo "m181010_180000_add_crashreport_username doesn't support migration down.\n";
        return false;
    }
}
