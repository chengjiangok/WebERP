<?php
/* $Id: HrSelectEmployee.php 7751 2018-04-13 16:34:26Z raymond $*/
/* Search for employees  */

include('includes/session.php');
$Title = _('Project Invoices Report');
$ViewTopic = 'ProjectAccounting';
$BookMark = 'Project Invoices Report';
include('includes/header.php');

echo'<script type="text/javascript" src="plugins/select2/js/select2.min.js"></script>';

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';



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

//    if (!isset($EN) or ($EN=='')){


      echo '<table class="selection"><tr><td>';

    echo  _('Project ') . ': <select id="select" name="Projects" required="required">
<option value="">Select Project</option>';

		$sql = "SELECT id, project_id,project_name FROM paprojects";
		$resultProjects = DB_query($sql);
		while ($myrow=DB_fetch_array($resultProjects)){
					if ($myrow['project_id']==$SelectedProject){
						echo '<option data-id="'.$myrow['project_id'].'" selected="selected" value="'. $myrow['project_id'] . '">' . $myrow['project_id'].' - '. $myrow['project_name'] . '</option>';
					} else {
						echo '<option data-id="'.$myrow['project_id'].'" value="'. $myrow['project_id'] . '">' . $myrow['project_id'].' -'. $myrow['project_name']. '</option>';
					}

			}


  		echo '</select> &nbsp;&nbsp'._('From') . ':
<input type="text" name="FromDate" required="required"  value="'.$SelectedStartDate.'" class="datepicker" maxlength="10" size="20"  />&nbsp';
echo '</select> &nbsp;&nbsp'._('To') . ':
<input type="text" name="ToDate" required="required" value="'.$SelectedEndDate.'" class="datepicker" maxlength="10" size="20"  />&nbsp';


      echo '<input type="submit" name="GenerateReport" value="' . _('Generate') . '" />
  			</td>
  			</tr>
  			</table>
  			<br />
				</div>
        </form>';

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


//    }
    if(isset($_POST['GenerateReport'])) {

			$Fromdate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['FromDate']);
			$Todate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['ToDate']);

			$sqlproject = "SELECT id, project_id,project_name,projectbudget
			FROM paprojects WHERE project_id='".$SelectedProject."'";
			$resultprojects = DB_query($sqlproject);
			$myrowproject = DB_fetch_array($resultprojects);



echo'<table><tr><td>';

			echo '<table class="selection">';
			echo'<tr><td colspan="6"><h4 align="center">Project Items Invoiced</h4></td></tr>';
					echo'<tr>
					<th class="ascending">' . _('invoice No') . '</th>
					<th class="ascending">' . _('Particulars') . '</th>
					<th class="ascending">' . _('Amount') . '</th>
<th class="ascending">' . _('Freight') . '</th>
<th class="ascending">' . _('Tax') . '</th>
<th class="ascending">' . _('Total Amount ') . '</th>
			';
			echo'</tr>';


					echo'<tr>	</tr>';

					$sqltime= "SELECT
					transno,
					debtorno,
					invtext ,
					ovamount,
					ovfreight,
					ovgst

					FROM debtortrans

					WHERE
					 debtorno ='".$SelectedProject."' AND
					 type='10' AND
					 trandate Between '".$Fromdate->format('Y-m-d')."' AND
					'".$Todate->format('Y-m-d')."'
					";
					$ErrMsg = _('The Projects could not be loaded because');
					$DbgMsg = _('The SQL that was used to get the Projects and failed was');
					$resulttime = DB_query($sqltime,$ErrMsg,$DbgMsg);
 $invoicetotal=0;
					while($myrowtime = DB_fetch_array($resulttime))
					{

			echo'<tr><td>'.$myrowtime['transno'].'</td>

			<td>'.$myrowtime['invtext'].'</td>
			<td>'.number_format($myrowtime['ovamount'],2).'</td>
<td>'.number_format($myrowtime['ovfreight'],2).'</td>
<td>'.number_format($myrowtime['ovgst'],2).'</td>
<td>'.number_format(($myrowtime['ovamount']+$myrowtime['ovfreight']+$myrowtime['ovgst']),2).'</td>
			<td></td>
			</tr>';
			$total=($myrowtime['ovamount']+$myrowtime['ovfreight']+$myrowtime['ovgst']);
			$invoicetotal =$invoicetotal+$total;
					}
echo'<tr><td colspan="5"><b>Total Amount Invoiced</b></td><td><b>'.number_format($invoicetotal,2).'</b></td></tr>';


			echo '</table></td>
<td>
<table style="background:#e2f5ff"><tr>
<td>Planned Budget </td><td>'.number_format($myrowproject['projectbudget'],2).'</td></tr>';

$sqlpaid= "SELECT
ovamount
FROM debtortrans

WHERE
 debtorno ='".$SelectedProject."' AND
 type='12' AND
 trandate Between '".$Fromdate->format('Y-m-d')."' AND
'".$Todate->format('Y-m-d')."'
";
$ErrMsg = _('The Projects could not be loaded because');
$DbgMsg = _('The SQL that was used to get the Projects and failed was');
$resulttime1 = DB_query($sqlpaid,$ErrMsg,$DbgMsg);
$totalpaid=0;
while($myrowtime1 = DB_fetch_array($resulttime1))
{
$totalpaid=$totalpaid+ abs($myrowtime1['ovamount']);

}

$sqlpaid= "SELECT
ovamount
FROM debtortrans

WHERE
 debtorno ='".$SelectedProject."' AND
 type='12' AND
 trandate Between '".$Fromdate->format('Y-m-d')."' AND
'".$Todate->format('Y-m-d')."'
";
$ErrMsg = _('The Projects could not be loaded because');
$DbgMsg = _('The SQL that was used to get the Projects and failed was');
$resulttime1 = DB_query($sqlpaid,$ErrMsg,$DbgMsg);
$totalpaid=0;
while($myrowtime1 = DB_fetch_array($resulttime1))
{
$totalpaid=$totalpaid+ abs($myrowtime1['ovamount']);

}

$sqllabour= "SELECT
totalamount
FROM patimesheetspayments

WHERE
 project_id ='".$myrowproject['id']."' AND
 datepaid Between '".$Fromdate->format('Y-m-d')."' AND
'".$Todate->format('Y-m-d')."'
";
$ErrMsg = _('The Projects could not be loaded because');
$DbgMsg = _('The SQL that was used to get the Projects and failed was');
$resultlabour = DB_query($sqllabour,$ErrMsg,$DbgMsg);
$totallabour=0;
while($myrowlabour = DB_fetch_array($resultlabour))
{
$totallabour=$totallabour+ abs($myrowlabour['totalamount']);

}

$percentage=(($totallabour+$totalpaid)/$myrowproject['projectbudget'])*100;

echo'<tr><td>Total Item  Cost  </td><td>'.number_format($totalpaid,2).'</td>';
echo'<tr><td>Total Labour  Cost  </td><td>'.number_format($totallabour,2).'</td>';
echo'<tr><td><b>Total Cost </b> </td><td><b>'.number_format(($totallabour+$totalpaid),2).'</b></td>';
echo'<tr><td><b>Total Cost % </b> </td><td><b>'.$percentage.'</b></td>';

echo'</tr></table></td>

			</tr>
<tr><td>
			'
			;

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
					Where project_id='".$myrowproject['id']."'
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
			WHERE project_id =".$myrowproject['id']."
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
					Where project_id='".$myrowproject['id']."'
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

					echo '<table class="selection">';

echo'<tr><td colspan="6"><h4 align="center">Approved Time Sheets</h4></td></tr>';

							echo'<tr>
								<th class="noprint" >&nbsp;</th>
								<th class="ascending">', _('Employee ID'), '</th>
								<th class="ascending">', _('Department'), '</th>';

											echo '<th class="ascending"> Total Time</th>';

											echo '<th class="ascending">Rate</th>
					<th class="ascending">Total Amount</th>
											</tr>';
							$totalpayments=0;$i=0;
							foreach($employee_array as $index => $employee){
							$sql2 ="SELECT departmentid,description FROM departments
								WHERE departmentid =".$employee['employee_department']."";
								$result2 = DB_query($sql2);
							$deparmentDetails = DB_fetch_array($result2);
							$sqlrate ="SELECT labourrate FROM paprojectresourcelabour
								WHERE project_id ='".$myrowproject['id']."'
								AND employee_id =".$employee['employee_id']."";
								$resultrate = DB_query($sqlrate);
							$labourrate= DB_fetch_array($resultrate);

							$totaltime=0;
					foreach($timesheet_array as $index => $timesheet){

					if($employee['employee_id']==$timesheet['employee_id']){
					$totaltime= $totaltime+$timesheet['totaltime'];

					}

					}
       $i++;
								echo '<tr>
											<td>'.$i.'</td>
											<td>'.$employee['employee_name'].'</td>
											<td>'.$deparmentDetails['description'].'</td>
											<td>'.$totaltime.'</td>';

								echo'	<td>'.number_format($labourrate['labourrate'],2).'</td>
											<td>'.number_format(($labourrate['labourrate']*$totaltime),2).'</td>';


								echo '</tr>';

								$employeepay=$labourrate['labourrate']*$totaltime;
							$totalpayments=$totalpayments+$employeepay;
							}

echo'<tr> <td colspan="5"><b>Total Labour Invoiced</b></td><td><b>'.number_format($totalpayments,2).'</b></td></tr>';

						echo '</table>';

					//echo'<h4> Total Project Invoice Amount:    '.number_format(($totalpayments+$invoicetotal),2).'</h4>';
echo'</td></tr></table>';

}
include('includes/footer.php');
?>
