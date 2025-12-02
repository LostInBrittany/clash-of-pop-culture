<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\VoteChoice;

use App\Repository\BattleRepository;
use Psr\Cache\CacheItemPoolInterface;

class GameEngine
{
    private const CYCLE_DURATION = 15;
    private const VOTE_DURATION = 10;

    public function __construct(
        private readonly CacheItemPoolInterface $cache,
        private readonly BattleRepository $battleRepository,
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
        
        // Deterministic battle selection based on 15s window
        $battle = $this->getCurrentBattle();
        $optionA = $battle ? $battle->getOptionA() : 'Loading...';
        $optionB = $battle ? $battle->getOptionB() : 'Loading...';

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
            'id' => $battleId,
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

    private function getCurrentBattle(): ?\App\Entity\Battle
    {
        $timestamp = $this->now ?: time();
        $count = $this->battleRepository->count([]);
        
        if ($count === 0) {
            return null;
        }

        // Deterministic Random Selection
        // 1. Get the current 15s window index (e.g., 123456)
        $windowIndex = (int) floor($timestamp / self::CYCLE_DURATION);
        
        // 2. Hash it to get a pseudo-random hex string (e.g., "a1b2...")
        $hash = md5((string) $windowIndex);
        
        // 3. Convert first 8 chars of hash to integer
        $seed = hexdec(substr($hash, 0, 8));
        
        // 4. Map to a valid battle index
        $battleIndex = $seed % $count;
        
        // Offset is 0-based, so we can use findBy with limit 1 and offset
        $results = $this->battleRepository->findBy([], ['id' => 'ASC'], 1, $battleIndex);
        return $results[0] ?? null;
    }

    private function getCurrentBattleId(): string
    {
        // Unique ID for the current battle window (e.g. "battle_173315345")
        // We use the 15s window timestamp to group votes for the same active battle
        $timestamp = $this->now ?: time();
        $windowStart = (int) floor($timestamp / self::CYCLE_DURATION);
        return 'battle_' . $windowStart;
    }
}