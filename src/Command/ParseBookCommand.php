<?php

namespace App\Command;

use App\Service\ParseBookService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:parse-book',
    description: 'Parse book from file and save to database'
)]
class ParseBookCommand extends Command
{
    public function __construct(
        private readonly string $dataLocation,
        private readonly Filesystem $filesystem,
        private readonly ParseBookService $parseBookService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Parse book from file and save to database');

        if (!$this->filesystem->exists($this->dataLocation)) {
            $io->error('The specified JSON file does not exist.');
            return Command::FAILURE;
        }

        $data = file_get_contents($this->dataLocation);

        foreach ($this->parseBookService->process($data) as $bookDto) {
            // todo
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
