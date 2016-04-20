<?php

require_once __DIR__ . '/vendor/autoload.php';
include_once ('planningsStock.php');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('quinohost.com', 5672, 'producerPlanningB', '123456789');
$channel = $connection->channel();
$channel->queue_declare($queuePlannings, false, true, false, false);

foreach ($allPlannings as $planning) {
    echo '** getting planning '.$planning. " \n";

    if (substr($planning, -1) == 'B') {
        sleep(rand(5, $delayReadPlanning));
        $dtNow = new DateTime('now');
        //$body = array($planning => ' Planning is '.$planning .' was sent at '.$dtNow->format('Y-m-d h:i:s'));
        $body = ''.$planning.'#Planning is '.$planning .' was sent at '.$dtNow->format('Y-m-d h:i:s');
        // $body = serialize($body);

        $msg = new AMQPMessage($body, array('delivery_mode' => 2));
        if (!$channel->basic_publish($msg, '', $queuePlannings)) {
            echo " [  ] Planning $planning NOT delivered to $queuePlannings \n ";
        } else {
            echo " [ok] Planning $planning DELIVERED SUCCESS  \n ";
        }
    }

}


$channel->close();

$connection->close();
