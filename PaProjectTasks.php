<?php

/* $Id: PaProjectResourcesLabour.php 7772 2018-05-04 09:30:06Z bagenda $ */

include('includes/session.php');
//include('includes/SQL_CommonFunctions.inc');
$Title = _('Project Tasks');

$ViewTopic = 'Project Tasks';
$BookMark = 'Project Tasks';
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
	'" alt="" />' . _('Project Tasks ') . '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;



	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TaskName']) ==0) {
		$InputError = 1;
		prnMsg(_('The Task Name  must contain at least one character'),'error');
		$Errors[$i] = 'Project';
		$i++;
	}

	if (mb_strlen($_POST['Begindate'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('Select Begin Date  first'),'error');
		$Errors[$i] = 'Begindate';
		$i++;
	}

	if (mb_strlen($_POST['Enddate'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('Select Begin Date  first'),'error');
		$Errors[$i] = 'Enddate';
		$i++;
	}

	if($_POST['Labourrate']==0){
		$labourrate=0;
	}else{

		$labourrate=$_POST['Labourrate'];
	}

	if($_POST['Expense']==0){
		$expense=0;
	}else{

		$expense=$_POST['Expense'];
	}

	if($_POST['Pricing']==0){
		$pricing=0;
	}else{

		$pricing=$_POST['Pricing'];
	}

	if(isset($_POST['Billable'])){
	$billabe=1;
	}else {
	$billabe=0;
	}

	if(isset($_POST['Milestone'])){
	$milestone=1;
	}else {
	$milestone=0;
	}
	if(isset($_POST['Utilized'])){
	$utilized=1;
	}else {
	$utilized=0;
	}
	if($_POST['Priority']==0){
	$priority=1;
	}else {
	$priority=$_POST['Priority'];
	}
	$begindate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['Begindate']);
	$enddate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['Enddate']);



	$checksql = "SELECT count(*)
		     FROM paprojecttasks
		     WHERE projecttask_name = '" . $_POST['TaskName'] . "'";
				;
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('This Task').' '.$_POST['TaskName'].' already exits','error');
		$Errors[$i] = 'TaskName';
		$i++;
	}

	if (isset($SelectedName) AND $InputError !=1) {

		$sql = "UPDATE paprojecttasks
			SET
			projecttask_name = '" . $_POST['TaskName'] . "',
			project_id = '" . $_POST['Project'] . "',
planbegindate= '" . $begindate->format('Y-m-d'). "',
planenddate= '" . $enddate->format('Y-m-d'). "',
plannedduration= '" . $_POST['Plannedduration']. "',
dependanttask= '" . $_POST['Dependent']. "',
taskbillable= '" . $billabe. "',
taskdesc= '" . $_POST['TaskDescription']. "',
taskmilestone= '" . $milestone. "',
taskutilized= '" . $utilized. "',
taskpriority= '" . $priority. "',
taskwbscode= '" . $_POST['WBScode']. "',
taskstatusid= '" . $_POST['Taskstatus']. "',
parent_task= '" . $_POST['Parenttask']. "'

			WHERE projecttask_id = '" .$SelectedName."'";

		$msg = _('The Task') . ' ' . $_POST['TaskName']. ' ' .  _('has been updated');

		if (isset($_POST['ClearFile']) ) {
	 		$file = $_SESSION['part_pics_dir'] . '/Task_' . $SelectedName. '.pdf';
	 		if (file_exists ($file) ) {
	 			//workaround for many variations of permission issues that could cause unlink fail
	 			@unlink($file);
	 			if(is_file($imagefile)) {
	 								prnMsg(_('You do not have access to delete this employee image file.'),'error');
	 			} else {
	 				$AssetImgLink = _('No File');
	 			}

	 	}
	  }


	} elseif ( $InputError !=1 ) {

		// First check the Name is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM paprojectresourcelabour
			     WHERE project_id = '" . $_POST['Project'] . "'AND employee_id = '" . $_POST['Employee'] . "'
					 AND services = '" . $_POST['Service'] . "' ";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg(_('This Employee').' '.$_POST['Employee'].' is already offering this servce on Project'.$_POST['Project'].'','error');

		} else {

			// Add new record on submit

			$sql = "INSERT INTO paprojecttasks
						(projecttask_name,
						project_id,
						planbegindate,
						 planenddate,
						 	plannedduration,
						dependanttask,

						taskbillable,
						 	taskdesc,
						taskmilestone,
            taskutilized,
             	taskpriority,
							taskwbscode,
								taskstatusid,
              parent_task

					 )
					VALUES ('" . $_POST['TaskName'] . "',
'" . $_POST['Project'] . "',
'" . $begindate->format('Y-m-d'). "',
'" . $enddate->format('Y-m-d'). "',
'" . $_POST['Plannedduration']. "',
'" .$_POST['Dependent']. "',
'" .$billabe . "',
'" . $_POST['TaskDescription']  . "',
'" . $milestone ."',
'" .$utilized . "',
'" . $priority . "',
'" . $_POST['WBScode'] . "',
'" . $_POST['Taskstatus']  . "',
'" . $_POST['Parenttask'] . "'

)";


			$msg = _('Project Task ') . ' ' . $_POST["TaskName"].' has been created';
			$checkSql = "SELECT count(projecttask_id)
			     FROM paprojecttasks";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);
$afterinsert=1;
		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);

		if($afterinsert==1){
			$SelectedName	 = DB_Last_Insert_ID($db,'paprojecttasks','projecttask_id');
		}

		if (isset($_FILES['Attachments']) AND $_FILES['Attachments']['name'] ) {
		 $FileExt = pathinfo($_FILES['Attachments']['name'], PATHINFO_EXTENSION);

		 $result    = $_FILES['Attachments']['error'];
		 $UploadTheFile = 'Yes'; //Assume all is well to start off with
		 $filename = $_SESSION['part_pics_dir'] . '/Task_' . $SelectedName . '.' . $FileExt;
		 if ($FileExt!="pdf") {
			 prnMsg(_('Only pdf files are supported - a file extension of pdf is expected'),'warn');
			 $UploadTheFile ='No';
		 } elseif ( $_FILES['Attachments']['size'] < 5024) { //File Size Check
			 prnMsg(_('The file size is over the maximum allowed. The maximum size allowed in KB is') . ' 5024 ','warn');
			 $UploadTheFile ='No';
		 }
		 if (file_exists ($filename) ) {
			 $result = unlink($filename);
			 if (!$result){
				 prnMsg(_('The existing File could not be removed'),'error');
				 $UploadTheFile ='No';
			 }
		 }

		 if ($UploadTheFile=='Yes'){
			 $result  =  move_uploaded_file($_FILES['Attachments']['tmp_name'], $filename);
			 $message = ($result)?_('File url')  . '<a href="' . $filename .'">' .  $filename . '</a>' : _('Something is wrong with uploading a file');
		 }


		}

		echo '<br />';
		prnMsg($msg,'success');

		unset($SelectedName);
		unset($_POST['projecttasks_id']);
		unset($_POST['TaskName']);
		unset($_POST['Project']);
		unset($_POST['Begindate']);
		unset($_POST['Enddate']);
		unset($_POST['Plannedduration']);
		unset($_POST['Dependent']);
		unset($_POST['Servicetask']);
		unset($_POST['Billabe']);
		unset($_POST['Description']);
		unset($_POST['Milestone']);
		unset($_POST['Utilized']);
		unset($_POST['Priority']);
		unset($_POST['WBScode']);
		unset($_POST['Taskstatus']);
		unset($_POST['Parenttask']);
			unset($_POST['Attachments']);
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

	$sql = "SELECT projecttask_id,
	 paprojects.project_id AS projectid,
	 projecttask_name ,
	 servicetask,
	 project_name,
	 planbegindate,
     planenddate,
     taskstatus,
	 taskbillable
	 FROM paprojecttasks
JOIN paprojects on paprojecttasks.project_id = paprojects.id
JOIN paprojecttaskstatus on paprojecttasks.taskstatusid = paprojecttaskstatus.taskstatusid
	 ";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
	<th class="ascending">' . _('Project Task Id') . '</th>
	 <th class="ascending">' . _('Task Name') . '</th>
 <th class="ascending">' . _('Project Id') . '</th>
  <th class="ascending">' . _('Project ') . '</th>
	  <th class="ascending">' . _('Start Date') . '</th>
  <th class="ascending">' . _('End Date') . '</th>


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

		<td><a href="%sSelectedName=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedName=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Category Name?') . '");\'>' . _('Delete') . '</a></td>
		</tr>',
		$myrow['projecttask_id'],
		$myrow['projecttask_name'],
		$myrow['projectid'],
		$myrow['project_name'],
		$myrow['planbegindate'],
			$myrow['planenddate'],
	
		$myrow['taskstatus'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['projecttask_id'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['projecttask_id']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedName)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Tasks Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

echo '<form method="post"
	action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '"
enctype="multipart/form-data">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing name
	if ( isset($SelectedName) AND $SelectedName!='' ) {

		$sql = "SELECT projecttask_id,
		projecttask_name,
		project_id,
		planbegindate,
		 planenddate,
		dependanttask,
		servicetask,
		taskbillable,
			taskdesc,
		taskmilestone,
		taskutilized,
			taskpriority,
			taskwbscode,
				taskstatusid,
			parent_task,
			plannedduration
		 FROM paprojecttasks
		        WHERE projecttask_id='".$SelectedName."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['projecttask_id'] = $myrow['projecttask_id'];
		$_POST['TaskName']  = $myrow['projecttask_name'];
		$_POST['Project']  = $myrow['project_id'];
			$_POST['Begindate']  = $myrow['planbegindate'];
			$_POST['Enddate']  = $myrow['planenddate'];
			$_POST['Plannedduration']  = $myrow['plannedduration'];
			$_POST['Dependent']  = $myrow['dependanttask'];
		$_POST['Service']  = $myrow['servicetask'];
		$_POST['Billable']  = $myrow['taskbillable'];
		$_POST['Description']  = $myrow['taskdesc'];
		$_POST['Milestone']  = $myrow['taskmilestone'];
	$_POST['Utilized']  = $myrow['taskutilized'];
	$_POST['Milestone']  = $myrow['taskmilestone'];
	$_POST['Utilized']  = $myrow['taskutilized'];
$_POST['Priority']  = $myrow['taskpriority'];
$_POST['WBScode']  = $myrow['taskwbscode'];
$_POST['Taskstatus']  = $myrow['taskstatusid'];
$_POST['Parenttask']  = $myrow['parent_task'];
$_POST['Attachments']  = $myrow['attachment'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="projecttask_id" value="' . $_POST['projecttask_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('Task ID') . ': ' . $_POST['projecttask_id'] . '</td>
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
	$Query4 = "SELECT  projecttask_id , projecttask_name  FROM paprojecttasks";
	$Result4 = DB_query($Query4);
	while ($Row4 = DB_fetch_array($Result4)) {
		$Tasks[$Row4['projecttask_id']] = $Row4['projecttask_id'].' '.$Row4['projecttask_name'];
	}

	$Services = array();
	$Query1 = "SELECT stockid, description FROM stockmaster
	Where mbflag ='D'";
	$Result1 = DB_query($Query1);
	while ($Row1 = DB_fetch_array($Result1)) {
		$Services[$Row1['stockid']] = $Row1['description'];
	}

	$taskstatus = array();
	$Query2 = "SELECT  	taskstatusid,taskstatus  FROM paprojecttaskstatus";
	$Result2 = DB_query($Query2);
	while ($Row2 = DB_fetch_array($Result2)) {
		$taskstatus[$Row2['taskstatusid']] = $Row2['taskstatus'];
	}

	echo '

	<tr>
			<td>' . _('Name ') . ':</td>
			<td><input type="text" name="TaskName"  required="required" title="' . _('The Task Name is required') . '" value="' . $_POST['TaskName'] . '" /></td>
		</tr>
		<tr>



	<tr>
			<td>' . _('Project') . ':</td>
			<td>
			<select name="Project">';
			  foreach ($Projects as $ProjectId => $Row) {
			echo'<option '._(($_POST['Project']== $ProjectId )? 'selected ' : '').' value="'.$ProjectId.'">'.$Row.'</option>';
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

		echo' <tr>
				<td>' . _('Planned Begin date') . ':</td>
				<td><input type="text" name="Begindate" required="required"   class="datepicker" required="required" title="' . _('Planned Begin Date is required') . '"  value="' .ConvertSQLDate($Begindate).'""/></td>
			</tr>

			<tr>
					<td>' . _('Planned End date') . ':</td>
					<td><input type="text" name="Enddate" required="required"   class="datepicker" required="required" title="' . _('Planned End Date is required') . '"  value="' .ConvertSQLDate($Enddate).'""/></td>
				</tr>
				<tr>
						<td>' . _('Planned Duration(Hrs)') . ':</td>
						<td><input type="text" name="Plannedduration" required="required"    required="required" title="' . _('Planned Duration is required') . '"  value="' .$_POST['Plannedduration'].'""/></td>
					</tr>


		<tr>
				<td>' . _('Dependent on task') . ':</td>
				<td>

				<select name="Dependent">';
				echo'<option value="0">None</option>';
				  foreach ($Tasks as $taskId => $Row) {
				echo'<option '._(($_POST['Dependent']== $taskId )? 'selected ' : '').' value="'.$taskId.'">'.$Row.'</option>';
					}
					echo'</select></td>
			</tr>

				<tr>
						<td>' . _('Billable') . ':</td>
						<td><input '._(($_POST['Billable']==1)? ' checked ':' ').' type="checkbox" name="Billable" "/></td>
					</tr>

		<tr>
				<td>' . _(' Description') . ':</td>

<td><textarea  name="TaskDescription">'.$_POST['TaskDescription'].'</textarea></td>
			</tr>
			<tr>
					<td>' . _('Milestone') . ':</td>
					<td><input  '._(($_POST['Milestone']==1)? ' checked ':' ').' type="checkbox" name="Milestone"   value="' .$_POST['Milestone'].'""/></td>
				</tr>
				<tr>
						<td>' . _('Utilized') . ':</td>
						<td><input '._(($_POST['Utilized']==1)? ' checked ':' ').' type="checkbox" name="Utilized"     value="' .$_POST['Utilized'].'""/></td>
					</tr>

			<tr>
					<td>' . _('Priority') . ':</td>
					<td><input type="text" name="Priority"    value="' .$_POST['Priority'].'""/></td>
				</tr>

				<tr>
						<td>' . _('WBS code') . ':</td>
						<td><input type="text" name="WBScode"   value="' . $_POST['WBScode'] . '" /></td>
					</tr>

					<tr>
							<td>' . _('Task Status') . ':</td>
							<td>

							<select name="Taskstatus">';
								foreach ($taskstatus as $taskid => $Row) {
							echo'<option '._(($_POST['Taskstatus']== $taskid )? 'selected ' : '').' value="'.$taskid.'">'.$Row.'</option>';
								}
								echo'</select></td>
						</tr>

						<tr>
								<td>' . _('Parent task') . ':</td>
								<td>

								<select name="Parenttask">';
								echo'<option value="0">None</option>';
									foreach ($Tasks as $taskId => $Row) {
								echo'<option '._(($_POST['Parenttask']== $taskId )? 'selected ' : '').' value="'.$taskId.'">'.$Row.'</option>';
									}
									echo'</select></td>
							</tr>
							<tr>
															<td>' . _('Attachments (*pdf only)') . ':</td>
															<td><input type="file" name="Attachments"   value="' . $_POST['Attachments'] . '" />';
							$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/Task_' . $_POST['projecttask_id'] . '.pdf')));
							if (file_exists ($imagefile)) {
							echo '<br><a href="' . $imagefile . '">Task_' . $_POST['projecttask_id'] . '.pdf'. '</a>
							<br><input type="checkbox" name="ClearFile" id="ClearFile" value="1" > '._('Clear File').'

							';
							} else {
							echo '<br>'._('No File ');
							}

															echo'</td>
														</tr>';


		echo'</table>
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
