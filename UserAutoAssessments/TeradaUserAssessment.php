<?php

  /**
   * 簡単な問題か難しい問題のどちらに挑戦したのかで分岐させて、その時のδの値を得る
   * @param $difficult     問題の難易度
   * @param $ability_score ユーザの実力
   * @param $status        このなかにstatusテーブルの１レコード分が入っている
   */
  function teradaUserDeltaFlag($difficult, $ability_score ,$status) {
      printf("問題番号" . $status["question_id"] . "　:　難易度＝" . $difficult . "<br>");
      if ($ability_score >= $difficult) {
          //簡単な問題にチャレンジした時の処理
          $delta = selectEasyQuestion($status);
      } else {
          //難し問題にチャレンジした時の処理
          $delta = selectDifficultQuestion($status);
      }
      printf("結果＝" . $status["result"] . "  δ＝$delta <br>");
      return $delta;
  }
  
  /**
   * 簡単な問題に挑戦した場合に、どの部分まで進んだかによってδの値を決めている
   * @param status statusテーブルの全てのカラムが入っている連想配列
   * @return delta　０〜１までの範囲 
   */
  function selectEasyQuestion($status) {
      switch ($status["result"]) {
          case COMPILE_ERROR:
               $delta = 1;
               break;
           case RUNTIME_ERROR:
               $delta = 0.7;
               break;
           case NOT_CORRECT:
               $delta = 0.5;
               break;
           case CLOSE_ANSWER:
               $delta = 0.5 - 0.5 * ($status["correct_answers"] / $status["testdatas_num"]);
               break;
           case ACCEPTED:
               $delta = 0;
               break;
      }
      return $delta;
  }
  
  /**
   * 難しい問題に挑戦した場合に、どの部分まで進んだかによってδの値を決めている
   * @param status statusテーブルの全てのカラムが入っている連想配列
   * @return delta　０〜１までの範囲 
   */
  function selectDifficultQuestion($status) {
      switch ($status["result"]) {
          case COMPILE_ERROR:
               $delta = 0;
               break;
           case RUNTIME_ERROR:
               $delta = 0;
               break;
           case NOT_CORRECT:
               $delta = 0;
               break;
           case CLOSE_ANSWER:
               $delta = 0.5 + 0.5 * ($status["correct_answers"] / $status["testdatas_num"]);
               break;
           case ACCEPTED:
               $delta = 1;
               break;
      }
      return $delta;
  }
  
  
  

?>