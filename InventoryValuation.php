<?php
/* $Id: InventoryValuation.php  $ */
/*二次开发完成
 * @Author: ChengJiang 
 * @Date: 2017-10-04 15:12:12 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-10-04 15:37:26
 */
include('includes/session.php');
/*Now figure out the inventory data to report for the category range under review */
	if ($_POST['Location']=='All' ||$_POST['subCategory']=='All'){
		$SQL = "SELECT stockmaster.categoryid,
					stockcategory.categorydescription,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.decimalplaces,
					SUM(locstock.quantity) AS qtyonhand,
					stockmaster.units,
					stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS unitcost,
					SUM(locstock.quantity) *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemtotal
				FROM stockmaster,
					stockcategory,
					locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.categoryid=stockcategory.categoryid
				GROUP BY stockmaster.categoryid,
					stockcategory.categorydescription,
					unitcost,
					stockmaster.units,
					stockmaster.decimalplaces,
					stockmaster.materialcost,
					stockmaster.labourcost,
					stockmaster.overheadcost,
					stockmaster.stockid,
					stockmaster.description
				HAVING SUM(locstock.quantity)!=0
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				ORDER BY stockcategory.categorydescription,
					stockmaster.stockid";
	} else {
		$SQL = "SELECT stockmaster.categoryid,
					stockcategory.categorydescription,
					stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.decimalplaces,
					locstock.quantity AS qtyonhand,
					stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS unitcost,
					locstock.quantity *(stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost) AS itemtotal
				FROM stockmaster,
					stockcategory,
					locstock
				INNER JOIN locationusers ON locationusers.loccode=locstock.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				WHERE stockmaster.stockid=locstock.stockid
				AND stockmaster.categoryid=stockcategory.categoryid
				AND locstock.quantity!=0
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND locstock.loccode = '" . $_POST['Location'] . "'
				AND LEFT(stockmaster.stockid,3) IN ('". implode("','",$_POST['subCategory'])."')
				ORDER BY stockcategory.categorydescription,
					stockmaster.stockid";
	}
	$InventoryResult = DB_query($SQL,'','',false,true);

if (isset($_POST['Search']) OR isset($_POST['PrintPDF']) OR isset($_POST['CSV']) OR isset($_POST['crtExcel'])){
	if (DB_error_no() !=0) {
	  $Title = _('Inventory Valuation') . ' - ' . _('Problem Report');
	  include('includes/header.php');
	   prnMsg( _('The inventory valuation could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
	   echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	   if ($debug==1){
		  echo '<br />' . $SQL;
	   }
	   include('includes/footer.php');
	   exit;
	}
}

if (isset($_POST['PrintPDF'])){

	include('includes/PDFStarter.php');

	$pdf->addInfo('Title',_('Inventory Valuation Report'));
	$pdf->addInfo('Subject',_('Inventory Valuation'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;


	
	if (DB_num_rows($InventoryResult)==0){
		$Title = _('Print Inventory Valuation Error');
		include('includes/header.php');
		prnMsg(_('There were no items with any value to print out for the location specified'),'info');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit;
	}

	include ('includes/PDFInventoryValnPageheader.inc');

	$Tot_Val=0;
	$Category = '';
	$CatTot_Val=0;
	$CatTot_Qty=0;

	while ($InventoryValn = DB_fetch_array($InventoryResult)){

		if ($Category!=$InventoryValn['categoryid']){
			$FontSize=10;
			if ($Category!=''){ /*Then it's NOT the first time round */

				/* need to print the total of previous category */
				if ($_POST['DetailedReport']=='Yes'){
					$YPos -= (2*$line_height);
					if ($YPos < $Bottom_Margin + (3*$line_height)){
		 				  include('includes/PDFInventoryValnPageheader.inc');
					}
					$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,_('Total for') . ' ' . $Category . ' - ' . $CategoryName);
				}

				$DisplayCatTotVal = locale_number_format($CatTot_Val,$_SESSION['CompanyRecord']['decimalplaces']);
				$DisplayCatTotQty = locale_number_format($CatTot_Qty,2);
				$LeftOvers = $pdf->addTextWrap(480,$YPos,80,$FontSize,$DisplayCatTotVal, 'right');
				$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,$DisplayCatTotQty, 'right');
				$YPos -=$line_height;

				If ($_POST['DetailedReport']=='Yes'){
				/*draw a line under the CATEGORY TOTAL*/
					$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
					$YPos -=(2*$line_height);
				}
				$CatTot_Val=0;
				$CatTot_Qty=0;
			}
			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$InventoryValn['categoryid'] . ' - ' . $InventoryValn['categorydescription']);
			$Category = $InventoryValn['categoryid'];
			$CategoryName = $InventoryValn['categorydescription'];
		}

		if ($_POST['DetailedReport']=='Yes'){
			$YPos -=$line_height;
			$FontSize=8;

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,100,$FontSize,$InventoryValn['stockid']);
			$LeftOvers = $pdf->addTextWrap(170,$YPos,220,$FontSize,$InventoryValn['description']);
			$DisplayUnitCost = locale_number_format($InventoryValn['unitcost'],$_SESSION['CompanyRecord']['decimalplaces']);
			$DisplayQtyOnHand = locale_number_format($InventoryValn['qtyonhand'],$InventoryValn['decimalplaces']);
			$DisplayItemTotal = locale_number_format($InventoryValn['itemtotal'],$_SESSION['CompanyRecord']['decimalplaces']);

			$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,$DisplayQtyOnHand,'right');
			$LeftOvers = $pdf->addTextWrap(423,$YPos,15,$FontSize,$InventoryValn['units'],'left');
			$LeftOvers = $pdf->addTextWrap(438,$YPos,60,$FontSize,$DisplayUnitCost, 'right');

			$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayItemTotal, 'right');
		}
		$Tot_Val += $InventoryValn['itemtotal'];
		$CatTot_Val += $InventoryValn['itemtotal'];
		$CatTot_Qty += $InventoryValn['qtyonhand'];

		if ($YPos < $Bottom_Margin + $line_height){
		   include('includes/PDFInventoryValnPageheader.inc');
		}

	} /*end inventory valn while loop */

	$FontSize =10;
	/*Print out the category totals */
	if ($_POST['DetailedReport']=='Yes'){
		$YPos -= (2*$line_height);
		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,200-$Left_Margin,$FontSize, _('Total for') . ' ' . $Category . ' - ' . $CategoryName, 'left');
	}
	$DisplayCatTotVal = locale_number_format($CatTot_Val,$_SESSION['CompanyRecord']['decimalplaces']);

	$LeftOvers = $pdf->addTextWrap(480,$YPos,80,$FontSize,$DisplayCatTotVal, 'right');
	$DisplayCatTotQty = locale_number_format($CatTot_Qty,2);
	$LeftOvers = $pdf->addTextWrap(360,$YPos,60,$FontSize,$DisplayCatTotQty, 'right');

	if ($_POST['DetailedReport']=='Yes'){
		/*draw a line under the CATEGORY TOTAL*/
		$YPos -= ($line_height);
		$pdf->line($Left_Margin, $YPos+$line_height-2,$Page_Width-$Right_Margin, $YPos+$line_height-2);
	}

	$YPos -= (2*$line_height);

	if ($YPos < $Bottom_Margin + $line_height){
		   include('includes/PDFInventoryValnPageheader.inc');
	}
	/*Print out the grand totals */
	$LeftOvers = $pdf->addTextWrap(80,$YPos,260-$Left_Margin,$FontSize,_('Grand Total Value'), 'right');
	$DisplayTotalVal = locale_number_format($Tot_Val,$_SESSION['CompanyRecord']['decimalplaces']);
	$LeftOvers = $pdf->addTextWrap(500,$YPos,60,$FontSize,$DisplayTotalVal, 'right');
	ob_end_clean();
	$pdf->OutputD($_SESSION['DatabaseName'] . '_Inventory_Valuation_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();
	
} elseif (isset($_POST['CSV'])) {

	$CSVListing =  iconv("UTF-8","gbk//TRANSLIT",_('Stock ID') ).','.iconv("UTF-8","gbk//TRANSLIT", _('Description')) .','.iconv("UTF-8","gbk//TRANSLIT", _('Category Description')) .','.iconv("UTF-8","gbk//TRANSLIT", _('Units')) .','.iconv("UTF-8","gbk//TRANSLIT",'库存数量') .','.iconv("UTF-8","gbk//TRANSLIT", _('Unit Cost')) .','. iconv("UTF-8","gbk//TRANSLIT",_('Total') ). "\n";
	while ($InventoryValn = DB_fetch_array($InventoryResult)) {
		$CSVListing .= '"';
		
		$CSVListing .=iconv("UTF-8","gbk//TRANSLIT",'`'.$InventoryValn['stockid'].'","'.$InventoryValn['description'].'","'.$InventoryValn['categorydescription'].'","'.$InventoryValn['units'].'","'.$InventoryValn['itemtotal'].'","'.$InventoryValn['unitcost'].'","'.$InventoryValn['qtyonhand']).'"'. "\n";
		//$CSVListing .=iconv("UTF-8","gbk//TRANSLIT",implode('","', $csvarr) ). '"' . "\n";itemtotal
	}
	header('Content-Encoding: UTF-8');
    header('Content-type: text/csv; charset=UTF-8');
    header("Content-disposition: attachment; filename=".iconv("UTF-8","gbk//TRANSLIT",'库存表_').  date('Y-m-d')  .'.csv');
    header("Pragma: public");
    header("Expires: 0");
    //echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $CSVListing;
	exit;


}else{ /*The option to print PDF nor to create the CSV was not hit */
	
		$Title='库存商品查询';//Inventory Valuation Reporting');
		include('includes/header.php');
	
		echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '
			</p>';
	
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
			  <div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="subCategory" value="' . $_POST['subCategory'] . '" />
			<input type="hidden" name="Location" value="' . $_POST['Location'] . '" />';
	
			echo '<table class="selection">
				<tr>
					<td>' . _('Select Inventory Categories') . ':</td>
					<td><select autofocus="autofocus" required="required" minlength="1" size="5" name="Categories[]"multiple="multiple">';
		$SQL = "SELECT categoryid,  categorydescription
				FROM stockcategory
				INNER JOIN locationusers ON locationusers.loccode = categoryid AND locationusers.userid = '".$_SESSION['UserID']."' AND locationusers.canupd = 1
				ORDER BY categorydescription";
		$CatResult = DB_query($SQL);
		while ($MyRow = DB_fetch_array($CatResult)) {
			if (isset($_POST['Categories']) AND in_array($MyRow['categoryid'], $_POST['Categories'])) {
				echo '<option selected="selected" value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] .'</option>';
			} else {
				echo '<option value="' . $MyRow['categoryid'] . '">' . $MyRow['categorydescription'] . '</option>';
			}
		}
		echo '</select>
				</td>
			</tr>';
	
		echo '<tr>
				<td>' . _('For Inventory in Location') . ':</td>
				<td><select name="Location">';
	
		$sql = "SELECT locations.loccode,
						locationname
				FROM locations
				INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
				ORDER BY locationname";
	
		$LocnResult=DB_query($sql);
	
		//echo '<option value="All">' . _('All Locations') . '</option>';
		if (!isset($_POST['Location'])){
			echo '<option selected="selected" value="All">' . _('All Locations') . '</option>';
			$_POST['Location'] ='All';
		} else {
			echo '<option value="All">' . _('All Locations') . '</option>';
		}
		while ($myrow=DB_fetch_array($LocnResult)){
			if (isset($_POST['Location']) AND $myrow['loccode']==$_POST['Location']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
			echo  $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
		echo '</select></td>
			</tr>';
			echo '<tr>
				<td>物料子类:</td>
				<td><select  name="subCategory[]" minlength="1" size="15" multiple="multiple">';
	$SQL = "SELECT subcategoryid, subcategorydspn FROM stocksubcategory INNER JOIN locationusers ON locationusers.loccode = categoryid AND locationusers.userid = '" .  $_SESSION['UserID'] . "' AND locationusers.canupd = 1 ORDER BY categoryid,subcategoryid";
	$CatResult = DB_query($SQL);
    //echo '<option value="All">所有类</option>';
	
	while ($MyRow = DB_fetch_array($CatResult)) {
		if (isset($_POST['subCategory']) AND in_array($MyRow['subcategoryid'], $_POST['subCategory'])) {
			echo '<option selected="selected" value="' . $MyRow['subcategoryid'] . '">' .$MyRow['subcategoryid'].'-'.  $MyRow['subcategorydspn'] .'</option>';
		} else {
			echo '<option value="' . $MyRow['subcategoryid'] . '">' .$MyRow['subcategoryid'].'-'. $MyRow['subcategorydspn'] . '</option>';
		}
	}
	echo '</select>
			</td>
		</tr>';
		echo '<tr>
				<td>' . _('Summary or Detailed Report') . ':</td>
				<td><select name="DetailedReport">
					<option selected="selected" value="No">' . _('Summary Report') . '</option>
					<option value="Yes">' . _('Detailed Report') . '</option>
					</select></td>
			</tr>
			</table>
		<br />
			<div class="centre">
				<input type="submit" name="Search" value="查询显示" />
				<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
				<input type="submit" name="CSV" value="' . _('Output to CSV') . '" />
			</div>';
		
}
	if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
		$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
		$_POST['Go'] = '';
	}
	
	if(!isset($_POST['PageOffset'])) {
		$_POST['PageOffset'] = 1;
	} else {
		if($_POST['PageOffset'] == 0) {
			$_POST['PageOffset'] = 1;
		}
	}
	if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
		//prnMsg('Serach调试中...[278]','info');
		if(isset($_POST['Search'])) {
			$_POST['PageOffset'] = 1;
		}	
		$ListCount = DB_num_rows($InventoryResult);
		if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
			// if Search then set to first page
			$_POST['PageOffset'] = 1;
		}
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if(isset($_POST['Next'])) {
			if($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if(isset($_POST['Previous'])) {
			if($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';

		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset1">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go1" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';
			echo '</div>';
		}
		echo '<table id="ItemSearchTable" class="selection">';
		$TableHeader = '<tr>
							<th>序号</th>
							<th>类别编码</th>
							<th>类别</th>
							<th> 物料编码</th>
							<th class="ascending">' . _('Description') . '</th>
							<th class="ascending">库存数量</th>
							<th>' . _('Units') . '</th>
							<th>单价</th>
							<th>总金额</th>						
						</tr>';
		echo $TableHeader;
		$j = 1;
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
		//if (DB_num_rows($InventoryResult) <> 0) {
			DB_data_seek($InventoryResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		//}
		while (($myrow = DB_fetch_array($InventoryResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			echo'<td>' .(  ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']+$RowIndex +1). '</td>
				<td>' . $myrow['categoryid'] . '</td>  
				<td>'. $myrow['categorydescription'] . '</td>              
				<td>' . $myrow['stockid'] . '</td>
				<td>' .$myrow['description'] . '</td>			
				<td class="number">' . locale_number_format($myrow['qtyonhand'], $myrow['decimalplaces'])  . '</td>
				<td>' . $myrow['units'] . '</td>
				<td class="number">' . locale_number_format($myrow['unitcost'], $myrow['decimalplaces'])  . '</td>
				<td class="number">' . locale_number_format($myrow['itemtotal'], $myrow['decimalplaces'])  . '</td>
				</tr>';
	   		$RowIndex = $RowIndex + 1;
			//end of page full new headings if
		}
		//end of while loop
		
		echo '</table>';
		if(isset($ListPageMax) AND $ListPageMax > 1) {
			echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset2">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				}// $ListPage == $_POST['PageOffset']
				else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}// $ListPage <= $ListPageMax
			echo '</select>
				<input type="submit" name="Go2" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';
			echo '</div>';
		}

	}	
if (!isset($_POST['PrintPDF'])||!isset($_POST['CSV'])){
	echo '</div>
		  </form>';

    
	include('includes/footer.php');

} /*end of else not PrintPDF */
?>
