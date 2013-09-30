<?php
/**
 * 2013-09-25
 * まずはこちらを、statusの変更に伴い変形させる
 */
//各計算式の読み込み
require_once(REQUIER_PASS . "/UserAutoAssessments/OriginalUserAssessment.php");
//require_once(REQUIER_PASS . "/UserAutoAssessments/IshikawaUserAssessment.php");
//require_once(REQUIER_PASS . "/UserAutoAssessments/TeradaUserAssessment.php");

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
  private $mysqli;
    
  /**
   * 最終更新 7/19
   * Main
   * ユーザの実力を計算する
   * 一通り出来ている（手計算で確認済み）
   * @param user_id ユーザのID
   */
  public final function Assessment($user_id, $users_ability_score_transition) {
      $this->mysqli = DatabaseConnection();
      //ユーザの実力を取得する（ただし、シミュレーションでは不要）
      $ability_score = $this->getAbilityScore($user_id);
      //ユーザの新規追加の場合だけ通る
      if (!array_key_exists($user_id, $users_ability_score_transition)) {
          $users_ability_score_transition["$user_id"] = array($ability_score);
      } 
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
   * 引数のuser_idと一致するユーザの実力値（ability_score）をDBから取ってくる
   * シミュレーションでは不要
   * @param user_id        ユーザID
   * @return ability_score ユーザの実力値
   */
  private final function getAbilityScore($user_id) {
      $query = "SELECT ability_score FROM users WHERE id = '$user_id';";
      $query_result = $this->mysqli->query($query);
      while($row = $query_result->fetch_assoc()) {
          $ability_score = $row["ability_score"];
      }
      return $ability_score;
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
       $query = "SELECT * FROM status WHERE user_id = '$user_id' ORDER BY created_at DESC";
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
           //list($difficult, $delta) = ishikawaUserDeltaFlag($difficult, $ability_score ,$status);
           //$delta = orignalUserDeltaFlag($difficult, $ability_score, $status);
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
  

  /**
   * @param query MySQLの命令文
   * @return status statusテーブルから条件に合った要素が入っている連想配列
   */
  private final function getUserStatusHistory($query) {
      $status = $this->mysqli->query($query);
      if(!$status) {
         exit('問題の履歴取得に失敗しました'. $this->mysqli->error);
      }
      return $status;
  }
  
  /**
   *　新しく計算された実力をusersテーブルに更新する
   * @param user_id ユーザID
   * @param new_ability_score 再計算した実力値
   */
  private final function updateUserAbilityScore($user_id, $new_ability_score) {
      $query = "update users set ability_score = '$new_ability_score' where id = '$user_id';";
      $this->updateColumn($query);
  }
  
  
  /**
   * 実力or難易度のupdate用の関数
   * @param query mysqlへの命令 
   */
  private final function updateColumn($query) {
      $update_flag = $this->mysqli->query($query);
      if(!$update_flag) {
          exit("失敗しました" . $this->mysqli->error);
      } 
  }
  
  
}

?>