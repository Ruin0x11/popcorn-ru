<?php

namespace App\Entity;

use App\Entity\VO\Images;
use App\Entity\VO\Rating;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MovieRepository")
 */
class Movie extends BaseMedia
{
    public function __construct()
    {
        parent::__construct();
        $this->torrents = new ArrayCollection();
    }

    /**
     * @var MovieTorrent[]
     * @ORM\OneToMany(targetEntity="App\Entity\MovieTorrent", fetch="EAGER", mappedBy="movie")
     * @ORM\OrderBy({"peer" = "ASC"})
     */
    protected $torrents;
    public function getTorrents() { return $this->torrents; }

    //<editor-fold desc="Movie Api Data">
    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     */
    protected $imdb;
    public function getImdb() { return $this->imdb; }
    public function setImdb($imdb) { $this->imdb = $imdb; return $this;}

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    protected $released;
    public function getReleased() { return $this->released; }
    public function setReleased($released) { $this->released = $released; return $this;}

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $trailer;
    public function getTrailer() { return $this->trailer; }
    public function setTrailer($trailer) { $this->trailer = $trailer; return $this;}

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $certification;
    public function getCertification() { return $this->certification; }
    public function setCertification($certification) { $this->certification = $certification; return $this;}
    //</editor-fold>
}
