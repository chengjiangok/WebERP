<?php
/* $Id: StockCounts.php 6942 2014-10-27 02:48:29Z daintree $*/

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup; 
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;

include('includes/session.php');

$Title = _('Stock Check Sheets Entry');

echo'<script type="text/javascript">
function inQty(p,r){
	//var  n=p.name.substring(9);	

	document.getElementById("QtyEdit_"+r).value=1;
}
function inRef(p,r){

		document.getElementById("RefEdit_"+r).value=1;
	//console.log(r);
}
function onStockCat(s){      
	
	var jsn=document.getElementById("StockCategoryJson").value;	

	var selectobj=document.getElementById("StkCat");
	var obj= JSON.parse(jsn);
	var temp = []; 	
	var objloc=[];
	selectobj.options.length=0; 
		
		for(var i=0; i<obj.length; i++)  
		{ 
			temp[i]= (function(n){				  
				if (Number(obj[n].loc)==s.value){		
				
					selectobj.options.add(new Option(obj[n].id+"-"+obj[n].des,obj[n].id));
					objloc[n]={
						id:obj[n].id,
						des:obj[n].des
					}
				}
			})(i);  
		}
		
		var jsonloc= JSON.stringify(objloc);	
}
</script>';
include('includes/header.php');
	$sql="SELECT `categoryid`, stockcategorylocation.`loccode`,locationname, `categorydescription` 
			FROM `stockcategorylocation` 
			LEFT JOIN locations ON locations.loccode=stockcategorylocation.loccode
			WHERE stockcategorylocation.loccode IN (SELECT `loccode` FROM `locationusers` WHERE  locationusers.userid = '".$_SESSION['UserID']."') 
			ORDER BY stockcategorylocation.`loccode`,`categoryid`";

	$resultCat = DB_query($sql);
	$StockCategory=[];
	while ($row=DB_fetch_array($resultCat)){
		$StockCategory[]=array("id"=>$row["categoryid"],"loc"=>$row["loccode"],"des"=>$row["categorydescription"]);
		$CategoryLocation[$row['categoryid']]=array($row['locationname'],$row['loccode']);

	}

	$StockCategoryJson=json_encode($StockCategory,JSON_UNESCAPED_UNICODE);	
	if (isset($_GET['Action'])){ 
		$_POST['BatchID']=$_GET['Action'];
	}
	$flagtxt=[0=>"待盘点",1=>"盘点",2=>"批准"];
	if (!isset($_POST['query']))
		$_POST['query']=0;
if(!isset($_POST['ExportExcel'])) {
	echo  ' <input type="hidden" id="StockCategoryJson" name="StockCategoryJson" value=' . $StockCategoryJson . ' />';
	echo '<form name="EnterCountsForm" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		
			<input type="hidden" name="BatchID" value="' . $_POST['BatchID'] . '" />';

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' .
		_('Inventory Adjustment') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<table class="selection">';
	echo '<tr>
			<td>' . _('For Inventory in Location') . ':</td>
			<td><select name="Location"  id="Location" onChange="onLocations( this )" >';
	$sql = "SELECT locations.loccode,
	               locationname 
				   FROM locations 
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			ORDER BY locationname";
	$LocnResult=DB_query($sql);

	while ($myrow=DB_fetch_array($LocnResult)){
		if (!isset($_POST['Location'])){
			$_POST['Location']=$myrow['loccode'] ;
		}
		if ($myrow['loccode']==$_POST['Location']){
			echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
			
	}
	echo '</select>
		</td>
		</tr>
			<tr>
				<td>选择存货分组:</td>
				<td><select  name="Categories"  id="Categories" >';

	foreach( $StockCategory as $row){
		
		if ($row['loc']==$_POST['Location']){
			if (isset($_POST['Categories']) AND in_array($row['id'], $_POST['Categories'])) {
				echo '<option selected="selected" value="' . $row['id'] . '">' . $row['id'].'-'. $row['des'] . '</option>';
			} else {
				echo '<option value="' . $row['id'] . '">' .$row['id'].'-'. $row['des'] . '</option>';
			}
		}
	}
	
	echo '</select>
			</td>
		</tr>';
	/*
	echo '<tr>
			<td>' . _('Show system quantity on sheets') . ':</td>
			<td>';

	if (isset($_POST['ShowInfo']) and $_POST['ShowInfo'] == false){
			echo '<input type="checkbox" name="ShowInfo" value="false" />';
	} else {
			echo '<input type="checkbox" name="ShowInfo" value="true" />';
	}
	echo '</td>
		</tr>';
*/
echo '<tr>
	<td>查询方式:</td>
	<td>
	  <input type="radio" name="query" value="0" '.($_POST['query'] == 0 ?'checked':''). ' />盘点
	  <input type="radio" name="query" value="1"  '.($_POST['query'] == 1?'checked ':''). ' />完成  
	';
   /*
	echo '<tr>
			<td>' . _('Only print items with non zero quantities') . ':</td>
			<td>';
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly'] == false){
			echo '<input type="checkbox" name="NonZerosOnly" value="false" />';
	} else {
			echo '<input type="checkbox" name="NonZerosOnly" value="true" />';
	}
*/
	echo '</td>
		</tr>
		</table>
		<br />';
	//if (isset($_POST['Search'])||isset($_GET['Del'])||isset($_GET['Action'])){
	if ($_POST['query']==0)
		$sql="SELECT `batchid`, `loccode`, `categoryid`, `stkcheckdate`,closed FROM `stockcheckfreeze` WHERE closed<2";
		else
	$sql="SELECT `batchid`, `loccode`, `categoryid`, `stkcheckdate`,closed FROM `stockcheckfreeze` WHERE closed=2";
		$result = DB_query($sql,'','',false,false);
	if (DB_error_no() !=0) {
		$Title = _('Stock Count Sheets - Problem Report');
		include('includes/header.php');
		prnMsg(_('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg(),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
				  echo '<br />' . $sql;
		}
		//include('includes/footer.php');
		//exit;
	}else{
		echo '<table class="selection">';
		echo '<tr>		
				<th colspan="7">盘点表</th>
		
			</tr>
			<tr>
				<th>序号</th>
				<th>表编号</th>
				<th>所属仓库</th>
				<th>所属分组</th>
				<th>统计</th>
				<th>状态</th>
				<th colspan="2"></th>
			</tr>';
		$RowCount=1;
		while ($StkRow = DB_fetch_array($result)) {
			echo '<tr>
					<td>' . $RowCount. '</td>
					<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" >' . $StkRow['batchid'] . '</td>
					<td>' . $StkRow['loccode'] . '</td>
					<td>' . $StkRow['categoryid'] . '</td>
					<td>' . $StkRow['cout'] . '</td>
					<td>' .$flagtxt[$StkRow['closed'] ]. '</td>
					<td>';
				if ($StkRow['closed']!=2)
					echo '<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?Action='.$StkRow['batchid'].'">盘点</a>';
				echo'</td>
				</tr>';
			$RowCount++;
		}
		echo '</table>';
	
	
   }
	echo'<div class="centre">
		<input type="submit" name="Search" value="查询检查表" />';
	if (isset($_GET['Action'])){
		echo '<input type="submit" name="ExportExcel" value="导出Excel" />';
	}
	echo'</div><br />';
	echo '<div class="center"><a href="' . $RootPath . '/StockCheck.php">创建新盘点表</a></div><br />';
}
if (isset($_GET['Action'])||isset($_POST['ExportExcel'])){

	
	$SQL="SELECT	`batchid`,
					`loccode`,
					 stockcounts.`categoryid`,
					 stockmaster.description,
					 stockcounts. `stockid`,
					`qoh`,
					`qtycounted`,
					`reference`,
					`edituser`,
					`auditer`,
					`stockcheckdate`,
					`auditdate`,
					`authorised`,
					`flag`
				FROM
					`stockcounts`
					LEFT JOIN stockmaster ON stockmaster.stockid=stockcounts.stockid
				WHERE
					batchid =".$_POST['BatchID']."
					ORDER BY stockcounts.stockid";
							// categoryid='" . $_POST['StkCat'] . "' 
	$ResultCounts = DB_query($SQL);
}
if(isset($_POST['ExportExcel'])) {
	
	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	$TitleData=array("Title"=>'对账单',"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal));	

	 $Header=array( '序号', '物料编码', '物料名称', '仓库', '物料组', '账面数', '盘点数', '摘要', '盘点日期' );	
	DB_data_seek($ResultCounts,0);	  
	ExportExcel($ResultCounts,$Header,$TitleData,$options);
}// end if producing a CSV
//if ($_GET['Action'] == 'Enter'){

if (isset($_POST['EnterCounts'])){

	$Added=0;
	$Counter = isset($_POST['RowCount'])?$_POST['RowCount'] : 10; // Arbitrary number of 10 hard coded as default as originally used - should there be a setting?
		for ($i=1;$i<=$Counter;$i++){
		$InputError =False; //always assume the best to start with

		$Quantity = 'Qty_' . $i;
		$BarCode = 'BarCode_' . $i;
		$StockID = 'StockID_' . $i;
		$Reference = 'Ref_' . $i;
		/*
		if (strlen($_POST[$BarCode])>0){
			$sql = "SELECT stockmaster.stockid
							FROM stockmaster
							WHERE stockmaster.barcode='". $_POST[$BarCode] ."'";

			$ErrMsg = _('Could not determine if the part being ordered was a kitset or not because');
			$DbgMsg = _('The sql that was used to determine if the part being ordered was a kitset or not was ');
			$KitResult = DB_query($sql,$ErrMsg,$DbgMsg);
			$myrow=DB_fetch_array($KitResult);

			$_POST[$StockID] = strtoupper($myrow['stockid']);
		}*/

		if (mb_strlen($_POST[$StockID])>0){
			if (!is_numeric($_POST[$Quantity])){
				$InputError=True;
			}

			if ($InputError==False){
				$Added++;
				if ( $_POST['QtyEdit_'.$i] ==1 || $_POST['RefEdit_'.$i] ==1){
					$sql = "UPDATE 	`stockcounts` SET ";
					if ( $_POST['QtyEdit_'.$i] ==1)
						$sql.=" 	`qtycounted` ='" . $_POST[$Quantity] . "', ";
					if ($_POST['RefEdit_'.$i] ==1)
						$sql.="	`reference` ='" . $_POST[$Reference] . "',";
						$sql.="	 `flag` =1 	WHERE 	`batchid` = ".$_POST['BatchID']." 	
											AND `stockid` ='" . $_POST[$StockID] . "' 	";

					$ErrMsg = _('The stock count line number') . ' ' . $i . ' ' . _('could not be entered because');
					//ECHO'-'.$sql  ."<BR/>";
					$Result = DB_query($sql,$ErrMsg);
				}
				
			}
		}//end
	
	} // end of loop
	$sql="SELECT count(*) cut FROM `stockcounts` WHERE batchid=". $_POST['BatchID'] ." AND flag=0 " ;
	
	$Result = DB_query($sql,$ErrMsg);
	$row=DB_fetch_assoc($row['cut']);
	if ($row['cut']==0){
		$sql="UPDATE  `stockcheckfreeze`  SET closed=1 WHERE batchid=". $_POST['BatchID'] ." " ;
		
		$Result = DB_query($sql,$ErrMsg);
	}
	prnMsg($Added . _(' Stock Counts Entered'), 'success' );
	unset($_POST['EnterCounts']);
} // end of if enter counts button hit

if (isset($_GET['Action']) ) {

	echo '<div class="centre">
			
			<input type="submit" name="EnterCounts" value="确认盘点数" />
		</div>';
	echo '<table cellpadding="2" class="selection">';
		echo '<tr>
				<th colspan="7">'.$_GET['Action'] . '号盘点表</th>
			</tr>
			<tr>	
				<th>序号</th>
				<th>' . _('Stock Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>账面数量</th>
				<th>' . _('Quantity') . '</th>
				<th>' . _('Reference') . '</th>
				<th></th>
				<th></th>
			</tr>';
			

		$RowCount=1;
	
		while ($StkRow = DB_fetch_array($ResultCounts)) {
			$_POST["Ref_' . $RowCount . '"] =$StkRow['reference'];
			$_POST["Qty_' . $RowCount . '"] =$StkRow['qtycounted'];
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			echo '
					<td>' . $RowCount . '</td>
					<td><input type="hidden" name="StockID_' . $RowCount . '" value="' . $StkRow['stockid'] . '" />' . $StkRow['stockid'] . '</td>
					<td>' . $StkRow['description'] . '</td>
					<td class="number">' .locale_number_format( $StkRow['qoh'],$StkRow['decimalplaces']). '
						<input type="hidden" name="Qoh_' . $RowCount . '" value="' . $StkRow['qoh'] . '" /></td>
					<td class="number">
						<input type="text" name="Qty_' . $RowCount . '" maxlength="10" size="10"  value="' . $_POST["Qty_' . $RowCount . '"] . '"  onChange="inQty(this,'.$RowCount.')" />
						<input type="hidden" name="QtyEdit_' . $RowCount . '"  id="QtyEdit_' . $RowCount . '" value="0"   /></td>
					<td><input type="text" name="Ref_' . $RowCount . '" maxlength="10" size="10"  value="' .$_POST["Ref_' . $RowCount . '"]  . '"  onChange="inRef(this,'.$RowCount.')"/>
						<input type="hidden" name="RefEdit_' . $RowCount . '"  id="RefEdit_' . $RowCount . '"  value="0" /></td>
					<td>'.$flagtxt[$StkRow['flag']] .'</td>
				</tr>';
			$RowCount++;
		}
		//<input type="checkbox" name="chkbx[]" id="chkbx[]" value="'.$RowIndex .'"   '.$checked.' >
	echo '</table>
			<br />
			<div class="centre">
				<input type="hidden" name="RowCount" value="' .$RowCount . '" />
				<input type="submit" name="EnterCounts" value="确认盘点数" />
			</div>';
			
} // there is a stock check to enter counts for
//END OF action=ENTER

if ($_GET['Action']=='View'){

	if (isset($_POST['DEL']) AND is_array($_POST['DEL']) ){
		foreach ($_POST['DEL'] as $id=>$val){
			if ($val == 'on'){
				$sql = "DELETE FROM stockcounts WHERE id='".$id."'";
				$ErrMsg = _('Failed to delete StockCount ID #').' '.$i;
				$EnterResult = DB_query($sql,$ErrMsg);
				prnMsg( _('Deleted Id #') . ' ' . $id, 'success');
			}
		}
	}

	//START OF action=VIEW
	$SQL = "select stockcounts.*,
					canupd from stockcounts
					INNER JOIN locationusers ON locationusers.loccode=stockcounts.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1";
	$result = DB_query($SQL);
	echo '<input type="hidden" name="Action" value="View" />';
	echo '<table cellpadding="2" class="selection">';
	echo '<tr>
			<th>' . _('Stock Code') . '</th>
			<th>' . _('Location') . '</th>
			<th>' . _('Qty Counted') . '</th>
			<th>' . _('Reference') . '</th>
			<th>' . _('Delete?') . '</th></tr>';
	while ($myrow=DB_fetch_array($result)){
		echo '<tr>
			<td>'.$myrow['stockid'].'</td>
			<td>'.$myrow['loccode'].'</td>
			<td>'.$myrow['qtycounted'].'</td>
			<td>'.$myrow['reference'].'</td>
			<td>';
		if ($myrow['canupd']==1) {
			echo '<input type="checkbox" name="DEL[' . $myrow['id'] . ']" maxlength="20" size="20" />';

		}
		echo '</td></tr>';

	}
	echo '</table><br /><div class="centre"><input type="submit" name="SubmitChanges" value="' . _('Save Changes') . '" /></div>';

//END OF action=VIEW
}

echo '</div>
      </form>';
include('includes/footer.php');

 
/**
   * Excel导出，TODO 可继续优化
   *
   * @param array  $Result
   * @param array  $header   导出文件名称
   * @param array  $TitleData "Title"=>'客户名单',
   * 						 
   * 						  "TitleDate"=>"2020-03-26",
   *                          "Compy"=>"华陆数控公司",
   *                          "Units"=>"元",
   *                           "k"=>3;
   * @param array  $options    操作选项，例如：
   *                           bool   print       设置打印格式
   *                           string freezePane  锁定行数，例如表头为第一行，则锁定表头输入A2
   *                           array  setARGB     设置背景色，例如['A1', 'C1']
   *                           array  setWidth    设���宽度，例如['A' => 30, 'C' => 20]
   *                           bool   setBorder   设置单元格边框
   *                           array  mergeCells  设置合并单元格，例如['A1:J1' => 'A1:J1']
   *                           array  formula     设置公式，例如['F2' => '=IF(D2>0,E42/D2,0)']
   *                           array  format      设���格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        ��置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置后则文件保存到服务��，不通过浏览器下载
   */	
  function ExportExcel($Result,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		$rowCnt=DB_num_rows($Result); 
		$k=$titledata['k'];
		$sheet = $spreadsheet->getActiveSheet();
		//设置sheet的名字  两种方法
		$sheet->setTitle($titledata['Title']);
		$spreadsheet->getActiveSheet()->setTitle($titledata['Title']);
			//设置默认文字居左，上下居中 
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_LEFT,
				'vertical'   => Alignment::VERTICAL_CENTER,
			],
		];
		$spreadsheet->getDefaultStyle()->applyFromArray($styleArray);
		//设置Excel Sheet 
		$activeSheet =  $spreadsheet->setActiveSheetIndex(0);

		//打印设置 
		if (isset($options['print']) && $options['print']) {
			//设置打印为A4效果 
			$activeSheet->getPageSetup()->setPaperSize(PageSetup:: PAPERSIZE_A4);
			//设置打印时边距 
			$pValue = 1 / 2.54;
			$activeSheet->getPageMargins()->setTop($pValue / 2);
			$activeSheet->getPageMargins()->setBottom($pValue * 2);
			$activeSheet->getPageMargins()->setLeft($pValue / 2);
			$activeSheet->getPageMargins()->setRight($pValue / 2);
		}
		//设置第一行行高为20pt

		$sheet->getRowDimension('1')->setRowHeight(25);
		$sheet->mergeCells('A1:'.Coordinate::stringFromColumnIndex($columnCnt).'1');
		//将A1至D1单元格设置成粗体
		//$sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex($columnCnt).'1')->getFont()->setBold(true);

		//将A1单元格设置成粗体，黑体，10号字
        $sheet->getStyle('A1')->getFont()->setBold(true)->setName('黑体')->setSize(14);

		$sheet->setCellValue('A1',  (string)$titledata['Title']); 
		$sheet->setCellValue('D2',  (string)$titledata['TitleDate']); 
		$sheet->setCellValue('A'.$k, "公司名称:". (string)$titledata['coyname']); 
		$sheet->setCellValue(Coordinate::stringFromColumnIndex($columnCnt).($k),  "单位：".(string)$titledata['Units']); 
		//设置默认行高
		$sheet->getDefaultRowDimension()->setRowHeight(20);
		
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER, //水平居中
				'vertical' => Alignment::VERTICAL_CENTER, //垂直居中
			],
		];
		$activeSheet->getStyle('A1')->applyFromArray($styleArray);
		$activeSheet->getStyle('A')->applyFromArray($styleArray);
		//$sheet->getStyle('A'.($k+1).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+$rowCnt))->applyFromArray($styleArray);
	
		$styleArray = [
			'borders' => [
				'outline' => [
					'borderStyle' => Border::BORDER_THICK,
					'color' => ['argb' => 'FFFF0000'],
				],
			],
		];
		$styleArray = [
			'borders' => [
				  'allBorders' => [
					'borderStyle' => Border::BORDER_THIN //细边框
				]
				]
		];
		$k++;	
		$activeSheet->getStyle('A'.(int)($k).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+$rowCnt))->applyFromArray($styleArray);
		  /* 设置宽度 */
		$activeSheet->getColumnDimension('B')->setWidth(15);		
		$activeSheet->getColumnDimension('C')->setWidth(30);
		$activeSheet->getColumnDimension('D')->setWidth(15);
		$activeSheet->getColumnDimension('H')->setWidth(30);
		$activeSheet->getColumnDimension('I')->setWidth(20);
		//		$activeSheet->getColumnDimension('D')->setAutoSize(true);
	
		//$activeSheet->getColumnDimension('F')->setWidth(15);
		//$activeSheet->getColumnDimension('G')->setWidth(25);
        //foreach ($options['setWidth'] as $swKey => $swItem) {
		//	$activeSheet->getColumnDimension($swKey)->setWidth($swItem);
	  
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);
			//表头
			$sheet->setCellValue($cellName.($k),  (string)$header[$_column-1]); 
		}
		$k++;
		$rw=$k-1;
	while ($row = DB_fetch_array($Result)){

		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);			
			if ($_column==1){
				//  序号列 
			
				$sheet->setCellValue($cellName.($k), $k-$rw); 
			}else{
			
				if ($_column==2){					
					$sheet->setCellValue($cellName.($k), (string)$row['stockid']);
				}elseif ($_column==3){					
					$sheet->setCellValue($cellName.($k), (string)$row['description']);
				}elseif ($_column==4){					
					$sheet->setCellValue($cellName.($k), (string)$row['loccode']);
				}elseif ($_column==5){					
					$sheet->setCellValue($cellName.($k), (string)$row['categoryid']);
				}elseif ($_column==6){					
					$sheet->setCellValue($cellName.($k), (float)$row['qoh']);
				}elseif ($_column==7){					
					$sheet->setCellValue($cellName.($k), (float)$row['qtycounted']);
				}elseif ($_column==8){					
					$sheet->setCellValue($cellName.($k), (string)$row['reference']);
				}elseif ($_column==9){					
					$sheet->setCellValue($cellName.($k), (string)$row['stockcheckdate']);
				}
			}
		
			if (!empty($row[$cellName-1])) {
				$isNull = false;
			}
		}
		$k++;

	}
	/*
     $amototal=json_decode($titledata['AmountTotal']);
	$sheet->setCellValue("A".($rowCnt+1+$k), ''); 	
	$sheet->setCellValue("B".($rowCnt+1+$k),"");				
	$sheet->setCellValue("C".($rowCnt+1+$k),"累计");
	$sheet->setCellValue("D".($rowCnt+1+$k), (string)$amototal[0]);
	$sheet->setCellValue("E".($rowCnt+1+$k), (string)$amototal[1]);
	$sheet->setCellValue("F".($rowCnt+1+$k), (string)$amototal[2]);
	$sheet->setCellValue("G".($rowCnt+1+$k), (string)$amototal[3]);
	$sheet->setCellValue("H".($rowCnt+1+$k), (string)$amototal[4]);
	*/


	
	//第一种保存方式
	/*	$writer = new Xlsx($spreadsheet);
	//保存的路径可自行设置
	$file_name = '../'.$file_name . ".xlsx";
	$writer->save($file_name);
	///第二种直接页面上显示下载
	*/

	$filename=$titledata['Title']. date('Y-m-d', time()).rand(1000, 9999).".xlsx";
	ob_end_clean();
	
	$ua = $_SERVER ["HTTP_USER_AGENT"];

	//$filename = basename ( $file );
	$encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
    $filename= iconv('UTF-8', $encode, $filename);
	$encoded_filename = rawurlencode ( $filename );
	header('Content-Type: application/vnd.ms-excel');
	if (preg_match ( "/MSIE/", $ua )) {
		header ( 'Content-Disposition: attachment; filename="' .convertEncoding($filename) . '"' );
	} else if (preg_match ( "/Firefox/", $ua )) {
		header ( "Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"' );
	} else {
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	}

	header('Cache-Control: max-age=0');

	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	//注意	createWriter($spreadsheet, 'Xls') //第二个参数首字母必须大写
	$writer->save('php://output'); 

}	
?>