
<?php

/* $Id$ $Revision: 1.5 $ */
/*
 * @Author: ChengJiang 
 * @Date: 2018-03-25 07:03:50 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-05-13 19:25:09
 */

include('includes/session.php');

if (isset($_POST['JournalNo'])) {
	$str=explode('^',$_POST['JournalNo']);
}else if (isset($_GET['JournalNo'])) {
	$str=explode('^',$_GET['JournalNo']);
 
} 
if ($str!='') {
	$JournalNo=$str[1];
	$periodno=$str[0];
}

include('includes/tcpdf/PDFJournal.php');
	
	$sql="SELECT gltrans.typeno,
					systypes.typename,
					gltrans.trandate,
					gltrans.transno,
					abs(gltrans.printno) printno,
					gltrans.account,
					chartmaster.accountname,
					gltrans.narrative,
					toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
					toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
					gltrans.tag		
				FROM gltrans
				LEFT JOIN chartmaster	ON gltrans.account=chartmaster.accountcode
				LEFT JOIN tags	ON gltrans.tag=tags.tagref
				LEFT JOIN systypes	ON gltrans.type=systypes.typeid
				WHERE gltrans.periodno='".$periodno."' 	AND gltrans.transno='" . $JournalNo . "'			
				ORDER BY abs(gltrans.printno),gltrans.transno";
	

$result = DB_query($sql,$ErrMsg,_('The SQL that failed was'),true);	

$row=DB_num_rows($result);
if ($row>1){
	$sql="SELECT  confvalue FROM myconfig WHERE confname='prtformat'";
	$confresult=DB_query($sql);
	$row=DB_fetch_row($confresult);

	$prtformat = json_decode($row[0],true);
	//$prtformat=array("lp"=>"L","format"=>'A5',"top"=>5,"prtrow"=>$row);
	// create new PDF document PDF_PAGE_ORIENTATION  PDF_PAGE_FORMAT
$pdf = new MYPDF('L', PDF_UNIT, 'A5');//, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('chengjiang');
$pdf->SetTitle('会计凭证PDF');
$pdf->SetSubject('会计凭证PDF' );
$pdf->SetKeywords('会计凭证PDF');
// set default header dataPDF_HEADER_TITLE.
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
//	$pdf->setPageFormat('A5', 'P')
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT,true);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetHeaderMargin(0);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetFooterMargin(0); 
$pdf->setPrintFooter(false);
$pdf->setPrintHeader(false);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 0);//PDF_MARGIN_BOTTOM);
// set image scale factor

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/tcpdf/chi.php')) {
	require_once(dirname(__FILE__).'/tcpdf/chi.php');
	$pdf->setLanguageArray($l);
}
// set font helvetica 
$pdf->SetFont('droidsansfallback', '', 10);
// add a page
$pdf->AddPage();
// column titles
$header = array(_('Sequence'), _('Date'), '凭证字号', '摘要',_('Account Code'), '明细科目名', _('Debits'), _('Credit'));
// print colored table		
$pdf->JournalPDF($header,$result,$SelectDate,$prtformat,$_SESSION['tagsgroup']);
// END OF FILE
		ob_end_clean();
			// close and output PDF document
			$ym= date('Y-m',strtotime('-'.($_SESSION['period']-$periodno).' Month',strtotime($_SESSION['lastdate'])));
			$pdffilename=$ym.'会计凭证打印号'.$JournalNo.'.pdf';
		
			$pdf->Output($pdffilename,'D');
			$pdf->__destruct();

			$sql="update  gltrans set printno=abs(printno) where  periodno=".$periodno." and abs(printno)=".$JournalNo." printno<0";
		
			$result = DB_query($sql);
	
	//exit;
}
?>