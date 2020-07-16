<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:56
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-06-18 17:01:32
 */

// User configurable variables
//--------开发调试用--20200311-----------------------------------------

// Default language to use for the login screen and the setup of new users.
$DefaultLanguage = 'zh_CN.utf8';

// Default theme to use for the login screen and the setup of new users.
$DefaultTheme = 'xenos';

// Whether to display the demo login and password or not on the login screen
$AllowDemoMode = FALSE;

// Connection information for the database
// $host is the computer ip address or name where the database is located
// assuming that the webserver is also the sql server
$host = 'localhost';

// assuming that the web server is also the sql server
$DBType = 'mysqli';
//assuming that the web server is also the sql server
$DBUser = 'root';
$DBPassword = 'cheng2019';
// The timezone of the business - this allows the possibility of having;
date_default_timezone_set('Asia/Shanghai');
putenv('TZ=Asia/Shanghai');
$AllowCompanySelectionBox ='ShowSelectionBox';
//The system administrator name use the user input mail;
//$SessionSavePath = 'c:/tmp';
$SysAdminEmail = '';
$DefaultDatabase = 'haichuang_erp';
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
/*
$CompanyList[] = array('database'=>'haichuang_erp' ,'company'=>'HaiChuang调试' );
$CompanyList[] = array('database'=>'gjw_erp' ,'company'=>'GJW开发' );
$CompanyList[] = array('database'=>'gjw_weberp' ,'company'=>'WebERP' );
$CompanyList[] = array('database'=>'hualu_erp' ,'company'=>'华陆数控' );
$CompanyList[] = array('database'=>'gjw_futai' ,'company'=>'FuTai' );
$CompanyList[] = array('database'=>'gjw_dianshi' ,'company'=>'点石云创' );
$CompanyList[] = array('database'=>'gjw_realestate' ,'company'=>'房地产开发' );
$CompanyList[] = array('database'=>'gjw_futai' ,'company'=>'富泰调试' );
*/
//End Installed companies-do not change this line

/* Make sure there is nothing - not even spaces after this last ?> */
?>