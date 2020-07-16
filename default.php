

<?php
/*
 * @Author: ChengJiang 
 * @Date: 2017-02-16 08:06:21 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2019-04-01 13:51:13
 */
	include ('includes/session.php');
	$Title = '钉钉授权演示';// Screen identification.
	$ViewTopic= 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'ProfitAndLoss';// Anchor's id in the manual's html document.
	include('includes/SQL_CommonFunctions.inc');
    include('includes/header.php');
$Code= $_GET['code'];
//{"errcode":0,"access_token":"275433467068364daeb5228c9b6bbd79","errmsg":"ok"}
$RootPath='erp/';
echo '<p class="page_title_text">
        <img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="" alt="" />' . ' ' . $Title . '</p>';
echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '"> 
       <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo'<table class="selection">';		
    echo'<tr>
            <td>';
    echo $_GET['code'];
            
    echo '</td>
        </tr>';
    echo'<tr>
        <td>得到Access_Token:<br>';  
        $url="https://oapi.dingtalk.com/sns/gettoken?appid=dingoauhh7qo9zz3jffdoy&appsecret=Iq_KW-6_8_5BDRzZrS9vHXtN_TWTGGlbtBrWSYpQPuuMPS4M12-65ldO9AlhnDEZ";
      
        $html = file_get_contents($url);       
        $htmlarr=json_decode($html, true);
        $access_token=$htmlarr['access_token'];
        // 获取授权码;

        $url="https://oapi.dingtalk.com/sns/get_persistent_code?access_token=".  $access_token; 
        $data = array("tmp_auth_code"=>$Code );
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
        $union_open=json_decode($result,true);
        var_dump($union_open);
        //获取用户授权的SNS_TOKEN。以post请求，请求这个地址

        $url="https://oapi.dingtalk.com/sns/get_sns_token?access_token=".$access_token;
        $data = array("openid"=>$union_open['openid'],
            "persistent_code"=>$union_open['persistent_code']
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
        $union_open=json_decode($result,true);
        $sns_token=$union_open['sns_token'];
        $url="https://oapi.dingtalk.com/sns/getuserinfo?sns_token=".$sns_token;
        $html = file_get_contents($url);       
        $htmlarr=json_decode($html, true);
       // $access_token=$htmlarr['access_token'];

        var_dump($htmlarr);

    echo'</td>		
        </tr>';



    echo'<tr>
        <td>3.获取授权码;';

    echo'</td>		
        </tr>';
        echo'<tr>
        <td>以post请求';
      
  

echo'</td>		
        </tr>';

if (isset($_POST['POSTDemo'])){
    echo'<tr>
        <td>以post';
  
echo'</td>
        </tr>';

}
echo'</table>';

echo '<div class="centre">			
        <input type="submit" name="POSTDemo" value="POSTDemo">		
        <input type="submit" name="demo" value="ReadDemo" />
        </div>';        
echo '</form>';
include('includes/footer.php');
function curlPost($url,$data){
    $ch = curl_init();
    $params[CURLOPT_URL] = $url;    //请求url地址
    $params[CURLOPT_HEADER] = FALSE; //是否返回响应头信息
    $params[CURLOPT_SSL_VERIFYPEER] = false;
    $params[CURLOPT_SSL_VERIFYHOST] = false;
    $params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
    $params[CURLOPT_POST] = true;
    $params[CURLOPT_POSTFIELDS] = $data;
    curl_setopt_array($ch, $params); //传入curl参数
    $content = curl_exec($ch); //执行
    curl_close($ch); //关闭连接
    return $content;
}
function curl_file_post_contents($durl, $post_data){
    // header传送格式
    $headers = array(
        "token:1111111111111",
        "over_time:22222222222",
    );
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $durl);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, false);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, true);
    // 设置post请求参数
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    // 添加头信息
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    // CURLINFO_HEADER_OUT选项可以拿到请求头信息
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    // 不验证SSL
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    //执行命令
    $data = curl_exec($curl);
    // 打印请求头信息
//        echo curl_getinfo($curl, CURLINFO_HEADER_OUT);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    return $data;
}
?>
