<?php
/* $Id: HrSelectEmployee.php 7751 2018-04-13 16:34:26Z raymond $*/
/* Search for employees  */

include('includes/session.php');
$Title = _('Project Status Report');
$ViewTopic = 'ProjectAccounting';
$BookMark = 'Project Status Report';
include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


		// BEGIN: Leave Types  array.
		$LeaveTypes = array();
		$Query = "SELECT 	hrleavetype_id, leavetype_name FROM hremployeeleavetypes WHERE leavetype_status =1 ";
		$Result = DB_query($Query);
		while ($Row = DB_fetch_array($Result)) {
		$LeaveTypes[$Row['hrleavetype_id']] = $Row['leavetype_name'];
		}

    if (isset($_GET['Report'])) {
    	$SelectedReport = $_GET['Report'];
    } elseif (isset($_POST['Report'])){
    	$SelectedReport = $_POST['Report'];
    } else {
    	unset($SelectedReport);
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

    echo  _('Project Status ') . ': <select name="Report">';
		$sql = "SELECT project_status_id,
		 project_status_name,
		 project_status_desc,
		 project_status_status
		 FROM paprojectstatus";
		$result = DB_query($sql);
echo'<option value="all" '._(($myrow['project_status_id']=='all')?' selected ':' ').'  >All</option>';
	while ($myrow = DB_fetch_array($result)) {
echo'<option value="'.$myrow['project_status_id'].'" '._(($myrow['project_status_id']==$_POST['Report'])?' selected ':' ').'>'.$myrow['project_status_name'].'</option>';
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

//    }
    if(isset($_POST['GenerateReport'])) {

			$Fromdate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['FromDate']);
			$Todate=DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['ToDate']);

		if($_POST['Report']=='all'){

    	echo '<table class="selection">
    			<tr>
					<th class="ascending">' . _('Subtotal name') . '</th>
					<th class="ascending">' . _('Project ID') . '</th>
					<th class="ascending">' . _('Project Name') . '</th>
	<th class="ascending">' . _('Customer') . '</th>
	<th class="ascending">' . _('Project Manager') . '</th>
<th class="ascending">' . _('Project Status') . '</th>
<th class="ascending">' . _('Project Type') . '</th>
<th class="ascending">' . _('Approved Time') . '</th>
<th class="ascending">' . _('Estimated Time') . '</th>
<th class="ascending">' . _('Remaining Time') . '</th>
<th class="ascending">' . _('Percent Completed') . '</th>

';
echo'</tr>';

						$sqlstatuslist= "SELECT project_status_id,
						 project_status_name,
						 project_status_desc,
						 project_status_status
						 FROM paprojectstatus";
						$ErrMsg = _('The Status could not be loaded because');
						$DbgMsg = _('The SQL that was used to get the Status and failed was');
						$sqlstatuslist = DB_query($sqlstatuslist,$ErrMsg,$DbgMsg);

						while($myrowstatuslist = DB_fetch_array($sqlstatuslist))
						{

$sumduration=0;
					echo'<tr>	<td colspan="6"><b>'.$myrowstatuslist ['project_status_name'].'</b></td></tr>';

					$sqltime= "SELECT
					id,
					project_id ,
					project_name ,
					project_category,
					name,
					first_name,
					middle_name,
					last_name,
					project_status,
            project_status_name,
						project_type_name
					FROM paprojects
          Join debtorsmaster ON paprojects.customer=debtorsmaster.debtorno
					JOIN hremployees ON paprojects.project_manager=hremployees.empid
					JOIN paprojectstatus ON paprojects.project_status =paprojectstatus.project_status_id
					JOIN paprojecttypes ON paprojects.project_type =paprojecttypes.project_type_id
					WHERE
 	         project_status ='".$myrowstatuslist ['project_status_id']."' AND
					begin_date Between '".$Fromdate->format('Y-m-d')."' AND
					'".$Todate->format('Y-m-d')."'
					";
					$ErrMsg = _('The Projects could not be loaded because');
					$DbgMsg = _('The SQL that was used to get the Projects and failed was');
					$resulttime = DB_query($sqltime,$ErrMsg,$DbgMsg);

					while($myrowtime = DB_fetch_array($resulttime))
					{

$sqlplannedtime=
"SELECT
	plannedduration
 FROM paprojecttasks
				WHERE project_id='".$myrowtime['id']."'
	AND planbegindate  Between '".$Fromdate->format('Y-m-d')."' AND
				'".$Todate->format('Y-m-d')."'

				";
$plannedtime=0;
		$resultplannedtime = DB_query($sqlplannedtime);
		while ($myrowplannedtime = DB_fetch_array($resultplannedtime)) {
			$plannedtime=$plannedtime + $myrowplannedtime['plannedduration'];
		}

		$sqlapprovedtime= "SELECT
		 sun,
		 mon,
		 tue,
		 wed,
		 thu,
		 fri,
		 sat,
timesheet_status
		FROM patimesheetentries
		JOIN patimesheetsinfo on patimesheetentries.timesheetinfo_id = patimesheetsinfo.timesheetsinfo_id
		Where project_id='".$myrowtime['id']."'
		AND timesheet_status='1'
		AND patimesheetsinfo.begin_date  Between '".$Fromdate->format('Y-m-d')."' AND
		'".$Todate->format('Y-m-d')."'
		";
		$approvedtime=0;
				$resultapprovedtime = DB_query($sqlapprovedtime);
				while ($myrowapprovedtime = DB_fetch_array($resultapprovedtime)) {

			$totalduration=$myrowapprovedtime['sun']+$myrowapprovedtime['mon']+$myrowapprovedtime['tue']+$myrowapprovedtime['wed']+$myrowapprovedtime['thu']+$myrowapprovedtime['fri']+$myrowapprovedtime['sat'];
					$approvedtime=$approvedtime + $totalduration;
				}

echo'<tr><td></td>

<td>'.$myrowtime['project_id'].'</td>
<td>'.$myrowtime['project_name'].'</td>
<td>'.$myrowtime['name'].'</td>
<td>'.$myrowtime['first_name'].' '.$myrowtime['middle_name'].' '.$myrowtime['last_name'].'</td>
<td>'.$myrowtime['project_status_name'].'</td>
<td>'.$myrowtime['project_type_name'].'</td>
<td>'.$approvedtime.'</td>
<td>'.$plannedtime.'</td>
<td>'.($plannedtime-$approvedtime).'</td>
<td>'.((($approvedtime)/$plannedtime)*100).'</td>
<td></td>
</tr>';
					}
echo'
<tr><td td colspan="12"><hr></td></tr>';

}


    	echo '</table>';

}else{

	echo '<table class="selection">
			<tr>
			<th class="ascending">' . _('Subtotal name') . '</th>
			<th class="ascending">' . _('Project ID') . '</th>
			<th class="ascending">' . _('Project Name') . '</th>
<th class="ascending">' . _('Customer') . '</th>
<th class="ascending">' . _('Project Manager') . '</th>
<th class="ascending">' . _('Project Status') . '</th>
<th class="ascending">' . _('Project Type') . '</th>
<th class="ascending">' . _('Approved Time') . '</th>
<th class="ascending">' . _('Estimated Time') . '</th>
<th class="ascending">' . _('Remaining Time') . '</th>
<th class="ascending">' . _('Percent Completed') . '</th>

';
echo'</tr>';


			echo'<tr>	<td colspan="6"><b>'.$myrowstatuslist ['project_status_name'].'</b></td></tr>';

			$sqltime= "SELECT
			id,
			project_id ,
			project_name ,
			project_category,
			name,
			first_name,
			middle_name,
			last_name,
			project_status,
				project_status_name,
				project_type_name
			FROM paprojects
			Join debtorsmaster ON paprojects.customer=debtorsmaster.debtorno
			JOIN hremployees ON paprojects.project_manager=hremployees.empid
			JOIN paprojectstatus ON paprojects.project_status =paprojectstatus.project_status_id
			JOIN paprojecttypes ON paprojects.project_type =paprojecttypes.project_type_id
			WHERE
			 project_status ='".$_POST['Report']."' AND
			 begin_date Between '".$Fromdate->format('Y-m-d')."' AND
 			'".$Todate->format('Y-m-d')."'
			";
			$ErrMsg = _('The Projects could not be loaded because');
			$DbgMsg = _('The SQL that was used to get the Projects and failed was');
			$resulttime = DB_query($sqltime,$ErrMsg,$DbgMsg);

			while($myrowtime = DB_fetch_array($resulttime))
			{

				$sqlplannedtime=
				"SELECT
					plannedduration
				 FROM paprojecttasks
								WHERE project_id='".$myrowtime['id']."'
					AND planbegindate  Between '".$Fromdate->format('Y-m-d')."' AND
								'".$Todate->format('Y-m-d')."'

								";
				$plannedtime=0;
						$resultplannedtime = DB_query($sqlplannedtime);
						while ($myrowplannedtime = DB_fetch_array($resultplannedtime)) {
							$plannedtime=$plannedtime + $myrowplannedtime['plannedduration'];
						}

						$sqlapprovedtime= "SELECT
						 sun,
						 mon,
						 tue,
						 wed,
						 thu,
						 fri,
						 sat,
				timesheet_status
						FROM patimesheetentries
						JOIN patimesheetsinfo on patimesheetentries.timesheetinfo_id = patimesheetsinfo.timesheetsinfo_id
						Where project_id='".$myrowtime['id']."'
						AND timesheet_status='1'
						AND created_date  Between '".$Fromdate->format('Y-m-d')."' AND
						'".$Todate->format('Y-m-d')."'
						";
						$approvedtime=0;
								$resultapprovedtime = DB_query($sqlapprovedtime);
								while ($myrowapprovedtime = DB_fetch_array($resultapprovedtime)) {

							$totalduration=$myrowapprovedtime['sun']+$myrowapprovedtime['mon']+$myrowapprovedtime['tue']+$myrowapprovedtime['wed']+$myrowapprovedtime['thu']+$myrowapprovedtime['fri']+$myrowapprovedtime['sat'];
									$approvedtime=$approvedtime + $totalduration;
								}



echo'<tr><td></td>

<td>'.$myrowtime['project_id'].'</td>
<td>'.$myrowtime['project_name'].'</td>
<td>'.$myrowtime['name'].'</td>
<td>'.$myrowtime['first_name'].' '.$myrowtime['middle_name'].' '.$myrowtime['last_name'].'</td>
<td>'.$myrowtime['project_status_name'].'</td>
<td>'.$myrowtime['project_type_name'].'</td>
<td>'.$approvedtime.'</td>
<td>'.$plannedtime.'</td>
<td>'.($plannedtime-$approvedtime).'</td>
<td>'.((($approvedtime)/$plannedtime)*100).'</td>
<td></td>
</tr>';
			}



	echo '</table>';

}
}
include('includes/footer.php');
?>
