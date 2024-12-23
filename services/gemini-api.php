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

if (!class_exists('gemini_api')) {
    class gemini_api {

        private $gemini_api_key;
    
        public function __construct($gemini_api_key='') {
            add_action('admin_init', array( $this, 'gemini_register_settings' ) );
            $this->gemini_api_key = get_option('gemini_api_key');
        }
    
        function gemini_register_settings() {
            // Register gemini section
            add_settings_section(
                'gemini-section-settings',
                'Gemini Settings',
                array( $this, 'gemini_section_settings_callback' ),
                'web-service-settings'
            );
        
            // Register fields for Open AI section
            add_settings_field(
                'gemini_api_key',
                'API KEY',
                array( $this, 'gemini_api_key_callback' ),
                'web-service-settings',
                'gemini-section-settings'
            );
            register_setting('web-service-settings', 'gemini_api_key');
        }
        
        function gemini_section_settings_callback() {
            echo '<p>Settings for Gemini.</p>';
        }
        
        function gemini_api_key_callback() {
            $value = get_option('gemini_api_key');
            echo '<input type="text" id="gemini_api_key" name="gemini_api_key" style="width:100%;" value="' . esc_attr($value) . '" />';
        }

        public function generateContent($userMessage) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $this->gemini_api_key;
            
            $data = array(
              "contents" => array(
                array(
                  "parts" => array(
                    array(
                      "text" => $userMessage,
                    )
                  )
                )
              )
            );
            
            $json_data = json_encode($data);
            
            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Set to true to return the response
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            
            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                $decoded_response = json_decode($response, true);

                if (isset($decoded_response['candidates'][0]['content']['parts'][0]['text'])) {
                    $generated_text = $decoded_response['candidates'][0]['content']['parts'][0]['text'];
                    echo $this->convert_markdown_to_html($generated_text);
                } else {
                    echo "Failed to generate text.";
                }
            }
            curl_close($ch);

        }

        function convert_markdown_to_html($markdown) {
            // Convert special HTML characters
            $markdown = htmlspecialchars($markdown, ENT_NOQUOTES, 'UTF-8');
        
            // Convert headers
            $markdown = preg_replace('/^\* \*\*(.+?)\*\*: (.+)$/m', '<li style="margin-bottom: 10px;"><strong style="color: #000;">$1:</strong> $2</li>', $markdown); // Bold items with description
            $markdown = preg_replace('/^\* \*\*(.+?)\*\*/m', '<li style="margin-bottom: 10px;"><strong style="color: #000;">$1</strong></li>', $markdown); // Bold items without description
            $markdown = preg_replace('/^\* (.+)$/m', '<li style="margin-bottom: 10px;">$1</li>', $markdown); // Regular list items
        
            // Wrap <li> items in <ul> tags
            $markdown = preg_replace_callback(
                '/(<li.*?>.*?<\/li>)+/s',
                function ($matches) {
                    return '<ul style="margin: 10px 0 20px 20px;">' . $matches[0] . '</ul>';
                },
                $markdown
            );
        
            // Convert paragraphs (two newlines separate paragraphs)
            $markdown = preg_replace('/\n{2,}/', '</p><p>', $markdown); // Create paragraphs
            $markdown = '<p style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">' . $markdown . '</p>'; // Wrap the entire content with <p> tags
            $markdown = str_replace("\n", '<br>', $markdown); // Convert single newlines to <br> tags
        
            // Clean up nested lists
            $markdown = preg_replace('/<\/ul>\s*<ul>/', '', $markdown); // Remove redundant <ul> tags
        
            return $markdown;
        }
   }
    $gemini_api = new gemini_api();
}