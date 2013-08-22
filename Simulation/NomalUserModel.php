<?php
require_once("./construct/const.php");

abstract class NomalUserModel extends UserModel{
    
    
    //正解率を返す関数
    protected function getCorrectAnswerRatio($true_ability, $true_difficult){
        $correct_answer_ratio = 50 + 10 * ($true_ability - $true_difficult);
        return $correct_answer_ratio;
    }
    
    //正解率から結果を返す
    protected function getResult($correct_answer_ratio, $testdata_num){
        $rand_x = mt_rand(0, 100);
        if($correct_answer_ratio >= $rand_x) {
            return array("Accepted", $testdata_num);
        }
        $gap = $rand_x - $correct_answer_ratio;
        if($gap <= 30) {
            $correct_testdata_num = $testdata_num * (30 - gap) / 30;
            return array("Wrong Answer", $correct_answer_ratio);
        }
        if($gap <= 60) {
            return array("Wrong Answer", 0);
        }
        if($gap <= 80) {
            return array("Runtime Error", 0);
        }
        return array("Compile Error", 0);
    }
    
    
}

?>