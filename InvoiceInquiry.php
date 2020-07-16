
<?php

/* $Id: SupplierAccounts.php  ChengJiang $ */
/*
 * @Author: ChengJiang 
 * @Date: 2018-09-14 21:04:25 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-14 16:53:15
 * 去除custsupusers  简单移除
 */

include('includes/DefineSuppTransClass.php');
include('includes/DefinePOClass.php'); //needed for auto receiving code

/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');

$Title ='销售发货查询';
/* webERP manual links before header.php */
$ViewTopic= 'AccountsPayable';
$BookMark = 'SupplierInvoice';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (!isset( $_POST['PurchIssue'][0])&&(!isset($_POST['PurchIssue'][1]))){
	
	$_POST['PurchIssue'][0]=1;	
	//$_POST['PurchIssue'][1]=2;
}

if (!isset( $_POST['OpenClear'][0])&&(!isset($_POST['OpenClear'][1]))){
	
	$_POST['OpenClear'][0]=1;	
	$_POST['OpenClear'][1]=2;
}
if (!isset( $_POST['perioddate'])){
	
	$_POST['perioddate']=1;	
	
}
/*

if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (!isset($_SESSION['SuppTrans']->SupplierName)) {
	$sql="SELECT name FROM suppliers WHERE supplierid='" . $_GET['SupplierID'] . "'";
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
if (isset($_POST['Select'])) { /*User has hit the button selecting a supplier */
	$_SESSION['SupplierID'] = $_POST['Select'];
	unset($_POST['Select']);
	unset($_POST['Keywords']);
	unset($_POST['SupplierCode']);
	unset($_POST['Search']);
	unset($_POST['Go']);
	unset($_POST['Next']);
	unset($_POST['Previous']);
}

if (isset($_POST['Search'])
	OR isset($_POST['Go'])
	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {

	if (mb_strlen($_POST['Keywords']) > 0 AND mb_strlen($_POST['SupplierCode']) > 0) {
		prnMsg( _('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info' );
	}
	$SQL = "SELECT debtorno ,
		             
					    name,
						currcode,
						address1,
						address2,
						address3,
						address4,
						`phoneno`,
						email
				FROM `debtorsmaster` 
				INNER JOIN customerusers ON regid=debtorno  
				WHERE  used>=0 AND (custype=1 OR custype=3)
						AND debtorsmaster.userid='".$_SESSION['UserID']."'";
	if (!($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '')) {	

		if (mb_strlen($_POST['Keywords']) > 0) {
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = " AND name " . LIKE . " '" . $SearchString . "' ";
		} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
			$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
			$SQL = " AND  debtorno " . LIKE  . " '%" . $_POST['SupplierCode'] . "%'";
		}
	} //one of keywords or SupplierCode was more than a zero length string
	$SQL.="	ORDER BY name";
	$Result = DB_query($SQL);
	if (DB_num_rows($Result) == 1) {
		$myrow = DB_fetch_row($Result);
		$SingleSupplierReturned = $myrow[0];
	}
	if (isset($SingleSupplierReturned)) { /*there was only one supplier returned */
 	   $_SESSION['SupplierID'] = $SingleSupplierReturned;
	   unset($_POST['Keywords']);
	   unset($_POST['SupplierCode']);
	   unset($_POST['Search']);
        } else {
               unset($_SESSION['SupplierID']);
        }
} //end of if search

if (isset($_SESSION['SupplierID'])) {
	$SupplierName = '';
	$SQL = "SELECT name
			FROM debtorsmaster
			WHERE debtorno ='" . $_SESSION['SupplierID'] . "'";
	$SupplierNameResult = DB_query($SQL);
	if (DB_num_rows($SupplierNameResult) == 1) {
		$myrow = DB_fetch_row($SupplierNameResult);
		$SupplierName = $myrow[0];
	}
}
//-----------
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SQL = "SELECT debtorno , 
	               name 
	            FROM `debtorsmaster` 				
				INNER JOIN customerusers ON regid=debtorno  
				WHERE  used>=0 AND custype=1
						AND customerusers.userid='".$_SESSION['UserID']."'
				ORDER BY name";
	$SupplierNameResult = DB_query($SQL);

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';

	echo'<table cellpadding="3" class="selection">';
	$BeforDate=date("Y-m-d");
	$AfterDate=date("Y-01-01");
	echo'<tr>
		   <td>选择查询日期</td>
		   <td >
		     <input type="date"   alt="" min="'.'01-01'.'" max="'.date("Y-m-01").'"  name="AfterDate" maxlength="10" size="11" value="' . $AfterDate . '" />
			 <input type="date"   alt="" min="'.'01-01'.'" max="'.$BeforDate.'"  name="BeforDate" maxlength="10" size="11" value="' . $BeforDate . '" />
			 </td>';
	echo '<td>
			<input type="radio" name="perioddate" value="1" '. ($_POST['perioddate']==1 ?"checked":"").' />按发货日期       
			<input type="radio" name="perioddate" value="2" '. ($_POST['perioddate']==2 ?"checked":"").' />按入账日期
		  </td>';	
	echo'</tr>';
	echo'<tr><td>选择客户</td>
			<td colspan="2">
		<input type="text" name="SuppName"  id="SuppName"   list="SuppCode"   maxlength="50" size="30"  onChange="inSelect(this, SuppCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
		<datalist id="SuppCode"> ';
		$n=1;
		while ($row=DB_fetch_array($SupplierNameResult )){
			$len=count(trim($row['debtorno']));
			$lnstr="";
			for ($i=0;$i<7-$len;$i++){
			   $lnstr.="-";
			}
			echo '<option value="'.$row['debtorno'] .$lnstr.htmlspecialchars(trim($row['name']), ENT_QUOTES,'UTF-8', false) . '"label="" >';
			$n++;
		}

		echo'</datalist></td>
			</tr>';
		echo'<tr>
			<td>选择单号</td>
			<td collspan="2"> <input type="text" name="PurchNo" value=""  > </td>
		</tr>';
	
	echo'<tr><td>选择清账类别</td>
	         <td collspan="2">';
	echo '<input type="checkbox" name="OpenClear[0]" value="1" '. ($_POST['OpenClear'][0]==1 ?"checked":"").' />未清账       
		  <input type="checkbox" name="OpenClear[1]" value="2" '. ($_POST['OpenClear'][1]==2 ?"checked":"").' />已清账';
	
	echo'</td></tr>';
	echo'</table>';
	echo '<div class="centre">';
		
		echo'<input type="submit" name="Search" value="查询" />
		     <input type="submit" name="ExportCSV" value="导出CSV" /></div></br>';
   
			    $sql="SELECT grnno,
							c.name ,
							a.stkmoveno,
							a.stockid,
							type,
							transno,
							loccode,
							accountdate,
							trandate,							
							a.debtorno ,
							branchcode,
							price,
							prd,
							reference,
							qty,
							discountpercent,
							standardcost,
							show_on_inv_crds,
							newqoh,
							newamount,
							hidemovt,
							narrative
						FROM
							stockmoves a
						LEFT JOIN grns b ON	a.transno = b.grnbatch
						LEFT JOIN debtorsmaster c ON a.debtorno=c.debtorno
						WHERE type=10 
						ORDER BY transno,c.name";
 if (isset($_POST['Search'])) {			
	 $result=DB_query($sql);			
		 echo '<table width="90%" cellpadding="4"  class="selection">
			 <tr>
				 <th >序号</th>
				 <th >收货单号</th>
				 <th >供应商编码名称</th>
				 <th >日期</th>					
				 <th >合同号</br>计划单</th>
				 <th >数量</th>
				 <th >价格</th>
				 <th >金额</th>
				 <th >税率</th>
				 <th >税额</th>
				 <th >合计</th>
				 <th >摘要</th>
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
		 $TransNO=0;
		 $SuppID=0;
	 while($row=DB_fetch_array($result)){
		 
		 
			 if ($k==1){
				 echo '<tr class="EvenTableRows">';
														 
				 $k=0;
			 }else {
				 echo '<tr class="OddTableRows">';
				 $k=1;
			 }
			 echo '	<td>'.$RowIndex.'</td>';
		 if ($TransNO!=$row['transno']){
				 $URL_Edit= $RootPath .'/PDFPurchPlanOrder.php?F='.$AuthorPrice.'&D=' . $myrow['dispatchid'] ;
		 
				 $TransNO=$row['transno'];			
		 
		 
			 echo'<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$row['transno'].'</a></td>';

			 if ($SuppID!=$row['debtorno']){
				 $SuppID=$row['debtorno'];
				 echo'<td>'.$row['debtorno'].$row['name'].'</td>';
			 }else{
				 echo'<td></td>';				}
				 
				 echo'<td >'.$row['trandate'].'</td>
					 <td ></td>';
		 }else{
			 
		 echo '  <td></td>
				 <td></td>
				 <td ></td>
				 <td ></td>';					
		 }			
		 echo'<td class="number">'.locale_number_format(round(-$row['qty'],2),2).'</td>
			 <td class="number">'.locale_number_format(round($row['price'],2),2).'</td>
			 <td ></td>
			 <td class="number">'.locale_number_format(round($taxtotal,2),2).'</td>
			 <td ></td>
			 <td ></td>
			 <td ></td>
			 <td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
			 </tr>';
			 
			 $RowIndex++;
			 
	 }//end while
		 
	 
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
//if (isset($_POST['Search'])&& (!$result)) {



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
		echo '<td><input type="submit" name="Select" value="'.$myrow['debtorno'].'" /></td>
				<td>' . $myrow['name'] . '</td>
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
