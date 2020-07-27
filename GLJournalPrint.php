
<?php
	/*$ID GLJournalPrint.php $*/

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-05-10 08:03:17
 */
include ('includes/session.php');

$Title ='会计凭证打印';// _('Accounting document Print');
$ViewTopic= 'GeneralLedger';
$BookMark = 'GLJournalPrint';
include('includes/SQL_CommonFunctions.inc');

if (!isset($_POST['selectperiod'])OR $_POST['selectperiod']==''){
	$_POST['selectperiod']=$_SESSION['period'].'^'.$_SESSION['lastdate'];
}
if (!isset($_POST['selectprint']) OR $_POST['selectprint']==''){
   $_POST['selectprint']=1;		 		
}
$PrintErr=true;
$msg='';
if (!isset($_POST['TagsGroup'])){
	$_POST['TagsGroup']=1;//$_SESSION['tagsgroup'][1][0]; 		 		
 }
if (!isset($_POST['Print']) || !$PrintErr){
	include('includes/header.php');
	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
				<input type="hidden" name="selectprint" value="' . $_POST['selectprint'] . '" />
				<input type="hidden" name="TagsGroup" value="' . $_POST['TagsGroup'] . '" />	
			<input type="hidden" name="selectperiod" value="' . $_POST['selectperiod'] . '" />';

		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		echo '<table class="selection">';  	
		echo '<tr>
				<td>选择会计期间</td>
				<td>';
				SelectPeriod($_SESSION['period'],$_SESSION['janr']);
		echo '</td></tr>';
		echo '<tr>
				<td>单元分组</td>
		  		<td>';
				  echo'<select name="TagsGroup" id="TagsGroup" size="1" >';
		
		foreach($_SESSION['tagsgroup'] as $key=>$row){
			if ($row[3]==1){
				if(isset($_POST['TagsGroup']) AND $key==$_POST['TagsGroup']){
					echo '<option selected="selected" value="';			
				}else{
					echo '<option value="';
				}
				echo  $key. '">' .$row[2] . '</option>';
			}
		}
				  echo'</select>';	
	   
   		echo'</td></tr>';     
			echo '<tr>
					<td>' . _('Print Option') . '</td>
					<td><input type="radio" name="selectprint" value="1" '.($_POST['selectprint']==1 ?'checked="true"':'').' >默认打印          
					<input type="radio" name="selectprint" value="2" '.($_POST['selectprint']==2 ?'checked':'').'  >选择打印 </td> </tr>';
			echo '<tr>
					<td>选择打印号</td>
					<td>:'. '<input type="text"  name="printno"  size="20" maxlength="20" value="'.$_POST['printno'].'" />'._('As') .': 1-12 OR 1,4,8 </td>
				
				</tr>';        
		echo '</table>';
	
	echo '<div class="centre">
					<input type="submit" name="Print" value="' . _('Print'). '" />
					<input type="submit" name="PrintCheck" value="打印号检查" />
					<input type="submit" name="PrintShow" value="查询已打印" /> ';	            
		echo'</div>';  	
}
//echo ($_SESSION['tagsgroup'][$_POST['TagsGroup']][0]);
//exit;
if (isset($_POST['Print'])||isset($_POST['PrintCheck'])){
	//prnMsg($_POST['ERPPrd'].'='.$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].'-'.$PrtErr);
	$sql="SELECT  confvalue FROM myconfig WHERE confname='prtformat'";
			$Result=DB_query($sql);
			$row=DB_fetch_row($Result);		
	$prtformat = json_decode($row[0],true);
	$PrtErr=JournalPrtNo($_POST['ERPPrd'],$_SESSION['tagsgroup'][$_POST['TagsGroup']][0],$prtformat['prtrow']);	

	if ($PrtErr!=0){
		$PrintErr=false;
		//
	}
	if ( $_POST['selectprint']==1 && $PrintErr){//默认打印   
		
		$Sql="SELECT DISTINCT typeno 													
				FROM  gltrans						
				WHERE  gltrans.tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].")	AND gltrans.periodno=".$_POST['ERPPrd']." ORDER BY transno ";
			//	WHERE  periodno=".$_POST['ERPPrd']." ";
		
		$Result=DB_query($Sql);
		$rw=1;
		$msg='';
		while ($row=DB_fetch_array($Result)) {
			if ((int)$rw!=(int)$row['typeno']){
				$msg.=(string)$rw.',';
				$rw++;
			}			
			$rw++;
		}
		if ($msg!=''){
			$PrintErr=false;
		}
	}	
}
//prnMsg("115");	
if (!$PrintErr){	
	include('includes/header.php');
	prnMsg($msg.' 缺号,不能进行凭证打印!','info');
	echo '<a href="' .$RootPath. '/GLJournalPrint.php">返回</a>';
	echo '</form>';
	include('includes/footer.php');
	exit;
}		
   $LastDate=PeriodGetDate($_POST['ERPPrd']);
  // print_r(
      // echo ($_SESSION['tagsgroup'][$_POST['TagsGroup']][0]);
if(isset($_POST['PrintShow']) ) {
     
				$SQL="SELECT gltrans.typeno,
								systypes.typename,
								gltrans.trandate,
								gltrans.transno,
								gltrans.printno,
								gltrans.account,
								chartmaster.accountname,
								gltrans.narrative,
								toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
								toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
								gltrans.flg,
								gltrans.prtchk,
								gltrans.periodno,
								gltrans.tag
							FROM gltrans
							LEFT JOIN chartmaster
								ON gltrans.account=chartmaster.accountcode		
								LEFT JOIN systypes
								ON gltrans.type=systypes.typeid
							WHERE gltrans.periodno=".$_POST['ERPPrd']."	
							      AND gltrans.tag IN ( ".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0]." )	
							ORDER BY gltrans.printno,gltrans.transno";
				$transResult = DB_query($SQL);

		echo '<table class="selection">';
		echo '<tr>
				<th>' . ('Date') . '</th>
				<th>凭证号</th>	
				<th>' . _('Account Code') . '</th>
				<th>' . _('Account Description') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Debits').' '.$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] . '</th>
				<th>' . _('Credits').' '.$_SESSION['CompanyRecord'][$_SESSION['Tag']]['currencydefault'] . '</th>			
				<th>' . ('Print') . '</th>	
			</tr>';

		$LastJournal = 0;
   		$LastPrint = -1;
   		$my_arr=array();
   		$i=0;
   		$r=1;
		while ($myrow = DB_fetch_array($transResult)){

			if ($myrow['transno']!=$LastJournal) {
			
				if ($r==1){
					echo '<tr class="EvenTableRows">';
					$r=0;
				}else {
					echo '<tr class="OddTableRows">';
					$r=1;
				}
				echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
				      <td title="'. $myrow['transno'].'">[' .$myrow['transno'].']'.$_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno']. '</td>';
               
			}else{
				if ($r==1){
					echo '<tr class="EvenTableRows"><td colspan="2"></td>';
					$r=0;
				}else{
					echo '<tr class="OddTableRows"><td colspan="2"></td>';
					$r=1;
				}
				
			}
     		$my_arr['account'][$i]=$myrow['account'];
      		$i=$i+1;
			echo '<td>' . $myrow['account'] . '</td>
						<td>' . $myrow['accountname'] . '</td>
						<td>' . $myrow['narrative']  . '</td>
						<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces'])) . '</td>
						<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']) ). '</td>';
						if ($myrow['transno']!=$LastJournal ) {
							$LastJournal = $myrow['transno'];
						}
						if ($myrow['printno']!=$LastPrint ){
							
							$LastPrint = $myrow['printno'];	
							
							if ($myrow['printno']!=0 ){
								echo '<td class="number"><a href="PDFGLJournal.php?JournalNo='.$myrow['periodno'].'^'.abs($myrow['printno']).'^'.$_POST['TagsGroup'].'">' . _('Print') .'</a></td></tr>';
							}else{
								echo '<td>'._('No').'</td></tr>';
						
							}
						}else{
							echo '<td></td>
									</tr>';								
						}
		}
	echo '</table>';
	
}elseif (isset($_POST['Print']) && $PrintErr  ) {
	
  //prnMsg('224');
	include('includes/tcpdf/PDFJournal.php');	
	if ( $_POST['selectprint']==1){//默认打印  
		
		$sql="SELECT gltrans.typeno,
					systypes.typename,
					gltrans.trandate,
					gltrans.transno,
					abs(gltrans.printno) printno,
					gltrans.account,
					chartmaster.accountname,
					gltrans.narrative,
					gltrans.tag,
					toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
					toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits			
				FROM gltrans
				LEFT JOIN chartmaster	ON gltrans.account=chartmaster.accountcode
				LEFT JOIN tags	ON gltrans.tag=tags.tagref
				LEFT JOIN systypes	ON gltrans.type=systypes.typeid
				WHERE gltrans.printno<>0   AND gltrans.tag IN ( ".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0]." )	AND gltrans.periodno=".$_POST['ERPPrd'];
		if (isset($_SESSION['Audit']) and $_SESSION['Audit']>0){//是否审核后打印
			$sql.=" AND prtchk=2 ";  
		}	 
		$sql.=' ORDER BY abs(gltrans.printno),gltrans.transno';
		
	
	}elseif ($_POST['selectprint']==2 AND $_POST['printno']!=''){//选择打印 
		
		if (is_numeric($_POST['printno']) AND $_POST['printno']!=''){
			$strno=$_POST['printno'];
		
		}else {
			if ( is_numeric(str_replace('-', '', $_POST['printno']))){
				$printno=explode('-', $_POST['printno']);
		
				$p=(int)$printno[0];
				$n=(int)$printno[1];
				if ($p< $n){
				for ($i =$p; $i < $n; $i++) {
				// code to execute 
					$strno.=$i.',';
				} 			
				}else {
					for ($i = $n; $i < $p; $i++) {
					// code to execute 
					$strno.=$i.',';
					} 			
						
				}
				$strno=substr($strno,0, -1);
			
			}elseif (is_numeric(str_replace(',', '', $_POST['printno']))){
						$strno=$_POST['printno'];
								
			}				
			
		}
		
			$sql="SELECT gltrans.typeno,
						systypes.typename,
						gltrans.trandate,
						gltrans.transno,
						abs(gltrans.printno) printno,
						gltrans.account,
						chartmaster.accountname,
						gltrans.narrative,
						chequeno,
						toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
						toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
						gltrans.tag		
					FROM gltrans
					LEFT JOIN chartmaster	ON gltrans.account=chartmaster.accountcode
					LEFT JOIN tags	ON gltrans.tag=tags.tagref
					LEFT JOIN systypes	ON gltrans.type=systypes.typeid
					WHERE   gltrans.printno in (".$strno.") AND gltrans.printno !=0 
					  AND gltrans.tag IN ( ".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0]." )	
					  AND gltrans.periodno=".$_POST['ERPPrd']."
					ORDER BY abs(gltrans.printno),gltrans.typeno";
		
	}
		$result = DB_query($sql,$ErrMsg,_('The SQL that failed was'),true);		
		$row=DB_num_rows($result);
		//prnMsg($sql);
		//exit;
		if ($row>1){
			$sql="SELECT  confvalue FROM myconfig WHERE confname='prtformat'";
			$Result=DB_query($sql);
			$row=DB_fetch_row($Result);		
			$prtformat = json_decode($row[0],true);

			$sql="SELECT  `confvalue` FROM `myconfig` WHERE confname='Controller'";
            $Result=DB_query($sql);
			$row=DB_fetch_row($Result);
			$Controller = json_decode($row[0],true);
			$Accountant=$Controller['Accountant'][0];
				// create new PDF document PDF_PAGE_ORIENTATION  PDF_PAGE_FORMAT
			$pdf = new MYPDF($prtformat['lp'], PDF_UNIT, $prtformat['format'], true, 'UTF-8', false);
			// set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor('北京国经纬');
			$pdf->SetTitle( '会计凭证打印');
			$pdf->SetSubject( _('Account Vouche') );
			$pdf->SetKeywords('TCPDF, PDF');
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
			//设置左边距
			$pdf->SetLeftMargin((int)$prtformat['left']);
			$pdf->SetHeaderMargin(0);
			//设置顶边距
			//$pdf->SetTopMargin(10);
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
			// set font helvetica 
			$pdf->SetFont('droidsansfallback', '', 10);
			// add a page
			$pdf->AddPage();
			// column titles
			$header ="";// array(_('Sequence'), _('Date'), '凭证号', '摘要','附单',_('Account Code'), '总账科目/明细科目', _('Debits'), _('Credit'));
			// print colored table		
	
		
			$pdf->PDFJournal($result,$header,$_POST['ERPPrd'],$prtformat,$_SESSION['tagsgroup'][$_POST['TagsGroup']]);
		
			// END OF FILE
					ob_end_clean();
						// close and output PDF document
						$pdffilename=$_SESSION['CompanyRecord'][1]['unitstab'].'会计凭证'.substr($LastDate,0,7).'.pdf';
						$pdf->Output($pdffilename,'D');
							/*PDF输出的方式。I，默认值，在浏览器中打开；D，点击下载按钮， PDF文件会被下载下来；F，文件会被保存在服务器中；S，PDF会以字符串形式输出；E：PDF以邮件的附件输出。 */
			
					$pdf->__destruct();
				if ($_POST['selectprint']==1){
						$sql="update  gltrans set printno=abs(printno) where  periodno=".$_POST['ERPPrd']." and printno<0";
						$ErrMsg = _('Cannot Update a GL entry for the printing the SQL');
						$result = DB_query($sql,$ErrMsg,_('The SQL that failed was'),true);
				}
				exit;
		}
			
}		
	$sql="SELECT sum( CASE WHEN  printno = 0  THEN 1 ELSE 0 END ) AS flg0 ,sum(CASE WHEN  printno< 0  THEN 1 ELSE 0 END) AS flg1 ,sum(CASE WHEN prtchk=0 and printno=0 THEN 1 ELSE 0 END) AS flg2 
	          FROM gltrans WHERE  gltrans.periodno='".$_POST['ERPPrd'] ."' ";
	    
        $result = DB_query($sql);
      	$row = DB_fetch_assoc($result);
	
	if ($row['flg2']>0 AND isset($_SESSION['Audit']) AND $_SESSION['Audit']!=0)  {
        $printflg=-1;

 	    prnMsg('有没有审核的凭证！','info');	

    }elseif ( $myrow['flg0'] <$TransRow AND  $_POST['ERPPrd']= $_SESSION['period']) {  	
      $printflg=-1;     
	    
	}
	
	echo '</form>';
	include('includes/footer.php');
function JournalPrtNo($prd,$tagsgroup,$TransRow=15){
	  	$prtflg=0;     
     	//读取凭证号֤    
		$sql="SELECT typeno	FROM gltrans 
		        WHERE  printno = 0 
				 AND gltrans.periodno = ".$prd ." 
				 AND tag IN (".$tagsgroup.")
				 ORDER BY tag,transno ";  
		$result = DB_query($sql,$db);		
		$trans_arr=array();
		$i=0;
		$th=0;
	    $pzh=$TransRow;
        $tran_='';
        $ListCount = DB_num_rows($result);             
      while($row = DB_fetch_array($result)) {     	
       	$trans_arr[$i]= $row['typeno'];
		 $i++;			  
	  }    
    	$transcount=array_count_values($trans_arr);////函数用于统计数组中所有的值出现的次数
	  if (max($transcount) >$TransRow){//凭证中有大于系统默认打印行返回4
			return 4;
		 }				 
	  	    $keyno='';
			$sumvalue=array_sum($transcount);  
	    foreach ($transcount as $key=>$value){  
			//$key=>$value  凭证号=>行数	    
	  	    if ($value > $TransRow-3){//大于12行凭证直接打印
	  	     	if ($th!=0){	  	     	   	 
                 //写上次循环凭证
			   	$printflg=WritePrtNo($prd,$tagsgroup,$keyno);
              $th=0;
              $keyno='';               
	  	     	}
						//本次   
          	   $printflg=WritePrtNo($prd,$tagsgroup,$key);
	  	        $th=0;
	  	      }else  {
					//合计大于13 <=15 打印
				if (($th+$value >$TransRow-2) AND ($th+ $value) <= $TransRow){
				
					$keyno= $keyno.'-'.$key;
					$keynostr=$keyno;	    
					$printflg=WritePrtNo($prd,$tagsgroup,$keyno);
					$th=0;
					$keyno='';
			    }else {
					//大于15行打印
					if ($th+$value > $TransRow ){
						$printflg=WritePrtNo($prd,$tagsgroup,$keyno);
						$th=$value;
						$keyno=$key;
					}else{ 
						//<15行，连接字符       
						if ($keyno==''){             
							$keyno=$key;
						}else{          
							$keyno.='-'.$key;       
						}
						$th+=$value;             
					}
				}

	  	    }
			  $sumvalue-=$value;
			  //上月最后凭证
			  if ($sumvalue==0 and $th>1 and $prd< DateGetPeriod(date('Y-m-d'))){
				  $printflg=WritePrtNo($prd,$tagsgroup,$keyno);
			  }
			     				
    	}//end foreach
		unset($trans_arr);
		unset($transno);
		unset($transcount);
		unset($transkey);
		unset($tran_);
	  
    return  $prtflg;//返回1有没有打印 0没有 2有没有审核 4异常
}
function WritePrtNo($prd,$tagsgroup,$trn_){
	 //写凭证号、自动生成打印号
	$trn_=str_replace('-',',',$trn_);  
	$sql="SELECT max(abs(printno)) printno 
	        FROM gltrans 
		  WHERE periodno=".$prd ."
		  AND tag IN (".$tagsgroup.")";
	$result = DB_query($sql);
	$rowprt = DB_fetch_assoc($result);
	$prtno=abs($rowprt['printno'])+1; 
	$sql="set sql_safe_updates=0";
	$result = DB_query($sql); 
	//$sql="update  gltrans set printno=".$prtno." where  periodno=".$prd." and transno in (".$trn_.")";
	$sql="UPDATE  gltrans SET printno=".$prtno." 
	       WHERE  periodno=".$prd." 
		   AND typeno IN (".$trn_.")
		   AND tag IN (".$tagsgroup.")";
	$ErrMsg = _('Cannot Update a GL entry for the payment using the SQL');
	$result = DB_query($sql,$ErrMsg,_('The SQL that failed was'),true);
	return 1;	
}
	?>
