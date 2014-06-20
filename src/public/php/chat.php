<?php

session_start();

if( array_key_exists("chat_enable",$_SESSION) && $_SESSION['chat_enable'] )
{		
	$lastChatsToShow = 5;
	
	if( array_key_exists("test", $_GET) )
	{
		$memcache = new Memcache;
		var_dump($memcache->addServer('localhost', 11211));
		var_dump($memcache->set('test',"blub"));
		var_dump($memcache->get('test'));
		$stats = $memcache->getExtendedStats();
	    print_r($stats);
	    echo $memcache->getServerStatus('localhost', 11211);
		
	}
	else if( array_key_exists("action", $_POST) )
	{
		if( array_key_exists("chan", $_POST) && ( $_POST['chan'] == 'Global' || $_POST['chan'] == 'Syndikat' || $_POST['chan'] == 'Allianz' || $_POST['chan'] == 'Hilfe' ) )
		{
			$channel = $_POST["chan"];
		}
		else 
		{
			$channel = "glob";
		}

		$channel_prefix = $channel . "_";
        
        if( $channel == "Syndikat" )
        {
            $channel_prefix .= $_SESSION['chat_rid'] . "_";
        }
        else if( $channel == "Allianz" )
        {
        	$channel_prefix .= $_SESSION['chat_ally'] . "_";
      	}
		
		if( $_POST['action'] == 'say' )
		{
			if( array_key_exists("action", $_POST) && $_POST['say'] != "" )
			{
				$memcache = new Memcache;
				$currentTimeGroup = substr(date("U"), 0, -1);
				
				if( !$memcache->addServer('localhost', 11211) )
				{
					echo "server connection failed";
				}
				else
				{
					$index = $memcache->get($channel_prefix . "index");
					
					if( !$index || $index != $currentTimeGroup )
					{
						$memcache->set($channel_prefix . "index",$currentTimeGroup,0,1000);
						$index = $currentTimeGroup;
					}
		
					$currentChatContent = $memcache->get($channel_prefix . $index);
					
					if( !$currentChatContent )
					{
						$memcache->set($channel_prefix . $index . "_count",0,0,600);
						$currentChatContent = array();
						$newContentIndex = 0;
					}
					else
					{
						$newContentIndex = $memcache->increment($channel_prefix . $index . "_count");
					}			
					
					$chat_id = $_SESSION['chat_id'];
					$chat_name = utf8_decode($_SESSION['chat_name']);
					$chat_is_mentor = $_SESSION['chat_mentor'];
					$chat_rid = $_SESSION['chat_rid'];
					$chat_message = htmlentities($_POST['say'], ENT_QUOTES);
					
					$currentChatContent[$newContentIndex] = array($chat_id,$chat_name,$chat_is_mentor,$chat_rid,$chat_message);
					$result = $memcache->set($channel_prefix . $index,$currentChatContent,0,300);
				}
			}			
		}
		else if( $_POST['action'] == 'content' )
		{
			$memcache = new Memcache;
			if( !$memcache->addServer('localhost', 11211) )
			{
				echo "server connection failed";
			}
			else
			{
				$partIndex = 0;
				$chatToReturn = array();
				$currentTimeGroup = substr(date("U"), 0, -1);
				
				if( array_key_exists("code", $_POST) || !empty($_POST['code']) )
				{
					$timecode = substr($_POST['code'], 0, strlen($currentTimeGroup));
					$partIndex = substr($_POST['code'], strlen($currentTimeGroup), strlen($_POST['code']) - strlen($currentTimeGroup));
	
					if( !$timecode || ( $currentTimeGroup - $timecode ) > $lastChatsToShow )
					{
						$timecode = $currentTimeGroup - $lastChatsToShow;
					}
					
					if( !$partIndex )
					{
						$partIndex = 0;
					}
					
					for( $i = $timecode; $i <= $currentTimeGroup; $i++)
					{
						$chatToReturn[] = $i;
					}
				}
				else
				{
					for( $i = $currentTimeGroup - $lastChatsToShow + 1; $i <= $currentTimeGroup; $i++)
					{
						$chatToReturn[] = $i;
					}
				}
				
				$resultChat = array();
	
				$timeGroupArray = 0;
				$i = 0;
				
				for(; $timeGroupArray < count($chatToReturn); $timeGroupArray++ )
				{
					$contentArray = $memcache->get($channel_prefix . $chatToReturn[$timeGroupArray]);
	
					if( $contentArray)
					{
						$startIndex = 0;
					
						if( $timeGroupArray == 0 )
						{
							$startIndex = $partIndex;
						}
						else
						{
							$startIndex = 0;
						}
						
						for($i = $startIndex; $i < count($contentArray); $i++)
						{
							$chat_name = $contentArray[$i][1];
							if($contentArray[$i][2] && $channel == "Hilfe")
							{
								$chat_name .= " *Mentor*";
							}
							$resultChat[] = "<span style=\"font-weight:bold;\"><a class=\"linkAuftableInner\" href=\"mitteilungen.php?action=psm&rec=" . $contentArray[$i][0] . "\">" . $chat_name  . "</a>(<a class=\"linkAuftableInner\" href=\"syndicate.php?rid=" . $contentArray[$i][3] . "\">#" . $contentArray[$i][3] . "</a>):</span> " . $contentArray[$i][4] . "<br>";
						}					
					}
				}
				
				$timeGroupArray--;
	 
				echo $channel . "|" . $chatToReturn[$timeGroupArray] . $i . "|";
				
				if( count($resultChat) != 0 )
				{
					for($j = 0; $j < count($resultChat);$j++)
					{
						echo $resultChat[$j];
					}
				}
			}
		}
		else
		{
			echo "Invalid input";
		}
	}
}
else
{
	// $chat_output = "ohne Login kein chat.. :P";

}
?>

