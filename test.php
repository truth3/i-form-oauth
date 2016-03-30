<?php

    require_once 'zerion_autoload.php';

    $url = "https://SERVER_NAME.iformbuilder.com/exzact/api/oauth/token";
    $client = "XXXXXX";
    $secret = "XXXXXX";

    $token = (new \Iform\Auth\iFormTokenResolver($url, $client, $secret))->getToken();

    var_dump($token);
