<?php
/*  PcMail.php
 * @Author: ChengJiang 
 * @Date: 2017-02-16 08:06:21 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-11-04 06:58:54
 */

include ('includes/session.php');
$Title = '邮件维护';// Screen identification.
$ViewTopic= 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
$BookMark = 'ProfitAndLoss';// Anchor's id in the manual's html document.
include('includes/SQL_CommonFunctions.inc');
include('includes/header.php');
echo'<script type="text/javascript">
	
</script>';			
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
  
	   <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	
echo '<table>';
	echo'<tr>
			<td>单元分组</td>
			<td >';
	SelectUnitsTag(2);
	echo'</td>
	</tr>';
		echo '<tr>
				  <td>' . _('Email to') . ':</td>
				  <td><input name="EmailTo" value="';
		while ($ContactDetails = DB_fetch_array($ContactsResult)) {
			if (mb_strlen($ContactDetails['email']) > 2 AND mb_strpos($ContactDetails['email'], '@') > 0) {
				echo $ContactDetails['email'];
			}
		}
		echo '"/>
			 </td></tr>
			 </table>';




if (isset($_POST['weberp_mail'])){ 
	echo '<table class="selection">	
	<th width="110">DEBUG DEMO</th>	';
	echo '<tr><td >ONE:';	
	/*
	$ConfirmationText = $ConfirmationText . ' ' . _('by user') . ' ' . $_SESSION['UserID'] . ' ' . _('at') . ' ' . Date('Y-m-d H:i:s');
	$EmailSubject = '采购计划收货单:'. $AdjustmentNumber . '号' .$InsertRow. '笔,已经录入,！'  ;
	
	if ($_SESSION['InventoryManagerEmail']!=''){
					
		if($_SESSION['SmtpSetting']==0){
				mail($_SESSION['InventoryManagerEmail'],$EmailSubject,$ConfirmationText);
		}else{
			include('includes/htmlMimeMail.php');
			$mail = new htmlMimeMail();
			$mail->setSubject($EmailSubject);
			$mail->setText($ConfirmationText);
			$result = SendmailBySmtp($mail,array($_SESSION['InventoryManagerEmail']));
		}					
	}
	ECHO '</td></tr>';
	echo '<tr><td >';*/
	echo $_SESSION['InventoryManagerEmail'];
					
    echo '<br>SmtpSetting:'.$_SESSION['SmtpSetting'];	
	//echo '</td></tr>';	*/
	var_dump($_SESSION['Transfer']);
		//以下发送邮件代码未启用
		/*
		$EmailSQL="SELECT email
					FROM www_users, departments
					WHERE departments.authoriser = www_users.userid
						AND departments.departmentid = '" . $_SESSION['Request']->Department ."'";
		$EmailResult = DB_query($EmailSQL);*/
		$myEmail='2914846009@qq.com';
		mail($_SESSION['InventoryManagerEmail'],$EmailSubject,$ConfirmationText);
		//if ($myEmail=DB_fetch_array($EmailResult)){
			$ConfirmationText = _('An internal stock request has been created and is waiting for your authoritation');
			$EmailSubject = _('Internal Stock Request needs your authoritation');
			if($_SESSION['SmtpSetting']==0){
				prnMsg('mail');
				mail($myEmail,$EmailSubject,$ConfirmationText);
			}else{
				include('includes/htmlMimeMail.php');
				$mail = new htmlMimeMail();
				$mail->setSubject($EmailSubject);
				$mail->setText($ConfirmationText);
				$result = SendmailBySmtp($mail,array($myEmail));
			}

		
		echo '</td></tr>';
			
	echo '	</table>';
	//	prnMsg($RequestNo.' 采购申请单已经建立现在需要审批！', 'success');
	


			
}else if (isset($_POST['SendMail'])){
		require_once dirname(__FILE__) .'/Classes/class.phpmailer.php';
		//	require('class.phpmailer.php');  
			
			$mail = new PHPMailer(); //实例化  
			
			$mail->IsSMTP(); // 启用SMTP  
			
			$mail->Host = "smtp.163.com"; //SMTP服务器 163邮箱例子  
			//$mail->Host = "smtp.126.com"; //SMTP服务器 126邮箱例子  
			//$mail->Host = "smtp.qq.com"; //SMTP服务器 qq邮箱例子  
			
			$mail->Port = 25;  //邮件发送端口  
			$mail->SMTPAuth   = true;  //启用SMTP认证  
			
			$mail->CharSet  = "UTF-8"; //字符集  
			$mail->Encoding = "base64"; //编码方式  
			
			$mail->Username = "okjc@163.com";  //你的邮箱  
			$mail->Password = "atiger90";  //你的密码  
			$mail->Subject = "成 你好"; //邮件标题  
			
			$mail->From = "okjc@163.com";  //发件人地址（也就是你的邮箱）  
			$mail->FromName = "chengjiang";   //发件人姓名  
			
			$address = "2914846009@qq.com";//收件人email  
			$mail->AddAddress($address, "cheng");    //添加收件人1（地址，昵称）  
			//$mail->AddAddress($address2, "xxx2");    //添加收件人2（地址，昵称）  
			
			$mail->AddAttachment('123.xls','我的附件.xls'); // 添加附件,并指定名称  
			$mail->AddAttachment('1234.xlsx','我的附件1.xlsx'); // 可以添加多个附件  
			//$mail->AddAttachment('xx2.xls','我的附件2.xls'); // 可以添加多个附件  
			
			$mail->IsHTML(true); //支持html格式内容  
			$mail->AddEmbeddedImage("logo.jpg", "my-attach", "logo.jpg"); //设置邮件中的图片  
			$mail->Body = '你好, <b>朋友</b>! <br/>这是一封邮件！'; //邮件主体内容  
			
			//发送  
			if(!$mail->Send()) {  
			echo "发送失败: " . $mail->ErrorInfo;  
			} else {  
			echo "成功";  
			}  
		
}else if(isset($_POST['DoIt'])){
	include('includes/htmlMimeMail.php');
		
	$mail = new htmlMimeMail();
	$attachment = $mail->getFile($_SESSION['reports_dir'] . '/' . $PdfFileName);
	$mail->setText(_('Please Process this Work order number') . ' ' . $SelectedWO);
	$mail->setSubject(_('Work Order Number') . ' ' . $SelectedWO);
	$mail->addAttachment($attachment, $PdfFileName, 'application/pdf');
	//since sometime the mail server required to verify the users, so must set this information.
	if($_SESSION['SmtpSetting'] == 0){
		//use the mail service provice by the server.使用服务器提供的邮件服务。
				
		$mail->setFrom($_SESSION['CompanyRecord'][1]['coyname'] . '<' . $_SESSION['CompanyRecord'][1]['email'] . '>');
		$Success = $mail->send(array($_POST['EmailTo']));
		prnMsg('499='.$_SESSION['CompanyRecord'][1]['email'] .	$Success); 
	}else if($_SESSION['SmtpSetting'] == 1) {
		$Success = SendmailBySmtp($mail,array($_POST['EmailTo']));

	}else{
		prnMsg(_('The SMTP settings are wrong, please ask administrator for help'),'error');
	
	}

	if ($Success == 1) {
		
		prnMsg(_('Work Order') . ' ' . $SelectedWO . ' ' . _('has been emailed to') . ' ' . $_POST['EmailTo'] . ' ' . _('as directed'), 'success');

	} else { //email failed
		
		prnMsg(_('Emailing Work order') . ' ' . $SelectedWO . ' ' . _('to') . ' ' . $_POST['EmailTo'] . ' ' . _('failed'), 'error');
	}

}elseif(){
	if (isset($_REQUEST['email'])) { // 如果接收到邮箱参数则发送邮件
		// 发送邮件
		$email = $_REQUEST['email'] ;
		$subject = $_REQUEST['subject'] ;
		$message = $_REQUEST['message'] ;
		mail("someone@example.com", $subject,
		$message, "From:" . $email);
		echo "邮件发送成功";
	} else { // 如果没有邮箱参数则显示表单
		echo "<form method='post' action='mailform.php'>
		Email: <input name='email' type='text'><br>
		Subject: <input name='subject' type='text'><br>
		Message:<br>
		<textarea name='message' rows='15' cols='40'>
		</textarea><br>
		<input type='submit'>
		</form>";
	}
}
echo '<br />
		<div class="centre">
    		<input type="submit" name="Search" value="' . _('Search') . '" />
			<input type="submit" name="DoIt" value="' . _('Paperwork') . '" />

		
				<input type="submit" name="SendMail" value="SendMail" />
				<input type="submit" name="weberp_mail" value="WebERP_Mail" />	';
				'</div>';
		echo '</div>
		</form>';

$SQL="SELECT `userid`, `realname`, `email` phone  FROM www_users WHERE length(userid)>=4 AND branchcode=0";
if(isset($_POST['Search'])){
}

include('includes/footer.php');
echo '<script type="text/javascript">
   		function reload(){
			window.location.reload();
			}
	  </script>';

/*if (isset($_POST['demo'])){
	//	function CryptPass( 
	$Password =$_POST['Password'];
		
	//	return $Hash;
	
		echo '<table class="selection">	
		<th width="110">STUDY DEMO</th>	';
		echo '<tr><td >1:base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM))';		
	   
		if (PHP_VERSION_ID < 50500) {
			$Salt = base64_encode(mcrypt_create_iv(22, MCRYPT_DEV_URANDOM));
			echo '<br>'.$Salt;
			$Salt = str_replace('+', '.', $Salt);
			echo '<br>';
			echo '2:str_replace('+', '.', $Salt)';
			echo '<br>';
			echo $Salt ;
			$Hash = crypt($Password, '$2y$10$' . $Salt . '$');
			echo '<br>';
			
			 echo $Hash;
			 echo '<br>';
		} else {
			$Hash = password_hash($Password,PASSWORD_DEFAULT);
			echo  '4:password_hash($Password,PASSWORD_DEFAULT);';
			echo '<br>';
			echo $Hash;
			echo '<br>';
		}		
		
		echo '<br>';
		echo'</td></tr>';
		echo '<tr><td>3: crypt($Password, $2y$10$ . $Salt . $)';
		 echo '<br>5:PHP_VERSION_ID';
		 echo '<br>'.PHP_VERSION_ID;	
		ECHO '</td></tr>';		
		echo '	</table>';
}else*/


	?>
