<?php

/* $Id: PaProjectBilling Terms.php 7772 2018-05-04 09:30:06Z bagenda $ */

include('includes/session.php');

$Title = _('Project Billing Terms');

$ViewTopic = 'Project Billing Terms';
$BookMark = 'Project Accounting';
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

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Billing Terms') .
	'" alt="" />' . _('Project Billing Terms ') . '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['TermName']) >100) {
		$InputError = 1;
		prnMsg(_('The Term  Name  must be 100 characters or less long'),'error');
		$Errors[$i] = 'TermName';
		$i++;
	}

	if (mb_strlen($_POST['TermName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Term Name  must contain at least one character'),'error');
		$Errors[$i] = 'TermName';
		$i++;
	}
	if (mb_strlen($_POST['TermDesc'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Term Desc  must contain at least one character'),'error');
		$Errors[$i] = 'TermDesc';
		$i++;
	}

	$checksql = "SELECT count(*)
		     FROM paprojectbillingterms
		     WHERE billing_term_name  = '" . $_POST['TermName'] . "'";
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have a Term Name').' '.$_POST['TermName'],'error');
		$Errors[$i] = 'TermName';
		$i++;
	}

	if (isset($SelectedName) AND $InputError !=1) {

		$sql = "UPDATE paprojectbillingterms
			SET billing_term_name = '" . $_POST['TermName'] . "',
billing_term_desc= '" . $_POST['TermDesc']. "',
billing_term_status= '" . $_POST['Status']. "',
billing_term_duedate= '" . $_POST['DueDay']. "',
billing_term_dueperiod= '" . $_POST['DuePeriodstart']. "',
billing_term_discountdate= '" . $_POST['DiscountDay']. "',
billing_term_discountperiod= '" . $_POST['DiscountPeriodstart']. "',
billing_term_discountamount= '" . $_POST['Amount']. "',
biling_term_discountcal= '" . $_POST['DiscountCal']. "',
billing_term_discountgracedays= '" . $_POST['Gracedays']. "',
billing_term_discountcalculate= '" . $_POST['Calculate']. "',
billing_term_penalitycycle= '" . $_POST['Cycle']. "',
billing_term_penalityamount= '" . $_POST['PenalityAmount']. "',
billing_term_penalitycal= '" . $_POST['PenalityCal']. "',
billing_term_penalitygracedays= '" . $_POST['PenalityGracedays']. "'


			WHERE billing_term_id = '" .$SelectedName."'";

		$msg = _('The Term Name') . ' ' . $_POST['TermName']. ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the Name is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM paprojectbillingterms
			     WHERE billing_term_name = '" . $_POST['TermName'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Term Name') . ' ' . $_POST['TermName'] . _(' already exist.'),'error');
		} else {


if ($_POST['DueDay'] ==0){
	$dueday=0;
}else{
	$dueday=$_POST['DueDay'];
}

if ($_POST['DiscountDay'] ==0){
	$discountday=0;
}else{
	$discountday=$_POST['DiscountDay'];
}
if ($_POST['Amount'] ==0){
	$amount=0;
}else{
	$amount=$_POST['Amount'];
}

if ($_POST['Gracedays'] ==0){
	$gracedays=0;
}else{
	$gracedays=$_POST['Gracedays'];
}

if ($_POST['PenalityAmount'] ==0){
	$penalityamount=0;
}else{
	$penalityamount=$_POST['PenalityAmount'];
}

if ($_POST['PenalityGracedays'] ==0){
	$penalitygracedays=0;
}else{
	$penalitygracedays=$_POST['PenalityGracedays'];
}





			// Add new record on submit

			$sql = "INSERT INTO paprojectbillingterms
						(billing_term_name ,
						billing_term_desc,
						billing_term_status,
            billing_term_duedate,
            billing_term_dueperiod,
            billing_term_discountdate,
						billing_term_discountperiod,
						billing_term_discountamount,
						biling_term_discountcal,
						billing_term_discountgracedays,
						billing_term_discountcalculate,
						billing_term_penalitycycle,
						billing_term_penalityamount ,
						billing_term_penalitycal,
						billing_term_penalitygracedays

					)
					VALUES ('" . $_POST['TermName'] . "',
'" . $_POST['TermDesc'] . "',
'" . $_POST['Status'] . "',
'" . $dueday . "',
'" . $_POST['DuePeriodstart'] . "',
'" . $discountday . "',
'" . $_POST['DiscountPeriodstart'] . "',
'" . $amount . "',
'" . $_POST['DiscountCal'] . "',
'" . $gracedays. "',
'" . $_POST['Calculate'] . "',
'" . $_POST['Cycle'] . "',
'" . $penalityamount . "',
'" . $_POST['PenalityCal'] . "',
'" . $penalitygracedays . "'

)";


			$msg = _('Term Name') . ' ' . $_POST["TermName"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(billing_term_id)
			     FROM paprojectbillingterms";
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
		unset($_POST['billing_term_id']);
		unset($_POST['TermName']);
		unset($_POST['TermDesc']);
		unset($_POST['Status']);
		unset($_POST['DueDay']);
		unset($_POST['DuePeriodstart']);
		unset($_POST['DiscountDay']);
		unset($_POST['DuePeriodstart']);
		unset($_POST['Amount']);
		unset($_POST['DiscountCal']);
		unset($_POST['Gracedays']);
		unset($_POST['Calculate']);
		unset($_POST['Cycle']);
		unset($_POST['PenalityAmount']);
		unset($_POST['PenalityCal']);
		unset($_POST['PenalityGracedays']);

	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'EMPLOYEE Positions'


	$sql= "SELECT COUNT(*)
	       FROM paprojectbillingterms
	       WHERE billing_term_id='".$SelectedName."'";

	$ErrMsg = _('The number of transactions using this Terme Name could not be retrieved');
	$result = DB_query($sql,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this Term because Projects  have been created using this Term') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('Projects using this Term'),'error');

	}

	 else {
			$result = DB_query("SELECT billing_term_name FROM paprojectbillingterms WHERE billing_term_id='".$SelectedName."'");
			if (DB_Num_Rows($result)>0){
				$NameRow = DB_fetch_array($result);
				$TermName = $TermRow['billing_term_name'];

				$sql="DELETE FROM paprojectbillingterms WHERE billing_term_id='".$SelectedName."'";
				$ErrMsg = _('The Term record could not be deleted because');
				$result = DB_query($sql,$ErrMsg);
				echo '<br />';
				prnMsg(_('Term Name') . ' ' . $TermName  . ' ' . _('has been deleted') ,'success');
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

	$sql = "SELECT billing_term_id,
	 billing_term_name,
	 billing_term_desc,
	 billing_term_status
	 FROM paprojectbillingterms";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
	<th class="ascending">' . _('Term id') . '</th>
 <th class="ascending">' . _('Term Name') . '</th>
 <th class="ascending">' . _('Term Description') . '</th>
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
		<td><a href="%sSelectedName=%s&amp;delete=yes" onclick=\'return confirm("' . _('Are you sure you wish to delete this Category Name?') . '");\'>' . _('Delete') . '</a></td>
		</tr>',
		$myrow['billing_term_id'],
		$myrow['billing_term_name'],
		$myrow['billing_term_desc'],
		($myrow['billing_term_status'] == 1) ? 'Active' : 'Inactive',
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['billing_term_id'],
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow['billing_term_id']);
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

		$sql = "SELECT billing_term_id,
		billing_term_name ,
		billing_term_desc,
		billing_term_status,
		billing_term_duedate,
		billing_term_dueperiod,
		billing_term_discountdate,
		billing_term_discountperiod,
		billing_term_discountamount,
		biling_term_discountcal,
		billing_term_discountgracedays,
		billing_term_discountcalculate,
		billing_term_penalitycycle,
		billing_term_penalityamount ,
		billing_term_penalitycal,
		billing_term_penalitygracedays
		 FROM paprojectbillingterms
		        WHERE billing_term_id='".$SelectedName."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['billing_term_id'] = $myrow['billing_term_id'];
		$_POST['TermName']  = $myrow['billing_term_name'];
		$_POST['TermDesc']  = $myrow['billing_term_desc'];
$_POST['status']  = $myrow['billing_term_status'];
$_POST['DueDay']  = $myrow['billing_term_duedate'];
$_POST['DuePeriodstart']  = $myrow['billing_term_dueperiod'];

$_POST['DiscountDay'] = $myrow['billing_term_discountdate'];
$_POST['DiscountPeriodstart']  = $myrow['billing_term_discountperiod'];
$_POST['Amount']  = $myrow['billing_term_discountamount'];
$_POST['Gracedays']  = $myrow['billing_term_discountgracedays'];
$_POST['DiscountCal']  = $myrow['biling_term_discountcal'];
$_POST['Calculate']  = $myrow['billing_term_discountcalculate'];
$_POST['Cycle']  = $myrow['billing_term_penalitycycle'];
$_POST['PenalityAmount']  = $myrow['billing_term_penalityamount'];
$_POST['PenalityCal']  = $myrow['billing_term_penalitycal'];
$_POST['PenalityGracedays']  = $myrow['billing_term_penalitygracedays'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="billing_term_id" value="' . $_POST['billing_term_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('Term ID') . ': ' . $_POST['billing_term_id'] . '</td>
			</tr>';
	} else 	{
		// This is a new Name so the user may volunteer a Name code
		echo '<table class="selection">';
	}

	if (!isset($_POST['TypeName'])) {
		$_POST['TypeName']='';
	}
	echo '<tr>
			<td>' . _('Billing Term Name') . ':</td>
			<td><input type="text" name="TermName"  required="required" title="' . _('The Term Name is required') . '" value="' . $_POST['TermName'] . '" /></td>
		</tr>
		<tr>
				<td>' . _('Term Desc') . ':</td>

<td><textarea  name="TermDesc">'.$_POST['TermDesc'].'</textarea></td>
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
				<tr>
<td colspan="2"><b>' . _('Due') . '</b><hr></td>
				</tr>';
				$periodstart = array();
				$Query = "SELECT period_id, period_name FROM paprojectperiodtostart";
				$Result = DB_query($Query);
				while ($Row = DB_fetch_array($Result)) {
					$periodstart[$Row['period_id']] = $Row['period_name'];
				}

				$billingcycle = array();
				$Query1 = "SELECT cycle_id, cycle_name FROM paprojectbillingcycle";
				$Result1 = DB_query($Query1);
				while ($Row1 = DB_fetch_array($Result1)) {
					$billingcycle[$Row1['cycle_id']] = $Row1['cycle_name'];
				}

				echo'<tr>
						<td>' . _('Day') . ':</td>
<td><input type="text" name="DueDay"   value="' . $_POST['DueDay'] . '" /></td>
</tr>
<tr>
<td>' . _('Period from Start') . ':</td>
<td><select name="DuePeriodstart">';
  foreach ($periodstart as $PeriodId => $Row) {
echo'<option '._(($_POST['DuePeriodstart']== $PeriodId )? 'selected ' : '').' value="'.$PeriodId.'">'.$Row.'</option>';
	}
	echo'</select></td></tr>
<tr><td colspan="2"><b>' . _('Discount') . '</b><hr></td>
						</tr>

					<tr>
							<td>' . _('Day') . ':</td>
	<td><input type="text" name="DiscountDay"    value="' . $_POST['DiscountDay'] . '" /></td>

						</tr>

						<tr>
						<td>' . _('Period from Start') . ':</td>
						<td><select name="DiscountPeriodstart">';
						  foreach ($periodstart as $PeriodId => $Row) {
						echo'<option '._(($_POST['DiscountPeriodstart']== $PeriodId )? 'selected ' : '').'value="'.$PeriodId.'">'.$Row.'</option>';
							}
							echo'</select></td></tr>
						<tr>

						<tr>
								<td>' . _('Amount') . ':</td>
						<td><input type="text" name="Amount"   value="' . $_POST['Amount'] . '" /></td>

							</tr>

							<tr><td><label for="Status">' . _('Discount Cal') .
								':</label></td>
								<td><input type="radio"';
								if (! isset($SelectedName)) {
								 echo ' checked';}
								if (isset($_POST['DiscountCal']) and $_POST['DiscountCal']=='amount') {
									echo ' checked';}
							echo'
								 name="DiscountCal" value="amount"> Amount
								 <input';
								if (isset($_POST['DiscountCal']) and $_POST['DiscountCal']=='percentage') {
									echo ' checked';
								}
							echo'

								type="radio" name="DiscountCal" value="percentage"> Percentage
								</td></tr>
								<tr>
<td>' . _('Grace days') . ':</td>
								<td><input type="text" name="Gracedays"   value="' . $_POST['Gracedays'] . '" /></td>

									</tr>

									<tr><td><label for="Status">' . _('Calculate on the') .
										':</label></td>
										<td><input type="radio"';
										if (! isset($SelectedName)) {
										 echo ' checked';}
										if (isset($_POST['Calculate']) and $_POST['Calculate']=="lineitem") {
											echo ' checked';}
									echo'
										 name="Calculate" value="lineitem">Line-items total, excluding added charges

										<input';
										if (isset($_POST['Calculate']) and $_POST['Calculate']=="invoicetotal") {
											echo ' checked';
										}
									echo'

										type="radio" name="Calculate" value="invoicetotal"> Invoice total, including all charges
										</td></tr>
										<tr>


									<tr><td colspan="2"><b>' . _('Penality') . '</b><hr></td>
															</tr>
		<tr>
															<td>' . _('Cycle') . ':</td>
															<td><select name="Cycle">';
																foreach ($billingcycle as $cycle => $Row) {
															echo'<option '._(($_POST['Cycle']== $cycle )? 'selected ' : '').' value="'.$cycle.'">'.$Row.'</option>';
																}
																echo'</select></td></tr>
															<tr>
															<tr>
															<td>' . _('Amount') . ':</td>
															<td><input type="text" name="PenalityAmount"   value="' . $_POST['PenalityAmount'] . '" /></td>

																</tr>

																<tr><td><label for="Status">' . _('Penality Cal') .
																	':</label></td>
																	<td><input type="radio"';
																	if (! isset($SelectedName)) {
																	 echo ' checked';}
																	if (isset($_POST['PenalityCal']) and $_POST['PenalityCal']=="Amount") {
																		echo ' checked';}
																echo'
																	 name="PenalityCal" value="Amount"> Amount

																	<input';
																	if (isset($_POST['PenalityCal']) and $_POST['PenalityCal']=="Percentage") {
																		echo ' checked';
																	}
																echo'

																	type="radio" name="PenalityCal" value="Percentage"> Percentage
																	</td></tr>
																	<tr>
																	<td>' . _('Grace days') . ':</td>
																	<td><input type="text" name="PenalityGracedays"   value="' . $_POST['PenalityGracedays'] . '" /></td>

																		</tr>


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
