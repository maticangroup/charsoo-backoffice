<?php
/**
 * Created by PhpStorm.
 * User: Amirhossein
 * Date: 11/14/2019
 * Time: 7:35 PM
 */
$container->loadFromExtension('twig', [
    // ...
    'globals' => [
        'application_domain' => \Matican\Settings::get('APPLICATION_DOMAIN')
    ],
]);