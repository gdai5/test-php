<?php
/**
 * 最終更新：7/8
 * 正誤判定と自動評価を組み合わせた動作確認プログラム
 */
require_once("AutoJudge.php");
require_once("./construct/const.php");

//初期化
$auto_judge  = new AutoJudge();
$user_id     = 8;
$question_id = 2;

$auto_judge->Judge($user_id, $question_id);

?>