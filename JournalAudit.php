


<?php
/*$ID JouranalAudit.php   $*/
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-05-04 14:27:33
 */
include ('includes/session.php');
$Title ='会计凭证审核';
$ViewTopic = 'GeneralLedger';
$BookMark = 'JournalAudit';
if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}	
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
foreach($_SESSION['CompanyRecord'] as $key=>$row)	{         
	if ($row['coycode']!=0){
		
	   $UnitsTag[$row['coycode']]=$row['unitstab'];
	   $UnitsTag[-$row['coycode']]=$row['unitstab']."内";     
	
	}
}
include('includes/header.php');
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' .$Title . '" alt="" />' .  $Title . '</p>';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		if (!isset($_POST['Search'])	AND !isset($_POST['Go'])	AND !isset($_POST['Next'])	AND !isset($_POST['Previous'])){ 
	echo '<div class="page_help_text">' .
			'功能简介：会计凭证审核、修改，删除凭证；'
				 . '</div>';
}
if (isset($_POST['Auditiy'])) {
		$strno = "";
	foreach( $_POST['chkbx'] as $i){
 		$strno .= $i.',';
		}
		$strno = substr($strno,0,-1);
	  if ($strno != ""){
 	$sql="update gltrans set prtchk=2 where transno in (".$strno.") and  periodno='" .$_SESSION['period'] ."' and (gltrans.prtchk=0 OR gltrans.prtchk is null OR gltrans.prtchk=1 )";
	$result = DB_query($sql);
	//prnMsg($strno,'info');
	}
	
}	
	   	 $sql="SELECT gltrans.typeno,systypes.typename, gltrans.type,
				gltrans.trandate,gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
				toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
				gltrans.tag
						
				FROM gltrans
			  LEFT JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode	
				LEFT JOIN systypes
				ON abs(gltrans.type)=systypes.typeid
			
				WHERE   gltrans.periodno='" .$_SESSION['period']."' AND  gltrans.prtchk<>2 	ORDER BY gltrans.transno  DESC";
	 
			$result = DB_query($sql);
			$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
			}
	
	if ($ListCount >0 OR isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])	OR isset($_POST['Previous'])) {
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
		
	  $ListCount = DB_num_rows($result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
	if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
		echo '<select name="PageOffset1">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type="submit" name="Go1" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}
	if ($_SESSION['AccessLevel']==8 OR $_SESSION['AccessLevel']==6){
		echo '  <input type="submit" name="Auditiy" value="凭证审核" />';
		    
	}
	echo '<br />'; 	
		echo '<table class="selection">';
		echo '<tr>
				<th>' . ('Date') . '</th>
				<th>' . _('Voucher No') . '</th>
				<th>' . _('Account Code') . '</th>
				<th>' . _('Account Description') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Debits').' '.$_SESSION['CompanyRecord'][1]['currencydefault'] . '</th>
				<th>' . _('Credits').' '.$_SESSION['CompanyRecord'][1]['currencydefault'] . '</th>	';
		if (isset($_SESSION['Tag'])){
			echo'<th>' . _('Unit Tag').' </th>';
	 	}
		echo '  	<th>' . _('Edit').' </th>		
							<th>审核</th>					
			</tr>';

  	$RowIndex = 0;
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		$LastJournal = 0;
   		$LastType = -1;
   		$r=0;
		while ($myrow = DB_fetch_array($result)  AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])){			
			//if ($myrow['typeno']!=$LastJournal or ($myrow['typeno']=$LastJournal and $myrow['type']!=$LastType) ) {			
			if ($myrow['transno']!=$LastJournal){ 
				if ($r==1){
					echo '<tr class="EvenTableRows">';
					$r=0;
				}else{
					echo '<tr class="OddTableRows">';
					$r=1;
				}
				echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
					  <td >记字'. $myrow['transno']. '</td>';
			}else{
				
				if ($r==1){
					echo '<tr class="EvenTableRows"><td colspan="2"></td>';
					$r=0;
				}else{
					echo '<tr class="OddTableRows"><td colspan="2"></td>';
					$r=1;
				}
			}

			echo'<td>'. $myrow['account'] . '</td>
				<td>'. $myrow['accountname'] . '</td>
				<td>'. $myrow['narrative']. '</td>
				<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord'][1]['decimalplaces'])) . '</td>
				<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord'][1]['decimalplaces']) ). '</td>';
				echo'<td >' . $UnitsTag[$myrow['tag']] . '</td>';
				/*
				if (isset($_SESSION['Tag'])){
						if ($myrow['tag']>0){
						   echo'<td >' . $UnitsTag[$myrow['tag']] . '</td>';
						}elseif ($myrow['tag']<0){
							echo'<td >' . $UnitsTag[$myrow['tag']] . '共享</td>';
						}
		     		}
					*/
			if ($myrow['transno']!=$LastJournal  ){
				echo '<td ><a href="JournalEntry.php?No='.$myrow['transno'].'&Tag='.$myrow['tag'].'&Edit=Yes"  target="_blank">' . _('Edit')  . '</a></td>';
				echo '<td ><input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'" checked></td></tr>';
	      		//$LastType = $myrow['type'];
				$LastJournal = $myrow['transno'];
					
			}else {
				echo '<td colspan="2"></td></tr>';
			}
			$RowIndex = $RowIndex + 1;
		}
		echo '</table>';
		if (isset($ListPageMax) AND  $ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
			echo '<select name="PageOffset2">';
				$ListPage = 1;
				while ($ListPage <= $ListPageMax) {
					if ($ListPage == $_POST['PageOffset']) {
						echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
					} else {
						echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
					}
					$ListPage++;
				}
				echo '</select>
					<input type="submit" name="Go2" value="' . _('Go') . '" />
					<input type="submit" name="Previous" value="' . _('Previous') . '" />
					<input type="submit" name="Next" value="' . _('Next') . '" />';
				echo '</div>';
		}
	}
	
	
	echo '</form>';
include('includes/footer.php');

?>