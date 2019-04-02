<?php

/**
 * Returns common param array.
 * @return array common params.
 */
function getCommonParams()
{
	return array(
		'version'=>'1.0.4', // CrashFix web app version
	);

}


/**
 * This helper function is used for extracting db connection parameters from INI
 * file.
 * @param string $overrideTablePrefix Allows to override table prefix string.
 * @return array Database connection config array.
 */
function dbParams($overrideTablePrefix=null)
{
	$userParams = parse_ini_file(dirname(__FILE__).DIRECTORY_SEPARATOR.'user_params.ini');

	return array(
			'class'=>'CDbConnection',
			'connectionString'=>str_replace('%DATA_DIR%', dirname(__FILE__).'/../data', $userParams['db_connection_string']),
			'username'=>$userParams['db_username'],
			'password'=>$userParams['db_password'],
			'tablePrefix' => $overrideTablePrefix==null?$userParams['db_table_prefix']:$overrideTablePrefix,
			'initSQLs' => ['SET NAMES utf8 ;']
			//'emulatePrepare'=>true,  // needed by some MySQL installations
			//'schemaCachingDuration'=>3600, // one hour
		);
}

function daemonParams()
{
    $userParams = parse_ini_file(dirname(__FILE__).DIRECTORY_SEPARATOR.'user_params.ini');
    return array(
        'class'       => 'Daemon',
        'host'        => isset($userParams['daemon_host']) ? $userParams['daemon_host'] : '127.0.0.1',
        'servicePort' => isset($userParams['daemon_port']) ? $userParams['daemon_port'] : '1234',
    );	
}