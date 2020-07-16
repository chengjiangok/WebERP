<?php
/* $Id: WorkOrderStatus.php  $*/

include('includes/session.php');
$Title = _('Work Order Status Inquiry');
include('includes/header.php');

if (isset($_GET['WO'])) {
	$SelectedWO = $_GET['WO'];
	$WoStock="WO=".$_GET['WO'];
} elseif (isset($_POST['WO'])){
	$SelectedWO = $_POST['WO'];
	$WoStock="WO=".$_POST['WO'];
} else {
	unset($SelectedWO);
}
if (isset($_GET['StockID'])) {
	$StockID = $_GET['StockID'];
	$WoStock.="&StockID=".$_GET['StockID'];
} elseif (isset($_POST['StockID'])){
	$WoStock.="&StockID=".$_POST['StockID'];
	$StockID = $_POST['StockID'];
} else {
	unset($StockID);
}


$ErrMsg = _('Could not retrieve the details of the selected work order item');
$WOResult = DB_query("SELECT  locations.locationname,
							 workorders.requiredby,
							 workorders.startdate,
							 workorders.closed,
							 stockmaster.description,
							 stockmaster.decimalplaces,
							 stockmaster.units,
							 woitems.qtyreqd,
							 woitems.qtyrecd,
							 stockmaster.categoryid loccode
						FROM workorders 
					
						INNER JOIN woitems
						ON workorders.wo=woitems.wo
						INNER JOIN stockmaster
						ON woitems.stockid=stockmaster.stockid
						INNER JOIN locations
						ON stockmaster.categoryid =locations.loccode
					
						WHERE woitems.stockid='" . $StockID . "'
						AND woitems.wo ='" . $SelectedWO . "'",
						$ErrMsg);
						//	INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.php');
	exit;
}
$WORow = DB_fetch_array($WOResult);
$SQL="SELECT   `component`,`units`, `quantity` FROM `bom` WHERE `parent`='" . $StockID . "'";
$result=DB_query($SQL);
$bomcount=DB_num_rows($result);

echo '<a href="'. $RootPath . '/SelectWorkOrder.php">' . _('Back to Work Orders'). '</a><br />';
//echo '<a href="'. $RootPath . '/WorkOrderCosting.php?WO=' .  $SelectedWO . '">' . _('Back to Costing'). '</a><br />';

echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' .
	_('Search') . '" alt="" />' . ' ' . $Title.'
	</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?'.$WoStock .  '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table cellpadding="2" class="selection">
	<tr>
		<td class="label">' . _('Work order Number') . ':</td>
		<td>' . $SelectedWO  . '</td>
		<td class="label">' . _('Item') . ':</td>
		<td>' . $StockID . ' - ' . $WORow['description'] . '</td>
	</tr>
 	<tr>
		<td class="label">' . _('Manufactured at') . ':</td>
		<td>' . $WORow['locationname'] . '</td>
		<td class="label">' . _('Required By') . ':</td>
		<td>' . ConvertSQLDate($WORow['requiredby']) . '</td>
	</tr>
 	<tr>
		<td class="label">' . _('Quantity Ordered') . ':</td>
		<td class="number">' . locale_number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
		<td colspan="2">' . $WORow['units'] . '</td>
	</tr>
 	<tr>
		<td class="label">' . _('Already Received') . ':</td>
		<td class="number">' . locale_number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
		<td colspan="2">' . $WORow['units'] . '</td>
	</tr>
	<tr>
		<td class="label">' . _('Start Date') . ':</td>
		<td>' . ConvertSQLDate($WORow['startdate']) . '</td>
		<td class="label">BOM:</td>
		<td >' . ($bomcount>0?$bomcount."种组件":"无BOM单" ). '</td>
	</tr>
	</table>
	<br />';
if (isset($_POST['CloseWorkOrder'])){

	$SQL="UPDATE `workorders` SET  `closed`=1 WHERE wo='".$SelectedWO."'";
	$result=DB_query($SQL);
	if ($result){
		prnMsg($SelectedWO.'工作单发料关闭!','success');
	}

}
	if ($bomcount>0){
	//set up options for selection of the item to be issued to the WO
	echo '<table class="selection">
			<tr>
				<th colspan="5"><h3>' . _('Material Requirements For this Work Order') . '</h3></th>
			</tr>';
	echo '<tr>
			<th colspan="2">' . _('Item') . '</th>
			<th>' . _('Qty Required') . '</th>
			<th>' . _('Qty Issued') . '</th>
		</tr>';

	$RequirmentsResult = DB_query("SELECT worequirements.stockid,
										stockmaster.description,
										stockmaster.decimalplaces,
										autoissue,
										qtypu
									FROM worequirements INNER JOIN stockmaster
									ON worequirements.stockid=stockmaster.stockid
									WHERE wo='" . $SelectedWO . "'
									AND worequirements.parentstockid='" . $StockID . "'");
		$IssuedAlreadyResult = DB_query("SELECT stockid,
						SUM(-qty) AS total
					FROM stockmoves
					WHERE stockmoves.type=28
					AND reference='".$SelectedWO."'
					GROUP BY stockid");
	while ($IssuedRow = DB_fetch_array($IssuedAlreadyResult)){
		$IssuedAlreadyRow[$IssuedRow['stockid']] = $IssuedRow['total'];
	}

	while ($RequirementsRow = DB_fetch_array($RequirmentsResult)){
		if ($RequirementsRow['autoissue']==0){
			echo '<tr>
					<td>' . _('Manual Issue') . '</td>
					<td>' . $RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description'] . '</td>';
		} else {
			echo '<tr>
					<td class="notavailable">' . _('Auto Issue') . '</td>
					<td class="notavailable">' .$RequirementsRow['stockid'] . ' - ' . $RequirementsRow['description']  . '</td>';
		}
		if (isset($IssuedAlreadyRow[$RequirementsRow['stockid']])){
			$Issued = $IssuedAlreadyRow[$RequirementsRow['stockid']];
			unset($IssuedAlreadyRow[$RequirementsRow['stockid']]);
		}else{
			$Issued = 0;
		}
		echo '<td class="number">'.locale_number_format($WORow['qtyreqd']*$RequirementsRow['qtypu'],$RequirementsRow['decimalplaces']).'</td>
			<td class="number">'.locale_number_format($Issued,$RequirementsRow['decimalplaces']).'</td></tr>';
	}
	/* Now do any additional issues of items not in the BOM */
	if(count($IssuedAlreadyRow)>0){
		$AdditionalStocks = implode("','",array_keys($IssuedAlreadyRow));
		$RequirementsSQL = "SELECT stockid,
						     description,
							decimalplaces
				FROM stockmaster WHERE stockid IN ('".$AdditionalStocks."')";
		$RequirementsResult = DB_query($RequirementsSQL);
			$AdditionalStocks = array();
			while($myrow = DB_fetch_array($RequirementsResult)){
				$AdditionalStocks[$myrow['stockid']]['description'] = $myrow['description'];
				$AdditionalStocks[$myrow['stockid']]['decimalplaces'] = $myrow['decimalplaces'];
			}
			foreach ($IssuedAlreadyRow as $StockID=>$Issued) {
			echo '<tr>
				<td>'._('Additional Issue').'</td>
				<td>'.$StockID . ' - '.$AdditionalStocks[$StockID]['description'].'</td>';
				echo '<td class="number">0</td>
					<td class="number">'.locale_number_format($Issued,$AdditionalStocks[$StockID]['decimalplaces']).'</td>
					</tr>';
			}
		}

	echo '</table>';
	}
	echo '<div class="centre"><input type="submit" name="CloseWorkOrder" value="关闭工作单" />
	            </div>';
	echo'</form>';
	include('includes/footer.php');

?>