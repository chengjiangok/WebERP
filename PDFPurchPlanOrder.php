
<?php
/* $Id: PDFOrder.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-06 09:46:34 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-10-14 06:41:45
 */
include('includes/session.php');
if (isset($_GET['D'])&&isset($_GET['F'])){
	$IssueNO=$_GET['D'];
	$IssueTyp=$_GET['F'];
}else{
	
	exit;
}
$PDFFormat=array(0=>'A5',1=>'采购计划单',2=>'14',3=>'需求部门');
$PaperSize=$PDFFormat[0];//默认页设置  
			$SQL="SELECT stockrequest.dispatchid,
			locations.locationname,
			stockmaster.categoryid loccode,
			stockrequest.despatchdate,
			stockrequest.deliverydate,
			stockrequest.narrative,
			b.realname initiatorname,
			departments.departmentid,
			departments.description departname,
			a.realname,
			stockrequestitems.stockid,
			stockrequestitems.dispatchitemsid,
			stockrequestitems.stockid,
			stockrequestitems.decimalplaces,
			stockrequestitems.uom,
			stockmaster.description,
			stockrequestitems.quantity,
			stockrequestitems.cess,
			stockrequestitems.taxprice,
			stockrequest.authorised,
			stockrequest.closed,
			stockrequestitems.completed,
			stockrequestitems.remark
		FROM  stockrequest
		INNER JOIN departments ON stockrequest.departmentid = departments.departmentid
        LEFT JOIN stockrequestitems ON stockrequestitems.dispatchid = stockrequest.dispatchid
	   LEFT JOIN stockmaster ON stockmaster.stockid = stockrequestitems.stockid
       INNER JOIN locations ON stockmaster.categoryid = locations.loccode
		INNER JOIN locationusers ON locationusers.loccode = locations.loccode AND locationusers.userid = 'lidy' AND locationusers.canupd = 1
		LEFT JOIN www_users a ON a.userid = stockrequest.audituser
		LEFT JOIN www_users b ON b.userid = stockrequest.initiator			
		WHERE stockrequest.dispatchid ='".$_GET['D']."'";
$Result=DB_query($SQL);

$departrow=DB_fetch_assoc($Result);
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
/* Prints company logo */
//$pdf->addJpegFromFile($_SESSION['LogoFile'], $XPos+20, $YPos-50, 0, 60);
$LeftOvers = $pdf->addTextWrap( $XPos, $YPos,$gettextwigth+100,$PDFFormat[2] ,$PDFFormat[1] );

$LeftOvers = $pdf->addTextWrap(20,$YPos-($lh*2),100,$FontSize,$PDFFormat[3].':'.$departrow['departname']);
$LeftOvers = $pdf->addTextWrap(($Page_Width/2-50) ,$YPos-($lh*2),100,$FontSize,'日期:'.$departrow['despatchdate']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-80,$YPos-($lh*2),200,$FontSize, 'No:' . $_GET['D']  );

//$pdf->line($Left_Margin, $YPos-($lh*2)-5,$Page_Width-$Right_Margin, $YPos-($lh*2)-5);
$n= $YPos-($lh*2)-5;
$pdf->MultiCell( 20,$lh,'序号','LRBT','J',$fill,0,$Left_Margin, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 100,$lh,'编码/物料名','RBT','J',$fill,0,$Left_Margin+21, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 20,$lh,'单位','RBT','J',$fill,0,$Left_Margin+122, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 38,$lh,'数量','RBT','J',$fill,0,$Left_Margin+143, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 21,$lh,'增值税税率','RBT','J',$fill,0,$Left_Margin+180, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 31,$lh,'单价','RBT','J',$fill,0,$Left_Margin+200, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 41,$lh,'金额','RBT','J',$fill,0,$Left_Margin+230, $lh*5,true,0,false, true,0,'M',true);
$pdf->MultiCell( 80,$lh,'备注','RBT','J',$fill,0,$Left_Margin+270, $lh*5,true,0,false, true,0,'M',true);
$sr=1;
DB_data_seek($Result,0);
$narrative='';
$totale=0;
while ($row=DB_fetch_array($Result)){
	if ($narrative==''){
		$narrative=str_replace("^","",strrchr($row['narrative'],"^"));
	}
	$pdf->MultiCell( 20,$lh,$sr,'LRB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 100,$lh,$row['stockid'].$row['description'],'RB','J',$fill,0,$Left_Margin+21, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 20,$lh,$row['uom'],'RB','J',$fill,0,$Left_Margin+122, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 38,$lh,locale_number_format($row['quantity'],2),'RB','R',$fill,0,$Left_Margin+143, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 21,$lh,(100*$row['cess']).'%','RB','R',$fill,0,$Left_Margin+180, $lh*($sr+5),true,0,false, true,0,'M',true);

	$pdf->MultiCell( 31,$lh,locale_number_format($row['taxprice'],2),'RB','R',$fill,0,$Left_Margin+200, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 41,$lh,locale_number_format(($row['quantity']*$row['taxprice']),2),'RB','R',$fill,0,$Left_Margin+230, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 80,$lh,$row['remark'],'RB','J',$fill,0,$Left_Margin+270, $lh*($sr+5),true,0,false, true,0,'M',true);
	$totale+=round($row['quantity']*$row['taxprice'],2);
	$sr++;

}  // $lh+=$lh+5;
	$pdf->MultiCell( 20,$lh+10,'摘要','LRB','J',$fill,0,$Left_Margin, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 161,$lh+10,$narrative,'LRB','J',$fill,0,$Left_Margin+20, $lh*($sr+5),true,0,false, true,0,'M',true);
	                                                                                // true,0,false, true,0,'M',true);
	//$pdf->MultiCell( 100,$lh,'','B','J',$fill,0,$Left_Margin+21, $lh*($sr),true,0,false, true,0,'M',true);
	//$pdf->MultiCell( 20,$lh+5,'合计','RB','J',$fill,0,$Left_Margin+122, $lh*($sr+5),true,0,false, true,0,'M',true);
	//$pdf->MultiCell( 38,$lh+5,locale_number_format($row['qty'],2),'RB','R',$fill,0,$Left_Margin+143, $lh*($sr+5),true,0,false, true,0,'M',true);
	//$pdf->MultiCell( 21,$lh+10,'合计','LB','R',$fill,0,$Left_Margin+181, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 51,$lh+10,'合计','RB','C',$fill,0,$Left_Margin+180, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 41,$lh+10,locale_number_format($totale,2),'RB','R',$fill,0,$Left_Margin+230, $lh*($sr+5),true,0,false, true,0,'M',true);
	$pdf->MultiCell( 80,$lh+10,'','RB','J',$fill,0,$Left_Margin+270, $lh*($sr+5),true,0,false, true,0,'M',true);
	
	
	$LeftOvers = $pdf->addTextWrap(40,$YPos-($lh*($sr+5))-5,100,$FontSize,'审批人:'.$departrow['realname']);

	$LeftOvers = $pdf->addTextWrap(170,$YPos-($lh*($sr+5))-5,100,$FontSize,'申请人:'.$departrow['initiatorname']);
	$LeftOvers = $pdf->addTextWrap(290,$YPos-($lh*($sr+5))-5,100,$FontSize,'领料人: ');
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-120,$YPos-($lh*($sr+6))-5,140,$FontSize, _('Printed').': ' . Date($_SESSION['DefaultDateFormat']) . '   '. _('Page'). ' ' . $PageNumber);
	$PageNumber++;
	ob_end_clean();
	$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');
	if(in_array($_SESSION['PageSecurityArray']['OrderEntryDiscountPricing'], $_SESSION['AllowedPageSecurityTokens'])) {//Is it an internal user with appropriate permissions?
		//$AuthorPrice=2;// Show two additional columns: 'Discount' and 'GP %'.
		$sql="UPDATE `stockrequest` SET allowprint=1 WHERE allowprint<>1 AND dispatchid = '".$_GET['D']."'";
		$result=DB_query($sql);
	} 
		
?>