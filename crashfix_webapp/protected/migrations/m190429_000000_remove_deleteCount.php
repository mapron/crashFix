<?php

class m190429_000000_remove_deleteCount extends CDbMigration
{
	public function up()
	{
		$this->dropColumn(
			'{{crashgroup}}', 
			'deletedCount'
			);
		return true;
	}

	public function down()
	{
		echo "m190429_000000_remove_deleteCount doesn't support migration down.\n";
		return false;
	}
}
