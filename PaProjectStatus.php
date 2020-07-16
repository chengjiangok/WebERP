<?php

/* $Id: PaProjectStatus.php 7772 2018-05-04 09:30:06Z bagenda $ */

include('includes/session.php');

$Title = _('Project Status');

$ViewTopic = 'Project Status';
$BookMark = 'Project Status';
include('includes/header.php');

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
	'" alt="" />' . _('Project Status ') . '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['StatusName']) >100) {
		$InputError = 1;
		prnMsg(_('The Status Name  must be 100 characters or less long'),'error');
		$Errors[$i] = 'StatusName';
		$i++;
	}

	if (mb_strlen($_POST['StatusName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Status Name  must contain at least one character'),'error');
		$Errors[$i] = 'StatusName';
		$i++;
	}



	$checksql = "SELECT count(*)
		     FROM paprojectstatus
		     WHERE project_status_name = '" . $_POST['StatusName'] . "'";
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have a Status Name').' '.$_POST['StatusName'],'error');
		$Errors[$i] = 'StatusName';
		$i++;
	}

	if (isset($SelectedName) AND $InputError !=1) {

		$sql = "UPDATE paprojectstatus
			SET project_status_name = '" . $_POST['StatusName'] . "',
project_status_desc= '" . $_POST['StatusDesc']. "',
project_status_status= '" . $_POST['Status']. "'

			WHERE project_status_id = '" .$SelectedName."'";

		$msg = _('The Status Name') . ' ' . $_POST['StatusName']. ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the Name is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM paprojectstatus
			     WHERE project_status_name = '" . $_POST['StatusName'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Status Name') . ' ' . $_POST['StatusName'] . _(' already exist.'),'error');
		} else {


			// Add new record on submit

			$sql = "INSERT INTO paprojectstatus
						(project_status_name,
						project_status_desc,
						project_status_status)
					VALUES ('" . $_POST['StatusName'] . "',
'" . $_POST['StatusDesc'] . "',
'" . $_POST['Status'] . "'
)";


			$msg = _('Status Name') . ' ' . $_POST["StatusName"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(project_status_id)
			     FROM paprojectstatus";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);

if(!isset($SelectedName)){
$prevents =$_POST['prevent'];
$projectstatus_id = DB_Last_Insert_ID($db,'paprojectstatus','project_status_id');
foreach($prevents as $prevent){

	$sql2 = "INSERT INTO paprojectstatusprevents
				(status_id,
					prevents )
			VALUES ('" . $projectstatus_id . "',
			'" . $prevent . "'
			)";

		$result2 = DB_query($sql2);
}

}
else {
$sql4="DELETE FROM paprojectstatusprevents WHERE status_id='".$SelectedGroupName."'";
$result4 = DB_query($sql4);

	$projectstatus_id = $SelectedName;
$prevents =$_POST['prevent'];

	foreach($prevents as $prevent)
	{
		$sql5 = "INSERT INTO paprojectstatusprevents
					(status_id,
						prevents )
				VALUES ('" . $projectstatus_id . "',
				'" . $prevent . "'
				)";
				$result5 = DB_query($sql5);
	}
	# code...
}




		echo '<br />';
		prnMsg($msg,'success');

		unset($SelectedName);
		unset($_POST['project_status_id']);
		unset($_POST['StatusName']);
		unset($_POST['StatusDesc']);
		unset($_POST['Status']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'EMPLOYEE Positions'


	$sql= "SELECT COUNT(*)
	       FROM paprojects
	       WHERE project_status='".$SelectedName."'";

	$ErrMsg = _('The number of Projects using this Status Name could not be retrieved');
	$result = DB_query($sql,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this Status because Projects  have been created using this Status') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('Projects using this Type'),'error');

	}

	 else {
			$result = DB_query("SELECT project_status_name FROM paprojectstatus WHERE project_status_id='".$SelectedName."'");
			if (DB_Num_Rows($result)>0){
				$NameRow = DB_fetch_array($result);
				$StatusName = $NameRow['project_status_name'];

				$sql="DELETE FROM paprojectstatus WHERE project_status_id='".$SelectedName."'";
				$ErrMsg = _('The Status record could not be deleted because');
				$result = DB_query($sql,$ErrMsg);
				echo '<br />';
				prnMsg(_('Status Name') . ' ' . $StatusName  . ' ' . _('has been deleted') ,'success');
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

	$sql = "SELECT project_status_id,
	 project_status_name,
	 project_status_desc,
	 project_status_status
	 FROM paprojectstatus";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Status id') . '</th>
			<th class="ascending">' . _('Status Name') . '</th>
			<th class="ascending">' . _('Status Description') . '</th>
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
		<td><a href="%sSelectedName=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedName=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Status ?') . '");\'>' . _('Delete') . '</a></td>
		</tr>',
		$myrow['project_status_id'],
		$myrow['project_status_name'],
		$myrow['project_status_desc'],
		($myrow['project_status_status'] == 1) ? 'Active' : 'Inactive',
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['project_status_id'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['project_status_id']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedName)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Status Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing name
	if ( isset($SelectedName) AND $SelectedName!='' ) {

		$sql = "SELECT project_status_id,
		project_status_name,
		project_status_desc,
		project_status_status
		        FROM paprojectstatus
		        WHERE project_status_id='".$SelectedName."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['project_status_id'] = $myrow['project_status_id'];
		$_POST['StatusName']  = $myrow['project_status_name'];
		$_POST['StatusDesc']  = $myrow['project_status_desc'];
$_POST['status']  = $myrow['project_status_status'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="project_status_id" value="' . $_POST['project_status_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('Status ID') . ': ' . $_POST['project_status_id'] . '</td>
			</tr>';
	} else 	{
		// This is a new Name so the user may volunteer a Name code
		echo '<table class="selection">';
	}

	if (!isset($_POST['StatusName'])) {
		$_POST['StatusName']='';
	}
	echo '<tr>
			<td>' . _('Status Name') . ':</td>
			<td><input type="text" name="StatusName"  required="required" title="' . _('The Project Status is required') . '" value="' . $_POST['StatusName'] . '" /></td>
		</tr>
		<tr>
				<td>' . _('Status Desc') . ':</td>

<td><textarea  name="StatusDesc">'.$_POST['StatusDesc'].'</textarea></td>
			</tr>

				<tr>
						<td>' . _('Prevents') . ':</td>

			<td>
			<p>Don\'t allow the selected when a project has this status.</p>';

			$Prevents = array();
			$Query = "SELECT status_id, prevents
						 FROM paprojectstatusprevents
						 WHERE status_id='".$SelectedName."'";
			$Result = DB_query($Query);
			while ($Row = DB_fetch_array($Result)) {

		$Prevents[$Row['prevents']] = $Row['prevents'];
			}

			$sql3 = "SELECT prevent,names
					        FROM paprojectprevents";
					$result3 = DB_query($sql3);
while ($myrow3 = DB_fetch_array($result3)) {
 echo '<input type="checkbox"';
 if ((in_array($myrow3['prevent'], $Prevents))) {
	 echo 'checked';
 }

echo' name="prevent[]" value="'.$myrow3['prevent'].'">'.$myrow3['names'].'<br>';
 }

			echo'
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

} // end if user wish to delete

include('includes/footer.php');
?>
