<?php
/**
 * 最終更新日
 * 2013-11-04
 * Teradaの計算式の実装を始める
 */

require_once("./UserAssessment/SimulationOrignalUserAssessment.php");
require_once("./UserAssessment/SimulationIshikawaUserAssessment.php");
require_once("./UserAssessment/SimulationTeradaUserAssessment.php");
 
class SimulationUserAssessment {
    
    private $orignal_ability_scores_transition = array(array());
    private $ishikawa_ability_scores_transition = array(array());
    private $terada_ability_scores_transition = array();
    
    //今現在の実力を保管する
    private $orignal_ability_scores = array(array());
    private $ishikawa_ability_scores = array(array());
    private $terada_ability_scores = array();
    
    //オリジナルの計算式を利用するために呼び出す
    private $orignal_user_assessment;
    //Ishikawaの計算式関数を呼び出すだけのもの
    private $ishikawa_user_assessment;
	//Teradaの計算式関数を呼び出すだけのもの
	private $terada_user_assessment;
    
    /**
     * 実力の推移と今現在の実力を保管する配列たちを初期化
     */
    function initialize() {
       for($i = 0; $i < DATA_NUM; $i++) {
           $ability_score = mt_rand(1, 10);
           $this->orignal_ability_scores_transition["$i"]  = array($ability_score);
           $this->ishikawa_ability_scores_transition["$i"] = array($ability_score);
           $this->terada_ability_scores_transition["$i"]   = array($ability_score);
           $this->orignal_ability_scores[$i]  = $ability_score;
           $this->ishikawa_ability_scores[$i] = $ability_score;
           $this->terada_ability_scores[$i]   = $ability_score;
       }
       
       //各種の計算式のクラスインスタンスを生成している
       $this->orignal_user_assessment  = new SimulationOrignalUserAssessment();
       $this->ishikawa_user_assessment = new SimulationIshikawaUserAssessment();
	   $this->terada_user_assessment   = new SimulationTeradaUserAssessment();
       //問題の難易度を引っ張ってくるだけのもの
       //$this->simulation_question_assessment = new SimulationQuestionAssessment();
       
    }
  
    /**
     * そのユーザの履歴と真の実力および、挑戦した真の問題の難易度を受け取り
     * 計算後のユーザの実力返す関数である
     */
    public final function Assessment($user_history, $question_assessment) {
        $round = 1;
        //各計算でδが0以外になった回数をカウント
        $orignal_delta_count  = 0;
        $ishikawa_delta_count = 0;
        $terada_delta_count   = 0;
      
        //各計算式で求まった新しい実力
        $orignal_new_ability_score  = 0;
        $ishikawa_new_ability_score = 0;
        $terada_new_ability_score   = 0;
        
        $user_id = $user_history[0][USER_ID];
        
		 
        /**
         * orignalでユーザの実力を計算する
         */
        //printf("-------------Orignal User Ability-------------<br>");
        //$this->outputOrignalUserHistory($user_history, $question_assessment);
        for ($i = 0; $i < count($user_history); $i++) {
            //Orignalでの履歴計算
            list($orignal_delta_count, $orignal_new_ability_score) = 
                $this->orignalUserAssessment($user_history[$i], $orignal_delta_count, $orignal_new_ability_score, $question_assessment);
        }
        $this->getOrignalNewAbilityScore($user_id, $orignal_delta_count, $orignal_new_ability_score);
        //printf("-------------Orignal User Ability END-------------<br>");
        
        
        /**
         * ishikawaでの実力を計算する
         */
        //printf("-------------Ishikawa User Ability-------------<br>");
        //$this->outputIshikawaUserHistory($user_history, $question_assessment);
        for ($i = 0; $i < count($user_history); $i++) {
            list($ishikawa_delta_count, $ishikawa_new_ability_score) = 
                $this->ishikawaUserAssessment($user_history[$i], $ishikawa_delta_count, $ishikawa_new_ability_score, $question_assessment);
        }
        $this->getIshikawaNewAbilityScore($user_id, $ishikawa_delta_count, $ishikawa_new_ability_score);
        //printf("-------------Ishikawa User Ability END-------------<br>");
        
        /**
         * teradaでの実力を計算する
         */
        //printf("-------------Terada User Ability-------------<br>");
        //$this->outputTeradaUserHistory($user_history, $question_assessment);
        for ($i = 0; $i < count($user_history); $i++) {
            list($terada_delta_count, $terada_new_ability_score) =
                $this->teradaUserAssessment($user_history[$i], $terada_delta_count, $terada_new_ability_score, $question_assessment);
        }
        $this->getTeradaNewAbilityScore($user_id, $terada_delta_count, $terada_new_ability_score);
        //printf("-------------Terada User Ability END-------------<br>");
    }

//--------------------------------Orignal----------------------------------------------------------------  
    //オリジナルの計算を行う関数
    private function orignalUserAssessment($history_data, $orignal_delta_count, $orignal_new_ability_score, $question_assessment) {
        //計算を行う前に必要なものを変数に入れておく
        $user_id       = $history_data[USER_ID];
        $question_id   = $history_data[QUESTION_ID];
        $result        = $history_data[RESULT];
        $ability_score = $this->orignal_ability_scores[$user_id];
        $difficult     = $question_assessment->getOrignalQuestionDifficult($question_id);
        $delta         = 0;
        
        //ここからユーザの実力計算を行う
        $delta = $this->orignal_user_assessment->orignalUserDeltaFlag($difficult, $ability_score, $result);
        //出力用
        //$this->outputHistorySum($ability_score, $difficult, $delta);
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
        //printf("　＝　$orignal_new_ability_score<br>");
        if($orignal_delta_count > 0) {
            //printf("δが０より大きくなった回数が１回以上あったので<br>");
            //printf("追加する実力　＝　" . $orignal_new_ability_score . " / " . $orignal_delta_count);
            $orignal_new_ability_score = $orignal_new_ability_score / $orignal_delta_count;
            //小数点第二位で四捨五入する
            $orignal_new_ability_score = $this->Rounding($orignal_new_ability_score);
            //printf("　＝　$orignal_new_ability_score<br>");
        }
        //printf("最後に最終的な実力　＝　" . $this->orignal_ability_scores[$user_id] . " + " . $orignal_new_ability_score);
        $orignal_new_ability_score += $this->orignal_ability_scores[$user_id];
        //小数点第二位で四捨五入する
        $orignal_new_ability_score = $this->Rounding($orignal_new_ability_score);
        //printf("　＝　" . $orignal_new_ability_score . "<br><br>");
        //メモリを圧迫しているのは推移だった
        array_push($this->orignal_ability_scores_transition["$user_id"], $orignal_new_ability_score);
        $this->orignal_ability_scores[$user_id] = $orignal_new_ability_score;
    }

//--------------------------------Ishikawa--------------------------------------------------------------------------------
    /**
     * 2013-10-26
     * ishikawaの計算式メインの部分
     * δと変化した難易度を取得して、新しい実力を計算する
     */
    private function ishikawaUserAssessment($history_data, $ishikawa_delta_count, $ishikawa_new_ability_score, $question_assessment) {
        //計算を行う前に必要なものを変数に入れておく
        $user_id              = $history_data[USER_ID];
        $question_id          = $history_data[QUESTION_ID];
        $result               = $history_data[RESULT];
        $correct_testdata_num = $history_data[CORRECT_TESTDATA_NUM];
        $testdata_num         = $history_data[TESTDATA_NUM];
        $ability_score        = $this->ishikawa_ability_scores[$user_id];
        $difficult            = $question_assessment->getIshikawaQuestionDifficult($question_id);
        $delta = 0;
        
        //ここからユーザの実力計算を行う
        list($delta, $difficult) = $this->ishikawa_user_assessment->ishikawaUserDeltaFlag(
                $difficult, $ability_score, $result, $correct_testdata_num, $testdata_num);
        //出力用
        //$this->outputHistorySum($ability_score, $difficult, $delta);
        if($delta > 0) {
            $ishikawa_new_ability_score += ($difficult - $ability_score) * $delta;
            $ishikawa_delta_count++;
        } 
        return array($ishikawa_delta_count, $ishikawa_new_ability_score);
    }

    /**
     * 2013-10-28
     * Ishikawaにおける新しい実力を求める行程
     */
    private function getIshikawaNewAbilityScore($user_id, $ishikawa_delta_count, $ishikawa_new_ability_score) {
        //printf("　＝　$ishikawa_new_ability_score<br>");
        if($ishikawa_delta_count > 0) {
            //printf("δが０より大きくなった回数が１回以上あったので<br>");
            //printf("追加する実力　＝　" . $ishikawa_new_ability_score . " / " . $ishikawa_delta_count);
            $ishikawa_new_ability_score = $ishikawa_new_ability_score / $ishikawa_delta_count;
            //小数点第二位で四捨五入する
            $ishikawa_new_ability_score = $this->Rounding($ishikawa_new_ability_score);
            //printf("　＝　$ishikawa_new_ability_score<br>");
        }
        //printf("最後に最終的な実力　＝　" . $this->ishikawa_ability_scores[$user_id] . " + " . $ishikawa_new_ability_score);
        $ishikawa_new_ability_score += $this->ishikawa_ability_scores[$user_id];
        //小数点第二位で四捨五入する
        $ishikawa_new_ability_score = $this->Rounding($ishikawa_new_ability_score);
        //printf("　＝　" . $ishikawa_new_ability_score . "<br><br>");
        //メモリを圧迫しているのは推移だった
        array_push($this->ishikawa_ability_scores_transition["$user_id"], $ishikawa_new_ability_score);
        $this->ishikawa_ability_scores[$user_id] = $ishikawa_new_ability_score;
    }

//-------------------------------------Teradaの計算式----------------------------------------------------
     /**
	  * 2013-11-04
	  * teradaでユーザの履歴を計算する関数
	  */
     private function teradaUserAssessment($history_data, $terada_delta_count, $terada_new_ability_score, $question_assessment) {
        //計算を行う前に必要なものを変数に入れておく
        $user_id              = $history_data[USER_ID];
        $question_id          = $history_data[QUESTION_ID];
        $result               = $history_data[RESULT];
        $correct_testdata_num = $history_data[CORRECT_TESTDATA_NUM];
        $testdata_num         = $history_data[TESTDATA_NUM];
        $ability_score        = $this->terada_ability_scores[$user_id];
        $difficult            = $question_assessment->getTeradaQuestionDifficult($question_id);
        
        //ここからユーザの実力計算を行う
        $delta = $this->terada_user_assessment->teradaUserDeltaFlag(
                $difficult, $ability_score, $result, $correct_testdata_num, $testdata_num);
        //出力用
        //$this->outputHistorySum($ability_score, $difficult, $delta);
        if($delta > 0) {
            $terada_new_ability_score += ($difficult - $ability_score) * $delta;
            $terada_delta_count++;
        } 
        return array($terada_delta_count, $terada_new_ability_score);
    }

    //2013-11-04
    //ここまで完成した
    private function getTeradaNewAbilityScore($user_id, $terada_delta_count, $terada_new_ability_score) {
        //printf("　＝　$terada_new_ability_score<br>");
        if($terada_delta_count > 0) {
            //printf("δが０より大きくなった回数が１回以上あったので<br>");
            //printf("追加する実力　＝　" . $terada_new_ability_score . " / " . $terada_delta_count);
            $terada_new_ability_score = $terada_new_ability_score / $terada_delta_count;
            //小数点第二位で四捨五入する
            $terada_new_ability_score = $this->Rounding($terada_new_ability_score);
            //printf("　＝　$terada_new_ability_score<br>");
        }
        //printf("最後に最終的な実力　＝　" . $this->terada_ability_scores[$user_id] . " + " . $terada_new_ability_score);
        $terada_new_ability_score += $this->terada_ability_scores[$user_id];
        //小数点第二位で四捨五入する
        $terada_new_ability_score = $this->Rounding($terada_new_ability_score);
        //printf("　＝　" . $terada_new_ability_score . "<br><br>");
        //メモリを圧迫しているのは推移だった
        array_push($this->terada_ability_scores_transition["$user_id"], $terada_new_ability_score);
        $this->terada_ability_scores[$user_id] = $terada_new_ability_score;
    }
//------------------------------------------------------------------------------------------------------

    private function Rounding($ability_score) {
        $ability_score = $ability_score * 10;
        $ability_score = round($ability_score);
        $ability_score = $ability_score / 10;
        return $ability_score;
    }

//------------------------------ここから下は推移などの記録用------------------------------------------------------------    
    /**
     * 2013-10-14
     * 石川
     * 全てのability_scores_transitionをファイルに記録する
     */
    public function writeAbilityScoreTransition() {
        $this->writeOrignalTransition();
        $this->writeIshikawaTransition();
        $this->writeTeradaTransition();
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
    
    private function writeIshikawaTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./UserTransition/Ishikawa/User" . $i . ".txt";
            $fp = fopen($file_name, "w");
            for($j = 0; $j < count($this->ishikawa_ability_scores_transition[$i]); $j++) {
                fwrite($fp, $this->ishikawa_ability_scores_transition[$i][$j] . "\n");
            }
            fclose($fp);
            unset($this->ishikawa_ability_scores_transition[$i]);
            $this->ishikawa_ability_scores_transition[$i] = array();
        }
    }
    
    private function writeTeradaTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./UserTransition/Terada/User" . $i . ".txt";
            $fp = fopen($file_name, "w");
            for($j = 0; $j < count($this->terada_ability_scores_transition[$i]); $j++) {
                fwrite($fp, $this->terada_ability_scores_transition[$i][$j] . "\n");
            }
            fclose($fp);
            unset($this->terada_ability_scores_transition[$i]);
            $this->terada_ability_scores_transition[$i] = array();
        }
    }
    
    /**
     * 2013-10-26
     * 計算式が３つあるため、これを呼び出せば３つ全ての書き込みを行わせる事ができるため設置
     */
    public function overwriteAbilityScoreTransition() {
        $this->overwriteOrignalTransition();
        $this->overwriteIshikawaTransition();
        $this->overwriteTeradaTransition();
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
     * ishikawaの計算式における、実力の推移をファイルに追加記入するための関数
     */
    private function overwriteIshikawaTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./UserTransition/Ishikawa/User" . $i . ".txt";
            $fp = fopen($file_name, "a");
            for($j = 0; $j < count($this->ishikawa_ability_scores_transition[$i]); $j++) {
                fwrite($fp, $this->ishikawa_ability_scores_transition[$i][$j] . "\n");
            }
            fclose($fp);
            unset($this->ishikawa_ability_scores_transition[$i]);
            $this->ishikawa_ability_scores_transition[$i] = array();
        }
    }
    
    /**
     * teradaの計算式における、実力の推移をファイルに追加記入するための関数
     */
    private function overwriteTeradaTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./UserTransition/Terada/User" . $i . ".txt";
            $fp = fopen($file_name, "a");
            for($j = 0; $j < count($this->terada_ability_scores_transition[$i]); $j++) {
                fwrite($fp, $this->terada_ability_scores_transition[$i][$j] . "\n");
            }
            fclose($fp);
            unset($this->terada_ability_scores_transition[$i]);
            $this->terada_ability_scores_transition[$i] = array();
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
    
//------------------------------------------------------------------------------------------------------------------------
    //これ以降は計算が正しく行われているかをチャックするための関数
    private function outputOrignalUserHistory($user_history, $question_assessment) {
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

    //Ishikawaの計算式が正しく行われているか確認用
    private function outputIshikawaUserHistory($user_history, $question_assessment) {
        printf("ユーザ" . $user_history[0][USER_ID] . "の履歴<br>");
        printf("問題を解く前の実力　＝　" . $this->ishikawa_ability_scores[$user_history[0][USER_ID]] . "<br>");
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
            printf("<td>" . $question_assessment->getIshikawaQuestionDifficult($user_history[$i][QUESTION_ID]) . "</td>");
            printf("<td>" . $user_history[$i][RESULT] . "</td>");
            printf("<td>" . $user_history[$i][TESTDATA_NUM] . "</td>");
            printf("<td>" . $user_history[$i][CORRECT_TESTDATA_NUM] . "</td>");
            printf("</tr>");
        }
        printf("</table>");
        printf("<br>");
        printf("履歴の総和　＝　");
    }
    
	/**
	 * 2013-11-04
	 * teradaの計算式が正しく動いているか確認する
	 */
	private function outputTeradaUserHistory($user_history, $question_assessment) {
        printf("ユーザ" . $user_history[0][USER_ID] . "の履歴<br>");
        printf("問題を解く前の実力　＝　" . $this->terada_ability_scores[$user_history[0][USER_ID]] . "<br>");
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
            printf("<td>" . $question_assessment->getTeradaQuestionDifficult($user_history[$i][QUESTION_ID]) . "</td>");
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
//------------------------------------------------------------------------------------------------------------------------

}

?>