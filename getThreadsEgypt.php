<?php
    echo "HELLO";
    $mysql = mysql_connect("deebee.yourdefaulthomepage.com","gonkclub", "cakebread") or die(mysql_error());
        //TUNISIA = 140kit_scratch_1
        //EGYPT = 140kit_scratch_2
	mysql_select_db("140kit_scratch_2",$mysql) or die(mysql_error());
	
	//$story = $_GET['__db___'];
	$story = "tweets";
	
	// max thread ID - to be calculated
	$query1 = "select MAX(threadID) from `$story`";
	echo "$query1<br>";
	
	$r1 = mysql_query($query1,$mysql) or die(mysql_error());
	$r1 = mysql_fetch_array($r1);
	
	$maxThreadID = $r1[0];
	
	// get entries whose thread ID is zero
	$query = "select * from `$story` where `threadID`=0 order by pubdate asc";	
	$result1 = mysql_query($query,$mysql) or die(mysql_error());
	
	while ($curTweet = mysql_fetch_array($result1)) {
		
		// get current id row from database
		$id = $curTweet[id];
		$cT = strtolower(loseDots($curTweet[text]));
		$curWords = explode(" ",$cT);	// array of words that make up current tweet
		$curSize = sizeof($curWords);	// size of current tweet
	
		// only if a tweet constitutes of at least three words
		if ($curSize>2) {

			// look for other tweets that came b4 this one
			$query1 = "SELECT * FROM `$story` WHERE `pubdate` <= '".$curTweet[pubdate]."'and `id`!='".$id."' order by `pubdate` desc";			
			$result = mysql_query($query1,$mysql) or die(mysql_error()); 
	
			$total = mysql_num_rows($result);
			if ($total > 0) {
				
				$continue = "yes";
				echo "<br><br>$curTweet[text]<br><br>";				
				while (($row = mysql_fetch_array($result)) && (strcmp($continue,"yes")==0)) {
				
					if (strcmp($continue,"yes")==0) {	
	
						$threshold = $curSize * 8 / 10;			// set threshold of 0.8 for similarity
						$iterTweet = $row['text'];
						$iT = strtolower(loseDots($iterTweet));
						$iterWords = explode(" ",$iT);
						$compWords = array_intersect($curWords,$iterWords);		// get intersection of words
						$arrSize = 	sizeof($compWords);
						$sharedWords = implode(",", $compWords);
					
						//echo "$iterTweet<br>";
					
						// they are part of the same thread
						if ($arrSize >= $threshold) {
							
							$num = (int) $row['threadID'];
							
							//echo "<br><br>$row[threadID] (vs.) $maxThreadID -- ";
							if ( $num <= 0 ) 
								$num = $maxThreadID+1;	
							
							// update current entry's threadID and sharedWords array (link it to a certain thread)
							$query = "UPDATE `$story` SET threadID='{$num}', sharedWords='{$sharedWords}' WHERE id='{$id}'";
						//	echo "($num) ";
	
							$result = mysql_query($query,$mysql) or die(mysql_error()); 
							$continue = "no";
	
							echo "connecting:<br> <strong>$curTweet[id]::$curTweet[pubdate]</strong> $curTweet[text]<br><strong>$row[id]::$row[pubdate]</strong> $iterTweet<br>";
						}
							   
				   }
					
						
				}
			
				if (strcmp("yes",$continue)==0) {
					// set new thread for this entry
					$maxThreadID++;
					$query = "UPDATE `$story` SET threadID='{$maxThreadID}' WHERE id='{$id}'";
					echo "$query<br>";
					$result = mysql_query($query,$mysql) or die(mysql_error()); 
		
					echo "created new thread ($maxThreadID) for $curTweet[text]<br>";		
				}
			}
			
			
		else {
			// empty database
			$query = "UPDATE `$story` SET threadID='1' WHERE id='{$id}'";
			//echo "$query<br>";
			$result = mysql_query($query,$mysql) or die(mysql_error()); 
		
		//	echo "created new thread (1) for $curTweet[text]<br>";					
		}
	}



	} //while




	function loseDots($str){
		
		$str = str_replace(",","",$str);
		$str = str_replace(".","",$str);
		$str = str_replace("?","",$str);
		$str = str_replace("!","",$str);
		$str = str_replace("’","",$str);
		$str = str_replace(":","",$str);
		$str = str_replace("'","",$str);
	//	$str = str_replace("/"," ",$str);
		$str = str_replace("\"","",$str);
	//	$str = str_replace("-"," ",$str);
		$str = str_replace("(","",$str);
		$str = str_replace(")","",$str);
		
		return $str;
	}


?>

