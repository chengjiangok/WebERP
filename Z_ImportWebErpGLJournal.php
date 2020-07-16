<?php

/* $Id: Z_ImportWebErpGLJournal.php 5776 2016-12-10 16:26:20 ChengJiang $*/
include('includes/DefineJournalClass.php');
include('includes/session.php');
$Title = _('Import General Ledger Transactions');
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img alt="" src="' . $RootPath . '/css/' . $Theme . 
		'/images/maintenance.png" title="' . 
		_('Import GL Payments Receipts Or Journals From CSV') . '" />' . ' ' . 
		_('Import GL Payments Receipts Or Journals From CSV') . '</p>';

$FieldHeadings = array( 'periodno',//0
                  'transno',//1
                  'type',//2
                  'typeno',//3
                 'trandate',	//4               
                   'account',	//5                
                  	'accountname',//6
                  'narrative',//7
                  	'amount',//8
               		'chequeno',//9
                  		'tag',//10
                  		'flg'	//11
                  		);


if (isset($_FILES['userfile']) and $_FILES['userfile']['name']) { //start file processing
	//check file info
	$FileName = $_FILES['userfile']['name'];
	$TempName  = $_FILES['userfile']['tmp_name'];
	$FileSize = $_FILES['userfile']['size'];
	$FieldTarget = 12;
	$InputError = 0;
 echo 
	//get file handle
	$FileHandle = fopen($TempName, 'r');

	//get the header row
	$HeadRow = fgetcsv($FileHandle,0, ",");

	//check for correct number of fields
	if (count($HeadRow) != count($FieldHeadings)) {
		prnMsg (_('File contains') . ' '. count($HeadRow) . ' ' . _('columns, expected') . ' ' . count($FieldHeadings) . '. ' . _('Try downloading a new template'),'error');
		fclose($FileHandle);
		include('includes/footer.php');
		exit;
	}

	//test header row field name and sequence
	$i = 0;
	foreach ($HeadRow as $HeadField) {
		if ( trim(mb_strtoupper($HeadField)) != trim(mb_strtoupper($FieldHeadings[$i]))) {
			prnMsg (_('File contains incorrect headers') . ' '. mb_strtoupper($HeadField). ' != '. mb_strtoupper($FieldHeadings[$i]). '. ' . _('Try downloading a new template'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit;
		}
		$i++;
	}


	//Total for transactions must come back to zero
	$TransactionTotal = 0;

	//loop through file rows
	$Row = 1;
	$transno=0;
	$period=0;

	while ( ($myrow = fgetcsv($FileHandle, 0, ',')) !== FALSE ) {

		//check for correct number of fields
		$FieldCount = count($myrow);
		if ($FieldCount != $FieldTarget){
			prnMsg (_($FieldTarget. ' fields required, '. $FieldCount. ' fields received'),'error');
			fclose($FileHandle);
			include('includes/footer.php');
			exit;
		}

		// cleanup the data (csv files often import with empty strings and such)
		foreach ($myrow as &$value) {
			$value = trim($value);
			$value = str_replace('"', '', $value);
		}

		//first off check that the account code actually exists
		$sql = "SELECT count(accountcode) FROM chartmaster WHERE accountcode like '" . $myrow[5] . "'";
		//$sql = "SELECT accountcode, accountname, group_, tag FROM chartmaster WHERE accountcode like '".substr($myrow[5],0,4)."' or accountcode like '" . $myrow[5] . "'";
		$result = DB_query($sql);
		
		$rows = DB_fetch_row($result);
		
		if ($rows[0] ==0) {
		//	$InputError = 1;
		//	prnMsg (_('Account code' . ' ' . $myrow[1] .'To rows'. $Row . 'does not exist'),'error');
		//	   prnMsg($sql,'info');                             
     $sql = "SELECT accountcode, accountname, group_, tag FROM chartmaster WHERE accountcode like '".substr($myrow[5],0,4)."'";
	   $result = DB_query($sql);
	   $row = DB_fetch_row($result);
		//	break;
			
			 $sql="INSERT INTO chartmaster( accountcode ,
                                      accountname ,
                                      group_,
                                      tag,
                                      crtdate ) 
                                      VALUES ('".$myrow[5]."',
                                      '". iconv('GB2312', 'UTF-8',$myrow[6])."',
                                      '".$row[2]."',
                                        '".$row[3]."',
                                          '".date('Y-m-d')."'
                                        )";
     
         $query = DB_query($sql); 
         
      	}
			

		if ($InputError !=1){
				$result = DB_Txn_Begin();
$SQL = "INSERT INTO gltrans ( transno,
					        type,
									typeno,
									trandate,
									periodno,
									account,
									narrative,
									amount,
									chequeNo,
									tag,
									flg)
								VALUES ('". $myrow[1]  ."',
								'" .$myrow[2]. "',
								'" . $myrow[3]. "',
								'" . $myrow[4] . "',
								'" . $myrow[0] . "',
								'" . $myrow[5] . "',
								'" . iconv('GB2312', 'UTF-8',$myrow[7]) . "',
								'" .$myrow[8]."',
								'".$myrow[9]."',
								'".$myrow[10]."',
								'".$myrow[11]."'
								)";
								$ErrMsg = _('Cannot insert a GL entry for the journal line because');
								$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
								$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
          $checksql="SELECT count(*) FROM   bankaccounts  where accountcode like '".$myrwo[5]."'";
          $checkresult = DB_query($checksql);
	      	$checkrow = DB_fetch_array($checkresult);
					if ( $checkrow[0] >0) {
		    //	$ReceiptTransNo = GetNextTransNo( 2, $db);
					$SQL = "INSERT INTO banktrans (
								transno,
								typeno,
								type,
								period,
								amountcleared,
								bankact,
								ref,
								exrate,
								functionalexrate,
								transdate,
								banktranstype,
								amount,
								currcode
							) VALUES ('".$myrow[1]."',
						'".$myrow[3]."',
								'2',
					'".$myrow[0]."',
							'0',							
						'".$myrow[5]."',
								'','1','1',							  
						'".$myrow[4]."',
								'',
						'".$myrow[5]."',
								'1')";
					$ErrMsg = _('Cannot insert a bank transaction because');
					$DbgMsg = _('Cannot insert a bank transaction with the SQL');
					$result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

	       	$result= DB_Txn_Commit();
					}

		//	$TransactionTotal+= (double)$myrow[6];
		}

	
		$Row++;


	}

	
	 echo '<table><tr><td>';

	echo '</tr>
	</td></table>';

	
	if ($InputError != 1 and round($TransactionTotal, 2) != 0) {
		$InputError = 1;
		prnMsg (_('The total of the transactions must balance back to zero'),'error');
	}

	if ($InputError == 1) { //exited loop with errors so rollback
		prnMsg(_('Failed on row') . ' ' . $Row. '. ' . _('Batch import has been rolled back'),'error');
		DB_Txn_Rollback();
	} else { //all good so commit data transaction
		DB_Txn_Commit();
		prnMsg( _('Batch Import of') .' ' . $FileName  . ' '.$Row. _('has been completed. All transactions committed to the database'),'success');
	}

	fclose($FileHandle);
	//include ('includes/GLPostings.inc');

} else { //show file upload form

	echo '<form action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '" method="post" class="noPrint" enctype="multipart/form-data">';
	echo '<div class="centre">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	 echo '<br /><input type="hidden" name="MAX_FILE_SIZE" value="1000000" />';
  echo '<div class="page_help_text">
			会计凭证导入工具简介：本工具是为了导入其他软件生成的会计凭证而设计，需要提供转换为csv文件的会计凭证，含有如下文件头，</br>
					 Period, Account,	Date,	Transno, Narrative,	Accountname,	AmountJ,	AmountD,	ChequeNo
							导入后失去凭证按付款收款用途分类功能。</br></div>';
	  echo _('Upload file') . ': <input name="userfile" type="file" />
			<input type="submit" name="submit" value="' . _('Send File') . '" />
		</div>
		</form>';

}

include('includes/footer.php');

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
