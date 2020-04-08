Test Aivo
---

Create a virtual host
---
create a virtual host in Apache

```bash
<VirtualHost *:80>
        ServerName local.aivo.com
        ServerAlias local.aivo.com
        ServerAdmin webmaster@localhost
        DocumentRoot /var/www/aivo_test/src/public
        <Directory /var/www/aivo_test/src/public>
                Options Indexes FollowSymLinks
                Order allow,deny
                Allow from all
                AllowOverride All
                Require all granted
        </Directory>
</VirtualHost>
```

and add next line on your /etc/hosts file

```bash
127.0.0.1     local.aivo.com
```

Install
---

`composer install`


Usage
---

`http://local.aivo.com/api/v1/albums?q=<band-name>`

Utilizando la api de spotify crear un endpoint al que ingresando el nombre de la banda se obtenga un array de toda la discografia, cada disco debe tener este formato:

```bash
[{
    "name": "Album Name",
    "released": "10-10-2010",
     "tracks": 10,
     "cover": {
         "height": 640,
         "width": 640,
         "url": "https://i.scdn.co/image/6c951f3f334e05ffa"
     }
 },
  ...
]
```