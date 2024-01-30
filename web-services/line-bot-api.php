<?php
/**
 * Copyright 2016 LINE Corporation
 *
 * LINE Corporation licenses this file to you under the Apache License,
 * version 2.0 (the "License"); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at:
 *
 *   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if (!class_exists('line_bot_api')) {
    class line_bot_api {
        /** @var string */
        public $channel_access_token;

        public function __construct($channelAccessToken='', $channelSecret='') {
    
            if ($channelAccessToken==''||$channelSecret=='') {
                if (file_exists(dirname( __FILE__ ) . '/config.ini')) {
                    $config = parse_ini_file(dirname( __FILE__ ) . '/config.ini', true);
                    if ($config['LineBot']['Token'] == null || $config['LineBot']['Secret'] == null) {
                        error_log("config.ini uncompleted!", 0);
                    } else {
                        $channelAccessToken = $config['LineBot']['Token'];
                        $channelSecret = $config['LineBot']['Secret'];
                    }
                }    
            } 
            $this->channel_access_token = $channelAccessToken;
        }

        /**
         * @return array
         */
        public function parseEvents() {
 
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                error_log('Method not allowed');
            }
        
            $entityBody = file_get_contents('php://input');
        
            if ($entityBody === false || strlen($entityBody) === 0) {
                http_response_code(400);
                error_log('Missing request body');
            }
        
            $data = json_decode($entityBody, true);
        
            if ($data === null) {
                // Handle JSON decoding error
                http_response_code(400);
                error_log('Failed to decode JSON');
                return [];
            }
        
            if (!isset($data['events']) || !is_array($data['events'])) {
                // Handle the case where 'events' key is not present or is not an array
                http_response_code(400);
                error_log('Missing "events" key or it is not an array');
                return [];
            }
        
            return $data['events'];
        }

        /**
         * @return mixed
         */
        public function backup_parseEvents() {
         
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                error_log('Method not allowed');
            }
    
            $entityBody = file_get_contents('php://input');
            
    
            if ($entityBody === false || strlen($entityBody) === 0) {
                http_response_code(400);
                error_log('Missing request body');
            }

            $data = json_decode($entityBody, true);
/*
            if ($data === null || !isset($data['events'])) {
                // Handle the case where decoding fails or 'events' key is not present
                http_response_code(400);
                error_log('Failed to decode JSON or missing "events" key');
                return []; // or any other default value or action you prefer
            }
*/            
            return $data['events'];
        }

        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public function broadcastMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/broadcast', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public function replyMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($message, JSON_UNESCAPED_UNICODE),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/reply', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $message
         * @return void
         */
        public function pushMessage($message) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($message),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/message/push', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $content
         * @return void
         */
        public function createRichMenu($content) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($content),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/richmenu', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param string $richMenuId
         * @return void
         */
        public function uploadImageToRichMenu($richMenuId, $imagePath, $content) {
    
            $header = array(
                'Content-Type: image/png',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($content),
                    'content' => $imagePath,
                ],
            ]);
    
            $response = file_get_contents('https://api-data.line.me/v2/bot/richmenu/'.$richMenuId.'/content', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param array<string, mixed> $content
         * $content['richMenuId']
         * $content['userIds']
         * @return void
         */
        public function setDefaultRichMenu($content) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($content),
                ],
            ]);
    
            if (is_null($content['userIds'])) {
                $response = file_get_contents('https://api.line.me/v2/bot/user/all/richmenu/'.$content['richMenuId'], false, $context);
            } else {
                $response = file_get_contents('https://api.line.me/v2/bot/richmenu/bulk/link', false, $context);
            }
            
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        }
    
        /**
         * @param string $userId
         * @return object
         */
        public function getProfile($userId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                    //'content' => json_encode($userId),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/profile/'.$userId, false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $groupId
         * @return object
         */
        public function getGroupSummary($groupId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/group/'.$groupId.'/summary', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $groupId, $userId
         * @return object
         */
        public function getGroupMemberProfile($groupId, $userId) {
    
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
    
            $response = file_get_contents('https://api.line.me/v2/bot/group/'.$groupId.'/member'.'/'.$userId, false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            $response = stripslashes($response);
            $response = json_decode($response, true);
            
            return $response;
        }
    
        /**
         * @param string $messageId
         * @return object
         */
        public function getContent($messageId) {
    
            $header = array(
                //'Content-Type: application/octet-stream',
                'Authorization: Bearer ' . $this->channel_access_token,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'GET',
                    'header' => implode("\r\n", $header),
                ],
            ]);
            $response = file_get_contents('https://api-data.line.me/v2/bot/message/'.$messageId.'/content', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
            return $this->save_temp_image($response);
            //return var_dump($response);
            //return $response;
    
        }
    
        /**
         * Save the submitted image as a temporary file.
         *
         * @todo Revisit file handling.
         *
         * @param string $img Base64 encoded image.
         * @return false|string File name on success, false on failure.
         */
        protected function save_temp_image($img) {
    
            // Strip the "data:image/png;base64," part and decode the image.
            $img = explode(',', $img);
            $img = isset($img[1]) ? base64_decode($img[1]) : base64_decode($img[0]);
            if (!$img) {
                return false;
            }
            // Upload to tmp folder.
            $filename = 'user-feedback-' . date('Y-m-d-H-i-s');
            $tempfile = wp_tempnam($filename, sys_get_temp_dir());
            if (!$tempfile) {
                return false;
            }
            // WordPress adds a .tmp file extension, but we want .png.
            if (rename($tempfile, $filename . '.png')) {
                $tempfile = $filename . '.png';
            }
            if (!WP_Filesystem(request_filesystem_credentials(''))) {
                return false;
            }
            /**
             * WordPress Filesystem API.
             *
             * @var \WP_Filesystem_Base $wp_filesystem
             */
            global $wp_filesystem;
            //$wp_filesystem->chdir(get_temp_dir());
            $success = $wp_filesystem->put_contents($tempfile, $img);
            if (!$success) {
                return false;
            }
            //return $tempfile;
            $upload = wp_get_upload_dir();
            $url = '<img src="'.$upload['url'].'/'.$filename. '.png">';
            $url = '<img src="'.sys_get_temp_dir().$filename. '.png">';
            //$url = $wp_filesystem->wp_content_dir().'/'.$filename;
            return $url;
        }
      
        /**
         * @param string $body
         * @return string
         */
        private function sign($body) {
    
            $hash = hash_hmac('sha256', $body, $this->channelSecret, true);
            $signature = base64_encode($hash);
            return $signature;
        }
    }
}
