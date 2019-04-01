<?php

class m190401_000000_add_pending_delete extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
	    $this->insert('{{lookup}}',
	        array(
	            'name'=>'Pending delete',
	            'type'=>'CrashReportStatus',
	            'code'=>5,
	            'position'=>5,
	        ));
		return true;
	}

	public function safeDown()
	{
		echo "m190401_000000_add_pending_delete doesn't support migration down.\n";
		return false;
	}
}
