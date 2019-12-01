<?php
require '../../server/api/config.php';

$_login = new Login;

if (!isset($_GET['accounts-server']) || !isset($_GET['code'])) die ("Server error, please contact us (err 1)");
if (!isset($_GET['state'])) die ("Server error, please contact us (err 3)");

/**
 * ON RÉCUPÈRE LE REFRESH TOKEN
 */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$_GET['accounts-server']}/oauth/v2/token");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

curl_setopt($ch, CURLOPT_POSTFIELDS, array(
    'grant_type' => 'authorization_code',
    'client_id' => ZOHO_CLIENT,
    'client_secret' => ZOHO_SECRET,
    'code' => $_GET['code'],
    'redirect_uri' => "https://linkiomatic.com/web/user/zoho.php",
));

$data = json_decode(curl_exec($ch), true);
curl_close($ch);


if (!isset($data['refresh_token'])) die ("Server error, please contact us (err 2)");

// je save le refresh_token dans la BDD
$_login->saveApiKey($_GET['state'], [ 'ApiKey' => $data['refresh_token'], 'CRM' => 'ZOHO' ]);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Linkiomatic</title>
</head>
<body>

Zoho is now set-up for you, please reload the LinkedIn page !
    
</body>
</html>