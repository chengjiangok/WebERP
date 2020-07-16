
<?php
/* $Id: InternalStockRequestAuthorisation.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-10 20:37:35 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-09-11 21:01:26
 */
include('includes/session.php');

$Title ='采购计划授权';// _('Authorise Internal Stock Requests');
$ViewTopic = 'Inventory';
$BookMark = 'AuthoriseRequest';

include('includes/header.php');

echo'<script type="text/javascript">
   function inSelectAuthor(p,i,d){
      p.value=4;
	 }
   function inTaxCat(p,i,d){		
		document.getElementById(i+"SelectAuthor"+d).value=4;
		document.getElementById(i+"SelectAuthor"+d).checked=true;
	}
	function inRemark(p,i,d){
		document.getElementById(i+"SelectAuthor"+d).value=4;
		document.getElementById(i+"SelectAuthor"+d).checked=true;
	}
	function inPrice(p,i,d){
		document.getElementById(i+"SelectAuthor"+d).value=4;
		document.getElementById(i+"SelectAuthor"+d).checked=true;
	}
	function inQTY(p,d){
		var  n=p.name.split("Quantity");	
		//alert(p.value);	
		var qty=(1*p.value).toFixed(d);
		if (parseFloat(p.value)!=qty){
			p.value=qty;
			alert("你输入数字小数位数和设置不同,系统自动按设置计算,默认"+d+"位!");
		}
		if (qty!=0){		
			//数量不为空
			document.getElementById(n[0]+"edit"+n[1]).value=1;		
		}		
	   //alert(	document.getElementById(n[0]+"edit"+n[1]).value);
	}
</script>';
echo'<style type="text/css">	      
			.lengthcss{			
					overflow:hidden;
					text-overflow:ellipsis;
					display:-webkit-box;
					-webkit-box-orient:vertical;
					-webkit-line-clamp:2;				
					}
					</style>';
if(in_array(13,$_SESSION['AllowedPageSecurityTokens'])){
	//授权
	$AuthorPrice=2;
}
if (!isset( $_POST['Author'])){
	//if($AuthorPrice==2){
		$_POST['Author']=1;	
}

if(isset($_POST['Go1']) OR isset($_POST['Go2'])) {
	$_POST['PageOffset'] = (isset($_POST['Go1']) ? $_POST['PageOffset1'] : $_POST['PageOffset2']);
	$_POST['Go'] = '';
}
if (!isset($_POST['PageOffset'])) {
	$_POST['PageOffset'] = 1;
} else {
	if ($_POST['PageOffset'] == 0) {
		$_POST['PageOffset'] = 1;
	}
}
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/transactions.png" title="' . $Title . '" alt="" />' . ' ' . $Title . '</p>';

if(in_array($_SESSION['PageSecurityArray']['OrderEntryDiscountPricing'], $_SESSION['AllowedPageSecurityTokens'])) {//Is it an internal user with appropriate permissions?
	$AuthorPrice=2;// Show two additional columns: 'Discount' and 'GP %'.
} else {
	$AuthorPrice=0;// Do NOT show 'Discount' and 'GP %'.
}
$TaxSql="SELECT `taxcatid`, `taxcatname` ,taxrate FROM `taxcategories` WHERE onorder=2 OR onorder=3 ";
	$TaxResult=DB_query($TaxSql);
	$TaxArr=array();
	while($row=DB_fetch_array($TaxResult)){
         $TaxArr[$row['taxcatid']]=$row['taxrate'];
	}
if (!isset($_POST['DispatchDate'])){
	$_POST['DispatchDate'] =date("Y-m-d",strtotime("last month"));
}

echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '">';
echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
		<input type="hidden"  name="Location" value=' . $_POST['Location'] . ' />
		<input type="hidden"  name="Location" value=' . $_POST['Department'] . ' />';
		if($_SESSION['AllowedDepartment'] == 0){
			// any internal department allowed
			$sql="SELECT departmentid,
						description
					FROM departments
					ORDER BY description";
		}else{
			// just 1 internal department allowed
			$sql="SELECT departmentid,
						description
					FROM departments
					WHERE departmentid = '". $_SESSION['AllowedDepartment'] ."'
					ORDER BY description";
		}
		$result=DB_query($sql);
echo '<table class="selection">';
echo '<tr>
		<th colspan="2"><h4>采购计划单明细</h4></th>
	</tr>
	<tr>
		<td>' . _('Department') . ':</td>';

echo '<td><select name="Department">';
echo'<option value="">' . _('All') . '</option>';
	while ($myrow=DB_fetch_array($result)){
		if ($_POST['Department']==$myrow['departmentid']){
			echo '<option selected="True" value="' . $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="' . $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
echo '</select></td>
	</tr>
	<tr>
		<td>选择仓库:</td>';
$sql="SELECT locations.loccode,
			locationname
		FROM locations
		INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
		WHERE internalrequest = 1
		ORDER BY locationname";

$result=DB_query($sql);
echo '<td><select name="Location">
		<option value="">' . _('All') . '</option>';
	while ($myrow=DB_fetch_array($result)){
		if ($_POST['Location']==$myrow['loccode']){
			echo '<option selected="True" value="' . $myrow['loccode'] . '">' . $myrow['loccode'].' - ' .htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['loccode'].' - ' .htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
echo '</select></td>
	</tr>
	<tr>
		<td>' . _('Date when required') . ':</td>';
echo '<td><input type="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="DispatchDate" maxlength="10" size="11" value="' . $_POST['DispatchDate'] . '" /></td>
      </tr>';
echo'<tr><td>选择类别</td>
		<td collspan="2">';
echo '<input type="radio" name="Author" value="0" '. ($_POST['Author']==0 ?"checked":"").' />全部
	  <input type="radio" name="Author" value="1" '. ($_POST['Author']==1 ?"checked":"").' />未授权       
	  <input type="radio" name="Author" value="2" '. ($_POST['Author']==2 ?"checked":"").' />已授权';

echo'</td></tr>
	</table>
	<br /><br />
		<div class="centre">';
	echo'<input type="submit" name="Search" value="' . _('Search'). '" />
	     <input type="submit" name="crtExcel" value="导出Excel" />';
	if (isset($_POST['Search'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
	OR isset($_POST['Previous'])) {
	
		echo'<input type="submit" name="UpdateAll" value="' . _('Update'). '" />';
	}
	echo'</div>';
if ($AuthorPrice==2){
	PrnMsg('计划单授权！','info');
}else{
	PrnMsg('计划单划价！','info');
}
	$sql="SELECT stockrequest.dispatchid,
								locations.locationname,
								stockmaster.categoryid loccode,
								stockmaster.taxcatid ,
								stockrequest.despatchdate,
								stockrequest.narrative,
								departments.departmentid,
								departments.description departname,
								www_users.realname,
								www_users.email,
								stockrequestitems.stockid,
								stockrequestitems.dispatchitemsid,
								stockrequestitems.stockid,
								stockrequestitems.taxprice,
								stockrequestitems.taxcatid taxid,
								stockrequestitems.cess,
								stockrequestitems.decimalplaces,
								stockrequestitems.remark,
								stockrequestitems.uom,
								stockrequestitems.qtydelivered,
								stockmaster.description,
								stockrequestitems.quantity,
								stockrequest.authorised,
								stockrequest.closed,
								stockrequest.allowprint,			
								stockrequestitems.completed,
								stockrequestitems.pricingdate,
								stockrequestitems.auditdate
			FROM stockrequest INNER JOIN departments	ON stockrequest.departmentid=departments.departmentid
			LEFT JOIN stockrequestitems ON stockrequestitems.dispatchid=stockrequest.dispatchid			
			INNER JOIN stockmaster	ON stockmaster.stockid=stockrequestitems.stockid
			INNER JOIN locationusers ON locationusers.loccode=stockmaster.categoryid AND  locationusers.userid='" .  $_SESSION['UserID'] . "'  AND locationusers.canupd=1
			INNER JOIN www_users	ON www_users.userid=departments.authoriser		
			INNER JOIN locations ON locations.loccode=stockmaster.categoryid
			WHERE 1 ";
			//initiator='".$_SESSION['UserID']."' ";
	$wh=0;

    if(in_array(13,	$_SESSION['AllowedPageSecurityTokens'])){
		//if(in_array($_SESSION['PageSecurityArray']['OrderEntryDiscountPricing'], $_SESSION['AllowedPageSecurityTokens'])) 
		//授权		13
	}
	if ($_POST['Department']!=""){

	   	$sql.=" AND departments.departmentid='".$_POST['Department']."'";
	} 
	if (!$_POST['Location']==""){

		$sql.=" AND  locations.loccode='".$_POST['Location']."'";
	}
	if(!$_POST['DispatchDate']==""){
	
		$sql.=" AND  stockrequest.despatchdate>='".$_POST['DispatchDate']."'";
	} 
	//if (!($_POST['Author'][0]==1 && $_POST['Author'][1]==2)){
	  
		if ($_POST['Author']==1){//未授权
			$sql.=" AND  stockrequestitems.completed<1";

		}
		if ($_POST['Author']==2){//已授权
			$sql.=" AND  stockrequestitems.completed>=1";

		}

		$sql.="	ORDER BY stockrequest.dispatchid";
	
		$result=DB_query($sql);
	
		$ListCount=DB_num_rows($result);
		if ($ListCount==0) {
			prnMsg(_('There are no transactions for this account in the date range selected'), 'info');	
		}
if (isset($_POST['crtExcel'])||isset($_POST['Search'])||isset($_POST['UpdateAll'])	OR isset($_POST['Go'])	OR isset($_POST['Next'])
OR isset($_POST['Previous'])) {
	$FIRST=1;
	if (!isset($_POST['Go']) AND !isset($_POST['Next']) AND !isset($_POST['Previous'])) {
		// if Search then set to first page
		$_POST['PageOffset'] = 1;
	}	

	if (isset($_POST['UpdateAll'])) {
	
		//prnMsg($AuthorPrice);
		$SelectID=0;
		 $hj=0;
		 $sq=0;
		foreach ($_POST as $POSTVariableName => $POSTValue) {
			
			if (strpos($POSTVariableName, 'TaxCat')) {
				$TaxRate=$TaxArr[$POSTValue];
				$TaxCatID=$POSTValue;
			}
		//	if (strpos($POSTVariableName, 'Remark')) {
		
			//	$Remark=$POSTValue;
		//	}
			
			if (strpos($POSTVariableName, 'edit')) {
		
				$edit=$POSTValue;
			}
			$ID = explode('TaxPrice', $POSTVariableName);
			$msgtext=' ';
			$qtysql=' ';
		    if ($edit==1){
				if (strpos($POSTVariableName, 'Qty')) {
		
					$Qty=$POSTValue;
				}
				if (strpos($POSTVariableName, 'Quantity')) {
		
					$Quantity=$POSTValue;
				}
				if ((float)$Qty!=(float)$Quantity){
					$msgtext= " narrative=concat('". $ID[0].":". $ID[1]."数量".$Qty."^',narrative), ";
					$qtysql="  quantity= '".$Quantity."' , ";
				}


			}
			if (strpos($POSTVariableName, 'Completed')) {
		
				$Completed=$POSTValue;
			}
			if (strpos($POSTVariableName, 'TaxPrice')) {
			
				$TaxPrice=$POSTValue;
				if ($AuthorPrice==2){
					//授权
					//prnMsg($ID[0]. "SelectAuthor".$ID[1]);
					if(	$_POST[$ID[0]. "SelectAuthor".$ID[1]]==4){					
				
						if ($SelectID!=$ID[0]  ){
						
							if($SelectID!=0){
								//更新授权状态
								$SQL="SELECT dispatchid
										FROM stockrequestitems
									WHERE dispatchid='".$SelectID."'
										AND completed>=1";
								$Result=DB_query($SQL);
								if (DB_num_rows($Result)>0) {
									$sql="UPDATE stockrequest
											SET authorised='1',
											".$msgtext."
											version=version+1,
											audituser='".$_SESSION['UserID']."',
											auditdate='".date('Y-m-d h:i:s')."'
										WHERE dispatchid='" .  $SelectID . "'";
								
								}else{
									$sql="UPDATE stockrequest
											SET authorised='0',
											version=0,
											".$msgtext."
											audituser='".$_SESSION['UserID']."',
											auditdate='".date('Y-m-d h:i:s')."'
										WHERE dispatchid='" .  $SelectID . "'";
								}
								$Result=DB_query($sql);
							}
							$SelectID=$ID[0];
						}
					if(in_array(13,	$_SESSION['AllowedPageSecurityTokens'])){
							//授权		
							$select=1;
							if ($TaxPrice>0){
								if ($Completed==-1){
									$select=0;
								}		
								$sql = "UPDATE stockrequestitems
								        LEFT JOIN stockrequest ON stockrequest.dispatchid=stockrequestitems.dispatchid
											SET completed='".$select."',
												taxprice='".filter_number_format($TaxPrice)."',
												".$qtysql."
												cess='".$TaxRate."',
												remark='".$_POST[$ID[0]. "Remark".$ID[1]]."',
												taxcatid='".$TaxCatID."',
												stockrequestitems.auditdate='".date('Y-m-d h:i:s')."'
											WHERE stockrequestitems.dispatchid='" . $ID[0] . "' 
											AND completed < 1 AND qtydelivered=0
											AND (allowprint=0 OR (allowprint=1 AND completed=-1))
											AND dispatchitemsid='" . $ID[1] . "'";
								//prnMsg($Completed);
								$Result = DB_query($sql);
								if ($Result){
									
									if ($select==1){
										$sq++;
										prnMsg($_POST[$ID[0]. "stockname".$ID[1]].'授权更新价格为:'.$TaxPrice.' 税率为:'.$TaxRate,'info');
									}else{
										$hj++;
										prnMsg($_POST[$ID[0]. "stockname".$ID[1]].'划价为:'.$TaxPrice.' 税率为:'.$TaxRate,'info');
									}
								}
							}else{
								prnMsg($_POST[$ID[0]. "stockname".$ID[1]].'授权更新价格为:0,不能授权或划价!','info');
							}
						}
					}					
				}else{
					//划价
				
					if ($_POST[$ID[0]. "SelectAuthor".$ID[1]]==4){
						if ($TaxPrice>0){		
						$sql = "UPDATE stockrequestitems
									SET completed=0,
										taxprice='".filter_number_format($TaxPrice)."',
										cess='".$TaxRate."',
										".$qtysql."
										taxcatid='".$TaxCatID."',
										remark='".$_POST[$ID[0]. "Remark".$ID[1]]."',
										edituser='".$_SESSION['UserID']."',
										pricingdate='".date('Y-m-d h:i:s')."'
									WHERE dispatchid='" . $ID[0] . "'
									AND dispatchitemsid='" . $ID[1] . "'
									AND completed < 1 ";
					
						$Result = DB_query($sql);
					  //prnMsg($sql);
						if ($Result){
							$hj++;
							prnMsg($_POST[$ID[0]. "stockname".$ID[1]].'更新价格为:'.$TaxPrice.' 税率为:'.$TaxRate,'info');
						}
						$sql="UPDATE stockrequest
							SET 
							".$msgtext."
							auditdate='".date('Y-m-d h:i:s')."'
							WHERE dispatchid='" .  $ID[0]  . "'";
							$Result=DB_query($sql);		
						}else{
							prnMsg($_POST[$ID[0]. "stockname".$ID[1]].'更新价格为:0,不能划价!','info');
						}		
					}	
				}	
			 }	//endTaxPrice	
		}//end foreash
		//if ($AuthorPrice==2){
			if ($sq>0){
				$msg=$sq."笔授权成功!";
			}
			if($hj>0){
		       $msg.=$hj."笔划价成功!";
			}
	        if ($msg!=""){
				prnMsg($msg,'info');
			}
		if ($SelectID!==0){
		
			$SQL="SELECT dispatchid
				FROM stockrequestitems
				WHERE dispatchid='".$SelectID."'
					AND completed=2";
		$Result=DB_query($SQL);
		if (DB_num_rows($Result)>0) {
			$sql="UPDATE stockrequest
				SET authorised='1',
					audituser='".$_SESSION['UserID']."',
					auditdate='".date('Y-m-d h:i:s')."'
				WHERE dispatchid='" .  $SelectID  . "'";
			$Result=DB_query($sql);
		}
			$SelectID=0;
		}
		DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
	}	//end UpdateAll
	if ($ListCount>0){	
		if (!isset($_POST['crtExcel'])){
			$ListPageMax = ceil($ListCount / $_SESSION['DisplayRecordsMax']);
			if (isset($_POST['Next'])) {
				if ($_POST['PageOffset'] < $ListPageMax) {
					$_POST['PageOffset'] = $_POST['PageOffset'] + 1;
				}
			}
			if (isset($_POST['Previous'])) {
				if ($_POST['PageOffset'] > 1) {
					$_POST['PageOffset'] = $_POST['PageOffset'] - 1;
				}
			}
			echo '<input type="hidden" name="PageOffset" value="' . $_POST['PageOffset'] . '" />';
			if (isset($ListPageMax) AND  $ListPageMax > 1) {
				echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
				echo '<select name="PageOffset1">';
				$ListPage = 1;
				while ($ListPage <= $ListPageMax) {
					if ($ListPage == $_POST['PageOffset']) {
						echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
					} else {
						echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
					}
					$ListPage++;
				}
				echo '</select>
					<input type="submit" name="Go1" value="' . _('Go') . '" />
					<input type="submit" name="Previous" value="' . _('Previous') . '" />
					<input type="submit" name="Next" value="' . _('Next') . '" />';
				echo '</div>';
			}
			echo '<br />';
			echo '<table class="selection">
				<tr>
					<th>采购单<br>编号</th>
					<th>' . _('Requested Date') . '</th>
					<th>' . _('Department') . '</th>		
					<th width="200px;">' . _('Narrative') . '</th>		
					
					<th>' . _('Product') . '</th>		
					<th>采购<br>数量</th>
					<th>' . _('Units') . '</th>
					<th>入库<br>数量</th>
					<th>税目</th>
					<th>含税<br>标准价格</th>
					<th>采购<br>价格</th>
					<th  width="100px">备注</th>
					<th>操作<br>选择</th>
					<th>状态</th>
				</tr>';
			$disp=0;
			$k=0;
			$RowIndex = 0;
			//$Resultarr=array();
			if($ListCount <> 0) {
					DB_data_seek($result, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);

				while ($myrow=DB_fetch_array($result) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
							
							$SQL = "SELECT materialcost + labourcost + overheadcost as stdcost,mbflag
									FROM stockmaster
									WHERE stockid='" . $myrow['stockid'] . "'";
						$ErrMsg =  _('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The standard cost of the item being received cannot be retrieved because');
						$DbgMsg = _('The following SQL to retrieve the standard cost was used');
						$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);

						$row = DB_fetch_row($Result);
						if ($row[0]==0){
							$Price=0;
							$msg="无标准价格"; 
						}else{
							if($row[1] == 'B') {
								$Price =round($row[0],2);
								$msg="标准价格".$Price;
							}else{
								$Price =round($row[0],2);
								$msg="成本".$Price;
							}
						}
						if ($Price==0){
							$SQL="SELECT newqoh,
										newamount
										FROM  stockmoves
										WHERE  stockid='" . $myrow['stockid'] . "'
										AND stkmoveno IN (SELECT  stkmoveno	FROM   stockmoves 	GROUP BY stockid	HAVING   MAX(stkmoveno))";
							$Result = DB_query($SQL);
							$LocRow = DB_fetch_assoc($Result);
							//存货价格
							if (empty($LocRow['newamount'])){
								$Price = 0;
								$msg.="无采购价格";
							
							}else{
								$Price = round( $LocRow['newamount']/$LocRow['newqoh'],2);
								$msg.="采购价格".$AvPrice;
						
							} 
					}

					if ($disp!=$myrow['dispatchid'] ){
						$disp=$myrow['dispatchid'];
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						}else{
							echo '<tr class="OddTableRows">';
							$k=1;
						}
						$cls="正常";

						echo '<td><a href="' . $RootPath . '/PDFPurchPlanOrder.php?F='.$AuthorPrice.'&D=' . $myrow['dispatchid'] . '" target="_blank"  title="点击打印">'.($myrow['allowprint']==0?"[":"已打印[").$myrow['dispatchid'].']</a></td>';
					
						echo'<td>' . ConvertSQLDate($myrow['despatchdate']) . '</td>
								<td>' . $myrow['departname'] . '</td>				
								<td class="lengthcss"   title="'. $myrow['narrative'].'">' . $myrow['narrative'] . '</td>';
					}else{
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						}else{
							echo '<tr class="OddTableRows">';
							$k=1;
						}
						echo '<td colspan="4"></td>';
					}
					if ($myrow['taxcatid']>0){
					
						$taxcatid=$myrow['taxcatid'];
					}else{
						$taxcatid=1;
					
					}
					$disabled=' '; 
					if($myrow['completed']>=1){
					
						$disabled="readonly" ; 
					}
					echo'<td title="' . $myrow['locationname'] . '">[' .$myrow['stockid']."]".  $myrow['description'] . '
								<input type="hidden" name="' . $myrow['dispatchid'] . 'stockname' . $myrow['dispatchitemsid'] . '" value="'.$myrow['stockid']." ".  $myrow['description'].'"  />
								<input type="hidden" name="' . $myrow['dispatchid'] . 'Completed' . $myrow['dispatchitemsid'] . '"  value="'.$myrow['completed'].'"  />
						    	<input type="hidden" id="'.$myrow['dispatchid'] . 'edit' . $myrow['dispatchitemsid']  . '" name="'.$myrow['dispatchid'] . 'edit' . $myrow['dispatchitemsid']  . '" value="0">
						
						        <input type="hidden" name="'.$myrow['dispatchid'] . 'Qty' . $myrow['dispatchitemsid'] .'" size="5" title=" "   value="'. round($myrow['quantity'],$myrow['decimalplaces']) .'"  /></td>
						<td >	
							
						        <input type="text" class="number" name="'.$myrow['dispatchid'] . 'Quantity' . $myrow['dispatchitemsid'] .'" size="5"    value="'. locale_number_format($myrow['quantity'],$myrow['decimalplaces']) .'"  onChange="inQTY(this,'.$myrow['decimalplaces'] . ' )"  '.$disabled.'  />' . '
							</td>
						<td>' . $myrow['uom'] .'</td>
						<td>' . $myrow['qtydelivered'] . '</td>';
					echo'<td>
							<select name="' . $myrow['dispatchid'] . 'TaxCat' . $myrow['dispatchitemsid']  .'"  id="' . $myrow['dispatchid'] . 'TaxCat' . $myrow['dispatchitemsid']  .'" onChange="inTaxCat(this,'.$myrow['dispatchid'] . ',' . $myrow['dispatchitemsid'].' )" >';
							DB_data_seek($TaxResult,0);
							while($row=DB_fetch_array($TaxResult)){
								if ($row['taxcatid']==$taxcatid ) {
									echo '<option selected="selected" value="' .$row['taxcatid'] . '">' . $row['taxcatname'] . '</option>';
								} else {
									echo '<option value="' . $row['taxcatid'] . '">' . $row['taxcatname'] . '</option>';
								}
								
							}
							echo '</select></td>';
							$show='';
							if ($myrow['taxprice']==0){
								$TaxPrice=$Price*(1+$TaxArr[$myrow['taxid']]);
							}else{
								$show="划价";
								$TaxPrice=$myrow['taxprice'];
							}
					
					echo'<td ><input   class="number"  type="text" name="'.$myrow['dispatchid'] . 'Price' . $myrow['dispatchitemsid'] .'" size="5" title="'.$msg.'" value="'. locale_number_format($Price*(1+$TaxArr[$myrow['taxid']]),2).'"  readonly="readonly" /></td>
						 <td ><input class="number" type="text" name="'.$myrow['dispatchid'] . 'TaxPrice' . $myrow['dispatchitemsid'] .'" size="5" value="'. locale_number_format($TaxPrice,2).'"  onChange="inRemark(this,'.$myrow['dispatchid'] . ',' . $myrow['dispatchitemsid'].' )" /></td>
						 <td ><input type="text" name="'.$myrow['dispatchid'] . 'Remark' . $myrow['dispatchitemsid'] .'" id="'.$myrow['dispatchid'] . 'Remark' . $myrow['dispatchitemsid'] .'" size="20" title="'.$myrow['remark'].'" value="'.  $myrow['remark'].'" onChange="inRemark(this,'.$myrow['dispatchid'] . ',' . $myrow['dispatchitemsid'].' )" /></td>';
						
							if($myrow['completed']==2){
								$show="完成";
								$flag=true;
								//$disabled='  readonly="readonly" ' ; 
							}elseif($myrow['completed']==1){
								$show="采购中";
								$flag=true;
								$ztmsg="审批日期:".$myrow['auditdate'];
								//$disabled='  readonly="readonly" ' ; 
							}elseif($myrow['completed']==0){
								$ztmsg="划价日期:".$myrow['pricingdate'];
								$show="划价未批";
								$flag=false;
							}elseif($myrow['completed']==-1){
								$show="未划价";
								$flag=false;
							}else{
								if ($show==''){
									$show="等待";
									$flag=false;
								}
							}
					if ($flag||$myrow['completed']==1||$myrow['closed']==1||($myrow['completed']==2&&$myrow['qtydelivered']>0)||($myrow['completed']==2&&$myrow['allowprint']==1)){
						echo '<td>
							<input type="checkbox" name="' . $myrow['dispatchid'] . 'SelectAuthor' . $myrow['dispatchitemsid'] . '"  id="' . $myrow['dispatchid'] . 'SelectAuthor' . $myrow['dispatchitemsid'] . '" value="2" disabled="disabled"   onChange="inSelectAuthor(this,'.$myrow['dispatchid'] . ',' . $myrow['dispatchitemsid'].' )" />
							<input type="hidden" name="' . $myrow['dispatchid'] . 'Edit' . $myrow['dispatchitemsid'] . '" id="' . $myrow['dispatchid'] . 'Edit' . $myrow['dispatchitemsid'] . '" value="2"  /></td>';
					}else{
						echo '<td>
							<input type="checkbox" name="' . $myrow['dispatchid'] . 'SelectAuthor' . $myrow['dispatchitemsid'] . '"  id="' . $myrow['dispatchid'] . 'SelectAuthor' . $myrow['dispatchitemsid'] . '" value="1" '.($flag?"checked":"").'  onChange="inSelectAuthor(this,'.$myrow['dispatchid'] . ',' . $myrow['dispatchitemsid'].' )" />
							<input type="hidden" name="' . $myrow['dispatchid'] . 'Edit' . $myrow['dispatchitemsid'] . '" id="' . $myrow['dispatchid'] . 'Edit' . $myrow['dispatchitemsid'] . '" value="1"  /></td>';
				
					}		
						echo '<td  title="'.$ztmsg.'" >
								'.$show.'</td>';		
						echo 	'</tr>';
						$RowIndex = $RowIndex + 1;
				}//while
				echo '</table>';
			}//分页判断
			if (isset($ListPageMax) AND  $ListPageMax > 1) {
				echo '<div class="centre"><br />&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ':';
				echo '<select name="PageOffset2">';
					$ListPage = 1;
					while ($ListPage <= $ListPageMax) {
						if ($ListPage == $_POST['PageOffset']) {
							echo '<option value="' . $ListPage . '" selected="selected">' . $ListPage . '</option>';
						} else {
							echo '<option value="' . $ListPage . '">' . $ListPage . '</option>';
						}
						$ListPage++;
					}
					echo '</select>
						<input type="submit" name="Go2" value="' . _('Go') . '" />
						<input type="submit" name="Previous" value="' . _('Previous') . '" />
						<input type="submit" name="Next" value="' . _('Next') . '" />';
					echo '</div>';
			}
		}else{
				//prnMsg('Excel');
				require_once 'Classes/PHPExcel.php'; 
			
				if (DB_num_rows($result) != 0){
					// Create new PHPExcel object
					$objPHPExcel = new PHPExcel();
					// Set document properties
					$objPHPExcel->getProperties()->setCreator("采购申请单")
												->setLastModifiedBy("webERP")
												->setTitle("Petty Cash Expenses Analysis")
												->setSubject("Petty Cash Expenses Analysis")
												->setDescription("Petty Cash Expenses Analysis")
												->setKeywords("")
												->setCategory("");
					$objPHPExcel->getActiveSheet()->mergeCells('A1:L1');
					$objPHPExcel->getActiveSheet()->setCellValue('A1', '采购申请单');
					$objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
					$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12); //设置列宽
					//$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true); 
					$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30); 
					$objPHPExcel->getActiveSheet()->mergeCells('A2:C2');
					$objPHPExcel->getActiveSheet()->setCellValue('A2', '采购单位:'.$_SESSION['CompanyRecord']['coyname']);
				
					$styleThinBlackBorderOutline = array('borders' => array ('outline' => array (
						//'style' => PHPExcel_Style_Border::BORDER_THIN,   //设置border样式
						'style' => PHPExcel_Style_Border::BORDER_THICK, // 另一种样式
								'color' => array ('argb' => 'FF000000'),          //设置border颜色
						), ),);  
						
						$objPHPExcel->getActiveSheet()->getStyle( 'A3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'B3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'C3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'D3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'E3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'F3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'G3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'H3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'I3')->applyFromArray($styleThinBlackBorderOutline); $objPHPExcel->getActiveSheet()->getStyle( 'A3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'J3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'K3')->applyFromArray($styleThinBlackBorderOutline);
						$objPHPExcel->getActiveSheet()->getStyle( 'L3')->applyFromArray($styleThinBlackBorderOutline);
					// $styleBorderR= array( 'borders' => array ('right'=> array ('style' => PHPExcel_Style_Border::BORDER_THIN ),),);  
					$objPHPExcel->getActiveSheet()->getStyle('1')->getAlignment()->setWrapText(true);
					
					$objPHPExcel->getActiveSheet()->getStyle('C:L')->getNumberFormat()->setFormatCode('#,###');
				
					$objPHPExcel->getActiveSheet()->setCellValue('A3', '采购单编号');
					$objPHPExcel->getActiveSheet()->setCellValue('B3',  '发运日期');
					$objPHPExcel->getActiveSheet()->setCellValue('C3', '采购部门');
					$objPHPExcel->getActiveSheet()->setCellValue('D3','摘要');
					$objPHPExcel->getActiveSheet()->setCellValue('E3', '仓库');
					$objPHPExcel->getActiveSheet()->setCellValue('F3', '物料编码');
					$objPHPExcel->getActiveSheet()->setCellValue('G3', '物料名称');
					$objPHPExcel->getActiveSheet()->setCellValue('H3', '备注');
					$objPHPExcel->getActiveSheet()->setCellValue('I3', '数量');
					$objPHPExcel->getActiveSheet()->setCellValue('J3' ,'单位');
					$objPHPExcel->getActiveSheet()->setCellValue('K3', '授权');
					$objPHPExcel->getActiveSheet()->setCellValue('L3' ,'进度');
					
					// Add data
					if (isset($_POST['ShowDetails']) ) {
						$ID = '';//mark the ID change of the internal request 
					}
					$i = 4;
					while ($myrow = DB_fetch_array($result)) {
						$authorised="是";
						if($myrow['authorised']==0){
							$authorised="否";
						}
						$completed="完成";
						if($myrow['closed']==1){
							$completed="关闭";
						}else{
							if($myrow['completed']==0){
								$completed="否";
							}
						}
						
						
							$objPHPExcel->setActiveSheetIndex(0);
							$objPHPExcel->getActiveSheet()->getStyle( 'A'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'B'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'C'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'D'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'E'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'F'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'G'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'H'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'I'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'J'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'K'.$i)->applyFromArray($styleThinBlackBorderOutline);
							$objPHPExcel->getActiveSheet()->getStyle( 'L'.$i)->applyFromArray($styleThinBlackBorderOutline);
						if (isset($ID) AND ($ID != $myrow['dispatchid'])) {
								$ID = $myrow['dispatchid'];
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$i,  $myrow['dispatchid']);
							$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$myrow['despatchdate']); 
						
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$myrow['departname']); 
								//设置A3单元格为文本
								$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
								$objPHPExcel->getActiveSheet()->getStyle('D'.$i)->getAlignment()->setWrapText(true); //设置换行
		
							$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $myrow['narrative']);
							$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['locationname']);
							$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['stockid']);
							$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['description']);
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $myrow['remark']);
							$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $myrow['quantity']);
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $myrow['uom']);
							$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $authorised);
							$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $completed);	
						}elseif (isset($ID) AND ($ID == $myrow['dispatchid'])) {
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, '');
							$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, '');
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, '');
							$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, '');
							$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['locationname']);
							$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['stockid']);
							$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['description']);
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $myrow['remark']);
							$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $myrow['quantity']);
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $myrow['uom']);
							$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $authorised);
							$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $completed);	

						}elseif(!isset($ID)) {
							$objPHPExcel->getActiveSheet()->setCellValue('A'.$i,  $myrow['dispatchid']);
							$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$myrow['despatchdate']); 
						
							$objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$myrow['departname']); 
							$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $myrow['narrative']);
							$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['locationname']);
							$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['stockid']);
							$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['description']);
							$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $myrow['remark']);
							$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $myrow['quantity']);
							$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $myrow['uom']);
							$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $authorised);
							$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $completed);	
						}			   
							$i++;
						
					}				
					// Freeze panes // $objPHPExcel->getActiveSheet()->freezePane('E2');
					// Auto Size columns
					foreach(range('A','K') as $columnID) {
						$objPHPExcel->getActiveSheet()->getColumnDimension($columnID)
							->setAutoSize(true);
					}				
					// Rename worksheet			   
					//$objPHPExcel->getActiveSheet()->setTitle('All Accounts');			   
					// Set active sheet index to the first sheet, so Excel opens this as the first sheet
					$objPHPExcel->setActiveSheetIndex(0);
					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel,'Excel2007');
					$objWriter->setIncludeCharts(TRUE);
					$RootPath=dirname(__FILE__ ) ;
					$outputFileName ='companies/'.$_SESSION['DatabaseName'].'/Stock/采购申请单_'.date("Ymd").'.xlsx';
					//echo $RootPath.'/'.$outputFileName;
					$objWriter->save($RootPath.'/'.$outputFileName);
					echo '<p><a href="'. $outputFileName. '">' . _('click here') . '</a> 下载采购申请单<br />';
				}else{
					$Title = _('Excel file for petty Cash Expenses Analysis');
					include('includes/header.php');
					prnMsg('No data to analyse');
					include('includes/footer.php');
					exit;
				}	
		}//endphpexcel
	}
}


echo '</div>
      </form>';
include('includes/footer.php');
?>