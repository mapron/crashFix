<?php

class m190514_000000_add_crashreport_fields extends CDbMigration
{
	// Use safeUp/safeDown to do migration with transaction
	public function safeUp()
	{
		$this->addColumn(
			'{{crashreport}}',
			'huid',
			'VARCHAR(128) DEFAULT NULL'
		);
		$this->addColumn(
			'{{crashreport}}',
			'huidhash',
			'VARCHAR(128) DEFAULT NULL'
		);
		$this->addColumn(
			'{{crashreport}}',
			'isTrial',
			'BOOLEAN DEFAULT FALSE'
		);
		$this->addColumn(
			'{{crashreport}}',
			'sendStatistics',
			'BOOLEAN DEFAULT FALSE'
		);
		return true;
	}
}
