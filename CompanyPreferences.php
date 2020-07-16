<?php

/* $Id: CompanyPreferences.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-11-21 20:46:54 
 * @Last Modified by:   ChengJiang 
 * @Last Modified time: 2018-11-21 20:46:54 
 */
include('includes/session.php');

$Title ='公司维护';// _('Company Preferences');
/* webERP manual links before header.php */
$ViewTopic= 'CreatingNewSystem';
$BookMark = 'CompanyParameters';
include('includes/header.php');

if (isset($Errors)) {
	unset($Errors);
}

//initialise no input errors assumed initially before we test
$InputError = 0;
$Errors = array();
$i=1;

if (isset($_POST['submit'])) {


	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (mb_strlen($_POST['CoyName']) > 50 OR mb_strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		prnMsg(_('The company name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		$i++;
	}

	if (mb_strlen($_POST['Email'])>0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}

	if ($InputError !=1){

		$sql = "UPDATE companies SET coyname='" . $_POST['CoyName'] . "',
									companynumber = '" . $_POST['CompanyNumber'] . "',
									gstno='" . $_POST['GSTNo'] . "',
									regoffice1='" . $_POST['RegOffice1'] . "',
									regoffice2='" . $_POST['RegOffice2'] . "',
									regoffice3='" . $_POST['RegOffice3'] . "',
									regoffice4='" . $_POST['RegOffice4'] . "',
									regoffice5='" . $_POST['RegOffice5'] . "',
									regoffice6='" . $_POST['RegOffice6'] . "',
									telephone='" . $_POST['Telephone'] . "',
									fax='" . $_POST['Fax'] . "',
									email='" . $_POST['Email'] . "',
									currencydefault='" . $_POST['CurrencyDefault'] . "',
									debtorsact='" . $_POST['DebtorsAct'] . "',
									pytdiscountact='" . $_POST['PytDiscountAct'] . "',
									creditorsact='" . $_POST['CreditorsAct'] . "',
									payrollact='" . $_POST['PayrollAct'] . "',
									grnact='" . $_POST['GRNAct'] . "'	,
									lastsettleperiod='" . $_POST['ERPPrd'] . "'	,
									defaulttaxcategory='".$_POST['DefaultTaxCategory']."',
									gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
									gllink_creditors='" . $_POST['GLLink_Creditors'] . "'
															
								WHERE coycode=1";
								/*exchangediffact='" . $_POST['ExchangeDiffAct'] . "',
									purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
									retainedearnings='" . $_POST['RetainedEarnings'] . "',
									gllink_stock='" . $_POST['GLLink_Stock'] ."'	
									*/
                      
			$ErrMsg =  _('The company preferences could not be updated because');
			$result = DB_query($sql,$ErrMsg);
			prnMsg( _('Company preferences updated'),'success');

			/* Alter the exchange rates in the currencies table */

			/* Get default currency rate */
			$sql="SELECT rate from currencies WHERE currabrev='" . $_POST['CurrencyDefault'] . "'";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
			$NewCurrencyRate=$myrow[0];

			/* Set new rates */
			$sql="UPDATE currencies SET rate=rate/" . $NewCurrencyRate;
			$ErrMsg =  _('Could not update the currency rates');
			$result = DB_query($sql,$ErrMsg);

			/* End of update currencies */

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include('includes/GetConfig.php');
			$ForceConfigReload = False;

	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $Title . '</p>';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

if ($InputError != 1) {
	$sql = "SELECT	`coycode`,
					`coyname`,
					`gstno`,
					`companynumber`,
					`regoffice1`,
					`regoffice2`,
					`regoffice3`,
					`regoffice4`,
					`regoffice5`,
					`regoffice6`,
					`telephone`,
					`fax`,
					`email`,
					`currencydefault`,
					`debtorsact`,
					`pytdiscountact`,
					`creditorsact`,
					`payrollact`,
					`grnact`,					
					`lastsettleperiod`,
					`settle`,					
					`dbase`,
					`taxtype`,
					`unitstab`,
					 defaulttaxcategory,
                    
					`exchangediffact`,
					`purchasesexchangediffact`,
					`retainedearnings`,
					`gllink_debtors`,
					`gllink_creditors`,
					`gllink_stock`,
					`freightact`
			FROM companies
					WHERE coycode=1";

	$ErrMsg =  _('The company preferences could not be retrieved because');
	$result = DB_query($sql,$ErrMsg);


	$myrow = DB_fetch_array($result);

	$_POST['CoyName'] = $myrow['coyname'];
	$_POST['GSTNo'] = $myrow['gstno'];
	$_POST['CompanyNumber']  = $myrow['companynumber'];
	$_POST['RegOffice1']  = $myrow['regoffice1'];
	$_POST['RegOffice2']  = $myrow['regoffice2'];
	$_POST['RegOffice3']  = $myrow['regoffice3'];
	$_POST['RegOffice4']  = $myrow['regoffice4'];
	$_POST['RegOffice5']  = $myrow['regoffice5'];
	$_POST['RegOffice6']  = $myrow['regoffice6'];
	$_POST['Telephone']  = $myrow['telephone'];
	$_POST['Fax']  = $myrow['fax'];
	$_POST['Email']  = $myrow['email'];
	$_POST['CurrencyDefault']  = $myrow['currencydefault'];
	$_POST['DebtorsAct']  = $myrow['debtorsact'];
	$_POST['PytDiscountAct']  = $myrow['pytdiscountact'];
	$_POST['CreditorsAct']  = $myrow['creditorsact'];
	$_POST['PayrollAct']  = $myrow['payrollact'];
	$_POST['GRNAct'] = $myrow['grnact'];
	$_POST['DefaultTaxCategory'] = $myrow['defaulttaxcategory'];
	$_POST['ERPPrd']=$myrow['lastsettleperiod'];	
	$_POST['GLLink_Debtors'] = $myrow['gllink_debtors'];
	$_POST['GLLink_Creditors'] = $myrow['gllink_creditors'];		

	/*_POST['ExchangeDiffAct']  = $myrow['exchangediffact'];
	$_POST['PurchasesExchangeDiffAct']  = $myrow['purchasesexchangediffact'];
	$_POST['RetainedEarnings'] = $myrow['retainedearnings'];

	$_POST['GLLink_Stock'] = $myrow['gllink_stock'];*/
}

echo '<tr>
		<td>公司名称:</td>
		<td><input '.(in_array('CoyName',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="1" type="text" autofocus="autofocus" required="required" name="CoyName" value="' . stripslashes($_POST['CoyName']) . '"  pattern="?!^ +$"  title="' . _('Enter the name of the business. This will appear on all reports and at the top of each screen. ') . '" size="52" maxlength="50" /></td>
	</tr>';

echo '<tr>
		<td>统一信用代码:</td>
		<td><input '.(in_array('CoyNumber',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="2" type="text" name="CompanyNumber" value="' . $_POST['CompanyNumber'] . '" size="22" maxlength="20" /></td>
	</tr>';

echo '<tr>
		<td>基本账号:</td>
		<td><input '.(in_array('TaxRef',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="3" type="text" name="GSTNo" value="' . $_POST['GSTNo'] . '" size="22" maxlength="20" />开户行
		<input '.(in_array('RegOffice6',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="4" type="text" name="RegOffice6" size="17" maxlength="15" value="' . stripslashes($_POST['RegOffice6']) . '" /></td>
	</tr>';
echo '<tr>
	<td>一般账户:</td>
	<td><input '.(in_array('RegOffice4',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="5" type="text" name="RegOffice4" title="' . _('Enter the fourth line of the company registered office. This will appear on invoices and statements.') . '" size="22" maxlength="20" value="' . stripslashes($_POST['RegOffice4']) . '" />
     开户行<input '.(in_array('RegOffice5',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="6" type="text" name="RegOffice5" size="17" maxlength="15" value="' . stripslashes($_POST['RegOffice5']) . '" /></td>
</tr>';


echo '<tr>
		<td>' . _('Address Line 1') . ':</td>
		<td><input '.(in_array('RegOffice1',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="7" type="text" name="RegOffice1" title="' . _('Enter the first line of the company registered office. This will appear on invoices and statements.') . '" required="required" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice1']) . '" /></td>
	</tr>';

echo '<tr>
		<td>法定代表人:</td>
		<td><input '.(in_array('RegOffice2',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="5" type="text" name="RegOffice2" title="' . _('Enter the second line of the company registered office. This will appear on invoices and statements.') . '" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice2']) . '" /></td>
	</tr>';

echo '<tr>
		<td>财务负责人:</td>
		<td><input '.(in_array('RegOffice3',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="6" type="text" name="RegOffice3" title="' . _('Enter the third line of the company registered office. This will appear on invoices and statements.') . '" size="42" maxlength="40" value="' . stripslashes($_POST['RegOffice3']) . '" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Telephone Number') . ':</td>
		<td><input '.(in_array('Telephone',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="10" type="tel" name="Telephone" required="required" title="' . _('Enter the main telephone number of the company registered office. This will appear on invoices and statements.') . '" size="26" maxlength="25" value="' . $_POST['Telephone'] . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Facsimile Number') . ':</td>
		<td><input '.(in_array('Fax',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="11" type="text" name="Fax" size="26" maxlength="25" value="' . $_POST['Fax'] . '" /></td>
	</tr>';

echo '<tr>
		<td>' . _('Email Address') . ':</td>
		<td><input '.(in_array('Email',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="12" type="email" name="Email" title="' . _('Enter the main company email address. This will appear on invoices and statements.') . '" required="required" placeholder="accounts@example.com" size="50" maxlength="55" value="' . $_POST['Email'] . '" /></td>
	</tr>';


$result=DB_query("SELECT currabrev, currency FROM currencies");
include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.

echo '<tr>
		<td>' . _('Home Currency') . ':</td>
		<td><select tabindex="13" name="CurrencyDefault">';

while ($myrow = DB_fetch_array($result)) {
	if ($_POST['CurrencyDefault']==$myrow['currabrev']){
		echo '<option selected="selected" value="'. $myrow['currabrev'] . '">' . $CurrencyName[$myrow['currabrev']] . '</option>';
	} else {
		echo '<option value="' . $myrow['currabrev'] . '">' . $CurrencyName[$myrow['currabrev']] . '</option>';
	}
} //end while loop

DB_free_result($result);

echo '</select></td>
	</tr>';
	$sql = "SELECT `taxcatid`, `taxcatname`, `taxglcode`,  `taxrate`  FROM `taxcategories` WHERE taxcatid<=4";
	//SELECT `taxid` taxcatid, `description` taxcatname,taxrate  FROM `taxauthorities` WHERE onorder IN (1,3) ORDER BY description";

	$ErrMsg = _('Could not load tax categories table');
	$Result = DB_query($sql,$ErrMsg);
	echo '<tr>
	      <td>' . _('Default Tax Category') . ':</td>';
	echo '<td><select name="DefaultTaxCategory">';
	
		while( $row = DB_fetch_array($Result) ) {
			echo '<option '.($_SESSION['DefaultTaxCategory'] == $row['taxcatid']?'selected="selected" ':'').'value="'.$row['taxcatid'].'">' . $row['taxcatname'] . '</option>';
		}
	
	echo '</select></td>
		</tr>';// . _('This is the tax category used for entry of supplier invoices and the category at which freight attracts tax')  . '</td></tr>';
  echo '<tr>
		<td>结账期间:</td>';
  echo '<td>';
  SelectPeriod($_SESSION['CompanyRecord'][1]['lastsettleperiod'],$_SESSION['janr']); 
  echo '</td>
	  </tr>';// .	
$result=DB_query("SELECT accountcode,
						accountname
					FROM chartmaster 
					ORDER BY chartmaster.accountcode");

echo '<tr>
		<td>' . _('Debtors Control GL Account') . ':</td>
		<td><select tabindex="14" title="' . _('Select the general ledger account to be used for posting the local currency value of all customer transactions to. This account will always represent the total amount owed by customers to the business. Only balance sheet accounts are available for this selection.') . '" name="DebtorsAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,4)=='1122'){
		if ($_POST['DebtorsAct']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
    }
} //end while loop

DB_data_seek($result,0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Creditors Control GL Account') . ':</td>
		<td><select tabindex="15" title="' . _('Select the general ledger account to be used for posting the local currency value of all supplier transactions to. This account will always represent the total amount owed by the business to suppliers. Only balance sheet accounts are available for this selection.') . '" name="CreditorsAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,4)=='2202'){
		if ($_POST['CreditorsAct']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option value="' . $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
    }
} //end while loop

DB_data_seek($result,0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>' . _('Payroll Net Pay Clearing GL Account') . ':</td>
		<td><select tabindex="16" name="PayrollAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,4)=='2211'){
		if ($_POST['PayrollAct']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
    }
} //end while loop

DB_data_seek($result,0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>存货总账科目:</td>
		<td><select title="' . _('Select the general ledger account to be used for posting the cost of goods received pending the entry of supplier invoices for the goods. This account will represent the value of goods received yet to be invoiced by suppliers. Only balance sheet accounts are available for this selection.') . '" tabindex="17" name="GRNAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,2)=='14'){
		if ($_POST['GRNAct']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
	}
} //end while loop

DB_data_seek($result,0);
echo '</select></td>
	</tr>';

echo '<tr>
		<td>利润总账科目:</td>
		<td><select title="' . _('Select the general ledger account to be used for clearing profit and loss accounts to that represents the accumulated retained profits of the business. Only balance sheet accounts are available for this selection.') . '" tabindex="18" name="RetainedEarnings">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,4)=='4103'){
	if ($_POST['RetainedEarnings']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
    }
} //end while loop

DB_free_result($result);

echo '</select></td>
	</tr>';
/*
echo '<tr>
		<td>销售费用科目:</td>
		<td><select tabindex="19" name="CellingCosts">';

$result=DB_query("SELECT accountcode,
						accountname
					FROM chartmaster 
					ORDER BY chartmaster.accountcode");

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,4)=='6601'){
	if ($_POST['CellingCosts']==$myrow[0]){

		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
    }
} //end while loop

DB_data_seek($result,0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>管理费用科目:</td>
		<td><select title="' . _('Select the general ledger account to be used for posting accounts receivable exchange rate differences to - where the exchange rate on sales invocies is different to the exchange rate of currency receipts from customers, the exchange rate is calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.') . '" tabindex="20" name="ExpensesAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,4)=='6602'){
	if ($_POST['ExpensesAct']==$myrow[0]){
		echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	} else {
		echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
	}
    }
} //end while loop

DB_data_seek($result,0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>财务费用科目:</td>
		<td><select tabindex="21" title="' . _('Select the general ledger account to be used for posting the exchange differences on the accounts payable transactions to. Supplier invoices entered at one currency and paid in the supplier currency at a different exchange rate have the differences calculated automatically and posted to this general ledger account. Only profit and loss general ledger accounts are available for this selection.') . '" name="FinancialCost">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,4)=='6603'){
		if ($_POST['FinancialCost']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option  value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
    }
} //end while loop

DB_data_seek($result,0);

echo '</select></td>
	</tr>';

echo '<tr>
		<td>销项税科目:</td>
		<td><select title="' . _('Select the general ledger account to be used for posting the value of payment discounts given to customers at the time of entering a receipt. Only profit and loss general ledger accounts are available for this selection.') . '" tabindex="22" name="SalesTaxAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,6)=='222101'){
		if ($_POST['SalesTaxAct']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
	}
} //end while loop

DB_data_seek($result,0);

echo '</select></td>
	</tr>';
	echo '<tr>
		<td>进项税科目:</td>
		<td><select title="' . _('Select the general ledger account to be used for posting the value of payment discounts given to customers at the time of entering a receipt. Only profit and loss general ledger accounts are available for this selection.') . '" tabindex="22" name="PurchaseTaxAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,6)=='222101'){
		if ($_POST['PurchaseTaxAct']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
	}
} //end while loop

DB_data_seek($result,0);
echo '</select></td>
	</tr>';
	echo '<tr>
		<td>已交增值税:</td>
		<td><select title="' . _('Select the general ledger account to be used for posting the value of payment discounts given to customers at the time of entering a receipt. Only profit and loss general ledger accounts are available for this selection.') . '" tabindex="22" name="VATpaidAct">';

while ($myrow = DB_fetch_row($result)) {
	if (substr($myrow[0],0,6)=='222101'){
		if ($_POST['VATpaidAct']==$myrow[0]){
			echo '<option selected="selected" value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		} else {
			echo '<option value="'. $myrow[0] . '">' . htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8') . ' ('.$myrow[0].')</option>';
		}
	}
} //end while loop
echo '</select></td>
	</tr>';

*/
echo '<tr>
		<td>销售对账凭证生成:</td>
		<td><select title="销售对账单生成凭证及发票关联及差异凭证" tabindex="23" name="GLLink_Debtors">';

if ($_POST['GLLink_Debtors']==0){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes'). '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes'). '</option>';
	echo '<option value="0">' . _('No'). '</option>';
}

echo '</select></td>
	</tr>';

echo '<tr>
		<td>采购对账生成凭证:</td>
		<td><select title="采购对账单生成凭证及发票关联及差异凭证" tabindex="24" name="GLLink_Creditors">';

if ($_POST['GLLink_Creditors']==0){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}

echo '</select></td>
	</tr>';
/*
echo '<tr>
		<td>对账单审核:</td>
		<td><select title="" tabindex="25" name="GLLink_Stock">';

if ($_POST['GLLink_Stock']=='0'){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}
*/
echo '</select></td>
	</tr>';
echo '</table>
	<br />
	<div class="centre">
		<input tabindex="26" type="submit" name="submit" value="' . _('Update') . '" />
	</div>';
echo '</div></form>';

include('includes/footer.php');
?>
