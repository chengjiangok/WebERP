<?php
/* $Id:Z_FixedAssetsInitial.php  
*Z_ImportFixedAssets.php2017-01-17 13:09:50 ChengJiang $*/
/* Script to import fixed assets into a specified period*/

include('includes/session.php');
$Title ='固定资产初始设置';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme . 
		'/images/fixed_assets.png" title="初始固定资产资料" />	初始��固定资产资料</p>';
echo '<form enctype="multipart/form-data" action="Z_FixedAssetsInitial.php" method="post">';
		echo '<div class="centre">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
	echo'	<div class="page_help_text">功能简介:账目固定资产初始资料录入  </br>
						  <<<<<<<<该页面功能可以清除数据初始化，没有自动备份功能，不要尝试第二行按钮的操作>>>>>>>>>
						
						  
						  </div>';
  echo '<table class="selection">
		<tr>
			<th colspan="3">初始化设置</th>
		</tr>
		<tr>
		<td>单元分组:</td>
		<td >';
		  SelectUnitsTag(2);
  echo '</td>
			</tr>
		<tr>
			<td>选择账期间:</td>
			<td ><select name="StartPeriod" size="1" >';
			$y=date('Y');			 
			for ($i=($y-1);$i<=$y;$i++){
			  
				if ($_POST['StartPeriod']==$i){
					echo  '<option selected="selected" value="' . $i . '">' .$i. '年1月</option>';
				} else {
					echo  '<option value="' . $i . '">' . $i . '年1月</option>';
				}
			}
			echo '</select></td>
				</tr>';
  echo '<tr>
		  <td>数据录入期间:</td>
		  <td ><select name="StartMth" size="1" >';
		  $y=date('Y');
		  
		  for ($i=1;$i<=12;$i++){
		  
			  if ($_POST['StartMth']==$i){
				  echo  '<option selected="selected" value="' . $i . '">' .$i. '月</option>';
			  } else {
				  echo  '<option value="' . $i . '">' . $i . '月</option>';
			  }
		  }
		  echo '</select></td>
			  </tr>';

  echo '<tr>
		  <td>:</td>
		  <td colspan="2">
				  <input type="text" name="TransNo" value="1"   />
				
		  </td>
		  </tr>
		  </table>';
  //$Initial=explode(',',$_SESSION['AccountsInitial'][$_POST['UnitsTag']]);
		  $sql="show tables like 'fixedassets_initial'";
		  $result=DB_query($sql);
		  $tablerow=DB_fetch_assoc($result);
		  
		 if (!isset($tablerow)){
			   prnMsg('初始化系统的表fixedassets_initial不存在!','info');
			   include('includes/footer.php');
			   exit;
		 }	
		  echo'<br />
		  <div class="centre">';
		  echo'<input type="submit" name="Search" value="初始资料查询"/>
				<input type="submit" name="ClearTable" value="清空2表"/>
				<input type="submit" name="SearchTrans" value="提取折旧"/>
		
			
			  <br>';
		//、  if (isset($_POST['SearchTrans'])){
			 
			   if (isset($_POST['Search']) ){
				  echo '<input type="submit" name="UpdateAccounts" value="更新期初余额"/>';
			  }
			
			  echo '<br>';
		 	
	  echo'</div>';
if (isset($_POST['SearchTrans'])||isset($_POST['CommitDepreciation'])){
	//prnMsg('//  提取折旧');
	$msg='';
	$PeriodDate=date("Y-m-d",strtotime((string)($_POST['StartPeriod']).'-01-30'));
	$JanrPrd=DateGetPeriod($PeriodDate);

		//$JanrPrd=$_SESSION['janr']; 
		
		      
		     
		$PeriodDate=date("Y-m-t",strtotime((string)($_POST['StartPeriod']).'-'.$_POST['StartMth'].'-28'));
		//$t2 = strtotime(date('Y-m-t'));//获取本月末  方法2
		//$t2 = strtotime($y.'-'.($m).'-'.date('t'));//本月末
		$_POST['ProcessDate']=$PeriodDate;
		$ERPPrd=DateGetPeriod($PeriodDate); 	
		//$ERPPrd=1+$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod'];//explode('^',$_POST["ERPPrd"])[0];
		
		prnMsg("你现在计算的折旧月的月末是：".$PeriodDate,'info');
		//$SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);
		 
	    $AllowUserEnteredProcessDate = true;
	    $InputError = true; //always hope for the best
   
	   $sql="SELECT fixedassets.assetid,
			   fixedassets.description,
			   fixedassets.depntype,
			   fixedassets.depnrate,
			   fixedassets.datepurchased,
			   fixedassetlocations.accumdepnact,
			   fixedassetlocations.depnact,	
			   fixedassets.assetcategoryid,	
			   fixedassets.assetlocation	,
			   fixedassetcategories.categorydescription,
			   SUM(CASE WHEN fixedassettrans.fixedassettranstype='cost' THEN fixedassettrans.amount ELSE 0 END) AS costtotal,
			   SUM(CASE WHEN fixedassettrans.fixedassettranstype='depn'AND fixedassettrans.periodno<'".$JanrPrd."'  THEN fixedassettrans.amount ELSE 0 END) AS depnbfwd ,
			   SUM(CASE WHEN fixedassettrans.fixedassettranstype='depn' AND fixedassettrans.periodno>='".$JanrPrd."'   AND fixedassettrans.periodno<='".$ERPPrd."' THEN fixedassettrans.amount ELSE 0 END) AS depnbfwdy
		   FROM fixedassets
		   LEFT JOIN fixedassetcategories	ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
		   LEFT JOIN fixedassetlocations	ON fixedassets.assetlocation=fixedassetlocations.locationid
		   INNER JOIN fixedassettrans	ON fixedassets.assetid=fixedassettrans.assetid
		   WHERE  ";
				   
			$sql.=" fixedassets.datepurchased<='" . FormatDateForSQL($_POST['ProcessDate']) . "'
			   AND fixedassets.disposaldate = '0000-00-00'
		   GROUP BY fixedassets.assetid,
			   fixedassets.description,
			   fixedassets.depntype,
			   fixedassets.depnrate,
			   fixedassets.datepurchased,
			   fixedassetlocations.accumdepnact,
			   fixedassetlocations.depnact,
			   fixedassets.assetcategoryid,
			   fixedassetcategories.categorydescription,
			   fixedassets.assetlocation
		   ORDER BY assetcategoryid,fixedassetlocations.accumdepnact,
		   fixedassetlocations.depnact,assetid";
	   		//prnMsg($sql,'info');
			  $AssetsResult=DB_query($sql);
	
			$SQL="SELECT `locationid`, 
						`locationdescription`,
						`depnact`,
						b.accountname depnname,
						`accumdepnact`,
						c.accountname
			      FROM `fixedassetlocations` a
				  LEFT JOIN chartmaster b ON depnact=b.accountcode 
				  LEFT JOIN chartmaster c ON accumdepnact=c.accountcode";
			$Result=DB_query($SQL);
			  // $DepnCode=array();
			while($row=DB_fetch_array($Result)){
				$DepnCode[$row['locationid']]=$row['locationdescription'];
				$DepnCode[$row['depnact']]=$row['depnname'];
				$DepnCode[$row['accumdepnact']]=$row['accountname'];
			}
			//var_dump($DepnCode);
		$AssetArr=array(); //折旧  费用读取汇总
		$DepnArr=array();
		while ($AssetRow=DB_fetch_array($AssetsResult)) {
				$BookValueBfwd = $AssetRow['costtotal'] - $AssetRow['depnbfwd']-$AssetRow['depnbfwdy'];
				//净值
				if ($AssetRow['depntype']==0){ //直线折旧striaght line depreciation
					$DepreciationType = _('SL');
					$NewDepreciation =round( $AssetRow['costtotal'] * $AssetRow['depnrate']/100/12,2);
					if ($NewDepreciation > $BookValueBfwd &&$BookValueBfwd>0){
						$NewDepreciation = $BookValueBfwd;
					}elseif($BookValueBfwd<=0){
						$NewDepreciation =0;
					}
				} else { //价值递减折旧Diminishing value depreciation
					$DepreciationType = _('DV');
					$NewDepreciation = round($BookValueBfwd * $AssetRow['depnrate']/100/12,2);
				}
				if (Date1GreaterThanDate2($AssetRow['datepurchased'],$_POST['ProcessDate'])){
					/*Over-ride calculations as the asset was not purchased at the date of the calculation!! */
					$NewDepreciation =0;
				}elseif(date('Ym',strtotime($AssetRow['datepurchased']))==date('Ym',strtotime($_POST['ProcessDate']))){
					$NewDepreciation =0;
				}
		      //按部门汇总读取
			 $AssetArr[$AssetRow['assetlocation']][$AssetRow['accumdepnact']]+=$NewDepreciation;
			 $AssetArr[$AssetRow['assetlocation']][$AssetRow['depnact']]+=$NewDepreciation;
			 $AssetArr[$AssetRow['assetlocation']][1]+=$AssetRow['costtotal'];
			 $DepnArr[$AssetRow['assetid']]=$NewDepreciation;
			$TotalCost +=$AssetRow['costtotal'];//累计原值
			$TotalAccumDepn +=$AssetRow['depnbfwd'];//上年累计折旧
			$TotalAccumDepny +=$AssetRow['depnbfwdy'];//本年累计折旧
			//$TotalDepn +=$NewDepreciation;	//本月折旧*/
		}//while
		//var_dump($AssetArr);
      
		// $depnflg=GetSys('settleflag',$_POST['costitem'],explode('^',$_POST["ERPPrd"])[0],0)[0];//????
		 //下面读取凭证期末原值  折旧 年 月原值 折旧 
		 $sql="SELECT account,
					 round(sum(sumamount(amount,periodno,-1,$JanrPrd-1)),2) Ending,
					 round(sum(sumamount(amount,periodno,$JanrPrd,$ERPPrd)) ,2) TotalYer ,
					 round(sum(sumamount(amount,periodno,$ERPPrd,$ERPPrd)),2)  TotalMth					 
					 FROM gltrans
					  WHERE periodno<=$ERPPrd
					  AND (account LIKE '1601%' OR account LIKE '1602%') GROUP BY account";
		 $Result=DB_query($sql);
	 
		 
		 while ($myrow=DB_fetch_array($Result)) {	
			$depnarr[substr($myrow['account'],0,4)]['Ending']=$myrow['Ending'];
			$depnarr[substr($myrow['account'],0,4)]['TotalYer']=$myrow['TotalYer'];
			$depnarr[substr($myrow['account'],0,4)]['TotalMth']=$myrow['TotalMth'];
		}
	    $total=$TotalCost-$depnarr['1601']['Ending']-$depnarr['1601']['TotalYer'];//原址
	    $totaldepn=$TotalAccumDepn+$TotalAccumDepny+$depnarr['1602']['Ending']+$depnarr['1602']['TotalYer'];

			$msg='';
		
			if(round($total,2)!=0 ){
				$InputError==true;
				 $msg.='<br />原值汇总差：'.round($total,2);
			}
			if(round($totaldepn,2)!=0){
				 $msg.='<br />累计折旧相差：'.round($totaldepn,2);
				 $InputError==true;
			}
			if ($msg!=''){
				prnMsg($msg.',<br>修正后，再提���折旧！','warn');
			 }
            /*
			if ($SettleTab[1]!=0){
				prnMsg('当前期间折旧已经提取!','info');
				$DepnExtr=1;
			}*/
		//DB_data_seek($AssetsResult,0);	
		$TotalCost =0;
		$TotalAccumDepn=0;
		$TotalAccumDepny=0;
		$TotalDepn = 0;		
		$AssetCategoryDescription ='0';	
		$TotalCategoryAccumDepny=0;		
		$TotalCategoryCost = 0;	
		$TotalCategoryDepn = 0;
		$RowCounter = 0;
		$k=0;
		echo'<table  class="selection">
				<tr>
				<th>序号</th>
				<th>设备类型</th>				
				<th>原值</th>
				<th>折旧科目</th>
				<th>本月折旧</th>
				<th>费用科目</th>				
				<th>本月折旧</th>
			</tr>';
		
		$TotalCost=0;//累计原值
		$TotalDepn =0;	//本月折旧*/
		$RowIndex=1;
		//$GLTransArr=array();
		//$RemarkArr=array();
		//部门遍历
		
		foreach($AssetArr as $key=>$row){
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			echo '<td >'.$RowIndex.'</td>
					<td>['.$key .']'. $DepnCode[$key].'</td>
					<td>'.$row[1] .'</td>';
			$TotalCost+=round($row[1],2);
			$Remark= $DepnCode[$key].'原值:'.$row[1];
			foreach($row as $ky=>$val){
				if (substr($ky,0,4)=='1602'){
					echo '<td >['.$ky.']'. $DepnCode[$ky].'</td>
					<td class="number">' . locale_number_format($val,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']) . '</td>
					';
					$TotalDepn+=round($val,2);
					$GLTransArr[$ky]-=$val;
					$RemarkArr[$ky]="折旧提取";
				}elseif(strlen($ky)>4){                   
					$GLTransArr[$ky]+=$val;
					$RemarkArr[$ky].=$Remark;
					echo '<td >['.$ky.']'. $DepnCode[$ky].'</td>
							<td class="number">' . locale_number_format($val,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']) . '</td>';

				}
			}
				echo'</tr>';
				$RowIndex++;

		}
		echo '<tr>
			<th ></th>
			<th>合计</th>
			<th>'.$TotalCost .'</th>
			<th>合计</th>
			<th>'.$TotalDepn .'</th>
			<th>合计</th>
			<th>'.$TotalDepn .'</th>';
		echo '</table>';

		if (isset($_POST['CommitDepreciation'])){//} AND $InputError==false){
			//折旧提取
		
			$TransNo =$ERPPrd;//GetTransNo( $ERPPrd, $db);			
			$TypeNo=$ERPPrd;//GetTypeNo (44,$ERPPrd,$db);
			$DbtAmo=0;
			$CdtAmo=0;
			$DepnAmo=0;
			
				//initialize
			
				DB_Txn_Begin();				
			
				foreach ($DepnArr as $key => $value) {
				
			
					$sql="INSERT INTO fixedassettrans(assetid,
													transtype,
													fixedassettranstype,
													transdate,
													periodno,
													transno,
													inputdate,
													amount )
											VALUES('".$key."',
													44,
													'depn',
													'".$_POST['ProcessDate']."',
													'".$ERPPrd."',
													'".$TransNo."',
													'".$_POST['ProcessDate']."',
													$value)";
						$result=DB_query($sql);
							
			
				}
			 
				
			DB_Txn_Commit();			
			
			prnMsg(_('Depreciation') . ' ' . $TransNo . ' ' . _('has been successfully entered'),'success');
		
			echo '<br /><a href="index.php">' ._('Return to main menu') . '</a>';
		
		}	
		//if ($InputError==false){
		echo '<div class="centre">		
		<input type="submit" name="CommitDepreciation" value="'._('Commit Depreciation').'" />';
		
		echo'</div>';		 

	echo'<hr />';
	//-----------------
	//	prnMsg('现在查询的是'.date('Y-m',strtotime(explode('^',$_POST["ERPPrd"])[1])),'info');
	echo '<br /><table  class="selection">';
	
	$Heading = '<tr>
					<th>' . _('Asset ID') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Date Purchased') . '</th>
					<th>原值</th>
					<th>' . _('Accum Depn') . '</th>
					<th>本年折旧</th>
					<th>净值</th>
					<th>' .  _('Depn Type') . '</th>
					<th>' .  _('Depn Rate') . '</th>
					<th>本月折旧</th>
				</tr>';
	echo $Heading;

	$AssetCategoryDescription ='0';
	$TotalCost =0;
	$TotalAccumDepn=0;
	$TotalAccumDepny=0;
	
	$TotalDepn = 0;
	$TotalCategoryCost = 0;
	$TotalCategoryAccumDepn =0;
	$TotalCategoryDepn = 0;
	$RowCounter = 0;
	$k=0;
	DB_data_seek($AssetsResult,0);
	while ($AssetRow=DB_fetch_array($AssetsResult)) {
		if ($AssetCategoryDescription != $AssetRow['categorydescription'] OR $AssetCategoryDescription =='0'){
			if ($AssetCategoryDescription !='0'){ //then print totals
				echo '<tr><th colspan="3" align="right">' . _('Total for') . ' ' . $AssetCategoryDescription . ' </th>
						<th class="number">' . locale_number_format($TotalCategoryCost,POI) . '</th>
						<th class="number">' . locale_number_format($TotalCategoryAccumDepn,POI) . '</th>
						<th class="number">' . locale_number_format($TotalCategoryAccumDepny,POI) . '</th>
					
						<th class="number">' . locale_number_format(($TotalCategoryCost-$TotalCategoryAccumDepn-$TotalCategoryAccumDepny),POI) . '</th>
						<th colspan="2"></th>
						<th class="number">' . locale_number_format($TotalCategoryDepn,POI) . '</th>
						</tr>';
				$RowCounter = 0;
			}
			echo '<tr>
					<th colspan="10" align="left">' . $AssetRow['categorydescription']  . '</th>
				</tr>';
			$AssetCategoryDescription = $AssetRow['categorydescription'];
			$TotalCategoryCost = 0;
			$TotalCategoryAccumDepn =0;
			$TotalCategoryAccumDepny =0;
			$TotalCategoryDepn = 0;
		}
		$BookValueBfwd = $AssetRow['costtotal'] - $AssetRow['depnbfwd']-$AssetRow['depnbfwdy'];
		//净值
		if ($AssetRow['depntype']==0){ //直线折旧striaght line depreciation
			$DepreciationType = _('SL');
			$NewDepreciation = round($AssetRow['costtotal'] * $AssetRow['depnrate']/100/12,2);
			if ($NewDepreciation > $BookValueBfwd &&$BookValueBfwd>0){
				$NewDepreciation = $BookValueBfwd;
			}elseif($BookValueBfwd<=0){
				$NewDepreciation =0;
			}
		} else { //价值递减折旧Diminishing value depreciation
			$DepreciationType = _('DV');
			$NewDepreciation = round($BookValueBfwd * $AssetRow['depnrate']/100/12,2);
		}
		if (Date1GreaterThanDate2($AssetRow['datepurchased'],$_POST['ProcessDate'])){
			/*Over-ride calculations as the asset was not purchased at the date of the calculation!! */
			$NewDepreciation =0;
		}elseif(date('Ym',strtotime($AssetRow['datepurchased']))==date('Ym',strtotime($_POST['ProcessDate']))){
			$NewDepreciation =0;
		}
		$RowCounter++;
		if ($RowCounter ==15){
			echo $Heading;
			$RowCounter =0;
		}
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		echo'<td>' . $AssetRow['assetid'] . '</td>
			<td>' . $AssetRow['description'] . '</td>
			<td>' . ConvertSQLDate($AssetRow['datepurchased']) . '</td>
			<td class="number">' . locale_number_format($AssetRow['costtotal'],POI) . '</td>
			<td class="number">' . locale_number_format($AssetRow['depnbfwd'],POI) . '</td>
			<td class="number">' . locale_number_format($AssetRow['depnbfwdy'],POI) . '</td>
			<td class="number">' . locale_number_format($BookValueBfwd,POI) . '</td>
		
			<td align="center">' . $DepreciationType . '</td>
			<td class="number">' . $AssetRow['depnrate']  . '</td>
			<td class="number">' . locale_number_format($NewDepreciation ,POI) . '</td>
		</tr>';
		$TotalCategoryCost +=$AssetRow['costtotal'];
		$TotalCategoryAccumDepn +=$AssetRow['depnbfwd'];
		$TotalCategoryAccumDepny +=$AssetRow['depnbfwdy'];
		
		$TotalCategoryDepn +=$NewDepreciation;
		$TotalCost +=$AssetRow['costtotal'];
		$TotalAccumDepn +=$AssetRow['depnbfwd'];
		$TotalAccumDepny +=$AssetRow['depnbfwdy'];
		$TotalDepn +=$NewDepreciation;

		
	} //end loop around the assets to calculate depreciation for
	echo '<tr>
			<th colspan="3" align="right">' . _('Total for') . ' ' . $AssetCategoryDescription . ' </th>
			<th class="number">' . locale_number_format($TotalCategoryCost,POI) . '</th>
			<th class="number">' . locale_number_format($TotalCategoryAccumDepn,POI) . '</th>
			<th class="number">' . locale_number_format($TotalCategoryAccumDepny,POI) . '</th>
			
			<th class="number">' . locale_number_format(($TotalCategoryCost-$TotalCategoryAccumDepn-$TotalCategoryAccumDepny),POI) . '</th>
			<th colspan="2"></th>
			<th class="number">' . locale_number_format($TotalCategoryDepn,POI) . '</th>
		</tr>
		<tr>
			<th colspan="3" align="right">' . _('GRAND Total') . ' </th>
			<th class="number">' . locale_number_format($TotalCost,POI) . '</th>
			<th class="number">' . locale_number_format($TotalAccumDepn,POI) . '</th>
		<th class="number">' . locale_number_format($TotalAccumDepny,POI) . '</th>
			<th class="number">' . locale_number_format(($TotalCost-$TotalAccumDepn-$TotalAccumDepny),POI) . '</th>
			<th colspan="2"></th>
			<th class="number">' . locale_number_format($TotalDepn,POI) . '</th>
		</tr>';
	echo '</table>			
			<br />';
		
	
}
if (isset($_POST['ClearTable'])) {
		prnMsg('清除29表内容！');
	$ActTable=array("fixedassets","fixedassettrans");
	foreach ($ActTable as $val) {
	
			$SQL="TRUNCATE TABLE ".$val;
			$Result=DB_query($SQL);
			if($Resulat){
				$I++;
			}
			prnMsg($SQL);
		
		
	}
	
	

}elseif (isset($_POST['Search'])|| isset($_POST['UpdateAccounts']))  { 
	$PeriodDate=date("Y-m-d",strtotime((string)($_POST['StartPeriod']-1).'-12-30'));
	$PeriodNo=DateGetPeriod($PeriodDate);
	prnMsg($PeriodNo.'-'.$PeriodDate);
		//添加验证表存在
		$sql="show tables like 'fixedassets_initial'";
		$result=DB_query($sql);
		$tablerow=DB_fetch_assoc($result);
		$CostTotal=0;
		$DepnTotal=0;
		$PeriodDate=date("Y-m-d",strtotime((string)($_POST['StartPeriod']-1).'-12-30'));
		$PeriodNo=DateGetPeriod($PeriodDate);
	   if (isset($tablerow)){
			$sql="SELECT *    FROM `fixedassets_initial` ";
					//WHERE periodno=0";
			$result=DB_query($sql);
			
			echo'<table>
			        <tr>
							<th width="8" >序号</th>
							<th width="15" >' . _('Asset ID') . '</th>
							<th>' . _('Description') . '</th>
							<th>部门</th>
							<th>设备类别</th>
							<th>' . _('Date Acquired') . '</th>
							<th width="15" >数量</th>
							<th width="15" >单位</th>
							<th>年初<br/>原值</th>
							<th>���初<br/>累计折旧</th>				
							<th>月折旧</th>
							<th>期限</th>
						
							<th>备注</th>
						</tr>';
				$k = 0; //row counter to determine background colour
				$RowIndex =1;		
			while ($myrow = DB_fetch_array($result)  ){
				
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}	
				echo'
					<td >' . $myrow['id'] . '</td>
					<td >'.	$myrow['serialno'].'</td>
					<td>'. $myrow['description']. '</td>
					
					<td>'.$myrow['categoryid'].' '.$myrow['categoryname']. '</td>
					<td>'.$myrow['locationid'].' '.$myrow['assetlocation']. '</td>
					<td>'.$myrow['datepurchased']. '</td>
					<td>'.$myrow['qty']. '</td>
					<td>'.$myrow['units']. '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['cost'], POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['accumdepn'], POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['Y'] , POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['Z'] , POI) . '</td>
					<td >'.$myrow['remark'].'</td>
					</tr>';					
				$CostTotal+=$myrow['cost'];
				$DepnTotal+=$myrow['accumdepn'];	
				$DepnMthTotal+=$myrow['Y'];			
				$RowIndex = $RowIndex + 1;
			}
			echo '<tr>
					<th></th>
					<th colspan="7"></th>
					<th style="vertical-align:top" class="number">' . locale_number_format($CostTotal, POI) . '</th>
					<th style="vertical-align:top" class="number">' . locale_number_format($DepnTotal, POI) . '</th>
					<th style="vertical-align:top" class="number">' . locale_number_format($DepnMthTotal , POI) . '</th>
					<th style="vertical-align:top" class="number"></th>
					<th ></th>
				</tr>';
					echo '</table>';
		}else{
			prnMsg("科目初始化表 gltrans_initial不存在！",'info');
		}
		if(isset($_POST['UpdateAccounts']))  { 

			//initialize
		
			$Result =DB_Txn_Begin();				
			Db_data_seek($result,0);
			while ( $myrow =DB_fetch_array($result) ) {	
			
				$DepnType=0;
				$DepnRate=round(0.95/($myrow['Z']/12)*100,POI);
					$sql = "INSERT INTO fixedassets (description,
													longdescription,
													assetcategoryid,
													serialno,
													barcode,
													assetlocation,
													qty,
													units,
													cost,
													accumdepn,
													depntype,
													depnrate,
													datepurchased,
													remark)
									VALUES ('" . $myrow['description'] . "',
											'" . $myrow['description'] . "',
											'" . $myrow['categoryid']. "',
											'" . $myrow['serialno'] . "',
											'',
											'" . $myrow['locationid'] . "',
											'" .  $myrow['qty'] . "',
											'" . $myrow['units'] . "',
										
											'" .$myrow['cost'] . "',
											'" . $myrow['accumdepn']  . "',
											'" . $DepnType . "',
											'" . $DepnRate . "',
											'" . FormatDateForSQL($myrow['datepurchased']) . "',
											'" . $myrow['remark'] . "')";
		
					$ErrMsg =  _('The asset could not be added because');
					$DbgMsg = _('The SQL that was used to add the asset and failed was');
					//prnMsg($sql);
					$Result = DB_query($sql, $ErrMsg, $DbgMsg);
					if (DB_error_no() ==0) { //the insert of the new code worked so bang in the fixedassettrans records too
		
		
						$AssetID = DB_Last_Insert_ID($db, 'fixedassets','assetid');
						$TransNo=$AssetID;
						$Cost =$myrow['cost'];
						$AccumDepn =$myrow['accumdepn'];
						$sql = "INSERT INTO fixedassettrans ( assetid,
														transtype,
														transno,
														transdate,
														periodno,
														inputdate,
														fixedassettranstype,
														amount)
											VALUES ( '" . $AssetID . "',
													'49',
													'" . $TransNo . "',
													'" .$PeriodDate . "',
													'" . $PeriodNo . "',
													'" . Date('Y-m-d') . "',
													'cost',
													'" . $Cost . "')";
				
						
						$Result = DB_query($sql,$ErrMsg,$DbgMsg);
				
						$sql = "INSERT INTO fixedassettrans ( assetid,
															transtype,
															transno,
															transdate,
															periodno,
															inputdate,
															fixedassettranstype,
															amount)
											VALUES ( '" . $AssetID . "',
													'49',
													'" . $TransNo . "',
													'" .$PeriodDate . "',
													'" . $PeriodNo . "',
													'" . Date('Y-m-d') . "',
													'depn',
													'" . $AccumDepn . "')";
				
					
						$Result = DB_query($sql,$ErrMsg,$DbgMsg);
				
					
					}
					
				} // there were errors checking the row so no inserts
				$Row++;
			} //endwhile
			
			$Result =DB_Txn_Commit();
	
}



    echo'    </div>
		</form>';



include('includes/footer.php');
?>
