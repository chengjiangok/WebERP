<?php
/* $Id: WorkOrderIssue.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-09-20 09:33:21 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-09-11 15:54:15
 */
include('includes/DefineWorkOrderIssueClass.php');
include('includes/session.php');
$Title = _('Issue Materials To Work Order');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
function InDemandQty(p,d,s,r){
	
	var  n=p.name.substring(9);	
	//alert(s);

	var vl = document.getElementById("stdCost"+n);
	var vlCost=vl.value;
	var qty=(1*p.value).toFixed(d);
	if (parseFloat(p.value).toFixed(2)!=qty){
		p.value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}
	var total=0;
	if (vl.value!=""){	
		//数量不为空	
		total=(p.value*vlCost).toFixed(2);
		if (total!=0){
			document.getElementById("edit"+n).value=1;
		}else{
			document.getElementById("edit"+n).value=-1;
		}
		document.getElementById("CostAmount"+n).value=total;		
	}		
	var costtotal=0;
	for(var i=1; i<=r; i++){	
		costtotal+=parseFloat(document.getElementById("CostAmount"+i).value.replace(",",""));
	}
	//alert(costtotal);
	document.getElementById("CostAmoTotal").value =costtotal.toFixed(2);
}
</script>';	
if (isset($_GET['WONO'])){
	$_POST['WO']=explode('-',$_GET['WONO'])[1];
	$_POST['WONO']=explode('-',$_GET['WONO'])[0];
	$WoStock="WONO=".$_GET['WONO'];
}
if (isset($_GET['StockID'])){
	$_POST['StockID']=$_GET['StockID'];
	$WoStock.="&StockID=".$_GET['StockID'];
}

if (!isset($_POST['chkStockCode'])){
	$_POST['chkStockCode']=2;
}

echo '<a href="'. $RootPath . '/SelectWorkOrder.php">' . _('Back to Work Orders'). '</a>
	<br />';
//echo '<a href="'. $RootPath . '/WorkOrderCosting.php?WONO=' .  $_POST['WONO'] .'-'. $_POST['WO']. '">' . _('Back to Costing'). '</a>
//	<br />';

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' .
	_('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?'.$WoStock .  '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
//echo'<input type="hidden" name="chkStockCode" value="' . $_POST['chkStockCode'] . '" />';


if (!isset($_POST['WO']) OR !isset($_POST['StockID'])) {
	/* This page can only be called with a work order number for issuing stock to*/
	echo '<div class="centre"><a href="' . $RootPath . '/SelectWorkOrder.php">' .
		_('Select a work order to issue materials to') . '</a></div>';
	prnMsg(_('This page can only be opened if a work order has been selected. Please select a work order to issue materials to first'),'info');
	include ('includes/footer.php');
	exit;
} else {
	echo '<input type="hidden" name="WO" value="' .$_POST['WO'] . '" />';
	echo '<input type="hidden" name="StockID" value="' .$_POST['StockID'] . '" />';
}
if (isset($_GET['IssueItem'])){
	$_POST['IssueItem']=$_GET['IssueItem'];
}
if (isset($_GET['FromLocation'])){
	$_POST['FromLocation'] =$_GET['FromLocation'];
}
//发料存储
$InputError=true;
//更新缓存
if (isset($_POST['UpdateLines'])||isset($_POST['Commit'])){
	$_SESSION['WOI']->Update_WOIssue(	$WOLine->LineNo,
											$_POST['DemandQty'.$WOLine->LineNo],
											'',													
											0);		
	if(isset($_POST['Commit'])){
	//	unset($_SESSION['WOI']->LineItems);
	//	unset($_SESSION['WOI']);
	}
	if(isset($_POST['UpdateLines'])){
		//var_dump($_SESSION['WOI']->LineItems);
	}
}	
if (isset($_POST['Commit']) && $_SESSION['WOI']->LineCounter>0){ //user hit the process the work order issues entered.
	
	foreach ($_SESSION['WOI']->LineItems as $WOLine) {
		$_SESSION['WOI']->Update_WODemand($WOLine->StockID,
										$_POST['DemandQty'.$WOLine->LineNo]);	
			//prnMsg($WOLine->StockID.'='.$_POST['DemandQty'.$WOLine->LineNo].'-='.$_POST['CostAmount'.$WOLine->LineNo]);
		
		if ((float) $WOLine->stdCost<=0 ){
			$InputError=false;
		}
	}	
	//var_dump($InputError);
	if ($InputError==false){
		prnMsg('你要发出的物料没有价格,请设置价格后发料!','warn');
	}
	if ($InputError==true && $_SESSION['WOI']->LineCounter>0){		
			
			/************************ BEGIN SQL TRANSACTIONS ************************/

			$Result = DB_Txn_Begin();
			/*Now Get the next WO Issue transaction type 28 - function in SQL_CommonFunctions*/
			$WOIssueNo = GetNextTransNo(28, $db);

			//$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db); //backdate
			$SQLIssuedDate = FormatDateForSQL(Date($_SESSION['DefaultDateFormat']." h:i:s"));
			//$StockGLCode = GetStockGLCode($_POST['IssueItem'],$db);

		foreach ($_SESSION['WOI']->LineItems as $WOLine) {
			/* Need to get the current location quantity will need it later for the stock movement */
			$SQL="SELECT locstock.quantity
						FROM locstock
						WHERE locstock.stockid='" . $WOLine->StockID  . "'
						AND loccode= '" . $_POST['FromLocation'] . "'";
            $QuantityIssued = $WOLine->DemandQty;
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
						SET quantity = locstock.quantity - " . $WOLine->DemandQty . "
						WHERE locstock.stockid = '" . $WOLine->StockID . "'
						AND loccode = '" .$WOLine->LocCode . "'";

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
					WHERE  stockid='" . $WOLine->StockID . "'
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
					if ($_SESSION['InventoryCostMethod']==1){
						//移动加权平均法
						if ($OldAmount==0){
							$Price=$IssueItemRow['cost'] ;
						}else{
							$Price=round($OldAmount/$OldQoh,2);
						}
					}else{
						$Price=$IssueItemRow['cost'] ;
					}
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
											connectid)
										VALUES ('" . $WOLine->StockID . "',
												28,
												'" . $WOIssueNo . "',
												'" . $_POST['FromLocation'] . "',
												'" . $SQLIssuedDate . "',
												'" . $_SESSION['UserID'] . "',
												'" . $WOLine->stdCost . "',
												'0',
												'" . $_POST['WO'] . "',
												'" . -$WOLine->DemandQty . "',
												'" . $WOLine->stdCost. "',
												'" . $NewQtyOnHand . "',
												'" . ($OldAmount +  $QuantityIssued*$Price ) . "',
												'".$_POST['WONO']."')";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order issue because');
			$DbgMsg =  _('The following SQL to insert the stock movement records was used');
			
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			/*Get the ID of the StockMove... */
			$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
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
											WHERE (stockid= '" . $WOLine->StockID . "')
											AND (loccode = '" . $_POST['FromLocation'] . "')
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
												'" . $WOLine->StockID . "',
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
									WHERE stockid='" .$WOLine->StockID . "'
									AND loccode = '" . $_POST['FromLocation'] . "'
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
											WHERE stockid='" . $WOLine->StockID . "'
											AND loccode = '" . $_POST['FromLocation'] . "'
											AND serialno = '" . $_POST['BatchRef' .$i] . "'";
							} elseif ($_POST['Qty'.$i] != 0) {
								$SQL = "INSERT INTO stockserialitems (stockid,
													loccode,
													serialno,
													qualitytext,
													quantity)
													VALUES ('" . $WOLine->StockID . "',
													'" . $_POST['FromLocation'] . "',
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
												'" . $WOLine->StockID . "',
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
			$UpdateWOResult =DB_query("UPDATE workorders
										SET costissued=costissued+" . ($QuantityIssued*$IssueItemRow['cost']) . "
										WHERE wo='" . $_POST['WONO'] . "'",
										$ErrMsg,
										$DbgMsg,
										true);


			$Result = DB_Txn_Commit();

			prnMsg('发料单: ' .$WOIssueNo . ' 号,' . _('has been processed').'
			      <a href="' . $RootPath . '/PDFIssueOrder.php?F=W&D=' . $WOIssueNo . '&StockID='.$_POST['StockID'].'"  target="_blank">点击打印</a>','success');
			echo '<p><ul>
			     
				   <li><a href="' . $RootPath . '/WorkOrderIssue.php?WONO=' .$_POST['WONO'].'-'. $_POST['WO'] . '&amp;StockID=' . $_POST['StockID'] . '">' . _('Issue more components to this work order') . '</a></li>';
			echo '<li><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select a different work order for issuing materials and components against'). '</a></li></ul>';
			//unset($_POST['WO']);
			//unset($_POST['WONO']);
			
			unset($_SESSION['WOI']);
			
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
} //end of if the user hit the process button



/* Always display quantities received and recalc balance for all items on the order */

$ErrMsg = _('Could not retrieve the details of the selected work order item');
	$SQL="SELECT stockmaster.categoryid loccode,
				locations.locationname,
				workorders.requiredby,
				workorders.startdate,
				workorders.closed,
				stockmaster.stockid,
				stockmaster.description,
				stockmaster.decimalplaces,
				stockmaster.units,
				woitems.qtyreqd,
				woitems.qtyrecd,
				woitems.qtyreqd,
				woitems.diff
				FROM workorders
				INNER JOIN woitems
				ON workorders.wo=woitems.wo
				INNER JOIN stockmaster
				ON woitems.stockid=stockmaster.stockid
				INNER JOIN locations
				ON stockmaster.categoryid=locations.loccode				
				WHERE woitems.wo ='" . $_POST['WO'] . "'";
			$WOResult = DB_query($SQL,	$ErrMsg);

if (DB_num_rows($WOResult)==0){
	prnMsg(_('The selected work order item cannot be retrieved from the database'),'info');
	include('includes/footer.php');
	exit;
}

/*
if (!isset($_POST['IssuedDate'])){
	$_POST['IssuedDate'] = Date($_SESSION['DefaultDateFormat']);
}*/
$WORow = DB_fetch_array($WOResult);
//BOM检验
$sql="SELECT `component` stockid, description, `categoryid` loccode, b.`units`, `quantity`,decimalplaces,`actualcost`, `lastcost`,( `materialcost`+ `labourcost`+ `overheadcost`) cost 
             FROM `bom` a 
             LEFT JOIN stockmaster b ON component=stockid 
			 WHERE trim(`parent`)='" . $_POST['StockID'] . "'";
$BomResult=DB_query($sql);
//prnMsg($sql);
$BOMCount=DB_num_rows($BomResult);

//检查工作单是否生成配料表
$sql="SELECT a.stockid, `qtypu`, `stdcost`, `autoissue`, description, `categoryid` loccode, b.`units`, decimalplaces,`actualcost`, `lastcost`,( `materialcost`+ `labourcost`+ `overheadcost`) cost 
             FROM `worequirements` a 
			 LEFT JOIN stockmaster b ON a.stockid=b.stockid
			 WHERE wo='" . $_POST['WO'] . "'";
$RequireResult=DB_query($sql);
$RequireCount=DB_num_rows($RequireResult);
//prnMsg("BOMCount;".$BOMCount.' -'.$RequireCount);
if (isset($_SESSION['WOI']) &&$_POST['WONO']!=$_SESSION['WOI']->WONO){
	unset($_SESSION['WOI']);
}
if (!isset($_SESSION['WOI'])) {
	
	$_SESSION['WOI'] = new WOIssue;
	$_SESSION['WOI']->WONO =$_POST['WONO'];	
	$_SESSION['WOI']->WO =$_POST['WO'];	
	$_SESSION['WOI']->$WOStockID = $_POST['StockID'];
	$_SESSION['WOI']->WOQty=$WORow['qtyreqd'];
	$_SESSION['WOI']->WOReceiveQty=$WORow['qtyrecd'];	
	$_SESSION['WOI']->Units=$WORow['units'];
	//$_SESSION['WOI']->BOMFlag=$BOMCount>0?1:0;
	$_SESSION['WOI']->BatchingList=$RequireCount>0?1:0;

} //end if initiating a new 
if( ($RequireCount>=1|| $BOMCount>=1)  && $_SESSION['WOI']->BOMFlag<0) {
	//prnMsg("读取BOM或配料单");
	if ($RequireCount>=1 ){
		prnMsg("配料单");
		$_SESSION['WOI']->BOMFlag=1;
		while($row=DB_fetch_array($RequireResult)){
			$sql="SELECT  SUM(`qty`) qty  ,SUM(qty*price) issuecost FROM `stockmoves` WHERE type=28 AND connectid='".$_POST['WONO']."'  AND stockid='".$row['stockid']."'";
			$result=DB_query($sql);
			if (DB_num_rows($result)>0){
				$IssueQty=$qtyrow['qty'];
				$IssueCost=$qtyrow['issuecost'];
			}else{
				$IssueQty=0;
				$IssueCost=0;
			}
			$qtyrow=DB_fetch_assoc($result);
			$_SESSION['WOI']->add_to_woissue ($_SESSION['WOI']->LineCounter+1,
												$row['stockid'],
												$row['description'],
												$row['loccode'],
												'',//$LocName,	
												$row['qtypu'],	//配料单$BOMQty,BOM计算数	
												$row['units'],
												$IssueQty,//已经发料
												$IssueCost,//发货成本小计
												0,
												0,	
												0,//$NewQty,	//$DemandQty,次发料数														
												'',//摘要
												$row['cost'],
												$row['decimalplaces'],
												0);
		}
	}elseif($BOMCount>=1){
		prnMsg("读取BOM");
		$_SESSION['WOI']->BOMFlag=2;
		while($row=DB_fetch_array($BomResult)){
			$sql="SELECT  SUM(`qty`) qty  ,SUM(qty*price) issuecost FROM `stockmoves` WHERE type=28 AND connectid='".$_POST['WONO']."'  AND stockid='".$row['stockid']."'";
			$result=DB_query($sql);
		    $result=DB_query($sql);
			if (DB_num_rows($result)>0){
				$IssueQty=$qtyrow['qty'];
				$IssueCost=$qtyrow['issuecost'];
			}else{
				$IssueQty=0;
				$IssueCost=0;
			}
			$qtyrow=DB_fetch_assoc($result);
				$_SESSION['WOI']->add_to_woissue ($_SESSION['WOI']->LineCounter+1,
													$row['stockid'],
													$row['description'],
													$row['loccode'],
													'',//$LocName,	
													round($row['quantity']*$_SESSION['WOI']->WOQty,$row['decimalplaces']),	//BOM计算数	
													$row['units'],
													$IssueQty,//已经发料
													$IssueCost,//发货成本小计
													0,
													0,	
													0,//$NewQty,	//$DemandQty,次发料数														
													'',//摘要
													$row['cost'],
													$row['decimalplaces'],
													0);
		}
	}else{
		prnMsg("无BOM");
		$_SESSION['WOI']->BOMFlag=0;
	
			
		

	}
}else{
	//没有bom  没有配料单
	$_SESSION['WOI']->BOMFlag=0;
}
//prnMsg($_SESSION['WOI']->BOMFlag);
//读取发料
if (isset($_POST['AddIssueItens'])){

	foreach ($_POST as $FormVariableName =>$Quantity) {
	
		if (mb_substr($FormVariableName, 0, 6)=='NewQty' AND filter_number_format($Quantity)!=0) { //if the form variable represents a Qty to add to the order
			$n=mb_substr($FormVariableName, 6);
			$ItemCode = $_POST['StockID' .$n ];
			$Description = $_POST['Description' . $n];
			$Decimalplaces=$_POST['Decimalplaces' . $n];
			$Units = $_POST['Units' . $n];
			$Cost = $_POST['Cost' . $n];
			$LocCode = $_POST['LocCode' .$n ];
			$NewQty=$_POST['NewQty'.$n];
		
           //prnMsg($ItemCode.$Description.'-'.$n.'='.$Units.';;'.$NewQty);
						
			$DeliveryDate = DateAdd(Date($_SESSION['DefaultDateFormat']),'d',$LeadTime);
			if (Date1GreaterThanDate2($_SESSION['WOI']->DeliveryDate,$DeliveryDate)){
				$DeliveryDate = $_SESSION['WOI']->DeliveryDate;
			}
			
			
			$_SESSION['WOI']->add_to_woissue ($_SESSION['WOI']->LineCounter+1,
													$ItemCode,
													$Description,
													'',//$LocCode,
													'',//$LocName,	
													0,	//配料单$BOMQty,BOM计算数	
													$Units,
													0,//$IssueQty,//已经发料
													0,
													0,
													0,	
													$NewQty,	//$DemandQty,次发料数														
													'',//摘要
													$Cost,
													$Decimalplaces,
													0);
				
		
		} /* end if the $_POST has NewQty in the variable name */
	} /* end loop around the $_POST array */

} /* end of if its a new item */

if (isset($_GET['Delete'])){
//	prnMsg($_GET['Delete']);
	$_SESSION['WOI']->remove_from_woissue($_GET['Delete']);
//	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?'.$WoStock .  '" method="post">';
	
}
//发出物料到工作单
echo '<table class="selection">
		<tr>
			<td class="label">工作单号:</td>
			<td>' .$_POST['WONO'].'-'. $_POST['WO']  . '</td>
		</tr>
		<tr>
			<td class="label">工作单属于:</td>
			<td>' . $WORow['locationname'] . '</td>
			<td class="label">开始日期:</td>
			<td>' . ConvertSQLDate($WORow['requiredby']) . '</td>
		</tr>
		<tr>
			<td class="label">' . ('Item') . '</td>
			<td class="label">' . _('Quantity Ordered') . ':</td>
			<td class="label">' . _('Already Received') . ':</td>
			<td class="label">' . _('Unit') . ':</td>
		</tr>';

if ($WORow['closed']==1){
	prnMsg(_('The selected work order has been closed and variances calculated and posted. No more issues of materials and components can be made against this work order.'),'info');
	include('includes/footer.php');
	exit;
}
DB_data_seek($WOResult,0);

while($WORow = DB_fetch_array($WOResult)){

	echo  '<tr>
				<td>' . $WORow['stockid'] . ' - ' . $WORow['description'] . '</td>
				<td class="number">' . locale_number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '</td>
				<td class="number">' . locale_number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '</td>
				<td>' . $WORow['units'] . '</td>
			</tr>';
}

echo '<tr>
		<td class="label">' . _('Date Material Issued') . ':</td>
		<td><input type="text" name="IssuedDate" value="' . Date($_SESSION['DefaultDateFormat']) . '" class="date" size="10" alt="'.$_SESSION['DefaultDateFormat'].'" /></td>
		<td class="label">' . _('Issued From') . ':</td>
		<td>';

if (!isset($_POST['IssueItem'])){
	$LocResult = DB_query("SELECT locations.loccode,
	                               locationname
							FROM locations
							INNER JOIN locationusers
								ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "'
								AND locationusers.canupd=1
							WHERE locations.usedforwo = 0");
	echo '<select name="FromLocation">';
		while ($LocRow = DB_fetch_array($LocResult)){
			if ($_POST['FromLocation'] ==$LocRow['loccode']){
				echo '<option selected="selected" value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'] . '</option>';
			}
		}
	echo '</select>';
} else {
	$LocResult = DB_query("SELECT loccode, locationname
						FROM locations
						WHERE loccode='" . $_POST['FromLocation'] . "'");
	$LocRow = DB_fetch_array($LocResult);
	echo '<input type="hidden" name="FromLocation" value="' . $_POST['FromLocation'] . '" />';
	echo $LocRow['locationname'];
}
echo '</td>
	</tr>';

echo'<tr>
		<td class="label">配料单:</td>
		<td>'.	 ($RequireCount>0?"有":"无").'</td>
		<td class="label">BOM:</td>
		<td>'.	 ($BOMCount>0?$BOMCount."种物料":"无BOM").'</td>
		</tr>';

echo'</table>
	<br />';

//显示缓存数据
			echo '<p class="page_title_text">
					<img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />
					 该产品使用的物料</p>';				
			echo'<table class="selection">
					<tr>
				    	<th>序号</th>
						<th class="ascending" >' . _('Code') . '/' . _('Description') . '</th>
						<th>' . _('Units') . '</th>
						<th class="ascending" >' . _('On Demand') . '</th>
						<th class="ascending" >已发料数</th>
						<th class="ascending" >已发料成本</th>
						<th class="ascending" >' . _('Available') . '</th>
						<th class="ascending" >成本价格</th>
						<th class="ascending" >' . _('Quantity') . '</th>
						<th class="ascending" >成本金额</th>
						<th></th>
					</tr>';
	$k = 0;  //row colour counter
	$CostTotal=0;
	foreach ($_SESSION['WOI']->LineItems as $WOLine) {
			$sql="SELECT sum( `quantity` ) qty FROM `locstock` WHERE stockid='". $WOLine->StockID ."'";
			$result=DB_query($sql);
			$qtyrow=DB_fetch_assoc($result);
			$LineTotal = $WOLine->DemandQty* $WOLine->stdCost;	
			//读取入库产品 已经结转的人工费 制造费用
			$sql="SELECT SUM(qty) qty ,SUM(standardcost*qty) qtycost FROM stockmoves WHERE type=28  AND connectid='".$_SESSION['WOI']->WONO."' AND  stockid='". $WOLine->StockID."'";
			$result=DB_query($sql);
			
			//已经发出 材料
			$issueqty=array();
		
			if (DB_num_rows($result)>0){
				//if ($_SESSION['WOI']->BOMFlag==0){
				while($row=DB_fetch_array($result)){
					$issueqty[ $WOLine->StockID]=array($row['qty'],$row['qtycost']);
				}	
				$IssueQty=$issueqty[ $WOLine->StockID][0];				
			}else{
				$IssueQty=0;
			}		
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			$DemandQty=0;
			$CostAmount=0;
			if ($_SESSION['WOI']->BOMFlag==0){
				$DemandQty=$WOLine->DemandQty;
				$CostAmount=$WOLine->stdCost*($WOLine->DemandQty);
			}else{
				if(($WOLine->BOMQty+$IssueQty)>0){
					$DemandQty=$WOLine->BOMQty+$IssueQty;
					$CostAmount=$WOLine->stdCost*($WOLine->BOMQty+$IssueQty);

				}
			}
			//locale_number_format(round($WOLine->Quantity,$WOLine->DecimalPlaces),$WOLine->DecimalPlaces) 
			echo'<td>' . $WOLine->LineNo .$_SESSION['WOI']->BOMFlag.'</td>
				 <td>' . $WOLine->StockID .':'.$WOLine->Description   .'
			    	<input type="hidden" name="StockID' . $WOLine->LineNo . '" value="' . $WOLine->StockID  . '">
					<input type="hidden" name="ItemDescription' . $WOLine->LineNo . '" value="' . stripslashes($WOLine->Description) . '"></td>
			
				<td>' . $WOLine->Units . '<input type="hidden" name="UOM' . $WOLine->LineNo . '" value="' . $WOLine->Units . '"></td>
				<td><input type="text" class="number" id="BOMQty' . $WOLine->LineNo .'"   name="BOMQty' . $WOLine->LineNo .'"  onChange="inQTY(this,'.$WOLine->DecimalPlaces .' ,'.$rw.' )"  size="5" value="' .locale_number_format( $WOLine->BOMQty,$WOLine->DecimalPlaces). '" /></td>
					<input type="hidden" id="edit' . $WOLine->LineNo . '" name="edit' . $WOLine->LineNo . '" value="0">';
			
			echo'<td><input type="text" class="number" id="IssueQty' . $WOLine->LineNo . '" name="IssueQty' . $WOLine->LineNo . '"  size="7" value="' .locale_number_format(-$issueqty[ $WOLine->StockID][0],$_SESSION['WOI']->DecimalPlaces) .'" readonly="readonly" /></td>
				<td><input type="text" class="number" id="IssueCost' . $WOLine->LineNo . '" name="IssueCost' . $WOLine->LineNo . '"  size="7" value="' .locale_number_format(-$issueqty[ $WOLine->StockID][1],2) .'" readonly="readonly" /></td>
				<td><input type="text" class="number" id="Available' . $WOLine->LineNo . '" name="Available' . $WOLine->LineNo . '"  size="7" value="' .locale_number_format( $qtyrow['qty'],$_SESSION['WOI']->DecimalPlaces) .'" readonly="readonly" /></td>
				<td><input type="text" class="number" id="stdCost' . $WOLine->LineNo . '" name="stdCost' . $WOLine->LineNo . '"  size="7" value="' .locale_number_format($WOLine->stdCost,2) .'" readonly="readonly" /></td>
				<td><input type="text" class="number" size="10" id="DemandQty' . $WOLine->LineNo . '"  name="DemandQty' . $WOLine->LineNo . '"  onChange="InDemandQty(this ,'.$WOLine->DecimalPlaces.','.$IssueQty.','.$_SESSION['WOI']->LineCounter.' )"  value="' . locale_number_format($DemandQty,$WOLine->DecimalPlaces) .'" /></td>
					
				<td><input type="text" class="number" id="CostAmount' . $WOLine->LineNo . '" name="CostAmount' . $WOLine->LineNo . '" maxlength="20" size="10" value="' .locale_number_format($CostAmount,2) .'" readonly="readonly" />
					<input type="hidden" id="edit' . $WOLine->LineNo . '" name="edit' . $WOLine->LineNo . '" value="0"></td>';
					
			echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?'.$WoStock.'&Delete=' . $WOLine->LineNo . '">' . _('Delete'). '</a></td>
			      </tr>';			
			
			
			$CostTotal += $LineTotal;	
	}

	//$DisplayTotal = locale_number_format($_SESSION['WOI']->Total,$_SESSION['WOI']->CurrDecimalPlaces);
	/*
	echo '<tr><td></td>
	        <td colspan="7" class="number">合计</td>				
			<td><input type="text"  class="number"  id= "CostAmoTotal" maxlength="20" size="10" value="'. $CostTotal. '" readonly="readonly" /></td>
		
			<td></td>
			</tr>
		';*/
	echo '<tr>
			<td></td>				
			<td colspan="3"><hr/></td>
			<td >合计</td>
			<td class="number"><input type="text" class="number" id="IssueCost"   name="IssueCost"    size="7" value="' . locale_number_format($TotalIssuedCost,$_SESSION['CompanyRecord']['decimalplaces'])   . '" ></td>
			
			<td ><hr /></td>
			<td class="number"><input type="text" class="number" id="SettleCost"   name="SettleCost"  size="7" value="' . locale_number_format($TotalIssuedCost,$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly ></td>
			<td colspan="1" ><hr /></td>
			<td class="number"><input type="text" class="number" id="CostAmoTotal"   name="CostAmoTotal"   size="10" value="' . locale_number_format($CostTotal,$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly ></td>
			<td></td>	
		</tr>
		</table>';
	echo '<br />
			<div class="centre">
			<input type="submit" name="UpdateLines" value="更新缓存" />';
	echo '&nbsp;<input type="submit" name="Commit" value="执行发料" />
			</div>';
		


    if ($RequireCount==0 && $BOMCount==0) {
		echo '<br /><div class="centre">' ;//. $msg;
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ';
		echo '查找物料</p></div>';
		//echo '<div class="page_help_text">' . _('Search for Order Items') . _(', Searches the database for items, you can narrow the results by selecting a stock category, or just enter a partial item description or partial item code') . '.</div><br />';
		echo '<table class="selection">
				<tr>
					<td><b>' . _('Select a Stock Category') . ': </b>
					<select tabindex="1" name="StockCat">';
                /*
				if (!isset($_POST['StockCat']) OR $_POST['StockCat']=='All'){
					echo '<option selected="selected" value="All">' . _('All') . '</option>';
					$_POST['StockCat'] = 'All';
				} else {
					echo '<option value="All">' . _('All') . '</option>';
				}*/
				/*
				$SQL="SELECT categoryid,
								categorydescription
						FROM stockcategory
						WHERE stocktype='B' AND categoryid IN (SELECT loccode FROM locationusers WHERE userid='".$_SESSION['UserID']."')
						ORDER BY categorydescription";
				//	WHERE stocktype='F' OR stocktype='D' OR stocktype='L'
				*/
				$SQL = "SELECT DISTINCT
								a.`loccode`,
								locationname
							FROM
								`stockcategorylocation` a
							LEFT JOIN locations b ON
								a.loccode = b.loccode
							INNER JOIN locationusers c ON
								c.loccode = a.loccode AND c.userid ='" .  $_SESSION['UserID'] . "'  AND c.canview = 1
							WHERE
								b.usedforwo = 1 AND a.mbflag = 'B'";
				$result1 = DB_query($SQL);
				while ($myrow1 = DB_fetch_array($result1)) {
					if ($_POST['StockCat']==$myrow1['loccode']){
						echo '<option selected="selected" value="' . $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
					} else {
						echo '<option value="'. $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
					}
				}

		echo '</select></td>
			<td><b>' . _('Enter partial Description') . ':</b>
			<input tabindex="2" type="text" name="Keywords" size="20" maxlength="25" value="' ;

        if (isset($_POST['Keywords'])) {
             echo $_POST['Keywords'] ;
        }
        echo '" /></td></tr>';
          
		echo '<tr>
		         <td></td>
		         <td align="right"><b>' . _('OR') .  ' ' . _('Enter extract of the Stock Code') . ':</b>
		          <input tabindex="3" type="text" ' . (!isset($_POST['PartSearch']) ? 'autofocus="autofocus"' :'') . ' name="StockCode" size="15" maxlength="18" value="';
        if (isset($_POST['StockCode'])) {
            echo  $_POST['StockCode'];
        }
		echo '" /></td>';
		echo '<tr>
	
				<td colspan="2"  style="text-align:center" >采购入库
				         <input type="checkbox"  name="chkStockCode" id="chkStockCode"  value="1"    '.($_POST['chkStockCode']==1?"checked":"").'   /></td>
			';
		echo '<tr>
			<td   colspan="1" style="text-align:center">
			    <input tabindex="4" type="submit" name="Search" value="' . _('Search Now') . '" /></td>
				<td></td>
			';
        
        echo '</tr>
			</table>
			<br />
			</div>';
	
/*User hit the search button looking for an item to issue to the WO */
if (isset($_POST['Search'])||isset($_POST['Next'])||isset($_POST['Previous'])) {
   // prnMsg($_POST['chkStockCode']);
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg(_('Stock description keywords have been used in preference to the Stock code extract entered'),'warn');
	}
	$SQL = "SELECT stockmaster.stockid,
					stockmaster.description,
					stockmaster.units,
					stockmaster.`actualcost`, 
					stockmaster.`lastcost`, 
					stockmaster.decimalplaces,
					(stockmaster.`materialcost`+	stockmaster.`labourcost`+ stockmaster.`overheadcost`) cost
					FROM stockmaster,stockcategory
					WHERE stockmaster.categoryid=stockcategory.categoryid
					AND (stockcategory.stocktype='B' OR stockcategory.stocktype='L' OR stockcategory.stocktype='M')
					AND stockmaster.discontinued=0
					AND (mbflag='B' OR mbflag='M' OR mbflag='D') ";
	if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		$SQL .= "	AND stockmaster.description " . LIKE . " '$SearchString' ";
		

	} 
	if (mb_strlen($_POST['StockCode'])>0){

		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
	
		if (strpos($_POST['StockCode'] ,",",1)>0){
			
			$SQL .= "	AND stockmaster.stockid IN (" .  $_POST['StockCode'] . ")";
		}elseif (strpos($_POST['StockCode'] ,"-",1)>0){
			$StartEnd =explode('-',  $_POST['StockCode'] );
			$SQL .= "	AND stockmaster.stockid>='" .  $StartEnd[0]. "' AND stockmaster.stockid<='" .  $StartEnd[1]. "' ";
		}else{
			$SearchString = '%' . $_POST['StockCode'] . '%';
			$SQL .= "	AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'";
		}	
	}
	if ($_POST['StockCat']!='All'){			
		$SQL .= "	AND stockmaster.categoryid='" . $_POST['StockCat'] . "'	";
	}
	if ($_POST['chkStockCode']==1){
		//以后需要添加日期限制前6个月的
		$SQL .=" AND stockmaster.stockid IN (SELECT DISTINCT `stockid` FROM `stockmoves` WHERE type=17 ) ";
	}
    // prnMsg($SQL);
	$ErrMsg = _('There is a problem selecting the part records to display because');
	$DbgMsg = _('The SQL used to get the part selection was');

	if (isset($_POST['Next'])) {
		$Offset = $_POST['NextList'];
	}
	if (isset($_POST['Previous'])) {
		$Offset = $_POST['PreviousList'];
	}
	if (!isset($Offset) OR $Offset < 0) {
		$Offset=0;
	}

	$SQL = $SQL . " ORDER BY  stockid LIMIT " . $_SESSION['DisplayRecordsMax'] . " OFFSET " . strval($_SESSION['DisplayRecordsMax'] * $Offset);

	$SearchResult = DB_query($SQL,$ErrMsg, $DbgMsg);
	if (DB_num_rows($SearchResult)==0 ){
		prnMsg (_('There are no products available meeting the criteria specified'),'info');

		if ($debug==1){
			prnMsg(_('The SQL statement used was') . ':<br />' . $SQL,'info');
		}
	}
	if (DB_num_rows($SearchResult)==1){
		$myrow=DB_fetch_array($SearchResult);
		$_POST['IssueItem'] = $myrow['stockid'];
		DB_data_seek($SearchResult,0);
	}
} //end of if search
		//增至销售订单 
		if (isset($SearchResult)) {
			echo '<br />';
			echo '<div class="page_help_text">选择物料及数量,然后增至发料单</div>';
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
			          <input name="AddIssueItens" type="hidden" value="1" />
					  <input tabindex="'.strval($j+9).'" type="submit" value="添加到发料单" /></td>';
			echo '<td colspan="1">
			          <input name="NextList" type="hidden" value="'.strval($Offset+1).'" />
			          <input tabindex="'.strval($j+10).'" name="Next" type="submit" value="'._('Next').'" /></td></tr>';
			echo '<tr>
					<th class="ascending" >' . _('Code') . '</th>
					<th>' . _('Description') . '</th>					
		   			<th>' . _('Units') . '</th>
		   			<th class="ascending" >' . _('On Hand') . '</th>
		   			<th class="ascending" >' . _('On Demand') . '</th>
					<th class="ascending" >' . _('On Order') . '</th>					 
					<th class="ascending" >' . _('Available') . '</th>
					<th class="ascending" >成本价格</th>
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
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s
							<input name="Cost%s" type="hidden" value="%s" /></td>
						<td>
						<input class="number" tabindex="%s" type="text" size="6" name="NewQty%s"  ' . ($i==0 ? 'autofocus="autofocus"':'') . ' value="" min="0"/>
						<input name="Decimalplaces%s" type="hidden" value="%s" />
						<input name="StockID%s" type="hidden" value="%s" />
						<input name="Description%s" type="hidden" value="%s" />
						<input name="Units%s" type="hidden" value="%s" />
						</td>
						</tr>',
						$myrow['stockid'],
						$myrow['longdescription'],
						$myrow['description'],					
						$myrow['units'],
						locale_number_format($QOH,$QOHRow['decimalplaces']),
						locale_number_format($DemandQty,$QOHRow['decimalplaces']),
						locale_number_format($OnOrder,$QOHRow['decimalplaces']),
						locale_number_format($Available,$QOHRow['decimalplaces']),
						$myrow['cost'],
						$i,
						$myrow['cost'],
						
						strval($j+7),
						$i,
						$i,
						$myrow['decimalplaces'],
						$i,
						$myrow['stockid'],
						$i,
						$myrow['description'],
						$i,
						$myrow['units'] );
				$i++;
				$j++;
			#end of page full new headings if
			}
				#end of while loop
			echo '<tr>
					<td><input name="PreviousList" type="hidden" value="'. strval($Offset-1).'" />
					     <input tabindex="'. strval($j+7).'" type="submit" name="Previous" value="'._('Previous').'" /></td>
					<td style="text-align:center" colspan="6">
					      <input name="AddIssueItens" type="hidden" value="1" />
					      <input tabindex="'. strval($j+8).'" type="submit" value="添加到发料单" /></td>
					<td>
					    <input name="NextList" type="hidden" value="'.strval($Offset+1).'" />
					     <input tabindex="'.strval($j+9).'" name="Next" type="submit" value="'._('Next').'" /></td>
				</tr>
				</table>
				</div>';
		}

	}#end if SearchResults to show

	
echo '</div>
      </form>';

include('includes/footer.php');

