
<?php

/* $Id: SupplierAccounts.php  $ */
/*
 * @Author: ChengJiang 
 * @Date: 2018-09-14 21:04:25 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-08 20:52:45
 */

include('includes/DefineSuppTransClass.php');
include('includes/DefinePOClass.php'); //needed for auto receiving code
include('includes/session.php');

$Title ='供应商对账';
/* webERP manual links before header.php */
$ViewTopic= 'AccountsPayable';
$BookMark = 'SupplierInvoice';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['selectprd'])){
	$_POST['selectprd']=$_SESSION['period'].'^'.$_SESSION['lastdate'];
}
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . $PrmString.'" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		  <input type="hidden" name="Select" value="' . $_POST['sELECT'] . '" />';
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';

    /*
	$sql="SELECT confvalue FROM myconfig WHERE confname='AccountStart'";
	$result = DB_query($sql);
	$row=DB_fetch_assoc($result);
    $TagStart=json_decode($row['confvalue'],true);*/
	//var_dump($TagStart);
	
	//最末单据录入日期
	$sql="SELECT trandate FROM stockmoves ORDER BY trandate DESC LIMIT 1";
	$result = DB_query($sql);
	$stockdate=DB_fetch_assoc($result);
	$msg.="仓储物料最末单据日期:".$stockdate['trandate'].'<br>';
	//读取未核对材料的凭证
	$sql="SELECT  `account`,count(*) cut, SUM(toamount(`amount`,'".$_SESSION['period']."','".$_SESSION['period']."','".$_SESSION['period']."',1,flg)) debit FROM `gltrans` WHERE periodno='".$_SESSION['period']."' AND account IN (SELECT   `stockact` FROM `stockcategory` WHERE stocktype='B') AND jobref=0";
	$result = DB_query($sql);
	
	while($row=DB_fetch_array($result)){
		$msg.="本期未核对存货科目凭证".$row['account'].' '.$row['cut'].'笔'. ' '.$row['debit'] .'元<br/>';
	}
	//读取发票
	$sql=" SELECT sum(`amount`) amount, COUNT(*) cut,SUM(CASE WHEN transno=0 THEN 1 ELSE 0 END) glcut,SUM(CASE WHEN stled=0 THEN 1 ELSE 0 END) loccut  FROM `invoicetrans` WHERE `invtype`=0 AND `tag`='3' AND stled=0 AND date_format(`invdate`,'%Y%m')='".date('Ym',strtotime($_SESSION['lastdate']))."'";
	$result = DB_query($sql);
    // prnMsg($sql);
	while($row=DB_fetch_array($result)){
		$msg.="本期未核对进项专票 ".$row['cut'].'笔'. ' '.$row['amount'] .'元 <br/>其中未制作凭证'.$row['glcut'].'笔 ,未核对库存'.$row['loccut'].'笔 <br/>';
	}
	
	$SelectPrd=1+$_SESSION['CompanyRecord'][1]['lastsettleperiod'];	
	//$_POST['SelectDate']= PeriodGetDate((1+$_SESSION['CompanyRecord'][$_SESSION['Tag']]['lastsettleperiod']));


	echo'<table cellpadding="3" class="selection">';	
	echo '<tr>
			<td width="100">选择期间 </td>';	
				echo'<td  colspan="2">';
				//echo substr($_POST['SelectDate'],0,7);
				SelectPeriod($_POST["ERPPrd"],$_SESSION['startperiod']);
			
			echo'</td>
				  </tr>';
		echo '<tr>
				<td>查询格式</td>
				<td colspan="2">				
					<input type="radio" name="query" value="0"   '.($_POST['query']==0 ? 'checked':"").'  >全部
					<input type="radio" name="query" value="1"   '.($_POST['query']==1 ? 'checked':"").' >未核对
					<input type="radio" name="query" value="2"   '.($_POST['query']==1 ? 'checked':"").' >已核对  
				</td>
		     </tr>';
		echo'<tr><td>' . _('Enter a partial Name') . ':</td>
		         <td>';
		if (isset($_POST['Keywords'])) {
			echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
		} else {
			echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
		}
		echo '</td></tr>		
			<tr>
			   
			   <td><b>' . _('OR') . '</b>' . _('Enter a partial Code') . ':</td>
			    <td>';
		if (isset($_POST['SupplierCode'])) {
			echo '<input type="text" autofocus="autofocus" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size="15" maxlength="18" />';
		} else {
			echo '<input type="text" autofocus="autofocus" name="SupplierCode" size="10" maxlength="15" />';
		}
		echo '</td>
			  </tr>';
	
	
echo'</table>';
//prnMsg($sql);
$SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);		
if ($SettleTab[7]>=1){
  //需要修改7销售
	prnMsg(substr($_POST['SelectDate'],0,7).'供应商对账已经完成!','info');
	include('includes/footer.php');
	exit;	
}
	
	prnMsg($msg,'info');

	echo '<br />
	<div class="centre">
		
		<input type="submit" name="SearchSettle" value="查询账单" />
		<input type="submit" name="crtExcel" value="导出Excel" />
		<input type="submit" name="CheckAccount" value="核对账单" /> 
	
	</div><br />';
	/*
	echo'<table cellpadding="3" class="selection">
	<tr>
	<td ><input type="submit" name="Search" value="查询" /><br></td>

		</table>';
        */
    	//TYPE 17 采购计划 25 采购合同收货  45简易收货
		
if (isset($_POST['SearchSettle'])&& $_POST['Select']=="") {
			$SQL="SELECT `invno`, `invtype`, `tag`, `transno`, `period`, 
			             `invdate`, `amount`, `tax`, `currcode`, `toregisterno`,
						  `toaccount`,	`toname`,`tobank`, `toaddress`, 
						  `stockname`, `spec`, `unit`, `price`, 
						  `quantity`, `remark`, `flg`, `stled`
					 FROM `invoicetrans` 
					 WHERE period=".$_SESSEN['period']." AND invtype=0";
	
	$_POST['SelectDate']=PeriodGetDate($_POST['ERPPrd']);
	$sql="SELECT  a.connectid ,
					debtorno supplierno,
					c.custname suppname,
					trandate,
					transno,
					a.stockid,						
					a.type,
					b.description,
					price,								
					sum(qty) qty 				
				FROM  stockmoves a
				LEFT JOIN stockmaster b ON b.stockid=a.stockid
				LEFT JOIN custname_reg_sub c ON c.regid = debtorno					
				WHERE a.type IN (17,25,45) AND trandate<='".$_POST['SelectDate']."'
						AND stled=0 ";
			
	if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
		$sql.=" AND d.suppname " . LIKE . " '$SearchString'";
	}
	if (isset($_POST['SupplierCode']) AND mb_strlen($_POST['SupplierCode'])>0) {
		if (stripos($_POST['SupplierCode'],',')>0){
			$sql.=" AND debtorno IN (".$_POST['SupplierCode'].")";
		}else if (stripos($_POST['SupplierCode'],'-')>0){
			$SupplierCode=explode('-',$_POST['SupplierCode']);
			$sql.=" AND debtorno >=".$SupplierCode[0]." AND debtorno<= ".$SupplierCode[count($SupplierCode)-1];
		
		}else if (stripos($_POST['SupplierCode'],'~')>0){
			$SupplierCode=explode('~',$_POST['SupplierCode']);
			$sql.=" AND debtorno >=".$SupplierCode[0]." AND debtorno<= ".$SupplierCode[count($SupplierCode)-1];
		
		}else{
			$sql.=" AND debtorno ='".$_POST['SupplierCode']."'";
		}				
	}
	$sql.="	GROUP BY 	a.connectid ,
						debtorno ,
						trandate,
						transno,
						a.stockid,						
						a.type,
						b.description,
						price			
					ORDER BY c.custname,
							debtorno,
						a.connectid ";
	$result=DB_query($sql);	
	//prnMsg($sql);
		echo '<table width="90%" cellpadding="4"  class="selection">
			<tr>
				<th >序号</th>				
				<th >合同号</th>
				<th >日期</th>
				<th >入库单号</th>
				<th >物料编码/名称</th>
				<th >收货数</th>
				<th >单价</th>
				<th >税率</th>
				<th >合计</th>
				<th >类别</th>
				<th ></th>
			</tr>';
			$RowIndex=1;
			$k=0;	
			$rw=0;
			$SuppNo=0;
			$supacc='-1';
			$price=0;
			$suptyp=2;
			$qty=0;
			$ATotal=0;
			$TATotal=0;
	while($row=DB_fetch_array($result)){
		if ($row['type']==25 ||$row['type']==45){  //采购合同收货
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
	
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		}else{
			echo '<tr class="OddTableRows">';
			$k=1;
		}	
		$SuppInv='';//Arr=array();	
		if ($row['supplierno']!=$SuppNo ) {
			if ($rw>1) {
				echo '
				<td></td>
				<td colspan="5">'.$SuppName.'....合计</td>				
				<td class="number">'.$Amount.'</td>
				<td class="number">'.($TaxAmo-$Amount).'</td>
				<td class="number">'.$TaxAmo.'</td>
				<td ></td>
				<td ></td>
			</tr><tr>';				
				$price=0;
				$TaxTotal=0;			
			}
			$rw=1;
			$SuppNo=$row['supplierno'];
			$SuppName=$row['suppname'];
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
			}	
			
				// <th colspan="12" ><input type="submit" name="Select" value="['.$row['supplierno'].']'.$row['suppname'].'" /></th>
			echo'<th ></th><th colspan="2">['.$row['supplierno'].']'.$row['suppname'].'</th>
				  <th colspan="9"></th>
				  </tr>';
				 
		    $invsql="SELECT `invno`, `invtype`,`transno`, `invdate`, `amount`, `tax`
			            FROM `invoicetrans` 
						WHERE regid=".$row['supplierno']." 
						 AND period='".$SelectPrd."' AND stled=0";
		   $Result=DB_query($invsql);
		  // prnMsg($invsql);
		if (DB_num_rows($Result)>0){
		   while($row=DB_fetch_array($Result)){
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
			}	
                $SuppInv.=$row['invno'].':'.$row['invdate'].'['.$row['amount'].':'.$row['tax'].']'.'&#13;<br>';
				echo'<td>'.$RowIndex.'</td>
				<td>';
				
					echo '<td>'.$row['invno'].'</td>';		
			echo'	<td>'.$row['invdate'].'</td>		
					<td>'.$row['invno'].'</td>		
					<td>'.$row['stockid'].':'.$row['description'].' 	</td> 	
					<td class="number">'.locale_number_format(round($row['amount'],2),2).'</td>
					<td class="number">'.locale_number_format($TaxPrice,2).'</td>
					<td class="number">'.locale_number_format($TaxRate*100,0).'%</td>
					<td class="number">'.locale_number_format($TaxPrice*$row['qty'],2).'</td>
					<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
				</tr>';
				
			}
		}
		 //  echo '<td title="'.$SuppInv.'">'.($SuppInv==''?"无发票":'已开发票').'</td>';
		}/*else {		
			$rw++;	
			echo'<td>'.$RowIndex.'</td>
				<td></td>
				<td></td>';		
		}
		*/
		$qty+=round($row['qty'],2);
	
		$orderno=$row['connectid'];	
		echo'<td>'.$RowIndex.'</td>
		    <td>';
			if ($row['type']==17){
				echo '计划单:'.$orderno;
			}elseif ($row['type']==25){
				echo '合同:'.$orderno;
			}elseif ($row['type']==45){
				echo '简易收货:'.$orderno;
			}
		echo' </td>
				<td>'.$row['trandate'].'</td>		
				<td>'.$row['transno'].'</td>		
				<td>'.$row['stockid'].':'.$row['description'].' 	</td> 	
				<td class="number">'.locale_number_format(round($row['qty'],2),2).'</td>
				<td class="number">'.locale_number_format($TaxPrice,2).'</td>
				<td class="number">'.locale_number_format($TaxRate*100,0).'%</td>
				<td class="number">'.locale_number_format($TaxPrice*$row['qty'],2).'</td>
				<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
			</tr>';
			
			$RowIndex++;
			
	}//end while
		if ($rw>1 ) {
			echo '<tr>
			<td></td>
			<td colspan="6">小计</td>				
			<td class="number" >'.locale_number_format($ATotal,2).'</td>
			<td class="number">'.locale_number_format(($TATotal-$ATotal),2).'</td>
			<td class="number">'.locale_number_format($TATotal,2).'</td>
			<td ></td>
			<td ></td>
		</tr>';
			//$SuppNo=$row['supplierno'];
		}
		if ( $rw>1) {
			echo '<tr>
			<td></td>
			<td colspan="3">对账合计</td>				
			<td class="number">'.locale_number_format($price,2).'</td>
			<td class="number">'.locale_number_format(($qty-$price),2).'</td>
			<td class="number">'.locale_number_format($qty,2).'</td>
			<td ></td>
			<td ></td>
		</tr>';
			$price=0;
			$qty=0;
			$rw=1;
		}
		echo '<tr>
				<td></td>
				<td colspan="3">总计</td>				
				<td class="number">'.locale_number_format($price,2).'</td>
				<td class="number">'.locale_number_format($TaxTotal,2).'</td>
				<td class="number">'.locale_number_format(($TotalAll+$TaxTotalAll),2).'</td>
				<td ></td>
				<td ></td>
			</tr>';
		echo'</table>';			
	//}
}elseif($_POST['Select']!=""||isset($_POST['SelectSave'])) {
	$_SESSION['Select']=substr(explode(']',$_POST['Select'])[0],1);
	$sql="SELECT  a.connectid ,
				debtorno supplierno,
				d.suppname,
				trandate,
				transno,
				a.stockid,						
				a.type,
				b.description,
				price,								
				sum(qty) qty 				
			FROM  stockmoves a
			LEFT JOIN stockmaster b ON b.stockid=a.stockid				
			LEFT JOIN suppliers d ON d.supplierid = debtorno					
			WHERE a.type IN (17,25) AND trandate<='".$_POST['SelectDate']."'
				AND stled=0 AND debtorno='".$_SESSION['Select']."'
			GROUP BY 
			a.connectid ,
				debtorno ,
				trandate,
				transno,
				a.stockid,						
				a.type,
				b.description,
				price			
			ORDER BY d.suppname,
					debtorno,
				a.connectid ";		
	$result=DB_query($sql);
	$sql="SELECT description taxcatname, taxid `taxcatid`, `taxrate` FROM `taxauthorities`";
		
	$TaxResult=DB_query($sql);
	echo '<table width="90%" cellpadding="4"  class="selection">
		<tr><td colspan="4">选择或输入发票号码<input list="companys"/>
			 <datalist id="companys">
			  <option value="Apple">
			  <option value="Microsoft">
			  <option value="Github">
			</datalist>
			</td>
		
			<td colspan="2">发票金额<input type="text" name="InvAmount" value=""   ></td>
			<td colspan="2"><select name="TaxCat">';//taxrate

			while ($myrow = DB_fetch_array($TaxResult)) {
				
				if (  $myrow['taxcatid'].'^'.$myrow['taxrate']  ==$_POST['TaxCat']) {
					echo '<option selected="selected" value="' . $myrow['taxcatid'].'^' .$myrow['taxrate']. '">' . $myrow['taxcatname'] . '</option>';
				} else {
					echo '<option value="' . $myrow['taxcatid'].'^'.$myrow['taxrate'] . '">' . $myrow['taxcatname'] . '</option>';
				} //end while loop
			}
		
		echo '</select></td>
				<td colspan="3">税额<input type="text" name="TaxAmount" value=""   ></td>
				</tr>
				<tr>
					<th >序号</th>	
					<th >发票号/金额</th>
					<th >合同号</th>
					<th >日期</th>
					<th >入库单号</th>
					<th >物料编码/名称</th>
					<th >收货数</th>
					<th >单价</th>
					<th >税率</th>
					<th >小计</th>
					<th >类别</th>
					<th ></th>
				</tr>';
			$RowIndex=1;
			$k=0;
			//$rr=0;
			$rw=1;
			$SuppNo='';
			$supacc='-1';
			$price=0;
			$suptyp=2;
			$qty=0;
			$ATotal=0;
			$TATotal=0;
	while($row=DB_fetch_array($result)){
		//$SQL="SELECT  `cess`, `taxprice` FROM `purchrequest` WHERE connectid='".$row['connectid']."' AND type='".$row['type']."' AND stockid='".$row['stockid']."' LIMIT 1";
		/*
		$Result=DB_query($SQL);
		$Row=DB_fetch_assoc($Result);
		if (isset($Row)){
			$TaxRate=$Row['cess'];
			$TaxPrice=$Row['taxprice'];
		}else{*/
		$TaxRate=0;
		$TaxPrice=0;
		$RPError=1;
	
		if ($supacc!=$suptyp ) {			
			if ( $rw>1) {
				echo '<tr>
				<td></td>
				<td colspan="7">对账合计</td>				
				<td >'.$price.'</td>
				<td >'.($qty-$price).'</td>
				<td >'.$qty.'</td>
				<td ></td>
				<td ></td>
			</tr>';
				$price=0;
				$TaxTotal=0;
				$rw=1;
			}				
			$supacc=$suptyp;
		}


		$qty+=round($row['qty'],2);

		$orderno=$row['connectid'];			
		echo '<tr class="OddTableRows">
				<td>'.$RowIndex.'</td>';
		$k=1;			
		echo '<td>'.$row['supaccno'].'</td>';
		echo'<td>';
		if ($row['type']==17){
			echo '计划单:'.$orderno;
		}else{
			echo '合同:'.$orderno;
		}
		echo' </td>
			<td>'.$row['trandate'].'</td>		
			<td>'.$row['transno'].'</td>		
			<td>'.$row['stockid'].':'.$row['description'].' 	</td> 	
				<td class="number">'.locale_number_format(round($row['qty'],2),2).'</td>
				<td class="number">'.locale_number_format($TaxPrice,2).'</td>
				<td class="number">'.locale_number_format($TaxRate*100,0).'%</td>
				<td class="number">'.locale_number_format($TaxPrice*$row['qty'],2).'</td>
				<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'"   ></td>											
			</tr>';
		
		$RowIndex++;
		//$rr++;	
		//$rw++;
}//end while

	//$SuppNo=$row['supplierno'];

	if ( $RowIndex>1) {
		echo '<tr>
				<td></td>
				<td colspan="3">对账合计</td>				
				<td class="number">'.locale_number_format($price,2).'</td>
				<td class="number">'.locale_number_format(($qty-$price),2).'</td>
				<td class="number">'.locale_number_format($qty,2).'</td>
				<td ></td>
				<td ></td>
			</tr>';
		$price=0;
		$qty=0;
		//$rw=1;
	}

echo'</table>';			
echo '<br />
	<div class="centre">
		
		
		<input type="submit" name="SelectSave" value="确认选择" /> 
	</div><br />';


}
	/*
if (isset($_POST['Search'])) {
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
	if (isset($SingleSupplierReturned)) { 
 	   $_SESSION['SupplierID'] = $SingleSupplierReturned;
	   unset($_POST['Keywords']);
	   unset($_POST['SupplierCode']);
	   unset($_POST['Search']);
        } else {
               unset($_SESSION['SupplierID']);
        }
	} //end of if search
    

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
}*/
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
