<?php
/**
 * Created by PhpStorm.
 * User: joaquim
 * Date: 22/05/15
 * Time: 17:28
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPConnection('quinohost.com', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('EngineCommunications', false, true, false, false);


$_SESSION['aFailures'] = array();

echo ' ... Waiting for messages. To exit press CTRL+C ...', "\n";

/* @var AMQPMessage $msg */
$callback = function($msg){
    $aFailures = $_SESSION['aFailures'];
    echo " Receiving new message ...";
    //$usersIds = array('A1', 'A2', 'X3', 'A4', 'A5', 'X6', 'X7', 'A8', 'X9', 'A10');
    $body = unserialize($msg->body);
    $userId = key($body);
    echo $userId, "\n";
    if(substr($userId,0,1)=='C'){
        echo " [x] Ommiting userId = $userId", "\n";
        if(array_key_exists($userId, $aFailures)){
            $aFailures[$userId] = $aFailures[$userId]++;
        } else{
            $aFailures[$userId] = 1;
        }

        if($aFailures[$userId]>100){
            sleep(2);
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
            echo " [ok] Forced userId = $userId as ACK", "\n";
            return true;
        }else{
            $msg->delivery_info['channel']->basic_publish($msg, '', 'EngineCommunications');
            $msg->delivery_info['channel']->basic_nack($msg->delivery_info['delivery_tag'],false, false);
            return true;
        }
    }

    //sleep(2);
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    echo " [ok] Done on userId = $userId ", "\n";

    return true;
};


$channel->basic_qos(null, 1, null);
$channel->basic_consume('EngineCommunications', '', false, false, false, false, $callback);
while(count($channel->callbacks)) {
    $channel->wait();
}


$channel->close();
$connection->close();