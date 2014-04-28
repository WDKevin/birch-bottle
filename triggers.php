<?
  // Sample Trigger
  if (substr($trigger, 0, 4) == '.hello') {
    fn_say($socket, $channel, 'Hello '.$params.'!');
  }