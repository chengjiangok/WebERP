
<?php
/* $Id: GLAccountsTag.php   chengjiang $*/

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-05-04 16:37:39
 */
include('includes/session.php');
$Title = '科目分组维护';

$ViewTopic= 'MyTools';
$BookMark = 'GLAccountTag';
include('includes/header.php');

if(!isset($_POST['UnitsTag'])){
	$_POST['UnitsTag']=0;
}
foreach($_SESSION['CompanyRecord'] as $key=>$row)	{         
	if ($row['coycode']!=0){
		
	   $UnitsTag[$row['coycode']]=$row['unitstab'];
	   $UnitsTag[-$row['coycode']]=$row['unitstab']."内";     
	
	}
}
  echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		_('General Ledger Accounts') . '" />' . ' ' . $Title . '</p>';
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';       	
	echo '<table class="selection">';	   
    echo '<tr>
     	    <td>选择单元分组</td>
			  <td>';
			  SelectUnitsTag();		
			  echo'</td></tr>';  
	echo '</table>';
	echo '<div class="page_help_text">科目分组功能,是对多单元核算的需求而设置，如：内外账设置、部门简易核算，如果已经录入会计凭证，不要轻易改变科目所属分组!</div>';
	echo '<br />
	       <div class="centre">
	             <input type="submit" name="Show" value="显示查询" />	
	             <input type="submit" name="submit" value="保存更改" />';
    echo'</div>';
  
if (isset($_POST['submit'])) {	
	$resultstr = "";
	foreach( $_POST['chkbx'] as $vl){
 	
		if (strlen($vl)>4)
			$sql = "UPDATE chartmaster SET tag = '" . $_POST['tg'.$vl] . "'	WHERE accountcode = '" . $vl . "'";
		
			$ErrMsg = _('Could not update the account because');
			$result = DB_query($sql,$ErrMsg);
			$resultstr .=$vl.'<br>';
	}
       if($resultstr==''){ 
		 prnMsg( '你没有选择科目，所以不能更改！','info');
	   }else{
		prnMsg($resultstr.'科目分组更改成功！','info'); 
	   }


}elseif (isset($_POST['Show'])) {
   if ($_POST['UnitsTag']==0){
		$sql="SELECT t3.accountname, t3.accountcode,t3.tag FROM chartmaster t3 WHERE t3.accountcode not in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
	( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))  AND used<>-1 order by t3.accountcode";
     }else{
	$sql="SELECT t3.accountname, t3.accountcode,t3.tag FROM chartmaster t3 WHERE t3.accountcode not in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
	( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)>0 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and t3.tag = '".$_POST['UnitsTag']."' AND used<>-1 order by t3.accountcode";
	}
	$ErrMsg = _('Could not update the account because');
	$result = DB_query($sql,$ErrMsg);
	$k=0; //row colour counter
	if (Db_num_rows($result)>0){
		echo '<br /><table class="selection">';
		echo '<tr>
				<th class="ascending">' . _('Account Code') . '</th>
				<th class="ascending">' . _('Account Name') . '</th>
					<th class="ascending">单元</th>		
					<th class="ascending">选择</th>		
			</tr>';

	
		while ($myrow = DB_fetch_row($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			echo "<td>".$myrow[0]."</td>
				<td>".htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8')."</td>	
				<td>";
				
		
				echo'<select name="tg'.$myrow[1].'" size="1" >';
				foreach($UnitsTag as $key=>$row){
			
					if($key==$myrow[2] ){
						echo '<option selected="selected" value="';				
					} else {				
						echo '<option value="';
					}
					echo $key . '">' .$row  . '</option>'; 
				}
			echo'</select>';		
		
			echo "</td>	
			<td >
				<input type='checkbox' name='chkbx[]' value=".$myrow[1]." ></td></tr>";		
		
		}//END WHILE LIST LOOP
		echo '</table>';
	}else{
		prnMsg("没有查询到你选择的科目",'info');
	}
}
echo '</form>';
include('includes/footer.php');
?>