<?php
/* $Id: GLAccounts.php  $*/
/*
 * @Author: ChengJiang 
 * @Date: 2019-01-23
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2020-03-12 05:29:03
 */
include('includes/session.php');
include ('includes/GLAccountFunction.php');
$Title ='会计科目维护';// _('Chart of Accounts Maintenance');
/* Manual links before header.php */
$ViewTopic= 'GeneralLedger';// Filename in ManualContents.php's TOC.
$BookMark = 'GLAccounts';// Anchor's id in the manual's html document.

if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}
if (isset($_GET['GLreturn'])){
	$_SESSION['GLreturn']=$_GET['GLreturn'];
}

if (!isset($_POST['currcode'])){
	$_POST['currcode']=$_SESSION['CompanyRecord'][1]['currencydefault'];
}
if (!isset($_POST['state'])){
	$_POST['state']=0;
}
if (!isset($_POST['UnitsTag'])){
    $_POST['UnitsTag']=1;		 
}
	
	$InputError = 0; 	
	if (!isset($_POST['CSV'])) {
		include('includes/header.php');
		echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
				_('General Ledger Accounts') . '" />' . ' ' . $Title . '</p>';
		echo'<form id="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" method="post" >
			<div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="state" value="' . $_POST['state'] . '" />
			<input type="hidden" name="clicksave" value="' . $_POST['Save'] . '" />
			<input type="hidden" name="Account" value="' . $_POST['Account'] . '" />
			<input type="hidden" name="AddAccount" value="' . $_POST['AddAccount'] . '" />';
		//first off validate inputs sensible
	
		if (empty($SubNoRule)){
	
			$SQL="SELECT  confname,  confvalue FROM myconfig WHERE conftype=1 OR (conftype=0 AND confname IN('SetAccountName','AccountSize','AccountNameSize')) ";
			$Result = DB_query($SQL);
			while ($row = DB_fetch_array($Result)) {		
					$SubNoRule[$row['confname']]=explode(',',$row['confvalue']);	
			}
		}	
		//var_dump($SubNoRule);
		if (isset($_POST['Save'])) {
			//prnMsg('52-save');
			if (!empty($_POST['SubAccount'])){
				if (mb_strlen($_POST['SubAccount']) <($SubNoRule['AccountSize'][0])) {
					$InputError = 1;
					prnMsg('会计科目子目编码必须为'.$SubNoRule['AccountSize'][0].'-'.$SubNoRule['AccountSize'][1].'个字符!','warn');
				}elseif  (mb_strlen($_POST['SubAccount']) > ($SubNoRule['AccountSize'][1])){
					$InputError = 1;
					prnMsg('会计科目子目编码必须为'.$SubNoRule['AccountSize'][0].'-'.$SubNoRule['AccountSize'][1].'个字符!','warn');
				}
			}
			if ($InputError==0 && !empty($_POST['SubAccount'])){
				//下级科目必须是数字，总长度小于20 
			   //
				$sql="SELECT  COUNT(*)  FROM  chartmaster where accountcode = '".trim($_POST['Account']).trim($_POST['SubAccount'])."';";
				$result = DB_query($sql,$ErrMsg); 
				$myrow = DB_fetch_row($result);
				if ($myrow[0]==0){
					$InputError = 0;
					$sql="SELECT group_ ,accountname FROM chartmaster   where accountcode like '".trim($_POST['Account'])."' limit 1 ";
					$ErrMsg = _('Could not query the account because');
					$result = DB_query($sql,$ErrMsg); 
					$myrow = DB_fetch_array($result);
					$group=$myrow['group_'];	  	
					$accountcode=trim($_POST['Account']).trim($_POST['SubAccount']);
					$accountname=$myrow['accountname'];
				}else{			
					prnMsg(	'你输入的科目代码已经存在！','info');
				}
			}
			if (mb_strlen($_POST['AccountName']) <$SubNoRule['AccountNameSize'][0]) {
				$InputError = 1;
				prnMsg('会计科目名称必须为'.$SubNoRule['AccountNameSize'][0].'-'.$SubNoRule['AccountNameSize'][1].'个字符!','warn');
			}elseif  (mb_strlen($_POST['AccountName']) >$SubNoRule['AccountNameSize'][1]){
			   $InputError = 1;
			   prnMsg('会计科目名称必须为'.$SubNoRule['AccountNameSize'][0].'-'.$SubNoRule['AccountNameSize'][1].'个字符!','warn');
			}
			if ($InputError==0){
				//prnMsg($SubNoRule['SetAccountName'][0]);
				//下级科目必须是数字，总长度小于20 
				//prnMsg(gettype($_POST['SubAccount']).'89');
				$ACCarr=array("1122","1221","2202","2241","1121");
				if (in_array($_POST['Account'],$ACCarr)){
				    $sql="SELECT  accountname  FROM  chartmaster where length(accountcode)>4 AND LEFT(accountcode,4) IN ('1122','1221','2202','2241')";
				}else{
					$sql="SELECT  accountname  FROM  chartmaster where accountcode LIKE '".$_POST['Account']."_%'";
				}
				$result = DB_query($sql,$ErrMsg); 
				$jg=90;
				$rr=0;			
				$Msg='';
				$i=0;
				if (in_array($_POST['Account'],$ACCarr)){
					while ($row = DB_fetch_row($result)){	
						$ff=0;
						$i++;
						$accstr=substr($row[0],(strpos($row[0],'-')+1));
						similar_text($accstr,$_POST['AccountName'],$ff);
							if ($ff>$jg  ){
								$rr++;
								if ($rr<5){
								$Msg.=$row[0].'<br>';
								}
							} 						
						}
						//prnMsg($accstr);
						if ($rr>0){
							$InputError = 1;
							prnMsg(	'你输入的科目名称近似的'.$rr.'条,<br>'.$Msg.'......！','info');
						}//
				}
			}
			//exit;
		}

	if (isset($_POST['Save']) AND $InputError ==0) {
			//prnMsg('126下级科目手动填入');
			
			//$stlacc=array("0"=>"1122","1"=>"1221","2"=>'2202',"3"=>'2241',"4"=>"1121","5"=>"2201");
        		// 	自动生成号码
			if ($InputError==0 AND $_POST['SubAccount']=='' AND  !isset($SelectedAccount) AND $SelectedAccount=="" ){
				$accountcode=GetAccount($_POST['Account'],$SubNoRule);
			}
		   
			  if ( isset($SelectedAccount)){
				 $_POST['Account']=substr($SelectedAccount,0,4);
			  }
			  $sql="SELECT group_ ,accountname FROM chartmaster   where accountcode like '".trim($_POST['Account'])."' limit 1 ";
				 $ErrMsg = _('Could not query the account because');
			 
			  $result = DB_query($sql,$ErrMsg); 
			  $myrow = DB_fetch_array($result);
			  $group=$myrow['group_'];	  	
			  $accountname=$myrow['accountname'];
			
		   if (isset($SelectedAccount) AND $InputError !=1) {
			  
			   if (strlen($SelectedAccount)!=4){
					$sql = "UPDATE chartmaster SET accountname='" . $accountname.'-'.$_POST['AccountName'] . "',
									group_='" . $group . "',
									crtdate='".date("Y-m-d")."',
									currcode='".$_POST['currcode']."',
									used='".$_POST['state']."',
									tag=	'".$_POST['UnitsTag']."',
									userid='".$_SESSION['UserID']."'
							WHERE accountcode ='" . $SelectedAccount . "'";
					$ErrMsg = _('Could not update the account because');
					$result = DB_query($sql,$ErrMsg);
					$sql="UPDATE registername SET custname='" . $_POST['AccountName'] . "',
					                                 tag=	'".$_POST['UnitsTag']."'
											   WHERE account='" . $SelectedAccount . "'";
					$result = DB_query($sql,$ErrMsg);
					prnMsg (_('The general ledger account has been updated'),'success');
				}else {
						prnMsg ('总账科目不能修改！','info');
				}
		   }elseif ($InputError ==0 AND  !isset($SelectedAccount) AND $SelectedAccount=="") {
			
			//SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries 
			   if ($accountcode!=''){
					$ErrMsg = _('Could not add the new account code');
					$sql = "INSERT INTO chartmaster (accountcode,
													accountname,
													group_,
													crtdate,
													currcode,
													used,
													tag,
													userid)
								VALUES ('" . $accountcode . "',
										'" . $accountname.'-'. match_chinese($_POST['AccountName'] ). "',
										'" .$group . "',
										'".date("Y-m-d")."',
										'".$_POST['currcode']."',
										'".$_POST['state']."',
										'".$_POST['UnitsTag']."',
										'".$_SESSION['UserID']."')";
				
					$result = DB_query($sql,$ErrMsg);
				
					if (in_array($_POST['Account'],$ACCarr)){
						//往来客户添加客户
						$sql="INSERT INTO registername(	custname,
															tag,
															account,
															regdate,
															custtype,
															flg,
															userid)
												VALUES ('" . match_chinese($_POST['AccountName']) . "',
												'".$_POST['UnitsTag']."',
												'" . $accountcode . "',									
												'".date("Y-m-d")."',
													'0',
													'0'	,
													'".$_SESSION['UserID']."')";
						$result = DB_query($sql,$ErrMsg);
					
						//缺少供应商及客户表添加
					}
					prnMsg($accountcode.$_POST['AccountName'] .'-'._('The new general ledger account has been added'),'success');
			   }else{
					prnMsg('科目输入异常！','warn');   
			   }
		 }
			unset ($_POST['Group']);
			unset ($_POST['AccountCode']);
			unset ($_POST['AccountName']);
			unset ($_GET['SelectedAccount']);
			unset($SelectedAccount);
			unset($_POST['SelectedAccount']);
			unset($_POST['AddAccount']);
    }
		if (isset($SelectedAccount) AND !isset($_GET['delete'])&& isset($_GET['SelectedAccount'])) {
			//editing an existing account
			$sql = "SELECT t1.accountcode, t1.accountname, t1.group_ ,t.accountname  parentname ,t1.currcode ,t1.used
			FROM chartmaster t1  INNER JOIN  chartmaster t on left(t1.accountcode,4)=t.accountcode WHERE  t1.accountcode='" . $SelectedAccount ."'";
			$result = DB_query($sql);
			$myrow = DB_fetch_array($result);
			$_POST['state']=$myrow['used'];
			$_POST['currcode']=$myrow['currcode'];
			$_POST['AccountCode'] = $myrow['accountcode'];
			$_POST['AccountName']	=  str_replace($myrow['parentname'].'-','',$myrow['accountname']);
			$_POST['Group'] = $myrow['group_'];
			echo '<input type="hidden" name="SelectedAccount" value="' . $SelectedAccount . '" />';
			echo '<input type="hidden" name="AccountCode" value="' . $_POST['AccountCode'] .'" />';
			echo '<table class="selection">
					<tr>
					   <td>' . _('Account Code') . ':</td>
					   <td>' . $_POST['AccountCode'] . '</td>
					   <td>'.'科目组：' . $_POST['Group'] . '</td></tr>';
		} else {
			echo '<table class="selection">';
			$sql="SELECT t3.accountname, t3.accountcode ,t3.currcode
			FROM chartmaster t3 
			WHERE t3.accountcode  in(SELECT t.accountcode FROM chartmaster t WHERE LENGTH(t.accountcode)=4 or EXISTS
			( select * from chartmaster t1 where locate(t.accountcode,t1.accountcode,1)=1 AND (LENGTH(t1.accountcode)>LENGTH(t.accountcode)) )) AND LEFT(t3.accountcode,4)<>'1123' AND LEFT(t3.accountcode,4)<>'2203' ORDER BY t3.accountcode";
			$result=DB_query($sql);
			echo '<tr>
					<td>' . _('Account Code') . ':</td>
					<td><select name="Account" size="1" >';
			if (!isset($_POST['AddAccount'])||$_POST['AddAccount']!=='添加科目'){
				echo'<option value="ALL">' . _('All') . '</option>';
		    }
			while ($myrow=DB_fetch_array($result)){
				if (isset($_POST['Account']) AND $_POST['Account']==$myrow['accountcode']){
					echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'].' - ' .htmlspecialchars($myrow['accountname'], ENT_QUOTES,'UTF-8', false) . '</option>';
				} else {
					echo '<option value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'].' - ' .htmlspecialchars($myrow['accountname'], ENT_QUOTES,'UTF-8', false)  . '</option>';
				}
			}
			echo '</select></td>';
			if (isset($_POST['AddAccount'])&&$_POST['AddAccount']=='添加科目'){
				echo'<td colspan="2">子目编码<input type="text"  name="SubAccount" size="10" maxlength="10" value=""  pattern="^[1-9]*\d{'.$SubNoRule['AccountSize'][0].','.$SubNoRule['AccountSize'][1].'}?"　  title="输入'.$SubNoRule['AccountSize'][0].'-'.$SubNoRule['AccountSize'][1].'位数字！"   placeholder="为空自动编码"  /></td>';
			}else{
				echo'<td colspan="2"></td>';
			}
			echo'		</tr>';
		}
		if (!isset($_POST['AccountName'])) {
			$_POST['AccountName']='';
		}
		if (((isset($_POST['AddAccount'])&&$_POST['AddAccount']=='添加科目'))||isset($_GET['SelectedAccount']) ){
			echo '<tr>
				<td>' . _('Account Name') . ':</td>
				<td colspan="2">
					<input type="text" name="AccountName" size="50" maxlength="50"  value="' . $_POST['AccountName'] . '" pattern="^[\u4e00-\u9fa5a-zA-Z0-9\]\[\(\)\\.]+$" placeholder="输入汉字、数字、字母！" /> </td>
				</tr>';
		
			 echo '<tr>
					  <td>单元分组</td>
					  <td  colspan="2">';
					  SelectUnitsTag(2);
			
				  echo'</td></tr>';
			
				$sql="SELECT currency, currabrev FROM currencies ";
				$result=DB_query($sql);
			echo '<tr>
				<td>币种:</td>
				<td><select name="currcode" size="1" >';
				while ($myrow=DB_fetch_array($result)){
					if (isset($_POST['currcode']) AND $_POST['currcode']==$myrow['currabrev']){
						echo '<option selected="selected" value="' . $myrow['currabrev'] . '">'  .htmlspecialchars($myrow['currency'], ENT_QUOTES,'UTF-8', false) . '</option>';
					} else {
						echo '<option value="' . $myrow['currabrev'] . '">' .htmlspecialchars($myrow['currency'], ENT_QUOTES,'UTF-8', false)  . '</option>';
					}
				}
				echo '</select></td><td>';
				echo'<input  name="state"  type="radio" value="0"  '.($_POST['state'] == 0 ?'checked':''). ' />正常
				     <input  name="state"  type="radio" value="-1"  '.($_POST['state'] == -1 ?'checked':''). ' />临时
		        	 <input  name="state"  type="radio" value="-3"  '.($_POST['state'] == -3 ?'checked':''). ' />停用';	
				echo'<td>
					</tr>';
			}else{
			echo '<tr>
					<td>查询科目关键字</td>
					<td colspan="2">
					<input type="text" size="51" placeholder="' . _('Enter up to 50 alpha-numeric characters for the general ledger account name') . '" maxlength="50" name="searchname" value="' . $_POST['searchname'] . '" /> </td>
					</tr>';	
			}		
			//echo	'<td colspan="2"><input type="text" size="51" required="required" ' . (isset($_POST['AccountCode']) ? 'autofocus="autofocus"':'') . ' title="' . _('Enter up to 50 alpha-numeric characters for the general ledger account name') . '" maxlength="50" name="AccountName" value="' . $_POST['AccountName'] . '" /></td></tr>';
			echo'	</table>
				<br />
				<div class="centre">			
					<input type="submit" name="Search" value="查询科目" />	';
				if ( !isset($_POST['AddAccount'])|| $_POST['AddAccount']!="添加科目"||isset($_POST['Search'])||isset($_POST['Save'])){//&&!isset($_GET['SelectedAccount'])){
					echo'<input type="submit" name="AddAccount" value="添加科目" />	';
				}
			echo '<input type="submit" name="CSV" value="导出CSV" />';
            if (((isset($_POST['AddAccount']) && $_POST['AddAccount']=="添加科目")||isset($_GET['SelectedAccount']))&&!isset($_POST['Search'])&&!isset($_GET['delete'])&&!isset($_POST['Save'])){
				echo'		<input type="submit" name="Save" value="保存" />';
			}
			echo'	<br />	</div>';
			if(isset($_GET['GLreturn'])OR isset($_SESSION['GLreturn'])){
				echo '<div class="centre"><a href="JournalEntry.php?'.urldecode($_SESSION['GLreturn']).'" >' . '返回凭证录入'  . '</a></div><br />';
			}
	}/*
	if (abs($_POST['UnitsTag'])>0){
		$tag=abs($_POST['UnitsTag']);
	}else{
		$tag=1;

	}
	if ($_POST['UnitsTag']==0){
	   $tagusers=implode(",",$_SESSION[$_SESSION['UserID']]);
	}else{
	   $tagusers=$_POST['UnitsTag'];	
	}
	*/
if (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'
   
	// PREVENT DELETES IF DEPENDENT RECORDS IN 'GLTrans'
		$sql= "SELECT COUNT(*)
				FROM gltrans
				WHERE gltrans.account ='" . $SelectedAccount . "'";
		$ErrMsg = _('Could not test for existing transactions because');
		$result = DB_query($sql,$ErrMsg);
		$myrow = DB_fetch_row($result);
		if ($myrow[0]>0) {
			$CancelDelete = 1;
			prnMsg( _('Cannot delete this account because transactions have been created using this account'),'warn');
			echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('transactions that require this account code');
		} else {
			//PREVENT DELETES IF Company default accounts set up to this account
			$sql= "SELECT COUNT(*) FROM companies
					WHERE debtorsact='" . $SelectedAccount ."'
					OR pytdiscountact='" . $SelectedAccount ."'
					OR creditorsact='" . $SelectedAccount ."'
					OR payrollact='" . $SelectedAccount ."'
					OR grnact='" . $SelectedAccount ."'
					OR exchangediffact='" . $SelectedAccount ."'
					OR purchasesexchangediffact='" . $SelectedAccount ."'
					OR retainedearnings='" . $SelectedAccount ."'";
			$ErrMsg = _('Could not test for default company GL codes because');
			$result = DB_query($sql,$ErrMsg);
			$myrow = DB_fetch_row($result);
			if ($myrow[0]>0) {
				$CancelDelete = 1;
				prnMsg( _('Cannot delete this account because it is used as one of the company default accounts'),'warn');
			} else  {
				//PREVENT DELETES IF Company default accounts set up to this account
				$sql= "SELECT COUNT(*) FROM taxauthorities
					WHERE taxglcode='" . $SelectedAccount ."'
					OR purchtaxglaccount ='" . $SelectedAccount ."'";
				$ErrMsg = _('Could not test for tax authority GL codes because');
				$result = DB_query($sql,$ErrMsg);
				$myrow = DB_fetch_row($result);
				if ($myrow[0]>0) {
					$CancelDelete = 1;
					prnMsg( _('Cannot delete this account because it is used as one of the tax authority accounts'),'warn');
				} else {
		//PREVENT DELETES IF SALES POSTINGS USE THE GL ACCOUNT
					$sql= "SELECT COUNT(*) FROM salesglpostings
						WHERE salesglcode='" . $SelectedAccount ."'
						OR discountglcode='" . $SelectedAccount ."'";
					$ErrMsg = _('Could not test for existing sales interface GL codes because');
					$result = DB_query($sql,$ErrMsg);
					$myrow = DB_fetch_row($result);
					if ($myrow[0]>0) {
						$CancelDelete = 1;
						prnMsg( _('Cannot delete this account because it is used by one of the sales GL posting interface records'),'warn');
					} else {
		//PREVENT DELETES IF COGS POSTINGS USE THE GL ACCOUNT
						$sql= "SELECT COUNT(*)
								FROM cogsglpostings
								WHERE glcode='" . $SelectedAccount ."'";
						$ErrMsg = _('Could not test for existing cost of sales interface codes because');
						$result = DB_query($sql,$ErrMsg);
						$myrow = DB_fetch_row($result);
						if ($myrow[0]>0) {
							$CancelDelete = 1;
							prnMsg(_('Cannot delete this account because it is used by one of the cost of sales GL posting interface records'),'warn');
						} else {
		//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
							$sql= "SELECT COUNT(*) FROM stockcategory
									WHERE stockact='" . $SelectedAccount ."'
									OR adjglact='" . $SelectedAccount ."'
									OR purchpricecode='" . $SelectedAccount ."'
									OR issueglact='" . $SelectedAccount ."'
									OR wipact='" . $SelectedAccount ."'";
							$Errmsg = _('Could not test for existing stock GL codes because');
							$result = DB_query($sql,$ErrMsg);
							$myrow = DB_fetch_row($result);
							if ($myrow[0]>0) {
								$CancelDelete = 1;
								prnMsg( _('Cannot delete this account because it is used by one of the stock GL posting interface records'),'warn');
							} else {
									//PREVENT DELETES IF STOCK POSTINGS USE THE GL ACCOUNT
									$sql= "SELECT COUNT(*) FROM bankaccounts
									WHERE accountcode='" . $SelectedAccount ."'";
									$ErrMsg = _('Could not test for existing bank account GL codes because');
									$result = DB_query($sql,$ErrMsg);
									$myrow = DB_fetch_row($result);
									if ($myrow[0]>0) {
										$CancelDelete = 1;
										prnMsg( _('Cannot delete this account because it is used by one the defined bank accounts'),'warn');
									} else {
									//汇总科目不能删
										$sql= "SELECT count(*) FROM chartmaster where accountcode like '" . $SelectedAccount ."%' and length('" . $SelectedAccount ."')< length(accountcode)";
										$ErrMsg = '科目下有子目不能删除！';
										$result = DB_query($sql,$ErrMsg);
										$myrow = DB_fetch_row($result);
										if ($myrow[0]>0) {
											$CancelDelete = 1;
											prnMsg( '科目下有子目不能删除！','warn');
										} else {
											//$sql = "DELETE FROM chartdetails WHERE accountcode='" . $SelectedAccount ."'";
											$sql = "DELETE FROM chartmaster WHERE accountcode='" . $SelectedAccount ."'";
											$result = DB_query($sql);
											$sql="DELETE FROM registername  WHERE account= '" . $SelectedAccount ."'";
											$result = DB_query($sql);
											$sql="DELETE FROM accountsubject WHERE subject= '" . $SelectedAccount ."'";
											$result = DB_query($sql);
											$sql="DELETE FROM registeraccount WHERE subject= '" . $SelectedAccount ."'";
											$result = DB_query($sql);
											prnMsg( _('Account') . ' ' . $SelectedAccount . ' ' . _('has been deleted'),'succes');
										}
									}
								}
							}
						}
					}
		}
	}
}
if (!isset($SelectedAccount) AND( isset($_POST['Search'])  ||isset($_POST['CSV']))) {
	$sql = "SELECT accountcode,
					accountname,
					chartmaster.group_,
					used,
					currcode,	
					chartmaster.tag,
					companies.unitstab tagdescription										   
					FROM chartmaster  LEFT JOIN companies ON chartmaster.tag=companies.coycode";
				
	if(isset($_POST['searchname'])&& $_POST['searchname']!=''){
		$sql .= "  WHERE accountname like '%". $_POST['searchname']."%' ";    	
	}elseif(isset($_POST['Account'])&& $_POST['Account']!=="ALL"){	
		$sql .= "  WHERE accountcode like '".$_POST['Account']."%'";    	
	}	
  	$sql.=" ORDER BY chartmaster.accountcode";
	$ErrMsg = _('The chart accounts could not be retrieved because');
  
	$result = DB_query($sql,$ErrMsg);
	if (!isset($_POST['CSV'])) {
	echo '<br /><table class="selection" style="width: 700px;">';
	echo '<tr>
			<th class="ascending" style="width: 10px;">序号</th>
			<th class="ascending"  style="width: 50px;">' . _('Account Code') . '</th>		
			<th class="ascending"  style="width: 300px;">' . _('Account Name') . '</th>
			<th style="width: 20px;">币种</th>
			<th class="ascending"style="width: 80px;">' . _('Account Group') . '</th>
			<th class="ascending" style="width: 70px;">科目分组</th>
			<th class="ascending" style="width: 30px;">状态</th>
			<th colspan="2" style="width: 50px;">&nbsp;</th>
		</tr>';
	$k=0; //row colour counter	<th class="ascending">' . _('P/L or B/S') . '</th>
    $r=1;
	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		if (strlen($myrow[0])>4){
		$urledit="<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?SelectedAccount=".$myrow[0]."\">" . _('Edit') . "</a>";
	    $urldel="<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?SelectedAccount=".$myrow[0]."&amp;delete=\"1 onclick=\"return confirm('" . _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.') . "');\" >" . _('Delete') . "</a>";
		}else{
			$urledit='';
			$urldel='';
		}
		if ($myrow[3]==0){
			$status='正常';
		}elseif($myrow[3]==-1){
			$status='临时';
		}elseif($myrow[3]==-3 ||$myrow[3]==-4){
			$status='停用';
		}else{
			$status='未知';
		}

	echo '<td>'.$r.'</td>
	<td>'.$myrow[0].'</td>
	<td>'.htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8').'</td>
	<td>'.$myrow[4].'</td>
	<td>'.$myrow[2].'</td>
	<td>'.$myrow[6].'</td>
	<td>'.$status.'</td>
	<td>'.$urledit.'</td>
	<td>'.$urldel.'</td></tr>';
		$r++;
	}
	//END WHILE LIST LOOP
	echo '</table>';
}
	if (isset($_POST['CSV'])) {
		$CSVListing =  iconv("UTF-8","gbk//TRANSLIT",'科目编码' ).','.iconv("UTF-8","gbk//TRANSLIT", '科目名称') .','.iconv("UTF-8","gbk//TRANSLIT", '币种') .','.iconv("UTF-8","gbk//TRANSLIT", '报表项目') .','.iconv("UTF-8","gbk//TRANSLIT", '分组') .','.iconv("UTF-8","gbk//TRANSLIT",'状态') . "\n";
		while ($row = DB_fetch_array($result)) {
			$CSVListing .= '"';
			$CSVListing .=iconv("UTF-8","gbk//TRANSLIT",$row['accountcode'].'","'.$row['accountname'].'","'.$row['currcode'].'","'.$row['group_'].'"').',"'.$row['tag'].'","'.$row['used'].'"'. "\n";
			//$CSVListing .=iconv("UTF-8","gbk//TRANSLIT",implode('","', $csvarr) ). '"' . "\n";itemtotal
		}
		header('Content-Encoding: UTF-8');
		header('Content-type: text/csv; charset=UTF-8');
		header("Content-disposition: attachment; filename=".iconv("UTF-8","gbk//TRANSLIT",'科目表_').  date('Y-m-d')  .'.csv');
		header("Pragma: public");
		header("Expires: 0");
		//echo "\xEF\xBB\xBF"; // UTF-8 BOM
		echo $CSVListing;
		exit;
	}
} elseif(isset($_POST['return'])){
	prnMsg($RootPath,'info');
	//unset($_SESSION['GLreturn']);	
	header('Location:'.$RootPath.'/GLJournal.php');
} 

/*elseif(isset($_POST['usedsave'])){
	$resultary = "";
	foreach( $_POST['chkbx'] as $i){
 		$resultary .= $i.',';
		}
	if (strlen($resultary)>4){	
		$resultary = substr($resultary,0,-1);
		$sql = "UPDATE chartmaster SET used =CASE WHEN accountcode IN( " . $resultary . ") THEN '-1' ELSE 0 END";
		$result = DB_query($sql,$ErrMsg);
		prnMsg (_('The general ledger account has been updated'),'success');
	}
		//prnMsg($resultary,'info');
}
*/
//end of ifs and buts!

echo'</form><br />';
include('includes/footer.php');

?>