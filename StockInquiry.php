
<?php

include('includes/session.php');

$Title ='库存账簿查询';
/* webERP manual links before header.php */
$ViewTopic= 'AccountsPayable';
$BookMark = 'SupplierInvoice';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (isset( $_POST['ClearAccount'])){
	$ClearAccount=$_POST['ClearAccount'];
}else{
	$_POST['ClearAccount']=1;
}
if (isset( $_POST['OpenAccount'])){
	$OpenAccount=$_POST['OpenAccount'];
}else{
	$_POST['OpenAccount']=1;

}
if (isset( $_POST['Purch'])){
	$Purch=$_POST['Purch'];
}else{
	$_POST['Purch']=1;
}
if (isset( $_POST['Issue'])){
	$Issue=$_POST['Issue'];
}else{
	$_POST['Issue']=1;
	//$Issue=2;
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (isset($_POST['Select'])) { /*User has hit the button selecting a supplier */
	$_SESSION['SupplierID'] = $_POST['Select'];
	unset($_POST['Select']);
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
	unset($_POST['Search']);
	unset($_POST['Go']);
	unset($_POST['Next']);
	unset($_POST['Previous']);
}
if (!isset($_POST['BeforDate'])){
	$_POST['BeforDate']=date("Y-m-d");
}
if (!isset($_POST['AfterDate'])){
	$_POST['AfterDate']=date('Y-m-01');
	//$_POST['AfterDate']=date('Y-m-01',strtotime('-1 month'));;
}
echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
$Title . ': ' . $SupplierName . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">
	<tr>
		<td>' . _('Account').':</td>
		<td><select name="Account">';

			$sql="SELECT accountname, accountcode  FROM chartmaster WHERE accountcode  like'".$SelectedAccount."%'";
$result = DB_query($sql);
while ($myrow=DB_fetch_array($result,$db)){
if($myrow['accountcode'] == $SelectedAccount){
	echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
} else {
	echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . $myrow['currcode'] .' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
}
}
echo '</select></td>
</tr>';

echo '<tr>
	<td>' . _('For Period range').':</td>
	<td>
		<select name="period[]" size="12" multiple="multiple">';
$sql = "SELECT periodno, lastdate_in_period 
		 FROM periods 
		 where periodno>=".$_SESSION['startperiod']. "  AND periodno<=".$_SESSION['period']. " 
		 ORDER BY periodno DESC";
$result = DB_query($sql);
while ($myrow=DB_fetch_array($result,$db)){
	if (isset($FirstPeriodSelected) AND $myrow['periodno'] >= $FirstPeriodSelected AND $myrow['periodno'] <= $LastPeriodSelected) {
		echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
	} else {
		echo '<option value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
	}
}
echo '</select></td></tr>
</table>
<br />
<div class="centre">
	<input type="submit" name="Show" value="'._('Show Account Transactions').'" />
	<input type="submit" name="CSV" value="导出CSV" /><br/>';
	if (isset($_SESSION['Act'])){
		$actstr='?Act='.$_SESSION['Act'];
	}elseif($_POST['show']=='SGLA'){
		$actstr="";
	}
	echo'<a href="' . $RootPath . '/InventoryQuantities.php'.$actstr.'">返回账簿查询</a>';
echo'</div>
</div>
</form>';

		$sql="SELECT stkmoveno,
				
					connectid,
					a.stockid,
					description,
					units,
					decimalplaces,
					type,
					loccode,
					accountdate,
					transno,
					trandate,
					userid,
					price,
					prd,
					reference,
					branchcode,
					qty,
					discountpercent,
					standardcost,
					show_on_inv_crds,
					newqoh,
					newamount,
					hidemovt,
					narrative,
					issuetab
				FROM  stockmoves a
				LEFT JOIN stockmaster b ON	a.stockid = b.stockid
				WHERE type IN (28, 39) 
				      AND DATE_FORMAT(trandate,'%Y-%m-%d')>='".$_POST['AfterDate']."'
					  AND DATE_FORMAT(trandate,'%Y-%m-%d')<='".$_POST['BeforDate']."'
					  AND loccode='".$_POST['StockCat']."'";
				
		
	
		if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$sql.=" AND CONCAT(d.description,d.longdescription) " . LIKE . " '$SearchString'";
		}
		if (isset($_POST['StockCode']) AND mb_strlen($_POST['StockCode'])>0) {
			$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
			$sql.=" AND a.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'";
		}
	
		if (isset($_POST['PurchNo']) AND mb_strlen($_POST['PurchNo'])>0) {
			if (stripos($_POST['PurchNo'],',')>0){
				$sql.=" AND transno IN (".$_POST['PurchNo'].")";
			}else if (stripos($_POST['PurchNo'],'-')>0){
				$purchno=explode('-',$_POST['PurchNo']);
				$sql.=" AND transno >=".$purchno[0]." AND transno<= ".$purchno[count($purchno)-1];
			
			}else if (stripos($_POST['PurchNo'],'~')>0){
				$purchno=explode('~',$_POST['PurchNo']);
				$sql.=" AND transno >=".$purchno[0]." AND transno<= ".$purchno[count($purchno)-1];
			
			}else{
				$sql.=" AND transno ='".$_POST['PurchNo']."'";
			}				
		}
	
		$sql.=" ORDER BY type, connectid,transno,a.stockid";

		$result = DB_query($sql);
		

	//prnMsg('发料查询');
		echo '<table width="90%" cellpadding="4"  class="selection">
			<tr>
				<th >序号</th>				
				<th >项目编码/名称</th>
				<th >发放单号</th>
				<th >发料日期</th>			
				<th >物料编码/名称</th>
				<th >单位</th>
				<th >发放数量</th>
				<th >成本价格</th>
				<th >成本金额</th>			
				<th >备注</th>
			</tr>';
			$RowIndex=1;
			$k=0;			
			$rw=0;
			$IssueType=0;
			$conid=0;
			$Subtotal=0;
			$Total=0;

	while($row=DB_fetch_array($result)){
	
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		}else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}	
		if ((int)$row['type']==28){
			$type='生产发放:';	
			$IsType='W';		
			$SQL="SELECT `wo`, `wono`, a.`stockid`,description 
			       FROM `woitems` a
				   LEFT JOIN stockmaster b ON a.stockid =b.stockid
				   WHERE wo='".$row['connectid']."'";
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);
			$IssueName=$row['connectid'].'工单 '.$Row['stockid'].':'.$Row['description'];	   
		}elseif ((int)$row['type']==39){
			$type='易耗发放:';
			$IsType='Y';		
			$SQL="SELECT `departmentid`, `description`, `account`, `authoriser` 
			       FROM `departments`
					WHERE departmentid='".$row['connectid']."'";
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);	
			$IssueName='['.$row['connectid'].']'.$Row['description'];
		}	
		//$IssueName.$row['type']
		
		if ($IssueType!=$row['type']){//&&($conid!=$row['connectid'])){
			
			if ($rw>1){
				if (39==$IssueType){
					$TotalStr= '易耗品发料小计';
				}elseif ($IssueType==28){
					$TotalStr= '生产发料小计';
				}
				echo '
					<td></td>
					<td colspan="7">'.$TotalStr.'</td>				
					<td class="number">'.locale_number_format($Subtotal,$_SESSION['StandardCostDecimalPlaces']).'</td>
					<td ></td>
					<td ></td>
				</tr><tr>';
				$Subtotal=0;
			}
			$IssueType=$row['type'];
			echo'<td>'.$RowIndex.'</td>';
			echo'<td >'.$IssueName.':</td>';
			$conid=$row['connectid']; 
			//=$IssueName;
		
	//}elseif($IssueType==$row['type']&&$conid!=$row['connectid']){
			
			/*$rw=1;
			echo'<td>'.$RowIndex.'</td>';
			$conid=$row['connectid']; 
			$TotalStr=$IssueName;
			echo'<td >'.$IssueName.':</td>';

		}elseif ($IssueType!=$row['type']){
			$IssueType=$row['type'];
			if ($rw>1){
				echo '<td></td>
						<td colspan="4">'.$TotalStr.'小计</td>				
						<td class="number">'.locale_number_format($TotalAll,2).'</td>
						<td class="number">'.locale_number_format($TaxTotalAll,2).'</td>
						<td class="number">'.locale_number_format(($TotalAll+$TaxTotalAll),2).'</td>
						<td ></td>
						<td ></td>
					</tr><tr>';
			}
			$rw=1;
			$TotalStr=$IssueName;
			echo'<td>'.$RowIndex.'</td>';
			echo'<td >'.$IssueName.':</td>';	*/		
		}else{
			   $rw++;
			    echo'<td>'.$RowIndex.'</td>';
				echo '<td></td>';

		}
		
	
		if ($TransNo!=$row['transno']){
		
			echo'<td><a href="'.$RootPath . '/PDFIssueOrder.php?F='.$IsType.'&D=' . $row['transno'] . '&StockID='.$row['stockid'] .'" title="点击" target="_blank" >'.$type.$row['transno'].'</a></td>';
			$TransNo=$row['transno'];
		}else{
			echo'<td></td>';
		}
		echo'<td>'.$row['trandate'].'</td>';					
		echo'<td >'.str_pad($row['stockid'],16,'-',STR_PAD_RIGHT).$row['description'].'</td>
			<td >'.$row['units'].'</td>
			<td class="number">'.locale_number_format(-$row['qty'],$row['decimalplaces']).'</td>
			<td class="number">'.locale_number_format($row['price'],$_SESSION['StandardCostDecimalPlaces']).'</td>
			<td class="number">'.locale_number_format($row['price']*-$row['qty'],$_SESSION['StandardCostDecimalPlaces']).'</td>	
			<td >'.$row['narrative'].'</td>										
		</tr>';
		//if ($IssueType!=$row['type']){
	
	//}

		$Subtotal+=round($row['price']*-$row['qty'],$_SESSION['StandardCostDecimalPlaces']);
		$Total+=round($row['price']*-$row['qty'],$_SESSION['StandardCostDecimalPlaces']);
		$RowIndex++;	
	}//end while
	if ($rw>1){
		if (39==$IssueType){
			$TotalStr= '易耗品发料小计';
		}elseif ($IssueType==28){
			$TotalStr= '生产发料小计';
		}
		echo '<tr>
			<td></td>
			<td colspan="7">'.$TotalStr.'</td>				
			<td class="number">'.locale_number_format($Subtotal,$_SESSION['StandardCostDecimalPlaces']).'</td>
			<td ></td>
			<td ></td>
		</tr>';
	}
	echo '<tr>
			<td></td>
			<td colspan="7">总计</td>				
			<td class="number">'.locale_number_format($Total,$_SESSION['StandardCostDecimalPlaces']).'</td>
			<td ></td>
			<td ></td>
		</tr>';
		echo'</table>';			
echo '<br />
		<div class="centre">
			<input type="submit" name="Search" value="查询" />			
			<input type="submit" name="crtExcel" value="导出Excel" />		
		</div>';
include('includes/footer.php');
?>
