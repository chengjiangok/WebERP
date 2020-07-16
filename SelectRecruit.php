<?php
/* $Id: Selectemployee.php 7544 2016-05-28 05:44:34Z daintree $*/
/* Selection of employee - from where all employee related maintenance, transactions and inquiries start */

include('includes/session.php');
$Title = _('Recruit Employee');
$ViewTopic = 'HumanResources';
	$BookMark = 'SelectRecruit';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/customer.png" title="',// Icon image.
	$Title, '" /> ',// Icon title.
	$Title, '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if(isset($_GET['Select'])) {
	$_SESSION['Employe'] = $_GET['Select'];
}

if(!isset($_SESSION['Employe'])) {// initialise if not already done
	$_SESSION['Employe'] = '';
}

if(isset($_POST['JustSelectedAEmploye'])) {
	if(isset ($_POST['SubmitEmployeSelection'])) {
	foreach ($_POST['SubmitEmployeSelection'] as $EmployeID => $BranchCode)
		$_SESSION['Employe'] = $EmployeID;

	} else {
		prnMsg(_('Unable to identify the selected Employe'), 'error');
	}
	
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

if(isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	unset($_POST['JustSelectedAEmploye']);
	if(isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}

	if(($_POST['Keywords'] == '') AND ($_POST['placeforgin'] == '') AND ($_POST['Phone'] == '') AND  ($_POST['empdate'] == '') AND  ($_POST['working'] == '7') AND ($_POST['address'] == '') AND ($_POST['startwork'] == '')) {
		// no criteria set then default to all emplayee
			
				$SQL="SELECT empname,
				             emplid, 
				             empdate, 
				             sex,
				             nationality, 
				             marriage,
				             birthdate,
				             weight,
				             height,
				             cardid,
				             placeoforigin,
				             address,
				             accountlocation,
				             graduateinstitutions,
				             education,
				             tel,
				             email,
				             workdate,
				             job,
				             working,
				             startwork,
				             social,
				             hopewages,
				             minwages,
				             experience,
				             myevaluate,
				             personnel,
				             department,
				             manager,
				             decision,
				             perdecision,
				             depdecision,
				             mandecision
				             FROM employfile";
	} else {
			prnMsg($_POST['working'],'info');
		$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['Keywords'])));
		$_POST['CustCode'] = mb_strtoupper(trim($_POST['CustCode']));
		$_POST['Phone'] = trim($_POST['Phone']);
		$_POST['CustAdd'] = trim($_POST['CustAdd']);
		
				$SQL="SELECT empname,
				             emplid, 
				             empdate, 
				             sex,
				             nationality, 
				             marriage,
				             birthdate,
				             weight,
				             height,
				             cardid,
				             placeoforigin,
				             address,
				             accountlocation,
				             graduateinstitutions,
				             education,
				             tel,
				             email,
				             workdate,
				             job,
				             working,
				             startwork,
				             social,
				             hopewages,
				             minwages,
				             experience,
				             myevaluate,
				             personnel,
				             department,
				             manager,
				             decision,
				             perdecision,
				             depdecision,
				             mandecision
				             FROM employfile 	WHERE empname " . LIKE . " '%" . $SearchKeywords . "%'
					AND placeforgin " . LIKE . " '%" . $_POST['CustCode'] . "%'
					AND working ='".$_POST['working']."'
					AND (tel " . LIKE . " '%" . $_POST['Phone'] . "%' OR tel IS NULL)
					AND (address " . LIKE . " '%" . $_POST['address'] . "%'";
		
		
		

		if($_POST['empdate']!='' AND ( $_POST['empday'] == '' OR  $_POST['empday'] == 0)) {
			$SQL .= " AND empdate= '" . $_POST['empdate'] . "'";
		}else 	if($_POST['empdate']!='' AND  $_POST['empday'] > 0) {
			$SQL .= " AND empdate >= '" . $_POST['empdate'] . "' AND empdate <= '" . $_POST['empdate'] . "'";
    } 	if($_POST['empdate']!='' AND  $_POST['empday'] < 0) {
			$SQL .= " AND empdate <= '" . $_POST['empdate'] . "' AND empdate >= '" . $_POST['empdate'] . "'";
    }
			if($_POST['startwork']!='' AND ( $_POST['startwork'] == '' OR  $_POST['startwork'] == 0)) {
			$SQL .= " AND startwork= '" . $_POST['startwork'] . "'";
		}else 	if($_POST['startwork']!='' AND  $_POST['startwork'] > 0) {
			$SQL .= " AND startwork >= '" . $_POST['startwork'] . "' AND startwork <= '" . $_POST['startwork'] . "'";
    } 	if($_POST['startwork']!='' AND  $_POST['startwork'] < 0) {
			$SQL .= " AND startwork <= '" . $_POST['startwork'] . "' AND startwork >= '" . $_POST['startwork'] . "'";
    }

	}// one of keywords OR custcode OR phone was more than a zero length string


	$SQL .= " ORDER BY empname";
	$ErrMsg = _('The search for the recruitment of staff to record the request can not be retrieved because');

	$result = DB_query($SQL, $ErrMsg);
	if(DB_num_rows($result) == 1) {
		$myrow = DB_fetch_array($result);
		$_SESSION['Employe'] = $myrow['empname'];
		$_SESSION['BranchCode'] = $myrow['emplid'];
		unset($result);
		unset($_POST['Search']);
	} elseif(DB_num_rows($result) == 0) {
		prnMsg(_('No recruiting staff record contains the selected text') . ' - ' . _('Please change your search terms, and then try again'), 'info');
		echo '<br />';
	}
}// end of if search


if($_SESSION['Employe'] != '' AND !isset($_POST['Search']) AND !isset($_POST['CSV'])) {
/*	if(!isset($_SESSION['BranchCode'])) {
		// !isset($_SESSION['BranchCode'])
		$SQL = "SELECT debtorsmaster.name,
					custbranch.phoneno,
					custbranch.brname
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.EmpoyeNo=custbranch.EmpoyeNo
			WHERE custbranch.EmpoyeNo='" . $_SESSION['Employe'] . "'";

	} else {
		// isset($_SESSION['BranchCode'])
		$SQL = "SELECT debtorsmaster.name,
					custbranch.phoneno,
					custbranch.brname
			FROM debtorsmaster INNER JOIN custbranch
			ON debtorsmaster.EmpoyeNo=custbranch.EmpoyeNo
			WHERE custbranch.EmpoyeNo='" . $_SESSION['Employe'] . "'
			AND custbranch.branchcode='" . $_SESSION['BranchCode'] . "'";
	}
	$ErrMsg = _('The employee name requested cannot be retrieved because');
	$result = DB_query($SQL, $ErrMsg);
	if($myrow = DB_fetch_array($result)) {
		$EmployeName = htmlspecialchars($myrow['name'], ENT_QUOTES, 'UTF-8', false);
		$PhoneNo = $myrow['phoneno'];
		$BranchName = $myrow['brname'];
	}// $myrow = DB_fetch_array($result)
	unset($result);
*/
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/customer.png" title="',// Icon image.
		_('Employee'), '" /> ',// Icon title.
		_('Employee'), ' : ', $_SESSION['Employe'], ' - ', $EmployeName, ' - ', $PhoneNo, _(' has been selected'), '</p>';// Page title.

	echo '<div class="page_help_text">', _('Select a menu option to operate using this employee'), '.</div>
		<br />
		<table cellpadding="4" width="90%" class="selection">
		<thead>
			<tr>
				<th style="width:33%">', _('Staff query'), '</th>
				<th style="width:33%">', _('Staff operation'), '</th>
				<th style="width:33%">',_('Staff maintenance'), '</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td valign="top" class="select">';
	// employee inquiries options:
	echo '<a href="', $RootPath, '/employeeInquiry.php?EmployeID=', urlencode($_SESSION['Employe']), '">' . _('Attendance query'). '</a><br />';
	echo '<a href="', $RootPath, '/employeeAccount.php?EmployeID=', urlencode($_SESSION['Employe']), '">' . _('Wage level query') . '</a><br />';
	echo '<a href="', $RootPath, '/PrintCustStatements.php?FromCust=', urlencode($_SESSION['Employe']), '&amp;ToCust=', urlencode($_SESSION['Employe']), '&amp;PrintPDF=Yes">' . _('Recruiting staff') . '</a><br />';
	echo '</td><td valign="top" class="select">';
	// employee transactions options:
	echo '<a href="', $RootPath, '/SelectSalesOrder.php?Selectedemployee=', urlencode($_SESSION['Employe']), '">' . _('Employee to official'). '</a><br />';
	echo '<a href="', $RootPath, '/employeeAllocations.php?EmpoyeNo=', urlencode($_SESSION['Employe']), '">' . _('Employee dismissal') . '</a><br />';

	echo '</td><td valign="top" class="select">';
	// employee maintenance options:
	echo '<a href="', $RootPath, '/Recruit.php?New=Yes ">' ._('New employee') . '</a><br />';
	echo '<a href="', $RootPath, '/Recruit.php?EmpoyeNo=', urlencode($_SESSION['Employe']), '&amp;Modify=No ">' . _('Staff maintenance') . '</a><br />';

		echo '<a href="', $RootPath, '/PayScale.php?EmpoyeNo=', urlencode($_SESSION['Employe']), '">' . _('Maintain wage level') . '</a><br />';
	
	
	echo '</td>
			</tr>
		<tbody>
		</table>';
} else {
	echo '<table cellpadding="4" width="90%" class="selection">
		<thead>
			<tr>
				<th style="width:33%">', _('employee Inquiries'), '</th>
				<th style="width:33%">', _('employee Transactions'), '</th>
				<th style="width:33%">', _('employee Maintenance'), '</th>
			</tr>
		</thead>
		<tbody>';
	echo '<tr>
			<td class="select"></td>
			<td class="select"></td>
			<td class="select">';
	if(!isset($_SESSION['SalesmanLogin']) OR $_SESSION['SalesmanLogin'] == '') {
		echo '<a href="', $RootPath, '/Recruit.php?New=Yes">' . _('New employee') . '</a><br />';
	}
	echo '</td>
			</tr>
		<tbody>
		</table>';
}

// Search for employees:
echo '<form action="', htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8'), '" method="post">',
	'<input type="hidden" name="FormID" value="', $_SESSION['FormID'], '" />';
if(mb_strlen($msg) > 1) {
	prnMsg($msg, 'info');
}
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/magnifier.png" title="',// Icon image.
	_('Search'), '" /> ',// Icon title.
	_('Staff query'), '</p>';// Page title.

echo '<table cellpadding="3" class="selection">';
echo '<tr> <td colspan="2"><nobr>' . _('Please enter a query to recruit staff information'). 	':</nobr></td></tr>';
echo '<tr>
		<td ><b>', _('OR'), '</b>',_('Name')._('section'), ':<input type="text" maxlength="8" name="Keywords" title="', _('If there is an entry in this field then employees with the text entered in their name will be returned') , '"  size="8" ',
			( isset($_POST['Keywords']) ? 'value="' . $_POST['Keywords'] . '" ' : '' ), '/></td>';

echo '<td><b>', _('OR'), '</b>', _('Place of birth')._('section'), ':<input maxlength="18" name="placeforigin" pattern="[\w-]*" size="15" type="text" title="', _('If there is an entry in this field then employees with the text entered in their employee code will be returned') , '" ', (isset($_POST['CustCode']) ? 'value="' . $_POST['CustCode'] . '" ' : '' ), '/></td>
	</tr>';

echo '<tr>
		<td><b>', _('OR'), '</b>',_('Contact number'), ':<input maxlength="15" name="Phone" pattern="[0-9\-\s()+]*" size="15" type="tel" ',
			( isset($_POST['Phone']) ? 'value="' . $_POST['Phone'] . '" ' : '' ), '/></td>';

echo '<td><b>', _('OR'), '</b>', _('Current address') ._('section'), ':<input maxlength="15" name="address" size="15" type="text" ',
			(isset($_POST['address']) ? 'value="' . $_POST['address'] . '" ' : '' ), '/></td>
	</tr>';
echo '<tr>
		<td >',_('Date of registration'), ':<input type="text" maxlength="7" name="empdate" title="', _('If there is an entry in this field then employees with the text entered in their name will be returned') , '"  size="7" ',
			( isset($_POST['empdate']) ? 'value="' . $_POST['empdate'] . '" ' : '' ), '/>','-', '<input maxlength="3" name="empday" size="3" type="text"',(isset($_POST['empday']) ? 'value="' . $_POST['empday'] . '" ' : '' ), '/></td>';

echo '<td><b>', _('OR'), '</b>',_('Joined Job date'), ':<input maxlength="7" name="startwork" pattern="[\w-]*" size="7" type="text" title="', _('If there is an entry in this field then employees with the text entered in their employee code will be returned') , '" ', (isset($_POST['startwork']) ? 'value="' . $_POST['startwork'] . '" ' : '' ), '/>'
,'-', '<input maxlength="3" name="startday" size="3" type="text"',(isset($_POST['startday']) ? 'value="' . $_POST['startday'] . '" ' : '' ), '/></td>
	</tr>';


echo '<tr> <td colspan="2"><nobr>' . _('Employee status') ;
  
    echo ' <input type="radio" name="working" value="7"  size="5" maxlength="5"  checked>'._('All').'
           <input type="radio" name="working" value="0"  size="5" maxlength="5" >'._('No').'
           <input type="radio" name="working" value="1"  size="5" maxlength="5" >'._('Yes').'
           <input type="radio" name="working" value="2"  size="5" maxlength="5" >'._('Try Out').'
           <input type="radio" name="working" value="4"  size="5" maxlength="5" >'._('Cencel').'
           <input type="radio" name="working" value="5"  size="5" maxlength="5" >'._('Formal').'
            <input type="radio" name="working" value="6"  size="5" maxlength="5" >'._('Quit');
          

    	echo 	':</nobr></td></tr>';
		
echo '</table><br />';
echo '<div class="centre">
		<input name="Search" type="submit" value="', _('Search Now'), '" />
		<input name="CSV" type="submit" value="', _('CSV Format'), '" />
	</div>';
// End search for employees.

if(isset($result)) {
	unset($_SESSION['Employe']);
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
		echo '<table cellpadding="2" class="selection">
				<thead>
					<tr>
						<th class="ascending">' . _('Name') . '</th>
						<th class="ascending">' . iconv('GB2312', 'UTF-8','�Ǽ�����') . '</th>
						<th class="ascending">' . _('Sex') . '</th>
						<th class="ascending">' . iconv('GB2312', 'UTF-8','����'). '</th>
						<th class="ascending">' . _('Height') . '</th>
						<th class="ascending">' . _('Weight'). '</th>
						<th class="ascending">' . iconv('GB2312', 'UTF-8','����') . '</th>
						<th class="ascending">' . iconv('GB2312', 'UTF-8','����סַ'). '</th>
							<th class="ascending">' . iconv('GB2312', 'UTF-8','רҵ'). '</th>
								<th class="ascending">' . iconv('GB2312', 'UTF-8','�绰'). '</th>
									<th class="ascending">' . iconv('GB2312', 'UTF-8','����'). '</th>
										<th class="ascending">' . iconv('GB2312', 'UTF-8','����'). '</th>
											<th class="ascending">' . iconv('GB2312', 'UTF-8','�μӹ���ʱ��'). '</th>
												<th class="ascending">' . iconv('GB2312', 'UTF-8','����ʱ��'). '</th>
													<th class="ascending">' . iconv('GB2312', 'UTF-8','�籣'). '</th>
														<th class="ascending">' . iconv('GB2312', 'UTF-8','��������'). '</th>
															<th class="ascending">' . iconv('GB2312', 'UTF-8','���͹���'). '</th>
					</tr>
				</thead>';
		$k = 0;// row counter to determine background colour
		$RowIndex = 0;
	}// end if NOT producing a CSV file
	if(DB_num_rows($result) <> 0) {
	
		if(isset($_POST['CSV'])) {// producing a CSV file of employees
			$FileName = $_SESSION['reports_dir'] . '/Employe_Listing_' . date('Y-m-d') . '.csv';
			echo '<br /><p class="page_title_text"><a href="' . $FileName . '">' . _('Click to view the csv Search Result') . '</p>';
			$fp = fopen($FileName, 'w');
			while ($myrow2 = DB_fetch_array($result)) {
				fwrite($fp, $myrow2['EmpoyeNo'] . ',' . str_replace(',', '', $myrow2['name']) . ',' . str_replace(',', '', $myrow2['address1']) . ',' . str_replace(',', '', $myrow2['address2']) . ',' . str_replace(',', '', $myrow2['address3']) . ',' . str_replace(',', '', $myrow2['address4']) . ',' . str_replace(',', '', $myrow2['contactname']) . ',' . str_replace(',', '', $myrow2['typename']) . ',' . $myrow2['phoneno'] . ',' . $myrow2['faxno'] . ',' . $myrow2['email'] . "\n");
			}// end loop through employees returned
		}elseif(!isset($_POST['CSV'])) {
			DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
		}
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
			echo '<td><button type="submit" name="SubmitEmployeSelection[', htmlspecialchars($myrow['empname'], ENT_QUOTES, 'UTF-8', false), ']" value="', htmlspecialchars($myrow['empname'], ENT_QUOTES, 'UTF-8', false), '" >', $myrow['empname'],  '</button></td>
				<td class="text">', $myrow['empdate'], '</td>
				<td class="text">', $myrow['sex'], '</td>
				<td class="text">', $myrow['birthdate'], '</td>
				<td class="text">', $myrow['height'], '</td>
				<td class="text">', $myrow['weight'], '</td>
				<td class="text">', htmlspecialchars($myrow['placeforigin'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', htmlspecialchars($myrow['address'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', htmlspecialchars($myrow['education'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', $myrow['phone'], '</td>
				<td class="text">', $myrow['email'], '</td>
				<td class="text">', $myrow['job'], '</td>
				<td class="text">', $myrow['workdate'], '</td>
				<td class="text">', $myrow['startwork'], '</td>
				<td class="text">', $myrow['social'], '</td>
				<td class="text">', $myrow['hopewages'], '</td>
				<td class="text">', $myrow['minwages'], '</td>
							
			</tr>';
			$i++;
			$RowIndex++;
			// end of page full new headings if
		}// end loop through employees
		
		echo '</tbody>';
		echo '</table>';
		echo '<input type="hidden" name="JustSelectedAEmploye" value="Yes" />';
	}// end if there are employees to show
}// end if results to show

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
if(isset($_SESSION['Employe']) AND $_SESSION['Employe'] != '') {

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
					debtorsmaster.EmpoyeNo,
					debtorsmaster.name,
					custbranch.branchcode,
					custbranch.brname,
					custbranch.lat,
					custbranch.lng,
					custbranch.braddress1,
					custbranch.braddress2,
					custbranch.braddress3,
					custbranch.braddress4
				FROM debtorsmaster
				LEFT JOIN custbranch
					ON debtorsmaster.EmpoyeNo = custbranch.EmpoyeNo
				WHERE debtorsmaster.EmpoyeNo = '" . $_SESSION['Employe'] . "'
					AND custbranch.branchcode = '" . $_SESSION['BranchCode'] . "'
				ORDER BY debtorsmaster.EmpoyeNo";
		$ErrMsg = _('An error occurred in retrieving the information');
		$result2 = DB_query($SQL, $ErrMsg);
		$myrow2 = DB_fetch_array($result2);
		$Lat = $myrow2['lat'];
		$Lng = $myrow2['lng'];

		if($Lat == 0 and $myrow2["braddress1"] != '' and $_SESSION['BranchCode'] != '') {
			$delay = 0;
			$base_url = "https://" . $map_host . "/maps/api/geocode/xml?address=";

			$geocode_pending = true;
			while ($geocode_pending) {
				$address = urlencode($myrow2["braddress1"] . "," . $myrow2["braddress2"] . "," . $myrow2["braddress3"] . "," . $myrow2["braddress4"]);
				$id = $myrow2["branchcode"];
				$EmpoyeNo =$myrow2["EmpoyeNo"];
				$request_url = $base_url . $address . ',&sensor=true';

				$buffer = file_get_contents($request_url)/* or die("url not loading")*/;
				$xml = simplexml_load_string($buffer);
				// echo $xml->asXML();

				$status = $xml->status;
				if(strcmp($status, "OK") == 0) {
					$geocode_pending = false;

					$Lat = $xml->result->geometry->location->lat;
					$Lng = $xml->result->geometry->location->lng;

					$query = sprintf("UPDATE custbranch " .
							" SET lat = '%s', lng = '%s' " .
							" WHERE branchcode = '%s' " .
						" AND EmpoyeNo = '%s' LIMIT 1;",
							($Lat),
							($Lng),
							($id),
							($EmpoyeNo));
					$update_result = DB_query($query);

					if($update_result == 1) {
						prnMsg( _('GeoCode has been updated for employeeID') . ': ' . $id . ' - ' . _('Latitude') . ': ' . $Lat . ' ' . _('Longitude') . ': ' . $Lng ,'info');
					}
				} else {
					$geocode_pending = false;
					prnMsg(_('Unable to update GeoCode for employeeID') . ': ' . $id . ' - ' . _('Received status') . ': ' . $status , 'error');
				}
				usleep($delay);
			}
		}
}

}// end if isset($_SESSION['Employe']) AND $_SESSION['Employe'] != ''
include('includes/footer.php');
?>
