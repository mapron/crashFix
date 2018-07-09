<?php

class m180709_185600_add_crashgroup_deleteCount extends CDbMigration
{

	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
        $this->addColumn(
                '{{crashgroup}}', 
                'deletedCount', 
                'INTEGER NOT NULL DEFAULT 0'
                );
        
        return true;
	}

	public function safeDown()
	{
        echo "m180709_185600_add_crashgroup_deleteCount does not support migration down.\n";
		return false;
	}
	
}