<?php
/*
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-03-06 09:12:37
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-07-12 14:48:29
 */
require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

include ('includes/session.php');
//include ('includes/GLSubject.php');
include ('includes/GLAccountFunction.php');
include ('includes/ExcelFunction.php');
$Title ="银行对账单上传";// _('Import Bank Transactions');// Screen identificator.
$ViewTopic = 'MyTools';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ImportBankTrans';// Anchor's id in the manual's html document.
include('includes/header.php');
echo'<script type="text/javascript">
function OnImportFile(ths){
	if (ths.value == "") {    
        alert("请上传文件");    
        return false;    
    } else {    
        if (!/\.(xls|xlsx)$/.test(ths.value)) {    
            alert("文件类型必须是.xls,xlsx中的一种");    
            ths.value = "";    
            return false;    
		} 
		var obj = document.getElementById("BankAccount"); //位id
	    var index = obj.selectedIndex; // 选中索引
		var val = obj.options[index].value.split("^")[0]; // 选中值  
	    var actstr=document.getElementById("ActStr").value;
		var	actar=actstr.split(",");
		var actflg=0;
		for (var f=0;f<actar.length ;f++ ) {
			
			if (actar[f]==val){
							
				actflg=1;
				break;
			}		
		}
		//console.log(actflg);
		if (actflg==1){
			alert("该银行账户,有未读入的文件,不能再上传!");
			ths.value = "";   
		   return false;
		}
    }    
    return true; 
}
function OnCheckAct(){
	//没有使用
	var res=confirm("该银行账户,有未读入的文件,不能再上传!");
	if (res==true){
		alert("1你按下的是【取消】");
		//return true;
	}else{
		alert("2你按下的是【取消】");
	}
}
function queren(id){
	//没有使用
	var res=confirm("请选择点击一个按钮!");
	if (res==true) { 
		//跳转到指定页面并传递id参数
	window.location.href="http://test.com/userlist?param="+id;
	}
	else
	{
	alert("你按下的是【取消】");
	}
}
function tishi(){
  var t=prompt("请输入您的名字","KING视界")
  if (t!=null && t!="")
    {
    document.write("精彩MV就在，" + t + "！属于你的世界")
    }
}
</script>';
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/bank.png" title="' .$Title . '" /> ' .// Icon title.
	$Title . '</p>';// Page title.
include('includes/SQL_CommonFunctions.inc');
//include('includes/CurrenciesArray.php');
  	$yeardir=dechex(date("Y",strtotime($_SESSION['lastdate'])));
 	$path = 'companies/'.$_SESSION['DatabaseName']."/BankStatement/";
	$filepath =$path.$yeardir."/";  
	 //<---------检测文件夹中的文件是否在数据表BankUpload中,如   没有插入表  ---------->
	if (is_dir($filepath)){//判断目录是否存在
		$FilesBank  =dirfiles( getcwd().'/'.$filepath);
		
	}else{
		//创建目录
		//prnMsg($filepath."创建目录！");	
		mkdir ($filepath,0777,true);	
	}  
  /*  	
//读取  中文件	，检测
$sql="SELECT `uploadid`, `account`, `filename`, `filepath`,  `uploaddate`, `balance`, `remark`, `flag` 
	FROM `bankupload` 
	WHERE flag<>2 AND   filepath='".$yeardir."'";
//prnMsg($sql);
$result=DB_query($sql);
while($row=DB_fetch_array($result)){

	if (isset($FilesBank[$row['filename']])){//表中文件存在于目录中
		$FilesBank[$row['filename']]['flag']=$row['flag'];
		$FilesBank[$row['filename']]['uploadid']=$row['uploadid'];
		$FilesBank[$row['filename']]['filepath']=$row['filepath'];
		//prnMsg($row['filename'].'='.$row['flag']);
		//unset($FilesBank[$row['filename']]);
	}else{//表目录中文件没有  在于表中,Updatre表
		//$FilesBank[$row['filename']]['flag']=2;
		//$InvType=getinvtype($row['filename']);
		$act=substr($filename,0,strpos($row['filename'],'_'));
		$SQL="UPDATE `bankupload` SET `flag`=2 WHERE flag=0 AND `filename`= '".$row['filename']."' AND `filepath`='".$yeardir."'";
		//prnMsg($SQL);
		$Result=DB_query($SQL);
	}
}
  //检测目录中的文件，是否在表中
foreach($FilesBank as  $fname=>$val){
	if ($val['flag']==-1){
		if(substr($fname,0,4)=='1002'||substr($fname,0,4)=='1001'){

			$SQL="INSERT IGNORE INTO `bankupload`(	`account`,
												`filename`,
												`filepath`,												
												`uploaddate`,
												`debit`,
												`credit`,
												`balance`,
												`remark`,
												`flag`)
									VALUES('".substr($fname,0,strpos($fname,'_')) ."',
										'".$fname."',
										'".$yeardir."',
										'". date('Y-m-d H:i:s',time())."',
										'0',
										'0',
										'0',
										'',										
										'0')";
			$Result=DB_query($SQL);
			if(DB_affected_rows($Result)>0){//
				$uploadid=DB_Last_Insert_ID($db,'bankupload','uploadid');
				$FilesBank[$fname]['flag']=0;
				$FilesBank[$fname]['uploadid']=$uploadid;
				$FilesBank[$fname]['filepath']=$yeardir;
			}
		}else{
			//文件名不含科目编码直删除件
			if (unlink($filepath.$fname)){
				$msg.=$fname."删除文件完成！<br>";
				$FilesBank[$fname]['flag']=-2; 	
				
			}
			
		}
	}
}*/
if(isset($_POST['ClearCache'])||isset($_GET['ClearCache'])){
	prnMsg('缓存清除成功！','info');
	unset($_SESSION['BankFile']);
	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/ImportBankTrans.php">';
}
$sql="SELECT `uploadid`, `account`, `filename`, `filepath`,  `uploaddate`, `balance`, `remark`, `flag` 
	FROM `bankupload` 
	WHERE flag=0 AND   filepath='".$yeardir."'";
//prnMsg($sql);
$result=DB_query($sql);
if (!isset($_SESSION['BankFile']) && DB_num_rows($result)>0){	
    //echo '185';
	//foreach($FilesBank as $key=>$val){
	while($row=DB_fetch_array($result)){		
		//if ($val['flag']==0){
		if(file_exists($filepath.$row['filename'])) {	       
				$act=substr($row['filename'],0,strrpos($row['filename'],'_'));
				$_SESSION['BankFile'][0]=0;
				$_SESSION['BankFile'][1]=$row['filename'];	
				//$_SESSION['BankFile'][2]=$row['uploaddate'];
				$_SESSION['BankFile'][3]=$row['uploadid'];	
				$_SESSION['BankFile'][4]=$row['filepath'];
				$_SESSION['BankFile'][5]=$act;
				break;
		}else{
			$SQL="UPDATE `bankupload` SET `flag`=2 WHERE flag=0 AND `filename`= '".$row['filename']."' AND `filepath`='".$yeardir."'";
		
			$Result=DB_query($SQL);
		}
		  
	}

}
//<---------End------->

	echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '" >';
	echo'<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			 <input type="hidden" name="BankAccount" value="' . $_POST['BankAccount'] . '" />
			 <input type="hidden" name="ActStr" id="ActStr"  value="'.$AccountStr.'" > ';
			 $sql = "SELECT bankaccounts.accountcode,
						bankaccounts.bankaccountname,
						bankaccounts.currcode,
						bankaccounts.bankaccountnumber,
						tag					
					FROM bankaccounts, bankaccountusers, chartmaster 
					WHERE bankaccounts.accountcode=chartmaster.accountcode
					AND bankaccounts.accountcode=bankaccountusers.accountcode
					AND bankaccounts.importformat IN (SELECT  bankid FROM bankformat)  
					AND bankaccountusers.userid = '" . $_SESSION['UserID'] ."'
					ORDER BY bankaccounts.accountcode";
			$ErrMsg = _('The bank accounts set up could not be retrieved because');
			$DbgMsg = _('The SQL used to retrieve the bank accounts was') . '<br />' . $sql;
			$result = DB_query($sql,$ErrMsg,$DbgMsg);
			if (DB_num_rows($result) ==0){
				prnMsg(_('There are no bank accounts defined that are set up to allow importation of bank statement transactions. First define the file format used by your bank for statement exports.'),'error');
				echo '<br /><a href="BankAccounts.php>' . _('Setup Import Format for Bank Accounts') . '</a>';
				include('includes/footer.php');
				exit;
			}
	echo'<table class="selection">
		<tr>
			<td>选择导入银行账户</td>
			<td><select name="BankAccount" id="BankAccount">';
		
		while ($myrow=DB_fetch_array($result,$db)){
			//$BankDataArr[$myrow['accountcode']]=array($myrow['bankaccountnumber'],$myrow['currcode'], $myrow['bankaccountname'] ,0,0,'',$myrow['tag']);
			if( (isset($_SESSION['BankFile'])&& $_SESSION['BankFile'][5]==$myrow['accountcode'])|| (isset($_POST['BankAccount']) and ($myrow['accountcode'].'^'.$myrow['bankaccountnumber'].'^'.$myrow['currcode'].'^'.		$myrow['tag']==$_POST['BankAccount']))){
				echo '<option selected="selected" value="' ;
			}else{
		
				echo '<option value ="';
			}					
				echo  $myrow['accountcode'] .'^'.$myrow['bankaccountnumber'].'^'.$myrow['currcode'].'^'.$myrow['tag']. '">' . $myrow['accountcode'] . '[' .$myrow['currcode'].']'. $myrow['bankaccountname'] . '</option>';
		}
	 echo'</select></td>
			 </tr>
			 <tr>
				<td>上传文件(xls,xlsx)</td>';			
		echo'<td>
				<input type="file" id="ImportFile"    title="' . _('Select the file that contains the bank transactions in format') . '" name="ImportFile" onchange="OnImportFile(this)"  > 
			</td>
			</tr>
		</table><br>';		
		//读取末期余额
	$sql="SELECT account,SUM(amount) endamount FROM banktransaction GROUP BY account";
	$Result=DB_query($sql);
	while ($row= DB_fetch_array($Result)) {
		$BankBalance[$row['account']]=$row['endamount'];
	}   
	DB_data_seek($result,0) ;
	//$Bank Data银行账户资料  币种  账号 银行名 最末余额 最末时间
	while($row=DB_fetch_array($result)){
		//读取最末时     发额
		$sql="SELECT  account, bankdate ,amount  FROM banktransaction WHERE account='".$row['accountcode']."' ORDER BY banktransid DESC  LIMIT 1";
		$Result=DB_query($sql);
		$Row=DB_fetch_assoc($Result);
		$enddate='';
		$amountlast=0;
		if (!empty($Row)){
			 $enddate=$Row['bankdate'];
			 $amountlast=$Row['amount'];  
		}
		$BankData[$row['accountcode']]=array("currcode"=>$row['currcode'],"banknumber"=>$row['bankaccountnumber'],"bankname"=>$row['bankaccountname'],"balancelast"=>$BankBalance[$row['accountcode']],"enddate"=>$enddate,"amountlast"=>$amountlast);
	}
	//var_dump($BankData);
	unset($BankBalance);
	unset($BankEndDate);
	echo '<table cellpadding="2" class="selection">
			<tr>
				<th >序号</th>							
				<th >账户科目</th>	
				<th >开户行名称</th>
				<th >币种</th>			
				<th >账户余额</th>
				<th >最末日期</th>
				<th >操作</th>							
			</tr>'; 
			$RowIndex=0; 

	foreach($BankData as $key=>$row){
		if ($row['enddate']!=''){

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}			
		echo '<td>'.($RowIndex+1).'</td>
				<td>'.$key.'</td>
				<td title="'.$row['banknumber'].'">'.$row['bankname'].'</td>
				<td>'.$row['currcode'].'</td>
				<td class="number">'.locale_number_format($row['balancelast'],2).'</td>';
     			echo'<td >'.date('Y-m-d',strtotime($row['enddate']))."</td>";
			/*if (isset($row['bankenddate']){
			echo"<td ><a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Read=".$BankDataArr[$row['accountcode']]['uploadid']."\" >读入</a>&nbsp&nbsp
				<a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Del=".$BankDataArr[$row['accountcode']]['uploadid']."\" onclick=\"return confirm('你确认要删除该文件吗!');\" >" . _('Delete') . "</a></td>";
			}else{*/
				echo'<td ></td>';	
				echo"</tr>";					
				$RowIndex++;
		}
	}
	echo '</table>';  	
	$BankCode=explode('^',$_POST['BankAccount']);
	echo'<div class="centre">		  
			<input type="submit" name="Upload" value="账单上传" />  ';
if (isset($_SESSION['BankFile'])){
		echo'<input type="submit" name="SaveUpdate" value="更新保存" />
			<input type="submit" name="ClearCache" value="清除缓存"/>';
}
echo'</div>';
if (isset($_GET['Del']) ){
	$sql="SELECT `uploadid`, `account`, `filename`, `filepath`,  `uploaddate`, `balance`, `remark`, `flag` 
	FROM `bankupload` 
	WHERE flag=0 AND   filepath='".$yeardir."'";
	if (unlink(	$filepath .$_GET['Del'])){
		$msg.=$_GET['Del']."删除文件完成！<br>"; 
		$SQL="DELETE FROM `bankupload` WHERE flag =0 AND filename='".$_GET['Del']."'";
		$Result=DB_query($SQL);
	}else{
		$SQL="UPDATE `bankupload` SET flag=2 WHERE   filename='".$_GET['Del']."'";
		$Result=DB_query($SQL);
	}
}
if (isset($_GET['read']) ){	
    $postfix=substr($_GET['read'],strrpos($$_GET['read'],'.')+1 );
    //echo '-='.$postfix;
  
      $SQL="SELECT `uploadid`, `account`, `filename`, `filepath`,  `uploaddate`, `balance`, `remark`, `flag` 
	  FROM `bankupload` 
	  WHERE filename='".$_GET['read']."'  AND uploaddate>='".date("Y-m-d",$_GET['time'])."' LIMIT 1";
           // echo '`filename`-='.$SQL;
       if (isset($_SESSION['InvFile']))
       unset($_SESSION['InvFile']);
      $BankResult=DB_query($SQL); 
      $BankRow=Db_fetch_assoc($BankResult);
      if (!empty($InvRow)){
		$_SESSION['BankFile'][0]=1;
		$_SESSION['BankFile'][1]=$BankRow['filename'];	
		$_SESSION['BankFile'][2]=$BankRow['uploaddate'];
		$_SESSION['BankFile'][3]=$BankRow['uploadid'];	
		$_SESSION['BankFile'][4]=$yeardir;
		$_SESSION['BankFile'][5]=$BankRow['account'];
      }
    
    //echo '读取文件'.$InvRow['uploadid'].$InvRow['filesinv'];
   
}
if ($_GET['bankdate']){
    if (count($FilesBank)>0){
      $InvTime=strtotime($_GET['bankdate']); 
      
      echo '<table cellpadding="2" class="selection">
          <tr>
            <th >序号</th>							
            <th >文件名称</th>	        
            <th >未读取文件名</th>
            <th >上传日期</th>
            <th ></th>
          </tr>'; 	
    
      $RowIndex=0;
      //while ($row= DB_fetch_array($result)) {
      foreach($FilesBank as $key=>$row)   {
        if ($row['time']>=$InvTime){
          if ($k==1){
            echo '<tr class="EvenTableRows">';
            $k=0;
          } else {
            echo '<tr class="OddTableRows">';
            $k=1;
          }	
          $RowIndex++;				
          echo'<td>'.$RowIndex.'</td>
              <td>'.$key.'</td>           
              <td >'.date("Y-m-d",$row['time'])."</td>
              <td ><a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?read=".$key."&time=".$row['time']."\" >读入</a>&nbsp&nbsp
              <a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Del=".$key."\" onclick=\"return confirm('你确认要删除该文件吗!');\" >" . _('Delete') . "</a></td>";
          
            echo"</tr>";
        }
              
      }
      echo'<input type="hidden" name="ReadTab" id="ReadTab"  value="'.$RowIndex.'" > ';
      echo '</table>';
    }
}
 // print_r($_SESSION['BankFile']);
//读取文件资料
if (isset($_SESSION['BankFile'])){

	$sql="SELECT	bankid,
					serialnumber,
					actnumber,
					actrow,
					accountcode,
					bkdate,
					bktime,
					debit,
					credit,
					currency,
					payaccount,
					payname,
					paybank,
					toaccount,
					toname,
					tobank,
					balance,
					abstract,
					remark,
					startrow,
					maxcol,
					dcflag,
					flg
					FROM bankformat 
					INNER JOIN bankaccounts	ON bankaccounts.importformat=bankformat.bankid
						WHERE flag=0 AND bankaccounts.accountcode='".$_SESSION['BankFile'][5]."'";
		$result=DB_query($sql);
		//prnMsg($sql);			
		$BankFormat=DB_fetch_array($result);
		$options=[];
		//echo ($filepath.$_SESSION['BankFile'][1]);//print_r($Data);
		$Data=BankExcel($filepath.$_SESSION['BankFile'][1],0,0, $options);
		if (is_array($Data)){
			//var_dump($Data);	
			$Act=$BankFormat['accountcode'];   
			//$uploadid=0;
		// if ($_SESSION['BankFile'][0]==1){//=1第一次上传新文件=0已经上传的
			$SQL="SELECT COUNT(*) FROM `banktransaction` WHERE uploadid='".$_SESSION['BankFile'][3]."'";
			$RESULT=DB_query($SQL);
			if (DB_fetch_row($RESULT)[0]>0){
				//$uploadid=$_SESSION['BankFile'][3];	
				$_SESSION['BankFile'][0]=2;		
			}  
			//excel数据转换成标准格式
			//print_r($Data);
		$DataBank=ExcelToData($Data,$BankFormat,$BankData[$_SESSION['BankFile'][5]],$_SESSION['BankFile'][3],$path.$_SESSION['BankFile'][4]."/".$_SESSION['BankFile'][1]);
		//print_r($DataBank);
			
		}else{
			prnMsg("你导入的Excel文件读取失败！","warn");
		}
	//}else{
		//unset($_SESSION['BankFile']);
	//}
    
	if (is_array($DataBank)){
		//var_dump($DataBank);
		$_SESSION['BankFile'][6]=$DataBank;    
		// $_SESSION['InvFile'][0]=1;
		unset($DataBank);
	}else{
	   //删除文件和表记录
	   $msg=' ';	
	   /*
	   if(unlink($filepath.$_SESSION['BankFile'][1])){
		//删除成功   删除表记录		
		   $msg.=",文件删除失败，请通知系统管理员";
	   }
	   */
	   $SQL="DELETE FROM `bankupload` WHERE flag=0 AND `filename`= '".$_SESSION['BankFile'][1]."' ";//AND `filepath`='".$yeardir."'";
	    // $Result=DB_query($SQL);
	   if ($Result){
		   unset($_SESSION['BankFile']);
	   }
	   prnMsg("你上传的文件有误，".$DataBank.$msg,'warn');
	   //添加跳转到起始文件链接
	}	
}



if (isset($_POST['Upload'])){	
	$file = $_FILES['ImportFile'];
	//获取文件名
	$fname = $_FILES['ImportFile']['name']; 
	$postfixarr=array("xls","xlsx");
	$bankfile=true;
	$filepostfix=substr($fname,strrpos($fname,'.')+1 );
	//prnMsg($filepostfix);	
	$filename=$BankCode[0].'_'.date('ymdhis',time()).".".$filepostfix;  
	if (isset($filename)&&$filename!=''){//判断是否选择上传文件
        $file_type=$_FILES['ImportFile']['type'];         
       	
		//得到标准文件名
		if (in_array($filepostfix,$postfixarr)){
			
			//  $file_true_name=$_FILES['ImportFile']['name']; 
			//	$file_name=$BankAct.'_'.(string)$uploadid.'_'.date('Ymd',time()).substr($file_true_name,strrpos($file_true_name,"."));  
			/*
			if (!is_array($FilesBank)){
				$FilesBank=dirfiles($filepath);//读取v目录下文件
			}
			*/
		    $uploadtab = move_uploaded_file($_FILES['ImportFile']['tmp_name'],$filepath.$filename);
				  //$postfix=substr($filename,strrpos($filename,'.')+1 );
				$uploaddate= date('Y-m-d H:i:s'); 
			    $sql="INSERT  INTO `bankupload`(`account`,
													`filename`,
													`filepath`,												
													`uploaddate`,
													`debit`,
													`credit`,
													`balance`,
													`remark`,
													`flag`)
										VALUES('".$BankCode[0] ."',
												'".$filename."',
												'".$yeardir."',
												'". $uploaddate."',
												'0',
												'0',
												'0',
												'',
												'0')";
						
				$result=DB_query($sql);
				$uploadid=DB_Last_Insert_ID($db,'bankupload','uploadid');
				//$sql="UPDATE bankcopy SET urlfile='".$file_name."' ,flag=-1 WHERE uploadid=".$uploadid;
				if (!isset($_SESSION['BankFile'])){	
					//$act=$BankCode[0];//substr($row['filename'],0,strrpos($row['filename'],'_'));			
					$_SESSION['BankFile'][0]=1;
					$_SESSION['BankFile'][1]=$filename;	
					$_SESSION['BankFile'][2]=$uploaddate;
					$_SESSION['BankFile'][3]=$uploadid;	
					$_SESSION['BankFile'][4]=$yeardir;
					$_SESSION['BankFile'][5]=$BankCode[0];
					echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/ImportBankTrans.php">';
					
				}  
 						
		}else{
			prnMsg($filename."不合乎上传文件格式！","warn");
	
  		}

	} else{
      prnMsg('你没有选择上传文件','info');
    }	
		/*if(!file_exists($move_to_path.$file_name) && $file_name!='') {  
			$sql="DELETE FROM bankcopy 	WHERE urlfile ='".$file_name."'";
			$result=DB_query($sql);	
			prnMsg($file_name.'文件不存在,系统自动删除!','warn');
			include ('includes/footer.php');
			exit;
		}*/
}
 
//var_dump($_SESSION['BankFile'][6]);
if(isset($_SESSION['BankFile'][6])){
	//有读取数据执行以下

	prnMsg($_SESSION['BankFile'][5],'info');
	echo '<table cellpadding="2" class="selection">
			<tr>
				<th >序号</th>							
				<th >' . _('Date') . '</th>			
				<th >收入金额</th>
				<th >支出金额</th>	
				<th >余额<br></th>					
				<th >对方账号</th>
				<th >对方名称</th>					
				<th style="word-wrap:break-word;word-break:break-all;">摘要</th>				
				<th >备注</th>
			</tr>';

	$RowIndex=1;
	$k=1;
	$styel='style="background: #ecc;"';
	foreach($_SESSION['BankFile'][6] as $row){
		//$nowbalance+=$row['debit']-$row['credit'];
			if ($k==1){
				echo '<tr '.$styel.'class="EvenTableRows">';
				$styel='';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
	
			$amountcurr=0;
			if($row['balance']==0){
				$balanceshow='0';
			}else{
				$balanceshow=locale_number_format($row['balance'],2);
			}
			echo'<td>'.$RowIndex.'</td>';
			echo'<td>'.$row['bkdate'].'</td>';							
			echo'<td class="number">'.locale_number_format($row['debit'],2).'</td>
					<td class="number">'.locale_number_format($row['credit'],2).'</td>
					<td class="number">'.$balanceshow.'</td>';			
			echo'<td >'.$row['toaccount'].'</td>				
					<td >'.$row['toname']. '</td>
					<td >'.$row['remark']. '</td>
					<td >'.$row['abstract']. '</td>';
		
		echo'</tr>';
				$RowIndex = $RowIndex + 1;
	}	//end of while loop
	
	echo '</table>';
		
}//end if 读取excel显示
if(isset($_POST['SaveUpdate'])){
	//prnMsg("Save Update");
	$RowIndex = 0;
	$result = DB_Txn_Begin();
	//<-----------以下为检账号是否在系统--->
	for($i=1;$i<=count($_SESSION['BankFile'][6]);$i++){	

		if ($_SESSION['BankFile'][6][$i]['toaccount']!=''){
			//$CustomToAct.=$_SESSION['BankFile'][6][$i]['toaccount'].",";
			$CustnameReg[$_SESSION['BankFile'][6][$i]['toaccount']]=$_SESSION['BankFile'][6][$i]['toname'];
		} 
	}
	//创建临时表
	$result = DB_query("DROP TABLE IF EXISTS tempcustomact");

	$sql = "CREATE TEMPORARY TABLE tempcustomact (bankaccount varchar(50),
											custname varchar(50),
											flag  tinyint(1),
											tag tinyint(2)) DEFAULT CHARSET=utf8";
	$ErrMsg = _('The SQL to create customact failed with the message');
	$result = DB_query($sql,$ErrMsg);
	$LastBalance=0;
    //把导入的账号写入临时表
    foreach($CustnameReg  as $key=>$val){
		$sql="INSERT INTO tempcustomact(bankaccount,
									custname,
									flag,
									tag)
									VALUES(
										'".$key."',
										'".$val."',
										0,
										'".$BankCode[3]."'	)";
		$result = DB_query($sql,$ErrMsg);

	}
	//检索新账号 更新标记到临时表
    $sql="SELECT  *  FROM tempcustomact  WHERE bankaccount NOT IN (SELECT `bankaccount` FROM `accountsubject` WHERE 1)";  
	$result = DB_query($sql);
	if (DB_num_rows($result)>0){
		DB_data_seek($result,0);
		while ($row=DB_fetch_array($result)) {
			$SQL="UPDATE tempcustomact SET flag=1 WHERE bankaccount='".$row['bankaccount']."'";			
			$Result = DB_query($SQL);
		}
	}
	//检索临时表  的公司名不在系统
	$sql="SELECT  *  FROM tempcustomact  WHERE custname  NOT IN (SELECT `custname` FROM `registername` WHERE 1)";  
	$result = DB_query($sql);

	while ($row=DB_fetch_array($result)) {	
	
		$SQL="UPDATE tempcustomact SET flag=2 WHERE bankaccount='".$row['bankaccount']."'";
		$Result = DB_query($SQL);
	}

	//读取新客户或需要更新客户到新数组$BankNew
	$SQL="SELECT  *  FROM tempcustomact WHERE flag=1 OR flag=2";  
	$Result = DB_query($SQL);	
	while ($row=DB_fetch_array($Result)) {	
		$BankNew[$row['bankaccount']]=array($row['flag'],$row['custname'],$row['tag']);
	}	
	//<------以上为得到新客户账号------->
	//var_dump($BankNew);
	$bkrow=&$_SESSION['BankFile'][6];
	$BalanceLast=round($bkrow[0]['balance'],2);
	
    $R=count($bkrow);
	for($i=1;$i<$R;$i++){
		if (round($bkrow[$i]['debit'],2)!=0){
			$amo=round($bkrow[$i]['debit'],2);
		}else{
			$amo=-round($bkrow[$i]['credit'],2);
		}
		if (isset($BankNew[$bkrow[$i]['toaccount']])){//插入新 公司判断
		
		
            if( AddUpdateCustomer($bkrow[$i],$BankNew[$bkrow[$i]['toaccount']])>0){
				unset($BankNew[$bkrow[$i]['toaccount']]);
			
			}
		}
		$sql="INSERT INTO 	banktransaction(account,
											serialnumber,
											bankdate,
											amount,
											currcode,
											toaccount,
											toname,
											tobank,
											remark,
											abstract,
											flag,
											flg,
											uploadid	)
									VALUES(
										'".$_SESSION['BankFile'][5] ."',
										'".$bkrow[$i]['serialnumber']."',
										'".$bkrow[$i]['bkdate']."',
										'".$amo."',
										'".$BankCode[2]."',
										'".	$bkrow[$i]['toaccount']."',
										'".	$bkrow[$i]['toname']."',
										'".	$bkrow[$i]['tobank']."',
										'".	$bkrow[$i]['remark']."',
										'".$bkrow[$i]['abstract']."',
										".$bkrow[$i]['flag'].",
										".$bkrow[$i]['flag'].",
										'".$_SESSION['BankFile'][3]."')";
													
			$result=DB_query($sql);	
			$LastBalance+=$amo;
		
		} //END FOR
	
		if  (round($LastBalance+$BalanceLast,POI)==round($bkrow[$R-1]['balance'],POI)){		
			if (empty($bkrow[$R-1]['balance'])){
				$bln=0;
			}else{
				$bln=$bkrow[$R-1]['balance'];
			}
			$sql="UPDATE bankupload	SET enddate ='".$bkrow[$R-1]['bkdate']."',balance =".$bln.",flag =1
					WHERE uploadid='".$_SESSION['BankFile'][3]."'";			
			$result=DB_query($sql);	
			$result = DB_Txn_Commit();
			unset($_SESSION['BankFile']);
			prnMsg('银行对账单上传更新成功!','info');
		}		
		echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/ImportBankTrans.php">';		
	//unset($_SESSION['BankFile']);  
}
echo'</form>';
include ('includes/footer.php');
/**
   *添加新单位 和账号  ，已经有用户名添加账号
   * @param array $ROW 对账   记录-> $banknew  用户名和标记
   *    *
   * @return tinyint
   * @throws Exception
   * 错误返回-1
   */
  function AddUpdateCustomer($ROW,$banknew){	
	
	if ($banknew[0]==1){//     单位插入账号  名
		$result = DB_Txn_Begin();
		$sql="INSERT IGNORE INTO registername (custname,
											tag,
											account,
											flg,
											regdate) 
										VALUE('". match_chinese($ROW['toname'])."',
												'".$banknew[2]."' ,
												'',
												'".$ROW['flag']."',
												'".date("Y-m-d h:i:s")."' 	) ";
		   $result=DB_query($sql);
	
		//  return $sql;
		if(DB_affected_rows($result)>0){//插入成功
			
			$regid=DB_Last_Insert_ID($db,'registername','regid');
			$sql="INSERT IGNORE INTO accountsubject(bankaccount,
												tag,
												regid,
												subject,
												acctype,
												bankname,
												bankcode,
												flg
											)
											VALUE('".match_number($ROW['toact'],2)."',
													'".$ROW['tag']."' ,
													'".$regid."',
													'',
													0,
													'".$ROW['tobank']."',
													'',
													'".$ROW['flag']."' 	) ";
			$result=DB_query($sql);
			if ($result){
				$result = DB_Txn_Commit();
				return 1;
			}
		}
	}elseif($banknew[0]==2){
		//已经存在名称,插入账号
	
		$sql="SELECT regid, custname, account FROM registername
		       WHERE custname='". $ROW['toname']."' ";//AND tag='".$ROW['tag']."'";
		$result=DB_query($sql);
		$row=DB_fetch_assoc($result);
		if (!empty($row)){
			$sql="INSERT INTO accountsubject(bankaccount,
													tag,
													regid,
													subject,
													acctype,
													bankname,
													bankcode,
													flg	)
											VALUE('".match_number($ROW['toact'],2)."',
													'".$banknew[2]."' ,
													'".$row['regid']."',
													'".$row['account']."',
													0,
													'".$ROW['tobank']."',
													'',
													'".$ROW['flag']."' 	) ";
			//prnMsg($sql);
			  $result=DB_query($sql);
			//return $sql;
			if ($result){
				return 1;
			}else{
				$SQL="INSERT INTO `erplogs`(`title`,
									   `content`,
								       `userid`,
									   `logtype`,
									   `logtime`) 
		                  VALUES (	'".$row['account']."' ,
									'账号".match_number($ROW['toact'],2)."REGID".$row['regid']."',
									'".$_SESSION['UserID']."',
									'-1',
									'".date("Y-m-d h:i:s")."' )";
					$Result=DB_query($SQL);
			}
		}
	}//endif
	return -1;
}

/**
   *银行对账单excel读取数组转换为标准数组格式;
   * @param string $file      文件地址
   *
   * @return array
   * @throws Exception
   * 错误返回-1
   */
function  ExcelToData($data,$bankformat,$BankData,$uploadid,$pathfile){
	//读取数据格式转换
	$actrow=0;//账号单元行
	$rowerr=0;
	$startrow=-1;
	

	$Act=$bankformat['accountcode'];
	//prnMsg('$data['.$bankformat['actrow'].$bankformat['actnumber'].']'.$data[$bankformat['actrow']][$bankformat['actnumber']].'!='.$BankData['banknumber']);
	//账单内有账号行得到账号
	if($bankformat['actrow']>0){
		if($data[$bankformat['actrow']][$bankformat['actnumber']]!=$BankData['banknumber']){
			return "-2,你导入的对账单账号核对异常";
		}
	}	
	//$bankrow=count($data);
	//判对账单倒序   ??问题 $data[count($data)修改为末尾
	foreach($data as $i=>$row){	
		if ($bankformat['startrow']<=$i){
		   $datareverse[]=$row;
		}
	}
	//如果对账单为倒序改为正序
    //print_r($datareverse);
	//echo '<br/>'.$data[$bankformat['startrow']][$bankformat['bkdate']].'>'.$data[count($data)-1][$bankformat['bkdate']];
	//if(strtotime($data[$bankformat['startrow']][$bankformat['bkdate']])>strtotime($data[count($data)][$bankformat['bkdate']])){	
	if(strtotime($datareverse[0][$bankformat['bkdate']])>strtotime($datareverse[count($datareverse)-1][$bankformat['bkdate']])){	
		$data=array_reverse($datareverse);
	}else{
		$data=$datareverse;
	}
	
	$lasttime=strtotime($BankData['enddate']);
	//print_r($data);	
	for ($i=0;$i<count($data);  $i++){	
		
		if ($bankformat['bktime']=='dd'||$bankformat['bktime']=='tt'||$bankformat['bktime']==""){
			//dd  发生日期  tt含时间  ""兼容， 日期时间分列=单行字母
			$banktime=strtotime($data[$i][$bankformat['bkdate']]);
		}else{
			//日   和时间分列
			$banktime=strtotime($data[$i][$bankformat['bkdate']].' '. $data[$i][$bankformat['bktime']]);
		}
		//prnMsg($data[$i][$bankformat['debit']].'='.$data[$i][$bankformat['credit']].'-'.$data[$i][$bankformat['bkdate']]);
	
		if ($banktime>=$lasttime ){//标准格式>=	
			//prnMsg($banktime.'>='.$lasttime.'>'.$BankData['enddate']);
				$debit=0;
				$credit=0;
				$flg=1;
				$amo=filter_number_format($data[$i][$bankformat['debit']]);					
				
				if ($bankformat['payaccount']!=''){//收付账户列模式
					$payto=1;   //收款单位一列付款单位一列	中行对账单			
				}else{
					$payto=0;//收付单位都在一列
				}
				if ($bankformat['dcflag']!='') {//2019-01-04信用社对账单格式改变
					//y标记借贷以+-分开数据在debit				
					$dc=json_decode($bankformat['dcflag'],JSON_UNESCAPED_UNICODE);
																							
					//if (strcmp(trim($data[$i][$bankformat['credit']]),trim($dc[0]))==0){
					if (trim($data[$i][$bankformat['credit']])==trim($dc[0])){
						$debit=$amo;	
						$credit=0;					
					}else{
					
						$debit=0;						
						$credit=$amo;	
						$amo=-$amo;						
					}										
				}else{				
					//if($data[$i][$bankformat['debit']]!="" && $data[$i][$bankformat['credit']]!=""){
					if($bankformat['debit']!="" && $bankformat['credit']!=""){//对账单借贷分开
						if (round((float)str_replace(',','',$data[$i][$bankformat['debit']]),2)!=0){
							if ($amo<0){
								$flg=-1;
							}
							$debit=$amo;
							
						}else{
							$amo=round((float)str_replace(',','',$data[$i][$bankformat['credit']]),2);
							if($amo<0){
								$flg=-1;
							}
							$amo=-$amo;
							$credit=-$amo;								
						}
					}else{
						$amo=	filter_number_format($data[$i][$bankformat['debit']]);	
						//prnMsg('//借贷在一行'.$amo);						
						if (round($amo,POI)>0){
							$debit=$amo;
							$credit=0;
						}else{
							$debit=0;
							$credit=-$amo;
						}	
					}						
				}// read debit credit
			   
				//prnMsg($debit.'='.$credit.'-'.trim($data[$i][$bankformat['debit']]).'[]'.trim($dc[0]));
				if ($payto==1){
					//收款单账号一列付款单位账号一列	中行对账单
					if ($amo>0){ 
						//收款的来源账号为preg_replace('# #','',//preg_replace('/^[(\xc2\xa0)|\s]+/', ''
						$toaccount=match_number($data[$i][$bankformat['payaccount']],1);
						$toname= match_chinese($data[$i][$bankformat['payname']]);
						$tobank=trim($data[$i][$bankformat['paybank']]);
					}else{
						//付款的目的账号									
						$toaccount=match_number($data[$i][$bankformat['toaccount']],1);
						$toname= match_chinese($data[$i][$bankformat['toname']]);
						$tobank=trim($data[$i][$bankformat['tobank']]);
					}
				}else{//收付   单位一列	
					$toaccount=match_number($data[$i][$bankformat['toaccount']],1);
					$toname= match_chinese($data[$i][$bankformat['toname']]);
					$tobank=trim($data[$i][$bankformat['tobank']]);
				}
				$serialnumber='';
				if (empty($bankformat['serialnumber'])){
					$serialnumber=$data[$i][$bankformat['serialnumber']];
				}
				//余额
				$balance=filter_number_format($data[$i][$bankformat['balance']]);
				//使用余额得到数据起始行  需要添加验证最末流水号
				//echo  "<br/>".$BankData['balancelast'].'+'.$amo.'=='.$balance.' &&'. $startrow;
				if (round($BankData['balancelast'],2)==round($balance-$amo,2) && $startrow==-1){
				
					if (empty($bankformat['serialnumber'])|| empty($serialnumber)){
					 //根据余额得到衔接开始行
						$startrow=$i; 
					   //  echo $serialnumber.' AA'.$startrow."<br/>";
					}elseif($serialnumber==$data[$i][$bankformat['serialnumber']]){
						//最末发生额的流水号验证、余额验证  后得到衔接开始行
						$startrow=$i; 
						//	echo $serialnumber.' BB'.$startrow."<br/>";
					}
				}
				
					//$data[$i][$bankformat['debit']].'<Debit['.$bankformat['balance'].'|'.$bankformat['debit'].']<br/>['.$i.'startrow['.$startrow.']={'.$BankData['balancelast'].'AMO>'.$amo.'=='.$balance."}");
				//	echo ("<br/>".$i.'[i-balance]'.$balance.'last'.$BankData['balancelast'].'amo'.$amo.'Total'.($balance-$amo).'startrow=i]'.$startrow.'日期'.$data[$i][$bankformat['bkdate']]);
					//if ($i>45)					exit;
				if ($i>=$startrow && $startrow!=-1){
					
					if (!isset($banktrans)){
						if($BankData['amountlast']>0){
							$debitlast=$BankData['amountlast'];
							$creditlast=0;
						}else{
							$creditlast=-$BankData['amountlast'];
							$debitlast=0;
						}	
						$banktrans[]=array("bkdate"=>$BankData['enddate'],	
											"serialnumber"=>$serialnumber,
											"debit"=>$debitlast,			
											"credit"=>$creditlast,
											"balance"=>$BankData['balancelast'],
											"curcode"=>'',
											"toaccount"	=>$BankData['toaccount'],
											"toname" =>$BankData['toname'],
											"tobank"=>  '',
											"remark"=>'上次导入的最末发生额',
											"abstract"=>mbStrSplit($BankData['abstract']),//.$lasttime,
											"flag"=>0);	
					}
				     if (strlen($toaccount)<4)
						$toaccount='';
					
					$banktrans[]=array("bkdate"=>date("Y-m-d h:i:s",$banktime),
										"serialnumber"=>$serialnumber,					                  
										"debit"=>$debit,				
										"credit"=>$credit,
										"balance"=>$balance,
										"curcode"=>$BankCurrency,
										"toaccount"	=>$toaccount,
										"toname" =>trim($toname),
										"tobank"=>  trim($tobank),
										"remark"=>mbStrSplit($data[$i][$bankformat['remark']]),//.$banktime,
										"abstract"=>mbStrSplit($data[$i][$bankformat['abstract']]),//.$lasttime,
										"flag"=>$flg);	
					//prnMsg(date("Y-m-d h:i:s",$banktime).'='.$debit.'[]'.$credit);				
				}				
			//}
		}else{
			//导入账单期小于上期最末日期
			$rowerr++;
		}      

	}//for
	if(is_array($banktrans)){//插入上期末余额和发生额      
			//var_dump($banktrans);
		//	array_values($banktrans);
		$i=0;	
		//依据余额校验读取数据是否合规
		foreach($banktrans as $key=>$row){
			if ($key==0){
				$balance=round($row['balance'],2);
			}else{
				if($row['debit']!=0){
					$balance=round($balance,2)+round($row['debit'],2);					
				}else{
					$balance=round($balance,2)-round($row['credit'],2);
				}
			
				if (round($balance,2)!=round($row['balance'],2)){
					//文件检查没有通过，删除上传文件
					
					if (unlink($pathfile)){
						$msg.=$_SESSION['InvFlag'][2]."删除文件完成！<br>"; 
						$SQL="DELETE FROM `bankupload` WHERE flag=0 AND uploadid=".$uploadid;
						$Result=DB_query($SQL);
					}
					return "-1,导入的数据顺序余额有错";
				    break;
				}
			}
			$banktransdata[]=$row;	
			
		}		
		return $banktransdata;    
	}else{
		//文件检查没  通过，删除上传文件
				
		if (unlink($pathfile)){
			$msg.=$_SESSION['InvFlag'][2]."删除文件完成！<br>"; 
			$SQL="DELETE FROM `bankupload` WHERE flag =0 AND uploadid=".$uploadid;
			$Result=DB_query($SQL);
		}else{
			$SQL="UPDATE `bankupload` SET flag=2 WHERE  uploadid=".$uploadid;
			$Result=DB_query($SQL);
		}
		return $rowerr.",导入数据有问题，请检查余额衔接、格式和日期！";
	}
	
}
function BankExcel(string $file = '', int $sheet = 0, int $columnCnt = 0, &$options = []){
    try {
		/* 转码 */
		//echo $file;
        $file = iconv("utf-8", "gb2312", $file);

        if (empty($file) OR !file_exists($file)) {
			throw new \Exception('文件不存在!');
			//return -1;
        }

        /** @var Xlsx $objRead */
        $objRead = IOFactory::createReader('Xlsx');

        if (!$objRead->canRead($file)) {
            /** @var Xls $objRead */
            $objRead = IOFactory::createReader('Xls');

            if (!$objRead->canRead($file)) {
				throw new \Exception('只支持导入Excel文件！');
				//return -1;
            }
        }

        /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
        empty($options) && $objRead->setReadDataOnly(true);
        /* 建立excel对象 */
        $obj = $objRead->load($file);
        /* 获取指定的sheet表 */
        $currSheet = $obj->getSheet($sheet);

        if (isset($options['mergeCells'])) {
            /* 读取合并行列 */
            $options['mergeCells'] = $currSheet->getMergeCells();
        }

        if (0 == $columnCnt) {
            /* 取得最大的列号 */
            $columnH = $currSheet->getHighestColumn();
            /* 兼容原逻，循时使用的是小于等于 */
            $columnCnt = Coordinate::columnIndexFromString($columnH);
        }      
        /* 获取总行数 */
		$rowCnt = $currSheet->getHighestRow();
		//echo  'Col'.$columnCnt."<br>".$rowCnt;		
        $data   = [];
        /* 读取内容 */
        for ($_row = 1; $_row <= $rowCnt; $_row++) {
            $isNull = true;

            for ($_column = 1; $_column <= $columnCnt; $_column++) {
                $cellName = Coordinate::stringFromColumnIndex($_column);
                $cellId   = $cellName . $_row;
                $cell     = $currSheet->getCell($cellId);

                if (isset($options['format'])) {
                    /* 获取格式 */
                    $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                    /* 记录格式 */
                    $options['format'][$_row][$cellName] = $format;
                }

                if (isset($options['formula'])) {
                    /* 获取公式，公式均为=号开头数据 */
                    $formula = $currSheet->getCell($cellId)->getValue();

                    if (0 === strpos($formula, '=')) {
                        $options['formula'][$cellName . $_row] = $formula;
                    }
                }

                if (isset($format) && 'm/d/yyyy' == $format) {
                    /* 日期   式翻转处理 */
                    $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                }

                $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                if (!empty($data[$_row][$cellName])) {
                    $isNull = false;
                }
            }

            /* 判断是否整行数据为空，是的话删除该行数据 */
            if ($isNull) {
                unset($data[$_row]);
            }
        }
      

        return $data;
    } catch (\Exception $e) {
		throw $e;
		return -1;
    }
}
?>
