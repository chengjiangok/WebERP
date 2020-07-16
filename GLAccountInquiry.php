<?php
/* $Id: GLAccountInquiry.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-01-09 06:50:09 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-01-09 21:10:32
 */
include ('includes/session.php');
$Title = _('General Ledger Account Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
if (isset($_GET['show'])){
	$urlstr=explode('^',urldecode($_GET['acp']));
	$_SESSION['showacp']=array($_GET['show'],$_GET['acp']);
	$SelectedAccount=$urlstr[0];
	if ($_GET['show']=='GLTB'){
		//从科目汇总表跳转，锁定期间
		$FirstPeriodSelected =$urlstr[1];
		$LastPeriodSelected = $urlstr[2];
    }else{

		//从账簿查询跳转SGLA 0account 1currency
		$SelectCurr=$urlstr[1];
		if (isset($_POST['period'])) { //If it was called from itself (in other words an inquiry was run and we wish to leave the periods selected unchanged
			$FirstPeriodSelected = min($_POST['period']);
			$LastPeriodSelected = max($_POST['period']);
		}else { // Otherwise just highlight the current period
			$FirstPeriodSelected =$_SESSION['janr'];
			$LastPeriodSelected = $_SESSION['period'];
		}
	}
}

if (!isset($_POST['TagsGroup']) ){

	$_POST['TagsGroup']=1;		 
}	
	$sql = "SELECT accountname,currcode,tag 
	         FROM chartmaster WHERE accountcode='" . $SelectedAccount . "'";
	$result = DB_query($sql);
	$row=DB_fetch_assoc($result);
	$SelectCurr=$row['currcode'];
	$SelectTag=$row['tag'];
	$SelectedAccountName=$row['accountname'];
	$sql="SELECT sum(amount) bfwd 
	       FROM gltrans 
		   where account='" . $SelectedAccount . "' and periodno< '" . $FirstPeriodSelected . "'
		   AND tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].")";
		$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
		$Result = DB_query($sql,$ErrMsg);
		$Row = DB_fetch_row($Result);
		$RunningTotal 	=round($Row[0],2);
if ($_SESSION['Currency']==1 &&$SelectCurr!=CURR ){
	$sql="SELECT SUM(examount) qcye ,SUM(amount)  bbqcye FROM currtrans WHERE account='" . $SelectedAccount . "' and period< '" . $FirstPeriodSelected . "'";
	$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
	$Result = DB_query($sql,$ErrMsg);
	$Row = DB_fetch_row($Result);
	$exRunningTotal 	= $Row[0];
	$RunningTotal=$Row[1];
	$SQL= "SELECT counterindex,
			type,
			typename,
			gltrans.transno,
			gltrans.typeno,
			gltrans.trandate,
			narrative,
			gltrans.amount,
			toamount(gltrans.amount,-1,0,0,1,gltrans.flg) debit,
			toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) credit,
			periodno,
			gltrans.tag	,
			currtrans.examount,
			toamount(currtrans.examount,-1,0,0,1,currtrans.flg) exdebit,
			toamount(currtrans.examount,-1,0,0,-1,currtrans.flg) excredit	
		FROM gltrans LEFT JOIN systypes ON systypes.typeid=abs(gltrans.type)	
		LEFT JOIN currtrans ON CONCAT(currtrans.period,currtrans.transno)=CONCAT(gltrans.periodno,gltrans.transno)		
		WHERE gltrans.account = '" . $SelectedAccount . "' AND currtrans.account= '" . $SelectedAccount . "' 
		AND periodno>='" . $FirstPeriodSelected . "'
		AND periodno<='" . $LastPeriodSelected . "' 
		AND gltrans.tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].")";
}else{
    $SQL= "SELECT counterindex,
		type,
		typename,
		transno,
		gltrans.typeno,
		trandate,
		narrative,
		amount,
		toamount(amount,-1,0,0,1,flg) debit,
		toamount(amount,-1,0,0,-1,flg) credit,
		periodno,
		gltrans.tag			
	FROM gltrans LEFT JOIN systypes
	ON systypes.typeid=abs(gltrans.type)			
	WHERE gltrans.account = '" . $SelectedAccount . "'
	AND periodno>='" . $FirstPeriodSelected . "'
	AND periodno<='" . $LastPeriodSelected . "' 
	AND gltrans.tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].")";
}
/*
if (isset($_POST['UnitsTag'] ) AND $_POST['UnitsTag'] !=0 ){
	$SQL.=" AND gltrans.tag= ".$_POST['UnitsTag']." ";
}*/
$SQL .= " ORDER BY periodno,gltrans.transno,gltrans.typeno,gltrans.tag, gltrans.trandate";

$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because') ;
//echo  $SQL;
$TransResult = DB_query($SQL,$ErrMsg);

if (isset($_POST['CSV'])) {
		$CSVListing =iconv( "utf-8","GBK//IGNORE",'序号,日期,凭证号,摘要,借方金额,贷方金额,借贷,余额')."\n";
		//$CSVListing =mb_convert_encoding("序号,日期,凭证号,摘要,借方金额,贷方金额,借贷,余额","gb2312",'auto')."\n";
		$CSVListing .= '" "," "," ",'.iconv('utf-8','gb2312','期初结余').',"","",'.iconv('utf-8','gb2312',(($RunningTotal >= 0)?'借':'贷')).',"'.$RunningTotal.'"'."\n";
		$idx=1;
		while ($row = DB_fetch_array($TransResult)) {
			$RunningTotal += $row['amount'];			
			$PeriodTotal += $row['amount'];
			//	$DebitAmount = locale_number_format($row['debit'],POI);
				$DebitSum +=$row['debit'];
			//	$CreditAmount = locale_number_format($row['credit'],POI);
				$CreditSum +=  $row['credit'] ;
			$CSVListing .=$idx.','.$row['trandate'].','.iconv('utf-8','gb2312',$_SESSION['tagref'][$row['tag']][2]).$row['typeno'].','.iconv('utf-8','gb2312',$row['narrative']).','.($row['debit']).','.($row['credit']).','.iconv('utf-8','gb2312',(($RunningTotal >= 0)?'借':'贷')).','.(abs($RunningTotal)) . "\n";
			$idx++;
		}
		$CSVListing .= '" "," "," ",'.iconv('utf-8','gb2312','累计').','.$DebitSum.','.$CreditSum.',"",""'."\n";
		//prnMsg($CSVListing);
		header('Content-Encoding: gb2312');
		header('Content-type: text/csv; charset=gb2312');
		header("Content-Disposition: attachment; filename=".iconv('utf-8','gb2312','账簿') .  $SelectedAccount  . '-' . $LastPeriodSelected  .'.csv');
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');   
		header("Expires: 0");
		header("Pragma: public");
		//echo "\xEF\xBB\xBF"; // UTF-8 BOM
		//echo chr(0xEF).chr(0xBB).chr(0xBF);  
		echo $CSVListing;
		exit;
}
include('includes/header.php');
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="" />' . ' ' . _('General Ledger Account Inquiry') . '</p>';
echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple periods') . '</div><br />';
if (isset($_SESSION['showacp'])){
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?show='.$_SESSION['showacp'][0].'&amp;acp='.$_SESSION['showacp'][1].'">';
}else{
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
}
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="show" value="' . $_GET['show'] . '" />
	  <input type="hidden" name="acp" value="' . $_GET['acp'] . '" />';
//echo '<input type="hidden" name="Act" value="' . $_GET['Act'] . '" />';
/*Dates in SQL format for the last day of last month*/
//$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));
/*Show a form to allow input of criteria for TB to show */
echo '<table class="selection">
		<tr>
			<td>' . _('Account').':</td>
			<td><select name="Account">';
			/*
	$sql="SELECT t3.accountname, t3.accountcode  FROM chartmaster t3 WHERE t3.accountcode not in(SELECT t.accountcode FROM chartmaster t WHERE 				(LENGTH(t.accountcode)=4 or EXISTS
				( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))) and t3.accountcode like'".$SelectedAccount."%'";*/
				$sql="SELECT accountname, accountcode  FROM chartmaster WHERE accountcode  like'".$SelectedAccount."%'";
	$result = DB_query($sql);
while ($myrow=DB_fetch_array($result,$db)){
	if($myrow['accountcode'] == $SelectedAccount){
		echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	} else {
		echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . $myrow['currcode'] .' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
 }
echo '</select></td>
	</tr>';
echo '<tr>
		<td>单元分组</td>
		 <td>';
		// echo'<select name="TagsGroup" id="TagsGroup" size="1" >';
		 TagGroup( 7);
    
		 echo'</select>';	
		
   
echo'</td></tr>';
echo '<tr>
		<td>' . _('For Period range').':</td>
		<td>
		    <select name="period[]" size="12" multiple="multiple">';
	$sql = "SELECT periodno, lastdate_in_period 
	         FROM periods 
			 where periodno>=".$_SESSION['startperiod']. "  AND periodno<=".$_SESSION['period']. " 
			 ORDER BY periodno DESC";
	$result = DB_query($sql);
	while ($myrow=DB_fetch_array($result,$db)){
		if (isset($FirstPeriodSelected) AND $myrow['periodno'] >= $FirstPeriodSelected AND $myrow['periodno'] <= $LastPeriodSelected) {
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
		} else {
			echo '<option value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
		}
	}
echo '</select></td></tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="Show" value="'._('Show Account Transactions').'" />
		<input type="submit" name="CSV" value="导出CSV" /><br/>';
		if (isset($_SESSION['Act'])){
			$actstr='?Act='.$_SESSION['Act'];
		}elseif($_POST['show']=='SGLA'){
			$actstr="";
		}
		echo'<a href="' . $RootPath . '/SelectGLAccount.php'.$actstr.'">返回账簿查询</a>';
	echo'</div>
	</div>
	</form>';
	//prnMsg($SQL);
//<input type="submit" name="submitreturn" value="' ._('Return').'" />
/* End of the Form  rest of script is what happens if the show button is hit*/
if (isset($_POST['Show']) OR isset($_POST['CSV'])|| ($_GET['show']=='SGLL')){
     // var_dump(mb_detect_encoding("我的便慢慢", array("ASCII",'UTF-8',"GB2312","GBK",'BIG5')));
	if (!isset($_POST['period'])){
		prnMsg(_('A period or range of periods must be selected from the list box'),'info');
		include('includes/footer.php');
		exit;
	}
	/*Is the account a balance sheet or a profit and loss account */
	$result = DB_query("SELECT pandl
				FROM accountgroups
				LEFT JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
				WHERE chartmaster.accountcode='" . $SelectedAccount ."'");
	$PandLRow = DB_fetch_row($result);
	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}
	//prnMsg(substr($SelectedAccount,0,strlen($_SESSION['Act'])));
    if ($_SESSION['Act']!=substr($SelectedAccount,0,strlen($_SESSION['Act']))){
        $_SESSION['Act']=substr($SelectedAccount,0,4);
	}
	$BankAccountInfo = isset($BankAccount)?'<th>' . _('Org Currency') . '</th>
						<th>' . _('Amount in Org Currency') . '</th>	
						<th>' . _('Bank Ref') .'</th>':'';
	echo '<br />
		<table class="selection">
		<thead>
			<tr>
				<th colspan="10"><b>', _('Transactions for account'), ' ', $SelectedAccount, ' - ', $SelectedAccountName, '</b></th>
			</tr>
			<tr>
				<th class="centre">', _('Date'), '</th>
				<th class="text">','凭证号', '</th>
				<th class="text">', _('Narrative'), '</th>';
	if ($_SESSION['Currency']==1 &&$SelectCurr!=CURR ){					
		echo'	<th class="number">外币</th>
	         	<th class="number">', _('Debit'), '</th>
				<th class="number">', _('Credit'), '</th>		
			  	<th class="text">', _('Tag'), '</th>
				<th class="number">外币余额</th>';
	   }else{
		echo'  	<th class="number">', _('Debit'), '</th>
				<th class="number">', _('Credit'), '</th>		
			  	<th class="text">', _('Tag'), '</th>';
	   }
		echo' 	<th class="number">', _('Balance'), '</th>					
			</tr>
		</thead><tbody>';
	//	$RunningTotal =$_POST['bfwd'];
	if ($PandLAccount==True) {
		$RunningTotal = 0;	
		$exRunningTotal = 0;
	} else {
		if ($_SESSION['Currency']==1 &&$SelectCurr!=CURR ){					
  			echo '<tr>
						<td colspan="2"></b></td>
						<td colspan="2"><b>', _('Brought Forward Balance'), '</b></td>
						<td colspan="2"></b></td>';
			if($RunningTotal < 0 ) {// It is a credit balance b/fwd
				echo '  <td >&nbsp;贷方</td>
						<td class="number"><b>', locale_number_format(-$exRunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></td>
						<td class="number"><b>', locale_number_format(-$RunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></td>
					</tr>';
			} else {// It is a debit balance b/fwd
				echo '
				<td >&nbsp;借方</td>
				<td class="number"><b>', locale_number_format($exRunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></td>
				<td class="number"><b>', locale_number_format($RunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></td>
				</tr>';
			}
		}else{//本币
			echo '<tr>
			<td colspan="2"></b></td>
			<td colspan="1"><b>', _('Brought Forward Balance'), '</b></td>
			<td colspan="2"></b></td>';
			if($RunningTotal < 0 ) {// It is a credit balance b/fwd
				echo '  <td >&nbsp;贷方</td>					
						<td class="number"><b>', locale_number_format(-$RunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></td>
					</tr>';
			} else {// It is a debit balance b/fwd
				echo '
				<td >&nbsp;借方</td>				
				<td class="number"><b>', locale_number_format($RunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></td>
				</tr>';
			}
		}
	}
	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$DeSum = 0;
	$CrSum = 0;
	$j = 1;
	$k=0; //row colour counter
	$IntegrityReport='';
	if ($_SESSION['Currency']==1 &&$SelectCurr!=CURR ){					
		while ($myrow=DB_fetch_array($TransResult)) {
			if ($myrow['periodno']!=$PeriodNo){
				if ($PeriodNo!=-9999){ //ie its not the first time around
					echo '<tr>
					<th colspan="2"></th>
						<th colspan="2"><b>' .$SelectCurr.'外币'. _('Total for period') . ' </b></th>
						<th class="number"><b>', locale_number_format($exDebitSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
						<th class="number"><b>', locale_number_format($exCreditSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th></tr>
						<th colspan="3">&nbsp;</th>';
					echo '<tr>
				<th colspan="2"></th>
						<th colspan="2"><b>' . _('Total for period') . ' </b></th>
						<th class="number"><b>', locale_number_format($DebitSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
						<th class="number"><b>', locale_number_format($CreditSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
						<th colspan="3">&nbsp;</th>
							</tr>';
					//$IntegrityReport = '<br />' . _('Period') . ': ' . $PeriodNo  . _('Account movement per transaction') . ': '  . locale_number_format($PeriodTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']) . ' ' . _('Movement per ChartDetails record') . ': ' . locale_number_format($ChartDetailRow['actual'],$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']) . ' ' . _('Period difference') . ': ' . locale_number_format($PeriodTotal -$ChartDetailRow['actual'],3);
				}
				$PeriodNo = $myrow['periodno'];
				$DeSum += $DebitSum ;
				$CrSum +=$CreditSum;
				$PeriodTotal = 0;
				$DebitSum = 0;
				$CreditSum = 0;
				$exDebitSum = 0;
				$exCreditSum = 0;
			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
				$exRunningTotal += $myrow['examount'];
				$exPeriodTotal += $myrow['examount'];
				$exAmount = locale_number_format($myrow['examount'],$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']);
				$exDebitSum +=$myrow['exdebit'];		
				$exCreditSum +=  $myrow['excredit'] ;
				$RunningTotal += $myrow['amount'];
				$PeriodTotal += $myrow['amount'];
				$DebitAmount = locale_number_format($myrow['debit'],POI);
				$DebitSum +=$myrow['debit'];
				$CreditAmount = locale_number_format($myrow['credit'],POI);
				$CreditSum +=  $myrow['credit'] ;
			$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
			$URL_to_TransDetail = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['periodno'].'^'.$myrow['transno'];
			
				printf('<td class="centre">%s</td>
					<td class="text" title="'.$myrow['transno'].'"><a href="%s" target="_blank" >%s</a></td>
					<td class="text">%s</td>				
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="text">%s</td>				
					<td class="number">%s</td>	
					<td class="number">%s</td>			
					</tr>',
					$FormatedTranDate,
					$URL_to_TransDetail,
					$_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno'],
					$myrow['narrative'],
					$exAmount,
					$DebitAmount,
					$CreditAmount,
					($RunningTotal >= 0)?_('Debit'):_('Credit'),
					locale_number_format(($exRunningTotal >= 0)? $exRunningTotal:-$exRunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']),
					locale_number_format(($RunningTotal >= 0)? $RunningTotal:-$RunningTotal,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']));
		}           
					echo '<tr>
							<th colspan="2"></th>
							<th colspan="2"><b>' . $SelectCurr.'外币'._('Total for period') . ' </b></th>
							<th class="number"><b>', locale_number_format($exDebitSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
							<th class="number"><b>', locale_number_format($exCreditSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th></tr>
							<th colspan="3">&nbsp;</th>';
					echo '<tr>
							<th colspan="2"></th>
							<th colspan="2"><b>' . _('Total for period') . ' </b></th>';				
					echo '<th class="number"><b>', locale_number_format($DebitSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
						<th class="number"><b>', locale_number_format($CreditSum,$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
						<th colspan="3">&nbsp;</th>
						</tr>';
				echo '<tr>
						<th colspan="2"></th>
						<th colspan="1"><b>查询期总计 </b></th>';				
				echo '<th class="number"><b>', locale_number_format(($DeSum+$DebitSum),$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
					<th class="number"><b>', locale_number_format(($CrSum+$CreditSum),$_SESSION['CompanyRecord'][$SelectTag]['decimalplaces']), '</b></th>
					<th colspan="3">&nbsp;</th>
					</tr>';
	}else{
		while ($myrow=DB_fetch_array($TransResult)) {
			if ($myrow['periodno']!=$PeriodNo){
				if ($PeriodNo!=-9999){ //ie its not the first time around
				echo '<tr>
				<th colspan="2"></th>
						<th colspan="1"><b>本月合计 </b></th>
						<th class="number"><b>', locale_number_format($DebitSum,POI), '</b></th>
						<th class="number"><b>', locale_number_format($CreditSum,POI), '</b></th>
						<th colspan="2">&nbsp;</th>
							</tr>';
					//$IntegrityReport = '<br />' . _('Period') . ': ' . $PeriodNo  . _('Account movement per transaction') . ': '  . locale_number_format($PeriodTotal,POI) . ' ' . _('Movement per ChartDetails record') . ': ' . locale_number_format($ChartDetailRow['actual'],POI) . ' ' . _('Period difference') . ': ' . locale_number_format($PeriodTotal -$ChartDetailRow['actual'],3);
				}
				$PeriodNo = $myrow['periodno'];
				$DeSum += $DebitSum ;
				$CrSum +=$CreditSum;
				$PeriodTotal = 0;
				$DebitSum = 0;
				$CreditSum = 0;
				$exDebitSum = 0;
				$exCreditSum = 0;
			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
				$exRunningTotal += $myrow['examount'];
				$exPeriodTotal += $myrow['examount'];
				$exAmount = locale_number_format($myrow['examount'],POI);
				$exDebitSum +=$myrow['exdebit'];		
				$exCreditSum +=  $myrow['excredit'] ;
				$RunningTotal += $myrow['amount'];
				$PeriodTotal += $myrow['amount'];
				$DebitAmount = locale_number_format($myrow['debit'],POI);
				$DebitSum +=$myrow['debit'];
				$CreditAmount = locale_number_format($myrow['credit'],POI);
				$CreditSum +=  $myrow['credit'] ;
			$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
			$URL_to_TransDetail = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['periodno'].'^'.$myrow['transno'];
			//if (isset($BankAccount)) {
				printf('<td class="centre">%s</td>
					<td class="text" title="'.$myrow['transno'].'"><a href="%s" target="_blank" >%s</a></td>
					<td class="text">%s</td>			
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="text">%s</td>				
					<td class="number">%s</td>			
					</tr>',
					$FormatedTranDate,
					$URL_to_TransDetail,$_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno'],
					$myrow['narrative'],		
					$DebitAmount,
					$CreditAmount,
					($RunningTotal >= 0)?_('Debit'):_('Credit'),
					locale_number_format(($RunningTotal >= 0)? $RunningTotal:-$RunningTotal,POI));
		}           
					echo '<tr>
							<th colspan="2"></th>
							<th colspan="1"><b>本月合计 </b></th>';				
					echo '<th class="number"><b>', locale_number_format($DebitSum,POI), '</b></th>
						<th class="number"><b>', locale_number_format($CreditSum,POI), '</b></th>
						<th colspan="2">&nbsp;</th>
						</tr>';
					echo '<tr>
						<th colspan="2"></th>
						<th colspan="1"><b>查询期总计 </b></th>';				
				echo '<th class="number"><b>', locale_number_format(($DeSum+$DebitSum),POI), '</b></th>
					<th class="number"><b>', locale_number_format(($CrSum+$CreditSum),POI), '</b></th>
					<th colspan="2">&nbsp;</th>
					</tr>';
	}
	echo '</tbody></table>';
}
/*
if (isset($ShowIntegrityReport) AND $ShowIntegrityReport==True ){
	if (!isset($IntegrityReport)) {
		$IntegrityReport='';
	}
	prnMsg( _('There are differences between the sum of the transactions and the recorded movements in the ChartDetails table') . '. ' . _('A log of the account differences for the periods report shows below'),'warn');
	echo '<p>' . $IntegrityReport;
}*/
include('includes/footer.php');
?>
