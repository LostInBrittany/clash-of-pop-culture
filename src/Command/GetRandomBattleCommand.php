<?php

namespace App\Command;

use App\Repository\BattleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:get-random-battle',
    description: 'Fetches a random battle from the database',
)]
class GetRandomBattleCommand extends Command
{
    public function __construct(
        private BattleRepository $battleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $count = $this->battleRepository->count([]);

        if ($count === 0) {
            $io->error('No battles found in the database.');
            return Command::FAILURE;
        }

        $offset = random_int(0, $count - 1);
        $results = $this->battleRepository->findBy([], null, 1, $offset);
        $battle = $results[0] ?? null;

        if (!$battle) {
            $io->error('Could not fetch a battle.');
            return Command::FAILURE;
        }

        $io->section('Random Battle');
        $io->text(sprintf('<info>%s</info> vs <info>%s</info>', $battle->getOptionA(), $battle->getOptionB()));

        return Command::SUCCESS;
    }
}
