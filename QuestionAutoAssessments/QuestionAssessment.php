<?php

class QuestionAssessment {
  private $mysqli;
   
  /**
   * まだデバックしていないので、要動作確認（7/23）
   * 問題の難易度を計算する
   */
  public final function Assessment($question_id, $questions_difficult_transition) {
      $this->mysqli = DatabaseConnection();
      $difficult = $this->getDifficult($question_id);
      //新規追加する場合だけ
      if (!array_key_exists($question_id, $questions_difficult_transition)) {
          $questions_difficult_transition["$question_id"] = array($difficult);
      } 
      $new_difficult = $this->DifficultCalculation($question_id, $difficult);
      //難易度の推移を記録
      array_push($questions_difficult_transition["$question_id"], $new_difficult);
      $this->updateNewDifficult($question_id, $new_difficult);
      $this->mysqli->close(); 
      return $questions_difficult_transition;
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
       $xi_count = 0;
       //降順でユーザの履歴を取得する
       $query = "SELECT * FROM status WHERE question_id = '$question_id' ORDER BY created_at DESC";
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
           $xi = $this->QuestionDeltaFlag($difficult, $ability_score, $status);
           printf("結果＝" . $status["result"] . "  ξ＝$xi <br>");
           if($xi != 0) {
               $new_difficult += $ability_score - $difficult;
               $xi_count++;
           }
       }
       if($xi_count != 0) {
           $new_difficult = $new_difficult / $xi_count;
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
         printf("QuestionsAssessment");
         exit('問題の履歴取得に失敗しました'. $this->mysqli->error);
      }
      return $status;
  }
  
  /**
   * 難易度の計算式においてξが1 or 0かをチェックする
   * 失敗として認識するもの：CompileError, RuntimeError, NotCorrect, CloseAnswer
   * 成功として認識するもの：Accepted
   * @param difficult 問題の難易度
   * @param ability_score ユーザの実力
   * @param $status       statusテーブル１行分の情報が入っている連想配列
   * @return delta 計算をする場合は1, そうでない場合は0 
   */
  private final function QuestionDeltaFlag($difficult, $ability_score ,$status) {
      $xi = 0;
      switch ($status["result"]) {
          case ACCEPTED:
              if($ability_score < $difficult) $xi = 1;
              break;
          default:
              if($ability_score >= $difficult) $xi = 1;
              break;
      }
      return $xi;
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