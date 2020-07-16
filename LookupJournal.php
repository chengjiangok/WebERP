
<?php
/* $Id: CreateJournal.php

 * @Author: mikey.zhaopeng 
 * @Date: 2019-04-03 02:42:32 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-04-03 09:13:15
 */

include('includes/JournalEntryClass.php');
include('includes/session.php');
$Title ='查找会计凭证';
$ViewTopic = 'GeneralLedger';
$BookMark = 'Journals';
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
			//alert(n);
			alert(m);
			document.getElementById("GLManualCode").value="";
			return false;
		}		
</script>';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
if (isset($_GET['InvPrm'])){//发票生成
	/*
	$TransArr=explode('^',urldecode($_GET['InvPrm']));
	$tag=$TransArr[10];
	$InvType=$TransArr[2];*/
		
	$TransArr=json_decode(str_replace('\"','"',urldecode($_GET['InvPrm'])),JSON_UNESCAPED_UNICODE);

	//var_dump($TransArr);
	
	$tag=$TransArr["tag"];
	$InvType=$TransArr['InvType'];
	$_SESSION['GetUrlLookup']='?InvPrm='.urlencode(json_encode($TransArr,JSON_UNESCAPED_UNICODE)); 
	
}elseif (isset($_GET['prm'])){
		
		$prm=explode('^',url_decode($_GET['prm']));
		
		if (isset($_GET['topara'])&& $_GET['topara']!=''){
			$topara=explode('^',$_GET['topara']);
		}
		
		if (isset($_GET['tab'])){
			if($_GET['tab']==3){  //结账			
				$tag=$prm[0];
	
			}elseif ($_GET['tab']==1){//发票
				$tag=$prm[10];

			}elseif ($_GET['tab']==2){//银行
		
				$tag=$prm[10];
			}
		}
		//prnMsg(	url_decode($_GET['prm']).'<br>'.$_GET['topara']);
	}elseif (isset($_GET['GLUrl'])){
		//银行凭证生成
		
		//$TransArr=explode('^',urldecode($_GET['GLPrm']));
		$TransArr=json_decode(str_replace('\"','"',urldecode($_GET['GLPrm'])),JSON_UNESCAPED_UNICODE);
	
		//var_dump($TransArr);
		$tag=$TransArr["tag"];
		if (isset($_GET['ToActPrm'])){
			//prnMsg(urldecode($_GET['ToActPrm']));
			$ToActArr=json_decode(str_replace('\"','"',urldecode($_GET['ToActPrm'])),JSON_UNESCAPED_UNICODE);
			
			//print_r($ToActArr);
		}
	
		//$ToActArr=json_decode(str_replace('\\','',$jsonToAct),true);
		$_SESSION['GetUrlLookup']='?GLUrl='.urlencode(json_encode($TransArr,JSON_UNESCAPED_UNICODE)); 
		if (isset($ToActArr)){
			$_SESSION['GetUrlLookup'].='&ToActPrm='.urlencode(json_encode($ToActArr,JSON_UNESCAPED_UNICODE)); 
		}
	   //  prnMsg($_SESSION['GetUrl_Trans']);		
	
		
	}elseif (isset($_GET['TransUrl'])){
		//银行凭证生成
		
		$TransArr=json_decode(str_replace('\"','"',urldecode($_GET['TransUrl'])),JSON_UNESCAPED_UNICODE);
	
		$_SESSION['GetUrlLookup']='?TransUrl='.urlencode(json_encode($TransArr,JSON_UNESCAPED_UNICODE)); 	
		//prnMsg(urldecode($_SESSION['GetUrlLookup']));
	   		
	}else{
		unset($prm);
		prnMsg('页面引导错误！','info');
		sleep(3);
		if (isset($_POST['Confirm'])){
			echo "<script>window.close();</script>";
		}
		//include('includes/footer.php');
		exit;
	}	
	//if (!isset($_GET['TransUrl'])&&!isset($_GET['GLUrl'])){
	//	unset($_SESSION['GetUrlLookup']);
	//}//
	/*
	$flag='?';
	if (isset($_GET['InvPrm'])){//发票生成
		$flag.='InvPrm='.$_GET['InvPrm'];
	}else{
		if (isset($_GET['tab'])){
			$flag.='tab='.$_GET['tab'].'&';
		}
		if (isset($_GET['prm'])){
			$flag.='prm='.$_GET['prm'];
		}  
	}
	
	$_SESSION['GetUrlLookup']=$flag;*/
	if (isset($_SESSION['GetUrlLookup']) AND strlen($_SESSION['GetUrlLookup'])>3){
		echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') .$_SESSION['GetUrlLookup']. '"  method="post" name="form">';
	}else{
	    echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" name="form">';
    }
	
	//echo'<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
	echo'<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />	';
		echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title ;
	if ($_GET['InvPrm']){	
		echo'<br>发票号码:'. $TransArr['InvNo'].',发票类型:'.$TransArr['InvType'].'金额'.$TransArr[3].'税额'.$TransArr['Tax'];
	}
		echo'</p>';
	
    if (isset($_GET['TransUrl'])){
		if ($TransArr['ToType']==1){
		$sql="SELECT  `type`,
		              `transno`,
					  `trandate`,
					  `account`,
					  accountname ,
					  `narrative`,
					  `amount`,
					  `posted`,
					  currcode,
					  gltrans.tag,
					   `flg` 
				FROM `gltrans` 
				LEFT JOIN chartmaster ON gltrans.account=accountcode
				WHERE periodno=".$TransArr['Period']."
						AND posted=0
						AND transno=".$TransArr['TransNo'];
			$Result=DB_query($sql);
			//得到转出科目和金额
			
			while($row=DB_fetch_array($Result)){
				if ($TransArr['BankAct']!=$row['account']){
					$ToBankTrans[]=array("Act"=>$row['account'],"Amount"=>$row['amount'],"CurrCode"=>$row['currcode'],"tag"=>$row['tag']);
				}
			}
		    // var_dump($ToBankTrans);
			if($_SESSION['CompanyRecord'][$ToBankTrans[0]['tag']]['currencydefault']==$ToBankTrans[0]['CurrCode']){
				//、、$SQL="SELECT banktransid, bankdate, amount, flag, flg FROM banktransaction WHERE  account='".$ToBankTrans[0]['Act']."'   AND transno=0  ORDER BY banktransid,bankdate";
				$SQL="SELECT banktransid, bankdate, amount, flag, flg FROM banktransaction WHERE  account='".$ToBankTrans[0]['Act']."'  AND amount='".$ToBankTrans[0]['Amount']."' AND  DATE_FORMAT(bankdate,'%Y-%m-%d')='".date('Y-m-d',strtotime($TransArr['ToBankDate']))."' AND transno=0  ORDER BY banktransid,bankdate";
			}
			//交易记录 种转户标记2，4   1	0
		$result=DB_query($SQL);
		}
			//prnMsg($SQL.$ToBankTrans[0]['tag']);
						
	}elseif($TransArr['InvType']==0){

		//进项专票%
		$sql="SELECT  `type`,
		              `transno`,
					  `trandate`,
					  `account`,
					  accountname ,
					  `narrative`, 
					  `amount`,
					  `posted`,
					  gltrans.tag,
					  `flg` 
				FROM `gltrans` 
				LEFT JOIN chartmaster ON gltrans.account=accountcode
			    WHERE periodno=".$TransArr['Period']."
						AND posted=0
						AND  transno IN (SELECT  `transno` FROM `gltrans`	WHERE periodno=".$TransArr['Period']."
											AND amount=".$TransArr['Tax']."
											AND account 
											IN (SELECT `taxcatact` FROM `stockcategory` WHERE stocktype='B'))";
		$Result=DB_query($sql);
		//prnMsg($sql);

	}elseif($TransArr['InvType']==1){

		//销售专票13%
		$sql="SELECT  `type`,
		              `transno`,
					  `trandate`,
					  `account`,
					  accountname ,
					  `narrative`, 
					  `amount`,
					  `posted`,
					  gltrans.tag,
					  `flg` 
				FROM `gltrans` 
				LEFT JOIN chartmaster ON gltrans.account=accountcode
			    WHERE periodno=".$TransArr['Period']."
						AND posted=0
						AND  transno IN (SELECT  `transno` FROM `gltrans`	WHERE periodno=".$TransArr['Period']."
											AND amount=-".$TransArr['Tax']."
											AND account 
											IN (SELECT `taxcatact` FROM `stockcategory` WHERE stocktype='B'))";
		$Result=DB_query($sql);
		//prnMsg($sql);

	}
	/*   0 $myrow['registerno'].'^'.
			 1  $myrow['invno'].'^'.
			 2  $myrow['invtype'].'^'.
			 3  $myrow['amount'].'^'.
			 4  $myrow['tax'].'^'.
			 5  $TranNoGL.'^'.
			 6  $CustAct.'^'.
			 7  $CustActName.'^'.
			 8  $myrow['invdate'].'^'.
			 9  $myrow['toname'].
			 10 $myrow['tag'].'^'.
			 11 $InvCurrType.'^'.
			 12 $TransType.'^'.
			 13 $myrow['period'].'^'.
			 14 $myrow['currcode'].'^'.
			 15 $RegID.'^'.
			 16 $TaxAct.'^'.
			 17 $TaxActName;*/


//	prnMsg($sql);
	//exit;
if (DB_num_rows($Result)>=2){
	//	if ($TransArr['ToType']==1){
		echo '<table class="selection" width="700">		
			<tr> 
			 <th colspan="7"  class="centre" >会计凭证对应交易</th>
			</tr>';
		echo'<tr>
					<th>交易序号</th>	
					<th>会计科目</th>			
					<th>交易日期</th>
					<th>币种</th>
					<th>汇率</th>
					<th>金额</th>
					<th>选择</th>
				</tr>';	
		db_data_seek($Result,0);
		$checked="checked";
		while($row=DB_fetch_array($result)){
			if ($k==1) {
				echo '<tr class="OddTableRows">';
				$k=0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k++;
			}
			echo '	<td>'.$row['banktransid'].'</td>
					<td>'.$ToBankTrans[0]['Act'].'</td>					
					
					<td>'.$row['bankdate'].'</td>
					<td>'.$ToBankTrans[0]['CurrCode'].'</td>
					<td>'.abs(round($TransArr['Amount']/$row['amount'],4)).'</td>
					<td>'.locale_number_format($row['amount'],2).'</td>
					<td><input type="radio" name="ToBankIndex[]" value="'.$row['banktransid'].'"  '.$checked.' ></td>	
				</tr>';
				$checked='';
		}
	//	}
	echo '<table class="selection" width="700">		
			<tr>  <th colspan="6"  class="centre" >查找到的会计凭证</th>
			</tr>';
			$TransNo=0;
			$rw=0;
			$k=0;
			$TotalDebit=0;
			$TotalCredit=0;
			$chk="checked";
			while($row=DB_fetch_array($Result)){
				if (($row['amount']>0 && $row['flg']==1)||($row['amount']<0 && $row['flg']==-1)){
					$debit=$row['amount'];
					$credit=0;
					$TotalDebit+=$debit;
				}else{
					$debit=0;
					$credit=-$row['amount'];
					$TotalCredit+=$credit;
				}
				if ($TransNo!=$row['transno']){
					
					echo'<tr>
							<th colspan="2">
								选择<input type="checkbox" name="Select[]" value="'.($row['transno']).'" '.$chk.'/></th>
							<th colspan="4"  class="centre" >
								记字:' .$row['transno'] .'
								日期:' .$row['trandate']. '
							</th>
					</tr>';
					if (isset($chk)){
						unset($chk);
					}
					echo'<tr>
							<th>序号</th>						
							<th>科目编码</th>
							<th>科目名称</th>
							<th>借方金额</th>
							<th>贷方金额</th>
							<th>摘要</th>
						</tr>';	
					if ($k==1) {
						echo '<tr class="OddTableRows">';
						$k=0;
					} else {
						echo '<tr class="EvenTableRows">';
						$k++;
					}
					$rw++;	
					if ($rw==1){		
					echo '<td>'.$rw.'</td>						
							<td>'.$row['account'].'</td>
							<td>'.$row['accountname'].'</td>
							<td>'.locale_number_format($debit,2).'</td>
							<td>'.locale_number_format($credit,2).'</td>
							<td>'.$row['narrative'].'</td>
						</tr>';
					}else{
						$rw=0;
					echo'<td></td>						
							<td></td>
							<td>合计</td>
							<td>'.locale_number_format($TotalDebit,2).'</td>
							<td>'.locale_number_format($TotalCredit,2).'</td>
							<td></td>
						</tr>';
					}				
					$TransNo=$row['transno'];
				}else{
					if ($k==1) {
						echo '<tr class="OddTableRows">';
						$k=0;
					} else {
						echo '<tr class="EvenTableRows">';
						$k++;
					}
					$rw++;
					echo '
							<td>'.$rw.'</td>						
							<td>'.$row['account'].'</td>
							<td>'.$row['accountname'].'</td>
							<td>'.locale_number_format($debit,2).'</td>
							<td>'.locale_number_format($credit,2).'</td>
							<td>'.$row['narrative'].'</td>
						</tr>';					
				}
			}//endwhile
			echo '<td></td>						
					<td></td>
					<td>合计</td>
					<td>'.locale_number_format($TotalDebit,2).'</td>
					<td>'.locale_number_format($TotalCredit,2).'</td>
					<td></td>
				</tr>';	
	echo '</table>';
	echo '<br />		
			<div class="centre">
				<input type="submit" name="Confirm" value="确认保存" />
			</div>';

}else{
		
		prnMsg('未找到对应的凭证!','info');
		include('includes/footer.php');
		//sleep(24);
	
		//echo "<script>window.close();</script>";
		exit;
}
if (isset($_POST['Confirm'])){
	if (isset($_GET['TransUrl'])){
		//prnMsg('银行交易凭证');
		
		$Result=DB_Txn_Begin();
		$SQL="UPDATE `gltrans` SET posted=1 WHERE periodno='".$TransArr['Period']."' AND transno='".$TransArr['TransNo']."' AND account='".$ToBankTrans[0]['Act']."'";
		$Result=DB_query($SQL);
		$SQL="UPDATE `banktransaction` SET `transno`='".$TransArr['TransNo']."',`period`='".$TransArr['Period']."' WHERE `banktransid`='".$_POST['ToBankIndex'][0]."' AND `account`='".$ToBankTrans[0]['Act']."'";
		$Result=DB_query($SQL);
		$Result=	DB_Txn_Commit();
		if ($TransArr['ToType']==1){
			 prnMsg('内部转户交易会计凭证:'.$TransArr['TransNo'].'绑定交易序号'.$_POST['ToBankIndex'][0].'成功!','success');
		}else{
			prnMsg('发票号:'.$TransArr['InvNo'] .'对应的会计凭证:'.$_POST['Select'][0].'绑定成功!','success');
		}

	}else{
		$Result=DB_Txn_Begin();
		$SQL="UPDATE `gltrans` SET posted=1 WHERE periodno=".$TransArr['Period']." AND transno=".$_POST['Select'][0];
		$Result=DB_query($SQL);
		$SQL="UPDATE `invoicetrans` SET   transno=".$_POST['Select'][0].", period=".$TransArr['Period']."  WHERE  `invno`='".$TransArr['InvNo']."' AND`invtype`=".$TransArr['InvType'];
		$Result=DB_query($SQL);
		$Result=	DB_Txn_Commit();
		prnMsg('发票号:'.$TransArr['InvNo'] .'对应的会计凭证:'.$_POST['Select'][0].'绑定成功!','success');
	//}
	
	}
	unset($_SESSION['GetUrlLookup']);
	//sleep(7);
	//if (isset($_POST['Confirm'])){
		echo "<script>window.close();</script>";
	
}
echo'</div>       
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
function GetJournaltype(&$JournalDetail,&$GLTypearr,$prd_){
	//判断凭证类型使用jouranltype表
	$styp=0;
	$typ=-1;
	$ftyp=0;
	$flg=1;

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
		$result=DB_query("SELECT  typeno FROM gltrans WHERE  type=".$typ." AND periodno=".$prd_);
		$row=DB_fetch_row($result);
	
		if (empty($row)){
			$typarr=array($typ,1);
		}else{
			$typarr=array($typ,($row[0]+1));
		}
        $typarrs=$typ.'='.$ftyp.'+'.$styp.'=]'.$glrow;
		return $typarr;
	}
	
	  //判断凭证类型
	 
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
						if (strpos($GLTypearr[$i][2],$val[0])!==false){
				
						array_push($typidarr,$GLTypearr[$i][0]);
						unset($journalarr[$key]);
						break;
					}
                    
				}

			}
		}
	
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
 
	$result=DB_query("SELECT  typeno FROM gltrans WHERE  type=".$typ." AND periodno=".$prd_);
	$row=DB_fetch_row($result);

	if (empty($row)){
		$typarr=array($typ,1);
	}else{
		$typarr=array($typ,($row[0]+1));
	}

	
	return $typarr;
}
  
?>
