
<?php
/* $Id: PDFPurchOrder.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-06 09:46:34 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-08-14 13:41:47
 */
include('includes/session.php');
if (isset($_GET['D'])&&isset($_GET['F'])){
	$IssueNO=$_GET['D'];
	$IssueTyp=$_GET['F'];
}else{
	
	exit;
}
if ($IssueTyp=="P"||$IssueTyp==17){
	$OrderType=17;
	$PDFFormat=array(0=>'A5_Landscape',1=>'外购入库单',2=>'14',3=>'供应商');
}elseif ($IssueTyp==45||$IssueTyp=="Y"){
	$OrderType=45;

     $PDFFormat=array(0=>'A5_Landscape',1=>'外购简易入库单',2=>'14',3=>'供应商');
}elseif ($IssueTyp==25){
	$OrderType=25;
     $PDFFormat=array(0=>'A5_Landscape',1=>'采购合同入库单',2=>'14',3=>'供应商');
}
$PaperSize=$PDFFormat[0];//默认页设置  
//$DocumentOrientation ='L';
	$SQL="SELECT a.stockid,
				b.description,
				loccode,
				trandate,
				a.userid,				
				price,
				prd,
				b.units,
				debtorno supplierid,
				reference,								
				qty,
				discountpercent,
				standardcost,
				narrative,				
				custname ,
				transno,
				a.connectid	,
				show_on_inv_crds		
		FROM  stockmoves a
		LEFT JOIN stockmaster b ON   a.stockid = b.stockid
		LEFT JOIN custname_reg_sub e ON   e.regid = debtorno		
		WHERE type IN (".$OrderType.") AND transno = '".$_GET['D']."'
		ORDER BY transno";
		//LEFT JOIN  stockrequestitems c ON a.connectid=dispatchid AND a.stockid=c.stockid AND 	
		//	LEFT JOIN stockrequestitems c ON    c.dispatchid = connectid AND c.stockid = a.stockid
		//	LEFT JOIN www_users d ON a.userid=d.userid
		//	LEFT JOIN grns c ON a.transno=c.grnbatch
	
	$Result=DB_query($SQL);
	$departrow=DB_fetch_assoc($Result);
	//读取计划数\一入库数
	$SQL="SELECT  `realname` FROM `www_users` WHERE userid='".$departrow['userid']."'";
	$result=DB_query($SQL);
	$namerow=DB_fetch_assoc($result);

	//prnMsg($SQL);
	$PaperSize= 'A5_Landscape';
	include('includes/PDFStarter.php');

	$FontSize=10;
	$pdf->addInfo('Title', $PDFFormat[1]);

	$PageNumber=1;
	$lh=17;
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$gettextwigth=$pdf->GetStringWidth($PDFFormat[1] ,'','', $PDFFormat[2] );
	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;
	$XPos=round(($Page_Width-$gettextwigth)/2,0)-$Left_Margin;
	$fill = 0;
	//$hs=0;

	$LeftOvers = $pdf->addTextWrap( $XPos, $YPos,$gettextwigth+100,$PDFFormat[2] ,$PDFFormat[1] );
	$LeftOvers = $pdf->addTextWrap(35,$YPos-($lh*2),120,$FontSize,$PDFFormat[3].':'.$departrow['custname']);
	$LeftOvers = $pdf->addTextWrap(($Page_Width/2-50) ,$YPos-($lh*2),100,$FontSize,'日期:'.$departrow['trandate']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-100,$YPos-($lh*2),200,$FontSize, '入库单号:' . $_GET['D']  );
	$FontSize=14;
	$YPos=$YPos-$hl;
	
	$pdf->SetY($pdf->GetY()-$lh, true, false);
	$pdf->MultiCell( 20,$lh,'序号','LRBT','C',$fill,0,$Left_Margin, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 120,$lh,'编码/物料名','RBT','C',$fill,0,$Left_Margin+21, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 25,$lh,'单位','RBT','C',$fill,0,$Left_Margin+142, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'入库数量','RBT','C',$fill,0,$Left_Margin+168, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'订单价','RBT','C',$fill,0,$Left_Margin+209, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 60,$lh,'金额','RBT','C',$fill,0,$Left_Margin+250, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'计划数','RBT','C',$fill,0,$Left_Margin+311, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'已入库数','RBT','C',$fill,0,$Left_Margin+352, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'计划单号','RBT','C',$fill,0,$Left_Margin+393, $lh*5,true,0,false, true,0,'M',true);
	$pdf->MultiCell( 70,$lh,'备注','RBT','C',$fill,0,$Left_Margin+434, $lh*5,true,0,false, true,0,'M',true);
	$sr=1;
	DB_data_seek($Result,0);
	$TotalAmo=0;
	$FontSize=10;
	$stockid='';
	$connectid='';
while ($row=DB_fetch_array($Result)){
	$TotalAmo+=round($row['qty']*$row['price'],2);
	$SQL="SELECT `quantity`, `decimalplaces`, `remark`, `completed` FROM `stockrequestitems` WHERE `dispatchitemsid`='".$row['show_on_inv_crds']."'AND  `dispatchid`='".$row['connectid']."' AND `stockid`='".$row['stockid']."' "; 
	$result=DB_query($SQL);
	if (DB_num_rows($result)>0){
		$RemarkRow=DB_fetch_assoc($result);
		$remark=$RemarkRow['remark'];
	}else{
		$remark='';
	}
    if ($stockid!=$row['stockid']||$connectid!=$row['connectid']){	
		//读取计划数\一入库数
		$SQL="SELECT stockid, sum(quantity) quantity, sum(qtydelivered) qtydelivered  FROM stockrequestitems WHERE  dispatchid='".$row['connectid']."' AND stockid='".$row['stockid']."'  GROUP BY stockid";
		$result=DB_query($SQL);
		$stockrow=DB_fetch_assoc($result);
	
	
		$pdf->MultiCell( 20,$lh,$sr,'LRB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 120,$lh,$row['stockid'].$row['description'],'RB','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 25,$lh,$row['units'],'RB','J',$fill,0,$Left_Margin+142, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format($row['qty'],2),'RB','R',$fill,0,$Left_Margin+168, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format($row['price'],2),'RB','R',$fill,0,$Left_Margin+209, $lh*($sr+5),true,0,false, true,0,'M',true);
		
		$pdf->MultiCell( 60,$lh,locale_number_format($row['qty']*$row['price'],2),'RB','R',$fill,0,$Left_Margin+250, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format($stockrow['quantity'],2),'RB','R',$fill,0,$Left_Margin+311, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format(($stockrow['qtydelivered']),2),'RB','R',$fill,0,$Left_Margin+352, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,$row['connectid'],'RB','R',$fill,0,$Left_Margin+393, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 70,$lh,$remark,'RB','J',$fill,0,$Left_Margin+434, $lh*($sr+5),true,0,false, true,0,'M',true);
		$stockid=$row['stockid'];
		$connectid=$row['connectid'];
	}else{
		$pdf->MultiCell( 20,$lh,$sr,'LRB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 120,$lh,$row['stockid'].$row['description'],'RB','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 25,$lh,$row['units'],'RB','J',$fill,0,$Left_Margin+142, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format($row['qty'],2),'RB','R',$fill,0,$Left_Margin+168, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,locale_number_format($row['price'],2),'RB','R',$fill,0,$Left_Margin+209, $lh*($sr+5),true,0,false, true,0,'M',true);
		
		$pdf->MultiCell( 60,$lh,locale_number_format($row['qty']*$row['price'],2),'RB','R',$fill,0,$Left_Margin+250, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+311, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+352, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 40,$lh,$row['connectid'],'RB','R',$fill,0,$Left_Margin+393, $lh*($sr+5),true,0,false, true,0,'M',true);
		$pdf->MultiCell( 70,$lh,$remark,'RB','J',$fill,0,$Left_Margin+434, $lh*($sr+5),true,0,false, true,0,'M',true);
	}
	$sr++;

}
	$pdf->MultiCell( 20,$lh,'','LB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 120,$lh,'','B','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 25,$lh,'合计','RB','J',$fill,0,$Left_Margin+142, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,locale_number_format($row['qty'],2),'RB','R',$fill,0,$Left_Margin+168, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+209, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 60,$lh,locale_number_format($TotalAmo,2),'RB','R',$fill,0,$Left_Margin+250, $lh*($sr+5),true,0,false, true,0,'M',true);
    $pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+311, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,locale_number_format(($row['qty']*$row['price']),2),'RB','R',$fill,0,$Left_Margin+352, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 40,$lh,'','RB','R',$fill,0,$Left_Margin+393, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 70,$lh,'','RB','J',$fill,0,$Left_Margin+434, $lh*($sr+5),true,0,false, true,0,'M',true);
	$LeftOvers = $pdf->addTextWrap(40,$YPos-($lh*($sr+5)),100,$FontSize,'入库仓管:'.$namerow['realname']);
	$LeftOvers = $pdf->addTextWrap(170,$YPos-($lh*($sr+5)),150,$FontSize,'经手人: ');

	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos-($lh*($sr+5)),140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);
	//$YPos =$lh*6;

	$PageNumber++;
	ob_end_clean();
	$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');
	


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

?>