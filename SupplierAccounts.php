<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-04-11 03:14:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-06-29 05:08:46
 */

	include('includes/session.php');

	include('includes/DefineSuppAllocsClass.php');
	$Title ='供应商对账';

	$ViewTopic= 'AccountsPayable';
	$BookMark = 'SupplierInvoice';
	include('includes/header.php');
	include('includes/SQL_CommonFunctions.inc');
	
	//$afterdt=date('Y-m-d',strtotime (date('Y-m-d'))-31536000);
	if (isset($_GET['PeriodSelect'])){
		//添加？参数使用$_SESSION['period']
		$_POST['AfterDate']=PeriodGetDate($_SESSION['janr']);
		$_POST['JanrPeriod']=$_SESSION['janr'];
		$_POST['BeforDate']=$_SESSION['lastdate'];
		$_POST['PeriodEnd']=$_SESSION['period'];
	}else{
		//if (!isset($_POST['AfterDate'])){
			$_POST['AfterDate']=date('Y-01-01');
			$_POST['JanrPeriod']=DateGetPeriod($_POST['AfterDate']);
		//}
		//if (!isset($_POST['BeforDate'])){
			$_POST['BeforDate']=date('Y-m-d');
			$_POST['PeriodEnd']=$_POST['JanrPeriod']+date('m')-1;;
		//}
	}
	//$JanrPeriod=DateGetPeriod($_POST['AfterDate']);
		//1月的期间period
	$_SESSION['AllocTrans'][2]=	$JanrPeriod;
	   //年1月1日	
	$_SESSION['AllocTrans'][3]=	$_POST['AfterDate'];
	   //选择期最末日期不能大于年末	
	$_SESSION['AllocTrans'][4]=$_POST['BeforDate'];	
	  //选择期最末Period不能大于年末	
	$_SESSION['AllocTrans'][5]=$_POST['PeriodEnd'];	
	if (isset($_POST['ClearCache'])){
		unset($_SESSION['SupplierAct']);
		unset($_SESSION['AllocTrans']);
		unset($_GET['AllocTrans']);
		//echo '-=';
		echo '<meta http-equiv="refresh" content="0; url=' . $RootPath . '/SupplierAccounts.php">';

	}
   
	$InvType=array(0=>'进项发票',1=>'销项专票',3=>'销项发票');
	
	if(!empty($_SESSION['AllocTrans'][0])){
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?AllocTrans='.$_SESSION['AllocTrans'][0]. '&SuppName='.$_SESSION['SuppName'][1].'"  method="post" name="form">';
	}else{
	    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" name="form">';
    }
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath . '/css/' . $Theme .
	'/images/transactions.png" title="' . _('Supplier Invoice') . '" />' . ' ' .
	$Title . ': ' . $SupplierName . '</p>';
	if (isset($_GET['AllocTrans'])){
	  
		$_SESSION['AllocTrans'][0]=$_GET['AllocTrans'];
		$_SESSION['AllocTrans'][1]=$_GET['SuppName'];
   
  	}
if (!isset($_GET['AllocTrans'])){
	echo'<table cellpadding="3" class="selection">
		<tr>
		<td>' . _('Enter a partial Name') . ':</td>
		<td>';
	if (isset($_POST['Keywords'])) {
		echo '<input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" />';
	} else {
		echo '<input type="text" name="Keywords" size="20" maxlength="25" />';
	}
	echo '</td>
		<td><b>' . _('OR') . '</b></td>
		<td>' . _('Enter a partial Code') . ':</td>
		<td>';
	if (isset($_POST['SupplierCode'])) {
		echo '<input type="text" autofocus="autofocus" name="SupplierCode" value="' . $_POST['SupplierCode'] . '" size="15" maxlength="18" />';
	} else {
		echo '<input type="text" autofocus="autofocus" name="SupplierCode" size="15" maxlength="18" />';
	}
		echo '</td></tr>
				</table>';		
	
	echo '<div class="centre">		
	         <input type="submit" name="Search" value="查询" />
			 <input type="submit" name="CSV" value="导出CSV" />
			</div>';
		
			$SQL="SELECT DISTINCT `periodno` FROM `gltranstock` WHERE periodno=".($JanrPeriod-1);
			$esult=DB_query($SQL);	
			if (DB_num_rows($result)==0){
				prnMsg($JanrPeriod."年末没有结账，不能进行供应商对账！",'info');
				include('includes/footer.php');
				//exit;
			}
			//读取本年的出入库物料	
			$sql="SELECT   suppname,							
							debtorno ,	
							stled,					
							SUM(CASE WHEN  stled=0 THEN price*qty ELSE 0 END) amount	,
							SUM(CASE WHEN  stled<>0 THEN price*qty ELSE 0 END) stledamount			
				  FROM stockmoves a		
				 	 LEFT JOIN suppliers b ON	a.debtorno = b.supplierid
 					WHERE (type=25 OR type=17 OR type=45) AND DATE_FORMAT(trandate,'%Y-%m-%d')<='".$_POST['BeforDate']."'
			  		AND DATE_FORMAT(trandate,'%Y-%m-%d')>='".$_POST['AfterDate']."'"; 
			if (isset($_POST['SupplierCode']) AND mb_strlen($_POST['SupplierCode'])>0) {
				//insert wildcard characters in spaces
				$_POST['SupplierCode'] = mb_strtoupper($_POST['SupplierCode']);
				$SuppNameString = '%' . $_POST['SupplierCode'] . '%';
				$sql.=" AND a.debtorno " . LIKE . " '$SuppNameString'";
			}
			if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
				//insert wildcard characters in spaces
				$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
				$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
				$sql.=" AND suppname " . LIKE . " '$SearchString'";
			}		
			$sql.=" GROUP BY suppname,debtorno,stled ORDER BY debtorno";
            //prnMsg($sql);
			$result=DB_query($sql);	
			$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
			include('includes/footer.php');
			exit;
		}
	if (isset($_POST['Search'])OR isset($_POST['Go'])	OR isset($_POST['Next'])OR isset($_POST['Previous'])) {

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
			
		echo '<table width="90%" cellpadding="4"  class="selection">
			<tr>
				<th >序号</th>				
				<th >供应商编码名称</th>
				<th >开始日期</th>					
				<th >最末日期</th>
				<th >已核对金额</th>
				<th >待核对金额</th>
				<th >合计</th>				
				<th ></th>								
			</tr>';				
			$RowIndex = 0;		
		if($ListCount <> 0 ) {
		
				DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			while ($row=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			
				if ($k==1){
					echo '<tr class="EvenTableRows">';
															
					$k=0;
				}else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				echo '<td>'.($RowIndex+1).'</td>				
					<td >['.$row['debtorno'].']'.$row['suppname'].'</td>	
					<td></td>
					<td ></td>	
					<td class="number">'.locale_number_format($row['stledamount'],POI).'</td>
					<td class="number">'.locale_number_format($row['amount'],POI).'</td>
					<td class="number"></td>		
					<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?AllocTrans='.$row['debtorno'].'&SuppName='.$row['suppname'].'">对账</a></td>													
					</tr>';				
					$RowIndex++;
						
			}//end while			
			echo '<tr>									
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
}

if (isset($_GET['AllocTrans'])||isset($_POST['VerifyAccount'])||isset($_POST['Serach'])){
	
	echo '<table class="selection">
	
			<tr>
				<td class="label" ><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Customer') . '" alt="" />供应商编码/名称 :<b></td>
				<td colspan="3"><b> [' .$_SESSION['AllocTrans'][0].']'. $_SESSION['AllocTrans'][1]. '</b></td>			
			</tr>
			<tr>
				<td class="label">合同号:</td>
				<td>'.$_SESSION['Items'.$identifier]->OrderNo.'</td>
				<td class="label"><b>' . _('Invoice amounts stated in') . '</b></td>
				<td ><b> ' . $_SESSION['Items'.$identifier]->DefaultCurrency . '</b></td>
			</tr>			
			<tr>
				<td class="label">客户合同号:</td>
				<td colspan="3">'.$_SESSION['Items'.$identifier]->CustRef.'</td>
			
		</tr>';
	
		echo '<tr>
			<td class="label">选择会计期间</td>
			<td colspan="2"><select name="SelectPrd">';	
		$mth=date("m");
		
		$prd=$_SESSION['AllocTrans'][2]+1;
		$prddate=PeriodGetDate($prd);   
    for ($m=0;$m<=$mth;$m++){
	 
	  $mp=$prd+$m;
	  if ($m==0){
		  $YM="全部";
		  $mp=0;
	  }else{
		   $YM=  MonthAndYearFromSQLDate(date("Y-$m-01"),strtotime($prddate));
	  }
	  if(!isset($_POST['SelectPrd']))
	  $_POST['SelectPrd']=$mp;
		  if($m+$prd == $_POST['SelectPrd']){
  
			  echo '<option selected="selected" value="' . ($mp). '">' . $YM . '</option>';
		  } else {
			  echo '<option value="' . $mp . '">' . $YM . '</option>';
		  }
	 
 	}
	  echo '</select></td>
						
				<td></td>
						</tr>';
	echo'</table><br />';
	echo '<div>	<input type="hidden" name="TotalNumberOfAllocs" value="' . $Counter . '" />
		<br />
		<input type="submit" name=Serach" value="查询" />
		<input type="submit" name="ClearCache" value="清除缓存" />';

	echo'</div>';
	//得到选择会计期间的月初-- 月末
	
	if ($_POST['SelectPrd']==0){
		$_POST['StartDate']=$_SESSION['AllocTrans'][3];
		$_POST['EndDate']=$_SESSION['AllocTrans'][4];
	
		//$_POST['JanrPeriod']=DateGetPeriod($_POST['AfterDate']);

	
		$_POST['PeriodEnd']=DateGetPeriod($_POST['EndDate']);
	}else{
	
		$_POST['PeriodEnd']=$_POST['SelectPrd'];
		$dt=PeriodGetDate($_POST['SelectPrd']);

		
		$_POST['StartDate']=date("Y-m-01",strtotime($dt));
		$_POST['EndDate']=date("Y-m-t",strtotime($dt));
	
	}
	prnMsg($_POST['StartDate'].'='.$_POST['EndDate']);
	if (!isset($_SESSION['SupplierAct']['INV'])){
		//读取发票内容  添加registerno
		$SQL="SELECT `invno`, `invtype`, `tag`, `transno`, `period`, `invdate`,
					`amount`, `tax`, `currcode`, `toregisterno`, `toaccount`,      
					`quantity`, `remark`, `uploadid`, `flg`
				FROM `invoicetrans` 
				WHERE regid='".$_GET['AllocTrans']."' AND invtype=0 AND stled=0
					   AND period>=".$_POST['JanrPeriod']." AND period<=" .$_POST['PeriodEnd'];
					  // echo  '-='.$SQL;
		$result=DB_query($SQL);
		$ListCount=DB_num_rows($result);
	
		if ($ListCount>0){
			while ($myrow=DB_fetch_array($result)) {
				$taxrate=$myrow['tax']!=0?round(100*$myrow['tax']/$myrow['amount'],0):0;
				$_SESSION['SupplierAct']['INV'][]=array("invdate"=>$myrow['invdate'],"invno"=>$myrow['invno'],"invtype"=>$myrow['invtype'],"taxrate"=>$taxrate,"amount"=>$myrow['amount'],"tax"=>$myrow['tax'],"transno"=>$myrow['transno'],"stled"=>$myrow['stled']);
			}
		}
	}	
	if (count($_SESSION['SupplierAct']['INV'])>0){
		// var_dump($_SESSION['SupplierAct']['INV']);
		$InvAmoTotal=[];					
		$InvTaxTotal=[];			
		$TransNO=0;
		echo '<table cellpadding="1" class="selection">
				<tr>
				<th colspan="11">会计凭证</th>	</tr>	
				<tr>
				<tr>
				<th class="ascending">序号</th>						
				<th class="ascending">' . _('Date') . '</th>	
				<th class="ascending" title="点击链接.手动生成会计凭证!">发票号</th>			
				<th class="ascending">发票类别</th>
				<th class="ascending">税率</th>
				<th class="ascending">税金</th>		
				<th class="ascending">金额</th>			
				<th >合计</th>
				<th >凭证号</th>
				<th >状态</th>			
				<th ></th>
			</tr>';	
		$k = 0; //row colour counter
		$RowIndex = 1;
	
		foreach($_SESSION['SupplierAct']['INV'] as $myrow){
			  $boxinv='';
			if (!isset($_POST["stlBox".$RowIndex])){
				$_POST["stlBox".$RowIndex]= $myrow['stled'];
			
			}
			if ($_POST["stlBox".$RowIndex]==0)
				$boxinv="checked";
			if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo'
			<td >'.$RowIndex.'</td>
			<td >'.$myrow['invdate'].'</td>
			<td >'.$myrow['invno'].'
				<input type="hidden" name="Number'.$RowIndex.'" value="' . $RowIndex . '" />			    
				<input type="hidden" name="InvNo'.$RowIndex.'" value="' . $myrow['invno'] . '" />
				<input type="hidden" name="InvTyep'.$RowIndex.'" value="' . $myrow['InvType'] . '" />
				<input type="hidden" name="InvTax'.$RowIndex.'" value="' . $myrow['tax'] . '" />
				<input type="hidden" name="InvAmount'.$RowIndex.'" value="' . $myrow['amount'] . '" />			
				<input type="hidden" name="TransNo'.$RowIndex.'" value="' . $myrow['TransNo'] . '" />	</td>
			<td >'.$InvType[$myrow['invtype']].'</td>
			<td >'.($myrow['tax']!=0?round(100*$myrow['tax']/$myrow['amount'],0).'%':'0').'</td>
			<td class="number">'.locale_number_format($myrow['tax'],POI).'</td>
			<td class="number">'.locale_number_format($myrow['amount'],POI).'</td>	
			<td >'.locale_number_format(($myrow['amount']+$myrow['tax']),POI).'</td>			
			<td >'.$myrow['transno'].'</td>
			<td >'.$myrow['stled'].'</td>
			<td >
			<input type="checkbox" name="stlBox'.$RowIndex.'" id="stlBox'.$RowIndex.'"  value="1"  onchange="OnChkbx(this ,'.$RowIndex.')" '.$boxinv.' ></td>	';
			$RowIndex++;
			
			$InvAmoTotal[$myrow['invtype']]+=$myrow['amount'];
			$InvTaxTotal[$myrow['invtype']]+=$myrow['tax'];
		}
		echo '<tr>
			<th></th>
			<th ></th>				
			<th colspan="3">总计</th>	
			<th class="number">'.locale_number_format(array_sum($InvTaxTotal),POI).'</th>
			<th class="number">'.locale_number_format(array_sum($InvAmoTotal),POI).'</th>
		
			<th class="number">'.locale_number_format(array_sum($InvAmoTotal)+array_sum($InvAmoTotal),POI).'</th>
			<th ></th>
			<th ></th>
			<th ></th>
		</tr>';
	}
	//对账单读取
	if (!isset($_SESSION['SupplierAct']['STL'])){
		$SQL="SELECT `stockactid`, `regid`, `stkacttype`, `transno`, `prd`, `stkactdate`, `settledate`, `tax`, `amount`, `tag`, `diffonexch`, `settled` 
		        FROM `stockaccounts` 
				WHERE  regid='".$_GET['AllocTrans']."' AND settled=0
				      AND prd  >=".$_POST['JanrPeriod']." AND prd<=" .$_POST['PeriodEnd'];
		$result=DB_query($SQL);
		$ListCount=DB_num_rows($result);
	
		if ($ListCount>0){
			while ($myrow=DB_fetch_array($result)) {
			
				$_SESSION['SupplierAct']['STL'][]=array("stkactdate"=>$myrow['stkactdate'],"stockactid"=>$myrow['stockactid'],"amount"=>$myrow['amount'],"tax"=>$myrow['tax'],"diffonexch"=>$myrow['diffonexch'],"settled"=>$myrow['settled']);
			}
		}
	}	
	$Number= $RowIndex;

	if (count($_SESSION['SupplierAct']['STL'])>0){
		// var_dump($_SESSION['SupplierAct']['STL']);
		$InvAmoTotal=[];					
		$InvTaxTotal=[];			
		$TransNO=0;
		echo '<table cellpadding="1" class="selection">
			<tr>
				<th colspan="10">对账单</th>	</tr>	
			<tr>
				<tr>
					<th class="ascending">序号</th>						
					<th class="ascending">' . _('Date') . '</th>	
					<th class="ascending" title="">对账单编号</th>					
					<th class="ascending">税额</th>
					<th class="ascending">不含税金额</th>		
					<th class="ascending">合��金额</th>			
					<th >差异额</th>
					<th >发票号</th>
					<th >结账日期</th>			
					<th >状态</th>
				</tr>';	
		$k = 0; //row colour counter
		$RowIndex = 1;
	
		foreach($_SESSION['SupplierAct']['STL'] as $myrow){
			$box='';
			if (!isset($_POST["stlBox".$Number])){
				$_POST["stlBox".$Number]= $myrow['stled'];
			
			}
			if ($_POST["stlBox".$Number]==0)
				$box="checked";
			if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo'
			<td >'.$RowIndex.'</td>
			<td >'.$myrow['stkactdate'].'</td>
			<td >'.$myrow['stockactid'].'
				<input type="hidden" name="Number'.$Number.'" value="' . $Number . '" />			    
				<input type="hidden" name="TransNo'.$Number.'" value="' . $myrow['TransNo'] . '" />	</td>
			<td class="number">'.locale_number_format($myrow['tax'],POI).'</td>
			<td class="number">'.locale_number_format($myrow['amount'],POI).'</td>	
			<td >'.locale_number_format(($myrow['amount']+$myrow['tax']),POI).'</td>
			<td class="number">'.locale_number_format($myrow['diffonexch'],POI).'</td>				
			<td >'.$myrow['diffonexch'].'</td>
			<td >'.$myrow['settled'].'</td>
			<td >
			<input type="checkbox" name="stlBox'.$Number.'" id="stlBox'.$Number.'"  value="1"  onchange="OnChkbx(this ,'.$Number.')" '.$box.' ></td>	';
			$Number++;
			$RowIndex++;
			$InvAmoTotal[$myrow['invtype']]+=$myrow['amount'];
			$InvTaxTotal[$myrow['invtype']]+=$myrow['tax'];
		}
		echo '<tr>
			<th></th>
			<th ></th>				
			<th colspan="3">总计</th>	
			<th class="number">'.locale_number_format(array_sum($InvTaxTotal),POI).'</th>
			<th class="number">'.locale_number_format(array_sum($InvAmoTotal),POI).'</th>
			<th class="number">'.locale_number_format(array_sum($InvAmoTotal)+array_sum($InvAmoTotal),POI).'</th>
			<th ></th>
			<th ></th>
			<th ></th>
		</tr>';
	}
	$SQLMove="SELECT  c.suppname ,
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
					debtorno,			
					price,
					prd,
					reference,
					qty,
					discountpercent,
					standardcost,
					show_on_inv_crds,			
					hidemovt,
					narrative,
					decimalplaces
					FROM stockmoves a		
					LEFT JOIN stockmaster d ON	a.stockid = d.stockid
					LEFT JOIN suppliers c ON a.debtorno=c.supplierid
				WHERE (type=25 OR type=17 OR type=45) AND  DATE_FORMAT(trandate,'%Y-%m-%d')<='".$_POST['EndDate']."'
						AND  DATE_FORMAT(trandate,'%Y-%m-%d')>='".$_POST['StartDate']."'
				       AND a.debtorno='".$_GET['AllocTrans']."' ";
               $movesResult=DB_query($SQLMove);
			   $ListCount=DB_num_rows($movesResult);
    //prnMsg($SQLMove);

	//if (isset($_POST['AllocTrans'])){

	echo '<input type="hidden" name="AllocTrans" value="' . $_POST['AllocTrans'] . '" />';	  		
	echo '<table width="90%" cellpadding="4"  class="selection">
		<tr>
			<th colspan="12">收货单</th>	</tr>	
		 <tr>
			<tr>
				<th >序号</th>
				<th >入库</br>单号</th>						
				<th >日期</th>					
				<th >合同号</br>计划单</th>
				<th >物料编码:名称</th>
				<th >数量</th>
				<th >含税</br>价格</th>
				<th >税额</th>
				<th >金额</br>合计</th>						
				<th >摘要</th>
				<th >算</br>价格</th>
				<th ></th>				
			</tr>';			
		 $StockTaxTotal=0;					
		 $StockAmoTotal=0;			
		 $Number= $RowIndex;
		 $RowIndex = 1;
		
		 $TaxTotal=0;
		 $AmoTotal=0;
	if($ListCount <> 0 ) {
		echo $TableHeader;
			DB_data_seek($movesResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			
		while ($row=DB_fetch_array($movesResult) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			$box='';
			if (!isset($_POST["stlBox".$Number])){
				$_POST["stlBox".$Number]= $myrow['stled'];
			
			}
			if ($_POST["stlBox".$Number]==0)
				$box="checked";
		
			if ($row['type']==25||$row['type']==45){  //采购合同收货
				$SQL="SELECT  `cess`, `taxprice` FROM `purchorderdetails` WHERE  `orderno`='".$row['connectid']."' AND itemcode='".$row['stockid']."' LIMIT 1";
			}elseif($row['type']==17){//计划单收��
				$SQL="SELECT   `taxcatid`, `cess`, `taxprice` FROM `stockrequestitems` WHERE `dispatchid`='".$row['connectid']."' AND`stockid`='".$row['stockid']."' LIMIT 1";
			}
			$Result=DB_query($SQL);
			
			
			$Row=DB_fetch_assoc($Result);
			if (isset($Row)){
				$TaxRate=$Row['cess'];
				$TaxPrice=$Row['taxprice'];
			}else{
				$TaxRate=1;
				$TaxPrice=0;
				$RPError=1;
			}
			if ( $TaxRate==0){
				$TaxRate=1;
			}
			$AmoTotal=round($row['qty']*$TaxPrice,POI);
			$TaxTotal=round($AmoTotal/(1+$TaxRate)*$TaxRate,POI);
			
				if ($k==1){
					echo '<tr class="EvenTableRows">';
															
					$k=0;
				}else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				
				echo '<td>'.$RowIndex.'
							<input type="hidden" name="Number'.$Number.'" value="' . $Number . '" />	</td></td>';
			if ($TransNO!=$row['transno']){
		
				$URL_Edit= $RootPath .'/PDFPurchOrder.php?F='.$row['type'].'&D=' . $row['transno'] ;
				$TransNO=$row['transno'];	 
				echo'<td><a href="'.$URL_Edit .'" title="点击" target="_blank" >'.$row['transno'].'</a></td>';				
				echo'<td >'.$row['trandate'].'</td>';
			}else{				
				echo'<td></td>			
					<td ></td>';					
			}	
			echo'<td>';
					if ($row['type']==17){
						echo '计划单:'.$row['connectid'];
					}elseif ($row['type']==25){
						echo '合同:'.$row['connectid'];
					}elseif ($row['type']==45){
						echo '简易收货:'.$row['connectid'];
					}
			echo'</td>
				<td >'.$row['stockid'].':'.$row['description'].'
				<input type="hidden" name="LocCode'.$Number.'" value="' . $myrow['loccode'] . '" />
					<input type="hidden" name="stkMoveNo'.$Number.'" value="' . $myrow['stkmoveno'] . '" />
					<input type="hidden" name="TranDate'.$Number.'" value="' . $myrow['trandate'] . '" />
					<input type="hidden" name="Qty'.$Number.'" value="' . $myrow['qty'] . '" />
					<input type="hidden" name="TaxPrice'.$Number.'" value="' . $TaxPrice . '" />
					<input type="hidden" name="StockTax'.$Number.'" value="' . $TaxTotal . '" /></td>
					<input type="hidden" name="StockAmo'.$Number.'" value="' . $AmoTotal . '" /></td>';
			echo'<td class="number">'.locale_number_format($row['qty'],$myrow['decimalplaces']).'</td>
				<td class="number">'.locale_number_format($TaxPrice,POI).'</td>
				<td class="number">'.locale_number_format($TaxTotal,POI).'</td>		
				<td class="number">'.locale_number_format($AmoTotal,POI).'</td>		
				<td >'.$myrow['narrative'].'</td>';
				if (!isset($_POST['SettlePrice'.$Number]))
				$_POST['SettlePrice'.$Number]=round($TaxTotal+$AmoTotal,POI);
			echo'<td ><input type="text" size="10"  maxlength="12"  name="SettlePrice'.$Number.'" value="' .$_POST['SettlePrice'.$Number] . '" /></td>
				<td ><input type="checkbox" name="stlBox'.$Number.'" id="stlBox'.$Number.'"  value="1"  onchange="OnChkbx(this ,'.$Number.')"   '.$box.' /></td>															
				</tr>';				
					$RowIndex++;
					$Number++;
					$StockAmoTotal+=$AmoTotal;
					$StockTaxTotal+=$TaxTotal;
						
		}//end while			
		echo '<tr>
				<th></th>
				<th ></th>	
				<th ></th>				
				<th colspan="2">总计</th>
				<th ></th>		
				<th class="number">'.locale_number_format(($InvTaxTotal),POI).'</th>
				<th class="number">'.locale_number_format(($InvAmoTotal),POI).'</th>
				<th class="number">'.locale_number_format(($InvAmoTotal+$InvAmoTotal),POI).'</th>
				<th ></th>
				<th ></th>
				<th ></th>
			</tr>';
		echo'</table>';	
	}
	if (isset($_POST['VerifyAccount'])){


				foreach ($_POST as $key => $value) {
					//prnMsg($key);
					if (mb_strpos($key,'Number')!==false) {				      
						$LineID = mb_substr($key,mb_strpos($key,'Number')+6);
					
						if ($_POST['stlBox'.$LineID]==1) {
							
							$StockAmo+=filter_number_format($_POST['StockAmo'.$LineID],POI);
							$StockTax+=filter_number_format($_POST['StockTax'.$LineID],POI);
						
							$InvAmount+=filter_number_format($_POST['InvAmount'.$LineID],POI);
							$InvTax+=filter_number_format($_POST['InvTax'.$LineID],POI);
						//	prnMsg($LineID.'-'.$_POST['InvNo'.$LineID].'['.$StockAmo.'=['.$_POST['stlBox'.$LineID].']'.$InvAmount.']'.$_POST['stockid'.$LineID]);
						
						}
					}
				}
				echo '<table cellpadding="1" class="selection">
				<tr>
				<th >序号</th>					
				<th >发票类别</th>
				<th >税金</th>	
				<th >金��</th>	
				<th >合���</th>		
				<th >状态</th>			
				</tr>';	
			echo'<tr class="EvenTableRows">
				<td >1</td>
				<td >采购发票合计</td>
				<td class="number">'.locale_number_format($InvTax,POI).'</td>		
				<td class="number">'.locale_number_format($InvAmount,POI).'</td>
				<td >'.locale_number_format($InvTax+$InvAmount,POI).'</td>
				<td ></td>
			</tr>';
			
		echo'<tr class="OddTableRows">
				<td >2</td>
				<td >对账单合计</td>
				<td class="number">'.locale_number_format($StockTax,POI).'</td>		
				<td class="number">'.locale_number_format($StockAmo,POI).'</td>			
				<td >'.locale_number_format($StockTax+$StockAmo,POI).'</td>
				<td ></td>
			</tr>';
		echo'<tr class="OddTableRows">
				<td >3</td>
				<td >入库合计</td>
				<td class="number">'.locale_number_format($StockTax,POI).'</td>		
				<td class="number">'.locale_number_format($StockAmo,POI).'</td>			
				<td >'.locale_number_format($StockTax+$StockAmo,POI).'</td>
				<td ></td>
			</tr>';	
		echo'<tr class="EvenTableRows">
				<td >4</td>
				<td >差异金额</td>
				<td class="number"><input type="text" size="15" name="Previous" value="'.locale_number_format($InvTax-$StockTax,POI).'"</td>
				<td class="number"><input type="text" size="15" name="Previous" value="'.locale_number_format($InvAmouny-$StockAmo,POI).'"</td>		
				<td >'.locale_number_format($InvAmouny-$StockAmo+$InvTax-$StockTax,POI).'</td>
				<td ></td>
			</tr>
		
			</table><br />';

	}elseif (isset($_POST['CreateAccounts'])||isset($_POST['SaveAccounts'])){


	foreach ($_POST as $key => $value) {
		//prnMsg($key);
		if (mb_strpos($key,'Number')!==false) {				      
			$LineID = mb_substr($key,mb_strpos($key,'Number')+6);
		
			if ($_POST['stlBox'.$LineID]==1) {
				
				$StockAmo+=filter_number_format($_POST['StockAmo'.$LineID],POI);
				$StockTax+=filter_number_format($_POST['StockTax'.$LineID],POI);
			    $SettleAmo+=filter_number_format($_POST['SettlePrice'.$LineID],POI);
				$InvAmount+=filter_number_format($_POST['InvAmount'.$LineID],POI);
				$InvTax+=filter_number_format($_POST['InvTax'.$LineID],POI);
		
			}
		}
	}
	echo '<table cellpadding="1" class="selection">
				<tr>
				<th class="ascending">序号</th>					
				<th class="ascending">发票类���</th>
				<th class="ascending">税金</th>	
				<th class="ascending">金额</th>	
				<th >合计</th>	
				<th >结算金额</th>	
				<th >状态</th>			
				</tr>';	
		echo'
			<tr class="OddTableRows">
				<td >2</td>
				<td >入库合计</td>
				<td class="number">'.locale_number_format($StockTax,POI).'</td>		
				<td class="number">'.locale_number_format($StockAmo,POI).'</td>
				<td >'.locale_number_format($StockTax+$StockAmo,POI).'</td>
				<td >'.locale_number_format($SettleAmo,POI).'</td>			
		</tr>';
		echo'<tr class="OddTableRows">
				<td >3</td>
				<td >入库合计</td>
				<td class="number">'.locale_number_format($StockTax,POI).'</td>		
				<td class="number">'.locale_number_format($StockAmo,POI).'</td>
				<td >'.locale_number_format($StockTax+$StockAmo,POI).'</td>
				<td >'.locale_number_format($SettleAmo,POI).'</td>
			</tr>';
		echo'<tr class="EvenTableRows">
				<td >4</td>
				<td >差异金额</td>
				<td class="number"></td>
				<td ></td>			
				<td ></td>
				<td >'.locale_number_format($SettleAmo-$StockTax-$StockAmo,POI).'</td>				
			</tr>
			</table><br />';
			
		if(isset($_POST['SaveAccounts'])){
			$result = DB_Txn_Begin();
			$SQL="INSERT INTO `stockaccounts`(	`regid`,
												`stkacttype`,
												`transno`,
												`prd`,
												`stkactdate`,
												`settledate`,
												`tax`,
												`amount`,
												`tag`,
												`diffonexch`,
												`settled`
											)
											VALUES(".$_GET['AllocTrans'].",
											2,
											0,
											0,'"
											.date("Y-m-d")."',
											null,"
											.$StockTax.","
											.$StockAmo.",'"
											.$_POST['UnitsTag']."',"
											.round($SettleAmo-$StockTax-$StockAmo,POI).",
											0)";
			$result=DB_query($SQL);

			$stkactid=DB_Last_Insert_ID($db,'stockaccounts','stkactid');
			foreach ($_POST as $key => $value) {
				//prnMsg($key);
				if (mb_strpos($key,'Number')!==false) {				      
					$LineID = mb_substr($key,mb_strpos($key,'Number')+6);
				
					if ($_POST['stlBox'.$LineID]==1) {
						
						
					
						$SQL="UPDATE `stockmoves` SET   `stkactid`='".$stkactid."',`accountdate`='".date("Y-m-d")."',   `stled`=1  WHERE stkmoveno='".$_POST['stkMoveNo'.$LineID]."' ";
						$result=DB_query($SQL);
					}
				}
			}
			$SQL="INSERT INTO `stockmoves`(  	`connectid`,
												`itemsid`,
												`stockid`,
												`type`,
												`transno`,
												`loccode`,
												`stkactid`,
												`accountdate`,
												`trandate`,
												`userid`,
												`debtorno`,
												`branchcode`,
												`price`,
												`prd`,
												`reference`,
												`qty`,
												`issueqty`,
												`discountpercent`,
												`standardcost`,
												`show_on_inv_crds`,
												`issuetab`,
												`newqoh`,
												`newamount`,
												`hidemovt`,
												`narrative`,
												`stled`
											)
											VALUES(";
											
			$result= DB_Txn_Commit();
		}
	}



   //执行清算class="centre" >
   
   echo '<div>	<input type="hidden" name="TotalNumberOfAllocs" value="' . $Counter . '" />
			<br />
			<input type="submit" name=Serach" value="查询" />
			<input type="submit" name="VerifyAccount" value="对账计算汇总" />
			<input type="submit" name="CreateAccounts" value="产生对账单" />
		
			<input type="submit" name="Allocations" value="' . _('Process Allocations') . '" />
			';
	if ($_POST['CreateAccounts']){
		echo '<br/><br/><input type="submit" name="SaveAccounts" value="对账单确认" />';
	}
	echo'</div>';
}
if (isset($_POST['Allocations'])){
	prnMsg("执行清算");
	//得到差异编号
	 $SQL="SELECT `categoryid`,   `purchpricecode`  FROM `stockcategory` WHERE stocktype='B'";
	 //差异单号类别 46-48空  拟设采购46  销售47  
	 //插入stockmoves  数量0  价格为��异���额


}

	echo '</div>
		</form>';

	include('includes/footer.php');
	?>
