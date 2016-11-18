<?php
/*
----------------------------------------------------
Mpass - Multi-Process Socket Server for PHP

copyright (c) 2010 Laruence
http://www.laruence.com

If you have any questions or comments, please email:
laruence@yahoo.com.cn

*/

/**
 * Mpass_Request is a wrapper for a client socket
 * and define the communication protocol
 *<code>
 * Read:
 * while (!$request->eof()) {
 *    $input .= $request->read($length);
 * }
 *
 * Write:
 * $len = $request->write($data);
 * //write do not send to client immediately
 * //we need call flush after write
 * $request->flush();
 *</code>
 *
 * @package Mpass
 */
class Mpass_Request {

    /** client name 
     *  eg: 10.23.33.158:3437
     */
    public  $name    = NULL;

	public $_socket = NULL;
	private $_pos    = 0;

    public  $initialized = FALSE;

	public function __construct($client) {
		if (!is_resource($client)) {
            return;
		}

		$this->_socket = $client;
        $this->name    = stream_socket_get_name($client, TRUE);

        $this->initialized = TRUE;
	}

	public function read($length = 1024) {
		$data = stream_socket_recvfrom($this->_socket, $length);
		$len  = strlen($data);
		$this->_pos += $len;

		return $data;
	}

	public function peek($length = 1) {
		return stream_socket_recvfrom($this->_socket, 1, STREAM_PEEK);
	}

    /**
     * send data to client
     */
	public function write($data) {

		$data 	= strval($data);
		$length = strlen($data);

		if ($length == 0) {
            return 0;
		}

        /* in case of send failed */
        $alreay_sent = 0;
        while ($alreay_sent < $length) {
            $alreay_sent += stream_socket_sendto($this->_socket, substr($data, $alreay_sent));
        }

        return $length;
    }

    public function name() {
        return $this->name;
    }

	public function __destruct() {
        /** in case of unset socket in user script
         *  we need do this in Server side */
        /* stream_socket_shutdown($this->_socket, STREAM_SHUT_RDWR); */
	}

    private $debug = true;

	private $handshake = false;

	function run()
	{
		while(true) {
            $buffer = $bytes = $this->read(2048);
            var_dump($bytes);die;
            if ($bytes == 0){
                $this->disConnect($this->_socket);
            } else {
                if (!$this->handshake){
                    $this->doHandShake($this->_socket, $buffer);
                } else {
                    $buffer = $this->decode($buffer);
                    $this->write($buffer); 
                }
            }
        }            
	}


	function disConnect($socket)
	{
		stream_socket_shutdown($socket, STREAM_SHUT_WR);
		$this->say($socket . " DISCONNECTED!");
	}

	function doHandShake($socket, $buffer)
	{
		$this->log("\nRequesting handshake...");
		$this->log($buffer);
		list($resource, $host, $origin, $key) = $this->getHeaders($buffer);
		$this->log("Handshaking...");
		$upgrade  = "HTTP/1.1 101 Switching Protocol\r\n" .
					"Upgrade: websocket\r\n" .
					"Connection: Upgrade\r\n" .
					"Sec-WebSocket-Accept: " . $this->calcKey($key) . "\r\n\r\n";  //必须以两个回车结尾
		$this->log($upgrade);
		$sent = fwrite($socket, $upgrade, strlen($upgrade));
		$this->handshake=true;
		$this->log("Done handshaking...");
		return true;
	}

	function getHeaders($req)
	{
		$r = $h = $o = $key = null;
		if (preg_match("/GET (.*) HTTP/"              ,$req,$match)) { $r = $match[1]; }
		if (preg_match("/Host: (.*)\r\n/"             ,$req,$match)) { $h = $match[1]; }
		if (preg_match("/Origin: (.*)\r\n/"           ,$req,$match)) { $o = $match[1]; }
		if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/",$req,$match)) { $key = $match[1]; }
		return array($r, $h, $o, $key);
	}

	function calcKey($key)
	{
		//基于websocket version 13
		$accept = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
		return $accept;
	}

	function decode($buffer) 
	{
		$len = $masks = $data = $decoded = null;
		$len = ord($buffer[1]) & 127;

		if ($len === 126) {
			$masks = substr($buffer, 4, 4);
			$data = substr($buffer, 8);
		} else if ($len === 127) {
			$masks = substr($buffer, 10, 4);
			$data = substr($buffer, 14);
		} else {
			$masks = substr($buffer, 2, 4);
			$data = substr($buffer, 6);
		}

		for ($index = 0; $index < strlen($data); $index++) {
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}
		
		return $decoded;
	}

	function frame($s)
	{
		$a = str_split($s, 125);
		if (count($a) == 1){
			return "\x81" . chr(strlen($a[0])) . $a[0];
		}
		$ns = "";
		foreach ($a as $o){
			$ns .= "\x81" . chr(strlen($o)) . $o;
		}
		return $ns;
	}

	
	function say($msg = "") 
	{
		echo $msg . "\n";
	}

	function log($msg = "")
	{
		if ($this->debug){
			echo $msg . "\n";
		} 
	}
}

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
