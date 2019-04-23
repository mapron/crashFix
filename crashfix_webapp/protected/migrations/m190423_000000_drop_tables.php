<?php

class m190423_000000_drop_tables extends CDbMigration
{
	public function up()
	{
		$this->dropTable('{{module}}');
		$this->dropTable('{{stackframe}}');
		$this->dropTable('{{thread}}');
		$this->dropTable('{{fileitem}}');
		return true;
	}

	public function down()
	{
		echo "m190423_000000_drop_tables doesn't support migration down.\n";
		return false;
	}
}
