<?php
	require __DIR__ . '/src/MinecraftPing.php';
	require __DIR__ . '/src/MinecraftPingException.php';
  
	use xPaw\MinecraftPing;
    use xPaw\MinecraftPingException;



    

    $servername = 'x2.xentradi.com';
    $username = 'sftTesting';
    $password = 'Hawa11an!';

    try {
        $conn = new PDO("mysql:host=$servername;dbname=sftTesting", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Connected successfully<br />"; 
        }
    catch(PDOException $e)
        {
        echo "Connection failed: " . $e->getMessage();
        }

    /**
     * Retreive the list of servers from the database
    **/
    $stmt = $conn->prepare("SELECT * FROM `servers`;");
    $stmt->execute();
    $SERVERS = $stmt->fetchAll(PDO::FETCH_ASSOC);
    print_r($SERVERS);
    echo '<br />';
    echo count($SERVERS);
    echo '<hr />';
    
    
    for($i = 0, $len = count($SERVERS); $i < $len; $i++) {
        echo '$i = ' . $i . '<br />';
        $serverAddr = $SERVERS[$i]['address'];
        $serverPort = $SERVERS[$i]['port'];
        $timeout = 1;
        echo '$SERVERS['. $i . '] = ';
        print_r($SERVERS[$i]);
        echo '<br />';

        try {
        		$Query = new MinecraftPing( $serverAddr, $serverPort, $timeout );
                if($Query) {
                    
                    $data = $Query->Query( );
                    if($data) {
                        $playerCount = $data[players][online];
                        $playerList = '';
    
                        for($i=0, $len=count($data[players][sample]); $i < $len; $i++) {
                            $playerList .= $data[players][sample][$i][name] . ' ';
                        }
                        
                        $stmt = $conn->prepare("INSERT INTO counter (serverID, playercount, playerList) VALUES (:serverID,:count,:plist)");
                        $stmt->bindValue(':serverID', $SERVERS[$i]['id']);
                        $stmt->bindValue(':count', $playerCount);
                        $stmt->bindValue(':plist', $playerList);
                        $stmt->execute();
    
                        echo 'Server: ' . $SERVERS[$i]['name'] . '<br />';
                        echo 'Player Count: ' . $playerCount . '<br />';
                        echo 'Player List: ' . $playerList;
                        echo '<hr>';
                    }
                    
                }
        }
    	catch( MinecraftPingException $e ) {
            // echo $e->getMessage();
            echo $SERVERS[$i]['name'] . ': Query Unsuccessful<hr />';
    	}
    	finally {
    		if( $Query ) {
    			$Query->Close();
    		}
        }


    }
  /*

    

	
*/
?>