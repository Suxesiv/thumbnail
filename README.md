# Suxesiv Thumbnail

Creates just thumbnails and nothing else...

Please make sure you use at least PHP 7 and that Imagick is available.

```php
<?php

use Suxesiv\Thumbnail;

require_once __DIR__ . '/Thumbnail.php';

$thumb = new Thumbnail(__DIR__);

$thumbSrc = $thumb
    ->setSrc('http://bit.ly/2whyDXd') // Works with remote or local files
    ->setHeight(100)
    ->setWidth(100)
    ->create();
?>

<img src="<?= $thumbSrc ?>">
```

If you don't like the thumbnails try a diffrent mode:

```php
<?php

use Suxesiv\Thumbnail;

require_once __DIR__ . '/Thumbnail.php';

$thumb = new Thumbnail(__DIR__);

$thumbSrc = $thumb
    ->setSrc('http://bit.ly/2whyDXd') // Works with remote or local files
    ->setHeight(100)
    ->setWidth(100)
    ->setMode(Thumbnail::MODE_FILLED)
    ->create();
?>

<img src="<?= $thumbSrc ?>">
```