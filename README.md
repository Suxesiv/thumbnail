# Suxesiv Thumbnail

Creates just thumbnails and nothing else...

Please make sure you use at least PHP 7 and that Imagick is available.

```php
<?php

use AppBundle\Service\Thumbnail;

require_once __DIR__ . '/Thumbnail.php';

$thumb = new Thumbnail(__DIR__);

$thumbSrc = $thumb
    ->setSrc('http://bit.ly/2whyDXd')
    ->setHeight(100)
    ->setWidth(100)
    ->create();
?>

<img src="<?= $thumbSrc ?>">
```