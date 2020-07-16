
<?php
/* $Id: CreateJournal.php
 * @Author: mikey.zhaopeng 
 * @Date: 2019-04-03 02:42:32 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-01 09:13:15<
 */

include('includes/JournalEntryClass.php');
include('includes/session.php');
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
	//打印封账限制录入
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
		prnMsg('你选择的会计期间已经打印封账,不能录入凭证或修改！','warn');
		echo '</form>';
		include('includes/footer.php');
		exit;
	}
	*/	

	
if (isset($_GET['InvPrm'])){
	//发票生成
	if (isset($_GET['ToActPrm'])){
		
		$ToActArr=json_decode(str_replace('\"','"',urldecode($_GET['ToActPrm'])),JSON_UNESCAPED_UNICODE);
		
	}
	//var_dump($ToActArr).'<br/>';
	
	$TransArr=json_decode(str_replace('\"','"',urldecode($_GET['InvPrm'])),JSON_UNESCAPED_UNICODE);

	//var_dump($TransArr);
	
	$tag=$TransArr["tag"];
	
	$_SESSION['GetUrl_Trans']='?InvPrm='.urlencode(json_encode($TransArr,JSON_UNESCAPED_UNICODE)); 
	if (isset($ToActArr)){
		$_SESSION['GetUrl_Trans'].='&ToActPrm='.urlencode(json_encode($ToActArr,JSON_UNESCAPED_UNICODE)); 
	}
}elseif (isset($_GET['GLPrm'])){
	//银行凭证生成
	
	//$TransArr=explode('^',urldecode($_GET['GLPrm']));
	$TransArr=json_decode(str_replace('\"','"',urldecode($_GET['GLPrm'])),JSON_UNESCAPED_UNICODE);
	//var_dump($TransArr);
	$tag=$TransArr["tag"];
	if (isset($_GET['ToActPrm'])){
		$ToActArr=json_decode(str_replace('\"','"',urldecode($_GET['ToActPrm'])),JSON_UNESCAPED_UNICODE);
	}
    $_SESSION['GetUrl_Trans']='?GLPrm='.urlencode(json_encode($TransArr,JSON_UNESCAPED_UNICODE)); 
	if (isset($ToActArr)){
		$_SESSION['GetUrl_Trans'].='&ToActPrm='.urlencode(json_encode($ToActArr,JSON_UNESCAPED_UNICODE)); 
	}	
}elseif (isset($_GET['ntpa'])){
	$TransArr=explode('^',urldecode($_GET['ntpa']));
	//$TransArr=urldecode($_GET['ntpa']);
	if (isset($_GET['ty'])){
		if($_GET['ty']==3){  //结账			
			$tag=$TransArr['tag'];

		}elseif ($_GET['ty']==2){//银行
	
			$tag=$TransArr['tag'];
		}
	}
	//	prnMsg(	url_decode($_GET['ntpa']).'<br>'.$_GET['topara']);
}else{
	unset($TransArr);
	prnMsg('页面引导错误！','info');
	echo "<script>window.close();</script>";
	//include('includes/footer.php');
	exit;
}
    $checkflg=0;//检查是否有全部科目有未知科目=1
	/*
	if (isset($_GET['NewJournal'])	AND $_GET['NewJournal'] == 'Yes'	AND isset($_SESSION['JournalDetail']) ){
		unset($_SESSION['JournalDetail']->GLEntries);
		unset($_SESSION['JournalDetail']);
	}
	*/
    $GetUrl='?';
if (isset($_GET['ntpa'])){
		if (isset($_GET['ty'])){
			$GetUrl.='ty='.$_GET['ty'].'&';
		}
		if (isset($_GET['ntpa'])){
			$GetUrl.='ntpa='.$_GET['ntpa'].'&';
		}  
		$_SESSION['GetUrl_Trans']=$GetUrl;
}
		//读取外币汇率
    if ($_SESSION['Currency']==1){
		$result=DB_query("SELECT currabrev, ROUND(rate,decimalplaces) rate  FROM currencies  WHERE currabrev!='".$_SESSION['CompanyRecord'][abs($TransArr['tag'])]['currencydefault']."'");
		$curratearr=array();
		$i=0;
		while ($row=DB_fetch_array($result)){

			$curratearr[$i]=array('currabrev'=>$row['currabrev'],'rate'=>$row['rate']);
			$i++;
		}
			$ratejsn=json_encode( $curratearr);	   
	}
	if (isset($_SESSION['JournalDetail'])){
		//比对发票号码
		if ((isset($_GET['InvPrm'])&& $_SESSION['JournalDetail']->TypeNo!=$TransArr['InvNo'])|| (isset($_GET['GLPrm']) && $_SESSION['JournalDetail']->TypeNo!=$TransArr['BankIndex'])){
		  
			unset($_SESSION['JournalDetail']->GLEntries);
			unset($_SESSION['JournalDetail']);		
		}	
	}
	if (!isset($_SESSION['JournalDetail'])){
		$_SESSION['JournalDetail']= new Journal;
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
	$BankAct=array();
	$accountbank=array();
	while ($myrow=DB_fetch_array($result)){
		$BankAct[$myrow['bankaccountnumber']][]=$myrow['accountcode'];
		$Accountbank[$myrow['accountcode']][]=array($myrow['accountname'],$myrow['currcode']);
	}

	$SQL="SELECT `accountcode`, `accountname`, `currcode` 
	       FROM `chartmaster`
	        WHERE length(accountcode)>4 AND currcode<>'".$_SESSION['CompanyRecord'][abs($TransArr['tag'])]['currencydefault']."'";
	$Result = DB_query($SQL);
	$AccCurr=array();	
	while ($row=DB_fetch_array($Result)){
		//$Accountbank[$myrow['accountcode']][]=array($myrow['accountname'],$myrow['currcode']);
		$AccCurr[$row['accountcode']]=$row['currcode'];
	} 
	if (!isset($GLTypeArr)||count($GLTypeArr)==0){
		$result=DB_query("SELECT typeid, account, toaccount, len, gltype FROM journaltype");
		$GLTypeArr=array();
		
		while($row=DB_fetch_array($result)){
		  array_push($GLTypeArr,array($row['typeid'],$row['account'],$row['toaccount'],$row['len'],$row['gltype']));
		}
	}  
		/*$regstr[InvPrm]		
		0[注册码]      1[发票号]     2[发票类别]  3[发票金额] 4[发票税额]
		5[查找的凭证号]6[客户科目]    7[客户科目名]8[发票日期] 9[客户名]
		10[分组]      11[解析发票分类]12[解析类别]13[期间]    14[币种]
		15[RegID]     16[税金科目]    17[税金科目名]
		 出口普票传递	专票销售		专票采购
		 */		
	//	prnMsg($_SESSION['JournalDetail']->JournalTotal.' =265 ==0 &&'. $_SESSION['JournalDetail']->GLItemCounter );
if ($_SESSION['JournalDetail']->JournalTotal  ==0 && $_SESSION['JournalDetail']->GLItemCounter ==0){
     
	if(isset($_GET['InvPrm']) && $dlsv==0){//进项专发票
	    //prnMsg('//$dlsv  删除、保存点击 //发票');
	
		$prd=$TransArr['Period'];
		$m=$TransArr['Period']-$_SESSION['period'];
		$dt=$_SESSION['lastdate'];
		$me=date("Y-m-t",strtotime("$dt +$m month"));
		$ms=date("Y-m-01",strtotime("$dt +$m month"));
		if(strtotime($TransArr['TabDate'])>strtotime($me)){
			$InvDate = $me;
			//$pd=2;
		}elseif(strtotime($TransArr['TabDate'])<strtotime($ms)){
			$InvDate = $ms;
			//$pd=1;
		}else{
			$InvDate = $TransArr['TabDate'];
			//$pd=0;
		}
		//prnMsg($me.'='.$InvDate.'--'.$ms);
		$_POST['JournalDate']=$InvDate;
		$_SESSION['JournalDetail']->JnlDate=$InvDate;
		$_SESSION['JournalDetail']->Period=$TransArr['Period'];
		$_SESSION['JournalDetail']->TransNo=$TransArr['InvNo'];	
		$_SESSION['JournalDetail']->TypeNo=$TransArr['InvNo'];
		$_SESSION['JournalDetail']->Accounts=$TransArr['RegID'];//
		$_SESSION['JournalDetail']->tag=$TransArr['tag'];
		//ToActPrm
		/*$regstr[InvPrm]		
		0[注册码]      1[发票号]     2[发票类别]  3[发票金额] 4[发票税额]
		5[查找的凭证号]6[客户科目]    7[客户��目名]8[发票日期] 9[客户名]
		10[分组]      11[解析发票分类]12[解析类别]13[期间]    14[币种]
		15[RegID]     16[税金科目]    17[税金科目名]
		出口普票传递	专票销售		专票采购
		$PrmArr
		[ToAct]  [ToActName] [ActAmo]  [msg] [CustAct]  [CurrCode] [CurrRate] [CurrAmo] 

		$SubTax=$CustActNameArr[0][0].'^'.$SubNameArr[0][1].'^'.$myrow['tax'];//应交税金
				$ToActPrm=$SubTax.'^'.$GLTemplet [$myrow['registerno']][0].'^'.$GLTemplet [$myrow['registerno']][1].'^'.$myrow['amount'];

		*/
		$CustAct=='';
		//prnMsg($TransArr['InvType'].'=313');
		if ($TransArr['InvType']==0){//专票进项
			//检测科目是否存在//无科目 有regid
			if (isset($ToActArr['ToAct'])&&$ToActArr['ToAct']!=''){
				//手动科目存在使用手动科目			
				$ToAct=$ToActArr['ToAct'];
				$ToActName=$ToActArr['ToActName'];
			}else{				
					$ToAct=$TransArr['ToAct'];
					$ToActName=$TransArr['ToActName'];
			}
			if (isset($ToActArr['CustAct']) && $ToActArr['CustAct']!=''){
				//外币普通发票选择
				$CustAct=$ToActArr['CustAct'];
				$CustActName=$ToAcTArr['CustActName'];
			}elseif (isset($TransArr['CustAct']) && $TransArr['CustAct']!=''){	
				$CustAct=$TransArr['CustAct'];
				$CustActName=$TransArr['CustActName'];

			}else{
			//if ($CustAct==''&& $TransArr['RegID']>0){
					//prnMsg('无科目,没有选择科目新增或绑定科目')
					$regstr=substr( $TransArr['CustActName'],strripos( $TransArr['CustActName'],'-')).'^'.$TransArr['RegID'].'^'.$TransArr['tag'].'^0^0^^'.$TransArr[0];
					//prnMsg($regstr);
					$ActReg=AddCustomer($regstr,'2202','');
					//var_dump($ActReg);
					if ($ActReg[0]>0){
						$CustAct=$ActReg[1];
					//$CustActName=$ToActArr['ToActName'];
					}else{
						prnMsg('科目添加错误,请联系系统管理员!','error');
						exit;
					} 					
				//}
			}
			
			if (isset($ToActArr['CurrCode'])&&$ToActArr['CurrCode']!=''){
				$CurrCode=$ToActArr['CurrCode'];
			}else{
				$CurrCode=$TransArr['CurrCode'];
			}
			if (isset($ToActArr['msg'])&&$ToActArr['msg']!=''){
				$Narrative=$ToActArr['msg'];
			}
			if (isset($ToActArr['remark'])&&$ToActArr['remark']!=''){
				$Narrative.=$ToActArr['remark'];
			}
			$TaxAct=$TransArr['TaxAct'];
			$TaxActName=$TransArr['TaxActName'];
			/*1-$Debit,
			2-$Credit,
			3- $Narrative,
			4- $GLCode, 
			5-$GLActName,
			6- $tag,
			7-$exrat,
			8-$examount,
			9-$currcode*/
			//客户科目
			$_SESSION['JournalDetail']->Type=$TransArr['RegID'];
			//prnMsg($Narrative.$CustAct.'='.$CustActName.	$tag.$CurrCode);
			$_SESSION['JournalDetail']->Add_To_GLAnalysis(0,//Debit
													($TransArr['Tax']+$TransArr['Amount']),
													$Narrative,//Narrative
													$CustAct,//GLcode
													$CustActName,
													$tag,
													'1',//Exrate
													0,  //ExAmount
													$CurrCode  //币种
													);
			//税金			
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($TransArr['Tax'],
														0,
														$Narrative,
														$TaxAct,
														$TaxActName,
														$tag,
														'1',
														0,
														$TransArr['CurrCode']);
				//prnMsg($TransArr['Amount'].','.	$Narrative.','.	$ToActArr['ToAct'].','.	$ToActArr['ToActName'].'='.$TransArr['CurrCode']);												
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($TransArr['Amount'],
														0,
														$Narrative,
														$ToAct,
														$ToActName,
														$tag,
														'1',
														0,
														$TransArr['CurrCode']);
													
				//var_dump($_SESSION['JournalDetail']->GLEntries);
		}elseif($TransArr['InvType']==1||$TransArr['InvType']==3){//销项
			/*
			ToActPrm=22210101^应交税金-进项税^224.77^1403101^原材料-汇总^7492.23^采购代开专票[01802050]的方式规划^22021009^CNY^环翠区田锋模具维修部 ^1
					*/
			$ExAmount=0;
			if ($TransArr['InvType']==3){
				//prnMsg($ToActArr['CustRegID']);
				$_SESSION['JournalDetail']->Accounts=$ToActArr['CustRegID'];
			}
			/*
			if (isset($ToActArr['ToAct'])&&$ToActArr['ToAct']!=''){
				//手动科目存在使用手动科目			
				$CustAct=$ToActArr['ToAct'];//???
				$CustActName=$ToActArr['ToActName'];//???
			}*/
			if (isset($ToActArr['CustAct']) && $ToActArr['CustAct']!=''){
				//外币普通发票选择
				$CustAct=$ToActArr['CustAct'];
				$CustActName=$ToAcTArr['CustActName'];
			}else{
				$CustAct=$TransArr['CustAct'];
				$CustActName=$TransArr['CustActName'];
			}
			if (isset($ToActArr['CurrCode'])&&$ToActArr['CurrCode']!=''){
				$CurrCode=$ToActArr['CurrCode'];
			}else{
				$CurrCode=$TransArr['CurrCode'];
			}
			if ($CurrCode!=$_SESSION['CompanyRecord'][$TransArr['tag']]['currencydefault']){
				$ExAmount=$ToActArr['CurrAmo'];
			}
			if (isset($ToActArr['msg'])&&$ToActArr['msg']!=''){
				$Narrative=$ToActArr['msg'];
			}
			if (isset($ToActArr['remark'])&&$ToActArr['remark']!=''){
				$Narrative.=$ToActArr['remark'];
			}
			$TaxAct=$TransArr['TaxAct'];
			$TaxActName=$TransArr['TaxActName'];
			//prnMsg($TransArr['Tax'].'+'.$TransArr['Amount'].$Narrative.$CustAct.$CustActName.	$tag.$ExAmount.	$CurrCode);
				$_SESSION['JournalDetail']->Add_To_GLAnalysis(($TransArr['Tax']+$TransArr['Amount']),
																0,
																$Narrative,
																$CustAct,
																$CustActName,
																$tag,
																'1',
																$ExAmount,
																$CurrCode);
				//税金
				$_SESSION['JournalDetail']->Add_To_GLAnalysis(0,
															$TransArr['Tax'],
																$Narrative,
																$TaxAct,
																$TaxActName,
																$tag,
																'1',
																0,
																$TransArr['CurrCode']);

				$_SESSION['JournalDetail']->Add_To_GLAnalysis(0,
															$TransArr['Amount'],
																$Narrative,
																$ToActArr['ToAct'],
																$ToActArr['ToActName'],
																$tag,
																'1',
																0,
																$TransArr['CurrCode']);
		//	 var_dump($_SESSION['JournalDetail']->GLEntries);
		}			
   
	}elseif(isset($_GET['GLPrm']) && $dlsv==0){
		//	prnMsg('//银行凭证');
	   /*收付款GLPrm=0[账号]1[银行科目]2['flg']3[借方金额]4[贷方金额]5[凭证号]6[对应科目]
	   7[对应科目名]8['bankdate'].9[对应客户名].10[分组]11['amount'].12LastJournal.13 (period^
	   //14 lastdate.)15['remark'].16['abstract'].17curr.18'banktransid'];
	   ToActPrm=ToAct: ToActName:  ToCustName:?	  
	   	//外币
				$Get_Url=0"ToAct"=>$myrow['toaccount'],
						  1"BankAct"=>$myrow['bankacc'],
						  2 "flg"=>$myrow['flg'],
						  3"ExAmoJ"=>$examoj,
						  4"ExAmoD"=>$examod,
						  5"TransNo"=>$transnogl,
						  8"TabDate"=>$myrow['bankdate'],
						  9"ToName"=>$myrow['toname'],
						  10"tag"=>
						  11"Amount"=>$amount,
						  "JulType"=>$JournalNo,
						  13"Period"=>$_POST['ERPPrd'],
						  12"LastDate"=>$LastDate,
						  13"Remark"=>$myrow['remark'],
						  14"Abstract"=>$myrow['abstract'],
						  17"Currcode"=>
						  18,"BankIndex"=>$myrow['banktransid'],
						  17"RegID"=>$RegID);
						  6"Act"=>$Act,
						 7 "ActName"=>$ActName,
						 "ToActType=>"
				*/
		$amoj=0;
		$amod=0;
		$ExCuAmount=0;
		if (is_numeric($TransArr['ExAmoJ'])){
			$amoj=$TransArr['ExAmoJ'];
		}
		if(is_numeric($TransArr['ExAmoD'])){
			$amod=$TransArr['ExAmoD'];
		}
		$ExCuAmountexamo=$TransArr['Amount'];
		$TransDate=date('Y-m-d',strtotime($TransArr['TabDate']));
	
		$_POST['JournalDate']=$TransDate;
		$prd=DateGetPeriod($TransDate);
	
		$_SESSION['JournalDetail']->JnlDate=$TransDate;
		$_SESSION['JournalDetail']->Period=$TransArr['Period'];	
		$_SESSION['JournalDetail']->TypeNo=$TransArr['BankIndex'];
		$_SESSION['JournalDetail']->tag=$TransArr['tag'];
		if (isset($ToActArr)){	
			//prnMsg('$ToActArr=0 binaktranid 1= 日期  2=币别  3=金额 4=转换方式[2本币-外币4 外币-本币  1 本币]  5->外币科目');
			$ToCurrCode=$ToActArr['ToBankActNumber'];
			$ToActType=$ToActArr['ToActType'];

		
		}else{
			$ToActType=0;
		}
		//prnMsg('凭证分类读取'.$ToActType.'='.$ToActArr['ToActType']);
		if ($ToActType>1){
			if ($ToActType==2)	{	
				//本币->外币
				$Amount=round(((float)$TransArr['ExAmoJ']-(float)$TransArr['ExAmoD']),2);//本币
				$amoj=round((float)$TransArr['ExAmoJ'],2);
				$amod=round((float)$TransArr['ExAmoD'],2);
				$CuAmount=$Amount;
				$CurrAccount=$ToActArr['ActName'];
				$ExCuAmount=$ToActArr['Amount'];//外币金额
				$narmsg="外币转户[".$ToCurrCode.$CurrAmo."]";
			}elseif($ToActType==4){
				//prnMsg('外币->本币');

				if ((float)$ToActArr['Amount']>0){
					$amod=round((float)$ToActArr['Amount'],2);
					$amoj=0;
				}else{
					$amoj=round((float)$ToActArr['Amount'],2);
					$amod=0;
				}
				$Amount=-$ToActArr['Amount'];		
				$ExCuAmount=round(((float)$TransArr['ExAmoJ']-(float)$TransArr['ExAmoD']),2);
				$narmsg="外币转户[".$TransArr['Currcode'] .$ExCuAmount."]";

			}
			if ($ToActType==2)	{	
				$rate=round(abs($Amount/$CurrAmo),4);//本币->外币
			}elseif($ToActType==4){
				$rate=round(abs($CurrAmo/$Amount),4);//外币-本币
			}
			
		}elseif($ToActType==1){
			$Amount=-round((float)$ToActArr['Amount'],2);
			$narmsg="内部转户";				
		}else{

			//prnMsg('以下为收货款 外币  本币');

			$dcflag=0;
			//外币  本币		
		
			if ( $TransArr['Currcode'] !=$_SESSION['CompanyRecord'][$TransArr['tag']]['currencydefault'] ){
					//收外币
					$ExCuAmount=round(((float)$TransArr['ExAmoJ']-(float)$TransArr['ExAmoD']),2);
			
				if (round((float)$TransArr['ExAmoJ'],2)!=0){//借方				
					$Amount=round($TransArr['Amount'],2);
					$amoj=round($TransArr['Amount'],2);
					$amod=0;
					$dcflag=1;//收款标记
					if  (substr($TransArr['Act'],0,4)=='1122'){ 
						$narmsg="收货款[".$TransArr['Currcode'].$ExCuAmount."]`".$TransArr['ToName'];
					}else{
						$narmsg="收款[".$TransArr['Currcode'].$ExCuAmount."]`".$TransArr['ToName'];
					}			
				}else{
					$dcflag=-1;//付款标记
					$Amount=-round($TransArr['Amount'],2);
					$amod=round($TransArr['Amount'],2);
					$amoj=0;
					if  (substr($TransArr['Act'],0,4)=='2202'){ 
						$narmsg="采购付款[".$TransArr['Currcode'].$ExCuAmount."]`".$TransArr['ToName'];
					}else{
						$narmsg="付款[".$TransArr['Currcode'].$ExCuAmount."]`".$TransArr['ToName'];
					}				
				}
				$rate=round(abs($ExCuAmount/$Amount),4);			
			}else{
				//prnMsg('//本币');		
				$Amount=round(((float)$TransArr['ExAmoJ']-(float)$TransArr['ExAmoD']),2);
				$amoj=round((float)$TransArr['ExAmoJ'],2);
				$amod=round((float)$TransArr['ExAmoD'],2);
				if ($TransArr['ExAmoJ']!=0){
					$dcflag=1;//收款标记
					if  (substr($TransArr['Act'],0,4)=='1122'||substr($TransArr['Act'],0,4)=='2202'){ 
						$narmsg="收货款`".$TransArr['ToName'];
					}elseif(substr($TransArr['Act'],0,4)=='6603'){
						$narmsg="收利息`".$TransArr['ToName'];
					}elseif(substr($TransArr['Act'],0,4)=='1221'||substr($TransArr['Act'],0,4)=='2241'){
						$narmsg="收往来款`".$TransArr['ToName'];
					}else{
						$narmsg="收款`".$TransArr['ToName'];
					}	
				}else{
					$dcflag=-1;//付款标记
			
					if  (substr($TransArr['Act'],0,4)=='1122'||substr($TransArr['Act'],0,4)=='2202'){ 
						$narmsg="采购付款`".$TransArr['ToName'];
					}elseif(substr($TransArr['Act'],0,4)=='6603'){
						$narmsg="付利息";
					}elseif(substr($TransArr['Act'],0,4)=='6602'){
						$narmsg="费用支出`".$TransArr['ToName'];
					}elseif(substr($TransArr['Act'],0,4)=='6601'){
						$narmsg="销售费用`".$TransArr['ToName'];
					}elseif(substr($TransArr['Act'],0,4)=='5001'){
						$narmsg="成本费用支出`".$TransArr['ToName'];
					}elseif(substr($TransArr['Act'],0,4)=='5101'){
						$narmsg="制造费用支出`".$TransArr['ToName'];
					}elseif(substr($TransArr['Act'],0,4)=='5301'){
						$narmsg="研发支出费用".$TransArr['ToName'];
					}else{
						$narmsg="付款`".$TransArr['ToName'];
					}
				}
					$rate=1;	//本币
			}	
		}//endif450
	
			if (isset($AccCurr[$TransArr['BankAct']])){//外币检测
				$curr_j=$AccCurr[$TransArr['BankAct']];
				$ExCuAmo_j=$ExCuAmount;
			}else{
				$ExCuAmo_j=0;
				$curr_j=$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['currencydefault'];
			}
			if (isset($AccCurr[$TransArr['Act']])){
				$curr_d=$AccCurr[$TransArr['Act']];
				$ExCuAmo_d=$ExCuAmount;
			}else{
				$ExCuAmo_d=0;
				$curr_d=$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['currencydefault'];
			}
			$ToAct=$TransArr['Act'];
			$ToActName=$TransArr['ActName'];
			if (isset($ToActArr['ToAct'])&&$ToActArr['ToAct']!=''){
				//手动科目存在使用手动科目			
				$ToAct=$ToActArr['ToAct'];
				$ToActName=$ToActArr['ToActName'];
			}	
		//	prnMsg($amoj.'-'.$amod.';'.$narmsg.'['.	$TransArr['BankAct'].']'.$accountbank[$TransArr['BankAct']][0].'=dc'.$dcflag.'['.$TransArr['tag'].']'.$ExCuAmo_j.']'.$curr_j);

		if ($dcflag==1){//收款银行
			
			//prnMsg($amoj.'-'.$amod.';'.$narmsg.'['.	$TransArr['BankAct'].']'.$accountbank[$TransArr['BankAct']][0].		'['.$TransArr['tag'].']'.$ExCuAmo_j.']'.$curr_j);
		
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amoj,
														$amod,
														$narmsg,
														$TransArr['BankAct'],
														$accountbank[$TransArr['BankAct']][0],
														$TransArr['tag'],
														'1',
														$ExCuAmo_j,
														$curr_j);
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amod,
															$amoj,
															$narmsg,
															$ToAct,
															$ToActName,
															$TransArr['tag'],
															'1',
															$ExCuAmo_d,
															$curr_d);
          // var_dump($_SESSION['JournalDetail']->GLEntries);
		}else{
			
			//prnMsg($amoj.$amod.$narmsg.	$TransArr['BankAct'].$accountbank[$TransArr['BankAct']][0]);
		
			//付款
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amoj,
															$amod,
															$narmsg,
															$TransArr['BankAct'],
															$accountbank[$TransArr['BankAct']][0],
															$TransArr['tag'],
															'1',
															$ExCuAmo_j,
															$curr_j);
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amod,
															$amoj,
															$narmsg,
															$ToAct,
															$ToActName,
															$TransArr['tag'],
															'1',
															$ExCuAmo_d,
															$curr_d);
		}	  	
		//var_dump($_SESSION['JournalDetail']->GLEntries);
	}elseif($_GET['ty']==3 && $dlsv==0){//结账
		$glarr=json_decode($TransArr[2]);

		$narmsg=$TransArr['BankAct']."月末结账";		
		$_POST['JournalDate']=$_SESSION['lastdate'];
		$_SESSION['JournalDetail']->JnlDate=$_SESSION['lastdate'];
		$_SESSION['JournalDetail']->Period=$_SESSION['period'];
		
		$gltran=array();	
		foreach($glarr as $val){
			$amt=str_replace(',','',$val[2]);
			$amt=(float)$amt;
			if (isset($gltran[$val[0]])){
				$gltran[$val[0]]+=$amt;
			}else{
				$gltran[$val[0]]=$amt;
			}
			if (isset($gltran[$val[1]])){
				$gltran[$val[1]]+=-$amt;
			}else{
				$gltran[$val[1]]=-$amt;
			}

		}
		unset($glarr);
		foreach($gltran as $key=>$val){
			if ($val>0){
				$jamo=$val;
				$damo=0;
			}else{
				$jamo=0;
				$damo=-$val;
			}
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($jamo,
															$damo,
															$narmsg,
															$key,
															'',
															$TransArr['tag'],
															'1',
															0,
															'CNY');
				
		}
	}//endif
	
}   /*
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
		$_SESSION['JournalDetail']->JnlDate=$_POST['JournalDate'];
	}elseif(isset($TransArr['TabDate'])){
		$_POST['JournalDate']=date('Y-m-d',strtotime($TransArr['TabDate']));
	}
    $JournalNo=1;//?没有使用
    $r=1;
//按保存按钮执行以下
if ((isset($_POST['CommitBatch']) AND $_POST['CommitBatch']=="保存/缓存")||(isset($_POST['savemode']) AND $_POST['savemode']=="保存/模板")){

	if (isset($_POST['CommitBatch']) AND $_POST['CommitBatch']=="保存/缓存"){
		//prnMsg('//录入行数不能大于15');
		if ($_SESSION['JournalDetail']->GLItemCounter >=14){
			prnMsg(_('Document line greater than 15'));
		}	
		//正负凭证判断
		if((double)$_SESSION['JournalDetail']->JournalTotal !==0) {
				//凭证金额自动凭证
			$_POST['GLAmount']=-$_SESSION['JournalDetail']->JournalTotal;  			  
			$JournalNo=1;
			if ($_SESSION['Currency']!=0){
				if ( (double)$_POST['examount']==0){
					$_POST['examount']=$_SESSION['JournalDetail']->JournalTotal*$_POST['currate'] ; 
				}
			}							
						
		}
		if ($_POST['GLManualCode']!=='' AND (double)$_POST['GLAmount']!==0){
			$inputerr=0;
			foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
				if($JournalItem->GLCode==$_POST['GLManualCode']){
					$inputerr=1;
					break;
				}
			}
			if ($inputerr==0){ 
				if ($_POST['currcode']!=$_SESSION['CompanyRecord'][$TransArr['tag']]['currencydefault']){
					$_POST['GLNarrative']= $_POST['GLNarrative'].'['.$_POST['currcode'].$_POST['examount'].']';
					}
				if($_POST['GLAmount']<0){
					$ExCuAmount=- $_POST['examount'];
				}		
				$_SESSION['JournalDetail']->Add_To_GLAnalysis($_POST['Debit'],
															$_POST['Credit'],
															$_POST['GLNarrative'],
															$_POST['GLManualCode'],
															$_POST['accname'],
															$TransArr['tag'],
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
				$_POST['currcode']=$_SESSION['CompanyRecord'][$TransArr['tag']]['currencydefault'];
			}else{
				prnMsg('你输入的科目重复！','warn');
			}															
		}		
				
	}	
	//var_dump($_SESSION['JournalDetail']);
	///prnMsg($_SESSION['JournalDetail']->JournalTotal.' ==0 AND'. $_SESSION['JournalDetail']->GLItemCounter);
		//存入表[借==贷 平衡 2行以上执行]
	if(round($_SESSION['JournalDetail']->JournalTotal,2) ==0 AND $_SESSION['JournalDetail']->GLItemCounter >1) {								
			//检验凭证格式
			$debit=0;
			$credit=0;
			$rowj=0;
			$rowd=0;
			$msgerr='';
		//if (isset($_GET['GLPrm'])){	
			foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
				if ($JournalItem->GLCode==''){
					//科目代码为空错误
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
		
			if (($rowj+ $rowd)==count($_SESSION['JournalDetail']->Accounts)){
				
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
		foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
			if(isset($_GET['InvPrm'])){//发票
				if ($JournalItem->GLCode==$TransArr['CustAct']){
					if(is_numeric( $JournalItem->Credit)&& $JournalItem->Credit!=0){
						$amt=-$JournalItem->Credit;
					}else{
						$amt=$JournalItem->Debit;
					}
					if ($TransArr['InvType']==0){//进项

						if( (double)$TransArr['Amount']+(double)$TransArr['Tax']==- $amt){
							//应付账款检验ok
							$checkgl=1;
						}
						
					}else{//销项
						if( (double)$TransArr['Amount']+(double)$TransArr['Tax']==$amt){
							//应收账款检验ok
							$checkgl=1;
						}
					}
				}
			}else{//收付款
				if($TransArr['Currcode']!=$_SESSION['CompanyRecord'][$TransArr['tag']]['currencydefault'] ){
					//外币
					if ($JournalItem->GLCode==$TransArr['Act']){
					
						if( (double)$TransArr['Amount']==(double)$JournalItem->examount){
							//应账款检验ok
							$checkgl+=1;
						}
						if( $TransArr['Currcode']==$JournalItem->curr){
							$checkgl+=2;
						}
						
					}

				}else{
					if ($JournalItem->GLCode==$TransArr['Act']){
					
							if( (double)$TransArr['ExAmoJ']+(double)$TransArr['ExAmoD']==(double)$JournalItem->Credit+(double)$JournalItem->Debit){
								//应账款检验ok
								$checkgl=1;
							}								
					}
				}
			}
		}//foreach	
	 
		if($inputerr==0){		 
				//Start a transaction to do the whole lot inside			
			$result = DB_Txn_Begin();				
			$TransNo =GetTransNo( $_SESSION['JournalDetail']->Period, $db);
			$TagTypeNo = GetTagTypeNo( $tag,$period, $db);			
			$GLTransType=GetTransType($_SESSION['JournalDetail']->GLEntries ,$GLTypeArr,$_SESSION['JournalDetail']->Period);
			//$GetTransType=$typarr[0];
			//$TagTypeNo =$typarr[1];			
			$msgerr='';
		
			//保存凭证
			foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
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
				//prnMsg(GetTransType.'==6 &&'.  $JournalItem->GLCode.'!='.$TransArr['Act'] .'&&'. $TransArr['ToActType']);
				/*
				if (GetTransType==6 &&  $JournalItem->GLCode==$TransArr['Act'] &&( $TransArr['ToActType']==4||$TransArr['ToActType']==2||$TransArr['ToActType']==1)){
					//转户查找对应账户的banktransid  更新
					if (isset($BankAct[$TransArr['ToAct']]) && $TransArr['Currcode']!=$_SESSION['CompanyRecord'][$TransArr['tag']]['currencydefault'] ){	////外币<--->默认币内部转户账号是否存在
					
						$keyacc=array_search($TransArr['BankAct'],$BankAct);//账号	
							
						$sql="SELECT banktransid ,amount ,flg FROM banktransaction WHERE DATE_FORMAT(bankdate,'%Y%m%d')=DATE_FORMAT('".$TransArr['TabDate']."','%Y%m%d') AND toaccount='".$keyacc."' AND amount>".(-$amot*.95)." AND amount<".(-$amot*1.05)." AND transno=0";
						prnMsg($sql);
						$result=DB_query('sql-'.$sql);
						$rw=DB_fetch_assoc($result);		
						$tag=$TransArr['tag'];	
										
					}else{
						
						$sq="SELECT banktransid  FROM banktransaction WHERE account='".$JournalItem->GLCode."'  AND transno=0 AND DATE_FORMAT(bankdate,'%Y%m%d')=	DATE_FORMAT('" . $TransArr['TabDate'] . "','%Y%m%d') AND  toaccount='". $TransArr['ToAct'] . "' AND  amount='" .$amot . "' LIMIT 1";
						prnMsg($sq);
						$res = DB_query('sq-'.$sq);
						$rw=DB_fetch_assoc($res);
					}
					
					if (isset($rw['banktransid'])){
					
						$SQL="UPDATE banktransaction SET transno='" . $TransNo . "',type='".$typ."',period='" .  $_SESSION['JournalDetail']->Period  . "' WHERE banktransid='".$rw['banktransid']."'";
						prnMsg($SQL);
						$res = DB_query('SQL-'.$SQL);
						$post=1;
					}
					
				}	*/
				if (GetTransType==6 &&  $JournalItem->GLCode==$TransArr['Act']  && ( $TransArr['ToActType']==4||$TransArr['ToActType']==2||$TransArr['ToActType']==1)){
					if  ( isset($ToActArr)){
						$SQL="UPDATE banktransaction SET transno='" . $TransNo . "',type='".$GetTransType."',period='" .  $_SESSION['JournalDetail']->Period  . "' WHERE banktransid='".$ToActArr['ToBankIndex']."'";
				    	$res = DB_query($SQL);
						$post=1;
					}else{
						$msgerr="转户对应凭证没有关联写入！";
					}

				}
				//下面代码为应收账款、应付账款录入
				if  (substr($JournalItem->GLCode,0,4)=='1122'){ 
					/*

					$sql="SELECT  unitscode, branchcode FROM accountunits WHERE account='" . $JournalItem->GLCode . "' ";
					$result = DB_query($sql);
					if (DB_num_rows($result)==1){
					$row=DB_fetch_row($result);
					*/
					$sql="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` FROM 					    `register_account_sub` WHERE sub= '" . $JournalItem->GLCode . "'";//  OR  custname  LIKE= ";
					$result = DB_query($sql);
					//if (DB_num_rows($result)==1){
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
														'" . $GetTransType . "',
														'" .  $row[0]. "',
													
														'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
														'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
														'" . $_SESSION['JournalDetail']->Period . "',
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
					$sql="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` FROM 					    `register_account_sub` WHERE sub= '" . $JournalItem->GLCode . "'";//  OR  custname  LIKE= ";
					$result = DB_query($sql);
					//if (DB_num_rows($result)==1){
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
													'" . $GetTransType . "',
													'" . $_SESSION['JournalDetail']->Period . "',
													'" .  $row[0] . "',
													'',
													'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
													'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
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
				
				if ( $JournalItem->Examount!=0 && $JournalItem->Currcode!=$_SESSION['CompanyRecord'][$TransArr['tag']]['currencydefault'] ){
			
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
												'" . $_SESSION['JournalDetail']->Period . "',
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
									VALUES (  '" . $GetTransType . "',
											'" . $TransNo . "',
											'" . $TagTypeNo . "',
											'".$_POST['documents']."',
											'" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "',
											'" . $_SESSION['JournalDetail']->Period . "',
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
				//prnMsg($SQL);			
				$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			}
			$result= DB_Txn_Commit();
			if (isset($_GET['InvPrm'])){
				  //发票
				  $sql="UPDATE invoicetrans SET transno=	'" . $TransNo . "',regid='". $_SESSION['JournalDetail']->Accounts ."' WHERE  invno=".$TransArr['InvNo'];
					$result = DB_query($sql);
			}else{
				if (isset($_GET['GLPrm'])){//银行
						//内部账户转户		
					$sql="UPDATE banktransaction SET type='".$GetTransType."',transno='".$TransNo."',period='". $_SESSION['JournalDetail']->Period ."'  WHERE banktransid=".$TransArr['BankIndex'];
					
					$result = DB_query($sql);
				}elseif ($_GET['ty']==3){//结账 写标签
					WriteSettle($TransArr['tag'],$TransArr['BankAct']);
				
				}
			}
			
			if(isset($_POST['savemode']) AND $_POST['savemode']=="保存/模板"){
				//没有使用	
				$accstr='';
				/*
				foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
					if ($TransArr['Act']!=$JournalItem->GLCode){
						if ($accstr=='') {
							$accstr.= $JournalItem->GLCode;

						}elseif(strlen($JournalItem->GLCode)>6){
							$accstr.=','.$JournalItem->GLCode;	
						}
					}

				}*/
				if (isset($_GET['InvPrm'])){//发票
					$sql="UPDATE registeraccount SET subject='".$accstr."'WHERE registerno='".$TransArr[0]."'";
					DB_query($sql);
					
				}else{
					if($_GET['ty']==2){//收付款
						$sql="UPDATE accountsubject SET subject='".$accstr."' WHERE bankaccount='".$TransArr['ToAccount']."'";
						DB_query($sql);	
					}
				}
				
			}
			unset($_POST['JournalProcessDate']);
			unset($_POST['JournalType']);
			//unset($_SESSION['JournalDetail']->GLEntries);
			//unset($_SESSION['JournalDetail']);
			unset($_SESSION['GetUrl_Trans']);
			
			$JournalNo=1;
			unset($typarr);
			unset($edittranarr);
			DB_free_result($result);
		} 
		
		//Set up a newy in case user wishes to enter another 
		echo '<br />';
		prnMsg($_POST['JournalDate'].'会计凭证 记 ' . $TransNo . ' '._('has been successfully entered').', '.$msgerr,'success');
		
		/*
		echo '<script type="text/javascript">
							window.opener=null;
							window.open("","_self"); 
							window.close();
							</script>';
							*/
		if($_GET['ty']==3){//
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/GLSettleAccounts.php">';
		}
		include ('includes/footer.php');
		/*
		if ($TransNo>0){
			//sleep(10);
			$TransNo=0;
			$JournalNo=1;
			//unset($_SESSION['JournalDetail']->GLEntries);
		//	unset($_SESSION['JournalDetail']);
			unset($_SESSION['GetUrl_Trans']);
		}*/
		exit;  
	}
}elseif (isset($_POST['cash'])) {
	//	echo "<script>window.close();</script>";
}elseif (isset($_GET['Delete'])){  
	
	//删除凭证

	$_POST['GLManualCode']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->GLCode;
	$_POST['Debit']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Debit;
	$_POST['Credit']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Credit;
	
	$_POST['GLNarrative']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Narrative ;
	$_POST['accname']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->GLActName;
	$tag=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->tag;
	$_POST['currate']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Exrat;
	$ExCuAmount=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Examount;
	$_POST['currcode']=$_SESSION['JournalDetail']->GLEntries[$_GET['Delete']]->Currcode;

	$_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);
		
}elseif(isset($_POST['savemode'])){
	unset($_POST['JournalProcessDate']);
	unset($_POST['JournalType']);
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);	
}
if ($TransArr['ToActType']!=null){
	echo '<div class="page_help_text">';
		if(isset($_GET['GLPrm'])){
			if ($TransArr['ToActType']==2||$TransArr['ToActType']==4||$TransArr['ToActType']==1)  {
			
					echo '内部转户对应交易:<br>交易序号:'.$ToActArr['ToBankIndex'].' 日期:'.$ToActArr['ToBankDate'] ;
					if ($TransArr['ToActType']==2||$TransArr['ToActType']==4)  {
						echo'转户币种：'.$ToActArr['ToCurrCode'].'汇率：'.$ToActArr['ToCurrRate'];
					}else{
						echo'本币 ';
					}
					echo '金额：'.$ToActArr['Amount'];

			
			}elseif (!isset($_GET['ToActPrm'])){
				echo '内部转户对应交易没有查找到,可能原因没有对应交易或汇率设置和实际交易差别超过+-10%！';

			}
		
		}
				
echo'</div>';	
}

echo '<table class="selection" width="700">
		<tr>
			<th colspan="7"><h3>'.$_SESSION['CompanyRecord'][$TransArr['tag']]['unitstab'].' 会计凭证</h3></th>
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
		//print_r($_SESSION['JournalDetail']->GLEntries);
		foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
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
	//var_dump($_SESSION['JournalDetail']->GLEntries);
	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
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
			echo'<td  class="number">' . locale_number_format($JournalItem->Examount,$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['decimalplaces']) . '</td>';
	    }
   		echo'<td class="number">' .locale_number_format($JournalItem->Debit,$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['decimalplaces']) . '</td> 
		     <td class="number">' . locale_number_format($JournalItem->Credit,$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['decimalplaces']) . '</td>';
			$CreditTotal+=$JournalItem->Credit;
			$DebitTotal += $JournalItem->Debit;   
	    echo '<td>' . $JournalItem->Narrative  . '</td>';
   		    //if ($JournalItem->ID!=0||$_SESSION['Currency']!=0 &&  $JournalItem->Currcode !=$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['currencydefault'] ){	
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
echo'<td class="number"><b>' . locale_number_format($DebitTotal,$_SESSION['CompanyRecord'][abs($TransArr['tag'])]['decimalplaces']) . '</b></td>
	 <td class="number"><b>' . locale_number_format($CreditTotal,$_SESSION['CompanyRecord'][abs($TransArr['tag'])]['decimalplaces']) . '</b></td>
	   <td></td>
		 <td></td>
	</tr>';

if ($DebitTotal!=$CreditTotal) {
	echo '<tr><td colspan="6" align="center" style="background-color: #fddbdb"><b>' . _('Required to balance') .': </b>' .
		locale_number_format(abs($DebitTotal-$CreditTotal),$_SESSION['CompanyRecord'][abs($TransArr['tag'])]['decimalplaces']);
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

 if ( $checkflg==1||$TransArr['Currcode']!=$_SESSION['CompanyRecord'][abs($TransArr['tag'])]['currencydefault']){
	 if ( $checkflg==1){
	
    $sql="SELECT t3.accountcode,t3.currcode,
	             t3.accountname
			 FROM chartmaster t3 
	 		 WHERE t3.accountcode  NOT IN (SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
		( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) and (t3.tag='".$TransArr['tag']."' or t3.tag='0' ) AND used>=0 AND t3.accountcode<>'".$acc."' ORDER BY t3.accountcode";
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
		echo '<option value="' . $row['accountcode'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false) . '"label=' . $row['accountcode'].'[' .$row['currcode'].']' .htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false)  . '</>';
	}

echo'</datalist> 
	<input type="hidden" name="accname" id="accname" value="' . $_POST['accname'] . '" />
	<input type="hidden" name="GLManualCode" id="GLManualCode" value="' . $_POST['GLManualCode'] . '" /></td>';
 
echo '<td><input type="text"  name="Debit" onchange="JorD(this,Credit,examount)" maxlength="12" size="12" value="' . $_POST['Debit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  /></td>
	  <td><input type="text"  name="Credit" onchange="JorD(this,Debit,examount)" maxlength="12" size="12" value="' . $_POST['Credit'] . '"  pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="匹配浮点数！"  /></td>
      </tr>';

echo '<tr>
     	<td>' . _('GL Narrative') . '</td>
		<td><input type="text" name="GLNarrative" maxlength="50" size="50" value="' . $_POST['GLNarrative'] . '" pattern="[\w\d\u0391-\uFFE5\(\)]+$" title="输入汉字和空格！" />
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
/*
if(count($_SESSION['JournalDetail']->GLEntries)>1) {
	echo '<br />
		<br />';
	prnMsg(_('The journal must balance ie debits equal to credits before it can be processed'),'warn');//凭证必须借贷相等才能保存/
}
*/
echo '</div>
	</form>';
include('includes/footer.php');
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

  
?>
