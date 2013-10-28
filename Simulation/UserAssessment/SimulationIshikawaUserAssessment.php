<?php

/**
 * 2013-10-26
 * 石川の計算式を実装
 * 上がる量自体はそんなに大きく無いが、下がる量が結構大きい気がする
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
         if($ability_score >= $difficult) {
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
                                + (($difficult * (1 / 3)) * ($correct_testdata_num / $testdata_num));
               $delta = 1;
               break;
           case ACCEPTED: //全てのテストデータに正解
               $delta = 0;
               break;
       }
       $difficlut = $difficult * 10;
       $difficlut = round($difficlut);
       $difficlut = $difficlut / 10;
       return array($delta, $difficult);
   }

   /**
    * 難しい問題だった場合の処理
    */
   private function selectDifficultQuestion($difficult, $ability_score, $result, $correct_testdata_num, $testdata_num) {
       $delta = 0;
       switch($result) {
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
                                + (($difficult * (1 / 3)) * ($correct_testdata_num / $testdata_num));
               if($ability_score < $difficult) {
                       $delta = 1;
               }
               break;
           case ACCEPTED: //全てのテストデータに正解
               $delta = 1;
               break;
       }
       $difficlut = $difficult * 10;
       $difficlut = round($difficlut);
       $difficlut = $difficlut / 10;
       return array($delta, $difficult);
   }
     
}
 
?>