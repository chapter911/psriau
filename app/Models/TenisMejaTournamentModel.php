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
                        'default'    => 'putra',
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
                $forge->addKey(['category', 'is_active']);
                $forge->createTable($this->table, true);
            }
        } catch (\Throwable $e) {
            log_message('error', 'TenisMejaTournamentModel::ensureTable failed: ' . $e->getMessage());
        }
    }

    /**
     * Normalize category slug
     */
    public static function normalizeCategory(?string $category): string
    {
        $c = strtolower(trim((string) $category));
        if ($c === 'putri' || str_contains($c, 'putri')) {
            return 'putri';
        }
        return 'putra';
    }

    /**
     * Get active tournament by category
     */
    public function getActiveTournament(?string $category = 'putra'): ?array
    {
        $this->ensureTable();
        $cat = self::normalizeCategory($category);

        $active = $this->where('category', $cat)
                       ->where('is_active', 1)
                       ->orderBy('id', 'DESC')
                       ->first();

        if ($active) {
            return $this->decodeJsonFields($active);
        }

        $latest = $this->where('category', $cat)
                       ->orderBy('id', 'DESC')
                       ->first();

        return $latest ? $this->decodeJsonFields($latest) : null;
    }

    /**
     * Create and initialize a new tournament with single elimination bracket
     */
    public function createTournament(string $title, string $category, array $participantList): int
    {
        $this->ensureTable();
        $cat = self::normalizeCategory($category);

        $cleanedParticipants = [];
        $seed = 1;

        foreach ($participantList as $item) {
            $name = '';
            $balai = '';

            if (is_array($item)) {
                $name = trim((string) ($item['name'] ?? ''));
                $balai = trim((string) ($item['balai'] ?? ''));
            } else {
                $name = trim((string) $item);
            }

            if ($name === '' && $balai !== '') {
                $name = $balai;
            } elseif ($name !== '' && $balai === '') {
                $balai = $name;
            }

            if ($name !== '') {
                $cleanedParticipants[] = [
                    'id'    => $seed,
                    'seed'  => $seed,
                    'name'  => $name,
                    'balai' => $balai,
                ];
                $seed++;
            }
        }

        if (count($cleanedParticipants) < 2) {
            throw new \InvalidArgumentException('Jumlah peserta minimal 2 orang/balai.');
        }

        $bracket = $this->buildSingleEliminationBracket($cleanedParticipants);

        // Deactivate older tournaments in the same category
        $this->where('category', $cat)
             ->where('is_active', 1)
             ->set(['is_active' => 0])
             ->update();

        $catLabel = ($cat === 'putri') ? 'Putri' : 'Putra';
        $now = date('Y-m-d H:i:s');
        $data = [
            'title'             => $title ?: ('Turnamen Tenis Meja ' . $catLabel),
            'category'          => $cat,
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
     * Build clean single elimination tournament bracket
     * Specialized for 5 participants according to user requirements:
     * - R1: A vs B (winner to Final slot 1), C vs D (winner to Semifinal slot 1)
     * - R2 (Semi Final): Winner(C vs D) vs E (winner to Final slot 2)
     * - R3 (Final): Winner(A vs B) vs Winner(Semi Final)
     */
    public function buildSingleEliminationBracket(array $participants): array
    {
        $N = count($participants);

        // 2 PARTICIPANTS (DIRECT FINAL)
        if ($N === 2) {
            $rounds = [
                1 => [
                    'round'   => 1,
                    'name'    => 'Final',
                    'matches' => [
                        [
                            'id'              => 'R1_M0',
                            'round'           => 1,
                            'round_name'      => 'Final',
                            'match_number'    => 1,
                            'player1'         => $participants[0],
                            'player2'         => $participants[1],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => null,
                            'next_match_slot' => null,
                            'note'            => 'Perebutan Juara 1 & 2',
                        ]
                    ]
                ]
            ];
            return [
                'total_rounds' => 1,
                'bracket_size' => 2,
                'rounds'       => $rounds,
                'podium'       => ['champion' => null, 'runner_up' => null],
            ];
        }

        // 3 PARTICIPANTS
        if ($N === 3) {
            $rounds = [
                1 => [
                    'round'   => 1,
                    'name'    => 'Semi Final',
                    'matches' => [
                        [
                            'id'              => 'R1_M0',
                            'round'           => 1,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 1,
                            'player1'         => $participants[0],
                            'player2'         => $participants[1],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M0',
                            'next_match_slot' => 1,
                            'note'            => 'Pemenang Lolos ke Final',
                        ]
                    ]
                ],
                2 => [
                    'round'   => 2,
                    'name'    => 'Final',
                    'matches' => [
                        [
                            'id'              => 'R2_M0',
                            'round'           => 2,
                            'round_name'      => 'Final',
                            'match_number'    => 2,
                            'player1'         => null,
                            'player2'         => $participants[2],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => null,
                            'next_match_slot' => null,
                            'note'            => 'Perebutan Juara 1 & 2',
                        ]
                    ]
                ]
            ];
            return [
                'total_rounds' => 2,
                'bracket_size' => 4,
                'rounds'       => $rounds,
                'podium'       => ['champion' => null, 'runner_up' => null],
            ];
        }

        // 4 PARTICIPANTS
        if ($N === 4) {
            $rounds = [
                1 => [
                    'round'   => 1,
                    'name'    => 'Semi Final',
                    'matches' => [
                        [
                            'id'              => 'R1_M0',
                            'round'           => 1,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 1,
                            'player1'         => $participants[0],
                            'player2'         => $participants[1],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M0',
                            'next_match_slot' => 1,
                        ],
                        [
                            'id'              => 'R1_M1',
                            'round'           => 1,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 2,
                            'player1'         => $participants[2],
                            'player2'         => $participants[3],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M0',
                            'next_match_slot' => 2,
                        ]
                    ]
                ],
                2 => [
                    'round'   => 2,
                    'name'    => 'Final',
                    'matches' => [
                        [
                            'id'              => 'R2_M0',
                            'round'           => 2,
                            'round_name'      => 'Final',
                            'match_number'    => 3,
                            'player1'         => null,
                            'player2'         => null,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => null,
                            'next_match_slot' => null,
                            'note'            => 'Perebutan Juara 1 & 2',
                        ]
                    ]
                ]
            ];
            return [
                'total_rounds' => 2,
                'bracket_size' => 4,
                'rounds'       => $rounds,
                'podium'       => ['champion' => null, 'runner_up' => null],
            ];
        }

        // 5 PARTICIPANTS (EXACT USER SPECIFICATION WITH EXPLICIT BYE BAGAN CARDS)
        if ($N === 5) {
            $byeDummy = [
                'id'     => 0,
                'seed'   => '-',
                'name'   => '(BYE)',
                'balai'  => 'Lolos Otomatis',
                'is_bye' => true,
            ];

            $rounds = [
                1 => [
                    'round'   => 1,
                    'name'    => 'Babak I',
                    'matches' => [
                        [
                            'id'              => 'R1_M0',
                            'round'           => 1,
                            'round_name'      => 'Babak I',
                            'match_number'    => 1,
                            'player1'         => $participants[0],
                            'player2'         => $participants[1],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M_BYE',
                            'next_match_slot' => 1,
                            'note'            => 'Pemenang Lolos ke Babak 2 (Menang Bye ke Final)',
                        ],
                        [
                            'id'              => 'R1_M1',
                            'round'           => 1,
                            'round_name'      => 'Babak I',
                            'match_number'    => 2,
                            'player1'         => $participants[2],
                            'player2'         => $participants[3],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M0',
                            'next_match_slot' => 1,
                            'note'            => 'Pemenang Melawan ' . ($participants[4]['name'] ?? 'Peserta 5') . ' di Semi Final',
                        ],
                        [
                            'id'              => 'R1_M_BYE',
                            'round'           => 1,
                            'round_name'      => 'Babak I',
                            'match_number'    => 'BYE',
                            'player1'         => $participants[4],
                            'player2'         => $byeDummy,
                            'score1'          => 1,
                            'score2'          => 0,
                            'winner'          => $participants[4],
                            'status'          => 'completed',
                            'is_bye'          => true,
                            'next_match_id'   => 'R2_M0',
                            'next_match_slot' => 2,
                            'note'            => 'Menang Bye Babak 1 (Lolos ke Semi Final)',
                        ],
                    ]
                ],
                2 => [
                    'round'   => 2,
                    'name'    => 'Semi Final',
                    'matches' => [
                        [
                            'id'              => 'R2_M_BYE',
                            'round'           => 2,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 'BYE',
                            'player1'         => null, // Winner of R1_M0
                            'player2'         => $byeDummy,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'is_bye'          => true,
                            'next_match_id'   => 'R3_M0',
                            'next_match_slot' => 1,
                            'note'            => 'Menang Bye Ronde 2 (Lolos ke Final)',
                        ],
                        [
                            'id'              => 'R2_M0',
                            'round'           => 2,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 3,
                            'player1'         => null, // Winner of R1_M1
                            'player2'         => $participants[4], // E (menang bye dari babak 1)
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R3_M0',
                            'next_match_slot' => 2,
                            'note'            => 'Pemenang Lolos ke Final melawan Pemenang Bye (A/B)',
                        ]
                    ]
                ],
                3 => [
                    'round'   => 3,
                    'name'    => 'Final (Bye vs Pemenang Semifinal)',
                    'matches' => [
                        [
                            'id'              => 'R3_M0',
                            'round'           => 3,
                            'round_name'      => 'Final (Bye vs Pemenang Semifinal)',
                            'match_number'    => 4,
                            'player1'         => null, // From R2_M_BYE (Winner R1_M0)
                            'player2'         => null, // From R2_M0 (Winner Semifinal)
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => null,
                            'next_match_slot' => null,
                            'note'            => 'Perebutan Juara 1 & 2 (Pemenang Bye vs Pemenang Semifinal)',
                        ]
                    ]
                ]
            ];
            return [
                'total_rounds' => 3,
                'bracket_size' => 5,
                'rounds'       => $rounds,
                'podium'       => ['champion' => null, 'runner_up' => null],
            ];
        }

        // 6 PARTICIPANTS
        if ($N === 6) {
            $rounds = [
                1 => [
                    'round'   => 1,
                    'name'    => 'Babak Penyisihan',
                    'matches' => [
                        [
                            'id'              => 'R1_M0',
                            'round'           => 1,
                            'round_name'      => 'Babak Penyisihan',
                            'match_number'    => 1,
                            'player1'         => $participants[2],
                            'player2'         => $participants[3],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M0',
                            'next_match_slot' => 2,
                        ],
                        [
                            'id'              => 'R1_M1',
                            'round'           => 1,
                            'round_name'      => 'Babak Penyisihan',
                            'match_number'    => 2,
                            'player1'         => $participants[4],
                            'player2'         => $participants[5],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M1',
                            'next_match_slot' => 2,
                        ],
                    ]
                ],
                2 => [
                    'round'   => 2,
                    'name'    => 'Semi Final',
                    'matches' => [
                        [
                            'id'              => 'R2_M0',
                            'round'           => 2,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 3,
                            'player1'         => $participants[0],
                            'player2'         => null,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R3_M0',
                            'next_match_slot' => 1,
                        ],
                        [
                            'id'              => 'R2_M1',
                            'round'           => 2,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 4,
                            'player1'         => $participants[1],
                            'player2'         => null,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R3_M0',
                            'next_match_slot' => 2,
                        ],
                    ]
                ],
                3 => [
                    'round'   => 3,
                    'name'    => 'Final',
                    'matches' => [
                        [
                            'id'              => 'R3_M0',
                            'round'           => 3,
                            'round_name'      => 'Final',
                            'match_number'    => 5,
                            'player1'         => null,
                            'player2'         => null,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => null,
                            'next_match_slot' => null,
                        ]
                    ]
                ]
            ];
            return [
                'total_rounds' => 3,
                'bracket_size' => 8,
                'rounds'       => $rounds,
                'podium'       => ['champion' => null, 'runner_up' => null],
            ];
        }

        // 7 PARTICIPANTS
        if ($N === 7) {
            $rounds = [
                1 => [
                    'round'   => 1,
                    'name'    => 'Babak Penyisihan',
                    'matches' => [
                        [
                            'id'              => 'R1_M0',
                            'round'           => 1,
                            'round_name'      => 'Babak Penyisihan',
                            'match_number'    => 1,
                            'player1'         => $participants[1],
                            'player2'         => $participants[2],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M0',
                            'next_match_slot' => 2,
                        ],
                        [
                            'id'              => 'R1_M1',
                            'round'           => 1,
                            'round_name'      => 'Babak Penyisihan',
                            'match_number'    => 2,
                            'player1'         => $participants[3],
                            'player2'         => $participants[4],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M1',
                            'next_match_slot' => 1,
                        ],
                        [
                            'id'              => 'R1_M2',
                            'round'           => 1,
                            'round_name'      => 'Babak Penyisihan',
                            'match_number'    => 3,
                            'player1'         => $participants[5],
                            'player2'         => $participants[6],
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R2_M1',
                            'next_match_slot' => 2,
                        ],
                    ]
                ],
                2 => [
                    'round'   => 2,
                    'name'    => 'Semi Final',
                    'matches' => [
                        [
                            'id'              => 'R2_M0',
                            'round'           => 2,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 4,
                            'player1'         => $participants[0],
                            'player2'         => null,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R3_M0',
                            'next_match_slot' => 1,
                        ],
                        [
                            'id'              => 'R2_M1',
                            'round'           => 2,
                            'round_name'      => 'Semi Final',
                            'match_number'    => 5,
                            'player1'         => null,
                            'player2'         => null,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => 'R3_M0',
                            'next_match_slot' => 2,
                        ],
                    ]
                ],
                3 => [
                    'round'   => 3,
                    'name'    => 'Final',
                    'matches' => [
                        [
                            'id'              => 'R3_M0',
                            'round'           => 3,
                            'round_name'      => 'Final',
                            'match_number'    => 6,
                            'player1'         => null,
                            'player2'         => null,
                            'score1'          => null,
                            'score2'          => null,
                            'winner'          => null,
                            'status'          => 'pending',
                            'next_match_id'   => null,
                            'next_match_slot' => null,
                        ]
                    ]
                ]
            ];
            return [
                'total_rounds' => 3,
                'bracket_size' => 8,
                'rounds'       => $rounds,
                'podium'       => ['champion' => null, 'runner_up' => null],
            ];
        }

        // DEFAULT FOR 8, 16, 32 PARTICIPANTS
        $P = 1;
        while ($P < $N) {
            $P *= 2;
        }
        $totalRounds = (int) log($P, 2);
        $rounds = [];
        $matchNumber = 1;

        $r1Matches = [];
        $half = (int) ($P / 2);
        for ($i = 0; $i < $half; $i++) {
            $p1 = $participants[$i * 2] ?? null;
            $p2 = $participants[$i * 2 + 1] ?? null;
            $r1Matches[] = [
                'id'              => 'R1_M' . $i,
                'round'           => 1,
                'round_name'      => ($totalRounds === 3 ? 'Perempat Final (8 Besar)' : 'Babak 1'),
                'match_number'    => $matchNumber++,
                'player1'         => $p1,
                'player2'         => $p2,
                'score1'          => null,
                'score2'          => null,
                'winner'          => null,
                'status'          => 'pending',
                'next_match_id'   => 'R2_M' . ((int) floor($i / 2)),
                'next_match_slot' => ($i % 2 === 0 ? 1 : 2),
            ];
        }
        $rounds[1] = ['round' => 1, 'name' => ($totalRounds === 3 ? 'Perempat Final (8 Besar)' : 'Babak 1'), 'matches' => $r1Matches];

        for ($r = 2; $r <= $totalRounds; $r++) {
            $mCount = count($rounds[$r - 1]['matches']) / 2;
            $rMatches = [];
            $rName = ($r === $totalRounds ? 'Final' : ($r === $totalRounds - 1 ? 'Semi Final' : 'Babak ' . $r));
            for ($i = 0; $i < $mCount; $i++) {
                $nextId = ($r < $totalRounds) ? ('R' . ($r + 1) . '_M' . (int) floor($i / 2)) : null;
                $rMatches[] = [
                    'id'              => 'R' . $r . '_M' . $i,
                    'round'           => $r,
                    'round_name'      => $rName,
                    'match_number'    => $matchNumber++,
                    'player1'         => null,
                    'player2'         => null,
                    'score1'          => null,
                    'score2'          => null,
                    'winner'          => null,
                    'status'          => 'pending',
                    'next_match_id'   => $nextId,
                    'next_match_slot' => ($i % 2 === 0 ? 1 : 2),
                ];
            }
            $rounds[$r] = ['round' => $r, 'name' => $rName, 'matches' => $rMatches];
        }

        return [
            'total_rounds' => $totalRounds,
            'bracket_size' => $P,
            'rounds'       => $rounds,
            'podium'       => ['champion' => null, 'runner_up' => null],
        ];
    }

    /**
     * Calculate Podium (Juara 1, 2, 3) from bracket state
     */
    protected function calculatePodium(array $rounds, int $totalRounds): array
    {
        $finalMatch = $rounds[$totalRounds]['matches'][0] ?? null;
        $champion = null;
        $runnerUp = null;

        if ($finalMatch && ! empty($finalMatch['winner'])) {
            $champion = $finalMatch['winner'];
            if (! empty($finalMatch['player1']) && ! empty($finalMatch['player2'])) {
                $runnerUp = ($finalMatch['winner']['name'] === $finalMatch['player1']['name'])
                    ? $finalMatch['player2']
                    : $finalMatch['player1'];
            }
        }

        return [
            'champion'    => $champion,
            'runner_up'   => $runnerUp,
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

        shuffle($participants);

        foreach ($participants as $idx => &$p) {
            $p['seed'] = $idx + 1;
        }
        unset($p);

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
     * Update match result (score and winner) and cascade to designated target match
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

        // Cascade to next match (support cascading through automatic BYE cards)
        $nextMatchId = $match['next_match_id'] ?? null;
        $nextSlot = $match['next_match_slot'] ?? 1;

        $cascadeQueue = [];
        if ($nextMatchId) {
            $cascadeQueue[] = [
                'id'     => $nextMatchId,
                'slot'   => $nextSlot,
                'player' => $selectedWinner,
            ];
        }

        while (! empty($cascadeQueue)) {
            $item = array_shift($cascadeQueue);
            $cId = $item['id'];
            $cSlot = $item['slot'];
            $cPlayer = $item['player'];

            foreach ($rounds as $rIdx => &$rd) {
                foreach ($rd['matches'] as &$targetM) {
                    if ($targetM['id'] === $cId) {
                        $oldPlayer = ($cSlot === 1) ? ($targetM['player1'] ?? null) : ($targetM['player2'] ?? null);

                        if ($cSlot === 1) {
                            $targetM['player1'] = $cPlayer;
                        } else {
                            $targetM['player2'] = $cPlayer;
                        }

                        if ($oldPlayer !== null && ($cPlayer === null || ($oldPlayer['name'] ?? '') !== ($cPlayer['name'] ?? ''))) {
                            $this->clearDownstreamMatches($rounds, $targetM['id']);
                        }

                        // If target match is an automatic BYE match (e.g. R2_M_BYE), automatically advance its winner
                        if (! empty($targetM['is_bye'])) {
                            $targetM['winner'] = $targetM['player1'];
                            $targetM['status'] = (! empty($targetM['player1'])) ? 'completed' : 'pending';

                            if (! empty($targetM['next_match_id'])) {
                                $cascadeQueue[] = [
                                    'id'     => $targetM['next_match_id'],
                                    'slot'   => $targetM['next_match_slot'] ?? 1,
                                    'player' => $targetM['winner'],
                                ];
                            }
                        }

                        break 2;
                    }
                }
                unset($targetM);
            }
            unset($rd);
        }

        $bracket['rounds'] = $rounds;
        $bracket['podium'] = $this->calculatePodium($rounds, $totalRounds);

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
    protected function clearDownstreamMatches(array &$rounds, string $startMatchId): void
    {
        $queue = [$startMatchId];

        while (! empty($queue)) {
            $mId = array_shift($queue);

            foreach ($rounds as &$rd) {
                foreach ($rd['matches'] as &$m) {
                    if ($m['id'] === $mId) {
                        $m['score1'] = null;
                        $m['score2'] = null;
                        $m['winner'] = null;
                        $m['status'] = 'pending';

                        if (! empty($m['next_match_id'])) {
                            $nextMId = $m['next_match_id'];
                            $slot = $m['next_match_slot'] ?? 1;

                            foreach ($rounds as &$rd2) {
                                foreach ($rd2['matches'] as &$targetM) {
                                    if ($targetM['id'] === $nextMId) {
                                        if ($slot === 1) {
                                            $targetM['player1'] = null;
                                        } else {
                                            $targetM['player2'] = null;
                                        }
                                        $queue[] = $nextMId;
                                    }
                                }
                                unset($targetM);
                            }
                            unset($rd2);
                        }
                    }
                }
                unset($m);
            }
            unset($rd);
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
