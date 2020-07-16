
<?php
/*  ReportFiles.php

 * @Author: ChengJiang 
 * @Date: 2018-12-08 11:10:32 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-12-09 11:32:19
 */

include ('includes/session.php');
require_once 'Classes/PHPExcel.php'; 
$Title ='报表档案';
$ViewTopic= 'ReportAccount';
$BookMark ='ReportAccount';

include('includes/SQL_CommonFunctions.inc');
include  ('includes/header.php');
if (!isset($_POST['selectprd'])OR $_POST['selectprd']==''){
		$_POST["selectprd"]=$_SESSION['period'];
 }
 
if(!isset($_POST['costitem'])){
	$_POST['costitem']=-1;

}
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		$Title . '" /> ' .// Icon title.
		$Title. '</p>';// Page title.
 // if (!isset($_POST['crtPDF'])){//} OR isset($_POST['ShowSheet'])){
//	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">';
 
echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if(isset($_POST['Import'])){
		$file_size=$_FILES['ImportFile']['size'];  
		if($file_size>2*1024*1024) {  
			prnMsg("文件过大，不能上传大于2M的文件",'info');  
				include('includes/footer.php');
				exit;
				$ReadTheFile ='No';
		 
		}  
	  
		$file_type=$_FILES['ImportFile']['type'];  
		//prnMsg($file_type);
		if($file_type!="application/octet-stream" && $file_type!="application/vnd.ms-excel" && $file_type!='application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {  
			prnMsg("文件类型只能为csv Excel格式",'info');   
				include('includes/footer.php');
				exit;
				$ReadTheFile ='No';
			 }  
	   //判断是否上传成功（是否使用post方式上传）  
		if(is_uploaded_file($_FILES['ImportFile']['tmp_name'])) {  
		  
			$uploaded_file=$_FILES['ImportFile']['tmp_name'];  
			$move_to_path='companies/'.$_SESSION['DatabaseName']."/ReportFile/";   
				
			$file_true_name=$_FILES['ImportFile']['name'];  
			$file_name=$_POST['selectyear'].'_'.$_POST['reporttype'].'_'.rand(1,100).substr($file_true_name,strrpos($file_true_name,"."));  
			 
			if(move_uploaded_file($uploaded_file,$move_to_path.$file_name)) { 
			
					$sql="INSERT INTO reportupload(   tag,
					                  periodyear,
														reporttype,
														reportid,
														urlfile,
														sheet,
														uploaddate,
														remark,
														flag)
														VALUES	(
														'".$_POST['unittag']."',
														'".$_POST['selectyear']."',
														'".$_POST['selectrpt']."',
														'".$_POST['reporttype']."',
														'".$file_name."',
														0,
														'". date('Y-m-d',time())."',
														'".$_FILES['ImportFile']['name']."',
														0)";
							$result=DB_query($sql);
		  
				   prnMsg( $file_name."上传成功!<br>");  
				   $_SESSION['ReportUpdate']=$file_name.'^'.$_POST['selectyear'].'^'.$_POST['selectrpt'].'^'.$_POST['reporttype'];
			} else {  
			   prnMsg("上传失败",'info');  
			}  
		} else {  
			  prnMsg("上传失败!",'info');  
		} 	
		//读取表

		
	} //end isset($_POST['Import']
	
	echo '<table class="selection">';
 	echo '<tr>
	        <td  colspan="2">' . _('Select Period To')  . '
			  <select name="selectyear">';	

	if ($_SESSION['period']-36>0){	  					
  		 $prdsql = "SELECT DISTINCT YEAR(lastdate_in_period) yy FROM periods where periodno>0 AND   periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}else{
		 $prdsql = "SELECT DISTINCT YEAR(lastdate_in_period) yy FROM periods where periodno>0 AND periodno  <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}
	$prdresult = DB_query($prdsql);
	 while ($myrow=DB_fetch_array($prdresult,$db)){	
		if(isset($_POST['selectyear']) AND $myrow['yy']==$_POST['selectyear']){	
			echo '<option selected="selected" value="';
		} else {
			echo '<option value ="';
		}
		echo   $myrow['yy']. '">' .$myrow['yy'] . '年</option>';
	}
   echo '<tr>
		  <td colspan="2">选择导入报表:
		     <select name="selectrpt" size="1" >';
 	     	$sql="SELECT reportid, reportname FROM reporttype WHERE rpttype=1 OR rpttype=52";
		    $Result = DB_query($sql);
		while ($myrow=DB_fetch_array($Result,$db)){	
	
			if(isset($_POST['selectrpt']) AND $myrow['reportid'].'^'. $myrow['reportname']==$_POST['selectrpt']){	
					
				echo '<option selected="selected" value="';
			
			} else {
				echo '<option value ="';
			}
			echo  $myrow['reportid'] .'^'. $myrow['reportname'] . '">' .  $myrow['reportname'] . '</option>';
		}
	echo	'</select>
	        </td>
			</tr>';
	if (isset($_SESSION['Tag'])){
     	  $sql="SELECT tagID, tagdatabase,tagdescription FROM unittag  WHERE flag=0 ORDER BY tagID ";
        $result = DB_query($sql);  
     echo '<tr>
     	    <td>单元分组
  			<select name="unittag" size="1" >';
  		$k=0;
  
 	 while ($myrow=DB_fetch_array($result,$db)){
	 	if(isset($_POST['unittag']) AND $myrow['tagID']==$_POST['unittag'] ){
			echo '<option selected="selected" value="';		 
		} else {		
			echo '<option value="';
		}
			echo $myrow['tagID'] . '">' .$myrow['tagdescription']  . '</option>'; 
	}
		 echo'</select>
		      </td>';
  }else{
	  $_POST['unittag']=-1;
	  $_POST['costitem']=-1;
  }    
	 
  
	  echo '<td >报表类别:<select name="reporttype" size="1" >';

	  $sql="SELECT reportid, reportname FROM reporttype WHERE rpttype=51";
	  $Result = DB_query($sql);
  while ($myrow=DB_fetch_array($Result,$db)){	

   if(isset($_POST['rpttype']) AND $myrow['reportid']==$_POST['rpttype']){	
		  
	  echo '<option selected="selected" value="';
  
  } else {
	  echo '<option value ="';
  }
	  echo  $myrow['reportid'] . '">' .  $myrow['reportname'] . '</option>';
  }
echo	'</select><a href="' . $RootPath . '/ReportType.php?typ=51"  target="_blank">类别添加</a>
	        </td>
			</tr>';
	echo' <tr>
	<td colspan="2">上传文件(Excel)
	     <input type="file" id="ImportFile"   title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile"> </td>

		   </tr>';
	echo '</table>
		<br />';

	echo '<div class="centre">
			<input type="submit" name="ShowSheet" value="显示查询" />
			<input type="submit" name="crtExcel" value="创建Excel" />
			<input type="submit" name="update" value="更新">
			<input type="submit" name="Import" value="上传">
		</div>';
  
if (isset($_POST['update'])) {
        if (isset($_SESSION['ReportUpdate'])){
		$move_to_path='companies/'.$_SESSION['DatabaseName']."/ReportFile/";   
		//下面代码为读取excel写入表
			require_once dirname(__FILE__) . '/Classes/PHPExcel.php';
			require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
			$selectarr=explode('^',$_SESSION['ReportUpdate']);
			$file_name=$selectarr[0];
			$tmp_file=$move_to_path.$file_name;	
			$file_true_name=$tmp_file;		
			if(substr($file_true_name,strrpos($file_true_name,"."))=='.xls'){
				
			   $reader = new PHPExcel_Reader_Excel5();					
			}elseif(substr($file_true_name,strrpos($file_true_name,"."))=='.xlsx'){
			   $reader = new PHPExcel_Reader_Excel2007();	  

			}			
			//读excel文件
			$reader->setReadDataOnly(false);  		
			$PHPExcel = $reader->load($tmp_file);//utf-8'); // 载入excel文件
			$countsheet = $PHPExcel->getSheetCount();//得到表数
	    for($i=0;$i<$countsheet;$i++){
				$sheet = $PHPExcel->getSheet($i); // 读取工作表
				$highestRow = $sheet->getHighestRow(); // 取得总行数
				$highestColumm = $sheet->getHighestColumn(); // 取得总列数
				similar_text($sheet->getTitle(), $selectarr[3], $percent);
				if ($percent>80){
					prnMsg($sheet->getTitle().'-'.$i.'='.$percent);
					$readsheet=1;
				}else{
					$readsheet=0;
				}							
			//$shtname = $PHPExcel->getSheetNames();//得到表名数组
			//$shtget = $PHPExcel->getSheetByName();           
			if ($readsheet==1){ //读取判断
				//把Excel数据保存数组中
				$data = array();
				$sheetdata=array();//关键词
				$headdata=array();//头关键词
				$sheettitle=array();
				$sql="SELECT  keyword, reporttype, itemtype, account, showlist, jd FROM keyword WHERE reporttype=".$i." and version=0 AND itemtype>=3 AND itemtype<=7";
				$result=DB_query($sql);
				prnMsg($sql);
				while($row=DB_fetch_array($result)){
					if ($row['itemtype']>2){
						$headdata[]=$row;
					}else{
					
						$sheetdata[]=$row;
					}
				}			
				for ($rowIndex = 1; $rowIndex <= $highestRow; $rowIndex++) {        //循环读取每个单元格的内容。注意行从1开始，列从A开始
					
					$chk=0;
					$datarow=array();
					for ($colIndex = 'A'; $colIndex <= $highestColumm; $colIndex++) {
						$addr = $colIndex . $rowIndex;
						$cell = $sheet->getCell($addr)->getCalculatedValue();
						if( $sheet->getCell($addr)->getDataType()==PHPExcel_Cell_DataType::TYPE_NUMERIC){  
						 
							$cellstyleformat = $sheet->getCell($addr)->getStyle( $sheet->getCell($addr)->getCoordinate())->getNumberFormat();  
							$formatcode=$cellstyleformat->getFormatCode(); 
							//prnMsg($formatcode.	$cell ); 
							//if (preg_match('/^(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy]/i', $formatcode)) {  
							if (preg_match('/^[hmsdy]/i', $formatcode)) {  
							   $cell=gmdate("Y-m-d", PHPExcel_Shared_Date::ExcelToPHP($cell));  
							  
							   $sheettitle[]=array($cell,$colIndex,$rowIndex );
							}else{  
								
								$cell=PHPExcel_Style_NumberFormat::toFormattedString($cell,$formatcode);  
							
							}
							//prnMsg($value.	$cell );
						}
						//	prnMsg($sheet->getCell($addr)->getDataType());
						//呵呵，找到了，将->getValue();改为->getCalculatedValue();
							if ($cell instanceof PHPExcel_RichText) { //富文本转换字符串
								$cell = $cell->__toString();
							}
					
						if (!is_null($cell)){
							$chk++;
							foreach($headdata as $val){
								similar_text( $cell,$val['keyword'], $percent);
								if ($percent>70){
									if ($val['jd']==1){
										$sheettitle[]=array($cell,(++$colIndex),$rowIndex );
									}elseif ($val['jd']==2){
										$sheettitle[]=array($cell,$colIndex,($rowIndex +1));
									}else{
										$sheettitle[]=array($cell,$colIndex,$rowIndex);
									}
								
								}

							}
							
						} 

						$datarow[$colIndex]=str_replace(' ','',$cell);
						if ($colIndex == $highestColumm&& $chk>0){//不是空行转为数组
							
							array_push($data,$datarow);							
							//unset($datarow);
						}
					}
				}
				
				$counter=count($data);
				//删除空列						
				for ($colIndex ='A';  $colIndex <=$highestColumm;  $colIndex++) {
					$last_names = array_column($data,  $colIndex);
					$chk=0;
					for ($rowIndex = 0; $rowIndex <$counter; $rowIndex++) { 
						if (!is_null($last_names[$rowIndex])){
							$chk++;
						}
					}				
						if ( $chk==0){
							for ($rowIndex = 0; $rowIndex <$counter; $rowIndex++) { 

								unset($data[$rowIndex][$colIndex]);
							}
						}
					
				}
			}//读取判断
		}
		//数据校验
		foreach($data as $row){
			foreach($sheatdata as $val){
				similar_text( $cell,$val['keyword'], $percent);
				if ($percent>70){
				
				}//endif

			}//foreach

		} //foreach
		 var_dump($sheettitle);
		 //var_dump($data);
		
	}else{
		prnMsg('没有需要更新的报表!','info');
	}

}//if ( $_FILES['ImportFile']['error']


if (isset($_POST['ShowSheet'])) {
	 $SQL="SELECT uploadid, tag,reporttype,a.reportid,reportname, urlfile, sheet, uploaddate, remark, a.flag FROM reportupload a LEFT JOIN reporttype b ON a.reportid=b.reportid WHERE  a.flag=0 AND periodyear='".$_POST['selectyear']."' AND rpttype=51 ORDER BY a.reportid,urlfile";
	//	 prnMsg($SQL);
	 $result=DB_query($SQL);
 
  	echo '<div id="Report">';
		echo '<table class="selection">';
			$TableHeader = '<tr>
		                   <th colspan="6" height="2">
								<div style="padding: 0; background-color: #'.$clr.'; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
		                 		</div> 
							</th>
						   </tr>
						   <tr>
								<th>序号</th>
								<th >下载</th>
								<th >属性</th>
								<th>读取</th>
								<th>删除</th>												
							</tr>';	
			
	echo  $TableHeader;
	$r=0;
	$i=1;
	while( $myrow=DB_fetch_array($result)){ 
		$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/ReportFile/'. $myrow['urlfile'];
	    if ($myrow['reporttype']==11){
			$reportname=substr($myrow['urlfile'],0,6).'资产负债表';

		}elseif ($myrow['reporttype']==12){
			$reportname=substr($myrow['urlfile'],0,6).'利润表';
		}else{
			$reportname=substr($myrow['urlfile'],0,6).'现金流量表';
		}
		if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			} else {
				echo '<tr class="OddTableRows">';
				$r=1;
			}
			echo'<td>'.$i.'</td>	  									
				
				<td ><a href="' . $outputFileName . '" >'. $reportname.'</a></td>
				<td>'.$myrow['reportname'].'</td>
				<td ><button type="submit" name="SubmitSettle" value="', htmlspecialchars(substr($GLCode,0,4), ENT_QUOTES, 'UTF-8', false), '" >读取</button></td>
				<td ><a href="' . $outputFileName . '" >删除</a></td>
			
				</tr>';
				$i++;

  }

	echo '</table>';
	echo '</div>';
}elseif (isset($_POST['crtExcel']) ) {

    require_once 'Classes/PHPExcel.php'; 
   require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls 
    
   $objExcel = new PHPExcel(); 
   //$objWriter = new PHPExcel_Writer_Excel5($objExcel);     // 用于其他版本格式 
  
   //设置文档基本属性 
   $objProps = $objExcel->getProperties(); 
   $objProps->setCreator("ChengJiang"); 
   $objProps->setLastModifiedBy("Chengjiang"); 
   $objProps->setTitle("利润表"); 
   $objProps->setSubject("会计报表"); 
   $objProps->setDescription("会计报表"); 
   $objProps->setKeywords("会计报表"); 
   $objProps->setCategory("会计报表"); 
   $RootPath=dirname(__FILE__ ) ;
   $sql="SELECT  lastdate_in_period FROM periods where  periodno ='".$_POST['selectprd'] ."'";
   $result=DB_query($sql);
   $row=DB_fetch_row($result);
   $dt=$row[0];
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
	if($_POST['rpttype']=='12'){
			//损益表
			$SQL="CALL GLPofit_Loss(".$_POST['selectprd'].",".$_POST['costitem'].",".$_POST['unittag'].")";
			$Result = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'));
			$ListCount = DB_num_rows($Result);	  

			//缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0  
			$objExcel->setActiveSheetIndex(0); 
			$objExcel->getSheet(0)->setTitle('利润表'); 
			$objSheet1 = $objExcel->getActiveSheet();   
		
			$mulit_arr =array(array( '项目' , '行次','本年累计','本月金额'));
			while ($myrow=DB_fetch_array($Result)) {			
				array_push($mulit_arr,array($myrow['title'],$myrow['showlist'],locale_number_format($myrow['amountq'],$_SESSION['CompanyRecord']['decimalplaces']),locale_number_format($myrow['amountm'],$_SESSION['CompanyRecord']['decimalplaces'])));	  
			}
		
			$objSheet1->getColumnDimension('A')->setWidth(40); 
			$objSheet1->getColumnDimension('B')->setWidth(7);   
			$objSheet1->getColumnDimension('C')->setWidth(20); 
			$objSheet1->getColumnDimension('D')->setWidth(20); 
			//合并单元格
			$objSheet1->mergeCells('A1:D1');
			$objSheet1->setCellValue('A1', '利润表');
			$objSheet1->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objSheet1->getStyle('A1')->getFont()->setSize(16);
			$objSheet1->setCellValue('A2', '');
			$objSheet1->setCellValue('B2', '');
			$objSheet1->setCellValue('C2', '');
			$objSheet1->setCellValue('D2', '02表');			
			$objSheet1->setCellValue('A3', '编制单位:'.$_SESSION['CompanyRecord']['coyname']);
			$objSheet1->mergeCells('B3:C3');
			$objSheet1->setCellValue('B3', '日期:'.$dt);			
			$objSheet1->setCellValue('D3', '单位:元');
			foreach($mulit_arr as $k=>$v){
			$k=$k+4;
			$objSheet1->setCellValue('A'.$k, $v[0]);
			$objSheet1->setCellValue('B'.$k, $v[1]);
			$objSheet1->setCellValue('C'.$k,$v[2]);
			$objSheet1->setCellValue('D'.$k, $v[3]);
			$objSheet1->getStyle( 'A'.$k.':'.'D'.$k)->applyFromArray($styleThinBlackBorderOutline);
			$objSheet1->getStyle('C'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('D'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle( 'A'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'B'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'C'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'D'.$k)->applyFromArray($styleBorderR);
			
			}
			// Rename worksheet			   
			//$objExcel->getActiveSheet()->setTitle('All Accounts');			   
			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$objExcel->setActiveSheetIndex(0);
		//写入类容
		$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
		//$objWriter->setIncludeCharts(TRUE);
		$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/利润表_'.periodymstr($_POST['selectprd'],0).'.xlsx';
		$objWriter->save($RootPath.'/'.$outputFileName);
		echo '<p><a href="'. $outputFileName. '">' . _('click here') . '</a> 下载文件<br />';
	}elseif($_POST['rpttype']=='11'){ 
			//资产表
		$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
		$SQL="CALL GLBalanceSheet(".$_POST['selectprd'].",".$_POST['costitem'].",".$_POST['unittag'].")";
		$Result = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'));
		$rptarr=array();
		$r=0;
		$ListCount = DB_num_rows($Result);

		while ($myrow=DB_fetch_array($Result)) {
			if ($r<32){
			$rptarr[$r]['title']=$myrow['title'];
			$rptarr[$r]['showlist']=$myrow['showlist'];
			$rptarr[$r]['amountq']=$myrow['amountq'];
			$rptarr[$r]['amountm']=$myrow['amountm'];
			}else{
				
			$rptarr[$r-32]['titler']=$myrow['title'];
			$rptarr[$r-32]['showlistr']=$myrow['showlist'];
			$rptarr[$r-32]['amountqr']=$myrow['amountq'];
			$rptarr[$r-32]['amountmr']=$myrow['amountm'];
			if ($myrow['title']=='负债合计'){
					$r+=64-$ListCount;
				}
			}
			$r++;
		}

		include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
			$objExcel->setActiveSheetIndex(0); 
			$objExcel->getSheet(0)->setTitle('资产负债表'); 
			$objSheet1 = $objExcel->getActiveSheet();   		
			$titlearr =array( '资产' , '行次','年初余额','期末余额','负债及所有者权益' , '行次','年初余额','期末余额');
			$objSheet1->getColumnDimension('A')->setWidth(20); 
			$objSheet1->getColumnDimension('B')->setWidth(5);   
			$objSheet1->getColumnDimension('C')->setWidth(12); 
			$objSheet1->getColumnDimension('D')->setWidth(12); 
			$objSheet1->getColumnDimension('E')->setWidth(20); 
			$objSheet1->getColumnDimension('F')->setWidth(5);   
			$objSheet1->getColumnDimension('G')->setWidth(12); 
			$objSheet1->getColumnDimension('H')->setWidth(12);
			//合并单元格
			$objSheet1->mergeCells('A1:H1');
			$objSheet1->setCellValue('A1', '资产负债表');
			$objSheet1->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objSheet1->getStyle('A1')->getFont()->setSize(16);
			$objSheet1->setCellValue('A2', '');
			$objSheet1->setCellValue('B2', '');
			$objSheet1->setCellValue('C2', '');
			$objSheet1->setCellValue('D2', '');
			$objSheet1->setCellValue('E2', '');
			$objSheet1->setCellValue('F2', '');
			$objSheet1->setCellValue('G2', '');
			$objSheet1->setCellValue('H2', '01表');
			
			$objSheet1->setCellValue('A3', '编制单位:'.$_SESSION['CompanyRecord']['coyname']);
			$objSheet1->mergeCells('D3:E3');
			$objSheet1->setCellValue('D3', '日期:'.$dt);
			
			$objSheet1->setCellValue('H3', '单位:元');
			//  设置单元格边框  锚：bbb
			$styleThinBlackBorderOutline = array(
			'borders' => array (
				'outline' => array (
					'style' => PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
					//'style' => PHPExcel_Style_Border::BORDER_THICK,  另一种样式
					'color' => array ('argb' => 'FF000000'),          //设置border颜色
				),  ),);     
			$objSheet1->setCellValue('A4', $titlearr[0]);
			$objSheet1->setCellValue('B4', $titlearr[1]);
			$objSheet1->setCellValue('C4',$titlearr[2]);
			$objSheet1->setCellValue('D4', $titlearr[3]);
			$objSheet1->setCellValue('E4', $titlearr[4]);
			$objSheet1->setCellValue('F4', $titlearr[5]);
			$objSheet1->setCellValue('G4',$titlearr[6]);
			$objSheet1->setCellValue('H4', $titlearr[7]);
			$objSheet1->getStyle( 'A4:H4')->applyFromArray($styleThinBlackBorderOutline);
			$objSheet1->getStyle('C4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('D4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('G4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('H4')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle( 'A4')->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'B4')->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'C4')->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'D4')->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'E4')->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'F4')->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'G4')->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'H4')->applyFromArray($styleBorderR);
			$k=4;
			foreach($rptarr as $myrow){ 
				$k++;
			$objSheet1->setCellValue('A'.$k,htmlspecialchars($myrow['title'],ENT_QUOTES,'UTF-8',false));
			$objSheet1->setCellValue('B'.$k,$myrow['showlist']);
			$objSheet1->setCellValue('C'.$k,locale_number_format($myrow['amountq'],$_SESSION['CompanyRecord']['decimalplaces']));
			$objSheet1->setCellValue('D'.$k,locale_number_format($myrow['amountm'],$_SESSION['CompanyRecord']['decimalplaces']));
			$objSheet1->setCellValue('E'.$k,htmlspecialchars($myrow['titler'],ENT_QUOTES,'UTF-8',false));
			$objSheet1->setCellValue('F'.$k,$myrow['showlistr']);
			$objSheet1->setCellValue('G'.$k,locale_number_format($myrow['amountqr'],$_SESSION['CompanyRecord']['decimalplaces']));
			$objSheet1->setCellValue('H'.$k,locale_number_format($myrow['amountmr'],$_SESSION['CompanyRecord']['decimalplaces']));
			$objSheet1->getStyle( 'A'.$k.':H'.$k)->applyFromArray($styleThinBlackBorderOutline);
			$objSheet1->getStyle('C'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('D'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('G'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('H'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		// $objSheet1->getStyle( 'A'.$k.':K'.$k)->applyFromArray($styleThinBlackBorderOutline);
			$objSheet1->getStyle( 'A'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'B'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'C'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'D'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'E'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'F'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'G'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'H'.$k)->applyFromArray($styleBorderR);
			
			}
		
		//写入类容
		$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
		$objWriter->setIncludeCharts(TRUE);
	
		$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/资产负债表_'.periodymstr($_POST['selectprd'],0).'.xlsx';

		$objWriter->save($RootPath.'/'.$outputFileName);
		echo '<p><a href="' . $outputFileName . '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
	
		
	}elseif ($_POST['rpttype']=='13'){
			// 现金表
		$SQL="CALL GLCashFlow(".$_POST['selectprd'].",".$_SESSION['janr'].")";
		$Result = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'));
		$ListCount = DB_num_rows($Result);

		$rptarr=array();
		$r=0;
		$ListCount = DB_num_rows($Result);	  

			//缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0  
			$objExcel->setActiveSheetIndex(0); 
			$objExcel->getSheet(0)->setTitle('现金流量表'); 
			$objSheet1 = $objExcel->getActiveSheet();   
		
			$mulit_arr =array(array( '项目' , '行次','本期数','本年累计数'));
			while ($myrow=DB_fetch_array($Result)) {			
				array_push($mulit_arr,array($myrow['title'],$myrow['showlist'],locale_number_format($myrow['amountm'],$_SESSION['CompanyRecord']['decimalplaces']),locale_number_format($myrow['amounty'],$_SESSION['CompanyRecord']['decimalplaces'])));	  
			}
		
			$objSheet1->getColumnDimension('A')->setWidth(40); 
			$objSheet1->getColumnDimension('B')->setWidth(7);   
			$objSheet1->getColumnDimension('C')->setWidth(20); 
			$objSheet1->getColumnDimension('D')->setWidth(20); 
			//合并单元格
			$objSheet1->mergeCells('A1:D1');
			$objSheet1->setCellValue('A1', '现金流量表');
			$objSheet1->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
			$objSheet1->getStyle('A1')->getFont()->setSize(16);
			$objSheet1->setCellValue('A2', '');
			$objSheet1->setCellValue('B2', '');
			$objSheet1->setCellValue('C2', '');
			$objSheet1->setCellValue('D2', '03表');
			
			$objSheet1->setCellValue('A3', '编制单位:'.$_SESSION['CompanyRecord']['coyname']);
			$objSheet1->mergeCells('B3:C3');
			$objSheet1->setCellValue('B3', '日期:'.$dt);			
			$objSheet1->setCellValue('D3', '单位:元');
			foreach($mulit_arr as $k=>$v){
			$k=$k+4;
			$objSheet1->setCellValue('A'.$k, $v[0]);
			$objSheet1->setCellValue('B'.$k, $v[1]);
			$objSheet1->setCellValue('C'.$k,$v[2]);
			$objSheet1->setCellValue('D'.$k, $v[3]);
			$objSheet1->getStyle( 'A'.$k.':'.'D'.$k)->applyFromArray($styleThinBlackBorderOutline);
			$objSheet1->getStyle('C'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			$objSheet1->getStyle('D'.$k)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			//$objSheet1->getStyle( 'A'.$k.':K'.$k)->applyFromArray($styleThinBlackBorderOutline);
			$objSheet1->getStyle( 'A'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'B'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'C'.$k)->applyFromArray($styleBorderR);
			$objSheet1->getStyle( 'D'.$k)->applyFromArray($styleBorderR);
			
			}
		//写入类容
		$objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
		$objWriter->setIncludeCharts(TRUE);	
		$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/'.'现金流量表_'.periodymstr($_POST['selectprd'],0).'.xlsx';
		$objWriter->save($RootPath.'/'.$outputFileName);
		echo '<p><a href="' .   $outputFileName. '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
	
	}
}
echo '</div>
	</form>';
include('includes/footer.php');
function BalanceSheet(&$data){

	
	return  $data;

}
?>
