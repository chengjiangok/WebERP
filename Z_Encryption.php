	<?php
	/* $Id: ZT_Encryption.php  ChengJiang $*/
	/* */
/*
 * @Author: ChengJiang 
 * @Date: 2017-02-16 08:06:21 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2017-12-15 09:09:43
 加密*/
	include ('includes/session.php');
	$Title = 'PHP代码加密';// Screen identification.
	$ViewTopic= 'GeneralLedger';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'ProfitAndLoss';// Anchor's id in the manual's html document.

    include('includes/header.php');

	
	echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
	echo '<form method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
			<input type="hidden" name="selectperiod" value="' . $_POST['selectperiod'] . '" />';

	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<div class="page_help_text">
	功能简介：PHP代码加密；注意：该程序直接压缩源文件，创建新文件在/Demo/Encryption目录下，压缩前请备份源文件！</br>
		
		</div>';
		echo '<table class="selection">
		<tr><td>压缩文件:</td>
   			<td><input type="text" autofocus="autofocus" name="phpfile" maxlength="50" size="50"  value=""  /></td>
		</tr>
		</table>';
	//	
if (isset($_POST['php'])){ 
		
				
			//调用函数 
		if ( $_POST['phpfile']!=''){ 
		$filename = $_POST['phpfile'];  
		encode_file_contents($filename); 
		prnMsg( "OK,加密完成！",'info');
		}else{
			prnMsg('没有输入文件','info');
		} 
		
	
}else if (isset($_POST['demo'])){
		echo '<table class="selection">	
			<th width="110">' . _('Debit') . '</th>
				<th width="210">' . date('Y-m-01',strtotime($_SESSION['lastdate'])) . '</th>';
			echo '<tr><td >1:';		
	
			echo '</td>';
			$url= htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8');
			preg_match('/\/([^\/]+\.[a-z]+)[^\/]*$/',$url,$match); 
			echo $match[1];
			echo '</br>';
			echo basename($url);
			//函数:得到文件名;输出结果为:image01.jpg.
			echo '</br>';
			//	使用 basename($uriString) 我们可以得到一个包含扩展名的文件名；
			
			//	如果不需要扩展名，也可以使用 basename($uriString, $extString) 过滤扩展名，仅仅返回文件名。
			echo __FILE__;//得到当前请求文件的完整路径,输出格式如:/mnt/hgfs/ictsapce/test/index.php
			echo '</br>';
			echo dirname($url);//函数返回路径中的目录部分。如:
			$filename=$url;
			$wrfile=dirname($filename).'/Encryption/'. basename($filename);
			echo $wrfile;
			echo '	</table>';
		
}
		echo '<div class="centre">
		
	
	    	<input type="submit" name="php" value="PHP加密" />
			<input type="submit" name="demo" value="Demo" />	';
			'</div>';
	echo '</div></form>';
	include('includes/footer.php');
function encode_file_contents($filename) {  
		//php文件加密
     $type=strtolower(substr(strrchr($filename,'.'),1));  
     if ('php' == $type && is_file($filename) && is_writable($filename)) {
		  // 如果是PHP文件 并且可写 则进行压缩编码  
         $contents = file_get_contents($filename); // 判断文件是否已经被编码处理  
         $contents = php_strip_whitespace($filename); //  去掉源代码所有注释和空格并显示在一行

         // 去除PHP头部和尾部标识  
         $headerPos = strpos($contents,'<?php');  
         $footerPos = strrpos($contents,'?>');  
         $contents = substr($contents, $headerPos + 5, $footerPos - $headerPos);  
         $encode = base64_encode(gzdeflate($contents)); // 开始编码  
         $encode = '<?php'."\n eval(gzinflate(base64_decode("."'".$encode."'".")));\n\n?>";   
		 $wrfile=dirname($filename).'/Demo/Encryption/'. basename($filename);
         return file_put_contents($wrfile, $encode);  
     }  
     return false;  
 }   
  
  
	?>
