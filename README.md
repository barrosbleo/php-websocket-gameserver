Simple game server using websockets and UDP protocol


Client source:
    https://github.com/barrosbleo/php-websocket-client.git


1 - copy client's folder contents into your root apache or nginx server
    eg.: C\AppServ\www
    eg.: C\www\Http\html

2 - create a mysql database and import database.sql

3 - access server folder and run command -> php server.php
    eg.: C\AppServ\gameserver> php server.php

4 - open your browser and access your localhost and pass the user id through GET method -> ?id=1
    eg.: http://localhost:8090?id=1
    eg.: http://localhost?id=2
    eg.: http://localhost:8000?id=2



Issues:
Server writing to database consums every HD resources while moving player;
