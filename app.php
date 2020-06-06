<?php

spl_autoload_register();
$propertiesProvider = new \App\Repository\PropertiesProvider();
(new \App\Controller\FileSystemController(
    (new \App\Service\CommandRunner(
        (new \App\Service\FileSystemManager(
            (new \App\Service\FileSystemUtils()),
            (new \App\Service\FtpManager(
                $propertiesProvider
            ))
        )),
        (new \App\Repository\FileSystemConfProvider(
            $propertiesProvider
        ))
    ))
))
    ->run($argv);
