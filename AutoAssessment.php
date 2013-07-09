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
      $this->mysqli = $this->DatabaseConnection();
      $this->mysqli->close();
  }
  
  /**
   * 問題の難易度を計算する
   */
  public final function QuestionAssessment() { 
  }
  
  /**
   * 完成：7/7
   * statusテーブルの情報を更新もしくは新規で追加する
   * @param user_id         ユーザのID
   * @param question_id     問題のID
   * @param judge_result    判定結果（Accepted, Wrong Answer, Compile Error, Runtime Error, Time Out, Output Limit Exceeded）
   * @param correct_answers 正解したテストデータの数
   */
  public final function writeStatus($user_id, $question_id, $judge_result, $correct_answers) {
      //$this->mysqli = $this->DatabaseConnection();

      $result_flag = $this->mysqli->query('SET NAMES utf8'); //文字コードの指定(UTF-8)
      if(!$result_flag) {
        $this->mysqli->close();
        exit('文字コードを指定できませんでした。');
      }
      
      //テーブルの情報を更新するのか、それとも新しく追加するのか
      $update_flag = $this->chkSameColum($user_id, $question_id);
      if(!$update_flag) { //新規追加
        //エラーの原因は変数をシングルクウォテーションで囲ってなかったため
        $query = "INSERT INTO status (user_id, question_id, result, correct_answers ,create_at) VALUES ('$user_id', '$question_id', '$judge_result', '$correct_answers', now());";  
      }else{ //更新
        $query = "update status set result = '$judge_result', correct_answers = '$correct_answers', create_at = now() where user_id = '$user_id' and question_id = '$question_id';";
      }
      
      $result_flag = $this->mysqli->query($query);
      if(!$result_flag) {
        exit('失敗しました。'. $this->mysqli->error);
      }
      //$this->mysqli->close();
  }
  
  /**
   * 完成：7/7
   * statusテーブルで既にuser_idとquestion_idの二つが一致しているものがあった場合
   * statusテーブルのresultの項目だけ、更新する。
   * そうでない場合は、新規で追加する。
   * @param user_id     ユーザのID
   * @param question_id 問題のID
   * @return false この場合には、テーブルに新しく要素を追加する（つまり、初めてその問題を解いたということ）
   *     or  true  この場合には、テーブルのresult項目だけを更新する
   *               更新は　Accepted > Wrong Anwser, Time Out, Output Limit Exceeded > Runtime Error > Compile Error
   *               となり、今の結果から左に行く場合は更新が発生する。右に行く場合は更新を行わない。
   */
  private final function chkSameColum($user_id, $question_id) { 
      $query = "SELECT id FROM status WHERE question_id = '$question_id' and user_id = '$user_id';";
      $status_colum = $this->mysqli->query($query);
      if($status_colum->fetch_assoc() == null) {
          printf("追加します");
          return false;
      }
      printf("更新します");
      return true;
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