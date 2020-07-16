<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:56
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-06-14 15:02:44
 */
/*$ ID BankTransReport.php  ChengJiang $*/
require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
include ('includes/session.php');
require_once 'Classes/PHPExcel.php'; 
$Title = '现金报表';
$ViewTopic= 'Bank Trans Report';
$BookMark ='Bank Trans Report';
include('includes/header.php');
if (isset($_POST['period'])) { 
	$FirstPeriod = min($_POST['period']);
	$LastPeriod = max($_POST['period']);
}else { // Otherwise just highlight the current period
	$FirstPeriod =$_SESSION['janr'];
	$LastPeriod = $_SESSION['period'];
}
if (!isset($_POST['query'])) { 
	$_POST['query']=1;
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
if ($_POST['UnitsTag']==0){
	$tagusers=implode(",",$_SESSION[$_SESSION['UserID']]);
 }else{
	$tagusers=$_POST['UnitsTag'];	
 }
		$SQL = "SELECT 	bankaccountname,
					bankaccounts.accountcode,
					bankaccounts.currcode
			FROM bankaccounts,
				chartmaster,
				bankaccountusers
			WHERE bankaccounts.accountcode=chartmaster.accountcode
				AND bankaccounts.accountcode=bankaccountusers.accountcode
			AND bankaccountusers.userid = '" . $_SESSION['UserID'] ."'";
	$ErrMsg = _('The bank accounts could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank accounts was');
	$AccountsResults = DB_query($SQL,$ErrMsg,$DbgMsg);
	if (!isset($_POST['BeforeDate']) OR !Is_Date($_POST['BeforeDate'])){
		$_POST['BeforeDate'] = Date($_SESSION['DefaultDateFormat']);
	}
	if (!isset($_POST['AfterDate']) OR !Is_Date($_POST['AfterDate'])){
		$_POST['AfterDate'] = Date($_SESSION['DefaultDateFormat'], Mktime(0,0,0,1,1,Date('Y',strtotime($_SESSION['lastdate']))));
	}
		$SQLBeforeDate = FormatDateForSQL($_POST['BeforeDate']);
		$SQLAfterDate = FormatDateForSQL($_POST['AfterDate']);
if(!isset($_POST['ImportExcel'])) {
    echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
    	<input type="hidden" name="query" value="' . $_POST['query'] . '" />';
	echo '<table class="selection">';	

	echo '<tr><th colspan="3">' . _('Selection Criteria') . '</th></tr>';
	echo '<tr>
			<td>' . _('Bank Account') . ':</td>
			<td><select name="BankAccount">';
	if (DB_num_rows($AccountsResults)==0){
		echo '</select></td>
				</tr></table>';
		prnMsg( _('Bank Accounts have not yet been defined. You must first') . ' <a href="' . $RootPath . '/BankAccounts.php">' . _('define the bank accounts') . '</a> ' . _('and general ledger accounts to be affected'),'warn');
		include('includes/footer.php');
		exit;
	} else {
	
		while ($myrow=DB_fetch_array($AccountsResults)){
			if (!isset($_POST['BankAccount'])){
				$_POST['BankAccount']=$myrow['accountcode'];
			}
			if ($_POST['BankAccount']==$myrow['accountcode']){
				echo '<option selected="selected" value="';
			} else {
			 		echo '<option  value="';
			}
					echo  $myrow['accountcode'] . '">' . $myrow['bankaccountname'] .  '</option>';
			}	
		echo '</select>
              </td>
			  </tr>';
	}
	echo '<tr>
		<td>单元分组</td>
		 <td>';
	
		 TagGroup( 7);
    
	echo'</select>
		 </td></tr>';
	echo '<tr>
		 <td>导出格式</td>
		 <td >
			 <input type="radio" name="query" value="0"  '.($_POST['query']==0 ? 'checked':"").' >导出全部          
			 <input type="radio" name="query" value="1"   '.($_POST['query']==1 ? 'checked':"").'  >导出单户
			
		 </td>
		 </tr>';
	echo '<tr>
		<td>' . _('For Period range').':</td>
		<td>
		    <select name="period[]" size="12" multiple="multiple">';
	$sql = "SELECT periodno, lastdate_in_period 
	         FROM periods 
			 where periodno>=".$_SESSION['startperiod']. "  AND periodno<=".$_SESSION['period']. " 
			 ORDER BY periodno DESC";
	$result = DB_query($sql);
	while ($myrow=DB_fetch_array($result,$db)){
		if (isset($FirstPeriod) AND $myrow['periodno'] >= $FirstPeriod AND $myrow['periodno'] <= $LastPeriod) {
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
		} else {
			echo '<option value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
		}
	}
echo '</select></td></tr>';
	
	echo '</table>';
	echo '<br /><div class="centre"><input type="submit" name="Search" value="' . '显示查询' . '" />		
			<input type="submit" name="ImportExcel" value="' . '导出Excel' .'" />
			<input type="submit" name="crtMail" value="'.'发送邮件'.'" />
		</div>';  
} 
			$SQL="SELECT account,
						accountname,
						currcode,
						SUM(sumamount(amount, periodno,0,$FirstPeriod-1)) as qcbalance, 
						SUM(toamount(amount, periodno,$FirstPeriod,$LastPeriod,1,flg)) as debittotal, 
						SUM(toamount(amount, periodno,$FirstPeriod,$LastPeriod,-1,flg)) as credittotal,
						SUM(sumamount(amount, periodno,0,$LastPeriod)) as qmbalance,
						SUM(toamount(amount, periodno,$FirstPeriod,$LastPeriod,1,flg)) as debityear,
						SUM(toamount(amount, periodno,$FirstPeriod,$LastPeriod,-1,flg)) as  credityear   

			FROM gltrans 
			LEFT JOIN chartmaster ON  account=accountcode
			WHERE periodno<=$LastPeriod AND periodno>=0 AND gltrans.tag IN (".$tagusers.") 
			 AND LEFT(account,4) IN ('1001','1002')
			 GROUP BY account,accountname ,currcode";
			$Result=DB_query($SQL);
			
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
				ON gltrans.type=systypes.typeid	WHERE   gltrans.account in(SELECT	bankaccounts.accountcode					
			FROM bankaccounts,	bankaccountusers	WHERE  bankaccounts.accountcode=bankaccountusers.accountcode	AND bankaccountusers.userid = '" . $_SESSION['UserID'] ."')  and  periodno>0";
			$sql.=" AND periodno >='". $FirstPeriod ."' AND  periodno <='". $LastPeriod."'
			AND gltrans.tag IN (".$tagusers.") ";
	       //$sql.=" AND trandate >='". $SQLAfterDate."' AND trandate<='". $SQLBeforeDate."' ";
	
if(isset($_POST['ImportExcel'])) {
	if ($_POST['query']==1){      	   

		$sql1 =$sql."  AND account='".$_POST['BankAccount']."'"; 
	}else{
		$sql1 =$sql;
	} 			
		

		$ListCount=DB_num_rows($Result);
       if ( $ListCount==0){
		   prnMsg('没有数据导出！','info');
		   exit;
	   }
	 //prnMsg($_POST['query'].'-'.$_POST['BankAccount']);
		$options = array("print"=>true);//,"setWidth"=>$setWidth);
		
	
		$FileName ="现金报表_". date('Y-m-d', time()).rand(1000, 9999);
		$TitleData=array("Title"=>'现金报表',"FileName"=>$FileName,"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"ListCount"=>$ListCount,"AmountTotal"=>json_encode($AmoTotal));	
	
			  
		exportExcel($Result,$sql1,$_POST['BankAccount'],$_POST['query'],$TitleData,$options);
	   exit;
		
}
	
	$AmoTotal=[];

	$sql.="AND account='".$_POST['BankAccount']."' 
	       ORDER BY gltrans.account, gltrans.periodno, gltrans.typeno";  
	$result = DB_query($sql);	
	$ListCount=DB_num_rows($result);	
if ($ListCount>0 AND (isset($_POST['Search'])))	{
	echo '<table cellpadding="2" class="selection">';
	$TableHeader = '<tr>
				<th colspan="7" height="2">
					<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
					</div> 
				</th>
			</tr>
			<tr> 
			
				<th  width="220" >' .'&nbsp;&nbsp;&nbsp;会计科目/名称&nbsp;&nbsp;&nbsp;'. '</th>
				<th>期初借方</th>  
				<th>期初贷方</th>
				<th>借方合计</th>
				<th>贷方合计</th>
				<th>期末借方</th>
				<th>期末贷方</th>
			
			</tr>';
	echo $TableHeader;
			$RowIndex = 0;
	while ($row=DB_fetch_array($Result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		$balance[$row['account']]=array('qcbalance'=>$row['qcbalance'],
		'debittotal'=>$row['debittotal'], 'credittotal'=>$row['credittotal'],
		'qmbalance'=>$row['qmbalance'] ,"flag"=>1 );
		$qcdebit=0;
		$qccredit=0;
			if (round($row['qcbalance'],POI)>0){
				$qcdebit=$row['qcbalance'];
			}else {
				$qccredit=-$row['qcbalance'];
			}
			$qmdebit=0;
			$qmcredit=0;
			if (round($row['qmbalance'],POI)>0){
				$qmdebit=$row['qmbalance'];
			}else {
				$qmcredit=-$row['qmbalance'];
			}
		printf('<td>%s</td>
				
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
			
				</tr>',
				'['.$row['account'].']'.substr($row['accountname'],strpos($row['accountname'],'-')+1), 			
				locale_number_format($qcdebit,POI),
				locale_number_format($qccredit,POI),
				locale_number_format($row['debittotal'],POI),
				locale_number_format($row['credittotal'],POI),
				locale_number_format($qmdebit,POI),
				locale_number_format($qmcredit,POI)
			);
		$RowIndex ++;
		$AmoTotal[0]+=round((float)$qcdebit,POI);			
		$AmoTotal[1]+=round((float)$qccredit,POI);	

		$AmoTotal[2]+=round((float)$qmdebit,POI);			
		$AmoTotal[3]+=round((float)$qmcredit,POI);

		$AmoTotal[4]+=round((float)$row['debittotal'],POI);
		$AmoTotal[5]+=round((float)$row['credittotal'],POI);
	}
	echo'<tr> 

	<th  width="220" >' .'累&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;计'. '</th>
	<th>'.	locale_number_format($AmoTotal[0],POI).'</th>  
	<th>'.	locale_number_format($AmoTotal[1],POI).'</th>

	<th>'.	locale_number_format($AmoTotal[4],POI).'</th>
	<th>'.	locale_number_format($AmoTotal[5],POI).'</th>
	<th>'.	locale_number_format($AmoTotal[2],POI).'</th>
	<th>'.	locale_number_format($AmoTotal[3],POI).'</th>
	</tr>';
	if(isset($_POST['BankAccount'])&& $_POST['BankAccount']==0){	
		prnMsg("显示查询不能选择全部！","info");
		exit;
	}

}
// end if producing a CSV 
if ($ListCount>0 AND (isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])	OR isset($_POST['Previous']))){
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
	if (DB_num_rows($result) <> 0) {
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
		echo '<br /><div class="centre">';
	if ( $ListPageMax > 1) {
		echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': </p>';
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
		echo '<br />';
	}
	echo 	'</div>';
	echo	'	<br />
		<table cellpadding="2">';
	echo '<tr>
			 
			<th>' .'序号' . '</th>	
			<th>' . _('Date') . '</th>
			<th>凭证字号</th>
			<th>' . _('Narrative') . '</th>
			<th>' . _('Debits').' '.$_SESSION['CompanyRecord'][1]['currencydefault'] . '</th>
			<th>' . _('Credits').' '.$_SESSION['CompanyRecord'][1]['currencydefault'] . '</th>	
			<th>方向</th>		
			<th>' .'账户余额'.' </th>				
		</tr>';
			$k = 0; //row counter to determine background colour
			$RowIndex = 0;
			$LastJournal = 0;
			$LastType = -1;
			$r=0;
			$x=1;
			$amountJ=0;
			$amountD=0;
			$qmbalance=$balance[$_POST['BankAccount']]['qcbalance'];
		while ($myrow = DB_fetch_array($result)  AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
			if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			} else {
				echo '<tr class="OddTableRows">';
				$r=1;
			}
			$qmbalance+=$myrow['Debits']-$myrow['Credits'];
			if ($myrow['account']!=$LastJournal OR ($myrow['account']=$LastJournal AND $myrow['accountname']!=$LastType) ) {
			  	if ($LastJournal!=0 AND $x > 1) {
			  		//$ye=$qcye+$amountJ-$amountD;
					  echo '<td colspan="3"></td>
					        <td >'.'合计'.'</td>
							<td >'.$amountJ.'</td>
							<td >'.$amountD.'</td>
							<td></td>
							<td></td>';
			 		if ($r==1){
						echo '<tr class="EvenTableRows">';
						$r=0;
					} else {
						echo '<tr class="OddTableRows">';
							$r=1;
					}
			  	}
			  	//添加计算期���余额
				//$qcye=GetAmount($selectprd,$periodrange,$mindate,$myrow['account']);
				$fx="贷";
				if (round($balance[$myrow['account']]['qcbalance'],POI)>0){
					$fx="借";
				}
				echo ' <td colspan="3"></td>
					  <td >'.'期初余额'.'</td>
					  <td ></td>
					  <td ></td>
					  <td>'.$fx.'</td>
					  <td class="number">' . isZero(locale_number_format($balance[$myrow['account']]['qcbalance'],POI)) . '</td>';
				$amountJ=0;
				$amountD=0;
				if ($r==1){
					echo '<tr class="EvenTableRows">';
					$r=0;
				} else {
					echo '<tr class="OddTableRows">';
					$r=1;
				}
			    $x=1;
			
			}
				echo '<td >'. $x .'</td>
					  <td>' .  $myrow['trandate'] . '</td>
					  <td title="'. $myrow['transno'].'">'.$_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno']. '</td>
				      <td>'.$myrow['narrative']. '</td>
					  <td class="number">' . isZero(locale_number_format($myrow['Debits'],POI)) . '</td>
					  <td class="number">' . isZero(locale_number_format($myrow['Credits'],POI) ). '</td>';
				
				if ($x%5==0){
					if ($qmbalance>0){
						echo  '<td >借</td>';
					}else{
						echo  '<td >贷</td>';
					}
					 echo'<td class="number">'.isZero(locale_number_format(abs($qmbalance),POI)).'</td>';
				}else{
					if ($ListCount-$x<5){
						if ($qmbalance>0){
							echo  '<td >借</td>';
						}else{
							echo  '<td >贷</td>';
						}
						 echo'<td class="number">'.isZero(locale_number_format(abs($qmbalance),POI)).'</td>';
					}else{
						echo  ' <td></td>
							<td></td>';
					}
				}
				echo'</tr>';	 
				$amountJ+=$myrow['Debits'];
				$amountD+=$myrow['Credits'];
				$x++;
				
			if ($myrow['account']!=$LastJournal OR $myrow['accountname']!=$LastType) {
			
	     		$LastType = $myrow['accountname'];
				$LastJournal = $myrow['account'];
			} 
			$RowIndex = $RowIndex + 1;
		}
	
		echo'<tr>';	 
			echo '<td colspan="3" ></td>';
			echo'<td>'.'合计'.'</td>
						<td class="number">' . isZero(locale_number_format($amountJ,POI)) . '</td>
						<td class="number">' . isZero(locale_number_format($amountD,POI) ). '</td>
						<td></td>
						<td class="number"></td></tr>';
			echo '</table>';
	if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': </p>';
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
		echo '<br />';
	}

}elseif (isset($_POST['crtMail'])) {	
	 prnMsg('该功能模块暂时未开通！','info');
	 //require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
	 export();
}
	
echo '</form>';  
include('includes/footer.php');
function exportExcel($result0,$_SQL,$BankAccount,$Query,$TitleData,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';  

	  $spreadsheet = new Spreadsheet();
	  // Create a new worksheet "
	  $sheet = $spreadsheet->getActiveSheet();
	  //设置sheet的名字  两种方法
	  $sheet->setTitle('银行存款-现金');
	  $spreadsheet->getActiveSheet()->setTitle('银行存款-现金汇总表');
	  	//设置默认文字居左，上下居中 
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_RIGHT,
				'vertical'   => Alignment::VERTICAL_CENTER,
			],
		];
		$spreadsheet->getDefaultStyle()->applyFromArray($styleArray);
	  $head=['科目编码/名称', '期初借方','期初贷方',  '借发生额', '贷发生额', '期末借款','期末贷方'];	
	  //数据中对应的字段，用于读取相应数据：
	
	  $columnCnt = count($head);  //计算表头数量
	for ($i = 65; $i < $columnCnt + 65; $i++) {     //数字转字母从65开始，循环设置表头：
		$sheet->setCellValue(strtoupper(chr($i)) . '1', $head[$i - 65]);
		if ($i==65)
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(30); //固��列宽
		else {
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
		}
	}
	/*--------------开始从数据库提取信息插入Excel表中------------------*/
	DB_data_seek($result0,0)     ;
	$k=2;
	while ($row = DB_fetch_array($result0) ){
		$qcdebit=0;
		$qccredit=0;
		 if (round($row['qcbalance'],POI)>0){
			 $qcdebit=$row['qcbalance'];
		 }else {
			 $qccredit=-$row['qcbalance'];
		 }
		 $qmdebit=0;
		 $qmcredit=0;
		  if (round($row['qmbalance'],POI)>0){
			  $qmdebit=$row['qmbalance'];
		  }else {
			  $qmcredit=-$row['qmbalance'];
		  }
		for ($i = 65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始：
			
			if ($i==65){
				$sheet->setCellValue(strtoupper(chr($i)) . ($k), '['.$row['account'].']'.substr($row['accountname'],strpos($row['accountname'],'-')+1));
				$sheet->getCell(strtoupper(chr($i)) . ($k))->getHyperlink()->setUrl('"'.$row['account'].'银行现金'.'"');
			
			}elseif($i==66){
				$sheet->setCellValue(strtoupper(chr($i)) . ($k), 	locale_number_format($qcdebit,POI));
			}elseif($i==67){
				$sheet->setCellValue(strtoupper(chr($i)) . ($k),locale_number_format($qccredit,POI));
			}elseif($i==68){
				$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($row['debittotal'],POI));
			}elseif($i==69){
				$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($row['credittotal'],POI));
			}elseif($i==70){
				$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($qmdebit,POI));
			}elseif($i==71){
				$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($qmcredit,POI));
			}
	
		}
		$AmoTotal[0]+=round((float)$qcdebit,POI);			
		$AmoTotal[1]+=round((float)$qccredit,POI);	

		$AmoTotal[2]+=round((float)$qmdebit,POI);			
		$AmoTotal[3]+=round((float)$qmcredit,POI);

		$AmoTotal[4]+=round((float)$row['debittotal'],POI);
		$AmoTotal[5]+=round((float)$row['credittotal'],POI);
		$k++;
	}
	for ($i = 65; $i < $columnCnt+65 ; $i++) {     
	
		if ($i==65){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), '累计');
		}elseif($i==66){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), 	locale_number_format($AmoTotal[0],POI));
		}elseif($i==67){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k),locale_number_format($AmoTotal[1],POI));
		}elseif($i==68){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($AmoTotal[4],POI));
		}elseif($i==69){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($AmoTotal[5],POI));
		}elseif($i==70){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($AmoTotal[2],POI));
		}elseif($i==71){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($AmoTotal[3],POI));
		}

	}
	$styleArray = [
		'borders' => [
			  'allBorders' => [
				'borderStyle' => Border::BORDER_THIN //细边框
			]
			]
	];
	$sheet->getStyle('A1:'.Coordinate::stringFromColumnIndex($columnCnt).($k))->applyFromArray($styleArray);
   //添加银行现金明细表
   DB_data_seek($result0,0);
 
 
	
   //$ListCount=DB_num_rows($result);	 	
   while ($Row = DB_fetch_array($result0) ){//遍历账户
	      $bk=0;
	     if ($Query==1 && $Row['account']==$BankAccount){
              $bk=1;   
		 }elseif($Query==0){
			 $bk =1;
		 }
	    if (round((abs($Row['debittotal'])+abs($Row['credittotal'])),POI)!=0  && $bk==1){			  //无发生额的账户跳过
			//$sheetname= iconv("UTF-8","gbk//TRANSLIT",substr($Row['accountname'],strpos($Row['accountname'],'-')+1));
			$sheetname= substr($Row['accountname'],strpos($Row['accountname'],'-')+1);
			//echo $sheetname;
			$WorkSheet[$Row['account']] = new Worksheet($spreadsheet,$Row['account']."银行现金");//与下面的配合使用
		
			$spreadsheet->addSheet($WorkSheet[$Row['account']], 0);//将“My Data”工作表作为电子表格对象中的第一个工作表���加
			$Header=array( '序号', '日期', '凭证号','摘要',  '借发生额', '贷发生额','方向', '期末余额');	
			$columnCnt=count($Header);
			//设置第一行行高为20pt

			$spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(25);
			//$spreadsheet->getActiveSheet()
			$WorkSheet[$Row['account']]->mergeCells('A1:'.strtoupper(chr($columnCnt+64)).'1');
		
			//将A1单元格设置成粗体，黑体，10号字
			$WorkSheet[$Row['account']]->getStyle('A1')->getFont()->setBold(true)->setName('黑体')->setSize(14);

			$WorkSheet[$Row['account']]->setCellValue('A1',  '['.$Row['account'].']'.$Row['accountname']); 
			$styleArray = [
				'alignment' => [
					'horizontal' => Alignment::HORIZONTAL_CENTER, //水平居中
					'vertical' => Alignment::VERTICAL_CENTER, //垂直居中
				],
			];
			$spreadsheet->getActiveSheet()->getStyle('A1')->applyFromArray($styleArray);
			
			for ($i =65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始，循环设置表头：
				
				$spreadsheet->getActiveSheet()->setCellValue(strtoupper(chr($i)) . '2', $Header[$i-65]);

				if ($i==65){
					$spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(10); //固定列宽
				}elseif($i==66){
					$spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(15); //固定列宽
				}elseif($i==67){
					$spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(10); //固定列宽
				}elseif($i==68){
					$spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(40); //固定列宽
				}else {
					$spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
				}
			}
			  $fx="贷";
			  $qmbalance[$Row['account']]=round($Row['qcbalance'],POI);
			if (round($Row['qcbalance'],POI)>0){
				$fx="借";
				
			}
			$k=3;
			for ($i =65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始，循环设置表头：
				if($i==68){
					$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k),  '起初余额');
				
				}elseif($i==71){
					$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), $fx);
				}elseif($i==72){
					$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format(abs($Row['qcbalance']),POI));
				}else{
					$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . '3','');
				}
				if ($i==65)
					$spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(30); //固定列宽
				else {
					$spreadsheet->getActiveSheet()->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
				}
			}
				//-------------开始从数据库提取信息插入Excel表中------------------
				$sql=$_SQL." AND gltrans.account='".$Row['account']."' ORDER BY gltrans.account, gltrans.periodno,gltrans.typeno"; 
				$gltransresult = DB_query($sql);
			$k=4;
			while ($row = DB_fetch_array($gltransresult) ){
				$qmbalance[$Row['account']]+=round($row['Debits'],POI)-round($row['Credits'],POI);
				for ($i = 65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始：
				
					if ($i==65){
						
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), $k-3	);
					}elseif($i==66){
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k),  $row['trandate']);
					}elseif($i==67){
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), '['. $row['transno'].']'. $_SESSION['tagref'][$row['tag']][2].$row['typeno']);
					}elseif($i==68){
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k),  $row['narrative']);
					}elseif($i==69){
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($row['Debits'],POI));
					}elseif($i==70){
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($row['Credits'],POI));
					}elseif($i==71){
						$fx="贷";
						if ($qmbalance[$Row['account']]>0){
						  $fx="借";	
						}
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), $fx);
					}elseif($i==72){
						
						$WorkSheet[$Row['account']]->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format(abs($qmbalance[$Row['account']]),POI));
					}
				
			
				}
				$k++;
			}//end  while
			//$WorkSheet[$Row['account']]
			$styleArray = [
				'borders' => [
					  'allBorders' => [
						'borderStyle' => Border::BORDER_THIN //细边框
					]
					]
			];
			$spreadsheet->getActiveSheet()->getStyle('A1:'.Coordinate::stringFromColumnIndex($columnCnt).($k))->applyFromArray($styleArray);
		}
	
	}
	$filename=$TitleData['FileName'].".xlsx";
	ob_end_clean();
	
	$ua = $_SERVER ["HTTP_USER_AGENT"];

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

	exit;

}

?>