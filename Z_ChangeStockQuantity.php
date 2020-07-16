
<?php

/* $Id: Z_ChangeStockQuantity $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-08-09 05:40:51 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-08-09 08:43:04
 */


include ('includes/session.php');
$Title ='出入库更新维护';
include('includes/header.php');

	echo '<p class="page_title_text">
	      <img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (!isset($_POST['Search'])	AND !isset($_POST['Go'])	AND !isset($_POST['Next'])	AND !isset($_POST['Previous'])){ 
		echo '<div class="page_help_text">
				功能简介：<br>
					</div>';
	}
	$rang=array('0'=>'物料入库','1'=>'物料出库',  '3'=>'产品入库','12'=>'销售出库','36'=>'跨年度');

echo '<table class="selection">
		<tr>
			<th colspan="3">' . _('Selection Criteria') . '</th>
		</tr>
		<tr>
			<td>选择类别:
			    <select name="StockType" size="1" style="width:80px" >';
	
		foreach($rang as $key=>$val){			
			if (isset($_POST['StockType'])&& $key==$_POST['StockType']){
				echo '<option selected="True" value ="';
			}else{
				echo '<option value ="';
			}
				echo $key.'">'.$val.'</option>';		
		}		
	echo'</select>
		</td>';
		//now lets add the time period option
	if (!isset($_POST['ToDate'])) {
		$_POST['ToDate'] = '';
	}
	if (!isset($_POST['FromDate'])) {
		$_POST['FromDate'] = '';
	}
	echo '<td>' . _('Date From') . '<input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="FromDate" maxlength="10" size="11" vaue="' . $_POST['FromDate'] .'" /></td> 
		  <td>' . _('Date To') . '<input type="text" class="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" /></td>
			</tr>';
	echo'<tr>
			
			<td>' . _('From Stock Location') . ':
				<select name="StockLocation">';
		$sql = "SELECT locations.loccode, locationname, canview FROM locations
		INNER JOIN locationusers 
			ON locationusers.loccode=locations.loccode 
			AND locationusers.userid='" . $_SESSION['UserID'] . "'
			AND locationusers.canview=1 
			AND locations.internalrequest=1";
		$LocResult = DB_query($sql);
		$LocationCounter = DB_num_rows($LocResult);
		$locallctr = 0;//location all counter
		$Locations = array();
	if ($LocationCounter>0) {
		while ($myrow = DB_fetch_array($LocResult)) {
			$Locations[] = $myrow['loccode'];
			if (isset($_POST['StockLocation'])){
				if ($_POST['StockLocation'] == 'All' AND $locallctr == 0) {
					$locallctr = 1;
					echo '<option value="All" selected="selected">' . _('All') . '</option>';
				} elseif ($myrow['loccode'] == $_POST['StockLocation']) {
					echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
				}
			} else {
				if ($LocationCounter>1 AND $locallctr == 0) {//we show All only when it is necessary	
					echo '<option value="All">' . _('All') . '</option>';
					$locallctr = 1;
				}
				echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
			}
		}
		echo '<select></td>';
	} else {//there are possiblity that the user is the authorization person,lets figure things out

		$sql = "SELECT stockrequest.loccode,locations.locationname FROM stockrequest INNER JOIN locations ON stockrequest.loccode=locations.loccode
			INNER JOIN department ON stockrequest.departmentid=department.departmentid WHERE department.authoriser='" . $_SESSION['UserID'] . "'";
		$authresult = DB_query($sql);
		$LocationCounter = DB_num_rows($authresult);
		if ($LocationCounter>0) {
			$Authorizer = true;

			while ($myrow = DB_fetch_array($authresult)) {
				$Locations[] = $myrow['loccode'];
				if (isset($_POST['StockLocation'])) {
					if ($_POST['StockLocation'] == 'All' AND $locallctr==0) {
						echo '<option value="All" selected="selected">' . _('All') . '</option>';
						$locallctr = 1;
					} elseif ($myrow['loccode'] == $_POST['StockLocation']) {
						echo '<option value="' . $myrow['loccode'] . '" selected="selected">' . $myrow['locationname'] . '</option>';
					}
				} else {
					if ($LocationCounter>1 AND $locallctr == 0) {
						$locallctr = 1;
						echo '<option value="All">' . _('All') . '</option>';
					}
					echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] .'</option>';
				}
			}
			echo '</select></td>';


		} else {
			prnMsg(_('You have no authority to do the internal request inquiry'),'error');
			include('includes/footer.php');
			exit;
		}
	}
	echo '<input type="hidden" name="Locations" value="' . serialize($Locations) . '" />';//store the locations for later using;
	if (!isset($_POST['Authorized'])) {
		$_POST['Authorized'] = 'All';
	}
	echo '<td>授权状态
	          <select name="Authorized">';
	$Auth = array('All'=>_('All'),0=>_('Unauthorized'),1=>_('Authorized'));
	foreach ($Auth as $key=>$value) {
		if ($_POST['Authorized'] == $value) {
			echo '<option selected="selected" value="' . $key . '">' . $value . '</option>';
		} else {
			echo '<option value="' . $key . '">' . $value . '</option>';
		}
	}
	echo '</select></td></tr>';

	//add the department, sometime we need to check each departments' internal request
	if (!isset($_POST['Department'])) {
		$_POST['Department'] = '';
	}

echo '<td>' . _('Department') . '
	      <select name="Department">';
		//now lets retrieve those deparment available for this user;
		$sql = "SELECT departments.departmentid, 
						departments.description
						FROM departments LEFT JOIN stockrequest 
							ON departments.departmentid = stockrequest.departmentid
							AND (departments.authoriser = '" . $_SESSION['UserID'] . "' OR stockrequest.initiator = '" . $_SESSION['UserID'] . "') 
						WHERE stockrequest.dispatchid IS NOT NULL 
						GROUP BY stockrequest.departmentid";//if a full request is need, the users must have all of those departments' authority 
		$depresult = DB_query($sql);
	if (DB_num_rows($depresult)>0) {
		$Departments = array(); 
		if (isset($_POST['Department']) AND $_POST['Department'] == 'All') {
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
		} else {
			echo '<option value="All">' . _('All') . '</option>';
		}
		while ($myrow = DB_fetch_array($depresult)) {
			$Departments[] = $myrow['departmentid'];
			if (isset($_POST['Department']) AND ($_POST['Department'] == $myrow['departmentid'])) {
				echo '<option selected="selected" value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
			} else {
				echo '<option value="' . $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
			}
		}
		echo '</select></td>
			 <td>' . _('Request Number') . ':
				<input type="text" name="RequestNo" maxlength="8" size="9" /></td>';
		echo '<input type="hidden" name="Departments" value="' . base64_encode(serialize($Departments)) . '" />';
	} else {
		prnMsg(_('There are no internal request result available for your or your department'),'error');
		include('includes/footer.php');
		exit;
	}


	echo'<td></td>
			</tr>
			</table>';

	$SQL = "SELECT stockcategory.categoryid,
					stockcategory.categorydescription
				FROM stockcategory, internalstockcatrole
				WHERE stockcategory.categoryid = internalstockcatrole.categoryid
					" . $WhereAuthorizer . "
				ORDER BY stockcategory.categorydescription";
	$result1 = DB_query($SQL);
	//first lets check that the category id is not zero
	$Cats = DB_num_rows($result1);



	echo '<br />
	      <table class="selection">
			<tr>
				<th colspan="2"><h3>搜索采购物料请选择</h3></th>
			</tr>';	
	echo '<tr>
			<td>' . _('Enter partial') . '  <b>' . _('Description') . '</b>:</td>';
		if (!isset($_POST['Keywords'])) {
			$_POST['Keywords'] = '';
		}
	echo '<td><input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" /></td>';		
	echo '</tr>
		<tr>
		
			<td>' . _('OR') . ' ' .  _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>';
	if (!isset($_POST['StockCode'])) {
		$_POST['StockCode'] = '';
	}
	echo '<td><input type="text" autofocus="autofocus" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" /></td>';


echo '</tr> </table>
	
	<br/>
	<div class="centre">
		<input type="submit" name="Search" value="' . _('Search Now') . '" />
		<input type="submit" name="Update" value="更新确认" />
	</div>
	';

/*

echo '<p class="bad">' . _('Problem Report') . ':<br />' . _('There are no stock categories currently defined please use the link below to set them up') . '</p>';
echo '<br />
<a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
exit;
*/

if ($_POST['Search']){
	prnMsg('Search');
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
			stockrequestitems.decimalplaces,
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
		if ($_POST['Department']=="All" ){

				$sql.=" WHERE 1 ";
		}else{

			$sql.="WHERE stockrequest.departmentid='".$_POST['Department']."'";

		}
		if ($_POST['StockLocation']!="All" ){

			$sql.=" AND stockrequest.loccode='".$_POST['StockLocation']."'";
			
		}
		if ($_POST['FromDate']!="" ){
			$sql.=" AND stockrequest.despatchdate >='".$_POST['FromDate']."'";

		}
		if ($_POST['Authorized']==1){
			//待收货
				$sql.=" AND  stockrequest.authorised=1 AND stockrequestitems.completed=0 ";

		}
		if ($_POST['Authorized']==3){
			//已经完成
				$sql.=" AND  stockrequest.authorised=1 AND  stockrequestitems.completed=1";
		}
		if ($_POST['Authorized']==2){
			//待批准
				$sql.=" AND stockrequest.authorised<>1 AND  stockrequestitems.completed=0";
		}	

		$sql.=" ORDER BY stockrequest.dispatchid";//	WHERE  www_users.userid='".$_SESSION['UserID']."'
		//prnMsg($sql);
		$result=DB_query($sql);


		if (DB_num_rows($result)==0) {
			prnMsg( _('There are no outstanding authorised requests for this location'), 'info');
			echo '<br />';
			echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select another location') . '</a></div>';
			include('includes/footer.php');
			exit;
		}
	echo'<table>
			<tr>
				<th>采购单<br>编号</th>			
				<th>' . _('Requested Date') . '</th>
				<th>' . _('Department') . '</th>
				
				<th>' . _('Location Of Stock') . '</th>				
				<th>' . _('Product') . '</th>
			
				<th>' . _('Units') . '</th>
				<th>采购<br>' . _('Quantity') .'</th>				
				<th>'. _('Delivered') .'<br />' . _('Quantity') .  '</th>
				<th>收货<br>数量</th>
				
				<th>金额</th>
				<th>选择</br><input type="checkbox" name="SelectCheck" value="1"  onclick="InSelectCheck(this,2  )" /></th>
				
			</tr>';
		$disp=0;
		$k=0;
		while ($myrow=DB_fetch_array($result)) {
			$htmlstr=' ';
			if($myrow['authorised']==0 || $myrow['closed']==1){
				$htmlstr="disabled";
			}else{
				if($myrow['completed']==1 ||$myrow['completed']==2){
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
				<td>' . $myrow['departname'] . '</td>			';				 
			}else{
				
				echo '<td colspan="3"></td>';	
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
			echo'<td>' . $myrow['locationname'] . '</td>
			<td>' .$myrow['stockid'].":". $myrow['description'] . '</td>
				
				<td>' . $myrow['uom'] . '</td>
				
				<td class="number">' . locale_number_format($myrow['quantity'],$myrow['decimalplaces']) . '</td>
				<td class="number">'.locale_number_format($myrow['qtydelivered'],$myrow['decimalplaces']).'</td>
				<td class="number"><input type="text" class="number" name="'. $myrow['dispatchid'] . 'Qty'. $myrow['dispatchitemsid'] . '" id="'. $myrow['dispatchid'] . 'Qty'. $myrow['dispatchitemsid'] . '" size="5" maxlength="5" onChange="inQTY(this,'.$myrow['decimalplaces'] .','.$Conf['quantity'].'  )"  value="'.locale_number_format($qtydeliv,$myrow['decimalplaces']).'" /></td>
				
				<td class="number"><input type="text" class="number" name="'. $myrow['dispatchid'] . 'Amount'. $myrow['dispatchitemsid'] . '"  id="'. $myrow['dispatchid'] . 'Amount'. $myrow['dispatchitemsid'] . '"  size="7" onChange="inAmount(this,'.$myrow['decimalplaces'] .' )"   value="'.locale_number_format($myrow['taxprice']*($qtydeliv),$myrow['decimalplaces']).'" /></td>';
		   echo'<td><input type="checkbox" id="SelectPurch" name="'. $myrow['dispatchid'] . 'SelectPurch'. $myrow['dispatchitemsid'] .'"   value="'. $myrow['dispatchid'] . 'SelectPurch'. $myrow['dispatchitemsid'] .'"    /></td>
			
				</tr>';
		}	
	echo '</table>';

}


	echo '</form>';

	
/*get the order number that was credited */

$SQL = "SELECT order_, id
		FROM debtortrans
		WHERE transno='" . $_GET['CreditNoteNo'] . "' AND type='11'";
$Result = DB_query($SQL);

$myrow = DB_fetch_row($Result);
$OrderNo = $myrow[0];
$IDDebtorTrans = $myrow[1];

/*Now get the stock movements that were credited into an array */

$SQL = "SELECT stockid,
				loccode,
				debtorno,
				branchcode,
				prd,
				qty
			FROM stockmoves
			WHERE transno ='" .$_GET['CreditNoteNo'] . "' AND type='11'";
$Result = DB_query($SQL);

$i=0;

While ($myrow = DB_fetch_array($Result)){
	$StockMovement[$i] = $myrow;
	$i++;
}

//prnMsg(_('The number of stock movements to be deleted is') . ': ' . DB_num_rows($Result),'info');


$Result = DB_Txn_Begin(); /* commence a database transaction */

/*Now delete the custallocns */

$SQL = "DELETE FROM custallocns
        WHERE transid_allocfrom ='" . $IDDebtorTrans . "'";

$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('The custallocns record could not be deleted') . ' - ' . _('the sql server returned the following error');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

//prnMsg(_('The custallocns record has been deleted'),'info');

/*Now delete the debtortranstaxes */

$SQL = "DELETE debtortranstaxes FROM debtortranstaxes
               WHERE debtortransid ='" . $IDDebtorTrans . "'";
$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('The debtortranstaxes record could not be deleted') . ' - ' . _('the sql server returned the following error');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

//prnMsg(_('The debtortranstaxes record has been deleted'),'info');

/*Now delete the DebtorTrans */
$SQL = "DELETE FROM debtortrans
               WHERE transno ='" . $_GET['CreditNoteNo'] . "' AND Type=11";
$DbgMsg = _('The SQL that failed was');
$ErrMsg = _('A problem was encountered trying to delete the Debtor transaction record');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

/*Now reverse updated SalesOrderDetails for the quantities credited */

foreach ($StockMovement as $CreditLine) {

	$SQL = "UPDATE salesorderdetails SET qtyinvoiced = qtyinvoiced - " . $CreditLine['qty'] . "
                       WHERE orderno = '" . $OrderNo . "'
                       AND stkcode = '" . $CreditLine['stockid'] . "'";

	$ErrMsg =_('A problem was encountered attempting to reverse the update the sales order detail record') . ' - ' . _('the SQL server returned the following error message');
	$Result = DB_query($SQL,$ErrMsg,$DbgMsg, true);

/*reverse the update to LocStock */

	$SQL = "UPDATE locstock SET locstock.quantity = locstock.quantity + " . $CreditLine['qty'] . "
			             WHERE  locstock.stockid = '" . $CreditLine['stockid'] . "'
			             AND loccode = '" . $CreditLine['loccode'] . "'";

	$ErrMsg = _('SQL to reverse update to the location stock records failed with the error');

	$Result = DB_query($SQL,$ErrMsg,$DbgMsg, true);

/*Delete Sales Analysis records
 * This is unreliable as the salesanalysis record contains totals for the item cust custbranch periodno */
	$SQL = "DELETE FROM salesanalysis
                       WHERE periodno = '" . $CreditLine['prd'] . "'
                       AND cust='" . $CreditLine['debtorno'] . "'
                       AND custbranch = '" . $CreditLine['branchcode'] . "'
                       AND qty = '" . $CreditLine['qty'] . "'
                       AND stockid = '" . $CreditLine['stockid'] . "'";

	$ErrMsg = _('The SQL to delete the sales analysis records with the message');

	$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
}

/* Delete the stock movements  */
$SQL = "DELETE stockmovestaxes.* FROM stockmovestaxes INNER JOIN stockmoves
			ON stockmovestaxes.stkmoveno=stockmoves.stkmoveno
               WHERE stockmoves.type=11 AND stockmoves.transno = '" . $_GET['CreditNoteNo'] . "'";

$ErrMsg = _('SQL to delete the stock movement tax records failed with the message');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
///prnMsg(_('Deleted the credit note stock move taxes').'info');
echo '<br /><br />';


$SQL = "DELETE FROM stockmoves
               WHERE type=11 AND transno = '" . $_GET['CreditNoteNo'] . "'";

$ErrMsg = _('SQL to delete the stock movement record failed with the message');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
//prnMsg(_('Deleted the credit note stock movements').'info');
echo '<br /><br />';




$SQL = "DELETE FROM gltrans WHERE type=11 AND typeno= '" . $_GET['CreditNoteNo'] . "'";
$ErrMsg = _('SQL to delete the gl transaction records failed with the message');
$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
//prnMsg(_('Deleted the credit note general ledger transactions').'info');

$result = DB_Txn_Commit();
//prnMsg(_('Credit note number') . ' ' . $_GET['CreditNoteNo'] . ' ' . _('has been completely deleted') . '. ' . _('To ensure the integrity of the general ledger transactions must be reposted from the period the credit note was created'),'info');

include('includes/footer.php');
?>
