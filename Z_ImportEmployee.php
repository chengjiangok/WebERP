<?php
/* $Id: Z_ImportEmployee.php 2016/11/28 21:57:26 ChengJiang $*/

include('includes/session.php');
$Title = iconv('GB2312', 'UTF-8', "������Ա����");
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['FormID'])){

	$_POST['AutoDebtorNo']=$_SESSION['AutoDebtorNo'];
	$_POST['UpdateIfExists']=0;
}

// If this script is called with a file object, then the file contents are imported
// If this script is called with the gettemplate flag, then a template file is served
// Otherwise, a file upload form is displayed
$FieldHeadings = array(
   'emplid',//0
   'emplname',//1	
   'sex',
   'cardid',	
   'birthdate',	
   'placeoforign',	
   'marriage',
   'accountlocation',	
   'degreeofeducation',
   'address',	
   'tel1',//10
   'tel2',
   'email1',
   'email2',
   	'department',//14
   		'job',
   			'duty',
   			'accdepartment',
   			'acctype',
   			'wage',
   			'wageetype',//20
   			'flag',
   			'minguarantee',
   			'overtime',
   			'attendance',
   			'workhours',
   			'subsidy',
   			'rent',
   			'power',
   			'social',
   			'enterdate',//30
   			'ontrial',
   			'remark'//32
);
if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing

	//initialize
	$FieldTarget = count($FieldHeadings);
	$InputError = 0;

	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName  = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];
	//get file handle
	$FileHandle = fopen($TempName, 'r');
	//get the header row
	$headRow = fgetcsv($FileHandle,  ",");
	//check for correct number of fields
	if ( count($headRow) != count($FieldHeadings) ) {
		prnMsg (_('File contains '. count($headRow). ' columns, expected '. count($FieldHeadings). '. Try downloading a new template.'),'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit;
	}

	//test header row field name and sequence
	$head = 0;
	foreach ($headRow as $headField) {
		if ( mb_strtoupper($headField) != mb_strtoupper($FieldHeadings[$head]) ) {
			prnMsg (_('File contains incorrect headers ('. mb_strtoupper($headField). ' != '. mb_strtoupper($header[$head]). '. Try downloading a new template.'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit;
		}
		$head++;
	}

	//start database transaction
	DB_Txn_Begin();

	//loop through file rows
	$row = 1;
	$UpdatedNum=0;
	$InsertNum=0;
 $STR='';
	while ( ($filerow = fgetcsv($FileHandle,  ",")) !== FALSE ) {

		//check for correct number of fields
		$fieldCount = count($filerow);
		if ($fieldCount != $FieldTarget){
			prnMsg (_($FieldTarget. ' fields required, '. $fieldCount. ' fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit;
		}

		// cleanup the data (csv files often import with empty strings and such)
		foreach ($filerow as &$value) {
			$value = trim($value);
		}

	//	$_POST['emplid']=$filerow[0];
		$_POST['emplname']=iconv('GB2312', 'UTF-8',$filerow[1]);
		$_POST['sex']=$filerow[2];
		$_POST['cardid']=$filerow[3];
		$_POST['birthdate']=$filerow[4];
		$_POST['placeoforign']=iconv('GB2312', 'UTF-8',$filerow[5]);
		$_POST['marriage']=$filerow[6];
		$_POST['accountlocation']=iconv('GB2312', 'UTF-8',$filerow[7]);
		$_POST['degreeofeducation']=iconv('GB2312', 'UTF-8',$filerow[8]);
		$_POST['address']=iconv('GB2312', 'UTF-8',$filerow[9]);
		$_POST['tel1']=$filerow[10];
		$_POST['tel2']=$filerow[11];
		$_POST['email1']=$filerow[12];
		$_POST['email2']=$filerow[13];
		$_POST['title']='';//$filerow[14];
		$_POST['post']="";
		$_POST['img']="";
	
	


		$i=0;
		if ($_POST['emplname']==0 AND mb_strlen($_POST['emplname']) < 2  AND mb_strlen($_POST['emplname']) >10) {
			$InputError = 1;
			prnMsg( iconv('GB2312', 'UTF-8',"��Ա������Ϊ�ջ�����10�֣�"),'error');
			$Errors[$i] = 'emplname';
			$i++;
		} elseif (ContainsIllegalCharacters($_POST['emplname']) OR mb_strpos($_POST['emplname'], ' ')) {
			$InputError = 1;
			prnMsg(iconv('GB2312', 'UTF-8','"��Ա�����ܰ��������κ��ַ�') . " . - ' &amp; + \" " . _('or a space'),'error');
			$Errors[$i] = 'emplname';
			$i++;
		}
	/*	if (mb_strlen($_POST['CustName']) > 40 OR mb_strlen($_POST['CustName'])==0) {
			$InputError = 1;
			prnMsg( _('The customer name must be entered and be forty characters or less long'),'error');
			$Errors[$i] = 'CustName';
			$i++;
		} elseif (mb_strlen($_POST['Address1']) >40) {
			$InputError = 1;
			prnMsg( _('The Line 1 of the address must be forty characters or less long'),'error');
			$Errors[$i] = 'Address1';
			$i++;
		}   elseif (!Is_Date($_POST['ClientSince'])) {
			$InputError = 1;
			prnMsg( _('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
			$Errors[$i] = 'ClientSince';
			$i++;
		} elseif (!is_numeric(filter_number_format($_POST['Discount']))) {
			$InputError = 1;
			prnMsg( _('The discount percentage must be numeric'),'error');
			$Errors[$i] = 'Discount';
			$i++;
		} elseif (filter_number_format($_POST['CreditLimit']) <0) {
			$InputError = 1;
			prnMsg( _('The credit limit must be a positive number'),'error');
			$Errors[$i] = 'CreditLimit';
			$i++;
		} 

	
		if (mb_strlen($_POST['EDIReference'])<4 AND ($_POST['EDIInvoices']==1 OR $_POST['EDIOrders']==1)){
			$InputError = 1;
			prnMsg(_('The customers EDI reference code must be set when EDI Invoices or EDI orders are activated'),'warn');
			$Errors[$i] = 'EDIReference';
			$i++;
		}
	

*//*
		if ($InputError !=1){
			$sql="SELECT 1 FROM debtorsmaster WHERE debtorno='".$_POST['DebtorNo']."' LIMIT 1";
			$result=DB_query($sql);
			$DebtorExists=(DB_num_rows($result)>0);
			if ($DebtorExists AND $_POST['UpdateIfExists']!=1) {
				$UpdatedNum++;
			}else{

				$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);

				if ($DebtorExists) {//update
					$UpdatedNum++;
					$sql = "SELECT 1
							  FROM debtortrans
							where debtorno = '" . $_POST['DebtorNo'] . "' LIMIT 1";
					$result = DB_query($sql);

					$curr=false;
					if (DB_num_rows($result) == 0) {
						$curr=true;
					}else{
						$CurrSQL = "SELECT currcode
							FROM debtorsmaster
							where debtorno = '" . $_POST['DebtorNo'] . "'";
						$CurrResult = DB_query($CurrSQL);
						$CurrRow = DB_fetch_array($CurrResult);
						$OldCurrency = $CurrRow[0];
						if ($OldCurrency != $_POST['CurrCode']) {
							prnMsg( _('The currency code cannot be updated as there are already transactions for this customer'),'info');
						}
					}

					$sql = "UPDATE debtorsmaster SET
							name='" . $_POST['CustName'] . "',
							address1='" . $_POST['Address1'] . "',
							address2='" . $_POST['Address2'] . "',
							address3='" . $_POST['Address3'] ."',
							address4='" . $_POST['Address4'] . "',
							address5='" . $_POST['Address5'] . "',
							address6='" . $_POST['Address6'] . "',";

					if($curr)
						$sql .= "currcode='" . $_POST['CurrCode'] . "',";

					$sql .=	"clientsince='" . $SQL_ClientSince. "',
							holdreason='" . $_POST['HoldReason'] . "',
							paymentterms='" . $_POST['PaymentTerms'] . "',
							discount='" . filter_number_format($_POST['Discount'])/100 . "',
							discountcode='" . $_POST['DiscountCode'] . "',
							pymtdiscount='" . filter_number_format($_POST['PymtDiscount'])/100 . "',
							creditlimit='" . filter_number_format($_POST['CreditLimit']) . "',
							salestype = '" . $_POST['SalesType'] . "',
							invaddrbranch='" . $_POST['AddrInvBranch'] . "',
							taxref='" . $_POST['TaxRef'] . "',
							customerpoline='" . $_POST['CustomerPOLine'] . "',
							typeid='" . $_POST['typeid'] . "',
							language_id='" . $_POST['LanguageID'] . "'
						  WHERE debtorno = '" . $_POST['DebtorNo'] . "'";

					$ErrMsg = _('The customer could not be updated because');
					$result = DB_query($sql,$ErrMsg);

				} else { //insert
					$InsertNum++;
					$sql = "INSERT INTO debtorsmaster (
							debtorno,
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
							discount,
							discountcode,
							pymtdiscount,
							creditlimit,
							salestype,
							invaddrbranch,
							taxref,
							customerpoline,
							typeid,
							language_id)
						VALUES ('" . $_POST['DebtorNo'] ."',
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
							'" . filter_number_format($_POST['Discount'])/100 . "',
							'" . $_POST['DiscountCode'] . "',
							'" . filter_number_format($_POST['PymtDiscount'])/100 . "',
							'" . filter_number_format($_POST['CreditLimit']) . "',
							'" . $_POST['SalesType'] . "',
							'" . $_POST['AddrInvBranch'] . "',
							'" . $_POST['TaxRef'] . "',
							'" . $_POST['CustomerPOLine'] . "',
							'" . $_POST['typeid'] . "',
							'" . $_POST['LanguageID'] . "')";

					$ErrMsg = _('This customer could not be added because');
					$result = DB_query($sql,$ErrMsg);
				}
			}

		}else{

			break;
		}
*/
		$i=0;

	
		if ($InputError !=1){
			if (DB_error_no() ==0) {
/*
				$sql = "SELECT 1
				     FROM custbranch
           			 WHERE debtorno='".$_POST['DebtorNo']."' AND
				           branchcode='".$_POST['BranchCode']."' LIMIT 1";
				$result=DB_query($sql);
				$BranchExists=(DB_num_rows($result)>0);
				if ($BranchExists AND $_POST['UpdateIfExists']!=1) {
					//do nothing
				}else{

					if (!isset($_POST['EstDeliveryDays'])) {
						$_POST['EstDeliveryDays']=1;
					}
					if (!isset($Latitude)) {
						$Latitude=0.0;
						$Longitude=0.0;
					}
					if ($BranchExists) {
						$sql = "UPDATE custbranch SET brname = '" . $_POST['BrName'] . "',
									braddress1 = '" . $_POST['BrAddress1'] . "',
									braddress2 = '" . $_POST['BrAddress2'] . "',
									braddress3 = '" . $_POST['BrAddress3'] . "',
									braddress4 = '" . $_POST['BrAddress4'] . "',
									braddress5 = '" . $_POST['BrAddress5'] . "',
									braddress6 = '" . $_POST['BrAddress6'] . "',
									lat = '" . $Latitude . "',
									lng = '" . $Longitude . "',
									specialinstructions = '" . $_POST['SpecialInstructions'] . "',
									phoneno='" . $_POST['PhoneNo'] . "',
									faxno='" . $_POST['FaxNo'] . "',
									fwddate= '" . $_POST['FwdDate'] . "',
									contactname='" . $_POST['ContactName'] . "',
									salesman= '" . $_POST['Salesman'] . "',
									area='" . $_POST['Area'] . "',
									estdeliverydays ='" . filter_number_format($_POST['EstDeliveryDays']) . "',
									email='" . $_POST['Email'] . "',
									taxgroupid='" . $_POST['TaxGroup'] . "',
									defaultlocation='" . $_POST['DefaultLocation'] . "',
									brpostaddr1 = '" . $_POST['BrPostAddr1'] . "',
									brpostaddr2 = '" . $_POST['BrPostAddr2'] . "',
									brpostaddr3 = '" . $_POST['BrPostAddr3'] . "',
									brpostaddr4 = '" . $_POST['BrPostAddr4'] . "',
									brpostaddr5 = '" . $_POST['BrPostAddr5'] . "',
									disabletrans='" . $_POST['DisableTrans'] . "',
									defaultshipvia='" . $_POST['DefaultShipVia'] . "',
									custbranchcode='" . $_POST['CustBranchCode'] ."',
									deliverblind='" . $_POST['DeliverBlind'] . "'
								WHERE branchcode = '".$_POST['BranchCode']."' AND debtorno='".$_POST['DebtorNo']."'";

					} else {
*/
						$sql = "INSERT INTO employee (
						                   emplname,
                               sex,
                               cardid,
                               birthdate,
                               placeoforign,
                               marriage,
                               accountlocation,
                               degreeofeducation,
                               address,
                               tel1,
                               tel2,
                               email1,
                               email2,
                               title,
                               post,
                               img )
								VALUES ('" .$_POST['emplname']."',
								         '".$_POST['sex']."',
								         '".$_POST['cardid']."',
								         '2016-1-1',
								         '".$_POST['placeoforign']."',
								         '".$_POST['marriage']."',
								         '".$_POST['accountlocation']."',
								         '".$_POST['degreeofeducation']."',
								         '".$_POST['address']."',
								         '".$_POST['tel1']."',
								         '".$_POST['tel2']."',
								         '".$_POST['email1']."',
								         '".$_POST['email2']."',
								         '".$_POST['title']."',
								         '',
								         '".$_POST['img']."' )";
								         
			

					$ErrMsg = _('The branch record could not be inserted or updated because');
					$result = DB_query($sql, $ErrMsg);


					if (DB_error_no() ==0) {
						prnMsg( iconv('GB2312', 'UTF-8',"��Ա����") .' ' .$_POST['emplname']  . ' '.iconv('GB2312', 'UTF-8',"�Ѿ������ɹ���"),'info');
					} else { //location insert failed so set some useful error info
						$InputError = 1;
						prnMsg(_($result),'error');
					}
				}
			} else { //item insert failed so set some useful error info
				$InputError = 1;
				prnMsg(_($result),'error');
			}

		

		if ($InputError == 1) { //this row failed so exit loop
			break;
		}

		$row++;
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(_('Failed on row '. $row. '. Batch import has been rolled back.'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( _('Batch Import of') .' ' . $FileName  . ' '. _('has been completed. All transactions committed to the database.'),'success');
		if($_POST['UpdateIfExists']==1){
			prnMsg( _('Updated:') .' ' . $UpdatedNum .' '._('Insert:'). $InsertNum );
		}else{
			prnMsg( _('Exist:') .' ' . $UpdatedNum .' '._('Insert:'). $InsertNum );
		}
	}

	fclose($FileHandle);

} elseif ( isset($_POST['gettemplate']) || isset($_GET['gettemplate']) ) { //download an import template

	echo '<br /><br /><br />"'. implode('","',$FieldHeadings). '"<br /><br /><br />';

} else { //show file upload form

	prnMsg(_('Please ensure that your csv file is encoded in UTF-8, otherwise the input data will not store correctly in database'),'warn');

	echo '
		<br />
		<a href="Z_ImportDebtors.php?gettemplate=1">Get Import Template</a>
		<br />
		<br />';
	echo '<form action="Z_ImportEmployee.php" method="post" enctype="multipart/form-data">';
    echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />' .
			_('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" value="' . _('Send File') . '" />';
	echo '<br/>',_('Create Debtor Codes Automatically'),':<input type="checkbox" name="AutoDebtorNo" ';
	if($_POST['AutoDebtorNo']==1)echo 'checked="checked"';
	echo '>';
	echo '<br/>',_('Update if DebtorNo exists'),':<input type="checkbox" name="UpdateIfExists">';
	echo'</div>
		</form>';

}


include('includes/footer.php');
?>
