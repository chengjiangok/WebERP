

<?php
/* $Id: ElecticityFees.php ChengJiang $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-11-08 09:15:28 
 * @Last Modified by:   ChengJiang 
 * @Last Modified time: 2017-11-08 09:15:28 
 */
include('includes/session.php');
$Title ='电费开票';

$ViewTopic= 'SalesOrders';
$BookMark = 'SalesOrderEntry';

include('includes/header.php');

include('includes/SQL_CommonFunctions.inc');


if (empty($_GET['identifier'])) {
	/*unique session identifier to ensure that there is no conflict with other order entry sessions on the same machine  */
	$identifier=date('U');
} else {
	$identifier=$_GET['identifier'];
}


//Customer logins are not allowed to select other customers hence in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])
if (isset($_POST['SearchCust'])	AND !isset($_SESSION['CustomerSelection'])
	AND in_array($_SESSION['PageSecurityArray']['ConfirmDispatch_Invoice.php'], $_SESSION['AllowedPageSecurityTokens'])){

		$SQL = "SELECT `ceid`, `cename`, `housenumber`, `electiccard`, custphone,`flg` FROM `customberelectricity` ";
	

	if (($_POST['CustKeywords']=='') AND ($_POST['CustCode']=='')  AND ($_POST['CustPhone']=='')) {
		$SQL .= "";
	} else {
		//insert wildcard characters in spaces
		$_POST['CustKeywords'] = mb_strtoupper(trim($_POST['CustKeywords']));
		$SearchString = str_replace(' ', '%', $_POST['CustKeywords']) ;

		$SQL .= " WHERE cename " . LIKE . " '%" . $SearchString . "%'
				AND  (housenumber " . LIKE . " '%" . mb_strtoupper(trim($_POST['CustCode'])) . "%'
				OR electiccard " . LIKE . " '%" . trim($_POST['CustCode']) . "%')
				AND custphone " . LIKE . " '%" . trim($_POST['CustPhone']) . "%'";
				

	} /*one of keywords or custcode was more than a zero length string */

	$SQL .=	" ORDER BY  `CEName`, `HouseNumber`";

	$ErrMsg = _('The searched customer records requested cannot be retrieved because');
	$result_CustSelect = DB_query($SQL,$ErrMsg);

	if (DB_num_rows($result_CustSelect)==1){
		$myrow=DB_fetch_array($result_CustSelect);
		$SelectedCustomer = $myrow['ceid'];
		$_SESSION['CustomerSelection']=$SelectedCustomer ;
		//$SelectedBranch = $myrow['branchcode'];
	} elseif (DB_num_rows($result_CustSelect)==0){
		prnMsg(_('No Customer Branch records contain the search criteria') . ' - ' . _('please try again') . ' - ' . _('Note a Customer Branch Name may be different to the Customer Name'),'info');
	}
} /*end of if search for customer codes/names */

/* will only be true if page called from customer selection form or set because only one customer
 record returned from a search so parse the $SelectCustomer string into customer code and branch code */

 if (isset($_POST['JustSelectedACustomer'])){
	
	 /*Need to figure out the number of the form variable that the user clicked on */
	 for ($i=0;$i<count($_POST);$i++){ //loop through the returned customers
		 if(isset($_POST['SubmitCustomerSelection'.$i])){
			 break;
		 }
	 }
	 if ($i==count($_POST) AND !isset($SelectedCustomer)){//if there is ONLY one customer searched at above, the $SelectedCustomer already setup, then there is a wrong warning
		 prnMsg(_('Unable to identify the selected customer').'=369','error');
	 } elseif(!isset($SelectedCustomer)) {
		 $SelectedCustomer = $_POST['SelectedCustomer'.$i];
		 $SelectedBranch = $_POST['SelectedBranch'.$i];
		 $_SESSION['CustomerSelection']=$SelectedCustomer;
	 }
 }
 
/*
if (isset($_POST['JustSelectedACustomer'])){
	
	 //Need to figure out the number of the form variable that the user clicked on 
	 for ($i=0;$i<count($_POST);$i++){ //loop through the returned customers
		 if(isset($_POST['SubmitCustomerSelection'.$i])){
			 break;
		 }
	 }
	 if ($i==count($_POST) AND !isset($SelectedCustomer)){//if there is ONLY one customer searched at above, the $SelectedCustomer already setup, then there is a wrong warning
		 prnMsg(_('Unable to identify the selected customer').'=369','error');
	 } elseif(!isset($SelectedCustomer)) {
		 $SelectedCustomer = $_POST['SelectedCustomer'.$i];
		 $SelectedBranch = $_POST['SelectedBranch'.$i];
	 }
 }*/

if(!isset($_POST['PrintSave'])){
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' .
' ' .$Title . '</p>';
echo '<div class="page_help_text">本功能为收取商铺电费专用模块</div>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?identifier='.$identifier . '" method="post">
	 <div>
		 <input name="FormID" type="hidden" value="' . $_SESSION['FormID'] . '" />';
if (isset($_SESSION['CustomerSelection'])){
	
		//prnMsg($SelectedCustomer.'-'. $SelectedBranch,'info');
		echo '<table class="selection" width="700">
		
		<tr>  <th colspan="5"  class="centre" >	' . _('Date to Process Journal') . ':
				<input type="date" required="required"  alt="" name="JournalDate" maxlength="10" size="11" value="' . date('Y-m-d') . '" /></td>
				</th>
		</tr>';
	  //录入凭证行
	 
	  
	  echo '<tr>
			  <th>客户ID</th>
			  <th>客户名</th>
			  <th>商铺号</th>
			  <th>电费卡号</th>
			  <th>收费金额</th>
		  </tr>';
	  
	  echo '<tr>
			  <td>'.explode('^',$_SESSION['CustomerSelection'])[0].'</td>
			  <td>'.explode('^',$_SESSION['CustomerSelection'])[1].'</td>
			  <td>'.explode('^',$_SESSION['CustomerSelection'])[2].'</td>
			  <td>'.explode('^',$_SESSION['CustomerSelection'])[3].'</td>';
	  echo '<td><input type="text" class="number" name="Debit"  maxlength="12" size="10" value="' . locale_number_format($_POST['Debit'],$_SESSION['CompanyRecord']['decimalplaces']) . '" /></td>
			
			</tr>';
		  
	  echo '<tr>
			  <th colspan="2">' . _('GL Narrative') . '</th>
			  <td  colspan="2"><input type="text" name="Narrative" maxlength="300"  value="'.$_POST['Narrative'].'" /></td>
			  <th></th>
			
			</tr>
			</table>';
		echo'<div class="centre">
			<input tabindex="4" type="submit" name="PrintSave" value="打印保存" />
			
		</div>';
	
}else {
echo'<table cellpadding="3" class="selection">
			<tr>
			<td>部分客户名称:</td>
			<td><input tabindex="1" type="text" autofocus="autofocus" name="CustKeywords" size="20" maxlength="25" title="' . _('Enter a text extract of the customer\'s name, then click Search Now to find customers matching the entered name') . '" /></td>
			<td><b>' . _('OR') . '</b></td>
			<td>铺号卡号:</td>
			<td><input tabindex="2" type="text" name="CustCode" size="15" maxlength="18" title="' . _('Enter a part of a customer code that you wish to search for then click the Search Now button to find matching customers') . '" /></td>
			<td><b>' . _('OR') . '</b></td>
			<td>部分电话号码:</td>
			<td><input tabindex="3" type="text" name="CustPhone" size="15" maxlength="18" title="' . _('Enter a part of a customer\'s phone number that you wish to search for then click the Search Now button to find matching customers') . '"/></td>
			</tr>

		</table>

		<div class="centre">
			<input tabindex="4" type="submit" name="SearchCust" value="' . _('Search Now') . '" />
			<input tabindex="5" type="submit" name="reset" value="' .  _('Reset') . '" />
		</div>';

if (isset($result_CustSelect)) {

	echo '
				<input name="JustSelectedACustomer" type="hidden" value="Yes" />
				<br />
				<table class="selection">';

	echo '<tr>
			<th class="ascending" >客户ID</th>
			<th class="ascending" >客户名称</th>
			<th class="ascending" >客户铺号</th>
			
			<th>电费卡号</th>
			<th>' . _('Phone') . '</th>
		</tr>';

	$j = 1;
	$k = 0; //row counter to determine background colour
	$LastCustomer='';
	while ($myrow=DB_fetch_array($result_CustSelect)) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}

		echo '	<td>' .$myrow['ceid'].'</td>
				<td><input tabindex="'.strval($j+5).'" type="submit" name="SubmitCustomerSelection' . $j .'" value="' . htmlspecialchars($myrow['cename'], ENT_QUOTES, 'UTF-8', false). '" />
				<input name="SelectedCustomer' . $j .'" type="hidden" value="'.$myrow['ceid'].'^'. $myrow['cename'].'^'.$myrow['housenumber'].'^'.$myrow['electiccard'].'" />
			
				<td>' . $myrow['housenumber'] . '</td>
				<td>' . $myrow['electiccard'] . '</td>
				<td>' . $myrow['custphone'] . '</td>
			</tr>';
		$LastCustomer=$myrow['cename'];
		$j++;
//end of page full new headings if
	}
//end of while loop
	echo '</table>';

}
}

}else{
	
	include('includes/tcpdf/PDFElecticity.php');
	   
	
	  // if ($row>1){
		   // create new PDF document
	   $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	   // set document information
	   $pdf->SetCreator(PDF_CREATOR);
	   $pdf->SetAuthor('chengjiang');
	   $pdf->SetTitle( _('Account Voucher'));
	   $pdf->SetSubject( _('Account Vouche') );
	   $pdf->SetKeywords('TCPDF, PDF');
	   // set default header dataPDF_HEADER_TITLE.
	   $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
	   // set header and footer fonts
	   $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	   $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	   // set default monospaced font
	   $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
	   // set margins
	   $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT,true);
	   //$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	   $pdf->SetHeaderMargin(0);

	   //$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
	   $pdf->SetFooterMargin(0); 

	   $pdf->setPrintFooter(false);
	   $pdf->setPrintHeader(false);
	   // set auto page breaks
	   $pdf->SetAutoPageBreak(TRUE, 0);//PDF_MARGIN_BOTTOM);

	   // set image scale factor
	   //$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	   // set some language-dependent strings (optional)
	   if (@file_exists(dirname(__FILE__).'/tcpdf/chi.php')) {
		   require_once(dirname(__FILE__).'/tcpdf/chi.php');
		   $pdf->setLanguageArray($l);
	   }
	   // ---------------------------------------------------------
	   // set font helvetica 
	   $pdf->SetFont('droidsansfallback', '', 10);
	   // add a page
	   $pdf->AddPage();
	   // column titles
	   $header = array('序号','商铺号','电费卡号','电费金额','备注');
	   // print colored table
	   $data=explode('^',$_SESSION['CustomerSelection']);
	   array_push($data,$_POST['Debit']);
	   array_push($data,$_POST['Narrative']);
	   $pdf->ElecticPDF($header,  $data,$_POST['JournalDate']);
	   // END OF FILE

			   ob_end_clean();
				   // close and output PDF document
			   $pdf->Output( 'Electic'.$_POST['JournalDate'].'.pdf','I');
			   
			   $pdf->__destruct();
		   $sql="INSERT INTO `electicityfees`( `CEID`, `EFdate`, `amount`,username, `flg`)
					  VALUES ('".$data[0]."',
					  '".$_POST['JournalDate']."',
					  '".$data[4]."',
					  '".$_SESSION['UsersRealName'] ."',
					  '0')";
		   $result=DB_query($sql);
				  
		   if ($result){
				
		 	unset($_SESSION['CustomerSelection']) ; 
	 		 }
}
echo'</div>
</form>';
include('includes/footer.php');
?>
