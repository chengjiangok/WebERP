<?php
/* $Id: GLAdditionalTaxExt.php$*/
/* Search for employees  */

include('includes/session.php');
$Title ='附加税提取';
$ViewTopic = 'HumanResource';
$BookMark = 'HumanResource';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
	function OnTaxExt(S,R,C) {	
		S.value=parseFloat(S.value).toFixed(2);
	
		var  k= Number(C.toString().length)+ Number(9);
	
		var  n=S.name.substring(k);	
		
		var amototal=0;
	
		for(var i=0;i<R;i++){	
		    if (document.getElementById(C+"TaxExtAmo"+i).value!=""){
							
				amototal=parseFloat(amototal)+parseFloat(document.getElementById(C+"TaxExtAmo"+i).value);
			}
		}    
		console.log(amototal);
		document.getElementById(C+"Edit"+n).value=2;
		document.getElementById("CostAmo"+C).value=amototal.toFixed(2);
		document.getElementById("CostTotal").value=amototal.toFixed(2);
		document.getElementById("TaxExtAmoTotal").value=amototal.toFixed(2);				
	}
	function OnRemark(S, R) {	
		console.log(S.value);
		
		//document.getElementById("ToSelectAct"+R).value=jsonStr;	
		//var check = document.getElementById("chkbx"+R);
		//check.checked=true;	
		
	}
</script>';
if(!isset($_POST['UnitsTag'])){
	$_POST['UnitsTag']=1;
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	  echo '<table class="selection">';
	  echo'<tr>
			<td>单元分组</td>
			<td >';
			SelectUnitsTag(2);

		echo'</td>
		</tr>';
		$SelectPrd=1+$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod'];
		$_POST['SelectDate']= PeriodGetDate($SelectPrd);
	  echo '<tr>
	      <td>' . _('Select Period To')  . '</td>
		  <td >';
		  echo    $_POST['SelectDate'];
		  echo'</td></tr>';

	
		echo '</table>
		<br />';
		//$SettleTabAct=array("0"=>'1602',"1"=>'2211',"2"=>'2221',"3"=>"1403","4"=>"1405","5"=>'5001',"6"=>'5101',"7"=>'6001');
	    $SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);
	
		
		if ($SettleTab[3]==1){
		
			prnMsg('城建税、教育附加、地方教育附加、水利基金已经提取!','info');
			include('includes/footer.php');
			exit;	
		}
		//读取提取科目
		if (!isset($AdditionAct)||count($AdditionAct)==0){

			$SQL="SELECT `itemtype`, confname,`confvalue`,accountname,conftype,notes FROM `myconfig` LEFT JOIN chartmaster ON confname=accountcode  WHERE myconfig.tag=".$_POST['UnitsTag']." AND conftype IN(10,9,5,7)";
			$AdditionAct=array();
			$Result = DB_query($SQL);
			while ($row = DB_fetch_array($Result)) {
				$AdditionAct[]=array($row['confname'],$row['accountname'],$row['confvalue'],$row['conftype'],$row['itemtype'],0,$row['notes']);
			}
		}
		//读取本年已经提取
		
		$SQL="SELECT  periodno,account,accountname,gltrans.tag,SUM(TOAMOUNT(amount,-1,0,0,1,flg)) detotal,SUM(toamount(amount,-1,0,0,-1,flg)) crtotal 
					FROM`gltrans` LEFT JOIN chartmaster ON account=accountcode
					WHERE	account IN(	SELECT `confname` FROM `myconfig` WHERE conftype IN (5,6,7) AND tag=abs(".$_POST['UnitsTag'].")	) 
					        AND periodno>=".$_SESSION['janr']."
					GROUP BY periodno,account,accountname,gltrans.tag	
					ORDER BY gltrans.tag, periodno,account";
		$Result = DB_query($SQL);
		$LastData=array();
		if (DB_num_rows($Result)>0){
			echo '<table class="selection">';
			echo'<tr>
					<th >月份</th>
					<th >分组</th>				
					<th >科目编码/名称</th>
					<th >借方金额</th>';				
				echo'<th >贷方金额</th>
				   </tr>';
			
			$k = 1;// Row colour counter.
			$RowIndex=1;
			$EmpAmo=0;
		
			while ($row = DB_fetch_array($Result)) {
				if($k == 1) {
					echo '<tr class="OddTableRows">';
					$k = 0;
				} else {
					echo '<tr class="EvenTableRows">';
					$k = 1;
				}
				
				   echo'<td>'.PeriodGetDate($row['periodno']).'</td>			      
						<td class="text">'.$_SESSION['CompanyRecord'][$row['tag']]['unitstab']. '</td>
						<td class="text">['. $row['account']. ']'.$row['accountname'].'</td> 
						<td class="text">'. $row['detotal']. '</td>'; 
				
					echo'<td class="text">'. $row['crtotal']. '</td>
					   </tr>';
					   if ($_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']==$row['periodno']){
					   		$LastData[$row['account']]=array($row['accountname'],$row['detotal'],$row['crtotal']);
					   }
					$RowIndex++;				
					$EmpAmo+=$row['amounttotal'];
				
			}// END foreach($Result as $row).
			echo'<tr>
					<td colspan="2" class="text"></td>
					<td class="text"></td>
					<td class="text">'. number_format($EmpAmo,2). '</td>
					<td class="text"></td>
					<td class="text"></td>						
				</tr>';
			echo '</table>
				<br />';
		}
	

   
if(isset($_POST['AdditionalTaxExt'])||isset($_POST['Updateing'])) {
		/*1.	有上传税务发票 
		2. 科目发生额
		3.*/

	//var_dump($LastData);
	if(isset($_POST['Updateing'])){
		
		$TransNo =GetTransNo( $SelectPrd, $db);			
		$TypeNo=GetTypeNo (51,$SelectPrd,$db);
		$TotalAmo=0;			
		$result = DB_Txn_Commit();
		foreach ($_POST as $FormVariableName =>$Qty) {
			//prnMsg(mb_substr($FormVariableName,7));
	       
			if (mb_substr($FormVariableName, 0, 7)=='CostAmo' AND filter_number_format($Qty)!=0) {
				
				$n=mb_substr($FormVariableName,7);
				$AmoTotal+= $_POST['CostAmo' .$n ];				
				$CostAmo = round($_POST['CostAmo' .$n ],2);
				$CostAct = $_POST['CostAct' .$n ];
				$Rmk=(string)(int)substr($_POST['SelectDate'],5,2).$_POST['Rmk' .$n ];
			//	prnMsg($CostAmo.'='.$CostAct);
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
										'".$CostAct."',
										'".$Rmk."',
										'".$CostAmo."',
										0,
										0,
										1,
										1 )";
					//prnMsg($sql);
				 $result=DB_query($sql);
			}
			if(strpos($FormVariableName,'TaxAct') !== false && filter_number_format($Qty)!=0){
				$i=explode('TaxAct',$FormVariableName);
				$TaxAct = $_POST[$i[0].'TaxAct' .$i[1] ];
				$TaxAmo = -round($_POST[$i[0].'TaxExtAmo' .$i[1] ],2);
				$AmoTotal+=$TaxAmo;	
			
				$Remark=$_POST[$i[0].'Remark' .$i[1] ];
				//prnMsg($TaxAct.';'.$TaxAmo.'--='.$FormVariableName)	;
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
										'".$TaxAct."',
										'".$Rmk.$Remark."',
										'".$TaxAmo."',
										0,
										0,
										1,
										1 )";
				//prnMsg($sql);
				 $result=DB_query($sql);
			}		
				
		}
			
			if (round($AmoTotal,2)==0){
					$result= DB_Txn_Commit();
					//$SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);
					$SettleTab[3]=1;
				   // prnMsg(implode(',',$SettleTab));
					$sql="UPDATE `companies` SET settle='".implode(',',$SettleTab)."' WHERE coycode=".$_POST['UnitsTag'];
					$result=DB_query($sql);
					if ($result){
						$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']=implode(',',$SettleTab);
					}
	
			}
				prnMsg('城建税教育附加及水利基金提取会计凭证: ' . $TransNo . ' ' . _('has been successfully entered'),'success');
			
			//	echo '<br /><a href="index.php">' ._('Return to main menu') . '</a>';			
		
	}


	echo '<table class="selection">';
		echo'<tr>
				<th >序号</th>				
				<th >费用科目</th>
				<th >借方金额</th>				
				<th >工资科目</th>
				<th >贷方金额</th>	
				<th >摘要</th>					
			</tr>';
	
	$k = 1;// Row colour counter.
	$RowIndex=1;
	$EmpAmo=0;

	$tr='';
	$rw1=0;
	$rw2=0;
	$tax=0;
	$income=0;
	//var_dump($AdditionAct);
	foreach( $AdditionAct as $row){
		if ($row[3]==5){
		$tax+=$LastData[$row[0]][1]-$LastData[$row[0]][2];
		}elseif($row[3]==7){
		$income+=$LastData[$row[0]][2];
		}elseif ($row[3]==9){
			$rw1++;
		}elseif ($row[3]==10){
			$rw2++;
		}
	}
	//prnMsg($tax.'='.$income);
	foreach( $AdditionAct as $key=>$row){

		if ($row[3]==9){
			if($k == 1) {
				echo '<tr class="OddTableRows">';
				$k = 0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k = 1;
			}		
			echo'<td rowspan="'.$rw2.'">'.$RowIndex.'='.$key.'</td>			      
				<td rowspan="'.$rw2.'">['.$row[0].']'.$row[1].'</td>
				<td rowspan="'.$rw2.'"  class="number">
					<input type="hidden"  name="CostAct'.$row[0].'" id="CostAct'.$row[0].'"   value="' .$row[0]. '" />
					<input type="hidden"  name="Rmk'.$row[0].'" id="Rmk'.$row[0].'"   value="月末结转附加税"  />
					<input type="text"  class="number" name="CostAmo'.$row[0].'" id="CostAmo'.$row[0].'" size="10"   value="' .locale_number_format( $_POST['CostAmo'.$row[0]],2). '" readonly /></td>';
				$rw=0;
                 $TaxExtTotal=0;
				foreach($AdditionAct as $ky=> $val){
					if ($val[3]==10){					
						if ($_POST[$row[0].'Edit'.$rw]==2){
							$AdditionAct[$ky][5]= $_POST[$row[0].'TaxExtAmo'.$rw];
							$TaxExtTotal+=$_POST[$row[0].'TaxExtAmo'.$rw];
						}else{
							$AdditionAct[$ky][5]=-round($tax*$val[2],2);
							$TaxExtTotal+=-round($tax*$val[2],2);
						}
						$_POST[$row[0].'TaxExtAmo'.$rw]=$AdditionAct[$ky][5];
						$_POST[$row[0].'Remark'.$rw]=$val[6];				
						echo $tr;					
						echo'<td >['.$val[0].']'.$val[6].'
							<input type="hidden"  name="'.$row[0].'Edit'.$rw.'" id="'.$row[0].'Edit'.$rw.'"   value="1" />
							<input type="hidden"  name="'.$row[0].'TaxAct'.$rw.'" id="'.$row[0].'TaxAct'.$rw.'"   value="' .$val[0]. '" /></td>				
						<td  class="number"> 
							<input type="text"  class="number" name="'.$row[0].'TaxExtAmo'.$rw.'" id="'.$row[0].'TaxExtAmo'.$rw.'"  size="10"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  value="'. $_POST[$row[0].'TaxExtAmo'.$rw].'"  onchange="OnTaxExt(this ,'.$rw2.','.$row[0].')" />	</td>
						<td > <input type="text"  class="number" name="'.$row[0].'Remark'.$rw.'"   id="'.$row[0].'Remark'.$rw.'"  size="15"  value="'.$_POST[$row[0].'Remark'.$rw].'"   title="'.$msg.'"  pattern="[\w\d\u0391-\uFFE5\(\)\[\]\ +$"  placeholder="月末结转附加税'.substr(strstr($val[1],'-'),1).'" onchange="OnRemark(this ,'.$row[0].')"  />	</td>';
						echo'</tr>';
						$tr='<tr>';
						$rw++;
						
					}
				}  
				echo'<script type="text/javascript">
					document.getElementById("CostAmo'.$row[0].'").value='.$TaxExtTotal.'; 	
					</script>';	
					$AdditionAct[$key][5]=$TaxExtTotal;
				$RowIndex++;
			
		}	
	}// END 
		$_POST['CostAmoTotal']=$TaxExtTotal;
        $_POST['TaxExtAmoTotal']=$TaxExtTotal;
		
		echo'<tr>				
				<td colspan="2"></td>				
				<td class="text"> <input type="text" name="CostTotal" id="CostTotal" size="10"  value="'.$_POST['CostTotal'].'" />	</td>
				<td></td>
				<td class="text"> <input type="text" name="TaxExtAmoTotal" id="TaxExtAmoTotal"  size="10"  value="'.$_POST['TaxExtAmoTotal'].'" />	</td>
				<td class="text"></td>				
				</tr>';
		echo '</table>
			<br />';
		
			
}
	echo '<div class="centre">
	<input type="submit" name="AdditionalTaxExt" value="提取附加税" />	';
	if(isset($_POST['AdditionalTaxExt'])) {
		echo '<input type="submit" name="Updateing" value="上传保存" />';
	}
echo'</div>';
echo'</div>
</form>';
include('includes/footer.php');
?>
