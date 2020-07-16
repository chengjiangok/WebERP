
<?php
/*
 * @Author: ChengJiang 
 * @Date: 2017-03-05 06:25:37 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-04-21 20:17:20
 */
/* $Id: GLBalanceSheet.php chengjiang $*/
/* This script shows the balance sheet for the company as at a specified date. */

/*Through deviousness and cunning, this system allows shows the balance sheets as at the end of any period selected - so first off need to show the input of criteria screen while the user is selecting the period end of the balance date meanwhile the system is posting any unposted transactions */


include ('includes/session.php');
require_once 'Classes/PHPExcel.php'; 
$Title = _('Balance Sheet');// Screen identification.
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'BalanceSheet';// Anchor's id in the manual's html document.
include('includes/SQL_CommonFunctions.inc');
//include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable 

 if (!isset($_POST['selectprd'])OR $_POST['selectprd']==''){
		$_POST["selectprd"]=$_SESSION['period'];
  	}
include('includes/header.php');
	
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		_('Print Statement of Financial Position') . '" /> ' .// Icon title.
		_('Balance Sheet') . '</p>';// Page title.
if (!isset($_POST['crtPDF']) OR isset($_POST['ShowSheet'])){
	echo '<form method="post" action="' . $file_name . '">';
	echo '<div>';// div class=?
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		 <input type="hidden" name="selectprd" value="' . $_POST['selectprd'] . '" />';

	echo '<table class="selection">
			<tr>
				<td>' . _('Select the balance date').':</td>
				<td><select required="required" name="selectprd">';

	//$sql = "SELECT periodno,lastdate_in_period	FROM periods WHERE periodno <= '" .$_SESSION['period']. "' and periodno>0	ORDER BY periodno DESC"; 
	if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
  		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}else{
		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".(floor($_SESSION['startperiod']/12)*12-23 )."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
	}
	$result = DB_query($sql);
	 while ($myrow=DB_fetch_array($result,$db)){	
   	
		if(isset($_POST['selectprd']) AND $myrow['periodno']==$_POST['selectprd']){	
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
		echo   $myrow['periodno']. '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
	}

	echo '</select>
	      </td>
		  </tr>
		  </table>';

	echo '<br />
			<div class="centre">
				<input type="submit" name="ShowSheet" value="'._('Show on Screen').'" />
		    	<input type="submit" name="crtExcel" value="导出Excel" />
				<input type="submit" name="crtPDF" value="导出PDF  " />
			</div>';
   echo '</div>
        </form>';
}
if(isset($_POST['ShowSheet'])OR isset($_POST['crtExcel'])OR isset($_POST['crtPDF'])){
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
	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['selectprd'] . "'";
	$Result = DB_query($sql);
	$myrow = DB_fetch_row($Result);
	$BalanceDate = ConvertSQLDate($myrow[0]);

	$SQL="CALL GLBalanceSheet(".$_POST['selectprd'].")";

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
  

}

if (isset($_POST['crtPDF'])) {  

	include('includes/tcpdf/PDFGLBalanceSheet.php');

		// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('chengjiang');
	$pdf->SetTitle( _('Balance Sheet'));
	$pdf->SetSubject( _('Balance Sheet') );
	$pdf->SetKeywords(' PDF,TCPDF');
	// set default header dataPDF_HEADER_TITLE.
	$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
	// set header and footer fonts
	$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	// set margins
	$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	$pdf->setPrintFooter(false);
	// set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	if (@file_exists(dirname(__FILE__).'/tcpdf/eng.php')) {
		require_once(dirname(__FILE__).'/tcpdf/eng.php');
		$pdf->setLanguageArray($l);
	}
	// ---------------------------------------------------------
	// set font helvetica 
	$pdf->SetFont('droidsansfallback', '', 10);
	// add a page
	$pdf->AddPage();
	// column titles
	$header = array(_('Asset'), _('Line Number'), _('Number of year'), _('Final number'),_('liabilities and capital'), _('Line Number'), _('Number of year'), _('Final number'));
	// print colored table
	$pdf->GLBalanceSheet($header, $rptarr,$BalanceDate );
	//============================================================+
	// END OF FILE
	//============================================================+

		if ($ListCount == 0) {   //UldisN
			$Title = _('Print Balance Sheet Error');
			include('includes/header.php');
			prnMsg( _('There were no entries to print out for the selections specified') );
			echo '<br /><a href="'. $RootPath.'/index.php?' . SID . '">' .  _('Back to the menu'). '</a>';
			include('includes/footer.php');
			exit;
		} else {
		
	     ob_end_clean();
		//   ob_clean();	
				// close and output PDF document
			$pdf->Output( 'BalanceSheet'.$BalanceDate.'.pdf','D');
			$pdf->__destruct();
		//  echo  realpath($file_read);
		}
 //  exit;
} elseif (isset($_POST['ShowSheet'])) {
	
	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
	// Page title as IAS1 numerals 10 and 51:
	include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	echo '<div id="Report">';// Division to identify the report block.
		echo '<table class="selection">';
			$TableHeader = '<tr>
		                   <th colspan="8" height="2">
								<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
		                 		</div> 
							</th>
						   </tr>
						   <tr>
								<th>' . _('Asset') . '</th>
								<th>' . _('Line') . '</th>
								<th>' . _('Number of year') . '</th>
								<th >' ._('Final number') . '</th>
								<th>' . _('Asset') . '</th>
								<th>' . _('Line') . '</th>
								<th>' . _('Number of year') . '</th>
								<th >' . ('Final number') . '</th>						
							</tr>';	
			$k=DB_num_rows($AccountsResult);
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
							<td>%s</td>	  					
							<td >%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							</tr>',	  					
							htmlspecialchars($myrow['title'],ENT_QUOTES,'UTF-8',false),
							$myrow['showlist'],
							locale_number_format($myrow['amountq'],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($myrow['amountm'],$_SESSION['CompanyRecord']['decimalplaces']),					
							htmlspecialchars($myrow['titler'],ENT_QUOTES,'UTF-8',false),
							$myrow['showlistr'],
							locale_number_format($myrow['amountqr'],$_SESSION['CompanyRecord']['decimalplaces']),
							locale_number_format($myrow['amountmr'],$_SESSION['CompanyRecord']['decimalplaces'])	);

  }

	echo '</table>';
	echo '</div>';// div id="Report".

	
}elseif(isset($_POST['crtExcel'])){
   			
	if ($ListCount ==0) {
	prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	
	}else{
     set_include_path(PATH_SEPARATOR .'Classes/PHPExcel' . PATH_SEPARATOR . get_include_path()); 
	
   //  require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls 
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
   $objExcel->getSheet(0)->setTitle('资产负债表'); 
   $objSheet1 = $objExcel->getActiveSheet();    
   	$r=1;
   	$k=1;  
   	$itemstr=array( '资产' ,'行号' , '年初数','期末金额','负债及所有者权益','行号' , '年初数','期末金额');
		
	//显式指定内容类型 
 	 
      $objSheet1->getColumnDimension('A')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('B')->setAutoSize(true);    
	  $objSheet1->getColumnDimension('C')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('D')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('E')->setAutoSize(true); 
       $objSheet1->getColumnDimension('F')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('G')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('H')->setAutoSize(true); 
	 	 
	  $objExcel->getActiveSheet()->setCellValue('A'.$k, $itemstr[0]);
      $objExcel->getActiveSheet()->setCellValue('B'.$k, $itemstr[1]);
      $objExcel->getActiveSheet()->setCellValue('C'.$k, $itemstr[2]);
      $objExcel->getActiveSheet()->setCellValue('d'.$k, $itemstr[3]);
	  $objExcel->getActiveSheet()->setCellValue('E'.$k, $itemstr[4]);
	 $objExcel->getActiveSheet()->setCellValue('F'.$k, $itemstr[5]);
      $objExcel->getActiveSheet()->setCellValue('G'.$k, $itemstr[6]);
	  $objExcel->getActiveSheet()->setCellValue('H'.$k, $itemstr[7]);
	// while ($myrow = DB_fetch_array($Result) ){	
	foreach($rptarr as $myrow){ 	 
   // foreach($mulit_arr as $k=>$v){
    $k ++;
    /* @func 设置列 */
   
    $objExcel->getActiveSheet()->setCellValue('A'.$k, $myrow['title']);
    $objExcel->getActiveSheet()->setCellValue('B'.$k, $myrow['showlist']);
  
    $objExcel->getActiveSheet()->setCellValue('C'.$k, number_format($myrow['amountm'], 2, '.', ''));
    $objExcel->getActiveSheet()->setCellValue('D'.$k, number_format($myrow['amountq'], 2, '.', ''));
    $objExcel->getActiveSheet()->setCellValue('E'.$k, $myrow['titler']);
    $objExcel->getActiveSheet()->setCellValue('F'.$k, $myrow['showlistr']);
  
    $objExcel->getActiveSheet()->setCellValue('G'.$k, number_format($myrow['amountmr'], 2, '.', ''));
    $objExcel->getActiveSheet()->setCellValue('H'.$k, number_format($myrow['amountqr'], 2, '.', ''));
           
   }
   //写入类容
   $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
   //$outputFileName = $_SESSION['reports_dir'] . '/RevenceCost_' . Date('Y-m-d') .'.xls';
   $objWriter->setIncludeCharts(TRUE);
   $RootPath=dirname(__FILE__ ) ;
   $outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/资产负债表_'.periodymstr($_POST['selectprd'],0).'.xlsx';
   $objWriter->save($RootPath.'/'.$outputFileName);
	   echo '<p><a href="' . $outputFileName . '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
   }
}

include('includes/footer.php');
?>
