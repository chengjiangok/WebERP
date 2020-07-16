
<?php

/* $Id: Customers.php 694 */

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:56
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-04-16 08:21:22
 */
include('includes/session.php');
include('includes/CountriesArray.php'); // To get the currency name from the currency code.
include('includes/GLAccountFunction.php');
if (isset($_POST['Edit']) or isset($_GET['Edit']) or isset($_GET['DebtorNo'])) {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'AmendCustomer';
} else {
	$ViewTopic = 'AccountsReceivable';
	$BookMark = 'NewCustomer';
}
$Title = '客户维护';//_('Customer Maintenance');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') .
	'" alt="" />' . ' ' .$Title . '
	</p>';

if (isset($Errors)) {
	unset($Errors);
}
if (!isset($_POST['Status'])){
	$_POST['Status']=0;
}
$status=array("0"=>"启用","-1"=>"停用");
if (!isset($_POST['Address6'])) {
	$_POST['Address6'] = $_SESSION['CountryOfOperation'];
}
$Errors = array();
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
	$i=1;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
    	//检查供应商名是否存在于系统
		$SQL="SELECT `regid`,  `custname`,registerno,bankaccount, `sub`, `regdate`, `acctype`, `tag`
		FROM `register_account_sub` 
		WHERE custname LIKE '".$_POST['CustName']."' OR registerno='".$_POST['Address5']."' OR bankaccount='".$_POST['BankAct']."'";

	$Result=DB_query($SQL);
	$Row=DB_fetch_assoc($Result);
	$Error=0;
	if (isset($Row) && isset($_POST['New'])){
		
		if ($_POST['CustName']!='' && $Row['custname']!=$_POST['CustName']){
			//输入名和系统名不一样  ,提示错误停止
			$Error=-1;
			$msg.='客户:'.$_POST['CustName'].'和系统存在的名称不同,';
			//return array(0=>-1);
		}
		if ($_POST['Address5']!='' && $Row['registerno']!=$_POST['Address5']){
			//输入注册码和系统码不一样  ,提示错误停止
			$Error=-2;
			$msg.='注册码:'.$_POST['Address5'].'和系统存在的注册码不同,';
		//	return array(0=>-2);
		}
		if($Error==-1|| $Error==-2) {
			//if ($Error==-1)
			prnMsg('你添加的'.$msg,'warn');
			
			echo '<br /><div class="centre"><a href="Customers.php" >客户添加</a></div><br />';
			include('includes/footer.php');
			exit;
			
		}else{
			//系统已经存在单位,但不完善 ,如果$SuppData不存在,新单位
			$CustRow=$Row;
		}
	}	
	if (mb_strlen($_POST['CustName']) > 40 OR mb_strlen($_POST['CustName'])==0) {
		$InputError = 1;
		prnMsg( _('The customer name must be entered and be forty characters or less long'),'error');
		$Errors[$i] = 'CustName';
		$i++;
	} elseif (mb_strlen($_POST['Address1']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address1';
		$i++;
	} elseif (mb_strlen($_POST['Address2']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address2';
		$i++;
	} elseif (mb_strlen($_POST['Address3']) >40) {
		$InputError = 1;
		prnMsg( _('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'Address3';
		$i++;
	} elseif (mb_strlen($_POST['Address4']) >50) {
		$InputError = 1;
		prnMsg( _('The Line 4 of the address must be fifty characters or less long'),'error');
		$Errors[$i] = 'Address4';
		$i++;
	} elseif (mb_strlen($_POST['Address5']) >20) {
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address5';
		$i++;
	} elseif (!is_numeric(filter_number_format($_POST['CreditLimit']))) {
		$InputError = 1;
		prnMsg( _('The credit limit must be numeric'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	} elseif  (!Is_Date($_POST['ClientSince'])) {
		$InputError = 1;
		prnMsg( _('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i] = 'ClientSince';
		$i++;
	} elseif (filter_number_format($_POST['CreditLimit']) <0) {
		$InputError = 1;
		prnMsg( _('The credit limit must be a positive number'),'error');
		$Errors[$i] = 'CreditLimit';
		$i++;
	}

	if ($InputError !=1){

		$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

		if (isset($_POST['New'])) {
		
			//添加客户');
			//1.添加科目 2.写入 系统表和供应商表  3.写入读取权限				
					//$CustData{ 0=custname 1=>regid 2=>tag 3=>flg	4=>costitem 5=> bankaccount,6=>regierno
					$CustData=$_POST['CustName'].'^'.$_POST['RegID'].'^'.$_SESSION['CompanyRecord'][1]['coycode'].'^2^^'.$_POST['BankAct'].'^'.$_POST['Address5'];
					$AccountArr=AddCustomer($CustData,'1122','',0);	
					
					if ($_POST['CustomerPOLine']=='' ||!isset($_POST['CustomerPOLine']))
						$_POST['CustomerPOLine']=0;
					if ($AccountArr[0]>0){
						$result=DB_Txn_Begin();			
						$sql = "INSERT INTO debtorsmaster (	debtorno,						
															name,
															address1,
															address2,
															address3,
															address4,
															address5,
															address6,
															currcode,
															clientsince,

															holdreason,
															paymentterms,														
															creditlimit,
															salestype,														
															remark,
															customerpoline,
															typeid,
															language_id,
															contactname,															
															phoneno,

															faxno,
															email,
															taxcatid,
															taxrate,
															used,
															userid)
												VALUES ('".$AccountArr[2]."',
													'" . $_POST['CustName'] ."',
														'" . $_POST['Address1'] ."',
														'" . $_POST['Address2'] ."',
														'" . $_POST['Address3'] . "',
														'" . $_POST['Address4'] . "',
														'" . $_POST['Address5'] . "',
														'" . $_POST['Address6'] . "',
														'" . $_POST['CurrCode'] . "',
														'" . $SQL_ClientSince . "',

														'" . $_POST['HoldReason'] . "',
														'" . $_POST['PaymentTerms'] . "',														
														'" . filter_number_format($_POST['CreditLimit']) . "',
														'" . $_POST['SalesType'] . "',														
														'" . $_POST['Remark'] . "',
														'" . $_POST['CustomerPOLine'] . "',
														'" . $_POST['TypeID'] . "',
														'" . $_POST['LanguageID'] . "',
														'" . $_POST['ContactName'] . "',			
														'" . $_POST['PhoneNo'] . "',
														
														'" . $_POST['FaxNo'] . "',
														'" . $_POST['Email'] . "',
														'" . explode('^',$_POST['TaxCat'])[0] . "',
														'" . explode('^',$_POST['TaxCat'])[1] . "',
														'" . $_POST['Status'] . "',
														'".$_SESSION['UserID']."')";

						$ErrMsg = _('This customer could not be added because');
						$result = DB_query($sql,$ErrMsg);
					
						$sql="INSERT  IGNORE INTO `customerusers`(	`regid`,
															`userid`,
															`custype`,
															`canview`,
															`canupd`)
															VALUES(	'" . $AccountArr[2] . "',
															'".$_SESSION['UserID']."',
															1,
															1,
															1)";
							$result = DB_query($sql, $ErrMsg);
						
					}
					if ($result){
						$result=DB_Txn_Commit();
						prnMsg('你添加的客户'.$_POST['CustName'].'添加成功,编码:'. $AccountArr[2].' 会计科目:'. $AccountArr[1],'success');
						unset($_POST['OnEdit']);
						unset($_POST['DebtorNo']);
						unset($_POST['CustName']);
						unset($_POST['RegID']);
						unset($_SESSION['DebtorArr']);
						
						unset($_POST['Address1']);
						unset($_POST['Address2']);
						unset($_POST['Address3']);
						unset($_POST['Address4']);
						unset($_POST['Address5']);
						unset($_POST['Address6']);
						unset($_POST['HoldReason']);
						unset($_POST['PaymentTerms']);
						unset($_POST['Remark']);
						unset($_POST['PhoneNo']);
						unset($_POST['FaxNo']);
						unset($_POST['Email']);
						unset($_POST['CreditLimit']);

						unset($_POST['SalesType']);
						unset($_POST['DebtorNo']);
						unset($_POST['InvAddrBranch']);
						unset($_POST['TaxRef']);
						unset($_POST['CustomerPOLine']);
						unset($_POST['LanguageID']);
					}
		
		}
		
	} else {
		prnMsg( _('Validation failed') . '. ' . _('No updates or deletes took place'),'error');
	}

} elseif (isset($_POST['delete'])) {

	if ($_POST['OnEdit']>2) { 
		prnMsg('未能删除客户记录,因为已经有该客户的业务发生!','warn');
	}else{ 
			//ie not cancelled the delete as a result of above tests
		if ((!isset($_POST['Account'])|| strlen($_POST['Account'])<4)&&($_POST['OnEdit']<=2)){
			$result=DB_Txn_Begin();	
			$sql="DELETE FROM `registeraccount` WHERE regid='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql,$ErrMsg);
		
			$sql="DELETE FROM `accountsubject` WHERE regid='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql);
			$sql="DELETE FROM `registername` WHERE regid='" . $_POST['DebtorNo'] . "'
			  AND userid='".$_POST['UserID']."'";
			$result = DB_query($sql);
			$result=DB_Txn_Commit();
		}
		if($_POST['OnStock']<1){
			$sql="DELETE FROM debtorsmaster WHERE debtorno='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql);
			$sql="DELETE FROM `customerusers` WHERE regid='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql);
		}
		prnMsg( _('Customer') . ' ' . $_POST['DebtorNo'] . ' ' .$_POST['CustName'].'  已经删除! ','success');
		include('includes/footer.php');
		unset($_POST['OnEdit']);
		unset($_POST['DebtorNo']);
		unset($_POST['CustName']);
		unset($_POST['RegID']);
		unset($_SESSION['DebtorArr']);
		
		
		unset($_POST['Address1']);
		unset($_POST['Address2']);
		unset($_POST['Address3']);
		unset($_POST['Address4']);
		unset($_POST['Address5']);
		unset($_POST['Address6']);
		unset($_POST['HoldReason']);
		unset($_POST['PaymentTerms']);
		unset($_POST['Remark']);
		unset($_POST['PhoneNo']);
		unset($_POST['FaxNo']);
		unset($_POST['Email']);
		unset($_POST['CreditLimit']);

		unset($_POST['SalesType']);
		unset($_POST['DebtorNo']);
		unset($_POST['InvAddrBranch']);
		unset($_POST['TaxRef']);
		unset($_POST['CustomerPOLine']);
		unset($_POST['LanguageID']);
		//exit;
	
	}//end if Delete Customer*/
}

if(isset($_POST['Reset'])){
	
	unset($_POST['OnEdit']);
	unset($_POST['DebtorNo']);
	unset($_POST['CustName']);
	unset($_POST['RegID']);
	unset($_SESSION['DebtorArr']);
	
	unset($_POST['Address1']);
	unset($_POST['Address2']);
	unset($_POST['Address3']);
	unset($_POST['Address4']);
	unset($_POST['Address5']);
	unset($_POST['Address6']);
	unset($_POST['HoldReason']);
	unset($_POST['PaymentTerms']);
	unset($_POST['Remark']);
	unset($_POST['PhoneNo']);
	unset($_POST['FaxNo']);
	unset($_POST['Email']);
	unset($_POST['CreditLimit']);

	unset($_POST['SalesType']);
	unset($_POST['DebtorNo']);
	unset($_POST['InvAddrBranch']);
	unset($_POST['TaxRef']);
	unset($_POST['CustomerPOLine']);
	unset($_POST['LanguageID']);

}

/*DebtorNo could be set from a post or a get when passed as a parameter to this page */

if (isset($_POST['DebtorNo'])){
	$DebtorNo = $_POST['DebtorNo'];
} elseif (isset($_GET['DebtorNo'])){
	$DebtorNo = $_GET['DebtorNo'];
}
if (isset($_POST['ID'])){
	$ID = $_POST['ID'];
} elseif (isset($_GET['ID'])){
	$ID = $_GET['ID'];
} else {
	$ID='';
}
if (isset($_POST['Edit'])){
	$Edit = $_POST['Edit'];
} elseif (isset($_GET['Edit'])){
	$Edit = $_GET['Edit'];
} else {
	$Edit='';
}

if (isset($_POST['Add'])){
	$Add = $_POST['Add'];
} elseif (isset($_GET['Add'])){
	$Add = $_GET['Add'];
}

if (!isset($DebtorNo)) {

	/*If the page was called without $_POST['DebtorNo'] passed to page then assume a new customer is to be entered show a form with a Debtor Code field other wise the form showing the fields with the existing entries against the customer will show for editing with only a hidden DebtorNo field*/
	/* First check that all the necessary items have been setup */

	$SetupErrors=0; //Count errors
	$sql="SELECT COUNT(typeabbrev)
				FROM salestypes";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]==0) {
		prnMsg( _('In order to create a new customer you must first set up at least one sales type/price list') . '<br />' .
			_('Click').' ' . '<a target="_blank" href="' . $RootPath . '/SalesTypes.php">' . _('here').' ' . '</a>' . _('to set up your price lists'),'warning') . '<br />';
		$SetupErrors += 1;
	}
	$sql="SELECT COUNT(typeid)
				FROM debtortype";
	$result=DB_query($sql);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]==0) {
		prnMsg( _('In order to create a new customer you must first set up at least one customer type') . '<br />' .
			_('Click').' ' . '<a target="_blank" href="' . $RootPath . '/CustomerTypes.php">' . _('here').' ' . '</a>' . _('to set up your customer types'),'warning');
		$SetupErrors += 1;
	}

	if ($SetupErrors>0) {
		echo '<br /><div class="centre"><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" >' . _('Click here to continue') . '</a></div>';
		include('includes/footer.php');
		exit;
	}
	
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="New" value="Yes" />';
	
	$DataError =0;
	
	   $SQL="SELECT	`regid`,
					`registerno`,
					`custname`,
					`tag`,
					`sub`,
					`regdate`,
					`acctype`
				FROM `custname_reg_sub`
				WHERE regid NOT IN(	SELECT	`debtorno`	FROM	`debtorsmaster`	)
					   AND (acctype=1 OR acctype=3 OR acctype=0)
					   AND LENGTH(custname)>15";
		$Result=DB_query($SQL);
	echo '<table cellpadding="3" class="selection">';
	echo '<tr>
			<td >', _('Enter a partial Name'), ':</td>
			<td colspan="3">
				<input type="text" name="RegCustName"  id="RegCustName" placeholder="输入编码、名称关键词筛选，然后选择供应商"  autocomplete="off"  list="CustCode"   maxlength="50" size="50" /> 
				<datalist id="CustCode"> ';		
					while ($row=DB_fetch_array($Result )){
						echo '<option value="' . $row['regid'] .':'.htmlspecialchars($row['custname'], ENT_QUOTES,'UTF-8', false) . ':'.$row['registerno'] . '"label="">';
					}
	echo'</datalist></td>
		</tr>';
	echo '<tr>
			<td></td>
			<td  colspan="3">或输入注册码:    <input maxlength="30" name="RegisterNo" pattern="[\w-]*" size="25" type="text" title="', _('If there is an entry in this field then customers with the text entered in their customer code will be returned') , '" value="'.$_POST['RegisterNo'] . '" /></td>
		</tr>';
	echo '<tr>
			<td></td>		
			<td colspan="3">或输入:银行账号<input maxlength="30" name="BankAct" size="25" type="text"  value="' . $_POST['BankAct'] . '"/></td>
		</tr>';
	echo '</table><br />';
	echo '<div class="centre">
			<input name="Read" type="submit" value="', _('Search Now'), '" />
			<input name="Reset" type="submit" value="清空" />
		</div>';	
	if (isset($_POST['Read'])){
		if (!strpos($_POST['RegCustName'],':')){		
		
			
			$SQL="SELECT `regid`,  `custname`,registerno,bankaccount, `sub`, `regdate`, `acctype`, `tag`
			FROM `register_account_sub` 
			WHERE custname LIKE '".$_POST['RegCustName']."' OR registerno='".$_POST['RegisterNo']."' OR bankaccount='".$_POST['BankAct']."'";
	        $OnEdit = 0;
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);
			$Error=0;
			if (isset($Row)){// && isset($_POST['New'])){
				
				if ($_POST['RegCustName']!='' && $Row['custname']!=$_POST['RegCustName']){
					//输入名和系统名不一样  ,提示错误停止
					$Error=-1;
					$msg.='客户:'.$_POST['RegCustName'].'和系统存在的名称不同,';
					//return array(0=>-1);
				}
				if ($_POST['RegisterNo']!='' && $Row['registerno']!=$_POST['RegisterNo']){
					//输入注册码和系统码不一样  ,提示错误停止
					$Error=-2;
					$msg.='注册码:'.$_POST['RegisterNo'].'和系统存在的注册码不同,';
				//	return array(0=>-2);
				}
				if ($Row['sub']!=''&&$Error==0){
					$_POST['Account']=$Row['sub'];
					prnMsg('你添加的客户'.$_POST['RegCustName'].'已经添加,编码:'.$Row['regid'].' 会计科目:'.$Row['sub'],'info');
					$CustData=$Row;
					//echo '<br /><div class="centre"><a href="Customers.php" >客户添加</a></div><br />';
					//include('includes/footer.php');
					//exit;
				}elseif($Error==-1|| $Error==-2) {
					//if ($Error==-1)
					prnMsg('你添加的'.$msg,'warn');
					
					echo '<br /><div class="centre"><a href="Customers.php" >客户添加</a></div><br />';
					include('includes/footer.php');
					exit;
					
				}else{
					//系统已经存在单位,但不完善 ,如果$SuppData不存在,��单位
					$CustData=$Row;
				}
			}else {//新客户
				$_POST['CustName']=$_POST['RegCustName'];
			    $_POST['Address5']=$_POST['RegisterNo'];
			    $_POST['Address4']=$_POST['BankAct'];
	
			}

		}else{//选择客户
			$OnEdit +=1;
			$rcnarr=explode(':',$_POST['RegCustName']);
			$_POST['CustName']=$rcnarr[1];
			$_POST['Address5']=$rcnarr[2];
			$_POST['RegID']=$rcnarr[0];
		}
		echo '<input type="hidden" name="Account" value="' . $_POST['Account'] . '" />';
		echo '<input type="hidden" name="Regid" value="' . $_POST['RegID'] . '" />';
	     if ($Error!=1){
			//添加stockmoves 表检查
			
			if ( $_POST['RegID'] >0|| isset($_POST['RegID'])){
				$sql= "SELECT count(*) FROM `stockmoves` WHERE debtorno='" . $_POST['RegID'] . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$OnEdit+=1;
				
				}
	
				$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['RegID'] . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$OnEdit+=1;
				
				}
			}
			if ( $_POST['Account']!=''&& isset($_POST['Account'])){
				$sql="SELECT count(* )FROM `gltrans` WHERE account='" . $_POST['Account'] . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$OnEdit+=1;
				}
			}
			
		}
		echo '<input type="hidden" name="OnEdit" value="' . $OnEdit . '" />';
	}
	// ----------End search for customers.	
	echo'<table class="selection">';

	if ($_SESSION['AutoDebtorNo']==0)  {
		echo '<tr>
				<td>' . _('Customer Code') . ':</td>
				<td><input type="text" data-type="no-illegal-chars" tabindex="1"  name="DebtorNo"  required="required" autofocus="autofocus"  title ="'._('Up to 10 characters for the customer code. The following characters are prohibited:') . ' \' &quot; + . &amp; \\ &gt; &lt;" placeholder="'._('alpha-numeric').'" size="11" maxlength="10" /></td></tr>';
	}

	echo '<tr>
			<td>' . _('Customer Name') . ':</td>
			<td>';
			if (isset($_POST['CustName'])&& strlen($_POST['CustName'])>=5){
			   echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="'.$_POST['CustName'].'" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />
			        <input  type="hidden" name="RegID" value="'.$_POST['RegID'].'" />';
			}else{
				echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$" />';	
			}
			echo'</td>
		</tr>
		<tr>
		<td>详细地址:</td>
			<td><input tabindex="3" type="text" name="Address1"  size="30" maxlength="30" /></td>
		</tr>
		<tr>
			<td>地址(省市/区):</td>
			<td><input tabindex="4" type="text" name="Address2" size="30" maxlength="30" /></td>
		</tr>
		<tr>
			<td>开户银行:</td>
			<td><input tabindex="5" type="text" name="Address3" size="25" maxlength="30"  /></td>
		</tr>
		<tr>
			<td>账号:</td>
			<td>';
			if (isset($_POST['Address4'])&& strlen($_POST['Address4'])>5){
			   echo'<input tabindex="6" type="text" name="Address4" size="22" maxlength="20" pattern="^[a-zA-Z0-9]*\d{5,30}?"　 value="'.$_POST['Address4'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
			}else{
				echo'<input tabindex="6" type="text" name="Address4" size="22" maxlength="20" value=""   pattern="^[a-zA-Z0-9]*\d{5,30}?"　 />';	
			}
			echo'</td>
			<td></td>
		</tr>
		<tr>
			<td>注册码/税号:</td>
			<td>';
			if (isset($_POST['Address5']) && strlen($_POST['Address5'])>9){
			   echo'<input tabindex="7" type="text" name="Address5" size="22" maxlength="20" value="'.$_POST['Address5'].'" '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
			}else{
				echo'<input tabindex="7" type="text" name="Address5" size="22" maxlength="20" value=""  pattern="^[a-zA-Z0-9]*\d{9,30}?"　/>';	
			}
			echo'</td>
		</tr>';


	echo '<tr>
			<td>' . _('Country') . ':</td>
			<td><select name="Address6">';
			foreach ($CountriesArray as $CountryEntry => $CountryName) {
				if (isset($_POST['Address6']) AND ($_POST['Address6'] == $CountryEntry)) {
					echo '<option selected="selected" value="' . $CountryEntry . '">' . $CountryName . '</option>';
				
				} else {
					echo '<option value="' . $CountryEntry . '">' . $CountryName . '</option>';
				}
			}
	echo '</select></td>
		</tr>';
	echo '<tr>
		<td>联系人:</td>';
		if (!isset($_POST['ContactName'])){
			 $_POST['ContactName']='';
		}
		echo '<td><input tabindex="3" type="text" name="ContactName"  size="12" maxlength="20" value="'. $_POST['ContactName'].'" /></td>
			</tr>';
		echo'<tr>
			<td>' . _('Phone Number').':</td>';
		if (!isset($_POST['PhoneNo'])) {
			$_POST['PhoneNo']='';
		}
		echo '<td><input tabindex="16" type="tel" name="PhoneNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['PhoneNo'].'" /></td>
		</tr>';

		echo '<tr>
				<td>其他联系方式:</td>';
			if (!isset($_POST['FaxNo'])) {
			$_POST['FaxNo']='';
			}
			echo '<td><input tabindex="17" type="tel" name="FaxNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['FaxNo'].'" /></td>
			</tr>';

			if (!isset($_POST['Email'])) {
			$_POST['Email']='';
			}
			echo '<tr>
	<td>' . (($_POST['Email']) ? '<a href="Mailto:'.$_POST['Email'].'">' . _('Email').':</a>' : _('Email').':') . '</td>';
	//only display email link if there is an email address
	echo '<td><input tabindex="18" type="email" name="Email" placeholder="e.g. example@domain.com" size="40" maxlength="40" value="'. $_POST['Email'].'" /></td>
	</tr>';

	// Show Sales Type drop down list
	$result=DB_query("SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type");
	if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr>
				<td colspan="2">' . prnMsg(_('No sales types/price lists defined'),'error') . '<br /><a href="SalesTypes.php?" target="_parent">' . _('Setup Types') . '</a></td>
			</tr>';
	} else {
        echo '<tr>
				<td>' . _('Sales Type') . '/' . _('Price List') . ':</td>
			   <td><select tabindex="9" name="SalesType" required="required">';

		while ($myrow = DB_fetch_array($result)) {
		   echo '<option value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		} //end while loopre
		DB_data_seek($result,0);
        echo '</select></td>
			</tr>';
	}

    // Show Customer Type drop down list
	$result=DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
	if (DB_num_rows($result)==0){
	   $DataError =1;
	   echo '<a href="SalesTypes.php?" target="_parent">' . _('Setup Types') . '</a>';
	   echo '<tr>
				<td colspan="2">' . prnMsg(_('No Customer types/price lists defined'),'error') . '</td>
			</tr>';
	} else {
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td><select tabindex="9" name="TypeID" required="required">';

		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>
			</tr>';
	}

	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr>
			<td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
			<td><input tabindex="10" type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="ClientSince" value="' . $DateString . '" size="12" maxlength="10" /></td>
		</tr>';
		$SQL = "SELECT `taxid` , `description`, `taxrate` FROM `taxauthorities` WHERE onorder IN (1,3)";
		$TaxGroupResults = DB_query($SQL);
		
		echo '<tr>
				<td>税种:</td>
				<td><select tabindex="19" name="TaxCat">';
	
		while ($myrow = DB_fetch_array($TaxGroupResults)) {
			if (isset($_POST['TaxCat']) AND $myrow['taxid'].'^'.$myrow['taxrate']==$_POST['TaxCat']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['taxid'].'^'.$myrow['taxrate'].'">' . $myrow['description'] . '</option>';
	
		}//end while loop
	
		echo '</select></td>
			</tr>';		
		echo'<tr>
					<td>' . _('Credit Limit') . ':</td>
					<td><input tabindex="14" type="text" class="integer" name="CreditLimit" required="required" value="' . locale_number_format($_SESSION['DefaultCreditLimit'],0) . '" size="16" maxlength="14" /></td>
				</tr>';

		$result=DB_query("SELECT terms, termsindicator FROM paymentterms");
		if (DB_num_rows($result)==0){
			$DataError =1;
			echo '<tr><td colspan="2">' . prnMsg(_('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
		} else {

			echo '<tr>
					<td>' . _('Payment Terms') . ':</td>
					<td><select tabindex="15" name="PaymentTerms" required="required">';

			while ($myrow = DB_fetch_array($result)) {
				echo '<option value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
			} //end while loop
			DB_data_seek($result,0);

			echo '</select></td></tr>';
	    }
		echo '<tr>
				<td>' . _('Credit Status') . ':</td>
				<td><select tabindex="16" name="HoldReason" required="required">';

		$result=DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
		if (DB_num_rows($result)==0){
			$DataError =1;
			echo '<tr>
					<td colspan="2">' . prnMsg(_('There are no credit statuses currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
				</tr>';
		} else {
			while ($myrow = DB_fetch_array($result)) {
				echo '<option value="'. $myrow['reasoncode'] . '">' . $myrow['reasondescription'] . '</option>';
			} //end while loop
			DB_data_seek($result,0);
			echo '</select></td></tr>';
		}

		$result=DB_query("SELECT currency, currabrev FROM currencies");
		if (DB_num_rows($result)==0){
			$DataError =1;
			echo '<tr>
					<td colspan="2">' . prnMsg(_('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
				</tr>';
		} else {
			if (!isset($_POST['CurrCode'])){
				$CurrResult = DB_query("SELECT currencydefault FROM companies WHERE coycode=1");
				$myrow = DB_fetch_row($CurrResult);
				$_POST['CurrCode'] = $myrow[0];
			}
			echo '<tr>
					<td>' . _('Customer Currency') . ':</td>
					<td><select tabindex="17" name="CurrCode" required="required">';
			while ($myrow = DB_fetch_array($result)) {
				if ($_POST['CurrCode']==$myrow['currabrev']){
					echo '<option selected="selected" value="'. $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
				} else {
					echo '<option value="'. $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
				}
			} //end while loop
			DB_data_seek($result,0);

			echo '</select></td>
				</tr>';
		}

		echo '<tr>
				<td>' . _('Language') . ':</td>
				<td><select name="LanguageID" required="required">';

		if (!isset($_POST['LanguageID']) OR $_POST['LanguageID']==''){
			$_POST['LanguageID']=$_SESSION['Language'];
		}

		foreach ($LanguagesArray as $LanguageCode => $LanguageName){
			if ($_POST['LanguageID'] == $LanguageCode){
				echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			} else {
				echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			}
		}
		echo '</select></td>
				</tr>';
		echo'<tr>
				<td>备注:</td>
				<td> <textarea  placeholder="在这里输入备注内容..." cols="45" rows="3" name="Remark" ></textarea></td>
			</tr>';
		echo'<tr>
			<td>状态:</td>
			<td><select name="Status" >';
		if (isset($_POST['Status'])&&$_POST['Status']==0){
				echo '<option selected="selected" value="0">启用</option>
				         <option  value="-1">停用</option>';
		}else{
				echo '<option value="0">启用</option>
				<option selected="selected"  value="-1">停用</option>';
		}
		echo '</select></td>
					</tr>';
	   
		echo'</table>';
		if ($DataError ==0){
			echo '<br />
				<div class="centre">
					<input tabindex="20" type="submit" name="submit" value="' . _('Add New Customer') . '" />&nbsp;
					<input tabindex="21" type="submit" name="Reset" value="' . _('Reset') . '" />
				</div>';

		}
		echo '</div>
		     </form>';
			
} else {
	//if (!isset($_POST['Address6'])) {
	//	$_POST['Address6'] = $_SESSION['CountryOfOperation'];
	//	}
	
	if (isset($_POST['Update'])) {
	
		$sqlstr='';
		$sqlname='';
		$sqlbank='';
		$sqlreg='';
		foreach($_SESSION['DebtorArr'] as $key=>$val){
		
			if ($key=="name" && $_POST['CustName']!=$val && strlen($_POST['CustName'])>0){
				$sqlstr.="  name='".trim($_POST['CustName'])."',";
				$sqlname=trim($_POST['CustName']);
			}elseif ($key=="address1" && $_POST['Address1']!=$val&&strlen($_POST['Address1'])>0){
				$sqlstr.="  address1=' ".$_POST['Address1']."',";
			}elseif ($key=="address2" && $_POST['Address2']!=$val&&strlen($_POST['Address2'])>0){
				$sqlstr.="  address2= '".$_POST['Address2']."',";
			}elseif ($key=="address3" && $_POST['Address3']!=$val && strlen($_POST['Address3'])>0){
				$sqlstr.="  address3= '".$_POST['Address3']."',";
			}elseif ($key=="address4" && $_POST['Address4']!=$val && strlen($_POST['Address4'])>0){
				//银行账号
				$sqlstr.="  address4='".$_POST['Address4']."',";
				$sqlbank=$_POST['Address4'];
			}elseif ($key=="address5" && $_POST['Address5']!=$val && strlen($_POST['Address5'])>0){
				//注册码
				$sqlreg=$_POST['Address5'];
				$sqlstr.="  address5='".$_POST['Address5']."',";
			}elseif ($key=="address6" && $_POST['Address6']!=$val && strlen( $_POST['Address6'])>2){
				$sqlstr.="  address6= '".$_POST['Address6']."',";
			}elseif ($key=="currcode" && $_POST['CurrCode']!=$val && strlen( $_POST['CurrCode'])>0){
				$sqlstr.="  currcode= '".$_POST['CurrCode']."',";
			}elseif ($key=="salestype" && $_POST['SalesType']!=$val && strlen( $_POST['SalesType'])>0){
				$sqlstr.="  salestype= '".$_POST['SalesType']."',";
			}elseif ($key=="holdreason" && $_POST['HoldReason']!=$val && strlen( $_POST['HoldReason'])>0){
				$sqlstr.="  holdreason= '".$_POST['HoldReason']."',";
			}elseif ($key=="paymentterms" && $_POST['PaymentTerms']!=$val && strlen( $_POST['PaymentTerms'])>0){
				$sqlstr.="  paymentterms= '".$_POST['PaymentTerms']."',";
			}elseif ($key=="remark" && $_POST['Remark']!=$val && strlen( $_POST['Remark'])>0){
				$sqlstr.="  remark= '".$_POST['Remark']."',";
			}elseif ($key=="phoneno" && $_POST['PhoneNo']!=$val && strlen( $_POST['PhoneNo'])>0){
				$sqlstr.="  phoneno='".$_POST['PhoneNo']."',";
			}elseif ($key=="faxno" && $_POST['FaxNo']!=$val && strlen( $_POST['FaxNo'])>0){
				$sqlstr.="  faxno= '".$_POST['FaxNo']."',";
			}elseif ($key=="email" && $_POST['Email']!=$val && strlen( $_POST['Email'])>0){
				$sqlstr.="  email= '".$_POST['Email']."',";
			}elseif ($key=="creditlimit" && filter_number_format($_POST['CreditLimit'])!=$val && strlen($_POST['CreditLimit'])>0){
				$sqlstr.="  creditlimit= '".filter_number_format($_POST['CreditLimit'])."',";
			}elseif ($key=="invaddrbranch" && $_POST['InvAddrBranch']!=$val && strlen( $_POST['InvAddrBranch'])>0){
				$sqlstr.="  invaddrbranch= '".$_POST['InvAddrBranch']."',";
			}elseif ($key=="taxcatid" && explode('^',$_POST['TaxCat'])[0]!=$val && strlen( $_POST['TaxCat'])>0){
				$sqlstr.="  taxcatid= '".explode('^',$_POST['TaxCat'])[0]."',";
			}elseif ($key=="taxrate" && explode('^',$_POST['TaxCat'])[1]!=$val && strlen( $_POST['TaxCat'])>0){
				$sqlstr.="  taxrate= '".explode('^',$_POST['TaxCat'])[1]."',";
			}elseif ($key=="customerpoline" && $_POST['CustomerPOLine']!=$val && strlen( $_POST['CustomerPOLine'])>0){
				$sqlstr.="  customerpoline= '".$_POST['CustomerPOLine']."',";
			}elseif ($key=="languageid" && $_POST['LanguageID']!=$val && strlen( $_POST['LanguageID'])>0){
				$sqlstr.="  languageid= '".$_POST['LanguageID']."',";
			}elseif ($key=="typeid" && $_POST['TypeID']!=$val && strlen( $_POST['TypeID'])>0){
				$sqlstr.="  typeid= '".$_POST['TypeID']."',";
			}elseif ($key=="clientsince" && $_POST['ClientSince']!=$val && strlen( $_POST['clientsince'])>0){
				$sqlstr.="  clientsince= '".FormatDateForSQL($_POST['clientsince'])."',";
			}elseif ($key=="contactname" && $_POST['ContactName']!=$val && strlen( $_POST['ContactName'])>0){
				$sqlstr.="  contactname= '".$_POST['ContactName']."',";
			}elseif ($key=="used" && $_POST['Status']!=$val){//} && strlen( $_POST['Status']==true)){
				$sqlstr.="  used= '".$_POST['Status']."',";
			}
		}
		$update=0;
		if ($sqlstr!=''){
			$update++;
			$result=DB_Txn_Begin();	
			$sqlstr=substr($sqlstr,0,-1);
			$sql="UPDATE `debtorsmaster` SET  lastpaiddate='".date('Y-m-d H:i:s')."',userid='".$_SESSION['UserID']."',".$sqlstr." WHERE 					debtorno=".$_POST['DebtorNo'];
			 $result=DB_query($sql); 
		}
		if ($sqlbank!=''){
			$update++;
			$sql="UPDATE `accountsubject` SET `bankaccount`='".$sqlbank."' WHERE regid=".$_POST['DebtorNo'];
			$result=DB_query($sql);
		}
		if ($sqlname!=''){
			$update++;
			$sql="UPDATE `registername` SET `custname`='".$sqlname."' WHERE regid=".$_POST['DebtorNo'];
			$result=DB_query($sql);
		}
		if ($sqlreg!=''){
			$update++;
			$sql="UPDATE `registeraccount` SET `registerno`='".$sqlreg."' WHERE regid=".$_POST['DebtorNo'];
			$result=DB_query($sql);
		}
		if ($result){
			$result=DB_Txn_Commit();
			if($update>0){
				//$update.$sqlstr.$_SESSION['CountryOfOperation']. strlen( $_POST['Address6']).
				prnMsg('你的客户['.$_POST['DebtorNo'].']'.$_POST['CustName'].'更新操作成功!','success');
			}else{
				prnMsg('你没有做任何改变!','info');
			}
			unset($_POST['OnEdit']);
			unset($_POST['DebtorNo']);
			unset($_POST['CustName']);
			unset($_POST['RegID']);
			unset($_SESSION['DebtorArr']);		
			unset($_POST['Address1']);
			unset($_POST['Address2']);
			unset($_POST['Address3']);
			unset($_POST['Address4']);
			unset($_POST['Address5']);
			unset($_POST['Address6']);
			unset($_POST['HoldReason']);
			unset($_POST['PaymentTerms']);
			unset($_POST['Remark']);
			unset($_POST['PhoneNo']);
			unset($_POST['FaxNo']);
			unset($_POST['Email']);
			unset($_POST['CreditLimit']);
	
			unset($_POST['SalesType']);
			unset($_POST['DebtorNo']);
			unset($_POST['InvAddrBranch']);
			unset($_POST['TaxRef']);
			unset($_POST['CustomerPOLine']);
			unset($_POST['LanguageID']);
		}
	
	}
    //DebtorNo exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (!isset($_POST['New'])) {
	
		$sql = "SELECT debtorno,
						custname name,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						currcode,
						salestype,
						clientsince,
						holdreason,
						paymentterms,
						remark,
						taxcatid,
						taxrate,
						creditlimit,
						invaddrbranch,					
						customerpoline,
						typeid,
						language_id,
						phoneno,
						faxno,
						email,
						userid,
						sub,
						a.used,
						contactname
				FROM debtorsmaster a
				LEFT JOIN custname_reg_sub b ON a.debtorno=b.regid
				WHERE debtorno = '" . $DebtorNo . "'";

		$ErrMsg = _('The customer details could not be retrieved because');
		$result = DB_query($sql,$ErrMsg);

		$myrow = DB_fetch_array($result);

	     foreach($myrow as $key=>$val){
			 if (!is_int($key)){
			 	$_SESSION['DebtorArr'][$key]=$val;
			 }
		 }
		//	var_dump($_SESSION['DebtorArr']);
		$_POST['CustName'] = $myrow['name'];
		$_POST['Address1']  = $myrow['address1'];
		$_POST['Address2']  = $myrow['address2'];
		$_POST['Address3']  = $myrow['address3'];
		$_POST['Address4']  = $myrow['address4'];
		$_POST['Address5']  = $myrow['address5'];
		$_POST['Address6']  = $myrow['address6'];
		$_POST['SalesType'] = $myrow['salestype'];
		$_POST['CurrCode']  = $myrow['currcode'];
		$_POST['ClientSince'] = ConvertSQLDate($myrow['clientsince']);
		$_POST['HoldReason']  = $myrow['holdreason'];
		$_POST['PaymentTerms']  = $myrow['paymentterms'];
		$_POST['PhoneNo']  = $myrow['phoneno'];
		$_POST['FaxNo']  = $myrow['FaxNo'];
		$_POST['Email']  = $myrow['Email'];
		$_POST['UserID'] = $myrow['userid'];
		$_POST['CreditLimit']	= locale_number_format($myrow['creditlimit'],0);
		$_POST['InvAddrBranch'] = $myrow['invaddrbranch'];
	    $_POST['Remark'] = $myrow['remark'];
		$_POST['CustomerPOLine'] = $myrow['customerpoline'];
		$_POST['TypeID'] = $myrow['typeid'];
		$_POST['LanguageID'] = $myrow['language_id'];
		$_POST['TaxCat'] = $myrow['taxcatid'].'^'.$myrow['taxrate'];
		$_POST['Account'] = $myrow['sub'];
		$_POST['Status'] = $myrow['used'];
		$_POST['ContactName'] = $myrow['contactname'];

		echo '<input type="hidden" name="Account" value="' . $_POST['Account'] . '" />';
		echo '<input type="hidden" name="DebtorNo" value="' . $DebtorNo . '" />';
		$OnEdit = 0;
		$OnStock=0;
		$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$OnEdit+=1;
			$OnStock+=1;
	
		}
		$sql= "SELECT count(*) FROM `stockmoves` WHERE debtorno='" . $_POST['DebtorNo'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$OnEdit+=1;
			$OnStock+=1;	
		}

		$sql="SELECT count(* )FROM `gltrans` WHERE account='" . $_POST['Account'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$OnEdit+=1;
			
		}
		$sql= "SELECT COUNT(*) FROM salesorders WHERE debtorno='" . $_POST['DebtorNo'] . "'";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$OnEdit += 1;
			$OnStock+=1;
		}
		//银行导入查询  发票导入查询
			$_POST['OnEdit'] = $OnEdit;
	     echo '<input type="hidden" name="OnEdit" value="' . $_POST['OnEdit'] . '" />';
	} else {
	    // its a new customer being added
		echo '<input type="hidden" name="New" value="Yes" />';	
	}
	  
	   echo '<table class="selection">';
	  
	echo '<tr>
	<td>' . _('Customer Name') . ':</td>
	<td>';
	if (isset($_POST['CustName'])&& strlen($_POST['CustName'])>=5){
	   echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="'.$_POST['CustName'].'" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$" '.($_POST['OnEdit']>=1? "readOnly":"").' />
			<input  type="hidden" name="RegID" value="'.$_POST['RegID'].'" />';
	}else{
		echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$" />';	
	}
	echo'</td>
		</tr>
		<tr>
		<td>详细地址:</td>
			<td><input tabindex="3" type="text" name="Address1"  size="30" maxlength="30" /></td>
		</tr>
		<tr>
			<td>地址(省市/区):</td>
			<td><input tabindex="4" type="text" name="Address2" size="30" maxlength="30" /></td>
		</tr>
		<tr>
			<td>开户银行:</td>
			<td><input tabindex="5" type="text" name="Address3" size="25" maxlength="30"  /></td>
		</tr>
		<tr>
			<td>账号:</td>
			<td>';
		if (isset($_POST['Address4'])&& strlen($_POST['Address4'])>5){
			echo'<input tabindex="6" type="text" name="Address4" size="22" maxlength="20" value="'.$_POST['Address4'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
		}else{
				echo'<input tabindex="6" type="text" name="Address4" size="22" maxlength="20" value=""   pattern="^[a-zA-Z0-9]*\d{5,30}?"　 />';	
		}
			echo'</td>
			<td></td>
		</tr>
		<tr>
			<td>注册号/税号:</td>
			<td>';
			if (isset($_POST['Address5']) && strlen($_POST['Address5'])>9){
				echo'<input tabindex="7" type="text" name="Address5" size="22" maxlength="20" value="'.$_POST['Address5'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
			}else{
				echo'<input tabindex="7" type="text" name="Address5" size="22" maxlength="20" value=""  pattern="^[a-zA-Z0-9]*\d{9,30}?"　/>';	
			}
		echo'</td>
		</tr>';

		
		echo '<tr>
			<td>' . _('Country') . ':</td>
			<td><select name="Address6">';
			foreach ($CountriesArray as $CountryEntry => $CountryName) {
				if (isset($_POST['Address6']) AND ($_POST['Address6'] == $CountryEntry)) {
					echo '<option selected="selected" value="' . $CountryEntry . '">' . $CountryName . '</option>';
				
				} else {
					echo '<option value="' . $CountryEntry . '">' . $CountryName . '</option>';
				}
			}
		echo '</select></td>
		</tr>';
		echo '<tr>
		<td>联系人:</td>';
		if (!isset($_POST['ContactName'])){
			$_POST['ContactName']='';
		}
		echo '<td><input tabindex="3" type="text" name="ContactName"  size="12" maxlength="20" value="'. $_POST['ContactName'].'" /></td>
			</tr>';
		echo'<tr>
			<td>' . _('Phone Number').':</td>';
		if (!isset($_POST['PhoneNo'])) {
			$_POST['PhoneNo']='';
		}
		echo '<td><input tabindex="16" type="tel" name="PhoneNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['PhoneNo'].'" /></td>
		</tr>';

		echo '<tr>
				<td>其他联系方式:</td>';
			if (!isset($_POST['FaxNo'])) {
			$_POST['FaxNo']='';
			}
			echo '<td><input tabindex="17" type="tel"   name="FaxNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['FaxNo'].'" /></td>
			</tr>';

			if (!isset($_POST['Email'])) {
			$_POST['Email']='';
			}
			echo '<tr>
		<td>' . (($_POST['Email']) ? '<a href="Mailto:'.$_POST['Email'].'">' . _('Email').':</a>' : _('Email').':') . '</td>';
		//only display email link if there is an email address
		echo '<td><input tabindex="18" type="email" name="Email" placeholder="e.g. example@domain.com" size="40" maxlength="40" value="'. $_POST['Email'].'" /></td>
		</tr>';

		// Show Sales Type drop down list
		$result=DB_query("SELECT typeabbrev, sales_type FROM salestypes ORDER BY sales_type");
		if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<tr>
				<td colspan="2">' . prnMsg(_('No sales types/price lists defined'),'error') . '<br /><a href="SalesTypes.php?" target="_parent">' . _('Setup Types') . '</a></td>
			</tr>';
		} else {
		echo '<tr>
				<td>' . _('Sales Type') . '/' . _('Price List') . ':</td>
			<td><select tabindex="9" name="SalesType" required="required">';

		while ($myrow = DB_fetch_array($result)) {
		echo '<option value="'. $myrow['typeabbrev'] . '">' . $myrow['sales_type'] . '</option>';
		} //end while loopre
		DB_data_seek($result,0);
		echo '</select></td>
			</tr>';
		}

		// Show Customer Type drop down list
		$result=DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
		if (DB_num_rows($result)==0){
		$DataError =1;
		echo '<a href="SalesTypes.php?" target="_parent">' . _('Setup Types') . '</a>';
		echo '<tr>
				<td colspan="2">' . prnMsg(_('No Customer types/price lists defined'),'error') . '</td>
			</tr>';
		} else {
		echo '<tr>
				<td>' . _('Customer Type') . ':</td>
				<td><select tabindex="9" name="TypeID" required="required">';

		while ($myrow = DB_fetch_array($result)) {
			echo '<option value="'. $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td>
			</tr>';
		}

		$DateString = Date($_SESSION['DefaultDateFormat']);
		echo '<tr>
			<td>' . _('Customer Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
			<td><input tabindex="10" type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="ClientSince" value="' . $DateString . '" size="12" maxlength="10" /></td>
		</tr>';
		$SQL = "SELECT `taxid` , `description`, `taxrate` FROM `taxauthorities` WHERE onorder IN (1,3)";
		$TaxGroupResults = DB_query($SQL);

		echo '<tr>
				<td>税种:</td>
				<td><select tabindex="19" name="TaxCat">';

		while ($myrow = DB_fetch_array($TaxGroupResults)) {
			if (isset($_POST['TaxCat']) AND $myrow['taxid'].'^'.$myrow['taxrate']==$_POST['TaxCat']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $myrow['taxid'].'^'.$myrow['taxrate'].'">' . $myrow['description'] . '</option>';

		}//end while loop

		echo '</select></td>
			</tr>';		
		echo'<tr>
					<td>' . _('Credit Limit') . ':</td>
					<td><input tabindex="14" type="text" class="integer" name="CreditLimit" required="required" value="' . locale_number_format($_SESSION['DefaultCreditLimit'],0) . '" size="16" maxlength="14" /></td>
				</tr>';

		$result=DB_query("SELECT terms, termsindicator FROM paymentterms");
		if (DB_num_rows($result)==0){
			$DataError =1;
			echo '<tr><td colspan="2">' . prnMsg(_('There are no payment terms currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td></tr>';
		} else {

			echo '<tr>
					<td>' . _('Payment Terms') . ':</td>
					<td><select tabindex="15" name="PaymentTerms" required="required">';

			while ($myrow = DB_fetch_array($result)) {
				echo '<option value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
			} //end while loop
			DB_data_seek($result,0);

			echo '</select></td></tr>';
		}
		echo '<tr>
				<td>' . _('Credit Status') . ':</td>
				<td><select tabindex="16" name="HoldReason" required="required">';

		$result=DB_query("SELECT reasoncode, reasondescription FROM holdreasons");
		if (DB_num_rows($result)==0){
			$DataError =1;
			echo '<tr>
					<td colspan="2">' . prnMsg(_('There are no credit statuses currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
				</tr>';
		} else {
			while ($myrow = DB_fetch_array($result)) {
				echo '<option value="'. $myrow['reasoncode'] . '">' . $myrow['reasondescription'] . '</option>';
			} //end while loop
			DB_data_seek($result,0);
			echo '</select></td></tr>';
		}

		$result=DB_query("SELECT currency, currabrev FROM currencies");
		if (DB_num_rows($result)==0){
			$DataError =1;
			echo '<tr>
					<td colspan="2">' . prnMsg(_('There are no currencies currently defined - go to the setup tab of the main menu and set at least one up first'),'error') . '</td>
				</tr>';
		} else {
			if (!isset($_POST['CurrCode'])){
				$CurrResult = DB_query("SELECT currencydefault FROM companies WHERE coycode=1");
				$myrow = DB_fetch_row($CurrResult);
				$_POST['CurrCode'] = $myrow[0];
			}
			echo '<tr>
					<td>' . _('Customer Currency') . ':</td>
					<td><select tabindex="17" name="CurrCode" required="required">';
			while ($myrow = DB_fetch_array($result)) {
				if ($_POST['CurrCode']==$myrow['currabrev']){
					echo '<option selected="selected" value="'. $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
				} else {
					echo '<option value="'. $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
				}
			} //end while loop
			DB_data_seek($result,0);

			echo '</select></td>
				</tr>';
		}

		echo '<tr>
				<td>' . _('Language') . ':</td>
				<td><select name="LanguageID" required="required">';

		if (!isset($_POST['LanguageID']) OR $_POST['LanguageID']==''){
			$_POST['LanguageID']=$_SESSION['Language'];
		}

		foreach ($LanguagesArray as $LanguageCode => $LanguageName){
			if ($_POST['LanguageID'] == $LanguageCode){
				echo '<option selected="selected" value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			} else {
				echo '<option value="' . $LanguageCode . '">' . $LanguageName['LanguageName']  . '</option>';
			}
		}
		echo '</select></td>
				</tr>';
		echo'<tr>
				<td>备注:</td>
				<td> <textarea  placeholder="在这里输入备注内容..."  cols="45" rows="3" name="Remark" ></textarea></td>
			</tr>';
			
		echo'<tr>
			<td>状态:</td>
			<td><select name="Status" >';
		foreach($status as $key=>$val){	
			if (isset($_POST['Status'])&&$_POST['Status']==$key){
				echo '<option selected="selected" value="'.$key.'">'.$val.'</option>
					';
			}else{
				echo '<option  value="'.$key.'">'.$val.'</option>';
			}
		}
		echo '</select></td>
					</tr>';
echo'</table>';	

		echo '<br />
			<div class="centre">
				<input type="submit" name="Update" value="' . _('Update Customer') . '" />&nbsp;
				<input type="submit" name="delete" value="' . _('Delete Customer') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
			</div>';
	echo '</div></form>';
}

include('includes/footer.php');
/*				<td><input ' . (in_array('Address5',$Errors) ?  'class="inputerror"' : '' ) .' type="text" name="Address5" size="42" maxlength="40" value="' . $_POST['Address5'] . '" /></td>
			$result=DB_query("SELECT terms FROM paymentterms WHERE termsindicator='".$_POST['PaymentTerms']."'");
	$result=DB_query("SELECT reasondescription FROM holdreasons WHERE reasoncode='".$_POST['HoldReason']."'");
	$sql = "SELECT 			FROM custcontacts
			
*/	
?>
