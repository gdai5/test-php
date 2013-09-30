<!-- 見やすくする為にテーブルを用意 -->
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

require_once("/Users/ishikawahitoshi/Sites/test-php/construct/const.php");

abstract class UserModel {
    //DBと接続するための変数
    private $mysqli;
    
    /**
     * 引数の$user_id, question_idを基に、結果をstatusテーブルに保存する
     * @param $model_datas = [[ユーザの真の実力, 問題の真の難易度, 問題に登録されているテストデータの数], ....]
     */
    public function run($data_set) {
        $true_user_ability_scores = array();
        $true_question_difficults = array();
        //問題毎にsqlを投げるのは時間が掛かるので、一回で持ってくる
        $question_testdatas       = array();
        $this->mysqli = DatabaseConnection();
        //ユーザの真の実力をHashで取得
        $true_user_ability_scores = $this->getTrueUserAbilityScores();
        //問題の真の難易度をHashで取得
        $true_question_difficults = $this->getTrueQuestionDifficults();
        //問題のテストデータ数をHashで取得
        $question_testdatas = $this->getTestdatas();
        
        /**
         * $data_set = [[user_id, question_id]....]のセット数だけ計算を繰り返す
         * 
         */
        //テーブルとしてデータを見やすくするために用意
        printf("<table>");
        printf("<tr>");
        printf("<td>セット番号</td>");
        printf("<td>ユーザID</td>");
        printf("<td>問題ID</td>");
        printf("<td>正解率</td>");
        printf("<td>結果</td>");
        printf("<td>その問題の全テストデータ数</td>");
        printf("<td>ユーザが正解したテストデータ数</td>");
        printf("</tr>");
        //$fp = fopen("test-data-set.txt", "w");
        for($i = 0; $i < count($data_set); $i++) {
            $user_id     = $data_set[$i][0];
            $question_id = $data_set[$i][1];
            //ユーザの真の実力と問題の真の難易度を引数にとり、正解率を返す
            $correct_answer_ratio = $this->getCorrectAnswerRatio($true_user_ability_scores[$user_id], 
                                                             $true_question_difficults[$question_id]);
            //ここをhtmlのテーブルで表示するようにする。
            printf("<tr>");
            printf("<td>". $i ."</td>");
            printf("<td>$user_id</td>");
            printf("<td>$question_id</td>");
            printf("<td>$correct_answer_ratio</td>");
            list($result, $correct_testdata_num) = $this->getResult($correct_answer_ratio, $question_testdatas[$question_id]);
            printf("<td>$result</td>");
            printf("<td>$question_testdatas[$question_id]</td>");
            printf("<td>$correct_testdata_num</td>");
            printf("</tr>");
            //fwrite($fp, "$user_id,$question_id,$result,$question_testdatas[$question_id],$correct_testdata_num\n");
        }
        
        printf("</table>");
        //fclose($fp);
    }
    
    /**
     * DBから真の実力をHashとして取得する
     * @return $true_user_ability_scores = {user_id => true_ability_score}
     */
    protected function getTrueUserAbilityScores() {
        $true_user_ability_scores = array();
        $query = "select user_id, true_ability_score from true_ability_score";
        $uas = $this->mysqli->query($query);
        while($row = $uas->fetch_assoc()) {
          $true_user_ability_scores[$row["user_id"]] = $row["true_ability_score"];
        }
        return $true_user_ability_scores;
    }
    
    /**
     * DBから真の難易度をHashとして取得する
     * @return $true_question_difficults = {question_id => true_difficult}
     */
    protected function getTrueQuestionDifficults() {
        $true_question_difficults = array();
        $query = "select question_id, true_difficult from true_difficult";
        $qd = $this->mysqli->query($query);
        while($row = $qd->fetch_assoc()) {
          $true_question_difficults[$row["question_id"]] = $row["true_difficult"];
        }
        return $true_question_difficults;
    }
    
    /**
     * DBから各問題のテストデータ数を取得する
     * @return $testdatas = {question_id => testdata_num}
     */
    protected function getTestdatas() {
        $testdatas = array();
        $query = "select id, testdata_num from questions";
        $td = $this->mysqli->query($query);
        while($row = $td->fetch_assoc()) {
          $testdatas[$row["id"]] = $row["testdata_num"];
        }
        return $testdatas;
    }
    
    //正解率を返す
    abstract protected function getCorrectAnswerRatio($true_ability, $true_difficult);
    //正解率から結果を返す
    abstract protected function getResult($correct_answer_ratio, $testdata_num);
      
}

?>