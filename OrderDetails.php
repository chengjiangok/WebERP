
<?php

/* $Id: OrderDetails.php 6941 2014-10-26 23:18:08Z daintree $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-03 12:56:32 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-03 16:01:22
 */
/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');

$_GET['OrderNumber']=(int)$_GET['OrderNumber'];

if (isset($_GET['OrderNumber'])) {
	$Title = _('Reviewing Sales Order Number') . ' ' . $_GET['OrderNumber'];
} else {
	include('includes/header.php');
	echo '<br /><br /><br />';
	prnMsg(_('This page must be called with a sales order number to review') . '.<br />' . _('i.e.') . ' http://????/OrderDetails.php?OrderNumber=<i>xyz</i><br />' . _('Click on back') . '.','error');
	include('includes/footer.php');
	exit;
}

include('includes/header.php');

$OrderHeaderSQL = "SELECT salesorders.debtorno,
							debtorsmaster.name,
							salesorders.branchcode,
							salesorders.customerref,
							salesorders.comments,
							salesorders.orddate,
							salesorders.ordertype,
							salesorders.shipvia,
							salesorders.deliverto,
							salesorders.deladd1,
							salesorders.deladd2,
							salesorders.deladd3,
							salesorders.deladd4,
							salesorders.deladd5,
							salesorders.deladd6,
							salesorders.contactphone,
							salesorders.contactemail,
							salesorders.freightcost,
							salesorders.deliverydate,
							debtorsmaster.currcode,
							salesorders.fromstkloc,
							currencies.decimalplaces
					FROM salesorders INNER JOIN 	debtorsmaster
					ON salesorders.debtorno = debtorsmaster.debtorno
					INNER JOIN currencies
					ON debtorsmaster.currcode=currencies.currabrev
					WHERE salesorders.orderno = '" . $_GET['OrderNumber'] . "'";

$ErrMsg =  _('The order cannot be retrieved because');
$DbgMsg = _('The SQL that failed to get the order header was');
$GetOrdHdrResult = DB_query($OrderHeaderSQL, $ErrMsg, $DbgMsg);

if (DB_num_rows($GetOrdHdrResult)==1) {
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Order Details') . '" alt="" />' . ' ' . $Title . '
		</p>';

	$myrow = DB_fetch_array($GetOrdHdrResult);
	$CurrDecimalPlaces = $myrow['decimalplaces'];

	if ($CustomerLogin ==1 AND $myrow['debtorno']!= $_SESSION['CustomerID']) {
		prnMsg (_('Your customer login will only allow you to view your own purchase orders'),'error');
		include('includes/footer.php');
		exit;
	}

	echo '<table class="selection">
			<tr>
				<th colspan="4"><h3>' . _('Order Header Details For Order No').' '.$_GET['OrderNumber'] . '</h3></th>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Customer Code') . ':</th>
				<td class="OddTableRows"><a href="' . $RootPath . '/SelectCustomer.php?Select=' . $myrow['debtorno'] . '">' . $myrow['debtorno'] . '</a></td>
				<th style="text-align: left">' . _('Customer Name') . ':</th>
				<th>' . $myrow['name'] . '</th>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Customer Reference') . ':</th>
				<td class="OddTableRows">' . $myrow['customerref'] . '</td>
				<th style="text-align: left">' . _('Deliver To') . ':</th>
				<th>' . $myrow['deliverto'] . '</th>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Ordered On') . ':</th>
				<td class="OddTableRows">' . ConvertSQLDate($myrow['orddate']) . '</td>
				<th style="text-align: left">' . _('Requested Delivery') . ':</th>
				<td class="OddTableRows">' . ConvertSQLDate($myrow['deliverydate']) . '</td>
				
			</tr>
			<tr>
			<tr><th style="text-align: left">' . _('Order Currency') . ':</th>
			<td class="OddTableRows">' . $myrow['currcode'] . '</td>

			<th style="text-align: left">交货地址:</th>
			<td class="OddTableRows">' . $myrow['deladd1'] . '</td>
				</tr>
				<tr>
				<th style="text-align: left">' . _('Deliver From Location') . ':</th>
				<td class="OddTableRows">' . $myrow['fromstkloc'] . '</td>
				<th style="text-align: left">交货联系人:</th>
				<td class="OddTableRows">' . $myrow['deladd2'] . '</td>
			</tr>';
			
		echo'<tr>
				<th style="text-align: left">' . _('Freight Cost') . ':</th>
				<td class="OddTableRows">' . $myrow['freightcost'] . '</td>
				
				<th style="text-align: left">' . _('Telephone') . ':</th>
				<td class="OddTableRows">' . $myrow['contactphone'] . '</td>
			</tr>
			<tr>
				<th style="text-align: left">' . _('Comments'). ': </th>
				<td colspan="1">' . $myrow['comments'] . '</td>			
				<th style="text-align: left">' . _('Email') . ':</th>
				<td class="OddTableRows"><a href="mailto:' . $myrow['contactemail'] . '">' . $myrow['contactemail'] . '</a></td>
			</tr>';
			/*
			<tr>
			<th style="text-align: left">' . _('Delivery Address 3') . ':</th>
			<td class="OddTableRows">' . $myrow['deladd3'] . '</td>
			<th style="text-align: left">' . _('Delivery Address 4') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd4'] . '</td>
				<th style="text-align: left">' . _('Delivery Address 5') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd5'] . '</td>
			</tr>
			<tr>
			
				<th style="text-align: left">' . _('Delivery Address 6') . ':</th>
				<td class="OddTableRows">' . $myrow['deladd6'] . '</td>
			</tr>';*/
			
		
}

/*Now get the line items */

	$LineItemsSQL = "SELECT stkcode,
							stockmaster.description,
							stockmaster.volume,
							stockmaster.grossweight,
							stockmaster.decimalplaces,
							stockmaster.mbflag,
							stockmaster.units,
							stockmaster.discountcategory,
							stockmaster.controlled,
							stockmaster.serialised,
							unitprice,
							taxprice,
							cess,
							quantity,
							discountpercent,
							actualdispatchdate,
							qtyinvoiced,
							itemdue,
							poline
						FROM salesorderdetails INNER JOIN stockmaster
						ON salesorderdetails.stkcode = stockmaster.stockid
						WHERE orderno ='" . $_GET['OrderNumber'] . "'";

	$ErrMsg =  _('The line items of the order cannot be retrieved because');
	$DbgMsg =  _('The SQL used to retrieve the line items, that failed was');
	$LineItemsResult = DB_query($LineItemsSQL, $ErrMsg, $DbgMsg);

	if (DB_num_rows($LineItemsResult)>0) {

		$OrderTotal = 0;
		$OrderQtyTotal = 0;
		$OrderTaxTotal = 0;
		$LineTotal = 0;
		$LineTaxTotal=0;
		$OrderTotalVolume = 0;
		$OrderTotalWeight = 0;

		echo '<br />
			<table class="selection">
			<tr>
				<th colspan="12"><h3>' . _('Order Line Details For Order No').' '.$_GET['OrderNumber'] . '</h3></th>
			</tr>
			<tr>
				<th>' . _('PO Line') . '</th>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Item Description') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Unit') . '</th>
				<th>订单价格</th>
				<th>税率</th>
				<th>' . _('Price') . '</th>
				<th>税额</th>
				<th>' . _('Total') . '</th>
				<th>' . _('Qty Del') . '</th>
				<th>' . _('Last Del') . '/' . _('Due Date') . '</th>
			</tr>';
		$k=0;
		/*
		$TaxSql="SELECT taxcatname, a.taxcatid, taxrate FROM taxauthrates a LEFT JOIN taxcategories b ON a.taxcatid=b.taxcatid WHERE taxcatid=".$_SESSION['PO' . $identifier]->TaxCatID;
		$TaxResult=DB_query($TaxSql);
		$TaxRow=DB_fetch_assoc($TaxResult);
		*/
		while ($myrow=DB_fetch_array($LineItemsResult)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			if ($myrow['qtyinvoiced']>0){
				$DisplayActualDeliveryDate = ConvertSQLDate($myrow['actualdispatchdate']);
			} else {
		  		$DisplayActualDeliveryDate = '<span style="color:red;">' . ConvertSQLDate($myrow['itemdue']) . '</span>';
			}
			$LineTotal = ($myrow['quantity'] * $myrow['taxprice'] );
			$LineTaxTotal=locale_number_format($LineTotal/(1+$myrow['cess'])*$myrow['cess'],2);
			echo 	'<td>' . $myrow['poline'] . '</td>
				<td>' . $myrow['stkcode'] . '</td>
				<td>' . $myrow['description'] . '</td>
				<td class="number">' . $myrow['quantity'] . '</td>
				<td>' . $myrow['units'] . '</td>
				<td>' . locale_number_format($myrow['taxprice'],$CurrDecimalPlaces) . '</td>
				<td>' . (100*$myrow['cess']) . '%</td>
				<td class="number">' . locale_number_format($myrow['unitprice'],$CurrDecimalPlaces) . '</td>
				<td class="number">' . $LineTaxTotal . '</td>
				<td class="number">' . locale_number_format($LineTotal ,$CurrDecimalPlaces) . '</td>
				<td class="number">' . locale_number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']) . '</td>
				<td>' . $DisplayActualDeliveryDate . '</td>
			</tr>';

			$OrderTotal +=$LineTotal;
			$OrderTaxTotal +=$LineTaxTotal;
			$OrderQtyTotal +=  locale_number_format($myrow['quantity'],$myrow['decimalplaces']) ;
			$OrderQtyInvTotal +=  locale_number_format($myrow['qtyinvoiced'],$myrow['decimalplaces']) ;
			
			//$OrderTotalVolume += ($myrow['quantity'] * $myrow['volume']);
			//$OrderTotalWeight += ($myrow['quantity'] * $myrow['grossweight']);

		}
		
		$DisplayVolume = locale_number_format($OrderTotalVolume,2);
		$DisplayWeight = locale_number_format($OrderTotalWeight,2);

		echo '<tr>
				<td colspan="3" class="number"><b>合计</b></td>
				<td >' . $OrderQtyTotal . ' </td>
				<td colspan="4"> </td>
				<td class="number">' . locale_number_format($OrderTaxTotal,$CurrDecimalPlaces)  . '</td>
				<td class="number">' .locale_number_format($OrderTotal,$CurrDecimalPlaces) . '</td>
				<td class="number">' . $OrderQtyInvTotal . '</td>
				<td ></td>
			</tr>
			</table>';
		/*
		echo '<br />
			<table class="selection">
			<tr>
				<td>' . _('Total Weight') . ':</td>
				<td>' . $DisplayWeight . '</td>
				<td>' . _('Total Volume') . ':</td>
				<td>' . $DisplayVolume . '</td>
			</tr>
		</table>';
		*/
	}

include('includes/footer.php');
?>
