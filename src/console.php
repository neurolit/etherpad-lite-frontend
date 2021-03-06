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
    ->setDescription('Lists all pads from etherpad-lite')
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
    ->setDescription('Returns text of a pad from etherpad-lite')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
       $padID = $input->getArgument('padID');
       $output->writeln($app['etherpad']->getText($padID));
   })
;

$console
    ->register('getLastEdited')
    ->setDefinition(array(
        new InputArgument('padID', InputArgument::REQUIRED, 'Pad ID')
    ))
    ->setDescription('Returns the timestamp of the last revision of the pad from etherpad-lite')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
       $padID = $input->getArgument('padID');
       $output->writeln($app['etherpad']->getLastEdited($padID));
   })
;

$console
    ->register('deletePad')
    ->setDefinition(array(
        new InputArgument('padID', InputArgument::REQUIRED, 'Pad ID')
    ))
    ->setDescription('Deletes a pad from etherpad-lite')
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

$console
    ->register('setPassword')
    ->setDefinition(array(
        new InputArgument('padID', InputArgument::REQUIRED, 'Pad ID'),
        new InputArgument('newPassword', InputArgument::REQUIRED, 'Pad new password')
    ))
    ->setDescription('Sets a new password for an already-protected pad')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
       $app['etherpad']->setPassword($input->getArgument('padID'),$input->getArgument('newPassword'));
       $output->writeln("Password updated!");
    })
;

return $console;
