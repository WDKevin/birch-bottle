<?
  // Sample Trigger
  if (substr($trigger, 0, 6) == '.hello') {
    fn_say($socket, $channel, 'Hello '.$params.'!');
  }
