/*
 * @Author: ChengJiang 
 * @Date: 2017-04-26 15:03:14 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-06-18 21:02:39
 */
<?php
/* $Id: WorkHourInquiry.php  $*/
include('includes/session.php');
$Title = '工单查询';// Screen identificator.
$ViewTopic = 'WorkHourInquiry';// Filename's id in ManualContents.php's TOC.
$BookMark = 'WorkHourInquiry';// Filename's id in ManualContents.php's TOC.
include('includes/header.php');
if (!isset($_POST['selectprd'])){
	$_POST['selectprd']=$_SESSION['period'].'^'.$_SESSION['lastdate'];
}

if (!isset($_POST['query'])) {
   $_POST['query']=1;
}
if (!isset($_POST['chkquery'])) {
   $_POST['chkquery']=1;
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
	//$Type = 'Receipts';
	//$TypeName =_('Receipts');
	echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
		'/images/bank.png" title="' .
		$Title. '" /> '.$Title.'</p>';// Page title.

//echo '<div class="page_help_text"></div><br />';

echo '<form action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	  <input type="hidden" name="Type" value="' . $Type . '" />
	     <input type="hidden" name="chkquery" value="' . $_POST['chkquery'] . '" />
	    <input type="hidden" name="prdrange" value="' . $_POST['prdrange'] . '" />';

echo '<table class="selection">
		<tr>
	      <td >' . _('Select Period To')  . '</td>
		  <td colspan="2"><select name="selectprd" size="1"  style="width:100px">';
			if (($_SESSION['period']-$_SESSION['startperiod'])<36){	  					
					$sql = "SELECT periodno, lastdate_in_period  FROM periods where periodno>0 AND periodno >='".$_SESSION['startperiod'] ."' AND  periodno <='".(1+$_SESSION['period'])."' ORDER BY periodno DESC ";
			}else{
					$sql = "SELECT periodno, lastdate_in_period FROM periods where periodno>0 AND periodno >='".(floor($_SESSION['startperiod']/12)*12-23 )."' AND  periodno <='".($_SESSION['period']+1)."' ORDER BY periodno DESC ";
			}
			$periods = DB_query($sql);

			while ($myrow=DB_fetch_array($periods,$db)){	

				if(isset($_POST['selectprd']) AND ($myrow['periodno'].'^'.$myrow['lastdate_in_period']==$_POST['selectprd'])){	
					echo '<option selected="selected" value="';
				
				} else {
					echo '<option value ="';
				}
				echo   $myrow['periodno']. '^'.$myrow['lastdate_in_period'].'">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
			}  
			$rang=array('0'=>'月度','1'=>'+/-月',  '3'=>'季度','12'=>'本年度','36'=>'跨年度');
			  
    echo '</select>方式：
	     
		<select name="prdrange" size="1" style="width:80px" >';
		if ($_SESSION['startperiod']<$_SESSION['janr'] ){
			unset($rang[36]);
		}
		foreach($rang as $key=>$val){			
				
				if (isset($_POST['prdrange'])&& $key==$_POST['prdrange']){
					echo '<option selected="True" value ="';
				}else{
					echo '<option value ="';
				}
					echo $key.'">'.$val.'</option>';		
		}		
	echo'	</select>
		</td></tr>';
echo '<tr>
			<td>查询格式：</td>
			<td colspan="2">			 
          			<input type="radio" name="query" value="2"   '.($_POST['query']==2 ? 'checked':"").'  >'._('Total').'
           			<input type="radio" name="query" value="1"   '.($_POST['query']==1 ? 'checked':"").' >'._('Detail').' 
            </td>
	</tr><tr>
		<td >查询类别:</td>
		<td>';
	$chkfmt=array(1=>'操作者',2=>'产品',3=>'工艺',4=>'设备');
	foreach($chkfmt as $key=>$val){
	
		echo'	<input type="checkbox" name="chkquery[]" value="'.$key.'"'.($_POST['chkquery']==$key ? 'checked':"").'   >'.$val;
	}
	/*	<input type="checkbox" name="chkquery[]" value="2"   '.($_POST['chkquery']==2 ? 'checked':"").'  >产品
		<input type="checkbox" name="chkquery[]" value="3"   '.($_POST['chkquery']==3 ? 'checked':"").'  >组件
		<input type="checkbox" name="chkquery[]" value="4"   '.($_POST['chkquery']==4 ? 'checked':"").'  >工艺
		<input type="checkbox" name="chkquery[]" value="5"   '.($_POST['chkquery']==5 ? 'checked':"").'  >设备
	*/
	echo'</td></tr>';
		
	
	/*	<td colspan="2"><select tabindex="4" name="Ostg_or_All">';
		 
		foreach($impft as $key=>$value){
				if (isset($_POST['Ostg_or_All']) and ($_POST['Ostg_or_All']==$key)){
				echo '<option selected="selected" value="' ;
			}else {
				echo '<option value ="';
			}
				echo   $key.'">'.$value.'</option>';
		}

echo '</select></td></tr>';*/
echo'	<tr>
		<td>选择单号:</td>
		<td colspan="2"><input type="text" name="billno" size="15" maxlength="20" value="' . $_POST['billno'] . '" />
		如1,4,7  5-9 >12 <14
		</td></tr>
		<tr>
		<td>输入关键词:</td>
		<td colspan="2"><input type="text" name="keyword" size="35" maxlength="40" value="' . $_POST['keyword'] . '" />
		
		</td></tr>
    </table>
	<br />
	<div class="centre">
		<input tabindex="5" type="submit" name="Search" value="显示工单" />
		<input tabindex="5" type="submit" name="debug" value="debug" />
		<input tabindex="6" type="submit" name="crtExcel" value="导出EXCEL" />';

echo '</div>';

$InputError=0;
	//翻页开始 
if (isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {

	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
			 $timeday=strtotime(explode('^',$_POST['selectprd'])[1]);
			if ($_POST['prdrange']==0){
				$firstday=date('Y-m-01',strtotime(explode('^',$_POST['selectprd'])[1]));
				$endday=date('Y-m-d',strtotime(explode('^',$_POST['selectprd'])[1]));
			}elseif ($_POST['prdrange']==1) {
				$firstday = date("Y-m-d",mktime(0, 0 , 0,date("m",$timeday)-1,1,date("Y",$timeday)));
				$endday=  date("Y-m-d",mktime(23,59,59,date("m",$timeday)+2 ,0,date("Y",$timeday)));
				
				
			}elseif ($_POST['prdrange']==3) {
			
			$season = ceil((date('n',$timeday))/3);//当月是第几季度
		
			$firstday= date('Y-m-d', mktime(0, 0, 0,$season*3-3+1,1,date('Y',$timeday)));
			$endday= date('Y-m-d', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y",$timeday))),date('Y',$timeday)));
			}elseif ($_POST['prdrange']==12) {
			$firstday=date('Y-m-d', mktime(0, 0, 0,1,1,date('Y',$timeday)));
			$endday=date('Y-m-d',strtotime(explode('^',$_POST['selectprd'])[1]));
			}
	if (isset($_POST['query']) && $_POST['query']==1){
   		$sql="SELECT wkhid,
					`wkhono`,
					workhour.`empid`,
					empname,
					`stockid`,
					`components`,
					`program`,
					`techid`,
					`technics`,
					`device`,
					`planquantity`,
					`quantity`,
					`extraquant`,
					`wkhdate`,
					`starttime`,
					`endtime`,
					`remark`,
					`flag`
				FROM `workhour` LEFT JOIN employfile 
				ON employfile.empid=workhour.empid 
				 WHERE wkhdate>='".$firstday."' AND wkhdate<='".$endday."'";
	    }else{
			  $subsql='';
             foreach($_POST['chkquery'] as $key=>$val){
				 if($key==1) {
					$subsql.='empid,';
				}elseif($key==2){
					$subsql.='stockid,';
				}elseif($key==3){
					$subsql.='components,';
				}elseif($key==4){
					$subsql.='techid,';
				}elseif($key==5){
					$subsql.='device,';
				}

			 }

				$sql="SELECT ".$subsql." SUM(`planquantity`) planquantity,
						SUM(`quantity`) quantity,
						SUM(`extraquant`) extraquant    
				FROM `workhour`
				WHERE wkhdate>='".$firstday."' AND wkhdate<='".$endday."' GROUP BY " .
				substr($subsql,0,-1);
				

		}
		$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
		$result = DB_query($sql, $ErrMsg);
		$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
		prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}
      if ($ListCount>0 AND (isset($_POST['Search'])	
	       OR isset($_POST['Go'])	OR isset($_POST['Next'])	
		   OR isset($_POST['Previous']))){
		if (!isset($blnarr)){
          $blnarr=array();
	
			while ($myrow=DB_fetch_array($result)) {
					$balance+=$myrow['debit']-$myrow['credit'];
					$blnarr[]=$balance;

			}
		}
	//	var_dump($blnarr);
		echo '<div>';
		echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
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
		if (isset($ListPageMax) AND  $ListPageMax > 1) {
		echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
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
			<input type="submit" name="Next" value="' . _('Next') . '" />';
		echo '</div>';
	}
		if (isset($_POST['query']) && $_POST['query']==1){
			echo '<table cellpadding="2" class="selection">
					<tr>
						<th class="ascending">序号</th>
						<th class="ascending">单据号</th>					
						<th class="ascending">' . _('Date') . '</th>
						<th class="ascending">操作者</th>
						<th class="ascending">产品</th>
						<th class="ascending">组件</th>	
						<th class="ascending">工艺</th>
						<th class="ascending">设备</th>						
						<th class="ascending">计划工时</th>
						<th class="ascending">实工</th>	
						<th class="ascending">附加工时</th>							
						<th >摘要</th>
						<th ></th>				
					</tr>';

			$k = 0; //row colour counter
			$i = 1; //no of rows counter
			$RowIndex = 0;
		
			if (DB_num_rows($result) <> 0) {
				DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			}
			$LastJournal = 0;
			$LastType = -1;
		//	prnMsg($Outstanding,'info');
			while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
		
				printf('	<td>%s</td>					
							<td>%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?edit=%s">' . _('Edit') . '</a></td>
							
				
						</tr>',
							$RowIndex+1+($_SESSION['DisplayRecordsMax']*($_POST['PageOffset']-1)),						
							$myrow['wkhono'],
							$myrow['wkhdate'],
								$myrow['empname'],
							$myrow['stockid'],
							$myrow['components'],
							
							$myrow['technics'],
							$myrow['device'],
							locale_number_format($myrow['planquantity'],$CurrDecimalPlaces),
							locale_number_format($myrow['quantity'],$CurrDecimalPlaces),
							locale_number_format($myrow['extraquant'],$CurrDecimalPlaces),
							$myrow['remark'],
							$myrow['whkid']);
		
			$RowIndex = $RowIndex + 1;
			}	//end of while loop
	}else{
		echo '<table cellpadding="2" class="selection">
					<tr>
						<th class="ascending">序号</th>
						<th class="ascending">单据号</th>					
						<th class="ascending">' . _('Date') . '</th>
						<th class="ascending">产品</th>
						<th class="ascending">组件</th>	
						<th class="ascending">工艺</th>
						<th class="ascending">设备</th>						
						<th class="ascending">计划工时</th>
						<th class="ascending">实工</th>	
						<th class="ascending">附加工时</th>							
						<th >摘要</th>
						<th ></th>				
					</tr>';

			$k = 0; //row colour counter
			$i = 1; //no of rows counter
			$RowIndex = 0;
		
			if (DB_num_rows($result) <> 0) {
				DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			}
			$LastJournal = 0;
			$LastType = -1;
		//	prnMsg($Outstanding,'info');
			while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax']) ){
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}
		
				printf('	<td>%s</td>					
							<td>%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td >%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td class="number">%s</td>
							<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?edit=%s">' . _('Edit') . '</a></td>
							
				
						</tr>',
							$RowIndex+1+($_SESSION['DisplayRecordsMax']*($_POST['PageOffset']-1)),						
							$myrow['wkhono'],
							$myrow['wkhdate'],
							$myrow['stockid'],
							$myrow['components'],
							
							$myrow['technics'],
							$myrow['device'],
							locale_number_format($myrow['planquantity'],$CurrDecimalPlaces),
							locale_number_format($myrow['quantity'],$CurrDecimalPlaces),
							locale_number_format($myrow['extraquant'],$CurrDecimalPlaces),
							$myrow['remark'],
							$myrow['whkid']);
		
			$RowIndex = $RowIndex + 1;
			}	//end of while loop
	}
	echo '</table>';
	}/*
   echo '<br />
			<div class="centre">
				<input type="hidden" name="RowCounter" value="' . $i . '" />
				<input type="submit" name="Update" value="' . _('Update Matching') . '" />
			</div>';
			*/
			//if (isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next']		
}elseif(isset($_POST['crtExcel'])){
	prnMsg('本功能暂时没有开启！','info');

}elseif(isset($_POST['debug'])){

	/*
	 $timeday=strtotime(explode('^',$_POST['selectprd'])[1]);
	if ($_POST['prdrange']==0){
        $firstday=date('Y-m-01',strtotime(explode('^',$_POST['selectprd'])[1]));
		$endday=date('Y-m-d',strtotime(explode('^',$_POST['selectprd'])[1]));
	}elseif ($_POST['prdrange']==1) {
		$firstday = date("Y-m-d",mktime(0, 0 , 0,date("m",$timeday)-1,1,date("Y",$timeday)));
		$endday=  date("Y-m-d",mktime(23,59,59,date("m",$timeday)+2 ,0,date("Y",$timeday)));
		
		
	}elseif ($_POST['prdrange']==3) {
	
	$season = ceil((date('n',$timeday))/3);//当月是第几季度
   
    $firstday= date('Y-m-d', mktime(0, 0, 0,$season*3-3+1,1,date('Y',$timeday)));
    $endday= date('Y-m-d', mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y",$timeday))),date('Y',$timeday)));
	}elseif ($_POST['prdrange']==12) {
	$firstday=date('Y-m-d', mktime(0, 0, 0,1,1,date('Y',$timeday)));
	$endday=date('Y-m-d',strtotime(explode('^',$_POST['selectprd'])[1]));
	}*/
   	prnMsg($firstday.'本启！'.$endday.'='.$_POST['chkquery'],'info');
}
echo '</div>';
echo '</form>';
include('includes/footer.php');
?>
