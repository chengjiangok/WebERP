<?php


/* $Id: GLAccountCSV.php 4492 2011-02-18 09:56:52Z daintree $ */

include ('includes/session.php');
$Title = _('General Ledger Account Report');

$ViewTopic= 'GeneralLedger';
$BookMark = 'GLAccountCSV';

include('includes/header.php');
include('includes/GLPostings.inc');

if (isset($_POST['Period'])){
	$SelectedPeriod = $_POST['Period'];
} elseif (isset($_GET['Period'])){
	$SelectedPeriod = $_GET['Period'];
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . _('General Ledger Account Inquiry') . '" alt="" />' . ' ' . _('General Ledger Account Report') . '</p>';

echo '<div class="page_help_text">' . _('Use the keyboard Shift key to select multiple accounts and periods') . '</div><br />';

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

/*Dates in SQL format for the last day of last month*/
$DefaultPeriodDate = Date ('Y-m-d', Mktime(0,0,0,Date('m'),0,Date('Y')));

/*Show a form to allow input of criteria for the report */
echo '<table>
	        <tr>
	         <td>' . _('Selected Accounts') . ':</td>
	         <td><select name="Account[]" size="25" multiple="multiple">';
	         /*
$sql = "SELECT chartmaster.accountcode, 
			   chartmaster.accountname
		FROM chartmaster 
		INNER JOIN glaccountusers ON glaccountusers.accountcode=chartmaster.accountcode AND glaccountusers.userid='" .  $_SESSION['UserID'] . "' AND glaccountusers.canview=1
		ORDER BY chartmaster.accountcode";*//*
		$sql = "SELECT chartmaster.accountcode, 
			   chartmaster.accountname
		FROM chartmaster 	
		ORDER BY chartmaster.accountcode";*/
		$sql="SELECT groupname accountcode,  pandl , parentgroupname accountname  FROM erpdebug.accountgroups;";
$AccountsResult = DB_query($sql);
$i=0;
while ($myrow=DB_fetch_array($AccountsResult,$db)){
	if(isset($_POST['Account'][$i]) AND $myrow['accountcode'] == $_POST['Account'][$i]){
		echo '<option selected="selected" value="' . $myrow['accountcode'] . '">' . $myrow['accountcode'] . '- ' . htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
		$i++;
	} else {
		$abd=sprintf("%-'-40s", $myrow['accountcode']);
		echo '<option value="' . $myrow['accountcode'] . '">' .$abd.  htmlspecialchars($myrow['accountname'], ENT_QUOTES, 'UTF-8', false) . '</option>';
	}
}
echo '</select></td></tr>';



echo '</table><br />
		<div class="centre"><input type="submit" name="MakeCSV" value="'._('Make CSV File').'" /></div>
    </div>
	</form>';

/* End of the Form  rest of script is what happens if the show button is hit*/

if (isset($_POST['MakeCSV'])){

	if (!isset($_POST['Account'])){
		prnMsg(_('An account or range of accounts must be selected from the list box'),'info');
		include('includes/footer.php');
		exit;
	}

	if (!file_exists($_SESSION['reports_dir'])){
		$Result = mkdir('./' . $_SESSION['reports_dir']);
	}

	$FileName = $_SESSION['reports_dir'] . '/Accounts_Listing_' . Date('Y-m-d') .'.csv';

	$fp = fopen($FileName,'w');

	if ($fp==FALSE){
		prnMsg(_('Could not open or create the file under') . ' ' . $FileName,'error');
		include('includes/footer.php');
		exit;
	}

	foreach ($_POST['Account'] as $SelectedAccount){
		/*Is the account a balance sheet or a profit and loss account */
		$SQL = "SELECT chartmaster.accountname,
								accountgroups.pandl
							    FROM accountgroups
							    INNER JOIN chartmaster ON accountgroups.groupname=chartmaster.group_
							    WHERE chartmaster.accountcode='" . $SelectedAccount . "'";
		$result = DB_query($SQL);
		$AccountDetailRow = DB_fetch_row($result);
		$AccountName = $AccountDetailRow[1];
		if ($AccountDetailRow[1]==1){
			$PandLAccount = True;
		}else{
			$PandLAccount = False; /*its a balance sheet account */
		}

	$sql = "SELECT accountcode,
			accountname,
			group_,
			CASE WHEN pandl=0 THEN '" . _('Balance Sheet') . "' ELSE '" . _('Profit/Loss') . "' END AS acttype
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname and chartmaster.accountcode = '" . $SelectedAccount . "'
		ORDER BY chartmaster.accountcode";
		
		
		$ErrMsg = _('The transactions for account') . ' ' . $SelectedAccount . ' ' . _('could not be retrieved because') ;
		$TransResult = DB_query($sql,$ErrMsg);

		$j = 1;
		$k=0; //row colour counter

		while ($myrow=DB_fetch_array($TransResult)) {
				fwrite($fp, $SelectedAccount .','.iconv( "UTF-8", "gb2312" , $myrow['accountname']).','.iconv( "UTF-8", "gb2312" , $myrow['group_'])."\n");

		}

	} /*end for each SelectedAccount */
	fclose($fp);
	echo '<p><a href="' .  $FileName . '">' . _('click here') . '</a> ' . _('to view the file') . '<br />';
} /* end of if CreateCSV button hit */

include('includes/footer.php');
?>