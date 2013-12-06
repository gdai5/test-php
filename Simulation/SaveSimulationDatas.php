<?php

/**
 * 2013-11-29
 * 複数のユーザモデルを切り替えてシミュレーションができるように
 * 同じシミュレーションを再現するために必要なデータをファイルに保存する
 */
class SaveSimulationDatas {
    
    //どの場所に保存するのかを決める
    //ここを変えれば保存場所を変えられる！
    private $save_directory_pass = "./SimulationDatas/DataSet1";
        
    /**
     * 2013-11-29
     * 同じシミュレーションを実現するために必要なデータをセーブするメイン関数
     * @param $true_ability_scores   :真の実力が格納されている配列
     * @param $true_difficults       :真の難易度が格納されている配列
     * @param $question_testdata_num :各問題のテストデータの数が格納されている配列
     * @param $data_sets             :どのユーザがどの問題に挑戦したのかが格納されている配列
     * @param $init_ability_scores   :初期設定で与えられた実力が格納されている配列
     * @param $init_difficults       :初期設定で与えられた難易度が格納されている配列
     * @param $rand_x_list           :各data_setsの要素毎に出たランダムな整数が格納されている配列
     */
    public final function saveDatas(array $true_ability_scores, array $true_difficults, array $question_testdata_num, array $data_sets, array $init_ability_scores, array $init_difficults, array $rand_x_list) {
        printf("保存を開始します<br>");
        //真の実力を保存
        $this->saveTrueAbilitys($true_ability_scores);
        //真の難易度を保存
        $this->saveTrueDifficults($true_difficults);
        //テストデータの数を保存
        $this->saveTestdataNum($question_testdata_num);
        //データセットを保存
        $this->saveDataSets($data_sets);
        //初期の実力を保存
        $this->saveInitAbilityScores($init_ability_scores);
        //初期の難易度を保存
        $this->saveInitDifficult($init_difficults);
        //データセット毎に決まった乱数を保存
        $this->saveRandXList($rand_x_list);
    }
    
    //2013-11-30　完成
    private final function saveTrueAbilitys(array $true_ability_scores){
        $fp = fopen($this->save_directory_pass . "/TrueAbilityScores.txt", "w");
        for($i = 0; $i < count($true_ability_scores); $i++) {
            fwrite($fp, $true_ability_scores[$i] . "\n");
        }
        fclose($fp);
    }
    //2013-12-01　完成
    private final function saveTrueDifficults(array $true_difficults){
        $fp = fopen($this->save_directory_pass . "/TrueDifficults.txt", "w");
        for($i = 0; $i < count($true_difficults); $i++) {
            fwrite($fp, $true_difficults[$i] . "\n");
        }
        fclose($fp);
    }
    //2013-12-01 完成
    private final function saveTestdataNum(array $question_testdata_num){
        $fp = fopen($this->save_directory_pass . "/QuestionTestdataNum.txt", "w");
        for($i = 0; $i < count($question_testdata_num); $i++) {
            fwrite($fp, $question_testdata_num[$i] . "\n");
        }
        fclose($fp);
    }
    //2013-12-01 完成
    private final function saveDataSets(array $data_sets){
        $fp = fopen($this->save_directory_pass . "/DataSets.txt", "w");
        for($i = 0; $i < count($data_sets); $i++) {
            fwrite($fp, $data_sets[$i][0] . "," . $data_sets[$i][1] . "\n");
        }
        fclose($fp);
    }
    ////2013-12-01 完成
    private final function saveInitAbilityScores(array $init_ability_scores){
        $fp = fopen($this->save_directory_pass . "/InitAbilityScores.txt", "w");
        for($i = 0; $i < count($init_ability_scores); $i++) {
            fwrite($fp, $init_ability_scores[$i] . "\n");
        }
        fclose($fp);
    }
    //2013-12-01 完成
    private final function saveInitDifficult(array $init_difficults){
        $fp = fopen($this->save_directory_pass . "/InitDifficults.txt", "w");
        for($i = 0; $i < count($init_difficults); $i++) {
            fwrite($fp, $init_difficults[$i] . "\n");
        }
        fclose($fp);
    }
    //2013-12-01 完成
    private final function saveRandXList(array $rand_x_list){
        $fp = fopen($this->save_directory_pass . "/RandXList.txt", "w");
        for($i = 0; $i < count($rand_x_list); $i++) {
            fwrite($fp, $rand_x_list[$i] . "\n");
        }
        fclose($fp);
    }
}
?>