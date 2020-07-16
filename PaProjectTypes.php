<?php

/* $Id: PaProjectTypes.php 7772 2018-05-04 09:30:06Z bagenda $ */

include('includes/session.php');

$Title = _('Project Types');

$ViewTopic = 'Project Types';
$BookMark = 'Project Types';
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
	'" alt="" />' . _('Project Types ') . '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TypeName']) >100) {
		$InputError = 1;
		prnMsg(_('The Type  Name  must be 100 characters or less long'),'error');
		$Errors[$i] = 'TypeName';
		$i++;
	}

	if (mb_strlen($_POST['TypeName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Type Name  must contain at least one character'),'error');
		$Errors[$i] = 'TypeName';
		$i++;
	}



	$checksql = "SELECT count(*)
		     FROM paprojecttypes
		     WHERE project_type_name = '" . $_POST['TypeName'] . "'";
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have a Type Name').' '.$_POST['TypeName'],'error');
		$Errors[$i] = 'TypeName';
		$i++;
	}

	if (isset($SelectedName) AND $InputError !=1) {

		$sql = "UPDATE paprojecttypes
			SET project_type_name = '" . $_POST['TypeName'] . "',
project_type_desc= '" . $_POST['TypeDesc']. "',
project_type_status= '" . $_POST['Status']. "'

			WHERE project_type_id = '" .$SelectedName."'";

		$msg = _('The Type Name') . ' ' . $_POST['TypeName']. ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the Name is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM paprojecttypes
			     WHERE project_type_name = '" . $_POST['TypeName'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Type Name') . ' ' . $_POST['TypeName'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO paprojecttypes
						(project_type_name,
						project_type_desc,
						project_type_status)
					VALUES ('" . $_POST['TypeName'] . "',
'" . $_POST['TypeDesc'] . "',
'" . $_POST['Status'] . "'
)";


			$msg = _('Type Name') . ' ' . $_POST["TypeName"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(project_type_Id)
			     FROM paprojecttypes";
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
		unset($_POST['project_type_id']);
		unset($_POST['TypeName']);
		unset($_POST['TypeDesc']);
		unset($_POST['Status']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'EMPLOYEE Positions'


	$sql= "SELECT COUNT(*)
	       FROM paprojects
	       WHERE project_type='".$SelectedName."'";

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

	$sql = "SELECT project_type_id,
	 project_type_name,
	 project_type_desc,
	 project_type_status
	 FROM paprojecttypes";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
	<th class="ascending">' . _('Type id') . '</th>
 <th class="ascending">' . _('Type Name') . '</th>
 <th class="ascending">' . _('Type Description') . '</th>
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
		<td><a href="%sSelectedName=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Type Name?') . '");\'>' . _('Delete') . '</a></td>
		</tr>',
		$myrow['project_type_id'],
		$myrow['project_type_name'],
		$myrow['project_type_desc'],
		($myrow['project_type_status'] == 1) ? 'Active' : 'Inactive',
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['project_type_id'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['project_type_id']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedName)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Types Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing name
	if ( isset($SelectedName) AND $SelectedName!='' ) {

		$sql = "SELECT project_type_id,
		project_type_name,
		project_type_desc,
		project_type_status
		        FROM paprojecttypes
		        WHERE project_type_id='".$SelectedName."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['project_type_id'] = $myrow['project_type_id'];
		$_POST['TypeName']  = $myrow['project_type_name'];
		$_POST['TypeDesc']  = $myrow['project_type_desc'];
$_POST['status']  = $myrow['project_type_status'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="project_type_id" value="' . $_POST['project_type_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('Type ID') . ': ' . $_POST['project_type_id'] . '</td>
			</tr>';
	} else 	{
		// This is a new Name so the user may volunteer a Name code
		echo '<table class="selection">';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName']='';
	}
	echo '<tr>
			<td>' . _('Type Name') . ':</td>
			<td><input type="text" name="TypeName"  required="required" title="' . _('The Project Name is required') . '" value="' . $_POST['TypeName'] . '" /></td>
		</tr>
		<tr>
				<td>' . _('Type Desc') . ':</td>

<td><textarea  name="TypeDesc">'.$_POST['TypeDesc'].'</textarea></td>
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
