<?php
/*
 * 自動評価
 * 最終更新7-6
 */
require_once("AutoAssessment.php");
require_once("./construct/const.php");

$auto_assessment = new AutoAssessment();
$user_id      = 1;
$question_id  = 4;
$judge_result = "Accepted";
$correct_answers = 3;
//$judge_result = "Wrong Answer";
//$judge_result = "Runtime Error";
//$judge_result = "Compile Error";
//$judge_result = "Time Out";

$auto_assessment->writeStatus($user_id, $question_id, $judge_result, $correct_answers);


?>
