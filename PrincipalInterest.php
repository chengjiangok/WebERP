
<?php

/* $Id: Z_ChangeStockQuantity $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-08-09 05:40:51 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-08-09 08:43:04
 */
//namespace Excel;
//require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';  

/*
class CustomValueBinder extends DefaultValueBinder {
    public static function dataTypeForValue($pValue) { //只重写dataTypeForValue方法，去掉一些不必要的判断
        if (is_null($pValue)) {
            return DataType::TYPE_NULL;
        } elseif ($pValue instanceof \PhpOffice\PhpSpreadsheet\RichText\RichText) {
            return DataType::TYPE_INLINE;
        } elseif ($pValue[0] === '=' && strlen($pValue) > 1) {
            return DataType::TYPE_FORMULA;
        } elseif (is_bool($pValue)) {
            return DataType::TYPE_BOOL;
        } elseif (is_float($pValue) || is_int($pValue)) {
            return DataType::TYPE_NUMERIC;
        }
        return DataType::TYPE_STRING;
    }
}
*/
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup; 
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
//, DataType};
use PhpOffice\PhpSpreadsheet\Cell\DataType;
//
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

//PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \Excel\CustomValueBinder()); //在创建spreadsheet对象前设置
//原文链接：https://blog.csdn.net/x554462/java/article/details/89192627
include ('includes/session.php');
$Title ='本金利息计算';
echo'<script type="text/javascript">

function del(){
	if(confirm("确定要删除吗？")){
	alert("删除成��！");
	return true;
	}else{
	return false;
}


	function onLocations(s){      
	
		var jsn=document.getElementById("StockCategoryJson").value;	
	
		var selectobj=document.getElementById("Categories");
		var obj= JSON.parse(jsn);
		var temp = []; 	
		var objloc=[];
		selectobj.options.length=0; 
				
		console.log(jsn);
			for(var i=0; i<obj.length; i++)  
			{ 
				temp[i]= (function(n){				  
					if (Number(obj[n].loc)==s.value){		
					
						selectobj.options.add(new Option(obj[n].id+"-"+obj[n].des,obj[n].id));
						objloc[n]={
							id:obj[n].id,
							des:obj[n].des
						}
					}
				})(i);  
			}
			
			var jsonloc= JSON.stringify(objloc);	
			console.log(jsonloc);
				
			
	}
</script>';
include('includes/header.php');
if(!isset($_POST['ExportExcel'])) {


	echo  ' <input type="hidden" id="StockCategoryJson" name="StockCategoryJson" value=' . $StockCategoryJson . ' />';
	echo '<p class="page_title_text">
	      <img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (!isset($_POST['Search'])	AND !isset($_POST['Go'])	AND !isset($_POST['Next'])	AND !isset($_POST['Previous'])){ 
		echo '<div class="page_help_text">
				功能简介：<br>
					</div>';
	}
	$CustFlag=array('0'=>'多宝','1'=>'张健');

echo '<table class="selection">
		<tr>
			<th colspan="3">' . _('Selection Criteria') . '</th>
		</tr>';
	echo'<tr>
			<td>选择类别:
			    <select name="CustType" size="1" style="width:80px" >';
	if (!isset($_POST['CustType'])){
		echo '<option selected="selected" value="0">' . _('All') . '</option>';
		$_POST['CustType'] ='All';
	} else {
		echo '<option value="0">' . _('All') . '</option>';
	}
		foreach($CustFlag as $key=>$val){			
			if (isset($_POST['CustType'])&& $val==$_POST['CustType']){
				echo '<option selected="True" value ="';
			}else{
				echo '<option value ="';
			}
				echo $val.'">'.$val.'</option>';		
		}		
	echo'</select>
		</td></tr>';
		//now lets add the time period option
	if (!isset($_POST['ToDate'])) {
		$_POST['ToDate'] = date('Y-m-d');;
	}
	if (!isset($_POST['FromDay'])) {
		$_POST['FromDay'] = date("d");
	}
echo '<tr>
	<td>' . _('Date From') . '<input type="text"  name="FromDay"  maxlength="10" size="10" value="' . $_POST['FromDay'] .'" /></td> 
	<td></td>
	  </tr>';
//' . _('Date To') . '<input type="date" alt="' . $_SESSION['DefaultDateFormat'] . '" name="ToDate" maxlength="10" size="11" value="' . $_POST['ToDate'] . '" />
echo '<tr>
	  <td>查询选择:</td>
	 <td>
	  <input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' />选择日
	  <input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />其他

</td>
</tr>

';	
echo '<tr>
<td>输入姓名或部分:</td>';
if (!isset($_POST['Keywords'])) {
$_POST['Keywords'] = '';
}
echo '<td><input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" /></td>';		
echo '</tr>';
/*
<tr>

<td>输入银行账号或银行:</td>';
if (!isset($_POST['StockCode'])) {
$_POST['StockCode'] = '';
}
echo '<td><input type="text" autofocus="autofocus" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" /></td></tr>';

	echo'<td></td>';
*/
echo '</tr> </table>
	
	<br/>
	<div class="centre">
		<input type="submit" name="PaySearch" value="每日付款查询" />
		<input type="submit" name="Search" value="' . _('Search Now') . '" />
		<input type="submit" name="PayPlan" value="付款计划查询" />';
	
	if (isset($_POST['PaySearch']))
		echo'<br/><br/><input type="submit" name="UpdateSave" value="付款保存" onclick="return confirm(\'你确定已经付款了吗，提交后录入数据表\');" />';
	if (isset($_POST['PaySearch'])){
		echo '	<input type="submit" name="ExportExcel" value="付款导出Excel" />
				<input type="submit" name="ExportExcel" value="生成付款模板" onclick="return confirm(\'你确定已经付款了吗，提交后录入数据表,并生成付款模板\');" />';
	}	
	if (isset($_POST['Search'])){
		echo '	<input type="submit" name="ExportExcel" value="查询导出Excel" />';
	}	
	if (isset($_POST['PayPlan'])){
		echo '	<input type="submit" name="ExportExcel" value="计划导出Excel" />';
	}	
	echo'</div><br/><br/>';
}
   $sql="SELECT id,`custtype`, `orderno`, `name`, `custid`, `amount`, `OutstandingAmount`,PayPrincipal2006Total, `periodDate`, `StartDate`,EndDate  ,`InterestRate`, `InterestMthTotal` ,
         banknumber,bank,`PayInterest2002`, `PayInterest2003`, `PayInterest2004`, `PayInterest2005`,
		 `PayPrincipal2006`, `PayPrincipal2007`, `PayPrincipal2008`, `PayPrincipal2009`, `PayPrincipal2010`, `PayPrincipal2011`, `PayPrincipal2012`, `PayPrincipal2101`, `PayPrincipal2102`, `PayPrincipal2103`, `PayPrincipal2104`, `PayPrincipal2105`,
		  `PayInterest2006`, `PayInterest2007`, `PayInterest2008`, `PayInterest2009`, `PayInterest2010`, `PayInterest2011`, `PayInterest2012`, `PayInterest2101`, `PayInterest2102`, `PayInterest2103`, `PayInterest2104`, `PayInterest2105`

		 FROM `principalinterest`  WHERE 1"; 
	if (isset($_POST['CustType']) && $_POST['CustType']!="0" ){	
		$sql.=" AND custtype='".$_POST['CustType']."' ";
	}
	if (isset($_POST['PaySearch'])||isset($_POST['UpdateSave'])||$_POST['ExportExcel']=="付款导出Excel"){
		if ($_POST['queryad'] == 0){
			if (isset($_POST['FromDay']) && $_POST['FromDay']>=1 &&$_POST['FromDay']<=31 ){	
				$sql.=" AND Day(EndDate)='".$_POST['FromDay']."' ";
			}
		}else{
			if (isset($_POST['Keywords']) && strlen($_POST['Keywords'])>=1){
				$sql.=" AND name LIKE '%".$_POST['Keywords']."%' ";
			}
		}
	}
	$sql.="     ORDER BY custtype,custid";
	$result=DB_query($sql);
	//echo $sql;
	if (isset($_GET['Edit'])){
		$CustData=explode('`',$_GET['Edit']);
		if (!isset( $_POST['BankNumber'])){
			$_POST['BankNumber']=$CustData[2];
		}
		if (!isset( $_POST['Bank'])){
			$_POST['Bank']=$CustData[3];
		}
		if (!isset( $_POST['BankCode'])){
			$_POST['BankCode']=$CustData[4];
		}
		echo '<table class="selection">
					<tr>
						<th colspan="2">你选择的客户:'.$CustData[0].' '. $CustData[1].'</th>
					</tr>
				
				<tr>
					<td>本金:</td>
					<td><input type="text" name="Name" value="' . $_POST['Name'] . '" size="10" maxlength="15" /></td>
				</tr>
				<tr>
				<td>利率:</td>
				<td><input type="text" name="Name" value="' . $_POST['Name'] . '" size="5" maxlength="10" /></td>
			</tr>
			<tr>
			<td>本金:</td>
			<td><input type="text" name="Name" value="' . $_POST['Name'] . '" size="10" maxlength="20" /></td>
		</tr>
		<tr>
				<td>账户:</td>
				<td><input type="text" name="BankNumber" value="' . $_POST['BankNumber'] . '" size="20" maxlength="25" /></td>
			</tr>
			<tr>
			<td>开户行:</td>
			<td><input type="text" name="Bank" value="' . $_POST['Bank'] . '" size="20" maxlength="25" /></td>
			</tr>
			<tr>
			<td>开户行号:</td>
			<td><input type="text" name="BankCode" value="' . $_POST['Bankcode'] . '" size="5" maxlength="10" /></td>
			</tr>';
			echo '<tr>
				<td>查询选择:</td>
				<td>
				<input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' />停止
				<input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />其他
	
			</td>
			</tr>';	
	
		echo '</table>';
		echo'<div class="centre">
			
				<input type="submit" name="Save" value="确认保存" />';
			
	
			echo'</div><br/><br/>';
			exit;
	}
if (isset($_POST['ExportExcel'])){

	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	$TitleData=array("Title"=>'付款本金利息表',"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>1,"AmountTotal"=>json_encode($AmoTotal));	
	if ($_POST['ExportExcel']=="生成付款模板"){
		$Header=array('收款账号',	
						'收款方户名',		
						'行内行外(行内0,行外1)',
						'收款账户开户行行号(行内转账无需输入)',
						'金额');
						$Insert=0;
						$PayMode=[];
						foreach ($_POST as $key => $value) {
							//prnMsg($key);
							if (mb_strpos($key,'CustID')!==false) {				      
								$LineID = mb_substr($key,mb_strpos($key,'CustID')+6);
							
								if ($_POST['Check'.$LineID]) {
									
								
								
									$PayPr=filter_number_format($_POST['PayPrincipal'.$LineID],POI);
									$PayIn=filter_number_format($_POST['PayInterest'.$LineID],POI);
									$BankNumber=$_POST['BankNumber'.$LineID];
									$Bank=$_POST['Bank'.$LineID];
									$BankCode=$_POST['BankCode'.$LineID];
									$custtype=$_POST['CustType'.$LineID];
									$custname=$_POST['CustName'.$LineID];
									//prnMsg($LineID.'-'.$_POST['PayPrincipal'.$LineID].'=='.$PayIn);
									if ($PayPr+$PayIn!=0){
										$SQL="INSERT INTO `payprincipalinterest`(	`custid`,
																					`amount`,
																					`PayDate`,
																					`PayPrincipal`,
																					`PayInterest`,
																					`BankNumber`,
																					`Bank`,
																					BankCode )
																				VALUES(".$LineID.",
																						'0',
																						'".date('Y-m-d H:s:i')."',
																						".$PayPr.",
																						".$PayIn.",
																						'".$BankNumber."',
																						'".$Bank."',
																						'".$BankCode."')";
										//$Result=DB_query($SQL);
										$PayCust[$custtype][$LineID]=array($custname,date('Y-m-d H:s:i'),$BankNumber,$Bank,$BankCode); 
										if ($PayPr>0){  //本金
											$PayAmo[0][$LineID]=$PayPr; 
										}
										if ($PayIn>0){  //利息
											$PayAmo[1][$LineID]=$PayIn; 
										}
									}
									if ($Result){
										$Insert++;
									}
								}
							}
						}//endfor	
				
			/*print_r( $PayAmo);
			foreach ($PayCust as $key=>$rowArray){	
				echo $key;
				echo '<br/>';
			     foreach ($rowArray as $Row){
					   print_r($Row);
					   echo '<br/>';
				 }
			}
		
		foreach ($PayCust as $key=>$rowArray){ 	
		if (isset($PayAmo[0])){
			$SheetName= $key."本金";
			$pi[$key."本金"]=0;
		}
		if (isset($PayAmo[1])){
			$pi[$key."利息"]=1;
			$SheetName= $key."利息";
		}
	}
	print_r($pi);*/
	 	ExportExcelPayMode($PayCust,$PayAmo,$Header,$TitleData,$options);
	}elseif ($_POST['ExportExcel']=="付款导出Excel"){
		$Header=array('序号',	
						'类别',		
						'合同号',
						'名称',
						'编码',			
						'借出本金',
						'截止20.1.11应还',			
						'6月付本金后余款',
						'起息日',				
						'解除日',
						'已付本金',
						'已付利息',
						'本月付本金',
						'本月付利息',
						'月利率',
						'每月利息',
						'付本金',
						'付利息',
						'付款合计',
						'',
						'银行账号',
						'开户行');	
		DB_data_seek($result,0);	  
		ExportExcelPay($result,$Header,$TitleData,$options);
	}elseif ($_POST['ExportExcel']=="查询导出Excel"){
		$Header=array('序号',	
		'类别',			
		'合同号',
		'名称',
		'编码',				
		'借出本金',
		'截止20.1.11应还',				
		'6月付本金后余款',
		'起息日',				
		'解除日',
		'已付本金',
		'已付利息',
		'月利率',
		'每月利息');	
		DB_data_seek($result,0);	  
		ExportExcelSearch($result,$Header,$TitleData,$options);
	}elseif ($_POST['ExportExcel']=="计划导出Excel"){
		$Header=array('序号',	
		'类别',			
		'合同号',
		'名称',
		'编码',				
		'借出本金',
		'截止20.1.11应还',				
		'6月付本金后余款',
		'起息日',				
		'解除日',
		'已付本金',
		'已付利息',
		'月利率',
		'每月利息');	
		DB_data_seek($result,0);	  
		ExportExcelPlan($result,$Header,$TitleData,$options);
	}
}elseif (isset($_POST['PayPlan'])){
	prnMsg('付款计划查询！','info');
	
	//	prnMsg($sql);
		//$result=DB_query($sql);
        $periodmth=array(2002,2003,2004,2005,2006,2007,2008,2009,2010,2011,2012,2101,2102,2103,2104,2105);

		if (DB_num_rows($result)==0) {
			prnMsg( _('There are no outstanding authorised requests for this location'), 'info');
			echo '<br />';
			echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select another location') . '</a></div>';
			include('includes/footer.php');
			exit;
		}
	echo'<table>
			<tr>
				<th>序号</th>	
				<th>类别</th>			
				<th>合同号</th>
				<th>名称</th>
				<th>编码</th>				
				<th>借出本金</th>
				<th>截止20.1.11应还</th>				
				<th>6月付本金后余款</th>
				<th>起息日</th>				
				<th>解除日</th>';
		foreach($periodmth as $val)		{
				echo '<th>'.$val.'</th>';
		}
				
			echo'<th>合计</th>		
					</tr>';
		$disp=0;
		$RowIndex=1;
		$InterestMthTotal=0;
		$PayPrincipalTotal=0;
		$PayPrincipalTotal6=0;
		$OutstandingAmount=0;
		$CustType='';
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			//$Starttime= ($myrow['StartDate'] -25569) * 24*60*60; //获得秒数
			//$Endtime= ($myrow['EndDate'] -25569) * 24*60*60; //获得秒数
			//$SQL ="UPDATE   `principalinterest` SET   `startdt`='". date('Y-m-d ', $Starttime)."',enddt= '". date('Y-m-d ',$Endtime)."'   WHERE id=".$myrow['id'] ;
			//$Result=DB_query($SQL);
			if ($CustType==''||$CustType!=$myrow['custtype'] ){
				if ($CustType!=""){
					echo'<tr>
					<th colspan="5">小计</th>				
					<th>'.locale_number_format($PayPrincipal[$i],POI).'</th>
					<th>'.locale_number_format($Outstanding[$i],POI).'</th>				
					<th>'.locale_number_format($PayPrincipal6[$i],POI).'</th>
					<th></th>
					<th></th>';
					foreach($periodmth as $val)	{	
							echo '<th></th>';
					}
						
					echo'<th></th></tr>';
					$i++;
					$InterestMth[$i]+=round($myrow['InterestMthTotal'],POI);
					$Outstanding[$i]+=round($myrow['OutstandingAmount'],POI);
					$PayPrincipal[$i]+=round($myrow['amount'],POI);
					$PayPrincipal6[$i]+=round($myrow['PayPrincipal2006Total'],POI);
				}
				$CustType=$myrow['custtype'];

			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$tr='<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
				$tr='<tr class="OddTableRows">';
			}
			echo'<td  rowspan="2">' .$RowIndex  . '</td>';
			echo '<input type="hidden"  name="StockID'. $myrow['orderno'] . '" value="'.$myrow['stockid'].'" />';
			echo'<td  rowspan="2">' . $myrow['custtype'] . '</td>
			     <td rowspan="2">' .$myrow['orderno']. '</td>
				 <td rowspan="2">' . $myrow['name'] . '</td>
				 <td rowspan="2">' . $myrow['custid'] . '</td>
				<td rowspan="2" class="number">' . locale_number_format($myrow['amount'],POI) . '</td>
				<td rowspan="2" class="number">'.locale_number_format($myrow['OutstandingAmount'],POI).'</td>
				<td rowspan="2" class="number">'.locale_number_format($myrow['PayPrincipal2006Total'],POI).'</td>
				<td rowspan="2">' . $myrow['StartDate']. '</td>
				<td rowspan="2">' . $myrow['EndDate']. '</td>';
				$payprtotal=0;
				foreach($periodmth as $val)	{	
				
					echo '<td>'.round($myrow['PayPrincipal'.$val],POI).'</td>';
				    $payprtotal+=round($myrow['PayPrincipal'.$val],POI);
				}

				$payintotal=0;
				echo'<td >' . $payprtotal. '</td>

			
				</tr>'.$tr;
			foreach($periodmth as $val)	{	
				$payintotal+=round($myrow['PayInterest'.$val],POI);
		
				echo '<td>'.round($myrow['PayInterest'.$val],POI).'</td>';
			}
				echo '<td >' . $payintotal. '</td></tr>';
				$RowIndex++;
				$InterestMth[$i]+=round($myrow['InterestMthTotal'],POI);
				$Outstanding[$i]+=round($myrow['OutstandingAmount'],POI);
				$PayPrincipal[$i]+=round($myrow['amount'],POI);
				$PayPrincipal6[$i]+=round($myrow['PayPrincipal2006Total'],POI);
				$InterestMthTotal+=round($myrow['InterestMthTotal'],POI);
				$OutstandingAmount+=round($myrow['OutstandingAmount'],POI);
				$PayPrincipalTotla+=round($myrow['amount'],POI);
				$PayPrincipalTotla6+=round($myrow['PayPrincipal2006Total'],POI);
		}	
		echo'<tr>
		<th colspan="5">小计</th>				
		<th>'.locale_number_format($PayPrincipal[$i],POI).'</th>
		<th>'.locale_number_format($Outstanding[$i],POI).'</th>				
		<th>'.locale_number_format($PayPrincipal6[$i],POI).'</th>
		<th></th>
		<th></th>';
		foreach($periodmth as $val)	{	
		echo '<th>'.$val.'</th>';
		}
		
		echo'<th></th></tr>';
		//	$i++;
		
		echo'<tr>
			<th colspan="5">合计</th>				
			<th>'.locale_number_format($PayPrincipalTotla,POI).'</th>
			<th>'.locale_number_format($OutstandingAmount,POI).'</th>				
			<th>'.locale_number_format($PayPrincipalTotla6,POI).'</th>
			<th></th>
			<th></th>
			';
			foreach($periodmth as $val)	{	
			echo '<th>'.$val.'</th>';

	}
		
	echo'<th></th>
		
	</tr>';
	echo '</table>';

}elseif (isset($_POST['Search'])){
	//prnMsg('');
	
	//	prnMsg($sql);
		//$result=DB_query($sql);


	
	echo'<table  class="selection" >
			<tr>
				<th>序号</th>	
				<th>类别</th>			
				<th>合同号</th>
				<th>名称</th>
				<th>编码</th>				
				<th>借出本金</th>
				<th>截止20.1.11应还</th>				
				<th>6月付本金后余款</th>
				<th>起息日</th>				
				<th>解除日</th>
				<th>已付本金</th>
				<th>已付利息</th>
				<th>月利率</th>
				<th>每月利息</th>
				<th>账号</th>
				<th  style="width: 100px;">开户行</th>
				<th>行号</th>
				<th></th>
			</tr>';
		$disp=0;
		$RowIndex=1;
		$InterestMthTotal=0;
		$PayPrincipalTotal=0;
		$PayPrincipalTotal6=0;
		$OutstandingAmount=0;
		$CustType='';
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			//$Starttime= ($myrow['StartDate'] -25569) * 24*60*60; //获得秒数
			//$Endtime= ($myrow['EndDate'] -25569) * 24*60*60; //获得秒数
			//$SQL ="UPDATE   `principalinterest` SET   `startdt`='". date('Y-m-d ', $Starttime)."',enddt= '". date('Y-m-d ',$Endtime)."'   WHERE id=".$myrow['id'] ;
			//$Result=DB_query($SQL);
			$PayPrRow=round($myrow['PayPrincipal2006'],POI);
			$PayInRow=round($myrow['PayInterest2002']+$myrow['PayInterest2003']+$myrow['PayInterest2004']+$myrow['PayInterest2005']+$myrow['PayInterest2006'],POI);
			if ($CustType==''||$CustType!=$myrow['custtype'] ){
				if ($CustType!=""){
					echo'<tr>
					<th colspan="5">小计</th>				
					<th>'.locale_number_format($PayPrincipal[$i],POI).'</th>
					<th>'.locale_number_format($Outstanding[$i],POI).'</th>				
					<th>'.locale_number_format($PayPrincipal6[$i],POI).'</th>
					<th></th>
					<th></th>
					<th>'.locale_number_format($PayPr_Total[$i],POI).'</th>
					<th>'.locale_number_format($PayIn_Total[$i],POI).'</th>
					<th></th>
					<th>'.locale_number_format($InterestMth[$i],POI).'</th>
					<th colspan="4"></th>
					
				</tr>';
					$i++;
					$InterestMth[$i]+=round($myrow['InterestMthTotal'],POI);
					$Outstanding[$i]+=round($myrow['OutstandingAmount'],POI);
					$PayPrincipal[$i]+=round($myrow['amount'],POI);
					$PayPrincipal6[$i]+=round($myrow['PayPrincipal2006Total'],POI);
					$PayPr_Total[$i]+=$PayPrRow;
					$PayIn_Total[$i]+=$PayInRow;
				}
				$CustType=$myrow['custtype'];

			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			  echo'<td>' .$RowIndex  . '</td>
						<input type="hidden"  name="CustID'. $myrow['custid'] . '" value="'.$myrow['custid'].'" />
					<td>' . $myrow['custtype'] . '</td>
			        <td>' .$myrow['orderno']. '</td>
				    <td>' . $myrow['name'] . '</td>
				    <td>' . $myrow['custid'] . '</td>
					<td class="number">' . locale_number_format($myrow['amount'],POI) . '</td>
					<td class="number">'.locale_number_format($myrow['OutstandingAmount'],POI).'</td>
					<td class="number">'.locale_number_format($myrow['PayPrincipal2006Total'],POI).'</td>
					<td>' . $myrow['StartDate']. '</td>
					<td>' . $myrow['EndDate']. '</td>
					<td class="number">'.locale_number_format($PayPrRow,POI).'</td>
					<td class="number">'.locale_number_format($PayInRow,POI).'</td>
					<td class="number">'.locale_number_format($myrow['InterestRate'],POI).'</td>
					<td class="number">'.locale_number_format($myrow['InterestMthTotal'],POI).'</td>
					<td>' .$myrow['banknumber']. '</td>
				    <td>' . $myrow['bank'] . '</td>
				    <td>' . $myrow['bankcode'] . '</td>
					<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Edit='.$myrow['custid'].'`'.$myrow['name'] .'`'.$myrow['banknumber'] .'`'.$myrow['bank'] .'`'.$myrow['bankcode'] .'">编辑</a></td>				
				</tr>';
				$RowIndex++;
				$InterestMth[$i]+=round($myrow['InterestMthTotal'],POI);
				$Outstanding[$i]+=round($myrow['OutstandingAmount'],POI);
				$PayPrincipal[$i]+=round($myrow['amount'],POI);
				$PayPrincipal6[$i]+=round($myrow['PayPrincipal2006Total'],POI);
				$InterestMthTotal+=round($myrow['InterestMthTotal'],POI);
				$PayPr_Total[$i]+=$PayPrRow;
				$PayIn_Total[$i]+=$PayInRow;
				$PayPrTotal+=$PayPrRow;
				$PayInTotal+=$PayInRow;
				$OutstandingAmount+=round($myrow['OutstandingAmount'],POI);
				$PayPrincipalTotla+=round($myrow['amount'],POI);
				$PayPrincipalTotla6+=round($myrow['PayPrincipal2006Total'],POI);

		}	
		echo'<tr>
		<th colspan="5">小计</th>				
		<th>'.locale_number_format($PayPrincipal[$i],POI).'</th>
		<th>'.locale_number_format($Outstanding[$i],POI).'</th>				
		<th>'.locale_number_format($PayPrincipal6[$i],POI).'</th>
		<th></th>
		<th></th>
		<th>'.locale_number_format($PayPr_Total[$i],POI).'</th>
		<th>'.locale_number_format($PayIn_Total[$i],POI).'</th>
		<th></th>
		<th>'.locale_number_format($InterestMth[$i],POI).'</th>
		<th colspan="4"></th>
	</tr>';
	//	$i++;
	
	echo'<tr>
		<th colspan="5">合计</th>				
		<th>'.locale_number_format($PayPrincipalTotal,POI).'</th>
		<th>'.locale_number_format($OutstandingAmount,POI).'</th>				
		<th>'.locale_number_format($PayPrincipalTotal6,POI).'</th>
		<th></th>
		<th></th>
		<th>'.locale_number_format($PayPrTotal,POI).'</th>
		<th>'.locale_number_format($PayInTotal,POI).'</th>
		<th></th>
		<th>'.locale_number_format($InterestMthTotal,POI).'</th>
		<th colspan="4"></th>
	</tr>';
	echo '</table>';

}elseif (isset($_POST['PaySearch'])||isset($_POST['UpdateSave'])){
	//prnMsg('PaySearch');
	//$result=DB_query($sql);


		if (DB_num_rows($result)==0) {
			prnMsg( _('There are no outstanding authorised requests for this location'), 'info');
			echo '<br />';
			echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Select another location') . '</a></div>';
			include('includes/footer.php');
			exit;
		}
	echo'<table>
			<tr>
				<th>序号</th>	
				<th>类别</th>			
				<th>合同号</th>
				<th>名称</th>
				<th>编码</th>				
				<th>借出本金</th>
				<th>截止20.1.11应还</th>				
				<th>本金余款</th>
				<th>起息日</th>				
				<th>解除日</th>
				<th>已付本金</th>
				<th>已付利息</th>
				<th>本月付本金</th>
				<th>本月付利息</th>
				<th>月利率</th>
				<th>每月利息</th>
				<th>付本金</th>
				<th>付利息</th>
				<th>付款合计</th>
				<th></th>
				<th>银行账号</th>
				<th>开户行</th>
				<th>开户行代码</th>
			</tr>';
		$disp=0;
		$RowIndex=1;
		$InterestMthTotal=0;
		$PayPrincipalTotal=0;
		$PayPrincipalTotal6=0;
		$OutstandingAmount=0;
		$Pay=0;
		$CustType='';
		$PayPr=0;
		$PayIn=0;
		$i=0;
		$md=date("ym");
		while ($myrow=DB_fetch_array($result)) {
			 $SQL="SELECT  SUM(`PayPrincipal`) paypr,SUM( `PayInterest`) payin FROM `payprincipalinterest` WHERE YEAR(PayDate)=".date("Y")." AND MONTH(PayDate)=".date("m")." AND custid=".$myrow['custid'];
			 $Result=DB_query($SQL);
			 $Row=DB_fetch_assoc($Result);
			 $PayPrMth=0;
			 $PayInMth=0;
			 if (!empty($Row)){
				$PayPrMth=$Row['paypr'];
				$PayInMth=$Row['payin'];
			 }
			if ($CustType==''||$CustType!=$myrow['custtype'] ){
				if ($CustType!=""){
					echo'<tr>
					<th colspan="5">小计</th>				
					<th>'.locale_number_format($PayPrincipal[$i],POI).'</th>
					<th>'.locale_number_format($Outstanding[$i],POI).'</th>				
					<th>'.locale_number_format($PayPrincipal6[$i],POI).'</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th>'.locale_number_format($InterestMth[$i],POI).'</th>
					<th></th>
				
					<th  colspan="4"></th>
				</tr>';
					$i++;
					$InterestMth[$i]+=round($myrow['InterestMthTotal'],POI);
					$Outstanding[$i]+=round($myrow['OutstandingAmount'],POI);
					$PayPrincipal[$i]+=round($myrow['amount'],POI);
					$PayPrincipal6[$i]+=round($myrow['PayPrincipal2006Total'],POI);
				}
				$CustType=$myrow['custtype'];

			}
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
            }
            if ($PayInMth>0){
                $_POST["PayInterest" . $myrow['custid']  ]='';
            }else{
                $_POST["PayInterest" . $myrow['custid']  ]=round($myrow['PayInterest'.$md],0);
            }
            if ($PayPrMth>0 ){
                $_POST["PayPrincipal" . $myrow['custid']  ]='';
            }else{
                
                $_POST["PayPrincipal" . $myrow['custid']  ]=round($myrow['PayPrincipal'.$md],0);
            }
			$PayPrincipal= round($myrow['PayPrincipal'.$md],POI);
            $_POST["PayPI" . $myrow['custid']  ]=round($_POST["PayInterest" . $myrow['custid']  ],POI)+round($_POST["PayPrincipal" . $myrow['custid']  ],POI);
            $checked="checked"; 
            if ($PayPrMth>0 ||$PayInMth>0){
            $checked=" "; 
            }
			echo'<td>' .$RowIndex  . '</td>';
			
			echo'<td>' . $myrow['custtype'] . '
					<input type="hidden"  name="CustID'. $myrow['custid'] . '" value="'.$myrow['custid'].'" />
					<input type="hidden"  name="CustType'. $myrow['custid'] . '" value="'.$myrow['custtype'].'" />
					<input type="hidden"  name="CustName'. $myrow['custid'] . '" value="'.$myrow['name'].'" /></td>
			     <td>' .$myrow['orderno']. '</td>
				 <td>' . $myrow['name'] . '</td>
				 <td>' . $myrow['custid'] . '</td>
				<td class="number">' . locale_number_format($myrow['amount'],POI) . '</td>
				<td class="number">'.locale_number_format($myrow['OutstandingAmount'],POI).'</td>
				<td class="number" title="截至6月应付本金：'.$myrow['PayPrincipal2006Total'].'">'.locale_number_format($myrow['PayPrincipal2006Total']-$PayPrincipal,POI).'</td>
				<td>' . $myrow['StartDate']. '</td>
				<td>' . $myrow['EndDate']. '</td>
				<td class="number">'.locale_number_format($PayPrincipal,POI).'</td>
				<td class="number">'.locale_number_format($myrow['PayInterest'],POI).'</td>
			

				<td class="number">'.locale_number_format($PayPrMth,POI).'</td>
				<td class="number">'.locale_number_format($PayInMth,POI).'</td>
				<td class="number">'.locale_number_format($myrow['InterestRate'],POI).'</td>
				<td class="number">'.locale_number_format($myrow['InterestMthTotal'],POI).' </td>
				<td><input type="text" name="PayPrincipal' . $myrow['custid']  . '" maxlength="10" size="7" value="'.$_POST["PayPrincipal" . $myrow['custid']  ].'" /></td>
				<td><input type="text" name="PayInterest' . $myrow['custid']  . '" maxlength="10" size="7" value="'.$_POST["PayInterest" . $myrow['custid']  ].'" /></td>
				<td><input type="text" name="PayPI' . $myrow['custid']  . '" maxlength="10" size="7" value="'.$_POST["PayPI" . $myrow['custid']  ].'" /></td>
				<td><input type="checkbox" name="Check'.$myrow['custid']  .'" id="Check'.$myrow['custid']  .'" value="'.$myrow['custid']  .'" '.$checked.' /></td>
				<td><input type="text"  name="BankNumber'. $myrow['custid'] . '" maxlength="10" size="10"  value="'.$myrow['banknumber'].'" /></td>
				<td><input type="text"  name="Bank'. $myrow['custid'] . '" maxlength="10" size="10"  value="'.$myrow['bank'].'" /></td>
				<td><input type="text"  name="BankCode'. $myrow['custid'] . '" maxlength="10" size="5"  value="'.$myrow['bankcode'].'" /></td>
				</tr>';
				$RowIndex++;
				$Pay+=$_POST["PayPI" . $myrow['custid']  ];
				$PayIn+=$_POST["PayInterest" . $myrow['custid']  ];
				$PayPr+=$_POST["PayPrincipal" . $myrow['custid']  ];
				
				$InterestMth[$i]+=round($myrow['InterestMthTotal'],POI);
				$Outstanding[$i]+=round($myrow['OutstandingAmount'],POI);
				$PayPrincipal[$i]+=round($myrow['amount'],POI);
				$PayPrincipal6[$i]+=round($myrow['PayPrincipal2006Total'],POI);

				$InterestMthTotal+=round($myrow['InterestMthTotal'],POI);
				$PayPrMthTotal+=round($PayPrincipal,POI);
				$OutstandingAmount+=round($myrow['OutstandingAmount'],POI);
				$PayPrincipalTotal+=round($myrow['amount'],POI);
				$PayPrincipalTotla6+=round($myrow['PayPrincipal2006Total'],POI);
		}	
	
	echo'<tr>
		<th colspan="5">合计</th>				
		<th>'.locale_number_format($PayPrincipalTotal,POI).'</th>
		<th>'.locale_number_format($OutstandingAmount,POI).'</th>				
		<th>'.locale_number_format($PayPrincipalTotla6-$PayPrMthTotal,POI).'</th>
		<th></th>
		<th></th>
		<th>'.locale_number_format($PayPrMthTotal,POI).'</th>
		<th></th>
		<th></th>
		<th></th>
		<th></th>
		<th>'.locale_number_format($InterestMthTotal,POI).'</th>
		<th>'.locale_number_format($PayPr,POI).'</th>
		<th>'.locale_number_format($PayIn,POI).'</th>
		<th>'.locale_number_format($Pay,POI).'</th>
		<th  colspan="4"></th>
	</tr>';
	echo '</table>';
	if (isset($_POST['UpdateSave'])){
		$Insert=0;
		foreach ($_POST as $key => $value) {
			//prnMsg($key);
			if (mb_strpos($key,'CustID')!==false) {				      
				$LineID = mb_substr($key,mb_strpos($key,'CustID')+6);
			
				if ($_POST['Check'.$LineID]) {
					
				
				
					$PayPr=filter_number_format($_POST['PayPrincipal'.$LineID],POI);
					$PayIn=filter_number_format($_POST['PayInterest'.$LineID],POI);
					$BankNumber=$_POST['BankNumber'.$LineID];
					$Bank=$_POST['Bank'.$LineID];
					//prnMsg($LineID.'-'.$_POST['PayPrincipal'.$LineID].'=='.$PayIn);
					$SQL="INSERT INTO `payprincipalinterest`(	`custid`,
																`amount`,
																`PayDate`,
																`PayPrincipal`,
																`PayInterest`,
																`BankNumber`,
																`Bank`,
																BankCode )
															VALUES(".$LineID.",
															'0',
															'".date('Y-m-d H:s:i')."',
															".$PayPr.",
															".$PayIn.",
															'".$BankNumber."',
															'".$Bank."',
															'')";
					$Result=DB_query($SQL);
					if ($Result){
						$Insert++;
					}
				}
			}
		}//endfor
		if ($Insert>0){
			prnMsg($Insert."笔付款记录添加成功！",'success');
		}else{
			prnMsg("付款记录没有添加成功！",'info');
		}
	}

}

$sql="SELECT `custid`, `amount`, `PayDate`, `PayPrincipal`, `PayInterest`, `BankNumber`, `Bank` FROM `payprincipalinterest` WHERE 1";
	echo '</form>';


include('includes/footer.php');

function ExportExcelPayMode($PayCust,$PayAmo,$Header,$TitleData,$options){
	//$result0,$_SQL,$BankAccount,$Query,$TitleData,$options){
	$CustFlag=array('0'=>'多宝','1'=>'张健');
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';  
	$k=$TitleData['k'];

	  $spreadsheet = new Spreadsheet();
	  // Create a new worksheet "
	  $sheet = $spreadsheet->getActiveSheet();
	  //设置sheet的名字  两种方法
	  $sheet->setTitle('本金利息支付表');
	  $spreadsheet->getActiveSheet()->setTitle('本金利息支付表');
	  	//设置默认文字居左，上下居中 
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_RIGHT,
				'vertical'   => Alignment::VERTICAL_CENTER,
			],
		];
		$spreadsheet->getDefaultStyle()->applyFromArray($styleArray);
	
	  //数据中对应的字段，用于读取相应数据：
	
	  $columnCnt = count($Header);  //计算表头数量

	/*--------------开始从数据库提取信息插入Excel表中------------------*//*
	
		*/
	for ($i = 65; $i < $columnCnt + 65; $i++) {     //数字转字母从65开始，循环设置表头：
		
		if ($i==67){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k),  '本金利息支付表' );
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(30); //固��列宽
		}else {
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
		}
	}
	$k++;
	$PrTotla=0;
	$InTotal=0;
	foreach ($PayCust as $key=>$rowArray){
		
	    for ($i = 65; $i < $columnCnt + 65; $i++) {     //数字转字母从65开始，循环设置表头：
			
			if ($i==65)
				$sheet->setCellValue(strtoupper(chr(65)) .($k),  $key );
			else {
				$sheet->setCellValue(strtoupper(chr($i)) .($k),  '' );
			}
		}
		$k++;
		foreach ($rowArray as $ky=>$row){
		
			for ($i = 65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始：
				
				if ($i==65){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), $row[0]);
				}elseif($i==66){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), 	$row[2].' ');
				}elseif($i==67){
				
					$sheet->setCellValue(strtoupper(chr($i)) . ($k),(string)$row[4].' ');
				}elseif($i==68){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($PayAmo[0][$ky],POI));
				}elseif($i==69){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k),locale_number_format($PayAmo[1][$ky],POI));
				}
		
			}
			$PrTotal+=$PayAmo[0][$ky];
			$InTotal+=$PayAmo[1][$ky];
			$k++;
		}//
	}
	for ($i = 65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始：
				
		if($i==68){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($PrTotal,POI));
		}elseif($i==69){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k),locale_number_format($InTotal,POI));
		}else{
			
				$sheet->setCellValue(strtoupper(chr($i)) . ($k),'');
			
		}

	}

   //添加第二表 及多表 

   $p=0;
   foreach ($PayCust as $key=>$rowArray){ 	
		if (isset($PayAmo[0])){
			$SheetName= $key."本金";
			$pi[$key."本金"]=array(0,$key);
		}
		if (isset($PayAmo[1])){
			$pi[$key."利息"]=array(1,$key);
			$SheetName= $key."利息";
		}
	}

	
	foreach ($pi as $key=>$val){ 	
		
			$SheetName= $key;
			$k=2;	
		
			//echo $sheetname;
			$WorkSheet[$SheetName] = new Worksheet($spreadsheet,$SheetName);//与下面的配合使用
		
			$spreadsheet->addSheet($WorkSheet[$SheetName], 0);//将“My Data”工作表作为电子表格对象中的第一个工作表���加
			  /* 设置宽度 */
			  $WorkSheet[$SheetName]->getColumnDimension('A')->setWidth(30);
			  $WorkSheet[$SheetName]->getColumnDimension('B')->setWidth(20);		
			  $WorkSheet[$SheetName]->getColumnDimension('C')->setWidth(20);
			  $WorkSheet[$SheetName]->getColumnDimension('D')->setWidth(20);
		  
			  $WorkSheet[$SheetName]->getColumnDimension('E')->setWidth(20);
		
			//设置第一行行高为20pt

			for ($_column = 1; $_column <= $columnCnt; $_column++) {
				$cellName = Coordinate::stringFromColumnIndex($_column);
				$cellId   = $cellName . ($k);
				//表头
				$WorkSheet[$SheetName]->setCellValue($cellName.($k),  (string)$Header[$_column-1]); 
			}
			$k=3;
			
				$PayAm=$PayAmo[$val[0]];
			//	$PayCust[$custtype][$LineID]=array($custname,date('Y-m-d H:s:i'),$BankNumber,$Bank,$BankCode); 
		    $PayTotal=0;
			foreach ($PayCust[$val[1]] as $keyid=>$row){
			
				for ($i = 65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始：
				
					if ($i==65){
						
						$WorkSheet[$SheetName]->setCellValue(strtoupper(chr($i)) . ($k),  $row[2].' ');
					}elseif($i==66){
						$WorkSheet[$SheetName]->setCellValue(strtoupper(chr($i)) . ($k),  $row[0]);
					}elseif($i==67){
						$WorkSheet[$SheetName]->setCellValue(strtoupper(chr($i)) . ($k), 1);
					}elseif($i==68){
						$WorkSheet[$SheetName]->setCellValue(strtoupper(chr($i)) . ($k),  $row[4]);
					}elseif($i==69){
						$WorkSheet[$SheetName]->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($PayAm[$keyid],POI));
					}
			
				}
				$PayTotal+=$PayAm[$keyid];
				$k++;
			}//end  while
			$WorkSheet[$SheetName]->setCellValue('A1',  '总笔数'); 
			$WorkSheet[$SheetName]->setCellValue('B1', $k-3); 
			$WorkSheet[$SheetName]->setCellValue('C1', '总金额'); 
			$WorkSheet[$SheetName]->setCellValue('D1', $PayTotal); 
		
	
	}
	$filename=$TitleData['Title'].".xlsx";
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

function ExportExcelPay($result,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		$rowCnt=DB_num_rows($Result); 
		$k=$titledata['k'];
		$sk=$k+1;
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
		//$sheet->setCellValue('A'.$k, "公司名称:". (string)$titledata['coyname']); 
		$sheet->setCellValue(Coordinate::stringFromColumnIndex($columnCnt).($k),  "单位：".(string)$titledata['Units']); 
		//设���默认行高
		$sheet->getDefaultRowDimension()->setRowHeight(20);
		
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_CENTER, //��平居中
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
	
		  /* 设置宽度 */
		$activeSheet->getColumnDimension('B')->setWidth(15);		
		$activeSheet->getColumnDimension('C')->setWidth(30);
		$activeSheet->getColumnDimension('D')->setWidth(15);
	
		$activeSheet->getColumnDimension('F')->setWidth(20);
		$activeSheet->getColumnDimension('G')->setWidth(20);
		$activeSheet->getColumnDimension('H')->setWidth(20);
		$activeSheet->getColumnDimension('I')->setWidth(15);
		$activeSheet->getColumnDimension('J')->setWidth(15);
		$activeSheet->getColumnDimension('K')->setWidth(20);
		$activeSheet->getColumnDimension('T')->setAutoSize(true);
	
		$activeSheet->getColumnDimension('U')->setWidth(30);
		$activeSheet->getColumnDimension('V')->setWidth(50);
	   
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);
			//表头
			$sheet->setCellValue($cellName.($k),  (string)$header[$_column-1]); 
		}
		$k++;
		$rw=$k-1;
		$InterestMthTotal=0;
		$PayPrincipalTotal=0;
		$PayPrincipalTotal6=0;
		$OutstandingAmount=0;
		$CustType='';
		$i=0;
		
		$PayPr=0;
		$PayIn=0;
		$i=0;
		$md=date("ym");
	while ($row = DB_fetch_array($result)){
		$PayPrRow=round($row['PayPrincipal2006'],POI);
		$PayInRow=round($row['PayInterest2002']+$row['PayInterest2003']+$row['PayInterest2004']+$row['PayInterest2005']+$row['PayInterest2006'],POI);
		$SQL="SELECT  SUM(`PayPrincipal`) paypr,SUM( `PayInterest`) payin FROM `payprincipalinterest` WHERE YEAR(PayDate)=".date("Y")." AND MONTH(PayDate)=".date("m")." AND custid=".$row['custid'];
		$Result=DB_query($SQL);
		$Row=DB_fetch_assoc($Result);
		$PayPrMth=0;
		$PayInMth=0;
		if (!empty($Row)){
		   $PayPrMth=$Row['paypr'];
		   $PayInMth=$Row['payin'];
		}
		$SQL="SELECT  SUM(`PayPrincipal`) payprtotal,SUM( `PayInterest`) payintotal FROM `payprincipalinterest` WHERE YEAR(PayDate)<".date("Y")." AND MONTH(PayDate)<".date("m")." AND custid=".$row['custid'];
		$Result=DB_query($SQL);
		$ROW=DB_fetch_assoc($Result);
		$PayPrTotal=0;
		$PayInTotal=0;
		if (!empty($Row)){
		   $PayPrTotal=$ROW['payprtotal'];
		   $PayInTotal=$ROW['payintotal'];
		}
		$PayInterest=round($row['InterestMthTotal'],POI);
			$PayPrincipal=round($row['PayPrincipal'.$md],POI);
		
			$PayInterest=round($row['InterestMthTotal'],POI);
			
			//$PayPrincipal= round($row['PayPrincipal'.$md],POI);
			$PayPI=$PayInterest+$PayPrincipal;
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);			
			if ($_column==1){
				//  序号列 
			
				$sheet->setCellValue($cellName.($k), $k-$rw); 
			}else{
			
				if ($_column==2){					
					$sheet->setCellValue($cellName.($k), (string)$row['custtype']);
				}elseif ($_column==3){					
					$sheet->setCellValue($cellName.($k), (string)$row['orderno']);
				}elseif ($_column==4){					
					$sheet->setCellValue($cellName.($k), (string)$row['name']);
				}elseif ($_column==5){					
					$sheet->setCellValue($cellName.($k), (string)$row['custid']);
				}elseif ($_column==6){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['amount'],POI) );
				}elseif ($_column==7){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['OutstandingAmount'],POI));
				}elseif ($_column==8){					
					$sheet->setCellValue($cellName.($k),locale_number_format($row['PayPrincipal2006Total'],POI));
				}elseif ($_column==9){					
					$sheet->setCellValue($cellName.($k), $row['StartDate']);
				}elseif ($_column==10){					
					$sheet->setCellValue($cellName.($k),$row['EndDate']);
				}elseif ($_column==11){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayPrRow+$PayPrTotal,POI));
				}elseif ($_column==12){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayInRow+$PayInTotal,POI));
				}elseif ($_column==13){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayPrMth,POI));
				}elseif ($_column==14){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayInMth,POI));
				}elseif ($_column==15){					
					$sheet->setCellValue($cellName.($k),locale_number_format($row['InterestRate'],POI));
				}elseif ($_column==16){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['InterestMthTotal'],POI));
				}elseif ($_column==17){					
					$sheet->setCellValue($cellName.($k),locale_number_format(	$PayPrincipal,POI));
				}elseif ($_column==18){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayInterest,POI));
				}elseif ($_column==19){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayPI,POI));
				}elseif ($_column==21){					
					$sheet->setCellValue($cellName.($k),locale_number_format($row['banknumber'],POI));
				}elseif ($_column==22){					
					$sheet->setCellValue($cellName.($k),$row['bank']);
				}
			}
		}
		
				$Pay+=$PayPI;
				$PayIn+=$PayInterest;
				$PayPr+=$PayPrincipal;

				$InterestMth[$i]+=round($row['InterestMthTotal'],POI);
				$Outstanding[$i]+=round($row['OutstandingAmount'],POI);
				$PayPrincipal[$i]+=round($row['amount'],POI);
				$PayPrincipal6[$i]+=round($row['PayPrincipal2006Total'],POI);

				$InterestMthTotal+=round($row['InterestMthTotal'],POI);
				//$PayPrTotal+=$PayPrRow;
				//$PayInTotal+=$PayInRow;
				$OutstandingAmount+=round($row['OutstandingAmount'],POI);
				$PayPrincipalTotal+=round($row['amount'],POI);
				$PayPrincipalTotla6+=round($row['PayPrincipal2006Total'],POI);

			if (!empty($row[$cellName-1])) {
				$isNull = false;
			}
		
		$k++;

	}
		//	$k++;
	
	for ($_column = 1; $_column <= $columnCnt; $_column++) {
		$cellName = Coordinate::stringFromColumnIndex($_column);
		$cellId   = $cellName . ($k);			
			if ($_column==6){					
				$sheet->setCellValue($cellName.($k), locale_number_format($PayPrincipalTotal,POI) );
			}elseif ($_column==7){					
				$sheet->setCellValue($cellName.($k), locale_number_format($OutstandingAmount,POI));
			}elseif ($_column==8){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayPrincipalTotal6,POI));
			}elseif ($_column==11){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayPrTotal,POI));
			}elseif ($_column==12){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayInTotal,POI));
			}elseif ($_column==14){					
				$sheet->setCellValue($cellName.($k), locale_number_format($InterestMthTotal,POI));
			}elseif ($_column==15){					
				$sheet->setCellValue($cellName.($k),locale_number_format($row['InterestRate'],POI));
			}elseif ($_column==16){					
				$sheet->setCellValue($cellName.($k), locale_number_format($row['InterestMthTotal'],POI));
			}elseif ($_column==17){					
				$sheet->setCellValue($cellName.($k),locale_number_format(	$PayPr,POI));
			}elseif ($_column==18){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayIn,POI));
			}elseif ($_column==19){					
				$sheet->setCellValue($cellName.($k),locale_number_format($Pay,POI));
			}else{
			
			
				$sheet->setCellValue($cellName.($k),''); 
			}
	}
		

	$activeSheet->getStyle('A'.(int)($sk).':'.Coordinate::stringFromColumnIndex($columnCnt).($k))->applyFromArray($styleArray);
	


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

  function ExportExcelSearch($Result,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		$rowCnt=DB_num_rows($Result); 
		$k=$titledata['k'];
		$sk=$k+1;
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
	
		  /* 设置宽度 */
		$activeSheet->getColumnDimension('B')->setWidth(15);		
		$activeSheet->getColumnDimension('C')->setWidth(30);
		$activeSheet->getColumnDimension('D')->setWidth(15);
	
		$activeSheet->getColumnDimension('F')->setWidth(20);
		$activeSheet->getColumnDimension('G')->setWidth(20);
		$activeSheet->getColumnDimension('H')->setWidth(20);
		$activeSheet->getColumnDimension('I')->setWidth(15);
		$activeSheet->getColumnDimension('J')->setWidth(15);
		$activeSheet->getColumnDimension('K')->setWidth(20);
		//		$activeSheet->getColumnDimension('D')->setAutoSize(true);
	
	
	   
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);
			//表头
			$sheet->setCellValue($cellName.($k),  (string)$header[$_column-1]); 
		}
		$k++;
		$rw=$k-1;
		$InterestMthTotal=0;
		$PayPrincipalTotal=0;
		$PayPrincipalTotal6=0;
		$OutstandingAmount=0;
		$CustType='';
		$i=0;
	while ($row = DB_fetch_array($Result)){
		$PayPrRow=round($row['PayPrincipal2006'],POI);
		$PayInRow=round($row['PayInterest2002']+$row['PayInterest2003']+$row['PayInterest2004']+$row['PayInterest2005']+$row['PayInterest2006'],POI);
		if ($CustType==''||$CustType!=$row['custtype'] ){
			if ($CustType!=""){
				for ($_column = 1; $_column <= $columnCnt; $_column++) {
					$cellName = Coordinate::stringFromColumnIndex($_column);
					$cellId   = $cellName . ($k);			
				        if ($_column==6){					
							$sheet->setCellValue($cellName.($k), locale_number_format($PayPrincipal[$i],POI) );
						}elseif ($_column==7){					
							$sheet->setCellValue($cellName.($k), locale_number_format($Outstanding[$i],POI));
						}elseif ($_column==8){					
							$sheet->setCellValue($cellName.($k),locale_number_format($PayPrincipal6[$i],POI));
						}elseif ($_column==11){					
							$sheet->setCellValue($cellName.($k),locale_number_format($PayPr_Total[$i],POI));
						}elseif ($_column==12){					
							$sheet->setCellValue($cellName.($k),locale_number_format($PayIn_Total[$i],POI));
						}elseif ($_column==14){					
							$sheet->setCellValue($cellName.($k), locale_number_format($InterestMth[$i],POI));
						}else{
						
						
							$sheet->setCellValue($cellName.($k),''); 
						}
				}
				$i++;
						$InterestMth[$i]+=round($row['InterestMthTotal'],POI);
						$Outstanding[$i]+=round($row['OutstandingAmount'],POI);
						$PayPrincipal[$i]+=round($row['amount'],POI);
						$PayPrincipal6[$i]+=round($row['PayPrincipal2006Total'],POI);
						//$InterestMthTotal+=round($row['InterestMthTotal'],POI);
						$PayPr_Total[$i]+=$PayPrRow;
						$PayIn_Total[$i]+=$PayInRow;
					
		
						
					if (!empty($row[$cellName-1])) {
						$isNull = false;
					}
					$k++;
			}
				
			
			$CustType=$row['custtype'];

		}
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);			
			if ($_column==1){
				//  序号列 
			
				$sheet->setCellValue($cellName.($k), $k-$rw); 
			}else{
			
				if ($_column==2){					
					$sheet->setCellValue($cellName.($k), (string)$row['custtype']);
				}elseif ($_column==3){					
					$sheet->setCellValue($cellName.($k), (string)$row['orderno']);
				}elseif ($_column==4){					
					$sheet->setCellValue($cellName.($k), (string)$row['name']);
				}elseif ($_column==5){					
					$sheet->setCellValue($cellName.($k), (string)$row['custid']);
				}elseif ($_column==6){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['amount'],POI) );
				}elseif ($_column==7){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['OutstandingAmount'],POI));
				}elseif ($_column==8){					
					$sheet->setCellValue($cellName.($k),locale_number_format($row['PayPrincipal2006Total'],POI));
				}elseif ($_column==9){					
					$sheet->setCellValue($cellName.($k), $row['StartDate']);
				}elseif ($_column==10){					
					$sheet->setCellValue($cellName.($k),$row['EndDate']);
				}elseif ($_column==11){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayPrRow,POI));
				}elseif ($_column==12){					
					$sheet->setCellValue($cellName.($k),locale_number_format($PayInRow,POI));
				}elseif ($_column==13){					
					$sheet->setCellValue($cellName.($k),locale_number_format($row['InterestRate'],POI));
				}elseif ($_column==14){					
					$sheet->setCellValue($cellName.($k), locale_number_format($row['InterestMthTotal'],POI));
				}
			}
		}
			//$RowIndex++;
				$InterestMth[$i]+=round($row['InterestMthTotal'],POI);
				$Outstanding[$i]+=round($row['OutstandingAmount'],POI);
				$PayPrincipal[$i]+=round($row['amount'],POI);
				$PayPrincipal6[$i]+=round($row['PayPrincipal2006Total'],POI);

				$PayPr_Total[$i]+=$PayPrRow;
				$PayIn_Total[$i]+=$PayInRow;


				$InterestMthTotal+=round($row['InterestMthTotal'],POI);
			
				$PayPrTotal+=$PayPrRow;
				$PayInTotal+=$PayInRow;
				$OutstandingAmount+=round($row['OutstandingAmount'],POI);
				$PayPrincipalTotal+=round($row['amount'],POI);
				$PayPrincipalTotla6+=round($row['PayPrincipal2006Total'],POI);


			if (!empty($row[$cellName-1])) {
				$isNull = false;
			}
		
		$k++;

	}
	for ($_column = 1; $_column <= $columnCnt; $_column++) {
		$cellName = Coordinate::stringFromColumnIndex($_column);
		$cellId   = $cellName . ($k);			
			if ($_column==6){					
				$sheet->setCellValue($cellName.($k), locale_number_format($PayPrincipal[$i],POI) );
			}elseif ($_column==7){					
				$sheet->setCellValue($cellName.($k), locale_number_format($Outstanding[$i],POI));
			}elseif ($_column==8){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayPrincipal6[$i],POI));
			}elseif ($_column==11){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayPr_Total[$i],POI));
			}elseif ($_column==12){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayIn_Total[$i],POI));
			}elseif ($_column==14){					
				$sheet->setCellValue($cellName.($k), locale_number_format($InterestMth[$i],POI));
			}else{
				
			
				$sheet->setCellValue($cellName.($k),''); 
			}
	}
		
	$k++;
	for ($_column = 1; $_column <= $columnCnt; $_column++) {
		$cellName = Coordinate::stringFromColumnIndex($_column);
		$cellId   = $cellName . ($k);			
			if ($_column==6){					
				$sheet->setCellValue($cellName.($k), locale_number_format($PayPrincipalTotal,POI) );
			}elseif ($_column==7){					
				$sheet->setCellValue($cellName.($k), locale_number_format($OutstandingAmount,POI));
			}elseif ($_column==8){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayPrincipalTotal6,POI));
			}elseif ($_column==11){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayPrTotal,POI));
			}elseif ($_column==12){					
				$sheet->setCellValue($cellName.($k),locale_number_format($PayInTotal,POI));
			}elseif ($_column==14){					
				$sheet->setCellValue($cellName.($k), locale_number_format($InterestMthTotal,POI));
			}else{
			
			
				$sheet->setCellValue($cellName.($k),''); 
			}
	}
		

	$activeSheet->getStyle('A'.(int)($sk).':'.Coordinate::stringFromColumnIndex($columnCnt).($k))->applyFromArray($styleArray);
	

	$filename='付款本金利息表'. date('Y-m-d', time()).rand(1000, 9999).".xlsx";
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
	//注意	createWriter($spreadsheet, 'Xls') //第二个参数首字��必须大写
	$writer->save('php://output'); 

}	

?>
