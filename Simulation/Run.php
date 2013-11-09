<?php

require_once("./SimulationRun.php");

/**
 * 2013/10/2
 * シミュレーション実験向けに全ての処理をメインメモリ上で行うように変更する
 * 旧バージョン=>「OldSimulationRun.php」
 */
set_time_limit(120);
$simulation_run = new SimulationRun();
$simulation_run->Run();
//NomalUserModelの検証完了 2013-10-07
//$simulation_run->nomalUserModelTest();

?>