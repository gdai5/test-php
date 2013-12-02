<?php

abstract class UserModel {
    
    //正解率を返す
    abstract protected function getCorrectAnswerRatio($true_ability, $true_difficult);
    //正解率から結果を返す
    abstract protected function getResult($correct_answer_ratio, $testdata_num, $rand_x);
    
    /**
     * 引数の$user_id, question_idを基に、結果をstatusテーブルに保存する
     * @param $model_datas = [[ユーザの真の実力, 問題の真の難易度, 問題に登録されているテストデータの数], ....]
     */
    public function run($true_ability_score, $true_difficult, $testdata_num, $rand_x) {
        $correct_answer_ratio = $this->getCorrectAnswerRatio($true_ability_score, $true_difficult);
        list($result, $correct_testdata_num) = $this->getResult($correct_answer_ratio, $testdata_num, $rand_x);
        return array($result, $correct_testdata_num);
    }
      
}

?>