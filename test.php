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
$auto_assessment = new AutoAssessment();
$user_id     = 1;
$question_id = 1;

//一時フォルダの作成およびコンパイル
$auto_judge->mkDirectory($user_id, $question_id);
if($auto_judge->Compile() == flase) {
    exit;
}

//テストデータを入れる配列の生成
$input_datas  = array();
$output_datas = array();


?>