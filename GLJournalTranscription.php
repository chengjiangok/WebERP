
<?php
/*$ID GLJournalTranscription.php$*/
/*
* @Author: chengjang 
* @Date: 2017-01-31 17:02:10 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-11-14
*/
//include('includes/DefineJournalClass.php');  
include ('includes/session.php');
$Title = '凭证导出分组';

$ViewTopic='MyTools';
$BookMark = 'GLTranscription';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

if (!isset($_POST['ERPPrd'])){
$_POST['ERPPrd']= $_SESSION['period'];
}
if (!isset($_POST['UnitsTag'])){
    $_POST['UnitsTag']=1;
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<input type="hidden" name="ERPPrd" value="' .$_POST['ERPPrd'] . '" />
	  <input type="hidden" name="UnitsTag" value="' .$_POST['UnitsTag'] . '" />';		
		
echo '<div><table class="selection">';
echo '<tr>
		<th colspan="2">' . _('Selection Criteria') . '</th>
		</tr>';

echo '<tr>
		<td>导出分组选择</td>
		<td>';
		SelectUnitsTag(2);	
echo'</td>
	</tr>';	
echo '<tr>
		<td>会计期间选择:</td>
		<td>';
			SelectPeriod($_SESSION['period'],$_SESSION['janr']);
echo '</td>
	 </tr>
	 </table>';
if(isset($_POST['Go1']) OR isset($_POST['Go2'])||isset($_POST['crtExcel'])	) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}	
	
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
//导出到数据库
$Dbase=$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['dbase'];	 
$db1 = mysqli_connect($host , $DBUser, $DBPassword, $Dbase, $mysqlport);
		mysqli_set_charset($db1, 'utf8');
	
if (!isset($_POST['Show']) OR $showflg==0){
	echo '<br />
		<div class="centre">
			<input type="submit" name="Show" value="待导出凭证" />	             
			<input type="submit" name="TranscriptionQuery" value="已导出凭证" />';
	echo'</div>';

}
if (isset($_POST['transcription'])) {	
	 //导出凭证操作
	$k=0;
	$SQL="SELECT `confname`,  `confvalue` FROM `myconfig` WHERE conftype=99 AND tag='".$_POST['UnitsTag']."'";
	$Result=DB_query($SQL);
	$ToAccounts=array();
	while ($row=DB_fetch_array($Result)){
       $ToAccounts[$row['confname']]=$row['confvalue'];
	}
	
	foreach( $_POST['chkbx'] as $value){
		$k++;
		$sql="SELECT gltrans.trandate,
		            	gltrans.type,	
						gltrans.transno,					
						gltrans.periodno,
						gltrans.chequeno,
						gltrans.printno,
						gltrans.tag	,
						gltrans.account,
						chartmaster.accountname,
						chartmaster.currcode,
						chartmaster.group_,
						chartmaster.cashflowsactivity,
						chartmaster.group_,
						gltrans.narrative,
						gltrans.amount,
						gltrans.flg						          
						FROM gltrans
						INNER JOIN chartmaster
						ON gltrans.account=chartmaster.accountcode	
						WHERE  gltrans.transno='".$value."'
						 AND  gltrans.tag= ".$_POST['UnitsTag']."
						  AND gltrans.periodno='" .$_POST['ERPPrd']."'	
						ORDER BY gltrans.transno";
			$result = DB_query($sql);
			$ROW=DB_fetch_row($result);
			$trandt=$ROW[0];
			$gltype=$ROW[1];
			$gltransno=$ROW[2];
			$prdno=$ROW[3];
			$chequeno=$ROW[4];
			$prtno=$ROW[5];
			$gltag=$ROW[6];
			$narrative=$ROW[13];
			$gltypeno=GetTypeNo1($gltype,$prdno,$db1);
			$transno=GetTransNo1($prdno,$db1);
			$r=0;
			$i=0;	
			mysqli_data_seek($result,0);//指针复位 
			mysqli_query($db,'SET autocommit=0');
	    	mysqli_query($db,'START TRANSACTION');
	    
	while ($myrow=DB_fetch_array($result)){
		//$query=DB_Txn_Begin(); 	
		mysqli_query($db1,'SET autocommit=0');
		mysqli_query($db1,'START TRANSACTION');
		  //得到外账科目
		if (isset($ToAccounts[$myrow['account']])){
			$Act=$ToAccounts[$myrow['account']];
		}else{
			$Act=$myrow['account'];
		}
		//$act=AccountCheck($myrow['account'],$_POST['UnitsTag']);
		$sql = "SELECT count(*) FROM chartmaster WHERE accountcode='".$Act."'"; 
		$query = mysqli_query($db1,$sql); 
		$row = mysqli_fetch_row($query);	
		if($row[0]==0){
		//insert into new  subject
			$sql="INSERT INTO chartmaster(accountcode ,
										accountname ,
										group_ ,
										currcode, 
										cashflowsactivity,
										tag,
										crtdate,
											low,
											used) 
										VALUES ('".$Act."',
										'".$myrow['accountname']."',
										'".$myrow['group_']."',
										'".$myrow['currcode']."',
										'".$myrow['cashflowsactivity']."',
										'".$myrow['tag']."',
										'".date("Y-m-d")."',
										0,0)";
				$query1 = mysqli_query($db1,$sql); 
     
		}
	   
		 $r++;
	   $sql="INSERT INTO gltrans(type,
		                        transno, 
								typeno,
								printno,
								prtchk,
								chequeno, 
								trandate, 
								periodno, 
								account, 
								narrative, 
								amount, 
								posted, 
								jobref,
								tag,
								userid,
								flg)
						VALUES ('".$gltype."',
								'".$transno."',
								'".$gltypeno."',
								'0',
								'0',
								'".$chequeno."',
								'".$trandt."',
								'".$prdno."',
								'".$Act."',
								'".$myrow['narrative']."',
								'".$myrow['amount']."',
								'0',
								'0',
								'".$myrow['tag']."',
								'auto',
								'".$myrow['flg']."')";
		$query1= mysqli_query($db1,$sql); 
			if ($query1){
				$i++;
			}
		
		} // end while
		
	if ($i==$r){//掺入标记库
	
		$sql="INSERT INTO  gltransimport (transno,
											intono,
											printno,
											intoprintno,
											periodno,
											intoperiod,
												tag)
											VALUES ('".$gltransno."',
													'".$transno."',
													'".abs($prtno)."',
													'0',
													'".$prdno."',
													'".$prdno."',
													'".$gltag."')";
			$query=mysqli_query($db,$sql);
			//prnMsg($sql);
				//更新导出凭证
		$sql="UPDATE gltrans SET  narrative= '".$narrative."[".date_format(date_create($trandt),'Y-m').'打印号:'.abs($prtno).' 凭证号:'.$transno.']'."' 
		        WHERE periodno='".$prdno."' 				
				AND transno='".$gltransno."'";	
		$query=mysqli_query($db,$sql);
			
		if ($query){
		//$query=DB_Txn_Commit();
			mysqli_query($db,'commit');
			mysqli_query($db,'SET autocommit=1');
			mysqli_query($db1,'commit');
			mysqli_query($db1,'SET autocommit=1');	
			
		}	
	
	} 

  }//row101 foreach end 
		if ($k>0){
			unset($_POST['chkbx']);
			mysqli_free_result($result);
			prnMsg('导出凭证 '.$k.' 张','info');
		}
}elseif (isset($_POST['Show'])) {

	$transno=0;
	$sql="SELECT  transno FROM gltransimport WHERE tag=".$_POST['UnitsTag']." AND  periodno='" .$_POST['ERPPrd']."'";

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
				gltrans.tag
			FROM gltrans
			INNER JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode	
			LEFT JOIN systypes
			ON gltrans.type=systypes.typeid
			
			WHERE  gltrans.tag= ".$_POST['UnitsTag']." and gltrans.periodno='" .$_POST['ERPPrd']."' 
			AND gltrans.transno NOT IN (SELECT  transno FROM gltransimport WHERE tag=".$_POST['UnitsTag']."
			AND  periodno='" .$_POST['ERPPrd']."')
			ORDER BY gltrans.transno, gltrans.type,gltrans.typeno";

	$resultv = DB_query($sqlv);
	$showflg=DB_num_rows($resultv);
	if($showflg==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	} else {
		echo'<input type="submit" name="transcription" value="导出更新" />';

		echo '<table class="selection">';
		echo '<tr>
				<th>' . _('Date') . '</th>
				<th>' . _('Voucher No') . '</th>
				<th>' . _('Account Code') . '</th>
				<th>' . _('Account Description') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Debits').' '.$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['currencydefault'] . '</th>
				<th>' . _('Credits').' '.$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['currencydefault'] . '</th>';
	
			echo'<th>单元分组 </th>
			   <th>导出标记</th>				
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
					<td >记字'. $myrow['transno']. '</td>';

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
					<td class="number">' . isZero(locale_number_format($myrow['Debits'],POI)) . '</td>
					<td class="number">' . isZero(locale_number_format($myrow['Credits'],POI) ). '</td>';

			if ($myrow['transno']!=$LastJournal ){
				//if (isset($_SESSION['Tag']))		{
					echo'<td >' . $_SESSION['CompanyRecord'][$myrow['tag']]['unitstab'] . '</td>';
				//}			
				echo '<td class="number">
						  <input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'" checked="true" ></td>
						  </tr>';
				//$LastType = $myrow['type'];
				$LastJournal = $myrow['transno'];		
				
			} else {
				echo '<td colspan="1"></td></tr>';
			}

		}	
		echo '</table>';
	} //end if no bank trans in the range to show   checked=true

}elseif (isset($_POST['TranscriptionQuery'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {

	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}

	$sql="SELECT  periodno,transno, intono,intoperiod, printno,intoprintno 
	       FROM gltransimport 
		   WHERE tag='".$_POST['UnitsTag']."' 
		   AND periodno='".$_POST['ERPPrd']."'";
	$result = DB_query($sql,$db);
	$ListCount=DB_num_rows($result);		
	if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		
	} else {
		
    	echo '<div>';
	
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
		echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
		if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
		echo '<select name="PageOffset1">';
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
			<input type="submit" name="Go1" value="' . _('Go') . '" />
			<input type="submit" name="Previous" value="' . _('Previous') . '" />
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}
	$RowIndex = 0;
	
 	if (DB_num_rows($result) <> 0) {
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}
	$dt=$_SESSION['lastdate'];
	
	echo'<br />
		<table class="selection">';
	echo '<tr>
			<th colspan="6">导出凭证查看</th></tr>';
	echo '<tr>	
			<th>导入期间-凭证号</th>
			<th>导入打印凭证号</th>
			<th>期间-' . _('Voucher No') . '</th>		
			<th>打印凭证号</th>			
			<th>凭证分类</th>				
		</tr>';

	$r=0;
	
	$intogl=array();
	$glarr=array();
	while ($myrow = DB_fetch_array($result)  AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
		
		$SQL="SELECT transno, trandate, account,accountname, narrative, amount, flg 
		      FROM gltrans
			   LEFT JOIN chartmaster ON account=accountcode 
			   WHERE periodno= ".$myrow['periodno'] ." AND transno=" .$myrow['transno'] ;
		$query=DB_query($SQL);	
	
		while ($row=DB_fetch_array($query)){
			$glarr[$myrow['transno']][]=array($row['trandate'],$row['account'],$row['accountname'],$row['narrative'],$row['amount'],$row['flg'],0);
		}
		$glcount=count($glarr[$myrow['transno']]);
		$SQL="SELECT transno, trandate, account,accountname, narrative, amount, flg 
		      FROM gltrans 
			  LEFT JOIN chartmaster ON account=accountcode 
			  WHERE periodno= ".$myrow['intoperiod'] ." AND transno=" .$myrow['intono'] ;
		$query1=mysqli_query($db1,$SQL);
		//prnMsg(mysqli_num_rows($query1).'!='.$glcount);
        if (mysqli_num_rows($query1)!=$glcount){
			$glarr[$myrow['transno']][][6]=1;
			$chk=1;
		}else{
			while ($row=mysqli_fetch_array($query1)){
			//,MYSQLI_ASSOC)){
				//prnMsg($row['trandate']);
				array_push($intogl[$myrow['transno']],$row);
			}
			$chk=0;
	    }

		if ($r==1){
			echo '<tr class="EvenTableRows">';
			$r=0;
		} else {
			echo '<tr class="OddTableRows">';
			$r=1;
		}
		$m=$myrow['periodno']-$_SESSION['period'] ;
		$m1=$myrow['intoperiod']-$_SESSION['period'] ;
		$prd=date("Y-m",strtotime("$dt +$m month"));

		$intoprd=date("Y-m",strtotime("$dt +$m1 month"));
		$glprdstr=url_encode($myrow['periodno'] ."^" .$myrow['transno'] .'^'.$myrow['intoperiod'] ."^" .$myrow['intono']);
		$glstr=url_encode(json_encode( $glarr[$myrow['transno']]));
		$iglstr=url_encode(json_encode( $intogl[$myrow['transno']]));
		echo '	<td ><a href="' . $RootPath . '/PDFJournals.php?Jnl='.$glprdstr.'&gl='.$glstr.'$igl='.$iglstr.'"  target="_blank">[' . $intoprd.'] '. $myrow['intono'] . '</a></td>
				<td >' . $myrow['intoprintno']. '</td>
				<td >['.$prd .'] ' .$myrow['transno'] . '</td>
				<td>'  .$myrow['printno']. '</td>
				<td >' . $chk. '</td>
				</tr>';
		$RowIndex = $RowIndex + 1;
	}
	echo '</table>';
		if (isset($ListPageMax) AND  $ListPageMax > 1) {
			echo '<div class="centre"><br />&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
			echo '<select name="PageOffset2">';
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
					<input type="submit" name="Go2" value="' . _('Go') . '" />
					<input type="submit" name="Previous" value="' . _('Previous') . '" />
					<input type="submit" name="Next" value="' . _('Next') . '" />';
				echo '</div>';
		}
		//var_dump($glarr);
	}
}

echo '</div></form>';
include('includes/footer.php');

//function AccountCheck($act,$tag){
	/*凭证导出的科目规则改变20191114
	函数作废
	   原科目导出,有预设科目按预设科目导出*/
	/*科目自动生成
	  5001','5101', '6001', '6051', '6401', '6402', '6403', '6601', '6602','2211', '1403', '1405', '1601', '1602'
	  自动变成六位
		以上科目以外的科目按系统设置（gljounalsettle WHERE gltype=20 ）的科目生成
		科目转换需要重新设置20190520
	*/
	/*
	$actarr=array('5001','5101', '6001', '6051', '6401', '6402', '6403', '6601', '6602','2211', '1403', '1405', '1601', '1602' );
	if ($TranscriptionType==0 && in_array(substr($act,0,4),$actarr)){
		//费用科目按系统默认改变一级科目不变+后2位
			//上面科目截取前四位+后2位
			$act=substr($act,0,4).substr($act,-2,2);
	}else{
		
		//按预设科目改变
	$sql="SELECT  account, toaccount FROM gljounalsettle WHERE gltype=20 AND tagid=".$tag;
	$result = DB_query($sql);
	while ($myrow = DB_fetch_array($result)){
		if($myrow['account']==$act){
			$act=$myrow['toaccount'];
		}
	}
		  
	return $act;
}*/
function GetTypeNo1 ($type,$prdno, &$db1){
	$sql = "SELECT max(typeno) FROM gltrans  WHERE  periodno= '" . $prdno . "' and type='".$type."'";

	$ErrMsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
	$Result = mysqli_query($db1,$sql);
	$myrow = mysqli_fetch_array($Result);	
	$trnno=1;
	if($Result){
		$trnno=$myrow[0] + 1;
	}
	return $trnno;
}
function GetTransNo1 ($prdno, &$db1){

	$sql = "SELECT max(transno)  transno FROM gltrans  WHERE  periodno= '" . $prdno . "'";
	$Errsg = _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': <BR>' . _('The next transaction number could not be retrieved from the database because');
	$DbgMsg =  _('The following SQL to retrieve the transaction number was used');
	$Result = mysqli_query($db1,$sql);
	$myrow = mysqli_fetch_array($Result);	
	$trnno=1;
	if($Result){
		$trnno=$myrow[0] + 1;
	}
	return $trnno;
}

   
	
?>