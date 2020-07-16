<?php
/* Z_GLTransChangTypeNo.php
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-05-09 15:36:48
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-05-09 15:37:06
*/
include ('includes/session.php');
$Title = '凭证分组号码重写';
$ViewTopic= 'Delete Journal';
$BookMark = 'Delete Journal';

include('includes/header.php');
echo '<script type="text/javascript">
function reload(){
 window.location.reload();
 }
 </script>';
 if (!isset($_POST['ERPPrd'])){
  $_POST['ERPPrd']=$_SESSION['period'];
 }
 
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form action="' . $urlstr . '" method="post">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="selection">
		    <tr>
          <td>当前会计期间:</td><td>';
          SelectPeriod(1);
   echo'</td>
    	</tr>
		</table>';
	     $sql="SELECT gltrans.typeno,                
                    gltrans.type,
			            	gltrans.trandate,
                    gltrans.transno,
				            gltrans.account,			
                    gltrans.tag,
                    CASE WHEN gltrans.amount>0 THEN  gltrans.amount ELSE 0 END AS Debits,
                    CASE WHEN gltrans.amount<0 THEN  -gltrans.amount ELSE 0 END AS Credits
        FROM gltrans
        WHERE  gltrans.periodno='" .$_POST['ERPPrd']."'  ORDER BY gltrans.transno,gltrans.typeno,tag  ";
      $result = DB_query($sql);
		echo '<br /><div class="centre">
	
          <input type="submit" name="Search" value="查询" />';
          if (isset($_POST['Search']))
          echo'<input type="submit" name="UpdateSave"   value="修改保存" />';
          echo'</div>';
    //print_r(	$_SESSION['tagref']);
   
		echo '<table class="selection">';
		echo '<tr>
			
        <th>顺序编号</th>
        <th>新分组编号</th>
        <th>旧分组编号</th>
        <th>' . ('Date') . $_POST['JournalType']. '</th>
        <th>单元分组</th>
				<th>' . _('Account Code') . '</th>		
				<th>' . _('Debits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>
				<th>' . _('Credits').' '.$_SESSION['CompanyRecord']['currencydefault'] . '</th>	
						
			</tr>';

		$LastJournal = 0;
   		$LastType = -1;
       $r=0;
if (isset($_POST['Search'])||isset($_POST['UpdateSave'])){
    
    $TagTypeNo=[];
		while ($myrow = DB_fetch_array($result)){	
     
    
			if ($myrow['transno']!=$LastJournal  ) {			
				if ($r==1){
				echo '<tr class="EvenTableRows">';
				$r=0;
				} else {
					echo '<tr class="OddTableRows">';
					$r=1;
        }
        $TagTypeNo[$myrow['tag']]++;
				echo '<td >记字' . $myrow['transno'].'
                         <input type="hidden" name="TransNo'.$myrow['transno'] .'"  value="' . $myrow['transno'] . '" /></td>
              <td>'. $_SESSION['tagref'][$myrow['tag']][2].$TagTypeNo[$myrow['tag']] . '
                         <input type="hidden" name="TagTypeNo'.$myrow['transno'] .'"  value="' . $TagTypeNo[$myrow['tag']] . '" /></td>
              <td>'.$myrow['typeno']. '</td>
              <td>' .  ConvertSQLDate($myrow['trandate']) . '</td>
              <td>'.$myrow['tag']. '</td>';
			} else {
				
				if ($r==1){
					echo '<tr class="EvenTableRows"><td colspan="4"></td>';
					$r=0;
				} else {
					echo '<tr class="OddTableRows"><td colspan="4"></td>';
					$r=1;
				}
			}
			echo '<td >' . $myrow['account'] . '</td>
			
				
					<td class="number">' . isZero(locale_number_format($myrow['Debits'],$_SESSION['CompanyRecord'][1]['decimalplaces'])) . '</td>
					<td class="number">' . isZero(locale_number_format($myrow['Credits'],$_SESSION['CompanyRecord'][1]['decimalplaces']) ). '</td>';
			if ($myrow['transno']!=$LastJournal ){
		
	      		//$LastType = $myrow['type'];
				$LastJournal = $myrow['transno'];
					
			} else {
				echo '<td colspan="1"></td></tr>';
			}

		}
	
   
}
echo '</table>';
//print_r($_SESSION['tagsgroup']);
if (isset($_POST['UpdateSave'])) {
 // prnMsg($_POST["ERPPrd"]);
   $rr=0;
  foreach ($_POST as $key => $value) {
   // prnMsg($key.'='.mb_strpos($key,'TransNo'));
    if (mb_strpos($key,'TransNo')!==false) {
		
      $LineID = mb_substr($key,mb_strpos($key,'TransNo')+7);
     
			if ($_POST['TagTypeNo'.$LineID]!="") {
        $TransNo = $_POST['TransNo'.$LineID];
        $TagNo = $_POST['TagTypeNo'.$LineID];
        $sql="UPDATE gltrans SET typeno= '".$TagNo."' WHERE transno=".$TransNo."  AND  periodno='" .$_POST["ERPPrd"] ."'";			
     		$result = DB_query($sql);	
        //echo $sql."<br/>";
        if ($result)
        $rr++;
      
      }
    }
  }
		if(  $rr>0)
     prnMsg("更新会计凭证".$rr."笔的分组编号成功！","Success");
		
}

echo '</form>';
include('includes/footer.php');
?>