<?php
session_start();
echo'
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>      
    <title>WebERP</title>
   
    <meta http-equiv="Content-Type" content="application/html; charset=utf-8" />
    <script type="text/javascript" src="https://g.alicdn.com/dingding/dinglogin/0.0.5/ddLogin.js"></script>
</head>
<body>
<div style="text-align:center">
<span>操作员开通申请</span>';
$Theme='xenos';
$PHP_SELF=$_SERVER['PHP_SELF'];
$RootPath=$_SERVER['HTTP_HOST'].substr($PHP_SELF,0,strrpos($PHP_SELF,'/'));
include( 'config.php');
$db0 = mysqli_connect($host , $DBUser, $DBPassword, 'erp_gjw', $mysqlport);
		mysqli_set_charset($db0, 'utf8');
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '"> ';
if (!isset($_GET['code'])&&!isset($_POST['Submit'])){
	echo'<div id="login_container">
		<script type="text/javascript" >
			var url=encodeURIComponent("http://weihai.erpgo.top/login.php");
			var obj = DDLogin({
				id:"login_container",
				goto: encodeURIComponent("https://oapi.dingtalk.com/connect/oauth2/sns_authorize?appid=dingoauhh7qo9zz3jffdoy&response_type=code&scope=snsapi_login&state=1&redirect_uri="+url),
				style: "border:none;background-color:#FFFFFF;",
				width : "250",
				height: "300"
			})

			var hanndleMessage = function (event) {
				var origin = event.origin;
				console.log("origin", event.origin);
				if( origin == "https://login.dingtalk.com" ) { 
					//判断是否来自ddLogin扫码事件。
					var loginTmpCode = event.data;
					//拿到loginTmpCode后就可以在这里构造跳转链接进行跳转了
					console.log("loginTmpCode", loginTmpCode);
					window.location.href="https://oapi.dingtalk.com/connect/oauth2/sns_authorize?appid=dingoauhh7qo9zz3jffdoy&response_type=code&scope=snsapi_login&state=STATE&redirect_uri=http://weihai.erpgo.top/login.php&loginTmpCode=" +loginTmpCode;
		
				} 
			}
			if (typeof window.addEventListener != "undefined") {
				window.addEventListener("message", hanndleMessage, false);
			} else if (typeof window.attachEvent != "undefined") {
				window.attachEvent("onmessage", hanndleMessage);
			}
		
		</script>
		</div>';
}else{//if(isset($_POST['Submit'])||isset($_GET['code'])){
	if (isset($_GET['code'])){
		$code= $_GET['code'];
	}
	if(!isset($_SESSION['unionnick'])){

		if (isset($_GET['code'])){

			$url="https://oapi.dingtalk.com/sns/gettoken?appid=dingoauhh7qo9zz3jffdoy&appsecret=Iq_KW-6_8_5BDRzZrS9vHXtN_TWTGGlbtBrWSYpQPuuMPS4M12-65ldO9AlhnDEZ";
				
			$html = file_get_contents($url);       
			$htmlarr=json_decode($html, true);
			$access_token=$htmlarr['access_token'];
			// 获取授权码;

			$url="https://oapi.dingtalk.com/sns/get_persistent_code?access_token=".  $access_token; 
			$data = array("tmp_auth_code"=>$code );
			$data_string = json_encode($data,JSON_UNESCAPED_UNICODE);


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
		}
			/*array(2) { ["errcode"]=> int(40078) ["errmsg"]=> string(27) "不存在的临时授权码" } -
			array(5) { ["errcode"]=> int(0) ["errmsg"]=> string(2) "ok" ["unionid"]=> string(27) "L5iiLfhKF2P19VMKRAc4QXAiEiE" ["openid"]=> string(27) "xrKiPtqVMEkJ5Q60Jfn3DAwiEiE" ["persistent_code"]=> string(64) "P5IEA6JVdH7C3tQXStXeV8t0LCP9Cw_a5cr8E_Q1CBib4sCGl8q3dcbKiKckaPAi" } -*/


		if ($union_code['errcode']==0){
			
				//获取用户授权的SNS_TOKEN。以post请求，请求这个地址
			if (isset($_GET['code'])&&!isset($_SESSION['unionnick'])){
				$url="https://oapi.dingtalk.com/sns/get_sns_token?access_token=".$access_token;
				$data = array("openid"=>$union_code['openid'],
					"persistent_code"=>$union_code['persistent_code']
						);
				$data_string = json_encode($data,JSON_UNESCAPED_UNICODE);


				$ch = curl_init($url);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_POSTFIELDS,$data_string);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
						'Content-Type: application/json',
						'Content-Length: ' . strlen($data_string))
				);

				$result = curl_exec($ch);
				$sns_token=json_decode($result,true);
				//获取钉钉用户的unionid
				$url="https://oapi.dingtalk.com/sns/getuserinfo?sns_token=".$sns_token['sns_token'];
				$result = file_get_contents($url);       
				$_SESSION['unionnick']=json_decode($result, true);
			}
		}else{
			echo  '不存再用户!';
			$_POST['UserErr']=1;
		}
	}
//}			//
			/*array(3) { ["errcode"]=> int(0) ["errmsg"]=> string(2) "ok" ["user_info"]=> array(4) 
			{ ["nick"]=> string(6) "王桂" 
				["unionid"]=> string(27) "L5iiLfhKF2P19VMKRAc4QXAiEiE"
				["dingId"]=> string(35) "$:LWCP_v1:$rjyMmYntDjHkVwmyWjO6KQ=="
				["openid"]=> string(27) "xrKiPtqVMEkJ5Q60Jfn3DAwiEiE" } }
			*/
    if (!isset($_POST['UserErr'])&& isset($_SESSION['unionnick'])){
		
			if(!isset($_POST['CompanyName'])){
				$_POST['CompanyName']='';
				}
				if(!isset($_POST['UserID'])){
				$_POST['UserID']='';
				}
				//if(!isset($_POST['UserName'])){
				$_POST['UserName']=$_SESSION['unionnick']['user_info']['nick'];
				//}
				if(!isset($_POST['PassWord'])){
				$_POST['PassWord']='';
				}
			if(!isset($_POST['Email'])){
				$_POST['Email']='';
			}
			if(!isset($_POST['Phone'])){
				$_POST['Phone']='';
			}
			if(!isset($_POST['Remark'])){
				$_POST['Remark']='';
			}
			var_dump($_SESSION['unionnick']['user_info']['unionid']);
		echo'<span>公司名称:</span><br />
			<span><input type="text" name="CompanyName"   value="'.$_POST['CompanyName'].'" /></span><br />
			<span>登陆ID:</span><br />
			<span><input type="text" name="UserID"   value="'.$_POST['UserID'].'" placeholder="大于4位的数字 字母"  /></span><br />
			<span>操作者姓名:</span><br />
			<input type="text" name="UserName"  maxlength="20"  value="'.$_POST['UserName'].'" placeholder="User name"  readonly /><br />
			<span>密码:</span><br />
			<input type="text"  name="Password"  value="'.$_POST['PassWord'].'"  placeholder="Password" /><br />
			<span>电话:</span><br />
			<span><input type="text" name="Phone"   value="'.$_POST['Phone'].'" /></span><br />
			<span>邮箱:</span><br />
			<span><input type="text" name="Email"    value="'.$_POST['Email'].'" /></span><br /><br />
			<span>备注:</span><br />
			<span><input type="text" name="Remark"    value="'.$_POST['Remark'].'" /></span><br /><br />
			<span><input  type="submit" value="确认" name="Submit" /></span><br />';
	
			
			$unionid=$_SESSION['unionnick']['user_info']['unionid'];
		
			//$comy=mysqli_num_rows($result);
			if (isset($_POST['Submit'])){
			
		
				//secho $sql;
				$result=mysqli_query($db0,$sql);
                if ($result){
				 unset($_SESSION['unionnick']);
				 	echo  '操作员:'.$_POST['UserName'].'操作ID'.$_POST['UserID'].'添加成功!';
				}else{
					echo  '操作员:'.$_POST['UserName'].'操作ID'.$_POST['UserID'].'添加不成功!';	
				}
			}
		//while($row=mysqli_fetch_array($result)){		
	}
}
//else{
	//echo'不存在的临时授权码';
//	echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/index.php">';

echo'</div>
        <div style="text-align:center">
		<span><h2>北京国经纬管理咨询有限公司</h2></span>
		<span><h3>提供云服务、本地部署的<br>管理解决方案和技术支持 </span><br />
		</div>
			</form>
</body>
</html>';
?>
