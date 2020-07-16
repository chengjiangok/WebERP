
<?php
/* $Id: SelectCustomer.php 7544 2016-05-28 05:44:34Z daintree $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-03-24 09:46:19 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-03-24 10:46:06
 */
/* Selection of customer - from where all customer related maintenance, transactions and inquiries start */
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
$Title = _('Search Customers');
$ViewTopic = 'AccountsReceivable';
$BookMark = 'SelectCustomer';
include('includes/SQL_CommonFunctions.inc');
include ('includes/ExcelFunction.php');




if(isset($_GET['Select'])) {
	$_SESSION['CustomerID'] = $_GET['Select'];
}

if(!isset($_SESSION['CustomerID'])) {// initialise if not already done
	$_SESSION['CustomerID'] = '';
}

if(isset($_GET['Area'])) {
	$_POST['Area'] = $_GET['Area'];
	$_POST['Search'] = 'Search';
	$_POST['Keywords'] = '';
	$_POST['CustCode'] = '';
	$_POST['CustPhone'] = '';
	$_POST['CustAdd'] = '';
	$_POST['CustType'] = '';
}

if(!isset($_SESSION['CustomerType'])) {// initialise if not already done
	$_SESSION['CustomerType'] = '';
}

if(isset($_POST['JustSelectedACustomer'])) {
	if(isset ($_POST['SubmitCustomerSelection'])) {
	foreach ($_POST['SubmitCustomerSelection'] as $CustomerID => $DebtorNo)
		$_SESSION['CustomerID'] = $CustomerID;
		$_SESSION['DebtorNo'] = $DebtorNo;
	} /*else {
		prnMsg('99'._('Unable to identify the selected customer'), 'error');
	}*/
}
$msg = '';

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
//if (!isset($_POST['CSV'])){
	include('includes/header.php');
	if(isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous']) ) {
		unset($_POST['JustSelectedACustomer']);
		if(isset($_POST['Search'])) {
			$_POST['PageOffset'] = 1;
		}

		if(($_POST['Keywords'] == '') AND ($_POST['CustCode'] == '') AND ($_POST['CustPhone'] == '') AND ($_POST['CustType'] == 'ALL') AND ($_POST['Area'] == 'ALL') AND ($_POST['CustAdd'] == '')) {
			// no criteria set then default to all customers
			$SQL = "SELECT debtorsmaster.name,
						debtorsmaster.debtorno,						
						debtorsmaster.address1,									
						debtorsmaster.contactname,					
						debtorsmaster.phoneno,					
						debtorsmaster.email,
						debtortype.typename,
						debtorsmaster.faxno,
						debtorsmaster.currcode,
						debtorsmaster.address2,
						debtorsmaster.address3,
						debtorsmaster.address4		
					FROM debtorsmaster 			
					INNER JOIN debtortype
					ON debtorsmaster.typeid = debtortype.typeid
					INNER JOIN customerusers ON trim(debtorsmaster.debtorno) =trim(customerusers.regid) 
					WHERE  used>=0 AND  (custype =1 OR custype =3)AND customerusers.userid='".$_SESSION['UserID']."'";
		} else {
			$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['Keywords'])));
			$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));
			$_POST['CustPhone'] = trim($_POST['CustPhone']);
			$_POST['CustAdd'] = trim($_POST['CustAdd']);
			$SQL = "SELECT debtorsmaster.name,
							debtorsmaster.debtorno,							
							debtorsmaster.address1,
							debtorsmaster.contactname,						
							debtorsmaster.phoneno,
							debtorsmaster.email,
							debtortype.typename,							
						    debtorsmaster.currcode,
						    debtorsmaster.address2,
							debtorsmaster.address3,
							debtorsmaster.address4,							
							debtorsmaster.faxno
						FROM debtorsmaster INNER JOIN debtortype
							ON debtorsmaster.typeid = debtortype.typeid					
						INNER JOIN customerusers ON debtorno =customerusers.regid 
						WHERE debtorsmaster.name " . LIKE . " '%" . $SearchKeywords . "%'
						AND debtorsmaster.debtorno " . LIKE . " '%" . $_POST['CustCode'] . "%'
						AND (debtorsmaster.phoneno " . LIKE . " '%" . $_POST['CustPhone'] . "%' OR debtorsmaster.phoneno IS NULL)
						AND  (custype =1 OR custype =3) AND customerusers.userid='".$_SESSION['UserID']."'
						AND (debtorsmaster.address1 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address2 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address3 " . LIKE . " '%" . $_POST['CustAdd'] . "%'
							OR debtorsmaster.address4 " . LIKE . " '%" . $_POST['CustAdd'] . "%')";// If there is no debtorsmaster set, the phoneno in debtorsmaster will be null, so we add IS NULL condition otherwise those debtors without custbranches setting will be no searchable and it will make a inconsistence with customerusers receipt interface.

			if(mb_strlen($_POST['CustType']) > 0 AND $_POST['CustType'] != 'ALL') {
				$SQL .= " AND debtortype.typename = '" . $_POST['CustType'] . "'";
			}

			if(mb_strlen($_POST['Area']) > 0 AND $_POST['Area'] != 'ALL') {
				$SQL .= " AND debtorsmaster.area = '" . $_POST['Area'] . "'";
			}

		}// one of keywords OR custcode OR custphone was more than a zero length string
		//prnMsg($SQL.'[136]','info');
		if($_SESSION['SalesmanLogin'] != '') {
			$SQL .= " AND debtorsmaster.salesman='" . $_SESSION['SalesmanLogin'] . "'";
		}

		$SQL .= "	ORDER BY debtorsmaster.debtorno";
		$ErrMsg = _('The searched customer records requested cannot be retrieved because');
		//prnMsg($SQL);
		$result = DB_query($SQL, $ErrMsg);
		if(DB_num_rows($result) == 1) {
			$myrow = DB_fetch_array($result);
			$_SESSION['CustomerID'] = $myrow['debtorno'];
			$_SESSION['DebtorNo'] = $myrow['debtorno'];
			unset($result);
			unset($_POST['Search']);
		} elseif(DB_num_rows($result) == 0) {
			prnMsg(_('No customer records contain the selected text') . ' - ' . _('please alter your search criteria AND try again'), 'info');
			echo '<br />';
		}
	}// end of if search

	if($_SESSION['CustomerID'] != '' AND !isset($_POST['Search']) AND !isset($_POST['CSV'])) {
		/*if(!isset($_SESSION['DebtorNo'])) {
			// !isset($_SESSION['DebtorNo'])
			$SQL = "SELECT debtorsmaster.name,
						debtorsmaster.phoneno				
				FROM debtorsmaster		
				WHERE debtorsmaster.debtorno='" . $_SESSION['CustomerID'] . "'";

		} else {*/
			// isset($_SESSION['DebtorNo'])
			$SQL = "SELECT debtorsmaster.name,
						debtorsmaster.phoneno					
				FROM debtorsmaster		
				WHERE debtorsmaster.debtorno='" . $_SESSION['CustomerID'] . "'
			";
		
		$ErrMsg = _('The customer name requested cannot be retrieved because');
		$result = DB_query($SQL, $ErrMsg);
		//prnMsg($SQL1,'info');
		if($myrow = DB_fetch_array($result)) {
			$CustomerName = htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false);
			$PhoneNo = $myrow['phoneno'];
			//$BranchName = $myrow['brname'];
		}//
		//if (!isset($_POST['CSV'])){
		//	include('includes/header.php');

		echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
			'/images/customer.png" title="',// Icon image.
			_('Customer'), '" /> ',// Icon title.
			_('Customer'), ' : ', $_SESSION['CustomerID'], ' - ', $CustomerName, ' - ', $PhoneNo, _(' has been selected'), '</p>';// Page title.

		echo '<div class="page_help_text">', _('Select a menu option to operate using this customer'), '.</div>
			<br />
			<table cellpadding="4" width="90%" class="selection">
			<thead>
				<tr>
					<th style="width:33%">', _('Customer Inquiries'), '</th>
					<th style="width:33%">', _('Customer Transactions'), '</th>
					<th style="width:33%">', _('Customer Maintenance'), '</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td valign="top" class="select">';
		// Customer inquiries options:
		echo '<a href="', $RootPath, '/CustomerInquiry.php?CustomerID=', urlencode($_SESSION['CustomerID']), '">' . _('Customer Transaction Inquiries') . '</a><br />';
		echo '<a href="', $RootPath, '/CustomerAccount.php?CustomerID=', urlencode($_SESSION['CustomerID']), '">' . _('Customer Account statement on screen') . '</a><br />';
		echo '<a href="', $RootPath, '/Customers.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '&amp;Modify=No">' . _('View Customer Details') . '</a><br />';
		echo '<a href="', $RootPath, '/PrintCustStatements.php?FromCust=', urlencode($_SESSION['CustomerID']), '&amp;ToCust=', urlencode($_SESSION['CustomerID']), '&amp;PrintPDF=Yes">' . _('Print Customer Statement') . '</a><br />';
		echo '<a href="', $RootPath, '/EmailCustStatements.php?FromCust=', urlencode($_SESSION['CustomerID']), '&amp;ToCust=', urlencode($_SESSION['CustomerID']), '&amp;PrintPDF=Yes">' . _('Email Customer Statement') . '</a><br />';
		echo '<a href="', $RootPath, '/SelectCompletedOrder.php?SelectedCustomer=', urlencode($_SESSION['CustomerID']), '">' . _('Order Inquiries') . '</a><br />';
		echo '<a href="', $RootPath, '/CustomerPurchases.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Show purchases from this customer') . '</a><br />';
		wikiLink('Customer', $_SESSION['CustomerID']);
		echo '</td><td valign="top" class="select">';
		// Customer transactions options:
		echo '<a href="', $RootPath, '/SelectSalesOrder.php?SelectedCustomer=', urlencode($_SESSION['CustomerID']), '">' . _('Modify Outstanding Sales Orders') . '</a><br />';
		echo '<a href="', $RootPath, '/CustomerAllocations.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Allocate Receipts OR Credit Notes') . '</a><br />';
			echo '<a href="', $RootPath, '/CustomerReceipt.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">客户交易对账</a><br />';
		if(isset($_SESSION['CustomerID']) AND isset($_SESSION['DebtorNo'])) {
		echo '<a href="', $RootPath, '/CounterSales.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '&amp;BranchNo=' . $_SESSION['DebtorNo'] . '">' . _('Create a Counter Sale for this Customer') . '</a><br />';
		}
		echo '</td><td valign="top" class="select">';
		// Customer maintenance options:
		echo '<a href="', $RootPath, '/Customers.php">' . _('Add a New Customer') . '</a><br />';
		echo '<a href="', $RootPath, '/Customers.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Modify Customer Details') . '</a><br />';
		echo '<a href="', $RootPath, '/CustomerBranches.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . _('Add/Modify/Delete Customer Branches') . '</a><br />';
		echo '<a href="', $RootPath, '/SelectProduct.php">' . _('Special Customer Prices') . '</a><br />';
		echo '<a href="', $RootPath, '/CustEDISetup.php">' . _('Customer EDI Configuration') . '</a><br />';
		echo '<a href="', $RootPath, '/CustLoginSetup.php">' . _('Customer Login Configuration'), '</a><br />';
		echo '<a href="', $RootPath, '/AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', _('Add a customer contact'), '</a><br />';
		echo '<a href="', $RootPath, '/AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', _('Add a note on this customer'), '</a>';
		echo '</td>
				</tr>
			<tbody>
			</table>';
	
	} elseif (!isset($_POST['CSV'])){
		echo '<table cellpadding="4" width="90%" class="selection">
			<thead>
				<tr>
					<th style="width:33%">', _('Customer Inquiries'), '</th>
					<th style="width:33%">', _('Customer Transactions'), '</th>
					<th style="width:33%">', _('Customer Maintenance'), '</th>
				</tr>
			</thead>
			<tbody>';
		echo '<tr>
				<td class="select"></td>
				<td class="select"></td>
				<td class="select">';
		if(!isset($_SESSION['SalesmanLogin']) OR $_SESSION['SalesmanLogin'] == '') {
			echo '<a href="', $RootPath, '/Customers.php">' . _('Add a New Customer') . '</a><br />';
		}
		echo '</td>
				</tr>
			<tbody>
			</table>';
	}

	// Search for customers:
	if (!isset($_POST['CSV'])){
		echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/customer.png" title="',// Icon image.
		_('Customer'), '" /> ',// Icon title.
		_('Customers'), '</p>';// Page title.
	}

	echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
		'<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
	if(mb_strlen($msg) > 1) {
		prnMsg($msg, 'info');
	}
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/magnifier.png" title="',// Icon image.
		_('Search'), '" /> ',// Icon title.
		_('Search for Customers'), '</p>';// Page title.

	echo '<table cellpadding="3" class="selection">';

	echo '<tr>
			<td colspan="2">', _('Enter a partial Name'), ':</td>
			<td><input type="text" maxlength="25" name="Keywords" title="', _('If there is an entry in this field then customers with the text entered in their name will be returned') , '"  size="20" ',
				( isset($_POST['Keywords']) ? 'value="' . $_POST['Keywords'] . '" ' : '' ), '/></td>';

	echo '<td><b>', _('OR'), '</b></td><td>', _('Enter a partial Code'), ':</td>
			<td><input maxlength="18" name="CustCode" pattern="[\w-]*" size="15" type="text" title="', _('If there is an entry in this field then customers with the text entered in their customer code will be returned') , '" ', (isset($_POST['CustCode']) ? 'value="' . $_POST['CustCode'] . '" ' : '' ), '/></td>
		</tr>';

	echo '<tr>
			<td><b>', _('OR'), '</b></td><td>', _('Enter a partial Phone Number'), ':</td>
			<td><input maxlength="18" name="CustPhone" pattern="[0-9\-\s()+]*" size="15" type="tel" ',
				( isset($_POST['CustPhone']) ? 'value="' . $_POST['CustPhone'] . '" ' : '' ), '/></td>';

	echo '<td><b>', _('OR'), '</b></td><td>', _('Enter part of the Address'), ':</td>
			<td><input maxlength="25" name="CustAdd" size="20" type="text" ',
				(isset($_POST['CustAdd']) ? 'value="' . $_POST['CustAdd'] . '" ' : '' ), '/></td>
		</tr>';

	echo '<tr>
			<td><b>', _('OR'), '</b></td><td>', _('Choose a Type'), ':</td>
			<td>';
	if(isset($_POST['CustType'])) {
		// Show Customer Type drop down list
		$result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
		// Error if no customer types setup
		if(DB_num_rows($result2) == 0) {
			$DataError = 1;
			echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
			echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
		} else {
			// If OK show select box with option selected
			echo '<select name="CustType">
					<option value="ALL">' . _('Any') . '</option>';
			while ($myrow = DB_fetch_array($result2)) {
				if($_POST['CustType'] == $myrow['typename']) {
					echo '<option selected="selected" value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
				}// $_POST['CustType'] == $myrow['typename']
				else {
					echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
				}
			}// end while loop
			DB_data_seek($result2, 0);
			echo '</select></td>';
		}
	} else {// CustType is not set
		// No option selected="selected" yet, so show Customer Type drop down list
		$result2 = DB_query("SELECT typeid, typename FROM debtortype ORDER BY typename");
		// Error if no customer types setup
		if(DB_num_rows($result2) == 0) {
			$DataError = 1;
			echo '<a href="CustomerTypes.php" target="_parent">' . _('Setup Types') . '</a>';
			echo '<tr><td colspan="2">' . prnMsg(_('No Customer types defined'), 'error') . '</td></tr>';
		} else {
			// if OK show select box with available options to choose
			echo '<select name="CustType">
					<option value="ALL">' . _('Any') . '</option>';
			while ($myrow = DB_fetch_array($result2)) {
				echo '<option value="' . $myrow['typename'] . '">' . $myrow['typename'] . '</option>';
			}// end while loop
			DB_data_seek($result2, 0);
			echo '</select></td>';
		}
	}

	/* Option to select a sales area */
	echo '<td><b>', _('OR'), '</b></td>
			<td>' . _('Choose an Area') . ':</td><td>';
	$result2 = DB_query("SELECT areacode, areadescription FROM areas");
	// Error if no sales areas setup
	if(DB_num_rows($result2) == 0) {
		$DataError = 1;
		echo '<a href="Areas.php" target="_parent">' . _('Setup Areas') . '</a>';
		echo '<tr><td colspan="2">' . prnMsg(_('No Sales Areas defined'), 'error') . '</td></tr>';
	} else {
		// if OK show select box with available options to choose
		echo '<select name="Area">';
		echo '<option value="ALL">' . _('Any') . '</option>';
		while ($myrow = DB_fetch_array($result2)) {
			if(isset($_POST['Area']) AND $_POST['Area'] == $myrow['areacode']) {
				echo '<option selected="selected" value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
			} else {
				echo '<option value="' . $myrow['areacode'] . '">' . $myrow['areadescription'] . '</option>';
			}
		}// end while loop
		DB_data_seek($result2, 0);
		echo '</select></td></tr>';
	}

	echo '</table><br />';
	echo '<div class="centre">
			<input name="Search" type="submit" value="', _('Search Now'), '" />';
	if(isset($result))
	echo'<input name="CSV" type="submit" value="导出Excel" />';
	echo'</div>';
	// End search for customers.
	if(isset($_SESSION['SalesmanLogin']) AND $_SESSION['SalesmanLogin'] != '') {
		prnMsg(_('Your account enables you to see only customers allocated to you'), 'warn', _('Note: Sales-person Login'));
	}
//}//csv
if(isset($_POST['CSV'])) {// producing a CSV file of customers

	$options = array("print"=>true);//,"setWidth"=>$setWidth);
	DB_data_seek($result,0);
	while($row=DB_fetch_row($result)){
	//	var_dump($row);
		$Datas[]=array($row[0],$row[1],$row[2],$row[3],$row[4],$row[5],$row[6],$row[7]);
		//prnMsg($row[5]);	
	}
	/*
	,"freezePane"=>"A2","setARGB"=>"['A1', 'C1']","setWidth"=>"['A' => 30, 'C' => 20]"
						   ,"setBorder"=>0,"mergeCells"=>"['A1:J1' => 'A1:J1']","formula"=>"['F2' => '=IF(D2>0,E42/D2,0)']"
						   ,"format"=>"['A' => 'General']","alignCenter"=>"['A1', 'A2']","bold"=>"['A1', 'A2']","savePath"=>"C:\Wnmp\html\GJWERP\companies\hualu_erp" );
	*/
	$FileName ="客户名单_". date('Y-m-d', time()).rand(1000, 9999);
	$TitleData=array("Title"=>'客户名单',"FileName"=>$FileName,"TitleDate"=>"2020-03-26","Compy"=>"华陆数控公司","Units"=>"元","k"=>3);
	

	 $Header=array("序号","客户名称","客户编码","地址","联系人","电话","邮件","客户类型");		  
	exportExcelCust($Datas,$Header,$TitleData,$options);
	//	$CSVListing ='"';
	/*
	$CSVListing .=iconv( "UTF-8", "gbk//TRANSLIT",'客户编码').','.iconv( "UTF-8", "gbk//TRANSLIT","客户名称").','.iconv( "UTF-8", "gbk//TRANSLIT","地址").','.iconv( "UTF-8", "gbk//TRANSLIT","联系人").','.iconv( "UTF-8", "gbk//TRANSLIT","电话"). ','.iconv( "UTF-8", "gbk//TRANSLIT","邮件").','.iconv( "UTF-8", "gbk//TRANSLIT","客户类型")."\n";
	while ($row = DB_fetch_row($result)) {
		$CSVListing .= '"';
		$CSVListing .= iconv("UTF-8","gbk//TRANSLIT",$row['debtorno'].'","'.$row['name'].'","'.$row['address1'].'","'.$row['conactname'].'"').'","'.$row['phoneno'].'","'.$row['email'].'","'.$row['typename'].'"'. "\n";
	}
	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
	header('Content-disposition: attachment; filename='.iconv( "UTF-8", "gbk//TRANSLIT","客户列表_") .  date('Y-m-d')  .'.csv');
	header("Pragma: public");
	header("Expires: 0");
	//echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $CSVListing;
	exit;*/
	/*
		$FileName = $_SESSION['reports_dir'] . '/Customer_Listing_' . date('Y-m-d') . '.csv';
		echo '<br /><p class="page_title_text"><a href="' . $FileName . '">' . _('Click to view the csv Search Result') . '</p>';
		$fp = fopen($FileName, 'w');
		while ($myrow2 = DB_fetch_array($result)) {
			fwrite($fp, $myrow2['debtorno'] . ',' . str_replace(',', '', $myrow2['name']) . ',' . str_replace(',', '', $myrow2['address1']) . ',' . str_replace(',', '', $myrow2['address2']) . ',' . str_replace(',', '', $myrow2['address3']) . ',' . str_replace(',', '', $myrow2['address4']) . ',' . str_replace(',', '', $myrow2['contactname']) . ',' . str_replace(',', '', $myrow2['typename']) . ',' . $myrow2['phoneno'] . ',' . $myrow2['faxno'] . ',' . $myrow2['email'] . "\n");
		}// end loop through customers returned
		*/
}// end if producing a CSV
 DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
if(isset($result) &&!isset($_POST['CSV'])) {
	unset($_SESSION['CustomerID']);
	$ListCount = DB_num_rows($result);
	$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
	if(!isset($_POST['CSV'])) {
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
		if($ListPageMax > 1) {
			echo '<br /><div class="centre">&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
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
	//}	
	if(DB_num_rows($result) <> 0) {
		echo '<table cellpadding="2" class="selection">
				<thead>
					<tr>
					    <th class="ascending">序号</th>
						<th class="ascending">' . _('Code') . '</th>
						<th class="ascending">' . _('Customer Name') . '</th>	
						<th class="ascending">地址</th>				
						<th class="ascending">' . _('Contact') . '</th>						
						<th class="ascending">' . _('Phone') . '</th>					
						<th class="ascending">' . _('Email') . '</th>
						<th class="ascending">' . _('Type') . '</th>
					</tr>
				</thead>';
		$k = 0;// row counter to determine background colour
		$RowIndex = 0;
	// end if NOT producing a CSV file
	
	
		//if(!isset($_POST['CSV'])) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		//}
		$i = 0;// counter for input controls
		echo '<tbody>';
		while (($myrow = DB_fetch_array($result)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
			if($k == 1) {
				echo '<tr class="EvenTableRows">';
				$k = 0;
			} else {
				echo '<tr class="OddTableRows">';
				$k = 1;
			}
			echo '	<td class="text">'.($RowIndex+1).'</td>
			<td><button type="submit" name="SubmitCustomerSelection[', htmlspecialchars($myrow['debtorno'], ENT_QUOTES, 'UTF-8', false), ']" value="', htmlspecialchars($myrow['debtorno'], ENT_QUOTES, 'UTF-8', false), '" >', $myrow['debtorno'], '</button></td>
			
				<td class="text">','[',$myrow['currcode'],']', htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', $myrow['address1'], '</td>
				<td class="text">', $myrow['contactname'], '</td>				
				<td class="text">', $myrow['phoneno'], '</td>			
				<td class="text">', $myrow['email'], '</td>
				<td class="text">', $myrow['typename'], '</td>
			</tr>';
			$i++;
			$RowIndex++;
			// end of page full new headings if
		}// end loop through customers
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedACustomer" value="Yes" />';
	}// end if there are customers to show
	}
}

if(!isset($_POST['CSV'])) {
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
	}// end if results to show
}

echo '</form>';

// Only display the geocode map if the integration is turned on, AND there is a latitude/longitude to display
if(isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID'] != ''&& !isset($_POST['CSV'])) {

	if($_SESSION['geocode_integration'] == 1) {

		$SQL = "SELECT * FROM geocode_param WHERE 1";
		$ErrMsg = _('An error occurred in retrieving the information');
		$result = DB_query($SQL, $ErrMsg);
		if(DB_num_rows($result) == 0) {
			prnMsg( _('You must first setup the geocode parameters') . ' ' . '<a href="' . $RootPath . '/GeocodeSetup.php">' . _('here') . '</a>', 'error');
			include('includes/footer.php');
			exit;
		}
		$myrow = DB_fetch_array($result);
		$API_key = $myrow['geocode_key'];
		$center_long = $myrow['center_long'];
		$center_lat = $myrow['center_lat'];
		$map_height = $myrow['map_height'];
		$map_width = $myrow['map_width'];
		$map_host = $myrow['map_host'];
		if($map_host == '') {$map_host = 'maps.googleapis.com';}// If $map_host is empty, use a default map host.

		$SQL = "SELECT
					debtorsmaster.debtorno,
					debtorsmaster.name,
				
					debtorsmaster.lat,
					debtorsmaster.lng,
					debtorsmaster.braddress1,
					debtorsmaster.braddress2,
					debtorsmaster.braddress3,
					debtorsmaster.braddress4,
					debtorsmaster.currcode
				FROM debtorsmaster
			
				WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'
				
				ORDER BY debtorsmaster.debtorno";
		$ErrMsg = _('An error occurred in retrieving the information');
		$result2 = DB_query($SQL, $ErrMsg);
		$myrow2 = DB_fetch_array($result2);
		$Lat = $myrow2['lat'];
		$Lng = $myrow2['lng'];

		if($Lat == 0 and $myrow2["braddress1"] != '' and $_SESSION['DebtorNo'] != '') {
			$delay = 0;
			$base_url = "https://" . $map_host . "/maps/api/geocode/xml?address=";

			$geocode_pending = true;
			while ($geocode_pending) {
				$address = urlencode($myrow2["braddress1"] . "," . $myrow2["braddress2"] . "," . $myrow2["braddress3"] . "," . $myrow2["braddress4"]);
				$id = $myrow2["debtorno"];
				$debtorno =$myrow2["debtorno"];
				$request_url = $base_url . $address . ',&sensor=true';

				$buffer = file_get_contents($request_url)/* or die("url not loading")*/;
				$xml = simplexml_load_string($buffer);
				// echo $xml->asXML();

				$status = $xml->status;
				if(strcmp($status, "OK") == 0) {
					$geocode_pending = false;

					$Lat = $xml->result->geometry->location->lat;
					$Lng = $xml->result->geometry->location->lng;

					$query = sprintf("UPDATE debtorsmaster " .
							" SET lat = '%s', lng = '%s' " .
							" WHERE debtorno = '%s' LIMIT 1;",
							($Lat),
							($Lng),
						
							($debtorno));
					$update_result = DB_query($query);

					if($update_result == 1) {
						prnMsg( _('GeoCode has been updated for CustomerID') . ': ' . $id . ' - ' . _('Latitude') . ': ' . $Lat . ' ' . _('Longitude') . ': ' . $Lng ,'info');
					}
				} else {
					$geocode_pending = false;
					prnMsg(_('Unable to update GeoCode for CustomerID') . ': ' . $id . ' - ' . _('Received status') . ': ' . $status , 'error');
				}
				usleep($delay);
			}
		}

		echo '<br />';
		if($Lat == 0) {
			echo '<div class="centre">' . _('Mapping is enabled, but no Mapping data to display for this Customer.') . '</div>';
		} else {
			echo '<table cellpadding="4">
				<thead>
					<tr>
						<th style="width:auto">', _('Customer Mapping'), '</th>
					</tr>
					<tr>
						<th style="width:auto">', _('Mapping is enabled, Map will display below.'), '</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><div class="center" id="map" style="height:', $map_height . 'px; margin: 0 auto; width:', $map_width, 'px;"></div></td>
					</tr>
				</tbody>
				</table>';

		// Reference: Google Maps JavaScript API V3, https://developers.google.com/maps/documentation/javascript/reference.
	echo '<script type="text/javascript">
	var map;
	function initMap() {

		var myLatLng = {lat: ', $Lat, ', lng: ', $Lng, '};', /* Fills with customer's coordinates. */'

		var map = new google.maps.Map(document.getElementById(\'map\'), {', /* Creates the map with the road map view. */'
			center: myLatLng,
			mapTypeId: google.maps.MapTypeId.ROADMAP,
			zoom: 14
		});

		var contentString =', /* Fills the content to be displayed in the InfoWindow. */'
			\'<div style="overflow: auto;">\' +
			\'<div><b>', $BranchName, '</b></div>\' +
			\'<div>', $myrow2['braddress1'], '</div>\' +
			\'<div>', $myrow2['braddress2'], '</div>\' +
			\'<div>', $myrow2['braddress3'], '</div>\' +
			\'<div>', $myrow2['braddress4'], '</div>\' +
			\'</div>\';

		var infowindow = new google.maps.InfoWindow({', /* Creates an info window to display the content of 'contentString'. */'
			content: contentString,
			maxWidth: 250
		});

		var marker = new google.maps.Marker({', /* Creates a marker to identify a location on the map. */'
			position: myLatLng,
			map: map,
			title: \'', $CustomerName, '\'
		});

		marker.addListener(\'click\', function() {', /* Creates the event clicking the marker to display the InfoWindow. */'
			infowindow.open(map, marker);
		});
	}
	</script>
	<script async defer src="https://maps.googleapis.com/maps/api/js?key=', $API_key, '&callback=initMap"></script>';
	/*		echo '<script src="https://' . $map_host . '/maps/api/js?v=3.exp&key=' . $API_key . '" type="text/javascript"></script>';*/
		}

	}// end if Geocode integration is turned on

	// Extended Customer Info only if selected in Configuration
	if($_SESSION['Extended_CustomerInfo'] == 1) {
		if($_SESSION['CustomerID'] != '') {
			$SQL = "SELECT debtortype.typeid,
							debtortype.typename
						FROM debtorsmaster INNER JOIN debtortype
					ON debtorsmaster.typeid = debtortype.typeid
					WHERE debtorsmaster.debtorno = '" . $_SESSION['CustomerID'] . "'";
			$ErrMsg = _('An error occurred in retrieving the information');
			$result = DB_query($SQL, $ErrMsg);
			$myrow = DB_fetch_array($result);
			$CustomerType = $myrow['typeid'];
			$CustomerTypeName = $myrow['typename'];
			// Customer Data
			echo '<br />';
			// Select some basic data about the Customer
			$SQL = "SELECT debtorsmaster.clientsince,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.clientsince))) as customersincedays,
						(TO_DAYS(date(now())) - TO_DAYS(date(debtorsmaster.lastpaiddate))) as lastpaiddays,
						debtorsmaster.paymentterms,
						debtorsmaster.lastpaid,
						debtorsmaster.lastpaiddate,
						currencies.decimalplaces AS currdecimalplaces
					FROM debtorsmaster INNER JOIN currencies
					ON debtorsmaster.currcode=currencies.currabrev
					WHERE debtorsmaster.debtorno ='" . $_SESSION['CustomerID'] . "'";
			$DataResult = DB_query($SQL);
			$myrow = DB_fetch_array($DataResult);
			// Select some more data about the customer
			$SQL = "SELECT sum(ovamount+ovgst) as total
					FROM debtortrans
					WHERE debtorno = '" . $_SESSION['CustomerID'] . "'
					AND type !=12";
			$Total1Result = DB_query($SQL);
			$row = DB_fetch_array($Total1Result);
			echo '<table cellpadding="4" style="width: 45%;">
				<tr>
					<th colspan="3" style="width:auto">', _('Customer Data'), '</th>
				</tr>
				<tr>
					<td class="select" valign="top">';
			/* Customer Data */
			if($myrow['lastpaiddate'] == 0) {
				echo _('No receipts from this customer.'), '</td>
					<td class="select">&nbsp;</td>
					<td class="select">&nbsp;</td>
				</tr>';
			} else {
				echo _('Last Paid Date'), ':</td>
					<td class="select"><b>' . ConvertSQLDate($myrow['lastpaiddate']), '</b></td>
					<td class="select">', $myrow['lastpaiddays'], ' ', _('days'), '</td>
				</tr>';
			}
			echo '<tr>
					<td class="select">', _('Last Paid Amount (inc tax)'), ':</td>
					<td class="select"><b>', locale_number_format($myrow['lastpaid'], $myrow['currdecimalplaces']), '</b></td>
					<td class="select">&nbsp;</td>
				</tr>';
			echo '<tr>
					<td class="select">', _('Customer since'), ':</td>
					<td class="select"><b>', ConvertSQLDate($myrow['clientsince']), '</b></td>
					<td class="select">', $myrow['customersincedays'], ' ', _('days'), '</td>
				</tr>';
			if($row['total'] == 0) {
				echo '<tr>
						<td class="select"><b>', _('No Spend from this Customer.'), '</b></td>
						<td class="select">&nbsp;</td>
						<td class="select">&nbsp;</td>
					</tr>';
			} else {
				echo '<tr>
						<td class="select">' . _('Total Spend from this Customer (inc tax)') . ':</td>
						<td class="select"><b>' . locale_number_format($row['total'], $myrow['currdecimalplaces']) . '</b></td>
						<td class="select"></td>
						</tr>';
			}
			echo '<tr>
					<td class="select">', _('Customer Type'), ':</td>
					<td class="select"><b>', $CustomerTypeName, '</b></td>
					<td class="select">&nbsp;</td>
				</tr>';
			echo '</table>';
		}// end if $_SESSION['CustomerID'] != ''

		// Customer Contacts
		$SQL = "SELECT * FROM custcontacts
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY contid";
		$result = DB_query($SQL);

		if(DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" />' . ' ' . _('Customer Contacts') . '</div>';
			echo '<br /><table width="45%">
 					<thead>
						<tr>
							<th class="ascending">' . _('Name') . '</th>
							<th class="ascending">' . _('Role') . '</th>
							<th class="ascending">' . _('Phone Number') . '</th>
							<th class="ascending">' . _('Email') . '</th>
							<th class="text">' . _('Statement') . '</th>
							<th class="text">', _('Notes'), '</th>
							<th class="noprint">', _('Edit'), '</th>
							<th class="noprint">' . _('Delete') . '</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th colspan="7"><a href="AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">', _('Add New Contact'), '</a></th>
						</tr>
					</tfoot>
					<tbody>';
			$k = 0;// row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				}// $k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' , $myrow[2] , '</td>
					<td>' , $myrow[3] , '</td>
					<td>' , $myrow[4] , '</td>
					<td><a href="mailto:' , $myrow[6] , '">' , $myrow[6] . '</a></td>
					<td>' , ($myrow[7]==0) ? _('No') : _('Yes'), '</td>
					<td>' , $myrow[5] , '</td>
					<td><a href="AddCustomerContacts.php?Id=' , $myrow[0] , '&amp;DebtorNo=' , $myrow[1] , '">' , _('Edit') , '</a></td>
					<td><a href="AddCustomerContacts.php?Id=' , $myrow[0] , '&amp;DebtorNo=' , $myrow[1] , '&amp;delete=1">' , _('Delete') , '</a></td>
					</tr>';
			}// END WHILE LIST LOOP

			// Customer Branch Contacts if selected
			if(isset ($_SESSION['DebtorNo']) AND $_SESSION['DebtorNo'] != '') {
				$SQL = "SELECT
							debtorno,
							name,
							contactname,
							phoneno,
							email
						FROM debtorsmaster
						WHERE debtorno='" . $_SESSION['CustomerID'] . "'";
							//AND branch code='" . $_SESSION['DebtorNo'] . "'";
				$result2 = DB_query($SQL);
				$BranchContact = DB_fetch_row($result2);

				echo '<tr class="EvenTableRows">
						<td>' . $BranchContact[2] . '</td>
						<td>' . _('Branch Contact') . ' ' . $BranchContact[0] . '</td>
						<td>' . $BranchContact[3] . '</td>
						<td><a href="mailto:' . $BranchContact[4] . '">' . $BranchContact[4] . '</a></td>
						<td colspan="3"></td>
					</tr>';
			}
			echo '</tbody>
			</table>';
		}// end if there are contact rows returned
		else {
			if($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/group_add.png" title="' . _('Customer Contacts') . '" alt="" /><a href="AddCustomerContacts.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . ' ' . _('Add New Contact') . '</a></div>';
			}
		}
		// Customer Notes
		$SQL = "SELECT
					noteid,
					debtorno,
					href,
					note,
					date,
					priority
				FROM custnotes
				WHERE debtorno='" . $_SESSION['CustomerID'] . "'
				ORDER BY date DESC";
		$result = DB_query($SQL);
		if(DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" />' . ' ' . _('Customer Notes') . '</div><br />';
			echo '<table style="width: 45%;">';
			echo '<tr>
					<th class="ascending">' . _('Date') . '</th>
					<th>' . _('Note') . '</th>
					<th>' . _('Hyperlink') . '</th>
					<th class="ascending">' . _('Priority') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th> <a href="AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . ' ' . _('Add New Note') . '</a> </th>
				</tr>';
			$k = 0;// row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				}// $k == 1
				else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . ConvertSQLDate($myrow['date']) . '</td>
					<td>' . $myrow['note'] . '</td>
					<td><a href="' . $myrow['href'] . '">' . $myrow['href'] . '</a></td>
					<td>' . $myrow['priority'] . '</td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&amp;DebtorNo=' . $myrow['debtorno'] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerNotes.php?Id=' . $myrow['noteid'] . '&amp;DebtorNo=' . $myrow['debtorno'] . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			}// END WHILE LIST LOOP
			echo '</table>';
		}// end if there are customer notes to display
		else {
			if($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/note_add.png" title="' . _('Customer Notes') . '" alt="" /><a href="AddCustomerNotes.php?DebtorNo=', urlencode($_SESSION['CustomerID']), '">' . ' ' . _('Add New Note for this Customer') . '</a></div>';
			}
		}
		// Custome Type Notes
		$SQL = "SELECT * FROM debtortypenotes
				WHERE typeid='" . $CustomerType . "'
				ORDER BY date DESC";
		$result = DB_query($SQL);
		if(DB_num_rows($result) <> 0) {
			echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/folder_add.png" title="' . _('Customer Type (Group) Notes') . '" alt="" />' . ' ' . _('Customer Type (Group) Notes for:' . '<b> ' . $CustomerTypeName . '</b>') . '</div><br />';
			echo '<table style="width: 45%;">';
			echo '<tr>
				 	<th class="ascending">' . _('Date') . '</th>
					<th>' . _('Note') . '</th>
					<th>' . _('File Link / Reference / URL') . '</th>
					<th class="ascending">' . _('Priority') . '</th>
					<th>' . _('Edit') . '</th>
					<th>' . _('Delete') . '</th>
					<th><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . _('Add New Group Note') . '</a></th>
				</tr>';
			$k = 0;// row colour counter
			while ($myrow = DB_fetch_array($result)) {
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				echo '<td>' . $myrow[4] . '</td>
					<td>' . $myrow[3] . '</td>
					<td>' . $myrow[2] . '</td>
					<td>' . $myrow[5] . '</td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&amp;DebtorType=' . $myrow[1] . '">' . _('Edit') . '</a></td>
					<td><a href="AddCustomerTypeNotes.php?Id=' . $myrow[0] . '&amp;DebtorType=' . $myrow[1] . '&amp;delete=1">' . _('Delete') . '</a></td>
					</tr>';
			}// END WHILE LIST LOOP
			echo '</table>';
		}// end if there are customer group notes to display
		else {
			if($_SESSION['CustomerID'] != '') {
				echo '<br /><div class="centre"><img src="' . $RootPath . '/css/' . $Theme . '/images/folder_add.png" title="' . _('Customer Group Notes') . '" alt="" /><a href="AddCustomerTypeNotes.php?DebtorType=' . $CustomerType . '">' . ' ' . _('Add New Group Note') . '</a></div><br />';
			}
		}
	}// end if Extended_CustomerInfo is turned on
}// end if isset($_SESSION['CustomerID']) AND $_SESSION['CustomerID'] != ''



include('includes/footer.php');

/**
   * Excel导出，TODO 可继续优化
   *
   * @param array  $datas      导出数据，格式['A1' => 'XXXX公司报表', 'B1' => '序号']
   * @param array  $header   导出文件名称
   * @param array  $TitleData "Title"=>'客户名单',
   * 						  "FileName"=>$FileName,
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
   *                           array  format      设置格式，整列设置，例如['A' => 'General']
   *                           array  alignCenter 设置居中样式，例如['A1', 'A2']
   *                           array  bold        设置加粗样式，例如['A1', 'A2']
   *                           string savePath    保存路径，设置后则文件保存到服务器，不通过浏览器下载
   */	
  function exportExcelCust($data,$header,$titledata,$options){
	require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';

   
		$spreadsheet = new Spreadsheet();
		set_time_limit(0);
		$columnCnt=count($header);
		$rowCnt=count($data); 
		$k=$titledata['k'];
		// @var Spreadsheet  $spreadsheet 
		
		$sheet = $spreadsheet->getActiveSheet();
		//设置sheet的名字  两种方法
		$sheet->setTitle($titledata['FileName']);
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
		$activeSheet->getStyle('A'.(int)($k+1).':'.Coordinate::stringFromColumnIndex($columnCnt).($k+$rowCnt))->applyFromArray($styleArray);
		  /* 设置宽度 */
		$activeSheet->getColumnDimension('B')->setWidth(40);
		//$activeSheet->getColumnDimension('B')->setAutoSize(true);
		$activeSheet->getColumnDimension('C')->setAutoSize(true);
		$activeSheet->getColumnDimension('D')->setWidth(30);
		$activeSheet->getColumnDimension('F')->setWidth(15);
		$activeSheet->getColumnDimension('G')->setWidth(25);
        //foreach ($options['setWidth'] as $swKey => $swItem) {
		//	$activeSheet->getColumnDimension($swKey)->setWidth($swItem);
	    //}  	
     
	for ($_row = 1; $_row <= $rowCnt; $_row++) {
		for ($_column = 1; $_column <= $columnCnt; $_column++) {
			$cellName = Coordinate::stringFromColumnIndex($_column);
			$cellId   = $cellName . ($_row+$k);
		  
			if ($_row==1){
				//表头
				$sheet->setCellValue($cellName.($_row+$k),  (string)$header[$_column-1]); 
				
								//$activeSheet->getColumnDimension("B")->setWidth(30);
				//$celldata[$_row][$cellName] = (string)$header[$_column-1];
			  
			}else{
				if ($_column==1){
					//  序号列  $celldata[$_row][$cellName] = $_row;
					$sheet->setCellValue($cellName.($_row+$k), $_row-1); 
				}else{
					$sheet->setCellValue($cellName.($_row+$k), (string)$data[$_row-1][$_column-2]); 
					//$celldata[$_row][$cellName] =$data[$_row-1][$_column-1];
                }
               // prnMsg($data[$_row-1][$_column-1]);
				
			}

			if (!empty($data[$_row-1][$cellName-1])) {
				$isNull = false;
			}
		}
	}
	
	//循环赋值
     // var_dump($celldata);

	
	//第一种保存方式
	/*	$writer = new Xlsx($spreadsheet);
	//保存的路径可自行设置
	$file_name = '../'.$file_name . ".xlsx";
	$writer->save($file_name);
	///第二种直接页面上显示下载
	*/
	
	$filename=$titledata['FileName'].".xlsx";
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
