<?php
    require __DIR__ . '/src/MinecraftPing.php';
    require __DIR__ . '/src/MinecraftPingException.php';
    require __DIR__ . '/config.php';
  
    use xPaw\MinecraftPing;
    use xPaw\MinecraftPingException;

    /**
     * Connect to the MySQL database
     */

    try {
        $conn = new PDO("mysql:host=$dbHostname;dbname=sftTesting", $dbUsername, $dbPassword);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Database connected successfully<br />";
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
    }

    /**
     * Retreive the list of servers from the database
     */
    $stmt = $conn->prepare("SELECT * FROM `" . $dbTableName_serverList . "`;");
    $stmt->execute();
    $SERVERS = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $SERVER_COUNT = count($SERVERS);
    echo 'Number of servers: ' . $SERVER_COUNT;
    echo '<hr />';
    
    
    for ($i = 0; $i < $SERVER_COUNT; $i++) {
        $serverID = $SERVERS[$i]['id'];
        $serverName = $SERVERS[$i]['name'];
        $serverAddr = $SERVERS[$i]['address'];
        $serverPort = $SERVERS[$i]['port'];
        $timeout = 1;
        echo $serverID . '. ' . $serverName . ' ' . $serverAddr. ':' . $serverPort . '<br />';

        try {
            $Query = new MinecraftPing($serverAddr, $serverPort, $timeout);
            if ($Query) {
                $data = $Query->Query();
                if ($data) {
                    $playerCount = $data[players][online];
                    $playerList = '';
    
                    for ($x=0, $len=count($data['players']['sample']); $x < $len; $x++) {
                        $playerList .= $data['players']['sample'][$x]['name'] . ' ';
                    }
                        
                    $stmt = $conn->prepare("INSERT INTO " . $dbTableName_counter . " (serverID,playercount,playerList) VALUES (:serverId,:count,:plist)");
                    $stmt->bindValue(':serverId', $serverID);
                    $stmt->bindValue(':count', $playerCount);
                    $stmt->bindValue(':plist', $playerList);
                    $stmt->execute();
    
                    echo 'Server: ' . $serverName . '<br />';
                    echo 'Player Count: ' . $playerCount . '<br />';
                    echo 'Player List: ' . $playerList;
                }
            }
        } catch (MinecraftPingException $e) {
            // echo $e->getMessage();
            echo 'Query Unsuccessful';
        } finally {
            if ($Query) {
                $Query->Close();
            }
            echo '<hr>';
        }
    }
