
<?php

/* $Id: HrEmployees.php  $ */
/*
 * @Author:  ChengJiang 
 * @Date: 2019-10-07 05:14:54 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-07 08:31:35
 */
/*	Add and Edit Employee*/

include('includes/session.php');
$Title = _('Employees');

$ViewTopic = 'EmployeeManagement';
$BookMark = 'Employees';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<a href="' . $RootPath . '/HrSelectEmployee.php">' . _('Search For Employee') . '</a><br />' . "\n";

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="' .
		_('Employee data') . '" alt="" />' . ' ' . $Title . '</p>';
    /* If this form is called with the EmpID then it is assumed that the employee is to be modified  */
    if (isset($_GET['EmpID'])){
    	$EmpID =$_GET['EmpID'];
    } elseif (isset($_POST['EmpID'])){
    	$EmpID =$_POST['EmpID'];
    } elseif (isset($_POST['Select'])){
    	$EmpID =$_POST['Select'];
    } else {
    	//$EmpID = '';
			//$_POST['EmpDOB'] = date($_SESSION['DefaultDateFormat']);
			//$_POST['EmpJoiningDate'] = date($_SESSION['DefaultDateFormat']);

    }

    $SupportedImgExt = array('png','jpg','jpeg');
		$Errors = array();

    if (isset($_FILES['EmployeePicture']) AND $_FILES['EmployeePicture']['name'] !='') {
    	$ImgExt = pathinfo($_FILES['EmployeePicture']['name'], PATHINFO_EXTENSION);

    	$result    = $_FILES['EmployeePicture']['error'];
     	$UploadTheFile = 'Yes'; //Assume all is well to start off with
    	$filename = $_SESSION['part_pics_dir'] . '/EMP_' . $EmpID . '.' . $ImgExt;
    	//But check for the worst
    	if (!in_array ($ImgExt, $SupportedImgExt)) {
    		prnMsg(_('Only ' . implode(", ", $SupportedImgExt) . ' files are supported - a file extension of ' . implode(", ", $SupportedImgExt) . ' is expected'),'warn');
    		$UploadTheFile ='No';
    	} elseif ( $_FILES['EmployeePicture']['size'] > ($_SESSION['MaxImageSize']*1024)) { //File Size Check
    		prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' ' . $_SESSION['MaxImageSize'],'warn');
    		$UploadTheFile ='No';
    	} elseif ( $_FILES['EmployeePicture']['type'] == 'text/plain' ) {  //File Type Check
    		prnMsg( _('Only graphics files can be uploaded'),'warn');
             	$UploadTheFile ='No';
    	}
    	foreach ($SupportedImgExt as $ext) {
    		$file = $_SESSION['part_pics_dir'] . '/EMP_' . $EmpID . '.' . $ext;
    		if (file_exists ($file) ) {
    			$result = unlink($file);
    			if (!$result){
    				prnMsg(_('The existing image could not be removed'),'error');
    				$UploadTheFile ='No';
    			}
    		}
    	}

    	if ($UploadTheFile=='Yes'){
    		$result  =  move_uploaded_file($_FILES['EmployeePicture']['tmp_name'], $filename);
    		$message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
    	}
     /* EOR Add Image upload for New Item  - by Ori */
	}
	//var_dump($CountriesArray );
    if (isset($_POST['submit'])) {

    	//initialise no input errors assumed initially before we test

    	/* actions to take once the user has clicked the submit button
    	ie the page has called itself with some user input */

    	//first off validate inputs sensible
    	$i=1;


    	if (!isset($_POST['EmpIDname']) or mb_strlen($_POST['EmpIDname']) > 50 OR mb_strlen($_POST['EmpIDname'])==0) {
    		$InputError = 1;
    		prnMsg (_('The employee_id must be entered and be fifty characters or less long. It cannot be a zero length string either, employeeId is required'),'error');
    		$Errors[$i] = 'EmpIDname';
    		$i++;
    	}
    	if (mb_strlen($_POST['EmpJoiningDate'])==0) {
    		$InputError = 1;
    		prnMsg (_('The date employee joined cannot be blank, a joining date is required'),'error');
    		$Errors[$i] = 'EmpJoiningDate';
    		$i++;
    	}

    	if (trim($_POST['EmpPositionID']) == '') {
    		$InputError = 1;
    		prnMsg(_('There are no employee positions defined. All employees must have a position'),'error');
    		$Errors[$i] = 'EmpPositionID';
    		$i++;
    	}

    	if (trim($_POST['EmpDepartmentID'])==''){
    		$InputError = 1;
    		prnMsg(_('There are no departments defined. All employees must belong to a valid department,'),'error');
    		$Errors[$i] = 'EmpDepartmentID';
    		$i++;
    	}
			if (trim($_POST['PayrollGroupID'])==''){
    		$InputError = 1;
    		prnMsg(_('There are no payroll groups defined. create a payroll group for salaries,'),'error');
    		$Errors[$i] = 'PayrollGroupID';
    		$i++;
    	}
			if (trim($_POST['Nationality'])==''){
    		$InputError = 1;
    		prnMsg(_('You have not selected the Nationality of Employee.'),'error');
    		$Errors[$i] = 'Nationality';
    		$i++;
    	}
    	if (!is_numeric(filter_number_format($_POST['MobileNumber']))
    		OR mb_strlen($_POST['MobileNumber'])>12
    		OR mb_strlen($_POST['MobileNumber'])<8){

    		$InputError = 1;
    		prnMsg(_('The mobile phone number is not valid, not less than 9 numbers and not more than 12 numbers'),'error');
    		$Errors[$i] = 'MobileNumber';
    		$i++;
    	}
      if (trim($_POST['EmpFirstName'])==''){
    		$InputError = 1;
    		prnMsg(_('Employee first name cannot be blank. ,'),'error');
    		$Errors[$i] = 'EmpFirstName';
    		$i++;
		}
		/*
      if (trim($_POST['EmpLastName'])==''){
    		$InputError = 1;
    		prnMsg(_('Employee last name cannot be blank. ,'),'error');
    		$Errors[$i] = 'EmpLastName';
    		$i++;
    	}*/
      if(trim($_POST['EmpEmail'])!=''){
          $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";
          if(!preg_match($pattern,$_POST['EmpEmail']))
          {
            $InputError = 1;
        		prnMsg(_('You have entered and invalid email. ,'),'error');
        		$Errors[$i] = 'EmpEmail';
        		$i++;
          }
      }
			if(isset($_POST['EnableLogin']))
			{
					if (trim($_POST['EmpUsername'])==''){
						$InputError = 1;
						prnMsg(_('Employee username cannot be blank. ,'),'error');
						$Errors[$i] = 'EmpUsername';
						$i++;
					}
					if (trim($_POST['EmpPassword'])==''){
						$InputError = 1;
						prnMsg(_('Employee password cannot be blank. ,'),'error');
						$Errors[$i] = 'EmpPassword';
						$i++;
					}
			}
			if (!is_numeric($_POST['EmpNetPay'])){
				$InputError = 1;
				prnMsg(_('Employee net pay is not well calculated. ,'),'error');
				$Errors[$i] = 'EmpNetPay';
				$i++;
			}
			if (trim($_POST['EmpNetPay'])==''){
				$InputError = 1;
				prnMsg(_('Employee net pay is not well calculated. ,'),'error');
				$Errors[$i] = 'EmpNetPay';
				$i++;
			}
			if ($_POST['EmpNetPay'] < 0){
				$InputError = 1;
				prnMsg(_('Employee net pay cannot be negative. ,'),'error');
				$Errors[$i] = 'EmpNetPay';
				$i++;
			}

			$dob = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['EmpDOB']);
			$joiningdate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['EmpJoiningDate']);


    	if ($InputError !=1){

    		if ($_POST['submit']==_('Update')) { /*so its an existing one */

    			/*Start a transaction to do the whole lot inside */
    			//$result = DB_Txn_Begin();
/* Update employees table first
	middle_name='" . $_POST['EmpMiddleName'] . "',
    						last_name='" . $_POST['EmpLastName'] . "',*/
    			$sql = "UPDATE hremployees
    					SET empid='" . $_POST['EmpIDname'] . "',
    						joining_date='" .$joiningdate->format('Y-m-d') . "',
    						first_name='" . $_POST['EmpFirstName'] . "',
    					
    						gender='" . $_POST['Gender'] . "',
    						employee_position='" . $_POST['EmpPositionID'] . "',
								job_title='" . $_POST['EmpJobTitle'] . "',
    						resume='" . $_POST['EmpCV'] . "',
    						employee_department='" . $_POST['EmpDepartmentID'] . "',
    						status='" . $_POST['EmpStatus'] . "',
    						date_of_birth='" . $dob->format('Y-m-d'). "',
    						marital_status='" . $_POST['MaritalStatus'] . "',
								father_name='" . $_POST['EmpFirstName'] . "',
    						mother_name='" . $_POST['EmpMotherName'] . "',
    						nationality='" . $_POST['Nationality'] . "',
    						national_id='" . $_POST['NationalID'] . "',
    						passport_no='" . $_POST['PassportNO'] . "',
    						home_address='" . $_POST['EmpAddress'] . "',
								mobile_phone='" . $_POST['MobileNumber'] . "',
								email='" . $_POST['EmpEmail'] . "',
								manager_id='" . $_POST['EmpManagerID'] . "',
								spouse_name='" . $_POST['SpouseName'] . "',
								spouse_phone_no='" . $_POST['SpousePhoneNumber'] . "',
								bank_name='" . $_POST['EmpBankName'] . "',
							
								bank_account_no='" . $_POST['EmpBankAccount'] . "',
    						employee_grade_id='" . $_POST['EmpGradeID'] . "'
    					WHERE employee_id='" . $EmpID . "'";

    			$ErrMsg = _('The employee could not be updated because');
    			$DbgMsg = _('The SQL that was used to update the employee and failed was');
    			$result = DB_query($sql,$ErrMsg,$DbgMsg);

    			prnMsg( _('Employee') . ' ' . $EmpID . ' ' . _('has been updated'), 'success');
    			echo '<br />';


					/* edit salary structure*/
					$sql = "SELECT empid,payrollgroup_id,salary_structure_id FROM hremployees JOIN hremployeesalarystructures ON hremployees.empid = hremployeesalarystructures.employee_id WHERE hremployees.employee_id ='".$EmpID."'";
					$result = DB_query($sql);
					$my_row = DB_fetch_array($result);
					$emp_id = $my_row['empid'];
					$old_payrollgroup_id = $my_row['payrollgroup_id'];
					$employee_salary_structure_id = $my_row['salary_structure_id'];
					$sql = "UPDATE hremployeesalarystructures
								SET payrollgroup_id ='".$_POST['PayrollGroupID']."',
										gross_pay ='".$_POST['GROSS']."',
										net_pay ='".$_POST['EmpNetPay']."'
								WHERE employee_id='".$emp_id."'
								";
								$ErrMsg = _('The employee salary structure could not be updated because');
			    			$DbgMsg = _('The SQL that was used to update the employee salary and failed was');
			    			$result = DB_query($sql,$ErrMsg,$DbgMsg);

				/* edit salary structure components*/

				// first delete all previous ones if payroll group id has changed
				if($_POST['PayrollGroupID'] != $old_payrollgroup_id)
				{
						$sql = "DELETE FROM hremployeesalarystructure_components WHERE salary_structure_id ='".$employee_salary_structure_id."'";
						$result = DB_query($sql);

						$sql= "SELECT payroll_category_name,payroll_category_type,payroll_category_code,hrpayrollcategories.payroll_category_id,payroll_category_value,payroll_category_type,additional_condition
									 FROM hrpayroll_groups_payroll_categories JOIN hrpayrollgroups ON hrpayroll_groups_payroll_categories.payroll_group_id = hrpayrollgroups.payrollgroup_id JOIN hrpayrollcategories ON hrpayroll_groups_payroll_categories.payroll_category_id = hrpayrollcategories.payroll_category_id
									 WHERE payroll_group_id='".$_POST['PayrollGroupID']."'";
									 $ErrMsg = _('The payroll groups categories could not be retrieved because');
									$DbgMsg = _('The SQL used to retrieve payroll group categories and failed was');
									$result = DB_query($sql,$ErrMsg,$DbgMsg);
									while ($MyRow = DB_fetch_array($result)) {
										//insert
										$RowCategoryCode = $MyRow['payroll_category_code'];
										$sql2 = "INSERT INTO hremployeesalarystructure_components (salary_structure_id,payroll_category_id,amount)
												VALUES(
															'" . $employee_salary_structure_id. "',
															'" . $MyRow['payroll_category_id']. "',
															'" . $_POST[$RowCategoryCode]. "')";
												$component_insert_result = DB_query($sql2);

									}
						}
						else if($_POST['PayrollGroupID'] == $old_payrollgroup_id)
						{
								//update old to new values in salary structure components.

								/* MUST ALSO CHECK IF NUMBER OF PAYGROLL CATEGORIES HAVE INCREASED OR REDUCED SO MAY BE DELETING AND WRITING AFRESH IS BETTER*/
								$sql= "SELECT payroll_category_name,payroll_category_type,payroll_category_code,hrpayrollcategories.payroll_category_id,payroll_category_value,payroll_category_type,additional_condition
											 FROM hrpayroll_groups_payroll_categories JOIN hrpayrollgroups ON hrpayroll_groups_payroll_categories.payroll_group_id = hrpayrollgroups.payrollgroup_id JOIN hrpayrollcategories ON hrpayroll_groups_payroll_categories.payroll_category_id = hrpayrollcategories.payroll_category_id
											 WHERE payroll_group_id='".$_POST['PayrollGroupID']."'";
											 $ErrMsg = _('The payroll groups categories could not be retrieved because');
											$DbgMsg = _('The SQL used to retrieve payroll group categories and failed was');
											$result = DB_query($sql,$ErrMsg,$DbgMsg);
											while ($MyRow = DB_fetch_array($result)) {
												//insert
												$RowCategoryCode = $MyRow['payroll_category_code'];
												$RowCategoryId = $MyRow['payroll_category_id'];
												$sql2 = "UPDATE hremployeesalarystructure_components
														SET 	amount= '" . $_POST[$RowCategoryCode]. "'
														WHERE salary_structure_id='".$employee_salary_structure_id."'
														AND payroll_category_id='".$RowCategoryId."'";
														$component_insert_result = DB_query($sql2);

											}
						}
						//unset($EmpID);
						prnMsg( _('Employee Salary Structure for') . ' ' . $EmpID . ' ' . _('have been updated'), 'success');
    		} else { //it is a NEW part
					/* add user if system access was enabled*/
					if(isset($_POST['EnableLogin']))
					{
						$sql = "INSERT INTO www_users (userid,
														realname,
														password,
														phone,
														email,
														defaultlocation,
														modulesallowed,
														fullaccess,
														theme,
														salesman,
														department)
										VALUES ('" . $_POST['EmpUsername'] . "',
											'" . $_POST['EmpFirstName'] ." ". $_POST['EmpLastName'] ."',
											'" . CryptPass($_POST['EmpPassword']) ."',
											'" . $_POST['MobileNumber'] . "',
											'" . $_POST['EmpEmail'] ."',
											'" . $_POST['DefaultLocation'] ."',
											'0,0,0,0,0,0,0,0,1,0,0,0,',
											'12',
											'xenos',
											'',
											'". $_POST['EmpDepartmentID'] ."')";
						$ErrMsg = _('The user could not be added because');
						$DbgMsg = _('The SQL that was used to insert the new user and failed was');
						$result = DB_query($sql,$ErrMsg,$DbgMsg);
						prnMsg( _('A new employee login has been created'), 'success' );
					}

					/* add employee */
					$EmpUsername = (isset($_POST['EmpUsername'])) ? $_POST['EmpUsername'] : 'NULL';
    			$sql = "INSERT INTO hremployees (empid,
												user_id,
												joining_date,
												first_name,
											
												gender,
												employee_position,
												employee_grade_id,
												job_title,
												resume,
												employee_department,
												date_of_birth,
												marital_status,
												father_name,
												mother_name,
												nationality,
												national_id,
												passport_no,
												home_address,
												mobile_phone,
												manager_id,
												email,
												spouse_name,
												spouse_phone_no,
												bank_name,
												
												bank_account_no)
    						VALUES (
									'" . $_POST['EmpIDname'] . "',
									'".$EmpUsername."',
									'" . $joiningdate->format('Y-m-d'). "',
									'" . $_POST['EmpFirstName'] . "',
								
									'" . $_POST['Gender'] . "',
									'" . $_POST['EmpPositionID'] . "',
									'" . $_POST['EmpGradeID'] . "',
    							'" . $_POST['EmpJobTitle'] . "',
    							'" . $_POST['EmpCV'] . "',
    							'" . $_POST['EmpDepartmentID'] . "',
									'" . $dob->format('Y-m-d') . "',
									'" . $_POST['MaritalStatus'] . "',
									'" . $_POST['EmpFatherName'] . "',
									'" . $_POST['EmpMotherName'] . "',
									'" . $_POST['Nationality'] . "',
    							'" . $_POST['NationalID'] . "',
    							'" . $_POST['PassportNO'] . "',
    							'" . $_POST['EmpAddress'] . "',
									'" . $_POST['MobileNumber'] . "',
									'" . $_POST['EmpManagerID'] . "',
    							'" . $_POST['EmpEmail'] . "',
									'" . $_POST['SpouseName'] . "',
									'" . $_POST['SpousePhoneNumber'] . "',
									'" . $_POST['EmpBankName'] . "',
								
									'" . $_POST['EmpBankAccount'] . "'
									 )";
    			$ErrMsg =  _('The employee could not be added because');
    			$DbgMsg = _('The SQL that was used to add the employee failed was');
    			$result = DB_query($sql, $ErrMsg, $DbgMsg);

					/* add salary structure*/
					$NewEmpID = DB_Last_Insert_ID($db,'hremployees', 'employee_id');
					$sql = "INSERT INTO hremployeesalarystructures (employee_id, payrollgroup_id, gross_pay, net_pay)
									VALUES(
											'" . $NewEmpID. "',
											'" . $_POST['PayrollGroupID'] . "',
											'" . $_POST['GROSS'] . "',
									    '" . $_POST['EmpNetPay'] . "')";
					$ErrMsg =  _('The employee salary structures could not be added because');
					$DbgMsg = _('The SQL that was used to add the employee salary structure failed was');
					$result = DB_query($sql, $ErrMsg, $DbgMsg);

					/* add salary structure components */
					$NewSalaryStructureId = DB_Last_Insert_ID($db,'hremployeesalarystructures', 'salary_structure_id');

					/* first get payroll category ids for selected payroll group*/
					$sql= "SELECT payroll_category_name,payroll_category_type,payroll_category_code,hrpayrollcategories.payroll_category_id,payroll_category_value,payroll_category_type,additional_condition
								 FROM hrpayroll_groups_payroll_categories JOIN hrpayrollgroups ON hrpayroll_groups_payroll_categories.payroll_group_id = hrpayrollgroups.payrollgroup_id JOIN hrpayrollcategories ON hrpayroll_groups_payroll_categories.payroll_category_id = hrpayrollcategories.payroll_category_id
								 WHERE payroll_group_id='".$_POST['PayrollGroupID']."'";
								 $ErrMsg = _('The payroll groups categories could not be retrieved because');
								$DbgMsg = _('The SQL used to retrieve payroll group categories and failed was');
								$result = DB_query($sql,$ErrMsg,$DbgMsg);
								while ($MyRow = DB_fetch_array($result)) {
									//insert
									$RowCategoryCode = $MyRow['payroll_category_code'];
									$sql2 = "INSERT INTO hremployeesalarystructure_components (salary_structure_id,payroll_category_id,amount)
											VALUES(
														'" . $NewSalaryStructureId. "',
														'" . $MyRow['payroll_category_id']. "',
														'" . $_POST[$RowCategoryCode]. "')";
											$component_insert_result = DB_query($sql2);

								}

    			if (DB_error_no() ==0) {
    				//$NewEmpID = DB_Last_Insert_ID($db,'hremployees', 'empid');
    				prnMsg( _('The new employee has been added to the database  :'),'success');
    				unset($_POST['EmpIDname']);
						unset($_POST['EmpFirstName']);
						
						unset($_POST['Nationality']);
						unset($_POST['Gender']);
						unset($_POST['EmpDepartmentID']);
						unset($_POST['EmpDOB']);
						unset($_POST['EmpJoiningDate']);
						unset($_POST['EmpPositionID']);
						unset($_POST['EmpGradeID']);
						unset($_POST['EmpJobTitle']);
						unset($_POST['EmpCV']);
						//unset($_POST['PassPortNo']);
						unset($_POST['NationalID']);
						unset($_POST['MaritalStatus']);
						unset($_POST['EmpFatherName']);
						unset($_POST['EmpMotherName']);
						unset($_POST['EmpEmail']);
						unset($_POST['EmpAddress']);
						unset($_POST['MobileNumber']);
						unset($_POST['EmpUsername']);
    			}//ALL WORKED SO RESET THE FORM VARIABLES

    			//$result = DB_Txn_Commit();
    		}
    	} else {
    		echo '<br />' .  "\n";
    		prnMsg( _('Validation failed, no updates or deletes took place'), 'error');
    	}

    }
    elseif (isset($_POST['delete']) AND mb_strlen($_POST['delete']) >1  or $_GET['delete']==1) {
    //the button to delete a selected record was clicked instead of the submit button

		$CancelDelete = 0;
		/*
			//GET EMPID using employeeId
			$sql_id = DB_query("SELECT empid FROM hremployees WHERE employee_id='".$EmpID."'");
			if(DB_num_rows($sql_id) < 1)
			{
				$CancelDelete =1; //
    		prnMsg(_('The employee cannot be found in the database'),'error');
				exit();
			}
			else if(DB_num_rows($sql_id) > 0)
			{
				$id_row = DB_fetch_array($sql_id);
				$EmpID = $id_row['empid'];
			}*/
    	//what validation is required before allowing deletion of employee ....  maybe there should be no deletion option?
    	$result = DB_query("SELECT payslip_id,
    								employee_id
    						FROM hremployeepayslips
    						WHERE employee_id='" . $EmpID . "'");

    	if (DB_num_rows($result) > 0) {
    		$CancelDelete =1; //cannot delete employee already paid
				$InputError = 1;
    		prnMsg(_('The employee has already been paid - only employees who have never been paid can be deleted'),'error');
				exit();
    	}
    	$result = DB_query("SELECT * FROM hremployeeleaves WHERE leaveemployee_id='" . $EmpID . "'");
    	if (DB_num_rows($result) > 0){
    		$CancelDelete =1; /*cannot delete employee with leave */
    		prnMsg(_('The employee already applied for leave. The employee can only be deleted if he has no leave'),'error');
				exit();
    	}

    	if ($CancelDelete==0) {


    		$sql="DELETE FROM hremployees WHERE employee_id='" . $EmpID . "'";
    		$result=DB_query($sql, _('Could not delete the employee record'),'',true);


    		// Delete the EmployeeImage
    		foreach ($SupportedImgExt as $ext) {
    			$file = $_SESSION['part_pics_dir'] . '/EMP_' . $EmpID . '.' . $ext;
    			if (file_exists ($file) ) {
    				unlink($file);
    			}
    		}

    		prnMsg(_('Deleted the employee  record for employee id' ) . ' ' . $EmpID );
				unset($_POST['EmpIDname']);
				unset($_POST['EmpFirstName']);
			
				unset($_POST['Nationality']);
				unset($_POST['Gender']);
				unset($_POST['EmpDepartmentID']);
				unset($_POST['EmpDOB']);
				unset($_POST['EmpJoiningDate']);
				unset($_POST['EmpPositionID']);
				unset($_POST['EmpGradeID']);
				unset($_POST['EmpJobTitle']);
				unset($_POST['EmpCV']);
				//unset($_POST['PassPortNo']);
				unset($_POST['NationalID']);
				unset($_POST['MaritalStatus']);
				unset($_POST['EmpFatherName']);
				unset($_POST['EmpMotherName']);
				unset($_POST['EmpEmail']);
				unset($_POST['EmpAddress']);
				unset($_POST['MobileNumber']);
				unset($_POST['EmpUsername']);
				unset($_POST['PayrollGroupID']);
    		unset($EmpID);


    	} //end if OK Delete Asset
}


echo '<form id="EmpForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
      <div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';


if (!isset($EmpID) OR $EmpID=='') {

/*If the page was called without $AssetID passed to page then assume a new asset is to be entered other wise the form showing the fields with the existing entries against the asset will show for editing with a hidden AssetID field. New is set to flag that the page may have called itself and still be entering a new asset, in which case the page needs to know not to go looking up details for an existing asset*/

	$New = 1;
	echo '<tr><td><input type="hidden" name="New" value="" /></td></tr>';

	$_POST['LongDescription'] = '';
	$_POST['Description'] = '';
	$_POST['AssetCategoryID']  = '';
	$_POST['SerialNo']  = '';
	$_POST['AssetLocation']  = '';
	$_POST['DepnType']  = '';
	$_POST['BarCode']  = '';
	$_POST['DepnRate']  = 0;

} elseif ($InputError!=1) { // Must be modifying an existing item and no changes made yet - need to lookup the details

	$sql = "SELECT hremployees.*,salary_structure_id,gross_pay,net_pay,payrollgroup_id
			FROM hremployees JOIN hremployeesalarystructures on hremployees.empid = hremployeesalarystructures.employee_id
			WHERE hremployees.employee_id ='" . $EmpID . "'";

	$result = DB_query($sql);
	$AssetRow = DB_fetch_array($result);
	$_POST['EmpID'] = $AssetRow['empid'];
	$_POST['EmpIDname'] = $AssetRow['employee_id'];
	$_POST['EmpFirstName'] = $AssetRow['first_name'];
	$EmpFirstName = $AssetRow['first_name'];
//	$_POST['EmpLastName'] = $AssetRow['last_name'];
//	$EmpLastName = $AssetRow['last_name'];
//	$_POST['EmpMiddleName']  = $AssetRow['middle_name'];
	//$EmpMiddleName = $AssetRow['middle_name'];
	$_POST['Gender']  = $AssetRow['gender'];
	$_POST['EmpDepartmentID']  = $AssetRow['employee_department'];
	$_POST['EmpDOB']  = $AssetRow['date_of_birth'];
	$_POST['EmpJoiningDate']  = $AssetRow['joining_date'];
	$_POST['EmpPositionID']  = $AssetRow['employee_position'];
	$_POST['EmpGradeID']  = $AssetRow['employee_grade_id'];
	$_POST['EmpJobTitle']  = $AssetRow['job_title'];
	$_POST['EmpCV']  = $AssetRow['resume'];
	//$_POST['PassPortNo']  = $AssetRow['passport_no'];
	$_POST['NationalID']  = $AssetRow['national_id'];
	$_POST['Nationality']  = $AssetRow['nationality'];
	$_POST['MaritalStatus']  = $AssetRow['marital_status'];
	$_POST['EmpFatherName']  = $AssetRow['father_name'];
	$_POST['EmpMotherName']  = $AssetRow['mother_name'];
	$_POST['EmpEmail']  = $AssetRow['email'];
	$_POST['EmpAddress']  = $AssetRow['home_address'];
	$_POST['MobileNumber']  = $AssetRow['mobile_phone'];
	$_POST['EmpStatus'] = $AssetRow['status'];
	$_POST['GROSS']  = $AssetRow['gross_pay'];
	$_POST['EmpNetPay']  = $AssetRow['net_pay'];
	$_POST['SpouseName'] = $AssetRow['spouse_name'];
	$_POST['SpousePhoneNumber'] = $AssetRow['spouse_phone_no'];
	$_POST['EmpBankName'] = $AssetRow['bank_name'];
	$_POST['EmpBankAccount'] = $AssetRow['bank_account_no'];
	//$_POST['EmpSocialSecurityNo'] = $AssetRow['social_security_no'];
	$_POST['EmpManagerID'] = $AssetRow['manager_id'];

	/*bug for editing employee*/
	if(!isset($_POST['UpdatePayrollGroup']))
	{
		$_POST['PayrollGroupID']  = $AssetRow['payrollgroup_id'];
	}

/* get salary component values*/
$salaryStructureId = $AssetRow['salary_structure_id'];
$sql = "SELECT hremployeesalarystructure_components.* ,payroll_category_code from hremployeesalarystructure_components JOIN hrpayrollcategories ON hremployeesalarystructure_components.payroll_category_id = hrpayrollcategories.payroll_category_id
				WHERE salary_structure_id =".$salaryStructureId."";
$result = DB_query($sql);
while ($myrow = DB_fetch_array($result)) {
	$salaryStructureCode = $myrow['payroll_category_code'];
	$_POST[$salaryStructureCode] = $myrow['amount'];
}

	echo '<tr>
			<td>' . _('Employee Id') . ':</td>
			<td>' . $EmpID . '</td>
		</tr>';
	echo '<tr><td><input type="hidden" name="EmpID" value="'.$EmpID.'"/></td></tr>';

} else { // some changes were made to the data so don't re-set form variables to DB ie the code above
	echo '<tr>
			<td>' . _('Employee Id') . ':</td>
			<td>' . $EmpID . '</td>
		</tr>';
	echo '<tr><td><input type="hidden" name="EmpID" value="' . $EmpID . '"/></td></tr>';
}

if (isset($AssetRow['disposaldate']) AND $AssetRow['disposaldate'] !='0000-00-00'){
	echo '<tr>
			<td>' . _('Asset Already disposed on') . ':</td>
			<td>' . ConvertSQLDate($AssetRow['disposaldate']) . '</td>
		</tr>';
}

if (isset($_POST['Description'])) {
	$Description = $_POST['Description'];
} else {
	$Description ='';
}
//General Details
echo '<tr><td><h3>基本资料</h3></td></tr>
			<tr><td colspan="2"><hr></td></tr>';
echo '<tr>
		<td>员工工号:</td>
		<td><input ' . (in_array('EmpIDname',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . _('Enter the employee id of the employeer. it should be unique.') . '" name="EmpIDname" maxlength="50" value="' . $_POST['EmpIDname'] . '" /></td>
	</tr>';
echo '<tr>
  		<td>员工姓名:</td>
  		<td><input ' . (in_array('EmpFirstName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . _('Enter the employee first name.') . '" name="EmpFirstName" maxlength="50" value="' . $_POST['EmpFirstName'] . '" /></td>
	  </tr>';

echo '<td>' . _('Gender') . ':</td>
		<td><select name="Gender">';

	if (!isset($_POST['Gender'])){
		$_POST['Gender'] = '1';
	}
	if ($_POST['Gender']=='1'){
		echo '<option selected="selected" value="1">男</option>';
		echo '<option value="0">女</option>';
	} else {
		echo '<option value="1">男</option>';
		echo '<option selected="selected" value="0">女</option>';
	}
	$sql = "SELECT departmentid, description FROM departments";
	$ErrMsg = _('The departments could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve departments and failed was');
	$result = DB_query($sql,$ErrMsg,$DbgMsg);
	if(!isset($_POST['Nationality'])){
		$_POST['Nationality']=$_SESSION['CountryOfOperation'];
	}
	echo '<tr>
	      		<td>' . _('National ID') . ':</td>
	      		<td><input ' . (in_array('NationalID',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee\'s national identification.') . '" name="NationalID" maxlength="50" value="' . $_POST['NationalID'] . '" /></td>
			  </tr>';			
	echo'<tr>
			<td>' . _('Employee Nationality') . ':</td>
			<td><select required="required" name="Nationality">';
		foreach ($CountriesArray as $CountryEntry => $CountryName) {
			if(isset($_POST['Nationality']) AND (strtoupper($_POST['Nationality']) == strtoupper($CountryEntry))) {
				echo '<option selected="selected" value="' . $CountryEntry . '">' . $CountryName . '</option>';
			} else {
				echo '<option value="' . $CountryEntry . '">' . $CountryName . '</option>';
			}
		}
		echo '</select></td>
		</tr>';
	echo '<tr>
			<td>' . _('Department') . ':</td>
			<td><select name="EmpDepartmentID">';

	while ($myrow=DB_fetch_array($result)){
		if ($_POST['EmpDepartmentID']==$myrow['departmentid']){
			echo '<option selected="selected" value="' . $myrow['departmentid'] .'">' . $myrow['description'] . '</option>';
		} else {
			echo '<option value="' . $myrow['departmentid'] .'">' . $myrow['description'] . '</option>';
		}
	}

	if (isset($EmpID)) {
		$dob= $_POST['EmpDOB'];
		$joiningdate= $_POST['EmpJoiningDate'];
	}else{
	$dob=date('Y-m-d');
		$joiningdate=date('Y-m-d');
	}

	echo '</select>
		<a target="_blank" href="'. $RootPath . '/Departments.php">' . ' ' . _('Add Department') . '</a></td>
		</tr>
		<tr>
			<td>' . _('Date of Birth') . ':</td>
			<td><input ' . (in_array('EmpDOB',$Errors) ?  'class="inputerror date"' : 'class="datepicker"' ) .'  type="text"  name="EmpDOB"   maxlength="10" value="' . ConvertSQLDate($dob) . '"   /></td>
		</tr>
		<tr>
			<td>' . _('joining Date') . ':</td>
			<td><input ' . (in_array('EmpJoiningDate',$Errors) ?  'class="inputerror"' : '' ) .'  type="text" class="datepicker" name="EmpJoiningDate" maxlength="10" value="' . ConvertSQLDate($joiningdate) . '"  /></td>
		</tr>
		<tr>';
	echo '<tr>
			<td>' . _('Employee Position') . ':</td>
			<td><select name="EmpPositionID">';

	$sql = "SELECT employee_position_id, position_name FROM hremployeepositions";
	$ErrMsg = _('The job positions could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve job positions and failed was');
	$result = DB_query($sql,$ErrMsg,$DbgMsg);

	while ($myrow=DB_fetch_array($result)){
		if (!isset($_POST['EmpPositionID']) or $myrow['employee_position_id']==$_POST['EmpPositionID']){
			echo '<option selected="selected" value="'. $myrow['employee_position_id'] . '">' . $myrow['position_name'] . '</option>';
		} else {
			echo '<option value="'. $myrow['employee_position_id'] . '">' . $myrow['position_name']. '</option>';
		}
		$position=$myrow['employee_position_id'];
	}
	echo '</select><a target="_blank" href="'. $RootPath . '/HrEmployeePositions.php">' . ' ' . _('Add or Modify position') . '</a></td></tr>';
	if (!isset($_POST['EmpPositionID'])) {
		$_POST['EmpPositionID']=$position;
	}
	echo '<tr>
			<td>' . _('Employee Grade') . ':</td>
			<td><select name="EmpGradeID"><option value="0">Select Grade</option>';

	$sql = "SELECT employee_grading_id, grading_name FROM hremployeegradings";
	$ErrMsg = _('The Job Grades could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve job grades and failed was');
	$result = DB_query($sql,$ErrMsg,$DbgMsg);

	while ($myrow=DB_fetch_array($result)){
		if (!isset($_POST['EmpGradeID']) or $myrow['employee_grading_id']==$_POST['EmpGradeID']){
			echo '<option selected="selected" value="'. $myrow['employee_grading_id'] . '">' . $myrow['grading_name'] . '</option>';
		} else {
			echo '<option value="'. $myrow['employee_grade_id'] . '">' . $myrow['grading_name']. '</option>';
		}
		$grade=$myrow['employee_position_id'];
	}
	echo '</select><a target="_blank" href="'. $RootPath . '/HrEmployeeGrades.php">' . ' ' . _('Add or Modify grade') . '</a></td></tr>';
	if (!isset($_POST['EmpGradeID'])) {
		$_POST['EmpGradeID']=$grade;
	}

	echo '<tr>
			<td>' . _('Reports To') . ':</td>
			<td><select name="EmpManagerID"><option value="0">Select Manager</option>';

	$sql = "SELECT empid,employee_id, first_name, last_name FROM hremployees where employee_id <>'".$EmpID."'";
	$ErrMsg = _('The Managers could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve managers and failed was');
	$result = DB_query($sql,$ErrMsg,$DbgMsg);

	while ($myrow=DB_fetch_array($result)){
		if ($myrow['empid']==$_POST['EmpManagerID']){
			echo '<option selected="selected" value="'. $myrow['empid'] . '">' . $myrow['first_name'] . ' '.$myrow['last_name'].'</option>';
		} else {
			echo '<option value="'. $myrow['empid'] . '">' . $myrow['first_name'] . ' '.$myrow['last_name'].'</option>';
		}

	}
	echo '</select></td></tr>';

	echo '<tr>
	  		<td>' . _('Job Title') . ':</td>
	  		<td><input ' . (in_array('EmpJobTitle',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . _('Enter the employee job Title.') . '" name="EmpJobTitle" maxlength="50" value="' . $_POST['EmpJobTitle'] . '" /></td>
	  	</tr>';
			echo '<tr>
					<td>' . _('CV Summary') . ' (' . _('Experience') . '):</td>
					<td><textarea ' . (in_array('EmpCV',$Errors) ?  'class="texterror"' : '' ) .'  name="EmpCV"  title="' . _('Enter the employee cv summary.i.e experience details ') . '" cols="40" rows="4">' . stripslashes($_POST['EmpCV']) . '</textarea></td>
				</tr>';
	if(isset($EmpID))
	{
		echo '<tr>
				<td>Employee Status :</td>
					<td><select name ="EmpStatus">
								<option value="1" '.(($_POST['EmpStatus'] == 1) ? 'selected' : '' ). '>Employed</option>
								<option value="0" '.(($_POST['EmpStatus'] == 0) ? 'selected' : '').'>No longer with us</option>
							</select>
					</td>
					</tr>';
	}
	echo '</select></td>';
	//Personal Details
	echo '<tr><td><h3>个人资料</h3></td></tr>
				<tr><td colspan="2"><hr></td></tr>';
				echo '	<td>' . _('Marital Status') . ':</td>
					<td><select id="marital_status" name="MaritalStatus">';

				if (!isset($_POST['MaritalStatus'])){
				$_POST['MaritalStatus'] = 'single';
				}
				if ($_POST['MaritalStatus']=='single'){
				echo '<option selected="selected" value="single">' . _('Single') . '</option>';
				echo '<option value="married">' . _('Married') . '</option>';
				} else {
				echo '<option value="single">' . _('Single') . '</option>';
				echo '<option selected="selected" value="married">' . _('Married') . '</option>';
				}

				echo '</select></td>
				</tr>';
	echo '<tr class="spouse-details">
						 <td>配偶姓名:</td>
						 <td><input ' . (in_array('SpouseName',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee spouse name.') . '" name="SpouseName" maxlength="50" value="' . $_POST['SpouseName'] . '"  /></td>
				</tr>';
	echo '<tr class="spouse-details">
						 <td>配偶电话:</td>
						 <td><input ' . (in_array('SpousePhoneNumber',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee spouse number.') . '" name="SpousePhoneNumber" maxlength="50" value="' . $_POST['SpousePhoneNumber'] . '"  /></td>
				</tr>';
	echo '<tr>
	  		<td>父亲名字:</td>
	  		<td><input ' . (in_array('EmpFatherName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" title="' . _('Enter the employee\'s father name.') . '" name="EmpFatherName" maxlength="50" value="' . $_POST['EmpFatherName'] . '" /></td>
	  	</tr>';
	echo '<tr>
			  		<td>母亲名字:</td>
			  		<td><input ' . (in_array('EmpMotherName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" title="' . _('Enter the employee\'s mothers name.') . '" name="EmpMotherName" maxlength="50" value="' . $_POST['EmpMotherName'] . '" /></td>
			  	</tr>';

if (!isset($New) ) { //ie not new at all!

	echo '<tr>
			<td>' .  _('Employee Photo (' . implode(", ", $SupportedImgExt) . ')') . ':</td>
			<td><input type="file" id="EmployeePicture" name="EmployeePicture" />
			<br /><input type="checkbox" name="ClearImage" id="ClearImage" value="1" > '._('Clear Image').'
			</td>';

	$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/EMP_' . $EmpID . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE)));
	if (extension_loaded ('gd') && function_exists ('gd_info') && file_exists ($imagefile) ) {
		$AssetImgLink = '<img src="GetStockImage.php?automake=1&textcolor=FFFFFF&bgcolor=CCCCCC'.
			'&StockID='.urlencode('EMP_' . $EmpID).
			'&text='.
			'&width=64'.
			'&height=64'.
			'" />';
	} else if (file_exists ($imagefile)) {
		$AssetImgLink = '<img src="' . $imagefile . '" height="64" width="64" />';
	} else {
		$AssetImgLink = _('No Image');
	}

	if ($AssetImgLink!=_('No Image')) {
		echo '<td>' . _('Image') . '<br />' . $AssetImgLink . '</td></tr>';
	} else {
		echo '</td></tr>';
	}

	// EOR Add Image upload for New Item  - by Ori
} //only show the add image if the asset already exists - otherwise AssetID will not be set - and the image needs the AssetID to save

if (isset($_POST['ClearImage']) ) {
	foreach ($SupportedImgExt as $ext) {
		$file = $_SESSION['part_pics_dir'] . '/EMP_' . $EmpID . '.' . $ext;
		if (file_exists ($file) ) {
			//workaround for many variations of permission issues that could cause unlink fail
			@unlink($file);
			if(is_file($imagefile)) {
               prnMsg(_('You do not have access to delete this employee image file.'),'error');
			} else {
				$AssetImgLink = _('No Image');
			}
		}
	}
}
echo '<tr><td><h3>Contact/Login Details</h3></td></tr>
			<tr><td colspan="2"><hr></td></tr>';
			echo '<tr>
			          <td>' . _('Email') . ':</td>
			          <td><input ' . (in_array('EmpEmail',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee email.') . '" name="EmpEmail" maxlength="50" value="' . $_POST['EmpEmail'] . '" /></td>
			      </tr>';
			echo '<tr>
			       <td>' . _('Phone Number') . ':</td>
			         <td><input required="required" ' . (in_array('MobileNumber',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee phone number.') . '" name="MobileNumber" maxlength="13" value="' . $_POST['MobileNumber'] . '" /></td>
			      </tr>';
			echo '<tr>
					<td>家庭住址:</td>
					<td><textarea ' . (in_array('EmpAddress',$Errors) ?  'class="texterror"' : '' ) .'  name="EmpAddress" required="required" title="' . _('Enter the employee address. ') . '" cols="40" rows="4">' . stripslashes($_POST['EmpAddress']) . '</textarea></td>
				</tr>';
	if(!isset($EmpID)){
		echo '<tr>
				<td>是否允许登陆系统:</td>
				<td><input id="enable-login-checkbox" value="yes" name="EnableLogin" type="checkbox" value="' . $_POST['EnableLogin'] . '"  /></td>
			</tr>';
		echo '<tr class="login-details">
						<td>登录名 (只允许字母和数字):</td>
						<td><input ' . (in_array('EmpUsername',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee username.') . '" name="EmpUsername" maxlength="50" value="' . $_POST['EmpUsername'] . '"  /></td>
			</tr>';
		echo '<tr class="login-details">
					<td>' . _('Password') . ' (' . _('***') . '):</td>
					<td><input ' . (in_array('EmpPassword',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee password.') . '" name="EmpPassword" maxlength="50" value="' . $_POST['EmpPassword'] . '"  /></td>
					</tr>';
		echo '<tr class="login-details">
					<td>员工工作地:</td>
					<td><select name="DefaultLocation">';

					$sql = "SELECT loccode, locationname FROM locations";
					$ErrMsg = _('The locations could not be retrieved because');
					$DbgMsg = _('The SQL used to retrieve locations and failed was');
					$result = DB_query($sql,$ErrMsg,$DbgMsg);

					while ($myrow=DB_fetch_array($result)){
						if (!isset($_POST['DefaultLocation']) or $myrow['loccode']==$_POST['DefaultLocation']){
							echo '<option selected="selected" value="'. $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
						} else {
							echo '<option value="'. $myrow['loccode'] . '">' . $myrow['locationname']. '</option>';
						}
						$locationCode=$myrow['loccode'];
					}
					echo '</select><a target="_blank" href="'. $RootPath . '/Locations.php">' . ' ' . _('Add or Modify location') . '</a></td></tr>';
					if (!isset($_POST['DefaultLocation'])) {
						$_POST['DefaultLocation']=$locationCode;
					}
}
		echo '<tr><td><h3>薪资明细</h3></td></tr>
				<tr><td colspan="2"><hr></td></tr>';
		echo '<tr>
				<td>银行账户名:</td>
				<td><input ' . (in_array('EmpBankName',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee bank.') . '" name="EmpBankName" maxlength="50" value="' . $_POST['EmpBankName'] . '"  /></td>
		     </tr>';
	    echo '<tr>
				<td>银行账号:</td>
				<td><input ' . (in_array('EmpBankAccount',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  title="' . _('Enter the employee bank account number.') . '" name="EmpBankAccount" maxlength="50" value="' . $_POST['EmpBankAccount'] . '"  /></td>
		     </tr>';
		echo '<tr>
				<td>工资组:</td>
				<td><select name="PayrollGroupID" onchange="ReloadForm(EmpForm.UpdatePayrollGroup)"><option value="">Select Paygroup</option>';

		    $sql = "SELECT * FROM hrpayrollgroups";
				$ErrMsg = _('The payroll groups could not be retrieved because');
				$DbgMsg = _('The SQL used to retrieve payroll groups and failed was');
				$result = DB_query($sql,$ErrMsg,$DbgMsg);

				while ($myrow=DB_fetch_array($result)){
						if($myrow['payrollgroup_id']==$_POST['PayrollGroupID'] )
						{
							echo '<option selected="selected" value="'. $myrow['payrollgroup_id'] . '">' . $myrow['payrollgroup_name'] . '</option>';
						}
						else {
							echo '<option value="'. $myrow['payrollgroup_id'] . '">' . $myrow['payrollgroup_name']. '</option>';
						}

				}
				echo '</select><a target="_blank" href="'. $RootPath . '/HrPayrollGroups.php">' . ' ' . _('Add or Modify payroll group') . '</a></td></tr>';
				/*if (!isset($_POST['PayrollGroupID'])) {
					$_POST['PayrollGroupID']=$payroll_group;
				}*/
		if(isset($_POST['PayrollGroupID']))
		{
			$sql= "SELECT payroll_category_name,payroll_category_type,payroll_category_code,payroll_category_value,payroll_category_type,additional_condition
							FROM hrpayroll_groups_payroll_categories JOIN hrpayrollgroups ON hrpayroll_groups_payroll_categories.payroll_group_id = hrpayrollgroups.payrollgroup_id JOIN hrpayrollcategories ON hrpayroll_groups_payroll_categories.payroll_category_id = hrpayrollcategories.payroll_category_id
							WHERE payroll_group_id='".$_POST['PayrollGroupID']."'";
							$ErrMsg = _('The payroll groups categories could not be retrieved because');
						$DbgMsg = _('The SQL used to retrieve payroll group categories and failed was');
						$result_categories = DB_query($sql,$ErrMsg,$DbgMsg);
			echo '<tr>
			         <td>.</td>
					 <td><h5>Earnings</h5></td>
					 </tr>';
						while ($myrow=DB_fetch_array($result_categories)){
							$CategoryCodeValue = $myrow['payroll_category_code'];
							if($myrow['payroll_category_type'] == 1){
									if(is_numeric($myrow['payroll_category_value'])){
									echo '<tr>
														<td>'.$myrow['payroll_category_name'].'</td>
														<td><input name="'.$CategoryCodeValue.'"  data-id="" id="'.$myrow['payroll_category_code'].'"' . (in_array('PayrollCategoryInput',$Errors) ?  'class="inputerror"' : 'class="value-input earning-input"' ) .' type="text"  title="enter ' . $myrow['payroll_category_name'] . ' value" name="'.$myrow['payroll_category_code'].'" maxlength="13" value="' . $_POST[$CategoryCodeValue] . '" /></td>
												</tr>';
									}else {
										echo '<tr>
															<td>'.$myrow['payroll_category_name'].'</td>
															<td><input name="'.$CategoryCodeValue.'"  readonly data-id="" id="'.$myrow['payroll_category_code'].'"' . (in_array('PayrollCategoryInput',$Errors) ?  'class="inputerror"' : 'class="formula-input earning-input"' ) .' type="text"   maxlength="13" value="' . $_POST[$CategoryCodeValue] . '" /> &nbsp;<button type="button" class="calculate_this_field">L</button></td>
													</tr>';
									}
								}
						}
						echo '<tr>
								<td>.</td>
								<td><h5>Deductions</h5></td>
								</tr>';
						DB_data_seek($result_categories, 0 );
						while ($myrow=DB_fetch_array($result_categories)){
							if($myrow['payroll_category_type'] == 0){
								$CategoryCodeValue = $myrow['payroll_category_code'];
									if(is_numeric($myrow['payroll_category_value'])){
									echo '<tr>
														<td>'.$myrow['payroll_category_name'].'</td>
														<td><input name="'.$CategoryCodeValue.'"  data-id="" id="'.$myrow['payroll_category_code'].'"' . (in_array('PayrollCategoryInput',$Errors) ?  'class="inputerror"' : 'class="value-input deduction-input"' ) .' type="text"  title="enter ' . $myrow['payroll_category_name'] . ' value" name="'.$myrow['payroll_category_code'].'" maxlength="13" value="' . $_POST[$CategoryCodeValue] . '" /></td>
												</tr>';
									}else {
										echo '<tr>
															<td>'.$myrow['payroll_category_name'].'</td>
															<td><input name="'.$CategoryCodeValue.'"   data-id="'.$myrow['payroll_category_value'].'" id="'.$myrow['payroll_category_code'].'"' . (in_array('PayrollCategoryInput',$Errors) ?  'class="inputerror"' : 'class="formula-input deduction-input"' ) .' type="text"   maxlength="13" value="' . $_POST[$CategoryCodeValue] . '" /> &nbsp; <button type="button" class="calculate_this_field">calculate</button></td>
													</tr>';
									}
								}
						}
						echo '<tr>
								 <td>.</td>
								 <td><h5>Net Income</h5></td>
								 </tr>';
						echo '<tr>
								<td>Employee Net Salary</td>
								<td><input ' . (in_array('EmpNetPay',$Errors) ?  'class="inputerror"' : '' ) .' type="text"  name="EmpNetPay" maxlength="13" value="'.$_POST['EmpNetPay'].'" />&nbsp;<button type="button" class="calculate_net_income"> Update NetPay</button></td>
									</tr>';
					}
/*					elseif(isset($_POST['PayrollGroupID']) && isset($EmpID))
					{

					}*/
			echo '</table>';

echo '<script>
					$( document ).ready(function() {
						var marital_status = "'.$_POST["MaritalStatus"].'";
						var enable_login = "'.$_POST["EnableLogin"].'";
						if(marital_status != "married")
						{
							$(".spouse-details").hide();
						}
						if(enable_login != "yes")
						{
							$(".login-details").hide();
						}
						var date_format = "'.$_SESSION["DefaultDateFormat"].'";
						var year_format = date_format.replace("Y", "yy");
						var month_format = year_format.replace("m", "mm");
						var new_date_format = month_format.replace("d", "dd");
						$(".datepicker").datepicker({
								changeMonth: true,
								changeYear: true,
								showButtonPanel: true,
								dateFormat: new_date_format
						});
							$(".calculate_this_field").click(function(){
									//get formula
									var current_field = $(this).closest("tr").find("input");
									var formula = current_field.attr("data-id");
									// get category
									var n = formula.indexOf("GROSS");
									var j = formula.lastIndexOf("CAT");
									var category = formula.substr(n,5);
									var value_input = $("#"+category).val();
									var expression = formula.replace(category,value_input);
									current_field.val(eval(expression));

							});

							$(".calculate_net_income").on("click",function(){
										var current_field = $(this).closest("tr").find("input");
										var i = 0;
										var j = 0;
										$("#EmpForm input[type=text]").each(function() {
												j = $(this).val();
												if($(this).hasClass("earning-input"))
												{

													i = parseInt(i)+parseInt(j);

												}
												else if($(this).hasClass("deduction-input"))
												{
													i = parseInt(i)-parseInt(j);

												}
			        			});

										current_field.val(i);
							});
							$("#enable-login-checkbox").change(function(){
									$(".login-details").toggle();
							});
							$("#marital_status").change(function(){
									var status = $(this).val();
									if(status == "married")
									{
										$(".spouse-details").show();
									}
									else if(status == "single")
									{
										$(".spouse-details").hide();
									}
							});
					});


			</script>';

if (isset($AssetRow)){
	echo '<table>
		<tr>
			<th colspan="2">' . _('Employee  Summary') . '</th>
		</tr>
		<tr>
			<td>' . _('Total Leaves Taken') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['cost'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>
		<tr>
			<td>' . _('Total Attendance') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';

		echo'<tr>
			<td>' . _('Total Salary Paid') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['cost']-$AssetRow['accumdepn'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';
		echo'<tr>
			<td>' . _('Total Social Security Paid') . ':</td>
			<td class="number">' . locale_number_format($AssetRow['disposalproceeds'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td>
		</tr>';



	/*Get the last period depreciation (depn is transtype =44) was posted for */
	$result = DB_query("SELECT periods.lastdate_in_period,
								max(fixedassettrans.periodno)
					FROM fixedassettrans INNER JOIN periods
					ON fixedassettrans.periodno=periods.periodno
					WHERE transtype=44
					GROUP BY periods.lastdate_in_period
					ORDER BY periods.lastdate_in_period DESC");

	$LastDepnRun = DB_fetch_row($result);
	if(DB_num_rows($result)==0){
		$LastRunDate = _('Not Yet Paid');
	} else {
		$LastRunDate = ConvertSQLDate($LastDepnRun[0]);
	}
	echo '<tr>
			<td>' . _('Last Paid Date') . ':</td>
			<td>' . $LastRunDate . '</td>
		</tr>
		</table>';
}

if (isset($New)) {
	echo '<div class="centre">
			<br />
			<input type="submit" name="submit" value="' . _('Insert New Employee') . '" />';
			echo '<input type="submit" name="UpdatePayrollGroup" style="visibility:hidden;width:1px" value="' . _('Payroll') . '" />';
} else {
	echo '<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Update') . '" />
			<input type="submit" name="UpdatePayrollGroup" style="visibility:hidden;width:1px" value="' . _('Payroll') . '" />
		</div>';
		prnMsg( _('Only click the Delete button if you are sure you wish to delete the employee. Only employees who have not yet been paid can be deleted'), 'warn', _('WARNING'));
	echo '<br />
		<div class="centre">
			<input type="submit" name="delete" value="' . _('Delete This Employee') . '" onclick="return confirm(\'' . _('Are You Sure? employees who have not yet been paid can be deleted') . '\');" />';
}

echo '</div>
      </div>
	</form>';

include('includes/footer.php');
?>
