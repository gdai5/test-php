<?php
  //Java Pass
  define("JAVA_PASS", "/Library/Java/JavaVirtualMachines/jdk1.7.0_25.jdk/Contents/Home/bin");
  
  //Direcotry Pass
  define("DIRECTORY_PASS", "TemporaryDirectory");
  
  //データベース接続変数の定義
  define('DB_HOST', 'localhost');
  define('DB_USER', 'Ishikawa');
  define('DB_PASSWORD', 'Hitoshi4');
  define('DB_NAME', 'ipso');
  
  function DatabaseConnection() {
    $mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if($mysqli->connect_errno) {
      print("エラーが発生しました。");
      exit;
    }
    print("接続おk<br>");
    $char_set_flag = $mysqli->query('SET NAMES utf8'); //文字コードの指定(UTF-8)
      if(!$char_set_flag) {
      $this->mysqli->close();
      exit('文字コードを指定できませんでした。');
    }
    return $mysqli;
  }
  
?>