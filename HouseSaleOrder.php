<?php

/* $Id: StockCheck.php 6962 2014-11-06 02:59:12Z tehonu $*/
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
include('includes/session.php');
echo'<script type="text/javascript">
	function onLocations(s){      
	
		var jsn=document.getElementById("StockCategoryJson").value;	
	
		var selectobj=document.getElementById("Level");
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

$Title='房屋管理';
include('includes/header.php');

if(isset($_POST['ClearCache'])) {
	unset($_SESSION['HouseStockID']);
}

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
//	$StockCategoryJson=json_encode($StockCategory,JSON_UNESCAPED_UNICODE);	 
	$flagtxt=[0=>"待盘点",1=>"盘点",2=>"批准"];
if(!isset($_POST['ExportExcel'])) {
	echo  ' <input type="hidden" id="StockCategoryJson" name="StockCategoryJson" value=' . $StockCategoryJson . ' />';
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="'
		. _('print') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="BatchID" value="' . $_POST['BatchID'] . '" />';

}
	$SQL="SELECT `stockid`, `stockno`, `HouseType`,HouseNumber, `project`, `BuildNumber`, `Units`, `level`, `qty`, `InsideArea`, `ShareArea`, `BuiltArea`, `ActualArea`, `used` FROM `houseaccount`  WHERE used=0 ";
							// categoryid='" . $_POST['StkCat'] . "' 
	$result = DB_query($SQL);

if (isset($_POST['ExportExcel'])){

	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	$TitleData=array("Title"=>'对账单',"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal));	

	 $Header=array( '序号', '物料编码', '物料名称', '仓库', '物料组', '账面数', '盘点数', '摘要', '盘点日期' );	
	DB_data_seek($ResultCounts,0);	  
	ExportExcel($ResultCounts,$Header,$TitleData,$options);
}	

if (isset($_POST['Select'])){
	$sql="SELECT `stockid`, `stockno`, `HouseType`, `project`, `BuildNumber`, `Units`, `level`, `qty`, `InsideArea`, `ShareArea`, `BuiltArea`, `ActualArea`, `used` 
	        FROM `houseaccount`  
			WHERE stockid='".$_POST['Select']."' ";
	$result = DB_query($sql);
	$ROW=DB_fetch_assoc($result);

	$_SESSION['HouseStockID']=$ROW;
	
	
	//var_dump($_SESSION['HouseStockID']);

}	
if (isset($_POST['UpdateSave'])){
	//保存合同
	$SQL="SELECT `regid`,  `custname`,registerno,bankaccount, `sub`, `regdate`, `acctype`, `tag`
			FROM `register_account_sub` 
			WHERE custname LIKE '".$_POST['RegCustName']."' OR registerno='".$_POST['RegisterNo']."' OR bankaccount='".$_POST['BankAct']."'";
			$OnEdit = 0;
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);
			$Error=0;
			if (isset($Row)){// && isset($_POST['New'])){
				
				if ($_POST['RegCustName']!='' && $Row['custname']!=$_POST['RegCustName']){
					//输入名和系统名不一样  ,提示错误停止
					$Error=-1;
					$msg.='客户:'.$_POST['RegCustName'].'和系统存在的名称不同,';
					//return array(0=>-1);
				}
				if ($_POST['RegisterNo']!='' && $Row['registerno']!=$_POST['RegisterNo']){
					//输入注册码和系统码不一样  ,提示错误停止
					$Error=-2;
					$msg.='注册码:'.$_POST['RegisterNo'].'和系统存在的注册码不同,';
				//	return array(0=>-2);
				}
				if ($Row['sub']!=''&&$Error==0){
					$_POST['Account']=$Row['sub'];
					prnMsg('你添加的客户'.$_POST['RegCustName'].'已经添加,编码:'.$Row['regid'].' 会计科目:'.$Row['sub'],'info');
					$CustData=$Row;
					//echo '<br /><div class="centre"><a href="Customers.php" >客户添加</a></div><br />';
					//include('includes/footer.php');
					//exit;
				}elseif($Error==-1|| $Error==-2) {
					//if ($Error==-1)
					prnMsg('你添加的'.$msg,'warn');
					
					echo '<br /><div class="centre"><a href="Customers.php" >客户添加</a></div><br />';
					include('includes/footer.php');
					exit;
					
				}else{
					//系统已经存在单位,但不完善 ,如果$SuppData不存在,��单位
					$CustData=$Row;
				}
			}else {//新客户
				$_POST['CustName']=$_POST['RegCustName'];
				$_POST['Address5']=$_POST['RegisterNo'];
				$_POST['Address4']=$_POST['BankAct'];

			}
	if (mb_strlen($_POST['CustName']) > 2 OR mb_strlen($_POST['CustName'])<5) {
		$InputError = 1;
		prnMsg('客户名称必须输入大于2个字符小于5个字符!','error');
		$Errors[$i] = 'CustName';
		$i++;
	} elseif (mb_strlen($_POST['Address1']) >20) {//地址
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address1';
		$i++;
	} elseif (mb_strlen($_POST['Address3']) >20) {//开户行
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address3';
		$i++;
	} elseif (mb_strlen($_POST['Address4']) >20) {//账号
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address4';
		$i++;
	}  elseif (mb_strlen($_POST['Address5']) >20) {//注册吗
		$InputError = 1;
		prnMsg( _('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'Address5';
		$i++;
	}/* elseif  (!Is_Date($_POST['ClientSince'])) {
		$InputError = 1;
		prnMsg( _('The customer since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i] = 'ClientSince';
		$i++;
	}*/
	if ($InputError !=1){

		//	$SQL_ClientSince = FormatDateForSQL($_POST['ClientSince']);
		
		unset($_SESSION['HouseStockID']);
		prnMsg("UpdateSave");
	}
} 
	$ListCount=DB_num_rows($result);
	if ($ListCount==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		include('includes/footer.php');
		exit;
	}
if(!isset($_SESSION['HouseStockID'])) {		

	echo '<table class="selection">';
	echo '<tr>
			<td>选择项目:</td>
			<td><select name="Location"  id="Location" onChange="onLocations( this )" >';
	$sql = "SELECT `hdcode`, `ProjectName`, `address`, `LandArea`, `ProjectLeader`, `level`, `BuiltArea`, `StartDate`, `CompleteDate`, `AcceptanceDate`, `LandNo`, `remark`, `flag` FROM `housedata` WHERE flag=0";
	$LocnResult=DB_query($sql);

	while ($myrow=DB_fetch_array($LocnResult)){
		if (!isset($_POST['Location'])){
			$_POST['Location']=$myrow['loccode'] ;
		}
		if ($myrow['loccode']==$_POST['Location']){
			echo '<option selected="selected" value="' . $myrow['hdcode'] . '">' . $myrow['ProjectName'] . '</option>';
		} else {
			echo '<option value="' . $myrow['hdcode'] . '">' . $myrow['ProjectName'] . '</option>';
		}
			
	}
	echo '</select>
		</td>
		</tr>
			<tr>
				<td>选择栋:</td>
				<td><select  name="BuildNumber"  id="BuildNumber" >';
			for( $i=1;$i<18; $i++){
				if (isset($_POST['BuildNumber']) ){//AND in_array($row['id'], $_POST['BuildNumber'])) {
					echo '<option selected="selected" value="' . $i . '">' . $i.'-栋</option>';
				} else {
					echo '<option value="'  . $i . '">' . $i.'-栋</option>';
				}
			}
	
	echo '</select>
			</td>
		</tr>		<tr>
		<td>选择楼层:</td>
		<td><select  name="Level"  id="Level" >';

	for( $i=1;$i<=5; $i++){


		if (isset($_POST['Level']) ){//AND in_array($row['id'], $_POST['Level'])) {
			echo '<option selected="selected" value="' . $i . '">' . $i.'-层</option>';
		} else {
			echo '<option value="'  . $i . '">' . $i.'-层</option>';
		}

	}

	echo '</select>
		</td>
	</tr>';

	echo '<tr>
		<td>查询方式:</td>
		<td>
		<input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' /> 全部
		<input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />可售房源 
		</td>
	</tr>';

	echo '</table>
		<br />';
	echo'<div class="centre">
			<input type="submit" name="Search" value="查询" />';
			if (isset($_POST['Search'])){
				echo '<input type="submit" name="ExportExcel" value="导出Excel" />';
			}
			echo'</div>';

}
if (isset($_POST['Search'])OR isset($_POST['Go'])	OR isset($_POST['Next'])OR isset($_POST['Previous'])) {

	$FIRST=1;
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
	// if Search then set to first page
	$_POST['PageOffset'] = 1;
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

	echo '<table width="90%" cellpadding="4"  class="selection">
		<tr>
			<th>序号</th>
			<th>编号</th>
			<th>栋号</th>
			<th>楼层</th>
			<th>门牌号</th>
			<th>建筑面积</th>		
			<th>实际面积</th>
			<th>套内面积</th>
			<th>摘要</th>
			<th></th>
		</tr>';
	$RowIndex = 0;		
	if($ListCount <> 0 ) {

		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		while ($row=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

			if ($k==1){
				echo '<tr class="EvenTableRows">';
														
				$k=0;
			}else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			//$urlrow=array(`stockid`=>$row['stockid'] , `BuildNumber`=>$row['BuildNumber'] , `Units`=>$row['Units'] , `level`=>$row['level'] , `qty`=>$row['qty'] , `InsideArea`=>$row['InsideArea'] ,  `BuiltArea`=>$row['BuiltArea'] , `ActualArea`=>$row['ActualArea'],'remark'=>$row['remark'] ); <input  type="hidden" name="SelectHouse'.$row['stockid'].'" value="' .  $urlrow  . '" />
			echo'<td>'.($RowIndex+1).'</td>
				<td> 
				      <input tabindex="4" type="submit" name="Select" value="' .  $row['stockid']  . '" /></td>
				<td>' . $row['BuildNumber'] . '</td>
				<td>' . $row['level'] . '</td>	
				<td></td>
				<td>' . $row['BuiltArea'] . '</td>			
				<td>' . $row['ActualArea'] . '</td>	
				<td>' . $row['InsideArea'] . '</td>
				<td>' . $row['remark'] . '</td>
				<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?AllocTrans='.$row['stockid'].'">编辑</a></td>													
				</tr>';				
				$RowIndex++;
					
		}//end while			
		echo '<tr>									
				<td ></td>
				<td ></td>					
				<td ></td>	
				<td >总计</td>	
				<td ></td>
				<td ></td>
				<td class="number">'.locale_number_format(($TotalAmo),2).'</td>
				<td ></td>
				<td></td>
			</tr>';
		echo'</table>';	
	}	
	//end if results to show
	if (isset($ListPageMax) and $ListPageMax > 1) {
		echo '<p>&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': </p>';
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
		echo '<br />';
	}
}

if(isset($_SESSION['HouseStockID'])) {	
			
	if(!isset($_POST['Quotation'])) {
		$_POST['Quotation']=1;
	}
		$SQL="SELECT	`regid`,
					`registerno`,
					`custname`,
					`tag`,
					`sub`,
					`regdate`,
					`acctype`
				FROM `custname_reg_sub`
				WHERE regid NOT IN(	SELECT	`debtorno`	FROM	`debtorsmaster`	)
						AND (acctype=1 OR acctype=3 OR acctype=0)
						AND LENGTH(custname)>15";
		$Result=DB_query($SQL);
		echo '<table cellpadding="3" class="selection">
				<tr>
				<th  colspan="2">查找客户</th>
			</tr>';
		echo '<tr>
				<td >', _('Enter a partial Name'), ':</td>
				<td >
					<input type="text" name="RegCustName"  id="RegCustName" placeholder="输入编码、名称关键词筛选，然后选择供应商"  autocomplete="off"  list="CustCode"   maxlength="30" size="20" /> 
					<datalist id="CustCode"> ';		
						while ($row=DB_fetch_array($Result )){
							echo '<option value="' . $row['regid'] .':'.htmlspecialchars($row['custname'], ENT_QUOTES,'UTF-8', false) . ':'.$row['registerno'] . '"label="">';
						}
			echo'</datalist></td>
			</tr>';
		echo '<tr>
				
				<td  colspan="2">或输入注册码/身份证号:    <input maxlength="30" name="RegisterNo" pattern="[\w-]*" size="25" type="text" title="', _('If there is an entry in this field then customers with the text entered in their customer code will be returned') , '" value="'.$_POST['RegisterNo'] . '" /></td>
			</tr>
			<tr>
				
				<td colspan="2">或输入:银行账号<input maxlength="30" name="BankAct" size="25" type="text"  value="' . $_POST['BankAct'] . '"/></td>
			</tr>';
		echo '</table><br />';
		echo '<div class="centre">
			<input name="SearchCust" type="submit" value="查找客户" />
			
		</div>';	
		//----------------------<input name="Reset" type="submit" value="清空" />
 
	echo '<table class="selection">
			<tr>
				<th colspan="9">销售订单</th>
			</tr>
			<tr><th>编号</th>	
				<th>栋号</th>
				<th>楼层</th>				
				<th>门牌号</th>
				<th>面积单位</th>	
				<th>建筑面积</th>		
				<th>实际面积</th>
				<th>套内面积</th>
				<th>备注</th>
			
			</tr>';


		if($k==1) {
			echo '<tr class="OddTableRows">';
			$k=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k=1;
		}
		echo '<td>' . $_SESSION['HouseStockID']['stockid']  . '</td>
				<td>' . $_SESSION['HouseStockID']['BuildNumber'] . '</td>
				<td>' . $_SESSION['HouseStockID']['level'] . '</td>
				<td>' . $_SESSION['HouseStockID']['HouseNumber'] . '</td>
				<td>' . $_SESSION['HouseStockID']['Units'] . '</td>
				
				<td class="number">' . $_SESSION['HouseStockID']['BuiltArea'] . '</td>
				<td class="number">' . $_SESSION['HouseStockID']['ActualArea'] . '</td>
				<td class="number">'.$_SESSION['HouseStockID']['InsideArea'] . '</td>
				<td ><input type="text" name="Remark" value="' . $_POST['Remark'] . '" /></td>
		
			
			</tr>';

	echo '</table><br />';


	if (isset($_POST['SearchCust'])){
		if (!strpos($_POST['RegCustName'],':')){		
		
			
			$SQL="SELECT `regid`,  `custname`,registerno,bankaccount, `sub`, `regdate`, `acctype`, `tag`
			FROM `register_account_sub` 
			WHERE custname LIKE '".$_POST['RegCustName']."' OR registerno='".$_POST['RegisterNo']."' OR bankaccount='".$_POST['BankAct']."'";
			$OnEdit = 0;
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);
			$Error=0;
			if (isset($Row)){// && isset($_POST['New'])){
				
				if ($_POST['RegCustName']!='' && $Row['custname']!=$_POST['RegCustName']){
					//输入名和系统名不一样  ,提示错误停止
					$Error=-1;
					$msg.='客户:'.$_POST['RegCustName'].'和系统存在的名称不同,';
					//return array(0=>-1);
				}
				if ($_POST['RegisterNo']!='' && $Row['registerno']!=$_POST['RegisterNo']){
					//输入注册码和系统码不一样  ,提示错误停止
					$Error=-2;
					$msg.='注册码:'.$_POST['RegisterNo'].'和系统存在的注册码不同,';
				//	return array(0=>-2);
				}
				if ($Row['sub']!=''&&$Error==0){
					$_POST['Account']=$Row['sub'];
					prnMsg('你添加的客户'.$_POST['RegCustName'].'已经添加,编码:'.$Row['regid'].' 会计科目:'.$Row['sub'],'info');
					$CustData=$Row;
					//echo '<br /><div class="centre"><a href="Customers.php" >客户添加</a></div><br />';
					//include('includes/footer.php');
					//exit;
				}elseif($Error==-1|| $Error==-2) {
					//if ($Error==-1)
					prnMsg('你添加的'.$msg,'warn');
					
					echo '<br /><div class="centre"><a href="Customers.php" >客户添加</a></div><br />';
					include('includes/footer.php');
					exit;
					
				}else{
					//系统已经存在单位,但不完善 ,如果$SuppData不存在,��单位
					$CustData=$Row;
				}
			}else {//新客户
				$_POST['CustName']=$_POST['RegCustName'];
				$_POST['Address5']=$_POST['RegisterNo'];
				$_POST['Address4']=$_POST['BankAct'];

			}

		}else{//选择客户
			$OnEdit +=1;
			$rcnarr=explode(':',$_POST['RegCustName']);
			$_POST['CustName']=$rcnarr[1];
			$_POST['Address5']=$rcnarr[2];
			$_POST['RegID']=$rcnarr[0];
		}
		echo '<input type="hidden" name="Account" value="' . $_POST['Account'] . '" />';
		echo '<input type="hidden" name="Regid" value="' . $_POST['RegID'] . '" />';
		if ($Error!=1){
			//添加stockmoves 表检查
			
			if ( $_POST['RegID'] >0|| isset($_POST['RegID'])){
				$sql= "SELECT count(*) FROM `stockmoves` WHERE debtorno='" . $_POST['RegID'] . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$OnEdit+=1;
				
				}

				$sql= "SELECT COUNT(*) FROM debtortrans WHERE debtorno='" . $_POST['RegID'] . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$OnEdit+=1;
				
				}
			}
			if ( $_POST['Account']!=''&& isset($_POST['Account'])){
				$sql="SELECT count(* )FROM `gltrans` WHERE account='" . $_POST['Account'] . "'";
				$result = DB_query($sql);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$OnEdit+=1;
				}
			}
			
		}
		echo '<input type="hidden" name="OnEdit" value="' . $OnEdit . '" />';
	}
	// ----------End search for customers.	
	echo'<table class="selection">';


	echo '<tr>
			<td>' . _('Customer Name') . ':</td>
			<td>';
			if (isset($_POST['CustName'])&& strlen($_POST['CustName'])>=5){
				echo'<input tabindex="2" type="text" name="CustName" size="15" maxlength="20" value="'.$_POST['CustName'].'" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />
					<input  type="hidden" name="RegID" value="'.$_POST['RegID'].'" />';
			}else{
				echo'<input tabindex="2" type="text" name="CustName" size="15" maxlength="20" value="" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)]+$" />';	
			}
	echo'</td>
		</tr>
		<tr>
		<td>详细地址:</td>
			<td><input tabindex="3" type="text" name="Address1"  size="30" maxlength="30" /></td>
		</tr>

		<tr>
			<td>开户银行:</td>
			<td><input tabindex="5" type="text" name="Address3" size="25" maxlength="30"  /></td>
		</tr>
		<tr>
			<td>账号:</td>
			<td>';
			if (isset($_POST['Address4'])&& strlen($_POST['Address4'])>5){
				echo'<input tabindex="6" type="text" name="Address4" size="22" maxlength="20" pattern="^[a-zA-Z0-9]*\d{5,30}?"　 value="'.$_POST['Address4'].'"  '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
			}else{
				echo'<input tabindex="6" type="text" name="Address4" size="22" maxlength="20" value=""   pattern="^[a-zA-Z0-9]*\d{5,30}?"　 />';	
			}
	echo'</td>
			<td></td>
		</tr>
		<tr>
			<td>注册码/身份证号:</td>
			<td>';
			if (isset($_POST['Address5']) && strlen($_POST['Address5'])>9){
				echo'<input tabindex="7" type="text" name="Address5" size="22" maxlength="20" value="'.$_POST['Address5'].'" '.($_POST['OnEdit']>=1? "readOnly":"").'  />';
			}else{
				echo'<input tabindex="7" type="text" name="Address5" size="22" maxlength="20" value=""  pattern="^[a-zA-Z0-9]*\d{9,30}?"　/>';	
			}
	echo'</td>
		</tr>';

	echo'<tr>
			<td>' . _('Phone Number').':</td>';
	if (!isset($_POST['PhoneNo'])) {
		$_POST['PhoneNo']='';
	}
	echo '<td><input tabindex="16" type="tel" name="PhoneNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['PhoneNo'].'" /></td>
	</tr>';
	echo '<tr>
			<td>其他联系方式:</td>';
	if (!isset($_POST['FaxNo'])) {
	$_POST['FaxNo']='';
	}
	echo '<td><input tabindex="17" type="tel" name="FaxNo" pattern="[0-9+()\s-]*" size="22" maxlength="20" value="'. $_POST['FaxNo'].'" /></td>
		</tr>';


		if (!isset($_POST['Email'])) {
		$_POST['Email']='';
		}
	echo '<tr>
		<td>' . (($_POST['Email']) ? '<a href="Mailto:'.$_POST['Email'].'">' . _('Email').':</a>' : _('Email').':') . '</td>';
	//only display email link if there is an email address
	echo '<td><input tabindex="18" type="email" name="Email" placeholder="e.g. example@domain.com" size="40" maxlength="40" value="'. $_POST['Email'].'" /></td>
		</tr>';
	echo '<tr>
				<td>' . _('Sales person'). ':</td>
				<td><select name="SalesPerson">';
		$SalesPeopleResult = DB_query("SELECT salesmancode, salesmanname FROM salesman WHERE current=1");
		if(!isset($_POST['SalesPerson']) AND $_SESSION['SalesmanLogin']!=NULL ) {
			$_SESSION['Items'.$identifier]->SalesPerson = $_SESSION['SalesmanLogin'];
		}

		while ($SalesPersonRow = DB_fetch_array($SalesPeopleResult)) {
			if($SalesPersonRow['salesmancode']==$_SESSION['Items'.$identifier]->SalesPerson) {
				echo '<option selected="selected" value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			} else {
				echo '<option value="' . $SalesPersonRow['salesmancode'] . '">' . $SalesPersonRow['salesmanname'] . '</option>';
			}
		}

		echo '</select></td>
			</tr>';
	echo '<tr><td>订单/报价单:</td>
			<td><select name="Quotation">';
	if($_POST['Quotation']==1) {
		echo '<option selected="selected" value="1">订单</option>';
		echo '<option value="0">报价单</option>';
	} else {
		echo '<option value="1">订单</option>';
		echo '<option selected="selected" value="0">报价单</option>';
	}
	echo '</select></td></tr>';
	echo'<tr>
			<td>备注:</td>
			<td> <textarea  placeholder="在这里输入备注内容..." cols="45" rows="3" name="Remark" ></textarea></td>
		</tr>';
	echo '<tr>
		<td>' .  _('Confirmed Order Date') .':</td>
		<td><input class="date" alt="'.$_SESSION['DefaultDateFormat'].'" type="text" size="15" maxlength="14" name="ConfirmedDate" value="' . $_SESSION['Items'.$identifier]->ConfirmedDate . '" /></td>
	</tr>';

	echo'</table>';

		echo '<br />
			<div class="centre">
				<input tabindex="20" type="submit" name="UpdateSave" value="确认保存" />';
}

if(isset($_SESSION['HouseStockID'])) 

echo '<input type="submit" name="ClearCache" value="清除缓存" /></div>';
echo'</div>
		</form>';

	include('includes/footer.php');

 
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
   *                           array  format      设���格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        ��置加粗样式，例如['A1', 'A2']
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
		$sheet->setCellValue('A'.$k, "公司名���:". (string)$titledata['coyname']); 
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
		$activeSheet->getColumnDimension('C')->setWidth(30);
		$activeSheet->getColumnDimension('D')->setWidth(15);
		$activeSheet->getColumnDimension('H')->setWidth(30);
		$activeSheet->getColumnDimension('I')->setWidth(20);
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

		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($k);			
			if ($_column==1){
				//  序号列 
			
				$sheet->setCellValue($cellName.($k), $k-$rw); 
			}else{
			
				if ($_column==2){					
					$sheet->setCellValue($cellName.($k), (string)$row['stockid']);
				}elseif ($_column==3){					
					$sheet->setCellValue($cellName.($k), (string)$row['description']);
				}elseif ($_column==4){					
					$sheet->setCellValue($cellName.($k), (string)$row['loccode']);
				}elseif ($_column==5){					
					$sheet->setCellValue($cellName.($k), (string)$row['categoryid']);
				}elseif ($_column==6){					
					$sheet->setCellValue($cellName.($k), (float)$row['qoh']);
				}elseif ($_column==7){					
					$sheet->setCellValue($cellName.($k), (float)$row['qtycounted']);
				}elseif ($_column==8){					
					$sheet->setCellValue($cellName.($k), (string)$row['reference']);
				}elseif ($_column==9){					
					$sheet->setCellValue($cellName.($k), (string)$row['stockcheckdate']);
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
	//注意	createWriter($spreadsheet, 'Xls') //第二个���数首字母必须大写
	$writer->save('php://output'); 

}	

?>