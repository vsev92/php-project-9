<?php

use PHPUnit\Framework\TestCase;
use App\DIConfigurator;
use App\SiteDAO;

final class AnalyzerTest extends TestCase
{
    use PHPUnit\Framework\TestCase;
    use App\DIConfigurator;
    use App\DbConnector;
    use App\Site;
    
    private PDO $conn;
    protected function setUp(): void
    {
        $configurator = new DIConfigurator();
        $dbUrl = $configurator->getUrlForDbConnectorTest();
        $connector = new DbConnector($dbUrl);
        $this->conn = $connector->getConnection();
  
        $sql1 = "DELETE * FROM  url_checks";
        $sql2 = "DELETE * FROM  urls";
        $this->conn->exec("DELETE FROM url_checks");
        $this->conn->exec("DELETE FROM url");

    }


    public function testSiteIsUrlValid(): void
    {
       $this->assertFalse(Site::isUrlValid('ggggggg')); 
       $this->assertFalse(Site::isUrlValid('htt://ru.hexlet.io')); 
       $this->assertFalse(Site::isUrlValid('htt://ru.hexlet')); 
       $this->assertFalse(Site::isUrlValid('htt://ruhexletio')); 
       $this->assertFalse(Site::isUrlValid('https:/ru.hexlet.io')); 
       $this->assertFalse(Site::isUrlValid('https://ru.hexlet.io')); 
       $longUrl = 'https://ru.hexlet.io' . str_repeat('o', 236);
       $this->assertFalse(Site::isUrlValid($longUrl)); 
       $Url255 = 'https://ru.hexlet.io' . str_repeat('o', 235);
       $this->assertTrue(Site::isUrlValid($Url255));

    }


    public function testSiteDAO(): void
    {
        $siteDAO = new SiteDAO($this->conn);
        $site1 = new Site('https://ru.hexlet.io');
        $site2 = new Site('https://github.com');
        $site3 = new Site('https://mail.ru');

       
        $this->assertTrue($siteDAO->save($site1));
        $this->assertTrue($siteDAO->save($site2));
        $this->assertTrue($siteDAO->save($site3));

        $col = $siteDAO->getAll();
        $this->assertEquals(3, count($col));
        $this->assertObjectEquals($col[0], $site1);
        $this->assertObjectEquals($col[1], $site1);
        $this->assertObjectEquals($col[2], $site1);


        $siteByName = $siteDAO->findByName('https://ru.hexlet.io');
        $this->assertObjectEquals($siteByName, $site1);

    }

    
}