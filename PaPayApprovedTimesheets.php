<?php

/* $Id: HrEmployees.php 7751 2018-04-13 16:34:26Z raymond $ */
/*	Add and Edit Employee*/

include('includes/session.php');
$Title = _('Pay Approved Timesheets');

$ViewTopic = 'Pay Approved Timesheets';
$BookMark = 'Payroll';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/CountriesArray.php');
echo'<script type="text/javascript" src="plugins/select2/js/select2.min.js"></script>';
if (isset($_GET['Projects'])) {
	$SelectedProject = $_GET['Projects'];
} elseif (isset($_POST['Projects'])){
	$SelectedProject = $_POST['Projects'];
} else {
	unset($SelectedProject);
}

if (isset($_GET['FromDate'])) {
	$SelectedStartDate = $_GET['FromDate'];
} elseif (isset($_POST['FromDate'])){
	$SelectedStartDate = $_POST['FromDate'];
} else {
	unset($SelectedStartDate);
}

if (isset($_GET['ToDate'])) {
	$SelectedEndtDate = $_GET['ToDate'];
} elseif (isset($_POST['ToDate'])){
	$SelectedEndDate = $_POST['ToDate'];
} else {
	unset($SelectedStartDate);
}

echo '<a href="' . $RootPath . '/PaSelectProject.php">' . _('Search For Projects') . '</a><br />' . "\n";

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/user.png" title="' .
		_('Employee data') . '" alt="" />' . ' ' . $Title . '</p>';


echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
    <div>
      <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

      echo '<table class="selection"><tr><td>';

    echo ''. _('Project') . ':<select required="required" id="select" name="Projects">
		<option value="">Select Project</option>';
    $sql = "SELECT id, project_id,project_name FROM paprojects";
    $resultProjects = DB_query($sql);
    while ($myrow=DB_fetch_array($resultProjects)){
          if ($myrow['id']==$SelectedProject){
            echo '<option data-id="'.$myrow['project_id'].'" selected="selected" value="'. $myrow['id'] . '">' . $myrow['project_id'].' - '. $myrow['project_name'] . '</option>';
          } else {
            echo '<option data-id="'.$myrow['project_id'].'" value="'. $myrow['id'] . '">' . $myrow['project_id'].' -'. $myrow['project_name']. '</option>';
          }

      }

      echo '</select>';

			echo '</select> &nbsp;&nbsp'._('From') . ':
			<input type="text" name="FromDate" required="required"  value="'.$SelectedStartDate.'" class="datepicker" maxlength="10" size="20"  />&nbsp';
			echo '</select> &nbsp;&nbsp'._('To') . ':
			<input type="text" name="ToDate" required="required" value="'.$SelectedEndDate.'" class="datepicker" maxlength="10" size="20"  />&nbsp';


			echo '<input type="submit" name="PayTimesheets" value="' . _('Generate ') . '" />
        &nbsp;&nbsp;</td>
        </tr>
        </table>
        <br />
        </div>
        </form>';
if(isset($PayTimesheets))
{
	//summary of payroll and bulk action buttons
	echo '<h3>Approved Timesheets</h3>';
	echo '<table>
					<tr><td>pay period</td><td>payment frequency</td><td>pay period</td><td>payslips generated</td></tr>
					<tr><td>'.$_POST['PaymentPeriod'].'</td><td>'.$PaymentFrequency.'</td><td>'.$PayslipEmployees.' of '.$EmployeesInPaygroup.' Approved: '.$ApprovedPayslips.' Pending: '.$PendingPayslips.' </td></tr>
			</table>';
}

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

echo '<script>
	$(document).ready(function() {
						$("head").append(\'<link rel="stylesheet" type="text/css" href="plugins/select2/css/select2.css"/>\');

						    $("#select").select2();

						});



		</script>';

if(isset($_POST['PayTimesheets'])) {

	$Fromdate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['FromDate']);
	$Todate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['ToDate']);
			$InputError = 0;
			if (trim($_POST['Projects']) == '') {
				$InputError = 1;
				prnMsg(_('please select a Project'),'error');
				$Errors[$i] = 'Projects';
				$i++;
			}
			if($InputError == 0)
			{
			$PayTimesheets = $_POST['PayTimesheets'];


			$sqlproject = "SELECT id, project_id,project_name
			FROM paprojects WHERE id=".$SelectedProject."";
			$resultprojects = DB_query($sqlproject);
			$myrowproject = DB_fetch_array($resultprojects);

					$sqlapprovedtime= "SELECT
					patimesheetsinfo.employee_id As employeeid,
					 sun,
					 mon,
					 tue,
					 wed,
					 thu,
					 fri,
					 sat,
			timesheet_status,
			first_name,
			middle_name,
			last_name
					FROM patimesheetentries
					JOIN patimesheetsinfo on patimesheetentries.timesheetinfo_id = patimesheetsinfo.timesheetsinfo_id
					JOIN hremployees on patimesheetsinfo.employee_id = hremployees.empid
					Where project_id='".$SelectedProject."'
					AND timesheet_status='1'
					AND paystatus='0'
					AND patimesheetsinfo.begin_date  Between '".$Fromdate->format('Y-m-d')."' AND
					'".$Todate->format('Y-m-d')."'
					";


					$result = DB_query($sqlapprovedtime);

					$timesheet_array = array();

					while($myrow = DB_fetch_array($result))
					{
$sqllabourrate ="SELECT labourrate FROM paprojectresourcelabour
	WHERE project_id =".$SelectedProject."
	AND employee_id =".$myrow['employeeid']."";
	$labourrate= DB_query($sqllabourrate);
$myrowlabourrate = DB_fetch_array($labourrate);

$employeetime_array=array();
           $totalduration=$myrow['sun']+$myrow['mon']+$myrow['tue']+$myrow['wed']+$myrow['thu']+$myrow['fri']+$myrow['sat'];
           $employee1_id=$myrow['employeeid'];
						$department_id = $myrow['employee_department'];
						$payslip_id = $myrow['payslip_id'];
						$gross_salary = $myrow['gross_pay'];
						$net_pay = $myrow['net_pay'];
						$employee_id = $myrow['employee_id'];
						$loan_amount_to_pay = $myrow['loan_deduction_amount'];
						$lop_amount = $myrow['lop_amount'];
						$payslip_status = $myrow['payslip_status'];

  $employeetime_array['totalamout'] =$totalduration*$myrowlabourrate['labourrate'];
  $employeetime_array['totaltime'] =$totalduration;
	$employeetime_array['employee_id'] =$employee1_id;

						$timesheet_array[] = $employeetime_array;

					}

					$sqlemployee= "SELECT
					DISTINCT patimesheetsinfo.employee_id As employeeid,
					first_name,
					middle_name,
					last_name,
					employee_department
					FROM patimesheetentries
					JOIN patimesheetsinfo on patimesheetentries.timesheetinfo_id = patimesheetsinfo.timesheetsinfo_id
					JOIN hremployees on patimesheetsinfo.employee_id = hremployees.empid
					Where project_id='".$SelectedProject."'
					AND timesheet_status='1'
					AND paystatus='0'
					AND patimesheetsinfo.begin_date  Between '".$Fromdate->format('Y-m-d')."' AND
					'".$Todate->format('Y-m-d')."'
					";
					$resultemployee= DB_query($sqlemployee);
					$employee_array = array();

					while($myrowemployee = DB_fetch_array($resultemployee))
					{
$employee1_array=array();
				$employee_name = $myrowemployee['first_name']." ".$myrowemployee['middle_name']." ".$myrowemployee['last_name'];
        $department_id = $myrowemployee['employee_department'];
				$employee_id =$myrowemployee['employeeid'];
						$employee1_array['employee_name'] =$employee_name;
            $employee1_array['employee_department'] = $department_id;
            $employee1_array['employee_id']=$employee_id;
						$employee_array[] = $employee1_array;
					}
			if (DB_error_no() ==0) {
				$PayslipEmployees = count($payroll_array);
				$TotalNetPay = array_sum(array_column($timesheet_array, 'totalamout'));
				$Totaltime = array_sum(array_column($timesheet_array, 'totaltime'));
				echo '<h3>Approved Timesheets</h3>';
				echo '<table>
								<tr><td>Project</td>
								<td>From</td>
								<td>To</td>
								<td>TotalTime</td>
								<td>Total Amount</td>
								<td></td></tr>
								<tr><td><strong>'.$myrowproject['project_id'].' '.$myrowproject['project_name'].'</strong></td>
								<td><strong>'.$SelectedStartDate.'</strong></td><td>'.$SelectedEndDate.'</td>
								<td>'.$Totaltime.'</td>
									<td><strong>'.$payroll_currency.' '.number_format($TotalNetPay,2).'</strong></td>
			<td><form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
									 <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
									 <input type="hidden" name="Projectid" value="'.$SelectedProject.'"/>
									 <input type="hidden" name="StartDate" value="'.$SelectedStartDate.'"/>
												<input type="hidden" name="EndDate" value="'.$SelectedEndDate.'"/>
												<input type="hidden" name="PayrollGroupID" value="'.$PayrollGroupID.'"/>
												<input type="hidden" name="TotalAmount" value="'.$TotalNetPay.'"/>
												<input type="hidden" name="Totaltime" value="'.$Totaltime.'"/>';
												if($TotalNetPay!=0){
											echo '<input type="submit" name="pay" value="' . _('Pay') . '" />';
										}
										echo'	</form>
									</td>
								</tr>
						</table>';
		echo '<table class="selection">
				<tr>
					<th class="noprint" >&nbsp;</th>
					<th class="ascending">', _('Employee ID'), '</th>
					<th class="ascending">', _('Department'), '</th>';

								echo '<th class="ascending"> Total Time</th>';

								echo '<th class="ascending">Rate</th>
<th class="ascending">Total Amount</th>
								</tr>';
				foreach($employee_array as $index => $employee){
				$sql2 ="SELECT departmentid,description FROM departments
					WHERE departmentid =".$employee['employee_department']."";
					$result2 = DB_query($sql2);
				$deparmentDetails = DB_fetch_array($result2);
				$sqlrate ="SELECT labourrate FROM paprojectresourcelabour
					WHERE project_id =".$SelectedProject."
					AND employee_id =".$employee['employee_id']."";
					$resultrate = DB_query($sqlrate);
				$labourrate= DB_fetch_array($resultrate);

				$totaltime=0;
foreach($timesheet_array as $index => $timesheet){

	if($employee['employee_id']==$timesheet['employee_id']){
$totaltime= $totaltime+$timesheet['totaltime'];

	}

}

					echo '<tr>

								<td>'.$employee_payslip['payslip_status'].'</td>
								<td>'.$employee['employee_name'].'</td>
								<td>'.$deparmentDetails['description'].'</td>
								<td>'.$totaltime.'</td>';

					echo'	<td>'.number_format($labourrate['labourrate'],2).'</td>
								<td>'.number_format(($labourrate['labourrate']*$totaltime),2).'</td>';


					echo '</tr>';
				}
			}


			echo '</table>';

	}



}

if(isset($_POST['pay'])){
	//check for general ledger permissions
	if(!in_array('10',$_SESSION['AllowedPageSecurityTokens']))
	{
		prnMsg( _('Unauthorized Access  :  you do not have access to this functionality'), 'error');
		echo '<br />';
	}
	else{

		$sql = DB_query("SELECT bankaccount FROM paprojects WHERE id = '".$_POST['Projectid']."'");
		$result = DB_fetch_array($sql);
		$paybank_account = $result['bankaccount'];
		//$gl_posting_account = $result['gl_posting_account'];
		//$payroll_currency = $result['currency'];
		if($paybank_account == NULL)
		{

			prnMsg( _('No bank account configured  :  Please configure a bank account for this payroll group or set default account for paying salaries'), 'error');
			echo '<br />';
			exit(1);
		}

		$sql = "SELECT sum(amount) as bank_balance FROM banktrans WHERE bankact ='".$paybank_account."'";
		$ErrMsg = _('The bank account for payroll could not be retrieved');
		$DbgMsg = _('The SQL that was used to check bank account and failed was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		$myrow = DB_fetch_array($result);
		$bank_balance = $myrow['bank_balance'];
		if($bank_balance < $_POST['TotalAmount'])
		{
			echo $_POST['TotalAmount'];
			prnMsg( _('Low Bank Balance  :  Please reconcile your bank account no: '.$payroll_bank_account.' for paying this payroll, it appears you may not have enough money to pay in this account'), 'error');
			echo '<br />';
		}
		else if($bank_balance > $_POST['TotalAmount'])
		{

			$Fromdate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['StartDate']);
			$Todate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['EndDate']);



					$sqlapprovedtime1= "SELECT
					patimesheetsinfo.employee_id As employeeid,
					timesheetentry_id,
					 sun,
					 mon,
					 tue,
					 wed,
					 thu,
					 fri,
					 sat,
			timesheet_status,
			first_name,
			middle_name,
			last_name
					FROM patimesheetentries
					JOIN patimesheetsinfo on patimesheetentries.timesheetinfo_id = patimesheetsinfo.timesheetsinfo_id
					JOIN hremployees on patimesheetsinfo.employee_id = hremployees.empid
					Where project_id='".$_POST['Projectid']."'
					AND timesheet_status='1'
					AND paystatus='0'
					AND patimesheetsinfo.begin_date  Between '".$Fromdate->format('Y-m-d')."' AND
					'".$Todate->format('Y-m-d')."'
					";

					$result1 = DB_query($sqlapprovedtime1);

					$timesheet_array1 = array();

					while($myrow1 = DB_fetch_array($result1))
					{
			$sqllabourrate1 ="SELECT labourrate FROM paprojectresourcelabour
			WHERE project_id =".$_POST['Projectid']."
			AND employee_id =".$myrow1['employeeid']."";
			$labourrate1= DB_query($sqllabourrate1);
			$myrowlabourrate1 = DB_fetch_array($labourrate1);

			$employeetime_array1=array();
					 $totalduration1=$myrow1['sun']+$myrow1['mon']+$myrow1['tue']+$myrow1['wed']+$myrow1['thu']+$myrow1['fri']+$myrow1['sat'];
					 $employee1_id1=$myrow1['employeeid'];
					  $timesheeetid=$myrow1['timesheetentry_id'];
						$department_id1 = $myrow1['employee_department'];

			$employeetime_array1['totalamout'] =$totalduration1*$myrowlabourrate1['labourrate'];
			$employeetime_array1['totaltime'] =$totalduration1;
			$employeetime_array1['employee_id'] =$employee1_id1;
     $employeetime_array1['timesheetentry_id'] =$timesheeetid;
						$timesheet_array1[] = $employeetime_array1;
					}

					$sqlemployee1= "SELECT
					DISTINCT patimesheetsinfo.employee_id As employeeid,
					first_name,
					middle_name,
					last_name,
					employee_department
					FROM patimesheetentries
					JOIN patimesheetsinfo on patimesheetentries.timesheetinfo_id = patimesheetsinfo.timesheetsinfo_id
					JOIN hremployees on patimesheetsinfo.employee_id = hremployees.empid
					Where project_id='".$_POST['Projectid']."'
					AND timesheet_status='1'
					AND paystatus='0'
					AND patimesheetsinfo.begin_date  Between '".$Fromdate->format('Y-m-d')."' AND
					'".$Todate->format('Y-m-d')."'
					";
					$resultemployee1= DB_query($sqlemployee1);
					$employee_array1 = array();

					while($myrowemployee1 = DB_fetch_array($resultemployee1))
					{
			$employee1_array1=array();

			$sqllabourrate2 ="SELECT labourrate FROM paprojectresourcelabour
			WHERE project_id =".$_POST['Projectid']."
			AND employee_id =".$myrowemployee1['employeeid']."";
			$labourrate2= DB_query($sqllabourrate2);
			$myrowlabourrate2 = DB_fetch_array($labourrate2);


				$employee_id1 =$myrowemployee1['employeeid'];
				$labourate2=$myrowlabourrate2['labourrate'];
						$employee1_array1['labourrate'] =	$labourate2;

						$employee1_array1['employee_id']=$employee_id1;
						$employee_array1[] = $employee1_array1;

					}




DB_Txn_Begin();
	foreach($employee_array1 as $index => $employee1){
$totaltime=0; $totalamount=0;
foreach($timesheet_array1 as $index => $timesheet1){
$sqlupdate ="UPDATE patimesheetentries
	SET paystatus  = '1'
WHERE timesheetentry_id = '" .$timesheet1['timesheetentry_id']."'";

	if($employee1['employee_id']==$timesheet1['employee_id']){
$totaltime= $totaltime+$timesheet1['totaltime'];
$totalamount=$totalamount +$timesheet1['totalamout'];

	}
}

$sql11 = "INSERT INTO patimesheetspayments
			(project_id,
			employee_id,
			labourrate ,
			totaltime,
			totalamount,
			startdate,
			enddate
		)
		VALUES ('" . $_POST['Projectid'] . "',
'" . $employee1['employee_id'] . "',
'" . $employee1['labourrate'] . "',
'" . $totaltime  . "',
'" . $totalamount . "',
'" . $Fromdate->format('Y-m-d') . "',
'" . $Todate->format('Y-m-d') . "'
)";
$resultpay = DB_query($sql11);

	}

	foreach($timesheet_array1 as $index => $timesheet1){
	$sqlupdate ="UPDATE patimesheetentries
		SET paystatus  = '1'
	WHERE timesheetentry_id = '" .$timesheet1['timesheetentry_id']."'";
$resulttime = DB_query($sqlupdate);
}


	$sql_company_details = DB_query("SELECT currencydefault, payrollact,debtorsact FROM companies");
	$result_company_details = DB_fetch_array($sql_company_details);
	$default_currency = $result_company_details['currencydefault'];
$default_project_gl_account= $result_company_details['payrollact'];
$project_currency = $default_currency;
$gl_posting_account = $default_project_gl_account;


$DatePaid = Date($_SESSION['DefaultDateFormat']);
$Narrative = "Approved Timesheet Payment";
$PeriodNo = GetPeriod($DatePaid,$db);
$Cheque = 0;
$Tag = 0;
$PaymentType ="Direct Credit";
$ExchangeRate = 1;
//begin transactions

$TransNo = GetNextTransNo( 1, $db);
$TransType = 1;

//1. First DO gl entry for payroll liabilities accounts
$SQL = "INSERT INTO gltrans (
			type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			amount,
			chequeno,
			tag
		) VALUES (
			1,'" .
			$TransNo . "','" .
			FormatDateForSQL($DatePaid) . "','" .
			$PeriodNo . "','" .
			$gl_posting_account . "','" .
			$Narrative . "','" .
			$_POST['TotalAmount'] . "','".
			$Cheque ."','" .
			$Tag .
		"')";
$ErrMsg = _('Cannot insert a GL entry for the payment using the SQL');
$result = DB_query($SQL,$ErrMsg,_('The SQL that failed was'),true);
$gl_transaction_id = DB_Last_Insert_ID($db,'gltrans','counterindex');
//2.  do GL transaction for the bank account - credit
$SQL = "INSERT INTO gltrans (
			type,
			typeno,
			trandate,
			periodno,
			account,
			narrative,
			amount
		) VALUES ('" .
			$TransType . "','" .
			$TransNo . "','" .
			FormatDateForSQL($DatePaid) . "','" .
			$PeriodNo . "','" .
			$paybank_account . "','" .
			$Narrative . "','" .
			-$_POST['TotalAmount'] .
		"')";
$ErrMsg = _('Cannot insert a GL transaction for the bank account credit because');
$DbgMsg = _('Cannot insert a GL transaction for the bank account credit using the SQL');
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
EnsureGLEntriesBalance($TransType,$TransNo,$db);


//3. do Bank transaction.
$SQL = "INSERT INTO banktrans (
			transno,
			type,
			bankact,
			ref,
			exrate,
			functionalexrate,
			transdate,
			banktranstype,
			amount,
			currcode
		) VALUES ('" .
			$TransNo . "','" .
			$TransType . "','" .
			$paybank_account . "','" .
			'@'._('Bank Withdraw : '). $Narrative . "','" .
			$ExchangeRate . "','" .
			$ExchangeRate . "','" .
			FormatDateForSQL($DatePaid) . "','" .
			$PaymentType . "','" .
			-$_POST['TotalAmount'] . "','" .
			$project_currency .
		"')";
$ErrMsg = _('Cannot insert a bank transaction because');
$DbgMsg = _('Cannot insert a bank transaction using the SQL');
$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

/*******/
DB_Txn_Commit();

prnMsg(_('Payment for Approved Project Timesheet with Gl transacton id:') . ' ' . $gl_transaction_id . ' ' . _('has been successfully entered'),'success');

}

	}




}

include('includes/footer.php');
?>
