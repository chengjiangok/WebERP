
<?php
/* $Id: Mould.php   $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-05-08 14:01:40 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-06-10 03:04:52
 */
include('includes/session.php');
$Title = '注塑模具维护';

include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['Edit'])){
	$SelectedParent =explode('^', $_GET['Edit']);
}else if (isset($_POST['Edit'])){
	$SelectedParent =explode('^', $_POST['Edit']);
}


if (isset($Errors)) {
	unset($Errors);
}
$_POST['costitem']=2;

$Errors = array();
$InputError = 0;
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
  $SQL='SELECT `confvalue`FROM `myconfig` WHERE  confname="BOM" AND costitem IN (SELECT `code` FROM `workcentres` WHERE worktype=2)';
  $Result = DB_query($SQL);
  $myrow=DB_fetch_assoc($Result);
  $BOMarr =json_decode($myrow['confvalue'],true );
 
 // var_dump($BOMarr);
/*
if (isset($Select)) {   //row 1073 end 		
	$SelectedParent = $Select;
	unset($Select);// = NULL;
	echo '<p class="page_title_text noprint"><img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') .
		'" alt="" />' . ' ' . $Title . '</p><br />';
	$bomstr='';

	if(isset($_GET['ReSelect'])) {
		$SelectedParent = $_GET['ReSelect'];
	}
   
	$sql = "SELECT stockmaster.description,
					stockmaster.mbflag
			FROM stockmaster
			WHERE stockmaster.stockid='" . $SelectedParent . "'";

	$ErrMsg = _('Could not retrieve the description of the parent part because');
	$DbgMsg = _('The SQL used to retrieve description of the parent part was');
	$result=DB_query($sql,$ErrMsg,$DbgMsg);

	$myrow=DB_fetch_row($result);

	$StockID = $SelectedParent;
	if (function_exists('imagecreatefromjpeg')){
		if ($_SESSION['ShowStockidOnImages'] == '0'){
			$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
							'&amp;StockID='.urlencode($StockID).
							'&amp;text='.
							'&amp;width=100'.
							'&amp;eight=100'.
							'" alt="" />';
		} else {
			$StockImgLink = '<img src="GetStockImage.php?automake=1&amp;textcolor=FFFFFF&amp;bgcolor=CCCCCC'.
							'&amp;StockID='.urlencode($StockID).
							'&amp;text='. $StockID .
							'&amp;width=100'.
							'&amp;height=100'.
							'" alt="" />';
		}
	} else {
		if( isset($StockID) AND file_exists($_SESSION['part_pics_dir'] . '/' .$StockID.'.jpg') ) {
			$StockImgLink = '<img src="' . $_SESSION['part_pics_dir'] . '/' . $StockID . '.jpg" height="100" width="100" />';
		} else {
			$StockImgLink = _('No Image');
		}
	}


		echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Select=' . $SelectedParent .'">';
         echo '</div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
        //已录入组件显示

	    
 
    if (isset($SelectedParent) AND isset($_POST['Submit'])) { 
		//editing a component need to do some validation of inputs
		$i = 1;

		if (!Is_Date($_POST['EffectiveAfter'])) {
			$InputError = 1;
			prnMsg(_('The effective after date field must be a date in the format') . ' ' .$_SESSION['DefaultDateFormat'],'error');
			$Errors[$i] = 'EffectiveAfter';
			$i++;
		}
		if (!Is_Date($_POST['EffectiveTo'])) {
			$InputError = 1;
			prnMsg(_('The effective to date field must be a date in the format')  . ' ' .$_SESSION['DefaultDateFormat'],'error');
			$Errors[$i] = 'EffectiveTo';
			$i++;
		}
	
		if(!Date1GreaterThanDate2($_POST['EffectiveTo'], $_POST['EffectiveAfter'])){
			$InputError = 1;
			prnMsg(_('The effective to date must be a date after the effective after date') . '<br />' . _('The effective to date is') . ' ' . DateDiff($_POST['EffectiveTo'], $_POST['EffectiveAfter'], 'd') . ' ' . _('days before the effective after date') . '! ' . _('No updates have been performed') . '.<br />' . _('Effective after was') . ': ' . $_POST['EffectiveAfter'] . ' ' . _('and effective to was') . ': ' . $_POST['EffectiveTo'],'error');
			$Errors[$i] = 'EffectiveAfter';
			$i++;
			$Errors[$i] = 'EffectiveTo';
			$i++;
		}
		if($_POST['AutoIssue']==1 AND isset($_POST['Component'])){
			$sql = "SELECT controlled FROM stockmaster WHERE stockid='" . $_POST['Component'] . "'";
			$CheckControlledResult = DB_query($sql);
			$CheckControlledRow = DB_fetch_row($CheckControlledResult);
			if ($CheckControlledRow[0]==1){
				prnMsg(_('Only non-serialised or non-lot controlled items can be set to auto issue. These items require the lot/serial numbers of items issued to the works orders to be specified so autoissue is not an option. Auto issue has been automatically set to off for this component'),'warn');
				$_POST['AutoIssue']=0;
			}
		}

		if (!in_array('EffectiveAfter', $Errors)) {
			$EffectiveAfterSQL = FormatDateForSQL($_POST['EffectiveAfter']);
		}
		if (!in_array('EffectiveTo', $Errors)) {
			$EffectiveToSQL = FormatDateForSQL($_POST['EffectiveTo']);
		}

		if (isset($SelectedParent) AND isset($SelectedComponent) AND $InputError != 1) {
			//更新BOM
			$Sequence = filter_number_format($_POST['Sequence']);
			$Digitals = GetDigitals($_POST['Sequence']);
			$Sequence = $Sequence * pow(10,$Digitals);
			$sql = "UPDATE bom SET sequence='" . $Sequence . "',
						digitals = '" . $Digitals . "',
						workcentreadded='" . $_POST['WorkCentreAdded'] . "',
						loccode='" . $_POST['LocCode'] . "',
						effectiveafter='" . $EffectiveAfterSQL . "',
						effectiveto='" . $EffectiveToSQL . "',
						quantity= '" . filter_number_format($_POST['Quantity']) . "',
						autoissue='" . $_POST['AutoIssue'] . "',
						remark='" . $_POST['Remark'] . "'
					WHERE bom.parent='" . $SelectedParent . "'
					AND bom.component='" . $SelectedComponent . "'";

			$ErrMsg =  _('Could not update this BOM component because');
			$DbgMsg =  _('The SQL used to update the component was');

			$result = DB_query($sql,$ErrMsg,$DbgMsg);
			$msg = _('Details for') . ' - ' . $SelectedComponent . ' ' . _('have been updated') . '.';
			UpdateCost($db, $SelectedComponent);

		} elseif ($InputError !=1 AND ! isset($SelectedComponent) AND isset($SelectedParent)) {
         
			
			       $msg='';
					//	ECHO $_POST['EffectiveAfter'].$_POST['EffectiveTo'];
		 			for($i=1;$i<5;$i++){
						 $sql="SELECT count(stockid) FROM stockmaster WHERE stockid='".$bomarr[$i]['Component']."'";
					     $result = DB_query($sql,$ErrMsg,$DbgMsg);
						 $row=DB_fetch_row($result);
						 if ($row[0]==0) {
                             insertstock($bomarr[$i]['Component']);
                         //   $msg.=$row[0];
						 }


					 }
					// prnMsg($msg,'info');
					$ErrMsg = _('Could not insert the BOM component because');
					$DbgMsg = _('The SQL used to insert the component was');
                  DB_Txn_Begin();
				  $ir=0;
				 for($i=0;$i<5;$i++){
                    $Sequence = filter_number_format($_POST['Sequence'][$i]);//系列
					$Digitals = GetDigitals($_POST['Sequence'][$i]);
					$Sequence = $Sequence * pow(10,$Digitals);
					$bomstr.=$_POST['bomqut'][$i];
					
					$sql = "INSERT INTO bom (sequence,
								        	digitals,
											parent,
											component,
											workcentreadded,
											loccode,
											quantity,
											effectiveafter,
											effectiveto,
											autoissue,
											remark)
							VALUES ('" .$Sequence . "',
								'" . $Digitals . "',
								'".$SelectedParent."',
								'" . $bomarr[$i]['Component'] . "',
								'21',
								'1',
								" . filter_number_format($_POST['bomqut'][$i]) . ",
								'" . $EffectiveAfterSQL . "',
								'" . $EffectiveToSQL . "',
								0,'')";
					$result = DB_query($sql,$ErrMsg,$DbgMsg);
					if ($result){
						$ir++;
					}

				 } 
				 if ($ir==5){
					 $sql="UPDATE mould SET flg=1 WHERE stockid='".$SelectedParent."'";
				     $result = DB_query($sql);
					 if ($result){
						 DB_Txn_Commit();
					 }
				 }
					UpdateCost($db, $_POST['Component']);
				//	$msg = _('A new component part') . ' ' . $_POST['Component'] . ' ' . _('has been added to the bill of material for part') . ' - ' . $SelectedParent . '.';

	    } else {

			
					prnMsg( _('The component') . ' ' . $_POST['Component'] . ' ' . _('is already recorded as a component of') . ' ' . $SelectedParent . '.' . '<br />' . _('Whilst the quantity of the component required can be modified it is inappropriate for a component to appear more than once in a bill of material'),'error');
					$Errors[$i]='ComponentCode';
	   }


	

		if ($msg != '') {
			prnMsg($msg,'success');
			}
	 

	}
  
}	*/
	// Work around to auto select\
//$sql="SELECT stockid, description FROM stockmaster WHERE mbflag='M' AND used=1 AND categoryid IN (SELECT loccode FROM locationusers WHERE userid='".$_SESSION['UserID']."') ";
		
	/*
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		prnMsg( _('At least one stock description keyword or an extract of a stock code must be entered for the search'), 'info' );
	} else {*/
		$sql="SELECT mouldid, 
			mould.stockid,  
			stockmaster.description,  								  
			mould.debtorno,
			name,
			Parting,
			materialno, 
			s.description matname,	
			cavityok,			
			mycyctime,						
			weight, 
			sprue, 				
			loss, 
			Tonnage, 			
			modate, 
			remark,
			motype,		
			flg ,
			mould.units
			FROM mould  LEFT JOIN stockmaster  ON  stockmaster.stockid=mould.stockid 
			LEFT JOIN debtorsmaster  ON  mould.debtorno=debtorsmaster.debtorno 
			LEFT JOIN stockmaster s ON  s.stockid=mould.materialno 
			WHERE  stockmaster.categoryid  IN (SELECT loccode FROM locationusers WHERE userid='".$_SESSION['UserID']."') ";
		if (mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';
			
              $sql.=" AND stockmaster.description " . LIKE . " '".$SearchString."' "; 
		

		} elseif (mb_strlen($_POST['StockCode'])>0){
			$sql="  AND mould.stockid " . LIKE  . "'" . $_POST['StockCode'] . "%'  ";
		

		}

		$ErrMsg = _('The SQL to find the parts selected failed with the message');
		$sql.=" ORDER BY mould.stockid"; 
	//prnMsg($sql.'-'.mb_strlen($_POST['Keywords']).'='.mb_strlen($_POST['Keywords']));
	$SearchResult= DB_query($sql);	

	if (isset($_POST['CSV'])) {
		$CSVListing ='"';
		$CSVListing .=iconv( "UTF-8", "gbk//TRANSLIT",'模具编码').'","'.iconv( "UTF-8", "gbk//TRANSLIT","产品编码").'","'.iconv( "UTF-8", "gbk//TRANSLIT","产品名称").'","'.iconv( "UTF-8", "gbk//TRANSLIT","客户编码").'","'.iconv( "UTF-8", "gbk//TRANSLIT","客户名称").'","'.iconv( "UTF-8", "gbk//TRANSLIT","部件").'","'.iconv( "UTF-8", "gbk//TRANSLIT","材料编码").'","'.iconv( "UTF-8", "gbk//TRANSLIT","材料名称").'","'.iconv( "UTF-8", "gbk//TRANSLIT","腔数").'","'.iconv( "UTF-8", "gbk//TRANSLIT","循环时间").'","'.iconv( "UTF-8", "gbk//TRANSLIT","产品单重").'","'.iconv( "UTF-8", "gbk//TRANSLIT","附件重量").'","'.iconv( "UTF-8", "gbk//TRANSLIT","损耗").'","'.iconv( "UTF-8", "gbk//TRANSLIT","注塑机吨数").'","'.iconv( "UTF-8", "gbk//TRANSLIT","更新日期"). '","'.iconv( "UTF-8", "gbk//TRANSLIT","备注"). '","'.iconv( "UTF-8", "gbk//TRANSLIT","类别"). '","'.iconv( "UTF-8", "gbk//TRANSLIT","标记").'"'. "\n";
		while ($InventoryValn = DB_fetch_row($SearchResult)) {
			$CSVListing .= '"';
			$CSVListing .= iconv( "UTF-8", "gbk//TRANSLIT",implode('","', $InventoryValn) ). '"' . "\n";
		}
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header('Content-disposition: attachment; filename='.iconv( "UTF-8", "gbk//TRANSLIT","注塑模具列表_") .  date('Y-m-d')  .'.csv');
		header("Pragma: public");
		header("Expires: 0");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $CSVListing;
		exit;
	
	}else{
		include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">' ;
	
	echo'<div class="page_help_text">注塑模具维护功能：添加 修改注塑模具</div>' .  '
			<div>
			<br />
			<table class="selection" cellpadding="3">
			<tr><td>' . _('Enter text extracts in the') . ' <b>' . _('description') . '</b>:</td>
				<td><input tabindex="1" type="text" name="Keywords" size="20" maxlength="25" /></td>
				<td><b>' . _('OR') . '</b></td>
				<td>' . _('Enter extract of the') . ' <b>' . _('Stock Code') . '</b>:</td>
				<td><input tabindex="2" type="text" name="StockCode" autofocus="autofocus" size="15" maxlength="18" /></td>
			</tr>
			</table>
	<br /><div class="centre">
			  <input tabindex="3" type="submit" name="Search" value="' . _('Search Now') . '" />
			
			  <input type="submit" name="CSV" value="导出CSV" /></div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="material" value="' . $_POST['material'] . '" />
	     <input type="hidden" name="customber" value="' . $_POST['customber'] . '" />';
}
	
	echo '<table class="selection noprint">';
				
	if (isset($_GET['Edit']) AND $InputError !=1){//|isset($_POST['SelectedParent'])||isset($SelectedParent)) {
		//editing a selected component from the link to the line item
		echo '<input type="hidden" name="SelectedParent" value="' . $SelectedParent . '" />';

		//echo '<input type="hidden" name="SelectedParent" value="' . $SelectedParent . '" />';
		echo '<input type="hidden" name="Component" value="' . $SelectedParent[0] . '" />';

		echo '<tr>
				<th colspan="3"><div class="centre"><b>' .$SelectedParent[0] .$SelectedParent[1]. ' </b>' . $StockImgLink . '</div></th>
			</tr>';
		echo '<tr>
				<td>1</td>
				<td>产品名称</td>
				<td><b>' .$SelectedParent[0] .$SelectedParent[1]  . '</b></td>
			
			</tr>';
			$_POST['Component']=$SelectedParent[0];
			$_POST['Parting']=$SelectedParent[3];
			$_POST['Remark'] = $SelectedParent[12];
			$_POST['sprue']=$SelectedParent[8];
			$_POST['mycyctime']=$SelectedParent[6];
			$_POST['cavityok']=$SelectedParent[5];
			$_POST['weight']=$SelectedParent[7];
			$_POST['loss']=$SelectedParent[9];
			$_POST['Tonnage']=$SelectedParent[10];
			$_POST['modate']=$SelectedParent[11];
			$_POST['material']=$SelectedParent[4];
			$_POST['customber']=$SelectedParent[2];
			$_POST['modelcode']=$SelectedParent[13];
			$_POST['units']=$SelectedParent[14];

	} else { //end of if $SelectedComponent
		
		
		//echo '<input type="hidden" name="SelectedParent" value="' . $SelectedParent . '" />';
		$sql = "SELECT	stockid, 
		               description
					FROM	stockmaster
					WHERE	categoryid  IN (SELECT `location` FROM `workcentres` WHERE worktype=2)
						AND (mbflag = 'M' OR mbflag = 'K' OR mbflag = 'A' ) 
						AND stockid NOT IN (SELECT	stockid FROM mould ) ORDER BY stockid";

		$ErrMsg = _('Could not retrieve the list of potential components because');
		$DbgMsg = _('The SQL used to retrieve the list of potential components part was');
		$result = DB_query($sql,$ErrMsg, $DbgMsg);
		echo '<tr>
				<th colspan="3"><div class="centre"><b>' . _('New Component Details')  . '</b></div></th>
			</tr>';
		echo '<tr>
				<td>1</td>
				<td>产品名称</td>';
		//echo '<td><select ' . (in_array('ComponentCode',$Errors) ?  'class="selecterror"' : '' ) .' tabindex="1" name="Component">';
        echo'<td><input type="text" name="Component"  id="Component"  value="'.$_POST['Component'].'" placeholder="输入编码、名称关键词筛选，然后选择物料" autocomplete="off"  list="ComponentList"   maxlength="50" size="50"  onChange="inSelect(this, ComponentList.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
			<datalist id="ComponentList"> ';
			$n=1;
			while ($row=DB_fetch_array($result )){
				$len=20-strlen($row['stockid']);
				echo '<option value="' .str_pad($row['stockid'].'~',$len, '-', STR_PAD_RIGHT).htmlspecialchars($row['description'], ENT_QUOTES,'UTF-8', false) . '"label=' .  $n. '>';
				$n++;
			}

		echo'</datalist>';
	
		echo'</td>
			</tr>';
	}
	echo'<tr>
		<td>2</td>
		<td>模具编码</td>					
		<td><input type="text"   name="modelcode"  value="'.$_POST['modelcode'].'"  > </td>							
	</tr>';	
	//DB_free_result($result);
			$sql = "SELECT	stockid, description
						FROM	stockmaster
						WHERE	categoryid IN (".$BOMarr['stockloccode'].")
							AND mbflag = 'B'  ORDER BY stockid";

			$result = DB_query($sql);
	echo '<tr>
			<td>3</td>
			<td>材料描述</td>';
	//$_POST['material']='H8051106';		
	echo'<td>
	       <input type="text" name="material" value="'.$_POST['material'].'" id="material" placeholder="输入编码、名称关键词筛选，然后选择物料" autocomplete="off"  list="materiallist"   maxlength="50" size="50"  onChange="inSelect(this, materiallist.options,'.	"'".'The  code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
	<datalist id="materiallist"> ';
	$n=1;
	while ($row=DB_fetch_array($result )){
		$len=20-strlen($row['stockid']);
		echo '<option value="' .str_pad($row['stockid'].'~',$len, '-', STR_PAD_RIGHT).htmlspecialchars($row['description'], ENT_QUOTES,'UTF-8', false) . '"label=' .  $n. '>';
		$n++;
	}

echo'</datalist>';
	echo '</td>
			</tr>';
	echo'<tr>
		<td>4</td>
		<td>客户名称</td>';
		$sql="SELECT custbranch.brname,						
					custbranch.branchcode,
					custbranch.debtorno								
				FROM custbranch
				LEFT JOIN debtorsmaster
				ON custbranch.debtorno=debtorsmaster.debtorno
				WHERE custbranch.disabletrans=0  AND  concat(custbranch.debtorno,custbranch.branchcode) IN (SELECT concat(csno, code) FROM custsupusers WHERE userid='".$_SESSION['UserID']."' )";
		DB_free_result($result);
		$result = DB_query($sql);
		echo'<td>
		<input type="text" name="customber"  id="customber" value="'.$_POST['customber'].'"  placeholder="输入编码、名称关键词筛选，然后选择客户"  autocomplete="off"  list="customberlist"   maxlength="50" size="50"  onChange="inSelect(this, customberlist.options,'.	"'".'The  code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
		<datalist id="customberlist"> ';
		$n=1;
		while ($row=DB_fetch_array($result )){
			$len=15-strlen($row['debtorno'].$row['branchcode']);
			echo '<option value="' .str_pad($row['debtorno'].'~'.$row['branchcode'].'~',10, '-', STR_PAD_RIGHT) . $row['brname']  . '"label=' .  $n. '>';
			$n++;
		}

		echo'</datalist>';

	echo '</td>
			</tr>';
	echo'<tr>
		<td>5</td>
		<td>部件</td>					
		<td><input type="number"   name="Parting"  value="'.$_POST['Parting'].'" min="1" max="30"  size="10"> </td>							
	</tr>
		<tr>
		<td>6</td>
		<td>腔数</td>		
		<td><input type="number"   name="cavityok"  value="'.$_POST['cavityok'].'" min="1" max="100" size="10" > </td>											
	</tr>
		<tr>
		<td>7</td>
		<td>循环时间(秒)</td>		
		<td><input type="number"   name="mycyctime"  value="'.$_POST['mycyctime'].'" min="1" max="1200"  size="10"> </td>													
	</tr>
		<tr>
		<td>8</td>
		
		<td>产品单重(g)</td>
		<td><input type="text" class="number"  name="weight"  min="10"  max="10" size="10" value="'.$_POST['weight'].'"  > </td>							
	</tr>
		<tr>
		<td>9</td>

		<td>附件重(g)</td>
		<td><input  type="text" class="number"   name="sprue"   min="1"  max="1000" size="10" value="'.$_POST['sprue'].'"  > </td>							
	</tr>
	<tr>
		<td>10</td>
		<td>材料损耗%</td>

		<td><input type="text" class="number"   name="loss"  value="'.$_POST['loss'].'"  min="1"  max="10" size="10"  > </td>							
	</tr>
	<tr>
		<td>11</td>
		<td>注塑机重量(T)</td>

		<td><input type="number"   name="Tonnage"  value="'.$_POST['Tonnage'].'" min="1" max="300" size="10" > </td>							
	</tr>
		<tr>
		<td>12</td>
		<td>摘要</td>

		<td><textarea  rows="3" col="70"  name="Remark" >' . $_POST['Remark'] . '</textarea></td>
	</tr>';
	echo '</table>';
	echo '<br /><div class="centre"><input tabindex="3" type="submit" name="Update" value="' . _('Update') . '" /></div>';



	if ( (isset($_POST['Search']) AND isset($SearchResult)) ||isset($SelectedParent) OR isset($_POST['CSV'])|| isset($_POST['Next'])|| isset($_POST['Go'])||isset($_POST['Previous'])) {
		$ListCount = DB_num_rows($SearchResult);
	if ($ListCount > 0) {
	
		// If the user hit the search button and there is more than one item to show
		$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
		if (isset($_POST['Next'])) {
			if ($_POST['PageOffset'] < $ListPageMax) {
				$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
			}
		}
		if (isset($_POST['Previous'])) {
			if ($_POST['PageOffset'] > 1) {
				$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
			}
		}
		if ($_POST['PageOffset'] > $ListPageMax) {
			$_POST['PageOffset'] = $ListPageMax;
		}
		if ($ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
			echo '<select name="PageOffset">';
			$ListPage = 1;
			while ($ListPage <= $ListPageMax) {
				if ($ListPage == $_POST['PageOffset']) {
					echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
				} else {
					echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
				}
				$ListPage++;
			}
			echo '</select>
				<input type="submit" name="Go" value="' . _('Go') . '" />
				<input type="submit" name="Previous" value="' . _('Previous') . '" />
				<input type="submit" name="Next" value="' . _('Next') . '" />
				<input type="hidden" name="Keywords" value="'.$_POST['Keywords'].'" />
				<input type="hidden" name="StockCat" value="'.$_POST['StockCat'].'" />
				<input type="hidden" name="StockCode" value="'.$_POST['StockCode'].'" />
				<br />
				</div>';
		}
		echo '<table class="selection">';
		echo '<tr>
			<th colspan="17"><div class="centre"></div></th>
	 	</tr>';

	$BOMTree = array();

	$i =0;
	
	$TableHeader =  '<tr>						
						<th>' . _('Sequence') . '</th>
						<th>模具编码</th>
						<th>产品编码</th>
						<th>产品描述</th>
						<th>客户名</th>
						<th>部件</th>
						<th>材料编码</th>
					     <th>材料名称</th>
						 <th>腔数</th>
						<th>循环时间</th>
						<th>产品单重</th>
						<th>附件单重</th>
						<th>损耗</th>
						<th>注塑机吨数</th>
						<th>启用日期</th>
						<th>备注</th>
						<th></th>
					</tr>';
	echo $TableHeader;	
			$i=1;
			$j = 1;
			$k = 0; //row counter to determine background colour
			$RowIndex = 0;
			if (DB_num_rows($SearchResult) <> 0) {
				DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			}
		while (($myrow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {

		  	if ($myrow['flg']>=0)	{
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				}else {
					echo '<tr class="OddTableRows">';
					$k++;
				}
	
 
				echo '	<td>'.$i.'</td>
						<td>' .$myrow['mouldid'] . '</td>
						<td>'. $myrow['stockid'].'</td>				
						<td>'. $myrow['description'].' </td>
						<td>'. $myrow['debtorno'].$myrow['brname'].'</td>
						<td>'. $myrow['Parting'].'</td>
						<td>'. $myrow['materialno'].' </td>	
						<td>'. $myrow['matname'].'</td>		
						<td>'. $myrow['cavityok'].'</td>					
						<td>'. $myrow['mycyctime'].'</td>
						<td>'. $myrow['weight'].'</td>
						<td>'. $myrow['sprue'].'</td>	
						<td>'. $myrow['loss'].'</td>							
						<td>'. $myrow['Tonnage'].'</td>
						<td>'. $myrow['modate'].'</td>
						<td>'.$myrow['remark'].'</td>';				 
				echo '<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '?Edit=' . $myrow['stockid'] .'^' .$myrow['description'] .'^'.$myrow['debtorno'].'^'.$myrow['Parting'].'^'.$myrow['materialno'].'^'.$myrow['cavityok'].'^'.$myrow['mycyctime'].'^'.$myrow['weight'].'^'.$myrow['sprue'].'^'.$myrow['loss'].'^'.$myrow['Tonnage'].'^'.$myrow['modate'].'^'.$myrow['remark'].'^'.$myrow['mouldid'].'^'.$myrow['units'].  '">' . _('Edit') . '</a></td>';
				                                                                                              //0                                 1                         2                        3                  4                        5                      6											78								9		10		        11                           12
				echo'		</tr>';
				$RowIndex = $RowIndex + 1;
		  		$i++;
		
			}
		}
	echo '</table>';
	}
}//row326

	

if (isset($_POST['Update'])){
	if (isset($_POST['SelectedParent'])){

	$sql="UPDATE  mould  
			SET   debtorno ='".explode('~',$_POST['customber'])[0]."',
					Parting =	'".$_POST['Parting']."',
					mouldid =	'".$_POST['modelcode']."', 
					materialno ='".explode('~',$_POST['material'])[0]."',
					cyctime ='".$_POST['mycyctime']."',
					mycyctime ='".$_POST['mycyctime']."',
					Tonnage =	'".$_POST['Tonnage']."',
					weight =	'".$_POST['weight']."',
					sprue =	'".$_POST['sprue']."',
					cavity = 	'".$_POST['cavityok']."',
					cavityok =	'".$_POST['cavityok']."', 
					loss = 	'".$_POST['loss']."',
					modate = '".date('Y-m-d')."',
					remark='".$_POST['Remark'] ."'
				WHERE  stockid = '".explode('~',$_POST['Component'])[0]."'";
	}else{
	$sql="INSERT INTO mould(mouldid,
						stockid,
						motype,
						debtorno,
						Parting,
						materialno,
						cyctime,
						mycyctime,
						Operator,
						Tonnage,
						deviceid,
						weight,
						sprue,
						cavity,
						cavityok,
						loss,
						Diesize,
						modate,
						remark,
						units)
						VALUE('".$_POST['modelcode']."',
						'".explode('~',$_POST['Component'])[0]."',
						'1',
						'".explode('~',$_POST['customber'])[0]."',
						'".$_POST['Parting']."',
						'".explode('~',$_POST['material'])[0]."',
						'".$_POST['mycyctime']."',
						'".$_POST['mycyctime']."',
						'',
						'".$_POST['Tonnage']."',
						'',
						'".$_POST['weight']."',
						'".$_POST['sprue']."',
						'".$_POST['cavityok']."',
						'".$_POST['cavityok']."',
						'".$_POST['loss']."',
						'',
						'".date('Y-m-d')."',
						'".$_POST['Remark'] ."',
						'".$_POST['units'] ."')";
		
						// var_dump($_POST['SelectedParent']);
			}		
			//prnMsg($sql.'[690*]' ,'info');	
		$result=DB_query($sql);

}
	echo '</div>
	      </form>';
include('includes/footer.php');  
function insertstock($stockid){
	$typ=array(261=>'模具工时',262=>'设备工时',263=>'设备功耗',264=>'操作工时');
   
    DB_Txn_Begin();
				$sql = "INSERT INTO stockmaster (stockid,
												description,
												longdescription,
												categoryid,
												units,
												mbflag,
												eoq,
												discontinued,
												controlled,
												serialised,
												perishable,
												volume,
												grossweight,
												netweight,
												barcode,
												discountcategory,
												taxcatid,
												decimalplaces,
												shrinkfactor,
												pansize)
							VALUES ('".$stockid."',
								'" . $typ[substr($stockid,0,3)].str_replace(substr($stockid,0,3),'',$stockid) . "',
								'" . $typ[substr($stockid,0,3)].str_replace(substr($stockid,0,3),'',$stockid) . "',
								'" . substr($stockid,0,2) . "',
								'H','G',0,	0,	0,  0,	0,	0,	0,	0,	'',	0,						
								0,	4,  0,0)";

				$ErrMsg =  _('The item could not be added because');
				$DbgMsg = _('The SQL that was used to add the item failed was');
				$result = DB_query($sql, $ErrMsg, $DbgMsg,'',true);
				if (DB_error_no() ==0) {
					//now insert the language descriptions
					$ErrMsg = _('Could not update the language description because');
					$DbgMsg = _('The SQL that was used to update the language description and failed was');
					$result = DB_query("INSERT INTO stockdescriptiontranslations (stockid,
																					language_id,
																					descriptiontranslation,
																					longdescriptiontranslation)
													VALUES('" . $stockid . "',
													            'zh_CN.utf8', 
																'zh_CN_utf8',
																	'zh_CN_utf8'													
																)",$ErrMsg,$DbgMsg,true);
				
				

					$result = DB_query("INSERT INTO stockitemproperties (stockid,
													stkcatpropid,
													value)
													VALUES ('" . $stockid . "',
														'" .  substr($stockid,2,1) . "',
														'0')",
								$ErrMsg,$DbgMsg,true);
				
					//Add data to locstock

					$sql = "INSERT INTO locstock (loccode,
													stockid)
										SELECT locations.loccode,
										'" . $stockid . "'
										FROM locations";

					$ErrMsg =  _('The locations for the item') . ' ' . $stockid .  ' ' . _('could not be added because');
					$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
					$InsResult = DB_query($sql,$ErrMsg,$DbgMsg,true);
					DB_Txn_Commit();
				}
}
?>
