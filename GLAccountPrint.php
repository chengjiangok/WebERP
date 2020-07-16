<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-06-16 10:05:24
 */
/*$ID AccountPring.php $*/

include ('includes/session.php');
$Title = '账簿打印';
$ViewTopic= 'GeneralLedger';
$BookMark = 'GLJournalPrint';

	if (!isset($_POST['PageOffset'])) {
		$_POST['PageOffset'] = 1;
	} else {
		if ($_POST['PageOffset'] == 0) {
			$_POST['PageOffset'] = 1;
		}
	}
	if (!isset($_POST['AccountBook'])){
		$_POST['AccountBook']='L';		 		
	}	 	

	if (!isset($_POST['PrintType'])){
		$_POST['PrintType']=0;		 		
	}	
	if(!isset($_POST['periodyear'])){
		$_POST['periodyear']=substr($_SESSION['lastdate'],0,4).'^'.$_SESSION['janr'];
	}
	if (!isset($_POST['TagsGroup']) ){
    	$_POST['TagsGroup']=0;		 
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
  $SelectYear=explode('^',$_POST['periodyear'])[0];
$SelectPrdJanr=explode('^',$_POST['periodyear'])[1];
if (!isset($LAccount)){ 
	$SQL="SELECT `account` FROM `accountstyle` WHERE mode=1";
	$Result=DB_query($SQL);
	while ($row=DB_fetch_array($Result)){
		if (!in_array(substr($row['account'],0,4),$LAct)){
			$LAct[]=substr($row['account'],0,4);//$row['account'];//
		}
		$LAccount[$row['account']]=substr($row['account'],0,4);
	}
}
if (isset($_POST['Print'])||isset($_POST['PrintShow'])||isset($_GET['ActPrint'])){

	$sql="SELECT COUNT(*) FROM accountprint WHERE periodyear='".  $SelectYear."'  AND prttype=0 ";
			$result=DB_query($sql);
			$row=DB_fetch_row($result);
		
		if ( $row[0]==0){		
				//$SQL="SELECT  account,sum( amount) amount FROM gltrans WHERE periodno<".$_SESSION['startperiod']."  AND periodno>=0 GROUP BY account";
				//$Result=DB_query($SQL);
				$sql="INSERT INTO accountprint( periodyear,
														prttype,
														account,
														indexbgn,
														indexend,
														prtcount,
														pagecount,
														pageno,
														balancebgn,
														amountj,
														amountd,
														prtdate,
														flg
													)
													SELECT '".  $SelectYear."' periodyear,
														0 prttype,
														account,
														0 indexbgn,
														0 indexend,
														0 prtcount,
														0 pagecount,
														0 pageno,
														SUM(amount) balancebgn,
														0 amountj,
														0 amountd,
														'".date('Y-m-d')."' prtdate, 0 flg
													FROM gltrans
													WHERE  periodno <".$SelectPrdJanr." AND periodno >-1
													GROUP BY account";	
									//prnMsg($sql);									
			$result=DB_query($sql);		
		}
}
if (isset($LAccount)){//in_array($LAct, $_POST['GLCode'] )){
	$ActStr=implode(",",array_keys($LAccount));
}
if (!isset($_POST['Print'])&&!isset($_POST['ActPrint'])){
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />	
	      <input type="hidden" name="periodyear" value="' . $_POST['periodyear'] . '" />
		  <input type="hidden" name="AccountBook" value="' . $_POST['AccountBook'] . '" />
	     	';
		$PrintType=0;//选择打印
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
	echo '</select></td>';

	$sq=" ";
	if (isset($LAccount)){//in_array($LAct, $_POST['GLCode'] )){
		//$ActStr=implode(",",array_keys($LAccount));
		$SL =" UNION SELECT a.accountcode ,a.accountname 
		FROM chartmaster  a WHERE a.accountcode IN (" . $ActStr.")
		";
		$sq=" AND T.accountcode NOT IN (" . $ActStr.")";
	}else{
		$ActStr="";
		$SL=" ";
	}
	
		$sql=" SELECT T.accountcode ,T.accountname FROM chartmaster T 
			   WHERE ( LENGTH(T.accountcode)=4  
			OR EXISTS (SELECT accountcode FROM chartmaster T1 WHERE locate(T.accountcode,T1.accountcode,1)>0 AND (LENGTH(T1.accountcode)>LENGTH(T.accountcode))))
			 AND LEFT(T.accountcode,4) IN (SELECT DISTINCT LEFT(account,4) accountcode FROM gltrans)".$sq.$SL;
		$result=DB_query($sql);
	
	echo '<tr>
	    	<td>' . _('Account Code') . ':</td>
			<td><select name="AccountBook" >'	;
	if($_POST['AccountBook']=='P') {
  	 echo '<option selected="selected"   value="P">竖式科目</option>
	        <option value="L">横式账簿</option>';
  	}elseif($_POST['AccountBook']=='L'){
	 echo '<option  value="P">竖式账簿</option>
	        <option  selected="selected" value="L">横式科目</option>';	  
	  }else{
	 echo '<option  value="P">竖式账簿</option>
	        <option  value="L">横式账簿</option>';	  
	  }
	while ($myrow=DB_fetch_array($result)){
		$cout=false;
		if (in_array(substr($myrow['accountcode'],0,4),$LAct)){
             if (isset($LAccount[$myrow['accountcode']])){
				$cout=true;
			 }
		}else{
			$cout=true;
		}
		if ($cout){
			if (isset($_POST['AccountBook']) AND $_POST['AccountBook']==$myrow['accountcode']){
				echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'].' - ' .htmlspecialchars($myrow['accountname'], ENT_QUOTES,'UTF-8', false) . '</option>';
			} else {
				echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'].' - ' .htmlspecialchars($myrow['accountname'], ENT_QUOTES,'UTF-8', false)  . '</option>';
			}
		}
	}
	echo '</select></td></tr>';
	echo '<tr>
	        <td>打印方式:</td><td>
				<input type="radio" name="PrintType" value="0"'.($_POST['PrintType']==0 ? 'checked':"").' />默认
				<input type="radio" name="PrintType" value="1"  '.($_POST['PrintType']==1 ? 'checked':"").' />重新
		    </td>
		  </tr>';
	echo '<tr>
		  	<td>单元分组</td>
		    <td>';
		   		TagsGroup(7);
		 
	 echo'</td></tr>';     
 	echo '</table>';
 	
	echo '<div class="centre">
			<input type="submit" name="PrintShow" value="打印查询" />';
		//	if (isset($_POST['PrintShow']))
	echo'<input type="submit" name="Print"   onChange="firm(this)" value="' . _('Print'). '账簿" />';
	echo'</div>';   
}	
  	
 	if (explode('^',$_POST['periodyear'])[0]<substr($_SESSION['lastdate'],0,4)){
		 $PrintType=1;// 1   动封账转下年
 	
    }else{
		 $PrintType=2;//  2当年打印
		
	}
	if($_POST['PrintType']==1){
		 $PrintType=3;// 3历史打印
	}
	if (isset($_GET['msg'])){
		  prnMsg('没有需要打印的账目记录！','info');
	}
	
	$SQ=' ';
	/*
	if (isset($_POST['TagsGroup'] ) AND $_POST['TagsGroup'] !='' ){
		
		$SQ.="  AND r.account IN (SELECT accountcode FROM `chartmaster` WHERE tag IN (".$_POST['TagsGroup'].")) ";
	}else
	if (isset($_GET['ActPrint'])) {
		$SQ.="  AND r.account =".$_GET['ActPrint']." ";
	}*/
	
		//选择    横竖
	if (isset($_POST['AccountBook'] )&& strlen($_POST['AccountBook'])<4){
	
		$PL=$_POST['AccountBook'];
	}else{
		$sql="SELECT account, title, acctype, format, pagehight,pagewidth, top, bottom, leftmargin, rightmargin, mode 
			FROM accountstyle 
			WHERE account='".$_POST['AccountBook']."'";
		
			$Result=DB_query($sql);	
			$stylerow=DB_fetch_assoc($Result);
		if ($stylerow['mode']==1){
			$PL='L';
				//竖P
		}else{
			$PL='P';
		}	
	}
	//echo  '-='.$_POST['AccountBook'];
if(isset($_POST['PrintShow'])) { 
	// 
	if ($PrintType==1){
		prnMsg('选择默认自动打印  账簿全部自动打印完成，并自动封账！ ','info');
	}
	
	
    if (isset($_POST['AccountBook'] )&& strlen($_POST['AccountBook'])==1){
		if ($_POST['AccountBook']=='L'){
			$Msg.="横格式账目";
		
		}else{
			$Msg="竖格式账目";	
			
		}
		prnMsg("你选择了打印全部".$Msg,'info');
	}else{
		
		$sql="SELECT r.account, chartmaster.accountname,record,prtcount,pagecount ,mode
				FROM accountrecord r
				LEFT JOIN chartmaster ON r.account=chartmaster.accountcode 
				LEFT JOIN accountprintview ON (r.account =accountprintview.account AND r.periodyear=accountprintview.periodyear) 				 
				WHERE r.periodyear=".  $SelectYear ;
			//选择打印总账	
		if (isset($_POST['AccountBook'] )&& strlen($_POST['AccountBook'])>1){
			$sql.=" AND r.account LIKE '".substr($_POST['AccountBook'],0,4)."%' ";
			$AccountBook=$_POST['AccountBook'];
		}
		$result = DB_query($sql); 
    	echo '<table class="selection">';
		echo '<tr>
				<th>' . _('Sequence') . '</th>
				<th>' . _('Account Code') . '</th>
				<th>' . _('Account Description') . '</th>
				<th>笔数</th>
				<th>打印笔数</th>
				<th>打印页数</th>		
				<th>打印选择</th>	
			</tr>';
		$r=1;
		$k=1;
		while ($myrow = DB_fetch_array($result)){

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			echo '<td>' .$r. '</td>
			      <td>' . $myrow['account'] . '</td>
				  <td>' . $myrow['accountname'] . '</td>
				  <td>' . $myrow['record']  . '</td>
				  <td class="number">' . $myrow['prtcount'] . '</td>
				  <td class="number">' .$myrow['pagecount']. '</td>				  
			      <td ><input type="checkbox" name="chkbx[]" value="'. $myrow['account'].'"'.($PL=="L"?"disabled":"checked").' ></td></tr>
 			</tr>';
				$r++;
		}	
			echo '</table>';
	}
}elseif(isset($_POST['Print']) OR isset($_GET['ActPrint'])) {
	include('includes/header.php'); 
	$SelectPrint='';  
	
		if ($PL=="P" ){
			foreach($_POST['chkbx'] as $val){
				
				$SelectPrint.=$val.',';

			}
		}elseif($PL=="L" ){
			if ($_POST['AccountBook'] =="L"){
				$SelectPrint= $ActStr;//横竖打印的科目
			}else{
				$SelectPrint=$_POST['AccountBook'];//   竖打印的科目
			}
		}
	//}
	//echo $SelectPrint.'-='.'['.$PL.'=='.$_POST['AccountBook'] ;
	if (strlen($SelectPrint)<4){
		prnMsg("全部打印");
	}else{
		
		prnMsg("部分打印");
		//$SelectPrint=substr($SelectPrint,0,-1);
	}

	     $SQL="SELECT r.account, chartmaster.accountname,record,ifnull(prtcount,0) prtcount ,ifnull(pagecount,0) pagecount ,mode
	           FROM accountrecord r
			   LEFT JOIN chartmaster ON r.account=chartmaster.accountcode 
			   LEFT JOIN accountprintview ON (r.account =accountprintview.account  AND r.periodyear=accountprintview.periodyear)
			   WHERE r.periodyear=".explode('^',$_POST['periodyear'])[0] ;
	
	//echo $sql;exit;
	$Result = DB_query($SQL);//记录总数
	
	$pagrcd=0;
	$pagprt=0;
	$pagcount=0;
	while($row=DB_fetch_array($Result)){          
		$pagrcd+=$row['record'];
		$pagprt+=$row['prtcount'];	
		if ($row['record']-$row['prtcount']>=$pag){
				$pagcount++;//未打印页数
		}	 
	}
     unset($Result);
	if ($_POST['PrintType']==0){ 
		if ($PrintType==1){
			if ($pagrcd-$pagprt>0){
				$PrintPages=$pagrcd-$pagprt;
				//$PrintType=$PrintType*10;
			}else{
				$msg="没有需要打印账页!";
			}
		}elseif ($PrintType==2){
			if ($pagcount>0){
				$PrintPages=$pagcount;
				//$PrintType=$PrintType*10;
			}else{
				$msg="本年没有需要打印的账目!"; 
			}
		}
	}else{
		//历史打印
		if ($pagprt>=0){
			$PrintPages=$pagprt;
			//	$PrintType=3;
		
		}else{
				$msg="没有需要打印的账页!";
		}		
	}
	//echo '-='.$PrintPages.'='. $PrintType;	 exit;
	if ( $PrintPages>0){	 
		$result = DB_query($sql);
		include('includes/tcpdf/PDFAccountPrint.php');
		// create new PDF document Landscape  PDF_PAGE_ORIENTATION
		
		$pdf = new MYPDF($PL, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('chengjiang');
		$pdf->SetTitle('账簿打印');
		$pdf->SetSubject("会计账簿");
		$pdf->SetKeywords('TCPDF, PDF');
	
			// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		// set margins
		$pdf->SetMargins(PDF_MARGIN_LEFT, 5, PDF_MARGIN_RIGHT,true);
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
		$pdf->setPrintFooter(false);
		$pdf->setPrintHeader(false);
		$pdf->SetAutoPageBreak(TRUE, 0);//PDF_MARGIN_BOTTOM);

			// set some language-dependent strings (optional)
		if (@file_exists(dirname(__FILE__).'/tcpdf/chi.php')) {
			require_once(dirname(__FILE__).'/tcpdf/chi.php');
			$pdf->setLanguageArray($l);
		}
		// ---------------------------------------------------------
		// set font helvetica 
		$pdf->SetFont('droidsansfallback', '', 10);
		// add a page
		//$pdf->AddPage();
		//DB_data_seek($result,0);
			$sltprint=array($PL,$SelectPrint);
			//print_r(explode(",",$SelectPrint));
		///	echo $SelectPrint; exit;
		if ($PL=='P'){
			//竖
			//  $SelectYear,年$SelectPrdJanr,$PrintType
			$pdf->AccountPDFP($sltprint,  $SelectYear,$SelectPrdJanr,$PrintType,$_POST['TagsGroup']);
		//	ECHO "pppp";
		}else{
			
			$pdf->AccountPDFL( $sltprint,  $SelectYear,$SelectPrdJanr,$PrintType,$_POST['TagsGroup']);	
		//	ECHO "LLLL";
		}
		//echo $pag,'=',  $SelectYear,'[==',$SelectPrdJanr,'[]',$PrintType,'-',$_POST['TagsGroup'];
	
		//============================================================+
		// END OF FILE
		//============================================================+	
		
		date_default_timezone_set("PRC");	
		ob_end_clean();
		//   ob_clean();
		$pdf->Output( 'AccountPrint.pdf','I');
		//PDF输出的方式。I，默认值，在浏览器  打开；D，点击下载按钮， PDF   件会被下载  来；F，文件会被保存在服务器中；S，PDF会以字符串形式输出；E：PDF以邮件的附件输出。 
		$pdf->__destruct();
		
		$sql="SELECT `periodno` 
		       FROM `periods` 
			   WHERE YEAR(lastdate_in_period)=".  $SelectYear." 
			   AND periodno<=".$_SESSION['period']. " 
			   ORDER BY periodno DESC LIMIT 1";
		$result=DB_query($sql);
		$nowprd=DB_fetch_row($result)[0];
	
		$sql="UPDATE `myconfig` SET   `confvalue`='".$nowprd."' WHERE confname='printprd' AND confvalue<".$nowprd;
		
		$result=DB_query($sql);
		
  		exit;
	}else{
		prnMsg("当期打印笔数不够一页!","info");
	}		
		//  header('Location:AccountPrint.php?msg="ok"');	
	  
}	
  
echo '</form>';
include('includes/footer.php');
function PrintSet($prdyear,$prd){
	 //没有使用？   
	$prd=1;
      //在启动打印时 启账期间、1月时 检查 是否设置    总科目
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
    //   有使用？？
   $result=DB_query("SELECT SUM(toamount( amount,periodno,".$_prd.",".$_prd.",".$_janr.",flg)) debit ,
                            SUM(toamount(amount,periodno,".$_prd.",".$_prd.",-1,flg)) credit ,
							SUM(toamount( amount,periodno,".$_janr.",".$_prd.",".$_janr.",flg)) jtotal ,
							SUM(toamount(amount,periodno,".$_janr.",".$_prd.",-1,flg)) dtotal 
					   FROM gltrans 
					   WHERE account='".$_acc."' 
					   AND periodno>=".$_janr." 
					   AND periodno<=".$_prd."");
   	$row=DB_fetch_assoc($result);
	if ($result){
		$totalarr=array($row['debit'],$row['credit'],$row['jtotal'],$row['dtotal']);
	}else{
        $totalarr='';
	}

   return $totalarr;
   
}   
	

?>