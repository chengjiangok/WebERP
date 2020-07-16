
<?php
/* $Id: BankMatching.php  rchacon $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-04-26 15:03:14 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-10-22 20:44:05
 * 2017-10-22  借贷方向错误
 */
include('includes/session.php');
$Title = _('Bank Matching');// Screen identificator.
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'BankMatching';// Filename's id in ManualContents.php's TOC.
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} /*else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}*/
	$Type = 'Receipts';
	$TypeName =_('Receipts');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/bank.png" title="' .
		_('Bank Matching') . '" /> ' .// Icon title.
		_('Bank Account Matching - Receipts') . '</p>';// Page title.

echo '<div class="page_help_text">通过导入的银行交易,智能核对、生成会计凭证,不同版本功能不同!</div><br />';

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />

      <input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />
      <input type="hidden" name="Type" value="' . $Type . '" />';

echo '<table class="selection">
		<tr>
			<td align="left">' . _('Bank Account') . ':</td>
			<td colspan="3"><select tabindex="1" autofocus="autofocus" name="BankAccount">';

	$sql = "SELECT bankaccounts.accountcode,
					bankaccounts.bankaccountname
			FROM bankaccounts, bankaccountusers
			WHERE bankaccounts.accountcode=bankaccountusers.accountcode
				AND bankaccountusers.userid = '" . $_SESSION['UserID'] ."'
			ORDER BY bankaccounts.bankaccountname";
	$resultBankActs = DB_query($sql);
while ($myrow=DB_fetch_array($resultBankActs)){
	if (isset($_POST['BankAccount'])
		AND $myrow['accountcode']==$_POST['BankAccount']){

		echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['bankaccountname'] . '</option>';
	}
}

echo '</select></td>
	</tr>';

if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
	$_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
}
if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
	$_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,Date('m')-3,Date('d'),Date('y')));
}

// Change to allow input of FROM DATE and then TO DATE, instead of previous back-to-front method, add datepicker
echo '<tr>
		<td>' . _('Show') . ' ' . $TypeName . ' ' . _('from') . ':</td>
		<td><input tabindex="3" type="text" name="AfterDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" size="12" maxlength="10" required="required" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . $_POST['AfterDate'] . '" /></td>
	</tr>';

echo '<tr>
        <td>' . _('to') . ':</td>
		<td><input tabindex="2" type="text" name="BeforeDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" size="12" maxlength="10" required="required" onchange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' . $_POST['BeforeDate'] . '" /></td>
	</tr>';
echo '<tr>
		<td colspan="3">选择收付款记录:</td>
		<td><select tabindex="4" name="Ostg_or_All">';
		 $impft=array(0=>'显示全部记录',1=>'显示全部收款',2=>'显示全部付款',3=>'显示未核对收款',4=>'显示未核对付款');
		foreach($impft as $key=>$value){
				if (isset($_POST['Ostg_or_All']) and ($_POST['Ostg_or_All']==$key)){
				echo '<option selected="selected" value="' ;
			}else {
				echo '<option value ="';
			}
				echo   $key.'">'.$value.'</option>';
		}

echo '</select></td>
	</tr>';

echo '</table>
	<br />
	<div class="centre">
		<input tabindex="6" type="submit" name="Search" value="查询交易" />';
	if (isset($_POST['Search'])|| $_POST['PageOffset']>1||isset($_POST['Go']) ||isset($_POST['Next']) ||isset($_POST['Previous'])){			
  	 echo'<input type="submit"name="TransAI" value="凭证生成" />	';			
	}
echo '</div>';

$InputError=0;
if (!Is_Date($_POST['BeforeDate'])){
	$InputError =1;
	prnMsg(_('The date entered for the field to show') . ' ' . $TypeName . ' ' . _('before') . ', ' .
		_('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' .
		$_SESSION['DefaultDateFormat'],'error');
}
if (!Is_Date($_POST['AfterDate'])){
	$InputError =1;
	prnMsg( _('The date entered for the field to show') . ' ' . $Type . ' ' . _('after') . ', ' .
		_('is not entered in a recognised date format') . '. ' . _('Entry is expected in the format') . ' ' .
		$_SESSION['DefaultDateFormat'],'error');
}
	//翻页开始 
	$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);
if (isset($_POST['TransAI']) || isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])||isset($_POST['autotrans'])) {
     /*
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}*/
    //if(isset($_POST['ShowTransactions'])){

	$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

	$BankResult = DB_query("SELECT decimalplaces,
									currcode
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode=currencies.currabrev
							WHERE accountcode='" . $_POST['BankAccount'] . "'");
	$BankRow = DB_fetch_array($BankResult);
	$CurrDecimalPlaces = $BankRow['decimalplaces'];
	$CurrCode = $BankRow['currcode'];
	$SQL="SELECT sum(amount) FROM banktransaction
	           WHERE  bankdate < '". $SQLAfterDate . "'
						AND account='" . $_POST['BankAccount'] . "'";
	$result = DB_query($SQL);
	$row=DB_fetch_row($result);
	if (isset($row)){
		$balance=$row[0];
	}else{
		$balance=0;
	}
	//prnMsg($balance,'info');
		$sql="SELECT banktransid,
					t.account bankacc,
					type,
					transno,
					period,
					bankdate,
					CASE WHEN(amount > 0 AND t.flg = 1) OR(amount < 0 AND t.flg = -1) THEN amount ELSE ''
					END debit,
					CASE WHEN(amount < 0 AND t.flg = 1) OR(amount > 0 AND t.flg = -1) THEN - amount ELSE ''
					END credit,
					currcode,
					g.account,
					t.toaccount,
					toname,
					tobank,
					t.remark,
					t.abstract,
					reliability,
					flag,
					t.flg,
					g.tag
					FROM banktransaction as t
               		LEFT JOIN gljnltemplet as g ON t.toaccount=g.toaccount
					WHERE  bankdate >= '". $SQLAfterDate . "'
						AND bankdate <= '" . $SQLBeforeDate . "'
						AND t.account='" . $_POST['BankAccount'] . "'
					ORDER BY bankdate";	

	    //prnMsg($sql,'info');
		$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
		$result = DB_query($sql, $ErrMsg);
		$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}
      if ($ListCount>0 AND (isset($_POST['Search'])||isset($_POST['TransAI'])	
	       OR isset($_POST['Go'])	OR isset($_POST['Next'])	
		   OR isset($_POST['Previous']))){
		if (!isset($blnarr)){
          $blnarr=array();
	
			while ($myrow=DB_fetch_array($result)) {
					$balance+=$myrow['debit']-$myrow['credit'];
					$blnarr[]=$balance;

			}
		}
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
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
		if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
		echo '<select name="PageOffset">';
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
			<input type="submit" name="Go" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}
		echo '<table cellpadding="2" class="selection">
				<tr>
					<th class="ascending">序号</th>				
					<th class="ascending">' . _('Date') . '</th>
					<th class="ascending">收入金额</th>
					<th class="ascending">支出金额</th>	
					<th class="ascending">余额</th>					
					<th class="ascending">对方名称</th>
					<th class="ascending">客户科目</th>					
					<th >摘要</th>
					<th >核对	</th>
					<th class="ascending">凭证号</th>
					<th class="ascending">收付款类别</th>
					<th ></th>
				</tr>';

		$k = 0; //row colour counter
		$i = 1; //no of rows counter
       	$RowIndex = 0;	
		if (DB_num_rows($result) <> 0) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		$LastJournal = 0;
		$LastType = -1;
		//	prnMsg($Outstanding,'info');
		while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){

		$DisplayTranDate = ConvertSQLDate($myrow['bankdate']);
	      if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
	  
			printf('	<td>%s</td>					
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td >%s</td>
						<td >%s</td>
						<td >%s</td>
						<td >%s</td>
						<td >%s</td>
						
						<td ><select name="bankType[]">		     
							<option  value="1" '.($invtyp==1? 'selected="selected"':'').'>客户收付款</option>
							<option  value="2" '.($invtyp==2? 'selected="selected"':'').'>往来收付款</option>
							<option  value="3" '.($invtyp==3? 'selected="selected"':'').'>工资支付</option>
							<option  value="4" '.($invtyp==4? 'selected="selected"':'').'>转户提现</option>
							<option  value="5" '.($invtyp==5? 'selected="selected"':'').'>费用支出</option>
							<option  value="6" '.($invtyp==6? 'selected="selected"':'').'>其他收支</option>
   							</select>
						</td>
				<td ><input type="checkbox" name="chkbx[]" value="%s" checked ></td>											

					</tr>',
						$RowIndex+1+($_SESSION['DisplayRecordsMax']*($_POST['PageOffset']-1)),						
					
						$DisplayTranDate,
						locale_number_format($myrow['debit'],$CurrDecimalPlaces),
						locale_number_format($myrow['credit'],$CurrDecimalPlaces),
						locale_number_format($blnarr[$RowIndex+($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']],$CurrDecimalPlaces),
						$myrow['toname'],
						$myrow['account'],
						$myrow['remark'],					
						$myrow['reliability'],
						$myrow['transno'],
						$myrow['banktransid'].'^'.($RowIndex+1+($_SESSION['DisplayRecordsMax']*($_POST['PageOffset']-1))));

		$RowIndex = $RowIndex + 1;
	}	//end of while loop
	echo '</table>';
	}
	//凭证生成
	if (isset($_POST['TransAI'])){
		$RowIndex =0;
		$rowar=array();
		if (DB_num_rows($result) <> 0) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		$f=0;
		
	    while ($row=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
		
			if($_POST['chkbx'][$f]!='' && empty($row['transno']) && $row['reliability']==0 ){
		   
				$rmktyp=array('结息'=>'6603101','维护费'=>'6603102','工资及退休金'=>'2211101','手续费'=>'6603102');
				$rmkp=array('工资'=>'2211101');
				$acc=$row['account']	;   
				$msg=$row['remark'];				
			
				$acc1=$acc;		
				if ($row['accname']==''){
					foreach($rmktyp as $key=>$val){				
						if (strpos($row['remark'].$row['abstract'],$key)!=false){
						$acc=$val;
				
						break;
						}
					}
				}else{
					foreach($rmkp as $key=>$val){			
						if (strpos($row['remark'].$row['abstract'],$key)!=false){
						$acc=$val;
					
						break;
						}
					}
				}
				if ($row['debit']!=0){
					$jd=-1;
					$amount=-$row['debit'];
				}else{
					$jd=1;
					$amount=$row['credit'];
				}
			
				$sltprd=$_SESSION['period']-(substr($_SESSION['lastdate'],0,4)-substr($row['bankdate'],0,4))*12+(substr($row['bankdate'],5,2)-substr($_SESSION['lastdate'],5,2));
				  //  计算对应期间     9-(2017-2018)*12+(1-9)
				if (!empty($acc)){
				$rowar=array('dt'=>substr($row['bankdate'],0,10),'prd'=>$sltprd,'jd'=>$jd,'amount'=>$amount,'accname'=>$row['toname'],
				'toacc'=>$row['toaccount'],'acc'=>$acc,'rmk'=>$row['remark'],'abst'=>$row['abstract'],
				'bankindx'=>$row['banktransid'],'tag'=>$row['tag'],'transno'=>$row['transno'], 'bankacc'=>$row['bankacc']);
				
				$transnew=BankJournalAI($rowar);		
				}	
				if ($f==1){
					$drf=$rowar;
				}
				unset($rowar);
			
			}
		
				$f++;
				$RowIndex = $RowIndex + 1;
			
  		
		}//while
	//	prnMsg($row['reliability'].'-'.$f,'info');
	}
 			
}//if122
echo '</div>';
echo '</form>';
include('includes/footer.php');
function UpdateAcc($ROW){
	//$ROW toname account toaccount tobank debit
	//根据已有客户名称比对，更新账户 添加GLJournalTemplet
	//suppliers    debtorsmaster Cusbranch  
	$sqlacc="SELECT `accountcode`, `accountname`,tag  
						FROM `chartmaster`
						WHERE LEFT(accountcode ,4) IN('1122','1221','2202','2241') AND length(accountcode)>4";
		/*	$SQL=" SELECT banktransid, t.account bankacc, TYPE, transno, period, bankdate, CASE WHEN(amount > 0 AND t.flg = 1) OR(amount < 0 AND t.flg = -1) THEN amount ELSE '' END debit,
							CASE WHEN(amount < 0 AND t.flg = 1) OR(amount > 0 AND t.flg = -1) THEN - amount ELSE '' END credit,
							currcode, g.account, t.toaccount, toname, tobank, t.remark, t.abstract, reliability, flag, t.flg 
							FROM banktransaction as t LEFT JOIN gljournaltemplet as g ON t.toaccount=g.toaccount
							WHERE bankdate >= '2017-03-01' AND bankdate <= '2017-07-09' AND t.account='1002101' ORDER BY bankdate";
			
				$RESULT=DB_query($SQL);*/
	$resultacc=DB_query($sqlacc);
	$jg=80;
	$retacc='';
	if (empty($ROW['account'])){
		DB_data_seek($resultacc,0);
		while($row=DB_fetch_array($resultacc)){
			
			$ff=0;
			$rr=-1;
			$invtyp=3;
			$bankid=0;			
			similar_text(subName($ROW['toname']),subName($row['accountname']),$ff);
			if ($ff>=$jg ){
				$jg=$ff;
				$rr=$i;
				$result=DB_query("SELECT  `unitscode`, `branchcode`, `tag`, `unittype` FROM `accountunits` WHERE account='".$row['accountcode']."'");
				$rowno=DB_fetch_row($result);
				if (!empty($rowno)){
					$retacc=$row['accountcode'];
				}
				DB_Txn_Begin();
				$result=DB_query("INSERT INTO gljournaltemplet(taxaccount,
											toaccount,
											account,
											acctype,											
											jd,
											templet,
											maxamount,
											remark,
											abstract,
											flg	)
									VALUES(	'',
											'".$ROW['toaccount']."',
											'".$row['accountcode']."',
											'1',
											
											'".($ROW['debit']!=0?'1':'-1')."',
											'',	 0,	'',	'',	0)");
			/*
			if (substr($row['accountcode'],0,4)=='1122'){
				$result=DB_query("UPDATE `debtorsmaster`
						SET  `address3` = CASE WHEN `address3`='' THEN '".$ROW['tobank']."' ELSE concat(address3,',','".$ROW['tobank']."') END ,
							`address4` =  CASE WHEN `address4`='' THEN '".$ROW['toaccount']."' ELSE concat(address4,',','".$ROW['toaccount']."') END 												
						WHERE  `debtorno` = '".$rowno[0]."'");				
				$result=DB_query("UPDATE   `custbranch`
							SET `braddress3` =  CASE WHEN `braddress3`='' THEN '".$ROW['tobank']."' ELSE concat(braddress3,',','".$ROW['tobank']."') END ,
								`braddress4` = CASE WHEN `braddress4`='' THEN '".$ROW['toaccount']."' ELSE concat(braddress4,',','".$ROW['toaccount']."') END 												
							WHERE `branchcode` ='".$rowno[1]."' AND 
								`debtorno` ='".$rowno[0]."' ");
			}elseif (substr($row['accountcode'],0,4)=='2202'){
				$result=DB_query("UPDATE  `suppliers`
									SET  `bankact` = CASE WHEN `bankact`='' THEN '".$ROW['toaccount']."' ELSE concat(bankact,',','".$ROW['toaccount']."') END 	,											
										bankpartics =  CASE WHEN bankpartics='' THEN '".$ROW['tobank']."' ELSE concat(bankpartics,',','".$ROW['tobank']."') END 
									WHERE  `supplierid` ='".$rowno[0]."' ");
			}
			*/			
				DB_Txn_Commit();
		//---
			} 
		
							
		}//while
	}//endif
	return $retacc;
}
function BankJournalAI($invar){
	//$dt,$jd,$amount,$accname,$toacc,$bankacc,acc $bankindx,$tag,$transno amoacc rmk
	//根据日期 发票号 类别 金额  税额  单位名 税号  账号 科目 付款序号 凭证号 销售科目/材料  销项税/进项税
	//进项销项发票凭证智能生成
		$TransNo = GetTransNo($invar['prd'], $db);
		$sql="SELECT `typeid` FROM `transtype` WHERE account='".substr($invar['acc'],0,4)."'";
		$result=DB_query($sql);
		$row=DB_fetch_row($result);
		$typ=$row[0];
		$TypeNo = GetTypeNo($typ,$invar['prd'], $db);		
		//，插入凭证	
		DB_Txn_Begin();
		$inst=0;
		$bankamo=$invar['amount']*$invar['jd'];
	
		$accamo=-1*$invar['amount']*$invar['jd'];
		
		$SQL = "INSERT INTO gltrans (type,
										typeno,
										transno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										flg)
							VALUES ('".$typ."',
								'" . $TypeNo . "',
									'" . $TransNo . "',
									'" . FormatDateForSQL($invar['dt']) . "',
									'" . $invar['prd'] . "',
									'" . $invar['bankacc'] . "',
									'".$invar['rmk'].$invar['accname']."', 
									'" . $bankamo ."',
									'1')";			
			$result = DB_query($SQL);
			if ($result){
				$inst++;
			}		 
	
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										transno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										flg)
									
							VALUES ('".$typ."',
								'" . $TypeNo . "',
									'" . $TransNo . "',
									'" . FormatDateForSQL($invar['dt']) . "',
									'" . $invar['prd'] . "',
									'" . $invar['acc'] . "',
									'".$invar['rmk']."', 
									'" . $accamo ."',
									'1')";	
		$result = DB_query($SQL);	
		if ($result){
				$inst++;
			}		 	
	      $SQL="UPDATE `banktransaction` SET   `transno` ='".$TypeNo."'  WHERE banktransid='".$invar['bankindx']."'";
		   $result=DB_query($SQL);				 
		if ($result && $inst==2){
		
			DB_Txn_Commit();
		}else{
			$TransNo='';
			DB_Txn_Rollback();		
		}	

	return $sql;//$TransNo;
}
?>
