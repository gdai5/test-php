<?php
require_once("./construct/const.php");

abstract class UserModel {
    
    private $mysqli;
    
    //正解率を返す関数
    abstract protected function getCorrectAnswerRatio($true_ability, $true_difficult);
    //正解率から結果を返す
    abstract protected function getResult($correct_answer_ratio);
    
    
}

?>