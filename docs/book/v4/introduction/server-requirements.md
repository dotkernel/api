# Server Requirements

For **production**, we highly recommend a *nix based system.
Either a bare metal server or a LXC container, running [Almalinux 9](https://almalinux.org/get-almalinux/)

For **development** we highly recommend to use either Almalinux 9 or [WSL](https://github.com/dotkernel/development/blob/main/wsl/os/almalinux9/README.md)

## Webservers

* Apache >= 2.4 with mod_rewrite and .htaccess support `(AllowOverride All)`

 **OR**

 * NGINX

## PHP >= 8.2

It is recommended to run PHP as FPM application served by Apache or Nginx

## Required PHP Settings and Modules & Extensions

* memory_limit >= 128M
* upload_max_filesize and post_max_size >= 100M (depending on your data)
* mbstring
* CLI SAPI (for Cron Jobs)
* Composer (added to $PATH)

## RDBMS

* MariaDB >= 10.11 LTS

## Recommended extensions

* opcache
* pdo_mysql or mysqli (if using MySQL or MariaDB as RDBMS)
* dom - if working with markup files structure (html, xml, etc)
* simplexml - working with xml files
* gd, exif - if working with images
* zlib, zip, bz2 - if compessing files
* curl (required if APIs are used)
