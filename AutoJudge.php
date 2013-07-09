<?php
require_once("./construct/const.php");

/*
 * テストデータを用いてプログラムの正しさを判定する
 * 自動正誤判定
 */
class AutoJudge {
  private $dir_pass = "TemporaryDirectory";
  private $temporary_dir_name = "";
  private $result ="";
  private $error  ="";
  private $question_id = 1;
	
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
      exec("mkdir ./$this->dir_pass/$this->temporary_dir_name");
      exec("cp ./Main.java ./$this->dir_pass/$this->temporary_dir_name");
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
   * @return error  実行が失敗した場合にこの中にエラー内容が入る
   */
  //ここから修正6-19
  public final function Run($input_datas) {
    for($i = 0; $i < count($input_datas); $i++) {
      exec("ulimit -f 1 -t 2;" . JAVA_PASS . 
        "/java -cp ./$this->dir_pass/$this->temporary_dir_name/ Main $input_datas[$i]"
        , $this->result, $this->error);
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