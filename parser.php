<?php

    if(is_readable('data.json') === false){
        exit('The file "data.json" does not exist, or is not readable. Check the documentation on Github.' . "\n");
    } 
    $dataset = json_decode(file_get_contents('data.json'), true);

    $defaults = array_shift($dataset);

    $i = 0; # counter
    foreach($dataset as $hostname => $v){
        # Setting up variables for each domain
        $root = isset($v['root']) ? $v['root'] : $defaults['root'];
        $enforce_https = isset($v['enforce_https']) ? $v['enforce_https'] : $defaults['enforce_https'];
        $ssl_parent_domain = isset($v['ssl_parent_domain']) ? $v['ssl_parent_domain'] : $defaults['ssl_parent_domain'];
        $aliases = isset($v['aliases']) ? $v['aliases'] : $defaults['aliases'];
        $custom_config = isset($v['custom_config']) ? $v['custom_config'] : $defaults['custom_config'];
        
        $export = '
    <VirtualHost *:80>
	    ServerName ' . $hostname . '
	    DocumentRoot ' . $root . '

	    ErrorLog ${APACHE_LOG_DIR}/' . $hostname . '_error.log
	    CustomLog ${APACHE_LOG_DIR}/' . $hostname . '_access.log combined';
        if($enforce_https === true){
            $export .= '
	    # Redirects everything to https
	    RewriteEngine On
	    RewriteCond %{HTTPS} off
	    RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}';
        }
        
        $export .= '
    </VirtualHost>
    <VirtualHost *:443>
	    ServerName ' . $hostname . '
	    DocumentRoot ' . $root . '
	    ErrorLog ${APACHE_LOG_DIR}/' . $hostname . '_error.log
	    CustomLog ${APACHE_LOG_DIR}/' . $hostname . '_access.log combined';
	
	    if($ssl_parent_domain !== false){
	        $export .= 'SSLEngine on
        SSLCertificateFile    /etc/letsencrypt/live/' . $ssl_parent_domain . '/cert.pem
        SSLCertificateKeyFile /etc/letsencrypt/live/' . $ssl_parent_domain . '/privkey.pem
        SSLCertificateChainFile /etc/letsencrypt/live/' . $ssl_parent_domain . '/fullchain.pem';
	    }
        
        $export .= '
        </VirtualHost>';

        if($custom_config !== false){
            $export .= "\n" . $custom_config;
        }

        if(file_put_contents('generated-vhost-configs/' . $hostname . '.conf', $export) !== false){
            $i++;
        }
    }
    echo 'finished - wrote ' . (int) $i . ' .conf files';
?>
