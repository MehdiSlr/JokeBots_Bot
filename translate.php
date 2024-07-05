<?php
/**
 * Class GoogleTranslate
 * 
 * A simple class for interacting with Google Translate API to perform text translations.
 * This class uses an unofficial Google Translate API endpoint to request translations.
 */
class GoogleTranslate
{
    /**
     * Translate text from a source language to a target language.
     * 
     * @param string $source The language code of the source language (e.g., 'en' for English).
     * @param string $target The language code of the target language (e.g., 'es' for Spanish).
     * @param string $text The text to be translated.
     * @return string The translated text.
     */
    public static function translate($source, $target, $text) {
        $response 		= self::requestTranslation($source, $target, $text); // Request translation from the Google Translate API
        $translation 	= self::getSentencesFromJSON($response); //// Extract translated sentences from the JSON response
        return $translation;
    }

    /**
     * Request translation from Google Translate API.
     * 
     * @param string $source The source language code.
     * @param string $target The target language code.
     * @param string $text The text to be translated.
     * @return string The JSON response from the API.
     */
    protected static function requestTranslation($source, $target, $text) {
        $url = "https://translate.google.com/translate_a/single?client=at&dt=t&dt=ld&dt=qca&dt=rm&dt=bd&dj=1&hl=es-ES&ie=UTF-8&oe=UTF-8&inputm=2&otf=2&iid=1dd3b944-fa62-4b55-b330-74909a99969e";
        $fields = array(
            'sl' => urlencode($source), // Source language code
            'tl' => urlencode($target), // Target language code
            'q' => urlencode($text) // Text to be translated
        );

        // Build the query string for the POST request
        $fields_string = "";
        foreach($fields as $key=>$value) {
            $fields_string .= $key.'='.$value.'&';
        }
        
        rtrim($fields_string, '&');
        $ch = curl_init(); // Initialize a cURL session
        curl_setopt($ch, CURLOPT_URL, $url); // Set the URL for the request
        curl_setopt($ch, CURLOPT_POST, count($fields)); // Specify that this is a POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string); // Set the POST fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8'); // Set the character encoding
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification (not recommended for production)
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Disable host verification (not recommended for production)
        curl_setopt($ch, CURLOPT_USERAGENT, 'AndroidTranslate/5.3.0.RC02.130475354-53000263 5.1 phone TRANSLATE_OPM5_TEST_1'); // Set a common User-Agent string for the request

        $result = curl_exec($ch); // Execute the cURL request

        curl_close($ch); // Close the cURL session
        return $result; // Return the result of the cURL execution
    }

    /**
     * Extract sentences from the JSON response returned by the Google Translate API.
     * 
     * @param string $json The JSON response from the API.
     * @return string The translated text.
     */
    protected static function getSentencesFromJSON($json) {
        $sentencesArray = json_decode($json, true); // Decode the JSON response into a PHP array
        $sentences = "";
        foreach ($sentencesArray["sentences"] as $s) {
            $sentences .= $s["trans"]; // Concatenate the translated sentences
        }
        return $sentences; // Return the concatenated translated text
    }
}
?>