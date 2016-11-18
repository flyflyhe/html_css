<?php
require ('./Server.php');
require ('./websocket.php');

class TestPserver implements Mpass_IExecutor {
	function execute(Mpass_Request $client) {
		try {
			$client->run();
		} catch (\Exception $e) {
			echo $e->getMessage();die;
		}
	}
}

$host = "127.0.0.1";
$port = 4000;

$service = new Mpass_Server($host, $port, new TestPserver);

$service->run();
