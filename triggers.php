<?
  // Sample Trigger - Basic trigger
  $trigger = '!hello';
  if (substr($text, 0, strlen($trigger)) == $trigger) {
    fn_say($socket, $channel, 'Hello!');
  }