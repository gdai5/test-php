<?php

require_once("./QuestionAssessment/SimulationOrignalQuestionAssessment.php");

class SimulationQuestionAssessment {
    private $orignal_difficult_transition = array();
    
    //今現在の難易度を保管
    private $orignal_difficult = array();
    
    private $orignal_question_assessment;
    
    //2013-10-23
    //正規化を用いる為にそのユーザが何回正解したかをカウントしておくもの
    private $accepted_counter = array();
    
    /**
     * 難易度の推移と今現在の難易度を保管する配列たちを初期化
     */
    function initialize() {
       for($i = 0; $i < DATA_NUM; $i++) {
           $difficult = mt_rand(1, 10);
           $this->orignal_difficult_transition["$i"] = array($difficult);
           $this->orignal_difficult[$i] = $difficult;
       }
       $this->orignal_question_assessment = new SimulationOrignalQuestionAssessment();
    }
    
    public function Assessment($question_history, $user_assessment) {
         //各計算でξが0以外になった回数をカウント
        $orignal_xi_count = 0;
      
        //各計算式で求まった新しい難易度
        $orignal_new_difficult = 0;
        
        //2013-10-10
        //計算が正しく行われているかを確認するため
        //$this->outputQuestionHistory($question_history, $user_assessment);
        
        //保存されている履歴の回数分計算を繰り返す
        for ($i = 0; $i < count($question_history); $i++) {
            list($orignal_xi_count, $orignal_new_difficult) = 
                $this->orignalQuestionAssessment($question_history[$i], $orignal_xi_count, $orignal_new_difficult, $user_assessment);
        }
        $this->getOrignalNewDifficult($question_history[0][QUESTION_ID], $orignal_xi_count, $orignal_new_difficult);
    }
    
    /**
     * 2013-10-08
     * 問題の難易度を計算するプログラム
     */
    private function orignalQuestionAssessment($history_data, $orignal_xi_count, $orignal_new_difficult, $user_assessment) {
        //計算を行う前に必要なものを変数に入れておく
        $user_id       = $history_data[USER_ID];
        $question_id   = $history_data[QUESTION_ID];
        $result        = $history_data[RESULT];
        $difficult     = $this->orignal_difficult[$question_id];
        //現状の実力を持ってくる場合
        //$ability_score = $user_assessment->getOrignalUserAbilityScore($user_id);
        //当時の実力を持ってくる場合
        $ability_score = $history_data[ABILITY_SCORE];
        
        //ここから問題の難易度計算を行う
        $xi = $this->orignal_question_assessment->orignalQuestionXiFlag($difficult, $ability_score ,$result);
        //出力用
        //$this->outputHistorySum($ability_score, $difficult, $xi);
        if($xi > 0) {
                $orignal_new_difficult += ($ability_score - $difficult) * $xi;
                $orignal_xi_count++;
        } 
        return array($orignal_xi_count, $orignal_new_difficult);
    }
    
    /**
     * 2013-10-08
     * 履歴の計算が全て終わったら、最終的な難易度を計算する
     */
    private function getOrignalNewDifficult($question_id, $orignal_xi_count, $orignal_new_difficult) {
        //printf("　＝　$orignal_new_difficult<br>");
        if($orignal_xi_count > 0) {
            //printf("ξが０より大きくなった回数が１回以上あったので<br>");
            //printf("追加する難易度　＝　" . $orignal_new_difficult . " / " . $orignal_xi_count);
            $orignal_new_difficult = $orignal_new_difficult / $orignal_xi_count;
            //小数点第二位で四捨五入する
            $orignal_new_difficult = $orignal_new_difficult * 10;
            $orignal_new_difficult = round($orignal_new_difficult);
            $orignal_new_difficult = $orignal_new_difficult / 10;
            //printf("　＝　$orignal_new_difficult<br>");
        }
        //printf("計算結果の難易度　＝　" . $this->orignal_difficult[$question_id] . " + " . $orignal_new_difficult);
        $orignal_new_difficult += $this->orignal_difficult[$question_id];
        //小数点第二位で四捨五入する
        $orignal_new_difficult = $orignal_new_difficult * 10;
        $orignal_new_difficult = round($orignal_new_difficult);
        $orignal_new_difficult = $orignal_new_difficult / 10;
        //printf("　＝　" . $orignal_new_difficult . "<br><br>");
        //array_push($this->orignal_difficult_transition["$question_id"], $orignal_new_difficult);
        $this->orignal_difficult[$question_id] = $orignal_new_difficult;
    }

    /**
     * 2013-10-14
     * 石川
     * 全てのability_scores_transitionをファイルに記録する
     */
    public function writeDifficultTransition() {
        $this->writeOrignalTransition();
    }
    
    private function writeOrignalTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./QuestionTransition/Orignal/Question" . $i . ".txt";
            $fp = fopen($file_name, "w");
            for($j = 0; $j < count($this->orignal_difficult_transition[$i]); $j++) {
                fwrite($fp, $this->orignal_difficult_transition[$i][$j] . "\n");
            }
            fclose($fp);
        }
    }

    //最後に出力結果の確認
    public function outputTransition() {
        print_r($this->orignal_difficult_transition);
    }
    
    //SimlationUserAssessment.phpで使う難易度を取得するための関数
    public function getOrignalQuestionDifficult($question_id) {
        return $this->orignal_difficult[$question_id];
    }
    
    /**
     * 2013-10-23
     * 問題の難易度を計算する前に正規化
     * 正解数/挑戦者数の値が低い人から
     * 
     * 今のままだとあまり効果がない。
     */
    public function normalization_old() {
        $max_correct_answer_ratio = 0;
        $min_correct_answer_ratio = 100;
        //まず正解率の最大と最小を求める
        foreach ($this->accepted_counter as $key => $value) {
            if($value[CORRECT_ANSWER_RATIO] > $max_correct_answer_ratio) {
                $max_correct_answer_ratio = $value[CORRECT_ANSWER_RATIO];
            }
            if($value[CORRECT_ANSWER_RATIO] < $min_correct_answer_ratio) {
                $min_correct_answer_ratio = $value[CORRECT_ANSWER_RATIO];
            }
        }
        foreach ($this->accepted_counter as $key => $value) {
            //printf("$key" . "<br>");
            //printf("難易度の正規化をします<br>");
            //printf("問題" . $key . "の正規化<br>");
            $difficult = 10 - (10 / ($max_correct_answer_ratio - $min_correct_answer_ratio)) * ($value[CORRECT_ANSWER_RATIO] - $min_correct_answer_ratio);
            $difficult = $difficult * 10;
            $difficult = round($difficult);
            $difficult = $difficult / 10;
            //printf("正解率から導いた難易度＝" . $difficult . "<br>");
            //printf("正規化した後の難易度＝(" . $difficult . " + " . $this->orignal_difficult[$key] . ") / 2 = ");
            $difficult = ($difficult + $this->orignal_difficult[$key]) / 2;
            $difficult = $difficult * 10;
            $difficult = round($difficult);
            $difficult = $difficult / 10;
            //printf($difficult . "<br>");
            //printf("<br>");
            $this->orignal_difficult[$key] = $difficult;
            array_push($this->orignal_difficult_transition["$key"], $difficult);
        }
    }

    /**
     * 2013-10-23
     * 計算されて求まった難易度から正規化を行う
     */
    public function normalization() {
        $max_difficult = 0;
        $min_difficult = 10;
        for($i = 0; $i < count($this->orignal_difficult); $i++) {
            //最大値の更新
            if($this->orignal_difficult[$i] > $max_difficult) {
                $max_difficult = $this->orignal_difficult[$i];
            }
            //最小値の更新
            if($this->orignal_difficult[$i] < $min_difficult) {
                $min_difficult = $this->orignal_difficult[$i];
            }
        }
        ////printf("正規化の範囲" . $min_difficult . "~" . $max_difficult . "<br>");
        for($i = 0; $i < count($this->orignal_difficult); $i++) {
            //printf("難易度の正規化をします<br>");
            //printf("問題" . $i . "の正規前　＝　" . $this->orignal_difficult[$i] . "<br>");
            //printf("問題" . $i . "の正規化<br>");
            $difficult = (10 / ($max_difficult - $min_difficult)) * ($this->orignal_difficult[$i] - $min_difficult);
            $difficult = $difficult * 10;
            $difficult = round($difficult);
            $difficult = $difficult / 10;
            //printf("正規化後の難易度＝" . $difficult . "<br>");
            //printf("<br>");
            $this->orignal_difficult[$i] = $difficult;
            array_push($this->orignal_difficult_transition["$i"], $difficult);
        }
    }
    
    /**
     * 2013-10-23
     * 各問題の正解した回数と挑戦者数をカウントするための関数
     */
    public function chkAnswerAccepted($question_id, $result) {
        //まだ登録されている人ではなかったら、新規に登録
        if (!array_key_exists("$question_id", $this->accepted_counter)) {
            $this->accepted_counter["$question_id"] = array(0, 0, 0);
        } 
        if($result == ACCEPTED) {
            $this->accepted_counter["$question_id"][ACCEPTED_COUNT] += 1;
        }
        $this->accepted_counter["$question_id"][CHALLENGER_COUNT] += 1;
        $correct_answer_ratio = round($this->accepted_counter["$question_id"][ACCEPTED_COUNT] / $this->accepted_counter["$question_id"][CHALLENGER_COUNT] * 100);
        $this->accepted_counter["$question_id"][CORRECT_ANSWER_RATIO] = $correct_answer_ratio;
    }
    
    public function outputAcceptedCounter() {
        print_r($this->accepted_counter);
    }
    
    //これ以降は計算が正しく行われているかをチャックするための関数
    private function outputQuestionHistory($question_history, $user_assessment) {
        printf("問題" . $question_history[0][QUESTION_ID] . "の履歴<br>");
        printf("計算前の問題の難易度　＝　" . $this->orignal_difficult[$question_history[0][QUESTION_ID]] . "<br>");
        printf("<table>");
        printf("<tr>");
        printf("<td>挑戦したユーザID</td>");
        //printf("<td>実力</td>");
        printf("<td>挑戦した当時の実力</td>");
        printf("<td>結果</td>");
        printf("<td>テストデータ数</td>");
        printf("<td>正解したテストデータ数</td>");
        printf("</tr>");
        for($i = 0; $i < count($question_history); $i++) {
            printf("<tr>");
            printf("<td>" . $question_history[$i][USER_ID] . "</td>");
            //計算を始めるときの実力
            //printf("<td>" . $user_assessment->getOrignalUserAbilityScore($question_history[$i][USER_ID]) . "</td>");
            //挑戦した当時の実力
            printf("<td>" . $question_history[$i][ABILITY_SCORE] . "</td>");
            printf("<td>" . $question_history[$i][RESULT] . "</td>");
            printf("<td>" . $question_history[$i][TESTDATA_NUM] . "</td>");
            printf("<td>" . $question_history[$i][CORRECT_TESTDATA_NUM] . "</td>");
            printf("</tr>");
        }
        printf("</table>");
        printf("<br>");
        printf("履歴の総和　＝　");
    }

    private function outputHistorySum($ability_score, $difficult, $xi) {
        printf("(" . $ability_score . " - " . $difficult . ")*" . $xi . "  + ");
    }
    
}

?>