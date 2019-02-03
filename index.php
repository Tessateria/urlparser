<?php
$shortopts = 'u::';
$longopts = ['url::'];
$options = getopt($shortopts, $longopts);

if ($options['u']) {
    $url = $options['u'];
} elseif ($options['url']) {
    $url = $options['url'];
} elseif ($argv[1]) {
    $url = $argv[1];
}

if (!(filter_var($url, FILTER_VALIDATE_URL))) {
    echo "Не валидный URL \n";
} else {

    $domainZonesFile = file_get_contents('tlds.txt');
    $domainZones = explode(PHP_EOL, $domainZonesFile);
    $domainSltListFile = file_get_contents('slds.txt');
    $domainSlt = explode(PHP_EOL, $domainSltListFile);

    $parsedHalf = (parse_url($url));
    $scheme = $parsedHalf['scheme'];

    if (($scheme !== "http") & ($scheme !== "https")) {
        echo "Не валидный протокол URL \n";
    } else {

        $parsedHalf = (parse_url($url));

        $parsedHost = explode('.', $parsedHalf['host']);

        $topDomain = array_pop($parsedHost);
        if (in_array(strtoupper($topDomain), $domainZones)) {
            $domainPart["tld"] = '.' . $topDomain;
        }

        $topDomain = array_pop($parsedHost);
        if (in_array($topDomain, $domainSlt)) {
            $domainPart["sld"] = '.' . $topDomain . ($domainPart["tld"]);
            $domainPart["domain"] = array_pop($parsedHost) . ($domainPart["sld"]);
        } else {
            $domainPart["domain"] = $topDomain . ($domainPart["tld"]);
        }

        if (count($parsedHost) > 0) {
            $domainPart["subdomain"] = implode('.', $parsedHost);
        }

        $resultArray = $parsedHalf + $domainPart;

        if (($parsedHalf["path"]) !== null) {
            $parsedPath = explode('.', $parsedHalf["path"]);
            if (count($parsedPath) >= 2) {
                $extension["extension"] = end($parsedPath);
                $resultArray += $extension;
            }
        }
        print_r($resultArray);

        if (($parsedHalf["query"]) !== null) {
            $allQuery = ($parsedHalf["query"]);
            $parsQuAll = explode('&', $allQuery);

            $Queries = array();
            foreach ($parsQuAll as $value) {
                parse_str($value, $parsedQuery);
                $Queries += $parsedQuery;
            }

            print_r($Queries);

        }

    }
}
