
<?php

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:58
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-04-16 08:25:10
 */

include('includes/session.php');
$Title = _('Supplier Maintenance');

$ViewTopic= 'AccountsPayable';
$BookMark = 'NewSupplier';
include('includes/header.php');

include('includes/SQL_CommonFunctions.inc');
//include('includes/GLAccountFunction.php');
include('includes/GLSubject.php');
include('includes/CountriesArray.php');
if (!isset($_POST['Status'])){
	$_POST['Status']=0;
}
$status=array("0"=>"启用","-1"=>"停用");
if(!isset($_POST['Address6'])){
	$_POST['Address6']=$_SESSION['CountryOfOperation'];
}

Function Is_ValidAccount ($ActNo) {

	if (mb_strlen($ActNo) < 16) {
		echo _('NZ account numbers must have 16 numeric characters in it');
		return False;
	}

	if (!Is_double((double) $ActNo)) {
		echo _('NZ account numbers entered must use all numeric characters in it');
		return False;
	}

	$BankPrefix = mb_substr($ActNo,0, 2);
	$BranchNumber = (int) (mb_substr($ActNo, 3, 4));

	if ($BankPrefix == '29') {
		echo _('NZ Accounts codes with the United Bank are not verified') . ', ' . _('be careful to enter the correct account number');
		exit;
	}

	//Verify correct branch details

	switch ($BankPrefix) {

	case '01':
		if (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1100 and $BranchNumber <= 1199))) {
		echo _('ANZ branches must be between 0001 and 0999 or between 1100 and 1199') . '. ' . _('The branch number used is invalid');
		return False;
		}
		break;
	case '02':
		If (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1200 and $BranchNumber <= 1299))) {
		echo _('Bank Of New Zealand branches must be between 0001 and 0999 or between 1200 and 1299') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;
	case '03':
	if (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1300 and $BranchNumber <= 1399))) {
		echo _('Westpac Trust branches must be between 0001 and 0999 or between 1300 and 1399') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;

	case '06':
		if (!(($BranchNumber >= 1 and $BranchNumber <= 999) or ($BranchNumber >= 1400 and $BranchNumber <= 1499))) {
			echo _('National Bank branches must be between 0001 and 0999 or between 1400 and 1499') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;

	case '08':
	if (!($BranchNumber >= 6500 and $BranchNumber <= 6599)) {
		echo _('National Australia branches must be between 6500 and 6599') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
		}
		break;
	case '09':
		if ($BranchNumber != 0) {
			echo _('The Reserve Bank branch should be 0000') . '. ' . _('The branch number used is invalid');
			return False;
			exit;
		}
		break;
	case '12':

	//"13" "14" "15", "16", "17", "18", "19", "20", "21", "22", "23", "24":

	if (!($BranchNumber >= 3000 and $BranchNumber <= 4999)) {
		echo _('Trust Bank and Regional Bank branches must be between 3000 and 4999') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '11':
	if (!($BranchNumber >= 5000 and $BranchNumber <= 6499)) {
		echo _('Post Office Bank branches must be between 5000 and 6499') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '25':
	if (!($BranchNumber >= 2500 and $BranchNumber <= 2599)) {
		echo _('Countrywide Bank branches must be between 2500 and 2599') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;
	case '29':
	if (!($BranchNumber >= 2150 and $BranchNumber <= 2299)) {
		echo _('United Bank branches must be between 2150 and 2299') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '30':
	if (!($BranchNumber >= 2900 and $BranchNumber <= 2949)) {
		echo _('Hong Kong and Shanghai branches must be between 2900 and 2949') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '31':
	if (!($BranchNumber >= 2800 and $BranchNumber <= 2849)) {
		echo _('Citibank NA branches must be between 2800 and 2849') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	case '33':
	if (!($BranchNumber >= 6700 and $BranchNumber <= 6799)) {
		echo _('Rural Bank branches must be between 6700 and 6799') . '. ' . _('The branch number used is invalid');
		return False;
		exit;
	}
		break;

	default:
	echo _('The prefix') . ' - ' . $BankPrefix . ' ' . _('is not a valid New Zealand Bank') . '.<br />' .
			_('If you are using webERP outside New Zealand error trapping relevant to your country should be used');
	return False;
	exit;

	} // end of first Bank prefix switch

	for ($i=3; $i<=14; $i++) {

	$DigitVal = (double)(mb_substr($ActNo, $i, 1));

	switch ($i) {
	case 3:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 6);
		}
		break;

	case 4:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 3);
		}
		break;

	case 5:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 7);
		}
		break;

	case 6:
		if ($BankPrefix == '08' or $BankPrefix == '09' or $BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = 0;
		} else {
			$CheckSum = $CheckSum + ($DigitVal * 9);
		}
		break;

	case 7:
		if ($BankPrefix == '08') {
			$CheckSum = $CheckSum + $DigitVal * 7;
		} elseif ($BankPrefix == '25' Or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 1;
		}
		break;

	case 8:
		if ($BankPrefix == '08') {
			$CheckSum = $CheckSum + ($DigitVal * 6);
		} elseif ($BankPrefix == '09') {
			$CheckSum = 0;
		} elseif ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 7;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 10;
		}
		break;

	case 9:
		if ($BankPrefix == '09') {
			$CheckSum = 0;
		} elseif ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 3;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 5;
		}
		break;

	case 10:
		if ($BankPrefix == '08') {
			$CheckSum = $CheckSum + $DigitVal * 4;
		} elseif ($BankPrefix == '09') {
			If (($DigitVal * 5) > 9) {
				$CheckSum = $CheckSum + (int) mb_substr((string)($DigitVal * 5),0,1) + (int) mb_substr((string)($DigitVal * 5),mb_strlen((string)($DigitVal *5))-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 5;
			}
		} elseif ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 8;
		}
		break;

	case 11:
		if ($BankPrefix == '08') {
			$CheckSum = $CheckSum + $DigitVal * 3;
		} elseif ($BankPrefix == '09') {
			if (($DigitVal * 4) > 9) {
				$CheckSum = $CheckSum + (int) mb_substr(($DigitVal * 4),0,1) + (int)mb_substr(($DigitVal * 4),mb_strlen($DigitVal * 4)-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 4;
			}
		} elseif ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 7;
		} else {
			$CheckSum = $CheckSum + $DigitVal * 4;
		}
		break;

	case 12:
		if ($BankPrefix == '25' or $BankPrefix == '33') {
			$CheckSum = $CheckSum + $DigitVal * 3;
		} elseif ($BankPrefix == '09') {
			if (($DigitVal * 3) > 9) {
				$CheckSum = $CheckSum + (int) mb_substr(($DigitVal * 3),0,1) + (int) mb_substr(($DigitVal * 3),mb_strlen($DigitVal * 3)-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 3;
			}
		} else {
			$CheckSum = $CheckSum + $DigitVal * 2;
		}
		break;

	case 13:
		if ($BankPrefix == '09') {
			If (($DigitVal * 2) > 9) {
				$CheckSum = $CheckSum + (int) mb_substr(($DigitVal * 2),0,1) + (int) mb_substr(($DigitVal * 2),mb_strlen($DigitVal * 2)-1, 1);
			} else {
				$CheckSum = $CheckSum + $DigitVal * 2;
			}
		} else {
			$CheckSum = $CheckSum + $DigitVal;
		}
		break;

	case 14:
		if ($BankPrefix == '09') {
			$CheckSum = $CheckSum + $DigitVal;
		}
	break;
	} //end switch

	} //end for loop

	if ($BankPrefix == '25' or $BankPrefix == '33') {
		if ($CheckSum / 10 - (int)($CheckSum / 10) != 0) {
			echo '<p>' . _('The account number entered does not meet the banking check sum requirement and cannot be a valid account number');
			return False;
		}
	} else {
		if ($CheckSum / 11 - (int)($CheckSum / 11) != 0) {
			echo '<p>' . _('The account number entered does not meet the banking check sum requirement and cannot be a valid account number');
			return False;
		}
	}

} //End Function


if (isset($_GET['SupplierID'])) {
	$SupplierID = mb_strtoupper($_GET['SupplierID']);
} elseif (isset($_POST['SupplierID'])) {
	$SupplierID = mb_strtoupper($_POST['SupplierID']);
} else {
	unset($SupplierID);
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Suppliers') . '</p>';
/*if (isset($SupplierID)) {
	echo '<p>
			<a href="' . $RootPath . '/SupplierContacts.php?SupplierID=' . $SupplierID . '">' . _('Review Supplier Contact Details') . '</a>
		</p>';
}*/
$InputError = 0;

if (isset($Errors)) {
	unset($Errors);
}
$Errors=Array();
if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$i=1; 

	if (mb_strlen(trim($_POST['CustName'])) > 40
		OR mb_strlen(trim($_POST['CustName'])) == 0
		OR trim($_POST['CustName']) == '') {

		$InputError = 1;
		prnMsg(_('The supplier name must be entered and be forty characters or less long'),'error');
		$Errors[$i]='Name';
		$i++;
	}
	if ($InputError!=1){
		//检查供应商名是否存在于系统
		$SQL="SELECT `regid`, 
		             `custname`,
					 registerno,
					 bankaccount,
					 `sub`,
					 `regdate`,
					  `acctype`,
					  `tag`
			   FROM `register_account_sub`";
		$where=" WHERE ";	   
		if ($_POST['CustName']!=""){
			$SQL.=$where." custname='".$_POST['CustName']."'";
			$where=" OR ";
		}
		if ($_POST['Address5']!=""){
		    $SQL.=$where." registerno='".$_POST['Address5']."'"; 
		    $where=" OR ";
		}
		 if ($_POST['BankAct']!=""){
		    $SQL.=$where." bankaccount='".$_POST['BankAct']."'";
		    $where=" OR ";
		}
		$Result=DB_query($SQL);
		//prnMsg($SQL);
		$Row=DB_fetch_assoc($Result);
		$Error=0;
		
		if (isset($Row) && isset($_POST['New'])){
		
			if ($_POST['CustName']!='' && $Row['custname']==$_POST['CustName']){
				//输入名和系统已经存在  提示错误停止
				//prnMsg($Row['regid'].$Row['custname'].'-'.$_POST['New']);	
				/*if ($Row['sub']!=''&&$Error==0){
					$InputError = 1;
					//prnMsg(
						$msg.='你添加的供应商'.$_POST['CustName'].'已经添加,编码:'.$Row['regid'].' 会计科目:'.$Row['sub'].'<br>';//,'success');
					//	$ReadCustData=$Row;
					$_POST['RegID']=$Row['regid'];
					$_POST['CustName']=$Row['custname'];
					$_POST['Address5']=$Row['registerno'];
					$_POST['BankAct']=$RowT['bankaccount'];
					$_POST['Account']=$Row['sub'];
					
					
				}*/
				$SQL="SELECT count(*) FROM `suppliers` WHERE supplierid='".$Row['regid']."'";
				$Result=DB_query($SQL);
				$SupRow = DB_fetch_row($Result);
				if ($SupRow[0]>0){				
				
					$msg.='供应商:['.$Row['regid'].']'.$_POST['CustName'].'已经存在于系统，你查询不到可能没有授权给你！';
					$InputError = 1;
					$Errors[$i] = 'CustName';
					$i++;
					$_POST['RegID']=$Row['regid'];
					$_POST['CustName']=$Row['custname'];
					$_POST['Address5']=$Row['registerno'];
					$_POST['BankAct']=$RowT['bankaccount'];
					$_POST['Account']=$Row['sub'];
				}
				//return array(0=>-1);
			}
			if ($_POST['Address5']!='' && $Row['registerno']!=$_POST['Address5']){
				//输入注册码和系统码不一样  ,提示错误停止
				$InputError = 1;
				$Error=-2;
				$msg.='注册码:'.$_POST['Address5'].'和系统存在的注册码不同,';
			//	return array(0=>-2);
			}
			
		}
	
	
	}
	/*
	if (ContainsIllegalCharacters($SupplierID)) {
		$InputError = 1;
		prnMsg(_('The supplier code cannot contain any of the illegal characters') ,'error');
		$Errors[$i]='ID';
		$i++;
	}*/
	if (mb_strlen($_POST['Phone']) >25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Telephone';
		$i++;
	}
	if (mb_strlen($_POST['Fax']) >25) {
		$InputError = 1;
		prnMsg(_('The fax number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Fax';
		$i++;
	}
	if (mb_strlen($_POST['Email']) >55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}
	if (mb_strlen($_POST['Email'])>0 AND !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}
		if (mb_strlen($_POST['URL']) >50) {
		$InputError = 1;
		prnMsg(_('The URL address must be 50 characters or less long'),'error');
		$Errors[$i] = 'URL';
		$i++;
	}

	if (!Is_Date($_POST['SupplierSince'])) {
		$InputError = 1;
		prnMsg(_('The supplier since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i]='SupplierSince';
		$i++;
	}	
	if ($InputError != 1) {

		$SQL_SupplierSince = FormatDateForSQL($_POST['SupplierSince']);

		$latitude = 0;
		$longitude = 0;
		if ($_SESSION['geocode_integration']==1 ) {
			// Get the lat/long from our geocoding host
			$sql = "SELECT * FROM geocode_param WHERE 1";
			$ErrMsg = _('An error occurred in retrieving the information');
			$resultgeo = DB_query($sql, $ErrMsg);
			$row = DB_fetch_array($resultgeo);
			$api_key = $row['geocode_key'];
			$map_host = $row['map_host'];
			define('MAPS_HOST', $map_host);
			define('KEY', $api_key);
			// check that some sane values are setup already in geocode tables, if not skip the geocoding but add the record anyway.
			if ($map_host=="") {
			echo '<div class="warn">' . _('Warning - Geocode Integration is enabled, but no hosts are setup.  Go to Geocode Setup') . '</div>';
			} else {
			$address = $_POST['Address1'] . ', ' . $_POST['Address2'] . ', ' . $_POST['BankPartics'] . ', ' . $_POST['BankAct'] . ', ' . $_POST['Address5']. ', ' . $_POST['Address6'];

			$base_url = 'http://' . MAPS_HOST . '/maps/geo?output=xml' . '&key=' . KEY;
			$request_url = $base_url . '&q=' . urlencode($address);

			$xml = simplexml_load_string(utf8_encode(file_get_contents($request_url))) or prnMsg(_('Goole map url not loading'),'warn');
			$coordinates = $xml->Response->Placemark->Point->coordinates;
			$coordinatesSplit = explode(',', $coordinates);
			// Format: Longitude, Latitude, Altitude
			$latitude = $coordinatesSplit[1];
			$longitude = $coordinatesSplit[0];

			$status = $xml->Response->Status->code;
			if (strcmp($status, '200') == 0) {
			// Successful geocode
				$geocode_pending = false;
				$coordinates = $xml->Response->Placemark->Point->coordinates;
				$coordinatesSplit = explode(",", $coordinates);
				// Format: Longitude, Latitude, Altitude
				$latitude = $coordinatesSplit[1];
				$longitude = $coordinatesSplit[0];
			} else {
			// failure to geocode
				$geocode_pending = false;
				echo '<p>' . _('Address') . ': ' . $address . ' ' . _('failed to geocode') ."\n";
				echo _('Received status') . ' ' . $status . "\n" . '</p>';
			}
			}
		}
		//its a new supplier
			//添加供应商
			/*
			if(isset($SuppData)){  //客户名存在系统,没有科目
				//1.添加科目 2.写入 系统表和供应商表  3.写入读取权限
				$CustData=$_POST['CustName'].'^0^'.$_SESSION['CompanyRecord']['tag'].'^2^^'.$_POST['BankAct'].'^'.$_POST['Address5'];
		
               $AccountArr=AddCustomer($CustData,'2202','');

			}else{
				*/
				//供应商名不在
				//1.添加科目 2.写入系统表 和供应商表   3.写入读取权限
				//$SuooData{ 0=custname 1=>regid 2=>tag 3=>flg
				//	4=>costitem 5=> bankaccount,6=>regierno
				$CustData=$_POST['CustName'].'^'.$_POST['RegID'].'^'.$_SESSION['CompanyRecord']['tag'].'^2^^'.$_POST['BankAct'].'^'.$_POST['Address5'];
				$AccountArr=AddCustomer($CustData,'2202','',0);		
			   // var_dump($AccountArr);
		        if ($AccountArr[0]>0){
					$result=DB_Txn_Begin();
					$sql = "INSERT INTO suppliers(supplierid,
												suppname,
												address1,
												address2,											
												address5,
												address6,
												telephone,
												fax,
												email,
												url,

												supptype,
												currcode,
												suppliersince,
												paymentterms,
												bankpartics,									
												bankact,
												remittance,
												taxcatid,
												taxrate,											
												factorcompanyid,

												lat,
												lng,
												taxref,
												userid,
												remark,
												contactname)
										VALUES ('".$AccountArr[2]."',
											'" . trim($_POST['CustName']) . "',
											'" . $_POST['Address1'] . "',
											'" . $_POST['Address2'] . "',										
											'" . $_POST['Address5'] . "',
											'" . $_POST['Address6'] . "',
											'" . $_POST['Phone'] . "',
											'" . $_POST['Fax'] . "',
											'" . $_POST['Email'] . "',
											'" . $_POST['URL'] . "',

											'".$_POST['SupplierType']."',
											'" . $_POST['CurrCode'] . "',
											'" . $SQL_SupplierSince . "',
											'" . $_POST['PaymentTerms'] . "',
											'" . $_POST['BankPartics'] . "',										
											'" . $_POST['BankAct'] . "',
											'" . $_POST['Remittance'] . "',
											'" . explode('^',$_POST['TaxCat'])[0] . "',
											'" . explode('^',$_POST['TaxCat'])[1] . "',
											'" . $_POST['FactorID'] . "',
											
											'" . $latitude ."',
											'" . $longitude ."',
											'" . $_POST['TaxRef'] . "',
											'" . $_SESSION['UserID'] . "',
											'" . $_POST['Remark'] . "',
											'" . $_POST['ContactName'] . "')";

					$ErrMsg = _('The supplier') . ' ' . $_POST['CustName'] . ' ' . _('could not be added because');
					$DbgMsg = _('The SQL that was used to insert the supplier but failed was');
					// prnMsg($sql);
					$result = DB_query($sql, $ErrMsg, $DbgMsg);		
					
                        $sql="INSERT IGNORE INTO `customerusers`(	`regid`,
															`userid`,
															`custype`,
															`canview`,
															`canupd`)
															VALUES(	'" . $AccountArr[2] . "',
															'".$_SESSION['UserID']."',
															2,
															1,
															1)";
							$result = DB_query($sql, $ErrMsg);
							$result=DB_Txn_Commit();

					}

					if ($AccountArr[0]>0 ){
						prnMsg(_('A new supplier for') . ' ' . $_POST['CustName'] . ' ' . _('has been added to the database'),'success');
					}else{
						prnMsg(_('A new supplier for') . ' ' . $_POST['CustName'] . ' 添加失败！['.$AccountArr[0]. ']','info');
				
					}
					/*
					echo '<p>
						<a href="' . $RootPath . '/SupplierContacts.php?SupplierID=' . $SupplierID . '">' . _('Review Supplier Contact Details') . '</a>
						</p>';
						*/
					unset($SupplierID);
					unset($_POST['SupplierID']);
					unset($_POST['OnEdit']);
					unset($_POST['CustName']);
					unset($_POST['Address1']);
					unset($_POST['Address2']);
					unset($_POST['BankPartics']);
					unset($_POST['BankAct']);
					unset($_POST['Address5']);
					unset($_POST['Address6']);
					unset($_POST['Phone']);
					unset($_POST['Fax']);
					unset($_POST['Email']);
					unset($_POST['URL']);
					unset($_POST['SupplierType']);
					unset($_POST['CurrCode']);
					unset($SQL_SupplierSince);
					unset($_POST['PaymentTerms']);
					unset($_POST['BankPartics']);
					unset($_POST['BankRef']);
					unset($_POST['BankAct']);
					unset($_POST['Remittance']);
					unset($_POST['TaxCat']);
					unset($_POST['BankAct']);
					unset($_POST['BankPartics']);
		
	} else {

		prnMsg($msg.'<br>'._('Validation failed') . _('no updates or deletes took place'),'warn');

	}

} elseif (isset($_POST['delete']) AND $_POST['delete'] != '') {

	//the link to delete a selected record was clicked instead of the submit button

	//$CancelDelete = 0;
    if ($_POST['OnEdit']>2) { 
			prnMsg('未能删除供应商记录,因为已经有该供应商的业务发生!','warn');
			//prnMsg(_('Cannot delete this supplier because there are supplier contacts set up against it') . ' - ' . _('delete these first'),'warn');
			//prnMsg(_('Cannot delete the supplier record because purchase orders have been created against this supplier'),'warn');
	}else{
			   if ((!isset($_POST['Account'])|| strlen($_POST['Account'])<4)&&($_POST['OnEdit']<=2)){
					$result=DB_Txn_Begin();	
					$sql="DELETE FROM `registeraccount` WHERE regid='" . $_POST['SupplierID'] . "'";
					$result = DB_query($sql,$ErrMsg);
					
					$sql="DELETE FROM `accountsubject` WHERE regid='" . $_POST['SupplierID'] . "'";
					$result = DB_query($sql);
					$sql="DELETE FROM `registername` WHERE regid='" . $_POST['SupplierID'] . "' 
						AND userid='".$_POST['UserID']."'";
					$result = DB_query($sql);
					$result=DB_Txn_Commit();
				}
				if($_POST['OnStock']<1){
					$sql="DELETE FROM `suppliers` WHERE supplierid='" . $_POST['AupplierID'] . "'";
					$result = DB_query($sql);
					$sql="DELETE FROM `customerusers` WHERE regid='" . $_POST['SupplierID'] . "'";
					$result = DB_query($sql);
				}
					prnMsg(_('Supplier record for') . ' ' . $SupplierID . ' ' . _('has been deleted'),'success');
			
				
					unset($_POST['SupplierID']);
					unset($_SESSION['SupplierID']);
					unset($_POST['RegID']);
					unset($_SESSION['DebtorArr']);
					unset($SupplierID);
					unset($_POST['OnEdit']);
					unset($_POST['CustName']);
					unset($_POST['Address1']);
					unset($_POST['Address2']);
					unset($_POST['BankPartics']);
					unset($_POST['BankAct']);
					unset($_POST['Address5']);
					unset($_POST['Address6']);
					unset($_POST['Phone']);
					unset($_POST['Fax']);
					unset($_POST['Email']);
					unset($_POST['URL']);
					unset($_POST['SupplierType']);
					unset($_POST['CurrCode']);
					unset($SQL_SupplierSince);
					unset($_POST['PaymentTerms']);
					unset($_POST['BankPartics']);
					unset($_POST['BankRef']);
					unset($_POST['BankAct']);
					unset($_POST['Remittance']);
					unset($_POST['TaxCat']);
					unset($_POST['BankAct']);
					unset($_POST['BankPartics']);
					include('includes/footer.php');
					 exit;
			
		} //end if Delete supplier
	
}


if (!isset($SupplierID)) {

	/*If the page was called without $SupplierID passed to page then assume a new supplier is to be entered show a form with a Supplier Code field other wise the form showing the fields with the existing entries against the supplier will show for editing with only a hidden SupplierID field*/

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="unittagID" value="' . $_POST['unittagID'] . '" />';

	echo '<input type="hidden" name="New" value="Yes" />';
	$SQL="SELECT`regid`,
				`registerno`,
				`custname`,
				`tag`,
				`sub`,
				`regdate`,
				`acctype`
			FROM `custname_reg_sub`
			WHERE regid NOT IN(	SELECT	`supplierid`	FROM	`suppliers`	)
				AND (acctype=2 OR acctype=3 OR acctype=0)
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
		$_POST['RegID']=0;
		if (!strpos($_POST['RegCustName'],':')){		
			//prnMsg($_POST['BankAct'].'=='.$_POST['RegisterNo']);
           
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
				
				prnMsg('你添加的供应商'.$_POST['RegCustName'].'已经添加,编码:'.$Row['regid'].' 会计科目:'.$Row['sub'],'info');
				//$CustData=$Row;
				$_POST['RegID']=$Row['regid'];
				$_POST['CustName']=$Row['custname'];
				$_POST['Address5']=$Row['registerno'];
				$_POST['BankAct']=$RowT['bankaccount'];			
			    $_POST['Account']=$Row['sub'];
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
				//系统已经存在单位,但不完善 ,如果$SuppData不存在,新单位
				//$CustData=$Row;
				$_POST['RegID']=$Row['regid'];
				$_POST['CustName']=$Row['custname'];
				$_POST['Address5']=$Row['registerno'];
				$_POST['BankAct']=$RowT['bankaccount'];
				$_POST['Account']=$Row['sub'];
			}
		}else {//新客户
			$_POST['CustName']=$_POST['RegCustName'];
			$_POST['Address5']=$_POST['RegisterNo'];
			$_POST['BankAct']=$_POST['BankAct'];
			
			// prnMsg(strlen($_POST['Address5']).'==596--'.$_POST['Address5']);
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
			//	}
			//if ( $_POST['RegID'] <=0|| isset($_POST['RegID'])){
			$sql= "SELECT COUNT(*) FROM supptrans WHERE supplierno='" . $_POST['RegID'] . "'";
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

	echo '<table class="selection">';

	/* if $AutoSupplierNo is off (not 0) then provide an input box for the SupplierID to manually assigned */
	/*
	if ($_SESSION['AutoSupplierNo']!=1 ) {

		echo '<tr><td>' . _('Supplier Code') . ':</td>
			<td><input type="text" data-type="no-illegal-chars" title="'._('The supplier id should not be within 10 legal characters and cannot be blank').'" required="required" name="SupplierID" placeholder="'._('within 10 characters').'" size="11" maxlength="10" /></td>
			</tr>';
		
	}*/
	echo '<tr>
			<td>' . _('Supplier Name') . ':</td>
			<td>';
			//<input type="text" pattern="(?!^\s+$)[^<>+]{1,40}" title="'._('The supplier name should not be blank and should be less than 40 legal characters').'" name="CustName" size="42" placeholder="'._('Within 40 legal characters').'" maxlength="40" /></td>
			if (isset($_POST['CustName'])&& strlen($_POST['CustName'])>=5){
				echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="'.$_POST['CustName'].'" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\（\）\(\)]+$" '.($_POST['OnEdit']>=1? "readOnly":"").' />
					 <input  type="hidden" name="RegID" value="'.$_POST['RegID'].'" />';
			 }else{
				 echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$" />';	
			 }
	echo'</td>
	    </tr>
		<tr>
			<td>详细地址:</td>
			<td><input type="text" pattern=".{1,40}" title="'._('The input should be less than 40 characters').'" placeholder="'._('Less than 40 characters').'" name="Address1" size="42" maxlength="40" /></td>
		</tr>
		<tr>
		<td>地址(省市/区):</td>
			<td><input type="text" name="Address2" pattern=".{1,40}" title="'._('The input should be less than 40 characters').'" placeholder="'._('Less than 40 characters').'" size="42" maxlength="40" /></td>
		</tr>';

	echo'<tr>
			<td>注册号/税号:</td>
			<td>';//<input type="text" name="Address5" size="42" placeholder="'._('Less than 40 characters').'" maxlength="40" />
		if (isset($_POST['Address5'])&& strlen($_POST['Address5'])>5){
			echo'<input tabindex="6" type="text" name="Address5" size="22" maxlength="20" value="'.$_POST['Address5'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
		}else{
			echo'<input tabindex="6" type="text" name="Address5" size="22" maxlength="20" value=""   pattern="^[a-zA-Z0-9]*\d{5,30}?"　 />';	
		}
	echo'</td>
		</tr>';
	echo'<tr>
			<td>' . _('Bank Particulars') . ':</td>		
			<td><input type="text" name="BankPartics" size="13" maxlength="12" value="' . $_POST['BankPartics'] . '" /></td>
		</tr><tr>
			<td>' . _('Bank Account No') . ':</td>
			<td>';//			<input type="text" name="Address5" size="31" maxlength="30" value="' . $_POST['BankAct'] . '" />
		if (isset($_POST['BankAct'])&& strlen($_POST['BankAct'])>5){
			echo'<input tabindex="6" type="text" name="BankAct" size="22" maxlength="20"  pattern="^[a-zA-Z0-9]*\d{5,30}?" value="'.$_POST['BankAct'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
		}else{
				echo'<input tabindex="6" type="text" name="BankAct" size="22" maxlength="20" value=""   pattern="^[a-zA-Z0-9]*\d{5,30}?"　 />';	
		}
	
	echo'</td>
         </tr>';
	echo'<tr>
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
			</tr>
		<tr>
			<td>' . _('Telephone') . ':</td>
			<td><input type="tel" pattern="[\s\d+)(-]{1,40}" title="'._('The input should be phone number').'" placeholder="'._('only number + - ( and ) allowed').'" name="Phone" size="30" maxlength="40" /></td>
		</tr>
		<tr>
			<td>其他联系方式:</td>
			<td><input type="tel" pattern="[\s\d+)(-]{1,40}" title="'._('The input should be fax number').'" placeholder="'._('only number + - ( and ) allowed').'" name="Fax" size="30" maxlength="40" /></td>
		</tr>
		<tr>
			<td>' . _('Email Address') . ':</td>
			<td><input type="email" name="Email" title="'._('Only email address are allowed').'" placeholder="'._('email format such as xx@mail.cn').'" size="30" maxlength="50" pattern="[a-z0-9!#$%&\'*+/=?^_ {|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)*" /></td>
		</tr>
		<tr>
			<td>' . _('URL') . ':</td>
			<td><input type="url" name="URL" title="'._('Only URL address are allowed').'" placeholder="'._('URL format such as www.example.com').'" size="30" maxlength="50" /></td>
		</tr>
		<tr>
			<td>' . _('Supplier Type') . ':</td>
			<td><select name="SupplierType">';
	$result=DB_query("SELECT typeid, typename FROM suppliertype");
	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
	} //end while loop
	echo '</select></td>
		</tr>';

	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr>
			<td>' . _('Supplier Since') . ' (' . $_SESSION['DefaultDateFormat'] . '):</td>
			<td><input type="text" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" name="SupplierSince" value="' . $DateString . '" size="12" maxlength="10" /></td>
		</tr>
		<tr>
			<td>' . _('Bank Particulars') . ':</td>
			<td><input type="text" name="BankPartics" size="13" maxlength="12" /></td>
		</tr>';
		
	echo'<tr>
			<td>' . _('Bank Account No') . ':</td>
			<td><input type="text" placeholder="'._('Less than 30 characters').'" name="BankAct" size="31" maxlength="30" />
			</td>
		</tr>';

	$result=DB_query("SELECT terms, termsindicator FROM paymentterms");

	echo '<tr>
			<td>' . _('Payment Terms') . ':</td>
			<td><select name="PaymentTerms">';

	while ($myrow = DB_fetch_array($result)) {
		echo '<option value="'. $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
	} //end while loop
	DB_data_seek($result, 0);
	echo '</select></td></tr>';

	$result=DB_query("SELECT id, coyname FROM factorcompanies");

	echo '<tr>
			<td>' . _('Factor Company') . ':</td>
			<td><select name="FactorID">';
	echo '<option value="0">' . _('None') . '</option>';
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['FactorID']) AND $_POST['FactorID'] == $myrow['id']) {
		echo '<option selected="selected" value="' . $myrow['id'] . '">' . $myrow['coyname'] . '</option>';
		} else {
		echo '<option value="' . $myrow['id'] . '">' . $myrow['coyname'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result, 0);
	echo '</select></td>
		</tr>';/*
		<tr>
			<td>' . _('Tax Reference') . ':</td>
			<td><input type="text" name="TaxRef" placehoder="'._('Within 20 characters').'" size="21" maxlength="20" /></td></tr>';
	*/
	$result=DB_query("SELECT currency, currabrev FROM currencies");
	if (!isset($_POST['CurrCode'])){
		$_POST['CurrCode'] =$_SESSION['CompanyRecord']['currencydefault'];
	}

	echo '<tr>
			<td>' . _('Supplier Currency') . ':</td>
			<td><select name="CurrCode">';
	while ($myrow = DB_fetch_array($result)) {
		if ($_POST['CurrCode'] == $myrow['currabrev']) {
			echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		} else {
			echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
		}
	} //end while loop
	DB_data_seek($result, 0);

	echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Remittance Advice') . ':</td>
			<td><select name="Remittance">
				<option value="0">' . _('Not Required') . '</option>
				<option value="1">' . _('Required') . '</option>
				</select></td>
		</tr>
		<tr>
			<td>' . _('Tax Group') . ':</td>
			<td><select name="TaxCat">';

	DB_data_seek($result, 0);

	$SQL = "SELECT taxid , description, taxrate FROM taxauthorities WHERE onorder IN (2,3)";
	
	$Result = DB_query($SQL);


	while ($myrow = DB_fetch_array($Result)) {
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
				<td>备注:</td>
				<td> <textarea placeholder="在这里输入备注内容..." cols="50" rows="3"  name="Remark"></textarea></td>
			</tr>';
	echo'<tr>
		<td>状态:</td>
		<td><select name="Status" >';
		//foreach($status as $key=>$val){	
		if (isset($_POST['Status'])&&$_POST['Status']==0){
				echo '<option selected="selected" value="0">启用</option>
				         <option  value="-1">停用</option>';
		}else{
				echo '<option value="0">启用</option>
				<option selected="selected"  value="-1">停用</option>';
		}
		
	echo '</select></td>
				</tr>';
	echo'</table>
		<br />
		<div class="centre"><input type="submit" name="submit" value="' . _('Insert New Supplier') . '" /></div>';
	echo '</div>
		</form>';
}else{

	//SupplierID exists - either passed when calling the form or from the form itself

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


	echo '<table class="selection">';

	if (!isset($_POST['New'])&&!(isset($_POST['Update'])) ) {
		$sql = "SELECT supplierid,
				custname suppname,
				address1,
				address2,
				
				address5,
				address6,
				telephone,
				fax,
				email,
				url,
				supptype,
				currcode,
				suppliersince,
				paymentterms,
				bankpartics,				
				bankact,
				remittance,
				taxcatid,
				factorcompanyid,
				taxref,
				remark,
				sub,
				a.used,
				userid,
				contactname
			FROM suppliers a
			LEFT JOIN custname_reg_sub b ON supplierid=b.regid
			WHERE supplierid = '" . $SupplierID . "'
				  AND( acctype=2 OR acctype=3 OR acctype=0) ";
		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);
		foreach($myrow as $key=>$val){
			if (!is_int($key)){
				$_SESSION['DebtorArr'][$key]=$val;
			}
		}
		$_POST['CustName'] = stripcslashes($myrow['suppname']);
		$_POST['Address1'] = stripcslashes($myrow['address1']);
		$_POST['Address2'] = stripcslashes($myrow['address2']);
	
		$_POST['Address5'] = stripcslashes($myrow['address5']);
		$_POST['Address6'] = stripcslashes($myrow['address6']);
		$_POST['CurrCode'] = stripcslashes($myrow['currcode']);
		$_POST['Phone'] = $myrow['telephone'];
		$_POST['Fax'] = $myrow['fax'];
		$_POST['Email'] = $myrow['email'];
		$_POST['URL'] = $myrow['url'];
		$_POST['SupplierType'] = $myrow['supptype'];
		$_POST['SupplierSince'] = ConvertSQLDate($myrow['suppliersince']);
		$_POST['PaymentTerms'] = $myrow['paymentterms'];
		$_POST['BankPartics'] = stripcslashes($myrow['bankpartics']);
		$_POST['Remittance'] = $myrow['remittance'];
		$_POST['Remark'] = $myrow['remark'];
		$_POST['BankAct'] = $myrow['bankact'];
		$_POST['TaxCat'] = $myrow['taxcatid'];
		$_POST['FactorID'] = $myrow['factorcompanyid'];
		$_POST['TaxRef'] = $myrow['taxref'];
		$_POST['Status'] = $myrow['used'];
		$_POST['ContactName'] = $myrow['contactname'];


		
		$OnEdit = 0;
		$OnStock=0;
		$sql= "SELECT COUNT(*) FROM supptrans WHERE supplierno='" . $SupplierID . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$OnEdit+=1;
			$OnStock++;
	
		}
		$sql= "SELECT COUNT(*) FROM purchorders WHERE supplierno='" . $SupplierID . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0] > 0) {
			$OnStock++;
			$OnEdit+=1;
			//$CancelDelete = 1;
			prnMsg(_('Cannot delete the supplier record because purchase orders have been created against this supplier'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('orders against this supplier');
		} 
		$sql= "SELECT count(*) FROM `stockmoves` WHERE debtorno='" . $SupplierID . "'";
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
		
			$_POST['OnEdit'] = $OnEdit;
			echo '<input type="hidden" name="OnStock" value="' . $_POST['OnStock'] . '" />';
		 echo '<input type="hidden" name="OnEdit" value="' . $_POST['OnEdit'] . '" />';
		 echo '<input type="hidden" name="SupplierID" value="' . $SupplierID . '" />';
		echo '<input type="hidden" name="Account" value="' . $_POST['Account'] . '" />';
		//$sql= ";
	} else {
		// its a new supplier being added
	
		echo '<input type="hidden" name="New" value="Yes" />';
	}
	echo '<tr>
			<td>' . _('Supplier Name') . ':</td>
			<td>';
			//<input '.(in_array('Name',$Errors) ? 'class="inputerror"' : '').' type="text" name="CustName" value="' . $_POST['CustName'] . '" size="42" maxlength="40" /></td>
			if (isset($_POST['CustName'])&& strlen($_POST['CustName'])>=5){
				echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="'.$_POST['CustName'].'" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\（\）\(\)]+$" '.($_POST['OnEdit']>=1? "readOnly":"").' />
					 <input  type="hidden" name="RegID" value="'.$_POST['RegID'].'" />';
			 }else{
				 echo'<input tabindex="2" type="text" name="CustName" size="42" maxlength="40" value="" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$" />';	
			 }
		echo'</td></tr>
		<tr>
			<td>详细地址:</td>
			<td><input type="text" name="Address1" value="' . $_POST['Address1'] . '" size="42" maxlength="40" /></td>
				</tr>
		<tr>
		<td>地址(省市/区):</td>
		<td><input type="text" name="Address2" value="' . $_POST['Address2'] . '" size="42" maxlength="40" /></td>
	</tr>';
	
	echo'<tr>
			<td>注册号/税号:</td>
			<td>';//<input type="text" name="Address5" size="42" placeholder="'._('Less than 40 characters').'" maxlength="40" />
		if (isset($_POST['Address5'])&& strlen($_POST['Address5'])>5){
			echo'<input tabindex="6" type="text" name="Address5" size="22" maxlength="20" value="'.$_POST['Address5'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
		}else{
			echo'<input tabindex="6" type="text" name="Address5" size="22" maxlength="20" value=""   pattern="^[a-zA-Z0-9]*\d{5,30}?"　 />';	
		}
	echo'</td>
		</tr>';
	echo'<tr>
			<td>' . _('Bank Particulars') . ':</td>		
			<td><input type="text" name="BankPartics" size="13" maxlength="12" value="' . $_POST['BankPartics'] . '" /></td>
		</tr><tr>
			<td>' . _('Bank Account No') . ':</td>
			<td>';//<input type="text" name="BankAct" size="31" maxlength="30" value="' . $_POST['BankAct'] . '" /></td>
		
		if (isset($_POST['BankAct'])&& strlen($_POST['BankAct'])>5){
			echo'<input tabindex="6" type="text" name="BankAct" size="22" maxlength="20"  pattern="^[a-zA-Z0-9]*\d{5,30}?" value="'.$_POST['BankAct'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
		}else{
				echo'<input tabindex="6" type="text" name="BankAct" size="22" maxlength="20" value=""   pattern="^[a-zA-Z0-9]*\d{5,30}?"　 />';	
		}
	
	echo'</td>
         </tr>';
	echo'<tr>
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
			</tr>
		<tr>
			<td>' . _('Telephone') . ':</td>
			<td><input '.(in_array('Name',$Errors) ? 'class="inputerror"' : '').' type="tel" pattern="[\s\d+()-]{1,40}" placeholder="'._('Only digit blank ( ) and - allowed').'" name="Phone" value="' . $_POST['Phone'] . '" size="42" maxlength="40" /></td>
		
					</tr>
		<tr>
			<td>其他联系方式:</td>
			<td><input '.(in_array('Name',$Errors) ? 'class="inputerror"' : '').' type="tel" pattern="[\s\d+()-]{1,40}" placeholder="'._('Only digit blank ( ) and - allowed').'" name="Fax" value="' . $_POST['Fax'] . '" size="42" maxlength="40" /></td>
	
				</tr>
		<tr>
			<td>' . _('Email Address') . ':</td>
			<td><input '.(in_array('Name',$Errors) ? 'class="inputerror"' : '').' type="email" title="'._('The input must be in email format').'" name="Email" value="' . $_POST['Email'] . '" size="42" maxlength="40" placeholder="'._('email format such as xx@mail.cn').'" pattern="[a-z0-9!#$%&\'*+/=?^_ {|}~.-]+@[a-z0-9-]+(\.[a-z0-9-]+)*" /></td>
		</tr>
		<tr>
			<td>' . _('URL') . ':</td>
		
			<td><input '.(in_array('Name',$Errors) ? 'class="inputerror"' : '').' type="url" title="'._('The input must be in url format').'" name="URL" value="' . $_POST['URL'] . '" size="42" maxlength="40" placeholder="'._('url format such as www.example.com').'" /></td>
		</tr>
		<tr>
			<td>' . _('Supplier Type') . ':</td>
			<td><select name="SupplierType">';
			$result=DB_query("SELECT typeid, typename FROM suppliertype");
			while ($myrow = DB_fetch_array($result)) {
				if ($_POST['SupplierType']==$myrow['typeid']) {
					echo '<option selected="selected" value="'. $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
				} else {
					echo '<option value="' . $myrow['typeid'] . '">' . $myrow['typename'] . '</option>';
				}
			} //end while loop
	echo '</select></td>
		</tr>';

	$DateString = Date($_SESSION['DefaultDateFormat']);
	echo '<tr>
			<td>' . _('Supplier Since') . ' (' . $_SESSION['DefaultDateFormat'] .'):</td>
			<td><input '.(in_array('SupplierSince',$Errors) ? 'class="inputerror"' : '').' size="12" maxlength="10" type="text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="SupplierSince" value="' . $_POST['SupplierSince'] . '" /></td>
				</tr>';
	$result=DB_query("SELECT terms, termsindicator FROM paymentterms");

	echo '<tr>
			<td>' . _('Payment Terms') . ':</td>
		
			<td><select name="PaymentTerms">';

			while ($myrow = DB_fetch_array($result)) {
				if ($_POST['PaymentTerms'] == $myrow['termsindicator']) {
				echo '<option selected="selected" value="' . $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
				} else {
				echo '<option value="' . $myrow['termsindicator'] . '">' . $myrow['terms'] . '</option>';
				}
			} //end while loop
			DB_data_seek($result, 0);
			echo '</select></td></tr>';

	$result=DB_query("SELECT id, coyname FROM factorcompanies");

	echo '<tr>
			<td>' . _('Factor Company') . ':</td>
			<td><select name="FactorID">';
			
			echo '<option value="0">' . _('None') . '</option>';
			while ($myrow = DB_fetch_array($result)) {
				if ($_POST['FactorID'] == $myrow['id']) {
				echo '<option selected="selected" value="' . $myrow['id'] . '">' . $myrow['coyname'] . '</option>';
				} else {
				echo '<option value="' . $myrow['id'] . '">' . $myrow['coyname'] . '</option>';
				}
			} //end while loop
			DB_data_seek($result, 0);
	echo '</select></td>
		</tr>';/*
		<tr>
			<td>' . _('Tax Reference') . ':</td>
			<td><input type="text" name="TaxRef" placehoder="'._('Within 20 characters').'" size="21" maxlength="20" /></td></tr>';
	*/
	$result=DB_query("SELECT currency, currabrev FROM currencies");
	if (!isset($_POST['CurrCode'])){
		$_POST['CurrCode'] =$_SESSION['CompanyRecord']['currencydefault'];
	}

	echo '<tr>
			<td>' . _('Supplier Currency') . ':</td>
			<td><select name="CurrCode">';
			while ($myrow = DB_fetch_array($result)) {
				if ($_POST['CurrCode'] == $myrow['currabrev']) {
					echo '<option selected="selected" value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
				} else {
					echo '<option value="' . $myrow['currabrev'] . '">' . $myrow['currency'] . '</option>';
				}
			} //end while loop
	DB_data_seek($result, 0);

	echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Remittance Advice') . ':</td>
			<td><select name="Remittance">';
		
	if ($_POST['Remittance'] == 0) {
		echo '<option selected="selected" value="0">' . _('Not Required') . '</option>';
		echo '<option value="1">' . _('Required') . '</option>';
	} else {
		echo '<option value="0">' . _('Not Required') . '</option>';
		echo '<option selected="selected" value="1">' . _('Required') . '</option>';

	}
	echo '</select></td>
		</tr>
		<tr>
			<td>' . _('Tax Group') . ':</td>
			<td><select name="TaxCat">';

	DB_data_seek($result, 0);

	$sql 	= "SELECT taxid , description ,taxrate FROM taxauthorities WHERE onorder IN (2,3)";
	
	$TaxGroupResults = DB_query($sql);
	while ($myrow = DB_fetch_array($TaxGroupResults)) {
		if (isset($_POST['TaxCat']) AND $myrow['taxid'].'^'.$myrow['taxrate']==$_POST['TaxCat']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
		echo $myrow['taxid'].'^'.$myrow['taxrate']. '">' . $myrow['description'] . '</option>';

	}//end while loop

	echo '</select></td>
		</tr>';
		echo'<tr>
				<td>备注:</td>
				<td> <textarea cols="50" rows="3"  name="Remark">'.$_POST['Remark'].'</textarea></td>
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

	echo '</table>';
	if (isset($_POST['Update'])) {
		//prnMsg('Update'.$_POST['OnEdit']);
		//var_dump($_SESSION['DebtorArr'] );
		$sqlstr='';
		$sqlname='';
		$sqlbank='';
		$sqlact='';
		foreach($_SESSION['DebtorArr'] as $key=>$val){
			//prnMsg($key.'[---]'.$val);
			if ($key=='suppname' && $_POST['CustName']!=$val && $_POST['CustName']!=''){
				
				$sqlstr.="  suppname='".trim($_POST['CustName'])."',";
				$sqlname=trim($_POST['CustName']);
			}elseif ($key=='address1' && $_POST['Address1']!=$val&&$_POST['Address1']!=''){
	
				$sqlstr.="  address1=' ".$_POST['Address1']."',";
			}elseif ($key=='address2' && $_POST['Address2']!=$val&&$_POST['Address2']!=''){
	
				$sqlstr.="  address2= '".$_POST['Address2']."',";
	
			}elseif ($key=='address5' && $_POST['Address5']!=$val &&$_POST['Address5']!=''){
				//注册码
				$sqlreg=$_POST['Address5'];
				$sqlstr.="  address5='".$_POST['Address5']."',";
			}elseif ($key=='address6' && $_POST['Address6']!=$val && $_POST['Address6']!=''){
	
				$sqlstr.="  address6= '".$_POST['Address6']."',";
			}elseif ($key=='currcode' && $_POST['CurrCode']!=$val && $_POST['CurrCode']!=''){
	
				$sqlstr.="  currcode= '".$_POST['CurrCode']."',";
			}elseif ($key=='supptype' && $_POST['SupplierType']!=$val && $_POST['SupplierType']!=''){
				
				$sqlstr.="  suppliertype= '".$_POST['SupplierType']."',";
			}elseif ($key=='paymentterms' && $_POST['PaymentTerms']!=$val && $_POST['PaymentTerms']!=''){
				//10
				$sqlstr.="  paymentterms= '".$_POST['PaymentTerms']."',";
			}elseif ($key=='remark' && $_POST['Remark']!=$val && $_POST['Remark']!=''){
	
				$sqlstr.="  remark= '".$_POST['Remark']."',";
			}elseif ($key=='telephone' && $_POST['Phone']!=$val && $_POST['Phone']!=''){
	
				$sqlstr.="  telephone='".$_POST['Phone']."',";
			
			}elseif ($key=='fax' && $_POST['Fax']!=$val && $_POST['Fax']!=''){
				$sqlstr.="  fax= '".$_POST['Fax']."',";
			}elseif ($key=='email' && $_POST['Email']!=$val && $_POST['Email']!=''){
				
				$sqlstr.="  email= '".$_POST['Email']."',";
			}elseif ($key=='taxcatid' && explode('^',$_POST['TaxCat'])[0]!=$val && $_POST['TaxCat']!=''){
	
				$sqlstr.="  taxcatid= '".explode('^',$_POST['TaxCat'])[0]."',";
			}elseif ($key=='taxrate' && explode('^',$_POST['TaxCat'])[1]!=$val && $_POST['TaxCat']!=''){
				$sqlstr.="  taxrate= '".explode('^',$_POST['TaxCat'])[1]."',";
			}elseif ($key=='url' && $_POST['URL']!=$val && $_POST['URL']!=''){
				$sqlstr.="  url= '".$_POST['URL']."',";
			}elseif ($key=='suppliersince' && $_POST['SupplierSince']!=$val && $_POST['SupplierSince']!=''){
				
				$sqlstr.="  suppliersince= '".FormatDateForSQL($_POST['SupplierSince'])."',";
			}elseif ($key=='contactname' && $_POST['ContactName']!=$val && $_POST['ContactName']!=''){
				
				$sqlstr.="  contactname= '".$_POST['ContactName']."',";
			}elseif ($key=='used' && $_POST['Status']!=$val ){//&& $_POST['Status']!=true){
			  //20	
				$sqlstr.="  used= '".$_POST['Status']."',";
			}elseif ($key=='bankact' && $_POST['BankAct']!=$val && $_POST['BankAct']!=''){
					//银行账号
				$sqlstr.="  bankact= '".$_POST['BankAct']."',";
				$sqlbank=$_POST['bankact'];
			}elseif ($key=='bankpartics' && $_POST['BankPartics']!=$val && $_POST['BankPartics']!=''){
				$sqlstr.="  bankpartics= '".$_POST['BankPartics']."',";
				
			}
		}
		//prnMsg($sqlstr);
		if ($sqlstr!=''){
			$result=DB_Txn_Begin();	
			//prnMsg($sqlstr);
			$sqlstr=substr($sqlstr,0,-1);
			$sql="UPDATE `suppliers` SET  lastpaiddate='".date('Y-m-d H:i:s')."',userid='".$_SESSION['UserID']."',".$sqlstr." WHERE supplierid=".$_POST['SupplierID'];
			 $result=DB_query($sql); 
		}
		if ($sqlbank!=''){
			$sql="UPDATE `accountsubject` SET `bankaccount`='".$sqlbank."' WHERE regid=".$_POST['SupplierID'];
			$result=DB_query($sql);
		}
		if ($sqlname!=''){
			$sql="UPDATE `registername` SET `custname`='".$sqlname."' WHERE regid=".$_POST['SupplierID'];
			$result=DB_query($sql);
		}
		if ($sqlreg!=''){
			$sql="UPDATE `registeraccount` SET `registerno`='".$sqlreg."' WHERE regid=".$_POST['SupplierID'];
			$result=DB_query($sql);
		}
		if ($result){
			$result=DB_Txn_Commit();
			prnMsg('你的供应商['.$_POST['SupplierID'].']'.$_POST['CustName'].'更新操作成功!','success');
			unset($_SESSION['DebtorArr']);
			unset($_SESSION['SupplierID']);
			unset($_POST['RegID']);
			unset($SupplierID);
			unset($_POST['OnEdit']);
			unset($_POST['CustName']);
			unset($_POST['Address1']);
			unset($_POST['Address2']);
			unset($_POST['BankPartics']);
			unset($_POST['BankAct']);
			unset($_POST['Address5']);
			unset($_POST['Address6']);
			unset($_POST['Phone']);
			unset($_POST['Fax']);
			unset($_POST['Email']);
			unset($_POST['URL']);
			unset($_POST['SupplierType']);
			unset($_POST['CurrCode']);
			unset($SQL_SupplierSince);
			unset($_POST['PaymentTerms']);
			unset($_POST['BankPartics']);
			unset($_POST['BankRef']);
			unset($_POST['BankAct']);
			unset($_POST['Remittance']);
			unset($_POST['TaxCat']);
			
		
		}
	
		   
	}
	if (isset($_POST['New'])) {
		echo '<br />
				<div class="centre">
					<input type="submit" name="submit" value="' . _('Add These New Supplier Details') . '" />
				</div>';
	} else {
		echo '<br />
				<div class="centre">
					<input type="submit" name="Update" value="' . _('Update Supplier') . '" />
				';
	//		echo '<p><font color=red><b>' . _('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed') . '<br /></font></b>';
	//	prnMsg(_('WARNING') . ': ' . _('There is no second warning if you hit the delete button below') . '. ' . _('However checks will be made to ensure there are no outstanding purchase orders or existing accounts payable transactions before the deletion is processed'), 'Warn');
		echo '
				<input type="submit" name="delete" value="' . _('Delete Supplier') . '" onclick="return confirm(\'' . _('Are you sure you wish to delete this supplier?') . '\');" />
			<br />';
			echo '<br /><div class="centre"><a href="Suppliers.php" >供应商添加</a></div><br />';
	}

} // end of main ifs

echo '</div>
</form>';
include('includes/footer.php');
?>
