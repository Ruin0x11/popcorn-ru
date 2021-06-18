<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Generator;

/**
 * @ORM\Table(name="shows")
 * @ORM\Entity(repositoryClass="App\Repository\ShowRepository")
 *
 */
class Show extends BaseShow
{
    public function __construct()
    {
        parent::__construct();
    }
}
