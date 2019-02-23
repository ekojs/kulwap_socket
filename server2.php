<?php

date_default_timezone_set('Asia/Jakarta');
// Set the ip and port we will listen on
$address = 'localhost';
$port = 1234;

if(false === ($server = @stream_socket_server('tcp://'.$address.':'.$port, $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN))){
    printf("Error %s : %s".PHP_EOL,$errno,$errstr);exit;
}

// Display server start time
echo "PHP Socket Server started at " . $address . " " . $port . ", at ". date( 'Y-m-d H:i:s' ).PHP_EOL;

try{
    if($client = @stream_socket_accept($server)){
        $ip = stream_socket_get_name($client,true);
        echo "New connection from " . $ip .PHP_EOL;
        fwrite($client,"Welcome to PHP Socket Server...".PHP_EOL);
        fwrite($client,"Tell me your name : ");

        // Read client input 1024 bytes data
        $close = false;
        $baca = "";
        while(!feof($client) && !$close){
            $baca .= fread($client,1024);
            if(preg_match("[\r\n]",$baca)){
                $close = true;
            }
        }
        $name = trim(preg_replace("[ \t\n\r]","",$baca));
        fwrite($client,"\nHello ".$name.", nice to meet you...".PHP_EOL);
        printf("%s is %s".PHP_EOL,$ip,$name);

        fclose($client);
        printf("%s | %s : Connection Closed...".PHP_EOL,date("d-m-Y H:i:s"),$name);
    }
}catch(Exception $e){
    echo "Message: " .$e->getMessage().PHP_EOL;
}

if($server) fclose($server);