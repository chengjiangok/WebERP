
<?php
/* $Id: SettleAccounts.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-11-03 13:29:33 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-06-10 15:26:15
 * version20180610
 */

include('includes/session.php');

$Title ='结账设置';// Screen identificator.
$ViewTopic= 'GettingStarted';// Filename's id in ManualContents.php's TOC.
$BookMark = 'UserMaintenance';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/group_add.png" title="' .// Title icon.
	_('Search') . '" />' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
$SettleType=array(20=>"年度结账",30=>"发料结转",31=>"产品成本结转",32=>"销售成本结转");
$SQL="SELECT `accountcode`, `accountname` FROM `chartmaster` WHERE LEFT(accountcode,4) IN ('1403','1405','5001','6001','6401','2221','4103') AND length(accountcode)>4";
$Result=DB_query($SQL);
$ActName=array();
while($row=DB_fetch_array($Result)){
	$ActName[$row['accountcode']]=$row['accountname'];
}
echo '<br />';// Extra line after page_title_text.

if (isset($_POST['submit'])) {
	//initialise no input errors assumed initially before we test
	$InputError = 0;
	
} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button


	

}

if (!isset($SelectedUser)) {

	/* If its the first time the page has been displayed with no parameters then none of the above are true and the list of Users will be displayed with links to delete or edit each. These will call the same page again and allow update/input or deletion of the records*/

	$sql = "SELECT `confname`,
	                accountname,
					myconfig.`tag`,
					`itemtype`, `conftype`, `confvalue`, `notes` 
	           FROM `myconfig` 
			   LEFT JOIN chartmaster ON accountcode=confname
			   WHERE conftype>=20
			   ORDER BY tag,conftype,confname";
	$result = DB_query($sql);

	echo '<table class="selection">';
	echo '<tr><th>序号</th>
	            <th>分组</th>
				<th>结账类别</th>
				<th>结账科目/名称</th>
				<th>对应科目序号</th>
				<th>对应科目/名称</th>			
				<th>系数</th>
				<th>注释</th>						
				<th>&nbsp;</th>
				<th>&nbsp;</th>
			</tr>';

	$k=0; //row colour counter
     $IndexRow=1;
	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		echo'<td>'.$IndexRow.'</td>	
		<td>'.$_SESSION['CompanyRecord'][$myrow['tag']]['unitstab'].'</td>	
		<td>['.$myrow['confname'].']'.$myrow['accountname'].'</td>	
		<td>'.$SettleType[$myrow['conftype']].'</td>	
		<td></td>	
		<td>['.$myrow['confvalue'].']'.$ActName[$myrow['confvalue']].'</td>	
		<td></td>	
		<td>'.$myrow['notes'].'</td>	
		
		<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?&amp;SelectedUser=%s">' . _('Edit') . '</a></td>
		<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '?&amp;SelectedUser=%s&amp;delete=1" onclick="return confirm(\'' . _('Are you sure you wish to delete this user?') . '\');">' . _('Delete') . '</a></td>
		</tr>';		
					$IndexRow++;

	} //END WHILE LIST LOOP
	echo '</table><br />';
} //end of ifs and buts!

if (isset($SelectedUser)) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')  . '">' . _('Review Existing Users') . '</a></div><br />';
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';

echo '<tr>
		<td>单元分组:</td>
		<td>';
		SelectUnitsTag(2);
echo '</td>

    </tr>';

echo '<tr>
		<td>结账类别:</td>
		<td><select name="SettleType">';
    foreach($SettleType as  $key=>$val){
		if ($_POST['SettleType']==0){
			echo '<option selected="selected" value="'.$key.'">' . $val . '</option>';
		
		} else {
			echo '<option value="'.$key.'">' . $val . '</option>';
		}
	}
echo '</select></td></tr>';

echo '<tr>
		<td>结账科目:</td>
		<td><input type="text" name="SettleAct"  size="30" maxlength="40" value="' . $_POST['SettleAct'] .'" /></td>
		</tr>';

echo '<tr>
		<td>对应科目:</td>
		<td><textarea  name="SettleToAct"  title="" cols="40" rows="4"    placeholder="格式如:[{\'5001101\':\'0.45\',\'5001102\':\'0.33\',\'5001103\':\'0.37\'}]	">' . stripslashes($_POST['SettleToAct']) . '</textarea></td>
	</tr>';

echo '<tr>
		<td>注释:</td>
		<td><input type="text" name="Notes"  size="30" maxlength="40" value="' . $_POST['Notes'] .'" /></td>
	</tr>';



echo '<tr>
		<td>' . _('Status') . ':</td>
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
