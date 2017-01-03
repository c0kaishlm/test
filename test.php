<?php

header('Content-Type: text/html; charset=utf8');

//'http://211.144.154.195/upload.php';


$fields = array(
    "teacher_whiteboard_data" => array("fieldname" => "t_voice_whiteboard", "type" => 1),
    "student_audio" => array("fieldname" => "sound_student", "type" => 2),
    "teacher_audio" => array("fieldname" => "sound_teacher", "type" => 3),
    "thumbnail_img" => array("fieldname" => "tutor_purview", "type" => 4),
    "video" => array("fieldname" => "voice_video", "type" => 5),
    "audio" => array("fieldname" => "audio", "type" => 6),
    "thumbnail_small" => array("fieldname" => "thumbnail_small", "type" => 7),
    "student_whiteboard_data" => array("fieldname" => "s_voice_whiteboard", "type" => 8),
);


include './Snoopy.class.php';
$snoopy = new Snoopy();
$serviceUrl = 'http://211.144.154.195/upload.php';

$formvars["question_id"] = '2634209';
//$formvars["userid"] = '10795989';
$time = time();
$key = "aaa";
$formvars["key"]  =$key;
$formvars["timestamp"]  =$time;
$formvars["signature"]  = md5($key . $time . 'student101dayi');

$postfiles["file"] = './upload/7411.png';   //文件要带上路径
//$snoopy->curl_path = '/usr/bin/curl';       //注意你的linux里的curl路径
$snoopy->set_submit_multipart();            //设定post方式
if ($snoopy->submit($serviceUrl, $formvars, $postfiles)) {
    echo '<pre>' . htmlspecialchars($snoopy->results) . '</pre>';
} else {
    echo "error fetching document: " . $snoopy->error . "\n";
}
echo '<pre>';
var_dump($snoopy);



















//try{
//    $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
//    $dbh->beginTransaction();//开启事务
//    $dbh->exec($sql1);
//    $dbh->exec($sql2);
//    $dbh->commit();//提交事务
//}catch(Exception$e){
//    $dbh->rollBack();//错误回滚
//    echo"Failed:".$e->getMessage();
//}



?>