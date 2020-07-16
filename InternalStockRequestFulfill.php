
<?php

/* $Id: InternalStockRequestFulfill.php  $*/
/*
 * @Author: chengjiang 
 * @Date: 2019-08-08 10:20:44 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-08-15 06:24:10
 */
include('includes/session.php');
$Title ='采购计划收货';// _('Fulfill Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'FulfilRequest';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
function inPrice(p,d){	
	var id=p.name.split("TaxPrice")[0];
	var it=p.name.split("TaxPrice")[1];			
	var vlqty = document.getElementById(id+"QTY"+it);

	var total=0;
	if (vlqty.value!=""){
		//数量不为空	
		total=parseFloat(vlqty.value)*parseFloat(p.value);
		document.getElementById(id+"Amount"+it).value=total.toFixed(2);	
	}	
}
function inQTY(p,d,f){
	var id=p.name.split("QTY")[0];
	var it=p.name.split("QTY")[1];	
	var vl = document.getElementById(id+"TaxPrice"+it);
	var qty=parseFloat(p.value).toFixed(d);
	var qtyconf=1;
	
	//计划数
	var planqty=parseFloat(document.getElementById(id+"RequestedQuantity"+it).value);
	//已经收货数
	var purchqty=parseFloat(document.getElementById(id+"QtyDeliveryed"+it).value);
	
	if (isNaN(purchqty))
	   purchqty=0;
	if (f>=0 ){//0限制超入  大于0超入比分比
	
		if (f==0){
			qtyconf=0;
		}else{
			qtyconf=(f/100);
		}
		if (((planqty*(1+qtyconf)).toFixed(d)-purchqty)<=qty){
			p.value=((planqty*(1+qtyconf)).toFixed(d)-purchqty).toFixed(d);
			alert("收货数大于允许收货的计划数!");
		}
	}     
		if (parseFloat(p.value)!=parseFloat(qty) ){
			p.value=qty;
			alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
		}
	
	var total=0;
	if (vl.value!=""){
		//数量不为空	
		document.getElementById(id+"SelectPurch"+it).checked=true;
		total=parseFloat(vl.value)*parseFloat(p.value);
		document.getElementById(id+"Amount"+it).value=total.toFixed(2);	
	}		
	
}
function inAmount(p,d){
	var id=p.name.split("Amount")[0];
	var it=p.name.split("Amount")[1];	
	var vlqty = document.getElementById(id+"QTY"+it);
	
	var qty=(1*vlqty.value).toFixed(d);
	if (qty==0){
		alert("请输入数量,然后计算价格,默认"+d+"位!");

	}else if (vlqty.value!=qty){
		document.getElementById(id+"QTY"+it).value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}	
	
	if (vlqty.value!=""){
		//数量不为空		
		vlprice=(parseFloat(p.value)/parseFloat(qty)).toFixed(2);
		document.getElementById(id+"TaxPrice"+it).value=vlprice;		
	}	
}
function inSelect(v, tA, m) {
	//alert(v.value.split(":")[0]);
	var n=0;
	for(i=0;i<tA.length;i++) {
		n=n+1;
		if(v.value.split(":")[0]==tA[i].value.split(":")[0]) {
			document.getElementById("Supplier").value=tA[i].value.split(":")[2];
			document.getElementById("SuppID").value=v.value.split(":")[0];						
			return true;
		}
	}
	alert(m);
	document.getElementById("SuppID").value="";
	return false;
}
function InSelectCheck(o,r) {
	var inputs = document.getElementsByTagName("input");//获取所有的input标签对象
	var checkboxArray = [];//初始化空数组，用来存放checkbox对象。
	for(var i=0;i<inputs.length;i++){
		var obj = inputs[i];
		if(obj.type=="checkbox" && !obj.disabled){
				
			if( obj.name.indexOf("SelectPurch")>0){
				if (o.checked){
					obj.checked=true; 
				}else{
					obj.checked=false; 
				}
			}
			// checkboxArray.push(obj.value);
		}
	}
	
	
}				
</script>';

if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Contract') . '" alt="" />' .$Title . '</p>';

if (!isset($_POST['DispatchDate'])){
	$_POST['DispatchDate'] =date("Y-m-d",strtotime("last month"));
}
if (!isset( $_POST['Purchasing'])){
		
	$_POST['Purchasing']=1;	
	
}
    //读取收货规则
	$SQL="SELECT `confvalue` FROM `myconfig` WHERE confname='InternalStock'";
	$ConfResult = DB_query($SQL);
	$row=DB_fetch_assoc($ConfResult);
	$Conf=json_decode($row['confvalue'],true);

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden"  name="Location" value=' . $_POST['Location'] . ' />
			<input type="hidden"  name="Location" value=' . $_POST['Department'] . ' />
			<input type="hidden"  name="DispatchDate" value=' . $_POST['DispatchDate'] . ' />';
	$SQL = "SELECT supplierid,
					custname,
					currcode
				FROM suppliers
				LEFT JOIN custname_reg_sub ON custname_reg_sub.regid=supplierid
				INNER JOIN customerusers ON customerusers.regid=supplierid  
				WHERE  suppliers.used>=0 AND (custype =2 OR custype =3)
						AND customerusers.userid='".$_SESSION['UserID']."'
				ORDER BY custname";
	$SupplierNameResult = DB_query($SQL);	
	
echo '<table class="selection">';
echo '<tr>
		<th colspan="2"><h4>采购计划单明细</h4></th>
	</tr>';
echo'<tr>
	<td>查询日期:</td>';
echo '<td><input type="date"  alt="'.$_SESSION['DefaultDateFormat'].'" name="DispatchDate" maxlength="10" size="11" value="' . $_POST['DispatchDate']  . '" /></td>
  </tr>';

	echo'<tr>
		<td>' . _('Department') . ':</td>';
if($_SESSION['AllowedDepartment'] == 0){
	// any internal department allowed
	$sql="SELECT departmentid,
				description
			FROM departments
			ORDER BY description";
}else{
	// just 1 internal department allowed
	$sql="SELECT departmentid,
				description
			FROM departments
			WHERE departmentid = '". $_SESSION['AllowedDepartment'] ."'
			ORDER BY description";
}
$result=DB_query($sql);
echo '<td><select name="Department">';
echo'<option value="">' . _('All') . '</option>';
while ($myrow=DB_fetch_array($result)){
	if (isset($_SESSION['Request']->Department) AND $_SESSION['Request']->Department==$myrow['departmentid']){
		echo '<option selected="True" value="' . $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
	} else {
		echo '<option value="' . $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
	}
}
echo '</select></td>
	</tr>
	<tr>
		<td>选择仓库:</td>
		<td><select name="Location">';
		$sql = "SELECT locations.loccode, locationname
				FROM locations
				INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
				WHERE internalrequest = 1
				ORDER BY locationname";
		$resultStkLocs = DB_query($sql);
		echo'<option value="">' . _('All') . '</option>';
		while ($myrow=DB_fetch_array($resultStkLocs)){
		
			if ($myrow['loccode']==$_POST['Location']){//==$_SESSION['UserStockLocation']){
				echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
				$_POST['StockLocation']=$myrow['loccode'];
			} else {
			 echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			}
		}
		echo '</select></td></tr>';

 echo'<tr><td>选择类别</td>
	  <td><input type="radio" name="Purchasing" value="1" '. ($_POST['Purchasing']==1 ?"checked":"").' />待收货       
		  <input type="radio" name="Purchasing" value="2" '. ($_POST['Purchasing']==2 ?"checked":"").' />待批准
		  <input type="radio" name="Purchasing" value="3" '. ($_POST['Purchasing']==3 ?"checked":"").' />已完成
      </td></tr>';
echo'</table><br />';
	echo '<div class="centre">	
			<input type="submit" name="EnterAdjustment" value="'. _('Show Requests'). '" />';
	echo '&nbsp&nbsp<input type="submit" name="UpdateAll" value="' . _('Update'). '" /></div>';

//if (isset($_POST['EnterAdjustment'])) {
	//prnMsg($_POST['DispatchDate']);
	
	//WHERE stockrequest.loccode='".$_POST['Location']."'";
	// stockrequest.authorised=1		AND stockrequest.closed=0
	$sql="SELECT stockrequest.dispatchid,
				locations.locationname,
				stockrequest.despatchdate,
				stockrequest.deliverydate,
				stockrequest.narrative,
				departments.description departname,			
				stockrequestitems.stockid,
				stockrequestitems.dispatchitemsid,
				stockrequestitems.stockid,
				stockrequestitems.cess,
				stockrequestitems.remark,
				stockmaster.categoryid loccode,
				stockrequestitems.completed,				
				stockrequestitems.qtydelivered,
				stockrequestitems.taxprice,
				stockmaster.decimalplaces,
				stockrequestitems.uom,
				stockmaster.description,
				stockrequestitems.quantity,
				stockrequest.authorised,
				stockrequest.closed
			FROM stockrequest INNER JOIN departments	ON stockrequest.departmentid=departments.departmentid
			LEFT JOIN stockrequestitems ON stockrequestitems.dispatchid=stockrequest.dispatchid
			INNER JOIN stockmaster		ON stockmaster.stockid=stockrequestitems.stockid
			INNER JOIN locations ON locations.loccode=stockmaster.categoryid
			INNER JOIN locationusers ON locationusers.loccode=	stockmaster.categoryid AND  locationusers.userid='" .  $_SESSION['UserID'] . "'  AND locationusers.canupd=1";
					//INNER JOIN www_users  ON www_users.userid=departments.authoriser
					//	www_users.realname,	www_users.email,
			$wh=0;
			if ($_POST['Department']=="" ){
			
					$sql.=" WHERE 1 ";
			}else{
			
				$sql.="WHERE stockrequest.departmentid='".$_POST['Department']."'";
			
			}
			if ($_POST['Location']!="" ){
			
				$sql.=" AND stockrequest.loccode='".$_POST['Location']."'";
				
			}
			if ($_POST['DispatchDate']!="" ){
				$sql.=" AND stockrequest.despatchdate >='".$_POST['DispatchDate']."'";
			
			}
			if ($_POST['Purchasing']==1){
				//待收货AND  stockrequest.authorised=1 AND stockrequestitems.completed=0
					$sql.=" AND  stockrequestitems.completed=1 ";
		
			}
			if ($_POST['Purchasing']==3){
				//已经完成AND  stockrequest.authorised=1 AND ( stockrequestitems.completed=2 OR  stockrequestitems.completed=1) ";
					$sql.=" AND   stockrequestitems.completed=2 ";
			}
			if ($_POST['Purchasing']==2){
				//待批准AND stockrequest.authorised<>1 AND  stockrequestitems.completed=0";
					$sql.=" AND  stockrequestitems.completed<1";
			}	
			
		  $sql.=" ORDER BY stockrequest.dispatchid";//	WHERE  www_users.userid='".$_SESSION['UserID']."'
		 
		$result=DB_query($sql);
		$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}


	if (DB_num_rows($result)==0) {
		prnMsg( _('There are no outstanding authorised requests for this location'), 'info');
		echo '<br />';
		echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select another location') . '</a></div>';
		include('includes/footer.php');
		exit;
	}
if (isset($_POST['EnterAdjustment'])|| isset($_POST['Go'])	OR isset($_POST['Next'])OR isset($_POST['Previous'])) {
	$FIRST=1;
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
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
	echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
	if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
		echo '<select name="PageOffset1">';
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
			<input type="submit" name="Go1" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}
	echo '<table class="selection">';
	echo'<tr>
			<th colspan="2">选择供应商</td>
			<th colspan="4">
				<input type="text" name="SuppName"  id="SuppName" placeholder="输入编码、名称关键词筛选，然后选择供应商"  autocomplete="off"  list="SuppCode"   maxlength="50" size="50"  onChange="inSelect(this, SuppCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
			<datalist id="SuppCode"> ';
		$n=1;
		while ($row=DB_fetch_array($SupplierNameResult )){
			echo '<option value="' . $row['supplierid'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['custname'], ENT_QUOTES,'UTF-8', false) . '"label=' .  $n. '>';
			$n++;
		}

		echo'</datalist></th>
		<th colspan="10">
		<input type="hidden" name="Supplier" id="Supplier" value="' . $_POST['Supplier'] . '" />
		<input type="hidden" name="SuppID" id="SuppID" value="' . $_POST['SuppID'] . '" /></td>
		</tr>';
		echo'<style type="text/css">	      
				.lengthcss{			
						overflow:hidden;
						text-overflow:ellipsis;
						display:-webkit-box;
						-webkit-box-orient:vertical;
						-webkit-line-clamp:2;				
						}
						</style>';
		echo'<tr>
				<th>采购单<br>编号</th>			
				<th>' . _('Requested Date') . '</th>
				<th>' . _('Department') . '</th>
				<th width="200px;">' . _('Narrative') . '</th>		
				<th>' . _('Location Of Stock') . '</th>				
				<th>' . _('Product') . '</th>
				<th>备注</th>
				<th>' . _('Units') . '</th>
				<th>采购<br>' . _('Quantity') .'</th>				
				<th>'. _('Delivered') .'<br />' . _('Quantity') .  '</th>
				<th>收货<br>数量</th>
				<th>完成</th>
				<th>税目</th>
				<th>采购<br>' . _('Price') . '</th>
				<th>金额</th>
				<th>选择</br><input type="checkbox" name="SelectCheck" value="1"  onclick="InSelectCheck(this,2)" /></th>
				
			</tr>';
		$disp=0;
		$k=0;
		$RowIndex = 0;
	if($ListCount <> 0) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	
		while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		
			$htmlstr=' ';
			if($myrow['closed']==1){
			//if($myrow['authorised']==0 || $myrow['closed']==1){
				$htmlstr="disabled";
			}else{
				//if($myrow['completed']==1 ||$myrow['completed']==2){
				if($myrow['completed']==2){
					$htmlstr="disabled";
				}else{
					$htmlstr='  check ';
				}
			}			
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			//prnMsg($_POST['Purchasing']);
			if ($disp!=$myrow['dispatchid'] ){
				$disp=$myrow['dispatchid'];		
				//echo '<td>' . $myrow['dispatchid'] . '</td>
				echo '	<td><a href="' . $RootPath . '/PurchOrder.php?F=P&D=' . $myrow['dispatchid'] . '"  target="_blank">'.$myrow['dispatchid'].'</a></td>';
	
				echo'<td>' . ConvertSQLDate($myrow['despatchdate']) . '</td>
				<td>' . $myrow['departname'] . '</td>
				<td class="lengthcss"   title="'. $myrow['narrative'].'">'. $myrow['narrative'] . '</td>';				 
			}else{
				
				echo '<td colspan="4"></td>';	
			}
			
			echo '<input type="hidden"  name="'. $myrow['dispatchid'] . 'StockID'. $myrow['dispatchitemsid'] . '" value="'.$myrow['stockid'].'" />';
			echo '<input type="hidden"  name="'. $myrow['dispatchid'] . 'Location'. $myrow['dispatchitemsid'] . '" value="'.$myrow['loccode'].'" />';
			echo '<input type="hidden"  name="'. $myrow['dispatchid'] . 'Cess'. $myrow['dispatchitemsid'] . '" value="'.$myrow['cess'].'" />';
			echo '<input type="hidden"  name="'. $myrow['dispatchid'] . 'QtyDeliveryed'. $myrow['dispatchitemsid'] . '" id="'. $myrow['dispatchid'] . 'QtyDeliveryed'. $myrow['dispatchitemsid'] . '" value="'.locale_number_format($myrow['qtydelivered'],$myrow['decimalplaces']).'" />';
			echo '<input type="hidden"  name="'. $myrow['dispatchid'] . 'RequestedQuantity'. $myrow['dispatchitemsid'] . '" id="'. $myrow['dispatchid'] . 'RequestedQuantity'. $myrow['dispatchitemsid'] . '" value="'.$myrow['quantity'].'" />';
			$qtydeliv=0;
			if ($myrow['quantity']-$myrow['qtydelivered']>0){			
				$qtydeliv=$myrow['quantity']-$myrow['qtydelivered'];
			}
			if ($myrow['decimalplaces']>=2){
				$dcm=2;
			}else{
				$dcm=$myrow['decimalplaces'];
			}
			echo'<td>' . $myrow['locationname'] . '</td>
			<td>' .$myrow['stockid'].":". $myrow['description'] . '</td>
				<td>'. $myrow['remark'] . '
				<input type="hidden"  name="'. $myrow['dispatchid'] . 'Remark'. $myrow['dispatchitemsid'] . '"   value="'.$myrow['remark'].'" /></td>
				<td>' . $myrow['uom'] . '</td>
				
				<td class="number">' . locale_number_format($myrow['quantity'],$dcm) . '</td>
				<td class="number">'.locale_number_format($myrow['qtydelivered'],$dcm).'</td>';
			    
			echo'<td class="number"><input type="text" class="number" name="'. $myrow['dispatchid'] . 'QTY'. $myrow['dispatchitemsid'] . '" id="'. $myrow['dispatchid'] . 'QTY'. $myrow['dispatchitemsid'] . '" size="5" maxlength="5" onChange="inQTY(this,'.$myrow['decimalplaces'] .','.$Conf['quantity'].'  )"  value="'.locale_number_format($qtydeliv,$dcm).'" /></td>
				<td><input type="checkbox" id="'. $myrow['dispatchid'] . 'Completed'. $myrow['dispatchitemsid'] .'" name="'. $myrow['dispatchid'] . 'Completed'. $myrow['dispatchitemsid'] .'"   value="2"    /></td>
				<td >'.round(100*$myrow['cess'],2).'%</td>			
				<td class="number"><input type="text" class="number" name="'. $myrow['dispatchid'] . 'TaxPrice'. $myrow['dispatchitemsid'] . '" id="'. $myrow['dispatchid'] . 'TaxPrice'. $myrow['dispatchitemsid'] . '" title="计划单批准价格:'.$myrow['taxprice'].'" size="5"  onChange="inPrice(this,'.$POLine->DecimalPlaces .')"  value="'.locale_number_format($myrow['taxprice'],2).'" /></td>
				<td class="number"><input type="text" class="number" name="'. $myrow['dispatchid'] . 'Amount'. $myrow['dispatchitemsid'] . '"  id="'. $myrow['dispatchid'] . 'Amount'. $myrow['dispatchitemsid'] . '"  size="7" onChange="inAmount(this,'.$myrow['decimalplaces'] .' )"   value="'.locale_number_format($myrow['taxprice']*($qtydeliv),2).'" /></td>';
		   echo'<td><input type="checkbox" id="'. $myrow['dispatchid'] . 'SelectPurch'. $myrow['dispatchitemsid'] .'" name="'. $myrow['dispatchid'] . 'SelectPurch'. $myrow['dispatchitemsid'] .'"   value="'. $myrow['dispatchid'] . 'SelectPurch'. $myrow['dispatchitemsid'] .'"  '.$htmlstr.'  /></td>
			
				</tr>';
				$RowIndex = $RowIndex + 1;
		}	
		echo '</table>';
	}
	if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
		echo '<select name="PageOffset2">';
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
				<input type="submit" name="Go2" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';
			echo '</div>';
	}
		echo '<input type="submit" name="UpdateAll" value="' . _('Update'). '" />';
}

if (isset($_POST['UpdateAll'])) {
	//prnMsg($_POST['custname'].'='.$_POST['SuppCode']);

	$InsertRow=0;
	$AdjustmentNumber = GetNextTransNo(17,$db);
	$SQLAdjustmentDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']." h:i:s"));
	$Result = DB_Txn_Begin();	
	$SelectRow=0;
	$CloseRequest=0;
	foreach ($_POST as $key => $value) {
	
		if (mb_strpos($key,'QTY')) {
			$complt=1;
			$RequestID = mb_substr($key,0, mb_strpos($key,'QTY'));
			$LineID = mb_substr($key,mb_strpos($key,'QTY')+3);
			if ($_POST[$RequestID.'SelectPurch'.$LineID]!="") {
				//已经收货数量
				$QtyDeliveryed =(double) filter_number_format($_POST[$RequestID.'QtyDeliveryed'.$LineID]);
				$TaxPrice = filter_number_format($_POST[$RequestID.'TaxPrice'.$LineID]);
				//本次收货数量			
				$Qty =filter_number_format($_POST[$RequestID.'QTY'.$LineID]);	//$Qty =str_replace(',','', $_POST[$RequestID.'QTY'.$LineID]);

				$Cess = $_POST[$RequestID.'Cess'.$LineID];
				$StockID = $_POST[$RequestID.'StockID'.$LineID];
				$Location = $_POST[$RequestID.'Location'.$LineID];
				$Amount = filter_number_format($_POST[$RequestID.'Amount'.$LineID]);
				$Remark = $_POST[$RequestID.'Remark'.$LineID];
				//prnMsg((double)$QtyDeliveryed .'['.$LineID.']'.$key.'['.$Qty.'='.$_POST[$RequestID.'QTY'.$LineID]);		
				if($Qty==0){//无收货关闭订单
				
						if ($QtyDeliveryed ==0){
							$complt=3;
						}else{
							$complt=2;
						}
						$SQL="UPDATE stockrequestitems
								SET completed=".$complt."
								WHERE dispatchid='" . $RequestID . "' 
									AND dispatchitemsid='" . $LineID . "'";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
						$DbgMsg = _('The following SQL to update the stock record was used');
					
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg,true);
						if ($Result){
							$CloseRequest++;
						}
						//prnMsg($SQL.'='.$CloseRequest);
						if (isset($RequestID)) {
							//查询订单收货情况
							$SQL="SELECT SUM(1) cutall,
										SUM(CASE WHEN completed=2 OR completed=3 THEN 1 ELSE 0 END ) cmp 
									FROM  stockrequestitems
									WHERE dispatchid='".$RequestID."'";
							
							$Result=DB_query($SQL);

							$row=DB_fetch_assoc($Result);
							if ($row['cutall']==$row['cmp']) {
								$SQL="UPDATE stockrequest
										SET closed=1,
										deliverydate='".date("Y-m-d")."'
								WHERE dispatchid='".$RequestID."'";
								$Result=DB_query($SQL);
							}
						}
					

				}else {
					# 大于0 或小于0
				
					if ($_POST['SuppID']!=""){
						if ($_POST[$RequestID.'SelectPurch'.$LineID]!="") {
							//选择框
							$Completed=True;
							$SelectRow++;				
						} else {
							$Completed=False;
									
						}
					}else{
						$Completed=False;
						prnMsg('您没有选择供应商,无法收货！','warn');
						break;
					}
				
					if ($Completed ){
						$complt=$_POST[$RequestID.'Completed'.$LineID]; 
							// filter_number_format($_POST[$RequestID.'RequestedQuantity'.$LineID]);
						//取得申请数量
						$RequestedQuantity = $_POST[$RequestID.'RequestedQuantity'.$LineID];
						$sql="SELECT materialcost, labourcost, overheadcost, decimalplaces FROM stockmaster WHERE stockid='".$StockID."'";
						$result=DB_query($sql);
						$myrow=DB_fetch_array($result);
						$StandardCost=$myrow['materialcost']+$myrow['labourcost']+$myrow['overheadcost'];
						$DecimalPlaces = $myrow['decimalplaces'];

						$Narrative = _('Issue') . ' ' . $RequestedQuantity . ' ' . _('of') . ' '. $StockID . ' ' . _('to department') . ' ' . $Department . ' ' . _('from') . ' ' . $Location ;
						//$PeriodNo = GetPeriod (Date($_SESSION['DefaultDateFormat']), $db);
						// Need to get the current location quantity will need it later for the stock movement
						$SQL="SELECT locstock.quantity
								FROM locstock
								WHERE locstock.stockid='" . $StockID . "'
									AND loccode= '" . $Location . "'";
						$Result = DB_query($SQL);
						if (DB_num_rows($Result)==1){
							$LocQtyRow = DB_fetch_row($Result);
							$QtyOnHandPrior = $LocQtyRow[0];
						} else {
							// There must actually be some error this should never happen
							$QtyOnHandPrior = 0;
						}
						$SQL="SELECT newqoh,
									newamount
								FROM  stockmoves
								WHERE  stockid='" . $StockID . "'
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
							
						if ($_SESSION['ProhibitNegativeStock']==0 OR ($_SESSION['ProhibitNegativeStock']==1 AND $QtyOnHandPrior >= $RequestedQuantity)) {
							//	prnMsg($_SESSION['ProhibitNegativeStock'].'==0 OR'. $_SESSION['ProhibitNegativeStock'].'==1 AND '.$QtyOnHandPrior.' >= '.$RequestedQuantity);	
							$dp=strlen((int)$Qty);
							$SQL = "INSERT INTO stockmoves (stockid,
															type,
															transno,
															loccode,
															trandate,
															userid,
															prd,
															reference,
															qty,
															price,
															newqoh,
															newamount,
															debtorno,
															connectid,
															itemsid,
															show_on_inv_crds,
															standardcost,
															narrative)
														VALUES ('" . $StockID . "',
																	17,
																	'" . $AdjustmentNumber . "',
																	'" . $Location . "',
																	'" . $SQLAdjustmentDate . "',
																	'" . $_SESSION['UserID'] . "',
																	'0',
																	'" . $Narrative ."',
																	'" . filter_number_format($Qty) . "',
																	'".round($TaxPrice,$dp)."',
																	'" . ($QtyOnHandPrior + filter_number_format($Qty)) . "',
																	'" . ($OldAmount +  filter_number_format($Qty)*($TaxPrice/(1+$Cess)) ) . "',
																	'".explode(':',$_POST['SuppName'])[0]."',
																	'".$RequestID."',
																	'".$LineID."',
																	'".$LineID."',
																	'".round($TaxPrice/(1+$Cess),$dp)."',
																	'".$Remark."')";

							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock movement record cannot be inserted because');
							$DbgMsg =  _('The following SQL to insert the stock movement record was used');
							
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							if ($Result){
								$InsertRow++;
							}

							//Get the ID of the StockMove... 
							$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
							//是否全收完
							
							if ($Conf['quantity']>=0){//有规则限制
								if ((round($RequestedQuantity*(1+$Conf['quantity']/100),$myrow['decimalplaces'])>=($Qty+$QtyDeliveryed) && round($RequestedQuantity*(1-$Conf['quantity']/100),$myrow['decimalplaces'])<=($QtyDeliveryed+ $Qty))||($RequestedQuantity<=$QtyDeliveryed+ $Qty)) {
									$complt=2;
								}
							}elseif ($Conf['quantity']<0){ //可以超入库,接近90%直接关闭
								if ((round($RequestedQuantity*(1-$Conf['quantity']/100),$myrow['decimalplaces'])>= ($Qty+$QtyDeliveryed) && round($RequestedQuantity*(1+$Conf['quantity']/100),$myrow['decimalplaces'])<=($QtyDeliveryed+ $Qty))||($RequestedQuantity<=$QtyDeliveryed+ $Qty))  {
									$complt=2;
								}
							}
						
							$SQL="UPDATE stockrequestitems
									SET qtydelivered=qtydelivered+" . filter_number_format($Qty) . ",
										completed=".$complt."
									WHERE dispatchid='" . $RequestID . "' 
										AND dispatchitemsid='" . $LineID . "'";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
							$DbgMsg = _('The following SQL to update the stock record was used');
					
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg,true);

							$SQL = "UPDATE locstock SET quantity = quantity + '" . filter_number_format($Qty) . "'
												WHERE stockid='" . $StockID . "'
													AND loccode='" . $Location . "'";

							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('The location stock record could not be updated because');
							$DbgMsg = _('The following SQL to update the stock record was used');
						
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);						
								// Check if request can be closed and close if done.
							if (isset($RequestID)) {
								//查询订单收货情况
								$SQL="SELECT SUM(1) cutall,
											SUM(CASE WHEN completed=2 THEN 1 ELSE 0 END ) cmp 
										FROM  stockrequestitems
										WHERE dispatchid='".$RequestID."'";
								$Result=DB_query($SQL);
								$row=DB_fetch_assoc($Result);
								if ($row['cutall']==$row['cmp']) {
									$SQL="UPDATE stockrequest
											SET closed=1,
											deliverydate='".date("Y-m-d")."'
									WHERE dispatchid='".$RequestID."'";
									$Result=DB_query($SQL);
								}
							}
						
						} else {
							$ConfirmationText = _('An internal stock request for'). ' ' . $StockID . ' ' . _('has been fulfilled from location').' ' . $Location .' '. _('for a quantity of') . ' ' . locale_number_format($RequestedQuantity, $DecimalPlaces) . ' ' . _('cannot be created as there is insufficient stock and your system is configured to not allow negative stocks');
							prnMsg( $ConfirmationText,'warn');
						}
					}       
				
				}//if end
			}
		}
	}//foreach end
		if ($SelectRow==0 && $CloseRequest==0 ){
			prnMsg('你没有选择物料!','info');
		}

		if ($InsertRow>0){
			$Result = DB_Txn_Commit();	
			$ConfirmationText = $ConfirmationText . ' ' . _('by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
			$EmailSubject = '采购计划收货单:'. $AdjustmentNumber . '号' .$InsertRow. '笔,已经录入,！'  ;
			if ($_SESSION['InventoryManagerEmail']!=''){
				
				if($_SESSION['SmtpSetting']==0){
					mail($_SESSION['InventoryManagerEmail'],$EmailSubject,$ConfirmationText);
				}else{
					include('includes/htmlMimeMail.php');
					$mail = new htmlMimeMail();
					$mail->setSubject($EmailSubject);
					$mail->setText($ConfirmationText);
					$result = SendmailBySmtp($mail,array($_SESSION['InventoryManagerEmail']));
				}					
			}				
			prnMsg( $EmailSubject,'success');
			echo '</br><a href="' . $RootPath . '/PDFPurchOrder.php?F=P&D=' . $AdjustmentNumber . '">打印发料单</a> </br>';
			$InsertRow=0;								
		}elseif ($CloseRequest>0){
			$Result = DB_Txn_Commit();
			$EmailSubject = '采购计划单' .$CloseRequest .'笔,已经关闭！'  ;
			prnMsg( $EmailSubject,'success');
			$CloseRequest=0;						
		}
		
		
			//echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">返回简易收货</a></br>';

	}
 echo'  </div>
          </form>';


include('includes/footer.php');

?>
