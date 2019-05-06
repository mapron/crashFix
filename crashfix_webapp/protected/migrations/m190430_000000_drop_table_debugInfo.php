<?php

class m190430_000000_drop_table_debugInfo extends CDbMigration
{
	public function up()
	{
		$this->dropTable('{{debuginfo}}');
		return true;
	}

	public function down()
	{
		echo "m190430_000000_drop_table_debugInfo doesn't support migration down.\n";
		return false;
	}
}
