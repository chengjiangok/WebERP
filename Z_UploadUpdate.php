<?php
	/* $Id: Z_UploadUpdate.php ChengJiang $*/
	/*
 * @Author: ChengJiang 
 * @Date: 2017-09-04 21:05:55 
 * @Last Modified by: ChengJiang
 * @Last Modified time: 2018-07-03 17:29:04
    Release version2017-09-24
 */
 	include ('includes/session.php');
	$Title = '系统升级';// Screen identification.
	$ViewTopic= 'upgrade';// Filename's id in ManualContents.php's TOC.
	$BookMark = 'upgrade';// Anchor's id in the manual's html document.
	include('includes/header.php');	
	include('includes/SQL_CommonFunctions.inc');	
echo '<p class="page_title_text"><img src="'.$RootPath.'/css/'.$Theme.'/images/money_add.png" title="' . _('Search') . '" alt="" />' . ' ' . $Title . '</p>';
echo '<form name="ImportForm" enctype="multipart/form-data" method="post"  action="' . $_SERVER['PHP_SELF'] . '">
       <input type="hidden" name="selectperiod" value="' . $_POST['selectperiod'] . '" />
	   <input type="hidden" name="ImportFormat" value="' . $_POST['ImportFormat'] . '" />
	   <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />
	   <div>';
	//$source=dirname(__FILE__);
	//$RootPath= $RootPath ."/sysupdate/";
	echo '<div class="page_help_text">
	功能简介：WebERP升级上传文件，更新系统，Update上传文件后更新</br>		
		</div>';
	if (!isset($_POST['SelectDate'])){
		$lastweek_end =date('Y-01-01', strtotime(date("Y-m-d")));
		$_POST['SelectDate']=$lastweek_end.'T00:00:00';
	}
	echo '<table class="selection">	
		<tr>
		<td>选择目录</td>
		<td>';
	$filesdir=array('./','./includes/','./includes/tcpdf/','./sql/sqlerp/','./companies/');
	echo '<select name="dirname">';
	
	foreach ($filesdir as  $val) {
		if ($val == $_POST['dirname']) {
			echo '<option value="' . $val . '" selected="selected">' . $val . '</option>';
		} else {
			echo '<option value="' . $val . '">' . $val . '</option>';
		}
		//$ListPage++;min="'.date('Y-01-01 0:0:0').'" max="'.date('Y-m-d H:i:s').'" 
	}
	
	echo '</select>
			</td>
			</tr>
			<tr>
				<td>选择日期时间</td>
				<td><input type="datetime-local" name="SelectDate" min="'.date('Y-01-01 0:0:0').'" max="'.date('Y-m-d H:i:s').'"  value="'.$_POST['SelectDate'].'"/></td>
			</tr>
			<tr>
				<td>上传文件</td>
				<td><input type="file" id="ImportFile"  title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile">
				</td>
			</tr>';
	echo '</table>';	
	echo '<div class="centre">
			<input type="submit" name="Search" value="查询" />
			<input type="submit" name="FilesData" value="资料更新" />
			<input type="submit" name="UploadUpdate" value="上传升级" />';
	    if (isset($_POST['FilesData'])){
			echo '<br /><br/><input type="submit" name="Updateing" value="上传保存" />';
		}
			echo'</div>';
	$uploadpath='sysupdate/upload/';


	$Dbase="erp_gjw";
	$dir =$_POST['dirname'];//目录	 
    $db1 = mysqli_connect($host , $DBUser, $DBPassword, $Dbase, $mysqlport);
		   mysqli_set_charset($db1, 'utf8');
		
	$SQL="SELECT `filename`, `filetype`, `filesize`, `version`, `createdate`, `updatedate`, `flag`, `notes` 
		   FROM `filesdata` 
		   WHERE dirname='".$dir."'";
	
   $filesname=array();
   $Result= mysqli_query($db1,$SQL); 
   while ($row = mysqli_fetch_array($Result)){
	   $filesname[$row['filename']]=array($row['updatedate']);
   }
  // var_dump($filesname);
/*	
if (isset($_POST['upload'])) {
	 // prnMsg($_POST['dirname']);
	  
		$file_size=$_FILES['ImportFile']['size'];  
		if($file_size>2*1024*1024) {  
			prnMsg("文件过大，不能上传大于2M的文件",'info');  
				include('includes/footer.php');
				exit;
				$ReadTheFile ='No';
		 
		}  
	  
		$file_type=$_FILES['ImportFile']['type'];  
	
		if($file_type=="application/octet-stream") {
		   $file_=1;
		}elseif($file_type=='application/javascript'){
			
		   $file_=1;    
		}	
		//prnMsg( $file_.'='.$file_type,'info');    
		   if ($file_!=1){	 
			prnMsg($file_type."文件类型只能为php、inc、js",'info');   
				include('includes/footer.php');
				exit;
				$ReadTheFile ='No';
		} 
	   //判断是否上传成功（是否使用post方式上传）  
		if(is_uploaded_file($_FILES['ImportFile']['tmp_name'])) {  
			//把文件转存到你希望的目录（不要使用copy函数）  
			$uploaded_file=$_FILES['ImportFile']['tmp_name'];  
			
			$file_name=$_FILES['ImportFile']['name'];
			//prnMsg( $_FILES['ImportFile']['name'].'debug'.$uploadpath.$file_name,'info');  
			 	
			if(move_uploaded_file($uploaded_file,'./'.$file_name)) { 
			   prnMsg( $_FILES['ImportFile']['name']."上传成功",'info');  
			} else {  
			   prnMsg("上传失败!",'info');  
			}  
		} else {  
			  prnMsg("上传失败!!",'info');  
		} 		
	   
		
}else*/
if (isset($_POST['UploadUpdate'])) {
	$UpdateDir=$_POST['dirname'];
	  
	$file_size=$_FILES['ImportFile']['size'];  
	if($file_size>2*1024*1024) {  
		prnMsg("文件过大，不能上传大于2M的文件",'info');  
			include('includes/footer.php');
			exit;
			$ReadTheFile ='No';
	 
	}  
  
	$file_type=$_FILES['ImportFile']['type'];  

	if($file_type=="application/octet-stream") {
	   $file_=1;
	}elseif($file_type=='application/javascript'){
		
	   $file_=1;    
	}	
	//prnMsg( $file_.'='.$file_type,'info');    
	   if ($file_!=1){	 
		prnMsg($file_type."文件类型只能为php、inc、js",'info');   
			include('includes/footer.php');
			exit;
			$ReadTheFile ='No';
	} 
   //判断是否上传成功（是否使用post方式上传）  
	if(is_uploaded_file($_FILES['ImportFile']['tmp_name'])) {  
		//把文件转存到你希望的目录（不要使用copy函数）  
		$uploaded_file=$_FILES['ImportFile']['tmp_name'];  
		//判断该用户文件夹是否已经有这个文件夹  
		/*if(!file_exists($user_path)) {  
			mkdir($user_path);  
		} */	  
		$file_name=$_FILES['ImportFile']['name'];
		//prnMsg( $_FILES['ImportFile']['name'].'debug'.$uploadpath.$file_name,'info');  
			 
		if(move_uploaded_file($uploaded_file,$UpdateDir.$file_name)) { 
		   prnMsg( $_FILES['ImportFile']['name']."上传目录成功",'info');  
		} else {  
		   prnMsg("上传失败!",'info');  
		}  
	} else {  
		  prnMsg("上传失败!!",'info');  
	} 	

	

   
	
}elseif (isset($_POST['FilesData'])){

		echo '<table class="selection">';
		$header='<tr>
			<th colspan="8" height="2">'.$_POST['dirname'].'</th>
			</tr>
			<tr>
				<th>序号</th>
				<th>目录名</th>
				<th>文件名</th>
				<th>文件大小</th>
				<th>日期时间</th>
				<th ></th>
								
			</tr>';	
		
		$f=0;	
	
			if (is_dir($dir)) {
			
				if ($dh = opendir($dir)) {
					$i = 0;
					unset($file);
					while (($file = readdir($dh)) !== false) {
					//	prnMsg(filemtime('./'.$dir.$file).'='.filectime('./'.$dir.$file).'-'.fileatime($file));
						if ($file != "." && $file != "..") {
							$files[$i]["name"] = $file;//获取文件名称
							$files[$i]["size"] = round((filesize('./'.$dir.$file)/1024),2);//获取文件大小
							$files[$i]["time"] = date("Y-m-d H:i:s",filemtime('./'.$dir.$file));//获取文件最近修改日期
							$i++;
						}
					}
				}
				closedir($dh);
				//var_dump($files);
				foreach($files as $k=>$v){
					$size[$k] = $v['size'];
					$time[$k] = $v['time'];
					$name[$k] = $v['name'];
				}
				array_multisort($time,SORT_DESC,SORT_STRING, $files);//按时间排序
				//array_multisort($name,SORT_DESC,SORT_STRING, $files);//按名字排序
				//array_multisort($size,SORT_DESC,SORT_NUMERIC, $files);//按大小排序
				//print_r($files);
			//	$f=0;
			$d=0;
			echo $header;
			foreach($files as $key=>$row){		
							
				if (!isset($filesname[$row['name']])||(isset($filesname[$row['name']])&&(strtotime($filesname[$row['name']][0])>strtotime($row['time'])))){
				   
					if (file_exists($dir.$row['name'])){
 
						if ($k==1){
							echo '<tr class="EvenTableRows">';
							$k=0;
						} else {
							echo '<tr class="OddTableRows">';
							$k=1;
						}
							echo '
							<td>'.$f.'</td>	
							<td>'. $dir. '</td>
							<td>'. $row['name']. '</td>
							<td>'. $row['size']. '</td>
							<td>'. $row['time']. '</td>
							<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete='.$_POST['dirname'].'/'.$row['name']. '">' . _('Delete') . '</a></td>
		
							</tr>';
						
						$f++;
					}
				}
				$d++;
			}//end for
			
			if ($f>0){
				prnMsg("$dir 目录下共计".($f)." 目录,".$f."文件",'info');
			}
		}
		echo'</div></table>';
		/*
			mysqli_query($db1,'SET autocommit=0');
		mysqli_query($db1,'START TRANSACTION');	
			foreach($files as $key=>$row){
				
				$sql = "INSERT INTO `filesdata`(
												`dirname`,
												`filename`,
												`filetype`,
												`filesize`,
												`version`,
												`createdate`,
												`updatedate`,
												`flag`,
												`notes`
											)
											VALUES ('".$dir."',
															'".$row['name']."',
															'0',
															'".$row['size']."',
															'1',
															'".$row['time']."',
															'".$row['time']."',
															0,
															'')";	
																
				$query = mysqli_query($db1,$sql); 					
							
					
					$f++;
		
		
			}//end for
			mysqli_query($db1,'commit');
			mysqli_query($db1,'SET autocommit=1');	
			if ($f>0){
				prnMsg("$dir 目录下共计".($f)." 目录,".$f."文件",'info');
			}
		}*/
	//
						
}elseif(isset($_POST['Search'])){
		

	echo '<table class="selection">';
	echo'<tr>
		<th colspan="8" height="2">'.$_POST['dirname'].'</th>
		</tr>
		<tr>
			<th>序号</th>
			<th>文件名</th>
			<th>文件大小</th>
			<th>日期时间</th>
			<th ></th>
							
		</tr>';	
		if (is_dir($dir)) {
			//prnMsg('240'.$dir);
			if ($dh = opendir('./'.$dir)) {
				$i = 0;
				while (($file = readdir($dh)) !== false) {
					if ($file != "." && $file != "..") {
						$files[$i]["name"] = $file;//获取文件名称
						$files[$i]["size"] = round((filesize('./'.$dir.$file)/1024),2);//获取文件大小
						$files[$i]["time"] = date("Y-m-d H:i:s",filemtime('./'.$dir.$file));//获取文件最近修改日期
						$i++;
					}
				}
			}
			closedir($dh);
		
			foreach($files as $k=>$v){
				$size[$k] = $v['size'];
				$time[$k] = $v['time'];
				$name[$k] = $v['name'];
			}
			array_multisort($time,SORT_DESC,SORT_STRING, $files);//按时间排序
			//array_multisort($name,SORT_DESC,SORT_STRING, $files);//按名字排序
			//array_multisort($size,SORT_DESC,SORT_NUMERIC, $files);//按大小排序
			//print_r($files);
		$f=0;
		$d=0;
		foreach($files as $key=>$row){
		
			//if (!isset($filesname[$row['name']])||(isset($filesname[$row['name']])&&(strtotime($filesname[$row['name']][0])>strtotime($row['time'])))){

			if (strtotime($_POST['SelectDate'])<=strtotime($row['time'])){
				if (file_exists($dir.$row['name'])){
					if ($k==1){
						echo '<tr class="EvenTableRows">';
						$k=0;
					} else {
						echo '<tr class="OddTableRows">';
						$k=1;
					}
						echo '<td>'.$key.'</td>	
							<td>'. $row['name']. '</td>
							<td>'. $row['size']. '</td>
							<td>'. $row['time']. '</td>
							<td><a href="'. htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '?Delete='.$_POST['dirname'].'/'.$row['name']. '">' . _('Delete') . '</a></td>

							</tr>';
				
					$f++;
				}
			}
			$d++;
		}//end for
        if ($i>0){
			prnMsg("$dir 目录下共计".($d-$f)." 目录,".$f."文件",'info');
		}
	}

	echo'</div></table>';
					

}elseif(isset($_GET['Delete'])){
	

	$file =$_GET['Delete'];
	if (!unlink($file))
	{
	  prnMsg ("Error deleting $file",'info');
	}
	else
	{
	prnMsg ("Deleted $file",'info');
	}
}	
	echo '</form>';
	include('includes/footer.php');
	function read_all ($dir){
		     if(!is_dir($dir)) return false;		     
		    $handle = opendir($dir);
		 
             if($handle){
		         while(($fl = readdir($handle)) !== false){
		             $temp = $dir.DIRECTORY_SEPARATOR.$fl;
		             //如果不加  $fl!='.' && $fl != '..'  则会造成把$dir的父级目录也读取出来
		             if(is_dir($temp) && $fl!='.' && $fl != '..'){
		                 echo '目录：'.str_replace($dir,'',$temp).'<br>';
		                 read_all($temp);
					 }
					 /*else{
		                 if($fl!='.' && $fl != '..'){
		 
		                     echo '文件：'.str_replace($dir,'',$temp).'<br>';
		                 }
		             }*/
		         }
		     }
		 }
	function getip_out(){ 
		$ip=false; 
		if(!empty($_SERVER["HTTP_CLIENT_IP"])){ 
		$ip = $_SERVER["HTTP_CLIENT_IP"]; 
		} 
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) { 
		$ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']); 
		if ($ip) { array_unshift($ips, $ip); $ip = FALSE; } 
		for ($i = 0; $i < count($ips); $i++) { 
		if (!eregi ("^(10│172.16│192.168).", $ips[$i])) { 
		$ip = $ips[$i]; 
		break; 
		} 
		} 
		} 
		return ($ip ? $ip : $_SERVER['REMOTE_ADDR']); 
	} 
	
	?>
