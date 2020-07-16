
<?php
/* $Id: SelectGLAccount.php  chengjiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-12-11 22:59:36 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-12-11 23:03:09
 */
include('includes/session.php');

$Title = _('Search GL Accounts');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
include('includes/header.php');

$msg='';
unset($result);
if (isset($_POST['Account'])){
	$_POST['GLCode'] = substr( $_POST['Account'],0,4);
} elseif (isset($_GET['Account'])){
	$_POST['GLCode'] = substr( $_POST['Account'],0,4);
}
if (isset($_GET['Act'])){
	$_POST['GLCode']=$_GET['Act'];
	$_SESSION['Act'] = $_GET['Act'];
}
if (!isset($LAccount)){ 
	$SQL="SELECT `account` FROM `accountstyle` WHERE mode=1";
	$Result=DB_query($SQL);
	while ($row=DB_fetch_array($Result)){
		if (!in_array(substr($row['account'],0,4),$LAct)){
			$LAct[]=substr($row['account'],0,4);
		}
		$LAccount[$row['account']]=substr($row['account'],0,4);
	}
}
//print_r($LAccount);
//$RootPath = dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8'));
if (isset($_POST['Search']) OR isset($_POST['Account'])||isset($_GET['Act'])){

	if (mb_strlen($_POST['Keywords']>0) AND mb_strlen($_POST['GLCode'])>0) {
		$msg=_('Account name keywords have been used in preference to the account code extract entered');
	}
	if ($_POST['Keywords']=='' AND $_POST['GLCode']=='') {
		/*
    $SQL="SELECT t3.accountname, t3.accountcode,t3.group_,t3.currcode  FROM chartmaster t3 WHERE t3.accountcode not in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
		  ( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) order by t3.accountcode";   */
		  $SQL="SELECT `accountcode`, `accountname`, `group_`, `currcode`, `tag` FROM `chartmaster` WHERE accountcode IN (SELECT `account` FROM `accountstyle` )"  ; 
    }
	elseif (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			$SQL ="SELECT t3.accountname, t3.accountcode,t3.group_,t3.currcode FROM chartmaster t3 WHERE (t3.accountcode not in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS ( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))) and t3.accountname  " . LIKE  . " '$SearchString' order by t3.accountcode";
		
	} elseif (mb_strlen($_POST['GLCode'])>0){
		$SQL="SELECT t3.accountname, t3.accountcode,t3.group_,t3.currcode  
		FROM chartmaster t3  WHERE 1 ";
		if (isset($LAccount[$_POST['GLCode']])){
			$SQL.=" AND t3.accountcode  " . LIKE  . "  '" . $_POST['GLCode'] . "%' " ;
		}else{
			$SQL.=" AND t3.accountcode  " . LIKE  . "  '" . $_POST['GLCode'] . "_%' " ;
		}
		/*
        if (isset($LAccount[$_POST['GLCode']])){//in_array($LAct, $_POST['GLCode'] )){
			$SL ="SELECT T.accountname, T.accountcode,T.group_,T.currcode  
			FROM chartmaster T WHERE T.accountcode='" . $_POST['GLCode']."'
			UNION ";
		}else{
			$SL=" ";
		}
		$SQL=$SL." SELECT t3.accountname, t3.accountcode,t3.group_,t3.currcode  
		        FROM chartmaster t3 
				WHERE (t3.accountcode NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 OR
				         EXISTS ( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 
						          AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )))
					AND t3.accountcode  " . LIKE  . "  '" . $_POST['GLCode'] . "%' " ;*/
	}
	
		if (isset($SQL) and $SQL!=''){
			$result = DB_query($SQL);
			$rw=DB_num_rows($result);
			
			if ($rw>0)
			if($_POST['GLCode']!=''){
				if ($_SESSION['Act']!=$_POST['GLCode']){
					$_SESSION['Act']=$_POST['GLCode'];
					//prnMsg($_POST['GLCode']);
				}
			}else{
				unset($_SESSION['Act']);
			//$row=DB_fetch_assoc($result);
			
				//$_SESSION['Act']=substr($row['accountcode'],0,4);
				
			}
		}
	
} //end of if search

//prnMsg($SQL."<br/>".$_POST['GLCode']);

if (!isset($AccountID) OR isset($_GET['Account'])) {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for General Ledger Accounts') . '</p>
		<br />
		<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	if(mb_strlen($msg)>1){
		prnMsg($msg,'info');
	}

	echo '<table class="selection">
		<tr>
			<td>' . _('Enter extract of text in the Account name') .':</td>
			<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>
			<td><b>' .  _('OR') . '</b></td>';
		
	
			$ResultSelection=DB_query("SELECT `accountcode`, `accountname`, `group_`, `currcode`, `tag` FROM `chartmaster` WHERE accountcode IN (SELECT `account` FROM `accountstyle` )");
				//"SELECT t.accountname, t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS                     ( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ) ORDER BY t.accountcode");
	echo '<td><select name="GLCode">';
	echo '<option value="">' . _('Select an Account Code') . '</option>';
	while ($MyRowSelection=DB_fetch_array($ResultSelection)){
		$cout=false;
		if (in_array(substr($MyRowSelection['accountcode'],0,4),$LAct)){
             if (isset($LAccount[$MyRowSelection['accountcode']])){
				$cout=true;
			 }
		}else{
			$cout=true;
		}
		if ($cout){
			if (isset($_POST['GLCode']) and $_POST['GLCode']==$MyRowSelection['accountcode']){
				echo '<option selected="selected" value="' . $MyRowSelection['accountcode'] . '">' . $MyRowSelection['accountcode'].' - ' .htmlspecialchars($MyRowSelection['accountname'], ENT_QUOTES,'UTF-8', false) . '</option>';
			} else {
				echo '<option value="' . $MyRowSelection['accountcode'] . '">' . $MyRowSelection['accountcode'].' - ' .htmlspecialchars($MyRowSelection['accountname'], ENT_QUOTES,'UTF-8', false)  . '</option>';
			}
		}
	}
	echo '</select></td>';

	echo '	</tr>
		</table>
		<br />';
		
	echo '<div class="centre">
			<input type="submit" name="Search" value="' . _('Search Now') . '" />
			<input type="submit" name="reset" value="' . _('Reset') .'" />
		</div>';

	if (isset($result) and DB_num_rows($result)>0) {
        //echo   $_POST['GLCode'];
		echo '<br /><table class="selection">';

		$TableHeader = '<tr>
							<th>' . _('Code') . '</th>
							<th>' . _('Account Name') . '</th>
							<th>' . _('Group') . '</th>						
							<th>' . _('Inquiry') . '</th>						
						</tr>';
		echo $TableHeader;

		$j = 1;

		while ($myrow=DB_fetch_array($result)) {
			if (isset($LAccount[$myrow['accountcode']])){
                 $LA="GLCostActInquiry.php?show=SGLL";
			}else{
				$LA="GLAccountInquiry.php?show=SGLA";
			}
			printf(
				'<tr '.($r==0?'class="EvenTableRows" >':'class="OddTableRows" >').
				   '<td>%s</td>
					<td>%s</td>
					<td>%s</td>					
					<td><a href="%s/'.$LA.'&amp;acp=%s"><img src="%s/css/%s/images/magnifier.png" title="' . _('Inquiry') . '" alt="' . _('Inquiry') . '" /></td>				
					</tr>',
					htmlspecialchars($myrow['accountcode'],ENT_QUOTES,'UTF-8',false),
					htmlspecialchars($myrow['accountname'],ENT_QUOTES,'UTF-8',false),
					$myrow['group_'],
					
					$RootPath,
					urlencode($myrow['accountcode'].'^'.$myrow['currcode']),
					$RootPath,
					$Theme);
					if ($r==0){
						$r=1;
					}else{
						
						$r=0;
					}
				

			$j++;
		
		}
	//end of while loop
	//	<td><a href="%s/GLAccounts.php?SelectedAccount=%s"><img src="%s/css/%s/images/maintenance.png" title="' . _('Edit') . '" alt="' . _('Edit') . '" /></a>
		echo '</table>';

	}
	//end if results to show

	echo '</div>
          </form>';

} //end AccountID already selected

include('includes/footer.php');
?>