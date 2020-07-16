
<?php
/* $Id: InternalStockRequest.php $*/
/*
 * @Author: ChengJiang 
 * @Date: 2018-10-30 21:45:45 
 * @Last Modified by: mikey.zhaopeng
 * @Last Modified time: 2019-08-15 06:08:46
 */
include('includes/DefineStockRequestClassCN.php');
include('includes/session.php');
$Title = '采购申请单';//_('Create an Internal Materials Request');
$ViewTopic = 'Inventory';
$BookMark = 'CreateRequest';
include('includes/header.php');
include('includes/SQL_CommonFunctions.inc');
echo'<script type="text/javascript">
	function ChangAction(){
		var url=document.getElementById("form1").action;		
		var p="New";  
		var urlen=url.indexOf(p);	
			if (urlen==-1){
				document.getElementById("form1").action=url+"?New=Yes";	
				//window.location.reload; 
				window.location.replace(location.href);
			}		
	}
	function SearchAction(){	
		var url=document.getElementById("form1").action;	
		var paramName="?";
		var urlen=url.indexOf(paramName);	
		if (urlen>-1){	
			urlen=Number(urlen).toFixed(0);
			url=url.slice(0,urlen);
			document.getElementById("form1").action=url;
			window.location.reload;
		}
		//alert("SearchAction");
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
if (isset($_POST['Clear'])){

   //	prnMsg('//清除缓存返回查询');
	unset($_GET);
	unset($_SESSION['Transfer']);
	unset($_SESSION['Request']);
	unset($_SESSION['action']);
	//echo "<script language=JavaScript> location.replace(location.href);</script>";
	echo '<meta http-equiv="refresh" content="0;url=InternalStockRequest.php"> ';
	//header('Location:InternalStockRequest.php');
	
}
if (isset($_GET)){
	if ($_SERVER["QUERY_STRING"]!=''){
		$_SESSION['action']='?'.$_SERVER["QUERY_STRING"];
	}
}
$all="全部";
if (!isset($_SESSION['Transfer'])){
	//prnMsg('73$_SESSION[Transfer不存在');
	if (((isset($_GET['New'])	AND $_GET['New'] == 'Yes'	) 
            OR(isset($_GET['Modify'])AND $_GET['Modify'] == 'Yes' ))&&!isset($_SESSION['Request'])){//||isset($_POST['SearchRequest'])){
		$_SESSION['Request'] = new StockRequest();
	//if (isset($_GET['New'])||isset($_GET['Modify'])&&!isset($_SESSION['Request'])||isset($_POST['SearchRequest'])) {
		if (isset($_GET['New'])	AND $_GET['New'] == 'Yes'	) {
			$_SESSION['Transfer']=array(1,0,0);
		}elseif(isset($_GET['Modify'])AND $_GET['Modify'] == 'Yes' ){
			$modify=explode('^',url_decode($_GET['ReqNO']));
			$_SESSION['Request']->DispatchID=$modify[0];
			$_SESSION['Transfer']=array(2,$modify[0],$modify[1]);
		}
		//unset();
		$all="";
		//prnMsg('Request'.$q++);	
	}
}

if (!isset($_POST['SearchType'])||count($_POST['SearchType'])==0){
	$_POST['SearchType']=array(0=>"0",1=>"1",2=>"2");
}

if(isset($_GET['Modify']) && $_SESSION['Transfer'][0]==2 && isset($_GET['ReqNO'])&&count($_SESSION['Request']->LineItems )==0) {
	//$modify=explode('^',url_decode($_GET['ReqNO']));
	$sql="SELECT	dispatchitemsid,
					a.dispatchid,
					b.closed,
					narrative,
					a.stockid,
					loccode,
					departmentid,
					despatchdate,
					narrative,
					description,
					quantity,
					qtydelivered,
					a.decimalplaces,
					a.taxcatid,
					a.cess,
					a.taxprice,
					uom,
					remark,
					completed,
					edituser,
					authorised,
					closed,
					initiator, 
					audituser
				FROM	stockrequestitems a	
				LEFT JOIN stockrequest b ON	a.dispatchid = b.dispatchid
				LEFT JOIN stockmaster c ON	a.stockid = c.stockid
					WHERE a.dispatchid=".$_SESSION['Transfer'][1];
		$result=DB_query($sql);
		while($row=DB_fetch_array($result)){
			if (!isset($_POST['DespatchDate'])) {
				//$_SESSION['Transfer'][0]=2;//编辑
				$_SESSION['Request']->DispatchID=$row['dispatchid'];
				$_SESSION['Request']->Department=$row['departmentid'];
				$_SESSION['Request']->Location=$row['loccode'];
				$_SESSION['Request']->DispatchDate=$row['despatchdate'];
				$_SESSION['Request']->Narrative=$row['narrative'];
				$_POST['Department']=$row['departmentid'];
				$_POST['DespatchDate']=$row['despatchdate'];
				$_POST['Narrative']=$row['narrative'];

			}
			$_SESSION['Request']->AddLine($row['stockid'],
											$row['description'],
											$row['loccode'],
											$row['quantity'],
											$row['uom'],
											$row['decimalPlaces'],
											$row['remark']) ;

		}
		
	//	var_dump($_SESSION['Request']->LineItems );
}

if (!isset($_POST['ShowDetails'])){
	$_POST['ShowDetails']=1;
}
if (isset($_GET['Modify'])&&$_GET['Modify']!='Yes'&&!isset($_POST['Submit'])) {
	unset($_SESSION['Request']->LineItems[$_GET['Modify']]);
	echo '<br />';
	prnMsg( _('The line was successfully deleted'), 'success');
	echo '<br />';
}
if (isset($_POST['order_items'])){
	//prnMsg('order_items');

	foreach ($_POST as $key => $value) {

		if (mb_strstr($key,'StockID')) {
			$Index=mb_substr($key, 7);
			
			//if (filter_number_format(
				$DecimalPlaces=$_POST['DecimalPlaces'.$Index];
			if(round((float)$_POST['Quantity'.$Index],$DecimalPlaces)!=0) {
				$StockID=$value;
				$ItemDescription=$_POST['ItemDescription'.$Index];
			
				$Remark='';//$_POST['Remark'.$StockID];
				$Loccode=$_POST['Loccode'.$Index];
				$NewItem_array[$StockID] =round((float)$_POST['Quantity'.$Index],$DecimalPlaces);// filter_number_format($_POST['Quantity'.$Index]);
				$_POST['Units'.$StockID]=$_POST['Units'.$Index];
				//prnMsg(	'Stockid'.$StockID.'<br>descript'.$ItemDescription.'<br>loccode'.$Loccode.
				//'<br>Quantity'. $NewItem_array[$StockID]. '<br>=unit'.$_POST['Units'.$StockID].'<br>'. $DecimalPlaces.'<'
				//prnMsg($_POST['Remark'.$StockID].'Remark'.$StockID);
				$_SESSION['Request']->AddLine($StockID,
											$ItemDescription,
											$Loccode,
											$NewItem_array[$StockID],
											$_POST['Units'.$StockID],
											$DecimalPlaces,
											$Remark);
			}//endif
		}
	}
	//echo count($_SESSION['Request']->LineItems).'<br>';
	//var_dump($_SESSION['Request']->LineItems );
}

echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/supplier.png" title="' . _('Dispatch') .
		'" alt="" />' . ' ' . $Title . '</p>';

if (!isset($_POST['DespatchDate'])){
	$_POST['DespatchDate'] =date('Y-m-d');
}
echo '<form  id="form1" name="form1" action="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .$_SESSION['action'].'" method="post">';

echo '<div>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	  <input type="hidden"  name="DispatchDate" value=' . $_POST['DispatchDate'] . ' />
	  <input type="hidden"  name="SearchNo" value=' . $_POST['SearchNo'] . ' />
	  <input type="hidden"  name="SearchType" value=' . $_POST['SearchType'] . ' />';

echo '<table class="selection">';
echo '<tr><th colspan="2"><h4>';
if (isset($_SESSION['Transfer'][0])&&$_SESSION['Transfer'][0]==2){
		echo $_SESSION['Request']->DispatchID.'-采购申请单维护';
}else{
	echo'采购申请明细';
}
echo'</h4></th></tr>
	<tr>
		<td>' . _('Department') . ':</td>';
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
echo '<td><select name="Department">';
if ($_SESSION['action']==''){
	echo '<option selected="True" value="0">全部</option>';
}

while ($myrow=DB_fetch_array($result)){
	if (isset($_POST['Department']) AND $_POST['Department']==$myrow['departmentid']){
		echo '<option selected="True" value="' . $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
	} else {
		echo '<option value="' . $myrow['departmentid'] . '">' . htmlspecialchars($myrow['description'], ENT_QUOTES,'UTF-8') . '</option>';
	}
}
echo '</select></td>
	</tr>';
echo'<tr>
		<td>' . _('Date when required') . ':</td>';
echo '<td><input type="date"  name="DespatchDate" maxlength="10" size="11" value="' . $_POST['DespatchDate'] . '" /></td>
      </tr>';

echo '<tr>
		<td>' . _('Narrative') . ':</td>
		<td><textarea title="输入70字符数以内" name="Narrative" cols="30" rows="3"    maxlength="70"> ' . str_replace("^","",strrchr("^",$_POST['Narrative'])). ' </textarea></td>
	</tr>';
//if (isset($_POST['SearchRequest'])
if ($_SESSION['action']==''){
	echo' <tr  style="background:#0CA3DC;height:1px;width:100%">
			  <td colspan="2" height="1px;"></td>
		</tr>';

	echo'<tr>
			<td>' . _('Location from which to request stock') . ':</td>';
	
	$sql="SELECT locations.loccode,
				 locationname
			FROM locations
			INNER JOIN locationusers ON locationusers.loccode=locations.loccode AND locationusers.userid='" .  $_SESSION['UserID'] . "' AND locationusers.canupd=1
			WHERE internalrequest = 1
			ORDER BY locationname";
	
	$result=DB_query($sql);
	
	echo '<td><select name="StockLocation">';
		//	<option value="">' . _('Select a Location') . '</option>';
		if (!isset($_SESSION['Transfer'][0])&&(!isset($_GET['New']))||(!isset($_GET['ReqNO']))){
			echo '<option selected="True" value="0">全部</option>';
		}
	while ($myrow=DB_fetch_array($result)){
		if (isset($_SESSION['Request']->Location) AND $_SESSION['Request']->Location==$myrow['loccode']){
			echo '<option selected="True" value="' . $myrow['loccode'] . '">' . $myrow['loccode'].' - ' .htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		} else {
			echo '<option value="' . $myrow['loccode'] . '">' . $myrow['loccode'].' - ' .htmlspecialchars($myrow['locationname'], ENT_QUOTES,'UTF-8') . '</option>';
		}
	}
	echo '</select></td>
		</tr>';  
	echo'<tr>
	        <td>查询类别</td>
			<td>';
	$SearchType=array(0=>'待收货',1=>'处理中',2=>'完成');		
	 foreach ($SearchType as $key=>$val){
		if (in_array($key,$_POST['SearchType'])){
            $CHK="checked";
		}else{
			$CHK="";
		}
	    echo'<input type="checkbox" name="SearchType[]" value="'.$key.'" '. $CHK.' />'. $val;     
		
	}
		echo'</td>
		</tr>
		<tr><td>查询方式</td>
		<td><input type="radio" name="ShowDetails" value="0" '. ($_POST['ShowDetails']==0 ?"checked":"").' />汇总       
			<input type="radio" name="ShowDetails" value="1" '. ($_POST['ShowDetails']==1 ?"checked":"").' />明细
		</td>
		</tr>
		<tr>
		<td>查询单号</td>
		<td><input type="text" name="SearchNo"  maxlength="20" size="20"  value="'. $_POST['SearchNo'].'"  pattern="(\d{1,4}-\d{1,4})?|(\d{1,4},)|[1-9]{1,4}$)"  placeholder="输入如:223 或118,105,158"  />       
			</td>
		</tr>
		<tr>
		<td>查询编码</td>
		<td><input type="text" name="SearchCode"  maxlength="20" size="20"  value="'. $_POST['SearchCode'].'"   pattern="(\d{1,4}-\d{1,4})?|(\d{1,4},)|[1-9]{1,4}$)"  placeholder="输入如:10244 或20154,301457,401250"   />       
				</td>
		</tr>
		<tr>
		<td>查询名称</td>
		<td><input type="text" name="SearchName"  maxlength="25" size="25"  value="'. $_POST['SearchName'].'"  pattern="^[\u4e00-\u9fa5a-zA-Z0-9\-\+\=\*\~\]\[\(\)\\.]+$" placeholder="输入汉字、数字、字母！"  />       
			</td>
		</tr>';
}
echo'</table>
	<br />';

echo '<div class="centre">';
if (!isset($_GET['New'])&&!isset($_GET['Modify'])){
	echo'<input type="submit" name="SearchRequest" value="申请单查找"   onclick="SearchAction();"/>';
}
if (isset($_POST['SearchRequest'])){
		echo'<input type="submit" name="crtExcel" value="导出Excel" />';
}
echo'<input type="submit" name="AddRequest" value="新增" onclick="ChangAction();" />
	 <input type="submit" name="Clear" value="返回查询" />
	</div>
	';

if (isset($_POST['Submit'])) {//提交
		$InputError=0;
		if ($_POST['Department']=='') {
			prnMsg( _('You must select a Department for the request'), 'error');
			$InputError=1;
		}
		//prnMsg($InputError.'==0 &&'. $_SESSION['Transfer'][0]);	
		if ($InputError==0 &&( $_SESSION['Transfer'][0]==2|| $_SESSION['Transfer'][0]==1)) {

			foreach ($_POST as $key => $value) {		
				if (mb_strstr($key,'Remark')) {
					$Index=mb_substr($key, 6);
					$_SESSION['Request']->update_item($Index,
											$_POST['Remark'.$Index]	);
				
					//prnMsg($_POST['Remark'.$Index]);
				}
			}
			
		}		
		if ($InputError==0 && $_SESSION['Transfer'][0]>=1) {
			$_SESSION['Request']->Department=$_POST['Department'];
			//$_SESSION['Request']->Location=$_POST['Location'];
			$_SESSION['Request']->DispatchDate=$_POST['DispatchDate'];
			$_SESSION['Request']->Narrative=$_POST['Narrative'];			
			
		
		}
		if (count($_SESSION['Request']->LineItems )==0 && $_SESSION['Transfer'][0]==1){
			  prnMsg('你没有输入明细行，不能提交！','warn');
		}else{
			
			
		DB_Txn_Begin();
		$InputError=0;
		if ($_SESSION['Request']->Department=='') {
			prnMsg( _('You must select a Department for the request'), 'error');
			$InputError=1;
		}
		
		//prnMsg($_SESSION['Transfer'][0]);
		if ($InputError==0) {
			if (isset($_SESSION['Transfer'][0])&& $_SESSION['Transfer'][0]==1){//新增
				$RequestNo = GetNextTransNo(38, $db);
			}else{//修改
			//	if (count($_SESSION['Request']->LineItems )==0 && $_SESSION['Transfer'][0]==2){
				//prnMsg( $_SESSION['Request']->DispatchID);
				//删除内容保留申请单号
				$RequestNo = $_SESSION['Request']->DispatchID;
				$SQL="DELETE FROM  stockrequest WHERE dispatchid='" .$RequestNo."'";
				$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
	
				$SQL="DELETE FROM stockrequestitems WHERE dispatchid='" .$RequestNo."'";
				$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request line record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($SQL,$ErrMsg,$DbgMsg,true);
			//	}
				
	
			}
			//prnMsg( $_SESSION['Request']->DispatchID.'=='.count($_SESSION['Request']->LineItems));
			if (count($_SESSION['Request']->LineItems )>0){
			//新增和修改执行如下	
				$HeaderSQL="INSERT INTO stockrequest (dispatchid,
													loccode,
													departmentid,
													initiator,
													despatchdate,
													
													narrative)
												VALUES(
													'" . $RequestNo . "',
													'" . $_SESSION['Request']->Location . "',
													'" . $_SESSION['Request']->Department . "',
													'" . $_SESSION['UserID'] . "',
													'" . FormatDateForSQL(date("Y-m-d")) . "',
												
													'" . $_SESSION['Request']->Narrative . "')";
													//deliverydate,	'" . FormatDateForSQL($_SESSION['Request']->DispatchDate) . "',
				$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request header record could not be inserted because');
				$DbgMsg = _('The following SQL to insert the request header record was used');
				$Result = DB_query($HeaderSQL,$ErrMsg,$DbgMsg,true);
	
				foreach ($_SESSION['Request']->LineItems as $LineItems) {
					$LineSQL="INSERT INTO stockrequestitems (dispatchitemsid,
															dispatchid,
															stockid,
															quantity,
															decimalplaces,
															uom,
															remark,															
															cess,
															taxprice,
															edituser,
															completed)
														VALUES(
															'".$LineItems->LineNumber."',
															'".$RequestNo."',
															'".$LineItems->StockID."',
															'".$LineItems->Quantity."',
															'".$LineItems->DecimalPlaces."',
															'".$LineItems->UOM."',
															'".$LineItems->Remark."',
															0,
															0,
															'',
															-1)";
					$ErrMsg =_('CRITICAL ERROR') . '! ' . _('NOTE DOWN THIS ERROR AND SEEK ASSISTANCE') . ': ' . _('The request line record could not be inserted because');
					$DbgMsg = _('The following SQL to insert the request header record was used');
				   // prnMsg($LineSQL);
					$Result = DB_query($LineSQL,$ErrMsg,$DbgMsg,true);
				}
		
	

		}
	
		}  //end error
			DB_Txn_Commit();
			if ($_SESSION['Transfer'][0]==1){
				//以下发送邮件代码未启用
				$EmailSQL="SELECT email
							FROM www_users, departments
							WHERE departments.authoriser = www_users.userid
								AND departments.departmentid = '" . $_SESSION['Request']->Department ."'";
				$EmailResult = DB_query($EmailSQL);
				//prnMsg($EmailSQL );
				if ($myEmail=DB_fetch_array($EmailResult)){
					$ConfirmationText = _('An internal stock request has been created and is waiting for your authoritation');
					$EmailSubject = _('Internal Stock Request needs your authoritation');
					if($_SESSION['SmtpSetting']==0){
						mail($myEmail['email'],$EmailSubject,$ConfirmationText);
					}else{
						include('includes/htmlMimeMail.php');
						$mail = new htmlMimeMail();
						$mail->setSubject($EmailSubject);
						$mail->setText($ConfirmationText);
						$result = SendmailBySmtp($mail,array($myEmail['email']));
					}

				}
				prnMsg($RequestNo.' 采购申请单已经建立现在需要审批！', 'success');
			}else{
				if (count($_SESSION['Request']->LineItems )>0){
					prnMsg($RequestNo.' 采购申请单修改完成！', 'success');
				}else{
					prnMsg($RequestNo.' 采购申请单删除完成！', 'success');
				}
			}
			unset($_GET);
			unset($_SESSION['Transfer']);
			unset($_SESSION['Request']);
			unset($_SESSION['action']);
			echo '<br /><div class="centre"><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?New=Yes">' . _('Create another request') . '</a></div>';
			include('includes/footer.php');
			unset($_SESSION['Request']);
			exit;
			
		}
}//end submit
	
	$i = 0; //Line Item Array pointer

	echo '<div>';

if ((isset($_GET['New'])||isset($_GET['ReqNO'])||isset($_POST['Search'])||isset($_SESSION['Transfer'])||isset($_POST['Select']))&&!isset($_POST['SearchRequest'])){

	/* display list if there is more than one record */
	//if(isset($_GET['New'])||isset($_POST['SelectPurchaseNo'])||isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev'])){

	if(isset($_POST['SelectPurchaseNo'])){
		$_SESSION['SelectPurchaseNo'] = $_POST['SelectPurchaseNo'];
		$SelectPurchaseNo = $_POST['SelectPurchaseNo'];
		unset($_POST['SelectPurchaseNo']);
	} else {
		$SelectPurchaseNo = $_SESSION['SelectPurchaseNo'];
	}
  
	if (isset($_POST['Search']) or isset($_POST['Next']) or isset($_POST['Prev'])){
	//	prnMsg('695');
		if ($_POST['Keywords']!='' AND $_POST['StockCode']=='') {
			prnMsg ( _('Order Item description has been used in search'), 'warn' );
		} elseif ($_POST['StockCode']!='' AND $_POST['Keywords']=='') {
			prnMsg ( _('Stock Code has been used in search'), 'warn' );
		}
		/* elseif ($_POST['Keywords']=='' AND $_POST['StockCode']=='') {
			prnMsg ( _('Stock Category has been used in search'), 'warn' );
		}*/

		if (isset($_POST['Keywords']) AND mb_strlen($_POST['Keywords'])>0) {
			//insert wildcard characters in spaces
			$_POST['Keywords'] = mb_strtoupper($_POST['Keywords']);
			$SearchString = '%' . str_replace(' ', '%', $_POST['Keywords']) . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								locations.locationname,
								stockmaster.categoryid  loccode,
								stockmaster.units as stockunits,
								stockmaster.decimalplaces
						FROM stockmaster,
							stockcategory,
							internalstockcatrole,
                            locations
						WHERE stockmaster.categoryid=stockcategory.categoryid
							AND stockcategory.categoryid = internalstockcatrole.categoryid
                            AND  stockmaster.categoryid=locations.loccode
							AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
							AND stockmaster.mbflag <>'G'
							AND stockmaster.description " . LIKE . " '" . $SearchString . "'
							AND stockmaster.discontinued=0 ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid loccode,
								locations.locationname,
								stockmaster.units as stockunits,
								stockmaster.decimalplaces
						FROM stockmaster,
							stockcategory,
							internalstockcatrole,
                            locations
						WHERE stockmaster.categoryid=stockcategory.categoryid
							AND stockcategory.categoryid = internalstockcatrole.categoryid
                            AND  stockmaster.categoryid=locations.loccode
							AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
							AND stockmaster.mbflag <>'G'
							AND stockmaster.discontinued=0
							AND stockmaster.description " . LIKE . " '" . $SearchString . "'
							AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
			}

		} elseif (mb_strlen($_POST['StockCode'])>0){

			$_POST['StockCode'] = mb_strtoupper($_POST['StockCode']);
			$SearchString = '%' . $_POST['StockCode'] . '%';

			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid loccode,
								locations.locationname,
								stockmaster.units as stockunits,
								stockmaster.decimalplaces
						FROM stockmaster,
							stockcategory,
							internalstockcatrole,
                            locations
						WHERE stockmaster.categoryid=stockcategory.categoryid
							AND stockcategory.categoryid = internalstockcatrole.categoryid
                            AND  stockmaster.categoryid=locations.loccode
							AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
							AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
							AND stockmaster.mbflag <>'G'
							AND stockmaster.discontinued=0 ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid loccode,
								locations.locationname,
								stockmaster.units as stockunits,
								stockmaster.decimalplaces
						FROM stockmaster,
							stockcategory,
							internalstockcatrole,
                            locations
						WHERE stockmaster.categoryid=stockcategory.categoryid
							AND stockcategory.categoryid = internalstockcatrole.categoryid
                            AND  stockmaster.categoryid=locations.loccode
							AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
							AND stockmaster.stockid " . LIKE . " '" . $SearchString . "'
							AND stockmaster.mbflag <>'G'
							AND stockmaster.discontinued=0
							AND stockmaster.categoryid='" . $_POST['StockCat'] . "' ";
			}

		} else {
			if ($_POST['StockCat']=='All'){
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid loccode,
								locations.locationname,
								stockmaster.units as stockunits,
								stockmaster.decimalplaces
						FROM stockmaster,
							stockcategory,
							internalstockcatrole,
                            locations
						WHERE stockmaster.categoryid=stockcategory.categoryid
							AND stockcategory.categoryid = internalstockcatrole.categoryid
                            AND  stockmaster.categoryid=locations.loccode
							AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
							AND stockmaster.mbflag <>'G'
							AND stockmaster.discontinued=0 ";
			} else {
				$SQL = "SELECT stockmaster.stockid,
								stockmaster.description,
								stockmaster.categoryid loccode,
								locations.locationname,
								stockmaster.units as stockunits,
								stockmaster.decimalplaces
						FROM stockmaster,
							stockcategory,
							internalstockcatrole,
                            locations
						WHERE stockmaster.categoryid=stockcategory.categoryid
							AND stockcategory.categoryid = internalstockcatrole.categoryid
                            AND  stockmaster.categoryid=locations.loccode
							AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
							AND stockmaster.mbflag <>'G'
							AND stockmaster.discontinued=0
							AND stockmaster.categoryid='" . $_POST['StockCat'] . "'";
			}
		}
        $SQL.=" ORDER BY stockmaster.stockid  ";  
		if (isset($_POST['Next'])) {
			$Offset = $_POST['NextList'];
		}
		if (isset($_POST['Prev'])) {
			$Offset = $_POST['Previous'];
		}
		if (!isset($Offset) or $Offset<0) {
			$Offset=0;
		}
		$SQL = $SQL . ' LIMIT ' . $_SESSION['DefaultDisplayRecordsMax'] . ' OFFSET ' . ($_SESSION['DefaultDisplayRecordsMax']*$Offset);
	    //prnMsg('657'.$SQL);
		$ErrMsg = _('There is a problem selecting the part records to display because');
		$DbgMsg = _('The SQL used to get the part selection was');
		$SearchResult = DB_query($SQL,$ErrMsg, $DbgMsg);

		if (DB_num_rows($SearchResult)==0 ){
			prnMsg (_('There are no products available meeting the criteria specified'),'info');
		}
		if (DB_num_rows($SearchResult)<$_SESSION['DisplayRecordsMax']){
			$Offset=0;
		}

	} //end of if search
	echo '<br />
		<table class="selection">
		<tr>
			<th colspan="8"><h4>' . _('Details of Items Requested') . '</h4></th>
		</tr>
		<tr>
			<th>' .  _('Line Number') . '</th>
			<th class="ascending">' .  _('Item Code') . '</th>
			<th class="ascending">' .  _('Item Description'). '</th>
			<th class="ascending">仓库</th>
			<th class="ascending">' .  _('Quantity Required'). '</th>
			<th>' .  _('UOM'). '</th>
			<th>备注</th>
			<th colspan="1"></th>
		</tr>';

	$k=0;

	foreach ($_SESSION['Request']->LineItems as $LineItems) {

		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k++;
		}
		echo '<td>' .( $LineItems->LineNumber+1) . '</td>
				<td>' . $LineItems->StockID . '</td>
				<td>' . $LineItems->ItemDescription . '</td>
				<td>' . $LineItems->Loccode . '</td>
				<td class="number">' . locale_number_format($LineItems->Quantity, $LineItems->DecimalPlaces) . '</td>
				<td>' . $LineItems->UOM . '</td>
				<td><input type="text" name="Remark' . $LineItems->LineNumber . '" value="'.$_POST['Remark' .$LineItems->LineNumber].'"> </td>
			
				<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Modify='.$LineItems->LineNumber.'">' . _('Delete') . '</a></td>
			</tr>';
	}
	//	<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') . '?Edit='.$LineItems->LineNumber.'">' . _('Edit') . '</a></td>
	echo '</table>
		<br />
		<div class="centre">
			<input type="submit" name="Submit" value="' . _('Submit') . '" />
		</div>
		<br />
	';
	echo '</div>
	</form>';
	
	echo '<p class="page_title_text"><img src="' . $RootPath . '/css/' . $Theme . '/images/magnifier.png" title="' . _('Search') . '" alt="" />' . ' ' . _('Search for Inventory Items'). '</p>';
	$SQL = "SELECT stockcategory.categoryid,
					stockcategory.categorydescription
				FROM stockcategory, internalstockcatrole,locationusers
				WHERE stockcategory.categoryid = internalstockcatrole.categoryid
					AND stockcategory.categoryid = locationusers.loccode
					AND locationusers.userid='".$_SESSION['UserID']."'
					AND internalstockcatrole.secroleid= " . $_SESSION['AccessLevel'] . "
				ORDER BY stockcategory.categorydescription";
				//prnMsg($SQL);
	$result1 = DB_query($SQL);
	if (DB_num_rows($result1) == 0) {
		echo '<p class="bad">' . _('Problem Report') . ':<br />' . _('There are no stock categories currently defined please use the link below to set them up') . '</p>';
		echo '<br />
			<a href="' . $RootPath . '/StockCategories.php">' . _('Define Stock Categories') . '</a>';
		exit;
	}
	echo '<table class="selection">
		<tr>
			<td>' . _('In Stock Category') .  ':<select name="StockCat">';

	if (!isset($_POST['StockCat'])) {
		$_POST['StockCat'] = '';
	}
	/*
	if ($_POST['StockCat'] == 'All') {
		echo '<option selected="True" value="All">' . _('All Authorized') . '</option>';
	} else {
		echo '<option value="All">' . _('All Authorized') . '</option>';
	}*/
	while ($myrow1 = DB_fetch_array($result1)) {
		if ($myrow1['categoryid'] == $_POST['StockCat']) {
			echo '<option selected="True" value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		} else {
			echo '<option value="' . $myrow1['categoryid'] . '">' . $myrow1['categorydescription'] . '</option>';
		}
	}
	echo '</select></td>
		<td>' . _('Enter partial') . '<b> ' . _('Description') . '</b>:</td>';
	if (isset($_POST['Keywords'])) {
		echo '<td><input type="text" name="Keywords" value="' . $_POST['Keywords'] . '" size="20" maxlength="25" /></td>';
	} else {
		echo '<td><input type="text" name="Keywords" size="20" maxlength="25" /></td>';
	}
	echo '</tr>
			<tr>
				<td></td>
				<td><h3>' . _('OR') . ' ' . '</h3>' . _('Enter partial') . ' <b>' . _('Stock Code') . '</b>:</td>';

	if (isset($_POST['StockCode'])) {
		echo '<td><input type="text" autofocus="autofocus" name="StockCode" value="' . $_POST['StockCode'] . '" size="15" maxlength="18" /></td>';
	} else {
		echo '<td><input type="text" name="StockCode" size="15" maxlength="18" /></td>';
	}
	echo '</tr>
		</table>
		<br />
		<div class="centre">
			<input type="submit" name="Search" value="物料查找" />
		</div>
		<br />
		';
	
		//prnMsg('836'.$SQL);
	if (isset($searchresult) AND !isset($_POST['Select'])) {
			echo '<div>';
			$ListCount = DB_num_rows($searchresult);
		if ($ListCount > 0) {
			// If the user hit the search button and there is more than one item to show
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
			if ($_POST['PageOffset'] > $ListPageMax) {
				$_POST['PageOffset'] = $ListPageMax;
			}
			if ($ListPageMax > 1) {
				echo '<div class="centre"><br />&nbsp;&nbsp;' . $_POST['PageOffset'] . ' ' . _('of') . ' ' . $ListPageMax . ' ' . _('pages') . '. ' . _('Go to Page') . ': ';
				echo '<select name="PageOffset">';
				$ListPage = 1;
				while ($ListPage <= $ListPageMax) {
					if ($ListPage == $_POST['PageOffset']) {
						echo '<option value=' . $ListPage . ' selected>' . $ListPage . '</option>';
					} else {
						echo '<option value=' . $ListPage . '>' . $ListPage . '</option>';
					}
					$ListPage++;
				}
				echo '</select>
					<input type="submit" name="Go" value="' . _('Go') . '" />
					<input type="submit" name="Previous" value="' . _('Previous') . '" />
					<input type="submit" name="Next" value="' . _('Next') . '" />
					<input type="hidden" name=Keywords value="'.$_POST['Keywords'].'" />
					<input type="hidden" name=StockCat value="'.$_POST['StockCat'].'" />
					<input type="hidden" name=StockCode value="'.$_POST['StockCode'].'" />
					<br />
					</div>';
			}
			echo '<table cellpadding="2">';
			echo '<tr>
					<th>' . _('Code') . '</th>
					<th>' . _('Description') . '</th>
					<th>' . _('Total Qty On Hand') . '</th>
					<th>' . _('Units') . '</th>
					<th>' . _('Stock Status') . '</th>
				</tr>';
			$j = 1;
			$k = 0; //row counter to determine background colour
			$RowIndex = 0;
			if (DB_num_rows($searchresult) <> 0) {
				DB_data_seek($searchresult, ($_POST['PageOffset'] - 1) * $_SESSION['DisplayRecordsMax']);
			}
			while (($myrow = DB_fetch_array($searchresult)) AND ($RowIndex <> $_SESSION['DisplayRecordsMax'])) {
				if ($k == 1) {
					echo '<tr class="EvenTableRows">';
					$k = 0;
				} else {
					echo '<tr class="OddTableRows">';
					$k++;
				}
				if ($myrow['mbflag'] == 'D') {
					$qoh = _('N/A');
				} else {
					$qoh = locale_number_format($myrow['qoh'], $myrow['decimalplaces']);
				}
				if ($myrow['discontinued']==1){
					$ItemStatus = '<p class="bad">' . _('Obsolete') . '</p>';
				} else {
					$ItemStatus ='';
				}
	
			echo '<td><input type="submit" name="Select" value="' . $myrow['stockid'] . '" /></td>
						<td>' . $myrow['description'] . '</td>
						<td class="number">' . $qoh . '</td>
						<td>' . $myrow['units'] . '</td>
						<td><a target="_blank" href="' . $RootPath . '/StockStatus.php?StockID=' . $myrow['stockid'].'">' . _('View') . '</a></td>
						<td>' . $ItemStatus . '</td>
					</tr>';
				//end of page full new headings if
			}
			//end of while loop
			echo '</table>
			
				<br />';
		}
	}
	/* end display list if there is more than one record */

	if (isset($SearchResult)) {
		$j = 1;
		echo '<br />
			<div class="page_help_text">' . _('Select an item by entering the quantity required.  Click Order when ready.') . '</div>
			<br />';
		//	<form  id="form1" name="form1" action="' . htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') .$_SESSION['action']. '" method="post" id="orderform">
		echo'<div>
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<table class="table1">
			<tr>
				<td>
					<input type="hidden" name="Previous" value="'.($Offset-1).'" />
					<input tabindex="'.($j+8).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
					<td style="text-align:center" colspan="6">
					<input type="hidden" name="order_items" value="1" />
					<input tabindex="'.($j+9).'" type="submit" value="'._('Add to Requisition').'" /></td>
				<td>
					<input type="hidden" name="NextList" value="'.($Offset+1).'" />
					<input tabindex="'.($j+10).'" type="submit" name="Next" value="'._('Next').'" /></td>
				</tr>
				<tr>
					<th class="ascending">' . _('Code') . '</th>
					<th class="ascending">' . _('Description') . '</th>
					<th>' . _('Units') . '</th>
					<th class="ascending">' . _('On Hand') . '</th>
					<th class="ascending">' . _('On Demand') . '</th>
					<th class="ascending">' . _('On Order') . '</th>
					<th class="ascending">' . _('Available') . '</th>
					<th class="ascending">' . _('Quantity') . '</th>
				</tr>';
		$ImageSource = _('No Image');

		$k=0; //row colour counter
		$i=0;
		while ($myrow=DB_fetch_array($SearchResult)) {
			if ($myrow['decimalplaces']=='') {
				$DecimalPlacesSQL="SELECT decimalplaces
									FROM stockmaster
									WHERE stockid='" .$myrow['stockid'] . "'";
				$DecimalPlacesResult = DB_query($DecimalPlacesSQL);
				$DecimalPlacesRow = DB_fetch_array($DecimalPlacesResult);
				$DecimalPlaces = $DecimalPlacesRow['decimalplaces'];
			} else {
				$DecimalPlaces=$myrow['decimalplaces'];
			}

			$QOHSQL = "SELECT sum(locstock.quantity) AS qoh
								FROM locstock
								WHERE locstock.stockid='" .$myrow['stockid'] . "' AND
								loccode = '" . $_SESSION['Request']->Location . "'";
			$QOHResult =  DB_query($QOHSQL);
			$QOHRow = DB_fetch_array($QOHResult);
			$QOH = $QOHRow['qoh'];

			// Find the quantity on outstanding sales orders
			$sql = "SELECT SUM(salesorderdetails.quantity-salesorderdetails.qtyinvoiced) AS dem
					FROM salesorderdetails INNER JOIN salesorders
					ON salesorders.orderno = salesorderdetails.orderno
					WHERE salesorders.fromstkloc='" . $_SESSION['Request']->Location . "'
					AND salesorderdetails.completed=0
					AND salesorders.quotation=0
					AND salesorderdetails.stkcode='" . $myrow['stockid'] . "'";
			$ErrMsg = _('The demand for this product from') . ' ' . $_SESSION['Request']->Location . ' ' . _('cannot be retrieved because');
			$DemandResult = DB_query($sql,$ErrMsg);

			$DemandRow = DB_fetch_row($DemandResult);
			if ($DemandRow[0] != null){
				$DemandQty =  $DemandRow[0];
			} else {
			$DemandQty = 0;
			}

			// Find the quantity on purchase orders
			$sql = "SELECT SUM(purchorderdetails.quantityord-purchorderdetails.quantityrecd)*purchorderdetails.conversionfactor AS dem
					FROM purchorderdetails LEFT JOIN purchorders
						ON purchorderdetails.orderno=purchorders.orderno
					WHERE purchorderdetails.completed=0
					AND purchorders.status<>'Cancelled'
					AND purchorders.status<>'Rejected'
					AND purchorders.status<>'Completed'
					AND purchorderdetails.itemcode='" . $myrow['stockid'] . "'";

			$ErrMsg = _('The order details for this product cannot be retrieved because');
			$PurchResult = DB_query($sql,$ErrMsg);

			$PurchRow = DB_fetch_row($PurchResult);
			if ($PurchRow[0]!=null){
				$PurchQty =  $PurchRow[0];
			} else {
				$PurchQty = 0;
			}

			// Find the quantity on works orders
			$sql = "SELECT SUM(woitems.qtyreqd - woitems.qtyrecd) AS dedm
				FROM woitems
				WHERE stockid='" . $myrow['stockid'] ."'";
			$ErrMsg = _('The order details for this product cannot be retrieved because');
			$WoResult = DB_query($sql,$ErrMsg);

			$WoRow = DB_fetch_row($WoResult);
			if ($WoRow[0]!=null){
				$WoQty =  $WoRow[0];
			} else {
				$WoQty = 0;
			}

			if ($k==1){
				echo '<tr class="EvenTableRows">';
				$k=0;
			} else {
				echo '<tr class="OddTableRows">';
				$k=1;
			}
			$OnOrder = $PurchQty + $WoQty;
			$Available = $QOH - $DemandQty + $OnOrder;
			echo '<td>' . $myrow['stockid'] . '</td>
					<td>' . $myrow['description'] . '</td>
					<td>' . $myrow['stockunits'] . '</td>
					<td class="number">' . locale_number_format($QOH,$DecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($DemandQty,$DecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($OnOrder, $DecimalPlaces) . '</td>
					<td class="number">' . locale_number_format($Available,$DecimalPlaces) . '</td>
					<td><input class="number" ' . ($i==0 ? 'autofocus="autofocus"':'') . ' tabindex="'.($j+7).'" type="text" size="6" name="Quantity'.$i.'" value="" />
					
					</td>
				</tr>';
			echo '<input type="hidden" name="Loccode'.$i.'" value="'.$myrow['loccode'].'" />
		
			<input type="hidden" name="StockID'.$i.'" value="'.$myrow['stockid'].'" />
			      <input type="hidden" name="DecimalPlaces'.$i.'" value="' . $myrow['decimalplaces'] . '" />';
			echo '<input type="hidden" name="ItemDescription'.$i.'" value="' . $myrow['description'] . '" />';
			echo '<input type="hidden" name="Units'.$i.'" value="' . $myrow['stockunits'] . '" />';
			$i++;
		}#end of while loop
		echo '<tr>
				<td><input type="hidden" name="Previous" value="'.($Offset-1).'" />
					<input tabindex="'.($j+7).'" type="submit" name="Prev" value="'._('Prev').'" /></td>
				<td style="text-align:center" colspan="6"><input type="hidden" name="order_items" value="1" />
					<input tabindex="'.($j+8).'" type="submit" value="'._('Add to Requisition').'" /></td>
				<td><input type="hidden" name="NextList" value="'.($Offset+1).'" />
					<input tabindex="'.($j+9).'" type="submit" name="Next" value="'._('Next').'" /></td>
			<tr/>
			</table>
		</div>
		';
	}#end if SearchResults to show
}

if(isset($_POST['SearchRequest'])||isset($_POST['crtExcel'])) {
	//查询计划单
	//echo "<script language=JavaScript> location.replace(location.href);</script>";
	if (isset($_POST['ShowDetails']) OR isset($StockID)) {
		$SQL = "SELECT stockrequest.dispatchid, 
						stockmaster.categoryid loccode,
						stockrequest.departmentid,
						departments.description,
						locations.locationname,
						despatchdate,
						authorised,
						closed,
						narrative,
						initiator,
						stockrequestitems.stockid,
						stockmaster.description as stkdescription,
						quantity,
						stockrequestitems.decimalplaces,
						uom,
						completed,
						remark
			FROM stockrequest 
			INNER JOIN stockrequestitems ON stockrequest.dispatchid=stockrequestitems.dispatchid 
			INNER JOIN departments ON stockrequest.departmentid=departments.departmentid 		
			INNER JOIN stockmaster ON stockrequestitems.stockid=stockmaster.stockid
			INNER JOIN locations ON locations.loccode=stockmaster.categoryid
			WHERE 1 "; 
					//查询单号 编码  名称
					$StringNo='';
				
					if ($_POST['SearchNo'] != '') {
							if ( strpos($_POST['SearchNo'],'-')>0 ){
								$SearchNoArr=explode('-', $_POST['SearchNo']);					
							if ((int)$SearchNoArr[0] <= (int)$SearchNoArr[1]){				
								$n=(int)$SearchNoArr[0];
								$p=(int)$SearchNoArr[1];
							}else {
								$p=(int)$SearchNoArr[0];
								$n=(int)$SearchNoArr[1];						
							}	
							for ($i = $n; $i <= $p; $i++) {					
								$StringNo.='"'.$i.'",';
							} 					
							$StringNo=substr($StringNo,0, -1);
						}else{
							$StringNo='"'.$_POST['SearchNo'].'"';							 		
						}
						
					}
					//物料编码
					
					if (!empty($StringNo) ){         
						$SQL.=" AND stockrequest.dispatchid IN  ( ".$StringNo." )  ";          
					}
					$StringCode='';
				if ($_POST['SearchCode'] != '') {
						if ( strpos($_POST['SearchCode'],'-')>0 ){
							$SearchCodeArr=explode('-', $_POST['SearchCode']);					
						if ((int)$SearchCodeArr[0] <= (int)$SearchCodeArr[1]){				
							$n=(int)$SearchCodeArr[0];
							$p=(int)$SearchCodeArr[1];
						}else {
							$p=(int)$SearchCodeArr[0];
							$n=(int)$SearchCodeArr[1];						
						}	
						for ($i = $n; $i <= $p; $i++) {					
							$StringCode.='"'.$i.'",';
						} 					
						$StringCode=substr($StringCode,0, -1);
					}else{
						$StringCode='"'.$_POST['SearchCode'].'"';							 		
					}
					
				}
				if (!empty($StringCode) ){         
					$SQL.=" AND stockrequestitems.stockid IN  ( ".$StringCode." )  ";          
				}
				//名称  
				$SearchStr='';
				if ($_POST['SearchName']!=''){
					$strr='';
					$str=str_replace(' ', '%',  trim($_POST['SearchName']));
					$strarr=str_split($str, 1);
					$p=-1;
					foreach($strarr as $val){
						if ($val=='%'){
							if ($p==-1){
							$strr.=$val;
							   $p=0;
							}else{
								$p=-1;
							}
						}else{
							$strr.=$val;
							$p=-1;
						}
					}
					$SearchStr = '%'.$strr .'%';
					$SQL.= ' AND stockmaster.description  like "'.$SearchStr.'" ';
				}
				$SearchNarrative='';
				if ($_POST['Narrative']!=''){
					$strr='';
					$str=str_replace(' ', '%',  trim($_POST['Narrative']));
					$strarr=str_split($str, 1);
					$p=-1;
					foreach($strarr as $val){
						if ($val=='%'){
							if ($p==-1){
							$strr.=$val;
							   $p=0;
							}else{
								$p=-1;
							}
						}else{
							$strr.=$val;
							$p=-1;
						}
					}
					$SearchNarrative = '%'.$strr .'%';
					$SQL.= ' AND remark  like "'.$SearchNarrative.'" ';
				}
			
	} else {
		$SQL = "SELECT stockrequest.dispatchid,
					stockrequest.loccode,
					stockrequest.departmentid,
					departments.description,
					'' locationname,
					despatchdate,
					authorised,
					closed,
					narrative,
					initiator
					FROM stockrequest
					INNER JOIN departments ON stockrequest.departmentid=departments.departmentid ";
	}
				//first the constraint of locations;
				if ($_POST['StockLocation'] != '0') {//retrieve the location data from current code
					$SQL .= " AND stockmaster.categoryid='" . $_POST['StockLocation'] . "'";
				} else {//retrieve the location data from serialzed data
					if (!in_array(19,$_SESSION['AllowedPageSecurityTokens'])) {
						$Locations = unserialize($_POST['Locations']);
						$Locations = implode("','",$Locations);
						$SQL .= " AND stockmaster.categoryid in ('" . $Locations . "')";
					} 
				}
				
				//the department: if the department is all, no bothering for this since user has no relation ship with department; but consider the efficency, we should use the departments to filter those no needed out
				if ($_POST['Department'] == '0') {
					if (!in_array(19,$_SESSION['AllowedPageSecurityTokens'])) {

						if (isset($_POST['Departments'])) {
							$Departments = unserialize(base64_decode($_POST['Departments']));
							$Departments = implode("','", $Departments);
							$SQL .= " AND stockrequest.departmentid IN ('" . $Departments . "')";
							
						} //IF there are no departments set,so forgot it
						
					}
				} else {
					$SQL .= " AND stockrequest.departmentid='" . $_POST['Department'] . "'";
				}
				//Date from
				if (isset($_POST['DispatchDate']) AND is_date($_POST['DispatchDate'])) {
					$SQL .= " AND despatchdate<='" . $_POST['DispatchDate'] . "'";
				}
		//	if (isset($_POST['ShowDetails']) OR isset($StockID)) {
			
		
				//the user or authority contraint
				if (!in_array(19,$_SESSION['AllowedPageSecurityTokens'])) {
					$SQL .= " AND (authoriser='" . $_SESSION['UserID'] . "' OR initiator='" . $_SESSION['UserID'] . "')";
				}
	            if (count($_POST['SearchType']) <3  &&count($_POST['SearchType'])>0){
					$SQL .= " AND (";
					$or="";
                    if (in_array(2,$_POST['SearchType']))   {
						$SQL .= "  closed=1  ";	
						$or="OR";
					}
					if (in_array(0,$_POST['SearchType']))   {
					
							$SQL .= $or. " authorised=1 ";
							if ($or==""){
								$or="OR";
							}
							
					}
					if (in_array(1,$_POST['SearchType']))   {
					
						$SQL .= $or."  (authorised=0 AND closed=0 ) ";	
						if ($or==""){
						    $or="OR";
						}
					}
					$SQL.=" ) ";
				}
				$SQL.=" ORDER BY stockrequest.dispatchid DESC, stockrequest.departmentid";
			
	$result = DB_query($SQL);
}
if(isset($_POST['SearchRequest'])){
	if (DB_num_rows($result)>0) {
		$Html = '';
		if (isset($_POST['ShowDetails']) OR isset($StockID)) {
			unset($_POST['Edit']);
			$Html .= '<table>
					<tr>
						<th>采购单编号</th>					
						<th>' . _('Department') . '</th>
						<th width="200px;">摘要</th>
						<th>' . _('Dispatch Date') . '</th>
						<th>' . _('Locations') . '</th>
						<th>' . _('Stock ID') . '</th>
						<th>' . _('Description') . '</th>
						<th>备注</th>
						<th>' . _('Quantity') . '</th>
						<th>' . _('Units') . '</th>
						<th>' . _('Authorization') . '</th>
						<th>' . _('Completed') . '</th>
					</tr>';
		} else {
			$Html .= '<table>
					<tr>
						<th>' . _('ID') . '</th>
						<th>' . _('Locations') . '</th>
						<th>' . _('Department') . '</th>
						<th>' . _('Authorization') . '</th>
						<th>' . _('Dispatch Date') . '</th>	
					</tr>';
		}

		if (isset($_POST['ShowDetails']) OR isset($StockID)) {
			$ID = '';//mark the ID change of the internal request 
		}
		$i = 0;
		//There are items without details AND with it
		while ($myrow = DB_fetch_array($result)) {
			if ($i == 0) {
				$Html .= "<tr class=\"EvenTableRows\">";
				$i = 1;
			} elseif ($i == 1) {
				$Html .= "<tr class=\"OddTableRows\">";
				$i = 0;
			}
			if ($myrow['authorised'] == 0) {
				$Auth = _('No');
			} else {
				$Auth = _('Yes');
			}
			if ($myrow['despatchdate'] == '0000-00-00') {
				$Disp = _('Not yet');
			} else {
				$Disp = ConvertSQLDate($myrow['despatchdate']);
			}
			if (isset($ID)) {
				if ($myrow['completed'] == 0) { 
					$Comp = _('No');
				} else {
					$Comp = _('Yes');
				}
			}
			if (isset($ID) AND ($ID != $myrow['dispatchid'])) {
				$ID = $myrow['dispatchid'];
				if ($myrow['authorised']==1||$myrow['closed']==1){
					$UrlString = $myrow['dispatchid'];
					if ($myrow['authorised']==1){
						$title="审批单已经授权";
					}else{
						$title="审批单已经��闭";
					}
				}else{
					$title="点击修改删除采购单";
					$UrlString = "<a href=".$RootPath . '/InternalStockRequest.php?Modify=Yes&ReqNO='.url_encode($myrow['dispatchid'].'^'.$myrow['completed']).'  id="href'.$RowIndex.'" name="href'.$RowIndex.'"  target="_blank" >'. $myrow['dispatchid'].'</a>';
				}	

				$Html .= '<td title="'.$title.'">'. $UrlString.'</td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $myrow['narrative'] . '</td>
						<td>' . $Disp . '</td>
						<td>' . $myrow['locationname'] . '</td>
						<td>' . $myrow['stockid'] . '</td>
						<td>' . $myrow['stkdescription'] . '</td>
						<td>' . $myrow['remark'] . '</td>
						<td>' . locale_number_format($myrow['quantity'],$myrow['decimalplaces']) . '</td>
						<td>' . $myrow['uom'] . '</td>
						<td>' . $Auth . '</td>
						<td>' . $Comp . '</td>';

			} elseif (isset($ID) AND ($ID == $myrow['dispatchid'])) {
				$Html .= '<td></td>
						<td></td>
						<td></td>
						
						<td></td>
						<td></td>
						<td>' . $myrow['stockid'] . '</td>
						<td>' . $myrow['stkdescription'] . '</td>
						<td>' . $myrow['remark'] . '</td>
						<td>' . locale_number_format($myrow['quantity'],$myrow['decimalplaces']) . '</td>
						<td>' . $myrow['uom'] . '</td>
						<td>' . $Auth . '</td>
						<td>' . $Comp . '</td>';
			} elseif(!isset($ID)) {
					$Html .= '<td>' . $myrow['dispatchid'] . '</td>
						<td>' . $myrow['locationname'] . '</td>
						<td>' . $myrow['description'] . '</td>
						<td>' . $Auth . '</td>
						<td>' . $Disp . '</td>';
			}
			$Html .= '</tr>';
		}//end of while loop;
		$Html .= '</table>';
		//echo '<a href="' . $RootPath . '/InternalStockRequestInquiry.php">' . _('Select Others') . '</a>';
		//echo '<input type="hidden" name="Edit" value="Yes" />';
		echo $Html;
	} else {
		prnMsg(_('There are no stock request available'),'warn');
	}	
	//------------
}elseif(isset($_POST['crtExcel'])){
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
				$objPHPExcel->getActiveSheet()->setCellValue('B3', '采购部门');
				$objPHPExcel->getActiveSheet()->setCellValue('C3', '摘要');
				$objPHPExcel->getActiveSheet()->setCellValue('D3', '发运日期');
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
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['description']);
						//设置A3单元格为文本
						$objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
						$objPHPExcel->getActiveSheet()->getStyle('C'.$i)->getAlignment()->setWrapText(true); //设置换行

						$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $myrow['narrative']);
						$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $myrow['despatchdate']);
						$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['locationname']);
						$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['stockid']);
						$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['stkdescription']);
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
						$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['stkdescription']);
						$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $myrow['remark']);
						$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $myrow['quantity']);
						$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $myrow['uom']);
						$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $authorised);
						$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $completed);	

					}elseif(!isset($ID)) {
						$objPHPExcel->getActiveSheet()->setCellValue('A'.$i,  $myrow['dispatchid']);
						$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $myrow['description']);
						$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $authorised);
						$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $myrow['despatchdate']);
						$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $myrow['locationname']);
						$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $myrow['stockid']);
						$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $myrow['stkdescription']);
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
}
echo '</div>
</form>';
//*********************************************************************************************************
include('includes/footer.php');
?>
