<?php
  //AutoJudge.phpで使用
  define("COMPILE_ERROR"      , 1);
  define("RUN_TIME_ERROR"     , 2);
  define("FILE_DOSE_NOT_EXIST", 3);
  //AutoAssessment.phpで使用
  define("Accepted"     , 11);
  define("WrongAnswer"  , 12);
  define("RunTimeError" , 13);
  define("CompileError" , 14);
  define("TimeOut"      , 15);
  
  define("JAVA_PASS", "/Library/Java/JavaVirtualMachines/jdk1.7.0_25.jdk/Contents/Home/bin");
  
  //データベース接続変数の定義
  define('DB_HOST', 'localhost');
  define('DB_USER', 'Ishikawa');
  define('DB_PASSWORD', 'Hitoshi4');
  define('DB_NAME', 'ipso');
  
?>