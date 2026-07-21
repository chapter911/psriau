<?php
$file = 'app/Controllers/Admin/Dashboard.php';
$content = file_get_contents($file);

// The correct function with fetch by ID
$newFunc = <<<'FUNC'
    public function mapContourData()
    {
        $db = db_connect();

        if (! $db->tableExists('mst_kontur_geojson')) {
            return $this->response->setJSON([
                'status'  => 'ok',
                'geojson' => ['type' => 'FeatureCollection', 'features' => []],
                'meta'    => ['message' => 'Tabel kontur belum tersedia.'],
            ]);
        }

        $zoom  = (int) $this->request->getGet('zoom');
        $south = (float) $this->request->getGet('south');
        $west  = (float) $this->request->getGet('west');
        $north = (float) $this->request->getGet('north');
        $east  = (float) $this->request->getGet('east');

        ini_set('memory_limit', '512M');

        try {
            if ($zoom >= 14) {
                $detailLevel = 'detail';
            } elseif ($zoom >= 12) {
                $detailLevel = 'medium';
            } else {
                $detailLevel = 'overview';
            }

            $builder = $db->table('mst_kontur_geojson')
                ->select('id')
                ->where('detail_level', $detailLevel);

            if ($south != 0 || $west != 0 || $north != 0 || $east != 0) {
                $builder
                    ->where('bbox_max_lat >=', $south)
                    ->where('bbox_min_lat <=', $north)
                    ->where('bbox_max_lng >=', $west)
                    ->where('bbox_min_lng <=', $east);
            }

            $ids = $builder->get()->getResultArray();
            
            $allFeatures = [];
            $totalCount  = 0;
            $hardLimit = 15000; 

            foreach ($ids as $idRow) {
                $row = $db->table('mst_kontur_geojson')->select('geojson_data')->where('id', $idRow['id'])->get()->getRowArray();
                if (empty($row['geojson_data'])) continue;

                $data = json_decode($row['geojson_data'], true);
                if ($data && isset($data['features']) && is_array($data['features'])) {
                    foreach ($data['features'] as $feature) {
                        $coords = $feature['geometry']['coordinates'] ?? [];
                        $geomType = $feature['geometry']['type'] ?? '';

                        $inBounds = false;
                        if ($south == 0 && $west == 0 && $north == 0 && $east == 0) {
                            $inBounds = true;
                        } else {
                            if ($geomType === 'LineString') {
                                foreach ($coords as $pt) {
                                    if ($pt[1] >= $south && $pt[1] <= $north && $pt[0] >= $west && $pt[0] <= $east) {
                                        $inBounds = true;
                                        break;
                                    }
                                }
                            } elseif ($geomType === 'MultiLineString') {
                                foreach ($coords as $line) {
                                    foreach ($line as $pt) {
                                        if ($pt[1] >= $south && $pt[1] <= $north && $pt[0] >= $west && $pt[0] <= $east) {
                                            $inBounds = true;
                                            break 2;
                                        }
                                    }
                                }
                            }
                        }

                        if ($inBounds) {
                            $allFeatures[] = $feature;
                            $totalCount++;
                            if ($totalCount >= $hardLimit) {
                                break 2;
                            }
                        }
                    }
                }
                unset($data);
                unset($row);
            }

            return $this->response->setJSON([
                'status'  => 'ok',
                'geojson' => ['type' => 'FeatureCollection', 'features' => $allFeatures],
                'meta'    => [
                    'detail_level' => $detailLevel,
                    'count'        => $totalCount,
                ],
            ]);
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'status'  => 'error',
                'meta'    => ['message' => $e->getMessage() . ' at line ' . $e->getLine()],
            ]);
        }
    }
FUNC;

$content = preg_replace('/public function mapContourData\(\).*?private function getMapTypes/s', $newFunc . "\n\n    private function getMapTypes", $content);
file_put_contents($file, $content);
