
<?php
/*ID GLJournalUnitTagCheck.php 6824 2016/12/11 4:59:09  chengjiang */
/*
 * @Author: ChengJiang 
 * @Date: 2016-12-11 04:59:09
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-05-07 17:29:39
 */
include('includes/DefineJournalClass.php');  
include ('includes/session.php');
$Title ='分组凭证查询维护';

$ViewTopic='Journal Unit Tag';
$BookMark = 'Journal Unit Tag';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['selectperiod'])){
	$_POST['selectperiod']= $_SESSION['period'];
}
  $SelectPeriod= $_POST['selectperiod'];
if (!isset($_POST['unittag'])){
	$_POST['unittag']=0;
}
// $selecttag='';
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="selectperiod" value="' .$_POST['selectperiod'] . '" />
	      <input type="hidden" name="unittag" value="' .$_POST['unittag'] . '" />';		
	echo '<table class="selection">';
	echo '<tr><th colspan="3">' . _('Selection Criteria') . '</th></tr>';
	if (isset($_SESSION['Tag'])){
			$sql="SELECT tagID,tagdescription FROM unittag WHERE flag=0   ORDER BY tagID ";
			$result = DB_query($sql);  
			$tagresult = DB_query($sql);
			$tagarr=array();
			while ($myrow=DB_fetch_array($tagresult,$db)){
				array_push($tagarr,array('tagID'=>$myrow['tagID'],'tagdescription'=>$myrow['tagdescription']));
				array_push($tagarr,array('tagID'=>(-$myrow['tagID']),'tagdescription'=>$myrow['tagdescription'].'共享'));
			}
		echo '<tr>
				<td>单元分组</td>
				<td><select name="unittag" size="1" >';
		if(isset($_POST['unittag'])==-1) {
		echo '<option selected="selected"   value="-1">' ._('All') . '</option>';
		}	
	
		foreach($tagarr as $val){
			if(isset($_POST['unittag']) AND $val['tagID']==$_POST['unittag']){
			   echo '<option selected="selected" value="';
			 }else{
			   echo '<option value="';
		   }
			echo  $val['tagID']. '">' .$val['tagdescription']  . '</option>';
	   
		   }
			echo'</select></td></tr>';
	}  

	  $sql = "SELECT  periodno,DATE_FORMAT(lastdate_in_period, '%Y-%m')  periodate FROM periods where periodno =  '".$_SESSION['period']."'";
  
	echo '<tr>
		<td>' . _('For Period range').':</td>
		<td><select name="selectperiod" size="1" >'; 
   		$result = DB_query($sql);

	while ($myrow=DB_fetch_array($result,$db)){
		if( isset($_POST['selectperiod']) AND $myrow['periodno']==$_POST['selectperiod']){
	
		echo '<option selected="selected" value ="';
		}else {
		 		echo '<option  value ="';
		}
  		echo  $myrow['periodno'] . '">' . $myrow['periodate'] . '</option>';

	}
 
    echo '</select></td>
					</tr>';
	echo '</table>';
	
	$sql="SELECT gltrans.typeno,systypes.typename, gltrans.type,
				gltrans.trandate,gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				CASE WHEN gltrans.amount>0 THEN  gltrans.amount ELSE 0 END AS Debits,
				CASE WHEN gltrans.amount<0 THEN  -gltrans.amount ELSE 0 END AS Credits,
				gltrans.tag,
				unittag.tagdescription		
			FROM gltrans
			INNER JOIN chartmaster
				ON gltrans.account=chartmaster.accountcode	
				LEFT JOIN systypes
				ON gltrans.type=systypes.typeid
				LEFT JOIN unittag
				ON gltrans.tag=unittag.tagID
				WHERE   gltrans.periodno='" .$_POST['selectperiod']."' 
				AND gltrans.tag=".$_POST['unittag']."
					ORDER BY gltrans.transno, gltrans.type,gltrans.typeno";

$tranresult = DB_query($sql);

		echo '<br />
	       	<div class="centre">
			<input type="submit" name="Show" value="' . _('Show transactions'). '" />';
			if (isset($_POST['Show']) ){
				if (DB_num_rows($tranresult)>0) {
				echo'<input type="submit" name="UnitTagCheck" value="分组校验" />
			 		<input type="submit" name="UnitTagModfiy" value="修改分组" />';
				}else{
					prnMsg('该分组本期没有凭证！','info');
				}
			}
		echo'</div>';
				//OR isset($_POST['UnitTagCheck']) OR isset($_POST['UnitTagModfiy'])
if (isset($_POST['UnitTagCheck'])) {
	
	while ($myrow=DB_fetch_array($tagresult,$db)){
	
	$sql="SELECT transno  FROM  gltrans WHERE  periodno='".$_POST['selectperiod']."' AND tag='".$myrow['tagID']."' AND  account  NOT IN (SELECT  accountcode   FROM chartmaster WHERE tag='".$myrow['tagID']."'OR tag=0 )";
  	$result = DB_query($sql);
	if (DB_num_rows($result)>0){
		$strtran='';
		while($myrow=DB_fetch_array($result)){
		
		$strtran.=($strtran==''?'':',').$myrow['transno'];
		}  	
	//	prnMsg('yes','info'); 
		break;
	}
 
 
  }
    if ($strtran!=''){
    $sql="SELECT gltrans.typeno,
                systypes.typename, 
                 gltrans.type,
				gltrans.trandate,
				gltrans.transno,
				gltrans.account,
				chartmaster.accountname,
				gltrans.narrative,
				CASE WHEN gltrans.amount>0 THEN  gltrans.amount ELSE 0 END AS Debits,
				CASE WHEN gltrans.amount<0 THEN  -gltrans.amount ELSE 0 END AS Credits,
				gltrans.tag,
				unittag.tagdescription		
			FROM gltrans
			INNER JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode	
			LEFT JOIN systypes
			ON gltrans.type=systypes.typeid
			LEFT JOIN unittag
			ON gltrans.tag=unittag.tagID
			WHERE   gltrans.periodno='" .$_POST['selectperiod']."' 
			AND gltrans.transno IN ( ".$strtran." )
				ORDER BY gltrans.transno, gltrans.type,gltrans.typeno";
  	    $tranresult = DB_query($sql);
  	  }else{
  	  	  prnMsg('Unit Tag Check Good','info');
  	  	
  	  	}
}elseif (isset($_POST['Show'])) {
	
	if (DB_num_rows($tranresult)>0) {
	
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
		/*echo '<div class="centre">
			   <input type="submit" name="Show" value="' . _('Show transactions'). '" />	             
				 <input type="submit" name="UnitTagCheck" value="分组校验" />
				   <input type="submit" name="UnitTagModfiy" value="修改分组" />
			   </div>';*/
			   
		 //prnMsg('当前查询的分组是'.$_POST['unittag'],'info');

	echo '<table class="selection">';
	echo '<tr>
			<th>' . ('Date') . '</th>
			<th>' . _('Voucher No') . '</th>
			<th>' . _('Account Code') . '</th>
			<th>' . _('Account Description') . '</th>
			<th>' . _('Narrative') . '</th>
			<th>' . _('Debits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
			<th>' . _('Credits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>';
			if (isset($_SESSION['Tag'])){
				echo'<th>' . _('Unit Tag').' </th>';
			 }	
	echo'<th>' . _('flag').' </th>				
		</tr>';

	$LastJournal = 0;
	   //$LastType = -1;
	   $r=1;
	   $k=0;
	while ($myrow = DB_fetch_array($tranresult)){			
		if ($myrow['transno']!=$LastJournal  ) {			
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			}else{
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			echo '<td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
				  <td >记字'. $myrow['transno']. '</td>';
		}else{
		
			if($k==1){
				echo '<tr class="EvenTableRows"><td colspan="2"></td>';
				$k=0;
			}else{
				echo '<tr class="OddTableRows"><td colspan="2"></td>';
				$k=1;
			}
		}

		echo '<td >' . $myrow['account'] . '</td>
				<td >' . $myrow['accountname'] . '</td>
				<td>'.$myrow['narrative']. '</td>
				<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord']['decimalplaces'])) . '</td>
				<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord']['decimalplaces']) ). '</td>';
			

		if ($myrow['transno']!=$LastJournal ){
			if (isset($_SESSION['Tag'])){
				echo'<td ><select name="selecttag'.$r.'">';
				foreach($tagarr as $val){
					if($myrow['tag']== $val['tagID']){
					   echo '<option selected="selected" value="';
					 }else{
					   echo '<option value="';
				   }
					echo  $val['tagID']. '">' .$val['tagdescription']  . '</option>';
			   
				   }
				echo'</select></td>';
			 }	
			echo '<td class="number"><input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'^'.$r.'" ></td></tr>';
  
			$LastJournal = $myrow['transno'];
				
		} else {
			echo '<td colspan="1"></td></tr>';
		}
		$r++;
	}
	echo '</table>';
	} //end if no bank trans in the range to show
 

}elseif(isset($_POST['UnitTagModfiy'])) {
	  
	if (isset($_POST['chkbx'])){
			$msgstr='';
			foreach( $_POST['chkbx'] as $value){ 
			
				$valarr=explode('^',$value);
				//prnMsg($valarr[0].$_POST['selecttag'.$valarr[1]]);
				$sql="UPDATE `gltrans` SET tag='".$_POST['selecttag'.$valarr[1]]."' WHERE transno =".$valarr[0]."  AND periodno='".$_POST['selectperiod']."'";
				$result = DB_query($sql); 
				if ($result){
					$msgstr.='记:'.$valarr[0].','; 
				}
		
			}
			/*$sql="SELECT transno  FROM  gltrans WHERE  periodno='".$_POST['selectperiod']."' AND transno IN ( ".$msgstr." ) AND  account  NOT IN (SELECT  accountcode   FROM chartmaster WHERE tag='".$_POST['unittag']."' OR tag  =0 )";
			$result = DB_query($sql);
			if (DB_num_rows($result)==0){
				$sql="UPDATE `gltrans` SET tag='".$_POST['unittag']."' WHERE transno IN (".$msgstr." ) AND periodno='".$_POST['selectperiod']."' and tag <> '".$_POST['unittag']."'";
				$result = DB_query($sql); 
					prnMsg('Unit tag update succss','info');
			
			}else{*/
				if ($msgstr!=''){
					prnMsg($msgstr.'更改成功!','info');
				}	
			 
		}else{
			prnMsg('You not select journal','info');
		}	
}
	
		

	echo '</form>';
include('includes/footer.php');


?>