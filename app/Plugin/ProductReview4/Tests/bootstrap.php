<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// $loader = require __DIR__.'/../../../../vendor/autoload.php';
// $envFile = __DIR__.'/../../../../.env';
// if (file_exists($envFile)) {
//     (new \Symfony\Component\Dotenv\Dotenv())->load($envFile);
// }

$loader = require dirname(__DIR__, 3).'/vendor/autoload.php';

$dotenv = new \Symfony\Component\Dotenv\Dotenv();
$envFiles = [dirname(__DIR__, 3).'/.env.local', dirname(__DIR__, 3).'/.env'];

foreach ($envFiles as $file) {
    if (file_exists($file)) {
        $dotenv->load($file);
    }
}
