<?php
/**
 * 2013-12-01
 * 同じシミュレーションを行えるように、SaveSimulationDatasで保存した
 * シミュレーションデータをロードするクラス
 */
class LoadSimulationDatas {
    
    private $load_directory_pass = "./SimulationDatas/DataSet1";
    //2013-12-01完成
    public final function loadTrueAbilityScores() {
        $true_ability_scores = array();
        $fp = fopen($this->load_directory_pass . "/TrueAbilityScores.txt", "r");
        while ($line = fgets($fp)) {
            $line = $this->remSpace($line);
            $true_ability_scores[] = $line;
        }
        fclose($fp);
        return $true_ability_scores;
    }
    //2013-12-02 完成
    public final function loadTrueDifficults() {
        $true_difficults = array();
        $fp = fopen($this->load_directory_pass . "/TrueDifficults.txt", "r");
        while ($line = fgets($fp)) {
            $line = $this->remSpace($line);
            $true_difficults[] = $line;
        }
        fclose($fp);
        return $true_difficults;
    }
    //2013-12-02 完成
    public final function loadTestdataNum() {
        $question_testdata_num = array();
        $fp = fopen($this->load_directory_pass . "/QuestionTestdataNum.txt", "r");
        while ($line = fgets($fp)) {
            $line = $this->remSpace($line);
            $question_testdata_num[] = $line;
        }
        fclose($fp);
        return $question_testdata_num;
    }
    //2013-12-02 完成
    public final function loadDataSets() {
        $data_set = array();
        $fp = fopen($this->load_directory_pass . "/DataSets.txt", "r");
        while ($line = fgets($fp)) {
            $data = explode(',', $line);
            //二つ目の要素の最後になぜか、半角スペースが入り
            //以降の処理が上手く進まなかったので、それを取り除く処理を追加した
            $data[1] = $this->remSpace($data[1]);
            array_push($data_set, $data);
        }
        fclose($fp);
        return $data_set;
    }
    //2013-12-02 完成
    public final function loadInitAbilityScores() {
        $init_ability_scores = array();
        $fp = fopen($this->load_directory_pass . "/InitAbilityScores.txt", "r");
        while ($line = fgets($fp)) {
            $line = $this->remSpace($line);
            $init_ability_scores[] = $line;
        }
        fclose($fp);
        return $init_ability_scores;
    }
    //2013-12-02 完成
    public final function loadInitDifficults() {
        $init_difficults = array();
        $fp = fopen($this->load_directory_pass . "/InitDifficults.txt", "r");
        while ($line = fgets($fp)) {
            $line = $this->remSpace($line);
            $init_difficults[] = $line;
        }
        fclose($fp);
        return $init_difficults;
    }
    //2013-12-02 完成
    public final function loadRandXList() {
        $rand_x_list;
        $fp = fopen($this->load_directory_pass . "/RandXList.txt", "r");
        while ($line = fgets($fp)) {
            $line = $this->remSpace($line);
            $rand_x_list[] = $line;
        }
        fclose($fp);
        return $rand_x_list;
    }
    /**
     * 2013-12-02
     * ファイルからデータを読み込む際に、半角スペースが紛れ込むので、それを削除するための関数
     * これを入れないと、上手くシミュレーションが動かない
     */
    private final function remSpace($str) {
        $str = preg_replace('/(\s|　)/','',$str);
        return $str;
    }
}

?>