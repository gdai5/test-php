<?php

//シミュレーションに必要なデータを入れるプログラム
//2013-09-29

echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
require_once("/Users/ishikawahitoshi/Sites/test-php/construct/const.php");

function insert_users_data($mysqli) {
    for($i = 1; $i <= 100; $i++) {
      $id = $i;
      $user_name = "user-$id";
      $password = "b";
      $ability_score = mt_rand(1, 10);
      $query = "INSERT INTO users (id, username, password, ability_score) 
                                VALUES ('$id', '$user_name', '$password', '$ability_score')";
      $char_set_flag = $mysqli->query($query);
      if(!$char_set_flag) {
        exit('失敗しました。'. $mysqli->error);
      }
    }
}

function insert_questions_data($mysqli) {
    for($i = 1; $i <= 100; $i++) {
      $id = $i;
      $title = "question-$id";
      $text = "b";
      $difficult = mt_rand(1, 10);
      $user_id = 1;
      $testdata_num = mt_rand(1, 40);
      $query = "INSERT INTO questions (id, title, text, difficult, user_id, testdata_num) 
                                VALUES ('$id', '$title', '$text', '$difficult', '$user_id', '$testdata_num')";
      $char_set_flag = $mysqli->query($query);
      if(!$char_set_flag) {
        exit('失敗しました。'. $mysqli->error);
      }
    }
}

function insert_true_ability_score_data($mysqli) {
    for($i = 1; $i <= 100; $i++) {
      $id = $i;
      $user_id = $id;
      $r = mt_rand(1, 10);
      $r = $r / 10;
      $true_ability_score = mt_rand(1, 9) + $r;
      $query = "INSERT INTO true_ability_score (id, user_id, true_ability_score) 
                                VALUES ('$id', '$user_id', '$true_ability_score')";
      $char_set_flag = $mysqli->query($query);
      if(!$char_set_flag) {
        exit('失敗しました。'. $mysqli->error);
      }
    }
}

function insert_true_difficult_data($mysqli) {
    for($i = 1; $i <= 100; $i++) {
      $id = $i;
      $question_id = $id;
      $r = mt_rand(1, 10);
      $r = $r / 10;
      $true_difficult = mt_rand(1, 9) + $r;
      $query = "INSERT INTO true_difficult (id, question_id, true_difficult) 
                                VALUES ('$id', '$question_id', '$true_difficult')";
      $char_set_flag = $mysqli->query($query);
      if(!$char_set_flag) {
        exit('失敗しました。'. $mysqli->error);
      }
    }
}

$mysqli = DatabaseConnection();
//usersに１００件導入
//insert_user_data($mysqli);
//questionsに１００件導入
//insert_questions_data($mysqli);
//true_ability_scoreに１００件導入
//insert_true_ability_score_data($mysqli);
//insert_true_difficult_data($mysqli);
$mysqli->close();
printf("無事成功しました");

?>