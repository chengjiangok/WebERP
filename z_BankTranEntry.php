
<?php
/*CashJournallize.php
 * @Author: ChengJiang 
 * @Date: 2017-10-28 15:03:14 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-08-15 10:42:59
 */
include('includes/session.php');
$Title ='银行交易录入';// Screen identificator.
$ViewTopic = 'BankTranactionEntry';// Filename's id in ManualContents.php's TOC.
$BookMark = 'BankTranactionEntry';// Filename's id in ManualContents.php's TOC.
echo'<script type="text/javascript">
		
 		function sltproduct(obj){
				
			window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?myslt="+obj.value ;
		}
		function ComboToInput(c, i,u) {
			i.value=c.value.split("^")[0];
			u.value=c.value.split("^")[1];
			document.getElementById("accname").value=c.value.split("^")[2];
		    document.getElementById("currate").value=getrate(c.value.split("^")[1]);			
		}
		function JorD(o, t, e) {
			if(o.value!="") {
				t.value="";
				e.value=(o.value*document.getElementById("currate").value).toFixed(2);
			}else if(o.value=="NaN") 
			o.value="";		
		}
		function ToDeCr(o, t, ye) {
						var yestr;
			if(o.value!="" && o.value!="NaN") {
				t.value="";
				yestr=(Number(ye)+Number(o.value)).toFixed(2);
			
			}else if(t.value!="" && t.value!="NaN"){
				o.value="";
				yestr=(Number(ye)+Number(t.value)).toFixed(2);
			}else{
				o.value="";	
			}
				document.getElementById("amoye").value=yestr;
		}
		function getrate(currcode){           
			var jsn=document.getElementById("ratejsn").value;	
		   var obj= JSON.parse(jsn);				
		   var temp = []; 	
		   var rat=0;			
			   for(var i=0; i<obj.length; i++)  
				 { 
				   temp[i]= (function(n){				  
				   if (obj[n].currabrev==currcode){						  
						   rat= obj[n].rate;   					    
				   }})(i);  
				 }  
			return rat;
		}
		function inSelect(v, tA, m) {
			for(i=0;i<tA.length;i++) {
				if(v.value==tA[i].value.split("^")[0]) {
					document.getElementById("accname").value=tA[i].value.split("^")[2];
					document.getElementById("currcode").value=tA[i].value.split("^")[1];
					document.getElementById("currate").value=getrate(tA[i].value.split("^")[1]);					
					return true;
				}
			}
			alert(m);
			document.getElementById("GLManualCode").value="";
			return false;
		}	
		
		function fun() {  
				location.reload();
		}  	
</script>';
/*function custcheng(myslt,tag){           
			var jsn=document.getElementById("custbankjsn").value;
		  
			// console.log(sltval); 
		   var list=myslt.value;
		   alert(list);
		   var maxspec=Number(Math.max.apply(this,  list));
		   var obj= JSON.parse(jsn);				
		   var temp = []; 	
		   var qut="";	
		
		   var ss = document.getElementById("bankid");
		   var op=document.createElement("option"); 		
			   for(var i=0; i<obj.length; i++)  { 
					   temp[i]= (function(n){				  
				   if (Number(obj[n].tag)==tag && list==obj[n].custname) {
						op.setAttribute("label",obj[n].bankaccount); 
						op.setAttribute("value",obj[n].bankaccount); 
					   
				   }
					   })(i);  
   				 }  
				
				 ss.appendChild(op);		 
		
	    }*/
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
} 
	
	$Type = 'Receipts';
	$TypeName =_('Receipts');
	$SQL="SELECT  `custname`,bankaccount, bankname ,A.tag FROM `registername` A LEFT JOIN accountsubject B ON A.regid=B.regid ";
		$RESULT = DB_query($SQL, $ErrMsg);
		$i=0;
		while ($row=DB_fetch_array($RESULT)){

			$custbankarr[$i]=array('custname'=>$row['custname'],'bankaccount'=>$row['bankaccount'],'bankname'=>$row['bankname'],'tag'=>$row['tag']);
			 $i++;
		  }
		  $custbankjsn=json_encode( $custbankarr);
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/bank.png" title="' .
		$Title . '" /> ' .// Icon title.
		  $Title.'</p>';// Page title.

echo '<div class="page_help_text">手工录入银行交易!</div><br />';

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
      <input type="hidden" id="custbankjsn" name="custbankjsn" value=' . $custbankjsn . ' />
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
	if (!isset($_POST['BankAccount'])){
		    $_POST['BankAccount']=$myrow['accountcode'] . '^'.$myrow['currcode']. '^'.$myrow['tag'];
			echo '<option  selected="selected"  value="' . $myrow['accountcode'] . '^'.$myrow['currcode']. '^'.$myrow['tag'].'">' . $myrow['accountcode'] . '[' .$myrow['currcode'].']'. $myrow['bankaccountname'] . '</option>';
	}elseif (isset($_POST['BankAccount'])AND $myrow['accountcode'].'^'.$myrow['currcode']. '^'.$myrow['tag']==$_POST['BankAccount']){

		echo '<option selected="selected" value="' . $myrow['accountcode'] .'^'.$myrow['currcode'].  '^'.$myrow['tag'].'">' . $myrow['accountcode'] . '[' .$myrow['currcode'].']' . $myrow['bankaccountname'] . '</option>';
	} else {
		echo '<option value="' . $myrow['accountcode'] . '^'.$myrow['currcode']. '^'.$myrow['tag'].'">' . $myrow['accountcode'] . '[' .$myrow['currcode'].']'. $myrow['bankaccountname'] . '</option>';
	}
}

echo '</select></td>
	</tr>';

echo '</table>';

$sql="SELECT  A.account,B.bankaccountname, MAX(bankdate) bankdt FROM banktransaction A LEFT JOIN bankaccounts B ON A.account=B.accountcode GROUP BY account";
$result=DB_query($sql);

while ($row= DB_fetch_array($result)) {
	$msg.= $row['account'].$row['bankaccountname'].'最末账单日期：'.$row['bankdt'].'<br>';
}
   $msg.=$rw;
  
   prnMsg($msg,'info');	     
//prnMsg($msg,'info');


$InputError=0;

	//翻页开始 
	$CurrCode=explode('^',$_POST['BankAccount'])[1]; 
	
	//$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	//$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);

	$BankResult = DB_query("SELECT decimalplaces,
									currcode,
									rate
							FROM bankaccounts INNER JOIN currencies
							ON bankaccounts.currcode=currencies.currabrev
							WHERE accountcode='" . explode('^',$_POST['BankAccount'])[0] . "'");
	$BankRow = DB_fetch_array($BankResult);
	$CurrDecimalPlaces = $BankRow['decimalplaces'];
	$CurrRate =round($BankRow['rate'],$CurrDecimalPlaces );
	$SQL="SELECT sum(amount) amount ,COUNT(banktransid) bankcount FROM banktransaction
	           WHERE   account='" . explode('^',$_POST['BankAccount'])[0] . "'";
	$result = DB_query($SQL);
	$row=DB_fetch_assoc($result);
	if (isset($row)){
		$balance=$row['amount'];
	}else{
		$balance=0;
	}
	
	$RowCount=$row['bankcount'];
	$lmt=10;
	if ($RowCount>10){
		$lmt=$RowCount-10;
	}
	$SQL="SELECT banktransid,bankdate FROM banktransaction
			   WHERE   account='" . explode('^',$_POST['BankAccount'])[0] . "' ORDER BY banktransid DESC LIMIT 1";
	$result = DB_query($SQL);
	$row=DB_fetch_assoc($result);
	$bankid=$row['banktransid'];
	$bankdt=date_format(date_create($row['bankdate']),"Y-m-d");
         
	if ($CurrCode==$_SESSION['CompanyRecord']['currencydefault']){ 
		$sql="SELECT	banktransid,
						T.account bankacc,
						type,
						transno,
						period,
						bankdate,
						0 amount,
						CASE WHEN(amount > 0 AND T.flg = 1) OR(amount < 0 AND T.flg = -1) THEN amount ELSE ''
						END debit,
						CASE WHEN(amount < 0 AND T.flg = 1) OR(amount > 0 AND T.flg = -1) THEN - amount ELSE ''
						END credit,
						currcode,
						T.toaccount,
						toname,
						tobank,
						T.remark,
						T.abstract,
						reliability,
						flag,
						T.flg
						FROM banktransaction AS T	
				WHERE  account='" . explode('^',$_POST['BankAccount'])[0] . "'
				ORDER BY banktransid LIMIT ".($lmt).",10";	
			
	}else{  //外币
		
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
					WHERE  t.account='" . explode('^',$_POST['BankAccount'])[0] . "'
					ORDER BY banktransid, bankdate";		

	}

		$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
		$Result = DB_query($sql, $ErrMsg);
		
if ( $Result ||isset($_POST['TransSave']) || isset($_POST['Search'])	) {
	
	    $ListCount=DB_num_rows($Result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}	
	 echo '<div>';
	 echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
    echo '<table cellpadding="2" class="selection">
		<tr>
			<th class="ascending">序号</th>							
			<th class="ascending">' . _('Date') . '</th>';
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode!=$_SESSION['CompanyRecord']['currencydefault'] ){					
				echo'<th class="number">外币折人民币</th>';
			}
		echo'<th class="ascending">收入金额</th>
			 <th class="ascending">支出金额</th>	
			  <th class="ascending">余额</th>	';	
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode!=$_SESSION['CompanyRecord']['currencydefault'] ){					
		echo'<th class="number">外币折人民币余额</th>';
    }			
		echo'<th class="ascending">对方名称</th>			
			<th style="word-wrap:break-word;word-break:break-all;">对应账号</th>
			<th >对应开户银行</th>				
			<th >摘要</th>
			<th class="ascending">凭证号</th>				
			<th >交易序号</th>
		</tr>';

	$k = 0; //row colour counter

   $RowIndex = 0;		
while ($myrow=DB_fetch_array($Result)){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		
		$amountcurr=0;
		echo '<td>'.($RowIndex+1).'</a></td>';
				 
		  echo'<td>'.$myrow['bankdate'].'</td>';
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode !=$_SESSION['CompanyRecord']['currencydefault'] ){	
			 echo'<td class="number">'.locale_number_format($myrow['amount'],2).'</td>';
			 $amountcurr=round($myrow['amount'],2);
		}
		echo'<td class="number">'.locale_number_format($myrow['debit'],2).'</td>
			<td class="number">'.locale_number_format($myrow['credit'],2).'</td>';
			if ($bankid==$myrow['banktransid']){
				echo'<td class="number">'.locale_number_format($balance,2).'</td>';
			}else{
				echo'<td class="number"></td>';
			}
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode!=$_SESSION['CompanyRecord']['currencydefault'] ){	
		echo'<td class="number">'.locale_number_format(0,2).'</td>';
		}
	
	echo'<td >'.$myrow['toname'].'</td>';
	echo'<td >'.$myrow['toaccount'].'</td>
		<td >'.$myrow['tobank'].'</td>
		<td >'.$myrow['remark'].'</td>		';
		echo'<td></td>
			<td >'.$myrow['banktransid'].'</td>	';	 
	echo'</tr>';

	
		 $RowIndex = $RowIndex + 1;  
}	//end of while loop

		$sql="SELECT `regid`, `custname`, `tag`, `account`, `regdate`, `flg` FROM `registername` where tag='".explode('^',$_POST['BankAccount'])[2]."'	 ";
		$result=DB_query($sql);


		echo '<tr class="OddTableRows">';
		echo'<td></td>';
		echo '<td><input type="date"  alt="" min="'.$bankdt.'" max="'.date('Y-m-d').'"  name="bankDate" maxlength="10" size="11" value="' . $bankdt . '" /></td>';
			

	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode !=$_SESSION['CompanyRecord']['currencydefault'] ){	
		echo'<td class="number">'.locale_number_format($myrow['amount'],2).'</td>';
		$amountcurr=round($myrow['amount'],2);
	}
	echo '<td><input type="text"  name="Debit" onchange="ToDeCr(this,Credit,'.$balance.')" maxlength="12" size="12" value="' . $_POST['Debit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  /></td>
	<td><input type="text"  name="Credit" onchange="ToDeCr(this,Debit,'.$balance.')" maxlength="12" size="12" value="' . $_POST['Credit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  /></td>';

		echo'<td ><input type="text" name="amoye" id="amoye"  maxlength="12" size="12" value="0" 　  title="匹配浮点数！"  /></td>';
	if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && $CurrCode!=$_SESSION['CompanyRecord']['currencydefault'] ){	
		echo'<td class="number"></td>';
	}
		echo'<td > <input type="text" id="cust_name" name="cust_name" list="cnid" onChange="custcheng(this,'.explode('^',$_POST['BankAccount'])[2].')" /> 
			<datalist id="cnid"> ';
			while ($row=DB_fetch_array($result)){
				echo '<option  value="'.$row['custname'].'"  label="'.$row['regid'].'" />'; 
			}
		echo'</datalist></td>';
		$sql="SELECT `bankaccount`,  `regid` FROM `accountsubject` WHERE tag=".explode('^',$_POST['BankAccount'])[2];
		$result=DB_query($sql);
	
		echo'<td > <input type="text" id="bank_acc" name="bank_acc"  list="bakid" /> 
			<datalist id="bakid" > ';
			while ($row=DB_fetch_array($result)){
				echo '<option value="'.$row['bankaccount'].'"  label="'.$row['regid'].'" />'; 
			}
		echo'</datalist></td>';

		echo'<td ><input type="text" name="bankname" value="" /></td>		';
		$sql="SELECT `remark`,srid FROM `subjectrule` WHERE acctype='1002'";
		$result=DB_query($sql);
		
		echo'<td > <input type="text" id="remark" name="remark"  list="rmkid" /> 
			<datalist id="rmkid" > ';
			while ($row=DB_fetch_array($result)){
				echo '<option value="'.$row['remark'].'"  label="'.$row['srid'].'" />'; 
			}
		echo'</datalist></td>';
		echo'<td></td><td ></td>	';	 
		echo'</tr>';
			echo '</table>';
			echo'<br />
			<div class="centre">
				<input tabindex="6" type="submit" name="Search" value="查询交易" />';
			
				   echo'<input type="submit"name="TransSave" value="录入交易" />	';	
						  
			//}
		echo '</div>';
}//287-Search
	  
	
	//凭证生成
	
 			
//}//if149
if (isset($_POST['TransSave'])){
	if ($_POST['remark']==''||($_POST['Debit']==''&& $_POST['Credit'])){
		prnMsg('摘要不能为空！','warn');
	}else{
	        $flg=1;
	    if ($_POST['Debit']!=0){
			$amt=$_POST['Debit'];
			if ($_POST['Debit']<0){
				$flg=-1;
			}
		}else{
			if ($_POST['Credit']<0){
				$flg=-1;
			}
			$amt=-$_POST['Credit'];
		} 	
		if ($amt!=0){
	     $regid=0;
	     $result=DB_Txn_Begin();
		$sql="INSERT INTO `banktransaction`( `account`,
		                                     `type`, 
											`transno`, 
											`period`,
											`bankdate`,
											 `amount`,
											 `currcode`,
											 `toaccount`,
											 `toname`,
											 `tobank`,
											 `remark`, `abstract`, `reliability`, `flag`, `flg`) VALUE(
											'".explode('^',$_POST['BankAccount'])[0]."',
											'0',
											'0',
											'0',
											'".$_POST['bankDate']."',
											'".$amt."',
											'".explode('^',$_POST['BankAccount'])[1]."',
											'".$_POST['bank_acc']."',
											'".$_POST['cust_name']."',
											'".$_POST['bankname']."',
											'".$_POST['remark']."',
											'',
											'',
											'1',
											'".$flg."')";
		$result=DB_query($sql);
		$sql="INSERT IGNORE INTO `registername`(`custname`, `tag`, `account`, `regdate`, `flg`) VALUE(
											'".$_POST['cust_name']."',
											'".explode('^',$_POST['BankAccount'])[2]."',
											'',
											'".date('Y-m-d')."',
											'0')";
		$result=DB_query($sql);
		$regid=DB_Last_Insert_ID($db,'registername','regid');
		if ($regid!=0){
		$sql="INSERT IGNORE INTO `accountsubject`(`bankaccount`, `tag`, `regid`, `subject`, `acctype`, `bankname`, `bankcode`, `flg`) VALUE(
										'".$_POST['bank_acc']."',
										'".explode('^',$_POST['BankAccount'])[2]."',
										'".$regid."',
										'',
										'0',
										'".$_POST['bankname']."',
										'',
										'0') ";
			$result=DB_query($sql);
		}
			$result=DB_Txn_Commit();
			if ($result){
				prnMsg($_POST['cust_name'].'交易:'.$amt.'录入成功！','info');
			}
			
		}else {
			prnMsg('金额不能为空！','warn');
		}
	}
}
echo '</div>
      </form>';
include('includes/footer.php');


?>
