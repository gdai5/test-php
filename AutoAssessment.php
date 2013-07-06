<?php
require_once("./construct/const.php");

class AutoAssessment {
  private $mysqli;
    
  /**
   * ユーザの実力を計算する
   */
  public final function UserAssessment() {
  }
  
  /**
   * 問題の難易度を計算する
   */
  public final function QuestionAssessment() { 
  }
  
  /**
   * ユーザの実力もしは問題の難易度のどちらかに変更が生じた場合
   * DBの対応する部分（スコア）を更新する
   */
  private final function writeStatus() {
  }
  
  /**
   * DBとの接続を行う
   * @return mysqli DBとの接続をやり取りする変数
   */
  private final function DatabaseConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if($mysqli->connect_errno) {
      print("エラーが発生しました。");
      exit;
    }
    print("接続おk<br>");
    return $mysqli;
  }
  
  
}

?>