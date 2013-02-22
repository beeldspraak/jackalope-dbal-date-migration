#!/usr/bin/env php
<?php

if (!$loader = @include __DIR__.'/../vendor/autoload.php') {
    die('You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -s http://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL);
}

$configFile = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cli-config.php';
if (!file_exists($configFile) || !is_readable($configFile)) {
    die('You must create a cli-config.php in the root file to be able to run this command. See cli-config.php.dist for an example'.PHP_EOL);
}

require $configFile;

if (!isset($dbConfig) || !is_array($dbConfig)) {
    die('You should define a $dbConfig variable in cli-config.php'.PHP_EOL);
}

$missingKeys = array();
foreach (array('hostname', 'username', 'password', 'database') as $key) {
    if (!isset($dbConfig[$key])) {
        $missingKeys[] = $key;
    }
}

if (count($missingKeys)) {
    die('You are missing the following keys in $dbConfig: '. join(', ', $missingKeys) . PHP_EOL);
}

$connection = \Doctrine\DBAL\DriverManager::getConnection(array(
    'driver'    => isset($dbConfig['driver']) ? $dbConfig['driver'] : 'pdo_mysql',
    'host'      => $dbConfig['hostname'],
    'user'      => $dbConfig['username'],
    'password'  => $dbConfig['password'],
    'dbname'    => $dbConfig['database'],
));

$phpcr_user = isset($phpcr['user']) ? $phpcr['user'] : $dbConfig['username'];
$phpcr_pass = isset($phpcr['pass']) ? $phpcr['pass'] : $dbConfig['password'];
$workspace  = isset($phpcr['workspace']) ? $phpcr['workspace'] : 'default';

$repository = Jackalope\RepositoryFactoryDoctrineDBAL::getRepository(array('jackalope.doctrine_dbal_connection' => $connection));
$session = $repository->login(new \PHPCR\SimpleCredentials($phpcr_user, $phpcr_pass), $workspace);

$cli = new \Symfony\Component\Console\Application('Jackalope Doctrine DBAL Migration');
$cli->setCatchExceptions(true);
$cli->setHelperSet(new \Symfony\Component\Console\Helper\HelperSet(array(
    'session'   => new \PHPCR\Util\Console\Helper\PhpcrHelper($session),
)));
$cli->addCommands(array(
    new MigrateDateProperties(),
));

$cli->run();