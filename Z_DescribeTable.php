<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:58
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-06-06 14:32:36
 */ 
/* $Id: Z_DescribeTable.php 6941 2014-10-26 23:18:08Z daintree $*/

include('includes/session.php');
$Title = _('Database table details');
include('includes/header.php');
//列出表结构
$sql='DESCRIBE '.$_GET['table'];
$result=DB_query($sql);

echo '<table><tr>';
echo '<th>' . _('Field name') . '</th>';
echo '<th>' . _('Field type') . '</th>';
echo '<th>' . _('Can field be null') . '</th>';
echo '<th>' . _('Default') . '</th>';
while ($myrow=DB_fetch_row($result)) {
	echo '<tr><td>' .$myrow[0]  . '</td><td>';
	echo $myrow[1]  . '</td><td>';
	echo $myrow[2]  . '</td><td>';
	echo $myrow[4]  . '</td></tr>';
}
echo '</table>';
include('includes/footer.php');


?>