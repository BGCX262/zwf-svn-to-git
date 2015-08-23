<?php

include_gobe_module("web.analytics");

set_path('home'      , SITE_BASEURL);
set_path('images'    , PATH_IMAGES);
set_path('css'       , PATH_CSS);
set_path('javascript', PATH_JAVASCRIPT);

/* Globally available variables */
add_gobe_variable('site-protocol', SITE_PROTOCOL);

Analytics::blockServer("dev." . DOMAIN_WEB);
Analytics::blockServer("127.0.0.1");
Analytics::blockUser("127.0.0.1");