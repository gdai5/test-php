<?php

class test {
  public function test1() {
    $dbc = "Hello";
    echo $dbc;
  }
  public function test2() {
    echo $dbc;
  }
}

$test = new test();
$test->test1();
//$test->test2();
?>