<?php
/**
 * 2013-10-03
 * シミュレーション用の実力評価
 */
//各計算式の読み込み
//require_once(REQUIER_PASS . "/UserAutoAssessments/OriginalUserAssessment.php");
//require_once(REQUIER_PASS . "/UserAutoAssessments/IshikawaUserAssessment.php");
require_once(REQUIER_PASS . "/UserAutoAssessments/TeradaUserAssessment.php");

/**
 * DBと文字列比較を行う予定だったが、それでは遅いので
 * 数字で表すように変更
 */
define("COMPILE_ERROR", 0);
define("RUNTIME_ERROR", 1);
define("NOT_CORRECT", 2);
define("CLOSE_ANSWER", 3);
define("ACCEPTED", 4);

class UserAssessment {
  
    
  /**
   * そのユーザの履歴と真の実力および、挑戦した真の問題の難易度を受け取り
   * 計算後のユーザの実力返す関数である
   */
  public final function Assessment($user_history, $true_ability_score, $true_difficult) {
      
      //履歴を基にユーザの新しい実力を取得する
      $new_ability_score = $this->abilityScoreCalculation($user_id, $ability_score);
      //実力の推移を記録
      array_push($users_ability_score_transition["$user_id"], $new_ability_score);
      //ユーザの実力を更新
      $this->updateUserAbilityScore($user_id, $new_ability_score);
      $this->mysqli->close();
      return $users_ability_score_transition;
  }
  
  
  /**
   * statusテーブルから、そのユーザの最新３０件を取得しそれを用いてユーザの新しい実力を計算する
   * $delta_countは$delta > 0になった回数
   * @param user_id            ユーザID
   * @param ability_score      ユーザの実力
   * @return new_ability_score 新しいユーザの実力値
   */
   private final function abilityScoreCalculation($user_id, $ability_score) {
       $new_ability_score = 0;
       //降順でユーザの履歴を取得する
       $query = "SELECT * FROM status WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 30";
       //最新３０件の履歴を取得する
       $user_status_history = $this->getUserStatusHistory($query);
       $delta_count = 0;
       printf("ユーザの実力：$ability_score <br>");
       while ($status = $user_status_history->fetch_assoc()) {
           $query = "SELECT difficult FROM questions WHERE id = '" . $status["question_id"] . "';";
           $query_result = $this->mysqli->query($query);
           //テーブルの要素を抜き出す
           while($row = $query_result->fetch_assoc()) {
               $difficult = $row['difficult'];
           }
           
           //ここを切り替えればいつでも自由にできる
           //$delta = orignalUserDeltaFlag($difficult, $ability_score, $status);
           //list($difficult, $delta) = ishikawaUserDeltaFlag($difficult, $ability_score ,$status);
           $delta = teradaUserDeltaFlag($difficult, $ability_score, $status);
           
           if($delta > 0) {
               $new_ability_score += ($difficult - $ability_score) * $delta;
               $delta_count++;
           }
       }
       if($delta_count > 0) {
           $new_ability_score = $new_ability_score / $delta_count;
       }
       $new_ability_score += $ability_score;
       printf("新しいユーザの実力＝$new_ability_score<br>");
       return $new_ability_score;
   }
  
}

?>