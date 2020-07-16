<?php
/* $Id: GLTransInquiry.php  chengjiang$*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-03-14 15:25:25 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-06-05 15:37:48
 * 使用的过程TransInquiry.php
 */
include ('includes/session.php');
$Title = '凭证查询';
$ViewTopic = 'GeneralLedger';// Filename in ManualContents.php's TOC.
$BookMark = 'GLTransInquiry';// Anchor's id in the manual's html document.
include('includes/header.php');

$MenuURL = '<div><a href="'. $RootPath . '/GLAccountInquiry.php">返回</a></div>';


if( !isset($_GET['TransNo']) ) {	
	prnMsg(_('This page requires a valid transaction type and number'),'warn');

} else {
	//========[ SHOW SYNOPSYS ]===========
	
		echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme .
			'/images/magnifier.png" title="' .	$Title . '" />' . ' ' .		$Title. '</p>';
			$SQL = "SELECT
			gltrans.periodno,
			gltrans.trandate,
			gltrans.type,
			gltrans.account,
			chartmaster.accountname,
			gltrans.narrative,
			gltrans.amount,
			toamount(amount,-1,0,0,1,flg) debit,
			toamount(amount,-1,0,0,-1,flg) credit,
			gltrans.posted,
			periods.lastdate_in_period
		FROM gltrans INNER JOIN chartmaster
		ON gltrans.account = chartmaster.accountcode
		INNER JOIN periods
		ON periods.periodno=gltrans.periodno
		WHERE gltrans.periodno= '" .$_GET['prdno'] . "'
		AND gltrans.transno = '" .  $_GET['TransNo'] . "'
		ORDER BY gltrans.counterindex";
		$Result = DB_query($SQL); 
		$row=DB_fetch_array($Result);
		echo '<table class="selection">'; //Main table
		echo '<tr>
				<th colspan="7"><h3>会计凭证</h3></th>
			</tr>
			<tr><th colspan="3"></th>			   
				 <th colspan="1">日期:' .$row['trandate'] . '</th>				 
				 <th  colspan="3">记字:' . $_GET['TransNo'] . '</th>
			</tr>
			<tr>
			    <th>序号</th>
			    <th>科目编码</th>
				<th>' . _('GL Account') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Debits') . '</th>
				<th>' . _('Credits') . '</th>
				<th>' . _('Posted') . '</th>
			</tr>';

		
			DB_data_seek($Result,0);
		$Posted = _('Yes');
		$CreditTotal = 0;
		$DebitTotal = 0;
		$AnalysisCompleted = 'Not Yet';
		$j = 1;// Row counter to determine background colour.
		$x=1;
		while( $Row = DB_fetch_array($Result) ) {
			$TranDate = ConvertSQLDate($Row['trandate']);
			$DetailResult = false;
	
			$DebitAmount = locale_number_format($Row['debit'],$_SESSION['CompanyRecord']['decimalplaces']);
				$DebitTotal += $Row['debit'];
			//	$CreditAmount = '&nbsp;';
		
				$CreditAmount = locale_number_format($Row['credit'],$_SESSION['CompanyRecord']['decimalplaces']);
				$CreditTotal += $Row['credit'];
				//$DebitAmount = '&nbsp;';

			if( $Row['posted']==0 ) {
				$Posted = _('No');
			}
			$AccountName = $Row['accountname'];
					if( mb_strlen($Row['narrative'])==0 ) {
						$Row['narrative'] = '&nbsp;';
					}
					if ($j==1) {
					    echo '<tr class="OddTableRows">';
					    $j=0;
					} else {
					    echo '<tr class="EvenTableRows">';
					    $j++;
					}			
					echo '<td>' . $x . '</td>
							<td>' . $Row['account'] . '</td>
							<td>' . $AccountName . '</td>
							<td>' . $Row['narrative'] . '</td>';
					echo '<td class="number">' . $DebitAmount . '</td>
							<td class="number">' . $CreditAmount . '</td>
							<td>' . $Posted . '</td>
							</tr>';
			if($DetailResult AND $AnalysisCompleted == 'Not Yet') {

				while( $DetailRow = DB_fetch_array($DetailResult) ) {
					if( $Row['amount'] > 0) {
						if($Row['account'] == $_SESSION['CompanyRecord']['debtorsact']) {
							$Debit = locale_number_format(($DetailRow['ovamount'] + $DetailRow['ovgst']+ $DetailRow['ovfreight']) / $DetailRow['rate'],$_SESSION['CompanyRecord']['decimalplaces']);
							$Credit = '&nbsp;';
						} else {
							$Debit = locale_number_format(-($DetailRow['ovamount'] + $DetailRow['ovgst']) / $DetailRow['rate'],$_SESSION['CompanyRecord']['decimalplaces']);
							$Credit = '&nbsp;';
						}
					} else {
						if($Row['account'] == $_SESSION['CompanyRecord']['debtorsact']) {
							$Credit = locale_number_format(-($DetailRow['ovamount'] + $DetailRow['ovgst'] + $DetailRow['ovfreight']) / $DetailRow['rate'],$_SESSION['CompanyRecord']['decimalplaces']);
							$Debit = '&nbsp;';
						} else {
							$Credit = locale_number_format(($DetailRow['ovamount'] + $DetailRow['ovgst']) / $DetailRow['rate'],$_SESSION['CompanyRecord']['decimalplaces']);
							$Debit = '&nbsp;';
						}
					}

					if ($j==1) {
					    echo '<tr class="OddTableRows">';
					    $j=0;
					} else {
					    echo '<tr class="EvenTableRows">';
					    $j++;
					}
					echo '
							<td><a href="' . $URL . $DetailRow['otherpartycode'] . $FromDate . '">' .
								$Row['accountname'] . ' - ' . $DetailRow['otherparty'] . '</a></td>
							<td>' . $Row['narrative'] . '</td>
							<td class="number">' . $Debit . '</td>
							<td class="number">' . $Credit . '</td>
							<td>' . $Posted . '</td>
						</tr>';
				}
				DB_free_result($DetailResult);
				$AnalysisCompleted = 'Done';
			}
			$x++;
		}
		DB_free_result($Result);

		echo '<tr style="background-color:#FFFFFF">
		        <td colspan="3"></td>
				<td ><b>' . _('Total') . '</b></td>
				<td class="number"><b>' .
					locale_number_format(($DebitTotal),$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				<td class="number"><b>' .
					locale_number_format(($CreditTotal),$_SESSION['CompanyRecord']['decimalplaces']) . '</b></td>
				<td>&nbsp;</td>
			</tr>
			</table></br></br>';
	//echo $MenuURL;

}

include('includes/footer.php');
?>
