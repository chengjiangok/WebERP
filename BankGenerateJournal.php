<?php
/*
 * @Author: ChengJiang 
 * @Date: 2017-10-28 15:03:14 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-07-24 10:42:59
 */
include('includes/session.php');
$Title ='银行凭证生成';// Screen identificator.
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'BankMatching';// Filename's id in ManualContents.php's TOC.
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['selectprd'])OR $_POST['selectprd']==''){
	$_POST["selectprd"]=$_SESSION['period'].'^'.$_SESSION['lastdate'];
  }

  if (!isset($_POST['prdrange']) OR $_POST['prdrange']==''){
	$_POST['prdrange']=0;		  	
   }
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} /*else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}*/
     //添加银行账户
	$result=DB_query("INSERT IGNORE INTO customeraccount(accountnumber, account, regid, custname, tag, acctype, bankname, bankcode, flg) SELECT DISTINCT toaccount accountnumber,'' account,0 regid, toname custname,0 tag,0 acctype,tobank bankname ,'' bankcode,0 flg FROM banktransaction WHERE toaccount NOT IN (SELECT accountnumber FROM customeraccount ) AND toaccount<>''");
	
	$Type = 'Receipts';
	$TypeName =_('Receipts');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/bank.png" title="' .
		$Title . '" /> ' .// Icon title.
		  $Title.'</p>';// Page title.

echo '<div class="page_help_text">通过导入的银行交易,智能核对、生成会计凭证!</div><br />';

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	  <input type="hidden" name="BankAccount" value="' . $_POST['BankAccount'] . '" />
      <input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />
      <input type="hidden" name="Type" value="' . $Type . '" />';

echo '<table class="selection">
		<tr>
			<td align="left">' . _('Bank Account') . ':</td>
			<td colspan="2"><select tabindex="1" autofocus="autofocus" name="BankAccount">';

	$sql = "SELECT	bankaccounts.accountcode,
					bankaccounts.bankaccountname,
					bankaccounts.currcode,
					chartmaster.tag
				FROM bankaccounts
				LEFT JOIN chartmaster ON chartmaster.accountcode = bankaccounts.accountcode,
					bankaccountusers
					WHERE bankaccounts.accountcode=bankaccountusers.accountcode
					AND bankaccounts.importformat IN (SELECT  bankid FROM bankcopyformat) 
					AND bankaccountusers.userid = '" . $_SESSION['UserID'] ."'
			ORDER BY bankaccounts.bankaccountname";
			//prnMsg($sql);
	$resultBankActs = DB_query($sql);
while ($myrow=DB_fetch_array($resultBankActs)){
	if (isset($_POST['BankAccount'])
		AND $myrow['accountcode'].'^'.$myrow['currcode']. '^'.$myrow['tag']==$_POST['BankAccount']){

		echo '<option selected="selected" value="' . $myrow['accountcode'] .'^'.$myrow['currcode'].  '^'.$myrow['tag'].'">' . $myrow['accountcode'] . '[' .$myrow['currcode'].']' . $myrow['bankaccountname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['accountcode'] . '^'.$myrow['currcode']. '^'.$myrow['tag'].'">' . $myrow['accountcode'] . '[' .$myrow['currcode'].']'. $myrow['bankaccountname'] . '</option>';
	}
}

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Select Period To')  . '</td>
		<td colspan="2"><select name="selectprd" size="1" >';
	if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
 		$sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}else{
		$sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".(floor($_SESSION['startperiod']/12)*12-23 )."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}
		$periods = DB_query($sql);
    
	while ($myrow=DB_fetch_array($periods,$db)){	

		if(isset($_POST['selectprd']) AND $myrow['periodno'].'^'.$myrow['lastdate_in_period']==$_POST['selectprd']){	
			echo '<option selected="selected" value="';

		} else {
			echo '<option value ="';
		}
			echo $myrow['periodno'].'^'.$myrow['lastdate_in_period']. '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
	}   
echo '</select>';
	$rang=array('0'=>'月度', '3'=>'季度','12'=>'本年','24'=>'上年','36'=>'前年');
	
echo '范围<select name="prdrange" size="1">';
		if (($_SESSION['janr']-$_SESSION['startperiod'])<=0 ){
			unset($rang[36]);
			unset($rang[24]);
		
		}elseif (($_SESSION['janr']-$_SESSION['startperiod'])<=12 ){
			unset($rang[36]);		
		}
		foreach($rang as $key=>$val){			

			if (isset($_POST['prdrange'])&& $key==$_POST['prdrange']){
				echo '<option selected="True" value ="';
			}else{
				echo '<option value ="';
			}
			echo $key.'">'.$val.'</option>';		
		}		
echo'</select>
	</td></tr>';
	if ($_POST['prdrange']==0){
		$firstprd=explode('^',$_POST['selectprd'])[0];
         $endprd=explode('^',$_POST['selectprd'])[0];
		 $_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr(explode('^',$_POST['selectprd'])[1],0,7).'-01')));
		 $_POST['BeforeDate']=FormatDateForSQL(explode('^',$_POST['selectprd'])[1]);		
   }elseif ($_POST['prdrange']==3) {
		$firstprd=$_SESSION['janr']+ceil(($_POST['selectprd']-$_SESSION['janr']+1)/3)*3-3;
		$endprd=$_SESSION['janr']+ceil(($_POST['selectprd']-$_SESSION['janr']+1)/3)*3-1;
		$_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr(explode('^',$_POST['selectprd'])[1],0,5).($endprd-2-$_SESSION['janr']+1).'-01')));
		//这句有错误
		$BeginDate=date('Y-m-01', strtotime(substr(explode('^',$_POST['selectprd'])[1],0,5).($endprd-$_SESSION['janr']+1).'-01'));
		$_POST['BeforeDate']=FormatDateForSQL(date('Y-m-d',strtotime ("$BeginDate +1 month -1 day")));
   }elseif ($_POST['prdrange']==12) {
		$firstprd=$_SESSION['janr'];
		   $endprd=$_POST['selectprd'];
		   $_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr(explode('^',$_POST['selectprd'])[1],0,5).'01-01')));
		   $_POST['BeforeDate']=FormatDateForSQL($_SESSION['lastdate']);	  		
		   		
   }elseif ($_POST['prdrange']==24 &&$_SESSION['janr']>=13){
		$firstprd=$_SESSION['janr']-12;
		   $endprd=$_SESSION['janr']-1;	
   }elseif($_POST['prdrange']==36 && $_SESSION['janr']>=25){
		 $firstprd=$_SESSION['janr']-24;
		   $endprd=$_SESSION['janr']-13;		
   }	
	

echo '</table>';

$sql="SELECT  A.account,B.bankaccountname, MAX(bankdate) bankdt FROM banktransaction A LEFT JOIN bankaccounts B ON A.account=B.accountcode GROUP BY account";
$result=DB_query($sql);

while ($row= DB_fetch_array($result)) {
	$msg.= $row['account'].$row['bankaccountname'].'最末账单日期：'.$row['bankdt'].'<br>';
}
prnMsg($msg,'info');
echo'<br />
	<div class="centre">
		<input tabindex="6" type="submit" name="Search" value="查询交易" />';
	
	if (isset($_POST['Search'])|| $_POST['PageOffset']>1||isset($_POST['Go']) ||isset($_POST['Next']) ||isset($_POST['Previous'])){			
	   echo'<input type="submit"name="TransSave" value="凭证保存" />	';	
	  			
	}
echo '</div>';

$InputError=0;

	//翻页开始 
	$CurrCode=explode('^',$_POST['BankAccount'])[1]; 
if (isset($_POST['CheckSave']) ||isset($_POST['TransSave']) || isset($_POST['Search'])|| isset($_POST['demo'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])||isset($_POST['autotrans'])) {
	
     /*
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}*/
    //if(isset($_POST['ShowTransactions'])){
		//读取账号
		$sql="SELECT accountnumber, account,custname, regid, acctype, bankname, bankcode,A. flg FROM customeraccount A LEFT JOIN chartmaster B ON A.account=B.accountcode WHERE custname<>'' ORDER BY custname";
		$result = DB_query($sql);
		$i=0;
		while ($row = DB_fetch_array($result)) {
			//if (!array_key_exists($row['custname'],$accarr)){
			
				$accarr[$i]=array($row['accountnumber'],'',$row['regid'],$row['account'],$row['flg'],$row['custname'],$row['acctype'],$row['custname']);
		      $i++;
		}
		//读取注册号
		$sql="SELECT regid, registerno, account, A.custname custname,B.accountname , acctype, flg FROM register A LEFT JOIN chartmaster B ON A.account=B.accountcode WHERE A.custname NOT IN (SELECT custname FROM customeraccount WHERE custname<>'' ) AND A.custname<>'' AND account<>''";
		$result = DB_query($sql);
		$i=count($accarr);
		
		while ($row = DB_fetch_array($result)) {
			
			$accarr[$i]=array('',$row['registerno'],$row['regid'],$row['account'],$row['flg'],$row['custname'],$row['acctype'],$row['custname']);
			$i++;
		}
		//读取其他凭证规则
		$sql="SELECT acctype, account,accountname, gljtype, remark, abstract, A.tag, jd, maxamount, A.flg FROM gljournalinfo A LEFT JOIN chartmaster B ON A.account=B.accountcode";
		$result = DB_query($sql);
		$i=0;
		while ($row = DB_fetch_array($result)) {
			
			$gljtemplet[$i]=array($row['acctype'],$row['gljtype'],$row['account'],$row['remark'],$row['abstract'],$row['tag'],$row['jd'],$row['maxamout'],$row['flg'],$row['accountname']);
			$i++;
		}
	//	var_dump($accarr);
	//---------------------
	$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

	$BankResult = DB_query("SELECT decimalplaces,
									currcode,
									rate
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode=currencies.currabrev
							WHERE accountcode='" . explode('^',$_POST['BankAccount'])[0] . "'");
	$BankRow = DB_fetch_array($BankResult);
	$CurrDecimalPlaces = $BankRow['decimalplaces'];
	$CurrRate =round($BankRow['rate'],$CurrDecimalPlaces );
	$SQL="SELECT sum(amount) FROM banktransaction
	           WHERE  DATE_FORMAT(bankdate,'%Y-%m-%d') < '". $SQLAfterDate . "'
						AND account='" . explode('^',$_POST['BankAccount'])[0] . "'";
	$result = DB_query($SQL);
	$row=DB_fetch_row($result);
	if (isset($row)){
		$balance=$row[0];
	}else{
		$balance=0;
	}
	if ($CurrCode==$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault']){ 
		$sql="SELECT banktransid,
				t.account bankacc,
				type,
				transno,
				period,
				bankdate,
				CASE WHEN(amount > 0 AND t.flg = 1) OR(amount < 0 AND t.flg = -1) THEN amount ELSE '' END debit,
				CASE WHEN(amount < 0 AND t.flg = 1) OR(amount > 0 AND t.flg = -1) THEN - amount ELSE ''	END credit,
				currcode,
				t.toaccount,
				toname,
				tobank,
				t.remark,
				t.abstract,
				reliability,
				flag,
				t.flg
				FROM banktransaction as t				
				WHERE  DATE_FORMAT(bankdate,'%Y-%m-%d') >= '". $SQLAfterDate . "'
					AND DATE_FORMAT(bankdate,'%Y-%m-%d') <= '" . $SQLBeforeDate . "'
					AND t.account='" . explode('^',$_POST['BankAccount'])[0] . "'
				ORDER BY banktransid,bankdate";	
	}else{
		
		$sql="SELECT banktransid,
					t.account bankacc,
					type,
					transno,
					period,
					bankdate,
					amount/round(rate, decimalplaces) amount,
					(CASE WHEN(amount > 0 AND t.flg = 1) OR(amount < 0 AND t.flg = -1) THEN amount ELSE '' END) debit,
					(CASE WHEN(amount < 0 AND t.flg = 1) OR(amount > 0 AND t.flg = -1) THEN - amount ELSE ''	END) credit,
					t.currcode,
					t.toaccount,
					toname,
					tobank,
					t.remark,
					t.abstract,
					reliability,
					flag,
					t.flg
					FROM banktransaction as t	LEFT JOIN currencies ON currencies.currabrev=t.currcode			
					WHERE  DATE_FORMAT(bankdate,'%Y-%m-%d') >= '". $SQLAfterDate . "'
						AND DATE_FORMAT(bankdate,'%Y-%m-%d') <= '" . $SQLBeforeDate . "'
						AND t.account='" . explode('^',$_POST['BankAccount'])[0] . "'
					ORDER BY banktransid, bankdate";		

	}
	//prnMsg($sql);
		$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
		$result = DB_query($sql, $ErrMsg);
		$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}	
	if ($ListCount>0 AND (isset($_POST['Search'])||isset($_POST['TransSave'])	
		OR isset($_POST['Go'])	OR isset($_POST['Next']) OR isset($_POST['Previous']))){
	 if (!isset($blnarr)){//计算余额
	   $blnarr=array();
 
		 while ($myrow=DB_fetch_array($result)) {
				 $balance+=$myrow['debit']-$myrow['credit'];
				 $blnarr[]=$balance;

		 }
	 }
	 //var_dump($blnarr);
	 //prnMsg($sql);
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
			<th class="ascending">' . _('Date') . '</th>';
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode!=$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] ){					
				echo'<th class="number">外币折人民币</th>';
			}
		echo'<th class="ascending">收入金额</th>
			 <th class="ascending">支出金额</th>	
			  <th class="ascending">余额</th>	';	
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode!=$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] ){					
		echo'<th class="number">外币折人民币余额</th>';
    }			
		echo'<th class="ascending">对方名称</th>
			<th class="ascending">凭证号</th>	
			<th >借/贷</th>
			<th style="word-wrap:break-word;word-break:break-all;">凭证科目</th>				
			<th >摘要</th>			
			<th ></th>
		</tr>';

	$k = 0; //row colour counter

   $RowIndex = 0;	
	if (DB_num_rows($result) <> 0) {
	DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	$LastJournal = 0;
	$chkTransNo=0;
	$gltarr=array();
	$transnoarr=array();

while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
	
    if ($myrow['credit']==''||$myrow['credit']==0){
		$jd=1;//收款
	}else{
		$jd=-1;
	}   
	$DisplayTranDate = ConvertSQLDate($myrow['bankdate']);
	$tranmsg='';
	$prdtransno='';
	if ($myrow['transno']>0){//已经录入凭证并核对
		$LastJournal =2;
		$chkTransNo=$myrow['transno'];
		$tranmsg='该凭证完成！';
		$prdtransno=$LastJournal;
	}else{//没有对应凭证号		
		$chktranarr=CheckTrans($myrow,$jd,$CurrCode,$transnoarr);//凭证数组 	
		if (is_array($chktranarr)){	//有对应凭证需要确认
			$prdtransno=$chktranarr[0]['periodno'].'^'.$chktranarr[0]['transno'];
			$LastJournal =-1;
			$chkTransNo=$chktranarr[0]['transno'];
			array_push($transnoarr,$chkTransNo);
			$tranmsg='"'.'会计凭证&#10;'.$chktranarr[0]['trandate'].'记:'.$chkTransNo.'&#10;';
			foreach($chktranarr as $val){
				 if($val['flg']==1){
					 if($val['amount']>0){
						$jdstr="借".$val['amount'];
					 }else{
						$jdstr="贷".(-$val['amount']);
					 }
				 }else{
					 if($val['amount']>0){
						$jdstr="贷".(-$val['amount']);
					 }else{
						$jdstr="借".$val['amount']; 
					 }
					
				}
				 $tranmsg.=$val['accunt'].' '.$val['accountname'].'  '.$jdstr.'&#13;';
			}
			$tranmsg.='摘要:'.$chktranarr[0]['narrative'].'"';
			//prnMsg($tranmsg);
		}else{
			//未有凭证
			$acc=ExplainAccount($accarr,$gljtemplet,$myrow);
			if ($acc=='0'){
				$acc=='该笔款对应单位未有设置';
				$LastJournal =1;
				$prdtransno=1;
			}else{
				$LastJournal =0;
				$prdtransno=0;
			}
			$chkTransNo=0;
		}
	}
	 if($LastJournal==2||$LastJournal==-1){
		$URL_to_TransDetail = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['period'].'^'.$myrow['transno'];
		//$URL_to_TransDetail = $RootPath . '/GLTransInquiry.php?prdno=' . $myrow['period'] . '&amp;TransNo=' . $chkTransNo;
	 }else{
		$URL_to_TransDetail='';
	 }
	
	if ($LastJournal ==1 &&$chkTransNo==0){
		echo '<tr style="background: #ecc;">'	;
	 }elseif ($LastJournal ==-1){
		echo '<tr style="background: #acc;">'	;
	 }else{
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
	}	
		$amountcurr=0;
		echo '<td>'.($RowIndex+1+($_SESSION['DisplayRecordsMax']*($_POST['PageOffset']-1))).'</td>
		      <input type="hidden" name="banktransid'.$RowIndex.'" value="' . $myrow['banktransid'] . '" />					
		      <td>'.$DisplayTranDate.'</td>';
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode !=$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] ){	
			 echo'<td class="number">'.locale_number_format($myrow['amount'],2).'</td>';
			 $amountcurr=round($myrow['amount'],2);
		}
		echo'<td class="number">'.locale_number_format($myrow['debit'],2).'</td>
			<td class="number">'.locale_number_format($myrow['credit'],2).'</td>';
			echo'<td class="number">'.locale_number_format($blnarr[$RowIndex+($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']],2).'</td>';
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode!=$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] ){	
		echo'<td class="number">'.locale_number_format(($blnarr[$RowIndex+($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']])/$CurrRate,2).'</td>';
		}
	
	echo'<td >'.$myrow['toname'].'</td>';
	if ($URL_to_TransDetail==''){
		echo'<td></td>';
	}else{
		echo'<td ><a href="'.$URL_to_TransDetail.'"  title='.$tranmsg.' target="_blank" >'.$chkTransNo.'</a></td>';
	}
	echo'<td >'.($jd==1?'借':'贷').'</td>
		<td >'.$acc.'</td>
		<td >'.$myrow['remark'].'</td>
		<td >
		<input type="hidden" name="LastJournal'.$RowIndex.'" value="' . $prdtransno. '" />';
     if($myrow['flag']==1){
		echo'<input type="checkbox" name="chkbx[]" value="'.$RowIndex .'" disabled="disabled" ></td>	';
	 }else{
		echo'<input type="checkbox" name="chkbx[]" value="'.$RowIndex .'"'.(($LastJournal ==1||$LastJournal ==2)?'disabled="disabled"':'checked ').' ></td>	';
	 }
		
	 
	echo'</tr>';
	
        $gltarr[$RowIndex]=array($DisplayTranDate,($myrow['debit']==''?(-$myrow['credit']):$myrow['debit']),$myrow['toname'],$jd,$acc,$myrow['remark'],$myrow['banktransid'],$LastJournal,$chkTransNo, $amountcurr)  ;
		$RowIndex = $RowIndex + 1;
   
}	//end of while loop
	echo '</table>';
}//287-Search
	  
	
	//凭证生成
	if (isset($_POST['TransSave'])){	
	    $rw=0;
	    $rr=1;
		if (count($_POST['chkbx'])>0){
			foreach($_POST['chkbx'] as $val){
				if (strlen(trim($_POST['LastJournal'.$val]))>1){
				   //凭证已经手工制作
				  // prnMsg(strlen(trim($_POST['LastJournal'.$val])).'-4='.$_POST['LastJournal'.$val]); 
					$result=DB_Txn_Begin(); 
					$sql="UPDATE banktransaction SET transno=".explode('^',$_POST['LastJournal'.$val])[1].",period=".explode('^',$_POST['LastJournal'.$val])[0]."  WHERE banktransid=".$_POST['banktransid'.$val];
					$result = DB_query($sql);
					$sql="UPDATE gltrans SET posted=1 WHERE periodno=".explode('^',$_POST['LastJournal'.$val])[0]." AND transno=".explode('^',$_POST['LastJournal'.$val])[1];
					$result = DB_query($sql);
					$result=DB_Txn_Commit();
					//$rr++;
					
				}else{//凭证自动生成
					if((int)$_POST['LastJournal'.$val]==0){
					//	prnMsg('0='.((int)$_POST['LastJournal'.$val]).'-'.$val);   
				    GenerateJournal($gltarr[$val],$_POST['BankAccount']);
				
					
					}
				}
				$rw++;
				
			}  
			
			prnMsg($rw.'笔凭证生成！','info');
	    }else{
			prnMsg('你没有选择！','info');
		}
	}
 			
}//if149
echo '</div>
      </form>';
include('includes/footer.php');
function GenerateJournal($row,$bankstr){
	//array(,$LastJournal,$chkTransNo, $amountcurr)  ;
	//0$DisplayTranDate,1amount,2$['toname'],3$jd,4$acc,5['remark']6['banktransid'] 7$LastJournal,8$chkTransNo, 9$amountcurr ;
	//0日期  1金额    2单位名 3  借贷4 科目5备注6摘要 凭证号 销售科目/材料 
	$prd=$_SESSION['period']-(substr($_SESSION['lastdate'],0,4)-substr($row[0],0,4))*12+(substr($row[0],5,2)-substr($_SESSION['lastdate'],5,2));
		//  计算对应期间     9-(2017-2018)*12+(1-9)
	$acc=substr(explode(']',$row[4])[0],1);
	$TransNo = GetTransNo($prd, $db);
	$sql="SELECT typeid FROM transtype WHERE account='".substr($acc,0,4)."'";
	$result=DB_query($sql);
	$typrow=DB_fetch_row($result);
	$bankarr=explode('^',$bankstr);
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $bankarr[1] !=$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] ){
		$amo=round($row[9],2);
	}else{
		$amo=round($row[1],2);
	}	
	if (empty($typrow)){
		if($row[1]>0){
			$typ=-12;
		}else{
			$typ=-22;
		}
	}else{
		$typ=$typrow[0];
	}
	$TypeNo = GetTypeNo($typ,$prd, $db);		
	//，插入凭证	
	DB_Txn_Begin();
	$inst=0;
	$SQL = "INSERT INTO gltrans (type,
									typeno,
									transno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									flg,
									tag,
									userid)
						VALUES ('".$typ."',
							'" . $TypeNo . "',
								'" . $TransNo . "',
								'" . $row[0] . "',
								'" . $prd . "',
								'" . $bankarr[0] . "',
								'".$row[5].$row[2]."', 
								'" . $amo ."',
								'1',
								'".explode('^',$_POST['BankAccount'])[2]."',
								'auto')";			
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
									flg,
									tag,
									userid)
								
						VALUES ('".$typ."',
							'" . $TypeNo . "',
								'" . $TransNo . "',
								'" . $row[0] . "',
								'" . $prd . "',
								'" . $acc . "',
								'".$row[5].$row[2]."', 
								'" .( -1*$amo) ."',
								'1',
								'".explode('^',$_POST['BankAccount'])[2]."',
								'auto')";	
	$result = DB_query($SQL);	
	if ($result){
			$inst++;
		}		 	
	  $SQL="UPDATE banktransaction SET  type=".$typ." ,transno ='".$TransNo."' ,period='".$prd."'  WHERE banktransid='".$row[6]."'";
	   $result=DB_query($SQL);				 
	if ($result && $inst==2){
	
		DB_Txn_Commit();
	}else{
		$TransNo='';
		DB_Txn_Rollback();		
	}	

	//return $sql;//$TransNo;FormatDateForSQL(
}
function ExplainAccount(&$accary,&$gljtemplet,$row){
	/*自动解析科目 */	
	//$row  收付款记录相关
	//客户单位注册号 账户号 及其他
	//$accary(0'',1['registerno'],2['regid']3['account'],4['flg'],5['custname'],6['acctype']7['custname']
	//$gljtemplet  0['acctype'],1['gljtype'],2['account'],3['remark'],4['abstract'],5'tag'6['jd'],7'maxamout'],8'flg'],9'accountname']);
	$ff=0;
	$jg=90;
	$retacc='0';
	$rr=0;
    //prnMsg($row['abstract'].$row['remark'].strpos('^'.$row['abstract'].$row['remark'],'薪资'));//mb_strlen(trim($row['toname'])));
	if ($row['toaccount']==''){//账号为空，用摘要解析科目
			foreach ($gljtemplet as $val){
				if ($val[1]<0){//gltype-

					if (strpos('^'.$row['abstract'].$row['remark'],trim($val[3]))>0){
						$rr++;
						$retacc='['.sprintf('%-10s', $val[2]).']'.$val[9];
						break;
					}
				}
			}
		}else{//账号不为空解析科目
			//用户名-科目=$accary(0'',1['registerno'],2['regid']3['account'],4['flg'],5['custname'],6['acctype']);
			//1.客户名为个人查询是否有工资再要
			//用户名小于5字个人，解析科目摘要优先
					if(mb_strlen(trim($row['toname']))<=5 && mb_strlen(trim($row['toname']))>=2){
						//如果是个人名
						
						foreach ($gljtemplet as $val){
							if ($val[1]==-3){//工资类别gltype=-3
							
								if (strpos('^'.$row['abstract'].$row['remark'],trim($val[3]))>0){
									$retacc='['.sprintf('%-10s', $val[2]).']'.$val[9];
									$rr++;
									break;
								}
							}
						}
		
					}
			//	prnMsg($rf.'='.$retacc);
			//2.没有工资的执行下面解析科目编码
			if ($retacc=='0'){
				
				foreach ($accary as $val){//用用户名解析对应科目
					
					//比较账户名称是否相符
					similar_text(subName($row['toname']),subName($val[7]),$ff);
						if ($ff>=$jg ){		//名称核对ok				
						$rr++;
							if ($val[3]!=''){							
								$retacc='['.sprintf('%-10s', $val[3]).']'.$val[5];
								if (trim($val[0])==trim($row['toaccount'])){							
									$retacc='['.sprintf('%-10s', $val[3]).']'.$val[5];
									break;
								}
							}else{
								//对应科目为空，查客户类，得到汇总科目
								//$gljtemplet  =0['acctype'],1['gljtype'],2['account'],3['remark'],4$['abstract'],5$['tag'],
								//6$row['jd'],7'maxamout']8,$['flg'],9['custname']);
								foreach ($gljtemplet as $glrow){
									if ($glrow[1]==$val[6]){//判断是收款还是付款默认应收应付只能概况gljtype
											$retacc='['.$glrow[2].']'.substr($glrow[9],(strpos($glrow[9],'-')+1));
											break;								
									}else{
										if (strpos('^'.$row['abstract'].$row['remark'],$glrow[3])>0){
											$retacc='['.$glrow[2].']'.$glrow[9];
											break;
										}
									}
								}//foreach
							}
					}//	if ($ff>=$jg )			
				}//foreach
			}
			if ($rr==0 ||($rr>0 && $retacc=='')){
				$retacc='0';//'该笔款对应单位未有设置';
			}
	}
	return $retacc;
}

function CheckTrans($row,$jd,$currcode,$transnoarr){
	/*根据交易和已经制作的会计凭证比对 */
	/*$ROW	banktransid,t.account bankacc,	type,	transno,period,	bankdate,
	// debit, credit,currcode,t.toaccount,	toname,	tobank,	t.remark,	t.abstract,	reliability,	flag,	t.flg
	$transnoarr  已经核对勾选会计凭证，防止重复勾选
	
	//1$row['account'] 不空查询 科目 2.查询多行按最近日期 3.收款凭证类别 付款凭证类别*/
	$transarr=array();
	if ($jd==1){
		$amo=$row['debit'];
	}else{
		$amo=-$row['credit'];
	}
	$BankDate = ConvertSQLDate($row['bankdate']);
	$transnostr='';
	if (count($transnoarr)>0){
		$transnostr=implode(',', $transnoarr);
	}
	//prnMsg($transnostr);
	//如果是外币以外币金额查询
    if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $currcode!=$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] ){	
		$sql="SELECT  transno,  amount, examount ,currid FROM currtrans   WHERE  account='".$row['bankacc']."' AND currcode='".$currcode."' AND period='".$row['period']."' AND date_format(transdate, '%Y%m')=date_format(".$BankDate.",'Ym') AND  transdate>='".$BankDate ."' AND examount='".$amo."'  ";
	    if($transnostr!=''){
			$sql.='AND transno  NOT IN ('.$transnostr.')';
		}
		$sql.='  ORDER BY transdate';
    }else{
	
		
		$sql="SELECT  transno, trandate,periodno, account FROM gltrans WHERE  date_format(trandate, '%Y%m')=date_format(".$BankDate.",'Ym') AND  trandate>='".$BankDate ."' AND amount='".$amo."' AND posted=0 AND LEFT(account,4)='1002'";
		if($transnostr!=''){
			$sql.='AND transno  NOT IN ('.$transnostr.')';
		}
		$sql.='  ORDER BY trandate';
	}
	
	//prnMsg($sql);
	$result=DB_query($sql);
	if (DB_num_rows($result)>=1){
		$tranrow=DB_fetch_row($result);
		
		$sql="SELECT transno, trandate,periodno, account,accountname, narrative, amount, a.tag, flg FROM gltrans a LEFT JOIN chartmaster b on a.account=b.accountcode WHERE periodno='".$tranrow[2]."' AND transno='".$tranrow[0]."' AND posted=0  ORDER BY trandate";
		$result=DB_query($sql);	
		$transarr=array();
		while ($myrow=DB_fetch_array($result)){
			array_push($transarr,$myrow);
		}		
	}else{
		$transarr=0;
	}
	

	return $transarr;
}
function subName($value){
	#  替换字符中的...
	$co=strlen($value);

	$value=str_replace('有限公司','',$value);
	if ($co==strlen($value)){
	$value=str_replace('有限责任公司','',$value);	
	}
	if ($co==strlen($value)){
	$value=str_replace('股份有限公司','',$value);	
	}
	if ($co==strlen($value)){
	$value=str_replace('集团有限公司','',$value);	
	}
	if ($co>strlen($value)){
	$value=str_replace('分公司','',$value);	
	}
	if (strpos($value,')')>0 || strpos($value,'）')){
		$i=strpos($value,'(');
		$e=strpos($value,')');
		if ($e>0){
			$value=substr($value,0,$i).substr($value,$e);
		}else{
			$i=strpos($value,'（');
			$e=strpos($value,'）');
			$value=substr($value,0,$i).substr($value,$e+3);	
		}
	}
	if (strpos($value,'-')>0 ){
		$value=str_replace('应收账款-','',$value);
		$value=str_replace('应付账款-','',$value);
		$value=str_replace('其他应收款-','',$value);	
		$value=str_replace('其他应付款-','',$value);			
	}
		return $value;
}

?>
