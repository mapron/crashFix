<?php

class m181011_044000_update_crashreport_exe_image extends CDbMigration
{
    // Use safeUp/safeDown to do migration with transaction
    public function safeUp()
    {
        $this->execute(
            "UPDATE " . $this->getDbConnection()->quoteTableName("{{crashreport}}") . " SET exe_image=SUBSTRING_INDEX(exe_image, '\\\\', -1)"
        );
        return true;
    }

	public function safeDown()
	{
		echo "m181011_044000_update_crashreport_exe_image doesn't support migration down.\n";
		return false;
	}
}
