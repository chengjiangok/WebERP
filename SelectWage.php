<?php
/* $Id: SelecteWage.php 0001 2016-05-28 05:44:34Z daintree $*/
/* Selection of employee - from where all employee related maintenance, transactions and inquiries start */

include('includes/session.php');
$Title = _('Wage level');
$ViewTopic = 'HumanResources';
	$BookMark = 'SelectRecruit';
include('includes/header.php');

echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/customer.png" title="',// Icon image.
	$Title, '" /> ',// Icon title.
	$Title, '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if(isset($_GET['Select'])) {
	$_SESSION['EmployeWage'] = $_GET['Select'];
}

if(!isset($_SESSION['EmployeWage'])) {// initialise if not already done
	$_SESSION['EmployeWage'] = '';
}

if(isset($_POST['JustSelectedAEmploye'])) {
	if(isset ($_POST['SubmitEmployeSelection'])) {
	foreach ($_POST['SubmitEmployeSelection'] as $EmployeID => $BranchCode)
		$_SESSION['EmployeWage'] = $EmployeID;
   header('Location:PayScale.php');
 //  ath, '/PayScale.php?DebtorNo=', urlencode($_SESSION['EmployeWage']), '">' . iconv('GB2312', 'UTF-8','ά�����ʼ���') . '</a><br />';
		exit;
	/*} else {
		prnMsg(_('Unable to identify the selected Employe'), 'error');*/
	}
//	prnMsg($_SESSION['Employe1'],'info');
	//exit;
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
			
				$SQL="SELECT employfile.empname,
				            employfile.emplid, 
				              case when employfile.sex=0 then '"._('woman')."' else '".('man')."' end sex,
				              startwork,
				            employfile.social,
                            wagefile.wage,
                            wagefile.department,
                           wagefile.job,
                           wagefile.duties,
                           wagefile.AccDepartment,
                           wagefile.AccType,
                           wagefile.MinGuarantee,
                           wagefile.overtime,
                           wagefile.wagetype,
                           wagefile.MonthlyAttendance,
                           wagefile.workhours,
                           wagefile.subsidy,
                           wagefile.social,
                           wagefile.EntryDate,
                           wagefile.OnTrial,
                           wagefile.quit,
                           wagefile.remark ,
                           wagefile.tag
                           FROM employfile
                            left join  wagefile on employfile.empname=wagefile.empname where  decision = 0 ";
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
		$_SESSION['EmployeWage'] = $myrow['empname'];
		$_SESSION['BranchCode'] = $myrow['emplid'];
		unset($result);
		unset($_POST['Search']);
	} elseif(DB_num_rows($result) == 0) {
		prnMsg(_('No recruiting staff record contains the selected text') . ' - ' . _('Please change your search terms, and then try again'), 'info');
		echo '<br />';
	}
}// end of if search
/*
if(isset($_SESSION['EmployeWage']) AND isset($_POST['Search']) AND !isset($_POST['CSV'])) {


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
		echo '<a href="', $RootPath, '/Recruit.php?New=Yes">' . iconv('GB2312', 'UTF-8','����ƸԱ��') . '</a><br />';
	}
	echo '</td>
			</tr>
		<tbody>
		</table>';
}
*/
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
	unset($_SESSION['EmployeWage']);
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
						<th class="ascending">' . iconv('GB2312', 'UTF-8','��ְ����') . '</th>
						<th class="ascending">' . _('Sex') . '</th>
						<th class="ascending">' . _('Department'). '</th>
						<th class="ascending">' . iconv('GB2312', 'UTF-8','���㲿��') . '</th>
						<th class="ascending">' . iconv('GB2312', 'UTF-8','���㵥Ԫ'). '</th>
						<th class="ascending">' . _('Wage') . '</th>
						<th class="ascending">' . iconv('GB2312', 'UTF-8','���ʺ�������'). '</th>
							<th class="ascending">' . iconv('GB2312', 'UTF-8','����'). '</th>
								<th class="ascending">' . iconv('GB2312', 'UTF-8','��Сʱ'). '</th>
               <th class="ascending">' . iconv('GB2312', 'UTF-8','�Ӱ�'). '</th>
									<th class="ascending">' . iconv('GB2312', 'UTF-8','���ͱ���'). '</th>
										<th class="ascending">' . iconv('GB2312', 'UTF-8','��������'). '</th>
										<th class="ascending">' . iconv('GB2312', 'UTF-8','���籣'). '</th>
										
												<th class="ascending">' ._('Remark'). '</th>
										
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
				fwrite($fp, $myrow2['debtorno'] . ',' . str_replace(',', '', $myrow2['name']) . ',' . str_replace(',', '', $myrow2['address1']) . ',' . str_replace(',', '', $myrow2['address2']) . ',' . str_replace(',', '', $myrow2['address3']) . ',' . str_replace(',', '', $myrow2['address4']) . ',' . str_replace(',', '', $myrow2['contactname']) . ',' . str_replace(',', '', $myrow2['typename']) . ',' . $myrow2['phoneno'] . ',' . $myrow2['faxno'] . ',' . $myrow2['email'] . "\n");
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
				<td class="text">', $myrow['EntryDate'], '</td>
				<td class="text">', $myrow['sex'], '</td>
				<td class="text">', htmlspecialchars($myrow['department'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">',htmlspecialchars( $myrow['AccDepartment'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', htmlspecialchars($myrow['tag'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', $myrow['wage'],'</td>
				<td class="text">', htmlspecialchars($myrow['acctype'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', $myrow['Attendance'], '</td>
				<td class="text">', $myrow['workhours'], '</td>
				<td class="text">', $myrow['ovretime'], '</td>
				<td class="text">', $myrow['MinGuarantee'], '</td>
				<td class="text">', $myrow['subsidy'], '</td>
				<td class="text">', $myrow['social'], '</td>
				<td class="text">', $myrow['remark'], '</td>
			
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

include('includes/footer.php');
?>
