
<?php
/* $Id: SO_Header.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-10-03 07:16:28 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-09-11 20:47:07
 */
include('includes/DefineCartClassCN.php');

/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/

include('includes/session.php');

include('includes/CurrenciesArray.php');
include('includes/CountriesArray.php');
if (isset($_GET['ModifyOrderNumber'])) {
	$Title = _('Modifying Order') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$Title ='选择客户';// _('Select Order Items');
}
/* webERP manual links before header.php */
$ViewTopic= 'SalesOrders';
$BookMark = 'SalesOrderEntry';

include('includes/header.php');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
function inSelect(v, tA, m) {

	var rate=v.value.split("^")[1];
	document.getElementById("CurrRate").value=rate;

}		
	function refresh() {  
		window.location.reload();
	}  
</script>';
if (isset($_POST['QuickEntry'])){
	unset($_POST['PartSearch']);
}

if (isset($_POST['SelectingOrderItems'])){
	//prnMsg('142=29增销售订单,读取数量->$NewItemArray','info');
	foreach ($_POST as $FormVariable => $Quantity) {
		if (mb_strpos($FormVariable,'OrderQty')!==false) {
			$NewItemArray[$_POST['StockID' . mb_substr($FormVariable,8)]] = filter_number_format($Quantity);
		}
	}
}
//var_dump($NewItemArray);
if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}

if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}
 //if ($_SESSION['Tag']==1){
	 $Tag=$_SESSION['Tag'];
 //}

if (isset($_GET['NewOrder'])){
  /*New order entry - clear any existing order details from the Items object and initiate a newy*/
	 if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		$_SESSION['Items'.$identifier]->ItemsOrdered=0;
		unset ($_SESSION['Items'.$identifier]);
	}

	$_SESSION['ExistingOrder' .$identifier]=0;
	$_SESSION['Items'.$identifier] = new cart;

	if ($CustomerLogin==1){ //its a customer logon
		$_SESSION['Items'.$identifier]->DebtorNo=$_SESSION['CustomerID'];
		$_SESSION['Items'.$identifier]->BranchCode=$_SESSION['UserBranch'];
		$SelectedCustomer = $_SESSION['CustomerID'];
		//$SelectedBranch = $_SESSION['UserBranch'];
		$_SESSION['RequireCustomerSelection'] = 0;
	} else {
		$_SESSION['Items'.$identifier]->DebtorNo='';
		$_SESSION['Items'.$identifier]->BranchCode='';
		$_SESSION['RequireCustomerSelection'] = 1;
	}

}

if (isset($_GET['ModifyOrderNumber'])	AND $_GET['ModifyOrderNumber']!=''){
	//prnMsg($_GET['ModifyOrderNumber'],'info');
	/* The delivery check screen is where the details of the order are either updated or inserted depending on the value of ExistingOrder */

	if (isset($_SESSION['Items'.$identifier])){
		unset ($_SESSION['Items'.$identifier]->LineItems);
		unset ($_SESSION['Items'.$identifier]);
	}
	$_SESSION['ExistingOrder'.$identifier]=$_GET['ModifyOrderNumber'];
	$_SESSION['RequireCustomerSelection'] = 0;
	$_SESSION['Items'.$identifier] = new cart;

	/*read in all the guff from the selected order into the Items cart  */
	
	$OrderHeaderSQL = "SELECT salesorders.debtorno,
			 				  debtorsmaster.name custname,
							  salesorders.branchcode,
							  salesorders.customerref,
							  salesorders.comments,
							  salesorders.orddate,
							  salesorders.taxcatid,
							  salesorders.taxrate,
							  salesorders.currcode,
							  salesorders.rate,
							  salesorders.ordertype,
							  salestypes.sales_type,
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
							  salesorders.salesperson,
							  salesorders.freightcost,
							  salesorders.deliverydate,
							  debtorsmaster.currcode,
							  currencies.decimalplaces,
							  paymentterms.terms,
							  salesorders.fromstkloc,
							  salesorders.printedpackingslip,
							  salesorders.datepackingslipprinted,
							  salesorders.quotation,
							  salesorders.quotedate,
							  salesorders.confirmeddate,
							  salesorders.deliverblind,
							  debtorsmaster.customerpoline,
							  locations.locationname,
							  debtorsmaste.estdeliverydays,
							  debtorsmaster.salesman
						FROM salesorders
						INNER JOIN debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN salestypes
						ON salesorders.ordertype=salestypes.typeabbrev
						
						INNER JOIN paymentterms
						ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN locations
						ON locations.loccode=salesorders.fromstkloc
						INNER JOIN currencies
						ON debtorsmaster.currcode=currencies.currabrev
						INNER JOIN locationusers ON locationusers.loccode=salesorders.fromstkloc AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
						WHERE salesorders.orderno = '" . $_GET['ModifyOrderNumber'] . "'";

	$ErrMsg =  _('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$ErrMsg);
      //prnMsg($$OrderHeaderSQL,'info');
	if (DB_num_rows($GetOrdHdrResult)==1) {

		$myrow = DB_fetch_array($GetOrdHdrResult);
		if ($_SESSION['SalesmanLogin']!='' AND $_SESSION['SalesmanLogin']!=$myrow['salesman']){
			prnMsg(_('Your account is set up to see only a specific salespersons orders. You are not authorised to modify this order'),'error');
			include('includes/footer.php');
			exit;
		}
		$_SESSION['Items'.$identifier]->OrderNo = $_GET['ModifyOrderNumber'];
		$_SESSION['Items'.$identifier]->DebtorNo = $myrow['debtorno'];
		$_SESSION['Items'.$identifier]->CreditAvailable = GetCreditAvailable($_SESSION['Items'.$identifier]->DebtorNo,$db);
	/*CustomerID defined in header.php */
		$_SESSION['Items'.$identifier]->Branch = $myrow['branchcode'];
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['custname'];
		$_SESSION['Items'.$identifier]->tag = $myrow['tag'];
		$_SESSION['Items'.$identifier]->CurrCode = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->ExRate = $myrow['rate'];
		$_SESSION['Items'.$identifier]->TaxCatID = $myrow['taxcatid'];
		$_SESSION['Items'.$identifier]->TaxRate = $myrow['taxrate'];
		$_SESSION['Items'.$identifier]->CustRef = $myrow['customerref'];
		$_SESSION['Items'.$identifier]->Comments = stripcslashes($myrow['comments']);
		$_SESSION['Items'.$identifier]->PaymentTerms =$myrow['terms'];
		$_SESSION['Items'.$identifier]->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items'.$identifier]->SalesTypeName =$myrow['sales_type'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['shipvia'];
		//$BestShipper = $myrow['shipvia'];
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['deladd1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['deladd2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['deladd3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['deladd4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['deladd5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['deladd6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items'.$identifier]->Email = $myrow['email'];
		$_SESSION['Items'.$identifier]->SalesPerson = $myrow['salesperson'];
		$_SESSION['Items'.$identifier]->Location = $myrow['fromstkloc'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];
		$_SESSION['Items'.$identifier]->Quotation = $myrow['quotation'];
		$_SESSION['Items'.$identifier]->QuoteDate = ConvertSQLDate($myrow['quotedate']);
		$_SESSION['Items'.$identifier]->ConfirmedDate = ConvertSQLDate($myrow['confirmeddate']);
		$_SESSION['Items'.$identifier]->FreightCost = $myrow['freightcost'];
		$_SESSION['Items'.$identifier]->Orig_OrderDate = $myrow['orddate'];
		$_SESSION['PrintedPackingSlip'] = $myrow['printedpackingslip'];
		$_SESSION['DatePackingSlipPrinted'] = $myrow['datepackingslipprinted'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];

		//Get The exchange rate used for GPPercent calculations on adding or amending items
		if ($_SESSION['Items'.$identifier]->DefaultCurrency != $_SESSION['CompanyRecord'][$Tag]['currencydefault']){
			$ExRateResult = DB_query("SELECT rate FROM currencies WHERE currabrev='" . $_SESSION['Items'.$identifier]->DefaultCurrency . "'");
			if (DB_num_rows($ExRateResult)>0){
				$ExRateRow = DB_fetch_row($ExRateResult);
				$ExRate = $ExRateRow[0];
			} else {
				$ExRate =1;
			}
		} else {
			$ExRate = 1;
		}

	/*need to look up customer name from debtors master then populate the line items array with the sales order details records */

			$LineItemsSQL = "SELECT salesorderdetails.orderlineno,
									salesorderdetails.stkcode,
									stockmaster.description,
									stockmaster.longdescription,
									stockmaster.volume,
									stockmaster.grossweight,
									stockmaster.units,
									stockmaster.serialised,
									stockmaster.nextserialno,
									stockmaster.eoq,
									salesorderdetails.unitprice,
									salesorderdetails.quantity,
									salesorderdetails.currprice,
									salesorderdetails.taxprice,
									salesorderdetails.discountpercent,
									salesorderdetails.actualdispatchdate,
									salesorderdetails.qtyinvoiced,
									salesorderdetails.narrative,
									salesorderdetails.itemdue,
									salesorderdetails.poline,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces,
									stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
									salesorderdetails.completed
								FROM salesorderdetails INNER JOIN stockmaster
								ON salesorderdetails.stkcode = stockmaster.stockid
								INNER JOIN locstock ON locstock.stockid = stockmaster.stockid
								WHERE  locstock.loccode = '" . $myrow['fromstkloc'] . "'
								AND salesorderdetails.orderno ='" . $_GET['ModifyOrderNumber'] . "'
								ORDER BY salesorderdetails.orderlineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		//prnMsg($LineItemsSQL);
		$LineItemsResult = DB_query($LineItemsSQL,$ErrMsg);
		if (DB_num_rows($LineItemsResult)>0) {

			while ($myrow=DB_fetch_array($LineItemsResult)) {
					if ($myrow['completed']==0){
						$_SESSION['Items'.$identifier]->add_to_cart($myrow['stkcode'],
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
																	$myrow['qohatloc'],
																	$myrow['mbflag'],
																	$myrow['actualdispatchdate'],
																	$myrow['qtyinvoiced'],
																	$myrow['discountcategory'],
																	0,	/*Controlled*/
																	$myrow['serialised'],
																	$myrow['decimalplaces'],
																	$myrow['narrative'],
																	'No', /* Update DB */
																	$myrow['orderlineno'],
																	$_SESSION['Items'.$identifier]->TaxCatID,
																	'',
																	ConvertSQLDate($myrow['itemdue']),
																	$myrow['poline'],
																	$myrow['standardcost'],
																	$myrow['eoq'],
																	$myrow['nextserialno'],
																	$_SESSION['Items'.$identifier]->ExRate,
																	$identifier );

				/*Just populating with existing order - no DBUpdates */
					}
					$LastLineNo = $myrow['orderlineno'];
			} /* line items from sales order details */
			 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
			 //prnMsg($_SESSION['Items'.$identifier]->LineCounter);
		} //end of checks on returned data set
	}
}


if (!isset($_SESSION['Items'.$identifier])){
	/* It must be a new order being created $_SESSION['Items'.$identifier] would be set up from the order
	modification code above if a modification to an existing order. Also $ExistingOrder would be
	set to 1. The delivery check screen is where the details of the order are either updated or
	inserted depending on the value of ExistingOrder */

	$_SESSION['ExistingOrder'.$identifier]=0;
	$_SESSION['Items'.$identifier] = new cart;
	$_SESSION['PrintedPackingSlip'] = 0; /*Of course cos the order aint even started !!*/



	if (in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])
		AND ($_SESSION['Items'.$identifier]->DebtorNo==''
		OR !isset($_SESSION['Items'.$identifier]->DebtorNo))){

	/* need to select a customer for the first time out if authorisation allows it and if a customer
	 has been selected for the order or not the session variable CustomerID holds the customer code
	 already as determined from user id /password entry  */
		$_SESSION['RequireCustomerSelection'] = 1;
	} else {
		$_SESSION['RequireCustomerSelection'] = 0;
	}
}

if (isset($_POST['ChangeCustomer']) AND $_POST['ChangeCustomer']!=''){

	if ($_SESSION['Items'.$identifier]->Any_Already_Delivered()==0){
		$_SESSION['RequireCustomerSelection']=1;
	} else {
		prnMsg(_('The customer the order is for cannot be modified once some of the order has been invoiced'),'warn');
	}
}

//Customer logins are not allowed to select other customers hence in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])
if (isset($_POST['SearchCust'])	AND $_SESSION['RequireCustomerSelection']==1
	AND in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])){
        //添加tag
		$SQL = "SELECT	a.contactname,
						b.tag,
						a.phoneno,
						a.faxno,				
						a.debtorno,
						b.custname ,
						a.currcode,
						a.taxcatid,
						a.taxrate
					FROM debtorsmaster a
					LEFT JOIN custname_reg_sub b ON a.debtorno=b.regid			
					INNER JOIN customerusers c ON c.regid =a.debtorno 
					WHERE a.used>=0 AND c.userid='".$_SESSION['UserID']."'";

	if (($_POST['CustKeywords']=='') AND ($_POST['CustCode']=='')  AND ($_POST['CustPhone']=='')) {
		$SQL .= "";
	} else {
		//insert wildcard characters in spaces
		$_POST['CustKeywords'] = mb_strtoupper(trim($_POST['CustKeywords']));
		$SearchString = str_replace(' ', '%', $_POST['CustKeywords']) ;

		$SQL .= "AND b.custname " . LIKE . " '%" . $SearchString . "%'
				AND a.debtorno " . LIKE . " '%" . mb_strtoupper(trim($_POST['CustCode'])) . "%'
				AND a.phoneno " . LIKE . " '%" . trim($_POST['CustPhone']) . "%'";

	} /*one of keywords or custcode was more than a zero length string */
	if ($_SESSION['SalesmanLogin']!=''){
		$SQL .= " AND a.contactname='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL .=	" ORDER BY debtorno";

	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	$result_CustSelect = DB_query($SQL,$ErrMsg);
   
	if (DB_num_rows($result_CustSelect)==1){
		$myrow=DB_fetch_array($result_CustSelect);
		$SelectedCustomer = $myrow['debtorno'];
	//	$SelectedBranch = $myrow['branchcode'];
		$Selectedtag=$myrow['tag'];
	} elseif (DB_num_rows($result_CustSelect)==0){
		prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'),'info');
	}
} /*end of if search for customer codes/names */
//prnMsg($SQL,'info');
if (isset($_POST['JustSelectedACustomer'])&& $_POST['JustSelectedACustomer']!=""){
 
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i=0;$i<count($_POST);$i++){ //loop through the returned customers
		if(isset($_POST['SubmitCustomerSelection'.$i])){
			break;
		}
	}
	//prnMsg(count($_POST).'-'.$i.'='.$SelectedCustomer);
	/*
	if ($i==count($_POST) AND !isset($SelectedCustomer)){//if there is ONLY one customer searched at above, the $SelectedCustomer already setup, then there is a wrong warning
		prnMsg(_('Unable to identify the selected customer'),'error');
	} else*/
	if(!isset($SelectedCustomer)) {
		$SelectedCustomer = $_POST['SelectedCustomer'.$i];
		//$SelectedBranch = $_POST['SelectedBranch'.$i];
		$Selectedtag = $_POST['Selectedtag'.$i];
	}
}
/* will only be true if page called from customer selection form or set because only one customer
 record returned from a search so parse the $SelectCustomer string into customer code and branch code */
if (isset($SelectedCustomer)) {

	$_SESSION['Items'.$identifier]->DebtorNo = trim($SelectedCustomer);
	$_SESSION['Items'.$identifier]->Branch = trim($SelectedBranch);//不用
	$_SESSION['Items'.$identifier]->tag = trim($Selectedtag);
	// Now check to ensure this account is not on hold */
	$sql = "SELECT debtorsmaster.name custname,
	               holdreasons.dissallowinvoices,
				   debtorsmaster.salestype,
					salestypes.sales_type, 
					debtorsmaster.currcode,
					debtorsmaster.customerpoline, 
					paymentterms.terms, 
					currencies.decimalplaces,
											
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						phoneno,
						email,						
						estdeliverydays,						
						salesman,
						defaultshipvia,					
						deliverblind,
						taxrate,
						taxcatid,
						contactname					
          FROM debtorsmaster INNER JOIN holdreasons ON debtorsmaster.holdreason=holdreasons.reasoncode 
          INNER JOIN salestypes ON debtorsmaster.salestype=salestypes.typeabbrev 
          INNER JOIN paymentterms ON debtorsmaster.paymentterms=paymentterms.termsindicator 
          INNER JOIN currencies ON debtorsmaster.currcode=currencies.currabrev 
          WHERE debtorsmaster.debtorno  = '" . $_SESSION['Items'.$identifier]->DebtorNo. "'";

	$ErrMsg = _('The details of the customer selected') . ': ' .  $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
	$result =DB_query($sql,$ErrMsg,$DbgMsg);
	//prnMsg($sql .'[439]','info');
	$myrow = DB_fetch_array($result);
	if ($myrow[1] != 1){
		if ($myrow[1]==2){
			prnMsg(_('The') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'),'warn');
		}

		$_SESSION['RequireCustomerSelection']=0;
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['custname'];
        //prnMsg($sql.$_SESSION['Items'.$identifier]->CustomerName );
# the sales type determines the price list to be used by default the customer of the user is
# defaulted from the entry of the userid and password.

		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
		$_SESSION['Items'.$identifier]->SalesTypeName = $myrow['sales_type'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items'.$identifier]->PaymentTerms = $myrow['terms'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$_SESSION['Items'.$identifier]->TaxCatID = $myrow['taxcatid'];
		$_SESSION['Items'.$identifier]->TaxRate = $myrow['taxrate'];
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['custname'];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['address1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['address2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['address3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['address4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['address5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['address6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['phoneno'];
		$_SESSION['Items'.$identifier]->Email = $myrow['contactemail'];
		$_SESSION['Items'.$identifier]->Location = $myrow['defaultlocation'];//没有用
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['defaultshipvia'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow['specialinstructions'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];//没有用
		//prnMsg($_SESSION['Items'.$identifier]->Branch  .'-414='.$_SESSION['Items'.$identifier]->DebtorNo.']'.$SelectedBranch,'info');
# the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway
         /*
		$result = GetCustBranchDetails-($identifier);
	 
		if (DB_num_rows($result)==0){

			prnMsg(_('The branch details for branch code') . ' ' . $_SESSION['Items'.$identifier]->Branch . ' ' . _('against customer code') . ': ' . $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('could not be retrieved') . '. ' . _('Check the set up of the customer and branch'),'error');

			if ($debug==1){
				prnMsg( _('The SQL that failed to get the branch details was') . ':<br />' . $sql . 'warning');
			}
			include('includes/footer.php');
			exit;
		}
		// add echo
		echo '<br />';
		$myrow = DB_fetch_array($result);*/
		if ($_SESSION['SalesmanLogin']!=NULL AND $_SESSION['SalesmanLogin']!=$myrow['salesman']){
			prnMsg(_('Your login is only set up for a particular salesperson. This customer has a different salesperson.'),'error');
			include('includes/footer.php');
			exit;
		}
	
		if ($_SESSION['SalesmanLogin']!= NULL AND $_SESSION['SalesmanLogin']!=''){
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		} else {
			$_SESSION['Items'.$identifier]->SalesPerson = $myrow['salesman'];
		}
		if ($_SESSION['Items'.$identifier]->SpecialInstructions)
		  prnMsg($_SESSION['Items'.$identifier]->SpecialInstructions,'warn');

		if ($_SESSION['CheckCreditLimits'] > 0){  /*Check credit limits is 1 for warn and 2 for prohibit sales */
			$_SESSION['Items'.$identifier]->CreditAvailable = GetCreditAvailable($_SESSION['Items'.$identifier]->DebtorNo,$db);

			if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
				prnMsg(_('The') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently at or over their credit limit'),'warn');
			} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
				prnMsg(_('No more orders can be placed by') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _(' their account is currently at or over their credit limit'),'warn');
				include('includes/footer.php');
				exit;
			}
		}

	} else {
		prnMsg(_('The') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently on hold please contact the credit control personnel to discuss'),'warn');
	}

} elseif (!$_SESSION['Items'.$identifier]->DefaultSalesType
			OR $_SESSION['Items'.$identifier]->DefaultSalesType=='')	{

#Possible that the check to ensure this account is not on hold has not been done
#if the customer is placing own order, if this is the case then
#DefaultSalesType will not have been set as above

	$sql = "SELECT debtorsmaster.debtorno,
	                 debtorsmaster.name  custname,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					debtorsmaster.currcode,
					currencies.decimalplaces,
					debtorsmaster.customerpoline,
								
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						phoneno,
						email,						
						estdeliverydays,						
						salesman,
						defaultshipvia,					
						deliverblind,
						taxrate,
						taxcatid,
						contactname
			FROM debtorsmaster
			INNER JOIN holdreasons
			ON debtorsmaster.holdreason=holdreasons.reasoncode
			INNER JOIN currencies
			ON debtorsmaster.currcode=currencies.currabrev
			WHERE debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

	$ErrMsg = _('The details for the customer selected') . ': ' .$_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('SQL used to retrieve the customer details was') . ':<br />' . $sql;
	$result =DB_query($sql,$ErrMsg,$DbgMsg);

	$myrow = DB_fetch_array($result);
	//var_dump($myrow);
	if ($myrow[1] == 0){

		$_SESSION['Items'.$identifier]->CustomerName = $myrow[0];

# the sales type determines the price list to be used by default the customer of the user is
# defaulted from the entry of the userid and password.

		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$_SESSION['Items'.$identifier]->Branch = $_SESSION['UserBranch'];//不用
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];

	// the branch would be set in the user data so default delivery details as necessary. However,
	// the order process will ask for branch details later anyway

		//$result = GetCustBranchDetails($identifier);
		//$myrow = DB_fetch_array($result);
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['custname'];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['address1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['address2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['address3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['address4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['address5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['address6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['phoneno'];
		$_SESSION['Items'.$identifier]->Email = $myrow['email'];
		$_SESSION['Items'.$identifier]->Location = $myrow['defaultlocation'];//没有使用
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['defaultshipvia'];
		$_SESSION['Items'.$identifier]->DeliverBlind = $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->LocationName = $myrow['locationname'];//没有用
		if ($_SESSION['SalesmanLogin']!= NULL AND $_SESSION['SalesmanLogin']!=''){
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		} else {
			$_SESSION['Items'.$identifier]->SalesPerson = $myrow['salesman'];
		}
	} else {
		prnMsg(_('Sorry, your account has been put on hold for some reason, please contact the credit control personnel.'),'warn');
		include('includes/footer.php');
		exit;
	}
}

if ($_SESSION['RequireCustomerSelection'] ==1
	OR !isset($_SESSION['Items'.$identifier]->DebtorNo)
	OR $_SESSION['Items'.$identifier]->DebtorNo=='') {

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' .
	' ' . _('Enter an Order or Quotation') . ' : ' . _('Search for the Customer Branch.') . '</p>';
	echo '<div class="page_help_text">' . _('Orders/Quotations are placed against the Customer Branch. A Customer may have several Branches.') . '</div>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" method="post">
		 <div>
			 <input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />
			 <table cellpadding="3" class="selection">
				<tr>
				<td>' . _('Part of the Customer Branch Name') . ':</td>
				<td><input tabindex="1" type="text" autofocus="autofocus" name="CustKeywords" size="20" maxlength="25" title="' . _('Enter a text extract of the customer\'s name, then click Search Now to find customers matching the entered name') . '" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Part of the Customer Branch Code') . ':</td>
				<td><input tabindex="2" type="text" name="CustCode" size="15" maxlength="18" title="' . _('Enter a part of a customer code that you wish to search for then click the Search Now button to find matching customers') . '" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Part of the Branch Phone Number') . ':</td>
				<td><input tabindex="3" type="text" name="CustPhone" size="15" maxlength="18" title="' . _('Enter a part of a customer\'s phone number that you wish to search for then click the Search Now button to find matching customers') . '"/></td>
				</tr>

			</table>

			<div class="centre">
				<input tabindex="4" type="submit" name="SearchCust" value="' . _('Search Now') . '" />
				<input tabindex="5" type="submit" name="reset" value="' .  _('Reset') . '" />
			</div>
		</div>';

	if (isset($result_CustSelect)) {

        echo '<div>
					<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />
					<input name="JustSelectedACustomer" type="hidden" value="Yes" />
					<br />
					<table class="selection">';

		echo '<tr>
				<th >序号</th>
				<th class="ascending" >客户编码</th>
				<th class="ascending" >' . _('Customer') . '</th>	
				<th>货币</th>
				<th>地址</th>		
				<th class="ascending" >' . _('Contact') . '</th>
				<th>' . _('Phone') . '</th>
				<th>Email</th>
			</tr>';

		$j = 1;
		$k = 0; //row counter to determine background colour
		$LastCustomer='';
		//$RowIndex=1;
		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
//    . htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false) . '</td>
			echo '<td>' .$j.'</td>
				<td>' .$myrow['debtorno'].'</td>
			    
					<td><input tabindex="'.strval($j+5).'" type="submit" name="SubmitCustomerSelection' . $j .'" value="' .htmlspecialchars($myrow['custname'], ENT_QUOTES, 'UTF-8', false). '" />
					<input name="SelectedCustomer' . $j .'" type="hidden" value="'.$myrow['debtorno'].'" />
					<input name="Selectedtag' . $j .'" type="hidden" value="'.$myrow['tag'].'" />
					<input name="SelectedBranch' . $j .'" type="hidden" value="'. $myrow['branchcode'].'" /></td>
					<td>' . $myrow['currcode']. '</td>
					<td>' . $myrow['address1'] . '</td>
					<td>' . $myrow['contactname'] . '</td>
					<td>' . $myrow['phoneno'] . '</td>
					<td>' . $myrow['email'] . '</td>
				</tr>';
			$LastCustomer=$myrow['custname'];
			$j++;
//end of page full new headings if
		}
//end of while loop
        echo '</table>
			</div>';
	}//end if results to show
	echo '</form>';
//end if RequireCustomerSelection
} //else 
if (isset($_SESSION['Items'.$identifier]->CustomerName)) {
	//var_dump($_SESSION['Items'.$identifier]);
		//添加header
		echo '<form id="form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post">';
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
		echo '<p class="page_title_text">
				<img src="' . $RootPath . '/css/' . $Theme . '/images/supplier.png" title="' . _('Purchase Order') . '" alt="" />
				' . $_SESSION['Items' . $identifier]->CustomerName . ' - ' . _('All amounts stated in') . '
				' . $_SESSION['Items' . $identifier]->CurrCode . '</p>';
	echo '<br />
	<table class="selection">
	<tr>
		<td>' .  _('Deliver To') .':</td>
		<td><input type="text"  size="42" maxlength="40" name="DeliverTo" value="' .  stripslashes($_SESSION['Items' . $identifier]->DeliverTo) . '"  /></td>
	</tr>';
	$sql = "SELECT currabrev, country, hundredsname,round(rate, decimalplaces) rate, webcart FROM currencies";
				//WHERE currabrev='" . $SelectedCurrency . "'";

		$ErrMsg = _('An error occurred in retrieving the currency information');;
		$result = DB_query($sql, $ErrMsg);

	echo'<tr>	<td>' ._('Currency') . ':</td>
			<td><select name="Currency"  onChange="inSelect(this, Currency.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" >';
		while ($row =DB_fetch_array($result)) {
			if ($row['currabrev']==$_SESSION['CompanyRecord'][$Tag]['currencydefault'] && (!isset($_POST['CurrRate']))){
				$_POST['CurrRate']=$row['rate'];
				echo '<option selected="True"  value="' . $row['currabrev'].'^'.$row['rate'] . '">[' .$row['currabrev'].']'. $row['country'] . '</option>';
			}else{
				echo '<option value="' . $row['currabrev'].'^'.$row['rate'] . '">[' .$row['currabrev'].']'. $row['country'] . '</option>';

			}
	}

		echo '</select>
				<input type="text" size="5" maxlength="5"  name="CurrRate" id="CurrRate" value="'.$_POST['CurrRate'].'" title="'.$_SESSION['CompanyRecord'][$Tag]['currencydefault'].'" />
		    </td></tr>';
		echo'<tr>
				 <td>税种:</td>
				 <td><select name="TaxCat"  id="TaxCat">';
		$TaxSql="SELECT `taxid` taxcatid, `description` taxcatname, `taxrate` FROM `taxauthorities` WHERE onorder IN (1,3)";
		//SELECT taxauthority, dispatchtaxprovince,taxcatname, a.taxcatid, taxrate FROM taxauthrates a LEFT JOIN taxcategories b ON a.taxcatid=b.taxcatid ";
		$TaxResult=DB_query($TaxSql);		
			//	DB_data_seek($TaxResult,0);
				while($row=DB_fetch_array($TaxResult)){
					if ($_POST['TaxCat']==$row['taxcatid'].'^'.$row['taxrate'] ) {
						echo '<option selected="selected" value="' .$row['taxcatid'].'^'.$row['taxrate'] . '">' . $row['taxcatname'] . '</option>';
					} else {
						echo '<option value="' . $row['taxcatid'].'^'.$row['taxrate'] . '">' . $row['taxcatname'] . '</option>';
					}
					
				}
				echo '</select></td></tr>';
		/*echo '<tr>
	<td>', _('Deliver from the warehouse at'), ':</td>
	<td><select name="Location">';
	
	if($_SESSION['Items'.$identifier]->Location=='' OR !isset($_SESSION['Items'.$identifier]->Location)) {
	$_SESSION['Items'.$identifier]->Location = $DefaultStockLocation;
	}
	
	$SQL = "SELECT locations.loccode, locationname
					FROM locations
					INNER JOIN locationusers
					ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" . $_SESSION['UserID'] . "'
						AND locationusers.canupd=1
					WHERE locations.allowinvoicing='1'
					ORDER BY locations.locationname";
	$ErrMsg = _('The stock locations could not be retrieved');
	$DbgMsg = _('SQL used to retrieve the stock locations was') . ':';
	$StkLocsResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	// COMMENT: What if there is no authorized locations available for this user?
	while($myrow=DB_fetch_array($StkLocsResult)) {
	echo '<option', ($_SESSION['Items'.$identifier]->Location==$myrow['loccode'] ? ' selected="selected"' : ''), ' value="', $myrow['loccode'], '">', $myrow['locationname'], '</option>';
	}
	echo '</select></td></tr>';*/
	
	// Set the default date to earliest possible date if not set already
	$EarliestDispatch = CalcEarliestDispatchDate();

	//if(!isset($_SESSION['Items'.$identifier]->DeliveryDate)
	if (!isset($_POST['DeliveryDate'])){
		$_POST['DeliveryDate']=Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
	   // $_SESSION['Items'.$identifier]->DeliveryDate = $_POST['DeliveryDate'];
	
   }
	if(!isset($_POST['QuoteDate'])) {
		$_POST['QuoteDate']= Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
	
	//$_SESSION['Items'.$identifier]->QuoteDate =$_POST['QuoteDate'];
	}
	if(!isset($_POST['ConfirmedDate'])) {
		$_POST['ConfirmedDate']= Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
	
	//$_SESSION['Items'.$identifier]->ConfirmedDate = Date($_SESSION['DefaultDateFormat'],$EarliestDispatch);
	}
	//prnMsg($EarliestDispatch.'-'.$_POST['DeliveryDate'] );
	
	// The estimated Dispatch date or Delivery date for this order
	$MinDate=date($_SESSION['DefaultDateFormat'],strtotime("now")-7776000*4);
	$MaxDate=date($_SESSION['DefaultDateFormat'],strtotime("now")+7776000*4);

	echo '<tr>
		<td>' .  _('Estimated Delivery Date') .':</td>
		<td><input type="date" alt="'.$_SESSION['DefaultDateFormat'].'"  min="'.$MinDate.'" max="'.$MaxDate.'"  size="15" maxlength="14" name="DeliveryDate" value="' . $_POST['DeliveryDate'] . '" onchange="return isDate(this, this.value, '."'" . '"/></td>
	</tr>';
	// The date when a quote was issued to the customer
	echo '<tr>
		<td>' .  _('Quote Date') .':</td>
	<td><input type="date" alt="'.$_SESSION['DefaultDateFormat'].'"  min="'.$MinDate.'" max="'.$MaxDate.'"  size="15" maxlength="14" name="QuoteDate" value="' . $_POST['QuoteDate'] . '" onchange="return isDate(this, this.value, '."'" . '"/></td>
			</tr>';
			//<td><input class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" size="15" maxlength="14" name="QuoteDate" value="' . $_SESSION['Items'.$identifier]->QuoteDate . '" /></td>

	// The date when the customer confirmed their order
	//	<td><input class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" size="15" maxlength="14" name="ConfirmedDate" value="' . $_SESSION['Items'.$identifier]->ConfirmedDate . '" /></td>

	echo '<tr>
		<td>' .  _('Confirmed Order Date') .':</td>
		<td><input type="date" alt="'.$_SESSION['DefaultDateFormat'].'"   min="'.$MinDate.'" max="'.$MaxDate.'"  size="15" maxlength="14" name="ConfirmedDate" value="' . $_POST['ConfirmedDate'] . '" onchange="return isDate(this, this.value, '."'" . '"/></td>
		</tr>

	
	<tr>
		<td>收货地址:</td>
		<td><input type="text" size="42" maxlength="40" name="BrAdd1" value="' . $_SESSION['Items'.$identifier]->DelAdd1 . '" /></td>
	</tr>
	
	<tr>
		<td>收货联系人:'.$_SESSION['CountryOfOperation'].'</td>
		<td><input type="text" size="10" maxlength="6" name="BrAdd2" value="' . $_SESSION['Items'.$identifier]->DelAdd2 . '" /></td>
	</tr>';
	
	echo '<tr>
		<td>' . _('Country') . ':</td>
		<td><select name="Abbreviation">';
		
		foreach ($CountriesArray  as $CurrencyCode => $CurrencyNameTxt) {
			if ($CurrencyCode==$_SESSION['CountryOfOperation']){
				echo '<option selected="True" value="' . $CurrencyCode . '">' . $CurrencyCode . ' - ' . $CurrencyNameTxt . '</option>';
	
			}else{
			echo '<option value="' . $CurrencyCode . '">' . $CurrencyCode . ' - ' . $CurrencyNameTxt . '</option>';
			}
		}
	echo '</select></td>
	</tr>';
	
	//1097   required="required"
	echo'	<tr>
		<td>' .  _('Contact Phone Number') .':</td>
		<td><input type="tel" size="25" maxlength="25"  name="PhoneNo" value="' . $_SESSION['Items'.$identifier]->PhoneNo . '" title="' . _('Enter the telephone number of the contact at the delivery address.') . '" /></td>
	</tr>
	<tr>
		<td>' . _('Contact Email') . ':</td>
		<td><input type="email" size="40" maxlength="38" name="Email" value="' . $_SESSION['Items'.$identifier]->Email . '" title="' . _('Enter the email address of the contact at the delivery address') . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Customer Reference') .':</td>
		<td><input type="text" size="25" maxlength="25" name="CustRef" value="' . $_SESSION['Items'.$identifier]->CustRef . '" title="' . _('Enter the customer\'s purchase order reference relevant to this order') . '" /></td>
	</tr>
	<tr>
		<td>' .  _('Comments') .':</td>
		<td><textarea name="Comments" cols="31" rows="5">' . $_SESSION['Items'.$identifier]->Comments  . '</textarea></td>
	</tr>';
	
	if($CustomerLogin  == 1) {
		echo '<input type="hidden" name="SalesPerson" value="' . $_SESSION['Items'.$identifier]->SalesPerson . '" />
			<input type="hidden" name="DeliverBlind" value="1" />
			<input type="hidden" name="FreightCost" value="0" />
			<input type="hidden" name="ShipVia" value="' . $_SESSION['Items'.$identifier]->ShipVia . '" />
			<input type="hidden" name="Quotation" value="0" />';
	} else {
		echo '<tr>
				<td>' . _('Sales person'). ':</td>
				<td><select name="SalesPerson">';
		$SalesPeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman WHERE current=1");
		if(!isset($_POST['SalesPerson']) AND $_SESSION['SalesmanLogin']!=NULL ) {
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		}
	
		while ($SalesPersonRow = DB_fetch_array($SalesPeopleResult)) {
			if($SalesPersonRow['salesmancode']==$_SESSION['Items'.$identifier]->SalesPerson) {
				echo '<option selected="selected" value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			} else {
				echo '<option value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			}
		}
	
		echo '</select></td>
			</tr>';
	
		/* This field will control whether or not to display the company logo and
		address on the packlist */
	
		echo '<tr><td>' . _('Packlist Type') . ':</td>
				<td><select name="DeliverBlind">';
	
		if($_SESSION['Items'.$identifier]->DeliverBlind ==2) {
			echo '<option value="1">' . _('Show Company Details/Logo') . '</option>';
			echo '<option selected="selected" value="2">' . _('Hide Company Details/Logo') . '</option>';
		} else {
			echo '<option selected="selected" value="1">' . _('Show Company Details/Logo') . '</option>';
			echo '<option value="2">' . _('Hide Company Details/Logo') . '</option>';
		}
		echo '</select></td></tr>';
	
		if(isset($_SESSION['PrintedPackingSlip']) AND $_SESSION['PrintedPackingSlip']==1) {
	
			echo '<tr>
				<td>' .  _('Reprint packing slip') .':</td>
				<td><select name="ReprintPackingSlip">';
			echo '<option value="0">' . _('Yes') . '</option>';
			echo '<option selected="selected" value="1">' . _('No') . '</option>';
			echo '</select>	'. _('Last printed') .': ' . ConvertSQLDate($_SESSION['DatePackingSlipPrinted']) . '</td></tr>';
		} else {
			echo '<tr><td><input type="hidden" name="ReprintPackingSlip" value="0" /></td></tr>';
		}
	
		echo '<tr>
				<td>' .  _('Charge Freight Cost ex tax') .':</td>
				<td><input type="text" class="number" size="10" maxlength="12" name="FreightCost" value="' . $_SESSION['Items'.$identifier]->FreightCost . '" /></td>';
	
		if($_SESSION['DoFreightCalc']==true) {
			echo '<td><input type="submit" name="Update" value="' . _('Recalc Freight Cost') . '" /></td>';
		}
		echo '</tr>';
	
		if((!isset($_POST['ShipVia']) OR $_POST['ShipVia']=='') AND isset($_SESSION['Items'.$identifier]->ShipVia)) {
			$_POST['ShipVia'] = $_SESSION['Items'.$identifier]->ShipVia;
		}
	
		echo '<tr>
				<td>' .  _('Freight/Shipper Method') .':</td>
				<td><select name="ShipVia">';
	
		$ErrMsg = _('The shipper details could not be retrieved');
		$DbgMsg = _('SQL used to retrieve the shipper details was') . ':';
	
		$sql = "SELECT shipper_id, shippername FROM shippers";
		$ShipperResults = DB_query($sql,$ErrMsg,$DbgMsg);
		while ($myrow=DB_fetch_array($ShipperResults)) {
			if($myrow['shipper_id']==$_POST['ShipVia']) {
				echo '<option selected="selected" value="' . $myrow['shipper_id'] . '">' . $myrow['shippername'] . '</option>';
			} else {
				echo '<option value="' . $myrow['shipper_id'] . '">' . $myrow['shippername'] . '</option>';
			}
		}
		echo '</select></td></tr>';
	
		echo '<tr><td>' .  _('Quotation Only') .':</td>
				<td><select name="Quotation">';
		if($_SESSION['Items'.$identifier]->Quotation==1) {
			echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
			echo '<option value="0">' . _('No') . '</option>';
		} else {
			echo '<option value="1">' . _('Yes') . '</option>';
			echo '<option selected="selected" value="0">' . _('No') . '</option>';
		}
		echo '</select></td></tr>';
	}//end if it is NOT a CustomerLogin
	
	echo '</table>';
	
	echo '<br /><div class="centre">';
	//<input type="submit" name="BackToLineDetails" value="' . _('Modify Order Lines') . '" /><br />';
	/*
	if($_SESSION['ExistingOrder'.$identifier]==0) {
	echo '<br /><br /><input type="submit" name="ProcessOrder" value="' . _('Place Order') . '" />';
	echo '<br /><br /><input type="submit" name="MakeRecurringOrder" value="' . _('Create Recurring Order') . '" />';
	} else {*/
	echo '<br /><input type="submit" name="ProcessOrder" value="继续输入订单行" />';
//	}
	
	echo '</div>
	  </div>
	  </form>';
	
		//end Header
}
//点击继续输入订单行执行
if (isset($_POST['ProcessOrder'])) { // user only hit update not "Enter Lines"
  
	//prnMsg($_POST['CurrRate']);
	$_SESSION['Items'.$identifier]->ExRate =$_POST['CurrRate'];
	$_SESSION['Items'.$identifier]->CurrCode=explode('^',$_POST['Currency'])[0];
	$_SESSION['Items'.$identifier]->DeliveryDate = $_POST['DeliveryDate'];
	$_SESSION['Items'.$identifier]->QuoteDate = Date($_SESSION['DefaultDateFormat'],$_POST['QuoteDate']);
	$_SESSION['Items'.$identifier]->ConfirmedDate=Date($_SESSION['DefaultDateFormat'],$_POST['ConfirmedDate']);
	
	$_SESSION['Items'.$identifier]->DelAdd1 = $_POST['DelAdd1'];
	$_SESSION['Items'.$identifier]->SalesPerson= '';
	$_SESSION['Items'.$identifier]->PhoneNo='';
	$_SESSION['Items'.$identifier]->BuyerName='';
	$_SESSION['Items'.$identifier]->Orig_OrderDate='';
	$_SESSION['Items'.$identifier]->totalWeight='';
	$_SESSION['Items'.$identifier]->CustRef = $_POST['CustRef'];
	//$_SESSION['Items'.$identifier]->DefaultCurrency='';
	$_SESSION['Items'.$identifier]->DeliverBlind = $_POST['DeliverBlind'];
	$_SESSION['Items'.$identifier]->ShipVia =$_POST['ShipVia'];
	$_SESSION['Items'.$identifier]->Email = '';
	$_SESSION['Items'.$identifier]->Tag='';
	$_SESSION['Items'.$identifier]->TaxRate=explode('^',$_POST['TaxCat'])[1];;
	$_SESSION['Items'.$identifier]->DeliveryDays='';
	$_SESSION['Items'.$identifier]->TaxCatID=explode('^',$_POST['TaxCat'])[0];
	$_SESSION['Items'.$identifier]->Location = '';
	$_SESSION['Items'.$identifier]->Quotation=$_POST['Quotation'];
    //跳转网页行
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/SO_Items.php?identifier=' . $identifier . '">';
	echo '<p>';
	//prnMsg(_('You should automatically be forwarded to the entry of the purchase order line items page') . '. ' . _('If this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' . '<a href="' . $RootPath . '/PO_Items.php?identifier=' . $identifier . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
	include('includes/footer.php');
	exit;
} // end if reprint not allowed
include('includes/footer.php');
/*
function GetCustBranchDetailsNo($identifier) {
	//原来分公司，现在废弃20200407
		global $db;
		defaultlocation,
					
						specialinstructions,
					LEFT JOIN locations
					ON debtorsmaster.defaultlocation=locations.loccode		locations.locationname,
		$sql = "SELECT custname,						
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						phoneno,
						email,						
						estdeliverydays,						
						salesman,
						defaultshipvia,					
						deliverblind,
						taxrate,
						taxcatid,
						contactname
					FROM debtorsmaster 
					LEFT JOIN custname_reg_sub ON custname_reg_sub.regid=debtorno
				
					WHERE debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";
				

		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
		$result = DB_query($sql,$ErrMsg,$DbgMsg);
		return $result;
}*/
?>
