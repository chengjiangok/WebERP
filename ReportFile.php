
<?php
/*
 * @Author: ChengJiang 
 * @Date: 2017-05-26 06:15:11 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-12-24 09:42:12
 汉字文件名
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
			$file_name=date('Ym',time()).'_'.rand(1,100).substr($file_true_name,strrpos($file_true_name,"."));  
			 
			if(move_uploaded_file($uploaded_file,$move_to_path.$file_name)) { 
			
					$sql="INSERT INTO `reportupload`(`tag`,
														`rptype`,
														`urlfile`,
														`period`,
														`uploaddate`,
														`remark`,
														`flag`
													)VALUES	(
														'".$_POST['unittag']."',
														'".$_POST['rptype']."',
														'".$file_name."',
														0,
										'". date('Y-m-d',time())."',
										'',
										0)";
							$result=DB_query($sql);
		  
				   $msg.= $_FILES['ImportFile']['name']."上传成功!<br>";  
			} else {  
			   prnMsg("上传失败",'info');  
			}  
		} else {  
			  prnMsg("上传失败!",'info');  
		} 	
		
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
		  <td colspan="2">选择报表:
		     <select name="rpttype" size="1" >';
 	     	$sql="SELECT reportid, reportname FROM reporttype WHERE rpttype=1";
		    $Result = DB_query($sql);
		while ($myrow=DB_fetch_array($Result,$db)){	
	
		 if(isset($_POST['rpttype']) AND $myrow['reportid']==$_POST['rpttype']){	
				
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
			echo  $myrow['reportid'] . '">' .  $myrow['reportname'] . '</option>';
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
  
	 
  
	  echo '<td >类别:<select name="reportype" size="1" >';

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
echo	'</select>
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
			<input type="submit" name="Import" value="上传更新">
		</div>';
  //} 
  if ( $_FILES['ImportFile']['error']==10){
	//下面代码为读取excel写入表
	/** 引入 PHPExcel_IOFactory */
	require_once dirname(__FILE__) . '/Classes/PHPExcel/IOFactory.php';
	//用load()加载表格文件

	//$inputFileName='companies/'.$_SESSION['DatabaseName']."/BankCopy/nongxin20170108h0-08.xls";   
	$inputFileName=$move_to_path.$file_name;
	$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);

	$objreader= new PHPExcel_Reader_Excel5();
	//    $objReader = new PHPExcel_Reader_Excel2007();

	$objPHPExcel = $objreader->load($inputFileName);
	//或者你也可以用PHPExcel_IOFactory中的createReader()方法来声明你的对象，
	//然后设置你要使用的加载器
	$inputFileType = 'Excel5';
	//    $inputFileType = 'Excel2007';

	$objReader = PHPExcel_IOFactory::createReader($inputFileType);
	$objPHPExcel = $objReader->load($inputFileName);
	$sheetData = $objPHPExcel->getActiveSheet()->toArray(null,true,true,true);
	$sheet = $objPHPExcel->getSheet(0); // 读取第一個工作表
	$excelRow = $sheet->getHighestRow(); // 取得总行数
	$excelColumm = $sheet->getHighestColumn(); // 取得总列数	
	$chkacc=0;
	$DCflg=$frmt['flg'];//借贷金额模式y标记借贷以+-分开数据在debit
	if ($frmt['payaccount']!=''){//收付账户列模式
		$payto=1;   //收款单位一列付款单位一列				
	}else{
		$payto=0;//收付单位都在一列
	}
	
		if($frmt['account']=='x'){
			$chkacc=1;
		}elseif ($sheet->getCell($frmt['account'])->getValue()==explode('^',$_POST['BankAccount'])[1]){
			$chkacc=1;
		}
//prnMsg( $frmt['account'].'=chkacc'.$chkacc.'banlance:'.$balance.'-'. explode('^',$_POST['BankAccount'])[1],'info');	
if ($chkacc==1){	 
	//检验上传文件账号是否和选择相同   
	 //对账单插入
	$sql="SELECT banktransid,  bankdate FROM banktransaction WHERE account='".explode('^',$_POST['BankAccount'])[0]."' ORDER BY bankdate DESC LIMIT 1";
	$result=DB_query($sql);
	$row=DB_fetch_row($result);		
	$bkindex=1;
	$balance=0;
	$startrow=-1;
	if (!is_null($row)){
	   $bkindex=$row[0];
	   $enddate=date($_SESSION['DefaultDateFormat'].' H:i:s',strtotime($row[1]));
	}
	//已经录入对账单余额和行数
	$sql="SELECT sum(amount) amount ,COUNT(*) cout FROM banktransaction WHERE account='".explode('^',$_POST['BankAccount'])[0]."'";
	$result=DB_query($sql);
	$row1=DB_fetch_row($result);		
	 if ($row1[0]===null){
		 $balance=0;
		 $startrow=0;
		 $flag=1;
	}else{
		 $balance=$row1[0];//已经录入余额
		 //$startrow=$row1[1];
		 $flag=0;
	}

	  $detotal=0;
	 $crtotal=0;			
	 $enddt='';            
	 $insertrow=0;
	// prnMsg( $enddate.'=row'.$balance.'-'. $startrow.'$excelRow'.$excelRow,'info');	
	for ($r = $frmt['startrow']; $r <= $excelRow; $r++){//行数是以设置行开始
		//prnMsg($r.'===');
		if ($balance==0 && $r==$frmt['startrow']){
			$flag=1;
		}else{
			$flag=0;
		}
			$amo=0;
			$flg=1;//冲账标记
	   // $dt1=date_create($sheet->getCell($frmt['bkdate'].$r)->getValue());
	
		if ($sheet->getCell($frmt['bkdate'].$r)->getValue()!=''){  //用日期判断内容是否为空
				$dttime=strtotime($sheet->getCell($frmt['bkdate'].$r)->getValue());
				$dt1=date($_SESSION['DefaultDateFormat'].' H:i:s',$dttime);					
				$diff=date_diff(date_create($dt1),date_create($enddate));
				//var_dump($diff);
				$diff_days = $diff->format("%R%a");
				//prnMsg($diff_days.'=='.$r);
			//	prnMsg($dt1.'end'.$enddate.'-'.$diff_days);
			if ($diff_days<=0){//$dt1大于等于$enddate
				//if ($diff>0){
				$amo=(float)str_replace(',','',$sheet->getCell($frmt['debit'].$r)->getValue());
		
				//if ( $sheet->getCell($frmt['flg'].$r)->getValue()!=''){//-1借贷以+-分开数据在debit
				if ( $DCflg=='y'){//y标记借贷以+-分开数据在debit
					if ($amo>0){
						$detotal=$amo;
					}else{
						$crtotal=-$amo;
					}
				}else{
					if ((float)str_replace(',','',$sheet->getCell($frmt['debit'].$r)->getValue())!=0){
						if ($amo<0){
							$flg=-1;
						}
						// $crtotal=0;
						$detotal=$amo;
					}else{
						$amo=(float)str_replace(',','',$sheet->getCell($frmt['credit'].$r)->getValue());
					if($amo<0){
							$flg=-1;
						}
						$amo=-$amo;
						$crtotal=-$amo;
					}
				}// read debit credit
			
					//查对已经录入余额和日期和对账单余额和日期	  
				if($startrow==0 AND round((float)str_replace(',','',$sheet->getCell($frmt['balance'].$r)->getValue())-$amo,2)!=round((float)$balance,2)){
						$startrow= $frmt['startrow'];
						$str=1;
					}elseif( round((float)str_replace(',','',$sheet->getCell($frmt['balance'].$r)->getValue())-$amo,2)==round((float)$balance,2)){
						$startrow=$r;
						$str=2;
				}
			
				//prnMsg($startrow.'/'.$balance.'-'.$str.'[]'.$r.'='.round((float)str_replace(',','',$sheet->getCell($frmt['balance'].$r)->getValue())-$amo,2),'info');
				if ($startrow > 0){
		
					if ($r==$startrow){//账单开始结束日期
						$startdate= date_format(date_create($sheet->getCell($frmt['bkdate'].$r)->getValue()),'Y-m-d H:i:s');
						//prnMsg('1'.$startdate);
					}elseif($r==$excelRow){
						$enddt= date_format(date_create($sheet->getCell($frmt['bkdate'].$r)->getValue()),'Y-m-d H:i:s');
						//prnMsg('2'.$enddt);
					}
					//prnMsg(	date('Y-m-d H:i:s',strtotime(date_create($sheet->getCell($frmt['bkdate'].$r)->getValue())))	);
				//   prnMsg($startrow.'<='.$r.' &&'. $amo.'[]'.$dt1);
				if ($startrow<=$r && $amo!=0){//账单开始行
					if ($payto==1){//收款单位一列付款单位一列	
					
						if ($amo>0){ //收款的来源账号为preg_replace('# #','',
							//preg_replace('/^[(\xc2\xa0)|\s]+/', ''
							$toaccount=mbStrSplit($sheet->getCell($frmt['payaccount'].$r)->getValue(),2);
							$toname= mbStrSplit($sheet->getCell($frmt['payname'].$r)->getValue());
							$tobank=trim($sheet->getCell($frmt['paybank'].$r)->getValue());
						}else{	//付款的目的账号
							//	if($sheet->getCell($frmt['payaccount'].$r)->getValue()==explode('^',$_POST['BankAccount'])[2]){
							$toaccount=mbStrSplit($sheet->getCell($frmt['toaccount'].$r)->getValue(),2);
							$toname= mbStrSplit($sheet->getCell($frmt['toname'].$r)->getValue());
							$tobank=trim($sheet->getCell($frmt['tobank'].$r)->getValue());
						
						
						}
					
					}else{//收付款单位一列	
						$toaccount=mbStrSplit($sheet->getCell($frmt['toaccount'].$r)->getValue(),2);
						$toname= mbStrSplit($sheet->getCell($frmt['toname'].$r)->getValue());
						$tobank=trim($sheet->getCell($frmt['tobank'].$r)->getValue());
					}
						$sql="INSERT INTO 	banktransaction(account,
														bankdate,
														amount,
														currcode,
														toaccount,
														toname,
														tobank,
														remark,
														abstract,
														flag,
														flg	)
														VALUES('".explode('^',$_POST['BankAccount'])[0] ."','".
														$dt1."','".
														$amo."','".						
														explode('^',$_POST['BankAccount'])[2]."','".
														$toaccount."','".
														trim($toname)."','".
														trim($tobank)."','".
														mbStrSplit($sheet->getCell($frmt['remark'].$r)->getValue())."','".
														mbStrSplit($sheet->getCell($frmt['abstract'].$r)->getValue())."',".
														$flag.",".
														$flg.")";
												//		prnMsg($sql);
						$result=DB_query($sql);	
						$insertrow++;
				}
			}
	
		}	//日期比对	
	 }	//内容不能空
	}//for
}else{//if row207
	$msg='上传的文件账户和选择的不同！';
}//检验上传文件账号是否和选择相同 end
//prnMsg($enddt.'e=s'.$startdate.'-'.$startdate);
if ($result==true and $insertrow>0){
	AddBankCustname();
	/*	
	$balance=$balance+$detotal-$crtotal;				
	$newname=explode('^',$_POST['BankAccount'])[0]."_". $bkindex.substr($file_name,strrpos($file_name,"."));
	$sql="UPDATE bankcopy SET urlfile='".$newname."',
						startdate='".$startdate."',
						enddate='".$enddt."',
						debit='".$detotal."',
						credit='".$crtotal."',
						balance='".$balance."',						
						flag=0 
						WHERE urlfile ='".$file_name."'";
	*/
	//if(!rename($move_to_path.$file_name,$move_to_path.$newname)) {
						   
	//   echo "改名失败！";
	//}
}else{//上传文件没有写入记录删除
		  $sql="DELETE FROM bankcopy 
						 WHERE urlfile ='".$file_name."'";
		 
		  $result=DB_query($sql);	
		  //$move_to_path='companies/'.$_SESSION['DatabaseName']."/BankCopy/"; 
		  $msg.="你上传文件没有能插入记录!<br>"; 
		if (unlink($move_to_path.$file_name)){
			 $msg.=$_FILES['ImportFile']['name']."删除文件完成！<br>"; 
		 }else{
			$msg.=$_FILES['ImportFile']['name']."删除文件失败！<br>"; 
		 }
}
	prnMsg($msg,'info'); 


}//if ( $_FILES['ImportFile']['error']


if (isset($_POST['ShowSheet'])) {
	 $SQL="SELECT `uploadid`, `tag`, `rptype`, `urlfile`, `period`, `uploaddate`, `remark`, `flag` FROM `reportupload` WHERE  period>=0";
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
								<th>期间</th>
								<th>类别</th>
								<th>URL</th>
								<th >下载</th>
								<th>读取</th>
								<th>删除</th>												
							</tr>';	
			
	echo  $TableHeader;
	$r=0;
	while( $myrow=DB_fetch_array($result)){ 
		$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/ReportFile/'.periodymstr($_POST['selectprd'],$_POST['prdrange']).'.xlsx';
	
		if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			} else {
				echo '<tr class="OddTableRows">';
				$r=1;
			}
			echo'<td>'. htmlspecialchars($myrow['remark'],ENT_QUOTES,'UTF-8',false) .'</td>	  									
							<td >'.$myrow['tag']  .'</td>
							<td ><a href="' . $outputFileName . '" >'. $myrow['urlfile'] .'</a></td>
							<td >'.$myrow['uploaddate'] .'</td>
							
							<td ><button type="submit" name="SubmitSettle" value="', htmlspecialchars(substr($GLCode,0,4), ENT_QUOTES, 'UTF-8', false), '" >读取</button></td>
							<td ><a href="' . $outputFileName . '" >删除</a></td>
						
							</tr>';

  }

	echo '</table>';
	echo '</div>';// div id="Report".



}elseif (isset($_POST['crtExcel']) ) {

   //set_include_path(PATH_SEPARATOR .'Classes/PHPExcel' . PATH_SEPARATOR . get_include_path()); 
   //require_once 'Classes/PHPExcel.php'; 
   //require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls 
    
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
?>
