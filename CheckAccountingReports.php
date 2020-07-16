/*
 * @Author: ChengJiang 
 * @Date: 2017-03-04 18:34:11 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-03-04 18:40:35
 */
<?php
/* $Id: CheckAccountingReports.php 7092 2016-09-15 chengjiang $*/
/*  */

include('includes/session.php');
$Title = '科目组维护 ';// Screen identificator.
$ViewTopic= 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = '科目组维护 ';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .
	_('Bank') . '" /> ' .// Icon title.
	_('Check Accounting Reports') . '</p>';// Page title.

//echo '<div class="page_help_text">' . _('to Back Default).') . '.</div><br />';

$Errors = array();
	$sql = "SELECT groupname,parentgroupname FROM accountgroups where concat( groupname,parentgroupname) not IN (SELECT concat( groupname,parentgroupname) FROM standardaccountgroups)";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	$r=DB_num_Rows($result);
	if ( $r==0){;
		prnMsg(_('Report item settings ok'),'info');
 }else
{
	prnMsg(_('Report item settings and default report items are modified.'),'warn');

}
 if (isset($_POST['submit']) and $r>0){
	
	$InputError = 0;

	
	$i=1;
$sql="set sql_safe_updates=0;
delete FROM accountgroups;
insert into accountgroups (groupname,sectioninaccounts,pandl,sequenceintb,parentgroupname,groupno,zfsq,flg) select groupname,sectioninaccounts,pandl,sequenceintb,parentgroupname ,groupno,zfsq,flg FROM standardaccountgroups where flg<>0 order by sequenceintb;";
$result=DB_query($sql);


}
/* Always show the list of accounts */
$aa='';
$bb='';
if ($r>0) {


	echo '<table class="selection">
			<tr>
				<th>' . _('GL Account Code') . '</th>
				<th>' . _('Bank Account Name') . '</th>
				<th>' . _('Bank Account Code') . '</th>
				<th>' . _('Bank Account Number') . '</th>
			
			</tr>';

	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>			
						</tr>',
			$myrow['groupname'],
			$myrow['parentgroupname'],
			$aa,
			$bb);
		

	}
	//END WHILE LIST LOOP

	echo '</table><br />';
}
 $sql="SELECT accountcode,accountname,group_ FROM chartmaster where group_ not in (select groupname FROM standardaccountgroups where flg<>0 order by sequenceintb);";
 	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	$rr=DB_num_Rows($result);
	if ( $rr==0){;
		prnMsg(_('Report item settings ok'),'info');
 }else
{
	prnMsg(_('Report item settings and default report items are modified.'),'warn');

}
if ($rr>0) {


	echo '<table class="selection">
			<tr>
				<th>' . _('GL Account Code') . '</th>
				<th>' . _('Bank Account Name') . '</th>
				<th>' . _('Bank Account Code') . '</th>
				<th>' . _('Bank Account Number') . '</th>
			
			</tr>';

 	$k=0; //row colour counter
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>			
						</tr>',
			$myrow['accountcode'],
			$myrow['accountname'],
			$myrow['group_'],
			$bb);
		

	}
	echo '</table><br />';
}
  
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if ($r>0 or $rr>0){
echo '	<div class="centre">    <input tabindex="9" type="submit" name="submit" value="'. _('Restoring default group') .'" /></div>
		</div>
	</form>';
	}
include('includes/footer.php');
?>
