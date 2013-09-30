<?php

/**
 * 2013-09-29
 * データセットファイルからデータ読み込み、実力と難易度を計算するシミュレーションのメイン
 * 処理の流れとして
 * １：データセットファイルから一行ずつ読み込む
 * ２：一行ずつstatusテーブルに書き込む
 * ３：statusテーブルに書き込みが行われたら、実力と難易度の計算を走らせる
 * ４：計算後の値をユーザもしくは問題毎に逐次保存していく
 * ５：これをデータセット数繰り返す
 */
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";

require_once("/Users/ishikawahitoshi/Sites/test-php/construct/const.php");
require_once(REQUIER_PASS . "/UserAutoAssessments/UserAssessment.php");
require_once(REQUIER_PASS . "/QuestionAutoAssessments/QuestionAssessment.php");

function writeStatusTable($data, $mysqli) {
    list($user_id, $question_id, $result, $testdata_num, $correct_testdata_num) = explode(",", $data);
    //テーブルの情報を更新するのか、それとも新しく追加するのか
    $update_flag = chkSameColum($user_id, $question_id, $mysqli);
    if(!$update_flag) { //新規追加
        $query = "INSERT INTO status (user_id, question_id, result, testdata_num, correct_testdata_num, created_at) 
                              VALUES ('$user_id', '$question_id', '$result', '$testdata_num', '$correct_testdata_num', now());";  
    }else{ //更新
        $query = "update status set result = '$result', testdata_num = '$testdata_num', 
                                    correct_testdata_num = '$correct_testdata_num', created_at = now() 
                                    where user_id = '$user_id' and question_id = '$question_id';";
    }
      
    $char_set_flag = $mysqli->query($query);
    if(!$char_set_flag) {
      exit('失敗しました。'. $this->mysqli->error);
    }
    return array($user_id, $question_id);
}
//statusテーブルでuser_id,question_idの二つが一致するものは更新処理する
function chkSameColum($user_id, $question_id, $mysqli) { 
      $query = "SELECT id FROM status WHERE question_id = '$question_id' and user_id = '$user_id';";
      $status_colum = $mysqli->query($query);
      if($status_colum->fetch_assoc() == null) {
          //printf("追加します");
          return false;
      }
      //printf("更新します");
      return true;
}


//ユーザの実力の推移をユーザ毎にファイルで保存
function makeAbilityScoreTransitionFile($users_ability_score_transition) {
    foreach ($users_ability_score_transition as $user_id => $ability_transition) {
        $fp = fopen("./UserAbilityScoreTransition/User$user_id.txt", "w");
        for($i = 0; $i < count($ability_transition); $i++) {
            fwrite($fp, "$ability_transition[$i]\n");
        }
        fclose($fp);
    }
}

//問題の難易度の推移を問題毎にファイルで保存
function makeDifficultTransitionFile($questions_difficult_transition) {
    foreach ($questions_difficult_transition as $question_id => $difficult_transition) {
        $fp = fopen("./QuestionDifficultTransition/Question$question_id.txt", "w");
        for($i = 0; $i < count($difficult_transition); $i++) {
            fwrite($fp, "$difficult_transition[$i]\n");
        }
        fclose($fp);
    }
}


/**
 * ここがシミュレーションを走らせているMainの場所
 */
//実力を計算
$user_assessment     = new UserAssessment();
//難易度を計算
$question_assessment = new QuestionAssessment();

$i = 1;

//ファイルからデータセットを読み込む
$fp = fopen("test-data-set.txt", "r");

//データベースと接続
$mysqli = DatabaseConnection();
//ユーザ毎の実力の推移を記録するHash
//$users_ability_score_transition = {user_id => [3.4, 4.5, 4.2...]}
$users_ability_score_transition = array();
//問題毎の難易度の推移を記録するHash
//$questions_difficult_transition = {question_id => [3.4, 4.5, 4.2...]}
$questions_difficult_transition = array();

while ($data = fgets($fp)) {
  //statusテーブルに書き込む
  list($user_id, $question_id) = writeStatusTable($data, $mysqli);
  
  printf($i . "回目<br>");
  printf("ユーザID = $user_id<br>");
  $users_ability_score_transition = $user_assessment->Assessment($user_id, $users_ability_score_transition);
  printf("問題ID = $question_id<br>");
  $questions_difficult_transition = $question_assessment->Assessment($question_id, $questions_difficult_transition);        
  printf("-------------------------------------------<br>");
  $i++;
}
//ユーザ毎の推移をファイルで保存
makeAbilityScoreTransitionFile($users_ability_score_transition);
//問題毎の推移をファイルで保存
makeDifficultTransitionFile($questions_difficult_transition);

$mysqli->close();
fclose($fp);
 
?>