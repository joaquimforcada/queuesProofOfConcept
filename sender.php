<?php

$usersIds = array('A1', 'A2', 'X3', 'A4', 'A5', 'X6', 'X7', 'A8', 'X9', 'A10');

$base = 2321458;
$limit = 2521458;
for($num=$base; $num<$limit; $num++){
    $letra = str_shuffle('AX');
    $usersIds[]= substr($letra,0,1).$num;
}
/**
 * Created by PhpStorm.
 * User: joaquim
 * Date: 22/05/15
 * Time: 16:40
 */

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;


$connection = new AMQPConnection('quinohost.com', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('EngineCommunications', false, true, false, false);


$descanso = 0;
foreach($usersIds as $userId){
    $descanso++;
    if($descanso%20==0){
        echo '*************************************';
        sleep(3);
    }
    $body = array($userId=>' Email of user is '.$userId.'@yopmail.com');
    $body = serialize($body);
    $msg = new AMQPMessage($body, array('delivery_mode' => 2));
    if(!$channel->basic_publish($msg, '', 'EngineCommunications')){
        echo " [x] User ".$userId." NOT registered \n ";
    }else{
        echo " [ok] User ".$userId." registered \n ";
    }
}


$channel->close();
$connection->close();