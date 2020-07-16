<?php
/* $Id: ZT_AccountUnits.php  ChengJiang $*/
/*
可能没有使用20190928
 * @Author: mikey.zhaopeng 
 * @Date: 2017-02-09 10:00:45 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-03-11 21:56:46
 */
include('includes/session.php');
$Title = '科目编码-客户编码维护';
/* Manual links before header.php */
$ViewTopic= 'GeneralLedger';// Filename in ManualContents.php's TOC.
$BookMark = 'GLAccounts';// Anchor's id in the manual's html document.
include('includes/header.php');
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' .
		_('General Ledger Accounts') . '" />' . ' ' . $Title . '</p>';
echo '<form method="post" id="GLAccounts" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">';
  echo '<div>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">
	科目编码-客户编码维护功能简介<br>
	科目-客户查询.....根据应收应付的科目编码查询对应单位编码！<br>
	客户-科目查询：根据单位编码查询对应科目科目代码！<br>
	根据查询自动生成单位编码和科目编码。
	</div>'; 
	echo '<table class="selection">';
	echo '<tr>
  	      <td>科目类别:</td>
		      <td><select name="accunit" size="1" >';	
			 		if ($_POST['accunit']==1122) {
						echo '<option  selected="selected"  value="1122" >[客户]应收账款</option>';
						echo '<option  value="2202" >[2202]应付账款</option>';
			 		}else {
						echo '<option   value="1122" >[1122]应收账款</option>';
						echo '<option  selected="selected" value="2202" >[供应商]应付账款</option>';
			 		}
			 		
	echo	'</select>
	      </td>
	      </tr>';
	if (isset($_SESSION['Tag'])){
			$sql="SELECT tagID, tagdatabase,tagdescription FROM unittag WHERE flag=0 ORDER BY tagID ";
			$result = DB_query($sql);
	
		echo '<tr>
				<td>',_('Unit Tag'),'</td>
				<td><select name="unittag" size="1" >';
			$k=0;
	
	
		while ($myrow=DB_fetch_array($result,$db)){
			if(isset($_POST['unittag']) AND $myrow['tagID']==$_POST['unittag'] ){
				echo '<option selected="selected" value="';
			
			} else {
			
				echo '<option value="';
			}
				echo $myrow['tagID'] . '">' .$myrow['tagdescription']  . '</option>'; 
		}
			echo'</select>
				</td></tr>';
	}    
        
	echo'	</table>
		<br />
		<div class="centre">
		
			<input type="submit" name="Search" value="科目-客户查询" />
					<input type="submit" name="UnitsAccount" value="客户-科目查询" />';
					if(isset($_POST['Search'])){
						echo'<br><input type="submit" name="crtunits" value="生成单位编码" />';
					}	
					if(isset($_POST['UnitsAccount'])){
						echo'<br><input type="submit" name="crtaccount" value="生成科目编码" />';
					}	
		//<input type="submit" name="accset" value="科目设置" /><br />
	echo'	</div>';
if ( isset($_POST['Search'])) {
     
	 if ($_POST['accunit']=='1122'){
	 	$sql = "SELECT accountcode ,
		                chartmaster.accountname,
								unitscode,
						debtorsmaster.name,					
						unittype ,
						chartmaster.tag
		             FROM chartmaster 
					 LEFT JOIN accountunits ON chartmaster.accountcode=accountunits.account
					 LEFT JOIN debtorsmaster ON accountunits.unitscode=debtorsmaster.debtorno
					 WHERE accountcode LIKE '1122_%' AND chartmaster.tag='".$_POST['unittag']."' ";
		
	 }else{
    	$sql = "SELECT accountcode ,
		chartmaster.accountname,
				unitscode,
		debtorsmaster.name,					
		unittype ,
		chartmaster.tag
	 FROM chartmaster 
	 LEFT JOIN accountunits ON chartmaster.accountcode=accountunits.account
	 LEFT JOIN debtorsmaster ON accountunits.unitscode=debtorsmaster.debtorno
	 WHERE accountcode LIKE '2202_%' AND chartmaster.tag='".$_POST['unittag']."' ";
	

	 }
   
	$ErrMsg = _('The chart accounts could not be retrieved because');

	$result = DB_query($sql,$ErrMsg);

	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Account Code') . '</th>
			<th class="ascending">' . _('Account Name') . '</th>
			<th class="ascending">单位编码</th>
			<th class="ascending">分组</th>
			<th class="ascending">选择</th>
		</tr>';

	$k=0; //row colour counter

	while ($row = DB_fetch_array($result)) {
		
		if (substr($row['accountcode'],0,4)=='1122'){
			if (strlen($row['unitscode'])<3){
				$unitcode=substr($row['accountcode'],5).' '.substr($row['accountcode'],4,1).$row['tag'].'1';
				$chkstr='checked';
			}else{
				$unitcode=$row['unitscode'];
			}
		}else{
			if (strlen($row['unitscode'])<3){
				$unitcode=substr($row['accountcode'],4);
				$chkstr='checked';
			}else{
				$unitcode=$row['unitscode'];
			}
		}
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}
		printf('<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td> <input type="checkbox" name="chose[]" value="%s" '.$chkstr.'></td>	
			</tr>',
			$row['accountcode'],
			htmlspecialchars($row['accountname'],ENT_QUOTES,'UTF-8'),		
			$unitcode,
			$row['tag'],
			$row['accountcode']);

	}

	echo '</table>';

}elseif(isset($_POST['accunitset'])){
	 $sql="SELECT accountcode acccode, accountname accname, tag FROM chartmaster WHERE LEFT(accountcode,4) ='".$_POST['accunit']."' AND   accountcode NOT IN (SELECT account FROM accountunits WHERE unittype=".substr($_POST['accunit'],0,1).")";
   $result = DB_query($sql);
	
	echo '<br /><table class="selection">';
	echo '<tr>
			<th class="ascending">' . _('Account Code') . '</th>
			<th class="ascending">' . _('Account Name') . '</th>
		
		</tr>';

 echo '<tr>
     			
  		<td><select name="acccode" size="20" >';
  		$k=0;
  
  while ($myrow=DB_fetch_array($result,$db)){	
		echo '<option value="';
		echo $myrow['acccode'] . '">' .$myrow['acccode'].'^'.$myrow['accname']  . '</option>'; 
	}
		echo'</select>
		 </td>';	
		if ($_POST['accunit']==1122) {
	 $sql="SELECT debtorno unitcode,branchcode,  brname unitname FROM custbranch WHERE concat(debtorno,branchcode)  NOT IN (SELECT  concat(unitscode,branchcode) FROM accountunits WHERE unittype=1 )";
  }else{
  	 $sql="SELECT supplierid unitcode,'' branchcode, suppname unitname FROM suppliers";  
  }
  
        $result = DB_query($sql);	
	echo'<td><select name="unitcode" size="20" >'; 
  		while ($myrow=DB_fetch_array($result,$db)){		
			echo '<option value="';		
			echo $myrow['unitcode'] .'^'.$myrow['branchcode'] .  '">' .$myrow['unitcode'].'^'.$myrow['unitname']  . '</option>'; 
	}
	echo'</select>
		 </td>	
		</tr>';
	echo '</table>'; 	
}elseif(isset($_POST['accset'])){
	//
	$sql="INSERT INTO accountunits(account, unitscode,branchcode, tag, unittype) VALUES(".$_POST['acccode'].",".$_POST['unitcode'].",".$_POST['unitcode'].",".$_POST['unittag'].",".substr($_POST['accunit'],0,1).")";
	$result=DB_query($sql);
}elseif(isset($_POST['UnitsAccount'])){
	
	if ($_POST['accunit']=='1122'){
		$sql = "SELECT accountcode ,
					   chartmaster.accountname,
							   unitscode,
					   debtorsmaster.name,					
					   unittype ,
					   chartmaster.tag
					FROM accountunits 
					LEFT JOIN chartmaster ON chartmaster.accountcode=accountunits.account
					LEFT JOIN debtorsmaster ON accountunits.unitscode=debtorsmaster.debtorno
					WHERE accountcode LIKE '1122_%' AND chartmaster.tag='".$_POST['unittag']."' ";
	   
	}else{
	   $sql = "SELECT accountcode ,
	   chartmaster.accountname,
			   unitscode,
	   debtorsmaster.name,					
	   unittype ,
	   chartmaster.tag
	FROM accountunits
	LEFT JOIN  chartmaster ON chartmaster.accountcode=accountunits.account
	LEFT JOIN debtorsmaster ON accountunits.unitscode=debtorsmaster.debtorno
	WHERE accountcode LIKE '2202_%' AND chartmaster.tag='".$_POST['unittag']."' ";
   

	}
  
   $ErrMsg = _('The chart accounts could not be retrieved because');

   $result = DB_query($sql,$ErrMsg);
   
   echo '<br /><table class="selection">';
   echo '<tr>
		   <th class="ascending">' . _('Account Code') . '</th>
		   <th class="ascending">' . _('Account Name') . '</th>
		   <th class="ascending">单位编码</th>
		   <th class="ascending">分组</th>
		   <th class="ascending">选择</th>
	   </tr>';

   $k=0; //row colour counter

   while ($row = DB_fetch_array($result)) {
	   
	   if (substr($row['accountcode'],0,4)=='1122'){
		   if (strlen($row['unitscode'])<3){
			   $unitcode=substr($row['accountcode'],5).' '.substr($row['accountcode'],4,1).$row['tag'].'1';
			   $chkstr='checked';
		   }else{
			   $unitcode=$row['unitscode'];
		   }
	   }else{
		   if (strlen($row['unitscode'])<3){
			   $unitcode=substr($row['accountcode'],4);
			   $chkstr='checked';
		   }else{
			   $unitcode=$row['unitscode'];
		   }
	   }
	   if ($k==1){
		   echo '<tr class="EvenTableRows">';
		   $k=0;
	   } else {
		   echo '<tr class="OddTableRows">';
		   $k=1;
	   }
	   printf('<td>%s</td>
		   <td>%s</td>
		   <td>%s</td>
		   <td>%s</td>
		   <td> <input type="checkbox" name="chose[]" value="%s" '.$chkstr.'></td>	
		   </tr>',
		   $row['accountcode'],
		   htmlspecialchars($row['accountname'],ENT_QUOTES,'UTF-8'),		
		   $unitcode,
		   $row['tag'],
		   $row['accountcode']);

   }

   echo '</table>';
}elseif(isset($_POST['crtunits'])){
	prnMsg('289','info');
	$sql="INSERT INTO accountunits(account, unitscode,branchcode, tag, unittype) VALUES(".$_POST['acccode'].",".$_POST['unitcode'].",".$_POST['unitcode'].",".$_POST['unittag'].",".substr($_POST['accunit'],0,1).")";
	$result=DB_query($sql);
	if (empty($_POST['chose'])){
		prnMsg('你没有选择改写的科目！','info');
	
	}else{

       foreach($_POST['chose'] as $val){
		   $accstr=explode('^',$val);
		   $sql="UPDATE `chartmaster` SET accountname='".$accstr[1]."',group_='".$accstr[2]."'  WHERE accountcode='".$accstr[0]."'";
		   $result = DB_query($sql);
		   if ($result){
			prnMsg($accstr[0].$accstr[1].'更新成功！','info');
		   }
	   }
	 
	}		
}elseif(isset($_POST['crtaccount'])){
	prnMsg('291','info');
	if (empty($_POST['chose'])){
		prnMsg('你没有选择改写的科目！','info');
	
	}else{

       foreach($_POST['chose'] as $val){
		   $accstr=explode('^',$val);
		   $sql="UPDATE `chartmaster` SET accountname='".$accstr[1]."',group_='".$accstr[2]."'  WHERE accountcode='".$accstr[0]."'";
		   $result = DB_query($sql);
		   if ($result){
			prnMsg($accstr[0].$accstr[1].'更新成功！','info');
		   }
	   }
	 
	}		
}

//end of ifs and buts!
echo'</form><br/>';
include('includes/footer.php');
?>