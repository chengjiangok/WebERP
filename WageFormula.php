<?php
/* $Id: WageFormula.php 2016/11/6 19:58:37 chengjiang $*/
/* Wage Formula Set Add Edit  */

include('includes/session.php');
$Title =_( 'Wage Formula Set');

$ViewTopic = 'HumanResources';
$BookMark = 'WageFormula';
include('includes/header.php');

if (isset($_POST['SelectWF'])){
	$SelectWF =$_POST['SelectWF'];
} elseif (isset($_GET['SelectWF'])){
	$SelectWF =$_GET['SelectWF'];
}
if (isset($_GET['delete'])) {
    $sql="SELECT COUNT(*)  FROM wageset WHERE formulaID='" . $SelectWF . "'";
    $result = DB_query($sql);
  	$myrow = DB_fetch_array($result);
	if ($myrow[0]==0 ) {
	 
	 		$sql="DELETE FROM wageformula WHERE formulaID='" . $SelectWF . "'";
			$result = DB_query($sql);
			prnMsg(_('The formula has been deleted'),'succes');
	   unset($SelectWF);
}else{
		prnMsg('Formula has been used, can not be deleted','info');
	
	}
 
}

if (!isset($SelectWF)) {


	echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '
		</p>';

	$sql = "SELECT formulaID, formulaname, formula, description, field, crtdate, flag FROM wageformula ";

	$result = DB_query($sql);
	echo '<table class="selection">
			<tr>
				<th class="ascending">',_('CODE'), '</th>
				<th class="ascending">', _( 'Formula Name'), '</th>
				<th class="ascending">', _('Field'), '</th>
				<th width="200">', _( 'Formula Description'), '</th>			
				<th class="ascending">', _('Date'), '</th>
				<th class="ascending">',_('Status'), '</th>				
				<th colspan="2">&nbsp;</th>
			</tr>';

	while ($myrow = DB_fetch_array($result)) {

		printf('<tr>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>				
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s&amp;SelectWF=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&amp;SelectWF=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this formula?') . '\');">' . _('Delete')  . '</a></td>
				</tr>',
				$myrow['formulaID'],			
				$myrow['formulaname'],
				$myrow['field'],
				$myrow['description'],			
				$myrow['crtdate'],
			  $myrow['flag'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['formulaID'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['formulaID']);
	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectWF)) {
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/maintenance.png" title="',// Icon image.
		$Title, '" /> ',// Icon title.
		$Title, '</p>';// Page title.
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show all Wage Item') . '</a></div>';
}
if(isset($_POST['enterfield'])){
	$_POST['formula'].= $_POST['fieldselect'];
/*	$sql = "SELECT  field, 
	                title 
	                FROM  wageitem";
	                
	$result = DB_query($sql);
	var_dump(ReplSign($_POST['formula'],$result)).'</br>';
	*/
/*	$i=1;
	while($myrow=DB_fetch_array($result)){//��ȡ������
	 			 	 			  		 			  	
	 if ($myrow['title'] ==iconv( "GB2312", "UTF-8" ,'����')){
	echo $myrow['title'];
	 break;
	}				
	//	var_dump( mb_detect_encoding($myrow['title'], 'UTF-8', true));
}*/
//var_dump( mb_detect_encoding(iconv( "GB2312", "UTF-8" ,'����'), 'UTF-8', true));

//var_dump(FormulaCheck($_POST['formula']));
echo $SelectWF;

	
	}
	if (isset($SelectWF)){
echo '<br />
				<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?SelectWF='.$SelectWF.'">';
			}else {
			 	echo '<br />
				<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';	
			}
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
      <input type="hidden" name="field" value="' . $_POST['field'] . '" />
      <input type="hidden" name="formulanme" value="' . $_POST['formulaname'] . '" />
      <input type="hidden" name="formula" value="' . $_POST['formula'] . '" />';

if (isset($SelectWF) AND !isset($_POST['submit']) AND !isset($_POST['enterfield']) ) {
	//editing an existing 

	$sql = "SELECT formulaID,
	               formulaname,
	               description, 
	               field, 
	               crtdate,
	               flag 
	               FROM wageformula 
					WHERE formulaID='" . $SelectWF . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['formula'] = $myrow['description'];
	$_POST['field'] = $myrow['field'];
	$_POST['formulaname'] = $myrow['formulaname'];
  $_POST['formulaID']  = $myrow['formulaID'];

	echo '<input type="hidden" name="SelectWF" value="' . $SelectWF . '" />
	
		<table class="selection">
			<tr>
				<td>' ._('CODE') . ':</td>
				<td>' . $_POST['formulaID'] .'-'.$_POST['formulaname'] . '</td>
			</tr>';

} 


	echo '<table class="selection">';


$SQL = "SELECT  field,
                title  
                FROM wageitem";
$result = DB_query($SQL);

if (!isset($_POST['formulaname'])) {
	$_POST['formulaname'] = '';
}
	echo '<tr>
					<td>' ._('Formula Name'). ':</td>
					<td><input type="text" pattern="^[a-zA-Z0-9_\u4e00-\u9fa5]{2,14}" required="required" title="'._('The Formula Name should be more than 2-14 characters and no illegal characters allowed').'" name="formulaname" size="21" maxlength="20" value="' . $_POST['formulaname'] . '" placeholder="'._('More than 2 legal characters').'" /></td>
				</tr>
				<tr>
					<td>' . _('Field') . ':</td>
					<td><select name="field">';
			//		prnMsg($_POST['field'],'info');
					
	while ($myrow = DB_fetch_array($result)) {
		if (isset($_POST['field']) and $myrow['field']==$_POST['field']) {
			echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
		}
			echo $myrow['field'] . '">' . $myrow['field'] .'-'.$myrow['title'] . '</option>';
} //end while loop


	echo 		'</select>
      	 </td>
    	 </tr>';

	echo'	<tr>
					<td>' ._('Formula') . ':</td>
				  <td ><textarea  name="formula"  tabindex="3" cols="65" rows="8" placeholder="'.iconv( "GB2312", "UTF-8" ,'����д����������') .'"  value="" >'.$_POST['formula'].'</textarea></td>
 			 </tr>';	
  echo '</table><br/>';
	echo '<table class="selection">
				<tr>
						<td>' . _( 'Enter Field') . ':</td>
						<td><select name="fieldselect">';
	
/*	$sql = "SELECT  field, 
	                title 
	                FROM  wageitem where witype>0";*/
	$result = DB_query($SQL);

	while ($myrow = DB_fetch_array($result)) {
	//	if (isset($_POST['fieldselect']) and $myrow['field']==$_POST['fieldselect']) {
		echo '<option value="'. $myrow['title'] . '">' .  $myrow['field'].'-'.$myrow['title'] . '</option>';
 } //end while loop

//DB_free_result($result);
	echo   		'</select>
							</td>
			  		</tr>
				</table>';	
	
 // prnMsg( $_POST['formula'] ,'info');
if (isset($_POST['submit'])) {
	echo $SelectWF;
	//var_dump(FormulaCheck($_POST['formula']));
	$InputError = 0;
	$sql = "SELECT count(*) FROM wageformula 
					WHERE formulaname='" . $_POST['formulaname'] . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);
	if ($myrow[0]>0 and !isset($SelectWF)) {
	 	prnMsg('The formula name  repetition!','info');
	 		$InputError = 1;
	}
	
	if (!FormulaCheck($_POST['formula'] ) OR strlen($_POST['formula'])< 1) {
	 	prnMsg('The formula gammer  error!','info');
	 		$InputError = 1;
	}
	
//prnMsg($InputError,'info');
	if (isset($SelectWF) AND $InputError !=1) {	

		$sql = "UPDATE wageformula 
		        SET formulaname=	'" . $_POST['formulaname'] . "',
		        description='" . $_POST['formula'] . "',
		        field='" . $_POST['field'] . "',		     
		        crtdate='" . date('Y-m-d') . "'		            
						WHERE formulaID = '" . $SelectWF . "'";	
		 $msg = _('The Wage Item record has been updated');
		// $referer = $_SERVER['PHP_SELF']; 
		//prnMsg($sql,'info');
		 unset( $SelectWF );
		
		// header("Location:WageFormula.php"); 
	} elseif ($InputError !=1) {

	
		$sql = "INSERT INTO wageformula(formulaname, description,  field, empname, departmentid, acctype,jobid,dutyid, crtdate, flag) VALUES(		    
					    	'" . $_POST['formulaname'] . "',
						  	'" . $_POST['formula'] . "',
							 	'" . $_POST['field'] . "',
							  '',
					    	'0','0','0',	'0',
							  '" . date('Y-m-d') . "',
								'0'	)";
						
		$msg = _('The new Wage item has been added to the database');
	}
	//run the SQL from either of the above possibilites
// prnMsg($sql,'info');
	if ($InputError !=1){
		$result = DB_query($sql,_('The update/addition of the formula failed because'));
		prnMsg($msg,'success');
		
		unset ($_POST['field']);
		unset ($_POST['formulaname']);
		unset ($_POST['formula']);
		unset ($SelectWF);
	}

}
	echo'<div class="centre">
				<input type="submit" name="submit" value="' . _('Enter Information') . '" />
				<input type="submit" name="enterfield" value="' . _('Enter Field') . '" />
			</div>
		</div>
  </form>';
include('includes/footer.php');
function FormulaCheck($fmlstr){
    $check=false;
	if(strlen($fmlstr)>0){
	 	$fstr=explode(',',$fmlstr);
   	$sign=array('-','+','*','/','sum','avg');
	  $judge=array('>=','<=','>','<','=','and','or');
	 	$sql = "SELECT  field, 
	                title 
	                FROM  wageitem";
	                
	   $result = DB_query($sql);
  
	 	if (count($fstr)==1){//ֻ��һ����
	 		$check=ReplSign($fstr[0], $result);
	 	
	 	}elseif (count($fstr)>=2 ){//����  ����
	 		 	// 	�����б������� > < =
	 		 		foreach($judge as $value){ 
	 	      if (strpos($fstr[0],$value,0)>0){
	 	      $check=true;
	 	      break;
	 	      }
	 	     }
	 	     if ($check){
	     	$check=ReplSign($fstr[0], $result);
	     	if ($check) {
	     	 	$check=ReplSign($fstr[1], $result);
	     	} else {
	     	 	$check=false;
	     	}
	     	if (count($fstr)==2 and $check ){
	     	
	     		 	$check=ReplSign($fstr[2], $result);
	     	}
	 	   
	
	 } 	
  	} 
	
	}else {	 	
	 //more then 1 charactore   
   return false;
	}
	
   return $check;
   
	}

function ReplSign($Restr,$resultRS){
		  	
		  	$sign=array('-','+','*','/','sum','avg');
	  	 $judge=array('>=','<=','>','<','=','and','or');
	 	  $checkRS=false;
			foreach($sign as $value){ 
	 			
	 			str_ireplace($value,',',$Restr);			
	 			
	 			}
	 			foreach($judge as $value){ 
	 			
	 			str_ireplace($value,',',$Restr);			
	 			
	 			}
	 			 	$fieldstr=explode(',',$Restr);
	 		foreach($fieldstr as $value){ 
	 			$checkRS=false;
	 			while($myrow=DB_fetch_array($resultRS)){//��ȡ������
	 			 if ($myrow['field']==$value OR strcmp(trim($myrow['title']),iconv( "GB2312", "UTF-8" ,$value)==0)) {//�����ֶ�
	 			  	$checkRS=true;	 			  	
	 			 }	 			
	 	
	 			if ($checkRS){
	 				break;
	 				}
	 	    }			
	 		}

    return  $checkRS;
		
		}
function IncomeTax($income){
	        $tax=0;	  
	        $income=$income-3500;    
	       $level=array(0=>80000,
	         1=> 55000,
	         2 => 35000,
	         3 => 9000,
	         4 => 4500,
	         5 => 1500,
	         6=>0,
	         );
	           $taxrate=array( 0 => 0.45,
	         1 => 0.35,
	         2 => 0.30,
	         3 =>0.25 ,
	         4 =>0.2,
	         5=>0.1,
	         6=>0.03);
	  	     $quick=array( 0 => 13505,
	         1 => 5505,
	         2 => 2755,
	         3 =>1005 ,
	         4 =>555,
	         5=>105,
	         6=>0);
	        
	    
      $num = count($level); 
      for($i=0; $i < $num;++$i){ 
       if ( $income > $level[$i] ){
       	$tax=$income*$taxrate[$i]-$quick[$i];
       	break;
       	}
     }  
	  
	
	 return $tax;
	}
?>
