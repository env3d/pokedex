<?php
require 'vendor/autoload.php';
require '.env';

ini_set('session.cookie_samesite', 'None');
ini_set('session.cookie_secure', 'True');

function reload()
{
    // Get the current URL
    $current_url = $_SERVER['REQUEST_URI'];

    // Remove the query string
    $parsed_url = parse_url($current_url);
    $redirect_url = $parsed_url['path'];

    // Redirect to the same path without parameters
    header("Location: $redirect_url");
    exit();
}

function show_login()
{
    if (!isset($_SESSION['email']) || !isset($_SESSION['name']) || !isset($_SESSION['pic'])) {
        print('<script src="https://accounts.google.com/gsi/client" async></script>
<script>
    function handleCredentialResponse(response) {
        console.log("Encoded JWT ID token: " + response.credential);
        const jwt = response.credential;        
        const payload = JSON.parse(atob(jwt.split(".")[1]))
        fetch(`${location.href.split("?")[0]}?email=${payload["email"]}&pic=${payload["picture"]}&name=${payload["name"]}`)
        .then( () => location.reload());            
    }
    window.addEventListener("load", function () {
        google.accounts.id.initialize({
            client_id: "107256413984-r8i468m63oe3afq55gc5aoto78voelpi.apps.googleusercontent.com",
            callback: handleCredentialResponse
        });
        google.accounts.id.renderButton(
            document.getElementById("loginButton"),
            { theme: "outline", size: "large" }  // customization attributes
        );
        google.accounts.id.prompt(); // also display the One Tap dialog
    });
</script>
');
    } else {
        $name = $_SESSION['name'];
        $pic = $_SESSION['pic'];
        print("
<script>
loginButton.innerHTML = '<img src=\'$pic\'/>';
</script>
");
    }
}

function paywall() {
    if (!isset($_SESSION['email'])) {
        show_login();
        print("<script>
        document.querySelector('.description').innerHTML = `Please login to see your horoscope`;
        document.querySelector('#restart').style.display = 'none';
        </script>");
        exit;
    }
}

session_start();

if (isset($_GET['image'])) {

    // Set the content type to PNG (or JPEG, GIF, etc. based on the image type)
    header('Content-Type: image/png');

    $image_id = $_GET['image'];
    
    // Load the image from the file
    if (isset($_SESSION['visited']) && count($_SESSION['visited']) > 0 ) {
        $image_id = end($_SESSION['visited']);
    }
    $image = imagecreatefrompng("images/$image_id.png");

    $text_color = imagecolorallocate($image, 255, 255, 255); // black text

    if (isset($_SESSION['name'])) {
        imagestring($image, 5, 5, 5, $_SESSION['name'], $text_color);
        imagestring($image, 15, 5, 5, "Last pokemon you picked:", $text_color);
    }
    // Output the image as PNG
    imagepng($image);
    
    // Free up memory
    imagedestroy($image);
    exit;   
}

if (isset($_GET['restart'])) {
    $_SESSION['visited'] = array();
    reload();
}

if (isset($_GET['email']) && isset($_GET['pic']) && isset($_GET['name'])) {
    $_SESSION['email'] = $_GET['email'];
    $_SESSION['pic'] = $_GET['pic'];
    $_SESSION['name'] = $_GET['name'];
    exit();
}

if (isset($_SESSION['visited']) && count($_SESSION['visited']) >= 5) {

    include("horoscope.html");
    flush();

    paywall();
    
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

        $response = preg_replace("/\n/", "<br/>", $result->choices[0]->message->content);
    }

    print("<script>document.querySelector('.description').innerHTML = `$response`;</script>");
    exit;
}

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    if (!isset($_SESSION['visited'])) $_SESSION['visited'] = array();
    array_push($_SESSION['visited'], $id);

    $html_string = file_get_contents("pokemon/$id.html");
    $html_string = preg_replace("/(const pokemon_id =).*/", "$1 $id;", $html_string);
    $html_string = preg_replace("/\.\.\//", "", $html_string);
    print($html_string);
} else {
    $index_html = file_get_contents('index.html');
    print($index_html);
}

show_login();