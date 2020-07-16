<?php

/* $Id: BOMsMold.php  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-03-26 14:01:40 
 * @Last Modified by: chengjiang
 * @Last Modified time: 2019-06-10 03:18:37
 */
include('includes/session.php');
$Title = '注塑BOM维护';
include('includes/SQL_CommonFunctions.inc');
/*
if (isset($_GET['SelectedParent'])){
	$SelectedParent = $_GET['SelectedParent'];
}else if (isset($_POST['SelectedParent'])){
	$SelectedParent = $_POST['SelectedParent'];
}
*/
  // $categoryid=4;
if (isset($_GET['Edit'])){
	$Select = $_GET['Edit'];
} elseif (isset($_POST['Edit'])){
	$Select = $_POST['Edit'];
}

if (isset($Errors)) {
	unset($Errors);
}
$_POST['costitem']=2;
$action='';
if (isset($_GET)){
	if ($_SERVER["QUERY_STRING"]!=''){
		$action='?'.$_SERVER["QUERY_STRING"];
	}
}
//$bomarr= array();
/*
    $SQL="SELECT  confvalue FROM myconfig WHERE confname='BOM'";
    $Result = DB_query($SQL);
	$myrow=DB_fetch_array($Result);
	$BOMarr =json_decode($myrow['confvalue'],true );
    //$categorysub=$bomjsn['product'];
    //$stocksub=$bomjsn['stock'];*/
$Errors = array();
$InputError = 0;
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
$sql = "SELECT stockmaster.stockid,
				stockmaster.description,
				stockmaster.units,	
				stockmaster.categoryid,			
				case when mould.flg is null then -1 else case when v_bomstockid.parent is null then 0 else 1 end end flg,  
				mould.modate					
				FROM stockmaster 
				LEFT JOIN mould ON stockmaster.stockid=mould.stockid
				LEFT JOIN v_bomstockid ON stockmaster.stockid=v_bomstockid.parent
				WHERE  stockmaster.categoryid  IN (SELECT `location` FROM `workcentres` WHERE worktype=2)
				AND (stockmaster.mbflag='M' OR stockmaster.mbflag='K' OR stockmaster.mbflag='A' )
				";
		//worktype=2生产中心类型注塑
	// Work around to auto select
	/*
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		$_POST['StockCode']='%';
	}
	if ($_POST['Keywords'] AND $_POST['StockCode']) {
		prnMsg( _('Stock description keywords have been used in preference to the Stock code extract entered'), 'info' );
	}
	if ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
		prnMsg( _('At least one stock description keyword or an extract of a stock code must be entered for the search'), 'info' );
	} else {*/
if (mb_strlen($_POST['Keywords'])>0) {
	//insert wildcard characters in spaces
	$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

	$sql.="	AND stockmaster.description " . LIKE . " '".$SearchString."' ";

} elseif (mb_strlen($_POST['StockCode'])>0){
	$sql .= " AND stockmaster.stockid " . LIKE  . "'" . $_POST['StockCode'] . "%'  ";
		

}

$ErrMsg = _('The SQL to find the parts selected failed with the message');
$sql.=" ORDER BY stockmaster.stockid";
$SearchResult = DB_query($sql,$ErrMsg);

	
	 //prnMsg($sql,'info');	
    
if (isset($_POST['CSV'])) {
	
	$CSVListing ='"';
	$CSVListing .=iconv("UTF-8","gbk//TRANSLIT",'产品编码').'","'.iconv( "UTF-8", "gb2312","产品名称").'","'.iconv( "UTF-8", "gb2312","单位").'","'.iconv( "UTF-8", "gb2312","BOM标记").'","'.iconv( "UTF-8", "gb2312","更新日期" ).'"'. "\n";
	while ($InventoryValn = DB_fetch_row($SearchResult)) {
		$CSVListing .= '"';
		$CSVListing .= iconv( "UTF-8", "gb2312",implode('","', $InventoryValn) ). '"' . "\n";
	}
	header('Content-Encoding: UTF-8');
	header('Content-type: text/csv; charset=UTF-8');
	header('Content-disposition: attachment; filename='.iconv( "UTF-8", "gb2312","注塑BOM名列表_") .  date("Y-m-d")  .'.csv');
	header("Pragma: public");
	header("Expires: 0");
	echo "\xEF\xBB\xBF"; // UTF-8 BOM
	echo $CSVListing;
	exit;

}else{
	include('includes/header.php');
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . $action.'" method="post">' .
	'<div class="page_help_text">' .  _('Select a manufactured part') . ' (' . _('or Assembly or Kit part') . ') ' . _('to maintain the bill of material for using the options below') .  '<br />' . _('Parts must be defined in the stock item entry') . '/' . _('modification screen as manufactured') . ', ' . _('kits or assemblies to be available for construction of a bill of material')  . '</div>' .  '
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
		<input tabindex="3" type="submit" name="SearchBOM" value="查询BOM" />
		<input type="submit" name="CSV" value="导出CSV" />
		</div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
}
if (isset($_GET['Edit'])){//$_POST['SearchBOM']) ){

	if (!isset($_POST['EffectiveTo']) OR $_POST['EffectiveTo']=='') {
		$_POST['EffectiveTo'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d'),Date('y')+3));
	}
	if (!isset($_POST['EffectiveAfter']) OR $_POST['EffectiveAfter']=='') {
		$_POST['EffectiveAfter'] = Date($_SESSION['DefaultDateFormat'],Mktime(0,0,0,Date('m'),Date('d')-1,Date('y')));
	}
	echo '<input type="hidden" name="Select" value="' . $Select . '" />';
	
			//	echo '<input type="hidden" name="SelectedComponent" value="' . $SelectedComponent . '" />';
	if (isset($Select)||isset($_POST['bomsave'])||isset($_POST['Select']) ) {
			if (!isset($Select)){
				$stkarr=explode('^',$_POST['Select']);
			}else{
				$stkarr=explode('^',$Select);
			}
		echo '<table class="selection noprint">';
		echo '<tr>
				<th colspan="6"><div class="centre"><b>' .  $stkarr[0]. '-'.$stkarr[2] . '</b></div></th>
				</tr>';
	
		echo '<tr>
				
				<td colspan="6" class="centre">' . _('Effective After') .'
				<input ' . (in_array('EffectiveAfter',$Errors) ?  'class="inputerror"' : '' ) . ' tabindex="5" type="text" required="required" name="EffectiveAfter" class="date" alt="' .$_SESSION['DefaultDateFormat'] .'" size="11" maxlength="10" value="' . $_POST['EffectiveAfter'] .'" />'
				._('Effective To') .'<input  ' . (in_array('EffectiveTo',$Errors) ?  'class="inputerror"' : '' ) . ' tabindex="6" type="text" name="EffectiveTo" class="date" alt="' .$_SESSION['DefaultDateFormat'] . '" size="11" maxlength="10" value="' . $_POST['EffectiveTo'] .'" /></td>
			
				
				</tr>';
		echo'<tr>
				<th>序号</th>
				<th>' . _('Code') . '</th>
				<th>' . _('Description') . '</th>
				<th>' . _('Units') . '</th>
				<th>数量</th>
				<th>备注</th>
				
			</tr>';
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
		if ($stkarr[1]==1){
			$sql = "SELECT	parent,
				stockmaster.description,
				sequence,
				component,
				workcentreadded,
				categoryid,
				effectiveafter,
				effectiveto,
				bom.units,
				quantity,
				autoissue,
				remark,
				digitals
			FROM bom
			LEFT JOIN stockmaster ON stockmaster.stockid = bom.component
			WHERE parent='".$stkarr[0]."'";
			//INNER JOIN locationusers ON locationusers.loccode=categoryid AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canview=1
		
			//prnMsg('321'.$sql,'info');
			//WHERE stockmaster.categoryid IN (SELECT	loccode	FROM locationusers	WHERE 	userid = '".$_SESSION['UserID']."')
			$Result = DB_query($sql);
			$BOMarr=array();
			$i=0;
			while ($row = DB_fetch_array($Result)) {

					$BOMarr[$i]['parent'] = $row['parent'];		// Assemble
					$BOMarr[$i]['component'] = $row['component'];	// Component
					$BOMarr[$i]['description'] = $row['description'];
					$BOMarr[$i]['loccode'] = $row['loccode'];
					$BOMarr[$i]['units'] = $row['units'];
					$BOMarr[$i]['quantity'] = $row['quantity'];
					$BOMarr[$i]['remark'] = $row['remark'];
					$i++;
				
			}
		}else{
		
			$sql="SELECT mouldid,
							Parting,
							materialno,
							mycyctime,
							Tonnage,
							weight,
							sprue,
							cavityok,
							loss,
							description,
							m.units,
							categoryid,
							deviceid,
							remark
						FROM mould m LEFT  JOIN stockmaster s ON m.materialno=s.stockid 
						WHERE m.stockid='".$stkarr[0]."' AND m.flg=0 limit 1";
			$Result = DB_query($sql);
			
			$row = DB_fetch_row($Result);
			$BOMarr=array();
			$BOMarr[0]['component'] = $row[2];	
			$BOMarr[0]['description'] = $row[9];
			$BOMarr[0]['loccode'] = $row[11];
			$BOMarr[0]['units'] = $row[10];	
			$BOMarr[0]['quantity'] = $row[5]*(1+$row[8]/100);	
			$BOMarr[0]['remark'] = $row[13];	

			$woid=substr($stkarr[3],0,1);
			$BOMarr[1]['component'] =$woid.'F1'.$row[0];
			$BOMarr[1]['description'] ='模具工时';
			$BOMarr[1]['loccode'] = $woid.'F';
			$BOMarr[1]['units'] = '秒';		
			$BOMarr[1]['quantity'] = $row[3];	
			$BOMarr[1]['remark'] = $row[13];	
			
			$BOMarr[2]['component'] = $woid.'F2'.$row[4];//$row[12]
			$BOMarr[2]['description'] = '设备工时';
			$BOMarr[2]['quantity'] = $row[3];	
			$BOMarr[2]['loccode'] = $woid.'F';
			$BOMarr[2]['units'] ='秒';
			$BOMarr[2]['remark'] = $row[13];	
			
			$BOMarr[3]['component'] =$woid.'F4'.$stkarr[0];
			$BOMarr[3]['description'] = '物料工时';
			$BOMarr[3]['loccode'] = $woid.'F';
			$BOMarr[3]['quantity'] = $row[3];	
			$BOMarr[3]['units'] ='秒';	
			$BOMarr[3]['remark'] = $row[13];	
			
		
		}
		$i=1;
		//var_dump($BOMarr);
		foreach($BOMarr as $BOMItem){
			
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else {
				echo '<tr class="OddTableRows">';
				$k++;
			}
			echo '
			<td>' .$i. '</td>
			<td>' .$BOMItem['component'] . '</td>
			<td>' .$BOMItem['description'] . '  </td>
			<td>' .$BOMItem['units'] . '  </td>
			<td><input type="text" class="number" name="quantity[]" value="' . $BOMItem['quantity'] . '" > </td>
			<td><input type="text"  name="remark[]" value="' .$BOMItem['remark'] . '">  </td>
			</tr>';
			$i++;

		}
	
		
		echo '<tr>
				<td>'.$i.' </td>
				<td colspan="3">';   	
					//        <select tabindex="1" name="matlcode">';
	
				$sql = " SELECT stockid, 
								description,
								CASE WHEN convunit is null THEN s.units ELSE convunit END units,
								categoryid
							FROM stockmaster s LEFT JOIN unitsofmeasure u ON s.units=u.unitname
							WHERE	 mbflag = 'B'  ORDER BY stockid";
				//categoryid IN(SELECT loccode	FROM locationusers	WHERE	userid = '".$_SESSION['UserID']."')	AND
				$ErrMsg = _('Could not retrieve the list of potential components because');
				$DbgMsg = _('The SQL used to retrieve the list of potential components part was');
				$result = DB_query($sql,$ErrMsg, $DbgMsg);
				echo'<input type="text" name="matlcode" value="'.$_POST['matlcode'].'" id="matlcode" placeholder="输入编码、名称关键词筛选，然后选择物料" autocomplete="off"  list="matlcodelist"   maxlength="50" size="50"  onChange="inSelect(this, matlcodelist.options,'.	"'".'The  code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
					<datalist id="matlcodelist"> ';
					$n=1;
					while ($row=DB_fetch_array($result )){
						$len=20-strlen($row['stockid']);
						echo '<option value="' . str_pad($row['stockid'].'~',10, '-', STR_PAD_RIGHT) . $row['description'] .'~'.$row['units'] . '"label=' .  $n. '>';
						$n++;
					}
				
				echo'</datalist></td>';
			/*

			while ($myrow = DB_fetch_array($result)) {
				echo '<option value="' .$myrow['stockid'].'^'.$myrow['units'].'^'.$myrow['categoryid'].'">' . str_pad($myrow['stockid'],10, '_', STR_PAD_RIGHT) . $myrow['description'] .'__'.$myrow['units']. '</option>';
			} //end while loop

			echo '</select></td>';*/
		
		echo'   <td><input type="text" class="number"  name="quant" value="' . $myrow['quant'] . '">            </td>
				<td> <input type="text"  name="remk" value="' . $myrow['remk'] . '" >           </td>
			</tr>';
		echo '</table>
			<br />';
		echo'	<div class="centre noprint">
			<input tabindex="8" type="submit" name="bomsave" value="' . _('Enter Information') . '" />
			
		</div>';
		//var_dump($stkarr);
		if(isset($_POST['bomsave']) && $stkarr[1]!=1) {//$stkarr[1]!=1无BOM
			//prnMsg('348');
			for ($i=0;$i<count($_POST['quantity']);$i++){
				$BOMarr[$i]['quantity'] =$_POST['quantity'][$i];	
				$BOMarr[$i]['remark'] = $_POST['remark'][$i];

			}
			//	prnMsg( var_dump($_POST['quantity ']),'info');
			
			if (isset($_POST['quant'])&& is_numeric($_POST['quant'])) {					
			
				$BOMarr[4]['component'] = explode('~',$_POST['matlcode'])[0];
				$BOMarr[4]['description'] = '';
				$BOMarr[4]['quantity'] =$_POST['quant'];
				$BOMarr[4]['loccode'] = explode('~',$_POST['matlcode'])[2];
				$BOMarr[4]['units'] =explode('~',$_POST['matlcode'])[1];
				$BOMarr[4]['remark'] =$_POST['remk'];
			}
			$i=0;
			foreach($BOMarr as $BOMItem){
				//prnMsg(substr($BOMItem['loccode'],0,2),'info');
				if (substr($BOMItem['loccode'],0,2)=='2F'){
					insertstock($BOMItem['component']);

				}
				$sql="INSERT INTO bom(parent,
										sequence,
										component,
										workcentreadded,
										loccode,
										effectiveafter,
										effectiveto, 
										units,
										quantity, 
										autoissue, 
										remark, 
										digitals) VALUES
									('".$stkarr[0] ."',
									'".$i ."',
									'".$BOMItem['component'] ."',
									'".substr($BOMItem['loccode'],0,1) ."',
									'".$BOMItem['loccode'] ."',
									'".$_POST['EffectiveAfter']."',
									'".$_POST['EffectiveTo']."',
									'".$BOMItem['units'] ."',
									'".$BOMItem['quantity'] ."',
									'0',
									'".$BOMItem['remark']."',
									'0')";	
				$result = DB_query($sql, $ErrMsg, $DbgMsg,'',true);	
				$i++;
				//prnMsg($sql);
			}
			if ($i>1){
				unset($BOMarr);
				prnMsg('BOM单更新成功！','info');
			}
		}elseif(isset($_POST['bomsave']) && $stkarr[1]==1) {
			prnMsg('BOM--1！','info');

		}
	
	}

}//130
if ( (isset($_POST['Search']) AND isset($SearchResult) ) OR isset($_POST['CSV'])||isset($_POST['Next'])||isset($_POST['Previous'])||isset($_POST['Go'])) {
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
	echo '<table cellpadding="2" class="selection">';
	$TableHeader = '<tr>
						<th>' . _('Code') . '</th>
						<th>' . _('Description') . '</th>
						<th>' . _('Units') . '</th>
						<th>更新日期</th>
						<th>BOM标记</th>
						<th></th>
					</tr>';

	echo $TableHeader;

	$j = 1;
	$k=0; //row colour counter
	$RowIndex = 0;
	if (DB_num_rows($SearchResult) <> 0) {
		DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	while (($myrow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';;
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';;
			$k++;
		}
		if ($myrow['mbflag']=='A' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='G'){
			$StockOnHand = _('N/A');
		} else {
			$StockOnHand = locale_number_format($myrow['totalonhand'],$myrow['decimalplaces']);
		}
		$tab = $j+3;
		//if ($myrow['flg']==0) {

		printf( '<td>%s</td>
				<td>%s</td>
				<td class="number noprint">%s</td>
				<td>%s</td>
				<td>%s</td><td>'.
				($myrow['flg']!=-1?'<a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '?Edit=' . $myrow['stockid'] .'^'.$myrow['flg'].'^'.$myrow['description'].'^'.$myrow['categoryid'].'">' . _('Edit') . '</a>':'').'</td>
				</tr>',
				$myrow['stockid'],
			
				$myrow['description'],
				$myrow['units'],
				$myrow['modate'],
				($myrow['flg']==-1?'无模':($myrow['flg']==1?'Yes':'No'))
				);
				$RowIndex = $RowIndex + 1;
		$j++;
	//end of page full new headings if
	}
	//end of while loop

	echo '</table>';
	}
}elseif (isset($_POST['SearchBOM'])){
	//prnMsg('SearchBOM','info');
	$sql="SELECT	sequence,
						parent,
					s.description,
					component,
					t.description name,						
					workcentreadded,						
					bom.units,
					quantity,
					autoissue,
					effectiveafter,
					effectiveto,
					remark,
					digitals
				FROM bom
				LEFT JOIN stockmaster  s ON s.stockid = bom.component
				LEFT JOIN stockmaster  t ON t.stockid = bom.parent
			
				ORDER BY  parent,sequence";
				//	WHERE workcentreadded IN (SELECT DISTINCT LEFT(`loccode`,1) FROM `locationusers` WHERE locationusers.userid='" .  $_SESSION['UserID'] . "' )
				//prnMsg($sql,'info');
		$ErrMsg = _('The searched supplier records requested cannot be retrieved because');
		$SearchResult = DB_query($sql, $ErrMsg); 
			echo '<table cellpadding="2" class="selection">';
			$TableHeader = '<tr>
							<th>序号</th>
								<th>' . _('Code') . '</th>
								<th>' . _('Description') . '</th>
								<th>物料编码</th>
								<th>物料名称序号</th>
								<th>' . _('Units') . '</th>
								<th>数量</th>
								<th>更新日期</th>
								<th>到期日期</th>
								<th>备注</th>
						
							</tr>';
	
			echo $TableHeader;
	
			$j = 1;
			$k=0; //row colour counter
			$RowIndex = 0;
			if (DB_num_rows($SearchResult) <> 0) {
				DB_data_seek($SearchResult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			}
			while (($myrow = DB_fetch_array($SearchResult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
				if ($k==1){
					echo '<tr class="EvenTableRows">';;
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';;
					$k++;
				}
				if ($myrow['mbflag']=='A' OR $myrow['mbflag']=='K' OR $myrow['mbflag']=='G'){
					$StockOnHand = _('N/A');
				} else {
					$StockOnHand = locale_number_format($myrow['totalonhand'],$myrow['decimalplaces']);
				}
				$tab = $j+3;
				//if ($myrow['flg']==0) {
	
				printf( '<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						
						<td class="number noprint">%s</td>
						<td>%s</td>
						<td>%s</td>
						<td>%s</td>
						</tr>',
						$myrow['sequence'],		
						$myrow['parent'],					 
						$myrow['name'],
						$myrow['component'],	
						$myrow['description'],							
						$myrow['units'],
						$myrow['quantity'],
						$myrow['effectiveafter'],
						$myrow['effectiveto'],
						$myrow['remark'] );
					
						$RowIndex = $RowIndex + 1;
				$j++;
			//end of page full new headings if
			}
			//end of while loop
	
			echo '</table>';

}
	//end if results to show


echo '</div>
      </form>';

include('includes/footer.php');
    // This function created by Dominik Jungowski on PHP developer blog
function arrayUnique($array, $preserveKeys = false){
	//Unique Array for return
	$arrayRewrite = array();
	//Array with the md5 hashes
	$arrayHashes = array();
	foreach($array as $key => $item) {
		// Serialize the current element and create a md5 hash
		$hash = md5(serialize($item));
		// If the md5 didn't come up yet, add the element to
		// arrayRewrite, otherwise drop it
		if (!isset($arrayHashes[$hash])) {
			// Save the current element hash
			$arrayHashes[$hash] = $hash;
			//Add element to the unique Array
			if ($preserveKeys) {
				$arrayRewrite[$key] = $item;
			} else {
				$arrayRewrite[] = $item;
			}
		}
	}
	return $arrayRewrite;
} 


function GetDigitals($Sequence) {
	$SQLNumber = filter_number_format($Sequence);
	return strlen(substr(strrchr($SQLNumber, "."),1));
}
  /*插入stockmaaster 的4个表stockmaster  
          货品语言设定   stockdescriptiontranslations
         SELECT stockid, stkcatpropid, value FROM    stockitemproperties 
                    locstock*/
function insertstock($stockid){

	$result=DB_query("SELECT count( *) FROM `stockmaster` WHERE stockid='".$stockid."'");
	$row=DB_fetch_row($result);
	 if ($row[0]!=1){
	$typ=array('2F1'=>'模具工时','2F2'=>'设备工时','2F4'=>'工时');
    
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

					$sql = "INSERT INTO locstock (loccode,stockid)
										SELECT locations.loccode,
										'" . $stockid . "'
										FROM locations";

					$ErrMsg =  _('The locations for the item') . ' ' . $stockid .  ' ' . _('could not be added because');
					$DbgMsg = _('NB Locations records can be added by opening the utility page') . ' <i>Z_MakeStockLocns.php</i> ' . _('The SQL that was used to add the location records that failed was');
					$InsResult = DB_query($sql,$ErrMsg,$DbgMsg,true);
					DB_Txn_Commit();
				}
			}
}
?>
