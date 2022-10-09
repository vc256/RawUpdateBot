<?php
define('BOT_TOKEN', 'x');
define('API_URL', 'https://api.telegram.org/bot'.BOT_TOKEN.'/');
define('WEBHOOK_URL', 'x');

function apiRequestWebhook($method, $parameters) {
	if (!is_string($method)) {
		error_log("Method name must be a string\n");
		return false;
	}

	if (!$parameters) {
		$parameters = array();
	} else if (!is_array($parameters)) {
		error_log("Parameters must be an array\n");
		return false;
	}

	$parameters['method'] = $method;

	header("Content-Type: application/json");
	echo json_encode($parameters);
	return true;
}

function exec_curl_request($handle) {
	$response = curl_exec($handle);

	if ($response === false) {
		$errno = curl_errno($handle);
		$error = curl_error($handle);
		error_log("Curl returned error $errno: $error\n");
		curl_close($handle);
		return false;
	}

	$http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
	curl_close($handle);

	if ($http_code >= 500) {
		// do not wat to DDOS server if something goes wrong
		sleep(10);
		return false;
	} else if ($http_code != 200) {
		$response = json_decode($response, true);
		error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
		if ($http_code == 401) {
			throw new Exception('Invalid access token provided');
		}
		return false;
	} else {
		$response = json_decode($response, true);
		if (isset($response['description'])) {
			error_log("Request was successful: {$response['description']}\n");
		}
		$response = $response['result'];
	}

	return $response;
}

function apiRequestJson($method, $parameters) {
	if (!is_string($method)) {
		error_log("Method name must be a string\n");
		return false;
	}

	if (!$parameters) {
		$parameters = array();
	} else if (!is_array($parameters)) {
		error_log("Parameters must be an array\n");
		return false;
	}

	$parameters['method'] = $method;

	$handle = curl_init(API_URL);
	curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 60);
	curl_setopt($handle, CURLOPT_TIMEOUT, 60);
	curl_setopt($handle, CURLOPT_POST, true);
	curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
	curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

	return exec_curl_request($handle);
}

function processMessage($message) {
	// process incoming message
	
	$message_id = $message['message_id'];
	$chat_id = $message['chat']['id'];
	
	$text = json_encode($message, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	
	do {
		$fragment = mb_substr($text, 0, 4096);
		
		apiRequestJson('sendMessage', [
				'chat_id' => $chat_id,
				'text' => '<pre>'.$fragment.'</pre>',
				'parse_mode' => 'HTML',
				'disable_web_page_preview' => true,
				'disable_notification' => true,
				'reply_to_message_id' => $message_id
		]);
		
		$text = mb_substr($text, 4096);
	} while (mb_strlen($text, 'UTF-8') > 0);
	
	return;
}

function processChatMemberUpdated($chatMemberUpdated) {
	
	$chat_id = $chatMemberUpdated['chat']['id'];
	
	$text = json_encode($chatMemberUpdated, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	
	do {
		$fragment = mb_substr($text, 0, 4096);
		
		apiRequestJson('sendMessage', [
				'chat_id' => $chat_id,
				'text' => '<pre>'.$fragment.'</pre>',
				'parse_mode' => 'HTML',
				'disable_web_page_preview' => true,
				'disable_notification' => true
		]);
		
		$text = mb_substr($text, 4096);
	} while (mb_strlen($text, 'UTF-8') > 0);
	
	return;
}

function processChatJoinRequest($chatJoinRequest) {
	
	$chat_id = $chatJoinRequest['chat']['id'];
	
	$text = json_encode($chatJoinRequest, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
	
	do {
		$fragment = mb_substr($text, 0, 4096);
		
		apiRequestJson('sendMessage', [
				'chat_id' => $chat_id,
				'text' => '<pre>'.$fragment.'</pre>',
				'parse_mode' => 'HTML',
				'disable_web_page_preview' => true,
				'disable_notification' => true
		]);
		
		$text = mb_substr($text, 4096);
	} while (mb_strlen($text, 'UTF-8') > 0);
	
	return;
}

if (php_sapi_name() == 'cli') {
	// if run from console, set or delete webhook
	apiRequestJson('setWebhook', [
		'url' => isset($argv[1]) && $argv[1] == 'delete' ? '' : WEBHOOK_URL,
		'allowed_updates' => [
			'message', 'edited_message', 
			'my_chat_member', 'chat_member', 
			'channel_post', 'edited_channel_post',
            'chat_join_request',
		]
	]);
	exit;
}


$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!$update) {
	// receive wrong update, must not happen
	exit;
}

if (isset($update['message'])) {
	processMessage($update['message']);
} elseif (isset($update['edited_message'])) {	
	processMessage($update['edited_message']);
} elseif (isset($update['chat_member'])) {
	processChatMemberUpdated($update['chat_member']);
} elseif (isset($update['my_chat_member'])) {
	processChatMemberUpdated($update['my_chat_member']);
} elseif (isset($update['chat_join_request'])) {
	processChatJoinRequest($update['chat_join_request']);
} elseif (isset($update['channel_post'])){
	 processMessage($update['channel_post']);
} elseif (isset($update['edited_channel_post'])) {
	 processMessage($update['edited_channel_post']);
}
