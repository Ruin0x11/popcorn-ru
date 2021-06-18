<?php

namespace App\Tests\Spider;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use App\Spider\NyaaSi;
use App\Spider\Dto\TopicDto;
use App\Entity\Torrent\ShowTorrent;
use VCR\VCR;

final class NyaaSiTest extends SpiderTestCase
{
    public function testNyaaSiGetTopic(): void
    {
        $spider = $this->getService(NyaaSi::class);

        $topic = new TopicDto("/view/848234", 10, 3, 10);

        VCR::insertCassette("nyaaSiGetTopic");
        $spider->getTopic($topic);
        VCR::eject();

        $entityManager = $this->getService('doctrine')->getManager();
        $torrentRepo = $entityManager->getRepository(ShowTorrent::class);

        $torrent = $torrentRepo->findOneBy(["provider" => "NyaaSi"]);
        \Doctrine\Common\Util\Debug::dump($torrent);

        $this->assertEquals("55.36 GB", $torrent->getFilesize());
        $this->assertEquals("en", $torrent->getSeed());
        $this->assertEquals(10, $torrent->getSeed());
        $this->assertEquals(13, $torrent->getPeer());
    }
}
