
<?php
/*
 * @Author: ChengJiang 
 * @Date: 2018-09-21 06:12:00 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-14 16:59:51
 */

include('includes/DefineSuppTransClass.php');
include('includes/DefinePOClass.php'); //needed for auto receiving code

/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');

$Title ='物料发放查询';
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
   // $maxdate=date("Y-m-d");
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';
	echo'<table cellpadding="3" class="selection">';
	echo'<tr>
			<td>选择查询日期</td>
			 <td collspan="2">
	          <input type="date"   alt="" min="'.substr($_SESSION['lastdate'],0,5).'01-01'.'" max="'.date("Y-m-d").'"  name="AfterDate" maxlength="10" size="12" value="' .  $_POST['AfterDate'] . '" />
			  <input type="date"   alt="" min="'.substr($_SESSION['lastdate'],0,5).'01-01'.'" max="'.date("Y-m-d").'"  name="BeforDate" maxlength="15" size="12" value="' . $_POST['BeforDate'] . '" />
			</td></tr>';
			$SQL = "SELECT categoryid, 
							categorydescription
						FROM stockcategory
						INNER JOIN locationusers ON locationusers.loccode = categoryid AND locationusers.userid = '".$_SESSION['UserID']."' AND locationusers.canupd = 1
						WHERE stocktype = 'B'
						ORDER BY categorydescription";
			$result = DB_query($SQL);
		echo'<tr>
				<td>' . _('In Stock Category') . ':</td>
				<td collspan="2">
				    <select name="StockCat">';
		if (!isset($_POST['StockCat'])) {
			$_POST['StockCat'] ='';
		}
		while ($myrow1 = DB_fetch_array($result)) {
			if ($myrow1['categoryid'] == $_POST['StockCat']) {
				echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			} else {
				echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			}
		}
		echo '</select>
				</td>
				</tr>';
		echo'<tr>
				<td>工作单号</td>
				<td > <input type="text" name="PurchNo" value="'.$_POST['PurchNo'].'"  > </td>
			</tr>';
		echo '<tr>					
			<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>
			<td>';
		if (isset($_POST['StockCode'])) {
			echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
		} else {
			echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
		}
		echo '</td></tr>';
		/*	
	echo'<tr>
	     <td>查询工作单状态</td>
		 <td collspan="2">
		     <input type="checkbox" name="OpenAccount" value="1" checked />发料中
			 <input type="checkbox" name="close" value="1" checked />关闭
			 <input type="checkbox" name="no" value="1" checked />无工作单
	     </td>
		 </tr>';
    */
	echo'</table>';	
	echo '<br />
		<div class="centre">
			<input type="submit" name="Search" value="查询" />			
			<input type="submit" name="crtExcel" value="导出Excel" />		
		</div>';
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
		
if (isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])	OR isset($_POST['Previous'])) {
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
}
/*
if (isset($_POST['Search'])){//&& (!$result)) {

	$ListCount = DB_num_rows($Result);
	//if ($ListCount>0){
	if(isset($_POST['CSV'])) {// producing a CSV file of customers
			$CSVListing ='"';
		$CSVListing .=iconv( "UTF-8", "gbk//TRANSLIT",'客户编码').'","'.iconv( "UTF-8", "gbk//TRANSLIT","客户名称").'","'.iconv( "UTF-8", "gbk//TRANSLIT","币种").'","'.iconv( "UTF-8", "gbk//TRANSLIT","地址").'","'.iconv( "UTF-8", "gbk//TRANSLIT","区县"). '","'.iconv( "UTF-8", "gbk//TRANSLIT","省市").'","'.iconv( "UTF-8", "gbk//TRANSLIT","银行账号").'","'.iconv( "UTF-8", "gbk//TRANSLIT","手机").'","'.iconv( "UTF-8", "gbk//TRANSLIT","Emai").'","'.iconv( "UTF-8", "gbk//TRANSLIT","URL"). '"'. "\n";
		while ($InventoryValn = DB_fetch_row($Result)) {
			$CSVListing .= '"';
			$CSVListing .= iconv( "UTF-8", "gbk//TRANSLIT",implode('","', $InventoryValn) ). '"' . "\n";
		}
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header('Content-disposition: attachment; filename='.iconv( "UTF-8", "gbk//TRANSLIT","供应商列表_") .  date('Y-m-d')  .'.csv');
		header("Pragma: public");
		header("Expires: 0");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $CSVListing;
		exit;
    }
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	if ($ListPageMax > 1) {
		echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': </p>';
		echo '<select name="PageOffset">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type="submit" name="Go" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '<br />';
	}
	echo '<input type="hidden" name="Search" value="' . _('Search Now') . '" />';
	echo '<br />
		<br />
		<br />
		<table cellpadding="2">';
	echo '<tr>
	  		<th class="ascending">' . _('Code') . '</th>
			<th class="ascending">' . _('Supplier Name') . '</th>
			<th class="ascending">' . _('Currency') . '</th>
			<th class="ascending">' . _('Address 1') . '</th>
			<th class="ascending">' . _('Address 2') . '</th>
			<th class="ascending">' . _('Address 3') . '</th>
			<th class="ascending">银行账号</th>
			<th class="ascending">' . _('Telephone') . '</th>
			<th class="ascending">' . _('Email') . '</th>
			<th class="ascending">' . _('URL') . '</th>
		</tr>';
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($Result) <> 0) {
		DB_data_seek($Result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	while (($myrow = DB_fetch_array($Result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k = 1;
		}
		echo '<td><input type="submit" name="Select" value="'.$myrow['supplierid'].'" /></td>
				<td>' . $myrow['suppname'] . '</td>
				<td>' . $myrow['currcode'] . '</td>
				<td>' . $myrow['address1'] . '</td>
				<td>' . $myrow['address2'] . '</td>
				<td>' . $myrow['address3'] . '</td>
				<td>' . $myrow['address4'] . '</td>
				<td>' . $myrow['telephone'] . '</td>
				<td><a href="mailto://'.$myrow['email'].'">' . $myrow['email']. '</a></td>
				<td><a href="'.$myrow['url'].'"target="_blank">' . $myrow['url']. '</a></td>
			</tr>';
		$RowIndex = $RowIndex + 1;
		//end of page full new headings if
	}
	//end of while loop
	echo '</table>';
  // }
}
//end if results to show
if (isset($ListPageMax) and $ListPageMax > 1) {
	echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': </p>';
	echo '<select name="PageOffset">';
	$ListPage = 1;
	while ($ListPage <= $ListPageMax) {
		if ($ListPage == $_POST['PageOffset']) {
			echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
		} else {
			echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
		}
		$ListPage++;
	}
	echo '</select>
		<input type="submit" name="Go" value="' . _('Go') . '" />
		<input type="submit" name="Previous" value="' . _('Previous') . '" />
		<input type="submit" name="Next" value="' . _('Next') . '" />';
	echo '<br />';
}
echo '</div>
      </form>';

//---
				$SQL = "SELECT grnbatch,
								grnno,
								purchorderdetails.orderno,
								purchorderdetails.unitprice,
								grns.itemcode,
								grns.deliverydate,
								grns.itemdescription,
								grns.qtyrecd,
								grns.quantityinv,
								grns.stdcostunit,
								purchorderdetails.glcode,
								purchorderdetails.shiptref,
								purchorderdetails.jobref,
								purchorderdetails.podetailitem,
								purchorderdetails.assetid,
								stockmaster.decimalplaces
						FROM grns INNER JOIN purchorderdetails
							ON  grns.podetailitem=purchorderdetails.podetailitem
						LEFT JOIN stockmaster ON grns.itemcode=stockmaster.stockid
						WHERE grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
						AND purchorderdetails.orderno = '" . intval($_GET['ReceivePO']) . "'
						AND grns.qtyrecd - grns.quantityinv > 0
						ORDER BY grns.grnno";
				$GRNResults = DB_query($SQL);
				//while ($myrow=DB_fetch_array($GRNResults)){

					if ($myrow['decimalplaces']==''){
						$myrow['decimalplaces']=2;
					}
		

	
	

	$TotalAssetValue = 0;

if(isset($InputError) AND $InputError==true){ //add a link to return if users make input errors.
	echo '<div class="centre"><a href="'.$RootPath.'/SupplierInvoice.php" >' . _('Back to Invoice Entry') . '</a></div>';
} //end of return link for input errors
*/
include('includes/footer.php');
?>
