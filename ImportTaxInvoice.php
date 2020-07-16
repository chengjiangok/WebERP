<?php
/*
 
 * @Descripttion: WebERP开发升级
 * @version: 202003
 * @Author: ChengJiang
 * @Date: 2020-02-18 20:16:58
 * @LastEditors: ChengJiang
 * @LastEditTime: 2020-05-12 16:40:02
 */

	
require_once __DIR__ . '/PhpOffice/PhpSpreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

  include ('includes/session.php');
  //include ('includes/FunctionsAccount.php');放弃20200319
  include ('includes/ExcelFunction.php');
  include ('includes/GLAccountFunction.php');

  $Title = '发票文件上传';
  $ViewTopic = 'ImportFile';// Filename's id in ManualContents.php's TOC.
  $BookMark = 'ImportTaxInvoice';
	include('includes/SQL_CommonFunctions.inc');
  include('includes/header.php');
  define('INV_TXT', 0);
  define('INV_SAVE', 1);
	if (!isset($_POST['Dbase'])){
    $_POST['Dbase']=$_SESSION['DatabaseName'] ;	 		
	}
echo'<script type="text/javascript">
  function OnImportFile(ths){
    if (ths.value == "") {    
          alert("请上传文件");    
          return false;    
      } else {    
          if (!/\.(xls|xml|txt|zip|xlsx)$/.test(ths.value)) {    
              alert("文件类型必须是.xls,xlsx,txt,xml中的一种");    
              ths.value = "";    
              return false;    
      }   
      var readflg=document.getElementById("ReadTab").value;		
      //alert(readflg);
      if (parseInt(readflg)>=100){//调试改100原始为1
        alert("有未读入的文件,请先处理,再上传!");
        ths.value = "";   
        return false;
      }
      }    
      return true;    
  }
</script>';
if(isset($_POST['Clear'])){
  unset($_SESSION['InvFile']);
  
  echo '<meta http-equiv="Refresh" content="1"; url=' . $RootPath . '/ImportTaxInvice.php">';
 
}/*elseif(isset($_POST['DemoMsg'])){
 //调试专用
 //prnMsg((intval(($_SESSION['period']-$_SESSION['janr']+1)/3)).'='.(($_SESSION['period']-$_SESSION['janr'])%3).'-'.$_SESSION['janr']);
  $jdm=($_SESSION['period']-$_SESSION['janr'])%3;
  echo '<meta http-equiv="Refresh" content="3"; url=' . $RootPath . '/ImportTaxInvice.php">';
  //  echo   implode(',',$CustNew);
}*/
  $yeardir=dechex(date("Y",strtotime($_SESSION['lastdate'])));
  $path = 'companies/'.$_SESSION['DatabaseName']."/TaxInvoice/";
  $filepath =$path.$yeardir."/";
  $tag=1;
  $impft=array(0=>'进项发票',1=>'销项专票',3=>'销项普票');
  //<---------检测件夹中的文件是否在数据invupload中,如果没有插入表中---------->
  if (is_dir($filepath)){//判断目录是否存在
    $FilesZip  =dirfiles( getcwd().'/'.$filepath.'FilesZip/');
    $FilesInv  =dirfiles( getcwd().'/'.$filepath.'FilesInv/');
  }else{
    //创建目录
    prnMsg($filepath."创建目录");

    mkdir ($filepath.'/FilesZip',0777,true);
    mkdir ($filepath.'/FilesInv',0777,true);
  }
  //<--------检测文件夹结束-检测表中文件状态，文件记录检测，如果有没有写入的记录读入SESSION['InvFile'],如果已经写入重新标记--->	
//自动检测读取没写入的表
$SQL="SELECT `uploadid`, `invtype`, `ziptype`, `filesinv`, `fileszip`,filepath,`flag`
       FROM `invupload` 
       WHERE  flag=0 AND  filepath='".$yeardir."'
       ORDER BY ziptype";

$TableResult=DB_query($SQL); 

if (DB_num_rows($TableResult)>0  ){	
  
  $RowIndex=0;
  while ($row= DB_fetch_array($TableResult)) {

    //测文件是否存在 是否已经读入
    if (!isset($_SESSION['InvFile'])){
     
      if ($row['flag']==0){
       
        $_SESSION['InvFile'][0]=0; 
        $fm=explode(",",$row['filesinv']);
        if (count($fm)>1){
          foreach($fm as $val){
            if (strpos($val,"_V10")!==false){
              $fname=$val;
              break;
            }
          }
        }else{
          $fname=$fm[0];
        }
        $_SESSION['InvFile'][1]=$fname;     
        $_SESSION['InvFile'][2]=$row['uploaddate'];
        $_SESSION['InvFile'][3]=$row['uploadid'];
        $_SESSION['InvFile'][4]=$row['filepath'];
        $_SESSION['InvFile'][5]=$row['invtype'];//$InvType;
        $_SESSION['InvFile'][6]=$row['ziptype'];//ziptype
        $_SESSION['InvFile'][7]=$_SESSION['CompanyRecord'][1]['companynumber'];//注册码      
        $_SESSION['InvFile'][8]=$row['fileszip'];
        break;
     }
    }
          
  }//endwhile
} 
echo '<p class="page_title_text"><img alt="" src="'.$RootPath.'/css/'.$Theme.'/images/bank.png" title="' .// Icon image.
            $Title.'" /> ' .$Title . '</p>';// Page title.
echo '<form name="ImportForm" enctype="multipart/form-data" method="post" action="' . htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') . '">
      <input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo'<table class="selection">';			
  echo '<tr>
            <td>上传文件</td>
            <td><input type="file" id="ImportFile"    title="' . _('Select the file that contains the bank transactions in MT940 format') . '" name="ImportFile" onchange="OnImportFile(this)" > </td>';
  echo'</tr>
        </table><br>';
  if (isset($_GET['Del']) ){
    $msg.=$_GET['Del']."删除文件功能没有打开！<br>"; 
    /*
    $sql="SELECT `uploadid`, `account`, `filename`, `filepath`,  `uploaddate`, `balance`, `remark`, `flag` 
    FROM `bankupload` 
    WHERE flag=0 AND   filepath='".$yeardir."'";
    if (unlink(	$filepath .$_GET['Del'])){
    
      $SQL="DELETE FROM `bankupload` WHERE flag =0 AND filename='".$_GET['Del']."'";
      $Result=DB_query($SQL);
    }else{
      $SQL="UPDATE `bankupload` SET flag=2 WHERE   filename='".$_GET['Del']."'";
      $Result=DB_query($SQL);
    }*/
  }
  if (isset($_GET['read']) ){	
    $postfix=substr($_GET['read'],strrpos($$_GET['read'],'.')+1 );
    //echo '-='.$postfix;
    if ($postfix=='zip'){
      $SQL="SELECT `uploadid`, `invtype`, `ziptype`, `filesinv`, `fileszip`, `filepath`, `uploaddate`,  `tag`, `flag` 
          FROM `invupload` 
          WHERE fileszip='".$_GET['read']."'  AND uploaddate>='".date("Y-m-d",$_GET['time'])."' LIMIT 1";
           $InvResult=DB_query($SQL); 
           $InvRow=Db_fetch_assoc($InvResult);
           if (!empty($InvRow)){
            $files=explode(",",$InvRow['filesinv']);
            if (count($files)==1){
                $filesinv=$fiels[0];
            }else{
                foreach($files as $val){
                  if (strpos($val,"_V10")!==false){
                    $filesinv=$val;
                    break;
                  }
                }
            }
            if (isset($_SESSION['InvFile']))
            unset($_SESSION['InvFile']);
            $_SESSION['InvFile'][1]=$filesinv; 
            $_SESSION['InvFile'][0]=1;      
            $_SESSION['InvFile'][2]=$InvRow['uploaddate'];
            $_SESSION['InvFile'][3]=$InvRow['uploadid'];
            $_SESSION['InvFile'][4]=$InvRow['filepath'];
            $_SESSION['InvFile'][5]=$InvRow['invtype'];//$InvType;
            $_SESSION['InvFile'][6]=$InvRow['ziptype'];//ziptype
            $_SESSION['InvFile'][7]=$InvRow['registerno'];//注册码      
            $_SESSION['InvFile'][8]=$InvRow['fileszip'];
          }
      
    }else{
      $SQL="SELECT `uploadid`, `invtype`, `ziptype`, `filesinv`, `fileszip`, `filepath`, `uploaddate`,  `tag`, `flag` 
            FROM `invupload` 
            WHERE filesinv='".$_GET['read']."'  AND uploaddate>='".date("Y-m-d",$_GET['time'])."' LIMIT 1";
           // echo '-='.$SQL;
       if (isset($_SESSION['InvFile']))
       unset($_SESSION['InvFile']);
      $InvResult=DB_query($SQL); 
      $InvRow=Db_fetch_assoc($InvResult);
      if (!empty($InvRow)){
        $_SESSION['InvFile'][0]=1;
    
        $_SESSION['InvFile'][1]=$InvRow['filesinv'];     
        $_SESSION['InvFile'][2]=$InvRow['uploaddate'];
        $_SESSION['InvFile'][3]=$InvRow['uploadid'];
        $_SESSION['InvFile'][4]=$InvRow['filepath'];
        $_SESSION['InvFile'][5]=$InvRow['invtype'];//$InvType;
        $_SESSION['InvFile'][6]=$InvRow['ziptype'];//ziptype
        $_SESSION['InvFile'][7]=$InvRow['registerno'];//注册码      
        $_SESSION['InvFile'][8]=$InvRow['fileszip'];
      }
    }
    //echo '读取文件'.$InvRow['uploadid'].$InvRow['filesinv'];
   
  }
  if ($_GET['invdate']){
    if (count($FilesInv)>0){
      $InvTime=strtotime($_GET['invdate']); 
      
      echo '<table cellpadding="2" class="selection">
          <tr>
            <th >序号</th>							
            <th >文件名称</th>	        
            <th >未读取文件名</th>
            <th >上传日期</th>
            <th ></th>
          </tr>'; 	
    
      $RowIndex=0;
      //while ($row= DB_fetch_array($result)) {
      foreach($FilesInv as $key=>$row)   {
        if ($row['time']>=$InvTime){
          if ($k==1){
            echo '<tr class="EvenTableRows">';
            $k=0;
          } else {
            echo '<tr class="OddTableRows">';
            $k=1;
          }	
          $RowIndex++;				
          echo'<td>'.$RowIndex.'</td>
              <td>'.$key.'</td>           
              <td >'.date("Y-m-d",$row['time'])."</td>
              <td ><a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?read=".$key."&time=".$row['time']."\" >读入</a>&nbsp&nbsp
              <a href=\"".htmlspecialchars($_SERVER['PHP_SELF'],ENT_QUOTES,'UTF-8') ."?Del=".$key."\" onclick=\"return confirm('你确认要删除该文件吗!');\" >" . _('Delete') . "</a></td>";
          
            echo"</tr>";
        }
              
      }
      echo'<input type="hidden" name="ReadTab" id="ReadTab"  value="'.$RowIndex.'" > ';
      echo '</table>';
    }
  }
  $SQL="SELECT `invtype`, `period`, SUM(`amount`) amounttotal, SUM(`tax`) taxtotal,count(*) counter
         FROM `invoicetrans`
         WHERE period>=".$_SESSION['janr']." AND period<=".$_SESSION['period']."
         GROUP BY period,invtype";
  $Result=DB_query($SQL);
   //汇总已经读入的发票内容
if (DB_num_rows($Result)>0  ){	
     $invtype=array(0=>"进项票",1=>"销专票",3=>"销普通票");
  echo '<table cellpadding="2" class="selection">
          <tr>
          <th class="ascending">序号</th>	
          <th class="ascending">会计期间</th>	        			
          <th class="ascending">发票类别</th>
          <th class="ascending">金额</th>
          <th class="ascending">税金</th>				
          <th class="ascending">合计</th>        
          <th class="ascending">发票分数</th>		
        </tr>';	
        $jdm=($_SESSION['period']-$_SESSION['janr'])%3;
        if ($jdm==0){
          $periodjd=(intval(($_SESSION['period']-$_SESSION['janr'])/3)-1)*3+$_SESSION['janr'];
      
        }else{
         $periodjd=intval(($_SESSION['period']-$_SESSION['janr']+1)/3)*3+$_SESSION['janr'];
        }
    while ($row= DB_fetch_array($Result)) {
        $YearAmo[$row['invtype']]+=$row['amounttotal'];
        $YearTax[$row['invtype']]+=$row['taxtotal'];
        $YearCounter[$row['invtype']]+=$row['counter'];
      if ($row['period']>=$periodjd-3)	{	
        $QuarterAmo[$row['invtype']]+=$row['amounttotal'];
        $QuarterTax[$row['invtype']]+=$row['taxtotal'];
        $QuarterCounter[$row['invtype']]+=$row['counter'];
        if ($k==1){
          echo '<tr class="EvenTableRows">';
          $k=0;
        } else {
          echo '<tr class="OddTableRows">';
          $k=1;
        }	         
        $RowIndex++;
        echo'<td>'.$RowIndex.'</td>   
            <td >'.substr(PeriodGetDate($row['period']),0,7).'</td>    
            <td >'.$invtype[$row['invtype']].'</td>
            <td >'.locale_number_format($row['amounttotal'],POI).'</td>
            <td >'.locale_number_format($row['taxtotal'],POI).'</td>
            <td >'.locale_number_format(($row['amounttotal']+ $row['taxtotal']),POI).'</td>
            <td >'.$row['counter'].'</td>     
            </tr>';
      }
    }
    foreach ($invtype as $key=>$val){
    echo'<tr style="background: #ece;">
          <td></td>   
          <td colspan="2">'.$val.'季度合计</td>    
          
          <td >'.locale_number_format($QuarterAmo[$key],POI).'</td>
          <td >'.locale_number_format($QuarterTax[$key],POI).'</td>
          <td >'.locale_number_format(($QuarterAmo[$key]+$QuarterTax[$key]),POI).'</td>
          <td >'.$QuarterCounter[$key].'</td>
          </tr>';
    }
    foreach ($invtype as $key=>$val){
      echo'<tr style="background: #eec;">
            <td></td>   
            <td colspan="2">'.$val.'年度合计</td>    
            
            <td >'.locale_number_format($YearAmo[$key],POI).'</td>
            <td >'.locale_number_format($YearTax[$key],POI).'</td>
            <td >'.locale_number_format(($YearAmo[$key]+$YearTax[$key]),POI).'</td>
            <td >'.$YearCounter[$key].'</td>
            </tr>';
      }
     
        echo '</table>';	
}  
unset($Result);
echo'<input type="hidden" name="ReadTab" id="ReadTab"  value="'.$RowIndex.'" > '; 
  //上传代码  
if(isset($_POST['Upload'])){
      //获取表单交的压缩文件
      $file = $_FILES['ImportFile'];
      //获取文件名
      $filename = $_FILES['ImportFile']['name']; 
       
      //定义文件保存路径
      $checkfile=true;
      $zipfile=true;//压缩文件检���标记
      $filesinv=true;
      $uploadtab=false;
      $invfile=true;//文件检查标记
      $unzipfiles=true; 

  if (isset($filename) && $filename!=''){//判断是否选上传文件
      
    $file_type=$_FILES['ImportFile']['type'];  
        //   application/x-zip-compressed
       //得到上传文件后缀
    $filepostfix=substr($filename,strrpos($filename,'.') );  
    
    $f=0;	   
    $filename=getfilename($filename);//得到标准文件名
    //prnMsg('上传文件-'.$filename.'，<br>-'.$filedir.'<br>;'.$file_type.'<br>'.$postfix);//调试
    //根据文件名判断上传文件类型
    $TaxDiscType=getinvtype($filename);
    if ($TaxDiscType<0){
      prnMsg($filename."不合乎上传文件格式！","warn");
      $checkfile=false;
    }

    if ($checkfile){//文件格式检查
      //prnMsg($filepostfix.' 262 ');
      if ($filepostfix=='.zip'){//判断是否压缩文件
        $filedir = getcwd().'/'.$filepath.'FilesZip/';
        ////读取目录下文��得到以文件名为键的数组
        $files=dirfiles($filedir);
        if (is_array($files)){       
            if (isset($files[$filename])){
              $zipfile=false;
            }
        }      
          //使PHP函数移动文件
        if ($zipfile){
          //没有存��压缩文件，上传文件改名
       
          $uploadtab = move_uploaded_file($_FILES['ImportFile']['tmp_name'],$filedir.$filename);
        }else{
          //删除上传文件
          prnMsg($filename."文件已经上传过！",'info');
          //$filesinv=false; 
        }
        //读取压缩文件里的文件名
        if ($uploadtab){
        
          $FilesInv=dirfiles( getcwd().'/'.$filepath.'FilesInv/');
        
           //解压文件夹中存在待解压文件
          //if (is_array($FilesInv)){//、、？？？
         
            $zip  =  zip_open ($filepath.'FilesZip/'.$filename);
            $fnamestr='';
            if ( $zip ) {
                while ( $zip_entry  =  zip_read ( $zip )) {
                  $zipname= zip_entry_name ($zip_entry );  
                  $FnameInv[]=  $zipname;              
               
                    zip_entry_close($zip_entry);
                }
                zip_close ( $zip );
            }           
        
          foreach($FnameInv  as $val){
            
            if (isset($FilesInv[$val])){
              //文件已经存在
                // if ($_SESSION['CompanyRecord'][1]['taxtype']==1){
                //税控盘
                if (strpos($val,"客户编码")!==false){
                  //客户编码文件上传间隔小于15天不能上传
                  if (round((strtotime(date('Y-m-d'))-$FilesInv[$val]['time'])/86400,2)<15){          
                
                    $unzipfiles=false;
                    break;
                  }
                }
            }
          }
        
          if ($unzipfiles && $zipfile){
            $fnamestr=implode(",",$FnameInv);
              //prnMsg("//已上传文件，没有重复解压文件执行");
              $zip = new ZipArchive();//实例化ZipArchive类
              //打开压缩文件，打开成功时返回true
              if ($zip->open($filedir.$filename ) === true) {
                  //解压文件到获得的路径a文件夹下
                  $zip->extractTo($filepath.'FilesInv/');                       
                  $zip->close(); //关闭
                  //插入数据表
                  $dt=date("Y-m-d h:i:s");
                  $SQL="INSERT INTO `invupload`(`invtype`,
                                                `ziptype`,
                                                `filesinv`,
                                                `fileszip`,
                                                filepath,
                                                `uploaddate`,
                                                `registerno`,
                                                period,
                                                `tag`,
                                                `counter`,                                            
                                                `remark`,
                                                `flag`)
                                            VALUES('".$TaxDiscType."',
                                                    'zip',
                                                    '".$fnamestr."',
                                                    '".$filename."',
                                                    '".$yeardir."',
                                                    '".$dt."',
                                                    '".$_SESSION['CompanyRecord'][1]['companynumber']."',
                                                    '',
                                                    '1',
                                                    '0',                                            
                                                    '',
                                                    '0')";
                $result=DB_query($SQL);
                $uploadid=DB_Last_Insert_ID($db,'invupload','uploadid');
              if ($result){
                $_SESSION['InvFile'][0]=0;
             
                $fm=explode(",",$fnamestr);
                if (count($fm)>1){
                  foreach($fm as $val){
                    if (strpos($val,"_V10")!==false){
                      $fname=$val;
                      break;
                    }
                  }

                }else{
                  $fname=$fm[0];
                }
               
                $_SESSION['InvFile'][1]=$fname;                 
                $_SESSION['InvFile'][2]=$dt;
                $_SESSION['InvFile'][3]= $uploadid;//id
                $_SESSION['InvFile'][4]=$yeardir;
                $_SESSION['InvFile'][5]=$TaxDiscType;
                $_SESSION['InvFile'][6]='zip';
                $_SESSION['InvFile'][7]=$_SESSION['CompanyRecord'][1]['companynumber'];
                $_SESSION['InvFile'][8]=$fileName;
              //     $_SESSION['InvFile'][]=$filepath;               
                
                prnMsg($filename.'上传成功！','info');
              } else {
                prnMsg($filename."文件解压失败，请通知系统管理员！",'warn');
              }
            } else{
              //压缩文件中的文件已经 上传，删除压缩文件';
              
              //if (unlink("C:\Wnmp\html\gjwerp/companies/gjw_erp/TaxInvZip/7e3/zip/91371000MA3MW0G920_201912A_dk.zip")){//
              if(unlink($filedir.$filename )){
                prnMsg($filename."文件已经删除！",'info');  
                $uploadtab=false;
               
                prnMsg($filedir.$filename."文件删除失败，请通知系统管理员！",'warn');
                echo '<meta http-equiv="Refresh" content="1;"/>';
              }
            }
          }else{
            //文件存在  删除上传压缩文件
            if (unlink($filedir.$filename)){
              prnMsg($filename.'文件删除成功!','info');
              unset($_SESSION['InvFile']);
              echo '<meta http-equiv="Refresh" content="1;"/>';
            }				
          }
        }
      }else{ 
         //没有 缩的文件
          $filedir = getcwd().'/'.$filepath.'FilesInv/';
          $filesunzip=dirfiles( getcwd().'/'.$filepath.'FilesInv/');
          $filesinv=dirfiles($filedir);//读取UploasInv目录下文件
          //prnMsg('//检测发 是否存在 检测解压后和上传无解压文');
          if (is_array($filesinv)){        
              if (isset($filesinv[$filename])){
                 //税控盘
                 if (strpos($filename,"客户编码")!==false){
                  //客户编码文件上传间隔小于15天不能上传
                  if (round((strtotime(date('Y-m-d'))-$FilesInv[$filename]['time'])/86400,2)<15){          
                     prnMsg(date("Y-m-d",$FilesInv[$filename]['time']).'-'.round((strtotime(date('Y-m-d'))-$FilesInv[$filename]['time'])/86400,2));
                    $invfile=false;   
                   
                  }
                }
                        
              }       
          }
          /*
          if (is_array($filesunzip)){        
            if (isset($filesunzip[$filename])){
              prnMsg('428unzipfile');
              $unzipfiles=false;           
            }       
          }*/
           //prnMsg($invfile.'[-]'. $unzipfiles);
          if ($invfile && $unzipfiles){
            $uploadtab = move_uploaded_file($_FILES['ImportFile']['tmp_name'],$filedir.$filename);
            $postfix=substr($filename,strrpos($filename,'.')+1 );
            //插入invupload

            $SQL="INSERT INTO `invupload`(`invtype`,
                                              `ziptype`,
                                              `filesinv`,
                                              `fileszip`,
                                              filepath,
                                              `uploaddate`,
                                              `registerno`,
                                              period,
                                              `tag`,
                                              `counter`,                                            
                                              `remark`,
                                              `flag`)
                                          VALUES('".$TaxDiscType."',
                                                  '".$postfix."',
                                                  '".$filename."',
                                                  '".$filename."',
                                                  '".$yeardir."',
                                                  '".date("Y-m-d h:i:s")."',
                                                  '".$_SESSION['CompanyRecord'][1]['companynumber']."',
                                                  '',
                                                  '1',
                                                  '0',                                               
                                                  '',
                                                  '0')";
              $result=DB_query($SQL);
              $uploadid=DB_Last_Insert_ID($db,'invupload','uploadid');
              if ($result){
                $_SESSION['InvFile'][0]=0;
                $_SESSION['InvFile'][1]=$filename;  
                            
                $_SESSION['InvFile'][2]=date("Y-m-d h:i:s");
                $_SESSION['InvFile'][3]=$uploadid;//id
                $_SESSION['InvFile'][4]=$yeardir;
                $_SESSION['InvFile'][5]=$TaxDiscType;
                $_SESSION['InvFile'][6]=$postfix;
                $_SESSION['InvFile'][7]=$_SESSION['CompanyRecord'][1]['companynumber'];
                $_SESSION['InvFile'][8]=$fileName;
              //     $_SESSION['InvFile'][]=$filepath;
                
                
                
                prnMsg($filename.'上传成功！','info');
              }
          }else{
                        
              prnMsg($filename." =文件已经上传过！",'info');
          }
        
      }
    }
  
  } else{
    prnMsg('你没有选择上传文件','info');
  } 
   
}

echo '<div class="centre">
        <input type="submit" name="Upload" value="文件上传" /> ';
if (isset($_SESSION['InvFile'])){
  echo '<input type="submit" name="UpdateSave" value="更新保存" />
        <input type="submit" name="Clear" value="清除缓存">';
}
//<input type="submit" name="DemoMsg" value="DebugMsg" />	';*/
echo  '<br/></div>';
  //print_r($_SESSION['InvFile']);

  //读取文件资料 及判断处理
if (isset($_SESSION['InvFile'])  && !isset($_SESSION['InvFile'][9])  && !isset($_SESSION['InvFile'][10])){
  
  if($_SESSION['InvFile'][6]=='zip'){
   
    if($_SESSION['InvFile'][5]==1){      
    
         $InvData=SaleInvXmlW($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
      
        // prnMsg('老税盘销zip'.$InvData.'--'.$path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
    }else if($_SESSION['InvFile'][5]==2){
        //进项xls
        if (empty($_SESSION['InvFile'][1])){
          prnMsg('进项解压文件失败!',"warn");
        }else{
       
          $options=[];
          $DataInv=InputTaxExecl($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1],0,0,$options);
        
          
          if (is_array($DataInv)){
            //echo ('-=zip696-|'.$path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
            $InvData=InputTaxData($DataInv,$tag);
          }  
        }    
    }

  }else {
     // echo'-=//非压缩文件'.$_SESSION['InvFile'][5].'='.$_SESSION['InvFile'][6];
    if($_SESSION['InvFile'][5]==0){
         //客名         
        if($_SESSION['InvFile'][6]=='txt'){
          $CustData=CustomerTxt($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
          //prnMsg(count($CustData)."客户编码文件".$_SESSION['InvFile'][1]."，没有读取到新的客户!",'info');
        
        }elseif($_SESSION['InvFile'][6]=='xml'){
          //prnMsg('x-xml');
        
          $CustData=CustomerXml($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
        }
    }else if($_SESSION['InvFile'][5]==1){
        
     // echo ('老税盘销售文件--'.$_SESSION['InvFile'][4].$_SESSION['InvFile'][1]);
      $InvData=SaleInvXmlW($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
      
     // echo '..............<br>';
      //var_dump($InvData);
     
    }else if($_SESSION['InvFile'][5]==2){
      //xls  文件进项
      //prnMsg($_SESSION['InvFile'][3].'-699xls  文件进项--'.$path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
      if (empty($_SESSION['InvFile'][1])){
        prnMsg('进项解压文件失败!',"warn");
      }else{
        $options='';
        //echo ('-=735普通文件='.$path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
        $DataInv=InputTaxExecl($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1],0,0, $options);
       
        if (is_array($DataInv)){
          $InvData=InputTaxData($DataInv,$tag);
        
        } 
      }     
    
    }else if($_SESSION['InvFile'][5]==3){
      //新税盘XML
        //echo ('-=740XML普通文件='.$path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
      $InvData=SaleInvXmlB($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
    }
  }
    //读���的文件内容转换变量
    $uperror=false;
  if ($_SESSION['InvFile'][5]==0){//客户名文
    if (is_array($CustData)){
      
      $_SESSION['InvFile'][10]=$CustData;    
      $_SESSION['InvFile'][0]=1;
    
      unset($CustData);
    }else{
      $uperror=true;
      $_SESSION['InvFile'][10]=$CustData;    
      $fp=$CustData;
    }
  }else{
    //以下为发票读取判断处理
   
    //prnMsg('税盘-728-'.$path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]);
    if (is_array($InvData)){
       
      $_SESSION['InvFile'][9]=$InvData['InvTrans'];    
      $_SESSION['InvFile'][0]=1;
      //print_r( $_SESSION['InvFile'][9]);
      //exit;
    
    
    
      unset($InvData);
    }else{
      //读取资料失败的文件标记3
        $fp=$InvData;
        $uperror=true;   
   
    }
  }

   /*-------------以下根据文件内容等判断处理文件*/
  if ($uperror ){
    if ( $_SESSION['InvFile'][0]==0){// $_SESSION['InvFile'][0]==0初次读取  1再次读取
      //检查该文件是否读取到系统';
      $SQL="SELECT  COUNT(*) fpcounter FROM `invoicetrans` WHERE uploadid=".$_SESSION['InvFile'][3];
      $Result=DB_query($SQL);
      $Row=DB_fetch_assoc($Result);
      if ($Row['fpcounter']>0){
       
        //系未检测到该上传过资料标记为3
         $SQL="UPDATE `invupload` SET flag=3,`remark`='".date("Y-m-d h:i:s")."' WHERE uploadid=".$_SESSION['InvFile'][3];
         $Result=DB_query($SQL);
         prnMsg($_SESSION['InvFile'][1].'读取发票文件,共计'.($InvData+1).'笔，已经上传！','info');
         unset($_SESSION['InvFile']);
         echo '<meta http-equiv="Refresh" content="1;"/>';// url=' . $RootPath . '/ImportTaxInvoice.php">';
         //exit;
      }else{
        //新文件没有插��系统
        $sql="DELETE FROM invupload WHERE flag=0 AND uploadid=".$_SESSION['InvFile'][3];
        $result=DB_query($sql);
        
        if (unlink($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1]))
          prnMsg($_SESSION['InvFile'][1]."删除文件完成！","info"); 
          unset($_SESSION['InvFile']);
          echo '<meta http-equiv="Refresh" content="1;"/>';
      }
  
    }elseif ( $_SESSION['InvFile'][0]==1){
      prnMsg($_SESSION['InvFile'][1]."再次读取文件失败！","info"); 
      unset($_SESSION['InvFile']);
      echo '<meta http-equiv="Refresh" content="1; url=' . $RootPath . '/ImportTaxInvoice.php">';
    }

  }
  

}
if(isset($_POST['Clear'])){
  unset($_SESSION['InvFile']);
  prnMsg("缓存清除！",'info');
}

//var_dump($_SESSION['InvFile'][9]);
if (isset($_SESSION['InvFile'])&& isset($_SESSION['InvFile'][9])){
    // $arr=array_unique(array_column($_SESSION['InvFile'][9], 'registerno'));
 
  //发票显示
  echo '<table cellpadding="2" class="selection">
      <tr>
        <th class="ascending">序号</th>	
        <th class="ascending">发票号</th>	
        <th class="ascending">开票日期</th>				
        <th class="ascending">发票类别</th>
        <th class="ascending">金额</th>
        <th class="ascending">税金</th>				
        <th class="ascending">税率</th>
        <th class="ascending">客户注册码</th>	
        <th class="ascending">客户名称</th>				
        <th >摘要</th> 					
      </tr>';			
    $k = 0; 
    $rw=1;		
    $AmoTotal=0;
    $TaxTotal=0;
    $Rate=-1;
    $tottypamo=0;
    $tottyptax=0;
    $InvType=0;
        
      
				$invrw=0;
  foreach($_SESSION['InvFile'][9] as $key=>$row) {	
      
        //	if (!isset($InvArr[$row['invno']])){//没有录入发票计算	
        $invrw++;		
        //按税率合计		 
      if ($Rate!=round(100*$row['tax']/$row['amount'],0) && $Rate!=-1){
        echo'<tr>
          <th ></th>			
          <th  colspan="3" >' . $Rate . '%税率合计</th>
          <th >'.round(abs($AmoTotal),2).'</th>
          <th >'.round(abs($TaxTotal),2).'</th>				
          <th colspan="4" ></th>				
        </tr>';
        $AmoTotal=($row['invtype']==2?-$row['amount']:$row['amount']);
        $TaxTotal=($row['invtype']==2?-$row['tax']:$row['tax']);
        $Rate=round(100*($row['tax']/$row['amount']),0);	
      
      }else{
        $AmoTotal+=($row['invtype']==2?-$row['amount']:$row['amount']);
        $TaxTotal+=($row['invtype']==2?-$row['tax']:$row['tax']);
      }
      //按进项销项合计
      if ($InvType!=$row['invtype'] && $InvType!=0){
        echo'<tr>
          <th ></th>			
          <th  colspan="3" >' . $impft[$InvType] . '合计</th>
          <th >'.round($tottypamo,2).'</th>
          <th >'.round($tottyptax,2).'</th>				
          <th colspan="4" ></th>				
        </tr>';
        $tottypamo=0;
        $tottyptax=0;
        $InvType=0;
        $rw=1;
      }
    
      if ($k==1){
        echo '<tr class="EvenTableRows">';
        $k=0;
      } else {
        echo '<tr class="OddTableRows">';
        $k=1;
      }				
      echo'<td>'.$rw.'</td>				
        <td>'. $key.'</td>				
        <td >'.$row['invdate'].'</td>
        <td >'.$impft[$row['invtype']].'</td>
        <td class="number">'.locale_number_format($row['amount'],POI).'</td>
        <td class="number">'.locale_number_format($row['tax'],POI).'</td>
        <td >'.round(100*$row['tax']/$row['amount'],0).'%</td>						
        <td >'.$row['registerno'].'</td>
        <td >'.$row['custname'].'</td>
        <td >'.$row['remark'].'</td>											
        </tr>';
          $Rate=round(100*$row['tax']/$row['amount'],0);					
          $tottypamo+=$row['amount'];
          $tottyptax+=$row['tax'];				
          $InvType=$row['invtype'];				
        
          $rw++;
      //	}//while
    //$_POST['InvTransArr']=$InvTransArr;
  }
				echo'<tr>
						<th ></th>			
						<th  colspan="3" >' . $Rate . '%税率合计</th>
						<th >'.abs($AmoTotal).'</th>
						<th >'.abs($TaxTotal).'</th>				
						<th colspan="4" ></th>				
					</tr>';
				echo'<tr>
						<th ></th>			
						<th  colspan="3" >' . $impft[$InvType] . '合计</th>
						<th >'.abs(round($tottypamo,2)).'</th>
						<th >'.abs(round($tottyptax,2)).'</th>				
						<th colspan="4" ></th>				
					</tr>';				
				echo '</table>';
				if ($invrw>100000){
					$_SESSION['invflag'][0][3]=$invrw;
				//}else{
					$sql="DELETE FROM invupload WHERE flag=0 AND filesinv='".$_SESSION['InvFile'][1]."'";
					$result=DB_query($sql);
					if (unlink($filepath.$_SESSION['InvFile'][1])){
						$msg.=$_SESSION['InvFile'][1]."删除文件完成！<br>"; 
						
						
					}else{
						$msg.=$_SESSION['InvFile'][1]."删除文件失败！<br>"; 
					}
					unset($_SESSION['InvFile']);
					prnMsg('没有数据需要更新到数据库!<br>'.$msg);
				}
		
		
	
}elseif (isset($_SESSION['InvFile'])&& isset($_SESSION['InvFile'][10])){
  //客户名称及编码  
    echo '<br />
      <table class="selection">
            <tr>
            <th class="ascending">序号</th>
            <th class="ascending">客户名称</th>
            <th>注册码</th>
            <th>账号</th>
            <th class="ascending">开户银行</th>	         	
            <th class="ascending">状态</th>
          </tr>'; 
      $R=1; 
      $k=0; //row colour counter
     
        //判断��字是否有相同.���后判断注册码  ���到regid
      foreach( $_SESSION['InvFile'][10] as $key=>$row ){       
       
        if ($k==1){
          echo '<tr class="EvenTableRows">';
          $k=0;
        } else {
          echo '<tr class="OddTableRows">';
          $k=1;
        }     
        echo  '<td>'.$R.'</td>	
            <td>'.$row['custname'].'</td>
            <td>'.$key.'</td>
            <td>'.$row['bankact'].'</td>
            <td>'.$row['bankname'].'</td>';
          if (isset($CustNew[$key])){  
            if (isset($CustInsert[$CustNew[$key]])){//有客户��无registerno
              echo' <td title="'.$CustInsert[$CustNew[$key]].'">插入注册码</td>';
            }else{
               echo' <td>新客户</td>';
            }
          }else{
            echo' <td>正常</td>';
          }
           
          echo'</tr>';						
        $R = $R + 1;
      }      
    echo '</table>';

}

   
if (!empty($_SESSION['InvFile'][9])){  
  
    //('//进项、��项文件');
  if (isset($_POST['UpdateSave'])){		
    if($FileType==1){//老税盘
      $msg='销项发票资料更新数据库!<br>';
    }elseif($FileType==0) {//进项
      # code...					
      $msg='进项发票资料更新数据库!<br>';
    }
    $rw=0;
    $wrcust=array();
    foreach ($_SESSION['InvFile'][9] as $key=>$row){  
       
          $sql="INSERT IGNORE INTO invoicetrans(invno,
                                              tag,
                                              invtype,
                                              transno,
                                              period,
                                              invdate,
                                              amount,
                                              tax,
                                              toregisterno,
                                              toaccount,
                                              toname,
                                              tobank,
                                              toaddress,
                                              stockname,
                                              spec,
                                              unit,
                                              price,
                                              quantity,
                                              remark,
                                              flg	,
                                              regid,
                                              stled,
                                              uploadid)
                                          VALUES('".$key."',
                                          '". $tag."',
                                          '".$row['invtype']."',
                                          '0',
                                          '".$row['prd']."',
                                          '".$row['invdate']."',
                                          '".$row['amount']."',
                                          '".$row['tax']."',
                                          '".$row['registerno']."',
                                          '',	
                                          '".$row['custname']."',														
                                          '',	 '','','','',
                                          '0','0',
                                          '".$row['remark']."',
                                          '0',
                                          '".$regid."',
                                          '0',
                                         '".$_SESSION['InvFile'][3]."')";
        
                                  
        $result=DB_query($sql);
        if ($result){
            $rw++;     
        } 
    }    
    if ($rw>0){
    
      $sql="UPDATE invupload SET flag=1 WHERE  uploadid='".$_SESSION['InvFile'][3]."'";
      $result=DB_query($sql); 
    
      if ($msg!='')
        prnMsg($msg,'info');
      unset($_SESSION['InvFile']);
    	echo '<meta http-equiv="Refresh" content="1; url=' . $RootPath . '/ImportTaxInvoice.php">';      
    }  
  }
}elseif (!empty($_SESSION['InvFile'][10])){
  //prnMsg('//txt户编，客户更新1034'); 
    $counter=0;
    $rw=0;   
   // var_dump($_SESSION['InvFile'][10]);
  if (isset($_POST['UpdateSave'])){		
   
    foreach($_SESSION['InvFile'][10] as $key=>$row){     
     
      if ($row['flag']==1){//注册码不存在  标记为1
          $counter++;
          $sql="INSERT IGNORE INTO registername (custname,
                          tag,
                          account,
                          flg,
                          custtype,
                          regdate) 
                VALUE(	'".$row['custname']."',
                    '".$row['tag']."' ,
                    '',
                    '1',
                    ".$_SESSION['InvFile'][5].",
                    '".date("Y-m-d")."') ";
            $result=DB_query($sql);
          //prnMsg($sql);
        if ($result){
          $rw++;
        }
        if(DB_affected_rows($result)>0){//插入成功			
       
          $regid=DB_Last_Insert_ID($db,'registername','regid');
        }else{
          $regid=$custname_reg[$row['custname']][1];//=array($row['tag'],$row['regid'])
        }
        if ($regid>0){
            $sql="INSERT IGNORE INTO registeraccount(regid,
                                registerno,
                                tag,
                                subject,
                                acctype,
                                flg)
                              VALUE('".$regid	."',
                                  '". $row['registerno']."',		
                                  '".$row['tag']."' ,
                                  '',
                                  ".$_SESSION['InvFile'][5].",
                                  '0' 	) ";
             //prnMsg($sql);
            $result=DB_query($sql);
        }
      }elseif ($row['flag']==2){//名称已经存在  标记2
        $counter++;
        $sql="INSERT IGNORE INTO registeraccount(regid,
                                                registerno,
                                                tag,
                                                subject,
                                                acctype,
                                                flg)
                                              VALUE('".$row['regid']."',
                                                  '". $key."',			
                                                  '".$row['tag']."' ,
                                                  '',
                                                  ".$_SESSION['InvFile'][5].",
                                                  '0')";
          // ECHO '-='.($sql);
                $result=DB_query($sql);
        if ($result){
          $rw++;
        }else {
          $SQL="INSERT INTO `erplogs`(`title`,
                                      `content`,
                                        `userid`,
                                      `logtype`,
                                      `logtime`) 
                                        VALUES (	'".$row['custname']."' ,
                                    '注册码".$row['registerno']."REGID ".$Row['regid']."',
                                    '".$_SESSION['UserID']."',
                                    '-1',
                                    '".date("Y-m-d h:i:s")."' )";
              $Result=DB_query($SQL);
        }
      }
      
    }//endforearch

     //echo('-=970客户及注册码更新到数据库!<br>'.$rw.'=='.$counter);
     //exit;
    if ($rw==$counter && $rw>0){
     
      $sql="UPDATE  invupload SET flag=1 WHERE  uploadid=".$_SESSION['InvFile'][3];
      $result=DB_query($sql); 
      //prnMsg($sql);
      if ($result){
       
          unset($_SESSION['InvFile']);
     
      }
     
     // echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/ImportTaxInvoice.php">';

      
    }else{
      $sql="DELETE FROM invupload WHERE flag=-1 AND filesinv='".$_SESSION['InvFile'][1]."'";
      
      $result=DB_query($sql);
      if ($result){
        if (unlink($path.$_SESSION['InvFile'][4]."/FilesInv/".$_SESSION['InvFile'][1])){

          $msg.=$_SESSION['InvFile'][1]."删除文件完成！<br>";         
          
        }else{
          $msg.=$_SESSION['InvFile'][1]."删除文件失败！<br>";      
       
        }
        unset($_SESSION['InvFile'] );     
      }
      if ($msg!=''){
        prnMsg($msg,'info');
      }
      //echo '<meta http-equiv="Refresh" content="0; url=' . $RootPath . '/ImportTaxInvoice.php">';
   }
  }
}				
	
 	echo '</div></form>';
  include('includes/footer.php');
   /**
   * 添加客单位注册码  用户名，在用
   *
   * @param string $file      文件地址
   * @param int    $sheet     工作表sheet(传0则获取第一个sheet)
   * @param int    $columnCnt 列(传0则自动获取最大列)
   * @param array  $options   操作选项
   *                          array mergeCells 合并单元格数组
   *                          array formula    公式数组
   *                          array format     单元格格式数组
   *
   * @return array
   * @throws Exception
   */
  
function  CustomerAdd($row,$acctype,$addtype){

	if ($addtype==1){
    //名称已 注册码不在  标记为1
		$sql="INSERT IGNORE INTO registername (custname,
                                        tag,
                                        account,
                                        flg,
                                        custtype,
                                        regdate) 
                              VALUE(	'".$row['custname']."',
                              '".$row['tag']."' ,
                              '',
                              '".$acctype."',
                              '".$acctype."',
                              '".$_SESSION['lastdate']."') ";
			$result=DB_query($sql);
		
		
			if(DB_affected_rows($result)>0){//插入成功			
				//$rw++;
				$regid=DB_Last_Insert_ID($db,'registername','regid');
				$sql="INSERT IGNORE INTO registeraccount(regid,
                                                registerno,
                                                tag,
                                                subject,
                                                acctype,
                                                flg)
                                              VALUE('".$regid	."',
                                                  '". $row['registerno']."',		
                                                  '".$row['tag']."' ,
                                                  '',
                                                  '".$acctype."',
                                                  '".$acctype."'	) ";
				$result=DB_query($sql);
			}
			if (isset($result)){
				$rw++;
			}
	}elseif ($addtype==2){//名称已经在 注册码不在  标记为2
    //$counter++;
    
    $sql="SELECT regid, custname, account FROM registername
           WHERE custname='". $row['custname']."' AND tag='".$row['tag']."'";
    $Result=DB_query($sql);
    $Row=DB_fetch_assoc($Result);
    if (!empty($Row)){
		  $sql="INSERT IGNORE INTO registeraccount(regid,
                                            registerno,
                                            tag,
                                            subject,
                                            acctype,
                                            flg)
                                          VALUE('".$Row['regid']	."',
                                              '". $row['registerno']."',			
                                              '".$row['tag']."' ,
                                              '0',
                                              '".$acctype."',
                                              '".$acctype."')";
    }
    
    $result=DB_query($sql);
    if ($result){
      $rw++;
    }else{
      $SQL="INSERT INTO `erplogs`(`title`,
                                `content`,
                                  `userid`,
                                `logtype`,
                                `logtime`) 
                                  VALUES (	'".$row['custname']."' ,
                              '注册码".$row['registerno']."REGID ".$Row['regid']."',
                              '".$_SESSION['UserID']."',
                              '-1',
                              '".date("Y-m-d h:i:s")."' )";
        $Result=DB_query($SQL);
    }
	
	
	}
	if ($rw>0){
		return $regid;
	}else{
		return -1;
	}
	
}
 /**
   * 进项税EXECL导入，在用
   *
   * @param string $file      文件地址
   * @param int    $sheet     工表sheet(传0则获取第一个sheet)
   * @param int    $columnCnt 列(���0则自动获取最大列)
   * @param array  $options   操作选项
   *                          array mergeCells 合并单元格数组
   *                          array formula    公式数组
   *                          array format     单元格格式数组
   *
   * @return array
   * @throws Exception
   */

function InputTaxExecl(string $file = '', int $sheet = 0, int $columnCnt = 0, &$options = []){
     //return  $file;
    try {
        /* 转码 */
        $file = iconv("utf-8", "gb2312", $file);

        if (empty($file) OR !file_exists($file)) {
            throw new \Exception('文件不存在!');
        }

        /** @var Xlsx $objRead */
        $objRead = IOFactory::createReader('Xlsx');

        if (!$objRead->canRead($file)) {
            /** @var Xls $objRead */
            $objRead = IOFactory::createReader('Xls');

            if (!$objRead->canRead($file)) {
                throw new \Exception('只支持导入Excel文件！');
            }
        }

        /* 如果不需要获取特殊操作，则只读容，可以大幅度提升读取Excel效率 */
        empty($options) && $objRead->setReadDataOnly(true);
        /* 建立excel对象 */
        $obj = $objRead->load($file);
        /* 获取指定的sheet表 */
        $currSheet = $obj->getSheet($sheet);

        if (isset($options['mergeCells'])) {
            /* 读取合并行 */
            $options['mergeCells'] = $currSheet->getMergeCells();
        }

        if (0 == $columnCnt) {
            /* 取得最大列号 */
            $columnH = $currSheet->getHighestColumn();
            /* 兼容原逻辑，循环时使用的是小于等于 */
            $columnCnt = Coordinate::columnIndexFromString($columnH);
        }

        /* 取总行数 */
        $rowCnt = $currSheet->getHighestRow();
        $data   = [];

        /* 读取内容 */
        for ($_row = 1; $_row <= $rowCnt; $_row++) {
            $isNull = true;

            for ($_column = 1; $_column <= $columnCnt; $_column++) {
                $cellName = Coordinate::stringFromColumnIndex($_column);
                $cellId   = $cellName . $_row;
                $cell     = $currSheet->getCell($cellId);

                if (isset($options['format'])) {
                    /* 获取格式 */
                    $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                    /* 记录格式 */
                    $options['format'][$_row][$cellName] = $format;
                }

                if (isset($options['formula'])) {
                    /* 获取公式，公式均为=号开头数��� */
                    $formula = $currSheet->getCell($cellId)->getValue();

                    if (0 === strpos($formula, '=')) {
                        $options['formula'][$cellName . $_row] = $formula;
                    }
                }

                if (isset($format) && 'm/d/yyyy' == $format) {
                    /* 日期格式翻转处理 */
                    $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                }

                $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                if (!empty($data[$_row][$cellName])) {
                    $isNull = false;
                }
            }

            /* 判断是��整行数据为空��是的话删该行数据 */
            if ($isNull) {
                unset($data[$_row]);
            } 
        }
      

        return $data;
    } catch (\Exception $e) {
        throw $e;
    }
}
  /**
   *进���税导出excel数组转数格式;在用
   * @param string $file      文件地址
   *
   * @return array  返回格式 array("InvTrans"=>$InvTrans,"fpcounter"=>$fpcounter);    
   *                返回 >0 此件数据已经读如
   * @throws Exception
   * 错误返回-1
   */
function  InputTaxData($data,$tag){
  //��取数据格式转换
  $fpcounter=-1;
  for ($i=1;$i<=count($data);  $i++){		//行数4开始数据项
  
    if ($i==2){
        if (strlen($data[$i]['H'])==6){
          $ym=date("Y-m-d",strtotime($data[$i]['H']."01"));
        }else{
          //没有得���期间数
          return $fpcounter;
        }
        $prd=DateGetPeriod($ym);
        
    }else if ($i>=4){
      //添加读取检查客户 
      $tax=$data[$i]['H'];
      $amount=$data[$i]['G'];
      $regno=$data[$i]['E'];
      $custname=$data[$i]['F'];
      $invdate=$data[$i]['D'];
      $SQL="SELECT a.`regid`, a.`registerno`,b.custname 
            FROM `registeraccount` a LEFT JOIN registername b ON a.regid=b.regid  WHERE a.`registerno`='".$regno."' OR b.custname ='".$custname."'";
        $result=DB_query($SQL);
        //prnMsg($SQL);
        $regrow=DB_fetch_assoc($result);
        //??有问题
        if (empty($regrow['registerno'])){
          //添加单位
          $flag=1;
          $ROW=array("registerno"=>$regno,"custname"=>$custname,"tag"=>$tag);
          $regid=CustomerAdd($ROW,2,1);
          if ($regid<0)
          $flag=-1;
          //添加日志记录
        }else{
          $regid=$regrow['regid'];
        }
        $SQL="SELECT invno FROM `invoicetrans` WHERE  invno='".$data[$i]['C']."' AND period=".$prd;
        $result=DB_query($SQL);
        $invrow=DB_fetch_assoc($result);
        if (empty($invrow['invno'])){
          $InvTrans[$data[$i]['C']]=array('rate'=>round(($tax/$amount)*100,0),
                                          'tag'=>$tag ,
                                          'invtype'=>0,
                                          'prd'=>$prd,
                                          'invdate'=>$invdate,
                                          'amount'=> $amount,
                                          'tax'=>$tax,
                                          'registerno'=>$regno,//registerno													
                                          'custname'=>$custname ,//custname
                                          'regid'=>$regid,																						
                                          'remark'=>'',
                                          'custype'=>2,
                                          'flag'=>$flag );
        }else{
          $fpcounter++;
        }
                                
    }                
  
  }//for
  if (is_array($InvTrans)){
    return array("InvTrans"=>$InvTrans,"fpcounter"=>$fpcounter);    
  }else{
    return $fpcounter;
  }
  //	$InvTransArr =sortArrByOneField($InvTransArr,'rate');	
}
  /**
   *读取XMLTxt 客户编��和名称转为数组
   * @param string $file      文件地址
   *
   * @return array
   * @throws Exception
   * 误返回-1
   */
function  CustomerTxt($fname){
 
    //txt客户编码
    $str = file_get_contents($fname);
    
    $str_encoding = mb_convert_encoding($str, 'UTF-8', 'UTF-8,GBK,GB2312,BIG5');//转换字符集（编码���
    //检测文件中的分隔符
    if (substr_count($str_encoding, '~~')>9){
      $exstr="~~";
    }else{
      $exstr=",";
    }
    $arr = explode("\r\n", $str_encoding);//转换成数组
  
    $r=1;
    $titlearr=array();
    $titlearr=explode($exstr,$arr[2]);	
  
    $insrow=0;	
    $_SESSION['InvFile'][1]=-1;//标记读取客户编码
    foreach($arr as $value){
      if($r>3){
        $file_arr=explode($exstr,$value);				
        $address='';
        $tel='';
        $bank='';
        $act='';
        preg_match_all("/([0-9]{3,4}-)?([0-9]{5,9}){1}/", $file_arr[4], $matches);
        $tel=$matches[0][0];
        $address=str_replace($matches[0][0],'',$file_arr[4]);				
        preg_match_all("/(([0-9]{2,6}-)?([0-9]{10,20}){1})|(([0-9]{21,30}){1})/", $file_arr[5], $matches);
        $bankacc=$matches[0][0];				
        $bank=str_replace($matches[0][0],'',$file_arr[5]);				
        if ($file_arr[3]!='' &&$file_arr[1]!=''){
          if(strlen($file_arr[3])==18){
            //$in++;
            $custname=match_chinese($file_arr[1]);
            $regno=	match_number($file_arr[3],1);
            $SQL="SELECT `regid`, `registerno`, `bankaccount`, `custname`, sub,`acctype`, `tag` FROM `register_account_sub` WHERE registerno='".$regno."' OR custname ='".$custname."'";
            $result=DB_query($SQL);
     
            $regrow=DB_fetch_assoc($result);
            $flag=0;
            if (!empty($regrow['sub'])){
                $act=$regrow['sub'];
            }
           // prnMsg($regrow['custname'].'---'.$regrow['registerno'].'[[['.$regrow['regid']);
            if (empty($regrow['custname'])&&empty($regrow['registerno'])){
             
                $flag=1;//新客户
            }elseif (empty($regrow['registerno'])){
                $flag=2;//无注册码
                $regid=$regrow['regid'];
            }
            if ($flag==1|| $flag==2){
              $CustnameReg[$regno]= array("custname"=>$custname,
                                          "tag"=>$_SESSION['Tag'],
                                          "bankact"=>$act,
                                           "bankname"=>$bank ,  
                                           "regid"=>$regid,                                     
                                           "flag"=>$flag);
            }
            
            //AddCustname($file_arr[1],$file_arr[3],$_POST['UnitsTag'],$act,$bank,1);
          }																
        }//row238						
      }
      $r++;
    }	
    
    if (is_array($CustnameReg)){
      return $CustnameReg;
    }else{
      return $r-3;//返���-1没有读取���新客户资料
    }
  }

function  CustomerXml($fname){//($filepostfix=='.xml'){//xml客户编码	
    //prnMsg('客户名xml下面代码为读取XML写入表  黑色税盘464');		??有问题没有验证	
  
    $xml = simplexml_load_file($fname);
    $amo=0;			
    $copies=0;			
    if( $xml['TYPE']=='KEHUBIANMA'){
      
      foreach($xml->KHXX->Row as $child){
        $ch0=(array)$child['MC'];
        $ch1=(array)$child['NSRSBH'];							
        $ch2=(array)$child['DZ'];
        $ch3=(array) $child['YHZH'];
        $ch4=(array)$child['YJDZ'];
        $ch5=(array) $child['O_BM'];
        $ch6=(array)$child['PID'];
        $dz=$ch2[0];
        $yhzh=$ch3[0];
        $custname=match_chinese($ch0[0]);
        $regno=	match_number($ch1[0],1);
          //电话
        preg_match_all("/([0-9]{3,4}-)?([0-9]{5,9}){1}/", $dz, $matches);
              $tel=$matches[0][0];
              $address=str_replace($matches[0][0],'',$dz);
            //银行账号  银行
        preg_match_all("/(([0-9]{2,6}-)?([0-9]{10,20}){1})|(([0-9]{21,30}){1})/", $yhzh, $matches);
        $act=$matches[0][0];
        
        $bank=str_replace($matches[0][0],'',$yhzh);
        
        $CustnameReg[$regno]= array("custname"=>$custname,
                                    "tag"=>1,
                                    "bankact"=>$act,
                                    "bankname"=>$bank );
            
      }
    }		
  
  
  if (is_array($CustnameReg)){
    return $CustnameReg;
  }else{
    return 1;
  }
}


    /**
   *读取XML写入表//新税盘501');
   * @param string $file      文件地址
   *
   * @return array
   * @throws Exception
   * 错误返回-1
   */

function SaleInvXmlB($fname){  
  try {  
  $xml = simplexml_load_file($fname);
  if (empty($xml) ) {
    throw new \Exception('文件不存在!');
  }
  //新祱盘销 销售发票
      

  
    $tax=0;
    $copies=0;			
    $msg='';
    $chkflg=0;						
    //$timey=getTime($_SESSION['lastdate'],'Y');//年初时间戳
    // $timem=getTime($_SESSION['lastdate'],'d');//月末时间戳
  
    if( $xml['INFO']=='YIKAIFAPIAO'){
      // $_SESSION['InvFile'][1]==2;
      $xmlar=array();
      foreach($xml->YKFP->Row as $child){
        //var_dump($child);
        $ch0=(array)$child['发票号码'];
        $ch1=(array)$child['开票日期'];							
        $ch2=(array)$child['合计金额'];
        $ch3=(array) $child['税额'];
        $ch4=(array)$child['客户识别号'];
        $ch5=(array) $child['客户名称'];
        $ch6=(array)$child['主要商品名称'];
        $ch7=(array)$child['发票类型'];
        $ch=(array)$child['发票状态'];
       //$sltprd=$_SESSION['period']-(date('m',strtotime($_SESSION['lastdate']))-date('m',strtotime($ch1[0])));
        $invdate= date('Y-m-d',strtotime($ch1[0]));
        $prd=DateGetPeriod($invdate);
        $regno=match_number($ch4[0],1);
        //prnMsg($ch5[0]);
        $custname=match_chinese($ch5[0]);
        if (isset($regname[$regno])){//根据注册码
        
          $flag=0;
        }else{
          if (isset($custname_reg[$custname])){
            //名称存在  注册码��存在							
            $flag=3;
          }else{  //都不存在
            $flag=1;
          }
        }
        if ($ch[0]=='正常发票'){
          $InvTransArr[$ch0[0]]=array('invno'=>$ch0[0],
                          'tag'=>$_POST['UnitsTag'] ,
                          'invtype'=>1,//$flag,
                          'prd'=>$prd,
                          'invdate'=>$invdate,
                          'amount'=>$ch2[0],
                          'tax'=>$ch3[0],
                          'registerno'=>$regno,//registerno													
                          'custname'=>$custname	,																								
                          'remark'=>'',
                          'flag'=>$flag,
                          'acctype'=>1);	
          //$xmlar[]= array($ch0[0],$ch1[0],$ch2[0],$ch3[0],$ch4[0],$ch5[0],$ch6[0],$ch7[0]);
        }
      }		
    } 
  } catch (Exception $e) {
    return false;
  }

  
  //else{  prnMsg('你上传的销项发 文件内容不正确','info');
    if (is_array($InvTransArr)){
      return array("InvTrans"=>$InvTransArr,"fpcounter"=>$fpcounter);  
    }else{
      return $fpcounter;
    }
  //  return $InvTransArr;
}
   /**
   *  老金税盘白色销项票导入xml
   *
   * @param string $file      文件地址
   *
   * @return array
   * @throws Exception
   * 错误返回-1
   */

function SaleInvXmlW($fname){  
     //读���老销售
    //prnMsg($fname);
    $xml = simplexml_load_file($fname);//, null, LIBXML_NOERROR);
    
    $amo=0;
    $tax=0;
    $copies=0;
    $tag=1;
    $fpcounter=-1;
    if ($xml!==false){//检测��取是���成功  
    
      if ($xml->getName()=='taxML'){//检验xml文件是否包含taxML
          $taxno=$xml->sbbZzsfpkjmx->head->publicHead->nsrsbh;
        
        if ($taxno!=$_SESSION['CompanyRecord'][$tag]['companynumber']){
        
          return -1;//'税号不符!';
        }
      }
       //prnMsg($xml->sbbZzsfpkjmx->body->zyfpkjhjxx->zyfpkjhjs);
      if ($xml->sbbZzsfpkjmx->body->zyfpkjhjxx->zyfpkjhjs>0){
        
          //prnMsg('//增值税专用发票');  
          //var_dump($xml->sbbZzsfpkjmx->body->zyfpkjmx->mxxx);    
          foreach($xml->sbbZzsfpkjmx->body->zyfpkjmx->mxxx as $child){
            if ($child->zfbz=='N'){
              $fphm=(array)$child->fphm;
              $xh=(array)$child->xh;						
              $kprq=(array)$child->kprq;//日期 <kprq>20180627 16:07:48</kprq>
              $je=(array) $child->je;
              $se=(array)$child->se;	//税额
              $rsbh=(array)$child->gmfnsrsbh;//客户税号
              $invdate=date("Y-m-d",strtotime($kprq[0]));
              $prd=DateGetPeriod($invdate);  
              $regno=match_number($rsbh[0],1);
              $custname='';//'老税盘客户名没有';       
             
              $SQL="SELECT invno FROM `invoicetrans` WHERE  invno='".$fphm[0]."' AND period=".$prd;
              //prnMsg($SQL);
              $result=DB_query($SQL);
              
              $invrow=DB_fetch_assoc($result);
              if (empty($invrow['invno'])){
            
                $InvTransArr[$fphm[0]]=array('tag'=>$tag ,
                                              'invtype'=>1,
                                              'prd'=>$prd,
                                              'invdate'=>$invdate,
                                              'amount'=>$je[0],
                                              'tax'=>$se[0],                                       
                                              'registerno'=>$regno,									
                                              'custname'=>$custname	,		
                                              'regid'=>'',																						
                                              'remark'=>'',
                                              'custype'=>1,
                                              'flag'=>0	);
                $amo+=$je[0];  
                $tax+=$se[0];	   
              }else{
                $fpcounter++;
              }      
            }
          }//endfor
      }//专;
      if ($xml->sbbZzsfpkjmx->body->ptfpkjhjxx->ptfpkjhjs>0){
        //prnMsg('//增值税普通发票');
        foreach($xml->sbbZzsfpkjmx->body->ptfpkjmx->mxxx as $child) {
          if ($child->zfbz=='N'){
            
              $n++;
              $fphm=(array)$child->fphm;
              $xh=(array)$child->xh;						
              $kprq=(array)$child->kprq;//日 <kprq>20180627 16:07:48</kprq>
              $je=(array) $child->je;
              $se=(array)$child->se;	//税额
              $rsbh=(array)$child->gmfnsrsbh;//客户税号
            $invdate=date("Y-m-d",strtotime($kprq[0]));
            $regno=match_number($rsbh[0],1);
            //$fphm=$child->fphm;
            $prd=DateGetPeriod($invdate);  
            //$flag=3; 
            $custname='';
           //$SQL="SELECT COUNT(*) counter FROM `invoicetrans` WHERE  invno='".$fphm[0]."' AND period=".$prd;
            $SQL="SELECT invno FROM `invoicetrans` WHERE  invno='".$fphm[0]."' AND period=".$prd;
            $result=DB_query($SQL);
            $invrow=DB_fetch_assoc($result);
            if (empty($invrow['invno'])){
              $InvTransArr[$fphm[0]]=array('tag'=>$tag ,
                                              'invtype'=>3,
                                              'prd'=>$prd,
                                              'invdate'=>$invdate,
                                              'amount'=>$je[0],
                                              'tax'=>$se[0],                                       
                                              'registerno'=>$regno,									
                                              'custname'=>$custname	,		
                                              'regid'=>'',																						
                                              'remark'=>'',
                                              'custype'=>3,
                                              'flag'=>0	);
               
          
                            
              $amo+=$je[0];
              $tax+=$se[0];	
            }	else{
              $fpcounter++;
            }				
          }						
        }//endfor
      
      }//普票   
      unset($xml);
      // 以下代码读取已经存在���系统中的名称 标示flag
      if (count($InvTransArr)>0){
          //无重复注册码
          $registerno=array_unique(array_column($InvTransArr, 'registerno'));

          //遍历注码  查regisd  客户名
          foreach($registerno as $val){
            $SQL="SELECT a.`regid`, a.`registerno`,b.custname 
                  FROM `registeraccount` a LEFT JOIN registername b ON a.regid=b.regid  WHERE a.`registerno`='".$val."'";
            $result=DB_query($SQL);
            //prnMsg($SQL);
            $regrow=DB_fetch_assoc($result);
            //系统不存在注册码 $registerid
            if (!empty($regrow['registerno'])){
                $registerid[$val]=array($regrow['regid'],$regrow['custname']);
            }else{
                $CustData[$val]=array($regrow['regid'],$regrow['custname']);
            }
          } 
          if (count($registerid)>0||count($CustData)>0){       
            //根据注册码  得到用户名
            $invrow=count($InvTransArr);
             //更新发票资料
            foreach ($InvTransArr as $key=>$row) { 
              if (isset($registerid[ $row['registerno'] ])){

                $custname=$registerid[ $row['registerno'] ][1];
                $InvTransArr[$key]['flag']=1;
                $InvTransArr[$key]['custname']=$custname;
                $InvTransArr[$key]['regid']=$registerid[ $row['registerno'] ][0];
              }else  if (isset($CustData[ $row['registerno'] ])){

                $custname=$CustData[ $row['registerno'] ][1];
                $InvTransArr[$key]['flag']=0;
                $InvTransArr[$key]['custname']=$custname;
                $InvTransArr[$key]['regid']=$registerid[ $row['registerno'] ][0];
              }
              
             
            }//end for
          }       
      } 
      if (is_array($InvTransArr)){
        return array("InvTrans"=>$InvTransArr,"fpcounter"=>$fpcounter);  
      }else{
        return $fpcounter;
      }
    }else{
      return -2;//读取失败
    }
    
}
 
function ReadInvFile($fnam,$filepath,$Inv_Type){
   //没有使用
    $arr=0;
    $ret='qwwee';//调试 
    switch ($Inv_Type) {
      case  0:        
        $arr=0;
      break;
      case 1:
        			//prnMsg('老金税盘白色销项票');
			
			$xml = simplexml_load_file($filepath.$fname);
			$amo=0;
			$tax=0;
			$copies=0;
		
				if ($xml->sbbZzsfpkjmx->body->zyfpkjhjxx->zyfpkjhjs>0){
					$fparr=array();
					//prnMsg('//增值税专用发票');
					$_SESSION['InvFile'][1]=1;
				
					foreach($xml->sbbZzsfpkjmx->body->zyfpkjmx->mxxx as $child)
					{
						if ($child->zfbz=='N'){
							$ch0=(array)$child->fphm;
							$ch1=(array)$child->xh;						
							$ch2=(array)$child->kprq;//日期 <kprq>20180627 16:07:48</kprq>
							$ch3=(array) $child->je;
							$ch4=(array)$child->se;	//���额
							$ch5=(array)$child->gmfnsrsbh;//客户税号
						
							$fparr[]=array($ch0,$ch1,$ch2,$ch3,$ch4,$ch5);
							$invdate=substr($child->kprq,0,4).'-'.substr($child->kprq,4,2).'-'.substr($child->kprq,6,2);
							$sltprd=$_SESSION['period']-(substr($_SESSION['lastdate'],0,4)-substr($child->kprq,0,4))*12+(substr($child->kprq,4,2)-substr($_SESSION['lastdate'],5,2));
							$regno=match_number($child->gmfnsrsbh,1);
						
							if (isset($regname[$regno][0])){//注册码  存在
								$custname=$regname[$regno][0];
								$flag=0;
							}else{
								$custname='';//名称存在  注册码不存在
								
									$flag=3;
							
							}
							$InvTransArr[]=array('invno'=>$child->fphm,
                                    'tag'=>$_POST['UnitsTag'] ,
                                    'invtype'=>1,//$FileType,
                                    'prd'=>$sltprd,
                                    'invdate'=>$invdate,
                                    'amount'=>$child->je,
                                    'tax'=>$child->se,
                                    'registerno'=>$regno,//registerno													
                                    'custname'=>$custname	,																								
                                    'remark'=>'',
                                    'flag'=>$flag	);
							$amo+=$child->je;     
							
							
						}
					}
				}//专票;
				if ($xml->sbbZzsfpkjmx->body->ptfpkjhjxx->ptfpkjhjs>0){
					//prnMsg('//增值税普通发票');
					foreach($xml->sbbZzsfpkjmx->body->ptfpkjmx->mxxx as $child)
					{
						if ($child->zfbz=='N'){
							
							$sltprd=$_SESSION['period']-(substr($_SESSION['lastdate'],0,4)-substr($child->kprq,0,4))*12+(substr($child->kprq,4,2)-substr($_SESSION['lastdate'],5,2));
							$n++;
							$invdate=substr($child->kprq,0,4).'-'.substr($child->kprq,4,2).'-'.substr($child->kprq,6,2);
							$regno=match_number($child->gmfnsrsbh,1);
						
							if (isset($regname[$regno])){//根据注册码
								$custname=$regname[$regno][0];
								$flag=0;
							}else{								
								$custname='新客户[税盘导入]';//名������在  注册码不存在
								$flag=3;
							
							}
							$InvTransArr[]=array('invno'=>$child->fphm,
												'tag'=>$_POST['UnitsTag'] ,
												'invtype'=>3,//$FileType,
												'prd'=>$sltprd,
												'invdate'=>$invdate,
												'amount'=>$child->je,
												'tax'=>$child->se,
												'registerno'=>$regno,//registerno													
												'custname'=>$custname	,																								
												'remark'=>'',
												'flag'=>$flag	);
																
							$amo+=$child->je;
							$tax+=$child->se;						
						}						
					}
				
				}//��票
        //$mid = memory_get_usage();	
      break;			
      case 2:
        $ret=$fname;
      // $InvTransArr =sortArrByOneField($InvTransArr,'rate');	
          //var_dump($InvTransArr);		
        break;
      default:
        $arr=4;   
    }
    return $ret;
}
function getinvtype($fname){
    /**根据税控盘类型，信用代码  文件名 得到文件类型  销售1，3  进项票0  客户编�� */
    //$postfix=substr($fname,strrpos($fname,'.') );
    $registerno=$_SESSION['CompanyRecord'][1]['companynumber'];//"91371000MA3MW0G920";
    $nametype=array("taxML_ZZSFPKJMX_"=>1,"_dk"=>2,"已开发票"=>3,"客户编码"=>7,"_V10"=>1,"_CRC"=>6);
    //已发票191019_085956
    //taxML_ZZSFPKJMX_1_1_20190801_20190831_913710025533932971
    //913710025533932971_201912_dk_1  
    //913710025533932971-20191122-201910-进项票-增值税专用发票
    
    if ($_SESSION['CompanyRecord'][1]['taxtype']==2){
      if (!(strpos($fname,"已开发票")===false)){
        //新税盘 销项
        return 3;
      }
    }
    if (!(strpos($fname,"客户编码")===false)){
      return 0;
    }
    if (!(strpos($fname,$registerno)===false)){
      if ($_SESSION['CompanyRecord'][1]['taxtype']==1){
        if (!(strpos($fname,"taxML_ZZSFPKJMX_")===false)){
           // 老盘销;
            return 1 ;

        }
      
        if (!(strpos($fname,"_V10")===false)){
          //老盘 销税;
          return 1;

        }
      }
      if (!(strpos($fname,"_dk")===false)){
       // 进项
        return 2;

      }
    }else{
      return -1;
    }
   
    
}

function getfilename($fname){
  /**取消压缩文件及其他文件名中副本 (1) _dk 后面数字 */
  $postfix=substr($fname,strrpos($fname,'.') );
  $tab=0;
  if (strpos($fname,'- 副本')>0 ){    
      $fname=substr($fname,0,strpos($fname,'- 副本') );
      $tab=1;
  }
  if (strpos($fname,'(')>0){
    $tab=1;
    $fname=substr($fname,0,strpos($fname,'(') );
  }
  if (strpos($fname,'_dk')>0){
    $tab=1;
    $fname=substr($fname,0,strpos($fname,'_dk') );
    $fname.="_dk";
  }
  if ($tab==1){
    $fname=trim($fname).$postfix;
  }
  
  return $fname;

}
//需开启配置 php_zip.dll
//phpinfo();
//header("Content-type:text/html;charset=utf-8");
function get_zip_originalsize($filename, $filepath) {
  //未使用
  //先判断待解压的文件是否存在
  if(!file_exists($filename)){
  die("文件 $filename 不��在！");
  } 
  $starttime = explode(' ',microtime()); //解压开始的间
  
  //将文件名和路���转成windows系统默认的gb2312编码，否则将会���取不到
  $filename = iconv("utf-8","gb2312",$filename);
  $filepath = iconv("utf-8","gb2312",$filepath);
  //打开压缩包
  $resource = zip_open($filename);
  $i = 1;
  //遍历读取压缩包里面的一个个文件
  while ($dir_resource = zip_read($resource)) {
  //如果能打开则继续
  if (zip_entry_open($resource,$dir_resource)) {
    //获取��前项目的名称,即压缩包里面当前对应的文件名
    $file_name = $filepath.zip_entry_name($dir_resource);
    //以最后一个“/���分割,再用字符串截取出 径部分
    $file_path = substr($file_name,0,strrpos($file_name, "/"));
    //如果路径不存在则创建一个目录，true表示���以创建多级目录
    if(!is_dir($file_path)){
    mkdir($file_path,0777,true);
    }
    //如果不是目，则写入文件
    if(!is_dir($file_name)){
    //读取这个文件
    $file_size = zip_entry_filesize($dir_resource);
    //最大读取6M，如果文件过大，跳过解压，继续下一个
    if($file_size<(1024*1024*6)){
      $file_content = zip_entry_read($dir_resource,$file_size);
      file_put_contents($file_name,$file_content);
    }else{
      echo "<p> ".$i++." 此文件已被跳过，原因：文件过大， -> ".iconv("gb2312","utf-8",$file_name)." </p>";
    }
    }
    //关闭当前
    zip_entry_close($dir_resource);
  }
  }
  //关闭压缩包
  zip_close($resource); 
  $endtime = explode(' ',microtime()); //压结束的时间
  $thistime = $endtime[0]+$endtime[1]-($starttime[0]+$starttime[1]);
  $thistime = round($thistime,3); //保留3为小数
  echo "<p>��压完毕！，本次解压花费：$thistime 秒。</p>";
}
 /*程序流程
 *使用
 *1。检测、创建、读取文件夹
 *2、比对表中的文件、��建、更新表
 *使用的函数11个
 *CustomerAdd($row,$acctype,$addtype)
 *InputTaxExecl(string $file = '', int $sheet = 0, int $columnCnt = 0, &$options = []
 *InputTaxData($data)
 *CustomerTxt($fname)
 *CustomerXML($fname)
 *SaleInvXmlB($fname)
 *SaleInvXmlW($fname)
 *ReadInvFile($fnam,$filepath,$Inv_Type)
 *getinvtype($fname)
 *getfilename($fname)
 *get_zip_originalsize($filename, $filepath)
 */
 // 以下代码留存，已经废弃
 //压缩文件 检测 
  //$SQL="SELECT `uploadid`, `invtype`, `ziptype`, `filesinv`, `fileszip`,  `uploaddate`,filepath, `period`, `registerno`, `counter`,                 `remark`, `tag`,`flag`      FROM `invupload`    WHERE  abs(flag)<>2 and flag<>3 AND filepath='".$yeardir."'        ORDER BY ziptype";
  
 
  /*
  while($row=DB_fetch_array($TableResult)){

    if ($row['ziptype']=='zip' ){
      
      if (isset($FilesZip[$row['fileszip']])){
        
          $FilesZip[$row['fileszip']]['flag']=$row['flag'];
          $FilesZip[$row['fileszip']]['uploadid']=$row['uploadid'];
          $FilesZip[$row['fileszip']]['filepath']=$row['filepath'];
          $fileinv=explode(",",$row['filesinv']);
          if (count($fileinv)>=1){
            foreach($fileinv as $val){
              if (isset($FilesInv[$val])){
                $FilesInv[$val]['flag']=$row['flag'];
                $FilesInv[$val]['uploadid']=$row['uploadid'];
                $FilesInv[$val]['filepath']=$row['filepath'];
              }
            }
          }else{//��压文件不存在
            $SQL="UPDATE `invupload` SET `flag`=3 WHERE flag=0 AND  `fileszip`= '".$row['fileszip']."' AND `filepath`='".$yeardir."'";
            $result=DB_query($SQL);
          }        
      }else{
          //目录中不存在的文件，更新标记flag=2       
          $SQL="UPDATE `invupload` SET `flag`=2 WHERE flag=0 AND  `fileszip`= '".$row['fileszip']."' AND `filepath`='".$yeardir."'";
          $result=DB_query($SQL);
      }     
    }else{//普通文件
      if (isset($FilesInv[$row['filesinv']])){
       
        $FilesInv[$row['fileszip']]['flag']=$row['flag'];
        $FilesInv[$row['fileszip']]['uploadid']=$row['uploadid'];
        $FilesInv[$row['fileszip']]['filepath']=$row['filepath'];
      }else{
        //目录中不存在文件，更新标记flag=2
        $SQL="UPDATE `invupload` SET `flag`=2 WHERE flag=0 AND  `filesinv`= '".$row['filename']."' AND `filepath`='".$yeardir."'";
        $result=DB_query($SQL);
      }
    }  
  }
  //检测压缩文件夹中的文件
  foreach($FilesZip as  $fname=>$val){
   
    if ($val['flag']==-1){
      //文件存在，表中没有
    
      $InvType=getinvtype($fname);
     
      $dt=date("Y-m-d h:i:s");
      $ZIP  =  zip_open ($filepath.'/FilesZip/'.$fname);
      $fnamestr='';
      $unfiles=true;  
      if ( $ZIP ) {
        while ( $zip_entry  =  zip_read ( $ZIP )) {
       
            $zipname= zip_entry_name ($zip_entry );  
            //prnMsg($zipname);  
            if ($fnamestr==''){   
              $fnamestr.=$zipname;
            }else{
              $fnamestr.=",".$zipname;
            }
            if (isset($FilesInv[$zipname])){
              //prnMsg('//解压文件存在');
              $unzipfiles=false;   
              $FilesInv[$fname]['flag']++;             
              //zip_entry_close($zip_entry);
              //break;
            }
        }
        zip_entry_close($zip_entry);
        zip_close ( $ZIP );
        if ($unfiles){
             // prnMsg('//解压文件不存在                         ');
          $zip = new ZipArchive();//实例化ZipArchive类
          //打开压缩文件，打开成功时返回true
          if ($zip->open($filepath.'/FilesZip/'.$fname ) === true) {
                //解压文件到获得的路径a文件夹下
                $zip->extractTo($filepath.'FilesInv/');                       
                $zip->close(); //关闭
                //���入数据表
                $dt=date("Y-m-d h:i:s");
          }
        
                $SQL="INSERT INTO `invupload`(`invtype`, 
                                              `ziptype`,
                                              `filesinv`,
                                              `fileszip`,
                                              filepath,
                                              `uploaddate`,
                                              `registerno`,
                                                period,
                                              `tag`,
                                              `counter`,                                            
                                              `remark`,
                                              `flag`)
                                          VALUES('".$InvType."',
                                                  'zip',
                                                  '".$fnamestr."',
                                                  '".$fname."',
                                                  '".$yeardir."',
                                                  '".$dt."',
                                                  '".$_SESSION['CompanyRecord'][1]['companynumber']."',
                                                  '',
                                                  '1',
                                                  '0',                                            
                                                  '',
                                                  '0')";
                 
            $Result=DB_query($SQL);
          if(DB_affected_rows($Result)>0){
            $uploadid=DB_Last_Insert_ID($db,'invupload','uploadid');
            $FilesZip[$fname]['flag']=0;
            $FilesZip[$fname]['uploadid']=$uploadid;
            $FilesZip[$fname]['filepath']=$yeardir;
            $fileinv=explode(",",$fnamestr);
            if (count($fileinv)>=1){
              foreach($fileinv as $val){
                if (isset($FilesInv[$val])){
                  $FilesInv[$val]['flag']=$row['flag'];
                  $FilesInv[$val]['uploadid']=$row['uploadid'];
                  $FilesInv[$val]['filepath']=$row['filepath'];
                }
              }
            }
          }
        }
    
      }
    } 
  } //end foreach
  
  //检测普通文件    
  foreach($FilesInv as  $fname=>$val){
    // prnMsg($val['flag']);
    if ($val['flag']==-1){
        $InvType=getinvtype($fname);
        ///$filename=$row['fileszip'];
        $postfix=substr($fname,strrpos($fname,'.')+1 );
        $SQL="INSERT IGNORE INTO  `invupload`(`invtype`,
                                      `ziptype`,
                                      `filesinv`,
                                      `fileszip`,
                                      filepath,
                                      `uploaddate`,
                                      `registerno`,
                                      period,                                    
                                      `tag`,
                                      `counter`,                                            
                                      `remark`,
                                      `flag`)
                                  VALUES('".$InvType."',
                                          '".$postfix."',
                                          '".$fname."',
                                          '".$fname."',
                                          '".$yeardir."',
                                          '".date("Y-m-d h:i:s")."',
                                          '".$_SESSION['CompanyRecord'][1]['companynumber']."',
                                          '',
                                          '1',
                                          '0',                                               
                                          'inv',
                                          '0')";
                              
            $Result=DB_query($SQL);
        if(DB_affected_rows($Result)>0){//
          $uploadid=DB_Last_Insert_ID($db,'invupload','uploadid');
          $FilesInv[$fname]['flag']=0;
          $filesInv[$fname]['uploadid']=$uploadid;
          $FilesInv[$fname]['filepath']=$yeardir;
        }
    
    }
  }  */
  //unset($FilesZip);
  //unset($FilesInv);
  //var_dump($FilesZip);
  //var_dump($FilesInv);
  ?>
  