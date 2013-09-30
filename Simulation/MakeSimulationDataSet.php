<?php
/**
 * このプログラムはstatusに書き込むべき情報を（１００００件）をテーブルで表示するプログラム
 * 表示内容：ユーザID, 問題ID, 正解率, 結果, その問題の全テストデータ数, ユーザが正解したテストデータ数
 */
 
/**
 * 2013-09-26
 * user = 100, question = 100までのセットの導入まで完了
 */
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
require_once("./NomalUserModel.php");

define("COMPILE_ERROR", 0);
define("RUNTIME_ERROR", 1);
define("NOT_CORRECT", 2);
define("CLOSE_ANSWER", 3);
define("ACCEPTED", 4);

//NomalUserModel.php Test
//$data_set = [[user_id, question_id]....]
$data_set = array();
for($i = 0; $i < 5; $i++) {
    $data = array(mt_rand(1, 5), mt_rand(1, 5));
    array_push($data_set, $data);
}
//print_r($data_set);
$nomal_user_model = new NomalUserModel();
//ここにモデルのデータを一つずつ入れていく
//$model_datas = [User_id, Question_id, true_ability, true_difficult, testdata_num]
$nomal_user_model->run($data_set);

?>