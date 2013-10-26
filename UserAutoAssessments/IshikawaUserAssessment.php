<?php
//この計算式は、実力と難易度が全く同じ場合でもその実行結果に応じて実力を下げる
//他の計算式では(難易度 - 実力)*δの()内が０になるためである。

   /**
   * 実力の計算式においてδが1 or 0かをチェックする
   * 失敗として認識するもの：Compile Error, Runtime Error, Time Out, Output Limit Exceeded, Wrong Answer
   * 成功として認識するもの：Accepted Wrong Answer 
   * @param difficult 問題の難易度
   * @param ability_score ユーザの実力
   * @param $status       statusテーブル１行分の情報が入っている連想配列
   * @return difficult 実行結果から計算し直した値
   * @return delta 計算をする場合は1, そうでない場合は0 
   */
   function ishikawaUserDeltaFlag($difficult, $ability_score ,$status) {
       printf("問題番号" . $status["question_id"] . "　:　難易度（修正前）＝" . $difficult);
       //簡単な問題なのか、難しい問題なのかで判定を分ける
       if($ability_score >= $difficult) {
           return selectEasyQuestion($difficult, $ability_score, $status);
       }else{
           return selectDifficultQuestion($difficult, $ability_score, $status);
       }
   }

   /**
    * 簡単な問題の処理
    */
   function selectEasyQuestion($difficult, $ability_score, $status) {
       $delta = 0;
       switch($status["result"]) {
           case COMPILE_ERROR: //コンパイルエラー
               $difficult = $difficult * (1 / 3);
               $delta = 1;   
               break;
           case RUNTIME_ERROR: //実行時エラー
               $difficult = $difficult * (1 / 2);
               $delta = 1;
               break;
           case NOT_CORRECT: //テストデータと一つも合っていない
               $difficult = $difficult * (2 / 3);
               $delta = 1;
               break;
           case CLOSE_ANSWER: //テストデータと一つ以上合う
               $difficult = ($difficult * (2 / 3))
                                + (($difficult * (1 / 3)) * ($status["correct_testdata_num"] / $status["testdata_num"]));
               $delta = 1;
               break;
           case ACCEPTED: //全てのテストデータに正解
               $delta = 0;
               break;
       }
       printf("　:　難易度（修正後）＝" . $difficult . "<br>");
       printf("結果＝" . $status["result"] . "  δ＝$delta <br>");
       return array($difficult, $delta);
   }

   /**
    * 難しい問題だった場合の処理
    */
   function selectDifficultQuestion($difficult, $ability_score, $status) {
       $delta = 0;
       switch($status["result"]) {
           case COMPILE_ERROR: //コンパイルエラー
               $difficult = $difficult * (1/3);
               if($ability_score > $difficult){
                   $delta = 1;  
               } 
               break;
           case RUNTIME_ERROR: //実行時エラー
               $difficult = $difficult * (1 / 2);
               if($ability_score > $difficult) {
                   $delta = 1;
               }
               break;
           case NOT_CORRECT: //テストデータと一つも合っていない
               $difficult = $difficult * (2 / 3);
               if($ability_score > $difficult) {
                   $delta = 1;
               }
               break;
           case CLOSE_ANSWER: //テストデータと一つ以上合う
               $difficult = ($difficult * (2 / 3))
                                + (($difficult * (1 / 3)) * ($status["correct_testdata_num"] / $status["testdata_num"]));
               if($ability_score < $difficult) {
                       $delta = 1;
               }
               break;
           case ACCEPTED: //全てのテストデータに正解
               $delta = 1;
               break;
       }
       printf("　:　難易度（修正後）＝" . $difficult . "<br>");
       printf("結果＝" . $status["result"] . "  δ＝$delta <br>");
       return array($difficult, $delta);
   }

?>