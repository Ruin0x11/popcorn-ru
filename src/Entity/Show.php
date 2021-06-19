<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Generator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="shows")
 * @ORM\Entity(repositoryClass="App\Repository\ShowRepository")
 * @UniqueEntity(
 *   fields={"imdb"},
 *   errorPath="imdb",
 *   message="IMDB ID is already in use."
 * )
 */
class Show extends BaseShow
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getShowType() { return parent::TYPE_SHOW; }
}
