<?php

namespace App\Models;

use CodeIgniter\Model;

class TenisMejaTournamentModel extends Model
{
    protected $table            = 'tenis_meja_tournaments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'title',
        'category',
        'participant_count',
        'participants_json',
        'bracket_json',
        'status',
        'is_active',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Default list of PUPR Balai for quick pick
     */
    public static array $defaultBalais = [
        'BWS Sumatera III',
        'BPJN Riau',
        'BPPW Riau',
        'BP2JK Riau',
        'Satker PPS Riau',
        'BPBPK Wilayah II',
        'Sekretariat PUPR Riau',
        'Dinas PUPR Provinsi Riau',
    ];

    /**
     * Ensure table exists in database
     */
    public function ensureTable(): void
    {
        try {
            if (! $this->db->tableExists($this->table)) {
                $forge = \Config\Database::forge();
                $forge->addField([
                    'id' => [
                        'type'           => 'INT',
                        'constraint'     => 11,
                        'unsigned'       => true,
                        'auto_increment' => true,
                    ],
                    'title' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 255,
                        'default'    => 'Turnamen Tenis Meja Sistem Gugur',
                    ],
                    'category' => [
                        'type'       => 'VARCHAR',
                        'constraint' => 50,
                        'default'    => 'Tunggal Putra',
                    ],
                    'participant_count' => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'default'    => 0,
                    ],
                    'participants_json' => [
                        'type' => 'LONGTEXT',
                        'null' => true,
                    ],
                    'bracket_json' => [
                        'type' => 'LONGTEXT',
                        'null' => true,
                    ],
                    'status' => [
                        'type'       => 'ENUM',
                        'constraint' => ['draft', 'ongoing', 'completed'],
                        'default'    => 'ongoing',
                    ],
                    'is_active' => [
                        'type'       => 'TINYINT',
                        'constraint' => 1,
                        'default'    => 1,
                    ],
                    'created_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                    'updated_at' => [
                        'type' => 'DATETIME',
                        'null' => true,
                    ],
                ]);

                $forge->addKey('id', true);
                $forge->addKey('is_active');
                $forge->createTable($this->table, true);
            }
        } catch (\Throwable $e) {
            log_message('error', 'TenisMejaTournamentModel::ensureTable failed: ' . $e->getMessage());
        }
    }

    /**
     * Get the active tournament or latest
     */
    public function getActiveTournament(): ?array
    {
        $this->ensureTable();

        $active = $this->where('is_active', 1)->orderBy('id', 'DESC')->first();
        if ($active) {
            return $this->decodeJsonFields($active);
        }

        $latest = $this->orderBy('id', 'DESC')->first();
        return $latest ? $this->decodeJsonFields($latest) : null;
    }

    /**
     * Create and initialize a new tournament with single elimination bracket
     *
     * @param string $title
     * @param string $category
     * @param array $participantNames (list of string names / balai)
     * @return int Tournament ID
     */
    public function createTournament(string $title, string $category, array $participantNames): int
    {
        $this->ensureTable();

        // Clean and filter participant names
        $cleanedParticipants = [];
        $seed = 1;
        foreach ($participantNames as $name) {
            $trimmed = trim((string)$name);
            if ($trimmed !== '') {
                $cleanedParticipants[] = [
                    'id'    => $seed,
                    'seed'  => $seed,
                    'name'  => $trimmed,
                    'balai' => $trimmed,
                ];
                $seed++;
            }
        }

        if (count($cleanedParticipants) < 2) {
            throw new \InvalidArgumentException('Jumlah peserta minimal 2 orang/balai.');
        }

        // Build the bracket structure
        $bracket = $this->buildSingleEliminationBracket($cleanedParticipants);

        // Deactivate older tournaments
        $this->where('is_active', 1)->set(['is_active' => 0])->update();

        $now = date('Y-m-d H:i:s');
        $data = [
            'title'             => $title ?: 'Turnamen Tenis Meja Sistem Gugur',
            'category'          => $category ?: 'Tunggal Putra',
            'participant_count' => count($cleanedParticipants),
            'participants_json' => json_encode($cleanedParticipants, JSON_UNESCAPED_UNICODE),
            'bracket_json'      => json_encode($bracket, JSON_UNESCAPED_UNICODE),
            'status'            => 'ongoing',
            'is_active'         => 1,
            'created_at'        => $now,
            'updated_at'        => $now,
        ];

        return (int) $this->insert($data, true);
    }

    /**
     * Build single elimination bracket data structure
     */
    public function buildSingleEliminationBracket(array $participants): array
    {
        $N = count($participants);

        // Find smallest power of 2 >= N
        $P = 1;
        while ($P < $N) {
            $P *= 2;
        }
        if ($P < 2) {
            $P = 2;
        }

        $totalRounds = (int) log($P, 2);

        // Determine round names
        $roundNames = $this->getRoundNames($totalRounds);

        // Prepare initial slots with participants and BYEs
        // Distribute BYEs evenly so that no match has two BYEs
        $slots = [];
        for ($i = 0; $i < $P; $i++) {
            $slots[$i] = null;
        }

        // Standard bracket placement
        // If P == N, direct fill
        // If P > N, number of byes is P - N.
        $byesCount = $P - $N;
        $matchesInR1 = $P / 2;

        // Pair players: match i gets slot (2*i) and (2*i + 1)
        // We assign players first, and if byesCount > 0, give bye to player2 of selected matches
        $playerIdx = 0;
        $assignedMatches = [];

        // For each of the matches in R1:
        // We decide if this match has a BYE.
        // Spread BYEs across matches from the bottom or alternately
        $hasByeInMatch = array_fill(0, $matchesInR1, false);
        if ($byesCount > 0) {
            // Give byes to matches starting from odd indexed or spread out
            // E.g., for 6 players (P=8, 2 byes, 4 matches): matches 1 and 3 or 0 and 2
            $step = max(1, (int) floor($matchesInR1 / $byesCount));
            $assignedByes = 0;
            for ($m = $matchesInR1 - 1; $m >= 0 && $assignedByes < $byesCount; $m--) {
                $hasByeInMatch[$m] = true;
                $assignedByes++;
            }
        }

        $rounds = [];

        // Round 1 matches
        $r1Matches = [];
        $matchNumber = 1;

        for ($m = 0; $m < $matchesInR1; $m++) {
            $p1 = $participants[$playerIdx++] ?? null;
            $isBye = $hasByeInMatch[$m];
            $p2 = $isBye ? ['id' => 0, 'seed' => 0, 'name' => '(BYE)', 'balai' => '(BYE)', 'is_bye' => true] : ($participants[$playerIdx++] ?? null);

            $matchId = 'R1_M' . $m;
            $nextMatchId = $totalRounds > 1 ? ('R2_M' . (int) floor($m / 2)) : null;
            $nextSlot = ($m % 2 === 0) ? 1 : 2;

            $winner = null;
            $status = 'pending';
            $score1 = null;
            $score2 = null;

            // If player 2 is BYE, player 1 automatically wins and advances
            if ($isBye && $p1 !== null) {
                $winner = $p1;
                $status = 'completed';
                $score1 = 1;
                $score2 = 0;
            } elseif ($p1 === null && $p2 !== null && empty($p2['is_bye'])) {
                $winner = $p2;
                $status = 'completed';
                $score1 = 0;
                $score2 = 1;
            }

            $r1Matches[] = [
                'id'              => $matchId,
                'round'           => 1,
                'round_name'      => $roundNames[1] ?? 'Babak 1',
                'match_index'     => $m,
                'match_number'    => $matchNumber++,
                'player1'         => $p1,
                'player2'         => $p2,
                'score1'          => $score1,
                'score2'          => $score2,
                'winner'          => $winner,
                'status'          => $status,
                'is_bye'          => $isBye,
                'next_match_id'   => $nextMatchId,
                'next_match_slot' => $nextSlot,
            ];
        }

        $rounds[1] = [
            'round'      => 1,
            'name'       => $roundNames[1] ?? 'Babak 1',
            'matches'    => $r1Matches,
        ];

        // Build subsequent rounds (Round 2 to totalRounds)
        for ($r = 2; $r <= $totalRounds; $r++) {
            $prevRoundMatches = $rounds[$r - 1]['matches'];
            $currentMatchCount = count($prevRoundMatches) / 2;
            $currentMatches = [];

            for ($m = 0; $m < $currentMatchCount; $m++) {
                $matchId = 'R' . $r . '_M' . $m;
                $nextMatchId = ($r < $totalRounds) ? ('R' . ($r + 1) . '_M' . (int) floor($m / 2)) : null;
                $nextSlot = ($m % 2 === 0) ? 1 : 2;

                // Check winners from previous round matches that feed into this
                $parentMatch1 = $prevRoundMatches[$m * 2] ?? null;
                $parentMatch2 = $prevRoundMatches[$m * 2 + 1] ?? null;

                $p1 = $parentMatch1['winner'] ?? null;
                $p2 = $parentMatch2['winner'] ?? null;

                $currentMatches[] = [
                    'id'              => $matchId,
                    'round'           => $r,
                    'round_name'      => $roundNames[$r] ?? ('Babak ' . $r),
                    'match_index'     => $m,
                    'match_number'    => $matchNumber++,
                    'player1'         => $p1,
                    'player2'         => $p2,
                    'score1'          => null,
                    'score2'          => null,
                    'winner'          => null,
                    'status'          => 'pending',
                    'is_bye'          => false,
                    'next_match_id'   => $nextMatchId,
                    'next_match_slot' => $nextSlot,
                    'parent_match_1'  => $parentMatch1['id'] ?? null,
                    'parent_match_2'  => $parentMatch2['id'] ?? null,
                ];
            }

            $rounds[$r] = [
                'round'   => $r,
                'name'    => $roundNames[$r] ?? ('Babak ' . $r),
                'matches' => $currentMatches,
            ];
        }

        // Calculate podium / champions
        $podium = $this->calculatePodium($rounds, $totalRounds);

        return [
            'total_rounds' => $totalRounds,
            'bracket_size' => $P,
            'rounds'       => $rounds,
            'podium'       => $podium,
        ];
    }

    /**
     * Get readable names for rounds based on total rounds
     */
    protected function getRoundNames(int $totalRounds): array
    {
        $names = [];
        for ($r = 1; $r <= $totalRounds; $r++) {
            $fromFinal = $totalRounds - $r;
            if ($fromFinal === 0) {
                $names[$r] = 'Final';
            } elseif ($fromFinal === 1) {
                $names[$r] = 'Semi Final';
            } elseif ($fromFinal === 2) {
                $names[$r] = 'Perempat Final (8 Besar)';
            } elseif ($fromFinal === 3) {
                $names[$r] = 'Babak 16 Besar';
            } elseif ($fromFinal === 4) {
                $names[$r] = 'Babak 32 Besar';
            } else {
                $names[$r] = 'Babak ' . $r;
            }
        }
        return $names;
    }

    /**
     * Calculate Podium (Juara 1, 2, 3) from bracket state
     */
    protected function calculatePodium(array $rounds, int $totalRounds): array
    {
        $finalMatch = $rounds[$totalRounds]['matches'][0] ?? null;
        $champion = null;
        $runnerUp = null;
        $thirdPlace = null;

        if ($finalMatch && ! empty($finalMatch['winner'])) {
            $champion = $finalMatch['winner'];
            if (! empty($finalMatch['player1']) && ! empty($finalMatch['player2'])) {
                $runnerUp = ($finalMatch['winner']['name'] === $finalMatch['player1']['name'])
                    ? $finalMatch['player2']
                    : $finalMatch['player1'];
            }
        }

        // Semifinal losers for 3rd place / semifinalists
        if ($totalRounds > 1 && isset($rounds[$totalRounds - 1]['matches'])) {
            $semiMatches = $rounds[$totalRounds - 1]['matches'];
            $semiLosers = [];
            foreach ($semiMatches as $sm) {
                if (! empty($sm['winner']) && ! empty($sm['player1']) && ! empty($sm['player2'])) {
                    $loser = ($sm['winner']['name'] === $sm['player1']['name']) ? $sm['player2'] : $sm['player1'];
                    if (! empty($loser['name']) && $loser['name'] !== '(BYE)') {
                        $semiLosers[] = $loser;
                    }
                }
            }
            if (! empty($semiLosers)) {
                $thirdPlace = $semiLosers;
            }
        }

        return [
            'champion'    => $champion,
            'runner_up'   => $runnerUp,
            'third_place' => $thirdPlace,
        ];
    }

    /**
     * Shuffle participant positions and regenerate bracket
     */
    public function shuffleParticipants(int $tournamentId): array
    {
        $tournament = $this->find($tournamentId);
        if (! $tournament) {
            throw new \RuntimeException('Data turnamen tidak ditemukan.');
        }

        $tournament = $this->decodeJsonFields($tournament);
        $participants = $tournament['participants_json'] ?? [];

        if (count($participants) < 2) {
            throw new \RuntimeException('Jumlah peserta tidak cukup untuk diacak.');
        }

        // Fisher-Yates shuffle
        shuffle($participants);

        // Reassign seeds
        foreach ($participants as $idx => &$p) {
            $p['seed'] = $idx + 1;
        }
        unset($p);

        // Rebuild bracket from shuffled participants
        $newBracket = $this->buildSingleEliminationBracket($participants);

        $now = date('Y-m-d H:i:s');
        $this->update($tournamentId, [
            'participants_json' => json_encode($participants, JSON_UNESCAPED_UNICODE),
            'bracket_json'      => json_encode($newBracket, JSON_UNESCAPED_UNICODE),
            'status'            => 'ongoing',
            'updated_at'        => $now,
        ]);

        return [
            'participants' => $participants,
            'bracket'      => $newBracket,
        ];
    }

    /**
     * Update match result (score and winner) and cascade to next round
     */
    public function updateMatch(int $tournamentId, string $matchId, ?int $score1, ?int $score2, ?string $winnerName): array
    {
        $tournament = $this->find($tournamentId);
        if (! $tournament) {
            throw new \RuntimeException('Data turnamen tidak ditemukan.');
        }

        $tournament = $this->decodeJsonFields($tournament);
        $bracket = $tournament['bracket_json'] ?? [];
        $rounds = $bracket['rounds'] ?? [];
        $totalRounds = (int) ($bracket['total_rounds'] ?? count($rounds));

        // Locate the target match
        $targetRound = null;
        $targetMatchIdx = null;

        foreach ($rounds as $r => $roundData) {
            foreach ($roundData['matches'] as $mIdx => $m) {
                if ($m['id'] === $matchId) {
                    $targetRound = $r;
                    $targetMatchIdx = $mIdx;
                    break 2;
                }
            }
        }

        if ($targetRound === null || $targetMatchIdx === null) {
            throw new \RuntimeException("Pertandingan {$matchId} tidak ditemukan dalam bagan.");
        }

        $match = &$rounds[$targetRound]['matches'][$targetMatchIdx];

        $match['score1'] = $score1;
        $match['score2'] = $score2;

        // Determine winner
        $selectedWinner = null;
        if ($winnerName !== null && $winnerName !== '') {
            if (! empty($match['player1']) && $match['player1']['name'] === $winnerName) {
                $selectedWinner = $match['player1'];
            } elseif (! empty($match['player2']) && $match['player2']['name'] === $winnerName) {
                $selectedWinner = $match['player2'];
            }
        } elseif ($score1 !== null && $score2 !== null) {
            if ($score1 > $score2 && ! empty($match['player1'])) {
                $selectedWinner = $match['player1'];
            } elseif ($score2 > $score1 && ! empty($match['player2'])) {
                $selectedWinner = $match['player2'];
            }
        }

        $match['winner'] = $selectedWinner;
        $match['status'] = $selectedWinner ? 'completed' : 'pending';

        // Cascade winner to next round match
        $nextMatchId = $match['next_match_id'] ?? null;
        $nextSlot = $match['next_match_slot'] ?? 1;

        if ($nextMatchId && $targetRound < $totalRounds) {
            $nextRoundNum = $targetRound + 1;
            if (isset($rounds[$nextRoundNum]['matches'])) {
                foreach ($rounds[$nextRoundNum]['matches'] as &$nextM) {
                    if ($nextM['id'] === $nextMatchId) {
                        $oldPlayer = ($nextSlot === 1) ? ($nextM['player1'] ?? null) : ($nextM['player2'] ?? null);
                        
                        if ($nextSlot === 1) {
                            $nextM['player1'] = $selectedWinner;
                        } else {
                            $nextM['player2'] = $selectedWinner;
                        }

                        // If the participant changed and next match had a winner, reset next match
                        if ($oldPlayer !== null && ($selectedWinner === null || $oldPlayer['name'] !== $selectedWinner['name'])) {
                            $this->clearDownstreamMatches($rounds, $nextRoundNum, $nextM['id']);
                        }
                        break;
                    }
                }
                unset($nextM);
            }
        }

        // Recalculate podium
        $bracket['rounds'] = $rounds;
        $bracket['podium'] = $this->calculatePodium($rounds, $totalRounds);

        // Check if tournament is fully completed
        $finalMatch = $rounds[$totalRounds]['matches'][0] ?? null;
        $newStatus = (! empty($finalMatch['winner'])) ? 'completed' : 'ongoing';

        $now = date('Y-m-d H:i:s');
        $this->update($tournamentId, [
            'bracket_json' => json_encode($bracket, JSON_UNESCAPED_UNICODE),
            'status'       => $newStatus,
            'updated_at'   => $now,
        ]);

        return $bracket;
    }

    /**
     * Clear downstream matches recursively if upstream winner changes
     */
    protected function clearDownstreamMatches(array &$rounds, int $startRound, string $startMatchId): void
    {
        $queue = [[$startRound, $startMatchId]];

        while (! empty($queue)) {
            [$r, $mId] = array_shift($queue);
            if (! isset($rounds[$r]['matches'])) {
                continue;
            }

            foreach ($rounds[$r]['matches'] as &$m) {
                if ($m['id'] === $mId) {
                    $m['score1'] = null;
                    $m['score2'] = null;
                    $m['winner'] = null;
                    $m['status'] = 'pending';

                    if (! empty($m['next_match_id'])) {
                        $nextR = $r + 1;
                        $slot = $m['next_match_slot'] ?? 1;
                        if (isset($rounds[$nextR]['matches'])) {
                            foreach ($rounds[$nextR]['matches'] as &$nextM) {
                                if ($nextM['id'] === $m['next_match_id']) {
                                    if ($slot === 1) {
                                        $nextM['player1'] = null;
                                    } else {
                                        $nextM['player2'] = null;
                                    }
                                    $queue[] = [$nextR, $nextM['id']];
                                }
                            }
                            unset($nextM);
                        }
                    }
                }
            }
            unset($m);
        }
    }

    /**
     * Reset match scores and winners while preserving participants
     */
    public function resetTournamentScores(int $tournamentId): array
    {
        $tournament = $this->find($tournamentId);
        if (! $tournament) {
            throw new \RuntimeException('Data turnamen tidak ditemukan.');
        }

        $tournament = $this->decodeJsonFields($tournament);
        $participants = $tournament['participants_json'] ?? [];

        $newBracket = $this->buildSingleEliminationBracket($participants);

        $now = date('Y-m-d H:i:s');
        $this->update($tournamentId, [
            'bracket_json' => json_encode($newBracket, JSON_UNESCAPED_UNICODE),
            'status'       => 'ongoing',
            'updated_at'   => $now,
        ]);

        return $newBracket;
    }

    /**
     * Decode JSON fields into PHP arrays
     */
    protected function decodeJsonFields(array $row): array
    {
        if (isset($row['participants_json']) && is_string($row['participants_json'])) {
            $row['participants_json'] = json_decode($row['participants_json'], true) ?: [];
        }
        if (isset($row['bracket_json']) && is_string($row['bracket_json'])) {
            $row['bracket_json'] = json_decode($row['bracket_json'], true) ?: [];
        }
        return $row;
    }
}
