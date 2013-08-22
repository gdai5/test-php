<?php

require_once("");

class SimulationDatas {
    private $mysqli;
    protected $data_set;
    protected $true_ability_list;
    protected $true_difficult_list;
    
    function __construct() {
        $this->mysqli = DatabaseConnection();
    }
    
    public final function getDataSet() {
        return $data_set;
    }
    
    public final function getTrueAbilityScores() {
        return $true_ability_score_list;
    }
    
    public final function getTrueDifficults() {
        return $true_difficult_list;
    }
}

?>