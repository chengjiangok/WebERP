<?php

/* $Id: PaProjectResourcesLabour.php 7772 2018-05-04 09:30:06Z bagenda $ */

include('includes/session.php');
//include('includes/SQL_CommonFunctions.inc');
$Title = _('Project Task Resources ');

$ViewTopic = 'Project Task Resources ';
$BookMark = 'Project Task Resources';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (isset($_POST['SelectedName'])){
	$SelectedName = mb_strtoupper($_POST['SelectedName']);
} elseif (isset($_GET['SelectedName'])){
	$SelectedName = mb_strtoupper($_GET['SelectedName']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Customer Types') .
	'" alt="" />' . _('Project Task Resources ') . '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['Project']) ==0) {
		$InputError = 1;
		prnMsg(_('Select the Project  Name first'),'error');
		$Errors[$i] = 'Project';
		$i++;
	}

	if (mb_strlen($_POST['Task'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('Select Task first'),'error');
		$Errors[$i] = 'Task';
		$i++;
	}

	if (mb_strlen($_POST['Employee'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('Select Employee first'),'error');
		$Errors[$i] = 'Employee';
		$i++;
	}

	if (mb_strlen($_POST['Description'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Description  must contain at least one character'),'error');
		$Errors[$i] = 'Description';
		$i++;
	}

	if(isset($_POST['Fulltime'])){
	$fulltime=1;
	}else {
	$fulltime=0;
	}

	$resources = "SELECT count(*)
		     FROM paprojectresourcelabour
		     WHERE project_id = '" . $_POST['Project'] . "'AND employee_id = '" . $_POST['Employee'] . "' ";
				;
	$checkresources=DB_query($resources);
	$checkrowresources=DB_fetch_row($checkresources);

	if ($checkrowresources[0]==0 ) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('This Employee').' '.$_POST['Employee'].' is not attached to this Project  '.$_POST['Project'].'','error');
		echo '<a href="' . $RootPath . '/PaProjectResourcesLabour.php">' . _('Add a Project Resources Labour') . '</a><br />' . "\n";

		$Errors[$i] = 'Employee';
		$i++;
	}

	$task = "SELECT count(*)
				 FROM paprojecttasks
				 WHERE project_id = '" . $_POST['Project'] . "'
				 AND  projecttask_id = '" . $_POST['Task'] . "' ";
				;
	$checktask=DB_query($task);
	$checkrowtask=DB_fetch_row($checktask);

	if ($checkrowtask[0]==0 ) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('This Task').' '.$_POST['Task'].' is not attached to this Project  '.$_POST['Project'].'','error');
		echo '<a href="' . $RootPath . '/PaProjectTasks.php">' . _('Add a Project Task') . '</a><br />' . "\n";

		$Errors[$i] = 'Employee';
		$i++;
	}





	$begindate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['Begindate']);
	$enddate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['Enddate']);

	$checksql = "SELECT count(*)
		     FROM paprojecttaskresources
		     WHERE project_id = '" . $_POST['Project'] . "'AND employee_id = '" . $_POST['Employee'] . "'
				 AND projecttask_id = '" . $_POST['Task'] . "' ";
				;
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('This Employee').' '.$_POST['Employee'].' is already offering this Service on Project'.$_POST['Project'].'','error');
		$Errors[$i] = 'Employee';
		$i++;
	}

	if (isset($SelectedName) AND $InputError !=1) {

		$sql = "UPDATE paprojecttaskresources
			SET project_id = '" . $_POST['Project'] . "',
employee_id= '" . $_POST['Employee']. "',
projecttask_id= '" . $_POST['Task']. "',
resource_begindate= '" .$begindate->format('Y-m-d'). "',
resource_enddate= '" .$enddate->format('Y-m-d'). "',

resource_desc= '" . $_POST['Description']. "',
resource_fulltime= '" . $fulltime. "',

resource_status= '" . $_POST['Status']. "'

			WHERE projecttaskresource_id = '" .$SelectedName."'";

		$msg = _('The Project task  Resource') . ' ' . $_POST['projecttaskresource_id']. ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the Name is not being duplicated

		$checkSql = "SELECT count(*)
		FROM paprojecttaskresources
	 WHERE project_id = '" . $_POST['Project'] . "'AND employee_id = '" . $_POST['Employee'] . "'
	 AND projecttask_id = '" . $_POST['Task'] . "' ";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg(_('This Employee').' '.$_POST['Employee'].' is already offering this Service on Project'.$_POST['Project'].'','error');

		} else {

			// Add new record on submit

			$sql = "INSERT INTO paprojecttaskresources
						(project_id,
						projecttask_id,
						employee_id,
						resource_begindate,
						 	resource_enddate ,
						 	resource_desc,
						 	resource_fulltime ,
						 	resource_status  )
					VALUES ('" . $_POST['Project'] . "',
'" . $_POST['Task'] . "',
'" . $_POST['Employee'] . "',
'" . $begindate->format('Y-m-d') . "',
'" . $enddate->format('Y-m-d') . "',
'" . $_POST['Description'] . "',
'" . $fulltime . "',
'" . $_POST['Status'] . "'
)";


			$msg = _('Project Task Resource assigned to Project ') . ' ' . $_POST["Project"];
			$checkSql = "SELECT count(projecttaskresource_id)
			     FROM paprojecttaskresources";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);

		echo '<br />';
		prnMsg($msg,'success');

		unset($SelectedName);
		unset($_POST['projecttaskresource_id']);
		unset($_POST['Project']);
		unset($_POST['Task']);
		unset($_POST['Begindate']);
		unset($_POST['Enddate']);
		unset($_POST['Description']);
		unset($_POST['Fulltime']);
		unset($_POST['Status']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'EMPLOYEE Positions'


	$sql= "SELECT COUNT(*)
	       FROM paprojects
	       WHERE project_type_id='".$SelectedName."'";

	$ErrMsg = _('The number of transactions using this Type Name could not be retrieved');
	$result = DB_query($sql,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this Type because Projects  have been created using this Type') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('Projects using this Type'),'error');

	}

	 else {
			$result = DB_query("SELECT project_type_name FROM paprojecttypes WHERE project_type_id='".$SelectedName."'");
			if (DB_Num_Rows($result)>0){
				$NameRow = DB_fetch_array($result);
				$TypeName = $NameRow['project_type_name'];

				$sql="DELETE FROM paprojecttypes WHERE project_type_id='".$SelectedName."'";
				$ErrMsg = _('The Type record could not be deleted because');
				$result = DB_query($sql,$ErrMsg);
				echo '<br />';
				prnMsg(_('Type Name') . ' ' . $TypeName  . ' ' . _('has been deleted') ,'success');
			}
			unset ($SelectedName);
			unset($_GET['delete']);

	} //end if Positions used in Employees set up
}

if (!isset($SelectedName)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPosition will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT projecttaskresource_id,
	 paprojects.project_id AS projectid,
	 project_name,
	 first_name,
	 middle_name,
	 last_name,
	 resource_begindate,
	  resource_enddate,
	 resource_fulltime,
	 resource_status,
	  projecttask_name
	 FROM paprojecttaskresources
JOIN hremployees on paprojecttaskresources.employee_id = hremployees.empid
JOIN paprojects on paprojecttaskresources.project_id = paprojects.id
JOIN paprojecttasks on paprojecttaskresources.projecttask_id = paprojecttasks.projecttask_id
	 ";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
	<th class="ascending">' . _('Project task Resource Id') . '</th>
 <th class="ascending">' . _('Project Id') . '</th>
  <th class="ascending">' . _('Project ') . '</th>
	  <th class="ascending">' . _('Employee') . '</th>
  <th class="ascending">' . _('Task') . '</th>
 <th class="ascending">' . _('Start Date') . '</th>
 <th class="ascending">' . _('End Date') . '</th>
 <th class="ascending">' . _('Full Time') . '</th>
<th class="ascending">' . _('Status') . '</th>
		</tr>';

		$k=0; //row colour counter
		while ($myrow = DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

printf('<td>%s</td>
		<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
<td>%s</td>
		<td><a href="%sSelectedName=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedName=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Category Name?') . '");\'>' . _('Delete') . '</a></td>
		</tr>',
		$myrow['projecttaskresource_id'],
		$myrow['projectid'],
		$myrow['project_name'],
		$myrow['first_name'].' '.$myrow['middle_name'].' '.$myrow['last_name'],
		$myrow['projecttask_name'],
		$myrow['resource_begindate'],
		$myrow['resource_enddate'],
		($myrow['resource_fulltime'] == 1) ? 'YES' : 'NO',
		($myrow['resource_status'] == 1) ? 'Active' : 'Inactive',
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['projecttaskresource_id'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['projecttaskresource_id']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedName)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Task Resource Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing name
	if ( isset($SelectedName) AND $SelectedName!='' ) {

		$sql = "SELECT
projecttaskresource_id,
		 project_id,
		projecttask_id,
		employee_id,
		resource_begindate,
			resource_enddate,
			resource_desc,
			resource_fulltime ,
			resource_status
		 FROM paprojecttaskresources
		        WHERE projecttaskresource_id='".$SelectedName."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['projecttaskresource_id'] = $myrow['projecttaskresource_id'];
		$_POST['Project']  = $myrow['project_id'];
			$_POST['Employee']  = $myrow['employee_id'];
		$_POST['Task']  = $myrow['projecttask_id'];
		$_POST['Begindate']  = $myrow['resource_begindate'];
		$_POST['Enddate']  = $myrow['resource_enddate'];
	$_POST['Description']  = $myrow['resource_desc'];

$_POST['Fulltime']  = $myrow['resource_fulltime'];
$_POST['status']  = $myrow['resource_status'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="projecttaskresource_id" value="' . $_POST['projecttaskresource_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('Task Resource ID') . ': ' . $_POST['projecttaskresource_id'] . '</td>
			</tr>';
	} else 	{
		// This is a new Name so the user may volunteer a Name code
		echo '<table class="selection">';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName']='';
	}

	$Employees = array();
	$Query = "SELECT empid, employee_id,first_name,middle_name,last_name  FROM hremployees";
	$Result = DB_query($Query);
	while ($Row = DB_fetch_array($Result)) {
		$Employees[$Row['empid']] = $Row['employee_id'].' '.$Row['first_name'].' '.$Row['middle_name'].' '.$Row['last_name'];
	}

	$Projects = array();
	$Query1 = "SELECT  	id,project_id , project_name  FROM paprojects";
	$Result1 = DB_query($Query1);
	while ($Row1 = DB_fetch_array($Result1)) {
		$Projects[$Row1['id']] = $Row1['project_id'].' '.$Row1['project_name'];
	}

	$Tasks = array();
	$Query1 = "SELECT projecttask_id, projecttask_name FROM paprojecttasks";
	$Result1 = DB_query($Query1);
	while ($Row1 = DB_fetch_array($Result1)) {
		$Tasks[$Row1['projecttask_id']] = $Row1['projecttask_id'].' '.$Row1['projecttask_name'];
	}



	echo '<tr>
			<td>' . _('Project') . ':</td>
			<td>
			<select name="Project">';
			  foreach ($Projects as $ProjectId => $Row) {
			echo'<option '._(($_POST['Project']== $ProjectId )? 'selected ' : '').' value="'.$ProjectId.'">'.$Row.'</option>';
				}
				echo'</select></td>
		</tr>
		<tr>
				<td>' . _('Task') . ':</td>
				<td>

								<select name="Task">';
									foreach ($Tasks as $taskId => $Row) {
								echo'<option '._(($_POST['Task']== $taskId )? 'selected ' : '').' value="'.$taskId.'">'.$Row.'</option>';
									}
									echo'</select>

				</td>
			</tr>
		<tr>
				<td>' . _('Employee') . ':</td>
				<td>

				<select name="Employee">';
				  foreach ($Employees as $EmpId => $Row) {
				echo'<option '._(($_POST['Employee']== $EmpId )? 'selected ' : '').' value="'.$EmpId.'">'.$Row.'</option>';
					}
					echo'</select></td>
			</tr>';

			if (isset($SelectedName)) {
				$Begindate= $_POST['Begindate'];
				$Enddate= $_POST['Enddate'];
			}else{
			$Begindate=date('Y-m-d');
				$Enddate=date('Y-m-d');
			}

			echo'<tr><td>' . _('Planned Begin date') . ':</td>
					<td><input type="text" name="Begindate" required="required" class="datepicker"  class="datepicker" required="required" title="' . _('Planned Begin Date is required') . '"  value="' .ConvertSQLDate($Begindate).'""/></td>
				</tr>

				<tr>
						<td>' . _('Planned End date') . ':</td>
						<td><input type="text" name="Enddate" required="required" class="datepicker"  class="datepicker" required="required" title="' . _('Planned End Date is required') . '"  value="' .ConvertSQLDate($Enddate).'""/></td>
					</tr>


		<tr>
				<td>' . _(' Description') . ':</td>

<td><textarea  name="Description">'.$_POST['Description'].'</textarea></td>
			</tr>

			<tr>
					<td>' . _('Full Time') . ':</td>
					<td><input  '._(($_POST['Fulltime']==1)? ' checked ':' ').' type="checkbox" name="Fulltime"   value="' .$_POST['Fulltime'].'""/></td>
				</tr>



			<tr><td><label for="Status">' . _('Status') .
			  ':</label></td>
			  <td><input type="radio"';
				if (! isset($SelectedName)) {
			   echo ' checked';}
			  if (isset($_POST['status']) and $_POST['status']==1) {
			    echo ' checked';}
			echo'
			   name="Status" value="1"> Active

			  <input';
			  if (isset($_POST['status']) and $_POST['status']==0) {
			    echo ' checked';
			  }
			echo'

			  type="radio" name="Status" value="0"> Inactive
			  </td></tr>


		</table>
		<br />
		<div class="centre">
			<input type="submit" name="submit" value="' . _('Accept') . '" />
		</div>
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


} // end if user wish to delete

include('includes/footer.php');
?>
