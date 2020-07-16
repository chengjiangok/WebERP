<?php

/* $Id: HrPayrollCategories.php 7772 2018-04-07 09:30:06Z bagenda $ */

include('includes/session.php');
$Title ='工资计算项';// _('Payroll Categories');
include('includes/header.php');
include('includes/EvalMath.php');


// BEGIN: General Ledger  array.
$GeneralLegderAccount = array();
$Query = "SELECT 	accountcode, accountname FROM chartmaster ";
$Result = DB_query($Query);
while ($Row = DB_fetch_array($Result)) {
	$GeneralLegderAccount[$Row['accountcode']] = $Row['accountname'];
}

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
	'" alt="" />' .$Title. '</p>';


if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;
	if (mb_strlen($_POST['CategoryName']) >100) {
		$InputError = 1;
		prnMsg(_('The Category  Name  must be 100 characters or less long'),'error');
		$Errors[$i] = 'CateryName';
		$i++;
	}

	if (mb_strlen($_POST['CategoryName'])==0) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Payroll Category Name  must contain at least one character'),'error');
		$Errors[$i] = 'CategoryName';
		$i++;
	}

	if (mb_strlen($_POST['CategoryCode'])>5) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('The Category Code  must be 5 characters or less long'),'error');
		$Errors[$i] = 'CategoryCode';
		$i++;
	}


	$checksql = "SELECT count(*)
		     FROM hrpayrollcategories
		     WHERE payroll_category_name = '" . $_POST['CategoryName'] . "'";
	$checkresult=DB_query($checksql);
	$checkrow=DB_fetch_row($checkresult);
	if ($checkrow[0]>0 and !isset($SelectedName)) {
		$InputError = 1;
		echo '<br />';
		prnMsg(_('You already have a Category Name').' '.$_POST['CategoryName'],'error');
		$Errors[$i] = 'CategoryName';
		$i++;
	}

	if (isset($SelectedName) AND $InputError !=1) {
		// this is an update...check for modifications and make changes to employee salary structure.
		$sql_old = DB_query("SELECT payroll_category_type,payroll_category_value,additional_condition FROM hrpayrollcategories WHERE payroll_category_id='".$SelectedName."'");
		$old_row = DB_fetch_array($sql_old);
		$old_payroll_category_type = $old_row['payroll_category_type'];
		$old_payroll_category_value = $old_row['payroll_category_value'];
		$old_payroll_additional = $old_row['additional_condition'];
		$new_payroll_category_type = $_POST['CategoryType'];
		$payroll_category_id = $SelectedName;
		if($old_payroll_category_value != $_POST['CategoryValue'])
		{
			//updates need to be made to employee salary structure.
			//1. get gross category id.
			$sql_gross = DB_query("SELECT payroll_category_id FROM hrpayrollcategories WHERE payroll_category_code='GROSS'");
			$gross_row = DB_fetch_array($sql_gross);
			$gross_category_id = $gross_row['payroll_category_id'];
			if(!is_numeric($_POST['CategoryValue']))
			{
				$new_formula = $_POST['CategoryValue'];
				//get all employees affected by change of this category id

				$sql_salary_structures = DB_query("SELECT salary_structure_id,employee_id,gross_pay,net_pay FROM hremployeesalarystructures ");
				while ($salary_row = DB_fetch_array($sql_salary_structures)) {
						$old_net_pay = $salary_row['net_pay'];
						$gross_salary = $salary_row['gross_pay'];
						$salary_structure_id = $salary_row['salary_structure_id'];
						$expression = str_replace('GROSS',$gross_salary,$new_formula);
						$m = new EvalMath;
						$new_category_value = $m->evaluate($expression);
						//work on current salary_structure_id
						if($new_category_value)
						{
							if($new_payroll_category_type == 0)
							{
								$new_net_pay = $old_net_pay - $new_category_value;
							}
							else if($new_payroll_category_type == 1)
							{
								$new_net_pay = $old_net_pay + $new_category_value;
							}

								$structure_component_sql = DB_query("UPDATE hremployeesalarystructure_components
																					SET amount = '".$new_category_value."' WHERE salary_structure_id='".$salary_structure_id."' AND payroll_category_id='".$payroll_category_id."'");

								$new_net_pay_sql = DB_query("UPDATE hremployeesalarystructures
																	SET net_pay='".$new_net_pay."' WHERE salary_structure_id='".$salary_structure_id."'");
						}

				}
			}
		}

		$sql = "UPDATE hrpayrollcategories
			SET payroll_category_name = '" . $_POST['CategoryName'] . "',
			payroll_category_code = '" . mb_strtoupper($_POST['CategoryCode']). "',
			payroll_category_value = '" . $_POST['CategoryValue'] . "',
			payroll_category_type= '" . $_POST['CategoryType']. "',
			additional_condition= '" . $_POST['AdditionalCondition']. "',
			general_ledger_account_id= '" . $_POST['GeneralLegder']. "'
			WHERE payroll_category_id = '" .$SelectedName."'";

		$msg = _('The Category Name') . ' ' . $_POST['CategoryName']. ' ' .  _('has been updated');
	} elseif ( $InputError !=1 ) {

		// First check the Name is not being duplicated

		$checkSql = "SELECT count(*)
			     FROM hrpayrollcategories
			     WHERE payroll_category_name = '" . $_POST['CategoryName'] . "'";

		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

		if ( $checkrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('The Category Name') . ' ' . $_POST['category_name'] . _(' already exist.'),'error');
		} else {

			// Add new record on submit

			$sql = "INSERT INTO hrpayrollcategories
						(payroll_category_name,
							payroll_category_code,
							payroll_category_value,
							payroll_category_type,
							additional_condition,
							general_ledger_account_id)
					VALUES ('" . $_POST['CategoryName'] . "',
					'" . mb_strtoupper($_POST['CategoryCode']) . "',
					'" . $_POST['CategoryValue']. "',
					'" . $_POST['CategoryType']. "',
					'" . $_POST['AdditionalCondition'] . "',
					'" . $_POST['GeneralLegder'] . "'
					)";


			$msg = _('Category Name') . ' ' . $_POST["CategoryName"] .  ' ' . _('has been created');
			$checkSql = "SELECT count(payroll_category_id)
			     FROM hrpayrollcategories";
			$result = DB_query($checkSql);
			$row = DB_fetch_row($result);

		}
	}

	if ( $InputError !=1) {
	//run the SQL from either of the above possibilites
		$result = DB_query($sql);


	// Fetch the default Category list.
		$DefaultCategoryName = $_SESSION['DefaultCategoryName'];

	// Does it exist
		$checkSql = "SELECT count(*)
			     FROM hrpayrollcategories
			     WHERE payroll_category_id = '" . $DefaultCategoryName . "'"
					 ;
		$checkresult = DB_query($checkSql);
		$checkrow = DB_fetch_row($checkresult);

	// If it doesnt then update config with newly created one.
		if ($checkrow[0] == 0) {
			$sql = "UPDATE config
					SET confvalue='" . $_POST['payroll_category_id'] . "'
					WHERE confname='DefaultCategoryName'";
			$result = DB_query($sql);
			$_SESSION['DefaultCategoryName'] = $_POST['payroll_category_id'];
		}
		echo '<br />';
		prnMsg($msg,'success');

		unset($SelectedName);
		unset($_POST['payroll_category_id']);
		unset($_POST['CategoryName']);
		unset($_POST['CategoryCode']);
		unset($_POST['CategoryValue']);
		unset($_POST['CategoryType']);
		unset($_POST['AdditionalCondition']);
	unset($_POST['GeneralLegder']);

	}

} elseif ( isset($_GET['delete']) ) {

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'Payroll groups '


	$sql= "SELECT COUNT(*)
	       FROM hrpayroll_groups_payroll_categories JOIN hrpayrollgroups ON hrpayroll_groups_payroll_categories.payroll_group_id = hrpayrollgroups.payrollgroup_id
	       WHERE payroll_category_id='".$SelectedName."'";

	$ErrMsg = _('The number of transactions using this Category Name could not be retrieved');
	$result = DB_query($sql,$ErrMsg);

	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this Category because payroll groups  have been created using this Category') . '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('Parolls using this Category'),'error');

	}

	 else {
			$result = DB_query("SELECT payroll_category_name FROM hrpayrollcategories WHERE payroll_category_id='".$SelectedName."'");
			if (DB_Num_Rows($result)>0){
				$NameRow = DB_fetch_array($result);
				$CategoryName = $NameRow['payroll_category_name'];

				$sql="DELETE FROM hrpayrollcategories WHERE payroll_category_id='".$SelectedName."'";
				$ErrMsg = _('The Category record could not be deleted because');
				$result = DB_query($sql,$ErrMsg);
				echo '<br />';
				prnMsg(_('Category Name') . ' ' . $CategoryName  . ' ' . _('has been deleted') ,'success');
			}
			unset ($SelectedName);
			unset($_GET['delete']);

	} //end if Positions used in Employees set up
}

if (!isset($SelectedName)){

/* It could still be the second time the page has been run and a record has been selected for modification - SelectedPayrollCategory will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters
then none of the above are true and the list of sales types will be displayed with
links to delete or edit each. These will call the same page again and allow update/input
or deletion of the records*/

	$sql = "SELECT payroll_category_id,
					payroll_category_name,
					payroll_category_code,
					payroll_category_value,
					payroll_category_type,
					additional_condition,
					general_ledger_account_id
			  FROM hrpayrollcategories";
	$result = DB_query($sql);

	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Category id') . '</th>
			<th class="ascending">' . _('Category Name') . '</th>
			<th class="ascending">' . _('Category Code') . '</th>
			<th class="ascending">' . _('Category Value') . '</th>
			<th class="ascending">' . _('Category Type') . '</th>
			<th class="ascending">' . _('Additional Condition') . '</th>
			<th class="ascending">' . _('General Legder Posting Account') . '</th>
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
				$myrow['payroll_category_id'],
				$myrow['payroll_category_name'],
				$myrow['payroll_category_code'],
				$myrow['payroll_category_value'],
				($myrow['payroll_category_type'] == 1) ? 'Earnings' : 'Deductions',
				$myrow['additional_condition'],
				$GeneralLegderAccount[$myrow['general_ledger_account_id']],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['payroll_category_id'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['payroll_category_id']);
			}
	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!
if (isset($SelectedName)) {

	echo '<div class="centre"><br /><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show All Categories Defined') . '</a></div>';
}
if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<br />';

	// The user wish to EDIT an existing name
	if ( isset($SelectedName) AND $SelectedName!='' ) {

		$sql = "SELECT payroll_category_id,
						payroll_category_name,
					payroll_category_code,
					payroll_category_value,
					payroll_category_type,
					additional_condition,
					general_ledger_account_id
								FROM hrpayrollcategories
								WHERE payroll_category_id='".$SelectedName."'";

						$result = DB_query($sql);
						$myrow = DB_fetch_array($result);

						$_POST['payroll_category_id'] = $myrow['payroll_category_id'];
						$_POST['CategoryName']  = $myrow['payroll_category_name'];
						$_POST['CategoryCode']  = $myrow['payroll_category_code'];
						$_POST['CategoryValue']  = $myrow['payroll_category_value'];
						$_POST['CategoryType']  = $myrow['payroll_category_type'];
				$_POST['AdditionalCondition']  = $myrow['additional_condition'];
				$_POST['GeneralLegder']  = $myrow['general_ledger_account_id'];
		echo '<input type="hidden" name="SelectedName" value="' . $SelectedName . '" />
			<input type="hidden" name="payroll_category_id" value="' . $_POST['payroll_category_id'] . '" />
			<table class="selection">';

		// We dont allow the user to change an existing Name code

		echo '<tr>
				<td>' . _('CATEGORY ID') . ': ' . $_POST['payroll_category_id'] . '</td>
			</tr>';
	} else 	{
		// This is a new Name so the user may volunteer a Name code
		echo '<table class="selection">';
	}

	if (!isset($_POST['CategoryName'])) {
		$_POST['CategoryName']='';
	}
	echo '<tr>
			<td>工资计算项名:</td>
			<td><input type="text" name="CategoryName"  required="required" title="' . _('The Category Name is required') . '" value="' . $_POST['CategoryName'] . '" /></td>
		</tr>
		<tr>
				<td>工资计算项编号:</td>
				<td><input type="text" name="CategoryCode"  required="required" title="' . _('The Category Code is required') . '" value="' . $_POST['CategoryCode'] . '" /></td>
			</tr>';
			$rang=array('0'=>'基本工资','1'=>'社保',  '2'=>'扣除项');
		echo '<tr>
			<td>工资类别:</td>
			<td>  
				<select name="prdrange" size="1" >';
				if ($_SESSION['startperiod']<$_SESSION['janr'] ){
					unset($rang[36]);
				}
				foreach($rang as $key=>$val){			
					if (isset($_POST['prdrange'])&& $key==$_POST['prdrange']){
						echo '<option selected="True" value ="';
					}else{
						echo '<option value ="';
					}
						echo $key.'">'.$val.'</option>';		
				}		
			echo'	</select>
				</td></tr>';
			echo'<tr>
			    <td><label for="CategoryType">工资计算项类别:</label></td>
			    <td><input type="radio"';
				if (! isset($SelectedName)) {
			   echo ' checked';
			}
			  if (isset($_POST['CategoryType']) and $_POST['CategoryType']==1) {
				echo ' checked';
			}
			echo'name="CategoryType" value="1"> 收益

			  <input';
			  if (isset($_POST['CategoryType']) and $_POST['CategoryType']==0) {
			    echo ' checked';
			  }
			echo' type="radio" name="CategoryType" value="0"> 扣款
			  </td></tr>
				<tr>
					<td>计算值:</td>
					<td><input type="text" name="CategoryValue"  required="required" title="' . _('The Category Value is required') . '" value="' . $_POST['CategoryValue'] . '" /></td>
				</tr>
				<tr>
					<td>附件条件:</td>
					<td><input type="text" name="AdditionalCondition"  title="' . _('The Additional Condition is required') . '" value="' . $_POST['AdditionalCondition'] . '" /></td>
				</tr>';
						// General Legder  input.
			echo '<tr>
					<td><label for="GeneralLegder">生成账户:</label></td>
					<td><select id="GeneralLegder" name="GeneralLegder" >';
					echo'<option value="NULL">NONE </option>';
						foreach ($GeneralLegderAccount as $AccountCode => $Row) {

							echo '<option';
							if (isset($_POST['GeneralLegder']) and $_POST['GeneralLegder']==$AccountCode) {
								echo ' selected="selected"';
							}
							echo ' value="' . $AccountCode . '">' . $Row . '</option>';
						}
						echo '</select>
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
