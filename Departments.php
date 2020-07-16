
<?php
/* $Id: Departments.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-04 12:50:19 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-04 12:56:09
 */
include('includes/session.php');

$Title = _('Departments');

include('includes/header.php');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('Departments') . '" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['SelectedDepartmentID']) )
	$SelectedDepartmentID = $_GET['SelectedDepartmentID'];
elseif (isset($_POST['SelectedDepartmentID']))
	$SelectedDepartmentID = $_POST['SelectedDepartmentID'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['DepartmentName'])) {
		$InputError = 1;
		prnMsg( _('The description of the department must not contain the character') . " '&amp;' " . _('or the character') ." '",'error');
	}
	if (trim($_POST['DepartmentName']) == '') {
		$InputError = 1;
		prnMsg( _('The Name of the Department should not be empty'), 'error');
	}

	if (isset($_POST['SelectedDepartmentID'])
		AND $_POST['SelectedDepartmentID']!=''
		AND $InputError !=1) {


		/*SelectedDepartmentID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM departments
				WHERE departmentid <> '" . $SelectedDepartmentID ."'
				AND description " . LIKE . " '" . $_POST['DepartmentName'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('This department name already exists.'),'error');
		} else {
			// Get the old name and check that the record still exist neet to be very careful here

			$sql = "SELECT description
					FROM departments
					WHERE departmentid = '" . $SelectedDepartmentID . "'";
			$result = DB_query($sql);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_array($result);
				$OldDepartmentName = $myrow['description'];
				$sql = array();
				$sql[] = "UPDATE departments
							SET description='" . $_POST['DepartmentName'] . "',
								authoriser='" . $_POST['Authoriser'] . "',
								account='". $_POST['DepartAccount'] ."',
								labouract='". $_POST['DepartLabourAct'] ."',
								salaryact='". $_POST['DepartSalaryAct'] ."'
							WHERE description " . LIKE . " '" . $OldDepartmentName . "'";
			} else {
				$InputError = 1;
				prnMsg( _('The department does not exist.'),'error');
			}
		}
		$msg = _('The department has been modified');
	} elseif ($InputError !=1) {
		/*SelectedDepartmentID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM departments
				WHERE description " . LIKE . " '" . $_POST['DepartmentName'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( _('There is already a department with the specified name.'),'error');
		} else {
			$sql = "INSERT INTO departments (description,
											 authoriser ,
											 account,
											 labouract,
											 salaryact)
					VALUES ('" . $_POST['DepartmentName'] . "',
							'" . $_POST['Authoriser'] . "',
						    '". $_POST['DepartAccount'] ."',
							'". $_POST['DepartLabourAct'] ."',
							'". $_POST['DepartSalaryAct'] ."')";
		}
		$msg = _('The new department has been created');
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin();
			$ErrMsg = _('The department could not be inserted');
			$DbgMsg = _('The sql that failed was') . ':';
			foreach ($sql as $SQLStatement ) {
				$result = DB_query($SQLStatement, $ErrMsg,$DbgMsg,true);
				if(!$result) {
					$InputError = 1;
					break;
				}
			}
			if ($InputError!=1){
				$result = DB_Txn_Commit();
			} else {
				$result = DB_Txn_Rollback();
			}
		} else {
			$result = DB_query($sql);
		}
		prnMsg($msg,'success');
        echo '<br />';
	}
	unset ($SelectedDepartmentID);
	unset ($_POST['SelectedDepartmentID']);
	unset ($_POST['DepartmentName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
	$sql = "SELECT description
			FROM departments
			WHERE departmentid = '" . $SelectedDepartmentID . "'";
	$result = DB_query($sql);
	if ( DB_num_rows($result) == 0 ) {
		prnMsg( _('You cannot delete this Department'),'warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldDepartmentName = $myrow[0];
		$sql= "SELECT COUNT(*)
				FROM stockrequest INNER JOIN departments
				ON stockrequest.departmentid=departments.departmentid
				WHERE description " . LIKE . " '" . $OldDepartmentName . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( _('You cannot delete this Department'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('There are items related to this department');
		} else {
			$sql="DELETE FROM departments WHERE description " . LIKE . "'" . $OldDepartmentName . "'";
			$result = DB_query($sql);
			prnMsg( $OldDepartmentName . ' ' . _('The department has been removed') . '!','success');
		}
	} //end if account group used in GL accounts
	unset ($SelectedDepartmentID);
	unset ($_GET['SelectedDepartmentID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedDepartmentID']);
	unset ($_POST['DepartmentID']);
	unset ($_POST['DepartmentName']);
}
$SQL = "SELECT accountcode,
				accountname
				FROM chartmaster
				WHERE LEFT(accountcode,4) IN ('5001','5101','6601','6602','6603')
				AND LENGTH(accountcode)>4
				ORDER BY accountcode";

$Result = DB_query($SQL);

 if (!isset($SelectedDepartmentID)) {

	$sql = "SELECT departmentid,
					description,
					account,
					labouract,
					salaryact,
					authoriser
			FROM departments
			ORDER BY description";

	$ErrMsg = _('There are no departments created');
	$result = DB_query($sql,$ErrMsg);

	echo '<table class="selection">
			<tr>
			    <th>编码</th>
				<th>' . _('Department Name') . '</th>
				<th>费用科目</th>
				<th>人工费科目</th>
				<th>工资科目</th>
				<th>' . _('Authoriser') . '</th>				
				<th colspan="2"></th>				
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

		echo '<td>' . $myrow['departmentid'] . '</td>
			<td>' . $myrow['description'] . '</td>
			<td><select name="DepartAccount">';

			DB_data_seek($Result,0);
		
			while ($row = DB_fetch_array($Result)) {
				if ( $row['accountcode']==$myrow['account']) {
					echo '<option selected="selected" value="';
				} else {
					echo '<option value="';
				}
				echo $row['accountcode'] . '">' .$row['accountcode'].':'. htmlspecialchars($row['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

			} //end while loop
			echo '</select></td>';
			echo'<td><select name="DepartLabourAct">';

			DB_data_seek($Result,0);
		
			while ($row = DB_fetch_array($Result)) {
				if ( $row['accountcode']==$myrow['labouract']) {
					echo '<option selected="selected" value="';
				} else {
					echo '<option value="';
				}
				echo $row['accountcode'] . '">' .$row['accountcode'].':'. htmlspecialchars($row['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

			} //end while loop
			echo '</select></td>';
			echo'<td><select name="DepartSalaryAct">';

			DB_data_seek($Result,0);
		
			while ($row = DB_fetch_array($Result)) {
				if ( $row['accountcode']==$myrow['salaryact']) {
					echo '<option selected="selected" value="';
				} else {
					echo '<option value="';
				}
				echo $row['accountcode'] . '">' .$row['accountcode'].':'. htmlspecialchars($row['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

			} //end while loop
			echo '</select></td>';
			echo'<td>' . $myrow['authoriser'] . '</td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedDepartmentID=' . $myrow['departmentid'] . '">' . _('Edit') . '</a></td>
				<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?SelectedDepartmentID=' . $myrow['departmentid'] . '&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this department?') . '\');">'  . _('Delete')  . '</a></td>
			</tr>';

	} //END WHILE LIST LOOP
	echo '</table>';
} //end of ifs and buts!


if (isset($SelectedDepartmentID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">' . _('View all Departments') . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedDepartmentID)) {
		//editing an existing section

		$sql = "SELECT departmentid,
						description,
						authoriser
				FROM departments
				WHERE departmentid='" . $SelectedDepartmentID . "'";

		$result = DB_query($sql);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('The selected departemnt could not be found.'),'warn');
			unset($SelectedDepartmentID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['DepartmentID'] = $myrow['departmentid'];
			$_POST['DepartmentName']  = $myrow['description'];
			$AuthoriserID			= $myrow['authoriser'];

			echo '<input type="hidden" name="SelectedDepartmentID" value="' . $_POST['DepartmentID'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['DepartmentName']='';
		echo '<table class="selection">';
	}
	echo '<tr>
			<td>' . _('Department Name') . ':' . '</td>
			<td><input type="text" name="DepartmentName" size="50" required="required" title="' ._('The department name is required') . '" maxlength="100" value="' . $_POST['DepartmentName'] . '" /></td>
		</tr>';
	echo'<tr>
			<td>费用科目:</td>
	        <td><select name="DepartAccount">';
			DB_data_seek($Result,0);		
			while ($row = DB_fetch_array($Result)) {
				if (isset($_POST['DepartAccount']) and $row['accountcode']==$_POST['DepartAccount']) {
					echo '<option selected="selected" value="';
				} else {
					echo '<option value="';
				}
				echo $row['accountcode'] . '">' .$row['accountcode'].':'.htmlspecialchars($row['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

			} //end while loop
	echo '</select></td></tr>';
	echo'<tr>
			<td>人工费科目:</td>
	        <td><select name="DepartLabourAct">';
			DB_data_seek($Result,0);		
			while ($row = DB_fetch_array($Result)) {
				if (isset($_POST['DepartAccount']) and $row['accountcode']==$_POST['DepartLabourAct']) {
					echo '<option selected="selected" value="';
				} else {
					echo '<option value="';
				}
				echo $row['accountcode'] . '">' .$row['accountcode'].':'.htmlspecialchars($row['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

			} //end while loop
	echo '</select></td></tr>';
	echo'<tr>
			<td>工资科目:</td>
	        <td><select name="DepartSalaryAct">';
			DB_data_seek($Result,0);		
			while ($row = DB_fetch_array($Result)) {
				if (isset($_POST['DepartAccount']) and $row['accountcode']==$_POST['DepartSalaryAct']) {
					echo '<option selected="selected" value="';
				} else {
					echo '<option value="';
				}
				echo $row['accountcode'] . '">' .$row['accountcode'].':'.htmlspecialchars($row['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';

			} //end while loop
	echo '</select></td></tr>';
	echo'<tr>
			<td>' . _('Authoriser') . '</td>
		    <td><select name="Authoriser">';
	$usersql="SELECT userid FROM www_users WHERE length(userid)>=4";
	$userresult=DB_query($usersql);
	while ($myrow=DB_fetch_array($userresult)) {
		if ($myrow['userid']==$AuthoriserID) {
			echo '<option selected="True" value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
		} else {
			echo '<option value="'.$myrow['userid'].'">' . $myrow['userid'] . '</option>';
		}
	}
	echo '</select></td>
		</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>
        </div>
		</form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>