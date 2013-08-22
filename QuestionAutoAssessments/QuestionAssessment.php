<?php

class QuestionAssessment {
  private $mysqli;
   
  /**
   * まだデバックしていないので、要動作確認（7/23）
   * 問題の難易度を計算する
   */
  public final function Assessment($question_id) {
      $this->mysqli = DatabaseConnection();
      $difficult = $this->getDifficult($question_id);
      //printf("難易度＝$difficult");
      $new_difficult = $this->DifficultCalculation($question_id, $difficult);
      //$this->updateNewDifficult($question_id, $new_difficult);
      $this->mysqli->close(); 
  }
  
  
  /**
   * 引数のquestion_idと一致する問題の難易度（difficult）を取ってくる
   * @param question_id    問題ID
   * @return difficult     難易度
   */
  private final function getDifficult($question_id) {
      $query = "SELECT difficult FROM questions WHERE id = '$question_id';";
      $query_result = $this->mysqli->query($query);
      while($row = $query_result->fetch_assoc()) {
          $difficult = $row["difficult"];
      }
      return $difficult;
  }
  
  /**
   * 問題の難易度を計算する
   * @param question_id    問題ID
   * @param difficult      難易度
   * @return new_difficult 再計算された難易度
   */
  private final function DifficultCalculation($question_id, $difficult) {
       $new_difficult = 0;
       $delta_count = 0;
       //降順でユーザの履歴を取得する
       $query = "SELECT * FROM status WHERE question_id = '$question_id' ORDER BY create_at DESC";
       $question_status = $this->getHistory($query);
       //$question_statusに最新３０件全てが入っているので
       //それを一つずつ計算していく
       printf("問題の難易度：$difficult <br>");
       while ($status = $question_status->fetch_assoc()) {
           $query = "SELECT ability_score FROM users WHERE id = '" . $status["user_id"] . "';";
           $query_result = $this->mysqli->query($query);
           //テーブルの要素を抜き出す
           while($row = $query_result->fetch_assoc()) {
               $ability_score = $row['ability_score'];
           }
           printf("ユーザ" . $status["user_id"] . "　:　実力　＝" . $ability_score . "<br>");
           $delta = $this->QuestionDeltaFlag($difficult, $ability_score, $status);
           printf("結果＝" . $status["result"] . "  δ＝$delta <br>");
           if($delta != 0) {
               $new_difficult += $ability_score - $difficult;
               $delta_count++;
           }
       }
       if($delta_count != 0) {
           $new_difficult = $new_difficult / $delta_count;
       }
       $new_difficult += $difficult;
       printf("新しい問題の難易度＝$new_difficult<br>");
       return $new_difficult;
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
   * 難易度の計算式においてδが1 or 0かをチェックする
   * 失敗として認識するもの：Compile Error, Runtime Error, Time Out, Output Limit Exceeded, Wrong Answer
   * 成功として認識するもの：Accepted
   * @param difficult 問題の難易度
   * @param ability_score ユーザの実力
   * @param $status       statusテーブル１行分の情報が入っている連想配列
   * @return delta 計算をする場合は1, そうでない場合は0 
   */
  private final function QuestionDeltaFlag($difficult, $ability_score ,$status) {
      $delta = 0;
      switch ($status["result"]) {
          case 'Accepted':
              if($ability_score < $difficult) $delta = 1;
              break;
          default:
              if($ability_score > $difficult) $delta = 1;
              break;
      }
      return $delta;
  }
  
  
  /**
   * 新しく計算された難易度をquetsionsテーブルに更新する
   * @param question_id 問題ID
   * @param new_difficult 再計算した難易度
   */
  private final function updateNewDifficult($question_id, $new_difficult) {
      $query = "update questions set difficult = '$new_difficult' where id = '$question_id';";
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