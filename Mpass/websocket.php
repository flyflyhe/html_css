<?php
class WS 
{
	private $debug = true;
	private $handshake = false;

	function run($socket)
	{
		while(true) {
            $buffer = $bytes = @stream_socket_recvfrom($socket, 2048, STREAM_OOB);
            if ($bytes == 0){
                $this->disConnect($socket);
            } else {
                if (!$this->handshake){
                    $this->doHandShake($socket, $buffer);
                } else {
                    $buffer = $this->decode($buffer);
                    $this->send($socket, $buffer); 
                }
            }
        }            
	}
	
	function send($client, $msg)
	{
		$this->log("> " . $msg);
		$msg = $this->frame($msg);
		fwrite($client, $msg, strlen($msg));
		$this->log("! " . strlen($msg));
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