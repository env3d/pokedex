# pokedex

A basic pokedex app to highlight the use of cookies, server logs, etc.

You may need to add .htaccess file as follows:

```shell
echo 'DirectoryIndex index.php' > .htaccess
```

Also need to add your own openai api key to the .env file:

```php
<?php
$yourApiKey = 'Your openai api key';
?>
```