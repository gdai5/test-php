<?php
/**
 * 2013-11-07
 * 三つの計算式の実装が完了
 */

require_once("./QuestionAssessment/SimulationOrignalQuestionAssessment.php");
require_once("./SimulationRun.php");

class SimulationQuestionAssessment {
    //orignal計算式での、難易度の推移を記録
    private $orignal_difficult_transition  = array();
    //ishikawa計算式での、難易度の推移を記録
    private $ishikawa_difficult_transition = array();
	//terada計算式での、難易度の推移を記録
	private $terada_difficult_transition   = array();
    
    //各計算式での今現在の難易度を保管
    private $orignal_difficult  = array();
    private $ishikawa_difficult = array();
	private $terada_difficult   = array();
    
    private $orignal_question_assessment;
    
    //2013-10-23
    //正規化を用いる為にそのユーザが何回正解したかをカウントしておくもの
    private $accepted_counter = array();
    
    //2013-11-29
    //初期設定で与えられた難易度を保管する
    private $init_difficults = array();
    
    
    /**
     * 難易度の推移と今現在の難易度を保管する配列たちを初期化
     * 2013-11-29
     * 初期設定で与えた難易度を簡単に取得出来るように、新しい配列（$init_difficults）を用意
     */
    function initialize($true_difficult) {
       for($i = 0; $i < DATA_NUM; $i++) {
           //2013-11-09
           //ここを少し弄った
           //難易度を変動させる時
           $difficult = mt_rand(1, 10);
           //難易度を固定する時
           //$difficult = $true_difficult[$i];
		   /**
		    * 3つの式それぞれの推移を記録する変数を初期化
		    * *_difficult 現段階での難易度を保持
		    * *_difficult_transition 難易度の推移を次々と格納していく（連想配列）
		    **/
           $this->orignal_difficult_transition["$i"]  = array($difficult);
           $this->ishikawa_difficult_transition["$i"] = array($difficult);
		   $this->terada_difficult_transition["$i"]   = array($difficult);
		   $this->orignal_difficult[$i]  = $difficult;
		   $this->ishikawa_difficult[$i] = $difficult;
		   $this->terada_difficult[$i]   = $difficult;
           //2013-11-29　同じシミュレーションの状況を実現させるために用意した
           $this->init_difficults[$i] = $difficult;
           
       }
       $this->orignal_question_assessment = new SimulationOrignalQuestionAssessment();
    }
    
    /**
     * 2013-12-02
     * 同じシミュレーションを行えるようにした初期化
     */
    function initSameSimulation(array $init_difficults) {
        for($i = 0; $i < count($init_difficults); $i++) {
            $difficult = $init_difficults[$i];
            $this->orignal_difficult_transition["$i"]  = array($difficult);
            $this->ishikawa_difficult_transition["$i"] = array($difficult);
            $this->terada_difficult_transition["$i"]   = array($difficult);
            $this->orignal_difficult[$i]  = $difficult;
            $this->ishikawa_difficult[$i] = $difficult;
            $this->terada_difficult[$i]   = $difficult;
            //2013-11-29　同じシミュレーションの状況を実現させるために用意した
            $this->init_difficults[$i] = $difficult;
        }
        $this->orignal_question_assessment = new SimulationOrignalQuestionAssessment();
    }
    
    public function Assessment($question_history, $user_assessment, $point_in_time_orignal_ability_scores, $point_in_time_ishikawa_ability_scores, $point_in_time_terada_ability_scores) {
         //各計算でξが0以外になった回数をカウント
        $orignal_xi_count  = 0;
        $ishikawa_xi_count = 0;
        $terada_xi_count   = 0;
      
        //各計算式で求まった新しい難易度
        $orignal_new_difficult  = 0;
        $ishikawa_new_difficult = 0;
        $terada_new_difficult   = 0;
        
        $question_id = $question_history[0][QUESTION_ID];
        
        //2013-10-10
        //計算が正しく行われているかを確認するため
        //orignalの出力
        // printf("--------------orignal--------------------<br>");
        // $this->outputQuestionHistory($question_history, 
                                     // $user_assessment, 
                                     // $point_in_time_orignal_ability_scores[$question_id], 
                                     // $this->orignal_difficult[$question_id]);
        
        //保存されている履歴の回数分計算を繰り返す
        for ($i = 0; $i < count($question_history); $i++) {
            //orignalでの難易度計算
            list($orignal_xi_count, $orignal_new_difficult) = $this->orignalQuestionAssessment(
                    $question_history[$i], $orignal_xi_count, 
                    $orignal_new_difficult, $this->orignal_difficult[$question_id], 
                    $point_in_time_orignal_ability_scores[$question_id][$i]);
        }
        //orignalでの最終計算
        $this->orignal_difficult[$question_id] = $this->getOrignalNewDifficult($question_id, 
                                                                               $orignal_xi_count, 
                                                                               $orignal_new_difficult, 
                                                                               $this->orignal_difficult[$question_id]);
        
        // printf("--------------orignal END--------------------<br>");
                
        // printf("--------------ishikawa--------------------<br>");
        // //ishikawaの出力用
        // $this->outputQuestionHistory($question_history, 
                                     // $user_assessment, 
                                     // $point_in_time_ishikawa_ability_scores[$question_id], 
                                     // $this->ishikawa_difficult[$question_id]);
        //ishikawaでの難易度計算                             
        for ($i = 0; $i < count($question_history); $i++) {
            list($ishikawa_xi_count, $ishikawa_new_difficult) = $this->orignalQuestionAssessment(
                    $question_history[$i], $ishikawa_xi_count, 
                    $ishikawa_new_difficult, $this->ishikawa_difficult[$question_id], 
                    $point_in_time_ishikawa_ability_scores[$question_id][$i]);
        }
        //ishikawaでの最終計算
        $this->ishikawa_difficult[$question_id] = $this->getOrignalNewDifficult($question_id, 
                                                                                $ishikawa_xi_count, 
                                                                                $ishikawa_new_difficult, 
                                                                                $this->ishikawa_difficult[$question_id]);
        
        // printf("--------------ishikawa END--------------------<br>");
        
        // printf("--------------terada--------------------<br>");
        // //teradaの出力用
        // $this->outputQuestionHistory($question_history, 
                                     // $user_assessment, 
                                     // $point_in_time_terada_ability_scores[$question_id], 
                                     // $this->terada_difficult[$question_id]);
        //teradaでの難易度計算                             
        for ($i = 0; $i < count($question_history); $i++) {
            list($terada_xi_count, $terada_new_difficult) = $this->orignalQuestionAssessment(
                    $question_history[$i], $terada_xi_count, 
                    $terada_new_difficult, $this->terada_difficult[$question_id], 
                    $point_in_time_terada_ability_scores[$question_id][$i]);
        }
        $this->terada_difficult[$question_id] = $this->getOrignalNewDifficult($question_id, 
                                                                              $terada_xi_count, 
                                                                              $terada_new_difficult, 
                                                                              $this->terada_difficult[$question_id]);
        
        //printf("--------------terada END--------------------<br>");
    }

    
    /**
     * 2013-10-08
     * 問題の難易度を計算するプログラム
     */
    private function orignalQuestionAssessment($history_data, $xi_count, $new_difficult, $difficult, $ability_score) {
        //計算を行う前に必要なものを変数に入れておく
        $user_id      = $history_data[USER_ID];
        $question_id  = $history_data[QUESTION_ID];
        $result       = $history_data[RESULT];
        
        //ここから問題の難易度計算を行う
        $xi = $this->orignal_question_assessment->orignalQuestionXiFlag($difficult, $ability_score ,$result);
        //出力用
        //$this->outputHistorySum($ability_score, $difficult, $xi);
        if($xi > 0) {
                $new_difficult += ($ability_score - $difficult) * $xi;
                $xi_count++;
        } 
        return array($xi_count, $new_difficult);
    }
    
    /**
     * 2013-10-08
     * 履歴の計算が全て終わったら、最終的な難易度を計算する
     */
    private function getOrignalNewDifficult($question_id, $xi_count, $new_difficult, $now_difficult) {
        //printf("　＝　$new_difficult<br>");
        if($xi_count > 0) {
            //printf("ξが０より大きくなった回数が１回以上あったので<br>");
            //printf("追加する難易度　＝　" . $new_difficult . " / " . $xi_count);
            $new_difficult = $new_difficult / $xi_count;
            //小数点第二位で四捨五入する
            $new_difficult = $this->Rounding($new_difficult);
            //printf("　＝　$new_difficult<br>");
        }
        //printf("計算結果の難易度　＝　" . $now_difficult . " + " . $new_difficult);
        $new_difficult += $now_difficult;
        //小数点第二位で四捨五入する
        $new_difficult = $this->Rounding($new_difficult);
        //printf("　＝　" . $new_difficult . "<br><br>");
        return $new_difficult;
    }

    /**
     * 2013-10-29
     * 四捨五入
     */
    private function Rounding($difficult) {
        $difficult = $difficult * 10;
        $difficult = round($difficult);
        $difficult = $difficult / 10;
        return $difficult;
    }

    /**
     * 2013-10-14
     * 石川
     * 全てのability_scores_transitionをファイルに記録する
     */
    public function writeDifficultTransition() {
        $this->writeOrignalTransition();
        $this->writeIshikawaTransition();
        $this->writeTeradaTransition();
    }
    
    /**
     * 2013-11-07
     * orignalの計算式で求まった難易度の推移を記録していく
     */
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
    
    /**
     * 2013-11-07
     * ishikawaの計算式で求まった難易度の推移を記録していく
     */
    private function writeIshikawaTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./QuestionTransition/Ishikawa/Question" . $i . ".txt";
            $fp = fopen($file_name, "w");
            for($j = 0; $j < count($this->ishikawa_difficult_transition[$i]); $j++) {
                fwrite($fp, $this->ishikawa_difficult_transition[$i][$j] . "\n");
            }
            fclose($fp);
        }
    }
    
    /**
     * 2013-11-07
     * teradaの計算式で求まった難易度の推移を記録していく
     */
    private function writeTeradaTransition() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $file_name = "./QuestionTransition/Terada/Question" . $i . ".txt";
            $fp = fopen($file_name, "w");
            for($j = 0; $j < count($this->terada_difficult_transition[$i]); $j++) {
                fwrite($fp, $this->terada_difficult_transition[$i][$j] . "\n");
            }
            fclose($fp);
        }
    }
    
    //SimlationUserAssessment.phpでoriginalの計算式で使う難易度を取得するための関数
    public function getOrignalQuestionDifficult($question_id) {
        return $this->orignal_difficult[$question_id];
    }
    
    //SimlationUserAssessment.phpでishikawaの計算式で使う難易度を取得するための関数
    public function getIshikawaQuestionDifficult($question_id) {
        return $this->ishikawa_difficult[$question_id];
    }
	
	public function getTeradaQuestionDifficult($question_id) {
		return $this->terada_difficult[$question_id];
	}
    
    /**
     * 2013-10-23
     * 計算されて求まった難易度から正規化を行う
     */
    public function normalization() {
       $this->orignalNomalization();
       $this->ishikawaNomalization();
       $this->teradaNomalization();
    }

    //Orignal, Ishikawa, Terada用の３つの正規化を書かなければいけない
    
    private function orignalNomalization() {
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
        //printf("orignalの正規化の範囲" . $min_difficult . "~" . $max_difficult . "<br>");
        for($i = 0; $i < count($this->orignal_difficult); $i++) {
            //printf("難易度の正規化をします<br>");
            //printf("問題" . $i . "の正規前　＝　" . $this->orignal_difficult[$i] . "<br>");
            //printf("問題" . $i . "の正規化<br>");
            $difficult = (10 / ($max_difficult - $min_difficult)) * ($this->orignal_difficult[$i] - $min_difficult);
            $difficult = $this->Rounding($difficult);
            //printf("正規化後の難易度＝" . $difficult . "<br>");
            //printf("<br>");
            $this->orignal_difficult[$i] = $difficult;
            array_push($this->orignal_difficult_transition["$i"], $difficult);
        }
    }
    
    private function ishikawaNomalization() {
        $max_difficult = 0;
        $min_difficult = 10;
        for($i = 0; $i < count($this->ishikawa_difficult); $i++) {
            //最大値の更新
            if($this->ishikawa_difficult[$i] > $max_difficult) {
                $max_difficult = $this->ishikawa_difficult[$i];
            }
            //最小値の更新
            if($this->ishikawa_difficult[$i] < $min_difficult) {
                $min_difficult = $this->ishikawa_difficult[$i];
            }
        }
        //printf("ishikawaの正規化の範囲" . $min_difficult . "~" . $max_difficult . "<br>");
        for($i = 0; $i < count($this->ishikawa_difficult); $i++) {
            //printf("難易度の正規化をします<br>");
            //printf("問題" . $i . "の正規前　＝　" . $this->ishikawa_difficult[$i] . "<br>");
            //printf("問題" . $i . "の正規化<br>");
            $difficult = (10 / ($max_difficult - $min_difficult)) * ($this->ishikawa_difficult[$i] - $min_difficult);
            $difficult = $this->Rounding($difficult);
            //printf("正規化後の難易度＝" . $difficult . "<br>");
            //printf("<br>");
            $this->ishikawa_difficult[$i] = $difficult;
            array_push($this->ishikawa_difficult_transition["$i"], $difficult);
        }
    }
    
    private function teradaNomalization() {
        $max_difficult = 0;
        $min_difficult = 10;
        for($i = 0; $i < count($this->terada_difficult); $i++) {
            //最大値の更新
            if($this->terada_difficult[$i] > $max_difficult) {
                $max_difficult = $this->terada_difficult[$i];
            }
            //最小値の更新
            if($this->terada_difficult[$i] < $min_difficult) {
                $min_difficult = $this->terada_difficult[$i];
            }
        }
        //printf("teradaの正規化の範囲" . $min_difficult . "~" . $max_difficult . "<br>");
        for($i = 0; $i < count($this->terada_difficult); $i++) {
            //printf("難易度の正規化をします<br>");
            //printf("問題" . $i . "の正規前　＝　" . $this->terada_difficult[$i] . "<br>");
            //printf("問題" . $i . "の正規化<br>");
            $difficult = (10 / ($max_difficult - $min_difficult)) * ($this->terada_difficult[$i] - $min_difficult);
            $difficult = $this->Rounding($difficult);
            //printf("正規化後の難易度＝" . $difficult . "<br>");
            //printf("<br>");
            $this->terada_difficult[$i] = $difficult;
            array_push($this->terada_difficult_transition["$i"], $difficult);
        }
    }
    
    //これ以降は計算が正しく行われているかをチャックするための関数
    private function outputQuestionHistory($question_history, $user_assessment, $ability_scores, $difficult) {
        printf("問題" . $question_history[0][QUESTION_ID] . "の履歴<br>");
        printf("計算前の問題の難易度　＝　" . $difficult . "<br>");
        printf("<table>");
        printf("<tr>");
        printf("<td>挑戦したユーザID</td>");
        printf("<td>挑戦した当時の実力</td>");
        printf("<td>結果</td>");
        printf("<td>テストデータ数</td>");
        printf("<td>正解したテストデータ数</td>");
        printf("</tr>");
        for($i = 0; $i < count($question_history); $i++) {
            printf("<tr>");
            printf("<td>" . $question_history[$i][USER_ID] . "</td>");
            //挑戦した当時の実力
            printf("<td>" . $ability_scores[$i] . "</td>");
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
    
    public final function getInitDifficult() {
        return $this->init_difficults;
    }
    
}

?>