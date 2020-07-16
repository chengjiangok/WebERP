<?php

/* $Id: PaProjectResourcesLabour.php 7772 2018-05-04 09:30:06Z bagenda $ */

include('includes/session.php');
//include('includes/SQL_CommonFunctions.inc');
$Title = _('Time Sheets');

$ViewTopic = 'Time Sheets ';
$BookMark = 'Time Sheets';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (isset($_POST['SelectedName']))
{
	$SelectedName = mb_strtoupper($_POST['SelectedName']);
} elseif (isset($_GET['SelectedName']))
{
	$SelectedName = mb_strtoupper($_GET['SelectedName']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Customer Types') .
	'" alt="" />' . _('Time Sheets ') . '</p>';

//Timesheet Info
if (isset($_POST['submit']))
  {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
$afterinsert=0;
	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;


	if (mb_strlen($_POST['Begindate'])==0)
	{
		$InputError = 1;
		echo '<br />';
		prnMsg(_('Enter Start Date'),'error');
		$Errors[$i] = 'Begindate';
		$i++;
	}


	if (mb_strlen($_POST['Employee'])==0)
	{
		$InputError = 1;
		echo '<br />';
		prnMsg(_('Select Employee first'),'error');
		$Errors[$i] = 'Employee';
		$i++;
	}

	if (mb_strlen($_POST['Description'])==0)
	{
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Description  must contain at least one character'),'error');
		$Errors[$i] = 'Description';
		$i++;
	}



$week_date =$_POST['Begindate'];

	$day = date('w',strtotime($_POST['Begindate']));
	$week_start = date('Y-m-d', strtotime($week_date.'-'.$day.' days'));
	$week_end = date('Y-m-d', strtotime($week_date.'+'.(6-$day).' days'));



	$checksql = "SELECT count(*)
		     FROM patimesheetsinfo
		     WHERE employee_id = '" . $_POST['Employee'] . "'AND begin_date = '" .$week_start. "'
				 AND end_date = '" . 	$week_end. "' ";
				;
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName))
	{
		$InputError = 1;
		echo '<br />';
		prnMsg(_('This Employee').' '.$_POST['Employee'].'  already has a Time Sheet for this Week ('.$week_start.' - '.$week_end.')','error');
		$Errors[$i] = 'Employee';
		$i++;
	}

	if (isset($SelectedName) AND $InputError !=1)
	 {

		 $query ="SELECT manager_id FROM hremployees WHERE empid='". $_POST['Employee']."'";
	 		$result2 = DB_query($query);
	 		$EmployeeManager = DB_fetch_array($result2);
	 		$ManagerId = $EmployeeManager['manager_id'];

	 		if($_POST['Status']==1){

	 		if((in_array('25',$_SESSION['AllowedPageSecurityTokens'])) ){
	 			}else{
	 				$InputError = 1;
	 				echo '<br />';
	 				prnMsg(_('Your not Authorised to Approve Timesheets'),'error');
	 				$Errors[$i] = 'Status';
	 				$i++;

	 			}


	 		}


			if ($_POST['Status']==1){
$approveduser=$_SESSION['UserID'];

}else{
$approveduser='null';

}

		$sql = "UPDATE patimesheetsinfo
			SET employee_id = '" . $_POST['Employee'] . "',
begin_date= '" . $week_start. "',
end_date= '" . $week_end. "',
timesheet_desc= '" . $_POST['Description']. "',
attachment= '" . $_POST['Attachments']. "',
timesheet_status= '" . $_POST['Status']. "',
approved_by ='".$approveduser."'
			WHERE timesheetsinfo_id = '" .$SelectedName."'";

		$msg = _('Time Sheet Info ') . ' ' . $_POST['timesheetsinfo_id']. ' ' .  _('has been updated');
			 if (isset($_POST['ClearFile']) ) {
			 		$file = $_SESSION['part_pics_dir'] . '/Timesheet_' . $SelectedName. '.pdf';
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


	} elseif ( $InputError !=1 )

	{


		if($_POST['Status']==1){

		if((in_array('25',$_SESSION['AllowedPageSecurityTokens'])) ){
		 }else{
			 $InputError = 1;
			 echo '<br />';
			 prnMsg(_('Your not Authorised to Approve Timesheets'),'error');
			 $Errors[$i] = 'Status';
			 $i++;

		 }


	 }

			// Add new record on submit

			$sql = "INSERT INTO patimesheetsinfo
						(employee_id,
						begin_date,
						end_date,
						timesheet_desc,
						 attachment,
					 timesheet_status  )
					VALUES ('" . $_POST['Employee'] . "',
'" . $week_start. "',
'" . $week_end. "',
'" . $_POST['Description'] . "',
'" . $_POST['Attachments'] . "',
'" . $_POST['Status'] . "'
)";


			$msg = _('Timesheet for '.$_POST['Employee'].'has been added ');
			$checkSql = "SELECT count(timesheetsinfo_id)
			     FROM patimesheetsinfo";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);
$afterinsert=1;


	}

	if ( $InputError !=1)
	{
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);

   if($afterinsert==1){
		 $SelectedName	 = DB_Last_Insert_ID($db,'patimesheetsinfo','timesheetsinfo_id');
	 }

	 if (isset($_FILES['Attachments']) AND $_FILES['Attachments']['name'] ) {
	  $FileExt = pathinfo($_FILES['Attachments']['name'], PATHINFO_EXTENSION);

	  $result    = $_FILES['Attachments']['error'];
	  $UploadTheFile = 'Yes'; //Assume all is well to start off with
	  $filename = $_SESSION['part_pics_dir'] . '/Timesheet_' . $SelectedName . '.' . $FileExt;
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

		//unset($SelectedName);
		//unset($_POST['employee_id']);
		//unset($_POST['EMPLOYEE']);
		//unset($_POST['Task']);
		//unset($_POST['Begindate']);
		//unset($_POST['Enddate']);
		//unset($_POST['Description']);
		//unset($_POST['Fulltime']);
		//unset($_POST['Status']);
	}

} elseif ( isset($_GET['delete']) )

{

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'EMPLOYEE Positions'



				$sql="DELETE FROM patimesheetsinfo WHERE timesheetsinfo_id='".$SelectedName."'";
				$ErrMsg = _('The Timesheet info record could not be deleted because');
				$result = DB_query($sql,$ErrMsg);
				echo '<br />';
				prnMsg(_('Timesheet info ') . ' ' . $SelectedName  . ' ' . _('has been deleted') ,'success');

			unset ($SelectedName);
			unset($_GET['delete']);
 //end if Positions used in Employees set up
}

//Time sheet Entries
if( isset($_POST['Entry'])){

	if($_POST['Sun']==0)
 	{
 	$sun =0;
 	}else{
  $sun=$_POST['Sun'];
 	}

if($_POST['Mon']==0)
 {
	$mon =0;
 }else{
$mon=$_POST['Mon'];
 }
 if($_POST['Tue']==0)
  {
 	$tue =0;
  }else{
 $tue=$_POST['Tue'];
  }

	if($_POST['Wed']==0)
   {
  	$wed =0;
   }else{
  $wed=$_POST['Wed'];
   }

	 if($_POST['Thu']==0)
	  {
	 	$thu =0;
	  }else{
	 $thu=$_POST['Thu'];
	  }
		if($_POST['Fri']==0)
	 	{
	 	$fri =0;
	 	}else{
	  $fri=$_POST['Fri'];
	 	}
		if($_POST['Sat']==0)
 	  {
 	 	$sat =0;
 	  }else{
 	 $sat=$_POST['Sat'];
 	  }

		$sqlemployee = "SELECT
		employee_id
		 FROM patimesheetsinfo
	WHERE timesheetsinfo_id = '" .$_POST['Timesheetinfo']."'
		 ";


		 $checkemployee=DB_query($sqlemployee);
		 $getemployee=DB_fetch_array($checkemployee);

		 $employeename = "SELECT
		 first_name,
		 middle_name,
		 last_name,
		 employee_id
		  FROM hremployees
		 WHERE empid = '" .$getemployee['employee_id']."'
		  ";
		  $checkemployeename=DB_query($employeename);
		  $getemployeename=DB_fetch_array($checkemployeename);
		$checkresource = "SELECT count(*)
					 FROM paprojectresourcelabour
					 WHERE project_id = '" . $_POST['Project'] . "'
					 AND employee_id = '" .$getemployee['employee_id']."'
					 ";

		$checkresultresource=DB_query($checkresource);
		$checkrowresource=DB_fetch_row($checkresultresource);
		if ($checkrowresource[0]==0){

			$InputError = 1;
			$SelectedName	 = $_POST['Timesheetinfo'];
			echo '<br />';
			prnMsg(_('First Add Project Resources/Labour For this Employee ').' '.$getemployeename['first_name'].' '.$getemployeename['middle_name'].' '.$getemployeename['last_name'],'error');
echo'<a href="'. $RootPath . '/PaProjectResourcesLabour.php">' . _('Add Project Resources/Labour') . '</a><br />' . "\n";
			$Errors[$i] = 'Task';
			$i++;
		}

		$checktask = "SELECT count(*)
					 FROM paprojecttaskresources
					 WHERE project_id = '" . $_POST['Project'] . "'
					 AND  projecttask_id='" . $_POST['Task'] . "'
					 AND employee_id = '" .$getemployee['employee_id']."'
					 ";

		$checkresulttask=DB_query($checktask);
		$checkrowtask=DB_fetch_row($checkresulttask);
		if ($checkrowtask[0]==0){

			$InputError = 1;
			$SelectedName	 = $_POST['Timesheetinfo'];
			echo '<br />';
			prnMsg(_(' Employee').' '.$getemployeename['first_name'].' '.$getemployeename['middle_name'].' '.$getemployeename['last_name'].' is not assigned to this Task '.$_POST['Task'],'error');
		echo'<a href="'. $RootPath . '/PaProjectTaskResources.php">' . _('Add Project Task Resource') . '</a><br />' . "\n";
			$Errors[$i] = 'Task';
			$i++;
		}



	$checksql = "SELECT count(*)
				 FROM patimesheetentries
				 WHERE project_id = '" . $_POST['Project'] . "'
				 AND projecttask_id = '" .$_POST['Task']."'
				 AND timesheetinfo_id = '" .$_POST['Timesheetinfo']."'
				 ";
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($_POST['SelectedEntry']))
	{
		$InputError = 1;
		$SelectedName	 = $_POST['Timesheetinfo'];
		echo '<br />';
		prnMsg(_('Time Sheet for this Task').' '.$_POST['Task'].'  is alredy Entered ','error');
		$Errors[$i] = 'Task';
		$i++;
	}


	if (isset($_POST['SelectedEntry']) AND $InputError !=1)
	 {

		$sql = "UPDATE patimesheetentries
			SET  	timesheetinfo_id = '" . $_POST['Timesheetinfo'] . "',
	project_id= '" . $_POST['Project']. "',
	projecttask_id= '" . $_POST['Task']. "',
	sun= '" . $sun. "',
	mon= '" . $mon. "',
	tue= '" . $tue. "',
	wed= '" . $wed. "',
	thu= '" . $thu. "',
	fri= '" . $fri. "',
	sat= '" . $sat. "'
			WHERE timesheetentry_id = '" .$_POST['SelectedEntry']."'";

		$msg = _('Time Sheet Entry') . ' ' . $_POST['timesheetentry_id']. ' ' .  _('has been updated');
$ErrMsgupdate = _('The Time sheet Entry for this Project '.$_POST['Project'].' and Task '.$_POST['Task'].'  is already Exits');
		$result = DB_query($sql,$ErrMsgupdate);

unset ($_POST['timesheetentry_id']);
unset ($_POST['Project']);
unset ($_POST['Task']);
unset ($_POST['Sun']);
unset ($_POST['Mon']);
unset ($_POST['Tue']);
unset ($_POST['Wed']);
unset ($_POST['Thu']);
unset ($_POST['Fri']);
unset ($_POST['Sat']);

$SelectedName	 = $_POST['Timesheetinfo'];

		echo '<br />';
		prnMsg($msg,'success');


	} elseif ( $InputError !=1 )

	{



			// Add new record on submit

			$sql = "INSERT INTO patimesheetentries
						(timesheetinfo_id ,
						project_id,
						projecttask_id,
						sun,
						 mon,
						 tue,
						 wed,
						 thu,
						 fri,
						 sat
					    )
					VALUES ('" . $_POST['Timesheetinfo'] . "',
	'" . $_POST['Project']. "',
	'" . $_POST['Task']. "',
	'" . $sun . "',
	'" . $mon . "',
	'" . $tue. "',
	'" . $wed . "',
	'" . $thu . "',
	'" . $fri. "',
		'" . $sat. "'
	)";


			$msg = _('Timesheet  Entry for '.$_POST['Employee'].'has been added ');
			$checkSql = "SELECT count(timesheetentry_id)
					 FROM patimesheetentries";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);

$result = DB_query($sql);

unset ($_POST['Project']);
unset ($_POST['Task']);
unset ($_POST['Sun']);
unset ($_POST['Mon']);
unset ($_POST['Tue']);
unset ($_POST['Wed']);
unset ($_POST['Thu']);
unset ($_POST['Fri']);
unset ($_POST['Sat']);
echo '<br />';
prnMsg($msg,'success');



	$SelectedName	 = $_POST['Timesheetinfo'];

	}


}elseif ( isset($_GET['deleteEntry']) )

{


				$sql="DELETE FROM patimesheetentries WHERE timesheetentry_id='".$_GET['SelectedEntry']."'";
				$ErrMsg = _('The Timesheet  Entry info record could not be deleted because');
				$result = DB_query($sql,$ErrMsg);
				echo '<br />';
				prnMsg(_('Timesheet Entry  ') . ' ' .$_GET['SelectedEntry'] . ' ' . _('has been deleted') ,'success');
$SelectedName	 = $_GET['info'];
			unset ($_GET['SelectedEntry']);
			unset($_GET['deleteEntry']);
 //end if Positions used in Employees set up
}

if (!isset($SelectedName)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPosition will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT
	timesheetsinfo_id,
	 first_name,
	 middle_name,
	 last_name,
	 begin_date,
	  end_date,
	  timesheet_desc,
		timesheet_status
	 FROM patimesheetsinfo
JOIN hremployees on patimesheetsinfo.employee_id = hremployees.empid
	 ";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
	<th class="ascending">' . _('Time sheet Id') . '</th>
	  <th class="ascending">' . _('Employee') . '</th>
  <th class="ascending">' . _('Begin Date') . '</th>
 <th class="ascending">' . _('End Date') . '</th>
 <th class="ascending">' . _('Description') . '</th>

		</tr>';

		$k=0; //row colour counter
		while ($myrow = DB_fetch_array($result))
		{
			if ($k==1)
			{
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else
			{
				echo '<tr class="OddTableRows">';
				$k++;
			}

echo '<td>'.$myrow['timesheetsinfo_id'].'</td>
		<td>'.$myrow['first_name'].' '.$myrow['middle_name'].' '.$myrow['last_name'].'</td>
<td>'.$myrow['begin_date'].'</td>
<td>'.$myrow['end_date'].'</td>
<td>'.$myrow['timesheet_desc'].'</td>
<td>'._(($myrow['timesheet_status'] == 1) ? 'Approved' : 'Pending').'</td>
<td>';
if($myrow['timesheet_status'] == 1)
{
echo'<a href="PaTimesheets.php?SelectedName='.$myrow['timesheetsinfo_id'].'">' . _('View') . '</a>';
}else
{
echo'<a href="PaTimesheets.php?SelectedName='.$myrow['timesheetsinfo_id'].'">' . _('Edit') . '</a>';
}
echo'</td>
		<td>';
		if($myrow['timesheet_status'] == 0)
		{
echo'<a href="PaTimesheets.php?SelectedName='.$myrow['timesheetsinfo_id'].'&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Category Name?') . '");\'>' . _('Delete') . '</a></td>
		';
		}
 echo'</tr>';
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedName))
{

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Timesheet Defined') . '</a></div>';
}
if (! isset($_GET['delete']))
{

	echo '<form method="post"
	action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '"
enctype="multipart/form-data"
	>
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing name
	if ( isset($SelectedName) AND $SelectedName!='' )
	 {

		$sql = "SELECT
		 	timesheetsinfo_id,
		employee_id,
		begin_date,
		end_date,
		timesheet_desc,
		 attachment,
		timesheet_status
		 FROM patimesheetsinfo
		        WHERE  	timesheetsinfo_id='".$SelectedName."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['timesheetsinfo_id'] = $myrow['timesheetsinfo_id'];
		$_POST['Employee']  = $myrow['employee_id'];
		$_POST['Begindate']  = $myrow['begin_date'];
		$_POST['Enddate']  = $myrow['end_date'];
	$_POST['Description']  = $myrow['timesheet_desc'];

$_POST['Attachments']  = $myrow['attachment'];
$_POST['status']  = $myrow['timesheet_status'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="timesheetsinfo_id" value="' . $_POST['timesheetsinfo_id'] . '" />



			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('Timesheet ID') . ': ' . $_POST['timesheetsinfo_id'] . '</td>
			</tr>';
	} else
		{
		// This is a new Name so the user may volunteer a Name code
		echo '<table class="selection">';
	}

	if (!isset($_POST['TypeName']))
	{
		$_POST['TypeName']='';
	}

	$Employees = array();
	$Query = "SELECT empid, employee_id,first_name,middle_name,last_name  FROM hremployees";
	$Result = DB_query($Query);
	while ($Row = DB_fetch_array($Result))
	 {
		$Employees[$Row['empid']] = $Row['employee_id'].' '.$Row['first_name'].' '.$Row['middle_name'].' '.$Row['last_name'];
	}

	$Projects = array();
	$Query1 = "SELECT  	id,project_id , project_name  FROM paprojects";
	$Result1 = DB_query($Query1);
	while ($Row1 = DB_fetch_array($Result1))
	 {
		$Projects[$Row1['id']] = $Row1['project_id'].' '.$Row1['project_name'];
	}

	$Tasks = array();
	$Query1 = "SELECT projecttask_id, projecttask_name FROM paprojecttasks";
	$Result1 = DB_query($Query1);
	while ($Row1 = DB_fetch_array($Result1))
	 {
		$Tasks[$Row1['projecttask_id']] = $Row1['projecttask_id'].' '.$Row1['projecttask_name'];
	}



	echo '
<tr colspan="2"><h4>Timesheet info</h4></tr>

	<tr>
			<td>' . _('Employee') . ':</td>
			<td>

			<select name="Employee">';
				foreach ($Employees as $EmpId => $Row)
				 {
			echo'<option '._(($_POST['Employee']== $EmpId )? 'selected ' : '').' value="'.$EmpId.'">'.$Row.'</option>';
				}
				echo'</select></td>
		</tr>


			<tr>
					<td>' . _('Begin date') . ':</td>
					<td><input type="text" name="Begindate" required="required" id="datepicker"  class="datepicker" required="required" title="' . _('Planned Begin Date is required') . '"  value="' .$_POST['Begindate'].'""/></td>
				</tr>

				<tr>
						<td>' . _('End date') . ':</td>
						<td>'.$_POST['Enddate'].'</td>
					</tr>


		<tr>
				<td>' . _(' Description') . ':</td>

<td><textarea  name="Description">'.$_POST['Description'].'</textarea></td>
			</tr>

			<tr>
											<td>' . _('Attachments (*pdf only)') . ':</td>
											<td><input type="file" name="Attachments"   value="' . $_POST['Attachments'] . '" />';
$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/Timesheet_' . $_POST['timesheetsinfo_id'] . '.pdf')));
if (file_exists ($imagefile)) {
	echo '<br><a href="' . $imagefile . '">Timesheet_' . $_POST['timesheetsinfo_id'] . '.pdf'. '</a>
<br><input type="checkbox" name="ClearFile" id="ClearImage" value="1" > '._('Clear File').'

	';
} else {
	echo '<br>'._('No File ');
}

											echo'</td>
										</tr>';

										if(in_array('25',$_SESSION['AllowedPageSecurityTokens']))
										{
if (isset($SelectedName)) {

										echo'	<tr><td><label for="Approved">' . _('Approved') .
															':</label></td>
															<td><input type="radio"';

															if (isset($_POST['status']) and $_POST['status']==1) {
																echo ' checked';}
														echo'
															 name="Status" value="1"> Approved

															<input';
															if (! isset($SelectedName)) {
															 echo ' checked';}
															if (isset($_POST['status']) and $_POST['status']==0) {
																echo ' checked';
															}
														echo'

															type="radio" name="Status" value="0">Pending
															</td></tr>
';

}else{

	echo'<input type="hidden" name="Status"   value="0" />';
}
}elseif(!in_array('25',$_SESSION['AllowedPageSecurityTokens'])){
											if (!isset($SelectedName)) {
										echo'<input type="hidden" name="Status"   value="0" />
										<textarea name="Remarks"  hidden ></textarea>
										';
										}else{
											echo'<input type="hidden" name="Status"   value="'.$_POST['status'].'" />

											';

										}


													}




echo'
		</table>
		<br />
		<div class="centre">';
		if (isset($SelectedName)){
		if($myrow['timesheet_status'] == 0)
		{
				echo'<input type="submit" name="submit" value="' . _('Accept') . '" />';
     }

		}else {
			echo'<input type="submit" name="submit" value="' . _('Accept') . '" />';
		}
		echo'</div>
	</div>
	</form>
	<h4>Time Sheet Entries</h4>
<table>';
if (isset($SelectedName)) {
	$selected_date = $_POST['Begindate'];
$day1 = date('w',strtotime($selected_date));
$week_start1 = date('Y-m-d', strtotime($selected_date.' -'.$day1.' days'));
$week_end1 = date('Y-m-d', strtotime($selected_date.' +'.(6-$day1).' days'));
$dates_of_week=array();
for($i=0;$i<7;$i++)
{
	$NewDate = date('Y-m-d', strtotime($week_start1 . " + ".$i." days"));
	$dates_of_week[] = $NewDate;
}

echo'<tr><th>Project</th><th>Task</th><th>Billable</th>';

foreach ($dates_of_week as $date_of_day)
{
echo '<th>'.date('D', strtotime($date_of_day)).'<br>'.date('d/m', strtotime($date_of_day)).'</th>';
}


echo'<th>Total</th><th></th></tr>';

$sqlentries = "SELECT
timesheetentry_id,
timesheetinfo_id ,
taskbillable,
patimesheetentries.project_id As projectid,
patimesheetentries.projecttask_id AS task_id,
sun,
 mon,
 tue,
 wed,
 thu,
 fri,
 sat,
 paystatus
 FROM patimesheetentries
JOIN paprojects on patimesheetentries.project_id = paprojects.id
JOIN paprojecttasks on patimesheetentries.projecttask_id = paprojecttasks.projecttask_id
WHERE timesheetinfo_id = '".$SelectedName."'
 ";
$resultentries = DB_query($sqlentries);
while ($myentries = DB_fetch_array($resultentries))
{
$totalduration=$myentries['sun']+$myentries['mon']+$myentries['tue']+$myentries['wed']+$myentries['thu']+$myentries['fri']+$myentries['sat'];
	echo'<tr>
	<form class="time" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<td>
				<select name="Project">';
					foreach ($Projects as $ProjectId => $Row)
					{
				echo'<option '._(($myentries['projectid']== $ProjectId )? 'selected ' : '').' value="'.$ProjectId.'">'.$Row.'</option>';
					}
					echo'</select></td>
	<td><select name="Task">';
		foreach ($Tasks as $TaskId => $Row)
		 {
	echo'<option '._(($myentries['task_id']== $TaskId )? 'selected ' : '').' value="'.$TaskId.'">'.$Row.'</option>';
		 }
		echo'</select></td>
	<td>'._(($myentries['taskbillable']==1)?'YES':'NO').'</td>
	<td><input type="number" style="width:60px" min="0" max="24" step=".01"  name="Sun"   value="' . $myentries['sun'] . '" /></td>
	<td><input type="number" style="width:60px" min="0" max="24" step=".01" name="Mon"  size="3" value="' . $myentries['mon'] . '" /></td>
	<td><input type="number" style="width:60px" min="0" max="24" step=".01"" name="Tue"  size="3" value="' .$myentries['tue'] . '" /></td>
	<td><input type="number" style="width:60px" min="0" max="24" step=".01" name="Wed"  size="3" value="' . $myentries['wed'] . '" /></td>
	<td><input type="number" style="width:60px" min="0" max="24" step=".01" name="Thu" size="3"  value="' . $myentries['thu'] . '" /></td>
	<td><input type="number" style="width:60px" min="0" max="24" step=".01" name="Fri" size="3"  value="' . $myentries['fri'] . '" /></td>
	<td><input type="number" style="width:60px" min="0" max="24" step=".01" name="Sat"  size="3" value="' . $myentries['sat'] . '" />
	</td>
	<td>'.$totalduration.'</td>';
	if($_POST['status']==1){
echo '<td>'._(($myentries['paystatus']==1 )?'Paid':'N0T PAID').'</td>';
}
	echo'<input type="hidden" name="Timesheetinfo"  size="3" value="' . $SelectedName . '" />
<input type="hidden" name="SelectedEntry"  size="3" value="' . $myentries['timesheetentry_id'] . '" />';
if($_POST['status']==0)
{
	echo'<td><input type="submit" name="Entry" value="' . _('Edit') . '" /></td>
<td><a href="PaTimesheets.php?SelectedEntry='.$myentries['timesheetentry_id'].'&amp;deleteEntry=yes &amp;info='.$SelectedName.'" onclick=\'return confirm("' . _('Are you sure you wish to delete this Time Sheet Entry?') . '");\'>' . _('Delete') . '</a></td>

	';
}
	echo' </form>
	</tr>';

}
if($_POST['status']==0)
{
echo'<tr>
<form class="time" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
<td>
			<select name="Project">';
				foreach ($Projects as $ProjectId => $Row)
				{
			echo'<option '._(($_POST['Project']== $ProjectId )? 'selected ' : '').' value="'.$ProjectId.'">'.$Row.'</option>';
				}
				echo'</select></td>
<td><select name="Task">';
	foreach ($Tasks as $TaskId => $Row)
	 {
echo'<option '._(($_POST['Task']== $TaskId )? 'selected ' : '').' value="'.$TaskId.'">'.$Row.'</option>';
	 }
	echo'</select></td>
<td></td>
<td><input  type="number" style="width:60px" min="0" max="24" step=".01"  name="Sun"  size="3"  value="' . $_POST['Sun'] . '" /></td>
<td><input  type="number" style="width:60px" min="0" max="24" step=".01"  name="Mon"  size="3" value="' . $_POST['Mon'] . '" /></td>
<td><input  type="number" style="width:60px" min="0" max="24" step=".01"  name="Tue"  size="3" value="' . $_POST['Tue'] . '" /></td>
<td><input  type="number" style="width:60px" min="0" max="24" step=".01"  name="Wed"  size="3" value="' . $_POST['Wed'] . '" /></td>
<td><input  type="number" style="width:60px" min="0" max="24" step=".01"  name="Thu" size="3"  value="' . $_POST['Thu'] . '" /></td>
<td><input  type="number" style="width:60px" min="0" max="24" step=".01"  name="Fri" size="3"  value="' . $_POST['Fri'] . '" /></td>
<td><input  type="number" style="width:60px" min="0" max="24" step=".01"  name="Sat"  size="3" value="' . $_POST['Sat'] . '" />
</td><input type="hidden" name="Timesheetinfo"  size="3" value="' . $SelectedName . '" />
<td></td>
<td><input type="submit" name="Entry" value="' . _('Save') . '" /></td>
</form>
</tr>';
}
echo'</table>';
}

echo '
	<script>
	$( function() {
		$(".datepicker").datepicker({
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				dateFormat: "yy-mm-dd"
		});

		$(".datepicker1").datepicker({
				changeMonth: true,
				changeYear: true,
				showButtonPanel: true,
				dateFormat: "yy-mm-dd"
		});
	} );
	</script>

	';

} // end if user wish to delete

include('includes/footer.php');
?>
