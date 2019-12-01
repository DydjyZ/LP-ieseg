<?php

include "../../server/api/config.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/server/vendor/stripe-php/init.php";

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$login = new Login;
$email = new Email;

$user = $login->getUserInfos($_POST['TOKEN']);
if ($user['result'] == 0) {
    return $result = true;
}

if (!isset($_POST['TOKEN'])) {
    die('Error');
}

if (!isset($_POST['STRIPETOKEN'])) {
    die('Error');
}

if (!isset($_POST['VOUCHER'])) {
    die('Error');
}

if (!isset($_POST['CARDHOLDER'])) {
    die('Error');
}

// je vérifie si le coupon existe avant de continuer
if (!empty($_POST['VOUCHER'])) {
    try {
        $testVoucher = \Stripe\Coupon::retrieve($_POST['VOUCHER']);
        echo "Voucher ok";
    } catch (\Exception $e) {
        echo "Voucher inconnu";
    }
}

$customer = \Stripe\Customer::create([
    'source' => $_POST['STRIPETOKEN'], // ça on le récupère depuis ionic (c'est la création de la CB)
    'email' => $user['EMAIL'],
    'description' => "ID : " . $user['ID'],
]);

// on ajoute la subscription mensuelle
$subscription = \Stripe\Subscription::create([
    'customer' => $customer->id,
    'coupon' => $_POST['VOUCHER'],
    'items' => [
        [
            'plan' => PLAN_MENSUEL,
            'quantity' => 1,
        ],
    ],
]);

echo "<pre>";
print_r($subscription);

$login->setStripeId($_POST['TOKEN'], $subscription->id);

if (!empty($user['PARRAIN'])) {
    // j'offre une semaine premium au parrain
    // ici il faut envoyer un email au parrain pour le prévenir

    $parrainInfos = $login->getUserFromId($user['PARRAIN']);
    if ($parrainInfos['result'] != 0) {

        $login->ID_addPremiumTime($user['PARRAIN'], "1 month");

        $email->sendEmailWithoutTemplate($parrainInfos['EMAIL'], "Hey !<br /><br />Someone subscribe on linkiomatic with your voucher today ! So, you won one premium month.<br /><br />Thanks for promoting us.<br /><br />Have a nice day,<br /><br/>Dylan", "You earn premium time on Linkiomatic !");

        $login->addNumberOfFilleulsSubscribed($user['PARRAIN']);

    }

    // ici email pour moi même pour savoir que qqn a payé
    $email->sendEmailWithoutTemplate("dylantxa@gmail.com", "Hey folk !<br /><br />There is a new payment on Linkiomatic ! Let's celebrate that ! <br/><br /><b>Email :</b> {$user['EMAIL']}<br /><br />Have a nice day,<br /><br/>Me", "New payment on Linkiomatic !");

}
