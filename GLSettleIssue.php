<?php
/* $Id: GLSettleIssue.php$*/
/* SettleIssue for employees  */

include('includes/session.php');
$Title ='发料成本结转';
$ViewTopic = 'HumanResource';
$BookMark = 'HumanResource';
include('includes/SQL_CommonFunctions.inc');
include('includes/header.php');
echo'<script type="text/javascript">
	function OnToAmo(S,R,C) {

		S.value=parseFloat(S.value).toFixed(2);	
		var  A=S.name.split("ToAmo")[0];	
		//对应科目串
		var actstr=document.getElementById("ToActStr"+A).value.toString();	   
		var	actar=actstr.split(",");		
		var amototal=0;		
		var costamototal=0;
		var righttotal=0;		
		var actrow=document.getElementById("ActRow").value;	
		//右面
		for (var f=0;f<actrow ;f++ ) {
		
				if (document.getElementById(A+"ToAmo"+f).value!=""){
								
					righttotal=parseFloat(righttotal)+parseFloat(document.getElementById(A+"ToAmo"+f).value);
				}		
		
		}
		
		document.getElementById(A+"Edit"+C).value=2;
		document.getElementById("CostAmo"+A).value=righttotal.toFixed(2);

		var leftactstr=document.getElementById("CostAct").value.toString();	
		
		var	leftactar=leftactstr.split(",");
		var lefttotal=0;
		for (var f=0;f<leftactar.length ;f++ ) {
			//console.log(document.getElementById("CostAmo"+leftactar[f]).value+"="+f);
			if (document.getElementById("CostAmo"+leftactar[f]).value!=0){
							
				lefttotal=parseFloat(lefttotal)+parseFloat(document.getElementById("CostAmo"+leftactar[f]).value);
			}		
	
	    }
		//console.log(lefttotal);
		document.getElementById("LeftTotal").value=lefttotal.toFixed(2);
		document.getElementById("RightTotal").value=lefttotal.toFixed(2);	
	}
</script>';
if(!isset($_POST['UnitsTag'])){
	$_POST['UnitsTag']=1;
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>
	<form action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post">
	<div>
		<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$SelectPrd=1+$_SESSION['CompanyRecord'][1]['lastsettleperiod'];	
	$_POST['SelectDate']= PeriodGetDate((1+$_SESSION['CompanyRecord'][1]['lastsettleperiod']));
	echo '<table class="selection">';
	echo '<tr>
		<td>' . _('Select Period To')  . '</td>
		<td >';
	echo $_POST['SelectDate'];
	echo'</td>
	    </tr>';

	echo'<tr>
			<td>单元分组</td>
			<td >';
			SelectUnitsTag(2);
	echo'</td>
	</tr>';
	echo '</table>
	<br />';
        
	
        //读取本年已经提取
		$SQL="SELECT  periodno,
		              account,
					  gltrans.tag,
					  accountname,
					  sum(sumamount(amount,periodno,0,". $SelectPrd.")) qmye,
					  SUM(TOAMOUNT(amount,-1,0,0,1,flg)) detotal,
					  SUM(toamount(amount,-1,0,0,-1,flg)) crtotal 
					FROM`gltrans` 
					LEFT JOIN chartmaster ON gltrans.account=accountcode
						WHERE	account LIKE '1403%' 						 
						 AND periodno>=".$_SESSION['janr']."
					GROUP BY periodno,account,  accountname,	gltrans.tag	
					ORDER BY gltrans.tag, periodno,account";
					// AND((flg = 1 AND amount < 0) OR(flg = -1 AND amount > 0)) 
		$Result = DB_query($SQL);
		//prnMsg($SQL);
if (DB_num_rows($Result)>0){
		echo '<table class="selection">';
		echo'<tr>
		<th colspan="6"   class="centre">'.substr($_POST['SelectDate'],0,4).'年发料成本结转</th>';
						
	echo'</tr>';
	echo'<tr>
			<th >月份</th>
			<th >分组</th>				
			<th >科目编码/名称</th>
			<th >借方金额</th>
			<th >贷方金额</th>
			<th ></th>
		</tr>';
		
		$k = 1;// Row colour counter.
		$RowIndex=1;
		$EmpCrAmo=0;
		$EmpDeAmo=0;
	while ($row = DB_fetch_array($Result)) {
		if($k == 1) {
			echo '<tr class="OddTableRows">';
			$k = 0;
		} else {
			echo '<tr class="EvenTableRows">';
			$k = 1;
		}
		
		echo'<td>'.PeriodGetDate($row['periodno']).'</td>			      
			<td class="text">'. $row['tag']. '</td>
			<td class="text">['. $row['account']. ']'.$row['accountname'].'</td> 
			<td class="text">'. $row['detotal']. '</td>
			<td class="text">'. $row['crtotal']. '</td>
			<td class="text"></td>
		</tr>';
			$RowIndex++;				
			$EmpCrAmo+=$row['detotal'];
			$EmpDeAmo+=$row['crtotal'];
		//	if (1+$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']==$row['periodno']){
			//	$LastData[$row['account']]=array($row['accountname'],$row['detotal'],$row['crtotal'],$row['qmye']);
			
	}// END foreach($Result as $row).
	echo'<tr>
			<td colspan="3" class="text"></td>				
			<td class="text">'. number_format($EmpDeAmo,2). '</td>
			<td class="text">'. number_format($EmpCrAmo,2). '</td>
			<td  class="text"></td>			
		</tr>';
			//读取提取科目
	if (!isset($SettleRule)||count($SettleRule)==0){

		$SQL="SELECT `itemtype`, confname,`confvalue`,accountname,conftype,notes 
				FROM `myconfig` 
				LEFT JOIN chartmaster ON confname=accountcode  
				WHERE myconfig.tag=".$_POST['UnitsTag']." 
						AND conftype=30";
		$SettleRule=array();
		$Result = DB_query($SQL);
		//SettleRule 0>借方4> 6>余额
		while ($row = DB_fetch_array($Result)) {
			$SettleRule[$row['confname']]=array($row['accountname'],$row['confvalue'],$row['conftype'],$row['itemtype'],0,$row['notes'],0,0);
		}
	}

	$i=1;
	$SQL=" SELECT   gltrans.tag,
					account,				     
					accountname, 
					SUM( sumamount(amount, periodno, 0,".$SelectPrd." -1) ) AS qcye ,
					SUM( toamount(amount, periodno,".$SelectPrd.",".$SelectPrd.", 1, flg) ) AS occur_de,
					SUM( toamount(amount, periodno,".$SelectPrd.",".$SelectPrd.", -1, flg) ) AS occur_cr,
					SUM( sumamount(amount, periodno, 0,".$SelectPrd.") ) AS  qmye,				
					1 flag 
					FROM gltrans
					LEFT JOIN chartmaster ON account = accountcode
					WHERE periodno <=".$SelectPrd."
						AND  gltrans.tag=".$_POST['UnitsTag']."
							AND   account like '1403%'
							group by  gltrans.tag, account,accountname";
	$Result = DB_query($SQL);			
	if (DB_num_rows($Result)>0){
		echo'
			<tr>
				<th colspan="6"><h3>结账期材料发生额</h3></th>
			<tr>
			<th >月份</th>
			<th >分组</th>				
			<th >科目编码/名称</th>
			<th >借方金额</th>
			<th >贷方金额</th>
			<th ></th>';										
		echo'</tr>';
		$EmpCrAmo=0;
		$EmpDeAmo=0;
		$AmoTotal=0;

		while ($row = DB_fetch_array($Result)) {
			if($k == 1) {
				echo '<tr class="OddTableRows">';
				$k = 0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k = 1;
			}
			echo'<td class="text">'. $i. '</td>
					<td class="text">'. $row['tag']. '</td>
					<td class="text">['. $row['account']. ']'.$row['accountname'].'</td>					
					<td class="text">'. number_format($row['occur_de'],2). '</td>
					<td class="text">'. number_format($row['occur_cr'],2). '</td>
					<td class="text">'. number_format($row['qmye'],2). '</td>					
			</tr>';
			$i++;
			$EmpDeAmo+=$row['occur_de'];
			$EmpCrAmo+=$row['occur_cr'];
			$AmoTotal+=$row['qmye'];
			if ($row['tag']>0){
				$SettleRule[$row['account']][0]=$row['accountname'];
				$SettleRule[$row['account']][7]=$row['occur_de'];
				$SettleRule[$row['account']][6]=$row['qmye'];
			}

		}
		echo'<tr>
			<td colspan="3" class="text"></td>				
			<td class="text">'. number_format($EmpDeAmo,2). '</td>
			<td class="text">'. number_format($EmpCrAmo,2). '</td>
			<td class="text">'. number_format($AmoTotal,2). '</td>		
		</tr>';
	}
	echo '</table>
		<br />';
		//	var_dump($SettleRule);
}
		//$SettleTabAct=array("0"=>'1602',"1"=>'2211',"2"=>'2221',"3"=>"1403","4"=>"1405","5"=>'5001',"6"=>'5101',"7"=>'6001');
	    $SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);		
		if ($SettleTab[5]>=1){
		
			prnMsg(substr($_POST['SelectDate'],0,7).'采购成本已经结转!','info');
			include('includes/footer.php');
			exit;	
		}
	
if(isset($_POST['SettleIssue'])||isset($_POST['Updateing'])) {
	   /*1.只能结转外账
		*/

	$ActStr=implode(',',array_keys($SettleRule));
	echo '<table class="selection">';
	   echo'<tr>
				<th >序号</th>				
				<th >材料科目</th>
				<th >贷方金额</th>				
				<th >结转到科目</th>
				<th >借方金额</th>	
				<th >摘要</th>					
			</tr>
				<input type="hidden"  name="CostAct" id="CostAct"   value="'.$ActStr.'" />';	
			$k = 1;// Row colour counter.
			$RowIndex=1;			
			$tr='';
			$rw=0;
			$rw2=0;		
			$rw1=count($SettleRule);
			$AmoTotal=0;		
	foreach($SettleRule as  $key=>$row){
		$ToActArr=json_decode($row[1],true);
		if (count($ToActArr)==0){
			$rw2=1;
		}else{
			$rw2=count($ToActArr);
			//$rw=$rw2;
		}
		$CostActStr=implode(',',array_keys($ToActArr));
		//prnMsg($CostActStr);
		if (strlen($CostActStr)>5){
			$SQL="SELECT `accountcode`, `accountname` 
					FROM `chartmaster`
					WHERE accountcode IN (".$CostActStr.")";
					
			$Result=DB_query($SQL);
			$ToActName=array();
			while($Row=DB_fetch_array($Result)){
				$ToActName[$Row['accountcode']]=$Row['accountname'];

			}
		
		}
			if($k == 1) {
				echo '<tr class="OddTableRows">';
				$k = 0;
			} else {
				echo '<tr class="EvenTableRows">';
				$k = 1;
			}
		
			echo'<td rowspan="'.$rw2.'">'.$RowIndex.'</td>			      
					<td rowspan="'.$rw2.'">['.$key.']'.$row[0].'</td>
					<td rowspan="'.$rw2.'"  class="number">
					
					<input type="hidden"  name="Rmk'.$key.'" id="Rmk'.$key.'"   value="月末结转材料"  />
					<input type="text"  class="number" name="CostAmo'.$key.'" id="CostAmo'.$key.'" size="10"   value="' .locale_number_format( $_POST['CostAmo'.$key],2). '" readonly />
					<input type="hidden"  name="CostAct'.$key.'" id="CostAct'.$key.'"   value="' .$key. '" />
					<input type="hidden"  name="ToActStr'.$key.'" id="ToActStr'.$key.'"   value="' .$CostActStr. '" /></td>';
			
			
				$ToAmoTotal=0;
				if ($rw2>0){
					foreach($ToActArr as $keyAct=> $val){
										
						if ($_POST[$key.'Edit'.$rw]==2){
							$SettleRule[$key][4]= $_POST[$key.'ToAmo'.$rw];
							$ToAmoTotal+=$_POST[$key.'ToAmo'.$rw];
							
						}else{
							$SettleRule[$key][4]=round($SettleRule[$key][7]/$rw2,2);
							$ToAmoTotal+=round($SettleRule[$key][4],2);
							$_POST[$key.'ToAmo'.$rw]=$SettleRule[$key][4];
						}
					
						//$_POST[$key.'Remark'.$rw]=$val[5];				
						echo $tr;					
						echo'<td >['.$keyAct.']'.$ToActName[$keyAct].'
							<input type="hidden"  name="'.$key.'Edit'.$rw.'" id="'.$key.'Edit'.$rw.'"   value="1" />
							<input type="hidden"  name="'.$key.'ToAct'.$rw.'" id="'.$key.'ToAct'.$rw.'"   value="' .$keyAct. '" /></td>				
						<td  class="number"> 
							<input type="text"  class="number" name="'.$key.'ToAmo'.$rw.'" id="'.$key.'ToAmo'.$rw.'"  size="10"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  value="'. $_POST[$key.'ToAmo'.$rw].'"  onchange="OnToAmo(this ,'.$rw2.','.$rw.')" />	</td>
						<td > <input type="text"  class="number" name="'.$key.'Remark'.$rw.'"   id="'.$key.'Remark'.$rw.'"  size="15"  value="'.$_POST[$key.'Remark'.$rw].'"   title="'.$msg.'"  pattern="[\w\d\u0391-\uFFE5\(\)\[\]\ +$"  placeholder="月末结转附加税'.substr(strstr($val[1],'-'),1).'" onchange="OnRemark(this ,'.$key.')"  />	</td>';
						echo'</tr>';
						$tr='<tr>';
						$rw++;								
					}  
					$AmoTotal+=$ToAmoTotal;
				}else{
					echo $tr;	
					echo'<td colspan="3"></td></tr>';
					$tr='<tr>';
							
				}
				echo'<script type="text/javascript">
						document.getElementById("CostAmo'.$key.'").value='.$ToAmoTotal.'; 	
					</script>';	
					//$SettleRule[$key][5]=$ToAmoTotal;
				$RowIndex++;					
	}// END 
				$_POST['LeftTotal']=$AmoTotal;
				$_POST['RightTotal']=$AmoTotal;	
		echo'<tr>				
				<td colspan="2"></td>				
				<td class="text"> <input type="text" name="LeftTotal" id="LeftTotal" size="10"  value="'.$_POST['LeftTotal'].'" />	</td>
				<td><input type="hidden"  name="ActRow" id="ActRow"   value="' .$rw. '" /></td>
				<td class="text"> <input type="text" name="RightTotal" id="RightTotal" size="10"  value="'.$_POST['RightTotal'].'" />	</td>
				<td class="text"></td>			
			</tr>';
	echo '</table>
		<br />';
		if(isset($_POST['Updateing'])){
		
			$TransNo =GetTransNo($SelectPrd, $db);			
			$TypeNo=GetTypeNo (51,$SelectPrd,$db);
			$TotalAmo=0;
			$AmoTotal=0;
			$result = DB_Txn_Commit();
			foreach ($_POST as $FormVariableName =>$Qty) {
				//prnMsg(mb_substr($FormVariableName,7));
				
				if (mb_substr($FormVariableName, 0, 7)=='CostAmo' AND filter_number_format($Qty)!=0) {
					
					$n=mb_substr($FormVariableName,7);
					//$AmoTotal+= $_POST['CostAmo' .$n ];				
					$CostAmo = -round($_POST['CostAmo' .$n ],2);
					$AmoTotal+=$CostAmo;
					$CostAct = $_POST['CostAct' .$n ];
					$Rmk=(string)(int)substr($_POST['SelectDate'],5,2).$_POST['Rmk' .$n ];
				
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
				if(strpos($FormVariableName,'ToAmo') !== false && filter_number_format($Qty)!=0){
					$i=explode('ToAmo',$FormVariableName);
					$ToAct = $_POST[$i[0].'ToAct' .$i[1] ];
					$ToAmo = round($_POST[$i[0].'ToAmo' .$i[1] ],2);
					$AmoTotal+=$ToAmo;	
				
					$Remark=$_POST[$i[0].'Remark' .$i[1] ];
					//prnMsg(filter_number_format($Qty).'-'.$ToAct.'[-]'.$i[0].'='.$FormVariableName)	;
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
											'".$ToAct."',
											'".$Rmk.$Remark."',
											'".$ToAmo."',
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
						$EndYearPrd=$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']-date('m',strtotime($_POST['SelectDate']))+13;
						$InitialTab=explode(',',$_SESSION['WebERP']);
						$SettleTab[5]=$SettleTab[5]+1;
						if ($InitialTab[5]==0){
							$SettleTab[5]=$SettleTab[5]+1;
						}elseif ($InitialTab[5]==-1){
							$SettleTab[5]=$SettleTab[5]+2;
						}
						$Status=0;
						for($i=1;$i<count($SettleTab);$i++){
							if ($InitialTab[$i]==0){
								if ($SettleTab[$i]!=1){
									$Status=1;
									break;
								}
							}elseif ($InitialTab[$i]>=-1){
								if ($SettleTab[$i]!=3){
									//初始为-1结内外账  标记为3为结账
									$Status=1;
									break;
								}
							}

						}

						
						if ($Status==0){
							//本期结账完成,结转到下期,如果时12份不结转
							if ($SelectPrd!=$EndYearPrd){
								$sql="UPDATE `companies` SET lastsettleperiod=lastsettleperiod+1,settle='".$_SESSION['WebERP']."' WHERE coycode=".$_POST['UnitsTag'];
							}else{
								$sql="UPDATE `companies` SET settle='".implode(',',$SettleTab)."' WHERE coycode=".$_POST['UnitsTag'];	
							}
						}else{
							//本期发料结账完成
							$sql="UPDATE `companies` SET settle='".implode(',',$SettleTab)."' WHERE coycode=".$_POST['UnitsTag'];
						
						}
						$result=DB_query($sql);
						if ($result){
							$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']=implode(',',$SettleTab);
						}
		
				}
				
					prnMsg('发料成本结转会计凭证: ' . $TransNo . ' ' . _('has been successfully entered'),'success');
				
				//	echo '<br /><a href="index.php">' ._('Return to main menu') . '</a>';			
			
		}
		
}
echo '<div class="centre">
			<input type="submit" name="SettleIssue" value="结转发料成本" />	';
	if(isset($_POST['SettleIssue'])) {
		echo '<input type="submit" name="Updateing" value="上传保存" />';
	}
	echo'</div>';
	echo'</div>
	</form>';

include('includes/footer.php');
?>
