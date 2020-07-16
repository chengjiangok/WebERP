<?php

/* $Id: StockCheck.php 6962 2014-11-06 02:59:12Z tehonu $*/
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
echo'<script type="text/javascript">
	function onLocations(s){      
	
		var jsn=document.getElementById("StockCategoryJson").value;	
	
		var selectobj=document.getElementById("Categories");
		var obj= JSON.parse(jsn);
		var temp = []; 	
		var objloc=[];
		selectobj.options.length=0; 
				
		console.log(jsn);
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
			console.log(jsonloc);
	}
</script>';

$Title='房屋管理';
include('includes/header.php');



//	$StockCategoryJson=json_encode($StockCategory,JSON_UNESCAPED_UNICODE);	 
	$flagtxt=[0=>"待盘点",1=>"盘点",2=>"批准"];
if(!isset($_POST['ExportExcel'])) {
	echo  ' <input type="hidden" id="StockCategoryJson" name="StockCategoryJson" value=' . $StockCategoryJson . ' />';
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="'
		. _('print') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="BatchID" value="' . $_POST['BatchID'] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<td>选择项目:</td>
			<td><select name="Location"  id="Location" onChange="onLocations( this )" >';
	$sql = "SELECT `code`, `location`, `description`, `capacity`, `overheadperhour`, `overheadrecoveryact`, `setuphrs` FROM `workcentres` 
		 	INNER JOIN locationusers ON locationusers.loccode=location AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1";
	$LocnResult=DB_query($sql);

	while ($myrow=DB_fetch_array($LocnResult)){
		if (!isset($_POST['Location'])){
			$_POST['Location']=$myrow['loccode'] ;
		}
		if ($myrow['loccode']==$_POST['Location']){
			echo '<option selected="selected" value="' . $myrow['location'] . '">' . $myrow['location'] . '</option>';
		} else {
			echo '<option value="' . $myrow['location'] . '">' . $myrow['location'] . '</option>';
		}
			
	}
	echo '</select>
		</td>
		</tr>
			<tr>
				<td>选择栋:</td>
				<td><select  name="Categories"  id="Categories" >';

	for( $i=1;$i<50; $i++){
		
		
			if (isset($_POST['Categories']) ){//AND in_array($row['id'], $_POST['Categories'])) {
				echo '<option selected="selected" value="' . $i . '">' . $i.'-栋</option>';
			} else {
				echo '<option value="'  . $i . '">' . $i.'-栋</option>';
			}
		
	}
	
	echo '</select>
			</td>
		</tr>		<tr>
		<td>选择楼层:</td>
		<td><select  name="Categories"  id="Categories" >';

for( $i=1;$i<50; $i++){


	if (isset($_POST['Categories']) ){//AND in_array($row['id'], $_POST['Categories'])) {
		echo '<option selected="selected" value="' . $i . '">' . $i.'-层</option>';
	} else {
		echo '<option value="'  . $i . '">' . $i.'-层</option>';
	}

}

echo '</select>
	</td>
</tr>';

	echo '<tr>
		<td>查询方式:</td>
		<td>
		  <input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' />完成  
		  <input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />盘点
		</td>
	   </tr>';
	echo '<tr>
			<td>非零数量的物料:</td>
			<td>';
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly'] == false){
			echo '<input type="checkbox" name="NonZerosOnly" value="false" />';
	} else {
			echo '<input type="checkbox" name="NonZerosOnly" value="true" />';
	}

	echo '</td>
		</tr>
		</table>
		<br />';
}


	
	$SQL="SELECT `stockid`, `stockno`, `HouseType`, `project`, `BuildNumber`, `Units`, `level`, `qty`, `InsideArea`, `ShareArea`, `BuiltArea`, `ActualArea`, `used` FROM `houseaccount` WHERE 1";
							// categoryid='" . $_POST['StkCat'] . "' 
	$result = DB_query($SQL);

if (isset($_POST['ExportExcel'])){

	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	$TitleData=array("Title"=>'对账单',"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal));	

	 $Header=array( '序号', '物料编码', '物料名称', '仓库', '物料组', '账面数', '盘点数', '摘要', '盘点日期' );	
	DB_data_seek($ResultCounts,0);	  
	ExportExcel($ResultCounts,$Header,$TitleData,$options);
}	
//if (isset($_POST['Search'])||isset($_GET['Del'])||isset($_GET['Action'])){
	 

	

	echo'<div class="centre">
			<input type="submit" name="Search" value="查询" />
		
			';
	if (isset($_POST['Search'])){
		echo '<input type="submit" name="ExportExcel" value="导出Excel" />';
	}
echo'</div>';

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
			<th>序号</th>
			<th>栋号</th>
			<th>楼层</th>
			<th>编号</th>
			<th>门牌号</th>
			<th>建筑面积</th>		
			<th>实际面积</th>
			<th>套内面积</th>
			<th>摘要</th>
			<th></th>
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
	echo'<td>'.($RowIndex+1).'</td>
		<td>' . $row['BuildNumber'] . '</td>
		<td>' . $row['level'] . '</td>			
		<td>' . $row['stockid'] . '</td>
		<td></td>
		<td>' . $row['BuiltArea'] . '</td>
		
		<td>' . $row['ActualArea'] . '</td>	
		<td>' . $row['InsideArea'] . '</td>
		<td>' . $row['remark'] . '</td>
		<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?AllocTrans='.$row['stockid'].'">编辑</a></td>													
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
		<td></td>
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

	echo'</div>
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
   *                           array  setWidth    设置宽度，例如['A' => 30, 'C' => 20]
   *                           bool   setBorder   设置单元格边框
   *                           array  mergeCells  设置合并单元格，例如['A1:J1' => 'A1:J1']
   *                           array  formula     设置公式，例如['F2' => '=IF(D2>0,E42/D2,0)']
   *                           array  format      设���格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        ��置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置后则文件保存到服务器，不通过浏览器下载
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
		$sheet->setCellValue('A'.$k, "公司名���:". (string)$titledata['coyname']); 
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
	//注意	createWriter($spreadsheet, 'Xls') //第二个���数首字母必须大写
	$writer->save('php://output'); 

}	

?>