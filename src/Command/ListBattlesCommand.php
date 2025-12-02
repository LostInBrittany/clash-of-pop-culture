<?php

namespace App\Command;

use App\Repository\BattleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-battles',
    description: 'Lists all battles in the database',
)]
class ListBattlesCommand extends Command
{
    public function __construct(
        private BattleRepository $battleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $battles = $this->battleRepository->findAll();
        $count = count($battles);

        if ($count === 0) {
            $io->warning('No battles found in the database.');
            return Command::SUCCESS;
        }

        $io->section(sprintf('Found %d Battles', $count));

        $rows = [];
        foreach ($battles as $battle) {
            $rows[] = [$battle->getId(), $battle->getOptionA(), $battle->getOptionB()];
        }

        $io->table(['ID', 'Option A', 'Option B'], $rows);

        return Command::SUCCESS;
    }
}
