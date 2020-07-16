<?php

/* $Id: PaProjectResourcesLabour.php 7772 2018-05-04 09:30:06Z bagenda $ */

include('includes/session.php');
//include('includes/SQL_CommonFunctions.inc');
$Title = _('Project Resource Labour');

$ViewTopic = 'Project Resource Labour';
$BookMark = 'Project Types';
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
	'" alt="" />' . _('Project Resources Labour ') . '</p>';


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


	$checksql = "SELECT count(*)
		     FROM paprojectresourcelabour
		     WHERE project_id = '" . $_POST['Project'] . "'AND employee_id = '" . $_POST['Employee'] . "'
				 AND services = '" . $_POST['Service'] . "' ";
				;
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('This Employee').' '.$_POST['Employee'].' is already offering this servce on Project'.$_POST['Project'].'','error');
		$Errors[$i] = 'TypeName';
		$i++;
	}

$startdate = DateTime::createFromFormat($_SESSION['DefaultDateFormat'],$_POST['Startdate']);


	if (isset($SelectedName) AND $InputError !=1) {

		$sql = "UPDATE paprojectresourcelabour
			SET project_id = '" . $_POST['Project'] . "',
employee_id= '" . $_POST['Employee']. "',
services= '" . $_POST['Service']. "',
resource_description= '" . $_POST['Description']. "',
resource_startdate= '" . $startdate->format('Y-m-d'). "',

labourrate= '" . $labourrate. "',
resource_expense= '" . $expense. "',
resource_price= '" . $pricing. "',
resource_status= '" . $_POST['Status']. "'

			WHERE project_resource_id = '" .$SelectedName."'";

		$msg = _('The Project Resource') . ' ' . $_POST['project_resource_id']. ' ' .  _('has been updated');
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

			$sql = "INSERT INTO paprojectresourcelabour
						(project_id,
						employee_id,
						services,
						resource_description,
						resource_startdate,
						labourrate,
						resource_expense,
						resource_price ,
						resource_status )
					VALUES ('" . $_POST['Project'] . "',
'" . $_POST['Employee'] . "',
'" . $_POST['Service'] . "',
'" . $_POST['Description'] . "',
'" .$startdate->format('Y-m-d'). "',
'" . $labourrate . "',
'" . $expense. "',
'" . $pricing . "',
'" . $_POST['Status'] . "'
)";


			$msg = _('Project Resource assigned to Project ') . ' ' . $_POST["Project"];
			$checkSql = "SELECT count(project_resource_id)
			     FROM paprojectresourcelabour";
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
		unset($_POST['Project']);
		unset($_POST['Employee']);
		unset($_POST['Service']);
		unset($_POST['Description']);
		unset($_POST['Startdate']);
		unset($_POST['Labourrate']);
		unset($_POST['Expense']);
		unset($_POST['Pricing']);
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

	$sql = "SELECT project_resource_id,
	 paprojects.project_id AS projectid,
	 project_name,
	 first_name,
	 middle_name,
	 last_name,
	 services,
	 resource_startdate,
	 labourrate,
	 resource_status,
	  description
	 FROM paprojectresourcelabour
JOIN hremployees on paprojectresourcelabour.employee_id = hremployees.empid
JOIN paprojects on paprojectresourcelabour.project_id = paprojects.id
JOIN stockmaster on paprojectresourcelabour.services = stockmaster.stockid
	 ";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
	<th class="ascending">' . _('Project Resource Id') . '</th>
 <th class="ascending">' . _('Project Id') . '</th>
  <th class="ascending">' . _('Project ') . '</th>
	  <th class="ascending">' . _('Employee') . '</th>
  <th class="ascending">' . _('Service Offered') . '</th>
 <th class="ascending">' . _('Start Date') . '</th>
 <th class="ascending">' . _('Labour Rate') . '</th>
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
		<td><a href="%sSelectedName=%s">' . _('Edit') . '</a></td>
		<td><a href="%sSelectedName=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Category Name?') . '");\'>' . _('Delete') . '</a></td>
		</tr>',
		$myrow['project_resource_id'],
		$myrow['projectid'],
		$myrow['project_name'],
		$myrow['first_name'].' '.$myrow['middle_name'].' '.$myrow['last_name'],
		$myrow['description'],
		$myrow['resource_startdate'],
		$myrow['labourrate'],
		($myrow['resource_status'] == 1) ? 'Active' : 'Inactive',
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['project_resource_id'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['project_resource_id']);
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

		$sql = "SELECT project_resource_id,
		 project_id,
		 employee_id,
		 services,
		 resource_description,
		 resource_startdate,
		 labourrate,
		 resource_expense,
		 resource_price ,
		 resource_status
		 FROM paprojectresourcelabour
		        WHERE project_resource_id='".$SelectedName."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['project_resource_id'] = $myrow['project_resource_id'];
		$_POST['Project']  = $myrow['project_id'];
			$_POST['Employee']  = $myrow['employee_id'];
		$_POST['Service']  = $myrow['services'];
		$_POST['Description']  = $myrow['resource_description'];
		$_POST['Startdate']  = $myrow['resource_startdate'];
	$_POST['Labourrate']  = $myrow['labourrate'];

$_POST['Expense']  = $myrow['resource_expense'];
$_POST['Pricing']  = $myrow['resource_price'];
$_POST['status']  = $myrow['resource_status'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="project_resource_id" value="' . $_POST['project_resource_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('Resource ID') . ': ' . $_POST['project_resource_id'] . '</td>
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

	$Services = array();
	$Query1 = "SELECT stockid, description FROM stockmaster
	Where mbflag ='D'";
	$Result1 = DB_query($Query1);
	while ($Row1 = DB_fetch_array($Result1)) {
		$Services[$Row1['stockid']] = $Row1['description'];
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
				<td>' . _('Employee') . ':</td>
				<td>

				<select name="Employee">';
				  foreach ($Employees as $EmpId => $Row) {
				echo'<option '._(($_POST['Employee']== $EmpId )? 'selected ' : '').' value="'.$EmpId.'">'.$Row.'</option>';
					}
					echo'</select></td>
			</tr>
			<tr>
					<td>' . _('Service Offered') . ':</td>
					<td>

									<select name="Service">';
									  foreach ($Services as $stockId => $Row) {
									echo'<option '._(($_POST['Service']== $stockId )? 'selected ' : '').' value="'.$stockId.'">'.$Row.'</option>';
										}
										echo'</select>

					</td>
				</tr>

		<tr>
				<td>' . _(' Description') . ':</td>

<td><textarea  name="Description">'.$_POST['Description'].'</textarea></td>
			</tr>';

			if (isset($SelectedName)) {
				$StartDate= $_POST['Startdate'];
			}else{
			$StartDate=date('Y-m-d');
			}

			echo'<tr>
					<td>' . _('Start date') . ':</td>
					<td><input type="text" name="Startdate" required="required" class="datepicker"  class="datepicker" required="required" title="' . _('The Project Name is required') . '"  value="' .ConvertSQLDate($StartDate).'""/></td>
				</tr>

				<tr>
						<td>' . _('Labour pricing option
Labour rate') . ':</td>
						<td><input type="text" name="Labourrate"   value="' . $_POST['Labourrate'] . '" /></td>
					</tr>
					<tr>
							<td>' . _('Expense pricing option
Expense %') . ':</td>
							<td><input type="text" name="Expense"   value="' . $_POST['Expense'] . '" /></td>
						</tr>
						<tr>
								<td>' . _('AP/PO pricing option
AP/PO %') . ':</td>
								<td><input type="text" name="Pricing"   value="' . $_POST['Pricing'] . '" /></td>
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
