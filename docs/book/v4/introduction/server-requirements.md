# Server Requirements

For production, we highly recommend a *nix based system.

## Webserver

* Apache >= 2.2 **or** Nginx
* mod_rewrite
* .htaccess support `(AllowOverride All)`

## PHP >= 8.2

Both mod_php and FCGI (FPM) are supported.

## Required Settings and Modules & Extensions

* memory_limit >= 128M
* upload_max_filesize and post_max_size >= 100M (depending on your data)
* mbstring
* CLI SAPI (for Cron Jobs)
* Composer (added to $PATH)

## RDBMS

* MySQL / MariaDB >= 5.5.3

## Recommended extensions

* opcache
* pdo_mysql or mysqli (if using MySQL or MariaDB as RDBMS)
* dom - if working with markup files structure (html, xml, etc)
* simplexml - working with xml files
* gd, exif - if working with images
* zlib, zip, bz2 - if compessing files
* curl (required if APIs are used)
