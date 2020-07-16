<?php
/* $Id: GLTrialBalance.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2020-03-28 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2020-03-28
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
$Title ='科目汇总表';
$ViewTopic= 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'TrialBalance';// Anchor's id in the manual's html document.
include('includes/SQL_CommonFunctions.inc');
include  ('includes/header.php');
 if (!isset($_POST['ERPPrd'])OR $_POST['ERPPrd']==''){
		$_POST["ERPPrd"]=$_SESSION['period'];
	}
if (!isset($_POST['prdrange']) OR $_POST['prdrange']==''){
     $_POST['prdrange']=0;		  	
	}
			
if (!isset($_POST['UnitsTag'])){
    $_POST['UnitsTag']=0;		 
}
	
if (!isset($_POST['query'])) {
   $_POST['query']=1;
}
if (!isset($_POST['querydata'])) {
	$_POST['querydata']=1;
 }
 
if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
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


if(!isset($_POST['ImportExcel'])) {
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		_('Print Trial Balance') . '" /> ' .// Icon title.
		$Title . '</p>';// Page title.  
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '"  method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	      <input type="hidden" name="ERPPrd" value="' . $_POST['ERPPrd'] . '" />
	   
	      <input type="hidden" name="query" value="' . $_POST['query'] . '" />
	      <input type="hidden" name="account" value="' . $_POST['account'] . '" />';
    //	   echo'  <input type="hidden" name="UnitsTag" value="' . $_POST['UnitsTag'] . '" />';
    echo '<div>';
	echo '<table class="selection">';
	echo '<tr>
	      <td>' . _('Select Period To')  . '</td>
		  <td >';
		  SelectPeriod($_SESSION['period'],$_SESSION['startperiod']);
		 
    echo '范围</td>';
		$rang=array('0'=>'月度', '3'=>'季度','12'=>'本年','24'=>'上年','36'=>'前年');
    echo '<td>  
		<select name="prdrange" size="1" style="width:80px" >';
		if (($_SESSION['janr']-$_SESSION['startperiod'])<=0 ){
			unset($rang[36]);
			unset($rang[24]);
		}elseif (($_SESSION['janr']-$_SESSION['startperiod'])<=12 ){
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
	echo'</select>
		</td></tr>';
	echo '<tr>
		<td>查询格式</td>
		<td colspan="2">
			<input type="radio" name="query" value="3"  '.($_POST['query']==3 ? 'checked':"").' >'._('All').'          
			<input type="radio" name="query" value="2"   '.($_POST['query']==2 ? 'checked':"").'  >'._('Total').'
			<input type="radio" name="query" value="1"   '.($_POST['query']==1 ? 'checked':"").' >'._('Detail').' 
		</td>
		</tr>';
	echo '<tr>
		<td>查询数据</td>
		<td colspan="2">
			<input type="radio" name="querydata" value="0"  '.($_POST['querydata']==0 ? 'checked':"").' >'._('All').'          
			<input type="radio" name="querydata" value="1"   '.($_POST['querydata']==1 ? 'checked':"").'  >精简
			<input type="radio" name="querydata" value="2"   '.($_POST['querydata']==2 ? 'checked':"").' >本月发生 
			<input type="radio" name="querydata" value="3"   '.($_POST['querydata']==3 ? 'checked':"").' >本年发生
		</td>
		</tr>';
	
   echo '<tr>
   			<td> 查询科目编码|名:</td>
			<td colspan="2" ><input type="text"  name="Account" size="25" maxlength="25" value="'.$_POST['Acount'].'" pattern="(^[1-6]\d{1,10})|(^[\u0391-\uFFE5\s]+$)"  placeholder="科目编码如:1122;科目名:汉字+空格" /></td>
		  </tr>'; 
		echo '<tr>
				<td>单元分组</td>
				<td>';
				SelectUnitsTag();

		echo'</td>
			<td></td>
			</tr>';	
	echo '	</table>
		<br />';
	echo '<div class="centre">
			<input type="submit" name="Search" value="显示查询" />	
			<input type="submit" name="ImportExcel" value="导出Excel" />			
		</div>';
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
	
	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" . $_POST['ERPPrd'] . "'";
	$Result = DB_query($sql);
	$row = DB_fetch_row($Result);
	$BalanceDate = $row[0];//ConvertSQLDate(
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
			}elseif ($_POST['prdrange']==24 &&$_SESSION['janr']>=13){
				 $firstprd=$_SESSION['janr']-12;
   				 $endprd=$_SESSION['janr']-1;	
			}elseif($_POST['prdrange']==36 && $_SESSION['janr']>=25){
				  $firstprd=$_SESSION['janr']-24;
   				 $endprd=$_SESSION['janr']-13;		
			}	
	
		$dt=PeriodGetDate($_POST['ERPPrd']);
	$mth=date("m",strtotime($dt));
	$janr=$_POST['ERPPrd']-$mth+1;
	
	   $sql="SELECT account,
	             	accountname,
					SUM(sumamount(amount, periodno,0,$firstprd-1)) as qcbalance, 
					SUM(toamount(amount, periodno,$firstprd,$endprd,1,flg)) as debittotal, 
					SUM(toamount(amount, periodno,$firstprd,$endprd,-1,flg)) as credittotal,
					SUM(sumamount(amount, periodno,0,$endprd)) as qmbalance,
					SUM(toamount(amount, periodno,$janr,$janr+11,1,flg)) as debityear,
					SUM(toamount(amount, periodno,$janr,$janr+11,-1,flg)) as  credityear   

			FROM gltrans 
			LEFT JOIN chartmaster ON  account=accountcode
			WHERE periodno<=$endprd AND periodno>=0 AND gltrans.tag IN (".$tagusers.") ";
		if(isset($_POST['Account'])&& $_POST['Account']!==""){		
		
			if(preg_match("/^\d*$/", $_POST['Account'])){
			
				$sql .= "  AND account like '".$_POST['Account']."%' ";    
			}else{
				$sql .= "  AND  accountname like '%". $_POST['Account']."%' "; 
			
			}	  
				
		}	
		
		$sql.="	GROUP BY account,accountname";
     
	$result=DB_query($sql);
	$QcTotal=[];
	$QmTotal=[];			
	$AmoTotal=[];
	$AmoYearTotal=[];
	
	while ($row=DB_fetch_array($result)) {
		if ((round((float)$row['qcbalance'],2)>0)){
			$qcdebit=(float)$row['qcbalance'];
			$qccredit=0;
		}else{
			$qcdebit=0;
			$qccredit=-(float)$row['qcbalance'];
		}
		if ((round($row['qmbalance'],2)>0)){
			$qmdebit=$row['qmbalance'];
			$qmcredit=0;
		}else{
			$qmdebit=0;
			$qmcredit=-$row['qmbalance'];
		}
	
		$accountrow[]=array('account'=>(string)$row['account'],'accountname'=>$row['accountname'],
							 'qcdebit'=>$qcdebit,'qccredit'=>$qccredit,
							 'debittotal'=>$row['debittotal'], 'credittotal'=>$row['credittotal'],
							'qmdebit'=>$qmdebit,'qmcredit'=>$qmcredit,
							'debityear'=>$row['debityear'],'credityear'=>$row['credityear'] ,"flag"=>1 );
		$AmoTotal[0]+=round((float)$qcdebit,POI);			
		$AmoTotal[1]+=round((float)$qccredit,POI);	

		$AmoTotal[2]+=round((float)$qmdebit,POI);			
		$AmoTotal[3]+=round((float)$qmcredit,POI);

		$AmoTotal[4]+=round((float)$row['debittotal'],POI);
		$AmoTotal[5]+=round((float)$row['credittotal'],POI);
	
		$AmoTotal[6]+=round((float)$row['debityear'],POI);
		$AmoTotal[7]+=round((float)$row['credityear'] ,POI);
	}

	//上级科目
	$SQL="SELECT t3.accountname, t3.accountcode ,t3.currcode
			FROM chartmaster t3 
			WHERE t3.accountcode  in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
			( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) AND LEFT(t3.accountcode,4)<>'1123' AND LEFT(t3.accountcode,4)<>'2203' ORDER BY t3.accountcode";
	$Result=DB_query($SQL);
	while ($Row=DB_fetch_array($Result) ) {
		$Act[(string)$Row['accountcode']]=array("accountname"=>$Row['accountname'],"currcode"=>$Row['currcode']);
	}
	if ($_POST['query']==3||$_POST['query']==2){//  全部
		foreach($Act as $key=>$val){
				
					$qcdebit=0;			
					$qccredit=0;	
					$qmdebit=0;			
					$qmcredit=0;
					$debittotal=0;
					$debittotal=0;
					$credittotal=0;					
					$debityear=0;
					$credityear=0 ;
			foreach($accountrow as $row){
				
				if ($row['account']!=""){

					if (strpos(trim($row['account']),trim($key),0)===0){
						
						$qcdebit+=$row['qcdebit'];			
						$qccredit+=$row['qccredit'];	
						$qmdebit+=$row['qmdebit'];			
						$qmcredit+=$row['qmcredit'];
						$debittotal+=$row['debittotal'];
						$credittotal+=$row['credittotal'];
					
						$debityear+=$row['debityear'];
						$credityear+=	$row['credityear'] ;
					
					}
				}
			}
			$ResultRow[]=array('account'=>(string)$key,'accountname'=>$val['accountname'],'qcdebit'=>$qcdebit,'qccredit'=>$qccredit,'debittotal'=>$debittotal,'credittotal'=>$credittotal,
			'qmdebit'=>$qmdebit,'qmcredit'=>$qmcredit,'debityear'=>$debityear,'credityear'=>	$credityear ,"flag"=>0 );
		}
	}
	if ($_POST['query']==3){//  全部
	
		$ROW=array_merge_recursive($ResultRow,$accountrow);
		unset($accountrow);
		unset($ResultRow);
		$actkey =  array_column($ROW,'account');//取出数组中serverTime的一列，返回一维数组
			
		array_multisort($actkey,SORT_ASC, SORT_STRING,$ROW,SORT_ASC, SORT_STRING);//排序，根据$serverTime 排序SORT_STRING
	}elseif($_POST['query']==2){//汇总
		$ROW=&$ResultRow;
		unset($ResultRow);
		//prnMsg($_POST['query']);
	}else{
		$ROW=$accountrow;
		unset($accountrow);
	}
if(isset($_POST['ImportExcel'])) {// producing a CSV file of customers

		$options = array("print"=>true);//,"setWidth"=>$setWidth);
		
		/*
		,"freezePane"=>"A2","setARGB"=>"['A1', 'C1']","setWidth"=>"['A' => 30, 'C' => 20]"
							   ,"setBorder"=>0,"mergeCells"=>"['A1:J1' => 'A1:J1']","formula"=>"['F2' => '=IF(D2>0,E42/D2,0)']"
							   ,"format"=>"['A' => 'General']","alignCenter"=>"['A1', 'A2']","bold"=>"['A1', 'A2']","savePath"=>"C:\Wnmp\html\GJWERP\companies\hualu_erp" );
		*/
		
		$FileName ="科目汇总表_". date('Y-m-d', time()).rand(1000, 9999);
		$TitleData=array("Title"=>'科目汇总表',"FileName"=>$FileName,"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal));	
	
		 $Header=array( '序号', '科目编码', '科目名', '期初借余额', '期初贷余额', '本期借发生额', '本期贷发生额', '期末借余额', '期末贷余额' ,'本年借累计', '本年贷累计');		  
		 //print_r($ROW);
		 exportExcelAccount($ROW,$Header,$TitleData,$options);
		
}// end if producing a CSV
  //var_dump($ROW);

if (isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])	OR isset($_POST['Previous'])){
	$FIRST=1;
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
   $wd=0;	
	//var_dump($ROW);
	$ListCount=count($ROW);
	if ($ListCount<=1) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		include('includes/footer.php');	
		exit;
	}
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
	echo '<br />';
	echo '<table cellpadding="2" class="selection">';
	$TableHeader = '<tr>
					   <th colspan="10" height="2">
							<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
							 </div> 
						</th>
					</tr>
					<tr> 
						<th>' . _('Account') . '</th>
						<th  width="220" >' .'&nbsp;&nbsp;&nbsp;'. _('Account Name') .'&nbsp;&nbsp;&nbsp;'. '</th>
						<th>期初借方</th>  
						<th>期初贷方</th>
						<th>借方合计</th>
						<th>贷方合计</th>
						<th>期末借方</th>
						<th>期末贷方</th>
						<th>借方本年累计</th>
						<th>贷方本年累计</th>
					</tr>';
	$j = 0;
	$k=0;
	echo $TableHeader;
	$RowIndex = 0;
	$ACT=$Act;

	//DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	$Start=($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax'];
	//while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
	
	for($i=$Start;$i<count($ROW)&& ($RowIndex <> $_SESSION['DisplayRecordsMax']) ;$i++){
		$showstaus=0;		
		
		if ($_POST['querydata']==3){//本年发生
			if((abs($ROW[$i]['qcdebit'])+abs($ROW[$i]['qccredit'])+abs($ROW[$i]['debittotal'])+abs($ROW[$i]['credittotal'])+abs($ROW[$i]['qmdebit'])+abs($ROW[$i]['qmcredit'])+abs($ROW[$i]['debityear'])+abs($ROW[$i]['credityear']))!=0){
				$showstaus=1;
				}
		}elseif ($_POST['querydata']==2){//本月发生
			if((abs($ROW[$i]['debittotal'])+abs($ROW[$i]['credittotal']))!=0){
				$showstaus=1;
			}
		}elseif ($_POST['querydata']==1){//精简
			if((abs($ROW[$i]['qmdebit'])+abs($ROW[$i]['qmcredit']))!=0){
				$showstaus=1;
			}
		}elseif ($_POST['querydata']==0){//全部
			$showstaus=1;
		}
		if ($ROW[$i]['flag']==1){
			$ActEnquiryURL = '<a href="'. $RootPath . '/GLAccountInquiry.php?show=GLTB&amp;acp=' .urlencode(  $ROW[$i]['account'].'^'.$_SESSION['janr'] . '^' . $_POST['ERPPrd']  ). '" target="_black">' . $ROW[$i]['account'] . '</a>';
		}else{
			$ActEnquiryURL = '<a href="'. $RootPath . '/SelectGLAccount.php?Act=' .$ROW[$i]['account'].'" target="_black">' . $ROW[$i]['account'] . '</a>';
			//					$ActEnquiryURL =$ROW[$i]['account'];
		}


		if ($showstaus==1){				
			
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
		
			printf('<td>%s</td>
					<td >%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>
					</tr>',
				$ActEnquiryURL,
				$ROW[$i]['accountname'], 			
				locale_number_format($ROW[$i]['qcdebit'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']),
				locale_number_format($ROW[$i]['qccredit'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']),
				locale_number_format($ROW[$i]['debittotal'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']),
				locale_number_format($ROW[$i]['credittotal'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']),
				locale_number_format($ROW[$i]['qmdebit'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']),
				locale_number_format($ROW[$i]['qmcredit'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']),
				locale_number_format($ROW[$i]['debityear'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']),
				locale_number_format($ROW[$i]['credityear'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']));
			$RowIndex ++;
			

		}
	}//end while
echo'<tr> 
		<th></th>
		<th  width="220" >' .'累&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;计'. '</th>
		<th>'.	locale_number_format($AmoTotal[0],POI).'</th>  
		<th>'.	locale_number_format($AmoTotal[1],POI).'</th>
	
		<th>'.	locale_number_format($AmoTotal[4],POI).'</th>
		<th>'.	locale_number_format($AmoTotal[5],POI).'</th>
		<th>'.	locale_number_format($AmoTotal[2],POI).'</th>
		<th>'.	locale_number_format($AmoTotal[3],POI).'</th>
		<th>'.	locale_number_format($AmoTotal[6],POI).'</th>
		<th>'.	locale_number_format($AmoTotal[7],POI).'</th>
		
	</tr>';
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
	//var_dump($ACT);
	//echo '<input type="hidden" name="resultarr" value="' . $Resultarr . '" />';
}
echo '</div>
	   </form>';	   
include('includes/footer.php');	


/**
   * Excel导出，TODO 可继续优化
   *
   * @param array  $datas      导出数据，格式['A1' => 'XXXX公司报表', 'B1' => '序号']
   * @param array  $header   导出文件名称
   * @param array  $TitleData "Title"=>'客户名单',
   * 						  "FileName"=>$FileName,
   * 						  "TitleDate"=>"2020-03-26",
   *                          "Compy"=>"华陆数控公司",
   *                          "Units"=>"元",
   *                           "k"=>3;
   * @param array  $options    操作选项，例如：
   *                           bool   print       设置打印格式
   *                           string freezePane  锁定行数，例如表头为第一行，则锁定表头输入A2
   *                           array  setARGB     设置背景色，例如['A1', 'C1']
   *                           array  setWidth    设置宽度，例如['A' => 30, 'C' => 20]
   *                           bool   setBorder   设置单元格边框
   *                           array  mergeCells  设置合并单元格，例如['A1:J1' => 'A1:J1']
   *                           array  formula     设置公式，例如['F2' => '=IF(D2>0,E42/D2,0)']
   *                           array  format      设置格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        设置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置后则文件保存到服务器，不通过浏览器下载
   */	
  function exportExcelAccount($data,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		$rowCnt=count($data); 
		$k=$titledata['k'];
		// @var Spreadsheet  $spreadsheet 
		
		$sheet = $spreadsheet->getActiveSheet();
		//设置sheet的名字  两种方法
		$sheet->setTitle($titledata['FileName']);
		$spreadsheet->getActiveSheet()->setTitle($titledata['Title']);
			//设置默认文字居左，上下居中 
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_LEFT,
				'vertical'   => Alignment::VERTICAL_CENTER,
			],
		];
		$spreadsheet->getDefaultStyle()->applyFromArray($styleArray);
		//设置Excel Sheet 
		$activeSheet =  $spreadsheet->setActiveSheetIndex(0);

		//打印设置 
		if (isset($options['print']) && $options['print']) {
			//设置打印为A4效果 
			$activeSheet->getPageSetup()->setPaperSize(PageSetup:: PAPERSIZE_A4);
			//设置打印时边距 
			$pValue = 1 / 2.54;
			$activeSheet->getPageMargins()->setTop($pValue / 2);
			$activeSheet->getPageMargins()->setBottom($pValue * 2);
			$activeSheet->getPageMargins()->setLeft($pValue / 2);
			$activeSheet->getPageMargins()->setRight($pValue / 2);
		}
		//设置第一行行高为20pt

		$sheet->getRowDimension('1')->setRowHeight(25);
		$sheet->mergeCells('A1:'.Coordinate::stringFromColumnIndex($columnCnt).'1');
		//将A1至D1单元格设置成粗体
		//$sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex($columnCnt).'1')->getFont()->setBold(true);

		//将A1单元格设置成粗体，黑体，10号字
        $sheet->getStyle('A1')->getFont()->setBold(true)->setName('黑体')->setSize(14);

		$sheet->setCellValue('A1',  (string)$titledata['Title']); 
		$sheet->setCellValue('D2',  (string)$titledata['TitleDate']); 
		$sheet->setCellValue('A3', "公司名称:". (string)$titledata['coyname']); 
		$sheet->setCellValue('J3',  "单位：".(string)$titledata['Units']); 
		//设置默认行高
		$sheet->getDefaultRowDimension()->setRowHeight(20);
		
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER, //水平居中
				'vertical' => Alignment::VERTICAL_CENTER, //垂直居中
			],
		];
		$activeSheet->getStyle('A1')->applyFromArray($styleArray);
		$activeSheet->getStyle('A')->applyFromArray($styleArray);
		//$sheet->getStyle('A'.($k+1).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+$rowCnt))->applyFromArray($styleArray);
	
		$styleArray = [
			'borders' => [
				'outline' => [
					'borderStyle' => Border::BORDER_THICK,
					'color' => ['argb' => 'FFFF0000'],
				],
			],
		];
		$styleArray = [
			'borders' => [
				  'allBorders' => [
					'borderStyle' => Border::BORDER_THIN //细边框
				]
				]
		];
		$activeSheet->getStyle('A'.(int)($k+1).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+$rowCnt+1))->applyFromArray($styleArray);
		  /* 设置宽度 */
		$activeSheet->getColumnDimension('B')->setWidth(15);
	
		
		$activeSheet->getColumnDimension('C')->setWidth(50);
		$activeSheet->getColumnDimension('D')->setAutoSize(true);
		$activeSheet->getColumnDimension('D')->setAutoSize(true);
		$activeSheet->getColumnDimension('E')->setAutoSize(true);
		$activeSheet->getColumnDimension('F')->setAutoSize(true);
		$activeSheet->getColumnDimension('G')->setAutoSize(true);	
		$activeSheet->getColumnDimension('H')->setAutoSize(true);
		//$activeSheet->getColumnDimension('F')->setWidth(15);
		//$activeSheet->getColumnDimension('G')->setWidth(25);
        //foreach ($options['setWidth'] as $swKey => $swItem) {
		//	$activeSheet->getColumnDimension($swKey)->setWidth($swItem);
	    //}  	
	// prnMsg(Coordinate::stringFromColumnIndex($columnCnt));
	$k++;
	for ($_column = 1; $_column <= $columnCnt; $_column++) {
		$cellName = Coordinate::stringFromColumnIndex($_column);
		$cellId   = $cellName . ($k);
	  
		//if ($_row==1){
			//表头
			$sheet->setCellValue($cellName.($k),  (string)$header[$_column-1]); 
		//}
	}
			

	for ($_row = 1; $_row <= $rowCnt; $_row++) {
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($_row+$k);
		  /*
			if ($_row==1){
				//表头
				$sheet->setCellValue($cellName.($_row+$k),  (string)$header[$_column-1]); 
				$k++;
								//$activeSheet->getColumnDimension("B")->setWidth(30);
				//$celldata[$_row][$cellName] = (string)$header[$_column-1];
			  
			}else{*/
				if ($_column==1){
					//  序号列 
					// $celldata[$_row][$cellName] = $_row;
					$sheet->setCellValue($cellName.($_row+$k), $_row-1); 
				}else{
					//$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1][$_column-2]);
					
					if ($_column==2){					
						$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1]['account']);
					}elseif ($_column==3){					
						$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1]['accountname']);
					}elseif ($_column==4){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['qcdebit']);
					}elseif ($_column==5){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['qccredit']);
					}elseif ($_column==6){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['debittotal']);
					}elseif ($_column==7){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['credittotal']);
					}elseif ($_column==8){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['qmdebit']);
					}elseif ($_column==9){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['qmcredit']);
					}elseif ($_column==10){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['debityear']);
					}elseif ($_column==11){					
						$sheet->setCellValue($cellName.($_row+$k), (float)$data[$_row-1]['credityear']);
					}
                }
               // prnMsg($data[$_row-1][$_column-1]);
				
			//}

			if (!empty($data[$_row-1][$cellName-1])) {
				$isNull = false;
			}
		}


	}
     $amototal=json_decode($titledata['AmountTotal']);
	$sheet->setCellValue("A".($rowCnt+1+$k), ''); 	
	$sheet->setCellValue("B".($rowCnt+1+$k),"");				
	$sheet->setCellValue("C".($rowCnt+1+$k),"累计");
	$sheet->setCellValue("D".($rowCnt+1+$k), (string)$amototal[0]);
	$sheet->setCellValue("E".($rowCnt+1+$k), (float)$amototal[1]);
	$sheet->setCellValue("F".($rowCnt+1+$k), (float)$amototal[4]);
	$sheet->setCellValue("G".($rowCnt+1+$k), (float)$amototal[5]);
	$sheet->setCellValue("H".($rowCnt+1+$k), (float)$amototal[2]);
	$sheet->setCellValue("I".($rowCnt+1+$k), (float)$amototal[3]);
	$sheet->setCellValue("J".($rowCnt+1+$k), (float)$amototal[6]);
	$sheet->setCellValue("k".($rowCnt+1+$k), (float)$amototal[7]);
	/*if ($_column==2){
					$celldata[$_row][$cellName] =$data[$_row-1]['account'];
					}elseif ($_column==3){					
						$celldata[$_row][$cellName] =$data[$_row-1]['accountname'];
					}elseif ($_column==4){					
						$celldata[$_row][$cellName] =(float)$data[$_row-1]['qcdebit'];
					}elseif ($_column==5){					
						$celldata[$_row][$cellName] =(float)$data[$_row-1]['qccredit'];
					}elseif ($_column==6){					
						$celldata[$_row][$cellName] =(float)$data[$_row-1]['debittotal'];
					}elseif ($_column==7){					
						$celldata[$_row][$cellName] =$data[$_row-1]['credittotal'];
					}elseif ($_column==8){					
						$celldata[$_row][$cellName] =$data[$_row-1]['qmdebit'];
					}elseif ($_column==9){					
						$celldata[$_row][$cellName] =$data[$_row-1]['qmcredit'];
					}elseif ($_column==10){					
						$celldata[$_row][$cellName] =$data[$_row-1]['debityear'];
					}elseif ($_column==11){					
						$celldata[$_row][$cellName] =$data[$_row-1]['credityear'];
					}  
					} */
	
	//循环赋值
    //var_dump($celldata);

	
	//第一种保存方式
	/*	$writer = new Xlsx($spreadsheet);
	//保存的路径可自行设置
	$file_name = '../'.$file_name . ".xlsx";
	$writer->save($file_name);
	///第二种直接页面上显示下载
	*/
	
	$filename=$titledata['FileName'].".xlsx";
	ob_end_clean();
	
	$ua = $_SERVER ["HTTP_USER_AGENT"];

	//$filename = basename ( $file );
	$encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
    $filename= iconv('UTF-8', $encode, $filename);
	$encoded_filename = rawurlencode ( $filename );
	header('Content-Type: application/vnd.ms-excel');
	if (preg_match ( "/MSIE/", $ua )) {
		header ( 'Content-Disposition: attachment; filename="' .convertEncoding($filename) . '"' );
	} else if (preg_match ( "/Firefox/", $ua )) {
		header ( "Content-Disposition: attachment; filename*=\"utf8''" . $filename . '"' );
	} else {
		header ( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	}

	header('Cache-Control: max-age=0');

	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	//注意	createWriter($spreadsheet, 'Xls') //第二个参数首字母必须大写
	$writer->save('php://output'); 

}	

?>
