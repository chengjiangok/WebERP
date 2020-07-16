<?php
/* $Id: SMTPServer.php 4469 2011-01-15 02:28:37Z daintree $*/
/* This script is <create a description for script table>. */

include('includes/session.php');
$Title ='移动办公_钉钉维护';// Screen identification.
$ViewTopic = 'CreatingNewSystem';// Filename's id in ManualContents.php's TOC.
$BookMark = 'SMTPServer';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/email.png" title="' .// Icon image.
	$Title. '" /> ' .// Icon title.
	$Title . '</p>';// Page title.
// First check if there are smtp server data or not


if (isset($_POST['submit']) AND $_POST['MailServerSetting']==1) {//If there are already data setup, Update the table
	
}



		

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	<div>
	<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	<input type="hidden" name="MailServerSetting" value="' . $MailServerSetting . '" />
	<table class="selection">
	<tr>
		<td>AppKey</td>
		<td><input type="text" name="Host" required="required" value="' . $myrow['host'] . '" /></td>
	</tr>

	<tr>
		<td>AppSecret</td>
		<td><input type="text" name="HeloAddress" value="' . $myrow['heloaddress'] . '" /></td>
	</tr>

	<tr>
		<td>' . _('Authorisation Required') . '</td>
		<td><select name="Auth">';
if ($myrow['auth']==1) {
	echo '<option selected="selected" value="1">' . _('True') . '</option>';
	echo '<option value="0">' . _('False') . '</option>';
} else {
	echo '<option value="1">' . _('True') . '</option>';
	echo '<option selected="selected" value="0">' . _('False') . '</option>';
}
echo '</select></td>
	</tr>
	<tr>
		<td>' . _('User Name') . '</td>
		<td><input type="text" required="required" name="UserName" size="50" maxlength="50" value="' . $myrow['username']  .'" /></td>
	</tr>
	<tr>
		<td>' . _('Password') . '</td>
		<td><input type="password" required="required" name="Password" value="' . $myrow['password'] . '" /></td>
	</tr>
	<tr>
		<td>' . _('Timeout (seconds)') . '</td>
		<td><input type="text" size="5" name="Timeout" class="number" value="' . $myrow['timeout'] . '" /></td>
	</tr>
	<tr>
		<td colspan="2"><div class="centre"><input type="submit" name="submit" value="' . _('Update') . '" /></div></td>
	</tr>
	</table>
	</div>
	</form>';

include('includes/footer.php');

?>
