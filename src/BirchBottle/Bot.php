<?php
namespace BirchBottle;

class Bot
{
    protected $hostname;
    protected $port;
    protected $bot_nick;
    protected $channels;

    /**
     * @var resource
     */
    protected $socket;

    public function __construct($hostname, $port = 6667, $nick = 'BirchBottle', $channels = array())
    {
        // TODO lrobert: We should be enforcing a channel array rather than trying to force it as a single channel
        if (is_array($channels) && count($channels) > 0) {
            $channels = array_shift($channels);
        }

        $this->hostname = $hostname;
        $this->port = $port;
        $this->bot_nick = $nick;
        $this->channels = $channels;
    }

    /**
     * Handles opening the connection to the server and logging in
     *
     * @throws \Exception
     * @return resource
     */
    protected function connect()
    {
        if (!isset($this->socket)) {
            // TODO lrobert: We need to use the error parameters and handle them in a better way.
            $this->socket = fsockopen($this->hostname, $this->port);

            if (!($this->socket)) {
                throw new \Exception("Could not establish connection");
            }

            // Set user info
            // TODO lrobert: Set proper user info
            fputs($this->socket, sprintf('USER %1$s %1$s %1$s %1$s :%1$s\n', $this->bot_nick));

            // Set nick
            fputs($this->socket, sprintf('NICK %1$s\n', $this->bot_nick));

            // Join channel(s)
            // TODO: convert channels to actually be an array
            fputs($this->socket, sprintf('JOIN %1$s\n', $this->channels));
        }

        return $this->socket;
    }

    /**
     * Disconnects from IRC
     */
    protected function disconnect()
    {
        if (isset($this->socket)) {
            // TODO lrobert: We need some logic to put quit and part messages into IRC
            fclose($this->socket);
            unset($this->socket);
        }
    }

    /**
     * Crappy function to throw the old contents of the bot in.
     */
    public function run()
    {
        try {
            // Establish connection
            $socket = $this->connect();

            /**
             * Parses the current server as it can change after connecting
             */
            $server = fgets($socket);
            $server = strstr($server, 'NOTICE', true);
            $server = str_replace(':', '', $server);

            while (1) {

                // Catch response from the server (line by line)
                while ($data = fgets($socket)) {

                    // Strip server from string
                    $data = str_replace(':' . $server, '', $data);

                    // Strip line break HTML off the end of the response
                    $data = nl2br($data);
                    $data = str_replace('<br />', '', $data);

                    // Echo response to log, screen, web browser, etc.
                    echo $data;
                    flush();

                    // Check for ping backs
                    $ping = explode(' ', $data);

                    // Answer ping requests from the server
                    if ($ping[0] == "PING") {
                        fputs($socket, sprintf('PONG %1$s\n', $ping[1]));
                        break;
                    }

                    // Auto-rejoin channel on kick
                    if (strpos($data, sprintf('KICK %1$s %2$s', $this->channels, $this->bot_nick)) !== false) {
                        echo sprintf('CHANNEL ACTION: Kicked from %1$s' . PHP_EOL, $this->channels);
                        fputs($socket, sprintf('JOIN %1$s\n', $this->channels));
                        break;
                    }

                    // Checks to make sure we are analyzing channel activity only
                    if (strpos($data, $this->channels) !== false) {
                        // Parse the response to get the users text
                        $start = strpos($data, sprintf('PRIVMSG %1$s :', $this->channels));
                        $count = strlen(sprintf('PRIVMSG %1$s :', $this->channels));
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
        } catch (\Exception $e) {
            $this->disconnect();
            throw $e;
        }

        $this->disconnect();
    }

    /**
     * Sends a message
     * @param $socket
     * @param $channel
     * @param $message
     * @param null $nick
     */
    function fn_say($socket, $channel, $message, $nick = null)
    {
        if (isset($nick)) {
            fputs($socket, sprintf('PRIVMSG %1$s :%2$s: %3$s\n', $channel, $nick, $message));
        } else {
            fputs($socket, sprintf('PRIVMSG %1$s :%2$s\n', $channel, $message));
        }
    }
}