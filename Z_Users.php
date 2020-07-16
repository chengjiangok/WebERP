<?php
/* $Id: ZT_Users.php 
/*
 * @Author: ChengJiang 
 * @Date: 2018-05-26 5:29:33 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-05-26 08:13:52
 
 */

include('includes/session.php');

$Title ='版本菜单维护';// Screen identificator.
$ViewTopic= 'GettingStarted';// Filename's id in ManualContents.php's TOC.
$BookMark = 'UserMaintenance';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/group_add.png" title="' .// Title icon.
	_('Search') . '" />' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['versionCode'])){
	$_POST['versionCode']='1111111010011';
}
echo '<br />';// Extra line after page_title_text.

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';
echo '<tr><th>' . _('User Login') . '</th>
			<th>' . _('Full Name') . '</th>
		
			<th>' . _('Security Role') . '</th>
			
			<th>'.$_SESSION['weberp'].'</th>
		
		</tr>';
		$SQL="SELECT `userid`, realname, secrolename,`modulesallowed`,a.fullaccess FROM `www_users` a LEFT JOIN securityroles b ON a.fullaccess=b.secroleid ";
		$Result=DB_query($SQL);
		$k=0;
		$MBarr=array();
		while ($myrow = DB_fetch_array($Result)) {
			if (strlen($myrow['userid'])<4){
				$MBarr[$myrow['userid']]=$myrow['modulesallowed'];
			}else{
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				printf('<td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>				
							</tr>',
							$myrow['userid'],
							$myrow['realname'],
							$myrow['secrolename'],
							$myrow['modulesallowed']	);
			}
	
		} //END WHILE LIST LOOP
		echo '</table><br />';
		//var_dump($MBarr[10]);

if ($_POST['check']){
	DB_data_seek($Result,0);
	$vcodearr=str_split($_SESSION['weberp'], 1);
	while ($myrow = DB_fetch_array($Result)) {
		if (strlen($myrow['userid'])>=4){
		$vstr='';
		$MB_arr=array();
		//角色编码数组
		$strarr=explode(',',substr($myrow['modulesallowed'],0,-1));
		//角色标准编码数组
		$MB_arr=explode(',',substr($MBarr[$myrow['fullaccess']],0,-1));
		//用版本编码改写标准编码
		for($i=0;$i<count($strarr);$i++){
			if ($vcodearr[$i]==0){
				$MB_arr[$i]=0;

			}
		}
		
		$vstr=implode(',', $MB_arr);
		//$sql="UPDATE `www_users` SET `modulesallowed`='".$vstr."' WHERE userid='".$myrow['userid']."'";
		//$result=DB_query($sql);
		echo $myrow['userid'].'...'.$vstr.'<br>';
		}
	
	}
	prnMsg('check');
}elseif ($_POST['update']){
	$sql="SELECT `confname`, `confvalue` FROM `config` WHERE confname='weberp'";
	$result=DB_query($sql);
	if (DB_num_rows($result)>0){
		$sql="UPDATE `config` SET `confvalue`='".$_POST['versionCode']."' WHERE `confname`='weberp'";
		$result=DB_query($sql);
	}else{
		$sql="INSERT INTO `config`(`confname`, `confvalue`) VALUES ('weberp','".$_POST['versionCode']."' )";
		$result=DB_query($sql);
	}
	prnMsg(	'版本编码插入成功!','info');
}elseif ($_POST['updatesave']){
	DB_data_seek($Result,0);
	$vcodearr=str_split($_SESSION['weberp'], 1);
	while ($myrow = DB_fetch_array($Result)) {
		if (strlen($myrow['userid'])>=4){
		$vstr='';
		$MB_arr=array();
		//角色编码数组
		$strarr=explode(',',substr($myrow['modulesallowed'],0,-1));
		//角色标准编码数组
		$MB_arr=explode(',',substr($MBarr[$myrow['fullaccess']],0,-1));
		//用版本编码改写标准编码
		for($i=0;$i<count($strarr);$i++){
			if ($vcodearr[$i]==0){
				$MB_arr[$i]=0;

			}
		}
		
		$vstr=implode(',', $MB_arr);
		//$sql="UPDATE `www_users` SET `modulesallowed`='".$vstr."' WHERE userid='".$myrow['userid']."'";
		//$result=DB_query($sql);
		echo $myrow['userid'].'...'.$vstr.'<br>';
	
		$sql="UPDATE `www_users` SET `modulesallowed`='".$vstr."' WHERE userid='".$myrow['userid']."'";
		$result=DB_query($sql);
		}
	
	}
}

echo '<tr>
		<td>版本编码:</td>
		<td><input type="text" name="versionCode" data-type="no-ilLegal-chars" title="' . _('If this user login is to be associated with a customer account a valid branch for the customer account must be entered.') . '" size="30" maxlength="30" value="' . $_POST['versionCode'] .'" /></td>
	</tr>';


echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="check" value="版本菜单检查" />
		<input type="submit" name="update" value="改写版本" />
		<input type="submit" name="updatesave" value="校验保存" />
	</div>
    </div>
	</form>';

include('includes/footer.php');
?>
