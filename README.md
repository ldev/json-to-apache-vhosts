# JSON to Apache2 vhost
The idea came as I were about to reconfigure Apache2 for LetsEncypt, and needed a simpler way to generate those hundred of lines. Also, inconsistency in the structure irritated me. POW, project born.

Uses file structure of SSL sertificates generated by certbot (letsencrypt) on Debian 8.

PHP choosen because... development time is of essense, as right now my domains are running with invalid SSL certificates :-D

Plans: Not much specific. Perhaps Python and Jinja2?

# Usage
 * Download this little beauty of a script
 * Do a `chmod 777 generated-vhost-configs`. This is not necessary if your user have write previlegies to the directory
 * copy `data.json.sample` to `data.json` and edit to your liking
 * Run it with `php -f parser.php` and enjoy
 
# Example
## The following json.data file...
```JSON
{
    "defaults": {
        "root": "\/var\/www\/parked_domain",
        "enforce_https": true,
        "ssl_parent_domain": false,
        "aliases": false,
        "allow_override_on_root": false,
        "custom_config": false
    },
    "example.tld": {
        "root": "\/var\/www\/example.tld\/www_root\/",
        "ssl_parent_domain": "example.tld"
    },
    "some.subdomain.example.tld": {
        "root": "\/var\/www\/some.subdomain.example.tld\/www_root\/",
        "enforce_https": false,
        "allow_override_on_root": true,
        "ssl_parent_domain": "example.tld",
        "custom_config": "\n<FilesMatch \"\\.phps$\">\n\tSetHandler application\/x-httpd-php-source\n\tRequire all granted\n<\/FilesMatch>"
    },
    "another.tld": {
        "root": "\/var\/www\/another.tld\/www_root\/"
    },
    "yet-another-example.tld": {
        "root": "\/var\/www\/yet-another-example.tld\/www_root\/",
        "ssl_parent_domain": "yet-another-example.tld",
        "aliases": [
            "www.yet-another-example.tld",
            "www-public.yet-another-example.tld"
        ]
    }
}
```

## ... will produce the following files
```
 % for x in generated-vhost-configs/*.conf; do echo "\n# $x:"; cat $x; done;
```

```ApacheConf
# generated-vhost-configs/another.tld.conf:

<VirtualHost *:80>
    ServerName another.tld
    DocumentRoot /var/www/another.tld/www_root/
    ErrorLog ${APACHE_LOG_DIR}/another.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/another.tld_access.log combined
    # Redirects everything to https
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}
</VirtualHost>
<VirtualHost *:443>
    ServerName another.tld
    DocumentRoot /var/www/another.tld/www_root/
    ErrorLog ${APACHE_LOG_DIR}/another.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/another.tld_access.log combined
</VirtualHost>

# generated-vhost-configs/example.tld.conf:

<VirtualHost *:80>
    ServerName example.tld
    DocumentRoot /var/www/example.tld/www_root/
    ErrorLog ${APACHE_LOG_DIR}/example.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/example.tld_access.log combined
    # Redirects everything to https
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}
</VirtualHost>
<VirtualHost *:443>
    ServerName example.tld
    DocumentRoot /var/www/example.tld/www_root/
    ErrorLog ${APACHE_LOG_DIR}/example.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/example.tld_access.log combinedSSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/example.tld/cert.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/example.tld/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/example.tld/fullchain.pem
</VirtualHost>

# generated-vhost-configs/some.subdomain.example.tld.conf:

<VirtualHost *:80>
    ServerName some.subdomain.example.tld
    DocumentRoot /var/www/some.subdomain.example.tld/www_root/
    ErrorLog ${APACHE_LOG_DIR}/some.subdomain.example.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/some.subdomain.example.tld_access.log combined
</VirtualHost>
<VirtualHost *:443>
    ServerName some.subdomain.example.tld
    DocumentRoot /var/www/some.subdomain.example.tld/www_root/
    ErrorLog ${APACHE_LOG_DIR}/some.subdomain.example.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/some.subdomain.example.tld_access.log combinedSSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/example.tld/cert.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/example.tld/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/example.tld/fullchain.pem
</VirtualHost>


<FilesMatch "\.phps$">
	SetHandler application/x-httpd-php-source
	Require all granted
</FilesMatch>

# generated-vhost-configs/yet-another-example.tld.conf:

<VirtualHost *:80>
    ServerName yet-another-example.tld
    DocumentRoot /var/www/yet-another-example.tld/www_root/
    ServerAlias www.yet-another-example.tld www-public.yet-another-example.tld
ErrorLog ${APACHE_LOG_DIR}/yet-another-example.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/yet-another-example.tld_access.log combined
    # Redirects everything to https
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule (.*) https://%{HTTP_HOST}%{REQUEST_URI}
</VirtualHost>
<VirtualHost *:443>
    ServerName yet-another-example.tld
    DocumentRoot /var/www/yet-another-example.tld/www_root/
    ServerAlias www.yet-another-example.tld www-public.yet-another-example.tld
ErrorLog ${APACHE_LOG_DIR}/yet-another-example.tld_error.log
    CustomLog ${APACHE_LOG_DIR}/yet-another-example.tld_access.log combinedSSLEngine on
    SSLCertificateFile    /etc/letsencrypt/live/yet-another-example.tld/cert.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/yet-another-example.tld/privkey.pem
    SSLCertificateChainFile /etc/letsencrypt/live/yet-another-example.tld/fullchain.pem
</VirtualHost>
```


# Liceense
Do whatever you want with this. It is to be concidered public domain.


Regards,
  Jonas H. Lindstad
  Lindstad Development
  ldev.no
