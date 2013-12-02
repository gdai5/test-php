<?php

/**
 * 2013-11-21
 * 今現状のモデルが、ユーザ４において一番良い結果を出している
 */
class SimulationIshikawaUserAssessment {
    /**
     * @param difficult     問題の難易度
     * @param ability_score ユーザの実力
     * @param $result       実行結果
     * @return difficult    ishikawaUserDeltaFlag関数に合わせて返しているだけ（変化は生じない）
     * @return delta        計算をする場合は1, そうでない場合は0 
     */
     public function ishikawaUserDeltaFlag($difficult, $ability_score ,$result, $correct_testdata_num, $testdata_num) {
         if($ability_score > $difficult) {
             return $this->selectEasyQuestion($difficult, $ability_score, $result, $correct_testdata_num, $testdata_num);
         }else{
             return $this->selectDifficultQuestion($difficult, $ability_score, $result, $correct_testdata_num, $testdata_num);
         }
     }
     
     /**
    * 簡単な問題の処理
    */
   private function selectEasyQuestion($difficult, $ability_score, $result, $correct_testdata_num, $testdata_num) {
       $delta = 0;
       switch($result) {
           case COMPILE_ERROR: //コンパイルエラー
               $difficult = $difficult * 0.7;
               $delta = 0.8;   
               break;
           case RUNTIME_ERROR: //実行時エラー
               $difficult = $difficult * 0.8;
               $delta = 0.9;
               break;
           case NOT_CORRECT: //テストデータと一つも合っていない
               $difficult = $difficult * 0.9;
               $delta = 1;
               break;
           case CLOSE_ANSWER: //テストデータと一つ以上合う
               //実力と難易度との差分を取り、テストデータの正解数に応じてdifficultに足し込む差分を求めることで
               //実力の下がる割合を調整している
               $diff = $ability_score - $difficult;
               $diff = $diff * ($correct_testdata_num / $testdata_num);
               //完全に正解しているわけではないため必ず実力は落とすために、少数第二位で切り捨て
               $diff = $diff * 10;
               $diff = floor($diff);
               $diff = $diff / 10;
               $difficult += $diff;
               //元のバージョン
               //$difficult = ($difficult * (3/5))
               //                 + (($difficult * (2/5)) * ($correct_testdata_num / $testdata_num));
               $delta = 1;
               break;
           case ACCEPTED: //全てのテストデータに正解
               $delta = 0;
               break;
       }
       $difficult = $difficult * 10;
       $difficult = round($difficult);
       $difficult = $difficult / 10;
       return array($delta, $difficult);
   }

   /**
    * 難しい問題だった場合の処理
    */
   private function selectDifficultQuestion($difficult, $ability_score, $result, $correct_testdata_num, $testdata_num) {
       $delta = 0;
       switch($result) {
           case COMPILE_ERROR: //コンパイルエラー
               $difficult = $difficult * 0.7;
               if($ability_score > $difficult){
                   $delta = 1;  
               } 
               break;
           case RUNTIME_ERROR: //実行時エラー
               $difficult = $difficult * 0.8;
               if($ability_score > $difficult) {
                   $delta = 1;
               }
               break;
           case NOT_CORRECT: //テストデータと一つも合っていない
               $difficult = $difficult * 0.9;
               if($ability_score > $difficult) {
                   $delta = 1;
               }
               break;
           case CLOSE_ANSWER: //テストデータと一つ以上合う
               //Acceptedでは、1.1倍にするため、こっちは切り捨てをしない
               $diff = $difficult - $ability_score;
               $diff = $diff * ($correct_testdata_num / $testdata_num);
               $diff = $diff * 10;
               $diff = floor($diff);
               $diff = $diff / 10;
               $difficult += $diff;
               //$difficult = ($difficult * (2/3))
               //                 + (($difficult * (1/3)) * ($correct_testdata_num / $testdata_num));
               if($ability_score < $difficult) {
                       $delta = 1;
               }
               break;
           case ACCEPTED: //全てのテストデータに正解
               $difficult = $difficult * 1.1;
               if($ability_score < $difficult) {
                   $delta = 1;
               }
               break;
       }
       $difficult = $difficult * 10;
       $difficult = round($difficult);
       $difficult = $difficult / 10;
       return array($delta, $difficult);
   }
     
}
 
?>