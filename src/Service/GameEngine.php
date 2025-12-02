<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\VoteChoice;

use Psr\Cache\CacheItemPoolInterface;

class GameEngine
{
    private const CYCLE_DURATION = 15;
    private const VOTE_DURATION = 10;

    private array $battles = [
        ['Beyblade', 'Bakugan'],
        ['Marcelino', 'Les Malheurs de Sophie'],
        ['Albator', 'Ulysse 31'],
        ['Dragon Ball', 'Les Chevaliers du Zodiaque'],
        ['Les Mystérieuses Cités d’Or', 'Il Était Une Fois… la Vie'],
        ['Nicky Larson', 'Cobra'],
        ['Action Man', 'G.I. Joe'],
        ['Totally Spies', 'Winx Club'],
    ];

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly int $now = 0, // Allows time injection for testing
    ) {}

    public function vote(VoteChoice $choice): void
    {
        $battleId = $this->getCurrentBattleId();
        $key = 'votes_' . $battleId;
        
        $item = $this->cache->getItem($key);
        $votes = $item->isHit() ? $item->get() : ['A' => 0, 'B' => 0];
        
        $votes[$choice->value]++;
        
        $item->set($votes);
        $item->expiresAfter(3600); // Keep for 1 hour
        $this->cache->save($item);

        error_log(sprintf('Vote received for %s in battle %s', $choice->value, $battleId));
    }

    public function getState(): array
    {
        $timestamp = $this->now ?: time();
        $cycleTime = $timestamp % self::CYCLE_DURATION;
        
        // Deterministic battle selection based on 10s window
        $battleIndex = $this->getCurrentBattleIndex();
        [$optionA, $optionB] = $this->battles[$battleIndex];

        $phase = match(true) {
            $cycleTime < self::VOTE_DURATION => 'VOTE',
            default => 'RESULT',
        };

        // Calculate real results
        $battleId = $this->getCurrentBattleId();
        $key = 'votes_' . $battleId;
        
        $item = $this->cache->getItem($key);
        $votes = $item->isHit() ? $item->get() : ['A' => 0, 'B' => 0];
        
        $total = $votes['A'] + $votes['B'];
        if ($total === 0) {
            $percentA = 50;
            $percentB = 50;
        } else {
            $percentA = (int) round(($votes['A'] / $total) * 100);
            $percentB = 100 - $percentA;
        }

        return [
            'phase' => $phase,
            'timeLeft' => self::CYCLE_DURATION - $cycleTime,
            'battle' => [
                'A' => $optionA,
                'B' => $optionB,
            ],
            'results' => [
                'A' => $percentA,
                'B' => $percentB,
            ],
            'colors' => [
                'A' => VoteChoice::A->getColor(),
                'B' => VoteChoice::B->getColor(),
            ]
        ];
    }

    private function getCurrentBattleIndex(): int
    {
        $timestamp = $this->now ?: time();
        return (int) floor($timestamp / self::CYCLE_DURATION) % count($this->battles);
    }

    private function getCurrentBattleId(): string
    {
        // Unique ID for the current battle window (e.g. "battle_173315345")
        // We use the 10s window timestamp to group votes for the same active battle
        $timestamp = $this->now ?: time();
        $windowStart = (int) floor($timestamp / self::CYCLE_DURATION);
        return 'battle_' . $windowStart;
    }
}