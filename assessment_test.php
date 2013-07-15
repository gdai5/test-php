<?php
/**
 * 最終更新：7/8
 * 正誤判定と自動評価を組み合わせた動作確認プログラム
 */
require_once("AutoJudge.php");
require_once("AutoAssessment.php");
require_once("./construct/const.php");

//初期化
$auto_assessment = new AutoAssessment();
$user_id     = 1;
$question_id = 2;
$judge_result = "";
$correct_answers = 0;
$testdatas_num = 0;

$auto_assessment->UserAssessment($user_id);

?>