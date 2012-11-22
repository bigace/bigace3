<?php
return array (
  'database' => 
  array (
    'type'    => 'PDO_Mysql',
    'host'    => 'localhost',
    'name'    => 'bigace_unit_test',
    'user'    => 'bigace_unit_test',
    'pass'    => 'unitTests',
    'prefix'  => 'cms_',
    'charset' => 'utf8',
  ),
  'community' => 
  array (
    'id'       => 999,
    'email'    => 'test@example.com',
    'host'     => 'localhost',
    'user'     => 'admin',
    'pass'     => 'admin',
    'language' => 'en'
  ),
  'ssl'     => false,
  'rewrite' => false,
);
