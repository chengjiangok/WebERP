
<?php
/* $Id: AccountUnits.php 49242017-01-17 07:59:13 ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-02-13 09:28:32 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-10-25 05:20:22
 * 有问题按钮下功能不能使用
 */

include('includes/session.php');
$Title = '已录凭证科目校验';
/* Manual links before header.php */
$ViewTopic= 'GeneralLedger';// Filename in ManualContents.php's TOC.
$BookMark = 'GLAccounts';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		_('General Ledger Accounts') . '" />' . ' ' . $Title . '</p>';
echo '<form method="post" id="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
  echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

    	echo '<div class="page_help_text">
		            检验已经录入会计凭证的科目是否存在于科目表中，显示对应科目代码名称！
			</div>';        
	echo'<br />
		<div class="centre">		
			<input type="submit" name="accountname" value="科目名称查询" />
			<input type="submit" name="acc" value="显示科目" />';
				
		
	echo'	</div>';
   
	$sql='SELECT DISTINCT account FROM `gltrans` WHERE account NOT IN(SELECT accountcode FROM chartmaster)';
	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$ErrMsg);
    if (DB_num_rows($result)>0){
	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Account Code') . '</th>
			
		</tr>';

	$k=0; //row colour counter
   $accarr=array();
	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}


	printf("<td>%s</td>		
		</tr>",
		$myrow[0]);
			}
	echo '</table>';
	}else{
		prnMsg('已录入凭证科目正确！','info');

}
if(isset($_POST['accountname'])){
	 $sql="SELECT `accountcode` acccode, `accountname` accname, `tag` FROM `chartmaster` WHERE LEFT(accountcode,4) ='".$_POST['accunit']."' AND   accountcode NOT IN (SELECT `account` FROM `accountunits` WHERE unittype=".substr($_POST['accunit'],0,1).")";
   $result = DB_query($sql);
	
	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Account Code') . '</th>
			<th class="ascending">' . _('Account Name') . '</th>		
		</tr>';
 echo '<tr>     			
  		<td><select name="acccode" size="20" >';
  		$k=0;  
  		while ($myrow=DB_fetch_array($result,$db)){	
			echo '<option value="';			
			echo $myrow['acccode'] . '">' .$myrow['acccode'].'^'.$myrow['accname']  . '</option>'; 
		}
		 echo'</select>
		 </td>';	
		if ($_POST['accunit']==1122) {
			$sql="SELECT debtorno unitcode,branchcode,  `brname` unitname FROM `custbranch` WHERE concat(debtorno,branchcode)  NOT IN (SELECT  concat(unitscode,branchcode) FROM `accountunits` WHERE unittype=1 )";
		}else{
			$sql="SELECT supplierid unitcode,'' branchcode, suppname unitname FROM suppliers";  
		}
  
        $result = DB_query($sql);	
		echo'<td><select name="unitcode" size="20" >';
 
  		while ($myrow=DB_fetch_array($result,$db)){	
			echo '<option value="';		
			echo $myrow['unitcode'] .'^'.$myrow['branchcode'] .  '">' .$myrow['unitcode'].'^'.$myrow['unitname']  . '</option>'; 
		}
		 echo'</select>
		 </td>	
		</tr>';
		echo '</table>'; 	
}

echo'</form><br/>';
include('includes/footer.php');
?>