<?php
//4/25
//DBからquestion_idを使って、条件に合うデータだけを抽出できた
//テーブルの行数もカウント
//問題点：現状だと「inputの数＝outputの数」となっているが、問題によっては、inputがない問題も考えられるので対応させたい
require_once("./construct/const.php");

class AutoJudge {
	private $java_file = "Main.java";
  private $time, $total_time;
  private $file_pass, $file_exist;
  private $result, $error;
  private $directory_pass;
  private $input_datas  = array();
  private $output_datas = array();
  private $question_id = 1;
	//private $class_file = "Main.class";
	
	//上手くjavaの実行結果が出力されない
	//コンソールでは、上手く表示できていた
	//ブラウザ上では、error_logよりバージョンが異なるためだと分かった
	//unset＝結果の文章が入るので、毎回初期化が必要
	public final function createFolderAndMainfile() {
	  unset($this->result);
    unset($this->error);
	  // exec("mkdir ./sample");
	  if(!$this->fileExist()){
      $this->error = 0;
      return $this->error;
	  }
    // exec("mv ./Main.java ./sample/");
    // exec("chmod 666 ./sample/*");
    //exec("mv ./sample/Main.java Main.java");
  }
  
  public final function CompileAndRun($directory_pass) {
    exec(JAVA_PASS . "/javac  Main.java", $this->result, $this->error);
    if($this->error != 0) {
      $this->error = COMPILE_ERROR;
      return;
    }
    exec("ulimit -f 1 -t 2;" . JAVA_PASS . "/java Main", $this->result, $this->error);
    if($this->error != 0) {
      $this->error = RUN_TIME_ERROR;
      return;
    }
  }
  
  //Main.javaが無事作成されているか確認
  private final function fileExist() {
    $this->file_pass = ".";
    if(is_file("$this->file_pass/Main.java")){
      return true;
    }else{
      return false;
    }
  }
  
  //DBからquetsion_idに対応したテストデータが保存されているファイル名を取得しそれを返す
  public final function getTestDatafiles($question_id) {
    $input_files  = array();
    $output_files = array();
    $input_row;
    $output_row;
    $mysqli = $this->DatabaseConnection(); //この内部だけ有効
    $query1 = "SELECT input_file, output_file FROM testdatas WHERE question_id = $question_id;";
    $query2 = "SELECT COUNT(input_file), COUNT(output_file) FROM testdatas where question_id = $question_id;"; //行数を獲得
    $testdata_files = $mysqli->query($query1);
    $testdatas_row = $mysqli->query($query2);
    while ($data_file = $testdata_files->fetch_assoc()) {
      array_push($input_files , $data_file["input_file"]);
      array_push($output_files, $data_file["output_file"]);
    }
    while ($row = $testdatas_row->fetch_assoc()) {
      $input_row = $row["COUNT(input_file)"];
      $output_row = $row["COUNT(output_file)"];
    }
    //解放
    $testdata_files->free();
    $testdatas_row->free();
    $mysqli->close();
    return array($input_files, $output_files, $input_row, $output_row);
  }

  //中身を上手く持ってきたが、色々と危ない気がする
  public final function readTestdatas(array $input_files, array $output_files) {
    for($i=0; $i < count($input_files); $i++) {
      if(is_file("./Datas/test_datas/input_datas/$this->question_id/$input_files[$i]")){ 
        $text = fopen("./Datas/test_datas/input_datas/$this->question_id/$input_files[$i]","r"); 
        while(!feof($text)){ 
          $line = fgets($text);
          if($line){
            $input_datas[] = $line; //要素の追加
          } 
        }
        fclose($text);
      }else{
        //echo "input file does not exist";
      }
    }
    for($i=0; $i < count($output_files); $i++) {
      if(is_file("./Datas/test_datas/output_datas/$this->question_id/$output_files[$i]")){
        $text = fopen("./Datas/test_datas/output_datas/$this->question_id/$output_files[$i]",'r'); 
        while(!feof($text)){ 
          $line = fgets($text);
          if($line){
            $output_datas[] =  $line; //要素の追加
          }
        }
        fclose($text);
      }else{
        //echo "output file does not exist";
      }
    }
    return array($input_datas, $output_datas);
  }
	
  //ユニークな名前を作成する
	public final function getUniquName() {
		return $this->java_file;
	}
  
  //とりあえず単体で動かすために実装
  //後で消す可能性が大きい
  private final function DatabaseConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if($mysqli->connect_errno) {
      print("エラーが発生しました。");
      exit;
    }
    print("接続おk<br>");
    return $mysqli;
  }
}
?>