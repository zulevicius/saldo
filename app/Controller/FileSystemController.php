<?php

namespace App\Controller;


use App\Service\CommandRunner;

class FileSystemController
{
    /**
     * @var CommandRunner
     */
    private $commandRunner;

    /**
     * @param CommandRunner $commandRunner
     */
    public function __construct(CommandRunner $commandRunner)
    {
        $this->commandRunner = $commandRunner;
    }

    /**
     * @param $args array
     */
    public function run(array $args): void
    {
        $this->commandRunner->execute($args);
    }
}