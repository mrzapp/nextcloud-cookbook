<?php

namespace tests\Integration\Setup\Migrations;

use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use PHPUnit\Framework\TestCase;

class Version000000Date20210701093123Test extends TestCase {
    
    /**
     * @var IAppContainer
     */
    private $container;
    
    public function setUp(): void {
        parent::setUp();
        
        $app = new App('cookbook');
        $this->container = $app->getContainer();
    }
    
    /**
     * @dataProvider dataProvider
     * @runInSeparateProcess
     */
    public function testRedundantEntriesInDB($data, $updatedUsers) {
//         print_r($updatedUsers);
        sort($updatedUsers);
//         print_r($updatedUsers);
        
        $this->assertEquals($updatedUsers, []);
        
        $this->markTestIncomplete('Not yet implemented');
    }
    
    public function dataProvider() {
        return [
            'caseB' => [
                [
                ],
                [],
            ],
            'caseC' => [
                [
                ],
                ['bob']
            ],
        ];
    }
}
