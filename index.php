<?php
  // Prevent PHP from stopping the script after 30 sec
  set_time_limit(0);

  // Include config options
  require_once('config.php');

  // Establish connection
  $socket = fsockopen($server, $port);
  
  // Set user info
  fputs($socket,"USER $bot_nick $bot_nick $bot_nick $bot_nick :$bot_nick\n");
  
  // Set nick
  fputs($socket,"NICK $bot_nick\n");
  
  // Join channel(s)
  fputs($socket, "JOIN ".$channel."\n");

  // Parse out the connected server
  $server = fgets($socket);
  $server = strstr($server, 'NOTICE', true);
  $server = str_replace(':', '', $server);

  while(1) {
    
    // Catch response from the server (line by line)
    while($data = fgets($socket)) {
      
      // Strip server from string
      $data = str_replace(':'.$server, '', $data);
    
      // Strip line break HTML off the end of the response
      $data = nl2br($data);
      $data = str_replace('<br />', '', $data);

      // Echo response to log, screen, web browser, etc.
      echo $data;
      flush();

      // Check for pingbacks
      $ping = explode(' ', $data);

      // Answer ping requests from the server
      if ($ping[0] == "PING"){
        fputs($socket, "PONG ".$ping[1]."\n");
        break;
      }

      // Auto-rejoin channel on kick
      if (strpos($data, "KICK $channel $bot_nick") !== false) {
        echo "CHANNEL ACTION: Kicked from $channel\r\n";
        fputs($socket, "JOIN ".$channel."\n");
        break;
      }

      // Checks to make sure we are analyzing channel activity only
      if (strpos($data, $channel) !== false) {
        // Parse the response to get the users text
        $start = strpos($data, "PRIVMSG $channel :");
        $count = strlen("PRIVMSG $channel :");
        $text = substr($data, $start + $count);

        if (isset($text)) {
          // Get users nick
          $nick = explode('!', $data[1]);
          $nick = $nick[0];

          include 'triggers.php';
        }
      }
    }
  }
  
  function fn_say($socket, $channel, $message, $nick = null) {
    if (isset($nick)) {
      fputs($socket, "PRIVMSG $channel :$nick: $message\n");
    } else {
      fputs($socket, "PRIVMSG $channel :$message\n");
    }
  }
  
  function fn_get_reddit_posts($subreddit, $sort, $limit = 5) {
    $string_reddit = file_get_contents("http://reddit.com/r/$subreddit/$sort.json");
    $json = json_decode($string_reddit, true);  

    $children = $json['data']['children'];
    
    $count = 0;
    
    foreach ($children as $child) {
      if ($count == $limit) {
        break;
      }
      $title = $child['data']['title'];
      $url = "http://reddit.com".$child['data']['permalink'];
      $score = $child['data']['score'];
      $ups = $child['data']['ups'];
      $downs = $child['data']['downs'];
      $result[$count] = "$score ($ups/$downs) $title - $url";
      $count++;
    }
    return $result;
  }
  
  function fn_get_page_title($url){
    $string = file_get_contents($url);
    if (strlen($string) > 0) {
      preg_match("/\<title\>(.*)\<\/title\>/", $string, $title);
      $title = $title[1];
      return 'Title: '.$title;
    }
  }