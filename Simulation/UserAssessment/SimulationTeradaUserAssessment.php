<?php

/**
 * 2013-11-04
 * teradaの計算式
 * 実行結果に応じて、δの値を0~1の間で変化させる
 */
class SimulationTeradaUserAssessment {
    /**
     * @param difficult     問題の難易度
     * @param ability_score ユーザの実力
     * @param $result       実行結果
     * @return difficult    ishikawaUserDeltaFlag関数に合わせて返しているだけ（変化は生じない）
     * @return delta        計算をする場合は1, そうでない場合は0 
     */
     public function teradaUserDeltaFlag($difficult, $ability_score ,$result, $correct_testdata_num, $testdata_num) {
         //実力と難易度が同じ場合は、δを必ず０に設定する為に設置    
         if($ability_score == $difficult) {
             return 0;
         }
         if($ability_score > $difficult) {
             return $this->selectEasyQuestion($difficult, $result, $correct_testdata_num, $testdata_num);
         }else {
             return $this->selectDifficultQuestion($difficult, $result, $correct_testdata_num, $testdata_num);
         }
     }
     
     /**
    * 簡単な問題の処理
    */
   private function selectEasyQuestion($difficult, $result, $correct_testdata_num, $testdata_num) {
       $delta = 0;
       switch($result) {
           case COMPILE_ERROR: //コンパイルエラー               
               $delta = 1;   
               break;
           case RUNTIME_ERROR: //実行時エラー
               $delta = 0.7;
               break;
           case NOT_CORRECT: //テストデータと一つも合っていない
               $delta = 0.5;
               break;
           case CLOSE_ANSWER: //テストデータと一つ以上合う
               $delta = 0.5 - 0.5 * ($correct_testdata_num / $testdata_num);
               break;
           case ACCEPTED: //全てのテストデータに正解
               $delta = 0;
               break;
       }
       $delta = $delta * 10;
       $delta = round($delta);
       $delta = $delta / 10;
       return $delta;
   }

   /**
    * 難しい問題だった場合の処理
    */
   private function selectDifficultQuestion($difficult, $result, $correct_testdata_num, $testdata_num) {
       $delta = 0;
       switch($result) {
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
               $delta = 0.5 + 0.5 * ($correct_testdata_num / $testdata_num);
               break;
           case ACCEPTED:
               $delta = 1;
               break;
       }
       $delta = $delta * 10;
       $delta = round($delta);
       $delta = $delta / 10;
       return $delta;
   }
     
}
 
?>