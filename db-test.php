<?php
//無事に動いたのでこれを利用する
require_once("./db_connect/db_connect_cos.php");

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if($mysqli->connect_errno) {
      print("エラーが発生しました。");
      exit;
    }

    print("接続おk<br>");
    
    $query = "SELECT input_file, output_file FROM testdatas WHERE question_id = 1";
    
    $result = $mysqli->query($query);
    // if(!$result) {
      // print("エラーが発生しました。");
      // exit;
    // }
//     
    print("query OK<br>");
    
    // if ($result == true) {
      // echo "No rows found, nothing to print so am exiting";
      // exit;
    // }
    
     while ($row = $result->fetch_assoc()) {
        printf ("%s (%s)\n", $row["input_file"], $row["output_file"]);
    }
    
    $result->free();
    $mysqli->close();
?>