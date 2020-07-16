<?php
/*$ IDZ_ExportGLTrans.php7630 2016-12-10 14:26:42 ChengJiang$*/

include ('includes/session.php');
//include('includes/DefineJournalClass.php');

$Title ="导出会计凭证CSV";

$ViewTopic='Z_ExportGLTrans';
$BookMark = 'Z_ExportGLTrans';

include('includes/header.php');

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

	 if (!isset($_POST['toperiod'])){
		$_POST['toperiod']=$_SESSION['period'];
	}
	 $periodend= $_POST['toperiod'];
	
	$_POST['ComType']='GLTrans';
	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="toperiod" value="' .$_POST['toperiod'] . '" />';			
	echo '<table class="selection">';
	echo '<tr><th colspan="3">' . _('Selection Criteria') . '</th></tr>';


	  $sql = "SELECT distinct  periodno,DATE_FORMAT(trandate, '%Y-%m')  periodate FROM gltrans where periodno <=  '".$_SESSION['period']."' and periodno > 0";
  
	echo '<tr>
		<td>' . _('For Period range').':</td>
		<td><select name="toperiod" size="1" >'; 
   $periods = DB_query($sql);

	while ($myrow=DB_fetch_array($periods,$db)){
	if (isset($_POST['toperiod']) AND $myrow['periodno']==$_POST['toperiod']) {
				echo '<option selected="selected" value="';
			} else {
				echo '<option value="';
			}
		echo $myrow['periodno'] . '">' . $myrow['periodate'] . '</option>';
		}
 
    echo '</select></td>
	</tr>';
	echo '</table>';

if (!isset($_POST['Show']) ){
	echo '<br /><div class="centre"><input type="submit" name="Show" value="' . _('Show transactions'). '" />';
  echo'</div>';

}


if (isset($_POST['transcription'])) {
	if (!file_exists($_SESSION['reports_dir'])){
		$Result = mkdir('./' . $_SESSION['reports_dir']);
	}

	$FileName = $_SESSION['reports_dir'] . '/V_'.$_POST['ComType'] .$_POST['toperiod'].'.csv';
	$fp = fopen($FileName,'a');
	if (!filesize($FileName)){

fwrite($fp, 'periodno,transno ,type,typeno,trandate,account,accountname,narrative,amount,chequeno,tag,flg'."\n");
		}
	if ($fp==FALSE){
		prnMsg(_('Could not open or create the file under') . ' ' . $FileName,'error');
		include('includes/footer.php');
		exit;
	}
		$result = "";
	foreach( $_POST['chkbx'] as $i){
 		$result .= $i.',';
		}
		$result = substr($result,0,-1);
	
 		$sqlout="SELECT periodno,
 						typeno, 
 						type,	
 						trandate,
 						transno,
 						chequeno,
 						account,
 			chartmaster.accountname,
 		   	narrative,
 		   		amount,
 		   		gltrans.tag,
 		   		flg 		    
			  FROM gltrans
				INNER JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode	
				 where transno in (".$result.") and  periodno='" .$_POST['toperiod'] ."' ORDER BY transno";
		$resultout = DB_query($sqlout);
		echo DB_num_rows($resultout);
		$str='';
		feof($fp);	
		
		while ($myrow = DB_fetch_array($resultout)){			
					fwrite($fp, $myrow['periodno'] .','. $myrow['transno'] .','. $myrow['type'] .','. $myrow['typeno'] .','
					.FormatDateForSQL($myrow['trandate']).','. $myrow['account'] .','.iconv( "UTF-8","gb2312",$myrow['accountname']) .','
									.iconv( "UTF-8", "gb2312" , $myrow['narrative']).','. $myrow['amount'].','. $myrow['chequeno'] .','. $myrow['tag'] .','. $myrow['flg'] ."\n");
						
				}
		
	
		//($str,'info');
																
	fclose($fp);
	echo '<p><a href="' .  $FileName . '">' . _('click here') . '</a> ' . _('to view the file') . '<br />';	
}else	if (isset($_POST['Show'])) {
		$typeid= $_POST['JournalType'][0];
		$str='';
		$transno=0;
	 if (isset($_POST['ToPeriod'])){
		PageSet('P',$_POST['ToPeriod']);
    	$periodend= $_POST['ToPeriod'];
		}	else{
		 $periodend= $_SESSION['period'];
		}
	//װ�뵥λ
	/*
		if (file_exists( $_SESSION['reports_dir'] . '/VT.csv')){
		$FileVT =fopen( $_SESSION['reports_dir'] . '/VT.csv','r');
		while ($mytype = fgetcsv($FileVT)) { 	
				if (file_exists( $_SESSION['reports_dir'] .'/V_'.trim($mytype[0]) .Date("Y-m",strtotime($_SESSION['lastdate'])).'.csv')){
				$strt=$mytype[0].$strt;
					$file =fopen($_SESSION['reports_dir'] .'/V_'.trim($mytype[0]) . Date("Y-m",strtotime($_SESSION['lastdate'])).'.csv','r');
					while ($myrow = fgetcsv($file)) { 
						 if ($transno==0 or $transno!=$myrow[0]){
						 	 if(trim($myrow[0])!='transno'){
		  	   			$str=$str.",'".$myrow[0]."'";
				      	$transno=$myrow[0];
				      }
		      		}						
					}
					fclose($file);
				}		
			}
	}
	fclose($FileVT);	*/
	//	prnMsg(Date("Y-m",strtotime($_SESSION['lastdate'])),'info');
	//prnMsg($_POST['ComType'],'error'); 
	   if ($str==''){
	   	 $sqlv="SELECT gltrans.typeno,
	   	 systypes.typename,
	   	  gltrans.type,
				gltrans.trandate,
				gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				CASE WHEN gltrans.amount>0 THEN  gltrans.amount ELSE 0 END AS Debits,
				CASE WHEN gltrans.amount<0 THEN  -gltrans.amount ELSE 0 END AS Credits
			FROM gltrans
			INNER JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode	
				LEFT JOIN systypes
				ON gltrans.type=systypes.typeid
				WHERE   gltrans.periodno='" .$_POST['toperiod']."'	ORDER BY gltrans.transno, gltrans.type,gltrans.typeno";
	 //  	$str = "SELECT distinct transno FROM gltrans where periodno =  '".$_SESSION['period']."'";
	   	}else{   
    	$str=substr($str,1);	   
    
	    $sqlv="SELECT gltrans.typeno,systypes.typename, gltrans.type,
				gltrans.trandate,gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				CASE WHEN gltrans.amount>0 THEN  gltrans.amount ELSE 0 END AS Debits,
				CASE WHEN gltrans.amount<0 THEN  -gltrans.amount ELSE 0 END AS Credits
			FROM gltrans
			INNER JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode	
				LEFT JOIN systypes
				ON gltrans.type=systypes.typeid
				WHERE  transno not  in ( ".$str." ) and gltrans.periodno='" .$_POST['toperiod']."'	ORDER BY gltrans.transno, gltrans.type,gltrans.typeno";
}
			$resultv = DB_query($sqlv);
	if (DB_num_rows($resultv)==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');
	} else {
			echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
		echo '<table class="selection">';
		echo '<tr>
				<th>' . ('Date') . '</th>
				<th>' . _('Voucher No') . '</th>
				<th>' . _('Account Code') . '</th>
				<th>' . _('Account Description') . '</th>
				<th>' . _('Narrative') . '</th>
				<th>' . _('Debits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
				<th>' . _('Credits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>	
					<th>' . _('flag').' </th>				
			</tr>';

		$LastJournal = 0;
   	$LastType = -1;
   	$r=0;
		while ($myrow = DB_fetch_array($resultv)){			
			if ($myrow['typeno']!=$LastJournal or ($myrow['typeno']=$LastJournal and $myrow['type']!=$LastType) ) {			
					if ($r==1){
			echo '<tr class="EvenTableRows">';
			$r=0;
		} else {
			echo '<tr class="OddTableRows">';
			$r=1;
		}
				echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
					<td >' ._('Accounting'). $myrow['transno'].'-'.$myrow['typename'] .$myrow['typeno'] . '</td>';

				//	<td >' . $myrow['typename'] . '</td>					<td class="number">' . $myrow['typeno'] . '</td>';

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
				

			if ($myrow['typeno']!=$LastJournal or ($myrow['typeno']=$LastJournal and $myrow['type']!=$LastType) ){
				echo '<td class="number"><input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'"checked=true ></td></tr>';
	      $LastType = $myrow['type'];
				$LastJournal = $myrow['typeno'];
					
			} else {
				echo '<td colspan="1"></td></tr>';
			}

		}
		echo '</table>';
	} //end if no bank trans in the range to show

//	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br /><div class="centre"><input type="submit" name="Show" value="' . _('Show transactions'). '" />
	<input type="submit" name="transcription" value="导出csv" /></div>';
  // echo $periodend.'---'.date('Y-m-d',strtotime($_SESSION['ProhibitPostingsBefore'])+24*3600);//ѡ���������� 
	//echo '</form>';

}
	echo '</form>';
include('includes/footer.php');

?>