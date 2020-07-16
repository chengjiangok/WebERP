<?php


$DefaultLanguage = 'zh_CN.utf8';
$DefaultTheme = 'xenos';
$AllowDemoMode = FALSE;
$host = 'localhost';
$DBType = 'mysqli';
$DBUser = 'root';
$DBPassword = 'atiger';
date_default_timezone_set('Asia/Shanghai');
putenv('TZ=Asia/Shanghai');
$AllowCompanySelectionBox = 'ShowSelectionBox';

$SessionSavePath = 'c:/tmp';
$SysAdminEmail = '';
$DefaultDatabase = 'erp';
$SessionLifeTime = 3600;
$MaximumExecutionTime = 120;
$DefaultClock = 12;
$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
if (isset($DirectoryLevelsDeep)){
   for ($i=0;$i<$DirectoryLevelsDeep;$i++){
		$RootPath = mb_substr($RootPath,0, strrpos($RootPath,'/'));
	}
}
if ($RootPath == '/' OR $RootPath == '\\') {
	$RootPath = '';
}
error_reporting(E_ALL && ~E_NOTICE && ~E_WARNING);
//Installed companies 


define ('LIKE','LIKE');

if (!isset($mysqlport)){
	$mysqlport = 3306;
}
$db1 = mysqli_connect($host , $DBUser, $DBPassword,$DefaultDatabase, $mysqlport);
//$result=DB_query('SET sql_mode = ANSI');
mysqli_set_charset($db1, 'utf8');


?>
