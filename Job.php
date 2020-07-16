<?php
/* $Id: UnitsOfMeasure.php 6945 2014-10-27 07:20:48Z daintree $*/

include('includes/session.php');

$Title ='工种';

include('includes/header.php');
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' .
		_('Search') . '" alt="" />' . ' ' . $Title . '</p>';

if ( isset($_GET['SelectedMeasureID']) )
	$SelectedMeasureID = $_GET['SelectedMeasureID'];
elseif (isset($_POST['SelectedMeasureID']))
	$SelectedMeasureID = $_POST['SelectedMeasureID'];

if (isset($_POST['Submit'])) {

	//initialise no input errors assumed initially before we test

	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible

	if (ContainsIllegalCharacters($_POST['MeasureName'])) {
		$InputError = 1;
		prnMsg( '该工种名不能包含任何非法字符','error');
	}
	if (trim($_POST['MeasureName']) == '') {
		$InputError = 1;
		prnMsg( '工种信息不得为空', 'error');
	}

	if (isset($_POST['SelectedMeasureID']) AND $_POST['SelectedMeasureID']!='' AND $InputError !=1) {


		/*SelectedMeasureID could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/
		// Check the name does not clash
		$sql = "SELECT count(*) FROM job
				WHERE jobid <> '" . $SelectedMeasureID ."'
				AND jobname ".LIKE." '" . $_POST['MeasureName'] . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( '不能重命名工种名，因为相同名称的另一个已经存在.','error');
		} else {
			// Get the old name and check that the record still exist neet to be very carefull here
			// idealy this is one of those sets that should be in a stored procedure simce even the checks are
			// relavant
			$sql = "SELECT jobname FROM job
				WHERE jobid = '" . $SelectedMeasureID . "'";
			$result = DB_query($sql);
			if ( DB_num_rows($result) != 0 ) {
				// This is probably the safest way there is
				$myrow = DB_fetch_row($result);
				$OldMeasureName = $myrow[0];
				$sql = array();
				$sql[] = "UPDATE job
					SET jobname='" . $_POST['MeasureName'] . "'
					WHERE jobname ".LIKE." '".$OldMeasureName."'";
			/*	$sql[] = "UPDATE stockmaster
					SET units='" . $_POST['MeasureName'] . "'
					WHERE units ".LIKE." '" . $OldMeasureName . "'";*/
			} else {
				$InputError = 1;
				prnMsg('工种名没有存在.','error');
			}
		}
		$msg = '工种名改变';
	} elseif ($InputError !=1) {
		/*SelectedMeasureID is null cos no item selected on first time round so must be adding a record*/
		$sql = "SELECT count(*) FROM job
				WHERE jobname " .LIKE. " '".$_POST['MeasureName'] ."'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ( $myrow[0] > 0 ) {
			$InputError = 1;
			prnMsg( '不能创建工种名，因为相同名称的另一个已经存在.','error');
		} else {
			$sql = "INSERT INTO job (jobname )
					VALUES ('" . $_POST['MeasureName'] ."')";
		}
		$msg = '新的工种名已经添加';
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		if (is_array($sql)) {
			$result = DB_Txn_Begin();
			$tmpErr = '不能更新新的工种名';
			$tmpDbg = _('The sql that failed was') . ':';
			foreach ($sql as $stmt ) {
				$result = DB_query($stmt, $tmpErr,$tmpDbg,true);
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
	}
	unset ($SelectedMeasureID);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureName']);

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button
// PREVENT DELETES IF DEPENDENT RECORDS IN 'stockmaster'
	// Get the original name of the unit of measure the ID is just a secure way to find the unit of measure
	$sql = "SELECT jobname FROM job
		WHERE jobid = '" . $SelectedMeasureID . "'";
	$result = DB_query($sql);
	if ( DB_num_rows($result) == 0 ) {
		// This is probably the safest way there is
		prnMsg( '不能删除这个工种名因为已经被使用','warn');
	} else {
		$myrow = DB_fetch_row($result);
		$OldMeasureName = $myrow[0];
		$sql= "SELECT COUNT(*) FROM stockmaster WHERE units ".LIKE." '" . $OldMeasureName . "'";
		$result = DB_query($sql);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			prnMsg( '工种已经被使用，所以不能删除！','warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . '该操作无效！' . '</font>';
		} else {
			$sql="DELETE FROM job WHERE jobname ".LIKE."'" . $OldMeasureName . "'";
			$result = DB_query($sql);
			prnMsg( $OldMeasureName . ' ' . '工种已经被删除' . '!','success');
		}
	} //end if account group used in GL accounts
	unset ($SelectedMeasureID);
	unset ($_GET['SelectedMeasureID']);
	unset($_GET['delete']);
	unset ($_POST['SelectedMeasureID']);
	unset ($_POST['MeasureID']);
	unset ($_POST['MeasureName']);
}

 if (!isset($SelectedMeasureID)) {



	$sql = "SELECT jobid,
			jobname
			FROM job
			ORDER BY jobid";

	$ErrMsg = '没取到工种名';
	$result = DB_query($sql,$ErrMsg);

	echo '<table class="selection">
			<tr>
				<th class="ascending">' . '工种'. '</th>
			</tr>';

	$k=0; //row colour counter
	while ($myrow = DB_fetch_row($result)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}

		echo '<td>' . $myrow[1] . '</td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $myrow[0] . '">' . _('Edit') . '</a></td>';
		echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectedMeasureID=' . $myrow[0] . '&amp;delete=1" onclick="return confirm(\'' . '确定你要删除这个工种名?' . '\';">' . _('Delete')  . '</a></td>';
		echo '</tr>';

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedMeasureID)) {
	echo '<div class="centre">
			<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' .'维护工种名' . '</a>
		</div>';
}

echo '<br />';

if (! isset($_GET['delete'])) {

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if (isset($SelectedMeasureID)) {
		//editing an existing section

		$sql = "SELECT jobid,
				jobname
				FROM job
				WHERE jobid='" . $SelectedMeasureID . "'";

		$result = DB_query($sql);
		if ( DB_num_rows($result) == 0 ) {
			prnMsg( _('Could not retrieve the requested unit of measure, please try again.'),'warn');
			unset($SelectedMeasureID);
		} else {
			$myrow = DB_fetch_array($result);

			$_POST['MeasureID'] = $myrow['jobid'];
			$_POST['MeasureName']  = $myrow['jobname'];

			echo '<input type="hidden" name="SelectedMeasureID" value="' . $_POST['MeasureID'] . '" />';
			echo '<table class="selection">';
		}

	}  else {
		$_POST['MeasureName']='';
		echo '<table>';
	}
	echo '<tr>
		<td>' . '工种' . ':' . '</td>
		<td><input required="required" pattern="(?!^ *$)[^+<>-]{1,}" type="text" name="MeasureName" title="'._('Cannot be blank or contains illegal characters').'" placeholder="'._('More than one character').'" size="30" maxlength="30" value="' . $_POST['MeasureName'] . '" /></td>
		</tr>';
	echo '</table>';

	echo '<div class="centre">
			<input type="submit" name="Submit" value="' . _('Enter Information') . '" />
		</div>';

	echo '</div>
          </form>';

} //end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
