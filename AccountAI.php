<?php
/* $Id: AccountAI.php 2017 chengjiang $*/
/*AccountUnitCheck.php
 * @Author: ChengJiang 
 * @Date: 2017-04-27 04:53:56 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-11-11 04:59:08
  */
/**/
include('includes/DefineImportBankTransClass.php');
include ('includes/session.php');
$Title = '新单位科目核验';
$ViewTopic = 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ImportTaxInvoice';
include('includes/header.php');
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

	if (!isset($_POST['selectprd'])){ 	
		$_POST["selectprd"]=$_SESSION['period'];
  	}
	 if (!isset($_POST['InvFormat'])){ 
		$_POST['InvFormat']=0;
  	}
    echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden" name="InvFormat" value="' . $_POST['InvFormat'] . '" />
		<input type="hidden" name="unittag" value="' .$_POST['unittag'] . '" />
		';
	echo '<table  class="selection">';

	echo'<tr>
			<td>类别</td>
			<td><select name="InvFormat">';
			$impft=array(0=>'银行类',1=>'发票类');
		foreach($impft as $key=>$value){
			if (isset($_POST['InvFormat']) and ($_POST['InvFormat']==$key)){
				echo '<option selected="selected" value="' ;
			}else {
				echo '<option value ="';
			}
				echo   $key.'">'.$value.'</option>';
		}			
   echo'</td>
			 </tr>';		
   echo'</table>
        <div class="centre">		    
			<input type="submit" name="Search" value="单位查询">';
	if (isset($_POST['Search'])){			
  	 echo'<input type="submit" name="confirm" value="生成科目">	';			
	}
	echo '</div>';
	//--读取税务开票名
	prnMsg('该模块计划放弃，功能已经并入账号税号科目设置','info');
	$move_to_path='companies/'.$_SESSION['DatabaseName'].'/TaxInvoice/';   
	// mb_substr($_SESSION['EDI_MsgSent'],0, strrpos($_SESSION['EDI_MsgSent'],'/'))."/TaxInvoice/";  
		 $str = file_get_contents($move_to_path.iconv('UTF-8','GB2312','客户编码').'.txt');//kehu.txt');
   		
			    // $str = file_get_contents('weixinname.txt');//将整个文件内容读入到一个字符串中
			$str_encoding = mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//转换字符集（编码）
			$arr = explode("\r\n", $str_encoding);//转换成数组
			$r=1;
			$titlearr=array();
			$titlearr=explode(',',$arr[2]);		
			foreach($arr as $value){
			if($r>3){
			
				$file_arr=explode(',',$value);
			
				$address='';
				$tel='';
				$bank='';
				$acc='';
				preg_match_all("/([0-9]{3,4}-)?([0-9]{5,9}){1}/", $file_arr[4], $matches);
		        $tel=$matches[0][0];
		 	    $address=str_replace($matches[0][0],'',$file_arr[4]);
				preg_match_all("/(([0-9]{2,6}-)?([0-9]{10,15}){1})|(([0-9]{15,21}){1})/", $file_arr[5], $matches);
				$acc=$matches[0][0];
			    $bank=str_replace($matches[0][0],'',$file_arr[5]);
			    if ($file_arr[3]!='' &&$file_arr[1]!=''){
					$sql="INSERT IGNORE  INTO `invoiceunits`(
														`taxno`,
														 code,
														`accountno`,
														`bankname`,
														`unitname`,
														`iutype`,
														`tag`,
														`address`,
														`email`,
														`remark`,
														`flg`)
												VALUES(	'".$file_arr[3]."',
														'".$file_arr[0]."',
														'".$acc."',
														'".$bank."',
														'".$file_arr[1]."',
														'1',
														'0',
														'".$address."',
														'".$file_arr[6]."',
														'".$file_arr[7]."',
														0)";
						$result=DB_query($sql);
						if ($result){
							$insflg++;
						}
					}
			
				}
			$r++;
			}
	
	//--
	$SQL="SELECT DISTINCT `invtype`, invoicetrans.`tag`,invoicetrans.`toregisterno`, `toaccount`, unitname toname 
	         FROM `invoicetrans` LEFT JOIN invoiceunits ON invoiceunits.taxno=invoicetrans.toregisterno WHERE toregisterno NOT IN (SELECT `taxaccount` FROM `gljournaltemplet` )";
    $RESULT = DB_query($SQL);
	//DB_data_seek($result, 0);
	$unitarr=array();
	$i=0;
	while($row=DB_fetch_array($RESULT)){
		if ($row['toaccount']==''){
            $acc=nameFuzzyquery($row['toname'],$row['tag']);
            $unitarr[$i]=array('typ'=>$row['invtype'],'tag'=>$row['tag'],'account'=>$acc,'toregisterno'=>$row['toregisterno'],'toaccount'=>$row['toaccount'],'toname'=>$row['toname']);
		    $i++;
		}else{
			$sql="SELECT  `acctype`, `tag`,`account`, `toaccount`, `taxaccount`, `jd` FROM `gljournaltemplet` WHERE  `toaccount`='".$row['toaccount']."'";
			$result = DB_query($sql);
			$rows=DB_fetch_row($result);
			if($rows[0]!=''){
				$SQL="UPDATE `gljournaltemplet` SET `taxaccount`='".$row['toregisterno']."' WHERE  `toaccount`='".$row['toaccount']."'";
				$result=DB_query($SQL);
			}else {
				 $acc=nameFuzzyquery($row['toname'],$row['tag']);
			    $unitarr[$i]=array('typ'=>$row['invtype'],'tag'=>$row['tag'],'account'=>$acc,'toregisterno'=>$row['toregisterno'],'toaccount'=>$row['toaccount'],'toname'=>$row['toname']);
		        $i++; 	
			}
		}
	}
	$SQL="SELECT DISTINCT `toaccount`, `toname`, CASE WHEN (amount>0 and flg=1) OR (amount<0 AND flg=-1 ) THEN 1 ELSE 2 END typ ,chartmaster.tag FROM `banktransaction` LEFT JOIN chartmaster ON chartmaster.accountcode=banktransaction.account WHERE toaccount NOT IN (SELECT `toaccount`FROM `gljournaltemplet`) AND toaccount<>'' AND `reliability`=0"; 
	 $Result = DB_query($SQL);

	while($row=DB_fetch_array($Result)){
	
            $acc=nameFuzzyquery($row['toname'],$row['tag']);
            $unitarr[$i]=array('typ'=>$row['typ'],'tag'=>$row['tag'],'account'=>$acc,'toregisterno'=>'','toaccount'=>$row['toaccount'],'toname'=>$row['toname']);
		    $i++;
		
	}	
if (isset($_POST['confirm']) OR isset($_POST['Search'])	OR  isset($_POST['Go'])	OR isset($_POST['Next']) 
	OR isset($_POST['Previous'])) {
 
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}

   	echo '<table cellpadding="2" class="selection">
			<tr>
				<th class="ascending">序号</th>			
				<th class="ascending">账号</th>
				<th class="ascending">税号/注册号</th>
				<th class="ascending">科目码/名称</th>
				<th class="ascending">客户名称</th> 
				<th >分组</th>
				<th >类别</th>          
				<th >类别</th>
			 	<th ></th>
			</tr>';
			$r=1;
		
	
	foreach($unitarr as $rows){
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
		    $typ=$rows['typ'];
    		printf('<td>%s</td>					
				<td>%s</td>
				<td >%s</td>
				<td >%s</td>
				<td >%s</td>
				<td >%s</td>
				
				<td ><select name="typ[]">		     
					<option  value="1" '.($typ==1? 'selected="selected"':'').'>应收账款</option>
					<option  value="2" '.($typ==2? 'selected="selected"':'').'>应付账款</option>
					<option  value="3" '.($typ==3? 'selected="selected"':'').'>其他应收款</option>
					<option  value="4" '.($typ==4? 'selected="selected"':'').'>其他应付款</option>
				
				</select></td>
				<td ><input type="checkbox" name="chkbx[]" value="%s" checked ></td>											
			</tr>',
			$r,
			$rows['toaccount'],
			$rows['toregisterno'],
			$rows['account'],
			$rows['toname'],
			$rows['tag'],
			$r);
			$r++;
	}
	echo '</table>';
	// prnMsg(var_dump($unitarr),'info');

}
	//下面为生成科目和凭证
if (isset($_POST['confirm'])) {	
		//	 
		$account='';
		$f=0;
	
		Db_data_seek($result,0);
	    prnMsg(count($unitarr),'info');
		foreach($unitarr as $row) {
		   // $strmy.=$_POST['chkbx'][$f];
			if ($row['toname']!=''){
				 
				if ($_POST['chkbx'][$f]!=''){
					$account=nameFuzzyquery($row['toname'],$row['toaccount'],$row['toregisterno'],$row['tag'],$row['typ']);
					$strmy.=$account;
					if ($account==''){
						prnMsg($row['toname'].','.$row['toaccount'].','.$row['toregisterno'].','.$row['tag'].','.$_POST['typ'][$f],'info');
						$account= AddAccount($row['toname'],$row['toaccount'],$row['toregisterno'],$row['tag'],$_POST['typ'][$f]);
					} 
			 	}
			 
			}
			$f++;
		
		
		}//foreach
	
		//	prnMsg($f.'-'.$strmy,'info');
	
	}
	/*elseif(isset($_POST['debug'])){
		$accname="应收调试有限公司1";
		$toacc='642580248810011201';
		$taxno='9137021461432343701';
		$typ=4;
		$tag=3;
			$costitem=1;
			$acccode='224113001';
			$account='2241'.$costitem.$tag.str_pad(substr($acccode,6)+1,strlen($acccode)-6,'0',STR_PAD_LEFT);	
		//$account='1221'.$costitem.$tag.str_pad(substr($acccode,6)+1,strlen($acccode)-6,'0',STR_PAD_LEFT);
		//	$account='1221'.$costitem.$tag.sprintf("%0".(strlen($acccode)-6)."d", (substr($acccode,6)+1));

		$account=AddAccount($accname,$toacc,$taxno,$tag,$typ);
		prnMsg($account,'info');

	}*/

echo ' </form>';
include ('includes/footer.php');
function AddAccount($accname,$toacc,$taxno,$tag,$typ){
	//根据税号  账号 单位名 类别 添加科目 客户
	$crtacc='';
	$costitem=1;
	if ($typ==1){//'1122'){
		//新增科目编码方式
		DB_Txn_Begin();
		$result=DB_query("SELECT MAX(debtorno)  FROM debtorsmaster");
		$debtno=DB_fetch_row($result)[0]+1;
		$crtacc=$debtno;
		$inst=0;
		$sql=" INSERT INTO debtorsmaster(debtorno,
						name,
						address1,
						address2,
						address3,
						address4,
						address5,
						address6,
						currcode,
						salestype,
						clientsince,
						holdreason,
						paymentterms,
						discount,
						pymtdiscount,
						lastpaid,
						lastpaiddate,
						creditlimit,
						invaddrbranch,
						discountcode,
						ediinvoices,
						ediorders,
						edireference,
						editransport,
						ediaddress,
						ediserveruser,
						ediserverpwd,
						taxref,
						customerpoline,
						typeid,
						language_id,
						used
					)
				VALUES('".$debtno."',
				          '".$accname."',
						    '','',
							'',
							'".$toacc."',
							'".$taxno."',
							'',
							'CNY',
							1,
							now(),
							1,20,0,0,0,NULL,100000,0,'',0,0,'','email','','','','',0,2,'zh_CN.utf8',0)";
				$result=DB_query($sql);
		if ($result){
			$inst++;
		}		 
		$sql="INSERT INTO custbranch(branchcode,
                            debtorno,
                            brname,
                            braddress1,
                            braddress2,
                            braddress3,
                            braddress4,
                            braddress5,
                            braddress6,
                            lat,
                            lng,
                            estdeliverydays,
                            area,
                            salesman,
                            fwddate,
                            phoneno,
                            faxno,
                            contactname,
                            email,
                            defaultlocation,
                            taxgroupid,
                            defaultshipvia,
                            deliverblind,
                            disabletrans,
                            brpostaddr1,
                            brpostaddr2,
                            brpostaddr3,
                            brpostaddr4,
                            brpostaddr5,
                            brpostaddr6,
                            specialinstructions,
                            custbranchcode,
                            used )
                    VALUES(CONCAT('".$costitem."','".$tag."','1'),
                             '".$debtno."',
                             CONCAT('".$costitem."','".$tag."','1','".$accname."'),
                             '','','',
							'".$toacc."',
							'".$taxno."',
							 '', 0,0,0,1,0, 0,'','','','',2,1,1,1,0,
                                 '','','','','','','','',0 );";
	    	$result=DB_query($sql);
				if ($result){
			$inst++;
		}		 
		$account="1122".$costitem.$tag.$debtno;
		$sql = "INSERT INTO chartmaster (accountcode,
						accountname,
						group_,
						`cashflowsactivity`,
						`tag`,
						`crtdate`,
						 `low`,
						  `used`)
				SELECT 	'".$account."' accountcode,CONCAT( `accountname`,'-','".$accname."') accountname, `group_`, `cashflowsactivity`,".$tag." `tag`,'".date("Y-m-d")."' `crtdate`,1 `low`, `used` FROM `chartmaster` WHERE accountcode='1122'";
				
		$result = DB_query($sql,$ErrMsg);
			if ($result){
			$inst++;
		}		 
		$sql="INSERT INTO `accountunits`(`account`,
									`unitscode`,
									`branchcode`,
									`tag`,
									`unittype`
								)
							VALUES(	'".$account."',
							      '".$debtno."',
							     '".$costitem.$tag."1',
								'".$tag."',
								'1')";
	    $result = DB_query($sql,$ErrMsg);
			if ($result){
			$inst++;
		}		 
		$sql="INSERT INTO `gljournaltemplet`(`account`,
											`toaccount`,
											`taxaccount`,
											`acctype`,
											`tag`,
											`jd`,
											`templet`,
											`maxamount`,
											`remark`,
											`abstract`,
											`flg`
										)
										VALUES('".$account."',
											'".$toacc."',
											'".$taxno."',
											'1',
											'".$tag."',
											'0',
											'',
											'0',
											'',
											'',
											'0')";
		 $result = DB_query($sql,$ErrMsg);
		if ($result && $inst==4){
			//$inst++;
			DB_Txn_Commit();
		}else{
			 DB_Txn_Rollback();

			 $account='';
		}		 
		//插入客户
	}elseif($typ==2){//'2202'){
		$sql="SELECT `supplierid` FROM `suppliers` WHERE supplierid LIKE CONCAT('1','".$tag."','%') ORDER BY supplierid DESC LIMIT 1";
		$result=DB_query($sql);
		$supid=DB_fetch_row($result)[0];
		if (substr($supid,2)=='999'){
			$debtno=substr($supid,0,2).'1000';
		}else{
			$debtno=$supid+1;
		}
		
		$sql="INSERT INTO `suppliers`(`supplierid`,
								`suppname`,
								`address1`,
								`address2`,
								`address3`,
								`address4`,
								`address5`,
								`address6`,
								`supptype`,
								`lat`,
								`lng`,
								`currcode`,
								`suppliersince`,
								`paymentterms`,
								`lastpaid`,
								`lastpaiddate`,
								`bankact`,
								`bankref`,
								`bankpartics`,
								`remittance`,
								`taxgroupid`,
								`factorcompanyid`,
								`taxref`,
								`phn`,
								`port`,
								`email`,
								`fax`,
								`telephone`,
								`url`,
								`used`
							)
						VALUES( '".$debtno."',
						        '".$accname."',										
									'','','',	
									'".$toacc."',
									'".$taxno."',
									'',0,0.000000,0.000000,'CNY',
									'".date("Y-m-d")."',
									'20',0,NULL,
									'','0','0',0,1,0,'',
									'','','','','','',1	)";
		$result = DB_query($sql,$ErrMsg);
			if ($result){
			$inst++;
		}		 
		$account="2202".$debtno;
		$sql = "INSERT INTO chartmaster (accountcode,
						accountname,
						group_,
						`cashflowsactivity`,
						`tag`,
						`crtdate`,
						 `low`,
						  `used`)
				SELECT 	'".$account."' accountcode,CONCAT( `accountname`,'-','".$accname."') accountname, `group_`, `cashflowsactivity`,".$tag." `tag`,'".date("Y-m-d")."' `crtdate`,1 `low`, `used` FROM `chartmaster` WHERE accountcode='2202'";
				
		$result = DB_query($sql,$ErrMsg);
			if ($result){
			$inst++;
		}		 

		$sql="INSERT INTO `gljournaltemplet`(
						`account`,
						`toaccount`,
						`taxaccount`,
						`acctype`,
						`tag`,
						`jd`,
						`templet`,
						`maxamount`,
						`remark`,
						`abstract`,
						`flg`
					)
				VALUES('".$account."',
											'".$toacc."',
											'".$taxno."',
											'1',
											'".$tag."',
											'0',
											'',
											'0',
											'',
											'',
											'0')";
			    	$result = DB_query($sql,$ErrMsg);
		if ($result && $inst==2){
			//$inst++;
			DB_Txn_Commit();
		}else{
			 DB_Txn_Rollback();

			 $account='';
		}		 
	}elseif($typ==3){//其他应收款1221
		$sql="SELECT `accountcode` FROM `chartmaster` WHERE accountcode='1221' OR (accountcode LIKE '1221%' AND tag=3) ORDER BY accountcode DESC LIMIT 1";
		
		$result=DB_query($sql);
		$acccode=DB_fetch_row($result)[0];
		if(strlen($acccode)==4){
			$account='1221'.$costitem.$tag.'001';
		}else{
			$account='1221'.$costitem.$tag.str_pad(substr($acccode,6)+1,strlen($acccode)-6,'0',STR_PAD_LEFT);
		}
		$sql="INSERT INTO `chartmaster`(`accountcode`,
										`accountname`,
										`group_`,
										`cashflowsactivity`,
										`tag`,
										`crtdate`,
										`low`,
										`used`
									) 
									SELECT
									 '".$account."' accountcode,
									CONCAT(`accountname`,'-','".$accname."') accountname,
									`group_`,
									`cashflowsactivity`,
									`tag`,
									'".date("Y-m-d")."' crtdate,
									`low`,
									`used`
								FROM  `chartmaster`
								WHERE  accountcode = '1221'";
		$result = DB_query($sql);
		if ($result){
			$inst++;
		}
			$sql="INSERT INTO `gljournaltemplet`(
						`account`,
						`toaccount`,
						`taxaccount`,
						`acctype`,
						`tag`,
						`jd`,
						`templet`,
						`maxamount`,
						`remark`,
						`abstract`,
						`flg`
					)
				VALUES('".$account."',
											'".$toacc."',
											'".$taxno."',
											'1',
											'".$tag."',
											'0',
											'',
											'0',
											'',
											'',
											'0')";
			    	$result = DB_query($sql,$ErrMsg);
		if ($result && $inst==1){
			//$inst++;
			DB_Txn_Commit();
		}else{
			 DB_Txn_Rollback();

			 $account='';
		}		 		 
	}elseif($typ==4){//其他应付款2241
		$sql="SELECT `accountcode` FROM `chartmaster` WHERE accountcode='2241' OR (accountcode LIKE '2241%' AND tag=3) ORDER BY accountcode DESC LIMIT 1";
		$result=DB_query($sql);
		$acccode=DB_fetch_row($result)[0];
		if(strlen($acccode)==4){
			$account='2241'.$costitem.$tag.'001';
		}else{
			$account='2241'.$costitem.$tag.str_pad(substr($acccode,6)+1,strlen($acccode)-6,'0',STR_PAD_LEFT);
		}
			$sql="INSERT INTO `chartmaster`(`accountcode`,
										`accountname`,
										`group_`,
										`cashflowsactivity`,
										`tag`,
										`crtdate`,
										`low`,
										`used`
									) SELECT
									'".$account."' accountcode,
									CONCAT(`accountname`,'-','".$accname."') accountname,
									`group_`,
									`cashflowsactivity`,
									`tag`,
									'".date("Y-m-d")."' crtdate,
									`low`,
									`used`
								FROM  `chartmaster`
								WHERE  accountcode = '2241'";
		$result = DB_query($sql);
		if ($result){
			$inst++;
		}
			$sql="INSERT INTO `gljournaltemplet`(
						`account`,
						`toaccount`,
						`taxaccount`,
						`acctype`,
						`tag`,
						`jd`,
						`templet`,
						`maxamount`,
						`remark`,
						`abstract`,
						`flg`
					)
				VALUES('".$account."',
						'".$toacc."',
						'".$taxno."',
						'1',
						'".$tag."',
						'0',
						'',
						'0',
						'',
						'',
						'0')";
		$result = DB_query($sql,$ErrMsg);
		if ($result && $inst==1){
			//$inst++;
			DB_Txn_Commit();
		}else{
			 DB_Txn_Rollback();

			 $account='';
		}		 		 
	}
	return $account;
}
function nameFuzzyquery($accname,$toacc,$taxno,$tag,$typ){
	//$ROW toname account toaccount tobank debit
	
	$sqlacc="SELECT `accountcode`, `accountname`  
						FROM `chartmaster`
						WHERE LEFT(accountcode ,4) IN('1122','1221','2202','2241') AND length(accountcode)>4 AND tag='".$tag."'";
	
	$resultacc=DB_query($sqlacc);
	$jg=80;
	$retacc='';

		DB_data_seek($resultacc,0);
		while($row=DB_fetch_array($resultacc)){
			
			$ff=0;
			$rr=-1;
			$invtyp=3;
			$bankid=0;			
			similar_text(subName($accname),subName($row['accountname']),$ff);
			if ($ff>=$jg ){
				$jg=$ff;
				$rr=$i;
			//	$result=DB_query("SELECT  `unitscode`, `branchcode`, `tag`, `unittype` FROM `accountunits` WHERE account='".$row['accountcode']."'");
			//	$rowno=DB_fetch_row($result);
		
				$retacc=$row['accountcode'];
				if ($toacc!='' ){
				$sql="UPDATE `gljournaltemplet`
							SET	`toaccount` ='".$toacc."'							
							WHERE account='".$retacc."' AND `taxaccount` !=''";	
				}elseif ($taxno!='' ){
					$sql="UPDATE `gljournaltemplet`
							SET	 `taxaccount`  ='".$taxno."'							
							WHERE account='".$retacc."' AND `toaccount`!=''";	
	
				}
				$result = DB_query($sql);
				if (!$result){
				$result=DB_query("INSERT INTO gljournaltemplet(taxaccount,
											toaccount,
											account,
											acctype,
											`tag`,											
											jd,
											templet,
											maxamount,
											remark,
											abstract,
											flg	)
									VALUES(	'".$taxno."',
											'".$toacc."',
											'".$row['accountcode']."',
											'1',
											'".$tag."',
											'1',
											'',	 0,	'',	'',	0)");
				$result = DB_query($sql);
				}
				if ($result){
					$inst++;
				}
				//DB_Txn_Commit();
				break;
			} 
		
							
		}//while

	return $retacc;
}
function subName($value){
	#  替换字符中的...
	$co=strlen($value);

	$value=str_replace('有限公司','',$value);
	if ($co==strlen($value)){
	$value=str_replace('有限责任公司','',$value);	
	}
	if ($co==strlen($value)){
	$value=str_replace('股份有限公司','',$value);	
	}
	if ($co==strlen($value)){
	$value=str_replace('集团有限公司','',$value);	
	}
	if ($co>strlen($value)){
	$value=str_replace('分公司','',$value);	
	}
	if (strpos($value,')')>0 || strpos($value,'）')){
	$i=strpos($value,'(');
	$e=strpos($value,')');
	if ($e>0){
	$value=substr($value,0,$i).substr($value,$e);
	}else{
	$i=strpos($value,'（');
	$e=strpos($value,'）');
	$value=substr($value,0,$i).substr($value,$e+3);	
	}
	}
		return $value;
	}
?>
