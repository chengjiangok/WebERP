<?php
/* $Id: GLAccountDay.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-08-13 06:30:16 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-09-26 13:56:24
 */

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup; 
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
include ('includes/session.php');
//require_once 'Classes/PHPExcel.php'; 
$Title = '应收应付对账';
$ViewTopic= 'GLAccountDay';
$BookMark = 'GLAccountDay';

include('includes/SQL_CommonFunctions.inc');
include  ('includes/header.php');

echo'<script type="text/javascript">
	function checkinput(obj){
	var td=obj.parentNode;  
	//alert( td.parentNode.rowIndex);
	glaccday.rows[td.parentNode.rowIndex].cells[td.cellIndex].getElementsByTagName("INPUT")[3].value=1;
 }
 function clickinput(obj){
	var td=obj.parentNode; 
	alert( td.parentNode.rowIndex);
	glaccday.rows[td.parentNode.rowIndex].cells[td.cellIndex].getElementsByTagName("INPUT")[3].value=1;
 }
</script>';


if (!isset($_POST['query'])) {
   $_POST['query']=1;
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} 
if (abs($_POST['UnitsTag'])>0){
	$tag=abs($_POST['UnitsTag']);
}else{
	$tag=1;

}
if ($_POST['UnitsTag']==0){
   $tagusers=implode(",",$_SESSION[$_SESSION['UserID']]);
}else{
   $tagusers=$_POST['UnitsTag'];	
}

	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		$Title . '" /> ' .// Icon title.
		$Title . '</p>';// Page title.  

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	    
	 
	   	  <input type="hidden" name="Account" value="' . $_POST['Account'] . '" />
		  <input type="hidden" name="CheckAmo" value="' . $_POST['CheckAmo'] . '" />
		  <input type="hidden" name="chkflg" value="' . $_POST['chkflg'] . '" />
	     ';

    echo '<div>';
	echo '<table class="selection">';  
	echo '<tr>
	      <td>' . _('Select Period To')  . '</td>
		  <td >';
		  SelectPeriod($_SESSION['period'],$_SESSION['period']);
		
	echo '</td>
		 <td></td>
		 </tr>';
	echo '<tr>
			<td>科目代码/名:</td>
			<td colspan="2">
			 <input type="text"  name="Account" size="20" maxlength="20" value="'.$_POST['Account'].'" /></td>
		</tr>';  

      
     echo '<tr>
     	    <td>单元分组</td>
  			<td>';
			  SelectUnitsTag();
		 echo'</select></td><td></td></tr>';
          

	echo '	</table>
		<br />';
	echo '<div class="centre">
			<input type="submit" name="Search" value="显示查询" />
	     	<input type="submit" name="check" value="对账更新" />
			<input type="submit" name="crtExcel" value="创建Excel" />
			
		</div>';
		$amo=0;		
		$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['ERPPrd'] . "'";
		$Result = DB_query($sql);
		$row = DB_fetch_row($Result);
		$BalanceDate = $row[0];//ConvertSQLDate(
			$endprd=$_POST['ERPPrd'];	
		
			$dt=PeriodGetDate($_POST['ERPPrd']);
		$mth=date("m",strtotime($dt));
		$janr=$_POST['ERPPrd']-$mth+1;
				$sql="SELECT gltrans.account,
							accountname,
							checktext,
							remark,
							glaccountcheck.amount,
							checkdate,
							SUM(sumamount(gltrans.amount, periodno,0,$endprd)) as qmbalance
						FROM gltrans 
						LEFT JOIN chartmaster ON  gltrans.account=accountcode
						LEFT JOIN glaccountcheck ON  glaccountcheck.account=gltrans.account
						WHERE periodno<=$endprd AND periodno>=0 AND gltrans.tag IN (".$tagusers.")
						     AND LEFT(gltrans.account,4) IN ('1221','2241','1122','2202') ";
					if(isset($_POST['Account'])&& $_POST['Account']!=""){		

						if(preg_match("/^\d*$/", $_POST['Account'])){

							$sql .= "  AND gltrans.account like '".$_POST['Account']."%' ";    
						}else{
							$sql .= "  AND  accountname like '%". $_POST['Account']."%' "; 

						}	  

					}	

						$sql.="	GROUP BY gltrans.account,checktext,remark,accountname";
						
						$result=DB_query($sql);
				//echo $sql;
       
if (isset($_POST['crtExcel']) ) { 
	prnMsg('该功能调试中！','info');
	
	$SQL= "CALL GLAccountDay('".$_POST['ERPPrd']."','" .$_SESSION['janr']."')";
	$AccountsResult = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));
	$ListCount=DB_num_rows($AccountsResult);
	if ($ListCount ==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	}else{
	$objExcel = new PHPExcel(); 
    
 	$objProps = $objExcel->getProperties(); 
	$objProps->setCreator("ChengJiang"); 
	$objProps->setLastModifiedBy("Cheng Jiang"); 
	$objProps->setTitle("账龄及对账表"); 
	$objProps->setSubject("��龄及对账表"); 
	$objProps->setDescription("账龄及对账表"); 
	$objProps->setKeywords("账龄及对账表"); 
	$objProps->setCategory("账龄及对账表"); 
	//添加一个新的worksheet 
	$objExcel->createSheet(); 	
	$objExcel->setActiveSheetIndex(0); 
	$objExcel->getSheet(1)->setTitle('账龄及对账表'); 
	$objSheet1 = $objExcel->getActiveSheet(); 

	$mulit_arr =  array(array(  '序号', _('Account'), _('Account Name'),'方向','余额','账龄','摘要',
	'摘要','确认余额'));
	
		$objSheet1->getColumnDimension('A')->setWidth(6) ; 
		$objSheet1->getColumnDimension('B')->setWidth(15);   
		$objSheet1->getColumnDimension('C')->setWidth(35);    
		$objSheet1->getColumnDimension('D')->setWidth(12);   
		$objSheet1->getColumnDimension('E')->setWidth(12);     
		$objSheet1->getColumnDimension('F')->setWidth(12);   
		$objSheet1->getColumnDimension('G')->setWidth(12);   
		$objSheet1->getColumnDimension('H')->setWidth(12);   
		$objSheet1->getColumnDimension('I')->setWidth(12);    
		 
		$objSheet1->mergeCells('A1:I1');
		$objSheet1->setCellValue('A1', '账龄及对账表');
		$objSheet1->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objSheet1->getStyle('A1')->getFont()->setSize(16);
		$objSheet1->setCellValue('A2', '');
		$objSheet1->setCellValue('B2', '');
		$objSheet1->setCellValue('C2', '');
		$objSheet1->setCellValue('D2', '');
		$objSheet1->setCellValue('E2', '');
		$objSheet1->setCellValue('F2', '');
		$objSheet1->setCellValue('G2', '');
		$objSheet1->setCellValue('H2', '');
		$objSheet1->setCellValue('I2', '');
		$objSheet1->setCellValue('A3', '编制单位:'.$_SESSION['CompanyRecord']['coyname']);
		$objSheet1->mergeCells('D3:E3');
		$objSheet1->setCellValue('D3', '日期:'.$BalanceDate);
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

	//foreach($mulit_arr as $k=>$v){
		$i=1;
		$k=4;
		while ($myrow=DB_fetch_array($AccountsResult)) {
			if ($myrow['qmye']<0 ){
				$jd='贷';
			}else{
				 $jd='借';
		   
		   }
		$objExcel->getActiveSheet()->setCellValue('A'.$k, $i);
		$objExcel->getActiveSheet()->setCellValue('B'.$k, $myrow['account']);
		$objExcel->getActiveSheet()->setCellValue('C'.$k, $myrow['accountname']);
		$objExcel->getActiveSheet()->setCellValue('d'.$k, $jd);
		$objExcel->getActiveSheet()->setCellValue('e'.$k, locale_number_format(abs($myrow['qmye']),$_SESSION['CompanyRecord']['decimalplaces']));
		$objExcel->getActiveSheet()->setCellValue('f'.$k, $myrow['aging']);
		$objExcel->getActiveSheet()->setCellValue('g'.$k,$myrow['checktxt']);
		$objExcel->getActiveSheet()->setCellValue('h'.$k, $myrow['chkdate']);
		$objExcel->getActiveSheet()->setCellValue('i'.$k, '');
	
		$objSheet1->getStyle( 'A'.$k.':I'.$k)->applyFromArray($styleThinBlackBorderOutline);
	    $objSheet1->getStyle( 'A'.$k)->applyFromArray($styleBorderR);
		$objSheet1->getStyle( 'B'.$k)->applyFromArray($styleBorderR);
        $objSheet1->getStyle( 'C'.$k)->applyFromArray($styleBorderR);
		$objSheet1->getStyle( 'D'.$k)->applyFromArray($styleBorderR);
		$objSheet1->getStyle( 'E'.$k)->applyFromArray($styleBorderR);
		$objSheet1->getStyle( 'F'.$k)->applyFromArray($styleBorderR);
		$objSheet1->getStyle( 'G'.$k)->applyFromArray($styleBorderR);
		$objSheet1->getStyle( 'H'.$k)->applyFromArray($styleBorderR);
		$objSheet1->getStyle( 'I'.$k)->applyFromArray($styleBorderR);
	
	}
	
	$objWriter = PHPExcel_IOFactory::createWriter($objExcel,'Excel2007');
	$objWriter->setIncludeCharts(TRUE);
	 $RootPath=dirname(__FILE__ ) ;
   $outputFileName ='companies/'.$_SESSION['DatabaseName'].'/reports/账龄及对账表_'.periodymstr($_POST['ERPPrd'],0).'.xlsx';
   $objWriter->save($RootPath.'/'.$outputFileName);
	   echo '<p><a href="' . $outputFileName . '">' . _('click here') . '</a> ' . '下载文件'. '<br />';
  	}
	 
	 
}elseif(isset($_POST['check']) ||isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {

		if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		
			$_POST['PageOffset'] = 1;
		}
  
		//$AccountsResult = DB_query($sql, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));
		
		$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}
		
		if ($ListCount>0 AND (isset($_POST['check']) ||isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])	OR isset($_POST['Previous']))){
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
		
		if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
		echo '<select name="PageOffset">';
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
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />';
			echo '</div>';
		}

		echo	'	<br />';
			echo '<table id="glaccday" cellpadding="2" class="selection">';
 
		$TableHeader = '<tr>
		                   <th colspan="10" height="2">
								<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
		                 		</div> 
							</th>
						</tr>
		                <tr> 
							<th>' . _('Account') . '</th>
							<th  width="220" >' .'&nbsp;&nbsp;&nbsp;'. _('Account Name') .'&nbsp;&nbsp;&nbsp;'. '</th>
							<th>历史对账</th>
							<th>借贷</th>
							<th>余额</th>
							<th  width="20">确认余额</th>				
						
							<th  width="20">备注</th>
							<th><input type="checkbox" name="Selectcheck"   onchange="clickinput(this)" value="1" /></th>
						</tr>';

		$j = 0;
		$k=0;
		//$k=DB_num_rows($AccountsResult);
		echo $TableHeader;
		$RowIndex = 0;
		//$Resultarr=array();
		
		
			$Start=($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax'];
			while ($row=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			
			//for($i=$Start;$i<count($ROW)&& ($RowIndex <> $_SESSION['DisplayRecordsMax']) ;$i++){
					$showstaus=0;		
					
				
					if ($row['flag']==1){
						$ActEnquiryURL = '<a href="'. $RootPath . '/GLAccountInquiry.php?show=GLTB&amp;acp=' .urlencode(  $row['account'].'^'.$_SESSION['janr'] . '^' . $_POST['ERPPrd']  ). '" target="_black">' . $row['account'] . '</a>';
					}else{
						$ActEnquiryURL = '<a href="'. $RootPath . '/SelectGLAccount.php?Act=' .$row['account'].'" target="_black">' . $row['account'] . '</a>';
					}
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}
					
						if ($row['qmbalance']>0){
							$jd="借";
						}else{
							$jd="贷";
						}
						if (!isset($_POST['Remark'.$row['account']]))
						$_POST['Remark'.$row['account']]=$row['remark'];

						if (!isset($_POST['CheckAmo'.$row['account']]))
						$_POST['CheckAmo'.$row['account']]=$row['qmbalance'];
						echo'<td>'.$ActEnquiryURL.'</td>
								<td >'.$row['accountname'].'</td>
								<td >'.$row['checktext'].'</td>
								<td >'.$jd.'</td>
								<td class="number">'.locale_number_format(abs($row['qmbalance']),$_SESSION['CompanyRecord'][$tag]['decimalplaces']).'
									<input type="hidden" name="'.$row['account'].'QMBalance"  size="15" maxlength="20" value="'.$row['qmbalance'].'"/></td>
								<td >	
								   <input  class="number" type="text" name="CheckAmo'.$row['account'].'"  size="10" maxlength="12" value="'. $_POST['CheckAmo'.$row['account']].'"  title="请在这里输入核对正确的余额!" onchange="checkinput(this)" /></td>
							
								<td title="'.$row['checkdate'].'">
									<input type="hidden" name="'.$row['account'].'CheckDate"  size="15" maxlength="20" value="'.$row['checkdate'].'"/>
								    <input type="text" name="Remark'.$row['account'].'"  size="15" maxlength="20" value="'.$_POST['Remark'.$row['account']].'"/></td>
								
								<td ><input type="checkbox" name="chkflg'.$row['account'].'" value="1" /></td>
								</tr>';
						
						$RowIndex ++;
				}//end while
		echo '</table><br />';
		echo '<input type="hidden" name="resultarr" value="' . $Resultarr . '" />';

	
    }
	if (isset($_POST['check'])) {
	
		//prnMsg($_POST['CheckAmo'][1].'该功能'.$_POST['chkflg'][1].'调试中！'.$f,'info');
		foreach ($_POST as $key => $value) {
			# code...
		   //prnMsg($key.'='.mb_strstr($key,'QMBalance',TRUE) );
			if (mb_strstr($key,'QMBalance')) {
				$Index=mb_strstr($key,'QMBalance',TRUE) ;
				
				if ($_POST['chkflg'.$Index]){
					prnMsg($_POST[$Index.'QMBalance']);
					if (empty($_POST[$Index.'CheckDate'])){
					$SQL="INSERT INTO glaccountcheck( `checkdate`,
														`period`,
														`account`,
														`remark`,
														`amount`,
														`checktext`,
														`checktype`,
														`regid`,
														`userid`,
														`flg`)

													VALUES ('".date('Y-m-d h:i:s')."',
															0,
															'".$Index."',
															'".$_POST['Remark'.$Index]."',
															".$_POST['CheckAmo'.$Index].",
															'',
															0,
															0,						
															'',
															0) ";
					}else{
						$SQL="UPDATE glaccountcheck 
						         SET `checkdate`='".date('Y-m-d')."',
								
									`remark`='".$_POST['Remark'.$Index]."',
									amount=".$_POST['CheckAmo'.$Index].",
									`checktext`= CONCAT(`checkdate`,`remark`,amount),
									`checktype`=0,
									`userid`='',
									`flg`=0	
							  WHERE 	`account`='".$Index."' ";

					}
					echo  $SQL.PHP_EOL;
					$result1=DB_query($SQL);
					DB_free_result($result1);		
				}
			     else
				 prnMsg('=='.$_POST['chkflg'.$Index]);
			
			}
			

		}
		$RowIndex =0;
	
	//	if (DB_num_rows($AccountsResult) <> 0) {
	//		DB_data_seek($AccountsResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	//	}
		$f=0;
		$str='';
	/*	while ($row=DB_fetch_array($AccountsResult) AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
			if ($_POST['chkflg'][$f]=='1') {
				$amo+=$_POST['CheckAmo'][$f];
			}
		
            $str.=$_POST['chkflg'][$f].',';
			$f++;
			$RowIndex = $RowIndex + 1;
		}//while
			foreach($_POST['chkflg'] as $val){
			if ($val==1){
				
			}
			$f++;
		}*/
	     // prnMsg($SQL.'功能调试中！'.$f,'info');
	}
	echo '</div>
	</form>';
	}
include('includes/footer.php');

?>
