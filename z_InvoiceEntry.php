
<?php
/* $Id:z_InvoiceEntry.php ChengJiang $*/
// TaxInvoiceCheck.php 
/*
 * @Author: ChengJiang 
 * @Date: 2017-10-29 04:53:56 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-08-11 16:29:54
  */

include ('includes/session.php');
$Title = '发票手工录入';
$ViewTopic = 'InvoiceEntry';// Filename's id in ManualContents.php's TOC.
$BookMark = 'InvoiceEntry';
include('includes/header.php');
echo'<script type="text/javascript">
		
 		function sltproduct(obj){
				
			window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?myslt="+obj.value ;
		}
	
		
		function ToAm(o, rate) {
			
			var yestr;
			if(o.value!="" ) {
				yestr=(Number(rate)*Number(o.value)).toFixed(2);
			
			}else if(o.value!="NaN"){
				o.value="";	
			}
			document.getElementById("Tax").value=yestr;
		}
		function ToTax(o, rate) {
			
			var yestr;
			if(o.value!="" ) {
			   yestr=(Number(rate)*Number(o.value)).toFixed(2);
			
			}else if(o.value!="NaN"){
				o.value="";	
			}
			document.getElementById("Amount").value=yestr;
		}
		
		
		function fun() {  
				location.reload();
		}  	
</script>';

if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .// Icon image.
	$Title.'" /> ' .// Icon title.
	$Title . '</p>';// Page title.

include('includes/SQL_CommonFunctions.inc');
include('includes/CurrenciesArray.php');
	if (!isset($_POST['selectprd'])){ 	
		$_POST["selectprd"]=$_SESSION['period'].'^'.date('Y-m-d',strtotime($myrow['lastdate_in_period']));
  	}
	if (!isset($_POST['InvFormat'])){ 
	$_POST['InvFormat']=0;
	}
	if (!isset($_POST['rate'])){ 
		$_POST['rate']=0.13;
			}
	$impft=array(0=>'全部发票',1=>'销项专票',0=>'进项发票',3=>'销项普票');
  
echo '<div class="page_help_text">通过导入的进项、销项发票,智能核对、生成会计凭证,不同版本功能不同!</div><br />';
echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="InvFormat" value="' . $_POST['InvFormat'] . '" />
		<input type="hidden" name="unittag" value="' .$_POST['unittag'] . '" />
		';
echo '<table  class="selection"><tr>
	      <td>' . _('Select Period To')  . '</td>
	      <td ><select name="selectprd" size="1" style="width:120">';
	  					
  		 $sql = "SELECT periodno, lastdate_in_period FROM periods where periodno >='".$_SESSION['janr'] ."' AND  periodno <='".$_SESSION['period']."' ORDER BY periodno DESC ";
  		 $result = DB_query($sql);
   
   while ($myrow=DB_fetch_array($result,$db)){
	  if(isset($_POST['selectprd']) AND $myrow['periodno'].'^'.date('Y-m-d',strtotime($myrow['lastdate_in_period']))==$_POST['selectprd']){	
			echo '<option selected="selected" value="';
		
		} else {
			echo '<option value ="';
		}
		echo   $myrow['periodno']. '^'.date('Y-m-d',strtotime($myrow['lastdate_in_period'])).'">' . MonthAndYearFromSQLDate($myrow['lastdate_in_period']) . '</option>';
	}   
	echo '</select>';
		$rang=array('0'=>'月度');//, '3'=>'季度','12'=>'本年','24'=>'上年','36'=>'前年');
	
	echo '范围<select name="prdrange" size="1">';
		if (($_SESSION['janr']-$_SESSION['startperiod'])<=0 ){
			unset($rang[36]);
			unset($rang[24]);
		
		}elseif (($_SESSION['janr']-$_SESSION['startperiod'])<=12 ){
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
echo'</select>
	</td></tr>';
	//if ($_POST['prdrange']==0){
		$firstprd=explode('^',$_POST['selectprd'])[0];
         $endprd=explode('^',$_POST['selectprd'])[0];
		 $_POST['AfterDate']=FormatDateForSQL(date('Y-m-d',strtotime (substr(explode('^',$_POST['selectprd'])[1],0,7).'-01')));
		 $_POST['BeforeDate']=FormatDateForSQL(explode('^',$_POST['selectprd'])[1]);		
  
  	echo'</td></tr>';
	
if (isset($_SESSION['Tag'])){
	$sql="SELECT tagID, taxno,tagdescription,flag FROM unittag WHERE flag=0  ORDER BY tagID ";
	$result = DB_query($sql);

	echo '<tr>
			<td>单元分组</td>
	<td><select name="unittag" size="1" style="width:120" >';

	while ($myrow=DB_fetch_array($result,$db)){
		if(isset($_POST['unittag']) AND $myrow['taxno'].'^'.$myrow['tagID']==$_POST['unittag']){
		echo '<option selected="selected" value="';
		} else {
			echo '<option value="';
					}
		echo  $myrow['taxno'].'^'.$myrow['tagID']. '">' .$myrow['tagdescription']  . '</option>';

	}
	echo'</select>
		</td>
		</tr>';
}

	echo'<tr>
			<td>发票种类</td>
			<td><select name="InvFormat">';
		
		foreach($impft as $key=>$value){
			if (isset($_POST['InvFormat']) and ($_POST['InvFormat']==$key)){
				echo '<option selected="selected" value="' ;
			}else {
				echo '<option value ="';
			}
				echo   $key.'">'.$value.'</option>';
		}				
   echo'</select>
		</td></tr>';	
	   	
   echo'  </table>';
     
	  $sql="SELECT CASE WHEN invtype=2 THEN '进项' ELSE '销项' END typ , MAX(invdate) invdt FROM invoicetrans GROUP BY invtype";
	  $result=DB_query($sql);
  
	  while ($row= DB_fetch_array($result)) {
		  $msg.= $row['typ'].'最末开票日期：'.$row['invdt'].'<br>';
	  }
	 
	  $sql="SELECT SUM(CASE WHEN length(custname)<=15 THEN 1 ELSE 0 END) prv,SUM(CASE WHEN length(custname)>15 THEN 1 ELSE 0 END) com FROM registername WHERE account='' AND flg=0";
	  $result=DB_query($sql);
      $row=DB_fetch_assoc($result); 
	  prnMsg($msg.$row['prv'].'个人无科目设置,'.$row['com'].'个单位无科目设置！','info');	  
	  echo '<table cellpadding="2" class="selection">
	  <tr>
	  <th class="ascending">序号</th>	
	  <th class="ascending">发票号</th>	
	  <th class="ascending">' . _('Date') . '</th>				
	  <th class="ascending">发票类别</th>
	  <th >税率</th>
				  
	  
	  <th class="ascending">客户名称</th>
	  <th >注册码</th>
	  <th class="ascending">金额</th>
	  <th class="ascending">税金</th>	
   
  </tr>';


if (isset($_POST['CheckSave'])||isset($_POST['InvSave']) OR isset($_POST['Search'])	OR  isset($_POST['Go'])	OR isset($_POST['Next']) 
	OR isset($_POST['Previous'])) {
 
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}   
		//读取注册号资料
		$sql="SELECT A.regid,A.registerno,A.tag, B.account,A.subject, A.acctype, A.flg FROM registeraccount A LEFT JOIN registername B ON b.regid=A.regid";
		$result = DB_query($sql);
		
		while ($row = DB_fetch_array($result)) {
		
				$accsub[$row['registerno']]=array($row['regid'],$row['subject'],$row['account'],$row['acctype'],$row['flg']);
		
		}
	
		//读取解析科目规则
		$sql="SELECT acctype, account,accountname, srtype, remark, abstract, A.tag, jd, maxamount, A.flg FROM subjectrule A LEFT JOIN chartmaster B ON A.account=B.accountcode WHERE A.tag=".explode('^',$_POST['unittag'])[1]." OR A.tag<1";
		$result = DB_query($sql);
	  
		while ($row = DB_fetch_array($result)) {
			if ($row['srtype']>0){
			
				$SubPub[$row['acctype']]=$row['account'];	
					
			}
			
		}
	
	   //var_dump(  $SubPub);
	if ($_POST['prdrange']==0){
		$sql="SELECT	invno,
						invtype,
						transno,
						period,
						invdate,
						amount,
						tax,
						toregisterno,
						A.toaccount,
						B.custname,
						B.account,
						C.accountname,
						D.subject,
						A.remark,
						A.flg,
						A.tag,
						A.currcode
					FROM  invoicetrans AS A
					LEFT JOIN registeraccount AS D	ON	D.registerno = A.toregisterno
					LEFT JOIN registername AS B	ON	B.regid = D.regid
					LEFT JOIN chartmaster C ON	C.accountcode = B.account
                        WHERE A.tag=" . explode('^',$_POST['unittag'])[1] . " AND A.flg=0
							  AND period>=" . $firstprd. "  AND period<=" . $endprd. "";
						//  AND DATE_FORMAT (invdate,'%Y%m')='" . explode('^',$_POST['selectprd'])[1] . "'";
  
	 if ($_POST['InvFormat']!=0){
		$sql.=" AND  invtype=" . $_POST['InvFormat'] ;
	 }	
     
		$sql.="	ORDER BY invtype, tax/amount,invdate ";  
	}elseif($_POST['prdrange']==12||$_POST['prdrange']==3){
		$sql="SELECT invtype,  period, SUM(amount) amount, SUM(tax) tax  FROM invoicetrans 
		  WHERE tag=" . explode('^',$_POST['unittag'])[1] . " 
				 AND flg=0  AND period>=" . $firstprd. "  AND period<=" . $endprd. "  GROUP BY invtype,period ORDER BY invtype,period";
	}
	
		$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
		$result = DB_query($sql, $ErrMsg);
	    //	prnMsg($sql);
	/*$ListCount=DB_num_rows($result);
		// $_SESSION['DisplayRecordsMax']=15;
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
	*/

   	
    
	$k = 0; //row colour counter

	$RowIndex = 0;
	$row=1;
	$LastJournal = 0;
    $totalamount=0;
	$totaltax=0;
	$taxrate=-1;
	$tottypamo=0;
	$tottyptax=0;
	$typ=0;
	$transnogl=0;

	$transnoarr=array();	
	while ($myrow=DB_fetch_array($result)) {	
		$tranmsg='';
		$prdgl='';
		if ($myrow['transno']>0){//已经录入凭证并核对
			$LastJournal =2;
			$transnogl=$myrow['transno'];
			$tranmsg='该凭证已完成！';
			$acc='';
			$accname='';
			//$prdgl=$LastJournal;
		}else{
			//$chktranno
			$chktranarr=0;//CheckTrans($myrow,$transnoarr);//查询凭证
		
			//prnMsg($chktranarr[0]['transno']);
			if (is_array($chktranarr)){//存在凭证读取
					//有对应凭证需要确认
				$prdtransno=$chktranarr[0]['periodno'].'^'.$chktranarr[0]['transno'];
				$LastJournal =-1;
				$transnogl=$chktranarr[0]['transno'];
				array_push($transnoarr,$transnogl);
				$tranmsg='"'.'会计凭证&#10;'.$chktranarr[0]['trandate'].'记:'.$transnogl.'&#10;';
				foreach($chktranarr as $val){
					if($val['flg']==1){
						if($val['amount']>0){
							$jdstr="借".$val['amount'];
						}else{
							$jdstr="贷".(-$val['amount']);
						}
					}else{
						if($val['amount']>0){
							$jdstr="贷".(-$val['amount']);
						}else{
							$jdstr="借".$val['amount']; 
						}
						
					}
					$tranmsg.=$val['accunt'].' '.$val['accountname'].'  '.$jdstr.'&#13;';
				}
				$tranmsg.='摘要:'.$chktranarr[0]['narrative'].'"';
				$acc='';
				//prnMsg($tranmsg);
			}else{//没有凭证,查询科目
				if ($myrow['invtype']==2){
					$tranmsg='['.$myrow['invno'].']采购发票入账';
				}else{
					$tranmsg='['.$myrow['invno'].']销售发票入账';
				}
				if ($myrow['account']!=''){
					$acc=$myrow['account'];
					$subname=$myrow['accountname'];
					$prdtransno=0;
				}else{
					if ($myrow['invtype']==2){
						if (isset($SubPub['2202'])){
							$acc=$SubPub['2202'];
							$accname='应付账款-零购商';
							$prdtransno=0;
						}else{
							$acc='';
							$tranmsg='系统未有设置';
							$prdtransno=1;
						}

					}else{
						if (isset($SubPub['1122'])){
							$acc=$SubPub['1122'];
							$accname='应收账款-零售商';
							$prdtransno=0;
						}else{
							$acc='';
							$tranmsg='系统未有设置';
							$prdtransno=1;
						}

					}
				
					
				}
				if ($myrow['subject']!=''){
						$sub=$myrow['subject'];
					}else{
						$sub=$SubPub[$myrow['invtype']];	
					}
				$transnogl=0;
				//$prdgl=0;
			}//endif
			
		}
		
		
		$regstr=$myrow['toregisterno'].'^'.$myrow['invno'].'^'.$myrow['invtype'].'^'.$myrow['amount'].'^'.$myrow['tax'].'^'.$transnogl.'^'.$acc.'^'.$accname.'^'.$myrow['invdate'].'^'.$myrow['custname'].'^'.$myrow['tag'].'^'.$sub.'^'.$LastJournal.'^'.$myrow['period'].'^'.$myrow['currcode'];
		if($LastJournal==2||$LastJournal==-1){ 
			$URL_to_TransDetail = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['period'].'^'.$transnogl;
			$URL_CrtJournal ='';
		
		}else{
			$URL_to_TransDetail='';
			$URL_CrtJournal = $RootPath . '/CreateJournal.php?ty=1&ntpa='.urlencode($regstr);
		}
		 
        //按税率合计		 
	    if ($taxrate!=round(100*$myrow['tax']/$myrow['amount'],2) && $taxrate!=-1){
			echo'<tr>
				<th ></th>	
				<th colspan="4" ></th>
				<th ></th>		
				<th>' . $taxrate . '%税率合计</th>
				<th >'.abs($totalamount).'</th>
				<th >'.abs($totaltax).'</th>				
								
			 </tr>';
			 $totalamount=($myrow['invtype']==2?-$myrow['amount']:$myrow['amount']);
			 $totaltax=($myrow['invtype']==、2?-$myrow['tax']:$myrow['tax']);
			 $taxrate=round(100*$myrow['tax']/$myrow['amount'],2);	
		
		}else{
			$totalamount+=($myrow['invtype']==2?-$myrow['amount']:$myrow['amount']);
			$totaltax+=($myrow['invtype']==2?-$myrow['tax']:$myrow['tax']);
		}
		//按进项销项合计
	    if ($typ!=$myrow['invtype'] && $typ!=0){
			echo'<tr>
				<th ></th>			
				<th  colspan="4" ></th>
				<th ></th>	
				<th >' . $impft[$typ] . '合计</th>		
				<th >'.$tottypamo.'</th>
				<th >'.$tottyptax.'</th>				
					
			</tr>';
			 $tottypamo=0;
             $tottyptax=0;
			 $typ=0;
			 $row=1;
		}
		
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
	
		
		  echo'<td>'.$row.'</td>';	
		
			  
					echo '<td>'. $myrow['invno'].'</td>';
			 
				echo'<td >'.$myrow['invdate'].'</td>
				<td >'.$impft[$myrow['invtype']].'</td>
				<td >'.round(100*$myrow['tax']/$myrow['amount'],2).'</td>	
				
				<td >'.$myrow['custname'].'</td>
				<td >'.$myrow['toregisterno'].'</td>
				<td class="number">'.locale_number_format($myrow['amount'],$_SESSION['CompanyRecord']['decimalplaces']).'</td>
				<td class="number">'.locale_number_format($myrow['tax'],$_SESSION['CompanyRecord']['decimalplaces']).'</td>';
			    echo'</tr>';
				$taxrate=round(100*$myrow['tax']/$myrow['amount'],2);	
				
				$tottypamo+=$myrow['amount'];
				$tottyptax+=$myrow['tax'];
				
				$typ=$myrow['invtype'];
				
				$RowIndex++;
				$row++;
	}//while
		echo'<tr>
		<th ></th>			
		<th  colspan="4" ></th>
		<th ></th>	
		<th >' . $taxrate . '%税率合计</th>
		<th >'.abs($totalamount).'</th>
		<th >'.abs($totaltax).'</th>				
				
	 </tr>';
	echo'<tr>
			<th ></th>			
			<th  colspan="4" ></th>
			<th ></th>	
			<th >' . $impft[$typ] . '合计</th>
			<th >'.abs($tottypamo).'</th>
			<th >'.abs($tottyptax).'</th>				
							
		</tr>';	
		/*echo'<tr>
			<th ></th>			
			<th  colspan="3" >销项减进项差</th>
			<th >'.$totalamount.'</th>
			<th >'.$totaltax.'</th>				
			<th colspan="7" ></th>				
		</tr>';
		*/
}
		$sql="SELECT A.regid,B.custname, registerno FROM registeraccount A LEFT JOIN registername B ON A.regid=B.regid WHERE A.tag=3";
		$result=DB_query($sql);


		echo '<tr class="OddTableRows">';
		echo'<td></td>';
		echo '<td><input type="text"  alt=""  name="invno" maxlength="10" size="11" value="" /></td>';
		$dt=explode('^',$_POST['selectprd'])[1];
		$invdate= date('Y-m-d',strtotime("$dt +14 day" ));
		$invdate1= date('Y-m-d',strtotime("$dt -6 month" ));
	
		echo '<td><input type="date"  alt="" min="'.$invdate1.'" max="'.$invdate.'"  name="invdate" maxlength="10" size="11" value="' . $dt . '" /></td>';
		
		echo '<td><select name="InvFormat">';
		
		foreach($impft as $key=>$value){
			if ($key>0){
			if (isset($_POST['InvFormat']) and ($_POST['InvFormat']==$key)){
				echo '<option selected="selected" value="' ;
			}else {
				echo '<option value ="';
			}
				echo   $key.'">'.$value.'</option>';
			}
		}				
   echo'</select></td>';
   
		$sql="SELECT `taxcatid`, `taxcatname`,taxrate FROM `taxcategories` WHERE `onorder`IN (2,3)";
		$result=DB_query($sql);
		   echo '<td><select name="rate">';	  
		   while($row=DB_fetch_array($result)){
			if (isset($_POST['rate']) and ($_POST['rate']==$row['taxrate'])){
				echo '<option selected="selected" value="' ;
			}else {
				echo '<option value ="';
			}
				echo   $row['taxrate'].'">'.$row['taxcatname'].'</option>';
			
		} 
		  
	   
   				
echo'</select></td>';
	
		echo'<td colspan="2"> <input type="text" id="cust_name" name="cust_name" size="50" list="custid" /> 
			<datalist id="custid"> ';
			while ($row=DB_fetch_array($result)){
				echo '<option value="'.$row['custname'].':'.$row['registerno'].'"  label="'.$row['regid'].'" />'; 
			}
		echo'</datalist></td>';

		echo'<td class="number"><input type="text" name="Amount" id="Amount" onchange="ToAm(this,'.$_POST['rate'].')" value="' . $_POST['Amount'] . '" /></td>
		     <td class="number"><input type="text" name="Tax" id="Tax" onchange="ToTax(this,'.$_POST['rate'].')"  value="' . $_POST['Tax'] . '"  /></td>';
		
		echo'</tr>';
		
	
	echo '</table>';
	
		echo'<br><div class="centre">		    
			<input type="submit" name="Search" value="发票查询">';
			
	   echo'<input type="submit" name="InvSave" value="录入保存">	';
	   			
	
	echo '</div>';


	if(isset($_POST['InvSave'])){
		prnMsg($_POST['rate'].$_POST['cust_name'].'-'.$_POST['InvFormat'].'你有'.$_POST['invno'].'选择'.$_POST['Tax'].'amo！'.$_POST['Amount'],'info');
		if ($_POST['cust_name']==''||($_POST['Amount']==''|| $_POST['Tax']==''|| $_POST['invno']=='')){
			prnMsg('发票号码、客户名称、金额不能为空！','warn');
		}else{
			$regid=0;
			$result=DB_Txn_Begin();
		    $sql="INSERT INTO invoicetrans( invno,
											invtype,
											tag,
											transno,
											period,
											invdate,
											currcode,
											amount,
											tax,
											toregisterno,
											toaccount,
											toname,
											tobank,
											toaddress,
											stockname,
											spec,
											unit,
											price,
											quantity,
											remark,
											flg) VALUES (
											'".$_POST['invno']."',
											'".$_POST['InvFormat']."',
											'".explode('^',$_POST['unittag'])[1]."',
											'0',
											'".explode('^',$_POST["selectprd"])[0]."',
											'".$_POST['invdate']."',
											'CNY',
											'".$_POST['Amount']."',
											'".$_POST['Tax']."',
											'".explode(':',$_POST['cust_name'])[1]."',
											'',
											'".explode(':',$_POST['cust_name'])[0]."',
											'','','','','','0','0','','0')";
		$result=DB_query($sql);
		//prnMsg($sql);
		$sql="INSERT IGNORE INTO registername(custname,
												tag,
												account,
												regdate,
												flg)
												VALUES(
												'".explode(':',$_POST['cust_name'])[0]."',
												'".explode('^',$_POST['unittag'])[1]."',
												'',
												'".date('Y-m-d')."',
												'0'	)";
		$result=DB_query($sql);
		$regid=DB_Last_Insert_ID($db,'registername','regid');
		//prnMsg($sql.'='.$regid);
		if ($regid!=0){
			$sql="INSERT IGNORE INTO registeraccount(regid,
														registerno,
														tag,
														subject,
														acctype,
														flg)
													VALUES(
														'".$regid."',
														'".explode(':',$_POST['cust_name'])[1]."',
														'".explode('^',$_POST['unittag'])[1]."',
														'',
														'".$_POST['InvFormat']."',
														'0')"; 
		//	prnMsg($sql);
			$result=DB_query($sql);
		}
			$result=DB_Txn_Commit();
		if ($result){
			prnMsg($_POST['cust_name'].'交易:'.$amt.'录入成功！','info');
		}
		
		//	prnMsg($_POST['rate'].$_POST['invdate'].'-'.$_POST['InvFormat'].'你有选择！'.$_POST['amount'],'info');
		}										
	}


echo ' </form>';
include ('includes/footer.php');


?>
