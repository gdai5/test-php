<?php

class SimulationOrignalQuestionAssessment {
    
    public final function orignalQuestionXiFlag($difficult, $ability_score ,$result) {
        $xi = 0;
        switch ($result) {
            case ACCEPTED:
                if($ability_score < $difficult) $xi = 1;
                break;
            default:
                if($ability_score > $difficult) $xi = 1;
                break;
        }
        return $xi;
    }    
}

?>