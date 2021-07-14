<?php

namespace tests\Integration\Setup\Migrations;

use OCP\IDBConnection;
use OCP\AppFramework\App;
use OCP\AppFramework\IAppContainer;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OC\DB\SchemaWrapper;
use PHPUnit\Framework\TestCase;

class Version000000Date20210701093123Test extends TestCase {
    
    /**
     * @var IAppContainer
     */
    private $container;
    
    /**
     * @var IDBConnection
     */
    private $db;
    
    public function setUp(): void {
        resetEnvironmentToBackup('default');
        
        parent::setUp();
        
        $app = new App('cookbook');
        $this->container = $app->getContainer();
        
        /**
         * @var IDBConnection $db
         */
        $this->db = $this->container->query(IDBConnection::class);
        $this->assertIsObject($this->db);
        /**
         * @var SchemaWrapper $schema
         */
        $schema = $this->container->query(SchemaWrapper::class);
        $this->assertIsObject($schema);
        
        // undo all migrations of cookbook app
        $qb = $this->db->getQueryBuilder();
        $numRows = $qb->delete('migrations')
            ->where('app=:app')
            ->setParameter('app', 'cookbook')
            ->execute();
        $this->assertGreaterThan(0, $numRows);
        
        $schema->dropTable('cookbook_names');
        $this->assertFalse($schema->hasTable('cookbook_names'));
        $schema->dropTable('cookbook_categories');
        $this->assertFalse($schema->hasTable('cookbook_categories'));
        $schema->dropTable('cookbook_keywords');
        $this->assertFalse($schema->hasTable('cookbook_keywords'));
        
        $schema->performDropTableCalls();
        
        // Reinstall app partially (just before the migration)
        runOCCCommand(['migration:migrate', 'cookbook', '000000Date20210427082010']);
    }
    
    /**
     * @dataProvider dataProvider
     * @runInSeparateProcess
     */
    public function testRedundantEntriesInDB($data, $updatedUsers) {
        // Add dummy data
        $qb = $this->db->getQueryBuilder();
        $qb->insert('cookbook_names')
            ->values([
                'recipe_id' => ':recipe',
                'user_id' => ':user',
                'name' => ':name',
            ]);
        $qb->setParameter('name', 'name of the recipe');
        foreach ($data as $d) {
            $qb->setParameter('user', $d[0]);
            $qb->setParameter('recipe', $d[1]);
            
            $this->assertEquals(1, $qb->execute());
        }
        
        // Set configuration values
        $current = time();
        
        $qb = $this->db->getQueryBuilder();
        $qb->insert('preferences')
            ->values([
                'userid' => ':user',
                'appid' => ':appid',
                'configkey' => ':property',
                'configvalue' => ':value',
            ]);
        
        $qb->setParameter('value', $current, IQueryBuilder::PARAM_STR);
        $qb->setParameter('appid', 'cookbook');
        $qb->setParameter('property', 'last_index_update');
        
        $users = array_unique(array_map(function ($x) {return $x[0];}, $data));
        foreach($users as $u){
            $qb->setParameter('user', $u);
            $this->assertEquals(1, $qb->execute());
        }
        
        runOCCCommand(['migration:migrate', 'cookbook', '000000Date20210701093123']);
        
        $qb = $this->db->getQueryBuilder();
        $qb->select('userid', 'configvalue')
            ->from('preferences')
            ->where(
                'appid = :appid',
                'configkey = :property'
                );
        $qb->setParameter('appid', 'cookbook');
        $qb->setParameter('property', 'last_index_update');
        
        $cursor = $qb->execute();
        $result = $cursor->fetchAll();
        
        $result = array_filter($result, function ($x) use ($current) { return $x < $current; });
        sort($result);
        sort($updatedUsers);
        
        $this->assertEquals($updatedUsers, $result);
        
        $this->markTestIncomplete('Not yet implemented');
    }
    
    public function dataProvider() {
        return [
            'caseA' => [
                [
                    ['alice', 123],
                    ['alice', 124],
                    ['bob', 125]
                ],
                [],
            ],
            'caseB' => [
                [
                    ['alice', 123],
                    ['alice', 124],
                    ['bob', 124],
                    ['bob', 125],
                ],
                [],
            ],
            'caseC' => [
                [
                    ['alice', 123],
                    ['alice', 124],
                    ['bob', 124],
                    ['bob', 124],
                    ['bob', 125],
                ],
                ['bob']
            ],
        ];
    }
}
