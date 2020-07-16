<?php
/* $Id: WageCompute.php 2016/11/6 15:39:54chengjiang  */



include ('includes/session.php');
$Title = _('Wage Compute');// Screen identification.
$ViewTopic = 'HumanResources';
$BookMark = 'WagecCompute';

include('includes/SQL_CommonFunctions.inc');


if (!isset($_POST['selectperiod'])){
	$_POST['selectperiod']= $_SESSION['period'];
		$periodend= $_SESSION['period'];
}else{

$periodend= $_POST['selectperiod'];
}
	if (!isset($_POST['department'])){
	$_POST['department']= 0;
	
}

	
	if (isset($_POST['unittag'])){
		//PageSet('TG',$_POST['unittag']);		
  		$unittag=$_POST['unittag'];
	}	else {
	 	$_POST['unittag']=50;
	 		$unittag=50;
	}
	if (isset($_POST['hstag'])){
		PageSet('HS',$_POST['hstag']);		
 		$hstag=$_POST['hstag'];
	}	else {
	 	$hstag=0;
	}
	
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
	include  ('includes/header.php');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/printer.png" title="' .// Icon image.
		$Title . '" /> ' .// Icon title.
		$Title . '</p>';// Page title.
  
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
    echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';


	echo '<div class="page_help_text">', _('Select a menu option to operate using this employee'), '.</div>
		<br />
		<table cellpadding="4" width="90%" class="selection">
		<thead>
			<tr>		
				<th style="width:33%">工资操作</th>
				<th style="width:33%">工资查询</th>
				<th style="width:33%">工资维护</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td valign="top" class="select">';
	// employee inquiries options:
	echo '<a href="', $RootPath, '/employeeInquiry.php?EmployeID=', urlencode($_SESSION['Employe']), '">工资查询</a><br />';
	echo '<a href="', $RootPath, '/employeeAccount.php?EmployeID=', urlencode($_SESSION['Employe']), '">工资账户</a><br />';

	echo '</td><td valign="top" class="select">';
	// employee transactions options:
	echo '<a href="', $RootPath, '/SelectSalesOrder.php?Selectedemployee=', urlencode($_SESSION['Employe']), '">工时统计</a><br />';
	echo '<a href="', $RootPath, '/employeeAllocations.php?EmpoyeNo=', urlencode($_SESSION['Employe']), '">统计</a><br />';
	echo '<a href="', $RootPath, '/employeeAllocations.php?EmpoyeNo=', urlencode($_SESSION['Employe']), '">工资统计</a><br />';

	echo '</td><td valign="top" class="select">';
	// employee maintenance options:
	echo '<a href="', $RootPath, '/Recruit.php?New=Yes ">' ._('Wage Sheets Set') . '</a><br />';
	echo '<a href="', $RootPath, '/Recruit.php?EmpoyeNo=', urlencode($_SESSION['Employe']), '&amp;Modify=No ">工资公式设置</a><br />';
	echo '</td>
			</tr>
		<tbody>
		</table>';
	echo '<table class="selection">';
 
	echo '<tr><td>' . _('Select Period To')  . '</td>
			<td><select name="selectperiod">';	
   $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".($periodend - 18) ."' AND  periodno <='".$periodend."' ORDER BY periodno DESC ";
   $result = DB_query($sql);
 /*  while ($myrow=DB_fetch_array($periods,$db)){		
	
		if($myrow['periodno']==$_POST['selectperiod']){	
			echo '<option selected="selected" value="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		
		} else {
			echo '<option value ="' . $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
	}*/
	while ($myrow=DB_fetch_array($result,$db)){
	if( isset($_POST['selectperiod']) AND $myrow['periodno']==$_POST['selectperiod']){
	
		echo '<option selected="selected" value ="';
		}else {
		 		echo '<option  value ="';
		}
  echo  $myrow['periodno'] . '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';

	}
    echo '</select></td>
	       </tr>';

 	
	  $sql="SELECT tagID, tagdatabase,tagdescription,flag FROM unittag  ORDER BY tagID ";
    $result = DB_query($sql);
  //  if (isset($_SESSION['tag'])){
     echo '<tr>
     				<td>',_('Unit tag'),'</td>
  		<td><select name="unittag" size="1" >';
  	
  while ($myrow=DB_fetch_array($result,$db)){
	 	if(isset($_POST['unittag']) AND $myrow['tagID']==$_POST['unittag']){
			echo '<option selected="selected" value="';
		  
		} else {
			
				echo '<option value="';
		}
			
		echo  $myrow['tagID']. '">' .$myrow['tagdescription']  . '</option>';
		
			}
		 echo'</select></td></tr>';
  //}


 
		echo '<tr><td>' . _('Select Account Unit') . ':</td>
		<td><select name="hstag" size="1" >';
    $k=0;
    if (file_exists( $_SESSION['reports_dir'] . '/workcenter.csv')){
		$FileVT =fopen( $_SESSION['reports_dir'] . '/workcenter.csv','r');
		while ($myrow = fgetcsv($FileVT)) { 
			 	if( $myrow[2]== PageGet('HS','0') AND $k==0 ){
			 		
			 		echo '<option  selected="selected"  value="' . $myrow[2] . '">' . iconv('GB2312', 'UTF-8', $myrow[1]) . '</option>';
			 		echo '<option  value="0">' ._('All') . '</option>';
			 		$k=1;
			 	}else{
			 		if ( 0== PageGet('TG','0') AND $k==0 ){
			 			echo '<option selected="selected" value="0">' ._('All') . '</option>';
			 			$k=1;
			 		}
			 			echo '<option  value="' . $myrow[2] . '">' . iconv('GB2312', 'UTF-8', $myrow[1]) . '</option>';
			 			
			 		
			}
		}

	}		
		fclose($FileVT);	

	echo	'</select></td></tr>';
	 echo'<tr>	<td>' ._('Department').':</td>';	
		$SQL = "SELECT departmentid,description FROM departments";
		$result = DB_query($SQL);
		echo '<td><select tabindex="10" name="department">';

   	echo '<option selected="selected" value="0">' ._('All') . '</option>';
		while ($myrow = DB_fetch_array($result)) {
		   echo '<option value="'.$myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
	
		}//end while loop
	
		echo '</select></td></tr>';
		DB_free_result($result);
	
	echo '	</table>
		<br />';

	echo '<div class="centre">
			<input type="submit" name="Search" value="'  ._('Display query') .'" />
				
				<input type="submit" name="wagecompute" value="' . _('Wage Computs') .'" />
					
					<input type="submit" name="wageput" value="' . _('Export Wage Sheets') .'" />
						<input type="submit" name="wageprint" value="' ._('Print Wage Sheets') .'" />
		
		</div>';


unset($result);

	$arr=json_decode($_SESSION['wage'],true);
if(isset($_POST['Search']) OR isset($_POST['CSV']) OR isset($_POST['Go']) OR isset($_POST['Next']) OR isset($_POST['Previous'])) {
	unset($_POST['JustSelectedAEmploye']);
	
//	$arr=json_decode($_SESSION['wage'],true);
	if (isset($arr[$_POST['selectperiod']])){

	if($arr[$_POST['selectperiod']] < 3){
			prnMsg(_('This period of wages has not been generated!'), 'info');
	  exit;
		}
		
} 
	if(isset($_POST['Search'])) {
		$_POST['PageOffset'] = 1;
	}

	if(($_POST['Keywords'] == '') AND ($_POST['placeforgin'] == '') AND ($_POST['Phone'] == '') AND  ($_POST['empdate'] == '') AND  ($_POST['working'] == '7') AND ($_POST['address'] == '') AND ($_POST['startwork'] == '')) {
		// no criteria set then default to all emplayee
			
				$SQL="SELECT * FROM wage ";
	} else {
		$SQL="SELECT `empname`, `empid`, `wage`, `department`, `duties`, `AccDepartment`, `MinGuarantee`, `MonthlyAttendance`, `workhours`, `workday`, `basewage` FROM `wage` ";
		/*	prnMsg($_POST['working'],'info');
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
*/
	}// one of keywords OR custcode OR phone was more than a zero length string


//	$SQL .= " ORDER BY empname";
	$ErrMsg = _('The search for the recruitment of staff to record the request can not be retrieved, because');

	$result = DB_query($SQL, $ErrMsg);
	if(DB_num_rows($result) == 1) {
		$myrow = DB_fetch_array($result);
		$_SESSION['EmployeWage'] = $myrow['empname'];
		$_SESSION['BranchCode'] = $myrow['emplid'];
		unset($result);
		unset($_POST['Search']);
	} elseif(DB_num_rows($result) == 0) {
		prnMsg($_POST['selectperiod']. ' - ' . _('Please change your search terms, and then try again'), 'info');
		echo '<br />';
	}
}elseif(isset($_POST['wagecompute'])){
		if (isset($arr[$_POST['selectperiod']])){
				
			$sql="select COLUMN_NAME from information_schema.COLUMNS where table_name = 'wagefile' and table_schema = '".$_SESSION['DatabaseName']."' ";
			$result = DB_query($sql);
			$fld=array();
	  while ($myrow=DB_fetch_array($result)){
  	 	array_push($fld,$myrow['COLUMN_NAME']);
	  	
	  	}
	  
	  	
	  	}
			$sql="select COLUMN_NAME from information_schema.COLUMNS where table_name = 'wage' and table_schema = '".$_SESSION['DatabaseName']."' ";
			$result = DB_query($sql);
			$strsql='';
			$strinto='';
			
	  while ($myrow=DB_fetch_array($result)){
	  	if ($strsql!=''){
	  		$strsql.=',';
	  }
	  if ($strinto!=''){
	  		$strinto.=',';
	  }
	  	$strsql.=$myrow['COLUMN_NAME'] ;
	    if(in_array($myrow['COLUMN_NAME'],$fld)){
	  	 $strinto.=$myrow['COLUMN_NAME'];
	   }else{
	   	 if('period'==$myrow['COLUMN_NAME']){
	   	 $strinto.=$_POST['selectperiod'].' '. $myrow['COLUMN_NAME'];
	   	
	  }else{
	  	 	 $strinto.='0 '. $myrow['COLUMN_NAME'];
	  	}
	   	}
	  	}	  	
	   	  // code to execute endwhile;	$myrow = DB_fetch_array($result);
	flush();
  $strsql = "INSERT INTO wage ( ".$strsql." )  select ".$strinto;		

	$strsql .= " from wagefile where quit is null";
	$result = DB_query($strsql);
	//prnMsg(iconv('GB2312', 'UTF-8','���ڹ��ʻ�û�����ɣ�'), 'info');
			
	
}elseif (isset($_POST['wageset'])) {
	
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
		$sql="SELECT field,title FROM wageitem where witype>1 or field='empname' or field='empid';";
		$resultset = DB_query($sql);
		echo '<table cellpadding="2" class="selection">
				<thead>
					<tr>';
					while ($myrow = DB_fetch_array($resultset)) {
						echo '<th class="ascending">' , htmlspecialchars($myrow['title'], ENT_QUOTES, 'UTF-8', false), '</th>';
					}
										
				echo'</tr>
				</thead>';
		$k = 0;// row counter to determine background colour
		$RowIndex = 0;
}
}
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
		$sql="SELECT field,title FROM  wageitem where print=0";
		$result = DB_query($sql);
		echo '<table cellpadding="2" class="selection">
				<thead>
					<tr>';
					while ($myrow = DB_fetch_array($result)) {
						echo '<th class="ascending">' , htmlspecialchars($myrow['title'], ENT_QUOTES, 'UTF-8', false), '</th>';
					}
										
				echo'</tr>
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
			/*
		echo '<td><button type="submit" name="SubmitEmployeSelection[', htmlspecialchars($myrow['empname'], ENT_QUOTES, 'UTF-8', false), ']" value="', htmlspecialchars($myrow['empname'], ENT_QUOTES, 'UTF-8', false), '" >', $myrow['empname'],  '</button></td>
				<td class="text">', $myrow['EntryDate'], '</td>
				<td class="text">', $myrow['sex'], '</td>
				<td class="text">', htmlspecialchars($myrow['department'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">',htmlspecialchars( $myrow['AccDepartment'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', htmlspecialchars($myrow['unittag'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', $myrow['wage'],'</td>
				<td class="text">', htmlspecialchars($myrow['acctype'], ENT_QUOTES, 'UTF-8', false), '</td>
				<td class="text">', $myrow['Attendance'], '</td>
				<td class="text">', $myrow['workhours'], '</td>
				<td class="text">', $myrow['ovretime'], '</td>
				<td class="text">', $myrow['MinGuarantee'], '</td>
				<td class="text">', $myrow['subsidy'], '</td>
				<td class="text">', $myrow['social'], '</td>
				<td class="text">', $myrow['remark'], '</td>
			
			</tr>';*/
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



//}
echo '</div>
	</form>';
include('includes/footer.php');
?>
