<?php

namespace App\Command;

use App\Service\ChatGPT\CipherforgeSuite;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'gpt:run',
    description: '',
)]
class ChatGPTRunCommand extends Command
{
    public function __construct(private CipherforgeSuite $cipherforgeSuite)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('mode', InputArgument::REQUIRED, 'encode or decode')
            ->addArgument('text', InputArgument::REQUIRED, 'The text to encode or decode')
            ->addOption('stack', null, InputOption::VALUE_REQUIRED, 'Custom stack override')
            ->addOption('key', null, InputOption::VALUE_OPTIONAL, 'any little word');
        
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $mode = strtolower($input->getArgument('mode'));
        $text = $input->getArgument('text');
        $stack = $input->getOption('stack');
        //$method = $input->getOption('method');
        
        if ($stack) {
            $this->cipherforgeSuite->setStack(array_map('trim', explode(',', $stack)));
            $output->writeln("<info>Using custom stack: {$stack}</info>");
        }

        if ($mode === 'encode') {
             $result = $this->cipherforgeSuite->stack($text, 1);
         } else {
             $result = $this->nativex->stack($text, -1);
        }

        

        $output->writeln($result);
        return Command::SUCCESS;
    }
}
