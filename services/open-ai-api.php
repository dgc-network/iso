<?php
/**
 * Copyright 2022 dgc.network
 *
 * dgc.network licenses this file to you under the Apache License,
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

if (!class_exists('open_ai_api')) {
    class open_ai_api {

        private $openai_api_key;
    
        public function __construct($openai_api_key='') {
            add_action('admin_init', array( $this, 'open_ai_register_settings' ) );
            $this->openai_api_key = get_option('open_ai_api_key');
        }
    
        function open_ai_register_settings() {
            // Register Open AI section
            add_settings_section(
                'open-ai-section-settings',
                'Open AI Settings',
                array( $this, 'open_ai_section_settings_callback' ),
                'web-service-settings'
            );
        
            // Register fields for Open AI section
            add_settings_field(
                'open_ai_api_key',
                'API KEY',
                array( $this, 'open_ai_api_key_callback' ),
                'web-service-settings',
                'open-ai-section-settings'
            );
            register_setting('web-service-settings', 'open_ai_api_key');
        }
        
        function open_ai_section_settings_callback() {
            echo '<p>Settings for Open AI.</p>';
        }
        
        function open_ai_api_key_callback() {
            $value = get_option('open_ai_api_key');
            echo '<input type="text" id="open_ai_api_key" name="open_ai_api_key" style="width:100%;" value="' . esc_attr($value) . '" />';
        }
                
        public function createCompletion($param) {
    
            $param["model"]="text-davinci-003";
            $param["max_tokens"]=1000;

            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
    
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
    
            $response = file_get_contents('https://api.openai.com/v1/completions', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
    
            //return $response;
            $data = json_decode($response, true);
            return $data['choices'][0];
        }
        
        public function createChatCompletion($prompt) {
            $param = array(
                'model' => 'gpt-3.5-turbo',
                'messages' => array(
                    // Fixed system role for maintaining the subject
                    array('role' => 'system', 'content' => 'iso-helper'),
                    // User's message
                    array('role' => 'user', 'content' => $prompt),
                ),
                'temperature' => 1.0,
                'max_tokens' => 4000,
                'frequency_penalty' => 0,
                'presence_penalty' => 0,
            );
        
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->openai_api_key,
            );
        
            $context = stream_context_create([
                'http' => [
                    'ignore_errors' => true,
                    'method' => 'POST',
                    'header' => implode("\r\n", $header),
                    'content' => json_encode($param),
                ],
            ]);
        
            $response = file_get_contents('https://api.openai.com/v1/chat/completions', false, $context);
            if (strpos($http_response_header[0], '200') === false) {
                error_log('Request failed: ' . $response);
            }
        
            $data = json_decode($response, true);
            $responseContent = $data['choices'][0]['message']['content'];
        
            return $responseContent;
        }
    }
    $open_ai_api = new open_ai_api();
}
