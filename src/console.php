<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

$console = new Application('Etherpad-Lite Frontend', '1.2.0');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console
    ->register('listAllPads')
    ->setDefinition(array())
    ->setDescription('List all pads from etherpad-lite')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
       $pads = $app['etherpad']->listAllPads();
       foreach($pads as $pad) {
          $output->writeln($pad);
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

$console
    ->register('deletePad')
    ->setDefinition(array(
        new InputArgument('padID', InputArgument::REQUIRED, 'Pad ID')
    ))
    ->setDescription('Delete a pad from etherpad-lite')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app, $console) {
       $dialog = $console->getHelperSet()->get('dialog');
       $padID = $input->getArgument('padID');
       if (!$dialog->askConfirmation(
               $output,
               '<question>Are you sure to delete the pad '.$padID.' (y/N)? </question>',
               false
          )) {
          return;
       }
       if (!$dialog->askConfirmation(
               $output,
               '<question>Are you REALLY sure to delete the pad '.$padID.' (y/N)? </question>',
               false
          )) {
          return;
       }
       $app['etherpad']->deletePad($padID);
       $output->writeln("$padID deleted!");
    })
;

return $console;
