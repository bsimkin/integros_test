<?php

class ApiCest {    

    CONST EXPECTED_STATS = [
        'https://integros.com/test/b1' => 10, 'https://integros.com/test/b2' => 40, 'https://integros.com/test/b3' => 25, 'https://integros.com/test/b4' => 25
    ];

    private function getVastLink(ApiTester $I) {
        $I->haveHttpHeader('X-Integros-Key', getenv('API_KEY')); // API_KEY is not checked by backend
        $I->sendGET('/v1/adserver/publishers/' . getenv('PUBLISHER_ID') .  '/zones/' . getenv('ZONE_ID') . '/link');
        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $r = json_decode($I->grabResponse());
        return $r->result;
    }

    private function percentage_of($total, $sub) {
        return $sub*100/$total;
    }

    private function checkBannerStats(ApiTester $I, $iterations, $assetDelta) {
        $vastLink = $this->getVastLink($I);
        $banners = [];
        for ($i=0; $i < $iterations; $i++) {
            $I->sendGET($vastLink);
            $I->seeResponseIsXML();
            $vastXml = $I->grabResponse();
            $banner = $I->grabTextContentFromXmlElement('//ClickThrough', $vastXml);
            $banners[] = $banner;
        }

        $stats = array_count_values($banners);
        ksort($stats);
        $I->assertTrue(array_keys(self::EXPECTED_STATS) === array_keys($stats), 'compare expected and actual banners' . codecept_debug($stats));

        foreach ($stats as $key => $value) {
            codecept_debug('Total requests: ' .  $iterations . ' '  . $key . ' ' .  $this->percentage_of($iterations, $value) . ' %' . PHP_EOL);
            $I->assertEquals(self::EXPECTED_STATS[$key], $this->percentage_of($iterations, $value), $key, $assetDelta);
        }

    }

    public function test100Banners($I) {
        $this->checkBannerStats($I, 100, 10.0);
    }

    public function test1000Banners($I) {
        $this->checkBannerStats($I, 1000, 3.0);
    }

    public function test10000Banners($I) {
        $this->checkBannerStats($I, 10000, 1.0);
    }



}
