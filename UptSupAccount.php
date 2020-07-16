
<?php


include('includes/session.php');
$Title ='收货对账单';
$ViewTopic = 'GeneralLedger';
$BookMark = 'Journals';
echo'<script type="text/javascript">
function ComboToInput(c, i,u) {
	i.value=c.value.split("^")[0];
	u.value=c.value.split("^")[1];
	document.getElementById("accname").value=c.value.split("^")[2];
	document.getElementById("currate").value=getrate(c.value.split("^")[1]);			
}

function inPrice(p,d,r){		
	var  n=p.name.substring(7);	
	var vlqty = document.getElementById("SuppQty"+n);
	
	var total=(p.value*vlqty.value).toFixed(2);
	if (vlqty.value!=""){
		//数量不为空
		total=(p.value*vlqty.value).toFixed(2);
		document.getElementById("SupTotal"+n).value=total;
	}


	var obj = document.getElementById("taxauth"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	
	var taxtol=(val*total).toFixed(2); 
	document.getElementById("SupTax"+n).value=taxtol;
	document.getElementById("SupTaxTol"+n).value=(Number(taxtol)+Number(total)).toFixed(2); 
	var to=0;
	var tax=0;
	var totax=0;
	for(var i=1; i<=r; i++){
		to=parseFloat(to)+parseFloat(document.getElementById("SupTotal"+i).value);
	
		tax=parseFloat(tax)+parseFloat(document.getElementById("SupTax"+i).value);
		totax=parseFloat(totax)+parseFloat(document.getElementById("SupTaxTol"+i).value);
	}


	document.getElementById("displaytotal").value =to.toFixed(2);
	document.getElementById("taxtotal").value =tax.toFixed(2);
	document.getElementById("alltotal").value =totax.toFixed(2);
	document.getElementById("edit").value=1;
		
	
}
function inQTY(p,d,r){
	var  n=p.name.substring(7);	
	var vl = document.getElementById("SuPrice"+n);
	
	var total=(p.value*vl.value).toFixed(2);
	if (vl.value!=""){
		//数量不为空
		total=(p.value*vl.value).toFixed(2);
		document.getElementById("SupTotal"+n).value=total;
	}


	var obj = document.getElementById("taxauth"+n); 
	var index = obj.selectedIndex; // 选中索引			
	var val = obj.options[index].value.split("^")[1]; // 选中值
	
	var taxtol=(val*total).toFixed(2); 
	document.getElementById("SupTax"+n).value=taxtol;
	document.getElementById("SupTaxTol"+n).value=(Number(taxtol)+Number(total)).toFixed(2); 
	var to=0;
	var tax=0;
	var totax=0;
	for(var i=1; i<=r; i++){
		to=parseFloat(to)+parseFloat(document.getElementById("SupTotal"+i).value);
	
		tax=parseFloat(tax)+parseFloat(document.getElementById("SupTax"+i).value);
		totax=parseFloat(totax)+parseFloat(document.getElementById("SupTaxTol"+i).value);
	}


	document.getElementById("displaytotal").value =to.toFixed(2);
	document.getElementById("taxtotal").value =tax.toFixed(2);
	document.getElementById("alltotal").value =totax.toFixed(2);
	document.getElementById("edit").value=1;

}
function QTY(q,o){
	var nn=o.name;
	var obj = document.getElementById(nn); 
	var index = obj.selectedIndex; // 选中索引
	var text = obj.options[index].text; // 选中文本		
	var value = obj.options[index].value; // 选中值

	alert(value);
//	alert(text);
}
function inSupTax(q,o){

	alert(q.value);

}
function inSupTotal(q,o){
	alert(q.value);
}

function refresh() {  
	window.location.reload();
}  
	
</script>';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
	if (isset($_GET['saot'])){
		$ntpa=explode('^',url_decode($_GET['saot']));
		//prnMsg(url_decode($_GET['ntpa']));
		$tag=$ntpa[10];
	}else{
		unset($ntpa);
		prnMsg('页面引导错误！','info');
		//echo "<script>window.close();</script>";
		//include('includes/footer.php');
		exit;
	}
   $checkflg=0;//检查是否有全部科目有未知科目=1
	$flag='?';
if (isset($_GET['ty'])){
  $flag.='ty='.$_GET['ty'].'&';
  }
 if (isset($_GET['ntpa'])){
  $flag.='ntpa='.$_GET['ntpa'].'&';
    }   
 if (strlen($flag)>2){
   	$flag= substr($flag,0,-1);   	
 }
$_SESSION['Journalstr']=$flag;
//读取外币汇率

echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title .':['. $_SESSION['PO' . $identifier]->SupplierID.']'.$_SESSION['PO' . $identifier]->SupplierName.'</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" id="choosesupplier">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table cellpadding="2" class="selection">
			<tr>
			<th colspan="12"><h3>收货明细</h3></th>
		    </tr>
			<tr>
			<td colspan="5">收货单位:</td><td colspan="7"></td>
		    </tr>
			<tr>
			<th>序号</th>
			<th>' . _('Date') . '</th>
				<th>' . _('Item Code') . '</th>
				<th>' . _('Description') . '</th>					
				<th>' . _('Units') . '</th>
				<th>' . _('Price') . '</th>
				<th>' . _('Quantity') . '</th>
				<th>不含税金额</th>
				<th>税率</th>
				<th>税金</th>
				<th>' . _('Total Value') . '</th>
				<th></th>		
			</tr>';
	$_SESSION['PO'.$identifier]->Total = 0;
	$k = 0;  //row colour counter
	$TaxTotal=0;
	foreach ($_SESSION['PO'.$identifier]->LineItems as $POLine) {

		if ($POLine->Deleted==False) {
			$LineTotal = $POLine->Quantity * $POLine->Price;
			$DisplayLineTotal = locale_number_format($LineTotal,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
			/*
			if ($POLine->Price > 1) {
				$DisplayPrice = locale_number_format($POLine->Price,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
				$SuppPrice = locale_number_format(round(($POLine->Price *$POLine->ConversionFactor),$_SESSION['PO'.$identifier]->CurrDecimalPlaces),$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
			} else {
				$DisplayPrice = locale_number_format($POLine->Price,4);
				$SuppPrice = locale_number_format(round(($POLine->Price *$POLine->ConversionFactor),4),4);
			}*/
			$SuppPrice =round($POLine->Price ,4);

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			//locale_number_format(round($POLine->Quantity,$POLine->DecimalPlaces),$POLine->DecimalPlaces) 
			echo'<td>' . $POLine->StockID  . '
			<input type="hidden" name="StockID' . $POLine->LineNo . '" value="' . $POLine->StockID  . '"></td>
				<td>' . stripslashes($POLine->ItemDescription) . '
				<input type="hidden" name="ItemDescription' . $POLine->LineNo . '" value="' . stripslashes($POLine->ItemDescription) . '"></td>
			
				<td>' . $POLine->Units.'
				<input type="hidden" name="UOM' . $POLine->LineNo . '" value="' . $POLine->UOM . '"></td>
				<td><input type="hidden" id="edit' . $POLine->LineNo . '" name="edit' . $POLine->LineNo . '" value="0">
					<input type="text" class="number" id="SuPrice' . $POLine->LineNo . '" name="SuPrice' . $POLine->LineNo . '"  onChange="inPrice(this,'.$POLine->DecimalPlaces .','.$rw.' )" size="7" value="' . $SuppPrice .'" /></td>
			
				<td><input type="text" class="number" id="SuppQty' . $POLine->LineNo .'"   name="SuppQty' . $POLine->LineNo .'"  onChange="inQTY(this,'.$POLine->DecimalPlaces .' ,'.$rw.' )"  size="7" value="' . $POLine->Quantity. '" /></td>
				<td><input type="text" class="number"  size="10" id="SupTotal' . $POLine->LineNo .'"  name="SupTotal' . $POLine->LineNo .'" value="' .$DisplayLineTotal. '" /></td>
				<td><select name="taxauth' . $POLine->LineNo .'"  id="taxauth' . $POLine->LineNo .'">';
				$ky=-1;
				foreach ($taxauth as $key=>$val){
						if ($ky==-1){
							$ky=$key;
						}			
					
						echo '<option value="' . $key.'^'.$val[1].'">' . $val[0] . '</option>';
					
				}
				echo '</select></td>';
				$SupTax=round($LineTotal*$taxauth[$ky][1] ,2);
				$SupRate=$taxauth[$ky][1];
				$TaxTotal+=$SupTax;
				$SupTaxTotal=round($POLine->Quantity* $POLine->Price*(1+$taxauth[$ky][1]) ,2);
				
					echo '<td><input type="text" class="number" size="7" id="SupTax' . $POLine->LineNo . '"  name="SupTax' . $POLine->LineNo . '"  onChange="inSupTax(this,SuTotal' . $POLine->LineNo .' )"  value="' . $SupTax  .'" /></td>
					      <td><input type="text" class="number" size="10" id="SupTaxTol' . $POLine->LineNo . '"  name="SupTaxTol' . $POLine->LineNo . '"  onChange="inSupTaxTol(this,SuTotal' . $POLine->LineNo .' )"  value="' . $SupTaxTotal .'" /></td>';
				
				if ($POLine->QtyReceived !=0 AND $POLine->Completed!=1){
					echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'&amp;Complete=' . $POLine->LineNo . '">' . _('Complete') . '</a></td>';
				} elseif ($POLine->QtyReceived ==0) {
					echo '<td><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier .'&amp;Delete=' . $POLine->LineNo . '">' . _('Delete'). '</a></td>';
				}
			echo '</tr>';
			//prnMsg($LineTotal.'='.$SupRate);
			$_SESSION['PO'.$identifier]->update_item(	$POLine->LineNo,
														$_POST['SuppQty'.$POLine->LineNo],
														$_POST['SuPrice'.$POLine->LineNo],
														$SupRate,
														$SupTax);
			
			
			$_SESSION['PO'.$identifier]->Total += $LineTotal;
		}
	}

	$DisplayTotal = locale_number_format($_SESSION['PO'.$identifier]->Total,$_SESSION['PO'.$identifier]->CurrDecimalPlaces);
	echo '<tr><td colspan="5" class="number">' . _('TOTAL')  . '</td>
			<td><input type="text"  class="number" id="displaytotal" maxlength="20" size="10" value="' . $DisplayTotal . '"  readonly="readonly" /></td>
			<td></td>
			<td><input type="text"  class="number"   id="taxtotal" maxlength="10" size="7" value="'. locale_number_format($TaxTotal,$_SESSION['PO'.$identifier]->CurrDecimalPlaces). '" readonly="readonly" /></td>
			<td><input type="text"  class="number"  id= "alltotal" maxlength="20" size="10" value="'. locale_number_format($TaxTotal+$_SESSION['PO'.$identifier]->Total,$_SESSION['PO'.$identifier]->CurrDecimalPlaces). '" readonly="readonly" /></td>
		
			<td></td>
			</tr>
			</table>';
	echo '<br />
			<div class="centre">';
	echo '&nbsp;<input type="submit" name="Commit" value="' . _('Process Order') . '" />
			</div>';

 /*Only display the order line items if there are any !! */


echo '</div>
	</form>';
include('includes/footer.php');
  
?>
