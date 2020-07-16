<?php
/*$ID GlJournalInquiry.php*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-12-12 16:10:26 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-09 06:25:55
 */
include ('includes/session.php');
require_once 'Classes/PHPExcel.php'; 
$Title ='会计凭证查询';// _('General Ledger Journal Inquiry');
$ViewTopic= 'GeneralLedger';
$BookMark = 'GLJournalInquiry';
include('includes/header.php');
if (!isset($_POST['ERPPrd'])){ //OR $_POST['ERPPrd']==''){
		$_POST["ERPPrd"]=$_SESSION['period'];
}
if (!isset($_POST['prdrange']) OR $_POST['prdrange']==''){
	$_POST['prdrange']=0;		  	
}
		$prdrange=$_POST['prdrange'];
if (!isset($_POST['UnitsTag']) ){
    	$_POST['UnitsTag']=0;		 
}		
		$tag=	$_POST['UnitsTag'];
 	if (!isset($_POST['queryad'])){
    	$_POST['queryad']=1;
	}		 
	if (isset($_POST['sfzchk0'])){
		$sfz0=$_POST['sfzchk0'];
	}else{
		$_POST['sfzchk0']=1;
	}
	if (isset($_POST['sfzchk1'])){
		$sfz1=$_POST['sfzchk1'];
	}else{
		$_POST['sfzchk1']=1;
	}
	if (isset($_POST['sfzchk2'])){
		$sfz2=$_POST['sfzchk2'];
	}else{
		$_POST['sfzchk2']=1;
	}	
if (!isset($_POST['jdtype'])){
	$_POST['jdtype']=0;   
}
	$jd = $_POST['jdtype'];   
if (!isset($_POST['account'])){
		$_POST['account']='';
}		
  		$acc=$_POST['account'];
if (!isset($_POST['amount'])){
	 $_POST['amount']='';
}
	$amou=$_POST['amount'];
if(isset($_POST['Go1']) OR isset($_POST['Go2'])||isset($_POST['crtExcel'])	) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}	
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
foreach($_SESSION['CompanyRecord'] as $key=>$row)	{         
	if ($row['coycode']!=0){
		
	   $UnitsTag[$row['coycode']]=$row['unitstab'];
	   $UnitsTag[-$row['coycode']]=$row['unitstab']."内";     
	
	}
}
  	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
      		<input type="hidden" name="ERPPrd" value="' . $_POST['ERPPrd'] . '" />
      		<input type="hidden" name="queryad" value="' . $_POST['queryad'] . '" />
      		<input type="hidden" name="amount" value="' . $_POST['amount'] . '" />
			<input type="hidden" name="account" value="' . $_POST['account'] . '" />
			<input type="hidden" name="sfzchk" value="' . $_POST['sfzchk'] . '" />';
      	 //	<input type="hidden" name="UnitsTag" value="' . $_POST['UnitsTag'] . '" />';
    echo '<table class="selection">
	        <tr>
				<th colspan="3">' . _('Selection Criteria') . '</th>
			</tr>
			<tr>
				<td>' . _('For Period range').':</td>
				<td >';
				SelectPeriod($_POST["ERPPrd"],$_SESSION['startperiod']);
				
    echo '范围</td>';
		$rang=array('0'=>'月度','1'=>'+/-月',  '3'=>'季度','12'=>'本年度','36'=>'跨年度');
    echo '<td>  
		<select name="prdrange" size="1" style="width:80px" >';
		if ($_SESSION['startperiod']<$_SESSION['janr'] ){
			unset($rang[36]);
		}
		foreach($rang as $key=>$val){			
			if (isset($_POST['prdrange'])&& $key==$_POST['prdrange']){
				echo '<option selected="True" value ="';
			}else{
				echo '<option value ="';
			}
				echo $key.'">'.$val.'</option>';		
		}		
	echo'	</select>
		</td></tr>';
      echo '<tr>
              <td>查询顺序:</td>
              <td colspan="2">
                <input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' />升序  
                <input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />降序
              </td>
			 </tr>';
 	
    echo '<tr>
     		<td>单元分组</td>
			  <td  colspan="2">';
			  SelectUnitsTag();
			
		 echo'</td></tr>';
	echo '<tr>
	 		<td>凭证类别:</td>
    		<td colspan="2">';
			if (empty($sfz0)&&empty($sfz1)&&empty($sfz2)){
				echo '<input type="checkbox" name="sfzchk0" value="1"  checked />转账       
							<input type="checkbox" name="sfzchk1" value="1" checked />收款
							<input type="checkbox" name="sfzchk2" value="1" checked />付款';
			}else{	
				echo '<input type="checkbox" name="sfzchk0" value="1"  '.($sfz0==1?"checked":"").'   />转账       
							<input type="checkbox" name="sfzchk1" value="1"  '.($sfz1==1?"checked":"").' />收款
							<input type="checkbox" name="sfzchk2" value="1" '.($sfz2==1?"checked":"").' />付款';
			}            
	echo'</td></tr>';
	echo '<tr>
					<td> 凭证号码范围:</td>	
					<td colspan="2"><input type="text"  name="transno" size="20" maxlength="20" value="'.$_POST['transno'].'" pattern="(\d{1,4}-\d{1,4})?|(\d{1,4},)|[1-9]{1,4}$)"  placeholder="输入如:12-35 或8,15,18"  /></td>
			</tr>
	     <tr>
					<td> 科目编码|名:</td>
					<td colspan="2" ><input type="text"  name="accname" size="30" maxlength="30" value="'.$_POST['accname'].'" pattern="(^[1-6]\d{1,10})|(^[\u0391-\uFFE5\s]+$)"  placeholder="科目编码如:1122;科目名:汉字+空格" /></td>
				</tr>
			<tr>
				<td> 查询金额范围:</td>
				<td colspan="2" ><input type="text"  name="SearchAmo" size="30" maxlength="30"  placeholder="输入金额,贷方为- 不同金额用,间隔或用~做为范围"  value="'.$_POST['SearchAmo'].'"  pattern="^((-?\d{1,10})(.\d{1,2})?)~((-?\d{1,10})(.\d{1,2})?)?|(((-?\d{1,10})(.\d{1,2})?),?)*"    /></td>
			</tr>
			<tr>
				<td>摘要关键词:</td>
				<td colspan="2" ><input type="text"  name="narrative" size="40" maxlength="40" value="'.$_POST['narrative'].'"  pattern="^[\u0391-\uFFE5\s]+$" placeholder="输入汉字、空格" /></td>
			</tr>';
  echo '</table>';
	echo '<br/><div class="centre">
				<input type="submit" name="Search" value="' . _('Search Now') . '" />
				<input type="submit" name="crtExcel" value="导出Excel" /><br/>	';	
	if (!isset($_POST['Search'])	AND !isset($_POST['Go'])	AND !isset($_POST['Next'])	AND !isset($_POST['Previous'])){ 
		echo '<div class="page_help_text">
				功能简介：以会计期间按月、季、年为查询范围;分组查询;凭证号查询,输入格式如:1-8或2,8,9</br>
					凭证类别:收款、付款、转账分类;科目编码|名查询:以科目编码或部分,科目名中的关键词, </br>
					关键词以空格分隔符；摘要关键词查询:关键词以空格为分隔符,金额范围查询以-为区间符号,<br>
					大于等于小于等于-两面金额，以,为或有金额分隔符.<br>
					</div>';
	}
//翻页开始
if (isset($_POST['crtExcel'])	OR isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	if   ($ERPPrd+$prdrange > $_SESSION['period']){
		prnMsg('你选择查询期间超出范围！','info');
		echo '</form>';  
		include('includes/footer.php');
	  	exit;
	}
	$strno=''; 
	if ($_POST['transno']!=''){
		if ( strpos($_POST['transno'],'-')>0 ){
				$tranarr=explode('-', $_POST['transno']);					
			if ((int)$tranarr[0] <= (int)$tranarr[1]){				
				$n=(int)$tranarr[0];
				$p=(int)$tranarr[1];
			}else {
				$p=(int)$tranarr[0];
				$n=(int)$tranarr[1];						
			}	
			for ($i = $n; $i <= $p; $i++) {					
				$strno.='"'.$i.'",';
			} 					
			$strno=substr($strno,0, -1);
		}else{
			$strno=$_POST['transno'];							 		
		}
	} 	
	  $ERPPrd=$_POST["ERPPrd"]; 	 
		$sql="SELECT gltrans.typeno,
								systypes.typename, 
								gltrans.type,
								gltrans.trandate,
								gltrans.transno,
								gltrans.account,
								chartmaster.accountname,
								gltrans.narrative,
								toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
								toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
								gltrans.tag,							
								gltrans.periodno,
								gltrans.jobref
							FROM gltrans
							LEFT JOIN chartmaster ON gltrans.account=chartmaster.accountcode						
							LEFT JOIN systypes
								ON gltrans.type=systypes.typeid	WHERE periodno >='";
			if ($_POST['prdrange']==0){
				 $firstprd=$_POST['ERPPrd'];
   				 $endprd=$_POST['ERPPrd'];		
			}elseif ($_POST['prdrange']==1) {
				 $firstprd=$_POST['ERPPrd']-1;
   				 $endprd=$_POST['ERPPrd']+1;			
			}elseif ($_POST['prdrange']==3) {
				 $firstprd=$_SESSION['janr']+ceil(($_POST['ERPPrd']-$_SESSION['janr']+1)/3)*3-3;
   				 $endprd=$_SESSION['janr']+ceil(($_POST['ERPPrd']-$_SESSION['janr']+1)/3)*3-1;
			}elseif ($_POST['prdrange']==12) {
				 $firstprd=$_SESSION['janr'];
   				 $endprd=$_POST['ERPPrd'];		
			}elseif ($_POST['prdrange']==36) {
				 $firstprd=$_POST['ERPPrd'];
   				 $endprd=$_SESSION['period'];		
			}
			  $sql.=$firstprd ."' AND  periodno <='".$endprd."' " ;
			if (isset($_POST['UnitsTag'] ) AND $_POST['UnitsTag'] !=0 ){
				$sql.=" AND gltrans.tag= ".$_POST['UnitsTag']." ";
			}
			if (!empty($strno) ){         
				$sql.=" AND transno in ( ".$strno." )  ";          
			} 
			$sq=''; 
        if ($sfz1==1 && $sfz2==1 &&$sfz0!=1 ){
			$sq=" AND ( account like '1002%'  or account like '1001%') ";
		}elseif ($sfz1==1&&$sfz0==1 &&$sfz2!=1){
			$sq= " AND ((account like '1002%'  or account like '1001%') AND amount > 0) OR (account  not like '1002%'  and account not like '1001%') ";
		}elseif ($sfz0==1&&$sfz2==1 &&$sfz1!=1){
			$sq=" AND ((account like '1002%'  or account like '1001%') AND amount < 0) OR (account  not like '1002%'  and account not like '1001%') ";
		}elseif ($sfz2==1 &&$sfz1!=1 &&$sfz0!=1){
			$sq=" AND ( account like '1002%'  or account like '1001%') AND amount < 0  ";
		}elseif ($sfz1==1 &&$sfz2!=1 &&$sfz0!=1){
			$sq=" AND (account like '1002%'  or account like '1001%') AND amount > 0  ";
		}elseif ($sfz0==1 &&$sfz2!=1 &&$sfz1!=1){
			$sq=" AND account  not like '1002%'  and account not like '1001%'  ";
		}
		//摘要
		if ($_POST['narrative']!=''){
			$str=str_replace(' ', '%',  trim($_POST['narrative']));
			$strarr=str_split($str, 1);
			$p=-1;
			foreach($strarr as $val){
				if ($val=='%'){
					if ($p==-1){
					$strr.=$val;
					   $p=0;
					}else{
						$p=-1;
					}
				}else{
					$strr.=$val;
					$p=-1;
				}
			}
			$SearchStr = '%'.$strr .'%';
			$sq.= ' AND narrative  like "'.$SearchStr.'" ';
		}
		//金额
	if($_POST['SearchAmo']!=''){	
		if ( strpos($_POST['SearchAmo'],',')>0 ){//11,23,12,44,
			if (strlen($_POST['SearchAmo'])-1==strripos($_POST['SearchAmo'],',')){
				$amoarr=explode(',',substr($_POST['SearchAmo'],0,-1));
			}else{
				$amoarr=explode(',',$_POST['SearchAmo']);
			}
			$sq.= ' AND (';
			for($i=0;$i<count($amoarr);$i++){
				if ($i==0){
					$sq.= ' amount='.$amoarr[$i].' ';
				}else{
					$sq.= 'OR amount='.$amoarr[$i].' ';
				}
			}
			$sq.=' )';
	
		}elseif(strpos($_POST['SearchAmo'],'~')>0){
			
				 $amoarr=explode('~',$_POST['SearchAmo']);
              
					if ( floatval($amoarr[1])>= floatval($amoarr[0])){
						$sq.='AND amount>='.$amoarr[0].' AND amount<='.$amoarr[1].' ';
					}else{
						$sq.= 'AND amount<='.$amoarr[0].' AND amount>='.$amoarr[1].' ';
					}
			
		}else{
			$sq.= 'AND amount='.$_POST['SearchAmo'] .' ';
		}
	}
	if ($_POST['accname']!=''){
		if  (is_numeric($_POST['accname'])){
			//
			$sq.= ' AND  account  like "'.$_POST['accname'].'%"';
		}else{
				$SearchString = '%' . str_replace(' ', '%',  $_POST['accname'] . '%');
				$sq.= " AND chartmaster.accountname LIKE  '".$SearchString ."'";
		}
	}
	if ($sq!=''){
		if ($SearchString!=''){
			$sql.=" AND CONCAT(periodno,  transno)  in ( SELECT distinct CONCAT(periodno, transno) FROM gltrans  LEFT JOIN chartmaster ON gltrans.account=chartmaster.accountcode  WHERE  periodno >=".$firstprd ." AND  periodno <=".$endprd." ".$sq." )";  
		}else{
			$sql.=" AND  CONCAT(periodno, transno)  in (SELECT distinct CONCAT(periodno, transno) FROM gltrans WHERE periodno >=".$firstprd  ." AND  periodno <=".$endprd." ".$sq." )";  
		}
	}      	
		if ($_POST['queryad']==1){
			$sql.=" ORDER BY gltrans.periodno,gltrans.transno, gltrans.typeno DESC";  
		}else{
			$sql.=" ORDER BY gltrans.periodno,gltrans.transno, gltrans.typeno  ASC"; 
		}
	  //prnMsg($sql);
			$result = DB_query($sql);
			$ListCount=DB_num_rows($result);		
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}
}	
if ($ListCount>0 AND (isset($_POST['crtExcel'])	OR isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])	OR isset($_POST['Previous']))){
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if (isset($_POST['Next'])) {
		if ($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if (isset($_POST['Previous'])) {
		if ($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
	if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
		echo '<select name="PageOffset1">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if ($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			} else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}
		echo '</select>
			<input type="submit" name="Go1" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}
	echo	'	<br />
		<table cellpadding="2">';
	echo '<tr>
	  		<th>' . ('Date') . '</th>
			<th>凭证号</th>					
			<th>' . _('Account Code') . '</th>
			<th>' . _('Account Description') . '</th>
			<th>' . _('Narrative') . '</th>
			<th>' . _('Debits').' '.$_SESSION['CompanyRecord'][1]['currencydefault'] . '</th>
			<th>' . _('Credits').' '.$_SESSION['CompanyRecord'][1]['currencydefault'] . '</th>	';
	
			echo'<th>单元分组 </th>';
	    
		  echo'	<th>' . _('Print').' </th>				
		</tr>';
		$k = 0; //row counter to determine background colour
		$RowIndex = 0;
 	if (DB_num_rows($result) <> 0) {
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	if (isset($_POST['crtExcel']) ) { 	
		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();
		// Set document properties
		$objPHPExcel->getProperties()->setCreator("webERP")
										->setLastModifiedBy("webERP")
										->setTitle("Petty Cash Expenses Analysis")
										->setSubject("Petty Cash Expenses Analysis")
										->setDescription("Petty Cash Expenses Analysis")
										->setKeywords("")
										->setCategory("");
		$objPHPExcel->getActiveSheet()->mergeCells('A1:I1');
		$objPHPExcel->getActiveSheet()->setCellValue('A1', '会计凭证查询');
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
		$objPHPExcel->getActiveSheet()->setCellValue('A2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('B2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('C2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('D2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('E2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('F2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('G2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('H2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('I2', '');
		$objPHPExcel->getActiveSheet()->setCellValue('A3', '编制单位:'.$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['coyname']);
		$objPHPExcel->getActiveSheet()->mergeCells('D3:E3');
		$objPHPExcel->getActiveSheet()->setCellValue('D3', '日期:'.$BalanceDate);
		$styleThinBlackBorderOutline = array(
			'borders' => array (
			'outline' => array (
					'style' => PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
				//  'style' => PHPExcel_Style_Border::BORDER_THICK, // 另一种样式
					'color' => array ('argb' => 'FF000000'),          //设置border颜色
			), 
			),);  
		$styleBorderR= array(
			'borders' => array (
				'right'     => array (
								'style' => PHPExcel_Style_Border::BORDER_THIN
						)   ,
			),);  
		$objPHPExcel->getActiveSheet()->getStyle('1')->getAlignment()->setWrapText(true);
		$objPHPExcel->getActiveSheet()->getStyle('G:I')->getNumberFormat()->setFormatCode('#,###');
		$objPHPExcel->getActiveSheet()->setCellValue('A4', '序号');
		$objPHPExcel->getActiveSheet()->setCellValue('B4', '日期');
		$objPHPExcel->getActiveSheet()->setCellValue('C4', '凭证号');
		$objPHPExcel->getActiveSheet()->setCellValue('D4', '科目编码');
		$objPHPExcel->getActiveSheet()->setCellValue('E4', '科目名称');				
		$objPHPExcel->getActiveSheet()->setCellValue('F4', '摘要');
		$objPHPExcel->getActiveSheet()->setCellValue('G4', '借方金额');
		$objPHPExcel->getActiveSheet()->setCellValue('H4', '贷方金额');
		$objPHPExcel->getActiveSheet()->setCellValue('I4', '单元分组');
		$i = 5;
	}
		$LastJournal = 0;
		$r=0;
		while ($myrow = DB_fetch_array($result)  AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
				if ($myrow['transno']!=$LastJournal ) {
					if ($r==1){
						echo '<tr class="EvenTableRows">';
						$r=0;
					}else{
						echo '<tr class="OddTableRows">';
						$r=1;
					}
					echo '<td>' .  ConvertSQLDate($myrow['trandate']) .'</td>
						  <td  title="'.$myrow['transno'].'" >'. $_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno']. '</td>';
				}else {			
					if ($r==1){
						echo '<tr class="EvenTableRows"><td colspan="2"></td>';
						$r=0;
					}else {
						echo '<tr class="OddTableRows"><td colspan="2"></td>';
						$r=1;
					}
				}
			echo '<td >' . $myrow['account'] . '</td>
					<td >' . $myrow['accountname'] . '</td>
					<td>'.$myrow['narrative']. '</td>
					<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord'][1]['decimalplaces'])) . '</td>
					<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord'][1]['decimalplaces']) ). '</td>';
					echo'<td >' . $UnitsTag[$myrow['tag']] . '</td>';
					/*
			if (isset($_SESSION['Tag'])){

		   		if ($myrow['tag']>0){
					echo'<td >' . $UnitsTag[$myrow['tag']] . '</td>';
				}elseif ($myrow['tag']<0){
					echo'<td >' . $UnitsTag[$myrow['tag']] . '</td>';
				}else{
					echo'<td ></td>';
				}
		    }*/
			if ($myrow['transno']!=$LastJournal) {
				echo '<td class="number"  ><a href="PDFTrans.php?JournalNo='.$myrow['periodno'].'^'.$myrow['transno'].'">' . _('Print')  . '</a></td></tr>';
				$LastJournal = $myrow['transno'];
			} else {
				echo '<td colspan="1"></td></tr>';
			}
            if (isset($_POST['crtExcel']) ) { 
			$objPHPExcel->setActiveSheetIndex(0);
			$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $i-4);
			$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['trandate']);
			$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno']);
			$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $myrow['account']);
			$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['accountname']);
			$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['narrative']);
			$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['Debits']);
			$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $myrow['Credits']);
			$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $UnitsTag[$myrow['tag']]);
			$i++;
		    }
			$RowIndex = $RowIndex + 1;
		}
		if (isset($_POST['crtExcel']) ) { 
			foreach(range('A','K') as $columnID) {
				$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
					->setAutoSize(true);
			}				
			$objPHPExcel->setActiveSheetIndex(0);
		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
		$objWriter->setIncludeCharts(TRUE);
		$RootPath=dirname(__FILE__ ) ;
		$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/会计凭证查询_'.PeriodGetDate($_POST['ERPPrd']).'.xlsx';
		$objWriter->save($RootPath.'/'.$outputFileName);
		echo '<p><a href="'. $outputFileName. '">' . _('click here') . '</a> 下载文件<br />';
	    }
		echo '</table>';  
		if (isset($ListPageMax) AND  $ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
			echo '<select name="PageOffset2">';
				$ListPage = 1;
				while ($ListPage <= $ListPageMax) {
					if ($ListPage == $_POST['PageOffset']) {
						echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
					} else {
						echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
					}
					$ListPage++;
				}
				echo '</select>
					<input type="submit" name="Go2" value="' . _('Go') . '" />
					<input type="submit" name="Previous" value="' . _('Previous') . '" />
					<input type="submit" name="Next" value="' . _('Next') . '" />';
				echo '</div>';
		}
}
echo '</div>
	</form>';
include('includes/footer.php');
?>