<style>
table {
    border-collapse: collapse;
}
td {
    border: solid 1px;
    padding: 0.5em;
}
</style>

<?php

/**
 * 2014-10-04
 * シミュレーション全体の流れ
 * 1:真の実力、真の難易度、テストデータ数を生成
 * 2:実力と難易度を生成
 * 3:誰がどの問題に挑戦したかを示すデータセットを生成
 * 4:データセットを一つ取り出し、NomalUserModelを動かして結果を得る
 * 5:結果をユーザと問題の履歴に記録
 * 6:実力と難易度を再計算
 * 7:4~6をデータセットの数だけ繰り返す
 */
 
/**
 * 2014-10-14
 * 石川
 * オリジナルの計算式を完成させて動かしてみたが、論文通りの結果を得られなかった
 * そこで原因究明を考える
 * 途中で、問題の難易度が一定の値で固まってしまうためそれ以降変化が起きない
 */

//近い内に、ユーザモデルが正しいかどうか検査する
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";

//必要なファイルの読み込み
require_once("./SimulationConst.php");
require_once("./NomalUserModel.php");
require_once("./SimulationUserAssessment.php");
require_once("./SimulationQuestionAssessment.php");

class SimulationRun{
    //真の実力、真の難易度、その問題の全テストデータ数
    private $true_ability_scores = array();
    private $true_difficult = array();
    private $question_testdata_num   = array();
    
    /**
     * 2013-10-03
     * どのユーザがどの問題に挑戦したかを示すデータセット
     * $data_set = [[user_id, question_id].....]
     */
    private $data_set = array();
     
    /**
     * 2013-10-04
     * 各ユーザおよび各問題の履歴
     * 最大で３０件ずつを必要とするため、配列の何番目に入れるのかを示すものも用意する
     */
    //$users_history = $questions_history = [[user_id, question_id, result, correct_testdata_num]]
    private $users_history = array(array());
    private $questions_history = array(array());
    //各履歴上の何番目の要素が一番古いのかを示す配列
    //これを基に次に上書きするべき場所を知る事ができる
    //$users_oldest_history_number = [$users_historyにおいて最も古い履歴が入っている配列番号, ....]
    private $users_oldest_history_number = array();
    //$questions_oldest_history_number = [$questions_historyにおいて最も古い履歴が入っている配列番号, ....]
    private $questions_oldest_history_number = array();
    
    private $user_assessment;
    private $question_assessment;
    
     
    
    /**
     * 2013-10-02
     * シミュレーションで使う「真の実力」「真の難易度」を1~10の範囲でランダム生成する
     * また、各問題の「テストデータ数」を1~40の間で生成する
     * 各データについては１００件ずつ作成される
     * ユーザと問題の履歴の中で最も古い履歴を示す配列の番号を０で初期化する
     * 2013-10-15
     * $true_ability_score = $i;
     * $true_difficult     = $i;
     * このように変えてしまったので原因が究明できたら修正
     */
    private function initialize() {
        for($i = 0; $i < DATA_NUM; $i++){
            $true_ability_score = $this->getRandomRealNumber();
            $true_difficult     = $this->getRandomRealNumber();
            if($i == 0) {
                $true_difficult = 0;
            }
            if($i == 1) {
                $true_difficult = 10;
            }
            $this->true_ability_scores[$i] = $true_ability_score;
            $this->true_difficult[$i] = $true_difficult;
            $this->question_testdata_num[$i]   = mt_rand(1, 40);
            
            //ユーザの履歴を初期化
            $this->users_history[$i][0] = array();
            /**
             * 問題の履歴を初期化
             * ただし、この履歴に関してはある一定時間取った後に計算を行い
             * その後初期化する
             */
            $this->questions_history[$i][0] = array();
            
            //ユーザと問題の履歴の中で最も古い履歴を示す配列の番号を０で初期化する
            $this->users_oldest_history_number[$i] = 0;
            $this->questions_oldest_history_number[$i] = 0;
        }
        /**
         * 2013-10-10
         * コンストラクタを使うと非常に面倒なことがおきたので
         * initializeを代わりにした
         */
        $this->user_assessment = new SimulationUserAssessment();
        $this->user_assessment->initialize();
        $this->question_assessment = new SimulationQuestionAssessment();
        $this->question_assessment->initialize();
    }
    
    
    /**
     * 2013-10-02
     * 各計算式におけるユーザの実力と問題の難易度を初期化するための関数
     */
    private function setAbilityScoreAndDifficult() {
        for($i = 0; $i < DATA_NUM; $i++) {
            $ability_score = $this->getRandomRealNumber();
            $this->orignal_user_ability_score[$i]  = $ability_score;
            $this->ishikawa_user_ability_score[$i] = $ability_score;
            $this->terada_user_ability_score[$i]   = $ability_score;
            $difficult     = $this->getRandomRealNumber();
            $this->orignal_question_difficult[$i]  = $difficult;
            $this->ishikawa_question_difficult[$i] = $difficult; 
            $this->terada_question_difficult[$i]   = $difficult;
        }
    }
     
    
    //1~10までの範囲で小数点第一位までの実数を返す
    private function getRandomRealNumber() {
        $r = mt_rand(1, 10);
        $r = $r / 10;
        $r = mt_rand(1, 9) + $r;
        return $r;
    }
    
    //データセットの生成
    private function makeDataSet() {
        for($i = 0; $i < 100; $i++) {
            for($j = 0; $j < DATA_NUM; $j++) {
                $data = array($j, mt_rand(0, 9));
                array_push($this->data_set, $data);
            }
        }
    }
    
    
    
    /**
     * 2013-10-22
     * ユーザの履歴を更新する関数
     */
    private function updateUserHistory($user_id, $question_id, $result, $correct_testdata_num, $testdata_num) {
        //ユーザの履歴を更新
        $user_update_num = $this->users_oldest_history_number[$user_id];
        $this->users_history[$user_id][$user_update_num] = 
                array($user_id, $question_id, $result, $correct_testdata_num, $testdata_num);
        //履歴の中で次に更新すべき場所を決める
        //$this->user_assessment->chkAnswerAccepted($user_id, $result);
        if($this->users_oldest_history_number[$user_id] < 29) {
            $this->users_oldest_history_number[$user_id] += 1;
        }else{
            $this->users_oldest_history_number[$user_id] = 0;
        }
    }
    
    /**
     * 2013-10-22
     * 問題毎に履歴を書き込むための関数
     */
    private function updateQuestionHistory($user_id, $question_id, $result, $correct_testdata_num, $testdata_num) {
        //問題に挑戦した当時の実力を取得するための関数
        $ability_score = $this->user_assessment->getOrignalUserAbilityScore($user_id);
        //問題の履歴を更新
        $question_update_num = $this->questions_oldest_history_number[$question_id];
        $this->questions_history[$question_id][$question_update_num] = 
                array($user_id, $question_id, $result, $correct_testdata_num, $testdata_num, $ability_score);
        $this->questions_oldest_history_number[$question_id] += 1;
        //履歴の記録
        //$this->question_assessment->chkAnswerAccepted($question_id, $result);
    }
    
    /**
     * 2013-10-04
     * シミュレーションプログラムを走らせるためのメイン関数
     */
    public function Run() {
        //真の実力、真の難易度、問題のテストデータ数を生成
        $this->initialize();
        //実力と難易度の生成
        $this->setAbilityScoreAndDifficult();
        //データセットの生成
        $this->makeDataSet();
        $nomal_user_model = new NomalUserModel();
        
        //計算回数のカウント用
        $round = 1;
        for($i = 0; $i < count($this->data_set); $i++) {
            $user_id = $this->data_set[$i][0];
            $question_id = $this->data_set[$i][1];
            $true_ability_score = $this->true_ability_scores[$user_id];
            $true_difficult     = $this->true_difficult[$question_id];
            $testdata_num       = $this->question_testdata_num[$question_id];
            list($result, $correct_testdata_num) = 
                    $nomal_user_model->run($true_ability_score, $true_difficult, $testdata_num);
            $this->updateUserHistory($user_id, $question_id, $result, $correct_testdata_num, $testdata_num);
            $this->updateQuestionHistory($user_id, $question_id, $result, $correct_testdata_num, $testdata_num);
            //ユーザの実力計算
            printf("-------------------" . $round . "回目の計算-------------------<br>");
            $this->user_assessment->Assessment($this->users_history[$user_id], $this->question_assessment);
            /**
             * 2013-10-11
             * 問題の難易度計算
             * 100回毎に全ての難易度の計算を行う
             * 計算が終わったら、計算に使った履歴を削除する
             * 無事動いている様子なので、検証する
             */ 
            if($round % ROUND == 0) {
                //直接問題の数を入れる
                for($j = 0; $j < count($this->questions_history); $j++) {
                    //一回以上誰かに問題を解かれているかどうか確認している
                    if($this->questions_oldest_history_number[$j] != 0) {
                        $this->question_assessment->Assessment(
                        $this->questions_history[$j], $this->user_assessment);
                        //ここら辺が非常にまずい気がする
                        //多分メモりを解放せずにゴミを溜め込んでると思われる
                        $this->questions_history[$j] = array(); 
                        $this->questions_history[$j][0] = array();
                        $this->questions_oldest_history_number[$j] = 0;
                    }
                }
                //正規化
                $this->question_assessment->normalization();
                //ユーザの履歴がメモリーリークの原因だったので、上書き作業をする
                if($round == ROUND) {
                    //printf("通ってる");
                    $this->user_assessment->writeAbilityScoreTransition();
                }else{
                    //printf("通った");
                    $this->user_assessment->overwriteAbilityScoreTransition();
                }
            }
            //メモリ量の確認
            //$this->dumpMemory();
            //printf("<br>");
            printf("-------------------" . $round . "回目の計算終了-------------------<br><br><br>");
            $round++;
        }
        //$this->question_assessment->outputAcceptedCounter();
        //$this->question_assessment->normalization();
        $this->user_assessment->overwriteAbilityScoreTransition();
        $this->question_assessment->writeDifficultTransition();
        $this->writeTrueAbilityScore();
        $this->writeTrueDifficult();
        printf("無事終わりました");
        
    }

    /**
     * 2013-10-14
     * 石川
     * 真の実力を保存するための関数
     */
    private function writeTrueAbilityScore() {
        $fp = fopen("./UserTransition/TrueAbilityScore.txt", "w");
        for($i = 0; $i < count($this->true_ability_scores); $i++) {
            fwrite($fp, $i . "番目" . $this->true_ability_scores[$i] . "\n");
        }
        fclose($fp);
    }
    
     /**
     * 2013-10-14
     * 石川
     * 真の難易度を保存するための関数
     */
    private function writeTrueDifficult() {
        $fp = fopen("./QuestionTransition/TrueDifficult.txt", "w");
        for($i = 0; $i < count($this->true_difficult); $i++) {
            fwrite($fp, $i . "番目" . $this->true_difficult[$i] . "\n");
        }
        fclose($fp);
    }

//--------------------------------------------確認用のプログラム（シミュレーションには関係ない）--------------------------------------------
    //履歴の更新がちゃんとできているか確認用プログラム
    private function chkUserHistory() {
        printf("<br>");
        printf("<br>");
        for($i = 0; $i < count($this->users_history); $i++) {
            for($j = 0; $j < $this->users_oldest_history_number[$i]; $j++) {
                printf("ユーザID : " . $this->users_history[$i][$j][0] . "<br>");
                printf("問題番号 : " . $this->users_history[$i][$j][1] . "<br>");
                printf("結果 : " . $this->users_history[$i][$j][2] . "<br>");
                printf("正解数 : " . $this->users_history[$i][$j][3] . "<br>");
            }
            printf("<br>");
        }
        print_r($this->users_oldest_history_number);
        printf("<br>");
        printf("<br>");
    }
    
    //一ユーザが挑戦した場合の件数を表示
    private function chkUpdateUserHistoryNum() {
        printf("<br>");
        printf("<br>");
        for($j = 0; $j < 30; $j++) {
                printf("ユーザID : " . $this->users_history[0][$j][0] . "<br>");
                printf("問題番号 : " . $this->users_history[0][$j][1] . "<br>");
                printf("結果 : " . $this->users_history[0][$j][2] . "<br>");
                printf("正解数 : " . $this->users_history[0][$j][3] . "<br>");
                printf("<br>");
        }
        print_r($this->users_oldest_history_number);
        printf("<br>");
        printf("<br>");
    }
    
    private function chkQuestionHistory() {
        printf("<br>");
        printf("<br>");
        for($i = 0; $i < count($this->questions_history); $i++) {
            for($j = 0; $j < $this->questions_oldest_history_number[$i]; $j++) {
                printf("問題番号 : " . $this->questions_history[$i][$j][1] . "<br>");
                printf("ユーザID : " . $this->questions_history[$i][$j][0] . "<br>");
                printf("結果 : " . $this->questions_history[$i][$j][2] . "<br>");
                printf("正解数 : " . $this->questions_history[$i][$j][3] . "<br>");
            }
            printf("<br>");
        }
        print_r($this->questions_oldest_history_number);
        printf("<br>");
        printf("<br>");
    }
    
    private function chkUpdateQuestionHistoryNum() {
        printf("<br>");
        printf("<br>");
        for($j = 0; $j < 30; $j++) {
            printf("問題番号 : " . $this->questions_history[0][$j][1] . "<br>");
            printf("ユーザID : " . $this->questions_history[0][$j][0] . "<br>");
            printf("結果 : " . $this->questions_history[0][$j][2] . "<br>");
            printf("正解数 : " . $this->questions_history[0][$j][3] . "<br>");
            printf("<br>");
        }
        print_r($this->questions_oldest_history_number);
        printf("<br>");
        printf("<br>");
    }
    
    //ユーザモデルが正しく動いているかどうか確認する
    public function nomalUserModelTest() {
        $true_ability_score = 5;
        $true_difficult     = 5;
        $testdata_num       = 20;
        //どの結果が何回出たのかをカウント
        $totaling = array(0, 0, 0, 0, 0);
        $nomal_user_model   = new NomalUserModel();
        printf("<table>");
        printf("<tr>");
        printf("<td>番号</td>");
        printf("<td>真の実力</td>");
        printf("<td>真の難易度</td>");
        printf("<td>結果</td>");
        printf("<td>テストデータ数</td>");
        printf("<td>正解したテストデータ数</td>");
        printf("</tr>");
        for($i = 0; $i < 1000; $i++) {
            list($result, $correct_testdata_num) = 
                    $nomal_user_model->run($true_ability_score, $true_difficult, $testdata_num);
            printf("<tr>");
            printf("<td>$i</td>");
            printf("<td>$true_ability_score</td>");
            printf("<td>$true_difficult</td>");
            printf("<td>$result</td>");
            printf("<td>$testdata_num</td>");
            printf("<td>$correct_testdata_num</td>");
            printf("</tr>");
            $totaling[$result] += 1;
        }
        printf("</table>");
        print_r($totaling);
    }

    private function outputTrueAbilityScoreAndDifficult() {
        printf("<table>");
        printf("<tr>");
        printf("<td>ユーザID</td>");
        printf("<td>真の実力</td>");
        printf("<td>挑戦した問題ID</td>");
        printf("<td>真の難易度</td>");
        printf("</tr>");
        for($i = 0; $i < count($this->true_ability_scores); $i++) {
            printf("<tr>");
            printf("<td>$i</td>");
            printf("<td>" . $this->true_ability_scores[$i] . "</td>");
            printf("<td>$i</td>");
            printf("<td>" . $this->true_difficult[$i] . "</td>");
            printf("</tr>");
        }
        printf("</table>");
    }

    private function chkBiteSize() {
        printf("user_history byte size = " . var_dump(count($this->users_history)) . "<br>");
        for($i = 0; $i < count($this->users_history); $i++) {
            printf("user_history[" . $i . "] byte size = " . var_dump(count($this->users_history[$i])) . "<br>");
        }
        //$questions_history = array(array());
        //$users_oldest_history_number = array();
        //$questions_oldest_history_number = array();
    }
    
    private function dumpMemory()  {  
        static $initialMemoryUse = null;  
        if ( $initialMemoryUse === null )  {  
            $initialMemoryUse = memory_get_usage();  
        }  
        var_dump(number_format(memory_get_usage() - $initialMemoryUse));  
    }  
//--------------------------------------------確認用のプログラム（シミュレーションには関係ない）--------------------------------------------
} 

/**
 * 2013/10/2
 * シミュレーション実験向けに全ての処理をメインメモリ上で行うように変更する
 * 旧バージョン=>「OldSimulationRun.php」
 */

$simulation_run = new SimulationRun();
$simulation_run->Run();
//NomalUserModelの検証完了 2013-10-07
//$simulation_run->nomalUserModelTest();

?>