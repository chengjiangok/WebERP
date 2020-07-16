/*
 * @Author: ChengJiang 
 * @Date: 2017-03-05 22:35:40 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-03-09 20:15:56
 */

<?php
/* $Id: GLProfit_Loss.php 7268 chengjiang $*/
/* Shows the profit and loss of the company for the range of periods entered. */

include ('includes/session.php');
$Title = _('Profit and Loss');// Screen identification.
$ViewTopic= 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ProfitAndLoss';// Anchor's id in the manual's html document.
include('includes/SQL_CommonFunctions.inc');
include('includes/AccountSectionsDef.inc'); // This loads the $Sections variable
if (!isset($_POST['selectprd'])OR $_POST['selectprd']==''){
		$_POST["selectprd"]=$_SESSION['period'];
		}
include('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		_('Print Statement of Comprehensive Income') . '" /> ' .// Icon title.
		_('Profit and Loss') . '</p>';// Page title.
if(!isset($_POST['crtPDF'] )or isset($_POST['SelectADifferentPeriod'])){	
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<div>';// div class=?
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

		echo '<table class="selection">
			<tr>
				<td>' . _('Select the balance date').':</td>
				<td><select required="required" name="selectprd">';

	$sql = "SELECT periodno,lastdate_in_period	FROM periods WHERE periodno <= '" .$_SESSION['period']. "' and periodno>0	ORDER BY periodno DESC"; 
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
		   </table>
		<br />';
		 
	echo '<br />
			<div class="centre">
				<input type="submit" name="ShowSheet" value="'._('Show on Screen').'" />
		    	<input type="submit" name="crtExcel" value="导出Excel" />
				<input type="submit" name="crtPDF" value="导出PDF  " />
			</div>';

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
	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno=" . $_POST['selectprd'] . "";
	$Result = DB_query($sql);
	$myrow = DB_fetch_row($Result);
	$BalanceDate = ConvertSQLDate($myrow[0]);
	
	$SQL="CALL GLPofit_Loss(".$_POST['selectprd'].",".$_SESSION['janr'].")";
	$Result = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'));
    $ListCount = DB_num_rows($Result);

    $rptarr=array();
	$r=0;
	$ListCount = DB_num_rows($Result);

}

 if (isset($_POST['crtPDF'])) {

  include('includes/tcpdf/PDFGLProfit.php');
  	echo '<input type="hidden" name="selectprd" value="' . $_POST['selectprd'] . '" />';
	
  

	if (DB_error_no() != 0) {
		$Title = _('Profit and Loss') . ' - ' . _('Problem Report') . '....';
		include('includes/header.php');
		echo $_POST['selectprd'];
		prnMsg( _('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg() );
		echo '<br /><a href="' .$RootPath .'/index.php">' .  _('Back to the menu'). '</a>';
		if ($debug == 1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}
	if ($ListCount==0){
		$Title = _('Print Profit and Loss Error');
		include('includes/header.php');
		echo '<br />';
		prnMsg( _('There were no entries to print out for the selections specified'),'warn' );
		echo '<br /><a href="'. $RootPath.'/index.php">' .  _('Back to the menu'). '</a>';
		include('includes/footer.php');
		exit;
	}
	// create new PDF document
	$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('chengjiang');
	$pdf->SetTitle( _('Profit Table'));
	$pdf->SetSubject( _('Profit Table') );
	$pdf->SetKeywords('TCPDF, PDF, Profit Table');
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
	$header = array('项目', _('Line Number'),  '本月金额', '本年金额');
	// print colored table
	$pdf->profit($header, $Result ,$BalanceDate);
	//============================================================+
	// END OF FILE
	//============================================================+

		if ($ListCount == 0) {   //UldisN
			$Title = _('Print Profit Table Error');
			include('includes/header.php');
			prnMsg( _('There were no entries to print out for the selections specified') );
			echo '<br /><a href="'. $RootPath.'/index.php?' . SID . '">' .  _('Back to the menu'). '</a>';
			include('includes/footer.php');
			exit;
		} else {
			ob_end_clean(); //solved
			// close and output PDF document
			$pdf->Output( 'Profit'. $BalanceDate.'.pdf','D');

			//$pdf->OutputD($_SESSION['DatabaseName'] . '_GL_Balance_Sheet_' . date('Y-m-d') . '.pdf');
		$pdf->__destruct();
		}
		exit;
}elseif (isset($_POST['ShowSheet'])) {
	// Page title as IAS1 numerals 10 and 51:	
		$TableHeader = '<tr>
		                   <th colspan="4" height="2">
								<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
		                 		</div> 
							</th>
						</tr>
						<tr>						
							<th>' . _('Project')  . '</th>
							<th>' . _('Line')  . '</th>
							<th>' . _('Occurrence number')  . '</th>
							<th>' . _('Cumulative occurrence of this year') . '</th>
						</tr>';
							// Page title as IAS1 numerals 10 and 51:
		include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
	 echo '<div id="Report">';// Division to identify the report block.
	 echo '<table class="selection">';
 	 echo  $TableHeader;
 	 $k=0;
	  
	while ($myrow=DB_fetch_array($Result)) {
			if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
			printf('<td><h4><i>%s</i></h4></td>							
								<td>%s</td>								
								<td class="number">%s</td>								
								<td class="number">%s</td>
								</tr>',							
								htmlspecialchars($myrow['title'],ENT_QUOTES,'UTF-8',false),
							    $myrow['showlist'],
								locale_number_format($myrow['amountm'],$_SESSION['CompanyRecord']['decimalplaces']),
								locale_number_format($myrow['amountq'],$_SESSION['CompanyRecord']['decimalplaces']));
			}	
 
	echo '</table>';
	echo '</div>';// div id="Report".

}elseif(isset($_POST['crtExcel'])){
   			
	if ($ListCount ==0) {
	prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	
	}else{
     set_include_path(PATH_SEPARATOR .'Classes/PHPExcel' . PATH_SEPARATOR . get_include_path()); 
	 require_once 'Classes/PHPExcel.php'; 
     require_once 'Classes/PHPExcel/Writer/Excel5.php';     // 用于其他低版本xls 
   $objExcel = new PHPExcel(); 
   $objWriter = new PHPExcel_Writer_Excel5($objExcel);     // 用于其他版本格式 
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
   	$itemstr=array( '项目' ,'行号' , '本期数','本年累计');
		
	//显式指定内容类型 
 	 
      $objSheet1->getColumnDimension('A')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('B')->setAutoSize(true);    
	  $objSheet1->getColumnDimension('C')->setAutoSize(true); 
	  $objSheet1->getColumnDimension('D')->setAutoSize(true); 
	 
	 	 
	  $objExcel->getActiveSheet()->setCellValue('A'.$k, $itemstr[0]);
      $objExcel->getActiveSheet()->setCellValue('B'.$k, $itemstr[1]);
      $objExcel->getActiveSheet()->setCellValue('C'.$k, $itemstr[2]);
      $objExcel->getActiveSheet()->setCellValue('d'.$k, $itemstr[3]);
	 
	while ($myrow = DB_fetch_array($Result) ){	
	//foreach($rptarr as $myrow){ 	 
       $k ++;
    /* @func 设置列 */
   
    $objExcel->getActiveSheet()->setCellValue('A'.$k, $myrow['title']);
    $objExcel->getActiveSheet()->setCellValue('B'.$k, $myrow['showlist']);
  
    $objExcel->getActiveSheet()->setCellValue('C'.$k, number_format($myrow['amountm'], 2, '.', ''));
    $objExcel->getActiveSheet()->setCellValue('D'.$k, number_format($myrow['amountq'], 2, '.', ''));
   
   }
   //写入类容
   $objwriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
    $outputFileName = $_SESSION['reports_dir'] . '/RevenceCost_' . Date('Y-m-d') .'.xls';
   
   $objWriter->save($outputFileName); 
   echo '<p><a href="' .  $outputFileName . '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
   }
}

echo '</div>';
echo '</form>';
include('includes/footer.php');

?>
