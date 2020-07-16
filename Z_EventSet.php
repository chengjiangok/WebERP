<?php
/* $Id: Z_EventSet.php 2017-06-12 chengjiang $*/
/*功能 关闭开启事件，修改事件启动时间
       功能没有完成*/
include('includes/session.php');
$Title ='事件设置';
$ViewTopic = 'EventSet';// Filename in ManualContents.php's TOC.
$BookMark = 'EventSet';// Anchor's id in the manual's html document.
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/group_add.png" title="' .// Title icon.
	_('Search') . '" />' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if (isset($_POST['submit'])) {

	$InputError = 0;

} elseif (isset($_GET['edit'])) {
}elseif (isset($_GET['stop'])) {
}

	$sql = "SELECT	`name`,
					`body`,
					`definer`,
					`execute_at`,
					`interval_value`,
					`interval_field`,
					`created`,
					`modified`,
					`last_executed`,
					`starts`,
					`ends`,
					`status`,
					`on_completion`,
					`sql_mode`,
					`comment`,
					`originator`
				FROM
					mysql.event
				WHERE
					db = 'erp'";
	$result = DB_query($sql);
   // prnMsg(DB_num_rows($result),'info');
	echo '<table class="selection">';
		echo '<tr>
			<th colspan="8">事件名称</th>
		</tr>';
	echo '<tr>
			<th>序号</th>
			<th>事件名称</th>
			<th>事件类别</th>
			<th>开始时间</th>
			<th>结束时间</th>
			<th>执行过程</th>
			<th></th>
			<th>状态</th>
				
		</tr>';

	$k = 0; //row colour counter
    $r=1;
	while ($MyRow = DB_fetch_array($result)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}

		

		printf('<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
		   		<td>%s</td>
				<td>%s</td>
				<td><a href="%s?edit=%s&amp;SelectedLocation=123" onclick="return confirm(\'' . _('Are you sure you wish to un-authorise this user?') . '\');">编辑</a></td>
				<td><a href="%s?stop=%s&amp;SelectedLocation=123" onclick="return confirm(\'' . _('Are you sure you wish to un-authorise this user?') . '\');">%s</a></td>

				</tr>',
				$r,
				$MyRow['name'],
				$MyRow['interval_field'],
				$MyRow['starts'],
				$MyRow['ends'],
				str_replace('CALL','',str_replace('END','',str_replace('BEGIN','',$MyRow['body']))),
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'),
				$MyRow['name'],
				htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'),
				$MyRow['name'],
				$MyRow['status']
				);
				$r++;			
	}
	//END WHILE LIST LOOP

	echo '</table><br />';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">';
		
echo '<tr>
		<td>事件名:</td>
		<td></td>';
echo '<tr>
				<td>事件类别:</td>
				<td><select name="SelectedLocation">';

			echo '<option value="1">日循环</option>';
	       	echo '<option value="2">星期循环</option>';
     		echo '<option value="3">月循环</option>';
	        echo '<option value="4">年度循环</option>';
			echo '</select></td></tr>';

echo '<tr>
		<td>开始时间</td>
		<td><input type="time" name="starttime" /></td>
	</tr>';
echo '<tr>
		<td>结束日期</td>
		<td><input type="date" name="enddate" /></td>
	</tr>';
echo '<tr>
		<td>状态</td>
		<td><input type="radio" name="status" value="1"/>
		    <input type="radio" name="status"  value="0"/></td>
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
