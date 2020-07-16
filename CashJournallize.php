<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-03-23 16:39:02
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-07-12 18:07:02
 */
include('includes/session.php');
$Title ='银行凭证生成';// Screen identificator.
$ViewTopic = 'MyTools';// Filename's id in ManualContents.php's TOC.
$BookMark = 'CashJournallize';// Filename's id in ManualContents.php's TOC.
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include ('includes/GLAccountFunction.php');
echo'<script type="text/javascript">
	function SelectAct(S, tA,R) {

		var hrf=document.getElementById("href"+R);	
		var oUrl=hrf.href;	

		var paramName="ToActPrm";		
		var urlen=oUrl.indexOf(paramName);
		var OptionAct=S.value.split("^");
		var url="";
		var jsontoprm=JSON.stringify({ToAct:OptionAct[0],ToActName:OptionAct[1],Type:OptionAct[2]});
		if (urlen==-1){
			url=oUrl;
          
		}else{
			url=oUrl.slice(0,urlen);
		}
		var toprm=encodeURIComponent(jsontoprm);	   	
		     
		hrf.setAttribute("href",url+"&"+paramName+"="+encodeURI(toprm));
		document.getElementById("ToSelectAct"+R).value=toprm;		
		
		//var check = document.getElementById("chkbx")[1];
		//check.checked=true;	
	
		return false;
	}	
	function chilkradio(p,r) {

		var hrf=document.getElementById("href"+r);
		var oUrl=hrf.href;
		var paramName="ToActPrm";
		var urlen=oUrl.indexOf(paramName);
		var url=oUrl.slice(0,urlen);
		//没有用以下2行
		var re=eval("/("+ paramName+"=)([^&]*)/gi");
		var nUrl = oUrl.replace(re,paramName+"="+p.value);
		
		hrf.setAttribute("href",url+paramName+"="+p.value);			
	}
	function replaceParamVal(paramName,replaceWith) {
		//没有使用，？到参数
		var oUrl = this.location.href.toString();
		var re=eval("/("+ paramName+"=)([^&]*)/gi");
		var nUrl = oUrl.replace(re,paramName+"="+replaceWith);
		this.location = nUrl;
	　　window.location.href=nUrl;
	}
	function getHref(n,rpl,d){
		//没有使用，？到参数
		var oUrl =n.href; 
		var paramName="ToActPrm";
		var re=eval("/("+ paramName+"=)([^&]*)/gi");
		var nUrl = oUrl.replace(re,paramName+"="+rpl);	
		document.getElementById("href"+d).href=nUrl;
	}
	
	
</script>';
if (!isset($_POST['ERPPrd'])OR $_POST['ERPPrd']==''){
	$_POST["ERPPrd"]=$_SESSION['period'];
}
if (!isset($_POST['prdrange']) OR $_POST['prdrange']==''){
	$_POST['prdrange']=0;		  	
}
if (!isset($_POST['BankAccount'])){
	if (isset($_SESSION['SelectBank'])){
		//$_SESSION['SelectBank']该参数传递给GLTransCreate
		$_POST["BankAccount"]=$_SESSION['SelectBank'][0][0]."^".$_SESSION['SelectBank'][0][1]."^".$_SESSION['SelectBank'][0][2]."^".$_SESSION['SelectBank'][0][3];
		$_POST["ERPPrd"]=$_SESSION['SelectBank'][1];
	}
	
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
}    
	$Type = 'Receipts';
	$TypeName =_('Receipts');
	if (!isset($CurrRateArr)){   //读取外币汇率
		$sql="SELECT currabrev, ROUND(rate,decimalplaces) rate ,decimalplaces FROM currencies  WHERE currabrev<>'".CURR."'";
		$Result=DB_query($sql);
      
		while ($row=DB_fetch_array($Result)){
			$CurrRateArr[trim($row['currabrev'])]=array($row['rate'],$row['decimalplaces']);			
		}
				   
	}
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/bank.png" title="' .	$Title . '" /> ' .// Icon title.
		  $Title.'</p>';// Page title.
echo '<div class="page_help_text">通过导入的银行交易,自动解析、生成会计凭证!</div><br />';
echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	 
      <input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />
      <input type="hidden" name="Type" value="' . $Type . '" />';

	$sql = "SELECT	bankaccounts.accountcode,
					bankaccounts.bankaccountnumber,
					bankaccounts.bankaccountname,
					bankaccounts.currcode,
					chartmaster.tag
				FROM bankaccounts
				LEFT JOIN chartmaster ON chartmaster.accountcode = bankaccounts.accountcode,
					bankaccountusers
					WHERE bankaccounts.accountcode=bankaccountusers.accountcode
					AND bankaccounts.importformat IN (SELECT  bankid FROM bankformat) 
					AND bankaccountusers.userid = '" . $_SESSION['UserID'] ."'
			ORDER BY bankaccounts.accountcode";
	$result = DB_query($sql);
	//本单位   账号 =>科目  币种  名
	while ($myrow=DB_fetch_array($result)){
		//准备放弃
		$BankAccountArr[$myrow['accountcode']]=array($myrow['bankaccountnumber'],$myrow['currcode'], $myrow['bankaccountname'] ,0,0,'');
		
		//$BankActNumber$BankActNumber[trim($myrow['bankaccountnumber'])]=array($myrow['accountcode'],$myrow['currcode'],$myrow['bankaccountname'],$myrow['tag']);
		//新增
		$BankActData[trim($myrow['bankaccountnumber'])]=array("accountcode"=>$myrow['accountcode'],"currcode"=>$myrow['currcode'],"bankaccountname"=>$myrow['bankaccountname'],"tag"=>$myrow['tag'],"rate"=>round($CurrRateArr[$row['currcode']][0],4));
	}
	//print_r($BankActData);  //本企业银行账户
	//$ BankAccountArr=array();//银行账户资料 autofocus="autofocus" 
	echo '<table class="selection">
		<tr>
			<td align="left">' . _('Bank Account') . ':</td>
			<td colspan="2">
			<select tabindex="1" name="BankAccount">';

	foreach($BankActData as $key=>$row){
		
		if (isset($_POST['BankAccount'])AND $row['accountcode'].'^'.$row['currcode']. '^'.$row['tag']. '^'.$key.'^'.$row['rate']==$_POST['BankAccount']){
			echo '<option selected="selected" value="' . $row['accountcode'] .'^'.$row['currcode'].  '^'.$row['tag']. '^'.$key.'^'.$row['rate'].'">' . $row['accountcode'] . '[' .$row['currcode'].']' . $row['bankaccountname'] . '</option>';
		} else {
			
			echo '<option value="' . $row['accountcode'] . '^'.$row['currcode']. '^'.$row['tag']. '^'.$key.'^'.$row['rate'].'">' . $row['accountcode'] . '[' .$row['currcode'].']'. $row['bankaccountname'] . '</option>';
		}
		if (!isset($_POST['BankAccount'])){
			$_POST['BankAccount']=$row['accountcode'] . '^'.$row['currcode']. '^'.$row['tag']. '^'.$key.'^'.$row['rate'];
		}
	}
	echo '</select></td>
		</tr>';
echo '<tr>
		<td>选择会计期间</td>
		<td colspan="2">';
		SelectPeriod($_SESSION['period'],$_SESSION['janr']);
		/*		
	    $rang=array('0'=>'月度', '3'=>'季度','12'=>'本年','24'=>'上年','36'=>'  年');
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
echo'</select>*/
     echo'</td></tr>';      

echo'</table>';
$LastDate=PeriodGetDate($_POST['ERPPrd']);
$firstprd=$_POST['ERPPrd'];
	 $endprd=$_POST['ERPPrd'];
	 $_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr($LastDate,0,7).'-01')));
	 $_POST['BeforeDate']=FormatDateForSQL($LastDate);	
	 $EndofLastDate=PeriodGetDate($_POST['ERPPrd']-1);	
/*
if ($_POST['prdrange']==0){
	$firstprd=$_POST['ERPPrd'];
	 $endprd=$_POST['ERPPrd'];
	 $_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr($LastDate,0,7).'-01')));
	 $_POST['BeforeDate']=FormatDateForSQL($LastDate);		
}elseif ($_POST['prdrange']==3) {
	$firstprd=$_SESSION['janr']+ceil(($LastDate-$_SESSION['janr']+1)/3)*3-3;
	$endprd=$_SESSION['janr']+ceil(($LastDate-$_SESSION['janr']+1)/3)*3-1;
	$_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr($LastDate,0,5).($endprd-2-$_SESSION['janr']+1).'-01')));
	//这句有错误
	$BeginDate=date('Y-m-01', strtotime(substr($LastDate,0,5).($endprd-$_SESSION['janr']+1).'-01'));
	$_POST['BeforeDate']=FormatDateForSQL(date('Y-m-d',strtotime ("$BeginDate +1 month -1 day")));
}elseif ($_POST['prdrange']==12) {
	$firstprd=$_SESSION['janr'];
	   $endprd=$_POST['ERPPrd'];
	   $_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr($LastDate,0,5).'01-01')));
	   $_POST['BeforeDate']=FormatDateForSQL($_SESSION['lastdate']);	  		
}elseif ($_POST['prdrange']==24 &&$_SESSION['janr']>=13){
	$firstprd=$_SESSION['janr']-12;
	   $endprd=$_SESSION['janr']-1;	
}elseif($_POST['prdrange']==36 && $_SESSION['janr']>=25){
	 $firstprd=$_SESSION['janr']-24;
	   $endprd=$_SESSION['janr']-13;		
}	*/
$SelectBankAct=explode('^',$_POST['BankAccount']);
$CurrCode=$SelectBankAct[1]; 
$tag=$SelectBankAct[2];
if (empty($tag)){
	$tag=$_SESSION['Tag'];
}
//echo $_POST['ERPPrd'];
//0-account1currcode 2tag3number
//  下读取银行账户资料
$sql="SELECT `account`, SUM(`amount`) amount FROM `gltrans` WHERE periodno<=".$_POST['ERPPrd']."  GROUP BY account";
$result=DB_query($sql);

while ($row= DB_fetch_array($result)) {
	$EndofLastAmount[$row['account']]=$row['amount'];
}
//,SUM(CASE WHEN bankdate<='".FormatDateForSQL($EndofLastDate)."' THEN `amount` ELSE 0 END) endoflastamo 
$sql="SELECT account,SUM(amount) endamount  FROM banktransaction GROUP BY account";
$result=DB_query($sql);

while ($row= DB_fetch_array($result)) {
	$BankAccountArr[$row['account']][3]=$row['endamount'];
	//$BankAccountArr[$row['account']][6]=$row['endoflastamo'];
}
$sql="SELECT account,COUNT(*) countfile FROM bankupload WHERE flag=0 GROUP BY account";
$result=DB_query($sql);

while ($row= DB_fetch_array($result)) {
	$BankAccountArr[$row['account']][4]=$row['countfile'];
}

$sql="SELECT  account, MAX(bankdate) bankdt FROM banktransaction  GROUP BY account";
$result=DB_query($sql);	
while ($row=DB_fetch_array($result)){
	$BankAccountArr[$row['account']][5]=$row['bankdt'];
}
	echo '<table cellpadding="2" class="selection">
			<tr>
				<th >序号</th>							
				<th >账户科目</th>	
				<th >开户行名称</th>
				<th >币种</th>	
				<th >会计账余额</th>
				
				<th >账户余额</th>
				<th >最末日期</th>
				<th >未更新文件数</th>							
			</tr>'; 
	$RowIndex=0;  
	foreach($BankAccountArr as $key=>$val) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}			
		   echo'<td>'.($RowIndex+1).'</td>
				<td>'.$key.'</td>
				<td  title="'.$val[0].'">'.$val[2].'</td>
				<td>'.$val[1].'</td>
				<td class="number">'.locale_number_format($EndofLastAmount[$key],2).'</td>
			
				<td class="number">'.locale_number_format($val[3],2).'</td>
				<td >'.substr(trim($val[5]),0,10).'</td>
				<td>'.$val[4].'</td>
			</tr>';				
		$RowIndex++;
				
	}
	echo '</table>';
    
echo'<br />
	<div class="centre">
		<input tabindex="6" type="submit" name="Search" value="查询交易" />';
	if (isset($_POST['Search'])|| $_POST['PageOffset']>1||isset($_POST['Go']) ||isset($_POST['Next']) ||isset($_POST['Previous'])){			
	   echo'<input type="submit"name="TransSave" value="凭证保存" />	';	
	}
	//echo'<input type="submit"name="Demo" value="DebugDemo" />	';
echo '</div>';
	$InputError=0;
	//翻页开 

if (isset($_POST['CheckSave']) ||isset($_POST['TransSave']) || isset($_POST['Search']) OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])||!isset($_SESSION['SelectBank'][3])) {
	
     /*
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}*/
	$_SESSION['SelectBank']=array($SelectBankAct,$_POST['ERPPrd'],$LastDate);

	//读取摘要规则
	
	if (!isset($ActRule)||count($ActRule)<1){
		//$SubRule=array();
		$sql="SELECT acctype, account,accountname,srtype, remark, abstract, A.tag, jd, maxamount, A.flg 
				FROM subjectrule A
				LEFT JOIN chartmaster B ON A.account=B.accountcode
				WHERE srtype<0";
		$result = DB_query($sql);		
		while ($row = DB_fetch_array($result)) {
			//$ SubRule[]=array($row['acctype'],$row['srtype'],$row['account'],trim($row['remark']),$row['abstract'],$row['tag'],$row['jd'],$row['maxamout'],$row['flg'],$row['accountname']);
			$ActRule[]=array("acctype"=>$row['acctype'],"srtype"=>$row['srtype'],"account"=>$row['account'],"remark"=>trim($row['remark']),"abstract"=>$row['abstract'],"tag"=>$row['tag'],"jd"=>$row['jd'],"maxamount"=>$row['maxamout'],"flg"=>$row['flg'],"actname"=>$row['accountname']);
		}
	}
	//var_dump($ActRule);
	//读取默认对应科目
	if (!isset($SubPub)){
		$sql="SELECT acctype, account,accountname,srtype 
		       FROM subjectrule A 
			   LEFT JOIN chartmaster B ON A.account=B.accountcode
			    WHERE A.tag=".$tag." OR A.tag<1";
		$result = DB_query($sql);
		//$SubPub=array();
		while ($row = DB_fetch_array($result)) {
			if ($row['srtype']>0){
				$SubPub[$row['acctype']]=array($row['account'],$row['accountname']);	
			}
		}
	}
	$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
	$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);
	
	$SQL="SELECT sum(amount) 
	       FROM banktransaction
	       WHERE  DATE_FORMAT(bankdate,'%Y-%m-%d') < '". $SQLAfterDate . "'
						AND account='" . explode('^',$_POST['BankAccount'])[0] . "'";
	$result = DB_query($SQL);
	$row=DB_fetch_row($result);

	if (isset($row)){
		$balance=$row[0];
	}else{
		$balance=0;
	}
	//echo $balance;
	if ($CurrCode==CURR){ 
		$sql="SELECT	banktransid,
						T.account ,
						type,
						transno,
						period,
						bankdate,
						amount ,
						0 examount,
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
				WHERE  DATE_FORMAT(bankdate,'%Y-%m-%d') >= '". $SQLAfterDate . "'
					AND DATE_FORMAT(bankdate,'%Y-%m-%d') <= '" . $SQLBeforeDate . "'
					AND T.account='" . explode('^',$_POST['BankAccount'])[0] . "'
				ORDER BY banktransid";	
	}else{  
		//外币  amount 人民币  debit credit 外币 
		$sql="SELECT banktransid,
					t.account ,
					type,
					transno,
					period,
					bankdate,
					amount ,
					round(amount/rate, decimalplaces) examount,
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
					FROM banktransaction as t
					LEFT JOIN currencies ON currencies.currabrev=t.currcode			
					WHERE  DATE_FORMAT(bankdate,'%Y-%m-%d') >= '". $SQLAfterDate . "'
						AND DATE_FORMAT(bankdate,'%Y-%m-%d') <= '" . $SQLBeforeDate . "'
						AND t.account='" . explode('^',$_POST['BankAccount'])[0] . "'
					ORDER BY banktransid";		
	}
		$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
		//echo $sql;
		$result = DB_query($sql, $ErrMsg);
	
		if (!isset($ForeignCurrAct)){
			$SQL="SELECT `accountcode`, `accountname`, `currcode` 
			FROM `chartmaster`
			WHERE length(accountcode)>4 
			AND currcode<>'".CURR."'";
			$Result = DB_query($SQL);
			//$ForeignCurrAct=array();

			while ($row=DB_fetch_array($Result)){
				$ForeignCurrAct[$row['accountcode']]=$row['currcode'];
			} 
		}
		//解析账号 户名对应科目
	if (!isset($CustomeAct)){
			 //读取交易，解析出账号》客户名。。。。
			 /**flag  初始值3 有单位无科目0 系统中无此客户-2 转户客户9  无信用代码-1 //无此银行账号-3 */
		while($row=DB_fetch_array($result))	{
			if ($row['toaccount']!=''&&strlen($row['toaccount'])>5){
				/*if (strlen($row['toname'])<=15&& $row['toname']!=''){
					$TypeCust=3;//得到客户类型1,2,3,0
				}*/
				if (!isset($CustomToAct['account'])){//>>?????
				
					$CustomData[$row['toaccount']]=array('account'=>'','actname'=>'','regid'=>0,"toname"=>$row['toname'],"tobank"=>$row['tobank'],"flag"=>3,'TypeCust'=>0);
				}
			} 
		}
		//var_dump($CustomData);
		foreach($CustomData as $key=>$row){
			
			if (!isset($BankActData[$key])){ //不是本单位银行账户
				$SQL="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` FROM `register_account_sub` WHERE custname LIKE '".$row['toname']."' 		OR bankaccount ='".$key."'";
				$Result = DB_query($SQL);	
				$regid=0;
				$custrow=DB_num_rows($Result);
				if ($custrow==1){
					$ROW=DB_fetch_assoc($Result);
					
					if ($ROW['sub']!=''){
						$CustomData[$key]['account']=$ROW['sub'];
						$CustomData[$key]['actname']=$ROW['custname'];
						$CustomData[$key]['regid']=$ROW['regid'];
						if ($CustomData[$key]['flag']==3){
							if ($key==$ROW['bankaccount']){
								$CustomData[$key]['flag']=0;	
							}else{
								$CustomData[$key]['flag']=-3;	//无此银行账号
							}		
						}	
						$CustomData[$key]['custname']=$ROW['custname'];							
						if (substr($ROW['sub'],0,4)==YSZK ||substr($ROW['sub'],0,4)==YFZK)
							$CustomData[$key]['TypeCust']=substr($ROW['sub'],0,1); //1客户 2供应商
						else						
						$CustomData[$key]['TypeCust']=5;//个人单位挂账
					}else{
						$CustomData[$key]['regid']=$ROW['regid'];
						$CustomData[$key]['custname']=$ROW['custname'];
						if ($CustomData[$key]['flag']==3){
							if ($key==$ROW['bankaccount'] ){
								$CustomData[$key]['flag']=0;	
							}else{
								$CustomData[$key]['flag']=-3;	
							}	
						}	
					}
				}elseif($custrow>1){
					$f=0;
					while($Row=DB_fetch_array($Result)){
						if ($Row['sub']!='' && empty($CustomData[$key]['account'])){
							$CustomData[$key]['account']=$Row['sub'];
							$CustomData[$key]['actname']=$Row['custname'];
							$CustomData[$key]['regid']=$Row['regid'];
						
							$CustomData[$key]['custname']=$Row['custname'];			
							if (substr($Row['sub'],0,4)==YSZK ||substr($Row['sub'],0,4)==YFZK){
								$CustomData[$key]['TypeCust']=substr($Row['sub'],0,1); //1客户 2供应商
							}else{						
								$CustomData[$key]['TypeCust']=5;//个人单位挂账
							}
						}
						if ($CustomData[$key]['flag']==3){
							
							if ($key==trim($Row['bankaccount'])){
								$CustomData[$key]['flag']=0;	
							}else{
								$CustomData[$key]['flag']=-3;	
							}		
						}	
					}
				}else{//系统中无有此客户
						$CustomData[$key]['flag']=-2;
				}
			}else{
				$CustomData[$key]['TypeCust']=9;
				$CustomData[$key]['flag']==9;
			}
			if ($CustomData[$key]['flag']==2||$CustomData[$key]['flag']==1){
				
				//如果账号没有插入，户名没有插入
				$regid=UpdateCustomer($row,$SelectBankAct);
			}
		}//end foreach
			
		   $CustomeAct=$CustomData;
		   unset($CustomData);	
	}		
		//调试使用
     //foreach($CustomeAct as $ky=>$val){	 echo  $ky,$val['toname'],"......",$val['flag'],"......",$val['TypeCust'],$val['account'],"<br>";}
	 
	   //得到需要更的客户名号	  
		$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}	
	if ($ListCount>0 AND (isset($_POST['Search'])||isset($_POST['TransSave'])||isset( $_SESSION['SelectBank'])	
		OR isset($_POST['Go'])	OR isset($_POST['Next']) OR isset($_POST['Previous']))){
			DB_data_seek($result,0);
		//if (!isset($blnarr)||empty($blnarr)){//计算余额
	
			while ($myrow=DB_fetch_array($result)) {
					$balance+=(float)$myrow['debit']-(float)$myrow['credit'];
					$blnarr[]=$balance;
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
				<th title="点击自动生成会计凭证!">序号</th>							
				<th >' . _('Date') . '</th>';
		if ( $CurrCode!=CURR ){					
			echo'<th>收付额<br>'.$_SESSION['CompanyRecord'][$tag]['currencydefault'].'</th>';
		}
			echo'<th >收入金额<br>'.$CurrCode.'</th>
				 <th >支出金额<br>'.$CurrCode.'</th>	
				 <th >余额<br>'.$CurrCode.'</th>	';	
		if ($CurrCode!=CURR ){					
			echo'<th >余额<br>'.$_SESSION['CompanyRecord'][$tag]['currencydefault'].'</th>';
		}			
			echo'<th >对方名称</th>
				
				<th >借/贷</th>
				<th style="word-wrap:break-word;word-break:break-all;">凭证科目内容</th>	
				<th >凭证号</th>			
				<th >摘要</th>			
				<th ></th>
			</tr>';
		$k = 0; //row colour counter
		$RowIndex = 0;	
		if (DB_num_rows($result) <> 0) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		$ToType = 0;//交易记录属性
		//$ chkTransNo=0;
		//$ TransType=-1;改换用途//银行交易类型 单位1  个人2  账号 0 摘要单位3  个人4 + - 解析正确和错误		
		//$ BankTranIdArr=array();//转户对应的
		//$ BankTranKeyArr=array();//转户账户
		$TransNoArr=array();	
			//读取解  科目规则$SubjectRule	
		//客户对    科目	
		if ( !isset($SubjectRule)){
			//管理费
			$sql="SELECT account,remark, jd, maxamount,  srtype,flg ,currcode 
			        FROM subjectrule a 
					LEFT JOIN chartmaster b ON a.account=b.accountcode 
					WHERE a.tag=".$tag." AND a.acctype=5 ORDER BY account";
			//prnMsg($sql);
			$Result = DB_query($sql);		
			while ($row = DB_fetch_array($Result)) {
				$SubjectRule[]=array($row['account'],$row['remark'],$row['jd'], $row['maxamount'],$row['flg']);				
			}
		}
		    
		if ( !isset($SelectRule)){
			
			$sql="SELECT `account`,remark, `srtype`,acctype, `jd`, `maxamount`, `flg` 
				   FROM `subjectrule` WHERE srtype=12  AND tag=".$tag."  ORDER BY account";
		
			$Result = DB_query($sql);		
			while ($row = DB_fetch_array($Result)) {
				$SelectRule[]=array("account"=>$row['account'],"actname"=>$row['remark'],"jd"=>$row['jd'], "maxamount"=>$row['maxamount'],"flag"=>$row['flg'],"acctype"=>$row['acctype']);
				
			}
		}
		//var_dump($SelectRule );	
		//根据注册码设定对  支出科   
		//$ GLTemplet=array();准   放弃
		/*
		if ((isset($GLTemplet) && count($GLTemplet)<1)|| !isset($GLTemplet)){
			$sql="SELECT registercode,
						 account,						
						 jd,
						 maxamount,
						 remark
					FROM gltemplet a					
					WHERE a.tag=".$tag."  AND tpdate<='".$_SESSION['lastdate']."'AND a.acctype =5 ";
			$Result = DB_query($sql);			
			//根   注册码设定特殊科目解    
			while ($row = DB_fetch_array($Result)) {
				$GLTemplet[$row['registercode']]=array($row['account'], $row['jd'], $row['maxamount'],  $row['remark']);
			}
		}*/
	    //  foreach($CustomeAct  as $ky=> $row){
         //   echo $ky .$row['toname'].$row['regid'].$row['account'].'<br/>';
		// }	
		/**解析银行交易科目 */
		while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
			$ToAct='';
				$ToActName='';
				$regid=0;
			if ($myrow['debit']!=0){
				$jd=1;//收款
			}else{
				$jd=-1;
			}  
			$bankDate = ConvertSQLDate($myrow['bankdate']);
			$TransContent='';		
			$TransNo=0;
			if ($myrow['transno']>0){
				//已经录入凭证并核对
				$TransNo=$myrow['transno'];
				$TransContent=GetTransContent($myrow['period'],$myrow['transno'],$SelectBankAct);
				 //prnMsg($TransContent);
			}else{
				//解析科目  成功返回科   等数组，不成功或异常返回数字，在得到客户-科目数组 $CustomeAct中的异常在下面方法返回异常标记-1
				$ToAct='';
				$ToActName='';
				$regid=0;
				$ToActType=-1;//-1解析不到科目		
			    $flag=-1;
				if ($myrow['toaccount']=='' ||strlen($myrow['toaccount'])<5){//1-账号为空，以摘要解析科目
					$flag=0;
					foreach ($ActRule as $key=>$glrow){	
						//prnMsg($myrow['abstract'].$myrow['remark'],$glrow['remark']);					
						if (strpos($myrow['abstract'].$myrow['remark'],$glrow['remark'])!==false){
							$ToAct=$glrow['account'];
							$ToActName=$glrow['actname'];
							$ToActType=3; //	3无账号摘要解析	成功
							break;
						}
					}//foreach
				}elseif (isset($CustomeAct[$myrow['toaccount']])){//&&strlen($myrow['toaccount'])>5){ 
					
					$flag=$CustomeAct[$myrow['toaccount']]['flag'];
					
					//有账号没有设定科目//[个人按摘要计入工资]	
					if((strlen(trim($myrow['toname'])))<=15 && $myrow['toname']!=''){
						$ToActType=-1;  //如果是个人名 摘要优先判断			
						foreach ($ActRule as $key=>$val){			
							if ($val["srtype"]==-3){//工资类别
								//prnMsg($myrow['abstract'].$myrow['remark'],$val['remark']);	
								if (strpos($myrow['abstract'].$myrow['remark'],trim($val['remark']))!==false){
									$ToAct= $val['account'];
									$ToActName=$val['actname'];
									$ToActType=1; //个人摘要解析标记	
									break;
								}										
							}							
						}//end foreach			
					}
					if ($ToAct==''){
						//prnMsg($CustomeAct[$myrow['toaccount']]['account'].'='.$myrow['toaccount'].')<'. $myrow['toname']);
						//有账号，转户以外的交易
						$TypeCust=$CustomeAct[$myrow['toaccount']]['TypeCust'];//得到客户类型1,2,3,0
						$regid=$CustomeAct[$myrow['toaccount']]['regid'];
						$ToAct=$CustomeAct[$myrow['toaccount']]['account'];					
						$ToActName=$CustomeAct[$myrow['toaccount']]['actname'];
						if ($ToAct!='' ){
							if (substr($ToAct,0,4)==YSZK ||substr($ToAct,0,4)==YFZK)
							$ToActType=substr($ToAct,0,1); //1客户 2供     
							else
							$ToActType=5; //个人位挂账
							
						}else{
							$ToActType=$TypeCust;//得到客户类型1,2,3,0
						}
					}
				}elseif (isset($BankActData[trim($myrow['toaccount'])])){
					//自己的银行账户		
					//$CurrToType 银行转户标记  1-同币2-转换币种
					//$ToActType   9转户  2 单位 3无账号费用 1个人摘要 		
					$bankdt=date($_SESSION['DefaultDateFormat'],strtotime ("+3 day", strtotime($myrow['bankdate'])));
					$bankdt1=date($_SESSION['DefaultDateFormat'],strtotime ($myrow['bankdate']));	
					$ToAct=$BankActData[$myrow['toaccount']]['accountcode'];					
					$ToActName=$BankActData[$myrow['toaccount']]['bankaccountname'];
					$ToActType=9;		//转户标   	9
					$flag=9;
					
				}
				if (isset($ForeignCurrAct[$ToAct])){
					$ToCurrCode=$ForeignCurrAct[$ToAct];
				}else{
					$ToCurrCode=CURR;
				}
				/*
				//prnMsg($ToAct.'='.$ToActName);
				if ($ToAct==''){
					$ToActType=-1;
				}else{
					$rowact=array("account"=>$ToAct,"actname"=>$ToActName,"regid"=>$regid,"ToActType"=>$_ToActType); 
				}
					//"ToActType"=>-1 解析       科目9 转户，1客户  ，2供应商 3 无帐号摘要解  ,4个人摘要解析/5解析不成功 6客户解   不成功，7供应商解析不成功 
				^/
				/*
				$getactname=GetCustomeAct($myrow,$CustomeAct,$ActRule,$BankActData);	
				 //var_dump($getactname).'<br/>';;//['account'].$getactname['actname']
				// echo $getactname."<br/>";
				if (is_array($getactname)){			 
					$ToAct=$getactname['account'];
					$ToActName=$getactname['actname'];
					$regid=$getactname['regid'];
					if (isset($ForeignCurrAct[$ToAct])){
						$ToCurrCode=$ForeignCurrAct[$ToAct];
					}else{
						$ToCurrCode=CURR;
					}
					unset($getactname);
				}else{
					$ToActType=$getactname;
					// "ToActType"=>-1 解析不到科目9 转户，1客户     2供应商 3 无帐号摘要解析,4个人摘要解析/5解析不成功 6客户��析成功，7供应商解析不成功 
						//prnMsg((string)$ToActType.''.$myrow['toname']);
				}
			    */
			}
			$ToBank='';
			if (isset($BankActData[trim($myrow['toaccount'])])){ 
				$ToAct= $BankActData[trim($myrow['toaccount'])]['accountcode'];
				$ToActName=$BankActData[trim($myrow['toaccount'])]['bankaccountname'];
				$ToCurrCode=$BankActData[trim($myrow['toaccount'])]['currcode'];
				//转户交易
				$ToBankTrans=GetBankTrans($myrow,$BankActData,$SelectBankAct,$CurrRateArr); 
				$ToBank=$ToBankTrans[0]['bankdate'].'^'.$ToBankTrans[0]['ToAmo'].'^'.$ToBankTrans[0]['ToBankTransID'].'^'.$ToBankTrans[0]['ToCurrCode'].'^'.$ToBankTrans[0]['ToAct'];
			}
			//每行的原始参数
			$GetUrl=urlencode(json_encode(array("BankTransID"=>$myrow['banktransid'],"BankDate"=>$myrow['bankdate'],"ToAccount"=>$myrow['toaccount'],"ToName"=>$myrow['toname'],
			              "CurrCode"=>$myrow['currcode'],"Amount"=>$myrow['amount'],"ExAmount"=>$myrow['examount'],"flg"=>$myrow['flg'],"Remark"=>$myrow['remark'],"Abstract"=>$myrow['abstract'],"tag"=>$tag,
						  "ToAct"=>$ToAct, "ToActName"=>$ToActName,"ToCurrCode"=>$ToCurrCode,"decimalplaces"=>$CurrRateArr[$ToCurrCode][1], "RegID"=>$regid,"flag"=>$flag,"ToActType"=>$ToActType,"ToBank"=>$ToBank),JSON_UNESCAPED_UNICODE));
			//	$ToAct=array("ToAct"=>$ToAct,"ToActName"=>$ToActName,"ToCustName"=>$myrow['toname']);
			$UrlTransPDF = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['period'].'^'.$myrow['transno'];
			$UrlToTrans = $RootPath . '/GLTransCreate.php?GLPrm='.$GetUrl;
			//'&ToActPrm='.$_POST['ToSelectAct'];
			$BankCuRate=$CurrRateArr[explode('^',$_POST['BankAccount'])[1]][0];
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}			
				//$amountcurr=0;
				//echo'<td><a href="'.$URL_CrtJournal .'" id="href'.$RowIndex.'" name="href'.$RowIndex.'"  title="点击   成会计凭证" target="_blank"  id="href'.$RowIndex.'" name="href'.$RowIndex.'" >'.($RowIndex+1+($_SESSION['DisplayRecordsMax']*($_POST['PageOffset']-1))).'</a></td>
				echo'<td>'.($RowIndex+1+($_SESSION['DisplayRecordsMax']*($_POST['PageOffset']-1))).'</td> 
						<td>'.$bankDate.'</td>';
			if ( $CurrCode !=CURR ){
				if ($myrow['transno']>0){
					$sql="SELECT   exrate, amount, examount FROM currtrans WHERE period=".$myrow['period']." AND transno=".$myrow['transno'];
					$query=DB_query($sql);
					$row=DB_fetch_assoc($query);
					echo'<td class="number">'.locale_number_format($row['amount'],2).'</td>';
				}else{	
					echo'<td class="number">'.locale_number_format($myrow['amount'],2).'</td>';
				//	$amountcurr=round($myrow['amount'],2);
				}
			}
			echo'<td class="number">'.locale_number_format($myrow['debit'],2).'</td>
				<td class="number">'.locale_number_format($myrow['credit'],2).'</td>
				<td class="number">'.locale_number_format($blnarr[$RowIndex+($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']],2).'</td>';
			if ( $CurrCode!=CURR ){	
				echo'<td class="number">'.locale_number_format(($blnarr[$RowIndex+($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']])/$BankCuRate,2).'</td>';
			}
			echo'<td  title="账号:'.$myrow['toaccount'] .'" >'.$myrow['toname'].'</td>	';
			echo' <td >'.($jd==1?'借':'贷').'</td>';
			if ($TransNo>0){
				//已经有凭证 target="_blank"
				echo'<td ></td>';
			}else{
				echo'<td >';			
				if (isset($BankActData[trim($myrow['toaccount'])])){ 
					//echo '<br/>//转户交易'.
					
					echo $ToAct.':'.$ToActName;
					$ToBankTrans=GetBankTrans($myrow,$BankActData,$SelectBankAct,$CurrRateArr); 				
					if (is_array($ToBankTrans)){
						$chk="checked";
						//转户对对应科目计金额
						foreach ($ToBankTrans as $row){							
							echo'<input type="radio" name="toBank'.$RowIndex.'[]"  id="toBank'.$RowIndex.'[]" onchange="chilkradio(this ,'.$RowIndex.')" value="'.$row['bankdate'].'^'.$row['ToAmo'].'^'.$row['ToBankTransID'].'^'.$row['ToCurrCode'].' "  '.$chk.'  >
									<a href="#"  title="'.$row['bankdate'].':'.$row['ToAmo'].':'.$row['ToBankTransID'].'" >ID:'.$row['ToBankTransID'].':'.$row['ToCurrCode'].':'.$row['ToAmo'].'</a><br>';
									$chk=" ";
						}						
					}else{
						echo $ToBankTrans."没有找到应的银行交易<br>";
					         $ToActType=$ToActType*$ToBankTrans;
						
					
					}
							
									
				}else{
					//解析科目选项
					  $acttype=array(1,2,3,4,5,9);				
					if (in_array($ToActType,$acttype)&&$ToAct!=""){	
						//自动解析到科目					
						echo $ToAct.':'.$ToActName;
				
					}else{
						//手动选择  自动选择+手动选择					
					    //if (strpos($myrow['remark'],"初始")!==false){
							echo'<select name="ToSelect'.$RowIndex.'"   id="ToSelect'.$RowIndex.'"  size="1" style="width:120" OnChange="SelectAct(this, ToSelect'.$RowIndex.'.options,'.$RowIndex.')"  >';	
							//解析选择项
							foreach($SelectRule as $row){	
								if(round($myrow['credit'],POI)!=0){
									$SF=-1;//付款
								}else{
									$SF=1;//收款
								}
								$sflag=false;
								if (($SF==$row['jd'])||$row['jd']==0){
									if ($SF==-1){
										if (round($myrow['credit'],POI)<=round($row['maxamount'],POI)|| round($row['maxamount'],POI)==0 ){
											if ($ToActType!=3|| strlen($myrow['toname'])<=5){
												$sflag=true;
											}
										}

									}else{
										if (round($myrow['debit'],POI)<=round($row['maxamount'],POI)||round($row['maxamount'],2)==0 ){
											if ($ToActType!=3){
												$sflag=true;
											}
										}
									}
									if ($sflag ){
										if(isset($_POST['ToSelect'.$RowIndex]) AND $row['account'].'^'.$row['actname'].'^'.$row['acctype']==$_POST['ToSelect'.$RowIndex]){
											echo '<option selected="selected" value="';
										} else {
											echo '<option value="';
										}
										echo  $row['account'].'^'.$row['actname'].'^'.$row['acctype'].'">' .$row['account'].':'.$row['actname']  . '</option>';
										if (!isset($_POST['ToSelect'.$RowIndex])){
											$_POST['ToSelect'.$RowIndex]= $row['account'].'^'.$row['actname'].'^'.$row['acctype'];
										}
									}	
								}
							}//foreach 							
							echo'</select>';
							$toact=explode("^",$_POST['ToSelect'.$RowIndex]);
							if (isset($ForeignCurrAct[$toact[0]])){
								$ToCurr=$ForeignCurrAct[$toact[0]];
							}else{
								$ToCurr=CURR;
							}
							$toselectact=array("ToAct"=>$toact[0],"ToActName"=>$toact[1],"Type"=>$toact[2],"ToCurrCode"=>$ToCurr);			
							echo'<input type="hidden" name="ToSelectAct'.$RowIndex.'" id="ToSelectAct'.$RowIndex.'" value="' . urlencode(json_encode($toselectact,JSON_UNESCAPED_UNICODE)) . '" />';
					
					}		
				}
				echo '</td>';
				
			}
		
			if ($TransNo==0){
				echo'<input type="hidden" name="GetUrl'.$RowIndex.'" value="' . $GetUrl . '" />';
				
				if (isset($_POST['ToSelect'.$RowIndex])){

					echo '<td><a href="'.$UrlToTrans.'&ToActPrm='.urlencode(json_encode($toselectact,JSON_UNESCAPED_UNICODE)).'" id="href'.$RowIndex.'" name="href'.$RowIndex.'"  title="点击生成或查找已经制作的会计凭证！"  >生成</a></td>';
				
				}else{	
					echo '<td><a href="'.$UrlToTrans.'" id="href'.$RowIndex.'" name="href'.$RowIndex.'" title="点击生成或查找已经制作的会计凭证！"  >生成</a></td>';
				}
			}else{
				echo'<td ><a href="'.$UrlTransPDF.'"  title="'.htmlspecialchars($TransContent, ENT_QUOTES,'UTF-8', false)  .'" >'.$TransNo.'</a></td>';
			
			}
			echo'<td  >'.$myrow['remark'].$myrow['abstract']. '</td>
				<td >';
			if($myrow['transno']>0){
				echo'<input type="checkbox" name="chkbx[]"  id="chkbx[]" value="'.$RowIndex .'" disabled="disabled" ></td>	';
			}else{
				$checked="checked";
				if ($ToActType==-9 || $ToActType==-1 || $ToActType==0){
					$checked='disabled="disabled"';
				}
			
				echo'<input type="checkbox" name="chkbx[]" id="chkbx[]" value="'.$RowIndex .'"   '.$checked.' ></td>	';
			
			}	 
			echo'</tr>';
				$RowIndex++;
		}	//end of while loop
		echo '</table>';	
	}//292-Search
	//凭证生成
	if (isset($_POST['TransSave'])){	
		
		if (!isset($CodeRule)){
			$SQL="SELECT  confname,confvalue 
					FROM myconfig 
					WHERE conftype=1
					 OR  confname  IN ('SetAccountName','AccountSize','AccountNameSize','AutoSubject')";
			
			$Result = DB_query($SQL);
			while ($row = DB_fetch_array($Result)) {		
					$CodeRule[$row['confname']]=explode(',',$row['confvalue']);	
			}
		}
		//var_dump($SubjectRule);
	    $rw=0;		
	
		if (count($_POST['chkbx'])>0){
					
				/**array(13) { ["BankTransID"]=> string(3) "596" ["BankDate"]=> string(19) "2019-12-03 10:13:28"
				 *  ["ToAccountt"]=> string(18)
				 *  "26-606001040002335" ["ToName"]=> string(39) "汉中正元兴机床  件有限公司"  "CurrCode"=>$myrow['currcode'],["Amount"]=> NULL ["ExAmount"]=> string(1) "0" 
				 * ["flg"]=> string(1) "1" ["Remark"]=> string(6) "货款" ["Abstract"]=> string(18) "网银互联  款"
				 *  ["ToAct"]=> string(8)  "22021354" ["ToActName"]=> string(52) "应付账款-汉中正元兴机床配件有限公司" 
				 * ["RegID"]=> NULL 
				 * ["ToActType"]=> array(3) { ["ToActType"]=> int(2) ["TypeCust"]=> int(3) ["ToActType"]=> int(2) } } 
				 * 	//"ToActType"=>-1 0 1 2 4,"TypeCust"=>1客户  2供应商 4 个人 0费用标记,"ToActType"=>0转户  个人1  单位2*/
			foreach($_POST['chkbx'] as $row){
				//	echo urldecode($_POST['GetUrl'.$row])."<br>";		
				$TransRow=json_decode(urldecode($_POST['GetUrl'.$row]),true);//JSON_UNESCAPED_UNICODE);				
			
				$ToAct=$TransRow['ToAct'];
				$ToActName=$TransRow['ToActName'];
				$ToCurrCode=$TransRow['ToCurrCode'];
						//手动选择科目
				if ($ToAct==''){				
					$ToSelectAct=json_decode(str_replace('\\','',urldecode($_POST['ToSelectAct'.$row])),true);
					/**array(3) { ["ToAct"]=> string(7) "6601101" ["ToActName"]=> string(19) "销售费用-  他" ["Type"]=> string(1) "1" } 0 为总   目录	 */
					//var_dump(	$ToSelectAct);	
					if($ToSelectAct['Type']==0){
						//生成新科目--------------------------------------------------

					}else{		
						$ToAct=$ToSelectAct['ToAct'];
						$ToActName=$ToSelectAct['ToActName'];
						$ToCurrCode=$ToSelectAct['ToCurrCode'];
					}
					
				}
				//var_dump($ToSelectAct);
				//prnMsg($ToSelectAct['ToCurrCode'].'-'.$row);
				if (isset($ForeignCurrAct[$ToAct])){
					$ToCurrCode=$ForeignCurrAct[$ToAct];
				}else{
					$ToCurrCode=CURR;
				}
				
				//转户对应科目	
				if (isset($BankActData[trim($ROW['ToAccount'])])){   
			       //prnMsg( $_POST['toBank'.$row][0] );
					//string(33) "2019-08-09 12:00:00^-3800.00^640 "
					//foreach( $_POST['toBank'.$row] as $val){              
					//break;
				}
				/*
				if (isset($ToSelectAct)){ 
					if ($ToSelectAct['Type']==0){
						//var_dump($TransRow);
						$CustData=array("customer"=> $TransRow['ToName'], "registerno"=> '',"tag"=>$tag,  "bankaccount"=> $TransRow['ToAccount'],"bank"=> $TransRow['ToBank'],  "custype"=>0,  "regid"=> $TransRow['RegID']);
						//生成科目
						$actcreate=CustomerAccountCreate($CustData,$ToSelectAct['ToAct'],$CodeRule);
						//prnMsg($actcreate);
						$ToSelectAct=array("ToAct"=>$actcreate,"ToActName"=> "销售费用-其它","Type"=> "1" ,"ToCurrCode"=>$ToCurrCode );
					}
				}*/
				/*
			     if($TransRow['ToAct']==""){
					echo    $TransRow['ToCurrCode'].'-'.$ToSelectAct['ToAct']."<br>";
				 }else{*/
				   //GLCreateTrans($TransRow,$ToSelectAct,$_POST['toBank'.$row][0],$_SESSION['SelectBank'],$_SESSION['tagref'][$tag][1]);
			
				   GLCreateTrans($TransRow,$ToSelectAct,$_POST['toBank'.$row][0],$_SESSION['SelectBank']);//_SESSION['tagref'][$tag][1]);
			
				//prnMsg(	$TransRow['ToName']);
				/*						
			
				   //凭证已经手工制作
				    $tpid=explode('^',$_POST['BankStr'.$row]); 
					$result=DB_Txn_Begin(); 
					$sql="UPDATE banktransaction SET transno=".explode('^',$_POST['JournalType'.$row])[1].",period=".explode('^',$_POST['JournalType'.$row])[0]."  WHERE banktransid=".explode('^',$_POST['JournalType'.$row])[2];
					$result = DB_query($sql);
					$sql="UPDATE gltrans SET posted=1 WHERE periodno=".explode('^',$_POST['JournalType'.$row])[0]." AND transno=".explode('^',$_POST['JournalType'.$row])[1];
					$result = DB_query($sql);
					$result=DB_Txn_Commit();	*/
					
					
				
				$rw++;
			
			}  
			prnMsg($rw.'凭证生成！','info');
			echo '<meta http-equiv="refresh" content="31"/>';
			//echo '<meta http-equiv="Refresh" content="8; url=' . $RootPath . '/CashJournallize.php">';		
	    }else{
			prnMsg('你没有选择！','info');
		}
	}
}//if-229

if (isset($_POST['Demo'])){
	var_dump($_POST['ToSelectAct']);
}
echo'<script type="text/javascript">
    var sles = document.getElementById("chkbx"); 
console.log(sles.lengh);
for(var i = 0,l=sles.length;i<l;i++){
	(function(i){
			sles[i].onchange = function(){
			alert(i);
			alert(this)
			};
		})(i);
}

</script>';
echo '</div>
      </form>';
include('includes/footer.php');

/**
   *   据银行记录自动生成会计凭证;		
   *
   * @param array  $TransRow 
   * 	GetUrl="BankTransID"=>$myrow['banktransid'],"BankDate"=>$myrow['bankdate'],"ToAccount"=>$myrow['toaccount'],"ToName"=>$myrow   ['toname'], "CurrCode"=>$myrow['currcode'],"Amount"=>$myrow['amount'],
   *             "ExAmount"=>$myrow['examount'],"flg"=>$myrow['flg'],"Remark"=>$myrow['remark'],"Abstract"=>$myrow['abstract'], 
   *             "ToAct"=>$ToAct, "ToActName"=>$ToActName,"ToCurrCode"=>"decimalplaces"=> "RegID"=>$regid,"ToActType"=>$ToActType,"ToBank"=>$ToBank),
   *               $ToSelectAct=array("ToAct"=>$actcreate,"ToActName"=> "销售费用-其他","Type"=> "1"  );
   *    *          $ToBank=$row['bankdate'].'^'.$row['ToAmo'].'^'.$row['ToBankTransID'].'^'.$row['ToCurrCode'].' 
   *               $selectbank  array(3) { [0]=> array(4) { [0]=> string(6) "100202" [1]=> string(3) "CNY" [2]=> string(1) "1" [3]=> string(12) "229919112111" } [1]=> string(2) "27" [2]=> string(10) "2020-03-31" }
   * @return array
   * @throws Exception
   * 错误返回-1
   */
function GLCreateTrans($ROW,$toselectact,$tobank,$selectbank){
    //var_dump($toselectact);//$tobank);//$ROW);
	
	$CurrToType=-1;
	$ToActType=$ROW['ToActType'];//0转户  个人1  单位2
	$_ToName=$ROW['ToName'];	
	$prd=$selectbank[1];
	$ActCode=$selectbank[0][0];
	$tag=$selectbank[0][2];
	$ToAct=$ROW['ToAct'];
	$CurrCode=$selectbank[0][1];
	$ToCurrCode=$ROW["ToCurrCode"];
	//return $ROW['Amount'].'='.$ROW['ExAmount'];//$ToActType;//$_ToName.$ActCode.$ToAct.$selectbank[0][3];
	if (!empty($toselectact)){
		$ToAct=$toselectact["ToAct"];
	}
	if (!empty($tobank)){
		$ToBankVal=explode("^",$tobank);//$row['bankdate'].'^'.$row['ToAmo'].'^'.$row['ToBankTransID'].'^'.$row['ToCurrCode'].' 
	}	
	//得到  证类及编码
	$sql="SELECT typeid,toto FROM transtype WHERE account='".substr($ToAct,0,4)."'";
	$result=DB_query($sql);
	while($row=DB_fetch_array($result)){
		if ($row['toto']==0){
			$typetrans=$row['typeid'];
		}else{
			if ($row['toto']==1&& $ROW['Amount']>0){
				$typetrans=$row['typeid'];
			     break;
			}else{
				$typetrans=$row['typeid'];
				break;
			}
		}
	}
	if (empty($typetrans)){
		if ($ROW['Amount']>0){
			$typetrans=-12;			
		}else{
			$typetrans=-22;		
		}
	}
	//$TypeNo = GetTypeNo($typetrans,$prd, $db);	

	$TypeNo = GetTagTypeNo($_SESSION['tagref'][ $tag][1],$prd, $db);
	$transno = GetTransNo($prd, $db);	
	$narrative='';
	$rate=1; 
	$tag=1;
	$post=1;
	$TranDate=FormatDateForSQL($ROW['BankDate']);
	//echo ($ToAct.'='.$ToCurrCode.'-'.$CurrCode.'[ToActtype'.$ToActType)."<br/>";
	//return;
	DB_Txn_Begin();
	//以下未处理交易数据
	if ($ToActType==9){
		//prnMsg('//转户1052');
		if (count($ToBankVal)<3){
			//该参数没有说���会不是转户
			$post=0;
		}
		if ($selectbank[0][1]==CURR){//本币账户
				
				if (trim($ToCurrCode)==CURR  ){
					//echo $ToCurrCode.'//本币->本币<br/>';
					$CurrToType=0;
					$Amount=round($ROW['Amount'],POI);
					$narrative="内部转户";
					if ($post==1){
						$SQL="UPDATE banktransaction SET  type=".$typetrans." ,transno ='".$transno."' ,period='".$prd."'  WHERE transno=0 AND banktransid='".$ToBankVal[2]."'";
						$result=DB_query($SQL);	

					}
				}elseif (trim($ToCurrCode)!=CURR ){
					//echo  $ToCurrCode.'//1133-本币  对外币;<br/>';
					$CurrToType=1;
					$Amount=$ROW['Amount'];//本币金额
					$CurrAmount=round($ToBankVal[1],POI);//外币金额
					$differences=0;//汇率差异，根据系统汇率和实际  换计算				
					$narrative="外币转户[".trim($ToCurrCode).$CurrAmount."]";
					//$rate=round(abs($CurrAmount/$Amount),4);
					if ($post==1){
						$SQL="UPDATE banktransaction SET  type=".$typetrans." ,transno ='".$transno."' ,period='".$prd."'  WHERE transno=0 AND banktransid='".$ToBankVal[2]."'";
						$result=DB_query($SQL);	

					}
					//prnMsg('//本币->外币'.$CurrAmount.'='.$Amount);
				}
				//prnMsg($Amount.'-'.$CurrAmount.'[00000]{'.$ToCurrCode.'=='.CURR .'}='.$narrative.'['.$ROW['ToAct'].']'.$prd);	
		}else{
				//外币账户转  
				if (trim($ToCurrCode)==CURR){

					$CurrToType=-3;
					echo $ToBankVal[1].'//外币>本币   外币  另种外币;不支持';
					$Amount=$ToBankVal[1];
					$CurrAmount=round($ROW['Amount'],POI);  //外币金额
					$narrative="外币转户[".trim($selectbank[0][1]) .$CurrAmount."]";
					if ($post==1){
						$SQL="UPDATE banktransaction SET  type=".$typetrans." ,transno ='".$transno."' ,period='".$prd."'  WHERE transno=0 AND banktransid='".$ToBankVal[2]."'";
						$result=DB_query($SQL);	

					}
				

				}elseif (trim($ToCurrCode)==$selectbank[0][1] ){

					$CurrToType=3;//外币对外币-同种;
					//需要使用固定汇率计算
					$Amount=round($ROW['Amount']/$selectbank[0][4],POI);//兑换本币金额  需     据  统汇率计   			
					$CurrAmount=round($ROW['Amount'],POI);  //外币金额
					$differences=0;//汇率差异，根据系统汇率和实际兑换计算
					$narrative="外币转户[".trim($ToCurrCode) .$CurrAmount."]";
					if ($post==1){
						$SQL="UPDATE banktransaction SET  type=".$typetrans." ,transno ='".$transno."' ,period='".$prd."'  WHERE transno=0 AND banktransid='".$ToBankVal[2]."'";
						$result=DB_query($SQL);	

					}
				}
				//prnMsg( $post.'-'.$SQL);
		}
			
	}else{
		//银行账户交易
		if ($selectbank[0][1]==CURR){//本币账户
	
			//以下为收货��� 外币  本币
			if (CURR==$ToCurrCode){			
				//本币
				$CurrToType=0;
				$Amount=round($ROW['Amount'],2);
				if ($ROW['ToAccount']!='')	{		
					if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){
					
						$narrative="收";				
					}else{			
						$narrative="付";				
					}	
				}
				//prnMsg($Amount.'-'.$CurrAmount.'[本币]'.$narrative.'['.$ROW['ToAct'].']'.$prd);
			}elseif(CURR!=$ToCurrCode){
				$CurrToType=1;
				//if ( $ToCurrCode !=CURR ){
				//收付外币
				$CurrAmount=round((float)$ROW['Amount'],2);//外币原币
				$Amount=round($ROW['ExAmount'],2);  //按标准折算本币
				if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){		
				
					$narrative="收[".$ToCurrCode.$CurrAmount."]";
								
				}else{		
					$narrative="付[".$ToCurrCode.$CurrAmount."]";
								
				}	
				//$rate=round(abs($CurrAmount/$Amount),4);		
			
			}
		}else{
			//外币户
			if (trim($ToCurrCode)!=$selectbank[0][1] ){
				$CurrToType=2;//外  对本币;
				
				$Amount=round($ROW['ExAmount'],POI);//兑换本币金额			
				$CurrAmount=round($ROW['Amount'],$ROW['decimalplaces']);  //外币金  
				if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){		
				
					$narrative="收外币[".$ToCurrCode.$Amount."]";
								
				}else{		
					$narrative="付外币[".$ToCurrCode.$Amount."]";
								
				}	
				//$narrative="外币[".trim($ToCurrCode) .$Amount."]";
				$rate=round(abs($CurrAmount/$Amount),4);//外币-本币
			}elseif (trim($ToCurrCode)==$selectbank[0][1] ){
				$CurrToType=3;//外币对外币-同种;
				//收付外币
				$CurrAmount=round((float)$ROW['Amount'],2);//外币原币
				$Amount=round($ROW['ExAmount'],2);  //按标准折算本币
				if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){		
				
					$narrative="收[".$ToCurrCode.$CurrAmount."]";
								
				}else{		
					$narrative="付[".$ToCurrCode.$CurrAmount."]";
								
				}	
				
			}
		
		}
		if ($ROW["Remark"]!=''){
			$narrative.=' '.$ROW['Remark'];
		}else{

			$narrative.='  ' .$ROW["Abstract"];		
		}
		//if ($ROW['ToName']!='')
			//$narrative.=' '.$ROW['ToName'];	
			
	}
	$inst=0	;
	$ErrMsg = _('Cannot insert a  transaction because');
	$DbgMsg = _('Cannot insert a  transaction with the SQL');
	 //以下插入外币currtrans
	// echo $ToCurrCode;
	if ($selectbank[0][1]==CURR){
		//银行本币
		if ( $selectbank[0][1] !=$ToCurrCode ){
			//银行本币>>>外币
			$SQL = "INSERT INTO currtrans(transdate,
										transno,
										period,
										account,
										exrate,
										amount,
										examount,
										currtype,
										currcode,
										flg)
							VALUES ('" .$TranDate . "',
									'" . $transno . "',
									'" . $prd . "',
									'" . $ToAct . "',
									'" . abs($CurrAmount/$Amount) . "',									
									'" . (-$Amount) . "',
									'" . (-$CurrAmount) . "',
									'0',
									'" . $ToCurrCode . "',
									'" . $ROW['flg']."' )";
			//prnMsg($SQL.'' .$ToCurrCode);
			   $result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
											
		}
			 		 	
	}else{
	   
		//银行外币
		
		if (CURR==$ToCurrCode){
				//银行外币>对应人民币
			$SQL = "INSERT INTO currtrans(transdate,
											transno,
											period,
											account,
											exrate,
											amount,
											examount,
											currtype,
											currcode,
											flg)
								VALUES ('" .$TranDate . "',
										'" . $transno . "',
										'" . $prd . "',
										'" .$ActCode . "',
										'" . abs($CurrAmount/$Amount) . "',									
										'" . $Amount . "',
										'" . $CurrAmount . "',
										'0',
										'" . $ToCurrCode . "',
										'" . $ROW['flg']."' )";
				
			$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		}elseif ($selectbank[0][1]==$ToCurrCode){
			//外币同币
			$SQL = "INSERT INTO currtrans(transdate,
											transno,
											period,
											account,
											exrate,
											amount,
											examount,
											currtype,
											currcode,
											flg)
								VALUES ('" .$TranDate . "',
										'" . $transno . "',
										'" . $prd . "',
										'" . $ActCode . "',
										'" . abs($CurrAmount/$Amount) . "',									
										'" . $Amount . "',
										'" . $CurrAmount . "',
										'0',
										'" . $ToCurrCode . "',
										'" . $ROW['flg']."' )";
				
			$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			$SQL = "INSERT INTO currtrans(transdate,
											transno,
											period,
											account,
											exrate,
											amount,
											examount,
											currtype,
											currcode,
											flg)
								VALUES ('" .$TranDate . "',
										'" . $transno . "',
										'" . $prd . "',
										'" . $ToAct . "',
										'" . abs($CurrAmount/$Amount) . "',									
										'" . $Amount . "',
										'" . $CurrAmount . "',
										'0',
										'" . $ToCurrCode . "',
										'" . $ROW['flg']."' )";
				
			$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		}
			//prnMsg($SQL);
	}

	$SQL="UPDATE banktransaction
	       SET  type=".$typetrans." ,transno ='".$transno."' ,period='".$prd."'  
	      WHERE banktransid='".$ROW['BankTransID']."'";
	$result=DB_query($SQL);	

	
			//下面代��为应收账款、应付收款录入
		if  (substr($ToAct,0,4)=='1122'){ 
			
				$sql="INSERT INTO debtortrans( transno,
												type,
												debtorno,
												branchcode,
												trandate,
												inputdate,
												prd,
												settled,
												reference,
												tpe,
												order_,
												rate,
												ovamount,
												ovgst,
												ovfreight,
												ovdiscount,
												diffonexch,
												alloc,
												invtext,
												shipvia,
												edisent,
												consignment,
												packages,
												salesperson	) 
												VALUE('" . $transno . "',
													'2',
													'" . $ROW['BankTransID'] . "',
													'1',
													'" .$TranDate . "',
													'" .$TranDate . "',
													'" . $prd . "',
													'0', '0', '0',  '0', 
													'" . $rate  . "',
													'" . $Amount . "',
													'0',  '0',  '0',  '0',  '0',  '','0','0','0','1',''	) ";
			$result = DB_query($sql);
			if ($result){
				$post=1;
			}
			
		}elseif  (substr($ToAct,0,4)=='2202'){ 
				
				$sql="INSERT INTO supptrans(	transno,
												type,
												prd,
												supplierno,
												suppreference,
												trandate,
												inputdate,
												settled,
												rate,
												ovamount,
												ovgst,
												diffonexch,
												alloc,
												transtext	)
										VALUE('" . $transno . "',
												'1',
												'" . $prd . "',
												'" . $ROW['BankTransID'] . "',
												'1',
												'" .$TranDate . "',
												'" .$TranDate . "',
												'0', 
												'" . $rate . "',
												'" . $Amount . "',
												'0',  '0',  '0', '') ";
					$result = DB_query($sql);
					if ($result){
						$post=1;
					}
			
				
		}
		//9 转户，1客户  ，2供应商 3 无帐号摘要解析,4个人  要解析	
		$nrt1="";
		$nrt2="";
		if ($ToActType==9){
			if ($Amount>0){
				$nrt1="收账号:".substr($ROW['ToAccount'],-6);
		
				$nrt2="付账号:".substr( $selectbank[0][3],-6);
			}else{
				$nrt2="收账号:".substr($ROW['ToAccount'],-6);
		
				$nrt1="付账号:".substr( $selectbank[0][3],-6);
			}
		}else{
			//if ($ToActType==1 ||$ToActType==2 ||$ToActType==4){
			if (!empty($_ToName)){
				if ($Amount>0){
					$nrt1="收于:".$_ToName;			
					$nrt2="存账号:". substr($selectbank[0][3],-6);
				}else{
					$nrt1="付给:".$_ToName;
					$nrt2="付给账号:".substr($ROW['ToAccount'],-6).' '.$_ToName;					
				}
			}
		}
		//return $nrt1.'-'.$nrt2.$_ToName.'['.$narrative;
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
									chequeno,
									posted,
									userid)
								VALUES ('".$typetrans."',
								'" . $TypeNo . "',
								'" . $transno . "',
								'" . $TranDate . "',
								'" . $prd . "',
								'" . $ActCode . "',
								'" .$narrative." ".$nrt1."', 
								'" . $Amount ."',
								'" . $ROW['flg'] . "',
								'".$tag."',
								'1',
								'1',
								'auto')";		

		$result = DB_query($SQL);
		 $n1=$SQL;
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
									chequeno,
									posted,
									userid)
								VALUES ('".$typetrans."',
								'" . $TypeNo . "',
								'" . $transno . "',
								'" . $TranDate. "',
								'" . $prd . "',
								'" . $ToAct . "',
								'" .$narrative.' '.$nrt2."', 
								'" .(-$Amount)."',
								'" . $ROW['flg'] . "',
								'".$tag."',
								'1',
								'".$post."',
								'auto')";	
			$result = DB_query($SQL);	
			//return $n1.'-'.$SQL;
		if ($result){
		$inst++;
		}	
	
	
		if ($result ){
			DB_Txn_Commit();
		}else{
			
			DB_Txn_Rollback();		
		}	

	
}
/**
   *解析会计科目
   * @param array $ROW=>(banktransid, account,type,transno,	period,	bankdate,	amount ,examount,    debit,	credit,	t.currcode,
   *				t.toaccount,	toname,	tobank,	t.remark,	t.abstract,	reliability,	flag,	t.flg)
   *             $ActRule=>,
   *            	$BankActData[trim($myrow['bankaccountnumber'])]=array("accountcode"=>$myrow['accountcode'],"currcode"=>$myrow['currcode'],"bankaccountname"=>$myrow['bankaccountname'],"tag"=>$myrow['tag']);
   *  	$ CustomeAct[$key]=('account'=>$subject,
   *                           "actname"=>$actname,
   *                           'regid'=>$regid,
   *                           'TypeCust'=>$custype,
   *                            "toname"=>$row['toname'],
   *                           "tobank"=>$row['tobank'],);
   *          
   * @return array "account"=>$ToAct,
	*              "actname"=>$ToActName,
	*			   "regid"=>$RegID,
	*			   "ToActType"=>-1 解析不到科目9 转户，1客户  ，2供应商 3   帐   摘要解析,4个人摘要解析/5解析不成功 6客户解析不成功，7供应商解析不成功 
    *
   * @throws Exception
   * 错返回-3  该笔款未有账号,不能  置,异常,请通知  统管理员!
   */
 function GetCustomeAct($ROW,$_CustomeAct,$ActRule,$bankactdata){

	$_ToActType=-1;//-1解析不到科目

	$_ToAct='';
	$_ToActName='';
	$RegID=0;
	if ($ROW['toaccount']=='' ||strlen($ROW['toaccount'])<5){//1-账号为空，以摘要解析科   
	
		foreach ($ActRule as $key=>$glrow){	
			//prnMsg($ROW['abstract'].$ROW['remark'],$glrow['remark']);					
			if (strpos($ROW['abstract'].$ROW['remark'],$glrow['remark'])!==false){
				$_ToAct=$glrow['account'];
				$_ToActName=$glrow['actname'];
				$_ToActType=3; //	3无账号摘要解析	成功
				break;
			}
		}//foreach
	}elseif (isset($_CustomeAct[$ROW['toaccount']])){//&&strlen($ROW['toaccount'])>5){ 
		//return $ROW['toaccount'];
		if ($_CustomeAct[$ROW['toaccount']]['flag']==-1){
			return -1;  //账号资料异常，已经计入erplogs
		}
		//有账号没有设定科目//[个人按摘要计入工资]	
		if((strlen(trim($ROW['toname'])))<=15 && $ROW['toname']!=''){
			$_ToActType=-1;  //如果是个人名 摘要优先判断			
			foreach ($ActRule as $key=>$val){			
				if ($val["srtype"]==-3){//工资类别
					//prnMsg($ROW['abstract'].$ROW['remark'],$val['remark']);	
					if (strpos($ROW['abstract'].$ROW['remark'],trim($val['remark']))!==false){
						$_ToAct= $val['account'];
						$_ToActName=$val['actname'];
						$_ToActType=1; //个人摘要解析标记	
						break;
					}										
				}							
			}//end foreach			
		}
		if ($_ToAct==''){
			//有账号，转户以外的交易
			$TypeCust=$_CustomeAct[$ROW['toaccount']]['TypeCust'];//得到客户类型1,2,3,0
			$RegID=$_CustomeAct[$ROW['toaccount']]['regid'];
			$_ToAct=$_CustomeAct[$ROW['toaccount']]['account'];					
			$_ToActName=$_CustomeAct[$ROW['toaccount']]['actname'];
			if ($_ToAct!='' ){
				if (substr($_ToAct,0,4)==YSZK ||substr($_ToAct,0,4)==YFZK)
				$_ToActType=substr($_ToAct,0,1); //1客户 2供应  
				else
				$_ToActType=5; //个人位挂账
				
			}else{
				return $TypeCust;//得到客户类型1,2,3,0
			}
		}
	}elseif (isset($bankactdata[trim($ROW['toaccount'])])){
		//自己的银行账户		
		//$CurrToType 银行转户标记  1-同币2-转   币种
		//$_ToActType   0转户  2 单位 3无账号费用 1个   摘    		
		$bankdt=date($_SESSION['DefaultDateFormat'],strtotime ("+3 day", strtotime($ROW['bankdate'])));
		$bankdt1=date($_SESSION['DefaultDateFormat'],strtotime ($ROW['bankdate']));	
		$_ToAct=$bankactdata[$ROW['toaccount']]['accountcode'];					
		$_ToActName=$bankactdata[$ROW['toaccount']]['bankaccountname'];
		$_ToActType=9;		//转户标记	9
		
	}
	//prnMsg($_ToAct.'='.$_ToActName);
	if ($_ToAct==''){
		$_ToActType=-1;
	}else{
		$rowact=array("account"=>$_ToAct,"actname"=>$_ToActName,"regid"=>$RegID,"ToActType"=>$_ToActType); 
	}
		//"ToActType"=>-1 解析     科目9 转户，1客户  ，2供应商 3 无   号  要解  ,4个人摘要  析/5解析不成功 6客户解析不成功，7供应商解析不成功 

	//未有科目异常---->原因未有账号

	if (is_array($rowact)){
		return  $rowact;
	}else{
		$_ToActName='该笔款未有账号,不能设,   常,请通知系统管理员!';				
		return -3;
	}	
}
/**
   *银行转户标记  0-同币 1  本币-外币  2外币-本币 3外币  币 -1外币对外币-不同种，不支持;		
   *   到对应记录
   * @param array $ROW
   * 			  $BankActData[trim($myrow['bankaccountnumber'])]=array("accountcode"=>$myrow['accountcode'],"currcode"=>$myrow['currcode'],"bankaccountname"=>$myrow['bankaccountname'],"tag"=>$myrow['tag']);
   * 选择银行     号、科目    $selectbank   * $row['accountcode']，$row['currcode']，$row['tag']，账号.
   *外币汇率       $CurrRateArr[trim($row['currabrev'])]=$row['rate'];	
 
   * @return array
   * 
   *    $ToBankTrans[]=array("banktransid"=>$Row['banktransid'],"bankdate"=>$Row['bankdate'],"toaccount"=>$selectbank[3],"amount"=>$Row['amount'],"currcode"=>$Row['currcode'],"CurrToType"=>$CurrToType);	
   * @throws Exception
   * 错误返回-1
   */
function  GetBankTrans($ROW,$BankActData,$selectbank,$CurrRateArr){
		//$CurrToType 银行转户标记  0-  币 1  本币-外币  2外   -本币 3外   外币 -1外币对外币-不同种，不支持;	

		$endbankdt=date("Y-m-d",strtotime ("+1 day", strtotime($ROW['bankdate'])));
		$bankdt=date("Y-m-d h:i:s",strtotime($ROW['bankdate']));
		$str='';
	
	$ToAccount=$BankActData[$ROW['toaccount']]['accountcode'];
	$ToCurrCode=$BankActData[$ROW['toaccount']]['currcode'];
		$tag=1;

	if ($selectbank[1]==CURR){  //所选择银行本币
		
		if ($ROW['currcode']==$ToCurrCode  ){
			$CurrToType=0;//本币
			//if ($ROW['amount']>0 && $ROW['flg']==1)||($ROW['amount']<0 && $ROW['flg']==)-1){
			$SQL="SELECT banktransid, bankdate, amount,currcode, flag, flg FROM banktransaction 
			       WHERE account ='".$ToAccount."'  
						 AND amount=".(-$ROW['amount'])." 
						 AND toaccount='".$selectbank[3]."'
						 AND bankdate>='".$bankdt."' AND bankdate<'".$endbankdt."' 
						 AND transno=0 
						 ORDER BY banktransid,bankdate";
	    
		}elseif ($ROW['currcode']!=$ToCurrCode){
			$CurrToType=1;//本币对外币;
			$CurrRate=$CurrRateArr[$ToCurrCode][0];
			$Amount=round($ROW['amount']*$CurrRate,2);
			$SQL="SELECT banktransid, bankdate, amount,currcode, flag, flg FROM banktransaction 
			       WHERE  account='".$ToAccount."'  
					  AND amount<".(-$Amount*.85)." AND amount>".(-$Amount*1.15)." 
					  AND toaccount='".$selectbank[3]."'
					  AND bankdate>='".$bankdt."' AND bankdate<'".$endbankdt."' 
					  AND transno=0 
					  ORDER BY banktransid,bankdate";

		}	
	}else{//所选择   行外币
		
		if ($ROW['currcode']!=$ToCurrCode ){//外币对本币;
			$CurrToType=2;
			$CurrRate=$CurrRateArr[$ROW['currcode']][0];
			$Amount=round($ROW['amount']/$CurrRate,2);
			if (round(-$Amount,2)>0){
				$tab1=">";
				$tab2="<";

			}else{
				$tab1="<";
				$tab2=">";
			}
			$SQL="SELECT banktransid, bankdate, amount,currcode, flag, flg FROM banktransaction 
			WHERE  account='".$ToAccount."'  
			   AND amount".$tab1." ".(-$Amount*.85)." AND amount".$tab2." ".(-$Amount*1.15)." 
			   AND toaccount='".$selectbank[3]."'
			   AND bankdate>='".$bankdt."' AND bankdate<'".$endbankdt."' 
			   AND transno=0 
			   ORDER BY banktransid,bankdate";
		}else	if ($ROW['currcode']==$ToCurrCode){//外币对外币-同种;
			$CurrToType=3;
			$SQL="SELECT banktransid, bankdate, amount,currcode, flag, flg FROM banktransaction 
			WHERE account ='".$ToAccount."'
				  AND amount=".(-$ROW['amount'])." 
				  AND toaccount='".$selectbank[3]."'
				  AND bankdate>='".$bankdt."' AND bankdate<'".$endbankdt."' 
				  AND transno=0 
				  ORDER BY banktransid,bankdate";
		}elseif ($ROW['currcode']!=$ToCurrCode ){//外币对外币-不同种，不支持
			$CurrToType=-3;
			return -3;
		}		
		
	}  

	//上面  递SQL
	$Result=DB_query($SQL);
   //  找对应的记录
	if (DB_num_rows($Result)>0){
	
		while ($Row=DB_fetch_array($Result)){
			//查找到的账户  录
					
			//if (count($to_trans[$ROW['toaccount']])==1){
				
				$ToBankTrans[]=array("ToBankTransID"=>$Row['banktransid'],"bankdate"=>$Row['bankdate'],"ToAct"=>$ToAccount,"ToAmo"=>$Row['amount'],"ToCurrCode"=>$ToCurrCode,"CurrToType"=>$CurrToType);														
			/*}else{
				for($i=0;$i<count($to_trans[$ROW['toaccount']]);$i++ ){
					if ($to_trans[$ROW['toaccount']][$i][0]==-$Row['amount']&& $to_trans[$ROW['toaccount']][$i][3]==0){
						//?外币条件，没有写
					
						$ToBankTrans[]=array("banktransid"=>$Row['banktransid'],"bankdate"=>$Row['bankdate'],"toaccount"=>$selectbank[3],"amount"=>$Row['amount'],"currcode"=>$Row['currcode'],"CurrToType"=>$CurrToType);														
						break;
					}
				}
	
			}*/
		}
		if (is_array($ToBankTrans)){
			return $ToBankTrans;
		}else{
			return -2;
		}
	}else{
		return -1;
	}

}
/*
   *添加新单位 和账号  ，已经有用户名添加账号
   * @param array $ROW 对账单记录
   *       	$CustomData[$row['toaccount']]=array('account'=>'',
   *                                   'actname'=>'',
   *                                    'regid'=>0,
   *                                    "toname"=>$row['toname'],
   *                                     "tobank"=>$row['tobank'],
   *                                     "flag"=>0,
   *                                    'TypeCust'=>0);
   *    *
   * @return tinyint
   * @throws Exception
   * 错误   回-1
   */
  function UpdateCustomer($banknew,$selectbank){//},$typecust){
	//$banknew  1toname 2 tag 3 tobank	
        $regid=-1;
	  if ($$banknew['flag']==2){//新单位插   账号  名
		  $result = DB_Txn_Begin();
		  $sql="INSERT IGNORE INTO registername (custname,
											  tag,
											  account,
											  flg,
											  regdate,
											  custtype) 
										  VALUE('". match_chinese($banknew['toname'])."',
												  '".$selectbank[2]."' ,
												  '',
												  '0',
												  '".date("Y-m-d h:i:s")."' ,
												  '".$banknew['TypeCust']."'
													  ) ";
		   $result=DB_query($sql);
		   // prnMsg($sql);
  
		  if(DB_affected_rows($result)>0){//插入成功
			  
			  $regid=DB_Last_Insert_ID($db,'registername','regid');
			  $sql="INSERT IGNORE INTO accountsubject(bankaccount,
												  tag,
												  regid,
												  subject,
												  acctype,
												  bankname,
												  bankcode,
												  flg
											  )
											  VALUE('".match_number($banknew['banknumber'],2)."',
													  '".$selectbank[2]."' ,
													  '".$regid."',
													  '',
													  '".$banknew['TypeCust']."',
													  '".$banknew['tobank']."',
													  '',
													  '0' 	) ";
										  
			  $result=DB_query($sql);
			  if ($result){
				 
				  $result = DB_Txn_Commit();
				  return $regid;
			  }
		  }
	  }elseif($$banknew['flag']==1){
		  //已   存在名称,插入账号
	  
		  $sql="SELECT regid, custname, account FROM registername
			  WHERE custname='".$banknew['toname']."' ";//AND tag='".$selectbank[2]."'";
		  $result=DB_query($sql);
		  $row=DB_fetch_assoc($result);
		  if (!empty($row)){
			  $sql="INSERT IGNORE INTO accountsubject(bankaccount,
													  tag,
													  regid,
													  subject,
													  acctype,
													  bankname,
													  bankcode,
													  flg	)
											  VALUE('".match_number($banknew['banknumber'],2)."',
													  '".$selectbank[2]."' ,
													  '".$row['regid']."',
													  '".$row['account']."',
													  '".$banknew['TypeCust']."',
													  '".$banknew['tobank']."',
													  '',
													  '0') ";
			  //prnMsg($sql);
			  $result=DB_query($sql);
			  //return $sql;
			  if ($result){
				 $regid=$row['regid'];
			  }
		  }
	  }//endif
      
	  return $regid;
}
/**
   *读取会计凭证，转换为  符串
   * @param array $period
   *              $transno
   *    *    *
   * @return String
   * @throws Exception
   * 错误返       -1
   */
function  GetTransContent($period,$transno,$SelectBank){

	$SQL="SELECT transno, 
				 trandate,
				 account,
				 accountname,
				 narrative,
				 amount,
				 flg,
				 posted
			FROM gltrans
			LEFT JOIN chartmaster ON gltrans.account=chartmaster.accountcode
			WHERE periodno=".$period." 
			  AND transno=".$transno;
		//	  prnMsg($SQL);
	$Result=DB_query($SQL);
	$Header='会计凭证';
	$narr='';
	$TransDate='';	
	$ToType=2;
	$tranmsg='';//'"'.'会计凭证&#10;'.$bankDate.'记:'.$transno.'&#10;';

	while($Row=DB_fetch_array($Result)){

		if(substr($Row['account'],0,4)=='1002'){
			if ($Row['account']!=$SelectBank[0] && $Row['posted']!=1){	//未核对凭证
				$ToType=1;
			}
		}elseif(substr($Row['account'],0,4)=='1122'){	//未核    凭证
				if ($Row['account']!=$SelectBank[0] && $Row['posted']!=1){	//未核对凭证
					$ToType=3;
				}
		}elseif(substr($Row['account'],0,4)=='2202'){	//未核对凭证
			if ($Row['account']!=$SelectBank[0] && $Row['posted']!=1){
				$ToType=4;
			}
		}
				
		
		if($Row['flg']==1){//数据为正
			if($Row['amount']>0){
				$jdstr="借".$Row['amount'];
			}else{
				$jdstr="贷".(-$Row['amount']);
			}
		}else{
			if($Row['amount']>0){
				$jdstr="贷".(-$Row['amount']);
			}else{
				$jdstr="借".$Row['amount']; 
			}
		}
		if ($narr==''){
		
			$TransDate=$Row['trandate'];
			$narr=$Row['narrative'];
		}
		
		if (strlen($Row['account'].$Row['accountname'])>$mlen){
			$mlen=strlen($Row['account'].$Row['accountname']);
	   }
	   $TransRow[]=$Row['account'].'&nbsp;'.$Row['accountname'];
	   if (strlen($jdstr)>$nlen){
		   $nlen=strlen($jdstr);
	   }
	   $TransAmo[]=$jdstr;
	}
	$TranCont= $Header.$TransDate.'&nbsp;记字'.$transno.'&#10;';
	$nbsp='';
	foreach($TransRow as $key=>$val){
		$len=$mlen-strlen($val)+$nlen-strlen($TransAmo[$key])+12;
		for($i=0;$i<$len;$i++){
			$nbsp.='&nbsp;';
		}
		$TranCont.=$val.$nbsp.'&nbsp;'.$TransAmo[$key].'&#10;';
		$nbsp='';
	}
	 $TranCont.='摘&nbsp;&nbsp;要:'.$narr.'"';
     return  $TranCont;
}
/**
 * 设计要点
 * GLCreateTrans($ROW,$toselectact,$tobank,$selectbank)
 *  GetCustomeAct($ROW,$_CustomeAct,$ActRule,$bankactdata)
 * GetBankTrans($ROW,$BankActData,$selectbank,$CurrRateArr)
 * UpdateCustomer($banknew,$selectbank)
 *  GetTransContent($period,$transno,$SelectBank)
 * 
 * 
 * */		


	
?>
