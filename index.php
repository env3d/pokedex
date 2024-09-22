<?php
require 'vendor/autoload.php';
require '.env';

session_start();

if (isset($_SESSION['visited']) && count($_SESSION['visited']) > 5) {
     
    include("horoscope.html");
    flush();

    $visited_ids = implode(", ", array_slice($_SESSION['visited'], -5));

    $client = OpenAI::client($yourApiKey);
    
    $response = '';
    if (strlen($response) == 0) {        
        $prompt = "Given I like these pokemons ids: $visited_ids.  What kind of trainer am i?";

        $result = $client->chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                [
                    'role' => 'system', 
                    'content' => 'You are a helpful assistant providing personailty recommendations based on pokemon preferences'
                ],
                [
                    'role' => 'user', 
                    'content' => $prompt
                ],
            ],
        ]);
        
        $response = preg_replace("/\n/","<br/>",$result->choices[0]->message->content);    
    }

    print("<script>document.querySelector('.description').innerHTML = `$response`;</script>");
    exit;
}

if (isset($_GET['pokemon'])) {

    $id = $_GET['pokemon'];

    if (!isset($_SESSION['visited'])) $_SESSION['visited'] = array();
    array_push($_SESSION['visited'], $id);    
    
    $html_string = file_get_contents("pokemon/$id.html");
    $html_string = preg_replace("/(const pokemon_id =).*/", "$1 $id;", $html_string);
    $html_string = preg_replace("/\.\.\//", "", $html_string);
    print($html_string);          

} else {
    $index_html = file_get_contents('index.html');
    print( $index_html );    
}

?>