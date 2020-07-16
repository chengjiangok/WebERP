<?php
/* $Id: WageItem.php2016/11/8 5:50:49 chengjiang$*/
/* */

include('includes/session.php');
$Title =_('Wage Item Set');

$ViewTopic = 'HumanResources';
$BookMark = 'WageItem';
include('includes/header.php');

if (isset($_POST['SelectedID'])){
	$SelectedID =$_POST['SelectedID'];
} elseif (isset($_GET['SelectedID'])){
	$SelectedID =$_GET['SelectedID'];
}

if (!isset($SelectedID)) {

	echo '<p class="page_title_text">
					<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '
				</p>';

	$sql = "SELECT wfID, field, title, list, formulaID,witype, datatype,print, crtdate, flag FROM  wageitem";

	$result = DB_query($sql);
	echo '<table class="selection">
				<tr>
					<th class="ascending">',_('Code'), '</th>
					<th class="ascending">',_( 'Item Title'), '</th>
					<th class="ascending">', _('Field'), '</th>
					<th class="ascending">', _( 'Sequence'), '</th>
					<th class="ascending">', _('Item Type'), '</th>
					<th class="ascending">', _('DataType'), '</th>
					<th class="ascending">', _('Print Flag'), '</th>
					<th class="ascending">', _('Updte Date'), '</th>
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
					<td>%s</td>
					<td>%s</td>
					<td><a href="%s&amp;SelectedID=%s">' . _('Edit') . '</a></td>
					<td><a href="%s&amp;SelectedID=%s&amp;delete=yes" onclick="return confirm(\'' . _('Are you sure you wish to delete this work centre?') . '\');">' . _('Delete')  . '</a></td>
				</tr>',
				$myrow['wfID'],			
				$myrow['title'],
				$myrow['field'],
				$myrow['list'],
				$myrow['witype'],
				$myrow['datatype'],
				$myrow['print'],
				$myrow['crtdate'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['field'],
				htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
				$myrow['field']);
	}

	//END WHILE LIST LOOP
	echo '</table>';
}

//end of ifs and buts!

if (isset($SelectedID)) {
	echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
		'/images/maintenance.png" title="',// Icon image.
		$Title, '" /> ',// Icon title.
		$Title, '</p>';// Page title.
	echo '<div class="centre"><a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">' . _('Show all Wage Item') . '</a></div>';
}

echo '<br />
			<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedID) and !isset($_POST['submit'])) {
	//editing an existing work centre

	$sql = "SELECT wfID,
	               field,
	               title,
	               list, 
	               formulaID, 
	               witype,
	               datatype, 
	               crtdate, 
	               flag 
	               FROM  wageitem	WHERE field='" . $SelectedID . "'";

	$result = DB_query($sql);
	$myrow = DB_fetch_array($result);

	$_POST['Code'] = $myrow['field'];
	$_POST['field'] = $myrow['field'];
	$_POST['Description'] = $myrow['title'];
	$_POST['witype']  = $myrow['witype'];
	$_POST['datatype_']  =explode('(', $myrow['datatype'])[0];
	$_POST['datatype']  =str_replace(')','',explode('(', $myrow['datatype'])[1]);
	$_POST['list']  = $myrow['list'];
	echo '<input type="hidden" name="SelectedID" value="' . $SelectedID . '" />
    		<input type="hidden" name="Code" value="' . $_POST['Code'] . '" />';
	echo'<table class="selection">
				<tr>
					<td>' ._('Code') . ':</td>
					<td>' . $_POST['Code'] .'</td>
				</tr>';
} else { //end of if $SelectedID 
	if (!isset($_POST['Code'])) {
		$_POST['Code'] = '';
	}
	echo '<table class="selection">';
	
}

$SQL = "select COLUMN_TYPE,   COLUMN_NAME from information_schema.COLUMNS where table_name = 'wagefile' and table_schema = '".$_SESSION['DatabaseName']."'";
$result = DB_query($SQL);
$fldtype=array();
if (!isset($_POST['Description'])) {
	$_POST['Description'] = '';
}
	echo '<tr>
					<td>' . _('Item Title'). ':</td>
					<td><input type="text" pattern="[^&+-]{2,}" required="required" title="'._('The Work Center should be more than 2 characters and no illegal characters allowed').'" name="Description" ' . (isset($SelectedID)? 'autofocus="autofocus"': '') . ' size="21" maxlength="20" value="' . $_POST['Description'] . '" placeholder="'._('More than 2 legal characters').'" /></td>
				</tr>
				<tr>
					<td>' . _('Field') . ':</td>
					<td><input  type="text" list="field"  name="field" value="'.$_POST['field'].'">
     	  <datalist id="field">';
 	       while ($myrow = DB_fetch_array($result)) {
			   	echo '<option value="'. $myrow['COLUMN_NAME']  . '">' .$myrow['COLUMN_TYPE'] . '</option>';
	 		  	$fldtype[ $myrow['COLUMN_NAME']]=$myrow['COLUMN_TYPE'];
		      } //end while loop
 	 echo'</datalist> ';
		    DB_free_result($result);
 	 echo '</td>	
 				 </tr>';
		echo '<tr>
						<td>' . _('Data Type') . ':</td>
						<td><select name="datatype_">';
			echo '<option selected="selected" value="varchar" >' ._('Character Type') . '</option>
						<option value="double">' . _('Number Type') . '</option>
						<option value="smallint">' . _('INT Type') . '</option>
						<option value="tinyint">' . _('Logic Type') . '</option>
    	    </select>';
			echo '<input type="number" class="number" name="datatype" size="6" title="'._('The input must be numeric').'" maxlength="6" value="'.$_POST['datatype'] .'" />
						</td>
					</tr>';	
	echo'	<tr>
					<td>' ._('Item Type') . ':</td>
					<td><select name="witype">';
  		echo '<option selected="selected" value="0" >' . _( 'Employ Type') . '</option>
						<option value="1">' . _( 'Data Type') . '</option>
						<option value="2">' . _( 'Compute Type') . '</option>
  				 	<option value="3">' . _( 'Subtotal Type') . '</option>
 					  <option value="4">' . _('Total Type') . '</option>
           </select>';

if (!isset($_POST['datatype'])) {
	$_POST['datatype']=0;
}

echo '</select></td></tr>';
echo '<tr>
		<td>' . ('Sequence') . ':</td>
		<td><input type="number" class="number" name="list" size="6" title="'._('The input must be numeric').'" maxlength="6" value="'.$_POST['list'].'" />

  </td>
	</tr>';
	echo '<tr>
		<td>' . _('Print Flag').  ':</td>
		<td><select name="print">';
  	echo '<option selected="selected" value="0" >' . _('Yes') . '</option>
		      <option value="1">' . _('No') . '</option>
	
           </select>';

 echo' </td>
	</tr>';
	
if (isset($_POST['submit'])) {


	$InputError = 0;

	//first off validate inputs sensible

	if (mb_strlen($_POST['Description'])>10) {
		$InputError = 1;
		prnMsg(_('The Wage Item description must be at 2-10 characters long'),'error');
	}
	if (isset($SelectedID) AND $InputError !=1) {

	

		$sql = "UPDATE wageitem SET field = '" . $_POST['field'] . "',
						title = '" . $_POST['Description'] . "',
						witype ='" . $_POST['witype'] . "',
						crtdate ='" . date('Y-m-d') . "',
						list ='" . $_POST['list'] . "',
						print =" . $_POST['print'] . ",";
						if ($_POST['datatype_']=='double'){
						$sql.= "datatype = '" .$_POST['datatype_'] ."(". ($_POST['datatype']>4 ? $_POST['datatype'].',2':'5,2').")'";
					  }else{
					 $sql.= "datatype = '" .$_POST['datatype_'] ."(". ($_POST['datatype']>0 ? $_POST['datatype']:'3').")'";
					  }
						$sql.="	WHERE field = '" . $SelectedID . "'";
		$msg = _('The Wage Item record has been updated');
	} elseif ($InputError !=1) {

	
		$sql = "INSERT INTO `wageitem`( `field`, `title`, `list`, `formulaID`, datatype,witype, `crtdate`,print, `flag`) VALUES (
		      	'" . $_POST['field'] . "',
						'" . $_POST['Description'] . "',
							'" . $_POST['list'] . "',0,";
				 if (array_key_exists($_POST['field'],$fldtype) ){
				 		$sql.=" '" .$fldtype[$_POST['field']] ."',";				 	
				 	
					}else{	
							if ($_POST['datatype_']=='double'){
							
						$sql.="  '" .$_POST['datatype_'] ."(". ($_POST['datatype']>4 ? $_POST['datatype'].',2':'5,2').")',";
					 
					  
					  }else{
					  		$sql.="  '" .$_POST['datatype_'] ."(". ($_POST['datatype']>0 ? $_POST['datatype']:'3').")',";
					  }		
	      //		$sql.=" '"  .$_POST['datatype_'] ."(". (strpos($_POST['datatype'],',') ? $_POST['datatype']:$_POST['datatype'].',0').")',";
		  	 }
		$sql.="	'" . $_POST['witype'] . "',				
						
							'" . date('Y-m-d') . "',
									" . $_POST['print'] . ",
								'0'	)";						
		$msg = _('The new Wage item has been added to the database');
 //  
	}
	//run the SQL from either of the above possibilites
//prnMsg($sql.'=='.$InputError,'info');
	if ($InputError !=1){
		$result = DB_query($sql,_('The update/addition of the work centre failed because'));
		prnMsg($msg,'success');
		unset ($_POST['field']);
		unset ($_POST['Description']);
		unset ($_POST['print']);
		unset ($_POST['witype']);
		unset ($_POST['datatype']);
			unset ($_POST['datatype_']);
			unset ($_POST['list']);
		unset ($SelectedID);
	}

} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button



	$sql= "SELECT COUNT(*)  from information_schema.COLUMNS where table_name = 'wage' and table_schema = '".$_SESSION['DatabaseName']."' and    COLUMN_NAME='" . $SelectedID . "'";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		prnMsg(_('Cannot delete this field because table  have been created '),'warn');
	}  else {
	
			$sql="DELETE FROM wageitem WHERE field='" . $SelectedID . "'";
			$result = DB_query($sql);
			prnMsg(_('The selected field has been deleted'),'succes');
		} 
	 // end of 
}if (isset($_POST['import'])) {
	$sql="INSERT INTO `wageitem`(`field`, `title`, `list`, `formulaID`, `datatype`, `crtdate`, `print`, `witype`, `flag`) select  COLUMN_NAME field,COLUMN_NAME title ,0 list,0 formulaID,COLUMN_TYPE datatype ,now() crtdate,0 print,0 witype,0 flag  from information_schema.COLUMNS where table_name = 'wagefile' and table_schema = 'hgrh_erp' and COLUMN_NAME not in( SELECT field COLUMN_NAME FROM hgrh_erp.wageitem)";	
	}

	echo '</table>
	<br />
	<div class="centre">
		<input type="submit" name="submit" value="' . _('Enter Information') . '" />
		<input type="submit" name="import" value="' . _('Item Import') . '" />
	</div>
	</div>
      </form>';
include('includes/footer.php');
?>
