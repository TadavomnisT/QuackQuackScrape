<?php


// ============ USAGE EXAMPLES FOR DUCKDUCKGO CLASS ============

require_once "DuckDuckGo.php";

// Example 1: Basic usage without proxy
$ddg = new DuckDuckGo;
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);

// Example 2: Using HTTP proxy
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('http://proxy.example.com:8080')->enableProxy();
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);
*/

// Example 3: Using HTTP proxy with authentication
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('http://username:password@proxy.example.com:8080')->enableProxy();
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);
*/

// Example 4: Using SOCKS5 proxy (Tor)
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('socks5://127.0.0.1:9050')->enableProxy();
$results = $ddg->searchUsingDuckduckgo("cute femboys");
var_dump($results);
*/

// Example 5: Search multiple pages
/*
$ddg = new DuckDuckGo;
$results = $ddg->searchMultiplePages("quantum computing", 3);
var_dump($results);
*/

// Example 6: Search with offset
/*
$ddg = new DuckDuckGo;
$results = $ddg->searchWithOffset("php programming", 30);
var_dump($results);
*/

// Example 7: Temporarily disable proxy
/*
$ddg = new DuckDuckGo;
$ddg->setProxy('http://proxy.example.com:8080')->enableProxy();
$results1 = $ddg->searchUsingDuckduckgo("test query"); // uses proxy
$ddg->disableProxy();
$results2 = $ddg->searchUsingDuckduckgo("another query"); // no proxy
var_dump($results2);
*/

?>
