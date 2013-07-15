<?php
require_once("./construct/const.php");

class AutoAssessment {
  private $mysqli;
    
  /**
   * 最終更新
   * ユーザの実力を計算する
   * @param user_id ユーザのID
   */
  public final function UserAssessment($user_id) {
      $this->mysqli = DatabaseConnection();
      $ability_score = $this->getAbilityScore($user_id);
      /*
       * ここに実力計算を行う式を書く
       */
      $this->setNewAbilityScore($user_id, $ability_score);
      $this->mysqli->close();
  }
  
  /**
   * 問題の難易度を計算する
   */
  public final function QuestionAssessment() { 
  }
  
  /**
   * 引数のuser_idと一致するユーザの実力値（ability_score）を取ってくる
   * @param user_id
   * @return ability_score ユーザの実力値
   */
  private final function getAbilityScore($user_id) {
      $query = "SELECT ability_score FROM users WHERE id = '$user_id';";
      $query_result = $this->mysqli->query($query);
      while($row = $query_result->fetch_assoc()) {
          $ability_score = $row['ability_score'];
      }
      return $ability_score;
  }
  
  /**
   *　新しく計算された実力をusersテーブルに更新する
   * @param user_id ユーザID
   * @param ability_score 実力値
   */
  private final function setNewAbilityScore($user_id, $ability_score) {
      $query = "update users set ability_score = '$ability_score' where id = '$user_id';";
      $update_flag = $this->mysqli->query($query);
      if(!$update_flag) {
        exit('失敗しました。'. $this->mysqli->error);
      }
  }
  
  
}

?>