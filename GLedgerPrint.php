<?php
/*$ID LedgerPrint.php " 2017-01-03 15:07:43 ChengJiang $*/
/*
* @Author: ChengJiang 
* @Date: 2017-10-22 11:37:24 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-10-22 13:32:51
*/
include ('includes/session.php');
$Title = '总账打印';
$ViewTopic= 'GeneralLedger';
$BookMark = 'GLJournalPrint';

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
if (!isset($_POST['printacc'])){
   $_POST['printacc']='P';		 		
}	 	
if (!isset($_POST['sltprt'])){
   $_POST['sltprt']=1;		 		
	}
if (!isset($_POST['prttyp'])){
   $_POST['prttyp']=0;		 		
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


	//检测明细账是否打印或全部打印
	/*
	$sql="SELECT COUNT(*) FROM accountprint WHERE periodyear='".$prtyear."'  AND prttype=0 ";
	$result=DB_query($sql);
	$row=DB_fetch_row($result);*/
	$SQL="SELECT  confvalue FROM myconfig WHERE confname='printprd'";
	$Result=DB_query($SQL);
	$ROW=DB_fetch_row($Result);
if (isset($_POST['Print'])&& $prtjanr+11>$ROW[0]){
		//if ($prtjanr+11>$ROW[0]){
			include('includes/header.php');	
			echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';	
				/*
		if ( $row[0]==0){
			prnMsg('明细账没有打印,不能打印总账！','warn');
		}*/
		//'if ($prtjanr+11>$ROW[0]){
			prnMsg('你选择的年度总账打印,因明细账没有打印或全部打印,不能打印总账！','warn');
		
		//if ($prtjanr+11>$ROW[0]){
			include('includes/footer.php');
			exit;
		
}

if (isset($_POST['PrintShow'])&& $row[0]==0){
	
		include('includes/header.php');	
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';	
		prnMsg('明细账没有打印,不能打印总账！','warn');
		include('includes/footer.php');
		exit;
	
}
    $SQL="SELECT  a.accountcode,
					accountname,
					b.ncye
				FROM chartmaster a
				LEFT JOIN ( SELECT  LEFT(account, 4) accountcode,
						SUM(balancebgn) ncye
					FROM   accountprint
					WHERE periodyear='".$prtyear."'
					GROUP BY LEFT(ACCOUNT, 3)
					ORDER BY LEFT(ACCOUNT, 4)
				) AS b ON  b.accountcode = a.accountcode
				WHERE  LENGTH(a.accountcode) = 4
				ORDER BY  a.accountcode";
	$RESULT = DB_query($SQL);  
if (!isset($_POST['Print'])){
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />	
	      <input type="hidden" name="periodyear" value="' . $_POST['periodyear'] . '" />
		  <input type="hidden" name="printacc" value="' . $_POST['printacc'] . '" />
	      <input type="hidden" name="sltprt" value="' . $_POST['sltprt'] . '" />	';
		$prtauto=0;//选择打印
	echo '<table class="selection">'; 
		$sql="SELECT   YEAR(lastdate_in_period) periodyear,periodno  FROM periods
	           WHERE MONTH(lastdate_in_period)=1 AND periodno <='".$_SESSION['period']."'";
	echo '<tr>
			<td>年度选择</td>
			<td><select name="periodyear">';
			$result=DB_query($sql);
		 	while($row=DB_fetch_array($result)){	
				if (isset($_POST['periodyear']) AND $_POST['periodyear']==$row['periodyear'].'^'.$row['periodno']){
                    echo '<option selected= "selected" value ="';
				}else{
					echo '<option value ="';
				}
				echo  $row['periodyear'] .'^'.$row['periodno'].'">'.$row['periodyear'] . '</option>';
			 }
	echo '</select></td></tr>';
	echo '<tr>
	      <td>打印方式:</td>
		  <td><input type="radio" name="prttyp" value="0"'.($_POST['prttyp']==0 ? 'checked':"").' />默认
  			  <input type="radio" name="prttyp" value="1"  '.($_POST['prttyp']==1 ? 'checked':"").' />全部
           </td>
		  </tr>';
 	echo '</table>';
 	
	echo '<div class="centre"><input type="submit" name="Print"   onChange="firm(this)" value="' . _('Print'). '" />
	            <input type="submit" name="PrintShow" value="打印查询" /> ';
	         
	echo'</div>';   
}	
 
if(isset($_POST['PrintShow'])) { 
	
	$result=DB_query($SQL);
    	echo '<table class="selection">';	
		$k=1;
	while ($myrow = DB_fetch_array($RESULT)){
        $sql="SELECT	DATE_FORMAT(trandate, '%Y%m') ym,
						LEFT(account, 4) acc,
						SUM(toamount(amount, -1, 0, 0, 1, flg)) jamo,
						SUM(toamount(amount, -1, 0, 0, -1, flg)) damo
						FROM gltrans
						WHERE	periodno >=".$prtjanr." AND periodno <=".($prtjanr+11)."
								AND LEFT(account, 4)='".$myrow['accountcode']."'
						GROUP BY DATE_FORMAT(trandate, '%Y%m'),	LEFT(account, 4)
							ORDER BY LEFT(account, 4), periodno";
		$result = DB_query($sql);
		if (DB_num_rows($result)>0){
		echo '<tr>
		<th colspan="9">' . $myrow['accountname'] . '</th>
			
		</tr>';	
		echo '<tr>
		<th colspan="9">' . $myrow['accountcode'] . '</th>
			
		</tr>';	
		echo '<tr>
					<th>' . _('Sequence') . '</th>
					<th>会计期间</th>
					<th>摘要</th>
					<th>借/贷</th>
					<th>期初余额</th>
					<th>借方合计</th>
					<th>贷方合计</th>
					<th>借/贷</th>
					<th>期末余额</th>							
					</tr>';	
		if (empty($myrow['ncye'])){
			$ncye=0;
		}else{
			$ncye=$myrow['ncye'];
		}
		   $jtotal=0;
		   $dtotal=0;
		   $r=1;
	    while ($row = DB_fetch_array($result)){	 
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
				
		  
				echo '<td>' .$r. '</td>
					  <td>' . $row['ym'] . '</td>
					  <td>本月合计</td>
					  <td>' . ($ncye?"借":"贷") . '</td>
					  <td class="number">' . abs(round($ncye,2)) . '</td>
					  <td class="number">' . $row['jamo'] . '</td>
					  <td class="number">' . $row['damo'] . '</td>';
					  $ncye+= $row['jamo']- $row['damo'];
				echo' <td>' . ($ncye?"借":"贷") . '</td>
					  <td class="number">' . abs(round($ncye,2)) . '</td>
				 </tr>';
				 if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				$jtotal+= $row['jamo'];
				$dtotal+= $row['damo'];	
			  
					echo '<td>' .$r. '</td>
						  <td>' . $row['ym'] . '</td>
						  <td>本年累计</td>
						  <td></td>
						  <td></td>
						  <td class="number">' .round($jtotal,2) . '</td>
						  <td class="number">' . round($dtotal,2) . '</td>';
						 
					echo' <td></td>
						  <td></td>
					 </tr>';
					 $r++;
		}	
		} 
			
	}	

		echo '</table>';
	//	echo var_dump($prtar);
}elseif(isset($_POST['Print'])) {
    	$result=DB_query($SQL);

	
		 $pagrcd=0;
		 $pagprt=0;
		 $pagcount=0;
	 
	//  if ( $prtauto>=10){	 
		include('includes/tcpdf/PDFLedgerPrint.php');
		// create new PDF document Landscape  PDF_PAGE_ORIENTATION
		$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('chengjiang');
		$pdf->SetTitle('总账打印');
		$pdf->SetSubject( _('Account Vouche') );
		$pdf->SetKeywords('TCPDF, PDF');
	
			// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT,true);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->SetAutoPageBreak(TRUE, 10);//PDF_MARGIN_BOTTOM);

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
		DB_data_seek($result,0);
	
		$pdf->LedgerPDF($result,$prtjanr,$prtrow,$prttyp);
	
		//============================================================+
		// END OF FILE
		//============================================================+		
		ob_end_clean();
		//   ob_clean();
		$pdf->Output( 'LedgerPrint.pdf','I');
		$pdf->__destruct();
	
		/*
		$sql="SELECT `periodno` FROM `periods` WHERE YEAR(lastdate_in_period)=".$prdyear." AND periodno<=".$_SESSION['period']. " ORDER BY periodno DESC LIMIT 1";
		$result=DB_query($sql);
		$nowprd=DB_fetch_row($result)[0];
		$sql="UPDATE `myconfig` SET   `confvalue`='".$nowprd."' WHERE confname='printprd' AND confvalue<".$nowprd;
		$result=DB_query($sql);*/
		exit;
	
		
		//  header('Location:LedgerPrint.php?msg="ok"');	
//}
	}	
  
echo '</form>';
include('includes/footer.php');
function PrintSet($prdyear,$prd){
	$prd=1;
      //在启动打印时 启账期间、1月时 检查 是否设置汇总科目
	  //$_SESSION['startprd']=0没有启账
	  //prttype，明细科目 0 汇总科目1 
		$sql="SELECT COUNT(*) FROM accountprint WHERE periodyear=".$prdyear." AND prttype=1";
   		$result = DB_query($sql);
      	$row = DB_fetch_row($result);
	if ($row[0]==0){
		$SQL="SELECT T.accountcode  FROM chartmaster T 
					WHERE ( LENGTH(T.accountcode)=4 
					OR EXISTS (SELECT accountcode FROM chartmaster T1 WHERE locate(T.accountcode,T1.accountcode,1)>0 AND (LENGTH(T1.accountcode)>LENGTH(T.accountcode)))) AND LEFT(T.accountcode,4) 
					IN (SELECT DISTINCT LEFT(account,4) accountcode FROM gltrans) ORDER BY T.accountcode";
     	$Result = DB_query($SQL);
		 while($myrow=DB_fetch_array($Result)){
         $sql="SELECT SUM(amount)  FROM gltrans WHERE account LIKE '".$myrow['accountcode']."%'"; 
	     $result = DB_query($sql);
		 $row = DB_fetch_row($result); 
	     $sql1="INSERT INTO accountprint( account,periodyear,prttype, balancebgn, prtdate, flg) VALUES
	     ('".$myrow['accountcode']."','".$prdyear."',1,".$row[0].",date_format(now(),'%Y-%m-%d') ,0)";
        $result1 = DB_query($sql1); 
	   }
	 
	} 
}

     		//插入打印初始标记和账套初始余额    SetSysFlag('prtflg',6);  $prtflg;//返回1 有打印,0没有
function JournalTotal($_acc,$_janr,$_prd){
  
   $result=DB_query("SELECT SUM(toamount( amount,periodno,".$_prd.",".$_prd.",".$_janr.",flg)) debit ,SUM(toamount(amount,periodno,".$_prd.",".$_prd.",-1,flg)) credit ,
                             SUM(toamount( amount,periodno,".$_janr.",".$_prd.",".$_janr.",flg)) jtotal ,SUM(toamount(amount,periodno,".$_janr.",".$_prd.",-1,flg)) dtotal 
                               FROM gltrans WHERE account='".$_acc."' AND periodno>=".$_janr." AND periodno<=".$_prd."");
   	$row=DB_fetch_assoc($result);
	if ($result){
		$totalarr=array($row['debit'],$row['credit'],$row['jtotal'],$row['dtotal']);
	}else{
        $totalarr='';
	}

   return $totalarr;
   
}   
	

?>