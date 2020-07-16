<?php
/* $Id: GLCostRevenceInquiry.php  ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-03-14 16:14:56 
 * @Last Modified by:   ChengJiang 
 * @Last Modified time: 2017-04-21 16:14:56 
 2017-09-04 clear
 */
include ('includes/session.php');
require_once 'Classes/PHPExcel.php'; 
$Title ='收益成本报表';
$ViewTopic= 'CostRevence';
$BookMark ='CostRevence';

include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['selectprd'])OR $_POST['selectprd']==''){
		$_POST["selectprd"]=$_SESSION['period'];
  	}
	 
if (!isset($_POST['dataformat'])) {
   $_POST['dataformat']=1;
}

if (!isset($_POST['costitem'])){
      $_POST['costitem']=0;
	}
	
	include  ('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		$Title. '"/> ' .// Icon title.
		$Title . '</p>';// Page title.
  
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	      <input type="hidden" name="selectprd" value="' . $_POST['selectprd'] . '" />
	  	<input type="hidden" name="dataformat" value="' . $_POST['dataformat'] . '" />
      	<input type="hidden" name="costitem" value="' . $_POST['costitem'] . '" />';

	echo '<table class="selection">';   
 	echo '<tr>
  	      <td>选择报表:</td>
	      <td><select name="costrevence" size="1" >';
       $sql="SELECT `reportid`, `reportname` FROM `reporttype` WHERE rpttype=2 " ;
//	if (file_exists( $_SESSION['reports_dir'] . '/CostType.csv')){
	//	$FileVT =fopen( $_SESSION['reports_dir'] . '/CostType.csv','r');
	//	while ($mytype = fgetcsv($FileVT)) { 
			// 	if( $mytype[2]==$_POST['costrevence']){
			$result = DB_query($sql);
   
		while ($myrow=DB_fetch_array($result)){	
					
				if(isset($_POST['costrevence']) AND $myrow['reportid']==$_POST['costrevence']){	
			 		
			 		echo '<option  selected="selected"  value="';
			 	}else{
			 		echo '<option  value="';
			 	}
			 	echo  $myrow['reportid'] . '">' . $myrow['reportname'] . '</option>';
		}
    if (isset($_POST['costrevence'])&& $_POST['costrevence']==21){
		$_POST['costrevence']='5001';
	}else{
		$_POST['costrevence']='6001';
	}			
	echo	'</select>
	      </td>
	      </tr>';
//	fclose($FileVT);	
	
	echo '<tr>
	        <td>' . _('Select Period To')  . '</td>
          <td >
		    <select name="selectprd" size="1" >';					
 
   	if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
  		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}else{
		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".(floor($_SESSION['startperiod']/12)*12-23 )."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}
   $periods = DB_query($sql);
   
   while ($myrow=DB_fetch_array($periods,$db)){	
   	
		if(isset($_POST['selectprd']) AND $myrow['periodno']==$_POST['selectprd']){	
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
		echo   $myrow['periodno']. '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
	}  
   
    echo '</select>';
    echo '<tr>
			<td>格式</td>
			<td>
			    <input type="radio" name="dataformat" value="1"  '.($_POST['dataformat']==1 ? 'checked':"").' >'._('Default').'          
                <input type="radio" name="dataformat" value="2"   '.($_POST['dataformat']==2 ? 'checked':"").' >'._('Detail').' 
            </td>
       </tr>'; 

 if (isset($_SESSION['Tag']) AND $_SESSION['Tag']!=0){
    $sql="SELECT left(code,1) code,description FROM  workcentres";
    $result = DB_query($sql);  
	echo '<tr>
	        <td>选择核算单元:</td>
	   		<td>
			   <select name="costitem" size="1" >';
	if($_POST['costitem']==0) {
    	 echo '<option selected="selected"   value="0">' ._('All') . '</option>';
  	}	else {
  	 echo '<option  value="0">' ._('All') . '</option>';	
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
	        </td>
			</tr>';
	 }
	echo '	</table>
		<br />';

	echo '<div class="centre">
				<input type="submit" name="Search" value="查询" />
		     	<input type="submit" name="crtExcel" value="导出Excel" />';
				
			
	echo '</div>';
if (isset($_POST['crtExcel'])OR isset($_POST['Search']) ){
   if (isset($_SESSIN['Tag']) AND $_POST['costitem']>0){
      
	   $sql="SELECT confvalue FROM myconfig WHERE confname = 'settleflag'  AND  costitem=".$_POST['costitem']." limit 1"; 
     }elseif (!isset($_SESSIN['Tag'])) {
	   $sql="SELECT confvalue  FROM myconfig WHERE confname='settleflag' limit 1 ;"; 
   	 
	 }
	if ($_POST['costitem']>0 OR !isset($_POST['costitem']) ){
	$Result = DB_query($sql);
	
	$row = DB_fetch_array($Result);
	
	$str=json_decode($row[0],true);
	
	$sarr=str_split($str[$_POST['selectprd']],1);	

	$wd=0;
	foreach($sarr as $value){
       $wd+=$value;
	}
	$wd=$wd*10;
	}else{
	 $wd=0;	
	}
  if ($_POST['costrevence']=='6001'){
	
	 $SQL = " CALL GLCostRevence('".$_POST["selectprd"]."','" .$_SESSION["janr"]."','".$_POST['costitem']."')";
     $h=14;
  }else{
     $h=33; 
 	 $SQL = "CALL GLCost('".$_POST["selectprd"]."','" .	$_SESSION["janr"]."','".$_POST['costitem']."')";
  }
    $Result = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));
	$ListCount=DB_num_rows($Result);
}
if (isset($_POST['crtExcel'])) {
 			
	if ($ListCount ==0) {
	prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	
	}else{
     //set_include_path(PATH_SEPARATOR .'Classes/PHPExcel' . PATH_SEPARATOR . get_include_path()); 
	
    // require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls 
   $objExcel = new PHPExcel(); 
   //$objWriter = new PHPExcel_Writer_Excel5($objExcel);     // 用于其他版本格式 
    //设置文档基本属性 
   $objProps = $objExcel->getProperties(); 
   $objProps->setCreator("Zeal Li"); 
   $objProps->setLastModifiedBy("Zeal Li"); 
   $objProps->setTitle("Office XLS Test Document"); 
   $objProps->setSubject("Office XLS Test Document, Demo"); 
   $objProps->setDescription("Test document, generated by PHPExcel."); 
   $objProps->setKeywords("office excel PHPExcel"); 
   $objProps->setCategory("Test"); 
   //设置当前的sheet索引，用于后续的内容操作。一般只有在使用多个sheet的时候才需要显示调用。 
   //缺省情况下，PHPExcel会自动创建第一个sheet被设置SheetIndex=0  
   $objExcel->setActiveSheetIndex(0); 
   $objExcel->getSheet(0)->setTitle('收益成本表'); 
   $objSheet1 = $objExcel->getActiveSheet();    
   	$r=1;
   	$k=1;  
   	$itemstr=array( '科目编码' ,'科目名称' , '序号','本期金额','本年合计');		
	//显式指定内容类型  	 
      $objSheet1->getColumnDimension('A')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('B')->setWidth(30);   
	  $objSheet1->getColumnDimension('C')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('D')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('E')->setAutoSize(true); 
	 
	  $objExcel->getActiveSheet()->setCellValue('A'.$k, $itemstr[0]);
      $objExcel->getActiveSheet()->setCellValue('B'.$k, $itemstr[1]);
      $objExcel->getActiveSheet()->setCellValue('C'.$k, $itemstr[2]);
      $objExcel->getActiveSheet()->setCellValue('d'.$k, $itemstr[3]);
	  $objExcel->getActiveSheet()->setCellValue('E'.$k, $itemstr[4]);
	 while ($myrow = DB_fetch_array($Result) ){	
   // foreach($mulit_arr as $k=>$v){
    $k ++;
    /* @func 设置列 */
    $objExcel->getActiveSheet()->setCellValue('A'.$k, $myrow['rptno']);
    $objExcel->getActiveSheet()->setCellValue('B'.$k, $myrow['title']);
    $objExcel->getActiveSheet()->setCellValue('C'.$k, $myrow['showlist']);
  
    $objExcel->getActiveSheet()->setCellValue('D'.$k, number_format($myrow['amountm'], 2, '.', ''));
    $objExcel->getActiveSheet()->setCellValue('E'.$k, number_format($myrow['amountq'], 2, '.', ''));
           
   }
   //写入类容
   $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    if ($_POST['costrevence']=='6001'){
       $outputFileName = 'companies/'.$_SESSION['DatabaseName'].'/reports/收入成本表_' . periodymstr($_POST['selectprd'],0).'.xlsx';
	}else{
        $outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/成本_' .periodymstr($_POST['selectprd'],0).'.xlsx';

	}
	$RootPath=dirname(__FILE__ ) ;
    $objWriter->save($RootPath.'/'.$outputFileName); 
    echo '<p><a href="' .  $outputFileName . '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
   }
} elseif (isset($_POST['Search'])) {	

	 echo '<table class="selection">';

	if ($_POST['costitem']=='0'){
			$rptarr=array();
	    $r=0;
	while ($myrow=DB_fetch_array($Result)) {
		if ($r<$h){
		$rptarr[$r]['title']=$myrow['title'];
		$rptarr[$r]['showlist']=$myrow['showlist'];
		$rptarr[$r]['amountm']=$myrow['amountm'];
		$rptarr[$r]['amountq']=$myrow['amountq'];
		}else{			
		$rptarr[$r-$h]['showlistr']=$myrow['showlist'];
		$rptarr[$r-$h]['amountmr']=$myrow['amountm'];
		$rptarr[$r-$h]['amountqr']=$myrow['amountq'];
		}
		$r++;
	}
	
	$TableHeader = '<tr>
					    <th></th>				
				        <th colspan="3">模具</th>
						<th colspan="3">注塑</th>		
					</tr>
					<tr>
						<th>科目名称</th>
						<th>' . _('Line')  . '</th>
						<th>' . _('Occurrence number')  . '</th>
						<th>' .  _('Cumulative occurrence of this year') . '</th>
								
										<th>' . _('Line') . '</th>
										<th>' . _('Occurrence number')  . '</th>
						<th>' .  _('Cumulative occurrence of this year') . '</th>				
					</tr>';	
	
	echo  $TableHeader;
	$r=0;
	foreach($rptarr as $myrow){ 
		if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			} else {
				echo '<tr class="OddTableRows">';
				$r=1;
			}
			printf('<td>%s</td>	  				
							<td >%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
						  					
							<td >%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							</tr>',	  					
							htmlspecialchars($myrow['title'],ENT_QUOTES,'UTF-8',false),
							$myrow['showlist'],
							locale_number_format($myrow['amountm'],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($myrow['amountq'],$_SESSION['CompanyRecord']['decimalplaces']),					
							$myrow['showlistr'],
							locale_number_format($myrow['amountmr'],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($myrow['amountqr'],$_SESSION['CompanyRecord']['decimalplaces'])	);

  }
	}else{
	     echo  '<tr>
					<th colspan="5" height="2">
						<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
						</div> 
					</th></tr>
				<tr>
					<th>科目编码</th>
					<th>科目名称</th>
					<th>' . _('Line')  . '</th>
					<th>' . _('Occurrence number')  . '</th>
					<th>' .  _('Cumulative occurrence of this year') . '</th>
			</tr>';
 				$k=0;
 				$R=1;
	while ($myrow=DB_fetch_array($Result)) {
			if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
			printf('<td>%s</td>
			<td><h4><i>%s</i></h4></td>							
								<td>%s</td>														
								<td class="number">%s</td>								
								<td class="number">%s</td>
									</tr>',	
								 $myrow['rptno'],						
								htmlspecialchars($myrow['title'],ENT_QUOTES,'UTF-8',false),
							    $myrow['showlist'],
							 	locale_number_format($myrow['amountm'],$_SESSION['CompanyRecord']['decimalplaces']),
								locale_number_format($myrow['amountq'],$_SESSION['CompanyRecord']['decimalplaces']));
			    $R++;
				
	}
	}
		echo '</table>';

}
echo '</div>
    </form>';
include('includes/footer.php');
?>
