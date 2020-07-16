<?php

/* $Id: MailSalesReport.php 6033 2013-06-24 07:36:26Z daintree $*/
/*Now this is not secure so a malicious user could send multiple emails of the report to the intended receipients
The intention is that this script is called from cron at intervals defined with a command like:
现在这是不安全的，因此恶意用户可以向预期的接收者发送报告的多封电子邮件
其目的是从cron调用此脚本，调用间隔由命令定义，如：

/usr/bin/wget http://localhost/web-erp/MailSalesReport.php

The configuration of this script requires the id of the sales analysis report to send
and an array of the receipients
此脚本的配置要求发送销售分析报告的id 以及一系列的接受者 */

/*The following three variables need to be modified for the report - the company database to use and the receipients
报告需要修改以下三个变量-要使用的公司数据库和收件人 */
/*The Sales report to send */
$_GET['ReportID'] = 2;
$AllowAnyone = true;
include('includes/session.php');
/*The company database to use */
$DatabaseName = $_SESSION['DatabaseName'];
/*The people to receive the emailed report */
$Recipients = GetMailList('SalesAnalysisReportRecipients');
if (sizeOf($Recipients) == 0) {
	$Title = _('Inventory Valuation') . ' - ' . _('Problem Report');
      	include('includes/header.php');
	prnMsg( _('There are no members of the Sales Analysis Report Recipients email group'), 'warn');
	include('includes/footer.php');
	//exit;
}
include ('includes/ConstructSQLForUserDefinedSalesReport.inc');
include ('includes/PDFSalesAnalysis.inc');

include('includes/htmlMimeMail.php');
$mail = new htmlMimeMail();

if ($Counter >0){ /* the number of lines of the sales report is more than 0  ie there is a report to send! */
	$pdf->Output($_SESSION['reports_dir'] .'/SalesAnalysis_' . date('Y-m-d') . '.pdf','F'); //save to file 
	$pdf->__destruct();
	$attachment = $mail->getFile($_SESSION['reports_dir'] . '/SalesAnalysis_' . date('Y-m-d') . '.pdf');
	$mail->setText(_('Please find herewith sales report'));
	$mail->SetSubject(_('Sales Analysis Report'));
	$mail->addAttachment($attachment, 'SalesAnalysis_' . date('Y-m-d') . '.pdf', 'application/pdf');
	if($_SESSION['SmtpSetting']==0){
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
		$result = $mail->send($Recipients);
	}else{
		$result = SendmailBySmtp($mail,$Recipients);
	}
} else {
	$mail->setText(_('Error running automated sales report number') . ' ' . $ReportID);
	if($_SESSION['SmtpSetting']==0){
		$mail->setFrom($_SESSION['CompanyRecord']['coyname'] . '<' . $_SESSION['CompanyRecord']['email'] . '>');
		$result = $mail->send($Recipients);
	}else{
		$result = SendmailBySmtp($mail,$Recipients);
	}
	
}

?>
