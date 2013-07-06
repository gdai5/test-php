<?php
/*
 * 自動正誤判定のメインプログラム
 * 最終更新6-19
 */
require_once("AutoJudge.php");
require_once("./construct/const.php");

/*
 * テストデータを取得する変数群
 */
 
$auto_judge = new AutoJudge();

//自動正誤判定に用いる変数群
$judge_result = array();
//$err_msg = String;
$crear_count;

//一時フォルダの生成およびMain.javaが生成されているかチェック
 if(!$auto_judge->CreateFolder()) {
   echo "ファイルもしくはフォルダが上手く生成されませんでした";
   exit;
 }

if(!$auto_judge->Compile("directory_pass")) {
  echo "コンパイルエラー"."<br>";
  exit;
}

//echo "コンパイル成功<br>";

//ここから6/19続き
//問題番号に応じて、テストデータを取ってくる
//(5/8)
//各ファイルの中身を順番に対応させて取得する配列
$input_datas  = array();
$output_datas = array();
$question_id = 1;
list($input_datas, $output_datas) = $auto_judge->getTestData($question_id);

// for($i = 0; $i < count($input_datas); $i++) {
  // print "inputdata$i : " . $input_datas[$i];
  // print "<br>";
// }
// for($i = 0; $i < count($output_datas); $i++) {
  // print "outputdata$i : " . $output_datas[$i];
  // print "<br>";
// }

//echo "<br>";
//echo "実験<br>";

list($judge_result, $judge_error) = $auto_judge->Run("directory_pass", $input_datas);
if($judge_error == RUN_TIME_ERROR) {
  echo "実行時エラー"."<br>";
  exit;
}

//echo "実行成功<br>";
$crear_count = $auto_judge->Judgement($judge_result, $output_datas);
echo count($output_datas) . "個中　：" . $crear_count . "個正解";

?>



