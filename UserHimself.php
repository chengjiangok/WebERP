<?php
/* $Id: WWW_Users.php 7402 2015-11-27 00:26:56Z tehonu $*/

include('includes/session.php');

$Title = _('Users Maintenance');// Screen identificator.
$ViewTopic= 'GettingStarted';// Filename's id in ManualContents.php's TOC.
$BookMark = 'UserMaintenance';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/group_add.png" title="' .// Title icon.
	_('Search') . '" />' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
 $PasswordVerified = false;

/*
if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . _('Review Existing Users') . '</a></div><br />';
}
*/
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<div>
	<table class="selection"><tbody>
			<tr>
				<td>用户编码:</td>
				<td>' .$_SESSION['UserID'] . '</td>
			</tr>';


if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['Theme'])) {
	$_POST['Theme']=$_SESSION['Theme'] ;
}

if (!isset($_POST['Email'])) {
	$_POST['Email']=$_SESSION['UserEmail'];
}

echo '<tr>
		<td>' . _('Full Name') . ':</td>
		<td> ' .$_SESSION['UsersRealName'].'</td>
	</tr>';
echo '<tr>
		<td>旧密码:</td>
		<td><input type="password" pattern=".{5,}" name="Password"  size="22" maxlength="20" value="' . $_POST['Password'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
echo '<tr>
		<td>新密码:</td>
		<td><input type="password" pattern=".{5,}" name="Password1"  size="22" maxlength="20" value="' . $_POST['Password1'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
echo '<tr>
		<td>再次输入:</td>
		<td><input type="password" pattern=".{5,}" name="Password2"  size="22" maxlength="20" value="' . $_POST['Password2'] . '" placeholder="'._('At least 5 characters').'" title="'._('Passwords must be 5 characters or more and cannot same as the users id. A mix of upper and lower case and some non-alphanumeric characters are recommended.').'" /></td>
	</tr>';
	/*	
echo '<tr>
		<td>' . _('Telephone No') . ':</td>
		<td><input type="tel" name="Phone" pattern="[0-9+()\s-]*" value="' . $_POST['Phone'] . '"  size="32" maxlength="30" /></td>
	</tr>';*/
echo '<tr>
		<td>' . _('Email Address') .':</td>
		<td><input type="email" name="Email" placeholder="' . _('e.g. user@domain.com') . '" value="' . $_POST['Email'] .'" size="32" maxlength="55" title="'._('A valid email address is required').'" /></td>
	</tr>';
	


echo '<tr>
		<td>' . _('Theme') . ':</td>
		<td><select required="required" name="Theme">';

$ThemeDirectories = scandir('css/');


foreach ($ThemeDirectories as $ThemeName) {

	if (is_dir('css/' . $ThemeName) AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){

		if (isset($_POST['Theme']) AND $_POST['Theme'] == $ThemeName){
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		} else if (!isset($_POST['Theme']) AND ($Theme==$ThemeName)) {
			echo '<option selected="selected" value="' . $ThemeName . '">' . $ThemeName  . '</option>';
		} else {
			echo '<option value="' . $ThemeName . '">' . $ThemeName . '</option>';
		}
	}
}

echo '</select></td>
	</tr>';



echo '</tbody></table></div>';
/*
	if (ContainsIlLegalCharacters($_POST['UserID'])) {
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' &amp; + \" \\ " . _('or a space'),'error');
	} */

if (isset($_POST['submit'])) {
	if ($_POST['Password']!=''&&$_POST['Password1']!='' && $_POST['Password2']!=''){
		if (mb_strlen($_POST['Password'])<5 ){
		
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		
		} elseif (mb_strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
		} 
		$sql = "SELECT *
				FROM www_users
				WHERE www_users.userid='" . $_SESSION['UserID'] . "'";

		$ErrMsg = _('Could not retrieve user details on login because');
		$debug =1;
       
		$Auth_Result = DB_query($sql,$ErrMsg);
        $msg='';
		if (DB_num_rows($Auth_Result) > 0) {
			$myrow = DB_fetch_array($Auth_Result);
		
			if (VerifyPass($_POST['Password'],$myrow['password'])) {
				$PasswordVerified = true;
			}else{
				$msg='旧密码输入错误 ';
			 
			}
		}
			if ($_POST['Password1']==$_POST['Password2'] && $_POST['Password1']!=''){
			
					$sql = "UPDATE www_users SET password = '" . CryptPass($_POST['Password1']) . "'"
							. " WHERE userid = '" .  $_SESSION['UserID'] . "';";
					DB_query($sql);
					prnMsg('密码修改成功！','info');
			}else{
				if($msg!=''){
				$msg.=',';	
				}
				$msg.=' 新密码两次输入不一致！';
			}
		unset($_POST['Password1']);		
		unset($_POST['Password2']);
		unset($_POST['Password']);	
	}

		 if(($_POST['Theme']!=$_SESSION['Theme']) ||($_POST['Email']!=$_SESSION['UserEmail'])){
			$sql = "UPDATE www_users SET 
					
						email='" . $_POST['Email'] ."',
					
						theme='" . $_POST['Theme'] . "'
				
					WHERE userid = '". $_SESSION['UserID']. "'";

				prnMsg( _('The selected user record has been updated'), 'success' );
			$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$ErrMsg,$DbgMsg);

		include('includes/session.php');
		unset($_POST['Theme']);
		unset($_POST['Email']);	

		}		
}
	if ($msg!=''){
				prnMsg($msg,'info');
     }

echo'	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';
/*if (isset($GLOBALS['CryptFunction']) && $PasswordVerified  ) {
			prnMsg('187旧密码输入错误！','info');
				switch ($GLOBALS['CryptFunction']) {
					case 'sha1':
						if ($myrow['password'] == sha1($_POST['Password'])) {
							$PasswordVerified = true;
						}
						break;
					case 'md5':
						if ($myrow['password'] == md5($_POST['Password'])) {
							$PasswordVerified = true;
						}
						break;
					default:
						if ($myrow['password'] == $_POST['Password']) {
							$PasswordVerified = true;
						}
				}*/
include('includes/footer.php');
?>
