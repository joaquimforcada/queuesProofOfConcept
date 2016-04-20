<?php

$queuePlannings = 'PlanningsQueue';
$base = 1;
$limit = 500;
$delayReadPlanning = 30; // seconds
$delayCubePlanning = 120; // seconds
$avgTimeConsumer = 180; // seconds
$planningsTypes = array('A','B','X');



$allPlannings = [];

for ($num=$base; $num<$limit; $num++) {
    $char = $planningsTypes[rand(0, 2)];
    $allPlannings[] = strval($num).$char;
}

