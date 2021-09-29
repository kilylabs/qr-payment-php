# QR-PAYMENT-PHP
Библиотека для генерации QR-кодов оплаты (с банковскими реквизитами), соответствующая стандарту [ГОСТ Р 56042—2014](https://docs.cntd.ru/document/1200110981) для PHP 7.4+.
Эти коды принимаются почти всеми банками (включая Сбербанк) и существенно упрощают жизнь обычными пользователям, которым не нужно вбивать реквизиты вручную.

Установка
------------

Рекомендуемый способ установки через
[Composer](http://getcomposer.org):

```
$ composer require kilylabs/qr-payment-php
```

Использование
-----
#### Пример использования
```php
<?php

require __DIR__.'/vendor/autoload.php';

use Kily\Payment\QR\Gost;
use Kily\Payment\QR\Exception as QRException;

$g = new Gost();

$g->setThrowExceptions(true); // Бросать исключения (поведение по-умолчанию)
$g->setValidateOnSet(false); // Отключить валидацию при уcтановке значения (поведение по-умолчанию)

var_dump($g->listRequired());
// выводится список обязательных атрибутов
//var_dump($g->listAdditional());
// выводится список дополнительных атрибутов
//var_dump($g->listOther());
// выводится список других атрибутов

$g->Name = 'ИП Богданов Александр Сергеевич';
$g->PersonalAcc = '40802810700020000317';
$g->BankName = 'ОАО АКБ «АВАНГАРД»';
$g->BIC = '044525201';
$g->CorrespAcc = '30101810000000000201';

try {
    $g->validate();

    echo $g->generate();
    // выводит: ST00012|Name=ИП Богданов Александр Сергеевич|PersonalAcc=40802810700020000317|BankName=ОАО АКБ «АВАНГАРД»|BIC=044525201|CorrespAcc=30101810000000000201
    echo $g->render();
    // выводит QR-код в бинарном формате (PNG)
    echo $g->render(false,[
        'imageBase64'=>true,
    ]);
    // выводит изображение в base64 (inline)
    $g->render("qr.png");
    // сохраняет QR-код в файл
} catch(QRException $e) {
    // something went wrong
    throw $e;
}
```
TODO
-----
- сделать генерацию Aztec и DataMatrix кодов (согласно стандарту ГОСТ)
- ~сделать список доступных полей~
- добавить другие кодировки (win1251, koi8-r) для сокращения размера
