
<?php
/* $Id: Z_AccountsInitial.php  ChengJiang $*/

/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:58
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-04-13 12:13:25
 */
include('includes/DefineJournalClass.php');
include('includes/session.php');
$Title = '系统初始维护';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');

		  
if (!isset($_POST['Dbase'])){
	$_POST['Dbase']=$_SESSION['DatabaseName'] ;	 		
}
if(!isset($_POST['CodeType'])){
	$_POST['CodeType']=0;
}
if (!isset($_POST['UnitsTag'])){
	$_POST['UnitsTag']=1;	 		
}
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme . 
		'/images/maintenance.png" title="' . $Title. '" />' . ' ' . 	$Title . '</p>';
echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	$db0 = mysqli_connect($host , $DBUser, $DBPassword, 'erp_gjw', $mysqlport);
	mysqli_set_charset($db0, 'utf8');
		  
	//连���数据库
	$db1 = mysqli_connect($host , $DBUser, $DBPassword, 'information_schema', $mysqlport);
	mysqli_set_charset($db1, 'utf8');
	/*
	$sql="SELECT TABLE_NAME, COLUMN_NAME, ORDINAL_POSITION, COLUMN_DEFAULT, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, CHARACTER_OCTET_LENGTH, COLUMN_TYPE, COLUMN_KEY,EXTRA 
			FROM COLUMNS 
			WHERE TABLE_SCHEMA='".$_POST['Dbase']."' AND TABLE_NAME='temp_gltrans'";
	$result=mysqli_query($db1,$sql);
	$TableCol=array();
	while($row=mysqli_fetch_array($result)){
		$TableCol[$row['COLUMN_NAME']]=array($row['COLUMN_TYPE'],$row['COLUMN_KEY'],$row['EXTRA'],0 );
	}*/
	//读取标准库中的初始科目
	$sql="SELECT `accountcode`, `accountname`, `group_`, `currcode`,cashflowsactivity FROM `chartmaster` WHERE 1";
	$result=mysqli_query($db0,$sql);

	while($row=mysqli_fetch_array($result)){	
		$Accounts[$row['accountcode']]=array($row['accountname'],$row['group_'],$row['currcode'],$row['cashflowsactivity']);
	}	
	//var_dump($ Accounts);	
	$sql="SELECT `TABLE_NAME`, `TABLE_TYPE`, `AccountType`, `CREATE_TIME`, `flag`, `notes` FROM `tablename` WHERE AccountType<>0";
	$result=mysqli_query($db0,$sql);
	if (!isset($_POST['Initial'])) {
		$sql="SELECT `coycode`, `coyname`, `gstno`, `companynumber`, `regoffice1`, `regoffice2`, `regoffice3`, `regoffice4`,`lastsettleperiod`, `settle`, `dbase`, `taxtype`, `unitstab` FROM `companies` WHERE coycode=1";
		$result=DB_query($sql);
		$row=DB_fetch_assoc($result);
		$_POST['CoyName']=$row['coyname'];
		$_POST['unitstab']=$row['unitstab'];
		$_POST['companynumber']=$row['companynumber'];
		$_POST['regoffice3']=$row['regoffice3'];
		$_POST['regoffice4']=$row['regoffice4'];
		$_POST['taxType']=$row['taxType'];
		if (!isset($_POST['taxType'])){
			$_POST['taxType']=2;
		}
	}
	echo '<br />
		  <div class="page_help_text">功能简介:账目初始资料录入  </br>
							<<<<<<<<该页面功能可以清除数据初始化，没有自动备份功能，不要尝试第二行按钮的操作>>>>>>>>>
							<br> 导入初始资料生成temp_gltrans,修改字段account,accountold,amount,name然后改名gltrans_initial
							<br> 导入历史凭证
							</div>';
	echo '<table class="selection">
		  <tr>
			  <th colspan="3">初始化设置</th>
		  </tr>
		  <tr>
		  <td>单元分组:</td>
		  <td >';
		    SelectUnitsTag(2);
	echo '</td>
			  </tr>
		  <tr>
			  <td>选择账期间:</td>
			  <td ><select name="StartPeriod" size="1" >';
			  $y=date('Y');			 
			  for ($i=($y-1);$i<=$y;$i++){
				
				  if ($_POST['StartPeriod']==$i){
					  echo  '<option selected="selected" value="' . $i . '">' .$i. '年1��</option>';
				  } else {
					  echo  '<option value="' . $i . '">' . $i . '年1月</option>';
				  }
			  }
			  echo '</select></td>
				  </tr>';
	echo '<tr>
			<td>数据录入期间:</td>
			<td ><select name="StartMth" size="1" >';
			$y=date('Y');
			
			for ($i=1;$i<=12;$i++){
			
				if ($_POST['StartMth']==$i){
					echo  '<option selected="selected" value="' . $i . '">' .$i. '月</option>';
				} else {
					echo  '<option value="' . $i . '">' . $i . '月</option>';
				}
			}
			echo '</select></td>
				</tr>';

	echo '<tr>
			<td>导入会计科目方式:</td>
			<td colspan="2">
					<input type="radio" name="CodeType" value="0"  '.($_POST['CodeType']==0 ? 'checked':"").' />仅会计科目
					<input type="radio" name="CodeType" value="1" '.($_POST['CodeType']==1 ? 'checked':"").' />含客户编码
			</td>
			</tr>';	
	echo '<tr>
			<td>企业名称:</td>
			<td colspan="2">
					
					<input type="text" maxlength="25"   size="25"name="CoyName" value="'.$_POST['CoyName'].'" />
			</td>
			</tr>';	
	echo '<tr>
			<td>企业简称:</td>
			<td colspan="2">
					
					<input type="text" maxlength="25"   size="25"name="unitstab" value="'.$_POST['unitstab'].'" />
			</td>
			</tr>';	
	echo '<tr>
			<td>企业信用代码:</td>
			<td colspan="2">
					
					<input type="text" maxlength="20"   size="20"name="companynumber" value="'.$_POST['companynumber'].'" />
			</td>
			</tr>';	
			echo '<tr>
			<td>企业法人:</td>
			<td colspan="2">
					
					<input type="text" maxlength="10"   size="10"name="regoffice3" value="'.$_POST['regoffice3'].'" />
			</td>
			</tr>';	
			echo '<tr>
			<td>主管会计:</td>
			<td colspan="2">
					
					<input type="text" maxlength="10"   size="10"name="regoffice4" value="'.$_POST['regoffice4'].'" />
			</td>
			</tr>';	
			echo '<tr>
			<td>税控盘选择:</td>
			<td colspan="2">
					<input type="radio" name="taxType" value="0"  '.($_POST['taxType']==2 ? 'checked':"").' />百望(黑色)
					<input type="radio" name="taxType" value="1" '.($_POST['taxType']==1 ? 'checked':"").' />金税盘(白色)
			</td>
			</tr>';	
			if (isset($_POST['SearchTable']) ){
	echo '<tr>
			<th colspan="3">初始化表:</th>					
			</tr>';
				$t=1;
			while($row=mysqli_fetch_array($result)){
				//	$TableCol[$row['COLUMN_NAME']]=array($row['COLUMN_TYPE'],$row['COLUMN_KEY'],$row['EXTRA'],0 );
				if ($k==1){
					echo '<tr class="EvenTableRows">';
					$k=0;
				} else {
					echo '<tr class="OddTableRows">';
					$k=1;
				}	
				echo'
				<td colspan="2">'.$t.'   '.strtoupper($row['TABLE_NAME']).'	
					<input type="hidden" name="TABLE'.$t.'" value="'.$row['TABLE_NAME'].'"  checked  /></td>
				<td >
					<input type="checkbox" name="Click'.$t.'" value="'.$row['TABLE_NAME'].'"  checked  />					     
				</td>
				</tr>';
				$t++;
			}
		}	
	echo'</table>';
	
   $Initial=explode(',',$_SESSION['AccountsInitial'][$_POST['UnitsTag']]);
   $sql="show tables like 'gltrans_initial'";
   $result=DB_query($sql);
   $tablerow=DB_fetch_assoc($result);
   
  if (!isset($tablerow)){
		prnMsg('初始化系统的表gltrans_initial不存在!','info');
		include('includes/footer.php');
		exit;
  }
 
	echo'<br />
		<div class="centre">';
		echo'<input type="submit" name="Search" value="初始资料查询"/>
			 <input type="submit" name="SearchTrans" value="查询历史凭证"/>
			 <input type="submit" name="SearchTable" value="查询初始表"/>
			<br>';
			 if (isset($_POST['Search']) ){
				echo '<input type="submit" name="UpdateAccounts" value="更新科目和期初余额"/>';
			}
			if (isset($_POST['SearchTrans'])){
				echo'<input type="submit" name="WriteTrans" value="写入历史凭证"/>	';				
			}
		
			if (isset($_POST['SearchTable']) ){
				echo'<input type="submit" name="ClearTable" value="清空29表数据"/>';
			}
			
			echo '<br>';
		echo'
				<input type="submit" name="Initial" value="初始化期间和起账日期"/>
				<input type="submit" name="AccountsWrite" value="写入初始科目"/>';	
	echo'</div>';
	prnMsg($_POST['CoyName'].'<br/>'.$_POST['companynumber'].'<br/>'.$_POST['unitstab'].'<br/>'.
		$_POST['regoffice3'].'<br/>'.
	$_POST['regoffice4'].'<br/>'.
	$_POST['taxType']);
if (isset($_POST['ClearTable'])) {
		prnMsg('清除29表内容！');
	$ActTable=array("bankaccounts","banktransaction","chartmaster","gltrans","fixedassets","fixedassettrans","audittrail",					"accountsubject","accountprint","debtorsmaster","debtortrans","purchorders","purchorderdetails","currtrans","locstock","					grns","	registeraccount","registername","stockmoves","stockrequest","stockrequestitems","suppliers","supptrans","woitems","workorders","periods","salesorderdetails","stockmaster","salesorders");
	foreach ($ActTable as $val) {
	
			$SQL="TRUNCATE TABLE ".$val;
			$Result=DB_query($SQL);
			if($Resulat){
				$I++;
			}
			prnMsg($SQL);
		
		
	}
	
	

}elseif (isset($_POST['Initial'])) {
	//检测periods  是否有数据
	$SQL="SELECT count(*) cnt FROM `periods` WHERE 1";
	$Resulat=DB_query($SQL);
	$periodRow=DB_fetch_assoc($Resulat);

	if ($periodRow['cnt']>0){
		
			prnMsg($periodRow[0].'初始化参数,已经完成！','info');	
	}else{
	
		//参数写入,不���入不能进下一步
		$Result = DB_Txn_Begin();
		$dt=date('Y-m-t',mktime(1,1,1,11,1,($_POST['StartPeriod']-1)));
        $SQL="INSERT INTO `periods`(`periodno`, `lastdate_in_period`)
					 VALUES(-1,'".$dt."')";
		$Resulat=DB_query($SQL);
					 
		$dt0=date('Y-m-t',mktime(1,1,1,12,1,($_POST['StartPeriod']-1)));
		$SQL="INSERT INTO `periods`(`periodno`, `lastdate_in_period`)
		           VALUES(0,'".$dt0."')";
		$Resulat=DB_query($SQL);

		$SelectDate=date('Y-m-t',mktime(1,1,1,$_POST['StartMth'],1,($_POST['StartPeriod'])));
		if ($_POST['StartMth']==1){
			$BeforeDate=$dt0;
			$BeforePrd=0;
		}else{
			$BeforeDate=date('Y-m-t',mktime(1,1,1,$_(POST['StartMth']-1),1,($_POST['StartPeriod'])));
			$BeforePrd=0;
		}
		//起始期间��期,起始起��余�� 和期间
		$_SESSION['StartPeriod'][$_POST['UnitsTag']]=array($_POST['StartMth'],$SelectDate,$BeforePrd,$BeforeDate);
		$p=1 ;
		
		for($i=$_POST['StartPeriod'];$i<=($_POST['StartPeriod']+2);$i++){
           for($m=1;$m<=12;$m++){
			$prddt=date('Y-m-t',mktime(1,1,1,$m,1,$i));
			$SQL="INSERT INTO `periods`(`periodno`, `lastdate_in_period`)
			      VALUES(".$p.",'".$prddt."')";
			$Resulat=DB_query($SQL);
			//prnMsg($SQL);
			$p++;
		   }
		}
		
		
		$SQL="UPDATE `myconfig` SET confvalue='".$_SESSION['StartPeriod'][$_POST['UnitsTag']][0]."' WHERE confname='printprd'";
		$Resulat=DB_query($SQL);
		$SQL="UPDATE `myconfig` SET confvalue=1 WHERE confname='printflag'";
		$Resulat=DB_query($SQL);
		$SQL="UPDATE `config` SET confvalue='".$dt0."' WHERE confname='ProhibitPostingsBefore'";
		$Resulat=DB_query($SQL);
		
		$SQL="UPDATE `companies` SET  `coyname`='".$_POST['CoyName']."',`companynumber`='".$_POST['companynumber']."',`regoffice3`='".$_POST['regoffice3']."',`regoffice4`='".$_POST['regoffice4']."',`lastsettleperiod`=1,
		`settle`='1,0,0,0,0,-1,-1,2', `dbase`='".$_SESSION['DatabaseName']."', `taxtype`='".$_POST['taxType']."', `unitstab`='".$_POST['unitstab']."' WHERE  coycode=1";
		$Resulat=DB_query($SQL);
		$result = DB_Txn_Commit();
		

	}

}elseif (isset($_POST['Search'])|| isset($_POST['UpdateAccounts']))  { 
	/**更新科目和期初余额
	 * 插入科目  及初始余额  插入银行账户
	 *  */
	//添加验证表存在
	$sql="show tables like 'gltrans_initial'";
	$result=DB_query($sql);
	$tablerow=DB_fetch_assoc($result);
	$StartTotal=0;
   if (isset($tablerow)){
		$sql="SELECT *    FROM `gltrans_initial` ";
				//WHERE periodno=0";
		$result=DB_query($sql);
		
		echo'<br /><table cellpadding="2">';
		echo '<tr>
				<th>序号</th>
				<th>旧科目编码</th>				
				<th>新科目编码</th>
				<th>总账科目</th>
				<th>明细科目名</th>
				<th>币种</th>
				<th>初始外币额</th>
				<th>初始余额</th>
			
				<th></th>				
			</tr>';
			$k = 0; //row counter to determine background colour
			$RowIndex =1;		
		while ($myrow = DB_fetch_array($result)  ){
			
			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}	
			echo'<td >'.$RowIndex.'</td>
				<td >' . $myrow['ACCOUNTOLD'] . '</td>
				<td > 
					<input type="input" name="Account'.$RowIndex.'" value="'.trim($myrow['ACCOUNT']).'" ></td>
				<td>'. $Accounts[ substr($myrow['ACCOUNT'],0,4)][0]. '
				</td>
				<td >
					<input type="input" name="Name'.$RowIndex.'" value="'. $myrow['NAME'].'" ></td>
				<td>'.$myrow['currcode']. '</td>';
				$CurrAmount=0;
				if (round($myrow['curramount'],2)!=0){
					$CurrAmount=locale_number_format($myrow['curramount'],POI);
				}
				echo'<td class="number">' . ($CurrAmount) . '
						</td>';				
					$Amount=0;
			if (round($myrow['amount'],2)!=0){
				$Amount=locale_number_format($myrow['amount'],POI);
			}
			echo'<td class="number">' . ($Amount) . '
					</td>';				
			echo'<td >
					<input type="checkbox" name="CHK'.$RowIndex.'" value="'.$Accounts[ substr($myrow['ACCOUNT'],0,4)][0].'^'.$myrow['NAME'].'^'.$myrow['currcode'].'^'.$Amount.'^'.$CurrAmount.'" checked>
				</td>
				</tr>';					
			$StartTotal+=$myrow['amount'];		
			$RowIndex = $RowIndex + 1;
		}
		echo '<tr>
				<th></th>
				<th colspan="5"></th>
				<th>'.round($StartTotal,2).'</th>
				<th>'.round($EndTotal,2).'</th>	
				<th> </th>				
			</tr>';
				echo '</table>';
	}else{
		prnMsg("科目初始化表 gltrans_initial不存在！",'info');
	}
	//prnMsg(round($StartTotal,2));
	if (round($StartTotal,2)!=0){		
		prnMsg("科目初始化余额不平衡,差异额：".round($StartTotal,2).".请检查后执行！",'warn');
		include('includes/footer.php');
		exit;
	}
	$SQL="SELECT count(*) cnt FROM `chartmaster` WHERE 1";
	$Resulat=DB_query($SQL);
	$chartRow=DB_fetch_assoc($Resulat);
	if ($chartRow['cnt']>150  ){
		prnMsg("科目初始化已经完成！",'info');
	}else{
		if (isset($_POST['UpdateAccounts'])) {
			
			//$Initial=explode(',',$_SESSION['AccountsInitial'][$_POST['UnitsTag']]);
			
				prnMsg('科目入');			
				//插入基础默认科目
			if ($chartRow['cnt']==0  &&	$chartRow['cnt']<130 ){ 
				foreach($Accounts as $key=>$row){
					$SQL="INSERT IGNORE INTO `chartmaster`(`accountcode`,
													`accountname`,
													`group_`,
													`currcode`,
													`cashflowsactivity`,
													`tag`,
													`crtdate`,
													`userid`,
													`low`,
													`used`)
											VALUES('".$key."',
												'".$row[0]."',
												'".$row[1]."',
												'".$row[2]."',
												'".$row[3]."',
												'".$_POST['UnitsTag']."',
												'".$StartPeriod['lastdate_in_period']."',
												'auto',
												'0',
												'0'	)";
					$Resulat=DB_query($SQL); 
				
					if ($Result){
						$rw++;
					}
					
				
				}	
			}
			   // var_dump($Accounts);
			if ($chartRow['cnt']>130 || $rw>130){ 
					$GLPeriod=$_POST['StartMth']-1;
					if ($GLPeriod==0){
						$GLDate=date('Y-m-t',mktime(1,1,1,12,1,($_POST['StartPeriod']-1)));
					}else{
					
						$GLDate=date('Y-m-t',mktime(1,1,1,$GLPeriod,1,($_POST['StartPeriod'])));
					}
					prnMsg('你导入的初始余额期间:'.$GLDate,'info');
				
					$Total=0;
					$p=0;
					$Result = DB_Txn_Begin();
					foreach ($_POST as $FormVariableName =>$Qty) {
					  $p++;
					 
						$n=mb_substr($FormVariableName,7);							
						if (mb_substr($FormVariableName, 0, 7)=='Account') {
						
							$n=mb_substr($FormVariableName,7);
						
							if (isset($_POST['CHK' .$n ])){

								$Act =$_POST['Account' .$n ];
								$actstr=explode('^',$_POST['CHK' .$n ]);
								$ActName =$actstr[0];
								$Name =$actstr[1];
								$CurrCode=$actstr[2];//$Accounts[substr($Act,0,4)][2];

								$Amount=filter_number_format($actstr[3]);
								$CurrAmount=filter_number_format($actstr[4]);
								$Group=$Accounts[substr($Act,0,4)][1];
								$CashFlow=$Accounts[substr($Act,0,4)][3];

								//导入模							
									$SQL="INSERT  IGNORE INTO `chartmaster` (`accountcode`,
																	`accountname`,
																	`group_`,
																	`currcode`,
																	`cashflowsactivity`,
																	`tag`,
																	`crtdate`,
																	`userid`,
																	`low`,
																	`used`)
																VALUES ('".$Act."',
																'".$ActName."-".$Name."',
																'".$Group."',
																'".$CurrCode."',
																'".$CashFlow."',
																'".$_POST['UnitsTag']."',
																'".$StartPeriod['lastdate_in_period']."',
																'auto',
																'0',
																'0')"; 
									$Resulat=DB_query($SQL); 
									if (substr($Act,0,4)=='1002'){
									 $SQL="INSERT INTO `bankaccounts`( `accountcode`,
																		`currcode`,
																		`invoice`,
																		`bankaccountcode`,
																		`bankaccountname`,
																		`bankaccountnumber`,
																		`bankaddress`,
																		`importformat`)
									 	                        VALUES ('".$Act."',
																 '".$CurrCode."',
																 '1',
																 '',
																'".$Name."',
																'',																
																'',
																'0')"; 
										$Resulat=DB_query($SQL); 
									}
								if (round($Qty,2)!=0){										
									$SQL="INSERT INTO `gltrans`(`type`,
															`transno`,
															`typeno`,
															`printno`,
															`prtchk`,
															`chequeno`,
															`trandate`,
															`periodno`,
															`account`,
															`narrative`,
															`amount`,
															`posted`,
															`jobref`,
															`tag`,
															`userid`,
															`flg`)
														VALUES('0',
														'0',
														'".$n."',
														'0',
														'0',
														'0',
														'".$GLDate."',
														'".$GLPeriod."',
														'".$Act."',
														'起始余额',
														'".$Amount."',
														0,
														0,
														'".$_POST['UnitsTag']."',
														'auto',
														'1')";
									$Resulat=DB_query($SQL); 
									if ($CurrCode!=CURR){
										if ($Amount!=0)
										$rate=abs(round($CurrAmount/$Amount,6));
										else
										$rate=0;
										$SQL="INSERT INTO `currtrans`( `transdate`,
																		`transno`,
																		`period`,
																		`account`,
																		`exrate`,
																		`amount`,
																		`examount`,
																		`currtype`,
																		`ref`,
																		`currcode`,
																		`flg`)
																		VALUES(
																			'".$GLDate."',
																			'".$n."',
																			'".$GLPeriod."',
														                    '".$Act."',
																			'".$rate."',
																			'".$Amount."',
																			'".$CurrAmount."',
																			0,
																			0,
																			'".$CurrCode."',
																			1)";
										$Resulat=DB_query($SQL); 
									}
								
									//prnMsg($SQL);//$Act.'-'.$_POST['CHK' .$n ].'-');
								$Total+=filter_number_format($_POST['Amount' .$n ]);
								}
								
								
							}
						}
					}//end for			
			}
				if ($Total==0){
					$Result = DB_Txn_Commit();
				}
		}  
	}
}elseif(isset($_POST['SearchTrans'])){

	$sql="SELECT *
	        FROM `temp_gltrans` ";
			$result=DB_query($sql);
	
	echo'<br /><table cellpadding="2">';
	echo '<tr>
			<th>序号</th>
			<th>旧科目编码</th>				
			<th>新科目编码</th>
			<th>总账科目</th>
			<th>明细科目名</th>
			<th>币种</th>
			<th>初始余额</th>
			<th>期末余额</th>	
			<th></th>				
		</tr>';
		$k = 0; //row counter to determine background colour
		$RowIndex =1;
		$StartTotal=0;
        $EndTotal=0; 
	while ($myrow = DB_fetch_array($result)  ){
		
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}	
		echo'<td >'.$RowIndex.'</td>
		    <td >' . $myrow['accountold'] . '</td>
			<td > 
			     <input type="input" name="Account'.$RowIndex.'" value="'.trim($myrow['account']).'" ></td>
			<td>'.$myrow['actname']. '
			<input type="hidden" name="ActName'.$RowIndex.'" value="'. $myrow['actname'].'" ></td>
			<td >
			     <input type="input" name="Name'.$RowIndex.'" value="'. $myrow['name'].'" ></td>
			<td>'.$myrow['currcode']. '
			     <input type="hidden" name="CurrCode'.$RowIndex.'" value="'. $myrow['currcode'].'" ></td>
			<td class="number">' . isZero(locale_number_format($myrow['amount'],$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['decimalplaces'])) . '
			     <input type="hidden" name="Amount'.$RowIndex.'" value="'.round($myrow['amount'],2).'" ></td>
			<td class="number">' . isZero(locale_number_format($myrow['endamount'],$_SESSION['CompanyRecord'][$_POST['UnitsTag']]['decimalplaces']) ). '
			     <input type="hidden" name="EndAmount'.$RowIndex.'" value="'. $myrow['endamount'].'" ></td>
		    <td >
				 <input type="checkbox" name="CHK'.$RowIndex.'" value="1" checked>
			 </td>
			 </tr>';	
			// if (round($myrow['amount'],2)!=0){
			//	$Amount[$myrow['account']]=array($myrow['amount'],0);
				
		$StartTotal+=$myrow['amount'];
		$EndTotal+=$myrow['endamount'];		
		$RowIndex = $RowIndex + 1;
	}
	echo '<tr>
			<th></th>
			<th colspan="5"></th>
			<th>'.round($StartTotal,2).'</th>
			<th>'.round($EndTotal,2).'</th>	
			<th> </th>				
		</tr>';
			echo '</table>';

}elseif(isset($_POST['WriteTrans'])){
	prnMsg('Write');
	$sql="SELECT  `accountold`, `account` 
	FROM `temp_gltrans` 
	WHERE periodno=0";
	$result=DB_query($sql);
	$ActRule=[];
	while ($row = DB_fetch_array($result)  ){
		$ActRule[$row['accountold']]=$row['account'];
	}
	$sql="SELECT `accountold`,
					`trandate`,
					`periodno`,
					`transno`,
					`gltype`,
					`account`,
					`narrative`,
					`actname`,
					`name`,
					`currcode`,
					`exrate`,
					`examount`,
					`debit`,
					`credit`,
					`amount`,
					`endamount`,
					`chequeno`,
					`userid`,
					`tag`,
					`flg` 
			FROM `temp_gltrans` 
			WHERE periodno>0";
	$result=DB_query($sql);
	$CheckAct=0;
	while ($row = DB_fetch_array($result)  ){
		if (!isset($ActRule[$row['accountold']])){
			$CheckAct++;
		}
	}
	$rw=0;
	DB_data_seek($result,0);
	if ($CheckAct==0){
		while ($row = DB_fetch_array($result)  ) {
			   //$rw++;
			   //if ($rw>10){
			   //break;
			   $flg=1;
			   if ($row['flg']==-1){
				   $flg=$row['flg'];
			   }
		      $SQL="INSERT INTO `gltrans`(`type`,
										`transno`,
										`typeno`,
										`printno`,
										`prtchk`,
										`chequeno`,
										`trandate`,
										`periodno`,
										`account`,
										`narrative`,
										`amount`,
										`posted`,
										`jobref`,
										`tag`,
										`userid`,
										`flg`
									)
									VALUES(	0,
										   '".$row['transno']."',
										   '".$row['transno']."',
											0,
											0,
											'".$row['chequeno']."',
											'".$row['trandate']."',
											'".$row['periodno']."',
											'".$ActRule[$row['accountold']]."',
											'".$row['narrative']."',
											'".$row['amount']."',
											1,
											0,
											'".$row['tag']."',
											'".$row['userid']."',
											'".$flg."')";
			//			prnMsg($SQL);
		    $Result=DB_query($SQL);
		}
						
					
				
	}else{
		prnMsg('导入��证中有'.$CheckAct.' ��老科目没有对应新科目','warn');
	}
}
include('includes/footer.php');
function GLType($resul1,&$typarr){
	  $jtyp=0;
	  $dtyp=0;
	  $accarr=array();
	  $i=0;
	  $jd=0;
	  $typ=0;
	  $jdt=0;
	
	  while ( $val = DB_fetch_array($resul1) ) {
		$str.=trim($val['account']);
       if (substr($val['account'],0,4)=='1001'||substr($val['account'],0,4)=='1002'){
		   if (($val['amount']>0 &&$val['flg']==1)||($val['amount']<0 &&$val['flg']==-1)){
			   $jtyp=1;//收款
		   }elseif (($val['amount']<0 &&$val['flg']==1)||($val['amount']>0 &&$val['flg']==-1)){
		       $dtyp=2;//付款
		   }
	   }else{//非现金客户
		if (($val['amount']>0 &&$val['flg']==1)||($val['amount']<0 &&$val['flg']==-1)){
			$jdt=1;
		}else if (($val['amount']<0 &&$val['flg']==1)||($val['amount']>0 &&$val['flg']==-1)){
			$jdt=-1;

		}
           $accarr[$i]=array('acc'=>substr($val['account'],0,4),'jd'=>$jdt);
	       $i++;
		}
	}
	$jd=$jtyp+$dtyp;   
	if($jd==1||$jd==2){//收款
		for($a=0;$a<$i;$a++){//移除现��客户后
		   for($b=0;$b<count($typarr);$b++){//非现金科目对应类别
			   if(strpos('-'.$typarr[$b]['toaccount'],$accarr[$a]['acc'])>0 &&$typarr[$b]['GLType']==1){
				   $typ=$typarr[$b]['typeid'];
				   if (strlen($typarr[$b]['toaccount'])==4){
					   break;
				   }

			   }
		   }
		}
	
	
	}elseif($jd==3){
		$typ=6;//专户
	}else{
		$tyarr=array();
		for($a=0;$a<$i;$a++){
			for($b=0;$b<count($typarr);$b++){
				if(strpos('-'.$typarr[$b]['toaccount'],$accarr[$a]['acc'])>0 &&$typarr[$b]['GLType']==0){
				array_push($tyarr,$typarr[$b]['typeid']);
			}
			}
		 }
		 $tarr=array_count_values($tyarr);//统计个数
		 $typ= array_search(max($tarr),$tarr);//最大值对应键

	}
	if (empty($typ)){
		$typ=0;
	}
	return $typ;//.'A'.$jtyp.'+'.$dtyp.'='.$jdt.'^'.$str.'~';
	//return '{'.$str.'}';
}
function countprd($dt){
	$year=((int)substr($_SESSION['lastdate'],0,4));//取得年份

	$month=((int)substr($_SESSION['lastdate'],5,2));//取得月份
	$year1=((int)substr($dt,0,4));//取得年份

	$month1=((int)substr($dt,5,2));//取得月份
	$prd=$_SESSION['period']-($year-$year1)*12-($month-$month1);

   return $prd;

}
function IsBankAccount($Account) {
	global $db;

	$sql ="SELECT accountcode FROM bankaccounts WHERE accountcode='" . $Account . "'";
	$result = DB_query($sql);
	if (DB_num_rows($result)==0) {
		return false;
	} else {
		return true;
	}
}

?>
