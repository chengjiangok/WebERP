
<?php

/* $Id: WorkOrderEntry.php ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-15 20:45:16 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-16 08:15:28
 */


include('includes/DefineWorkOrderClass.php');
include('includes/session.php');
$Title = _('Work Order Entry');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
function inDiff(p){
	var  n=p.name.substring(9);	
	
	if (parseFloat(p.value)>10){
         p.value=0;
	}else{	
		document.getElementById("edit"+n).value=1;
	}
	
}
function inComments(p){
	var  n=p.name.substring(10);	
	document.getElementById("edit"+n).value=1;
	
}
function inOutputQty(p,d){	
	var  n=p.name.substring(9);	
	var vl = document.getElementById("OutputQty"+n);
	
	var qty=parseFloat(p.value).toFixed(d);
	if (parseFloat(p.value)!=qty){
		p.value=qty;
		alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
	}
	 document.getElementById("edit"+n).value=1;
}
	
</script>';
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
	</p>';
	if (empty($_GET['identifier'])) {
		$identifier = date('U');
	} else {
		$identifier = $_GET['identifier'];
	}
	$EditingExisting = false;
if (isset($_GET['WONO'])) {
	$SelectedWO = explode('-',$_GET['WONO'])[0];
	$_POST['WONO']=$SelectedWO;
	$_POST['WO']=explode('-',$_GET['WONO'])[1];
	//$identifier = date('U');
} elseif (isset($_POST['WONO'])){
	$SelectedWO = explode('-',$_POST['WONO'])[0];
	$_POST['WONO']=$SelectedWO;
	$_POST['WO']=explode('-',$_POST['WONO'])[1];
} else {
	unset($SelectedWO);
}

if (isset($_GET['ReqDate'])){
	$ReqDate = $_GET['ReqDate'];
} else {
	$ReqDate=Date('Y-m-d',strtotime("+1 month"));
}

if (isset($_GET['StartDate'])){
	$StartDate = $_GET['StartDate'];
} else {
	$StartDate=Date('Y-m-d');
}
if (!isset($_SESSION['WO' . $identifier])) {
	
	$_SESSION['WO' . $identifier] = new WorkOrder;
	//prnMsg('71-'.$identifier);

} //end if initiating a new WO

/*
if (isset($_GET['loccode'])){
	$LocCode = $_GET['loccode'];
} else {
	$LocCode=$_SESSION['UserStockLocation'];
}


$sql="SELECT locations.loccode FROM locations
						INNER JOIN locationusers ON locationusers.loccode=locations.loccode
						AND locationusers.userid='" .  $_SESSION['UserID'] . "'
						AND locationusers.canupd=1
						WHERE locations.loccode='" . $LocCode . "'";
						$LocResult = DB_query($sql);
$LocRow = DB_fetch_array($LocResult);

if (is_null($LocRow['loccode']) OR $LocRow['loccode']==''){
	prnMsg(_('Your security settings do not allow you to create or update new Work Order at this location') . ' ' . $LocCode,'error');
	echo '<br /><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select an existing work order') . '</a>';
	include('includes/footer.php');
	exit;
}*/

foreach ($_POST as $key=>$value) {
	if (substr($key, 0, 9)=='OutputQty' OR substr($key, 0, 7)=='RecdQty') {
		$_POST[$key] = filter_number_format($value);
	}
}

// check for new or modify condition
if (isset($SelectedWO) AND$SelectedWO!=''){
	// modify
	$_POST['WONO'] = (int)$SelectedWO;
	$EditingExisting = true;
} 


if (isset($_GET['NewItem'])){
	$NewItem = $_GET['NewItem'];
}
if (isset($_GET['ReqQty'])){
	$ReqQty = $_GET['ReqQty'];
}
if (!isset($_POST['StockLocation'])){
	if (isset($LocCode)){
		$_POST['StockLocation']=$LocCode;
	} elseif (isset($_SESSION['UserStockLocation'])){
		$_POST['StockLocation']=$_SESSION['UserStockLocation'];
	}
}

if (isset($_POST['submit']) ) { //The update button has been clicked
    
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .'">' . _('Enter a new work order') . '</a>';
	echo '<br /><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select an existing work order') . '</a>';
	echo '<br /><a href="'. $RootPath . '/WorkOrderCosting.php?WO=' .  $SelectedWO . '">' . _('Go to Costing'). '</a></div>';

	$Input_Error = false; //hope for the best
	 for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
	   	if (!is_numeric($_POST['OutputQty'.$i])){
		   	prnMsg(_('The quantity entered must be numeric'),'error');
			$Input_Error = true;
		} elseif ($_POST['OutputQty'.$i]<=0){
			prnMsg(_('The quantity entered must be a positive number greater than zero'),'error');
			$Input_Error = true;
		}
	 }
	 if (!Is_Date($_POST['RequiredBy'])){
		prnMsg(_('The required by date entered is in an invalid format'),'error');
		$Input_Error = true;
	 }
	 //新工作单
	if ($InputError==false &&$EditingExisting==false){
		//prnMsg('163');
		foreach ($_SESSION['WO'.$identifier]->LineItems as $POLine) {
			if ($POLine->Deleted==False) {
				
				if (!is_numeric(filter_number_format($_POST['OutputQty'.$POLine->LineNo]))){
					prnMsg(_('The supplier price is expected to be numeric. Please re-enter as a number'),'error');
				} else { //ok to update the WO object variables
					if($_POST['edit'.$POLine->LineNo]==1){
						
						$_SESSION['WO'.$identifier]->LineItems[$POLine->LineNo]->Qty = filter_number_format($_POST['OutputQty'.$POLine->LineNo],$POLine->DecimalPlaces);
						$_SESSION['WO'.$identifier]->LineItems[$POLine->LineNo]->Narrative =$_POST['WOComments'.$POLine->LineNo];
					}
				}
				//$_SESSION['WO'.$identifier]->RequiredbyDate= $_POST['RequiredBy'];
            	//$_SESSION['WO'.$identifier]->StartDate = $_POST['StartDate'];


			}
		}
			
			$Result = DB_Txn_Begin();
			//$_POST['WONO'] = GetNextTransNo(40,$db);
			//插入合同头，wono自动编号
			$SQL = "INSERT INTO workorders (loccode,
											requiredby,
											startdate,
											initiator)
										VALUES (										
											'" . $_SESSION['WO'.$identifier]->Location . "',
											'" . $_POST['RequiredBy'] . "',
											'" .$_POST['StartDate']. "',
											'".$_SESSION['UserID']."')";
			$InsWOResult = DB_query($SQL);
			$_POST['WONO']  = DB_Last_Insert_ID($db,'workorders','wono');
			foreach ($_SESSION['WO'.$identifier]->LineItems as $POLine) {
				if ($POLine->Deleted==False) {
					$rowtol++;
					$ReqQty=$POLine->Qty;
					$CheckItemResult = DB_query("SELECT mbflag,
														eoq,
														controlled
													FROM stockmaster
													WHERE stockid='" . $POLine->StockID . "'");
	
					$CheckItemRow = DB_fetch_array($CheckItemResult);
					if ($CheckItemRow['controlled']==1 AND $_SESSION['DefineControlledOnWOEntry']==1){ //need to add serial nos or batches to determine quantity
					$EOQ = 0;
					} else {
					if (!isset($ReqQty)) {
					$ReqQty=$CheckItemRow['eoq'];
					}
					$EOQ = $ReqQty;
					}
					if ($CheckItemRow['mbflag']!='M'){
					prnMsg(_('The item selected cannot be added to a work order because it is not a manufactured item'),'warn');
					$InputError=true;
					}
				$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
												FROM stockmaster
												INNER JOIN bom
													ON stockmaster.stockid=bom.component
												WHERE bom.parent='" . $POLine->StockID . "'
													
													AND bom.effectiveafter<='" . Date('Y-m-d') . "'
													AND bom.effectiveto>='" . Date('Y-m-d') . "'");
											//		AND bom.loccode=(SELECT loccode FROM workorders WHERE wo='" . $_POST['WONO'] . "')
				$CostRow = DB_fetch_array($CostResult);
					if (is_null($CostRow['cost']) OR $CostRow['cost']==0){
						$Cost =0;
					//保留	prnMsg(_('The cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
					} else {
						$Cost = $CostRow['cost'];
					}
					if (!isset($EOQ)){
					$EOQ=1;
					}
				// insert parent item info
				$SQL = "INSERT INTO woitems (wono,
											stockid,
											loccode,
											qtyreqd,
											stdcost,
											comments,
											diff)
										VALUES (
											'" . $_POST['WONO'] . "',
											'" . $POLine->StockID . "',
											'" . $POLine->LocCode . "',
											'" . $EOQ . "',
											'" . $Cost . "',
											'" . $POLine->Narrative . "',
											'" . $POLine->Diff . "'
										)";
				$ErrMsg = _('The work order item could not be added');
				$result = DB_query($SQL,$ErrMsg);
				$_POST['WONO']  = DB_Last_Insert_ID($db,'woitems','wo');
				if ($result){
					$inrow++;
				}
	
				//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
				WoRealRequirements($db, $_POST['WONO'],  $POLine->LocCode,  $POLine->StockID);
				}
			}
			$result = DB_Txn_Commit();
			if (isset($_POST['submit']) && $inrow>0) {
					
			unset($_SESSION['WO'.$identifier]->LineItems);
			unset($_SESSION['WO'.$identifier]);
			unset($identifier);
				prnMsg('工单号:'.$_POST['WONO'].' '._('The work order has been updated'),'success');
			}
			//unset($NewItem);
		//} //end if there were no input errors
	} //adding a new item to the work order
    //修改工作单
	if ($Input_Error == false && $EditingExisting==true) {
	
		foreach ($_SESSION['WO'.$identifier]->LineItems as $POLine) {
			if ($POLine->Deleted==False) {
				
				if (!is_numeric(filter_number_format($_POST['OutputQty'.$POLine->LineNo]))){
					prnMsg(_('The supplier price is expected to be numeric. Please re-enter as a number'),'error');
				} else { //ok to update the WO object variables
					if($_POST['edit'.$POLine->LineNo]==1){
						
						$_SESSION['WO'.$identifier]->LineItems[$POLine->LineNo]->Qty = filter_number_format($_POST['OutputQty'.$POLine->LineNo],$POLine->DecimalPlaces);
						$_SESSION['WO'.$identifier]->LineItems[$POLine->LineNo]->Narrative =$_POST['WOComments'.$POLine->LineNo];
					}
				}

			}
		}
		$SQL_ReqDate = FormatDateForSQL($_POST['RequiredBy']);
		$QtyRecd=0;
		$Result = DB_Txn_Begin();
		//prnMsg(_('The factory where this work order is made can only be updated if the quantity received on all output items is 0'),'warn');

			$SQL= "UPDATE workorders SET requiredby='" .FormatDateForSQL($_SESSION['WO'.$identifier]->StartDate) . "',
												startdate='" . FormatDateForSQL($_SESSION['WO'.$identifier]->StartDate) . "'
											WHERE wono='" . $_POST['WONO'] . "'";
			$result = DB_query($SQL,$ErrMsg);
		foreach ($_SESSION['WO'.$identifier]->LineItems as $POLine) {
			if ($POLine->Deleted==False) {
				$rowtol++;
				$ReqQty=$POLine->Qty;
				$CheckItemResult = DB_query("SELECT mbflag,
													eoq,
													controlled
												FROM stockmaster
												WHERE stockid='" . $POLine->StockID . "'");

				$CheckItemRow = DB_fetch_array($CheckItemResult);
				if ($CheckItemRow['controlled']==1 AND $_SESSION['DefineControlledOnWOEntry']==1){ //need to add serial nos or batches to determine quantity
				$EOQ = 0;
				} else {
				if (!isset($ReqQty)) {
				$ReqQty=$CheckItemRow['eoq'];
				}
				$EOQ = $ReqQty;
				}
				if ($CheckItemRow['mbflag']!='M'){
					prnMsg(_('The item selected cannot be added to a work order because it is not a manufactured item'),'warn');
				$InputError=true;
				}
			$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
											FROM stockmaster
											INNER JOIN bom
												ON stockmaster.stockid=bom.component
											WHERE bom.parent='" . $POLine->StockID . "'
												
												AND bom.effectiveafter<='" . Date('Y-m-d') . "'
												AND bom.effectiveto>='" . Date('Y-m-d') . "'");
										//		AND bom.loccode=(SELECT loccode FROM workorders WHERE wo='" . $_POST['WONO'] . "')
			$CostRow = DB_fetch_array($CostResult);
				if (is_null($CostRow['cost']) OR $CostRow['cost']==0){
					$Cost =0;
				//保留	prnMsg(_('The cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
				} else {
					$Cost = $CostRow['cost'];
				}
				if (!isset($EOQ)){
				$EOQ=1;
				}
				//prnMsg('348'.$_POST['WO'. $POLine->LineNo]);
				if ($_POST['WO'. $POLine->LineNo ]>0){
					$SQL = "UPDATE woitems SET qtyreqd =  	'" . $EOQ . "',
												 nextlotsnref = '0'
											WHERE wono='" . $_POST['WONO'] . "' AND wo=	'" . $POLine->LocName . "'
											AND stockid='" . $POLine->StockID . "'";
				}else{
					$SQL = "INSERT INTO woitems (wono,
												stockid,
												loccode,
												qtyreqd,
												stdcost,
												comments,
												diff)
											VALUES (
												'" . $_POST['WONO'] . "',
												'" . $POLine->StockID . "',
												'" . $POLine->LocCode . "',
												'" . $EOQ . "',
												'" . $Cost . "',
												'" . $POLine->Narrative . "',
												'" . $POLine->Diff . "'	)";


				}
		    $ErrMsg = _('The work order item could not be added');
			$result = DB_query($SQL,$ErrMsg);
						
			$_POST['WO']  = DB_Last_Insert_ID($db,'woitems','wo');
			if ($result){
				$inrow++;
			}

			//Recursively insert real component requirements - see includes/SQL_CommonFunctions.in for function WoRealRequirements
			WoRealRequirements($db, $_POST['WO'],  $POLine->LocCode,  $POLine->StockID);
			}
		}
		$result = DB_Txn_Commit();
		if (isset($_POST['submit']) && $inrow>0) {
			$EditingExisting=false;
		unset($_SESSION['WO'.$identifier]->LineItems);
		unset($_SESSION['WO'.$identifier]);
		unset($identifier);
			prnMsg('工单号:'.$_POST['WONO'].' '._('The work order has been updated'),'success');
		}
		//prnMsg($SQL_ReqDate);
		/*
		for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
				$QtyRecd+=$_POST['RecdQty'.$i];
		}
		unset($SQL);
		prnMsg('286'.$QtyRecd.'='.$i);
		if ($QtyRecd==0){ //can only change factory location if Qty Recd is 0loccode='" . $_POST['StockLocation'] .
				$SQL[] = "UPDATE workorders SET requiredby='" . $SQL_ReqDate . "',
												startdate='" . FormatDateForSQL($_POST['StartDate']) ."'
											WHERE wono='" . $_POST['WONO'] . "'";
		} else {
				prnMsg(_('The factory where this work order is made can only be updated if the quantity received on all output items is 0'),'warn');
				$SQL[] = "UPDATE workorders SET requiredby='" . $SQL_ReqDate . "',
												startdate='" . FormatDateForSQL($_POST['StartDate']) . "'
											WHERE wono='" . $_POST['WONO'] . "'";
		}

		for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
			if (!isset($_POST['NextLotSNRef'.$i])) {
				$_POST['NextLotSNRef'.$i]='';
			}
			if (!isset($_POST['WOComments'.$i])) {
				$_POST['WOComments'.$i]='';
			}
			//if ($_POST['WO'.$i]>0){
			$SQL[] = "UPDATE woitems SET comments = '". $_POST['WOComments'.$i] ."'
										WHERE wono='" . $_POST['WONO'] . "' AND wo='".$_POST['WO'.$i]."'
										AND stockid='" . $_POST['OutputItem'.$i] . "'";
			//}else{			}
			if (isset($_POST['QtyRecd'.$i]) AND $_POST['QtyRecd'.$i]>$_POST['OutputQty'.$i]){
				$_POST['OutputQty'.$i]=$_POST['QtyRecd'.$i]; //OutputQty must be >= Qty already reced
			}
			if ($_POST['RecdQty'.$i]==0 AND (!isset($_POST['HasWOSerialNos'.$i]) OR $_POST['HasWOSerialNos'.$i]==false)){
				// can only change location cost if QtyRecd=0 
				$CostResult = DB_query("SELECT SUM((materialcost+labourcost+overheadcost)*bom.quantity) AS cost
												FROM stockmaster
												INNER JOIN bom ON stockmaster.stockid=bom.component
												WHERE bom.parent='" . $_POST['OutputItem'.$i] . "'
												AND bom.loccode='" . $_POST['StockLocation'] . "'
												AND bom.effectiveafter<='" . Date('Y-m-d') . "'
												AND bom.effectiveto>='" . Date('Y-m-d') . "'");
				$CostRow = DB_fetch_array($CostResult);
				if (is_null($CostRow['cost'])){
					$Cost =0;
					prnMsg(_('The cost of this item as accumulated from the sum of the component costs is nil. This could be because there is no bill of material set up ... you may wish to double check this'),'warn');
				} else {
					$Cost = $CostRow['cost'];
				}
				if ($_POST['WO'.$i]>0){
				$SQL[] = "UPDATE woitems SET qtyreqd =  '". $_POST['OutputQty' . $i] . "',
											 nextlotsnref = '". $_POST['NextLotSNRef'.$i] ."',
											 stdcost ='" . $Cost . "'
										WHERE wono='" . $_POST['WONO'] . "' AND wo='".$_POST['WO'.$i]."'
										AND stockid='" . $_POST['OutputItem'.$i] . "'";
				}else{
					$SQL[] = "INSERT INTO woitems (wono,
											stockid,
											loccode,
											qtyreqd,
											stdcost,
											comments)
										VALUES (
											'" . $_POST['WONO'] . "',
											'" . $_POST['OutputItem'.$i] . "',
											'". $_POST['LocCode' . $i] . "',
											'". $_POST['OutputQty' . $i] . "',
											'" . $Cost . "',
											'". $_POST['Narrative' . $i] . "'
										)";

				}			

  			} elseif (isset($_POST['HasWOSerialNos'.$i]) AND $_POST['HasWOSerialNos'.$i]==false) {
				if ($_POST['WO'.$i]>0){
				$SQL[] = "UPDATE woitems SET qtyreqd =  '". $_POST['OutputQty' . $i] . "',
											 nextlotsnref = '". $_POST['NextLotSNRef'.$i] ."'
										WHERE wono='" . $_POST['WONO'] . "' AND wo='".$_POST['WO'.$i]."'
										AND stockid='" . $_POST['OutputItem'.$i] . "'";
				}else{
					$SQL[] = "INSERT INTO woitems (wono,
											stockid,
											loccode,
											qtyreqd,
											stdcost,
											comments)
										VALUES (
											'" . $_POST['WONO'] . "',
											'" . $_POST['OutputItem'.$i] . "',
											'". $_POST['LocCode' . $i] . "',
											'". $_POST['OutputQty' . $i] . "',
											'" . $Cost . "',
											'". $_POST['Narrative' . $i] . "'
										)";

				}
			    }
		}*/

		//run the SQL from either of the above possibilites
		$ErrMsg = _('The work order could not be added/updated');
		foreach ($SQL as $SQL_stmt){
		//	echo '<br />' . $SQL_stmt;
			$result = DB_query($SQL_stmt,$ErrMsg);

		}
	

		for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
		  		 unset($_POST['OutputItem'.$i]);
				 unset($_POST['OutputQty'.$i]);
				 unset($_POST['QtyRecd'.$i]);
				 unset($_POST['NetLotSNRef'.$i]);
				 unset($_POST['HasWOSerialNos'.$i]);
				 unset($_POST['WOComments'.$i]);
		}
		
	}
} elseif(isset($_POST['delete'])) {
				
	unset($_SESSION['WO'.$identifier]->LineItems);
	unset($_SESSION['WO'.$identifier]);
	unset($identifier);

		//echo '<p><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select an existing outstanding work order') . '</a></p>';
		
		unset($_POST['WO']);
		for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++) {
			unset($_POST['OutputItem'.$i]);
			unset($_POST['OutputQty'.$i]);
			unset($_POST['QtyRecd'.$i]);
			unset($_POST['NetLotSNRef'.$i]);
			unset($_POST['HasWOSerialNos'.$i]);
			unset($_POST['WOComments'.$i]);
		}
	//	include('includes/footer.php');
	//	exit;
	//}	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/SO_Items.php?identifier=' . $identifier . '">';
}


if (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	    
		$_SESSION['WONO'.$identifier]->remove_from_work($_GET['Delete']);
	//	prnMsg( _('This item cannot be deleted because some of it has already been received'),'warn');
	//	$CancelDelete=false; //always assume the best

	// can't delete it there are open work issues
	$HasTransResult = DB_query("SELECT transno
									FROM stockmoves
								WHERE (stockmoves.type= 26 OR stockmoves.type=28)
								AND reference " . LIKE  . " '%" . $_POST['WONO'] . "%'");
	if (DB_num_rows($HasTransResult)>0){
		prnMsg(_('This work order cannot be deleted because it has issues or receipts related to it'),'error');
		$CancelDelete=true;
	}

	if ($CancelDelete==false) { //ie all tests proved ok to delete
		DB_Txn_Begin();
		$ErrMsg = _('The work order could not be deleted');
		$DbgMsg = _('The SQL used to delete the work order was');
		//delete the worequirements
		$SQL = "DELETE FROM worequirements WHERE wo='" . $_POST['WONO'] . "'";
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		//delete the items on the work order
		$SQL = "DELETE FROM woitems WHERE wo='" . $_POST['WONO'] . "'";
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		//delete the controlled items defined in wip
		$SQL="DELETE FROM woserialnos WHERE wo='" . $_POST['WONO'] . "'";
		$ErrMsg=_('The work order serial numbers could not be deleted');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
		// delete the actual work order
		$SQL="DELETE FROM workorders WHERE wo='" . $_POST['WONO'] . "'";
		$ErrMsg=_('The work order could not be deleted');
		$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

		DB_Txn_Commit();
		prnMsg(_('The work order has been cancelled'),'success');


		echo '<p><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select an existing outstanding work order') . '</a></p>';
		unset($_POST['WONO']);
		for ($i=1;$i<=$_POST['NumberOfOutputs'];$i++){
			unset($_POST['OutputItem'.$i]);
			unset($_POST['OutputQty'.$i]);
			unset($_POST['QtyRecd'.$i]);
			unset($_POST['NetLotSNRef'.$i]);
			unset($_POST['HasWOSerialNos'.$i]);
			unset($_POST['WOComments'.$i]);
		}
		include('includes/footer.php');
		exit;
	}
}
if ($EditingExisting==true){
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '&WONO='.$SelectedWO.'" method="post" id="choosesupplier">';
}else{
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" id="choosesupplier">';

}
//echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" name="form1">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<br /><table class="selection">';
//prnMsg($_SESSION['WO'.$identifier]->LineCounter.'[462='.$identifier);
//以下为读取已经存在的workorder
if ($EditingExisting==true && $_SESSION['WO'.$identifier]->LineCounter==0){
	$SQL="SELECT workorders.loccode,
				requiredby,
				startdate,
				costissued,
				closed
			FROM workorders				
			WHERE workorders.wono='" . $_POST['WONO'] . "'";
			//INNER JOIN locations ON workorders.loccode=locations.loccode
	//INNER JOIN locationusers ON locationusers.loccode=workorders.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
	
	$WOResult = DB_query($SQL);
	if (DB_num_rows($WOResult)==1){

		$myrow = DB_fetch_array($WOResult);
		$_SESSION['WO'.$identifier]->StartDate=ConvertSQLDate($myrow['startdate']);
		$_SESSION['WO'.$identifier]->RequiredbyDate= ConvertSQLDate($myrow['requiredby']);	
		$_SESSION['WO'.$identifier]->WorkOrderNo=$_POST['WONO'] ;
		//$_POST['StartDate'] = ConvertSQLDate($myrow['startdate']);
		$_POST['CostIssued'] = $myrow['costissued'];
		$_POST['Closed'] = $myrow['closed'];
		//$_POST['RequiredBy'] = ConvertSQLDate($myrow['requiredby']);
		$_POST['StockLocation'] = $myrow['loccode'];
		$ErrMsg =_('Could not get the work order items');
		$WOItemsResult = DB_query("SELECT   woitems.stockid,
											stockmaster.description,
											stockmaster.units,
											qtyreqd,
											qtyrecd,
											stdcost,
											nextlotsnref,
											controlled,
											serialised,
											stockmaster.decimalplaces,
											nextserialno,
											loccode,
											wo,
											woitems.comments,
											woitems.diff
									FROM woitems INNER JOIN stockmaster
									ON woitems.stockid=stockmaster.stockid
									WHERE wono='" .$_POST['WONO'] . "'
									AND wo='" .$_POST['WO'] . "'",
									$ErrMsg);
			//prnMsg()
		$NumberOfOutputs=DB_num_rows($WOItemsResult);
		$i=1;
		while ($WOItem=DB_fetch_array($WOItemsResult)){
					//$_POST['OutputItem' . $i]=$WOItem['stockid'];
					//$_POST['OutputItemDesc'.$i]=$WOItem['description'];
					//$_POST['OutputQty' . $i]= $WOItem['qtyreqd'];
					//$_POST['RecdQty' .$i] =$WOItem['qtyrecd'];
					//$_POST['WOComments' .$i] =$WOItem['comments'];
					//$_POST['DecimalPlaces' . $i] = $WOItem['decimalplaces'];
					if ($WOItem['serialised']==1 AND $WOItem['nextserialno']>0){
					$_POST['NextLotSNRef' .$i]=$WOItem['nextserialno'];
					} else {
					$_POST['NextLotSNRef' .$i]=$WOItem['nextlotsnref'];
					}
					//$_POST['Controlled'.$i] =$WOItem['controlled'];
					$_POST['Serialised'.$i] =$WOItem['serialised'];
					$HasWOSerialNosResult = DB_query("SELECT wo FROM woserialnos WHERE wo='" . $_POST['WONO'] . "'");
					if (DB_num_rows($HasWOSerialNosResult)>0){
					$_POST['HasWOSerialNos']=true;
					} else {
					$_POST['HasWOSerialNos']=false;
					}
					$QOH=0;
					$PuWoQty=0;
					$DemandQty=0;
					$_SESSION['WO'.$identifier]->add_to_work ($_SESSION['WO'.$identifier]->LineCounter,
																$WOItem['stockid'],
																$WOItem['description'],
																$WOItem['loccode'],
																$WOItem['wo'],
																$WOItem['qtyreqd'] ,
																$WOItem['units'] ,
																$QOH,
																$PuWoQty,
																$DemandQty,
																$WOItem['diff'] ,
																$WOItem['comments'],
																0,
																$WOItem['decimalplaces'],
																$WOItem['controlled']);
					$i++;
		}
		//prnMsg($i.'-479');
	} else {
		if ($EditingExisting==true){
			prnMsg(_('Your location security settings do not allow you to Update this Work Order'),'error');
			echo '<br /><a href="' . $RootPath . '/SelectWorkOrder.php">' . _('Select an existing work order') . '</a>';
			include('includes/footer.php');
			exit;
		}

	}
}

//echo '<input type="hidden" name="WO" value="' .$_POST['WONO'] . '" />';
/*
echo '<tr><td class="label">' . _('Work Order Reference') . ':</td>
          <td>' . $_POST['WONO'] . '</td></tr>';*/

if (!isset($_POST['StartDate'])){
	$_POST['StartDate'] = Date($_SESSION['DefaultDateFormat']);
}

echo '<tr>
		<td class="label">' . _('Start Date') . ':</td>
		<td><input type="text" name="StartDate" size="12" maxlength="12" value="' . $_POST['StartDate'] .'" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" /></td>
	</tr>';

if (!isset($_POST['RequiredBy'])){
	$_POST['RequiredBy'] = Date($_SESSION['DefaultDateFormat'],strtotime("+1 month"));
}

echo '<tr>
		<td class="label">入库日期:</td>
		<td><input type="text" name="RequiredBy" size="12" maxlength="12" value="' . $_POST['RequiredBy'] .'" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" /></td>
	</tr>';
	
if (isset($WOResult)){
	echo '<tr><td class="label">' . _('Accumulated Costs') . ':</td>
			  <td class="number">' . locale_number_format($myrow['costissued'],$_SESSION['CompanyRecord']['decimalplaces']) . '</td></tr>';
}
echo '</table>';
if (isset($_POST['SelectingOrderItems'])){
	//prnMsg('517readwork');
	/* get the item details from the database and hold them in the cart object make the quantity 1 by default then add it to the cart */
	/*Now figure out if the item is a kit set - the field MBFlag='K'*/
	$AlreadyWarnedAboutCredit = false;
	
	foreach ($_POST as $FormVariable => $Quantity) {
		if (mb_strpos($FormVariable,'OrderQty')!==false && $Quantity>0) {
			//$NewItemArray[$_POST['StockID' . mb_substr($FormVariable,8)]] = filter_number_format($Quantity);
			$i=mb_substr($FormVariable,8);
			$NewItem=$_POST['StockID' . $i];
			$NewItemQty = filter_number_format($Quantity);
			//prnMsg( _('The item code').$FormVariable.'=' .$NewItemQty . '655-'.$Quantity, 'warn');
			$Description =$_POST['Description' . $i];
			$UOM =$_POST['Units' . $i];
			$PuWoQty =$_POST['PuWoQty' . $i];
			$QOH= $_POST['QOH' . $i];
			$Diff= $_POST['Diff' . $i];
			$LocCode= explode('^',$_POST['LocCode' . $i])[0];
			$LocName= explode('^',$_POST['LocCode' . $i])[1];			
			$DemandQty =$_POST['DemandQty' . $i];
			//prnMsg($QOH.'-qoh'.$PuWoQty.'-'.$DemandQty.'='.$NewItemQty.'=='.$_POST['DecimalPlaces'.$i]);
			$sql = "SELECT stockmaster.mbflag
					FROM stockmaster
					WHERE stockmaster.stockid='". $NewItem ."'";

			$ErrMsg =  _('Could not determine if the part being ordered was a kitset or not because');

			$KitResult = DB_query($sql,$ErrMsg);

			//$NewItemQty = 1; /*By Default */
			$Discount = 0; /*By default - can change later or discount category override */

			if ($myrow=DB_fetch_array($KitResult)){
			//	prnMsg( _('The item code') .$myrow['mbflag'].'1204- ' . $sql , 'warn');
				if ($myrow['mbflag']=='K'){	/*It is a kit set item */
					$sql = "SELECT bom.component,
									bom.quantity
							FROM bom
							WHERE bom.parent='" . $NewItem . "'
							AND bom.effectiveafter <= '" . date('Y-m-d') . "'
							AND bom.effectiveto > '" . date('Y-m-d') . "'";

					$ErrMsg = _('Could not retrieve kitset components from the database because');
					$KitResult = DB_query($sql,$ErrMsg);

					$ParentQty = $NewItemQty;
					$NewItemDue = date($_SESSION['DefaultDateFormat']);
					while ($KitParts = DB_fetch_array($KitResult)){
						$NewItem = $KitParts['component'];
						$NewItemQty = $KitParts['quantity'] * $ParentQty;
						//$NewItemDue = date($_SESSION['DefaultDateFormat']);
					   
						//include('includes/SelectOrderItemsIntoCartCN.inc')
				
						$_SESSION['WO'.$identifier]->add_to_work ($_SESSION['WO'.$identifier]->LineCounter,
																   $NewItem,
																   $Description,
																   $LocCode,
																   $LocName,	
																   $NewItemQty ,
																   $UOM,
																   $QOH,
																   $PuWoQty,
																   $DemandQty,
																   $_POST['WODiff'.$i],
																    $_POST['WOComments'.$i],
																   0,
																	$_POST['DecimalPlaces'.$i],
																    $_POST['Controlled'.$i]);
					}

				} else { /*Its not a kit set item*/
					
					//$NewPOLine = 0;
				
					$_SESSION['WO'.$identifier]->add_to_work ($_SESSION['WO'.$identifier]->LineCounter,
																$NewItem,
																$Description,
																$LocCode,
																$LocName,	
																$NewItemQty ,
																$UOM,
																$QOH,
																$PuWoQty,
																$DemandQty,
																$_POST['WODiff'.$i],
																$_POST['WOComments'.$i],
																0,
																$_POST['DecimalPlaces'.$i],
																$_POST['Controlled'.$i]);
				}
			} /* end of if its a new item */
			
				//	include('includes/SelectOrderItemsIntoCartCN.inc');
				
		} /*end of if its a new item */
	}//forearch 
	
	
	//var_dump($_SESSION['WO'.$identifier]->LineItems);
	//prnMsg(count($_SESSION['WO'.$identifier]->LineItems));
} /* if the NewItem_array is set */
echo'<br /><table class="selection">';
  echo '<tr><th>' . _('Output Item') . '</th>
            <th>仓库</th>
			<th>' . _('Units') . '</th>
			<th class="ascending" >' . _('On Hand') . '</th>
			<th class="ascending" >工单<br/>采购数</th>
			<th class="ascending" >订单数</th>
			<th class="ascending" >预计可用数</th>
			<th>' . _('Comments') . '</th>
			<th>入库<br>限制%</th>
			<th>计划工单数</th>				
			<th>' . _('Next Lot/SN Ref') . '</th>
			<th></th>		
			</tr>';
			$j=0;
			$i=0;
			foreach ($_SESSION['WO'.$identifier]->LineItems as $POLine) {
				if ($POLine->Deleted == false) {
					if ($j==1) {
						echo '<tr class="OddTableRows">';
						$j=0;
					} else {
						echo '<tr class="EvenTableRows">';
						$j++;
					}
					$Available=$POLine->QOH+$POLine->OrderQty-$POLine->DemandQty;
					$_POST['WODiff' . $POLine->LineNo]=0;
					echo '<td>
					        <input type="hidden" name="OutputItem' . $POLine->LineNo . '" value="' . $POLine->StockID . '" />' .
							$POLine->StockID . ' - ' . $POLine->Description . '</td>';
					echo '<td>
					        <input type="hidden" name="LocCode' . $POLine->LineNo . '"  value="' .$POLine->LocCode  . '" /></td>';
				
					echo '<td>
					        <input type="hidden" name="Units' . $POLine->LineNo . '"  value="' .$POLine->Units  . '" />' .$POLine->Units. '</td>';
				
					echo '<td class="number">
					        <input type="hidden" name="OnQOH' . $POLine->LineNo . '" value="' .$POLine->QOH. '" />'. locale_number_format($POLine->QOH, $POLine->DecimalPlaces)  . 
					       '</td>';
					echo '<td class="number">
					       <input type="hidden" name="OnOrder' . $POLine->LineNo . '" value="' . $POLine->OrderQty. '" />' .  locale_number_format($POLine->OrderQty, $POLine->DecimalPlaces). '</td>';
					echo '<td class="number">
					       <input type="hidden" name="OnDemand' . $POLine->LineNo . '" value="' . $POLine->DemandQty. '" />' .  locale_number_format($POLine->DemandQty, $POLine->DecimalPlaces). '</td>';
					echo '<td class="number">
						   <input type="hidden" name="Available' . $POLine->LineNo . '" value="' . $Available. '" />
						   ' .  locale_number_format($Available, $POLine->DecimalPlaces). '</td>';
				
					echo'<td><input type="text"  name="WOComments' . $POLine->LineNo . '" id="WOComments' . $POLine->LineNo . '" value="' . $_POST['WOComments' . $POLine->LineNo] . '"   onChange="inComments(this)"  /></td>';
					echo'<td><input type="number"  name="WODiff' . $POLine->LineNo . '" id="WODiff' . $POLine->LineNo . '"   min="0" max="10"   step = "1" size="5"   maxlength="5"  value="' . $_POST['WODiff' . $POLine->LineNo] . '"   title="设置入库数量允许幅度,最大10%"  onChange="inReceive(this)"     /></td>';
				
					if ($POLine->Controlled==1 AND $_SESSION['DefineControlledOnWOEntry']==1){
						echo '<td class="number">' . locale_number_format($_POST['OutputQty' . $POLine->LineNo], $_POST['DecimalPlaces' . $POLine->LineNo]) . '</td>';
						echo '<input type="hidden" name="OutputQty' . $POLine->LineNo .'" id="OutputQty' . $POLine->LineNo .'" value="' . locale_number_format($_POST['OutputQty' . $POLine->LineNo]-$_POST['RecdQty' .$POLine->LineNo], $_POST['DecimalPlaces' . $POLine->LineNo]) . '" />';
					} else {
						echo'<td><input type="text"  class="number" name="OutputQty' . $POLine->LineNo . '" id="OutputQty' . $POLine->LineNo .'" size="5"  value="' . locale_number_format($POLine->Qty,2 ) . '" size="10" maxlength="10"    onChange="inOutputQty(this,'.$POLine->DecimalPlaces .')"   title="'._('The input format must be positive numeric').'" /></td>';
					}
					if ($POLine->Controlled==1){
						echo '<td><input type="text" name="NextLotSNRef' .$POLine->LineNo . '" value="' . $_POST['NextLotSNRef'.$POLine->LineNo] . '" /></td>';
						if ($_SESSION['DefineControlledOnWOEntry']==1){
							if ($_POST['Serialised' . $POLine->LineNo]==1){
								$LotOrSN = _('S/Ns');
							} else {
								$LotOrSN = _('Batches');
							}
							echo '<td><a href="' . $RootPath . '/WOSerialNos.php?WO=' . $POLine->LineNo. '&StockID=' . $_POST['OutputItem' .$POLine->LineNo] . '&Description=' . $_POST['OutputItemDesc' .$POLine->LineNo] . '&Serialised=' . $_POST['Serialised' .$POLine->LineNo] . '&NextSerialNo=' . $_POST['NextLotSNRef' .$POLine->LineNo] . '">' . $LotOrSN . '</a></td>';
						}
					}
					echo '<td>';
					if ($_SESSION['WikiApp']!=0){
						wikiLink('WorkOrder', $_POST['WONO'] . $_POST['OutputItem' .$POLine->LineNo]);
					}
					echo '</td>';
					echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$POLine->LineNodentifier .'&amp;Delete=' . $POLine->LineNo . '">' . _('Delete'). '</a></td>';

					echo '</tr>';
					if (isset($_POST['Controlled' . $POLine->LineNo])) {
						echo '<input type="hidden" name="Controlled' . $POLine->LineNo .'" value="' . $_POST['Controlled' . $POLine->LineNo] . '" />';
					}
					if (isset( $_POST['Serialised' . $POLine->LineNo])) {
						echo '<input type="hidden" name="Serialised' . $POLine->LineNo .'" value="' . $_POST['Serialised' . $POLine->LineNo] . '" />';
					}
					if (isset($_POST['HasWOSerialNos' . $POLine->LineNo])) {
						echo '<input type="hidden" name="HasWOSerialNos' . $POLine->LineNo .'" value="' . $_POST['HasWOSerialNos' . $POLine->LineNo] . '" />';
					}
	
				echo '<input type="hidden" name="NumberOfOutputs" value="' . ($POLine->LineNo).'" />
				<input type="hidden" id="WO' . $POLine->LineNo . '" name="WO' . $POLine->LineNo . '" value="' .$POLine->LocName. '">

				<input type="hidden" id="edit' . $POLine->LineNo . '" name="edit' . $POLine->LineNo . '" value="">';
			}
			//$i++;
			}
			echo '</table>';
			
			echo '<br /><div class="centre"><button type="submit" name="submit">' . _('Save') . '</button></div>';
			
 		echo '<br /><div class="centre"><button type="submit" name="delete" onclick="return confirm(\'' . _('Are You Sure?') . '\');">' . _('Cancel This Work Order') . '</button>';

echo '</div><br />';

$SQL="SELECT categoryid, 
             categorydescription
			FROM stockcategory
			INNER JOIN locationusers ON locationusers.loccode = categoryid AND locationusers.userid = '".$_SESSION['UserID']."' AND locationusers.canupd = 1
			WHERE  stocktype = 'M'
			ORDER BY categorydescription";
	$result1 = DB_query($SQL);

echo '<table class="selection"><tr><td>' . _('Select a stock category') . ':<select name="StockCat">';

if (!isset($_POST['StockCat'])){
	echo '<option selected="True" value="All">' . _('All') . '</option>';
	$_POST['StockCat'] ='All';
} else {
	echo '<option value="All">' . _('All') . '</option>';
}

while ($myrow1 = DB_fetch_array($result1)) {

	if ($_POST['StockCat']==$myrow1['categoryid']){
		echo '<option selected="True" value=' . $myrow1['categoryid'] . '>' . $myrow1['categorydescription'] . '</option>';
	} else {
		echo '<option value='. $myrow1['categoryid'] . '>' . $myrow1['categorydescription'] . '</option>';
	}
}

if (!isset($_POST['Keywords'])) {
    $_POST['Keywords']='';
}

if (!isset($_POST['StockCode'])) {
    $_POST['StockCode']='';
}

echo '</select>
		<td>' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</td>
		<td><input type="text" name="Keywords" size="20" maxlength="25" value="' . $_POST['Keywords'] . '" /></td>
	</tr>
    <tr>
		<td>&nbsp;</td>
		<td><font size="3"><b>' . _('OR') . ' </b></font>' . _('Enter extract of the') . ' <b>' . _('Stock Code') . '</b>:</td>
		<td><input type="text" name="StockCode" autofocus="autofocus" size="15" maxlength="18" value="' . $_POST['StockCode'] . '" /></td>
	</tr>
	<tr>
		
		<td><font size="3">合同查询	<input type="checkbox" name="SaleOrder"  value="1" checked /></td>
		<td colspan="2">&nbsp;</td>
	</tr>
	</table>
	<br />
	<div class="centre">
		<button type="submit" name="Search">' . _('Search Now') . '</button>
	</div>';
if (isset($_POST['Search']) OR isset($_POST['Prev']) OR isset($_POST['Next'])){
	if(!empty($_POST['RawMaterialFlag'])){
		$RawMaterialSellable = " OR stockcategory.stocktype='M'";
	}else{
		$RawMaterialSellable = '';
	}
	if(!empty($_POST['CustItemFlag'])){
		$IncludeCustItem = " INNER JOIN custitem ON custitem.stockid=stockmaster.stockid
							AND custitem.debtorno='" .  $_SESSION['Items'.$identifier]->DebtorNo . "' ";
	} else {
		$IncludeCustItem = " LEFT OUTER JOIN custitem ON custitem.stockid=stockmaster.stockid
							AND custitem.debtorno='" .  $_SESSION['Items'.$identifier]->DebtorNo . "' ";
	}

	if ($_POST['Keywords']!='' AND $_POST['StockCode']=='') {
		$msg='<div class="page_help_text">' . _('Order Item description has been used in search') . '.</div>';
	} elseif ($_POST['StockCode']!='' AND $_POST['Keywords']=='') {
		$msg='<div class="page_help_text">' . _('Stock Code has been used in search') . '.</div>';
	} elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$msg='<div class="page_help_text">' . _('Stock Category has been used in search') . '.</div>';
	}
	$SQL = "SELECT stockmaster.stockid,
			stockmaster.description,
			stockmaster.longdescription,
			stockmaster.units,
			stockmaster.controlled,
			stockmaster.categoryid,
			stockmaster.decimalplaces,
			locations.locationname,
			custitem.cust_part,
			custitem.cust_description
		FROM stockmaster INNER JOIN stockcategory
		ON stockmaster.categoryid=stockcategory.categoryid
		LEFT JOIN locations ON locations.loccode=stockmaster.categoryid
		" . $IncludeCustItem . "
		WHERE (stockcategory.stocktype='M' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L' " . $RawMaterialSellable . ")
		AND stockmaster.mbflag <>'G'
		AND stockmaster.discontinued=0 ";
		//WHERE (stockcategory.stocktype='F' OR stockcategory.stocktype='D' OR stockcategory.stocktype='L' " . $RawMaterialSellable . ")
		if (isset($_POST['SaleOrder'])){
			//prnMsg('783'.'='.$_POST['SaleOrder']);
			$SQL.= "AND stockmaster.stockid IN (SELECT DISTINCT  `stkcode`FROM `salesorderdetails`  WHERE  `completed`>=0 ) ";
		}
		if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat']=='All'){
		$SQL .= "AND stockmaster.description " . LIKE . " '" . $SearchString . "'
		ORDER BY stockmaster.stockid";
		} else {
		$SQL .= "AND (stockmaster.description " . LIKE . " '" . $SearchString . "' OR stockmaster.longdescription " . LIKE . " '" . $SearchString . "') 
		AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
		ORDER BY stockmaster.stockid";
		}

		} elseif (mb_strlen($_POST['StockCode'])>0){

		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
		$SearchString = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
		$SQL .= "AND (stockmaster.stockid " . LIKE . " '" . $SearchString . "'
		OR stockmaster.stockno " . LIKE . " '" . $SearchString . "') 
		ORDER BY stockmaster.stockid";
		} else {
		$SQL .= "AND ( stockmaster.stockid " . LIKE . " '" . $SearchString . "' 
				OR stockmaster.stockno " . LIKE . " '" . $SearchString . "') 
				AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
				ORDER BY stockmaster.stockid";
		}

		} else {
		if ($_POST['StockCat']=='All'){
			$SQL .= "ORDER BY stockmaster.stockid";
		} else {
			$SQL .= "AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid";
		}
		}

		if (isset($_POST['Next'])) {
		$Offset = $_POST['NextList'];
		}
		if (isset($_POST['Previous'])) {
		$Offset = $_POST['PreviousList'];
		}
		if (!isset($Offset) OR $Offset < 0) {
		$Offset=0;
		}

		$SQL = $SQL . " LIMIT " . $_SESSION['DisplayRecordsMax'] . " OFFSET " . strval($_SESSION['DisplayRecordsMax'] * $Offset);

		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL used to get the part selection was');

		$SearchResult = DB_query($SQL,$ErrMsg, $DbgMsg);
		 //prnMsg($SQL.'-1074','info');
		if (DB_num_rows($SearchResult)==0 ){
			prnMsg (_('There are no products available meeting the criteria specified'),'info');
		}
		if (DB_num_rows($SearchResult)==1){
			$myrow=DB_fetch_array($SearchResult);
			$NewItem = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}
		if (DB_num_rows($SearchResult) < $_SESSION['DisplayRecordsMax']){
			$Offset=0;
		}
	} //end of if search

	if (isset($SearchResult)) {
		echo '<br />';
		echo '<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>';//ͨ����������������ѡ�����ϡ������ɺ���������
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
				  <input name="SelectingOrderItems" type="hidden" value="1" />
				  <input tabindex="'.strval($j+9).'" type="submit" value="增至工作单" /></td>';
		echo '<td colspan="1">
				  <input name="NextList" type="hidden" value="'.strval($Offset+1).'" />
				  <input tabindex="'.strval($j+10).'" name="Next" type="submit" value="'._('Next').'" /></td></tr>';
		echo '<tr>
				<th class="ascending" >' . _('Code') . '</th>
				<th class="ascending" >' . _('Description') . '</th>
				<th class="ascending" >仓库</th>
				<th>' . _('Units') . '</th>
				<th class="ascending" >' . _('On Hand') . '</th>
				<th class="ascending" >工单<br/>采购数</th>
				<th class="ascending" >订单数</th>
				<th class="ascending" >预计可用数</th>
				<th>' . _('Quantity') . '</th>
			   </tr>';
		$ImageSource = _('No Image');
		$i=0;
		$k=0; //row colour counter
		
		//prnMsg($QOHSQL,'info');
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
			$PuWoQty = $PurchQty + $WoQty;
			$Available = $QOH - $DemandQty + $PuWoQty;
			echo'<td>'.$myrow['stockid'].'
			<input name="StockID'.$i.'" type="hidden" value="'.$myrow['stockid'] .'" /></td>
			<td title="'.$myrow['longdescription'].'">'.$myrow['description'].'
			<input name="Description'.$i.'" type="hidden" value="'.$myrow['description'] .'" /></td>
			<td>'. $myrow['locationname'].'</td>
			<td>'.$myrow['units'].'
			<input name="Units'.$i.'" type="hidden" value="'.$myrow['units'] .'" /></td>
			<td class="number">'.locale_number_format($QOH,$myrow['decimalplaces']).'
			<input name="QOH'.$i.'" type="hidden" value="'.$QOH .'" /></td>
		
			<td class="number">'.locale_number_format($PuWoQty,$myrow['decimalplaces']).'
			<input name="PuWoQty'.$i.'" type="hidden" value="'.$PuWoQty .'" /></td>

			<td class="number">'.locale_number_format($DemandQty,$myrow['decimalplaces']).'
			<input name="DemandQty'.$i.'" type="hidden" value="'.$DemandQty .'" /></td>

			<td class="number">'.locale_number_format($Available,$myrow['decimalplaces']).'</td>
			<td>
			<input class="number" tabindex="'.strval($j+7).'" type="text" size="6" name="OrderQty'.$i.'"  ' . ($i==0 ? 'autofocus="autofocus"':'') . ' value="" min=""/>
			<input name="Controlled'.$i.'" type="hidden" value="'.$myrow['controlled'] .'" />
			<input name="DecimalPlaces'.$i.'" type="hidden" value="'.$myrow['decimalplaces'] .'" />
			<input name="LocCode'.$i.'" type="hidden" value="'.$myrow['categoryid'].'^'.$myrow['locationname'] .'" />
			</td>
			</tr>';
            
			$i++;
			$j++;
		#end of page full new headings if
		}
			#end of while loop
		echo '<tr>
				<td><input name="PreviousList" type="hidden" value="'. strval($Offset-1).'" />
					 <input tabindex="'. strval($j+7).'" type="submit" name="Previous" value="'._('Previous').'" /></td>
				<td style="text-align:center" colspan="6">
					  <input name="SelectingOrderItems" type="hidden" value="1" />
					  <input tabindex="'. strval($j+8).'" type="submit" value="'._('Add to Sales Order').'" /></td>
				<td>
					<input name="NextList" type="hidden" value="'.strval($Offset+1).'" />
					 <input tabindex="'.strval($j+9).'" name="Next" type="submit" value="'._('Next').'" /></td>
			</tr>
			</table>
			</div>';

	}#end if SearchResults to show

if (isset($SearchResult)) {

	if (DB_num_rows($SearchResult)>1){

		$PageBar = '<tr><td colspan="2"><input type="hidden" name="CurrPage" value="'.$Offset.'">';
		if($Offset>0)
			$PageBar .= '<input type="submit" name="Prev" value="'._('Prev').'" />';
		else
			$PageBar .= '<input type="submit" name="Prev" value="'._('Prev').'" disabled="disabled"/>';
			$PageBar .= '</td><td style="text-align:center" colspan="2"><input type="submit" value="复制到工作单" name="NewItem"/></td>
			         <td colspan="3"></td><td>';
		if($Offset<$ListPageMax)
			$PageBar .= '<input type="submit" name="Next" value="'._('Next').'" />';
		else
			$PageBar .= '<input type="submit" name="Next" value="'._('Next').'" disabled="disabled"/>';
		$PageBar .= '</td></tr>';

		echo '<br />
		     <table cellpadding="2" class="selection">';
		echo $PageBar;
		echo '<tr>
				<th class="ascending">' . _('Code') . '</th>
	   			<th class="ascending">' . _('Description') . '</th>
				<th>' . _('Units') . '</th>
				<th>图像</th>
				<th>库存数</th>
				<th>合同数</th>
				<th>未完工数</th>
				<th>工作单数</th>
				</tr>';
		$j = 1;
		$k=0; //row colour counter
		$ItemCodes = array();
		for ($i=1;$i<=$NumberOfOutputs;$i++){
			$ItemCodes[] =$_POST['OutputItem'.$i];
		}

		while ($myrow=DB_fetch_array($SearchResult)) {

			if (!in_array($myrow['stockid'],$ItemCodes)){

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
					$ImageSource = '<img src="' . $imagefile . '" height="64" width="64" />';
				} else {
					$ImageSource = _('No Image');
				}

				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}

			echo'<td><font size="1">'.$myrow['stockid'].'</font></td>
						<td><font size="1">'.$myrow['description'].'</font></td>
						<td><font size="1">'.$myrow['units'].'</font></td>
						<td>'.$ImageSource.'</td>
						<td><font size="1"></font></td>
						<td><font size="1"></font></td>
						<td><font size="1"></font></td>
						<td><input class="number" type="text" size="6" value="" name="NewQty' . $j . '" /></td>
						</tr>';
					
					//	htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?WO=' . $_POST['WONO'] . '&NewItem=' . $myrow['stockid'].'&Line='.$i);

				$j++;
			} //end if not already on work order
		}//end of while loop
	} //end if more than 1 row to show
	echo '</table>';

}#end if SearchResults to show

echo '</form>';
include('includes/footer.php');
?>
