# bIRCh BOTtle
---

### Setup
1. Clone the repository to your local server
2. Copy config-sample.php to config.php and edit the settings inside

### Run
From the command line, run 'php index.php'

### Buidling Triggers
In triggers.php is a sample trigger. A basic trigger looks like:
    
    $trigger = '!hello';
    if (substr($text, 0, strlen($trigger)) == $trigger) {
      fn_say($socket, $channel, 'Hello!');
    }
    
The first line defines the trigger to look for. Inside the if block is where the logic goes. To simply say something to the channel, just pass the text in as the 3rd parameter to fn_say.

The trigger below will fetch the most recent value of Bitcoins in USD from btc-e.com and say it to the channel:
  
    $trigger = '!bitcoin';
    if (substr($text, 0, strlen($trigger)) == $trigger) {
      $bitcoin = file_get_contents('https://btc-e.com/api/2/btc_usd/ticker');
      $bitcoin = json_decode($bitcoin, true);
      fn_say($socket, $channel, 'Current BTC Value: '.$bitcoin['ticker']['last']);
    }