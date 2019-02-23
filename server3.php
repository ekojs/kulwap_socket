<?php
/**
 * Handling Multiple Connections
 */
// ini_set("default_socket_timeout",10);
date_default_timezone_set('Asia/Jakarta');
// Set the ip and port we will listen on
$address = 'localhost';
$port = 1234;
$bytes_read = 1024;
$max_clients = 3;

if(false === ($server = @stream_socket_server('tcp://'.$address.':'.$port, $errno, $errstr, STREAM_SERVER_BIND|STREAM_SERVER_LISTEN))){
    printf("Error %s : %s".PHP_EOL,$errno,$errstr);exit;
}

// Display server start time
echo "PHP Socket Server started at " . $address . " " . $port . ", at ". date( 'Y-m-d H:i:s' ).PHP_EOL;

//array of sockets to read
$reads = array();
$client_socks = array();

$intro = array();
$names = array();
for($i=0;$i<$max_clients;$i++){
    $intro[$i] = false;
}

$exit = false;
while(!$exit){
    try{
        $reads = array();
		$reads[0] = $server;
		
		for($i=0;$i<$max_clients;$i++){
			if(!empty($client_socks[$i])){
				$reads[$i+1] = $client_socks[$i];
			}
		}
        
        if(false === (stream_select($reads,$write,$except,0))){
			$errorcode = socket_last_error();
			$errormsg = socket_strerror($errorcode);
			exit("Could not listen on socket : [$errorcode] $errormsg \n");
		}

        if(in_array($server,$reads)){
            for($i=0;$i<$max_clients;$i++){
                if(empty($client_socks[$i])){
                    $client_socks[$i] = @stream_socket_accept($server);
                    if($client_socks[$i]){
                        $ip = stream_socket_get_name( $client_socks[$i], true );

                        echo date( 'Y-m-d H:i:s' ) . " - New connection from " . $ip .PHP_EOL;
                        echo "Now there are total ". count(array_filter($client_socks)) . " clients.".PHP_EOL;
                        fwrite($client_socks[$i],"Welcome to PHP Socket Server...".PHP_EOL);
                        fwrite($client_socks[$i],"Enter your name : ");
                    }
                }
            }
        }

        for($i=0;$i<$max_clients;$i++){
            if(!empty($client_socks[$i]) && in_array($client_socks[$i],$reads)){
                $ip = stream_socket_get_name( $client_socks[$i], true );
				
				// Read the input from the client – 1024 bytes
                $baca = fread($client_socks[$i], $bytes_read);
                while(!feof($client_socks[$i]) && !preg_match("[\r\n]",$baca,$matches)){
                    $baca .= fread($client_socks[$i], $bytes_read);
                }
				if(empty($baca)){
					fclose($client_socks[$i]);
                    unset($client_socks[$i]);
                    unset($names[$i]);
                    
					echo date( 'Y-m-d H:i:s' ) . " | " . $ip . ": Connection Closed..." . PHP_EOL;
                    echo "A client disconnected. Now there are total ". count(array_filter($client_socks)) . " clients.".PHP_EOL;
                    $intro[$i] = false;
					continue;
				}else if(!$intro[$i]){
                    $names[$i] = trim(preg_replace("[ \t\n\r]","",$baca));
                    fwrite($client_socks[$i],"Hello ".$names[$i].", nice to meet you...".PHP_EOL);
                    $intro[$i] = true;
                }else{
                    fwrite($client_socks[$i],$baca);
                }
                echo "Send reply to ".$names[$i].PHP_EOL;

            }
        }
    }catch(Exception $e){
        echo "Message: " .$e->getMessage().PHP_EOL;
    }
}

if($server) fclose($server);