<?php
/*
 * 
 */
require_once("AutoJudge.php");
require_once("./construct/const.php");

 $input_files  = array();
 $output_files = array();
 $input_datas  = array();
 $output_datas = array();
 $input_row;
 $output_row;
$judge_result = array();
$judge_error;
$judge = new AutoJudge();

//javaのコンパイル＆実行
//正しく動く（5/10）
 // list($judge_result, $judge_error) = $judge->createFolderAndMainfile();
 // //var_dump($judge_result);
 // //var_dump($judge_error);
 // switch ($judge_error) {
     // case COMPILE_ERROR:
         // echo "コンパイルエラー"."<br>";
         // break;
     // case RUN_TIME_ERROR:
         // echo "実行時エラー<br>";
         // break;
     // default:
         // for($i = 0; $i < count($judge_result); $i++) {
           // echo $judge_result[$i];
           // echo "<br>";
          // }
         // break;
 // }
 
 
 
//問題番号に応じて、テストデータを取ってくる
//(5/8)
$question_id = 1;
list($input_files, $output_files, $input_row, $output_row) = $judge->getTestDatafiles($question_id);

for($i = 0; $i < count($input_files); $i++) {
  print $input_files[$i] . "　：　" .  $output_files[$i];
  print "<br>";
}

//question_idに対応テストデータファイルの中身を引っ張る
//(5/17)
list($input_datas, $output_datas) = $judge->readTestdatas($input_files, $output_files);
for($i = 0; $i < count($input_datas); $i++) {
  print "inputdata$i : " . $input_datas[$i];
  print "<br>";
}
for($i = 0; $i < count($output_datas); $i++) {
  print "outputdata$i : " . $output_datas[$i];
  print "<br>";
}

// list($judge_result, $judge_error) = $judge->CompileAndRun($directory_pass);

// if(!$judge->fileExist()) {
  // echo"file not exist\n";
  // exit;
// }

//ファイルの中身を一行ずつ読み込む
// if(is_file($file_name)){ 
  // $text = fopen($file_name,'r'); 
  // while(!feof($text)){ 
    // $lines = fgets($text);
    // if($lines){
      // print $lines;
    // }
  // }
  // fclose($text);
  // echo"\n";
// }else{
  // print 'ファイルがありません';
  // echo "\n";
  // exit;
// } 

?>