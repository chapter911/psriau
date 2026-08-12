<?php

namespace App\Models;

use CodeIgniter\Model;

class GateballMatchModel extends Model
{
    protected $table            = 'gateball_matches';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'category',
        'match_number',
        'team1',
        'team2',
        'score1',
        'score2',
        'timer_seconds',
        'timer_status',
        'timer_started_at',
        'score_details_json',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public static array $defaultTeams = [
        'PS',
        'BWSS III',
        'BPBPK',
        'BP2JK',
        'BPJN',
    ];

    /**
     * Normalize team name alias (e.g. BWS to BWSS III)
     */
    public static function normalizeTeamName(string $name): string
    {
        $name = trim($name);
        $upper = strtoupper($name);

        if ($upper === 'BWS' || $upper === 'BWS III' || $upper === 'BWSS III' || $upper === 'BWSS3') {
            return 'BWSS III';
        }

        return $upper;
    }

    /**
     * Get matches list for category ordered by match_number
     */
    public function getMatchesByCategory(string $category): array
    {
        return $this->where('category', $category)
            ->orderBy('match_number', 'ASC')
            ->findAll();
    }

    /**
     * Calculate standings for category
     */
    public function getStandings(string $category): array
    {
        $matches = $this->where('category', $category)->findAll();

        $standings = [];
        foreach (self::$defaultTeams as $team) {
            $standings[$team] = [
                'team'   => $team,
                'played' => 0,
                'm'      => 0, // Menang
                'k'      => 0, // Kalah
                's'      => 0, // Seri
                'gm'     => 0, // Memasukkan
                'gk'     => 0, // Kemasukan
                'score'  => 0, // Selisih Skor (Memasukkan - Kemasukan)
                'point'  => 0, // Total Poin
            ];
        }

        foreach ($matches as $match) {
            $score1 = $match['score1'];
            $score2 = $match['score2'];

            // Match is completed if both scores are not null
            if ($score1 !== null && $score2 !== null && $match['status'] === 'completed') {
                $t1 = self::normalizeTeamName($match['team1']);
                $t2 = self::normalizeTeamName($match['team2']);

                if (! isset($standings[$t1])) {
                    $standings[$t1] = [
                        'team'   => $t1,
                        'played' => 0,
                        'm'      => 0,
                        'k'      => 0,
                        's'      => 0,
                        'gm'     => 0,
                        'gk'     => 0,
                        'score'  => 0,
                        'point'  => 0,
                    ];
                }
                if (! isset($standings[$t2])) {
                    $standings[$t2] = [
                        'team'   => $t2,
                        'played' => 0,
                        'm'      => 0,
                        'k'      => 0,
                        's'      => 0,
                        'gm'     => 0,
                        'gk'     => 0,
                        'score'  => 0,
                        'point'  => 0,
                    ];
                }

                $s1 = (int) $score1;
                $s2 = (int) $score2;

                $standings[$t1]['played']++;
                $standings[$t2]['played']++;

                $standings[$t1]['gm'] += $s1;
                $standings[$t1]['gk'] += $s2;
                $standings[$t2]['gm'] += $s2;
                $standings[$t2]['gk'] += $s1;

                if ($s1 > $s2) {
                    $standings[$t1]['m']++;
                    $standings[$t1]['point'] += 3;
                    $standings[$t2]['k']++;
                } elseif ($s1 < $s2) {
                    $standings[$t2]['m']++;
                    $standings[$t2]['point'] += 3;
                    $standings[$t1]['k']++;
                } else {
                    $standings[$t1]['s']++;
                    $standings[$t1]['point'] += 1;
                    $standings[$t2]['s']++;
                    $standings[$t2]['point'] += 1;
                }
            }
        }

        // Calculate final score difference
        foreach ($standings as &$item) {
            $item['score'] = $item['gm'] - $item['gk'];
        }
        unset($item);

        // Default priority order as shown in official banner
        $defaultOrder = [
            'PS'       => 1,
            'BWSS III' => 2,
            'BPBPK'    => 3,
            'BP2JK'    => 4,
            'BPJN'     => 5,
        ];

        // Sort standings: Point DESC -> Score difference DESC -> Total GM DESC -> Default Order ASC
        $list = array_values($standings);
        usort($list, function ($a, $b) use ($defaultOrder) {
            if ($b['point'] !== $a['point']) {
                return $b['point'] <=> $a['point'];
            }
            if ($b['score'] !== $a['score']) {
                return $b['score'] <=> $a['score'];
            }
            if ($b['gm'] !== $a['gm']) {
                return $b['gm'] <=> $a['gm'];
            }
            $orderA = $defaultOrder[$a['team']] ?? 99;
            $orderB = $defaultOrder[$b['team']] ?? 99;
            return $orderA <=> $orderB;
        });

        // Add rank number (1-5)
        foreach ($list as $idx => &$row) {
            $row['rank'] = $idx + 1;
        }
        unset($row);

        return $list;
    }
}
