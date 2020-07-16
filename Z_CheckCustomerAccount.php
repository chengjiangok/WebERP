
<?php
/* $Id: Z_AccountNameCheck.php  $*/

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:58
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-05-04 18:02:27
 */

include('includes/session.php');
include ('includes/FunctionsAccount.php');
$Title = '客户供应商会计科目维护';
/* Manual links before header.php */
$ViewTopic= 'GeneralLedger';// Filename in ManualContents.php's TOC.
$BookMark = 'GLAccounts';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<script type="text/javascript">
function reload(){
 window.location.reload();
 }
 function get_value(){ 
	 var selsct_value = document.getElementById("chose").value;
	 //获取select的值 
	 alert(selsct_value);
	  form.submit();
	}

 function check(){				
	var all=document.getElementById("chose")
	var box=document.getElementsByTagName("input")   //此处选中了所有的input,包括全选按钮本身，在后面操作中需要注意
	
	for (i = 0; i < box.length-1; i++) {
	box[i].onclick = onclike
	}
	     
}

</script>';

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		_('General Ledger Accounts') . '" />' . ' ' . $Title . '</p>';
echo '<form method="post" id="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
      <div>
	  <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	  /*
echo '<div class="page_help_text">
		应收应付科目检查功能简介<br>
		科目名近似比对.....根据应收应付的科目名称比较,如果科目名近似，手工确认合并相近科目名及代码！<br>
		科目客户户查询：根据应收应付的科目名和发票导出的客户名称比较,如果科目名近似，手工确认合并相近科目名及代码！
		</div>'; */
		echo '<table class="selection">';
		echo '<tr>
				<td>单元分组</td>
				<td><select name="unittag" size="1" >';	
		foreach($_SESSION['CompanyRecord'] as $key=>$row)	{		
			    if ($row['coycode']!=0){
					if($row['coycode']==$myrow['tag']){
						echo '<option selected="selected" value="';			
					}else{
						echo '<option value="';
					}
						echo  $row['coycode']. '">' .$row['unitstab']  . '</option>';
			
					if(-$row['coycode']==$myrow['tag']){
						echo '<option selected="selected" value="'. (-$row['coycode']). '">' .$row['unitstab']  . '内</option>';
					}else{
						echo '<option  value="'. (-$row['coycode']). '">' .$row['unitstab']  . '内</option>';
					}
				}
			
			
		}
		echo'<tr> <td>科目类别</td>    			
				<td><select tabindex="1"  name="acctype">				
				 <option value="1122" >1122 应收账款</option>
				 <option value="1221" >1221 其他应收款</option>
				 <option value="2202" >2202 应付账款</option>
				 <option value="2241" >2241 其他应付款</option>
	  
	        </select>
	   </td></tr>';	
	   echo '</table>' ;     
echo'<br />
		<div class="centre">	
	
		    <input type="submit" name="ActCustname" value="科目/客户对应" />
		
			<input type="submit" name="CustCheck" value="相近客户名查找[客户表]" />
			
		
			<input type="submit" name="CheckCustAct" value="客户科目检查" />
		
			<input type="submit" name="accnamecheck" value="科目名近似查找[科目表]" />
			<br/><br/>';
			//  <input type="submit" name="AccountName" value="客户名科目对应" />
			//	<input type="submit" name="AccountUseCheck" value="科目频度检查" />
			/*
			if(isset($_POST['AccountName'])){
				echo'<br><input type="submit" name="AccNameModify" value="科目名客户名关联" />';
			}	
			*/
			if(isset($_POST['CustCheck'])){
				echo'<br><input type="submit" name="CustCheckModify" value="相近客户名改写" />';
			}
			/*	
			if(isset($_POST['AccountUseCheck'])){
				echo'<br><input type="submit" name="AccountUseModify" value="科目频度改写" />';
			}	*/
		
	echo'	</div>';
	if(isset(	$_POST['SelectTo'])||isset(	$_POST['ToSelect'])){
		//prnMsg(	$_POST['SelectTo'].'='.$_POST['ToSelect']);
		$InputError ==1;
	
		$delacc='';
		$toacc='';
		if(isset(	$_POST['SelectTo'])){
			$ST=explode(',',$_POST['SelectTo']);
			$delacc=$ST[1];
			$toacc=$ST[0];
	
		}elseif(isset(	$_POST['ToSelect'])){
			$ST=explode(',',$_POST['ToSelect']);
			$delacc=$ST[1];
			$toacc=$ST[0];
		}
		
			if(strlen($toacc)>5&&strlen($delacc)>5) {// no input errors
			
				$result = DB_Txn_Begin();
				//echo '<br />' . _('Adding the new chartmaster record');
				$sql = "DELETE FROM chartmaster
						WHERE accountcode='" . $delacc . "'";		
	
				$DbgMsg = _('The SQL statement that failed was');
				$ErrMsg =_('The SQL to insert the new chartmaster record failed');
				$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
				//echo ' ... ' . _('completed');
				$sql="DELETE FROM glaccountusers WHERE accountcode='" . $delacc . "'";
				$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
				//echo ' ... ' . _('completed');
			
				DB_IgnoreForeignKeys();
	
				ChangeFieldInTable("bankaccounts", "accountcode", $delacc, $toacc, $db);
	
				ChangeFieldInTable("bankaccountusers", "accountcode", $delacc, $toacc, $db);
	
				ChangeFieldInTable("banktrans", "bankact", $delacc, $toacc, $db);
	
				//ChangeFieldInTable("chartdetails", "accountcode", $delacc, $toacc, $db);
				
	
				ChangeFieldInTable("cogsglpostings", "glcode", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "debtorsact", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "pytdiscountact", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "creditorsact", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "payrollact", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "grnact", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "exchangediffact", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "purchasesexchangediffact", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "retainedearnings", $delacc, $toacc, $db);
				ChangeFieldInTable("companies", "freightact", $delacc, $toacc, $db);
	
				ChangeFieldInTable("fixedassetcategories", "costact", $delacc, $toacc, $db);
				ChangeFieldInTable("fixedassetcategories", "depnact", $delacc, $toacc, $db);
				ChangeFieldInTable("fixedassetcategories", "disposalact", $delacc, $toacc, $db);
				ChangeFieldInTable("fixedassetcategories", "accumdepnact", $delacc, $toacc, $db);
	
				ChangeFieldInTable("glaccountusers", "accountcode", $delacc, $toacc, $db);
				
				ChangeFieldInTable("gltrans", "account", $delacc, $toacc, $db);
	
				ChangeFieldInTable("lastcostrollup", "stockact", $delacc, $toacc, $db);
				ChangeFieldInTable("lastcostrollup", "adjglact", $delacc, $toacc, $db);
	
				ChangeFieldInTable("locations", "glaccountcode", $delacc, $toacc, $db);// Location's ledger account.
	
				ChangeFieldInTable("pcexpenses", "glaccount", $delacc, $toacc, $db);
	
				ChangeFieldInTable("pctabs", "glaccountassignment", $delacc, $toacc, $db);
				ChangeFieldInTable("pctabs", "glaccountpcash", $delacc, $toacc, $db);
	
				ChangeFieldInTable("purchorderdetails", "glcode", $delacc, $toacc, $db);
	
				ChangeFieldInTable("salesglpostings", "discountglcode", $delacc, $toacc, $db);
				ChangeFieldInTable("salesglpostings", "salesglcode", $delacc, $toacc, $db);
	
				ChangeFieldInTable("stockcategory", "stockact", $delacc, $toacc, $db);
				ChangeFieldInTable("stockcategory", "adjglact", $delacc, $toacc, $db);
				ChangeFieldInTable("stockcategory", "issueglact", $delacc, $toacc, $db);
				ChangeFieldInTable("stockcategory", "purchpricecode", $delacc, $toacc, $db);
				//ChangeFieldInTable("stockcategory", "materialuseagevarac", $delacc, $toacc, $db);
				ChangeFieldInTable("stockcategory", "wipact", $delacc, $toacc, $db);
	
				ChangeFieldInTable("taxauthorities", "taxglcode", $delacc, $toacc, $db);
				ChangeFieldInTable("taxauthorities", "purchtaxglaccount", $delacc, $toacc, $db);
				ChangeFieldInTable("taxauthorities", "bankacctype", $delacc, $toacc, $db);
	
				ChangeFieldInTable("workcentres", "overheadrecoveryact", $delacc, $toacc, $db);
				ChangeFieldInTable("registername", "account", $delacc, $toacc, $db);
				ChangeFieldInTable("registeraccount", "subject", $delacc, $toacc, $db);
				ChangeFieldInTable("accountsubject", "subject", $delacc, $toacc, $db);
				DB_ReinstateForeignKeys();
	
				$result = DB_Txn_Commit();
				echo '<p>' . _('GL account Code') . ': ' . $delacc . ' ' . _('was successfully changed to') . ' : ' . $toacc;
			}//only do the stuff above if  $InputError==0
			
		//}
	}
if(isset($_POST['CheckCustAct'])){//客户名转换查询20200503
	$sql="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` 
	       FROM `register_account_sub`
		   WHERE sub IN (SELECT sub FROM `register_account_sub` WHERE sub<>'' GROUP BY sub HAVING count(*)>1)
		   ORDER BY sub";
		
	$result = DB_query($sql);
	$subject='';
	$flag=0;
	while ($row = DB_fetch_array($result)) {
		if ($subject==""|| $subject!=$row['sub']){
			if ($flag==0 &&isset($AccountCustome[$subject]) ){
				unset($AccountCustome[$subject]);
				$flag=0;
			}
			$subject=$row['sub'];
			$regid=$row['regid'];
			$custname=$row['custname'];
			$flag=0;
			$AccountCustome[$row['sub']][]=$row;
			
		}
		if(!($subject==$row['sub']&&$regid==$row['regid']&&$custname==$row['custname'])){		
			$AccountCustome[$row['sub']][]=$row;
			$flag++;
		}
		
	}

	if ($flag==0 &&isset($AccountCustome[$subject]) ){
		
		unset($AccountCustome[$subject]);
		$flag=0;
	}
	echo '<div class="page_help_text">
		客户科目检查功能简介<br>
	     以科目编码检索对应的客户编码及客户名称!
		</div>'; 
	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">序号</th>		
			<th class="ascending">科目编码</th>
			<th class="ascending">客户名称</th>			
			<th class="ascending">客户编码</th>	
			<th class="ascending">注册码</th>		
			<th class="ascending">银行账号</th>		
			<th class="ascending"></th>		
		</tr>';
	
				$r=1;
		foreach ($AccountCustome as $sub=>$vl){
			foreach($vl as $row){
				if ($subject==""|| $subject!=$row['sub']){
					$subject=$row['sub'];
					 if ($k==1)
					 $k=0;
					 else
					 $k=1;
				}
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					//$k=0;
				} else {
					echo '<tr class="OddTableRows">';
				
				}	 
			printf('<td>%s</td>
					<td>%s</td>
					<td>%s</td>					
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td></td>	
				</tr>',
						$r,
						$row['sub'],
						$row['custname'],
						$row['regid'],
						$row['registerno'],
						$row['bankaccount']	);
					$r++;
			
				}
				
		}
		echo '</table>'; 
	
	
     
}elseif (isset($_POST['accnamecheck'])||isset(	$_POST['SelectTo'])||isset(	$_POST['ToSelect'])){	//科目名近似查��
	
	//	if ($_POST['unittag']==0){
				$sql="SELECT accountcode, accountname, tag,  used FROM chartmaster WHERE LEFT(accountcode,4)  IN('1122','2202','1221','2241')  AND length(accountcode)>4 ORDER BY accountcode";
	/*	}else{
				$sql="SELECT accountcode, accountname, tag,used FROM chartmaster WHERE LEFT(accountcode,4) IN('1122','2202','1221','2241')  AND length(accountcode)>4 AND tag=".explode('^',$_POST['unittag'])[1]." ORDER BY accountcode";

		}*/
		$ErrMsg = _('The chart accounts could not be retrieved because');

		$result = DB_query($sql,$ErrMsg);
	
		$accarr=array();
		while ($row = DB_fetch_array($result)) {
		
			$accarr[$row['accountcode']]=array($row['accountname'],$row['tag'],$row['used']);
		}
    //var_dump($accarr);
		$acc_arr=$accarr;
		$jg=90;
		$ff=0;
		$rr=0;
		$accary=array();
		foreach($accarr as $KEY=> $VAL){
			//array_diff_key
			//echo substr($VAL[0],(1+strpos($VAL[0],'-')))."<br/>";
			unset($acc_arr[$KEY]);
			foreach($acc_arr as $key=>$val){
					similar_text(substr($VAL[0],(1+strpos($VAL[0],'-'))),substr($val[0],(strpos($val[0],'-')+1)),$ff);
					if ($ff>$jg  ){
				
						$rr++;
					//	prnMsg(substr($VAL[0],(1+strpos($VAL[0],'-'))).'[]'.substr($val[0],(strpos($val[0],'-')+1)).'()'.$ff)	;		
						$accary[$KEY]=array(array($VAL[0],$key,$val[0]),$ff);
						unset($acc_arr[$key]);
					} 
					$ff=0;

			}
			//if ($rr>0){
					}
		
		if (count($accary)>0 ){
		echo '<br /><table class="selection">';
		echo '<tr>
				<th class="ascending">序号</th>		
				<th class="ascending">选择科目</th>
				<th class="ascending">科目名称</th>	
				<th class="ascending">选择科目</th>
				<th class="ascending">科目名称</th>			
				<th class="ascending"></th>
							
			</tr>';
			$k=0; //row colour counter
				$r=0;
		foreach($accary as $key=>$val){
			for($i=0 ;$i<count($val);$i++){
				if (!empty($val[$i][0])){
					
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}	 
			printf(' <td>%s</td>
					<td><button type="submit" name="SelectTo" value="%s,%s" >%s</button></td>
					<td>%s</td>	
					<td><button type="submit" name="ToSelect" value="%s,%s" >%s</button></td>
					<td>%s</td>
					<td> </td>	
				</tr>',
				$r+1,
				$key,$val[$i][1],				
				$key,
				$val[$i][0],

				$val[$i][1],			
				$key,
				$val[$i][1],
				$val[$i][2]
				);
				$r++;
				}
			}
		}	
			echo  '<input type="hidden" name="row" value="' . $r . '" />';
			echo '</table>'; 
			}else{
			prnMsg('往来科目名中没有近似的科目名','info');
		}

		/*
		$sql="SELECT accountcode, accountname, tag, crtdate, low, used FROM chartmaster WHERE LEFT(accountcode,4)='".$_POST['acctype']."' AND length(accountcode)>4 ORDER BY accountname";

		$ErrMsg = _('The chart accounts could not be retrieved because');

		$result = DB_query($sql,$ErrMsg);
		DB_data_seek($result,0);
		echo '<br /><table class="selection">';
		echo '<tr>
				<th class="ascending">' . _('Account Code') . '</th>
				<th class="ascending">科目名称</th>
			</tr>';

		$k=0; //row colour counter
		
		while ($row = DB_fetch_array($result)) {
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
		printf("<td>%s</td>	
				<td>%s</td>	
			</tr>",
			$row['accountcode'],$row['accountname']);
				}
		echo '</table>';
    */

}elseif(isset($_POST['CustCheck'])){//相近客户名查找[客户表]
	
	//if ($_POST['unittag']==0){
   		$sql="SELECT regid, custname, tag, account, regdate, custtype, flg FROM registername ";//WHERE tag=".substr($_POST['acctype'],0,1);
	//}else{
	//	$sql="SELECT regid, custname, tag, account, regdate, custtype, flg FROM registername WHERE tag=".explode('^',$_POST['unittag'])[1];

	//}
	$result = DB_query($sql);
	$accarr=array();
	while ($row = DB_fetch_array($result)) {
	
		 $accarr[$row['regid']]=array($row['custname'],$row['tag'],$row['account'],$row['custtype'],$row['flg']);
	}

	$acc_arr=$accarr;
	$jg=95;
	$ff=0;
	$rr=0;
	$accary=array();
	foreach($accarr as $KEY=> $VAL){
		//array_diff_key
		unset($acc_arr[$KEY]);
	    foreach($acc_arr as $key=>$val){
			similar_text($VAL[0],$val[0],$ff);
	 	 // similar_text($VAL[0],substr($val[0],(strpos($val[0],'-')+1)),$ff);
			if ($ff>$jg  ){
				$rr++;
				$VACC='';
				$vacc='';
				/*
				if ($VAL[2]==''){
		      		$SQL ="SELECT registername.regid,			
							accountsubject.subject accsub,												
							registeraccount.subject regsub											
						FROM  registername
					
						LEFT JOIN accountsubject ON registername.regid=accountsubject.regid
						LEFT JOIN registeraccount ON registername.regid=registeraccount.regid
						WHERE registername.flg<>-1 AND (accountsubject.subject <>'' OR registeraccount.subject <>'') 
						AND registername.regid=".$KEY;
					$result=DB_query($SQL);
					$ROW=DB_fetch_row($result);
					if(!empty($ROW)){
						if ($ROW[1]!=''){
								$VACC=$ROW[1];
						}elseif ($ROW[2]!='') {
								$VACC=$ROW[2];
						}
					}
				}else{
					$VACC=$VAL[2];
				}
					if ($val[2]==''){
						$SQL ="SELECT registername.regid,			
										accountsubject.subject accsub,												
										registeraccount.subject regsub											
									FROM  registername											
									LEFT JOIN accountsubject ON registername.regid=accountsubject.regid
									LEFT JOIN registeraccount ON registername.regid=registeraccount.regid
									WHERE registername.flg<>-1 AND (accountsubject.subject <>'' OR registeraccount.subject <>'') 
									AND registername.regid=".$key;
						$result=DB_query($SQL);
						$ROW=DB_fetch_row($result);
						if(!empty($ROW)){
							if ($ROW[1]!=''){
									$vacc=$ROW[1];
							}elseif ($ROW[2]!='') {
									$vacc=$ROW[2];
							}
						}
					

				}else{
					$vacc=$val[2];
				}*/
				$accary[$KEY]=array(array($VAL[0],$key,$val[0],$vacc,$VACC),$ff);
			} 
			$ff=0;

		}
		if ($rr==0){
			unset($accarr[$KEY]);
		}
	}
	//var_dump($accary);
	if (count($accary)>0 ){
	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">序号</th>		
			<th class="ascending">序列编号</th>
			<th class="ascending">科目编码</th>
			<th class="ascending">保留客户名</th>	
			<th class="ascending">序列编码</th>
			<th class="ascending">科目编号</th>
			<th class="ascending">客户名</th>			
			<th class="ascending">选择/改变默认</th>		
		</tr>';
		$k=0; //row colour counter
	     $r=0;
	foreach($accary as $key=>$val){
		for($i=0 ;$i<count($val);$i++){
			if (!empty($val[$i][0])){
				
			 if ($k==1){
				 echo '<tr class="EvenTableRows">';
				 $k=0;
			 } else {
				 echo '<tr class="OddTableRows">';
				 $k=1;
			 }	 
		 printf(' <td>%s</td>
							<td>%s</td>
							<td>%s</td>
							<td>%s</td>	
							<td>%s</td>	
							<td>%s</td>
							<td>%s</td>
							<td> <input type="checkbox" name="chose'.$r.'[]" value="%s" checked>
									<input type="checkbox" name="chose'.$r.'[]" value="%s"  ></td>	
						</tr>',
			 $r+1,
			 $key,
			 $val[$i][4],
			 $val[$i][0],
			 $val[$i][1],
			 $val[$i][3],
			 $val[$i][2],
			 $key.'^'.	 $val[$i][4].'^'. $val[$i][1].'^'. $val[$i][3],			
			 $val[$i][1] .'^'.$key);
			 $r++;
			}
		}
	}	
		echo  '<input type="hidden" name="row" value="' . $r . '" />';
		echo '</table>'; 
    }else{
		prnMsg('往来科目名中没有近似的科目名','info');
	}

}elseif(isset($_POST['ActCustname'])){//科目客户名对应20200503
	$sql="SELECT `regid`, `registerno`, `bankaccount`, `custname`, `sub`, `regdate`, `acctype`, `tag` FROM `register_account_sub` WHERE 1";
	
	$result = DB_query($sql);

	while ($row = DB_fetch_array($result)) {
	      if (strlen($row['sub'])<5){
			 $CustnameRegID[$row['custname']]=array($row['regid'],$row['tag'],$row['sub'],$row['custtype'],0);
		  }else{
		 	$CustnameAct[$row['sub']]=array($row['custname'],$row['regid'],$row['tag'],$row['custtype'],0);
		  }
	}
	//var_dump( $CustnameRegID);
	$sql="SELECT `accountcode`, `accountname`, `currcode`, `tag`, `crtdate` 
	        FROM `chartmaster` 
			WHERE LEFT(accountcode,4)  IN ('1122','1221','2241','2202') 
			  AND length(accountcode)>4";

	$result = DB_query($sql);


	echo '<br /><table class="selection" style="width: 770px;">';
	echo '<tr>
			<th style="width: 7px;">序号</th>		
			<th style="width: 33px;">科目编码</th>
			<th  style="width: 320px;">科目名称</th>	
			<th style="width: 300px;">科目->客户名称</th>
			<th style="width: 50px;">客户名->客户编码</th>
			<th style="width: 30px;"></th>
			<th style="width: 30px;">客户名</th>			
				
		</tr>';
		$r=1; //row colour counter
	while ($row=DB_fetch_array($result)){
			 if ($k==1){
				 echo '<tr class="EvenTableRows">';
				 $k=0;
			 } else {
				 echo '<tr class="OddTableRows">';
				 $k=1;
			 }
			 if (isset($CustnameAct[$row['accountcode']])){
				$CustnameAct[$row['accountcode']][4]=1;
			 }
			 if(isset($CustnameRegID[substr($row['accountname'],strpos($row['accountname'],'-')+1)])){
				$CustnameRegID[substr($row['accountname'],strpos($row['accountname'],'-')+1)][4]=1;
			 }
			 $custname=htmlentities(substr($row['accountname'],strpos($row['accountname'],'-')+1))	; 
		 printf(' <td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>	
					<td>%s</td>	
					<td>%s</td>							
					<td> 
						<input type="checkbox" name="chose'.$r.'[]" value="%s"  ></td>	
				</tr>',
			 $r,
			 $row['accountcode'],
			 $row['accountname'],
			 empty($CustnameAct[$row['accountcode']][0])?"":'['. $CustnameAct[$row['accountcode']][1].']'.$CustnameAct[$row['accountcode']][0],
			$CustnameRegID[substr($row['accountname'],strpos($row['accountname'],'-')+1)][0],
			'',
			$custname);
			 $r++;
			
		
	}	
	
		echo '</table>'; 
		echo '<br /><table class="selection" style="width: 720px;">';
		echo '<tr>
				<th style="width: 7px;">序号</th>		
				<th style="width: 33px;">科目编码</th>
				<th  style="width: 320px;">科目名称</th>	
				<th style="width: 300px;">客户名称</th>
				<th style="width: 40px;">客户编码</th>
				<th style="width: 20px;"></th>			
			</tr>';
			$r=1;
		foreach ($CustnameAct as $key=>$val){
			if ($val[4]==0){
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
		
			printf(' <td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>	
					<td>%s</td>	
					<td>%s</td>						
				</tr>',
				$r,
				$key,
				'',
				$val[0],
				$val[1],
				'');
				$r++;
			}	
		   }	
		   $r=1;
		   echo '<tr>
		   <th style="width: 7px;">序号</th>		
		   <th style="width: 33px;">科目编码</th>
		   <th  style="width: 320px;">科目名称</th>	
		   <th style="width: 300px;">客户名称</th>
		   <th style="width: 40px;">客户编码</th>
		   <th style="width: 20px;"></th>			
	   		</tr>';
		   foreach ($CustnameRegID as $key=>$val){
			if ($val[4]==0){
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
		
			printf(' <td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>	
					<td>%s</td>	
					<td>%s</td>							
				</tr>',
				$r,
				$val[2],
				'',
				$key,
				$val[0],
				'');
				$r++;
			}	
  		 }	
	   echo '</table>'; 
  

}elseif(isset($_POST['CustCheckModify'])){/**删除regid  如果有科目需要修改科目 */
	
    //	prnMsg('相近客户名改写'.$_POST['chose0'][1],'info');
	$InputError ==1;
	$reg_key='';
	for($i=0;$i<$_POST['row'];$i++){

	    if(isset($_POST['chose'.$i][0])){
				$reg_key=explode('^',$_POST['chose'.$i][0]);
			
				if(isset($_POST['chose'.$i][1])){//不选择默认
					  $regok=$reg_key[2];
						$regdel=$reg_key[0]; 
						$accok=$reg_key[3];
						$accdel=$reg_key[1];
				}else{//默认
					 $regok=$reg_key[0];
					 $regdel=$reg_key[2];
				
					 $accok=$reg_key[1];
					 $accdel=$reg_key[3]; 
					}
			if ($delacc==''){  //被删除的regid 没有科目 直接跟新registeraccount  

			  $sql="UPDATE registeraccount SET  regid=".$regok."  WHERE regid=".$regdel;
					$result=DB_query($sql);
				$sql="UPDATE accountsubject SET regid=".$regok." WHERE regid=".$regdel;
					$result=DB_query($sql);
				$sql="DELETE FROM registername WHERE regid=".$regdel;
					$result=DB_query($sql);
			
				}else{//registeraccount不空
					$sql="UPDATE registeraccount SET regid=".$regok."  WHERE regid=".$regdel;
					$result=DB_query($sql);
					UpdateAccount($accok,$acdelc);

				} 	
			}else{//del科目不为空
           if ($accok!=''){
						 //del registername  update accountsubject registeraccount  regid
						 //运行   UpdateAccount();
					 }
			}
				prnMsg($acc_acc[0].'accmodify','info');

		}
	
}

//功能没有使用
/*
if(isset($_POST['AccountUseCheck'])){
	//prnMsg('科目频度暂时不能使用!');
	$sql ="SELECT account,ROUND(SUM( amount),2) amount FROM gltrans WHERE periodno<= ".$_SESSION['period'] ." AND LEFT(account,4) IN ('1122','1221','2202','2401') GROUP BY  account";
	//var_dump($accarr);

	$result = DB_query($sql,$ErrMsg);
	$acc_arr=array();
	
	
	while ($row = DB_fetch_array($result)) {

		$acc_arr[$row['account']]=$row['amount'];
		$accountString=$row['account'].',';
	}
	if ($_POST['unittag']==0){
		$sql="SELECT accountcode, accountname, tag,crtdate, low, used FROM chartmaster WHERE LEFT(accountcode,4)  IN('1122','2202','1221','2241')  AND length(accountcode)>4 ORDER BY accountcode";
	}else{
			$sql="SELECT accountcode, accountname, tag,crtdate, low, used  FROM chartmaster WHERE LEFT(accountcode,4) IN('1122','2202','1221','2241')  AND length(accountcode)>4 AND tag=".explode('^',$_POST['unittag'])[1]." ORDER BY accountcode";

	}
	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$ErrMsg);
	
	$accarr=array();
	while ($row = DB_fetch_array($result)) {

		$accarr[$row['accountcode']]=array($row['accountname'],$row['tag'],$row['crtdate'],$row['low'],$row['used'],$acc_arr[$row['accountcode']],'',1);
	}
	$sql="SELECT account ,MAX(trandate) dt FROM gltrans WHERE periodno<=  ".$_SESSION['period'] ." AND LEFT(account,4) IN ('1122','1221','2202','2401') GROUP BY account";
	$result = DB_query($sql,$ErrMsg);
	while ($row = DB_fetch_array($result)) {
    if (!isset($accarr[$row['account']])){

			 
				$accarr[$row['account']][7]=0;


		}
		$accarr[$row['account']][6]=$row['dt'];
	}


	 
	if (count($accarr)>0 ){
	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">序号</th>		
			<th class="ascending">科目编码</th>
			<th class="ascending">科目名</th>	
			<th class="ascending">科目余额</th>
			<th class="ascending">最末发生日期</th>	
			<th class="ascending">创建日期</th>			
			<th class="ascending">状态</th>	
			<th class="ascending">���择</th>
						
		</tr>';
		$k=0; //row colour counter
			$r=0;
	foreach($accarr as $key=>$val){
				
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}	
		
			if($val[7]==0){
				$chd='disabled="disabled"';
			}else{
				$chd="checked";
			}
		printf(' <td>%s</td>
						<td>%s</td>
				<td>%s</td>	
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td>%s</td>
				<td> <input type="checkbox" name="chose'.$r.'[]" value="%s" '.$chd.'></td>
				
			</tr>',
			($r+1),
			$key,
			$val[0],
			$val[5],
			$val[6],
			$val[2],
			$val[3],
			$key.'^'. $val[1].'^'.$val[1]			
		);
	
	}	
		echo  '<input type="hidden" name="row" value="' . $r . '" />';
		echo '</table>'; 
		}else{
		prnMsg('往来科目名中没有近似的科目名','info');
	}

}
if(isset($_POST['AccountName'])){//不实用//客户名-科目名
	
	if ($_POST['unittag']==0){
		$sql="SELECT regid, custname, tag, account, regdate, custtype, flg FROM registername ";
	//	$sql="SELECT accountcode, accountname, tag,  used FROM chartmaster WHERE LEFT(accountcode,4)  IN('1122','2202','1221','2241')  AND length(accountcode)>4 ORDER BY accountcode";
	}else{
		$sql="SELECT regid, custname, tag, account, regdate, custtype, flg FROM registername WHERE  tag=".explode('^',$_POST['unittag'])[1]." ";
		//$sql="SELECT accountcode, accountname, tag,used FROM chartmaster WHERE LEFT(accountcode,4) IN('1122','2202','1221','2241')  AND length(accountcode)>4 AND tag=".explode('^',$_POST['unittag'])[1]." ORDER BY accountcode";

	}
	//
	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$ErrMsg);
	
	$accarr=array();
	while ($row = DB_fetch_array($result)) {
		
		$accarr[$row['regid']]=array($row['custname'],$row['account'],$row['tag'],$row['typ']);

	//	$accarr[$row['accountcode']]=array($row['accountname'],$row['tag'],$row['used']);
	}
		//"SELECT regid, custname, tag, sub account, regdate, typ, flg FROM custname_subject WHERE flg<>1";
	


		$sql ="SELECT regid, registerno, bankaccount, custname, sub , regdate, acctype , tag FROM register_account_sub";

	$acc_arr=array();
	$result = DB_query($sql,$ErrMsg);
	while ($row = DB_fetch_array($result)) {
	   $acc_arr[$row['regid']][]=array($row['custname'],$row['sub'],$row['registerno'], $row['bankaccount'],$row['tag'],$row['acctype']);
	}
	$jg=85;
	$ff=0;
	$rr=0;
	$f=0;
	$accary=array();
	foreach($accarr as $ACC_key=> $VAL){
		  $f=0;
		    
			foreach($acc_arr as $reg_key=>$val){
				if ($ACC_key==$val[2]){
				
					$accary[$ACC_key]=array(array($VAL[0],$reg_key,$val[0]),0);
					//unset($accarr[$ACC_key]);
					unset($acc_arr[$reg_key]);
					$f=1;
				}else{

					similar_text(substr($VAL[0],(1+strpos($VAL[0],'-'))),$val[0],$ff);
					if ($ff>$jg  ){
						unset($acc_arr[$reg_key]);
						$rr++;			
						$accary[$ACC_key]=array(array($VAL[0],$reg_key,$val[0]),$ff);
					  $f=1;
					} 
					$ff=0;
				}
				if ($f==0){
					$accary[$ACC_key]=array(array($VAL[0],0,''),1);
				}

		}
	}
	 //var_dump($accary['11220']);
	// echo count($accary);
	if (count($accarr)>0 ){
	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">序号</th>		
			<th class="ascending">客���编码</th>
			<th class="ascending">客户名称</th>	
			<th class="ascending">科目编码</th>
			<th class="ascending">注册码</th>
			<th class="ascending">账号</th>				
			
			<th class="ascending">单元分组</th>	
			<th class="ascending">选择</th>			
		</tr>';
		$k=0; //row colour counter
			$r=0;
	foreach($accarr as $key=>$val){
		for($i=0 ;$i<count($val[0]);$i++){
			if (!empty($val[$i][0])){
				
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}	
		
			if($val[1]==0){
				$chd='disabled="disabled"';
			}else{
				$chd="checked";
			}
		echo ' <td>'.($r+1).'</td>
						<td>'.$key.'</td>
				<td>'.$val[0].'</td>	
				<td>';
				$subarr=array();
				if ($val[1]!=''){
					echo $val[1].'<br>';
					$subarr[]=$val[1];
				}
			
				if (count($acc_arr[$key])>0){
				//	echo $acc_arr[$key][$i][1];
			//	}else{
					for($i=0;$i<count($acc_arr[$key]);$i++){
						if (!in_array($acc_arr[$key][$i][1],$subarr)){
                            $subarr[]=$acc_arr[$key][$i][1];
							echo $acc_arr[$key][$i][1].'</br>';
						}
					}
				}
				if (count($subarr)>1){
                     echo '客户设置多科目';
				}
				echo '</td>			
				<td>  ';
				if (count($acc_arr[$key])<=1){
					echo $acc_arr[$key][$i][2];
				}else{
					$regno=array();
					for($i=0;$i<count($acc_arr[$key]);$i++){
						if (!in_array($acc_arr[$key][$i][2],$regno)){
                            $regno[]=$acc_arr[$key][$i][2];
							echo $acc_arr[$key][$i][2].'</br>';
						}
					}
				}
				echo'</td>
				    <td>';
				for($i=0;$i<count($acc_arr[$key]);$i++){
                    echo $acc_arr[$key][$i][3].'</br>';
				}
				echo'</td>
			
						<td>'.$val[2].'</td>
						<td><input type="checkbox" name="chose'.$r.'[]" value="'.$val[1] .'^'.$key.'"  ></td>	
			</tr>';
		
			$r++;
		}
	}

		}
	
		
		echo  '<input type="hidden" name="row" value="' . $r . '" />';
		echo '</table>'; 
		}else{
		prnMsg('往来���目名���没有近似的科目名','info');
	}

}*/
if(isset($_POST['AccountUseModify'])){
	
	////if (empty($_POST['chose'])){
		prnMsg('科目频度改写暂时不能使用!');
	
	//}

     /*  foreach($_POST['chose'] as $val){
		   $accstr=explode('^',$val);
		   $sql="UPDATE chartmaster SET accountname='".$accstr[1]."',group_='".$accstr[2]."'  WHERE accountcode='".$accstr[0]."'";
		   $result = DB_query($sql);
		   if ($result){
			prnMsg($accstr[0].$accstr[1].'更新成功！','info');
		   }
	   }
	 
	}		*/
	//var_dump($_POST['chose']);
}
echo'</form><br/>';
include('includes/footer.php');
function UpdateAccount($toacc,$delacc){
	//$toacc=explode('^',$acc_acc)[0];
	//$delacc=explode('^',$acc_acc)[1];
	$result = DB_Txn_Begin();
	echo '<br />' . _('Adding the new chartmaster record');
	$sql = "DELETE FROM chartmaster
			WHERE accountcode='" . $delacc . "'";		

	$DbgMsg = _('The SQL statement that failed was');
	$ErrMsg =_('The SQL to insert the new chartmaster record failed');
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');
			$sql="DELETE FROM glaccountusers WHERE accountcode='" . $delacc . "'";
	$result = DB_query($sql,$ErrMsg,$DbgMsg,true);
	echo ' ... ' . _('completed');

	DB_IgnoreForeignKeys();

	ChangeFieldInTable("bankaccounts", "accountcode", $delacc, $toacc, $db);
	ChangeFieldInTable("bankaccountusers", "accountcode", $delacc, $toacc, $db);
  ChangeFieldInTable("banktrans", "bankact", $delacc, $toacc, $db);
	//ChangeFieldInTable("chartdetails", "accountcode", $delacc, $toacc, $db);
	ChangeFieldInTable("registername", "account", $delacc, $toacc, $db);
	ChangeFieldInTable("registeraccount", "subject", $delacc, $toacc, $db);
	ChangeFieldInTable("accountsubject", "subject", $delacc, $toacc, $db);
	ChangeFieldInTable("cogsglpostings", "glcode", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "debtorsact", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "pytdiscountact", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "creditorsact", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "payrollact", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "grnact", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "exchangediffact", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "purchasesexchangediffact", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "retainedearnings", $delacc, $toacc, $db);
	ChangeFieldInTable("companies", "freightact", $delacc, $toacc, $db);
	ChangeFieldInTable("fixedassetcategories", "costact", $delacc, $toacc, $db);
	ChangeFieldInTable("fixedassetcategories", "depnact", $delacc, $toacc, $db);
	ChangeFieldInTable("fixedassetcategories", "disposalact", $delacc, $toacc, $db);
	ChangeFieldInTable("fixedassetcategories", "accumdepnact", $delacc, $toacc, $db);
	ChangeFieldInTable("glaccountusers", "accountcode", $delacc, $toacc, $db);	
	ChangeFieldInTable("gltrans", "account", $delacc, $toacc, $db);
	ChangeFieldInTable("lastcostrollup", "stockact", $delacc, $toacc, $db);
	ChangeFieldInTable("lastcostrollup", "adjglact", $delacc, $toacc, $db);
	ChangeFieldInTable("locations", "glaccountcode", $delacc, $toacc, $db);// Location's ledger account.

	ChangeFieldInTable("pcexpenses", "glaccount", $delacc, $toacc, $db);
	ChangeFieldInTable("pctabs", "glaccountassignment", $delacc, $toacc, $db);
	ChangeFieldInTable("pctabs", "glaccountpcash", $delacc, $toacc, $db);
	ChangeFieldInTable("purchorderdetails", "glcode", $delacc, $toacc, $db);
	ChangeFieldInTable("salesglpostings", "discountglcode", $delacc, $toacc, $db);
	ChangeFieldInTable("salesglpostings", "salesglcode", $delacc, $toacc, $db);
	ChangeFieldInTable("stockcategory", "stockact", $delacc, $toacc, $db);
	ChangeFieldInTable("stockcategory", "adjglact", $delacc, $toacc, $db);
	ChangeFieldInTable("stockcategory", "issueglact", $delacc, $toacc, $db);
	ChangeFieldInTable("stockcategory", "purchpricecode", $delacc, $toacc, $db);
	ChangeFieldInTable("stockcategory", "materialuseagevarac", $delacc, $toacc, $db);
	ChangeFieldInTable("stockcategory", "wipact", $delacc, $toacc, $db);

	ChangeFieldInTable("taxauthorities", "taxglcode", $delacc, $toacc, $db);
	ChangeFieldInTable("taxauthorities", "purchtaxglaccount", $delacc, $toacc, $db);
	ChangeFieldInTable("taxauthorities", "bankacctype", $delacc, $toacc, $db);
	ChangeFieldInTable("workcentres", "overheadrecoveryact", $delacc, $toacc, $db);

	DB_ReinstateForeignKeys();

	$result = DB_Txn_Commit();
	echo '<p>' . _('GL account Code') . ': ' . $delacc . ' ' . _('was successfully changed to') . ' : ' . $toacc;
  //only do the stuff above if  $InputError==0
}
/*
function ComparisonName(&$accarr,$accname,$acc){	
		//
		$jg=60;
		$ff=0;
		$retacc='';
		$retkey='';
		foreach($accarr as $key=>$val){
			if($val[1]!=''){
				if($val[1]==$acc){
					unset($accarr[$key]);
					$retacc=$val[2];
					$retkey=$key;
					break;
				}
			}
		}
		if ($retacc==''){
			foreach($accarr as $key=>$val){
			similar_text($val[2],substr($accname,(strpos($accname,'-')+1)),$ff);
				if ($ff>$jg  ){
				
					$retacc=$val[2];
					$retkey=$key;
				} 
				$ff=0;

			}
		}
	return $retkey.':'.$retacc;
}*/

?>