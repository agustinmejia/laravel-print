<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

## Acerca Laravel-Print

Servidor local para impresión en impresoras pos.

## Instrucciones

```
composer install
cp .env.example .env
php artisan printer:install
```

Para impresión de prueba se debe hacer una petición <b>POST</b> a la ruta <i><b>/api/test</b></i> con los siguientes parámetros:

```
{
    "platform": "windows",
    "printer_name": "POS001",
}
```

Para imprimir se debe hacer una petición <b>POST</b> a la ruta <i><b>/api/print</b></i> con los siguientes parámetros:

```
{
    "platform": "windows",
    "printer_name": "POS001",
    "company_name": "La Colmena",
    "sale_number": "0001",
    "sale_type": "mesa",
    "table_number": 1,
    "discount": "0",
    "observations": "",
    "print_kitchen_tickets": true,
    "details": [
        {
            "quantity": 1,
            "product": "Product 1",
            "total": "10"
        },
        ...
    ]
}
```

## Imprimir logo del negocio
Para que se imprima el logo del negocio en la parte superio del recibo, se debe agregar una imagen <b>logo.png</b> en el directorio <b>public/img/</b>.
<br><br>
<i><b>NOTA: </b> la imagen debe tener una dimensión de 180x180 px a 200x200 px.</i>