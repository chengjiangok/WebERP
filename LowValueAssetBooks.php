<?php
/* $Id: FixedAssetRegister.php  2017-01-20 08:32:33 ChengJiang $*/
include ('includes/session.php');
$Title = '低值易耗品账簿';

$ViewTopic = 'LowValueAssets';
$BookMark = 'AssetRegister';
$csv_output = '';
include ('includes/header.php');
if (!isset($_POST['selectperiod'])OR $_POST['selectperiod']==''){
		$_POST["selectperiod"]=$_SESSION['period'].'^'.$_SESSION['lastdate'];
  	}
if (!isset($_POST['periodrange']) OR $_POST['periodrange']==''){
     $_POST['periodrange']=0;		  	
	}

if (!isset($_POST['query'])) {
   $_POST['query']=1;
}


	if (!isset($_POST['costitem'])){
      $_POST['costitem']=0;
		}
	$_POST['ProcessDate']=explode('^',$_POST["selectperiod"])[1];
    $AllowUserEnteredProcessDate = true;
    //1month periodno
	  $prdstart=explode('^',$_POST["selectperiod"])[0]-date('m',strtotime(explode('^',$_POST["selectperiod"])[1]))+1;  
	  
	  $prdme=$prdm+date('m',strtotime(explode('^',$_POST["selectperiod"])[1]))-1;
	  $prdno=explode('^',$_POST["selectperiod"])[0]  ;


	
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	$result = DB_query('SELECT categoryid,categorydescription FROM fixedassetcategories');
	echo '<form id="RegisterForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	$prtprd=6;
// $sql = "SELECT periodno, lastdate_in_period FROM periods WHERE periodno>'".$prtprd."' AND periodno<='".$_SESSION['period']."'  AND periodno NOT IN (SELECT DISTINCT periodno FROM fixedassettrans) ORDER BY periodno DESC ";
 //  $result = DB_query($sql);
	echo '<table class="selection">';
  /*
	echo '<tr><td>' . _('Select Period To')  . '</td>
	<td ><select name="selectperiod" size="1" >';					
  
   while ($myrow=DB_fetch_array($result,$db)){	
   	
		if(isset($_POST['selectperiod']) AND $myrow['periodno'].'^'. $myrow['lastdate_in_period']==$_POST['selectperiod']){	
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
		echo   $myrow['periodno'].'^'. $myrow['lastdate_in_period'].'">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
	}
   
   
    echo '</select></td><td></td></tr>';
	echo '		<tr>
			<td>' . iconv('GB2312', 'UTF-8','查询方式'). '</td>
				<td colspan="2">
				    <input type="radio" name="query" value="0"  '.($_POST['query']==0 ? 'checked':"").' >'._('Default').'          
            <input type="radio" name="query" value="2"   '.($_POST['query']==1 ? 'checked':"").'  >'._('Total').'
           
       </td>
       </tr>';*/
 
          
	 if (isset($_SESSION['Tag'])){
   $sql="SELECT code,description FROM  workcentres";
    $result = DB_query($sql);
  
		echo '<tr><td>选择核算单元:</td>
		<td colspan="2"><select name="costitem" size="1" >';
		 while ($myrow=DB_fetch_array($result,$db)){	
   	
		if(isset($_POST['costitem']) AND $myrow['code']==$_POST['costitem']){	
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
		echo   $myrow['code']. '">' . $myrow['description'] . '</option>';
	}
		
  
	echo	'</select>
	        </td></tr>';
	 }
	echo '	</table>
		<br />';
	
	echo '<div class="centre">
		<input type="submit" name="submit" value="' . _('Show Assets') . '" />&nbsp;
		<input type="submit" name="pdf" value="' . _('Print as a pdf') . '" />&nbsp;
		<input type="submit" name = "csv" value="' . _('Print as CSV') . '" />
	</div>';
		
		
// Reports being generated in HTML, PDF and CSV/EXCEL format
if (isset($_POST['submit']) OR isset($_POST['pdf']) OR isset($_POST['csv'])) {
	if (isset($_POST['pdf'])) {
		$PaperSize = 'A4_Landscape';
		include ('includes/PDFStarter.php');
	} /*else if (empty($_POST['csv'])) {
		include ('includes/header.php');
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	}*/
	//$DateFrom = FormatDateForSQL($_POST['FromDate']);
	//$DateTo = FormatDateForSQL($_POST['ToDate']);
	$sql="SELECT assetid, 
	             serialno,
				  barcode,
				   description,
				    longdescription, 
				assetlocation, 
				cost  costbfwd,
				Custodian,
				CountsDate,
				 amortizeamount depnbfwd,
					cost AS periodadditions,
					cost AS perioddepn,
					cost AS perioddisposal,
				  transno, 
				  period, 
				  datepurchased, 
				  assetcategoryid,
				   amortizetype, 
				   disposaldate 
				   FROM lowvalueassete WHERE 1";
				   /*
	$sql = "SELECT fixedassets.assetid,
					fixedassets.description,
					fixedassets.longdescription,
					fixedassets.assetcategoryid,
					fixedassets.serialno,
					fixedassetlocations.locationdescription,
					fixedassets.datepurchased,
					fixedassetlocations.parentlocationid,
					fixedassets.assetlocation,
					fixedassets.disposaldate,
					SUM(CASE WHEN (fixedassettrans.periodno >=0 AND fixedassettrans.periodno <'" . $prdno . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS costbfwd,
					SUM(CASE WHEN (fixedassettrans.periodno >=0 AND fixedassettrans.periodno <'" . $prdno . "' AND fixedassettrans.fixedassettranstype='depn') THEN fixedassettrans.amount ELSE 0 END) AS depnbfwd,
					SUM(CASE WHEN (fixedassettrans.periodno='" . $prdno . "' AND fixedassettrans.fixedassettranstype='cost') THEN fixedassettrans.amount ELSE 0 END) AS periodadditions,
					SUM(CASE WHEN fixedassettrans.periodno='" . $prdno . "' AND fixedassettrans.fixedassettranstype='depn' THEN fixedassettrans.amount ELSE 0 END) AS perioddepn,
					SUM(CASE WHEN fixedassettrans.periodno='" . $prdno. "' AND fixedassettrans.fixedassettranstype='disposal' THEN fixedassettrans.amount ELSE 0 END) AS perioddisposal
			FROM fixedassets
			INNER JOIN fixedassetcategories ON fixedassets.assetcategoryid=fixedassetcategories.categoryid
			INNER JOIN fixedassetlocations ON fixedassets.assetlocation=fixedassetlocations.locationid
			INNER JOIN fixedassettrans ON fixedassets.assetid=fixedassettrans.assetid
			
			GROUP BY fixedassets.assetid,
					fixedassets.description,
					fixedassets.longdescription,
					fixedassets.assetcategoryid,
					fixedassets.serialno,
					fixedassetlocations.locationdescription,
					fixedassets.datepurchased,
					fixedassetlocations.parentlocationid,
					fixedassets.assetlocation";*/
	$result = DB_query($sql);
	if (isset($_POST['pdf'])) {
		$FontSize = 10;
		$pdf->addInfo('Title', _('Fixed Asset Register'));
		$pdf->addInfo('Subject', _('Fixed Asset Register'));
		$PageNumber = 1;
		$line_height = 12;
		if ($_POST['AssetCategory']=='%') {
			$AssetCategory=_('All');
		} else {
			$CategorySQL="SELECT categorydescription FROM fixedassetcategories WHERE categoryid='".$_POST['AssetCategory']."'";
			$CategoryResult=DB_query($CategorySQL);
			$CategoryRow=DB_fetch_array($CategoryResult);
			$AssetCategory=$CategoryRow['categorydescription'];
		}

		if ($_POST['AssetID']=='%') {
			$AssetDescription =_('All');
		} else {
			$AssetSQL="SELECT description FROM fixedassets WHERE assetid='".$_POST['AssetID']."'";
			$AssetResult=DB_query($AssetSQL);
			$AssetRow=DB_fetch_array($AssetResult);
			$AssetDescription =$AssetRow['description'];
		}
		PDFPageHeader();
	} elseif (isset($_POST['csv'])) {
		$csv_output = "'Asset ID','Description','Serial Number','Location','Date Acquired','Cost B/Fwd','Period Additions','Depn B/Fwd','Period Depreciation','Cost C/Fwd', 'Accum Depn C/Fwd','NBV','Disposal Value'\n";
	} else {
		/*echo '<form id="RegisterForm" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?' . SID . '">
              <div>';
        echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		//echo '<div class="centre">' ._('From') . ':' . $_POST['FromDate'] . ' ' . _('to') . ' ' . $_POST['ToDate'] . '</div>';
*/	
		echo '<br />
			<table width="80%" cellspacing="1" class="selection">
			<tr>
				<th width="10" >' . _('Asset ID') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Serial Number') . '</th>
				<th>' . _('Location') . '</th>
				<th>' . _('Date Acquired') . '</th>
				<th>保管人</th>
				<th>盘点日期</th>
				<th>年初成本</th>
				<th>年初摊销</th>				
				<th>本年增加成本</th>
				<th>本年摊销</th>
				<th>本月增加成本</th>
				<th>本月摊销</th>
			    <th>期末成本</th>
				<th>累计摊销</th>				
				<th>净值</th>
			</tr>';
	}
	$TotalCostBfwd =0;
	$TotalCostCfwd = 0;
	$TotalDepnBfwd = 0;
	$TotalDepnCfwd = 0;
	$TotalAdditions = 0;
	$TotalDepn = 0;
	$TotalDisposals = 0;
	$TotalNBV = 0;
  $k=0;
	while ($myrow = DB_fetch_array($result)) {
		/*
		 * $Ancestors = array();
		$Ancestors[0] = $myrow['locationdescription'];
		$i = 0;
		while ($Ancestors[$i] != '') {
			$LocationSQL = "SELECT parentlocationid from fixedassetlocations where locationdescription='" . $Ancestors[$i] . "'";
			$LocationResult = DB_query($LocationSQL);
			$LocationRow = DB_fetch_array($LocationResult);
			$ParentSQL = "SELECT locationdescription from fixedassetlocations where locationid='" . $LocationRow['parentlocationid'] . "'";
			$ParentResult = DB_query($ParentSQL);
			$ParentRow = DB_fetch_array($ParentResult);
			$i++;
			$Ancestors[$i] = $ParentRow['locationdescription'];
		}
		*/
		if (Date1GreaterThanDate2(ConvertSQLDate($myrow['disposaldate']),$_POST['FromDate']) OR $myrow['disposaldate']='0000-00-00') {

			if ($myrow['disposaldate']!='0000-00-00' AND Date1GreaterThanDate2($_POST['ToDate'], ConvertSQLDate($myrow['disposaldate']))){
				/*The asset was disposed during the period */
				$CostCfwd = 0;
				$AccumDepnCfwd = 0;
			} else {
				$CostCfwd = $myrow['periodadditions'] + $myrow['costbfwd'];
				$AccumDepnCfwd = $myrow['perioddepn'] + $myrow['depnbfwd'];
			}

			if (isset($_POST['pdf'])) {

				$LeftOvers = $pdf->addTextWrap($XPos, $YPos, 30 - $Left_Margin, $FontSize, $myrow['assetid']);
				$LeftOvers = $pdf->addTextWrap($XPos + 30, $YPos, 150 - $Left_Margin, $FontSize, $myrow['description']);
				$LeftOvers = $pdf->addTextWrap($XPos + 180, $YPos, 40 - $Left_Margin, $FontSize, $myrow['serialno']);
				/*
				 * $TempYPos = $YPos;
				for ($i = 1;$i < sizeof($Ancestors) - 1;$i++) {
					for ($j = 0;$j < $i;$j++) {
						$TempYPos-= (0.8 * $line_height);
						$LeftOvers = $pdf->addTextWrap($XPos + 300, $TempYPos, 300 - $Left_Margin, $FontSize, '	');
					}
					$LeftOvers = $pdf->addTextWrap($XPos + 300, $TempYPos, 300 - $Left_Margin, $FontSize, '|_' . $Ancestors[$i]);
				}
				* */

				$LeftOvers = $pdf->addTextWrap($XPos + 220, $YPos, 50 - $Left_Margin, $FontSize, ConvertSQLDate($myrow['datepurchased']));
				$LeftOvers = $pdf->addTextWrap($XPos + 270, $YPos, 70, $FontSize, locale_number_format($myrow['costbfwd'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($XPos + 340, $YPos, 70, $FontSize, locale_number_format($myrow['depnbfwd'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($XPos + 410, $YPos, 70, $FontSize, locale_number_format($myrow['periodadditions'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($XPos + 480, $YPos, 70, $FontSize, locale_number_format($myrow['perioddepn'], 0), 'right');
				$LeftOvers = $pdf->addTextWrap($XPos + 550, $YPos, 70, $FontSize, locale_number_format($CostCfwd, 0), 'right');
				$LeftOvers = $pdf->addTextWrap($XPos + 620, $YPos, 70, $FontSize, locale_number_format($AccumDepnCfwd, 0), 'right');
				$LeftOvers = $pdf->addTextWrap($XPos + 690, $YPos, 70, $FontSize, locale_number_format($CostCfwd - $AccumDepnCfwd, 0), 'right');

				$YPos = $YPos - (0.8 * $line_height);
				if ($YPos < $Bottom_Margin + $line_height) {
					PDFPageHeader();
				}
			} elseif (isset($_POST['csv'])) {
				$csv_output .= $myrow['assetid'] . ',' . $myrow['longdescription'] .',' . $myrow['serialno'] . ',' . $myrow['locationdescription'] . ',' . $myrow['datepurchased'] . ',' . $myrow['costbfwd'] . ',' . $myrow['periodadditions'] . ',' . $myrow['depnbfwd'] . ',' . $myrow['perioddepn'] . ',' . $CostCfwd . ',' . $AccumDepnCfwd . ',' . ($CostCfwd - $AccumDepnCfwd) . ',' . $myrow['perioddisposal'] . "\n";

			} else {
			if ($k==1){
		echo '<tr class="EvenTableRows">';
		$k=0;
	} else {
		echo '<tr class="OddTableRows">';
		$k++;
	}	
				echo '
						<td style="vertical-align:top ">' . $myrow['assetid'] . '</td>
						<td style="vertical-align:top">' . $myrow['longdescription'] . '</td>
						<td style="vertical-align:top">' . $myrow['serialno'] . '</td>
						<td>' . $myrow['locationdescription'] . '<br />';
			/*	Not reworked yet
			 * for ($i = 1;$i < sizeOf($Ancestors) - 1;$i++) {
					for ($j = 0;$j < $i;$j++) {
						echo '&nbsp;&nbsp;&nbsp;&nbsp;';
					}
					echo '|_' . $Ancestors[$i] . '<br />';
				}
			*/
				echo '</td>
					<td style="vertical-align:top">' . ConvertSQLDate($myrow['datepurchased']) . '</td>
					<td>' . $myrow[Custodian] . '</td>
					<td style="vertical-align:top">' . ConvertSQLDate($myrow['CountsDate']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['costbfwd'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['depnbfwd'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['periodadditions'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['perioddepn'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format(0 , $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format(0, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format(0, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($myrow['perioddisposal'], $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
					<td style="vertical-align:top" class="number">' . locale_number_format($CostCfwd - $AccumDepnCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</td>
	
				</tr>';
			}
		} // end of if the asset was either not disposed yet or disposed after the start date
		$TotalCostBfwd +=$myrow['costbfwd'];
		$TotalCostCfwd += ($myrow['costbfwd']+$myrow['periodadditions']);
		$TotalDepnBfwd += $myrow['depnbfwd'];
		$TotalDepnCfwd += ($myrow['depnbfwd']+$myrow['perioddepn']);
		$TotalAdditions += $myrow['periodadditions'];
		$TotalDepn += $myrow['perioddepn'];
		$TotalDisposals += $myrow['perioddisposal'];

		$TotalNBV += ($CostCfwd - $AccumDepnCfwd);
	}

	if (isset($_POST['pdf'])) {
		$LeftOvers = $pdf->addTextWrap($XPos, $YPos, 300 - $Left_Margin, $FontSize, _('TOTAL'));
		$LeftOvers = $pdf->addTextWrap($XPos + 270, $YPos, 70, $FontSize, locale_number_format($TotalCostBfwd, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 340, $YPos, 70, $FontSize, locale_number_format($TotalDepnBfwd, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 410, $YPos, 70, $FontSize, locale_number_format($TotalAdditions, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 480, $YPos, 70, $FontSize, locale_number_format($TotalDepn, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 550, $YPos, 70, $FontSize, locale_number_format($TotalCostCfwd, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 620, $YPos, 70, $FontSize, locale_number_format($TotalDepnCfwd, $_SESSION['CompanyRecord']['decimalplaces']), 'right');
		$LeftOvers = $pdf->addTextWrap($XPos + 690, $YPos, 70, $FontSize, locale_number_format($TotalNBV, $_SESSION['CompanyRecord']['decimalplaces']), 'right');

		$pdf->Output($_SESSION['DatabaseName'] . '_Asset Register_' . date('Y-m-d') . '.pdf', 'I');
		exit;
	} elseif (isset($_POST['csv'])) {
		$FileName =  $_SESSION['reports_dir'] . '/FixedAssetRegister_' . Date('Y-m-d') .'.csv';
		$csvFile = fopen($FileName, 'w');
		$i = fwrite($csvFile, $csv_output);
		header('Location: ' .$_SESSION['reports_dir'] . '/FixedAssetRegister_' . Date('Y-m-d') .'.csv');

	} else {
		//Total Values
		echo '<tr><th style="vertical-align:top" colspan="5">' . _('TOTAL') . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalCostBfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalDepnBfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalAdditions, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalDepn, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalCostCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalDepnCfwd, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalNBV, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalDisposals, $_SESSION['CompanyRecord']['decimalplaces']) . '</th>';
		echo '<th style="text-align:right">' . locale_number_format($TotalNBV, $_SESSION['CompanyRecord']['decimalplaces']) . '</th></tr>';

		echo '</table>';

        echo '<input type="hidden" name="FromDate" value="' . $_POST['FromDate'] . '" />';
        echo '<input type="hidden" name="ToDate" value="' . $_POST['ToDate'] . '" />';
        echo '<input type="hidden" name="AssetCategory" value="' . $_POST['AssetCategory'] . '" />';
        echo '<input type="hidden" name="AssetID" value="' . $_POST['AssetID'] . '" />';
        echo '<input type="hidden" name="AssetLocation" value="' . $_POST['AssetLocation'] . '" />';

		/*echo '<br /><div class="centre"><input type="submit" name="pdf" value="' . _('Print as a pdf') . '" />&nbsp;';
		echo '<input type="submit" name="csv" value="' . _('Print as CSV') . '" />
              </div>
              </div>
              </form>';*/
	}
} //else {


  echo' </div>
	</form>';
//}
include ('includes/footer.php');


function PDFPageHeader (){
	global $PageNumber,
				$pdf,
				$XPos,
				$YPos,
				$Page_Height,
				$Page_Width,
				$Top_Margin,
				$Bottom_Margin,
				$FontSize,
				$Left_Margin,
				$Right_Margin,
				$line_height,
				$AssetDescription,
				$AssetCategory;

	if ($PageNumber>1){
		$pdf->newPage();
	}

	$FontSize=10;
	$YPos= $Page_Height-$Top_Margin;
	$XPos=0;
	$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+20,$YPos-50,0,60);



	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos,240,$FontSize,$_SESSION['CompanyRecord']['coyname']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*1),240,$FontSize, _('Asset Category ').' ' . $AssetCategory );
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*2),240,$FontSize, _('Asset Location ').' ' . $_POST['AssetLocation'] );
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*3),240,$FontSize, _('Asset ID').': ' . $AssetDescription);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*4),240,$FontSize, _('From').': ' . $_POST['FromDate']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*5),240,$FontSize, _('To').': ' . $_POST['ToDate']);
	$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-240,$YPos-($line_height*7),240,$FontSize, _('Page'). ' ' . $PageNumber);

	$YPos -= 60;

	$YPos -=2*$line_height;
	//Note, this is ok for multilang as this is the value of a Select, text in option is different

	$YPos -=(2*$line_height);

	/*Draw a rectangle to put the headings in     */
	$YTopLeft=$YPos+$line_height;
	$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);
	$pdf->line($Left_Margin, $YPos+$line_height,$Left_Margin, $YPos- $line_height);
	$pdf->line($Left_Margin, $YPos- $line_height,$Page_Width-$Right_Margin, $YPos- $line_height);
	$pdf->line($Page_Width-$Right_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos- $line_height);

	/*set up the headings */
	$FontSize=10;
	$XPos = $Left_Margin+1;
	$YPos -=(0.8*$line_height);
	$LeftOvers = $pdf->addTextWrap($XPos,$YPos,30,$FontSize,  _('Asset'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+30,$YPos,150,$FontSize,  _('Description'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+180,$YPos,40,$FontSize,  _('Serial No.'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+220,$YPos,50,$FontSize,  _('Purchased'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+270,$YPos,70,$FontSize,  _('Cost B/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+340,$YPos,70,$FontSize,  _('Depn B/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+410,$YPos,70,$FontSize,  _('Additions'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+480,$YPos,70,$FontSize,  _('Depreciation'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+550,$YPos,70,$FontSize,  _('Cost C/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+620,$YPos,70,$FontSize,  _('Depn C/Fwd'), 'centre');
	$LeftOvers = $pdf->addTextWrap($XPos+690,$YPos,70,$FontSize,  _('Net Book Value'), 'centre');
	//$LeftOvers = $pdf->addTextWrap($XPos+760,$YPos,70,$FontSize,  _('Disposal Proceeds'), 'centre');

	$pdf->line($Left_Margin, $YTopLeft,$Page_Width-$Right_Margin, $YTopLeft);
	$pdf->line($Left_Margin, $YTopLeft,$Left_Margin, $Bottom_Margin);
	$pdf->line($Left_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $Bottom_Margin);
	$pdf->line($Page_Width-$Right_Margin, $Bottom_Margin,$Page_Width-$Right_Margin, $YTopLeft);

	$FontSize=8;
	$YPos -= (1.5 * $line_height);

	$PageNumber++;
}

?>