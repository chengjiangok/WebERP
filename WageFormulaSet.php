<?php
/* $Id: WWW_Users.php 7402 2015-11-27 00:26:56Z tehonu $*/

if (isset($_POST['UserID']) AND isset($_POST['ID'])){
	if ($_POST['UserID'] == $_POST['ID']) {
		$_POST['Language'] = $_POST['UserLanguage'];
	}
}
include('includes/session.php');

$Title = _('Wage Formula Set');// Screen identificator.
$ViewTopic= 'GettingStarted';// Filename's id in ManualContents.php's TOC.
$BookMark = 'UserMaintenance';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/group_add.png" title="' .// Title icon.
	_('Search') . '" />' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['submit'])) {

} elseif (isset($_GET['delete'])) {

}

if (!isset($SelectedUser)) {

	
	$sql = "SELECT `formulaID`, `title`, `formula`, description, `field`, `empname`, `department`, `crtdate`, `flag` FROM `wageformula` ";
	$result = DB_query($sql);

	echo '<table class="selection">
		<tr>
				<th class="ascending">',iconv( "GB2312", "UTF-8" ,'��ʽ����'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'��ʽ��'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'�ֶ���'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'��ʽ����'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'��ʽ�﷨'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'����'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'Ա��'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'����'), '</th>
				<th class="ascending">', iconv( "GB2312", "UTF-8" ,'״̬'), '</th>
				<th colspan="2">&nbsp;</th>
			</tr>';
		

	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {

		printf('<tr>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
						<td>%s</td>
							<td>%s</td>
					<td class="number">%s</td>
					<td><a href="%s&amp;SelectedWC=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&amp;SelectedWC=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this work centre?') . '\');">' . _('Delete')  . '</a></td>
				</tr>',
				$myrow['formulaID'],
			
				$myrow['title'],
					$myrow['field'],
				$myrow['description'],
				$myrow['formula'],
					$myrow['department'],
						$myrow['empname'],
						$myrow['crtdate'],
							$myrow['flag'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['wfID'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['wfID']);
	}
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

	$sql = "SELECT userid,
			realname,
			phone,
			email,
			customerid,
			password,
			branchcode,
			supplierid,
			salesman,
			pagesize,
			fullaccess,
			cancreatetender,
			defaultlocation,
			modulesallowed,
			showdashboard,
			blocked,
			theme,
			language,
			pdflanguage,
			department
		FROM www_users
		WHERE userid='" . $SelectedUser . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	$_POST['Phone'] = $myrow['phone'];
	$_POST['Email'] = $myrow['email'];
	$_POST['Cust']	= $myrow['customerid'];
	$_POST['BranchCode']  = $myrow['branchcode'];
	$_POST['SupplierID'] = $myrow['supplierid'];
	$_POST['Salesman'] = $myrow['salesman'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['CanCreateTender'] = $myrow['cancreatetender'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['ShowDashboard'] = $myrow['showdashboard'];
	$_POST['Blocked'] = $myrow['blocked'];
	$_POST['PDFLanguage'] = $myrow['pdflanguage'];
	$_POST['Department'] = $myrow['department'];

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

if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}
echo '<tr>
		<td>' . _('Password') . ':</td>
		<td><input type="password" pattern=".{5,}" name="Password" ' . (!isset($SelectedUser) ? 'required="required"' : '') . ' size="22" maxlength="20" value="' . $_POST['Password'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
echo '<tr>
		<td>' . _('Full Name') . ':</td>
		<td><input type="text" name="RealName" ' . (isset($SelectedUser) ? 'autofocus="autofocus"' : '') . ' required="required" value="' . $_POST['RealName'] . '" size="36" maxlength="35" /></td>
	</tr>';

/*Make an array out of the comma separated list of modules allowed*/
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);

$i=0;
foreach($ModuleList as $ModuleName){

	echo '<tr>
			<td>' . _('Display') . ' ' . $ModuleName . ' ' . _('module') . ': </td>
			<td><select name="Module_' . $i . '">';
	if ($ModulesAllowed[$i]==0){
		echo '<option selected="selected" value="0">' . _('No') . '</option>';
		echo '<option value="1">' . _('Yes') . '</option>';
	} else {
	 	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
		echo '<option value="0">' . _('No') . '</option>';
	}
	echo '</select></td>
		</tr>';
	$i++;
}

echo '<tr>
		<td>' . _('Display Dashboard after Login') . ': </td>
		<td><select name="ShowDashboard">';
if($_POST['ShowDashboard']==0) {
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}
echo '</select></td>
	</tr>';

if (!isset($_POST['PDFLanguage'])){
	$_POST['PDFLanguage']=2;
}

echo '<tr>
		<td>' . _('PDF Language Support') . ': </td>
		<td><select name="PDFLanguage">';
for($i=0;$i < count($PDFLanguages);$i++){
	if ($_POST['PDFLanguage']==$i	){
		echo '<option selected="selected" value="' . $i .'">' . $PDFLanguages[$i] . '</option>';
	} else {
		echo '<option value="' . $i .'">' . $PDFLanguages[$i]. '</option>';
	}
}
echo '</select></td>
	</tr>';

/* Allowed Department for Internal Requests */

echo '<tr>
		<td>' . _('Allowed Department for Internal Requests') . ':</td>';

$sql="SELECT departmentid,
			description
		FROM departments
		ORDER BY description";

$result=DB_query($sql);
echo '<td><select name="Department">';
if ((isset($_POST['Department']) AND $_POST['Department']=='0') OR !isset($_POST['Department'])){
	echo '<option selected="selected" value="0">' .  _('Any Internal Department') . '</option>';
} else {
	echo '<option value="">' . _('Any Internal Department') . '</option>';
}
while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['Department']) AND $myrow['departmentid'] == $_POST['Department']){
		echo '<option selected="selected" value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
	} else {
		echo '<option value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
	}
}
echo '</select></td>
	</tr>';

/* Account status */

echo '<tr>
		<td>' . _('Account Status') . ':</td>
		<td><select required="required" name="Blocked">';
if ($_POST['Blocked']==0){
	echo '<option selected="selected" value="0">' . _('Open') . '</option>';
	echo '<option value="1">' . _('Blocked') . '</option>';
} else {
 	echo '<option selected="selected" value="1">' . _('Blocked') . '</option>';
	echo '<option value="0">' . _('Open') . '</option>';
}
echo '</select></td>
	</tr>';

echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.php');
?>
