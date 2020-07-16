
<?php
/* $Id: SelectFixedAsset.php  chengjiang  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-01-28 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-01-28 13:53:40
 * 发行版 distro
 */
include ('includes/session.php');
$Title = '添加固定资产';

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetItems';
if (!isset($_POST['Account'])){
	$_POST['Account']='1601';
}
	
//prnMsg($RunningTotal,'info');
if (isset($_POST['CSV'])) {

		$CSVListing =iconv('utf-8','gb2312', '序号,日期,凭证号,摘要,借方金额,贷方金额,借/贷,余额')."\n";
		$CSVListing .= '" "," "," ",'.iconv('utf-8','gb2312','期初结余').',"","",'.iconv('utf-8','gb2312',(($RunningTotal >= 0)?'借':'贷')).',"'.$RunningTotal.'"'."\n";
		$idx=1;
		while ($row = DB_fetch_array($TransResult)) {
			$RunningTotal += $row['amount'];			
			$PeriodTotal += $row['amount'];
			//	$DebitAmount = locale_number_format($row['debit'],$_SESSION['CompanyRecord']['decimalplaces']);
				$DebitSum +=$row['debit'];
			//	$CreditAmount = locale_number_format($row['credit'],$_SESSION['CompanyRecord']['decimalplaces']);
				$CreditSum +=  $row['credit'] ;
			$CSVListing .=$idx.','.$row['trandate'].','.$row['transno'].','.iconv('utf-8','gb2312',$row['narrative']).','.($row['debit']).','.($row['credit']).','.iconv('utf-8','gb2312',(($RunningTotal >= 0)?'借':'贷')).','.(abs($RunningTotal)) . "\n";
			$idx++;
		}
		$CSVListing .= '" "," "," ",'.iconv('utf-8','gb2312','累计').','.$DebitSum.','.$CreditSum.',"",""'."\n";
		header('Content-Encoding: gb2312');
		header('Content-type: text/csv; charset=gb2312');

		header("Content-Disposition: attachment; filename=".iconv('utf-8','gb2312','账簿') .  $SelectedAccount  . '-' . $LastPeriodSelected  .'.csv');
		header('Cache-Control:must-revalidate,post-check=0,pre-check=0');   
		header("Expires: 0");
		header("Pragma: public");
		//echo "\xEF\xBB\xBF"; // UTF-8 BOM
	
		echo $CSVListing;
		exit;
	
}
include('includes/header.php');
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' .$Title . '</p>';

echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple periods') . '</div><br />';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/*Dates in SQL format for the last day of last month*/
//$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));

/*Show a form to allow input of criteria for TB to show */

echo '<table class="selection">
		<tr>
			<td>' . _('Account').':</td>
			<td><select name="Account">';

			$sql="SELECT t3.accountname, t3.accountcode FROM chartmaster t3 WHERE t3.accountcode not in(SELECT t.accountcode FROM chartmaster t WHERE (LENGTH(t.accountcode)=4 or EXISTS
( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))) and t3.accountcode like'1601%'";
$result = DB_query($sql);
while ($myrow=DB_fetch_array($result,$db)){
	if($myrow['accountcode'] == $_POST['Account']){
	
		echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	} else {
		echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
 }
echo '</select></td>
	</tr>

	</table>
	<br />
	<div class="centre">
		<input type="submit" name="Show" value="'._('Show Account Transactions').'" />
		<br/>
		
		
	</div>';
	$sql= "SELECT 	type,
					typename,
					transno,
					gltrans.typeno,
					trandate,
					narrative,
					amount,
					toamount(amount,-1,0,0,1,flg) debit,
					toamount(amount,-1,0,0,-1,flg) credit,
					periodno,
					gltrans.tag	,
					account	,
					posted,
					prtchk	
				FROM gltrans INNER JOIN systypes
				ON systypes.typeid=abs(gltrans.type)			
				WHERE gltrans.account LIKE '".$_POST['Account']."%'
				AND posted=0
				AND periodno>='" . $_SESSION['janr'] . "'
				AND periodno<'" .  ($_SESSION['janr']+12). "'";

$sql .= " ORDER BY periodno, gltrans.trandate";

$namesql = "SELECT accountname FROM chartmaster WHERE accountcode='" . $SelectedAccount . "'";
$nameresult = DB_query($namesql);
$namerow=DB_fetch_array($nameresult);
$SelectedAccountName=$namerow['accountname'];
$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because') ;
//prnMsg($sql,'info');
$TransResult = DB_query($sql,$ErrMsg);

	/*Is the account a balance sheet or a profit and loss account */
	$result = DB_query("SELECT pandl
				FROM accountgroups
				INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
				WHERE chartmaster.accountcode='" . $SelectedAccount ."'");
	$PandLRow = DB_fetch_row($result);
	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}

	
	$BankAccountInfo = isset($BankAccount)?'<th>' . _('Org Currency') . '</th>
						<th>' . _('Amount in Org Currency') . '</th>	
						<th>' . _('Bank Ref') .'</th>':'';
if (DB_num_rows($TransResult)>0){
	echo '<br />
		<table class="selection">
		<thead>
			<tr>
				<th colspan="7"><b>', _('Transactions for account'), ' ', $SelectedAccount, ' - ', $SelectedAccountName, '</b></th>
			</tr>
			<tr>
				<th class="centre">', ('Date'), '</th>
				<th class="text">', _('Voucher No'), '</th>
				<th class="text">科目编码</th>
				<th class="text">', _('Narrative'), '</th>			
				<th class="number">', _('Debit'), '</th>
				<th class="number">', _('Credit'), '</th>		
			  	<th class="text">', _('Tag'), '</th>
					
			</tr>
		</thead><tbody>';
	
	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$j = 1;
	$k=0; //row colour counter
	$IntegrityReport='';
	while ($myrow=DB_fetch_array($TransResult)) {
		if ($myrow['periodno']!=$PeriodNo){
			if ($PeriodNo!=-9999){ //ie its not the first time around
				echo '<tr>
					<td colspan="3"><b>' . _('Total for period') . ' </b></td>';
			   echo '<td class="number"><b>', locale_number_format($DebitSum,$_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
					 <td class="number"><b>', locale_number_format($CreditSum,$_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
					 <td colspan="2">&nbsp;</td>
						</tr>';
				$IntegrityReport = '<br />' . _('Period') . ': ' . $PeriodNo  . _('Account movement per transaction') . ': '  . locale_number_format($PeriodTotal,$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Movement per ChartDetails record') . ': ' . locale_number_format($ChartDetailRow['actual'],$_SESSION['CompanyRecord']['decimalplaces']) . ' ' . _('Period difference') . ': ' . locale_number_format($PeriodTotal -$ChartDetailRow['actual'],3);
			}
			$PeriodNo = $myrow['periodno'];
			$PeriodTotal = 0;
			$DebitSum = 0;
			$CreditSum = 0;
		}

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		$RunningTotal += $myrow['amount'];
		$PeriodTotal += $myrow['amount'];
		$DebitAmount = locale_number_format($myrow['debit'],$_SESSION['CompanyRecord']['decimalplaces']);
		$DebitSum +=$myrow['debit'];
		$CreditAmount = locale_number_format($myrow['credit'],$_SESSION['CompanyRecord']['decimalplaces']);
		$CreditSum +=  $myrow['credit'] ;     
		$FormatedTranDate = ConvertSQLDate($myrow['trandate']);
		$URL_to_TransDetail = $RootPath . '/GLTransInquiry.php?prdno=' . $myrow['periodno'] . '&amp;TransNo=' . $myrow['transno'];
		//if (isset($BankAccount)) {
		$geturl= $RootPath . '/FixedAssetSimple.php?Select='.urlencode(json_encode(array($myrow['account'],$myrow['transno'],$myrow['periodno'],$myrow['amount'],$myrow['trandate'],$myrow['type'],$myrow['prtchk'])));	
	
			echo'<td class="centre">'. $FormatedTranDate.'</td>
				<td class="text">
				     <a href="'. $URL_to_TransDetail.'" target="_blank" >'. '记字'. $myrow['transno'].'</a></td>
				<td class="text">'. $myrow['account'].'</td>
				<td class="text">'.$myrow['narrative'] .'</td>				
				<td class="number">'.$DebitAmount .'</td>
				<td class="number">'.$CreditAmount .'</td>
				<td><a href="' .$geturl.  '"  title="点击生成或查找已经制作的会计凭证！" >添加</a></td>			
					
				</tr>';
	}
 				echo '<tr>
					<td colspan="3"><b>' . _('Total for period') . ' </b></td>';
			   	echo '<td class="number"><b>', locale_number_format($DebitSum,$_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
					 <td class="number"><b>', locale_number_format($CreditSum,$_SESSION['CompanyRecord']['decimalplaces']), '</b></td>
					 <td colspan="2">&nbsp;</td>
					</tr>';

	echo '</tbody>
		  </table>';
}else{
	prnMsg("当前没有需要添加固定资产的会计凭证！",'info');
}
	echo '</div>
	</form>';


if (isset($ShowIntegrityReport) AND $ShowIntegrityReport==True ){
	if (!isset($IntegrityReport)) {
		$IntegrityReport='';
	}
	prnMsg( _('There are differences between the sum of the transactions and the recorded movements in the ChartDetails table') . '. ' . _('A log of the account differences for the periods report shows below'),'warn');
	echo '<p>' . $IntegrityReport;
}
include('includes/footer.php');
?>
