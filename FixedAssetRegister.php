<?php
/* $Id: FixedAssetRegister.php  2017-01-20 08:32:33 ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-06-10 08:31:04 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-09-30 16:39:31
 */
include ('includes/session.php');
require_once 'Classes/PHPExcel.php'; 
$Title = _('Fixed Asset Register');

$ViewTopic = 'FixedAssets';
$BookMark = 'AssetRegister';
$excelarr = '';
include ('includes/header.php');
if (!isset($_POST['selectperiod'])OR $_POST['selectperiod']==''){
		$_POST["selectperiod"]=$_SESSION['period'].'^'.$_SESSION['lastdate'];
  	}

if (!isset($_POST['query'])) {
   $_POST['query']=0;
}
if (!isset($_POST['costitem'])) {
   $_POST['costitem']=0;
}

if (!isset($_POST['costitem'])){
	$_POST['costitem']=0;
	}
	$_POST['ToDate']=explode('^',$_POST["selectperiod"])[1];
    $AllowUserEnteredProcessDate = true;
    //选择期间1 month periodno
	$sltprd1=explode('^',$_POST["selectperiod"])[0]-date('m',strtotime(explode('^',$_POST["selectperiod"])[1]))+1;  
	
	//$prdme=$prdm+date('m',strtotime(explode('^',$_POST["selectperiod"])[1]))-1;
	$prd=explode('^',$_POST["selectperiod"])[0]  ;	
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	$result = DB_query('SELECT categoryid,categorydescription FROM fixedassetcategories');
	echo '<form id="RegisterForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	      <input type="hidden" name="query" value="' . $_POST['query'] . '" />';
if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
  		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}else{
		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".(floor($_SESSION['startperiod']/12)*12-23 )."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}
 
   $result = DB_query($sql);
	echo '<table class="selection">';
  
	echo '<tr><td>' . _('Select Period To')  . '</td>
	<td ><select name="selectperiod" size="1" >';					
  
   while ($myrow=DB_fetch_array($result,$db)){	
   	
		if(isset($_POST['selectperiod']) AND $myrow['periodno'].'^'. $myrow['lastdate_in_period']==$_POST['selectperiod']){	
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
		echo   $myrow['periodno'].'^'. $myrow['lastdate_in_period'].'">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
	}  
    echo '</select></td><td></td></tr>';
	echo '		<tr>
			<td>查询方式</td>
				<td colspan="2">
				    <input type="radio" name="query" value="0"  '.($_POST['query']==0 ? 'checked':"").' >按设备类别          
					<input type="radio" name="query" value="1"   '.($_POST['query']==1 ? 'checked':"").'  >按部门
					<input type="radio" name="query" value="2"   '.($_POST['query']==2 ? 'checked':"").'  >按会计科目
        </td>
       </tr>';          
	 if (isset($_SESSION['Tag'])&&$_SESSION['Tag']>0){
  		 $sql="SELECT LEFT(code,1) code,description FROM  workcentres";
    	 $result = DB_query($sql);
  
		echo '<tr><td>选择核算单元:</td>
		<td colspan="2"><select name="costitem" size="1" >';
		if (!isset($_POST['costitem']) OR $_POST['costitem']==0){
			echo '<option selected="selected" value="0">全部 </option>';
		}else{
			echo '<option  value="0">全部 </option>';
		}
		
		while ($myrow=DB_fetch_array($result,$db)){	
   	
			if(isset($_POST['costitem']) AND $myrow['code']==$_POST['costitem']){	
				echo '<option selected="selected" value="';
			
			} else {
				echo '<option value ="';
			}
			echo   $myrow['code']. '">' . $myrow['description'] . '</option>';
		} 
	echo	'</select>
	        </td></tr>';
	 }
	echo '	</table>
		<br />';
	
	echo '<div class="centre">
		<input type="submit" name="submit" value="' . _('Show Assets') . '" />&nbsp;
		<input type="submit" name = "crtExcel" value="生成Excel" />
	</div>';	
// Reports being generated in HTML, and crtExcel/EXCEL format
if (isset($_POST['submit'])  OR isset($_POST['crtExcel'])) {

	$sql = "SELECT fixedassets.assetid,
					fixedassets.description,
					fixedassets.longdescription,
					fixedassets.assetcategoryid,
					fixedassets.serialno,					
					fixedassets.datepurchased,
					fixedassetlocations.parentlocationid,
					fixedassets.assetlocation,
					fixedassets.disposaldate ,
					fixedassetlocations.locationdescription,
					fixedassetcategories.categorydescription,
					fixedassets.qty,
					fixedassets.units, ";
	if ($_POST['query']==2) {
		$sql.="	fixedassetcategories.depnact,chartmaster.accountname, ";
	}	
		$sql.="SUM(CASE WHEN (fixedassettrans.periodno >=0 AND fixedassettrans.periodno <'" . $sltprd1 . "' AND fixedassettrans.     fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS costbfwd,
				SUM(CASE WHEN (fixedassettrans.periodno >=0 AND fixedassettrans.periodno <'" . $sltprd1 . "' AND fixedassettrans.fixedassettranstype='depn') THEN fixedassettrans.amount ELSE 0 END) AS depnbfwd,
				SUM(CASE WHEN (fixedassettrans.periodno >='".$sltprd1."' AND fixedassettrans.periodno<='" . $prd . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS costcfwd,
				SUM(CASE WHEN fixedassettrans.periodno >='".$sltprd1."' AND fixedassettrans.periodno<='" . $prd . "' AND fixedassettrans.fixedassettranstype='depn' THEN fixedassettrans.amount ELSE 0 END) AS depncfwd,
				SUM(CASE WHEN (fixedassettrans.periodno='" . $prd . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS mcost,
				SUM(CASE WHEN  fixedassettrans.periodno='" . $prd . "' AND fixedassettrans.fixedassettranstype='depn' THEN fixedassettrans.amount ELSE 0 END) AS mdepn,
				SUM(CASE WHEN fixedassettrans.periodno='" . $prd. "' AND fixedassettrans.fixedassettranstype='disposal' THEN fixedassettrans.amount ELSE 0 END) AS perioddisposal
			FROM fixedassets
			LEFT JOIN fixedassetcategories ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
			LEFT JOIN fixedassetlocations ON fixedassets.assetlocation=fixedassetlocations.locationid
			LEFT JOIN fixedassettrans ON fixedassets.assetid=fixedassettrans.assetid 
			LEFT JOIN chartmaster 
			ON fixedassetcategories.depnact=accountcode";
	if($_POST['costitem']!=0){
			$sql .=" WHERE fixedassets.assetcategoryid IN (SELECT `categoryid` FROM `fixedassetcategories` WHERE LEFT(categoryid,1)='".$_POST['costitem']."')";
	}		
	$sql.="	GROUP BY fixedassets.assetid,
					fixedassets.description,
					fixedassets.longdescription,
					fixedassets.assetcategoryid,
					fixedassets.serialno,
					fixedassetlocations.locationdescription,
					fixedassets.datepurchased,
					fixedassetlocations.parentlocationid,
					fixedassetlocations.locationdescription,
					fixedassetcategories.categorydescription,
					fixedassets.qty,
					fixedassets.units,   ";
					if ($_POST['query']==2) {
						$sql.="chartmaster.accountname, ";
					}
		$sql.="fixedassets.assetlocation ";
	$sql.=" ORDER BY assetcategoryid, ";
	if ($_POST['query']==1) {
		$sql.="fixedassetlocations.locationdescription, ";
	}elseif ($_POST['query']==2) {
		$sql.="fixedassetcategories.depnact,";
	}elseif ($_POST['query']==0) {
		$sql.="	fixedassetcategories.categorydescription, ";

	}
	$sql.=" fixedassets.longdescription, assetid";
	$result = DB_query($sql);

	if (isset($_POST['crtExcel'])) {
		
			$excelarr =array();//array('序号','资产ID','描述','部门','类别','购入日期','原值','累计折旧','本年增加原值','本年增加折旧','本月增加原值','本月增加折旧','净值','处置日期'));
	} else {
	
		echo '<br />
			<table width="80%" cellspacing="1" class="selection">';
	    $Heading='<tr>
				<th width="8" >序号</th>
				<th width="15" >' . _('Asset ID') . '</th>
				<th>' . _('Description') . '</th>
				<th>部门</th>
				<th>设备类别</th>
				<th>' . _('Date Acquired') . '</th>
				<th width="15" >数量</th>
				<th width="15" >单位</th>
				<th>年初<br/>原值</th>
				<th>年初<br/>累计折旧</th>				
				<th>本年<br/>原值</th>
				<th>本年<br/>折旧</th>
				<th>本月<br/>原值</th>
				<th>本月折旧</th>
			 	<th>' . _('NBV').'</th>
			</tr>';
	}
	$queryformat ='0';
	$Total_CostBfwd =0;
	$Total_CostCfwd =0;
	$Total_DepnBfwd =0;
	$Total_DepnCfwd =0;
	$Total_CostM =0;
	$Total_DepnM =0;
	$Total_NBV =0;
	$RowCounter = 0;

	$TotalCostBfwd =0;
	$TotalCostCfwd = 0;
	$TotalDepnBfwd = 0;
	$TotalDepnCfwd = 0;
	$TotalAdditions = 0;
	$TotalDepn = 0;
	$TotalDisposals = 0;
	$TotalNBV = 0;
    $k=0;
	$r=1;
	if (isset($_POST['submit']) ) {
	echo $Heading;

	}
	while ($myrow = DB_fetch_array($result)) {
		$RowCounter++;
		if ($RowCounter ==15){
			echo $Heading;
			$RowCounter =0;
		}
		if ($_POST['query']==1) {
			$queryrow=$myrow['locationdescription'];
		}elseif ($_POST['query']==2) {
			$queryrow=$myrow['chartmaster.accountname'];
		}elseif ($_POST['query']==0) {
			$queryrow=$myrow['categorydescription'];
		}	
		if ($queryformat != $queryrow OR $queryformat =='0'){
			if ($queryformat !='0'){ //then print totals
				if (isset($_POST['crtExcel']) ) {
				array_push(	$excelarr,array('','','',$queryformat,'','',locale_number_format($Total_CostBfwd,POI) ,
				locale_number_format($Total_DepnBfwd,POI) ,
				locale_number_format($Total_CostCfwd,POI) ,
				locale_number_format($Total_DepnCfwd,POI) ,
				locale_number_format($Total_CostM,POI) ,
				locale_number_format($Total_DepnM,POI) ,
				$Total_NBV,''));
				}else{	
				echo '<tr><th colspan="8" align="right">' . _('Total for') . ' ' .$queryformat . ' </th>
						<th class="number">' . locale_number_format($Total_CostBfwd,POI) . '</th>
						<th class="number">' . locale_number_format($Total_DepnBfwd,POI) . '</th>
						<th class="number">' . locale_number_format($Total_CostCfwd,POI) . '</th>
						<th class="number">' . locale_number_format($Total_DepnCfwd,POI) . '</th>
						<th class="number">' . locale_number_format($Total_CostM,POI) . '</th>
						<th class="number">' . locale_number_format($Total_DepnM,POI) . '</th>
						<th class="number">' . locale_number_format($Total_NBV,POI) . '</th>
						
						</tr>';
				}
				$RowCounter = 0;
				
			}
			if (isset($_POST['submit']) ) {
			echo '<tr>
					<th colspan="15" align="left">' .$queryformat . '</th>
				</tr>';
			}
			$queryformat = $queryrow;
			$Total_CostBfwd =0;
			$Total_CostCfwd =0;
			$Total_DepnBfwd =0;
			$Total_DepnCfwd =0;
			$Total_CostM =0;
			$Total_DepnM =0;
			$Total_NBV =0;
		}
	  			$CostCfwd = $myrow['costcfwd'] + $myrow['costbfwd'];
				$AccumDepnCfwd = $myrow['depncfwd'] + $myrow['depnbfwd'];
		
         if (isset($_POST['crtExcel'])) {
		
			array_push($excelarr,array( $r, $myrow['assetid'], $myrow['longdescription'],
				$myrow['locationdescription'], $myrow['categorydescription'],
				ConvertSQLDate($myrow['datepurchased']) , locale_number_format($myrow['qty'], $myrow['devimalplaces']) ,$myrow['units']  ,locale_number_format($myrow['costbfwd'], POI) ,
				locale_number_format($myrow['depnbfwd'], POI) ,locale_number_format($myrow['costcfwd'], POI) ,locale_number_format($myrow['depncfwd'], POI) ,locale_number_format($myrow['mcost'], POI) ,locale_number_format($myrow['mdepn'], POI) ,locale_number_format($CostCfwd - $AccumDepnCfwd, POI) ,$myrow['disposaldate']));
			

		 } else {
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k++;
				}	
				echo '  <td style="vertical-align:top ">' . $r . '</td>
						<td style="vertical-align:top ">' . $myrow['assetid'] . '</td>
						<td style="vertical-align:top">' . $myrow['longdescription'] . '</td>
						<td>' . $myrow['locationdescription'] . '<br />';
			/*	Not reworked yet
			 * for ($i = 1;$i < sizeOf($Ancestors) - 1;$i++) {
					for ($j = 0;$j < $i;$j++) {
						echo '&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					echo '|_' . $Ancestors[$i] . '<br />';
				}
			*/
				echo '</td>
				    <td>' . $myrow['categorydescription'] . '</td>
					<td style="vertical-align:top" title="处置日期：'. ConvertSQLDate($myrow['disposaldate'] ).'">' . ConvertSQLDate($myrow['datepurchased']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['qty'], $myrow['devimalplaces']) . '</td>
					<td style="vertical-align:top">' . $myrow['units'] . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['costbfwd'], POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['depnbfwd'], POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['costcfwd'], POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['depncfwd'], POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['mcost'] , POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['mdepn'] , POI) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($CostCfwd - $AccumDepnCfwd, POI) . '</td>
				
			
				</tr>';
			}
		
			$Total_CostBfwd +=$myrow['costbfwd'];
			$Total_CostCfwd += $myrow['costcfwd'];
			$Total_DepnBfwd += $myrow['depnbfwd'];
			$Total_DepnCfwd +=$myrow['depncfwd'];
			$Total_CostM += $myrow['mcost'];
			$Total_DepnM += $myrow['mdepn'];
			$Total_NBV += ($CostCfwd - $AccumDepnCfwd);
		$TotalCostBfwd +=$myrow['costbfwd'];
		$TotalCostCfwd += $myrow['costcfwd'];
		$TotalDepnBfwd += $myrow['depnbfwd'];
		$TotalDepnCfwd +=$myrow['depncfwd'];
		$TotalCostM += $myrow['mcost'];
		$TotalDepnM += $myrow['mdepn'];
		$TotalNBV += ($CostCfwd - $AccumDepnCfwd);
		$CostCfwd = 0;
		$AccumDepnCfwd = 0;
		$queryformat =='0';
		$r++;
	}

	if (isset($_POST['crtExcel'])) {
		//var_dump($excelarr);
		array_push($excelarr,array('','','','','','','','',$TotalCostBfwd,$TotalDepnBfwd,$TotalCostCfwd,$TotalDepnCfwd,$TotalCostM,$TotalDepnM,$TotalNBV,''));
			// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("webERP")
										->setLastModifiedBy("webERP")
										->setTitle("Petty Cash Expenses Analysis")
										->setSubject("Petty Cash Expenses Analysis")
										->setDescription("Petty Cash Expenses Analysis")
										->setKeywords("")
										->setCategory("");
		$objPHPExcel->getActiveSheet()->mergeCells('A1:P1');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '固定资产账簿');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
								
		$objPHPExcel->getActiveSheet()->mergeCells('A2:P2');

		
		$objPHPExcel->getActiveSheet()->setCellValue('A3', '编制单位:'.$_SESSION['CompanyRecord']['coyname']);
		$objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
		$objPHPExcel->getActiveSheet()->setCellValue('D3', '日期:'.explode('^',$_POST["selectperiod"])[1]);
		
		$objPHPExcel->getActiveSheet()->setCellValue('N3', '单位:元');
		$styleThinBlackBorderOutline = array(
			'borders' => array (
				
			'outline' => array (
					'style' => PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
				//  'style' => PHPExcel_Style_Border::BORDER_THICK, // 另一种样式
					'color' => array ('argb' => 'FF000000'),          //设置border颜色
			), 
			),);  
		$styleBorderR= array(
			'borders' => array (
				'right'     => array (
								'style' => PHPExcel_Style_Border::BORDER_THIN
						)   ,
			),);  
	
		$objPHPExcel->getActiveSheet()->getStyle('1')->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('I:O')->getNumberFormat()->setFormatCode('#,###');
		$objPHPExcel->getActiveSheet()->setCellValue('A4', '序号');
		$objPHPExcel->getActiveSheet()->setCellValue('B4', '资产ID');
		$objPHPExcel->getActiveSheet()->setCellValue('C4', '描述');
		$objPHPExcel->getActiveSheet()->setCellValue('D4', '部门');
		$objPHPExcel->getActiveSheet()->setCellValue('E4', '类别');
		$objPHPExcel->getActiveSheet()->setCellValue('F4', '购置日期');
		$objPHPExcel->getActiveSheet()->setCellValue('G4', '数量');
		$objPHPExcel->getActiveSheet()->setCellValue('H4', '单位');
		$objPHPExcel->getActiveSheet()->setCellValue('I4', '原值');
		$objPHPExcel->getActiveSheet()->setCellValue('J4', '累计折旧');
		$objPHPExcel->getActiveSheet()->setCellValue('K4', '本年增加原值');
		$objPHPExcel->getActiveSheet()->setCellValue('L4', '本年增加折旧');
		$objPHPExcel->getActiveSheet()->setCellValue('M4' ,'本月增加原值');
		$objPHPExcel->getActiveSheet()->setCellValue('N4', '本月增加折旧');
		$objPHPExcel->getActiveSheet()->setCellValue('O4', '净值');
		$objPHPExcel->getActiveSheet()->setCellValue('P4' ,'处置日期');	
		$i = 5;
		
		foreach($excelarr as $val) {
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $val[0]);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $val[1]);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $val[2]);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i,  $val[3]);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i,  $val[4]);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i,  $val[5]);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i,  $val[6]);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i,  $val[7]);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i,  $val[8]);
			$objPHPExcel->getActiveSheet()->setCellValue('J'.$i,  $val[9]);
			$objPHPExcel->getActiveSheet()->setCellValue('K'.$i,  $val[10]);
			$objPHPExcel->getActiveSheet()->setCellValue('L'.$i,  $val[11]);	
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i,  $val[12]);
			$objPHPExcel->getActiveSheet()->setCellValue('N'.$i,  $val[13]);
			$objPHPExcel->getActiveSheet()->setCellValue('O'.$i,  $val[14]);
			$objPHPExcel->getActiveSheet()->setCellValue('P'.$i,  $val[15]);	
		}			
		// Freeze panes // $objPHPExcel->getActiveSheet()->freezePane('E2');
		// Auto Size columns
		foreach(range('A','P') as $columnID) {
			$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
				->setAutoSize(true);
		}				
	
		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
		$objWriter->setIncludeCharts(TRUE);
		$RootPath=dirname(__FILE__ ) ;
		$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/固定资产账簿_'.explode('^',$_POST["selectperiod"])[1].'.xlsx';
		$objWriter->save($RootPath.'/'.$outputFileName);
		echo '<p><a href="'. $outputFileName. '">' . _('click here') . '</a> 下载文件<br />';
	} else {
		//Total Values
		echo '<tr><th style="vertical-align:top" colspan="8">' . _('TOTAL') . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalCostBfwd, POI) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalDepnBfwd, POI) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalCostCfwd, POI) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalDepnCfwd, POI) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalCostM, POI) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalDepnM, POI) . '</th>';
		
		echo '<th style="text-align:right">' . locale_number_format($TotalNBV, POI) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format('', POI) . '</th></tr>';
		echo '</table>';

     
        echo '<input type="hidden" name="ToDate" value="' . $_POST['ToDate'] . '" />';
     
        echo '<input type="hidden" name="AssetID" value="' . $_POST['AssetID'] . '" />';
    
	}
} //end


  echo' </div>
	</form>';
include ('includes/footer.php');
function PDFPageHeader (){
	global $PageNumber,
				$pdf,
				$XPos,
				$YPos,
				$Page_Height,
				$Page_Width,
				$Top_Margin,
				$Bottom_Margin,
				$FontSize,
				$Left_Margin,
				$Right_Margin,
				$line_height,
				$AssetDescription,
				$AssetCategory;

	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;
	$XPos=0;
	$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+20,$YPos-50,0,60);



	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos,240,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*1),240,$FontSize, _('Asset Category ').' ' . $AssetCategory );
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*2),240,$FontSize, _('Asset Location ').' ' . $_POST['AssetLocation'] );
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*3),240,$FontSize, _('Asset ID').': ' . $AssetDescription);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*4),240,$FontSize, _('From').': ' . $_POST['FromDate']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*5),240,$FontSize, _('To').': ' . $_POST['ToDate']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*7),240,$FontSize, _('Page'). ' ' . $PageNumber);

	$YPos -= 60;

	$YPos -=2*$line_height;
	//Note, this is ok for multilang as this is the value of a Select, text in option is different

	$YPos -=(2*$line_height);

	/*Draw a rectangle to put the headings in     */
	$YTopLeft=$YPos+$line_height;
	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
	$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
	$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

	/*set up the headings */
	$FontSize=10;
	$XPos = $Left_Margin+1;
	$YPos -=(0.8*$line_height);
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,30,$FontSize,  _('Asset'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+30,$YPos,150,$FontSize,  _('Description'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+180,$YPos,40,$FontSize,  _('Serial No.'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+220,$YPos,50,$FontSize,  _('Purchased'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+270,$YPos,70,$FontSize,  _('Cost B/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+340,$YPos,70,$FontSize,  _('Depn B/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+410,$YPos,70,$FontSize,  _('Additions'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+480,$YPos,70,$FontSize,  _('Depreciation'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+550,$YPos,70,$FontSize,  _('Cost C/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+620,$YPos,70,$FontSize,  _('Depn C/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+690,$YPos,70,$FontSize,  _('Net Book Value'), 'centre');
	//$LeftOvers = $pdf->addTextWrap($XPos+760,$YPos,70,$FontSize,  _('Disposal Proceeds'), 'centre');

	$pdf->line($Left_Margin, $YTopLeft,$Page_Width-$Right_Margin, $YTopLeft);
	$pdf->line($Left_Margin, $YTopLeft,$Left_Margin, $Bottom_Margin);
	$pdf->line($Left_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $Bottom_Margin);
	$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $YTopLeft);

	$FontSize=8;
	$YPos -= (1.5 * $line_height);

	$PageNumber++;
}

?>