<?php
/**
 * 作成日：8/22
 */
require_once("./UserModel.php");

/**
 * 普通のユーザのモデル
 * 　ー特殊な性質（例：正解率に関わらず一定の間隔でCompile Errorを起こすなど）を一切持たないユーザ
 */
class NomalUserModel extends UserModel{
    
    /**
     * 真の実力と真の難易度の二つをもって、正解率を返す
     * @param $true_ability   真のユーザの実力
     * @param $true_difficult 真の問題の難易度
     * @return $correct_answer_ratio 正解率
     */
    protected function getCorrectAnswerRatio($true_ability, $true_difficult){
        //model1
        //正解率の出し方はこれが良い気がする(一番近い値にいくので)
        //モデルとして正当性を主張できるようにする
        $correct_answer_ratio = 50 + 20 * ($true_ability - $true_difficult);
        if ($correct_answer_ratio < 0) { //難易度が実力よりも５以上大きかったとき
            $correct_answer_ratio = 0;
        }
        if ($correct_answer_ratio > 100) { //実力が難易度よりも５以上大きかったとき
            $correct_answer_ratio = 100;
        }
        return $correct_answer_ratio;
    }
    
    /**
     * 正解率とランダム生成された値（rand_x）を基に結果を返す
     * @param $correct_answer_ratio 正解率
     * @param $testdata_num         その問題のテストデータ数
     * @return [結果, 正解したテストデータ数]
     */
    protected function getResult($correct_answer_ratio, $testdata_num, $rand_x){
        //検証のため、一旦コメントアウト
        //$rand_x = mt_rand(0, 100);
        if($correct_answer_ratio >= $rand_x) {
            //全てのテストデータに正解
            return array(ACCEPTED, $testdata_num);
        }
        if($correct_answer_ratio == 0) {//正解率が０の場合は、問答無用でcompile errorとなる
            //正解率０なら問答無用でコンパイルエラー
            return array(COMPILE_ERROR, 0);
        }
        /**
         * 2013-11-28
         * これ以降は「失敗」した場合の処理なので、割合を変える必要がある
         */
        //この段階で（$rand_x >= $correct_answer_ratio）は自明である
        $gap = $rand_x - $correct_answer_ratio;
        if($gap <= 10) {
            //  テストデータが一つ以上合っている
            $correct_testdata_num = $testdata_num * (10 - $gap) / 10;
            //丸める（例：9.99 => 9）
            $correct_testdata_num = floor($correct_testdata_num);
            //テストデータ数が一つしかないときに、正解したテストデータ数が０になる可能性があるのでその対策
            if ($correct_testdata_num == 0) {
                return array(NOT_CORRECT, $correct_testdata_num);
            }
            return array(CLOSE_ANSWER, $correct_testdata_num);
        }
        if($gap <= 20) {
            //テストデータが一つも合わない
            return array(NOT_CORRECT, 0);
        }
        if($gap <= 40) {
            //実行時エラー
            return array(RUNTIME_ERROR, 0);
        }
        return array(COMPILE_ERROR, 0);
    }
}

?>