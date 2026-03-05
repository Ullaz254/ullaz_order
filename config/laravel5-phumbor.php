<?php

return array(
	// the path to your Thumbor server
	// if it runs on a port other than 80, be sure to include it
	'server' => env('APP_ENV') === 'local' ? 'http://images.drivarr.com' : 'https://images.royoorders.com',
	
	// your Thumbor server's secret key
	'key' => '',
);