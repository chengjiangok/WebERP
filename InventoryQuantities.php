<?php
/*
 * @Descripttion: WebERP开发升级二次开发，需核对数据
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-07-05 15:20:07
 */ 
/* $Id: InventoryQuantities.php $ */

 
// InventoryQuantities.php - Report of parts with quantity. Sorts by part and shows
// all locations where there are quantities of the part
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
include('includes/session.php');
echo'<script type="text/javascript">
	function onStockCat(s){      
	
		var jsn=document.getElementById("StockCategoryJson").value;	
		console.log(jsn);
		var selectobj=document.getElementById("StockCategory");
		var obj= JSON.parse(jsn);
		var temp = []; 	
		var objloc=[];
		selectobj.options.length=0; 
			
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
				
			//document.getElementById("ToSelectAct"+R).value=jsonStr;	
	}


	function   radioquery(s)  {
		console.log(s.value);
		var a=document.getElementById("AfterDate");  
		var b=document.getElementById("BeforDate");     
		var p=document.getElementById("SelectPrd");     
		//var r=document.getElementById("prdrange"); 

		if (s.value==2){
			a.disabled=true;    
			b.disabled=true;
			//r.disabled=false;
			p.disabled=false;
		}else { 
			p.disabled=true;	
			//r.disabled=true;			
			b.disabled=false;		
			a.disabled=false;
		}
		var i=document.getElementById("InvNo");
			i.value="";

	}
	function   onPeriod(s)  {
	
	//	var p=document.getElementById("SelectPrd"); 
		var b=document.getElementById("BeforDate");   
		var a=document.getElementById("AfterDate");  
		var r2=document.getElementById("rdoquery2");
		//var r1=document.getElementById("rdoquery1");
		r2.checked=true;
		b.disabled=true;
		a.disabled=true;
	}
	function   OnClickDate(s)  {
	
		var p=document.getElementById("SelectPrd"); 
		var b=document.getElementById("BeforDate");   
		var a=document.getElementById("AfterDate");  
		//var r2=document.getElementById("rdoquery2");
		var r1=document.getElementById("rdoquery1");
		r1.checked=true;
		p.disabled=true;
		b.disabled=false;
		a.disabled=false;
			
	}
</script>';
$sql="SELECT `categoryid`, stockcategorylocation.`loccode`,locationname, `categorydescription` 
       FROM `stockcategorylocation` 
	   LEFT JOIN locations ON locations.loccode=stockcategorylocation.loccode
	   WHERE stockcategorylocation.loccode IN (SELECT `loccode` FROM `locationusers` WHERE  locationusers.userid = '".$_SESSION['UserID']."') 
	   ORDER BY stockcategorylocation.`loccode`,`categoryid`";
//WHERE loccode=11
$resultCat = DB_query($sql);
$StockCategory=[];
while ($row=DB_fetch_array($resultCat)){
	$StockCategory[]=array("id"=>$row["categoryid"],"loc"=>$row["loccode"],"des"=>$row["categorydescription"]);
	$CategoryLocation[$row['categoryid']]=array($row['locationname'],$row['loccode']);
	
}
//print_r($CategoryLocation);
$StockCategoryJson=json_encode($StockCategory,JSON_UNESCAPED_UNICODE);	 
echo  ' <input type="hidden" id="StockCategoryJson" name="StockCategoryJson" value=' . $StockCategoryJson . ' />';
//echo  strlen($StockCategoryJson);
$period=DateGetPeriod(date("Y-m-d"));
if (!isset($_POST['SelectPrd'])){ 	
	$_POST["SelectPrd"]=$period;
}
if (!isset($_POST['UnitsTag'])){ 	
	if (isset($_SESSION['SelectInv'])){
	
		$_POST["UnitsTag"]=$_SESSION['SelectInv'][1];
	}else{
		$_POST["UnitsTag"]=$_SESSION['period'];
	}
	
}


if (!isset($_POST['query'])){
	$_POST['query']=2;

}
if (!isset($_POST['AfterDate']))
	$_POST['AfterDate']=FormatDateForSQL(date('Y-01-01'));//FormatDateForSQL($_SESSION['StockStartRunDate']);
if (!isset($_POST['BeforDate']))
	$_POST['BeforDate']=FormatDateForSQL(date('Y-m-d'));
	$ThatYear=1; 
if ($_POST['query']==2) {  
	//选择期间查询

	$endprd=$_POST['SelectPrd'];
		
	$BeforDate=PeriodGetDate($endprd);
	$AfterDate=date("Y-01-01",strtotime(PeriodGetDate($endprd))); 
	
		$ThatYear=2;
}else{

	$firstprd=DateGetPeriod($_POST['AfterDate']);
	$endprd=DateGetPeriod($_POST['BeforDate']);
	$AfterDate=$_POST['AfterDate'];	
	$BeforDate=$_POST['BeforDate'];	
	if(date("Y-01-01",strtotime($_POST['AfterDate']))!=$_POST['AfterDate']){
	
		$StartDate=date("Y-01-01",strtotime($_POST['AfterDate']));
	}
	if(date("Y-m-d")!=$_POST['BeforDate']){
	
		$EndDate=date("Y-m-d");
	}
	$JanrDate=date("Y-01-01",strtotime($_POST['AfterDate']));

	$EndofLastPeriod=date("Y-01-01",strtotime($_POST['AfterDate']))-1;	
}
$prd=DateGetPeriod(date("Y-01-01"))-1;
if (date("m")>=2&& $prd>=12){
	
	$SQL="SELECT COUNT(*) cut FROM `locstockdetails` WHERE period=".$prd;
	$result=DB_query($SQL);
	$Row=DB_fetch_assoc($result);
	if ($Row['cut']==0){
		//echo "没��期初余额";
		$sdate=date("Y-01-01",strtotime(PeriodGetDate($prd-11)));
		$edate=date("Y-12-t",strtotime($sdate));
		$SQL="SELECT	a.`loccode`,
						a.`stockid`,
						ROUND(a.`quantity`, b.decimalplaces) qty,
						ROUND(e.`quantity`, b.decimalplaces) endoflastqty,
						b.decimalplaces
					FROM
						`locstock` a
					LEFT JOIN stockmaster b ON
						a.stockid = b.stockid
					LEFT JOIN locstockdetails e ON
						a.stockid = e.stockid AND a.loccode = e.loccode AND period = ".$prd;
		$result=DB_query($SQL);
		while($row=DB_fetch_array($result)){
			$inqty=0;
			$outqty=0;
			$endoflastqty=0;
			$SQL="SELECT	
					 SUM(CASE WHEN `type` = 17 OR `type` = 26 THEN `qty` ELSE 0 END) inqty, 
					 SUM(CASE WHEN `type` = 10 OR `type` = 28 OR `type` = 39 THEN `qty` ELSE 0 END) outqty 
						FROM
							`stockmoves` 
						WHERE stockid='".$row['stockid']."'
						   AND trandate>='".$sdate."' AND trandate<='".$edate."'
						   AND loccode='".$row['loccode']."'";
			  //	echo $SQL."<br/>";
			$Result=DB_query($SQL);
			$Row=DB_fetch_assoc($Result);
			$inqty=round($Row['inqty'],$row['decimalplaces']);
			$outqty=round($Row['outqty'],$row['decimalplaces']);
			//
			if (!empty($row['endoflastqty'])){
				$endoflastqty=$row['endoflastqty'];
			}
			$EndofLastQty=	round($endoflastqty+$inqty+$outqty,$row['decimalplaces']);
			//echo '==================='.$endoflastqty.'+'.$inqty.'+'.$outqty,"<br/>";
			$SQL="INSERT INTO `locstockdetails`(	`stockid`,
													`loccode`,
													`period`,
													`inqty`,
													`outqty`,
													`quantity`,
													`flag`   
												)
												VALUES(
													'".$row['stockid']."',
													'".$row['loccode']."',
													'".$prd."',
													'".$inqty."',
													'".$outqty."',
													'".$EndofLastQty."',
													0)";
				//	echo $SQL."<br/>";
		 	$Result=DB_query($SQL);
		}
	}
}
//echo $_SESSION['startperiod'];

    
if (isset($_POST['Search']) OR isset($_POST['PrintPDF']) OR isset($_POST['ExportExcel']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {  

	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg (_('Stock description keywords have been used in preference to the Stock code extract entered'), 'info');
	}

	$SQL="SELECT	a.`loccode`,
					c.locationname,
					a.`stockid`,
					b.description,
					a.`quantity`,
					e.`quantity` endoflastqty,
					`reorderlevel`,
					`bin`,
					b.decimalplaces,
					b.categoryid,				
					b.units,
					b.mbflag
				FROM	`locstock` a
				LEFT JOIN stockmaster b ON
					a.stockid = b.stockid
				LEFT JOIN locations c ON
					a.loccode = c.loccode
				INNER JOIN locationusers d ON
					a.loccode = d.loccode
				LEFT JOIN locstockdetails e ON a.stockid=e.stockid AND a.loccode=e.loccode AND period='".$EndofLastPeriod."'
				WHERE   categoryid IN (SELECT `loccode`FROM `stockcategorylocation`	WHERE  loccode ='" . $_POST['Locations'] . "'  )
					AND d.userid =  '".$_SESSION['UserID']."'" ;
					// categoryid IN (SELECT `categoryid`FROM `stockcategorylocation`
	if (isset($_POST['StockCategory']) AND $_POST['StockCategory']!="All") {
           $SQL.=" AND a.stockid  LIKE '".$_POST['StockCategory']."%' "; 

	} 
	if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
		//insert wildcard characters in spaces
		$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
		$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
	
			$SQL.=" AND  CONCAT(b.description,b.longdescription) " . LIKE . " '$SearchString' ";
		
	} 
	if (isset($_POST['StockCode']) AND mb_strlen($_POST['StockCode'])>0) {
		$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
	
			$SQL .= "  AND a.stockid " . LIKE . " '" . $_POST['StockCode'] . "%' ";

	}
	$SQL.="	ORDER BY  b.stockid  ";
	$ErrMsg = _('No stock items were returned by the SQL because');
	$DbgMsg = _('The SQL that returned an error was');

	$SearchResult = DB_query($SQL, $ErrMsg, $DbgMsg);
	
	if (DB_num_rows($SearchResult) == 0) {
		//include('includes/header.php');
		prnMsg(_('No stock items were returned by this search please re-enter alternative criteria to try again'), 'info');
		include('includes/footer.php');
		exit;
	}
	//unset($_POST['Search']);
	
}
 //prnMsg($SQL);
if (isset($_POST['Search']) OR isset($_POST['PrintPDF']) OR isset($_POST['ExportExcel'])){
	if(isset($_POST['ExportExcel'])) {
		
		 //prnMsg($_POST['query'].'-'.$_POST['BankAccount']);
			$options = array("print"=>true);//,"setWidth"=>$setWidth);
			$FileName ="库存数量表_". date('Y-m-d', time()).rand(1000, 9999);
			$TitleData=array("Title"=>'库存数量表',"FileName"=>$FileName,"TitleDate"=>$dt,"coyname"=>$_SESSION['CompanyRecord'][1]['coyname'],"Units"=>"元","k"=>3,"ListCount"=>$ListCount,"AmountTotal"=>json_encode($AmoTotal));	
			exportExcelIQ($SearchResult,$CategoryLocation,$TitleData,$options);
		   exit;
			
	}
		

	if (DB_error_no() !=0) {
		$Title = _('Inventory Quantities') . ' - ' . _('Problem Report');
	    include('includes/header.php');
		 prnMsg( _('The Inventory Quantity report could not be retrieved by the SQL because') . ' '  . DB_error_msg(),'error');
		 echo '<br /><a href="' .$RootPath .'/index.php">' . _('Back to the menu') . '</a>';
		 if ($debug==1){
			echo '<br />' . $sql;
		 }
		 include('includes/footer.php');
		 exit;
	  }
	  if (DB_num_rows($SearchResult)==0){
			  $Title = _('Print Inventory Quantities Report');
		  	include('includes/header.php');
			  prnMsg(_('There were no items with inventory quantities'),'error');
		    	echo '<br /><a href="'.$RootPath.'/InventoryQuantities.php">' . _('Back to the menu') . '</a>';
			  include('includes/footer.php');
			  exit;
	  }

}
if (isset($_POST['PrintPDF'])){
	include('includes/PDFStarter.php');
	$pdf->addInfo('Title',_('Inventory Quantities Report'));
	$pdf->addInfo('Subject',_('Parts With Quantities'));
	$FontSize=9;
	$PageNumber=1;
	$line_height=12;

	$Xpos = $Left_Margin+1;
	$WhereCategory = ' ';
	$CatDescription = ' ';

	PrintHeader($pdf,
			$YPos,
			$PageNumber,
			$Page_Height,
			$Top_Margin,
			$Left_Margin,
			$Page_Width,
			$Right_Margin,
			$CatDescription);

	$FontSize=8;

	$holdpart = " ";
	While ($myrow = DB_fetch_array($SearchResult,$db)){
	if ($myrow['stockid'] != $holdpart) {
		$YPos -=(2 * $line_height);
		$holdpart = $myrow['stockid'];
	} else {
		$YPos -=($line_height);
	}

	// Parameters for addTextWrap are defined in /includes/class.pdf.php
	// 1) X position 2) Y position 3) Width
	// 4) Height 5) Text 6) Alignment 7) Border 8) Fill - True to use SetFillColor
	// and False to set to transparent

		$pdf->addTextWrap(50,$YPos,100,$FontSize,$myrow['stockid'],'',0);
		$pdf->addTextWrap(150,$YPos,150,$FontSize,$myrow['description'],'',0);
		$pdf->addTextWrap(310,$YPos,60,$FontSize,$myrow['loccode'],'left',0);
		$pdf->addTextWrap(370,$YPos,50,$FontSize,locale_number_format($myrow['quantity'],
											$myrow['decimalplaces']),'right',0);
		$pdf->addTextWrap(420,$YPos,50,$FontSize,locale_number_format($myrow['reorderlevel'],
											$myrow['decimalplaces']),'right',0);

	if ($YPos < $Bottom_Margin + $line_height){
	   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
				   $Right_Margin,$CatDescription);
	}
	} /*end while loop */

	if ($YPos < $Bottom_Margin + $line_height){
   PrintHeader($pdf,$YPos,$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,$Page_Width,
			   $Right_Margin,$CatDescription);
	}
	/*Print out the grand totals */
	ob_end_clean();
	$pdf->OutputD($_SESSION['DatabaseName'] . '_Inventory_Quantities_' . Date('Y-m-d') . '.pdf');
	$pdf->__destruct();	



	
}else{ /*The option to print PDF nor to create the Excel was not hit */
    $Title="库存账簿";//_('Inventory Quantities Reporting');
	include('includes/header.php');

	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/inventory.png" title="' . _('Inventory') . '" alt="" />' . ' ' . $Title . '</p>';
	//echo '<div class="page_help_text">' . _('Use this report to display the quantity of Inventory items in different categories.') . '</div><br />';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
		<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table  class="selection"  ><tr>
		<td>选择会计期间</td>
		<td colspan="2"><select name="SelectPrd" id ="SelectPrd" size="1"  onclick="onPeriod(this);">';
		//SelectPeriod($_SESSION['period'],$_SESSION['startperiod']);
		for ($i=$period;$i>=$_SESSION['startperiod'];$i--){
			$m=$i-$period;
			if (isset($_POST['SelectPrd'])&& $i==$_POST['SelectPrd']){
				echo '<option selected="True" value ="';
			}else{
				echo '<option value ="';
			}
			echo $i.'">'.date("Y-m",strtotime("$m Month")).'</option>';	

		}
	
	echo'</select></td>
		</td></tr>';
echo '<tr>
		<td>查询方式</td>
		<td colspan="2">
			<input type="radio" name="query"  id="rdoquery2" value="2" '.($_POST['query']==2?"checked":"").'  onclick="radioquery(this);" >期间
			<input type="radio" name="query"  id="rdoquery1" value="1"   '.($_POST['query']==1?"checked":"").'  onclick="radioquery(this);" >日期 
		</td>
		</tr>';
echo'<tr>
		<td>选择日期</td>
		<td >
			<input type="date"   alt="" min="'.$afterdate.'" max="'.date('Y-m-d').'"  name="AfterDate" id= "AfterDate" maxlength="10" size="11" value="' . $_POST['AfterDate'] . '" onclick="OnClickDate(this);"/>
			<input type="date"   alt="" min="'.$afterdate.'" max="'.date('Y-m-d').'"  name="BeforDate" 	id="BeforDate" maxlength="10" size="11" value="' .$_POST['BeforDate']. '"  onclick="OnClickDate(this);" />
			</td>
		<td></td>
		</tr>	
		</table>';
	echo'<table class="selection">';


		$SQL = "SELECT locations.`loccode`, `locationname`
		        FROM `locations` 
				INNER JOIN locationusers ON locationusers.loccode =locations.loccode AND locationusers.userid = '".$_SESSION['UserID']."' AND locationusers.canupd = 1
				ORDER BY  `locationname`";
	$resultLoc = DB_query($SQL);
	if (DB_num_rows($resultLoc)==0){
		echo '</table>
			<p />';
		prnMsg(_('There are no stock categories currently defined please use the link below to set them up'),'warn');
		echo '<br /><a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
		include ('includes/footer.php');
		exit;
	}
		echo '<tr>
				<td>选择仓库:<select name="Locations" id="Locations"  onChange="onStockCat( this ) ">';
			while ($myrow1 = DB_fetch_array($resultLoc)) {
				if (!isset($_POST['Locations'])){
					$_POST['Locations']=$myrow1['loccode'] ;
				}
				if ($myrow1['loccode']==$_POST['Locations']){
					echo '<option selected="selected" value="' . $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
				} else {
					echo '<option value="' . $myrow1['loccode'] . '">' . $myrow1['locationname'] . '</option>';
				}
			}
			echo '</select></td>
				</tr>';
		echo '<tr>';
		echo '<td>选择物料:
		 <select name="StockCategory" id="StockCategory">';
		if (!isset($_POST['StockCategory'])) {
			$_POST['StockCategory'] ='';
		}
		if ($_POST['StockCategory'] == 'All') {
			echo '<option selected="selected" value="All">' . _('All') . '</option>';
		} else {
			echo '<option value="All">' . _('All') . '</option>';
		}
		foreach( $StockCategory as $row){
		
			if ($row['loc']==$_POST['Locations']){
				if ($row['id'] == $_POST['StockCategory']) {
					echo '<option selected="selected" value="' . $row['id'] . '">' . $row['id'].'-'. $row['des'] . '</option>';
				} else {
					echo '<option value="' . $row['id'] . '">' .$row['id'].'-'. $row['des'] . '</option>';
				}
			}
		}
		echo '</select></td>';
		echo '<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td><td>';
		if (isset($_POST['Keywords'])) {
			echo '<input type="text" autofocus="autofocus" name="Keywords" value="' . $_POST['Keywords'] . '" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
		} else {
			echo '<input type="text" autofocus="autofocus" name="Keywords" title="' . _('Enter text that you wish to search for in the item description') . '" size="20" maxlength="25" />';
		}
		echo '</td>
			</tr>
			<tr>
				<td></td>
				<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>
				<td>';
		if (isset($_POST['StockCode'])) {
			echo '<input type="text" name="StockCode" value="' . $_POST['StockCode'] . '" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
		} else {
			echo '<input type="text" name="StockCode" title="' . _('Enter text that you wish to search for in the item code') . '" size="15" maxlength="18" />';
		}/*
		echo '<tr>
				<td></td>
				<td><b>' . _('OR') . ' ' . '</b>' . _('Enter partial') . ' <b>' . _('Supplier Code') . '</b>:</td>
				<td>';
		if (isset($_POST['SupplierStockCode'])) {
			echo '<input type="text" name="SupplierStockCode" value="' . $_POST['SupplierStockCode'] . '" title="' . _('Enter text that you wish to search for in the supplier\'s item code') . '" size="15" maxlength="18" />';
		} else {
			echo '<input type="text" name="SupplierStockCode" title="' . _('Enter text that you wish to search for in the supplier\'s item code') . '" size="15" maxlength="18" />';
		}
		echo '</td></tr>';*/
		echo'</table><br />';
	echo'<br />
		<div class="centre">
			<input type="submit" name="Search" value="查询显示" />
			<input type="submit" name="ExportExcel" value="导出Excel" />
			<input type="submit" name="PrintPDF" value="' . _('Print PDF') . '" />
		</div>';
}//ENDIF
//-------------------
if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}

if(!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
$TableHeader = '<tr>
					<th>序号</th>
					<th> 物料编码</th>
					<th class="ascending">' . _('Description') . '</th>							
					<th class="ascending">仓库</th>
					<th>' . _('Units') . '</th>
					<th>期初库存<br/>数量</th>
					<th>入库数量</th>
					<th>出库数量</th>
					<th>库存数量</th>
					<th>订单</th>
					<th>Level</th>
					<th></th>
					</tr>';
if (isset($_POST['Search']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	prnMsg($_POST['query'].'[Select].....'.$_POST['SelectPrd'].'AfterDate'.$AfterDate.'$BeforDate'.$BeforDate.'"','info');
	
	if(isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}	
	$ListCount = DB_num_rows($SearchResult);
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if(isset($_POST['Next'])) {
		if($_POST['PageOffset'] < $ListPageMax) {
			$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
		}
	}
	if(isset($_POST['Previous'])) {
		if($_POST['PageOffset'] > 1) {
			$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
		}
	}
	echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';

	if ($_POST['PageOffset'] > $ListPageMax) {
		$_POST['PageOffset'] = $ListPageMax;
	}
	if ($ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset1">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if($ListPage == $_POST['PageOffset']) {
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
	echo '<table id="ItemSearchTable" class="selection">';
	
	
	echo $TableHeader;
	$j = 1;
	$k = 0; //row counter to determine background colour
	$RowIndex = 0;
	//if (DB_num_rows($SearchResult) <> 0) {
		DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	//}
	while (($myrow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		if ($_POST['query']==1) {//日期
			
			
			$sql="SELECT SUM(CASE  WHEN  trandate >='".$AfterDate."' AND trandate<='".$BeforDate."'  THEN  CASE WHEN `type` = 17 OR `type` = 26 THEN `qty` ELSE 0 END ELSE 0 END) inqty, 
				         SUM(CASE  WHEN  trandate >='".$AfterDate."'  AND trandate<='".$BeforDate."'  THEN CASE WHEN `type` = 10 OR `type` = 28 OR `type` = 39 THEN `qty` ELSE 0 END ELSE 0 END ) outqty";
			if (!empty($StartDate)){		
			$sql.=", SUM(CASE  WHEN  trandate >='".$StartDate."' AND trandate<'".$AfterDate."'  THEN  CASE WHEN `type` = 17 OR `type` = 26 THEN `qty` ELSE 0 END ELSE 0 END) inqty0, 
					SUM(CASE  WHEN  trandate >='".$StartDate."'  AND trandate<'".$AfterDate."'  THEN CASE WHEN `type` = 10 OR `type` = 28 OR `type` = 39 THEN `qty` ELSE 0 END ELSE 0 END ) outqty0 ";
			}
			if (!empty($EndDate)){
			$sql.=", SUM(CASE  WHEN  trandate >'".$BeforDate."' AND trandate<='".$EndDate."'  THEN  CASE WHEN `type` = 17 OR `type` = 26 THEN `qty` ELSE 0 END ELSE 0 END) inqty1, 
					SUM(CASE  WHEN  trandate >'".$BeforDate."'  AND trandate<='".$EndDate."'  THEN CASE WHEN `type` = 10 OR `type` = 28 OR `type` = 39 THEN `qty` ELSE 0 END ELSE 0 END ) outqty1 ";
			}	
			 $sql.=" FROM `stockmoves` 
				   WHERE  stockid ='".$myrow['stockid']."' 
						   AND loccode='".$myrow['loccode']."' ";
			if (!empty($StartDate)){	
						$sql.=" AND   trandate >='".$StartDate."' ";
			 }else {
				 $sql.=" AND trandate>='".$AfterDate."'";
			 } 
			 if (!empty($EndDate)){
				 $sql.=" AND trandate<='".$EndDate."' ";
			 }else {
				$sql.=" AND trandate<='".$EndDate."'";
			} 	   
			$sql.="	GROUP BY  `type`, `loccode`";
				//echo $sql."<br/>";
		}else{
			$sql="SELECT  SUM(CASE WHEN `type` = 17 OR `type` = 26 THEN `qty` ELSE 0 END) inqty, 
							SUM(CASE WHEN `type` = 10 OR `type` = 28 OR `type` = 39 THEN `qty` ELSE 0 END) outqty 
				FROM `stockmoves` 
				WHERE trandate >='".$AfterDate."' AND trandate<='".$BeforDate."'
				AND stockid ='".$myrow['stockid']."' 
					AND loccode='".$myrow['loccode']."'
				GROUP BY  `type`, `loccode`";
		}
		$result=DB_query($sql);
		$InQty=0;
		$OutQty=0;
		$Row=DB_fetch_assoc($result);
		if (!empty($Row['inqty']))	
			$InQty=$Row['inqty'];
		if (!empty($Row['outqty']))	
			$OutQty=$Row['outqty'];
		if ($_POST['query']==2) {//使用期间

			if (date("Y01")==date("Ym",strtotime($BeforDate))){
				//当年一月  期末余额
				$EndQty=round($myrow['quantity'], $myrow['decimalplaces']);
				$StartQty=$EndQty-$InQty-$OutQty;
			}else {
				$EndQty=round($myrow['endoflastqty'], $myrow['decimalplaces'])+$InQty+$OutQty;
				$StartQty=round($myrow['endoflastqty'], $myrow['decimalplaces']);
			}
		}else{
			   $EndFlag=0;
			   $StartFlag=0;
			   //当年一月 1-31 
			if (empty($EndDate)){
				if(date("Y01")==date("Y-m",strtotime($_POST['BeforDate']))){
					$EndFlag=1;
				}
			}else{
				if(date("Y01")==date("Y-m",strtotime($EndDate))){
					$EndFlag=1;
				}
			}
			//当年一月 1-31 
			if (empty($StartDate)){
				if(date("Y01")==date("Y-m",strtotime($_POST['AfterDate']))){
					$StartFlag=1;
		
				}
			}else{
				if(date("Y01")==date("Y-m",strtotime($StartDate))){
					$StartFlag=1;
		
				}
			}
			$InQty0=0;
			$OutQty0=0;
			$InQty1=0;
			$OutQty1=0;

			if (!empty($Row['inqty0']))	
				$InQty0=$Row['inqty0'];
			if (!empty($Row['outqty0']))	
				$OutQty0=$Row['outqty0'];
			if (!empty($Row['inqty1']))	
				$InQty1=$Row['inqty1'];
			if (!empty($Row['outqty1']))	
				$OutQty1=$Row['outqty1'];

			if ($StartFlag==1 && $EndFlag==1){
				//当年一月 1-31  期末余额
				$EndQty=round($myrow['quantity'], $myrow['decimalplaces'])-$InQty1-$OutQty1;
				$StartQty=$EndQty-$InQty-$OutQty-$InQty1-$OutQty1;
			}else {
			
				$StartQty=round($myrow['endoflastqty'], $myrow['decimalplaces'])+$InQty0+$OutQty0;
				$EndQty=$StartQty+$InQty+$OutQty;
			}
			/*	$StartDate=date("Y-01-01",strtotime($_POST['AfterDate']));
			}
			if(date("Y-m-d")!=$_POST['BeforDate']){
			
				$EndDate=date("Y-m-d");
			} */

		}
		
		
	
	
		if ($k == 1) {
			echo '<tr class="EvenTableRows">';
			$k = 0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		echo'<td>' . ($RowIndex+1) . '</td>
			<td>'.$myrow['stockid'].'</td>
			<td>' .$myrow['description'] . '</td>		
			<td>'. $CategoryLocation[substr($myrow['stockid'],0,3)][0] . '</td>
			<td>' . $myrow['units'] . '</td>
			<td class="number">' . locale_number_format($StartQty, $myrow['decimalplaces'])  . '</td>
			<td>' . locale_number_format($InQty, $myrow['decimalplaces']). '</td>
			<td>' .locale_number_format( -$OutQty , $myrow['decimalplaces']). '</td>
			<td class="number">' . locale_number_format($EndQty, $myrow['decimalplaces'])  . '</td>
		
			<td></td>
			<td>'.$myrow['reorderlevel'].'</td>
			<td><a href="StockInquiry.php?StkID='.$myrow['stockid'].'"  target="_blank" >查询</a></td>
		</tr>';
		   $RowIndex = $RowIndex + 1;
		//end of page full new headings if
	}
	//end of while loop
	
	echo '</table>';
	if(isset($ListPageMax) AND $ListPageMax > 1) {
		echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
		echo '<select name="PageOffset2">';
		$ListPage = 1;
		while ($ListPage <= $ListPageMax) {
			if($ListPage == $_POST['PageOffset']) {
				echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
			}// $ListPage == $_POST['PageOffset']
			else {
				echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
			}
			$ListPage++;
		}// $ListPage <= $ListPageMax
		echo '</select>
			<input type="submit" name="Go2" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}

}	

//-----------

//else { /*The option to print PDF was not hit so display form */
if (!isset($_POST['PrintPDF'])){
	echo '</div>
			</form>';

	
	include('includes/footer.php');

} 
function exportExcelIQ($ResultIQ,$CategoryLocation,$TitleData,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';  

	  $spreadsheet = new Spreadsheet();
	  // Create a new worksheet "
	  	//设���默认文字居左，上下居中 
		$styleArray = [
			'alignment' => [
				'horizontal' => Alignment::HORIZONTAL_RIGHT,
				'vertical'   => Alignment::VERTICAL_CENTER,
			],
		];
		$spreadsheet->getDefaultStyle()->applyFromArray($styleArray);
	  $sheet = $spreadsheet->getActiveSheet();
	  //设置sheet的名字  两种方法
	  $sheet->setTitle($TitleData['Title']);
	  //$spreadsheet->getActiveSheet()->setTitle('银行存款-现金汇总表');
	  
	  //$head=['科���编码/名称', '期初���方','期初贷方',  '借发生额', '贷发生额', '期末借款','期末贷方'];	
	   $head=['序号','物料编码',	'描述'	,'仓库',	'库存数量',	'单位'	,'订单','Level']; 
	  //数据中对应的字段���用于读���相应数据：
	
	  $columnCnt = count($head);  //计算表头数量
	for ($i = 65; $i < $columnCnt + 65; $i++) {     //数字转字母从65开始，循环设置表头：
		$sheet->setCellValue(strtoupper(chr($i)) . '1', $head[$i - 65]);
		if ($i==65){
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(7); //列宽
		}elseif ($i==66){
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(15); //固定列宽
		}elseif ($i==67){
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(20); //固定列宽
		}else{
			$sheet->getColumnDimension(strtoupper(chr($i)))->setWidth(15); //固定列宽
		}
	}
	/*--------------开始从数据库���取信息插入Excel表中------------------*/
	DB_data_seek($ResultIQ,0);
	$k=2;
	$QtyTotal=0;
	while ($row = DB_fetch_array($ResultIQ) ){//遍历账户

		for ($i = 65; $i < $columnCnt+65 ; $i++) {     //数字转字母从1开始：
			
				if ($i==65){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), ($k-1));
				}elseif($i==66){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), $row['stockid']);
					//$sheet->getCell(strtoupper(chr($i)) . ($k))->getHyperlink()->setUrl('"'.$row['account'].'银行现金'.'"');
				
				}elseif($i==67){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), 	$row['description']);
				}elseif($i==68){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k),$CategoryLocation[substr($row['stockid'],0,3)][0]);
				}elseif($i==69){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($row['qoh'],$row['decimalplaces']));
				}elseif($i==70){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), $row['units']);
				}elseif($i==71){
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($row['units'],POI));
				}else{
					$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format('',POI));
				}
		
		}
		$QtyTotal+=round($row['qoh'],$row['decimalplaces']);
		$k++;
	
	}
	
	for ($i = 65; $i < $columnCnt+65 ; $i++) {     
	
		if ($i==65){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), '');
		}elseif($i==66){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), 	'');
		}elseif($i==67){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k),'合计');
		}elseif($i==69){
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), locale_number_format($QtyTotal,POI));
		}else{
			$sheet->setCellValue(strtoupper(chr($i)) . ($k), '');
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
function PrintHeader(&$pdf,&$YPos,&$PageNumber,$Page_Height,$Top_Margin,$Left_Margin,
                     $Page_Width,$Right_Margin,$CatDescription) {

	/*PDF page header for Reorder Level report */
	if ($PageNumber>1){
		$pdf->newPage();
	}
	$line_height=12;
	$FontSize=9;
	$YPos= $Page_Height-$Top_Margin;

	$pdf->addTextWrap($Left_Margin,$YPos,300,$FontSize,$_SESSION['CompanyRecord']['coyname']);

	$YPos -=$line_height;

	$pdf->addTextWrap($Left_Margin,$YPos,150,$FontSize,_('Inventory Quantities Report'));
	$pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos,160,$FontSize,_('Printed') . ': ' .
		 Date($_SESSION['DefaultDateFormat']) . '   ' . _('Page') . ' ' . $PageNumber,'left');
	$YPos -= $line_height;
	$pdf->addTextWrap($Left_Margin,$YPos,50,$FontSize,_('Category'));
	$pdf->addTextWrap(95,$YPos,50,$FontSize,$_POST['Locations']);
	$pdf->addTextWrap(160,$YPos,150,$FontSize,$CatDescription,'left');
	$YPos -=(2*$line_height);

	/*set up the headings */
	$Xpos = $Left_Margin+1;

	$pdf->addTextWrap(50,$YPos,100,$FontSize,_('Part Number'), 'left');
	$pdf->addTextWrap(150,$YPos,150,$FontSize,_('Description'), 'left');
	$pdf->addTextWrap(310,$YPos,60,$FontSize,_('StockCat'), 'left');
	$pdf->addTextWrap(370,$YPos,50,$FontSize,_('Quantity'), 'right');
	$pdf->addTextWrap(420,$YPos,50,$FontSize,_('Reorder'), 'right');
	$YPos -=$line_height;
	$pdf->addTextWrap(415,$YPos,50,$FontSize,_('Level'), 'right');


	$FontSize=8;
	$PageNumber++;
} // End of PrintHeader() function
?>