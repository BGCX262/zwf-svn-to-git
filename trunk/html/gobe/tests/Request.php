<?php

require_once("boot.php");
require_once(GOBE_PATH_ENGINE . "Router.php");
require_once(GOBE_PATH_ENGINE . "Route.php");
require_once(GOBE_PATH_ENGINE . "Request.php");

echo "<pre>";

$router = Gobe_Router::getInstance();

$router->addRoute(array(
	new Gobe_Route('`^/drinks/(\d+).*`i',         '/drinks/?id=$1'),
	new Gobe_Route('`^/drinks/categories/(.*)`i', '/categories/$1', 301),
	new Gobe_Route('`^/recipes/(.*)`i',           '/drinks/$1',     301),
	new Gobe_Route('`^/beverages/(.*)`i',         '/drinks/$1',     301, true)
));


$testPaths = array(
	# Input                           => # Expected result
	"/drinks/123/dinki-dinki"         => array("/drinks/?id=123", null, "Single rewrite"),
	"/recipes/123/dinki-dinki"        => array("/drinks/?id=123", 301, "Double rewrite; status change"),
	"/beverages/123/dinki-dinki"      => array("/drinks/123/dinki-dinki", 301, "Single rewrite, cut short; status change"),
	"/drinks/categories/456/shooters" => array("/categories/456/shooters", 301, "Double rewrite (rule differentiation); status change"),
	"/contact"                        => array("/contact", null, "No rewrite"),
);


$i = 1;
foreach ( $testPaths as $start => $end ) {
	$r = new Gobe_Request($router->getRoutes(), $start);
	$nr = $r->translate();
	$result = $nr->getUrl() == $end[0] && (!isset($end[1]) || $end[1] == $nr->getStatus());
	
	echo "Test #$i: {$end[2]}\n",
		"<blockquote>",
		"Start path:         $start\n",
		"Start request:      ", $r->getStatus(false), ": ", $r->getUrl(false), "\n",
		"Translated request: ", $nr->getStatus(), ": ", $nr->getUrl(), "\n";
	
	if ( !$result ) {
		echo "Expected request:   {$end[1]}: {$end[0]}\n";
	}
	
	echo "Result:             ", ($result  
				? "Success"
				: "<font color='red'>Failure</font>"
		), "\n";
	
	echo "</blockquote>";
	++$i;
}

