
<?php
/* $Id:TaxInvoiceInquiry.php  $*/

/*
 * @Descripttion: WebERP开发升级  
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-04-27 21:16:58
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-07-19 07:43:53
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
$Title = '税务发票查询';
$ViewTopic = 'MyTools';// Filename's id in ManualContents.php's TOC.
$BookMark = 'InvoiceJournallize';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
include('includes/CurrenciesArray.php');
echo'<script type="text/javascript">


function   radioquery(s)  {
	console.log(s.value);
	var a=document.getElementById("AfterDate");  
	var b=document.getElementById("BeforDate");     
	var p=document.getElementById("SelectPrd");     
	var r=document.getElementById("prdrange"); 

	if (s.value==2){
		a.disabled=true;    
		b.disabled=true;
		r.disabled=false;
		p.disabled=false;
	}else { 
		p.disabled=true;	
		r.disabled=true;			
		b.disabled=false;		
		a.disabled=false;
	}
	var i=document.getElementById("InvNo");
		i.value="";

}
function   disableinv(s)  {
 
  var p=document.getElementById("SelectPrd"); 
  var r=document.getElementById("prdrange");  
  
  var b=document.getElementById("BeforDate");   
  var a=document.getElementById("AfterDate");  

  var r2=document.getElementById("rdoquery2");
  var r1=document.getElementById("rdoquery1");
  console.log(r2.value+"--"+r1.value);
  
  r2.checked=true;
  if (s.value==""){

	 r2.checked=true;
    p.disabled=false;
    r.disabled=false;
    b.disabled=true;
	a.disabled=true;
  }else{
    p.disabled=true;
    r.disabled=true;
    b.disabled=true;
	a.disabled=true;
  }
}

</script>';
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}


	if (!isset($_POST['ERPPrd'])){ 	
		if (isset($_SESSION['SelectInv'])){
		
			$_POST["ERPPrd"]=$_SESSION['SelectInv'][0];
		}else{
			$_POST["ERPPrd"]=$_SESSION['period'];
		}
		
	}
	if (!isset($_POST['UnitsTag'])){ 	
		if (isset($_SESSION['SelectInv'])){
		
			$_POST["UnitsTag"]=$_SESSION['SelectInv'][1];
		}else{
			$_POST["UnitsTag"]=$_SESSION['period'];
		}
		
	}
	if (!isset($_POST['prdrange'])){ 	
		if (isset($_SESSION['SelectInv'])){
		
			$_POST["prdrange"]=$_SESSION['SelectInv'][3];
		}else{
			$_POST["prdrange"]=$_SESSION['period'];
		}
		
	}
	if (!isset($_POST['InvType'])){ 
		if (isset($_SESSION['SelectInv'])){
	
			$_POST["InvType"]=$_SESSION['SelectInv'][2];
		}else{
			$_POST['InvType']=-1;
		}
	}
	if (!isset($_POST['query'])){
		$_POST['query']=2;
	
	}
	if (!isset($_POST['AfterDate']))
	$_POST['AfterDate']=FormatDateForSQL(date('Y-01-01'));//FormatDateForSQL($_SESSION['StockStartRunDate']);
	if (!isset($_POST['BeforDate']))
	$_POST['BeforDate']=FormatDateForSQL(date('Y-m-d'));
	
	if ($_POST['prdrange']==0){
		 $firstprd=$_POST['ERPPrd'];
		 $endprd=$_POST['ERPPrd'];
			
   }elseif ($_POST['prdrange']==3) {
		$firstprd=$_SESSION['janr'];
		$endprd=$_POST['ERPPrd'];	

   }elseif ($_POST['prdrange']==12) {
		$firstprd=$_SESSION['janr'];
		$endprd=$_POST['ERPPrd'];		
   }
 // echo  Date1GreaterThanDate2($_SERVER['StockStartRunDate'],$_SESSION['lastdate']);
    $afterdate=$_SESSION['StockStartRunDate'];
	$InvName=array(-1=>'全部发票',0=>'进项专票',2=>'采购普票',1=>'销项发票',3=>'销项普票',5=>'销项0税率普票');
if (!isset($_POST['ExportExcel'])){
		echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
			'/images/bank.png" title="' .// Icon image.
			$Title.'" /> ' .// Icon title.
			$Title . '</p>';// Page title.
			
	echo '<div class="page_help_text">查询进项、销项发票,使用发票号、日期、类别、金额、用户！</div><br />';
	echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table  class="selection"  ><tr>
			<td>' . _('Select Period To')  . '</td>
			<td >';
			SelectPeriod($_SESSION['period'],$_SESSION['startperiod']);
			
			$rang=array('0'=>'月度', '3'=>'季度','12'=>'本年');//,'24'=>'上年');//,'36'=>'前年');
		
		echo '范围<select name="prdrange" id ="prdrange" size="1">';
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
		echo'</select></td>
			<td></td></tr>';
	echo '<tr>
			<td>查询格式</td>
			<td colspan="2">
				<input type="radio" name="query"  id="rdoquery2" value="2" '.($_POST['query']==2?"checked":"").'  onclick="radioquery(this);" >期间
				<input type="radio" name="query"  id="rdoquery1" value="1"   '.($_POST['query']==1?"checked":"").'  onclick="radioquery(this);" >日期 
			</td>
			</tr>';
	echo'<tr>
			<td>选择发票日期</td>
			<td >
				<input type="date"   alt="" min="'.$afterdate.'" max="'.date('Y-m-d').'"  name="AfterDate" id= "AfterDate" maxlength="10" size="11" value="' . $_POST['AfterDate'] . '" "/>
				<input type="date"   alt="" min="'.$afterdate.'" max="'.date('Y-m-d').'"  name="BeforDate" 	id="BeforDate" maxlength="10" size="11" value="' .$_POST['BeforDate']. '"   />
				</td>
			<td></td>
			</tr>';
		echo '<tr>
				<td>单元分组</td>
				<td colspan="2">';
				SelectUnitsTag(2);		
		echo'</td>
			</tr>';
		echo'<tr>
				<td>发票种类</td>
				<td colspan="2"><select name="InvType" id="InvType">';		
			foreach($InvName as $key=>$value){
				if (isset($_POST['InvType']) and ($_POST['InvType']==$key)){
					echo '<option selected="selected" value="' ;
				}else {
					echo '<option value ="';
				}
					echo   $key.'">'.$value.'</option>';
			}				
		echo'</select>
			</td></tr>';
		echo '<tr>
				<td> 发票号码:</td>	
				<td colspan="2">
					<input type="text"  name="InvNo"  id="InvNo" size="25" maxlength="30"  value="'.$_POST['InvNo'].'"  onchange="disableinv(this);" pattern="((\d{1,15}-\d{1,15})?|(\d{1,15},?\d{1,15}){0,10}|\s)"  placeholder="输入单张发票号码或多张,使用分隔符 -或,"  />
					</td>
			</tr>
			<tr>
				<td> 查询金额范围:</td>
				<td colspan="2" ><input type="text"  name="SearchAmo" id="SearchAmo" size="25" maxlength="30"  placeholder="输入金额,贷方为- 不同金额用,间隔或用~做为范围"  value="'.$_POST['SearchAmo'].'"  pattern="^((-?\d{1,10})(.\d{1,2})?)~((-?\d{1,10})(.\d{1,2})?)?|(((-?\d{1,10})(.\d{1,2})?),?)*"    /></td>
			</tr>
			<tr>
				<td>客户名:</td>
				<td colspan="2" ><input type="text"  name="Customer" id="Customer" size="25" maxlength="30" value="'.$_POST['Customer'].'"  pattern="^[\u0391-\uFFE5\s]+$" placeholder="输入汉字、空格" /></td>
			</tr>
		
			</table>';
	echo'<br><div class="centre">		    
				<input type="submit" name="Search" value="发票查询">';
				//if (isset($_GET['Action'])){
					echo '<input type="submit" name="ExportExcel" value="导出Excel" />';
				//}
		echo '</div>';
}
	$tag=$_POST['UnitsTag'];

	if ($_POST['prdrange']==0){	//本月	
		$sql="SELECT	invno,
						invtype,
						a.tag,
						a.transno,
					     c.typeno,
						period,
						invdate,
						a.amount,
						tax,
						currcode,
						toregisterno ,
						toaccount,
						toname,
						custname,
						remark,
						a.flg
				FROM	invoicetrans a
					LEFT JOIN `custname_reg_sub` b ON b.registerno=a.toregisterno 
					LEFT JOIN gltypenotag c ON a.period=c.periodno AND a.transno=c.transno				
				WHERE  a.tag = " . $tag .  " " ;
									 
		if ($_POST['InvType']!=-1){
			$sql.=" AND  invtype=" . $_POST['InvType'] ;
		}	
        if (isset($_POST['InvNo'])&& strlen($_POST['InvNo'])>2){		
		
				if ( strpos($_POST['InvNo'],'-')>0 ){
						$InvNoarr=explode('-', $_POST['InvNo']);					
					if ((int)$InvNoarr[0] <= (int)$InvNoarr[1]){				
						$n=(int)$InvNoarr[0];
						$p=(int)$InvNoarr[1];
					}else {
						$p=(int)$InvNoarr[0];
						$n=(int)$InvNoarr[1];						
					}	
					for ($i = $n; $i <= $p; $i++) {					
						$InvNoStr.='"'.$i.'",';
					} 					
					$InvNoStr=substr($InvNoStr,0, -1);
				}else{
					$InvNoStr=$_POST['InvNo'];							 		
				}
				$sql.=" AND invno  IN ( ".$InvNoStr." )  ";    	
		}else{
			if ($_POST['query']==2){
			
				$sql.=" AND period>=" . $firstprd. "  AND period<=" . $endprd. "";
			}else{
				$sql.=" AND invdate>='".$_POST['AfterDate']."' AND invdate<='".$_POST['BeforDate']."'" ;
			}
		}
		if (isset($_POST['SearchAmo'])&& round((float)($_POST['SearchAmo']),POI)!=0){	
			$sql.=" AND (amount+tax)>=" . round((float)($_POST['SearchAmo']),POI)." ";
		}
		if ($_POST['Customer']!=''){
			$str=str_replace(' ', '%',  trim($_POST['Customer']));
			
		
			$SearchStr = '%'.$str .'%';
			$sql.= ' AND ( toname  like "'.$SearchStr.'"  OR custname  LIKE  "'.$SearchStr.'" )';
		}
		$sql.="	ORDER BY invtype, a.tax/a.amount,invdate ";  
	    // echo $sql;
	}elseif($_POST['prdrange']==12||$_POST['prdrange']==3){
		//年度度查询
		$sql="SELECT invtype,  period, SUM(amount) amount, SUM(tax) tax 
		       FROM invoicetrans 
		        WHERE tag=" . $tag . " 
					AND flg=0  AND period>=" . $firstprd. " 
					AND period<=" . $endprd. "  
					GROUP BY invtype,period ORDER BY invtype,period";
	}
	
	$result = DB_query($sql, $ErrMsg);

if (isset($_POST['ExportExcel'])){

		$options = array("print"=>true);//,"setWidth"=>$setWidth);
		$TitleData=array("Title"=>'发票清单',"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal));	
	
		 $Header=array( '序号', '发票号码', '发票日期', '会计期间','发票类别', '金额', '税金', '税率', '物品名称', '规格','数量','单位','客户名','凭证号' );	
		DB_data_seek($result,0);	  
		ExportExcel($result,$Header,$TitleData,$options);
}
	$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
	//$Result = DB_query($SQL, $ErrMsg);

	$ListCount=DB_num_rows($result);
	//echo $_POST['prdrange'];
if (empty($ListCount)){	

	prnMsg("你选择的查询条件，没有查询到内容！","info");
	include ('includes/footer.php');

	exit;
}	

	echo'<input type="hidden" name="CurrencyDefault" id="CurrencyDefault" value="'.	$_SESSION['CompanyRecord'][$tag]['currencydefault'].'">	';
if (isset($_POST['Search'])	OR  isset($_POST['Go'])	OR isset($_POST['Next']) 
	OR isset($_POST['Previous']) ||isset( $_SESSION['SelectInv'])) {

	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}

	

		/*
		// $_SESSION['DisplayRecordsMax']=15;
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
	*/


	$i=0;
	
		//本月解析
	if ($_POST['prdrange']==0){//本月
			echo '<table cellpadding="1" class="selection">
				<tr>
				<th class="ascending">序号</th>	
				<th class="ascending" >发票号</th>	
				<th class="ascending">' . _('Date') . '</th>	
				<th class="ascending">会计期间</th>			
				<th class="ascending">发票类别</th>
				<th class="ascending">金额</th>
				<th class="ascending">税金</th>				
				<th class="ascending">税率</th>
				<th >物品名称</th>
				<th >规格</th>   
				<th >数量</th>
				<th >单位</th>
				<th class="ascending">客户名</th>							
				<th >凭证号</th>			
			
			</tr>';	
			$k = 0; //row colour counter
			$RowIndex = 0;
			$rw=1;
			$TransType = 0;//科目解析标记 0 1科目解析成功 2凭证已完成 -1 -2没有科目新公司
			$TotalAmount=0;
			$TotalTax=0;
			$TaxRate=-1;
			$TotalTypeAmo=0;
			$TotalTypeTax=0;
			$amo_jx=0;
			$tax_jx=0;
			$tax_xx=0;
			$amo_xx=0;
			$InvType=-1;
			$TranNoGL=0;
			$TransNoArr=array();	
			//---------添清除对应科目和选择代码
			$SQL="SELECT currabrev, round(rate,decimalplaces) rate FROM currencies";
			$Result=DB_query($SQL);		
			$CurrRate=array();
			while ($row=DB_fetch_array($Result)) {	
				$CurrRate[$row['currabrev']]=$row['rate'];
			}	
		
			$TotalTypeAmo=0;
			$TotalTypeTax=0;
			DB_data_seek($result,0);
		
			while ($myrow=DB_fetch_array($result)) {	
				$TranMsg='';
				$msg='';
				$prdgl='';
				$subject='';
				$subname='';
				$AubAnalysis=0;	
				$RegID=0;	
				$TranNoGL=0;		
				if ($myrow['transno']>0){//已经录入凭证并核对
					$TransType =2;
					$TranNoGL=$myrow['transno'];
					$TranMsg=GetTransContent($myrow['period'],$myrow['transno']);
				}
				if (empty($myrow['toname'])){
					if (!isset($SearchCust[$myrow['toregisterno']])){
						$SQL="SELECT `regid`, `registerno`, `custname` FROM `custname_reg_sub` WHERE registerno='".$myrow['toregisterno']."'";
						$Result=DB_query($SQL);
						$ROW=DB_fetch_assoc($Result);
						if (!empty($ROW['custname'])){
							$SearchCust[$myrow['toregisterno']]=$ROW['custname'];
							
						}
					}
					$custname=$SearchCust[$myrow['toregisterno']];
				}else{
					$custname=$myrow['toname'];
				}
			
				//摘要自动写入 缺少外币写入
				if ($myrow['invtype']==0){
						
					if ($InvCurrType==5){
						//代开发票
						$msg=$myrow['toname'].'`采购代开专票号;'.$myrow['invno'].";";
					}else{
						$msg=$myrow['toname'].'`采购专票号;'.$myrow['invno'].";";
					}
				}elseif ($myrow['invtype']==1){
					$msg=$myrow['toname'].'`销售专票号;'.$myrow['invno'].";";
				}elseif ($myrow['invtype']==3){
					$msg=$myrow['toname'].'`销售普票号;'.$myrow['invno'].";";
				}
				//按税率合	
					if (( ($InvType!=(int)$myrow['invtype']&&(int)$TaxRate==(int)round(100*(float)$myrow['tax']/(float)$myrow['amount'],0) )||(int)$TaxRate!=(int)round(100*(float)$myrow['tax']/(float)$myrow['amount'],0) )&& $TaxRate>=0 ) {
						echo'<tr>
								<th ></th>			
								<th  colspan="4" >' . $TaxRate . '%税率合计</th>
								<th >'.abs($TotalTypeAmo).'</th>
								<th >'.abs($TotalTypeTax).'</th>				
								<th colspan="8" ></th>				
							</tr>';		
								
							$TotalTypeAmo=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
							$TotalTypeTax=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);						
							$TaxRate=round(100*(float)$myrow['tax']/(float)$myrow['amount'],0);	
					
					}else{
						if ($TaxRate==-1){
							$TaxRate=round(100*(float)$myrow['tax']/(float)$myrow['amount'],0);		
						}
						$TotalTypeAmo+=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
						$TotalTypeTax+=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);
					}
					if ((int)$InvType!=(int)$myrow['invtype'] && $InvType!=-1){  //进项转换销项  
				
						echo'<tr>
								<th ></th>			
								<th  colspan="4" >' . $TaxRate . '%'. $InvName[$InvType].'合计</th>
								<th >'.abs($TotalAmount).'</th>
								<th >'.abs($TotalTax).'</th>				
								<th colspan="8" ></th>				
							</tr>';		
							if ($myrow['invtype']!=0){
								$amo_xx=$TotalAmount;
								$tax_xx=$TotalTax;
							}else{
								$amo_jx=$TotalAmount;
								$tax_jx=$TotalTax;
							}		
							$InvType=$myrow['invtype'];	   
							$TotalAmount=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
							$TotalTax=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);
							//$TaxRate=round(100*(float)$myrow['tax']/(float)$myrow['amount'],0);	
						
					}else{
						if ($InvType==-1){
							$InvType=$myrow['invtype'];
						}
							$TotalAmount+=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
							$TotalTax+=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);
					}
			
				if ($TransType ==-2 &&$TranNoGL==0){
					echo '<tr style="background: #ecc;">'	;
				}elseif ($TransType ==-1){
					echo '<tr style="background: #acc;">'	;
				}else{
					if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}
				}				
				echo'<td>'.$rw.'</td>
					<td>'. $myrow['invno'].'</td>
					<td >'.$myrow['invdate'].'</td>
					<td >'.substr(PeriodGetDate($myrow['period']),0,7).'</td>
					<td >'.$InvName[$myrow['invtype']].'				
					</td>
					<td class="number">'.locale_number_format($myrow['amount'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']).'</td>
					<td class="number">'.locale_number_format($myrow['tax'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']).'</td>
					<td >'.($myrow['tax']!=0?round(100*$myrow['tax']/$myrow['amount'],0).'%':'0').'</td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td>'.$custname.'</td>';
				$URL_GLDetail = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['period'].'^'.$TranNoGL;
				if ($TranNoGL==0){
					echo'<td  title="注册码['.$myrow['toregisterno'].']'.$myrow['toname'].'"  >无</td>	';
				
				}else{
				
					echo'<td >
						<a href="'.$URL_GLDetail.'" title="'.$TranMsg.'" target="_blank" >['.$TranNoGL.']'.$_SESSION['tagref'][$myrow['tag']][2].$myrow['typeno'].'</a>
					</td>';			
				}
				
												
				echo'</tr>';
						
				$RowIndex++;
				$rw++;
			}//while
			echo'<tr>
					<th ></th>			
					<th  colspan="3" >' . $InvName[$InvType] . '合计</th>
					<th >'.abs($TotalTypeAmo).'</th>
					<th >'.abs($TotalTypeTax).'</th>				
					<th colspan="8" ></th>				
				</tr>';	
		
			echo'<th colspan="8" ></th>				
					</tr>';		
			echo '</table>';

		//当结束
	}elseif ($_POST['prdrange']==3){
			//季度计
		echo '<table cellpadding="2" class="selection">';
		$invtyp=-1;	
		$j=0;
		$TotalAmount=0;	
		$TotalTax=0;	
		$TotalAmountJ=0;
		$TotalTaxJ=0;
		$r=0;
		$TotalTypeAmo=0;
		$TotalTypeTax=0;
		$TotalTypeAmoJ=0;
		$TotalTypeTaxJ=0;
		while ($myrow=DB_fetch_array($result)) {
			if ($invtyp!=-1&&$invtyp!=$myrow['invtype']){
				if ($invtyp==0){
					echo'<th ></th>			
								<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
								<th >'.locale_number_format($TotalTypeAmo,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
								<th >'.locale_number_format($TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
								<th >'.locale_number_format($TotalTypeAmo+$TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
							</tr>';			
				}
				if ($invtyp!=0){
				
					echo'<th ></th>			
								<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
								<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
								<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
								<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
							</tr>';		
							$tax_xx=$TotalAmount;
							$amo_xx=$TotalTax;			
			}
			$r=0;		
				echo'<tr>
				<th class="ascending">序号</th>			
				<th class="ascending">月份</th>	
				<th >摘要</th>   
				<th >借/贷</th>
				<th class="ascending">金额</th>
				<th class="ascending">税金</th>					
				<th class="ascending">合计</th>	
			</tr>';
			}
			//$ymstr=substr($_SESSION['lastdate'],0,5).($_SESSION['period']-substr($_SESSION['lastdate'],5,2)+$myrow['period']);
			if ($myrow['invtype']==0){//进项合计
				
				$TotalTypeAmo+=$myrow['amount'];
				$TotalTypeTax+=$myrow['tax'];

				$TotalTypeAmoJ+=$myrow['amount'];
				$TotalTypeTaxJ+=$myrow['tax'];
		}else{	//销项合计
			$TotalAmount+=$myrow['amount'];
			$TotalTax+=$myrow['tax'];

			$TotalAmountJ+=$myrow['amount'];
			$TotalTaxJ+=$myrow['tax'];
		}
			if ((($myrow['period']-$_SESSION['janr']==2)||($myrow['period']-$_SESSION['janr']==5)||($myrow['period']-$_SESSION['janr']==8)||($myrow['period']-$_SESSION['janr']==11))&&($myrow['invtype']==1||$myrow['invtype']==3)){
			
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				printf('<td>%s</td>					
						<td>%s</td>
						<td >%s</td>
						<td >%s</td>					
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>					
					</tr>',
				$r+1,	
							
				(round(($myrow['period']-$_SESSION['janr']+1)/3,2)).'季度',
				'销项票本月合计',
				'贷',
				locale_number_format($TotalAmountJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
				locale_number_format($TotalTaxJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
				locale_number_format(($TotalAmountJ+$TotalTaxJ),$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']));	
				$TotalAmountJ=0;
				$TotalTaxJ=0;
				$r++;
			}
			if ((($myrow['period']-$_SESSION['janr']==2)||($myrow['period']-$_SESSION['janr']==5)||($myrow['period']-$_SESSION['janr']==8)||($myrow['period']-$_SESSION['janr']==11))&&$myrow['invtype']==0){
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			printf('<td>%s</td>					
					<td>%s</td>
					<td >%s</td>
					<td >%s</td>				
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>					
				</tr>',
			$r+1,	
						
			round(($myrow['period']-$_SESSION['janr']+1)/3).'季度',
			'进项票本月合计',
			'借',
			locale_number_format($TotalTypeAmoJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
			locale_number_format($TotalTypeTaxJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
			locale_number_format($TotalTypeAmoJ+$TotalTypeTaxJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']));	
			$TotalTypeAmoJ=0;
			$TotalTypeTaxJ=0;
			$r++;
			}
		
			$invtyp=$myrow['invtype'];
			$j++;
		}
			echo'<tr>
			<th ></th>			
			<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
			<th >'.locale_number_format($amo_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
			<th >'.locale_number_format($tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
			<th >'.locale_number_format($amo_xx+$tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
		</tr>';	
			echo'<tr>
			<th  class="ascending"></th>			
			<th  colspan="3" >销项累计</th>
			<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
			<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
			<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
			</tr>';
		
		echo '</table>';
	}elseif ($_POST['prdrange']==12){
		//年度询
		echo '<table cellpadding="2" class="selection">';

			$invtyp=-1;
			while ($myrow=DB_fetch_array($result)) {
				if ($invtyp!=-1&&$invtyp!=$myrow['invtype']){
				
					if ($invtyp==0){
						$r=0;
						echo'<th ></th>			
									<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
									<th >'.locale_number_format($TotalTypeAmo,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
									<th >'.locale_number_format($TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
									<th >'.locale_number_format($TotalTypeAmo+$TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
								</tr>';			
					}
					if ($invtyp!=0){				
						echo'<th ></th>			
									<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
									<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
									<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
									<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
								</tr>';	
								$tax_xx=$TotalAmount;
								$amo_xx=$TotalTax;		
					}			
					echo'<tr>
					<th class="ascending">序号</th>			
					<th class="ascending">月份</th>	
					<th >摘要</th>   
					<th >借/贷</th>
					<th class="ascending">金额</th>
					<th class="ascending">税金</th>					
					<th class="ascending">合计</th>	
				</tr>';
				}
				$ymstr=substr($_SESSION['lastdate'],0,5).($myrow['period']-$_SESSION['period']+substr($_SESSION['lastdate'],5,2));
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
				printf('<td>%s</td>					
						<td>%s</td>
						<td >%s</td>
						<td >%s</td>					
						<td class="number">%s</td>
						<td class="number">%s</td>
						<td class="number">%s</td>					
					</tr>',
				$r+1,						
				$ymstr,
				($myrow['invtype']==0?'进项票':'销项票').'本月合计',
				($myrow['invtype']==0?'贷':'借'),
				locale_number_format($myrow['amount'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
				locale_number_format($myrow['tax'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
				locale_number_format($myrow['tax']+$myrow['amount'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']));	
				if ($myrow['invtype']!=0){	
					$TotalAmount+=$myrow['amount'];
					$TotalTax+=$myrow['tax'];
				}else{
					$TotalTypeAmo+=$myrow['amount'];
					$TotalTypeTax+=$myrow['tax'];
					$tax_xx+=$myrow['amount'];
					$amo_xx+=$myrow['tax'];		
				}
				
				$r++;
				$invtyp=$myrow['invtype'];
		
			}
			echo'<tr>
			<th ></th>			
			<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
			<th >'.locale_number_format($amo_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
			<th >'.locale_number_format($tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
			<th >'.locale_number_format($amo_xx+$tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
		</tr>';	
				echo'<tr>
					<th ></th>			
					<th  colspan="3" >'. $InvName[$invtyp] .'累计</th>
					<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
					<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
					<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
				</tr>';	    
		echo '</table>';

	}

}//endif 293

echo ' </form>';

include ('includes/footer.php');
 
/**
   * Excel导出，TODO 可继续优化
   *
   * @param array  $Result
   * @param array  $header   导出文件名称
   * @param array  $TitleData "Title"=>'客户名单',
   * 						 
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
   *                           array  format      设格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置后则文件保存到服务器，不通过浏览器下载
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
		$InvType=array(0=>'进项专票',2=>'采购普票',1=>'销项发票',3=>'销项普票',5=>'销项0税率普票');
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

		//将A1单元格设置成粗体，黑，10号字
        $sheet->getStyle('A1')->getFont()->setBold(true)->setName('黑体')->setSize(14);

		$sheet->setCellValue('A1',  (string)$titledata['Title']); 
		$sheet->setCellValue('D2',  (string)$titledata['TitleDate']); 
		$sheet->setCellValue('A'.$k, "公司名称:". (string)$titledata['coyname']); 
		$sheet->setCellValue(Coordinate::stringFromColumnIndex($columnCnt).($k),  "单位：".(string)$titledata['Units']); 
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
		$k++;
		$activeSheet->getStyle('A'.(int)($k).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+$rowCnt))->applyFromArray($styleArray);
		  /* 设置宽度 */
		$activeSheet->getColumnDimension('B')->setWidth(15);		
		$activeSheet->getColumnDimension('C')->setWidth(10);
		$activeSheet->getColumnDimension('D')->setWidth(10);
		$activeSheet->getColumnDimension('E')->setWidth(10);
		$activeSheet->getColumnDimension('M')->setWidth(30);
		$activeSheet->getColumnDimension('N')->setWidth(10);
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
	while ($row = DB_fetch_array($Result)){
		//$custname=$row['toname'];
		if (empty($row['toname'])){
			if (!isset($SearchCust[$row['registerno']])){
				$SQL="SELECT `regid`, `registerno`, `custname` FROM `custname_reg_sub` WHERE registerno='".$row['toregisterno']."'";
				$custResult=DB_query($SQL);
				$ROW=DB_fetch_assoc($custResult);
				if (!empty($ROW['custname'])){
					$SearchCust[$row['toregisterno']]=$ROW['custname'];
					
				}
			}
			$custname=$SearchCust[$row['toregisterno']];
		}else{
			$custname=$row['toname'];
		}
		if ($row['period']!=$prd){
			$invdate=substr(PeriodGetDate($row['period']),0,7);
			$prd=$row['period'];
		}
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);			
			if ($_column==1){
				//  序号列 
			
				$sheet->setCellValue($cellName.($k), $k-$rw); 
			}else{
			// invno, invtype, a.tag, a.transno, c.typeno, period, invdate, a.amount, tax, currcode, toregisterno registerno, toaccount, toname, custname, remark, a.flg
			//	 $Header=array( '序号', '发票号码', '发票日期', '会计期间','发票类别', '金额', '税金', '税率', '物品名称', '规格','数量','单位','客户名','凭证号' );	
				if ($_column==2){					
					$sheet->setCellValue($cellName.($k), (string)$row['invno']);
				}elseif ($_column==3){					
					$sheet->setCellValue($cellName.($k), (string)$row['invdate']);
				}elseif ($_column==4){					
					$sheet->setCellValue($cellName.($k), $invdate);
				}elseif ($_column==5){					
					$sheet->setCellValue($cellName.($k), $InvType[$row['invtype']]);
				}elseif ($_column==6){					
					$sheet->setCellValue($cellName.($k), (float)$row['amount']);
				}elseif ($_column==7){					
					$sheet->setCellValue($cellName.($k), (float)$row['tax']);
				}elseif ($_column==8){					
					$sheet->setCellValue($cellName.($k), (string)((round($row['tax']/$row['amount']*100))."%"));
				}elseif ($_column==13){					
					$sheet->setCellValue($cellName.($k), (string)$custname);
				}elseif ($_column==14){	
					if ($row['transno']==0){
						$GLTransNo='';
					}else{
						$GLTransNo=$row['transno'].$_SESSION['tagref'][$row['tag']][2].$row['typeno'];
					}			
					$sheet->setCellValue($cellName.($k), (string)$GLTransNo);
				}else{					
					$sheet->setCellValue($cellName.($k), '');
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
	//注意	createWriter($spreadsheet, 'Xls') //第二个参数首字母必须大写
	$writer->save('php://output'); 

}	
/**
   *读取会计凭证，转换为符串
   * @param array $period
   *              $transno
   *    *    *
   * @return String
   * @throws Exception
   * 错误返-1
   */
function  GetTransContent($period,$transno){

	$SQL="SELECT transno, 
				 trandate,
				 typeno,
				 gltrans.tag,
				 account,
				 accountname,
				 narrative,
				 amount,
				 flg,
				 posted
			FROM gltrans
			LEFT JOIN chartmaster ON gltrans.account=chartmaster.accountcode
			WHERE periodno=".$period." 
			  AND transno=".$transno;
	
	$Result=DB_query($SQL);
	$Header='会计凭证';
	$narr='';
	$TransDate='';	
	$ToType=2;
	$tranmsg='';
	$mlen=0;

	while($Row=DB_fetch_array($Result)){
	
		
		if($Row['flg']==1){//据为
			if($Row['amount']>0){
				$jdstr="借 ".$Row['amount'];
			}else{
				$jdstr="贷 ".(-$Row['amount']);
			}
		}else{
			if($Row['amount']>0){
				$jdstr="贷 ".(-$Row['amount']);
			}else{
				$jdstr="借 ".$Row['amount']; 
			}
		}
		if ($narr==''){		
			$TransDate=$Row['trandate'];
			$narr=$Row['narrative'];
		}
		if (strlen($Row['account'].$Row['accountname'])>$mlen){
             $mlen=strlen($Row['account'].$Row['accountname']);
		}
		$TransRow[]=$Row['account'].'&nbsp;'.$Row['accountname'];
		if (strlen($jdstr)>$nlen){
			$nlen=strlen($jdstr);
		}
		$TransAmo[]=$jdstr;
	}

	$TranCont= $Header.$TransDate.'&nbsp;记字'.$transno.'&#10;';
	$nbsp='';
	foreach($TransRow as $key=>$val){
		$len=$mlen-strlen($val)+$nlen-strlen($TransAmo[$key])+12;
		for($i=0;$i<$len;$i++){
			$nbsp.='&nbsp;';
		}
		$TranCont.=$val.$nbsp.'&nbsp;'.$TransAmo[$key].'&#10;';
		$nbsp='';
	}
	 $TranCont.='摘&nbsp;&nbsp;要:'.$narr.'"';
     return  $TranCont;
    
}
/*--------------设计概要--------------

*/
?>
