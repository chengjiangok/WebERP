<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-03-27 09:39:11
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-04-11 03:02:36
 */

/* $Id: ConfirmDispatch_Invoice.php  $*/

/* Session started in session.php for password checking and authorisation level check */
include('includes/DefineCartClassCN.php');
include('includes/DefineSerialItems.php');
include('includes/session.php');
$Title ='发货开单';// _('Confirm Dispatches and Invoice An Order');

$ViewTopic= 'ARTransactions';
$BookMark = 'ConfirmInvoice';

include('includes/header.php');

include('includes/SQL_CommonFunctions.inc');
include('includes/FreightCalculation.inc');
include('includes/GetSalesTransGLCodes.inc');
echo'<script type="text/javascript">

function inQtyDispatched(p,d,r,c){
	var n=p.name.split("_")[0];
	var rate= parseFloat(document.getElementById(n+"_TaxRate").value);
	var taxprice= parseFloat(document.getElementById(n+"_TaxPrice").value);  
	//var currrate= parseFloat(document.getElementById(CurrRate").value);
	var total=p.value*taxprice;
	var tax=(total/(1+rate))*rate;
	var currp=0;
	document.getElementById(n+"_TaxTotal").value=tax.toFixed(2);
	document.getElementById(n+"_LineTotal").value=total.toFixed(2);
	if (c==1){
		currp= parseFloat(document.getElementById(n+"_CurrPrice").value); 
		document.getElementById(n+"_CurrTotal").value=(p.value*currp).toFixed(2); 

	}
	var taxtotal=0;
	var amototal=0;
	var currtotal=0;
	for(var i=0; i<r; i++){
			
		taxtotal=parseFloat(taxtotal)+parseFloat(document.getElementById(i+"_TaxTotal").value);
		amototal=parseFloat(amototal)+parseFloat(document.getElementById(i+"_LineTotal").value);
		if (c=1)
		currtotal=parseFloat(currtotal)+parseFloat(document.getElementById(i+"_CurrTotal").value);
	}
	
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
	document.getElementById("AmoTotal").value =amototal.toFixed(2);
	if (c==1){
			document.getElementById("CurrTotal").value =currtotal.toFixed(2);
	}
}

</script>';

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}
   $tag=1;   
if (!isset($_GET['OrderNumber']) AND !isset($_SESSION['ProcessingOrder'])) {
	/* This page can only be called with an order number for invoicing*/
	echo '<div class="centre">
			<a href="' . $RootPath . '/SelectSalesOrder.php">' . _('Select a sales order to invoice'). '</a>
		</div>
		<br />
		<br />';
	prnMsg( _('This page can only be opened if an order has been selected Please select an order first from the delivery details screen click on Confirm for invoicing'), 'error' );
	include ('includes/footer.php');
	exit;
} elseif (isset($_GET['OrderNumber']) and $_GET['OrderNumber']>0) {

	unset($_SESSION['Items'.$identifier]->LineItems);
	unset ($_SESSION['Items'.$identifier]);

	$_SESSION['ProcessingOrder']=(int)$_GET['OrderNumber'];
	$_GET['OrderNumber']=(int)$_GET['OrderNumber'];
	$_SESSION['Items'.$identifier] = new cart;

	/*read in all the guff from the selected order into the Items cart  */
  
	$OrderHeaderSQL = "SELECT salesorders.orderno,
								salesorders.debtorno,
								debtorsmaster.name,								
								salesorders.customerref,
								salesorders.comments,
								salesorders.orddate,
								salesorders.ordertype,
								salesorders.shipvia,
								salesorders.deliverto,
								salesorders.taxcatid,
								salesorders.taxrate,
								salesorders.currcode,
								salesorders.rate,
								
								salesorders.deladd1,
								salesorders.deladd2,
								salesorders.deladd3,
								salesorders.deladd4,
								salesorders.deladd5,
								salesorders.deladd6,
								salesorders.contactphone,
								salesorders.contactemail,
								salesorders.salesperson,
								salesorders.freightcost,
								salesorders.deliverydate,								
								salesorders.fromstkloc,								
								currencies.rate as currency_rate,
								currencies.decimalplaces								
						FROM salesorders 
						LEFT JOIN  debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno				
						INNER JOIN currencies
						ON salesorders.currcode = currencies.currabrev
						
						WHERE salesorders.orderno = '" . $_GET['OrderNumber']."'";
		//INNER JOIN locations		ON locations.loccode=salesorders.fromstkloc
		//	INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
					
		//	locations.taxprovinceid,
	if ($_SESSION['SalesmanLogin'] != '') {
		$OrderHeaderSQL .= " AND salesorders.salesperson='" . $_SESSION['SalesmanLogin'] . "'";
	}

	$ErrMsg = _('The order cannot be retrieved because');
	$DbgMsg = _('The SQL to get the order header was');
	//prnMsg('133开票出库'.$OrderHeaderSQL);
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$ErrMsg,$DbgMsg);

	if (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);

		$_SESSION['Items'.$identifier]->DebtorNo = $myrow['debtorno'];
		$_SESSION['Items'.$identifier]->OrderNo = $myrow['orderno'];
		$_SESSION['Items'.$identifier]->Branch = 1;//$myrow['branchcode'];
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
		$_SESSION['Items'.$identifier]->CustRef = $myrow['customerref'];
		$_SESSION['Items'.$identifier]->Comments = $myrow['comments'];
		$_SESSION['Items'.$identifier]->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$BestShipper = $myrow['shipvia'];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['shipvia'];

		if (is_null($BestShipper)){
		   $BestShipper=0;
		}
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items'.$identifier]->CurrCode = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->ExRate = $myrow['rate'];
		$_SESSION['Items'.$identifier]->TaxCatID = $myrow['taxcatid'];
		$_SESSION['Items'.$identifier]->TaxRate = $myrow['taxrate'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];

		$_SESSION['Items'.$identifier]->BrAdd1 = $myrow['deladd1'];
		$_SESSION['Items'.$identifier]->BrAdd2 = $myrow['deladd2'];
		$_SESSION['Items'.$identifier]->BrAdd3 = $myrow['deladd3'];
		$_SESSION['Items'.$identifier]->BrAdd4 = $myrow['deladd4'];
		$_SESSION['Items'.$identifier]->BrAdd5 = $myrow['deladd5'];
		$_SESSION['Items'.$identifier]->BrAdd6 = $myrow['deladd6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items'.$identifier]->Email = $myrow['contactemail'];
		$_SESSION['Items'.$identifier]->SalesPerson = $myrow['salesperson'];

		$_SESSION['Items'.$identifier]->Location = $myrow['fromstkloc'];
		$_SESSION['Items'.$identifier]->FreightCost = $myrow['freightcost'];
		$_SESSION['Old_FreightCost'] = $myrow['freightcost'];
		$_POST['ChargeFreightCost'] = $_SESSION['Old_FreightCost'];
		$_SESSION['Items'.$identifier]->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['CurrencyRate'] = $myrow['currency_rate'];
		$_SESSION['Items'.$identifier]->TaxGroup = $myrow['taxgroupid'];
		$_SESSION['Items'.$identifier]->DispatchTaxProvince = $myrow['taxprovinceid'];
		$_SESSION['Items'.$identifier]->GetFreightTaxes();
		$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow['specialinstructions'];

		DB_free_result($GetOrdHdrResult);

		/*now populate the line items array with the sales order details records */

		$LineItemsSQL = "SELECT stkcode,
								stockmaster.description,
								stockmaster.longdescription,
								stockmaster.controlled,
								stockmaster.serialised,
								stockmaster.volume,
								stockmaster.grossweight,
								stockmaster.units,
								stockmaster.decimalplaces,
								stockmaster.mbflag,
								stockmaster.taxcatid,
								stockmaster.discountcategory,
								salesorderdetails.unitprice,
								salesorderdetails.quantity,
								salesorderdetails.taxprice,
								salesorderdetails.currprice,
								salesorderdetails.discountpercent,
								salesorderdetails.actualdispatchdate,
								salesorderdetails.qtyinvoiced,
								salesorderdetails.narrative,
								salesorderdetails.orderlineno,
								salesorderdetails.poline,
								salesorderdetails.itemdue,
								stockmaster.materialcost + stockmaster.labourcost + stockmaster.overheadcost AS standardcost
							FROM salesorderdetails INNER JOIN stockmaster
							 	ON salesorderdetails.stkcode = stockmaster.stockid
							WHERE salesorderdetails.orderno ='" . $_GET['OrderNumber'] . "'
							AND salesorderdetails.quantity - salesorderdetails.qtyinvoiced >0
							ORDER BY salesorderdetails.orderlineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		$DbgMsg = _('The SQL that failed was');
		$LineItemsResult = DB_query($LineItemsSQL,$ErrMsg,$DbgMsg);
		//prnMsg('228读取未发货'.$LineItemsSQL);
		$r=0;
		if (DB_num_rows($LineItemsResult)>0) {
			//
			while ($myrow=DB_fetch_array($LineItemsResult)) {
            
				$_SESSION['Items'.$identifier]->add_to_cart($myrow['stkcode'],//1
															$myrow['quantity'],
															$myrow['description'],
															$myrow['longdescription'],
															$myrow['unitprice'],
															$myrow['discountpercent'],
															$myrow['units'],
															
															$myrow['currprice'],
															$myrow['taxprice'],				
															$_SESSION['Items'.$identifier]->TaxRate,	

															$myrow['volume'],
															$myrow['grossweight'],														       
															0,//		$Amount,           
															0,//		$CurrAmount,   14   
															0,
															$myrow['mbflag'],
															$myrow['actualdispatchdate'],
															$myrow['qtyinvoiced'],
															$myrow['discountcategory'],
															$myrow['controlled'],//20
															$myrow['serialised'],
															$myrow['decimalplaces'],
														
														
															htmlspecialchars_decode($myrow['narrative']),
															'No',
															$_SESSION['Items'.$identifier]->LineCounter,//$LineNumber	
															$myrow['taxcatid'],			
															'',
															'',
															$myrow['orderlineno'],			$myrow['standardcost'],	
															1,
															0,
															1,// ExRate					
															$identifier,
															0);	/*NB NO Updates to DB */

				/*Calculate the taxes applicable to this line item from the customer branch Tax Group and Item Tax Category */

				//$_SESSION['Items'.$identifier]->GetTaxes($myrow['orderlineno']);

			} /* line items from sales order details */
			//var_dump($_SESSION['Items'.$identifier]->LineItems );
		} else { /* there are no line items that have a quantity to deliver */
			echo '<br />';
			prnMsg( _('There are no ordered items with a quantity left to deliver. There is nothing left to invoice'));
			include('includes/footer.php');
			exit;

		} //end of checks on returned data set
		DB_free_result($LineItemsResult);

	} else { /*end if the order was returned sucessfully */
         //无法检索此订单项。请选择其他订单
		echo '<br />' .
		prnMsg('287'. _('This order item could not be retrieved. Please select another order'), 'warn');
		include ('includes/footer.php');
		exit;
	} //valid order returned from the entered order number
} else {

	/* if processing, a dispatch page has been called and ${$StkItm->LineNumber} would have been set from the post
	set all the necessary session variables changed by the POST  */
	if (isset($_POST['ShipVia'])){
		$_SESSION['Items'.$identifier]->ShipVia = $_POST['ShipVia'];
	}
	if (isset($_POST['ChargeFreightCost'])){
		$_SESSION['Items'.$identifier]->FreightCost = filter_number_format($_POST['ChargeFreightCost']);
	}
	$i=1;
	foreach ($_SESSION['Items'.$identifier]->FreightTaxes as $FreightTaxLine) {
		if (isset($_POST['FreightTaxRate'  . $i])){
			$_SESSION['Items'.$identifier]->FreightTaxes[$i]->TaxRate = filter_number_format($_POST['FreightTaxRate'  . $i])/100;
		}
		$i++;
	}

	foreach ($_SESSION['Items'.$identifier]->LineItems as $Itm) {
		if (sizeOf($Itm->SerialItems) > 0) {
			$_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyDispatched = 0; //initialise QtyDispatched
			foreach ($Itm->SerialItems as $SerialItem) { //calculate QtyDispatched from bundle quantities
				$_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyDispatched += $SerialItem->BundleQty;
			}
			//Preventing from dispatched more than ordered. Since it's controlled items, users must select the batch/lot again.
			if($_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyDispatched > ($_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->Quantity - $_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyInv)){
				prnMsg(_('Dispathed Quantity should not be more than order balanced quantity').'. '._('To dispatch quantity is').' '.$_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyDispatched.' '._('And the order balance is ').' '.($_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->Quantity - $_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyInv),'error');
				include('includes/footer.php');
				exit;
			}
		} else if (isset($_POST[$Itm->LineNumber .  '_QtyDispatched' ])){
			if (is_numeric(filter_number_format($_POST[$Itm->LineNumber .  '_QtyDispatched' ]))
				AND filter_number_format($_POST[$Itm->LineNumber .  '_QtyDispatched']) <= ($_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->Quantity - $_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyInv)){

				$_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->QtyDispatched = round(filter_number_format($_POST[$Itm->LineNumber  . '_QtyDispatched']),$Itm->DecimalPlaces);
			}
		}
		$i=1;
		foreach ($Itm->Taxes as $TaxLine) {
			if (isset($_POST[$Itm->LineNumber  . $i . '_TaxRate'])){
				$_SESSION['Items'.$identifier]->LineItems[$Itm->LineNumber]->Taxes[$i]->TaxRate = filter_number_format($_POST[$Itm->LineNumber  . $i . '_TaxRate'])/100;
			}
			$i++;
		}
	} //end foreach lineitem

}

/* Always display dispatch quantities and recalc freight for items being dispatched */
$SQL="SELECT `taxcatid`, `taxcatname`, `taxrate` FROM `taxcategories`  WHERE `onorder` IN (2,3)";
$Result=DB_query($SQL);
while($row=DB_fetch_array($Result)){
	$TaxCat[$row['taxcatid']]=array($row['taxcatname'],$row['taxrate']);
}

if ($_SESSION['Items'.$identifier]->SpecialInstructions) {
  prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Confirm Invoice') .
	'" alt="" />' . ' ' .$Title. '</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">
			<tr>
				<td class="label" ><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') . '" alt="" />客户编码/名称 :<b></td>
				<td colspan="2"><b> [' .$_SESSION['Items'.$identifier]->DebtorNo .']'. $_SESSION['Items'.$identifier]->CustomerName. '</b></td>
			
			</tr>
			<tr>
				<td class="label">合同号:</td><td>'.$_SESSION['Items'.$identifier]->OrderNo.'</td><td class="label"><b>' . _('Invoice amounts stated in') . '</b></td>
				     <td><b> ' . $_SESSION['Items'.$identifier]->DefaultCurrency . '</b></td>
			</tr>
			<tr>
			<td class="label">客户合同号:</td>
			<td>'.$_SESSION['Items'.$identifier]->CustRef.'</td><td></td><td></td>
		</tr>
	</table><br />';


/***************************************************************
	Line Item Display
***************************************************************/
$curr=2;
//调试查看合同内容var_dump($_SESSION['Items'.$identifier]->LineItems);
$rw= $_SESSION['Items'.$identifier]->LineCounter;
//var_dump($_SESSION['Items'.$identifier]);
echo '<table width="90%" cellpadding="2" class="selection">
	<tr>
		<th>' . _('Item Code') . '</th>
		<th>' . _('Item Description' ) . '</th>
		<th>' . _('Ordered') . '</th>
		<th>' . _('Units') . '</th>
		<th>' . _('Already') . '<br />' . _('Sent') . '</th>
		<th>' . _('This Dispatch') .'</th>';
		if ( $_SESSION['Items'.$identifier]->CurrCode!=CURR){
			$curr=1;
			echo'<th>订单价目<br/>['.$_SESSION['Items'.$identifier]->CurrCode.  ']</th>';
		}
		echo'<th>订单价目<br/> ['.CURR.  ']</th>
			<th>税目</th>
			<th>税额</th>
			<th>['.$_SESSION['CountryOfOperation'].']合计 </th>';
		if ( $_SESSION['Items'.$identifier]->CurrCode!=CURR){
			echo'<th>['.$_SESSION['Items'.$identifier]->CurrCode.']合计</th>';
		}
	echo'</tr>';

	$_SESSION['Items'.$identifier]->total = 0;
	$_SESSION['Items'.$identifier]->totalVolume = 0;
	$_SESSION['Items'.$identifier]->totalWeight = 0;
	$TaxTotals = array();
	$TaxGLCodes = array();
	$TaxTotal =0;
	$CurrTotal=0;
	/*show the line items on the order with the quantity being dispatched available for modification */

	$k=0; //row colour counter
	$j=0;
	$AmoTotal=0;
	//prnMsg( $_SESSION['Items'.$identifier]->LineCounter .'='.count($_SESSION['Items'.$identifier]->LineItems));
	$CurrPOI=$_SESSION['Items'.$identifier]->CurrDecimalPlaces;
foreach ($_SESSION['Items'.$identifier]->LineItems as $LnItm) {

	$j++;
	if ($k==1){
		$RowStarter = '<tr class="EvenTableRows">';
		$k=0;
	} else {
		$RowStarter = '<tr class="OddTableRows">';
		$k=1;
	}
	$_SESSION['Items'.$identifier]->total += $LineTotal;
	$_SESSION['Items'.$identifier]->totalVolume += ($LnItm->QtyDispatched * $LnItm->Volume);
	$_SESSION['Items'.$identifier]->totalWeight += ($LnItm->QtyDispatched * $LnItm->Weight);

    echo $RowStarter;
	echo '<td>' . $LnItm->StockID .'-'. $_SESSION['Items'.$identifier]->TaxRate.'</td>
		<td title="'. $LnItm->LongDescription . '">' .$LnItm->ItemDescription . '</td>
		<td class="number">' . locale_number_format($LnItm->Quantity,$LnItm->DecimalPlaces) . '</td>
		<td>' . $LnItm->Units . '</td>
		<td class="number">' . locale_number_format($LnItm->QtyInv,$LnItm->DecimalPlaces) . '</td>';
	if ($LnItm->Controlled==1){

		if (isset($_POST['ProcessInvoice'])) {
			echo '<td class="number">' . locale_number_format($LnItm->QtyDispatched,$LnItm->DecimalPlaces) . '</td>';
		} else {
			echo '<td class="number"><input type="hidden" name="' . $LnItm->LineNumber . '_QtyDispatched"  value="' . $LnItm->QtyDispatched . '" />
			        <a href="' . $RootPath .'/ConfirmDispatchControlled_Invoice.php?identifier=' . $identifier . '&amp;LineNo='. $LnItm->LineNumber.'">' .locale_number_format($LnItm->QtyDispatched,$LnItm->DecimalPlaces) . '</a></td>';
		}
	} else {
		if (isset($_POST['ProcessInvoice'])) {
			echo '<td class="number">' .  locale_number_format($LnItm->QtyDispatched,$LnItm->DecimalPlaces) . '</td>';
		} else {//发出数
			echo '<td class="number"><input tabindex="'.$j.'" type="text" ' . ($j==1 ? 'autofocus="autofocus" ':'') . ' class="number" required="required" title="' . _('Enter the quantity to charge the customer for, that has been dispatched') . '" name="' . $LnItm->LineNumber . '_QtyDispatched" id="' . $LnItm->LineNumber . '_QtyDispatched" maxlength="8" size="8"  onChange="inQtyDispatched(this,'.$LnItm->DecimalPlaces .' ,'.$rw.','.$curr.' )" value="' . locale_number_format($LnItm->QtyDispatched,$LnItm->DecimalPlaces) . '" /></td>';
		}
	}

	$LineTotal = $LnItm->QtyDispatched * $LnItm->TaxPrice ;//* (1 - $LnItm->DiscountPercent);
	$DisplayLineNetTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
	if ( $_SESSION['Items'.$identifier]->CurrCode!=CURR){
        $col=4;
		echo '<td class="number">'.locale_number_format( $LnItm->CurrPrice ,$CurrPOI).
		       '<input   type="hidden"  id="' . $LnItm->LineNumber . '_CurrPrice"    value="' .  $LnItm->CurrPrice . '"   />';
		
	}else{
		$col=3;
		echo'<input  type="hidden"  id="' . $LnItm->LineNumber . '_CurrPrice"    value=""   />';
	}
	
	
	$LineTaxTotal=(($LnItm->QtyDispatched*$LnItm->TaxPrice)/ (1+$_SESSION['Items'.$identifier]->TaxRate))*$_SESSION['Items'.$identifier]->TaxRate;
	echo '<td class="number">' .locale_number_format( $LnItm->TaxPrice,$_SESSION['Items'.$identifier]->CurrDecimalPlaces) . '
	       		<input  type="hidden"  name="' . $LnItm->LineNumber . '_TaxPrice" id="' . $LnItm->LineNumber . '_TaxPrice"   value="' . $LnItm->TaxPrice . '" /></td>
		  <td class="number">' .$TaxCat[ $_SESSION['Items'.$identifier]->TaxCatID][0] . '
		  		<input  type="hidden"  name="' . $LnItm->LineNumber . '_TaxRate" id="' . $LnItm->LineNumber . '_TaxRate"   value="' .$_SESSION['Items'.$identifier]->TaxRate. '" /></td>';
		//合计
		echo '<td class="number"><input type="text"  class="number"  name="' . $LnItm->LineNumber . '_TaxTotal"  id="' . $LnItm->LineNumber . '_TaxTotal"  maxlength="5" size="5"  value="' . locale_number_format($LineTaxTotal ,POI) . '" readonly="readonly"  /></td>';
		echo '<td class="number"><input  type="text"  class="number"  name="' . $LnItm->LineNumber . '_LineTotal" id="' . $LnItm->LineNumber . '_LineTotal" maxlength="8" size="8"   value="' . locale_number_format($LineTotal,POI) . '" readonly="readonly"  /></td>';
	if ( $_SESSION['Items'.$identifier]->CurrCode!=CURR){
		$LineCurrTotal=$LnItm->QtyDispatched * $LnItm->CurrPrice; 
		echo '<td class="number"><input  type="text"  id="' . $LnItm->LineNumber . '_CurrTotal"  maxlength="8" size="8"    value="' . locale_number_format($LineCurrTotal ,$CurrPOI) . '"   readonly="readonly" />';
		
	}else{
		echo'<input  type="hidden"  id="' . $LnItm->LineNumber . '_CurrTotal"    value="' . locale_number_format($CurrTotal,$CurrPOI) . '"   />';
	}
	$AmoTotal+=$LineTotal;	
	$TaxTotal += $LineTaxTotal;
	$CurrTotal+=$LineCurrTotal;
	//$DisplayGrossLineTotal = locale_number_format($LineTotal+ $TaxLineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
	if ($LnItm->Controlled==1){
		if (!isset($_POST['ProcessInvoice'])) {
			echo '<td><a href="' . $RootPath . '/ConfirmDispatchControlled_Invoice.php?identifier=' . $identifier . '&amp;LineNo='. $LnItm->LineNumber.'">';
			if ($LnItm->Serialised==1){
				echo _('Enter Serial Numbers');
			} else { /*Just batch/roll/lot control */
				echo _('Enter Batch/Roll/Lot #');
			}
			echo '</a></td>';
		}
	}
	echo '</tr>';
	if (mb_strlen($LnItm->Narrative)>1){
		$Narrative=str_replace('\r\n','<br />', $LnItm->Narrative);
		echo $RowStarter . '<td colspan="12">' . stripslashes($Narrative) . '</td></tr>';
	}
}//end foreach ($line)
    //以下代码也没有使用-531
if(!isset($_SESSION['Items'.$identifier]->FreightCost)) {
	if ($_SESSION['DoFreightCalc']==True){
		list ($FreightCost, $BestShipper) = CalcFreightCost($_SESSION['Items'.$identifier]->total,
														$_SESSION['Items'.$identifier]->BrAdd2,
														$_SESSION['Items'.$identifier]->BrAdd3,
														$_SESSION['Items'.$identifier]->BrAdd4,
														$_SESSION['Items'.$identifier]->BrAdd5,
														$_SESSION['Items'.$identifier]->BrAdd6,
														$_SESSION['Items'.$identifier]->totalVolume,
														$_SESSION['Items'.$identifier]->totalWeight,
														$_SESSION['Items'.$identifier]->Location,
														$_SESSION['Items'.$identifier]->DefaultCurrency,
														$db);
		$_SESSION['Items'.$identifier]->ShipVia = $BestShipper;
	}
  	if (is_numeric($FreightCost)){
		$FreightCost = $FreightCost / $_SESSION['CurrencyRate'];
  	} else {
		$FreightCost =0;
  	}
  	if (!is_numeric($BestShipper)){
  		$SQL =  "SELECT shipper_id FROM shippers WHERE shipper_id='" . $_SESSION['Default_Shipper'] . "'";
		$ErrMsg = _('There was a problem testing for a default shipper because');
		$TestShipperExists = DB_query($SQL, $ErrMsg);
		if (DB_num_rows($TestShipperExists)==1){
			$BestShipper = $_SESSION['Default_Shipper'];
		} else {
			$SQL =  "SELECT shipper_id FROM shippers";
			$ErrMsg = _('There was a problem testing for a default shipper');
			$TestShipperExists = DB_query($SQL, $ErrMsg);
			if (DB_num_rows($TestShipperExists)>=1){
				$ShipperReturned = DB_fetch_row($TestShipperExists);
				$BestShipper = $ShipperReturned[0];
			} else {
				//未定义发货人“）.”。_（'请使用下面的链接来设置货运公司，系统希望选择货运公司或使用默认货运公司
				prnMsg( _('There are no shippers defined') . '. ' . _('Please use the link below to set up shipping freight companies, the system expects the shipping company to be selected or a default freight company to be used'),'error');
				echo '<a href="' . $RootPath . 'Shippers.php">' .  _('Enter') . '/' . _('Amend Freight Companies'). '</a>';
			}
		}
	}
}
$DisplaySubTotal = locale_number_format(($_SESSION['Items'.$identifier]->total + filter_number_format($_POST['ChargeFreightCost'])),$_SESSION['Items'.$identifier]->CurrDecimalPlaces);

echo '<tr>';
echo'<td  colspan="5" >['.$_SESSION['Items'.$identifier]->CurrCode.']汇率:<input name="CurrRate"  id="CurrRate" maxlength="4" size="4"   type="text" value="'.$_SESSION['Items'.$identifier]->ExRate.'" readonly="readonly" /></td>';	  

echo'<td colspan="'.$col.'" class="number">' . _('Invoice Totals'). '</td>';
echo '<td class="number"><input  type="text"  class="number"  id="TaxTotal" maxlength="8" size="8"   value="' . locale_number_format($TaxTotal,$CurrPOI) . '" readonly="readonly"  /></td>
	  <td class="number"><input  type="text"  class="number"  id="AmoTotal" maxlength="8" size="8"   value="' . locale_number_format($AmoTotal,$CurrPOI) . '" readonly="readonly"  /></td>';
if ( $_SESSION['Items'.$identifier]->CurrCode!=CURR){
	echo'<td class="number"><input  type="text"   id="CurrTotal" maxlength="8" size="8"   value="' . locale_number_format($CurrTotal,$CurrPOI) . '" readonly="readonly"  /></td>';
}else{
	echo'<input  type="hidden"  id="CurrTotal"    value="' . locale_number_format($CurrTotal,$CurrPOI) . '"   />';
}
	echo'</tr>';


if (! isset($_POST['DispatchDate']) OR  ! Is_Date($_POST['DispatchDate'])){
	$DefaultDispatchDate = Date($_SESSION['DefaultDateFormat'],CalcEarliestDispatchDate());
} else {
	$DefaultDispatchDate = $_POST['DispatchDate'];
}

echo '</table><br />';

if (isset($_POST['ProcessInvoice']) && $InputErr==false AND $_POST['ProcessInvoice'] != ''){
  
	/* SQL to process the postings for sales invoices...

	/*First check there are lines on the dipatch with quantities to invoice
	invoices can have a zero amount but there must be a quantity to invoice */

	$QuantityInvoicedIsPositive = false;
	$StandardCostErr=false;
	//以下为检查库存是否为负及成本为0,暂时关闭
	
	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
		/*
		if ($OrderLine->QtyDispatched > 0){//检查库存
			$QuantityInvoicedIsPositive =true;
		}
		if ($OrderLine->QtyDispatched>0  && $OrderLine->StandardCost== 0){
			$StandardCostErr=true;
		}*/
		if ( $OrderLine->StandardCost== 0){
			$StandardCostErr=true;
		}
	}
	/*
	if (! $QuantityInvoicedIsPositive){
		//此订单上没有要发票数量的行。_没有做进一步的处理
		prnMsg( _('There are no lines on this order with a quantity to invoice') . '. ' . _('No further processing has been done'),'error');
		include('includes/footer.php');
		exit;
	}*/
	if ($StandardCostErr){
		prnMsg("您要发货的产品没有成本价格,请补录成本价格后发货!","warn");
		//include('includes/footer.php');
		//exit;
	}
	
	if ($_SESSION['ProhibitNegativeStock']==1){ // checks for negative stock after processing invoice
		//sadly this check does not combine quantities occuring twice on and order and each line is considered individually :-(
		$NegativesFound = false;
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {
			$SQL = "SELECT stockmaster.description,
					   		locstock.quantity,
					   		stockmaster.mbflag
		 			FROM locstock
		 			INNER JOIN stockmaster
					ON stockmaster.stockid=locstock.stockid
					WHERE stockmaster.stockid='" . $OrderLine->StockID . "'
					AND locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'";

			$ErrMsg = _('Could not retrieve the quantity left at the location once this order is invoiced (for the purposes of checking that stock will not go negative because)');
			$Result = DB_query($SQL,$ErrMsg);
			$CheckNegRow = DB_fetch_array($Result);
			if (($CheckNegRow['mbflag']=='B' OR $CheckNegRow['mbflag']=='M') AND mb_substr($OrderLine->StockID,0,4)!='ASSET'){
				if ($CheckNegRow['quantity'] < $OrderLine->QtyDispatched){
					//为所选订单开具发票将导致���库存。系统参数设置为禁止出现负库存。在纠正库存之前，不能创建此发票
					prnMsg( _('Invoicing the selected order would result in negative stock. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$OrderLine->StockID . ' ' . $CheckNegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
					$NegativesFound = true;
				}
			} elseif ($CheckNegRow['mbflag']=='A') {

				/*Now look for assembly components that would go negative */
				$SQL = "SELECT bom.component,
							   stockmaster.description,
							   locstock.quantity-(" . $OrderLine->QtyDispatched  . "*bom.quantity) AS qtyleft
						FROM bom
						INNER JOIN locstock
						ON bom.component=locstock.stockid
						INNER JOIN stockmaster
						ON stockmaster.stockid=bom.component
						WHERE bom.parent='" . $OrderLine->StockID . "'
						AND locstock.loccode='" . $_SESSION['Items'.$identifier]->Location . "'
                        AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                        AND bom.effectiveto > '" . date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve the component quantity left at the location once the assembly item on this order is invoiced (for the purposes of checking that stock will not go negative because)');
				$Result = DB_query($SQL,$ErrMsg);
				while ($NegRow = DB_fetch_array($Result)){
					if ($NegRow['qtyleft']<0){
						//对所选订单进行nvoi操作将导致订单上装配项目的组件的库存为负。系统参数设置为禁止出现负库存。在纠���库存之前，不能创建此发票
						prnMsg(_('Invoicing the selected order would result in negative stock for a component of an assembly item on the order. The system parameters are set to prohibit negative stocks from occurring. This invoice cannot be created until the stock on hand is corrected.'),'error',$NegRow['component'] . ' ' . $NegRow['description'] . ' - ' . _('Negative Stock Prohibited'));
						$NegativesFound = true;
					} // end if negative would result
				} //loop around the components of an assembly item
			}//end if its an assembly item - check component stock

		} //end of loop around items on the order for negative check

		if ($NegativesFound){
            echo '</div>';
            echo '</form>';
            echo '<div class="centre">
					<input type="submit" name="Update" value="' . _('Update'). '" /></div>';
			include('includes/footer.php');
			exit;
		}

	}//end of testing for negative stocks


		/* Now Get the area where the sale is to from the branches table */
		/*
		$SQL = "SELECT area,
						defaultshipvia
				FROM custbranch
				WHERE custbranch.debtorno ='". $_SESSION['Items'.$identifier]->DebtorNo . "'
				AND custbranch.branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "'";

		$ErrMsg = _('We were unable to load Area where the Sale is to from the BRANCHES table') . '. ' . _('Please remedy this');
		$Result = DB_query($SQL, $ErrMsg);
		$myrow = DB_fetch_row($Result);
		$Area = $myrow[0];
		$DefaultShipVia = $myrow[1];
		DB_free_result($Result);*/

		/*company record read in on login with info on GL Links and debtors GL account*/

	if ($_SESSION['CompanyRecord']==0){
		/*由于某种原因，无法检索公司数据和首选项The company data and preferences could not be retrieved for some reason */
		prnMsg( _('The company information and preferences could not be retrieved') . ' - ' . _('see your system administrator'), 'error');
		include('includes/footer.php');
		exit;
	}

	/*现在需要检查订单详细信息是否与读取到Items数组时相同。如果他们改变了，那么可能是其他人给他们开了发票Now need to check that the order details are the same as they were when they were read into the Items array. If they've changed then someone else may have invoiced them */

		$SQL = "SELECT stkcode,
						quantity,
						qtyinvoiced,
						orderlineno
					FROM salesorderdetails
					WHERE completed=0 AND quantity-qtyinvoiced > 0
					AND orderno = '" . $_SESSION['ProcessingOrder']."'";

		$Result = DB_query($SQL);
		//prnMsg($SQL);
		if (DB_num_rows($Result) != count($_SESSION['Items'.$identifier]->LineItems)){

		/*there should be the same number of items returned from this query as there are lines on the invoice - if  not 	then someone has already invoiced or credited some lines */

			if ($debug==1){
				echo '<br />' . $SQL;
				echo '<br />' . _('Number of rows returned by SQL') . ':' . DB_num_rows($Result);
				echo '<br />' . _('Count of items in the session') . ' ' . count($_SESSION['Items'.$identifier]->LineItems);
			}

			echo '<br />';
			//此订单已更改或开具发票，因为此交货已开始确认“）.”。_（“处理已停止”）。_（'输入并确认此发送'）。'/' . _（'发票必须重新选择订单并重新读取，以更新其他用户所做的更改
			prnMsg('710'. _('This order has been changed or invoiced since this delivery was started to be confirmed') . '. ' . _('Processing halted') . '. ' . _('To enter and confirm this dispatch') . '/' . _('invoice the order must be re-selected and re-read again to update the changes made by the other user'), 'error');

			unset($_SESSION['Items'.$identifier]->LineItems);
			unset($_SESSION['Items'.$identifier]);
			unset($_SESSION['ProcessingOrder']);
			include('includes/footer.php'); exit;
		}

		$Changes =0;

		while ($myrow = DB_fetch_array($Result)) {
			//prnMsg($_SESSION['Items'.$identifier]->LineItems[$myrow['orderlineno']]->Quantity.' !='. $myrow['quantity']);
			//prnMsg($_SESSION['Items'.$identifier]->LineItems[$myrow['orderlineno']]->QtyInv.' !='. $myrow['qtyinvoiced']);
			if ($_SESSION['Items'.$identifier]->LineItems[$myrow['orderlineno']]->Quantity != $myrow['quantity']
				OR $_SESSION['Items'.$identifier]->LineItems[$myrow['orderlineno']]->QtyInv != $myrow['qtyinvoiced']) {

				echo '<br />' .  _('Orig order for'). ' ' . $myrow['orderlineno'] . ' '. _('has a quantity of'). ' ' . $myrow['quantity'] . ' '. _('and an invoiced qty of'). ' ' . $myrow['qtyinvoiced'] . ' '. _('the session shows quantity of'). ' ' . $_SESSION['Items'.$identifier]->LineItems[$myrow['orderlineno']]->Quantity . ' ' . _('and quantity invoice of'). ' ' . $_SESSION['Items'.$identifier]->LineItems[$myrow['orderlineno']]->QtyInv;
                //'此订单已更改或开具发票，因为已开始确认此交货'（“处理已停���。”）（'若要输入并确认此分派，必须重新选择并重新读取此分派以更新其他用户所做的更改'）
				prnMsg('728'. _('This order has been changed or invoiced since this delivery was started to be confirmed') . ' ' . _('Processing halted.') . ' ' . _('To enter and confirm this dispatch, it must be re-selected and re-read again to update the changes made by the other user'), 'error');

				echo '<br />';

				echo '<div class="centre"><a href="'. $RootPath . '/SelectSalesOrder.php">' .  _('Select a sales order for confirming deliveries and invoicing'). '</a></div>';

				unset($_SESSION['Items'.$identifier]->LineItems);
				unset($_SESSION['Items'.$identifier]);
				unset($_SESSION['ProcessingOrder']);
				include('includes/footer.php');
				exit;
			}
		} /*loop through all line items of the order to ensure none have been invoiced since started looking at this order*/

		DB_free_result($Result);

	// *************************************************************************
	//   S T A R T   O F   I N V O I C E   S Q L   P R O C E S S I N G
	// *************************************************************************

	/*Now Get the next invoice number - function in SQL_CommonFunctions*/

	$InvoiceNo = GetNextTransNo(10, $db);
	$PeriodNo = GetPeriod($DefaultDispatchDate, $db);

	/*Start an SQL transaction */

	DB_Txn_Begin();

	$DefaultDispatchDate = FormatDateForSQL($DefaultDispatchDate);

	/*Update order header for invoice charged on */
	$SQL = "UPDATE salesorders
			SET comments = CONCAT(comments,' Inv ','" . $InvoiceNo . "')
			WHERE orderno= '" . $_SESSION['ProcessingOrder']."'";

	$ErrMsg = _('CRITICAL ERROR') . ' ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order header could not be updated with the invoice number');
	$DbgMsg = _('The following SQL to update the sales order was used');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	
	/* If balance of the order cancelled update sales order details quantity. Also insert log records for OrderDeliveryDifferencesLog */

	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {	

		/*Test to see if the item being sold is an asset */
		if (mb_substr($OrderLine->StockID,0,6)=='ASSET-'){
			$IsAsset = true;
			$HyphenOccursAt = mb_strpos($OrderLine->StockID,'-',6);
			if ($HyphenOccursAt == false){
				$AssetNumber =   intval(mb_substr($OrderLine->StockID,6));
			} else {
				$AssetNumber =   intval(mb_substr($OrderLine->StockID,6,mb_strlen($OrderLine->StockID)-$HyphenOccursAt-1));
			}
			prnMsg (_('The asset number being disposed of is:') . ' ' . $AssetNumber, 'info');
		} else {
			$IsAsset = false;
			$AssetNumber = 0;
		}
		   //以下代码日志记录没有执行掺入
		   //prnMsg($_POST['BOPolicy'].'='.$OrderLine->Quantity .'[-]'. $OrderLine->QtyDispatched);
		if ($_POST['BOPolicy']=='CAN'){

			$SQL = "UPDATE salesorderdetails
					SET quantity = quantity - " . ($OrderLine->Quantity - $OrderLine->QtyDispatched - $OrderLine->QtyInv) . "
					WHERE orderno = '" . $_SESSION['ProcessingOrder'] . " '
						AND orderlineno = '" . $OrderLine->LineNumber . "'";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated because');
			$DbgMsg = _('The following SQL to update the sales order detail record was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);


			if (($OrderLine->Quantity - $OrderLine->QtyDispatched)>0){

				$SQL = "INSERT INTO orderdeliverydifferenceslog (orderno,
															invoiceno,
															stockid,
															quantitydiff,
															debtorno,
															branch,
															can_or_bo)
														VALUES (
															'" . $_SESSION['ProcessingOrder'] . "',
															'" . $InvoiceNo . "',
															'" . $OrderLine->StockID . "',
															'" . ($OrderLine->Quantity - $OrderLine->QtyDispatched  - $OrderLine->QtyInv) . "',
															'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
															'" . $_SESSION['Items'.$identifier]->Branch . "',
															'CAN')";

				$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The order delivery differences log record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the order delivery differences record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}

		} elseif (($OrderLine->Quantity - $OrderLine->QtyDispatched)>0
				AND DateDiff(ConvertSQLDate($DefaultDispatchDate),$_SESSION['Items'.$identifier]->DeliveryDate,'d')>0) {

			/*The order is being short delivered after the due date - need to insert a delivery differnce log */

			$SQL = "INSERT INTO orderdeliverydifferenceslog (orderno,
															invoiceno,
															stockid,
															quantitydiff,
															debtorno,
															branch,
															can_or_bo
														)
												VALUES (
													'" . $_SESSION['ProcessingOrder'] . "',
													'" . $InvoiceNo . "',
													'" . $OrderLine->StockID . "',
													'" . ($OrderLine->Quantity - $OrderLine->QtyDispatched  - $OrderLine->QtyInv) . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
													'" . $_SESSION['Items'.$identifier]->Branch . "',
													'BO'
												)";

			$ErrMsg =  '<br />' . _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The order delivery differences log record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the order delivery differences record was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		} /*end of order delivery differences log entries */

		/*现在更新已开票数量和实际发货日期的SalesOrderDetails
		//Now update SalesOrderDetails for the quantity invoiced and the actual dispatch dates. */

		if ($OrderLine->QtyDispatched !=0 AND $OrderLine->QtyDispatched!='' AND $OrderLine->QtyDispatched) {

			// Test above to see if the line is completed or not
			if ($OrderLine->QtyDispatched>=($OrderLine->Quantity - $OrderLine->QtyInv) OR $_POST['BOPolicy']=='CAN'){
				$SQL = "UPDATE salesorderdetails
							SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
								actualdispatchdate = '" . $DefaultDispatchDate .  "',
								completed=1
							WHERE orderno = '" . $_SESSION['ProcessingOrder'] . "'
							AND orderlineno = '" . $OrderLine->LineNumber . "'";
			} else {
				$SQL = "UPDATE salesorderdetails
							SET qtyinvoiced = qtyinvoiced + " . $OrderLine->QtyDispatched . ",
								actualdispatchdate = '" . $DefaultDispatchDate .  "'
							WHERE orderno = '" . $_SESSION['ProcessingOrder'] . "'
							AND orderlineno = '" . $OrderLine->LineNumber . "'";

			}

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales order detail record could not be updated because');
			$DbgMsg = _('The following SQL to update the sales order detail record was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			 /* Update location stock records if not a dummy stock item
			 need the MBFlag later too so save it to $MBFlag */
			$Result = DB_query("SELECT mbflag
								FROM stockmaster
								WHERE stockid = '" . $OrderLine->StockID . "'",
								 _('Cannot retrieve the mbflag'));

			$myrow = DB_fetch_row($Result);
			$MBFlag = $myrow[0];

			if ($MBFlag=='B' OR $MBFlag=='M') {
				$Assembly = False;

				/* Need to get the current location quantity
				will need it later for the stock movement */
               	$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $OrderLine->StockID . "'
						AND loccode= '" . $_SESSION['Items'.$identifier]->Location . "'";
				$ErrMsg = _('WARNING') . ': ' . _('Could not retrieve current location stock');
				$Result = DB_query($SQL, $ErrMsg);

				if (DB_num_rows($Result)==1){
                       			$LocQtyRow = DB_fetch_row($Result);
                       			$QtyOnHandPrior = $LocQtyRow[0];
				} else {
					/* There must be some error this should never happen */
					$QtyOnHandPrior = 0;
				}

				$SQL = "UPDATE locstock
						SET quantity = locstock.quantity - " . $OrderLine->QtyDispatched . "
						WHERE locstock.stockid = '" . $OrderLine->StockID . "'
						AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR   SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated because');
				$DbgMsg = _('The following SQL to update the location stock record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			} else if ($MBFlag=='A'){ //是装配物料  its an assembly 
				/*v需要得到这个零件的物料清单组件的库存移动，然后更新位置库存余额N
				eed to get the BOM for this part and make	stock moves for the components then update the Location stock balances */
				$Assembly=True;
				//prnMsg('956 A');
				$StandardCost =0; /*To start with - accumulate the cost of the comoponents for use in journals later on */
				$SQL = "SELECT bom.component,
								bom.quantity,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standard
							FROM bom INNER JOIN stockmaster
							ON bom.component=stockmaster.stockid
							WHERE bom.parent='" . $OrderLine->StockID . "'
                            AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                            AND bom.effectiveto > '" . date('Y-m-d') . "'";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not retrieve assembly components from the database for'). ' '. $OrderLine->StockID . _('because').' ';
				$DbgMsg = _('The SQL that failed was');
				$AssResult = DB_query($SQL,$ErrMsg,$DbgMsg,true);

				while ($AssParts = DB_fetch_array($AssResult)){

					$StandardCost += ($AssParts['standard'] * $AssParts['quantity']) ;
					/* Need to get the current location quantity
					will need it later for the stock movement */
	                  		$SQL="SELECT locstock.quantity
							FROM locstock
							WHERE locstock.stockid='" . $AssParts['component'] . "'
							AND loccode= '" . $_SESSION['Items'.$identifier]->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Can not retrieve assembly components location stock quantities because ');
					$DbgMsg = _('The SQL that failed was');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	                  		if (DB_num_rows($Result)==1){
	                  			$LocQtyRow = DB_fetch_row($Result);
	                  			$QtyOnHandPrior = $LocQtyRow[0];
					} else {
						/*There must be some error this should never happen */
						$QtyOnHandPrior = 0;
					}
					if (empty($AssParts['standard'])) {
						$AssParts['standard']=0;
					}
					$SQL="SELECT newqoh,
								newamount
						FROM  stockmoves
						WHERE  stockid='" . $AssParts['component']  . "'
						AND stkmoveno IN (SELECT  stkmoveno	FROM   stockmoves 	GROUP BY stockid	HAVING   MAX(stkmoveno))";
					$Result = DB_query($SQL);
					$LocRow = DB_fetch_assoc($Result);
					//存货价格
					if (empty($LocRow['newqoh'])){
						$OldQoh = 0;
						$OldAmount=0;
					
					}else{
						$OldQoh = $LocRow['newqoh'];
						$OldAmount = $LocRow['newamount'];
					
				
					} 
					$SQL = "INSERT INTO stockmoves (stockid,
													type,
													transno,
													loccode,
													trandate,
													userid,
													debtorno,
													branchcode,
													prd,
													reference,
													qty,
													standardcost,
													show_on_inv_crds,
													newqoh,
													newamount,
													connectid
													)
										VALUES ('" . $AssParts['component'] . "',
												 10,
												 '" . $InvoiceNo . "',
												 '" . $_SESSION['Items'.$identifier]->Location . "',
												 '" . $DefaultDispatchDate . "',
												 '" . $_SESSION['UserID'] . "',
												 '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												 '" . $_SESSION['Items'.$identifier]->Branch . "',
												 '" . $PeriodNo . "',
												 '" . _('Assembly') . ': ' . $OrderLine->StockID . ' ' . _('Order') . ': ' . $_SESSION['ProcessingOrder'] . "',
												 '" . -$AssParts['quantity'] * $OrderLine->QtyDispatched . "',
												 '" . $AssParts['standard'] . "',
												 '". $OrderLine->StandardCost."',
												 '" . ($QtyOnHandPrior - $AssParts['quantity'] * $OrderLine->QtyDispatched) . "',
												 '" . ($OldAmount -  $AssParts['quantity']*$OrderOLine->Price ) . "',
												 '" . $_SESSION['Items'.$identifier]->OrderNo . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records for the assembly components of'). ' '. $OrderLine->StockID . ' ' . _('could not be inserted because');
					$DbgMsg = _('The following SQL to insert the assembly components stock movement records was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);


					$SQL = "UPDATE locstock
							SET quantity = locstock.quantity - " . ($AssParts['quantity'] * $OrderLine->QtyDispatched) . "
							WHERE locstock.stockid = '" . $AssParts['component'] . "'
							AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Location stock record could not be updated for an assembly component because');
					$DbgMsg = _('The following SQL to update the locations stock record for the component was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				} /* end of assembly explosion and updates */

				/*Update the cart with the recalculated standard cost from the explosion of the assembly's components*/
				$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->StandardCost = $StandardCost;
				$OrderLine->StandardCost = $StandardCost;
			} /* end of its an assembly */

			/*��入库存变动-含单位成本将decimalplaces更改为5，以避免发票上的价格或行总差异。由于stockmoves表现在将其定义为decimal（21,5），所以小数位数不应超过5。Insert stock movements - with unit cost	 change decimalplaces to 5 to avoid price or lines total variance on invoice. And the decimal places should not be over 5 since the stockmoves table defined it as decimal(21,5) now.*/
			$LocalCurrencyPrice = round(($OrderLine->Price / $_SESSION['CurrencyRate']),5);

			if (empty($OrderLine->StandardCost)) {
				$OrderLine->StandardCost=0;
			}
			$SQL="SELECT newqoh,
						newamount
						FROM  stockmoves
						WHERE  stockid='" .  $OrderLine->StockID  . "'
						AND stkmoveno IN (SELECT  stkmoveno	FROM   stockmoves 	GROUP BY stockid	HAVING   MAX(stkmoveno))";
					$Result = DB_query($SQL);
					$LocRow = DB_fetch_assoc($Result);
					//存货价格
					if (empty($LocRow['newqoh'])){
						$OldQoh = 0;
						$OldAmount=0;
					
					}else{
						$OldQoh = $LocRow['newqoh'];
						$OldAmount = $LocRow['newamount'];
					
				
					} 
			if ($MBFlag=='B' OR $MBFlag=='M'){
				//prnMsg('1090 BM');
            			$SQL = "INSERT INTO stockmoves (stockid,
														type,
														transno,
														loccode,
														trandate,
														userid,
														debtorno,
														branchcode,
														price,
														prd,
														reference,
														qty,
														discountpercent,
														standardcost,
														newqoh,
														narrative ,
														newamount,
														connectid)
													VALUES ('" . $OrderLine->StockID . "',
														10,
														'" . $InvoiceNo . "',
														'" . $_SESSION['Items'.$identifier]->Location . "',
														'" . $DefaultDispatchDate . "',
														'" . $_SESSION['UserID'] . "',
														'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
														'" . $_SESSION['Items'.$identifier]->Branch . "',
														'" . $LocalCurrencyPrice . "',
														'" . $PeriodNo . "',
														'" . DB_escape_string($_SESSION['ProcessingOrder']) . "',
														'" . -$OrderLine->QtyDispatched . "',
														'" . $OrderLine->DiscountPercent . "',
														'" . $OrderLine->StandardCost . "',
														'" . ($QtyOnHandPrior - $OrderLine->QtyDispatched) . "',
														'" . DB_escape_string($OrderLine->Narrative) . "',
														'" . ($OldAmount -  $OrderLine->QtyDispatched*$OrderLine->Price ) . "' ,
														'" . $_SESSION['Items'.$identifier]->OrderNo . "')";
			} else {
            // its an assembly or dummy and assemblies/dummies always have nil stock (by definition they are made up at the time of dispatch  so new qty on hand will be nil
				if (empty($OrderLine->StandardCost)) {
					$OrderLine->StandardCost=0;
				}
				//prnMsg('11133 --');
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												debtorno,
												branchcode,
												price,
												prd,
												reference,
												qty,
												discountpercent,
												standardcost,
												narrative ,
												newqoh,
												newamount)
											VALUES ('" . $OrderLine->StockID . "',
												10,
												'" . $InvoiceNo . "',
												'" . $_SESSION['Items'.$identifier]->Location . "',
												'" . $DefaultDispatchDate . "',
												'" . $_SESSION['UserID'] . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . $_SESSION['Items'.$identifier]->Branch . "',
												'" . $LocalCurrencyPrice . "',
												'" . $PeriodNo . "',
												'" . $_SESSION['ProcessingOrder'] . "',
												'" . -$OrderLine->QtyDispatched . "',
												'" . $OrderLine->DiscountPercent . "',
												'" . $OrderLine->StandardCost . "',
												'" . DB_escape_string($OrderLine->Narrative) . "',
												'" . ($QtyOnHandPrior - $OrderLine->QtyDispatched) . "',
												'" . ($OldAmount - $OrderLine->QtyDispatched*$OrderLine->Price ) . "'
												)";
			}


			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Stock movement records could not be inserted because');
			$DbgMsg = _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');

			/*Insert the taxes that applied to this line */
			/*
			foreach ($OrderLine->Taxes as $Tax) {

				$SQL = "INSERT INTO stockmovestaxes (stkmoveno,
													taxauthid,
													taxrate,
													taxcalculationorder,
													taxontax)
										VALUES ('" . $StkMoveNo . "',
											'" . $Tax->TaxAuthID . "',
											'" . $Tax->TaxRate . "',
											'" . $Tax->TaxCalculationOrder . "',
											'" . $Tax->TaxOnTax . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Taxes and rates applicable to this invoice line item could not be inserted because');
				$DbgMsg = _('The following SQL to insert the stock movement tax detail records was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}*/


				/*Insert the StockSerialMovements and update the StockSerialItems  for controlled items*/

			if ($OrderLine->Controlled ==1){
				foreach($OrderLine->SerialItems as $Item){
                   /*We need to add the StockSerialItem record and the StockSerialMoves as well */

					$SQL = "UPDATE stockserialitems	SET quantity= quantity - " . $Item->BundleQty . "
							WHERE stockid='" . $OrderLine->StockID . "'
							AND loccode='" . $_SESSION['Items'.$identifier]->Location . "'
							AND serialno='" . $Item->BundleRef . "'";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be updated because');
					$DbgMsg = _('The following SQL to update the serial stock item record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

					/* now insert the serial stock movement */

					$SQL = "INSERT INTO stockserialmoves (stockmoveno,
														stockid,
														serialno,
														moveqty)
									VALUES ('" . $StkMoveNo . "',
											'" . $OrderLine->StockID . "',
											'" . $Item->BundleRef . "',
											'" . -$Item->BundleQty . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
					$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				}/* foreach controlled item in the serialitems array */
			} /*end if the orderline is a controlled item */

			/*Insert Sales Analysis records */

			$SalesValue = 0;
			if ($_SESSION['CurrencyRate']>0){
				$SalesValue = $OrderLine->Price * $OrderLine->QtyDispatched / $_SESSION['CurrencyRate'];
			}

			$SQL="SELECT COUNT(*),
						salesanalysis.stockid,
						salesanalysis.stkcategory,
						salesanalysis.cust,
						salesanalysis.custbranch,
						salesanalysis.area,
						salesanalysis.periodno,
						salesanalysis.typeabbrev,
						salesanalysis.salesperson
					FROM salesanalysis INNER JOIN custbranch
						ON salesanalysis.cust=custbranch.debtorno
						AND salesanalysis.custbranch=custbranch.branchcode
						AND salesanalysis.area=custbranch.area
					INNER JOIN stockmaster
					ON salesanalysis.stkcategory=stockmaster.categoryid
					WHERE salesanalysis.salesperson='" . $_SESSION['Items'.$identifier]->SalesPerson . "'
					AND salesanalysis.typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
					AND salesanalysis.periodno='" . $PeriodNo . "'
					AND salesanalysis.cust='" . $_SESSION['Items'.$identifier]->DebtorNo . "'
					AND salesanalysis.custbranch='" . $_SESSION['Items'.$identifier]->Branch . "'
					AND salesanalysis.stockid='" . $OrderLine->StockID . "'
					AND salesanalysis.budgetoractual=1
					GROUP BY salesanalysis.stockid,
						salesanalysis.stkcategory,
						salesanalysis.cust,
						salesanalysis.custbranch,
						salesanalysis.area,
						salesanalysis.periodno,
						salesanalysis.typeabbrev,
						salesanalysis.salesperson,
						salesanalysis.budgetoractual";

			$ErrMsg = _('The count of existing Sales analysis records could not run because');
			$DbgMsg = '<br />' .  _('SQL to count the no of sales analysis records');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

			$myrow = DB_fetch_row($Result);

			if ($myrow[0]>0){  /*Update the existing record that already exists */

				$SQL = "UPDATE salesanalysis SET amt=amt+" . round(($SalesValue),POI) . ",
												cost=cost+" . round(($OrderLine->StandardCost * $OrderLine->QtyDispatched),POI) . ",
												qty=qty +" . $OrderLine->QtyDispatched . ",
												disc=disc+" . round(($OrderLine->DiscountPercent * $SalesValue),POI) . "
								WHERE salesanalysis.area='" . $myrow[5] . "'
								AND salesanalysis.salesperson='" . $myrow[8] . "'
								AND typeabbrev ='" . $_SESSION['Items'.$identifier]->DefaultSalesType . "'
								AND periodno = '" . $PeriodNo . "'
								AND cust " . LIKE . " '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
								AND custbranch " . LIKE . " '" . $_SESSION['Items'.$identifier]->Branch . "'
								AND stockid " . LIKE . " '" . $OrderLine->StockID . "'
								AND salesanalysis.stkcategory ='" . $myrow[2] . "'
								AND budgetoractual=1";

			} else { /* insert a new sales analysis record */

				$SQL = "INSERT INTO salesanalysis (typeabbrev,
												periodno,
												amt,
												cost,
												cust,
												custbranch,
												qty,
												disc,
												stockid,
												area,
												budgetoractual,
												salesperson,
												stkcategory )
								SELECT '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										'" . $PeriodNo . "',
										'" . round(($SalesValue),POI) . "',
										'" . round(($OrderLine->StandardCost * $OrderLine->QtyDispatched),POI) . "',
										'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
										'" . $_SESSION['Items'.$identifier]->Branch . "',
										'" . ($OrderLine->QtyDispatched) . "',
										'" . round(($OrderLine->DiscountPercent * $SalesValue),POI) . "',
										'" . $OrderLine->StockID . "',
										custbranch.area,
										1,
										'" . $_SESSION['Items'.$identifier]->SalesPerson . "',
										stockmaster.categoryid
								FROM stockmaster, custbranch
								WHERE stockmaster.stockid = '" . $OrderLine->StockID . "'
								AND custbranch.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'
								AND custbranch.branchcode='" . $_SESSION['Items'.$identifier]->Branch . "'";
			}

			$ErrMsg = _('Sales analysis record could not be added or updated because');
			$DbgMsg = _('The following SQL to insert the sales analysis record was used');
			//prnMsg($SQL.'[1314');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		} /*Quantity dispatched is more than 0 */
	} /*end of OrderLine loop 803*/

	

		DB_Txn_Commit();
		// *************************************************************************
		//   E N D   O F   I N V O I C E   S Q L   P R O C E S S I N G
		// *************************************************************************

	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	unset($_SESSION['ProcessingOrder']);

	echo prnMsg( '产品发货单: '. $InvoiceNo .' '. _('processed'), 'success');

	echo '<br /><div class="centre">';

	if ($_SESSION['InvoicePortraitFormat']==0){
		echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' . 
		        '<a target="_blank" href="'.$RootPath.'/PrintCustTrans.php?FromTransNo='.$InvoiceNo.'&amp;InvOrCredit=Invoice&amp;PrintPDF=True"  target="_blank">打印发货单</a><br /><br />';
	} else {
		echo '<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" />' . ' ' .
		        '<a target="_blank" href="'.$RootPath.'/PrintCustTransPortrait.php?FromTransNo='.$InvoiceNo.'&amp;InvOrCredit=Invoice&amp;PrintPDF=True"  target="_blank">打印发货单 (' . _('Portrait') . ')</a><br /><br />';
	}
	echo '<a href="'.$RootPath.'/SelectSalesOrder.php">选择其他要开发货单的订单</a><br /><br />';
	echo '<a href="'.$RootPath.'/SelectOrderItems.php?NewOrder=Yes">' . _('Sales Order Entry') . '</a></div><br />';
	/*end of process invoice */
	include('includes/footer.php');
	exit;

} else { /*Process Invoice not set so allow input of invoice data */

	if (!isset($_POST['Consignment'])) {
		$_POST['Consignment']='';
	}
	if (!isset($_POST['Packages'])) {
		$_POST['Packages']='1';
	}
	if (!isset($_POST['InvoiceText'])) {
		$_POST['InvoiceText']='';
	}
	$j++;
	echo '<table class="selection">
		<tr>
			<td>' ._('Date On Invoice'). ':</td>
			<td><input tabindex="'.$j.'" type="text" maxlength="10" size="15" required="required" name="DispatchDate" value="' . $DefaultDispatchDate . '" id="datepicker" alt="' . $_SESSION['DefaultDateFormat'] . '" class="date" /></td>
		</tr>';
	$j++;
	echo '<tr>
			<td>' . _('Consignment Note Ref'). ':</td>
			<td><input tabindex="'.$j.'" type="text" data-type="no-illegal-chars" title="' . _('Enter the consignment note reference to enable tracking of the delivery in the event of customer proof of delivery issues') . '" maxlength="20" size="20" name="Consignment" value="' . $_POST['Consignment'] . '" /></td>
		</tr>';
	$j++;
	echo '<tr>
			<td>' . _('No Of Packages in Delivery'). ':</td>
			<td><input tabindex="'.$j.'" type="number" maxlength="6" size="6" class="integer" name="Packages" value="' . $_POST['Packages'] . '" /></td>
		</tr>';

	$j++;
	echo '<tr>
			<td>' . _('Action For Balance'). ':</td>
			<td><select tabindex="'.$j.'" name="BOPolicy"><option selected="selected" value="BO">' . _('Automatically put balance on back order') . '</option><option value="CAN">' . _('Cancel any quantities not delivered') . '</option></select></td>
		</tr>';
	$j++;
	echo '<tr>
			<td>' ._('Invoice Text'). ':</td>
			<td><textarea tabindex="'.$j.'" name="InvoiceText" pattern=".{0,20}" cols="31" rows="5">' . reverse_escape($_POST['InvoiceText']) . '</textarea></td>
		</tr>';

	$j++;
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" tabindex="'.$j.'" name="Update" value="' . _('Update'). '" />
			<br />';

	$j++;
	echo '<br />
			<input type="submit" tabindex="'.$j.'" name="ProcessInvoice" value="确认发货" />
		</div>
		<input type="hidden" name="ShipVia" value="' . $_SESSION['Items'.$identifier]->ShipVia . '" />';
}
echo '</div>';
echo '</form>';
	/*Now insert the DebtorTrans */
	/*
		$_POST['ChargeFreightCost']='9';
		//ovgst错误
		$SQL = "INSERT INTO debtortrans (transno,
									type,
									debtorno,
									branchcode,
									trandate,
									inputdate,
									prd,
									reference,
									tpe,
									order_,
									ovamount,
									ovgst,
									ovfreight,
									rate,
									invtext,
									shipvia,
									consignment,
									packages,
									salesperson )
								VALUES (
									'". $InvoiceNo . "',
									10,
									'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
									'" . $_SESSION['Items'.$identifier]->Branch . "',
									'" . $DefaultDispatchDate . "',
									'" . date('Y-m-d H-i-s') . "',
									'" . $PeriodNo . "',
									'',
									'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
									'" . $_SESSION['ProcessingOrder'] . "',
									'" . $_SESSION['Items'.$identifier]->total . "',
									'" . $TaxTotal . "',
									'" . filter_number_format($_POST['ChargeFreightCost']) . "',
									'" . $_SESSION['CurrencyRate'] . "',
									'" . $_POST['InvoiceText'] . "',
									'" . $_SESSION['Items'.$identifier]->ShipVia . "',
									'" . $_POST['Consignment'] . "',
									'" . $_POST['Packages'] . "',
									'" . $_SESSION['Items'.$identifier]->SalesPerson . "' )";

			$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction record could not be inserted because');
			$DbgMsg = _('The following SQL to insert the debtor transaction record was used');
			$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
											*/
			//$DebtorTransID = DB_Last_Insert_ID($db,'debtortrans','id');

			/* Insert the tax totals for each tax authority where tax was charged on the invoice */
			/*
			foreach ($TaxTotals AS $TaxAuthID => $TaxAmount) {

				$SQL = "INSERT INTO debtortranstaxes (debtortransid,
													taxauthid,
													taxamount)
										VALUES ('" . $DebtorTransID . "',
											'" . $TaxAuthID . "',
											'" . $TaxAmount/$_SESSION['CurrencyRate'] . "')";

				$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The debtor transaction taxes records could not be inserted because');
				$DbgMsg = _('The following SQL to insert the debtor transaction taxes record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}
	*/
			/* If GLLink_Stock then insert GLTrans to credit stock and debit cost of sales at standard cost*/
			/*
				if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $OrderLine->StandardCost !=0 AND ! $IsAsset){

			

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (
										10,
										'" . $InvoiceNo . "',
										'" . $DefaultDispatchDate . "',
										'" . $PeriodNo . "',
										'" . GetCOGSGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db) . "',
										'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
										'" . round(($OrderLine->StandardCost * $OrderLine->QtyDispatched),POI) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);


				$StockGLCode = GetStockGLCode($OrderLine->StockID,$db);

				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
									VALUES (
										10,
										'" . $InvoiceNo . "',
										'" . $DefaultDispatchDate . "',
										'" . $PeriodNo . "',
										'" . $StockGLCode['stockact'] . "',
										'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->StandardCost . "',
										'" . round((-$OrderLine->StandardCost * $OrderLine->QtyDispatched),POI) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the cost of sales GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
					} 
				
					if ($_SESSION['CompanyRecord']['gllink_debtors']==1 AND $OrderLine->Price !=0){

						if (!$IsAsset){ // its a normal stock item
							//Post sales transaction to GL credit sales
							$SalesGLAccounts = GetSalesGLAccount($Area, $OrderLine->StockID, $_SESSION['Items'.$identifier]->DefaultSalesType, $db);

							$SQL = "INSERT INTO gltrans (type,
														typeno,
														trandate,
														periodno,
														account,
														narrative,
														amount )
												VALUES (
													10,
													'" . $InvoiceNo . "',
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $SalesGLAccounts['salesglcode'] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " x " . $OrderLine->QtyDispatched . " @ " . $OrderLine->Price . "',
													'" . (-$OrderLine->Price * $OrderLine->QtyDispatched/$_SESSION['CurrencyRate']) . "')";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales GL posting could not be inserted because');
							$DbgMsg = '<br />' ._('The following SQL to insert the GLTrans record was used');
							$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

							if ($OrderLine->DiscountPercent !=0){

								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
														VALUES (
															10,
															'" . $InvoiceNo . "',
															'" . $DefaultDispatchDate . "',
															'" . $PeriodNo . "',
															'" . $SalesGLAccounts['discountglcode'] . "',
															'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . " @ " . ($OrderLine->DiscountPercent * 100) . "%',
															'" . ($OrderLine->Price * $OrderLine->QtyDispatched * $OrderLine->DiscountPercent/$_SESSION['CurrencyRate']) . "')";

								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The sales discount GL posting could not be inserted because');
								$DbgMsg = _('The following SQL to insert the GLTrans record was used');
								$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
							} 

						} else {
							
							$SQL = "SELECT cost,
											accumdepn,
											costact,
											accumdepnact,
											disposalact
								FROM fixedassetcategories INNER JOIN fixedassets
								ON fixedassetcategories.categoryid = fixedassets.assetcategoryid
								WHERE assetid ='" . $AssetNumber . "'";
							$ErrMsg = _('The asset disposal GL posting details could not be retrieved because');
							$DbgMsg = _('The following SQL was used to get the asset posting details');
							$DisposalResult = DB_query( $SQL,$ErrMsg,$DbgMsg);
							$DisposalRow = DB_fetch_array($DisposalResult);

							if ($DisposalRow['accumdepn']!=0){
								$SQL = "INSERT INTO gltrans (type,
															typeno,
															trandate,
															periodno,
															account,
															narrative,
															amount)
													VALUES (
														10,
														'" . $InvoiceNo . "',
														'" . $DefaultDispatchDate . "',
														'" . $PeriodNo . "',
														'" . $DisposalRow['accumdepnact'] . "',
														'" . $_SESSION['Items'.$identifier]->DebtorNo . ' - ' . $OrderLine->StockID . ' ' . _('accumulated depreciation disposal') . "',
														'" . $DisposalRow['accumdepn'] . "')";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The reversal of accumulated depreciation GL posting on disposal could not be inserted because');
							$DbgMsg = _('The following SQL to insert the GLTrans record was used');
							$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
						}
						
						if($DisposalRow['cost']!=0){
							$SQL = "INSERT INTO gltrans (type,
														typeno,
														trandate,
														periodno,
														account,
														narrative,
														amount)
												VALUES (
													10,
													'" . $InvoiceNo . "',
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $DisposalRow['costact'] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID . ' ' . _('cost disposal') . "',
													'" . -$DisposalRow['cost'] . "')";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The reversal of asset cost on disposal GL posting could not be inserted because');
							$DbgMsg = _('The following SQL to insert the GLTrans record was used');
							$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
						}
						//3. Debit disposal account with NBV
						if($DisposalRow['cost']-$DisposalRow['accumdepn']!=0){
							$SQL = "INSERT INTO gltrans (type,
														typeno,
														trandate,
														periodno,
														account,
														narrative,
														amount )
												VALUES (
													10,
													'" . $InvoiceNo . "',
													'" . $DefaultDispatchDate . "',
													'" . $PeriodNo . "',
													'" . $DisposalRow['disposalact'] . "',
													'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID .  ' ' . _('net book value disposal') . "',
													'" . ($DisposalRow['cost']-$DisposalRow['accumdepn']) . "')";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The disposal net book value GL posting could not be inserted because');
							$DbgMsg = '<br />' ._('The following SQL to insert the GLTrans record was used');
							$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
						}

						//4. Credit the disposal account with the proceeds
						$SQL = "INSERT INTO gltrans (type,
													typeno,
													trandate,
													periodno,
													account,
													narrative,
													amount )
											VALUES (
												10,
												'" . $InvoiceNo . "',
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . $DisposalRow['disposalact'] . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . " - " . $OrderLine->StockID .  ' ' . _('asset disposal proceeds') . "',
												'" . (-$OrderLine->Price * $OrderLine->QtyDispatched* (1 - $OrderLine->DiscountPercent)/$_SESSION['CurrencyRate']) . "')";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The disposal proceeds GL posting could not be inserted because');
						$DbgMsg = '<br />' ._('The following SQL to insert the GLTrans record was used');
						$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

						} // end if the item being sold was an asset
					} 

					if ($IsAsset) {
					
						$SQL = "INSERT INTO fixedassettrans (assetid,
															transtype,
															transno,
															periodno,
															inputdate,
															fixedassettranstype,
															amount,
															transdate)
												VALUES ('" . $AssetNumber . "',
														10,
														'" . $InvoiceNo . "',
														'" . $PeriodNo . "',
														'" . Date('Y-m-d') . "',
														'disposal',
														'" . round(($OrderLine->Price * $OrderLine->QtyDispatched* (1 - $OrderLine->DiscountPercent)/$_SESSION['CurrencyRate']),POI) . "',
														'" . $DefaultDispatchDate . "')";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The fixed asset transaction could not be inserted because');
						$DbgMsg = '<br />' ._('The following SQL to insert the fixed asset transaction record was used');
						$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

						$SQL = "UPDATE fixedassets
								SET disposalproceeds ='" . round(($OrderLine->Price * $OrderLine->QtyDispatched* (1 - $OrderLine->DiscountPercent)/$_SESSION['CurrencyRate']),POI) . "',
									disposaldate ='" . $DefaultDispatchDate . "'
								WHERE assetid ='" . $AssetNumber . "'";

						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The fixed asset record could not be updated for the disposal because');
						$DbgMsg = '<br />' ._('The following SQL to update the fixed asset record was used');
						$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

					}
			*/
	/*
			if ($_SESSION['CompanyRecord'][$tag]['gllink_debtors']==1){

				//Post debtors transaction to GL debit debtors, credit freight re-charged and credit sales //
			if (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal) !=0) {
				$SQL = "INSERT INTO gltrans (type,
											typeno,
											trandate,
											periodno,
											account,
											narrative,
											amount)
										VALUES (
											10,
											'" . $InvoiceNo . "',
											'" . $DefaultDispatchDate . "',
											'" . $PeriodNo . "',
											'" . $_SESSION['CompanyRecord'][$tag]['debtorsact'] . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
											'" . (($_SESSION['Items'.$identifier]->total + $_SESSION['Items'.$identifier]->FreightCost + $TaxTotal)/$_SESSION['CurrencyRate']) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The total debtor GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the total debtors control GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}

			//Could do with setting up a more flexible freight posting schema that looks at the sales type and area of the customer branch to determine where to post the freight recovery //

			if ($_SESSION['Items'.$identifier]->FreightCost !=0) {
				$SQL = "INSERT INTO gltrans (
							type,
							typeno,
							trandate,
							periodno,
							account,
							narrative,
							amount	)
					VALUES (
						10,
						'" . $InvoiceNo . "',
						'" . $DefaultDispatchDate . "',
						'" . $PeriodNo . "',
						'" . $_SESSION['CompanyRecord']['freightact'] . "',
						'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
						'" . (-$_SESSION['Items'.$identifier]->FreightCost/$_SESSION['CurrencyRate']) . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The freight GL posting could not be inserted because');
				$DbgMsg = _('The following SQL to insert the GLTrans record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}
			foreach ( $TaxTotals as $TaxAuthID => $TaxAmount){
				if ($TaxAmount !=0 ){
					$SQL = "INSERT INTO gltrans (type,
												typeno,
												trandate,
												periodno,
												account,
												narrative,
												amount)
											VALUES (
												10,
												'" . $InvoiceNo . "',
												'" . $DefaultDispatchDate . "',
												'" . $PeriodNo . "',
												'" . $TaxGLCodes[$TaxAuthID] . "',
												'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
												'" . (-$TaxAmount/$_SESSION['CurrencyRate']) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The tax GL posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				}
			}
			} //end of if Sales and GL integrated //
			///EnsureGLEntriesBalance(10,$InvoiceNo,$db);
		*/

?>
