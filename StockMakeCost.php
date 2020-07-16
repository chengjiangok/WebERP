
<?php
/*  StoreMakeCost.php
 * @Author: ChengJiang 
 * @Date: 2018-09-21 06:12:00 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-14 17:40:55
 */


/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');

$Title ='生产成本结账';
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

/*

if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (!isset($_SESSION['SuppTrans']->SupplierName)) {
	$sql="SELECT suppname FROM suppliers WHERE supplierid='" . $_GET['SupplierID'] . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	$SupplierName=$myrow[0];
} else {
	$SupplierName=$_SESSION['SuppTrans']->SupplierName;
}*/
//-------------
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

//-----------
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';
	echo'<table cellpadding="3" class="selection">';
	echo'<tr><td>选择查询日期</td><td collspan="2">';
		       
		echo'	<input type="date"   alt="" min="'.substr($_SESSION['lastdate'],0,5).'01-01'.'" max="'.substr($_SESSION['lastdate'],0,8).'01'.'"  name="AfterDate" maxlength="10" size="11" value="' . substr($_SESSION['lastdate'],0,8).'01' . '" />
		<input type="date"   alt="" min="'.substr($_SESSION['lastdate'],0,5).'01-01'.'" max="'.$_SESSION['lastdate'].'"  name="BeforDate" maxlength="10" size="11" value="' . $_SESSION['lastdate'] . '" />';
		
	echo'</td></tr>';
	
	echo'<tr><td>选择清账类别</td><td collspan="2">';
	if (empty($OpenAccount) && empty($ClearAccount)){
		echo'<input type="checkbox" name="OpenAccount" value="1" checked />未清账
		<input type="checkbox" name="ClearAccount" value="1" checked />已清账';
	}else{
		echo'<input type="checkbox" name="OpenAccount" value="1" '. ($OpenAccount==1 ?"checked":"").' />未清账
		<input type="checkbox" name="ClearAccount" value="1" '. ($ClearAccount==1 ?"checked":"").' />已清账';
			
	}
	echo'</td></tr>';
	echo'</table>';
	$SQL = "SELECT categoryid,  categorydescription
				FROM stockcategory
				INNER JOIN locationusers ON locationusers.loccode = categoryid AND locationusers.userid = '".$_SESSION['UserID']."' AND locationusers.canupd = 1
				WHERE stocktype = 'M'
				ORDER BY categorydescription";
$result1 = DB_query($SQL);
	echo'<table cellpadding="3" class="selection">';
	echo'<tr>';
	echo '<td>' . _('In Stock Category') . ':';
	echo '<select name="StockCat">';
	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] ='';
	}
	
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		}
	}
	echo '</select></td>';
	echo '<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
	}
	echo '</td>
		</tr>
		<tr>
			<td></td>
			<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>
			<td>';
	if (isset($_POST['StockCode'])) {
		echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
	}
	echo '<tr>
			<td></td>
			<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Supplier Code') . '</b>:</td>
			<td>';
	if (isset($_POST['SupplierStockCode'])) {
		echo '<input type="text" name="SupplierStockCode" value="' . $_POST['SupplierStockCode'] . '" title="' . _('Enter text that you wish to search for in the supplier\'s item code') . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" name="SupplierStockCode" title="' . _('Enter text that you wish to search for in the supplier\'s item code') . '" size="15" maxlength="18" />';
	}
	echo '</td></tr></table><br />';
	
	echo '<br />
		<div class="centre">
			<input type="submit" name="Search" value="查询" />
			
			<input type="submit" name="crtExcel" value="导出Excel" />
		
		</div>';
    
	if (isset($_POST['SuppAccount'])) {
		if (isset($_SESSION['SupplierID'])){
		    prnMsg($_POST['PurchIssue']);
		}else{
			prnMsg($_POST['Issue']);
		}
		//$result=DB_query($sql);
		//prnMsg($sql);
		
			echo '<table width="90%" cellpadding="4"  class="selection">
				<tr>
				    <th >序号</th>
					<th >供应商名称</th>
					<th >对账单号</th>
					<th >合同号</th>
					<th >收货金额</th>
					<th >税额</th>
					<th >合计</th>
					<th >类别</th>
					<th ></th>
				</tr>';
				$RowIndex=1;
				$k=0;
				$rr=0;
				$rw=1;
				$suppno='';
				$supacc='-1';
				$Total=0;
				$suptyp=2;
				$TaxTotal=0;
				$TotalAll=0;
				$TaxTotalAll=0;
		while($row=DB_fetch_array($result)){
			 $taxtotal=round($row['total'],2)+round($row['taxamount'],2);
			 if($row['supaccno']>0){
				$suptyp=1;
				$URL_Edit= $RootPath . '/CreateJournal.php?ty=2&ntpa=';
			 }else{
				$suptyp=0;
				$URL_Edit= $RootPath . '/CreateJournal.php?ty=2&ntpa=2';
			 }
			 if ($supacc!=$suptyp ) {			
				if ( $rw>1) {
					echo '<tr>
			        <td></td>
					<td colspan="3">对账合计</td>				
					<td >'.$Total.'</td>
					<td >'.$TaxTotal.'</td>
					<td >'.($Total+$TaxTotal).'</td>
					<td ></td>
					<td ></td>
				</tr>';
					$Total=0;
					$TaxTotal=0;
					$rw=1;
				}				
				$supacc=$suptyp;
			 }			
			 if ($row['supplierno']!=$suppno ) {
				if ($suppno!=0 && $rr>1) {
					echo '<tr>
			        <td></td>
					<td colspan="3">小计</td>				
					<td class="number">'.$Total.'</td>
					<td class="number">'.$TaxTotal.'</td>
					<td class="number">'.($Total+$TaxTotal).'</td>
					<td ></td>
					<td ></td>
				</tr>';
				    $rr=0;
					$Total=0;
					$TaxTotal=0;
				
				}
			
				$suppno=$row['supplierno'];
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				}else{
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				echo ' <td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$rw.'</a></td>
					  <td >['.$row['supplierno'].']'.$row['suppname'].'</td>';
			}else {	
					
				if ($k==1){
					echo '<tr class="EvenTableRows">
							<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$rw.'</a></td>
							<td></td>';
					$k=0;
				}else {
					echo '<tr class="OddTableRows">
							<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$rw.'</a></td>
							<td></td>';
					$k=1;
				}
			}
			$Total+=round($row['total'],2);
			$TaxTotal+=round($row['taxamount'],2);
			$TotalAll+=round($row['total'],2);
			$TaxTotalAll+=round($row['taxamount'],2);
			$orderno=$row['orderno'];
			
			echo '<td>'.$row['supaccno'].'</td>
			      <td>'.$row['orderno'].'</td>';			
			echo' 	<td class="number">'.locale_number_format(round($row['total'],2),2).'</td>
					<td class="number">'.locale_number_format(round($row['taxamount'],2),2).'</td>
					<td class="number">'.locale_number_format(round($taxtotal,2),2).'</td>
					<td ></td>
					<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
				</tr>';
				
				$RowIndex++;
				$rr++;	
				$rw++;
		}//end while
			if ($rr>1 ) {
				echo '<tr>
				<td></td>
				<td colspan="3">小计</td>				
				<td class="number" >'.locale_number_format($Total,2).'</td>
				<td class="number">'.locale_number_format($TaxTotal,2).'</td>
				<td class="number">'.locale_number_format(($Total+$TaxTotal),2).'</td>
				<td ></td>
				<td ></td>
			</tr>';
				//$suppno=$row['supplierno'];
			}
			if ( $rw>1) {
				echo '<tr>
				<td></td>
				<td colspan="3">对账合计</td>				
				<td class="number">'.locale_number_format($Total,2).'</td>
				<td class="number">'.locale_number_format($TaxTotal,2).'</td>
				<td class="number">'.locale_number_format(($Total+$TaxTotal),2).'</td>
				<td ></td>
				<td ></td>
			</tr>';
				$Total=0;
				$TaxTotal=0;
				$rw=1;
			}
			echo '<tr>
			        <td></td>
					<td colspan="3">总计</td>				
					<td class="number">'.locale_number_format($TotalAll,2).'</td>
					<td class="number">'.locale_number_format($TaxTotalAll,2).'</td>
					<td class="number">'.locale_number_format(($TotalAll+$TaxTotalAll),2).'</td>
					<td ></td>
					<td ></td>
				</tr>';
			echo'</table>';			
	}
if (isset($_POST['Search'])&& (!$result)) {

	$ListCount = DB_num_rows($Result);
	if ($ListCount>0){
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
   }
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

include('includes/footer.php');
?>
