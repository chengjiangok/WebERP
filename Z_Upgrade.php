
<?php
/* z_Upgrade.php
 * @Author: ChengJiang 
 * @Date: 2018-07-03 17:29:19 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-08-28 17:29:48
 */

 //$PageSecurity = 15;
include('includes/session.php');
$Title ='升级维护系统';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

$Agent=$_SERVER['HTTP_USER_AGENT']; 
/*
if ($_POST['DoUpgrade']) {
	PrnMsg($_POST['userfile'].'='.$HTTP_POST_FILES['userfile']['tmp_name']);
}elseif ($_POST['DoLinuxCommand']) {
	prnMsg($_POST['LinuxCommand'].PHP_OS);
}*/
//prnMsg($_POST['DoLinuxCommand'].'DoLinuxCommand');
$comdatabase='information_schema';
$db1 = mysqli_connect($host , $DBUser, $DBPassword, $comdatabase, $mysqlport);
mysqli_set_charset($db1, 'utf8');
$db0 = mysqli_connect($host , $DBUser, $DBPassword, 'erp_gjw', $mysqlport);
mysqli_set_charset($db0, 'utf8');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		$Title. '"/> ' .// Icon title.
		$Title . '</p>';// Page titl
echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">';

//echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	prnMsg('此模块使用Linux命令对服务器进行设置或使用脚本进行版本升级和修改以修改数据库，请谨慎使用。','info');
echo '<table class="selection">';
echo '<tr>
		<td>选择数据库:</td>
		<td>
		    <select name="Database[]" size="12" multiple="multiple">';
	$SQL="SELECT `DATABASE_NAME`, `UPDATE_TIME`, `flag`, `notes` FROM `databasename` WHERE 1";
			
   
   $Result= mysqli_query($db0,$SQL);
   if (empty($DatabaseName)){ 
		while ($row = mysqli_fetch_array($Result)){
			$DatabaseName[]=$row['DATABASE_NAME'];
		}	
	}
	mysqli_data_seek($Result,0);
	while ($myrow=DB_fetch_array($Result,$db)){
		if (in_array($myrow['DATABASE_NAME'],$DatabaseName)) {
			echo '<option selected="selected" value="' . $myrow['DATABASE_NAME'] . '">' . $myrow['DATABASE_NAME'] . '</option>';
		} else {
			echo '<option value="' . $myrow['DATABASE_NAME'] . '">' . $myrow['DATABASE_NAME'] . '</option>';
		}
	}
echo '</select></td></tr>';
echo '<tr>
<td>查询方式:</td>
<td>
  <input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' />全部  
  <input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />对账
</td>
</tr>';
echo'<tr>
		<td>脚本:</td>
		<td><input name="LinuxCommand" type="text" size=50  rows="5" cols="50"/></td>
		<td ></td>
		</tr>';
echo'<br>';
/*
echo '<tr>
	   <td>输入LInux命令:</td>
	   <td><input name="LinuxCommand" type="text" size=50 /></td>
	   <td></td>
		<input type="submit" name="DoLinuxCommand"  value="执行Linux命令" />	
	   </tr>';*/
//	echo '<div class="centre"><input type="submit" name="DoUpgrade" value="' . _('Perform Upgrade') . '" /></div>';
echo'</table><br/>';
	echo '<div class="centre">
		<input type="submit" name="Search" value="查询" />
		<input type="submit" name="DoUpgrade" value="执行脚本" />';
	
		if(isset($_POST['Search8']) ) {
		echo'<br/><br/><input type="submit" name="CheckSave" value="对账确认" />';
		}
	echo'</div>';
	echo	($_POST['Scripts']);
if (isset($_POST['Search'])){
		print_r($_POST['Database']);
		prnMsg($_POST['Scripts']);

}elseif (isset($_POST['DoLinuxCommand']) && $_POST['DoLinuxCommand'] =='执行Linux命令'){
	$a = system($_POST['DoLinuxCommand'],$out);
	if ($out==0){
		prnMsg('命令执行成功!<br>'.$a,'info');
	}else{
		prnMsg($_POST['DoLinuxCommand'].'执行失败！','info');
	}
}elseif (isset($_POST['DoUpgrade'])){//} && $_POST['DoUpgrade'] =="执行脚本"){
	foreach($_POST['Database'] as $val){
            echo $val."<br/>";
	
	 	
			
			$sql ="USE ".$val." ;". $_POST['LinuxCommand'];
			$InAFunction = false;
			echo '<br /><table>';
	
					//if (mb_strpos($SQLScriptFile[$i],';')>0 AND ! $InAFunction){
						//$sql = mb_substr($sql,0,mb_strlen($sql)-1);
						$result =mysqli_query($db0,$sql);// DB_query($sql, $ErrMsg, $DBMsg, false, false);
						echo  $sql."<br/>";
						switch (mysqli_error()) {
							case 0:
								echo '<tr><td>' . $comment . '</td><td style="background-color:green">' . _('Success') . '</td></tr>';
								break;
							case 1050:
								echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
									_('Table has already been created') . '</td></tr>';
								break;
							case 1060:
								echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
									_('Column has already been created') . '</td></tr>';
								break;
							case 1061:
								echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
									_('Index already exists') . '</td></tr>';
								break;
							case 1062:
								echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
									_('Entry has already been done') . '</td></tr>';
								break;
							case 1068:
								echo '<tr><td>' . $comment . '</td><td style="background-color:yellow">' . _('Note').' - '.
									_('Primary key already exists') . '</td></tr>';
								break;
							default:
								echo '<tr><td>' . $comment . '</td><td style="background-color:red">' . _('Failure').' - '.
									_('Error number').' - '.DB_error_no()  . '</td></tr>';
								break;
						}
						unset($sql);
					

				 //end if its a valid sql line not a comment
				
			} //end of for loop around the lines of the sql script
			echo '</table>';
	
	
}

echo'</div></form>';
include('includes/footer.php');
?>
