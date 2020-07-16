<?php
/* $Id: Currencies.php 7317 2015-06-05 03:28:26Z turbopt $*/
/* This script defines the currencies available. Each customer and supplier must be defined as transacting in one of the currencies defined here. */

include('includes/session.php');
$Title ='移动办公_更新';
$ViewTopic= 'Currencies';// Filename's id in ManualContents.php's TOC.
$BookMark = 'Currencies';// Anchor's id in the manual's html document.
include('includes/header.php');


include('includes/SQL_CommonFunctions.inc');

if (isset($_GET['SelectedCurrency'])){
	$SelectedCurrency = $_GET['SelectedCurrency'];
} elseif (isset($_POST['SelectedCurrency'])){
	$SelectedCurrency = $_POST['SelectedCurrency'];
}

$ForceConfigReload = true;
include('includes/GetConfig.php');

$FunctionalCurrency = $_SESSION['CompanyRecord']['currencydefault'];

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();
	 // 获取授权码;
	 if ((isset($_SESSION['accesstoken'])&&$_SESSION['tokentime']<strtotime("now")-7000)||!isset($_SESSION['accesstoken'])){
		$url="https://oapi.dingtalk.com/gettoken?appkey=".$_SESSION['appkey']."&appsecret=".$_SESSION['appsecret'];
		//oapi.dingtalk.com/sns/gettoken?appid=dingoauhh7qo9zz3jffdoy&appsecret=Iq_KW-6_8_5BDRzZrS9vHXtN_TWTGGlbtBrWSYpQPuuMPS4M12-65ldO9AlhnDEZ";
		// N1s7Gmt_zrWYxh3hkEa0OHozcoZEZCpZDevKNoP4-F1_5a_UBhnFqLABcYcabuo_	
		$html = file_get_contents($url);       
		$token=json_decode($html, true);
		//array(4) { ["errcode"]=> int(0) ["access_token"]=> string(32) "024d3888cf063242951ea66762746536" ["errmsg"]=> string(2) "ok" ["expires_in"]=> int(7200) }
		if ($token['errcode']==0){
				$_SESSION['accesstoken']=$token['access_token'];
				$_SESSION['tokentime']=strtotime("now");
		}
	}
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.
	'/images/currency.png" title="' .// Icon image.
	$Title . '" /> ' .// Icon title.
	$Title . '</p>';// Page title.
	echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . $_SERVER['PHP_SELF'] . '">
			<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
			<input type="hidden" name="SelectTable" value="' . $_POST['SelectTable'] . '" />';
	echo '<div class="page_help_text">
			功能简介：更新移动办公服务资料</br>		
		</div>';	
$move_to_path='companies/'.$_SESSION['DatabaseName']."/temp/";
$dir='./companies/'.$_SESSION['DatabaseName'].'/temp/';
//echo 'AppKey---'.$_SESSION['appkey'];
//echo  '<br>Appsecret---'.$_SESSION['appsecret'];
//echo '<br>AccessToken---'.$_SESSION['accesstoken'];

if (isset($_POST['Department'])){ 
	//获取部门列表
	$url="https://oapi.dingtalk.com/department/list?access_token=".$_SESSION['accesstoken'];
	$html = file_get_contents($url);       
	$token=json_decode($html, true);
	echo '<table class="selection">	
	<th width="110">DD DEMO</th>	';
	echo '<tr><td >';		
	foreach($token['department'] as $row){
		  $SQL="INSERT INTO `mobiledepart`(`id`,
											`departmentid`,
											`name`,
											`parentid`,
											`createDeptGroup`,
											`autoAddUser`,
											`lastdate`)
					          	VALUES('".$row['id']."',
								        0,
										'".$row['name']."',
										'".$row['parentid']."',
										'".$row['createDeptGroup']."',
										'".$row['autoAddUser']."',
										'".date("Y-m-d h:i:s")."')";
	     $result=DB_query($SQL);
	}
	echo'</td></tr>
	</table';
}elseif (isset($_POST['Addressbook'])){ 
           	//获取部门用户userid列表
			$url="https://oapi.dingtalk.com/user/getDeptMember?access_token=".$_SESSION['accesstoken']."&deptId=72472294";
			$html = file_get_contents($url);       
			$token=json_decode($html, true);
			echo '<tr><td>TWO:';
			//var_dump($token);
			$SQL="INSERT INTO `mobileusers`(`unionid`,
											`userid`,
											`openId`,
											`name`,
											`employee_id`,
											`isLeaderInDepts`,
											`isBoss`,
											`isSenior`,
											`orderInDepts`,
											`department`,
											`dingId`,
											`mobile`,
											`active`,
											`avatar`,
											`stateCode`,
											`status`,
											`lastdate`,
											`email`)
										VALUES('";
			echo'</td></tr>';
			//uniodid获取员工详情
			$url="https://oapi.dingtalk.com/user/getUseridByUnionid?access_token=".$_SESSION['accesstoken']."&unionid=040955443929412844";
			$html = file_get_contents($url);       
			$token=json_decode($html, true);
			echo '<tr><td>TWO:';
			//var_dump($token);
			/* ["userIds"]=> array(10) { [0]=> string(18) "040955443929412844" 王祖德
										[1]=> string(18) "144421202229323612" 成功
										[2]=> string(16) "5104231322775325"姜先安
										 [3]=> string(18) "144418482722862961"姜子童
										  [4]=> string(14) "18640112738228 " 姜成
										  [5]=> string(18) "010931004529424471" 王祖财
										  [6]=> string(16) "2437533123316457"姜锡明 
										  [7]=> string(18) "050122483135387000费丽娜""
										   [8]=> string(18) "014500571220227384" 于浩艳
										   [9]=> string(16) "2207615620005835"丛彩芹 } }*/
			echo'</td></tr>';
				//userid获取员工详情
				$url="https://oapi.dingtalk.com/user/get?access_token=".$_SESSION['accesstoken']."&userid=2207615620005835";
				$html = file_get_contents($url);       
				$token=json_decode($html, true);
				echo '<tr><td>TWO:';
				var_dump($token);
				echo'</td></tr>';
			echo '	</table>';
}else if (isset($_POST['Procurement'])){
	//采购申请

		$url="https://oapi.dingtalk.com/topapi/processinstance/create?access_token=".$_SESSION['accesstoken']; 
		$processCode="PROC-CFYJKV2V-A86077FI31B7P72KRASH2-U1T1O8OJ-7";
	
		$originator_user_id="014500571220227384";
		$dept_id="203717592";
				// #钉钉后台配置的需要填写的字段（这一段由点到传过来实现，这里只是测试使用）
			
				$mx=array(array(array('name'=>'名称','value'=>'试验ghjhrtyuiortg品' ),array("name"=>"数量","value"=>"12345" ),array("name"=> "单价", "value"=>"345"),array("name"=> "单品总价", "value" =>"12987345") ));
				$detail=json_encode($mx,JSON_UNESCAPED_UNICODE);
				
		$form_component_values =array(array("name"=>"采购日期","value"=>"2019-12-12"), 
									array("name"=>"采购人", "value" =>"于艳"),
									array("name"=>"明细","value"=>$detail),
									  array("name"=>"备注","value"=>"fdsasf")
								 ); 
								
		$json_data = '{
		"agent_id": "203717592",
		"process_code" :"' .$processCode.'",
			   "originator_user_id" :"' .$originator_user_id.'",
			   "dept_id" :"' .$dept_id.'",
			   "approvers": "18640112738228",
				"cc_list": ["144421202229323612", "144418482722862961"],
			   "form_component_values" :' .json_encode($form_component_values,JSON_UNESCAPED_UNICODE).' }';
	//, #钉钉后台配置的需要填写的字段

		
			$ret=curlexec($url,$json_data);
			echo '<table class="selection">	';
			echo '<tr><td >';		
			echo $json_data."<br>";
			$ret=curlexec($url,$json_data);
			  var_dump($ret);
			echo'<br>';		
			ECHO '</td></tr>';
			
			echo '	</table>';

}else if (isset($_POST['GetApproval'])){
	//采购申请

		$url="https://oapi.dingtalk.com/topapi/processinstance/listids?access_token=".$_SESSION['accesstoken']; 
		$processCode="PROC-CFYJKV2V-A86077FI31B7P72KRASH2-U1T1O8OJ-7";
	
		$originator_user_id="014500571220227384";
		$dept_id="203717592";
				// #钉钉后台配置的需要填写的字段（这一段由点到传过来实现，这里只是测试使用）
		$starttime=strtotime("2019-12-13")	;
								
		$json_data = '{
				"agent_id": "203717592",
				"process_code" :"' .$processCode.'",
			   "originator_user_id" :"' .$originator_user_id.'",
			   "dept_id" :"' .$dept_id.'",
			   "start_time":'.$starttime.',
			   "approvers": "18640112738228"}';
			   /*
				"cc_list": ["144421202229323612", "144418482722862961"],
			   "form_component_values" :' .$form_component_values.' }';
	//, #钉钉后台配置的需要填写的字段
*/
		
			$ret=curlexec($url,$json_data);
			echo '<table class="selection">	';
			echo '<tr><td >';		
		//	echo $json_data."<br>";
		
			
			echo $ret['result']['list'][0];
			//获取审批明细
			$url="https://oapi.dingtalk.com/topapi/processinstance/get?access_token=".$_SESSION['accesstoken']; 
			$json_data='{"process_instance_id":"'.$ret['result']['list'][0].'"}';
			$ret=curlexec($url,$json_data);
			var_dump($ret);
			echo'<br>';		
			ECHO '</td></tr>';
			
			echo '	</table>';

}else if (isset($_POST['message'])){
	$url="https://oapi.dingtalk.com/topapi/message/corpconversation/asyncsend_v2?access_token=".$_SESSION['accesstoken']; 
		$data = array("tmp_auth_code"=>$code );
	
	       //"userid_list": "18640112738228", 			                
			           
		$data_string ='{ "userid_list": "014500571220227384", 			                
						"agent_id ": "203717592",
						"msgtype": "text",	
						"msg":{ 
						   "msgtype": "text",	
						"text": {
							"content": "于浩艳,你好!"
						}
					   }
				  }';			 
		//json_encode($data,JSON_UNESCAPED_UNICODE);
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		$union_code=json_decode($result,true);
		echo '<table class="selection">	
		<th width="110">STUDY DEMO</th>	';
		echo '<tr><td >ONE:';		
	  var_dump($union_code);
		echo'</td></tr>';		
		echo '	</table>';

}else if (isset($_POST['eventcallback'])){

	//2.1 审批列表请求地址
	
	$url="https://oapi.dingtalk.com/topapi/processinstance/listids?access_token=".$_SESSION['accesstoken']; 
	
	//$url="https://oapi.dingtalk.com/topapi/attendance/isopensmartreport?access_token=".$_SESSION['accesstoken']; 
	$html = file_get_contents($url);   
	//$html=curlexec($url,'') ;   
	$token=json_decode($html, true);
		echo '<table class="selection">	
		<th width="110">STUDY DEMO</th>	';
		echo '<tr><td >ONE:';		
	  var_dump($token);
		echo'</td></tr>';
		echo '<tr><td>TWO:';
	
		echo'<br>';
	
	
		ECHO '</td></tr>';
		
		echo '	</table>';





}else if (isset($_POST['PayApproval'])){
    // 通用付款申请     
	$form_component_values ='[{"name": "付款事由", "value" : "部门级"}, 
							   {"name": "付款总额", "value" : "12345"}, 
							   {"name": "付款方式", "value" : "现金"}, 
								  {"name":"支付日期", "value" : "2019-09-10"},
								  {"name": "支付对象", "value" : "部门级5"},
								  {"name": "支付对象", "value" : "公司3456"},
								  {"name": "开户行", "value" : "dfghj"},
								  {"name": "银行账户", "value" : "部门级"}
								  						   ] ';		
	
	$processCode="PROC-F83BFADE-98D3-4438-8A99-87600CB10D27";
	
	$originator_user_id="014500571220227384";
	$dept_id="203717592";
	
	$json_data = '{
		"agent_id": "203717592",
		"process_code" :"' .$processCode.'",
			   "originator_user_id" :"' .$originator_user_id.'",
			   "dept_id" :"' .$dept_id.'",
			   "approvers": "18640112738228",
				"cc_list": ["144421202229323612", "144418482722862961"],
			   "form_component_values" :' .$form_component_values.' }';
	
	 $url="https://oapi.dingtalk.com/topapi/processinstance/create?access_token=".$_SESSION['accesstoken']; 
    echo $json_data."<br>";
	 $ret=curlexec($url,$json_data);
       var_dump($ret);
}else if (isset($_POST['leave'])){
	//请假
	//$processCode="PROC-8DBF731D-5D09-4CA3-A15C-EDFC1F44E972";
	$process_code="PROC-5FYJE1FV-ZW30W6W210H8ABULTGAQ3-730EICOJ-94";
	$url ="https://oapi.dingtalk.com/topapi/processinstance/create?access_token=" .$_SESSION['accesstoken']; 
	$originator_user_id="014500571220227384";
	$dept_id="72472294";
	$form_component_values =' [{"name": "姓名", "value": "测试2"},
						{"name": "部门", "value": "测试2"},
						 {"name": \"["开始时间","结束时间"]\", "value": \"["2019-12-11 17:32","2019-12-13 11:36"]\"}, 
						 {"name": "加班事由", "value": "测试2"}]';
	// #钉钉后台配置的需要填写的字段（这一段由点到传过来实现，这里只是测试使用）
  
	$json_data='
	
    {
		"agent_id": "203717592",
        "process_code": "' .$process_code.'",
        "originator_user_id": "' .$originator_user_id.'",
        "dept_id": "' .$dept_id.'",
        "form_component_values": ' .$form_component_values.' 
    }';


	
	// $url="https://oapi.dingtalk.com/topapi/process/workrecord/create?access_token=".$_SESSION['accesstoken']; 
    echo $json_data;
	 $ret=curlexec($url,$json_data);
       var_dump($ret);

}
echo '<div class="centre">
	<input type="submit" name="Department" value="更新部门" />
	<input type="submit" name="Addressbook" value="更新通讯录" />
	<input type="submit" name="eventcallback" value="业务事件回调"/>
	<input type="submit" name="message" value="钉钉消息通知"/>	
	<input type="submit" name="POSTDEMO" value="考勤管理" /><br/>

	<input type="submit" name="leave" value="请假发起"/>
	<input type="submit" name="leave" value="报销"/>
	<input type="submit" name="Procurement" value="采购申请"/>
	<input type="submit" name="PayApproval" value="付款申请"/><br/>
	<input type="submit" name="GetApproval" value="获取审批明细" />	';
	'</div>';
echo '</div></form>';
include('includes/footer.php');
function curlexec($Url,$JsonData){
	$ch = curl_init($Url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS,$JsonData);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				'Content-Type: application/json',
				'Content-Length: ' . strlen($JsonData))
		);

		$result = curl_exec($ch);
		$json_array=json_decode($result,true);
      return $json_array;
}	
