
<?php
/* $Id: PDFIssueOrder.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-06 09:46:34 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-06 16:37:52
 */
include('includes/session.php');
if (isset($_GET['D'])&&isset($_GET['F'])){
	$IssueNO=$_GET['D'];
	$IssueTyp=$_GET['F'];
}else{
	
	exit;
}
if ($IssueTyp=="W"){
	$OrderType=28;
	$PDFFormat=array(0=>'A5_Landscape',1=>'生产发料单',2=>'14',3=>'工作单');
	
}elseif ($IssueTyp=="Y"){
	$OrderType=39;
	$PDFFormat=array(0=>'A5_Landscape',1=>'易耗品发料单',2=>'14',3=>'领用部门');

    // $PDFFormat=array(0=>'A5_Landscape',1=>'简易收货单',2=>'14',3=>'供应商');
}

$PaperSize=$PDFFormat[0];//默认页设置  
$SQL="SELECT  a.stockid,
				b.description,
				loccode,
				trandate,
				a.userid,
				d.realname,
				price,
				prd,
				b.units,
				debtorno,
				reference,
				c.description departname,
				qty,
				discountpercent,
				standardcost,
				narrative,
				connectid,
				b.decimalplaces
				FROM  stockmoves a
				LEFT JOIN stockmaster b ON   a.stockid = b.stockid
				LEFT JOIN departments c ON a.connectid=c.departmentid
				LEFT JOIN www_users d ON a.userid=d.userid
				WHERE type='".$OrderType."' AND transno = '".$_GET['D']."'
				ORDER BY transno";
$Result=DB_query($SQL);
$stockrow=DB_fetch_assoc($Result);
if ($IssueTyp=="W"){

	$unitname=" ".$stockrow['connectid']."_产品编码".$_GET['StockID'];
}else{

	$unitname=$stockrow['departname'];
    // $PDFFormat=array(0=>'A5_Landscape',1=>'简易收货单',2=>'14',3=>'供应商');
}
include('includes/PDFStarter.php');

$FontSize=10;
$pdf->addInfo('Title', $PDFFormat[1] );

$PageNumber=1;
$lh=12;
if ($PageNumber>1){
	$pdf->newPage();
}
$gettextwigth=$pdf->GetStringWidth($PDFFormat[1] ,'','', $PDFFormat[2] );
$FontSize=10;
$YPos= $Page_Height-$Top_Margin;
$XPos=round(($Page_Width-$gettextwigth)/2,0)-$Left_Margin;
$fill = 0;
$hs=0;


$LeftOvers = $pdf->addTextWrap( $XPos, $YPos,$gettextwigth+100,$PDFFormat[2] ,$PDFFormat[1] );
$LeftOvers = $pdf->addTextWrap(35,$YPos-($lh*2),120,$FontSize,$PDFFormat[3].':'.$unitname);
$LeftOvers = $pdf->addTextWrap(($Page_Width/2-50) ,$YPos-($lh*2),100,$FontSize,'日期:'.$stockrow['trandate']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100,$YPos-($lh*2),200,$FontSize, '单号:' . $_GET['D']  );
$FontSize=14;

$n= $YPos-($lh*2)-5;
$pdf->MultiCell( 20,$lh,'序号','LRBT','C',$fill,0,$Left_Margin, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 120,$lh,'编码/物料名','RBT','C',$fill,0,$Left_Margin+21, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 25,$lh,'单位','RBT','C',$fill,0,$Left_Margin+142, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 40,$lh,'需求数量','RBT','C',$fill,0,$Left_Margin+168, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 40,$lh,'成本单价','RBT','C',$fill,0,$Left_Margin+209, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 60,$lh,'已发数量','RBT','C',$fill,0,$Left_Margin+250, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 40,$lh,'已发成本','RBT','C',$fill,0,$Left_Margin+311, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 40,$lh,'发料数量','RBT','C',$fill,0,$Left_Margin+352, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 40,$lh,'发料成本','RBT','C',$fill,0,$Left_Margin+393, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 70,$lh,'备注','RBT','C',$fill,0,$Left_Margin+434, $lh*5,true,0,false, true,0,'M',true);
$sr=1;
DB_data_seek($Result,0);
$TotalAmo=0;
	$FontSize=10;
	$stockid='';
	$connectid='';
while ($row=DB_fetch_array($Result)){
	$sql="SELECT  SUM(qty) qty  ,SUM(qty*price) issuecost FROM stockmoves WHERE type='".$OrderType."' AND connectid='".$_GET['D']."'  AND stockid='".$row['stockid']."'";
	$result=DB_query($sql);
	$qtyrow=DB_fetch_assoc($result);
		$pdf->MultiCell( 20,$lh,$sr,'LRB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 120,$lh,$row['stockid'].$row['description'],'RB','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 25,$lh,$row['units'],'RB','J',$fill,0,$Left_Margin+142, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format('',$row['decimalplaces']),'RB','R',$fill,0,$Left_Margin+168, $lh*($sr+5),true,0,false, true,0,'M',true);
		//$pdf->MultiCell( 40,$lh,locale_number_format($row['qty'],$row['decimalplaces']),'RB','R',$fill,0,$Left_Margin+168, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format($row['price'],2),'RB','R',$fill,0,$Left_Margin+209, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 60,$lh,locale_number_format(-$qtyrow['qty'],$row['decimalplaces']),'RB','R',$fill,0,$Left_Margin+250, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,$qtyrow['issuecost'],'RB','R',$fill,0,$Left_Margin+311, $lh*($sr+5),true,0,false, true,0,'M',true);
	
		//$stockrow['qtydelivered']
		$pdf->MultiCell( 40,$lh,locale_number_format(-$row['qty'],$row['decimalplaces']),'RB','R',$fill,0,$Left_Margin+352, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format((-$row['qty']*$row['price']),$_SESSION['StandardCostDecimalPlaces']),'RB','R',$fill,0,$Left_Margin+393, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 70,$lh,$remark,'RB','J',$fill,0,$Left_Margin+434, $lh*($sr+5),true,0,false, true,0,'M',true);
		$stockid=$row['stockid'];
		$connectid=$row['connectid'];
        $TotalAmo+=locale_number_format((-$row['qty']*$row['price']),$_SESSION['StandardCostDecimalPlaces']);
	$sr++;

}
	$sql="SELECT contact FROM `locations` WHERE loccode='".$stockrow['loccode']."'";
	$result=DB_query($sql);
	$sfkprow=DB_fetch_assoc($result);
	$pdf->MultiCell( 20,$lh,'','LB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 120,$lh,'','B','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 25,$lh,'合计','RB','J',$fill,0,$Left_Margin+142, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,locale_number_format($row['qty'],2),'RB','R',$fill,0,$Left_Margin+168, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+209, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 60,$lh,'','RB','R',$fill,0,$Left_Margin+250, $lh*($sr+5),true,0,false, true,0,'M',true);
    $pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+311, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+352, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,locale_number_format($TotalAmo,$_SESSION['StandardCostDecimalPlaces']),'RB','R',$fill,0,$Left_Margin+393, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 70,$lh,'','RB','J',$fill,0,$Left_Margin+434, $lh*($sr+5),true,0,false, true,0,'M',true);
	$LeftOvers = $pdf->addTextWrap(40,$YPos-($lh*($sr+5)),100,$FontSize,'入库仓管:'.$sfkprow['contcat']);
	$LeftOvers = $pdf->addTextWrap(170,$YPos-($lh*($sr+5)),150,$FontSize,'经手人: ');

	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos-($lh*($sr+5)),140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);
	

	$PageNumber++;
/*
$TotalAmo=0;
$FontSize=10;
$stockid='';
$connectid='';
$sr=1;
DB_data_seek($Result,0);
while ($row=DB_fetch_array($Result)){
	$pdf->MultiCell( 20,$lh,$sr,'LRB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 100,$lh,$row['stockid'].$row['description'],'RB','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 30,$lh,$row['units'],'RB','J',$fill,0,$Left_Margin+122, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,locale_number_format($row['qty'],2),'RB','R',$fill,0,$Left_Margin+153, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,locale_number_format($row['price'],2),'RB','R',$fill,0,$Left_Margin+194, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 50,$lh,locale_number_format(($row['qty']*$row['price']),2),'RB','R',$fill,0,$Left_Margin+235, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 50,$lh,$row['narrative'],'RB','J',$fill,0,$Left_Margin+286, $lh*($sr+5),true,0,false, true,0,'M',true);
	$sr++;

}
$pdf->MultiCell( 20,$lh,'','LB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 100,$lh,'','B','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 30,$lh,'合计','RB','J',$fill,0,$Left_Margin+122, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,locale_number_format($row['qty'],2),'RB','R',$fill,0,$Left_Margin+153, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+194, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 50,$lh,locale_number_format(($row['qty']*$row['price']),2),'RB','R',$fill,0,$Left_Margin+235, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 50,$lh,$row['narrative'],'RB','J',$fill,0,$Left_Margin+286, $lh*($sr+5),true,0,false, true,0,'M',true);
$LeftOvers = $pdf->addTextWrap(40,$YPos-($lh*($sr+5)),100,$FontSize,'出库仓管:'.$stockrow['realname']);
$LeftOvers = $pdf->addTextWrap(170,$YPos-($lh*($sr+5)),100,$FontSize,'领料人: ');
	
	
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos-($lh*($sr+5)),140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);



*/

$PageNumber++;



//$LeftOvers = $pdf->addTextWrap(50,$YPos,300,$FontSize, _('The Sum Of').' :');
//include('includes/CurrenciesArray.php'); // To get the currency name from the currency code.
//$LeftOvers = $pdf->addTextWrap(150,$YPos,300,$FontSize, locale_number_format(-$Amount,$DecimalPlaces).' '. $CurrencyCode . '-' . $CurrencyName[$CurrencyCode]);

//$YPos=$YPos-($lh*2);

//$LeftOvers = $pdf->addTextWrap(50,$YPos,200,$FontSize, _('Details').' :');
//$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize, $Narrative);

//YPos=$YPos-($lh*8);

//$LeftOvers = $pdf->addTextWrap(50,$YPos,200,$FontSize,_('Signed On Behalf Of').' :     '.$_SESSION['CompanyRecord']['coyname']);

//$YPos=$YPos-($lh*10);

//$LeftOvers = $pdf->addTextWrap(50,$YPos,200,$FontSize,'______________________________________________________________________________');
ob_end_clean();
$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');
?>