<?php
require_once(REQUIER_PASS . "/UserAutoAssessments/IshikawaUserAssessment.php");
require_once(REQUIER_PASS . "/UserAutoAssessments/OriginalUserAssessment.php");
require_once(REQUIER_PASS . "/UserAutoAssessments/TeradaUserAssessment.php");

class UserAssessment {
  private $mysqli;
    
  /**
   * 最終更新 7/19
   * ユーザの実力を計算する
   * 一通り出来ている（手計算で確認済み）
   * @param user_id ユーザのID
   */
  public final function Assessment($user_id) {
      $this->mysqli = DatabaseConnection();
      $ability_score = $this->getAbilityScore($user_id);
      $new_ability_score = $this->AbilityScoreCalculation($user_id, $ability_score);
      //$this->updateNewAbilityScore($user_id, $new_ability_score);
      $this->mysqli->close();
  }

  
  /**
   * 引数のuser_idと一致するユーザの実力値（ability_score）を取ってくる
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
   * ユーザの実力を計算する
   * @param user_id            ユーザID
   * @param ability_score      ユーザの実力
   * @return new_ability_score 新しいユーザの実力値
   */
   private final function AbilityScoreCalculation($user_id, $ability_score) {
       $new_ability_score = 0;
       //降順でユーザの履歴を取得する
       $query = "SELECT * FROM status WHERE user_id = '$user_id' ORDER BY create_at DESC";
       $user_status = $this->getHistory($query);
       $delta_count = 0;
       //$user_statusに最新３０件全てが入っているので
       //それを一つずつ計算していく
       printf("ユーザの実力：$ability_score <br>");
       while ($status = $user_status->fetch_assoc()) {
           $query = "SELECT difficult FROM questions WHERE id = '" . $status["question_id"] . "';";
           $query_result = $this->mysqli->query($query);
           //テーブルの要素を抜き出す
           while($row = $query_result->fetch_assoc()) {
               $difficult = $row['difficult'];
           }
           //ここを切り替えればいつでも自由にできる
           //list($difficult, $delta) = UserDeltaFlag($difficult, $ability_score ,$status);
           //list($difficult, $delta) = OrignalUserDeltaFlag($difficult, $ability_score, $status);
           list($difficult, $delta) = TeradaUserDeltaFlag($difficult, $ability_score, $status);
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
  private final function getHistory($query) {
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
  private final function updateNewAbilityScore($user_id, $new_ability_score) {
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