<?php

namespace App\Command;

use App\Entity\Battle;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-battles',
    description: 'Loads initial battle data',
)]
class LoadBattlesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $battles = [
            ["Deep Purple", "Black Sabbath"],
            ["Renaud", "Johnny Hallyday"],
            ["Bob Sinclar", "David Guetta"],
            ["Maître Gims", "Jul"],
            ["Alliage", "2Be3"],
            ["Garou", "Céline Dion"],
            ["Jean-Michel Jarre", "Jean-Jacques Goldman"],

            ["Beyblade", "Bakugan"],
            ["Marcelino", "Les Malheurs de Sophie"],
            ["Albator", "Ulysse 31"],
            ["Dragon Ball", "Les Chevaliers du Zodiaque"],
            ["Les Mystérieuses Cités d’Or", "Il Était Une Fois… la Vie"],
            ["Nicky Larson", "Cobra"],
            ["Action Man", "G.I. Joe"],
            ["Totally Spies", "Winx Club"],

            ["Cauchemar en Cuisine (UK)", "Cauchemar en Cuisine (FR)"],
            ["Caméra Café", "H"],
            ["Les Inconnus", "Les Nuls"],
            ["Groland", "Les Guignols"],
            ["Questions pour un Champion", "Des Chiffres et des Lettres"],
            ["Tournez Manège", "L’Amour est dans le Pré"],
            ["Un Gars, une Fille", "Scènes de Ménage"],
            ["Intervilles", "Fort Boyard"],
            ["Takeshi’s Castle", "Total Wipeout"],

            ["Papa Schultz", "Les Têtes Brûlées"],
            ["Columbo", "Derrick"],
            ["Zorro", "Thierry la Fronde"],
            ["Walker, Texas Ranger", "L’Agence Tous Risques"],
            ["Buffy contre les Vampires", "Charmed"],
            ["Supercopter", "K2000"],
            ["K2000", "Tonnerre Mécanique"],
            ["Alice Nevers, le Juge est une Femme", "Docteur Quinn, Femme Médecin"],

            ["Inglourious Basterds", "Django Unchained"],
            ["La Grande Vadrouille", "La 7e Compagnie"],
            ["Inception", "Tenet"],
            ["Léon", "L’Immortel"],
            ["Taxi", "Le Transporteur"],
            ["Mission: Impossible", "Jason Bourne"],
            ["Alien", "Predator"],
            ["Alien", "Aliens"],
            ["L’Aile ou la Cuisse", "La Soupe aux Choux"],
            ["Don Camillo", "La Vache et le Prisonnier"],
            ["Rocky", "Rambo"],
            ["Les Ripoux", "Inspecteur la Bavure"],
            ["Superman", "Spider-Man"],
            ["V for Vendetta", "1984"],

            ["Mario", "Sonic"],
            ["Factorio", "Satisfactory"],
            ["Crash Bandicoot", "Spyro the Dragon"],
            ["Tropico", "Anno"],
            ["Metroid", "Castlevania"],
            ["F-Zero", "Wipeout"],
            ["Civilization", "Europa Universalis"],
            ["Forza Motorsport", "Gran Turismo"],
            ["Call of Duty", "Battlefield"],
            ["FIFA", "Pro Evolution Soccer"],
            ["Gears of War", "Mass Effect"],
            ["Need for Speed", "Burnout"],
            ["Quake", "Unreal Tournament"],
            ["Pinball", "Démineur"],
            ["The Elder Scrolls V: Skyrim", "The Elder Scrolls IV: Oblivion"],
            ["Tony Hawk’s Pro Skater", "SSX"],
            ["Terraria", "Minecraft"],
            ["God of War (1/2/3)", "God of War (4/5)"],
            ["Guitar Hero", "Rock Band"],
            ["Diablo", "Titan Quest"],
            ["Alex Kidd", "Conker"],
            ["Assassin’s Creed I/II/Brotherhood/Revelations/III/Black Flag", "Assassin’s Creed Unity — Shadows"],
            ["Superman", "Spider-Man"]
            ];

        foreach ($battles as [$optionA, $optionB]) {
            $battle = new Battle();
            $battle->setOptionA($optionA);
            $battle->setOptionB($optionB);
            $this->entityManager->persist($battle);
        }

        $this->entityManager->flush();

        $io->success('Battles loaded successfully!');

        return Command::SUCCESS;
    }
}
