<?php

/**
 * A* search algorithm implementation.
 */
class Cli_Model_Astar extends Cli_Model_Heuristics
{

    /**
     * The set of nodes already evaluated.
     *
     * @var array
     */
    private $_close = array();

    /**
     * The set of tentative nodes to be evaluated
     *
     * @var array
     */
    private $_open = array();

    /**
     * Number of loops
     *
     * @var int
     */
    private $nr = 0;

    /**
     * @var Cli_Model_Fields
     */
    private $_fields;
    /**
     * @var Cli_Model_TerrainTypes
     */
    private $_terrain;
    private $limit;
    private $myCastleId = array();
    private $movementType;

    /**
     * @var Cli_Model_Army
     */
    private $_army;
    private $_movesLeft;
    private $_color;
    /**
     * @var Cli_Model_Players
     */
    private $_players;

    /**
     * Cli_Model_Astar constructor.
     * @param Cli_Model_Army $army
     * @param $destX
     * @param $destY
     * @param $game
     * @param null $params
     */
    public function __construct(Cli_Model_Army $army, $destX, $destY, $game, $params = null)
    {
        parent::__construct($destX, $destY);

        if (isset($params['limit'])) {
            $this->limit = $params['limit'];
        }
        $this->_fields = $game->getFields();
        $this->_terrain = $game->getTerrain();
        $this->_movesLeft = $army->getMovesLeft();
        $this->_color = $army->getColor();
        $this->_army = $army;
        $this->_players = $game->getPlayers();

        $this->movementType = $army->getMovementType();

        $this->init();
        $this->aStar();
    }

    private function init()
    {
        $x = $this->_army->getX();
        $y = $this->_army->getY();

        if ($castleId = $this->_fields->isPlayerCastle($this->_color, $x, $y)) {
            $this->myCastleId[$castleId] = true;
        }

        $this->_open[$x . '_' . $y] = $this->node($x, $y, 0, null, 'c');
    }

    /**
     * A* algorithm
     *
     * @throws Exception on too many loops
     * @return bool
     */
    private function aStar()
    {
        $this->nr++;
        if ($this->nr > 30000) {
            $this->nr--;
            throw new Exception('>' + $this->nr);
        }
        $key = $this->findSmallestF();
        $x = $this->_open[$key]['x'];
        $y = $this->_open[$key]['y'];
        $this->_close[$key] = $this->_open[$key];
        if ($x == $this->_destX && $y == $this->_destY) {
            return;
        }
        unset($this->_open[$key]);
        $this->addOpen($x, $y);
        if ($this->isEmpty()) {
            echo 'Nie znalazłem ścieżki' . "\n";
            return;
//            throw new Exception('Nie znalazłem ścieżki');
        }
        $this->aStar();
    }

    /**
     * Counts open set
     *
     * @return int
     */
    private function isEmpty()
    {
        return !count($this->_open);
    }

    /**
     * Finds smallest cost to goal
     *
     * @return int
     */
    private function findSmallestF()
    {
        $i = 0;
        foreach (array_keys($this->_open) as $k) {
            if (!isset($this->_open[$i])) {
                $i = $k;
            }
            if ($this->_open[$k]['F'] < $this->_open[$i]['F']) {
                $i = $k;
            }
        }
        return $i;
    }

    /**
     * Adds nodes which are around $x,$y to the open set
     *
     * @param int $x
     * @param int $y
     */
    private function addOpen($x, $y)
    {
        $startX = $x - 1;
        $startY = $y - 1;
        $endX = $x + 1;
        $endY = $y + 1;
        for ($i = $startX; $i <= $endX; $i++) {
            for ($j = $startY; $j <= $endY; $j++) {

                if ($x == $i && $y == $j) {
                    continue;
                }

                $key = $i . '_' . $j;

                if (isset($this->_close[$key]) && $this->_close[$key]['x'] == $i && $this->_close[$key]['y'] == $j) {
                    continue;
                }

                $terrainType = $this->_fields->getAStarType($i, $j, $this->_color, $this->_destX, $this->_destY, $this->_players);
                if (!$terrainType) {
                    continue;
                }

                // jeżeli na polu znajduje się wróg to pomiń to pole
                if ($terrainType == 'e') {
                    continue;
                }

                $g = $this->_terrain->getTerrainType($terrainType)->getCost($this->movementType);

                // jeżeli koszt ruchu większy od 6 to pomiń to pole
                if ($g > 6) {
                    continue;
                }

                if (isset($this->_open[$key])) {
                    $this->calculatePath($x . '_' . $y, $g, $key);
                } else {
                    $g += $this->_close[$x . '_' . $y]['G'];
                    // pomiń jeśli koszt ścieżki jest większy od pozostałych ruchów
                    if ($this->limit && $g > $this->_movesLeft) {
                        continue;
                    }
                    $parent = array(
                        'x' => $x,
                        'y' => $y
                    );
                    $this->_open[$key] = $this->node($i, $j, $g, $parent, $terrainType);
                }
            }
        }
    }

    /**
     * Calculates path cost
     *
     * @param string $kA
     * @param int $g
     * @param string $key
     */
    private function calculatePath($kA, $g, $key)
    {
        if ($this->_open[$key]['G'] > ($g + $this->_close[$kA]['G'])) {
            $this->_open[$key]['parent'] = array(
                'x' => $this->_close[$kA]['x'],
                'y' => $this->_close[$kA]['y']
            );
            $this->_open[$key]['G'] = $g + $this->_close[$kA]['G'];
            $this->_open[$key]['F'] = $this->_open[$key]['G'] + $this->_open[$key]['H'];
        }
    }

    /**
     *
     * @param integer $x
     * @param integer $y
     * @param integer $g
     * @param array $parent
     * @param string $terrainType
     * @return array
     */
    private function node($x, $y, $g, $parent, $terrainType)
    {
        $h = $this->calculateH($x, $y);
        return array(
            'x' => $x,
            'y' => $y,
            'G' => $g,
            'H' => $h,
            'F' => $h + $g,
            'parent' => $parent,
            't' => $terrainType
        );
    }

    /**
     * @return Cli_Model_Path
     */
    public function path()
    {
        $i = 0;
        $key = $this->_destX . '_' . $this->_destY;
        $path = $this->returnPath($key);

        if (is_array($path)) {
            $path = array_reverse($path);

            foreach ($path as $k => $step) {
                if ($step['t'] == 'c') {
                    $castleId = $this->_fields->isPlayerCastle($this->_color, $step['x'], $step['y']);
                    if (isset($this->myCastleId[$castleId])) {
                        $path[$k]['cc'] = true;
                        $i++;
                    } else {
                        $this->myCastleId[$castleId] = true;
                    }
                }
                $path[$k]['F'] -= $i;
                $path[$k]['G'] -= $i;
            }
        }
        return new Cli_Model_Path($path, $this->_army, $this->_terrain);
    }

    /**
     *
     *
     * @param string $key
     * @param type $moves
     * @return int
     */
    public function returnPath($key)
    {
        if (!isset($this->_close[$key])) {
            $l = new Coret_Model_Logger();
            $l->log('W ścieżce nie ma podanego jako parametr klucza: ' . $key . ' (getPath)');
            return;
        }
        $path = array();
        // stoję w pozycji docelowej
        if (empty($this->_close[$key]['parent'])) {
            $path[] = $this->_close[$key];
            return $path;
        }
        while (!empty($this->_close[$key]['parent'])) {
            $path[] = $this->_close[$key];
            $key = $this->_close[$key]['parent']['x'] . '_' . $this->_close[$key]['parent']['y'];
        }
        return $path;
    }

    public function inRange()
    {
        return $this->path()->enemyInRange(); //todo zmienić na zasięg jednostek a nie armii
    }
}

