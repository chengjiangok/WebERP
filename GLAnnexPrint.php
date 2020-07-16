<?php
/*$ID AccountPring.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-04-18 19:37:04 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-03-18 16:11:54
 */
include ('includes/session.php');
$Title = '账表附件打印';
$ViewTopic= 'GeneralLedger';
$BookMark = 'GLJournalPrint';

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if(!isset($_POST['periodyear'])){
	$_POST['periodyear']=substr($_SESSION['lastdate'],0,4).'^'.$_SESSION['janr'];
}
echo'<script type="text/javascript">
	function firm(obj) {  
        //利用对话框返回的值 （true 或者 false）  
        if (confirm("你确定提交吗？")) {  
            alert("点击了确定");  
        }  
        else {  
            alert("点击了取消");  
        }  
  
    }  		
	window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . ' ;
 </script>';
$prtyear=explode('^',$_POST['periodyear'])[0];
$prtjanr=explode('^',$_POST['periodyear'])[1];

if (!isset($_POST['Print'])){
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />	
	   	';
		$prtauto=0;//选择打印
	echo '<table class="selection">'; 				 
	echo '<tr>
			 <td>选择期间</td>
			 <td>';
				 SelectPeriod($_SESSION['period'],$_SESSION['janr']);
	echo '</td></tr>';
	$annex=array(0=>"会计凭证附件",1=>"会计凭证图片",2=>"报表封面");	
	echo '<tr>
	    	<td>' . _('Account Code') . ':</td>
			<td><select name="Annex" >'	;
    foreach($annex as $key=>$row){ 
  	 //echo '<option selected="selected"   value="'.$key.'">'.$row'</option>';
	   echo '<option  value="'.$key.'">'.$row.'</option>';
	}
	
	echo '</select></td></tr>';
	 echo '<tr><td>方式:</td><td>
	  		<input type="radio" name="prttyp" value="0"'.($_POST['prttyp']==0 ? 'checked':"").' />全部
            <input type="radio" name="prttyp" value="1"  '.($_POST['prttyp']==1 ? 'checked':"").' />发生
		   
              </td>
			  </tr>';
	     
 	echo '</table>';
 	
	echo '<div class="centre">
	<input type="submit" name="Search" value="查询" />
	<input type="submit" name="Print"   onChange="firm(this)" value="' . _('Print'). '" />
	            ';
	         
	echo'</div>';   
}	
  
   //读取有附件的凭证号
   $SQL="SELECT DISTINCT	transno,-1  dc,LEFT(account,4) account
			FROM	`gltrans`
			WHERE	(LEFT(account,4) IN (SELECT account FROM `glannex` WHERE `annextype`=0  AND  debitcredit=-1) 
				AND((flg = 1 AND amount < 0) OR(flg = -1 AND amount > 0))  ) AND periodno  =".$_POST['ERPPrd']." 
		UNION
		SELECT DISTINCT	 transno,1 dc,LEFT(account,4) account
			FROM	`gltrans`
			WHERE   (LEFT(account,4) IN (SELECT account FROM `glannex` WHERE `annextype`=0  AND  debitcredit=1)   
					AND((flg = 1 AND amount > 0) OR(flg = -1 AND amount < 0)) ) 
				AND periodno  =".$_POST['ERPPrd']." ";
	$result =DB_query($SQL);
	//prnMsg($SQL);
	while($row = DB_fetch_array($result)){
		$KeyTrans[$row['account']][]=array($row['transno'],$row['dc']);
	}
	
	//读取有附件的凭证内容
	$SQ="SELECT DISTINCT	CONCAT(periodno, transno)
			FROM  `gltrans`
			WHERE  (LEFT(account,4) IN (SELECT account FROM `glannex` WHERE `annextype`=0  AND  debitcredit=-1) 
				AND((flg = 1 AND amount < 0) OR(flg = -1 AND amount > 0))  ) AND periodno  =".$_POST['ERPPrd']." 
		UNION
		SELECT DISTINCT	CONCAT(periodno, transno)
			FROM	`gltrans`
			WHERE   (LEFT(account,4) IN (SELECT account FROM `glannex` WHERE `annextype`=0  AND  debitcredit=1)   
					AND((flg = 1 AND amount > 0) OR(flg = -1 AND amount < 0)) ) 
				AND periodno  =".$_POST['ERPPrd']." ";
	$SQL="SELECT  gltrans.tag,
					periodno,
					transno,
					account,					
					accountname,
					narrative,
					SUM(TOAMOUNT(amount,-1,0,0,1,flg)) debit,
					SUM(toamount(amount,-1,0,0,-1,flg)) credit 
					FROM`gltrans` 
					LEFT JOIN chartmaster ON gltrans.account=accountcode
					WHERE	CONCAT(periodno, transno) IN(".$SQ.") AND periodno  =".$_POST['ERPPrd']." 
					GROUP BY gltrans.tag,	periodno,transno,account,  accountname
					ORDER BY gltrans.tag, periodno,transno,account,narrative";
		//		prnMsg($SQL);
	$result =DB_query($SQL);
	while($row = DB_fetch_array($result)){
		$TransData[$row['transno']][]=array("account"=>$row['account'],"debit"=>$row['debit'],"credit"=>$row['credit'],'narrative'=>$row['narrative']);
	}
	//附件格式读取
	$sql="SELECT `annexname`,account, `papersize`, `paperorientation`, `margintop`, `marginbottom` FROM `glannex` WHERE annextype<5";
	$result =DB_query($sql);
	while($row = DB_fetch_array($result)){
		$AnnexRow[]=$row;
	}
   //var_dump($TransData[198]);
   //var_dump($KeyTrans['1602']);
if(isset($_POST['Search'])) { 				
     //var_dump($AnnexRow);
	echo '<table class="selection">';
	echo '<tr>		
			<th>项目名</th>
			<th>科目</th>			
			<th>凭证号</th>
			<th>' . _('Sequence') . '</th>
			<th>科目名称</th>
			<th>借方金额</th>
			<th>贷方金额</th>
			<th>备注</th>
			<th>附件链接</th>						
			<th>' . _('Print').' </th>	
			<th>' . _('flag').' </th>				
		</tr>';		
	$k=1;	
	foreach($AnnexRow  as  $row){	
		$rw=0;
		//读取凭证行数
		foreach($KeyTrans[$row['account']] as $val){
			$rw+=count($TransData[$val[0]]);
		}
		if ($rw==0){
			$rw=1;
		}
		if ($kk==1){
			echo '<tr class="EvenTableRows">';
			$kk=0;
		} else {
			echo '<tr class="OddTableRows">';
			$kk=1;
		}   			
		echo '<td rowspan="'.$rw.'">'.$rw . $row['annexname'] . '</td>				 
			  <td rowspan="'.$rw.'">' . $row['account'] . '</td>'  ;
		$ActType='';
		if ($rw>1){
		
			//依据凭证号遍历
			foreach($KeyTrans[$row['account']]  as $transrow){
				$glrw=1;
				$TransType=0;
				$r=1;
				foreach($TransData[$transrow[0] ] as $key=>$glrow){//遍历凭证内容
					$glrw=count($TransData[$transrow[0] ]);

					if ($ActType==''||$ActType!=$row['account']){
						if ($TransType==0){
							echo'<td rowspan="'.$glrw.'">' .$transrow[0]. '</td>';
							
						}
						//else{
							echo'<td>' .$r. '</td>
								<td>'.$glrow['account'].'</td>
								<td class="number">' . $glrow['debit']  . '</td>
								<td class="number">'.$glrow['credit'].'</td> 
								<td >' . $glrow['narrative'] . '</td>';
							echo'<td >' .$row['pagecount']. '</td>
								<td ><a href="PDFAnnex.php?Tran='.$row['transno'].'">' . _('Print')  . '</a></td>
								<td ><input type="checkbox" name="chkbx[]" value="'. $row['account'].'" ></td>
							</tr>';
							$r++;
						$ActType=$row['account'];
					}else{
						/*
						if ($TransType==0){
							echo'<td>' .$r. '</td>
								<td>'.$glrow['account'] .'</td>
								<td>' . $glrow['debit']  . '</td>
								<td>'.$glrow['credit'].'</td> 
								<td class="number">' . $row['prtcount'] . '</td>';
							echo'<td class="number">' .$row['pagecount']. '</td>
								<td ><a href="PDFAnnex.php?Tran='.$row['transno'].'">' . _('Print')  . '</a></td>
								<td ><input type="checkbox" name="chkbx[]" value="'. $row['account'].'" ></td></tr>
							</tr>';
							$TransType=$transrow[0];
							$r++;
						}*/
			
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}   					
						
						echo'<td>' .$r. '</td>
							<td>'.$glrow['account'].'</td>
							<td class="number">' . $glrow['debit']  . '</td>
							<td class="number">'.$glrow['credit'].'</td> 
							<td >' . $glrow['narrative'] . '</td>';
						echo'<td >' .$row['pagecount']. '</td>
							<td ><a href="PDFAnnex.php?Tran='.$row['transno'].'">' . _('Print')  . '</a></td>
							<td ><input type="checkbox" name="chkbx[]" value="'. $row['account'].'" ></td>
						</tr>';
						$r++;
					}
				
				}
				$ActType='';
				
			}
		}else{
			echo'<td ></td>';			
		
			echo'<td></td>
				<td></td>
				<td class="number"></td>
				<td class="number"></td> 
				<td ></td>';
			echo'<td ></td>
			<td ><a href="PDFAnnex.php?Tran='.$row['transno'].'">' . _('Print')  . '</a></td>
			<td ><input type="checkbox" name="chkbx[]" value="'. $row['account'].'" ></td>
			</tr>';

		}

	}	

		echo '</table>';
	
}elseif(isset($_POST['Print'])) {
    //include('includes/header.php');   
        $sql="SELECT  `annexname`,  `account`, `debitcredit`, `papersize`, `paperorientation`, `margintop`, `marginbottom`, `marginleft`, `marginright`, `titledesc`, `title1font`, `titlefontsize`, `colwidth` ,coltitle
		      FROM `glannex` WHERE `annextype`<=5";
	
		$result =DB_query($sql);
		while($row = DB_fetch_array($result)){
			$FormatPDF[$row['account']]=array("papersize"=>$row['papersize'],"orientation"=>$row['paperorientation'],"margintop"=>$row['margintop'],'marginbottom'=>$row['marginbottom'],'marginleft'=>$row['marginleft'],'marginright'=>$row['marginright'],'titlefontsize'=>$row['titlefontsize'],
			"titledesc"=>$row['titledesc'],"colwidth"=>$row['colwidth'],"coltitle"=>$row["coltitle"]);
		} 
		//require_once('includes/tcpdf/tcpdf.php');
		include('includes/tcpdf/PDFAnnex.php');
		$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
		
		//$pdf = new MYPDF("P", PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('WebERP');
		$pdf->SetTitle('附件打印');
		$pdf->SetSubject('附件打印' );
		$pdf->SetKeywords('TCPDF, PDF');
		// set default header dataPDF_HEADER_TITLE.
		$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
		// set header and footer fonts
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set margins
		
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
		//$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/tcpdf/chi.php')) {
			require_once(dirname(__FILE__).'/tcpdf/chi.php');
			$pdf->setLanguageArray($l);
		}
		// ---------------------------------------------------------
		// set font helvetica 
		$pdf->SetFont('droidsansfallback', '', 10);
		// add a page
		$pdf->AddPage();
		$dt='2020-01-12';
		$pdf->AnnexPDF($AnnexRow,$FormatPDF,$KeyTrans,$TransData,$dt);
		//$pdf->DemoPDF($yy);
		//============================================================+
		// END OF FILE
		//============================================================+	
		//date_default_timezone_set("PRC");	
		ob_end_clean();
	
		$pdf->Output( '凭证附件PDF.pdf','D');
		//PDF输出的方式。I，默认值，在浏览器中打开；D，点击下载按钮， PDF文件会被下载下来；F，文件会被保存在服务器中；S，PDF会以字符串形式输出；E：PDF以邮件的附件输出。 
		$pdf->__destruct();
		//$sql="SELECT `periodno` FROM `periods` WHERE YEAR(lastdate_in_period)=".$prtyear." AND periodno<=".$_SESSION['period']. " ORDER BY periodno DESC LIMIT 1";
		//$result=DB_query($sql);
		//$nowprd=DB_fetch_row($result)[0];
		
		//$sql="UPDATE `myconfig` SET   `confvalue`='".$nowprd."' WHERE confname='printprd' AND confvalue<".$nowprd;
		//		$result=DB_query($sql);
		
  		exit;
	  		
		//  header('Location:AccountPrint.php?msg="ok"');	
	  
}	
  
echo '</form>';
include('includes/footer.php');

?>