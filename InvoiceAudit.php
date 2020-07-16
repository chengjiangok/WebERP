

	<?php

	/* $Id: PuchInquiry.php  ChengJiang $ */
	/*
		* @Author: ChengJiang 
		* @Date: 2018-10-07 15:37:49 
		* @Last Modified by: ChengJiang
		* @Last Modified time: 2018-10-07 16:49:59
		内容没有编写20190630
		*/
	include('includes/session.php');

	$Title ='销售发货审核';
	/* webERP manual links before header.php */
	$ViewTopic= 'AccountsPayable';
	$BookMark = 'SupplierInvoice';
	include('includes/header.php');
	include('includes/SQL_CommonFunctions.inc');
	if (empty($_GET['identifier'])) {
		$identifier=date('U');
	} else {
		$identifier=$_GET['identifier'];
	}
	if (!isset($_SESSION['R' . $identifier])) {   
			$_SESSION['R' . $identifier] = array();
	} //end if initiating a new PO

	if (!isset( $_POST['PurchIssue'][0])&&(!isset($_POST['PurchIssue'][1]))){
		
		$_POST['PurchIssue'][0]=1;	
		//$_POST['PurchIssue'][1]=2;
	}
	if (!isset( $_POST['OpenClear'][0])&&(!isset($_POST['OpenClear'][1]))){
		
		$_POST['OpenClear'][0]=1;	
		//$_POST['OpenClear'][1]=2;
	}
	//-------------
	if (!isset($_POST['PageOffset'])) {
		$_POST['PageOffset'] = 1;
	} else {
		if ($_POST['PageOffset'] == 0) {
			$_POST['PageOffset'] = 1;
		}
	}
	if ($_SESSION['R' . $identifier][1]==1){
		unset($_SESSION['R' . $identifier][0]);
		
	}
	if (isset($_POST['Select'])) { /*User has hit the button selecting a supplier */
		//$_SESSION['SupplierID'] = $_POST['Select'];
		$_SESSION['R' . $identifier] = array(0=>$_POST['Select']);
		/*
		if (!isset($_SESSION['SuppTrans']->SupplierName)) {
			$sql="SELECT suppname FROM suppliers WHERE supplierid='" . $_GET['SupplierID'] . "'";
			$result = DB_query($sql);
			$myrow = DB_fetch_row($result);
			$SupplierName=$myrow[0];
		} else {
			$SupplierName=$_SESSION['SuppTrans']->SupplierName;
		}*/
		unset($_POST['Select']);
		unset($_POST['Keywords']);
		unset($_POST['SupplierCode']);
		unset($_POST['SearchSuppliers']);
		unset($_POST['Go']);
		unset($_POST['Next']);
		unset($_POST['Previous']);
		//prnMsg($_SESSION['R' . $identifier][0]);
	}
	if (isset($_POST['Reset'])){
		unset($_SESSION['R' . $identifier]);
		unset($identifier);

	}
	if (isset($_POST['SearchSuppliers'])
		OR isset($_POST['Go'])
		OR isset($_POST['Next'])
		OR isset($_POST['Previous'])) {

		if (mb_strlen($_POST['Keywords']) > 0 AND mb_strlen($_POST['SupplierCode']) > 0) {
			prnMsg( _('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info' );
		}
		if ($_POST['Keywords'] == '' AND $_POST['SupplierCode'] == '') {
			$SQL = "SELECT supplierid,
						suppname,
						currcode
					FROM suppliers
					INNER JOIN custsupusers ON csno=supplierid  
					WHERE  used>=0 AND notype=2
							AND userid='".$_SESSION['UserID']."'
					ORDER BY suppname";
					$_SESSION['R' . $identifier][1]=1;
		} else {
			if (mb_strlen($_POST['Keywords']) > 0) {
				$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
				//insert wildcard characters in spaces
				$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
				$SQL = "SELECT supplierid,
								suppname,
								currcode								
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
								currcode
							FROM suppliers
							INNER JOIN custsupusers ON csno=supplierid  
							WHERE supplierid " . LIKE  . " '%" . $_POST['SupplierCode'] . "%'
							AND  used>=0 AND notype=2
							AND userid='".$_SESSION['UserID']."'
							ORDER BY supplierid";
			}
		} //one of keywords or SupplierCode was more than a zero length string
	
	} //end of if search
	
	if (isset($_SESSION['R' . $identifier][0])) {
		$SupplierName = '';
		$sql = "SELECT suppliers.suppname
				FROM suppliers
				WHERE suppliers.supplierid ='" . $_SESSION['R' . $identifier][0] . "'";
		$SupplierNameResult = DB_query($sql);
		if (DB_num_rows($SupplierNameResult) == 1) {
			$myrow = DB_fetch_row($SupplierNameResult);
			$SupplierName = $myrow[0];
		}
	}
	$SQL = "SELECT supplierid,
	suppname,
	currcode
FROM suppliers
INNER JOIN custsupusers ON csno=supplierid  
WHERE  used>=0 AND notype=2
		AND userid='".$_SESSION['UserID']."'
ORDER BY suppname";
	$SupplierNameResult = DB_query($SQL);
	//-----------
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" id="choosesupplier">';
	//echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';
	$_POST['AfterDate']=date('Y-m-01',strtotime (date('Y-m-d')));
	$_POST['BeforDate']=date('Y-m-d');
		echo'<table cellpadding="3" class="selection">';
		echo'<tr>
			<td>选择查询日期</td>
			<td >
			    <input type="date"   alt="" min="'.$_POST['AfterDate'].'" max="'.date('Y-m-d').'"  name="AfterDate" maxlength="10" size="11" value="' . $_POST['AfterDate'] . '" />
				<input type="date"   alt="" min="'.date('Y-m-01',strtotime (date('Y-m-d'))).'" max="'.date('Y-m-d').'"  name="BeforDate" maxlength="10" size="11" value="' .$_POST['BeforDate']. '" />
				</td>';
		echo '<td>
				<input type="radio" name="perioddate" value="1" '. ($_POST['perioddate']==1 ?"checked":"").' />按发货日期       
				<input type="radio" name="perioddate" value="2" '. ($_POST['perioddate']==2 ?"checked":"").' />按入账日期
			</td>';	
		echo'</tr>';
		echo'<tr><td>选择供应商</td>
		         <td colspan="2">
		     <input type="text" name="SuppName"  id="SuppName"   list="SuppCode"   maxlength="50" size="30"  onChange="inSelect(this, SuppCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
				<datalist id="SuppCode"> ';
				$n=1;
				while ($row=DB_fetch_array($SupplierNameResult )){
					echo '<option value="' . $row['supplierid'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['suppname'], ENT_QUOTES,'UTF-8', false) . '"label=' .  $n. '>';
					$n++;
				}

			echo'</datalist></td>
			       </tr>';
		
		echo'<tr><td>选择清账类别</td>
				<td collspan="2">';
		echo '<input type="checkbox" name="OpenClear[0]" value="1" '. ($_POST['OpenClear'][0]==1 ?"checked":"").' />未清账       
			<input type="checkbox" name="OpenClear[1]" value="2" '. ($_POST['OpenClear'][1]==2 ?"checked":"").' />已清账';
		
		echo'</td></tr>';
		
		echo'<tr>
			<td>选择查询方式</td>
			<td collspan="2">';	
		echo '<input type="checkbox" name="PurchIssue[0]" value="1" '. ($_POST['PurchIssue'][0]==1 ?"checked":"").' />按公司       
			<input type="checkbox" name="PurchIssue[1]" value="2" '. ($_POST['PurchIssue'][1]==2 ?"checked":"").' />按物料
			<input type="checkbox" name="PurchIssue[2]" value="3" '. ($_POST['PurchIssue'][2]==3 ?"checked":"").' />按单据       
			<input type="checkbox" name="PurchIssue[3]" value="3" '. ($_POST['PurchIssue'][3]==4 ?"checked":"").' />按合同
			<input type="checkbox" name="PurchIssue[4]" value="4" '. ($_POST['PurchIssue'][4]==5 ?"checked":"").' />明细';
		
		echo'</td></tr>
			</table><br>';
	/*
	if (!isset($_SESSION['R'.$identifier][0])){//&& count($_SESSION['R'.$identifier])>1){
		echo '<div>';
		if (isset($SuppliersReturned)) {
			echo '<input type="hidden" name="SuppliersReturned" value="' . $SuppliersReturned . '" />';
		}
		//echo $_SESSION['PO' . $identifier]->SupplierName;
		if ($_SESSION['R' . $identifier][1]!=1) {
		//if (!isset($result_SuppSelect)){
		echo '<table cellpadding="3" class="selection">
		<tr>
		<td>' . _('Enter text in the supplier name') . ':</td>
		<td><input type="text" name="Keywords" autofocus="autofocus" size="20" maxlength="25" /></td>
		<td><h3><b>' . _('OR') . '</b></h3></td>
		<td>' . _('Enter text extract in the supplier code') . ':</td>
		<td><input type="text" name="SuppCode" size="15" maxlength="18" /></td>
		</tr>
		</table>';
		}


	}*/
	echo '<div class="centre">';
			//if (!isset($_POST['SearchSuppliers'])){
		echo'<input type="submit" name="SearchSuppliers" value="查询" />';
		echo'<input type="submit" name="Reset" value="' . _('Reset') . '" /><br>';
		//	}
		if (isset($_SESSION['R'.$identifier])&& count($_SESSION['R'.$identifier])>1){
			echo'<input type="submit" name="SuppAccount" value="查询账单" />
				<input type="submit" name="crtExcel" value="导出Excel" />
				<input type="submit" name="CheckAccount" value="核对账单" /> 
			</div>';
		}
					$sq="select  *   
					from (select stkmoveno,stockid,newqoh from stockmoves as a
						where  stkmoveno=(select max(b.stkmoveno)  
							from stockmoves as b  
							where a.stockid = b.stockid  
							)  
						) as a  
					group by stockid";
					$sql="SELECT	grnno,
					b.supplierid,
					c.suppname,
					a.`stkmoveno`,
					a.`stockid`,
					`type`,
					`transno`,
					`loccode`,
					`accountdate`,
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
					`newamount`,
					`hidemovt`,
					`narrative`
				FROM
					`stockmoves` a
				LEFT JOIN grns b ON	a.transno = b.grnbatch
				LEFT JOIN suppliers c ON b.supplierid=c.supplierid
				WHERE type=25";
		if (isset($_POST['SearchSuppliers'])) {
			
			$result=DB_query($sql);
			//prnMsg($sql);
			
				echo '<table width="90%" cellpadding="4"  class="selection">
					<tr>
						<th >序号</th>
						<th >单号</th>
						<th >供应商编码名称</th>					
						<th >合同号</th>
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
			while($row=DB_fetch_array($result)){
				
				if($row['supaccno']>0){
					$suptyp=1;
					$URL_Edit= $RootPath . '/CreateJournal.php?ty=2&ntpa=';
				}else{
					$suptyp=0;
					$URL_Edit= $RootPath . '/CreateJournal.php?ty=2&ntpa=2';
				}
				
				
						
					if ($k==1){
						echo '<tr class="EvenTableRows">
								<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$RowIndex.'</a></td>
								';
						$k=0;
					}else {
						echo '<tr class="OddTableRows">
								<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$RowIndex.'</a></td>
								';
						$k=1;
					}
				echo '	<td>'.$row['grnno'].'</td>
						<td>'.$row['supplierid'].$row['suppname'].'</td>
						<td ></td>';			
				echo' 	<td class="number">'.locale_number_format(round($row['qty'],2),2).'</td>
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

	include('includes/footer.php');
	?>
