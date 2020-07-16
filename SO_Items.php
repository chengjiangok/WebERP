<?php
/* $Id: SO_Items.php$*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-10-03 07:16:28 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-09-11 21:08:51
 */
include('includes/DefineCartClassCN.php');
/* Session started in session.php for password checking and authorisation level check
config.php is in turn included in session.php*/
include('includes/session.php');
if (isset($_GET['ModifyOrderNumber'])) {
	$Title = _('Modifying Order') . ' ' . $_GET['ModifyOrderNumber'];
} else {
	$Title = _('Select Order Items');
}
/* webERP manual links before header.php */
$ViewTopic= 'SalesOrders';
$BookMark = 'SalesOrderEntry';
include('includes/header.php');
include('includes/GetPrice.inc');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
function inCrPrice(p,d,r,c){	
	//alert(c);	
	var  n=p.name.substring(8);		
	var vlqty = document.getElementById("Quantity_"+n);	
	var total=0;
	var price=0;		
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	
	if ((1*p.value).toFixed(2)<(1*p.value)){
		p.value=(1*p.value).toFixed(2);
	}
	var currprice=0;
	var rate=0;
	var curramo=0;
	if (c=1){
		//alert("38");
		rate = document.getElementById("CurrRate").value;
		currprice=p.value*rate;
		document.getElementById("CurrPrice"+n).value=currprice.toFixed(2);
	}

	if (vlqty.value!=""){
		//数量不为空
		document.getElementById("edit"+n).value=1;
		total=(p.value*vlqty.value).toFixed(2);
		price=(parseFloat(p.value)/(1+parseFloat(val))).toFixed(2);

		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price_"+n).value=price;
		document.getElementById("TaxAmo"+n).value=(total/(1+parseFloat(val))*val).toFixed(2);;
        if (c=1){
			
			curramo=currprice*vlqty.value;
			document.getElementById("CurrAmo"+n).value=curramo.toFixed(2);
		}
	}
	var taxtotal=0;
	var amototal=0;
	var currtotal=0;
	for(var i=0; i<r; i++){		
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
		if (c=1){
			currtotal=parseFloat(currtotal)+parseFloat(document.getElementById("CurrAmo"+i).value);
		}
	}
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
	if (c=1){
	document.getElementById("CurrTotal").value =currtotal.toFixed(2);
	}
}
function inCrQTY(p,d,r,c){
	var  n=p.name.substring(9);	
	
	var qty=parseFloat(p.value).toFixed(d);
	if (parseFloat(p.value).toFixed(2)!=qty){
		p.value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}
	
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 中值
	var taxamo=0;
	var taxprice=0;
	var currprice=0;
	var rate=0;
	var curramo=0;
	if (c=1){
		rate = document.getElementById("CurrRate").value;
		currprice=document.getElementById("CurrPrice"+n).value;
		taxprice=currprice/rate;
		document.getElementById("TaxPrice"+n).value=taxprice.toFixed(2);
	}else{
	
	   taxprice = document.getElementById("TaxPrice"+n).value;
	}
	if (taxprice!=""){
	
		//数量不为空
		document.getElementById("edit"+n).value=1;
		total=(p.value*taxprice).toFixed(2);

		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price_"+n).value=(parseFloat(taxprice)/(1+parseFloat(val))).toFixed(2);
		
		taxamo=(total/(1+parseFloat(val))*parseFloat(val)).toFixed(2);
		document.getElementById("TaxAmo"+n).value=taxamo;
        if (c=1){
			
			curramo=currprice*p.value;
			document.getElementById("CurrAmo"+n).value=curramo.toFixed(2);
		}

	}		
	var taxtotal=0;
	var amototal=0;
	var currtotal=0;
	for(var i=0; i<r; i++){
			
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
		if (c=1)
		currtotal=parseFloat(currtotal)+parseFloat(document.getElementById("CurrAmo"+i).value);
	}
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
	if (c=1)
	document.getElementById("CurrTotal").value =currtotal.toFixed(2);
}
function inCrAmount(p,d,r,c){
	var  n=p.name.substring(6);	
	var vlqty = document.getElementById("Quantity_"+n);
	
	var qty=parseFloat(vlqty.value).toFixed(d);
	if (qty==0){
		alert("请输入数量,然后计算价格,默认"+d+"位!");

	}else if (parseFloat(vlqty.value)>qty){
		document.getElementById("Quantity_"+n).value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}	
	var taxprice=0;			
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	var currprice=0;
	var rate=0;
	var curramo=0;
	
	if (vlqty.value!=""){
		//数量不为空
		if (c=1){
			rate = document.getElementById("CurrRate").value;
			taxprice=p.value/qty;
			currprice=taxprice*parseFloat(rate);			
			document.getElementById("CurrPrice"+n).value=currprice.toFixed(2);
		}else{
			taxprice=(parseFloat(p.value)/parseFloat(qty)).toFixed(2);
		}
		document.getElementById("edit"+n).value=1;	
		document.getElementById("TaxPrice"+n).value=taxprice.toFixed(2);		
		document.getElementById("Price_"+n).value=(parseFloat(taxprice)/(1+parseFloat(val))).toFixed(2);
		document.getElementById("TaxAmo"+n).value=(parseFloat(p.value)/(1+parseFloat(val))*val).toFixed(2);
		if (c=1){			
			curramo=currprice.toFixed(2)*qty;
			document.getElementById("CurrAmo"+n).value=curramo.toFixed(2);
		}
	}
	var taxtotal=0;
	var amototal=0;
	var currtotal=0;
	for(var i=0; i<r; i++){
			
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
		if (c=1)
		currtotal=parseFloat(currtotal)+parseFloat(document.getElementById("CurrAmo"+i).value);
	}
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
	if (c=1)
	document.getElementById("CurrTotal").value =currtotal.toFixed(2);
}
function inCurrPrice(p,r){		
	var  n=p.name.substring(9);		
	var rate = document.getElementById("CurrRate").value;
	var taxprice=p.value/rate;
	document.getElementById("TaxPrice"+n).value=taxprice.toFixed(2);
	var vlqty = document.getElementById("Quantity_"+n);
	var total=0;
	var price=0;
	var curramo=0;
			
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	

	if (vlqty.value!=""){
		//数量不为空
		document.getElementById("edit"+n).value=1;
		total=(taxprice*vlqty.value).toFixed(2);
		curramo=(p.value*vlqty.value).toFixed(2);
		//alert(curramo);
		price=(parseFloat(taxprice)/(1+parseFloat(val))).toFixed(2);

		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price_"+n).value=price;
		document.getElementById("TaxAmo"+n).value=(total/(1+parseFloat(val))*val).toFixed(2);;
		document.getElementById("CurrAmo"+n).value=curramo;
	}
	var taxtotal=0;
	var amototal=0;
	var currtotal=0;
	for(var i=0; i<r; i++){		
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
		currtotal+=parseFloat(document.getElementById("CurrAmo"+i).value.replace(",",""));
	}
	
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);	
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("CurrTotal").value =currtotal.toFixed(2);
}
function inCurrAmo(p,r){		
	var  n=p.name.substring(7);
	//alert(p.name);		
	var rate = document.getElementById("CurrRate").value;	
	var vlqty = document.getElementById("Quantity_"+n);	
	var taxprice=0;
	var total=0;
	var price=0;
	var curramo=0;
	var currprice=0;
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	var qty=0;
	if (vlqty.value!=""){
		//数量不为空
		qty=vlqty.value.replace(",","");
		currprice=p.value/qty;

		document.getElementById("edit"+n).value=1;
		taxprice=currprice/rate;
		total=(taxprice*qty).toFixed(2);		
		price=(parseFloat(taxprice)/(1+parseFloat(val))).toFixed(2);
		document.getElementById("CurrPrice"+n).value=currprice.toFixed(2);
		document.getElementById("TaxPrice"+n).value=taxprice.toFixed(2);
		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price_"+n).value=price;
		document.getElementById("TaxAmo"+n).value=(total/(1+parseFloat(val))*val).toFixed(2);;
		
	}
	
	var taxtotal=0;
	var amototal=0;
	var currtotal=0;
	for(var i=0; i<r; i++){		
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
		currtotal+=parseFloat(document.getElementById("CurrAmo"+i).value.replace(",",""));
	}
	
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);	
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("CurrTotal").value =currtotal.toFixed(2);

}

function inPrice(p,d,r){
	//价格变动后计算		
	var  n=p.name.substring(8);		
	var vlqty = document.getElementById("Quantity_"+n);
	var qty=vlqty.value.replace(",","");
	var Q=qty.length+1;
	if (Q<3){
		Q=4;
	}else{
		Q=6;
	}
	var total=0;
	var price=0;		
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	
	if ((1*p.value).toFixed(2)<(1*p.value)){
		p.value=(1*p.value).toFixed(2);
	}
	if (vlqty.value!=""){
		//数量不为空
		document.getElementById("edit"+n).value=1;
		total=(p.value*qty).toFixed(2);
		price=(parseFloat(p.value)/(1+parseFloat(val))).toFixed(2);

		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price_"+n).value=price;
		document.getElementById("TaxAmo"+n).value=(total/(1+parseFloat(val))*val).toFixed(2);;

	}
	var taxtotal=0;
	var amototal=0;
	for(var i=0; i<r; i++){		
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
	}
	
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);	
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
}
function inQTY(p,d,r){
	var  n=p.name.substring(9);	
	
	var vl = document.getElementById("TaxPrice"+n);
	var vlprice=vl.value;
	/*
	var Q=(vlprice).length+1;
	if (Q<3){
		Q=4;
	}else{
		Q=6;
	}*/
	var qty=(1*p.value).toFixed(d);
	if (parseFloat(p.value).toFixed(2)!=qty){
		p.value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}
	
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	var taxamo=0;
	if (vl.value!=""){
	
		//数量不为空
		document.getElementById("edit"+n).value=1;
		total=(p.value*vlprice).toFixed(2);

		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price_"+n).value=(parseFloat(vlprice)/(1+parseFloat(val))).toFixed(2);
		
		taxamo=(total/(1+parseFloat(val))*parseFloat(val)).toFixed(2);
		document.getElementById("TaxAmo"+n).value=taxamo;
	}		
	var taxtotal=0;
	var amototal=0;
	for(var i=0; i<r; i++){
			
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
	}
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
}
function inAmount(p,d,r){
	var  n=p.name.substring(6);	
	var taxtotal=0;
	var amototal=0;
	if (parseFloat(p.value).toFixed(2)!=parseFloat(p.value)){
		
		p.value=parseFloat(p.value).toFixed(2);
	}
	var vlqty = document.getElementById("Quantity_"+n);
	
	var qty=parseFloat(vlqty.value.replace(",","")).toFixed(d);
	var Q=qty.length+1;
	if (Q<3){
		Q=4;
	}else{
		Q=6;
	}
	if (qty==0){
		alert("请输入数量,然后计算价格,默认"+d+"位!");

	}else if (parseFloat(vlqty.value.replace(",",""))>qty){
		document.getElementById("Quantity_"+n).value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}	
	var vlprice=0;			
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	if (vlqty.value!=""){
		//数量不为空
		document.getElementById("edit"+n).value=1;
		vlprice=(parseFloat(p.value)/parseFloat(qty)).toFixed(Q);
		document.getElementById("TaxPrice"+n).value=vlprice;		
		document.getElementById("Price_"+n).value=(parseFloat(vlprice)/(1+parseFloat(val))).toFixed(Q);
		document.getElementById("TaxAmo"+n).value=(parseFloat(p.value)/(1+parseFloat(val))*val).toFixed(2);
	}
	for(var i=0; i<r; i++){
		
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
		
	}

	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
}
function inCurrRate(p,d,t,r){		
  
	if ((parseFloat(p.value)-t)/p.value>0.10){
	   alert("修改的汇率不能大于系统汇率的10%");
		p.value=t;

	}
	var taxprice=0;
	var total=0;
	var qty=0;
	var price=0;
	var curramo=0;
	var currprice=0;	
	var taxtotal=0;
	var amototal=0;
	var currtotal=0;
	var obj;
	var rate= document.getElementById("CurrRate").value; 
	var index =0;		
	var val = 0;
	var vv="";
	for(var i=0; i<r; i++){	
		currprice=document.getElementById("CurrPrice"+i).value;
		qty = document.getElementById("Quantity_"+i).value;
	    curramo=currprice*qty; 
		document.getElementById("edit"+i).value=1;
		taxprice=currprice/rate;
		total=(taxprice*qty).toFixed(2);	
		obj = document.getElementById("TaxCat"+i); 
		index = obj.selectedIndex; // 选中索引	
		vv = obj.options[index].value	;;	
	    val = obj.options[index].value.split("^")[1]; // 选中值	   
		price=(parseFloat(taxprice)/(1+parseFloat(val))).toFixed(2);
		
		document.getElementById("TaxPrice"+i).value=taxprice.toFixed(2);
		document.getElementById("Amount"+i).value=total;
		document.getElementById("Price_"+i).value=price;
		document.getElementById("TaxAmo"+i).value=(total/(1+parseFloat(val))*val).toFixed(2);;
		document.getElementById("CurrAmo"+i).value=curramo.toFixed(2);
	
		taxtotal+=parseFloat(document.getElementById("TaxAmo"+i).value.replace(",",""));
		amototal+=parseFloat(document.getElementById("Amount"+i).value.replace(",",""));
		currtotal+=parseFloat(document.getElementById("CurrAmo"+i).value.replace(",",""));
	}
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);	
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("CurrTotal").value =currtotal.toFixed(2); 
	
}
	function refresh() {  
		window.location.reload();
	}  
</script>';	
if (isset($_POST['QuickEntry'])){
	unset($_POST['PartSearch']);
}
$Tag=1;
//增销售订单,读取数量->$NewItemArray
if (isset($_POST['SelectingOrderItems'])){

	foreach ($_POST as $FormVariable => $Quantity) {
		
		if (mb_strpos($FormVariable,'OrderQty')!==false && $Quantity!=0) {
		
		
			$NewItemArray[$_POST['StockID' . mb_substr($FormVariable,8)]] = filter_number_format($Quantity);
		}
	}
}

if (isset($_GET['NewItem'])){
	$NewItem = trim($_GET['NewItem']);
}
/*
if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}*/
$identifier=$_GET['identifier'];
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
		//$_SESSION['Items'.$identifier]->BranchCode=$_SESSION['UserBranch'];
		$SelectedCustomer = $_SESSION['CustomerID'];
		//$SelectedBranch = $_SESSION['UserBranch'];
		$_SESSION['RequireCustomerSelection'] = 0;
	} else {
		$_SESSION['Items'.$identifier]->DebtorNo='';
		//$_SESSION['Items'.$identifier]->BranchCode='';
		$_SESSION['RequireCustomerSelection'] = 1;
	}

}
//$_SESSION['Items'.$identifier]->Location =11;
//读取已经存在合同
if (isset($_GET['ModifyOrderNumber'])	AND $_GET['ModifyOrderNumber']!=''){

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
			 				  debtorsmaster.name,
							  salesorders.branchcode,
							  salesorders.customerref,
							  salesorders.comments,
							  salesorders.orddate,
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
							  salesorders.tag,
							  salesorders.currcode,
							  salesorders.rate,							 
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
							  debtorsmaster.estdeliverydays,
							  debtorsmaster.salesman
						FROM salesorders
						INNER JOIN debtorsmaster
						ON salesorders.debtorno = debtorsmaster.debtorno
						INNER JOIN salestypes
						ON salesorders.ordertype=salestypes.typeabbrev
						
						INNER JOIN paymentterms
						ON debtorsmaster.paymentterms=paymentterms.termsindicator
						INNER JOIN currencies ON debtorsmaster.currcode = currencies.currabrev					
						WHERE salesorders.orderno = '" . $_GET['ModifyOrderNumber'] . "'";
  	
	$ErrMsg =  _('The order cannot be retrieved because');
	$GetOrdHdrResult = DB_query($OrderHeaderSQL,$ErrMsg);
  
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
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];
		
		$_SESSION['Items'.$identifier]->CustRef = $myrow['customerref'];
		$_SESSION['Items'.$identifier]->Tag = $myrow['tag'];
		$_SESSION['Items'.$identifier]->CurrCode = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->ExRate = $myrow['rate'];
		$_SESSION['Items'.$identifier]->Comments = stripcslashes($myrow['comments']);
		$_SESSION['Items'.$identifier]->PaymentTerms =$myrow['terms'];
		$_SESSION['Items'.$identifier]->DefaultSalesType =$myrow['ordertype'];
		$_SESSION['Items'.$identifier]->SalesTypeName =$myrow['sales_type'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['shipvia'];
		$BestShipper = $myrow['shipvia'];
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['deliverto'];
		$_SESSION['Items'.$identifier]->DeliveryDate = ConvertSQLDate($myrow['deliverydate']);
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['deladd1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['deladd2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['deladd3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['deladd4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['deladd5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['deladd6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['contactphone'];
		$_SESSION['Items'.$identifier]->Email = $myrow['contactemail'];
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
									stockmaster.categoryid loccode,
									salesorderdetails.unitprice,
									salesorderdetails.quantity,
									salesorderdetails.discountpercent,
									salesorderdetails.actualdispatchdate,
									salesorderdetails.qtyinvoiced,
									salesorderdetails.narrative,
									salesorderdetails.itemdue,
									salesorderdetails.poline,
									salesorderdetails.cess,
									salesorderdetails.taxprice,
									salesorderdetails.currprice,
									locstock.quantity as qohatloc,
									stockmaster.mbflag,
									stockmaster.discountcategory,
									stockmaster.decimalplaces,
									stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS standardcost,
									salesorderdetails.completed,
									salesorderdetails.amount,
									salesorderdetails.curramount
								FROM salesorderdetails
								LEFT JOIN stockmaster	ON salesorderdetails.stkcode = stockmaster.stockid
								LEFT JOIN locstock ON locstock.stockid = stockmaster.stockid
								WHERE   salesorderdetails.orderno ='" . $_GET['ModifyOrderNumber'] . "'
								ORDER BY salesorderdetails.orderlineno";

		$ErrMsg = _('The line items of the order cannot be retrieved because');
		
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
																	$myrow['cess'],
																	$myrow['volume'],
																	$myrow['grossweight'],

																	$myrow['amount'],
																	$myrow['curramount'],
																	
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
																	0,
																	-1,
																	ConvertSQLDate($myrow['itemdue']),
																	$myrow['poline'],
																	$myrow['standardcost'],
																	$myrow['eoq'],
																	$myrow['nextserialno'],
																	$ExRate,
																	$identifier,
																    $myrow['loccode'] );//35

				/*Just populating with existing order - no DBUpdates */
					}
					$LastLineNo = $myrow['orderlineno'];
			} /* line items from sales order details */
			 $_SESSION['Items'.$identifier]->LineCounter = $LastLineNo+1;
			
		} //end of checks on returned data set
	}
}
	/*

	if (!isset($_SESSION['Items'.$identifier])){
		

		$_SESSION['ExistingOrder'.$identifier]=0;
		$_SESSION['Items'.$identifier] = new cart;
		$_SESSION['PrintedPackingSlip'] = 0; 



		if (in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])
			AND ($_SESSION['Items'.$identifier]->DebtorNo==''
			OR !isset($_SESSION['Items'.$identifier]->DebtorNo))){


			$_SESSION['RequireCustomerSelection'] = 1;
		} else {
			$_SESSION['RequireCustomerSelection'] = 0;
		}
	}
	*/
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
		//prnMsg('771这个代码没有使用！');
		$SQL = "SELECT 
					debtorsmaster.contactname,
					debtorsmaster.tag,
					debtorsmaster.phoneno,
				
					debtorsmaster.debtorno,
					debtorsmaster.name,
					taxcatid,
					taxrate
				FROM  debtorsmaster
		
				INNER JOIN customerusers ON debtorsmaster.debtorno=regid 
				WHERE  userid='".$_SESSION['UserID']."'";

	if (($_POST['CustKeywords']=='') AND ($_POST['CustCode']=='')  AND ($_POST['CustPhone']=='')) {
		$SQL .= "";
	} else {
		//insert wildcard characters in spaces
		$_POST['CustKeywords'] = mb_strtoupper(trim($_POST['CustKeywords']));
		$SearchString = str_replace(' ', '%', $_POST['CustKeywords']) ;

		$SQL .= "AND debtorsmaster.name " . LIKE . " '%" . $SearchString . "%'
				AND debtorsmaster.debtorno " . LIKE . " '%" . mb_strtoupper(trim($_POST['CustCode'])) . "%'
				AND debtorsmaster.phoneno " . LIKE . " '%" . trim($_POST['CustPhone']) . "%'";

	} /*one of keywords or custcode was more than a zero length string */
	if ($_SESSION['SalesmanLogin']!=''){
		$SQL .= " AND debtorsmaster.salesman='" . $_SESSION['SalesmanLogin'] . "'";
	}
	$SQL .=	" ORDER BY debtorsmaster.debtorno
					";

	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	$result_CustSelect = DB_query($SQL,$ErrMsg);
   
	if (DB_num_rows($result_CustSelect)==1){
		$myrow=DB_fetch_array($result_CustSelect);
		$SelectedCustomer = $myrow['debtorno'];
		//$SelectedBranch = $myrow['branchcode'];
		$Selectedtag=$myrow['tag'];
	} elseif (DB_num_rows($result_CustSelect)==0){
		prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'),'info');
	}
} /*end of if search for customer codes/names */

if (isset($_POST['JustSelectedACustomer'])){
   
	/*Need to figure out the number of the form variable that the user clicked on */
	for ($i=0;$i<count($_POST);$i++){ //loop through the returned customers
		if(isset($_POST['SubmitCustomerSelection'.$i])){
			break;
		}
	}
	if ($i==count($_POST) AND !isset($SelectedCustomer)){//if there is ONLY one customer searched at above, the $SelectedCustomer already setup, then there is a wrong warning
		prnMsg(_('Unable to identify the selected customer'),'error');
	} elseif(!isset($SelectedCustomer)) {
		$SelectedCustomer = $_POST['SelectedCustomer'.$i];
		//$SelectedBranch = $_POST['SelectedBranch'.$i];
		$Selectedtag = $_POST['Selectedtag'.$i];
	}
}
/* will only be true if page called from customer selection form or set because only one customer
 record returned from a search so parse the $SelectCustomer string into customer code and branch code */
if (isset($SelectedCustomer)) {

	$_SESSION['Items'.$identifier]->DebtorNo = trim($SelectedCustomer);
	//$_SESSION['Items'.$identifier]->Branch = trim($SelectedBranch);
	$_SESSION['Items'.$identifier]->tag = trim($Selectedtag);
	// Now check to ensure this account is not on hold */
	$sql = "SELECT debtorsmaster.name,
	               holdreasons.dissallowinvoices,
				   debtorsmaster.salestype,
					salestypes.sales_type, 
					debtorsmaster.currcode,
					debtorsmaster.customerpoline, 
					paymentterms.terms, 
					currencies.decimalplaces,

					debtorsmaster.debtorno,							
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,							
					debtorsmaster.clientsince,
					debtorsmaster.holdreason,
					debtorsmaster.paymentterms,
					debtorsmaster.discount,
					debtorsmaster.pymtdiscount,
					debtorsmaster.lastpaid,
					debtorsmaster.lastpaiddate,
					debtorsmaster.creditlimit,
					debtorsmaster.invaddrbranch,
					debtorsmaster.estdeliverydays,
					debtorsmaster.discountcode,
					debtorsmaster.ediinvoices,
					debtorsmaster.ediorders,
					debtorsmaster.edireference,
					debtorsmaster.editransport,
					debtorsmaster.ediaddress,
					debtorsmaster.ediserveruser,
					debtorsmaster.ediserverpwd,
					debtorsmaster.taxcatid,
					debtorsmaster.taxrate,							
					debtorsmaster.typeid,
					debtorsmaster.remark,
					debtorsmaster.contactname,
					debtorsmaster.salesman,
					debtorsmaster.phoneno,
					debtorsmaster.faxno,
					debtorsmaster.email,
					debtorsmaster.userid,
					debtorsmaster.language_id,
					debtorsmaster.used
          FROM debtorsmaster 
		  INNER JOIN holdreasons ON debtorsmaster.holdreason=holdreasons.reasoncode 
          INNER JOIN salestypes ON debtorsmaster.salestype=salestypes.typeabbrev 
          INNER JOIN paymentterms ON debtorsmaster.paymentterms=paymentterms.termsindicator 
          INNER JOIN currencies ON debtorsmaster.currcode=currencies.currabrev 
          WHERE debtorsmaster.debtorno  = '" . $_SESSION['Items'.$identifier]->DebtorNo. "'";

	$ErrMsg = _('The details of the customer selected') . ': ' .  $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the customer details and failed was') . ':';
	$result =DB_query($sql,$ErrMsg,$DbgMsg);
	
	$myrow = DB_fetch_array($result);
	if ($myrow[1] != 1){
		if ($myrow[1]==2){
			prnMsg(_('The') . ' ' . htmlspecialchars($myrow[0], ENT_QUOTES, 'UTF-8', false) . ' ' . _('account is currently flagged as an account that needs to be watched. Please contact the credit control personnel to discuss'),'warn');
		}

		$_SESSION['RequireCustomerSelection']=0;
		$_SESSION['Items'.$identifier]->CustomerName = $myrow['name'];

	# the sales type determines the price list to be used by default the customer of the user is
	# defaulted from the entry of the userid and password.

		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
		$_SESSION['Items'.$identifier]->SalesTypeName = $myrow['sales_type'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];
		$_SESSION['Items'.$identifier]->PaymentTerms = $myrow['terms'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
	
	# the branch was also selected from the customer selection so default the delivery details from the customer branches table CustBranch. The order process will ask for branch details later anyway
	//	$result = GetCustBranchDetails($identifier);
		
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
		$myrow = DB_fetch_array($result);
		if ($_SESSION['SalesmanLogin']!=NULL AND $_SESSION['SalesmanLogin']!=$myrow['salesman']){
			prnMsg(_('Your login is only set up for a particular salesperson. This customer has a different salesperson.'),'error');
			include('includes/footer.php');
			exit;
		}
		$_SESSION['Items'.$identifier]->TaxCatID = $myrow['taxcatid'];
		$_SESSION['Items'.$identifier]->TaxRate = $myrow['taxrate'];
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['name'];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['braddress1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['braddress2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['braddress3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['braddress4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['braddress5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['braddress6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['phoneno'];
		$_SESSION['Items'.$identifier]->Email = $myrow['email'];
		$_SESSION['Items'.$identifier]->Location = 0;//$myrow['defaultlocation'];不用
		$_SESSION['Items'.$identifier]->ShipVia = $myrow['defaultshipvia'];
		$_SESSION['Items'.$identifier]->DeliverBlind =$myrow['deliverblind'];//交付
		$_SESSION['Items'.$identifier]->SpecialInstructions = $myrow['specialinstructions'];
		$_SESSION['Items'.$identifier]->DeliveryDays = $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->LocationName ='';// $myrow['locationname'];
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
	                 debtorsmaster.name,
					holdreasons.dissallowinvoices,
					debtorsmaster.salestype,
					debtorsmaster.currcode,
					currencies.decimalplaces,
					debtorsmaster.customerpoline,
										
					debtorsmaster.address1,
					debtorsmaster.address2,
					debtorsmaster.address3,
					debtorsmaster.address4,
					debtorsmaster.address5,
					debtorsmaster.address6,							
					debtorsmaster.clientsince,
					debtorsmaster.holdreason,
					debtorsmaster.paymentterms,
					debtorsmaster.discount,
					debtorsmaster.pymtdiscount,
					debtorsmaster.lastpaid,
					debtorsmaster.lastpaiddate,
					debtorsmaster.creditlimit,
					debtorsmaster.invaddrbranch,
					debtorsmaster.estdeliverydays,
					debtorsmaster.discountcode,
					debtorsmaster.ediinvoices,
					debtorsmaster.ediorders,
					debtorsmaster.edireference,
					debtorsmaster.editransport,
					debtorsmaster.ediaddress,
					debtorsmaster.ediserveruser,
					debtorsmaster.ediserverpwd,
					debtorsmaster.taxcatid,
					debtorsmaster.taxrate,							
					debtorsmaster.typeid,
					debtorsmaster.remark,
					debtorsmaster.contactname,
					debtorsmaster.salesman,
					debtorsmaster.phoneno,
					debtorsmaster.faxno,
					debtorsmaster.email,
					debtorsmaster.userid,
					debtorsmaster.language_id,
					debtorsmaster.used
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
	if ($myrow[1] == 0){

		$_SESSION['Items'.$identifier]->CustomerName = $myrow[0];

	# the sales type determines the price list to be used by default the customer of the user is
	# defaulted from the entry of the userid and password.

		$_SESSION['Items'.$identifier]->DefaultSalesType = $myrow['salestype'];
		$_SESSION['Items'.$identifier]->DefaultCurrency = $myrow['currcode'];
		$_SESSION['Items'.$identifier]->CurrDecimalPlaces = $myrow['decimalplaces'];
		$_SESSION['Items'.$identifier]->Branch = $_SESSION['UserBranch'];
		$_SESSION['Items'.$identifier]->DefaultPOLine = $myrow['customerpoline'];

	// the branch would be set in the user data so default delivery details as necessary. However,
	// the order process will ask for branch details later anyway

		//$result = GetCustBranchDetails($identifier);
		$myrow = DB_fetch_array($result);
		$_SESSION['Items'.$identifier]->DeliverTo = $myrow['brname'];
		$_SESSION['Items'.$identifier]->DelAdd1 = $myrow['braddress1'];
		$_SESSION['Items'.$identifier]->DelAdd2 = $myrow['braddress2'];
		$_SESSION['Items'.$identifier]->DelAdd3 = $myrow['braddress3'];
		$_SESSION['Items'.$identifier]->DelAdd4 = $myrow['braddress4'];
		$_SESSION['Items'.$identifier]->DelAdd5 = $myrow['braddress5'];
		$_SESSION['Items'.$identifier]->DelAdd6 = $myrow['braddress6'];
		$_SESSION['Items'.$identifier]->PhoneNo = $myrow['phoneno'];
		$_SESSION['Items'.$identifier]->Email = $myrow['email'];
		$_SESSION['Items'.$identifier]->Location =0;// $myrow['defaultlocation'];
		$_SESSION['Items'.$identifier]->DeliverBlind =0;// $myrow['deliverblind'];
		$_SESSION['Items'.$identifier]->DeliveryDays =0;// $myrow['estdeliverydays'];
		$_SESSION['Items'.$identifier]->LocationName = '';//$myrow['locationname'];
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
				<th class="ascending" >' . _('Customer') . '</th>
				<th class="ascending" >' . _('Branch') . '</th>
				<th class="ascending" >' . _('Contact') . '</th>
				<th>1127' . _('Phone') . '</th>
				<th>' . _('Fax') . '</th>
			</tr>';

		$j = 1;
		$k = 0; //row counter to determine background colour
		$LastCustomer='';
		while ($myrow=DB_fetch_array($result_CustSelect)) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}

			echo '	<td>[' .$myrow['debtorno'].']'. htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false) . '</td>
					<td><input tabindex="'.strval($j+5).'" type="submit" name="SubmitCustomerSelection' . $j .'" value="' . htmlspecialchars($myrow['brname'], ENT_QUOTES, 'UTF-8', false). '" />
					<input name="SelectedCustomer' . $j .'" type="hidden" value="'.$myrow['debtorno'].'" />
					<input name="Selectedtag' . $j .'" type="hidden" value="'.$myrow['tag'].'" />
					<input name="SelectedBranch' . $j .'" type="hidden" value="'. $myrow['branchcode'].'" /></td>
					<td>' . $myrow['contactname'] . '</td>
					<td>' . $myrow['phoneno'] . '</td>
					<td>' . $myrow['faxno'] . '</td>
				</tr>';
			$LastCustomer=$myrow['name'];
			$j++;
	//end of page full new headings if
		}
	//end of while loop
        echo '</table>
			</div>';
	}//end if results to show
	echo '</form>';
	//end if RequireCustomerSelection
}else{ //dont require customer selection
	// everything below here only do if a customer is selected

 	if (isset($_POST['CancelOrder'])) {
		$OK_to_delete=1;	//assume this in the first instance

		if($_SESSION['ExistingOrder' . $identifier]!=0) { //need to check that not already dispatched

			$sql = "SELECT qtyinvoiced
					FROM salesorderdetails
					WHERE orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'
					AND qtyinvoiced>0";

			$InvQties = DB_query($sql);

			if (DB_num_rows($InvQties)>0){

				$OK_to_delete=0;

				prnMsg( _('There are lines on this order that have already been invoiced. Please delete only the lines on the order that are no longer required') . '<p>' . _('There is an option on confirming a dispatch/invoice to automatically cancel any balance on the order at the time of invoicing if you know the customer will not want the back order'),'warn');
			}
		}

		if ($OK_to_delete==1){
			if($_SESSION['ExistingOrder' . $identifier]!=0){

				$SQL = "DELETE FROM salesorderdetails WHERE salesorderdetails.orderno ='" . $_SESSION['ExistingOrder' . $identifier] . "'";
				$ErrMsg =_('The order detail lines could not be deleted because');
				$DelResult=DB_query($SQL,$ErrMsg);

				$SQL = "DELETE FROM salesorders WHERE salesorders.orderno='" . $_SESSION['ExistingOrder' . $identifier] . "'";
				$ErrMsg = _('The order header could not be deleted because');
				$DelResult=DB_query($SQL,$ErrMsg);

				$_SESSION['ExistingOrder' . $identifier]=0;
			}

			unset($_SESSION['Items'.$identifier]->LineItems);
			$_SESSION['Items'.$identifier]->ItemsOrdered=0;
			unset($_SESSION['Items'.$identifier]);
			$_SESSION['Items'.$identifier] = new cart;

			if (in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])){
				$_SESSION['RequireCustomerSelection'] = 1;
			} else {
				$_SESSION['RequireCustomerSelection'] = 0;
			}
			echo '<br /><br />';
			prnMsg(_('This sales order has been cancelled as requested'),'success');
			include('includes/footer.php');
			exit;
		}
	}else { /*Not cancelling the order */

		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Order') . '" alt="" />' . ' ';

		if ($_SESSION['Items'.$identifier]->Quotation==1){
			echo _('Quotation for customer') . ' ';
		} else {
			echo _('Order for customer') . ' ';
		}

		echo ':<b> ' . $_SESSION['Items'.$identifier]->DebtorNo  . ' ' . _('Customer Name') . ': ' . htmlspecialchars($_SESSION['Items'.$identifier]->CustomerName, ENT_QUOTES, 'UTF-8', false);
		echo '</b></p><div class="page_help_text">' . '<b>' . _('Default Options (can be modified during order):') . '</b><br />' . _('Deliver To') . ':<b> ' . htmlspecialchars($_SESSION['Items'.$identifier]->DeliverTo, ENT_QUOTES, 'UTF-8', false);
		echo '</b>&nbsp;' . _('From Location') . ':<b> ' . $_SESSION['Items'.$identifier]->LocationName;
		echo '</b><br />' . _('Sales Type') . '/' . _('Price List') . ':<b> ' . $_SESSION['Items'.$identifier]->SalesTypeName;
		echo '</b><br />' . _('Terms') . ':<b> ' . $_SESSION['Items'.$identifier]->PaymentTerms;
		echo '</b></div>';
	}
	$msg ='';
	if (isset($_POST['Search']) OR isset($_POST['Next']) OR isset($_POST['Previous'])){
		if(!empty($_POST['RawMaterialFlag'])){
			$RawMaterialSellable = " OR stockcategory.stocktype='M'";
		}else{
			$RawMaterialSellable = '';
		}
		if(!empty($_POST['CustItemFlag'])){
			$IncludeCustItem = " INNER JOIN custitem ON custitem.stockid=stockmaster.stockid
								AND custitem.debtorno='" .  $_SESSION['Items'.$identifier]->DebtorNo . "' ";
		} else {
			$IncludeCustItem = " LEFT OUTER JOIN custitem ON custitem.stockid=stockmaster.stockid
								AND custitem.debtorno='" .  $_SESSION['Items'.$identifier]->DebtorNo . "' ";
		}

		if ($_POST['Keywords']!='' AND $_POST['StockCode']=='') {
			$msg='<div class="page_help_text">' . _('Order Item description has been used in search') . '.</div>';
		} elseif ($_POST['StockCode']!='' AND $_POST['Keywords']=='') {
			$msg='<div class="page_help_text">' . _('Stock Code has been used in search') . '.</div>';
		} elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
			$msg='<div class="page_help_text">' . _('Stock Category has been used in search') . '.</div>';
		}
		$SQL = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.units,
						custitem.cust_part,
						custitem.cust_description,
						stockmaster.categoryid loccode
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				" . $IncludeCustItem . "
				WHERE (stockcategory.stocktype='M' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L' " . $RawMaterialSellable . ")
				AND stockmaster.mbflag <>'G'
				AND stockmaster.discontinued=0 ";
          //WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L' " . $RawMaterialSellable . ")
		if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			if ($_POST['StockCat']=='All'){
				$SQL .= "AND stockmaster.description " . LIKE . " '" . $SearchString . "'
					ORDER BY stockmaster.stockid";
			} else {
				$SQL .= "AND (stockmaster.description " . LIKE . " '" . $SearchString . "' OR stockmaster.longdescription " . LIKE . " '" . $SearchString . "') 
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid";
			}

		} elseif (mb_strlen($_POST['StockCode'])>0){

			$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
			$SearchString = '%' . $_POST['StockCode'] . '%';

			if ($_POST['StockCat']=='All'){
				$SQL .= "AND (stockmaster.stockid " . LIKE . " '" . $SearchString . "'
				     OR stockmaster.stockno " . LIKE . " '" . $SearchString . "') 
					ORDER BY stockmaster.stockid";
			} else {
				$SQL .= "AND ( stockmaster.stockid " . LIKE . " '" . $SearchString . "' 
				      OR stockmaster.stockno " . LIKE . " '" . $SearchString . "') 
					 AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					 ORDER BY stockmaster.stockid";
			}

		} else {
			if ($_POST['StockCat']=='All'){
				$SQL .= "ORDER BY stockmaster.stockid";
			} else {
				$SQL .= "AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					 ORDER BY stockmaster.stockid";
			  }
		}

		if (isset($_POST['Next'])) {
			$Offset = $_POST['NextList'];
		}
		if (isset($_POST['Previous'])) {
			$Offset = $_POST['PreviousList'];
		}
		if (!isset($Offset) OR $Offset < 0) {
			$Offset=0;
		}

		$SQL = $SQL . " LIMIT " . $_SESSION['DisplayRecordsMax'] . " OFFSET " . strval($_SESSION['DisplayRecordsMax'] * $Offset);

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL used to get the part selection was');

		$SearchResult = DB_query($SQL,$ErrMsg, $DbgMsg);
 	
		if (DB_num_rows($SearchResult)==0 ){
			prnMsg (_('There are no products available meeting the criteria specified'),'info');
		}
		if (DB_num_rows($SearchResult)==1){
			$myrow=DB_fetch_array($SearchResult);
			$NewItem = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}
		if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']){
			$Offset=0;
		}
	} //end of if search

	#Always do the stuff below if not looking for a customerid

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" id="SelectParts" method="post">';
    echo '<div>';
	echo '<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />';

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


		/*/快速录入循环Process Quick Entry */
		/* If enter is pressed on the quick entry screen, the default button may be Recalculate */
	 if (isset($_POST['SelectingOrderItems'])	OR isset($_POST['QuickEntry'])	OR isset($_POST['Recalculate'])){
		
		 /* get the item details from the database and hold them in the cart object */

		 /*Discount can only be set later on  -- after quick entry -- so default discount to 0 in the first place */
		$Discount = 0;
		$AlreadyWarnedAboutCredit = false;
		 $i=1;
		 //快速录入10循环-1022end
		  while ($i<=$_SESSION['QuickEntries'] AND isset($_POST['part_' . $i]) AND $_POST['part_' . $i]!='') {
			$QuickEntryCode = 'part_' . $i;
			$QuickEntryQty = 'qty_' . $i;
			$QuickEntryPOLine = 'poline_' . $i;
			$QuickEntryItemDue = 'itemdue_' . $i;

			$i++;

			if (isset($_POST[$QuickEntryCode])) {
				$NewItem = mb_strtoupper($_POST[$QuickEntryCode]);
			}
			if (isset($_POST[$QuickEntryQty])) {
				$NewItemQty = filter_number_format($_POST[$QuickEntryQty]);
			}
			if (isset($_POST[$QuickEntryItemDue])) {
				$NewItemDue = $_POST[$QuickEntryItemDue];
			} else {
				$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
			}
			if (isset($_POST[$QuickEntryPOLine])) {
				$NewPOLine = $_POST[$QuickEntryPOLine];
			} else {
				$NewPOLine = 0;
			}
	
			if (!isset($NewItem)){
				unset($NewItem);
				break;	/* break out of the loop if nothing in the quick entry fields*/
			}

			if(!Is_Date($NewItemDue)) {
				prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $NewItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
				//Attempt to default the due date to something sensible?
				$NewItemDue = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
			}
			/*Now figure out if the item is a kit set - the field MBFlag='K'*/
			$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";
 		
			$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
			$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
			$KitResult = DB_query($sql,$ErrMsg,$DbgMsg);

			
           
			if (DB_num_rows($KitResult)==0){//
				prnMsg( _('The item code') . ' ' . $NewItem . ' ' . _('could not be retrieved from the database and has not been added to the order'),'warn');
			} elseif ($myrow=DB_fetch_array($KitResult)){
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
							bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
                            AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                            AND bom.effectiveto > '" . date('Y-m-d') . "'";

					$ErrMsg =  _('Could not retrieve kitset components from the database because') . ' ';
					$KitResult = DB_query($sql,$ErrMsg,$DbgMsg);

					$ParentQty = $NewItemQty;
					while ($KitParts = DB_fetch_array($KitResult)){
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						$NewPOLine = 0;
						include('includes/SelectOrderItemsIntoCartCN.inc');
					}

				} elseif ($myrow['mbflag']=='G'){
					prnMsg(_('Phantom assemblies cannot be sold, these items exist only as bills of materials used in other manufactured items. The following item has not been added to the order:') . ' ' . $NewItem, 'warn');
				} else { /*Its not a kit set item*/
					include('includes/SelectOrderItemsIntoCartCN.inc');
				}
			}
		 }
		 unset($NewItem);
	 } /* end of if quick entry */

	//固定资产-1115
	if (isset($_POST['AssetDisposalEntered'])){ //its an asset being disposed of
		if ($_POST['AssetToDisposeOf'] == 'NoAssetSelected'){ //don't do anything unless an asset is disposed of
			prnMsg(_('No asset was selected to dispose of. No assets have been added to this customer order'),'warn');
		} else { //need to add the asset to the order
			/*First need to create a stock ID to hold the asset and record the sale - as only stock items can be sold
			 * 		and before that we need to add a disposal stock category - if not already created
			 * 		first off get the details about the asset being disposed of */
			 $AssetDetailsResult = DB_query("SELECT  fixedassets.description,
													fixedassets.longdescription,
													fixedassets.barcode,
													fixedassetcategories.costact,
													fixedassets.cost-fixedassets.accumdepn AS nbv
											FROM fixedassetcategories INNER JOIN fixedassets
											ON fixedassetcategories.categoryid=fixedassets.assetcategoryid
											WHERE fixedassets.assetid='" . $_POST['AssetToDisposeOf'] . "'");
			$AssetRow = DB_fetch_array($AssetDetailsResult);

			/* Check that the stock category for disposal "ASSETS" is defined already */
			$AssetCategoryResult = DB_query("SELECT categoryid FROM stockcategory WHERE categoryid='ASSETS'");
			if (DB_num_rows($AssetCategoryResult)==0){
				/*Although asset GL posting will come from the asset category - we should set the GL codes to something sensible
				 * based on the category of the asset under review at the moment - this may well change for any other assets sold subsequentely */

				/*OK now we can insert the stock category for this asset */
				$InsertAssetStockCatResult = DB_query("INSERT INTO stockcategory ( categoryid,
																				categorydescription,
																				stockact)
														VALUES ('ASSETS',
																'" . _('Asset Disposals') . "',
																'" . $AssetRow['costact'] . "')");
			}

			/*First check to see that it doesn't exist already assets are of the format "ASSET-" . $AssetID
			 */
			 $TestAssetExistsAlreadyResult = DB_query("SELECT stockid
														FROM stockmaster
														WHERE stockid ='ASSET-" . $_POST['AssetToDisposeOf']  . "'");
			 $j=0;
			while (DB_num_rows($TestAssetExistsAlreadyResult)==1) { //then it exists already ... bum
				$j++;
				$TestAssetExistsAlreadyResult = DB_query("SELECT stockid
														FROM stockmaster
														WHERE stockid ='ASSET-" . $_POST['AssetToDisposeOf']  . '-' . $j . "'");
			}
			if ($j>0){
				$AssetStockID = 'ASSET-' . $_POST['AssetToDisposeOf']  . '-' . $j;
			} else {
				$AssetStockID = 'ASSET-' . $_POST['AssetToDisposeOf'];
			}
			if ($AssetRow['nbv']==0){
				$NBV = 0.001; /* stock must have a cost to be invoiced if the flag is set so set to 0.001 */
			} else {
				$NBV = $AssetRow['nbv'];
			}
			/*OK now we can insert the item for this asset */
			$InsertAssetAsStockItemResult = DB_query("INSERT INTO stockmaster ( stockid,
																				description,
																				longdescription,
																				categoryid,
																				mbflag,
																				controlled,
																				serialised,
																				taxcatid,
																				materialcost)
										VALUES ('" . $AssetStockID . "',
												'" . DB_escape_string($AssetRow['description']) . "',
												'" . DB_escape_string($AssetRow['longdescription']) . "',
												'ASSETS',
												'D',
												'0',
												'0',
												'" . $_SESSION['DefaultTaxCategory'] . "',
												'". $NBV . "')");
			/*not forgetting the location records too */
			$InsertStkLocRecsResult = DB_query("INSERT INTO locstock (loccode,
																	stockid)
												SELECT loccode, '" . $AssetStockID . "'
												FROM locations");
			/*Now the asset has been added to the stock master we can add it to the sales order */
			$NewItemDue = date($_SESSION['DefaultDateFormat']);
			if (isset($_POST['POLine'])){
				$NewPOLine = $_POST['POLine'];
			} else {
				$NewPOLine = 0;
			}
			$NewItem = $AssetStockID;
			include('includes/SelectOrderItemsIntoCart.inc');
		} //end if adding a fixed asset to the order
	} //end if the fixed asset selection box was set

	 /*Now do non-quick entry delete/edits/adds */
     //1453=更新类');
	if ((isset($_SESSION['Items'.$identifier])) OR isset($NewItem)){
       
		if(isset($_GET['Delete'])){
			//page called attempting to delete a line - GET['Delete'] = the line number to delete
			$QuantityAlreadyDelivered = $_SESSION['Items'.$identifier]->Some_Already_Delivered($_GET['Delete']);
			if($QuantityAlreadyDelivered == 0){
				$_SESSION['Items'.$identifier]->remove_from_cart($_GET['Delete'], 'Yes', $identifier);  /*Do update DB */
			} else {
				$_SESSION['Items'.$identifier]->LineItems[$_GET['Delete']]->Quantity = $QuantityAlreadyDelivered;
			}
		}

		$AlreadyWarnedAboutCredit = false;
		  //遍历缓存
		
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

			if (isset($_POST['Quantity_' . $OrderLine->LineNumber])){

				$Quantity = round(filter_number_format($_POST['Quantity_' . $OrderLine->LineNumber]),$OrderLine->DecimalPlaces);

				if (ABS($OrderLine->Price - filter_number_format($_POST['Price_' . $OrderLine->LineNumber]))>0.01){
					/*There is a new price being input for the line item */

					$Price = filter_number_format($_POST['Price_' . $OrderLine->LineNumber]);
					$_POST['GPPercent_' . $OrderLine->LineNumber] = (($Price*(1-(filter_number_format($_POST['Discount_' . $OrderLine->LineNumber])/100))) - $OrderLine->StandardCost*$ExRate)/($Price *(1-filter_number_format($_POST['Discount_' . $OrderLine->LineNumber])/100)/100);
								/*} elseif (ABS($OrderLine->GPPercent - filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]))>=0.01) {
					// A GP % has been input so need to do a recalculation of the price at this new GP Percentage 
					$Price = ($OrderLine->StandardCost*$ExRate)/(1 -((filter_number_format($_POST['GPPercent_' . $OrderLine->LineNumber]) + filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]))/100));
				*/}else{
					$Price = filter_number_format($_POST['Price_' . $OrderLine->LineNumber]);
				}
				$DiscountPercentage = filter_number_format($_POST['Discount_' . $OrderLine->LineNumber]);
				if ($_SESSION['AllowOrderLineItemNarrative'] == 1) {
					$Narrative = $_POST['Narrative_' . $OrderLine->LineNumber];
				} else {
					$Narrative = '';
				}

				if (!isset($OrderLine->DiscountPercent)) {
					$OrderLine->DiscountPercent = 0;
				}

				if(!Is_Date($_POST['ItemDue_' . $OrderLine->LineNumber])) {
					prnMsg(_('An invalid date entry was made for ') . ' ' . $NewItem . ' ' . _('The date entry') . ' ' . $ItemDue . ' ' . _('must be in the format') . ' ' . $_SESSION['DefaultDateFormat'],'warn');
					//Attempt to default the due date to something sensible?
					$_POST['ItemDue_' . $OrderLine->LineNumber] = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
				}
				if ($Quantity<0 OR $Price <0 OR $DiscountPercentage >100 OR $DiscountPercentage <0){
					prnMsg(_('The item could not be updated because you are attempting to set the quantity ordered to less than 0 or the price less than 0 or the discount more than 100% or less than 0%'),'warn');
				} elseif($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->Price != $Price) {
					prnMsg(_('The item you attempting to modify the price for has already had some quantity invoiced at the old price the items unit price cannot be modified retrospectively'),'warn');
				} elseif($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)!=0 AND $_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->DiscountPercent != ($DiscountPercentage/100)) {

					prnMsg(_('The item you attempting to modify has had some quantity invoiced at the old discount percent the items discount cannot be modified retrospectively'),'warn');

				} elseif ($_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->QtyInv > $Quantity){
					prnMsg( _('You are attempting to make the quantity ordered a quantity less than has already been invoiced') . '. ' . _('The quantity delivered and invoiced cannot be modified retrospectively'),'warn');
				} elseif ($OrderLine->Quantity !=$Quantity
							OR $OrderLine->Price != $Price
							OR ABS($OrderLine->DiscountPercent - $DiscountPercentage/100) >0.001
							OR $OrderLine->Narrative != $Narrative
							OR $OrderLine->ItemDue != $_POST['ItemDue_' . $OrderLine->LineNumber]
							OR $OrderLine->POLine != $_POST['POLine_' . $OrderLine->LineNumber]) {

					$WithinCreditLimit = true;

					if ($_SESSION['CheckCreditLimits'] > 0 AND $AlreadyWarnedAboutCredit==false){
						/*Check credit limits is 1 for warn breach their credit limit and 2 for prohibit sales */
						$DifferenceInOrderValue = ($Quantity*$Price*(1-$DiscountPercentage/100)) - ($OrderLine->Quantity*$OrderLine->Price*(1-$OrderLine->DiscountPercent));
						$_SESSION['Items'.$identifier]->CreditAvailable -= $DifferenceInOrderValue;

						if ($_SESSION['CheckCreditLimits']==1 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
							prnMsg(_('The customer account will breach their credit limit'),'warn');//客户帐户将违反他们的信用额度。
							$AlreadyWarnedAboutCredit = true;
						} elseif ($_SESSION['CheckCreditLimits']==2 AND $_SESSION['Items'.$identifier]->CreditAvailable <=0){
							prnMsg(_('This change would put the customer over their credit limit and is prohibited'),'warn');//这一改变将使客户超出他们的信额度，并且被禁止。
							$WithinCreditLimit = false;
							$_SESSION['Items'.$identifier]->CreditAvailable += $DifferenceInOrderValue;
							$AlreadyWarnedAboutCredit = true;
						}
					}
					/* The database data will be updated at this step, it will make big mistake if users do not know this and change the quantity to zero, unfortuately, the appearance shows that this change not allowed but the sales order details' quantity has been changed to zero in database. Must to filter this out! A zero quantity order line means nothing */
					if ($WithinCreditLimit AND $Quantity >0){
						
						
						$CurrAmount=0;
						
						if ((float)$_POST['CurrAmo' . $OrderLine->LineNumber]!=0){
							$CurrAmount=(float)$_POST['CurrAmo' . $OrderLine->LineNumber];
						}
					
					
						$_SESSION['Items'.$identifier]->update_cart_item($OrderLine->LineNumber,
																		$Quantity,
																		$Price,
																		($_POST['CurrPrice' . $OrderLine->LineNumber]),
																		($_POST['TaxPrice' . $OrderLine->LineNumber]),																	
																		$_SESSION['Items'.$identifier]->TaxRate,
																		($DiscountPercentage/100),
																		$Narrative,
																		'Yes', /*Update DB */
																		$_POST['ItemDue_' . $OrderLine->LineNumber],
																		$_POST['POLine_' . $OrderLine->LineNumber],
																		($_POST['GPPercent_' . $OrderLine->LineNumber]),
																		$identifier,
																	
																		$_POST['Amount' . $OrderLine->LineNumber],
																		$CurrAmount);

                    
					} //within credit limit so make changes
				} //there are changes to the order line to process
			} //page not called from itself - POST variables not set
		} // Loop around all items on the order

       
		/* Now Run through each line of the order again to work out the appropriate discount from the discount matrix */
		$DiscCatsDone = array();
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

			if ($OrderLine->DiscCat !='' AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
				$DiscCatsDone[]=$OrderLine->DiscCat;
				$QuantityOfDiscCat = 0;

				foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine_2) {
					/* add up total quantity of all lines of this DiscCat */
					if ($OrderLine_2->DiscCat==$OrderLine->DiscCat){
						$QuantityOfDiscCat += $OrderLine_2->Quantity;
					}
				}
				$result = DB_query("SELECT MAX(discountrate) AS discount
									FROM discountmatrix
									WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
									AND discountcategory ='" . $OrderLine->DiscCat . "'
									AND quantitybreak <= '" . $QuantityOfDiscCat ."'");
				$myrow = DB_fetch_row($result);
				if ($myrow[0]==NULL){
					$DiscountMatrixRate = 0;
				} else {
					$DiscountMatrixRate = $myrow[0];
				}
				if ($DiscountMatrixRate!=0){ /* need to update the lines affected */
					foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine_2) {
						if ($OrderLine_2->DiscCat==$OrderLine->DiscCat){
							$_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
							$_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->GPPercent = (($_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->Price*(1-$DiscountMatrixRate)) - $_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->StandardCost*$ExRate)/($_SESSION['Items'.$identifier]->LineItems[$OrderLine_2->LineNumber]->Price *(1-$DiscountMatrixRate)/100);
						}
					}
				}
			}
		} /* end of discount matrix lookup code */
	} // the order session is started or there is a new item being added
	
		/*echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/DeliveryDetails.php?identifier='.$identifier . '">';
		prnMsg(_('You should automatically be forwarded to the entry of the delivery details page') . '. ' . _('if this does not happen') . ' (' . _('if the browser does not support META Refresh') . ') ' .
		  '<a href="' . $RootPath . '/DeliveryDetails.php?identifier='.$identifier . '">' . _('click here') . '</a> ' . _('to continue'), 'info');
	  	exit;*/
	
	

if(isset($_POST['DeliveryDetails'])){//ProcessOrder'])) {
	/*Default OK_to_PROCESS to 1 change to 0 later if hit a snag */
	if (empty($_POST['CurrRate'])){
		$_POST['CurrRate']=1;
	}
	$_SESSION['Items'.$identifier]->ExRate=$_POST['CurrRate'];
	
	//$_SESSION['Items'.$identifier]->CurrCode=$_POST['CurrCode'];
	
	/*echo '<br>';
	var_dump($_SESSION['Items'.$identifier]->LineItems);
	
	
	include('includes/footer.php');
	exit;*/


	if($InputErrors ==0) {
		$OK_to_PROCESS = 1;
	}
	if($_POST['FreightCost'] != $OldFreightCost AND $_SESSION['DoFreightCalc']==True) {
		$OK_to_PROCESS = 0;
		prnMsg(_('The freight charge has been updated') . '. ' . _('Please reconfirm that the order and the freight charges are acceptable and then confirm the order again if OK') .' <br /> '. _('The new freight cost is') .' ' . $_POST['FreightCost'] . ' ' . _('and the previously calculated freight cost was') .' '. $OldFreightCost,'warn');
	} else {

	/*check the customer's payment terms */
		$sql = "SELECT daysbeforedue,
				dayinfollowingmonth
			FROM debtorsmaster,
				paymentterms
			WHERE debtorsmaster.paymentterms=paymentterms.termsindicator
			AND debtorsmaster.debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "'";

		$ErrMsg = _('The customer terms cannot be determined') . '. ' . _('This order cannot be processed because');
		$DbgMsg = _('SQL used to find the customer terms') . ':';
		$TermsResult = DB_query($sql,$ErrMsg,$DbgMsg);


		$myrow = DB_fetch_array($TermsResult);
		if($myrow['daysbeforedue']==0 AND $myrow['dayinfollowingmonth']==0) {

	/* THIS IS A CASH SALE NEED TO GO OFF TO 3RD PARTY SITE SENDING MERCHANT ACCOUNT DETAILS AND CHECK FOR APPROVAL FROM 3RD PARTY SITE BEFORE CONTINUING TO PROCESS THE ORDER

	UNTIL ONLINE CREDIT CARD PROCESSING IS PERFORMED ASSUME OK TO PROCESS

		NOT YET CODED   */

			$OK_to_PROCESS =1;


		} #end if cash sale detected

	} #end if else freight charge not altered
} #end if process order
//保存新订单
if(!isset($_POST['ShipVia'])){
	$_POST['ShipVia']=0;
}
if(isset($OK_to_PROCESS) AND $OK_to_PROCESS == 1 AND $_SESSION['ExistingOrder'.$identifier]==0) {
	//var_dump($_SESSION['Items'.$identifier]->LineItems); 
	/* finally write the order header to the database and then the order line details */
	//var_dump($_SESSION['Items'.$identifier]->LineItems );
	$DelDate = FormatDateforSQL($_SESSION['Items'.$identifier]->DeliveryDate);
	$QuotDate = FormatDateforSQL($_SESSION['Items'.$identifier]->QuoteDate);
	$ConfDate = FormatDateforSQL($_SESSION['Items'.$identifier]->ConfirmedDate);
	$OrderNo = GetNextTransNo(30);//, $db);
	$Result = DB_Txn_Begin();
	if (!is_int($_SESSION['Items'.$identifier]->DeliverBlind) ){
		$DeliverBlind=0;
	} 
	//$_SESSION['Items'.$identifier]->Location=11;
	
   
	$HeaderSQL = "INSERT INTO salesorders (	orderno,
											debtorno,							
											branchcode,
											tag,
											currcode,
											rate,
											taxcatid,
											taxrate,
											customerref,
											comments,
											orddate,
											ordertype,
											shipvia,
											deliverto,
											deladd1,
											deladd2,
											deladd3,
											deladd4,
											deladd5,
											deladd6,
											contactphone,
											contactemail,
											salesperson,
											freightcost,
											fromstkloc,
											deliverydate,
											quotedate,
											confirmeddate,
											quotation,
											deliverblind)
										VALUES (
										
											'". $OrderNo . "',
											'" . $_SESSION['Items'.$identifier]->DebtorNo . "',
											'" . $_SESSION['Items'.$identifier]->Branch . "',
											'" . $_SESSION['Items'.$identifier]->tag . "',
											'". DB_escape_string($_SESSION['Items'.$identifier]->CurrCode) ."',
											'". $_SESSION['Items'.$identifier]->ExRate ."',
											'". $_SESSION['Items'.$identifier]->TaxCatID ."',
											'". $_SESSION['Items'.$identifier]->TaxRate ."',
											'". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
											'". DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
											'" . Date("Y-m-d H:i") . "',
											'" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
											'" . $_SESSION['Items'.$identifier]->ShipVia ."',
											'".  DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
											'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd1) . "',
											'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd2) . "',
											'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd3) . "',
											'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd4) . "',
											'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd5) . "',
											'" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd6) . "',
											'" . $_SESSION['Items'.$identifier]->PhoneNo . "',
											'" . $_SESSION['Items'.$identifier]->Email . "',
											'" . $_SESSION['Items'.$identifier]->SalesPerson . "',
											'" . $_SESSION['Items'.$identifier]->FreightCost ."',
											'" . $_SESSION['Items'.$identifier]->Location ."',
											'" . $DelDate . "',
											'" . $QuotDate . "',
											'" . $ConfDate . "',
											'" . $_SESSION['Items'.$identifier]->Quotation . "',
											'" . $_SESSION['Items'.$identifier]->DeliverBlind ."'
											)";

	$ErrMsg = _('The order cannot be added because');
	$InsertQryResult = DB_query($HeaderSQL,$ErrMsg);
    
	$StartOf_LineItemsSQL = "INSERT INTO salesorderdetails (orderlineno,
															orderno,
															stkcode,
															unitprice,
															quantity,
															currprice,
															discountpercent,
															narrative,
															poline,
															itemdue,
															taxprice,
															cess,
															amount,
															curramount)
														VALUES (";
	$DbgMsg = _('The SQL that failed was');
	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {
	
		$CurrAmount=0;
		$CurrPrice=0;
		if (round($StockItem->CurrAmount,POI)!=0){
			$CurrAmount=(float)$StockItem->CurrAmount;
			$CurrPrice=(float)$StockItem->CurrPrice;
		}
		
		$LineItemsSQL = $StartOf_LineItemsSQL ."
					'" . $StockItem->LineNumber . "',
					'" . $OrderNo . "',
					'" . $StockItem->StockID . "',
					'" . $StockItem->Price . "',
					'" . $StockItem->Quantity . "',				
					'" . $CurrPrice . "',
					'" . floatval($StockItem->DiscountPercent) . "',
					'" . DB_escape_string($StockItem->Narrative) . "',
					'" . $StockItem->POLine . "',
					'" . FormatDateForSQL($StockItem->ItemDue) . "',
					'" . $StockItem->TaxPrice . "',
					'" . $StockItem->Cess . "',
					'" . $StockItem->Amount . "',
					'" . $CurrAmount . "'	)";
		$ErrMsg = _('Unable to add the sales order line');
		
		$Ins_LineItemResult = DB_query($LineItemsSQL,$ErrMsg,$DbgMsg,true);
 
		/*Now check to see if the item is manufactured
		 * 			and AutoCreateWOs is on
		 * 			and it is a real order (not just a quotation)*/

		if($StockItem->MBflag=='M'
			AND $_SESSION['AutoCreateWOs']==1
			AND $_SESSION['Items'.$identifier]->Quotation!=1) {//oh yeah its all on!

			echo '<br />';

			//now get the data required to test to see if we need to make a new WO
			$QOHResult = DB_query("SELECT SUM(quantity) FROM locstock WHERE stockid='" . $StockItem->StockID . "'");
			$QOHRow = DB_fetch_row($QOHResult);
			$QOH = $QOHRow[0];//物料库存数合计

			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
					FROM salesorderdetails INNER JOIN salesorders
					ON salesorderdetails.orderno=salesorders.orderno
					WHERE salesorderdetails.stkcode = '" . $StockItem->StockID . "'
					AND salesorderdetails.completed = 0
					AND salesorders.quotation=0";
			$DemandResult = DB_query($SQL);
			$DemandRow = DB_fetch_row($DemandResult);
			$QuantityDemand = $DemandRow[0];//合同未执行数

			$SQL = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
					FROM salesorderdetails INNER JOIN salesorders
					ON salesorderdetails.orderno=salesorders.orderno
					INNER JOIN bom ON salesorderdetails.stkcode=bom.parent
					INNER JOIN stockmaster ON stockmaster.stockid=bom.parent
					WHERE salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
					AND bom.component='" . $StockItem->StockID . "'
					AND salesorders.quotation=0
					AND stockmaster.mbflag='A'
					AND salesorderdetails.completed=0";
			$AssemblyDemandResult = DB_query($SQL);
			$AssemblyDemandRow = DB_fetch_row($AssemblyDemandResult);
			$QuantityAssemblyDemand = $AssemblyDemandRow[0];//bom领料计算

			$SQL = "SELECT SUM(purchorderdetails.quantityord - purchorderdetails.quantityrecd) as qtyonorder
					FROM purchorderdetails,
						purchorders
					WHERE purchorderdetails.orderno = purchorders.orderno
					AND purchorderdetails.itemcode = '" . $StockItem->StockID . "'
					AND purchorderdetails.completed = 0";
			$PurchOrdersResult = DB_query($SQL);
			$PurchOrdersRow = DB_fetch_row($PurchOrdersResult);
			$QuantityPurchOrders = $PurchOrdersRow[0];//采购合同未收货

			$SQL = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) as qtyonorder
					FROM woitems LEFT JOIN workorders
					ON woitems.wo=workorders.wo
					WHERE woitems.stockid = '" . $StockItem->StockID . "'
					AND woitems.qtyreqd > woitems.qtyrecd
					AND workorders.closed = 0";
			$WorkOrdersResult = DB_query($SQL);
			$WorkOrdersRow = DB_fetch_row($WorkOrdersResult);
			$QuantityWorkOrders = $WorkOrdersRow[0];//工作单未完成的

			//Now we have the data - do we need to make any more?
			//该产品库存、工作单未完工、采购未收货、销售未发出、BOM未领料
			$ShortfallQuantity = $QOH-$QuantityDemand-$QuantityAssemblyDemand+$QuantityPurchOrders+$QuantityWorkOrders;
				//工作单
			if($ShortfallQuantity < 0) {//then we need to make a work order
				//How many should the work order be for??
				if($ShortfallQuantity + $StockItem->EOQ < 0) {
					$WOQuantity = -$ShortfallQuantity;
				} else {
					$WOQuantity = $StockItem->EOQ;
				}

				$WO = GetNextTransNo(40,$db);//凭证号
				
				$ErrMsg = _('Unable to insert a new work order for the sales order item');
				$InsWOResult = DB_query("INSERT INTO workorders (  wo,
																	loccode,
																	requiredby,
																	startdate,
																	initiator)
								 VALUES ( '" . $WO . "',
								 	'" .  $_SESSION['Items'.$identifier]->Location . "',
										'" . Date('Y-m-d') . "',
										'" . Date('Y-m-d'). "',
										'".$_SESSION['UserID']."')",
										$ErrMsg,
										$DbgMsg,
										true);
		
				//$WO=DB_Last_Insert_ID($db,'workorders','wo');//	DB_fetch_array($InsWOResult);
				//Need to get the latest BOM to roll up cost
				$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
													FROM stockmaster INNER JOIN bom
													ON stockmaster.stockid=bom.component
													WHERE bom.parent='" . $StockItem->StockID . "'
													AND bom.loccode='" . $_SESSION['DefaultFactoryLocation'] . "'");
				$CostRow = DB_fetch_row($CostResult);
				//BOM成本为0 warn
				if(is_null($CostRow[0]) OR $CostRow[0]==0) {
					$Cost =0;
					prnMsg(_('In automatically creating a work order for') . '  ' . $StockItem->StockID . ' ' . _('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
				} else {
					$Cost = $CostRow[0];
				}

				// insert parent item info
				$sql = "INSERT INTO woitems (wo,
											 stockid,
											 qtyreqd,
											 stdcost,
											 loccode,
											 diff)
								 VALUES ( '" . $WO . "',
										 '" . $StockItem->StockID . "',
										 '" . $WOQuantity . "',
										 '" . $Cost . "',
										 '" .  $_SESSION['Items'.$identifier]->Location . "',
										 0)";
			    // echo  '-='.$sql;
				$ErrMsg = _('The work order item could not be added');
				$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
				//$WO = DB_Last_Insert_ID($db,'woitems','wo');
				
				//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
				// WoRealRequirements($db, $WO, $LocCode, $StockID, $Qty=1, $ParentID='')
				//BOM发货INSERT
				//echo  '-='. $WO.'['.$_SESSION['Items'.$identifier]->Location.']'. $StockItem->StockID;
				WoRealRequirements($WO,$_SESSION['Items'.$identifier]->Location, $StockItem->StockID);
					// ItemCostUpdateGL($db, $StockID, $NewCost, $OldCost, $QOH) 
			
				$FactoryManagerEmail = _('A new work order has been created for') .
									":\n" . $StockItem->StockID . ' - ' . $StockItem->ItemDescription . ' x ' . $WOQuantity . ' ' . $StockItem->Units .
									"\n" . _('These are for') . ' ' . $_SESSION['Items'.$identifier]->CustomerName . ' ' . _('there order ref') . ': ' . $_SESSION['Items'.$identifier]->CustRef . ' ' ._('our order number') . ': ' . $OrderNo;
	
				if($StockItem->Serialised AND $StockItem->NextSerialNo>0) {
						//then we must create the serial numbers for the new WO also
						$FactoryManagerEmail .= "\n" . _('The following serial numbers have been reserved for this work order') . ':';

						for ($i=0;$i<$WOQuantity;$i++) {

							$result = DB_query("SELECT serialno FROM stockserialitems
												WHERE serialno='" . ($StockItem->NextSerialNo + $i) . "'
												AND stockid='" . $StockItem->StockID ."'");
							if(DB_num_rows($result)!=0) {
								$WOQuantity++;
								prnMsg(($StockItem->NextSerialNo + $i) . ': ' . _('This automatically generated serial number already exists - it cannot be added to the work order'),'error');
							} else {
								$sql = "INSERT INTO woserialnos (wo,
																stockid,
																serialno)
													VALUES ('" . $WO . "',
															'" . $StockItem->StockID . "',
															'" . ($StockItem->NextSerialNo + $i) . "')";
								$ErrMsg = _('The serial number for the work order item could not be added');
								$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
								$FactoryManagerEmail .= "\n" . ($StockItem->NextSerialNo + $i);
							}
						}//end loop around creation of woserialnos
						$NewNextSerialNo = ($StockItem->NextSerialNo + $WOQuantity +1);
						$ErrMsg = _('Could not update the new next serial number for the item');
						$UpdateNextSerialNoResult = DB_query("UPDATE stockmaster SET nextserialno='" . $NewNextSerialNo . "' WHERE stockid='" . $StockItem->StockID . "'",$ErrMsg,$DbgMsg,true);
				}// end if the item is serialised and nextserialno is set
				
				$EmailSubject = _('New Work Order Number') . ' ' . $WO . ' ' . _('for') . ' ' . $StockItem->StockID . ' x ' . $WOQuantity;
				//Send email to the Factory Manager
				if($_SESSION['SmtpSetting']==0) {//不使用mail
				
					mail($_SESSION['FactoryManagerEmail'],$EmailSubject,$FactoryManagerEmail);

				} else {
					//下面代码有问题
					include('includes/htmlMimeMail.php');
					$mail = new htmlMimeMail();
					$mail->setSubject($EmailSubject);
					$result = SendmailBySmtp($mail,array($_SESSION['FactoryManagerEmail']));
				}
			

			}//end if with this sales order there is a shortfall of stock - need to create the WO
		}//end if auto create WOs in on
	} /* end inserted line items into sales order details */

	$result = DB_Txn_Commit();
	echo '<br />';
	if($_SESSION['Items'.$identifier]->Quotation==1) {
		prnMsg(_('Quotation Number') . ' ' . $OrderNo . ' ' . _('has been entered'),'success');
	} else {
		prnMsg(_('Order Number') . ' ' . $OrderNo . ' ' . _('has been entered'),'success');
		unset($_SESSION['Items'.$identifier]->LineItems);
		unset($_SESSION['Items'.$identifier]);
	}

	if(count($_SESSION['AllowedPageSecurityTokens'])>1) {
		/* Only allow print of packing slip for internal staff - customer logon's cannot go here */

		if($_POST['Quotation']==0) { /*那它不是一个引语而是一个真正的命令then its not a quotation its a real order */

			echo '<br /><table class="selection">
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
						<td>' . ' ' . '<a target="_blank" href="' . $RootPath . '/PrintCustOrder.php?identifier='.$identifier . '&amp;TransNo=' . $OrderNo . '">' . _('Print packing slip') . ' (' . _('Preprinted stationery') . ')' . '</a></td>
					</tr>';
					/*
			echo '<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
					<td>' . ' ' . '<a target="_blank" href="' . $RootPath . '/PrintCustOrder_generic.php?identifier='.$identifier . '&amp;TransNo=' . $OrderNo . '">' . _('Print packing slip') . ' (' . _('Laser') . ')' . '</a></td>
				</tr>';

			echo '<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Invoice') . '" alt="" /></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/ConfirmDispatch_Invoice.php?identifier='.$identifier . '&amp;OrderNumber=' . $OrderNo .'">' . _('Confirm Dispatch and Produce Invoice') . '</a></td>
				</tr>';
			*/
			echo '</table>';

		} else {
			/*link to print the quotation */
			echo '<br /><table class="selection">
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Order') . '" alt=""></td>
						<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotation.php?identifier='.$identifier . '&amp;QuotationNo=' . $OrderNo . '" target="_blank">' . _('Print Quotation (Landscape)') . '</a></td>
					</tr>
					</table>';
			echo '<br /><table class="selection">
					<tr>
						<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Order') . '" alt="" /></td>
						<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotationPortrait.php?identifier='.$identifier . '&amp;QuotationNo=' . $OrderNo . '" target="_blank">' .  _('Print Quotation (Portrait)')  . '</a></td>
					</tr>
					</table>';
		}
		echo '<br /><table class="selection">
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . _('Order') . '" alt="" /></td>
					<td>' . ' ' . '<a href="'. $RootPath .'/SO_Header.php?NewOrder=Yes">' .  _('Add Another Sales Order')  . '</a></td>
				</tr>
				</table>';
	} else {
		/*its a customer logon so thank them */
		prnMsg(_('Thank you for your business'),'success');
	}

	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);
	include('includes/footer.php');
	exit;

}elseif(isset($OK_to_PROCESS) AND ($OK_to_PROCESS == 1 AND $_SESSION['ExistingOrder'.$identifier]!=0)) {
   // prnMsg('1986修改订单');
	/* update the order header then update the old order line details and insert the new lines */

	$DelDate = FormatDateforSQL($_SESSION['Items'.$identifier]->DeliveryDate);
	$QuotDate = FormatDateforSQL($_SESSION['Items'.$identifier]->QuoteDate);
	$ConfDate = FormatDateforSQL($_SESSION['Items'.$identifier]->ConfirmedDate);

	$Result = DB_Txn_Begin();

	/*see if this is a contract quotation being changed to an order? */
	if($_SESSION['Items'.$identifier]->Quotation==0) {//now its being changed? to an order
		$ContractResult = DB_query("SELECT contractref,
											requireddate
									FROM contracts WHERE orderno='" .  $_SESSION['ExistingOrder'.$identifier] ."'
									AND status=1");
		if(DB_num_rows($ContractResult)==1) {//then it is a contract quotation being changed to an order
			$ContractRow = DB_fetch_array($ContractResult);
			$WO = GetNextTransNo(40,$db);
			$ErrMsg = _('Could not update the contract status');
			$DbgMsg = _('The SQL that failed to update the contract status was');
			$UpdContractResult=DB_query("UPDATE contracts SET status=2,
															wo='" . $WO . "'
										WHERE orderno='" .$_SESSION['ExistingOrder'.$identifier] . "'",
										$ErrMsg,
										$DbgMsg,
										true);
			$ErrMsg = _('Could not insert the contract bill of materials');
			$InsContractBOM = DB_query("INSERT INTO bom (parent,
														 component,
														 workcentreadded,
														 loccode,
														 effectiveafter,
														 effectiveto,
													 	 quantity)
											SELECT contractref,
													stockid,
													workcentreadded,
													'" . $_SESSION['Items'.$identifier]->Location ."',
													'" . Date('Y-m-d') . "',
													'2099-12-31',
													quantity
											FROM contractbom
											WHERE contractref='" . $ContractRow['contractref'] . "'",
											$ErrMsg,
											$DbgMsg);

			$ErrMsg = _('Unable to insert a new work order for the sales order item');
			$InsWOResult = DB_query("INSERT INTO workorders (wo,
															 loccode,
															 requiredby,
															 startdate)
											 VALUES ('" . $WO . "',
													'" . $_SESSION['Items'.$identifier]->Location ."',
													'" . $ContractRow['requireddate'] . "',
													'" . Date('Y-m-d'). "')",
										$ErrMsg,
										$DbgMsg);
			//Need to get the latest BOM to roll up cost but also add the contract other requirements
			$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*contractbom.quantity) AS cost
									FROM stockmaster INNER JOIN contractbom
									ON stockmaster.stockid=contractbom.stockid
									WHERE contractbom.contractref='" .  $ContractRow['contractref'] . "'");
			$CostRow = DB_fetch_row($CostResult);
			if(is_null($CostRow[0]) OR $CostRow[0]==0) {
				$Cost =0;
				prnMsg(_('In automatically creating a work order for') . ' ' . $ContractRow['contractref'] . ' ' . _('an item on this sales order, the cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
			} else {
				$Cost = $CostRow[0];//cost of contract BOM
			}
			$CostResult = DB_query("SELECT SUM(costperunit*quantity) AS cost
									FROM contractreqts
									WHERE contractreqts.contractref='" .  $ContractRow['contractref'] . "'");
			$CostRow = DB_fetch_row($CostResult);
			//add other requirements cost to cost of contract BOM
			$Cost += $CostRow[0];

			// insert parent item info
			$sql = "INSERT INTO woitems (wo,
										 stockid,
										 qtyreqd,
										 stdcost)
							 VALUES ( '" . $WO . "',
									 '" . $ContractRow['contractref'] . "',
									 '1',
									 '" . $Cost . "')";
			$ErrMsg = _('The work order item could not be added');
			$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
			//接受工单信息并迭代物料清单，插入实际组件（分解幻影组件）
			//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
			WoRealRequirements($WO, $_SESSION['Items'.$identifier]->Location, $ContractRow['contractref']);

		}//end processing if the order was a contract quotation being changed to an order
	}//end test to see if the order was a contract quotation being changed to an order
     if (!is_int($_SESSION['Items'.$identifier]->DeliverBlind) ){
		 $DeliverBlind=0;
	 } 

	$HeaderSQL = "UPDATE salesorders SET debtorno = '" . $_SESSION['Items'.$identifier]->DebtorNo . "',
										branchcode = '" . $_SESSION['Items'.$identifier]->Branch . "',
										customerref = '". DB_escape_string($_SESSION['Items'.$identifier]->CustRef) ."',
										comments = '". DB_escape_string($_SESSION['Items'.$identifier]->Comments) ."',
										ordertype = '" . $_SESSION['Items'.$identifier]->DefaultSalesType . "',
										shipvia = '" . $_POST['ShipVia'] . "',
										deliverydate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->DeliveryDate)) . "',
										quotedate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->QuoteDate)) . "',
										confirmeddate = '" . FormatDateForSQL(DB_escape_string($_SESSION['Items'.$identifier]->ConfirmedDate)) . "',
										deliverto = '" . DB_escape_string($_SESSION['Items'.$identifier]->DeliverTo) . "',
										deladd1 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd1) . "',
										deladd2 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd2) . "',
										deladd3 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd3) . "',
										deladd4 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd4) . "',
										deladd5 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd5) . "',
										deladd6 = '" . DB_escape_string($_SESSION['Items'.$identifier]->DelAdd6) . "',
										contactphone = '" . $_SESSION['Items'.$identifier]->PhoneNo . "',
										contactemail = '" . $_SESSION['Items'.$identifier]->Email . "',
										salesperson = '" .  $_SESSION['Items'.$identifier]->SalesPerson . "',
										freightcost = '" . $_SESSION['Items'.$identifier]->FreightCost ."',
										fromstkloc = '" . $_SESSION['Items'.$identifier]->Location ."',
										printedpackingslip = '" . $_POST['ReprintPackingSlip'] . "',
										quotation = '" . $_SESSION['Items'.$identifier]->Quotation . "',
										currcode = '" . $_SESSION['Items'.$identifier]->CurrCode . "',
										rate = '" . $_SESSION['Items'.$identifier]->ExRate . "',
										tag = '" . $_SESSION['Items'.$identifier]->Tag . "',
										deliverblind = '" . $DeliverBlind . "'
						WHERE salesorders.orderno='" . $_SESSION['ExistingOrder'.$identifier] ."'";

	$DbgMsg = _('The SQL that was used to update the order and failed was');
	$ErrMsg = _('The order cannot be updated because');
	$InsertQryResult = DB_query($HeaderSQL,$ErrMsg,$DbgMsg,true);


	foreach ($_SESSION['Items'.$identifier]->LineItems as $StockItem) {

		/* Check to see if the quantity reduced to the same quantity
		as already invoiced - so should set the line to completed */
		if($StockItem->Quantity == $StockItem->QtyInv) {
			$Completed = 1;
		} else {  /* order line is not complete */
			$Completed = 0;
		}
		$CurrAmount=0;
		
		if ($StockItem->CurrAmount!=0){
			$CurrAmount=(float)$StockItem->CurrAmount;
	
		}
		$LineItemsSQL = "UPDATE salesorderdetails SET unitprice='"  . $StockItem->Price . "',
													quantity='" . $StockItem->Quantity . "',
													cess='" .$_SESSION['Items'.$identifier]->TaxRate . "',
													taxprice='" . $StockItem->TaxPrice . "',
													currprice='" . $StockItem->CurrPrice . "',
													discountpercent='" . floatval($StockItem->DiscountPercent) . "',
													completed='" . $Completed . "',
													poline='" . $StockItem->POLine . "',
													itemdue='" . FormatDateForSQL($StockItem->ItemDue) . "',
													amount='".$StockItem->Amount."',
													curramount='".$CurrAmount."'
						WHERE salesorderdetails.orderno='" . $_SESSION['ExistingOrder'.$identifier] . "'
						AND salesorderdetails.orderlineno='" . $StockItem->LineNumber . "'";

		$DbgMsg = _('The SQL that was used to modify the order line and failed was');
		$ErrMsg = _('The updated order line cannot be modified because');
		$Upd_LineItemResult = DB_query($LineItemsSQL,$ErrMsg,$DbgMsg,true);

	} /* updated line items into sales order details */

	$Result=DB_Txn_Commit();
	$Quotation = $_SESSION['Items'.$identifier]->Quotation;
	unset($_SESSION['Items'.$identifier]->LineItems);
	unset($_SESSION['Items'.$identifier]);

	if($Quotation) {//handle Quotations and Orders print after modification
		prnMsg(_('Quotation Number') .' ' . $_SESSION['ExistingOrder'.$identifier] . ' ' . _('has been updated'),'success');

		/*link to print the quotation */
		echo '<br /><table class="selection">
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Order') . '" alt=""></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotation.php?identifier='.$identifier . '&amp;QuotationNo=' . $_SESSION['ExistingOrder'.$identifier] . '" target="_blank">' .  _('Print Quotation (Landscape)')  . '</a></td>
				</tr>
				</table>';
		echo '<br /><table class="selection">
				<tr>
					<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Order') . '" alt="" /></td>
					<td>' . ' ' . '<a href="' . $RootPath . '/PDFQuotationPortrait.php?identifier='.$identifier . '&amp;QuotationNo=' . $_SESSION['ExistingOrder'.$identifier] . '" target="_blank">' .  _('Print Quotation (Portrait)')  . '</a></td>
				</tr>
				</table>';
	} else {

	prnMsg(_('Order Number') .' ' . $_SESSION['ExistingOrder'.$identifier] . ' ' . _('has been updated'),'success');

	echo '<br />
			<table class="selection">
			<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
			<td><a target="_blank" href="' . $RootPath . '/PrintCustOrder.php?identifier='.$identifier  . '&amp;TransNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Print packing slip - pre-printed stationery')  . '</a></td>
			</tr>';
			/*
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . _('Print') . '" alt="" /></td>
			<td><a  target="_blank" href="' . $RootPath . '/PrintCustOrder_generic.php?identifier='.$identifier  . '&amp;TransNo=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Print packing slip') . ' (' . _('Laser') . ')'  . '</a></td>
		</tr>';
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/reports.png" title="' . _('Invoice') . '" alt="" /></td>
			<td><a href="' . $RootPath .'/ConfirmDispatch_Invoice.php?identifier='.$identifier  . '&amp;OrderNumber=' . $_SESSION['ExistingOrder'.$identifier] . '">' .  _('Confirm Order Delivery Quantities and Produce Invoice')  . '</a></td>
		</tr>';*/
	echo '<tr>
			<td><img src="'.$RootPath.'/css/'.$Theme.'/images/sales.png" title="' . _('Order') . '" alt="" /></td>
			<td><a href="' . $RootPath .'/SelectSalesOrder.php?identifier='.$identifier   . '">' .  _('Select A Different Order')  . '</a></td>
		</tr>
		</table>';
	}//end of print orders
	include('includes/footer.php');
	exit;
}

	//save end

	if (isset($NewItem)){
		/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
		/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$sql = "SELECT stockmaster.mbflag
		   		FROM stockmaster
				WHERE stockmaster.stockid='". $NewItem ."'";

		$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

		$KitResult = DB_query($sql,$ErrMsg);

		$NewItemQty = 1; /*By Default */
		$Discount = 0; /*By default - can change later or discount category override */

		if ($myrow=DB_fetch_array($KitResult)){
		   	if ($myrow['mbflag']=='K'){	/*It is a kit set item */
				$sql = "SELECT bom.component,
							bom.quantity
						FROM bom
						WHERE bom.parent='" . $NewItem . "'
                        AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                        AND bom.effectiveto > '" . date('Y-m-d') . "'";

				$ErrMsg = _('Could not retrieve kitset components from the database because');
				$KitResult = DB_query($sql,$ErrMsg);

				$ParentQty = $NewItemQty;
				while ($KitParts = DB_fetch_array($KitResult)){
					$NewItem = $KitParts['component'];
					$NewItemQty = $KitParts['quantity'] * $ParentQty;
					$NewPOLine = 0;
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					include('includes/SelectOrderItems_IntoCart.inc');
				}

			} else { /*Its not a kit set item*/
				$NewItemDue = date($_SESSION['DefaultDateFormat']);
				$NewPOLine = 0;

				include('includes/SelectOrderItems_IntoCart.inc');
			}

		} /* end of if its a new item */

	} /*end of if its a new item */

	//添加新的销售订单-Add to Sales Order
	if (isset($NewItemArray) AND isset($_POST['SelectingOrderItems'])){
		/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
		/*Now figure out if the item is a kit set - the field MBFlag='K'*/
		$AlreadyWarnedAboutCredit = false;
		//prnMsg(_('The item code') . '-增销售订单按钮执行' , 'warn');
		//增销售订单按钮执行		
		foreach($NewItemArray as $NewItem => $NewItemQty) {
          
			if($NewItemQty > 0)	{
				$sql = "SELECT stockmaster.mbflag
						FROM stockmaster
						WHERE stockmaster.stockid='". $NewItem ."'";

				$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');
            
				$KitResult = DB_query($sql,$ErrMsg);

				//$NewItemQty = 1; /*By Default */
				$Discount = 0; /*By default - can change later or discount category override */

				if ($myrow=DB_fetch_array($KitResult)){
			
					if ($myrow['mbflag']=='K'){	/*It is a kit set item */
						$sql = "SELECT bom.component,
										bom.quantity
								FROM bom
								WHERE bom.parent='" . $NewItem . "'
                                AND bom.effectiveafter <= '" . date('Y-m-d') . "'
                                AND bom.effectiveto > '" . date('Y-m-d') . "'";

						$ErrMsg = _('Could not retrieve kitset components from the database because');
						$KitResult = DB_query($sql,$ErrMsg);
	
						$ParentQty = $NewItemQty;
						while ($KitParts = DB_fetch_array($KitResult)){
							$NewItem = $KitParts['component'];
							$NewItemQty = $KitParts['quantity'] * $ParentQty;
							$NewItemDue = date($_SESSION['DefaultDateFormat']);
							$NewPOLine = 0;
							include('includes/SelectOrderItemsIntoCartCN.inc');
						}

					} else { /*Its not a kit set item*/
						
						$NewItemDue = date($_SESSION['DefaultDateFormat']);
						$NewPOLine = 0;
					
						include('includes/SelectOrderItemsIntoCartCN.inc');//添加到
				
					}
				} /* end of if its a new item */
			} /*end of if its a new item */
		}/* loop through NewItem array */
		//var_dump($_SESSION['Items'.$identifier]->LineItems);
	} /* if the NewItem_array is set */
	
	/* Run through each line of the order and work out the appropriate discount from the discount matrix */
	$DiscCatsDone = array();
	$counter =0;

	foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

		if ($OrderLine->DiscCat !="" AND ! in_array($OrderLine->DiscCat,$DiscCatsDone)){
			$DiscCatsDone[$counter]=$OrderLine->DiscCat;
			$QuantityOfDiscCat =0;

			foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
				/* add up total quantity of all lines of this DiscCat */
				if ($StkItems_2->DiscCat==$OrderLine->DiscCat){
					$QuantityOfDiscCat += $StkItems_2->Quantity;
				}
			}
			$result = DB_query("SELECT MAX(discountrate) AS discount
								FROM discountmatrix
								WHERE salestype='" .  $_SESSION['Items'.$identifier]->DefaultSalesType . "'
								AND discountcategory ='" . $OrderLine->DiscCat . "'
								AND quantitybreak <= '" . $QuantityOfDiscCat . "'");
			$myrow = DB_fetch_row($result);
			if ($myrow[0] == NULL){
				$DiscountMatrixRate = 0;
			} else {
				$DiscountMatrixRate = $myrow[0];
			}
			if ($DiscountMatrixRate != 0) {
				foreach ($_SESSION['Items'.$identifier]->LineItems as $StkItems_2) {
					if ($StkItems_2->DiscCat==$OrderLine->DiscCat){
						$_SESSION['Items'.$identifier]->LineItems[$StkItems_2->LineNumber]->DiscountPercent = $DiscountMatrixRate;
					}
				}
			}
		}
	} /* end of discount matrix lookup code */
     //显示缓存数据
	if (count($_SESSION['Items'.$identifier]->LineItems)>0){ /*only show order lines if there are any */

		/* This is where the order as selected should be displayed  reflecting any deletions or insertions*/

	 	if($_SESSION['Items'.$identifier]->DefaultPOLine ==1) {// Does customer require PO Line number by sales order line?
			$ShowPOLine=1;// Show one additional column:  'PO Line'.
		} else {
			$ShowPOLine=0;// Do NOT show 'PO Line'.
		}

		if(in_array($_SESSION['PageSecurityArray']['OrderEntryDiscountPricing'], $_SESSION['AllowedPageSecurityTokens'])) {//Is it an internal user with appropriate permissions?
			$ShowDiscountGP=2;// Show two additional columns: 'Discount' and 'GP %'.
		} else {
			$ShowDiscountGP=0;// Do NOT show 'Discount' and 'GP %'.
		}
		$curr=2;
		echo '<div class="page_help_text">' . _('Quantity (required) - Enter the number of units ordered.  Price (required) - Enter the unit price.  Discount (optional) - Enter a percentage discount.  GP% (optional) - Enter a percentage Gross Profit (GP) to add to the unit cost.  Due Date (optional) - Enter a date for delivery.') . '</div><br />';
		//2393--添加订单
		echo '<br />
			<table width="90%" cellpadding="2">
				<tr style="background-color:#800000">';
		/*		if($_SESSION['Items'.$identifier]->DefaultPOLine == 1){
		if ($_SESSION['UpdateCurrencyRatesDaily']!=0 && 
	  	$JournalItem->Currcode !=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){*/
		if($ShowPOLine) {
			echo '<th>' . _('PO Line') . '</th>';
		}
			echo'<th>' . _('Item Code') . '</th>
				 <th>' . _('Item Description') . '</th>
				 <th>' . _('Quantity') . '</th>
				 <th>' . _('QOH') . '</th>
				 <th>' . _('Unit') . '</th>';
			
			if ($_SESSION['Currency']==1 && $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
				$curr=1;
				echo'<th class="ascending">订单价格' . '<br/> ['.$_SESSION['Items'.$identifier]->CurrCode.  ']</th>';
			}
			
			echo'<th class="ascending">' . _('Order Price') . '<br/> ['.$_SESSION['CountryOfOperation'].  ']</th>
				 <th>税目</th>';
			echo'<th>价格<br>不含税</th>';		
			echo'<th>税额</th>';
			echo'<th>['.$_SESSION['CountryOfOperation'].  ']小计</th>';
			if ($_SESSION['Currency']==1&& $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
				echo'<th>['.$_SESSION['Items'.$identifier]->CurrCode.  ']小计</th>';
			}
			echo'<th>' . _('Due Date') . '</th>
				 <th>&nbsp;</th>
				 </tr>';

			$_SESSION['Items'.$identifier]->total = 0;
			$_SESSION['Items'.$identifier]->TaxTotals=0;
			//	totalVolume = 0;
			$_SESSION['Items'.$identifier]->totalWeight = 0;
			$k =0;  //row colour counter
			$TaxSql="SELECT `taxid` taxcatid, `description` taxcatname,taxrate  FROM `taxauthorities`";
			$TaxResult=DB_query($TaxSql);
		
			$rw= $_SESSION['Items'.$identifier]->LineCounter;
			//读取资料到订单录入条目(
			//var_dump($_SESSION['Items'.$identifier]->LineItems);
		foreach ($_SESSION['Items'.$identifier]->LineItems as $OrderLine) {

			$LineTotal = $OrderLine->Quantity * $OrderLine->TaxPrice ;
			$LineTaxTotal = $OrderLine->Quantity *( $OrderLine->TaxPrice- $OrderLine->Price) ;
			$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
			$LineCurrTotal = $OrderLine->Quantity * $OrderLine->CurrPrice ;
			//$DisplayDiscount = locale_number_format(($OrderLine->DiscountPercent * 100),2);
			$QtyOrdered = $OrderLine->Quantity;
			$QtyRemain = $QtyOrdered - $OrderLine->QtyInv;

			if ($OrderLine->QOHatLoc < $OrderLine->Quantity AND ($OrderLine->MBflag=='B' OR $OrderLine->MBflag=='M')) {
				/*There is a stock deficiency in the stock location selected */
				$RowStarter = '<tr style="background-color:#EEAABB">'; //rows show red where stock deficiency
			} elseif ($k==1){
				$RowStarter = '<tr class="OddTableRows">';
				$k=0;
			} else {
				$RowStarter = '<tr class="EvenTableRows">';
				$k=1;
			}
			echo $RowStarter;
            echo '<td>';
            /*			if($_SESSION['Items'.$identifier]->DefaultPOLine ==1){ //show the input field only if required*/
			if($ShowPOLine) {// Show the input field only if required.
				echo '<input maxlength="20" name="POLine_' . $OrderLine->LineNumber . '" size="20" title="' . _('Enter the customer\'s purchase order reference if required by the customer') . '" type="text" value="' . $OrderLine->LineNumber . '" /></td><td>';
			} else {
			echo '<input name="POLine_' . $OrderLine->LineNumber . '" type="hidden" value="" />';
			}

			echo '<a href="' . $RootPath . '/StockStatus.php?identifier='.$identifier . '&amp;StockID=' . $OrderLine->StockID . '&amp;DebtorNo=' . $_SESSION['Items'.$identifier]->DebtorNo . '" target="_blank">' . $OrderLine->StockID . '</a></td>
				<td title="' . $OrderLine->LongDescription . '">' . $OrderLine->ItemDescription . '</td>';
			if ($_SESSION['Currency']==1&& $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
			
				echo '<td><input class="number" maxlength="8" name="Quantity_' . $OrderLine->LineNumber . '"  id="Quantity_' . $OrderLine->LineNumber . '" required="required" size="5" title="' . _('Enter the quantity of this item ordered by the customer') . '" type="text" onChange="inCrQTY(this,'.$OrderLine->DecimalPlaces .' ,'.$rw.','.$curr.' )"  value="' . locale_number_format($OrderLine->Quantity,$OrderLine->DecimalPlaces) . '" />';
			}else{
				echo '<td><input class="number" maxlength="8" name="Quantity_' . $OrderLine->LineNumber . '"  id="Quantity_' . $OrderLine->LineNumber . '" required="required" size="5" title="输入客户订购的此项目的数量" type="text" onChange="inQTY(this,'.$OrderLine->DecimalPlaces .' ,'.$rw.')"  value="' . locale_number_format($OrderLine->Quantity,$OrderLine->DecimalPlaces) . '" />';

			}
			if ($QtyRemain != $QtyOrdered){
				echo '<br />' . locale_number_format($OrderLine->QtyInv,$OrderLine->DecimalPlaces) .' ' . _('of') . ' ' . locale_number_format($OrderLine->Quantity,$OrderLine->DecimalPlaces).' ' . _('invoiced');
			}
			//现有数量
			echo '</td>
					<td class="number">' . locale_number_format($OrderLine->QOHatLoc,$OrderLine->DecimalPlaces) . '</td>
					<td>' . $OrderLine->Units . '</td>';

			/*OK to display with discount if it is an internal user with appropriate permissions */
            /*			if (in_array($_SESSION['PageSecurityArray']['OrderEntryDiscountPricing'], $_SESSION['AllowedPageSecurityTokens'])){*/
			//价格
			if ($_SESSION['Currency']==1&& $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
				echo '<td><input class="number" maxlength="8" id="CurrPrice' . $OrderLine->LineNumber . '"  name="CurrPrice' . $OrderLine->LineNumber . '"  required="required" size="5" title="' . _('Enter the price to charge the customer for this item') . '" type="text"  onChange="inCurrPrice(this,'.$rw.' )"  value="' . locale_number_format($OrderLine->CurrPrice,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '" /></td>';
			}
			if ($_SESSION['Currency']==1&& $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
		
				echo '<td><input class="number" maxlength="8" id="TaxPrice' . $OrderLine->LineNumber . '"  name="TaxPrice' . $OrderLine->LineNumber . '"  required="required" size="5" title="' . _('Enter the price to charge the customer for this item') . '" type="text"  onChange="inCrPrice(this,'.$OrderLine->DecimalPlaces .','.$rw.','.$curr.' )"  value="' . locale_number_format($OrderLine->TaxPrice,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '" /></td>';
			}else{
				echo '<td><input class="number" maxlength="8" id="TaxPrice' . $OrderLine->LineNumber . '"  name="TaxPrice' . $OrderLine->LineNumber . '"  required="required" size="5" title="' . _('Enter the price to charge the customer for this item') . '" type="text"  onChange="inPrice(this,'.$OrderLine->DecimalPlaces .','.$rw.' )"  value="' .$OrderLine->TaxPrice  . '" /></td>';
			
			}
			echo'<td><input type="hidden" id="edit' . $OrderLine->LineNumber . '" name="edit' . $OrderLine->LineNumber . '" value="">
					<select name="TaxCat' . $OrderLine->LineNumber .'"  id="TaxCat' . $OrderLine->LineNumber .'" disabled="disabled">';
				
			DB_data_seek($TaxResult,0);
			while($row=DB_fetch_array($TaxResult)){
				if ($_SESSION['Items'.$identifier]->TaxCatID==$row['taxcatid']) {
					echo '<option selected="selected" value="' .$row['taxcatid'].'^'.$row['taxrate'] . '">' . $row['taxcatname'] . '</option>';
				} else {
					echo '<option value="' . $row['taxcatid'].'^'.$row['taxrate'] . '">' . $row['taxcatname'] . '</option>';
				}
				
			}
				echo '</select></td>';
				echo '<td><input class="number" maxlength="8" name="Price_' . $OrderLine->LineNumber . '" id="Price_' . $OrderLine->LineNumber . '" size="5" title=""  type="text" value="' . $OrderLine->Price . '"  readonly="readonly"  /></td>';
				echo '<td><input class="number" maxlength="10" name="TaxAmo' . $OrderLine->LineNumber . '"  id="TaxAmo' . $OrderLine->LineNumber . '"  size="10" title="" type="text" value="' . locale_number_format( $LineTaxTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '"  readonly="readonly" /></td>';
			

			if ($_SESSION['Items'.$identifier]->Some_Already_Delivered($OrderLine->LineNumber)){
				$RemTxt = _('Clear Remaining');
			} else {
				$RemTxt = _('Delete');
			}
				
			
			//echo '<td class="number">' . locale_number_format($OrderLine->Price,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);	  
			$LineDueDate = $OrderLine->ItemDue;
			if (!Is_Date($OrderLine->ItemDue)){
				$LineDueDate = DateAdd (Date($_SESSION['DefaultDateFormat']),'d', $_SESSION['Items'.$identifier]->DeliveryDays);
				$_SESSION['Items'.$identifier]->LineItems[$OrderLine->LineNumber]->ItemDue= $LineDueDate;
			}
			//小计
			if ($_SESSION['Currency']==1&& $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
			
				echo '<td><input class="number" maxlength="10" name="Amount' . $OrderLine->LineNumber . '"  id="Amount' . $OrderLine->LineNumber . '"  size="10" title="' . _('Enter the price to charge the customer for this item') . '" type="text"  onChange="inCrAmount(this,'.$OrderLine->DecimalPlaces .','.$rw.','.$curr.' )"  value="' . locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '" /></td>';
			}else{
				echo '<td><input class="number" maxlength="10" name="Amount' . $OrderLine->LineNumber . '"  id="Amount' . $OrderLine->LineNumber . '"  size="10" title="' . _('Enter the price to charge the customer for this item') . '" type="text"  onChange="inAmount(this,'.$OrderLine->DecimalPlaces .','.$rw.' )"  value="' . locale_number_format($LineTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '" /></td>';
		
			}
			if ($_SESSION['Currency']==1 && $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
				
				$ShowCurr=1;
					echo'<td><input class="number" maxlength="10" id="CurrAmo' . $OrderLine->LineNumber . '" name="CurrAmo' . $OrderLine->LineNumber . '"  size="10"  type="text" value="' . locale_number_format($LineCurrTotal,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '"  onChange="inCurrAmo(this,'.$rw.' )"    /></td>';
			}else{
				$ShowCurr=2;
			}

			echo '<td><input alt="' . $_SESSION['DefaultDateFormat'] . '" class="date" maxlength="10" name="ItemDue_' . $OrderLine->LineNumber . '" size="10" type="text" value="' . $LineDueDate . '" /></td>';

			echo '<td ><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier=' . $identifier . '&amp;Delete=' . $OrderLine->LineNumber . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . $RemTxt . '</a></td></tr>';

			if ($_SESSION['AllowOrderLineItemNarrative'] == 1){
				echo $RowStarter;
				$varColSpan=10;//$ShowPOLine;//+$ShowDiscountGP;
				echo '<td colspan="' . $varColSpan . '">' . _('Narrative') . ':<textarea name="Narrative_' . $OrderLine->LineNumber . '" cols="70%" rows="1" title="' . _('Enter any narrative to describe to the customer the nature of the charge for this line') . '" >' . stripslashes(AddCarriageReturns($OrderLine->Narrative)) . '</textarea><br /></td>';
				echo'<td colspan="4" ></td>';
							
				echo'</tr>';
			} else {
				echo '<tr>
						<td><input name="Narrative" type="hidden" value="" /></td>
					</tr>';
			}

			$_SESSION['Items'.$identifier]->total = $_SESSION['Items'.$identifier]->total + $LineTotal;
			$_SESSION['Items'.$identifier]->TaxTotals=$_SESSION['Items'.$identifier]->TaxTotals+$LineTaxTotal;
			//totalVolume = $_SESSION['Items'.$identifier]->totalVolume + $OrderLine->Quantity * $OrderLine->Volume;
			$_SESSION['Items'.$identifier]->totalWeight = $_SESSION['Items'.$identifier]->totalWeight + $OrderLine->Quantity * $OrderLine->Weight;

		} /* end of loop around items */

		$DisplayTotal = locale_number_format($_SESSION['Items'.$identifier]->total,$_SESSION['Items'.$identifier]->CurrDecimalPlaces);
		/*		if (in_array($_SESSION['PageSecurityArray']['OrderEntryDiscountPricing'], $_SESSION['AllowedPageSecurityTokens'])){
			$ColSpanNumber = 2;
		} else {
			$ColSpanNumber = 1;
		}*/
		
		echo '<tr class="EvenTableRows">
				<td class="number" colspan="'.($varColSpan-$ShowCurr).'"><b>合计</b></td>';
				
		echo'<td><input class="number" maxlength="10" id="TaxTotal"  size="10"  type="text" value="' . locale_number_format($_SESSION['Items'.$identifier]->TaxTotals,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '"  readonly="readonly" /></td>';
	
		echo'<td><input class="number" maxlength="10" id="AmountTotal"  size="10"  type="text" value="' . locale_number_format($_SESSION['Items'.$identifier]->total,$_SESSION['Items'.$identifier]->CurrDecimalPlaces)  . '"  readonly="readonly" /></td>';
		if ($_SESSION['Currency']==1&& $_SESSION['Items'.$identifier]->CurrCode!=$_SESSION['CompanyRecord'][$Tag]['currencydefault']){
			echo'<td><input class="number" maxlength="10" id="CurrTotal"  size="10"  type="text" value=""  readonly="readonly" /></td>';
			echo'<td  colspan="2" >['.$_SESSION['Items'.$identifier]->CurrCode.']汇率:
					<input class="number" maxlength="5" name="CurrRate"  id="CurrRate"  size="5"  type="text"  onChange="inCurrRate(this,'.$OrderLine->DecimalPlaces .','.$_SESSION['Items'.$identifier]->ExRate.','.$rw.' )"  value="'.$_SESSION['Items'.$identifier]->ExRate.'" /></td>';	  
		}else{
		
		echo'<td colspan="2">&nbsp;</td>';
		}
		echo'</tr>
			</table>';
        /*
		$DisplayVolume = locale_number_format($_SESSION['Items'.$identifier]->totalVolume,2);
		$DisplayWeight = locale_number_format($_SESSION['Items'.$identifier]->totalWeight,2);
		echo '<table>
					<tr class="EvenTableRows"><td>' . _('Total Weight') . ':</td>
						 <td>' . $DisplayWeight . '</td>
						 <td>' . _('Total Volume') . ':</td>
						 <td>' . $DisplayVolume . '</td>
					</tr>
				</table>
				<br />*/
			echo'<div class="centre">
					<input type="submit" name="Recalculate" value="' . _('Re-Calculate') . '" />
					<input type="submit" name="DeliveryDetails" value="确认保存订单" />
				</div>
				<br />';
	} # end of if lines

		/* Now show the stock item selection search stuff below */

	 if ((!isset($_POST['QuickEntry'])AND !isset($_POST['SelectAsset']))){

		echo '<input name="PartSearch" type="hidden" value="' .  _('Yes Please') . '" />';

		if ($_SESSION['FrequentlyOrderedItems']>0){ //show the Frequently Order Items selection where configured to do so

			// Select the most recently ordered items for quick select
			$SixMonthsAgo = DateAdd (Date($_SESSION['DefaultDateFormat']),'m',-6);

			$SQL="SELECT stockmaster.units,
						stockmaster.description,
						stockmaster.longdescription,
						stockmaster.stockid,
						salesorderdetails.stkcode,
						SUM(qtyinvoiced) salesqty
					FROM salesorderdetails INNER JOIN stockmaster
					ON  salesorderdetails.stkcode = stockmaster.stockid
					WHERE ActualDispatchDate >= '" . FormatDateForSQL($SixMonthsAgo) . "'
					GROUP BY stkcode
					ORDER BY salesqty DESC
					LIMIT " . $_SESSION['FrequentlyOrderedItems'];
         
			$result2 = DB_query($SQL);
			echo '<p class="page_title_text">
					<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' .
					' ' . _('Frequently Ordered Items') .
					'</p>
					<br />
					<div class="page_help_text">[该功能没有开启]' . _('Frequently Ordered Items') . _(', shows the most frequently ordered items in the last 6 months.  You can choose from this list, or search further for other items') .
					'.</div>
					<br />
					<table class="table1">
					<tr>
						<th class="ascending" >' . _('Code') . '</th>
						<th class="ascending" >' . _('Description') . '</th>
						<th>' . _('Units') . '</th>
						<th class="ascending" >' . _('On Hand') . '</th>
						<th class="ascending" >' . _('On Demand') . '</th>
						<th class="ascending" >' . _('On Order') . '</th>
						<th class="ascending" >' . _('Available') . '</th>
						<th class="ascending" >' . _('Quantity') . '</th>
					</tr>';
			$i=0;
			$j=1;
			$k=0; //row colour counter

			while ($myrow=DB_fetch_array($result2)) {
				// This code needs sorting out, but until then :
				$ImageSource = _('No Image');
				// Find the quantity in stock at location
				$QOHSQL = "SELECT sum(locstock.quantity) AS qoh
							FROM locstock
							WHERE stockid='" .$myrow['stockid'] . "'
							AND loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";
				$QOHResult =  DB_query($QOHSQL);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['qoh'];

				// Find the quantity on outstanding sales orders
				$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						WHERE salesorders.fromstkloc='" . $_SESSION['Items'.$identifier]->Location . "'
						AND salesorderdetails.completed=0
						AND salesorders.quotation=0
						AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items'.$identifier]->Location . ' ' .
					 _('cannot be retrieved because');
				$DemandResult = DB_query($sql,$ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null){
				  $DemandQty =  $DemandRow[0];
				} else {
				  $DemandQty = 0;
				}
				// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.inc
				$PurchQty = GetQuantityOnOrderDueToPurchaseOrders($myrow['stockid'], '');
				// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.inc
				$WoQty = GetQuantityOnOrderDueToWorkOrders($myrow['stockid'], '');

				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$OnOrder = $PurchQty + $WoQty;

				$Available = $QOH - $DemandQty + $OnOrder;
				//required="required"
				printf('<td>%s</td>
						<td title="%s">%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td><input class="number" ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="%s" type="text"  size="6" name="OrderQty%s" value="" />
						<input name="StockID%s" type="hidden" value="%s" />
						</td>
						</tr>',
						$myrow['stockid'],
						$myrow['longdescription'],
						$myrow['description'],
						$myrow['units'],
						locale_number_format($QOH, $QOHRow['decimalplaces']),
						locale_number_format($DemandQty, $QOHRow['decimalplaces']),
						locale_number_format($OnOrder, $QOHRow['decimalplaces']),
						locale_number_format($Available, $QOHRow['decimalplaces']),
						strval($j+7),
						$i,
						$i,
						$myrow['stockid']);
				$i++;
				#end of page full new headings if
			}
				#end of while loop for Frequently Ordered Items
			echo '<td style="text-align:center" colspan="8">
					 <input name="SelectingOrderItems" type="hidden" value="1" />
					 <input tabindex="'.strval($j+8).'" type="submit" value="'._('Add to Sales Order').'" /></td></tr>';
			echo '</table>';
		} //end of if Frequently Ordered Items > 0
		echo '<br /><div class="centre">' . $msg;
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
		echo _('Search for Order Items') . '</p></div>';
		echo '<div class="page_help_text">' . _('Search for Order Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
		echo '<table class="selection">
				<tr>
					<td><b>' . _('Select a Stock Category') . ': </b><select tabindex="1" name="StockCat">';

		if (!isset($_POST['StockCat']) OR $_POST['StockCat']=='All'){
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
			$_POST['StockCat'] = 'All';
		} else {
			echo '<option value="All">' . _('All') . '</option>';
		}
		$SQL="SELECT categoryid,
						categorydescription
				FROM stockcategory
				WHERE stocktype='M' AND categoryid IN (SELECT loccode FROM locationusers WHERE userid='".$_SESSION['UserID']."')
				 ORDER BY categorydescription";
		 //	WHERE stocktype='F' OR stocktype='D' OR stocktype='L'
		
		$result1 = DB_query($SQL);
		while ($myrow1 = DB_fetch_array($result1)) {
			if ($_POST['StockCat']==$myrow1['categoryid']){
				echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			} else {
				echo '<option value="'. $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			}
		}

		echo '</select></td>
			<td><b>' . _('Enter partial Description') . ':</b>
			<input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="' ;

        if (isset($_POST['Keywords'])) {
             echo $_POST['Keywords'] ;
        }
        echo '" /></td>';

		echo '<td align="right"><b>' . _('OR') .  ' ' . _('Enter extract of the Stock Code') . ':</b>
		          <input tabindex="3" type="text" ' . (!isset($_POST['PartSearch']) ? 'autofocus="autofocus"' :'') . ' name="StockCode" size="15" maxlength="18" value="';
        if (isset($_POST['StockCode'])) {
            echo  $_POST['StockCode'];
        }
		echo '" /></td>

		<td><input type="checkbox" name="RawMaterialFlag" value="M" />'._('Raw material flag').'&nbsp;&nbsp;<br/><span class="dpTbl">'._('If checked, Raw material will be shown on search result').'</span> </td>
		<td><input type="checkbox" name="CustItemFlag" value="C" />'._('Customer Item flag').'&nbsp;&nbsp;<br/><span class="dpTbl">'._('If checked, only items for this customer will show').'</span> </td>
			</tr>';

		echo '<tr>
			<td style="text-align:center" colspan="1">
			    <input tabindex="4" type="submit" name="Search" value="' . _('Search Now') . '" /></td>
			<td style="text-align:center" colspan="1">
			     <input tabindex="5" type="submit" name="QuickEntry" value="' .  _('Use Quick Entry') . '" /></td>';

		if (in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])){ //not a customer entry of own order
			echo '<td style="text-align:center" colspan="1">
			         <input tabindex="6" type="submit" name="ChangeCustomer" value="' . _('Change Customer') . '" /></td>
			<td style="text-align:center" colspan="1">
			         <input tabindex="7" type="submit" name="SelectAsset" value="' . _('Fixed Asset Disposal') . '" /></td>';
		}
        echo '</tr>
			</table>
			<br />
			</div>';
		//增至销售订单 
		if (isset($SearchResult)) {
			echo '<br />';
			echo '<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>';//ͨ
			echo '<br />';
			$j = 1;
			//echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" method="post" name="orderform">';
            echo '<div>';
			echo '<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />';
			echo '<table class="table1">';
			echo '<tr>
			         <td colspan="1">
			         <input name="PreviousList" type="hidden" value="'.strval($Offset-1).'" />
					 <input tabindex="'.strval($j+8).'" type="submit" name="Previous" value="'._('Previous').'" /></td>';
			echo '<td style="text-align:center" colspan="6">
			          <input name="SelectingOrderItems" type="hidden" value="1" />
					  <input tabindex="'.strval($j+9).'" type="submit" value="'._('Add to Sales Order').'" /></td>';
			echo '<td colspan="1">
			          <input name="NextList" type="hidden" value="'.strval($Offset+1).'" />
			          <input tabindex="'.strval($j+10).'" name="Next" type="submit" value="'._('Next').'" /></td></tr>';
			echo '<tr>
				    <th class="ascending" >' . _('Code') . '</th>
		   			<th class="ascending" >' . _('Description') . '</th>
					<th class="ascending" >' . _('Customer Item') . '</th>
		   			<th>' . _('Units') . '</th>
		   			<th class="ascending" >' . _('On Hand') . '</th>
		   			<th class="ascending" >' . _('On Demand') . '</th>
		   			<th class="ascending" >' . _('On Order') . '</th>
		   			<th class="ascending" >' . _('Available') . '</th>
		   			<th>' . _('Quantity') . '</th>
		   		</tr>';
			$ImageSource = _('No Image');
			$i=0;
			$k=0; //row colour counter
			
		
			while ($myrow=DB_fetch_array($SearchResult)) {

				// Find the quantity in stock at location
				$QOHSQL = "SELECT quantity AS qoh,
									stockmaster.decimalplaces
							   FROM locstock INNER JOIN stockmaster
							   ON locstock.stockid = stockmaster.stockid
							   WHERE locstock.stockid='" .$myrow['stockid'] . "' AND
							   loccode = '" . $_SESSION['Items'.$identifier]->Location . "'";
				$QOHResult =  DB_query($QOHSQL);
				$QOHRow = DB_fetch_array($QOHResult);
				$QOH = $QOHRow['qoh'];

				// Find the quantity on outstanding sales orders
				$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
						FROM salesorderdetails INNER JOIN salesorders
						ON salesorders.orderno = salesorderdetails.orderno
						 WHERE  salesorders.fromstkloc='" . $_SESSION['Items'.$identifier]->Location . "'
						 AND salesorderdetails.completed=0
						 AND salesorders.quotation=0
						 AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";

				$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Items'.$identifier]->Location . ' ' . _('cannot be retrieved because');
				$DemandResult = DB_query($sql,$ErrMsg);

				$DemandRow = DB_fetch_row($DemandResult);
				if ($DemandRow[0] != null){
					$DemandQty =  $DemandRow[0];
				} else {
					$DemandQty = 0;
				}

				// Get the QOO due to Purchase orders for all locations. Function defined in SQL_CommonFunctions.inc
				$PurchQty = GetQuantityOnOrderDueToPurchaseOrders($myrow['stockid'], '');
				// Get the QOO dues to Work Orders for all locations. Function defined in SQL_CommonFunctions.inc
				$WoQty = GetQuantityOnOrderDueToWorkOrders($myrow['stockid'], '');

				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$OnOrder = $PurchQty + $WoQty;
				$Available = $QOH - $DemandQty + $OnOrder;

				printf('<td>%s</td>
						<td title="%s">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td>
						<input class="number" tabindex="%s" type="text" size="6" name="OrderQty%s"  ' . ($i==0 ? 'autofocus="autofocus"':'') . ' value="" min="0"/>
						<input name="StockID%s" type="hidden" value="%s" />
						</td>
						</tr>',
						$myrow['stockid'],
						$myrow['longdescription'],
						$myrow['description'],
						$myrow['cust_part'] . '-' . $myrow['cust_description'],
						$myrow['units'],
						locale_number_format($QOH,$QOHRow['decimalplaces']),
						locale_number_format($DemandQty,$QOHRow['decimalplaces']),
						locale_number_format($OnOrder,$QOHRow['decimalplaces']),
						locale_number_format($Available,$QOHRow['decimalplaces']),
						strval($j+7),
						$i,
						$i,
						$myrow['stockid'] );
				$i++;
				$j++;
			#end of page full new headings if
			}
				#end of while loop
			echo '<tr>
					<td><input name="PreviousList" type="hidden" value="'. strval($Offset-1).'" />
					     <input tabindex="'. strval($j+7).'" type="submit" name="Previous" value="'._('Previous').'" /></td>
					<td style="text-align:center" colspan="6">
					      <input name="SelectingOrderItems" type="hidden" value="1" />
					      <input tabindex="'. strval($j+8).'" type="submit" value="'._('Add to Sales Order').'" /></td>
					<td>
					    <input name="NextList" type="hidden" value="'.strval($Offset+1).'" />
					     <input tabindex="'.strval($j+9).'" name="Next" type="submit" value="'._('Next').'" /></td>
				</tr>
				</table>
				</div>';

		}#end if SearchResults to show
		echo '</form>';
		  /*end of PartSearch options to be displayed */
	}elseif( isset($_POST['QuickEntry'])) { /* show the quick entry form variable */
		 	 /*FORM VARIABLES TO POST TO THE ORDER  WITH PART CODE AND QUANTITY */
	   		echo '<div class="page_help_text"><b>' . _('Use this screen for the '). _('Quick Entry')._(' of products to be ordered') . '</b></div><br />
		 			<table class="selection">
					<tr>';
			/*do not display colum unless customer requires po line number by sales order line*/
		 	if($_SESSION['Items'.$identifier]->DefaultPOLine ==1){
				echo	'<th>' . _('PO Line') . '</th>';
			}
			echo '<th>' . _('Part Code') . '</th>
				  <th>' . _('Quantity') . '</th>
				  <th>' . _('Due Date') . '</th>
				  </tr>';
			$DefaultDeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$_SESSION['Items'.$identifier]->DeliveryDays);
			for ($i=1;$i<=$_SESSION['QuickEntries'];$i++){

		 		echo '<tr class="OddTableRow">';
		 		/* Do not display colum unless customer requires po line number by sales order line*/
		 		if($_SESSION['Items'.$identifier]->DefaultPOLine > 0){
					echo '<td><input type="text" name="poline_' . $i . '" size="21" maxlength="20" title="' . _('Enter the customer purchase order reference') . '" /></td>';
				}
				echo '<td>
				        <input type="text" name="part_' . $i . '" size="21" maxlength="20" title="' . _('Enter the item code ordered') . '" /></td>
						<td><input class="number" type="text" name="qty_' . $i . '" size="6" maxlength="6" title="' . _('Enter the quantity of the item ordered by the customer') . '" /></td>
						<td><input type="text" class="date" name="itemdue_' . $i . '" size="25" maxlength="25"
                        alt="'.$_SESSION['DefaultDateFormat'].'" value="' . $DefaultDeliveryDate . '" title="' . _('Enter the date that the customer requires delivery by') . '" /></td>
                      </tr>';
	   		}
			echo '</table>
					<br />
					<div class="centre">
						<input type="submit" name="QuickEntry" value="' . _('Quick Entry') . '" />
						<input type="submit" name="PartSearch" value="' . _('Search Parts') . '" />
					</div>
					</div>
                  </form>';
		}elseif(isset($_POST['SelectAsset'])){

			echo '<div class="page_help_text"><b>' . _('Use this screen to select an asset to dispose of to this customer') . '</b></div>
					<br />
		 			<table border="1">';
			/*do not display colum unless customer requires po line number by sales order line*/
		 	if($_SESSION['Items'.$identifier]->DefaultPOLine ==1){
				echo	'<tr>
							<td>' . _('PO Line') . '</td>
							<td>
							   <input type="text" name="poline" size="21" maxlength="20" title="' . _('Enter the customer\'s purchase order reference') . '" /></td>
						</tr>';
			}
			echo '<tr>
					<td>' . _('Asset to Dispose Of') . ':</td>
					<td><select name="AssetToDisposeOf">';
			$AssetsResult = DB_query("SELECT assetid, description FROM fixedassets WHERE disposaldate='0000-00-00'");
			echo '<option selected="selected" value="NoAssetSelected">' . _('Select Asset To Dispose of From the List Below') . '</option>';
			while ($AssetRow = DB_fetch_array($AssetsResult)){
				echo '<option value="' . $AssetRow['assetid'] . '">' . $AssetRow['assetid'] . ' - ' . $AssetRow['description'] . '</option>';
			}
			echo '</select></td>
				</tr>
				</table>
				<br />
				<div class="centre">
					<input type="submit" name="AssetDisposalEntered" value="' . _('Add Asset To Order') . '" />
					<input type="submit" name="PartSearch" value="' . _('Search Parts') . '" />
				</div>
				</form>';

		} //end of if it is a Quick Entry screen/part search or asset selection form to display

		if ($_SESSION['Items'.$identifier]->ItemsOrdered >=1){
			echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .
				'" method="post" name="deleteform">';
            		echo '<div>';
			echo '<input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />
				<br />
				<div class="centre">
					<input name="CancelOrder" type="submit" value="' . _('Cancel Whole Order') . '" onclick="return confirm(\'' . _('Are you sure you wish to cancel this entire order?') . '\');" />
				</div>
                </div>
				</form>';
		}
	}#end of else not selecting a customer

include('includes/footer.php');
/*
function GetCustBranchDetails($identifier) {
		global $db;

					FROM custbranch
			
		$ErrMsg = _('The customer branch record of the customer selected') . ': ' . $_SESSION['Items'.$identifier]->DebtorNo . ' ' . _('cannot be retrieved because');
		$DbgMsg = _('SQL used to retrieve the branch details was') . ':';
		$result = DB_query($sql,$ErrMsg,$DbgMsg);
		return $result;
}*/
?>
