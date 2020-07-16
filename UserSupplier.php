
<?php
/* $Id: UserSupplier.php  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-11-20 16:43:32 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-11-20 16:21:58
 */

include('includes/session.php');
$Title = '授权供应商';
$ViewTopic = 'UserSupplier';
$BookMark = 'UserSupplier';
include('includes/header.php');

if(isset($_POST['SelectedUser']) and $_POST['SelectedUser']<>'') {//If POST not empty:
	$SelectedUser = $_POST['SelectedUser'];
} elseif(isset($_GET['SelectedUser']) and $_GET['SelectedUser']<>'') {//If GET not empty:
	$SelectedUser = $_GET['SelectedUser'];
} else {// Unset empty SelectedUser:
	unset($_GET['SelectedUser']);
	unset($_POST['SelectedUser']);
	unset($SelectedUser);
}

if(isset($_POST['SelectedCustCode']) and $_POST['SelectedCustCode']<>'') {//If POST not empty:
	$SelectedCustCode = mb_strtoupper($_POST['SelectedCustCode']);
} elseif(isset($_GET['SelectedCustCode']) and $_GET['SelectedCustCode']<>'') {//If GET not empty:
	$SelectedCustCode = mb_strtoupper($_GET['SelectedCustCode']);
} else {// Unset empty SelectedCustCode:
	unset($_GET['SelectedCustCode']);
	unset($_POST['SelectedCustCode']);
	unset($SelectedCustCode);
}

if(isset($_GET['Cancel']) or isset($_POST['Cancel'])) {
	unset($SelectedUser);
	unset($SelectedCustCode);
}


if(!isset($SelectedUser)) {// If is NOT set a user for GL accounts.
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="',// Icon image.
		$Title, '" /> ',// Icon title.
		$Title, '</p>';// Page title.

	/* It could still be the second time the page has been run and a record has been selected for modification - SelectedCustCode will exist because it was sent with the new call. If its the first time the page has been displayed with no parameters then none of the above are true. These will call the same page again and allow update/input or deletion of the records.*/

	if(isset($_POST['Process'])) {
		prnMsg(_('You have not selected any user'), 'error');
	}
	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<table class="selection">
			<tr>
				<td>', _('Select User'), ':</td>
				<td><select name="SelectedUser" onchange="this.form.submit()">',// Submit when the value of the select is changed.
					'<option value="">', _('Not Yet Selected'), '</option>';
	$Result = DB_query("SELECT	userid,
								realname
							FROM www_users
							ORDER BY userid");
	while ($MyRow = DB_fetch_array($Result)) {
		echo '<option ';
		if(isset($SelectedUser) and $MyRow['userid'] == $SelectedUser) {
			echo 'selected="selected" ';
		}
		echo 'value="', $MyRow['userid'], '">', $MyRow['userid'], ' - ', $MyRow['realname'], '</option>';
	}// End while loop.
	echo '</select></td>
			</tr>
		</table>';//Close Select_User table.

	DB_free_result($Result);

	echo	'<div class="centre noprint">',// Form buttons:
				'<button name="Process" type="submit" value="Submit"><img alt="" src="', $RootPath, '/css/', $Theme,
					'/images/user.png" /> ', _('Accept'), '</button> '; // "Accept" button.

} else {// If is set a user for GL accounts ($SelectedUser).
	$Result = DB_query("SELECT realname
							FROM www_users
							WHERE userid='" . $SelectedUser . "'");
	$MyRow = DB_fetch_array($Result);
	$SelectedUserName = $MyRow['realname'];
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/gl.png" title="',// Icon image.
		$Title, '" /> ',// Icon title.
		$Title, ' ', $SelectedUserName, '</p>';// Page title.

	// BEGIN: Needs $SelectedUser, $SelectedCustCode:
	if(isset($_POST['submit'])) {
		
		if(!isset($SelectedCustCode)) {
			prnMsg(_('You have not selected an GL Account to be authorised for this user'), 'error');
		} else {
			// First check the user is not being duplicated
			//SELECT `regid`, `userid`, `custype`, `canview`, `canupd` FROM `customerusers
			$CheckResult = DB_query("SELECT count(*)
		              	 FROM customerusers 
					 	WHERE (custype=2 OR custype=3)
						 AND customerusers.userid='" . $SelectedUser . "'
				AND regid= '" . $SelectedCustCode . "'	");
			$CheckRow = DB_fetch_row($CheckResult);
			if($CheckRow[0] > 0) {
				prnMsg(_('The GL Account') . ' ' . $SelectedCustCode . ' ' . _('is already authorised for this user'), 'error');
			} else {
				// Add new record on submit
				$csnocode=$SelectedCustCode;
				$SQL = "INSERT INTO customerusers(regid,  userid, custype, canview, canupd)
							 VALUES ('" .$csnocode . "',
							 			 '" .$SelectedUser . "',
								'2',
								'1',
								'1')";
				$ErrMsg ='对选择的供应商授权没有完成';
				if(DB_query($SQL, $ErrMsg)) {
					prnMsg( $SelectedUser . ' 对选择的供应商[编码: ' . $SelectedCustCode . ']授权', 'success');
					unset($_GET['SelectedCustCode']);
					unset($_POST['SelectedCustCode']);
				}
			}
		}
	} elseif(isset($_GET['delete']) or isset($_POST['delete'])) {
		$SQL = "DELETE FROM customerusers
			     WHERE regid='" . $SelectedCustCode . "'
				  AND ( custype=2 OR custype=3)
			      AND userid='" . $SelectedUser . "'";
		$ErrMsg ='对选择的供应商授权撤销没有完成';
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg( $SelectedUser . '对选择的供应商[编码: ' . $SelectedCustCode . ']授权撤销', 'success');
			unset($_GET['delete']);
			unset($_POST['delete']);
		}
	} elseif(isset($_GET['ToggleUpdate']) or isset($_POST['ToggleUpdate'])) {// Can update (write) GL accounts flag.
		if(isset($_GET['ToggleUpdate']) and $_GET['ToggleUpdate']<>'') {//If GET not empty.
			$ToggleUpdate = $_GET['ToggleUpdate'];
		} elseif(isset($_POST['ToggleUpdate']) and $_POST['ToggleUpdate']<>'') {//If POST not empty.
			$ToggleUpdate = $_POST['ToggleUpdate'];
		}
		$SQL = "UPDATE customerusers
				SET canupd='" . $ToggleUpdate . "'
				WHERE regid='" . $SelectedCustCode . "'
				AND ( custype=2 OR custype=3)
				AND userid='" . $SelectedUser . "'";
		$ErrMsg = '对选择的供应商授权修改没有完成';
		if(DB_query($SQL, $ErrMsg)) {
			prnMsg($SelectedUser . ' 对选择的供应商[编码:  ' . $SelectedCustCode . ']跟新权撤销', 'success');
			unset($_GET['ToggleUpdate']);
			unset($_POST['ToggleUpdate']);
		}
	}
	// END: Needs $SelectedUser, $SelectedCustCode.

	echo '<table class="selection">
		<thead>
		<tr>
			<th class="text">', _('Code'), '</th>
			<th class="text">', _('Name'), '</th>
			<th class="centre">', _('View'), '</th>
			<th class="centre">', _('Update'), '</th>
			<th class="noprint" colspan="2">&nbsp;</th>
		</tr>
		</thead><tbody>';
	
	 $Result=DB_query("SELECT regid custcode,
	                       suppname custname,
						   custype, 
						   canview,
						   canupd 
	 	                FROM customerusers
						LEFT JOIN suppliers  ON supplierid=regid 
	 					WHERE custype=2 AND  customerusers.userid='" . $SelectedUser . "'
						ORDER BY customerusers.regid ASC");

	if(DB_num_rows($Result)>0) {// If the user has access permissions to one or more GL accounts:
		$k = 0; //row colour counter
		while ($MyRow = DB_fetch_array($Result)) {
			if($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '<td class="text">', $MyRow['custcode'], '</td>
				<td class="text">', $MyRow['custname'], '</td>
				<td class="centre">';
			if($MyRow['canview'] == 1) {
				echo _('Yes');
			} else {
				echo _('No');
			}
			echo '</td>
				<td class="centre">';

			$ScriptName = htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8');
			if($MyRow['canupd'] == 1) {
				echo _('Yes'), '</td>',
					'<td class="noprint"><a href="', $ScriptName, '?SelectedUser=', $SelectedUser, '&amp;SelectedCustCode=', $MyRow['custcode'], '&amp;ToggleUpdate=0" onclick="return confirm(\'', _('Are you sure you wish to remove Update for this GL Account?'), '\');">', _('Remove Update');
			} else {
				echo _('No'), '</td>',
					'<td class="noprint"><a href="', $ScriptName, '?SelectedUser=', $SelectedUser, '&amp;SelectedCustCode=', $MyRow['custcode'], '&amp;ToggleUpdate=1" onclick="return confirm(\'', _('Are you sure you wish to add Update for this GL Account?'), '\');">', _('Add Update');
			}
			echo	'</a></td>',
					'<td class="noprint"><a href="', $ScriptName, '?SelectedUser=', $SelectedUser, '&amp;SelectedCustCode=', $MyRow['custcode'], '&amp;delete=yes" onclick="return confirm(\'', _('Are you sure you wish to un-authorise this GL Account?'), '\');">', _('Un-authorise'), '</a></td>',
				'</tr>';
		}// End while list loop.
	} else {// If the user does not have access permissions to GL accounts:
		echo '<tr><td class="centre" colspan="6">', _('User does not have access permissions to GL accounts'), '</td></tr>';
	}
	echo '</tbody></table>',
		'<br />',
		'<form action="', htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'), '" method="post">',
		'<input name="FormID" type="hidden" value="', $_SESSION['FormID'], '" />',
		'<input name="SelectedUser" type="hidden" value="', $SelectedUser, '" />',
		'<br />
		<table class="selection noprint">
			<tr>
				<td>';
	//$Result = DB_query("SELECT concat(`supplierid`,'^') custcode,  1 custype,`suppname` custname FROM `suppliers` UNION SELECT CONCAT(debtorno,'^',branchcode) custcode,2 custype, brname custname FROM custbranch  
	
	$Result = DB_query("SELECT supplierid custcode, suppname custname 
					FROM suppliers 
					WHERE
					NOT EXISTS (SELECT   canupd  FROM customerusers   WHERE  supplierid=regid AND custype=2
	 	  			AND  customerusers.userid='" . $SelectedUser . "')");
	
	if(DB_num_rows($Result)>0) {// If the user does not have access permissions to one or more GL accounts:
		echo	'选择供应商添加权限:</td>
				<td><select name="SelectedCustCode">';
		if(!isset($_POST['SelectedCustCode'])) {
			echo '<option selected="selected" value="">', _('Not Yet Selected'), '</option>';
		}
		while ($MyRow = DB_fetch_array($Result)) {
			if(isset($_POST['SelectedCustCode']) and $MyRow['custcode'] == $_POST['SelectedCustCode']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo $MyRow['custcode'], '">', $MyRow['custcode'], ' - ', $MyRow['custname'], '</option>';
		}
		echo	'</select></td>
				<td><input type="submit" name="submit" value="确认更新" />';
	} else {// If the user has access permissions to all GL accounts:
		echo _('User has access permissions to all GL accounts');
	}
	echo		'</td>
			</tr>
		</table>';
	DB_free_result($Result);
	echo '<br>', // Form buttons:
		'<div class="centre noprint">',
			'<button onclick="javascript:window.print()" type="button"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/printer.png" /> ', _('Print This'), '</button>', // "Print This" button.
			'<button formaction="UserSupplier.php?Cancel" type="submit"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/user.png" /> ', _('Select A Different User'), '</button>'; // "Select A Different User" button.
}
echo		'<button formaction="UserSupplier.php" type="submit"><img alt="" src="', $RootPath, '/css/', $Theme,
				'/images/previous.png" /> ', _('Return'), '</button>', // "Return" button.
		'</div>
	</form>';

include('includes/footer.php');
?>
