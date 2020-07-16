
<?php
/*ID GLJournalUnitTagCheck.php  */

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:57
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-05-04 18:20:49
 */
include('includes/DefineJournalClass.php');  
include ('includes/session.php');
$Title ='凭证单元分组维护';

$ViewTopic='MyTools';
$BookMark = 'GLJournalTagCheck';

include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (!isset($_POST['ERPPrd'])){
	$_POST['ERPPrd']= $_SESSION['period'];
}
  $ERPPrd= $_POST['ERPPrd'];
if (!isset($_POST['UnitsTag'])){
	$_POST['UnitsTag']=1;
}
foreach($_SESSION['CompanyRecord'] as $key=>$row)	{         
	if ($row['coycode']!=0){
		
	   $UnitsTag[$row['coycode']]=$row['unitstab'];
	   $UnitsTag[-$row['coycode']]=$row['unitstab']."内";     
	
	}
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';

echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<input type="hidden" name="ERPPrd" value="' .$_POST['ERPPrd'] . '" />';
		
	echo '<table class="selection">';
	echo '<tr><th colspan="3">' . _('Selection Criteria') . '</th></tr>';		
    echo '<tr>
     	<td>单元分组</td>
		<td>';
		  SelectUnitsTag();		
	echo'</td></tr>';
    
	echo '<tr>
		<td>' . _('For Period range').':</td>
		<td>';
		SelectPeriod($_SESSION['period'],$_SESSION['period']); 
    echo '</td>
			</tr>';
	echo '</table>';
	
	$sql="SELECT gltrans.typeno,systypes.typename, gltrans.type,
			gltrans.trandate,gltrans.transno,
			gltrans.account,
			chartmaster.accountname,
			gltrans.narrative,
			CASE WHEN gltrans.amount>0 THEN  gltrans.amount ELSE 0 END AS Debits,
			CASE WHEN gltrans.amount<0 THEN  -gltrans.amount ELSE 0 END AS Credits,
			gltrans.tag			
		FROM gltrans
		INNER JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode	
			LEFT JOIN systypes
			ON gltrans.type=systypes.typeid		
			WHERE   gltrans.periodno='" .$_POST['ERPPrd']."' 
			AND gltrans.tag=".$_POST['UnitsTag']."
				ORDER BY gltrans.transno, gltrans.type,gltrans.typeno";

		$tranresult = DB_query($sql);

		echo '<br />
	       	<div class="centre">
			<input type="submit" name="Show" value="' . _('Show transactions'). '" />';
			if (isset($_POST['Show']) ){
			
				echo'
			 		<input type="submit" name="UnitTagModfiy" value="修改分组" />';
				
			}
		echo'</div>';
				//<input type="submit" name="UnitTagCheck" value="分组校验" />
if (isset($_POST['UnitTagCheck'])) {
	
	while ($myrow=DB_fetch_array($tagresult,$db)){
	
	$sql="SELECT transno  FROM  gltrans WHERE  periodno='".$_POST['ERPPrd']."' AND tag='".$myrow['tagID']."' AND  account  NOT IN (SELECT  accountcode   FROM chartmaster WHERE tag='".$myrow['tagID']."'OR tag=0 )";
  	$result = DB_query($sql);
	if (DB_num_rows($result)>0){
		$strtran='';
		while($myrow=DB_fetch_array($result)){
		
		$strtran.=($strtran==''?'':',').$myrow['transno'];
		}  	
		prnMsg('yes','info'); 
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
				gltrans.tag	
			FROM gltrans
			INNER JOIN chartmaster
			ON gltrans.account=chartmaster.accountcode	
			LEFT JOIN systypes
			ON gltrans.type=systypes.typeid
			
			WHERE   gltrans.periodno='" .$_POST['ERPPrd']."' 
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
		echo '<table class="selection" style="width: 900px;">';
		echo '<tr>
				<th style="width: 100px;">' . ('Date') . '</th>
				<th style="width: 50px;">凭证号</th>
				<th style="width: 30px;">' . _('Account Code') . '</th>
				<th style="width: 300px;">' . _('Account Description') . '</th>
				<th style="width: 200px;">' . _('Narrative') . '</th>
				<th style="width: 50px;">' . _('Debits').' '.CURR . '</th>
				<th style="width: 50px;">' . _('Credits').' '.CURR . '</th>
				<th style="width: 20px;">单元分组</th>
				<th > </th>				
			</tr>';

		$LastJournal = 0;
		
		$r=1;
		
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
					<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord'][abs($_POST['UnitsTag'])]['decimalplaces'])) . '</td>
					<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord'][abs($_POST['UnitsTag'])]['decimalplaces']) ). '</td>';
				

			if ($myrow['transno']!=$LastJournal ){
				
					echo'<td >';
					
					echo'<select name="selecttag'.$r.'" size="1" >';
				foreach($_SESSION['CompanyRecord'] as $key=>$row)	{	

					
						if ($key!=0){
							if($key==$myrow['tag']){
								echo '<option selected="selected" value="';			
							}else{
								echo '<option value="';
							}
								echo  $key. '">' .$row['unitstab']  . '</option>';
					
							if(-$key==$myrow['tag']){
								echo '<option selected="selected" value="'. (-$key). '">' .$row['unitstab']  . '内</option>';
							}else{
								echo '<option  value="'. (-$key). '">' .$row['unitstab']  . '内</option>';
							}
						}
					
					
				}
			
				echo'</select></td>';
					
				echo '<td class="number"><input type="checkbox" name="chkbx[]" value="'. $myrow['transno'].'^'.$r.'" ></td></tr>';
	
				$LastJournal = $myrow['transno'];
					
			} else {
				echo '<td ></td>
					</tr>';
			}
			$r++;
		}
		echo '</table>';
	}else{
		prnMsg('该分组本期没有凭证！','info');
	} //end if no bank trans in the range to show
}elseif(isset($_POST['UnitTagModfiy'])) {
	  
	if (isset($_POST['chkbx'])){
			$msgstr='';
			foreach( $_POST['chkbx'] as $value){ 
			
				$valarr=explode('^',$value);
				//prnMsg($valarr[0].$_POST['selecttag'.$valarr[1]]);
				$sql="UPDATE `gltrans` SET tag='".$_POST['selecttag'.$valarr[1]]."' WHERE transno =".$valarr[0]."  AND periodno='".$_POST['ERPPrd']."'";
				$result = DB_query($sql); 
				if ($result){
					$msgstr.='记:'.$valarr[0].','; 
				}
		
			}
		
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