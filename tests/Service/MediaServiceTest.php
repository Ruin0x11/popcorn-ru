<?php

namespace App\Tests\Service;

use App\Service\MediaService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaServiceTest extends KernelTestCase
{
    public function testFetchAnimeShowTvdb()
    {
        self::bootKernel();

        $container = self::$container;

        $mediaService = $container->get(MediaService::class);

        $anime = $mediaService->fetchAnime("290");

        $this->assertEquals("tt0433722", $anime->getImdb());
        $this->assertEquals("75092", $anime->getTvdb());
        $this->assertEquals("paranoia-agent", $anime->getSlug());
        $this->assertEquals("JP", $anime->getCountry());
        $this->assertEquals("ANIMAX", $anime->getNetwork());
        $this->assertEquals("", $anime->getAirDay());
        $this->assertEquals("", $anime->getAirTime());
        $this->assertEquals("finished", $anime->getStatus());
        $this->assertEquals("1", $anime->getNumSeasons());
        $this->assertEquals(1084838400, $anime->getLastUpdated());

        $this->assertEquals("290", $anime->getKitsu());
        $this->assertEquals("323", $anime->getMal());
        $this->assertEquals("show", $anime->getType());
    }
}
