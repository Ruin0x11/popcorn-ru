<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Generator;

/**
 * @ORM\Table(name="animes")
 * @ORM\Entity(repositoryClass="App\Repository\AnimeRepository")
 *
 */
class Anime extends BaseShow
{
    public function __construct()
    {
        parent::__construct();
    }

    //<editor-fold desc="Show Api Data">
    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $kitsu;
    public function getKitsu() { return $this->kitsu; }
    public function setKitsu($kitsu) { $this->kitsu = $kitsu; return $this;}

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $mal;
    public function getMal() { return $this->mal; }
    public function setMal($mal) { $this->mal = $mal; return $this;}

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $type;
    public function getType() { return $this->type; }
    public function setType($type) { $this->type = $type; return $this;}

    //</editor-fold>
}
