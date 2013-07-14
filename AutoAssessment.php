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
      $this->mysqli->close();
  }
  
  /**
   * 問題の難易度を計算する
   */
  public final function QuestionAssessment() { 
  }
  
  
}

?>