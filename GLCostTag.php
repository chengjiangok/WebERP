<?php
/* $Id: GLTrialBalanceTag.php 7268 2016-09-08 14:57:47Z ChengJiang rchacon $*/
/* Shows the trial balance for the month and the for the period selected together with the budgeted trial balances. */


include ('includes/session.php');
$Title =iconv('GB2312', 'UTF-8','�ɱ����ñ�');
$ViewTopic= iconv('GB2312', 'UTF-8','�ɱ����ñ�');
$BookMark =iconv('GB2312', 'UTF-8','�ɱ����ñ�');

include('includes/SQL_CommonFunctions.inc');

/*
if (isset($_POST['chtype'])) {
 $chtype=	$_POST['chtype'];
}else{
	$chtype=1;
	}*/
 if (isset($_POST['ToPeriod'])){
		PageSet('P',$_POST['ToPeriod']);		
  	 $perno= $_POST["ToPeriod"];	
	}else{	
	 $perno= $_SESSION['period'];
	}
	if (isset($_POST['periodend'])){
		PageSet('PD',$_POST['periodend']);		
  		$pdend=$_POST['periodend'];
	}	else {
	 	$pdend=0;
	}
	$periodend= $_SESSION['period'];
	if (isset($_POST['tag'])){
		PageSet('TG',$_POST['tag']);		
  		$tag=$_POST['tag'];
	}	else {
	 	$tag=50;
	}
	if (isset($_POST['hstag'])){
		PageSet('HS',$_POST['hstag']);		
 		$hstag=$_POST['hstag'];
	}	else {
	 	$hstag=0;
	}
	
//if ( ! isset($_POST['ToPeriod'])OR isset($_POST['SelectADifferentPeriod'])){//Select A Different Periodѡ�������ڼ�
	include  ('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/printer.png" title="' .// Icon image.
		iconv('GB2312', 'UTF-8','�ɱ����ñ�') . '" /> ' .// Icon title.
		iconv('GB2312', 'UTF-8','�ɱ����ñ�') . '</p>';// Page title.
  
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


	echo '<table class="selection">';
   //cheng����
	$chsql="SELECT max(periodno) as period FROM gltrans";
	
	$chPeriods = DB_query($chsql);
	$chrow=DB_fetch_assoc($chPeriods,$db);
	
  	echo '<tr><td>' . iconv('GB2312', 'UTF-8',"ѡ������") . ':</td>
		<td><select name="tag" size="1" >';
		$k=0;
	if (file_exists( $_SESSION['reports_dir'] . '/CostType.csv')){
		$FileVT =fopen( $_SESSION['reports_dir'] . '/CostType.csv','r');
		while ($mytype = fgetcsv($FileVT)) { 
			 	if( $mytype[2]== PageGet('CT','5001') AND $k==0){
			 		
			 		echo '<option  selected="selected"  value="' . $mytype[2] . '">' . iconv('GB2312', 'UTF-8', $mytype[1]) . '</option>';
			 	
			 		$k=1;
			 	}else{
			 		
			 			echo '<option  value="' . $mytype[2] . '">' . iconv('GB2312', 'UTF-8', $mytype[1]) . '</option>';
			 		
			}
		}

	}		
	
	
	echo	'</select></td><td></td></tr>';
	fclose($FileVT);	
 // 
	echo '<tr><td>' . _('Select Period To')  . '</td>
			<td><select name="ToPeriod">';	
   $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".($periodend -18) ."' AND  periodno <='".$periodend."' ORDER BY periodno DESC ";
   $periods = DB_query($sql);
   while ($myrow=DB_fetch_array($periods,$db)){
		
	
				if($myrow['periodno']==PageGet('P',$_SESSION['period'])){	
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		
		} else {
			echo '<option value ="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
	}
	echo '</select></td><td>'.iconv('GB2312', 'UTF-8','��').'';
    echo '<select name="periodend" size="1" >';
    for( $i=0; $i <= 12; $i++) {    	
    	if($i== PageGet('PD','0') ){
		 	 echo '<option selected="selected" value ="'.$i.'" >' .$i.iconv('GB2312', 'UTF-8','����'). '</option>';		 
			 	
		} elseif ($i==0 OR $i==3 OR $i==6 ) {
			echo '<option value ="' . $i . '">' . $i .iconv('GB2312', 'UTF-8','����'). '</option>';
		}   	
    }    
    echo'</select></td></tr>';

  	echo '<tr><td>' . iconv('GB2312', 'UTF-8',"ѡ������") . ':</td>
		<td><select name="tag" size="1" >';
		$k=0;
	if (file_exists( $_SESSION['reports_dir'] . '/VT.csv')){
		$FileVT =fopen( $_SESSION['reports_dir'] . '/VT.csv','r');
		while ($mytype = fgetcsv($FileVT)) { 
			 	if( $mytype[2]== PageGet('TG','50') AND $k==0){
			 		
			 		echo '<option  selected="selected"  value="' . $mytype[2] . '">' . iconv('GB2312', 'UTF-8', $mytype[1]) . '</option>';
			 	  echo '<option   value="50">' . iconv('GB2312', 'UTF-8', "ȫ��") . '</option>';
			 		$k=1;
			 	}else{
			 		if ( 50== PageGet('TG','50') AND $k==0 ){
			 			echo '<option selected="selected" value="50">' . iconv('GB2312', 'UTF-8', "ȫ��") . '</option>';
			 			$k=1;
			 		}
			 			echo '<option  value="' . $mytype[2] . '">' . iconv('GB2312', 'UTF-8', $mytype[1]) . '</option>';
			 		
			}
		}

	}		
	
	
	echo	'</select></td><td></td></tr>';
	fclose($FileVT);	
 /*   $sql="SELECT code,description FROM  workcentres";
    $result = DB_query($sql);*/
		echo '<tr><td>' . iconv('GB2312', 'UTF-8',"ѡ�����㵥Ԫ") . ':</td>
		<td><select name="hstag" size="1" >';
    $k=0;
    if (file_exists( $_SESSION['reports_dir'] . '/workcenter.csv')){
		$FileVT =fopen( $_SESSION['reports_dir'] . '/workcenter.csv','r');
		while ($myrow = fgetcsv($FileVT)) { 
			 	if( $myrow[2]== PageGet('HS','0') AND $k==0 ){
			 		
			 		echo '<option  selected="selected"  value="' . $myrow[2] . '">' . iconv('GB2312', 'UTF-8', $myrow[1]) . '</option>';
			 		echo '<option  value="0">' . iconv('GB2312', 'UTF-8', "ȫ��") . '</option>';
			 		$k=1;
			 	}else{
			 		if ( 0== PageGet('TG','0') AND $k==0 ){
			 			echo '<option selected="selected" value="0">' . iconv('GB2312', 'UTF-8', "ȫ��") . '</option>';
			 			$k=1;
			 		}
			 			echo '<option  value="' . $myrow[2] . '">' . iconv('GB2312', 'UTF-8', $myrow[1]) . '</option>';
			 			
			 		
			}
		}

	}		
		fclose($FileVT);	

	echo	'</select></td><td></td></tr>';
	
	echo '	</table>
		<br />';

	echo '<div class="centre">
			<input type="submit" name="Show" value="'  . iconv('GB2312', 'UTF-8','��ʾ��ѯ') .'" />
				<input type="submit" name="crtExcel" value="' . iconv('GB2312', 'UTF-8','����Excel') .'" />
			<input type="submit" name="PrintPDF" value="'.iconv('GB2312', 'UTF-8','����PDF').'" />
		</div>';



//} else
if (isset($_POST['PrintPDF'])) {

include('includes/tcpdf/tcpdfAccountBalance.php');

 	/*Calculate B/Fwd retained earnings */
	$sql = "SELECT lastdate_in_period FROM periods WHERE periodno='" .$_POST['ToPeriod']. "'";
	$PrdResult = DB_query($sql);
	$myrow = DB_fetch_row($PrdResult);
	$BalanceDate = $myrow[0];
	$SQL = "call accountbalance('".$_POST['ToPeriod']."','" .$chtype."')";
  //$AccountsResult = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));
	$AccountsResult = DB_query($SQL);
	if (DB_error_no() !=0) {
		$Title = _('Account Balance Sheet') . ' - ' . _('Problem Report') . '....';
		include('includes/header.php');
		prnMsg( _('No general ledger accounts were returned by the SQL because') . ' - ' . DB_error_msg() );
		echo '<br /><a href="' .$RootPath .'/index.php">' .  _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	 $ListCount = DB_num_rows($AccountsResult); // UldisN

	// create new PDF document
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('chengjiang');
$pdf->SetTitle( _('Balance Sheet'));
$pdf->SetSubject( _('Balance Sheet') );
$pdf->SetKeywords('TCPDF, PDF');
// set default header dataPDF_HEADER_TITLE.
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
// set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
// set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
// set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setPrintFooter(false);
// set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/tcpdf/eng.php')) {
	require_once(dirname(__FILE__).'/tcpdf/eng.php');
	$pdf->setLanguageArray($l);
}
// ---------------------------------------------------------
// set font helvetica 
$pdf->SetFont('droidsansfallback', '', 10);
// add a page
$pdf->AddPage('L','A4');
// column titles
$header = array(_('Account'), _('Account Name'), _('Initial debit'), _('Initial credit'),_('Total debit'), _('Total credit'), _('Debit balance'), _('Credit balance'), _('Annual cumulative debit'), _('Annual cumulative credit'));
// print colored table
$pdf->AccountBalance($header, $AccountsResult,$BalanceDate);
//============================================================+
// END OF FILE
//============================================================+

	if ($ListCount == 0) {   //UldisN
		$Title = _('Print Balance Sheet Error');
		include('includes/header.php');
		prnMsg( _('There were no entries to print out for the selections specified') );
		echo '<br /><a href="'. $RootPath.'/index.php?' . SID . '">' .  _('Back to the menu'). '</a>';
		include('includes/footer.php');
		exit;
	} else {
     ob_end_clean();

        $pdf->Output( 'AccountBalanceSheet'.$BalanceDate.'.pdf','D');
  
}
	exit;
	
	
} elseif (isset($_POST['Show'])) {

 // prnMsg("'".$perno."','" .$periodend."','".$chtype."','".$tag."','".$hstag."'",'info');
	$RetainedEarningsAct = $_SESSION['CompanyRecord']['retainedearnings'];
  $PeriodToDate =GetPeriodDate($perno);
	
	$SQL = "call GLProfitTag('".$perno."','" .$pdend."','".$tag."','".$hstag."')";
	$AccountsResult = DB_query($SQL, _('No general ledger accounts were returned by the SQL because'), _('The SQL that failed was:'));
	


	
		$TableHeader = '<tr>						
							<th>' . _('Project')  . '</th>
							<th>' . _('Line')  . '</th>
							<th>' . _('Occurrence number')  . '</th>
							<th>' . _('Cumulative occurrence of this year') . '</th>
						</tr>';
							// Page title as IAS1 numerals 10 and 51:
				include_once('includes/CurrenciesArray.php');// Array to retrieve currency name.
		/*		echo '<div id="Report">';// Division to identify the report block.
				echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/gl.png" title="' .// Icon image.
				_('Statement of Comprehensive Income') . '" /> '.// Icon title.
				_('Profit and Loss Statement') . '<br />'.// Page title, reporting statement.
				stripslashes($_SESSION['CompanyRecord']['coyname']) . '<br />' .// Page title, reporting entity.
     		_('as at') . ' ' . $BalanceDate . '<br /></p>' ;// Page title, reporting period.
     		*/
     echo '<table class="selection">';
 				echo  $TableHeader;
 				$k=0;
	while ($myrow=DB_fetch_array($AccountsResult)) {
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
								htmlspecialchars($myrow['groupname'],ENT_QUOTES,'UTF-8',false),
							 $myrow['idx'],
								locale_number_format($myrow['zncye'],$_SESSION['CompanyRecord']['decimalplaces']),
								locale_number_format($myrow['znmye'],$_SESSION['CompanyRecord']['decimalplaces']));
			
				
				}
			
	
	

	echo '</table>';
	echo '</div>';// div id="Report".

/*
		echo '<table cellpadding="2" class="selection">';

	$TableHeader = '<tr>
						<th>' . _('Account') . '</th>
						<th  width="110" >' .'&nbsp;&nbsp;&nbsp;'. _('Account Name') .'&nbsp;&nbsp;&nbsp;'. '</th>
						<th>' . _('Initial debit') . '</th>  
						<th>' . _('Initial credit') . '</th>
		     			<th>' . _('Total debit') . '</th>
						<th>' . _('Total credit')  . '</th>
						<th>' . _('Debit balance') . '</th>
			     		<th>' . _('Credit balance') . '</th>
						<th>' . _('Annual cumulative debit') . '</th>
						<th>' . _('Annual cumulative credit')  . '</th>
					</tr>';

	$j = 0;
	$k=0;
	$k=DB_num_rows($AccountsResult);
    echo $TableHeader;
	while ($myrow=DB_fetch_array($AccountsResult)) {
		$ActEnquiryURL = '<a href="'. $RootPath . '/GLAccountInquiry.php?FromPeriod=' . $_POST['ToPeriod'] . '&amp;ToPeriod=' . $_POST['ToPeriod'] . '&amp;Account=' . $myrow['account'] . '&amp;Show=Yes">' . $myrow['account'] . '</a>';
      $j=$j+1;
  //if ($j<$k){
  	if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf('<td>%s</td>
				<td>%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>
				</tr>',
				$ActEnquiryURL,
				htmlspecialchars($myrow['accountname'], ENT_QUOTES,'UTF-8', false), 			
				locale_number_format($myrow['debit1'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['credit1'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['debit2'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['credit2'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['debit3'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['credit3'],$_SESSION['CompanyRecord']['decimalplaces']),
				locale_number_format($myrow['debit4'],$_SESSION['CompanyRecord']['decimalplaces']),
                locale_number_format($myrow['credit4'],$_SESSION['CompanyRecord']['decimalplaces']));

}
	echo '</table><br />';

	echo '<div class="centre noprint">'.
			'<button onclick="javascript:window.print()" type="button"><img alt="" src="'.$RootPath.'/css/'.$Theme.
				'/images/printer.png" /> ' . _('Print This') . '</button>'.// "Print This" button.
			'<button name="SelectADifferentPeriod" type="submit" value="'. _('Select A Different Period') .'"><img alt="" src="'.$RootPath.'/css/'.$Theme.
				'/images/gl.png" /> ' . _('Select A Different Period') . '</button>'.// "Select A Different Period" button.
			'<button formaction="index.php" type="submit"><img alt="" src="'.$RootPath.'/css/'.$Theme.
				'/images/previous.png" /> ' . _('Return') . '</button>'.// "Return" button.
		'</div>';

*/

}elseif (isset($_POST['crtExcel']) ) {
	
	 prnMsg(iconv('GB2312', 'UTF-8','�ù���ģ����ʱδ��ͨ��').'chtype'.$chtype.'$tag'.$tag.'hstag'.$hstag,'info');
}
echo '</div>
	</form>';
include('includes/footer.php');
?>
