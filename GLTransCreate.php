

<?php

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-04-22 02:21:07
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-07-14 07:45:04
 */
include('includes/session.php');
include('includes/JournalEntryClass.php');

//include('includes/GLSubject.php');
include ('includes/GLAccountFunction.php');
echo'<script type="text/javascript">
		function ComboToInput(c, i,u) {
			i.value=c.value.split("^")[0];
			u.value=c.value.split("^")[1];
			document.getElementById("accname").value=c.value.split("^")[2];
		    document.getElementById("currate").value=getrate(c.value.split("^")[1]);			
		}
		function JorD(o, t, e) {
			if(o.value!="") {
				t.value="";
				e.value=(o.value*document.getElementById("currate").value).toFixed(2);
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

include('includes/SQL_CommonFunctions.inc');
$Title ='生成会计凭证';
$ViewTopic = 'GeneralLedger';
$BookMark = 'JournalEntry';
include('includes/header.php');
if (isset($_SESSION['SelectBank'][3])&&  $_SESSION['SelectBank'][3]>0){
	echo "72-----72<br/>";
	unset($_SESSION['SelectBank'][4]);
	unset($_SESSION['SelectBank']);
	unset($_SESSION['GLTransCreate']->GLEntries);
	unset(	$_SESSION['GLTransCreate']);
	unset( $_SESSION['GetUrl_Trans']);
    header("location:CashJournallize.php");
}

//exit;
	//打印封账限录入
/*
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
		prnMsg('你���择的会计期间已经打印封账,不能录入凭证或修改！','warn');
		echo '</form>';
		include('includes/footer.php');
		exit;
	}
	*/	
	var_dump($_SESSION['SelectBank']);
////->GLEntries);
		     //xit;
	//array(4) { [0]=> string(7) "1002101" [1]=> string(3) "CNY" [2]=> string(1) "1" [3]=> string(20) "08506201090000000097" } 
if (isset($_GET['GLPrm'])){
		/*array(17) { ["BankTransID"]=> string(4) "4457" ["BankDate"]=> string(19) "2020-03-06 13:34:48" ["ToAccount"]=> string(18) "817840501421002568" ["ToName"]=> string(57) "威海临港经济技术开发区国库集中支付中心" ["CurrCode"]=> string(3) "CNY" ["Amount"]=> string(8) "16800.00" ["ExAmount"]=> string(1) "0" ["flg"]=> string(1) "1" ["Remark"]=> string(51) "年中央工业企业结构调整（稳定就业奖" ["Abstract"]=> string(9) "自定义" ["ToAct"]=> string(0) "" ["ToActName"]=> string(0) "" ["ToCurrCode"]=> string(3) "CNY" ["decimalplaces"]=> NULL ["RegID"]=> int(0) ["ToActType"]=> int(0) [
			["ToBank"]=> string(44) "2020-03-11 10:46:47^26293.48^4476^CNY^100202" }  }
		   array(3) { [0]=> array(4) { [0]=> string(7) "1002101" [1]=> string(3) "CNY" [2]=> string(1) "1" [3]=> string(20) "08506201090000000097" } 
				 	  [1]=> string(2) "16" 
					  [2]=> string(10) "2019-04-30" } 	*/
	//$ROW=explode('^',urldecode($_GET['GLPrm']));
	$ROW=json_decode(str_replace('\"','"',urldecode($_GET['GLPrm'])),JSON_UNESCAPED_UNICODE);
	//var_dump($ROW);
	$tag=$ROW["tag"];
	if (isset($_GET['ToActPrm'])){
	
		$ToActArr=json_decode(str_replace('\"','"',urldecode($_GET['ToActPrm'])),JSON_UNESCAPED_UNICODE);
	}
	//echo"<br/>";
	//var_dump($ToActArr);
	$CurrToType=-1;
	$ToActType=$ROW['TransType']['ToActType'];//9转户  个人1  单位2	
	$prd=$_SESSION['SelectBank'][1];
	$tag=$_SESSION['SelectBank'][0][2];	

	$ActCode=$_SESSION['SelectBank'][0][0];	
	$CurrCode=$_SESSION['SelectBank'][0][1];
	$ToAct=$ROW['ToAct'];
	$ToActName=$ROW['ToActName'];
	$ToCurrCode=$ROW['ToCurrCode'];
	$BankTransID=$ROW["BankTransID"];
	$TransDate=date('Y-m-d',strtotime($ROW['BankDate']));

	if (!empty($ToActArr)){
		$ToAct=$ToActArr["ToAct"];
		//echo  '<br>[134-='.$ToAct;
	}
	if (!empty($ROW['ToBank'])){
		
		$ToBankVal=explode("^",$ROW['ToBank']);//2020-03-11 10:46:47^26293.48^4476^CNY^100202" }
		$ToAct=$ToBankVal[4];
		//echo  '<br>[139-='.$ToAct;
		$ToActName='';
		$ToCurrCode=$ToBankVal[3];
	}
    $_SESSION['GetUrl_Trans']='?GLPrm='.urlencode(json_encode($ROW,JSON_UNESCAPED_UNICODE)); 
	if (isset($ToActArr)){
		$_SESSION['GetUrl_Trans'].='&ToActPrm='.urlencode(json_encode($ToActArr,JSON_UNESCAPED_UNICODE)); 
	}	
	//var_dump($_SESSION['SelectBank']);
}else{
	unset($ROW);
	prnMsg('页面引导错误！','info');
	echo "<script>window.close();</script>";
	//include('includes/footer.php');
	exit;
}
    $checkflg=0;//检查是否有全部科目未知科目=1
	/*
	if (isset($_GET['NewJournal'])	AND $_GET['NewJournal'] == 'Yes'	AND isset($_SESSION['GLTransCreate']) ){
		unset($_SESSION['GLTransCreate']->GLEntries);
		unset($_SESSION['GLTransCreate']);
	}
	*/

		//读取外币汇率
    if ($_SESSION['Currency']==1){
		$result=DB_query("SELECT currabrev, ROUND(rate,decimalplaces) rate  FROM currencies  WHERE currabrev!='".$_SESSION['CompanyRecord'][abs($ROW['tag'])]['currencydefault']."'");
		$curratearr=array();
		$i=0;
		while ($row=DB_fetch_array($result)){

			$curratearr[$i]=array('currabrev'=>$row['currabrev'],'rate'=>$row['rate']);
			$i++;
		}
			$ratejsn=json_encode( $curratearr);	   
	}
	//echo '-=168='.$_SESSION['SelectBank'][4].'!='.$ROW['BankTransID'];

	
	//echo .'!='.$ROW['BankTransID'];
	if (isset($_SESSION['GLTransCreate'])&& $_SESSION['GLTransCreate']->TransID!=$ROW['BankTransID']){
	prnMsg($BankTransID."---Total[".($_SESSION['GLTransCreate']->JournalTotal.' ]counter['. $_SESSION['GLTransCreate']->GLItemCounter)."]===".$_SESSION['GLTransCreate']->TransID);
		//if ( (isset($_GET['GLPrm']) && $_SESSION['GLTransCreate']->TypeNo!=$ROW['BankIndex'])){
		  
			//unset($_SESSION['GLTransCreate']->GLEntries);
			//unset($_SESSION['GLTransCreate']);		
		//}	
	}
	//echo $BankTransID."---".$_SESSION['SelectBank'][4]."比对发票号码===".$_SESSION['GLTransCreate']->TransID;
	if (!isset($_SESSION['GLTransCreate'])){//||$_SESSION['GLTransCreate']->TransID==0){//(isset($_SESSION['GLTransCreate']) &&  $_SESSION['SelectBank'][4]!=$BankTransID)||empty($_SESSION['GLTransCreate']->TransID)){
		$_SESSION['GLTransCreate']= new Journal;
		$_SESSION['SelectBank'][4]=$BankTransID;
		echo "194=======================194";
	}
	//
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
	$inputerr=0;
	$JournalNo=1;
	$dlsv=0;
	if (isset($_GET['Delete'])||isset( $_POST['CommitBatch'])){
		$dlsv=1;
	}	
	$sql = "SELECT	bankaccounts.accountcode,
					bankaccounts.bankaccountnumber,
					bankaccounts.currcode,
					chartmaster.tag,
					chartmaster.accountname
				FROM bankaccounts
				LEFT JOIN chartmaster ON chartmaster.accountcode = bankaccounts.accountcode,
					bankaccountusers
					WHERE bankaccounts.accountcode=bankaccountusers.accountcode
					ORDER BY bankaccounts.accountcode";
		
	$result = DB_query($sql);
	//$BankAct=array();
	//$accountbank=array();
	while ($myrow=DB_fetch_array($result)){
		$BankAct[$myrow['bankaccountnumber']][]=$myrow['accountcode'];
		$Accountbank[$myrow['accountcode']][]=array($myrow['accountname'],$myrow['currcode']);
	}

	$SQL="SELECT `accountcode`, `accountname`, `currcode` 
	       FROM `chartmaster`
	        WHERE length(accountcode)>4 AND currcode<>'".$_SESSION['CompanyRecord'][abs($ROW['tag'])]['currencydefault']."'";
	$Result = DB_query($SQL);
	//$AccCurr=array();	
	while ($row=DB_fetch_array($Result)){
		
		$AccCurr[$row['accountcode']]=$row['currcode'];
	} 
	if (!isset($GLTypeArr)||count($GLTypeArr)==0){
		$result=DB_query("SELECT typeid, account, toaccount, len, gltype FROM journaltype");
		$GLTypeArr=array();
		
		while($row=DB_fetch_array($result)){
		  array_push($GLTypeArr,array($row['typeid'],$row['account'],$row['toaccount'],$row['len'],$row['gltype']));
		}
	}  

if ($_SESSION['GLTransCreate']->JournalTotal  ==0 && $_SESSION['GLTransCreate']->GLItemCounter ==0 && count($_SESSION['GLTransCreate']->GLEntries )==0 ){
	echo '<br/>255-='.($_SESSION['GLTransCreate']->JournalTotal.' =]'. count($_SESSION['GLTransCreate']->GLEntries ).'[='. $_SESSION['GLTransCreate']->GLItemCounter );
	if(isset($_GET['GLPrm']) && $dlsv==0){
					
		if ($ToActType==9){
			//转户
			if (count($ToBankVal)<3){
				$post=0;
			}
			//if ($CurrCode==CURR){//本币账��
					
			if (trim($CurrCode)==CURR  ){
				//本币->本币
				$CurrToType=0;
				$CurrAmount=0;
				$Amount=round($ROW['Amount'],POI);
				/*
				if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){
					$amoj=$Amount;
					$amod=0;
				}else{
					$amoj=0;
					$amod=-$Amount;
				}*/
				$narrative="内部转户";
				if ($post==1){
					$SQL="UPDATE banktransaction SET  type=".$typetrans." ,transno ='".$transno."' ,period='".$prd."'  WHERE transno=0 AND banktransid='".$ToBankVal[2]."'";
					$result=DB_query($SQL);	

				}
			}elseif (trim($ToCurrCode)==$CurrCode ){
				$CurrToType=3;//外币对外币-同种;
				$Amount=round($ROW['ExAmount'],2); //兑换���币金额  需要根据系统汇率计算			
				$CurrAmount=round($ROW['Amount'],POI);  //外币金额
				$differences=0;//汇率差异，根据系统汇率和实际兑换计算
				$narrative="外币转户[".trim($ToCurrCode) .$CurrAmount."]";
				
			}elseif (trim($CurrCode)!=CURR ){
				//本币对外币;
				$CurrToType=1;
				$Amount=$ROW['Amount'];//本币金额
				$CurrAmount=round($ToBankVal[1],POI);//外币金额
				$differences=0;//汇率差异，根据系统汇率和实际兑换计算				
				$narrative="外币转户[".trim($ToCurrCode).$CurrAmount."]";
				//$rate=round(abs($CurrAmount/$Amount),4);
				prnMsg('//本币->外币');
			}
			if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){
				$amoj=$Amount;
				$amod=0;
			}else{
				$amoj=0;
				$amod=-$Amount;
			}
		
				
		}else{
			//收付款   本币
			if ($CurrCode==CURR){			
				//本币
				$CurrToType=0;
				$Amount=round($ROW['Amount'],2);			
				if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){
					$amoj=$Amount;
					$amod=0; 
					$narrative="收款";				
				}else{			
					$narrative="付款";	
					$amoj=0;
					$amod=-$Amount;			
				}	
				
				
			}else{
				//收付款 外币
				$CurrToType=1;				
				$CurrAmount=round((float)$ROW['Amount'],2);//外币原币
				$Amount=round($ROW['ExAmount'],2);  //按标准折算本币
				if (($Amount>0 && $ROW['flg']==1)||($Amount<0 && $ROW['flg']==-1)){		
					$amoj=$Amount;
					$amod=0; 
					$narrative="收外币[".$CurrCode.$CurrAmount."]";
								
				}else{		
					$narrative="付外币[".$CurrCode.$CurrAmount."]";
					$amoj=0;
					$amod=-$Amount;	
								
				}	
				//$rate=round(abs($CurrAmount/$Amount),4);		
				//prnMsg($Amount.'-'.$CurrAmount.'[外币]'.$narrative.'['.$ROW['ToAct'].']'.$prd);	
			}
		
			if ($ROW['ToName']!='')
				$narrative.=' '.$ROW['Remark'].' '.$ROW["Abstract"].' '.$ROW['ToName'];				
		}
		//echo'<br/>345-=' .( $ToCurrCode.' !'.$Amount.'='.$CurrAmount.'='.$CurrCode.'['.$ToAct );	
		$_POST['JournalDate']=$TransDate;	
	
	    $_SESSION['GLTransCreate']->JnlDate=$TransDate;
		$_SESSION['GLTransCreate']->Period=$prd;	
		
		$_SESSION['GLTransCreate']->tag=$tag;	
		$_SESSION['GLTransCreate']->TransID=$BankTransID;//$ROW['BankTransID'];
		if ($ROW['RegID']>0){
			$RegID=$ROW['RegID'];
		}
			$_SESSION['GLTransCreate']->Add_To_GLAnalysis($amoj,
															$amod,
															$narrative,
															$ActCode,
															'',
															$tag,
															'1',
															$CurrAmount,
															$CurrCode
														    );
			$_SESSION['GLTransCreate']->Add_To_GLAnalysis( $amod,
															$amoj,
															$narrative,
															$ToAct,
															$ToActName,
															$tag,
															'1',
															-$CurrAmount,
															$ToCurrCode,
														    $RegID);
		  	
		//echo $_SESSION['GLTransCreate']->TransID."----381";
	}
	
}   
var_dump($_SESSION['GLTransCreate']);  ItemCounter );
 //var_dump($_SESSION['GLTransCreate']);
/*
		if (!isset($_POST['JournalDate'])){
			if (date('Y-m')==date('Y-m',strtotime($_SESSION['lastdate']) )){
				$_POST['JournalDate']=date('Y-m-d');

			}else{
				$_POST['JournalDate']=$_SESSION['lastdate'];	
			}
		}	 
		*/
	if (isset($_SESSION['GetUrl_Trans']) AND strlen($_SESSION['GetUrl_Trans'])>3){
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .$_SESSION['GetUrl_Trans']. '"  method="post" name="form">';
	}else{
	    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" name="form">';
    }
     //  <input type="hidden" id="JournalDate" name="JournalDate" value="'.$_POST['JournalDate'].'" />
	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />	    
		  <input type="hidden" id="ratejsn" name="ratejsn" value=' . $ratejsn . ' />';
	echo '<p class="page_title_text">
				<img src="'.$RootPath.'/css/'.$Theme.'/images/maintenance.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title.'</p>';  
				 
	if (isset($_POST['JournalDate'])){			
		$_SESSION['GLTransCreate']->JnlDate=$_POST['JournalDate'];
	}elseif(isset($ROW['BankDate'])){
		$_POST['JournalDate']=date('Y-m-d',strtotime($ROW['BankDate']));
	}
    $JournalNo=1;//?没有使用
    $r=1;
//按保存按钮执行以下
if ((isset($_POST['CommitBatch']) AND $_POST['CommitBatch']=="保存/缓存")||(isset($_POST['savemode']) AND $_POST['savemode']=="保存/模板")){
	//unset($_SESSION['GetUrl_Trans']);
	//header("Refresh:1;url=' . $RootPath . '/CashJournallize.php");
	 // header("location:' . $RootPath . '/CashJournallize.php");
	 var_dump($_SESSION['GLTransCreate']);
   prnMsg("418>>>>". $_SESSION['GLTransCreate']->GLItemCounter );
	//if ((isset($_POST['CommitBatch']) AND $_POST['CommitBatch']=="保存/缓存")  && isset($_GET['ToActPrm'])){
	
		if (isset($_GET['ToActPrm']) && $_POST['GLManualCode']==''){
		    prnMsg("422--ToActPrm");
		     //var_dump($ToActArr);
			if (strlen($ToActArr['ToAct'])>4){
				foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
					if ($JournalItem->GLCode==''){
						$GLItem[]=array("ID"=>$JournalItem->ID,"GLCode"=>$JournalItem->GLCode,
						"Debit"=>$JournalItem->Debit,  "Credit"=>$JournalItem->Credit, "Narrative"=>$JournalItem->Narrative, "GLActName"=>$JournalItem->GLActName, "tag"=>$JournalItem->tag, "Exrat"=>$JournalItem->Exrat, "Examount"=>$JournalItem->Examount, "Currcode"=>$JournalItem->Currcode);
						$_SESSION['GLTransCreate']->remove_GLEntry($JournalItem->ID);
						$_SESSION['GLTransCreate']->GLItemCounter++;
					}	
					//prnMsg($JournalItem->ID);				
				}
				//var_dump($GLItem);
				$GLItem[0]["GLCode"]=$ToActArr['ToAct'];
				$_SESSION['GLTransCreate']->add_To_GLAnalysis($GLItem[0]['Debit'],
																$GLItem[0]['Credit'],
																$GLItem[0]['Narrative'],
																$GLItem[0]['GLCode'],
																$GLItem[0]['GLActName'],
																$GLItem[0]['tag'],
																$GLItem[0]['CurRate'],
																$GLItem[0]['Examount'],
																$GLItem[0]['Currcode'] );

				
			}else{//选择总账科目
				//prnMsg($ROW['ToActType'].'['.$ToActArr['ToAct'].$ROW['ToName']);
				$custdata=array("customer"=> $ROW['ToName'] ,"registerno"=>'', "tag"=>1,  "bankaccount"=>$ROW['ToAccount'],"bank"=>'' ,
				"account"=>$ToAct,"flag"=>$ROW['flag'],"ToActType"=>$ROW['ToActType'],  "regid"=>0);
				
				$AccountCreate=CustomerAccountCreate($custdata,$ToActArr['ToAct'],'');
				$AccountCreate=$CreateAct[0];
				$regid=$CreateAct[1];

				if (strlen($AccountCreate)>4){
					foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
						if ($JournalItem->GLCode==''){
							$GLItem[]=array("ID"=>$JournalItem->ID,"GLCode"=>$JournalItem->GLCode,
							"Debit"=>$JournalItem->Debit,  "Credit"=>$JournalItem->Credit, "Narrative"=>$JournalItem->Narrative, "GLActName"=>$JournalItem->GLActName, "tag"=>$JournalItem->tag, "Exrat"=>$JournalItem->Exrat, "Examount"=>$JournalItem->Examount, "Currcode"=>$JournalItem->Currcode);
							$_SESSION['GLTransCreate']->remove_GLEntry($JournalItem->ID);
							$_SESSION['GLTransCreate']->GLItemCounter++;
						}	
						//prnMsg($JournalItem->ID);				
					}
					//var_dump($GLItem);
					$GLItem[0]["GLCode"]=$AccountCreate;
					$GLItem[0]['Narrative'].=$ROW['ToName'] ;
					$_SESSION['GLTransCreate']->add_To_GLAnalysis($GLItem[0]['Debit'],
																	$GLItem[0]['Credit'],
																	$GLItem[0]['Narrative'],
																	$GLItem[0]['GLCode'],
																	$GLItem[0]['GLActName'],
																	$GLItem[0]['tag'],
																	$GLItem[0]['CurRate'],
																	$GLItem[0]['Examount'],
																	$GLItem[0]['Currcode'] );

				}
			}
			//var_dump($_SESSION['GLTransCreate']->GLEntries);
		     //xit;
		}
		
		//prnMsg('//录入行不能大于15');
		if ($_SESSION['GLTransCreate']->GLItemCounter >=14){
			prnMsg(_('Document line greater than 15'));
		}	
		//正负凭证判断
		if((double)$_SESSION['GLTransCreate']->JournalTotal !==0) {
				//凭证金额自动凭证
			$_POST['GLAmount']=-$_SESSION['GLTransCreate']->JournalTotal;  			  
			$JournalNo=1;
			if ($_SESSION['Currency']!=0){
				if ( (double)$_POST['examount']==0){
					$_POST['examount']=$_SESSION['GLTransCreate']->JournalTotal*$_POST['currate'] ; 
				}
			}							
						
		}
		if ($_POST['GLManualCode']!=='' AND (double)$_POST['GLAmount']!==0){
			$inputerr=0;
			foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
				if($JournalItem->GLCode==$_POST['GLManualCode']){
					$inputerr=1;
					break;
				}
			}
			echo'511-='.$inputerr;
			if ($inputerr==0){ 
				if ($_POST['currcode']!=$_SESSION['CompanyRecord'][$ROW['tag']]['currencydefault']){
					$_POST['GLNarrative']= $_POST['GLNarrative'].'['.$_POST['currcode'].$_POST['examount'].']';
					}
				if($_POST['GLAmount']<0){
					$ExCuAmount=- $_POST['examount'];
				}	
				$_SESSION['GLTransCreate']->add_To_GLAnalysis($_POST['Debit'],
															$_POST['Credit'],
															$_POST['GLNarrative'],
															$_POST['GLManualCode'],
															$_POST['accname'],
															$ROW['tag'],
															$_POST['currate'],
															$ExCuAmount,
															$_POST['currcode'] );

				$_POST['Credit']='';
				$_POST['Debit']='';													
				$_POST['GLManualCode']='';
				$_POST['accname']='';													
				$_POST['GLAmount']=0;
				$_POST['GLCode']='';
				$_POST['currate']=1;
				$_POST['examount']=0;
				$_POST['currcode']=$_SESSION['CompanyRecord'][$ROW['tag']]['currencydefault'];
			}else{
				prnMsg('你输入的科目重复！','warn');
			}															
		}		
	//}	
	echo '514-='.($_SESSION['GLTransCreate']->JournalTotal.' ==0'. $_SESSION['GLTransCreate']->GLItemCounter);
	
		//存入表[借==贷 平衡 2行以上执行]
	if(round($_SESSION['GLTransCreate']->JournalTotal,2) ==0 AND $_SESSION['GLTransCreate']->GLItemCounter >1) {								
		prnMsg("检验凭���格式");
			$debit=0;
			$credit=0;
			$rowj=0;
			$rowd=0;
			$msgerr='';
			
		//if (isset($_GET['GLPrm'])){	
			foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
				if ($JournalItem->GLCode==''){
					//科目代码为空误
					$inputerr=4;
				}
				if( is_numeric($JournalItem->Debit) &&$JournalItem->Debit!= 0) {
					if ( substr(trim($JournalItem->GLCode),0,4)=='1001' || substr(trim($JournalItem->GLCode),0,4)=='1002' ){
						$rowj++;
					}					
					$debit ++;				
				}elseif(is_numeric($JournalItem->Credit) && $JournalItem->Credit!= 0){
					if ( substr(trim($JournalItem->GLCode),0,4)=='1001' || substr(trim($JournalItem->GLCode),0,4)=='1002' ){
						$rowd++;
					}				
					$credit++;					
				}	
			}		
			//转户凭证判断
		
			if (($rowj+ $rowd)==count($_SESSION['GLTransCreate']->Accounts)){
				
				if (($rowj+ $rowd>2)&&($rowj==$rowd)){		
					$inputerr=2;
				}						
			}
			if(min($credit,$debit)>1 ){
				//多借多贷				 				
				$inputerr=3;						
			}		
			if ($inputerr==3 ||$inputerr==2){
				prnMsg('你输入的凭证格式有错误！</br>凭证格式:只能一借多贷或一贷多借，现金银行科目不能多借多贷！</br>','info');
			}
		//}
		$checkgl=0;
		//if(isset($_GET['InvPrm'])){
		foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
			if(isset($_GET['InvPrm'])){//发票
				if ($JournalItem->GLCode==$ROW['CustAct']){
					if(is_numeric( $JournalItem->Credit)&& $JournalItem->Credit!=0){
						$amt=-$JournalItem->Credit;
					}else{
						$amt=$JournalItem->Debit;
					}
					if ($ROW['InvType']==0){//进项

						if( (double)$ROW['Amount']+(double)$ROW['Tax']==- $amt){
							//应付账款检验ok
							$checkgl=1;
						}
						
					}else{//销项
						if( (double)$ROW['Amount']+(double)$ROW['Tax']==$amt){
							//应收账款检验ok
							$checkgl=1;
						}
					}
				}
			}else{//收付款
				if($ROW['Currcode']!=$_SESSION['CompanyRecord'][$ROW['tag']]['currencydefault'] ){
					//外币
					if ($JournalItem->GLCode==$ROW['Act']){
					
						if( (double)$ROW['Amount']==(double)$JournalItem->examount){
							//应账款检验ok
							$checkgl+=1;
						}
						if( $ROW['Currcode']==$JournalItem->curr){
							$checkgl+=2;
						}
						
					}

				}else{
					if ($JournalItem->GLCode==$ROW['Act']){
					
							if( (double)$ROW['ExAmoJ']+(double)$ROW['ExAmoD']==(double)$JournalItem->Credit+(double)$JournalItem->Debit){
								//应账款检验ok
								$checkgl=1;
							}								
					}
				}
			}
		}//foreach	
	 
		if($inputerr==0){		 
				//Start a transaction to do the whole lot inside
				$tagsgroup=$_SESSION['tagref'][ $JournalItem->tag][1];			
			$result = DB_Txn_Begin();				
			$TransNo =GetTransNo( $_SESSION['GLTransCreate']->Period, $db);			
			$TransType=GetTransType($_SESSION['GLTransCreate']->GLEntries ,$GLTypeArr,$_SESSION['GLTransCreate']->Period);
			$TagTypeNo = GetTagTypeNo( $tagsgroup,$_SESSION['GLTransCreate']->Period, $db);
			//$TagTypeNo =$typarr[1];			
			$msgerr='';
		
			//保存凭证
			foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
				$flg=1;
				$post=0;
				if(( is_numeric($JournalItem->Credit) && $JournalItem->Credit< 0)||(is_numeric($JournalItem->Debit) &&$JournalItem->Debit< 0)){
					
					$flg=-1;
				}
				if( is_numeric($JournalItem->Credit) && $JournalItem->Credit!=0){
					$amot=-$JournalItem->Credit;
				}else{
					$amot=$JournalItem->Debit;
				}
			
				//if ($TransType==6 &&  $JournalItem->GLCode==$ROW['Act']  && ( $ROW['ToActType']==4||$ROW['ToActType']==2||
				if($ROW['ToActType']==9){
					//if  ( isset($ToActArr)){
						$SQL="UPDATE banktransaction SET transno='" . $TransNo . "',type='".$TransType."',period='" .  $_SESSION['GLTransCreate']->Period  . "' WHERE banktransid='".$ToBankVal[2]."'";
				    	$res = DB_query($SQL);
						$post=1;
					//}else{
					//	$msgerr="转户对应凭证没有��联写入！";
					//}

				}
				//下面代码为收账款、应付账款录入
				if  (substr($JournalItem->GLCode,0,4)=='1122'){ 
				
					$sql="INSERT INTO debtortrans( transno,
													type,
													debtorno,
													branchcode,
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
														'" . $TransType . "',
														'" .  $JournalItem->RegID . "',
														'0',
														'" . FormatDateForSQL($_SESSION['GLTransCreate']->JnlDate) . "',
														'" . FormatDateForSQL($_SESSION['GLTransCreate']->JnlDate) . "',
														'" . $_SESSION['GLTransCreate']->Period . "',
														'0', '0', '0',  '0', 
														'" . (is_numeric($JournalItem->Exrat)?$JournalItem->Exrat:1)  . "',
														'" . $amot . "',
														'0',  '0',  '0',  '0',  '0',  '','0','0','0','1',''	) ";
						//prnMsg($sql);
						$result = DB_query($sql);
						if ($result){
							$post=1;
						}				      
						//$msgerr=',应收账款对应的客户异常！';				
				}elseif  (substr($JournalItem->GLCode,0,4)=='2202'){ 
					/*
					$sql="SELECT  unitscode, branchcode FROM accountunits WHERE account='" . $JournalItem->GLCode . "' ";
				
					$result = DB_query($sql);
					if (DB_num_rows($result)==1){
					$row=DB_fetch_row($result);
					*/
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
													'" . $TransType . "',
													'" . $_SESSION['GLTransCreate']->Period . "',
													'" . $JournalItem->RegID . "',
													'" .  $JournalItem->RegID. "',
													'" . FormatDateForSQL($_SESSION['GLTransCreate']->JnlDate) . "',
													'" . FormatDateForSQL($_SESSION['GLTransCreate']->JnlDate) . "',
													'0', 
													'" . (is_numeric($JournalItem->Exrat)?$JournalItem->Exrat:1) . "',
													'" . $amot . "',
													'0',  '0',  '0', '') ";
			
						$result = DB_query($sql);						
					//		$msgerr=',应付账款对应的客户异常！';
					if ($result){
						$post=1;
					}				
					
				
				}
				
				if ( $JournalItem->Examount!=0 && $JournalItem->Currcode!=$_SESSION['CompanyRecord'][$ROW['tag']]['currencydefault'] ){
			
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
										VALUES ('" . FormatDateForSQL($_SESSION['GLTransCreate']->JnlDate) . "',
												'" . $TransNo . "',
												'" . $_SESSION['GLTransCreate']->Period . "',
												'" . $JournalItem->GLCode . "',
												'" . abs(round($JournalItem->Examount/$amot,4)) . "',
												'" . $amot . "',
												'" . $JournalItem->Examount . "',
												'0',
												'" . $JournalItem->Currcode . "',
												'" . $flg."' )";
							$ErrMsg = _('Cannot insert a  transaction because');
							$DbgMsg = _('Cannot insert a  transaction with the SQL');
							$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);	
					
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
											posted)
									VALUES (  '" . $TransType . "',
											'" . $TransNo . "',
											'" . $TagTypeNo . "',
											'".$_POST['documents']."',
											'" . FormatDateForSQL($_SESSION['GLTransCreate']->JnlDate) . "',
											'" . $_SESSION['GLTransCreate']->Period . "',
											'" . $JournalItem->GLCode . "',
											'" . $JournalItem->Narrative  . "',
											'" . $amot . "',
											'" . $JournalItem->tag."',
											'" . $flg."' ,
											'".$_SESSION['UserID']."',
											'" . $post."' )";
				
				$ErrMsg = _('Cannot insert a GL entry for the journal line because');
				$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
				$r++;	
				prnMsg($SQL);			
				$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}
			
				if (isset($_GET['GLPrm'])){
					//银行账户	
					$sql="UPDATE banktransaction SET type='".$TransType."',transno='".$TransNo."',period='". $_SESSION['GLTransCreate']->Period ."'  WHERE banktransid=".$ROW['BankTransID'];
					
					$result = DB_query($sql);
					$_SESSION['SelectBank'][3]=$TransNo;//凭证成功标记
				}
			
				$result= DB_Txn_Commit();
		
			if(isset($_POST['savemode']) AND $_POST['savemode']=="保存/模板"){
				//没有使用	
				$accstr='';
				/*
				foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
					if ($ROW['Act']!=$JournalItem->GLCode){
						if ($accstr=='') {
							$accstr.= $JournalItem->GLCode;

						}elseif(strlen($JournalItem->GLCode)>6){
							$accstr.=','.$JournalItem->GLCode;	
						}
					}

				}
				if (isset($_GET['InvPrm'])){//发票
					$sql="UPDATE registeraccount SET subject='".$accstr."'WHERE registerno='".$ROW[0]."'";
					DB_query($sql);
					
				}else{
					if($_GET['ty']==2){//收款
						$sql="UPDATE accountsubject SET subject='".$accstr."' WHERE bankaccount='".$ROW['ToAccount']."'";
						DB_query($sql);	
					}
				}*/
				
			}
			unset($_POST['JournalProcessDate']);
			unset($_POST['JournalType']);
			//开发关闭
			unset($_SESSION['GLTransCreate']->GLEntries);
			unset($_SESSION['GLTransCreate']);
			unset($_SESSION['GetUrl_Trans']);
			
			$JournalNo=1;
			//unset($typarr);
			unset($edittranarr);
			DB_free_result($result);
		} 
		
		//Set up a newy in case user wishes to enter another 
		echo '<br />';
		prnMsg($_POST['JournalDate'].'会��凭证 记 ' . $TransNo . ' '._('has been successfully entered').', '.$msgerr,'success');
		echo '<meta http-equiv="refresh" content="1"/>';
		//echo '<meta http-equiv="Refresh" content="3"; url=' . $RootPath . '/CashJournallize.php">';		
		
		/*
		echo '<script type="text/javascript">
							window.opener=null;
							window.open("","_self"); 
							window.close();
							</script>';
							*/
							/*
		if($_GET['ty']==3){//
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/GLSettleAccounts.php">';
		}*/
		include ('includes/footer.php');
		/*
		if ($TransNo>0){
			//sleep(10);
			$TransNo=0;
			$JournalNo=1;
			//unset($_SESSION['GLTransCreate']->GLEntries);
		//	unset($_SESSION['GLTransCreate']);
			unset($_SESSION['GetUrl_Trans']);
		}*/
		exit;  
	}
}elseif (isset($_POST['cash'])) {
	//	echo "<script>window.close();</script>";
}elseif (isset($_GET['Delete'])){  
	
	//删除凭证

	$_POST['GLManualCode']=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->GLCode;
	$_POST['Debit']=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->Debit;
	$_POST['Credit']=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->Credit;
	
	$_POST['GLNarrative']=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->Narrative ;
	$_POST['accname']=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->GLActName;
	$tag=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->tag;
	$_POST['currate']=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->Exrat;
	$ExCuAmount=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->Examount;
	$_POST['currcode']=$_SESSION['GLTransCreate']->GLEntries[$_GET['Delete']]->Currcode;

	$_SESSION['GLTransCreate']->Remove_GLEntry($_GET['Delete']);
		
}elseif(isset($_POST['savemode'])){
	unset($_POST['JournalProcessDate']);
	unset($_POST['JournalType']);
	unset($_SESSION['GLTransCreate']->GLEntries);
	unset($_SESSION['GLTransCreate']);	
}
if ($ROW['ToActType']==9){
	echo '<div class="page_help_text">';
	if (!empty($ROW['ToBank'])){
		echo '内部转户交易[币种：'.$ToBankVal[3].']<br>
		      交易���期:'.$ToBankVal[0] .'序号:'.$ToBankVal[2].'  ';				
	
		echo '金额：'.$ToBankVal[1];
	}else{
		echo '<br/>内部转户对应交易没有查找到,可能原因没有对应交易或汇率设置实际交易差别超过+-10%！';
	}
				
	echo'</div>';	
}
if (isset($_GET['ToActPrm'])){
	
	echo '<div class="page_help_text">
	单位名称：'.$ROW['ToName'].$ROW['ToAccount'].'<br>
	你选择了'.$ToActArr['ToAct'].'-'.$ToActArr['ToActName'].' </br>
		
	
		</div>';
}
echo '<table class="selection" width="700">
		<tr>
			<th colspan="7"><h3>'.$_SESSION['CompanyRecord'][$ROW['tag']]['unitstab'].' 会计凭证</h3></th>
	    </tr>
		<tr> 
			<th colspan="7"  class="centre" >	' . _('Date to Process Journal') . ':
			<input type="date" required="required"  alt="" min="'.substr($_POST['JournalDate'],0,8).'01" max="'.$_POST['JournalDate'].'"  name="JournalDate" maxlength="10" size="11" value="' . $_POST['JournalDate']. '" /></td>
			</th>
		</tr>
		<tr>
			<th width="10">' . _('Sequence') . '</th>
			<th width="300">' . _('GL Account') . '</th>';
	if ($_SESSION['Currency']==1){
		echo'<th width="110">外币金额</th>';
	}
    echo'<th width="110">' . _('Debit') . '</th>
		 <th width="110">' . _('Credit') . '</th>
		 <th width="150" >' . _('Narrative') . '</th>
		 <th width="20">操作</th>
		</tr>';
		$codestr='';
		//print_r($_SESSION['GLTransCreate']->GLEntries);
		foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
			if ($JournalItem->GLCode!=''){
				if ($codestr==''){
					$codestr.=$JournalItem->GLCode;
				}else{
					$codestr.=','.$JournalItem->GLCode;
				}
			}
		}
		$acc_name=array();
		if ($codestr!=''){
			$sql="SELECT accountcode, accountname FROM chartmaster WHERE accountcode IN ( ".$codestr." )";
			$result=DB_query($sql);
		
			while($row=DB_fetch_array($result)){
				$acc_name[$row['accountcode']]=$row['accountname'];
			}
	    }

	$DebitTotal=0;
	$CreditTotal=0;
	$j=0;
	//显示已录入数据
	//var_dump($_SESSION['GLTransCreate']->GLEntries[1]);
	foreach ($_SESSION['GLTransCreate']->GLEntries as $JournalItem) {
			if ($j==1) {
				echo '<tr class="OddTableRows">';
				$j=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$j=1;
			}		
		echo '<td>' . $r  . '</td>';
		echo' <td>' . $JournalItem->GLCode . ' ' . $acc_name[ $JournalItem->GLCode] . '</td>';
		if ($_SESSION['Currency']==1){
			echo'<td  class="number">' . locale_number_format($JournalItem->Examount,$_SESSION['CompanyRecord'][$_SESSION['GLTransCreate']->tag]['decimalplaces']) . '</td>';
	    }
   		echo'<td class="number">' .locale_number_format($JournalItem->Debit,$_SESSION['CompanyRecord'][$_SESSION['GLTransCreate']->tag]['decimalplaces']) . '</td> 
		     <td class="number">' . locale_number_format($JournalItem->Credit,$_SESSION['CompanyRecord'][$_SESSION['GLTransCreate']->tag]['decimalplaces']) . '</td>';
			$CreditTotal+=$JournalItem->Credit;
			$DebitTotal += $JournalItem->Debit;   
	    echo '<td>' . $JournalItem->Narrative  . '</td>';
   		    //if ($JournalItem->ID!=0||$_SESSION['Currency']!=0 &&  $JournalItem->Currcode !=$_SESSION['CompanyRecord'][$_SESSION['GLTransCreate']->tag]['currencydefault'] ){	
			if (isset($_SESSION['GetUrl_Trans']) AND strlen($_SESSION['GetUrl_Trans'])>3){
					echo '<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . $_SESSION['GetUrl_Trans']. '&Delete=' . $JournalItem->ID . '">' . _('Delete') . '</a></td>';
			}else{
					echo '<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete=' . $JournalItem->ID . '">' . _('Delete') . '</a></td>';
					
			}		
	    echo '</tr>';
	    $r++;
	}
     //prnMsg($r);
   //合计
echo '<tr class="EvenTableRows">       
        <td></td>
		<td class="number"><b>' . _('Total') .  '</b></td>';
if ($_SESSION['Currency']!=0){
	echo'<td class="number"><b></b></td>';
}
echo'<td class="number"><b>' . locale_number_format($DebitTotal,$_SESSION['CompanyRecord'][abs($ROW['tag'])]['decimalplaces']) . '</b></td>
	 <td class="number"><b>' . locale_number_format($CreditTotal,$_SESSION['CompanyRecord'][abs($ROW['tag'])]['decimalplaces']) . '</b></td>
	   <td></td>
		 <td></td>
	</tr>';

if ($DebitTotal!=$CreditTotal) {
	echo '<tr><td colspan="6" align="center" style="background-color: #fddbdb"><b>' . _('Required to balance') .': </b>' .
		locale_number_format(abs($DebitTotal-$CreditTotal),$_SESSION['CompanyRecord'][abs($ROW['tag'])]['decimalplaces']);
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

//<input type="text" autofocus="autofocus" name="GLManualCode" id="GLManualCode" maxlength="12" size="12"  onchange="inSelect(this, GLCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" value="'. $_POST['GLManualCode'] .'" pattern="^[1-6]\d{1,19}"  title="输入明细科目编码如:1001101" /></td>';

 if ( $checkflg==1||$ROW['Currcode']!=$_SESSION['CompanyRecord'][$tag]['currencydefault']){
	 if ( $checkflg==1){
	
    $sql="SELECT t3.accountcode,t3.currcode,
	             t3.accountname
			 FROM chartmaster t3 
	 		 WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
		( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and (t3.tag='".$ROW['tag']."' or t3.tag='0' ) AND used>=0 AND t3.accountcode<>'".$acc."' ORDER BY t3.accountcode";
	 }else{
		$sql="SELECT t3.accountcode,t3.currcode,
		t3.accountname
	FROM chartmaster t3 
	 WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) ))  AND used>=0  ORDER BY t3.accountcode";

	 }
  }else{
    //默认
	$sql="SELECT t3.accountcode,t3.currcode,
	             t3.accountname 
			FROM chartmaster t3 WHERE t3.accountcode NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
				( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) AND used>=0 AND LEFT(t3.accountcode,4)  IN ('6602','6601','6603','5101','5001',1403,1405,1601,1604,2221,2211) order by t3.accountcode";

   }

$result=DB_query($sql);

echo'<tr><td colspan="2">
     <input type="text" name="GLCodeName"  id="GLCodeName"   list="GLCode"   maxlength="100" size="70"  onChange="inSelect(this, GLCode.options,'.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
	 <datalist id="GLCode"> ';
	while ($row=DB_fetch_array($result)){
		echo '<option value="' . $row['accountcode'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false) . '"    label="' . $row['accountcode'].'[' .$row['currcode'].']' .htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false)  . '" />';
	}

echo'</datalist> 
	<input type="hidden" name="accname" id="accname" value="' . $_POST['accname'] . '" />
	<input type="hidden" name="GLManualCode" id="GLManualCode" value="' . $_POST['GLManualCode'] . '" /></td>';
 
echo '<td><input type="text"  name="Debit" onchange="JorD(this,Credit,examount)" maxlength="12" size="12" value="' . $_POST['Debit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  /></td>
	  <td><input type="text"  name="Credit" onchange="JorD(this,Debit,examount)" maxlength="12" size="12" value="' . $_POST['Credit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  /></td>
      </tr>';

echo '<tr>
     	<td>' . _('GL Narrative') . '</td>
		<td><input type="text" name="GLNarrative" maxlength="50" size="50" value="' . $_POST['GLNarrative'] . '" pattern="[\w\d\u0391-\uFFE5\(\)\" "\（\）]+$" title="输入汉字和空格字符" />
		附件数<input type="text" name="documents" maxlength="5" size="5" value="'.$_POST['documents'].'"  pattern="^[1-9]*\d{1,2}?"　  title="输入3位以内正整数！"  /></td>
		<td colspan="2">';
	if ($_SESSION['Currency']!=0){
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
			//	<input type="submit" name="savemode" value="保存/模板" />';
			
		
	echo'</div>';
	echo '<p><a href="'. $RootPath . '/CashJournallize.php"  title="点击生成或查找已经制作的会计凭��！"  >返回</a></p>';
/*
if(count($_SESSION['GLTransCreate']->GLEntries)>1) {
	echo '<br />
		<br />';
	prnMsg(_('The journal must balance ie debits equal to credits before it can be processed'),'warn');//凭证必须贷相等才能保存/
}
*/
echo '</div>
	</form>';
include('includes/footer.php');
/*
function WriteSettle($tag,$stlacc){
	  //读取结账单项
		$SQL="SELECT  settle FROM periods WHERE periodno=".$_SESSION['period'];
		$Result=DB_query($SQL);	
		$row=DB_fetch_assoc($Result);
		$settlearr=json_decode($row['settle'],true);		
		$settleflag=str_split($settlearr[$tag],1);
	
		$SQL="SELECT DISTINCT  LEFT(account,4) account ,accountname FROM accountsettle LEFT JOIN chartmaster ON  LEFT(account,4)=accountcode WHERE accountsettle.tag=".$tag;
		$Result=DB_query($SQL);
		$i=0;
		while ($row=DB_fetch_array($Result)){
			if($row['account']==$stlacc){
				$settleflag[$i]=1;
				break;
			};		
			$i++;
		}        
	   $settlearr[$tag]= implode('',$settleflag);
	   $settlejsn=json_encode( $settlearr);
	   $sql="UPDATE periods SET settle='".$settlejsn."' WHERE  periodno=".$_SESSION['period'];
	   $Result=DB_query($sql);
	 if ($Result){
		 return true;
	 }else{
		  return false;
	 }
}
*/
  
?>
