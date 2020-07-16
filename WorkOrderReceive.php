<?php
/* $Id: WorkOrderIssue.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-09-20 09:33:21 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-28 16:54:06
 */
include('includes/DefineWorkOrderIssueClass.php');
include('includes/session.php');
$Title = _('Receive Work Order');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
function InCloseCheck(p,d,s){

	//alert(p.value);
}

function InReceive(p,d,s,r){
		var QtyReqd = document.getElementById("QtyReqd").value
		var QtyRecd = document.getElementById("QtyRecd").value;
		var Diff = document.getElementById("Diff").value;
	
	    if (QtyRecd==""||QtyRecd==null){
		  QtyReqcd=0;
		}
		var CloseCheck = document.getElementById("Close");
	
		var  rw=document.getElementById("worow").value;
		var bomqty=0;//定额数量
		var  stdcost=0 ;//成本单价
		var demandqty=0;
		var dp=0;
		var totalcost=0;

		var ratiolabour=0;
		var ratiooverhead=0;
		var totallo=0;
	
		if ((parseFloat(QtyReqd)*(1+Diff/100)-parseFloat(QtyRecd))>=parseFloat(p.value)){
			if ((parseFloat(QtyReqd)*(1-Diff/100)-parseFloat(QtyRecd))<=parseFloat(p.value)){
				CloseCheck.checked=true;
			}else{
				CloseCheck.checked=false;
			}
		 
		   for(var i=1; i<=rw; i++){
			  dp=parseInt(document.getElementById("DecimalPlaces"+i).value);
			
			  bomqty=parseFloat(document.getElementById("BOMQty"+i).value).toFixed(dp);
			  stdcost=parseFloat(document.getElementById("StandardCost"+i).value.replace(",",""));
			  demandqty=(bomqty/QtyReqd*p.value).toFixed(dp);
			  totalcost+=parseFloat(totalcost)+parseFloat(demandqty*stdcost).toFixed(2);
			  document.getElementById("DemandQty"+i).value=demandqty.toString();
			  document.getElementById("DemandCost"+i).value=(demandqty*stdcost).toFixed(2);
			
		   }
		   document.getElementById("TotalCost").value=parseFloat(totalcost).toFixed(2);
		  
		   document.getElementById("PrmLab").value=parseFloat(totalcost).toFixed(2);
		   document.getElementById("PrmOvh").value=parseFloat(totalcost).toFixed(2);

		   ratiolabour=document.getElementById("RatioLabour").value;
		   document.getElementById("TotalLabour").value=(parseFloat(totalcost)*ratiolabour).toFixed(2);
		   
		   
		   ratiooverhead=document.getElementById("RatioOverhead").value;
		   document.getElementById("TotalOverhead").value=(parseFloat(totalcost)*ratiooverhead).toFixed(2);
		   totallo=(parseFloat(totalcost)*ratiooverhead).toFixed(2)*1+1*(parseFloat(totalcost)*ratiolabour).toFixed(2);
		   document.getElementById("TotalLabOvh").value=totallo.toFixed(2);
		}else{
			alert("你接受的数量已经超过计划数:"+(QtyRecd+parseFloat(p.value)-QtyReqd).toString());
			p.value=QtyReqd-QtyRecd;
		}
	
}
</script>';	
/**elseif ((parseFloat(QtyReqd)*(1+Diff/100)-parseFloat(QtyRecd))==parseFloat(p.value)){
			//等于
			alert("wait");
		} */
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
	$_POST['chkStockCode']=1;
}


echo '<a href="'. $RootPath . '/SelectWorkOrder.php">' . _('Back to Work Orders'). '</a>
	<br />';
//echo '<a href="'. $RootPath . '/WorkOrderCosting.php?WONO=' .  $_POST['WONO'] .'-'. $_POST['WO']. '">' . _('Back to Costing'). '</a>


echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' .
	_('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?'.$WoStock .  '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	   <input type="hidden" name="chkStockCode" value="' . $_POST['chkStockCode'] . '" />';


if (!isset($_POST['WONO']) OR !isset($_POST['StockID'])) {
	/* This page can only be called with a work order number for issuing stock to*/
	echo '<div class="centre"><a href="' . $RootPath . '/SelectWorkOrder.php">' .
		_('Select a work order to issue materials to') . '</a></div>';
	prnMsg(_('This page can only be opened if a work order has been selected. Please select a work order to issue materials to first'),'info');
	include ('includes/footer.php');
	exit;
} else {
	echo '<input type="hidden" name="WONO" value="' .$_POST['WONO'] . '" />';
	echo '<input type="hidden" name="StockID" value="' .$_POST['StockID'] . '" />';
}
/*
if (isset($_GET['IssueItem'])){
	$_POST['IssueItem']=$_GET['IssueItem'];
}
if (isset($_GET['FromLocation'])){
	$_POST['FromLocation'] =$_GET['FromLocation'];
}
*/
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
				woitems.stdcost,
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
$sql="SELECT component stockid, description, categoryid loccode, b.units, quantity,decimalplaces,actualcost, lastcost,diff,( materialcost+ labourcost+ overheadcost) cost 
             FROM bom a 
             LEFT JOIN stockmaster b ON component=stockid 
			 WHERE trim(parent)='" . $_POST['StockID'] . "'";
$BomResult=DB_query($sql);
$BOMCount=DB_num_rows($BomResult);
//prnMsg($BOMCount.''.$sql);
//检查工作单是否生成配料表
$sql="SELECT a.stockid, qtypu, stdcost, autoissue, description, categoryid loccode, b.units, decimalplaces,actualcost, lastcost,( materialcost+ labourcost+ overheadcost) cost 
             FROM worequirements a 
			 LEFT JOIN stockmaster b ON a.stockid=b.stockid
			 WHERE wo='" . $_POST['WO'] . "'";
	$RequireResult=DB_query($sql);
$RequireCount=DB_num_rows($RequireResult);
if (isset($_SESSION['WOR']) && $_POST['WONO']!=$_SESSION['WOR']->WONO){
	unset($_SESSION['WOR']);
}
if (!isset($_SESSION['WOR'])) {
	
	$_SESSION['WOR'] = new WOIssue;
	$_SESSION['WOR']->WONO =$_POST['WONO'];	
	$_SESSION['WOR']->WO =$_POST['WO'];	
	$_SESSION['WOR']->WOStockID = $_POST['StockID'];
	$_SESSION['WOR']->WOQty=$WORow['qtyreqd'];
	$_SESSION['WOR']->WOReceiveQty=$WORow['qtyrecd'];	
	$_SESSION['WOR']->Units=$WORow['units'];
	$_SESSION['WOR']->Location=$WORow['loccode'];
	$_SESSION['WOR']->Diff =$WORow['diff'];

	$_SESSION['WOR']->BatchingList=$RequireCount>0?1:0;

} //end if initiating a new 
//prnMsg($_POST['IntoLocation'].'&&'. $_SESSION['WOR']->BOMFlag);
if( ($RequireCount>=1|| $BOMCount>=1)  && $_SESSION['WOR']->BOMFlag<0) {
	//prnMsg("读取BOM或配料单");
	if ($RequireCount>=1 ){
		prnMsg("配料单");
		$_SESSION['WOR']->BOMFlag=1;
		while($row=DB_fetch_array($RequireResult)){
			$sql="SELECT  SUM(qty) qty ,SUM(qty*standardcost) qtycost FROM stockmoves WHERE type=28 AND connectid='".$_POST['WONO']."'  AND stockid='".$row['stockid']."'";
			$result=DB_query($sql);
			$qtyrow=DB_fetch_assoc($result);
			$NewQty=0;//($row['qtypu']-$qtyrow['qty']),//
			$_SESSION['WOR']->add_to_woissue ($_SESSION['WOR']->LineCounter+1,
												$row['stockid'],
												$row['description'],
												$row['loccode'],
												'',//$LocName,	
												$row['qtypu'],	//配料单$BOMQty,BOM计算数	
												$row['units'],
												$qtyrow['qty'],//$IssueQty,//已经发料
												round($qtyrow['qtycost'],2),
												0,
												0,	
												$NewQty,	//$DemandQty,次发料数														
												'',//摘要
												$row['cost'],
												$row['decimalplaces'],
												0);
		}
	}else{
		prnMsg("读取BOM");
		$_SESSION['WOR']->BOMFlag=2;
		//DB_data_seek($BomResult,0);
		while($row=DB_fetch_array($BomResult)){
			$sql="SELECT  SUM(qty) qty ,SUM(qty*standardcost) qtycost FROM stockmoves WHERE type=28 AND connectid='".$_POST['WONO']."'  AND stockid='".$row['stockid']."'";
			$result=DB_query($sql);
			//prnMsg($sql);
			$qtyrow=DB_fetch_assoc($result);
			$NewQty=0;//round($row['quantity']*$_SESSION['WOR']->WOQty,$row['decimalplaces'])-	$qtyrow['qty'];
				$_SESSION['WOR']->add_to_woissue ($_SESSION['WOR']->LineCounter+1,
													$row['stockid'],
													$row['description'],
													$row['loccode'],
													'',//$LocName,	
													round($row['quantity']*$_SESSION['WOR']->WOQty,$row['decimalplaces']),	//BOM计算数	
													$row['units'],
													$qtyrow['qty'],//$IssueQty,//已经发料
													round($qtyrow['qtycost'],2),
													0,
													0,	
													$NewQty,	//$DemandQty,次发料数														
													'',//摘要
													$row['cost'],
													$row['decimalplaces'],
													0);
		//prnMsg($row['quantity'].'*'.$_SESSION['WOR']->WOQty.','.$qtyrow['qty'],'*',$row['cost']);
		}
	}
}else {
	//没有bom  没有配料单
	$_SESSION['WOR']->BOMFlag=0;
	if (count($_SESSION['WOR']->LineItems)==0){
	    $sql="SELECT a.stockid,b.description,b.categoryid loccode,b.units,b.decimalplaces, SUM(qty) qty ,SUM(qty*standardcost) qtycost FROM stockmoves a LEFT JOIN stockmaster b ON a.stockid=b.stockid WHERE type=28 AND connectid='".$_POST['WONO']."' GROUP BY a.stockid,b.description,b.units,b.decimalplaces,b.categoryid";
		//$sql="SELECT stockid, SUM(qty) qty ,SUM(qty*standardcost) qtycost FROM stockmoves WHERE type=28 AND connectid='".$_POST['WONO']."' GROUP BY stockid";
		$Result=DB_query($sql);
		//$qtyrow=DB_fetch_assoc($esult);
		$NewQty=0;//($row['qtypu']-$qtyrow['qty']),//
	while($row=DB_fetch_array($Result)){
		$_SESSION['WOR']->add_to_woissue ($_SESSION['WOR']->LineCounter+1,
											$row['stockid'],
											$row['description'],
											$row['loccode'],
											'',//$LocName,	
											0,//	//配料单$BOMQty,BOM计算数	
											$row['units'],
											$row['qty'],//$IssueQty,//已经发料
											round($row['qtycost'],2),
											0,
											0,	
											$NewQty,	//$DemandQty,次发料数														
											'',//摘要
											round($row['qtycost']/$row['qty'],2),//$row['cost'],
											$row['decimalplaces'],
											0);
	}
}
}
if (isset($_POST['Commit'])){ //user hit the process the work order receipts entered.
	
	//var_dump($_SESSION['WOR']->WorkItems);
	$IssueChk=0;
	foreach ($_SESSION['WOR']->LineItems as $WOLine) {
		if (round($WOLine->BOMQty*0.95,$WOLine->DecimalPlaces)>-$WOLine->IssueQty){
			$IssueChk=1;
			break;

		}
	}

	if ($InputError==false){
		/************************ BEGIN SQL TRANSACTIONS ************************/
		$_POST['LOLoccode'] =16;
		$Result = DB_Txn_Begin();
		/*Now Get the next WOReceipt transaction type 26 - function in SQL_CommonFunctions*/
		$WOReceiptNo = GetNextTransNo(26, $db);

		$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);

		if (!isset($_POST['ReceivedDate'])){
			$_POST['ReceivedDate'] = Date($_SESSION['DefaultDateFormat']." h:i:s");
		}

		$SQLReceivedDate = FormatDateForSQL($_POST['ReceivedDate']);
		$QuantityReceived = filter_number_format($_POST['QtyReceive']);
		//得到对应科目  废弃
		//$StockGLCode = GetStockGLCode($_POST['StockID'],$db);         
		//产品入库更新
		$SQL = "SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_POST['StockID'] . "'
				 AND loccode= '" . $_POST['IntoLocation'] . "'";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
		/*There must actually be some error this should never happen */
			$QtyOnHandPrior = 0;
		}

		$SQL = "UPDATE locstock
				SET quantity = locstock.quantity + " . $QuantityReceived . "
				WHERE locstock.stockid = '" . $_POST['StockID'] . "'
				AND loccode = '" . $_POST['IntoLocation'] . "'";
				//prnMsg($SQL);
		$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
		$DbgMsg =  _('The following SQL to update the location stock record was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		//产品人工费入库更新
		/*
		$SQL = "SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" .$_SESSION['WOR']->WorkItems['labourcode']. "'";
				//AND loccode= '" . $_POST['IntoLocation'] . "'";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$LabourQty = $LocQtyRow[0];
		} else {
	
			$LabourQty = 0;
		}
		*/
		//劳务
		$TotalLab=0;
	    if  (filter_number_format($_POST['TotalLabour'])!=0){
			$TotalLab= filter_number_format($_POST['TotalLabour']);
			$SQL = "UPDATE locstock
					SET quantity = locstock.quantity + " . $TotalLab. "
					WHERE locstock.stockid = '" . $_SESSION['WOR']->WorkItems['labourcode'] . "'";
				//	prnMsg($SQL);
			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
			$DbgMsg =  _('The following SQL to update the location stock record was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
		//产品制造费用000513
		/*
		$SQL = "SELECT locstock.quantity
				FROM locstock
				WHERE locstock.stockid='" . $_SESSION['WOR']->WorkItems['overheadcode']. "'";
				//AND loccode= '" . $_POST['IntoLocation'] . "'";

		$Result = DB_query($SQL);
		if (DB_num_rows($Result)==1){
			$LocQtyRow = DB_fetch_row($Result);
			$QtyOnHandPrior = $LocQtyRow[0];
		} else {
	
			$QtyOnHandPrior = 0;
		}
		*/
		$TotalOvh=0;
		if (filter_number_format($_POST['TotalOverhead'])!=0){
			//制造费用
			$TotalOvh= filter_number_format($_POST['TotalOverhead']);
			$SQL = "UPDATE locstock
					SET quantity = locstock.quantity + " . $TotalOvh . "
					WHERE locstock.stockid = '" .$_SESSION['WOR']->WorkItems['overheadcode']. "'";
					//prnMsg($SQL);
			$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
			$DbgMsg =  _('The following SQL to update the location stock record was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			//$WOReceiptNo = GetNextTransNo(26,$db);
			/*Insert stock movements - with unit cost */
		}
		//入库产品成本单价
		$StdCost=0.01;
		if ($_POST['TotalCost']+$TotalLab+$TotalOvh!=0){
		$StdCost=round(($_POST['TotalCost']+$TotalLab+$TotalOvh)/$QuantityReceived,2);
		}
         //产品入库更新
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
					VALUES ('" . $_POST['StockID'] . "',
							26,
							'" . $WOReceiptNo . "',
							'" . $_POST['IntoLocation'] . "',
							'" . Date('Y-m-d h:i:s') . "',
							'" . $_SESSION['UserID'] . "',
							'" . $WORow['stdcost'] . "',
							'" . $PeriodNo . "',
							'" . $_POST['WONO'] . "',
							'" . $QuantityReceived . "',
							'" . $StdCost . "',
							'" . ($QtyOnHandPrior + $QuantityReceived) . "',
							0,
							'" . $_POST['WONO'] . "')";
       
		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted when processing the work order receipt because');
		$DbgMsg =  _('The following SQL to insert the stock movement records was used');
		$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		//人工费
		if  (filter_number_format($_POST['TotalLabour'])!=0){
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
									VALUES ('" .$_SESSION['WOR']->WorkItems['labourcode'] . "',
									27,
									'" . $WOReceiptNo . "',
									'" . $_POST['LOLoccode'] . "',
									'" . Date('Y-m-d h:i:s') . "',
									'" . $_SESSION['UserID'] . "',
									'" . $WORow['stdcost'] . "',
									'" . $PeriodNo . "',
									'" . $_POST['WONO'] . "',
									'" . $_POST['TotalLabour'] . "',
									'1',
									'" . ($QtyOnHandPrior + $_POST['TotalLabour']) . "',
									0,
									'" . $_POST['WONO'] . "')";
						
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
				//制造费用
		if (filter_number_format($_POST['TotalOverhead'])!=0){
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
									VALUES ('" .$_SESSION['WOR']->WorkItems['overheadcode'] . "',
											34,
											'" . $WOReceiptNo . "',
											'" . $_POST['LOLoccode'] . "',
											'" . Date('Y-m-d h:i:s') . "',
											'" . $_SESSION['UserID'] . "',
											'" . $WORow['stdcost'] . "',
											'" . $PeriodNo . "',
											'" . $_POST['WONO'] . "',
											'" . $_POST['TotalOverhead']. "',
											'1',
											'" . ($QtyOnHandPrior +$_POST['TotalOverhead']) . "',
											0,
											'" . $_POST['WONO'] . "')";
				 		
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
		}
		/*Get the ID of the StockMove... */
		$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
		/* Do the Controlled Item INSERTS HERE */

		if ($WORow['controlled'] ==1){
			//the form is different for serialised items and just batch/lot controlled items
			if ($WORow['serialised']==1){
				//serialised items form has a possible 60 fields for entry of serial numbers - 12 rows x 5 per row
				for($i=0;$i<$_POST['CountOfInputs'];$i++){
				/*  We need to add the StockSerialItem record and
					The StockSerialMoves as well */
					if (trim($_POST['SerialNo' .$i]) != ""){
						if ($_SESSION['DefineControlledOnWOEntry']==0 OR
							($_SESSION['DefineControlledOnWOEntry']==1 AND $_POST['CheckItem'.$i]==true)){

							$LastRef = trim($_POST['SerialNo' .$i]);
							//already checked to ensure there are no duplicate serial numbers entered
							if (isset($_POST['QualityText'.$i])){
								$QualityText = $_POST['QualityText'.$i];
							} else {
								$QualityText ='';
							}

							if(empty($_POST['ExpiryDate'])){
									$SQL = "INSERT INTO stockserialitems (stockid,
																	loccode,
																	serialno,
																	quantity,
																	qualitytext)
											VALUES ('" . $_POST['StockID'] . "',
													'" . $_POST['IntoLocation'] . "',
													'" . $_POST['SerialNo' . $i] . "',
													1,
													'" . $QualityText . "')";
							}else{// Store expiry date for perishable product

								$SQL = "INSERT INTO stockserialitems(stockid,
																	loccode,
																	serialno,
																	quantity,
																	qualitytext,
																	expirationdate)
											VALUES ('" . $_POST['StockID'] . "',
												'" . $_POST['IntoLocation'] . "',
												'" . $_POST['SerialNo' . $i] . "',
												1,
												'" . $QualityText . "',
												'" . FormatDateForSQL($_POST['ExpiryDate']) . "')";
							}

							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
							$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

							/** end of handle stockserialitems records */

							/** now insert the serial stock movement **/
							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
																	stockid,
																	serialno,
																	moveqty)
										VALUES ('" . $StkMoveNo . "',
												'" . $_POST['StockID'] . "',
												'" . $_POST['SerialNo' .$i] . "',
												1)";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

							if ($_SESSION['DefineControlledOnWOEntry']==1){
								//need to delete the item from woserialnos
								$SQL = "DELETE FROM	woserialnos
											WHERE wo='" . $_POST['WONO'] . "'
											AND stockid='" . $_POST['StockID'] ."'
											AND serialno='" . $_POST['SerialNo'.$i] . "'";
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The predefined serial number record could not be deleted because');
								$DbgMsg = _('The following SQL to delete the predefined work order serial number record was used');
								$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							}
						}//end prefined controlled items or not
						if ($_SESSION['QualityLogSamples']==1) {
							CreateQASample($_POST['StockID'],$_POST['SerialNo'.$i], '', 'Created from Work Order', 0, 0,$db);
						}
					} //non blank SerialNo
				} //end for all of the potential serialised fields received
			} else { //the item is just batch/lot controlled not serialised
			/*the form for entry of batch controlled items is only 15 possible fields */
				for($i=0;$i<$_POST['CountOfInputs'];$i++){
				/*  We need to add the StockSerialItem record and
					The StockSerialMoves as well */
				//need to test if the batch/lot exists first already
					if (trim($_POST['BatchRef' .$i]) != "" AND (is_numeric($_POST['stlQty' . $i]) AND ABS($_POST['stlQty' . $i]>0))){
						$LastRef = trim($_POST['BatchRef' .$i]);
						$SQL = "SELECT COUNT(*) FROM stockserialitems
								WHERE stockid='" . $_POST['StockID'] . "'
								AND loccode = '" . $_POST['IntoLocation'] . "'
								AND serialno = '" . $_POST['BatchRef' .$i] . "'";
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a serial number for the stock item already exists because');
						$DbgMsg =  _('The following SQL to test for an already existing serialised stock item was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						$AlreadyExistsRow = DB_fetch_row($Result);
						if (isset($_POST['QualityText'.$i])){
							$QualityText = $_POST['QualityText'.$i];
						} else {
							$QualityText ='';
						}
						if ($AlreadyExistsRow[0]>0){
							$SQL = "UPDATE stockserialitems SET quantity = quantity + " . filter_number_format($_POST['stlQty' . $i]) . ",
																qualitytext = '" . $QualityText . "'
										WHERE stockid='" . $_POST['StockID'] . "'
										AND loccode = '" . $_POST['IntoLocation'] . "'
										AND serialno = '" . $_POST['BatchRef' .$i] . "'";
						} else if($_POST['stlQty' . $i]>0) {//only the positive quantity can be insert into database;
							if(empty($_POST['ExpiryDate'])){
								$SQL = "INSERT INTO stockserialitems (stockid,
																loccode,
																serialno,
																quantity,
																qualitytext)
										VALUES ('" . $_POST['StockID'] . "',
												'" . $_POST['IntoLocation'] . "',
												'" . $_POST['BatchRef' . $i] . "',
												'" . filter_number_format($_POST['stlQty'.$i]) . "',
												'" . $_POST['QualityText'] . "')";


							}else{	//If it's a perishable product, add expiry date

								$SQL = "INSERT INTO stockserialitems (stockid,
																loccode,
																serialno,
																quantity,
																qualitytext,
																expirationdate)
										VALUES ('" . $_POST['StockID'] . "',
												'" . $_POST['IntoLocation'] . "',
												'" . $_POST['BatchRef' . $i] . "',
												'" . filter_number_format($_POST['stlQty'.$i]) . "',
												'" . $_POST['QualityText'] . "',
												'" . FormatDateForSQL($_POST['ExpiryDate']) . "')";
							}
						} else {
							prnMsg(_('The input quantity should not be negative since there are no this lot no existed'),'error');
							include('includes/footer.php');
							exit;
						}
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
						$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

						/** end of handle stockserialitems records */

						/** now insert the serial stock movement **/
						$SQL = "INSERT INTO stockserialmoves (stockmoveno,
														stockid,
														serialno,
														moveqty)
									VALUES ('" . $StkMoveNo . "',
											'" . $_POST['StockID'] . "',
											'" . $_POST['BatchRef'.$i]  . "',
											'" . filter_number_format($_POST['stlQty'.$i])  . "')";
						$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
						$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
						$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

						if ($_SESSION['DefineControlledOnWOEntry']==1){
							//check how many of the batch/bundle/lot has been received
							$SQL = "SELECT sum(moveqty) FROM stockserialmoves
										INNER JOIN stockmoves ON stockserialmoves.stockmoveno=stockmoves.stkmoveno
										WHERE stockmoves.type=26
										AND stockserialmoves.stockid='" . $_POST['StockID'] . "'
										AND stockserialmoves.serialno='" . 	$_POST['BatchRef'.$i] . "'";

							$BatchTotQtyResult = DB_query($SQL);
							$BatchTotQtyRow = DB_fetch_row($BatchTotQtyResult);
						/*	if ($BatchTotQtyRow[0] >= $_POST['QtyReqd'.$i]){
								//need to delete the item from woserialnos
								$SQL = "DELETE FROM	woserialnos
										WHERE wo='" . $_POST['WONO'] . "'
										AND stockid='" . $_POST['StockID'] ."'
										AND serialno='" . $_POST['BatchRef'.$i] . "'";
								$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The predefined batch/lot/bundle record could not be deleted because');
								$DbgMsg = _('The following SQL to delete the predefined work order batch/bundle/lot record was used');
								$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
						} */
						}
						if ($_SESSION['QualityLogSamples']==1) {
							CreateQASample($_POST['StockID'],$_POST['BatchRef'.$i], '', 'Created from Work Order', 0 ,0,$db);
						}
					}//non blank BundleRef
				} //end for all of the potential batch/lot fields received
			} //end of the batch controlled stuff
		} //end if the woitem received here is a controlled item


	
		if (!isset($LastRef)) {
			$LastRef = '';
		}
		//update the wo with the new qtyrecd
		// 检验并关闭工作单 `closed`,  0 初始  1关闭发料 2收货关闭  3 收货后关闭

		$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' ._('Could not update the work order item record with the total quantity received because');
		$DbgMsg = _('The following SQL was used to update the work order');
		$UpdateWOResult =DB_query("UPDATE woitems
									SET qtyrecd=qtyrecd+" . $QuantityReceived . ",
										nextlotsnref='" . $LastRef . "'
									WHERE wo='" . $_POST['WO'] . "'
									AND stockid='" . $_POST['StockID'] . "'",
									$ErrMsg,
									$DbgMsg,
									true);
		 //`closed`,  0 初始  1关闭发料 2收货关闭  3 收货后关闭
		 if ($_SESSION['WOR']->BOMFlag==0){
			$closed=3;

		 }else{
			if ($IssueChk==0){
				$closed=1;
			}
			if (isset($_POST['Close'])&&($_POST['Close']==1)){
				$closed+=2;
			}
		}
		$CosedResult=DB_query("UPDATE `workorders`
	                               SET	`closed` =".$closed."	
								  WHERE  wo='" . $_POST['WONO'] . "'");
								
	
								


		$Result = DB_Txn_Commit();

		prnMsg('产品入库单:' .$WOReceiptNo.' 已经生成,属于工作单:'. $_POST['WONO'].' 产品: '. $_POST['StockID'] . ' - ' . $WORow['description'] .'入库数量:' . $QuantityReceived . ' ' . $WORow['units'] ,'success');
		echo '<a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select a different work order for receiving finished stock against'). '</a>';
		unset($_SESSION['WOR']);
		unset($_POST['WONO']);
		unset($_POST['StockID']);
		unset($_POST['IntoLocation']);
		//unset($_POST['Process']);
		for ($i=1;$i<$_POST['CountOfInputs'];$i++){
			unset($_POST['SerialNo'.$i]);
			unset($_POST['BatchRef'.$i]);
			unset($_POST['stlQty'.$i]);
			unset($_POST['QualityText'.$i]);
			unset($_POST['QtyReqd'.$i]);
		}
        echo '</div>';
        echo '</form>';
		/*end of process work order goods received entry */
		include('includes/footer.php');
		exit;
	} //end if there were not input errors reported - so the processing was allowed to continue
} //end of if the user hit the process button
if (isset(	$_POST['IntoLocation'])){
	$_POST['IntoLocation']=$_SESSION['WOR']->Location;
}
//发出物料到工作单
echo '<table class="selection">
		<tr>
			<td class="label">工作单号:</td>
			<td>' .$_POST['WONO'].':'. $_POST['WO']  . '</td>
		</tr>
		<tr>
			<td class="label">工作单属于:</td>
			<td>' . $WORow['locationname'] .'</td>
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
//	include('includes/footer.php');
//	exit;
}
DB_data_seek($WOResult,0);
$qtyreqd=0;
$qtyrecd=0;
while($WORow = DB_fetch_array($WOResult)){

	echo  '<tr>
			<td>' . $WORow['stockid'] . ' - ' . $WORow['description'] . '</td>
			<td class="number">' . locale_number_format($WORow['qtyreqd'],$WORow['decimalplaces']) . '
				<input type="hidden" id="QtyReqd" value="' . round($WORow['qtyreqd'],$WORow['decimalplaces']) . '"    /></td>
			<td class="number">' . locale_number_format($WORow['qtyrecd'],$WORow['decimalplaces']) . '
				<input type="hidden" id="QtyRecd" value="' . round($WORow['qtyrecd'],$WORow['decimalplaces']) . '"    /></td>
			<td>' . $WORow['units'] . '</td>
		</tr>';
   $qtyreqd+=(float)$WORow['qtyreqd'];
   $qtyrecd+=(float)$WORow['qtyrecd']; 
   $decimalplaces=$WORow['decimalplaces'];

}

echo '<tr>
		<td class="label">接收日期:</td>
		<td><input type="text" name="ReceiveDate" value="' . Date($_SESSION['DefaultDateFormat']) . '"  size="10" alt="'.$_SESSION['DefaultDateFormat'].'"  readonly="true" /></td>
		<td class="label">接收到:</td>
		<td>';

	$LocResult = DB_query("SELECT locations.loccode,
	                               locationname
							FROM locations
							INNER JOIN locationusers
								ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "'
								AND locationusers.canupd=1
							WHERE locations.usedforwo = 1");
	echo '<select name="IntoLocation">';
		while ($LocRow = DB_fetch_array($LocResult)){
			if ($_SESSION['WOR']->Location==$LocRow['loccode']){
				echo '<option selected="selected" value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'] . '</option>';
			} else {
				echo '<option value="' . $LocRow['loccode'] .'">' . $LocRow['locationname'] . '</option>';
			}
		}
	echo '</select></td>
			</tr>';
	//添加提示
	if( ($RequireCount>=1|| $BOMCount>=1)  && $_SESSION['WOR']->BOMFlag<0) {
		if ($RequireCount>=1 ){
			//prnMsg("配料单");
			$_SESSION['WOR']->BOMFlag=1;
		}else{
			//prnMsg("读取BOM");
			$_SESSION['WOR']->BOMFlag=2;
		}
	}elseif( $RequireCount==1&&$BOMCount==1) {
			//没有bom  没有配料单
			$_SESSION['WOR']->BOMFlag=0;
	}
echo'<tr>
		<td class="label">配料单:</td>
		<td>'.	 ($RequireCount>0?"有":"无").'</td>
		<td class="label">BOM:</td>
		<td>'.	 ($BOMCount>0?$BOMCount."种物料":"无BOM").'</td>
		</tr>';
		$_POST['QtyReceive']=$qtyreqd-	$qtyrecd;
		$RW=$_SESSION['WOR']->LineCounter;
/*			//读取发料成本

			$SQL = "SELECT stockid,						
						SUM(qty) qty,
						SUM(price) qtycost
				FROM stockmoves 
				WHERE type=28
				AND connectid = '" . $_POST['WONO'] . "'";
				

$Result = DB_query($SQL,_('Could not get issues that were not required by the BOM because'));
$RW=DB_num_rows($Result);
if ($RW>0){
	while ($Row = DB_fetch_array($Result)){
		$_SESSION['WOR']->Update_WODemand( $Row['stockid'],
												$Row['qty'],								
													);
	}
}*/
if ($_SESSION['WOR']->Diff==""||$_SESSION['WOR']->Diff==0){
	$Diff=0;
}else{
	$Diff=$_SESSION['WOR']->Diff.'%';
}
echo '<tr>
		<td class="label">接收数量:</td>
		<td><input type="text" name="QtyReceive" value="'.locale_number_format($_POST['QtyReceive'],$WORow['decimalplaces']).'"  size="10"  onChange="InReceive(this,'.$decimalplaces .','.($qtyreqd-	$qtyrecd).','.$RW.' )"  '.	 ($BOMCount>0? '':' readonly="true"').' /></td>
		<td class="label">接收成本</td>
		<td><input type="text" id ="ReceiveCost" name="ReceiveCost" value=""  size="9"  readonly /></td>
		</tr>';
echo '<tr>
	<td class="label">是否关闭:</td>
	<td><input type="checkbox" name="Close" id="Close"  value="1"   onclick="InCloseCheck(this ,'.$decimalplaces .','.($qtyreqd-$qtyrecd).')" checked  /></td>
	<td class="label">接收差异</td>
	<td>'.$Diff.'<input type="hidden" id ="Diff" name="Diff" value="'.$_SESSION['WOR']->Diff.'"  /></td>

	</tr>';
echo'</table>
	<br />';
		$IndexRow=1;
	//	var_dump($_SESSION['WOR']->LineItems);
		echo '<table class="selection">';
			//成本计算汇总
			echo '<tr>
					<th colspan="11"><h3>产品入库材料成本结转明细</h3></th>
					</tr>';
			echo '<tr>
					<th>序号</th>
					<th>' . _('Item') .'/'. _('Description'). '</th>				
					<th>单位</th>				
					<th>额定数量</th>
					<th>发料数量</th>
					<th>发料成本</th>
					<th>已经<br>结转数量</th>
					<th>已经<br>结转成本</th>
					<th>成本单价</th>
					<th>结转数量</th>
					<th>结转成本</th>	
					<input type="hidden" id="worow"   value="'  .$_SESSION['WOR']->LineCounter . '">				
				</tr>';
				$TotalIssuedCost=0;
				$TotalCost=0;
		if ($RW>0){
			//已经结转  材料  人工  制造费用
			$stlqtycost=array();
			if ($_SESSION['WOR']->BOMFlag!=0){
			//读取入库产品 已经结转的人工费 制造费用
			$sql="SELECT stockid,SUM(qty) qty ,SUM(standardcost*qty) qtycost FROM stockmoves WHERE type IN ( 26,27,34)  AND connectid='".$_SESSION['WOR']->WONO."' GROUP BY  stockid";
			$result=DB_query($sql);
			//prnMsg($sql);
			
		
			if (DB_num_rows($result)>0){
				//if ($_SESSION['WOR']->BOMFlag==0){
				while($row=DB_fetch_array($result)){
					$stlqtycost[$row['stockid']]=array($row['qty'],$row['qtycost']);

				}					
			}
			//var_dump($stlqtycost);
			//结转产品数量
			$stlqty=$stlqtycost[$_SESSION['WOR']->WOStockID][0];
			//结转材料成本
			$stlStockCost=$stlqtycost[$_SESSION['WOR']->WOStockID][1]-$stlqtycost[$_SESSION['WOR']->WorkItems['labourcode']][1]-$stlqtycost[$_SESSION['WOR']->WorkItems['overheadcode']][1];
			$_SESSION['WOR']->WOReceiveQty=$stlqty;
			//$stlQty=$_SESSION['WOR']->WOQty-$stlqty;
			$_SESSION['WOR']->Update_WOSettleCost($stlStockCost	);
		}else{
			$stlqty=0;
			$stlStockCost=0;
		}
			foreach ($_SESSION['WOR']->LineItems as $WOLine) {
	
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k++;
				}
			     if ($WOLine->BOMQty>=-$WOLine->IssueQty){
					$stlQty=$WOLine->BOMQty-$WOLine->SettleQty;	
					$stlCost=$WOLine->BOMQty*$WOLine->stdCost;
				 }else{
					 $stlQty=-$WOLine->IssueQty-$WOLine->SettleQty;
					 $stlCost=-$WOLine->IssueCost-$WOLine->SettleCost;
				 }
				 
				 echo '<td>' .  $WOLine->LineNo . '</td>
					  <td>	<input type="hidden" name="StockID' . $WOLine->LineNo  . '" value="' . $WOLine->StockID . '">
					  		<input type="hidden" id="DecimalPlaces' . $WOLine->LineNo  . '" value="' . $WOLine->DecimalPlaces . '">
					  		' . $WOLine->StockID .':'.$WOLine->Description   . '</td>					
						<td >'. $WOLine->Units.'
							<input type="hidden" name="Units' . $WOLine->LineNo  . '"  value="'  .$WOLine->Units . '"></td>
						<td ><input type="text" class="number" id="BOMQty' . $WOLine->LineNo  .'"   name="BOMQty' . $WOLine->LineNo  .'"   size="5" value="' .round($WOLine->BOMQty,$WOLine->DecimalPlaces) . '"  readonly ></td>
						<td ><input type="text" class="number" id="IssueQty' . $WOLine->LineNo  .'"   name="IssueQty' . $WOLine->LineNo  .'"  size="5" value="' .round(-$WOLine->IssueQty,$WOLine->DecimalPlaces) . '"  readonly ></td>
						<td ><input type="text" class="number" id="IssueCost' . $WOLine->LineNo  .'"   name="IssueCost' . $WOLine->LineNo  .'"  size="7" value="' .round(-$WOLine->IssueCost,$WOLine->DecimalPlaces) . '" readonly ></td>
						<td ><input type="text" class="number" id="stlQty' . $WOLine->LineNo  .'"   name="stlQty' . $WOLine->LineNo  .'"  size="5" value="' .($WOLine->SettleQty) . '"  readonly ></td>
						<td ><input type="text" class="number" id="stlCost' . $WOLine->LineNo  .'"   name="stlCost' . $WOLine->LineNo  .'"  size="7" value="' .round($WOLine->SettleCost,$_SESSION['CompanyRecord']['decimalplaces']) . '"  readonly ></td>
						<td ><input type="text" class="number" id="StandardCost' . $WOLine->LineNo  .'"   name="StandardCost' . $WOLine->LineNo  .'"   size="5" value="' . locale_number_format($WOLine->stdCost,$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly ></td>
						<td class="number">
						    <input type="text" class="number" id="DemandQty' . $WOLine->LineNo  .'"   name="DemandQty' . $WOLine->LineNo  .'"  size="5" value="' . locale_number_format($stlQty,$WOLine->DecimalPlaces)   . '" readonly ></td>
						<td class="number">
						    <input type="text" class="number" id="DemandCost' . $WOLine->LineNo  .'"   name="DemandCost' . $WOLine->LineNo  .'"    size="7" value="' . locale_number_format($stlCost,2)   . '"  readonly ></td>
					</tr>';
		
						  
				$TotalIssuedCost += $stlCost;
				$IndexRow++;
			}
			$TotalCost=$TotalIssuedCost;
		}
		# <!--	<td colspan="5"></td> -->
		echo '<tr>
		        <td></td>				
				<td colspan="3"><hr/></td>
				<td >合计</td>
				<td class="number"><input type="text" class="number" id="IssueCost"   name="IssueCost"    size="7" value="' . locale_number_format($TotalIssuedCost,$_SESSION['CompanyRecord']['decimalplaces'])   . '" ></td>
				
				<td ><hr /></td>
				<td class="number"><input type="text" class="number" id="SettleCost"   name="SettleCost"  size="7" value="' . locale_number_format($TotalIssuedCost,$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly ></td>
				<td colspan="2" ><hr /></td>
				<td class="number"><input type="text" class="number" id="TotalCost"   name="TotalCost"   size="7" value="' . locale_number_format($TotalIssuedCost,$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly ></td>

			</tr>';
		echo '</table>';
		    $SQL="SELECT	code,
							location,
							description,
							worktype,
							capacity,
							overheadperhour,
							a.labouract,
							labourperhr,
							overheadrecoveryact,
							setuphrs,
							stocktype,
							stockact,
							adjglact,
							issueglact,
							labourcode,						
							overheadcode,
							b.overheadact,
							wipact,
							defaulttaxcatid
						FROM  workcentres a
						LEFT JOIN stockcategory b ON	a.location = categoryid
							WHERE location='".$_SESSION['WOR']->Location."'";
			$Result = DB_query($SQL);
			$WorkRow=DB_fetch_assoc($Result);
			if (count($_SESSION['WOR']->WorkItems)==0){
				$_SESSION['WOR']->WorkItems=$WorkRow;
			}
			
		echo '<table class="selection">
		       <tr>
				<th>序号</th>
				<th>' . _('Item') .'/'.  _('Description') . '</th>				
				<th>额定分摊<br>金额</th>			
				<th>已经结转<br>金额</th>
				<th>分摊参数</th>
				<th>系数</th>			
				<th>费用分摊<br>金额</th>
			
				</tr>';
				$_POST['RatioLabour']=$WorkRow['labourperhr'];
				
				$_POST['PrmLab']=$TotalCost;
				$_POST['PrmOvh']=$TotalCost;
				$_POST['RatedLabour']=$_POST['PrmLab']*$_POST['RatioLabour'];
				$_POST['TotalLabour']=$_POST['PrmLab']*$_POST['RatioLabour'];
		echo '<tr>
		        <td>1</td>				
				<td >'.$WorkRow['labourcode'].':人工费</td>
				<td >
				    <input type="text" class="number" id="RatedLabour"   name="RatedLabour"    size="7" value="' . locale_number_format($_POST['RatedLabour'],$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly ></td>
				<td ><input type="text" class="number" id="SettleLabour"   name="SettleLabour"    size="7" value="' . locale_number_format($stlqtycost[$_SESSION['WOR']->WorkItems['labourcode']][1],$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly ></td>
				<td ><input type="text" class="number" id="PrmLab"   name="PrmLab"    size="7" value="' . locale_number_format($_POST['PrmLab'],$_SESSION['CompanyRecord']['decimalplaces'])   . '" ></td>';
				
		   echo'<td ><input type="text" class="number" id="RatioLabour"   name="RatioLabour"    size="7" value="' . locale_number_format($_POST['RatioLabour'],$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly>
					 <input type="hidden" name="UOM" value="'.$WorkRow['labourperhr'].'"></td>
			
				<td ><input type="text" class="number" id="TotalLabour"   name="TotalLabour"    size="7" value="' . locale_number_format($_POST['TotalLabour'],$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly ></td>
				
				
			</tr>';
			$TotalCost+=$_POST['TotalLabour'];
			$_POST['RatioOverhead']=$WorkRow['overheadperhour'];
			$_POST['TotalOverhead']=$_POST['PrmOvh']*$_POST['RatioOverhead'];
			$_POST['RatedOverhead']=$_POST['PrmOvh']*$_POST['RatioOverhead'];
			//制造费用
		
			echo '<tr>
				<td>2</td>				
				<td >'.$WorkRow['overheadcode'].':制造费用</td>
				<td ><input type="text" class="number" id="RatedOverhead"   name="RatedOverhead"    size="7" value="' . locale_number_format($_POST['RatedOverhead'],$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly ></td>
				<td><input type="text" class="number" id="SettleOverhead"   name="SettleOverhead"    size="7" value="' . locale_number_format($stlqtycost[$_SESSION['WOR']->WorkItems['overheadcode']][1],$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly ></td>
				<td><input type="text" class="number" id="PrmOvh"   name="PrmOvh"    size="7" value="' . locale_number_format($_POST['PrmOvh'],$_SESSION['CompanyRecord']['decimalplaces'])   . '" ></td>';
			
			echo'<td >
					<input type="text" class="number" id="RatioOverhead"   name="RatioOverhead"    size="7" value="' . locale_number_format($_POST['RatioOverhead'],$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly >
					<input type="hidden" name="UOM" value="'.$WorkRow['overheadperhour'].'"></td>
				<td ><input type="text" class="number" id="TotalOverhead"   name="TotalOverhead"    size="7" value="' . locale_number_format($_POST['TotalOverhead'],$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly ></td>
				
			</tr>';
			$TotalCost+=$_POST['TotalOverhead'];
			$_POST['TotalLabOvh']=$_POST['TotalLabour']+$_POST['TotalOverhead'];

			$_POST['TotalRated']=round($_POST['RatedLabour']+$_POST['RatedOverhead'],$_SESSION['CompanyRecord']['decimalplaces']);

		echo '<tr>
				<td></td>				
			
				<td >合计</td>
				<td class="number"><input type="text" class="number" id="TatalRated"   name="TotalRated"    size="7" value="' . locale_number_format($_POST['TotalRated'],$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly ></td>
				
			
				<td class="number"><input type="text" class="number" id="Overhead"   name="Amount' . $IndexRow .'"    size="7" value="' . locale_number_format($_POST['Total'],$_SESSION['CompanyRecord']['decimalplaces'])   . '" readonly ></td>
				<td colspan="2" ><hr /></td>
				<td class="number"><input type="text" class="number" id="TotalLabOvh"   name="TotalLabOvh"    size="7" value="' . locale_number_format($_POST['TotalLabOvh'],$_SESSION['CompanyRecord']['decimalplaces'])   . '"  readonly ></td>

			</tr>';
	
	echo '</table>';
		
	echo '<br />
			<div class="centre">
			<input type="submit" name="Commit" value="执行收货" onclick="return confirm(\'' .'确认收取货物,该工作单将自动关闭! 确认收货吗?' . '\');" />
			
			</div>';
 /*
		//Recalculate the standard for the item if there were no items previously received against the work order
		if ($WORow['qtyrecd']==0){
			$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
									FROM stockmaster INNER JOIN bom
									ON stockmaster.stockid=bom.component
									WHERE bom.parent='" . $_POST['StockID'] . "'");
								//	AND bom.loccode='" . $WORow['loccode'] . "'");
			$CostRow = DB_fetch_row($CostResult);
			if (is_null($CostRow[0]) OR $CostRow[0]==0){
					$Cost =0;
			} else {
					$Cost = $CostRow[0];
			}
			//Need to refresh the worequirments with the bom components now incase they changed
			$DelWORequirements = DB_query("DELETE FROM worequirements
											WHERE wo='" . $_POST['WONO'] . "'
											AND parentstockid='" . $_POST['StockID'] . "'");

			//Recursively insert real component requirements
			//接受工单信息并迭代物料清单，插入实际组件（分解幻影组件）
			WoRealRequirements($db, $_POST['WONO'], $WORow['loccode'], $_POST['StockID']);

			//Need to check this against the current standard cost and do a cost update if necessary
			$sql = "SELECT materialcost+labourcost+overheadcost AS cost,
						  sum(quantity) AS totalqoh,
						  labourcost,
						  overheadcost
					FROM stockmaster INNER JOIN locstock
						ON stockmaster.stockid=locstock.stockid
					WHERE stockmaster.stockid='" . $_POST['StockID'] . "'
					GROUP BY materialcost,
							labourcost,
							overheadcost";
			$ItemResult = DB_query($sql);
			$ItemCostRow = DB_fetch_array($ItemResult);

			if (($Cost + $ItemCostRow['labourcost'] + $ItemCostRow['overheadcost']) != $ItemCostRow['cost']){ //the cost roll-up cost <> standard cost

				if ($_SESSION['CompanyRecord']['gllink_stock']==1 AND $ItemCostRow['totalqoh']!=0){

					$CostUpdateNo = GetNextTransNo(35, $db);
					$PeriodNo = GetPeriod(Date($_SESSION['DefaultDateFormat']), $db);
                     //计算人工费 制造费用
					$ValueOfChange = $ItemCostRow['totalqoh'] * (($Cost + $ItemCostRow['labourcost'] + $ItemCostRow['overheadcost']) - $ItemCostRow['cost']);

					$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
							VALUES (35,
								'" . $CostUpdateNo . "',
								'" . Date('Y-m-d') . "',
								'" . $PeriodNo . "',
								'" . $StockGLCode['adjglact'] . "',
								'" . _('Cost roll on release of WONO') . ': ' . $_POST['WONO'] . ' - ' . $_POST['StockID'] . ' ' . _('cost was') . ' ' . $ItemCostRow['cost'] . ' ' . _('changed to') . ' ' . $Cost . ' x ' . _('Quantity on hand of') . ' ' . $ItemCostRow['totalqoh'] . "',
								'" . (-$ValueOfChange) . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL credit for the stock cost adjustment posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

					$SQL = "INSERT INTO gltrans (type,
								typeno,
								trandate,
								periodno,
								account,
								narrative,
								amount)
							VALUES (35,
								'" . $CostUpdateNo . "',
								'" . Date('Y-m-d') . "',
								'" . $PeriodNo . "',
								'" . $StockGLCode['stockact'] . "',
								'" . _('Cost roll on release of WONO') . ': ' . $_POST['WONO'] . ' - ' . $_POST['StockID'] . ' ' . _('cost was') . ' ' . $ItemCostRow['cost'] . ' ' . _('changed to') . ' ' . $Cost . ' x ' . _('Quantity on hand of') . ' ' . $ItemCostRow['totalqoh'] . "',
								'" . $ValueOfChange . "')";

					$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The GL debit for stock cost adjustment posting could not be inserted because');
					$DbgMsg = _('The following SQL to insert the GLTrans record was used');
					$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				}

				$SQL = "UPDATE stockmaster SET
							lastcostupdate='" . Date('Y-m-d') . "',
							materialcost='" . $Cost . "',
							labourcost='" . $ItemCostRow['labourcost'] . "',
							overheadcost='" . $ItemCostRow['overheadcost'] . "',
							lastcost='" . $ItemCostRow['cost'] . "'
						WHERE stockid='" . $_POST['StockID'] . "'";

				$ErrMsg = _('The cost details for the stock item could not be updated because');
				$DbgMsg = _('The SQL that failed was');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			} //cost as rolled up now <> current standard cost  so do adjustments
		}   //qty recd previously was 0 so need to check costs and do adjustments as required
	*/
	/*
		//Do the issues for autoissue components in the worequirements table
		$AutoIssueCompsResult = DB_query("SELECT worequirements.stockid,
												 qtypu,
												 materialcost+labourcost+overheadcost AS cost,
												 stockcategory.stockact,
												 stockcategory.stocktype
										  FROM worequirements
										  INNER JOIN stockmaster
										  ON worequirements.stockid=stockmaster.stockid
										  INNER JOIN stockcategory
										  ON stockmaster.categoryid=stockcategory.categoryid
										  WHERE wo='" . $_POST['WONO'] . "'
										  AND parentstockid='" .$_POST['StockID'] . "'
										  AND autoissue=1");

		$WOIssueNo = GetNextTransNo(28,$db);
		while ($AutoIssueCompRow = DB_fetch_array($AutoIssueCompsResult)){

			//Note that only none-controlled items can be auto-issuers so don't worry about serial nos and batches of controlled ones
			
			if ($AutoIssueCompRow['stocktype']!='L'){
				//Need to get the previous locstock quantity for the component at the location where the WONO manuafactured
				$CompQOHResult = DB_query("SELECT locstock.quantity
											FROM locstock
											WHERE locstock.stockid='" . $AutoIssueCompRow['stockid'] . "'
											AND loccode= '" . $WORow['loccode'] . "'");
				if (DB_num_rows($CompQOHResult)==1){
							$LocQtyRow = DB_fetch_row($CompQOHResult);
							$NewQtyOnHand = $LocQtyRow[0] - ($AutoIssueCompRow['qtypu'] * $QuantityReceived);
				} else {
							//There must actually be some error this should never happen 
							$NewQtyOnHand = 0;
				}

				$SQL = "UPDATE locstock
							SET quantity = quantity - " . ($AutoIssueCompRow['qtypu'] * $QuantityReceived). "
							WHERE locstock.stockid = '" . $AutoIssueCompRow['stockid'] . "'
							AND loccode = '" . $WORow['loccode'] . "'";

				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated by the issue of stock to the work order from an auto issue component because');
				$DbgMsg =  _('The following SQL to update the location stock record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			} else {
				$NewQtyOnHand =0;
			}
			$SQL = "INSERT INTO stockmoves (stockid,
											type,
											transno,
											loccode,
											trandate,
											userid,
											prd,
											reference,
											price,
											qty,
											standardcost,
											newqoh,
											newamount,
											connectid)
						VALUES ('" . $AutoIssueCompRow['stockid'] . "',
								28,
								'" . $WOIssueNo . "',
								'" . $WORow['loccode'] . "',
								'" . Date('Y-m-d') . "',
								'" . $_SESSION['UserID'] . "',
								'" . $PeriodNo . "',
								'" . $_POST['WONO'] . "',
								'" . $AutoIssueCompRow['cost'] . "',
								'" . -($AutoIssueCompRow['qtypu'] * $QuantityReceived) . "',
								'" . $AutoIssueCompRow['cost'] . "',
								'" . $NewQtyOnHand . "',
								0,
								0)";

			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement record could not be inserted for an auto-issue component because');
			$DbgMsg =  _('The following SQL to insert the stock movement records was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			//Update the workorder record with the cost issued to the work order
			$SQL = "UPDATE workorders SET
						costissued = costissued+" . ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) ."
					WHERE wo='" . $_POST['WONO'] . "'";
			$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not update the work order cost for an auto-issue component because');
			$DbgMsg =  _('The following SQL to update the work order cost was used');
			$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

			if ($_SESSION['CompanyRecord']['gllink_stock']==1
				AND ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost'])!=0){
			//if GL linked then do the GL entries to DR wip and CR stock

				$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (28,
								'" . $WOIssueNo . "',
								'" . Date('Y-m-d') . "',
								'" . $PeriodNo . "',
								'" . $StockGLCode['wipact'] . "',
								'" . $_POST['WONO'] . ' - ' . $_POST['StockID'] . ' ' . _('Component') . ': ' . $AutoIssueCompRow['stockid'] . ' - ' . $QuantityReceived . ' x ' . $AutoIssueCompRow['qtypu'] . ' @ ' . locale_number_format($AutoIssueCompRow['cost'],$_SESSION['CompanyRecord']['decimalplaces']) . "',
								'" . ($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) . "')";

					$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The WIP side of the work order issue GL posting could not be inserted because');
					$DbgMsg =  _('The following SQL to insert the WONO issue GLTrans record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

				$SQL = "INSERT INTO gltrans (type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount)
							VALUES (28,
								'" . $WOIssueNo . "',
								'" . Date('Y-m-d') . "',
								'" . $PeriodNo . "',
								'" . $AutoIssueCompRow['stockact'] . "',
								'" . $_POST['WONO'] . ' - ' . $_POST['StockID'] . ' -> ' . $AutoIssueCompRow['stockid'] . ' - ' . $QuantityReceived . ' x ' . $AutoIssueCompRow['qtypu'] . ' @ ' . locale_number_format($AutoIssueCompRow['cost'],$_SESSION['CompanyRecord']['decimalplaces']) . "',
								'" . -($AutoIssueCompRow['qtypu'] * $QuantityReceived * $AutoIssueCompRow['cost']) . "')";

					$ErrMsg =   _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The stock side of the work order issue GL posting could not be inserted because');
					$DbgMsg =  _('The following SQL to insert the WONO issue GLTrans record was used');
					$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
			}//end GL-stock linked

		} //end of auto-issue loop for all components set to auto-issue
    */
echo '</div>
      </form>';

include('includes/footer.php');

