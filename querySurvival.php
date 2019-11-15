<?php
	require __DIR__ . '/src/MinecraftPing.php';
	require __DIR__ . '/src/MinecraftPingException.php';
    
	use xPaw\MinecraftPing;
	use xPaw\MinecraftPingException;
    
    define( 'MQ_SERVER_ADDR', '167.114.173.235' );
	define( 'MQ_SERVER_PORT', 25568 );
	define( 'MQ_TIMEOUT', 1 );

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

	try
	{
		$Query = new MinecraftPing( MQ_SERVER_ADDR, MQ_SERVER_PORT, MQ_TIMEOUT );
		
        //print_r( $Query->Query() );
        $data = $Query->Query( );
        $playerCount = $data[players][online];
        
        $stmt = $conn->prepare("INSERT INTO playerCount (serverID,playercount) VALUES (:serverID,:count,)");
        $stmt->bindValue(':serverID', 1);
        $stmt->bindValue(':count', $playerCount);
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