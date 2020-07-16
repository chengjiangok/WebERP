
<?php
/* $Id: SuppPriceList.php 6944 2014-10-27 07:15:34Z daintree $*/
/*修改crtExcel未写  Serach写了未调试
 * @Author: ChengJiang 
 * @Date: 2017-10-04 10:11:39 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-10-04 10:56:18
 */
include('includes/session.php');

if (isset($_GET['SelectedSupplier'])) {
	$_POST['supplierid']=$_GET['SelectedSupplier'];
}
$Title=_('Supplier Price List');
include('includes/header.php');
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (!isset($_POST['PrintPDF'])) { /*The option to print PDF was not hit so display form */
	
		
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Purchase') . '" alt="" />' . ' ' . _('Supplier Price List') . '</p>';
		echo '<div class="page_help_text">' . _('View the Price List from supplier') . '</div><br />';
	
		echo '<br/>
			<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
		$sql = "SELECT supplierid,suppname FROM `suppliers`
		        
				WHERE  LEFT(suppliers.supplierid,1) IN (".$_SESSION['costitem'].")";
		$result = DB_query($sql);
		echo '<table class="selection">
				<tr>
					<td>' . _('Supplier') . ':</td>
					<td><select name="supplier"> ';
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['supplierid']) and ($myrow['supplierid'] == $_POST['supplierid'])) {
				 echo '<option selected="selected" value="' . $myrow['supplierid'] . '">' . $myrow['supplierid'].' - '.$myrow['suppname'] . '</option>';
			} else {
				 echo '<option value="' . $myrow['supplierid'] . '">' . $myrow['supplierid'].' - '.$myrow['suppname'] . '</option>';
			}
		}
		echo '</select></td>
			</tr>';
	
		$sql="SELECT categoryid, categorydescription FROM stockcategory WHERE stocktype='B' AND LEFT(categoryid,1) IN (".$_SESSION['costitem'].")";
		$result = DB_query($sql);
		echo '<tr>
				<td>' . _('Category') . ':</td>
				<td><select name="category"> ';
			//echo '<option value="all">' . _('ALL') . '</option>';
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['categoryid']) and ($myrow['categoryid'] == $_POST['categoryid'])) {
				 echo '<option selected="selected" value="' . $myrow['categoryid'] . '">' . $myrow['categoryid']-$myrow['categorydescription'] . '</option>';
			} else {
				 echo '<option value="' . $myrow['categoryid'] . '">' .$myrow['categoryid'].' - '. $myrow['categorydescription'] . '</option>';
			}
		}
		echo '</select></td>
			</tr>';
	
		echo '<tr>
				<td>' . _('Price List') . ':</td>
				<td><select name="price">
					<option value="all">' ._('All Prices') . '</option>
					<option value="current">' ._('Only Current Price') . '</option>
					</select>
				</td>
			</tr>';
	
	
		echo '</table>
				<br/>
				<div class="centre">
					<input type="submit" name="Serach" value="查询显示" />
					<input type="submit" name="crtExcel" value="生成Excel" />
					<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
				</div>';
	
		
	
	} /*end of else not PrintPDF */

	//get supplier
	$sqlsup = "SELECT suppname,
	currcode,
	decimalplaces AS currdecimalplaces
	FROM suppliers INNER JOIN currencies
	ON suppliers.currcode=currencies.currabrev
	WHERE supplierid='" . $_POST['supplier'] . "'";
	$resultsup = DB_query($sqlsup);
	$RowSup = DB_fetch_array($resultsup);
	$SupplierName=$RowSup['suppname'];
	$CurrCode =$RowSup['currcode'];
	$CurrDecimalPlaces=$RowSup['currdecimalplaces'];

		//get category
		if ($_POST['category']!='all'){
		$sqlcat="SELECT categorydescription
		FROM `stockcategory`
		WHERE categoryid ='" . $_POST['category'] . "'";

		$resultcat = DB_query($sqlcat);
		$RowCat = DB_fetch_row($resultcat);
		$Categoryname=$RowCat['0'];
		} else {
		$Categoryname='ALL';
		}


		//get date price
		if ($_POST['price']=='all'){
		$CurrentOrAllPrices=_('All Prices');
		} else {
		$CurrentOrAllPrices=_('Current Price');
		}

//price and category = all
if (($_POST['price']=='all') AND ($_POST['category']=='all')){
		$sql = "SELECT 	purchdata.stockid,
		stockmaster.description,
		purchdata.price,
		purchdata.conversionfactor,
		(purchdata.effectivefrom)as dateprice,
		purchdata.supplierdescription,
		purchdata.suppliers_partno
		FROM purchdata,stockmaster
		WHERE supplierno='" . $_POST['supplier'] . "'
		AND stockmaster.stockid=purchdata.stockid
		ORDER BY stockid ASC ,dateprice DESC";
} else {
		//category=all and price != all
	if (($_POST['price']!='all') AND ($_POST['category']=='all')){

		$sql = "SELECT purchdata.stockid,
				stockmaster.description,
				(SELECT purchdata.price
				FROM purchdata
				WHERE purchdata.stockid = stockmaster.stockid
				ORDER BY effectivefrom DESC
				LIMIT 0,1) AS price,
				purchdata.conversionfactor,
				(SELECT purchdata.effectivefrom
				FROM purchdata
				WHERE purchdata.stockid = stockmaster.stockid
				ORDER BY effectivefrom DESC
				LIMIT 0,1) AS dateprice,
				purchdata.supplierdescription,
				purchdata.suppliers_partno
		FROM purchdata, stockmaster
		WHERE supplierno = '" . $_POST['supplier'] . "'
		AND stockmaster.stockid = purchdata.stockid
		GROUP BY stockid
		ORDER BY stockid ASC , dateprice DESC";
	} else {
		//price = all category !=all
		if (($_POST['price']=='all')and($_POST['category']!='all')){

		$sql = "SELECT 	purchdata.stockid,
					stockmaster.description,
					purchdata.price,
					purchdata.conversionfactor,
					(purchdata.effectivefrom)as dateprice,
					purchdata.supplierdescription,
					purchdata.suppliers_partno
			FROM purchdata,stockmaster
			WHERE supplierno='" . $_POST['supplier'] . "'
			AND stockmaster.stockid=purchdata.stockid
			AND stockmaster.categoryid='" . $_POST['category'] .  "'
			ORDER BY stockid ASC ,dateprice DESC";
		} else {
		//price != all category !=all
		$sql = "SELECT 	purchdata.stockid,
					stockmaster.description,
					(SELECT purchdata.price
					FROM purchdata
					WHERE purchdata.stockid = stockmaster.stockid
					ORDER BY effectivefrom DESC
					LIMIT 0,1) AS price,
					purchdata.conversionfactor,
					(SELECT purchdata.effectivefrom
					FROM purchdata
					WHERE purchdata.stockid = stockmaster.stockid
					ORDER BY effectivefrom DESC
					LIMIT 0,1) AS dateprice,
					purchdata.supplierdescription,
					purchdata.suppliers_partno
			FROM purchdata,stockmaster
			WHERE supplierno='" . $_POST['supplier'] . "'
			AND stockmaster.stockid=purchdata.stockid
			AND stockmaster.categoryid='" . $_POST['category'] .  "'
			GROUP BY stockid
			ORDER BY stockid ASC ,dateprice DESC";
		}
	}
}
$result = DB_query($sql,'','',false,true);
//prnMsg($sql.'[row203]','info');
if (isset($_POST['crtExcel'])||isset($_POST['PrintPDF'])||isset($_POST['Serach'])){
	if (DB_error_no() !=0) {
	$Title = _('Price List') . ' - ' . _('Problem Report');
	//	include('includes/header.php');
	prnMsg( _('The Price List could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
	echo '<br />
	<a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
	if ($debug==1){
	echo '<br />' . $sql;
	}
	include('includes/footer.php');
	exit;
}

if (DB_num_rows($result)==0) {

$Title = _('Supplier Price List') . '-' . _('Report');
//	include('includes/header.php');
prnMsg(_('There are no result so the PDF is empty'));
include('includes/footer.php');
exit;
}
}

if (!isset($_POST['PrintPDF'])){
	echo '</div>
	</form>';
include('includes/footer.php');
}
if (isset($_POST['PrintPDF'])){
	
		include('includes/PDFStarter.php');
	
		$FontSize=9;
		$pdf->addInfo('Title',_('Supplier Price List'));
		$pdf->addInfo('Subject',_('Price List of goods from a Supplier'));
	
		$PageNumber=1;
		$line_height=12;
	PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
	            $Page_Width,$Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices);

	$FontSize=8;
	$code='';
	while ($myrow = DB_fetch_array($result,$db)){
		$YPos -=$line_height;

		$PriceDated=ConvertSQLDate($myrow[4]);

		//if item has more than 1 price, write only price, date and supplier code for the old ones
		if ($code==$myrow['stockid']){

			$pdf->addTextWrap(350,$YPos,50,$FontSize,locale_number_format($myrow['price'],$CurrDecimalPlaces),'right');
			$pdf->addTextWrap(410,$YPos,50,$FontSize,$PriceDated,'left');
			$pdf->addTextWrap(470,$YPos,90,$FontSize,$myrow['suppliers_partno'],'left');
			$code=$myrow['stockid'];
		} else {
			$code=$myrow['stockid'];
			$pdf->addTextWrap(30,$YPos,100,$FontSize,$myrow['stockid'],'left');
			$pdf->addTextWrap(135,$YPos,160,$FontSize,$myrow['description'],'left');
			$pdf->addTextWrap(300,$YPos,50,$FontSize,locale_number_format($myrow['conversionfactor'],'Variable'),'right');
			$pdf->addTextWrap(350,$YPos,50,$FontSize,locale_number_format($myrow['price'],$CurrDecimalPlaces),'right');
			$pdf->addTextWrap(410,$YPos,50,$FontSize,$PriceDated,'left');
			$pdf->addTextWrap(470,$YPos,90,$FontSize,$myrow['suppliers_partno'],'left');
		}


		if ($YPos < $Bottom_Margin + $line_height){

			PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
			            $Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices);
		}


	} /*end while loop  */


	if ($YPos < $Bottom_Margin + $line_height){
	       PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
	                   $Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices);
	}


	$pdf->OutputD( $_SESSION['DatabaseName'] . '_SupplierPriceList_' . Date('Y-m-d') . '.pdf');


}elseif(isset($_POST['crtExcel'])){
	prnMsg('crtExcel调试中...[291]','info');
}elseif(isset($_POST['Serach'])){
	prnMsg('Serach调试中....[293]','info');
	    $ListCount = DB_num_rows($result);
//	if ($ListCount == 0) {
		// If the user hit the search button and there is more than one item to show
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
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
				<input type="submit" name="Next" value="' . _('Next') . '" />
				<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'" />
				<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'" />
				<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'" />
				<br />
				</div>';
		}
	echo '<table id="ItemSearchTable" class="selection">';
	$TableHeader = '<tr>
						<th>序号</th>
						<th> 物料编码</th>
						<th class="ascending">' . _('Description') . '</th>
						<th class="ascending">转换因子</th>
						<th class="ascending">价格</th>
						<th>日期</th>
						<th></th>
					</tr>';
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($result) <> 0) {
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		$PriceDated=ConvertSQLDate($myrow[4]);
		echo '<td>' . $RowIndex . '</td>
			  <td>' . $myrow['stockid'] . '</td>
			  <td>' .$myrow['description'] . '</td>
			  <td class="number">' .locale_number_format($myrow['conversionfactor']) . '</td>
			  <td class="number">' .locale_number_format($myrow['price'])  . '</td>
			  <td>' . $PriceDated . '</td>
			  <td>'.$myrow['suppliers_partno'].'</td>
			</tr>';

		$RowIndex = $RowIndex + 1;
		//end of page full new headings if
	}
	//end of while loop
	echo '</table>';
   
} 



function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin,$SupplierName,$Categoryname,$CurrCode,$CurrentOrAllPrices) {


	/*PDF page header for Supplier price list */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;
	$YPos -=(3*$line_height);

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize+2,$_SESSION['CompanyRecord']['coyname']);
	$YPos -=$line_height;

	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('Supplier Price List for').' '.$CurrentOrAllPrices);

	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Supplier').'   ');
	$pdf->addTextWrap(95,$YPos,150,$FontSize,_(': ').$SupplierName);

	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Category').' ');

	$pdf->addTextWrap(95,$YPos,150,$FontSize,_(': ').$Categoryname);
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Currency').'  ');
	$pdf->addTextWrap(95,$YPos,50,$FontSize,_(': ').$CurrCode);
	$YPos -=(2*$line_height);
	/*set up the headings */

	$pdf->addTextWrap(30,$YPos,80,$FontSize,_('Code'), 'left');
	$pdf->addTextWrap(135,$YPos,80,$FontSize,_('Description'), 'left');
	$pdf->addTextWrap(300,$YPos,50,$FontSize,_('Conv Factor'), 'left');
	$pdf->addTextWrap(370,$YPos,50,$FontSize,_('Price'), 'left');
	$pdf->addTextWrap(410,$YPos,80,$FontSize,_('Date From'), 'left');
	$pdf->addTextWrap(470,$YPos,80,$FontSize,_('Supp Code'), 'left');

	$FontSize=8;
	$PageNumber++;
} // End of PrintHeader() function
?>
