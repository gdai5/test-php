<?php
/**
 * 2013-10-03
 * シミュレーション用の実力評価
 */

require_once("./UserAssessment/SimulationOrignalUserAssessment.php");
 
class SimulationUserAssessment {
    
    private $orignal_ability_scores_transition = array(array());
    private $ishikawa_ability_scores_transition = array();
    //private $terada_ability_scores_transition = array();
    
    //今現在の実力を保管する
    private $orignal_ability_scores = array(array());
    private $ishikawa_ability_scores = array();
    //private $terada_ability_scores = array();
    
    //オリジナルの計算式を利用するために呼び出す
    private $orignal_user_assessment;
    
    //2013-10-17
    //正規化を用いる為にそのユーザが何回正解したかをカウントしておくもの
    private $accepted_counter = array();
    
    /**
     * 実力の推移と今現在の実力を保管する配列たちを初期化
     */
    function initialize() {
       for($i = 0; $i < DATA_NUM; $i++) {
           $ability_score = mt_rand(1, 10);
           $this->orignal_ability_scores_transition["$i"] = array($ability_score);
           //$this->ishikawa_ability_scores_transition["$i"] = array($ability_score);
           //$this->terada_ability_scores_transition["$i"] = array($ability_score);
           $this->orignal_ability_scores[$i] = $ability_score;
           //$this->ishikawa_ability_scores[$i] = $ability_score;
           //$this->terada_ability_scores[$i] = $ability_score;
       }
       $this->orignal_user_assessment = new SimulationOrignalUserAssessment();
       $this->simulation_question_assessment = new SimulationQuestionAssessment();
    }
  
    /**
     * そのユーザの履歴と真の実力および、挑戦した真の問題の難易度を受け取り
     * 計算後のユーザの実力返す関数である
     */
    public final function Assessment($user_history, $question_assessment) {
        $round = 1;
        //各計算でδが0以外になった回数をカウント
        $orignal_delta_count = 0;
        //$ishikawa_delta_count = 0;
        //$terada_delta_count = 0;
      
        //各計算式で求まった新しい実力
        $orignal_new_ability_score = 0;
        //$ishikawa_new_ability_score = 0;
        //$terada_new_ability_score = 0;
        
        //2013-10-10
        //評価が正しく行われているかをチャックするための出力
        $this->outputUserHistory($user_history, $question_assessment);
        
        //保存されている履歴の回数分計算を繰り返す
        for ($i = 0; $i < count($user_history); $i++) {
            list($orignal_delta_count, $orignal_new_ability_score) = 
                $this->orignalUserAssessment($user_history[$i], $orignal_delta_count, $orignal_new_ability_score, $question_assessment);
                
            //list($difficult, $ishikawa_delta) = ishikawaUserDeltaFlag($difficult, $ability_score ,$status);
            //$terada_delta = teradaUserDeltaFlag($difficult, $ability_score, $status);
        }
        //ここで最終的な実力を計算して出力する
        $this->getOrignalNewAbilityScore($user_history[0][USER_ID], $orignal_delta_count, $orignal_new_ability_score);
        
    }
    
    //オリジナルの計算を行う関数
    private function orignalUserAssessment($history_data, $orignal_delta_count, $orignal_new_ability_score, $question_assessment) {
        //計算を行う前に必要なものを変数に入れておく
        $user_id       = $history_data[USER_ID];
        $question_id   = $history_data[QUESTION_ID];
        $result        = $history_data[RESULT];
        $ability_score = $this->orignal_ability_scores[$user_id];
        $difficult     = $question_assessment->getOrignalQuestionDifficult($question_id);
        
        //ここからユーザの実力計算を行う
        $delta = $this->orignal_user_assessment->orignalUserDeltaFlag($difficult, $ability_score, $result);
        //出力用
        $this->outputHistorySum($ability_score, $difficult, $delta);
        if($delta > 0) {
            $orignal_new_ability_score += ($difficult - $ability_score) * $delta;
            $orignal_delta_count++;
        } 
        return array($orignal_delta_count, $orignal_new_ability_score);
    }
    
    /**
     * 2013-10-07
     * 履歴の計算が全て終わったら、最終的な実力を求める
     */
    private function getOrignalNewAbilityScore($user_id, $orignal_delta_count, $orignal_new_ability_score) {
        printf("　＝　$orignal_new_ability_score<br>");
        if($orignal_delta_count > 0) {
            printf("δが０より大きくなった回数が１回以上あったので<br>");
            printf("追加する実力　＝　" . $orignal_new_ability_score . " / " . $orignal_delta_count);
            $orignal_new_ability_score = $orignal_new_ability_score / $orignal_delta_count;
            //小数点第二位で四捨五入する
            $orignal_new_ability_score = $orignal_new_ability_score * 10;
            $orignal_new_ability_score = round($orignal_new_ability_score);
            $orignal_new_ability_score = $orignal_new_ability_score / 10;
            printf("　＝　$orignal_new_ability_score<br>");
        }
        printf("最後に最終的な実力　＝　" . $this->orignal_ability_scores[$user_id] . " + " . $orignal_new_ability_score);
        $orignal_new_ability_score += $this->orignal_ability_scores[$user_id];
        //小数点第二位で四捨五入する
        $orignal_new_ability_score = $orignal_new_ability_score * 10;
        $orignal_new_ability_score = round($orignal_new_ability_score);
        $orignal_new_ability_score = $orignal_new_ability_score / 10;
        printf("　＝　" . $orignal_new_ability_score . "<br><br>");
        //メモリを圧迫しているのは推移だった
        array_push($this->orignal_ability_scores_transition["$user_id"], $orignal_new_ability_score);
        $this->orignal_ability_scores[$user_id] = $orignal_new_ability_score;
    }
    
    /**
     * 2013-10-14
     * 石川
     * 全てのability_scores_transitionをファイルに記録する
     */
    public function writeAbilityScoreTransition() {
        $this->writeOrignalTransition();
    }
    
    private function writeOrignalTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./UserTransition/Orignal/User" . $i . ".txt";
            $fp = fopen($file_name, "w");
            for($j = 0; $j < count($this->orignal_ability_scores_transition[$i]); $j++) {
                fwrite($fp, $this->orignal_ability_scores_transition[$i][$j] . "\n");
            }
            fclose($fp);
            unset($this->orignal_ability_scores_transition[$i]);
            $this->orignal_ability_scores_transition[$i] = array();
        }
    }
    
    /**
     * 2013-10-26
     * 計算式が３つあるため、これを呼び出せば３つ全ての書き込みを行わせる事ができるため設置
     */
    public function overwriteAbilityScoreTransition() {
        $this->overwriteOrignalTransition();
        
    }
    
    /**
     * 2013-10-26
     * メモリーリーク対策用にある一定間隔でファイルに書き込みをする
     */
    private function overwriteOrignalTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./UserTransition/Orignal/User" . $i . ".txt";
            $fp = fopen($file_name, "a");
            for($j = 0; $j < count($this->orignal_ability_scores_transition[$i]); $j++) {
                fwrite($fp, $this->orignal_ability_scores_transition[$i][$j] . "\n");
            }
            fclose($fp);
            unset($this->orignal_ability_scores_transition[$i]);
            $this->orignal_ability_scores_transition[$i] = array();
        }
    }

    /**
     * 2013-10-17
     * 石川
     * 各ユーザの正解した回数をカウントするための関数
     */
    public function chkAnswerAccepted($user_id, $result) {
        if($result == ACCEPTED) {
            if (!array_key_exists("$user_id", $this->accepted_counter)) {
                $this->accepted_counter["$user_id"] = 0;
            } 
            $this->accepted_counter["$user_id"] += 1;
        }
    }
    
    /**
     * 2013-10-07
     * SimulationQuestionAssessmentでそのユーザの実力を参照する際に使う
     */
    public function getOrignalUserAbilityScore($user_id) {
        return $this->orignal_ability_scores[$user_id];
    }
    
    public function getIshikawaUserAbilityScore($user_id) {
        return $this->ishikawa_ability_scores[$user_id];
    }
    
    public function getTeradaUserAbilityScore($user_id) {
        return $this->terada_ability_scores[$user_id];
    }
    
    /**
     * 2013-10-17
     * 石川
     * ユーザが問題に正解した割合を基に正規化する関数
     * 
     */
    public function normalization() {
        //正規化するための実力
        $ability_score = 10;
        $previous_accepted_counter = -1;
        $equal_accepted_counter_num = 0;
        //keyと値の関係を維持していくれる
        arsort($this->accepted_counter);
        foreach($this->accepted_counter as $key => $value) {
            if($value == $previous_accepted_counter) {
                $this->orignal_ability_scores[$key] = $ability_score;
                $equal_accepted_counter_num += 1;
            }else{
                $previous_accepted_counter = $value;
                $ability_score -= 1 + $equal_accepted_counter_num;
                $this->orignal_ability_scores[$key] = $ability_score;
                $equal_accepted_counter_num = 0;
            }
        }
    }

    //これ以降は計算が正しく行われているかをチャックするための関数
    private function outputUserHistory($user_history, $question_assessment) {
        printf("ユーザ" . $user_history[0][USER_ID] . "の履歴<br>");
        printf("問題を解く前の実力　＝　" . $this->orignal_ability_scores[$user_history[0][USER_ID]] . "<br>");
        printf("<table>");
        printf("<tr>");
        printf("<td>挑戦した問題ID</td>");
        printf("<td>難易度</td>");
        printf("<td>結果</td>");
        printf("<td>テストデータ数</td>");
        printf("<td>正解したテストデータ数</td>");
        printf("</tr>");
        for($i = 0; $i < count($user_history); $i++) {
            printf("<tr>");
            printf("<td>" . $user_history[$i][QUESTION_ID] . "</td>");
            printf("<td>" . $question_assessment->getOrignalQuestionDifficult($user_history[$i][QUESTION_ID]) . "</td>");
            printf("<td>" . $user_history[$i][RESULT] . "</td>");
            printf("<td>" . $user_history[$i][TESTDATA_NUM] . "</td>");
            printf("<td>" . $user_history[$i][CORRECT_TESTDATA_NUM] . "</td>");
            printf("</tr>");
        }
        printf("</table>");
        printf("<br>");
        printf("履歴の総和　＝　");
    }

    private function outputHistorySum($ability_score, $difficult, $delta) {
        printf("(" . $difficult . " - " . $ability_score . ")*" . $delta . "  + ");
    }

}

?>