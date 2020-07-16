<?php
/*ZT_DelJournal.php  chengjiang*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-06-18 05:10:25 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-09-24 05:11:04
 */
include ('includes/session.php');
$Title = '删除凭证';
$ViewTopic= 'Delete Journal';
$BookMark = 'Delete Journal';

include('includes/header.php');
echo '<script type="text/javascript">
function reload(){
 window.location.reload();
 }
 </script>';
$sql = "SELECT MAX(`transno`) FROM `gltrans` WHERE periodno=". $_SESSION['period'];  
$result = DB_query($sql); 
$row=DB_fetch_row($result);
$tranmax=$row[0];
$sql = "SELECT MAX(`transno`) FROM `gltrans` WHERE (abs(printno)>0 OR prtchk>0 ) AND periodno=". $_SESSION['period'];  
$result = DB_query($sql); 
$row=DB_fetch_row($result);	
$chkmax=$row[0];
$dateprd=date_format(date_create($_SESSION['lastdate']),"Y-m");	
$dtprd=(int)date("Ym");	
$tprd=(int)str_replace('-','',$dateprd);
if ($_GET['del']=='d'){
	$urlstr=htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8').'?del=d';
}else{
	$urlstr=htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form action="' . $urlstr . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="selection">
		<tr>
   			<td>当前会计期间:'.$dateprd.'</td>
		</tr>
		</table>';
if (($tprd-$dtprd==0)||($tprd-$dtprd==1 && (int)date("m")<15)|| $_GET['del']=='d'){
	if ($tranmax-$chkmax>0){	 
		$showno=30; 
	    if ($tranmax-$chkmax>$showno){
			$showno=$tranmax-$showno;
		}else{
			$showno=$tranmax-($tranmax-$chkmax);
		}
	    $sql="SELECT gltrans.typeno,systypes.typename, gltrans.type,
				gltrans.trandate,gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				CASE WHEN gltrans.amount>0 THEN  gltrans.amount ELSE 0 END AS Debits,
				CASE WHEN gltrans.amount<0 THEN  -gltrans.amount ELSE 0 END AS Credits
			FROM gltrans
			INNER JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode
	
				LEFT JOIN systypes
				ON gltrans.type=systypes.typeid
			WHERE  gltrans.periodno='" .$_SESSION['period']."' AND abs(printno)=0 and prtchk=0 and transno>=".$showno." ORDER BY gltrans.transno Desc";
		$result = DB_query($sql);
		echo '<br /><div class="centre">
	
		<input type="submit" name="DeleteJournal" value="删除最末凭证"  onchange="reload()" /></div>';
		if (DB_num_rows($result)==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'error');
		} else {
			prnMsg('凭证删除将不能恢复，请你谨慎使用!','warn');
		if (isset($_POST['DeleteJournal'])) {
			prnMsg('你删除的凭证会计期间:'.$dateprd.' 凭证号:'.$tranmax, 'info');
		}
		echo '<table class="selection">';
		echo '<tr>
				<th>' . ('Date') . $_POST['JournalType']. '</th>
				<th>' . _('Voucher No') . '</th>
				<th>' . _('Account Code') . '</th>
				<th>' . _('Account Description') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Debits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
				<th>' . _('Credits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>	
						
			</tr>';

		$LastJournal = 0;
   		$LastType = -1;
   		$r=0;
		while ($myrow = DB_fetch_array($result)){			
			if ($myrow['transno']!=$LastJournal  ) {			
				if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
				} else {
					echo '<tr class="OddTableRows">';
					$r=1;
				}
				echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
					<td >' ._('Accounting'). $myrow['transno'].'-'.$myrow['typename'] .$myrow['typeno'] . '</td>';
			} else {
				
				if ($r==1){
					echo '<tr class="EvenTableRows"><td colspan="2"></td>';
					$r=0;
				} else {
					echo '<tr class="OddTableRows"><td colspan="2"></td>';
					$r=1;
				}
			}
			echo '<td >' . $myrow['account'] . '</td>
					<td >' . $myrow['accountname'] . '</td>
					<td>'.$myrow['narrative']. '</td>
					<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord']['decimalplaces'])) . '</td>
					<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord']['decimalplaces']) ). '</td>';
			if ($myrow['transno']!=$LastJournal ){
		
	      		//$LastType = $myrow['type'];
				$LastJournal = $myrow['transno'];
					
			} else {
				echo '<td colspan="1"></td></tr>';
			}

		}
		echo '</table>';
	  
		} //end if no bank trans in the range to show
	}else {
		prnMsg('本期间还没有录入凭证！','info');   
}
if (isset($_POST['DeleteJournal'])) {
		$sql="Delete	FROM gltrans where transno=".$tranmax." AND ABS(printno)=0 AND prtchk=0 AND  periodno='" .$_SESSION['period'] ."'";
		$result = DB_query($sql);	
		
}
}else{
	prnMsg('凭证删除功能支持当前期、结账期后15内无打印、审核的凭证操作!','info');
}
echo '</form>';
include('includes/footer.php');
?>