<?php
  // Prevent PHP from stopping the script after 30 sec
  set_time_limit(0);

  // Include config options
  require_once('config.php');

  // Establish connection
  $socket = fsockopen($server, $port);
  
  // Set user info
  fputs($socket,"USER $nick $nick $nick $nick :$nick\n");
  
  // Set nick
  fputs($socket,"NICK $nick\n");
  
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
    
      // Echo response to log, screen, web browser, etc.
      $data = nl2br($data);
      echo $data;
      flush();

      // Strip line break HTML off the end of the response
      $data = str_replace('<br />', '', $data);
    
      // Check for pingbacks
      $ping = explode(' ', $data);

      // Answer ping requests from the server
      if ($ping[0] == "PING"){
        fputs($socket, "PONG ".$ping[1]."\n");
      }

      // Checks to make sure we are analyzing channel activity only
      if (strpos($data, $channel) !== false) {
        // Parse the response to get the users text
        $data = explode(':', $data);

        if (isset($data[2])) {
          // Set the users text
          $user_text = $data[2];
    
          if (isset($data[3])) {
            $url = $data[3];
          }
          
          // Get users nick
          $nick = explode('!', $data[1]);
          $nick = $nick[0];
                    
          $text = explode(' ', $user_text);
          $trigger = $text[0];
          
          $params = str_replace($trigger.' ', '', $user_text);

          // Sample Trigger
          if (substr($trigger, 0, 4) == '.hello') {
            fn_say($socket, $channel, 'Hello '.$params.'!');
          }
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
      $result[$count] = "$score ($ups/$downs) $title - $url<br>";
      $count++;
    }
    return $result;
  }
  
  function fn_get_page_title($url){
    $url = 'http://'.$url;
    $string = file_get_contents($url);
    if (strlen($string) > 0) {
      preg_match("/\<title\>(.*)\<\/title\>/", $string, $title);
      $title = $title[1];
      return 'Title: '.$title.' | URL: '.$url;
    }
  }
  
  ?>