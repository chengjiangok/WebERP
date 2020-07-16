
<?php
/* $Id:InvoiceJournallize.php  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2017-10-29 04:53:56 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-08 10:32:09
 * version 201809151043
  */
include ('includes/session.php');
$Title = '发票凭证生成';
$ViewTopic = 'MyTools';// Filename's id in ManualContents.php's TOC.
$BookMark = 'InvoiceJournallize';
include('includes/header.php');
echo'<script type="text/javascript">
function OnAmountCurr(S, R) {

    var obj =document.getElementById("UnitsTag");
	var index = obj.selectedIndex; // 选中索引
	var currdefault =obj.options[index].value.split("^")[0];	
	var hrf=document.getElementById("href"+R);
	var oUrl=hrf.href;
	var paramName="ToActPrm";		
	var urlen=oUrl.indexOf(paramName);	
	var url=oUrl.slice(0,urlen);
	var jsonPara=decodeURIComponent(oUrl.slice(urlen+9));	
	var jsObj=JSON.parse( jsonPara);
		jsObj.CurrAmo=S.value;
	var rmk0=jsObj.remark;
	
	if (rmk0.indexOf("]")>0){					
		
		var rmk2len=(rmk0.indexOf("]"));
		var rmk2=rmk0.slice(rmk2len+1);
			rmk0="["+jsObj.CurrCode+S.value+"]"+rmk2;
 
	}
	jsObj.remark=rmk0;
	jsonStr= JSON.stringify(jsObj);
	hrf.setAttribute("href",url+paramName+"="+encodeURI(jsonStr));	
	document.getElementById("SelectCustAct"+R).value=jsonStr;	
	
}
function OnSelectAct(S, tA,R, t,m){

	//alert("客户科目新版更新20190930"+t);  	
	var url;
	var jsonPara;
	var jsObj;
	var currdefault = document.getElementById("CurrencyDefault").value;	
	var AmoCurr= document.getElementById("AmountCurr"+R);
	var hrf=document.getElementById("href"+R);
	var oUrl=hrf.href;

	var paramName="ToActPrm";		
	var urlen=oUrl.indexOf(paramName);
	console.log(S.value+currdefault);
	var  slen=S.value.indexOf(":");
	var splt="^";
	var Act;
	var currrate;
	if (slen>0){
		splt=":";
	}
     if (urlen>0){
	         url=oUrl.slice(0,urlen);		
	         jsonPara=decodeURIComponent(oUrl.slice(urlen+9));	
		      jsObj=JSON.parse( jsonPara);//方法用于将个 JSON 字符串转换为对象。	
		    Act=(S.value).split(splt); 
		if (currdefault!=Act[1]){
			AmoCurr.hidden="";
			jsObj.CustRegID=Act[3];
			jsObj.CurrRate=Act[4];
			jsObj.CurrCode=Act[3];	
			currrate=Act[4];
		}	
		
		//写入更新科目
		jsObj.CustAct=Act[0];
		jsObj.CustActName=Act[1];
	}else{
		jsonPara=decodeURIComponent(oUrl);	
		url=oUrl+"&";
		Act=(S.value).split(splt); 
		if (currdefault!=Act[1]){
			AmoCurr.hidden="";
		}
		jsObj ={
			    CustAct: Act[0],		
			　　 CustActName: Act[2],
				CurrCode: Act[1],
				CurrRate: Act[4]
			}
       console.log(jsObj);

	}
	
	//var rmk0=document.getElementById("rmk"+R).value;
	//var rmk=document.getElementById("remark"+R).value;

	if (t==4){	
		var amt=document.getElementById("Amount"+R).value;			
		amt=(amt*Act[4]).toFixed(2);	
		console.log(amt);
		document.getElementById("AmountCurr"+R).value=amt;
		jsObj.CurrAmo=amt;				
		document.getElementById("AmountCurr"+R).title=Act[1]+"汇率:"+Act[4];
		//摘要
		/*
		if (rmk0.indexOf("`")>0){		
			var rmk2len=(rmk0.indexOf("`")+1);
			var rmk2=rmk0.slice(rmk2len);				
			rmk0=Act[2]+" "+rmk2;	 
		}else{
			if (currdefault!=Act[1]){
				rmk0=Act[2]+" "+rmk0;
			}
		}
		if (currdefault!=Act[1]){
			rmk="["+Act[1]+amt+"]"+rmk;			
		}		
		document.getElementById("JournalType"+R).value=1;
		jsObj.msg=rmk0;
		jsObj.remark=rmk;*/
		
	}
	//document.getElementById("rmk"+R).value=rmk0;
	//document.getElementById("remark"+R).value=rmk;
	
	jsonStr= JSON.stringify(jsObj);
	console.log(jsonStr);
	hrf.setAttribute("href",url+paramName+"="+encodeURI(jsonStr));
		
	document.getElementById("SelectCustAct"+R).value=jsonStr;

	var check = document.getElementById("chkbx"+R);
	check.checked=true;	
	return false;
}
function OnToSelect(S, tA,R, t,m) {
	//alert("新版客对应科目20200423");
	var url;
	var jsonPara;
	var jsObj;

	var hrf=document.getElementById("href"+R);
	var oUrl=hrf.href;
		
	var paramName="ToActPrm";		
	var urlen=oUrl.indexOf(paramName);

	if (urlen>0){
		url=oUrl.slice(0,urlen);
		jsonPara=decodeURIComponent(oUrl.slice(urlen+9));
		
		console.log(jsonPara);
		
		jsObj=JSON.parse( jsonPara);
		
		var OptionAct=S.value.split("^");
			jsObj.ToAct=OptionAct[0];
			jsObj.ToActName=OptionAct[1];
	}else{
		jsonPara=decodeURIComponent(oUrl);	
		url=oUrl+"&";
		var SelectAct=(S.value).split("^"); 
		jsObj ={
			    ToAct: SelectAct[0],		
			　　 ToActName: SelectAct[1] 
			}
       //console.log(jsObj);
	}
	var jsonStr= JSON.stringify(jsObj);	
	hrf.setAttribute("href",url+paramName+"="+encodeURI(jsonStr));
		
	//document.getElementById("ToSelectAct"+R).value=jsonStr;	
		
	var check = document.getElementById("chkbx"+R);
			check.checked=true;	
	return false;
}
function OnRemark(S, R) {
	//新版更新自己20190930  废弃
	var hrf=document.getElementById("href"+R);
	var oUrl=hrf.href;
	var paramName="ToActPrm";		
	var urlen=oUrl.indexOf(paramName);

	var url=oUrl.slice(0,urlen);
	var jsonPara=decodeURIComponent(oUrl.slice(urlen+7));	

	var jsObj=JSON.parse( jsonPara);
	var rmk0=jsObj.remark;
	//console.log("=="+rmk0);
	if (rmk0==undefined){
		rmk0=S.value;
	}else{
		if (rmk0.indexOf("]")>0 && rnk0!=""){					
			
			var rmk2len=(rmk0.indexOf("]"));
			var rmk2=rmk0.slice(0,rmk2len+1);
				rmk0=rmk2+S.value;
	
		}
	}
	jsObj.remark=rmk0;
     // console.log(rmk0);
	var jsonStr= JSON.stringify(jsObj);
	
    hrf.setAttribute("href",url+paramName+"="+encodeURI(jsonStr));	
	document.getElementById("ToSelectAct"+R).value=jsonStr;	

	var check = document.getElementById("chkbx"+R);
	check.checked=true;		
	
}
function OnChkbx(S,R){
	if (S.checked==true){
		document.getElementById("JournalType"+R).value=1;
	}else{
		document.getElementById("JournalType"+R).value=-2;
	}
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
	if (!isset($_POST['ERPPrd'])){ 	
		if (isset($_SESSION['SelectInv'])){
		
			$_POST["ERPPrd"]=$_SESSION['SelectInv'][0];
		}else{
			$_POST["ERPPrd"]=$_SESSION['period'];
		}
		
	}
	if (!isset($_POST['UnitsTag'])){ 	
		if (isset($_SESSION['SelectInv'])){
		
			$_POST["UnitsTag"]=$_SESSION['SelectInv'][1];
		}else{
			$_POST["UnitsTag"]=$_SESSION['period'];
		}
		
	}
	if (!isset($_POST['prdrange'])){ 	
		if (isset($_SESSION['SelectInv'])){
		
			$_POST["prdrange"]=$_SESSION['SelectInv'][3];
		}else{
			$_POST["prdrange"]=$_SESSION['period'];
		}
		
	}
	if (!isset($_POST['InvFormat'])){ 
		if (isset($_SESSION['SelectInv'])){
	
			$_POST["InvFormat"]=$_SESSION['SelectInv'][2];
		}else{
			$_POST['InvFormat']=-1;
		}
	}
	$InvName=array(-1=>'全部发票',0=>'进项专票',2=>'采购普票',1=>'销项专票',3=>'销项普票',5=>'销项0税率普票');
echo '<div class="page_help_text">通过导入的进项、销项税务发票导出文件、自动生成会计凭证！</div><br />';
echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="InvFormat" value="' . $_POST['InvFormat'] . '" />';
echo '<table  class="selection"><tr>
	      <td>' . _('Select Period To')  . '</td>
		  <td >';
		  SelectPeriod($_SESSION['period'],$_SESSION['janr']);
		
		$rang=array('0'=>'月度', '3'=>'季度','12'=>'本年');//,'24'=>'上年');//,'36'=>'前年');
	
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
  	echo'</td></tr>';
	echo '<tr>
			<td>单元分组</td>
			<td>';
			SelectUnitsTag(2);		
	echo'</td>
		</tr>';
	echo'<tr>
			<td>发票种类</td>
			<td><select name="InvFormat">';		
		foreach($InvName as $key=>$value){
			if (isset($_POST['InvFormat']) and ($_POST['InvFormat']==$key)){
				echo '<option selected="selected" value="' ;
			}else {
				echo '<option value ="';
			}
				echo   $key.'">'.$value.'</option>';
		}				
   echo'</select>
		</td></tr>
		</table>';
		$SelectDate=PeriodGetDate($_POST['ERPPrd']);
		if ($_POST['prdrange']==0){
			 $firstprd=$_POST['ERPPrd'];
			 $endprd=$_POST['ERPPrd'];
			 $_POST['AfterDate']=FormatDateForSQL(date('Y-m-01',strtotime ($SelectDate)));
			 $_POST['BeforeDate']=FormatDateForSQL($SelectDate);		
	   }elseif ($_POST['prdrange']==3) {
			$firstprd=$_SESSION['janr'];
			$endprd=$_POST['ERPPrd'];	
	
	   }elseif ($_POST['prdrange']==12) {
			$firstprd=$_SESSION['janr'];
			$endprd=$_POST['ERPPrd'];		
	   }
	   /*
	  $sql="SELECT  invtype ,MAX(invdate) invdt FROM invoicetrans GROUP BY invtype";
	  $result=DB_query($sql);
  
	  while ($row= DB_fetch_array($result)) {
		  $querymsg.= $InvName[$row['invtype']].'最末开票日期：'.$row['invdt'].'<br>';
	  }

	  $sql="SELECT SUM(CASE WHEN length(custname)<=15 THEN 1 ELSE 0 END) prv,SUM(CASE WHEN length(custname)>15 THEN 1 ELSE 0 END) com FROM registername WHERE account='' AND flg=0";
	  $result=DB_query($sql);
      $row=DB_fetch_assoc($result); 
	  prnMsg($row['prv'].'个人无科目设置,'.$row['com'].'个单    无科目设置！','info');	 */	  
   echo'<br><div class="centre">		    
			<input type="submit" name="Search" value="发票查询">';
	if (isset($_POST['Search'])&&$_POST['prdrange']==0){			
	   echo'<input type="submit" name="TransSave" value="凭证保存">	';
	   			
	}
	echo '</div>';
	$tag=$_POST['UnitsTag'];

	if (!isset($SubjectRule)){
		
		$sql="SELECT acctype, 
					 account,
					 remark, 
					 jd, 
					 maxamount,  
					 srtype,
					 flg ,
					 currcode 
				FROM subjectrule a 
				LEFT JOIN chartmaster b ON a.account=b.accountcode 
				WHERE srtype=5 ORDER BY acctype,srtype,flg";
		
		$result = DB_query($sql);		
		//$SubjectRule=[];
		//发票对应的科目//根据发票类型读取则 进项0 销售1 ,3
		while ($row = DB_fetch_array($result)) {
		    if (substr($row['account'],0,4)=='2221'){
				$SubjectTax[$row['acctype']]=array($row['account'],$row['remark']);

			}else{
				$SubjectRule[$row['acctype']][]=array("account"=>$row['account'],"actname"=>$row['remark'],"jd"=>$row['jd'], "maxamount"=>$row['maxamount'],"flag"=>$row['flg'],"acctype"=>$row['acctype'],"currcode"=>$row['currcode']);
			}
		}		
	}		
			//$S ubjectRule[$row['acctype']][]=array($row['account'],$row['remark'], $row['srtype'],$row['jd'], $row['maxamount'],  $row['flg'],$row['currcode']);
	echo'<input type="hidden" name="CurrencyDefault" id="CurrencyDefault" value="'.	$_SESSION['CompanyRecord'][$tag]['currencydefault'].'">	';
if (isset($_POST['CheckSave'])||isset($_POST['TransSave']) OR isset($_POST['Search'])	OR  isset($_POST['Go'])	OR isset($_POST['Next']) 
	OR isset($_POST['Previous']) ||isset( $_SESSION['SelectInv'])) {

	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}
	$_SESSION['SelectInv']=array($_POST['ERPPrd'],$_POST['UnitsTag'],$_POST['InvFormat'],$_POST['prdrange']);   
	/*
		//读取注册号资料	
		if ((isset($accsub) && count($accsub)<1)||!isset($sccsub)){
			$sql="SELECT A.regid,A.registerno,A.tag, B.account,C.accountname,A.subject, A.acctype, A.flg FROM registeraccount A LEFT JOIN registername B ON B.regid=A.regid LEFT JOIN chartmaster C ON C.accountcode=B.account WHERE A.tag=".$tag;
			$result = DB_query($sql);
			while ($row = DB_fetch_array($result)) {			
					$accsub[$row['registerno']]=array($row['regid'],$row['subject'],$row['account'],$row['acctype'],$row['flg'],$row['accountname']);			
			}
		}*/
		//读取解析科目则$ S ubjectRule	
		//根据注册码设定对应支出科
		if ((isset($GLTemplet) && count($GLTemplet)<1)|| !isset($GLTemplet)){
			$sql="SELECT registercode,
						 account,
						 b.accountname,
						 jd,
						 maxamount,
						 remark
					FROM gltemplet a
					LEFT JOIN chartmaster b ON a.account=b.accountcode
					WHERE a.tag=".$tag."  AND tpdate<='".$_SESSION['lastdate']."'AND a.acctype IN (0,1,3) ";
			$result = DB_query($sql);
			
			$GLTemplet=array();
			//根据注册码设定特殊科目解析
			while ($row = DB_fetch_array($result)) {
				$GLTemplet[$row['registercode']]=array($row['account'],$row['accountname'], $row['jd'], $row['maxamount'],  $row['remark']);
			}
		}
		/*
		if ($_SESSION['Currency']==1){
			$sql="SELECT `currabrev`,round(rate,`decimalplaces`) `rate` FROM `currencies` WHERE 1";
	         $CurrencyArr=array();
			 $result = DB_query($sql);
			while ($row = DB_fetch_array($result)) {
				$CurrencyArr[$row['currabrev']]=$row['rate'];
			}
		}*/
	
	if ($_POST['prdrange']==0){	//本月	
		$sql="SELECT	invno,
						invtype,
						tag,
						transno,
						period,
						invdate,
						amount,
						tax,
						currcode,
						toregisterno registerno,
						toaccount,
						toname,
						remark,
						flg
				FROM	invoicetrans
				WHERE  tag = " . $tag . " 
						AND period>=" . $firstprd. "  AND period<=" . $endprd. "";					 
		if ($_POST['InvFormat']!=-1){
			$sql.=" AND  invtype=" . $_POST['InvFormat'] ;
		}	
     
		$sql.="	ORDER BY invtype, tax/amount,invdate ";  
	  // echo  '-='.$sql;
	}elseif($_POST['prdrange']==12||$_POST['prdrange']==3){
		//年度季度查询
		$sql="SELECT invtype,  period, SUM(amount) amount, SUM(tax) tax 
		       FROM invoicetrans 
		        WHERE tag=" . $tag . " 
					AND flg=0  AND period>=" . $firstprd. " 
					AND period<=" . $endprd. "  
					GROUP BY invtype,period ORDER BY invtype,period";
	}
		$ErrMsg = _('The payments with the selected criteria could not be retrieved because');
		$result = DB_query($sql, $ErrMsg);
		$SQL="SELECT a.accountcode,
					a.accountname,
					currcode,
					c.regid,
					CASE WHEN b.custtype IS NULL  OR b.custtype='' THEN -1 ELSE b.custtype	END custtype
				FROM	chartmaster a
				LEFT JOIN custname_reg_sub c ON c.sub=a.accountcode
				LEFT JOIN customersexport b ON	a.accountcode = b.account
				WHERE	(accountcode LIKE '2202_%'
						OR accountcode LIKE '1122_%' 
						OR accountcode LIKE '1001_%'	)
					AND a.used >= -1 
					AND c.sub<>''
					AND a.tag = '".$_POST['UnitsTag']."'
					ORDER BY accountcode";

	$Result=DB_query($SQL);		
	$CustArr=array();
	$SuppArr=array();
	$CustRegArr=array();
	//读取科目及对应的regid
	while ($row=DB_fetch_array($Result)) {
		$CustRegArr[trim($row['accountcode'])]=array($row['accountname'],$row['currcode'],$row['custtype'],$row['regid']);

		if (substr(trim($row['accountcode']),0,4)=="1001"){
			$CustArr[trim($row['accountcode'])]=array($row['accountname'],$row['currcode'],$row['custtype'],$row['regid']);
			$SuppArr[trim($row['accountcode'])]=array($row['accountname'],$row['currcode'],$row['custtype'],$row['regid']);

		}elseif (substr(trim($row['accountcode']),0,4)=="1122"){
		//	$CSArr[trim($row['accountcode'])]=array($row['accountname'],$row['currcode'],$row['custtype'],1,$row['regid']);

			$CustArr[trim($row['accountcode'])]=array($row['accountname'],$row['currcode'],$row['custtype'],$row['regid']);
		}elseif (substr(trim($row['accountcode']),0,4)=="2202")	{
			//$CSArr[trim($row['accountcode'])]=array($row['accountname'],$row['currcode'],$row['custtype'],0,$row['regid']);
			$SuppArr[trim($row['accountcode'])]=array($row['accountname'],$row['currcode'],$row['custtype'],$row['regid']);
		}
	}		
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
	//$i=0;	
	//本月解析
if ($_POST['prdrange']==0){//本月

	$result = DB_query($sql, $ErrMsg);
	//读取交易对应账
	while($row=DB_fetch_array($result))	{
		if ($row['registerno']!=''&&strlen($row['registerno'])>5){
				
			$CustomData[$row['registerno']]=array('account'=>'','actname'=>'','toaccount'=>$row['toaccount'],'regid'=>0,"flag"=>3,'TypeCust'=>$row['invtype'],"custname"=>$row['toname']);
		} 
	}
	//var_dump($CustomData);
		//解析账号 户名应科目
	if (!isset($CustomeAct)){
	
			 /**flag  初始值3 有单位无科目0 系统中无此客户-2 转户客户9  无信用代码-1 //无此银行账号-3 */
		foreach($CustomData as $key=>$row){

			$toactsql='';
		    if ($row['toaccount']!='' &&　strlen($row['toaccount'])>5){
				
				$toactsql=" OR bankaccount = '".$row['toaccount']."' ";
			}
		
			if (!empty($row['custname'])){//!='' &&　strlen($row['custname'])>5){
				
				$toactsql=" OR custname  LIKE '".$row['custname']."' ";
			}
			$SQL="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` FROM `register_account_sub` WHERE registerno='".$key ."'" .$toactsql;
			//if( $key=='91371002706087777P')
			//echo '<br/>-='.($SQL);
			$Result = DB_query($SQL);	
				//if (strlen($row['sub'])<4){	if ($key=='91370282697184865A')	    echo '-='.($SQL);	}		
				$regid=0;
				$custrow=DB_num_rows($Result);
				if ($custrow==1){
					$ROW=DB_fetch_assoc($Result);
				    
					if ($ROW['sub']!=''){
						//echo '<br/>=',$ROW['sub'].'['.$ROW['custname'].']'.$ROW['regid'];
						$CustomData[$key]['account']=$ROW['sub'];
						$CustomData[$key]['actname']=$ROW['custname'];
						$CustomData[$key]['regid']=$ROW['regid'];
						if ($CustomData[$key]['flag']==3){
							if ($key==$ROW['registerno']){
								$CustomData[$key]['flag']=0;	
							}else{
								$CustomData[$key]['flag']=-1;	//无信用代码
							}		
						}	
						$CustomData[$key]['custname']=$ROW['custname'];					
						if (substr($ROW['sub'],0,4)==YSZK ||substr($ROW['sub'],0,4)==YFZK)
							$CustomData[$key]['TypeCust']=substr($ROW['sub'],0,1); //1客户 2供应商
						
					}else{
						//echo '<br/>==',$ROW['sub'].'['.$ROW['custname'].']'.$ROW['regid'];
						$CustomData[$key]['regid']=$ROW['regid'];
						$CustomData[$key]['custname']=$ROW['custname'];
					
						if ($CustomData[$key]['flag']==3){
							if ($key==$ROW['registerno'] ){
								$CustomData[$key]['flag']=-4;	//无科目
							}else{
								$CustomData[$key]['flag']=-1;	
							}	
						}	
					}
				}elseif($custrow>1){
					while($Row=DB_fetch_array($Result)){
					
						if ($Row['sub']!='' && empty($CustomData[$key]['account'])){
							$CustomData[$key]['account']=$Row['sub'];
							$CustomData[$key]['actname']=$Row['custname'];
							$CustomData[$key]['regid']=$Row['regid'];
						
							$CustomData[$key]['custname']=$Row['custname'];			
							if (substr($Row['sub'],0,4)==YSZK ||substr($Row['sub'],0,4)==YFZK)
							$CustomData[$key]['TypeCust']=substr($Row['sub'],0,1); //1客户 2供应商
						
						}
						if ($CustomData[$key]['flag']==3){
							
							if ($key==trim($Row['registerno'])){
								$CustomData[$key]['flag']=0;	
							}else{
								$CustomData[$key]['flag']=-1;	//无信用代码
							}		
						}	
					}
					
				}else{//系统中无此客户
					$CustomData[$key]['flag']=-2;
				}	
		}//end foreach
		   $CustomeAct=$CustomData;
		   unset($CustomData);	
	}	
      //var_dump($CustomeAct);
	 // foreach($CustomeAct  as $ky=> $row){    echo $ky .$row['custname'].'---]    '.$row['flag'].'==='.$row['registerno'].'<br/>';	 }	
		echo '<table cellpadding="1" class="selection" style="width: 950px;">
			<tr>
				<th style="width: 10px;">序号</th>	
				<th  style="width: 30px;">发票号</th>	
				<th style="width: 90px;">' . _('Date') . '</th>				
				<th style="width: 70px;">发票类别</th>
				<th style="width: 50px;">金额</th>
				<th style="width: 30px;">税金</th>				
				<th style="width: 20px;">税率</th>
				<th style="width: 10px;">借/贷</th>
				<th style="width: 300px;">客户名/科目编码</th>	
				<th style="width: 200px;">摘要</th>              
				<th style="width: 30px;">凭证号</th>			
				<th ></th>
			</tr>';	
		$k = 0; //row colour counter
		$RowIndex = 0;
		$rw=1;
		$TransType = 0;//科目解析标记 0 1科目解析成功 2凭证已完成 -1 -2没有科目新公司
		$TotalAmount=0;
		$TotalTax=0;
		$TaxRate=-1;
		$TotalTypeAmo=0;
		$TotalTypeTax=0;
		$amo_jx=0;
		$tax_jx=0;
		$tax_xx=0;
		$amo_xx=0;
		$InvType=-1;
		$TranNoGL=0;
		//$TransNoArr=array();	
		//---------添加清除对应科目和选择代码
		$SQL="SELECT currabrev, round(rate,decimalplaces) rate FROM currencies";
		$Result=DB_query($SQL);		
		$CurrRate=array();
		while ($row=DB_fetch_array($Result)) {	
			$CurrRate[$row['currabrev']]=$row['rate'];
		}	
	
		$TotalTypeAmo=0;
		$TotalTypeTax=0;
		DB_data_seek($result,0);
	
	while ($myrow=DB_fetch_array($result)) {	
		$TranMsg='';
		$msg='';
		$prdgl='';
		$subject='';
		$subname='';
		$AubAnalysis=0;	
		$RegID=0;			
		if ($myrow['transno']>0){//已经录入凭证并核对
			$TransType =2;
			$TranNoGL=$myrow['transno'];
			$TranMsg=GetTransContent($myrow['period'],$myrow['transno']);
		}else{
			// 析科目>>>>>>>>
			//$ChkTranArr=CheckTrans($myrow,$TransNoArr);	
				//2-没有凭证,解析科目					
				$TransType =0;		
				if (isset($CustomeAct[trim($myrow['registerno'])])&&$myrow['registerno']!=''){
					//解析出科目  客户科目
					$TransType =1;
					$CustAct=$CustomeAct[$myrow['registerno']]['account'];
					$CustActName=$CustomeAct[$myrow['registerno']]['custname'];
					if ($myrow['toname']==""){
						$CustName=$CustomeAct[$myrow['registerno']]['custname'];
					}else{
						$CustName=$myrow['toname'];
					}
					$RegID=	$CustomeAct[$myrow['registerno']]['regid'];
					$CurrCode=$CustomeAct[$myrow['registerno']]['currcode'];
				}else{
					//不能解析出科目					
					$TransType =-2;	
				}
				$TranNoGL=0;
				$chk="checked";
		
			$TaxAct=$SubjectTax[$myrow['invtype']][0];
			$TaxActName=$SubjectTax[$myrow['invtype']][1];		
			//应交税金
		
			/*	
			if(isset($GLTemplet[trim($myrow['registerno'])])&&$myrow['registerno']!=''){	
				//prnMsg('//按模板设定的    注册码对应特殊支出科    ');	没有使用						
				$ToActPrmArr=array("ToAct"=>$GLTemplet [$myrow['registerno']][0],"ToActName"=>$GLTemplet [$myrow['registerno']][1],"ActAmo"=>$myrow['amount'],"msg"=>$msg);
				$chk='  ';					
			}else{*/
				//有问题
				/*
				  if ($i==0){
				    $i=10;
					print_r($SubjectRule[$myrow['invtype']]);
				  }*/
				  /*
			if ($CustAct=='' ){
				$ToActPrm=[];
				foreach($SubjectRule[$myrow['invtype']] as $val){	
					if ($val['flag']==1){        
						$ToActPrm=array("ToAct"=>$val['account'],"ToActName"=>$val['actname'],"ActAmo"=>$myrow['amount'],"msg"=>$msg,"CurrCode"=>$myrow['currcode']);
						break;
					}	
				}
				var_dump($ToActPrm);
			}*/	
				//$ToActPrm==json_encode($ToActPrm,JSON_UNESCAPED_UNICODE);
		}				
		if (trim($myrow['registerno'])=='000000000000000'){//出口发票
			$InvCurrType=4;
			//echo  '696===4';
		}elseif(strlen($myrow['registerno'])<18){  //代开发票
			$InvCurrType=5;
		}else{   //正常发票
			$InvCurrType=6;
		}
		//摘要自动写入 缺少外币写入
		if ($myrow['invtype']==0){
				
			if ($InvCurrType==5){
				//代开发票
				$msg=$CustName.'`采购代开专票号;'.$myrow['invno'].";";
			}else{
				$msg=$CustName.'`采购专票号;'.$myrow['invno'].";";
			}
		}elseif ($myrow['invtype']==1){
			$msg=$CustName.'`销售发票号;'.$myrow['invno'].";";
		}elseif ($myrow['invtype']==3){
			$msg=$CustName.'`销售普票号;'.$myrow['invno'].";";
		}
		//按税率合计	
			if (( ($InvType!=(int)$myrow['invtype']&&(int)$TaxRate==(int)round(100*(float)$myrow['tax']/(float)$myrow['amount'],0) )||(int)		$TaxRate!=(int)round(100*(float)$myrow['tax']/(float)$myrow['amount'],0) )&& $TaxRate>=0 ) {
				echo'<tr>
						<th ></th>			
						<th  colspan="3" >' . $TaxRate . '%税率合计</th>
						<th >'.abs($TotalTypeAmo).'</th>
						<th >'.abs($TotalTypeTax).'</th>				
						<th colspan="8" ></th>				
					</tr>';		
						
					$TotalTypeAmo=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
					$TotalTypeTax=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);						
					$TaxRate=round(100*(float)$myrow['tax']/(float)$myrow['amount'],0);	
			
			}else{
				if ($TaxRate==-1){
					$TaxRate=round(100*(float)$myrow['tax']/(float)$myrow['amount'],0);		
				}
				$TotalTypeAmo+=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
				$TotalTypeTax+=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);
			}
			if ((int)$InvType!=(int)$myrow['invtype'] && $InvType!=-1){  //进项转换销项  
		
				echo'<tr>
						<th ></th>			
						<th  colspan="3" >' . $TaxRate . '%'. $InvName[$InvType].'合计</th>
						<th >'.abs($TotalAmount).'</th>
						<th >'.abs($TotalTax).'</th>				
						<th colspan="8" ></th>				
					</tr>';		
					if ($myrow['invtype']!=0){
						$amo_xx=$TotalAmount;
						$tax_xx=$TotalTax;
					}else{
						$amo_jx=$TotalAmount;
						$tax_jx=$TotalTax;
					}		
					$InvType=$myrow['invtype'];	   
					$TotalAmount=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
					$TotalTax=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);
					//$TaxRate=round(100*(float)$myrow['tax']/(float)$myrow['amount'],0);	
				
			}else{
				  if ($InvType==-1){
					$InvType=$myrow['invtype'];
				  }
					$TotalAmount+=($myrow['invtype']==0?-$myrow['amount']:$myrow['amount']);
					$TotalTax+=($myrow['invtype']==0?-$myrow['tax']:$myrow['tax']);
			}
		//按进销项销项合计
		//if ((int)$InvType!=(int)$myrow['invtype'] && $InvType!=-1){
			/*
			if ($myrow['invtype']!=0){
				$amo_xx=$TotalTypeAmo;
				$tax_xx=$TotalTypeTax;
			}
			$amo_jx=$TotalTypeAmo;
			$tax_jx=$TotalTypeTax;		
			$TotalTypeAmo=0;
			$TotalTypeTax=0;*/
			//	$rw=1;
		//	}		
		if ($TransType ==-2 &&$TranNoGL==0){
			echo '<tr style="background: #ecc;">'	;
		}elseif ($TransType ==-1){
			echo '<tr style="background: #acc;">'	;
		}else{
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
		}				
		echo'<td>'.$rw.'</td>
		    <td>'. $myrow['invno'].'</td>
			<td >'.$myrow['invdate'].'</td>
			<td >'.$InvName[$myrow['invtype']].'
				<input type="hidden" name="InvType'.$RowIndex.'" id="InvType'.$RowIndex.'"  value="' . $myrow['invtype'] . '" />
				<input type="hidden" name="invcurrtype'.$RowIndex.'" id="invcurrtype'.$RowIndex.'"  value="' . $InvCurrType . '" />
				<input type="hidden" name="Amount'.$RowIndex.'" id="Amount'.$RowIndex.'"  value="' . $myrow['amount'] . '"/>	
				<input type="hidden" name="RegID'.$RowIndex.'" id="RegID'.$RowIndex.'"  value="' . $RegID . '" />
			</td>
			<td class="number">'.locale_number_format($myrow['amount'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']).'</td>
			<td class="number">'.locale_number_format($myrow['tax'],$_SESSION['CompanyRecord'][$tag]['decimalplaces']).'</td>
			<td >'.($myrow['tax']!=0?round(100*$myrow['tax']/$myrow['amount'],0).'%':'0').'</td>
			<td>'.($myrow['invtype']==0?'贷':'借').'</td>
			<td>';
		
			//科目编码:币种:单位名:汇率:RegID
			if($myrow['transno']>0){
					echo '<p title="'.$myrow['registerno'].':'.$myrow['toname'].'">'.$myrow['toname'].'</p>';		

			}elseif (strlen($CustAct)>4 ){
				echo  $CustAct.":".$CustomeAct[$myrow['registerno']]['custname'];
			}else{
                 $ToActPrm="";
				if ($myrow['invtype']==3){
					//销普票外币
					echo'<input type="text" name="GLCodeName"   id="GLCodeName"   list="GLCode1" title="'.$myrow['registerno'].':'.$CustName.'"  maxlength="45" size="35"  autocomplete="off"    onChange="OnSelectAct(this, GLCode1.options,'.$RowIndex.','.$InvCurrType.','.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')" /> 
							<datalist id="GLCode1"> ';
						foreach($CustArr  as $key=>$row){
							if (substr($key,0,4)=='1001'||(substr(trim($key),0,4)=='1122')){//&& $row[2]==1 ) ||(substr(trim($key),0,4)=='1122' && $row[1]==$_SESSION['CompanyRecord']['currencydefault'])) {
								$custname=mb_substr($row[0],mb_strpos($row[0],'-')+1);
								//.htmlspecialchars(mb_substr($row[0],0,mb_strpos($row[0],'-')), ENT_QUOTES,'UTF-8', false) 
								echo '<option value="' . $key . ':'.$row[1] .':'.htmlspecialchars($custname, ENT_QUOTES,'UTF-8', false). ':'.$row[3] .':'.trim($CurrRate[$row[1]]) .' " label="" >';
							}
						} 
					echo'</datalist>';

					$TransType=-2;
				}elseif ($myrow['invtype']==1){
					//销售专票	//8.解析科目应收应付等
					$Result=DB_query($SQL);
					echo $CustName."<br>";
					echo'<select name="GLCodeName'.$RowIndex.'"  id="GLCodeName'.$RowIndex.'"size="1" style="width:120"   onChange="OnSelectAct(this, GLCodeName'.$RowIndex.'.options,'.$RowIndex.','.	$InvCurrType.','.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')"  >';
						//解析选择项
						foreach($SubjectRule[1] as $row){	
								$SF=1;//    款
							$sflag=false;
							if (1==$row['jd']){
									if(isset($_POST['GLCodeName'.$RowIndex]) AND$row['account'].'^'.$row['currcode'].'^'.$row['actname'].'^'.$row['acctype']==$_POST['GLCodeName'.$RowIndex]){
										echo '<option selected="selected" value="';
									} else {
										echo '<option value="';
									}
									echo $row['account'].'^'.$row['currcode'].'^'.$row['actname'].'^'.$row['acctype'].'">' .$row['account'].':'.$row['actname']  . '</option>';
									if (!isset($_POST['GLCodeName'.$RowIndex])){
										$_POST['GLCodeName'.$RowIndex]=$row['account'].'^'.$row['currcode'].'^'.$row['actname'].'^'.$row['acctype'];
									}
							}
						}//foreach 					
					echo'</select>';		
					$TransType=-2;
				}elseif ($myrow['invtype']==0 ){
					//进项解析客户科目>对应科目			
					echo $CustName."<br>";
					echo'<select name="GLCodeName'.$RowIndex.'"  id="GLCodeName'.$RowIndex.'"size="1" style="width:120"   onChange="OnSelectAct(this, GLCodeName'.$RowIndex.'.options,'.$RowIndex.','.$InvCurrType.','.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')"  >';
					
					foreach($SubjectRule[0] as $row){	
						//if (In_array(substr($row['account'],0,4),$actrule)){
						if (-1==$row['jd']){
							if(isset($_POST['GLCodeName'.$RowIndex]) AND $row['account'].'^'.$row['currcode'].'^'.$row['actname'].'^'.$row['acctype']==$_POST['GLCodeName'.$RowIndex]){
								echo '<option selected="selected" value="';
							} else {
								echo '<option value="';
							}
							echo  $row['account'].'^'.$row['currcode'].'^'.$row['actname'].'^'.$row['acctype'].'">' .$row['account'].':'.$row['actname']  . '</option>';
							if (!isset($_POST['GLCodeName'.$RowIndex])){
								$_POST['GLCodeName'.$RowIndex]= $row['account'].'^'.$row['currcode'].'^'.$row['actname'].'^'.$row['acctype'];
							}
							if ($ToActPrm==''){
								$ToActPrm=urlencode('{"CustAct":"'.$val['acount'].'","CustActName":"'.$val['actname'],'"CurrCode":"'.$row['currcode'].'"}');
							}
						}
					}//foreach 				
					echo'</select>';		
					$TransType=-2;						
				}
			}
			$SelectCustAct=urlencode('{"CustAct":"'.$CustAct.'","CurrCode":"'.$CurrCode.'","CurrRate":"'.$CurrRate.'","CustActName":"'.$CustActName.'"}');
		echo '<input type="hidden" name="SelectCustAct'.$RowIndex.'" id="SelectCustAct'.$RowIndex.'"   value="'.$SelectCustAct.'"   />';
		if($TransType ==-1||$TransType ==2){

			$chk='disabled="disabled"';
		}else{
			if($TransType ==-2){
				$chk=" ";
			}else{
				$chk='checked';
			}			
		}
		echo'<input type="hidden" name="InvCurr'.$RowIndex.'"  id="InvCurr'.$RowIndex.'"  value="' . $_POST['InvCurr'.$RowIndex] . '" />
		</td>';
			/*
			if ($TransType!=2){
				echo'<td >';	 
				$chk="checked";
			
				if(isset($GLTemplet[trim($myrow['registerno'])][0])&&$myrow['registerno']!=''){	
					//按模板设定的对应科目
					$ToAccount=$GLTemplet [$myrow['registerno']][0];
					$ToActName=$GLTemplet [$myrow['registerno']][1];
					//客户科目
					$ToSelectAct=urlencode('{"ToAct":"'.$ToAccount.'","ToActName":"'.$ToActName.'","ActAmo":"'.$myrow['amount'].'"}');
		
					echo'<input type="text" name="ToSelect'.$RowIndex.'"   id="ToSelect'.$RowIndex.'"  maxlength="20" size="15"  value="'.$ToAccount.'^'.$ToAccName.'^'.$myrow['amount'].' " readonly  />
						<p   title="" >'.$ToAccount.':'.$ToActName.'</p> ';
				}else{*/
			if ($myrow['transno']==0){
					//10  解析对应科目  营业收入  材料  费用科目
		
				$ToSelectAct='';
				echo'<td >';	 
					echo'<select name="ToSelect'.$RowIndex.'"   id="ToSelect'.$RowIndex.'"  size="1" style="width:120" OnChange="OnToSelect(this, ToSelect'.$RowIndex.'.options,'.$RowIndex.','.	$InvCurrType.','.	"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')"  >';	
					foreach($SubjectRule[$myrow['invtype']] as $val){	
						//$i++;
						if( (1==$val['jd']&& $myrow['invtype']==0)||(-1==$val['jd'] && ($myrow['invtype']==1|| $myrow['invtype']==3))){
							//对应科目选择			
			
							if(isset($_POST['ToSelect'.$RowIndex]) AND $val['account'].'^'.$val['actnme'].'^'.$row['currcode']==$_POST['ToSelect'.$RowIndex]){
								
								echo '<option selected="selected" value="';
							} else {
								echo '<option value="';
							}
							if (!isset($_POST['ToSelect'.$RowIndex])){
								$_POST['ToSelect'.$RowIndex]= $val['account'].'^'.$val['actname'].'^'.$row['currcode'];
							}
							if ($ToSelectAct==''){
								//可能未使用
								$ToSelectAct=urlencode('{"ToAct":"'.$val['acount'].'","ToActName":"'.$val['actname'],'"CurrCode":"'.$row['currcode'].'"}');
							}
							echo  $val['account'].'^'.$val['actname'].'^'.$row['currcode'].'">' .$val['account'].':'.$val['actname']  . '</option>';					
						}	
					}//foreach 
					echo'</select>';		
					$hidden="true" ;					
				if ($myrow['invtype']==3 && $InvCurrType==4  && CURR!=$CurrCode)	{
					$hidden=' ';
					$hidden="true" ;
					echo'<br><input type="text" name="AmountCurr'.$RowIndex.'" id="AmountCurr'.$RowIndex.'" hidden='. $hidden .' maxlength="25" size="20"  onChange="OnAmountCurr(this,'.$RowIndex.')"  value="' . $_POST['AmountCurr'.$RowIndex] . '"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  title="按系统汇率计算金额"  />';
				}else{	
				
					echo'<input type="hidden" name="AmountCurr'.$RowIndex.'" id="AmountCurr'.$RowIndex.'"   value="'.$_POST['AmountCurr'.$RowIndex].'"   />';
				}
				
				echo '</td>';
			}else{
				echo'<td ></td>';
			}
		
			$ToAP=explode("^",$_POST['GLCodeName'.$RowIndex]);
			$ToA=explode('^',$_POST['ToSelect'.$RowIndex]);
			$ToActPrm=array( "CustAct"=>$ToAP[0],"CustActName"=>$ToAP[2], "CurrCode"=>$ToAP[1],"ToAct"=>$ToA[0], "ToActName"=>$ToAP[1]);
			$Get_Url=array("RegisterNo"=>$myrow['registerno'],"InvNo"=>$myrow['invno'],"InvType"=>$myrow['invtype'],
							"Amount"=>$myrow['amount'],"Tax"=>$myrow['tax'],"TransNo"=>$TranNoGL,"CustAct"=>$CustAct,
							"CustActName"=>$CustActName,"TabDate"=>$myrow['invdate'],"ToName"=>$CustName,"tag"=>$myrow['tag'],
							"InvCurrType"=>$InvCurrType,"TransType"=>$TransType,"Period"=>$myrow['period'],
							"CurrCode"=>$myrow['currcode'],	"RegID"=>$RegID,"TaxAct"=>$TaxAct,
							"TaxActName"=>$TaxActName,"flag"=>$CustomeAct[$myrow['registerno']]['flag'],"ToPrm"=>$ToActPrm);	
			if($TransType==2||$TransType==-1){ 
				if ($myrow['period']==$_SESSION['period']){
					$URL_to_TransDetail = $RootPath . '/JournalEntry.php?No='.$TranNoGL.'&Tag='.$myrow['tag'].'&Edit=Yes';
				}else{
					$URL_to_TransDetail = $RootPath . '/PDFTrans.php?JournalNo='.$myrow['period'].'^'.$TranNoGL;
		
				}
				$URL_CrtJournal ='';
			}else{
				//$URL_to_TransDetail=$RootPath . '/LookupJournal.php?InvPrm='.urlencode(json_encode($Get_Url,JSON_UNESCAPED_UNICODE));
				$URL_CrtJournal = $RootPath . '/GLInvCreate.php?InvPrm='.urlencode(json_encode($Get_Url,JSON_UNESCAPED_UNICODE));
				//.'&ToActPrm=""'
				//$URL _CrtJournal = $RootPath . '/GLInvCreate.php?InvPrm='.urlencode($regstr).'&ToActPrm='.urlencode('{'.$ToActPrm.',"msg":"'.$msg.'"}');
			}
			//echo'<input type="hidden" name="ToSelectAct'.$RowIndex.'" id="ToSelectAct'.$RowIndex.'" value="' . $ToSelectAct . '" />';
		/*
		echo'<td>';							
		//11摘要
	
	     	 <input type="hidden" name="rmk'.$RowIndex.'" id="rmk'.$RowIndex.'"   value="'.$msg.'" />';
		if($TransType !=2){
			echo'<input type="text" name="remark'.$RowIndex.'" id="remark'.$RowIndex.'" title="'.$msg.'" maxlength="20" size="15"  onchange="OnRemark(this ,'.$RowIndex.')"    value="'.$_POST['remark'.$RowIndex].'"  placeholder="你输入的字      ,      统自动合并到系统自动产生的备注!"  />';
		}
		echo '</td>';*/
		//12凭证链接
		if ($myrow['transno']==0){
			if ($RegID>0){
				$CustStr="客户编码".$RegID;
				
			}else{
				$CustStr="新客户无编码";
				//echo '<td  title="税号['.$myrow['registerno'].']'.$CustName.$CustStr.'"  >异常</td>';
			}
			echo'<td ><a href="'. $URL_CrtJournal.'"  id="href'.$RowIndex.'" name="href'.$RowIndex.'" title="税号['.$myrow['registerno'].']'.$CustName.$CustStr.'"  >生成</a></td>	';
		    //
		}else{
			echo'<td >
				<a href="'.$URL_to_TransDetail.'" title="'.$TranMsg.'" target="_blank" >'.$TranNoGL.'</a>
				<input type="hidden" name="PeriodTransNo'.$RowIndex.'" value="' .$ChkTranArr[0]['periodno'].'^'.$ChkTranArr[0]['transno']. '" /></td>';			
		}
		//13操作选择
		echo'<td >
				<input type="hidden" name="invno'.$RowIndex.'" value="' . $myrow['invno'] . '" />									
				<input type="hidden" name="JournalType'.$RowIndex.'" id="JournalType'.$RowIndex.'"  value="' . $TransType . '" />
				<input type="hidden" name="GetUrl'.$RowIndex.'" value="' . urlencode(json_encode($Get_Url,JSON_UNESCAPED_UNICODE)) . '" />';
			//	<input type="hidden" name="RegStr'.$RowIndex.'" value="' . $regstr . '" />
		if ($myrow['invtype']==3  && $myrow['toregisterno']=='000000000000000')	{
			echo'<input type="checkbox" name="chkbx'.$RowIndex.'" id="chkbx'.$RowIndex.'"  value="1"  onchange="OnChkbx(this ,'.$RowIndex.')"  ></td>	';

		}else{
			echo'<input type="checkbox" name="chkbx'.$RowIndex.'"  id="chkbx'.$RowIndex.'" value="1"  '.(($myrow['transno']>0||$TransType==-2)?'disabled="disabled"':($TransType ==1?"checked":" ")).'  onchange="OnChkbx(this ,'.$RowIndex.')" ></td>	';
		}										
		echo'</tr>';
		//$TaxRate=round(100*(float)$myrow['tax']/(float)$myrow['amount'],0);					
		//$TotalTypeAmo+=$myrow['amount'];
		//$TotalTypeTax+=$myrow['tax'];				
		//$InvType=(int)$myrow['invtype'];				
		$RowIndex++;
		$rw++;
	}//while
	echo'<tr>
			<th ></th>			
			<th  colspan="3" >' . $InvName[$InvType] . '合计</th>
			<th >'.abs($TotalTypeAmo).'</th>
			<th >'.abs($TotalTypeTax).'</th>				
			<th colspan="8" ></th>				
	     </tr>';	
	echo'<tr>
			<th ></th>			
			<th  colspan="3" >' . $TaxRate . '销项累计</th>
			<th >'.abs($TotalTypeAmo+$amo_xx).'</th>
			<th >'.abs($TotalTypeTax+$tax_xx).'</th>				
			<th colspan="8" ></th>				
		</tr>';
	echo'<tr>
			<th ></th>			
			<th  colspan="1" >销项税减进项税额</th>
			<th >'.($TotalTypeTax+$tax_xx+$tax_jx).'</th>
			<th  colspan="2" >增值税税负</th>';
		if ($TotalTypeTax+$tax_xx+$tax_jx>0){
			echo'<th >'.round(($TotalTypeTax+$tax_xx+$tax_jx)/($TotalTypeAmo+$amo_xx)*100,0).'%</th>		';		
		}else{
			echo'<th></th>';
		}
	echo'<th colspan="8" ></th>				
			</tr>';		
	echo '</table>';
       //当月结束
}elseif ($_POST['prdrange']==3){
		//季度合      
	echo '<table cellpadding="2" class="selection">';
	$invtyp=-1;	
	$j=0;
	$TotalAmount=0;	
	$TotalTax=0;	
	$TotalAmountJ=0;
	$TotalTaxJ=0;
	$r=0;
	$TotalTypeAmo=0;
	$TotalTypeTax=0;
	$TotalTypeAmoJ=0;
	$TotalTypeTaxJ=0;
	while ($myrow=DB_fetch_array($result)) {
		if ($invtyp!=-1&&$invtyp!=$myrow['invtype']){
			if ($invtyp==0){
				echo'<th ></th>			
							<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
							<th >'.locale_number_format($TotalTypeAmo,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
							<th >'.locale_number_format($TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>			
							<th >'.locale_number_format($TotalTypeAmo+$TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
						</tr>';			
			}
			if ($invtyp!=0){
			
				echo'<th ></th>			
							<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
							<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
							<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
							<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
						</tr>';		
						$tax_xx=$TotalAmount;
						$amo_xx=$TotalTax;			
		}
		$r=0;		
			echo'<tr>
			<th >序号</th>			
			<th >月份</th>	
			<th >摘要</th>   
			<th >借/贷</th>
			<th >金额</th>
			<th >税金</th>					
			<th >合计</th>	
		</tr>';
		}
		//$ymstr=substr($_SESSION['lastdate'],0,5).($_SESSION['period']-substr($_SESSION['lastdate'],5,2)+$myrow['period']);
		if ($myrow['invtype']==0){//进项合计
			
			$TotalTypeAmo+=$myrow['amount'];
			$TotalTypeTax+=$myrow['tax'];

			$TotalTypeAmoJ+=$myrow['amount'];
			$TotalTypeTaxJ+=$myrow['tax'];
	}else{	//销项合计
		$TotalAmount+=$myrow['amount'];
		$TotalTax+=$myrow['tax'];

		$TotalAmountJ+=$myrow['amount'];
		$TotalTaxJ+=$myrow['tax'];
	}
		if ((($myrow['period']-$_SESSION['janr']==2)||($myrow['period']-$_SESSION['janr']==5)||($myrow['period']-$_SESSION['janr']==8)||($myrow['period']-$_SESSION['janr']==11))&&($myrow['invtype']==1||$myrow['invtype']==3)){
		
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			printf('<td>%s</td>					
					<td>%s</td>
					<td >%s</td>
					<td >%s</td>					
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>					
				</tr>',
			$r+1,	
			(round(($myrow['period']-$_SESSION['janr']+1)/3,2)).'季度',
			'销项票本月合计',
			'贷',
			locale_number_format($TotalAmountJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
			locale_number_format($TotalTaxJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
			locale_number_format(($TotalAmountJ+$TotalTaxJ),$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']));	
			$TotalAmountJ=0;
			$TotalTaxJ=0;
			$r++;
		}
		if ((($myrow['period']-$_SESSION['janr']==2)||($myrow['period']-$_SESSION['janr']==5)||($myrow['period']-$_SESSION['janr']==8)||($myrow['period']-$_SESSION['janr']==11))&&$myrow['invtype']==0){
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf('<td>%s</td>					
				<td>%s</td>
				<td >%s</td>
				<td >%s</td>				
				<td class="number">%s</td>
				<td class="number">%s</td>
				<td class="number">%s</td>					
			</tr>',
		$r+1,	
		round(($myrow['period']-$_SESSION['janr']+1)/3).'季度',
		'进项票本月合计',
		'借',
		locale_number_format($TotalTypeAmoJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
		locale_number_format($TotalTypeTaxJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
		locale_number_format($TotalTypeAmoJ+$TotalTypeTaxJ,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']));	
		$TotalTypeAmoJ=0;
		$TotalTypeTaxJ=0;
		$r++;
		}
		$invtyp=$myrow['invtype'];
		$j++;
	}
		echo'<tr>
		<th ></th>			
		<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
		<th >'.locale_number_format($amo_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
		<th >'.locale_number_format($tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
		<th >'.locale_number_format($amo_xx+$tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
	</tr>';	
		echo'<tr>
		<th  ></th>			
		<th  colspan="3" >销项累计</th>
		<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
		<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
		<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>			
		</tr>';
	
	echo '</table>';
}elseif ($_POST['prdrange']==12){
	//年度    询
	echo '<table cellpadding="2" class="selection">';

		$invtyp=-1;
		while ($myrow=DB_fetch_array($result)) {
			if ($invtyp!=-1&&$invtyp!=$myrow['invtype']){
			
				if ($invtyp==0){
					$r=0;
					echo'<th ></th>			
						<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
						<th >'.locale_number_format($TotalTypeAmo,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
						<th >'.locale_number_format($TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>		
						<th >'.locale_number_format($TotalTypeAmo+$TotalTypeTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
					</tr>';			
				}
				if ($invtyp!=0){				
					echo'<th ></th>			
						<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
						<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
						<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
						<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
					</tr>';	
					$tax_xx=$TotalAmount;
					$amo_xx=$TotalTax;		
				}			
				echo'<tr>
				<th >序号</th>			
				<th >月份</th>	
				<th >摘要</th>   
				<th >借/贷</th>
				<th >金额</th>
				<th >税金</th>					
				<th >合计</th>	
			</tr>';
			}
			$ymstr=substr($_SESSION['lastdate'],0,5).($myrow['period']-$_SESSION['period']+substr($_SESSION['lastdate'],5,2));
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			printf('<td>%s</td>					
					<td>%s</td>
					<td >%s</td>
					<td >%s</td>					
					<td class="number">%s</td>
					<td class="number">%s</td>
					<td class="number">%s</td>					
				</tr>',
			$r+1,						
			$ymstr,
			($myrow['invtype']==0?'进项票':'销项票').'本月合计',
			($myrow['invtype']==0?'贷':'借'),
			locale_number_format($myrow['amount'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
			locale_number_format($myrow['tax'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']),
			locale_number_format($myrow['tax']+$myrow['amount'],$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']));	
			if ($myrow['invtype']!=0){	
				$TotalAmount+=$myrow['amount'];
				$TotalTax+=$myrow['tax'];
			}else{
				$TotalTypeAmo+=$myrow['amount'];
				$TotalTypeTax+=$myrow['tax'];
				$tax_xx+=$myrow['amount'];
				$amo_xx+=$myrow['tax'];		
			}
			
			$r++;
			$invtyp=$myrow['invtype'];
	
		}
		echo'<tr>
		<th ></th>			
		<th  colspan="3" >'. $InvName[$invtyp] .'合计</th>
		<th >'.locale_number_format($amo_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
		<th >'.locale_number_format($tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
		<th >'.locale_number_format($amo_xx+$tax_xx,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
	</tr>';	
			echo'<tr>
				<th ></th>			
				<th  colspan="3" >'. $InvName[$invtyp] .'累计</th>
				<th >'.locale_number_format($TotalAmount,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>
				<th >'.locale_number_format($TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
				<th >'.locale_number_format($TotalAmount+$TotalTax,$_SESSION['CompanyRecord'][$_SESSION['Tag']]['decimalplaces']).'</th>				
			</tr>';	    
	echo '</table>';

}
	//下面为生成科目和凭证
if(isset($_POST['TransSave'])){
		$rw=0;
		foreach ($_POST as $FormName =>$val) {
	
			if (mb_substr($FormName, 0, 5)=='chkbx') { //
				$n=mb_substr($FormName, 5);	
				
				if (strlen(trim($_POST['JournalType'.$n]))==-1&&$_POST['PeriodTransNo'.$n]!=""){
					//凭已经手工制作
					$result=DB_Txn_Begin(); 
					$sql="UPDATE invoicetrans SET transno=".explode('^',$_POST['PeriodTransNo'.$n])[1]." WHERE  invno=".$_POST['invno'.$n];
					$result = DB_query($sql);
					$sql="UPDATE gltrans SET posted=1 WHERE  transno=".explode('^',$_POST['PeriodTransNo'.$n])[1]." AND periodno=".explode('^',$_POST['PeriodTransNo'.$n])[0];
					$result = DB_query($sql);
					$result=DB_Txn_Commit();
					
				}else{
					//凭证自动生成   ---发票信息
					/*手动选择对应科目营业收入没有全部验证OK*/	
					//echo	'JournalType'.$n.'='.$_POST['JournalType'.$n]."<br>";
					//传递交易数据
					$row=json_decode(urldecode($_POST['GetUrl'.$n]),true);
                   // var_dump($row);
   				    $tag=$row['tag'];
					if((int)$_POST['JournalType'.$n]==1){
							$SelectCustAct=json_decode(str_replace('\\','',urldecode($_POST['SelectCustAct'.$n])),true);//选择单      
							//'{"ToAct":"'.$ToAccount.'","ToActName":"'.$ToActName.'","ActAmo":"'.$myrow['amount'].'"}');
							
							//该参数是在js代码中赋值,可能未使用
							$ToAct=json_decode(str_replace('\\','',urldecode($_POST['ToSelectAct'.$n])),true);//凭证对应科目
							$rw++;	
						$ToAccount=$row['ToPrm']['ToAct'];//ToAct['ToAct'];					
							//读取销项进项的科目	
						$TaxAccount=$row['TaxAct'];
						$InvType=$row['InvType'];
						if ($row['InvType']==0){
							$TransType=20;//采购发票
						}else{
							$TransType=10;//销售发票
						}
								
						$TabDate = DateExistsPeriod ($row['TabDate'],$row['Period']);
						//$msg=$_POST['rmk'.$n].$_POST['remark'.$n];
						$TransNo = GetTransNo($row['Period'], $db);							
						$TagTypeNo = GetTagTypeNo($tag,$row['Period'], $db);
						
						if (isset($SelectCustAct['CustRegID'])){  //
							$RegID=$SelectCustAct['CustRegID'];
						}else{
							$CustAct=$row['CustAct'];//??
							$CustActName=$row['CustActName'];//??
						}		
						if (isset($SelectCustAct['CustAct'])){  //币种
							$CustAct=$SelectCustAct['CustAct'];
							$CustActName=$SelectCustAct['CustActName'];
						}else{
							$CustAct=$row['CustAct'];
							$CustActName=$row['CustActName'];
						}			
						
                        if (empty($SelectCustAct['CurrCode'])){  //币种
						
							$CurrCode=$row['CurrCode'];
						}else{
							$CurrCode=$SelectCustAct['CurrCode'];
						
						}		
						//echo'<br>-='.$CurrCode.'$row[CurrCode]'.$row['CurrCode'].'SelectCustAct'.$SelectCustAct['CurrCode'];			
						$inst=0;
						$GLType=$InvType;
						if ($row['InvType']==0){//进项销项发票凭证生成					
						   
							$Amount=$row['Amount'];
							$TaxAmo=$row['Tax'];
							$TotalAmo=-($row['Amount']+$row['Tax']);
						}elseif ($row['InvType']==1||$row['InvType']==3){
							$Amount=-$row['Amount'];
							$TaxAmo=-$row['Tax'];
							$TotalAmo=($row['Amount']+$row['Tax']);
							if ($val[11]==4){//外币金额
								//$ExAmount=$_POST['InvCurr'+$n];
								if (isset($SelectCustAct['CurrAmo'])){  //币种
									$ExAmount=$SelectCustAct['CurrAmo'];
								}else{
									$ExAmount=$_POST['$ExAmount'.$n];
								}	
							}
						}
						$Narrative="";
						if ($CurrCode==CURR){
							if ($InvType==0){
								if (substr($ToAct,0,4)=='1403'){
									$Narrative.="购材料";
								}else{
									$Narrative.=$row['toname']."支出";
								}
								$Narrative.=" 专票号:".$row['InvNo'];
							}elseif($InvType==1||$InvType==3){
								if($InvType==1){
									$Narrative.="收入 专票号:".$row['InvNo'];
								}else{
					
									$Narrative.="收入 普票号:".$row['InvNo'];
								}
							}
							$Narrative.=substr($CustActName,strpos($CustActName,'-'));
						}else{
						    //外币
							if ($InvType==0){
								if (substr($ToAct,0,4)=='1403'){
									$Narrative.="购材料[".$row['CurrCode'].$row['Amount'].']';
								}else{
									$Narrative.=$row['toname']."支出[".$row['CurrCode'].$Amount.']';
								}
								$Narrative.=" 专票号:".$row['InvNo'];
							}elseif($InvType==1||$InvType==3){
							
									$Narrative.="收入[".$row['CurrCode'].$Amount.']';
									if($InvType==1){
										$Narrative.="专票号:".$row['InvNo'];
									}else{
					
										$Narrative.="普票号:".$row['InvNo'];
									}
							}
							$Narrative.=substr($CustActName,strpos($CustActName,'-'));
						}	
						$result = DB_Txn_Begin();
							$SQL = "INSERT INTO gltrans (type,
														typeno,
														transno,
														trandate,
														periodno,
														account,
														narrative,
														amount,
														flg,
														tag,
														chequeno,
														userid,
														posted)
												VALUES ('".$TransType."',
														'" . $TagTypeNo . "',
														'" . $TransNo . "',
														'" . FormatDateForSQL($TabDate) . "',
														'" . $row['Period']. "',
														'" . $CustAct . "',
														'".$Narrative."', 
														'" . $TotalAmo ."',
														'1',
														" . $tag . ",
														'1',
														'auto',
														'1')";			
								$result = DB_query($SQL);
								 //prnMsg($SQL);
						
								if ($result){
									$inst++;
								}		 
								$SQL = "INSERT INTO gltrans (type,
															typeno,
															transno,
															trandate,
															periodno,
															account,
															narrative,
															amount,
															flg,
															tag,
															chequeno,
															userid,
															posted)
														
												VALUES ('".$TransType."',
													'" . $TagTypeNo . "',
														'" . $TransNo . "',
														'" . FormatDateForSQL($TabDate) . "',
														'" . $row['Period']. "',
														'" . $ToAccount . "',
														'".$Narrative."', 
														'" . $Amount ."',
														'1',
														" . $tag . ",
														'1',
														'auto',
														'0')";	
								//echo'-='.($SQL);
								$result = DB_query($SQL);
						
								if ($result){
									$inst++;
								}	
								if ( $TaxAmo<>0){	//零税率发票 
								$SQL = "INSERT INTO gltrans (type,
															typeno,
															transno,
															trandate,
															periodno,
															account,
															narrative,
															amount,
															flg,
															tag,
															chequeno,
															userid,
															posted)
														
												VALUES ('".$TransType."',
													'" . $TagTypeNo . "',
														'" . $TransNo . "',
														'" . FormatDateForSQL($TabDate) . "',
														'" . $row['Period']. "',
														'" . $TaxAccount . "',
														'".$Narrative."', 
														'" . $TaxAmo ."',
														'1',
														" . $tag . ",
														'1',
														'auto',
														'0')";	
									//		prnMsg($SQL);
							$result = DB_query($SQL);
				     
							if ($result){
									$inst++;
								}
							}
							if ( $CurrCode!=CURR ){
							
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
													VALUES (	'" . FormatDateForSQL($TabDate) . "',
															'" . $TransNo . "',
															'" . $row['Period']. "',
															'" . $CustAct . "',
															'" . abs(round($ExAmount/$Amount ,4)) . "',
															'" . $Amount . "',
															'" . $ExAmount . "',
															'0',
															'" . $CurrCode . "',
															'1' )";
										$ErrMsg = _('Cannot insert a  transaction because');
										$DbgMsg = _('Cannot insert a  transaction with the SQL');
										$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
								
							}
								//代码为应收账款、应付账款录入
								$rate=1;
								if  (substr($CustAct,0,4)=='1122'){ 
									
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
																		'" . $GLType . "',
																		'" . $RegID . "',
																	
																		'" . FormatDateForSQL($TabDate) . "',
																		'" . FormatDateForSQL($TabDate) . "',
																		'" . $row['Period'] . "',
																		'0', '0', '0',  '0', 
																		'" . $rate  . "',
																		'" . $TotalAmo . "',
																		'0',  '0',  '0',  '0',  '0',  '','0','0','0','1',''	) ";
										$result = DB_query($sql);
								    // prnMsg($sql);									
								}elseif  (substr($CustAct,0,4)=='2202'){ 
									
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
																	'" . $GLType . "',
																	'" . $row['Period'] . "',
																	'" . $RegID . "',
																	'',
																	'" . FormatDateForSQL($TabDate) . "',
																	'" . FormatDateForSQL($TabDate) . "',
																	'0', 
																	'" . $rate . "',
																	'" . $TotalAmo . "',
																	'0',  '0',  '0', '') ";
										$result = DB_query($sql);
									//prnMsg($sql);	
									
								}	
							$SQL="UPDATE  invoicetrans SET   transno ='".$TransNo."' ,regid='".$RegID."' WHERE invno='".$row['InvNo']."' AND transno=0";
						   	$result=DB_query($SQL);	
				           // prnMsg($inst.$SQL);
							if ($result && ($inst>=3||($inst>=2&& $TaxAmo==0))){							
								$result = DB_Txn_Commit();
								//prnMsg('1643ok');
							}else{
								$TransNo='';	
								//prnMsg('1646no');					
								$result = DB_Txn_Rollback();		
							}		
					}
				}		//$_POST['JournalType'.$n]
			
			}//endif
			$_POST['rmk'.$n]='';
			$_POST['remark'.$n]='';
			$_POST['AmountCurr'.$n]='';
		}//foreach
		echo '<meta http-equiv="refresh" content="0.1"/>';
		//echo '<meta http-equiv="Refresh" content="3"; url=' . $RootPath . '/InvoiceJournallize.php">';	
	}	
}//endif 293

echo ' </form>';
echo'<script type="text/javascript">

</script>';
include ('includes/footer.php');

/**
   *读取会计凭证，转换为符串
   * @param array $period
   *              $transno
   *    *    *
   * @return String
   * @throws Exception
   * 错误返-1
   */
  function  GetTransContent($period,$transno){

	$SQL="SELECT transno, 
				 trandate,
				 account,
				 accountname,
				 narrative,
				 amount,
				 flg,
				 posted
			FROM gltrans
			LEFT JOIN chartmaster ON gltrans.account=chartmaster.accountcode
			WHERE periodno=".$period." 
			  AND transno=".$transno;
	
	$Result=DB_query($SQL);
	$Header='会计凭证';
	$narr='';
	$TransDate='';	
	$ToType=2;
	$tranmsg='';
	$mlen=0;

	while($Row=DB_fetch_array($Result)){
	
		
		if($Row['flg']==1){//数据为正
			if($Row['amount']>0){
				$jdstr="借 ".$Row['amount'];
			}else{
				$jdstr="贷 ".(-$Row['amount']);
			}
		}else{
			if($Row['amount']>0){
				$jdstr="贷 ".(-$Row['amount']);
			}else{
				$jdstr="借 ".$Row['amount']; 
			}
		}
		if ($narr==''){		
			$TransDate=$Row['trandate'];
			$narr=$Row['narrative'];
		}
		if (strlen($Row['account'].$Row['accountname'])>$mlen){
             $mlen=strlen($Row['account'].$Row['accountname']);
		}
		$TransRow[]=$Row['account'].'&nbsp;'.$Row['accountname'];
		if (strlen($jdstr)>$nlen){
			$nlen=strlen($jdstr);
		}
		$TransAmo[]=$jdstr;
	}

	$TranCont= $Header.$TransDate.'&nbsp;记字'.$transno.'&#10;';
	$nbsp='';
	foreach($TransRow as $key=>$val){
		$len=$mlen-strlen($val)+$nlen-strlen($TransAmo[$key])+12;
		for($i=0;$i<$len;$i++){
			$nbsp.='&nbsp;';
		}
		$TranCont.=$val.$nbsp.'&nbsp;'.$TransAmo[$key].'&#10;';
		$nbsp='';
	}
	 $TranCont.='摘&nbsp;&nbsp;要:'.$narr.'"';
     return  $TranCont;
    
}
function CheckTrans($row,$transnoarr){
	/*$row [invno,	invtype,transno,period,	invdate,	amount,	tax,
	toregisterno,	A.toaccount,	  custname,	toname,	B.account ,	C.accountname,	A.remark,	A.flg*/
	//根据交易和已经制作的会计凭证比对 /.查询多行按最近日期 
	$transarr=array();
	$BankDate = ConvertSQLDate($row['invdate']);
	$transnostr='';
	if (count($transnoarr)>0){
		$transnostr=implode(',', $transnoarr);
	}
	if ($row['invtype']==1){//销
		$sql="SELECT  transno,COUNT(trandate) FROM gltrans WHERE periodno=".$row['period']." AND trandate>='".$BankDate."' AND  amount=".($row['amount']+$row['tax'])."  AND account='".$row['account']."' AND posted=0  ";
	}else{//进

		$sql="SELECT  transno,COUNT(trandate) tranrow FROM gltrans WHERE periodno=".$row['period']." AND trandate>='".$BankDate."' AND  amount=-".($row['amount']+$row['tax'])."  AND account='".$row['account']."' AND posted=0 ";

	}
	if($transnostr!=''){
		$sql.='AND transno  NOT IN ('.$transnostr.')';
	}
	$sql.='  GROUP BY transno';
	$result=DB_query($sql);
	$trnno=0;

	if (DB_num_rows($result)>=1){
		$tranrow=DB_fetch_row($result);
		
		$sql="SELECT transno, 
		             trandate,periodno, account,accountname, narrative, amount, a.tag,               flg 
				FROM gltrans a LEFT JOIN chartmaster b on a.account=b.accountcode WHERE periodno='".$row['period']."' AND transno='".$tranrow[0]."' AND posted=0  ORDER BY trandate";
		$result=DB_query($sql);	
		$transarr=array();
		while ($myrow=DB_fetch_array($result)){
			array_push($transarr,$myrow);
		}		
	}else{
		$transarr=0;
	}/*
	while($rows=DB_fetch_array($result)){
		if ($rows['tranrow']==2){
			$trnno=$rows['transno'];
			break;
		}
	}
	*/
	return $transarr;
}
function AddJournalInvoice(&$valstr,&$subpub,$dt){
	//没有使用
	/*$row=$myrow['toregisterno'].'^'.$myrow['invno'].'^'.$myrow['invtype'].'^'.$myrow['amount'].'^'.$myrow['tax'].'^'.$myrow['invdate'].'^'.$CustAct.'^'.$CustActName.'^'.$TabDate.'^'.$myrow['custname'].'^'.$myrow['tag'].'^'.$subject.'^'.$TransType.'^'.$prd;
	0注    号 发票号码1类别2 ,金额3，税4，凭证号5，科目6,科目名7,发      日期8  9客      名 分组10 期间13 */
		
	 $row=explode('^',$valstr);
	
	//进项销项发票凭证生成
	
	//  计      对应期间     9-(2017-2018)*12+(1-9)
	$CustAct=$row['CustAct'];
	//$prd=$row['Period'];
	$TransNo = GetTransNo($row['Period'], $db);
	
	//读取销项进项的科目	
	$taxacc=explode(',',$subpub[$row['InvType']])[1];
	$toacc=explode(',',$subpub[$row['InvType']])[0];
	//$dt=$_SESSION['lastdate'];
		$m=$row['Period']-$_SESSION['period'];
		$me=date("Y-m-d",strtotime("$dt +$m month -1 day"));
		$ms=date("Y-m-01",strtotime("$dt +$m month"));
		if(strtotime($row['TabDate'])>strtotime($me)){
			$TabDate = $me;
			$pd=2;
		}elseif(strtotime($row['TabDate'])<strtotime($ms)){
			$TabDate = $ms;
			$pd=1;
		}else{
			$TabDate = $row['TabDate'];
			$pd=0;
		}

		if ($row['InvType']==2){		
			$InvType=20;
			$TransType=20;//采购发票
			$GLType=20;
			$jd=-1;
			if($pd>=1){
				$msg='日期:'.$row['TabDate'].'采购转账['.$row['InvNo'].']'.$val[9];
			}else{
				$msg='采购转账['.$row['InvNo'].']'.$val[9];
			}
		}else{	
			$GLType=10;
			$InvType=10;
			$TransType=10;//销售发票
			$jd=1;
			$msg='销售转账['.$row['InvNo'].']'.$val[7];
		
		}
		$TagTypeNo = GetTagTypeNo($tag,$row['Period'], $db);	
		//，插入凭证	
		DB_Txn_Begin();
		$inst=0;
		$amo=-($row['Amount']*$jd);
		$tax=-($row['Tax']*$jd);
		$total=($row['Amount']+$row['Tax'])*$jd;
		
		$SQL = "INSERT INTO gltrans (type,
										typeno,
										transno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										flg,
										tag,
										chequeno,
										userid,
										posted)
							VALUES ('".$TransType."',
								'" . $TagTypeNo . "',
									'" . $TransNo . "',
									'" . FormatDateForSQL($TabDate) . "',
									'" . $row['Period']. "',
									'" . $CustAct . "',
									'".$msg."', 
									'" . $total ."',
									'1',
									" . $tag . ",
									'1',
									'auto',
									'1')";			
			$result = DB_query($SQL);
			if ($result){
				$inst++;
			}		 
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										transno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										flg,
										tag,
										chequeno,
										userid,
										posted)
									
							VALUES ('".$TransType."',
								'" . $TagTypeNo . "',
									'" . $TransNo . "',
									'" . FormatDateForSQL($TabDate) . "',
									'" . $row['Period']. "',
									'" . $toacc . "',
									'".$msg."', 
									'" . $amo ."',
									'1',
									" . $tag . ",
									'1',
									'auto',
									'0')";	
			$result = DB_query($SQL);
			if ($result){
				$inst++;
			}	
			if ( $tax<>0){	//零税率发票 
			$SQL = "INSERT INTO gltrans (type,
										typeno,
										transno,
										trandate,
										periodno,
										account,
										narrative,
										amount,
										flg,
										tag,
										chequeno,
										userid,
										posted)
									
							VALUES ('".$TransType."',
								'" . $TagTypeNo . "',
									'" . $TransNo . "',
									'" . FormatDateForSQL($TabDate) . "',
									'" . $row['Period']. "',
									'" . $taxacc . "',
									'".$msg."', 
									'" . $tax ."',
									'1',
									" . $tag . ",
									'1',
									'auto',
									'0')";	
		$result = DB_query($SQL);	
		if ($result){
				$inst++;
			}
		}
			//下面代码为应收账款、应付账款录入
			$rate=1;
			if  (substr($CustAct,0,4)=='1122'){ 
				$sql="SELECT  unitscode, branchcode FROM accountunits WHERE account='" . $CustAct . "' ";
				$result = DB_query($sql);
				if (DB_num_rows($result)==1){
				$row=DB_fetch_row($result);
				
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
													'" . $GLType . "',
													'" . $row[0] . "',
													'" . $row[1] . "',
													'" . FormatDateForSQL($TabDate) . "',
													'" . FormatDateForSQL($TabDate) . "',
													'" . $row['Period'] . "',
													'0', '0', '0',  '0', 
													'" . $rate  . "',
													'" . $total . "',
													'0',  '0',  '0',  '0',  '0',  '','0','0','0','1',''	) ";
					$result = DB_query($sql);

				}else{
					$msgerr=',应收账款对应的客户异常!';
				}
			
				//SELECT gltrans.transno, gltrans.type,accountunits.unitscode debtorno,accountunits.branchcode
				// branchcode, gltrans.trandate,gltrans.trandate inputdate, periodno prd, 0settled,0 reference,0 tpe, 0order_,0 rate,
				//amount ovamount,0 ovgst,0 ovfreight, 0ovdiscount,0 diffonexch,0 alloc,'' invtext,0 shipvia,0 edisent,0 consignment,1 packages, '' salesperson  FROM gltrans LEFT JOIN accountunits ON gltrans.account=accountunits.account WHERE jobref=0 AND LEFT(gltrans.account,4)='1122' AND periodno<=prd1;
			
			}elseif  (substr($CustAct,0,4)=='2202'){ 
				$sql="SELECT  unitscode, branchcode FROM accountunits WHERE account='" . $CustAct . "' ";
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
												'" . $GLType . "',
												'" . $row['Period'] . "',
												'" . $row[0] . "',
												'" . $row[1] . "',
												'" . FormatDateForSQL($TabDate) . "',
												'" . FormatDateForSQL($TabDate) . "',
												'0', 
												'" . $rate . "',
												'" . $total . "',
												'0',  '0',  '0', '') ";
					$result = DB_query($sql);
			
				}else{
					$msgerr=',应付账款对应的客户异常！';
				}
			}	/*
		 if (substr($CustAct,-3)=='000'){
			$sql="UPDATE register SET account='".$CustAct."' WHERE registerno='".$val[0]."'";
			$result=DB_query($sql);	
		 }*/		 	
	    $SQL="UPDATE  invoicetrans SET   transno ='".$TransNo."'  WHERE invno='".$row['InvNo']."' AND transno=0";
		$result=DB_query($SQL);				 
		if ($result && $inst==3){
		
			DB_Txn_Commit();
		}else{
			$TransNo='';
			DB_Txn_Rollback();		
		}		 


	return $TransNo;
}

/*--------------设计概要--------------
读取发票  分类  进项 专项 0
			   销项专       1
			   销项普票 出口   3
			   >>>>普票进   2
			   >>销项普票   5
	根据发票解析
	  1-  有凭证  2-查找凭证
	    3-解析科目    {
			1>有注册码 有科目科目 单位  个人
			2>
	  }
	  进项点击createJournal.php有问题, 不能传递更改科目
*/
?>
