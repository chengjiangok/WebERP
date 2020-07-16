<?php

/* $Id: ReportType.php ChengJiang $*/

include('includes/session.php');
$Title ='报表类别';
include('includes/header.php');
if (isset($_GET['typ'])){
	$SelectTyp = $_GET['typ'];
}else{
	//exit;
}

if (isset($_POST['SelectedDT'])){
	$SelectedDT = trim(mb_strtoupper($_POST['SelectedDT']));
} elseif (isset($_GET['SelectedDT'])){
	$SelectedDT = trim(mb_strtoupper($_GET['SelectedDT']));
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
		_('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;
  
	if (mb_strlen($_POST['Description'])<3) {
		$InputError = 1;
		prnMsg('报表类别必须大于3个字符!','error');
	}

	if (isset($SelectedDT) AND $InputError !=1) {

		$sql = "UPDATE reporttype SET reportname = '" . $_POST['Description'] . "'
				WHERE reportid = '" . $SelectedDT . "'";
		$msg ='报表类别已经更新!';
	} elseif ($InputError !=1) {

		$sql = "INSERT INTO `reporttype`( `rpttype`, `reportname`, `flag`)
					VALUES ('" . $SelectTyp . "',
						'" . $_POST['Description'] . "',
						0)";
		$msg = '新的报表类别已经添加!';
	}
	//run the SQL from either of the above possibilites

	if ($InputError !=1){
		$result = DB_query($sql,'更新/添加报表类别失败!');
		prnMsg($msg,'success');
		echo '<br />';
		unset ($_POST['Description']);
		unset ($_POST['MRPDemandType']);
		unset ($SelectedDT);
	}

} elseif (isset($_GET['delete'])) {

	$sql= "SELECT COUNT(*) FROM `reportupload`
	         WHERE reportid='" . $SelectedDT . "'";
	        
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg('不能删除该分类因为已经上传了报表<br />' . _('There are') . ' ' . $myrow[0] . '份报表!' ,'warn');
    } else {
			$sql="DELETE FROM reporttype WHERE reportid='" . $SelectedDT . "'";
			$result = DB_query($sql);
			prnMsg('你选择的报表类别已经删除!','succes');
			echo '<br />';
	} // end of MRPDemands test
}

if (!isset($SelectedDT) or isset($_GET['delete'])) {

	$sql = "SELECT `reportid`,  `reportname` FROM `reporttype` WHERE rpttype='".$SelectTyp."'";

	$result = DB_query($sql);

	echo '<table class="selection">
			<tr>
				<th>' . _('Description') . '</th>
				<th colspan="2"></th>
			</tr>';

	while ($myrow = DB_fetch_row($result)) {

		printf('<tr>
		        <td>%s</td>
				<td><a href="%styp=%s&amp;SelectedDT=%s">' . _('Edit') . '</a></td>
				<td><a href="%styp=%s&amp;SelectedDT=%s&amp;delete=yes">' . _('Delete')  . '</a></td>
				</tr>',				
				$myrow[1],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$SelectTyp,
				$myrow[0],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$SelectTyp,
				$myrow[0]);
	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedDT) and !isset($_GET['delete'])) {
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?typ='.$SelectTyp.'">' . _('Show all Demand Types') . '</a></div>';
}

echo '<br /><form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?typ='.$SelectTyp.'">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedDT) and !isset($_GET['delete'])) {
	//editing an existing demand type

	$sql = "SELECT `reportid`,  `reportname` FROM `reporttype` WHERE rpttype='" . $SelectedDT . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);


	$_POST['Description'] = $myrow['description'];

	echo '<input type="hidden" name="SelectedDT" value="' . $SelectedDT . '" />';
	
} 

if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}

echo '<tr>
		<td>报表类别:</td>
		<td><input type="text" name="Description" size="31" maxlength="30" value="' . $_POST['Description'] . '" /></td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
	</div>
    </div>
	</form>';

include('includes/footer.php');
?>