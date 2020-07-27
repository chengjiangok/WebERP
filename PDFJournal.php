<?php
//============================================================+
// File name   : tcpdf_include.php
// Begin       : 2016-05-14
// Last Update : 2018-09-11
//
// Description : Search and include the TCPDF library.
//
// Author: Nicola Asuni
//
// (c) Copyright:
//               Nicola Asuni
//               Tecnick.com LTD
//               www.tecnick.com
//               info@tecnick.com
//============================================================+

/**
 * Search and include the TCPDF library.
 * @package com.tecnick.tcpdf
 * @abstract TCPDF - Include the main class.
 * @author Nicola Asuni
 * @since 2013-05-14
 */

// always load alternative config file for examples
require_once('includes/tcpdf/tcpdf.php');

// Include the main TCPDF library (search the library on the following directories).

// extend TCPF with custom functions
class MYPDF extends TCPDF {
	 //JournalPDF函数使用PDFJournalPDF替代  使用GLJournalPrint也需要升级 20200723
	 //大于15行没有完成
	// 打印行数可变改变$prtformat=10-15行 格式可变A4 A5  
	public function PDFJournal($Result,$header,$period,$prtformat,$TagsGroup) {
		// Data	
	 $fill = 0;	
	 $trn_arr=array();
	 $i=1;	
	 while(	$row = DB_fetch_array($Result)) {	
		// $TransRow[$row['transno']]+=1;
		// $PrintNo[$row['printno']][]=$row['transno'];
		 $transrow[]=$row['transno'];
		
		 $printno[]=$row['printno'];
	}
	$TransRow=array_count_values($transrow);//凭证》行
    $PrintNo=array_count_values($printno);//打印号》行
	$header = array(_('Sequence'), _('Date'), '凭证号', '摘要','附单',_('Account Code'), '总账科目/明细科目', _('Debits'), _('Credit'));
	$tagsgroup=$TagsGroup[0]; 
	// $TransRow=array_unique($trn_arr['printno']);//移除重复打印号        	
	 $w = array(8, 12,12, 30,8,25,50,20,20);
	 $num_headers = count($header); 
	 $this->SetY(10,$resetx=true,$rtloff=false);
	 $trnarr=array();
	 //$a=0;
	 $n=0;
	 $hi=$prtformat['top'];//头距离
	
	 $hs=0;
	 $hr=0;
	 $pn=1;//A4打印凭证数设置
 
	 if($prtformat['format']=="A4"){
		 $pn=2;
	 }

	foreach ($PrintNo as $key=>$val){//$printno){//以打印号循环打印	 
		//$Pval=implode(',',array_unique($val));//移除重复打印号       
	
	   $js=0;
	   $ds=0;
	   $fd=0;
	   $sql="SELECT gltrans.typeno, systypes.typename, gltrans.trandate, gltrans.transno, abs(gltrans.printno) printno, gltrans.account, chartmaster.accountname,
						gltrans.narrative, gltrans.tag, 
					toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits, 
					toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits ,
					gltrans.userid,
					chequeno
					FROM gltrans 
				LEFT JOIN chartmaster ON gltrans.account=chartmaster.accountcode 
				LEFT JOIN tags ON gltrans.tag=tags.tagref LEFT JOIN systypes ON gltrans.type=systypes.typeid 
				WHERE  gltrans.periodno=".$period." 
				AND gltrans.printno= ".$key."
				AND gltrans.tag IN  ( ".$tagsgroup." )
				  ORDER BY abs(gltrans.printno),gltrans.transno";  

	
		$glResult = DB_query($sql);
		//echo "<br/>";
		$i=0;
		$Pages=ceil( $val/15);
		$PeriodDate=PeriodGetDate($period); 
		//遍历凭证
		//for ($p=1;$p<=$Pages;$p++){
			if ($Pages==1){
				$PrintNo=$key;
			}else{
				$PrintNo=$key.".1/".$Pages;
			}
		
			$this->SetFont('droidsansfallback', 'B', 16);
			$this->MultiCell(40, 10,'会计凭证',0,'C',false,1,90, $hi,true,0,false, true,0,'M',true);
			$this->SetFillColor(255, 255, 255);
		
			$this->SetTextColor(0,0,0);
			$this->SetLineWidth(0.2);
			$this->SetFont('', 'B',10);
			$hy= $this->GetY();
			$this->MultiCell(90, 10,'单位名称:'.stripslashes($_SESSION['CompanyRecord'][1]['coyname']),0,'L',false,0,15, $hy,true,0,false, true,0,'M',true);
			$this->MultiCell(40, 10,'账期:'.substr($PeriodDate,0,7),0,'C',false,0,95, $hy,true,0,false, true,0,'M',true);
			
			$this->MultiCell(50, 10,'装订号:['.$TagsGroup[1].']'.date('m',strtotime($PeriodDate)).'-'.$PrintNo,0,'R',false,1,140, $hy,true,0,false, true,0,'M',true);		
			$this->SetX(15);
			$this->setFontSize(9);		 	
			for($j = 0; $j < $num_headers; $j++) {
				if ($j==0){
					$this->MultiCell($w[$j], 8, $header[$j],'LRTB','C',0,0,$this->GetX(),$this->GetY() ,true,0,false, true,0,'C',false);
				}else{
					$this->MultiCell($w[$j], 8, $header[$j],'RTB','C',0,0,$this->GetX(),$this->GetY() ,true,0,false, true,0,'C',false);
				}
			
				//	$this->Cell($w[$j], 8, $header[$j], 'RBT', 0, 'C', 1);
			
			}
			
			$this->Ln();		
			$hs= $this->GetY();
			$hr=$hs; 	
			$hh=0;//行合计 
			$h=6.5;
			$n=1;
			$UsersRealName='';
			$a=0;
			$p=1;
			while($row = DB_fetch_array($glResult)) {	
				$i=$i+1;
				$this->setFontSize(9);
				if (!isset($gldate[$row['transno']])){
					$gldate[$row['transno']]=array( $row['trandate'],$row['typeno'],$row['transno']);
				
					$h =$TransRow[$row['transno']]*6.5 ;
					$hh=$hh+$h;
		
					$this->MultiCell($w[0], $h ,$n,'LRB','C',$fill,0,15,$hs,true,0,false, true,0,'M',true);
					$this->MultiCell($w[1], $h,substr($gldate[$row['transno']][0],5,5),'RB','J',$fill,0,$this->GetX(), $hs,true,0,false, true,0,'M',true);
					$this->MultiCell($w[2], $h,$_SESSION['tagref'][$row['tag']][2].$gldate[$row['transno']][1].PHP_EOL. '['.$gldate[$row['transno']][2].']','RB','J',$fill,0,$this->GetX(), $hs,true,0,false, true,0,'M',true);
					$this->MultiCell($w[3], $h, $row['narrative']  ,'RB','J',$fill,0,$this->GetX(), $hs,true,0,false, true,0,'M',true);
					$this->MultiCell($w[4], $h, $row['chequeno'] ,'RB','C',$fill,0,$this->GetX(), $hs,true,0,false, true,0,'M',true);
					$hs=$hs+$h;
					$fd+= $row['chequeno'];
					$n++;
				}
				if ($UsersRealName==''){
					$UsersRealName=$row['userid'];
				}
					//$a=0;			
					$js+=$row['Debits']; 
					$ds+=$row['Credits']; 				
					$a = $a+1;				
					$this->MultiCell($w[5], 6.5, $row['account'],'RB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4], $hr,true,0,false, true,0,'M',true);
					$this->MultiCell($w[6], 6.5, $row['accountname'],'RB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5], $hr,true,0,false, true,0,'M',true);
					$this->MultiCell($w[7], 6.5,isZero($row['Debits']),'RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], $hr,true,0,false, true,0,'M',true);
					$this->MultiCell($w[8], 6.5,isZero($row['Credits']),'RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6]+$w[7], $hr,true,0,false, true,0,'M',true);
					$this->Ln();
					$hr= $this->GetY();    
					if ($Pages>1 && $a==15){
						$hi= $this->GetY(); 			
						$this->MultiCell($w[0]+$w[1]+$w[2]+$w[3], 6.5 ,'小计','LRB','C',$fill,0,15, $this->GetY(),true,0,false, true,0,'M',true);
						$this->MultiCell($w[4], 6.5, $fd,'RB','C',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3],$this->GetY() ,true,0,false, true,0,'M',true);
						$this->MultiCell($w[5], 6.5, '','RB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4],$this->GetY(),true,0,false, true,0,'M',true);
						$this->MultiCell($w[6], 6.5,'','RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5], $this->GetY(),true,0,false, true,0,'M',true);
						$this->MultiCell($w[7], 6.5,locale_number_format($js,POI),'RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], $this->GetY(),true,0,false, true,0,'M',true);
						$this->MultiCell($w[8], 6.5,locale_number_format($ds,POI),'RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6]+$w[7], $this->GetY(),true,0,false, true,0,'M',true);
						$this->Ln();
						$this->SetX(15);
						$this->Cell(50, 7, '会计主管:'.$_SESSION['CompanyRecord'][1]['regoffice4'], 0, 0, 'L', 0);
						$this->Cell(50, 7, '审核人:'.$_SESSION['CompanyRecord'][1]['regoffice4'], 0, 0, 'C', 0);
						$this->Cell(50, 7, '制单人:'.$UsersRealName , 0, 0, 'R', 0);
						$this->Ln();		
						if($prtformat['format']=="A4"){
							if($val !== end($PrintNo)) {
								if ($hi>250){		       	
									$this->AddPage();
									$hi= $prtformat['top'];//头距离;
								
								}else{
									$hi=150;
								}
								$a=0;
							//if ($hi>=136  || $hi>=166 ){
						
							}
						
						}else{
							if($val !== end($PrintNo)) {
								$this->AddPage();
								$hi=0;
								$a=0;
							
							}
						}
						$p++;
					
							$PrintNo=$key.".".$p."/".$Pages;
							
						$this->SetFont('droidsansfallback', 'B', 16);
						$this->MultiCell(40, 10,'会计凭证',0,'C',false,1,90, $hi,true,0,false, true,0,'M',true);
						$this->SetFillColor(255, 255, 255);
					
						$this->SetTextColor(0,0,0);
						$this->SetLineWidth(0.2);
						$this->SetFont('', 'B',10);
						$hy= $this->GetY();
						$this->MultiCell(90, 10,'单位名称:'.stripslashes($_SESSION['CompanyRecord'][1]['coyname']),0,'L',false,0,15, $hy,true,0,false, true,0,'M',true);
						$this->MultiCell(40, 10,'账期:'.substr($PeriodDate,0,7),0,'C',false,0,95, $hy,true,0,false, true,0,'M',true);
						
						$this->MultiCell(50, 10,'装订号:'.date('m',strtotime($PeriodDate)).'-'.$PrintNo,0,'R',false,1,150, $hy,true,0,false, true,0,'M',true);		
						$this->SetX(15);
						$this->setFontSize(9);		 	
						for($j = 0; $j < $num_headers; $j++) {
							if ($j==0){
								$this->MultiCell($w[$j], 8, $header[$j],'LRTB','C',0,0,$this->GetX(),$this->GetY() ,true,0,false, true,0,'C',false);
							}else{
								$this->MultiCell($w[$j], 8, $header[$j],'RTB','C',0,0,$this->GetX(),$this->GetY() ,true,0,false, true,0,'C',false);
							}
						
						}
						
						$this->Ln();		
						$hs= $this->GetY();
						$hr=$hs; 	

					}
			}//146tky
				$hi= $this->GetY(); 			
				$this->MultiCell($w[0]+$w[1]+$w[2]+$w[3], 6.5 ,_('Total'),'LRB','C',$fill,0,15, $this->GetY(),true,0,false, true,0,'M',true);
				$this->MultiCell($w[4], 6.5, $fd,'RB','C',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3],$this->GetY() ,true,0,false, true,0,'M',true);
				$this->MultiCell($w[5], 6.5, '','RB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4],$this->GetY(),true,0,false, true,0,'M',true);
				$this->MultiCell($w[6], 6.5,'','RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5], $this->GetY(),true,0,false, true,0,'M',true);
				$this->MultiCell($w[7], 6.5,locale_number_format($js,POI),'RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], $this->GetY(),true,0,false, true,0,'M',true);
				$this->MultiCell($w[8], 6.5,locale_number_format($ds,POI),'RB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6]+$w[7], $this->GetY(),true,0,false, true,0,'M',true);
				$this->Ln();
				$this->SetX(15);
				$this->Cell(50, 7, '会计主管:'.$_SESSION['CompanyRecord'][1]['regoffice4'], 0, 0, 'L', 0);
				$this->Cell(50, 7, '审核人:'.$_SESSION['CompanyRecord'][1]['regoffice4'], 0, 0, 'C', 0);
				$this->Cell(50, 7, '制单人:'.$UsersRealName , 0, 0, 'R', 0);
				$UsersRealName='';
				$this->Ln();
				$hi= $this->GetY();
			
				//if ($p<$Pages){
				if($prtformat['format']=="A4"){
					if($val !== end($PrintNo)) {
						if ($hi>250){		       	
							$this->AddPage();
							$hi= $prtformat['top'];//头距离;
						}else{
							if ($val==$i&& ($hi>$hy+14.5  || $hi>=166 )){
								$hi=150;
							}
						}
					}
				}else{
					if($val !== end($PrintNo)) {
						$this->AddPage();
						$hi=0;
					}
				}
				//}
				$this->SetFillColor(224, 235, 255);
				$this->SetTextColor(0);
				$this->SetFont('');
		//}//打印号遍历


	}// 打印号循环

		
	 }//pdf
	 //JournalPDF函数使用PDFJournalPDF替代  使用GLJournalPrint也需要升级 20200723
	public function JournalPDF($header,$data,$dt,$prtformat,$tagsgroup) {
	   	// Data	
		$fill = 0;	
		$trn_arr=array();
		$i=0;	
		while(	$row = DB_fetch_array($data)) {
			$trn_arr['transno'][$i]= $row['transno'];
			$trn_arr['printno'][$i]= $row['printno'];
			$trn_arr['typeno'][$i]= $row['typeno'];
			$trn_arr['typename'][$i]= $row['typename'];
			$trn_arr['trandate'][$i]= $row['trandate'];
			$trn_arr['narrative'][$i]= $row['narrative'];
			$trn_arr['account'][$i]= $row['account'];
			$trn_arr['accountname'][$i]= $row['accountname'];
			$trn_arr['Debits'][$i]= $row['Debits'];
			$trn_arr['Credits'][$i]= $row['Credits']; 
			$trn_arr['tag'][$i]=$row['tag'];
			$i=$i+1;		  
		}
		
		$TransRow=array_unique($trn_arr['printno']);//移除重复打印号        	
		$w = array(8, 19,20, 25,25,50,20,20);
		$num_headers = count($header); 
		$this->SetY(10,$resetx=true,$rtloff=false);
		$trnarr=array();
		$a=0;
		$n=0;
		$hi=$prtformat['top'];//头距离
		$hs=0;
		$hr=0;
		$pn=1;//A4打印凭证数设置
	
		if($prtformat['format']=="A4"){
			$pn=2;
		}
	
		$i=0;
		foreach ($TransRow as $printno){//以打印号循环打印	 
		 	  $js=0;
			   $ds=0;
			if($prtformat['format']=="A4"){
		 	if(($i % $pn==0 and $i>1) or $i==0 ){
		 	   $this -> SetY(0,false,true); 
		 	   $hi=0;
		 	}else{
		 	  	$hi=$hi+10;
			 }
			}else{
			   $this -> SetY(0,false,true); 
		 	   $hi=0;
			}
		 	for ($j = 0; $j< count($trn_arr['printno']); $j++){//打印号涵盖凭证号
				if ($printno==$trn_arr['printno'][$j]){ 
				
					//$trnarr[$a]=$trn_arr['typeno'][$j];
					$trnarr[$a]=$trn_arr['transno'][$j];
					$a=$a+1;
				}		 		 		
		 	}
				$a=0;
				//$trnun=array_unique($trnarr);		//去重复凭证号 		 	 
				$trnsum=array_count_values($trnarr);//函数对凭证号的所有值进行计数
				$trnkey=array_keys ($trnsum);
		
			$this->SetFont('droidsansfallback', 'B', 16);
			$this->MultiCell(40, 10,'会计凭证',0,'C',false,1,90, $hi,true,0,false, true,0,'M',true);
			$this->SetFillColor(255, 255, 255);
		
			$this->SetTextColor(0,0,0);
			$this->SetLineWidth(0.2);
			$this->SetFont('', 'B',10);
			$hy= $this->GetY();
			$this->MultiCell(90, 10,'单位名称:'.stripslashes($_SESSION['CompanyRecord'][1]['coyname']),0,'L',false,0,15, $hy,true,0,false, true,0,'M',true);
			$this->MultiCell(40, 10,'账期:'.substr($trn_arr['trandate'][0],0,7),0,'C',false,0,95, $hy,true,0,false, true,0,'M',true);
			//$this->MultiCell(50, 10,'装订号:'.date('m',strtotime($dt)).'-'.$printno,0,'R',false,1,150, $hy,true,0,false, true,0,'M',true);	
			$this->MultiCell(50, 10,'装订号:'.date('m',strtotime($dt)).'-'.$printno,0,'R',false,1,150, $hy,true,0,false, true,0,'M',true);		
			$this->SetX(15);		 	
			for($p = 0; $p < $num_headers; $p++) {
					$this->Cell($w[$p], 8, $header[$p], 1, 0, 'C', 1);
			}	 	 			
			$this->Ln();		
			$hs= $this->GetY();
			$hr=$hs; 	
			$hh=0;//行合计 
			$h=6.5;
			$n=0;
        
			$this->setFontSize(9);
			
			foreach ($trnkey as $tky){  
				$TransNo='';
		        $f=0;
				$h =$trnsum[$tky]*6.5 ;
				$hh=$hh+$trnsum[$tky];

				$f=array_search($tky,$trn_arr['transno']);
				if ($_SESSION['TagType']>=1){
					$TypeNo=$trn_arr['typeno'][$f];
				}    	
				// 				$tagsgroup[$trn_arr['tag'][$f]][1]
				$this->MultiCell($w[0], $h ,$n+1,'LRB','J',$fill,0,15,$hs,true,0,false, true,0,'M',true);
				$this->MultiCell($w[1], $h, $trn_arr['trandate'][$f],'RB','J',$fill,0,$this->GetX(), $hs,true,0,false, true,0,'M',true);
				$this->MultiCell($w[2], $h, '['.$tky.']'.$_SESSION['tagref'][$trn_arr['tag'][$f]][2].$trn_arr['typeno'][$f],'RB','J',$fill,0,$this->GetX(), $hs,true,0,false, true,0,'M',true);
				$this->MultiCell($w[3], $h, $trn_arr['narrative'][$f] ,'RB','J',$fill,0,$this->GetX(), $hs,true,0,false, true,0,'M',true);
				$hs=$hs+$h;
				$a=0;
				for ($j = 0; $j< count($trn_arr['transno']); $j++){
					if ($tky==$trn_arr['transno'][$j]){ 
						$js=$js+$trn_arr['Debits'][$j]; 
						$ds=$ds+$trn_arr['Credits'][$j]; 
						$a = $a+1;
					
						$this->MultiCell($w[4], 6.5, $trn_arr['account'][$j],'LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3], $hr,true,0,false, true,0,'M',true);
						$this->MultiCell($w[5], 6.5, $trn_arr['accountname'][$j],'LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4], $hr,true,0,false, true,0,'M',true);
						$this->MultiCell($w[6], 6.5,isZero(locale_number_format($trn_arr['Debits'][$j],POI)),'LRB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5], $hr,true,0,false, true,0,'M',true);
						$this->MultiCell($w[7], 6.5,isZero(locale_number_format($trn_arr['Credits'][$j],POI)),'LRB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], $hr,true,0,false, true,0,'M',true);
						$this->Ln();
						$hr= $this->GetY();     
					}
				}//for153
			
				$n=$n+1;
			}//146tky
	   
     
			for ($j =$hh ; $j< $prtformat['prtrow']; $j++){
				$this->MultiCell(47+$w[3], 6.5 ,'','LRB','J',$fill,0,15, $this->GetY(),true,0,false, true,0,'M',true);
				$this->MultiCell($w[4], 6.5, '','LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3],$this->GetY() ,true,0,false, true,0,'M',true);
				$this->MultiCell($w[5], 6.5, '','LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4], $this->GetY(),true,0,false, true,0,'M',true);
				$this->MultiCell($w[6], 6.5, '','LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5], $this->GetY(),true,0,false, true,0,'M',true);
				$this->MultiCell($w[7], 6.5, '','LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], $this->GetY(),true,0,false, true,0,'M',true);
				$this->Ln();	
				$n=$n+1;
		
			} 
	  	
			$this->MultiCell(47+$w[3], 6.5 ,'','LRB','J',$fill,0,15, $this->GetY(),true,0,false, true,0,'M',true);
			$this->MultiCell($w[4], 6.5, '','LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3],$this->GetY() ,true,0,false, true,0,'M',true);
			$this->MultiCell($w[5], 6.5, _('Total'),'LRB','J',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4],$this->GetY(),true,0,false, true,0,'M',true);
			$this->MultiCell($w[6], 6.5,isZero(locale_number_format($js,POI)),'LRB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5], $this->GetY(),true,0,false, true,0,'M',true);
			$this->MultiCell($w[7], 6.5, isZero(locale_number_format($ds,POI)),'LRB','R',$fill,0,15+$w[0]+$w[1]+$w[2]+$w[3]+$w[4]+$w[5]+$w[6], $this->GetY(),true,0,false, true,0,'M',true);
			$this->Ln();
			$this->SetX(15);
		    $this->Cell(50, 7, '会计主管:', 0, 0, 'L', 0);
		    $this->Cell(50, 7, '审核人:', 0, 0, 'C', 0);
			$this->Cell(50, 7, '制单人:' , 0, 0, 'R', 0);
		    $this->Ln();
		
			unset($trnarr);
			//unset($trnun);
			unset($trnsum);
			unset($trnkey);	    
			$hi= $this->GetY();  
			$i=$i+1;
			if($printno != end($TransRow)) {
				if($prtformat['format']=="A4"){
					if($i%$pn==0){		       	
						$this->AddPage();
						$hi= $this->GetY();
					}
				}else{
					$this->AddPage();
					$hi=0;
				}
			}
		}//以打印号循环打印	
			// Color and font restoration
			$this->SetFillColor(224, 235, 255);
			$this->SetTextColor(0);
			$this->SetFont('');
	}//pdf
}//class


//============================================================+
// END OF FILE
//============================================================+
?>