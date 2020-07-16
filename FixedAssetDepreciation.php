<?php
/* $Id: FixedAssetDepreciation.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-06-25 08:45:49 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-06-25 13:37:35
 */
include('includes/session.php');
$Title = '折旧提取';//_('Depreciation Extraction');
$ViewTopic = 'FixedAssets';
$BookMark = 'AssetDepreciation';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
		function sltcostit(obj){		
  			window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?cstit="+obj.value ;
 		}	
	  </script>';
	  /*
if (!isset($_POST['ERPPrd'])OR $_POST['ERPPrd']==''){
		$_POST["ERPPrd"]=$_SESSION['period'];//.'^'.$_SESSION['lastdate'];
	  }
	  */
if (!isset($_POST['UnitsTag'])){
		$_POST['UnitsTag']=1;
}
	  /*
if (!isset($_POST['periodrange']) OR $_POST['periodrange']==''){
     $_POST['periodrange']=0;		  	
	}
*/
if (!isset($_POST['query'])) {
   $_POST['query']=0;
}
$tag=$_SESSION['Tag'];
echo '<div class="centre">
	<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Fixed Asset Categories') . '" alt="" />' . ' ' . $Title . '
	</p>' ;
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />	  
	      <input type="hidden" name="query" value="' . $_POST['query'] . '" />';
echo '<div>';
$SQL="SELECT `periodno`, `lastdate_in_period` 
FROM `periods` 
WHERE periodno>=".$_SESSION['janr']." AND periodno<=".$_SESSION['period']."  AND  `lastdate_in_period`> (SELECT last_day(min(`datepurchased`)) FROM `fixedassets`)
AND periodno NOT IN (SELECT DISTINCT periodno FROM `fixedassettrans` WHERE `fixedassettranstype`='depn'AND `transtype`=44)" ;
 
$Result = DB_query($SQL);
if (DB_num_rows($Result)==0){
	prnMsg($_SESSION['lastdate'].'以前的折旧已经提取！','info');
	include('includes/footer.php');
	exit;
}
	echo '<table class="selection">';  	
	echo '<tr>
	          <td width="100">选择会计期间</td>
			  <td width="150"><select name="SelectPrd">';	

    while ($myrow=DB_fetch_array($Result)){

		if(!isset($_POST['SelectPrd']))
		$_POST['SelectPrd']=$myrow['periodno'];

			if($myrow['periodno'] == $_POST['SelectPrd']){
	
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}
	}
		echo '</select></td>';
	echo '<tr>
			<td>查询方式</td>
			<td>
				<input type="radio" name="query" value="0"  '.($_POST['query']==0 ? 'checked':"").' >'._('Default').'          
				<input type="radio" name="query" value="2"   '.($_POST['query']==1 ? 'checked':"").'  >'._('Total').'
            </td>
         </tr>';      
	echo '	</table>
		<br />';
		$prd=$_POST['SelectPrd'];//1+$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod'] ;
		$_POST['ProcessDate']=PeriodGetDate($_POST['SelectPrd']);	
		$sltprd1=$prd-(int)date("m",strtotime($_POST["ProcessDate"]))+1;
		$msg='';
		$SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);
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
			   SUM(CASE WHEN (fixedassettrans.periodno >=0 AND fixedassettrans.periodno <'" . $sltprd1 . "' AND fixedassettrans.     fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS costbfwd,
				SUM(CASE WHEN (fixedassettrans.periodno >=0 AND fixedassettrans.periodno <'" . $sltprd1 . "' AND fixedassettrans.fixedassettranstype='depn') THEN fixedassettrans.amount ELSE 0 END) AS depnbfwd,
				SUM(CASE WHEN (fixedassettrans.periodno >='".$sltprd1."' AND fixedassettrans.periodno<='" . $prd . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS costcfwd,
				SUM(CASE WHEN fixedassettrans.periodno >='".$sltprd1."' AND fixedassettrans.periodno<='" . $prd . "' AND fixedassettrans.fixedassettranstype='depn' THEN fixedassettrans.amount ELSE 0 END) AS depncfwd,
				SUM(CASE WHEN (fixedassettrans.periodno='" . $prd . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS mcost,
				SUM(CASE WHEN  fixedassettrans.periodno='" . $prd . "' AND fixedassettrans.fixedassettranstype='depn' THEN fixedassettrans.amount ELSE 0 END) AS mdepn,
				SUM(CASE WHEN fixedassettrans.periodno='" . $prd. "' AND fixedassettrans.fixedassettranstype='disposal' THEN fixedassettrans.amount ELSE 0 END) AS perioddisposal
			  
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
		$TotalCost =0;
		$TotalDepn =0;//上年计折旧

		$TotalNewAccumDepn =0;//本年累计折旧
		$TotalNewAccumCost=0;
		while ($AssetRow=DB_fetch_array($AssetsResult)) {
			$BookValueBfwd = $AssetRow['costbfwd'] - $AssetRow['depnbfwd']-$AssetRow['depncfwd']+$AssetRow['costcfwd'];
				//净值
				if ($AssetRow['depntype']==0){ //直线折旧striaght line depreciation
					$DepreciationType = _('SL');
					$NewDepreciation =round(( $AssetRow['costbfwd'] +$AssetRow['costcfwd'])* $AssetRow['depnrate']/100/12,2);
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
			 $AssetArr[$AssetRow['assetlocation']][1]+=$AssetRow['costbfwd']+$AssetRow['costcfwd'];
			 $DepnArr[$AssetRow['assetid']]=$NewDepreciation;
			$TotalCost +=$AssetRow['costbfwd'];//累计原值
			$TotalDepn +=$AssetRow['depnbfwd'];//上年计折旧

			$TotalNewAccumDepn +=$AssetRow['depncfwd'];//本年累计折旧
			$TotalNewAccumCost +=$AssetRow['costcfwd'];//本年原值
			//$TotalDepn +=$NewDepreciation;	//本月折旧*/
		}//while
		//var_dump($AssetArr);
      
		// $depnflg=GetSys('settleflag',$_POST['costitem'],explode('^',$_POST["ERPPrd"])[0],0)[0];//????
		 //下面读取凭证期末原值  折旧 年 月原值 折旧 
		 $sql="SELECT account,
					 round(sum(sumamount(amount,periodno,-1,$sltprd1-1)),2) Ending,
					 round(sum(sumamount(amount,periodno,$sltprd1,$prd)) ,2) TotalYer ,
					 round(sum(sumamount(amount,periodno,$prd,$prd)),2)  TotalMth					 
					 FROM gltrans
					  WHERE periodno<=$prd
					  AND (account LIKE '1601%' OR account LIKE '1602%') GROUP BY account";
		 $Result=DB_query($sql);
	      //prnMsg($TotalCost.'<br>'.$sql);
		 
		 while ($myrow=DB_fetch_array($Result)) {	
			$depnarr[substr($myrow['account'],0,4)]['Ending']+=$myrow['Ending'];
			$depnarr[substr($myrow['account'],0,4)]['TotalYer']+=$myrow['TotalYer'];
			$depnarr[substr($myrow['account'],0,4)]['TotalMth']+=$myrow['TotalMth'];
		}
		//print_r($depnarr);
		$TotalFixedCost=$TotalNewAccumCost +$TotalCost;
		$TotalGLCost=$depnarr['1601']['Ending']+$depnarr['1601']['TotalYer'];//原值

		$TotalFixedDepn=($TotalDepn+$TotalNewAccumDepn);
		$TotalGLDepn=-$depnarr['1602']['Ending']-$depnarr['1602']['TotalYer'];

			$msg='';
		
			if(round($TotalFixedCost-$TotalGLCost,2)!=0 ){
				$InputError==true;
				 $msg.='  原值汇总差：'.round($TotalFixedCost-$TotalGLCost,2)."  固定资产账簿金额[".$TotalFixedCost."] 会计账簿金额[".$TotalGLCost."]";
			}
			if(round($TotalFixedDepn-$TotalGLDepn,2)!=0){
				 $msg.='   <br/>累计折旧相差：'.round($TotalFixedDepn-$TotalGLDepn,2)."  固定资产账簿金额[".$TotalFixedDepn."] 会计账簿金额[".$TotalGLDepn."]";;
				 $InputError==true;
			}
			if ($msg!=''){
				prnMsg($msg.',修正后，再提取折旧！','warn');
			 }

			if ($SettleTab[1]!=0){
				prnMsg('当前期间折旧已经提取!','info');
				$DepnExtr=1;
			}
		
	
	echo '<div class="centre">
			<input type="submit" name="Search" value="折旧显示" />
			<input type="submit" name="crtExcel" value="创建Excel" />';

	echo'</div><br/>';
	if ($DepnExtr==1){
		include('includes/footer.php');
		exit;
	}
  	//提取折旧
if (isset($_POST['Search']) OR isset($_POST['CommitDepreciation'])) {
      
		//DB_data_seek($AssetsResult,0);	
		$TotalCost =0;
		$TotalAccumDepn=0;
		$TotalNewAccumDepn=0;
		$TotalDepn = 0;		
		$AssetCategoryDescription =0;	
		$TotalCategoryNewAccumDepn=0;	
		$TotalCategoryNewCost = 0;		
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
	    $GLTransArr=array();
		$RemarkArr=array();
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
					<td class="number">' . locale_number_format($val,POI) . '</td>
					';
					$TotalDepn+=round($val,2);
					$GLTransArr[$ky]-=$val;
					$RemarkArr[$ky]=date("Y-m",strtotime($_POST['ProcessDate']))."折旧提取";
				}elseif(strlen($ky)>4){                   
					$GLTransArr[$ky]+=$val;
					$RemarkArr[$ky].=$Remark;
					echo '<td >['.$ky.']'. $DepnCode[$ky].'</td>
					      <td class="number">' . locale_number_format($val,POI) . '</td>';

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
		
			$TransNo =GetTransNo( $prd, $db);			
			$TypeNo=GetTagTypeNo($tag,$prd,$db);//GetTypeNo (44,$prd,$db);
			$DbtAmo=0;
			$CdtAmo=0;
			$DepnAmo=0;
			//var_dump($GLTransArr);
			$result = DB_Txn_Commit();
			foreach($GLTransArr as $key=>$val){
				if (round($val,POI)!=0){
				$sql="INSERT INTO gltrans(`type`,
											transno,
											typeno,
											printno,
											prtchk,
											chequeno,
											trandate,
											periodno,
											account,
											narrative,
											amount,
											posted,
											jobref,
											tag,
											flg	)
									VALUES(44,
										'".$TransNo."',	
										'".$TypeNo."',							  
										0, 0,0,
										'".$_POST['ProcessDate']."', 
										'".$prd."',
										'".$key."',
										'".$RemarkArr[$key]."',
										'".$val."',
										0,
										0,
										'".$tag."',
										1 )";
				
					$result=DB_query($sql);
					//prnMsg($sql);
					if ($result){
						if ($val>0){
							$DbtAmo+=$val;
						}else{
							$CdtAmo+=abs($val);
						}
					}
				}
			}
			foreach ($DepnArr as $key => $value) {
				
				if (round($value,POI)!=0){
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
												'".$prd."',
												'".$TransNo."',
												'".$_POST['ProcessDate']."',
												$value)";
					$result=DB_query($sql);
					if ($result){
						$DepnAmo+=$value;
					}			
				}
			}
			if ($DbtAmo==$CdtAmo && $DbtAmo==$DepnAmo){
				$result= DB_Txn_Commit();
				$settle[1]=1;
				//prnMsg(implode(',',$settle));
				$sql="UPDATE `companies` SET settle='".implode(',',$settle)."' WHERE coycode=".$_POST['UnitsTag'];
				$result=DB_query($sql);
				if ($result){
					$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']=implode(',',$settle);
				}

			}
			prnMsg(_('Depreciation') . ' ' . $TransNo . ' ' . _('has been successfully entered'),'success');
			//unset($_POST['ProcessDate']);
			echo '<br /><a href="index.php">' ._('Return to main menu') . '</a>';
			echo '<meta http-equiv="refresh" content="0.3"/>';
		}	
	
		echo '<div class="centre">		
		<input type="submit" name="CommitDepreciation" value="'._('Commit Depreciation').'" />';
		
		echo'</div>';		 
	
	echo'<hr />';
		
     
	//	prnMsg('现在查询的是'.date('Y-m',strtotime(explode('^',$_POST["ERPPrd"])[1])),'info');
	echo '<br /><table  class="selection">';
	
	$Heading = '<tr>
					<th>' . _('Asset ID') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Date Purchased') . '</th>
					<th>原值</th>
					<th>' . _('Accum Depn') . '</th>
					<th>本年原值</th>
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
	$TotalNewAccumDepn=0;
	$TotalNewCost=0;
	$TotalDepn = 0;
	$TotalCategoryCost = 0;
	$TotalCategoryAccumDepn =0;
	$TotalCategoryNewCost = 0;//本年原值
	$TotalCategoryNewAccumDepn =0;//本年折旧
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
						<th class="number">' . locale_number_format($TotalCategoryNewCost ,POI) . '</th>
						<th class="number">' . locale_number_format($TotalCategoryNewAccumDepn,POI) . '</th>
					
						<th class="number">' . locale_number_format(($TotalCategoryCost-$TotalCategoryAccumDepn+$TotalCategoryNewCost-$TotalCategoryNewAccumDepn),POI) . '</th>
						<th colspan="2"></th>
						<th class="number">' . locale_number_format($TotalCategoryDepn,POI) . '</th>
						</tr>';
				$RowCounter = 0;
			}
			echo '<tr>
					<th colspan="11" align="left">' . $AssetRow['categorydescription']  . '</th>
				</tr>';
			$AssetCategoryDescription = $AssetRow['categorydescription'];
			$TotalCategoryCost = 0;
			$TotalCategoryAccumDepn =0;
			$TotalCategoryNewAccumDepn =0;
			$TotalCategoryNewCost = 0;
			$TotalCategoryDepn = 0;
		}
		$BookValueBfwd = $AssetRow['costbfwd'] - $AssetRow['depnbfwd']-$AssetRow['depncfwd']+$AssetRow['costcfwd'];
		//净值
		if ($AssetRow['depntype']==0){ //直线折旧striaght line depreciation
			$DepreciationType = _('SL');
			$NewDepreciation = round(($AssetRow['costbfwd'] +$AssetRow['costcfwd'])* $AssetRow['depnrate']/100/12,2);
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
			<td class="number">' . locale_number_format($AssetRow['costbfwd'],POI) . '</td>
			<td class="number">' . locale_number_format($AssetRow['depnbfwd'],POI) . '</td>
			<td class="number">' . locale_number_format($AssetRow['costcfwd'],POI) . '</td>
			<td class="number">' . locale_number_format($AssetRow['depncfwd'],POI) . '</td>
			<td class="number">' . locale_number_format($BookValueBfwd,POI) . '</td>
		
			<td align="center">' . $DepreciationType . '</td>
			<td class="number">' . $AssetRow['depnrate']  . '</td>
			<td class="number">' . locale_number_format($NewDepreciation ,POI) . '</td>
		</tr>';
		$TotalCategoryCost +=$AssetRow['costbfwd'];
		$TotalCategoryAccumDepn +=$AssetRow['depnbfwd'];
		$TotalCategoryNewAccumDepn +=$AssetRow['depncfwd'];
		$TotalCategoryNewCost +=$AssetRow['costcfwd'];

		$TotalCategoryDepn +=$NewDepreciation;
		$TotalCost +=$AssetRow['costbfwd'];
		$TotalAccumDepn +=$AssetRow['depnbfwd'];//上年累计折旧
		$TotalNewCost +=$AssetRow['costcfwd'];//本年
		$TotalNewAccumDepn +=$AssetRow['depncfwd'];//本年
		$TotalDepn +=$NewDepreciation;

		
	} //end loop around the assets to calculate depreciation for
	echo '<tr>
			<th colspan="3" align="right">' . _('Total for') . ' ' . $AssetCategoryDescription . ' </th>
			<th class="number">' . locale_number_format($TotalCategoryCost,POI) . '</th>
			<th class="number">' . locale_number_format($TotalCategoryAccumDepn,POI) . '</th>
			<th class="number">' . locale_number_format($TotalCategoryNewCost,POI) . '</th>
			<th class="number">' . locale_number_format($TotalCategoryNewAccumDepn,POI) . '</th>
			<th class="number">' . locale_number_format(($TotalCategoryCost-$TotalCategoryAccumDepn+$TotalCategoryNewCost-$TotalCategoryNewAccumDepn),POI) . '</th>
			<th colspan="2"></th>
			<th class="number">' . locale_number_format($TotalCategoryDepn,POI) . '</th>
		</tr>
		<tr>
			<th colspan="3" align="right">' . _('GRAND Total') . ' </th>
			<th class="number">' . locale_number_format($TotalCost,POI) . '</th>
			<th class="number">' . locale_number_format($TotalAccumDepn,POI) . '</th>
			<th class="number">' . locale_number_format($TotalNewCost,POI) . '</th>
			<th class="number">' . locale_number_format($TotalNewAccumDepn,POI) . '</th>
			<th class="number">' . locale_number_format(($TotalCost-$TotalAccumDepn+$TotalNewCost-$TotalNewAccumDepn),POI) . '</th>
			<th colspan="2"></th>
			<th class="number">' . locale_number_format($TotalDepn,POI) . '</th>
		</tr>';
	echo '</table>			
			<br />';
}elseif (isset($_POST['crtExcel']) ) {
		prnMsg(	'该功能暂时关闭！','info');
			echo'</div>
				</form>';
		include('includes/footer.php');
	
		exit;
	/*
			$SQL = TrialBalance('".$prd."','" .$periodrange."','".$query."','".$tag."','".$costitem."','".$_POST['account']."')";
	
			$AccountsResult = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));
			
			
				$ListCount=DB_num_rows($AccountsResult);
				
				
			if ($ListCount ==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
			}else{
			set_include_path(PATH_SEPARATOR .'Classes/PHPExcel' . PATH_SEPARATOR . get_include_path()); 
			require_once 'Classes/PHPExcel.php'; 
		require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls 
		//require_once 'Classes/PHPExcel/Writer/Excel2007.php'; // 用于 excel-2007 格式 
		
		$objExcel = new PHPExcel(); 
		$objWriter = new PHPExcel_Writer_Excel5($objExcel);     // 用于其他版本格式 
		// $objWriter = new PHPExcel_Writer_Excel2007($objExcel); // 用于 2007 格式 
	
		//设置文档基本属性 
		$objProps = $objExcel->getProperties(); 
		$objProps->setCreator("Zeal Li"); 
		$objProps->setLastModifiedBy("Zeal Li"); 
		$objProps->setTitle("Office XLS Test Document"); 
		$objProps->setSubject("Office XLS Test Document, Demo"); 
		$objProps->setDescription("Test document, generated by PHPExcel."); 
		$objProps->setKeywords("office excel PHPExcel"); 
		$objProps->setCategory("Test"); 
		//设置当前的sheet索引，用于后续的内容操作。 
	
		//缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0 
		$objExcel->setActiveSheetIndex(0); 
	
			$objActSheet = $objExcel->getActiveSheet(); 
	
		//设置当前活动sheet的名称 
	
			$objActSheet->setTitle('汇总表'); 
			$mulit_arr =  array(array(  '序号', _('Account'), _('Account Name'),'期初借方余额','期初贷方余额','本期借方发生额','本期贷方发生额',
			'本年借方累计','本年贷方累计','期末借方余额','期末贷方余���'));
	
	
		//设置单元格内容 
	
		//由PHPExcel根据传入���容自动判断单元格内容类型 
			$objActSheet->setCellValue('A1', 'ninsadasdcheng'); // 字符串内容 
			$objActSheet->setCellValue('A2', 26);            // 数值 
			$objActSheet->setCellValue('A3', true);          // 布尔值 
			$objActSheet->setCellValue('A4', '=SUM(A2:A2)'); // 公式 
	
		//显式指定内容类型 
		$objActSheet->setCellValueExplicit('A5', '847475847857487584', PHPExcel_Cell_DataType::TYPE_STRING); 
	
		//合并单元格 
			$objActSheet->mergeCells('B1:C22'); 
	
		//分离单元格 
			$objActSheet->unmergeCells('B1:C22'); 
		
		//设置单元格样式 
	
		//设置宽度 
		$objActSheet->getColumnDimension('B')->setAutoSize(true); 
		$objActSheet->getColumnDimension('A')->setWidth(30); 
		$objStyleA5 = $objActSheet->getStyle('A5'); 
		
	
	
	
		//都按原始内容全部显示出来。 
		$objStyleA5 ->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER); 
	
		//设置字体 
			$objFontA5 = $objStyleA5->getFont(); 
			$objFontA5->setName('Courier New'); 
			$objFontA5->setSize(10); 
			$objFontA5->setBold(true); 
			$objFontA5->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE); 
			$objFontA5->getColor()->setARGB('FF999999'); 
	
		//设置对齐方式 
			$objAlignA5 = $objStyleA5->getAlignment(); 
			$objAlignA5->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT); 
			$objAlignA5->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); 
	
		//设置边框 
		$objBorderA5 = $objStyleA5->getBorders(); 
		$objBorderA5->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 
		$objBorderA5->getTop()->getColor()->setARGB('FFFF0000'); // color 
		$objBorderA5->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 
		$objBorderA5->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 
		$objBorderA5->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN); 
	
		//设置填充颜色 
		$objFillA5 = $objStyleA5->getFill(); 
		$objFillA5->setFillType(PHPExcel_Style_Fill::FILL_SOLID); 
		$objFillA5->getStartColor()->setARGB('FFEEEEEE'); 
	
		//从指定的单元格复制样式信息. 
			$objActSheet->duplicateStyle($objStyleA5, 'B1:C22'); 
		
	
		//添加一个新的worksheet 
		$objExcel->createSheet(); 
		
		$objExcel->setActiveSheetIndex(1); 
		$objExcel->getSheet(1)->setTitle('科目汇总表'); 
		$objSheet1 = $objExcel->getActiveSheet(); 
	
		//设置当前活动sheet的名称 
		//    $objActSheet->setTitle('chengSheet'); 
		//写入多行数据
	
			$x=1;
		
			
				while ($myrow = DB_fetch_array($AccountsResult) ){
					
				
					array_push($mulit_arr,array($x,$myrow['account'], $myrow['accountname'],isZero(locale_number_format($myrow['debit1'],POI)),
						isZero(locale_number_format($myrow['credit1'],POI) ),isZero(locale_number_format($myrow['debit2'],POI)),
						isZero(locale_number_format($myrow['credit2'],POI) ),isZero(locale_number_format($myrow['debit3'],$_SESSION['CompanyRecord']['decimalplaces'])),
						isZero(locale_number_format($myrow['credit3'],$_SESSION['CompanyRecord']['decimalplaces']) ),isZero(locale_number_format($myrow['debit4'],$_SESSION['CompanyRecord']['decimalplaces'])),
						isZero(locale_number_format($myrow['credit4'],$_SESSION['CompanyRecord']['decimalplaces']) )));	  
				
					
			
			$x = $x + 1;
			}  
			
							// isZero(locale_number_format($amountD,$_SESSION['CompanyRecord']['decimalplaces']) ),$ye));	  
			//显式指定内容类型 
		//  $objActSheet1->setCellValueExplicit('A5', '847475847857487584', PHPExcel_Cell_DataType::TYPE_STRING); 
			$objSheet1->getColumnDimension('A')->setAutoSize(true); 
			$objSheet1->getColumnDimension('B')->setAutoSize(true); 
			$objSheet1->getColumnDimension('C')->setAutoSize(true); 
			$objSheet1->getColumnDimension('D')->setAutoSize(true); 
			$objSheet1->getColumnDimension('E')->setWidth(50);     
			$objSheet1->getColumnDimension('F')->setAutoSize(true); 
			$objSheet1->getColumnDimension('G')->setAutoSize(true); 
			$objSheet1->getColumnDimension('H')->setAutoSize(true);
				$objSheet1->getColumnDimension('I')->setAutoSize(true); 
			$objSheet1->getColumnDimension('J')->setAutoSize(true); 
			$objSheet1->getColumnDimension('K')->setAutoSize(true); 
		
		foreach($mulit_arr as $k=>$v){
			$k = $k+1;
			
			$objExcel->getActiveSheet()->setCellValue('A'.$k, $v[0]);
			$objExcel->getActiveSheet()->setCellValue('B'.$k, $v[1]);
			$objExcel->getActiveSheet()->setCellValue('C'.$k, $v[2]);
			$objExcel->getActiveSheet()->setCellValue('d'.$k, $v[3]);
				$objExcel->getActiveSheet()->setCellValue('e'.$k, $v[4]);
					$objExcel->getActiveSheet()->setCellValue('f'.$k, $v[5]);
						$objExcel->getActiveSheet()->setCellValue('g'.$k, $v[6]);
						$objExcel->getActiveSheet()->setCellValue('h'.$k, $v[7]);
							$objExcel->getActiveSheet()->setCellValue('i'.$k, $v[8]);
						$objExcel->getActiveSheet()->setCellValue('j'.$k, $v[9]);
							$objExcel->getActiveSheet()->setCellValue('k'.$k, $v[10]);
				
		}
	
	
	
		//写入类容
		$objwriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
		//$obwriter = PHPExcel_IOFactory::createWriter( $objExcel, 'Excel2007');
		
		//���护单元格 
		$objExcel->getSheet(1)->getProtection()->setSheet(true); 
		$objExcel->getSheet(1)->protectCells('A1:C22', 'PHPExcel'); 
	
		
		//$outputFileName = "NewCheng.xls"; 
		// $outputFileName = "companies/erprenyou/NewCheng.xls"; 
		$outputFileName = $_SESSION['reports_dir'] . '/AccountingSheets_' . Date('Y-m-d') .'.xls';
		//到文件 
		$objWriter->save($outputFileName); 
		echo '<p><a href="' .  $outputFileName . '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
	
	
		}
	 */
		 
}


echo'</div>
	</form>';

include('includes/footer.php');

?>