<?php
  $server = "";
  $port = 6667;
  $bot_nick = "";
  $channel = "";
  $bot_owner = "";

  // Response codes that should be blocked from logging are added here (372 is MOTD, 376 is End of MOTD)
  $blocked_responses = array('372', '376');