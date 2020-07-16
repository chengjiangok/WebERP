
<?php
/* $Id: Z_CheckGLTransExport.php $*/

/*
 * @Author: ChengJiang 
 * @Date: 2019-06-28 20:36:58 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-06-28 11:16:31
 */
include ('includes/session.php');
$Title = '凭证导出维护';// Screen identificator.
$ViewTopic = 'SpecialUtilities';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Z_ChangeGLAccountCode';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="', $RootPath, '/css/', $Theme,
	'/images/gl.png" title="',// Icon image.
	$Title, '" /> ',// Icon title.
	$Title, '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');

if(isset($_POST['ProcessGLAccountCode'])) {

	$InputError =0;

	$_POST['NewAccountCode'] = mb_strtoupper($_POST['NewAccountCode']);

	/*First check the code exists */
	$result=DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'");
	if(DB_num_rows($result)==0) {
		prnMsg(_('The GL account code') . ': ' . $_POST['OldAccountCode'] . ' ' . _('does not currently exist as a GL account code in the system'),'error');
		$InputError =1;
	}

	if(ContainsIllegalCharacters($_POST['NewAccountCode'])) {
		prnMsg(_('The new GL account code to change the old code to contains illegal characters - no changes will be made'),'error');
		$InputError =1;
	}

	if($_POST['NewAccountCode']=='') {
		prnMsg(_('The new GL account code to change the old code to must be entered as well'),'error');
		$InputError =1;
	}


	/*Now check that the new code doesn't already exist */
	$result=DB_query("SELECT accountcode FROM chartmaster WHERE accountcode='" . $_POST['NewAccountCode'] . "'");
	if(DB_num_rows($result)!=0) {
		echo '<br /><br />';
		prnMsg(_('The replacement GL account code') . ': ' . $_POST['NewAccountCode'] . ' ' . _('already exists as a GL account code in the system') . ' - ' . _('a unique GL account code must be entered for the new code'),'error');
		$InputError =1;
	}


	if($InputError ==0) {// no input errors
		$result = DB_Txn_Begin();
		echo '<br />' . _('Adding the new chartmaster record');
		$sql = "INSERT INTO chartmaster (accountcode,
										accountname,
										group_,crtdate,
										currcode)
				SELECT '" . $_POST['NewAccountCode'] . "',
					accountname,
					group_,'".date("Y-m-d")."',
					currcode
				FROM chartmaster
				WHERE accountcode='" . $_POST['OldAccountCode'] . "'";

		$DbgMsg = _('The SQL statement that failed was');
		$ErrMsg =_('The SQL to insert the new chartmaster record failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		DB_IgnoreForeignKeys();

		ChangeFieldInTable("bankaccounts", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("bankaccountusers", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("banktrans", "bankact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("chartdetails", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("cogsglpostings", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("companies", "debtorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "pytdiscountact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "creditorsact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "payrollact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "grnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "exchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "purchasesexchangediffact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "retainedearnings", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("companies", "freightact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("fixedassetcategories", "costact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "depnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "disposalact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("fixedassetcategories", "accumdepnact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("glaccountusers", "accountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		
		ChangeFieldInTable("gltrans", "account", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("lastcostrollup", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("lastcostrollup", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("locations", "glaccountcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);// Location's ledger account.

		ChangeFieldInTable("pcexpenses", "glaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("pctabs", "glaccountassignment", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("pctabs", "glaccountpcash", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("purchorderdetails", "glcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("salesglpostings", "discountglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("salesglpostings", "salesglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("stockcategory", "stockact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "adjglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "issueglact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "purchpricevaract", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "materialuseagevarac", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("stockcategory", "wipact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("taxauthorities", "taxglcode", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("taxauthorities", "purchtaxglaccount", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);
		ChangeFieldInTable("taxauthorities", "bankacctype", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		ChangeFieldInTable("workcentres", "overheadrecoveryact", $_POST['OldAccountCode'], $_POST['NewAccountCode'], $db);

		DB_ReinstateForeignKeys();

		$result = DB_Txn_Commit();

		echo '<br />' . _('Deleting the old chartmaster record');
		$sql = "DELETE FROM chartmaster WHERE accountcode='" . $_POST['OldAccountCode'] . "'";
		$ErrMsg = _('The SQL to delete the old chartmaster record failed');
		$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
		echo ' ... ' . _('completed');

		echo '<p>' . _('GL account Code') . ': ' . $_POST['OldAccountCode'] . ' ' . _('was successfully changed to') . ' : ' . $_POST['NewAccountCode'];
	}//only do the stuff above if  $InputError==0

}

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .  '" method="post">';
echo '<div class="centre">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<div class="page_help_text">
	功能简介：自动筛选出顺序排号中缺少的凭证！</br>
		
		</div>';
echo '<br />
	<table>';
echo'<tr>	<td>' . _('For Period range').':</td>
	<td ><select name="selectprd" size="1" >';					
		if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
		$sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
		}else{
		$sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".(floor($_SESSION['startperiod']/12)*12-23 )."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
		}
		$result = DB_query($sql); 
		while ($myrow=DB_fetch_array($result,$db)){	
		if(isset($_POST['selectprd']) AND $myrow['periodno']==$_POST['selectprd']){	
		echo '<option selected="selected" value="';
		} else {
		echo '<option value ="';
		}
		echo   $myrow['periodno']. '">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
		}
		echo '</select></td></tr>';

	/*echo'<tr>
		<td>' . _('Existing GL Account Code') . ':</td>
		<td><input type="text" name="OldAccountCode" size="20" maxlength="20" /></td>
	</tr>
	<tr>
		<td>' . _('New GL Account Code') . ':</td>
		<td><input type="text" name="NewAccountCode" size="20" maxlength="20" /></td>
	</tr>*/
	echo'</table>
		<input type="submit" name="CheckGLNo" value="检验凭证号顺序" />
	
		
		<input type="submit" name="CheckTrans" value="凭证检验" />
		</div>';
		//<input type="submit" name="CheckGLtrans" value="导出凭证检验" />
		//	<input type="submit" name="CheckTransNo" value="检验凭证号顺序" />
	//	<input type="submit" name="ProcessGLAccountCode" value="' . _('Process') . '" />
	
	/*#有凭证转录
				SELECT count(*) FROM  zsc_hwgzsc.gltrans WHERE periodno=@period AND transno=@transno;
				
				SELECT gltID, transno, periodno, intono, printno, intoprintno, intoperiod, tag, flg FROM gltransimport WHERE periodno=@period AND intono=@transno;
				UPDATE invoicetransSET transno=@transno_new  WHERE period=@period AND transno=@transno;
				update currtrans set transno=@transno_new WHERE period=@period AND transno=@transno;
				
				update debtortrans SEt  transno=@transno_new  WHERE prd=@period AND transno=@transno;
				update banktransactionSET transno=@transno_new WHERE period=@period AND transno=@transno;
				#凭证转录
				UPDATE zsc_hwgzsc.gltransimport SET intono=@transno_new WHERE periodno=@period AND intono=@transno;
				SET @period=50;
				SET @transno=39;
				SET @transno_new=9; 

				#有凭证转录
				SELECT transno,narrative,REPLACE(narrative,@transno,@transno_new) FROM  zsc_hwgzsc.gltrans WHERE periodno=@period AND transno=@transno;
				SELECT gltID, transno, periodno, intono, printno, intoprintno, intoperiod, tag, flg FROM gltransimport WHERE periodno=@period AND intono=@transno;*/ 
        $sql="SELECT  conftype, confvalue FROM myconfig WHERE confname='unittag'";
        $result=DB_query($sql);
        $row=DB_fetch_assoc($result);
        //var_dump($row);
        if ($row['conftype']==0){//转入
            $database=$row['confvalue'];
            $unittype=0;
            $db1 = mysqli_connect($host , $DBUser, $DBPassword, $database, $mysqlport);
                    mysqli_set_charset($db1, 'utf8');
        }
    if (isset($_GET['changtrans'])){
		prnMsg('补号');
		//改号
		$sql="update gltrans SEt transno=@transno_new WHERE periodno=@period AND transno=@transno";
	}elseif (isset($_GET['JNL'])){
        prnMsg('需改摘要及号码');
	}
	$dt=$_SESSION['lastdate'];
if(isset($_POST['CheckTransNo'])){
	
	$sql="SELECT DISTINCT  transno,printno FROM gltrans WHERE periodno= ".$_POST['selectprd'];
	//prnMsg($sql);
	$result = DB_query($sql); 
	$count=DB_num_rows($result);
	//$EndTransNo=GetTransNo($_POST['selectprd'], $db);
	
	$TransArr=array();
	$no=0;
	while($row=DB_fetch_array($result)){
		$no++;
		if ($no!=$row['transno']){	
			$TransArr[]=array($no,0);
		//	$TransArr[]=array($key,$row['printno']);
			$no++;
			if ($no!=$row['transno']){
				for ($n=$no;$n<=$row['transno'];$n++){
					$TransArr[]=array($no+$n,0);					
				}
				$no+=$n;
			}
		}

	}
	prnMsg('凭证最末号'.($count+count($TransArr)).',共计 '.$count.' 笔凭证!');
	
	//echo count($TransArr);
	if (count($TransArr)>0){
		echo	'	<br />
		<table cellpadding="2">';
	echo '<tr>
			<th colspan="4">缺号凭证</th>
		  </tr>
	      <tr>
	  		<th>序号</th>
			<th>' . _('Voucher No') . '</th>					
			<th>打印号</th>
			<th>操作</th>
			</tr>';
	for($i=0 ;$i<count($TransArr);$i++ ){
	
			if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			}else{
				echo '<tr class="OddTableRows">';
				$r=1;
			}
			echo '<td>' . ($i+1) .'</td>
				  <td>记字'.$TransArr[$i][0]. '</td>';
				
			echo '<td >' . $TransArr[$i][1] . '</td>
				   <td class="number"><a href="Z_CheckGLTransExport.php?changtrans='.$_POST['selectprd'].'^'.$TransArr[0].'">补号</a></td>
				   </tr>';

	}
	echo'</table>';
	}else{
		prnMsg('本月凭证顺序号没有缺号,共计'.$no.'张凭证.','info');
	}
	
}elseif($_POST['CheckGLtrans']){
	prnMsg('凭证内容检查');
    //	$sql="SELECT  conftype, confvalue FROM myconfig WHERE confname='unittag'";
    //	$result=DB_query($sql);
    //	$row=DB_fetch_assoc($result);
        //var_dump($row);
	if ( $unittype==0){//导入单位
	//	$database=$row['confvalue'];
      //  $db1 = mysqli_connect($host , $DBUser, $DBPassword, $database, $mysqlport);
	//		   mysqli_set_charset($db1, 'utf8');
		$sql="SELECT  transno, periodno, intono, printno, intoprintno, intoperiod, tag, flg FROM ".$database.".gltransimport WHERE periodno=".$_POST['selectprd'];
		$query = mysqli_query($db1,$sql); 
		//prnMsg($sql);
		$row = mysqli_fetch_array($query);	
		//var_dump($host.'-'.$DBUser.'='.$DBPassword.'='. $database.'='. $mysqlport);
		echo'<br />
		<table class="selection">';
	echo '<tr>
			<th colspan="8">导出凭证查看</th></tr>';
	echo '<tr>	
			<th>会计期间</th>
			<th>导入凭证号</th>
			<th>导入打印凭证号</th>
			<th>' . _('Voucher No') . '</th>		
			<th>打印凭证号</th>			
			<th>凭证金额</th>	
			<th>备注</th>
			<th>操作</th>				
		</tr>';

	$r=0;

	$intogl=array();
	$glarr=array();
	$glimportarr=array();
	while ($row = mysqli_fetch_array($query) ){
		$glimportarr[$row['intono']]=array('periodno'=>$row['periodno'], 'transno'=>$row['transno'], 'printno'=>$row['printno'], 'intoprintno'=>$row['intoprintno'], 'intoperiod'=>$row['intoperiod'], 'tag'=>$row['tag'] );
	}
	//var_dump($glimportarr);
	  foreach($glimportarr as $key=>$val){
		  //导出单位
		$SQL="SELECT transno, trandate, account,accountname, narrative, amount, flg FROM ".$database.".gltrans LEFT JOIN ".$database.".chartmaster ON account=accountcode WHERE periodno= ".$val['periodno'] ." AND transno=" .$val['transno'] ;
		$query1=mysqli_query($db1,$SQL);
		$totalamo=0;
		$chkstr="";
		while ($row=mysqli_fetch_array($query1)){
			$glarr[$key][]=array($row['trandate'],$row['account'],$row['accountname'],$row['narrative'],$row['amount'],$row['flg'],0);
		    if ($row['amount']>0){
				$totalamo+=$row['amount'];
			}
		}
	//if (strstr($glarr[3],'[')){
		    $narrstr=substr($glarr[$val['transno']][0][3],0,strpos($glarr[$val['transno']][0][3],'['));
		//	$getTransno=strstr($glarr[$val['transno']][0][3],'凭证号:');
		
	//	}
	    //导入单位
		$SQL="SELECT transno, trandate, account,accountname, narrative, amount, flg FROM gltrans LEFT JOIN chartmaster ON account=accountcode WHERE periodno= ".$val['periodno'] ." AND transno=" .$key." ORDER BY periodno,transno";
		$query=DB_query($SQL);
	
		//prnMsg(DB_num_rows($query));
		$intotalamo=0;
		while ($row=DB_fetch_array($query)){
			if ($row['amount']>0){
				$intotalamo+=$row['amount'];
			}
			$intogl[$row['transno']][]=array($row['trandate'],$row['account'],$row['accountname'],$row['narrative'],$row['amount'],$row['flg'],0);
        }
        if (count($glarr)<1){  //导出不存在
			$chkstr.="导出凭证不存在<br>";
		}else{
            if (count($intogl)<1){//导入不存在
                $chkstr.="导入凭证不存在<br>";
            }else{
        
                $intonarrstr=$intogl[$key][0][3];
                if (trim($intonarrstr)!=trim($narrstr)){
                    $chkstr=$narrstr.'<br>'.$intonarrstr.'<br>';
                }
                if (round((float)$intotalamo,2)==round((float)$totalamo,2)){
                    $flagamo="";
                }else{
                    $flagamo="差:".(round((float)$intotalamo,2)-round((float)$totalamo,2));
                }
            }
        }
        $m=$val['periodno']-$_SESSION['period'] ;
		$m1=$val['intoperiod']-$_SESSION['period'] ;
		$prd=date("Y-m",strtotime("$dt +$m month"));

		$intoprd=date("Y-m",strtotime("$dt +$m1 month"));
		$glprdstr=url_encode($val['periodno'] ."^" .$val['transno'] .'^'.$val['intoperiod'] ."^" .$key);
		//导出凭证
		$glstr=url_encode(json_encode( $glarr[$val['transno']]));
		//导入凭证
		$iglstr=url_encode(json_encode( $intogl[$key]));
	
		if ($r==1){
			echo '<tr class="EvenTableRows">';
			$r=0;
		} else {
			echo '<tr class="OddTableRows">';
			$r=1;
		}
	
	
		echo '	<td >'.count($glarr) .'['. $intoprd.']'.count($intogl).'</td>
		        <td >'. $key . '</td>
				<td >' . $val['intoprintno']. '</td>
				<td >'.$val['transno'] . '</td>
				<td>'  .$val['printno']. '</td>
				<td >' .$flagamo.$totalamo. '</td>
				<td >' . $chkstr. '</td>
				<td ><a href="' . $RootPath . '/Z_CheckGLTransExport.php?JNL='.$glprdstr.'&gl='.$glstr.'$igl='.$iglstr.'"  target="_blank">更新</a></td>
                </tr>';
                unset($glarr);
                unset($intogl);
		$RowIndex = $RowIndex + 1;
	}
	echo '</table>';
	
	
	
	}



}elseif(isset($_POST['CheckGLNo'])){
	
	$sql="SELECT DISTINCT  transno,printno FROM gltrans WHERE periodno= ".$_POST['selectprd']." ORDER BY transno";

	$result = DB_query($sql); 
	$TransArr=array();

	while($row=DB_fetch_array($result)){
		$TransArr[$row['transno']]=array($row['transno'],$row['printno']);
	}
	$endTransno = end($TransArr);

	prnMsg('凭证最末号'.($endTransno[0]).',共计 '.count($TransArr).' 笔凭证!');

	if ($endTransno>0){
		echo	'	<br />
		<table cellpadding="2">';
	echo '<tr>
			<th colspan="4">缺号凭证</th>
		  </tr>
	      <tr>
	  		<th>序号</th>
			<th>凭证号:记</th>					
			<th>打印号</th>
			<th>操作</th>
			</tr>';
	for($i=1 ;$i<=$endTransno[0];$i++ ){
	
			if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			}else{
				echo '<tr class="OddTableRows">';
				$r=1;
			}
			echo '<td>' . ($i) .'</td>';
			if (isset( $TransArr[$i])){
				 echo'<td> '.$i. ' </td>
				 <td >' . $TransArr[$i][1] . '</td>';
			}else{
				echo'<td></td>
					<td></td>';
			}
				
			echo '
				   <td class="number"><a href="Z_CheckGLTransExport.php?changtrans='.$_POST['selectprd'].'^'.$TransArr[0].'">补号</a></td>
				   </tr>';

	}
	echo'</table>';
	}else{
		prnMsg('本月凭证顺序号没有缺号,共计'.$no.'张凭证.','info');
	}
	
}elseif(isset($_POST['CheckTrans'])){
	//新版
	$sql="SELECT DISTINCT  transno,printno FROM gltrans WHERE periodno= ".$_POST['selectprd']." ORDER BY transno";

	$result = DB_query($sql); 
	$TransArr=array();

	while($row=DB_fetch_array($result)){
		$TransArr[$row['transno']]=array($row['transno'],$row['printno']);
	}
	$endTransno = end($TransArr);

	prnMsg('凭证最末号'.($endTransno[0]).',共计 '.count($TransArr).' 笔凭证!');
    if ( $unittype==0){//导入单位
			$sql="SELECT  transno, periodno, intono, printno, intoprintno, intoperiod, tag, flg FROM ".$database.".gltransimport WHERE periodno=".$_POST['selectprd'];
			$query = mysqli_query($db1,$sql); 
			//prnMsg($sql);
		//	$row = mysqli_fetch_array($query);	
	
		$r=0;
		$dt=$_SESSION['lastdate'];
		$intogl=array();
		$glarr=array();
		$glimportarr=array();
		while ($row = mysqli_fetch_array($query) ){
			$glimportarr[$row['intono']]=array('periodno'=>$row['periodno'], 'transno'=>$row['transno'], 'printno'=>$row['printno'], 'intoprintno'=>$row['intoprintno'], 'intoperiod'=>$row['intoperiod'], 'tag'=>$row['tag'] );
		}
	}
	//var_dump($glimportarr);
	if ($endTransno>0){
		$m=end($glimportarr)['periodno']-$_SESSION['period'] ;
		//prnMsg($glimportarr[$endTransno]['periodno'].'-'.$_SESSION['period'] );
		//$m1=$glimportarr[$endTransno]['periodno']-$_SESSION['period'] ;
		$prd=date("Y-m",strtotime("$dt +$m month"));
		echo	'	<br />
		<table cellpadding="2">';
		echo '<tr>
		<th colspan="10">会计期间'.$prd.'</th>
	  </tr>';
	echo '<tr>
			<th colspan="4">导入凭证</th>
			<th colspan="3">导出凭证</th>
			<th colspan="3"></th>
		  </tr>';
		  echo '<tr>	
					<th>序号</th>
					<th>凭证号</th>
					<th>打印号</th>
					<th>导入检查</th>
					<th>' . _('Voucher No') . '</th>		
					<th>打印号</th>
					<th>凭证检查</th>			
					<th>凭证金额</th>	
					<th>摘要检查</th>
					<th>操作</th>				
				</tr>';
	for($i=1 ;$i<=$endTransno[0];$i++ ){
		if (!isset( $TransArr[$i])){
			echo '<tr style="background: #efc;">'	;
		}else{
			if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
			}else{
				echo '<tr class="OddTableRows">';
				$r=1;
			}
		}
			  //导出单位
			$SQL="SELECT transno, trandate, account,accountname, narrative, amount, flg FROM ".$database.".gltrans LEFT JOIN ".$database.".chartmaster ON account=accountcode WHERE periodno= ".$glimportarr[$i]['periodno'] ." AND transno=" .$glimportarr[$i]['transno']  ;
			$query1=mysqli_query($db1,$SQL);
			$totalamo=0;
			$chkstr="";
			while ($row=mysqli_fetch_array($query1)){
				$glarr[$glimportarr[$i]['transno'] ][]=array($row['trandate'],$row['account'],$row['accountname'],$row['narrative'],$row['amount'],$row['flg'],0);
				if ($row['amount']>0){
					$totalamo+=$row['amount'];
				}
			}
			$narrstr=substr($glarr[$glimportarr[$i]['transno']][0][3],0,strpos($glarr[$glimportarr[$i]['transno']][0][3],'['));
			$getTransNo=strstr($glarr[$glimportarr[$i]['transno']][0][3],'凭证号:');
			//导入单位
		if (isset($glimportarr[$i])){
			$SQL="SELECT transno, trandate, account,accountname, narrative, amount, flg FROM gltrans LEFT JOIN chartmaster ON account=accountcode WHERE periodno= ".$glimportarr[$i]['periodno'] ." AND transno=" .$i." ORDER BY periodno,transno";
			$query=DB_query($SQL);
		
			//prnMsg(DB_num_rows($query));
			$intotalamo=0;
			while ($row=DB_fetch_array($query)){
				if ($row['amount']>0){
					$intotalamo+=$row['amount'];
				}
				$intogl[$i][]=array($row['trandate'],$row['account'],$row['accountname'],$row['narrative'],$row['amount'],$row['flg'],0);
			}
			
			//摘要 金额核对		
			$intonarrstr=$intogl[$i][0][3];
			if (trim($intonarrstr)!=trim($narrstr)){
				$narrerr=1;
				$chkstr=$narrstr.'<br>'.$intonarrstr.'<br>';
			}else{
				$chkstr=$intonarrstr;	
				$narrerr=0;
			}
			if (isset( $TransArr[$i])){
				if ($getTransNo==$i){
					$narrerr=3;
                    $chkstr.='导入凭证号['.$i.'] 和摘要提取凭证号['.$getTransNo.']不一样<br>';
				}

			}
			if (round((float)$intotalamo,2)==round((float)$totalamo,2)){
				$flagamo=0;
			}else{
				
				$flagamo=(round((float)$intotalamo,2)-round((float)$totalamo,2));
			}
				
			
		}
			echo '<td>' . ($i) .'</td>';
			if (isset( $TransArr[$i])){//凭证存在
				 echo'<td >'.$i. ' </td>
					  <td >'. $TransArr[$i][1] . '</td>';
				if (isset($glimportarr[$i])){
					
					echo '<td>导入</td>';	
				}else{	
					echo '<td>自制</td>';
				}
			}else{
				echo'<td></td>
					 <td></td>';
			   if (isset($glimportarr[$i])){
				   echo '<td>缺错号</td>';
				   $intoerr=1;	
			   }else{
				   echo '<td>缺号</td>';	
			   }			
			}			
			echo'<td >'.$glimportarr[$i]['transno'] . '</td>
				 <td >'.$glimportarr[$i]['printno'] . '</td>';
			if (count($glarr)<1){  //导出不存在
				echo'<td>缺凭证</td>';
			}else{
				echo'<td></td>';
			}
			if ($flagamo==0){
				echo'<td >' .$totalamo. '</td>';
			}else{
				if ($intoerr==1){
					echo'<td title="导入缺号">' .$totalamo. '</td>';
				}else{
					echo'<td >差:' .$flagamo. '</td>';
				}
			}
			if ($narrerr==0){
				echo'<td title="' . $chkstr. '" >正确</td>';
			}else{
				echo'<td title="' . $chkstr. '" >错误</td>';	
			}
			echo '<td class="number"><a href="Z_CheckGLTransExport.php?changtrans='.$_POST['selectprd'].'^'.$TransArr[0].'">补号</a></td>
				   </tr>';

	}
	echo'</table>';
	}else{
		prnMsg('本月凭证顺序号没有缺号,共计'.$no.'张凭证.','info');
	}
	
}
	echo'</form>';

include('includes/footer.php');
?>