

<?php
/*$ID GLJournalCheck.php $*/
/*
* @Author: chengjang 
* @Date: 2018-05-13
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-05-14 18:40:37
*/
 
include ('includes/session.php');
$Title = '凭证检验';
$ViewTopic='凭证';
$BookMark = 'JournalCheck';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (!isset($_POST['selectprd'])){
$_POST['selectprd']= $_SESSION['period'];
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="selectprd" value="' .$_POST['selectprd'] . '" />
	  <input type="hidden" name="unittag" value="' .$_POST['unittag'] . '" />';		
		
echo '<div><table class="selection">';
echo '<tr>
		<th colspan="2">' . _('Selection Criteria') . '</th></tr>';
		$sql="SELECT tagID, tagdatabase,tagdescription,flag FROM unittag   ORDER BY tagID ";
		$result = DB_query($sql);
if (isset($_SESSION['Tag'])){
	echo '<tr>
			<td>导出分组选择</td>
	<td><select name="unittag" size="1" >';
	while ($myrow=DB_fetch_array($result,$db)){
		if(isset($_POST['unittag']) AND $myrow['tagdatabase'].'-'.$myrow['tagID']==$_POST['unittag']){
		echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
					}
		echo  $myrow['tagdatabase'].'-'.$myrow['tagID']. '">' .$myrow['tagdescription']  . '</option>';

	}
	echo'</select>
		</td>
		</tr>';
}

echo '<tr>
<td>会计期间选择:</td>
<td><select name="selectprd" size="1" >'; 
$dtstr=date('Y',strtotime($_SESSION['lastdate']));   
	
			echo '<option selected="selected" value="';
			echo  $_SESSION['period'] .'">' .$dtstr. '</option>';
  echo '</select>
</td>
	</tr>';
echo '</table>';
echo '<br />
<div class="centre">
<input type="submit" name="acccheck" value="凭证科目检验" />
<input type="submit" name="accresult" value="科目检验" />	             
<input type="submit" name="jdcheck" value="凭证借贷检验" />
<input type="submit" name="narrcheck" value="凭证摘要检验" />';
echo'</div>';
  DB_data_seek($result,0);
  $row=DB_fetch_array($result,$db);

$comdatabase=$row['tagdatabase'];
//prnMsg($comdatabase);
		//连接数据库 导入单元组库
		$db1 = mysqli_connect($host , $DBUser, $DBPassword, $comdatabase, $mysqlport);
		mysqli_set_charset($db1, 'utf8');
if (isset($_POST['acccheck'])) {

	$transno=0;
	$str=explode('-',$_POST['unittag']);	   

	
	$sqlv="SELECT gltrans.typeno,
				systypes.typename,
				gltrans.type,
				gltrans.trandate,
				gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
				toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
				gltrans.tag,
				unittag.tagdescription
			FROM gltrans
			LEFT JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode	
			LEFT JOIN systypes
			ON gltrans.type=systypes.typeid
			LEFT JOIN unittag
			ON gltrans.tag=unittag.tagID
			WHERE  concat(gltrans.periodno,  gltrans.transno) IN (SELECT concat(periodno,transno) FROM gltrans WHERE account NOT IN (SELECT accountcode FROM chartmaster))	
			 ORDER BY periodno, gltrans.transno, gltrans.type,gltrans.typeno";

	$resultv = DB_query($sqlv);
	$showflg=DB_num_rows($resultv);
	if($showflg==0) {
	prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	} else {
	
	echo '<table class="selection">';
	echo '<tr>
	<th>' . _('Date') . '</th>
	<th>' . _('Voucher No') . '</th>
	<th>' . _('Account Code') . '</th>
	<th>' . _('Account Description') . '</th>
	<th>' . _('Narrative') . '</th>
	<th>' . _('Debits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
	<th>' . _('Credits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>';
		if (isset($_SESSION['Tag'])){
	echo'<th>单元分组 </th>';
	}
	echo '<th>导出标记</th>				
	</tr>';

	$LastJournal = 0;
	$LastType = -1;
	$r=0;
	while ($myrow = DB_fetch_array($resultv)){			
	if ($myrow['transno']!=$LastJournal ) {			
		if ($r==1){
	echo '<tr class="EvenTableRows">';
	$r=0;
	} else {
	echo '<tr class="OddTableRows">';
	$r=1;
	}
	echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
	      <td >' ._('Accounting'). $myrow['transno']. '</td>';

	} else {

	if ($r==1){
	echo '<tr class="EvenTableRows"><td colspan="2"></td>';
	$r=0;
	} else {
	echo '<tr class="OddTableRows"><td colspan="2"></td>';
	$r=1;
	}
	}

	echo '<td >' . $myrow['account'] . '</td>
			<td >' . $myrow['accountname'] . '</td>
			<td>'.$myrow['narrative']. '</td>
			<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord']['decimalplaces'])) . '</td>
			<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord']['decimalplaces']) ). '</td>';

	if ($myrow['transno']!=$LastJournal ){
	if (isset($_SESSION['Tag']))		{
	echo'<td >' . $myrow['tagdescription'] . '</td>';
	}			
	echo '<td class="number"><input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'" checked="true" ></td></tr>';
	//$LastType = $myrow['type'];
	$LastJournal = $myrow['transno'];		
		
	} else {
	echo '<td colspan="1"></td></tr>';
	}

	}	
	echo '</table>';
	} //end if no bank trans in the range to show   checked=true

}elseif (isset($_POST['narrcheck'])) {

	
	$str=explode('-',$_POST['unittag']);	
		
	$sqlv="SELECT gltrans.typeno,
				systypes.typename,
				gltrans.type,
				gltrans.trandate,
				gltrans.transno,
				gltrans.periodno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
				toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
				gltrans.tag,
				unittag.tagdescription
			FROM gltrans
			LEFT JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode	
			LEFT JOIN systypes
			ON gltrans.type=systypes.typeid
			LEFT JOIN unittag
			ON gltrans.tag=unittag.tagID
			WHERE  concat(gltrans.periodno,  gltrans.transno) IN (SELECT DISTINCT  concat(periodno,transno) FROM gltrans WHERE narrative='' OR narrative is null)	
			 ORDER BY periodno, gltrans.transno, gltrans.type,gltrans.typeno";

	$resultv = DB_query($sqlv);
	$showflg=DB_num_rows($resultv);
	if($showflg==0) {
	prnMsg('凭证摘要检验完成，没有空白摘要！', 'info');
	} else {
		echo '<table class="selection">';
	echo '<tr>
	<th>' . _('Date') . '</th>
	<th>' . _('Voucher No') . '</th>	
	<th>' . _('Narrative') . '</th>';	
		if (isset($_SESSION['Tag'])){
	echo'<th>单元分组 </th>';
	}
	echo '<th></th>				
	</tr>';

	$LastJournal = 0;
	$LastType = -1;
	$r=0;
	$RowIndex=0;
	while ($myrow = DB_fetch_array($resultv)){			
		if ($myrow['transno']!=$LastJournal ) {			
			if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			} else {
				echo '<tr class="OddTableRows">';
				$r=1;
			}
		echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
		<input type="hidden" name="prdtranno'.$RowIndex.'" value="' . $myrow['periodno'].'^'.$myrow['transno'] . '" />					
			<td >' ._('Accounting'). $myrow['transno']. '</td>';	

		echo '<td><input type="text" name="narrative'.$RowIndex.'" value="'. $myrow['narrative'].'"  ></td>'		;

		if ($myrow['transno']!=$LastJournal ){
		if (isset($_SESSION['Tag']))		{
		echo'<td >' . $myrow['tagdescription'] . '</td>';
		}			
		echo '<td class="number"><input type="checkbox" name="chk[]" value="'. $RowIndex.'" checked="true" ></td></tr>';
		//$LastType = $myrow['type'];
		$LastJournal = $myrow['transno'];		
			
		} else {
		echo '<td colspan="1"></td></tr>';
		}
		$RowIndex++;

	}	
}
	echo '</table>';
	echo '
		<div class="centre">
			<input type="submit" name="narrsave" value="摘要保存" />';
		echo'</div><br />';
	//---------------
	DB_data_seek($resultv,0);
	echo '<table class="selection">';
	echo '<tr>
	<th>' . _('Date') . '</th>
	<th>' . _('Voucher No') . '</th>
	<th>' . _('Account Code') . '</th>
	<th>' . _('Account Description') . '</th>
	<th>' . _('Narrative') . '</th>
	<th>' . _('Debits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
	<th>' . _('Credits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>';
		if (isset($_SESSION['Tag'])){
	echo'<th>单元分组 </th>';
	}
	echo '<th>导出标记</th>				
	</tr>';

	$LastJournal = 0;
	$LastType = -1;
	$r=0;
	while ($myrow = DB_fetch_array($resultv)){			
		if ($myrow['transno']!=$LastJournal ) {			
			if ($r==1){
		echo '<tr class="EvenTableRows">';
		$r=0;
		} else {
		echo '<tr class="OddTableRows">';
		$r=1;
		}
		echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
			<td >' ._('Accounting'). $myrow['transno']. '</td>';

		} else {

		if ($r==1){
		echo '<tr class="EvenTableRows"><td colspan="2"></td>';
		$r=0;
		} else {
		echo '<tr class="OddTableRows"><td colspan="2"></td>';
		$r=1;
		}
		}

		echo '<td >' . $myrow['account'] . '</td>
				<td >' . $myrow['accountname'] . '</td>
				<td>'.$myrow['narrative']. '</td>
				<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord']['decimalplaces'])) . '</td>
				<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord']['decimalplaces']) ). '</td>';

		if ($myrow['transno']!=$LastJournal ){
		if (isset($_SESSION['Tag']))		{
		echo'<td >' . $myrow['tagdescription'] . '</td>';
		}			
		echo '<td class="number"><input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'" checked="true" ></td></tr>';
		//$LastType = $myrow['type'];
		$LastJournal = $myrow['transno'];		
			
		} else {
		echo '<td colspan="1"></td></tr>';
		}
	

	}	
	echo '</table>';
	} //end if no b
}elseif (isset($_POST['jdcheck'])) {
	
	
	$str=explode('-',$_POST['unittag']);	
	$sql="SELECT periodno, transno,ROUND( SUM(amount),2) FROM gltrans WHERE periodno>0 GROUP BY periodno,transno HAVING ROUND(SUM(amount),2)<>0";
		
	$sqlv="SELECT gltrans.typeno,
				systypes.typename,
				gltrans.type,
				gltrans.trandate,
				gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				toamount(gltrans.amount,-1,0,0,1,gltrans.flg) AS Debits,
				toamount(gltrans.amount,-1,0,0,-1,gltrans.flg) AS Credits,
				gltrans.tag,
				unittag.tagdescription
			FROM gltrans
			LEFT JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode	
			LEFT JOIN systypes
			ON gltrans.type=systypes.typeid
			LEFT JOIN unittag
			ON gltrans.tag=unittag.tagID
			WHERE  concat(gltrans.periodno,  gltrans.transno) IN (SELECT concat( periodno, transno) FROM gltrans WHERE periodno>0 GROUP BY concat(periodno,transno) HAVING ROUND(SUM(amount),2)<>0)	
			 ORDER BY periodno, gltrans.transno, gltrans.type,gltrans.typeno";

	$resultv = DB_query($sqlv);
	$showflg=DB_num_rows($resultv);
	if($showflg==0) {
		prnMsg('凭证借贷平衡检验完成，没有借贷不平的会计凭证！', 'info');
	} else {
	
	echo '<table class="selection">';
	echo '<tr>
	<th>' . _('Date') . '</th>
	<th>' . _('Voucher No') . '</th>
	<th>' . _('Account Code') . '</th>
	<th>' . _('Account Description') . '</th>
	<th>' . _('Narrative') . '</th>
	<th>' . _('Debits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
	<th>' . _('Credits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>';
		if (isset($_SESSION['Tag'])){
	echo'<th>单元分组 </th>';
	}
	echo '<th>导出标记</th>				
	</tr>';

	$LastJournal = 0;
	$LastType = -1;
	$r=0;
	while ($myrow = DB_fetch_array($resultv)){			
	if ($myrow['transno']!=$LastJournal ) {			
		if ($r==1){
	echo '<tr class="EvenTableRows">';
	$r=0;
	} else {
	echo '<tr class="OddTableRows">';
	$r=1;
	}
	echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
	      <td >' ._('Accounting'). $myrow['transno']. '</td>';

	} else {

	if ($r==1){
	echo '<tr class="EvenTableRows"><td colspan="2"></td>';
	$r=0;
	} else {
	echo '<tr class="OddTableRows"><td colspan="2"></td>';
	$r=1;
	}
	}

	echo '<td >' . $myrow['account'] . '</td>
			<td >' . $myrow['accountname'] . '</td>
			<td>'.$myrow['narrative']. '</td>
			<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord']['decimalplaces'])) . '</td>
			<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord']['decimalplaces']) ). '</td>';

	if ($myrow['transno']!=$LastJournal ){
	if (isset($_SESSION['Tag']))		{
	echo'<td >' . $myrow['tagdescription'] . '</td>';
	}			
	echo '<td class="number"><input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'" checked="true" ></td></tr>';
	//$LastType = $myrow['type'];
	$LastJournal = $myrow['transno'];		
		
	} else {
	echo '<td colspan="1"></td></tr>';
	}

	}	
	echo '</table>';
	} //end if no bank trans in the range to show   checked=true

}elseif (isset($_POST['accresult'])){
	
	$str=explode('-',$_POST['unittag']);	
	$sql="SELECT account FROM gltrans WHERE account NOT IN (SELECT accountcode FROM chartmaster)";
	$result = DB_query($sql);
	$accarr=array();
	while ($row=DB_fetch_array($result)){
          array_push($accarr,$row['account']);
	}
	
		$accstr=implode(',',$accarr);		
		$sql = "SELECT accountcode, accountname, currcode, group_, cashflowsactivity  FROM chartmaster WHERE accountcode IN (".$accstr.")"; 
		$query = mysqli_query($db1,$sql); 
		$row = mysqli_num_rows($query);   
		//var_dump($accarr);
		//prnMsg($accstr.$host. $DBUser. $DBPassword. $comdatabase.$mysqlport);   
		if(count($accarr)==0) {
			prnMsg('凭证科目检验完成，没有需要添加的科目！', 'info');
		} else {
			if ($row==0){
				prnMsg('凭证科目检验完成，异常问题，缺少源科目！[通知系统管理员]', 'info');	
			}else{
			echo '<input type="submit" name="addacc" value="科目添加" />';
			echo '<table class="selection">';
			echo '<tr>	
			<th>' . _('Account Code') . '</th>
			<th>' . _('Account Description') . '</th>
			<th>' . _('Narrative') . '</th>
			<th></th>	
			</tr>';
	$r=0;
	$RowIndex =0;
	mysqli_data_seek($query,0);//指针复位 
	while ($myrow = mysqli_fetch_array($query)){			
		
		if ($r==1){
		echo '<tr class="EvenTableRows">';
		$r=0;
		} else {
		echo '<tr class="OddTableRows">';
		$r=1;
		}
	  echo '<td >' . $myrow['accountcode'] . '</td>
			<td >' . $myrow['accountname'] . '</td>
			<td></td>
			<input type="hidden" name="accname'.$RowIndex.'" value="' .  $myrow['accountcode'].'^'. $myrow['accountname']. '" />
			<td><input type="checkbox" name="chkbx[]" value="'.$RowIndex .'" checked  ></td>	
			</tr>';
			$RowIndex ++;
	}	
	echo '</table>';	
	}
	}

}elseif(isset($_POST['addacc'])){
	
		$rw=0;
		$str='';
	    $rr=1;
		if (count($_POST['chkbx'])>0){
			foreach($_POST['chkbx'] as $val){
				$accarr=explode('^',$_POST['accname'.$val]); 
				$sql="INSERT INTO chartmaster(accountcode, accountname, group_, currcode, cashflowsactivity, tag,  low, used) 
				         SELECT '".$accarr[0]."' accountcode,'".$accarr[1]."' accountname, group_, currcode, cashflowsactivity, tag,  low, used FROM chartmaster WHERE accountcode LIKE   '".substr($accarr[0],0,4)."%' LIMIT 1";
				$result = DB_query($sql);			
				//prnMsg($sql);
				$rw++;			
			}  			
			prnMsg($rw.'笔凭证生成！','info');
	    }else{
			prnMsg('你没有选择！','info');
		}
}elseif(isset($_POST['narrsave'])){
		$rw=0;
		$str='';
	    $rr=1;
		if (count($_POST['chk'])>0){
			foreach($_POST['chk'] as $val){
				$prdtranarr=explode('^',$_POST['prdtranno'.$val]); 
				$sql="UPDATE `gltrans` SET narrative='".$_POST['narrative'.$val]."' WHERE periodno=".$prdtranarr[0]." AND transno=".$prdtranarr[1];
				$result = DB_query($sql);			
				//prnMsg($sql);
				$rw++;			
			}  		
			prnMsg($rw.'笔凭证生成！','info');
	    }else{
			prnMsg('你没有选择！','info');
		}
	
	}



	


echo '</div></form>';
include('includes/footer.php');


   
	
?>