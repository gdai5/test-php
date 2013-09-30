<?php

  /**
   * シュミレーションの比較用の計算式
   * 菅沼らの研究に使われている計算式をそのまま表現したもの
   * @param difficult 問題の難易度
   * @param ability_score ユーザの実力
   * @param $status       statusテーブル１行分の情報が入っている連想配列
   * @return difficult UserDeltaFlag関数に合わせて返しているだけ（変化は生じない）
   * @return delta 計算をする場合は1, そうでない場合は0 
   */
  function orignalUserDeltaFlag($difficult, $ability_score ,$status) {
      printf("問題番号" . $status["question_id"] . "　:　難易度＝" . $difficult . "<br>");
      $delta = 0;
      switch ($status["result"]) {
          case ACCEPTED:
              //難しい問題だったかどうか
              if($ability_score < $difficult) $delta = 1;
              break;
          default:
              //簡単な問題だったかどうか
              if($ability_score >= $difficult) $delta = 1;
              break;
      }
      printf("結果＝" . $status["result"] . "  δ＝$delta <br>");
      return $delta;
  }
  
  

?>