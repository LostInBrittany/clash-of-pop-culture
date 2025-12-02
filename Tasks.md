# Tasks to Recreate "Clash of Pop Culture"

Follow these steps to build the demo application.

## 1. Prerequisites
- PHP 8.3 or higher
- Composer installed
- Symfony CLI (recommended)

## 2. Project Initialization

Run the following commands in your terminal:

```bash
# 1. Create the Symfony Skeleton project
composer create-project symfony/skeleton clash_of_pop_culture

# 2. Enter the directory
cd clash_of_pop_culture

# 3. Install required dependencies
# serializer-pack is needed for #[MapRequestPayload]
composer require symfony/serializer-pack symfony/validator
```

## 3. Create Application Files

Create the following files with the provided content.

### 3.1 Create `src/Enum/VoteChoice.php`

```php
<?php

declare(strict_types=1);

namespace App\Enum;

enum VoteChoice: string
{
    case A = 'A';
    case B = 'B';

    public function getColor(): string
    {
        return match($this) {
            self::A => 'text-cyan-400 border-cyan-400 shadow-[0_0_20px_rgba(34,211,238,0.5)]',
            self::B => 'text-fuchsia-400 border-fuchsia-400 shadow-[0_0_20px_rgba(232,121,249,0.5)]',
        };
    }
}
```

### 3.2 Create `src/Service/GameEngine.php`

```php
<?php

declare(strict_types=1);

namespace App\Service;

use App\Enum\VoteChoice;

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
        
        // Deterministic battle selection based on 15s window
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
        // We use the 15s window timestamp to group votes for the same active battle
        $timestamp = $this->now ?: time();
        $windowStart = (int) floor($timestamp / self::CYCLE_DURATION);
        return 'battle_' . $windowStart;
    }
}
```

### 3.3 Create `src/Dto/VoteDto.php`

```php
<?php

declare(strict_types=1);

namespace App\Dto;

use App\Enum\VoteChoice;
use Symfony\Component\Validator\Constraints as Assert;

class VoteDto
{
    public function __construct(
        #[Assert\NotNull]
        public VoteChoice $choice,
    ) {}
}
```

### 3.4 Create `src/Controller/GameController.php`

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\VoteDto;
use App\Service\GameEngine;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

class GameController extends AbstractController
{
    #[Route('/api/state', methods: ['GET'])]
    public function state(GameEngine $engine): JsonResponse
    {
        return $this->json($engine->getState());
    }

    #[Route('/api/vote', methods: ['POST'])]
    public function vote(
        #[MapRequestPayload] VoteDto $vote,
        GameEngine $engine,
    ): JsonResponse {
        $engine->vote($vote->choice);
        
        return $this->json([
            'status' => 'received', 
            'choice' => $vote->choice
        ]);
    }
}
```

### 3.5 Create `public/index.html`

```html
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clash of Pop Culture</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .neon-text { text-shadow: 0 0 10px currentColor; }
    </style>
</head>
<body class="bg-slate-900 text-white h-screen flex flex-col items-center justify-center overflow-hidden selection:bg-fuchsia-500 selection:text-white">

    <div class="absolute inset-0 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] opacity-20 pointer-events-none"></div>

    <main class="relative z-10 w-full max-w-4xl p-8 text-center">
        <h1 class="text-6xl font-black mb-12 tracking-tighter bg-gradient-to-r from-cyan-400 to-fuchsia-400 bg-clip-text text-transparent animate-pulse">
            CLASH OF POP CULTURE
        </h1>

        <div id="battle-arena" class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center mb-12">
            <!-- Option A -->
            <button id="btn-a" onclick="vote('A')" class="group relative p-8 rounded-2xl border-2 border-slate-700 hover:border-cyan-400 transition-all duration-300 hover:scale-105 active:scale-95 overflow-hidden">
                <!-- Progress Bar Background -->
                <div id="progress-a" class="absolute inset-0 bg-cyan-900/50 origin-left transition-transform duration-1000 scale-x-0 z-0"></div>
                
                <div class="relative z-10">
                    <div class="absolute inset-0 bg-cyan-500/10 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                    <h2 id="option-a" class="text-3xl font-bold text-slate-300 group-hover:text-cyan-400 transition-colors">Loading...</h2>
                    <div id="percent-a" class="text-4xl font-black text-cyan-400 mt-2 opacity-0 transition-opacity">0%</div>
                    <div class="mt-4 text-sm font-mono text-slate-500 group-hover:text-cyan-300">PRESS A</div>
                </div>
            </button>

            <!-- VS -->
            <div class="text-4xl font-black text-slate-600 italic">VS</div>

            <!-- Option B -->
            <button id="btn-b" onclick="vote('B')" class="group relative p-8 rounded-2xl border-2 border-slate-700 hover:border-fuchsia-400 transition-all duration-300 hover:scale-105 active:scale-95 overflow-hidden">
                <!-- Progress Bar Background -->
                <div id="progress-b" class="absolute inset-0 bg-fuchsia-900/50 origin-left transition-transform duration-1000 scale-x-0 z-0"></div>
                
                <div class="relative z-10">
                    <div class="absolute inset-0 bg-fuchsia-500/10 opacity-0 group-hover:opacity-100 transition-opacity rounded-2xl"></div>
                    <h2 id="option-b" class="text-3xl font-bold text-slate-300 group-hover:text-fuchsia-400 transition-colors">Loading...</h2>
                    <div id="percent-b" class="text-4xl font-black text-fuchsia-400 mt-2 opacity-0 transition-opacity">0%</div>
                    <div class="mt-4 text-sm font-mono text-slate-500 group-hover:text-fuchsia-300">PRESS B</div>
                </div>
            </button>
        </div>

        <!-- Status / Timer -->
        <div class="flex flex-col items-center gap-4">
            <div id="phase-indicator" class="text-xl font-bold tracking-widest uppercase text-slate-400">WAITING...</div>
            <div class="w-full max-w-md h-2 bg-slate-800 rounded-full overflow-hidden">
                <div id="timer-bar" class="h-full bg-gradient-to-r from-cyan-500 to-fuchsia-500 w-full transition-all duration-1000 ease-linear"></div>
            </div>
            <div id="time-left" class="font-mono text-slate-500">00s</div>
        </div>
    </main>

    <script>
        let currentUserVote = null;
        let currentBattleId = null;

        async function syncState() {
            try {
                const res = await fetch('/api/state');
                const data = await res.json();
                render(data);
            } catch (e) {
                console.error("Connection lost", e);
            }
        }

        async function vote(choice) {
            try {
                // Optimistic update
                currentUserVote = choice;
                
                await fetch('/api/vote', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ choice })
                });
                // Visual feedback could be added here
            } catch (e) {
                console.error("Vote failed", e);
            }
        }

        function render(state) {
            // Detect new battle to reset vote
            const battleId = state.battle.A + state.battle.B;
            if (currentBattleId !== battleId) {
                currentBattleId = battleId;
                currentUserVote = null;
            }

            // Update Text
            document.getElementById('option-a').innerText = state.battle.A;
            document.getElementById('option-b').innerText = state.battle.B;
            
            // Update Phase
            const phaseEl = document.getElementById('phase-indicator');
            const timerBar = document.getElementById('timer-bar');
            const timeLeftEl = document.getElementById('time-left');
            
            phaseEl.innerText = state.phase === 'VOTE' ? 'VOTE NOW!' : 'RESULTS';
            phaseEl.className = `text-xl font-bold tracking-widest uppercase ${state.phase === 'VOTE' ? 'text-green-400 animate-pulse' : 'text-yellow-400'}`;
            
            // Show vote time remaining if in vote phase
            const displayTime = state.phase === 'VOTE' ? (state.timeLeft - 5) : 0;
            timeLeftEl.innerText = displayTime + 's';
            
            // Simple timer visualization (10s vote cycle)
            // timeLeft is total cycle time remaining.
            // Vote phase is when timeLeft > 5 (15 down to 5).
            // We want 100% at 15s, 0% at 5s.
            let percentage = 0;
            if (state.phase === 'VOTE') {
                 percentage = ((state.timeLeft - 5) / 10) * 100;
            }
            timerBar.style.width = `${percentage}%`;

            // Disable buttons during result phase
            const isResult = state.phase !== 'VOTE';
            document.getElementById('btn-a').disabled = isResult;
            document.getElementById('btn-b').disabled = isResult;
            
            // Handle Results Visualization
            const progressA = document.getElementById('progress-a');
            const progressB = document.getElementById('progress-b');
            const percentA = document.getElementById('percent-a');
            const percentB = document.getElementById('percent-b');

            // Reset borders first
            document.getElementById('btn-a').classList.remove('border-yellow-400', 'shadow-[0_0_30px_rgba(250,204,21,0.5)]');
            document.getElementById('btn-b').classList.remove('border-yellow-400', 'shadow-[0_0_30px_rgba(250,204,21,0.5)]');

            // Apply selection style if voting
            if (currentUserVote === 'A') {
                document.getElementById('btn-a').classList.add('border-yellow-400', 'shadow-[0_0_30px_rgba(250,204,21,0.5)]');
            } else if (currentUserVote === 'B') {
                document.getElementById('btn-b').classList.add('border-yellow-400', 'shadow-[0_0_30px_rgba(250,204,21,0.5)]');
            }

            if (isResult) {
                document.getElementById('btn-a').classList.add('cursor-not-allowed');
                document.getElementById('btn-b').classList.add('cursor-not-allowed');
                
                // Show percentages
                progressA.style.transform = `scaleX(${state.results.A / 100})`;
                progressB.style.transform = `scaleX(${state.results.B / 100})`;
                
                percentA.innerText = state.results.A + '%';
                percentB.innerText = state.results.B + '%';
                
                percentA.classList.remove('opacity-0');
                percentB.classList.remove('opacity-0');
            } else {
                document.getElementById('btn-a').classList.remove('cursor-not-allowed');
                document.getElementById('btn-b').classList.remove('cursor-not-allowed');
                
                // Reset
                progressA.style.transform = 'scaleX(0)';
                progressB.style.transform = 'scaleX(0)';
                percentA.classList.add('opacity-0');
                percentB.classList.add('opacity-0');
            }
        }

        // Poll every second
        setInterval(syncState, 1000);
        syncState(); // Initial call
    </script>
</body>
</html>
```

## 4. Running the Application

Run the following command to start the web server:

```bash
symfony server:start
```

Open `http://127.0.0.1:8000`.
