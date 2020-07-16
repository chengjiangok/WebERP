<?php

/* $Id: HrEmploymentGrades.php 7772 2018-04-07 09:30:06Z bagenda $ */

include('includes/session.php');

$Title ='员工级别';// _('Employee Grades');

include('includes/header.php');

if (isset($_POST['SelectedGrading'])){
	$SelectedGrading = mb_strtoupper($_POST['SelectedGrading']);
} elseif (isset($_GET['SelectedGrading'])){
	$SelectedGrading = mb_strtoupper($_GET['SelectedGrading']);
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Customer Types') .
	'" alt="" />' .$Title . '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['GradingName']) >100) {
		$InputError = 1;
		prnMsg(_('The Grade  Name description must be 100 characters or less long'),'error');
		$Errors[$i] = 'GradingName';
		$i++;
	}

	if (mb_strlen($_POST['GradingName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Grading Name  must contain at least one character'),'error');
		$Errors[$i] = 'GradingName';
		$i++;
	}

	if (mb_strlen($_POST['GradingPriority'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Grading Priority  must contain at least one character'),'error');
		$Errors[$i] = 'GradingPriority';
		$i++;
	}

	$checksql = "SELECT count(*)
		     FROM hremployeegradings
		     WHERE grading_name = '" . $_POST['GradingName'] . "'";
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedGrading)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have a Grading Name').' '.$_POST['GradingName'],'error');
		$Errors[$i] = 'GradingName';
		$i++;
	}

	if (isset($SelectedGrading) AND $InputError !=1) {

		$sql = "UPDATE hremployeegradings
					SET grading_name = '" . $_POST['GradingName'] . "',
						priority= '" . $_POST['GradingPriority'] . "',
						grading_description= '" . $_POST['GradingDescription'] . "',
						grading_status= '" . $_POST['Status'] . "'
			      WHERE employee_grading_id = '" .$SelectedGrading."'";

		$msg = _('The Grading Name') . ' ' . $_POST['GradingName'] . ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the Name is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM hremployeegradings
			     WHERE grading_name = '" . $_POST['GradingName'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Grading Name') . ' ' . $_POST['GradingName'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO hremployeegradings
						(grading_name,
						priority,grading_description,grading_status)
							VALUES ('" . $_POST['GradingName'] . "',
								'" . $_POST['GradingPriority'] . "',
								'" . $_POST['GradingDescription'] . "',
								'" . $_POST['Status'] . "'
								)";


			$msg = _('Grading Name') . ' ' . $_POST["GradingName"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(employee_grading_id)
			     FROM hremployeegradings";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);


	// Fetch the default price list.
		$DefaultGradingName = $_SESSION['DefaultGradingName'];

	// Does it exist
		$checkSql = "SELECT count(*)
			     FROM hremployeegradings
			     WHERE employee_grading_id = '" . $DefaultGradingName . "'"
					 ;
		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

	// If it doesnt then update config with newly created one.
		if ($checkrow[0] == 0) {
			$sql = "UPDATE config
					SET confvalue='" . $_POST['employee_grading_id'] . "'
					WHERE confname='DefaultGradingName'";
			$result = DB_query($sql);
			$_SESSION['DefaultGradingName'] = $_POST['employee_grading_id'];
		}
		echo '<br />';
		prnMsg($msg,'success');

		unset($SelectedGrading);
		unset($_POST['employee_grading_id']);
		unset($_POST['GradingName']);
		unset($_POST['GradingPriority']);
		unset($_POST['GradingDescription']);
		unset($_POST['Status']);
	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'employees'


	$sql= "SELECT COUNT(*)
	       FROM hremployees
	       WHERE employee_grade_id='".$SelectedGrading."'";

	$ErrMsg = _('The number of employees using this Grade Name could not be retrieved');
	$result = DB_query($sql,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this Grade because Employee   have been created using this Grade') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('Employees using this Grade'),'error');

	} else  {
			$result = DB_query("SELECT grading_name FROM hremployeegradings WHERE employee_grading_id='".$SelectedGrading."'");
			if (DB_Num_Rows($result)>0){
				$GradingRow = DB_fetch_array($result);
				$GradingName = $GradingRow['grading_name '];

				$sql="DELETE FROM hremployeegradings WHERE employee_grading_id='".$SelectedGrading."'";
				$ErrMsg = _('The Grade record could not be deleted because');
				$result = DB_query($sql,$ErrMsg);
				echo '<br />';
				prnMsg(_('Grade Name') . ' ' . $GradingName  . ' ' . _('has been deleted') ,'success');
			}
			unset ($SelectedGrading);
			unset($_GET['delete']);

		}
	} //end if Grade used in Employees  set up


if (!isset($SelectedGrading)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedGrading will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of employee grades will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT employee_grading_id, grading_name,priority,grading_description,grading_status FROM hremployeegradings";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Grading id') . '</th>
			<th class="ascending">' . _('Grading Name') . '</th>
			<th class="ascending">' . _('Priority') . '</th>
			<th class="ascending">' . _('Description') . '</th>
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
				<td><a href="%sSelectedGrading=%s">' . _('Edit') . '</a></td>
				<td><a href="%sSelectedGrading=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Grading Name?') . '");\'>' . _('Delete') . '</a></td>
				</tr>',
		$myrow['employee_grading_id'],
		$myrow['grading_name'],
		$myrow['priority'],
		$myrow['grading_description'],
		($myrow['grading_status'] == 1) ? 'Active' : 'Inactive',
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['employee_grading_id'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['employee_grading_id']);
	}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedGrading)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Grading Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing name
	if ( isset($SelectedGrading) AND $SelectedGrading!='' ) {

		$sql = "SELECT employee_grading_id,
			       grading_name,priority, grading_description,grading_status
		        FROM hremployeegradings
		        WHERE employee_grading_id='".$SelectedGrading."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['employee_grading_id'] = $myrow['employee_grading_id'];
		$_POST['GradingName']  = $myrow['grading_name'];
		$_POST['GradingPriority']  = $myrow['priority'];
		$_POST['GradingDescription']  = $myrow['grading_description'];
        $_POST['Status']  = $myrow['grading_status'];
		echo '<input type="hidden" name="SelectedGrading" value="' . $SelectedGrading . '" />
			<input type="hidden" name="employee_grading_id" value="' . $_POST['employee_grading_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('GRADING ID') . ': ' . $_POST['employee_grading_id'] . '</td>
			</tr>';
	} else 	{
		// This is a new Name so the user may volunteer a Name code
		echo '<table class="selection">';
	}

	if (!isset($_POST['GradingName'])) {
		$_POST['GradingName']='';
	}
	echo '<tr>
			<td>级别名:</td>
			<td><input type="text" name="GradingName"  required="required" title="' . _('The Grading Name is required') . '" value="' . $_POST['GradingName'] . '" /></td>
		</tr>
		<tr>
			<td>工资总额:</td>
			<td><input type="text" name="GrossPayroll"  required="required" title="' . _('The  Priority is required') . '" value="' . $_POST['GradingPriority'] . '" /></td>
		</tr>
		<tr>
			<td>优先级:</td>
			<td><input type="text" name="GradingPriority"  required="required" title="' . _('The  Priority is required') . '" value="' . $_POST['GradingPriority'] . '" /></td>
		</tr>
		<tr>
			<td>描述:</td>
			<td>
                 <textarea name="GradingDescription"  title="' . _('The  Description is required') . '">' . $_POST['GradingDescription'] . '</textarea>
			</td>
		 </tr>
		 <tr>
		    <td><label for="Status">' . _('Status') .':</label></td>
			  <td><input type="radio"';
				if (! isset($SelectedGrading)) {
			   echo ' checked';}
			  if (isset($_POST['Status']) and $_POST['Status']==1) {
			    echo ' checked';}
			echo'
			   name="Status" value="1"> Active

			  <input';
			  if (isset($_POST['Status']) and $_POST['Status']==0) {
			    echo ' checked';
			  }
			echo'type="radio" name="Status" value="0"> Inactive
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
