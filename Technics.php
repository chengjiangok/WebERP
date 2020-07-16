<?php
/*
 * @Author: ChengJiang 
 * @Date: 2017-06-29 05:08:39 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-06-29 05:50:28
 */
include('includes/session.php');
$Title = '工艺维护';
/* Manual links before header.php */
$ViewTopic= 'GeneralLedger';// Filename in ManualContents.php's TOC.
$BookMark = 'GLAccounts';// Anchor's id in the manual's html document.
include('includes/header.php');

echo'<script type="text/javascript">
		function sltproduct(obj){
		  //  var objval=obj.value.split("^")[0];
  			window.location.href="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?myslt="+obj.value ;
 		}
		 function computetime1(myslt){
        	var jsn=document.getElementById("techquota").value;
	        var sltval =myslt.value;					
            var techcode=(document.getElementById("technics").value).split("^")[0];
			
            var list=(sltval.split("^")[1]).split("*"); 
		    var maxspec=Math.max.apply(this,  list);// 数组中的最大值
			var obj = eval(jsn);		//var obj= JSON.parse(jsn);
			var qut=0;			
				for(var i=0; i<obj.length; i++)  
  				{ 
   					 if ((maxspec>= obj[i].specmin) &&( maxspec<=obj[i].specmax) && (obj[i].techid=techcode))
					{ 
						qut=obj[i].quota;
						break;
					}
  				}   
			document.getElementById("extraquant").value=qut;		
		}
		function computetime(myslt){
             //alert(myslt.value);
        	 var jsn=document.getElementById("techquota").value;
			 var sltval = document.getElementById("companents").value;
             //var techcode=Number((document.getElementById("technics").value).split("^")[0]);
		 	var techcode=Number((myslt.value).split("^")[0]);
			 // console.log(techcode); 
              var list=(sltval.split("^")[0]).split("*"); 		    
			 //console.log(sltval.split("^")[0]); 
			    var maxspec=Number(Math.max.apply(this,  list));
			//	alert(maxspec);
				var obj= JSON.parse(jsn);				
				var temp = []; 	
					var str="";			
				for(var i=0; i<obj.length; i++)  
  				{  
   					 temp[i]= (function(n){				  
					if (Number(obj[n].techid)==techcode){		
						if ((maxspec>=Number( obj[n].specmin)) && (maxspec<=Number(obj[n].specmax)))
						{	   
							str= obj[n].quota;   
						}   
					}
       				 })(i);  
                
  				}
			
				document.getElementById("extraquant").value=str;
				     //根据id查找对象，
			  	
			    //alert("hello");  
         		 var sltobj=document.getElementById("equipment");  
               //添加一个选项  
     		    // obj.add(new Option("文本","值"));				     //这个只能在IE中有效  
          	    sltobj.options.length=0;  
			  	sltobj.options.add(new Option("数控中心","1")); //这个兼容IE与firefox  
 	            sltobj.options.add(new Option("电火花","2")); //这个兼容IE与firefox  

		}
	function fun() {  
          window.location.reload();
    }  
</script>';


if (isset($_POST['SelectedAccount'])){
	$SelectedAccount = $_POST['SelectedAccount'];
} elseif (isset($_GET['SelectedAccount'])){
	$SelectedAccount = $_GET['SelectedAccount'];
}
if (isset($_GET['GLreturn'])){
	$_SESSION['GLreturn']='GL';
}
if (isset($_POST['account'])){
  $account=trim($_POST['account']);
}
if (isset($_POST['subaccount'])){
  $subaccount=trim($_POST['subaccount']);
}else {
 	 $subaccount='';
}
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		_('General Ledger Accounts') . '" />' . ' ' . $Title . '</p>';
echo '<form method="post" id="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
 	//initialise no input errors assumed initially before we test
	$InputError = 0; 	
	//first off validate inputs sensible
if (isset($_POST['save'])) {
	if (mb_strlen($_POST['AccountName']) >50) {
		$InputError = 1;
		prnMsg( _('The account name must be fifty characters or less long'),'warn');
	}elseif  (mb_strlen($_POST['AccountName']) < 2){
	   $InputError = 1;
	 	prnMsg( _('The account name must be tow characters or less long'),'warn');
	}
}
if (isset($_POST['save']) AND $InputError ==0) {

	//下级科目手动填入
    if  ($subaccount!='' AND  !isset($SelectedAccount) ){    	
 
		if  ( strlen($subaccount)>0 AND strlen($subaccount) < 20-strlen($account) AND  $InputError == 0  AND is_numeric($subaccount)){
			//下级科目必须是数字，总长度小于20 
 
			$sql="SELECT  COUNT(*)  FROM  chartmaster where accountcode = '".$account.$subaccount."';";
			$result = DB_query($sql,$ErrMsg); 
			$myrow = DB_fetch_row($result);
	
			if ($myrow[0]==0){
				$InputError = 0;
				$sql="SELECT group_ ,accountname FROM chartmaster   where accountcode like '".$account."' limit 1 ";
				$ErrMsg = _('Could not query the account because');
				$result = DB_query($sql,$ErrMsg); 
				$myrow = DB_fetch_array($result);
				$group=$myrow['group_'];	  	
				$accountcode=$account.$subaccount;
				$accountname=$myrow['accountname'];
			}else{
		  
		  	prnMsg(	'你输入的科目代码已经存在！','info');
		  }
    
        } else{
    	//initialise no input errors assumed initially before we test
  
     	prnMsg(	'你输入的科目代码不符合规范，必须是数字，在2-16位之间！','info');
	    }
	 }
	// 	自动生成号码
	if ($InputError==0 AND $_POST['subaccount']=='' AND  !isset($SelectedAccount) AND $SelectedAccount=="" ){
        $sql="SELECT accountcode +1  accountcode ,group_ ,accountname FROM chartmaster   where accountcode like '".$account."%'  and length(accountcode)> length(".$account.") order by accountcode desc limit 1 ";
    	$ErrMsg = _('Could not query the account because');
		$result = DB_query($sql,$ErrMsg); 
		$myrow = DB_fetch_array($result);
		if (isset($myrow['accountcode'])){
	
			$accountcode=$myrow['accountcode'];
    	}else{
	       if (substr($_POST['account'], 0,1)>2){
	     		 $accountcode=$_POST['account'].'01';
	  	   }else{
				$accountcode=$_POST['account'].'001';
	       }
	    }
	  } 
	  if ( isset($SelectedAccount)){
	     $account=substr($SelectedAccount,0,4);
	  }else {
	   	 $account=trim($_POST['account']);
	  }
	  $sql="SELECT group_ ,accountname FROM chartmaster   where accountcode like '".$account."' limit 1 ";
   	  $ErrMsg = _('Could not query the account because');
	  $result = DB_query($sql,$ErrMsg); 
	  $myrow = DB_fetch_array($result);
	  $group=$myrow['group_'];	  	
	  $accountname=$myrow['accountname'];

  if (isset($SelectedAccount) AND $InputError !=1) {
	  if (strlen($SelectedAccount)!=4){
		$sql = "UPDATE chartmaster SET accountname='" . $accountname.'-'.$_POST['AccountName'] . "',
						group_='" . $group . "',
						crtdate='".date("Y-m-d")."'
				WHERE accountcode ='" . $SelectedAccount . "'";

		$ErrMsg = _('Could not update the account because');
		$result = DB_query($sql,$ErrMsg);
		prnMsg (_('The general ledger account has been updated'),'success');
		}else {
				prnMsg ('总账科目不能修改！','info');
		}
  } elseif ($InputError ==0 AND  !isset($SelectedAccount) AND $SelectedAccount=="") {
	//SelectedAccount is null cos no item selected on first time round so must be adding a	record must be submitting new entries 
       if ($accountcode!=''){
		$ErrMsg = _('Could not add the new account code');
		$sql = "INSERT INTO chartmaster (accountcode,
						accountname,
						group_,crtdate)
					VALUES ('" . $accountcode . "',
							'" . $accountname.'-'. $_POST['AccountName'] . "',
							'" .$group . "','".date("Y-m-d")."')";
		$result = DB_query($sql,$ErrMsg);

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

} elseif (isset($_GET['delete'])) {
	//the link to delete a selected record was clicked instead of the submit button

	// PREVENT DELETES IF DEPENDENT RECORDS IN 'ChartDetails'

	$sql= "SELECT COUNT(*)
			FROM chartdetails
			WHERE chartdetails.accountcode ='" . $SelectedAccount . "'
			AND chartdetails.actual <>0";
	$result = DB_query($sql);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this account because chart details have been created using this account and at least one period has postings to it'),'warn');
		echo '<br />' . _('There are') . ' ' . $myrow[0] . ' ' . _('chart details that require this account code');

	} else {
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
									OR purchpricevaract='" . $SelectedAccount ."'
									OR materialuseagevarac='" . $SelectedAccount ."'
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

									$sql = "DELETE FROM chartdetails WHERE accountcode='" . $SelectedAccount ."'";
									$result = DB_query($sql);
									$sql="DELETE FROM chartmaster WHERE accountcode= '" . $SelectedAccount ."'";
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
}
  	echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

if (isset($SelectedAccount) AND !isset($_GET['delete'])) {
		//editing an existing account

		$sql = "SELECT t1.accountcode, t1.accountname, t1.group_ ,t.accountname  parentname  
		FROM chartmaster t1  INNER JOIN  chartmaster t on left(t1.accountcode,4)=t.accountcode WHERE  t1.accountcode='" . $SelectedAccount ."'";

		$result = DB_query($sql);
		$myrow = DB_fetch_array($result);

		$_POST['AccountCode'] = $myrow['accountcode'];
		$_POST['AccountName']	=  str_replace($myrow['parentname'].'-','',$myrow['accountname']);
		$_POST['Group'] = $myrow['group_'];

		echo '<input type="hidden" name="SelectedAccount" value="' . $SelectedAccount . '" />';
		echo '<input type="hidden" name="AccountCode" value="' . $_POST['AccountCode'] .'" />';
		echo '<table class="selection">
				<tr><td>' . _('Account Code') . ':</td>
					<td>' . $_POST['AccountCode'] . '</td>
					<td>'.'科目组：' . $_POST['Group'] . '</td>
				</tr>';
} else {
		echo '<table class="selection">';
		
		$sql="SELECT code, subcategorydspn, flg FROM stocksubcategory WHERE stocktype='M' AND LEFT(categoryid,1)=1";		
		$result=DB_query($sql);
		echo '<tr>
				<td>产品分类:</td>
				<td><select name="product" id="product" onchange="sltproduct(this)">'	;
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['product']) AND $_POST['product']==$myrow['code']){
				echo '<option selected="selected" value="' . $myrow['code'] . '">' . $myrow['code'].' - ' .htmlspecialchars($myrow['subcategorydspn'], ENT_QUOTES,'UTF-8', false) . '</option>';
			} else {
				echo '<option value="' . $myrow['code'] . '">' . $myrow['code'].' - ' .htmlspecialchars($myrow['subcategorydspn'], ENT_QUOTES,'UTF-8', false)  . '</option>';
			}
		}
		echo '</select></td>
		      <td><select name="account" onchange="return assignComboToInput(this,'.'GLManualCode'.'">'	;
		$sql="SELECT stockid code, categoryid, description, longdescription, units FROM stockmaster WHERE stockid LIKE '".$_GET['myslt']."%'";
		$result=DB_query($sql);
		while ($myrow=DB_fetch_array($result)){
			if (isset($_POST['account']) AND $_POST['account']==$myrow['code']){
				echo '<option selected="selected" value="' . $myrow['code'] . '">'  .htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8', false) . '</option>';
			} else {
				echo '<option value="' . $myrow['code'] . '">'  .htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8', false)  . '</option>';
				echo '<option value="' . $myrow['code'] . '@">'  .htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8', false)  . '附件</option>';
			}
		}
		echo '</select>
				</td></tr>';
		echo'<tr><td>组件编码</td>
				<td colspan="2"><input type="text"  name="subaccount" size="10" maxlength="10" value="" /></td>
				</tr>';
}

	if (!isset($_POST['AccountName'])) {
		$_POST['AccountName']='';
	}
	echo '<tr><td>组件名称:</td>';
	echo '<td colspan="2">
			<input type="text" size="51" title="' . _('Enter up to 50 alpha-numeric characters for the general ledger account name') . '" maxlength="50" name="AccountName" value="' . $_POST['AccountName'] . '" /></td></tr>';		


	echo'	</table>
		<br />
		<div class="centre">
			<input type="submit" name="save" value="保存" />
			<input type="submit" name="show" value="显示全部" />
			<input type="submit" name="subshow" value="显示子目" />
			<input type="submit" name="usedsave" value="状态保存" />
			<br />';
		
	echo'	</div>';
	if(isset($_GET['GLreturn'])OR isset($_SESSION['GLreturn'])){
	
		echo '<div class="centre"><a href=GLJournal.php?GLreturn=GL >' . '返回凭证录入'  . '</a></div><br />';
	}

if (!isset($SelectedAccount) AND( isset($_POST['show']) OR isset($_POST['subshow']) )) {
     //	$sql = "SELECT stockid, compcode, description, spec,  procedur, technics,  quantity, flag FROM components WHERE 1 ";
	    $sql="SELECT stockid, compcode, program, description, planquanttity, techid, technics,  quantity, flag FROM technicsprogram WHERE 1";
    if(isset($_POST['subshow']) ){
			$sql .= "  WHERE accountcode like '".$account."%'";    	
  	}	
  	
  	$sql.=" ORDER BY stockid,compcode";
	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$ErrMsg);

	echo '<br /><table class="selection">';
	echo '<tr>
	        <th>序号</th>
			<th class="ascending">产品编码</th>
			<th class="ascending">产品名称</th>
			<th class="ascending">组件编码</th>
			<th class="ascending">组件名称</th>
			<th class="ascending">工序</th>
			<th>计划工时</th>
			<th>备注</th>
			<th colspan="2"></th>
		</tr>';

	$k=0; //row colour counter
    $n=1;
	while ($myrow = DB_fetch_row($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
	printf("<td>%s</td>
	<td>%s</td>
		<td>%s</td>
		<td>%s</td>
		<td>%s</td>
			<td>%s</td>
			<td>%s</td>
		 <td><input type='checkbox' name='chkbx[]' value='%s' %s></td>
	    <td><a href=\"%sSelectedAccount=%s\">" . _('Edit') . "</a></td>
	   	<td><a href=\"%sSelectedAccount=%s&amp;delete=1\" onclick=\"return confirm('" . _('Are you sure you wish to delete this account? Additional checks will be performed in any event to ensure data integrity is not compromised.') . "');\">" . _('Delete') . "</a></td>
		</tr>",
		$n,
		$myrow[0],
		htmlspecialchars($myrow[1],ENT_QUOTES,'UTF-8'),
		$myrow[2],		
		$myrow[3],
		$myrow[0],
		$myrow[7],
		$myrow[6],
		$myrow[4]<0?'checked':'',
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
	
		$myrow[0],
	
		
		htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?',
		$myrow[0]);
		$n++;

	}
	//END WHILE LIST LOOP
	echo '</table>';

} elseif(isset($_POST['return'])){
	prnMsg($RootPath,'info');
	
	//unset($_SESSION['GLreturn']);	
	header('Location:'.$RootPath.'/GLJournal.php');
 	
} elseif(isset($_POST['usedsave'])){
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

//end of ifs and buts!

echo'</form><br />';
include('includes/footer.php');
?>