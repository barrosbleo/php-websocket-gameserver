<?php

define("ADDRESS", "127.0.0.1");
define("PORT", 55555);

define("HOST", "localhost");
define("USER", "root");
define("PASS", "12345678");
define("DB", "udpgs");


function getUsersOnline($conn){
    $rows = [];
    $users = [];
    $stmt = $conn->prepare('SELECT * FROM users WHERE online = 1');
    $stmt->execute();

    while($row = $stmt->fetch()){
        for($i = 0; $i < count($row); $i++){
            unset($row[$i]);
        }
        array_push($rows, $row);
    }
    //print_r($rows);

    foreach($rows as $row){
        array_push($users, implode('-', $row));
        //print_r($users);
    }

    //print_r($users);

    return implode('/', $users);
}

function updateUser($conn, $params){
    $user = explode(',', $params);
    $stmt = $conn->prepare('UPDATE users SET pos_X = :pos_X, pos_Y = :pos_Y WHERE id = :id');
    $stmt->bindParam(':pos_X', $user[0]);
    $stmt->bindParam(':pos_Y', $user[1]);
    $stmt->bindParam(':id', $user[2]);
    return $stmt->execute();
}

try{
    if(($conn = new PDO('mysql:host='.HOST.'; dbname='.DB, USER, PASS)) === false){
        throw new Exception("Unable to connect to mysql database");
    }
}catch(Exception $err){
    echo "Error: " . $err->getMessage();
}


try{
    if(($server = socket_create(AF_INET, SOCK_DGRAM, 0)) === false){
        throw new Exception("Unable to create connection socket.");
    }
    if(socket_bind($server, ADDRESS, PORT) === false){
        throw new Exception("Failed to bind socket at " . ADDRESS . ":" . PORT . ".");
    }
}catch(Exception $err){
    echo "Error: " . $err->getMessage() . "\n";
}

while(true){
    sleep(1/10);
    try{
        if(socket_recvfrom($server, $buffer, 512, 0, $CLIENT_IP, $CLIENT_PORT) === false){
            throw new Exception("Failed retrieving data from client.");
        }
    }catch(Exception $err){
        echo "Error: " . $err->getMessage() . "\n";
    }

    // parse $buffer string to array [function, params]
    $buffer = explode('-', $buffer);

    // process user order
    if(!empty($buffer)){
        if(count($buffer) < 2){
            $buffer = call_user_func($buffer[0], $conn);
        }else{
            $buffer = call_user_func_array($buffer[0], [$conn, $buffer[1]]);
        }
    }

    try{
        if(socket_sendto($server, $buffer, 100, 0, $CLIENT_IP, $CLIENT_PORT) === false){
            throw new Exception("Failed to send back user request.");
        }
    }catch(Exception $err){
        echo "Error: " . $err->getMessage() . "\n";
    }
}

socket_close($server);
?>