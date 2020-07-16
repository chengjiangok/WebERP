<?php
/* $Id: GLSalaryExtraction.php$*/
/* SalaryExt for employees  */

include('includes/session.php');
$Title ='工资提取结转';
$ViewTopic = 'HumanResource';
$BookMark = 'HumanResource';
include('includes/SQL_CommonFunctions.inc');
include('includes/header.php');
/**设计要点
 *  读取1-当��期���  使用confirmerp 工资是否提取
 *  读取凭证中提取��额
 *  使用config的ERP  1位折旧2位���资模式标记0���易和1精��
 *  使用confirmerp  的确认数
 */
echo'<script type="text/javascript">
	function OnSalaryAmo(S,R,C) {

		S.value=parseFloat(S.value).toFixed(2);
		var actstr=document.getElementById("CostAct").value.toString();
		var	actar=actstr.split(",");	
		var  k= Number(C.toString().length)+ Number(9);	
		var  n=S.name.substring(k);			
		var amototal=0;
		var costamototal=0;
		var salaryamototal=0;
		 
		for(var i=0;i<R;i++){	
		    if (document.getElementById(C+"SalaryAmo"+i).value!=""){
							
				amototal=parseFloat(amototal)+parseFloat(document.getElementById(C+"SalaryAmo"+i).value);
			}
		}    
	
		document.getElementById(C+"Edit"+n).value=2;
		document.getElementById("CostAmo"+C).value=amototal.toFixed(2);
		
		for (var f=0;f<actar.length ;f++ ) {
			if (document.getElementById("CostAmo"+actar[f]).value!=""){
				costamototal=parseFloat(costamototal)+parseFloat(document.getElementById("CostAmo"+actar[f]).value);
			}
			for(var i=0;i<R;i++){	
				if (document.getElementById(actar[f]+"SalaryAmo"+i).value!=""){
								
					salaryamototal=parseFloat(salaryamototal)+parseFloat(document.getElementById(actar[f]+"SalaryAmo"+i).value);
				}
			}
		}
	
		document.getElementById("CostTotal").value=costamototal.toFixed(2);
		document.getElementById("SalaryTotal").value=salaryamototal.toFixed(2);	
	}
	function OnSalaryAmo1(S,R,C) {	
		S.value=parseFloat(S.value).toFixed(2);
		var amototal=0;
		for(var i=0;i<R;i++){	
		    if (document.getElementById(i+"SalaryAmo"+C).value!=""){
							
				amototal=parseFloat(amototal)+parseFloat(document.getElementById(i+"SalaryAmo"+C).value);
			}
		}    
		document.getElementById("CostAmo"+C).value=amototal.toFixed(2);		
	}
	function OnRemark(S, R) {
		//���版更新自己20190930
		//console.log("=="+rmk0);
		console.log(S.value);
		//document.getElementById("ToSelectAct"+R).value=jsonStr;	
		//var check = document.getElementById("chkbx"+R);
		//check.checked=true;	
		
	}
</script>';
if (!isset($_POST['SelectPrd'])){
	$_POST['SelectPrd']=$_SESSION['period'];
	
}
if (isset($_SESSION['ERP'])){
	$SalartMode=explode(",",$_SESSION['ERP'])[1];
}else{
	$SalartMode=0;
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
	  echo '<div>';
		/*$SQL="SELECT `periodno`, `lastdate_in_period` 
				FROM `periods` 
				WHERE periodno>=".$_SESSION['janr']." AND periodno<=".$_SESSION['period']."  AND  periodno NOT IN (SELECT period   FROM ` confirmerp`   WHERE period>=".$_SESSION['janr']." AND period<=".($_SESSION['period'])." AND itemtype='2211')";*/
	    $SQL="SELECT DISTINCT  period 
		             FROM ` confirmerp`
		             WHERE period>=".$_SESSION['janr']." AND period<=".($_SESSION['period'])."  AND itemtype='2211'"	;
	 $Result = DB_query($SQL);
	 while ($row=DB_fetch_array($Result)){
		 $Period[]=$row['period'];
	 }
	 /*
	 if (DB_num_rows($Result)==0){
		 prnMsg($_SESSION['lastdate'].'���前的工资已经提取！','info');
		 include('includes/footer.php');
		 exit;
	 } */
	 
	  echo '<table class="selection">';
	  echo '<tr>
			<td width="100">选择会计期间</td>
			<td width="150"><select name="SelectPrd">';	
		$PeriodStr='';
	
		for ($i=$_SESSION['janr'];$i<=$_SESSION['period'];$i++){	
		
            
				if($i == $_POST['SelectPrd']){

					echo '<option selected="selected" value="' . $i . '">' . MonthAndYearFromSQLDate(PeriodGetDate($i) ). '</option>';
				} else {
					echo '<option value="' . $i . '">' . MonthAndYearFromSQLDate(PeriodGetDate($i) ). '</option>';
				}
		}
			echo '</select></td>';
	  echo'<tr>
		    <td>单元分组</td>
			<td >';
			SelectUnitsTag(2);
	
		echo'</td>
		</tr>';
		echo '</table>
		<br />';
		$SelectPrd=$_POST['SelectPrd'];//1+$_SESSION['CompanyRecord'][1]['lastsettleperiod'];	
		$_POST['SelectDate']= PeriodGetDate($_POST['SelectPrd']);
	
     
        //读取本年已经提取
		$SQL="SELECT  periodno,
		              account,
					  gltrans.tag,
					  accountname,
					  SUM(TOAMOUNT(amount,-1,0,0,1,flg)) detotal,
					  SUM(toamount(amount,-1,0,0,-1,flg)) crtotal 
					FROM`gltrans` 
					LEFT JOIN chartmaster ON gltrans.account=accountcode
				WHERE	account LIKE '2211%' 
						 AND((flg = 1 AND amount < 0) OR(flg = -1 AND amount > 0))  
						 
						 AND  periodno>=".$_SESSION['janr']." AND periodno<=".$_POST['SelectPrd']."
					GROUP BY periodno,account,  accountname,	gltrans.tag	
					ORDER BY gltrans.tag, periodno,account";
			
		$Result = DB_query($SQL);
	   // echo $SQL;
	if (DB_num_rows($Result)>0){
			echo '<table class="selection">';
			echo'<tr>
			<th colspan="5"   class="centre">本年工资福利提取</th>';
							
		echo'</tr>';
			echo'<tr>
					<th >月份</th>
							
					<th >科目编码/名  </th>
					<th >借方金额</th> 
					<th >贷方金额</th>
					<th >备注</th>
				</tr>';
			
			$k = 1;// Row colour counter.
			$RowIndex=1;
			$EmpCrAmo=0;
			$EmpDeAmo=0;
			$SalaryExt=[];
			while ($row = DB_fetch_array($Result)) {
				$ActName[$row['account']]=$row['accountname'];
					$RowIndex++;				
				if ($row['periodno']<$_POST['SelectPrd']-1){
					$SalaryExt[0][$row['account']]+=$row['crtotal'];
					$EmpDeAmo+=$row['detotal'];
					$EmpCrAmo+=$row['crtotal'];
					if ($_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']==$row['periodno']){
						$LastData[$row['account']]=array($row['accountname'],$row['detotal'],$row['crtotal']);
				    }
				}elseif ($_POST['SelectPrd']==$row['periodno']){
					$SalaryExt[$_POST['SelectPrd']][$row['account']]+=$row['crtotal'];
			
				}elseif (($_POST['SelectPrd']-1)==$row['periodno']){
					$SalaryExt[$_POST['SelectPrd']-1][$row['account']]+=$row['crtotal'];
				
				}
			
				
			}// END foreach($Result as $row).
			//print_r($SalaryExt);
			 $R=1;
		    foreach ($SalaryExt as $key=>$val){
				$TotalSalary=0;
				foreach($val as $ky=>$vl){
					echo'<tr>
						<td  class="text">'.$R.'</td>
					
						<td  class="text">'.$ky.$ActName[$ky].'</td>
						<td class="text"></td>
						<td class="text">'. number_format($vl,2). '</td>
						<td class="text"></td>
					</tr>';
					$R++;
					$TotalSalary+=$vl;
				}
				if ($key==0){
						echo'<tr>
						<th  class="text"></th>
						
						<th  class="text">本期以前合计</th>
						<th class="text">'. number_format($EmpDeAmo,2). '</th>
						<th class="text">'. number_format($EmpCrAmo,2). '</th>	
						<th  class="text"></th>		
					</tr>';
					
				}else	if ($key==$_POST['SelectPrd']){
						echo'<tr>
						<th  class="text"></th>
					
						<th  class="text">当前期合计</th>
						<th class="text"></th>
						<th class="text">'. number_format($TotalSalary,2). '</th>	
						<th  class="text"></th>		
					</tr>';
					$R++;
				}
			}
			if(($TotalSalary+$EmpCrAmo)!=0){
					echo'<tr>
					<th  class="text"></th>
				
					<th  class="text">累计</th>
					<th class="text"></th>
					<th class="text">'. number_format($TotalSalary+$EmpCrAmo,2). '</th>	
					<th  class="text"></th>		
				</tr>';
			}
			echo '</table>
				<br />';
	}else{
		prnMsg("本年度你没有提取过工资及福利费！",'info');
	}
		//$SettleTabAct=array("0"=>'1602',"1"=>'2211',"2"=>'2221',"3"=>"1403","4"=>"1405","5"=>'5001',"6"=>'5101',"7"=>'6001');
		/*
	    $SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);
		if ($SettleTab[2]==1){
			prnMsg(substr($_POST['SelectDate'],0,7).'工资已经提取，不能重复提取!','info');
			include('includes/footer.php');
			exit;	
		}*/
if(isset($_POST['SalaryExt'])||isset($_POST['Updateing'])) {
	   /*1.	有上传税务申报工资使用税务工资 
		 2.    年有上月的工资,使���
		 3.手动填入
		*/
	if ($SalartMode==1){  //erp模式

		//读取提取科目
		$SQL="SELECT `itemtype`,confname, `confvalue`,accountname
				FROM `hrconfig`
				LEFT JOIN chartmaster ON confname=accountcode
				WHERE  `conftype`=10";
				//$SalaryAct=array();
				$Result = DB_query($SQL);
				while ($row = DB_fetch_array($Result)) {
					$SalaryAct[]=array($row['confname'],$row['accountname'],$row['confvalue']);
				}
		if (!isset($DepartAct)||count($DepartAct)==0){

			$SQL="SELECT `departmentid`, `description`, `labouract`, `salaryact`,accountname  
					FROM `departments` 
					LEFT JOIN chartmaster ON accountcode=labouract  WHERE 1";
			$DepartAct=array();
			$Result = DB_query($SQL);
			while ($row = DB_fetch_array($Result)) {
				$DepartAct[$row['labouract']]=array($row['accountname'],$row['salaryact'],0,$row['description']);
			}
		}
		$ActStr=implode(',',array_keys($DepartAct));
		/*
		$SQL="SELECT `confname`, `itemtype`, `confvalue` 
				FROM `hrconfig` 
				WHERE conftype=5 AND todate<'".$_POST['SelectDate']."' OR (conftype=1 AND itemtype=1)";
		$PensionArr=array();
		$Result = DB_query($SQL);
		while ($row = DB_fetch_array($Result)) {
			$PensionArr[$row['itemtype']]=array($row['confname'],$row['confvalue']);
		}*/
		$SQL=	"SELECT b.employee_department, 	                   
							`labouract`,					
							`salaryact`,
							SUM(`income`) income, 
							SUM(`taxeswithheld`) taxeswithheld, 
							a.`flg` 
					FROM `hrtaxpayslips` a 
					LEFT JOIN hremployees b ON a.employee_id=b.employee_id
					LEFT JOIN departments c ON b.employee_department= c.departmentid
						WHERE	a.tag='".$_POST['UnitsTag']."' AND  period=".$SelectPrd. "
						GROUP BY b.employee_department,`labouract`,`salaryact`";
						$Result = DB_query($SQL);
			if (DB_num_rows($Result)>0){
				while ($row = DB_fetch_array($Result)) {
					$DepartAct[$row['labouract']][2]=$DepartAct[$row['labouract']][2]+$row['income'];
				}
			}else{
				//没有导入税务工资读取上月工资
				$SalaryValue=array_sum(array_column($SalaryAct,2));
				
				foreach($DepartAct as $key=>$row){
					if (isset($LastData[$key])){
						
						$DepartAct[$key][2]=Round($LastData[$key][1]/$SalaryValue,2);
					}
				}
			}
		//	var_dump($DepartAct);
		//	var_dump($SalaryAct);
	}else{
		//简易模式读取模板
		if (isset($SalaryExt[$_POST['SelectPrd']])){
			prnMsg('你选择的会计期间的工资已经提��！','info');
			include('includes/footer.php');
			exit;
		}
		//读取模板  模板不存读取上月实际提取凭证，保存���模板
		$SQL="SELECT `confname`, `tag`, `itemtype`, `conftype`, `confvalue`, `notes` 
				FROM `myconfig` WHERE conftype=110 AND  confname  LIKE '2211%'";
		$Result = DB_query($SQL);
		//$ROW=DB_fetch_assoc($Result);
		if (DB_num_rows($Result)>0){
		    
			//读取模板���目
			$SalartMode=2;
			while ($row=DB_fetch_array($Result)){
				$SalaryAct[$row['confname']]=json_decode($row['confvalue'],true);
			}
		}else{
			//读取凭证中前月的提取
			$SQL="SELECT transno,
			            account,
						accountname,
						amount ,
						narrative
			        FROM `gltrans` 
					LEFT JOIN chartmaster ON  account=accountcode
					WHERE periodno=".($_POST['SelectPrd']-1)." 
					   AND transno IN (SELECT transno FROM gltrans WHERE account LIKE '2211%' AND periodno=".($_POST['SelectPrd']-1)." AND amount<0) 
					   ORDER BY transno";
			$Result = DB_query($SQL);
			if (DB_num_rows($Result)>0){
				while ($row=DB_fetch_array($Result)){
					
					$SalaryAct[$row['transno']][]=array($row['account'],$row['amount'],$row['narrative'],$row['accountname']);
				}
				$SalartMode=0;
			}else{
				prnMsg('没有提取模板��你选择的会计���间的前月工资也没有提取,请直接在会计凭证录入中提取!!','info');
				$SalartMode=-1;
				include('includes/footer.php');
				exit;
			}


		}
		

	}
	//print_r($SalaryAct);

	if ($SalartMode==1){
		echo '<table class="selection">';
		echo'<tr>
				 <th >序号</th>				
				 <th >费用科目</th>
				 <th >借方金额</th>				
				 <th >工资科目</th>
				 <th >贷方金额</th>	
				 <th >摘    要</th>					
			 </tr>	<input type="hidden"  name="CostAct" id="CostAct"   value="'.$ActStr.'" />';	
		$k = 1;// Row colour counter.
		$RowIndex=1;
		$AmountTotal=0;
		if (!isset($SalaryArr)||count($SalaryArr)==0){
			$SalaryArr=array();
		}

		foreach($DepartAct as $KeyAct=>$row){
			if($k == 1) {
				echo '<tr class="OddTableRows">';
				$k = 0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k = 1;
			}			
			echo'<td rowspan="3">'.$RowIndex.'</td>			      
			<td rowspan="3">['.$KeyAct.']'.$row[0].'</td>
			<td rowspan="3"  class="number">
			    <input type="hidden"  name="CostAct'.$KeyAct.'" id="CostAct'.$KeyAct.'"   value="' .$KeyAct. '" />
				<input type="hidden"  name="Rmk'.$KeyAct.'" id="Rmk'.$KeyAct.'"   value="月末结转工资福利费'.$row[3].'"  />
			    <input type="text"  class="number" name="CostAmo'.$KeyAct.'" id="CostAmo'.$KeyAct.'" size="10"   value="' .locale_number_format( $_POST['CostAmo'.$KeyAct],2). '" readonly /></td>';
			
				$tr='';
				$rw=0;
				$CostTotal=0;
			foreach($SalaryAct as $ky=> $val){
				if ($_POST[$KeyAct.'Edit'.$rw]==2){
					$DepartAct[$ky][2]= $_POST[$KeyAct.'SalaryAmo'.$rw];
					$CostTotal+=$_POST[$KeyAct.'SalaryAmo'.$rw];
					$SalaryArr[$KeyAct][$ky]= $_POST[$KeyAct.'SalaryAmo'.$rw];
				}else{
					$SalaryArr[$KeyAct][$ky]=round($DepartAct[$KeyAct][2]*$val[2],2);
					$DepartAct[$ky][2]=round($DepartAct[$KeyAct][2]*$val[2],2);
					$CostTotal+=round($DepartAct[$KeyAct][2]*$val[2],2);
				}
				$_POST[$KeyAct.'SalaryAmo'.$rw]=$SalaryArr[$KeyAct][$ky];
				$_POST[$KeyAct.'Remark'.$rw]=$val[6];				
				echo $tr;				
				echo'<td >['.$val[0].']'.substr(strstr($val[1],'-'),1).'
					<input type="hidden"  name="'.$KeyAct.'Edit'.$rw.'" id="'.$KeyAct.'Edit'.$rw.'"   value="1" />
				</td>				

					</td>			
				<td  class="number"> 
					  <input type="text"  class="number" name="'.$KeyAct.'SalaryAmo'.$rw.'" id="'.$KeyAct.'SalaryAmo'.$rw.'"  size="10"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  value="'.$_POST[$KeyAct.'SalaryAmo'.$rw].'"  onchange="OnSalaryAmo(this ,'.count($val).','.$KeyAct.')" />
					  <input type="hidden"  name="'.$KeyAct.'SalaryAct'.$rw.'" id="'.$KeyAct.'SalaryAct'.$rw.'"   value="' .$val[0]. '" />	</td>
				<td > <input type="text"  class="number" name="Remark'.$val[0].'"  id="Remark'.$val[0].'"  size="10"  value=""   title="'.$val[0].'"  pattern="[\w\d\u0391-\uFFE5\(\)\[\]\ +$"  placeholder="月末结转工   '.substr(strstr($val[1],'-'),1).'" onchange="OnRemark(this ,'.$KeyAct.')"  />	</td>';
				echo'</tr>';
				$tr='<tr>';
				$rw++;
				}  
				echo'<script type="text/javascript">
					document.getElementById("CostAmo'.$KeyAct.'").value='.$CostTotal.'; 	
				</script>';	 
				$AmountTotal+=$CostTotal;	
				$DepartAct[$KeyAct][2]=$CostTotal;
				$RowIndex++;
				//$EmpAmo+=$row['income'];		
			}// END 

			$_POST['CostTotal']=$AmountTotal;
			$_POST['SalaryTotal']=$AmountTotal;
				echo'<tr>				
						<td colspan="2"></td>				
						<td class="text"> <input type="text" name="CostTotal" id="CostTotal" size="10"  value="'.$_POST['CostTotal'].'" />	</td>
						<td></td>
						<td class="text"> <input type="text" name="SalaryTotal" id="SalaryTotal" size="10"  value="'.$_POST['SalaryTotal'].'" />	</td>
						<td class="text"></td>			
					</tr>';
	}else{
		//简易模式
		echo '<table class="selection">';
		echo'<tr>
				 <th >序号</th>				
				 <th >工资提取科目</th>
				 <th >费用科目</th>
				 <th >提取金额</th>	
				 <th >摘    要</th>					
			 </tr>	<input type="hidden"  name="CostAct" id="CostAct"   value="'.$ActStr.'" />';	
			// print_r($SalaryAct);
			$RowIndex=1;
			foreach($SalaryAct as $key=>$row){
				$w=1;
				$creditact=[];
				$debitact=[];
				foreach($row as $ky=> $val){
					$ActName[$val[0]]=$val[3];
					if ($val[1]<0){
						$creditact[$val[0]]+=$val[1];
					}else{
						$debitact[$val[0]]+=$val[1];
					}
				}
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				$w=count($debitact);	
				//贷方科目
				foreach($creditact as $crkey=> $val){
					//if (!isset($_POST['CostAmo'.$crkey]))
					//	$_POST['CostAmo'.$crkey]=$val;

				   echo'<td rowspan="'.$w.'">'.$RowIndex.'</td>			      
						<td rowspan="'.$w.'">['.$crkey.']'.$ActName[$crkey].'
						<input type="hidden"  name="'.$RowIndex.'SalaryAct'.$crkey.'" id="'.$RowIndex.'SalaryAct'.$crkey.'"   value="' .$crkey. '" />
						<input type="hidden"  name="Rmk'.$crkey.'" id="Rmk'.$crkey.'"   value="提取工资福利���"  />
						</td>';
				}
						$tr="";
				//<input type="text"  class="number" name="CostAmo'.$crkey.'" id="CostAmo'.$crkey.'" size="10"   value="' .locale_number_format( $_POST['CostAmo'.$crkey],2). '" readonly />
				//借方科目
				foreach($debitact as  $dekey=>$val){
					if(!isset($_POST[$RowIndex.'CostAmo'.$dekey])){
						$_POST[$RowIndex.'CostAmo'.$dekey]=round($val,POI);	
					}		
					if(!isset($_POST[$RowIndex.'Remark'.$dekey])){
						$_POST[$RowIndex.'Remark'.$dekey]='提取工资';	
					}		
					echo $tr;				
					echo'<td >['.$dekey.']'.$ActName[$dekey].'
						<input type="hidden"  name="'.$RowIndex.'Edit'.$dekey.'" id="'.$RowIndex.'Edit'.$dekey.'"   value="1" />
					</td>				
					
					<td  class="number"> 
							<input type="text"  class="number" name="'.$RowIndex.'CostAmo'.$dekey.'" id="'.$RowIndex.'CostAmo'.$dekey.'"  size="10"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  value="'.$_POST[$RowIndex.'CostAmo'.$dekey].'"  onchange="OnCostAmo(this ,'.count($debitact).','.$dekey.')" />
							<input type="hidden"  name="'.$RowIndex.'CostAccount'.$dekey.'" id="'.$RowIndex.'CostAccount'.$dekey.'"   value="' .$dekey. '" />	</td>
					<td > <input type="text"  name="'.$RowIndex.'Remark'.$dekey.'"  id="'.$RowIndex.'Remark'.$dekey.'"  size="10"  value="'.$_POST[$RowIndex.'Remark'.$dekey].'"   title="'.$dekey.'"  pattern="[\w\d\u0391-\uFFE5\(\)\[\]\ +$"  placeholder="提取工资" onchange="OnRemark(this ,'.$dekey.')"  />	</td>';
					echo'</tr>';
					$tr='<tr>';
					$rw++;
					/*
					echo'<script type="text/javascript">
						document.getElementById("'.$RowIndex.'CostAmo'.$dekey.'").value='.$CostTotal.'; 	
					</script>';	 */
				
					
					$CostAmo+=$val;		
				}// END 
				$RowIndex++;
			}
			
				$_POST['TotalAmo']=$CostAmo;
			if (!isset($_POST['TotalTaxAmo']))
				$_POST['TotalTaxAmo']=$CostAmo;
			echo'<tr>
					<th ></th>				
					<th ></th>
						
					<th >合计</th>
					<th ><input type="text"  class="number" name="TotalAmo" id="TotalAmo"  size="10"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  value="'.$_POST['TotalAmo'].'"  /></th>	
					<th ></th>					
				</tr>	
				<tr>
					<th ></th>				
					<th ></th>
						
					<th >税务申报金额</th>
					<th >	<input type="text"  class="number" name="TotalTaxAmo" id="TotalTaxAmo"  size="10"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  value="'.$_POST['TotalTaxAmo'].'"  /></th>	
					<th ></th>					
				</tr>	';	
			echo '</table>
			<br />';
		}

		if(isset($_POST['Updateing'])){
			//1、如果没有模板保存为模板
			//2、核对税务申报金额
			/*
		*/
			$SalaryAccount=[];
			//读取手动录入
			foreach ($_POST as $FormVariableName =>$Qty) {

				if (mb_strstr($FormVariableName, 'SalaryAct')) {
				
					$m=explode("SalaryAct",$FormVariableName);
				
				   //prnMsg($m[0].'='.$m[1].'-');
				   $SalaryAccount[$m[0]-1][$_POST[$m[0].'SalaryAct' .$m[1] ]]=0;
					
				}
				
				if (mb_strstr($FormVariableName, 'CostAccount')&& $FormVariableName!='') {
				
					$n=explode("CostAccount",$FormVariableName);
					$Remark[$_POST[$n[0].'CostAccount' .$n[1] ]]=$_POST[$n[0].'Remark' .$n[1] ];
				  // prnMsg($n[0].'[=]'.$m[0].']'.$FormVariableName.'[-'.$CostAmo);
				   $SalaryAccount[$m[0]-1][$_POST[$m[0].'CostAccount' .$n[1] ]]=round((float)$_POST[$n[0].'CostAmo' .$n[1] ],2);
				}
			}//end while
			//echo  json_encode($SalaryAccount);
			//组成凭证数据
			for($i=0;$i<count($SalaryAccount);$i++){
			     $amount=0;
			
					foreach($SalaryAccount[$i] as $key=>$val){

						if ($val>0){
							$amount+=round($val,POI);
						}elseif($val==0){
							$slyact=$key;
						}
						//prnMsg($key.'='.$val);
					}
				if ($amount>0){
					$SalaryAccount[$i][$slyact]=-round((float)$amount,POI);
					$amount=0;
					$slyact='';

				}

				//print_r($row).PHP_EOL;
			}
			$jsnact= json_encode($SalaryAccount);
				
			if (count($SalaryAccount)>0){	
				if ($SalartMode==10){//没有模板
					$SQL="INSERT INTO myconfig ( `confname`, `tag`, `itemtype`, `conftype`, `confvalue`, `notes` )
					       VALUES('2211',1,0,11,'".$jsnact."','工资提取模板')";
					
					$Result = DB_query($SQL);

				}
					$TransNo =GetTransNo($SelectPrd, $db);			
					$TypeNo=GetTagTypeNo (51,$SelectPrd,$db);
					$TotalAmo=0;
					$result = DB_Txn_Commit();
			 	foreach($SalaryAccount as $ky=>$row){		
				     //prnMsg($TransNo.'='.$TypeNo.'-'.$row[0]);
					//$Rmk=(string)(int)substr($_POST['SelectDate'],5,2).$_POST['Rmk' .$n ];
					foreach($row as $key=>$val){
						$sql="INSERT INTO gltrans(`type`,
													transno,
													typeno,
													printno,
													prtchk,
													chequeno,
													trandate,
													periodno,
													account,
													narrative,
													amount,
													posted,
													jobref,
													tag,
													flg	)
											VALUES(51,
												'".$TransNo."',	
												'".$TypeNo."',							  
												0, 0,0,
												'".$_POST['SelectDate']."', 
												'".$SelectPrd."',
												'".$key."',
												'".$Remark[$key]."',
												'".$val."',
												0,
												0,
												1,
												1 )";
						//prnMsg($sql);
						$result=DB_query($sql);
					}
				 }
				 $result= DB_Txn_Commit();
			}
				/*
			
					$Remark=$_POST[$i[0].'Remark' .$i[1] ];
					//prnMsg(filter_number_format($Qty).'-'.$SalaryAct.'[-]'.$i[0].'='.$FormVariableName)	;
				
				if (round($AmoTotal,2)==0){
						$result= DB_Txn_Commit();
						//$SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);
						$SettleTab[2]=1;
						//prnMsg(implode(',',$SettleTab));
						$sql="UPDATE `companies` SET settle='".implode(',',$SettleTab)."' WHERE coycode=".$_POST['UnitsTag'];
						$result=DB_query($sql);
						if ($result){
							$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']=implode(',',$SettleTab);
						}
		
				}
				*/
					prnMsg('  资福利费提取会计凭证: ' . $TransNo . ' ' . _('has been successfully entered'),'success');
				
				//	echo '<br /><a href="index.php">' ._('Return to main menu') . '</a>';			
			
		}
		
}
echo '<div class="centre">
			<input type="submit" name="SalaryExt" value="查询" />	';
	if(isset($_POST['SalaryExt'])) {
		echo '<input type="submit" name="Updateing" value="执行提取" />';
	}
	echo'</div>';
	echo'</div>
	</form>';

include('includes/footer.php');

?>
