<?php

/* $Id: Z_DataExport.php 6944 2014-10-27 07:15:34Z daintree $*/


include('includes/session.php');

function stripcomma($str) { //because we're using comma as a delimiter
    $str = trim($str);
    $str = str_replace('"', '""', $str);
    $str = str_replace("\r", "", $str);
    $str = str_replace("\n", '\n', $str);
    if($str == "" )
        return $str;
    else
        return '"'.$str.'"';
}

function NULLToZero( &$Field ) {
    if( is_null($Field) )
        return '0';
    else
        return $Field;
}

function NULLToPrice( &$Field ) {
    if( is_null($Field) )
        return '-1';
    else
        return $Field;
}


// EXPORT FOR PRICE LIST
if ( isset($_POST['custlist']) ) {
	$SQL = "SELECT debtorsmaster.debtorno,
			custbranch.branchcode,
			debtorsmaster.name,
			custbranch.contactname,
			debtorsmaster.address1,
			debtorsmaster.address2,
			debtorsmaster.address3,
			debtorsmaster.address4,
			debtorsmaster.address5,
			debtorsmaster.address6,
			debtorsmaster.currcode,
			debtorsmaster.clientsince,
			debtorsmaster.creditlimit,
			debtorsmaster.taxref,
			custbranch.braddress1,
			custbranch.braddress2,
			custbranch.braddress3,
			custbranch.braddress4,
			custbranch.braddress5,
			custbranch.braddress6,
			custbranch.disabletrans,
			custbranch.phoneno,
			custbranch.faxno,
			custbranch.email
		FROM debtorsmaster,
			custbranch
		WHERE debtorsmaster.debtorno=custbranch.debtorno
		AND ((defaultlocation = '".$_POST['Location']."') OR (defaultlocation = '') OR (defaultlocation IS NULL))";

	$CustResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Customer List Export Problem ....');
		include('includes/header.php');
		prnMsg( _('The Customer List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	$CSVContent = stripcomma('debtorno') . ',' .
			stripcomma('branchcode') . ',' .
			stripcomma('name') . ',' .
			stripcomma('contactname') . ',' .
			stripcomma('address1') . ',' .
			stripcomma('address2') . ',' .
			stripcomma('address3') . ',' .
			stripcomma('address4') . ',' .
			stripcomma('address5') . ',' .
			stripcomma('address6') . ',' .
			stripcomma('phoneno') . ',' .
			stripcomma('faxno') . ',' .
			stripcomma('email') . ',' .
			stripcomma('currcode') . ',' .
			stripcomma('clientsince') . ',' .
			stripcomma('creditlimit') . ',' .
			stripcomma('taxref') . ',' .
			stripcomma('disabletrans') . "\n";


	While ($CustList = DB_fetch_array($CustResult,$db)){

		$CreditLimit = $CustList['creditlimit'];
		if ( mb_strlen($CustList['braddress1']) <= 3 ) {
			$Address1 = $CustList['address1'];
			$Address2 = $CustList['address2'];
			$Address3 = $CustList['address3'];
			$Address4 = $CustList['address4'];
			$Address5 = $CustList['address5'];
			$Address6 = $CustList['address6'];
		} else {
			$Address1 = $CustList['braddress1'];
			$Address2 = $CustList['braddress2'];
			$Address3 = $CustList['braddress3'];
			$Address4 = $CustList['braddress4'];
			$Address5 = $CustList['braddress5'];
			$Address6 = $CustList['braddress6'];
		}

		$CSVContent .= (stripcomma($CustList['debtorno']) . ',' .
			stripcomma($CustList['branchcode']) . ',' .
			stripcomma($CustList['name']) . ',' .
			stripcomma($CustList['contactname']) . ',' .
			stripcomma($Address1) . ',' .
			stripcomma($Address2) . ',' .
			stripcomma($Address3) . ',' .
			stripcomma($Address4) . ',' .
			stripcomma($Address5) . ',' .
			stripcomma($Address6) . ',' .
			stripcomma($CustList['phoneno']) . ',' .
			stripcomma($CustList['faxno']) . ',' .
			stripcomma($CustList['email']) . ',' .
			stripcomma($CustList['currcode']) . ',' .
			stripcomma($CustList['clientsince']) . ',' .
			stripcomma($CreditLimit) . ',' .
			stripcomma($CustList['taxref']) . ',' .
			stripcomma($CustList['disabletrans']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . mb_strlen($CSVContent));
	header('Content-Disposition: inline; filename=CustList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;

} elseif ( isset($_POST['salesmanlist']) ) {
	$SQL = "SELECT salesmancode,
			salesmanname,
			smantel,
			smanfax,
			commissionrate1,
			breakpoint,
			commissionrate2
		FROM salesman";

	$SalesManResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Salesman List Export Problem ....');
		include('includes/header.php');
		prnMsg( _('The Salesman List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	$CSVContent = stripcomma('salesmancode') . ',' .
			stripcomma('salesmanname') . ',' .
			stripcomma('smantel') . ',' .
			stripcomma('smanfax') . ',' .
			stripcomma('commissionrate1') . ',' .
			stripcomma('breakpoint') . ',' .
			stripcomma('commissionrate2') . "\n";


	While ($SalesManList = DB_fetch_array($SalesManResult,$db)){

		$CommissionRate1 = $SalesManList['commissionrate1'];
		$BreakPoint 	 = $SalesManList['breakpoint'];
		$CommissionRate2 = $SalesManList['commissionrate2'];

		$CSVContent .= (stripcomma($SalesManList['salesmancode']) . ',' .
			stripcomma($SalesManList['salesmanname']) . ',' .
			stripcomma($SalesManList['smantel']) . ',' .
			stripcomma($SalesManList['smanfax']) . ',' .
			stripcomma($CommissionRate1) . ',' .
			stripcomma($BreakPoint) . ',' .
			stripcomma($CommissionRate2) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . mb_strlen($CSVContent));
	header('Content-Disposition: inline; filename=SalesmanList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['imagelist']) ) {
	$SQL = "SELECT stockid
		FROM stockmaster
		ORDER BY stockid";
	$ImageResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Security Token List Export Problem ....');
		include('includes/header.php');
		prnMsg( _('The Image List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	$CSVContent = stripcomma('stockid') . ','.
				  stripcomma('filename') . ','.
				  stripcomma('url') . "\n";
	$baseurl = 'http://'. $_SERVER['HTTP_HOST'] . dirname(htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8')) . '/' . 'getstockimg.php?automake=1&stockid=%s.png';
	While ($ImageList = DB_fetch_array($ImageResult,$db)){
		$url = sprintf($baseurl, urlencode($ImageList['stockid']));
		$CSVContent .= (
			stripcomma($ImageList['stockid']) . ',' .
			stripcomma($ImageList['stockid'] . '.png') . ',' .
			stripcomma($url) . "\n");
	}

	header('Content-type: application/csv');
	header('Content-Length: ' . mb_strlen($CSVContent));
	header('Content-Disposition: inline; filename=ImageList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['sectokenlist']) ) {
	$SQL = "SELECT tokenid,
			tokenname
		FROM securitytokens";

	$SecTokenResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Security Token List Export Problem ....');
		include('includes/header.php');
		prnMsg( _('The Security Token List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php?' . SID . '">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	$CSVContent = stripcomma('tokenid') . ',' .
			stripcomma('tokenname') . "\n";


	While ($SecTokenList = DB_fetch_array($SecTokenResult,$db)){

		$CSVContent .= (stripcomma($SecTokenList['tokenid']) . ',' .
			stripcomma($SecTokenList['tokenname']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . mb_strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecTokenList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['BillList']) ) {
	$SQL = "SELECT secroleid,
			secrolename
		FROM securityroles";

	$SecRoleResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Security Role List Export Problem ....');
		include('includes/header.php');
		prnMsg( _('The Security Role List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	$CSVContent = stripcomma('secroleid') . ',' .
			stripcomma('secrolename') . "\n";


	While ($SecRoleList = DB_fetch_array($SecRoleResult,$db)){

		$CSVContent .= (stripcomma($SecRoleList['secroleid']) . ',' .
			stripcomma($SecRoleList['secrolename']) . "\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . mb_strlen($CSVContent));
	header('Content-Disposition: inline; filename=SecRoleList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['AccountList']) ) {
//$SQL = "SELECT groupname,sectioninaccounts,pandl,sequenceintb,parentgroupname FROM accountgroups";
$SQL="SELECT accountcode,accountname,group_ FROM erprenyou.chartmaster";
	$SecUserResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('Account List Export  ....');
		include('includes/header.php');
		prnMsg( _('The Account Group List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	$CSVContent = stripcomma('accountcode') . ',' .
			stripcomma('accountname') . ','.
			stripcomma('group_ ') . "\n";


	While ($SecUserList = DB_fetch_array($SecUserResult,$db)){

		$CSVContent .= (stripcomma($SecUserList['accountcode']) . ',' .
			stripcomma(iconv( "UTF-8", "gb2312" ,$SecUserList['accountname'])).','. 
			stripcomma(iconv( "UTF-8", "gb2312" ,$SecUserList['group_'])) ."\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . mb_strlen($CSVContent));
	header('Content-Disposition: inline; filename=AccountList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} elseif ( isset($_POST['AccountGroup']) ) {
	$SQL = "SELECT groupname,sectioninaccounts,pandl,sequenceintb,parentgroupname FROM accountgroups";

	$SecUserResult = DB_query($SQL,'','',false,false);

	if (DB_error_no() !=0) {
		$Title = _('AccountGroup List Export  ....');
		include('includes/header.php');
		prnMsg( _('The Account Group List could not be retrieved by the SQL because'). ' - ' . DB_error_msg(), 'error');
		echo '<br /><a href="' .$RootPath .'/index.php">' .   _('Back to the menu'). '</a>';
		if ($debug==1){
			echo '<br />' .  $SQL;
		}
		include('includes/footer.php');
		exit;
	}

	$CSVContent = stripcomma('groupname') . ',' .
			stripcomma('sectioninaccounts') . ','.
			stripcomma('pandl') . ','.
			stripcomma('sequenceintb') . ','.
			stripcomma('parentgroupname') . "\n";


	While ($SecUserList = DB_fetch_array($SecUserResult,$db)){

		$CSVContent .= (stripcomma(iconv( "UTF-8", "gb2312" ,$SecUserList['groupname'])) . ',' .
			stripcomma($SecUserList['sectioninaccounts']) . ',' .
			stripcomma($SecUserList['pandl']) . ',' .
			stripcomma($SecUserList['sequenceintb']) . ',' .
			stripcomma(iconv( "UTF-8", "gb2312" ,$SecUserList['parentgroupname'])) ."\n");
	}
	header('Content-type: application/csv');
	header('Content-Length: ' . mb_strlen($CSVContent));
	header('Content-Disposition: inline; filename=AccountGroupList.csv');
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	echo $CSVContent;
	exit;
} else {
	$Title = _('Data Exports');
	include('includes/header.php');

	// SELECT EXPORT SECURITY GROUPS
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan="2">' . _('Account Group List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='submit' name='AccountGroup' value='" . _('Export') . "' /></div>";
	echo '</div>
          </form><br />';



	
// SELECT EXPORT SECURITY USERS
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan="2">' . _('Account List Export') . '</th></tr>';
	echo '</table>';
	echo '<div class="centre"><input type="submit" name="AccountList" value="' . _('Export') . '" /></div>';
	echo '</div>
          </form><br />';
	// Export Stock For Location
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan="2">' . _('Bill List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='submit' name='BillList' value='" . _('Export') . "' /></div>";
	echo '</div>
          </form><br />';

	// SELECT EXPORT FOR IMAGES
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan="2">' . _('Image List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='submit' name='imagelist' value='" . _('Export') . "' /></div>";
	echo '</div>
          </form><br />';

	// SELECT EXPORT SECURITY TOKENS
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan="2">' . _('Security Token List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='submit' name='sectokenlist' value='" . _('Export') . "' /></div>";
	echo '</div>
          </form><br />';

	// SELECT EXPORT SECURITY ROLES
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan="2">' . _('Security Role List Export') . '</th></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='submit' name='secrolelist' value='" . _('Export') . "' /></div>";
	echo '</div>
          </form><br />';

// Export Stock For Location
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table>';
	echo '<tr><th colspan="2">' . _('Customer List Export') . '</th></tr>';

	$sql = 'SELECT loccode, locationname FROM locations';
	$SalesTypesResult=DB_query($sql);
	echo '<tr><td>' . _('For Location') . ':</td>';
	echo '<td><select name="Location">';
	while ($myrow=DB_fetch_array($SalesTypesResult)){
	          echo '<option value="' . $myrow['loccode'] . '">' . $myrow['locationname'] . '</option>';
	}
	echo '</select></td></tr>';
	echo '</table>';
	echo "<div class='centre'><input type='submit' name='custlist' value='" . _('Export') . "' /></div>";
	echo '</div>
          </form><br />';

	


	include('includes/footer.php');
}
?>