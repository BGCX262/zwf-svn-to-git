<?php

$r = Gobe_Router::getInstance();

$r->addRoute(array(
	new Gobe_Route("`^/contact/1-800-ZEBRAKICK/(.*)$`i", '/contact/$1', 200)
));

