<?php

use src\Decorator\DecoratorManager;

$manager = new DecoratorManager('ya.ru', 'user', 'pass');
$manager->setLogger($logger);
$manager->getResponse();