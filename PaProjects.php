<?php

/* $Id: HrEmployees.php 7751 2018-04-13 16:34:26Z raymond $ */
/*	Add and Edit Employee*/

include('includes/session.php');
$Title = _('Projects');

$ViewTopic = 'Projects';
$BookMark = 'Projects';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');

echo '<a href="' . $RootPath . '/PaSelectProject.php">' . _('Search For Project') . '</a><br />' . "\n";

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="' .
		_('Project data') . '" alt="" />' . ' ' . $Title . '</p>';
    /* If this form is called with the EmpID then it is assumed that the employee is to be modified  */
    if (isset($_GET['ProjectID'])){
    	$ProjectID =$_GET['ProjectID'];
    } elseif (isset($_POST['ProjectID'])){
    	$ProjectID =$_POST['ProjectID'];
    } elseif (isset($_POST['Select'])){
    	$ProjectID =$_POST['Select'];
    } else {
    	//$EmpID = '';
		//	$_POST['BeginDate'] = date($_SESSION['DefaultDateFormat']);
	//		$_POST['EndDate'] = date($_SESSION['DefaultDateFormat']);

    }


		// BEGIN: Bank accounts  array.
		$BankAccount = array();
		$Query = "SELECT 	accountcode, bankaccountname FROM bankaccounts ";
		$Result = DB_query($Query);
		while ($Row = DB_fetch_array($Result)) {
			$BankAccount[$Row['accountcode']] = $Row['bankaccountname'];
		}


    if (isset($_POST['submit'])) {

      //initialise no input errors assumed initially before we test

      /* actions to take once the user has clicked the submit button
      ie the page has called itself with some user input */

      //first off validate inputs sensible
      $i=1;

      if (!isset($_POST['ProjectID']) or mb_strlen($_POST['ProjectID']) > 50 OR mb_strlen($_POST['ProjectID'])==0) {
    		$InputError = 1;
    		prnMsg (_('The project_id must be entered and be fifty characters or less long. It cannot be a zero length string either, employeeId is required'),'error');
    		$Errors[$i] = 'ProjectID';
    		$i++;
    	}
    	if (mb_strlen($_POST['BeginDate'])==0) {
    		$InputError = 1;
    		prnMsg (_('The date the project is to start cannot be blank, a project start date is required'),'error');
    		$Errors[$i] = 'EndDate';
    		$i++;
    	}
      if (trim($_POST['ProjectType']) == '') {
    		$InputError = 1;
    		prnMsg(_('There are no project Types defined. All projects must have a type'),'error');
    		$Errors[$i] = 'ProjectType';
    		$i++;
    	}
      if (trim($_POST['ProjectName'])==''){
    		$InputError = 1;
    		prnMsg(_('Project name cannot be blank. ,'),'error');
    		$Errors[$i] = 'ProjectName';
    		$i++;
    	}
			if (trim($_POST['ProjectDescription'])==''){
    		$InputError = 1;
    		prnMsg(_('Project description cannot be blank. ,'),'error');
    		$Errors[$i] = 'ProjectDescription';
    		$i++;
    	}
			$begindate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['BeginDate']);
			$enddate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['EndDate']);


      if ($InputError !=1){

    		if ($_POST['submit']==_('Update')) { /*so its an existing one */
					$parent_project = (mb_strlen($_POST['ParentProjectID']) > 0 ) ? $_POST['ParentProjectID'] : NULL;
					$billable_expense = (isset($_POST['BillableExpense'])) ? 1 : 0;
					$billable_ap = (isset($_POST['BillableAp'])) ? 1 : 0;

          $sql = "UPDATE paprojects
    					SET project_id='" .mb_strtoupper($_POST['ProjectID']) . "',
								project_name='" . $_POST['ProjectName'] . "',
								project_category='".$_POST['ProjectCategory']."',
								project_type='" . $_POST['ProjectType'] . "',
								project_description='" . $_POST['ProjectDescription'] . "',
								parent_project='" . $parent_project . "',
								customer='" . $_POST['Customer'] . "',
								begin_date='" .$begindate->format('Y-m-d'). "',
								end_date='" .$enddate->format('Y-m-d') . "',
								project_manager='" . $_POST['ProjectManager'] . "',
								status='" . $_POST['Status'] . "',
								project_status='" . $_POST['ProjectStatus'] . "',
								project_location='" . $_POST['ProjectLocation'] . "',
								billing_term='" . $_POST['BillingTerm'] . "',
								billing_type='" . $_POST['BillingType'] . "',
								billable_expense='" . $billable_expense . "',
								billable_ap='" . $billable_ap . "',
								contract_amount='" . $_POST['ContractAmount'] . "',
								bankaccount='" . $_POST['BankAccount'] . "',
								projectbudget='" . $_POST['ProjectBudget'] . "'
    					WHERE project_id='" . $ProjectID . "'";

    			$ErrMsg = _('The project could not be updated because');
    			$DbgMsg = _('The SQL that was used to update the project and failed was');
    			$result = DB_query($sql,$ErrMsg,$DbgMsg);

    			prnMsg( _('Project') . ' ' . $ProjectID . ' ' . _('has been updated'), 'success');
    			echo '<br />';


        }else { //it is a NEW project

					DB_Txn_Begin();

					$parent_project = (mb_strlen($_POST['ParentProjectID']) > 0 ) ? $_POST['ParentProjectID'] : 0;
					$billable_expense = (isset($_POST['BillableExpense'])) ? 1 : 0;
					$billable_ap = (isset($_POST['BillableAp'])) ? 1 : 0;

          $sql = "INSERT INTO paprojects (project_id,
						project_name,
						project_category,
						project_type,
						project_description,
						parent_project,
						customer,
						begin_date,
						end_date,
						project_manager,
						status,
						project_status,
						project_location,
						billing_term,
						billing_type,
						billable_expense,
						billable_ap,
						contract_amount,
						bankaccount,
						projectbudget
					)
    						VALUES (
									'" .mb_strtoupper($_POST['ProjectID']) . "',
									'" . $_POST['ProjectName'] . "',
									'".$_POST['ProjectCategory']."',
									'" . $_POST['ProjectType'] . "',
									'" . $_POST['ProjectDescription'] . "',
									'" . $parent_project . "',
									'" . $_POST['Customer'] . "',
									'" . $begindate->format('Y-m-d')  . "',
									'" . $enddate->format('Y-m-d') . "',
									'" . $_POST['ProjectManager'] . "',
    							'" . $_POST['Status'] . "',
    							'" . $_POST['ProjectStatus'] . "',
    							'" . $_POST['ProjectLocation'] . "',
									'" . $_POST['BillingTerm'] . "',
									'" . $_POST['BillingType'] . "',
									'" . $billable_expense . "',
									'" . $billable_ap . "',
    							'" . $_POST['ContractAmount'] . "',
									'" . $_POST['BankAccount'] . "',
									'" . $_POST['ProjectBudget'] . "'
									 )";
    			$ErrMsg =  _('The project could not be added because');
    			$DbgMsg = _('The SQL that was used to add the project failed was');
    			$result = DB_query($sql, $ErrMsg, $DbgMsg);

					$sql_company_details = DB_query("SELECT currencydefault, payrollact FROM companies");
					$result_company_details = DB_fetch_array($sql_company_details);
					$default_currency = $result_company_details['currencydefault'];


					$sqlcustomer = "INSERT INTO debtorsmaster (
									debtorno,
									name,
									address1,
									currcode,
									clientsince,
									holdreason,
									paymentterms,
									discount,

									pymtdiscount,
									creditlimit,
									salestype,
									invaddrbranch,
									customerpoline,
									typeid,
									language_id ,
 	                 isproject
								)
						VALUES ('" .mb_strtoupper( $_POST['ProjectID'])."',
								'" . $_POST['ProjectName'] ."',
								'" . $_POST['ProjectLocation'] ."',
								'" . $default_currency . "',
								'" . date('Y-m-d') . "',
								'1',
								'20',
								'" . filter_number_format(0)/100 . "',

								'" . filter_number_format(0)/100 . "',
								'" . filter_number_format($_SESSION['DefaultCreditLimit']) . "',
								'" . $_SESSION['DefaultPriceList'] . "',
								'0',
								'0',
								'" . $_SESSION['DefaultCustomerType'] . "',
								'" . $_SESSION['Language'] . "',
								'1')";

					$ErrMsg = _('This customer could not be added because');
					$resultcustomer = DB_query($sqlcustomer,$ErrMsg);

					//pick sales person and sales areas and pricelist
					$sql_salesman = DB_query("SELECT salesmancode from salesman limit 1");
					$sql_row = DB_fetch_array($sql_salesman);
					$salesman = $sql_row['salesmancode'];

					$sql_area = DB_query("SELECT areacode FROM areas limit 1");
					$sql_row = DB_fetch_array($sql_area);
					$salesarea = $sql_row['areacode'];
					$SQLbrcustomer = "INSERT INTO custbranch (branchcode,
									debtorno,
									brname,
									estdeliverydays,
									fwddate,
									salesman,
									contactname,
									area,
									taxgroupid,
									defaultlocation,
									disabletrans,
									specialinstructions,
									deliverblind)
							VALUES ('" . mb_strtoupper($_POST['ProjectID']) . "',
								'" .mb_strtoupper($_POST['ProjectID']) . "',
								'" . $_POST['ProjectName']  . "',
								'" . filter_number_format(0) . "',
								'0',
								'".$salesman."',
								'" .$_POST['ProjectName'] . "',
								'".$salesarea."',
								'1',
								'" . $_POST['ProjectLocation'] . "',
								'0',
								'',
								'1')";

$resultbrcustomer = DB_query($SQLbrcustomer);



          if (DB_error_no() ==0) {
						DB_Txn_Commit();
    				//$NewEmpID = DB_Last_Insert_ID($db,'hremployees', 'empid');
    				prnMsg( _('The new project has been added to the database  :'),'success');
						prnMsg( _('Customer '.$_POST['ProjectID'].'  has been created  :'),'success');

    				unset($_POST['ProjectName']);
						unset($_POST['ProjectCategory']);
						unset($_POST['ProjectType']);
						unset($_POST['Status']);
						unset($_POST['ProjectStatus']);
						unset($_POST['BillingType']);
						unset($_POST['BillingTerm']);
						unset($_POST['ContractAmount']);
						unset($_POST['ProjectID']);

    			}//ALL WORKED SO RESET THE FORM VARIABLES

        }


      }
      else {
        echo '<br />' .  "\n";
        prnMsg( _('Validation failed, no updates or deletes took place'), 'error');
      }

    }
    elseif (isset($_POST['delete']) AND mb_strlen($_POST['delete']) >1  or $_GET['delete']==1) {
    //the button to delete a selected record was clicked instead of the submit button

    	$CancelDelete = 0;

    	//what validation is required before allowing deletion of project ....  maybe there should be no deletion option?
    	$result = DB_query("SELECT project_id
    						FROM paprojectresourcelabour
    						WHERE project_id='" . $ProjectID . "'");

    	if (DB_num_rows($result) > 0) {
    		$CancelDelete =1; //cannot delete employee already paid
				$InputError = 1;
    		prnMsg(_('The project has resources attached..cannot delete'),'error');
				exit();
    	}
    	$result = DB_query("SELECT * FROM hremployeeleaves WHERE leaveemployee_id='" . $EmpID . "'");
    	if (DB_num_rows($result) > 0){
    		$CancelDelete =1; /*cannot delete employee with leave */
    		prnMsg(_('The employee already applied for leave. The employee can only be deleted if he has no leave'),'error');
				exit();
    	}

    	if ($CancelDelete==0) {


    		$sql="DELETE FROM paprojects WHERE id='" . $ProjectID . "'";
    		$result=DB_query($sql, _('Could not delete the project record'),'',true);




    		prnMsg(_('Deleted the project  record for project id' ) . ' ' . $ProjectID );
				unset($_POST['ProjectName']);
				unset($_POST['ProjectCategory']);
				unset($_POST['ProjectType']);
				unset($_POST['Status']);
				unset($_POST['ProjectStatus']);
				unset($_POST['BillingType']);
				unset($_POST['BillingTerm']);
				unset($_POST['ContractAmount']);
    		unset($ProjectID);


    	} //end if OK Delete Asset
}
/*styles for this form*/
echo '<style>
				label{
					display: block;
				}

			</style>';
echo '<form id="ProjectForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
      <div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';

if (!isset($ProjectID) OR $ProjectID=='') {

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

	$sql = "SELECT paprojects.*
			FROM paprojects
			WHERE id ='" . $ProjectID . "'";

	$result = DB_query($sql);
	$ProjectRow = DB_fetch_array($result);
	$_POST['ProjectID'] = $ProjectRow['project_id'];
	$_POST['ProjectName'] = $ProjectRow['project_name'];
	$_POST['ProjectDescription'] = $ProjectRow['project_description'];
	$_POST['ProjectCategory'] = $ProjectRow['project_category'];
	$_POST['ProjectType']  = $ProjectRow['project_type'];
	$_POST['BeginDate']  = $ProjectRow['begin_date'];
	$_POST['EndDate']  = $ProjectRow['end_date'];
	$_POST['BillingType']  = $ProjectRow['billing_type'];
	$_POST['BillingTerm']  = $ProjectRow['billing_term'];
	$_POST['Status']  = $ProjectRow['status'];
	$_POST['ProjectStatus']  = $ProjectRow['project_status'];
	$_POST['BillableExpense']  = $ProjectRow['billable_expense'];
	$_POST['BillableAp']  = $ProjectRow['billable_ap'];
	$_POST['InvoiceParent']  = $ProjectRow['invoice_with_parent'];
	$_POST['ProjectLocation']  = $ProjectRow['project_location'];
	$_POST['Customer']  = $ProjectRow['customer'];
	$_POST['ContractAmount']  = $ProjectRow['contract_amount'];
	$_POST['PID']  = $ProjectRow['id'];
	$_POST['BankAccount']  = $ProjectRow['bankaccount'];
	$_POST['ProjectBudget']  = $ProjectRow['projectbudget'];

  }
  echo '<tr><td><h3>Project Info</h3></td></tr>
  			<tr><td colspan="2"><hr></td></tr>';
  echo '<tr>
	  				<td>
							<label>' . _('Project ID') . ' (' . _('unique') . '):</label>
			  			<input ' . (in_array('ProjectID',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . _('Enter the id of project. it should be unique.') . '" name="ProjectID" maxlength="50" value="' . $_POST['ProjectID'] . '" />
						</td>
						<td><label> Customer </label>
						<select name="Customer" ><option value="">Select Customer</option>';
							$sql = "SELECT debtorno,name FROM debtorsmaster";
							$ErrMsg = _('The customers could not be retrieved because');
							$DbgMsg = _('The SQL used to retrieve customers and failed was');
							$result = DB_query($sql,$ErrMsg,$DbgMsg);

							while ($myrow=DB_fetch_array($result)){
								if ($myrow['debtorno']==$_POST['Customer']){
									echo '<option selected="selected" value="'. $myrow['debtorno'] . '">' . $myrow['name'] . '</option>';
								} else {
									echo '<option value="'. $myrow['debtorno'] . '">' . $myrow['name']. '</option>';
								}

							}
							echo '</select><a target="_blank" href="'. $RootPath . '/Customers.php">' . ' ' . _('New') . '</a>
						</td>
					</tr>';

							if (isset($SelectedName)) {
								$Begindate= $_POST['BeginDate'];
								$Enddate= $_POST['EndDate'];
							}else{
							$Begindate=date('Y-m-d');
								$Enddate=date('Y-m-d');
							}

	echo '<tr>
					<td>
						<label>' . _('Project Name') . ' </label>
						<input ' . (in_array('ProjectName',$Errors) ?  'class="inputerror"' : '' ) .' type="text" required="required" title="' . _('Enter the name of project. it should be unique.') . '" name="ProjectName" maxlength="50" value="' . $_POST['ProjectName'] . '" />
					</td>
					<td>
						<label>' . _('Project Start Date') . ' </label>
						<input ' . (in_array('BeginDate',$Errors) ?  'class="inputerror datepicker"' : 'class="datepicker"' ) .' type="text" required="required"  name="BeginDate" maxlength="50" value="' . ConvertSQLDate($Begindate) . '" />
					</td>
				</tr>';
	echo '<tr>

						<td><label> Project Category </label>
						<select name="ProjectCategory">
							<option value="Contract">Contract</option>
							<option value="Capitalized">Capitalized</option>
							<option value="Internal Non-billable">Internal Non-billable</option>
							<option value="Internal Billable">Internal Billable</option>
							</select>
						</td>
						<td>
							<label>' . _('End Date') . '</label>
			  			<input ' . (in_array('EndDate',$Errors) ?  'class="inputerror datepicker"' : 'class="datepicker"' ) .' type="text" required="required"  name="EndDate" maxlength="50" value="' . ConvertSQLDate($Enddate) . '" />
						</td>
					</tr>';
	echo '<tr>
					<td><label> Project Type </label>
					<select name="ProjectType" >';
					$sql = "SELECT project_type_id, project_type_name FROM paprojecttypes";
			    $resultProjectTypes = DB_query($sql);
			    while ($myrow=DB_fetch_array($resultProjectTypes)){
			  				if ($myrow['project_type_id'] == $_POST['ProjectType']){
			  					 echo '<option selected="selected" value="' . $myrow['project_type_id'] . '">' . $myrow['project_type_name'] . '</option>';
			  				} else {
			  					 echo '<option value="' . $myrow['project_type_id'] . '">' . $myrow['project_type_name'] . '</option>';
			  				}

			  		}
						echo '</select><a target="_blank" href="'. $RootPath . '/PaProjectTypes.php">' . ' ' . _('New') . '</a>
					</td>
					<td><label> Parent Project</label>
					<select name="ParentProjectID" ><option value="">Choose Parent Project</option>';
					$sql = "SELECT id, project_name FROM paprojects";
					$resultParentProjects = DB_query($sql);
					while ($myrow=DB_fetch_array($resultParentProjects)){
								if ($myrow['_id'] == $_POST['ParentProjectID']){
									 echo '<option selected="selected" value="' . $myrow['id'] . '">' . $myrow['name'] . '</option>';
								} else {
									 echo '<option value="' . $myrow['id'] . '">' . $myrow['name'] . '</option>';
								}

						}
						echo '</select>
					</td>

				</tr>';
echo '<tr>
				<td><label> Project Description</label>
				   <textarea required="required" ' . (in_array('ProjectDescription',$Errors) ?  'class="texterror"' : '' ) .'  name="ProjectDescription"  title="' . _('Enter the project description ') . '" cols="20" rows="4">' . stripslashes($_POST['ProjectDescription']) . '</textarea>
				</td>
				<td><label> Invoice with parent ?</label>
						<input type="checkbox" name="InvoiceParent" />
				</td>
			</tr>';
echo '<tr>
				<td><label> Project Postion</label>
				<select name="ProjectStatus" >';
				$sql = "SELECT project_status_id, project_status_name FROM paprojectstatus";
				$resultProjectStatus = DB_query($sql);
				while ($myrow=DB_fetch_array($resultProjectStatus)){
							if ($myrow['project_status_id'] == $_POST['ProjectStatus']){
								 echo '<option selected="selected" value="' . $myrow['project_status_id'] . '">' . $myrow['project_status_name'] . '</option>';
							} else {
								 echo '<option value="' . $myrow['project_status_id'] . '">' . $myrow['project_status_name'] . '</option>';
							}

					}
					echo '</select><a target="_blank" href="'. $RootPath . '/PaProjectStatus.php">' . ' ' . _('New') . '</a>
				</td>
				<td><label> Status </label>
				<select name="Status">
					<option value="1">Active</option>
					<option value="0">Inactive</option>

					</select>
				</td>
				<td>
			</tr>';
echo '<tr>
					<td><label> Project Manager</label>
					<select name="ProjectManager" >';
					$sql = "SELECT empid, employee_id,first_name,last_name FROM hremployees";
					$resultEmployees = DB_query($sql);
					while ($myrow=DB_fetch_array($resultEmployees)){
								if ($myrow['empid'] == $_POST['ProjectManager']){
									 echo '<option selected="selected" value="' . $myrow['empid'] . '">' . $myrow['first_name'].' '. $myrow['last_name'] .'('. $myrow['employee_id'] .')' . '</option>';
								} else {
									 echo '<option value="' . $myrow['empid'] . '">'  . $myrow['first_name'].' '. $myrow['last_name'] .'('. $myrow['employee_id'] .')' .'</option>';
								}

						}
						echo '</select>
					</td>
					<td><label> Project Location </label>
						<select name="ProjectLocation" >';
						$sql = "SELECT loccode, locationname FROM locations";
						$ErrMsg = _('The locations could not be retrieved because');
						$DbgMsg = _('The SQL used to retrieve locations and failed was');
						$result = DB_query($sql,$ErrMsg,$DbgMsg);

						while ($myrow=DB_fetch_array($result)){
							if ($myrow['loccode']==$_POST['ProjectLocation']){
								echo '<option selected="selected" value="'. $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
							} else {
								echo '<option value="'. $myrow['loccode'] . '">' . $myrow['locationname']. '</option>';
							}

						}
						echo '</select><a target="_blank" href="'. $RootPath . '/Locations.php">' . ' ' . _('New') . '</a>
					</td>
			</tr>';

			// Bank Accounts.
			echo '<tr><td><label for="BankAccount">' . _('Bank Account') .
				':</label></td><td><select id="BankAccount" name="BankAccount" >';
			foreach ($BankAccount as $AccountCode => $Row) {

				echo '<option';
				if (isset($_POST['BankAccount']) and $_POST['BankAccount']==$AccountCode) {
					echo ' selected="selected"';
				}
				echo ' value="' . $AccountCode . '">' . $Row . '</option>';
			}
			echo '</select> </td></tr>';

echo'<tr><td>Project Budget</td><td><input type="text" required="required" name="ProjectBudget"  value="'.$_POST['ProjectBudget'].'"/></td></tr>';
echo '</table>';

echo '<table class="selection">';

echo '<tr><td><h3>Project Billing</h3></td></tr>
			<tr><td colspan="2"><hr></td></tr>';
echo '<tr>
				<td><label> Billing Term</label>
				<select name="BillingTerm" >';
					$sql = "SELECT billing_term_id, billing_term_name FROM paprojectbillingterms";
					$ErrMsg = _('The locations could not be retrieved because');
					$DbgMsg = _('The SQL used to retrieve locations and failed was');
					$result = DB_query($sql,$ErrMsg,$DbgMsg);

					while ($myrow=DB_fetch_array($result)){
						if ($myrow['billing_term_id']==$_POST['BillingTerm']){
							echo '<option selected="selected" value="'. $myrow['billing_term_id'] . '">' . $myrow['billing_term_name'] . '</option>';
						} else {
							echo '<option value="'. $myrow['billing_term_id'] . '">' . $myrow['billing_term_name']. '</option>';
						}

					}
				echo '</select><a target="_blank" href="'. $RootPath . '/PaProjectBillingTerm.php">' . ' ' . _('New') . '</a>
				</td>
				<td><label> Billable Employee Expenses ?</label>
						<input type="checkbox" name="BillableExpense" />
				</td>
			</tr>';
echo '<tr>
				<td><label> Billing Type</label>
					<select name="BillingType" >
						<option value="Time and Material">Time and Material</option>
						<option value="Fixed Fee">Fixed Fee</option>
						<option value="Fixed Fee and Expense">Fixed Fee and Expense</option>
					</select>
					</td>
					<td><label> Billabe AP/PO</label>
							<input type="checkbox" name="BillableAp" />
					</td>
			</tr>';
echo '<tr>
				<td><label> Contract Amount</label>
					<input type="text" '. (in_array('ContractAmount',$Errors) ?  'class="texterror"' : '' ) .'  name="ContractAmount"  title="' . _('Enter the project contract amount ') . '" value="'.$_POST['ContractAmount'].'" />
				</td>
		 </tr>

		</table>';
		echo "<script>
						$( document ).ready(function() {
								//create date.
								//get format.
								var date_format = '".$_SESSION['DefaultDateFormat']."';
								var new_date_format = date_format.replace('Y', 'yy');
								$('.datepicker').datepicker({
										changeMonth: true,
										changeYear: true,
										showButtonPanel: true,
										dateFormat: new_date_format
								});
						});

				</script>";

if (isset($New)) {
	echo '<div class="centre">
			<br />
			<input type="submit" name="submit" value="' . _('Insert New Project') . '" />';
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
			<input type="submit" name="delete" value="' . _('Delete This Project') . '" onclick="return confirm(\'' . _('Are You Sure? employees who have not yet been paid can be deleted') . '\');" />';
}

echo '</div>
      </div>
	</form>';

include('includes/footer.php');

?>
