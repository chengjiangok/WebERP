

<?php
/* $Id: StockExpenIssue.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-04 09:33:21 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-09-11 21:19:13
 */
include('includes/DefineStockExpendClass.php');
include('includes/session.php');
$Title ='易耗品发货';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">

	function inQTY(p,d,r){
		//alert("d");
		var  n=p.name.substring(8);	
		var vl = document.getElementById("Price"+n);
		var qty=parseFloat(p.value).toFixed(d);
	
		if (parseFloat(p.value)!=qty){
			p.value=qty;
			alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
		}		
		var taxamo=0;
		var total=0;
		if (vl.value!=""){
			//数量不为空
			document.getElementById("edit"+n).value=1;		
			document.getElementById("Amount"+n).value=(parseFloat(p.value)*parseFloat(vl.value)).toFixed(2);
		}		
		var QtyTotal=0;
		var amototal=0;
		for(var i=1; i<=r; i++){
				
			QtyTotal=parseFloat(QtyTotal)+parseFloat(document.getElementById("Quantity"+i).value);
			amototal=parseFloat(amototal)+parseFloat(document.getElementById("Amount"+i).value);
		}
		document.getElementById("AmountTotal").value =amototal.toFixed(2);
		document.getElementById("QtyTotal").value =QtyTotal.toFixed(2);

	}
</script>';

if (empty($_GET['identifier'])) {
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}
if (!isset($_SESSION['PO' . $identifier])) {
   
	$_SESSION['PO' . $identifier] = new ExpendIssue;
	

} //end if initiating a new PO

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/group_add.png" title="' .
	_('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" id="choosesupplier">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($_POST['UpdateLines']) OR isset($_POST['Commit'])) {
    if ($_POST['SelectPrint']){	
		echo '<a href="' . $RootPath . '/PDFOrder.php?id=33">打印发货单</a> ';
	
		//echo '<meta http-equiv="refresh" content="0"; url="' . $RootPath . '/PDFOrder.php?id=33">';
	
	}
	
	$_SESSION['PO'.$identifier]->DepartmentID=explode('^',$_POST['Department'])[0];
	$_SESSION['PO'.$identifier]->DepartName=explode('^',$_POST['Department'])[1];
	$_SESSION['PO'.$identifier]->IssueDate=$_POST['DefaultIssueDate'];
	$_SESSION['PO'.$identifier]->Account=explode('^',$_POST['Department'])[2];	
    foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
		
		if ($POLine->Deleted == false) {
			if ($_POST['edit'.$POLine->LineNo]==1){//标记为更新
				
				$_SESSION['PO'.$identifier]->update_item($POLine->LineNo,
										-$_POST['Quantity'.$POLine->LineNo],
										$_POST['Price'.$POLine->LineNo]);
				
				$_POST['edit'.$POLine->LineNo]=0;
			}
		}
	  
	}
	$inputerr=true;
	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
		if ($POLine->Deleted == false) {
		
			if (!is_numeric(filter_number_format($_POST['Quantity'.$POLine->LineNo]))||(float)$_POST['Quantity'.$POLine->LineNo]==0){
				prnMsg(_('The quantity in the supplier units is expected to be numeric. Please re-enter as a number'),'error');
			   $inputerr=false;
			}
			if (!is_numeric(filter_number_format($_POST['Price'.$POLine->LineNo]))||(float)$_POST['Price'.$POLine->LineNo]<=0){
				prnMsg(_('The supplier price is expected to be numeric. Please re-enter as a number'),'error');
				$inputerr=false;
			}
		}
	}

}

if (isset($_POST['Commit'])&&$inputerr==true){

		if (IsEmailAddress($_SESSION['UserEmail'])){
			$UserDetails  = ' <a href="mailto:' . $_SESSION['UserEmail'] . '">' . $_SESSION['UsersRealName']. '</a>';
		} else {
			$UserDetails  = ' ' . $_SESSION['UsersRealName'] . ' ';
		}
		//授权金额查验
		/*
		if ($_SESSION['AutoAuthorisePO']==1) {
			//if the user has authority to authorise the PO then it will automatically be authorised
			$AuthSQL ="SELECT authlevel
						FROM purchorderauth
						WHERE userid='".$_SESSION['UserID']."'
						AND currabrev='".$_SESSION['PO'.$identifier]->CurrCode."'";

			$AuthResult=DB_query($AuthSQL);
			$AuthRow=DB_fetch_array($AuthResult);

			if (DB_num_rows($AuthResult) > 0 AND $AuthRow['authlevel'] > $_SESSION['PO'.$identifier]->Order_Value()) { //user has authority to authrorise as well as create the order
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created and Authorised by') . $UserDetails . '<br />' .  $_SESSION['PO'.$identifier]->StatusComments . '<br />';
				$_SESSION['PO'.$identifier]->AllowPrintPO=3;
				$_SESSION['PO'.$identifier]->Status = 'Authorised';
			} else { // no authority to authorise this order
				if (DB_num_rows($AuthResult) ==0){
					$AuthMessage = _('Your authority to approve purchase orders in') . ' ' . $_SESSION['PO'.$identifier]->CurrCode . ' ' . _('has not yet been set up') . '<br />';
				} else {
					$AuthMessage = _('You can only authorise up to').' '.$_SESSION['PO'.$identifier]->CurrCode.' '.$AuthRow['authlevel'] .'.<br />';
				}

				prnMsg( _('You do not have permission to authorise this purchase order').'.<br />' .  _('This order is for').' '.
					$_SESSION['PO'.$identifier]->CurrCode . ' '. $_SESSION['PO'.$identifier]->Order_Value() .'. '.
					$AuthMessage .
					_('If you think this is a mistake please contact the systems administrator') . '<br />' .
					_('The order will be created with a status of pending and will require authorisation'), 'warn');

				$_SESSION['PO'.$identifier]->AllowPrintPO=0;
				$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . $UserDetails . '<br />' . $_SESSION['PO'.$identifier]->StatusComments . '<br />';
				$_SESSION['PO'.$identifier]->Status = 'Pending';
			}
		} else { //auto authorise is set to off
			$_SESSION['PO'.$identifier]->AllowPrintPO=3;
			$StatusComment=date($_SESSION['DefaultDateFormat']).' - ' . _('Order Created by') . $UserDetails . ' - '.$_SESSION['PO'.$identifier]->StatusComments . '<br />';
			$_SESSION['PO'.$identifier]->Status = 'Pending';
		}*/
		$GRN = GetNextTransNo(39, $db);
		$result = DB_Txn_Begin();
		$inrow=0;
		$rowtol=0;
		foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
			if ($POLine->Deleted==False) {
				$rowtol++;
				if ($POLine->StockID!=''){ // if the order line is in fact a stock item //	
						// Update location stock records - NB  a PO cannot be entered for a dummy/assembly/kit parts //		
						// Need to get the current location quantity will need it later for the stock movement //
					$SQL="SELECT locstock.quantity
									FROM locstock
									WHERE locstock.stockid='" . $POLine->StockID . "'
									AND loccode= '" . $POLine->LocCode . "'";
	
					$Result = DB_query($SQL);
					
					if (DB_num_rows($Result)==1){
						$LocRow = DB_fetch_row($Result);
						$QtyOnHandPrior = $LocRow[0];
					} else {
						//There must actually be some error this should never happen //
						$QtyOnHandPrior = 0;
					}
					$SQL="SELECT newqoh,
							  newamount
						FROM  stockmoves
						WHERE  stockid='" . $POLine->StockID . "'
						 AND stkmoveno IN (SELECT  stkmoveno	FROM   stockmoves 	GROUP BY stockid	HAVING   MAX(stkmoveno))";
					$Result = DB_query($SQL);
					$LocRow = DB_fetch_assoc($Result);
					//存货价格
					if (empty($LocRow['newqoh'])){
						$OldQoh = 0;
						$OldAmount=0;
						$InvPrice=$POLine->Price;
					}elseif($LocRow['newqoh']<0){
						$OldQoh = $LocRow['newqoh'];
						$OldAmount = $LocRow['newamount'];
						$InvPrice=$POLine->Price;
				
					} else {
										
						$OldQoh = $LocRow['newqoh'];
						$OldAmount = $LocRow['newamount'];
						$InvPrice=abs(round(($LocRow['newamount']/ $LocRow['newqoh']),$_SESSION['StandardCostDecimalPlaces']));

					}
				}	
				$SQL = "UPDATE locstock
							SET quantity = locstock.quantity + '" .  $POLine->Quantity . "'
						WHERE locstock.stockid = '" . $POLine->StockID . "'
						AND loccode = '" . $POLine->LocCode . "'";
				
				$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The location stock record could not be updated because');
				$DbgMsg =  _('The following SQL to update the location stock record was used');
				$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
				if ($Result){
					$inrow++;				
				}
				// Insert stock movements - with unit cost //	
				$sql = "INSERT INTO stockmoves (stockid,
												type,
												transno,
												loccode,
												trandate,
												userid,
												price,
												prd,
												branchcode,
												reference,
												qty,
												standardcost,
												newqoh,
												newamount,
												connectid)
									VALUES (
										'" . $POLine->StockID . "',
										39,
										'" . $GRN . "',
										'" . $POLine->LocCode . "',
										'" . $_SESSION['PO'.$identifier]->IssueDate . "',
										'" . $_SESSION['UserID'] . "',
										'" .$InvPrice  . "',
										'0',
										'" .$_SESSION['PO'.$identifier]->DepartmentID  . "',
										'" .$_SESSION['PO'.$identifier]->DepartmentID  . "',
										'" .  ($POLine->Quantity) . "',
										'" . $POLine->Price . "',
										'" . ($QtyOnHandPrior +  $POLine->Quantity) . "',
										'" . ($OldAmount +  $POLine->Quantity*$InvPrice) . "',
										'" .$_SESSION['PO'.$identifier]->DepartmentID  . "'
										)";
				//存货计价方法InventoryValuationMethod=1 加权平均法  0 移动加权平均法
				$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('stock movement records could not be inserted because');
				$DbgMsg =  _('The following SQL to insert the stock movement records was used');
				//prnMsg($sql);
				$result = DB_query($sql, $ErrMsg, $DbgMsg, true);
				if ($result){
					$inrow++;				
				}
			
				$StkMoveNo = DB_Last_Insert_ID($db,'stockmoves','stkmoveno');
				
				if ($POLine->Controlled ==1){
					prnMsg('受控物料开启！');
					foreach($POLine->SerialItems as $Item){
						// we know that StockItems return an array of SerialItem (s)
						//We need to add the StockSerialItem record and	 The StockSerialMoves as well //
						//need to test if the controlled item exists first already
							$SQL = "SELECT COUNT(*) FROM stockserialitems
									WHERE stockid='" . $POLine->StockID . "'
									AND loccode = '" . $POLine->LocCode  . "'
									AND serialno = '" . $Item->BundleRef . "'";
							$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('Could not check if a batch or lot stock item already exists because');
							$DbgMsg =  _('The following SQL to test for an already existing controlled but not serialised stock item was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							$AlreadyExistsRow = DB_fetch_row($Result);
							if (trim($Item->BundleRef) != ''){
								if ($AlreadyExistsRow[0]>0){
									if ($POLine->Serialised == 1) {
										$SQL = "UPDATE stockserialitems SET quantity = '" . $Item->BundleQty . "'";
									} else {
										$SQL = "UPDATE stockserialitems SET quantity = quantity + '" . $Item->BundleQty . "'";
									}
									$SQL .= "WHERE stockid='" . $POLine->StockID . "'
											AND loccode = '" .  $POLine->LocCode  . "'
											AND serialno = '" . $Item->BundleRef . "'";
								} else {
									$SQL = "INSERT INTO stockserialitems (stockid,
																			loccode,
																			serialno,
																			qualitytext,
																			expirationdate,
																			quantity)
																		VALUES ('" . $POLine->StockID . "',
																			'" .  $POLine->LocCode  . "',
																			'" . $Item->BundleRef . "',
																			'',
																			'" . FormatDateForSQL($Item->ExpiryDate) . "',
																			'" . $Item->BundleQty . "')";
								}

								$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock item record could not be inserted because');
								$DbgMsg =  _('The following SQL to insert the serial stock item records was used');
								$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);

								// end of handle stockserialitems records //

							// now insert the serial stock movement //
							$SQL = "INSERT INTO stockserialmoves (stockmoveno,
																	stockid,
																	serialno,
																	moveqty)
															VALUES (
																'" . $StkMoveNo . "',
																'" . $POLine->StockID . "',
																'" . $Item->BundleRef . "',
																'" . $Item->BundleQty . "'
																)";
							$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The serial stock movement record could not be inserted because');
							$DbgMsg = _('The following SQL to insert the serial stock movement records was used');
							$Result = DB_query($SQL, $ErrMsg, $DbgMsg, true);
							if ($_SESSION['QualityLogSamples']==1) {
								CreateQASample($POLine->StockID,$Item->BundleRef, '', 'Created from Purchase Order', 0, 0,$db);
							}
						}//non blank BundleRef
					} //end foreach
				}//$POLine->Controlled 
						
			}
			
			//收货结束			
					
		} // end of the loop forearch
	      //  prnMsg($inrow.'*=='.$rowtol);
			if($inrow==(2*$rowtol)){
				$result = DB_Txn_Commit();
			
				//调试关闭
				unset($_SESSION['PO'.$identifier]->LineItems);
				unset($_SESSION['PO'.$identifier]);		
				
				prnMsg('易耗品发料单No' . $GRN . '录入成功！','success');
				if ( isset($_POST['Commit'])) {			
			
					echo '</br><a href="' . $RootPath . '/PDFIssueOrder.php?F=Y&D=' . $GRN . '">打印发料单</a> </br>';
				
					//echo '<meta http-equiv="refresh" content="0"; url="' . $RootPath . '/PDFOrder.php?id=33">';
				
				}
				//echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">返回简易收货</a></br>';
				
			}
		
	
   
} /*460row end of */
if(isset($_GET['Delete'])){

		$_SESSION['PO'.$identifier]->remove_item($_GET['Delete']);
	
}	
if (isset($_POST['NewItem'])){

	foreach ($_POST as $FormVariableName =>$Quantity) {
	
		if (mb_substr($FormVariableName, 0, 6)=='NewQty' AND filter_number_format($Quantity)!=0) { 
			//if the form variable represents a Qty to add to the order
			$n=mb_substr($FormVariableName, 6);
			$ItemCode = $_POST['StockID' .$n ];
			$LocCode = $_POST['LocCode' .$n ];
			$Controlled = $_POST['Controlled' . $n];
			$Decimalplaces=$_POST['Decimalplaces'.$n];
			$Price = $_POST['StandardPrice' . $n];
			$NewQty=$_POST['NewQty'.$n];
			$Description = $_POST['Description' . $n];
			$UOM=$_POST['Units'.$n];
		
			$sql="SELECT newqoh,
						 newamount
						FROM  stockmoves
						WHERE  stockid='" . $POLine->StockID . "'
						 AND 	stkmoveno IN(	SELECT  stkmoveno	FROM   stockmoves 	GROUP BY   stockid
							HAVING   MAX(stkmoveno))";
					$result = DB_query($sql);
					$row = DB_fetch_assoc($result);
					//存货价格  库存为<=0  使用StockMaster 标准价格
					if (empty($row['newqoh'])||$row['newqoh']<0){
						
						$InvPrice=$Price ;
				
					} else {									
						
						$InvPrice=abs(round(($row['newamount']/ $row['newqoh']),$_SESSION['StandardCostDecimalPlaces']));

					}
		
			$_SESSION['PO'.$identifier]->add_to_items($_SESSION['PO'.$identifier]->LineIndex+1,
														$ItemCode ,
														$Description,
														$LocCode,
														-$NewQty,														
														$InvPrice,
														$Price,
														$UOM,
														$Decimalplaces,
														$Controlled);
		
		} // end if the $_POST has NewQty i
	} // end loop around the $_POST array 
	$_SESSION['PO_ItemsResubmitForm' . $identifier]++; //change the $_SESSION VALUE

} /* end of if its a new item */

		//-----------------
		$rw=$_SESSION['PO'.$identifier]->LineIndex;
	   
		//读取缓存数据,显示明细
	  if ($rw>0){
		$sql = "SELECT departmentid,
						description,
						account,
						authoriser
				FROM departments
				ORDER BY description";

				$ErrMsg = _('There are no departments created');
				$Result = DB_query($sql,$ErrMsg);
		  
		 
				$timestamp=strtotime ("now");
		  $AfterDate=date('Y-m-01',$timestamp);
		  $_POST['DefaultIssueDate']=date('Y-m-d h:i:s',$timestamp);
		  $_POST['IssueDate']=date('Y-m-d',$timestamp);
		  echo '<table cellpadding="2" class="selection">
				  <tr>
				  	<th colspan="8"><p>易耗品发出明细</p></th>
				  </tr>
				  <tr>
				  <td>领用部门:</td>
				  <td><select name="Department">';			 
			  
				  while ($row = DB_fetch_array($Result)) {
					  if ( $row['departmentid'].'^'.$row['description'].'^'.$row['account']==$_POST['Department']) {
						  echo '<option selected="selected" value="';
					  } else {
						  echo '<option value="';
					  }
					  echo $row['departmentid'] .'^'.$row['description'].'^'.$row['account'] . '">' .$row['departmentid'].':'. htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	  
				  } //end while loop

				  echo '</select></td>
					  <td colspan="6"  class="centre" >发料日期:
					     <input type="hidden"  name="DefaultIssueDate" value="' . $_POST['DefaultIssueDate'] . '" />
						  <input type="date" alt="'. $_SESSION['DefaultDateFormat'] .'"  min="'.$_POST['IssueDate'].'" max="'.$_POST['IssueDate'].'"  maxlength="10" size="10"  name="IssueDate" value="' . $_POST['IssueDate'] . '" readonly="true" /></td>
				  </tr>';
				  /*onchange="return isDate(this, this.value, '."'".
						  $_SESSION['DefaultDateFormat']."'".')"*/
			  echo '<tr>
			      <th class="ascending">序号</th>
				  <th class="ascending">' . _('Item Code') . '</th>
				  <th class="ascending">' . _('Description') . '</th>';
			  echo'<th>单位</th>';
			  echo'<th class="ascending">' . _('Quantity') .'</th>';
			  echo'<th class="ascending">' . _('Price') .'</th>';
			  echo'<th class="ascending">' . _('Sub-Total') .'</th>
				   <th></th>
				  </tr>';
			  
		  $_SESSION['PO'.$identifier]->Total = 0;
		  $k = 0;  //row colour counter
		  $QtyTotal=0;
		
		  foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {
	  
			  if ($POLine->Deleted==False) {
				  $LineTotal =- $POLine->Quantity * $POLine->InvPrice;
				  $DisplayLineTotal = locale_number_format($LineTotal,$POLine->DecimalPlaces);
				  
				 $InvPrice =round($POLine->InvPrice ,4);		  
	  
				  if ($k==1){
					  echo '<tr class="EvenTableRows">';
					  $k=0;
				  } else {
					  echo '<tr class="OddTableRows">';
					  $k=1;
				  }
				  //locale_number_format(round($POLine->Quantity,$POLine->DecimalPlaces),$POLine->DecimalPlaces) 
				  echo'	<td>' . $POLine->LineNo .'-'.$POLine->DecimalPlaces .' ,'.$rw. '</td>
				  <td>' . $POLine->StockID  . '
				  <input type="hidden" name="StockID' . $POLine->LineNo . '" value="' . $POLine->StockID  . '"></td>
					  <td>' . stripslashes($POLine->Description) . '
					  <input type="hidden" name="Description' . $POLine->LineNo . '" value="' . stripslashes($POLine->Description) . '"></td>
				  
					  <td>' . $POLine->Units.'<input type="hidden" name="UOM' . $POLine->LineNo . '" value="' . $POLine->UOM . '"></td>
					  <td><input type="text" class="number" id="Quantity' . $POLine->LineNo .'"   name="Quantity' . $POLine->LineNo .'"  onChange="inQTY(this,'.$POLine->DecimalPlaces .' ,'.$rw.' )"  size="7" value="' .locale_number_format( -$POLine->Quantity,$POLine->DecimalPlaces). '" /></td>
					  <td><input type="text" class="number" id="Price' . $POLine->LineNo . '" name="Price' . $POLine->LineNo . '"  size="7" value="' .locale_number_format($InvPrice ,4).'" readonly="true" /></td>
					  <input type="hidden" id="edit' . $POLine->LineNo . '" name="edit' . $POLine->LineNo . '" value="0">';
	      	    echo '<td><input type="text" class="number" size="10" id="Amount' . $POLine->LineNo . '"  name="Amount' . $POLine->LineNo . '"  value="' . locale_number_format($LineTotal,2) .'"  readonly="true"  /></td>';
		        echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'&amp;Delete=' . $POLine->LineNo . '">' . _('Delete'). '</a></td>';
					  
				  echo '</tr>';
				
				  $_SESSION['PO'.$identifier]->Total += $LineTotal;
				  $QtyTotal+=-$POLine->Quantity;
			  }
		  }
	  
		  $DisplayTotal = locale_number_format($_SESSION['PO'.$identifier]->Total,$POLine->DecimalPlaces);
		  echo '<tr>
		     
		  		  <th colspan="4" >合计</th>			
				  <th><input type="text"  class="number"   id="QtyTotal" maxlength="10" size="7" value="'. locale_number_format($QtyTotal,$POLine->DecimalPlaces). '" readonly="readonly" /></th>
				  <th></td>
				  <th><input type="text"  class="number"  id= "AmountTotal" maxlength="20" size="10" value="'. $DisplayTotal. '" readonly="readonly" /></th>
			  
				  <th></th>
				  </tr>
				  </table>';
		  echo '<br />
				  <div class="centre">
				  <input type="submit" name="UpdateLines" value="' . _('Update Lines') . '" />';
		  echo '&nbsp;<input type="submit" name="Commit" value="' . _('Save') . '" />';
		 
		  echo'</div>';
		 
	  
	  } /* line items if there are any !! */
		//---------------------
		$sql="SELECT categoryid, 
					categorydescription
					FROM stockcategory
					INNER JOIN locationusers ON locationusers.loccode = categoryid AND locationusers.userid = '".$_SESSION['UserID']."' AND locationusers.canupd = 1
					WHERE stocktype = 'M'
					ORDER BY categorydescription";
		$ErrMsg = _('The supplier category details could not be retrieved because');
		$DbgMsg = _('The SQL used to retrieve the category details but failed was');
		$result1 = DB_query($sql,$ErrMsg,$DbgMsg);
		echo '<br /><table class="selection"><tr>';
		echo '<td>' . _('In Stock Category') . ':';
		echo '<select name="StockCat">';
			if (!isset($_POST['StockCat'])) {
				$_POST['StockCat'] ='';
			}
			if ($_POST['StockCat'] == 'All') {
				echo '<option selected="selected" value="All">' . _('All') . '</option>';
			} else {
				echo '<option value="All">' . _('All') . '</option>';
			}
		while ($myrow1 = DB_fetch_array($result1)) {
			if ($myrow1['categoryid'] == $_POST['StockCat']) {
				echo '<option selected="selected" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			} else {
				echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
			}
		}
		echo '</select></td>';
		echo '<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';
			if (isset($_POST['Keywords'])) {
				echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
			} else {
				echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
			}
		echo '</td>
			</tr>
			<tr>
				<td></td>
				<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>
				<td>';
		if (isset($_POST['StockCode'])) {
			echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
		} else {
			echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
		}
		echo '<tr>
			<td></td>
			<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Supplier Code') . '</b>:</td>
			<td>';
			if (isset($_POST['SupplierStockCode'])) {
				echo '<input type="text" name="SupplierStockCode" value="' . $_POST['SupplierStockCode'] . '" title="' . _('Enter text that you wish to search for in the supplier\'s item code') . '" size="15" maxlength="18" />';
			} else {
				echo '<input type="text" name="SupplierStockCode" title="' . _('Enter text that you wish to search for in the supplier\'s item code') . '" size="15" maxlength="18" />';
			}
		echo '</td></tr></table><br />';
		echo '<div class="centre">
				<input type="submit" name="Search" value="' . _('Search Now') . '" />	
			
		</div><br />';


if (isset($_POST['Search']) OR isset($_POST['Prev']) OR isset($_POST['Next'])){  /*ie seach for stock items */
     
		if ($_POST['Keywords'] AND $_POST['StockCode']) {
			prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
		}
	if ($_POST['Keywords']) {
		//insert wildcard characters in spaces
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

		if ($_POST['StockCat']=='All'){
			if ($_POST['SupplierItemsOnly']=='on'){
				$sql = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid,
								stockmaster.controlled,
								stockmaster.units,
								stockmaster.decimalplaces,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
						AND stockmaster.description " . LIKE . " '" . $SearchString ."'
						ORDER BY stockmaster.stockid ";
			} else { // not just supplier purchdata items

				$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.categoryid,
							stockmaster.controlled,
							stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
							stockmaster.units,
							stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.description " . LIKE . " '" . $SearchString ."'
					ORDER BY stockmaster.stockid ";
			}
		}else{ //for a specific stock category
			if ($_POST['SupplierItemsOnly']=='on'){
				$sql = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid,
								stockmaster.controlled,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
								stockmaster.units,
								stockmaster.decimalplaces
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						AND stockmaster.description " . LIKE . " '". $SearchString ."'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid ";
			} else {
				$sql = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid,
								stockmaster.controlled,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
								stockmaster.units,
								stockmaster.decimalplaces
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND stockmaster.discontinued<>1
						AND stockmaster.description " . LIKE . " '". $SearchString ."'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid ";
			}
		}

	}elseif ($_POST['StockCode']){

		$_POST['StockCode'] = '%' . $_POST['StockCode'] . '%';

		if ($_POST['StockCat']=='All'){
			if ($_POST['SupplierItemsOnly']=='on'){
				$sql = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid,	stockmaster.controlled,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
								stockmaster.units,
								stockmaster.decimalplaces
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
						ORDER BY stockmaster.stockid ";
			} else {
				$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.categoryid,	stockmaster.controlled,
							stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
							stockmaster.units,
							stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.stockid " . LIKE . " '" . $_POST['StockCode'] . "'
					ORDER BY stockmaster.stockid ";
			}
		} else { //for a specific stock category and LIKE stock code
			if ($_POST['SupplierItemsOnly']=='on'){
				$sql = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid,	stockmaster.controlled,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
								stockmaster.units,
								stockmaster.decimalplaces
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
						and stockmaster.discontinued<>1
						AND stockmaster.stockid " . LIKE  . " '" . $_POST['StockCode'] . "'
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid ";
			} else {
				$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.categoryid,	stockmaster.controlled,
							stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
							stockmaster.units,
							stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					and stockmaster.discontinued<>1
					AND stockmaster.stockid " . LIKE  . " '" . $_POST['StockCode'] . "'
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
			}
		}

	} else {
		if ($_POST['StockCat']=='All'){
			if (isset($_POST['SupplierItemsOnly'])){
				$sql = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid,	stockmaster.controlled,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
								stockmaster.units,
								stockmaster.decimalplaces
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						ORDER BY stockmaster.stockid ";
			} else {
				$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.categoryid,	stockmaster.controlled,
							stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
							stockmaster.units,
							stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					ORDER BY stockmaster.stockid ";
			}
		} else { // for a specific stock category
			if (isset($_POST['SupplierItemsOnly']) AND $_POST['SupplierItemsOnly']=='on'){
				$sql = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid,	
								stockmaster.controlled,
								stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
								stockmaster.units,
								stockmaster.decimalplaces
						FROM stockmaster INNER JOIN stockcategory
						ON stockmaster.categoryid=stockcategory.categoryid
						INNER JOIN purchdata
						ON stockmaster.stockid=purchdata.stockid
						WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
						AND stockmaster.mbflag<>'A'
						AND stockmaster.mbflag<>'K'
						AND stockmaster.mbflag<>'G'
						AND purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
						AND stockmaster.discontinued<>1
						AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
						ORDER BY stockmaster.stockid ";
			} else {
				$sql = "SELECT stockmaster.stockid,
							stockmaster.description,
							stockmaster.categoryid,	stockmaster.controlled,
							stockmaster.materialcost+stockmaster.labourcost+stockmaster.overheadcost AS price,
							stockmaster.units,
							stockmaster.decimalplaces
					FROM stockmaster INNER JOIN stockcategory
					ON stockmaster.categoryid=stockcategory.categoryid
					WHERE (stockmaster.mbflag<>'D' OR stockcategory.stocktype='L')
					AND stockmaster.mbflag<>'A'
					AND stockmaster.mbflag<>'K'
					AND stockmaster.mbflag<>'G'
					AND stockmaster.discontinued<>1
					AND stockmaster.categoryid='" . $_POST['StockCat'] . "'
					ORDER BY stockmaster.stockid ";
			}
		}
	}

				$SQLCount = substr($sql,strpos($sql,   "FROM"));
				$SQLCount = substr($SQLCount,0, strpos($SQLCount,   "ORDER"));
				$SQLCount = 'SELECT COUNT(*) '.$SQLCount;
				$ErrMsg = _('Failed to retrieve result count');
				$DbgMsg = _('The SQL failed is ');
				$SearchResult = DB_query($SQLCount,$ErrMsg,$DbgMsg);
				$myrow=DB_fetch_array($SearchResult);
				DB_free_result($SearchResult);
				unset($SearchResult);
				$ListCount = $myrow[0];
				$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax'])-1;
		if ($ListPageMax < 0) {
			$ListPageMax = 0;
		}
		if (isset($_POST['Next'])) {
			$Offset = $_POST['currpage']+1;
		}
		if (isset($_POST['Prev'])) {
			$Offset = $_POST['currpage']-1;
		}
		if (!isset($Offset)) {
			$Offset = 0;
		}
		if($Offset < 0){
			$Offset = 0;
		}
		if($Offset > $ListPageMax) {
			$Offset = $ListPageMax;
		}

		$sql = $sql . "LIMIT " . $_SESSION['DisplayRecordsMax']." OFFSET " . strval($_SESSION['DisplayRecordsMax']*$Offset);



		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL statement that failed was');
		$SearchResult = DB_query($sql,$ErrMsg,$DbgMsg);
		//prnMsg($sql);

		if (DB_num_rows($SearchResult)==0 AND $debug==1){
			prnMsg( _('There are no products to display matching the criteria provided'),'warn');
		}
		if (DB_num_rows($SearchResult)==1){

			$myrow=DB_fetch_array($SearchResult);
			$_GET['NewItem'] = $myrow['stockid'];
			DB_data_seek($SearchResult,0);
		}

} 

	//物料	
if (isset($SearchResult)) {
	$PageBar = '<tr><td><input type="hidden" name="currpage" value="'.$Offset.'">';
	if($Offset>0)
		$PageBar .= '<input type="submit" name="Prev" value="'._('Prev').'" />';
	else
		$PageBar .= '<input type="submit" name="Prev" value="'._('Prev').'" disabled="disabled"/>';
	$PageBar .= '</td><td style="text-align:center" colspan="4"><input type="submit" value="增至发货单行" name="NewItem"/></td><td>';
	if($Offset<$ListPageMax)
		$PageBar .= '<input type="submit" name="Next" value="'._('Next').'" />';
	else
		$PageBar .= '<input type="submit" name="Next" value="'._('Next').'" disabled="disabled"/>';
	$PageBar .= '</td></tr>';
	echo '<table cellpadding="1" class="selection">';
	echo $PageBar;
	$TableHeader = '<tr>
						<th class="ascending">' . _('Code')  . '1937</th>
						<th class="ascending">' . _('Description') . '</th>
						<th>图像</th>
						<th>' . _('Units') . '</th>
						<th>单价</th>
						<th>库存数量</th>					
					
					</tr>';
	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter

	while ($myrow=DB_fetch_array($SearchResult)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

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
			$ImageSource = '<img src="' . $imagefile . '" height="100" width="100" />';
		} else {
			$ImageSource = _('No Image');
		}

		/*Get conversion factor and supplier units if any */
		$sql =  "SELECT purchdata.conversionfactor,
						purchdata.suppliersuom
					FROM purchdata
					WHERE purchdata.supplierno='" . $_SESSION['PO'.$identifier]->SupplierID . "'
					AND purchdata.stockid='" . $myrow['stockid'] . "'";
		$ErrMsg = _('Could not retrieve the purchasing data for the item');
		$PurchDataResult = DB_query($sql,$ErrMsg);

		if (DB_num_rows($PurchDataResult)>0) {
			$PurchDataRow = DB_fetch_array($PurchDataResult);
			$OrderUnits=$PurchDataRow['suppliersuom'];
			$ConversionFactor = locale_number_format($PurchDataRow['conversionfactor'],'Variable');
		} else {
			$OrderUnits=$myrow['units'];
			$ConversionFactor =1;
		}
		echo '<td>' . $myrow['stockid']  . '</td>
			<input type="hidden" name="StockID' . $j .'" . value="' . $myrow['stockid'] . '" />
			<td>' . $myrow['description']  . '</td>
			<input type="hidden" name="Description' . $j .'" . value="' . $myrow['description'] . '" />
			<td>' . $ImageSource . '</td>
			<td>' . $myrow['units']  . '</td>
			<input type="hidden" name="Units' . $j .'" . value="' . $myrow['units'] . '" />
			<input type="hidden"  value="'. $myrow['price'] .'" name="StandardPrice' . $j . '" />
			<input type="hidden" value="'. $myrow['categoryid'] .'" name="LocCode' . $j . '" /></td>
			<input type="hidden" value="'. $myrow['decimalplaces'] .'" name="Decimalplaces' . $j . '" /></td>
			<input type="hidden" value="'. $myrow['controlled'] .'" name="Controlled' . $j . '" /></td>
			
			<td>' . $myrow['price']  . '</td>
			<td><input class="number" type="text" size="6" value="0" name="NewQty' . $j . '" /></td>
			
			</tr>';
		$j++;
		$PartsDisplayed++;
	#end of page full new headings if
	}

	echo $PageBar;
	#end of while loop
	echo '</table>';
	echo '<input type="hidden" name="PO_ItemsResubmitFormValue" value="' . $_SESSION['PO_ItemsResubmitForm' . $identifier] . '" />';
	echo '<a name="end"></a><br /><div class="centre"><input type="submit" name="NewItem" value="' . _('Order some') . '" /></div>';
}#1616end if SearchResults to show
echo '</div>
      </form>';

include('includes/footer.php');
?>
