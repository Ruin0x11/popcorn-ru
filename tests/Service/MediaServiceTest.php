<?php

namespace App\Tests\Service;

use VCR\VCR;
use App\Service\MediaService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MediaServiceTest extends KernelTestCase
{
    public function testFetchByKitsuShowTvdb()
    {
        self::bootKernel();

        $container = self::$container;

        $mediaService = $container->get(MediaService::class);

        VCR::insertCassette("fetchByKitsuShowTvdb");
        $anime = $mediaService->fetchByKitsu("290");
        VCR::eject();

        $this->assertEquals("tt0433722", $anime->getImdb());
        $this->assertEquals("75092", $anime->getTvdb());
        $this->assertEquals("paranoia-agent", $anime->getSlug());
        $this->assertEquals("2004", $anime->getYear());
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

    public function testFetchByKitsuShowNoTvdbMapping()
    {
        self::bootKernel();

        $container = self::$container;

        $mediaService = $container->get(MediaService::class);

        VCR::insertCassette("fetchByKitsuShowNoTvdbMapping");
        $anime = $mediaService->fetchByKitsu("43620");
        VCR::eject();

        $this->assertEquals("tt13248076", $anime->getImdb());
        $this->assertEquals("390028", $anime->getTvdb());
        $this->assertEquals("wonder-egg-priority", $anime->getSlug());
        $this->assertEquals("2021", $anime->getYear());
        $this->assertEquals("JP", $anime->getCountry());
        $this->assertEquals("NTV", $anime->getNetwork());
        $this->assertEquals("", $anime->getAirDay());
        $this->assertEquals("", $anime->getAirTime());
        $this->assertEquals("finished", $anime->getStatus());
        $this->assertEquals("1", $anime->getNumSeasons());
        $this->assertEquals(1617148800, $anime->getLastUpdated());

        $this->assertEquals("43620", $anime->getKitsu());
        $this->assertEquals("43299", $anime->getMal());
        $this->assertEquals("show", $anime->getType());
    }

    public function testFetchByKitsuMovieNoTvdbMapping() {
        self::bootKernel();

        $container = self::$container;

        $mediaService = $container->get(MediaService::class);

        VCR::insertCassette("fetchByKitsuMovieNoTvdbMapping");
        $anime = $mediaService->fetchByKitsu("13618");
        VCR::eject();

        $this->assertEquals("tt7089878", $anime->getImdb());
        $this->assertEquals("", $anime->getTvdb());
        $this->assertEquals("liz-to-aoi-tori", $anime->getSlug());
        $this->assertEquals("2018", $anime->getYear());
        $this->assertEquals("JP", $anime->getCountry());
        $this->assertEquals("", $anime->getNetwork());
        $this->assertEquals("", $anime->getAirDay());
        $this->assertEquals("", $anime->getAirTime());
        $this->assertEquals("finished", $anime->getStatus());
        $this->assertEquals("1", $anime->getNumSeasons());
        $this->assertEquals(1524268800, $anime->getLastUpdated());

        $this->assertEquals("13618", $anime->getKitsu());
        $this->assertEquals("35677", $anime->getMal());
        $this->assertEquals("movie", $anime->getType());
    }
}
