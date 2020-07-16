
<?php
/* $Id: CreateSubject.php  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-05-10 22:03:28 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-03-31 07:17:54
 * 
 */
include('includes/session.php');
$Title = '科目生成';
/* Manual links before header.php */
$ViewTopic= 'MyTools';// Filename in ManualContents.php's TOC.
$BookMark = 'CreateSubject';// Anchor's id in the manual's html document.
include('includes/GLAccountFunction.php');
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
		var box=document.getElementsByTagName("input")   //此处选中了所有的input,包括全按钮本身，在后面的操作中需要注意
		for (i = 0; i < box.length-1; i++) {
		box[i].onclick = onclike
		}
	}
	function AccRegNameVal(){
		return("已经生成的科目,根据名称相识度查询！");
	}
	function AccCustVal(){
		return("根据科目名称相近自动关联税号/账号,人工确认！");
	}
	function SearchVal(){
		return("根据客户类别添加科目编码，确认保存！");
	}
	//function CustRegVal() {
		//	return("使用金税盘上传的<客户名自动保存.txt>文件更新客户名税号");
	//	}	     
</script>';
if (!isset($_POST['acctype'])){
	$_POST['acctype']=1;
}

if (!isset($_POST['UnitsTag'])){
	$_POST['UnitsTag']=0;
}

	if (!isset( $_POST['ComPrv'][0])&&(!isset($_POST['ComPrv'][1]))){
		$_POST['ComPrv'][0]=1;	
		//$_POST['ComPrv'][1]=2;
	}
	if (!isset( $_POST['AccSet'][0])&&(!isset($_POST['AccSet'][1]))){
		$_POST['AccSet'][0]=1;	
		//$_POST['AccSet'][1]=2;
	}
	if (!isset( $_POST['OrderByType'][0])&&(!isset($_POST['OrderByType'][1]))){
		$_POST['OrderByType'][1]=2;	
		//$_POST['OrderByType'][1]=2;
	}
	if (!isset(	$_POST['SelectDate'])){
		$_POST['SelectDate']=date('Y-01-01',strtotime($_SESSION['lastdate']));
	}
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		_('General Ledger Accounts') . '" />' . ' ' . $Title . '</p>';
echo '<form method="post" id="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
<div>
	  <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	  <input type="hidden" name="SelectDate" value="' . $_POST['SelectDate'] . '" />';
echo '<div class="page_help_text">
		科目设置功能简介<br>		
		使用税控盘导出的-客户编码.txt文件、根据银行流水及导入发票更新客户名称、账号<br>
		</div><br>'; 
	echo '<table cellpadding="3" class="selection">';
	echo '<tr>
			<td>单元分组</td>
			<td >';
			SelectUnitsTag();
			echo'</td>';
	echo'<td></td>
		<td ></td>
		  </tr>';
	echo'<tr>
	         <td >查询选择</td>
			 <td>
				<input type="checkbox" name="ComPrv[0]" value="1" '. ($_POST['ComPrv'][0]==1 ?"checked":"").'/>单位类
				<input type="checkbox" name="ComPrv[1]" value="2" '. ($_POST['ComPrv'][1]==2 ?"checked":"").' />个人类
	         </td>
		  <td >科目类别</td>
		  <td><input type="checkbox" name="AccSet[0]" value="1" '. ($_POST['AccSet'][0]==1 ?"checked":"").'/>未设科目
			  <input type="checkbox" name="AccSet[1]" value="2" '. ($_POST['AccSet'][1]==2 ?"checked":"").' />已设科目
			  </td>
	      </tr>';
	echo '<tr>
			<td >查询名称:</td>
			<td >
			  <input type="text" maxlength="20" name="Keywords" title="', _('If there is an entry in this field then customers with the text entered in their name will be returned') , '"  size="20" ',
				( isset($_POST['Keywords']) ? 'value="' . $_POST['Keywords'] . '" ' : '' ), '/></td>';
				$startdt=date('Y-m-01',strtotime($_SESSION['lastdate'])-31536000);
				$enddt=date('Y-m-d',strtotime($_SESSION['lastdate']));
	//prnMsg($enddt.$startdt);
	echo ' 
			<td colspan="2">账号:<input  type="text"   name="BankAcc"  size="20" maxlength="30"  pattern="[\w]*" title="" value="' . $_POST['BankAcc'] . '" /></td>
			</tr>
			<tr>
			<td>注册码:</td>
			<td><input     type="text"  name="RegisterNo" maxlength="30"  size="20"  pattern="[\w]*"value="' . $_POST['RegisterNo'] . '" /></td>
			</tr>';
	echo'<tr>
			<td>选择日期:</td>
			<td>
				<input type="date"   alt="" min="'.$startdt.'" max="'.$enddt.'"  name="SelectDate" maxlength="10" size="10" value="' . $_POST['SelectDate'] . '" /></td>
	        <td>排序选择</td>
			<td >
			    <input type="checkbox" name="OrderByType[0]" value="1" '. (in_array(1,$_POST['OrderByType']) ?"checked":"").'/>名称排序
				<input type="checkbox" name="OrderByType[1]" value="2" '. (in_array(2,$_POST['OrderByType']) ?"checked":"").' />编码排序
				</td>
			</tr>
		</table><br />';
	  
echo'<br />
		<div class="centre">		
			<input type="submit" name="Search" value="查询" onMouseOver="this.title=AccRegNameVal()"/>
			<input type="submit" name="refresh" value="刷新" />';
   			if(isset($_POST['Search']) ){
				echo'<br><input type="submit" name="AddAccount" value="科目添加" />';
			}				
	echo'</div>';
	/*
	$SQL="SELECT `regid`, `custname`,  `account` FROM `registername` WHERE 1";
	$Result = DB_query($SQL);
	while ($row=DB_fetch_array($Result)){
		$RegCustomer[$row['regid']]=array("custname"=>$row['custname'],'account'=>$row['account'],'flag'=>0);
	}*/

	$SQL="SELECT `accountcode`, `accountname`, `currcode` FROM `chartmaster` ";
	$result = DB_query($SQL);
	//$AccountNameArr=array();
	while ($row=DB_fetch_array($result)){
        $AccountNameArr[$row['accountcode']]=array($row['accountname'],$row['currcode']); 
	}
	$SQTag=' ';
	if ($_POST['UnitsTag']!=0){
        $SQTag="  AND a.tag= ".$_POST['UnitsTag']." ";
	}
	$SQL="SELECT `regid`, `registerno`,bankaccount, `custname`,'' accountname, a.`tag`, `sub`,regdate, `acctype` 
	        FROM `register_account_sub` a 			  
			WHERE  1 ". $SQTag;
			
if(!(($_POST['Keywords'] == '') AND ($_POST['BankAcc'] == '') AND ($_POST['RegisterNo'] == '') )) {
		$SearchKeywords = mb_strtoupper(trim(str_replace(' ', '%', $_POST['Keywords'])));
		$_POST['BankAcc'] = mb_strtoupper(trim($_POST['BankAcc']));
		$_POST['RegisterNo'] = trim($_POST['RegisterNo']);
		
	if ($_POST['Keywords']!='' ){
	
			$SQL .= "	AND custname " . LIKE . " '%" . $SearchKeywords . "%'";
		
	}
    
	if($_POST['RegisterNo']!=''){
		
			$SQL .= "	AND registerno " . LIKE . " '%" . $_POST['RegisterNo'] . "%'";
	
	}
	if($_POST['BankAcc']!=''){
		$SQBank=' ';
		if ($_POST['UnitsTag']!=0){
			$SQBank="  AND tag= ".$_POST['UnitsTag']." ";
		}
		
			$SQL .= "	AND regid IN (SELECT regid FROM `accountsubject` WHERE  bankaccount  " . LIKE . " '%".$_POST['BankAcc']."%'  ".$SQBank." )";
	
	}
}
   if(isset($_POST['SelectDate'])){
	
		$SQL .= "	AND regdate >='" . $_POST['SelectDate'] . "'";
	
	}
	if (!(($_POST['ComPrv'][0]==1)&& ($_POST['ComPrv'][1]==2))){
		if ($_POST['ComPrv'][1]==2){
			
				$SQL.="AND LENGTH(custname)<15 ";
		
		}
		if($_POST['ComPrv'][0]==1){
			
			$SQL.="AND LENGTH(custname)>=15 ";
		
		}
	}
	if(!(($_POST['AccSet'][0]==1)&&($_POST['AccSet'][1]==2))){
		if($_POST['AccSet'][0]==1){
			
			$SQL.="AND  (LENGTH(sub) < 5 OR sub is null)";
		
		}
		if($_POST['AccSet'][1]==2){
			
			$SQL.="AND LENGTH(sub)>5 ";
		
		}
	}
	if (in_array(2,$_POST['OrderByType'])){
		$SQL.=" Order By regid";
	}
    //echo '-='.$SQL;
	$result = DB_query($SQL,$ErrMsg);
	$SQBank=' ';
	if ($_POST['UnitsTag']!=0){
		$SQBank="  AND tag= ".$_POST['UnitsTag']." ";
	}
	$sql="SELECT `bankaccount`,bankname,regid 
	        FROM `accountsubject` 
			WHERE  1" .$SQBank;
	$Result = DB_query($sql,$ErrMsg);
	//$BankRegArr=array();
	while ($row=DB_fetch_array($Result)){
        $BankRegArr[$row['regid']][]=$row['bankaccount'].':'.$row['bankname']; 
	}

	if(isset($_POST['Search'])||isset($_POST['AddAccount'])){
		$selectarr=array(0=>'待定',-1=>'应收账款-零售',1=>'应收账款',-2=>'应付账款-零购',2=>'应付账款',5=>'应付职工薪酬',4=>'其他应收款',8=>'其他应付款');
		if (strlen($_POST['acctype'])==1){
			prnMsg('行显示为浅黄绿色,该用户没有生成会计科目！','info');
		}
		echo '<br /><table class="selection" style="width: 700px;">';
	
		echo '<tr>
				<th style="width: 10px;">序号</th>
				<th style="width: 50px;">编码</th>
				<th  style="width: 350px;" >客户名称</th>
				<th style="width: 50px;">科目编码</th>		
				<th style="width: 100px;">创建日期</th>			
				<th style="width: 50px;">科目类别</th>
				<th style="width: 20px;"></th>
				</tr>'; 
			$r=1; 
			//判断客户类别
			$acctyp=0;
			if ($row['acctype']==0 && $row['sub']==''){
					$ActOption=array(1=>'应收账款',2=>'应付账款',4=>'其他应收款',8=>'其他应付款');
					$acctyp=-1;				
			}elseif($row['acctype']==1 && $row['sub']==''){
				$acctyp=1;
				$ActOption=array(1=>'应收账款');
			}elseif(strlen($row['custname'])<=15 && $row['sub']==''){
				$acctyp=4;
				$ActOption=array(4=>'其���应收款',8=>'其他应付款');
			}elseif($row['acctype']==2 && $row['sub']==''){
				$acctyp=2;
				$ActOption=array(2=>'应付账款');
			}
			$RowIndex =0;
			//$CustTyp=array(0=>'固定科目',1=>'账号科目',2=>'税号科目');//,3=>'无科目',4=>'科目异常');
			while ($row = DB_fetch_array($result)) {
				//prnMsg($RowIndex.'='.$row['regid'].$row['custname']);
				$sub='';
				$subname='';
				$acctype=$row['acctype'];
				if(!empty($row['sub'])){
					$sub=$row['sub'];
					if (!empty($AccountNameArr[$row['sub']])){
						$subname=$AccountNameArr[$row['sub']][0];
					}
				}
				if($row['sub']!=''||$row['acctype']==-1){
					if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}
				}else{
					echo '<tr style="background: #efc;">'	;
				}
				$bkregno=0;
				if (!empty($row['bankaccount'])){
                    $bkregno+=1;
				}
				if (!empty($row['registerno'])){
                    $bkregno+=2;
				}
				$regstr= $row['custname'].'^'.$row['regid'].'^'.$row['tag'].'^'.$row['acctype'].'^1^'.$row['bankaccount'].'^'.$row['registerno'];
				if (isset($BankRegArr[$row['regid']])){
					$bkac=implode('&#13;',$BankRegArr[$row['regid']]);
				
					//echo '<td><a href="#" title="'.$bkac.'" target="_blank" >'.$ah.'</a></td>';
				}
			
			echo  '<td   >'.($RowIndex+1).'</td>	
					<td>'.$row['regid'].'</td>
					<td  title="账号:'.$bkac.'注册码:'.$row['registerno'].'" >'.$row['custname'].'</td>
					<td  title="'.$subname.'">'.$sub.'</td>';
					/*
					echo'<td><select name="CustTyp'.$RowIndex.'">';
					foreach($CustTyp as $key=>$value){
							if ($acctype==$key){
								echo '<option selected="selected" value="'.$key.'">'.$value.'</option>'; ;
							}else {
								if ($key==0 ||$bkregno==3){
									echo '<option value ="'.$key.'">'.$value.'</option>';
								}elseif ($key==1 &&$bkregno==1){
									echo '<option value ="'.$key.'">'.$value.'</option>';;
								}elseif ($key==2 &&$bkregno==2){
									echo '<option value ="'.$key.'">'.$value.'</option>';
								}
							}
					}				
			   echo'</select></td>';*/
				//	if ($_POST['ShowAccReg'][0]==1){
					/*
					if (isset($BankRegArr[$row['regid']])){
						$bkac=implode('&#13;',$BankRegArr[$row['regid']]);
						if (count($BankRegArr[$row['regid']])>1){
							   $ah='......';
							   
						}else{
							$ah='...';
						}
						echo '<td><a href="#" title="'.$bkac.'" target="_blank" >'.$ah.'</a></td>';
					}else{
						echo '<td></td>';
					}
							
					if ($row['registerno']!=''){
					echo '<td><a href="#" title="'.$row['registerno'].'" target="_blank" >...</a></td>';
					}else{
						echo '<td></td>';
					}
				*/
					echo'<td>'.date("Y-m-d",strtotime($row['regdate'])).'</td>';
					if ($acctyp<0 && $row['sub']!=''){
						echo '<td></td>';
					}else{
						echo '<td ><select name="SubType'.$RowIndex.'">';
						$sd=' selected="selected" ';
						//根据客户名选择对应总账科目
						foreach ($ActOption as $key=>$val) {
							echo'<option value="'.$key.'"';
							if (mb_strlen($row['custname'])<6 && (mb_strlen($row['custname'])>1 && $key==1) ){
								    echo $sd.'>'.$val.'</option>';
									$sd='';
							}elseif($key==2){
								echo $sd.'>'.$val.'</option>';
								$sd='';
							}else{
								echo'>'.$val.'</option>';
							}
						}
					}
					echo'</select></td>';
				if ($row['acctype']==-1){
					echo'<td ><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'" disabled="disabled" ></td>';	
				}else{
					echo'<td ><input type="checkbox" name="chkbx[]" value="'.$RowIndex.'" '.($row['sub']!=''?'disabled="disabled"':($row['sub']=='' && $acctyp！=0?'':'checked')).' ></td>';	
				}
				echo'<input type="hidden" name="regstr'.$RowIndex.'" value="' . $regstr . '" />	
				</tr>';	
				$RowIndex = $RowIndex + 1;
			}
		echo '</table>';
	
		$SQL="SELECT accountcode 
		        FROM chartmaster
				WHERE accountcode>='".$SubNoRule[$acctyp][0]."'
				 AND accountcode <='".$SubNoRule[$acctyp][1]."'
				  ORDER BY accountcode";
		$Result=DB_query($SQL);
		$subarr=array();
		while ($row=DB_fetch_array($Result)){
			//echo substr($row['accountcode'],4).'<br>';
			$subarr[substr($row['accountcode'],4)]=$row['accountcode'];
		}	//
		
		if (!isset($SubNoRule)){
			$SQL="SELECT  confname,
			              confvalue 
						FROM myconfig 
						WHERE conftype=1
						OR  confname  IN ('SetAccountName','AccountSize','AccountNameSize','AutoSubject')";

			$Result = DB_query($SQL);
			while ($row = DB_fetch_array($Result)) {		
					$SubNoRule[$row['confname']]=explode(',',$row['confvalue']);	
			}
		}
		//var_dump($SubNoRule);
		if(isset($_POST['AddAccount'])){
			$subcode=array(1=>'1122',2=>'2202',4=>'1221',8=>'2241');
			$sub='';
			$regid=0;
			$countrow=0;
			$mg='';
			if (empty($_POST['chkbx'])){
					prnMsg('你没有选择客户！','info');
			}else{
				$r=0;
				foreach($_POST['chkbx'] as $val){
		             $r++;		
							
				$act=AddCustomer($_POST['regstr'.$val],$subcode[$_POST['SubType'.$val]],$SubNoRule);
			   // prnMsg($act[0]);
				
				}//foreach	
			}
   		}	    
	}elseif(isset($_POST['refresh'])){
		$_SESSION['mdhmid']=$_SESSION['FormID'] ;
		//unset($flgid);
	}	

echo'</div></form>';
include('includes/footer.php');
/*
function Addcustname(){
}*/
function AccountQuery($custname,$tag,$typ)  {
	//用名称查询科目 regid ....
	if ($typ==2){
		$sql="SELECT account,regid, registerno,  acctype FROM register WHERE flg=0 AND  custname='".$custname."' ";
	}elseif($typ==1){
		$sql="SELECT account,accountnumber,  regid, acctype  FROM customeraccount WHERE flg=0 AND  custname='".$custname."'";
	}
	if ($tag!=0){
		$sql.=" AND tag='".$tag."'";
	}	
	$result = DB_query($sql);
	$row=DB_fetch_row($result);
	return $row[0];
}
	

?>