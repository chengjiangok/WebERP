<?php
/* $Id: PayScale.php 7413 2016-10-08 04:04:13Z exsonqu $*/
/* Defines the wage level employee.*/

include('includes/session.php');
$Title =  _('Wage level');// Screen identification.
	$ViewTopic = 'HumanResources';
$BookMark = 'PayScale';
include('includes/header.php');

if (isset($_GET['EmpoyeNo'])) {
	
		$SelectEmpoye =mb_strtoupper($_GET['EmpoyeNo']);
} else if (isset($_POST['EmpoyeNo'])){

		$SelectEmpoye =mb_strtoupper($_POST['EmpoyeNo']);
}elseif (isset($_SESSION['EmployeWage'])) {
	$SelectEmpoye = mb_strtoupper($_SESSION['EmployeWage']);
}
/*
if (isset($_GET['SelectedBranch'])){
	$SelectEmpoye = mb_strtoupper($_GET['SelectedBranch']);
} else if (isset($_POST['SelectedBranch'])){
	$SelectEmpoye = mb_strtoupper($_POST['SelectedBranch']);
}*/

if (isset($Errors)) {
	unset($Errors);
}
if(!isset($_POST['EntryDate'])){
	$_POST['EntryDate']=date('Y-m-d');
}
if(!isset($_POST['scoial'])){
	$_POST['scoial']=0;
}
if(!isset($_POST['Attendance'])){
	$_POST['Attendance']=26;
}
if(!isset($_POST['workhours'])){
	$_POST['workhours']=8;
}
	
$Errors = array();
$InputError = 0;
if (isset($_POST['submit'])) {
	$i=1;	
	if (!isset($Latitude)) {
		$Latitude=0.0;
		$Longitude=0.0;
	}	
	$SQL= "SELECT COUNT(*) FROM wagefile WHERE empname='".$SelectEmpoye."'";
	$result = DB_query($SQL);
	$myrow = DB_fetch_row($result);	
	if (isset($SelectEmpoye)  AND $myrow[0]>0 AND $InputError !=1) {
		prnMsg('46update','info');
		$SQL = "UPDATE wagefile set
		                    department='".$_POST['department']."',
		                    job='" . $_POST['job']   ."',
		                    duties=	'" . $_POST['duties']   ."',
		                    AccDepartment='" . $_POST['AccDepartment'] ."',
		              
		                    MinGuarantee=	'" . $_POST['MinGuarantee']  ."',
		                    overtime='" . $_POST['overtime']  ."',
		                    wage=	'" . $_POST['wage']  ."',
		                    wagetype=	'" . $_POST['wagetype']  ."',
		                    MonthlyAttendance=	'" . $_POST['Attendance']  ."',
		                    workhours=	'" . $_POST['workhours']  ."',
		                    subsidy=	'" . $_POST['subsidy']  ."',
		                     social=			'" . $_POST['social']  ."',
		                    EntryDate=	'" . $_POST['EntryDate']  ."',
		                    OnTrial='" . $_POST['OnTrial']  ."',
		                    quit=	'" . $_POST['quit']  ."',
		                    remark=	'" . $_POST['remark']  ." ',
		                    tag=	'" . $_POST['tag'] ." ' WHERE empname = '".$SelectEmpoye."''";
      $ErrMsg = _('The employee could not be updated because');
			$result = DB_query($sql,$ErrMsg);
			prnMsg( _('Employee updated'),'success');
			echo '<br />';
		//$msg = $_POST['BrName'] . ' '._('branch has been updated.');
	} elseif ($InputError !=1  AND $myrow[0]==0 AND isset($SelectEmpoye)) {	
		$SQL = "INSERT INTO wagefile (empname,
		                    department,
		                    job,
		                    duties,
		                    AccDepartment,
		                  
		                    MinGuarantee,
		                    overtime,
		                    wage,
		                    wagetype,
		                    MonthlyAttendance,
		                    workhours,
		                    subsidy,
		                     social,
		                    EntryDate,
		                
		                    remark,
		                    tag )
				VALUES ('" . $SelectEmpoye  ."',
			'" . $_POST['department']   ."',
			'" . $_POST['job']   ."',
			'" . $_POST['duties']   ."',
			'" . $_POST['AccDepartment'] ."',		
			'" . ( $_POST['MinGuarantee']==''? 0: $_POST['MinGuarantee'] )  ."',
			'" . $_POST['overtime']  ."',
			'" . $_POST['wage']  ."',
			'" . $_POST['wagetype']  ."',
			'" . $_POST['Attendance']  ."',
			'" . $_POST['workhours']  ."',
			'" . ($_POST['subsidy']=='' ? 0:$_POST['subsidy'])  ."',
			'" . $_POST['social']  ."',
			'" . $_POST['EntryDate']  ."',			
			'" . $_POST['remark']  ." ',
			'" . $_POST['tag'] ." ' )";			
	}
	echo '<br />';
	$msg = _('employee') . '<b> ' .$SelectEmpoye . ': ' . ' </b>' . _('has been added, or return to the') ;
	if (isset($_SESSION['EmployeWage'])){
 //  header('Location:SelectWage.php');
}else{
	header('Location:SelectRecruit.php');
	
	}
	$ErrMsg = _('The employee wage level record could not be inserted or updated because');
	if ($InputError==0) {
		$result = DB_query($SQL, $ErrMsg);
	}

	if (DB_error_no() ==0 AND $InputError==0) {
		prnMsg($msg,'success');
	    unset($_POST['department']  );
			unset($_POST['job'] );
			unset($_POST['duties']  );
			unset($_POST['AccDepartment']);		
			unset($_POST['MinGuarantee']);
			unset( $_POST['overtime'] );
			unset( $_POST['wage'] );
			unset( $_POST['wagetype'] );
			unset( $_POST['Attendance'] );
			unset( $_POST['workhours'] );
			unset( $_POST['subsidy'] );
			unset( $_POST['social'] );
			unset( $_POST['EntryDate'] );
	   	unset( $_POST['remark']  );
      unset( $_POST['tag'] );
      unset($SelectEmpoye);
	}
}
//if (!isset($_GET['delete'])) {
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'">';
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	if (isset($SelectEmpoye)) {
		//editing an existing branch
		$SQL = "SELECT 
		         empid,
		         empname,
		         department,
		         job,
		         duties,
		         AccDepartment,
		     
		         MinGuarantee,
		         overtime,
		         wage,
		         wagetype,
		         MonthlyAttendance,
		         workhours,
		         subsidy,
		         social,
		         EntryDate,
		         OnTrial,
		         quit,
		         remark,
		         tag 
		         FROM wagefile 
		         where empname='".$SelectEmpoye."'";
		$result = DB_query($SQL);
		$myrow = DB_fetch_array($result);
		if ($myrow) {
			$_POST['empid'] = $myrow['empid'];
			$_POST['empname'] = $myrow['empname'];
			$_POST['department'] = $myrow['department'];
			$_POST['job'] = $myrow['job'];
			$_POST['duties'] = $myrow['duties'];
			$_POST['AccDepartment'] = $myrow['AccDepartment'];
		$_POST['wage'] = $myrow['wage'];
			$_POST['MinGuarantee'] =( $myrow['MinGuarantee']=='' ? '0': $myrow['MinGuarantee']);
			$_POST['overtime'] = $myrow['overtime'];
			$_POST['wagetype'] = $myrow['wage'];
			$_POST['Attendance'] = $myrow['Attendance'];
			$_POST['workhours'] = $myrow['workhours'];
			$_POST['subsidy'] = $myrow['subsidy'];
			$_POST['rent'] = $myrow['rent'];
			$_POST['power'] = locale_number_format($myrow['power'],0);
			$_POST['social'] =($myrow['social']=='' ? 0 :$myrow['social']);
			$_POST['EntryDate'] = $myrow['EntryDate'];
			$_POST['OnTrial'] =$myrow['OnTrial'];
			$_POST['quit'] =$myrow['quit'];
			$_POST['remark'] =$myrow['remark'];
			$_POST['tag'] =$myrow['tag'];		
		}

		echo '<input type="hidden" name="SelectedBranch" value="' . $SelectEmpoye . '" />';
		echo '<input type="hidden" name="BranchCode" value="' . $_POST['BranchCode'] . '" />';
		echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' . _('Wage level') . '" alt="" />
				 '. ' '. $SelectEmpoye . ' ' .  _('Wage level'). '</p>';	
	}
	 echo '<table >
	    	<tr><td >
	      <table class="selection" >';		
	 echo '<tr><td width="150"><nobr>'. _('Employee Name').':</nobr></td><td  width="180"><nobr>'. $SelectEmpoye.'</nobr></td></tr>';
   echo '<tr>
	       <td><nobr>' ._('Basic wage').':</nobr></td>
	       <td><nobr><input tabindex="1" type="number" name="wage"  size="22" maxlength="20" value="'. $_POST['wage'].'" </td>
	       </tr>';
    echo '<tr>
			    <td>',_('Wages type'), ':</td>
		     	<td><select  name="wagetype" tabindex="2">';	
	             	$SQL = "SELECT wageid, description FROM wagetype";
		$result = DB_query($SQL);		
		while ($myrow = DB_fetch_array($result)) {		
		echo '<option value="';
		echo $myrow['wageid'] . '">' . $myrow['description'] . '</option>';
		}
	  echo '</select></td></tr>';
    echo '<tr>
	       <td>' ._('Work hours').':</td>
	       <td><input  type="number" tabindex="3" name="workhours" min="0"  max="12" size="22" maxlength="20" value="'. $_POST['workhours'].'" /></td>
	       </tr>';
    echo'<td >' ._('OverTime') .'</td><td>
         <input type="radio" tabindex="4" name="overtime" value="1"  size="5" maxlength="5"  >'._('Yes').' <input type="radio" name="overtime" tabindex="2" value="0"  size="5" maxlength="5" checked>'._('No').':</td>
         </tr>';
     
    echo '<tr>
      
	       <td>' ._('Monthly Attendance').':</td>
	       <td><input tabindex="5" type="number" name="Attendance"  min="0"  max="31"  size="22" maxlength="20" value="'. $_POST['Attendance'].'" /></td>
	       </tr>';
    echo'<td >' . _('Subsidy') .'</td><td>
         <input type="radio" tabindex="6" name="ynsubsidy" value="1"  size="5" maxlength="5"  >'._('Yes').' <input type="radio" name="ynsubsidy" tabindex="2" value="0"  size="5" maxlength="5" checked>'._('No').':</td>
         </tr>';
    echo '<tr>
	       <td>' ._('Amount of subsidySubsidy').':</td>
	       <td><input tabindex="7" type="number" name="subsidy"size="22" maxlength="20" value="'. $_POST['subsidy'].'" /></td>
	       </tr>';
	  echo'<td >' . _('Minimum guarantee') .'</td><td>
         <input type="radio"  tabindex="8" name="ynMinGuarantee" value="1"  size="5" maxlength="5"  >'._('Yes').' <input type="radio" name="ynMinGuarantee" tabindex="2" value="0"  size="5" maxlength="5" checked>'._('No').':</td>
         </tr>';
    echo '<tr>
	       <td>' ._('Amount of Minimum guarantee').':</td>
	       <td><input tabindex="9" type="number" name="MinGuarantee"  size="22" maxlength="20" value="'. $_POST['MinGuarantee'].'" /></td>
	       </tr>';
   echo'<tr>	<td>' ._('Department').':</td>';	
		$SQL = "SELECT departmentid,description fROM departments";
		$result = DB_query($SQL);
		echo '<td><select tabindex="10" name="department">';
		while ($myrow = DB_fetch_array($result)) {
		
		echo '<option value="';
		echo $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
		}//end while loop
		echo '</select></td></tr>';
		DB_free_result($result);
  	echo '<tr>
			<td>' ._('Duty').':</td>
			<td><select tabindex="11" name="duties">';
		$SQL = "SELECT dutyid, dutyname FROM duty";
		$result = DB_query($SQL);		
		while ($myrow = DB_fetch_array($result)) {		
		echo '<option value="';
		echo $myrow['dutyid'] . '">' . $myrow['dutyname'] . '</option>';
		}
	echo '</select></td>
		</tr>';
  echo '<tr>
			<td>' ._('Job').':</td>
			<td><select tabindex="12" name="job">';
				$SQL = "SELECT jobid, jobname FROM job";
		$result = DB_query($SQL);		
		while ($myrow = DB_fetch_array($result)) {		
		echo '<option value="';
		echo $myrow['jobid'] . '">' . $myrow['jobname'] . '</option>';
		}

	echo '</select></td>
		</tr>';

 echo'<tr>	<td>' ._('Account Department').':</td>';	
		$SQL = "SELECT departmentid,description fROM departments";
		$result = DB_query($SQL);
		echo '<td><select tabindex="13" name="AccDepartment">';
		while ($myrow = DB_fetch_array($result)) {
		
		echo '<option value="';
		echo $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
		}//end while loop
		echo '</select></td></tr>';
 echo'<tr>	<td>' ._('Account Unit').':</td>';	
		$SQL = "SELECT departmentid,description fROM departments";
		$result = DB_query($SQL);
		echo '<td><select tabindex="14" name="tag">';
		while ($myrow = DB_fetch_array($result)) {
		
		echo '<option value="';
		echo $myrow['departmentid'] . '">' . $myrow['description'] . '</option>';
		}//end while loop
		echo '</select></td></tr>';

	echo '<tr>
			<td>' ._('Labour insurance').':</td>';
	echo '<td><input tabindex="15" type="number" name="social"  size="22" maxlength="20" value="'. $_POST['social'].'" /></td>
		</tr>';
	echo' <tr>
   <td >' . _('Start Date') . ':</td><td><input type="date"  tabindex="15"  alt=""  name="EntryDate" size="9" maxlength="9"   value="'.$_POST['EntryDate'].'"/></td>
    </tr>';
		echo '<tr>
			<td>' ._('Remark').':</td>';
	echo '<td><input tabindex="15" type="text" name="remark"  size="22" maxlength="20" value="'. $_POST['remark'].'" /></td>
		</tr>';
	echo '</table>	</td ></tr><table ><br />
		<div class="centre">
			<input name="submit" tabindex="16" type="submit" value="', _('Update Wage'), '" />
		</div>
		</div>
		</form>';

//}//end if record deleted no point displaying form to add record

include('includes/footer.php');
?>
