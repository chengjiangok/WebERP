/*
 * @Author: chengjiang 
 * @Date: 2017-02-02 09:05:09 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2017-02-02 09:55:59
 */
<?php
/* $Id：BankCopyFormat    chengjiang $ */
/*实现银行账单格式设计，建设中*/
if (isset($_POST['UserID']) AND isset($_POST['ID'])){
	if ($_POST['UserID'] == $_POST['ID']) {
		$_POST['Language'] = $_POST['UserLanguage'];
	}
}
include('includes/session.php');

$ModuleList = array(_('Sales'),
					_('Receivables'),
					_('Purchases'),
					_('Payables'),
					_('Inventory'),
					_('Manufacturing'),
					_('General Ledger'),	
				_('Human Resources'),	
					_('Asset Manager'),
					_('Petty Cash'),
						_('My Tools'),		
					_('Setup'),
					_('Utilities'));
$PDFLanguages = array(_('Latin Western Languages'),
						_('Eastern European Russian Japanese Korean Vietnamese Hebrew Arabic Thai'),
						_('Chinese'),
						_('Free Serif'));

$Title ='银行对帐单设置';// Screen identificator.
$ViewTopic= 'GettingStarted';// Filename's id in ManualContents.php's TOC.
$BookMark = 'UserMaintenance';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/group_add.png" title="' .// Title icon.
	_('Search') . '" />' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

echo '<br />';// Extra line after page_title_text.

// Make an array of the security roles
$sql = "SELECT secroleid,
				secrolename
		FROM securityroles
		ORDER BY secrolename";

$Sec_Result = DB_query($sql);
$SecurityRoles = array();
// Now load it into an a ray using Key/Value pairs
while( $Sec_row = DB_fetch_row($Sec_Result) ) {
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);

if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	if (mb_strlen($_POST['UserID'])<4){
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIlLegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'),'error');
	} elseif (mb_strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	} elseif ((mb_strlen($_POST['Cust'])>0)
				AND (mb_strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'),'error');
	} elseif ($AllowDemoMode AND $_POST['UserID'] == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be modified.'),'error');
		$InputError = 1;
	}

	if (!isset($SelectedUser)){
		/* check to ensure the user id is not already entered */
		$result = DB_query("SELECT userid FROM www_users WHERE userid='" . $_POST['UserID'] . "'");
		if (DB_num_rows($result)==1){
			$InputError =1;
			prnMsg(_('The user ID') . ' ' . $_POST['UserID'] . ' ' . _('already exists and cannot be used again'),'error');
		}
	}

	if ((mb_strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		// check that the entered branch is valid for the customer code
		$sql = "SELECT custbranch.debtorno
				FROM custbranch
				WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
				AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";

		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		if (DB_num_rows($result)==0){
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'),'error');
			$InputError = 1;
		}
	}

	/* Make a comma separated list of modules allowed ready to update the database*/
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList)){
		$FormVbl = 'Module_' . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	
	$_POST['ModulesAllowed']= $ModulesAllowed.'0,';

	if (isset($SelectedUser) AND $InputError !=1) {

/*SelectedUser could also exist if submit had not been clicked this code would not run in this case cos submit is false of course  see the delete code below*/

		if (!isset($_POST['Cust'])
			OR $_POST['Cust']==NULL
			OR $_POST['Cust']==''){

			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		$UpdatePassword = '';
		if ($_POST['Password'] != ''){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',
						customerid='" . $_POST['Cust'] ."',
						phone='" . $_POST['Phone'] ."',
						email='" . $_POST['Email'] ."',
						" . $UpdatePassword . "
						branchcode='" . $_POST['BranchCode'] . "',
						supplierid='" . $_POST['SupplierID'] . "',
						salesman='" . $_POST['Salesman'] . "',
						pagesize='" . $_POST['PageSize'] . "',
						fullaccess='" . $_POST['Access'] . "',
						cancreatetender='" . $_POST['CanCreateTender'] . "',
						theme='" . $_POST['Theme'] . "',
						language ='" . $_POST['UserLanguage'] . "',
						defaultlocation='" . $_POST['DefaultLocation'] ."',
						modulesallowed='" . $ModulesAllowed . "',
						showdashboard='" . $_POST['ShowDashboard'] . "',
						blocked='" . $_POST['Blocked'] . "',
						pdflanguage='" . $_POST['PDFLanguage'] . "',
						department='" . $_POST['Department'] . "'
					WHERE userid = '". $SelectedUser . "'";

		prnMsg( _('The selected user record has been updated'), 'success' );
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO www_users (userid,
						realname,
						customerid,
						branchcode,
						supplierid,
						salesman,
						password,
						phone,
						email,
						pagesize,
						fullaccess,
						cancreatetender,
						defaultlocation,
						modulesallowed,
						displayrecordsmax,
						theme,
						language,
						pdflanguage,
						department)
					VALUES ('" . $_POST['UserID'] . "',
						'" . $_POST['RealName'] ."',
						'" . $_POST['Cust'] ."',
						'" . $_POST['BranchCode'] ."',
						'" . $_POST['SupplierID'] ."',
						'" . $_POST['Salesman'] . "',
						'" . CryptPass($_POST['Password']) ."',
						'" . $_POST['Phone'] . "',
						'" . $_POST['Email'] ."',
						'" . $_POST['PageSize'] ."',
						'" . $_POST['Access'] . "',
						'" . $_POST['CanCreateTender'] . "',
						'" . $_POST['DefaultLocation'] ."',
						'" . $ModulesAllowed . "',
						'" . $_SESSION['DefaultDisplayRecordsMax'] . "',
						'" . $_POST['Theme'] . "',
						'". $_POST['UserLanguage'] ."',
						'" . $_POST['PDFLanguage'] . "',
						'" . $_POST['Department'] . "')";
		prnMsg( _('A new user record has been inserted'), 'success' );
		
		$LocationSql = "INSERT INTO locationusers (loccode,
													userid,
													canview,
													canupd
												) VALUES (
													'" . $_POST['DefaultLocation'] . "',
													'" . $_POST['UserID'] . "',
													1,
													1
												)";
		$ErrMsg = _('The default user locations could not be processed because');
		$DbgMsg = _('The SQL that was used to create the user locations and failed was');
		$Result = DB_query($LocationSql, $ErrMsg, $DbgMsg);
		prnMsg( _('User has been authorized to use and update only his / her default location'), 'success' );
		
		$GLAccountsSql = "INSERT INTO glaccountusers (userid, accountcode, canview, canupd)
						  SELECT '" . $_POST['UserID'] . "', chartmaster.accountcode,1,1
						  FROM chartmaster;	";
		
		$ErrMsg = _('The default user GL Accounts could not be processed because');
		$DbgMsg = _('The SQL that was used to create the user GL Accounts and failed was');
		$Result = DB_query($GLAccountsSql, $ErrMsg, $DbgMsg);
		prnMsg( _('User has been authorized to use and update all GL accounts'), 'success' );
	}

	if ($InputError!=1){
		//run the SQL from either of the above possibilites
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Cust']);
		unset($_POST['BranchCode']);
		unset($_POST['SupplierID']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['CanCreateTender']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['ShowDashboard']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($_POST['Department']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button


	if ($AllowDemoMode AND $SelectedUser == 'admin') {
		prnMsg(_('The demonstration user called demo cannot be deleted'),'error');
	} else {
		$sql="SELECT userid FROM audittrail where userid='" . $SelectedUser ."'";
		$result=DB_query($sql);
		if (DB_num_rows($result)!=0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {
			$sql="DELETE FROM locationusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Location - User could not be deleted because');;
			$result = DB_query($sql,$ErrMsg);

			$sql="DELETE FROM glaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The GL Account - User could not be deleted because');;
			$result = DB_query($sql,$ErrMsg);

			$sql="DELETE FROM bankaccountusers WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The Bank Accounts - User could not be deleted because');;
			$result = DB_query($sql,$ErrMsg);

			$sql="DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');;
			$result = DB_query($sql,$ErrMsg);
			prnMsg(_('User Deleted'),'info');
		}
		unset($SelectedUser);
	}

}

if (!isset($SelectedUser)) {

/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT `bankid`, `bank_name`, `importformat` 
	        FROM `bankname` ";
	$result = DB_query($sql);

	echo '<table class="selection">';
	echo '<tr><th>' . _('ID') . '</th>
				<th>' . _('Bank Name') . '</th>
				<th>' . _('Update Date') . '</th>
			
			
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>';

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

	if ($myrow[8]=='') {
		$LastVisitDate = _('No login record');
	} else {
		$LastVisitDate = ConvertSQLDate($myrow[8]);
	}

		/*The SecurityHeadings array is defined in config.php */

		printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					
					<td><a href="%s&amp;SelectedUser=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&amp;SelectedUser=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this user?') . '\');">' . _('Delete') . '</a></td>
					</tr>',
					$myrow['bankid'],
					$myrow['bank_name'],
					$myrow['importformat'],
				
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?',
					$myrow['bankid'],
					htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
					$myrow['bankid']);

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!


if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . _('Review Existing Users') . '</a></div><br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedUser)) {
	//editing an existing User

	$sql = "SELECT
	       `bankid`, `header`, `title`, `remark`, `flg` FROM `bankcopyformat`
		WHERE bankid='" . $SelectedUser . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	

	echo '<input type="hidden" name="SelectedUser" value="' . $SelectedUser . '" />';
	echo '<input type="hidden" name="UserID" value="' . $_POST['UserID'] . '" />';
	echo '<input type="hidden" name="ModulesAllowed" value="' . $_POST['ModulesAllowed'] . '" />';

	echo '<table class="selection">
			<tr>
				<td>' . _('User code') . ':</td>
				<td>' . $_POST['UserID'] . '</td>
			</tr>';

} else { //end of if $SelectedUser only do the else when a new record is being entered

	echo '<table class="selection">
			<tr>
				<td>' . _('User Login') . ':</td>
				<td><input pattern="(?!^([aA]{1}[dD]{1}[mM]{1}[iI]{1}[nN]{1})$)[^?+.&\\>< ]{4,}" type="text" required="required" name="UserID" size="22" maxlength="20" placeholder="'._('At least 4 characters').'" title="'._('Please input not less than 4 characters and canot be admin or contains illegal characters').'"  /></td>
			</tr>';

	/*set the default modules to show to all
	this had trapped a few people previously*/
	$i=0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed']='';
	}
	foreach($ModuleList as $ModuleName){
		if ($i>0){
			$_POST['ModulesAllowed'] .=',';
		}
		if ($i==9 or $i==10){
			$_POST['ModulesAllowed'] .= '0';
		}else {
		 	
			
		$_POST['ModulesAllowed'] .= '1';
		}
		$i++;
	}
}



/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);

$i=0;
foreach($ModuleList as $ModuleName){

	echo '<tr>
			<td>' . _('Display') . ' ' . $ModuleName . ' ' . _('module') . ': </td>
		
	<td><input type="text" name="RealName" ' . $i . 'value="' .' $_POST["'.'RealName'.$i.'"] '. '" size="36" maxlength="35" /></td>';

	echo '</td>
		</tr>';
	$i++;
}




echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.php');
?>
