<?php
/**
 * 最終更新：7/8
 * 正誤判定と自動評価を組み合わせた動作確認プログラム
 */
require_once("AutoJudge.php");
require_once("AutoAssessment.php");
require_once("./construct/const.php");

//初期化
$auto_judge      = new AutoJudge();
$user_id     = 5;
$question_id = 2;
$judge_result = "";
$correct_answers = 0;
$testdatas_num = 0;

//一時フォルダの作成およびコンパイル
$auto_judge->mkDirectory($user_id, $question_id);
if($auto_judge->Compile() == false) {
    $judge_result = "Compile Error";
    $auto_judge->writeStatus($user_id, $question_id, $judge_result, $testdatas_num, $correct_answers);
    exit;
}

//テストデータを入れる配列の生成
$input_datas  = array();
$output_datas = array();
list($input_datas, $output_datas) = $auto_judge->getTestData($question_id);

//実行
$program_outputs = array();
$program_outputs = $auto_judge->Run($input_datas);
if($program_outputs[0] == "Run time Error"){
    $judge_result = "Run time Error";
    $auto_judge->writeStatus($user_id, $question_id, $judge_result, $testdatas_num, $correct_answers);
    exit;
}

//正誤比較
list($correct_answers, $judge_result) = $auto_judge->Judgement($program_outputs, $output_datas);

//DBに結果の書き込み
$testdatas_num = count($output_datas);
$auto_judge->writeStatus($user_id, $question_id, $judge_result, $testdatas_num, $correct_answers);

?>