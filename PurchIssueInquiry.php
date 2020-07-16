
<?php

/* $Id: SupplierAccounts.php  ChengJiang $ */
/*
 * @Author: ChengJiang 
 * @Date: 2018-09-14 21:04:25 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-09-21 05:58:47
 */

include('includes/DefineSuppTransClass.php');
include('includes/DefinePOClass.php'); //needed for auto receiving code

/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');

$Title ='出入库查询';
/* webERP manual links before header.php */
$ViewTopic= 'AccountsPayable';
$BookMark = 'SupplierInvoice';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (isset( $_POST['ClearAccount'])){
	$ClearAccount=$_POST['ClearAccount'];
}else{
	$_POST['ClearAccount']=1;
}
if (isset( $_POST['OpenAccount'])){
	$OpenAccount=$_POST['OpenAccount'];
}else{
	$_POST['OpenAccount']=1;

}
if (isset( $_POST['Purch'])){
	$Purch=$_POST['Purch'];
}else{
	$_POST['Purch']=1;
}
if (isset( $_POST['Issue'])){
	$Issue=$_POST['Issue'];
}else{
	$_POST['Issue']=1;
	//$Issue=2;
}

/*

if (empty($_GET['identifier'])) {
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}

if (!isset($_SESSION['SuppTrans']->SupplierName)) {
	$sql="SELECT suppname FROM suppliers WHERE supplierid='" . $_GET['SupplierID'] . "'";
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
	if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
		$SQL = "SELECT supplierid,
					suppname,
					currcode,
					address1,
					address2,
					address3,
					address4,
					telephone,
					email,
					url
				FROM suppliers
				INNER JOIN custsupusers ON csno=supplierid  
				WHERE  used>=0 AND notype=2
						AND userid='".$_SESSION['UserID']."'
				ORDER BY suppname";
	} else {
		if (mb_strlen($_POST['Keywords']) > 0) {
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4,
							telephone,
							email,
							url
						FROM suppliers
						INNER JOIN custsupusers ON csno=supplierid  
						WHERE suppname " . LIKE . " '" . $SearchString . "'
						AND  used>=0 AND notype=2
						AND userid='".$_SESSION['UserID']."'
						ORDER BY suppname";
		} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
			$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
			$SQL = "SELECT supplierid,
							suppname,
							currcode,
							address1,
							address2,
							address3,
							address4,
							telephone,
							email,
							url
						FROM suppliers
						INNER JOIN custsupusers ON csno=supplierid  
						WHERE supplierid " . LIKE  . " '%" . $_POST['SupplierCode'] . "%'
						AND  used>=0 AND notype=2
						AND userid='".$_SESSION['UserID']."'
						ORDER BY supplierid";
		}
	} //one of keywords or SupplierCode was more than a zero length string
  
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
	$SQL = "SELECT suppliers.suppname
			FROM suppliers
			WHERE suppliers.supplierid ='" . $_SESSION['SupplierID'] . "'";
	$SupplierNameResult = DB_query($SQL);
	if (DB_num_rows($SupplierNameResult) == 1) {
		$myrow = DB_fetch_row($SupplierNameResult);
		$SupplierName = $myrow[0];
	}

	$sql="SELECT
				`stkmoveno`,
				`stockid`,
				`type`,
				`transno`,
				`loccode`,
				`trandate`,
				`userid`,
				`debtorno`,
				`branchcode`,
				`price`,
				`prd`,
				`reference`,
				`qty`,
				`discountpercent`,
				`standardcost`,
				`show_on_inv_crds`,
				`newqoh`,
				`hidemovt`,
				`narrative`
			FROM
				`stockmoves`
			WHERE
				1";
    }else{
		$sql="SELECT
				`stkmoveno`,
				`stockid`,
				`type`,
				`transno`,
				`loccode`,
				`trandate`,
				`userid`,
				`debtorno`,
				`branchcode`,
				`price`,
				`prd`,
				`reference`,
				`qty`,
				`discountpercent`,
				`standardcost`,
				`show_on_inv_crds`,
				`newqoh`,
				`hidemovt`,
				`narrative`
			FROM
				`stockmoves`
			WHERE
				1";
	}
//-----------
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';
	echo'<table cellpadding="3" class="selection">
	<tr>
		<td>' . _('Enter a partial Name') . ':</td>
		<td>';
	if (isset($_POST['Keywords'])) {
	echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
	echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</td>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter a partial Code') . ':</td>
		<td>';
	if (isset($_POST['SupplierCode'])) {
	echo '<input type="text" autofocus="autofocus" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size="15" maxlength="18" />';
	} else {
	echo '<input type="text" autofocus="autofocus" name="SupplierCode" size="15" maxlength="18" />';
	}
	echo '</td></tr>';
	echo'<tr><td>选择查询日期</td><td collspan="2">';
		       
		echo'	<input type="date"   alt="" min="'.substr($_SESSION['lastdate'],0,5).'01-01'.'" max="'.substr($_SESSION['lastdate'],0,8).'01'.'"  name="AfterDate" maxlength="10" size="11" value="' . substr($_SESSION['lastdate'],0,8).'01' . '" />
		<input type="date"   alt="" min="'.substr($_SESSION['lastdate'],0,5).'01-01'.'" max="'.$_SESSION['lastdate'].'"  name="BeforDate" maxlength="10" size="11" value="' . $_SESSION['lastdate'] . '" />';
		
	echo'</td></tr>';

	echo'<tr><td>选择查询类别</td><td collspan="2">';
	if (empty($Issue)&&empty($Purch)){
		echo '<input type="checkbox" name="Purch" value="1"  checked />收货类       
				<input type="checkbox" name="Issue" value="1" checked />发货类';
	}else{		       
		echo'<input type="checkbox" name="Purch" value="1" '. ($Purch==1 ?"checked":"").' />收货类
			<input type="checkbox" name="Issue" value="1" '. ($Issue==1 ?"checked":"").' />发货类';
	}
	echo'</td></tr>';
	echo'<tr><td>选择清账类别</td><td collspan="2">';
	if (empty($OpenAccount) && empty($ClearAccount)){
		echo'<input type="checkbox" name="OpenAccount" value="1" checked />未清账
		<input type="checkbox" name="ClearAccount" value="1" checked />已清账';
	}else{
		echo'<input type="checkbox" name="OpenAccount" value="1" '. ($OpenAccount==1 ?"checked":"").' />未清账
		<input type="checkbox" name="ClearAccount" value="1" '. ($ClearAccount==1 ?"checked":"").' />已清账';
			
	}
	echo'</td></tr>';
	echo'</table>';
	echo '<br />
		<div class="centre">
			<input type="submit" name="Search" value="查询" /><br>
			<input type="submit" name="SuppAccount" value="查询账单" />
			<input type="submit" name="crtExcel" value="导出Excel" />
			<input type="submit" name="CheckAccount" value="核对账单" /> 
		</div>';
    
	if (isset($_POST['SuppAccount'])) {
		if (isset($_SESSION['SupplierID'])){
		    prnMsg($_POST['PurchIssue']);
		}else{
			prnMsg($_POST['Issue']);
		}
		$result=DB_query($sql);
		//prnMsg($sql);
		
			echo '<table width="90%" cellpadding="4"  class="selection">
				<tr>
				    <th >序号</th>
					<th >供应商名称</th>
					<th >对账单号</th>
					<th >合同号</th>
					<th >收货金额</th>
					<th >税额</th>
					<th >合计</th>
					<th >类别</th>
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
		while($row=DB_fetch_array($result)){
			 $taxtotal=round($row['total'],2)+round($row['taxamount'],2);
			 if($row['branchcode']>0){
				$suptyp=1;
				$URL_Edit= $RootPath . '/CreateJournal.php?ty=2&ntpa=';
			 }else{
				$suptyp=0;
				$URL_Edit= $RootPath . '/CreateJournal.php?ty=2&ntpa=2';
			 }
			 if ($supacc!=$suptyp ) {			
				if ( $rw>1) {
					echo '<tr>
			        <td></td>
					<td colspan="3">对账合计</td>				
					<td >'.$Total.'</td>
					<td >'.$TaxTotal.'</td>
					<td >'.($Total+$TaxTotal).'</td>
					<td ></td>
					<td ></td>
				</tr>';
					$Total=0;
					$TaxTotal=0;
					$rw=1;
				}				
				$supacc=$suptyp;
			 }			
			 if ($row['supplierno']!=$suppno ) {
				if ($suppno!=0 && $rr>1) {
					echo '<tr>
			        <td></td>
					<td colspan="3">小计</td>				
					<td class="number">'.$Total.'</td>
					<td class="number">'.$TaxTotal.'</td>
					<td class="number">'.($Total+$TaxTotal).'</td>
					<td ></td>
					<td ></td>
				</tr>';
				    $rr=0;
					$Total=0;
					$TaxTotal=0;
				
				}
			
				$suppno=$row['supplierno'];
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				}else{
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				echo ' <td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$rw.'</a></td>
					  <td >['.$row['supplierno'].']'.$row['suppname'].'</td>';
			}else {	
					
				if ($k==1){
					echo '<tr class="EvenTableRows">
							<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$rw.'</a></td>
							<td></td>';
					$k=0;
				}else {
					echo '<tr class="OddTableRows">
							<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$rw.'</a></td>
							<td></td>';
					$k=1;
				}
			}
			$Total+=round($row['total'],2);
			$TaxTotal+=round($row['taxamount'],2);
			$TotalAll+=round($row['total'],2);
			$TaxTotalAll+=round($row['taxamount'],2);
			$orderno=$row['orderno'];
			
			echo '<td>'.$row['transno'].'</td>
			      <td>'.$row['trandate'].'</td>';			
			echo' 	<td class="number">'.locale_number_format(round($row['qty'],2),2).'</td>
					<td class="number">'.locale_number_format(round($row['price'],2),2).'</td>
					<td class="number">'.locale_number_format(round($taxtotal,2),2).'</td>
					<td ></td>
					<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
				</tr>';
				
				$RowIndex++;
				$rr++;	
				$rw++;
		}//end while
			if ($rr>1 ) {
				echo '<tr>
				<td></td>
				<td colspan="3">小计</td>				
				<td class="number" >'.locale_number_format($Total,2).'</td>
				<td class="number">'.locale_number_format($TaxTotal,2).'</td>
				<td class="number">'.locale_number_format(($Total+$TaxTotal),2).'</td>
				<td ></td>
				<td ></td>
			</tr>';
				//$suppno=$row['supplierno'];
			}
			if ( $rw>1) {
				echo '<tr>
				<td></td>
				<td colspan="3">对账合计</td>				
				<td class="number">'.locale_number_format($Total,2).'</td>
				<td class="number">'.locale_number_format($TaxTotal,2).'</td>
				<td class="number">'.locale_number_format(($Total+$TaxTotal),2).'</td>
				<td ></td>
				<td ></td>
			</tr>';
				$Total=0;
				$TaxTotal=0;
				$rw=1;
			}
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
		echo '<td><input type="submit" name="Select" value="'.$myrow['supplierid'].'" /></td>
				<td>' . $myrow['suppname'] . '</td>
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
