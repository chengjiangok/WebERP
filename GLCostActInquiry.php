<?php
/* $Id: GLAccountInquiry.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-01-09 06:50:09 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-01-09 21:10:32
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
$Title = _('General Ledger Account Inquiry');
$ViewTopic = 'GeneralLedger';
$BookMark = 'GLAccountInquiry';
if (isset($_GET['show'])){
	$urlstr=explode('^',urldecode($_GET['acp']));
	$_SESSION['showacp']=array($_GET['show'],$_GET['acp']);
	$SelectedAccount=$urlstr[0];
	if ($_GET['show']=='GLTB'){
		//从科目汇总表跳转，锁定期间
		$FirstPeriodSelected =$urlstr[1];
		$LastPeriodSelected = $urlstr[2];
    }else{

		//从账簿查询跳转SGLA 0account 1currency
		$SelectCurr=$urlstr[1];
		if (isset($_POST['period'])) { //If it was called from itself (in other words an inquiry was run and we wish to leave the periods selected unchanged
			$FirstPeriodSelected = min($_POST['period']);
			$LastPeriodSelected = max($_POST['period']);
		}else { // Otherwise just highlight the current period
			$FirstPeriodSelected =$_SESSION['janr'];
			$LastPeriodSelected = $_SESSION['period'];
		}
	}
}
if (!isset($_POST['TagsGroup']) ){
	$_POST['TagsGroup']=1;		 
}
    //读取横打印的子目名
	$sql = "SELECT accountcode,
	               accountname,
				   currcode,
				   tag,
				   used 
	         FROM chartmaster 
			 WHERE accountcode  LIKE '" . $SelectedAccount . "_%'";
	$result = DB_query($sql);

	while($row=DB_fetch_array($result)){
		 if (!isset($SelectedAccountName))
		 $SelectedAccountName=explode("-",$row['accountname'])[0];

		 $header[$row['accountcode']][0]=explode("-",$row['accountname'])[1];
		 $header[$row['accountcode']][6]=$row['used'];
	}
	
	//$SelectCurr=$row['currcode'];
	//$SelectTag=$row['tag'];

	//读取子目期初余额
	$sql="SELECT account,
	             SUM(amount) bfwd 
	       FROM gltrans 
		   where account  LIKE '" . $SelectedAccount . "_%'
		   AND periodno< '" . $FirstPeriodSelected . "'
		   AND gltrans.tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].")
		   GROUP BY account";
		$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
		$Result = DB_query($sql,$ErrMsg);
		$RunningTotal=0;
		while($row=DB_fetch_array($Result)){
			if (isset($header[$row['account']])){
				$header[$row['account']][6]=0;
			}
			$header[$row['account']][1]=$row['bfwd'];
			$RunningTotal+=$row['bfwd'];
	   }
	  // $RunningTotal=array_sum(array_column($header)
		//$Row = DB_fetch_row($Result);
	//	$RunningTotal 	=round($Row[0],2);
if ($_SESSION['Currency']==1 &&$SelectCurr!= CURR ){
	$sql="SELECT account ,
	             SUM(examount) qcye ,
				 SUM(amount)  bbqcye 
		    FROM currtrans 
			WHERE account='" . $SelectedAccount . "' 
			AND period< '" . $FirstPeriodSelected . "'
			GROUP BY account";
	$ErrMsg = _('The chart details for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved');
	$Result = DB_query($sql,$ErrMsg);
	$Row = DB_fetch_row($Result);
	$exRunningTotal 	= $Row[0];
	$RunningTotal=$Row[1];
	$SQL= "SELECT counterindex,
			type,
			typename,
			gltrans.transno,
			gltrans.typeno,
			gltrans.trandate,
			gltrans.account,
			narrative,
			gltrans.amount,
			toamount(gltrans.amount,-1,0,0,1,gltrans.flg) debit,
			toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) credit,
			periodno,
			gltrans.tag	,
			currtrans.examount,
			toamount(currtrans.examount,-1,0,0,1,currtrans.flg) exdebit,
			toamount(currtrans.examount,-1,0,0,-1,currtrans.flg) excredit	
		FROM gltrans LEFT JOIN systypes ON systypes.typeid=abs(gltrans.type)	
		LEFT JOIN currtrans ON CONCAT(currtrans.period,currtrans.transno)=CONCAT(gltrans.periodno,gltrans.transno)		
		WHERE gltrans.account  LIKE '" . $SelectedAccount . "%' AND currtrans.account  LIKE '" . $SelectedAccount . "%' 
		AND periodno>='" . $FirstPeriodSelected . "'
		AND periodno<='" . $LastPeriodSelected . "'
		AND gltrans.tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].") ";
}else{
    $SQL= "SELECT counterindex,
		type,
		typename,
		transno,
		gltrans.account,
		gltrans.typeno,
		trandate,
		narrative,
		amount,
		toamount(amount,-1,0,0,1,flg) debit,
		toamount(amount,-1,0,0,-1,flg) credit,
		periodno,
		gltrans.tag			
	FROM gltrans LEFT JOIN systypes
	ON systypes.typeid=abs(gltrans.type)			
	WHERE gltrans.account LIKE '" . $SelectedAccount . "%'
	AND periodno>='" . $FirstPeriodSelected . "'
	AND periodno<='" . $LastPeriodSelected . "' 
	AND gltrans.tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].")";
}

	$SQL .= " ORDER BY periodno,gltrans.transno,gltrans.typeno,gltrans.tag, gltrans.trandate";

	$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because') ;
	$TransResult = DB_query($SQL,$ErrMsg);
	/*Is the account a balance sheet or a profit and loss account */
	$result = DB_query("SELECT pandl
	FROM accountgroups
	LEFT JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
	WHERE chartmaster.accountcode='" . $SelectedAccount ."'");
	$PandLRow = DB_fetch_row($result);
	if ($PandLRow[0]==1){
		$PandLAccount = True;
	}else{
		$PandLAccount = False; /*its a balance sheet account */
	}
//prnMsg(substr($SelectedAccount,0,strlen($_SESSION['Act'])));
if ($_SESSION['Act']!=substr($SelectedAccount,0,strlen($_SESSION['Act']))){
	$_SESSION['Act']=substr($SelectedAccount,0,4);
}
while ($row=DB_fetch_array($TransResult)) {
	if (isset($header[$row['account']]) && $header[$row['account']][6]==-2){
		$header[$row['account']][6]=0;
	}

}
$colspan=0;
foreach($header as $key=>$val){
	if ($val[6]!=-2)
	$colspan++;
}
$colspan+=7;
if (isset($_POST['CSV'])) {
	    
	
	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	
	/*
	,"freezePane"=>"A2","setARGB"=>"['A1', 'C1']","setWidth"=>"['A' => 30, 'C' => 20]"
						   ,"setBorder"=>0,"mergeCells"=>"['A1:J1' => 'A1:J1']","formula"=>"['F2' => '=IF(D2>0,E42/D2,0)']"
						   ,"format"=>"['A' => 'General']","alignCenter"=>"['A1', 'A2']","bold"=>"['A1', 'A2']","savePath"=>"C:\Wnmp\html\GJWERP\companies\hualu_erp" );
	*/
	
	$FileName =$SelectedAccountName."账簿_". date('Y-m-d', time()).rand(1000, 9999);
	$TitleData=array("Title"=>$SelectedAccountName.'会计账簿',"FileName"=>$FileName,"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal),"Col"=>$colspan,"exRunningTotal"=>$exRunningTotal,"RunningTotal"=>$RunningTotal);	

	 $HD=array('日期','凭证号','摘要','借方金额','贷方金额','借贷','余额');
	//	  '序号', '科目编码', '科目名', '期初借余额', '期初贷余额', '本期借发生额', '本期贷发生额', '期末借余额', '期末贷余额' ,'本年借累计', '本年贷累计');		  
	DB_data_seek($TransResult,0);
	//foreach  ($HD  as $val){	prnMsg($val);	}
	exportExcelGLCost($TransResult,$HD,$header,$TitleData,$options);
	exit;  
}
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="" />' . ' ' . _('General Ledger Account Inquiry') . '</p>';
	echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple periods') . '</div><br />';
	if (isset($_SESSION['showacp'])){
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?show='.$_SESSION['showacp'][0].'&amp;acp='.$_SESSION['showacp'][1].'">';
	}else{
		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	}
	$sql="SELECT accountname, accountcode  FROM chartmaster WHERE  accountcode like  '".$SelectedAccount."'";

	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="show" value="' . $_GET['show'] . '" />
		<input type="hidden" name="acp" value="' . $_GET['acp'] . '" />';
	//echo '<input type="hidden" name="Act" value="' . $_GET['Act'] . '" />';
	/*Dates in SQL format for the last day of last month*/
	//$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));
	/*Show a form to allow input of criteria for TB to show */
	echo '<table class="selection">
			<tr>
				<td>' . _('Account').':</td>
				<td><select name="Account">';

		//DB_data_seek($result,0);
		$result = DB_query($sql);
	while ($myrow=DB_fetch_array($result,$db)){
		if($myrow['accountcode'] == $SelectedAccount){
			echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . ' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		} else {
			echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . $myrow['currcode'] .' ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		}
	}
	echo '</select></td>
		</tr>';
	echo '<tr>
			<td>单元分组</td>
			<td>';
			// SelectUnitsTag();
			echo'<select name="TagsGroup" id="TagsGroup" size="1" >';
			
			foreach($_SESSION['tagsgroup'] as $key=>$val){
					if(isset($_POST['TagsGroup']) AND $key==$_POST['TagsGroup']){
						echo '<option selected="selected" value="';			
					}else{
						echo '<option value="';
					}
					echo  $key. '">' .$val[2] . '</option>';
			}
			echo'</select>';	
	
	echo'</td></tr>';
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
			if (isset($FirstPeriodSelected) AND $myrow['periodno'] >= $FirstPeriodSelected AND $myrow['periodno'] <= $LastPeriodSelected) {
				echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
			} else {
				echo '<option value="' . $myrow['periodno'] . '">' . _(MonthAndYearFromSQLDate($myrow['lastdate_in_period'])) . '</option>';
			}
		}
	echo '</select></td></tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Show" value="'._('Show Account Transactions').'" />
			<input type="submit" name="CSV" value="导出Excel" /><br/>';
			if (isset($_SESSION['Act'])){
				$actstr='?Act='.$_SESSION['Act'];
			}elseif($_POST['show']=='SGLA'){
				$actstr="";
			}
			echo'<a href="' . $RootPath . '/SelectGLAccount.php'.$actstr.'">返回账簿查询</a>';
		echo'</div>
		</div>
		</form>';

	
//<input type="submit" name="submitreturn" value="' ._('Return').'" />
/* End of the Form  rest of script is what happens if the show button is hit*/
if (isset($_POST['Show']) OR isset($_POST['CSV'])|| ($_GET['show']=='SGLL')){
	$BankAccountInfo = isset($BankAccount)?'<th>' . _('Org Currency') . '</th>
						<th>' . _('Amount in Org Currency') . '</th>	
						<th>' . _('Bank Ref') .'</th>':'';
	echo '<br />
		<table class="selection">
		<thead>
			<tr>
				<th colspan="'.$colspan.'"><b>', _('Transactions for account'), ' ', $SelectedAccount, ' - ', $SelectedAccountName, '</b></th>
			</tr>
			<tr>
				<th class="centre">', _('Date'), '</th>
				<th class="text">','凭证号', '</th>
				<th class="text"  style="width:150px">', _('Narrative'), '</th>';
	if ($_SESSION['Currency']==1 &&$SelectCurr!= CURR ){					
		echo'	<th class="number">外币</th>
	         	<th class="number">', _('Debit'), '</th>
				<th class="number">', _('Credit'), '</th>		
			  	<th class="text">', '方向', '</th>
				<th class="number">外币余额</th>';
	   }else{
		echo'  	<th class="number">', _('Debit'), '</th>
				<th class="number">', _('Credit'), '</th>		
			  	<th class="text">', '方向', '</th>';
	   }
		echo' 	<th class="number">', _('Balance'), '</th>	';
		foreach($header as $key=>$val){
			if ($val[6]!=-2)
			echo' 	<th class="number">',$val[0].'<br/>',$key ,'</th>	';
		}

		echo'</tr>
		</thead><tbody>';
		if ($_SESSION['Currency']==1 &&$SelectCurr!= CURR ){					
  			echo '<tr>
						<td colspan="2"></b></td>
						<td colspan="2"><b>', _('Brought Forward Balance'), '</b></td>
						<td colspan="2"></b></td>';
			if($RunningTotal < 0 ) {// It is a credit balance b/fwd
				echo '  <td >&nbsp;贷方</td>
						<td class="number"><b>', locale_number_format(-$exRunningTotal,POI), '</b></td>
						<td class="number"><b>', locale_number_format(-$RunningTotal,POI), '</b></td>
					</tr>';
			} else {// It is a debit balance b/fwd
				echo '
				<td >&nbsp;借方</td>
				<td class="number"><b>', locale_number_format($exRunningTotal,POI), '</b></td>
				<td class="number"><b>', locale_number_format($RunningTotal,POI), '</b></td>
				</tr>';
			}
		}else{//本币
			echo '<tr>
			<td colspan="2"></b></td>
			<td colspan="1"><b>', _('Brought Forward Balance'), '</b></td>
			<td colspan="2"></b></td>';
			if($RunningTotal < 0 ) {// It is a credit balance b/fwd
				echo '  <td >&nbsp;贷</td>					
						<td class="number"><b>', locale_number_format(-$RunningTotal,POI), '</b></td>
					';
			} else {// It is a debit balance b/fwd
				echo '
				<td >&nbsp;借</td>				
				<td class="number"><b>', locale_number_format($RunningTotal,POI), '</b></td>
				';
			}
			foreach($header as $key=>$val){
				if ($val[6]!=-2)
				echo' 	<td class="number">'. locale_number_format($val[1],POI).'</td>	';
			}
			echo '</tr>';
		}

	$PeriodTotal = 0;
	$PeriodNo = -9999;
	$ShowIntegrityReport = False;
	$DeSum = 0;
	$CrSum = 0;
	$j = 1;
	$k=0; //row colour counter
	$IntegrityReport='';

		DB_data_seek($TransResult,0);
		while ($myrow=DB_fetch_array($TransResult)) {
			if ($myrow['periodno']!=$PeriodNo){
				if ($PeriodNo!=-9999){ //ie its not the first time around
				echo '<tr>
					<th colspan="2"></th>
							<th colspan="1"><b>本月合计 </b></th>
							<th class="number"><b>', locale_number_format($DebitSum,POI), '</b></th>
							<th class="number"><b>', locale_number_format($CreditSum,POI), '</b></th>
							<th colspan="2">&nbsp;</th>';
							foreach($header as $key=>$val){
								if ($val[6]!=-2)
								echo' 	<th class="number">'. locale_number_format($val[2],POI).'</th>	';
							}
						echo'</tr>';
					//$IntegrityReport = '<br />' . _('Period') . ': ' . $PeriodNo  . _('Account movement per transaction') . ': '  . locale_number_format($PeriodTotal,POI) . ' ' . _('Movement per ChartDetails record') . ': ' . locale_number_format($ChartDetailRow['actual'],POI) . ' ' . _('Period difference') . ': ' . locale_number_format($PeriodTotal -$ChartDetailRow['actual'],3);
				}
				foreach($header as $key=>$val){
					if ($val[6]!=-2){
						$header[$key][2]=0;
						$header[$key][3]=0;
						if ($DebitSum!=0){
							$header[$key][2]=0;
						}else{
							$header[$key][3]=0;
						}
					}
				}
				$PeriodNo = $myrow['periodno'];
				$DeSum += $DebitSum ;
				$CrSum +=$CreditSum;
				$PeriodTotal = 0;
				$DebitSum = 0;
				$CreditSum = 0;
				$exDebitSum = 0;
				$exCreditSum = 0;
			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
				$exRunningTotal += $myrow['examount'];
				$exPeriodTotal += $myrow['examount'];
				$exAmount = locale_number_format($myrow['examount'],POI);
				$exDebitSum +=$myrow['exdebit'];		
				$exCreditSum +=  $myrow['excredit'] ;
				$RunningTotal += $myrow['amount'];
				$PeriodTotal += $myrow['amount'];
				$DebitAmount = locale_number_format($myrow['debit'],POI);
				$DebitSum +=$myrow['debit'];
				$CreditAmount = locale_number_format($myrow['credit'],POI);
				$CreditSum +=  $myrow['credit'] ;
		
			$URL_to_TransDetail = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['periodno'].'^'.$myrow['transno'];
			
				echo'<td class="centre">'.ConvertSQLDate($myrow['trandate']).'</td>
					<td class="text" title="'.$myrow['transno'].'"><a href="'. $URL_to_TransDetail.'" target="_blank" >'.$_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno'] .'</a></td>
					<td class="text">'. $myrow['narrative'].'</td>			
					<td class="number">'.$DebitAmount .'</td>
					<td class="number">'.$CreditAmount .'</td>
					<td class="text">'.( ($RunningTotal >= 0)?'借':'贷').'</td>				
					<td class="number">'.locale_number_format(($RunningTotal >= 0)? $RunningTotal:-$RunningTotal,POI) .'</td>	';
					$header[$key][3]=0;
					$header[$key][2]=0;		
					foreach($header as $key=>$val){
						if ($val[6]!=-2){
							if($key==$myrow['account']){
								
								if (empty($myrow['debit'])){
									echo' 	<td class="number">'.locale_number_format(-$myrow['credit'],POI).'</td>	';
									$header[$key][3]+=$myrow['credit'];
									$header[$key][5]+=$myrow['credit'];
								}else{
									echo' 	<td class="number">'.locale_number_format($myrow['debit'],POI).'</td>	';
									$header[$key][2]+=$myrow['debit'];
									$header[$key][4]+=$myrow['debit'];
								}
							}else{
							echo' 	<td class="number"></td>	';
							}
						}
					}
				echo'	</tr>';
		}           
				echo '<tr>
						<th colspan="2"></th>
						<th colspan="1"><b>本月合计 </b></th>
						<th class="number"><b>', locale_number_format($DebitSum,POI), '</b></th>
						<th class="number"><b>', locale_number_format($CreditSum,POI), '</b></th>
						<th colspan="2">&nbsp;</th>';
						foreach($header as $key=>$val){
							if ($val[6]!=-2)
							echo' 	<th class="number">'. locale_number_format($val[2],POI).'</th>	';
						}
					echo'</tr>';
				echo '<tr>
					<th colspan="2"></th>
					<th colspan="1"><b>查询期总计 </b></th>
					<th class="number"><b>', locale_number_format(($DeSum+$DebitSum),POI), '</b></th>
					<th class="number"><b>', locale_number_format(($CrSum+$CreditSum),POI), '</b></th>
					<th colspan="2">&nbsp;</th>';
					foreach($header as $key=>$val){
						if ($val[6]!=-2)
						echo' 	<th class="number">'. locale_number_format($val[4],POI).'</th>	';
					}
				echo'</tr>';
	}
	echo '</tbody></table>';

include('includes/footer.php');

/**
   * Excel导出，TODO 可继续优化
   *
   * @param array  $datas      导出数据，格式['A1' => 'XXXX公司报表', 'B1' => '序号']
   * @param array  $header   导出文件名称
   * @param array  $TitleData "Title"=>'   户名单',
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
   *                           bool   setBorder   设置单元格  框
   *                           array  mergeCells  设置合并单元格，例如['A1:J1' => 'A1:J1']
   *                           array  formula     设置公   ，例如['F2' => '=IF(D2>0,E42/D2,0)']
   *                           array  format      设置格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        设置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置   则文件保存到服务器，不通过浏览器下载
   */	
  function exportExcelGLCost($result,$hd,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		
		set_time_limit(0);
		$columnCnt=$titledata['Col'];
		$rowCnt=19;//count($data); 
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

		//将A1单元格设   成粗体，黑体，10号字
        $sheet->getStyle('A1')->getFont()->setBold(true)->setName('黑体')->setSize(14);

		$sheet->setCellValue('A1',  (string)$titledata['Title']); 
		$sheet->setCellValue('D2',  (string)$titledata['TitleDate']); 
		$sheet->setCellValue('A3', "公司名称:". (string)$titledata['coyname']); 
		//$sheet->setCellValue('J3',  "单位：".(string)$titledata['Units']); 
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
		$activeSheet->getStyle('A'.(int)($k+1).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+2))->applyFromArray($styleArray);
		  /* 设置宽度 */
		  $activeSheet->getColumnDimension('A')->setWidth(12);
		$activeSheet->getColumnDimension('B')->setWidth(10);
		$activeSheet->getColumnDimension('C')->setWidth(50);
		$activeSheet->getColumnDimension('D')->setWidth(20);
		$activeSheet->getColumnDimension('E')->setWidth(20);
		$activeSheet->getColumnDimension('F')->setWidth(5);
		$activeSheet->getColumnDimension('G')->setWidth(20);
	
		$activeSheet->getColumnDimension('H')->setAutoSize(true);
		$_row=1+$k;
		$_col=1;
		//页头
		foreach($hd as $val){
			$cellName = Coordinate::stringFromColumnIndex($_col);
			$cellId   = $cellName . ($_row);
		    //prnMsg($cellId.'--'.$col);
			$sheet->setCellValue($cellId,  $val); 
			$_col++;
		}
		foreach($header as $key=>$val){
			if ($val[6]!=-2){
				$cellName = Coordinate::stringFromColumnIndex($_col);
				$cellId   = $cellName . ($_row);
				
				$sheet->setCellValue($cellId,$val[0]."\n".$key);
				$sheet->getStyle($cellId)->getAlignment()->setWrapText(true);
				$_col++;
			}
		
		}
		$_row+=1;
		$sheet->setCellValue("A".($_row), ''); 	
		$sheet->setCellValue("B".($_row),"");				
		$sheet->setCellValue("C".($_row),"结转余额");
		if (round($titledata['RunningTotal'],POI)>0){
			$sheet->setCellValue("D".($_row),"借");
			$sheet->setCellValue("E".($_row), $titledata['RunningTotal']);
		}else{
			$sheet->setCellValue("D".($_row),"贷");
			$sheet->setCellValue("E".($_row), $titledata['RunningTotal']);
		}
		$_col=8;
		foreach($header as $key=>$val){
			if ($val[6]!=-2){
				$cellName = Coordinate::stringFromColumnIndex($_col);
				$cellId   = $cellName . ($_row);
				
				$sheet->setCellValue($cellId,locale_number_format($val[1],POI));
				$_col++;
			}			
		}
		$_row+=1;
		$PeriodNo=-9999;
		$PeriodTotal = 0;
		$PeriodNo = -9999;
		$ShowIntegrityReport = False;
		$DeSum = 0;
		$CrSum = 0;
		$j = 1;
		$rowCnt=DB_num_rows($result);
		$activeSheet->getStyle('A'.(int)($_row).':'.Coordinate::stringFromColumnIndex($columnCnt).($_row+$rowCnt))->applyFromArray($styleArray);
	while ($row=DB_fetch_array($result)){
		if ($row['periodno']!=$PeriodNo){
			if ($PeriodNo!=-9999){ //ie its not the first time around
				$sheet->setCellValue("A".($_row), ''); 	
				$sheet->setCellValue("B".($_row),"");				
				$sheet->setCellValue("C".($_row),"本月合计");
				
					$sheet->setCellValue("D".($_row), locale_number_format($DebitSum,POI));
					$sheet->setCellValue("E".($_row),  locale_number_format($CreditSum,POI));
				
				$_col=8;
				foreach($header as $key=>$val){
					if ($val[6]!=-2){
						$cellName = Coordinate::stringFromColumnIndex($_col);
						$cellId   = $cellName . ($_row);
						
						$sheet->setCellValue($cellId,locale_number_format($val[2],POI));
						$_col++;
					}			
				}
		
				$_row++;	
			}
			foreach($header as $key=>$val){
				if ($val[6]!=-2){
					$header[$key][2]=0;
					$header[$key][3]=0;
					if ($DebitSum!=0){
						$header[$key][2]=0;
					}else{
						$header[$key][3]=0;
					}
				}
			}
			$PeriodNo = $row['periodno'];
			$DeSum += $DebitSum ;
			$CrSum +=$CreditSum;
			$PeriodTotal = 0;
			$DebitSum = 0;
			$CreditSum = 0;
			$exDebitSum = 0;
			$exCreditSum = 0;
			
		}
		$exRunningTotal += $row['examount'];
		$exPeriodTotal += $row['examount'];
		$exAmount = locale_number_format($row['examount'],POI);
		$exDebitSum +=$row['exdebit'];		
		$exCreditSum +=  $row['excredit'] ;
		$RunningTotal += $row['amount'];
		$PeriodTotal += $row['amount'];
		$DebitAmount = locale_number_format($row['debit'],POI);
		$DebitSum +=$row['debit'];
		$CreditAmount = locale_number_format($row['credit'],POI);
		$CreditSum +=  $row['credit'] ;

		$sheet->setCellValue("A".($_row), ConvertSQLDate($row['trandate'])); 	
		$sheet->setCellValue("B".($_row),$_SESSION['tagref'][$row['tag']][2].$row['typeno'] );				
		$sheet->setCellValue("C".($_row), $row['narrative']);
		
		$sheet->setCellValue("D".($_row), locale_number_format($DebitAmount,POI));
		$sheet->setCellValue("E".($_row),  locale_number_format($CreditAmount,POI));
		$sheet->setCellValue("F".($_row),( ($RunningTotal >= 0)?'借':'贷'));
		$sheet->setCellValue("G".($_row), locale_number_format(($RunningTotal >= 0)? $RunningTotal:-$RunningTotal,POI) );
		
		$_col=8;
		$header[$key][3]=0;
		$header[$key][2]=0;		
		foreach($header as $key=>$val){
			if ($val[6]!=-2){
				$cellName = Coordinate::stringFromColumnIndex($_col);
				$cellId   = $cellName . ($_row);
				if($key==$row['account']){
					//prnMsg($cellId.'-'.$row['debit'].'='.$row['credit']);
					if (empty($row['debit'])){
						$sheet->setCellValue($cellId,locale_number_format(-$row['credit'],POI));
						//echo' 	<td class="number">'.locale_number_format(-$row['credit'],POI).'</td>	';
						$header[$key][3]+=$row['credit'];
						$header[$key][5]+=$row['credit'];
					}else{
						//echo' 	<td class="number">'.locale_number_format($row['debit'],POI).'</td>	';
						$sheet->setCellValue($cellId,locale_number_format($row['debit'],POI));
						$header[$key][2]+=$row['debit'];
						$header[$key][4]+=$row['debit'];
					}
				}else{
					$sheet->setCellValue($cellId,'');
				
				}	
				$_col++;
			}
		}
		$_row++;
	}
	//$_row++;	
		$sheet->setCellValue("A".($_row), ''); 	
		$sheet->setCellValue("B".($_row),"");				
		$sheet->setCellValue("C".($_row),"本月合计");
		
			$sheet->setCellValue("D".($_row),  locale_number_format($DebitSum,POI));
			$sheet->setCellValue("E".($_row),  locale_number_format($CreditSum,POI));
		
		$_col=8;
		foreach($header as $key=>$val){
			if ($val[6]!=-2){
				$cellName = Coordinate::stringFromColumnIndex($_col);
				$cellId   = $cellName . ($_row);
				
				$sheet->setCellValue($cellId,locale_number_format($val[2],POI));
				$_col++;
			}			
		}
		$_row++;	
	
		$sheet->setCellValue("A".($_row), ''); 	
		$sheet->setCellValue("B".($_row),"");				
		$sheet->setCellValue("C".($_row),"查询期总计");
		
			$sheet->setCellValue("D".($_row), locale_number_format(($DeSum+$DebitSum),POI));
			$sheet->setCellValue("E".($_row),  locale_number_format(($CrSum+$CreditSum),POI));
		
		$_col=8;
		foreach($header as $key=>$val){
			if ($val[6]!=-2){
				$cellName = Coordinate::stringFromColumnIndex($_col);
				$cellId   = $cellName . ($_row);
				
				$sheet->setCellValue($cellId,locale_number_format($val[4],POI));
				$_col++;
			}			
		}
		$activeSheet->getStyle('A'.(int)($_row-$rowCnt).':'.Coordinate::stringFromColumnIndex($columnCnt).($_row))->applyFromArray($styleArray);
		//$_row++;	
	
    

	
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
