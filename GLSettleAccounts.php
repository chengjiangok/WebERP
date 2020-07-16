
<?php
/* $Id: GLSettleAccounts.php  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-11-03 10:00:54 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-10-09 06:56:39
 */
//include('includes/DefineJournalClass.php');
//include('includes/DefineSettleClass.php');
include('includes/session.php');
$Title ='期末结账业务';// Screen identificator.
$ViewTopic= 'settle accounts';// Filename's id in ManualContents.php's TOC.
$BookMark = 'settleaccounts';// Anchor's id in the manual's html document.
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

echo'<script type="text/javascript">
	function settlchk(s,v){
		v.value=1;
	}		
	function sltproduct(obj){		
	 	window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?myslt="+obj.value ;
	}	
	function fun() {  
          window.location.reload();
    }  	
</script>';
if (empty($_GET['identifier'])) {
	$identifier = date('U');
} else {
	$identifier = $_GET['identifier'];
}
/*
if (!isset($_SESSION['SA' . $identifier])) {
	
	$_SESSION['SA' . $identifier] = new GLSettle;
} //end if initi

*/
if (!isset($_POST['UnitsTag'])){
    $_POST['UnitsTag']=1;
}

echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/group_add.png" title="' .	_('Search') . '" />' .// Icon title.
	$Title . '</p>';// Page title.
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?identifier=' . $identifier . '" method="post" id="choosesupplier">';

echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	 

	  echo '<table class="selection">';
	  echo '<tr>
				<td>单元分组</td>
				<td  colspan="2">';
				SelectUnitsTag(2);
			echo'</td></tr>';  
			
			$_POST['SelectDate']= PeriodGetDate((1+$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']));
	  echo '<tr>
				<td width="100">结账会计期间 </td>
				<td>';
			echo $_POST['SelectDate'];
			echo'  </td>
			</tr>';   
	 echo'</table>';

	     $SelectPrd=1+$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod'];
	     $EndYearPrd=$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']-date('m',strtotime($_POST['SelectDate']))+13;
		 //$SettleTabAct=array("0"=>'1602',"1"=>'2211',"2"=>'2221',"3"=>"1403","4"=>"1405","5"=>'5001',"6"=>'6001');
		 $SettleTabAct=array("0"=>'折旧提取',"1"=>'工资提取',"2"=>'附加税提取',"3"=>"采购成本结转","4"=>"发料成本结转","5"=>'产品成本结转',"6"=>'销售成本结转');
		 $SettleTab=explode(',',$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']);
				 //本年度结账
				 $JanrPrd=$_SESSION['janr'];  
				 $msg='';
				 echo '<div class="page_help_text">';

				 for($i=0;$i<count($SettleTabAct);$i++){
					if ((int)$SettleTab[$i+1]==0||(int)$SettleTab[$i+1]==-1){
						echo ' '.($i+1).'、'.$SettleTabAct[$i];
					}		
			  	}
						echo '<br>以上账目没有结转，请先结转账目!';	 
				 echo'</div>';				 

			echo'<table width="720" cellpadding="4">
				<tr>
					<th style="width:240">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;结账操作&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
					<th style="width:240">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;查&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;询&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
					<th style="width:240">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;设&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;置&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
				</tr>';
			echo'<tbody>
					<tr>
						<td valign="top" class="select">';	
						if ($SettleTab[1]==0){	   
							echo '<a href="', $RootPath, '/FixedAssetDepreciation.php"  target="_blank" >折旧提取</a><br>';
						}else{
							echo'提取折旧<br>';
						}
						if ($SettleTab[2]==0){
							echo '<a href="', $RootPath, '/GLSalaryExtraction.php"  target="_blank" >工资提取</a><br />';
						}else{
							echo'工资提取<br>';
						}
						if ($SettleTab[3]==0){
							echo '<a href="', $RootPath, '/GLAdditionalTaxExt.php"  target="_blank" >附加税提取</a><br />';
						}else{
							echo'附加税提取<br>';
						}
						if ($SettleTab[4]!=-3){
							if ($SettleTab[4]==0){//
								echo '<a href="', $RootPath, '/StockPurchCost.php"   target="_blank">采购成本结转</a><br />';
							}elseif ($SettleTab[4]==-1){//简易结转
								echo '<a href="', $RootPath, '/GLSettleIssue.php"   target="_blank">采购成本结转</a><br />';
							}else {
								
								echo'采购成本结转<br>';
							}
						}
						if ($SettleTab[5]==0){
							echo '<a href="', $RootPath, '/StorePurchCost.php"   target="_blank">发料成本结转</a><br />';
						}elseif ($SettleTab[5]==-1){
							echo '<a href="', $RootPath, '/GLSettleIssue.php"   target="_blank">发料成本结转</a><br />';
						}else {
							
							echo'发料成本结转<br>';
						}
						if ($SettleTab[6]==0){
							echo '<a href="', $RootPath, '/StoreMakeCost.php"   target="_blank">产品成本结转</a><br />';
						}elseif ($SettleTab[6]==-1){
							echo '<a href="', $RootPath, '/GLSettleMake.php"   target="_blank">产品成本结转</a><br />';
						}else{
							echo'产品成本结转<br>';
						}
						if ($SettleTab[7]==0){
							echo '<a href="', $RootPath, '/StoreSaleCost.php"   target="_blank">销售成本结转</a><br />';
						}elseif ($SettleTab[7]==-1){
							echo '<a href="', $RootPath, '/GLSettleSales.php"   target="_blank">销售成本结转</a><br />';
						}else{
							echo'销售成本结转<br>';
						}  				
						echo '</td>
							  <td valign="top" class="select">';
						echo '<a href="', $RootPath, '/_demo0.php"   target="_blank">结账查询</a><br />';					
						echo '<a href="', $RootPath, '/FixedAssetRegister.php"   target="_blank" >资产账簿</a><br />';
						
					//	echo '<a href="', $RootPath, '/_demo0.php"   target="_blank">参数查询</a><br />';
						echo '</td>
							  <td valign="top" class="select">';	
						echo '<a href="', $RootPath, '/AccountGroups.php">会计要素</a><br />';		
						echo '<a href="', $RootPath, '/SettleAccounts.php"   target="_blank">结账设置</a><br />';
						//echo '<a href="', $RootPath, '/_demo0.php"   target="_blank">系数设置</a><br />';
					
						echo '</td>
					</tr>
				<tbody>
		</table><br>';
	
	 
//echo  '-='.$EndYearPrd.'=='.$SelectPrd;
//if ($EndYearPrd==$SelectPrd){
	   //调试!==  正常==
		$SQL="SELECT `confname`, `confvalue`,accountname, `notes` FROM `myconfig` LEFT JOIN chartmaster ON confvalue=accountcode WHERE conftype=20";
		$Result=DB_query($SQL);
		$SettleArr=array();
		while($row=DB_fetch_array($Result)){
           $SettleArr[$row['confname']]=array($row['confvalue'],$row['accountname'],$row['notes']);
		}	
		$SQL="SELECT account ,
		             accountname,
					 round(SUM(amount),2) total
				FROM `gltrans` 	
				LEFT JOIN chartmaster ON gltrans.account=chartmaster.accountcode
				WHERE periodno>=0 AND periodno<'".$_SESSION['janr']."' 
				AND account  IN (SELECT `confname` FROM `myconfig` WHERE conftype=20 AND tag=".$_POST['UnitsTag'].")		
				GROUP BY account,accountname";
		$Result=DB_query($SQL);	
		$TotalAmo=0;	
		$yeardate=substr(PeriodGetDate($_SESSION['janr']-1),0,4);
	
		if(isset($_POST['Updateing'])){
		    //年末结账
			$TransNo =GetTransNo($SelectPrd, $db);			
			$TypeNo=GetTypeNo (51,$SelectPrd,$db);
			$TotalAmo=0;
				
			$result = DB_Txn_Commit();
			foreach ($_POST as $FormVariableName =>$Qty) {
				//prnMsg($FormVariableName);
				
				if (mb_substr($FormVariableName, 0,8)=='AmoTotal' AND filter_number_format($Qty)!=0) {
					
					$Act=mb_substr($FormVariableName,8);
					$TotalAmo+= $_POST['AmoTotal' .$Act ];
					$DC=$_POST['DeCr'.$Act];
					if (round((float)$_POST['AmoTotal' .$Act ],2)!=0){
						$Amo=$DC*$Qty;//_POST['Total' .$Act ];
						//prnMsg( $_POST['Total' .$Act ].'-'. $Qty);
					}					
					$ToAct = $_POST['ToAct' .$Act ];
					$Rmk=(string)((int)substr($_POST['SelectDate'],0,5)-1).'年末结账'.$_POST['Rmk' .$Act ];
				
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
											'".$Act."',
											'".$Rmk."',
											'".(-$Amo)."',
											0,
											0,
											1,
											1 )";
					//prnMsg($sql);
					$result=DB_query($sql);
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
											'".$Rmk."',
											'".$Amo."',
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
						//
						$SettleTab[2]=1;
						//prnMsg(implode(',',$SettleTab));
						$sql="UPDATE `companies` SET settle='".implode(',',$SettleTab)."' WHERE coycode=".$_POST['UnitsTag'];
						//$result=DB_query($sql);
						if ($result){
							//$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']=implode(',',$SettleTab);
						}
				}
					prnMsg('年末结账会计凭证: ' . $TransNo . ' ' . _('has been successfully entered'),'success');
				//	echo '<br /><a href="index.php">' ._('Return to main menu') . '</a>';			
		}
		echo '<div class="centre">';
		if ($TotalAmo>0){
			prnMsg('上年账未全部���转,所以不能做当期结账!','warn');
		
			echo'<input type="submit" name="Updateing" value="年度结转确认" />
			';	
		}
		echo'<br /> 
			 
				<input type="submit" name="SettleUpdate" value="月结账确认" />
				<input type="submit" name="Settle" value="当期结账查询" />
			</div>';
		if (DB_num_rows($Result)>=0){//正确>0 调试改为=0
			
			echo '<table cellpadding="2" class="selection">
			<tr><th colspan="6" class="centre">'.$yeardate.'年度</th></tr>
			<tr>
				<th >序号</th>											
				<th >科目编码</th>	
				<th >科目名</th>	
				<th >借/贷</th>	
				<th >上年末余额</th>
				<th >本期发生额</th>
				<th >本年末余额</th>
				<th >结转科目</th>			
			
			</tr>'; 
		$RowIndex=1;
		while ($row=DB_fetch_array($Result)){
	    	
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}	
			$_POST['AmoTotal'.$row['account']]=abs($row['total']);		
			echo'<td>'.$RowIndex.'</td>				
				<td>'.$row['account'].'</td>
				<td>'.$row['accountname'].'</td>
				<td >'.($row['total']>0?"借":"贷").'</td>
				<td></td>
				<td></td>
				<td>
				    <input type="text"  class="number" name="AmoTotal'.$row['account'].'" id="AmoTotal'.$row['account'].'"   size="10"   pattern="(^-?\d{1,10})(.\d{1,2})?$"　  value="'.$_POST[ 'AmoTotal'.$row['account']].'"  readonly  /></td>	
				    <input type="hidden"  name="Rmk'.$row['account'].'" id="Rmk'.$row['account'].'"   value="'.$row['accountname'].'"  />
				<td>
				    <input type="hidden"  name="DeCr'.$row['account'].'" id="DeCr'.$row['account'].'"    value="'.($row['total']>0?"1":"-1").'" />
					<input type="hidden"  name="ToAct'.$row['account'].'" id="ToAct'.$row['account'].'"    value="'.$SettleArr[$row['account']][0].'" />
					'.$SettleArr[$row['account']][1]."</td>				
				
			</tr>";	
				$TotalAmo+=$row['total'];	
				$RowIndex++;		
		}	
		//<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Del=".$row['uploadid']."\" onclick=\"return confirm('你确认要结账吗!');\" >结账</a>
		   echo'<tr>
		        <td></td>
				<td ></td>			
				<td>合计</td>
				<td >'.($TotalAmo>0?"借":"贷").'</td>
				<td></td>
				<td></td>
				<td>'.abs($TotalAmo)."</td>	
				<td></td>				
				
			</tr>";	
			echo '</table>';
		
	//	echo'</form>';		
		//include('includes/footer.php');
		//exit;
	
	}
//}


	//月结账
if (isset($_POST['SettleUpdate'])){
  

		$InitialTab=explode(',',$_SESSION['WebERP']);
		
		$Status=0;
		for($i=1;$i<count($SettleTab);$i++){
			if ($InitialTab[$i]==0){
				//初始为0
				if ($SettleTab[$i]!=1){
					$Status=1;//有未结账项
					break;
				}
			}elseif ($InitialTab[$i]==-1){
				//初始为-1结内外账  标记为3为结账
				if ($SettleTab[$i]!=3){
					$Status=1;
					break;
				}
			}

		}

		prnMsg($Status);
		if ($Status==0){
			//本期结账完成,结转到下期,
			
				$sql="UPDATE `companies` SET lastsettleperiod=lastsettleperiod+1,settle='".$_SESSION['WebERP']."' WHERE coycode=".$_POST['UnitsTag'];
		
		}
		$result=DB_query($sql);
		if ($result){
			$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['settle']=$_SESSION['WebERP'];
			$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']=$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['lastsettleperiod']+1;
	
		}
	
	
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/GLSettleAccounts.php">';
	
  
}
echo'</div></form>';	
include('includes/footer.php');

?>
