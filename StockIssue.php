
<?php
	/* $Id:StockIssue.php  $ */
	/*
		* @Author: ChengJiang 
		* @Date: 2018-10-07 15:37:49 
 * @Last Modified by: chengjiang
 * @Last Modified time: 2019-08-14 07:45:57
		*/
	include('includes/session.php');

	$Title ='生产简易发料';
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
	if (!isset($_SESSION['SI' . $identifier])) {   
			$_SESSION['SI' . $identifier] = array();
	} //end if initiating a new PO

	if (!isset( $_POST['PurchIssue'][0])&&(!isset($_POST['PurchIssue'][1]))){
		
		$_POST['PurchIssue'][0]=1;	
		//$_POST['PurchIssue'][1]=2;
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
if (!isset( $_POST['IssueStatus'])){
		
	$_POST['IssueStatus']=1;	
	
}
	if ($_SESSION['SI' . $identifier][1]==1){
		unset($_SESSION['SI' . $identifier][0]);
		
	}
	if (isset($_POST['Search'])OR isset($_POST['Confirm'])
		OR isset($_POST['Go'])
		OR isset($_POST['Next'])
		OR isset($_POST['Previous'])) {

		if (mb_strlen($_POST['SuppName']) > 0 AND mb_strlen($_POST['SupplierCode']) > 0) {
			prnMsg( _('Supplier name keywords have been used in preference to the Supplier code extract entered'), 'info' );
		}
		if ($_POST['SuppName'] == '' AND $_POST['SupplierCode'] == '') {
			$SQL = "SELECT supplierid,
						custname suppname,
						currcode
					FROM suppliers
					INNER JOIN customerusers ON regid=supplierid  
					LEFT JOIN custname_reg_sub  ON custname_reg_sub.regid=supploerid		
					WHERE  used>=0 AND  custype=2
							AND customerusers.userid='".$_SESSION['UserID']."'
					ORDER BY suppname";
					$_SESSION['SI' . $identifier][1]=1;
		} else {
			if (mb_strlen($_POST['SuppName']) > 0) {
				$_POST['SuppName'] = mb_strtoupper($_POST['SuppName']);
				//insert wildcard characters in spaces
				$SearchString = '%' . str_replace(' ', '%', $_POST['SuppName']) . '%';
				$SQL = "SELECT supplierid,
								suppname,
								currcode								
							FROM suppliers
							INNER JOIN customerusers ON regid=supplierid  
							WHERE suppname " . LIKE . " '" . $SearchString . "'
							AND  used>=0 AND  custype=2
							AND customerusers.userid='".$_SESSION['UserID']."'
							ORDER BY suppname";
			} elseif (mb_strlen($_POST['SupplierCode']) > 0) {
				$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
				$SQL = "SELECT supplierid,
								suppname,
								currcode
							FROM suppliers
							INNER JOIN customerusers ON regid=supplierid  
							WHERE supplierid " . LIKE  . " '%" . $_POST['SupplierCode'] . "%'
							AND  used>=0 AND  custype=2
							AND customerusers.userid='".$_SESSION['UserID']."'
							ORDER BY supplierid";
			}
		} //one of keywords or SupplierCode was more than a zero length string
	
	} //end of if search
	
	if (isset($_SESSION['SI' . $identifier][0])) {
		$SupplierName = '';
		$sql = "SELECT suppliers.suppname
				FROM suppliers
				WHERE suppliers.supplierid ='" . $_SESSION['SI' . $identifier][0] . "'";
		$SupplierNameResult = DB_query($sql);
		if (DB_num_rows($SupplierNameResult) == 1) {
			$myrow = DB_fetch_row($SupplierNameResult);
			$SupplierName = $myrow[0];
		}
	}
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SQL = "SELECT supplierid,
					suppname,
					currcode
				FROM suppliers
				INNER JOIN customerusers ON regid=supplierid  
				WHERE  used>=0 AND  custype=2
						AND customerusers.userid='".$_SESSION['UserID']."'
				ORDER BY suppname";
	$SupplierNameResult = DB_query($SQL);

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';

	$afterdt=date('Y-m-d',strtotime (date('Y-m-d'))-31536000);
	if (!isset($_POST['AfterDate'])){
		$_POST['AfterDate']=date('Y-m-01');
	}
	if (!isset($_POST['BeforDate'])){
		$_POST['BeforDate']=date('Y-m-d');
	}
		echo'<table cellpadding="3" class="selection">';
		echo'<tr>
			<td>选择查询日期(起)</td>
			<td >
				<input type="date"   alt="" min="'.$afterdt.'" max="'.date('Y-m-d').'"  name="AfterDate" maxlength="12" size="12" value="' . $_POST['AfterDate'] . '" />
				</td>
			<td>至
				<input type="date"   alt="" min="'.date('Y-01-01',strtotime (date('Y-m-d'))).'" max="'.date('Y-m-d').'"  name="BeforDate" maxlength="12" size="12" value="' .$_POST['BeforDate']. '" />
				</td>			
			</tr>';
			echo'<tr>
			<td>发料仓库:</td>';
			$sql="SELECT DISTINCT
						stockcategorylocation.`loccode`,
						locationname
					FROM
						`stockcategorylocation`
					LEFT JOIN locations ON locations.loccode = stockcategorylocation.loccode
					INNER JOIN locationusers ON locationusers.loccode = stockcategorylocation.loccode AND locationusers.canupd=1 AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
					WHERE
						mbflag = 'B'  AND internalrequest = 1
					ORDER BY
						stockcategorylocation.loccode";	/*
	$sql="SELECT locations.loccode,
				 locationname
			FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			WHERE internalrequest = 1
			ORDER BY locationname";*/
	
	$result=DB_query($sql);
	
	echo '<td>
	          <select name="StockLocation">';
	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['StockLocation']) AND $_POST['StockLocation']==$myrow['loccode']){
			echo '<option selected="True" value="' . $myrow['loccode'] . '">' . $myrow['loccode'].' - ' .htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">'.htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
	echo '</select></td>';
	$sql="SELECT DISTINCT
				stockcategorylocation.`loccode`,
				locationname
			FROM
				`stockcategorylocation`
			LEFT JOIN locations ON locations.loccode = stockcategorylocation.loccode
			INNER JOIN locationusers ON locationusers.loccode = stockcategorylocation.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
			WHERE
				mbflag = 'M'
			ORDER BY
				stockcategorylocation.loccode";/*
	$sql="SELECT `categoryid` loccode, `categorydescription` locationname
	         FROM `stockcategory` 
			INNER JOIN locationusers ON locationusers.loccode=categoryid AND locationusers.userid='" .  $_SESSION['UserID'] . "' 
			WHERE stocktype='M'
			ORDER BY locationname";*/
	
	$result=DB_query($sql);
	
	echo '<td>产品仓库:
	          <select name="WorkLocation">';	
	while ($myrow=DB_fetch_array($result)){
		if (isset($_POST['WorkLocation']) AND $_POST['WorkLocation']==$myrow['loccode']){
			echo '<option selected="True" value="' . $myrow['loccode'] . '">' . $myrow['loccode'].' - ' .htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">'.htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
	echo '</select></td>
		</tr>';  
	echo'<tr>
		<td>选择摘要关键字</td>
		<td colspan="2"> <input type="text" name="RemarkKeys" value=""  > </td>
	
	</tr>';
	echo'<tr>
			<td>选择供应商</td>
			<td colspan="2">
			<input type="text" name="SuppName"  id="SuppName"   list="SuppCode"   maxlength="50" size="30"  onChange="inSelect(this, SuppCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
		<datalist id="SuppCode"> ';
		$n=1;
		while ($row=DB_fetch_array($SupplierNameResult )){
			echo '<option value="' . $row['supplierid'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['suppname'], ENT_QUOTES,'UTF-8', false) . '"label=' .  $n. '>';
			$n++;
		}

	echo'</datalist>
		  </td>
		
		</tr>';
	echo'<tr>
			<td>选择单号</td>
			<td colspan="2"> <input type="text" name="PurchNo" value="'.$_POST['PurchNo'].'"  > </td>
		
			</tr>';
	echo '<tr>
			<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td>
			<td colspan="2">';
		if (isset($_POST['keywords'])) {
			echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
		} else {
			echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
		}
	echo '	</td></tr>
			<tr>
			<td></td>
			<td colspan="2"><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:
			';
			if (isset($_POST['StockCode'])) {
				echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
			} else {
				echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
			}
			echo '</td>
				</tr>';
	echo'<tr><td>查询类别</td>
				<td><input type="radio" name="IssueStatus" value="0" '. ($_POST['IssueStatus']==0 ?"checked":"").' />全部       
					<input type="radio" name="IssueStatus" value="1" '. ($_POST['IssueStatus']==1 ?"checked":"").' />未发料
					<input type="radio" name="IssueStatus" value="2" '. ($_POST['IssueStatus']==2 ?"checked":"").' />已发料
				</td></tr>';	
		echo'</table><br>';
	
	echo '<div class="centre">		
			 <input type="submit" name="Search" value="查询" />';
	if (isset($_POST['Search'])OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])){
		echo' <input type="submit" name="Confirm" value="发料确认" />';
	}
	echo'</div>';		
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
							narrative,
							decimalplaces,
							itemsid,
							issueqty,
							issuetab
						FROM stockmoves a		
						LEFT JOIN stockmaster d ON	a.stockid = d.stockid
						LEFT JOIN custname_reg_sub c ON a.debtorno=c.regid		
						WHERE (type=25 OR type=17 OR type=45)
						 AND trandate>='".$_POST['AfterDate']."'
						 AND trandate<='".$_POST['BeforDate']."'";
		    if ($_POST['IssueStatus']==1){
				//待收货AND  stockrequest.authorised=1 AND stockrequestitems.completed=0
					$sql.=" AND  issuetab=0 ";
		
			}elseif ($_POST['IssueStatus']==2){
				//已经完成AND  stockrequest.authorised=1 AND ( stockrequestitems.completed=2 OR  stockrequestitems.completed=1) ";
				$sql.=" AND  issuetab<>0 ";
			}
			
			if (isset($_POST['RemarkKeys']) AND mb_strlen($_POST['RemarkKeys'])>0) {
				//insert wildcard characters in spaces
				$_POST['RemarkKeys'] = mb_strtoupper($_POST['RemarkKeys']);
				$RemarkKeys = '%' . str_replace(' ', '%', $_POST['RemarkKeys']) . '%';
				$sql.=" AND narrative " . LIKE . " '$RemarkKeys'";
			}
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
				$sql.=" AND d.description " . LIKE . " '$SearchString'";
			}
			if (isset($_POST['StockCode']) AND mb_strlen($_POST['StockCode'])>0) {
				$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
				$sql.=" AND d.stockid " . LIKE . " '%" . $_POST['StockCode'] . "%'";
			}
			
			if ($_POST['PurchNo']!="" ){
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
			
			$sql.=" ORDER BY transno,c.custname";
			
			$result=DB_query($sql);	
			$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
			include('includes/footer.php');
			exit;
		}
	
	if (isset($_POST['Search'])OR isset($_POST['Go'])	OR isset($_POST['Next'])
		OR isset($_POST['Previous'])OR isset($_POST['Confirm'])) {
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
		if (isset($_POST['Confirm'])){
			$WoStk=explode(':',$_POST['Department']);// wo  wono
			if ($WoStk[0]==1){//部门
				$connectid=$WoStk[1];//部门id

				$woitype=39;
			}else{
				$woitype=28;
				$connectid=$WoStk[1];//工作单id
			}
			if (count($_POST['Select'])>0){
			
					/************************ BEGIN SQL TRANSACTIONS ************************/
						$Result = DB_Txn_Begin();
						/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
						$WOIssueNo = GetNextTransNo($woitype, $db);
						$SQLIssuedDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']." h:i:s"));
						
					foreach($_POST['Select'] as $WOLine){
						/* Need to get the current location quantity will need it later for the stock movement */
						$SQL="SELECT locstock.quantity
									FROM locstock
									WHERE locstock.stockid='" . $_POST['StockID'.$WOLine]  . "'
									AND loccode= '" . $_POST['StockLocation'] . "'";
						$QuantityIssued = $_POST['Qty'.$WOLine];
						$Result = DB_query($SQL);
						if (DB_num_rows($Result)==1){
							$LocQtyRow = DB_fetch_row($Result);
							$NewQtyOnHand = ($LocQtyRow[0] - $QuantityIssued);
							if ($NewQtyOnHand < $VarianceAllowed) {
								$NewQtyOnHand = 0;
							}
						} else {
						/*There must actually be some error this should never happen */
							$NewQtyOnHand = 0;
						}
			
							$SQL = "UPDATE locstock
									SET quantity = locstock.quantity - " . $_POST['Qty'.$WOLine] . "
									WHERE locstock.stockid = '" . $_POST['StockID'.$WOLine] . "'
									AND loccode = '" .$_POST['StockLocation'] . "'";
			
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
							$DbgMsg =  _('The following SQL to update the location stock record was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							
					    //Need to get the current standard cost for the item being issued
						$SQL = "SELECT materialcost+labourcost+overheadcost AS cost,
										controlled,
										serialised,
										decimalplaces,
										mbflag
									FROM stockmaster
									WHERE stockid='" .$_POST['IssueItem'] . "'";
						$Result = DB_query($SQL);
						$IssueItemRow = DB_fetch_array($Result);
						//now lets get the decimalplaces needed
						if ($IssueItemRow['decimalplaces'] <=4) {
							$VarianceAllowed = 0.0001;
						} else {
							$VarianceAllowed = pow(10,-$IssueItemRow['decimalplaces']);
						}
					
						$SQL="SELECT newqoh,
									newamount
								FROM  stockmoves
								WHERE  stockid='" . $_POST['StockID'.$WOLine] . "'
								AND stkmoveno IN (SELECT  stkmoveno	FROM   stockmoves 	GROUP BY stockid	HAVING   MAX(stkmoveno))";
								$Result = DB_query($SQL);
								$LocRow = DB_fetch_assoc($Result);
								//存货价格
								if (empty($LocRow['newqoh'])){
									$OldQoh = 0;
									$OldAmount=0;
			
								}else{
									$Old4Qoh = $LocRow['newqoh'];
									$OldAmount = $LocRow['newamount'];
								} 
								/*
								if ($_SESSION['InventoryCostMethod']==1){
									//移动加权平均法
									if ($OldAmount==0){
										$Price=$IssueItemRow['cost'] ;
									}else{
										$Price=round($OldAmount/$OldQoh,2);
									}
								}else{
									*/
									
									$Decimal=strlen((int)$_POST['Qty'.$WOLine]);
									if ($Decimal<=$_SESSION['StandardCostDecimalPlaces'] ){
										$Decimal=$_SESSION['StandardCostDecimalPlaces'];
									}else{
										$Decimal=strlen((int)$_POST['Qty'.$WOLine]);
									}
									$IssuePrice=round($_POST['TaxPrice'.$WOLine]/(1+$_POST['TaxRate'.$WOLine]),$Decimal ); 
									$Price=$IssueItemRow['cost'] ;
								//}
						/*Insert stock movements - with unit cost */
						//transo 发料单号  connectid  工作单号
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
														connectid,
														itemsid)
													VALUES ('" . $_POST['StockID'.$WOLine] . "',
															$woitype,
															'" . $WOIssueNo . "',
															'" . $_POST['StockLocation'] . "',
															'" . $SQLIssuedDate . "',
															'" . $_SESSION['UserID'] . "',
															'" . $IssuePrice . "',
															'0',
															'" . $_POST['WO'] . "',
															'" . -$_POST['Qty'.$WOLine] . "',
															'" . $IssuePrice. "',
															'" . $NewQtyOnHand . "',
															'" . ($OldAmount +  $QuantityIssued*$IssuePrice ) . "',
															'".$connectid."',
															'" . -$_POST['ItemsID'.$WOLine] . "')";
			
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
						$DbgMsg =  _('The following SQL to insert the stock movement records was used');
						
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
					
						/*Get the ID of the StockMove... */
						$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
						$SQL="UPDATE `stockmoves`
									SET	issuetab =	'" . $WOIssueNo . "',
									    issueqty=issueqty+".$_POST['Qty'.$WOLine] ."
									WHERE `stkmoveno` ='".$WOLine."' ";
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);	
							
						/* Do the Controlled Item INSERTS HERE */
			
						if ($IssueItemRow['controlled'] ==1){
							//the form is different for serialised items and just batch/lot controlled items
							if ($IssueItemRow['serialised']==1){
								//serialised items form has multi select box of serial numbers that contains all the available serial numbers at the location selected
								foreach ($_POST['SerialNos'] as $SerialNo){
								/*  We need to add the StockSerialItem record and
									The StockSerialMoves as well */
								//need to test if the serialised item exists first already
									if (trim($SerialNo) != ""){
			
										$SQL = "UPDATE stockserialitems set quantity=0
														WHERE (stockid= '" . $_POST['StockID'.$WOLine] . "')
														AND (loccode = '" . $_POST['StockLocation'] . "')
														AND (serialno = '" . $SerialNo . "')";
										$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
										$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
										$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			
										/** end of handle stockserialitems records */
			
										/* now insert the serial stock movement */
										$SQL = "INSERT INTO stockserialmoves (stockmoveno,
																				stockid,
																				serialno,
																				moveqty)
													VALUES ('" . $StkMoveNo . "',
															'" . $_POST['StockID'.$WOLine] . "',
															'" . $SerialNo . "',
															-1)";
										$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
										$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
										$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
									}//non blank SerialNo
								} //end for all of the potential serialised entries in the multi select box
							} else { //the item is just batch/lot controlled not serialised
							/*the form for entry of batch controlled items is only 15 possible fields */
								for($i=0;$i<$_POST['LotCounter'];$i++){
								/*  We need to add the StockSerialItem record and
									The StockSerialMoves as well */
									//need to test if the batch/lot exists first already
									if (trim($_POST['BatchRef' .$i]) != ""){
			
										$SQL = "SELECT COUNT(*) FROM stockserialitems
												WHERE stockid='" .$_POST['StockID'.$WOLine] . "'
												AND loccode = '" . $_POST['StockLocation'] . "'
												AND serialno = '" . $_POST['BatchRef' .$i] . "'";
										$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a batch/lot reference for the item already exists because');
										$DbgMsg =  _('The following SQL to test for an already existing controlled item was used');
										$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
										$AlreadyExistsRow = DB_fetch_row($Result);
			
										if ($AlreadyExistsRow[0]>0 AND $_POST['Qty'.$i] != 0){
											$SQL = "UPDATE stockserialitems SET quantity = CASE
																WHEN abs(quantity -" . $_POST['Qty' . $i] . ")<" . $VarianceAllowed . "
																THEN 0 
																ELSE  quantity - " . $_POST['Qty' . $i] . " 
																END
														WHERE stockid='" . $_POST['StockID'.$WOLine] . "'
														AND loccode = '" . $_POST['StockLocation'] . "'
														AND serialno = '" . $_POST['BatchRef' .$i] . "'";
										} elseif ($_POST['Qty'.$i] != 0) {
											$SQL = "INSERT INTO stockserialitems (stockid,
																loccode,
																serialno,
																qualitytext,
																quantity)
																VALUES ('" . $_POST['StockID'.$WOLine] . "',
																'" . $_POST['StockLocation'] . "',
																'" . $_POST['BatchRef' . $i] . "',
																'',
																'" . -(filter_number_format($_POST['Qty'.$i])) . "')";
										}
			
										$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The batch/lot item record could not be inserted because');
										$DbgMsg =  _('The following SQL to insert the batch/lot item records was used');
										$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			
										/** end of handle stockserialitems records */
			
										/** now insert the serial stock movement **/
										if ($_POST['Qty'.$i]!=0) {
											$SQL = "INSERT INTO stockserialmoves (stockmoveno,
															stockid,
															serialno,
															moveqty)
													VALUES ('" . $StkMoveNo . "',
															'" . $_POST['StockID'.$WOLine] . "',
															'" . $_POST['BatchRef'.$i]  . "',
															'" . filter_number_format($_POST['Qty'.$i])*-1  . "')";
											$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
											$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
											$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
										}
									}//non blank BundleRef
								} //end for all 15 of the potential batch/lot fields received
							} //end of the batch controlled stuff
						} //end if the woitem received here is a controlled item
					}//发料循环
						//update the wo with the new qtyrecd
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('Could not update the work order cost issued to the work order because');
						$DbgMsg = _('The following SQL was used to update the work order');
						if ($woitype==28){
							$SQL="UPDATE workorders
									SET costissued=costissued+" . ($QuantityIssued*$IssuePrice) . "
									WHERE wo='" . $WoStk[1] . "'";
							
							$Result =DB_query($SQL,	$ErrMsg,
														$DbgMsg,
														true);
						}
						
			
						$Result = DB_Txn_Commit();
						if ($woitype==28){
							$msg='简易发料单: ' .$WOIssueNo . ' 号,' . _('has been processed').'<a href="' . $RootPath . '/PDFIssueOrder.php?F=W&D=' . $WOIssueNo . '&StockID='.$WoStk[3];
						}else{
							$msg='易耗品发料单: ' .$WOIssueNo . ' 号,' . _('has been processed').'<a href="' . $RootPath . '/PDFIssueOrder.php?F=Y&D=' . $WOIssueNo ;
						}
			
						prnMsg($msg  .'"  target="_blank">点击打印</a>','success');
						/*
						echo '<p><ul>
							 
							   <li><a href="' . $RootPath . '/WorkOrderIssue.php?WONO=' .$WoStk[1].'-'. $_POST['WO'] . '&amp;StockID=' . $_POST['StockID'] . '">' . _('Issue more components to this work order') . '</a></li>';
						echo '<li><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select a different work order for issuing materials and components against'). '</a></li></ul>';
						*///unset($_POST['WO']);
						//unset($_POST['WONO']);
						
						//unset($_SESSION['WOI']);
						
						//unset($_POST['Commit']);
					//	unset($_POST['SerialNos']);
						for ($i=0;$i<$_POST['LotCounter'];$i++){
							unset($_POST['BatchRef'.$i]);
							unset($_POST['Qty'.$i]);
						}
					//	unset($_POST['Qty']);
						/*end of process work order issues entry */
						include('includes/footer.php');
						exit;
				}		
		
			//DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}	
			
		echo '<table width="90%" cellpadding="4"  class="selection">';
		echo'<tr>
		<td colspan="5">选择工作单或部门';
	//	$_POST['WorkLocation'] ='12';
	$sql="SELECT concat(workorders.wo,':',woitems.wono,':',woitems.stockid) departmentid,
			     concat('WO:',workorders.wo,'_',woitems.wono,' 产品 [',woitems.stockid,']',stockmaster.description) description,
			     2 issuetype				
				FROM workorders
				INNER JOIN woitems ON woitems.wo=workorders.wo						
				INNER JOIN stockmaster ON woitems.stockid=stockmaster.stockid
				WHERE workorders.closed IN (0,1)
				AND stockmaster.categoryid='" . $_POST['WorkLocation'] . "'
				UNION
				SELECT departmentid,
				description,
				'1' issuetype
			FROM departments";
	$Result=DB_query($sql);

	echo '<select name="Department">';	

	while ($myrow=DB_fetch_array($Result)){
		if (isset($_POST['Department']) AND $_POST['Department']==$myrow['departmentid']){
			echo '<option selected="True" value="' .$myrow['issuetype'].':'. $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="' .$myrow['issuetype'].':'. $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
	echo '</select></td>
		<td colspan="5"></td>
		</tr>';

		echo'<tr>
			<th >序号</th>
			<th >入库</br>单号</th>
			<th >供应商编码名称</th>
			<th >日期</th>					
			<th >合同号</br>计划单</th>
			<th >物料编码:名称</th>
			<th >数量</th>
			<th >税率%</th>
			<th >含税</br>价格</th>
			<th >金额</br>小计</th>						
			<th >摘要</th>	
			<th ></th>				
		</tr>';				
			$k=0;	//$rr=0	//$rw=1;	//$suppno='';	//$supacc='-1';	//$suptyp=2;	//$Resultarr=array();
			$Total=0;					
			$TotalAmo=0;			
			$TransNO=0;
			$SuppID=0;
			$RowIndex = 0;
		
	if($ListCount <> 0) {
	
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
		
				$URL_Edit= $RootPath .'/PDFPurchOrder.php?F=P&D=' . $row['transno'] ;
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
				<td >'.$row['stockid'].':'.$row['description'].'
				<input type="hidden" name="ItemsID'.$row['stkmoveno'].'" value="'.$row['itemsid'].'" />
					  <input type="hidden" name="StockID'.$row['stkmoveno'].'" value="'.$row['stockid'].'" />
					  <input type="hidden" name="TranaNo'.$row['stkmoveno'].'" value="'.$row['transno'].'" /></td>';
			echo'<td class="number">'.locale_number_format(round($row['qty'],2),$row['decimalplaces']).'
					<input type="hidden" name="Qty'.$row['stkmoveno'].'" value="'.(round($row['qty'],$row['decimalplaces'])).'" />
					<input type="hidden" name="DecimalPlaces'.$row['stkmoveno'].'" value="'.$row['decimalplaces'].'" /></td>
				<td class="number">'.($TaxRate*100).'
					<input type="hidden" name="TaxRate'.$row['stkmoveno'].'" value="'.$TaxRate.'" /></td>	
				<td class="number">'.locale_number_format($TaxPrice,2).'
					<input type="hidden" name="TaxPrice'.$row['stkmoveno'].'" value="'.$TaxPrice.'" /></td>
					<td class="number">'.locale_number_format($Total,2).'</td>		
					<td >'.$row['narrative'].'
					<input type="hidden" name="Narrative'.$row['stkmoveno'].'" value="'.$row['narrative'].'" /></td>
					<td >';
				if ($row['issuetab']==0){
					echo'<input type="checkbox" name="Select[]" value="'.($row['stkmoveno']).'" />';
				}else{
                    echo'发料单:'.$row['issuetab'];
				}
					echo'</td>																
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

	include('includes/footer.php');
	?>
