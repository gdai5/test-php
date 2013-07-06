<?php
/*
 * 4/25
 * DBからquestion_idを使って、条件に合うデータだけを抽出できた
 * テーブルの行数もカウント
 * 問題点：現状だと「inputの数＝outputの数」となっているが、問題によっては、inputがない問題も考えられるので対応させたい
 * 5/17
 * input_dataをかませて、output_dataと比較まで完了
 * 次の課題
 * 間違った箇所を掲載するruct/const.php");
 * 6/19
 * 変数名の統一および不要な変数の消去
 */

/*
 * テストデータを用いてプログラムの正しさを判定する
 * 自動正誤判定
 */
class AutoJudge {
  private $java_file = "Main.java";
  private $time, $total_time;
  private $file_pass, $file_exist;
  //private $result, $error;
  private $directory_pass;
  private $input_datas  = array();
  private $output_datas = array();
  private $question_id = 1;
	//private $class_file = "Main.class";
	
	/**
   * 実行などを行うための一時フォルダおよび
   * ユーザが書いたプログラムをMain.javaとして作成する
   * @return 
   */
	public final function CreateFolder() {
	  // exec("mkdir ./sample");
	  if(!$this->chkFileExist()){
      return false;
	  }
    // exec("mv ./Main.java ./sample/");
    // exec("chmod 666 ./sample/*");
    //exec("mv ./sample/Main.java Main.java");
    return true;
  }
  
  /**
   * ユーザの書いたコードでMain.javaが作成されているか確認
   * @return true, 失敗：false
   */
  private final function chkFileExist() {
    $this->file_pass = ".";
    if(is_file("$this->file_pass/Main.java")){
      return true;
    }else{
      return false;
    }
  }
  
  //directory_pass 毎回一時的なフォルダを作成し、その中でコンパイルおよび実行を行うため
  //その一時フォルダの名前が入る
  //実際は　java -cp ./pass/ Mainでおk
  /**
   * 作成したMain.javaをコンパイルする
   * @param directory_pass 一時ディレクトリのパス
   * @return 成功：true, 失敗：false
   */
  public final function Compile($directory_pass) {
    $result = array();
    $err_code = 0;
    exec(JAVA_PASS . "/javac  Main.java", $result, $err_code);
    if($err_code != 0) {
      return false;
    }
    return true;
  }
  
  /**
   * テストデータを引数に取り実行する
   * @param directory_pass 一時ディレクトリのパス
   * @param input_datas    テストデータが入っている配列
   * @return result 実行結果が入った配列
   * @return error  実行が失敗した場合にこの中にエラー内容が入る
   */
  //ここから修正6-19
  public final function Run($directory_pass, $input_datas) {
    for($i = 0; $i < count($input_datas); $i++) {
      exec("ulimit -f 1 -t 2;" . JAVA_PASS . "/java Main $input_datas[$i]", $this->result, $this->error);
      if($this->error != 0) {
        $this->error = RUN_TIME_ERROR;
        return array($this->result, $this->error);
      }
    }
    return array($this->result, $this->error);
  }
  
  
  /**
   * DBからquetsion_idに対応したテストデータが保存されているファイル名を取得しそれを返す
   * @param question_id 問題番号
   * @return input_datas テストデータ（入力）の値が入った配列
   * @return output_datas テストデータ（出力）の値が入った配列
   */
  public final function getTestData($question_id) {
    $mysqli = $this->DatabaseConnection();
    $query = "SELECT input_file, output_file FROM testdatas WHERE question_id = $question_id;";
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
      = $this->readTestDataFiles($input_files, $output_files);
    
    return array($input_datas, $output_datas);
  }
  
  /**
   * DBとの接続を行う
   * @return mysqli DBとの接続をやり取りする変数
   */
  private final function DatabaseConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if($mysqli->connect_errno) {
      print("エラーが発生しました。");
      exit;
    }
    print("接続おk<br>");
    return $mysqli;
  }

  //中身を上手く持ってきたが、色々と危ない気がする
  /**
   * テストデータが入っているファイルから中の値を読み込む
   * @param input_files テストデータ（入力）のファイル名が入った配列
   * @param output_files テストデータ（出力）のファイル名が入った配列
   * @return input_datas テストデータ（入力）の値が入った配列
   * @return output_datas テストデータ（出力）の値が入った配列
   */
  private final function readTestDataFiles(array $input_files, array $output_files) {
    //テストデータの値（入力）を配列に格納していく
    $input_datas = array();
    for($i=0; $i < count($input_files); $i++) {
      if(is_file("./Datas/test_datas/input_datas/$this->question_id/$input_files[$i]")){ 
        $text = fopen("./Datas/test_datas/input_datas/$this->question_id/$input_files[$i]","r"); 
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
      if(is_file("./Datas/test_datas/output_datas/$this->question_id/$output_files[$i]")){
        $text = fopen("./Datas/test_datas/output_datas/$this->question_id/$output_files[$i]",'r'); 
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
  public final function Judgement($result, $output_datas) {
    $count = 0;
    if(count($result) != count($output_datas)) { //いる？
      return $count;
    }
    for($i=0; $i < count($output_datas); $i++) {
      echo ("プログラムの出力：" . $result[$i]       . "<br>");
      echo ("用意した予想出力：" . $output_datas[$i] . "<br>");
      if(preg_match("/^".$result[$i]."(\s|\s\s)$/", $output_datas[$i])) { //空白二つまで容認
        echo "ヒット<br>";
        $count ++;
      }
      echo "<br>";
    }
    return $count;
  }
	
  //ユニークな名前を作成する
	public final function getUniquName() {
		return $this->java_file;
	}
}
?>