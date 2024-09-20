### How to install

1. Deploy api to your favorite server www. Copy this package or clone from github:
   https://github.com/Masakrancja/ApiVacations.git

2. If you use apache2 as server www - check if you have enable "rewrite" and "headers" modules

3. Edit file `.htaccess` for your preferences

4. Run `composer install`

5. Set privileges 777 to `Log` directory
    `chmod 777 ./Log`

6. Copy `Src/Config/config_sample.php` to `Src/Config/config.php` and fill it correct database credentials

6. Instructions:

- Api routes: `docs.txt`
- phpdoc: `docs/`

7. If you don't want install or deploy - try it followed link:
   https://api.vacations.kefas.ovh/
