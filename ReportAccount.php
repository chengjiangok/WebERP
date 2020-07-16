
<?php
/*
 * @Author: ChengJiang 
 * @Date: 2017-05-26 06:15:11 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-12-24 09:42:12
 汉字文件名
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
$Title ='会计报表';
$ViewTopic= 'ReportAccount';
$BookMark ='ReportAccount';

include('includes/SQL_CommonFunctions.inc');
include  ('includes/header.php');
if (!isset($_POST['SelectPrd'])OR $_POST['SelectPrd']==''){
		$_POST["SelectPrd"]=$_SESSION['period'];
 }
 if (!isset($_POST['TagsGroup']) ){
	$_POST['TagsGroup']=1;		 
}
 if(!isset($_POST['ImportExcel'])) { 
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		$Title . '" /> ' .// Icon title.
		$Title. '</p>';// Page title.
 // if (!isset($_POST['crtPDF']) OR isset($_POST['ShowSheet'])){
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class="selection">';
 	echo '<tr>
	        <td>' . _('Select Period To')  . '</td>
			<td>';
			//SelectPeriod($_SESSION['period'],$_SESSION['janr']);
			    /*  会计期间选择控件设置默认选择和默认开始及总显示期间
	*/	
	
	$startprd=$_SESSION['startperiod'];
	
	$dt=date("Y-m-d", strtotime($_SESSION['lastdate']));
	
	if(!isset($_POST['SelectPrd'])){
		$_POST['SelectPrd']=$defaultprd;
	}
	$quarter=array(1,4,7,10);
	$quartnumber=array(1=>'1',2=>'2',3=>'3',4=>'4');
	echo'<select name="SelectPrd">';	
		for ($i=$_SESSION['period'];$i>=$startprd;$i--){
			$prd=$i;
			if (isset($_POST['SelectPrd']) and ($prd==$_POST['SelectPrd'])&&$_POST['SelectPrd']>0){			
					echo '<option selected="selected" value="' ;
				}else {
					echo '<option value ="';
				}			
				echo   $prd . '">' . MonthAndYearFromSQLDate($dt). '</option>';
			
				$mth=date('m',strtotime($dt));
			if (in_array($mth,$quarter)){
				if (isset($_POST['SelectPrd']) and (-$prd==$_POST['SelectPrd'])&&$_POST['SelectPrd']<0){			
					echo '<option selected="selected" value="' ;
				}else {
					echo '<option value ="';
				}			
				echo (-$prd) . '">'.$quartnumber[ceil($mth/3)].'季度'.date('Y',strtotime($dt)).'</option>';
			}
			$dt=date("Y-m-d", strtotime("last day of -1 month", strtotime($dt)));
		}
		echo '</select>';
	
	echo'</td></tr>';	
		
   echo '<tr>
          <td>选择报表:</td>
	      <td><select name="rpttype" size="1" >';
 	     	$sql="SELECT reportid, reportname FROM reporttype WHERE rpttype=1";
		    $Result = DB_query($sql);
		while ($myrow=DB_fetch_array($Result,$db)){	
	
		 if(isset($_POST['rpttype']) AND $myrow['reportid']==$_POST['rpttype']){	
				
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
			echo  $myrow['reportid'] . '">' .  $myrow['reportname'] . '</option>';
		}
	echo	'</select>
	        </td>
			</tr>';
    echo '<tr>
     	    <td>单元分组</td>
			  <td>';
			 TagGroup(7);
		
		echo'</select>';	
			 // SelectUnitsTag(1); 
	echo'</td>
			  </tr>'; 
	echo '	</table><br />';
    if ($_POST['SelectPrd']<1){
		//选择的季度  期间为负���
		$StartPrd=abs($_POST['SelectPrd']);//季度开始期间
		$SelectPrd=abs($_POST['SelectPrd'])+2;//季度季度结束期间
	}else{
		//选择月份的期间
		$StartPrd=$_POST['SelectPrd'];
		$SelectPrd=$_POST['SelectPrd'];
	}
	echo '<div class="centre">
			<input type="submit" name="ShowSheet" value="查询显示" />
			<input type="submit" name="ImportExcel" value="导出Excel" />
		
		</div>';
  } 
  $tag=$_SESSION['Tag'];
  /*
   if ($_POST['UnitsTag']!=0){
        $tag=abs($_POST['UnitsTag']);
   }else{
	   $tag=1;
   }
   
   if ($_POST['UnitsTag']==0){
		$UserTag=implode(",",$_SESSION[$_SESSION['UserID']]);
	  
	}else{
			
		$UserTag=(string)$_POST['UnitsTag'];	
	}
	*/
	if ($_POST['SelectPrd']>0){
		//选择了月份
	  $SelectDate=PeriodGetDate(abs($_POST['SelectPrd']));
	  $DateLabel=date('Y-m',strtotime($SelectDate));
	 
	  $mth=date("m",strtotime($SelectDate));
	  $janprd=$_POST['SelectPrd']-$mth+1;
	  $startprd=$_POST['SelectPrd'];
	  $endprd=$_POST['SelectPrd'];
	}else{
		//选择了季度
	   $SelectDate=PeriodGetDate(abs($_POST['SelectPrd'])+2); 
	   $mth=date('m',strtotime($SelectDate));
	   $DateLabel=date('Y',strtotime($SelectDate)).'年'.(ceil(($mth)/3)).'季度' ;
	   $janprd=abs($_POST['SelectPrd'])-$mth+3;
	   $startprd=abs($_POST['SelectPrd']);
	   $endprd=abs($_POST['SelectPrd'])+2;
	}
	//prnMsg($janprd.'[1]'.$startprd.'-'.$endprd.'='.$DateLabel);
	$TableDate = '<tr><th colspan="8" class="centr" >'.$DateLabel.'</th></tr>';
if(isset($_POST['ShowSheet'])OR isset($_POST['ImportExcel'])){
	
	 $wd=30;	
	
	$sql='SELECT SUM(amount) FROM gltrans';
    $result = DB_query($sql);
    $row= DB_fetch_row($result);
	$clr='99FF99';
    if (round($row[0],2)!=0) {
       $clr='FF7F50';
	} 
	
}
if (isset($_POST['ShowSheet'])||isset($_POST['ImportExcel'])) {
 
  if ($_POST['rpttype']=='12'){
	//利润表
	//读取利润表公式中的科目
	$SQL=" SELECT formulaField, jd FROM reportitem WHERE  reportNO=21 AND (itemtype=0 OR itemtype=5 )AND formulaField<>''";
	$Result=DB_query($SQL);
	while ($row=DB_fetch_array($Result)){
		if (strlen($row['formulaField'])==4){
			$ActTotal[$row['formulaField']]=$row['jd'];
		}else{
			$act=explode(",",$row['formulaField']);
			if (count($act)==1){
				if (strlen($act[0])==4){
					$ActTotal[$act[0]]=$row['jd'];//一级科目
				}else{
					$ActMx[$act[0]]=$row['jd']; //明细科��
				}

			}else{
				foreach($act  as $val){
					//prnMsg(strlen($val));
					if (strlen($val)>4){
						$ActMx[$val]=$row['jd'];
					}else{
						$ActTotal[$val]=$row['jd'];
					}
				}
			}
			
		}
	}

     //读取汇总项
	$SQL="SELECT    LEFT(account,4) account ,
	                SUM(toamount(amount,periodno,".$startprd.",".$endprd.",1,flg)) bqamoj,
					SUM(toamount(amount,periodno,".$startprd.",".$endprd.",-1,flg)) bqamod,
					SUM(toamount(amount,periodno,".$janprd.",".$endprd.",1,flg)) byamoj,
					SUM(toamount(amount,periodno,".$janprd.",".$endprd.",-1,flg)) byamod 
			FROM  gltrans
			WHERE periodno >= ".$janprd." AND periodno <= ".$endprd."
			      AND LEFT(account,4) IN (".implode(",",array_keys($ActTotal)).")  AND tag IN(".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0]." )
			GROUP BY  LEFT(account, 4);";
	$Result=DB_query($SQL);

	while ($row=DB_fetch_array($Result)){
		$ReportData[$row['account']]=array("BQAmoJ"=>$row['bqamoj'],"BQAmoD"=>$row['bqamod'],"BYAmoJ"=>$row['byamoj'],"BYAmoD"=>$row['byamod']);
	}
    
    //读取明细项
	$SQL="SELECT account ,
					SUM(toamount(amount,periodno,".$startprd.",".$endprd.",1,flg)) bqamoj,
					SUM(toamount(amount,periodno,".$startprd.",".$endprd.",-1,flg)) bqamod,
					SUM(toamount(amount,periodno,".$janprd.",".$endprd.",1,flg)) byamoj,
					SUM(toamount(amount,periodno,".$janprd.",".$endprd.",-1,flg)) byamod  
			FROM gltrans WHERE periodno>=".$janprd." AND periodno<=".$endprd." 
				AND account IN(".implode(",",array_keys($ActMx)).")		  
				AND tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0].")
				GROUP BY account;";
	$Result=DB_query($SQL);
	while ($row=DB_fetch_array($Result)){
		$ReportMX[$row['account']]=array("BQAmoJ"=>$row['bqamoj'],"BQAmoD"=>$row['bqamod'],"BYAmoJ"=>$row['byamoj'],"BYAmoD"=>$row['byamod']);
	}


	$SQL="SELECT  account , title, showlist,itemtype ,formulaField, jd FROM reportitem WHERE  reportNO=21 AND itemtype<=5";

	$Result = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'));
	while ($row=DB_fetch_array($Result)) {
		if ($row['itemtype']==0||$row['itemtype']==5){
			if ($row['formulaField']==''){
				$ReportForm[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"BQAmo"=>0,"BYAmo"=>0);
			}else{
				if(strlen($row['formulaField'])==4){
					if ($row['jd']==1){
						$BQAmo=$ReportData[$row['formulaField']]['BQAmoJ'];
						$BYAmo=$ReportData[$row['formulaField']]['BYAmoJ'];
					}else{
						$BQAmo=$ReportData[$row['formulaField']]['BQAmoD'];
						$BYAmo=$ReportData[$row['formulaField']]['BYAmoD'];
					}
					$ReportForm[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"BQAmo"=>$BQAmo,"BYAmo"=>$BYAmo,"jd"=>$row['jd']);
				}else{
					$act=explode(",",$row['formulaField']);
				
					$BQAmo=0;
					$BYAmo=0;
					$act=explode(",",$row['formulaField']);
					foreach($act  as $val){
						//prnMsg(strlen($val));					
						if (strlen($val)>4){
							if ($row['jd']==1){
								$BQAmo+=$ReportMX[$val]['BQAmoJ'];
								unset($ReportMX[$val]['BQAmoJ']);
								$BYAmo+=$ReportMX[$val]['BYAmoJ'];
								unset($ReportMX[$val]['BYAmoJ']);
							}else{
								$BQAmo+=$ReportMX[$val]['BQAmoD'];
								unset($ReportMX[$val]['BQAmoD']);
								$BYAmo+=$ReportMX[$val]['BYAmoD'];
								unset($ReportMX[$val]['BYAmoD']);
							}
							//$ReportForm[$row['account']]=array("BQAmo"=>$BQAmo,"BYAmo"=>$BYAmo);
						}else{
							//一级科目
							if ($row['jd']==1){
								$BQAmo+=$ReportData[$val]['BQAmoJ'];
								unset($ReportData[$val]['BQAmoJ']);
								$BYAmo+=$ReportData[$val]['BYAmoJ'];
								unset($ReportData[$val]['BYAmoJ']);
							}else{
								$BQAmo+=$ReportData[$val]['BQAmoD'];
								unset($ReportData[$val]['BQAmoD']);
								$BYAmo+=$ReportData[$val]['BYAmoD'];
								unset($ReportData[$val]['BYAmoD']);
							}
							
						
						}
							//$ReportForm[$row['account']]=array("BQAmo"=>$BQAmo,"BYAmo"=>$BYAmo);
					
					}
					$ReportForm[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"BQAmo"=>$BQAmo,"BYAmo"=>$BYAmo,"jd"=>$row['jd']);
				
				}
				
			}
		}elseif($row['itemtype']==2){
			if ($row['formulaField']!=''){
			
				$BQAmo=0;
				$BYAmo=0;
				$act=explode(",",$row['formulaField']);
				foreach($act  as $val){
					
					if ($row['jd']==1){
						$BQAmo+=$ReportForm[$val]['BQAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BQAmoJ']);
						$BYAmo+=$ReportForm[$val]['BYAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BYAmoJ']);
					}else{
						$BQAmo+=$ReportForm[$val]['BQAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BQAmoD']);
						$BYAmo+=$ReportForm[$val]['BYAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BYAmoD']);
					}
				
				}
				$ReportForm[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"BQAmo"=>$BQAmo*$row['jd'],"BYAmo"=>$BYAmo*$row['jd'],"jd"=>$row['jd']);
			}

		}
	}
    
    if(isset($_POST['ImportExcel'])) {// producing a CSV file of customers

		$options = array("print"=>true);//,"setWidth"=>$setWidth);
		
		
		$FileName ="利润表".$DateLabel;
		$TitleData=array("Title"=>'利润表',"FileName"=>$FileName,"TitleDate"=>$DateLabel,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","rowCnt"=>0,"k"=>3,"AmountTotal"=>json_encode($AmoTotal));	
		$Header=array( '项目', '行次', '本期金额', '本年累计金额');		 
			  
		exportReportForm($ReportForm,$Header,$TitleData,$options);
		
	}else{

		$TableHeader = '<tr>
							<th colspan="4" class="centre" >利润表['.$DateLabel.']</th></tr>
						<tr>
							<th colspan="4" height="2">
								<div style="padding: 0; background-color: #'.$clr.'; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
								</div> 
							</th>
							</tr>
							<tr>						
								<th>项目</th>
								<th>行</th>
								<th>本期金额</th>
								<th>本年累计金额</th>
							</tr>';
							
		echo '<div id="Report">';// Division to identify the report block.
		echo '<table class="selection">';
		echo  $TableHeader;
		$k=0;

		foreach ($ReportForm as $key=>$row){
				if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
				printf('<td><h4><i>%s</i></h4></td>							
						<td>%s</td>								
						<td class="number">%s</td>								
						<td class="number">%s</td>
						</tr>',							
						htmlspecialchars($row['title'],ENT_QUOTES,'UTF-8',false),
						$row['showlist'],
						locale_number_format($row['BQAmo'],POI),
						locale_number_format($row['BYAmo'],POI));
		}	
	
		echo '</table>';
		//print_r($ReportForm);
		echo '</div>';// div id="Report".
	}
  }else if ($_POST['rpttype']=='11'){
	  	//资产负债表
	$RetainedEarningsAct = $_SESSION['CompanyRecord'][$tag]['retainedearnings'];
	
		//���取资产负��表公式中的科目
		$SQL=" SELECT formulaField, jd FROM reportitem WHERE  (reportNO=11 OR reportNO=12) AND (itemtype=0 OR itemtype=3 )AND formulaField<>''";
		$Result=DB_query($SQL);
		while ($row=DB_fetch_array($Result)){
			if (strlen($row['formulaField'])==4){
				$ActTotal[$row['formulaField']]=$row['jd'];
			}else{
				$act=explode(",",$row['formulaField']);
				if (count($act)==1){
					if (strlen($act[0])==4){
						$ActTotal[$act[0]]=$row['jd'];//一级科目
					}else{
						$ActMx[$act[0]]=$row['jd']; //明细科目
					}
	
				}else{
					foreach($act  as $val){
						//prnMsg(strlen($val));
						if (strlen($val)>4){
							$ActMx[$val]=$row['jd'];
						}else{
							$ActTotal[$val]=$row['jd'];
						}
					}
				}
				
			}
		}
		//print_r($ActTotal);
		echo "<br/>";
	
		$ActStr=implode(",",array_keys($ActTotal));
		$SQL="SELECT    LEFT(account,4) account ,
		                 SUM(sumamount(amount,periodno,0,$endprd)) amountqm, 
						SUM(sumamount(amount,periodno,0,$janprd-1)) amountqc 
					
				FROM  gltrans
				WHERE periodno >= 0 AND periodno <= ".$endprd."
					  AND LEFT(account,4) IN (".$ActStr.")  AND tag IN(".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0]." )
				GROUP BY  LEFT(account, 4);";
		$Result=DB_query($SQL);
	
		while ($row=DB_fetch_array($Result)){
			$ReportData[$row['account']]=array("QMAmo"=>$row['amountqm'],"QCAmo"=>$row['amountqc']);
		}
		$SQL="SELECT  account,sum(sumamount(amount,periodno,0,$janprd-1)) qcamount,
		                      sum(sumamount(amount,periodno,0,$endprd)) qmamount 
			  FROM  gltrans 
			  WHERE   periodno>=0 AND periodno<=$endprd
			   AND LEFT(account,4) IN('1122','2202','2241','1221')
			   AND tag IN (".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0]." )
			   GROUP BY account  HAVING sum(sumamount(amount,periodno,0,$janprd-1)) <>0 OR sum(sumamount(amount,periodno,0,$endprd))<>0";
		
		$Result=DB_query($SQL);
		while ($row=DB_fetch_array($Result)){
			if (substr($row['account'],0,4)=='1122'){
				if (round($row['qcamount'],POI)>0){
					$ReportSF['1122']["QCAmo"]+=round($row['qcamount'],POI);					
				}else {
					$ReportSF['2203']["QCAmo"]+=round($row['qcamount'],POI);
				
				}
				if (round($row['qmamount'],POI)>0){
					$ReportSF['1122']["QMAmo"]+=round($row['qmamount'],POI);
					
				}else {
					$ReportSF['2203']["QMAmo"]+=round($row['qmamount'],POI);
					
				}
			}elseif(substr($row['account'],0,4)=='2202'){
				if (round($row['qcamount'],POI)>0){
					$ReportSF['1123']["QCAmo"]+=round($row['qcamount'],POI);
				
				}else {
					$ReportSF['2202']["QCAmo"]+=round($row['qcamount'],POI);
					
				}
				if (round($row['qmamount'],POI)>0){
					
					$ReportSF['1123']["QMAmo"]+=round($row['qmamount'],POI);
				}else {
					$ReportSF['2202']["QMAmo"]+=round($row['qmamount'],POI);
				
				}

			}elseif(substr($row['account'],0,4)=='1221'){
				if (round($row['qcamount'],POI)>0){
					$ReportSF['1221']["QCAmo"]+=round($row['qcamount'],POI);
					
				}else {
				
					$ReportSF['2241']["QCAmo"]+=round($row['qcamount'],POI);
				}
				if (round($row['qmamount'],POI)>0){
				
					$ReportSF['1221']["QMAmo"]+=round($row['qmamount'],POI);
				}else {
					$ReportSF['2241']["QMAmo"]+=round($row['qmamount'],POI);
				
				}

			}elseif(substr($row['account'],0,4)=='2241'){
				if (round($row['qcamount'],POI)>0){
					$ReportSF['1221']["QCAmo"]+=round($row['qcamount'],POI);
					
				}else {
					$ReportSF['2241']["QCAmo"]+=round($row['qcamount'],POI);
				
				}
				if (round($row['qmamount'],POI)>0){
					$ReportSF['1221']["QMAmo"]+=round($row['qmamount'],POI);
				
				}else {
					
					$ReportSF['2241']["QMAmo"]+=round($row['qmamount'],POI);
				}

			}
		
		}
		foreach($ReportSF as $key=>$row){
			$ReportData[$key]["QMAmo"]=$row['QMAmo'];
			$ReportData[$key]["QCAmo"]=$row['QCAmo'];
		}
	
			
		$SQL="SELECT  account , title, showlist,itemtype ,formulaField, jd FROM reportitem WHERE  (reportNO=11 OR reportNO=12) AND itemtype<5";
	
		$Result = DB_query($SQL,_('No general ledger accounts were returned by the SQL because'));
    
		$r=0;
	 	$ListCount = DB_num_rows($Result);

	while ($row=DB_fetch_array($Result)) {
		if ($row['itemtype']==0||$row['itemtype']==3){
			if ($row['formulaField']==''){
				$BalanceSheet[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"QCAmo"=>0,"QMAmo"=>0,"jd"=>$row['jd']);
			}else{
			
					$act=explode(",",$row['formulaField']);
					$QCAmo=0;
					$QMAmo=0;
					$act=explode(",",$row['formulaField']);
					foreach($act  as $val){
								$QCAmo+=$ReportData[$val]['QCAmo'];
								unset($ReportData[$val]['QCAmo']);
								$QMAmo+=$ReportData[$val]['QMAmo'];
								unset($ReportData[$val]['QMAmo']);			
					}
					$BalanceSheet[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"QCAmo"=>$QCAmo,"QMAmo"=>$QMAmo,"jd"=>$row['jd']);
			}
		}elseif($row['itemtype']==2){
			if ($row['formulaField']!=''){
				//prnMsg($row['formulaField']);
				$QCAmo=0;
				$QMAmo=0;
				$act=explode(",",$row['formulaField']);
				foreach($act  as $val){
					//prnMsg(strlen($val));
					if ($row['jd']==1){
						$QCAmo+=$BalanceSheet[$val]['QCAmo'];//*$BalanceSheet[$val]['jd'];
						//unset($BalanceSheet[$val]['BQAmoJ']);
						$QMAmo+=$BalanceSheet[$val]['QMAmo'];//*$BalanceSheet[$val]['jd'];
						//unset($BalanceSheet[$val]['BYAmoJ']);
					}else{
						$QCAmo+=$BalanceSheet[$val]['QCAmo'];//*$BalanceSheet[$val]['jd'];
						//unset($BalanceSheet[$val]['BQAmoD']);
						$QMAmo+=$BalanceSheet[$val]['QMAmo'];//*$BalanceSheet[$val]['jd'];
						//unset($BalanceSheet[$val]['BYAmoD']);
					}
				
				}
				$BalanceSheet[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"QCAmo"=>$QCAmo,"QMAmo"=>$QMAmo,"jd"=>$row['jd']);
			}

		}
	     
		
		$r++;
	}
	//print_r($BalanceSheet);
     if(isset($_POST['ImportExcel'])) {// producing a CSV file of customers

		$options = array("print"=>true);//,"setWidth"=>$setWidth);
		
		
		
		$FileName ="资产负债表". $DateLabel;
		$TitleData=array("Title"=>'资产负债表',"FileName"=>$FileName,"TitleDate"=>$DateLabel,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","rowCnt"=>32,"k"=>3,"AmountTotal"=>json_encode($AmoTotal));	
		$Header=array( '资产', '行次', '期末余额', '年初余额', '负债及所有者权益', '行次', '期末余额', '年初余额');
	 
		exportBalanceReport($BalanceSheet,$Header,$TitleData,$options);
		
	}else{

	//print_r($BalanceSheet);
	echo '<div id="Report">';// Division to identify the report block.
		echo '<table class="selection">';
			$TableHeader = '<tr><th colspan="8" class="centre" >资产负债表['.$DateLabel.']</th></tr>
			              <tr>
		                   <th colspan="8" height="2">
								<div style="padding: 0; background-color: #'.$clr.'; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
		                 		</div> 
							</th>
						   </tr>
					 <tr>
						<th>' . _('Asset') . '</th>
						<th>行</th>
						<th>年初余额</th>
						<th >期末余额</th>
						<th>负债和所有者权益</th>
						<th>行次</th>
						<th>年初余额</th>
						<th >期末余额</th>					
					</tr>';	
			$k=DB_num_rows($AccountsResult);
	
	echo  $TableHeader;
	$r=0;
	$rowtotal=64-count($BalanceSheet);
	for($i=0;$i<=32;$i++){ 
		if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			} else {
				echo '<tr class="OddTableRows">';
				$r=1;
			}
			if(($i+50)>=70 && ($i+50)<=($rowtotal+70)){
				 $title='';
				 $showlist='';
				 $qcamo='';
				 $qmamo='';
			  }elseif($i+50 <70){
				$ii=$i+50;
				$rjd=$BalanceSheet[$ii]['jd'];
				$title= htmlspecialchars($BalanceSheet[$ii]['title'],ENT_QUOTES,'UTF-8',false);
				$showlist= $BalanceSheet[$ii]['showlist'];
				$qcamo= locale_number_format($BalanceSheet[$ii]['QCAmo']*$rjd,POI);
				$qmamo= locale_number_format($BalanceSheet[$ii]['QMAmo']*$rjd,POI)	;
				
			  }else{
				  $ii=$i+50-$rowtotal;
				  $rjd=$BalanceSheet[$ii]['jd'];
				 $title= htmlspecialchars($BalanceSheet[$ii]['title'],ENT_QUOTES,'UTF-8',false);
				 $showlist= $BalanceSheet[$ii]['showlist'];
				 $qcamo= locale_number_format($BalanceSheet[$ii]['QCAmo']*$rjd,POI);
				 $qmamo= locale_number_format($BalanceSheet[$ii]['QMAmo']*$rjd,POI)	;
			  }
			  $ljd=$BalanceSheet[$i]['jd'];
			printf('<td>%s</td>	  				
							<td >%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td>%s</td>	  					
							<td >%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							</tr>',	  					
							htmlspecialchars($BalanceSheet[$i]['title'],ENT_QUOTES,'UTF-8',false),
							$BalanceSheet[$i]['showlist'],
							locale_number_format($BalanceSheet[$i]['QCAmo']*$ljd,POI),
							locale_number_format($BalanceSheet[$i]['QMAmo']*$ljd,POI),
							$title,
							$showlist,
							$qcamo,
							$qmamo);

  	}
	
		echo '</table>';
		echo '</div>';// div id="Report".
	}
  }elseif ($_POST['rpttype']=='13'){
	//现金流量包
	$SQL=" SELECT formulaField, jd FROM reportitem WHERE  reportNO=31 AND itemtype=0 AND formulaField<>''";
	//OR itemtype=3 )
	$Result=DB_query($SQL);
	while ($row=DB_fetch_array($Result)){
		if (strlen($row['formulaField'])==4){
			$ActTotal[$row['formulaField']]=$row['jd'];
		}else{
			$act=explode(",",$row['formulaField']);
			if (count($act)==1){
				if (strlen($act[0])==4){
					$ActTotal[$act[0]]=$row['jd'];//��级科目
				}else{
					$ActMx[$act[0]]=$row['jd']; //明细科目
				}

			}else{
				foreach($act  as $val){
					//prnMsg(strlen($val));
					if (strlen($val)>4){
						$ActMx[$val]=$row['jd'];
					}else{
						$ActTotal[$val]=$row['jd'];
					}
				}
			}
			
		}
	}
	//print_r($ActTotal);
	echo "<br/>";
	//print_r($ActMx);
	$ActStr=implode(",",array_keys($ActTotal));
	$SQL="SELECT   type, 
			sum(toamount(amount,periodno,$startprd,$endprd,1,flg)) bqamoj,
			sum(toamount(amount,periodno,$startprd,$endprd,-1,flg) ) bqamod,
			sum(toamount(amount,periodno,$janprd,$endprd,1,flg)) byamoj,
			sum(toamount(amount,periodno,$janprd,$endprd,-1,flg) )  byamod  
			FROM gltrans
			WHERE account LIKE '100%'  AND type!=6 AND periodno >= 0 AND periodno <= ".$endprd."
				  AND LEFT(account,4) IN (".$ActStr.")  AND tag IN(".$_SESSION['tagsgroup'][$_POST['TagsGroup']][0]." )
				  GROUP BY type;";
	$Result=DB_query($SQL);
	while ($row=DB_fetch_array($Result)){
		$ReportData[$row['type']]=array("BQAmoJ"=>$row['bqamoj'],"BQAmoD"=>$row['bqamod'],"BYAmoJ"=>$row['byamoj'],"BYAmoD"=>$row['bYamod']);
	}
	

	$SQL="SELECT  account , title, showlist,itemtype ,formulaField, jd FROM reportitem WHERE  reportNO=31 AND itemtype<5";
	$Result = DB_query($SQL);
	//$ListCount = DB_num_rows($Result); 
    while ($row=DB_fetch_array($Result)) {
		if ($row['itemtype']==0||$row['itemtype']==3){
			if ($row['formulaField']==''){
				$ReportForm[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"BQAmo"=>0,"BYAmo"=>0,"jd"=>$row['jd']);
			}else{
			
					$act=explode(",",$row['formulaField']);
					$BQAmo=0;
					$BYAmo=0;
					$act=explode(",",$row['formulaField']);
					foreach($act  as $val){
						if ($row['jd']==1){
							$BQAmo+=$ReportData[$val]['BQAmoJ'];
							unset($ReportData[$val]['BQAmoJ']);
							$BYAmo+=$ReportData[$val]['BYAmoJ'];
							unset($ReportData[$val]['BYAmoJ']);
						}else{
							$BQAmo+=$ReportData[$val]['BQAmoD'];
							unset($ReportData[$val]['BQAmoD']);
							$BYAmo+=$ReportData[$val]['BYAmoD'];
							unset($ReportData[$val]['BYAmoD']);

						}			
					}
					$ReportForm[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"BQAmo"=>$BQAmo,"BYAmo"=>$BYAmo,"jd"=>$row['jd']);
			}
		}elseif($row['itemtype']==2){
			if ($row['formulaField']!=''){
			
				$BQAmo=0;
				$BYAmo=0;
				$act=explode(",",$row['formulaField']);
				foreach($act  as $val){
					//prnMsg(strlen($val));
					if ($row['jd']==1){
						$BQAmo+=$ReportForm[$val]['BQAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BQAmoJ']);
						$BYAmo+=$ReportForm[$val]['BYAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BYAmoJ']);
					}else{
						$BQAmo+=$ReportForm[$val]['BQAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BQAmoD']);
						$BYAmo+=$ReportForm[$val]['BYAmo']*$ReportForm[$val]['jd'];
						//unset($ReportForm[$val]['BYAmoD']);
					}
				
				}
				$ReportForm[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"BQAmo"=>$BQAmo,"BYAmo"=>$BYAmo,"jd"=>$row['jd']);
			}

		}
	     
		
		$r++;
	}
	$SQL="SELECT sum(sumamount(amount,periodno,0,$startprd-1)) qcye,
	             sum(sumamount(amount,periodno,0,$endprd) ) qmye ,
				 sum(sumamount(amount,periodno,0,$janprd-1)) ycye
	          
			FROM gltrans 
			WHERE account LIKE '100%' AND periodno>=0 AND periodno<=".$endprd;

	$Result = DB_query($SQL);
	$Row=DB_fetch_assoc($Result);
	//现金期初期末余额
	$ReportForm[21]['BQAmo']=$Row['qcye'];
	$ReportForm[22]['BQAmo']=$Row['qmye'];

	$ReportForm[21]['BYAmo']=$Row['ycye'];
	$ReportForm[22]['BYAmo']=$Row['qmye'];
	if(isset($_POST['ImportExcel'])) {// producing a CSV file of customers

		$options = array("print"=>true);//,"setWidth"=>$setWidth);
		
		/*
		,"freezePane"=>"A2","setARGB"=>"['A1', 'C1']","setWidth"=>"['A' => 30, 'C' => 20]"
							   ,"setBorder"=>0,"mergeCells"=>"['A1:J1' => 'A1:J1']","formula"=>"['F2' => '=IF(D2>0,E42/D2,0)']"
							   ,"format"=>"['A' => 'General']","alignCenter"=>"['A1', 'A2']","bold"=>"['A1', 'A2']","savePath"=>"C:\Wnmp\html\GJWERP\companies\hualu_erp" );
		*/
		
		$FileName ="现金流量表".$DateLabel;
		$TitleData=array("Title"=>'现金流量表',"FileName"=>$FileName,"TitleDate"=>$DateLabel,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","rowCnt"=>0,"k"=>3,"AmountTotal"=>json_encode($AmoTotal));	
		$Header=array( '项目', '行次', '本期发生额', '本年发生额');		 
			  
		exportReportForm($ReportForm,$Header,$TitleData,$options);
		
	}else{
		$TableHeader = '<tr>
						<th colspan="4" class="centre" >现金流量表['.$DateLabel.']</th>
						</tr>
						<tr>
						<th colspan="4" height="2">
							<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
							</div> 
						</th>
						</tr>
						<tr>						
							<th>项目</th>
							<th>行次</th>
							<th>本期发生额</th>
							<th>本年发生额</th>
						</tr>';
							

		echo '<div id="Report">';// Division to identify the report block.
		echo '<table class="selection">';

		echo  $TableHeader;
		$k=0;
		
		//while ($myrow=DB_fetch_array($Result)) {
		foreach ($ReportForm as $key => $row) {
			# code...
		
			//prnMsg($myrow['title'].'-'.(float)$myrow['amounty']);
				if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
				printf('<td><h4><i>%s</i></h4></td>							
						<td>%s</td>								
						<td class="number">%s</td>								
						<td class="number">%s</td>
						</tr>',							
						$row['title'],
						$row['showlist'],
						locale_number_format($row['BQAmo'],POI),
						locale_number_format($row['BYAmo'],POI));
				}	
	
		echo '</table>';
		echo '</div>';// div id="Report".
	}
  }



}

echo '</div>
	</form>';
include('includes/footer.php');



/**
   * Excel导出利润表 现金流量表
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
function exportReportForm($data,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		//取得表行数
	
		if ($titledata['rowCnt']>0){
			$rowCnt=$titledata['rowCnt'];
		}else{
			$rowCnt=count($data);
		} 
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
		$sheet->setCellValue('B2',  "日期 ".(string)$titledata['TitleDate']); 
		$sheet->setCellValue('A3', "公司名称:". (string)$titledata['coyname']); 
		$sheet->setCellValue('D3',  "单位：".(string)$titledata['Units']); 
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
		$activeSheet->getColumnDimension('A')->setWidth(40);		
		$activeSheet->getColumnDimension('B')->setWidth(8);
		$activeSheet->getColumnDimension('C')->setWidth(25);	
		$activeSheet->getColumnDimension('D')->setWidth(30);	
		//$activeSheet->getColumnDimension('D')->setAutoSize(true);
	
		
	
	for ($_row = 1; $_row <= $rowCnt+1; $_row++) {
		
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($_row+$k);
		  
			if ($_row==1){
				//表头
				$sheet->setCellValue($cellName.($_row+$k),  (string)$header[$_column-1]); 		  
			}else{
				$ljd=1;//$data[$_row-1]['jd'];
				//$BalanceSheet[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"QCAmo"=>0,"QMAmo"=>0,"jd"=>$row['jd']
				if ($_column==1){							
						$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1]['title']);
				}elseif ($_column==2){					
						$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1]['showlist']);
				}elseif ($_column==3){					
						$sheet->setCellValue($cellName.($_row+$k),locale_number_format($data[$_row-1]['BQAmo']*$ljd,POI));
				}elseif ($_column==4){					
						$sheet->setCellValue($cellName.($_row+$k), locale_number_format($data[$_row-1]['BYAmo']*$ljd,POI));
				}
			}

			if (!empty($data[$_row-1][$cellName-1])) {
				$isNull = false;
			}
		}


	}
	
	
	
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

/**
   * Excel导出资产负债表
   *
   * @param array  $datas      导出数据，格式['A1' => 'XXXX公司报表', 'B1' => '序号']
   * @param array  $header   导出文件名称
   * @param array  $TitleData "Title"=>'客户名单',
   * 						  "FileName"=>$FileName,
   * 						  "TitleDate"=>"2020-03-26",
   *                          "Compy"=>"华陆数控公司",
   *                          "Units"=>"元",
   *                           "k"=>3;
   * @param array  $options    操作选项，���如：
   *                           bool   print       设��打印格式
   *                           string freezePane  锁定行数，例如表头为第一行，则锁定表头输入A2
   *                           array  setARGB     设置背景色，例如['A1', 'C1']
   *                           array  setWidth    设置宽度，例如['A' => 30, 'C' => 20]
   *                           bool   setBorder   设置单元格边框
   *                           array  mergeCells  设置合并单元格，例如['A1:J1' => 'A1:J1']
   *                           array  formula     设置公式，例如['F2' => '=IF(D2>0,E42/D2,0)']
   *                           array  format      设置格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        设置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置后则文件保存到服务器，���通过浏览器下载
   */	
  function exportBalanceReport($data,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		//取得表行数
		$rowtotal=64-count($data);
		if ($titledata['rowCnt']>0){
			$rowCnt=$titledata['rowCnt'];
		}else{
			$rowCnt=count($data);
		} 
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
		$sheet->setCellValue('D2',  "日期 ".(string)$titledata['TitleDate']); 
		$sheet->setCellValue('A3', "公司名称:". (string)$titledata['coyname']); 
		$sheet->setCellValue('H3',  "单位：".(string)$titledata['Units']); 
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
		$activeSheet->getColumnDimension('A')->setWidth(25);		
		$activeSheet->getColumnDimension('B')->setWidth(8);
		$activeSheet->getColumnDimension('C')->setAutoSize(true);
		$activeSheet->getColumnDimension('D')->setAutoSize(true);
		$activeSheet->getColumnDimension('E')->setWidth(25);
		$activeSheet->getColumnDimension('F')->setWidth(8);
		$activeSheet->getColumnDimension('G')->setAutoSize(true);	
		$activeSheet->getColumnDimension('H')->setAutoSize(true);
		
	
	//for ($_row = 1; $_row <= $rowCnt; $_row++) {
		for($_row=1;$_row<=32+$k;$_row++){ 
		
				if(($_row+49)>=70 && ($_row+49)<=($rowtotal+70)){
					 $title='';
					 $showlist='';
					 $qcamo='';
					 $qmamo='';
				  }elseif($_row+49 <70){
					$ii=$_row+49;
					$rjd=$data[$ii]['jd'];
					$title= htmlspecialchars($data[$ii]['title'],ENT_QUOTES,'UTF-8',false);
					$showlist= $data[$ii]['showlist'];
					$qcamo= locale_number_format($data[$ii]['QCAmo']*$rjd,POI);
					$qmamo= locale_number_format($data[$ii]['QMAmo']*$rjd,POI)	;
					
				  }else{
					  $ii=$_row+49-$rowtotal;
					  $rjd=$data[$ii]['jd'];
					 $title= htmlspecialchars($data[$ii]['title'],ENT_QUOTES,'UTF-8',false);
					 $showlist= $data[$ii]['showlist'];
					 $qcamo= locale_number_format($data[$ii]['QCAmo']*$rjd,POI);
					 $qmamo= locale_number_format($data[$ii]['QMAmo']*$rjd,POI)	;
				  }
			$ljd=$data[$_row-1]['jd'];
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($_row+$k);
		  
			if ($_row==1){
				//表头
				$sheet->setCellValue($cellName.($_row+$k),  (string)$header[$_column-1]); 		  
			}else{
				//$BalanceSheet[$row['account']]=array('title'=>$row['title'],'showlist'=>$row['showlist'],"QCAmo"=>0,"QMAmo"=>0,"jd"=>$row['jd']
				if ($_column==1){							
						$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1]['title']);
				}elseif ($_column==2){					
						$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1]['showlist']);
				}elseif ($_column==3){					
					$sheet->setCellValue($cellName.($_row+$k), locale_number_format($data[$_row-1]['QMAmo']*$ljd,POI));
				}elseif ($_column==4){	
					$sheet->setCellValue($cellName.($_row+$k),locale_number_format($data[$_row-1]['QCAmo']*$ljd,POI));				
						
				}elseif($_column==5){

					$sheet->setCellValue($cellName.($_row+$k),$title);
				}elseif ($_column==6){					
						$sheet->setCellValue($cellName.($_row+$k), $showlist);
				}elseif ($_column==7){					
					$sheet->setCellValue($cellName.($_row+$k), $qmamo);
				}elseif ($_column==8){
					$sheet->setCellValue($cellName.($_row+$k),$qcamo);					
						
				}   				
			}

			if (!empty($data[$_row-1][$cellName-1])) {
				$isNull = false;
			}
		}


	}
	
	
	
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
	//注��	createWriter($spreadsheet, 'Xls') //第二个参数首字母必须大写
	$writer->save('php://output'); 

}	
?>
