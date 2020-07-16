
<?php
	/* $Id: PuchInquiry.php  $ */
	/*
		* @Author: ChengJiang 
		* @Date: 2018-10-07 15:37:49 
 * @Last Modified by: chengjiang
 * @Last Modified time: 2019-08-14 07:45:57
		*/
	include('includes/session.php');

	$Title ='采购收货查询';
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
	if (!isset( $_POST['OpenClear'])){
		
		$_POST['OpenClear']=0;	
		//$_POST['OpenClear'][1]=2;
	}
	
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
		unset($_POST['Search']);
		unset($_POST['Go']);
		unset($_POST['Next']);
		unset($_POST['Previous']);
		//prnMsg($_SESSION['R' . $identifier][0]);
	}
	if (isset($_POST['Reset'])){
		unset($_SESSION['R' . $identifier]);
		unset($identifier);

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
						currcode
					FROM suppliers
					INNER JOIN customerusers ON regid=supplierid  
					WHERE  used>=0 AND custype=2
							AND customerusers.userid='".$_SESSION['UserID']."'
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
							INNER JOIN customerusers ON regid=supplierid  
							WHERE suppname " . LIKE . " '" . $SearchString . "'
							AND  used>=0 AND custype=2
							AND  customerusers.userid='".$_SESSION['UserID']."'
							ORDER BY suppname";
			} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
				$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
				$SQL = "SELECT supplierid,
								suppname,
								currcode
							FROM suppliers
							INNER JOIN customerusers ON regid=supplierid  
							WHERE supplierid " . LIKE  . " '%" . $_POST['SupplierCode'] . "%'
							AND  used>=0 AND custype=2
							AND  customerusers.userid='".$_SESSION['UserID']."'
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
				INNER JOIN customerusers ON regid=supplierid  
				WHERE  used>=0 AND custype=2
						AND  customerusers.userid='".$_SESSION['UserID']."'
				ORDER BY suppname";
	$SupplierNameResult = DB_query($SQL);

	$afterdt=date('Y-m-d',strtotime (date('Y-m-d'))-31536000);
	if (!isset($_POST['AfterDate'])){
		$_POST['AfterDate']=date('Y-m-01');
	}
	if (!isset($_POST['BeforDate'])){
		$_POST['BeforDate']=date('Y-m-d');
	}
	if (!isset($_POST['CSV'])) {
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';

		echo'<table cellpadding="3" class="selection">';
		echo'<tr>
			<td>选择查询日期</td>
			<td >
				<input type="date"   alt="" min="'.$afterdt.'" max="'.date('Y-m-d').'"  name="AfterDate" maxlength="10" size="11" value="' . $_POST['AfterDate'] . '" />
				<input type="date"   alt="" min="'.date('Y-01-01',strtotime (date('Y-m-d'))).'" max="'.date('Y-m-d').'"  name="BeforDate" maxlength="10" size="11" value="' .$_POST['BeforDate']. '" />
				</td>';
		echo '<td>
			
			</td>';	
			//	<input type="radio" name="perioddate" value="1" '. ($_POST['perioddate']==1 ?"checked":"").' />按发货日期       
			//	<input type="radio" name="perioddate" value="2" '. ($_POST['perioddate']==2 ?"checked":"").' />按入账日期
		echo'</tr>';
		echo'<tr><td>选择供应商</td>
		         <td >
		     		<input type="text" name="SuppName"  id="SuppName"   list="SuppCode"   maxlength="50" size="30"  onChange="inSelect(this, SuppCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
				<datalist id="SuppCode"> ';
				$n=1;
				while ($row=DB_fetch_array($SupplierNameResult )){
					echo '<option value="' . $row['supplierid'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['suppname'], ENT_QUOTES,'UTF-8', false) . '"label=' .  $n. '>';
					$n++;
				}

			echo'</datalist></td>
			       </tr>';
			echo'<tr>
				   <td>选择单号</td>
				   <td > <input type="text" name="PurchNo" value="'.$_POST['PurchNo'].'"  > </td>
			   </tr>';
			echo '<tr><td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';
			if (isset($_POST['Keywords'])) {
				echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
			} else {
				echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
			}
			echo '</td>
				</tr>
				<tr>					
					<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>
					<td>';
			if (isset($_POST['StockCode'])) {
				echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
			} else {
				echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
			}
			echo '</td></tr>';
		echo'<tr>
		        <td>选择清账类别</td>
				<td >
					<input type="radio" name="OpenClear" value="0" '. ($_POST['OpenClear']==0 ?"checked":"").' />全部      
					<input type="radio" name="OpenClear" value="1" '. ($_POST['OpenClear']==1 ?"checked":"").' />已清账
					<input type="radio" name="OpenClear" value="2" '. ($_POST['OpenClear']==2 ?"checked":"").' />未清账
				</td>
			</tr>';	
		echo'</table><br>';
	
	echo '<div class="centre">		
	         <input type="submit" name="Search" value="查询" />
			 <input type="submit" name="CSV" value="导出CSV" />
			</div>';		
	}
			$sql="SELECT  c.custname suppname,
							a.stkmoveno,
							
							a.stockid,
							d.description,
							d.longdescription,
							type,
							transno,
							connectid,
							loccode,
							accountdate,
							trandate,
							a.userid,
							debtorno supplierid,
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
						FROM stockmoves a		
						LEFT JOIN stockmaster d ON	a.stockid = d.stockid
						LEFT JOIN custname_reg_sub c ON a.debtorno=c.regid	
						
						WHERE (type=25 OR type=17 OR type=45) AND DATE_FORMAT(trandate,'%Y-%m-%d')>='".$_POST['AfterDate']."'AND DATE_FORMAT(trandate,'%Y-%m-%d')<='".$_POST['BeforDate']."'";
			//$wh=0;
			/*
			if ($_POST['Keywords'] AND $_POST['StockCode']) {
				prnMsg (_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
			}*/
			if (isset($_POST['SuppName']) AND mb_strlen($_POST['SuppName'])>0) {
				//insert wildcard characters in spaces
				$_POST['SuppName'] = mb_strtoupper($_POST['SuppName']);
				$SuppNameString = '%' . str_replace(' ', '%', explode(":",$_POST['SuppName'])[0]) . '%';
				$sql.=" AND c.custname " . LIKE . " '$SuppNameString'";
			}
			if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
				//insert wildcard characters in spaces
				$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
				$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
				$sql.=" AND CONCAT(d.description,d.longdescription) " . LIKE . " '$SearchString'";
			}
			if (isset($_POST['StockCode']) AND mb_strlen($_POST['StockCode'])>0) {
				$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
				$sql.=" AND d.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'";
			}
		
			if (isset($_POST['PurchNo']) AND mb_strlen($_POST['PurchNo'])>0) {
				if (stripos($_POST['PurchNo'],',')>0){
					$sql.=" AND transno IN (".$_POST['PurchNo'].")";
				}else if (stripos($_POST['PurchNo'],'-')>0){
					$purchno=explode('-',$_POST['PurchNo']);
					$sql.=" AND transno >=".$purchno[0]." AND transno<= ".$purchno[count($purchno)-1];
				
				}else if (stripos($_POST['PurchNo'],'~')>0){
					$purchno=explode('~',$_POST['PurchNo']);
					$sql.=" AND transno >=".$purchno[0]." AND transno<= ".$purchno[count($purchno)-1];
				
				}else{
					$sql.=" AND transno ='".$_POST['PurchNo']."'";
				}				
			}
			if ($_POST['OpenClear']==1){
				//已清账
					$sql.=" AND  branchcode<>0 ";
		
			}elseif ($_POST['OpenClear']==2){
				//未清账
				$sql.=" AND  branchcode=0 ";
			}			
			$sql.=" ORDER BY transno,c.custname";
            //prnMsg($sql);
			$result=DB_query($sql);	
			$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
			include('includes/footer.php');
			exit;
		}
	if (isset($_POST['Search'])OR isset($_POST['Go'])	OR isset($_POST['Next'])
		OR isset($_POST['Previous'])||isset($_POST['CSV'])) {

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
		if( !isset($_POST['CSV'])){
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
			
		echo '<table width="90%" cellpadding="4"  class="selection">
			<tr>
				<th >序号</th>
				<th >入库</br>单号</th>
				<th >供应商编码名称</th>
				<th >日期</th>					
				<th >合同号</br>计划单</th>
				<th >物料编码:名称</th>
				<th >数量</th>
				<th >含税</br>价格</th>
				<th >金额</br>小计</th>						
				<th >摘要</th>				
			</tr>';				
			$k=0;	//$rr=0	//$rw=1;	//$suppno='';	c='-1';	//$suptyp=2;	//$Resultarr=array();
			$Total=0;					
			$TotalAmo=0;			
			$TransNO=0;
			$SuppID=0;
			$RowIndex = 0;
		
	if($ListCount <> 0 ) {
	
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		while ($row=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			//$SQL="SELECT  `cess`, `taxprice` FROM `purchorderdetails` WHERE  `orderno`='".$row['connectid']."'  AND itemcode='".$row['stockid']."' LIMIT 1";
			if ($row['type']==25||$row['type']==45){  //采购合同收货
				$SQL="SELECT  `cess`, `taxprice` FROM `purchorderdetails` WHERE  `orderno`='".$row['connectid']."' AND itemcode='".$row['stockid']."' LIMIT 1";
			}elseif($row['type']==17){//计划单收货
                $SQL="SELECT   `taxcatid`, `cess`, `taxprice` FROM `stockrequestitems` WHERE `dispatchid`='".$row['connectid']."' AND`stockid`='".$row['stockid']."' LIMIT 1";
			}
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);
			if (isset($Row)){
				$TaxRate=$Row['cess'];
				$TaxPrice=$Row['taxprice'];
			}else{
				$TaxRate=0;
				$TaxPrice=0;
				$RPError=1;
			}
			$Total=round($row['qty']*$TaxPrice,2);
			$TotalAmo+=$Total;
				if ($k==1){
					echo '<tr class="EvenTableRows">';
															
					$k=0;
				}else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				echo '<td>'.($RowIndex+1).'</td>';
			if ($TransNO!=$row['transno']){
		
				$URL_Edit= $RootPath .'/PDFPurchOrder.php?F='.$row['type'].'&D=' . $row['transno'] ;
				$TransNO=$row['transno'];			
			
				echo'<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$row['transno'].'</a></td>';

				if ($SuppID!=$row['supplierid']){
					$SuppID=$row['supplierid'];
					echo'<td>'.$row['supplierid'].$row['suppname'].'</td>';
				}else{
					echo'<td></td>';
				}					
					echo'<td >'.$row['trandate'].'</td>';
			}else{				
			  echo '<td></td>
					<td></td>
					<td ></td>';					
			}	
			echo '<td>';
					if ($row['type']==17){
						echo '计划单:'.$row['connectid'];
					}elseif ($row['type']==25){
						echo '合同:'.$row['connectid'];
					}elseif ($row['type']==45){
						echo '简易收货:'.$row['connectid'];
					}
		echo' </td>
				  <td >'.$row['stockid'].':'.$row['description'].'</td>';
			echo'<td class="number">'.locale_number_format(round($row['qty'],2),2).'</td>
				<td class="number">'.locale_number_format($TaxPrice,2).'</td>
				<td class="number">'.locale_number_format($Total,2).'</td>		
				<td >'.$row['narrative'].'</td>														
				</tr>';				
				$RowIndex++;
					
		}//end while			
		echo '<tr>
				<td></td>
				<td ></td>				
				<td ></td>
				<td ></td>					
				<td ></td>	
				<td >总计</td>	
				<td ></td>
				<td ></td>
				<td class="number">'.locale_number_format(($TotalAmo),2).'</td>
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
}
	if (isset($_POST['CSV'])) {
	
		$CSVListing =  iconv("UTF-8","gbk//TRANSLIT",'序号' ).','.iconv("UTF-8","gbk//TRANSLIT", '入库单号') .','.iconv("UTF-8","gbk//TRANSLIT", '供应商编码名称') .
		','.iconv("UTF-8","gbk//TRANSLIT", '日期') .','.iconv("UTF-8","gbk//TRANSLIT", '合同号/计划单') .','.iconv("UTF-8","gbk//TRANSLIT",'物料编码:名称') 
		.','.iconv("UTF-8","gbk//TRANSLIT",'数量') .','.iconv("UTF-8","gbk//TRANSLIT",'含税价格') .','.iconv("UTF-8","gbk//TRANSLIT",'金额小计').','.iconv("UTF-8","gbk//TRANSLIT",'摘要') ."\n";
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	    $rw=1;
		while ($row=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			$CSVListing .= '"';
			if ($row['type']==25||$row['type']==45){  //采购合同收货
				$SQL="SELECT  `cess`, `taxprice` FROM `purchorderdetails` WHERE  `orderno`='".$row['connectid']."' AND itemcode='".$row['stockid']."' LIMIT 1";
			}elseif($row['type']==17){//计划单收货
                $SQL="SELECT   `taxcatid`, `cess`, `taxprice` FROM `stockrequestitems` WHERE `dispatchid`='".$row['connectid']."' AND`stockid`='".$row['stockid']."' LIMIT 1";
			}
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);
			if (isset($Row)){
				$TaxRate=$Row['cess'];
				$TaxPrice=$Row['taxprice'];
			}else{
				$TaxRate=0;
				$TaxPrice=0;
				$RPError=1;
			}
			$Total=round($row['qty']*$TaxPrice,2);
			$TotalAmo+=$Total;
			if ($row['type']==17){
				$type= '计划单:'.$row['connectid'];
			}elseif ($row['type']==25){
				$type= '合同:'.$row['connectid'];
			}elseif ($row['type']==45){
				
				$type='简易收货:'.$row['connectid'];
			}
			$CSVListing .=$rw.'","'.$row['transno'].'","'.$row['supplierid'].iconv("UTF-8","gbk//TRANSLIT",$row['suppname']).'","'.$row['transdate'].'","'.iconv("UTF-8","gbk//TRANSLIT",$type).'","'.$row['stockid'].':'.iconv("UTF-8","gbk//TRANSLIT",$row['description']).'","'.round($row['qty'],2).'","'.round($TaxPrice,2).'","'.round($Total,2).'","'.iconv("UTF-8","gbk//TRANSLIT",$row['narrative']).'"'. "\n";
			$rw++;
		}
	
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header("Content-disposition: attachment; filename=".iconv("UTF-8","gbk//TRANSLIT",'采购收货表_').  date('Y-m-d_his')  .'.csv');
		header("Pragma: public");
		header("Expires: 0");
	//	echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $CSVListing;
		exit;
	}


}
	echo '</div>
		</form>';

	include('includes/footer.php');
	?>
