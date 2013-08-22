<?php
   /**
   * 実力の計算式においてδが1 or 0かをチェックする
   * 失敗として認識するもの：Compile Error, Runtime Error, Time Out, Output Limit Exceeded, Wrong Answer
   * 成功として認識するもの：Accepted Wrong Answer 
   * ただしWrong Answerについてはテストデータが一つでもクリアしたら成功として認識
   * @param difficult 問題の難易度
   * @param ability_score ユーザの実力
   * @param $status       statusテーブル１行分の情報が入っている連想配列
   * @return difficult 実行結果から計算し直した値
   * @return delta 計算をする場合は1, そうでない場合は0 
   */
   function UserDeltaFlag($difficult, $ability_score ,$status) {
       printf("問題番号" . $status["question_id"] . "　:　難易度（修正前）＝" . $difficult);
       $delta = 0;
       switch($status["result"]) {
           case 'Compile Error': 
               $difficult = 0;
               if($ability_score > $difficult){
                   $delta = 1;  
               } 
               break;
           case 'Runtime Error':
               $difficult = $difficult * (1 / 3);
               if($ability_score > $difficult) {
                   $delta = 1;
               }
               break;
           case 'Time Out':
               $difficult = $difficult * (2 / 3);
               if($ability_score > $difficult) {
                   $delta = 1;
               }
               break;
           case 'Output Limit Exceeded':
               $difficult = $difficult * (2 / 3);
               if($ability_score > $difficult) {
                   $delta = 1;
               }
               break;
           case 'Wrong Answer': //失敗と成功の二つの認識パターンを作成
               if($status["correct_answers"] == 0) { //テストデータを一つもクリアできていない場合
                   $difficult = $difficult * (2 / 3);
                   if($ability_score > $difficult) {
                       $delta = 1;
                   }
                   break;
               }else{ //$difficult = 難易度の2/3 + 難易度の1/3 * クリアした個数/テストデータの数
                   $difficult = ($difficult * (2 / 3))
                                + (($difficult * (1 / 3)) * ($status["correct_answers"] / $status["testdatas_num"]));
                   if($ability_score < $difficult) {
                       $delta = 1;
                   }
                   break;
               }
           case 'Accepted':
               if($ability_score < $difficult) {
                   $delta = 1;
               }
               break;
       }
       printf("　:　難易度（修正後）＝" . $difficult . "<br>");
       printf("結果＝" . $status["result"] . "  δ＝$delta <br>");
       return array($difficult, $delta);
   }

?>