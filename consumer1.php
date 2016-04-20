<?php

require_once __DIR__ . '/vendor/autoload.php';
include_once ('planningsStock.php');

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('quinohost.com', 5672, 'consumerPlannings', '123456789');
$channel = $connection->channel();
$channel->queue_declare($queuePlannings, false, true, false, false);


//session_start();
$aFailures = array();

echo ' ... Waiting for messages. To exit press CTRL+C ...', "\n";

/**
 * @param AMQPMessage $msg
 * @return bool
 */
$callback = function($msg) {
    $delayCubePlanning = $GLOBALS['delayCubePlanning'];

    echo " Receiving new message ...";

    $aBody = explode('#', $msg->body);
    $planning = $aBody[0];

    sleep(rand(10, $delayCubePlanning/2));

    echo $planning, " succesfully received \n";
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    echo " [ok] Planning $planning ACK", "\n";
    //$msg->delivery_info['channel']->basic_publish($msg, '', 'EngineCommunications');
    //$msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'],false, false);
    return true;
};


$channel->basic_qos(null, 1, null);
$channel->basic_consume($queuePlannings, '', false, false, false, false, $callback);

while (count($channel->callbacks)) {
    echo '...wait within channel until further messages...';
    $channel->wait();
}


$channel->close();
$connection->close();