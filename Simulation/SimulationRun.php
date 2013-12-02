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
 * 最終更新日
 * 2013-12-02
 * 同じシミュレーションが実行できるようになった
 */
 

//近い内に、ユーザモデルが正しいかどうか検査する
echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";

//必要なファイルの読み込み
require_once("./SimulationConst.php");
require_once("./NomalUserModel.php");
require_once("./SimulationUserAssessment.php");
require_once("./SimulationQuestionAssessment.php");
require_once("./SaveSimulationDatas.php");
require_once("./LoadSimulationDatas.php");

class SimulationRun{
    //真の実力、真の難易度、その問題の全テストデータ数
    private $true_ability_scores = array();
    private $true_difficults = array();
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
    //履歴とは別に問題を解いた当時の実力を保管しておくための配列を用意
    private $point_in_time_orignal_ability_scores = array(array());
    private $point_in_time_ishikawa_ability_scores = array(array());
	private $point_in_time_terada_ability_scores = array(array());
    //各履歴上の何番目の要素が一番古いのかを示す配列
    //これを基に次に上書きするべき場所を知る事ができる
    //$users_oldest_history_number = [$users_historyにおいて最も古い履歴が入っている配列番号, ....]
    private $users_oldest_history_number = array();
    //$questions_oldest_history_number = [$questions_historyにおいて最も古い履歴が入っている配列番号, ....]
    private $questions_oldest_history_number = array();
    
    private $user_assessment;
    private $question_assessment;
    
    /**
     * 2013-11-28
     * 様々なユーザモデルでの実力と難易度推移を記録するために
     * rand_xで出た値も取得するため用意
     */ 
    private $rand_x_list = array();
    private $save_simulation_datas;
    
    
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
            $this->true_difficults[$i]      = $true_difficult;
            $this->question_testdata_num[$i]   = mt_rand(1, 40);
            
            //ユーザの履歴を初期化
            $this->users_history[$i][0] = array();
            /**
             * 問題の履歴を初期化
             * ただし、この履歴に関してはある一定時間取った後に計算を行い
             * その後初期化する
             */
            $this->questions_history[$i][0] = array();
            $this->point_in_time_orignal_ability_scores[$i][0]  = array();
            $this->point_in_time_ishikawa_ability_scores[$i][0] = array();
			$this->point_in_time_terada_ability_scores[$i][0]   = array();
            
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
        $this->question_assessment->initialize($this->true_difficults);
        $this->save_simulation_datas = new SaveSimulationDatas();
        
        /**
         * 2013-11-28
         * 予め使うべきrand_xを準備しておく
         */
        for($i = 0; $i < 100000; $i++) {
            $this->rand_x_list[$i] = mt_rand(0, 100);
        }
    }
    
    /**
     * 2013-12-01
     * 同じシミュレーションを実行できるように変更した初期化
     */
    public final function loadSimulationDatas() {
        $load_simulation_datas       = new LoadSimulationDatas();
        $this->true_ability_scores   = $load_simulation_datas->loadTrueAbilityScores();
        $this->true_difficults       = $load_simulation_datas->loadTrueDifficults();
        $this->question_testdata_num = $load_simulation_datas->loadTestdataNum();
        $this->data_set              = $load_simulation_datas->loadDataSets();
        $this->rand_x_list           = $load_simulation_datas->loadRandXList();
        //必要な初期化
        for($i = 0; $i < DATA_NUM; $i++){
            //ユーザの履歴を初期化
            $this->users_history[$i][0] = array();
            /**
             * 問題の履歴を初期化
             * ただし、この履歴に関してはある一定時間取った後に計算を行い
             * その後初期化する
             */
            $this->questions_history[$i][0] = array();
            $this->point_in_time_orignal_ability_scores[$i][0]  = array();
            $this->point_in_time_ishikawa_ability_scores[$i][0] = array();
            $this->point_in_time_terada_ability_scores[$i][0]   = array();
            
            //ユーザと問題の履歴の中で最も古い履歴を示す配列の番号を０で初期化する
            $this->users_oldest_history_number[$i] = 0;
            $this->questions_oldest_history_number[$i] = 0;
        }
        //init_ability_scoresをセットするため
        $this->user_assessment = new SimulationUserAssessment();
        $init_ability_scores = $load_simulation_datas->loadInitAbilityScores();
        $this->user_assessment->initSameSimulation($init_ability_scores);
        //init_difficultsをセットするため
        $this->question_assessment = new SimulationQuestionAssessment();
        $init_difficults = $load_simulation_datas->loadInitDifficults();
        $this->question_assessment->initSameSimulation($init_difficults);
    }
    
    //1~10までの範囲で小数点第一位までの実数を返す
    private function getRandomRealNumber() {
        $r = mt_rand(0, 10);
        $r = $r / 10;
        $r = mt_rand(1, 9) + $r;
        return $r;
    }
    
    //データセットの生成
    private function makeDataSet() {
        $question_number = DATA_NUM - 1;
        for($i = 0; $i < 1000; $i++) {
            for($j = 0; $j < DATA_NUM; $j++) {
                $data = array($j, mt_rand(0, $question_number));
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
        //履歴が３０件以上たまっているかどうかチェック
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
        //問題の履歴を更新
        $question_update_num = $this->questions_oldest_history_number[$question_id];
        $this->questions_history[$question_id][$question_update_num] = 
                array($user_id, $question_id, $result, $correct_testdata_num, $testdata_num);
        //問題に挑戦した当時の実力を取得するための関数
        $orignal_ability_score  = $this->user_assessment->getOrignalUserAbilityScore($user_id);
        $ishikawa_ability_score = $this->user_assessment->getIshikawaUserAbilityScore($user_id);
		$terada_ability_score   = $this->user_assessment->getTeradaUserAbilityScore($user_id);
        //当時の実力を別途で保管
        $this->point_in_time_orignal_ability_scores[$question_id][$question_update_num]  = $orignal_ability_score;
        $this->point_in_time_ishikawa_ability_scores[$question_id][$question_update_num] = $ishikawa_ability_score;
		$this->point_in_time_terada_ability_scores[$question_id][$question_update_num]   = $terada_ability_score;
        $this->questions_oldest_history_number[$question_id] += 1;
    }
    
    /**
     * 2013-10-04
     * シミュレーションプログラムを走らせるためのメイン関数
     */
    public function Run() {
        //真の実力、真の難易度、問題のテストデータ数を生成
        //$this->initialize();
        //データセットの生成
        //$this->makeDataSet();
        //こっちが同じシミュレーションをするための初期化
        $this->loadSimulationDatas();
        $nomal_user_model = new NomalUserModel();
        
        //計算回数のカウント用
        $round = 1;
        for($i = 0; $i < count($this->data_set); $i++) {
            $user_id     = $this->data_set[$i][0];
            $question_id = $this->data_set[$i][1];
            $true_ability_score = $this->true_ability_scores[$user_id];
            $true_difficult     = $this->true_difficults[$question_id];
            $testdata_num       = $this->question_testdata_num[$question_id];
            //間違ったときの割合を取得するために、rand_xで出た値も取得するため
            list($result, $correct_testdata_num) = 
                    $nomal_user_model->run($true_ability_score, $true_difficult, $testdata_num, $this->rand_x_list[$i]);
            $this->updateUserHistory($user_id, $question_id, $result, $correct_testdata_num, $testdata_num);
            //難易度を固定にする場合はここも消さないとメモリーを圧迫する
            $this->updateQuestionHistory($user_id, $question_id, $result, $correct_testdata_num, $testdata_num);
            //ユーザの実力計算
            //printf("-------------------" . $round . "回目の計算-------------------<br>");
            $this->user_assessment->Assessment($this->users_history[$user_id], $this->question_assessment);
            
            //10000回毎に難易度の計算に入る
            if($round % ROUND == 0) {
                //直接問題の数を入れる
                for($j = 0; $j < count($this->questions_history); $j++) {
                    //一回以上誰かに問題を解かれているかどうか確認している
                    if($this->questions_oldest_history_number[$j] != 0) {
                        $this->question_assessment->Assessment(
                                $this->questions_history[$j], $this->user_assessment, 
                                $this->point_in_time_orignal_ability_scores, 
                                $this->point_in_time_ishikawa_ability_scores,
								$this->point_in_time_terada_ability_scores);
                        //履歴の削除
                        $this->questions_history[$j] = array(); 
                        $this->questions_history[$j][0] = array();
                        $this->questions_oldest_history_number[$j] = 0;
						$this->point_in_time_orignal_ability_scores[$j]     = array();
                        $this->point_in_time_orignal_ability_scores[$j][0]  = array();
                        $this->point_in_time_ishikawa_ability_scores[$j]    = array();
                        $this->point_in_time_ishikawa_ability_scores[$j][0] = array();
						$this->point_in_time_terada_ability_scores[$j]      = array();
						$this->point_in_time_terada_ability_scores[$j][0]   = array();
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
            //printf("-------------------" . $round . "回目の計算終了-------------------<br><br><br>");
            $round++;
        }
        //最後に推移などを記録
        $this->user_assessment->overwriteAbilityScoreTransition();
        $this->question_assessment->writeDifficultTransition();
        $this->writeTrueAbilityScore();
        $this->writeTrueDifficult();
        //$this->save_simulation_datas->saveDatas($this->true_ability_scores, $this->true_difficults, $this->question_testdata_num, $this->data_set, $this->user_assessment->getInitAbilityScores(), $this->question_assessment->getInitDifficult(), $this->rand_x_list);
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
        for($i = 0; $i < count($this->true_difficults); $i++) {
            fwrite($fp, $i . "番目" . $this->true_difficults[$i] . "\n");
        }
        fclose($fp);
    }
    
    /**
     * メモリーの使用量を確認するための関数
     */
    private function dumpMemory()  {  
        static $initialMemoryUse = null;  
        if ( $initialMemoryUse === null )  {  
            $initialMemoryUse = memory_get_usage();  
        }  
        var_dump(number_format(memory_get_usage() - $initialMemoryUse));  
    }
    
} 

?>