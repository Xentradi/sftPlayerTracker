<?php
	require __DIR__ . '/src/MinecraftPing.php';
	require __DIR__ . '/src/MinecraftPingException.php';
    
    
    
	use xPaw\MinecraftPing;
	use xPaw\MinecraftPingException;
    
    define( 'MQ_SERVER_ADDR', '167.114.173.235' );
	define( 'MQ_SERVER_PORT', 25568 );
    define( 'MQ_TIMEOUT', 1 );

    $dbHostname = 'localhost';
    $dbUsername = 'sftTesting';
    $dbPassword = 'Hawa11an!';
    $dbTableName = 'counter';
    $mcServerID = 1;

    try {
        $conn = new PDO("mysql:host=$dbHostname;dbname=sftTesting", $dbUsername, $dbPassword);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        echo "Database connected successfully<br />"; 
    }
    catch(PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
    }

	try
	{
		$Query = new MinecraftPing( MQ_SERVER_ADDR, MQ_SERVER_PORT, MQ_TIMEOUT );
		
        //print_r( $Query->Query() );
        $data = $Query->Query( );
        $playerCount = $data[players][online];
        $playerList = '';
        for($i=0, $len=count($data[players][sample]); $i < $len; $i++) {
            $playerList .= $data[players][sample][$i][name] . ' ';
        }

        

        $stmt = $conn->prepare("INSERT INTO " . $dbTableName . " (serverID,playercount,playerList) VALUES (:serverId,:count,:plist)");
        $stmt->bindValue(':serverId', $mcServerID);
        $stmt->bindValue(':count', $playerCount);
        $stmt->bindValue(':plist', $playerList);
        $stmt->execute();

        echo 'Player Count: ' . $playerCount . '<br />';
        echo 'Player List: ' . $playerList;
    }
	catch( MinecraftPingException $e )
	{
		echo $e->getMessage();
	}
	finally
	{
		if( $Query )
		{
			$Query->Close();
		}
	}
?>
