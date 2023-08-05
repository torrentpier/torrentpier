<?php

namespace vkApi;

class vk {
    private $token;
    private $count = -1;
    private static $class = null;

    public static function create($token){
        if(!self::$class){
            self::$class = new vk($token);
        }
        return self::$class;
    }

    private function __clone(){}
    private function __construct($token){
        $this->token = $token;
    }

    function call($method, array $data, $version = '5.131', $filename = 'response.json'){
        $this->count ++;
        if($this->count >= 3){
            $this->count = 0;
            sleep(1);
        }
        $params = array();
        foreach($data as $name => $val){
            $params[$name] = $val;
        }
        $params['access_token'] = $this->token;
        $params['v'] = $version;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.vk.com/method/' . $method);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $json = curl_exec($ch);
        curl_close($ch);

        file_put_contents('api.log', date('Y-m-d H:i:s') . ' ' . $method . ' ' . json_encode($params) . PHP_EOL, FILE_APPEND); // сохраняем ответ сервера в файл
		file_put_contents('api.log', $json . PHP_EOL, FILE_APPEND);
        return json_decode($json, true);
    }
}

class post{
    private $vk;
    private $owner;

    function __construct(vk $vk, $user = null, $group = null){
        $this->vk = $vk;
        if(!$user && !$group){
            throw new \Exception('Not found group or user');
        }
        $this->owner = array(
            'type' => $group ? 'group_id' : 'user_id',
            'value' => $group ? $group : $user
        );
        $this->owner['value'] = (int) preg_replace('/([^\d]+)/', '', $this->owner['value']);
    }

    private function logPost($text, $img = null, $data) {
    $log = date('[Y-m-d H:i:s]') . " post():\n";
    $log .= "text: " . $text . "\n";
    $log .= "img: " . $img . "\n";
    $log .= "data: " . print_r($data, true) . "\n";
    $log .= "--------------------------------------------------\n";
    error_log($log, 3, 'vk.log');
}

public function post($text, $img = null, $link = null) {
    $data = array(
        'message' => $text,
        'owner_id' => -$this->owner['value']
    );

    // Добавляем ссылку как attachment
    if ($link) {
        $data['attachments'] = $link;
    }

    // Добавляем картинку как attachment
    if ($img) {
        $upload_data = $this->load($img);

        if (isset($upload_data['response'])) {
            $attachments = 'photo' . $upload_data['response'][0]['owner_id'] . '_' . $upload_data['response'][0]['id'];

            // Добавляем картинку к уже существующим attachments, если они есть
            if (isset($data['attachments'])) {
                $data['attachments'] .= ',' . $attachments;
            } else {
                $data['attachments'] = $attachments;
            }
        }
    }

    if ($this->owner['type'] == 'group_id') {
        $data['from_group'] = 1;
    }

    $response = $this->vk->call('wall.post', $data);

    if (isset($response['error'])) {
        $error_msg = isset($response['error']['error_msg']) ? $response['error']['error_msg'] : 'Unknown error occurred';
        throw new \Exception($error_msg);
    }

    // вызываем функцию логирования
    $this->logPost($text, $img, $response);

    return $response;
}

    private function load($src) {
    $logFile = 'logs.txt';
    $logMessage = 'Loading image from source: ' . $src . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    $photo = (array)$this->getPhoto($src);
    $photo['group_id'] = $this->owner['value'];

    $data = $this->vk->call('photos.saveWallPhoto', $photo);

    $logMessage = 'Image loaded with the following data:' . PHP_EOL;
    $logMessage .= print_r($data, true) . PHP_EOL;
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    return $data;
}

    private function getPhoto($src) {
        $name = VKIMAGES_DIR . ''.md5($src).'.png';
        file_put_contents($name, file_get_contents($src));
        $data = $this->vk->call('photos.getWallUploadServer', array(
            $this->owner['type'] => $this->owner['value'],
        ), '5.131');
        $upload_url = $data['response']['upload_url'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $upload_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('photo' => '@' . $name));
        $response = json_decode(curl_exec($ch), true);
        curl_close($ch);
		
		$log_msg = sprintf("[%s] - Uploaded photo %s\n", date('Y-m-d H:i:s'), $src);
		error_log($log_msg, 3, 'logs.txt');
		
        return array(
            'server' => $response['server'],
            'photo' => stripslashes($response['photo']),
            'hash' => $response['hash'],
        );
    }
}