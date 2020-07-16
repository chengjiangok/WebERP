
<?php
/*
 * @Author: ChengJiang 
 * @Date: 2018-09-21 06:12:00 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-14 17:40:55
 */


/* Session started in header.php for password checking and authorisation level check */
include('includes/session.php');

$Title ='月末成本结账';
/* webERP manual links before header.php */
$ViewTopic= 'AccountsPayable';
$BookMark = 'SupplierInvoice';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
	function STLType(I){	
		var paramName="STL"
		var oUrl = this.location.href.toString();
		var re=eval("/("+ paramName+"=)([^&]*)/gi");
		var nUrl = oUrl.replace(re,paramName+"="+I.value);
		this.location = nUrl;
	　　window.location.href=nUrl;
		//document.getElementById("SettleType").value=I.value;
	}	
</script>';
if (!isset($_POST['SettleType'])) {
	$_POST['SettleType']=0;
}

if (isset($_GET['STL'])) {
	$_POST['SettleType']=$_GET['STL'];
}
if (!isset($_POST['query'])){
	$_POST['query']=1;
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if (isset($_POST['SettleType'])){
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?STL='.$_POST['SettleType'].'" method="post">';
}else{
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
}
   echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	 	  <input type="hidden" name="workcentre" value="' . $_POST['workcentre'] . '" />';
	

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . '</p>';
	$sql="SELECT confvalue FROM myconfig WHERE confname='AccountStart'";
	$result = DB_query($sql);
	$row=DB_fetch_assoc($result);
    $TagStart=json_decode($row['confvalue'],true);
	//var_dump($TagStart);
	
	//最末单据录入日期
	$sql="SELECT trandate FROM stockmoves ORDER BY trandate DESC LIMIT 1";
	$result = DB_query($sql);
	$stockdate=DB_fetch_assoc($result);
	//最末凭证录入period
	$sql="SELECT DISTINCT periodno FROM gltrans WHERE periodno>=1 AND LEFT(account,4) IN ('1401','1403','1405','1406','5001','6001','6401') ORDER BY periodno DESC  LIMIT 1";
	$result = DB_query($sql);
	$gldate=DB_fetch_assoc($result);
	$settle=array("0"=>"生产成本","1"=>"采购成本","2"=>"销售成本");
	echo'<table cellpadding="3" class="selection">';	
	echo '<tr>
		<td>结账项目</td>
		<td  colspan="2">
		   <select name="SettleType" id="SettleType" size="1" onchange="STLType(this)">';
	
    foreach ($settle as $key=>$val){
	
		if(isset($_POST['SettleType']) AND $key==$_POST['SettleType'] ){
			echo '<option selected="selected" value="';			
		} else {			
			echo '<option value="';
		}
		echo $key . '">' .$val  . '</option>'; 
	}
		echo'</select>
			 </td>
			 </tr>';
	$sql="SELECT code, tag,description,tagdescription ,'1' confvalue FROM workcentres a LEFT JOIN unittag b ON a.tag=b.tagID";
	$result = DB_query($sql);
echo '<tr>
	<td>核算项目</td>
	<td  colspan="2"><select name="workcentre" size="1"  >';
	while ($myrow=DB_fetch_array($result,$db)){
		$conf=json_decode($myrow['confvalue'],true);
		if (isset($conf[substr($_SESSION['lastdate'],4)])){
			$wkconf=$conf[substr($_SESSION['lastdate'],4)];
		}else{
			$wkconf=1;
		}
		if(isset($_POST['workcentre']) AND $myrow['code']==$_POST['workcentre'] ){
			echo '<option selected="selected" value="';			
		} else {			
			echo '<option value="';
		}
		echo $myrow['code']. '^'.$myrow['tag']. '^'.$wkconf.'">' .$myrow['description'].'_'.$myrow['tagdescription']  . '</option>'; 
	}
		echo'</select>
			</td></tr>';
		echo '<tr>
				<td>查询格式</td>
				<td colspan="2">				
					<input type="radio" name="query" value="2"   '.($_POST['query']==2 ? 'checked':"").'  >'._('Total').'
					<input type="radio" name="query" value="1"   '.($_POST['query']==1 ? 'checked':"").' >'._('Detail').' 
				</td>
			</tr>';
	$sql="SELECT periodno, lastdate_in_period FROM periods WHERE periodno>=( SELECT confvalue FROM myconfig WHERE confname='printprd') AND periodno>=".$gldate['periodno'] ." AND periodno<=". $_SESSION['period'];
	$result = DB_query($sql);
	$row=DB_num_rows($result);
	echo '<tr>
			<td width="100">选择期间 </td>';
			if ($row==0){
				echo'<td width="100"> </td>';
			}else{
				echo'<td><select name="selectprd" size="1" >';
				//.substr($_SESSION['lastdate'],0,7).
				while ($myrow=DB_fetch_array($result,$db)){
					if(isset($_POST['selectprd']) AND $myrow['periodno']==$_POST['selectprd'] ){
						echo '<option selected="selected" value="';			
					} else {			
						echo '<option value="';
					}
					echo $myrow['periodno'] . '">' .$myrow['lastdate_in_period']  . '</option>'; 
				}
			}

			echo'  </td>
		</tr>'; 

echo'</table>';
//prnMsg($sql);
	if ($row==0){
		prnMsg("你选择的会计期间不需要成本结转",'info');
	}
	prnMsg("仓储物料最末单据日期:".$stockdate['trandate'].'<br>'.'最末录入凭证期间:'.$gldate['periodno'],'info');
	echo '<br />
		<div class="centre">
			<input type="submit" name="SettleGo" value="结账查询" />			
			<input type="submit" name="crtExcel" value="导出Excel" />
		
		</div>';
    
if (isset($_POST['SettleGo'])) {		
		
	if (isset($_POST['SettleType'])&& $_POST['SettleType']==0){
		prnMsg("生产成本");
	
					
		//39易耗品发料,28生产发料
		//取得部门科目
		$SQL="SELECT departmentid,
					description,
					account,
					authoriser
			FROM departments
			ORDER BY description";
		$result=DB_query($SQL);
		$deptact=array();
		$stockarr=array();
		while($row=DB_fetch_array($result)){
			$deptact[$row['departmentid']]=$row['account'];
		}
        $SQL="SELECT  c.stockact, c.wipact,  type, sum(qty*standardcost) total 
				FROM stockmoves a 
				LEFT JOIN stockmaster b ON a.stockid=b.stockid
				LEFT JOIN stockcategory c ON c.categoryid=b.categoryid 
				WHERE type = 39 OR type = 28  AND stled=0  group by  c.stockact, c.wipact,  type";
		$result=DB_query($SQL);
		while($row=DB_fetch_array($result)){
			$stockarr[$row['stockact']][]=array($row['type'],$row['wipact'],$row['total']);
		}
		//var_dump($stockarr);
		$SQL="SELECT `accountcode`, `accountname`, `group_`, `currcode`, `cashflowsactivity`, `tag`, `crtdate`, `low`, `used` FROM `chartmaster` WHERE accountcode LIKE '140__%' OR accountcode LIKE '6602_%' OR accountcode LIKE '6401_%'OR accountcode LIKE '6402_%' OR accountcode LIKE '5001_%' OR accountcode LIKE '5301_%'";
		$result=DB_query($SQL);
		while($row=DB_fetch_array($result)){
			$AccountNameArr[$row['accountcode']]=$row['accountname'];
		}
		$RowIndex=1;
		if (count($stockarr)>0){
			echo '<table width="90%" cellpadding="4"  class="selection">
			<tr>
				<th >序号</th>				
				<th >存货科目</th>
				<th >结转科目</th>			
				<th >金额</th>		
				<th >备注</th>
				<th ></th>
				
			</tr>';
			$sumtotal=0;
			$Index=0;
			foreach($stockarr as $key=>$val){
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				}else{
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$rowspan=count($val);
				$td=0;
				$total=0;
				echo'<td rowspan="'.$rowspan.'">'.$RowIndex.'</td>				
					 <td rowspan="'.$rowspan.'">'.$key.'-'.$AccountNameArr[$key].'</td>';
					 if (count($val)>0){
						 $i=0;
						echo '<td>'. $val[$i][1].'-'.$AccountNameArr[$val[$i][1]].'
						      <input type="hidden"  name="'.$val[$i][1]."StockID".$RowIndex.'-'.$Index.'" value=""   ></td>
							  <td class="number">'.locale_number_format(round($val[$i][2],2),2).'</td>
							  <td><input type="text"   name="Remark'.$RowIndex."-".$Index.'"  value="'.$RowIndex.'-'.$Index.'"    ></td>
							  <td rowspan="'.$rowspan.'">
							     <input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>
							  </tr>';
							  $total=$val[$i][2];
							  $sumtotal=$total;
							  $Index++;

                             
					 }
					
					if (count($val)>1){						
						for ($i=1;$i<count($val);$i++){
							echo'<tr>
								<td>'. $val[$i][1].'-'.$AccountNameArr[$val[$i][1]].'
									<input type="hidden"  name="'.$val[$i][1]."StockID".$RowIndex.'-'.$Index.'" value=""   ></td>
								<td class="number">'.locale_number_format(round($val[$i][2],2),2).'</td>
								<td>
								    <input type="text" name="Remark'.$RowIndex."-".$Index.'"  value= "'.$RowIndex.'-'.$Index.'" ></td>
								</tr>';
								$total+=$val[$i][2];
								$sumtotal+=$total;	
								$Index++;

						}
						echo '<tr>
								<td></td>
								<td></td>
								<td>小计</td>
								<td class="number">'.locale_number_format(round($total,2),2).'</td>
								<td></td>
								<td > </td>
								</tr>';	

				
					}
					
					$RowIndex++;
			}
			echo '<tr>
								<td></td>
								<td></td>
								<td>合计</td>
								<td class="number">'.locale_number_format(round($sumtotal,2),2).'</td>
								<td></td>
								<td > </td>
								</tr>';	
			echo'</table>';	
		}
		$SQL="SELECT stkmoveno,					
					connectid,
					a.stockid,
					b.description,
					c.stockact,
					c.wipact,
					units,
					type,
					transno,
					loccode,
					accountdate,
					trandate,
					userid,
					debtorno,
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
				FROM  stockmoves a 
				LEFT JOIN stockmaster b ON a.stockid=b.stockid
				LEFT JOIN stockcategory c ON c.categoryid=b.categoryid
				WHERE type	= 39 OR type = 28";
		$result=DB_query($SQL);
		echo '	<input type="submit" name="SaveTrans" value="凭证确认" />';
		echo '<table width="90%" cellpadding="4"  class="selection">
			<tr>
				<th >序号</th>
				<th >日期</th>
				<th >工作单/项目</th>
				<th >物料编码</th>
				<th >存货科目</th>
				<th >结转科目</th>
				<th >单位</th>
				<th >数量</th>
				<th >单价</th>
				<th >金额</th>		
				<th >备注</th>
				<th ></th>
				
			</tr>';
					$RowIndex=1;
					$k=0;				
					$Total=0;					
					$TaxTotal=0;
					$TotalAll=0;
					$TaxTotalAll=0;
			while($row=DB_fetch_array($result)){
			
			    $qty=-$row['qty'];
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				}else{
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				echo'<td>'.$RowIndex.'</td>
					 <td>'.$row['trandate'].'</td>';				
				echo'<td>'.$row['transno'].'</td>
					 <td>'.$row['stockid'].'</td>
					 <td>'.$row['stockact'].'</td>
					 <td>'.$row['wipact'].'</td>
					 <td>'.$row['units'].'</td>';			
				echo'<td class="number">'.locale_number_format(round($qty,2),2).'</td>
					 <td class="number">'.locale_number_format(round($row['standardcost'],2),2).'</td>
					 <td class="number">'.locale_number_format(round($qty*$row['standardcost'],2),2).'</td>
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
	}elseif (isset($_POST['SettleType'])&& $_POST['SettleType']==1){
		prnMsg( $_POST['SettleType']."采购成本");
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
								stockmaster.units,
								purchorderdetails.glcode,
								purchorderdetails.shiptref,
								purchorderdetails.jobref,
								purchorderdetails.podetailitem,
								purchorderdetails.assetid,
								stockmaster.decimalplaces
						FROM grns INNER JOIN purchorderdetails
							ON  grns.podetailitem=purchorderdetails.podetailitem
						LEFT JOIN stockmaster ON grns.itemcode=stockmaster.stockid
						WHERE grns.qtyrecd - grns.quantityinv > 0
						ORDER BY grns.grnno";
						// grns.supplierid ='" . $_SESSION['SuppTrans']->SupplierID . "'
						// purchorderdetails.orderno = '" . intval($_GET['ReceivePO']) . "'
						
				$result = DB_query($SQL);
		echo '<table width="90%" cellpadding="4"  class="selection">
				<tr>
				    <th >序号</th>
					<th >供应商名称</th>
					<th >对账单号</th>
					<th >合同号</th>
					<th >编码/物料名</th>
					<th >单位</th>
					<th >数量</th>
					<th >价格</th>
					<th >金额</th>
					<th >税率</th>
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
				if ($k==1){
					echo '<tr class="EvenTableRows">';
						
					$k=0;
				}else {
					echo '<tr class="OddTableRows">';
						
					$k=1;
				}
			
			$Total+=round($row['total'],2);
			$TaxTotal+=round($row['taxamount'],2);
			$TotalAll+=round($row['total'],2);
			$TaxTotalAll+=round($row['taxamount'],2);
			$orderno=$row['orderno'];
			echo '	<td>'.$RowIndex.'</a></td>
			        <td></td>';
			echo '<td>'.$row['supaccno'].'</td>
				  <td>'.$row['orderno'].'</td>
				  <td>'.$row['itemcode'].'/'.$row['itemcodedescription'].'</td>
				  <td>'.$row['units'].'</td>  ';			
			echo'
			<td class="number">'.locale_number_format(round($row['qtyrecd'],2),2).'</td>
			<td class="number">'.locale_number_format(round($row['qtyrecd'],2),2).'</td>
				<td class="number">'.locale_number_format(round($row['unitprice'],2),2).'</td>
				<td class="number">'.locale_number_format(round($taxtotal,2),2).'</td>
				<td ></td>
				<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
			</tr>';
				
				$RowIndex++;
			
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

	}elseif (isset($_POST['SettleType'])&& $_POST['SettleType']==2){
		prnMsg( $_POST['SettleType']."销售成本");
	
		echo '<table width="90%" cellpadding="4"  class="selection">
			<tr>
				<th >序号</th>
				<th >日期</th>
				<th >工作单/项目</th>
				<th >物料编码</th>
				<th >单位</th>
				<th >数量</th>
				<th >单价</th>
				<th >金额</th>		
				<th >备注</th>
				<th ></th>
				
			</tr>';

	}		
}elseif(isset($_POST['SaveTrans'])){

	$str='';
	foreach ($_POST as $key => $value) {
        // $str.=$key.'<br>';
		if (mb_strstr($key,'Remark')) {
			$IDX=mb_substr($key, 6);
			
		
				$Value=$_POST['Remark'.$IDX];
				$str.=$Value.'<br>';
		}
	}
	prnMsg('凭证保存'.$str);
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
include('includes/footer.php');
?>
