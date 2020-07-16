
<?php
/* $Id: JournalEntry.php
/*
 * @Author: ChengJiang 
 * @Date: 2019-04-14 15:42:37 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-10-13 1:03
 *功能 科目分组、打印封账限制、外币、应收账款应付账款自动导入对应库 
  正则表达式规则
 */
include('includes/DefineJournalEntryClass.php');
include('includes/session.php');
include ('includes/GLAccountFunction.php');
$Title ='录入会计凭证';
$ViewTopic = 'GeneralLedger';
$BookMark = 'JournalEntry';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
	function OnSelectTag(S,t){		
		//外账
		var objname= document.getElementById("GLCodeName");
		var objname1= document.getElementById("GLCodeName1");	
		if (S.value<0){
			//内帐
			objname1.hidden="";
			objname.hidden="true";		
		}else{
			objname.hidden="";
			objname1.hidden="true";
			
		}
	}
	function ComboToInput(c, i,u) {
		i.value=c.value.split("^")[0];
		u.value=c.value.split("^")[1];
		document.getElementById("accname").value=c.value.split("^")[2];
		document.getElementById("currate").value=getrate(c.value.split("^")[1]);			
	}
	function JorD(o, t, e) {
		// alert(t.name=="Credit");
		if(o.value!="") {
		
			if (t.name=="Credit"){
				e.value=(o.value*document.getElementById("currate").value).toFixed(2);
			}else{
				e.value=-(o.value*document.getElementById("currate").value).toFixed(2);
			}
			t.value="";
		}else if(o.value=="NaN") 
		o.value="";		
	}
	function getrate(currcode){           
		var jsn=document.getElementById("ratejsn").value;	
		var obj= JSON.parse(jsn);				
		var temp = []; 	
		var rat=0;			
			for(var i=0; i<obj.length; i++)  
				{ 
				temp[i]= (function(n){				  
				if (obj[n].currabrev==currcode){						  
						rat= obj[n].rate;   					    
				}})(i);  
				}  
		return rat;
	}
	function inSelect(v, tA, m) {
		//alert(v.value.split(":")[0]);
		var n=0;
		for(i=0;i<tA.length;i++) {
			n=n+1;
			if(v.value.split(":")[0]==tA[i].value.split(":")[0]) {
				document.getElementById("accname").value=tA[i].value.split(":")[2];
				document.getElementById("GLManualCode").value=v.value.split(":")[0];
				document.getElementById("currcode").value=tA[i].value.split(":")[1];
				document.getElementById("currate").value=getrate(tA[i].value.split(":")[1]);					
				return true;
			}
		}	
		alert(m);
		document.getElementById("GLManualCode").value="";
		return false;
	}		
</script>';
    //打印封账限制录入
	$SQL="SELECT  confvalue FROM myconfig WHERE confname='printprd'";
	$Result=DB_query($SQL);
	$row=DB_fetch_row($Result);
	if ($_SESSION['period']<$row[0]){
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" name="form">';
		  echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		  <input type="hidden" id="ratejsn" name="ratejsn" value=' . $ratejsn . ' />';
		echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
			</p>';
		prnMsg('你选择的会计期间已经打印封账,不能录入凭证或修改！','warn');
		echo '</form>';
		include('includes/footer.php');
		exit;
	}
$tag=0;
if (isset($_GET['Tag'])){
	$tag=$_GET['Tag'];
}
if ((isset($_GET['NewJournal'])	AND $_GET['NewJournal'] == 'Yes'	AND isset($_SESSION['JournalDetail']) )
            OR(isset($_GET['Edit'])AND $_GET['Edit'] == 'Yes' AND  isset($_SESSION['JournalDetail']))){
 	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);
}
	$flag='';
	$transno=0;
if (isset($_GET['Tag']) AND isset($_SESSION['Tag'])){
  $flag.='Tag='.$_GET['Tag'].'&';
}
if (isset($_GET['No'])){
  $flag.='No='.$_GET['No'].'&';
  $transno=$_GET['No'];
}
if (isset($_GET['Edit'])){
  $flag.='Edit=Y&';
}
if (strlen($flag)>2){
	   $flag= substr($flag,0,-1); 
	   $_SESSION['Journalstr']=$flag;  	
}

if (isset($_GET['GLreturn'])){
	if (isset($_SESSION['GLreturn'])){
		 
		unset($_SESSION['GLreturn']);
	}
	
}

if (!isset($_POST['SelectTag'])){
	foreach($_SESSION[$_SESSION['UserID']]	as $val){
		if ($val==$tag){
			if (empty($_POST['SelectTag'])){
				$_POST['SelectTag']=$val;
				break;
			}
		}
	}

	
	
}	
$SelectTag=$_POST['SelectTag'];
///echo $SelectTag."====<br/>";
//print_r($_SESSION[$_SESSION['UserID']]);
if (!isset($GLTypeArr)||count($GLTypeArr)==0){
    $result=DB_query("SELECT typeid, account, toaccount, len, gltype FROM journaltype");
	$GLTypeArr=array();
	$d++;
	while($row=DB_fetch_array($result)){
      array_push($GLTypeArr,array($row['typeid'],$row['account'],$row['toaccount'],$row['len'],$row['gltype']));
	}
}   
if ($_SESSION['Currency']==1){
		$result=DB_query("SELECT currabrev, ROUND(rate,decimalplaces) rate  FROM currencies  WHERE currabrev!='".$_SESSION['CompanyRecord'][abs($_POST['SelectTag'])]['currencydefault']."'");
		//$curratearr=array();
		$i=0;
		while ($row=DB_fetch_array($result)){
			$curratearr[$i]=array('currabrev'=>$row['currabrev'],'rate'=>$row['rate']);
			$i++;
		}
			$ratejsn=json_encode( $curratearr);	   
}
if (!isset($_SESSION['JournalDetail'])){
	$_SESSION['JournalDetail'] = new Journal;
}
if (!isset($_POST['GLManualCode'])) {
	$_POST['GLManualCode']='';
}
if (!isset($_POST['documents'])){
	$_POST['documents']=1;
}
if (!isset($_POST['GLNarrative'])) {
	$_POST['GLNarrative'] = '';
}
if (!isset($_POST['Credit'])) {
	$_POST['Credit'] = '';
}
if (!isset($_POST['Debit'])) {
	$_POST['Debit'] = '';
}
	//启用分组功能后

$cm=$_SESSION['CompanyRecord'][$tag]['unitstab'];
if(isset($_GET['Edit'])	AND $_GET['Edit'] == 'Yes' && (empty($_SESSION['JournalDetail']->TransNo)|| $_SESSION['JournalDetail']->TransNo==0)){
		 
		 $sql="SELECT gltrans.typeno,
						systypes.typename,
						gltrans.type,
						gltrans.trandate,
						gltrans.transno,
						gltrans.account,
						chartmaster.accountname,
						gltrans.narrative,
						gltrans.amount,
						gltrans.flg,
						gltrans.chequeno,
						gltrans.tag,
						currtrans.currcode,
						currtrans.exrate,
						currtrans.examount
					FROM gltrans
					LEFT JOIN chartmaster ON gltrans.account = chartmaster.accountcode
					LEFT JOIN systypes ON gltrans.type = systypes.typeid
					LEFT JOIN currtrans ON CONCAT(currtrans.period,
													currtrans.transno,
													currtrans.account) = CONCAT(
													gltrans.periodno,
													gltrans.transno,
													gltrans.account)
				WHERE   gltrans.periodno=" .$_SESSION['period']."  AND gltrans.transno=".$_GET['No']."	ORDER BY gltrans.transno, gltrans.type,gltrans.typeno";
			$result = DB_query($sql);
			$row=DB_fetch_assoc($result);
			$_SESSION['JournalDetail']->TransNo=$row['transno'];
			$_SESSION['JournalDetail']->TypeNo=$row['typeno'];
			$_SESSION['JournalDetail']->Type=$row['type'];
			$_SESSION['JournalDetail']->Tag=$row['tag'];

			$_POST['SelectTag']=$row['tag'];						
			$cm.="  ".$row['transno']."号";
			$_SESSION['JournalDetail']->JnlDate=$row['trandate'];
			$_SESSION['JournalDetail']->Period=$_SESSION['period'];
			$_POST['JournalDate']=date("Y-m-d",strtotime($row['trandate']));
			DB_data_seek($result,0);
			$i=0;							
     while ($myrow = DB_fetch_array($result)){
		$i++;
		if (  $myrow['currcode'] !=CURR){
			$currflg=1;
		 }	
     	if (($myrow['amount']>0 && $myrow['flg']==1)||($myrow['amount']<0 && $myrow['flg']==-1)){
			 $debit=$myrow['amount'];
			 $credit=0;
		 }else{
			$credit=-$myrow['flg']*$myrow['amount'];
			$debit=0;
		 }
		$_SESSION['JournalDetail']->Add_To_GLAnalysis($debit,
		                                              $credit,
												$myrow['narrative'],
												$myrow['account'],
												$myrow['accountname'],
												$myrow['tag'],											
												($myrow['exrate']===null?1:$myrow['exrate']),
												($myrow['examount']===null?1:$myrow['examount']),
												($myrow['currcode']===null?$_SESSION['CompanyRecord'][abs($_POST['SelectTag'])]['currencydefault']:$myrow['currcode']) 
												);	
	}
}
//prnMsg($_SESSION['JournalDetail']->JnlDate.'='.$_SESSION['JournalDetail']->TransNo);
 //var_dump($_SESSION['JournalDetail']->GLEntries );
 	 //录入日期默认���设置
   if (isset($_GET['NewJournal'])	AND $_GET['NewJournal'] == 'Yes'){
		//if (isset($_POST['JournalDate'])AND Is_Date($_POST['JournalDate'])){
			if( date('Y-m',strtotime($_POST['JournalDate']))!=date('Y-m',strtotime($_SESSION['lastdate']))){
				if (date('Y-m')==date('Y-m',strtotime($_SESSION['lastdate']) )){
				$_POST['JournalDate']=date('Y-m-d');
				}else{
					$_POST['JournalDate']=date("Y-m-d",strtotime($_SESSION['lastdate']));
				}
			}
	}
	if (!isset($_POST['JournalDate'])||strtotime($_POST['JournalDate'])<strtotime(substr($_SESSION['lastdate'],0,4).'-01-01')){
		//if (isset($_GET['NewJournal'])	AND $_GET['NewJournal'] == 'Yes'){
		if(isset($_GET['Edit']) ){
				$_POST['JournalDate']=$_SESSION['JournalDetail']->JnlDate;
		}else{
			if (date('Y-m')==date('Y-m',strtotime($_SESSION['lastdate']) )){
				$_POST['JournalDate']=date('Y-m-d');
			}else{
				$_POST['JournalDate']=date("Y-m-d",strtotime($_SESSION['lastdate']));
			}	
		}
	}

	if((isset($_SESSION['Journalstr']) AND strlen($_SESSION['Journalstr'])>3)||isset($_GET['GLreturn'])){
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?'.$_SESSION['Journalstr']. '"  method="post" name="form">';
	}else{
	    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" name="form">';
    }
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	  <input type="hidden"  name="JournalDate" value=' . $_POST['JournalDate'] . ' />
	  <input type="hidden" id="ratejsn" name="ratejsn" value=' . $ratejsn . ' />
	  <input type="hidden" id="SelectTag" name="SelectTag" value=' . $_POST['SelectTag'] . ' />';
echo '<p class="page_title_text">
			<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'
			</p>';
$inputerr=0;
//按保存按钮执行以下

if (isset($_POST['CommitBatch']) AND $_POST['CommitBatch']=='保存/缓存'){
	//录入行数不能大于15
    if ($_SESSION['JournalDetail']->GLItemCounter >=14){
   	   	prnMsg(_('Document line greater than 15'));
	}
	if ($_SESSION['JournalDetail']->GLItemCounter ==0){
		$_SESSION['JournalDetail']->JnlDate=$_POST['JournalDate'];
	}
	
		if( $_POST['GLManualCode']!='' && $_POST['Debit']=='' && $_POST['Credit']=='' ){								
			//凭证自动金额
			if((double)$_SESSION['JournalDetail']->JournalTotal >0 ) {
				$credit=$_SESSION['JournalDetail']->JournalTotal;  			  
				$debit='';
			}else{
				$debit=-$_SESSION['JournalDetail']->JournalTotal;  			  
				$credit='';
			}  
		}else{
				//新数据录入
			if ($_POST['Debit']!=''){
				$debit=$_POST['Debit'];
				$credit=0;
			}else{
				$debit=0;
				$credit=$_POST['Credit'];
			}
		}
		if ( $_POST['currcode']==CURR){
			if ( $debit!=0){
				$_POST['examount']=$debit*$_POST['currate'] ; 
			}else{
				$_POST['examount']=-$credit*$_POST['currate'] ; 
			}
		}	
		if ($inputerr==0){ 
			if ($_POST['currcode']!=CURR){
                $_POST['GLNarrative']= $_POST['GLNarrative'].'['.$_POST['currcode'].$_POST['examount'].']';
				}
				$examo=$_POST['examount'];
			if(isset($_GET['Edit'])	AND $_GET['Edit'] == 'Y'){	
				$TransNo =$_GET['No'];
			}
		   $_SESSION['JournalDetail']->Add_To_GLAnalysis($debit,
		   												$credit,
		   												 $_POST['GLNarrative'],
														 $_POST['GLManualCode'],
														 $_POST['accname'],
													   	 $SelectTag,
														 $_POST['currate'],
														 $examo,
														 $_POST['currcode']);
		  $_POST['Credit']='';;
		  $_POST['Debit']='';
	
		  $_POST['GLManualCode']='';
		  $_POST['accname']='';	  
	      $_POST['GLAmount']=0;
		  $_POST['GLCode']='';
		  $_POST['currate']=0;
		  $_POST['examount']=0;
		  $_POST['currcode']='';
		}    
	 //余额合计=0 2行以上存入gltrans表
	 //echo '-='.$_SESSION['JournalDetail']->JournalTotal;
	 //print_r($_SESSION['JournalDetail']->GLEntries );
	if(round($_SESSION['JournalDetail']->JournalTotal,2) ==0 AND $_SESSION['JournalDetail']->GLItemCounter >1) {								
		if ($inputerr==1){
			prnMsg('你输入的凭证格式有错误！</br>凭证格式:只能一借多贷或一贷多借，现金银行科目不能多借多贷！</br>','info');
		}
		if($inputerr==0){		 
				//Start a transaction to do the whole lot inside
			$period =$_SESSION['period'];
			$prtchk=0;
			$tagsgroup=$_SESSION['tagref'][ $SelectTag][1];
		
			$result = DB_Txn_Begin();
			if(isset($_GET['Edit'])	AND $_GET['Edit'] == 'Y'){	
						$TransNo =$_GET['No'];
					$result=DB_Txn_Begin();	
					$SQL="DELETE FROM gltrans WHERE periodno='".$period."' AND transno='".$_GET['No']."'";
					$result = DB_query($SQL);
					$SQL="DELETE FROM `debtortrans` WHERE prd='".$period."'   AND transno='".$_GET['No']."'";
					$result = DB_query($SQL);
					$SQL="DELETE FROM supptrans WHERE prd='".$period."'   AND transno='".$_GET['No']."'";
					$result = DB_query($SQL);
					$SQL="UPDATE `banktransaction` SET `transno`=0 WHERE `period`='".$period."'  AND transno='".$_GET['No']."'";
					$result = DB_query($SQL);
					$SQL="UPDATE `invoicetrans` SET  `transno`=0  WHERE `period`='".$period."' AND  transno='".$_GET['No']."'";
					$result = DB_query($SQL);
					$TagTypeNo = GetTagTypeNo( $tagsgroup,$period, $db);
					//if ($currflg==1){
						$SQL="DELETE FROM currtrans WHERE period= '".$period."' AND transno='".$_GET['No']."'";
						$result = DB_query($SQL);
					
					$result = DB_Txn_Commit();
				//	}
			}else{
				$TransNo =GetTransNo( $period, $db);
				//$tagsgroup=$_SESSION['tagref'][ $SelectTag][1];
				$TagTypeNo = GetTagTypeNo( $tagsgroup,$period, $db);
			}
			$GLTransType=GetTransType($_SESSION['JournalDetail']->GLEntries ,$GLTypeArr,$period);
				//$GLTransType=$typarr[0];
				//$TypeNo =$typarr[1];
			$msgerr='';
			$post=0;
			//根据内外帐选择  存储凭证类型	
			foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
				if  (substr($JournalItem->GLCode,0,4)=='1122'||substr($JournalItem->GLCode,0,4)=='2202'){ 
					$post=1;
				}else{
					$post=0;
				}
				if ($JournalItem->Debit!=0){
					$Amount=$JournalItem->Debit;
				}else{
					$Amount=-$JournalItem->Credit;
				}
				$dc=1;
				if ($JournalItem->Debit<0||$JournalItem->Credit<0){
					$dc=-1;
				}
				$SQL = "INSERT INTO gltrans ( type,
												transno,
												typeno,
												chequeno,
												trandate,
												periodno,
												account,
												narrative,
												amount,
												tag,
												flg,
												userid,
												posted,
												prtchk)
										VALUES (  '" . $GLTransType . "',
												'" . $TransNo . "',
												'" . $TagTypeNo . "',
												'".$_POST['documents']."',
												'" . $_SESSION['JournalDetail']->JnlDate . "',
												'" . $period . "',
												'" . $JournalItem->GLCode . "',
												'" . $JournalItem->Narrative  . "',
												'" . $Amount . "',
												'" . $SelectTag."',
												'" . $dc."' ,
												'".$_SESSION['UserID']."',
												'" . $post."' ,
												'" . $prtchk."')";
				$ErrMsg = _('Cannot insert a GL entry for the journal line because');
				$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
				$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				//下面代码为应收账款、应付账款录入
				if  (substr($JournalItem->GLCode,0,4)=='1122'){ 
					$sql="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` FROM 					    `register_account_sub` WHERE sub= '" . $JournalItem->GLCode . "'";//  OR  custname  LIKE= ";
					$result = DB_query($sql);
					if (DB_num_rows($result)==1){
					$row=DB_fetch_row($result);
					$sql="INSERT INTO debtortrans( transno,
													type,
													debtorno,
												
													trandate,
													inputdate,
													prd,
													settled,
													reference,
													tpe,
													order_,
													rate,
													ovamount,
													ovgst,
													ovfreight,
													ovdiscount,
													diffonexch,
													alloc,
													invtext,
													shipvia,
													edisent,
													consignment,
													packages,
													salesperson	) 
													VALUE('" . $TransNo . "',
														'" . $GLTransType . "',
														'" . $row[0] . "',
													
														'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
														'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
														'" . $period . "',
														'0', '0', '0',  '0', 
														'" . (is_numeric($JournalItem->Exrat)?$JournalItem->Exrat:1)  . "',
														'" . $Amount . "',
														'0',  '0',  '0',  '0',  '0',  '','0','0','0','1',''	) ";
						$result = DB_query($sql);
					}else{
						$SQL="INSERT INTO `erplogs`(`title`,
													`content`,
													`userid`,
													`logtype`,
													`logtime`) 
									VALUES (	'".$JournalItem->GLCode ."' ,
												'".$JournalItem->Narrative."',
												'".$_SESSION['UserID']."',
												'1',
												'".date("Y-m-d h:i:s")."' )";
							$result = DB_query($SQL,$ErrMsg);	
						$msgerr=',应收账款对应的客户异常！';
					}
				}elseif  (substr($JournalItem->GLCode,0,4)=='2202'){ 
					$sql="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` FROM 					    `register_account_sub` WHERE sub= '" . $JournalItem->GLCode . "'";//  OR  custname  LIKE= ";
					$result = DB_query($sql);
					if (DB_num_rows($result)==1){
					$row=DB_fetch_row($result);
					$sql="INSERT INTO supptrans(	transno,
													type,
													prd,
													supplierno,
													suppreference,
													trandate,
													inputdate,
													settled,
													rate,
													ovamount,
													ovgst,
													diffonexch,
													alloc,
													transtext	)
											VALUE('" . $TransNo . "',
													'" . $GLTransType . "',
													'" . $period . "',
													'" . $row[0] . "',
													'',
													'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
													'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
													'0', 
													'" . (is_numeric($JournalItem->Exrat)?$JournalItem->Exrat:1) . "',
													'" . $Amount . "',
													'0',  '0',  '0', '') ";
						$result = DB_query($sql);
					}else{
						$SQL="INSERT INTO `erplogs`(`title`,
													`content`,
													`userid`,
													`logtype`,
													`logtime`) 
									VALUES (	'".$JournalItem->GLCode ."' ,
												'".$JournalItem->Narrative."',
												'".$_SESSION['UserID']."',
												'1',
												'".date("Y-m-d h:i:s")."' )";
							$result = DB_query($SQL,$ErrMsg);	
						$msgerr=',应付账款对应的客户异常！';
					}
				}
				if ( $JournalItem->Examount!=0 && $JournalItem->Currcode!='CNY'){
					$SQL = "INSERT INTO currtrans(transdate,
													transno,
													period,
													account,
													exrate,
													amount,
													examount,
													currtype,
													currcode,
													flg)
										VALUES ('" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
												'" . $TransNo . "',
												'" . $period . "',
												'" . $JournalItem->GLCode . "',
												'" . $JournalItem->Exrat . "',
												'" . $Amount . "',
												'" . $JournalItem->Examount . "',
												'0',
												'" . $JournalItem->Currcode . "',
												'" . $dc."' )";
							$ErrMsg = _('Cannot insert a  transaction because');
							$DbgMsg = _('Cannot insert a  transaction with the SQL');
							$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
				}
			}
			$result= DB_Txn_Commit();
			if(isset($_GET['Edit'])	AND $_GET['Edit'] == 'Y'){	
				prnMsg($_POST['JournalDate'].'会计凭证 记 ' . $TransNo . ' 修改成功!'.$msgerr,'success');
			}else{
				prnMsg($_POST['JournalDate'].'会计凭证 记 ' . $TransNo . ' '._('has been successfully entered').$msgerr,'success');
			}
			unset($_POST['JournalProcessDate']);
			unset($_POST['JournalType']);
			unset($_SESSION['JournalDetail']->GLEntries);
			unset($_SESSION['JournalDetail']);
			DB_free_result($result);
		} 
		echo '<br />';
			if(isset($_GET['Edit'])){
				unset($_SESSION['Journalstr']);
				echo "<script>window.close();</script>";
				echo '<a href="' .$RootPath. '/JournalAudit.php?See=Y">返回审核</a>';
			}else{
				//echo "<script>window.close();</script>";
				//header('Location:JournalEntry.php?NewJournal=Yes&Tag='.$tag);
				if ($tag==0){
					echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?NewJournal=Yes">返回录入凭证</a></br>';
			
				}else{
					echo '<a href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?NewJournal=Yes&Tag='.$tag.'">返回录入凭证</a></br>';
				}	
			}	
			include ('includes/footer.php');
			exit;  
	}
}elseif (isset($_GET['Delete'])){  
	//删除凭证
	$_POST['JournalDate']=$_SESSION['JournalDetail']->JnlDate;
	$_POST['GLManualCode']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->GLCode;
	if ($_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Debit!=0){
		$_POST['Debit']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Debit;
		$_POST['Credit']='';
	}else{
		$_POST['Debit']='';
		$_POST['Credit']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Credit;
	}
	$_POST['GLNarrative']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Narrative ;
	$_POST['accname']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->GLActName;
	$tag=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->tag;
	$_POST['currate']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Exrat;
	$examo=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Examount;
	$_POST['currcode']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Currcode;
	$_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);
}elseif($_POST['DelJournal']){
	if ($_SESSION['AccessLevel']==8 OR $_SESSION['AccessLevel']==6){
		$sql="DELETE FROM `gltrans` WHERE periodno=".$_SESSION['JournalDetail']->Period." AND transno=".$_SESSION['JournalDetail']->TransNo ." AND prtchk=0 AND  printno=0";
	}else{
		$sql="DELETE FROM `gltrans` WHERE periodno=".$_SESSION['JournalDetail']->Period." AND transno=".$_SESSION['JournalDetail']->TransNo ." AND prtchk=0 AND  printno=0 AND userid='".$_SESSION['UserID']."'";
	}
	$result=DB_query($sql);
	if ($result){
		prnMsg($_SESSION['JournalDetail']->JnlDate.'会计凭证号:'.$_SESSION['JournalDetail']->TransNo.'删除成功！');
			unset($_SESSION['JournalDetail']->GLEntries);
			unset($_SESSION['JournalDetail']);
			unset($typarr);
		
			DB_free_result($result);
			echo "<script>window.close();</script>";
	}else{
		prnMsg('没有成功删除凭证!','info');
	}
}

$AfterDate=date('Y-m-01',strtotime ($_SESSION['lastdate']));

echo '<table class="selection" width="700">
		<tr>
			<th colspan="7"><h3>'.$cm.' 会计凭证</h3></th>
		</tr>';
echo '<tr>';
//内外账

	echo'<th ></th>
		<th>
		<select name="SelectTag" size="1"  onchange="OnSelectTag(this,'.$tag.')" >';		
		foreach($_SESSION[$_SESSION['UserID']]	as $val){
			//echo '<option '.($_POST['SelectTag']<0 ?'selected="selected"':"").' value="' . (-$_SESSION['Tag']) . '">'.$tag.'='.$val.'内账凭证</option>';
			if (abs($val)==$tag){
				if (isset($_POST['SelectTag'])&&$_POST['SelectTag']==$val){
					echo '<option selected="selected" value="';			
				}else{
					echo '<option value="';
				}
					echo  $val. '">' .($val>0?"外":"附加".abs($val))  . '凭证</option>';
			
			}
		}
			echo '</select>
			</th>';		  	

echo'<th  colspan="5"  style="text-align:left;">	' . _('Date to Process Journal') . ':
				<input type="date"   alt="" min="'.$AfterDate.'" max="'.$_SESSION['lastdate'].'"  name="JournalDate" maxlength="10" size="11" value="' . $_POST['JournalDate'] . '" />
				</th>
		</tr>
		<tr>
			<th width="10">' . _('Sequence') .'</th>
			<th width="300">' . _('GL Account') . '</th>';
	if ($_SESSION['Currency']==1){
			echo'<th width="110">外币金额</th>';
	}else{
			echo '<th></th>';	
		}
     echo'<th width="110">' . _('Debit') . '</th>
			<th width="110">' . _('Credit') . '</th>
			<th width="150" >' . _('Narrative') . '</th>
			<th width="20">操作</th>
		</tr>';
$DebitTotal=0;
$CreditTotal=0;
$j=0;
//显示已录入数据
foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
		if ($j==1) {
			echo '<tr class="OddTableRows">';
			$j=0;
		} else {
			echo '<tr class="EvenTableRows">';
			$j++;
		}
	echo '<td>' . $r  . '</td>';
	echo' <td>' . $JournalItem->GLCode . ' - ' . $JournalItem->GLActName . '</td>';
	if (  $JournalItem->Currcode !=CURR){
		echo'<td  class="number">' . locale_number_format($JournalItem->Examount,$_SESSION['CompanyRecord'][abs($_POST['SelectTag'])]['decimalplaces']) . '</td>';
    }else{
		echo '<td></td>';
	}
   			echo '<td class="number">' .locale_number_format($JournalItem->Debit,$_SESSION['CompanyRecord'][abs($_POST['SelectTag'])]['decimalplaces']) . '</td> ';
			echo '<td class="number">' . locale_number_format($JournalItem->Credit,$_SESSION['CompanyRecord'][abs($_POST['SelectTag'])]['decimalplaces']) . '</td>';
			$CreditTotal+=$JournalItem->Credit;
			$DebitTotal += $JournalItem->Debit;   
	  echo '<td>' . $JournalItem->Narrative  . '</td>';
  
			if (isset($_SESSION['Journalstr']) AND strlen($_SESSION['Journalstr'])>3){
					echo '<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .'?'. $_SESSION['Journalstr']. '&Delete=' . $JournalItem->ID . '">' . _('Delete') . '</a></td>';
			}else{
					echo '<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $JournalItem->ID . '">' . _('Delete') . '</a></td>';
			}
		 echo '</tr>';
	  $r++;
	}
   //合计
echo '<tr class="EvenTableRows">       
        <td></td>
		<td class="number"><b>' . _('Total') .  '</b></td>';
if ($_SESSION['Currency']==1){
	echo'<td class="number"><b></b></td>';
}else{
		echo'<td></td>';
	}
echo'	<td class="number"><b>' . locale_number_format($DebitTotal,$_SESSION['CompanyRecord'][abs($SelectTag)]['decimalplaces']) . '</b></td>
		<td class="number"><b>' . locale_number_format($CreditTotal,$_SESSION['CompanyRecord'][abs($SelectTag)]['decimalplaces']) . '</b></td>
	    <td></td>
		<td></td>
	</tr>';
if ($DebitTotal!=$CreditTotal) {
	echo '<tr><td colspan="7" align="center" style="background-color: #fddbdb"><b>' . _('Required to balance') .': </b>' .
		locale_number_format(abs($DebitTotal-$CreditTotal),$_SESSION['CompanyRecord'][abs($SelectTag)]['decimalplaces']);
}
if ($DebitTotal>$CreditTotal) {
	echo ' ' . _('Credit') . '</td></tr>';
} else if ($DebitTotal < $CreditTotal) {
	echo ' ' . _('Debit') . '</td></tr>';
}
echo '</table>
    </td>
    </tr>
    </table>';
    //录入凭证行
echo '<table class="selection" width="80%">';
echo '<tr>
		<th colspan="4">
		<div class="centre"><h3>' . _('Journal Line Entry') . '</h3></div>
		</th>
	</tr>';
echo '<tr>
	<th colspan="2">' . _('Select GL Account') . '</th>
		<th>' . _('Debit') . '</th>
		<th>' . _('Credit') . '</th>
	</tr>';
	//$_SESSION['GLAccount']该参数可能作废
 if (isset($_GET['Tag']) AND !isset($_SESSION['GLAccount'])){
	if (isset($_POST['SelectTag'])&&$_POST['SelectTag']>0){
	//echo '1-Tag';
    $sql="SELECT t3.accountcode,t3.currcode,
	             t3.accountname
			 FROM chartmaster t3 
	 		 WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
		( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and (t3.tag=1 or t3.tag='0' ) AND used>=-1 ORDER BY t3.accountcode";
	}else{
		$sql="SELECT t3.accountcode,t3.currcode,
					t3.accountname 
			FROM chartmaster t3 WHERE t3.accountcode NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
				( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) AND used>=-1 order by t3.accountcode";
	}
  }elseif (!isset($_GET['Tag'])AND isset($_SESSION['GLAccount'])) {
	//echo '3-NoTag';
	 $sql="SELECT distinct t.account accountcode,t1.currcode,
	                       t1.accountname 
						FROM gltrans t
						 left join  chartmaster t1 on t.account=t1.accountcode
						 WHERE t.periodno<='".$_SESSION['period']."' and t.periodno>".$_SESSION['period']."-3 AND used>=-1 order by accountcode";
 }elseif (isset($_GET['Tag'])AND isset($_SESSION['GLAccount'])) {
	 
	 //echo '4常用-Tag';
	$sql="SELECT distinct t.account accountcode,t1.currcode,
	                      t1.accountname
	 					FROM gltrans t 
						 left join  chartmaster t1 on t.account=t1.accountcode 
						 WHERE t.periodno<='".$_SESSION['period']."' and t.periodno>".$_SESSION['period']."-3 and (t.tag='".$tag."' or t.tag='0' ) AND used>=-1 order by accountcode";
}
/**elseif(!isset($_SESSION['GLAccount'])){
	echo '2-默认';
	
	$sql="SELECT t3.accountcode,t3.currcode,
	             t3.accountname 
			FROM chartmaster t3 WHERE t3.accountcode NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
				( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) AND used>=-1 order by t3.accountcode";
   } */
$result=DB_query($sql);
echo'<tr>
	   <td colspan="2">';
  //外账
  echo'<input type="text" name="GLCodeName"  id="GLCodeName"   list="GLCode" '.($_POST['SelectTag']<0?'hidden="hidden"':"").' maxlength="100" size="70" placeholder="输入科目、编码关键词筛选，然后选择" autocomplete="off"   onChange="inSelect(this, GLCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
	   <datalist id="GLCode"> ';
			while ($row=DB_fetch_array($result)){
				echo '<option value="' . $row['accountcode'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false) . '"label=' . $row['accountcode'].'[' .$row['currcode'].']' .htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false)  . '>';
			}
  echo'</datalist>'; 
  //内帐
     DB_data_seek($result,0);
    echo'<input type="text" name="GLCodeName1"  id="GLCodeName1"   list="GLCode1" '. ($_POST['SelectTag']>0?'hidden="hidden"':"").'  maxlength="100" size="70" placeholder="输入科目、编码关键词筛选，然后选择" autocomplete="off"   onChange="inSelect(this, GLCode1.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
	 	<datalist id="GLCode1"> ';
				while ($row=DB_fetch_array($result)){
						if (substr($row['accountcode'],0,4)!='1002'){
						echo '<option value="' . $row['accountcode'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false) . '"label=' . $row['accountcode'].'[' .$row['currcode'].']' .htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false)  . '>';
					}
				}
	echo'</datalist>'; 
	echo'<input type="hidden" name="accname" id="accname" value="' . $_POST['accname'] . '" />
		 <input type="hidden" name="GLManualCode" id="GLManualCode" value="' . $_POST['GLManualCode'] . '" />
				</td>';
       
echo '<td>
          <input type="text"  name="Debit" onchange="JorD(this,Credit,examount)" maxlength="12" size="12" value="' . $_POST['Debit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  />
	    </td>
			<td>
				<input type="text"  name="Credit" onchange="JorD(this,Debit,examount)" maxlength="12" size="12" value="' . $_POST['Credit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  />
			</td>
      </tr>';
echo '<tr>
     	<td>' . _('GL Narrative') . '</td>
			<td><input type="text" name="GLNarrative" maxlength="50" size="50" value="' . $_POST['GLNarrative'] . '" pattern="[\w\d\u0391-\uFFE5\(\)\[\]\ +$" title="输入汉字和空格！" />
				附件数<input type="text" name="documents" maxlength="5" size="5" value="'.$_POST['documents'].'"  pattern="^[1-9]*\d{1,2}?"　  title="输入3位以内正整数！"  /></td>
			<td colspan="2">';
if ($_SESSION['Currency']==1){
		echo'<input type="text" name="currcode" id="currcode"   size="1" value="' . $_POST['currcode'] . '" readonly />
				<input type="text" name="currate" id="currate"  size="2" value="' . $_POST['currate'] . '" readonly />外币:
				<input type="text"  name="examount" id="examount"  maxlength="20" size="10" value="'.$_POST['examount'].'"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！" />';
	}else{
		echo'<input type="hidden" name="currcode" id="currcode"  value="' . $_POST['currcode'] . '" />
		     <input type="hidden" name="currate" id="currate"   value="' . $_POST['currate'] . '" />
	       <input type="hidden"  name="examount" id="examount"  value="'.$_POST['examount'].'" />';
  }
     echo'</td></tr>
	  </table>';
	echo '<br />		
			<div class="centre">
			<input type="submit" name="CommitBatch" value="保存/缓存" />';
			if(isset($_GET['Edit'])	AND $_GET['Edit'] == 'Yes'){
				echo'<input type="submit" name="DelJournal" value="凭证删除" />';
			}
		//echo'	<input type="submit" name="debug" value="DEBUG" />';
		echo '</br></br><a href="' . $RootPath . '/GLAccounts.php?GLreturn='.urlencode($_SESSION['Journalstr']).'" >添加新科目</a>';
		if (isset($_GET['Edit'])){
			echo '</br></br><a href="' . $RootPath . '/JournalAudit.php">返回凭证审核</a>';
			//echo'<input type="submit" name="submitreturn" value="' ._('Return').'" />';
	       }
	echo'</div>';
if(count($_SESSION['JournalDetail']->GLEntries)>100) {
	echo '<br />
		<br />';
	prnMsg(_('The journal must balance ie debits equal to credits before it can be processed'),'warn');//凭证必须借贷相等才能保存/
}
echo '</div>
	</form>';
	
include('includes/footer.php');
/*
function GetJournaltype(&$JournalDetail,&$GLTypearr,$prd_){
	//判断凭证���型使用jouranltype表  类同表 transtype 2020-5-1
	$styp=0;
	$typ=-1;
	$ftyp=0;
	$flg=1;
	//if ($_SESSION['JournalDetail']->GLItemCounter >=14){
    $glrow=count($JournalDetail);//->$GLItemCounter;
	 //判断凭证是收付类型，剔除收付科目
	$journalarr=array();
	$n=0;
	foreach ($JournalDetail as $JournalItem) {
		$jd=1; 
		//$flg=$JournalItem->assetid;
		$n++;
		if (substr($JournalItem->GLCode,0,4)=='1001'||substr($JournalItem->GLCode,0,4)=='1002'){
		        if ($JournalItem->Debit!=0){
				  $styp+=1;    
			    }else{
				  $ftyp+=1;
				}
		}else{
			if ($JournalItem->Debit>0){
				  $jd=1;    
			}elseif ($JournalItem->Credit>0){
				  $jd=2	;
			}elseif ($JournalItem->Debit<0 ){
					$jd=-1;    
			}else{
					$jd=-2	;
			}    
	         array_push($journalarr,array(substr($JournalItem->GLCode,0,4),$jd,0));
		}
	}//foreach
		if ($ftyp>0){
			$sfz=2;
		}elseif ($styp>0){
			$sfz=1;
		}else{
			$sfz=3;
		}
		for($i=0;$i<count($journalarr);$i++ ){
			$journalarr[$i][2]=$sfz;
		}
		$journalarr=array_unique($journalarr);
		//return $journalarr;
	  //转户凭证取号
	  $typarr=array(); 
	if($ftyp+$styp==$glrow && $glrow>1){
		$typ=6;
	
			$typarr=array($typ,0);
		//}
       // $typarrs=$typ.'='.$ftyp.'+'.$styp.'=]'.$glrow;
		return $typarr;
	}
	  //判断凭证类型
	  //return $GLTypearr;
	  $typidarr=array();
	  if ($glrow>1){
          for($i=0;$i<count($Journalarr);$i++){
			  if($Journalarr[$i][0]=='2221'){
				  unset($Journalarr[$i][0]);
				  $glrow=($glrow-1)*-1;
			  }
		  }
	  }
	  $n=0;
	if($sfz==1){//收付凭证
		foreach($journalarr as $key=>$val){
		    reset($GLTyparr);
			for($i=0;$i<count($GLTypearr); $i++){
				if ($GLTypearr[$i][1]=='1001' && ($GLTypearr[$i][4]==1||$GLTypearr[$i][4]==3 )){
					//$cot=strpos($GLTypearr[$i][2],$val[0]);
					//$typid.=';'.$GLTypearr[$i][2].'-'.$val[0];//$cot.'-'.$GLTypearr[$i][0];
					if (strpos($GLTypearr[$i][2],$val[0])!==false){
						//$n++;
						array_push($typidarr,$GLTypearr[$i][0]);
						unset($journalarr[$key]);
						break;
					}
				}
			}
		}
		//return $typid.'='.$n;
		if (empty($typidarr)){
			$typ=-12;
		}else{
			$typidarr=array_unique($typidarr);
			if(count($typidarr)==1){
				$typ=$typidarr[0];
			}else{
				$typ=-12;
			}
		}
	}elseif($sfz==2){
		foreach($journalarr as $key=>$val){
			for($i=0;$i<count($GLTypearr); $i++){
				if ($GLTypearr[$i][1]=='1001' && ($GLTypearr[$i][4]==2||$GLTypearr[$i][4]==3 )){
					//	$cot=strpos($GLTypearr[$i][3],$val[0]);
					if (strpos($GLTypearr[$i][2],$val[0])!==false){
						array_push($typidarr,$GLTypearr[$i][0]);
						unset($journalarr[$key]);
						break;
					}
				}
			}
		}
		if (empty($typidarr)){
			$typ=-22;
		}else{
			$typidarr=array_unique($typidarr);
			if(count($typidarr)==1){
				$typ=$typidarr[0];
			}else{
				$typ=-22;
			}
		}
	}else{//转账凭证
		foreach($journalarr as $row){
			for($i=0;$i<count($GLTypearr); $i++){
				if ($GLTypearr[$i][1]!='1001' && $GLTypearr[$i][4]>=4 ){
					if (strstr($GLTypearr[$i][1].','.$GLTypearr[$i][2],$row[0])){
						array_push($typidarr,$GLTypearr[$i][0]);
						unset($journalarr[$key]);
						break;
					}
				}
			}
		}
		if (empty($typidarr)){
			$typ=0;
		}else{
			$typidarr=array_unique($typidarr);
			if(count($typidarr)==1){
				$typ=$typidarr[0];
			}else{
				$typ=0;
			}
		}
   }
	//$result=DB_query("SELECT  typeno FROM gltrans WHERE  type=".$typ." AND periodno=".$prd_);
	//$row=DB_fetch_row($result);
	//if (empty($row)){
	//	$typarr=array($typ,1);
	//}else{
		$typarr=array($typ,0);
	
	return $typarr;
} */

/**
 * 内外账启用  
 * 原来使用$_SESSION['Tag]==-1  现在没有使用
 * 外币开启  $SESSION['Currency']==1  开启外币录入
 */
?>
