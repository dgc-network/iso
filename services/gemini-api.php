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
/*            
            // gemini generate the content
            $header = array(
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->gemini_api_key,
            );

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent";

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

            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Set to true to return the response
            curl_setopt($ch, CURLOPT_VERBOSE, true);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            } else {
                $decoded_response = json_decode($response, true);
                echo print_r($decoded_response, true);

            }

            curl_close($ch);
*/


        


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
                    echo $generated_text;
                } else {
                    echo "Failed to generate text.";
                }
                                



//$decoded_response = json_decode($response, true);
//echo print_r($decoded_response, true);
/*
              // Access the generated text here
              if (isset($decoded_response['generated_texts'][0]['text'])) {
                $generated_text = $decoded_response['generated_texts'][0]['text'];
                echo $generated_text;
              } else {
                echo "Failed to generate text.";
              }
*/
            }
            
            curl_close($ch);

        }
    }
    $gemini_api = new gemini_api();
}