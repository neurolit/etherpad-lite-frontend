<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('Etherpad-Lite Frontend', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console
    ->register('listAllPads')
    ->setDefinition(array())
    ->setDescription('List all pads from etherpad-lite')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
       $pads = $app['etherpad']->listAllPads();
       foreach($pads as $pad) {
          print "$pad\n";
       }
    })
;

$console
    ->register('getText')
    ->setDefinition(array(
        new InputArgument('padID', InputArgument::REQUIRED, 'Pad ID')
    ))
    ->setDescription('Return text of a pad from etherpad-lite')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
       $padID = $input->getArgument('padID');
       $output->writeln($app['etherpad']->getText($padID));
    })
;

return $console;
