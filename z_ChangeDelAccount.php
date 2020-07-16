
<?php
/* $Id: z_ChangeDelAccount.php   chengjiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-10-22 17:58:15 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-10-26 05:59:37
 */
/* Utility to change a GL account code in all webERP. 
    改变科目代码及发生业务，并删除chartmaster  `glaccountusers`中科目*/

include ('includes/session.php');
$Title = '更改科目代码并删除';// Screen identificator.
$ViewTopic = 'SpecialUtilities';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangeGLAccountCode';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title="',// Icon image.
	_('Change A GL Account Code'), '" /> ',// Icon title.
	_('Change A GL Account Code'), '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br />
    <table>
	<tr>
		<td>修改删除科目代码:</td>
		<td><input type="text" name="OldAccountCode" size="20" maxlength="20" /></td>
	</tr>
	<tr>
		<td>并入已存在的科目:</td>
		<td><input type="text" name="NewAccountCode" size="20" maxlength="20" /></td>
	</tr>
	</table>

		<input type="submit" name="ProcessGLAccountCode" value="' . _('Process') . '" />
	</div>';
if(isset($_POST['ProcessGLAccountCode'])) {

	$InputError =0;

	$_POST['NewAccountCode'] = mb_strtoupper($_POST['NewAccountCode']);

	/*First check the code exists */
	$result=DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'");
	if(DB_num_rows($result)==0) {
		prnMsg(_('The GL account code') . ': ' . $_POST['OldAccountCode'] . ' ' . _('does not currently exist as a GL account code in the system'),'error');
		$InputError =1;
	}

	if(ContainsIllegalCharacters($_POST['NewAccountCode'])) {
		prnMsg(_('The new GL account code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if($_POST['NewAccountCode']=='' OR  $_POST['OldAccountCode']=='') {
		prnMsg(_('The new GL account code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}


	/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['NewAccountCode'] . "'");
	if(DB_num_rows($result)==0) {
		echo '<br /><br />';
		prnMsg(_('The replacement GL account code') . ': ' . $_POST['NewAccountCode'] . ' ' . _('already exists as a GL account code in the system') . ' - ' . _('a unique GL account code must be entered for the new code'),'error');
		$InputError =1;
	}


	if($InputError ==0) {// no input errors
		$result = DB_Txn_Begin();
		echo '<br />' . _('Adding the new chartmaster record');
		/*$sql = "INSERT INTO chartmaster (accountcode,
										accountname,
										group_,crtdate)
				SELECT '" . $_POST['NewAccountCode'] . "',
					accountname,
					group_,'".date("Y-m-d")."'
				FROM chartmaster
				WHERE accountcode='" . $_POST['OldAccountCode'] . "'";*/
		$sql = "DELETE FROM chartmaster
				WHERE accountcode='" . $_POST['OldAccountCode'] . "'";		

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to insert the new chartmaster record failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');
        $sql="DELETE FROM `glaccountusers` WHERE accountcode='" . $_POST['OldAccountCode'] . "'";
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');
	
		DB_IgnoreForeignKeys();

		ChangeFieldInTable("bankaccounts", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("bankaccountusers", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

     	ChangeFieldInTable("banktrans", "bankact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		//ChangeFieldInTable("chartdetails", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("cogsglpostings", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("companies", "debtorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "pytdiscountact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "creditorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "payrollact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "grnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "exchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "purchasesexchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "retainedearnings", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "freightact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("fixedassetcategories", "costact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "depnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "disposalact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "accumdepnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("glaccountusers", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		
		ChangeFieldInTable("gltrans", "account", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("lastcostrollup", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("lastcostrollup", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("locations", "glaccountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);// Location's ledger account.

		ChangeFieldInTable("pcexpenses", "glaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("pctabs", "glaccountassignment", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("pctabs", "glaccountpcash", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("purchorderdetails", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("salesglpostings", "discountglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("salesglpostings", "salesglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("stockcategory", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "issueglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "purchpricecode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		//ChangeFieldInTable("stockcategory", "materialuseagevarac", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "wipact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("taxauthorities", "taxglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("taxauthorities", "purchtaxglaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("taxauthorities", "bankacctype", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("workcentres", "overheadrecoveryact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		
		ChangeFieldInTable("registername", "account", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("accountsubject", "subject", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("registeraccount", "subject", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		DB_ReinstateForeignKeys();

		$result = DB_Txn_Commit();

		echo '<br />' . _('Deleting the old chartmaster record');
		$sql = "DELETE FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'";
		$ErrMsg = _('The SQL to delete the old chartmaster record failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<p>' . _('GL account Code') . ': ' . $_POST['OldAccountCode'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewAccountCode'];
	}//only do the stuff above if  $InputError==0

}


echo'	</form>';

include('includes/footer.php');
?>