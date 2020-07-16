<?php

/* $Id:Recruit.php 6942 2016-10-1 chengjiang $ */

include('includes/session.php');

$ViewTopic = 'HumanResources';
$BookMark = 'Recruit';
 $Title ='招聘员工';// _('Recruit Employee');

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text">
		<img src="'.$RootPath.'/css/'.$Theme.'/images/customer.png" title="' .  _('Recruit Employee') .
	'" alt="" />' . ' ' . _('Recruit Employee') . '	</p>';

if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();



if(isset($_GET['New'])) {
	$_SESSION['NewEdit']=1;
	unset($_SESSION['Employe']);
}elseif(isset($_GET['Modify'])) {
	$_SESSION['NewEdit']=2;
	}

if (isset($_POST['EmpoyeNo'])){
	$EmpoyeNo = $_POST['EmpoyeNo'];
} elseif (isset($_GET['EmpoyeNo'])){
	$EmpoyeNo = $_GET['EmpoyeNo'];
}
if(!isset($_POST['nationality'])){
	$_POST['nationality']= '';
}
if(!isset($_POST['marriage'])){
	$_POST['marriage']='';
}
if(!isset($_POST['empdate'])){
	$_POST['empdate']=date('Y-m-d');
}
if(!isset($_POST['startwork'])){
	$_POST['startwork']=date('Y-m-d');
}
if(!isset($_POST['workdate'])){
	$_POST['workdate']=date('Y-m-d');
}

if (isset($_POST['submit'])) {

	$InputError = 0;
	$i=1;
 
if ($InputError !=1 AND !isset($_POST['Modify']) AND $_SESSION['NewEdit']==1){
     // $dt= date('Y-m-d',strtotime(substr($_POST['cardid'],6,4).'-'.substr($_POST['cardid'],10,2).'-'.substr($_POST['cardid'],12,2)));
  
			$empdate=FormatDateForSQL($_POST['empdate']);

      $_POST['birthdate']=date('Y-m-d',strtotime(substr($_POST['cardid'],6,4).'-'.substr($_POST['cardid'],10,2).'-'.substr($_POST['cardid'],12,2)));
			$sql = "INSERT INTO employfile (
							empname,
							empdate,
							sex,
							nationality,
							marriage,
							weight,
							height,
							birthdate,
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
							mandecision) 
				VALUES ('" . $_POST['empname'] ."',
						'" . $empdate ."',
						'" . $_POST['sex'] ."',
						'" . $_POST['nationality'] ."',
						'" . $_POST['marriage'] . "',
						'" . $_POST['weight'] . "',
						'" . $_POST['height'] . "',
						'" . $_POST['birthdate'] . "',
						'" . $_POST['cardid'] . "',
						'" . $_POST['placeoforigin'] . "',
						'" . $_POST['address'] . "',
						'" . $_POST['accountlocation'] . "',
						'" . $_POST['graduate'] . "',
						'" . $_POST['education']. "',
						'" . $_POST['phone'] . "',
						'" . $_POST['email'] . "',
						'" . $_POST['workdate'] . "',
						'" . $_POST['job'] . "',
						'" . $_POST['working'] . "',
						'" . $_POST['startwork'] . "',
						'" . $_POST['social'] . "',
						'" . ($_POST['hopewages']=='' ? 0: $_POST['hopewages'] ). "',
						'" . ($_POST['minwages']==''? 0: $_POST['minwages'] ). "',
						'" . $_POST['gzjl'] . "',
						'" . $_POST['myjl']. "',
						'','','',0,0,0,0 )";
 
			$ErrMsg = _('Data entry success');
			$result = DB_query($sql,$ErrMsg);

	
	if (DB_error_no() ==0 ) {
		  unset($_SESSION['NewEdit']);
			echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath .'/PayScale.php?EmpoyeNo=' . $_POST['empname'] . '">';

			echo '<div class="centre">' . _('You should automatically be forwarded to the entry of a employee  page') .
			'. ' . _('If this does not happen') .' (' . _('if the browser does not support META Refresh') . ') ' .
			'<a href="' . $RootPath . '/PayScale.php?EmpoyeNo=' . $_POST['empname']  . '"></a></div>';

			include('includes/footer.php');
			exit;
		}
	 else {
		prnMsg( _('Validation failed') . '. ' . _('No updates or deletes took place'),'error');
	}
}
}elseif (isset($_POST['delete'])) {




		$SQL="DELETE FROM employfile WHERE  empname='" . $_SESSION['Employe'] . "'";
		$result = DB_query($SQL,$ErrMsg);
//prnMsg($SQL,'info');
		$sql="DELETE FROM wagefile WHERE empname='" . $_SESSION['Employe'] . "'";
		$result = DB_query($sql,$ErrMsg);

		if (DB_error_no() ==0){
		prnMsg( _('employee') . ' ' . $_POST['EmpoyeNo'] . ' ' . _('has been deleted - together with all the associated branches and contacts'),'success');
	//	include('includes/footer.php');
	  	unset($_SESSION['Employe']);
			header('Location:SelectRecruit.php');
		exit;
}

}
if(isset($_POST['Reset']) OR isset($_POST['delete']) OR isset($_POST['submit'])){
	
            unset( $_POST['empname']);
						
						unset( $_POST['sex']);
						unset( $_POST['nationality']);
						unset( $_POST['marriage'] );
						unset( $_POST['weight'] );
						unset( $_POST['height'] );
						unset( $_POST['birthdate'] );
						unset( $_POST['cardid'] );
						unset( $_POST['placeoforigin'] );
						unset( $_POST['address'] );
						unset( $_POST['accountlocation'] );
						unset( $_POST['graduate'] );
						unset( $_POST['education']);
						unset( $_POST['phone'] );
						unset( $_POST['email'] );
						unset( $_POST['workdate'] );
						unset( $_POST['job'] );
						unset( $_POST['working'] );
						unset( $_POST['startwork'] );
						unset( $_POST['social'] );
						unset( $_POST['hopewages'] );
						unset( $_POST['minwages'] );
						unset( $_POST['gzjl'] );
						unset( $_POST['myjl']);
						unset($_SESSION['Employe']);
}
	
	//prnMsg(	$_SESSION['NewEdit'].'--'.$_GET['New'],'info');

if (isset($_GET['Modify'])) {
		//修改删除
	
		$sql = "SELECT empname,
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
				             FROM employfile 	WHERE empname = '" . $EmpoyeNo . "'";

		$ErrMsg = _('The employee details could not be retrieved because');
		$result = DB_query($sql,$ErrMsg);

		$myrow = DB_fetch_array($result);
		
		
		$_POST['empname'] = $myrow['empname'];
		$_POST['empdate']  = $myrow['empdate'];
		$_POST['sex']  = $myrow['sex'];
		$_POST['nationality']  = $myrow['nationality'];
		$_POST['marriage']  = $myrow['marriage'];
		$_POST['weight']  = $myrow['weight'];
		$_POST['height']  = $myrow['height'];
		$_POST['cardid'] = $myrow['cardid'];
		$_POST['placeoforigin']  = $myrow['placeforigin'];
		$_POST['address'] =$myrow['address'];
		$_POST['accountlocation']  = $myrow['accountlocation'];
		$_POST['graduateinstitutions']  = $myrow['graduateinstitutions'];
		$_POST['education']  =$myrow['education'] ;
		$_POST['phone']  = $myrow['tel'];
		$_POST['email']  = $myrow['email'];
		$_POST['job']	= $myrow['job'];
		$_POST['workdate'] = $myrow['workdate'];
		$_POST['social'] = $myrow['social'];
		$_POST['startwork'] = $myrow['startwork'];
		$_POST['working'] = $myrow['working'];
		$_POST['experience'] = $myrow['experience'];
			$_POST['hopewages'] = $myrow['hopewages'];
				$_POST['minwages'] = $myrow['minwages'];
					$_POST['decision'] = $myrow['decision'];
        	$_POST['myevaluate'] = $myrow['myevaluate'];
	

	}

if (isset($_SESSION['NewEdit']) OR isset($_GET['New']) or isset( $_GET['Modify'])) {
	 if($_SESSION['NewEdit']==2){
     echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Modify=Yes">';
    }else{	 	
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?New=Yes">';
  }
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="New" value="Yes" />';

	$DataError =0;

	echo '<div>';
	 if( mb_strlen($_POST['empname']) > 1 AND $_SESSION['NewEdit']!=2) {
  	
  	$SQL= "SELECT COUNT(*) FROM employfile WHERE empname like '".$_POST['empname']."'";
 // 	prnMsg($SQL,'info');
  	$result = DB_query($SQL);
	 $myrow = DB_fetch_row($result);
	if ($myrow[0] > 0) {
		$InputError = 1;
		prnMsg( _('The employee name already exists in the database'),'error');
		$Errors[$i] = 'empname';
		$i++;
	}
	if($_POST['cardid'!='']){
	if (isCreditNo($_POST['cardid'])) {
		$InputError = 1;
			prnMsg(  _('You enter card identity  have error!') ,'error');
		$Errors[$i] = 'cradid';
		$i++;
	}}
	if ($InputError!=1){
		$EmpoyeNo = $_POST['empname'];	
	}
}

	if (mb_strlen($_POST['address']) > 40) {
		$InputError = 1;
		prnMsg( _('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'address';
		$i++;

	} elseif (!Is_Date($_POST['startwork'])) {
		$InputError = 1;
		prnMsg( _('The employee since field must be a date in the format') . ' ' . $_SESSION['DefaultDateFormat'],'error');
		$Errors[$i] = 'startwork';
		$i++;
	
	} 
// 	prnMsg($SQL,'info');

	echo'<table >	<tr><td >
	<table class="selection" width="700" border="0" cellspacing="3" cellpadding="3">
  <tr>';
  if ($_SESSION['NewEdit']==2){
  echo '  <td colspan="2"><nobr>' . _('Name') . ':&nbsp&nbsp'.$_POST['empname'].'</nobr></td>';
}else{
	  echo '  <td colspan="2"><nobr>' . _('Name') . ':&nbsp&nbsp<input type="text"  tabindex="1"maxlength="7" name="empname"   required="required" autofocus="autofocus"title ="'._('Recruit') . ' \' &quot; + . &amp; \\ &gt; &lt;" placeholder="'._('不超过5个汉字') .'" size="15" maxlength="15" value="'.$_POST['empname'].'" /></nobr></td>';

	}
  
  echo '  <td colspan="2"><nobr>性别' ;
    if ( isset($_POST['sex']) AND  $_POST['sex']==0 ){
    echo ' <input type="radio" name="sex" value="1"  size="5" maxlength="5"  >男 <input type="radio" name="sex" tabindex="2" value="0"  size="5" maxlength="5" checked>女:</nobr></td>';
    } else
    {
    	   echo ' <input type="radio" name="Sex" value="1"  size="5" maxlength="5"  checked>男 <input type="radio" name="sex" tabindex="2" value="0"  size="5" maxlength="5" >女:</nobr></td>';

    	
    	}
  
  echo ' <td  colspan="2"><nobr>' ._('The cluster') . '&nbsp&nbsp&nbsp&nbsp<input tabindex="2" type="text" name="nationality" size="7" maxlength="8" value="'.$_POST['nationality'].'" /></nobr></td> 
    <td width="180" rowspan="8"><input type="image" src="logo_server.jpg" name="employee" width="175" height="220"/></td>
  </tr>
  <tr>
   	<td colspan="2"><nobr>' . _('Get Married') . ':&nbsp&nbsp<input  type="text" name="marriage" tabindex="4" size="7" maxlength="8"  value="'.$_POST['marriage'].'"/></nobr></td> 
   	<td  colspan="2"><nobr>' ._('Weight') . '(KG):	<input  type="number" min="35" max="150" name="weight" tabindex="5" size="10" maxlength="10"  value="'.$_POST['weight'].'"/></nobr></td>   
		<td colspan="2"><nobr>' ._('Height') . '(CM)<input type="number" name="height" min="100" max="210" tabindex="6" size="7" maxlength="8"  value="'.$_POST['height'].'"/></nobr></td> 
  </tr>
  <tr>
     <td colspan="3">	<nobr>' ._('ID number'). ':<input  type="text" name="cardid"  tabindex="7" size="20" maxlength="20"  value="'.$_POST['cardid'].'"/></nobr></td>
	 	<td colspan="3"><nobr>' . _('Place of birth') . ':<input type="text" data-type="no-illegal-chars" tabindex="8"  name="placeoforigin" title ="" size="20" maxlength="20"  value="'.$_POST['placeforigin'].'"/></nobr></td>
  </tr>
  <tr>
  <td  colspan="3" ><nobr>' . _('Current address') . ':<input tabindex="9" type="text" name="address"  size="30" maxlength="30"  value="'.$_POST['address'].'"/></nobr></td>
			<td  colspan="3" ><nobr>' . _('Registered residence') . ':<input tabindex="10" type="text" name="accountlocation" size="20" maxlength="20"  value="'.$_POST['accountlocation'].'"/></nobr></td>
  </tr>
  <tr>
   	<td   colspan="3"><nobr>' . _('Graduate institutions') . ':<input tabindex="11" type="text" name="graduate" size="30" maxlength="30"  value="'.$_POST['graduate'].'"/></nobr></td>
	
			<td  colspan="3"><nobr>' ._('Education and professional') . ':<input tabindex="12" type="text" name="education" size="20" maxlength="20"   value="'.$_POST['education'].'"/></nobr></td>
  </tr>
  <tr>
  <td   colspan="3"><nobr>' . _('Contact number') . ':<input tabindex="13" type="tel" name="phone"  pattern="[0-9+()\s-]*"  size="20" maxlength="20"   value="'.$_POST['phone'].'"/></nobr></td>
	
			<td  colspan="3"><nobr>' ._('Email') . ':&nbsp<input tabindex="14" type="email" name="email" size="20" maxlength="20"  value="'.$_POST['email'].'" /></nobr></td>
  </tr>
  <tr>
   <td  colspan="3" ><nobr>' . _('Take work date') . ':<input type="date"  tabindex="15" alt="" name="workdate" size="9" maxlength="9"   value="'.$_POST['workdate'].'"/></nobr></td>
	
			<td  colspan="3"><nobr>' ._('Job') . ':
			<select tabindex="12" name="job">';
				$SQL = "SELECT jobid, jobname FROM job";
		$result = DB_query($SQL);
		
		while ($myrow = DB_fetch_array($result)) {
		
		if (isset($_POST['job']) AND $myrow['jobid']==$_POST['job']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
		echo $myrow['jobid'] . '">' . $myrow['jobname'] . '</option>';
		}

	echo '</select></nobr></td>
  </tr>
 
  <tr>
   
  	<td  colspan="3" ><nobr>' . _('Join social insurance') . ':';
  	 if ( isset($_POST['social']) AND  $_POST['social']==1 ){
    echo ' 	<input type="radio" name="social" value="1"  size="5" maxlength="5"  checked>'._('Yes').' <input type="radio" name="social" value="0"  size="5" maxlength="5" >'._('No').':</nobr></td>';
  }else{
  	echo ' 	<input type="radio" name="social" value="1"  size="5" maxlength="5"  >'._('Yes').' <input type="radio" name="social"  value="0"  size="5" maxlength="5" checked>'._('No').':</nobr></td>';
  	
  	}
    
    
	echo'	<td  colspan="3"><nobr>' ._('Expected Salary') . ':<input tabindex="17" type="number" min="1500" max="30000" name="hopewages" size="7" maxlength="7"   value="'.$_POST['hopewages'].'"/>'
		._('Acceptable wage') . ':<input tabindex="18" type="number" name="minwages"  min="1500" max="30000" size="7" maxlength="7"   value="'.$_POST['minwages'].'"/></nobr></td>
					
  </tr>
   <tr>
  	<td  colspan="2" ><nobr>' . _('Whether on the job') . ':';
  	 if ( isset($_POST['working']) AND  $_POST['working']==1 ){
    echo '<input type="radio" name="working" value="1"  size="5" maxlength="5"  checked>'._('Yes').' <input type="radio" name="working" value="0"  size="5" maxlength="5" >'._('No').':</nobr></td>';
	  }else{
	  	  echo '<input type="radio" name="working" value="1"  size="5" maxlength="5" >'._('Yes').' <input type="radio" name="working" value="0"  size="5" maxlength="5"  checked>'._('No').':</nobr></td>';

	  }
	echo '<td  colspan="2"><nobr>' ._('Start work date') . ':<input type="date"  tabindex="19"  alt="" name="startwork" maxlength="9" size="9" value="' .$_POST['startwork'] . '" /></nobr></td>
 	<td colspan="2" ><nobr>' ._('Date of Filling in') . ':<input type="date" required="required" tabindex="20"  alt="" name="empdate"  maxlength="9" size="9" value="' .$_POST['empdate'] . '" /></nobr></td>
 	<td ><nobr></nobr></td>
  </tr> 
  <tr>
  	<td  colspan="2" ><nobr></nobr></td>
	
			<td  colspan="2" ><nobr></nobr></td>
				<td  colspan="3" ><nobr>';
 	
	

	echo '<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
	    <input name="userfile" type="file" />
			<input type="submit" value="' . _('Send File') . '" />';
	

	
 	
 	echo '</nobr></td>
  </tr>
   
  <tr>
    <td width="41" height="140">' . _('工<br> 作<br> 经<br> 历<br> ') . '</td>
    <td colspan="6"><textarea  name="gzjl" tabindex="21" cols="85" rows="8" placeholder="'._('Please fill in the work time, the unit name, the Department and the position, the reason of leaving the job, the proof person') .'"   value=""  wrap="hard"/>'.$_POST['gzjl'].'</textarea></td>
  </tr>
  
  <tr>
    <td height="120">' . _('自<br>我<br>评<br>价<br> ') . '</td>
   <td colspan="6"><textarea  name="myjl"  tabindex="22" cols="85" rows="8" placeholder="'._('Please fill in the work and business experience, technical expertise, interest and hobbies') .'"  value="" >'.$_POST['myjl'].'</textarea></td>
  </tr>';
 
echo '</table></td> </tr></table>';
	
	

echo '</table>
      </td></tr></table>';
}
/*
if (isset($_GET['delete'])) { //User hit delete link on employee contacts

		prnMsg('delete-774','info');
		$resultupcc = DB_query("DELETE FROM employfile
								WHERE empname='".$EmpoyeNo."'");
		prnMsg(_('Contact Deleted'),'success');
}	
		*/
		
if (isset($_GET['New']) OR $_SESSION['NewEdit']==1) {
		echo '<div class="centre">
				<input type="submit" name="submit" value="' . _('Add New employee') . '" />&nbsp;
				<input type="submit" name="Reset" value="' . _('Reset') . '" />
			</div>';
	} elseif ((isset($_GET['Modify'])OR $_SESSION['NewEdit']==2) and $_POST['decision']==0){
		echo '<br />
			<div class="centre">
				<input type="submit" name="submit" value="' . _('Update employee') . '" />&nbsp;
				<input type="submit" name="delete" value="' . _('Delete employee') . '" onclick="return confirm(\'' . _('Are You Sure?') . '\');" />
            </div>';
	}

	echo '</div>
          </form>';
// } else{
 	//prnMsg('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'],'info');
 	//prnMsg( 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'],'info');
//}


include('includes/footer.php');
function isCreditNo($vStr){
   $vCity = array(
        '11','12','13','14','15','21','22',
        '23','31','32','33','34','35','36',
        '37','41','42','43','44','45','46',
        '50','51','52','53','54','61','62',
        '63','64','65','71','81','82','91'
    );
 
    if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return true;
 
    if (!in_array(substr($vStr, 0, 2), $vCity)) return true;
 
    $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
    $vLength = strlen($vStr);
 
    if ($vLength == 18)
    {
        $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
    } else {
        $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
    }
 
    if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return true;
    if ($vLength == 18)
    {
        $vSum = 0;
 
        for ($i = 17 ; $i >= 0 ; $i--)
        {
            $vSubStr = substr($vStr, 17 - $i, 1);
            $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr , 11));
        }
 
        if($vSum % 11 != 1) return true;
    }
 
    return false;
    }
 
?>
