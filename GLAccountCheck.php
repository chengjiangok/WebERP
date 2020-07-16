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
$Title = '往来客户对账';
$ViewTopic= 'GLAccountDay';
$BookMark = 'GLAccountDay';

include('includes/SQL_CommonFunctions.inc');
include  ('includes/header.php');

echo'<script type="text/javascript">
function inCheck(p,r){

	console.log(r);
	document.getElementById("CheckAmoEdit_"+r).value=1;
	document.getElementById("Check"+r).checked=true;
}
function inRemark(p,r){

		document.getElementById("RemarkEdit_"+r).value=1;
		document.getElementById("Check"+r).checked=true;
}
	function checkinput(obj){
	//var td=obj.parentNode;  
	//alert( td.parentNode.rowIndex);
	//glaccday.rows[td.parentNode.rowIndex].cells[td.cellIndex].getElementsByTagName("INPUT")[3].value=1;
 }
 function clickinput(obj){
	var td=obj.parentNode; 
	//alert( td.parentNode.rowIndex);
	glaccday.rows[td.parentNode.rowIndex].cells[td.cellIndex].getElementsByTagName("INPUT")[3].value=1;
 }
</script>';
/*
if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}*/
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}

if (!isset($_POST['query'])) {
   $_POST['query']=1;
}

if (abs($_POST['UnitsTag'])>0){
	$tag=abs($_POST['UnitsTag']);
}else{
	$tag=1;

}
if ($_POST['UnitsTag']==0){
   $tagusers=implode(",",$_SESSION[$_SESSION['UserID']]);
}else{
   $tagusers=$tag;	
}

if(!isset($_POST['ExportExcel'])) {
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		$Title . '" /> ' .// Icon title.
		$Title . '</p>';// Page title.  

	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	   	  <input type="hidden" name="Account" value="' . $_POST['Account'] . '" />    ';

    echo '<div>';
	echo '<table class="selection">';  
	echo '<tr>
	      <td>' . _('Select Period To')  . '</td>
		  <td >';
		  SelectPeriod($_SESSION['period'],$_SESSION['period']);
		
	echo '</td>
		 <td></td>
		 </tr>';
		 $AccountType=[	"1122"=>"应收账款",	 "1221"=>"其他应收款", "2202"=>"应付账款",	 "2241"=>"其他应付款"];
	echo'<tr>
			<td>科目类别</td>    			
			<td><select tabindex="1"  name="AccountType">';
			if ($_POST['AccountType'] == 'All') {
				echo '<option selected="selected" value="All">' . _('All') . '</option>';
			} else {
				echo '<option value="All">' . _('All') . '</option>';
			}
			foreach ($AccountType as $key=>$val){				
				if ($key==$_POST['AccountType']){
					echo '<option selected="selected" value="'.$key.'" >'.$key.$val.'</option>';
				} else {
					echo '<option value="'.$key.'" >'.$key.$val.'</option>';
				}
			}
		echo'</select>
		</td></tr>';	
	echo '<tr>
			<td>科目代码/名:</td>
			<td >
			 <input type="text"  name="Account" size="20" maxlength="20" value="'.$_POST['Account'].'" /></td>
		</tr>';  

   
    echo '<tr>
		 <td>查询方式:</td>
		 <td>
		   <input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' />全部  
		   <input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />对账
		 </td>
		</tr>';
	echo '<tr>
		 <td>非零客户对账单:</td>
		 <td>';
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly'] == false){
			echo '<input type="checkbox" name="NonZerosOnly" value="false" />';
	} else {
			echo '<input type="checkbox" name="NonZerosOnly" value="true" checked />';
	}      

	echo '	</table>
		<br />';
	echo '<div class="centre">
			<input type="submit" name="Search" value="查询对账单" />
			
			 <input type="submit" name="MakeCheckNew" value="新建对账单" />
			<input type="submit" name="ExportExcel" value="导出Excel" />';
	if(isset($_POST['MakeCheckNew']) 	OR isset($_POST['Go1'])	OR isset($_POST['Next1'])
			OR isset($_POST['Previous1'])) {
		echo'<br/><br/><input type="submit" name="CheckNew" value="对账单生成" />';
	}
	if(isset($_POST['Search']) 	OR isset($_POST['Go'])	OR isset($_POST['Next'])
			OR isset($_POST['Previous'])) {
		echo'<br/><br/><input type="submit" name="CheckSave" value="对账确认" />';
	}
	echo'</div>';
}
	
		$SelectDate =PeriodGetDate($_POST['ERPPrd']);
		$endprd=$_POST['ERPPrd'];	
		$mth=date("m",strtotime($SelectDate));
		$janr=$_POST['ERPPrd']-$mth+1;
		
		$sql="SELECT	`glacid`,
						`account`,
						accountname,
						`checkdate`,
						`period`,
						`debit`,
						`credit`,
						`endamount`,
						`checkamount`,
						`remark`,
						`auditer`,
						`authorised`,
						`auditdate`,
						`regid`,
							glaccountcheck.`edituser`,
						`flg`
					FROM
						`glaccountcheck`
				
				LEFT JOIN chartmaster ON glaccountcheck.account=accountcode
				WHERE	glacid IN (	SELECT 	MAX(glacid)	FROM	`glaccountcheck`	GROUP BY	`account`)";
			if(isset($_POST['Account'])&& $_POST['Account']!=""){		

				if(preg_match("/^\d*$/", $_POST['Account'])){

					$sql .= "  AND  glaccountcheck.account like '".$_POST['Account']."%' ";    
				}else{
					$sql .= "  AND  accountname like '%". $_POST['Account']."%' "; 

				}	  

			}
			if(isset($_POST['AccountType'])&& $_POST['AccountType']!="All"){		

				$sql .= "  AND  glaccountcheck.account like '".$_POST['AccountType']."%' ";    
			}		
	
				$SQL="SELECT   	`accountcode` account,
				`accountname`,
				`group_`,
				`currcode`,
				`cashflowsactivity`,
				`tag`,
				`crtdate`,
				`userid`,
				`low`,
				`used`
			FROM	`chartmaster`
			WHERE	LEFT(accountcode, 4) IN ('1122', '1221', '2202', '2241') AND LENGTH(accountcode)>4";
				
		if(isset($_POST['AccountType'])&& $_POST['AccountType']!="All"){			
		$SQL .= "  AND  accountcode  like '".$_POST['AccountType']."%' ";    
		}		
		$SQL.="	ORDER BY accountcode";
if(isset($_POST['ExportExcel'])) {
	//prnMsg($_POST['CheckCheck'] ."==");
	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	$TitleData=array("Title"=>'对账单',"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal));	
	$Result=DB_query($sql);
		$Header=array(  '序号','科目编码','科目名称',"借方发生额","贷方发生额",'方向','余额','确认余额','备注','确认日期','状态' );	
	//DB_data_seek($ResultCounts,0);	  
	ExportExcel($Result,$Header,$TitleData,$options);

}elseif(isset($_POST['MakeCheckNew']) OR isset($_POST['CheckNew'])||  isset($_POST['Go1'])	OR isset($_POST['Next1'])
	OR isset($_POST['Previous1'])) {
     
		$ResultAccount = DB_query($SQL,'','',false,false);
		
		if (!isset($_POST['Go1']) AND !isset($_POST['Next1']) AND !isset($_POST['Previous1'])) {
		
			$_POST['PageOffset'] = 1;
		}
  
		$ListCount=DB_num_rows($ResultAccount);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}
		
		if ($ListCount>0 AND (isset($_POST['Check']) ||isset($_POST['MakeCheckNew']) 	OR isset($_POST['Go1'])	OR isset($_POST['Next1'])	OR isset($_POST['Previous1']))){//????
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next1'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous1'])) {
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
				<input type="submit" name="Go1" value="' . _('Go') . '" />
				<input type="submit" name="Previous1" value="' . _('Previous') . '" />
				<input type="submit" name="Next1" value="' . _('Next') . '" />';
			echo '</div>';
		}

		echo	'	<br />';
			echo '<table id="glaccday" cellpadding="2" class="selection">';
 
		$TableHeader = '<tr>
		                   <th colspan="11" height="2">
								<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
		                 		</div> 
							</th>
						</tr>
						<tr> 
							<th>序号</th>
							<th>' . _('Account') . '</th>
							<th  width="220" >' .'&nbsp;&nbsp;&nbsp;'. _('Account Name') .'&nbsp;&nbsp;&nbsp;'. '</th>
							<th  width="20">借方发生额</th>	
							<th  width="20">贷方发生额</th>	
							<th>���贷</th>		
							<th>余额</th>				
							<th  width="20">���注</th>
							<th>状态</th>
							<th><input type="checkbox" name="Selectcheck"   onchange="clickinput(this)" value="1" /></th>
						</tr>';
		echo $TableHeader;
		$RowIndex = 0;
		
			$sql="SELECT  `confvalue`  FROM `myconfig` WHERE confname='AccountsCheck'";
			$result = DB_query($sql);
			$row=DB_fetch_assoc($result);
			$CheckPeriod=1;
			if (!empty($row['confvalue'])) {
				$CheckPeriod=$row['confvalue'];
			}
			if (DB_num_rows($ResultAccount) <> 0) {
				DB_data_seek($ResultAccount, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			}
		while ($row=DB_fetch_array($ResultAccount) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			$sql="SELECT	`glacid`,
							`account`,
						
							`checkdate`,
							`period`,
							`debit`,
							`credit`,
							`endamount`,
							`checkamount`,
							`remark`,
							`auditer`,
							`authorised`,
							`auditdate`,
							`regid`,
							glaccountcheck.`edituser`,
							`flg`
					FROM	`glaccountcheck`
					WHERE	glacid IN (	SELECT	MAX(glacid)	FROM  `glaccountcheck` WHERE account ='".$row['account']."'	GROUP BY  `account`) AND account ='".$row['account']."'";
			$ResultCheck = DB_query($sql,'','',false,false);
			//echo $sql;
			$RowCheck=DB_fetch_assoc($ResultCheck);
			if (empty($RowCheck)){
				$flagtxt="新"  ;  
				$sql="SELECT account,
							SUM(toamount(amount, periodno,$janr,$endprd,1,flg)) as debit, 
							SUM(toamount(amount, periodno,$janr,$endprd,-1,flg)) as credit,
							SUM(sumamount(amount, periodno,0,$endprd)) as endamount
					FROM gltrans 
					WHERE periodno<=$endprd AND periodno>=0 AND gltrans.tag IN (".$tagusers.") 
					AND   account ='".$row['account']."'	
						GROUP BY account";
					
				$ResultGL = DB_query($sql,'','',false,false);
				//echo $sql;
				$RowGL=DB_fetch_assoc($ResultGL);
			}else{
				$flagtxt=substr($RowCheck['checkdate'],0,10);
				$Debit=$RowCheck['debit'];
				$Credit=$RowCheck['credit'];
				$EndAmount=$RowCheck['endamount'];
				
			}
			if (DateDiff ($SelectDate,$RowCheck['checkdate'], "m") >$CheckPeriod){
			
				$Checked="checked";		
			}else{
				$Checked= "";
			}
				
			
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
				
					if ($EndAmount>0){
						$jd="借";
					}else{
						$jd="贷";
					}
					if (!isset($_POST['Remark'.$row['account']]))
					$_POST['Remark'.$row['account']]=$row['remark'];

					if (!isset($_POST['CheckAmo'.$row['account']]))
					$_POST['CheckAmo'.$row['account']]=$row['checkamount'];
					echo'	<td >'.($RowIndex+1).'
							<input type="hidden" name="'.$row['account'].'Account"   value="'.$row['account'].'"/>
							<input type="hidden" name="'.$row['account'].'EndAmount"   value="'.($EndAmount).'"/></td>
							<td>'.$ActEnquiryURL.'</td>
							<td >'.$row['accountname'].'</td>
							<td >'.locale_number_format($Debit,POI).'
							<input type="hidden" name="'.$row['account'].'Debit"   value="'.($Debit).'"/>	 </td>
							<td >'.locale_number_format($Credit,POI).'
							<input type="hidden" name="'.$row['account'].'Credit"   value="'.($Credit).'"/>	 </td>
							<td >'.$jd.'</td>
							<td class="number">'.locale_number_format(abs($EndAmount),POI).'</td>
						
							<td title="'.$row['checkdate'].'">
								<input type="hidden" name="'.$row['account'].'CheckDate"  value="'.$row['checkdate'].'"/>
								<input type="text" name="Remark'.$row['account'].'"  size="15" maxlength="20" value="'.$_POST['Remark'.$row['account']].'"/></td>
							<td title="'.$RowCheck['checkdate'].'">'.$flagtxt.'</td>
							<td ><input type="checkbox" name="Check'.$row['account'].'" value="1" '.$Checked.' /></td>
							</tr>';
					
					$RowIndex ++;
		}//end while
		echo '</table><br />';
		echo '<input type="hidden" name="CheckCheck" value="' . $_POST['MakeCheckNew'] . '" />';

	
    }
	if (isset($_POST['CheckNew'])) {
	
	        $CheckNew=0;
		foreach ($_POST as $key => $value) {
			# code...
		 
			if (mb_strstr($key,'Account')) {
				$Index=mb_strstr($key,'Account',TRUE) ;
				
				if ($_POST['Check'.$Index]){
					//prnMsg($_POST[$Index.'Account'].'-'.);
					if ( $_POST['NonZerosOnly']){//非零对账
						if ($_POST[$Index.'EndAmount']!=0 ){
							$SQLNew="INSERT INTO glaccountcheck( `account`,
															`checkdate`,
															`period`,
															`debit`,
															`credit`,
															`endamount`,
															`checkamount`,
															`remark`,
															`edituser`,
															`flg`)
													VALUES ( '".$Index."',
															'".date('Y-m-d h:i:s')."',
															'".$_POST['ERPPrd']."',
															'".$_POST[$Index.'Debit']."',
															".$_POST[$Index.'Credit'].",
															'".$_POST[$Index.'EndAmount']."',
															0,
															'".$_POST['Remark'.$Index]."',
															'".$_SESSION['UserID']."',
															0) ";
							$CheckNew++;
						
						
						}
					}else{
						$SQLNew="INSERT INTO glaccountcheck( `account`,
															`checkdate`,
															`period`,
															`debit`,
															`credit`,
															`endamount`,
															`checkamount`,
															`remark`,
															`edituser`,
															`flg`)
													VALUES ( '".$Index."',
															'".date('Y-m-d h:i:s')."',
															'".$_POST['ERPPrd']."',
															'".$_POST[$Index.'Debit']."',
															".$_POST[$Index.'Credit'].",
															'".$_POST[$Index.'EndAmount']."',
															0,
															'".$_POST['Remark'.$Index]."',
															'".$_SESSION['UserID']."',
															0) ";
						$CheckNew++;

					}
					//echo  $SQLNew.PHP_EOL;
					$result1=DB_query($SQLNew);
					DB_free_result($result1);		
				}
			}
		}//endfor
	     prnMsg("产生客户对账单".$CheckNew."笔！",'success');
	}
}elseif(isset($_POST['CheckSave']) ||isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {
	$ResultCheck=DB_query($sql);
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
	
		$_POST['PageOffset'] = 1;
	}
	$ListCount=DB_num_rows($ResultCheck);
	if ($ListCount==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
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
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
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
						<th colspan="11" height="2">
								<div style="padding: 0; background-color: #99FF99; border: 0; width:'. $wd.'%; text-align: center; height:100%;">
								</div> 
							</th>
						</tr>
						<tr> 
							<th>序号</th>
							<th>' . _('Account') . '</th>
							<th  width="220" >' .'&nbsp;&nbsp;&nbsp;'. _('Account Name') .'&nbsp;&nbsp;&nbsp;'. '</th>
							<th>借贷</th>
							<th>余额</th>
							<th  width="20">确认余额</th>				
							<th  width="20">备注</th>
							<th>核对日期</th>
							<th>状态</th>
							<th><input type="checkbox" name="Selectcheck"   onchange="clickinput(this)" value="1" /></th>
						</tr>';
		echo $TableHeader;
		$RowIndex = 0;
		if (DB_num_rows($ResultCheck) <> 0) {
			DB_data_seek($ResultCheck, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
		
		while ($row=DB_fetch_array($ResultCheck) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			
			$Debit=$row['debit'];
			$Credit=$row['credit'];
			$EndAmount=$row['endamount'];
		
			if ($row['flag']==1){
				$ActEnquiryURL = '<a href="'. $RootPath . '/GLAccountInquiry.php?show=GLTB&amp;acp=' .urlencode(  $row['account'].'^'.$_SESSION['janr'] . '^' . $_POST['ERPPrd']  ). '" target="_black">' . $row['account'] . '</a>';
			}else{
				$ActEnquiryURL = '<a href="'. $RootPath . '/SelectGLAccount.php?Act=' .$row['account'].'" target="_black">' . $row['account'] . '</a>';
			}
		
			$flagtxt="待核对";
			if($row['authorised']==1){ 
					$flagtxt="待批准";
			}elseif($row['authorised']==3){ 
				$flagtxt="核准";
			}
			
			if (!isset($_POST['CheckAmo'.$row['account']]))
				$_POST['CheckAmo'.$row['account']]=$EndAmount;

			if (DateDiff ($SelectDate,$row['checkdate'], "m") >$CheckPeriod){
			
				$Checked="checked";		
			}else{
				$Checked= "";
			}
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
			
				if ($row['endamount']>0){
					$jd="借";
				}else{
					$jd="贷";
				}
				if (!isset($_POST['Remark'.$row['account']]))
					$_POST['Remark'.$row['account']]=$row['remark'];

			
				echo'	<td >'.($RowIndex+1).'
							<input type="hidden" name="'.$row['account'].'Accounts"   value="'.$row['account'].'"/>
							<input type="hidden" name="'.$row['account'].'GLACID"   value="'.$row['glacid'].'"/></td>
						<td>'.$ActEnquiryURL.'</td>
						<td >'.$row['accountname'].'</td>
					
						<td >'.$jd.'</td>
						<td class="number">'.locale_number_format(abs($row['endamount']),$_SESSION['CompanyRecord'][$tag]['decimalplaces']).'
							<input type="hidden" name="'.$row['account'].'EmdAmount"   value="'.$row['endamount'].'"/></td>
						<td >
						<input type="hidden" name="CheckAmoEdit_' . $row['account'] . '"  id="CheckAmoEdit_' . $row['account'] . '" value="0"   />	
						<input  class="number" type="text" name="CheckAmo'.$row['account'].'"  size="10" maxlength="12" value="'. $_POST['CheckAmo'.$row['account']].'"  title="请在这里输入核对正确的余额!'.$EndAmount.'"onChange="inCheck(this,'.$row['account'].')" /></td>
					
						<td title="'.$row['checkdate'].'">
							<input type="hidden" name="'.$row['account'].'CheckDate"   value="'.$row['checkdate'].'"/>
							<input type="hidden" name="RemarkEdit_' . $row['account'] . '"  id="RemarkEdit_' . $row['account'] . '" value="0"   />
							<input type="text" name="Remark'.$row['account'].'"  size="15" maxlength="20" value="'.$_POST['Remark'.$row['account']].'"   onChange="inRemark(this,'.$row['account'].')" /></td>
						<td title="'.$row['checkdate'].'">'.substr($row['checkdate'],0,10).'</td>
							<td title="'.$row['checkdate'].'">'.$flagtxt.'</td>
						<td ><input type="checkbox" name="Check'.$row['account'].'"  id="Check'.$row['account'].'" value="1" /></td>
						</tr>';
				
				$RowIndex ++;
		}//end while
		echo '</table><br />';
		echo '<input type="hidden" name="CheckCheck" value="' . $_POST['Search'] . '" />';

	if (isset($_POST['CheckSave'])) {

	     $CheckUpdate=0;
		foreach ($_POST as $key => $value) {
			# code...
	
			if (mb_strstr($key,'Accounts')) {
				$Index=mb_strstr($key,'Accounts',TRUE) ;
				$GLACID=$_POST[$Index.'GLACID'];
				
				if ($_POST['Check'.$Index]){
					//prnMsg($_POST[$Index.'Accounts'].$_POST['CheckAmo'.$Index].$_POST['Remark'.$Index]);
						$SQL="UPDATE glaccountcheck 
								SET `remark`='".$_POST['Remark'.$Index]."',
									 checkamount=".$_POST['CheckAmo'.$Index].",
									`checkdate`= '".date('Y-m-d h:i:s')."',
									`authorised`=1,
									`edituser`='".$_SESSION['UserID']."'
									
							WHERE 	`account`='".$Index."' AND glacid=".$GLACID;
					
					$result1=DB_query($SQL);
					DB_free_result($result1);	
					$CheckUpdate++;
				}
			
			}
			

		}//end for
		prnMsg("客户对账单确认".$CheckUpdate." 笔！",'success');

	
	}
}
	echo '</div>
	</form>';

include('includes/footer.php');

/**
   * Excel导出��TODO 可继续优化
   *
   * @param array  $Result
   * @param array  $header   导出文件名称
   * @param array  $TitleData "Title"=>'客户名单',
   * 						 
   * 						  "TitleDate"=>"2020-03-26",
   *                          "Compy"=>"华陆数控公司",
   *                          "Units"=>"元",
   *                           "k"=>3;
   * @param array  $options    操作���项，例如：
   *                           bool   print       设置打印格式
   *                           string freezePane  锁���行数，例如表头为第一行，则锁定表头输入A2
   *                           array  setARGB     设置背景色，例如['A1', 'C1']
   *                           array  setWidth    设�����度，例如['A' => 30, 'C' => 20]
   *                           bool   setBorder   设置单元格边框
   *                           array  mergeCells  设置合并单元格，例如['A1:J1' => 'A1:J1']
   *                           array  formula     设置公式，例如['F2' => '=IF(D2>0,E42/D2,0)']
   *                           array  format      设���格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        ��置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置后则文件保存到服务��，不通过浏览器下载
   */	
  function ExportExcel($Result,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		$rowCnt=DB_num_rows($Result); 
		$k=$titledata['k'];
		$sheet = $spreadsheet->getActiveSheet();
		//设置sheet的名字  两种方法
		$sheet->setTitle($titledata['Title']);
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
			//设置��印为A4效果 
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
		$sheet->setCellValue('A'.$k, "公司名称:". (string)$titledata['coyname']); 
		$sheet->setCellValue(Coordinate::stringFromColumnIndex($columnCnt).($k),  "单位：".(string)$titledata['Units']); 
		//设置���认行高
		$sheet->getDefaultRowDimension()->setRowHeight(20);
		
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER, //水平居���
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
		$k++;	
		$activeSheet->getStyle('A'.(int)($k).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+$rowCnt))->applyFromArray($styleArray);
		  /* 设置宽度 */
		$activeSheet->getColumnDimension('B')->setWidth(15);		
		$activeSheet->getColumnDimension('C')->setWidth(50);
		$activeSheet->getColumnDimension('D')->setWidth(20);
		$activeSheet->getColumnDimension('E')->setWidth(20);
		$activeSheet->getColumnDimension('F')->setWidth(5);
		$activeSheet->getColumnDimension('G')->setWidth(20);
		$activeSheet->getColumnDimension('H')->setWidth(20);
		$activeSheet->getColumnDimension('I')->setWidth(30);
		$activeSheet->getColumnDimension('J')->setWidth(15);
		//		$activeSheet->getColumnDimension('D')->setAutoSize(true);
	
		//$activeSheet->getColumnDimension('F')->setWidth(15);
		//$activeSheet->getColumnDimension('G')->setWidth(25);
        //foreach ($options['setWidth'] as $swKey => $swItem) {
		//	$activeSheet->getColumnDimension($swKey)->setWidth($swItem);
	  
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);
			//表头
			$sheet->setCellValue($cellName.($k),  (string)$header[$_column-1]); 
		}
		$k++;
		$rw=$k-1;
		/*SELECT
		
			$Header=array(  '序号','科目编码','科目名称',"借方发生额","贷方发生额",'方向','余额','确认余额','确认日期','备注','状态' );	
				
						`flg`*/
	while ($row = DB_fetch_array($Result)){

		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);			
			if ($_column==1){
				//  序号列 
			
				$sheet->setCellValue($cellName.($k), $k-$rw); 
			}else{
			    if(round($row['endamount'],POI)>0){
					$jd="借";
				}else{
					$jd="贷";
				}
				
			$flagtxt="待核对";
			if($row['authorised']==1){ 
					$flagtxt="待批准";
			}elseif($row['authorised']==3){ 
				$flagtxt="核准";
			}
				if ($_column==2){					
					$sheet->setCellValue($cellName.($k), (string)$row['account']);
				}elseif ($_column==3){					
					$sheet->setCellValue($cellName.($k), (string)$row['accountname']);
				}elseif ($_column==4){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['debit'],POI));
				}elseif ($_column==5){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['credit'],POI));
				}elseif ($_column==6){					
					$sheet->setCellValue($cellName.($k),$jd);
				}elseif ($_column==7){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['endamount'],POI));
				}elseif ($_column==8){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['checkamount'],POI));
				}elseif ($_column==9){					
					$sheet->setCellValue($cellName.($k), (string)$row['remark']);
				}elseif ($_column==10){					
					$sheet->setCellValue($cellName.($k), (string)substr($row['checkdate'],0,10));
				}elseif ($_column==11){					
					$sheet->setCellValue($cellName.($k),$flagtxt);
				}

			}
		
			if (!empty($row[$cellName-1])) {
				$isNull = false;
			}
		}
		$k++;

	}
	/*
     $amototal=json_decode($titledata['AmountTotal']);
	$sheet->setCellValue("A".($rowCnt+1+$k), ''); 	
	$sheet->setCellValue("B".($rowCnt+1+$k),"");				
	$sheet->setCellValue("C".($rowCnt+1+$k),"累计");
	$sheet->setCellValue("D".($rowCnt+1+$k), (string)$amototal[0]);
	$sheet->setCellValue("E".($rowCnt+1+$k), (string)$amototal[1]);
	$sheet->setCellValue("F".($rowCnt+1+$k), (string)$amototal[2]);
	$sheet->setCellValue("G".($rowCnt+1+$k), (string)$amototal[3]);
	$sheet->setCellValue("H".($rowCnt+1+$k), (string)$amototal[4]);
	*/


	
	//第一种保存方式
	/*	$writer = new Xlsx($spreadsheet);
	//保存的路径可自行设置
	$file_name = '../'.$file_name . ".xlsx";
	$writer->save($file_name);
	///第二种直接页面上显示下载
	*/

	$filename=$titledata['Title']. date('Y-m-d', time()).rand(1000, 9999).".xlsx";
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
	//注意	createWriter($spreadsheet, 'Xls') //第二个参数��字母必须大写
	$writer->save('php://output'); 

}	
?>
