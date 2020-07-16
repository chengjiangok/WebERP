<?php
/* $Id: CloseOff.php  ChengJiang $*/
/* This script is for maintenance of the Close off. */
/*
 * @Author: ChengJiang 
 * @Date: 2019-04-08 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-09 04:21:24
 */
include('includes/session.php');

$Title ='封账/返回';// Screen identificator.
$ViewTopic= 'GettingStarted';// Filename's id in ManualContents.php's TOC.
$BookMark = 'SystemConfiguration';// Anchor's id in the manual's html document.
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/maintenance.png" title="' .// Title icon.
	$Title . '" />' .// Icon title.
	$Title . '</p>';// Page title.

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
		if ($_SESSION['ProhibitPostingsBefore'] != $_POST['X_ProhibitPostingsBefore'] ) {
			$sql = "UPDATE config SET confvalue = '" . $_POST['X_ProhibitPostingsBefore']."' WHERE confname = 'ProhibitPostingsBefore'";
		}
		$ErrMsg =  _('The system configuration could not be updated because');
		$result = DB_query($sql,$ErrMsg);
		prnMsg( _('System configuration updated'),'success');
		$ForceConfigReload = True; // Required to force a load even if stored in the session vars
		include('includes/GetConfig.php');
		$ForceConfigReload = False;

} /* end of if submit */

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table cellpadding="2" class="selection" width="98%">';

echo '<div class="page_help_text"> 封账的功能只是关闭了本期凭证录入功能，更改当前会计期间，没有改变任何数据！
			
</div>';
$LastPeriodResult = DB_query("SELECT periodno,lastdate_in_period FROM periods order by periodno DESC LIMIT 1");
$LastPeriodRow = DB_fetch_row($LastPeriodResult);
$CreateTo = $LastPeriodRow[0];
$dt=$LastPeriodRow[1];
echo '<tr style="outline: 1px solid">
				<td>' . _('Prohibit GL Journals to Periods Prior To') . ':</td>
				<td><select name="X_ProhibitPostingsBefore">';
if ($CreateTo -$_SESSION['period']<6){
	$lastday = date('Y-m-d', strtotime(date('Y-m-01', strtotime($dt)) . ' +2 month -1 day'));
	$SQL='INSERT INTO periods(periodno, lastdate_in_period)  VALUES ('.($CreateTo+1).',"' . $lastday . '")';
	$InsertFirstPeriodResult = DB_query($SQL,_('Could not insert second period'));

}
$sql="SELECT lastdate_in_period ,periodno FROM periods WHERE periodno>=".($_SESSION['startperiod']-1)." AND date_format(lastdate_in_period,'%Y%m')< date_format(NOW(), '%Y%m' )  ORDER BY periodno DESC";
$ErrMsg = _('Could not load periods table');
$result = DB_query($sql,$ErrMsg);

while ($PeriodRow = DB_fetch_row($result)){
	if ($PeriodRow[1]==-1){
		$csstr="初始录入选择";
	}else{
		$csstr= ConvertSQLDate($PeriodRow[0]) ;
	}
	if ($_SESSION['ProhibitPostingsBefore']==$PeriodRow[0]){
		echo  '<option selected="selected" value="' . $PeriodRow[0] . '">' .$csstr. '</option>';
	} else {
		echo  '<option value="' . $PeriodRow[0] . '">' . $csstr . '</option>';
	}
}
echo '</select></td>
	</tr>';


echo '</table>';

echo'		<br />
		<div class="centre">
		<input type="submit" name="submit" value="' . _('Update') . '" /></div>
    </div>
	</form>';

include('includes/footer.php');
?>
