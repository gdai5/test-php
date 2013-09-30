 <?php
 
 echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'>";
/**
 * 最終更新：7/8
 * 正誤判定と自動評価を組み合わせた動作確認プログラム
 */
require_once("./construct/const.php");
require_once(REQUIER_PASS . "/UserAutoAssessments/UserAssessment.php");
require_once(REQUIER_PASS . "/QuestionAutoAssessments/QuestionAssessment.php");

//初期化
$user_assessment     = new UserAssessment();
$question_assessment = new QuestionAssessment();
$user_id     = 1;
$question_id = 4;

printf("入った");

for($i = 1; $i < 2; $i++) {
    printf($i . "回目<br>");
    
    $user_assessment->Assessment($user_id);
    $question_assessment->Assessment($question_id);        
    printf("-------------------------------------------<br>");
}
?>