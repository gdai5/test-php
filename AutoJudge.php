<?php
require_once("./construct/const.php");

/*
 * テストデータを用いてプログラムの正しさを判定する
 * 自動正誤判定
 */
class AutoJudge {
  private $dir_pass = "TemporaryDirectory";
  private $temporary_dir_name = "";
	
  /**
   * 完了：7/8
   * 一時フォルダの生成およびMain.javaの存在確認
   * @param user_id
   * @param question_id
   * @return true or false
   */
  public final function mkDirectory($user_id, $question_id) {
      $this->temporary_dir_name = $this->getUniquName($user_id, $question_id);
      $this->chkDirecotryExist();
      exec("mkdir ./". DIRECTORY_PASS ."/$this->temporary_dir_name");
      exec("cp ./Main.java ./". DIRECTORY_PASS ."/$this->temporary_dir_name");
      if(!$this->chkFileExist()){
          return false;
      }
      return true;
  }
  
  /**
   * 完了：7/8
   * 以前生成されたディレクトリが存在するかどうかの確認
   */
  private final function chkDirecotryExist() {
      if(file_exists("./$this->dir_pass/$this->temporary_dir_name") 
        && is_dir("./$this->dir_pass/$this->temporary_dir_name")) {
          printf("既にディレクトリが生成されています");
          //exec("chmod 666 ./$this->dir_pass/$this->temporary_dir_name/");
          exec("rm -rf ./$this->dir_pass/$this->temporary_dir_name");
          printf("削除しました");
      }else{
          printf("ディレクトリの生成を開始");
      }
  }
  
  /**
   * 完了：7/8
   * 正しく一時フォルダの中にMain.javaが生成されているか確認
   * @return true, false
   */
  private final function chkFileExist() {
      if(is_file("./$this->dir_pass/$this->temporary_dir_name/Main.java")){
          printf("無事に確認<br>");
          return true;
      }else{
          printf("確認できませんでした<br>");
          return false;
      }
  }
  
  /**
   * 作成したMain.javaをコンパイルする
   * @return 成功：true, 失敗：false
   */
  public final function Compile() {
      $result = array();
      $err_code = 0;
      //コンパイル
      exec(JAVA_PASS . "/javac ./$this->dir_pass/$this->temporary_dir_name/Main.java", $result, $err_code);
      if($err_code != 0) {
          printf("コンパイルに失敗<br>");
          return false;
      }
      printf("コンパイルに成功<br>");
      return true; 
  }
  
  /**
   * テストデータを引数に取り実行する
   * 実際は　java -cp ./pass/ Mainでおk
   * @param directory_pass 一時ディレクトリのパス
   * @param input_datas    テストデータが入っている配列
   * @return result 実行結果が入った配列
   */
  //ここから修正6-19
  public final function Run($input_datas) {
      $program_outputs = array();
      $error  = "";
      for($i = 0; $i < count($input_datas); $i++) {
        exec("ulimit -f 1 -t 2;" . JAVA_PASS . 
          "/java -cp ./$this->dir_pass/$this->temporary_dir_name/ Main $input_datas[$i]"
          , $program_outputs, $error);
        if($error != "") {
          printf("Run time Error!");
          $program_outputs[0] = "Run time Error";
          return $program_outputs;
        }
      }
      return $program_outputs;
  }
  
  
  /**
   * DBからquetsion_idに対応したテストデータが保存されているファイル名を取得しそれを返す
   * @param question_id 問題番号
   * @return input_datas テストデータ（入力）の値が入った配列
   * @return output_datas テストデータ（出力）の値が入った配列
   */
  public final function getTestData($question_id) {
    $mysqli = DatabaseConnection();
    $query = "SELECT input_file, output_file FROM testdatas WHERE question_id = '$question_id';";
    $testdata_files = $mysqli->query($query);
    
    //テストデータが入っているファイル名が入った配列
    $input_files  = array();
    $output_files = array();
    while ($data_file = $testdata_files->fetch_assoc()) {
      array_push($input_files , $data_file["input_file"]);
      array_push($output_files, $data_file["output_file"]);
    }
    //解放
    $testdata_files->free();
    $mysqli->close();
    
    //ファイルからテストデータの値が入った配列
    $input_datas  = array();
    $output_datas = array();
    list($input_datas, $output_datas) 
      = $this->readTestDataFiles($input_files, $output_files, $question_id);
    
    return array($input_datas, $output_datas);
  }

  //中身を上手く持ってきたが、色々と危ない気がする
  /**
   * テストデータが入っているファイルから中の値を読み込む
   * @param input_files テストデータ（入力）のファイル名が入った配列
   * @param output_files テストデータ（出力）のファイル名が入った配列
   * @return input_datas テストデータ（入力）の値が入った配列
   * @return output_datas テストデータ（出力）の値が入った配列
   */
  private final function readTestDataFiles(array $input_files, array $output_files, $question_id) {
    //テストデータの値（入力）を配列に格納していく
    $input_datas = array();
    for($i=0; $i < count($input_files); $i++) {
      if(is_file("./Datas/test_datas/input_datas/$question_id/$input_files[$i]")){ 
        $text = fopen("./Datas/test_datas/input_datas/$question_id/$input_files[$i]","r"); 
        while(!feof($text)){ 
          $line = fgets($text);
          if(preg_match("/^[a-zA-Z0-9]/", $line)){ //空白の行は無視
            array_push($input_datas, $line);
            //$input_datas[] = $line; //要素の追加
          } 
        }
        fclose($text);
      }else{
        //echo "input file does not exist";
      }
    }
    
    //テストデータの値（出力）を配列に格納していく
    $output_datas = array();
    for($i=0; $i < count($output_files); $i++) {
      if(is_file("./Datas/test_datas/output_datas/$question_id/$output_files[$i]")){
        $text = fopen("./Datas/test_datas/output_datas/$question_id/$output_files[$i]",'r'); 
        while(!feof($text)){ 
          $line = fgets($text);
          if(preg_match("/^[a-zA-Z0-9]/", $line)){ //空白の行は無視
            array_push($output_datas, $line);
            //$output_datas[] =  $line; //要素の追加
          }
        }
        fclose($text);
      }else{
        //echo "output file does not exist";
      }
    }
    return array($input_datas, $output_datas);
  }

  /**
   * プログラムの実行結果と予測結果の比較
   * @param result プログラムの実行結果が入った配列
   * @param output_datas 予測結果が入った配列
   * @return count 何問予測結果と一致したか
   */
  public final function Judgement($program_outputs, $output_datas) {
    $correct_answers = 0;
    $judge_result = "";
    if(count($program_outputs) != count($output_datas)) { //いる？
      printf("出力結果の個数が一致していません");
      return array($correct_answers, $judge_result);
    }
    for($i=0; $i < count($output_datas); $i++) {
      echo ("プログラムの出力：" . $program_outputs[$i]       . "<br>");
      echo ("用意した予想出力：" . $output_datas[$i] . "<br>");
      if(preg_match("/^".$program_outputs[$i]."(\s|\s\s)$/", $output_datas[$i])) { //空白二つまで容認
        echo "ヒット<br>";
        $correct_answers++;
      }
      echo "<br>";
    }
    if($correct_answers == count($output_datas)) {
        $judge_result = "Accepted";
    }else{
        $judge_result = "Wrong Answer";
    }
    return array($correct_answers, $judge_result);
  }
  
  /**
   * 完成：7/7
   * statusテーブルの情報を更新もしくは新規で追加する
   * @param user_id         ユーザのID
   * @param question_id     問題のID
   * @param judge_result    判定結果（Accepted, Wrong Answer, Compile Error, Runtime Error, Time Out, Output Limit Exceeded）
   * @param correct_answers 正解したテストデータの数
   */
  public final function writeStatus($user_id, $question_id, $judge_result, $testdatas_num, $correct_answers) {
      $this->mysqli = DatabaseConnection();

      $char_set_flag = $this->mysqli->query('SET NAMES utf8'); //文字コードの指定(UTF-8)
      if(!$char_set_flag) {
        $this->mysqli->close();
        exit('文字コードを指定できませんでした。');
      }
      
      //テーブルの情報を更新するのか、それとも新しく追加するのか
      $update_flag = $this->chkSameColum($user_id, $question_id);
      if(!$update_flag) { //新規追加
        //エラーの原因は変数をシングルクウォテーションで囲ってなかったため
        $query = "INSERT INTO status (user_id, question_id, result, testdatas_num, correct_answers, create_at) 
                              VALUES ('$user_id', '$question_id', '$judge_result', '$testdatas_num', '$correct_answers', now());";  
      }else{ //更新
        $query = "update status set result = '$judge_result', testdatas_num = '$testdatas_num', 
                                    correct_answers = '$correct_answers', create_at = now() 
                                    where user_id = '$user_id' and question_id = '$question_id';";
      }
      
      $char_set_flag = $this->mysqli->query($query);
      if(!$char_set_flag) {
        exit('失敗しました。'. $this->mysqli->error);
      }
      $this->mysqli->close();
  }

  /**
   * 完成：7/7
   * statusテーブルで既にuser_idとquestion_idの二つが一致しているものがあった場合
   * statusテーブルのresultの項目だけ、更新する。
   * そうでない場合は、新規で追加する。
   * @param user_id     ユーザのID
   * @param question_id 問題のID
   * @return false この場合には、テーブルに新しく要素を追加する（つまり、初めてその問題を解いたということ）
   *     or  true  この場合には、テーブルのresult項目だけを更新する
   *               更新は　Accepted > Wrong Anwser, Time Out, Output Limit Exceeded > Runtime Error > Compile Error
   *               となり、今の結果から左に行く場合は更新が発生する。右に行く場合は更新を行わない。
   */
  private final function chkSameColum($user_id, $question_id) { 
      $query = "SELECT id FROM status WHERE question_id = '$question_id' and user_id = '$user_id';";
      $status_colum = $this->mysqli->query($query);
      if($status_colum->fetch_assoc() == null) {
          printf("追加します");
          return false;
      }
      printf("更新します");
      return true;
  }
  
	
  /**
   * 完了：7/8
   * ユーザIDと問題のIDを組み合わせてユニークな名前の生成
   * @param user_id
   * @param question_id
   * @return uniqu_name 「ユーザID-問題ID」の文字列を返す
   */
  public final function getUniquName($user_id, $question_id) {
    $uniqu_name = "$user_id" . "-" . "$question_id";
    printf($uniqu_name);
	return $uniqu_name;
  }
}
?>