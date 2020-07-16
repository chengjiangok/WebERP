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

$Title=_('Stock Check Sheets');
include('includes/header.php');
/*
If (isset($_POST['PrintPDF'])){

	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Stock Count Sheets'));
	$pdf->addInfo('Subject',_('Stock Count Sheets'));
	$FontSize=10;
	$PageNumber=1;
	$line_height=30;

	//First off do the stock check file stuff 

	if ($_POST['MakeStkChkData']=='AddUpdate'){
		$sql = "DELETE stockcounts
				FROM stockcounts
				INNER JOIN stockmaster ON stockcounts.stockid=stockmaster.stockid
				WHERE stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockcounts.loccode='" . $_POST['Location'] . "'";

		$result = DB_query($sql,'','',false,false);
		if (DB_error_no() !=0) {
			$Title = _('Stock Freeze') . ' - ' . _('Problem Report') . '.... ';
			include('includes/header.php');
			prnMsg(_('The old quantities could not be deleted from the freeze file because') . ' ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
		  			echo '<br />' . $sql;
			}
			include('includes/footer.php');
			exit;
		}

		$sql = "INSERT INTO stockcounts (stockid,
										  loccode,
										  qoh,
										  stockcheckdate)
				SELECT locstock.stockid,
					loccode ,
					locstock.quantity,
					'" . Date('Y-m-d') . "'
				FROM locstock INNER JOIN stockmaster
				ON locstock.stockid=stockmaster.stockid
				WHERE locstock.loccode='" . $_POST['Location'] . "'
				AND stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
				AND stockmaster.mbflag!='A'
				AND stockmaster.mbflag!='K'
				AND stockmaster.mbflag!='G'
				AND stockmaster.mbflag!='D'";

		$result = DB_query($sql,'','',false,false);
		if (DB_error_no() !=0) {
			$Title = _('Stock Freeze - Problem Report');
			include('includes/header.php');
			prnMsg(_('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
		  			echo '<br />' . $sql;
			}
			include('includes/footer.php');
			exit;
		} else {
			$Title = _('Stock Check Freeze Update');
			include('includes/header.php');
			echo '<p><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Print Check Sheets') . '</a>';
			prnMsg( _('Added to the stock check file successfully'),'success');
			include('includes/footer.php');
			exit;
		}
	}


	$SQL = "SELECT stockmaster.categoryid,
				 stockcounts.stockid,
				 stockmaster.description,
				 stockmaster.decimalplaces,
				 stockcategory.categorydescription,
				 stockcounts.qoh
			 FROM stockcounts INNER JOIN stockmaster
			 ON stockcounts.stockid=stockmaster.stockid
			 INNER JOIN stockcategory
			 ON stockmaster.categoryid=stockcategory.categoryid
			 WHERE stockmaster.categoryid IN ('". implode("','",$_POST['Categories'])."')
			 AND (stockmaster.mbflag='B' OR mbflag='M')
			 AND stockcounts.loccode = '" . $_POST['Location'] . "'";
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly']==true){
		$SQL .= " AND stockcounts.qoh<>0";
	}

	$SQL .=  " ORDER BY stockmaster.categoryid, stockmaster.stockid";

	$InventoryResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Stock Sheets') . ' - ' . _('Problem Report') . '.... ';
		include('includes/header.php');
		prnMsg( _('The inventory quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg(),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		if ($debug==1){
		  	echo '<br />' . $SQL;
		}
		include ('includes/footer.php');
		exit;
	}
	if (DB_num_rows($InventoryResult) ==0) {
		$Title = _('Stock Count Sheets - Problem Report');
		include('includes/header.php');
		prnMsg(_('Before stock count sheets can be printed, a copy of the stock quantities needs to be taken - the stock check freeze. Make a stock check data file first'),'error');
		echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
		include('includes/footer.php');
		exit;
	}

	include ('includes/PDFStockCheckPageheader.php');

	$Category = '';

	While ($InventoryCheckRow = DB_fetch_array($InventoryResult,$db)){

		if ($Category!=$InventoryCheckRow['categoryid']){
			$FontSize=12;
			if ($Category!=''){ //Then it's NOT the first time round 
				//draw a line under the CATEGORY TOTAL
				$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);
				$YPos -=(2*$line_height);
			}

			$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,260-$Left_Margin,$FontSize,$InventoryCheckRow['categoryid'] . ' - ' . $InventoryCheckRow['categorydescription'], 'left');
			$Category = $InventoryCheckRow['categoryid'];
		}

		$FontSize=10;
		$YPos -=$line_height;

		if (isset($_POST['ShowInfo']) and $_POST['ShowInfo']==true){

			$SQL = "SELECT SUM(salesorderdetails.quantity - salesorderdetails.qtyinvoiced) AS qtydemand
			   		FROM salesorderdetails INNER JOIN salesorders
			   		ON salesorderdetails.orderno=salesorders.orderno
			   		WHERE salesorders.fromstkloc ='" . $_POST['Location'] . "'
			   		AND salesorderdetails.stkcode = '" . $InventoryCheckRow['stockid'] . "'
			   		AND salesorderdetails.completed = 0
			   		AND salesorders.quotation=0";

			$DemandResult = DB_query($SQL,'','',false, false);

			if (DB_error_no() !=0) {
	 			$Title = _('Stock Check Sheets - Problem Report');
		  		include('includes/header.php');
		   		prnMsg( _('The sales order demand quantities could not be retrieved by the SQL because') . ' ' . DB_error_msg(), 'error');
	   			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
	   			if ($debug==1){
		  				echo '<br />' . $SQL;
		   		}
		   		include('includes/footer.php');
	   			exit;
			}

			$DemandRow = DB_fetch_array($DemandResult);
			$DemandQty = $DemandRow['qtydemand'];

			//Also need to add in the demand for components of assembly items
			$sql = "SELECT SUM((salesorderdetails.quantity-salesorderdetails.qtyinvoiced)*bom.quantity) AS dem
						   FROM salesorderdetails INNER JOIN salesorders
						   ON salesorders.orderno = salesorderdetails.orderno
						   INNER JOIN bom
						   ON salesorderdetails.stkcode=bom.parent
						   INNER JOIN stockmaster
						   ON stockmaster.stockid=bom.parent
						   WHERE salesorders.fromstkloc='" . $_POST['Location'] . "'
						   AND salesorderdetails.quantity-salesorderdetails.qtyinvoiced > 0
						   AND bom.component='" . $InventoryCheckRow['stockid'] . "'
						   AND stockmaster.mbflag='A'
						   AND salesorders.quotation=0";

			$DemandResult = DB_query($sql,'','',false,false);
			if (DB_error_no() !=0) {
				prnMsg(_('The demand for this product from') . ' ' . $myrow['loccode'] . ' ' . _('cannot be retrieved because') . ' - ' . DB_error_msg(),'error');
				if ($debug==1){
		   			echo '<br />' . _('The SQL that failed was') . ' ' . $sql;
				}
				exit;
			}

			if (DB_num_rows($DemandResult)==1){
	  			$DemandRow = DB_fetch_row($DemandResult);
	  			$DemandQty += $DemandRow[0];
			}

			$LeftOvers = $pdf->addTextWrap(350,$YPos,60,$FontSize,locale_number_format($InventoryCheckRow['qoh'], $InventoryCheckRow['decimalplaces']), 'right');
			$LeftOvers = $pdf->addTextWrap(410,$YPos,60,$FontSize,locale_number_format($DemandQty,$InventoryCheckRow['decimalplaces']), 'right');
			$LeftOvers = $pdf->addTextWrap(470,$YPos,60,$FontSize,locale_number_format($InventoryCheckRow['qoh']-$DemandQty,$InventoryCheckRow['decimalplaces']), 'right');

		}

		$LeftOvers = $pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,$InventoryCheckRow['stockid'], 'left');

		$LeftOvers = $pdf->addTextWrap(150,$YPos,200,$FontSize,$InventoryCheckRow['description'], 'left');


		$pdf->line($Left_Margin, $YPos-2,$Page_Width-$Right_Margin, $YPos-2);

		if ($YPos < $Bottom_Margin + $line_height){
		   $PageNumber++;
		   include('includes/PDFStockCheckPageheader.php');
		}

	} //end STOCK SHEETS while loop 

	$pdf->OutputD($_SESSION['DatabaseName'] . '_Stock_Count_Sheets_' . Date('Y-m-d') .'.pdf');
}*/
if (isset($_GET['Action'])){ 
	$_POST['BatchID']=$_GET['Action'];
}
	$sql="SELECT `categoryid`, stockcategorylocation.`loccode`,locationname, `categorydescription` 
				FROM `stockcategorylocation` 
				LEFT JOIN locations ON locations.loccode=stockcategorylocation.loccode
				WHERE stockcategorylocation.loccode IN (SELECT `loccode` FROM `locationusers` WHERE  locationusers.userid = '".$_SESSION['UserID']."') 
				ORDER BY stockcategorylocation.`loccode`,`categoryid`";
	
	$resultCat = DB_query($sql);
	$StockCategory=[];
	while ($row=DB_fetch_array($resultCat)){
		$StockCategory[]=array("id"=>$row["categoryid"],"loc"=>$row["loccode"],"des"=>$row["categorydescription"]);
		$CategoryLocation[$row['categoryid']]=array($row['locationname'],$row['loccode']);
	
	}

	$StockCategoryJson=json_encode($StockCategory,JSON_UNESCAPED_UNICODE);	 
	$flagtxt=[0=>"待盘点",1=>"盘点",2=>"批准"];
if(!isset($_POST['ExportExcel'])) {
	echo  ' <input type="hidden" id="StockCategoryJson" name="StockCategoryJson" value=' . $StockCategoryJson . ' />';
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="'
		. _('print') . '" alt="" />' . ' ' . $Title . '</p><br />';

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="BatchID" value="' . $_POST['BatchID'] . '" />';
	echo '<table class="selection">';
	echo '<tr>
			<td>' . _('For Inventory in Location') . ':</td>
			<td><select name="Location"  id="Location" onChange="onLocations( this )" >';
	$sql = "SELECT locations.loccode,
	               locationname 
				   FROM locations 
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			ORDER BY locationname";
	$LocnResult=DB_query($sql);

	while ($myrow=DB_fetch_array($LocnResult)){
		if (!isset($_POST['Location'])){
			$_POST['Location']=$myrow['loccode'] ;
		}
		if ($myrow['loccode']==$_POST['Location']){
			echo '<option selected="selected" value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
		}
			
	}
	echo '</select>
		</td>
		</tr>
			<tr>
				<td>选择存货分组:</td>
				<td><select  name="Categories"  id="Categories" >';

	foreach( $StockCategory as $row){
		
		if ($row['loc']==$_POST['Location']){
			if (isset($_POST['Categories']) AND in_array($row['id'], $_POST['Categories'])) {
				echo '<option selected="selected" value="' . $row['id'] . '">' . $row['id'].'-'. $row['des'] . '</option>';
			} else {
				echo '<option value="' . $row['id'] . '">' .$row['id'].'-'. $row['des'] . '</option>';
			}
		}
	}
	
	echo '</select>
			</td>
		</tr>';


	echo '<tr>
		<td>查询方式:</td>
		<td>
		  <input type="radio" name="queryad" value="0" '.($_POST['queryad'] == 0 ?'checked':''). ' />完成  
		  <input type="radio" name="queryad" value="1"  '.($_POST['queryad'] == 1?'checked ':''). ' />盘点
		</td>
	   </tr>';
	echo '<tr>
			<td>非零数量的物料:</td>
			<td>';
	if (isset($_POST['NonZerosOnly']) and $_POST['NonZerosOnly'] == false){
			echo '<input type="checkbox" name="NonZerosOnly" value="false" />';
	} else {
			echo '<input type="checkbox" name="NonZerosOnly" value="true" />';
	}

	echo '</td>
		</tr>
		</table>
		<br />';
}
if (isset($_GET['Action'])||isset($_POST['ExportExcel'])){

	
	$SQL="SELECT	`batchid`,
					`loccode`,
					stockcounts.`categoryid`,
					stockmaster.description,
					stockcounts. `stockid`,
					`qoh`,
					`qtycounted`,
					`reference`,
					`edituser`,
					`auditer`,
					`stockcheckdate`,
					`auditdate`,
					`authorised`,
					`flag`
				FROM
					`stockcounts`
					LEFT JOIN stockmaster ON stockmaster.stockid=stockcounts.stockid
				WHERE
					batchid =".$_POST['BatchID']."
					ORDER BY stockcounts.stockid";
							// categoryid='" . $_POST['StkCat'] . "' 
	$ResultCounts = DB_query($SQL);
}
if (isset($_POST['ExportExcel'])){

	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	$TitleData=array("Title"=>'对账单',"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"AmountTotal"=>json_encode($AmoTotal));	

	 $Header=array( '序号', '物料编码', '物料名称', '仓库', '物料组', '账面数', '盘点数', '摘要', '盘点日期' );	
	DB_data_seek($ResultCounts,0);	  
	ExportExcel($ResultCounts,$Header,$TitleData,$options);
}elseif ($_POST['MakeStkChkNew']){
	//$sql = "TRUNCATE TABLE stockcounts";
	$sql="SELECT  `confvalue`  FROM `myconfig` WHERE confname='StockCounts'";
	$result = DB_query($sql);
	$row=DB_fetch_assoc($result);
	$stockcounts=1;
	if (!empty($row['confvalue'])) 
		 $stockcounts=$row['confvalue'];
	$sql="SELECT `batchid`, `stkcheckdate`, `closed`
	      FROM `stockcheckfreeze` 
		  WHERE loccode= ".$_POST['Location'] ."  AND categoryid='".$_POST['Categories']."' ORDER BY  `stkcheckdate` DESC LIMIT 1";
	$result = DB_query($sql);
	$row=DB_fetch_assoc($result);
	$stkcheck=0;
	if (empty($row['stkcheckdate'])){
		$stkcheck=1;
	}else{
	
		if (DateDiff (Date('Y-m-d'),$row['stkcheckdate'], "m") >$stockcounts)
			$stkcheck=1;
	}
	if ($stkcheck==1){
		$sql="INSERT INTO `stockcheckfreeze`(	`loccode`,
												`categoryid`,
												`stkcheckdate`,										
												`authorised`,
												`closed`,
												`narrative`,
												`initiator`,										
												`version`)
											VALUES( ".$_POST['Location'] .",
													'".$_POST['Categories']."',
													'" . Date('Y-m-d') . "',
													0,
													0,
													'',
													'".$_SESSION['UserID']."',
													0)";
		$result = DB_query($sql);

		if(DB_affected_rows($result)>0){//插入成功
					
			$batchid=DB_Last_Insert_ID($db,'stockcheckfreeze','batchid');
			if ($_POST['NonZerosOnly'])
			   $SQ=" AND locstock.quantity<> 0 ";
			$sql = "INSERT INTO stockcounts (	`batchid`,
													`loccode`,
													`categoryid`,
													`stockid`,
													`qoh`,
													`qtycounted`,
													`reference`,
													`edituser`,
													`auditer`,
													`stockcheckdate` ,
													auditdate)
						SELECT ".$batchid.",
								locstock.loccode,
								LEFT(locstock.stockid,3) categoruid,
								locstock.stockid,
								locstock.quantity,
								0,
								'',
								'".$_SESSION['UserID']."',
								'',
								'" . Date('Y-m-d') . "',
								'0000-00-00'
								
						FROM locstock,
								stockmaster
						WHERE locstock.stockid=stockmaster.stockid 
						AND locstock.loccode='" . $_POST['Location'] . "' 
						AND LEFT(stockmaster.stockid,3)='".$_POST['Categories']."' ".$SQ. "	AND stockmaster.mbflag!='A' 
						AND stockmaster.mbflag!='K' 
						AND stockmaster.mbflag!='D'";
						//   AND stockmaster.categoryid IN ('".$_POST['Categories']."')
			// prnMsg($sql);
			$result = DB_query($sql,'','',false,false);
		}
		if (DB_error_no() !=0) {
			$Title = _('Stock Count Sheets - Problem Report');
			include('includes/header.php');
			prnMsg(_('The inventory quantities could not be added to the freeze file because') . ' ' . DB_error_msg(),'error');
			echo '<br /><a href="' . $RootPath . '/index.php">' . _('Back to the menu') . '</a>';
			if ($debug==1){
						echo '<br />' . $sql;
			}
			include('includes/footer.php');
			exit;
		}else{
			prnMsg(	$batchid.'号检查表创建成功！','info');
		}
	}else{
		prnMsg($_POST['Location'].$_POST['Categories']."盘点表已经产生，盘点周期".$stockcounts."个月",'info');
	}

	

}
		
//if (isset($_POST['Search'])||isset($_GET['Del'])||isset($_GET['Action'])){
	    $sql="SELECT  batchid,  `loccode`,
												`categoryid`,
												`stkcheckdate`,
												`auditdate`,
												`authorised`,
												`closed`,
												`narrative`,
												`initiator`,
												`audituser`,
												`version`
									FROM	`stockcheckfreeze`";

		$Result = DB_query($sql);
	
		echo '<table class="selection">';
		echo '<tr>		
				<th colspan="7">盘点表</th>
		
			</tr>
			<tr>
				<th>序号</th>
				<th>表编号</th>
				<th>所属仓库</th>
				<th>所属分组</th>
				<th>统计</th>
				<th>状态</th>
				<th colspan="2"></th>
			</tr>';
		$RowCount=1;
		while ($StkRow = DB_fetch_array($Result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			echo '	<td>' . $RowCount. '</td>
					<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'" >' . $StkRow['batchid'] . '</td>
					<td>' . $StkRow['loccode'] . '</td>
					<td>' . $StkRow['categoryid'] . '</td>
					<td>' . $StkRow['cout'] . '</td>
					<td>' .$flagtxt[$StkRow['closed'] ]. '</td>
					<td><a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?Action='.$StkRow['batchid'].'">' . _('Edit') . '</a>';
			if ($StkRow['closed']==0)
				echo'<a href="'.htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?Del='.$StkRow['batchid'].'"  onclick="  return confirm(\'你确认要删除 '.$StkRow['batchid'].'号盘点表！\') "; >' . _('Delete') .'</a>';
			echo'</td>
				</tr>';
			$RowCount++;
		}
		echo '</table><br />';
	
	
//}
echo '<div class="center"><a href="' . $RootPath . '/StockCounts.php">开始盘点</a></div><br />';
	echo'<div class="centre">
			<input type="submit" name="Search" value="查询检查表" />
		
			<input type="submit" name="MakeStkChkNew" value="' . _('Make new stock check data file')  . '" />';
	if (isset($_GET['Action'])){
		echo '<input type="submit" name="ExportExcel" value="导出Excel" />';
	}
echo'</div>';
if (isset($_GET['Action'])){


	$RowCount=1;
	echo '<table class="selection">';
	echo '<tr>
			<th colspan="8">'.$_GET['Action'].'号—盘点���</th>
		</tr>
		<tr>
			<th>' . _('Stock Code') . '</th>
			<th>' . _('Description') . '</th>
			<th>账面数量</th>		
			<th>' . _('Reference') . '</th>
			<th>盘点日期</th>
			<th>状态</th>
		</tr>';
	while ($Row = DB_fetch_array($ResultCounts)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
	
		echo   '<td>' . $Row['stockid'] . '</td>
				<td>' . $Row['description'] . '</td>
				<td><input type="text" name="Qty_' . $RowCount . '" maxlength="10" size="10" /></td>
				<td>' . $Row['remark'] . '</td>
				<td>' . $Row['stockcheckdate'] . '</td>
				<td>' . $Row['flag'] . '</td>
			</tr>';
		$RowCount++;
	}

	echo '</table>';
	echo'<div class="centre">
	<input type="submit" name="Update" value="确认保存" />

	</div>';
}
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
	//注意	createWriter($spreadsheet, 'Xls') //第二个参数首字母必须大写
	$writer->save('php://output'); 

}	

?>