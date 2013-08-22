<?php


  function TeradaUserDeltaFlag($difficult, $ability_score ,$status) {
      printf("問題番号" . $status["question_id"] . "　:　難易度＝" . $difficult . "<br>");
      if ($ability_score > $difficult) {
          //簡単な問題にチャレンジした時の処理
          $delta = SelectEasyQuestion($status);
      } else {
          //難し問題にチャレンジした時の処理
          $delta = SelectDifficultQuestion($status);
      }
      printf("結果＝" . $status["result"] . "  δ＝$delta <br>");
      return array($difficult, $delta);
  }
  
  /**
   * 簡単な問題に挑戦した場合に、どの部分まで進んだかによってδの値を決めている
   * @param status statusテーブルの全てのカラムが入っている連想配列
   * @return delta　０〜１までの範囲 
   */
  function SelectEasyQuestion($status) {
      switch ($status["result"]) {
          case 'Compile Error':
               $delta = 1;
               break;
           case 'Runtime Error':
               $delta = 0.7;
               break;
           case 'Time Out':
               $delta = 0.5;
               break;
           case 'Output Limit Exceeded':
               $delta = 0.5;
               break;
           case 'Wrong Answer':
               $delta = 0.5 - 0.5 * ($status["correct_answers"] / $status["testdatas_num"]);
               break;
           case 'Accepted':
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
  function SelectDifficultQuestion($status) {
      switch ($status["result"]) {
          case 'Compile Error':
               $delta = 0;
               break;
           case 'Runtime Error':
               $delta = 0.3;
               break;
           case 'Time Out':
               $delta = 0.5;
               break;
           case 'Output Limit Exceeded':
               $delta = 0.5;
               break;
           case 'Wrong Answer':
               $delta = 0.5 + 0.5 * ($status["correct_answers"] / $status["testdatas_num"]);
               break;
           case 'Accepted':
               $delta = 1;
               break;
      }
      return $delta;
  }
  
  
  

?>