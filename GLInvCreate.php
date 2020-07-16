
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
if (isset($_SESSION['SelectInv'][5])&&  $_SESSION['SelectInv'][5]>0){
	//unset($_SESSION['SelectInv'][0]);
	unset($_SESSION['SelectInv'][5]);
	unset(	$_SESSION['JournalDetail']);
	unset($_SESSION['GetUrl_Trans']);
    header("location:InvoiceJournallize.php");
}
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
		prnMsg('你选���的会计期间已经打印封账,不能录入凭证或���改！','warn');
		echo '</form>';
		include('includes/footer.php');
		exit;
	}
	*/	

	
if (isset($_GET['InvPrm'])){
	//发票生���
	$ROW=json_decode(str_replace('\"','"',urldecode($_GET['InvPrm'])),JSON_UNESCAPED_UNICODE);
   // var_dump($ROW);	
	if (isset($_GET['ToActPrm'])&&$_GET['ToActPrm']!=''){		
		$ToActArr=json_decode(str_replace('\"','"',urldecode($_GET['ToActPrm'])),JSON_UNESCAPED_UNICODE);		
	}
	//var_dump($ToActArr).'<br/>';
	$tag=$ROW["tag"];	
	$_SESSION['GetUrl_Trans']='?InvPrm='.urlencode(json_encode($ROW,JSON_UNESCAPED_UNICODE)); 
	if (isset($ToActArr)){
		$_SESSION['GetUrl_Trans'].='&ToActPrm='.urlencode(json_encode($ToActArr,JSON_UNESCAPED_UNICODE)); 
	}
}else{
	unset($ROW);
	prnMsg('页面引导错误！','warn');
	echo "<script>window.close();</script>";
	//include('includes/footer.php');
	exit;
}

    $checkflg=0;//检查是否有全部科目有未知科目=1

	$GetUrl='?';

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
	if (isset($_SESSION['JournalDetail'])){
		//比对发票号码
		if ((isset($_GET['InvPrm'])&& $_SESSION['JournalDetail']->TransNo!=$ROW['InvNo'])){
		  
			unset($_SESSION['JournalDetail']->GLEntries);
			unset($_SESSION['JournalDetail']);		
		}	
	}
		
	//unset($_SESSION['JournalDetail']);
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
	$InvDate= DateExistsPeriod($ROW['TabDate'],$ROW['Period']);

		$_POST['JournalDate']=$InvDate;
		$_SESSION['JournalDetail']->JnlDate=$InvDate;

	if (!isset($AccCurr)){
		$SQL="SELECT `accountcode`, `accountname`, `currcode` 
			FROM `chartmaster`
				WHERE length(accountcode)>4 AND currcode<>'".CURR."'";
		$Result = DB_query($SQL);

		while ($row=DB_fetch_array($Result)){
	
			$AccCurr[$row['accountcode']]=$row['currcode'];
		} 
	}
	if (!isset($GLTypeArr)||count($GLTypeArr)==0){
		$result=DB_query("SELECT typeid, account, toaccount, len, gltype FROM journaltype");
		$GLTypeArr=array();
	
		while($row=DB_fetch_array($result)){
		  array_push($GLTypeArr,array($row['typeid'],$row['account'],$row['toaccount'],$row['len'],$row['gltype']));
		}
	}  
if ($_SESSION['JournalDetail']->JournalTotal  ==0 && $_SESSION['JournalDetail']->GLItemCounter ==0){
     
	if(isset($_GET['InvPrm']) && $dlsv==0){//发票
		//prnMsg('//$dlsv  删除、保存点击 //发票');
		if (!empty($ROW['ToAct'])){
				
			$ToAct=$ROW['ToAct'];
			$ToActName=$ROW['ToActName'];
		}elseif (isset($ToActArr['ToAct'])&&$ToActArr['ToAct']!=''){
			//手动科目存在使用手动科���			
			$ToAct=$ToActArr['ToAct'];
			$ToActName=$ToActArr['ToActName'];
		}elseif (isset($ROW['ToPrm']['ToAct']) && $ROW['ToPrm']['ToAct']!=''){			
			$ToAct=$ROW['ToPrm']['ToAct'];
			$ToActName=$ROW['ToPrm']['ToActName'];
		}
		if (isset($ROW['CustAct']) && $ROW['CustAct']!=''){
			//外币普通发票选择
			$CustAct=$ROW['CustAct'];
			$CustActName=$ROW['CustActName'];
		}elseif (isset($ToActArr['CustAct']) && $ToActArr['CustAct']!=''){
			//��币普通发票选择
			$CustAct=$ToActArr['CustAct'];
			$CustActName=$ToAcTArr['CustActName'];
		}elseif (isset($ROW['ToPrm']['CustAct']) && $ROW['ToPrm']['CustAct']!=''){	
			$CustAct=$ROW['ToPrm']['CustAct'];
			$CustActName=$ROW['ToPrm']['CustActName'];

		}
		if (!empty($ToActArr['CurrCode'])){
			$CurrCode=$ToActArr['CurrCode'];
		}elseif(!empty($ROW['ToPrm']['CurrCode'])){
			$CurrCode=$ROW['ToPrm']['CurrCode'];
		}elseif (!empty($ROW['CurrCode'])){
			$CurrCode=$ROW['CurrCode'];
		}
		$TaxAct=$ROW['TaxAct'];
		$TaxActName=$ROW['TaxActName'];
	
		$_SESSION['JournalDetail']->Period=$ROW['Period'];
		$_SESSION['JournalDetail']->TransNo=$ROW['InvNo'];	
		//$_SESSION['JournalDetail']->Accounts=$ROW['RegID'];//
	
		$_SESSION['JournalDetail']->tag=$ROW['tag'];
		if ($CurrCode==CURR){
			if ($ROW['InvType']==0){
				if (substr($ToAct,0,4)=='1403'){
					$Narrative.="购材料";
				}else{
					$Narrative.="支出";
				}
				$Narrative.=" 专票号:".$ROW['InvNo'];
			}elseif($ROW['InvType']==1||$ROW['InvType']==3){

				if($ROW['InvType']==1){
					$Narrative.="收入 专票号:".$ROW['InvNo'];
				}else{

					$Narrative.="收入 普票号:".$ROW['InvNo'];
				}
			}
			if (strlen($CustAct)==4){
				$Narrative.=$ROW['ToName'];
			}else{
				$Narrative.=substr($CustActName,strpos($CustActName,'-'));
			}
		}else{
		    //外��
			if ($ROW['InvType']==0){
				if (substr($ToAct,0,4)=='1403'){
					$Narrative.="购材料[".$ROW['CurrCode'].$ROW['Amount'].']';
				}else{
					$Narrative.="支出[".$ROW['CurrCode'].$ROW['Amount'].']';
				}
				$Narrative.=" 专票号:".$ROW['InvNo'];
			}elseif($ROW['InvType']==1||$ROW['InvType']==3){
			
					$Narrative.="收入[".$ROW['CurrCode'].$ROW['Amount'].']';
					if($ROW['InvType']==1){
						$Narrative.="专票号:".$ROW['InvNo'];
					}else{

						$Narrative.="普票号:".$ROW['InvNo'];
					}
			}
			if (strlen($CustAct)==4){
				$Narrative.=$ROW['ToName'];
			}else{
				$Narrative.=substr($CustActName,strpos($CustActName,'-'));
			}
		}
		if ($ROW['InvType']==0){//专票进项
			//检测科目是否存在//无科��� 有regid
			
			//客户科目
			//$_SESSION['JournalDetail']->Type=$ROW['RegID'];
		
			$_SESSION['JournalDetail']->Add_To_GLAnalysis(0,//Debit
														($ROW['Tax']+$ROW['Amount']),
														$Narrative,//Narrative
														$CustAct,//GLcode
														$CustActName,
														$tag,
														'1',//Exrate
														0,  //ExAmount
														$CurrCode , //币种
														$ROW['RegID']);
			//税金			
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($ROW['Tax'],
														0,
														$Narrative,
														$TaxAct,
														$TaxActName,
														$tag,
														'1',
														0,
														$ROW['CurrCode']);
														
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($ROW['Amount'],
														0,
														$Narrative,
														$ToAct,
														$ToActName,
														$tag,
														'1',
														0,
														$ROW['CurrCode']);
													
				//var_dump($_SESSION['JournalDetail']->GLEntries);
		}elseif($ROW['InvType']==1||$ROW['InvType']==3){//销项
			
			$ExAmount=0;
		
				$_SESSION['JournalDetail']->Add_To_GLAnalysis(($ROW['Tax']+$ROW['Amount']),
																0,
																$Narrative,
																$CustAct,
																$CustActName,
																$tag,
																'1',
																$ExAmount,
																$CurrCode,
																$ROW['RegID']);
				//税金
				$_SESSION['JournalDetail']->Add_To_GLAnalysis(0,
															$ROW['Tax'],
																$Narrative,
																$TaxAct,
																$TaxActName,
																$tag,
																'1',
																0,
																$ROW['CurrCode']);

				$_SESSION['JournalDetail']->Add_To_GLAnalysis(0,
															$ROW['Amount'],
																$Narrative,
																$ToAct,
																$ToActName,
																$tag,
																'1',
																0,
																$ROW['CurrCode']);
		//echo $ROW['InvType'].'-='.$CustAct;
		
		
		}	
			
   
	}
}
//var_dump($_SESSION['JournalDetail']->GLEntries);
// exit;
	//var_dump($_SESSION['JournalDetail']);
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
		$_SESSION['JournalDetail']->JnlDate=$_POST['JournalDate'];
	}elseif(isset($ROW['TabDate'])){
		$_POST['JournalDate']=date('Y-m-d',strtotime($ROW['TabDate']));
	}
    $JournalNo=1;//?没有��用
    $r=1;
//按保存按钮执行以下
if ((isset($_POST['CommitBatch']) AND $_POST['CommitBatch']=="保存/缓存")||(isset($_POST['savemode']) AND $_POST['savemode']=="保存/模板")){

	//if (isset($_POST['CommitBatch']) AND $_POST['CommitBatch']=="保存/缓存"){
		if (isset($_GET['InvPrm']) && $_POST['GLManualCode']==''){		
		   
			if( (strlen($ToActArr['CustAct'])==4 && !isset($ROW['ToPrm']['CustAct']))||strlen($ROW['ToPrm']['CustAct'])==4){
			  //选择总账科目
			  if(strlen($ToActArr['CustAct'])==4 ){
				  $ToCustAct=$ToActArr['CustAct'];
				  $ToCustActName=$ToActArr['CustActName'];
			  }elseif( !isset($ROW['ToPrm']['CustAct'])||strlen($ROW['ToPrm']['CustAct'])==4){
				  $ToCustAct=$ROW['ToPrm']['CustAct'];
				  $ToCustActName=$ROW['ToPrm']['CustActName'];
			  }
			  
			   $custdata=array("customer"=> $ROW['ToName'] ,"registerno"=>$ROW['RegisterNo'], "tag"=>$ROW['tag'],  "bankaccount"=>$ROW['ToAccount'],"bank"=>'' ,"account"=>$CustAct,"flag"=>$ROW['flag'],"ToActType"=>$ROW['ToActType'],  "regid"=>$ROW['RegID']);
			  
			   $CreateAct=CustomerAccountCreate($custdata,$ToCustAct,'');
			   $AccountCreate=$CreateAct[0];
			   $regid=$CreateAct[1];
			   //echo $AccountCreate.'-='.$regid.'[-='.$ROW['flag'].'['.$ROW['RegisterNo']."<br/>";
			 
			   if (strlen($AccountCreate)>4){
				   foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
					   if (strlen($JournalItem->GLCode)<5){
						   $GLItem[]=array("ID"=>$JournalItem->ID,"GLCode"=>$JournalItem->GLCode,
						   "Debit"=>$JournalItem->Debit,  "Credit"=>$JournalItem->Credit, "Narrative"=>$JournalItem->Narrative, "GLActName"=>$JournalItem->GLActName, "tag"=>$JournalItem->tag, "Exrat"=>$JournalItem->Exrat, "Examount"=>$JournalItem->Examount, "Currcode"=>$JournalItem->Currcode);
						   $_SESSION['JournalDetail']->remove_GLEntry($JournalItem->ID);
						   $_SESSION['JournalDetail']->GLItemCounter++;
					   }	
					   // prnMsg($JournalItem->ID);				
				   }
				 // var_dump($GLItem);
				   $GLItem[0]["GLCode"]=$AccountCreate;
				   $GLItem[0]['Narrative'].=$ROW['ToName'] ;
				   $_SESSION['JournalDetail']->add_To_GLAnalysis($GLItem[0]['Debit'],
																   $GLItem[0]['Credit'],
																   $GLItem[0]['Narrative'],
																   $GLItem[0]['GLCode'],
																   $GLItem[0]['GLActName'],
																   $GLItem[0]['tag'],
																   $GLItem[0]['CurRate'],
																   $GLItem[0]['Examount'],
																   $GLItem[0]['Currcode'],
																   $regid );

			   }
		   }
		   //var_dump($_SESSION['JournalDetail']->GLEntries);
			//exit;
	   	}
		//prnMsg('//录��行数不能大于15');
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
				if ($_POST['currcode']!=$_SESSION['CompanyRecord'][$ROW['tag']]['currencydefault']){
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
			//转户���证判断
		
			if (($rowj+ $rowd)==count($_SESSION['JournalDetail']->TransNo)){
				
				if (($rowj+ $rowd>2)&&($rowj==$rowd)){		
					$inputerr=2;
				}						
			}
			if(min($credit,$debit)>1 ){
				//多借多贷				 				
				$inputerr=3;						
			}		
			if ($inputerr==3 ||$inputerr==2){
				prnMsg('你输入的凭证格式有误！</br>凭证格式:只能一借多贷或多贷多借，现金银行科目不能多借多贷！</br>','info');
			}
		//}
		$checkgl=0;
		//if(isset($_GET['InvPrm'])){
		foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
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
			}
			/*else{//收付款
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
								//���账款检验ok
								$checkgl=1;
							}								
					}
				}
			}*/
		}//foreach	
	 
		if($inputerr==0){		 
				//Start a transaction to do the whole lot inside
				$tagsgroup=$_SESSION['tagref'][$tag][1];		
			$result = DB_Txn_Begin();				
			$TransNo =GetTransNo( $_SESSION['JournalDetail']->Period, $db);			
			$TransType=GetTransType($_SESSION['JournalDetail']->GLEntries ,$GLTypeArr,$_SESSION['JournalDetail']->Period);
			$TagTypeNo = GetTagTypeNo($tagsgroup, $_SESSION['JournalDetail']->Period, $db);
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
				//prnMsg($TransType.'==6 &&'.  $JournalItem->GLCode.'!='.$ROW['Act'] .'&&'. $ROW['ToActType']);
				if  ((substr($JournalItem->GLCode,0,4)=='1122'||  substr($JournalItem->GLCode,0,4)=='2202')&&empty($JournalItem->RegID)){
					$SQL="SELECT `regid` FROM `register_account_sub` WHERE sub='".$JournalItem->GLCode."'";
					
					$Result=DB_query($SQL);
					$SubRow=DB_fetch_assoc($Result);
					if (empty($SubRow['regid'])){
						$SQL="INSERT INTO `erplogs`(`title`,
													`content`,
													`userid`,
													`logtype`,
													`logtime`) 
									VALUES (	'".$JournalItem->GLCode."' ,
												'查询REGID失败',
												'".$_SESSION['UserID']."',
												'10',
												'".date("Y-m-d h:i:s")."' )";
							$Result = DB_query($SQL,$ErrMsg);	
					}
				}else{
					$regid= $JournalItem->RegID;
				}
				//下面代码为应���账款、应付账款录入
				if  (substr($JournalItem->GLCode,0,4)=='1122'){ 
					/*

					$sql="SELECT  unitscode, branchcode FROM accountunits WHERE account='" . $JournalItem->GLCode . "' ";
					$result = DB_query($sql);
					if (DB_num_rows($result)==1){
					$row=DB_fetch_row($result);
					*/
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
														'" . $regid. "',
														'0',
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
													'" . $_SESSION['JournalDetail']->Period . "',
													'" . $regid . "',
													'0',
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
									VALUES (  '" . $TransType . "',
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
				  $sql="UPDATE invoicetrans SET transno=	'" . $TransNo . "',regid='". $regid ."' WHERE  invno=".$ROW['InvNo'];
					$result = DB_query($sql);
			}
		
			
			if(isset($_POST['savemode']) AND $_POST['savemode']=="保存/模板"){
				//没有使用	
				$accstr='';
				/*
				foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) {
					if ($ROW['Act']!=$JournalItem->GLCode){
						if ($accstr=='') {
							$accstr.= $JournalItem->GLCode;

						}elseif(strlen($JournalItem->GLCode)>6){
							$accstr.=','.$JournalItem->GLCode;	
						}
					}

				}*/
			
				
			}
			unset($_POST['JournalProcessDate']);
			unset($_POST['JournalType']);
			unset($_SESSION['JournalDetail']->GLEntries);
			unset($_SESSION['JournalDetail']);
			unset($_SESSION['GetUrl_Trans']);
			
			$JournalNo=1;
			unset($typarr);
			unset($edittranarr);
			DB_free_result($result);
		} 
		
		//Set up a newy in case user wishes to enter another 
		echo '<br />';
		prnMsg($_POST['JournalDate'].'会计凭证 记 ' . $TransNo . ' '._('has been successfully entered').', '.$msgerr,'success');
		$_SESSION['SelectInv'][5]= $TransNo ;
		echo '<meta http-equiv="refresh" content="1"/>';
		/*
		echo '<script type="text/javascript">
							window.opener=null;
							window.open("","_self"); 
							window.close();
							</script>';
							*/
	
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

if(strlen($ToActArr['CustAct'])==4||( !isset($ROW['ToPrm']['CustAct'])||strlen($ROW['ToPrm']['CustAct'])==4)){
	if(strlen($ToActArr['CustAct'])==4 ){
		$ToCustAct=$ToActArr['CustAct'];
		$ToCustActName=$ToActArr['CustActName'];
	}elseif( !isset($ROW['ToPrm']['CustAct'])||strlen($ROW['ToPrm']['CustAct'])==4){
		$ToCustAct=$ROW['ToPrm']['CustAct'];
		$ToCustActName=$ROW['ToPrm']['CustActName'];
	}
	echo '<div class="page_help_text">
	单位名称：'.$ROW['ToName'].'信用代码：'.$ROW['RegisterNo'].'<br>
	你选择了 '.$ToCustAct.'-'.$ToCustActName.' </br>';
	if (empty($ROW['ToName'])){
			echo '单位名称为空,不能自动生成科目！';	
	}		
		echo'</div>';	
	if (empty($ROW['ToName'])){
		
	    prnMsg("123456");
	
		unset($_SESSION['SelectInv'][5]);
		unset(	$_SESSION['JournalDetail']);
		unset($_SESSION['GetUrl_Trans']);
	//	echo '</div>
		//	</form>';
		//echo '<meta http-equiv="Refresh" content="1"; url=' . $RootPath . '/InvoiceJournallize.php">';
		header("location:InvoiceJournallize.php");
		//include('includes/footer.php');
		
		//exit;

		
	}
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

 if ( $checkflg==1||$ROW['Currcode']!=$_SESSION['CompanyRecord'][abs($ROW['tag'])]['currencydefault']){
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
		echo '<option value="' . $row['accountcode'] . ':'.$row['currcode'] .':'.htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false) . '" label="' . $row['accountcode'].'[' .$row['currcode'].']' .htmlspecialchars($row['accountname'], ENT_QUOTES,'UTF-8', false)  . '" />';
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


 /*elseif(isset($_GET['GLPrm']) && $dlsv==0){
		//	prnMsg('//银行凭证');
	 
		$amoj=0;
		$amod=0;
		$ExCuAmount=0;
		if (is_numeric($ROW['ExAmoJ'])){
			$amoj=$ROW['ExAmoJ'];
		}
		if(is_numeric($ROW['ExAmoD'])){
			$amod=$ROW['ExAmoD'];
		}
		$ExCuAmountexamo=$ROW['Amount'];
		$TransDate=date('Y-m-d',strtotime($ROW['TabDate']));
	
		$_POST['JournalDate']=$TransDate;
		$prd=DateGetPeriod($TransDate);
	
		$_SESSION['JournalDetail']->JnlDate=$TransDate;
		$_SESSION['JournalDetail']->Period=$ROW['Period'];	
		$_SESSION['JournalDetail']->TypeNo=$ROW['BankIndex'];
		$_SESSION['JournalDetail']->tag=$ROW['tag'];
		if (isset($ToActArr)){	
			//prnMsg('$ToActArr=0 binaktranid 1= 日期  2=币别  3=金额 4=转换方式[2本币-外币4 外币-本币  1 本币]  5->���币科目');
			$ToCurrCode=$ToActArr['ToBankActNumber'];
			$ToActType=$ToActArr['ToActType'];

		
		}else{
			$ToActType=0;
		}
		//prnMsg('凭证分类读取'.$ToActType.'='.$ToActArr['ToActType']);
		if ($ToActType>1){
			if ($ToActType==2)	{	
				//本币->外币
				$Amount=round(((float)$ROW['ExAmoJ']-(float)$ROW['ExAmoD']),2);//本币
				$amoj=round((float)$ROW['ExAmoJ'],2);
				$amod=round((float)$ROW['ExAmoD'],2);
				$CuAmount=$Amount;
				$CurrAccount=$ToActArr['ActName'];
				$ExCuAmount=$ToActArr['Amount'];//外���金额
				$narmsg="外币转户[".$ToCurrCode.$CurrAmo."]";
			}elseif($ToActType==4){
				//prnMsg('外币->������');

				if ((float)$ToActArr['Amount']>0){
					$amod=round((float)$ToActArr['Amount'],2);
					$amoj=0;
				}else{
					$amoj=round((float)$ToActArr['Amount'],2);
					$amod=0;
				}
				$Amount=-$ToActArr['Amount'];		
				$ExCuAmount=round(((float)$ROW['ExAmoJ']-(float)$ROW['ExAmoD']),2);
				$narmsg="外币转户[".$ROW['Currcode'] .$ExCuAmount."]";

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
			//外币  ���币		
		
			if ( $ROW['Currcode'] !=$_SESSION['CompanyRecord'][$ROW['tag']]['currencydefault'] ){
					//收外币
					$ExCuAmount=round(((float)$ROW['ExAmoJ']-(float)$ROW['ExAmoD']),2);
			
				if (round((float)$ROW['ExAmoJ'],2)!=0){//借方				
					$Amount=round($ROW['Amount'],2);
					$amoj=round($ROW['Amount'],2);
					$amod=0;
					$dcflag=1;//收款标记
					if  (substr($ROW['Act'],0,4)=='1122'){ 
						$narmsg="收货款[".$ROW['Currcode'].$ExCuAmount."]`".$ROW['ToName'];
					}else{
						$narmsg="收款[".$ROW['Currcode'].$ExCuAmount."]`".$ROW['ToName'];
					}			
				}else{
					$dcflag=-1;//付款标记
					$Amount=-round($ROW['Amount'],2);
					$amod=round($ROW['Amount'],2);
					$amoj=0;
					if  (substr($ROW['Act'],0,4)=='2202'){ 
						$narmsg="采购付款[".$ROW['Currcode'].$ExCuAmount."]`".$ROW['ToName'];
					}else{
						$narmsg="付款[".$ROW['Currcode'].$ExCuAmount."]`".$ROW['ToName'];
					}				
				}
				$rate=round(abs($ExCuAmount/$Amount),4);			
			}else{
				//prnMsg('//本币');		
				$Amount=round(((float)$ROW['ExAmoJ']-(float)$ROW['ExAmoD']),2);
				$amoj=round((float)$ROW['ExAmoJ'],2);
				$amod=round((float)$ROW['ExAmoD'],2);
				if ($ROW['ExAmoJ']!=0){
					$dcflag=1;//收款标记
					if  (substr($ROW['Act'],0,4)=='1122'||substr($ROW['Act'],0,4)=='2202'){ 
						$narmsg="收���款`".$ROW['ToName'];
					}elseif(substr($ROW['Act'],0,4)=='6603'){
						$narmsg="收利息`".$ROW['ToName'];
					}elseif(substr($ROW['Act'],0,4)=='1221'||substr($ROW['Act'],0,4)=='2241'){
						$narmsg="收往来款`".$ROW['ToName'];
					}else{
						$narmsg="收款`".$ROW['ToName'];
					}	
				}else{
					$dcflag=-1;//付款标记
			
					if  (substr($ROW['Act'],0,4)=='1122'||substr($ROW['Act'],0,4)=='2202'){ 
						$narmsg="采购付款`".$ROW['ToName'];
					}elseif(substr($ROW['Act'],0,4)=='6603'){
						$narmsg="付利息";
					}elseif(substr($ROW['Act'],0,4)=='6602'){
						$narmsg="费用支出`".$ROW['ToName'];
					}elseif(substr($ROW['Act'],0,4)=='6601'){
						$narmsg="销售费用`".$ROW['ToName'];
					}elseif(substr($ROW['Act'],0,4)=='5001'){
						$narmsg="成本费用支出`".$ROW['ToName'];
					}elseif(substr($ROW['Act'],0,4)=='5101'){
						$narmsg="制造费用支出`".$ROW['ToName'];
					}elseif(substr($ROW['Act'],0,4)=='5301'){
						$narmsg="研发支出费用".$ROW['ToName'];
					}else{
						$narmsg="付款`".$ROW['ToName'];
					}
				}
					$rate=1;	//本币
			}	
		}//endif450
	
			if (isset($AccCurr[$ROW['BankAct']])){//外币检测
				$curr_j=$AccCurr[$ROW['BankAct']];
				$ExCuAmo_j=$ExCuAmount;
			}else{
				$ExCuAmo_j=0;
				$curr_j=$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['currencydefault'];
			}
			if (isset($AccCurr[$ROW['Act']])){
				$curr_d=$AccCurr[$ROW['Act']];
				$ExCuAmo_d=$ExCuAmount;
			}else{
				$ExCuAmo_d=0;
				$curr_d=$_SESSION['CompanyRecord'][$_SESSION['JournalDetail']->tag]['currencydefault'];
			}
			$ToAct=$ROW['Act'];
			$ToActName=$ROW['ActName'];
			if (isset($ToActArr['ToAct'])&&$ToActArr['ToAct']!=''){
				//手动科目存在使用手动科目			
				$ToAct=$ToActArr['ToAct'];
				$ToActName=$ToActArr['ToActName'];
			}	
		//	prnMsg($amoj.'-'.$amod.';'.$narmsg.'['.	$ROW['BankAct'].']'.$accountbank[$ROW['BankAct']][0].'=dc'.$dcflag.'['.$ROW['tag'].']'.$ExCuAmo_j.']'.$curr_j);

		if ($dcflag==1){//收款银行
			
			//prnMsg($amoj.'-'.$amod.';'.$narmsg.'['.	$ROW['BankAct'].']'.$accountbank[$ROW['BankAct']][0].		'['.$ROW['tag'].']'.$ExCuAmo_j.']'.$curr_j);
		
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amoj,
														$amod,
														$narmsg,
														$ROW['BankAct'],
														$accountbank[$ROW['BankAct']][0],
														$ROW['tag'],
														'1',
														$ExCuAmo_j,
														$curr_j);
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amod,
															$amoj,
															$narmsg,
															$ToAct,
															$ToActName,
															$ROW['tag'],
															'1',
															$ExCuAmo_d,
															$curr_d);
          // var_dump($_SESSION['JournalDetail']->GLEntries);
		}else{
			
			//prnMsg($amoj.$amod.$narmsg.	$ROW['BankAct'].$accountbank[$ROW['BankAct']][0]);
		
			//付款
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amoj,
															$amod,
															$narmsg,
															$ROW['BankAct'],
															$accountbank[$ROW['BankAct']][0],
															$ROW['tag'],
															'1',
															$ExCuAmo_j,
															$curr_j);
			$_SESSION['JournalDetail']->Add_To_GLAnalysis($amod,
															$amoj,
															$narmsg,
															$ToAct,
															$ToActName,
															$ROW['tag'],
															'1',
															$ExCuAmo_d,
															$curr_d);
		}	  	
		//var_dump($_SESSION['JournalDetail']->GLEntries);
		}elseif($_GET['ty']==3 && $dlsv==0){//结账
			$glarr=json_decode($ROW[2]);

			$narmsg=$ROW['BankAct']."月末结账";		
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
																$ROW['tag'],
																'1',
																0,
																'CNY');
					
			}
		}//endif
	*/ 
?>
