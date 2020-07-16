
<?php
/** $Id:PO_PurchReceive.php*/
/*无合同采购收货 用 PO_Header.php、 SelectProduct.php、PO_SelectOSPurchOrder.php部分代码 $ */
/*
 * @Author: ChengJiang 
 * @Date: 2019-03-15 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-09-11 20:55:24
 */
include('includes/DefinePOClass.php');
include('includes/session.php');
$Title = '简易采购收货';
$ViewTopic= 'Inventory';
$BookMark = 'PurchReceive';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
function inPrice(p,d,r){		
	var  n=p.name.substring(7);	
	var vlqty = document.getElementById("SuppQty"+n);
	var suppqty=vlqty.value.replace(",","");
	//var len=suppqty.length+parseInt(p.value).length-2;
	//if (len<2){
		len=d;
	//}
	var total=0;
		
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var taxrate = obj.options[index].value.split("^")[1]; // 选中值
	console.log(taxrate);
	if ((1*p.value).toFixed(2)<(1*p.value)){
		p.value=(1*p.value).toFixed(2);
	}
	if (vlqty.value!=""){
		//数量不为空
		document.getElementById("edit"+n).value=1;
		total=(p.value*suppqty).toFixed(2);
		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price"+n).value=(parseFloat(p.value)/(1+parseFloat(taxrate))).toFixed(len);
		document.getElementById("TaxAmo"+n).value=(total*taxrate).toFixed(2);
	}	
	var taxtol=(taxrate*total).toFixed(2); 
	document.getElementById("TaxAmo"+n).value=taxtol;
	document.getElementById("Amount"+n).value=Number(total).toFixed(2); 
	var taxtotal=0;
	var amototal=0;
	for(var i=1; i<=r; i++){			
		taxtotal=parseFloat(taxtotal)+parseFloat(document.getElementById("TaxAmo"+i).value);
		amototal=parseFloat(amototal)+parseFloat(document.getElementById("Amount"+i).value);
	}
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);	
}
function inQTY(p,d,r){
	var  n=p.name.substring(7);	
	var vl = document.getElementById("SuPrice"+n);
	var qty=(1*p.value).toFixed(d);
	if (parseFloat(p.value)!=qty){	
		p.value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}	
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var taxrate = obj.options[index].value.split("^")[1]; // 选中值
	var taxamo=0;
	var total=0;
	if (vl.value!=""){
		//数量不为空
		document.getElementById("edit"+n).value=1;
		total=(p.value*vl.value).toFixed(2);
		document.getElementById("Amount"+n).value=total;
		document.getElementById("Price"+n).value=(parseFloat(vl.value)/(1+parseFloat(taxrate))).toFixed(2);
		taxamo=(total/(1+parseFloat(taxrate))*parseFloat(taxrate)).toFixed(2);
		document.getElementById("TaxAmo"+n).value=taxamo;
	}		
	var taxtotal=0;
	var amototal=0;
	for(var i=1; i<=r; i++){
			
		taxtotal=parseFloat(taxtotal)+parseFloat(document.getElementById("TaxAmo"+i).value);
		amototal=parseFloat(amototal)+parseFloat(document.getElementById("Amount"+i).value);
	}	
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
}
function inAmount(p,d,r){
	var  n=p.name.substring(6);	
	var vlqty = document.getElementById("SuppQty"+n);
	var suppqty=vlqty.value.replace(",","");
	var len=suppqty.length;	
	//输入数字小数位数和设置	
	if (1*p.value!=(1*p.value).toFixed(2)){
		p.value=(parseFloat(p.value)).toFixed(2);
	}	
	var vlprice=0;			
	var obj = document.getElementById("TaxCat"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var taxrate = obj.options[index].value.split("^")[1]; // 选中值
	if (vlqty.value!=""){
		//数量不为空
		document.getElementById("edit"+n).value=1;
		vlprice=parseFloat(p.value)/parseFloat(suppqty);	
		len=len+parseInt(vlprice).toString().length;
		
		if (len<2){
			len=2;
		}		
		document.getElementById("SuPrice"+n).value=vlprice.toFixed(len);		
		document.getElementById("Price"+n).value=(parseFloat(vlprice)/(1+parseFloat(taxrate))).toFixed(len);
		document.getElementById("TaxAmo"+n).value=(parseFloat(p.value)/(1+taxrate)*taxrate).toFixed(2);
	}
	var taxtotal=0;
	var amototal=0;
	for(var i=1; i<=r; i++){			
		taxtotal=parseFloat(taxtotal)+parseFloat(document.getElementById("TaxAmo"+i).value);
		amototal=parseFloat(amototal)+parseFloat(document.getElementById("Amount"+i).value);
	}
	document.getElementById("AmountTotal").value =amototal.toFixed(2);
	document.getElementById("TaxTotal").value =taxtotal.toFixed(2);
}

	function refresh() {  
		window.location.reload();
	}  
</script>';
if (empty($_GET['identifier'])) {
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}
/*
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}*/
	
/*
if (isset($_SESSION['PO' . $identifier])) {
	unset($_SESSION['PO' . $identifier]);
	$_SESSION['ExistingOrder'] = 0;
}
*/



if (!isset($_SESSION['PO' . $identifier])) {
	$_SESSION['ExistingOrder'] = 0;
	$_SESSION['PO' . $identifier] = new PurchOrder;
	$_SESSION['PO' . $identifier]->AllowPrintPO = 3;	
	$_SESSION['PO' . $identifier]->GLLink = $_SESSION['CompanyRecord']['gllink_stock'];

	if ($_SESSION['PO' . $identifier]->SupplierID == '' OR !isset($_SESSION['PO' . $identifier]->SupplierID)) {
	    
		$_SESSION['RequireSupplierSelection'] = 1;
	} else {
		$_SESSION['RequireSupplierSelection'] = 0;
	}

} //end if initiating a new PO
//查找客户
if (isset($_POST['SearchSuppliers'])) {
	if (mb_strlen($_POST['Keywords']) > 0 AND mb_strlen($_SESSION['PO' . $identifier]->SupplierID) > 0) {
		prnMsg(_('Supplier name keywords have been used in preference to the supplier code extract entered'), 'warn');
	}
	$SQL = "SELECT suppliers.supplierid,
					custname_reg_sub.custname suppname,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.currcode
				FROM suppliers
				LEFT JOIN custname_reg_sub  ON suppliers.supplierid=custname_reg_sub.regid
				WHERE suppliers.supplierid IN (SELECT `regid` FROM `customerusers` WHERE userid ='".$_SESSION['UserID']."')";
     
	if (mb_strlen($_POST['Keywords']) > 0) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		$SQL.=" AND custname_reg_sub.custname " . LIKE . " '" . $SearchString . "' ";

	} elseif (mb_strlen($_POST['SuppCode']) > 0) {

		$SQL .= " AND suppliers.supplierid " . LIKE . " '%" . $_POST['SuppCode'] . "%' ";
	} 

		$SQL .= "	ORDER BY suppliers.supplierid";
	

	$ErrMsg = _('The searched supplier records requested cannot be retrieved because');
	$result_SuppSelect = DB_query($SQL, $ErrMsg);
	$SuppliersReturned = DB_num_rows($result_SuppSelect);
	if (DB_num_rows($result_SuppSelect) == 1) {
		$myrow = DB_fetch_array($result_SuppSelect);
		$_POST['SelectSupp'] = $myrow['supplierid'];
		$_POST['SelectSuppname'] = $myrow['suppname'];
	} elseif (DB_num_rows($result_SuppSelect) == 0) {
		
		$Msg=_('No supplier records contain the selected text') . ' - ' . _('please alter your search criteria and try again');//, 'info');
	}
} /*end of if search for supplier codes/names */


if (isset($_POST['SelectSupp'])) {	
	//prnMsg($_POST['SelectSupp'].'select898','info');
	$sql = "SELECT custname_reg_sub.custname suppname,
					suppliers.currcode,
					currencies.rate,
					currencies.decimalplaces,
					suppliers.paymentterms,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.telephone,
					suppliers.port,
					suppliers.taxcatid,
					suppliers.taxrate
			
				FROM suppliers 
				LEFT JOIN custname_reg_sub  ON suppliers.supplierid=custname_reg_sub.regid
				INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_POST['SelectSupp'] . "'";
	$ErrMsg = _('The supplier record of the supplier selected') . ': ' . $_POST['SelectSupp'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$result = DB_query($sql, $ErrMsg, $DbgMsg);
	$myrow = DB_fetch_array($result);
	// added for suppliers lookup fields
	$AuthSql = "SELECT cancreate
				FROM purchorderauth
				WHERE userid='" . $_SESSION['UserID'] . "'
				AND currabrev='" . $myrow['currcode'] . "'";
	$AuthResult = DB_query($AuthSql);
	if (($AuthRow = DB_fetch_array($AuthResult) and $AuthRow['cancreate'] == 0)) {
		$_POST['SupplierName'] = $myrow['suppname'];
		$_POST['CurrCode'] = $myrow['currcode'];
		$_POST['CurrDecimalPlaces'] = $myrow['decimalplaces'];
		$_POST['ExRate'] = $myrow['rate'];
		$_POST['PaymentTerms'] = $myrow['paymentterms'];
		$_POST['SuppDelAdd1'] = $myrow['address1'];
		$_POST['SuppDelAdd2'] = $myrow['address2'];
		$_POST['SuppDelAdd3'] = $myrow['address3'];
		$_POST['SuppDelAdd4'] = $myrow['address4'];
		$_POST['SuppDelAdd5'] = $myrow['address5'];
		$_POST['SuppDelAdd6'] = $myrow['address6'];
		$_POST['SuppTel'] = $myrow['telephone'];
		$_POST['Port'] = $myrow['port'];
		//$_POST['TaxGroupID'] = '';
		$_POST['TaxCatID'] = $myrow['taxcatid'];
		$_POST['TaxRate'] = $myrow['taxrate'];
	
		$_SESSION['PO' . $identifier]->SupplierID = $_POST['SelectSupp'];
		$_SESSION['RequireSupplierSelection'] = 0;
		$_SESSION['PO' . $identifier]->SupplierName = $_POST['SupplierName'];
		$_SESSION['PO' . $identifier]->CurrCode = $_POST['CurrCode'];
		$_SESSION['PO' . $identifier]->CurrDecimalPlaces = $_POST['CurrDecimalPlaces'];
		$_SESSION['PO' . $identifier]->ExRate = $_POST['ExRate'];
		$_SESSION['PO' . $identifier]->PaymentTerms = $_POST['PaymentTerms'];
		$_SESSION['PO' . $identifier]->SuppDelAdd1 = $_POST['SuppDelAdd1'];
		$_SESSION['PO' . $identifier]->SuppDelAdd2 = $_POST['SuppDelAdd2'];
		$_SESSION['PO' . $identifier]->SuppDelAdd3 = $_POST['SuppDelAdd3'];
		$_SESSION['PO' . $identifier]->SuppDelAdd4 = $_POST['SuppDelAdd4'];
		$_SESSION['PO' . $identifier]->SuppDelAdd5 = $_POST['SuppDelAdd5'];
		$_SESSION['PO' . $identifier]->SuppDelAdd6 = $_POST['SuppDelAdd6'];
		$_SESSION['PO' . $identifier]->SuppTel = $_POST['SuppTel'];
		$_SESSION['PO' . $identifier]->Port = $_POST['Port'];
		$_SESSION['PO' . $identifier]->TaxGroupID = $_POST['TaxGroupID'];
		$_SESSION['PO' . $identifier]->TaxCatID = $_POST['TaxCatID'];
		$_SESSION['PO' . $identifier]->TaxRate = $_POST['TaxRate'];
	

	} else {

		prnMsg(_('You do not have the authority to raise Purchase Orders for') . ' ' . $myrow['suppname'] . '. ' . _('Please Consult your system administrator for more information.') . '<br />' . _('You can setup authorisations') . ' ' . '<a href="PO_AuthorisationLevels.php">' . _('here') . '</a>', 'warn');
		include('includes/footer.php');
		exit;
	}

	// end of added for suppliers lookup fields

} else {
	$_POST['SelectSupp'] = $_SESSION['PO' . $identifier]->SupplierID;
	$sql = "SELECT custname_reg_sub.custname suppname,
					suppliers.currcode,
					currencies.decimalplaces,
					suppliers.paymentterms,
					suppliers.address1,
					suppliers.address2,
					suppliers.address3,
					suppliers.address4,
					suppliers.address5,
					suppliers.address6,
					suppliers.telephone,
					suppliers.port,
					suppliers.taxcatid,
					suppliers.taxrate
					
				FROM suppliers 
				LEFT JOIN custname_reg_sub  ON suppliers.supplierid=custname_reg_sub.regid		
				INNER JOIN currencies
				ON suppliers.currcode=currencies.currabrev
				WHERE supplierid='" . $_POST['SelectSupp'] . "'";

	$ErrMsg = _('The supplier record of the supplier selected') . ': ' . $_POST['SelectSupp'] . ' ' . _('cannot be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the supplier details and failed was');
	$result = DB_query($sql, $ErrMsg, $DbgMsg);
    
	$myrow = DB_fetch_array($result);

	// added for suppliers lookup fields
	if (!isset($_SESSION['PO' . $identifier])) {
		$_POST['SupplierName'] = $myrow['suppname'];
		$_POST['CurrCode'] = $myrow['currcode'];
		$_POST['CurrDecimalPlaces'] = $myrow['decimalplaces'];
		$_POST['ExRate'] = $myrow['rate'];
		$_POST['PaymentTerms'] = $myrow['paymentterms'];
		$_POST['SuppDelAdd1'] = $myrow['address1'];
		$_POST['SuppDelAdd2'] = $myrow['address2'];
		$_POST['SuppDelAdd3'] = $myrow['address3'];
		$_POST['SuppDelAdd4'] = $myrow['address4'];
		$_POST['SuppDelAdd5'] = $myrow['address5'];
		$_POST['SuppDelAdd6'] = $myrow['address6'];
		$_POST['SuppTel'] = $myrow['telephone'];
		$_POST['Port'] = $myrow['port'];
	//	$_POST['TaxGroupID'] = $myrow['taxgroupid'];
		$_POST['TaxCatID'] = $myrow['taxcatid'];
		$_POST['TaxRate'] = $myrow['taxrate'];	

		$_SESSION['PO' . $identifier]->SupplierID = $_POST['SelectSupp'];
		$_SESSION['RequireSupplierSelection'] = 0;
		$_SESSION['PO' . $identifier]->SupplierName = $_POST['SupplierName'];
		$_SESSION['PO' . $identifier]->CurrCode = $_POST['CurrCode'];
		$_SESSION['PO' . $identifier]->CurrDecimalPlaces = $_POST['CurrDecimalPlaces'];
		$_SESSION['PO' . $identifier]->ExRate = filter_number_format($_POST['ExRate']);
		$_SESSION['PO' . $identifier]->PaymentTerms = $_POST['PaymentTerms'];
		$_SESSION['PO' . $identifier]->SuppDelAdd1 = $_POST['SuppDelAdd1'];
		$_SESSION['PO' . $identifier]->SuppDelAdd2 = $_POST['SuppDelAdd2'];
		$_SESSION['PO' . $identifier]->SuppDelAdd3 = $_POST['SuppDelAdd3'];
		$_SESSION['PO' . $identifier]->SuppDelAdd4 = $_POST['SuppDelAdd4'];
		$_SESSION['PO' . $identifier]->SuppDelAdd5 = $_POST['SuppDelAdd5'];
		$_SESSION['PO' . $identifier]->SuppDelAdd6 = $_POST['SuppDelAdd6'];
		$_SESSION['PO' . $identifier]->SuppTel = $_POST['SuppTel'];
		$_SESSION['PO' . $identifier]->Port = $_POST['Port'];
		$_SESSION['PO' . $identifier]->TaxGroupID = $_POST['TaxGroupID'];
		$_SESSION['PO' . $identifier]->TaxCatID = $_POST['TaxCatID'];
		$_SESSION['PO' . $identifier]->TaxRate = $_POST['TaxRate'];
		// end of added for suppliers lookup fields
	}
}
$TitleSupplier='';
if ($_SESSION['PO' . $identifier]->SupplierID != '' && isset($_SESSION['PO' . $identifier]->SupplierID)) {
	
	$TitleSupplier=':['. $_SESSION['PO' . $identifier]->SupplierID.']'.$_SESSION['PO' . $identifier]->SupplierName;
}elseif($_POST['SelectSupp'] !=''){
	$TitleSupplier=':['.$_POST['SelectSupp'].']'.$_POST['SelectSuppname'] ;
	//$TitleSupplier ;
}
echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title .$TitleSupplier.'</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" id="choosesupplier">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$sql="SELECT confvalue FROM myconfig WHERE conftype=-1 AND confname='purch'";
	$result=DB_query($sql);
	$row=DB_fetch_assoc($result);
	$purchtyp=0;
	if ($row['confvalue']!=''){
		$purchtyp=$row['confvalue'];
	}
	if (isset($_POST['SelectSupp']) AND empty($_POST['SupplierContact'])) {
		$sql = "SELECT contact
					FROM suppliercontacts
					WHERE supplierid='" . $_POST['SelectSupp'] . "'";
	
		$SuppCoResult = DB_query($sql);
		if (DB_num_rows($SuppCoResult) > 0) {
			$myrow = DB_fetch_row($SuppCoResult);
			$_POST['SupplierContact'] = $myrow[0];
		} else {
			$_POST['SupplierContact'] = '';
		}
	}


if ($_SESSION['PO' . $identifier]->SupplierID == '' || !isset($_SESSION['PO' . $identifier]->SupplierID)) {
	//if (!isset($result_SuppSelect)){
	echo '<table cellpadding="3" class="selection">
			<tr>
				<td>' . _('Enter text in the supplier name') . ':</td>
				<td><input type="text" name="Keywords" autofocus="autofocus" size="20" maxlength="25" /></td>
				<td><h3><b>' . _('OR') . '</b></h3></td>
				<td>' . _('Enter text extract in the supplier code') . ':</td>
				<td><input type="text" name="SuppCode" size="15" maxlength="18" /></td>
			</tr>
			</table>
			<br />
			<div class="centre">
				<input type="submit" name="SearchSuppliers" value="' . _('Search Now') . '" />
				<input type="submit" value="' . _('Reset') . '" />
			</div>';
		if ($Msg!=''){
			prnMsg($Msg,'info');
		}
  
	//if (isset($_GET['identifier'])&&$_SESSION['RequireSupplierSelection'] == 1){//$result_SuppSelect)) {
	if (isset($result_SuppSelect)){
		echo '<br /><table cellpadding="3" class="selection">';
		echo '<tr>
				<th class="ascending">' . _('Code') . '</th>
				<th class="ascending">' . _('Supplier Name') . '</th>
				<th class="ascending">' . _('Address') . '</th>
				<th class="ascending">' . _('Currency') . '</th>
			</tr>';
		$j = 1;
		$k = 0;
		//row counter to determine background colour 

		while ($myrow = DB_fetch_array($result_SuppSelect)) {
			if ($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}

			echo '<td><input type="submit" style="width:100%" name="SelectSupp" value="' . $myrow['supplierid'] . '" /></td>
				<td>' . $myrow['suppname'] . '</td><td>';

			for ($i = 1; $i <= 6; $i++) {
				if ($myrow['address' . $i] != '') {
					echo $myrow['address' . $i] . '<br />';
				}
			}
			echo '</td>
					<td>' . $myrow['currcode'] . '</td>
				</tr>';

			//end of page full new headings if
		} //end of while loop

		echo '</table>';
	}
}	
if (isset($_POST['UpdateLines']) OR isset($_POST['Commit'])) {

	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
		if ($POLine->Deleted == false) {
		
			if (!is_numeric(filter_number_format($_POST['SuppQty'.$POLine->LineNo]))){
				prnMsg(_('The quantity in the supplier units is expected to be numeric. Please re-enter as a number'),'error');
			} else { //ok to update the PO object variables
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->Quantity = round(filter_number_format($_POST['SuppQty'.$POLine->LineNo]),$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->DecimalPlaces);
			}
			if (!is_numeric(filter_number_format($_POST['SuPrice'.$POLine->LineNo]))){
				prnMsg(_('The supplier price is expected to be numeric. Please re-enter as a number'),'error');
			} else { //ok to update the PO object variables
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->TaxPrice = filter_number_format($_POST['SuPrice'.$POLine->LineNo]);
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->Price =round( filter_number_format($_POST['SuPrice'.$POLine->LineNo]/(1+$_SESSION['PO'.$identifier]->TaxRate)),2);
	
			}
			$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ReqDelDate = $_POST['ReqDelDate'.$POLine->LineNo];
            $_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->ItemDescription = $_POST['ItemDescription'.$POLine->LineNo];
		}
	}
	//var_dump($_SESSION['PO'.$identifier]->LineItems );

    foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
		//prnMsg('215='.$_POST['edit'.$POLine->LineNo].'p-'.$_POST['TaxCat'.$POLine->LineNo].'-'.$_POST['Price'.$POLine->LineNo]);
		//if ($POLine->Deleted == false) {
		if ($_POST['edit'.$POLine->LineNo]==1){//标记为更新
				$SuPrice=	str_ireplace(",","",$_POST['SuPrice'.$POLine->LineNo]);
				$Price=	str_ireplace(",","",$_POST['Price'.$POLine->LineNo]);
				$SuppQty=	str_ireplace(",","",$_POST['SuppQty'.$POLine->LineNo]);
				$cess=round((($SuPrice-$Price)/$Price),2);
				$_SESSION['PO'.$identifier]->update_item(	$POLine->LineNo,
															$SuppQty,
															$Price,
															(is_numeric($cess)?$cess:'0'),	
															$SuPrice,
															$_POST['Remark'.$POLine->LineNo]);
				$_POST['edit'.$POLine->LineNo]=0;
			
		}
	  
	}
	//var_dump($_SESSION['PO'.$identifier]->LineItems );
}

if (isset($_POST['Commit'])){
	
	$result = DB_Txn_Begin();

	/*figure out what status to set the order to */
	if (IsEmailAddress($_SESSION['UserEmail'])){
		$UserDetails  = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName']. '</a>';
	} else {
		$UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
	}
	//授权金额查验
	if ($_SESSION['AutoAuthorisePO']==1) {
		//if the user has authority to authorise the PO then it will automatically be authorised
		$AuthSQL ="SELECT authlevel
					FROM purchorderauth
					WHERE userid='".$_SESSION['UserID']."'
					AND currabrev='".$_SESSION['PO'.$identifier]->CurrCode."'";

		$AuthResult=DB_query($AuthSQL);
		$AuthRow=DB_fetch_array($AuthResult);

		if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $_SESSION['PO'.$identifier]->Order_Value()) { //user has authority to authrorise as well as create the order
			$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created and Authorised by') . $UserDetails . '<br />' .  $_SESSION['PO'.$identifier]->StatusComments . '<br />';
			$_SESSION['PO'.$identifier]->AllowPrintPO=3;
			$_SESSION['PO'.$identifier]->Status = 'Authorised';
		} else { // no authority to authorise this order
			if (DB_num_rows($AuthResult) ==0){
				$AuthMessage = _('Your authority to approve purchase orders in') . ' ' . $_SESSION['PO'.$identifier]->CurrCode . ' ' . _('has not yet been set up') . '<br />';
			} else {
				$AuthMessage = _('You can only authorise up to').' '.$_SESSION['PO'.$identifier]->CurrCode.' '.$AuthRow['authlevel'] .'.<br />';
			}

			prnMsg( _('You do not have permission to authorise this purchase order').'.<br />' .  _('This order is for').' '.
				$_SESSION['PO'.$identifier]->CurrCode . ' '. $_SESSION['PO'.$identifier]->Order_Value() .'. '.
				$AuthMessage .
				_('If you think this is a mistake please contact the systems administrator') . '<br />' .
				_('The order will be created with a status of pending and will require authorisation'), 'warn');

			$_SESSION['PO'.$identifier]->AllowPrintPO=0;
			$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . $UserDetails . '<br />' . $_SESSION['PO'.$identifier]->StatusComments . '<br />';
			$_SESSION['PO'.$identifier]->Status = 'Pending';
		}
	} else { //auto authorise is set to off
		$_SESSION['PO'.$identifier]->AllowPrintPO=3;
		$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . $UserDetails . ' - '.$_SESSION['PO'.$identifier]->StatusComments . '<br />';
		$_SESSION['PO'.$identifier]->Status = 'Pending';
	}
	$sql="SELECT orderno, supplierno, orddate FROM purchorders WHERE date_format(orddate,'%Y%m')='".substr($_SESSION['lastdate'],0,4).substr($_SESSION['lastdate'],5,2)."' AND allowprint=3 AND supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "' AND status='Completed'";
	$result=DB_query($sql);
	$row=DB_fetch_assoc($result);
	//prnMsg($sql);
	//if ($_SESSION['ExistingOrder']==0){
	if ($row['orderno']==0||empty($row)){ 
	
		$_SESSION['PO'.$identifier]->OrderNo =  GetNextTransNo(45, $db);
       //  var_dump($_SESSION['PO'.$identifier]->LineItems);
		/*Insert to purchase order header record */
		$sql = "INSERT INTO purchorders ( orderno,
										supplierno,
										comments,
										orddate,
										rate,
										initiator,
										requisitionno,
										intostocklocation,
										deladd1,
										deladd2,

										deladd3,
										deladd4,
										deladd5,
										deladd6,
										tel,
										suppdeladdress1,
										suppdeladdress2,
										suppdeladdress3,
										suppdeladdress4,
										suppdeladdress5,

										suppdeladdress6,
										suppliercontact,
										supptel,
										contact,
										version,
										revised,
										deliveryby,
										status,
										stat_comment,
										deliverydate,
										paymentterms,
										allowprint,
										taxcatid,
										taxrate	,
										ordtype)
						VALUES(	'" . $_SESSION['PO'.$identifier]->OrderNo . "',
								'" . $_SESSION['PO'.$identifier]->SupplierID . "',
								'" . $_SESSION['PO'.$identifier]->Comments . "',
								'" . Date('Y-m-d') . "',
								'" . $_SESSION['PO'.$identifier]->ExRate . "',
								'" . $_SESSION['PO'.$identifier]->Initiator . "',
								'" . $_SESSION['PO'.$identifier]->RequisitionNo . "',
								'" . $_SESSION['PO'.$identifier]->Location . "',
								'" . $_SESSION['PO'.$identifier]->DelAdd1 . "',
								'" . $_SESSION['PO'.$identifier]->DelAdd2 . "',
								'" . $_SESSION['PO'.$identifier]->DelAdd3 . "',
								'" . $_SESSION['PO'.$identifier]->DelAdd4 . "',
								'" . $_SESSION['PO'.$identifier]->DelAdd5 . "',
								'" . $_SESSION['PO'.$identifier]->DelAdd6 . "',
								'" . $_SESSION['PO'.$identifier]->Tel . "',
								'" . $_SESSION['PO'.$identifier]->SuppDelAdd1 . "',
								'" . $_SESSION['PO'.$identifier]->SuppDelAdd2 . "',
								'" . $_SESSION['PO'.$identifier]->SuppDelAdd3 . "',
								'" . $_SESSION['PO'.$identifier]->SuppDelAdd4 . "',
								'" . $_SESSION['PO'.$identifier]->SuppDelAdd5 . "',
								'" . $_SESSION['PO'.$identifier]->SuppDelAdd6 . "',
								'" . $_SESSION['PO'.$identifier]->SupplierContact . "',
								'" . $_SESSION['PO'.$identifier]->SuppTel. "',
								'" . $_SESSION['PO'.$identifier]->Contact . "',
								'1',
								'" . Date('Y-m-d') . "',
								'" . $_SESSION['PO'.$identifier]->DeliveryBy . "',
								'Completed',
								'" . htmlspecialchars($StatusComment,ENT_QUOTES,'UTF-8') . "',
								'" . FormatDateForSQL( $_POST['DefaultReceivedDate'] ) . "',
								'" . $_SESSION['PO'.$identifier]->PaymentTerms. "',
								'3',
								'" . $_SESSION['PO'.$identifier]->TaxCatID . "' ,
								'" . $_SESSION['PO'.$identifier]->TaxRate . "',
								'1'  )";

		$ErrMsg =  _('The purchase order header record could not be inserted into the database because');
		$DbgMsg = _('The SQL statement used to insert the purchase order header record and failed was');
		//prnMsg('1068'.$sql);
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		}else{
			$_SESSION['PO'.$identifier]->OrderNo =$row['orderno'] ; 
		}

		$GRN =$_SESSION['PO'.$identifier]->OrderNo;// GetNextTransNo(45, $db);
		 /*Insert the purchase order detail records */
		 $inrow=0;
		 $rowtol=0;
		foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
			if ($POLine->Deleted==False) {
				$rowtol++;
				$sql = "INSERT INTO purchorderdetails (orderno,
													itemcode,
													deliverydate,
													itemdescription,
													glcode,
													unitprice,
													quantityord,
													shiptref,
													jobref,
													suppliersunit,
													suppliers_partno,
													assetid,
													conversionfactor ,
													cess,
													taxprice)												
								VALUES ('" .$_SESSION['PO'.$identifier]->OrderNo . "',
										'" . $POLine->StockID . "',
										'" . FormatDateForSQL($_POST['DefaultReceivedDate']) . "',
										'" . DB_escape_string($POLine->ItemDescription) . "',
										'',
										'" . $POLine->Price . "',
										'" . $POLine->Quantity . "',
										'" . $POLine->ShiptRef . "',
										'" . $POLine->JobRef . "',
										'" . $POLine->SuppliersUnit . "',
										'" . $POLine->Suppliers_PartNo . "',
										'0',
										'" . $POLine->ConversionFactor . "',
										'" . (is_numeric($POLine->Cess)?$POLine->Cess:0) . "',
										'" . $POLine->TaxPrice . "'
										)";
				$ErrMsg =_('One of the purchase order detail records could not be inserted into the database because');
				$DbgMsg =_('The SQL statement used to insert the purchase order detail record and failed was');
				
				$result =DB_query($sql,$ErrMsg,$DbgMsg,true);
				if ($result){
					$inrow++;
				//prnMsg('1110'.$sql);

				}
			}
			if ($POLine->StockID!='') { //Its a stock item line
				/*Need to get the current standard cost as it is now so we can process GL jorunals later*/
				$SQL = "SELECT materialcost + labourcost + overheadcost as stdcost,mbflag
							FROM stockmaster
							WHERE stockid='" . $POLine->StockID . "'";
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The standard cost of the item being received cannot be retrieved because');
				$DbgMsg = _('The following SQL to retrieve the standard cost was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

				$myrow = DB_fetch_row($Result);
				if($myrow[1] != 'D') {
					if ($POLine->QtyReceived==0){ //its the first receipt against this line
						$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->StandardCost = $myrow[0];
					}
					$CurrentStandardCost = $myrow[0];
					/*Set the purchase order line stdcostunit = weighted average / standard cost used for all receipts of this line
						 This assures that the quantity received against the purchase order line multiplied by the weighted average of standard
						 costs received = the total of standard cost posted to GRN suspense*/
					$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->StandardCost = (($CurrentStandardCost *  $POLine->Quantity) + ($_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->StandardCost * $POLine->QtyReceived)) / ( $POLine->Quantity + $POLine->QtyReceived);
				} elseif ($myrow[1] == 'D') { //it's a dummy part which without stock.
					$Dummy = true;
					if($POLine->QtyReceived == 0){//There is
						$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->StandardCost = $LocalCurrencyPrice;
					}
				}

			} elseif ($POLine->QtyReceived==0 AND $POLine->StockID=='') {
				/*Its a nominal item being received */
				/*Need to record the value of the order per unit in the standard cost field to ensure GRN account entries clear */
				$_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->StandardCost = $LocalCurrencyPrice;
			}

			if ($POLine->StockID=='' OR !empty($Dummy)) { /*Its a NOMINAL item line */
				$CurrentStandardCost = $_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->StandardCost;
			}

			//Need to insert a GRN item //
	
			$SQL = "INSERT INTO grns (grnbatch,
									podetailitem,
									itemcode,
									itemdescription,
									deliverydate,
									qtyrecd,
									supplierid,
									stdcostunit,
									supplierref)
							VALUES ('" . $_SESSION['PO'.$identifier]->OrderNo . "',
								'" . $POLine->LineNo . "',
								'" . $POLine->StockID . "',
								'" . DB_escape_string($POLine->ItemDescription) . "',
								'" . $_POST['DefaultReceivedDate'] . "',
								'" . $POLine->Quantity . "',
								'" . $_SESSION['PO'.$identifier]->SupplierID . "',
								'" . $CurrentStandardCost . "',
								'" . trim($_POST['SupplierReference']) ."')";

			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('A GRN record could not be inserted') . '. ' . _('This receipt of goods has not been processed because');
			$DbgMsg =  _('The following SQL to insert the GRN record was used');
		
			$result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			if ($result){
				$inrow++;
				//prnMsg('1172'.$sql);
			}

			if ($POLine->StockID!=''){ // if the order line is in fact a stock item //	
			// Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts //		
			// Need to get the current location quantity will need it later for the stock movement //
				$SQL="SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $POLine->StockID . "'
								AND loccode= '" . $_POST['StockPurch'] . "'";

				$Result = DB_query($SQL);
				
				if (DB_num_rows($Result)==1){
					$LocQtyRow = DB_fetch_row($Result);
					$QtyOnHandPrior = $LocQtyRow[0];
				
					
					/*else {
										
						$OldQoh = $LocRow['newqoh'];
						$OldAmount = $LocRow['newamount'];
						$InvPrice=round(($LocRow['newamount']/ $LocRow['newqoh']),2);

					}*/
					
					$SQL = "UPDATE locstock
								SET quantity = locstock.quantity + '" .  $POLine->Quantity . "'
							WHERE locstock.stockid = '" . $POLine->StockID . "'
							AND loccode = '" . $_POST['StockPurch'] . "'";

					$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
					$DbgMsg =  _('The following SQL to update the location stock record was used');
					$result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					if ($result){
						$inrow++;
					
					}
				} else {
					//There must actually be some error this should never happen //
					$QtyOnHandPrior = 0;
					$SQL="INSERT INTO `locstock`(	`loccode`,
													`stockid`,
													`quantity`,
													`reorderlevel`,
													`bin`)
													VALUES(
														'".$_POST['StockPurch']."',
														'".$POLine->StockID."',
														'" .  $POLine->Quantity . "',
														0,
														0)";
												
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
						$DbgMsg =  _('The following SQL to update the location stock record was used');
						$result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						if ($result){
							$inrow++;
						
						}
				}
				$SQL="SELECT newqoh,
							newamount
						FROM  stockmoves
						WHERE  stockid='" . $POLine->StockID . "'
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
			}
				// Insert stock movements - with unit cost //	
				$SQL = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												price,
												prd,
												reference,
												qty,
												standardcost,
												newqoh,
												newamount,
												debtorno,
												connectid,
												narrative,
												itemsid)
									VALUES (
										'" . $POLine->StockID . "',
										45,
										'" . $_SESSION['PO'.$identifier]->OrderNo . "',
										'" . $_POST['StockPurch'] . "',
										'" . $_POST['DefaultReceivedDate'] . "',
										'" . $_SESSION['UserID'] . "',
										'" . $POLine->Price . "',
										'0',
										'" . $_SESSION['PO'.$identifier]->SupplierID . " (" . DB_escape_string($_SESSION['PO'.$identifier]->SupplierName) . ") - " .$_SESSION['PO'.$identifier]->OrderNo . "',
										'" .  $POLine->Quantity . "',
										'" . $_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->StandardCost . "',
										'" . ($QtyOnHandPrior +  $POLine->Quantity) . "',
										'" . ($OldAmount +  $POLine->Quantity*$POLine->Price ) . "',
										'" . $_SESSION['PO'.$identifier]->SupplierID . "',
										'" .$_SESSION['PO'.$identifier]->OrderNo . "',
										'" . $_SESSION['PO'.$identifier]->LineItems[$POLine->LineNo]->Remark . "',
										'" . $POLine->LineNo . "')";

				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the stock movement records was used');
				
				$result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				if ($result){
					$inrow++;		
				
				}
			
				$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
				
				if ($POLine->Controlled ==1){
					foreach($POLine->SerialItems as $Item){
						// we know that StockItems return an array of SerialItem (s)
						 //We need to add the StockSerialItem record and	 The StockSerialMoves as well //
						//need to test if the controlled item exists first already
							$SQL = "SELECT COUNT(*) FROM stockserialitems
									WHERE stockid='" . $POLine->StockID . "'
									AND loccode = '" . $_POST['StockPurch']  . "'
									AND serialno = '" . $Item->BundleRef . "'";
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a batch or lot stock item already exists because');
							$DbgMsg =  _('The following SQL to test for an already existing controlled but not serialised stock item was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							$AlreadyExistsRow = DB_fetch_row($Result);
							if (trim($Item->BundleRef) != ''){
								if ($AlreadyExistsRow[0]>0){
									if ($POLine->Serialised == 1) {
										$SQL = "UPDATE stockserialitems SET quantity = '" . $Item->BundleQty . "'";
									} else {
										$SQL = "UPDATE stockserialitems SET quantity = quantity + '" . $Item->BundleQty . "'";
									}
									$SQL .= "WHERE stockid='" . $POLine->StockID . "'
											 AND loccode = '" .  $POLine->LocCode  . "'
											 AND serialno = '" . $Item->BundleRef . "'";
								} else {
									$SQL = "INSERT INTO stockserialitems (stockid,
																			loccode,
																			serialno,
																			qualitytext,
																			expirationdate,
																			quantity)
																		VALUES ('" . $POLine->StockID . "',
																			'" .  $POLine->LocCode  . "',
																			'" . $Item->BundleRef . "',
																			'',
																			'" . FormatDateForSQL($Item->ExpiryDate) . "',
																			'" . $Item->BundleQty . "')";
								}

								$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
								$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
								$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

								// end of handle stockserialitems records //

							// now insert the serial stock movement //
							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
																	stockid,
																	serialno,
																	moveqty)
															VALUES (
																'" . $StkMoveNo . "',
																'" . $POLine->StockID . "',
																'" . $Item->BundleRef . "',
																'" . $Item->BundleQty . "'
																)";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							if ($_SESSION['QualityLogSamples']==1) {
								CreateQASample($POLine->StockID,$Item->BundleRef, '', 'Created from Purchase Order', 0, 0,$db);
							}
						}//non blank BundleRef
					} //end foreach
				}
				
			
			
			$PONo = $_SESSION['PO'.$identifier]->OrderNo;
			 
			
			//收货结束
			
			
			
		} /* end of the loop forearch*/
		//prnMsg($inrow.'='.$rowtol*5);
		if ($inrow==$rowtol*4){
			
				$result = DB_Txn_Commit();
			
			unset($_SESSION['PO'.$identifier]->LineItems);
			unset($_SESSION['PO'.$identifier]);
			//unset($_SESSION['PO' . $identifier]->SupplierID);
			//unset($_POST['ProcessGoodsReceived']);
			//echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title .':['. $_SESSION['PO' . $identifier]->SupplierID.']'.$_SESSION['PO' . $identifier]->SupplierName.'</p>';

			//echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" id="choosesupplier">';				 
			prnMsg('快捷收货单No' . $GRN . '录入成功！','success');
			if ( isset($_POST['Commit'])) {			
		    //   echo '<meta http-equiv="refresh" content="0"; url="' . $RootPath . '/PO_PurchReceive.php">';
				
				echo '</br><a href="' . $RootPath . '/PDFPurchOrder.php?F=Y&D=' . $GRN . '">打印发料单</a> </br>';
										
			}
			echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">返回简易收货</a></br>';

		}
		/*调试关闭
		unset($_SESSION['PO'.$identifier]); //Clear the PO data to allow a newy to be input
		*/
	//include('includes/footer.php');
	//exit;
//  } /*876 end if there were no input errors trapped */
} /*175row end of */




   //读取已经存在合同
    $sql="SELECT orderno, supplierno, comments, orddate, rate, dateprinted, allowprint,
	             initiator, requisitionno, intostocklocation, deladd1, deladd2, deladd3,
				  deladd4, deladd5, deladd6, tel,
	             suppdeladdress1, suppdeladdress2, suppdeladdress3, suppdeladdress4, suppdeladdress5,
				  suppdeladdress6, suppliercontact, supptel, contact, version, revised, realorderno,
				   deliveryby, deliverydate, status, stat_comment, paymentterms, port 
	         FROM purchorders
			 WHERE allowprint=3 
			 AND DATE_FORMAT(orddate,'%Y%m')='".$_SESSION['lastdate']."' ";
	$result=DB_query($sql);
	if ($result){
		$row=DB_fetch_assoc($result);
		$_SESSION['ExistingOrder'] = 1;
	}else{
		$_SESSION['ExistingOrder'] = 0;

	}
//if (!isset($_POST['SearchSuppliers'])&&  $_SESSION['PO' . $identifier]->SupplierID!=''){
	if(isset($_GET['Delete'])){
		if($_SESSION['PO'.$identifier]->Some_Already_Received($_GET['Delete'])==0){
			$_SESSION['PO'.$identifier]->remove_from_order($_GET['Delete']);
			include ('includes/PO_UnsetFormVbls.php');
		} else {
			prnMsg( _('This item cannot be deleted because some of it has already been received'),'warn');
		}
	}	

		$_SESSION['PO'.$identifier]->Total = 0;
		$k=0; //row colour counter
		$c=0;	
		$sql="SELECT stockcategory.categoryid ,
						categorydescription 
					FROM stockcategory		
					INNER JOIN locationusers ON locationusers.loccode =stockcategory.categoryid AND locationusers.userid = '".$_SESSION['UserID']."' AND locationusers.canupd = 1
					WHERE stocktype = 'B' 
					ORDER BY categorydescription";
$ErrMsg = _('The supplier category details could not be retrieved because');
$DbgMsg = _('The SQL used to retrieve the category details but failed was');
$result1 = DB_query($sql,$ErrMsg,$DbgMsg);		
//prnMsg($_POST['PO_ItemsResubmitFormValue'].')	AND'. $_SESSION['PO_ItemsResubmitForm' . $identifier].' =='. $_POST['PO_ItemsResubmitFormValue']);
if (isset($_POST['NewItem'])){
	//读取新物料
	foreach ($_POST as $FormVariableName =>$Quantity) {
	
		if (mb_substr($FormVariableName, 0, 6)=='NewQty' AND filter_number_format($Quantity)!=0) { //if the form variable represents a Qty to add to the order
			$n=mb_substr($FormVariableName, 6);
			$ItemCode = $_POST['StockID' .$n ];
			$NewPrice = $_POST['NewPrice' . $n];
			$LocCode = $_POST['LocCode' .$n ];
			$NewQty=$_POST['NewQty'.$n];
			$AlreadyOnThisOrder = 0;

			if ($_SESSION['PO_AllowSameItemMultipleTimes'] ==false){
				if (count($_SESSION['PO'.$identifier]->LineItems)!=0){

					foreach ($_SESSION['PO'.$identifier]->LineItems AS $OrderItem) {

					/* do a loop round the items on the order to see that the item is not already on this order */
						if (($OrderItem->StockID == $ItemCode) AND ($OrderItem->Deleted==false)) {
							$AlreadyOnThisOrder = 1;
							prnMsg( _('The item') . ' ' . $ItemCode . ' ' . _('is already on this order') . '. ' . _('The system will not allow the same item on the order more than once') . '. ' . _('However you can change the quantity ordered of the existing line if necessary'),'error');
						}
					} /* end of the foreach loop to look for preexisting items of the same code */
				}
			}
			//prnMsg($Quantity.'=$Quantity=$AlreadyOnThisOrder'.$AlreadyOnThisOrder);
			if ($AlreadyOnThisOrder!=1 AND filter_number_format($Quantity) > 0){
				$sql = "SELECT description,
							longdescription,
							stockid,
							units,
							decimalplaces,
							stockact,
							accountname,
							stockmaster.categoryid
						FROM stockmaster INNER JOIN stockcategory
						ON stockcategory.categoryid = stockmaster.categoryid
						INNER JOIN chartmaster
						ON chartmaster.accountcode = stockcategory.stockact
						WHERE  stockmaster.stockid = '". $ItemCode . "'";

				$ErrMsg = _('The item details for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
				$DbgMsg = _('The SQL used to retrieve the item details but failed was');
				//prnMsg($sql);
				$ItemResult = DB_query($sql,$ErrMsg,$DbgMsg);
				if (DB_num_rows($ItemResult)==1){
					$ItemRow = DB_fetch_array($ItemResult);
					

					$sql = "SELECT price,
								conversionfactor,
								supplierdescription,
								suppliersuom,
								suppliers_partno,
								leadtime,
								MAX(purchdata.effectivefrom) AS latesteffectivefrom
							FROM purchdata
							WHERE purchdata.supplierno = '" . $_SESSION['PO'.$identifier]->SupplierID . "'
							AND purchdata.effectivefrom <='" . Date('Y-m-d') . "'
							AND purchdata.stockid = '". $ItemCode . "'
							GROUP BY purchdata.price,
									purchdata.conversionfactor,
									purchdata.supplierdescription,
									purchdata.suppliersuom,
									purchdata.suppliers_partno,
									purchdata.leadtime
							ORDER BY latesteffectivefrom DESC";

					$ErrMsg = _('The purchasing data for') . ' ' . $ItemCode . ' ' . _('could not be retrieved because');
					$DbgMsg = _('The SQL used to retrieve the purchasing data but failed was');
					$PurchDataResult = DB_query($sql,$ErrMsg,$DbgMsg);
					if (DB_num_rows($PurchDataResult)>0){ //the purchasing data is set up
						$PurchRow = DB_fetch_array($PurchDataResult);

						/* Now to get the applicable discounts */
						$sql = "SELECT discountpercent,
										discountamount
								FROM supplierdiscounts
								WHERE supplierno= '" . $_SESSION['PO'.$identifier]->SupplierID . "'
								AND effectivefrom <='" . Date('Y-m-d') . "'
								AND effectiveto >='" . Date('Y-m-d') . "'
								AND stockid = '". $ItemCode . "'";

						$ItemDiscountPercent = 0;
						$ItemDiscountAmount = 0;
						$ErrMsg = _('Could not retrieve the supplier discounts applicable to the item');
						$DbgMsg = _('The SQL used to retrive the supplier discounts that failed was');
						$DiscountResult = DB_query($sql,$ErrMsg,$DbgMsg);
						while ($DiscountRow = DB_fetch_array($DiscountResult)) {
							$ItemDiscountPercent += $DiscountRow['discountpercent'];
							$ItemDiscountAmount += $DiscountRow['discountamount'];
						}
						if ($ItemDiscountPercent != 0) {
							prnMsg(_('Taken accumulated supplier percentage discounts of') .  ' ' . locale_number_format($ItemDiscountPercent*100,2) . '%','info');
						}
						if ($ItemDiscountAmount != 0 ){
							prnMsg(_('Taken accumulated round sum supplier discount of') .  ' ' . $_SESSION['PO'.$identifier]->CurrCode . ' ' . locale_number_format($ItemDiscountAmount,$_SESSION['PO'.$identifier]->CurrDecimalPlaces) . ' (' . _('per supplier unit') . ')','info');
						}
						$PurchPrice = ($PurchRow['price']*(1-$ItemDiscountPercent) - $ItemDiscountAmount)/$PurchRow['conversionfactor'];
						$ConversionFactor = $PurchRow['conversionfactor'];
						if (mb_strlen($PurchRow['supplierdescription'])>2){
							$SupplierDescription = $PurchRow['supplierdescription'];
						} else {
							$SupplierDescription = $ItemRow['description'];
						}
						$SuppliersUnitOfMeasure = $PurchRow['suppliersuom'];
						$SuppliersPartNo = $PurchRow['suppliers_partno'];
						$LeadTime = $PurchRow['leadtime'];
						/* Work out the delivery date based on today + lead time
					 * if > header DeliveryDate then set DeliveryDate to today + leadtime
				        */
						$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$LeadTime);
						if (Date1GreaterThanDate2($_SESSION['PO'.$identifier]->DeliveryDate,$DeliveryDate)){
							$DeliveryDate = $_SESSION['PO'.$identifier]->DeliveryDate;
						}
					} else { // no purchasing data setup
						$PurchPrice = 0;
						$ConversionFactor = 1;
						$SupplierDescription = 	$ItemRow['description'];
						$SuppliersUnitOfMeasure = $ItemRow['units'];
						$SuppliersPartNo = '';
						$LeadTime=1;
						$DeliveryDate = $_SESSION['PO'.$identifier]->DeliveryDate;
					}
				    //prnMsg($rate.'[r]'.$NewQty.'[=q'.$Quantity);
					$_SESSION['PO'.$identifier]->add_to_order ($_SESSION['PO'.$identifier]->LinesOnOrder+1,
															$ItemCode,
															0, /*Serialised */
															0, /*Controlled */
															$NewQty, /* Qty */
														    $SupplierDescription,
															$NewPrice,
															$ItemRow['units'],
															0,
															$_SESSION['PO' . $identifier]->TaxRate,
															$ItemRow['categoryid'],
															$DeliveryDate,
															0,
															0,
															0,															
															0,
															0,
															'',
															$ItemRow['decimalplaces'],
															$SuppliersUnitOfMeasure,
															$ConversionFactor,
															$LeadTime,
															$SuppliersPartNo,
														    '');
						//var_dump($_SESSION['PO'.$identifier]->LineItems);
				} else { //no rows returned by the SQL to get the item
					prnMsg (_('The item code') . ' ' . $ItemCode . ' ' . _('does not exist in the database and therefore cannot be added to the order'),'error');
					if ($debug==1){
						echo '<br />' . $sql;
					}
					include('includes/footer.php');
					exit;
				}
			} /* end of if not already on the order */
		} /* end if the $_POST has NewQty in the variable name */
	} /* end loop around the $_POST array */
	$_SESSION['PO_ItemsResubmitForm' . $identifier]++; //change the $_SESSION VALUE
	//var_dump($_SESSION['PO'.$identifier]->LineItems);
	//prnMsg($_SESSION['PO_ItemsResubmitForm' . $identifier]);
} /* end of if its a new item */

   $rw=count($_SESSION['PO'.$identifier]->LineItems);
  //755已录入数据显示收货明细
if ($rw>0){
	//var_dump($_SESSION['PO'.$identifier]);
	$_POST['DefaultReceivedDate']=date('Y-m-d');
	/*$AfterDate=date('Y-m-01',strtotime ($_SESSION['lastdate']));
	if( date('Y-m',strtotime($_POST['DefaultReceivedDate']))!=date('Y-m',strtotime($_SESSION['lastdate']))){
		if (date('Y-m')==date('Y-m',strtotime($_SESSION['lastdate']) )){
		$_POST['DefaultReceivedDate']=date('Y-m-d');

		}else{
			$_POST['DefaultReceivedDate']=date("Y-m-d",strtotime($_SESSION['lastdate']));
		}
	}*/
	/*	<input type="date" alt="'. $_SESSION['DefaultDateFormat'] .'"  min="'.$AfterDate.'" max="'.$_SESSION['lastdate'].'"  maxlength="10" size="10" onchange="return isDate(this, this.value, '."'".
					$_SESSION['DefaultDateFormat']."'".')" name="DefaultReceivedDate" value="' . $_POST['DefaultReceivedDate'] . '" /></td>*/
	echo '<table cellpadding="2" class="selection">
			<tr>
			<th colspan="11"><h3>收货明细</h3></th>
			</tr>
			<tr>
			<td colspan="2">选择收货仓库';
			DB_data_seek($result1,0);
	echo '<select name="StockPurch">';
			if (!isset($_POST['StockPurch'])) {
				$_POST['StockPurch'] ='';		}
		
			while ($myrow1 = DB_fetch_array($result1)) {
				if ($myrow1['categoryid'] == $_POST['StockPurch']) {
					echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
				} else {
					echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
				}
			}
			echo '</select></td>
			</td>
				<td colspan="8"  class="centre" >' .  _('Date Goods/Service Received'). ':
				<input type="text"  maxlength="10" size="10" name="DefaultReceivedDate" value="' . $_POST['DefaultReceivedDate'] . '" readonly="true" /></td>
			</tr>';
		echo '<tr>
			<th class="ascending">' . _('Item Code') . '</th>
			<th class="ascending">' . _('Description') . '</th>';
		echo'<th>单位</th>';
		echo'<th class="ascending">' . _('Order Quantity') .'</th>';
		echo'<th class="ascending">' . _('Order Price') . ' ('.$_SESSION['PO'.$identifier]->CurrCode.  ')</th>';
		echo'<th class="ascending">税目</th>
			<th class="ascending">不含税价格</th>
			<th class="ascending">税额</th>
			<th class="ascending">' . _('Sub-Total') .' ('.$_SESSION['PO'.$identifier]->CurrCode.  ')</th>
			<th class="ascending">备注</th>
			<th></th>
		
			</tr>';
		
	$_SESSION['PO'.$identifier]->Total = 0;
	$k = 0;  //row colour counter
	$TaxTotal=0;
	$TaxSql="SELECT `taxcatid`, `taxcatname`,  `taxrate` FROM `taxcategories` WHERE onorder=2 OR onorder=3 ";
	$TaxResult=DB_query($TaxSql);
	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
		//prnMsg($_POST['TaxCat'.$POLine->LineNo].'=='.$POLine->Deleted);
		if ($POLine->Deleted==False) {
			$LineTotal = $POLine->Quantity * $POLine->Price;
			$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
			
			$SuPrice =round($POLine->TaxPrice ,4);
			$Price=round($POLine->TaxPrice/(1+$_SESSION['PO'.$identifier]->TaxRate),2);
			$TaxAmo=round($LineTotal/(1+$_SESSION['PO'.$identifier]->TaxRate)*$_SESSION['PO'.$identifier]->TaxRate ,2);
		
			$TaxTotal+=$TaxAmo;
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			//locale_number_format(round($POLine->Quantity,$POLine->DecimalPlaces),$POLine->DecimalPlaces) 
			echo'<td>' . $POLine->StockID  . '
			<input type="hidden" name="StockID' . $POLine->LineNo . '" value="' . $POLine->StockID  . '"></td>
				<td>' . stripslashes($POLine->ItemDescription) . '
				<input type="hidden" name="ItemDescription' . $POLine->LineNo . '" value="' . stripslashes($POLine->ItemDescription) . '"></td>
			
				<td>' . $POLine->Units.'
				      <input type="hidden" name="UOM' . $POLine->LineNo . '" value="' . $POLine->UOM . '"></td>
				<td>
				       <input type="text"  id="SuppQty' . $POLine->LineNo .'"   name="SuppQty' . $POLine->LineNo .'" pattern="^(([1-9]\d*)|0)(\.\d{1-2})?$"　 onChange="inQTY(this,'.$POLine->DecimalPlaces .' ,'.$rw.' )"  size="5" value="' .locale_number_format( $POLine->Quantity,$POLine->DecimalPlaces). '" /></td>
				<td>
				       <input type="text"  id="SuPrice' . $POLine->LineNo . '" name="SuPrice' . $POLine->LineNo . '" pattern="^(([1-9]\d*)|0|\,)(\.\d{1-2})?$"　 onChange="inPrice(this,'.POI .','.$rw.' )" size="7" value="' .locale_number_format( $SuPrice ,$_SESSION['PO'.$identifier]->CurrDecimalPlaces).'" /></td>
						<input type="hidden" id="edit' . $POLine->LineNo . '" name="edit' . $POLine->LineNo . '" value="0">';
				
			echo'<td><select name="TaxCat' . $POLine->LineNo .'"  id="TaxCat' . $POLine->LineNo .'" disabled="disabled">';
				
				DB_data_seek($TaxResult,0);
				while($row=DB_fetch_array($TaxResult)){
					if ($_SESSION['PO'.$identifier]->TaxCatID==$row['taxcatid']) {
						echo '<option selected="selected" value="' .$row['taxcatid'].'^'.$row['taxrate'] . '">' . $row['taxcatname'] . '</option>';
					} else {
						echo '<option value="' . $row['taxcatid'].'^'.$row['taxrate'] . '">' . $row['taxcatname'] . '</option>';
					}
					
				}
				echo '</select></td>';								
				echo '<td><input type="text" class="number" id="Price' . $POLine->LineNo . '" name="Price' . $POLine->LineNo . '"  size="7" value="' .locale_number_format( $Price,$_SESSION['PO'.$identifier]->CurrDecimalPlaces) .'" readonly="readonly" /></td>
					  <td><input type="text" class="number" size="7" id="TaxAmo' . $POLine->LineNo . '"  name="TaxAmo' . $POLine->LineNo . '"   value="' . locale_number_format($TaxAmo ,$_SESSION['PO'.$identifier]->CurrDecimalPlaces) .'" readonly="readonly" /></td>
					  <td><input type="text"  id="Amount' . $POLine->LineNo . '"  name="Amount' . $POLine->LineNo . '"  size="10"  pattern="^(([1-9]\d*)|0|\,)(\.\d{1-2})?$"　  onChange="inAmount(this,'.$POLine->DecimalPlaces .','.$rw.' )"  value="' . locale_number_format($LineTotal,$_SESSION['PO'.$identifier]->CurrDecimalPlaces) .'" /></td>
					  <td><input type="text"  size="12" id="Remark' . $POLine->LineNo . '"  name="Remark' . $POLine->LineNo . '"   value="' . $_SESSION['PO'.$identifier]->Remark .'"  /></td>';
				//备注没有添加js
				if ($POLine->QtyReceived !=0 AND $POLine->Completed!=1){
					echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'&amp;Complete=' . $POLine->LineNo . '">' . _('Complete') . '</a></td>';
				} elseif ($POLine->QtyReceived ==0) {
					echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'&amp;Delete=' . $POLine->LineNo . '">' . _('Delete'). '</a></td>';
				}
			echo '</tr>';
			/*
			$SuPrice=	str_ireplace(",","",$_POST['SuPrice'.$POLine->LineNo]);
			$Price=	str_ireplace(",","",$_POST['Price'.$POLine->LineNo]);
			$SuppQty=	str_ireplace(",","",$_POST['SuppQty'.$POLine->LineNo]);
			$cess=round((($SuPrice-$Price)/$Price),2);
			$_SESSION['PO'.$identifier]->update_item(	$POLine->LineNo,
														$SuppQty,
														$Price,
														(is_numeric($cess)?$cess:'0'),	
														$SuPrice,
														$_POST['Remark'.$POLine->LineNo]);
			
			//$_SESSION['PO'.$identifier]->DeliveryDate=*/

			$_SESSION['PO'.$identifier]->Total += $LineTotal;
			$TaxAmoTotal+=$TaxAmo;
			//prnMsg($_POST['TaxCat'.$POLine->LineNo]);
		}
	}

	$DisplayTotal = locale_number_format($_SESSION['PO'.$identifier]->Total,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
	echo '<tr><td></td>
	        <td colspan="6" class="number">' . _('TOTAL')  . '</td>			
			<td><input type="text"  class="number"   id="TaxTotal" maxlength="10" size="7" value="'. locale_number_format($TaxAmoTotal,$_SESSION['PO'.$identifier]->CurrDecimalPlaces). '" readonly="readonly" /></td>
			<td><input type="text"  class="number"  id= "AmountTotal" maxlength="20" size="10" value="'. $DisplayTotal. '" readonly="readonly" /></td>
		
			<td></td>
			</tr>
			</table>';
	echo '<br />
			<div class="centre">
			<input type="submit" name="UpdateLines" value="' . _('Update Order Lines') . '" />';
	echo '&nbsp;<input type="submit" name="Commit" value="' . _('Process Order') . '" />	
	</div>';

} /*Only display the order line items if there are any !! */

if (!isset($_POST['SearchSuppliers'])&&  $_SESSION['PO' . $identifier]->SupplierID!=''){
	//读取物料开始----
		$j = 1;
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	if (DB_num_rows($SearchResult) <> 0) {
		DB_data_seek($SearchResult, 0);
	}
	while (($myrow = DB_fetch_array($SearchResult)) AND ($RowIndex <15)) {
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		if ($myrow['mbflag'] == 'D') {
			$qoh = _('N/A');
		} else {
			$qoh = locale_number_format($myrow['qoh'], $myrow['decimalplaces']);
		}
		if ($myrow['discontinued']==1){
			$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
		} else {
			$ItemStatus ='';
		}

		echo '<td>' . $ItemStatus . '</td>
			<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
			<td>' . $myrow['stockno'] . '</td>
			<td>' . $myrow['description'] . '</td>
			<td>'. $myrow['longdescription'] . '</td>
			<td class="number">' . $qoh . '</td>
			<td>' . $myrow['units'] . '</td>
			<td><input type="text" name="receive" size="10" maxlength="12" value="" /></td>
			</tr>';

		$RowIndex = $RowIndex + 1;
		//end of page full new headings if
	}
	//end of while loop
	echo '</table>     
		  <br />';
	//----已录入数据显示end
	if (!isset($_POST['SupplierReference'])) {
		$_POST['SupplierReference'] = '';
	} else {
		if (isset($_POST['SupplierReference']) AND mb_strlen(trim($_POST['SupplierReference']))>30) {
			prnMsg(_('The supplier\'s delivery note no should not be more than 30 characters'),'error');
		} else {
			$_SESSION['PO' . $identifier]->SupplierReference = $_POST['SupplierReference'];
		}
	}
	$SupplierReference = isset($_SESSION['PO' . $identifier]->SupplierReference)? $_SESSION['PO' . $identifier]->SupplierReference: $_POST['SupplierReference'];
	
		
		echo '<br /><table class="selection"><tr>';
		echo '<td>' . _('In Stock Category') . ';:';
		echo '<select name="StockCat">';
		//if (!isset($_POST['StockCat'])) {
		//	$_POST['StockCat'] ='';
		//}
		/*
		if ($_POST['StockCat'] == 'All') {
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
		} else {
			echo '<option value="All">' . _('All') . '</option>';
		}*/
		DB_data_seek($result1,0);
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
		echo '<div class="centre">
				<input type="submit" name="Search" value="' . _('Search Now') . '" />
				
				
		</div><br />';
}

if (isset($_POST['Search']) OR isset($_POST['Prev']) OR isset($_POST['Next'])){  /*ie seach for stock items */

		if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
		}
		if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat']=='All'){
		if ($_POST['SupplierItemsOnly']=='on'){
			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.categoryid
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN purchdata
					ON stockmaster.stockid=purchdata.stockid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					AND stockmaster.description " . LIKE . " '" . $SearchString ."'
					ORDER BY stockmaster.stockid ";
		} else { // not just supplier purchdata items

			$sql = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,	stockmaster.categoryid
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
				AND stockmaster.mbflag<>'K'
				AND stockmaster.mbflag<>'A'
				AND stockmaster.mbflag<>'G'
				AND stockmaster.discontinued<>1
				AND stockmaster.description " . LIKE . " '" . $SearchString ."'
				ORDER BY stockmaster.stockid ";
		}
		} else { //for a specific stock category
		if ($_POST['SupplierItemsOnly']=='on'){
			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN purchdata
					ON stockmaster.stockid=purchdata.stockid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					AND stockmaster.discontinued<>1
					AND stockmaster.description " . LIKE . " '". $SearchString ."'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
		} else {
			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,	stockmaster.categoryid
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.description " . LIKE . " '". $SearchString ."'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
		}
		}

		} elseif ($_POST['StockCode']){

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
		if ($_POST['SupplierItemsOnly']=='on'){
			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,	stockmaster.categoryid
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN purchdata
					ON stockmaster.stockid=purchdata.stockid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'G'
					AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					AND stockmaster.discontinued<>1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid ";
		} else {
			$sql = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,	stockmaster.categoryid
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
				AND stockmaster.mbflag<>'A'
				AND stockmaster.mbflag<>'K'
				AND stockmaster.mbflag<>'G'
				AND stockmaster.discontinued<>1
				AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
				ORDER BY stockmaster.stockid ";
		}
		} else { //for a specific stock category and LIKE stock code
		if ($_POST['SupplierItemsOnly']=='on'){
			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,	stockmaster.categoryid
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN purchdata
					ON stockmaster.stockid=purchdata.stockid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					and stockmaster.discontinued<>1
					AND stockmaster.stockid " . LIKE  . " '" . $_POST['StockCode'] . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
		} else {
			$sql = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,	stockmaster.categoryid
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
				AND stockmaster.mbflag<>'A'
				AND stockmaster.mbflag<>'K'
				AND stockmaster.mbflag<>'G'
				and stockmaster.discontinued<>1
				AND stockmaster.stockid " . LIKE  . " '" . $_POST['StockCode'] . "'
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid ";
		}
		}

		} else {
		if ($_POST['StockCat']=='All'){
		if (isset($_POST['SupplierItemsOnly'])){
			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.categoryid
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN purchdata
					ON stockmaster.stockid=purchdata.stockid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					AND stockmaster.discontinued<>1
					ORDER BY stockmaster.stockid ";
		} else {
			$sql = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,
						stockmaster.categoryid
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
				AND stockmaster.mbflag<>'A'
				AND stockmaster.mbflag<>'K'
				AND stockmaster.mbflag<>'G'
				AND stockmaster.discontinued<>1
				ORDER BY stockmaster.stockid ";
		}
		} else { // for a specific stock category
		if (isset($_POST['SupplierItemsOnly']) AND $_POST['SupplierItemsOnly']=='on'){
			$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.units,
							stockmaster.categoryid
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					INNER JOIN purchdata
					ON stockmaster.stockid=purchdata.stockid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					AND stockmaster.discontinued<>1
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
		} else {
			$sql = "SELECT stockmaster.stockid,
						stockmaster.description,
						stockmaster.units,	stockmaster.categoryid
				FROM stockmaster INNER JOIN stockcategory
				ON stockmaster.categoryid=stockcategory.categoryid
				WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
				AND stockmaster.mbflag<>'A'
				AND stockmaster.mbflag<>'K'
				AND stockmaster.mbflag<>'G'
				AND stockmaster.discontinued<>1
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid ";
		}
		}
		}

		$SQLCount = substr($sql,strpos($sql,   "FROM"));
		$SQLCount = substr($SQLCount,0, strpos($SQLCount,   "ORDER"));
		$SQLCount = 'SELECT COUNT(*) '.$SQLCount;
		$ErrMsg = _('Failed to retrieve result count');
		$DbgMsg = _('The SQL failed is ');
		$SearchResult = DB_query($SQLCount,$ErrMsg,$DbgMsg);
		$myrow=DB_fetch_array($SearchResult);
		DB_free_result($SearchResult);
		unset($SearchResult);
		$ListCount = $myrow[0];
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax'])-1;
		if ($ListPageMax < 0) {
			$ListPageMax = 0;
		}
		if (isset($_POST['Next'])) {
			$Offset = $_POST['currpage']+1;
		}
		if (isset($_POST['Prev'])) {
			$Offset = $_POST['currpage']-1;
		}
		if (!isset($Offset)) {
			$Offset = 0;
		}
		if($Offset < 0){
			$Offset = 0;
		}
		if($Offset > $ListPageMax) {
			$Offset = $ListPageMax;
		}

		$sql = $sql . "LIMIT " . $_SESSION['DisplayRecordsMax']." OFFSET " . strval($_SESSION['DisplayRecordsMax']*$Offset);
		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL statement that failed was');
		$SearchResult = DB_query($sql,$ErrMsg,$DbgMsg);

		if (DB_num_rows($SearchResult)==0 AND $debug==1){
			prnMsg( _('There are no products to display matching the criteria provided'),'warn');
		}
		if (DB_num_rows($SearchResult)==1){

			$myrow=DB_fetch_array($SearchResult);
			$_GET['NewItem'] = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}

} 
		
if (isset($SearchResult)) {
	$PageBar = '<tr><td><input type="hidden" name="currpage" value="'.$Offset.'">';
	if($Offset>0)
		$PageBar .= '<input type="submit" name="Prev" value="'._('Prev').'" />';
	else
		$PageBar .= '<input type="submit" name="Prev" value="'._('Prev').'" disabled="disabled"/>';
	$PageBar .= '</td><td style="text-align:center" colspan="3"><input type="submit" value="'._('Order some').'" name="NewItem"/></td><td>';
	if($Offset<$ListPageMax)
		$PageBar .= '<input type="submit" name="Next" value="'._('Next').'" />';
	else
		$PageBar .= '<input type="submit" name="Next" value="'._('Next').'" disabled="disabled"/>';
	$PageBar .= '</td></tr>';
	echo '<table cellpadding="1" class="selection">';
	echo $PageBar;
	$TableHeader = '<tr>
						<th class="ascending">' . _('Code')  . '</th>
						<th class="ascending">' . _('Description') . '</th>
						<th>图像</th>
						<th>' . _('Our Units') . '</th>									
						<th>收货数量</th>	
					</tr>';
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($SearchResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$SupportedImgExt = array('png','jpg','jpeg');
		$imagefile = reset((glob($_SESSION['part_pics_dir'] . '/' . $myrow['stockid'] . '.{' . implode(",", $SupportedImgExt) . '}', GLOB_BRACE)));
		if (extension_loaded('gd') && function_exists('gd_info') && file_exists ($imagefile) ) {
			$ImageSource = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
			'&amp;StockID='.urlencode($myrow['stockid']).
			'&amp;text='.
			'&amp;width=64'.
			'&amp;height=64'.
			'" alt="" />';
		} else if (file_exists ($imagefile)) {
			$ImageSource = '<img src="' . $imagefile . '" height="100" width="100" />';
		} else {
			$ImageSource = _('No Image');
		}

		/*Get conversion factor and supplier units if any */
		$sql =  "SELECT purchdata.conversionfactor,
						purchdata.suppliersuom
					FROM purchdata
					WHERE purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					AND purchdata.stockid='" . $myrow['stockid'] . "'";
		$ErrMsg = _('Could not retrieve the purchasing data for the item');
		$PurchDataResult = DB_query($sql,$ErrMsg);

		if (DB_num_rows($PurchDataResult)>0) {
			$PurchDataRow = DB_fetch_array($PurchDataResult);
			$OrderUnits=$PurchDataRow['suppliersuom'];
			$ConversionFactor = locale_number_format($PurchDataRow['conversionfactor'],'Variable');
		} else {
			$OrderUnits=$myrow['units'];
			$ConversionFactor =1;
		}
		echo '<td>' . $myrow['stockid']  . '</td>
			<input type="hidden" name="StockID' . $j .'" . value="' . $myrow['stockid'] . '" />
			<td>' . $myrow['description']  . '</td>
			<input type="hidden" name="description' . $j .'" . value="' . $myrow['description'] . '" />
			<td>' . $ImageSource . '</td>
			<td>' . $myrow['units']  . '</td>
				<input type="hidden" name="units' . $j .'" . value="' . $myrow['units'] . '" />		
				<input type="hidden" size="6" value="'. $myrow['price'] .'" name="NewPrice' . $j . '" /></td>
				<input type="hidden" value="'. $myrow['categoryid'] .'" name="LocCode' . $j . '" /></td>
			<td><input class="number" type="text" size="6" value="" name="NewQty' . $j . '" /></td>
			
			</tr>';
		$j++;
		$PartsDisplayed++;
	#end of page full new headings if
	}

	echo $PageBar;
	#end of while loop
	echo '</table>';
	echo '<input type="hidden" name="PO_ItemsResubmitFormValue" value="' . $_SESSION['PO_ItemsResubmitForm' . $identifier] . '" />';
	echo '<a name="end"></a><br />
	      <div class="centre">
			<input type="submit" name="NewItem" value="' . _('Order some') . '" /></div>';
}#1616end if SearchResults to show
	
			
//}//row496选择客户后执行的代码end      

//if(is_null( $_SESSION['PO' . $identifier]->SupplierID)){
//	if (!isset($_POST['SearchSuppliers'])||$_POST['SearchSuppliers']==''){
	//if (!isset($_POST['SearchSuppliers'])){//&& !isset($_GET['identifier'])){//isset($_SESSION['PO' . $identifier]->SupplierID)) {
		//if ($_SESSION['RequireSupplierSelection'] == 1 OR !isset($_SESSION['PO' . $identifier]->SupplierID) OR $_SESSION['PO' . $identifier]->SupplierID == '') {
		echo '<div>';
		if (isset($SuppliersReturned)) {
			echo '<input type="hidden" name="SuppliersReturned" value="' . $SuppliersReturned . '" />';
		}
		
echo '</div>
      </form>';
include('includes/footer.php');


?>
	
