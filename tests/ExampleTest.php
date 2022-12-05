<?php
declare(strict_types=1);

use Example\Api\Api;
use Example\Utility\Random;
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    public function testOpenSession()
    {
        $api = new Api();
        $name =  "Device". Random::generateCharacters(10);
        $response = $api->openSession(array(
            "name" => $name,
            "platform" => 1,
            "version" => "1.0",
            "region" => "lt"
        ));
        $response = json_decode($response, true);
        $this->assertEquals("ok", $response['status'], "Failed to open session for player!".PHP_EOL. json_encode($response, JSON_PRETTY_PRINT));
        $this->assertTrue(isset($response['data'][0]['player-id']), "Player id is not set!");
        $playerId = $response['data'][0]['player-id'];
        $responseGet = json_decode($api->getPlayer(array("player-id" => $playerId)),true);
        $this->assertEquals("ok", $responseGet['status'], "Failed to get player!".PHP_EOL. json_encode($responseGet, JSON_PRETTY_PRINT));
        $this->assertEquals(1, $responseGet['data'][0]['platform'], "Platforms does not match!");
        $this->assertEquals("1.0", $responseGet['data'][0]['version'], "Versions does not match!");
        $this->assertEquals("lt", $responseGet['data'][0]['region'], "Regions does not match!");
        $this->assertEquals($name, $responseGet['data'][0]['device-id'], "Names does not match!");
        $responseDelete = json_decode($api->deletePlayer(array("player-id" => $playerId)),true);
        $this->assertEquals("ok", $responseDelete['status'], "Failed to delete player!".PHP_EOL. json_encode($responseDelete, JSON_PRETTY_PRINT));
        $responseGet = json_decode($api->getPlayer(array("player-id" => $playerId)),true);
        $this->assertEquals("fail", $responseGet['status'], "Deleted player can be get!".PHP_EOL. json_encode($responseGet, JSON_PRETTY_PRINT));
    }

    public function testLeaderboard()
    {
        $api = new Api();
        $name2 =  "Leaderboard". Random::generateCharacters(10);
        $responseCreate = json_decode($api->createLeaderboard(array("flag" => "score","audience" => array(), "name" => $name2)), true);
        $this->assertEquals("ok", $responseCreate['status'], "Failed to create leaderboard!".PHP_EOL. json_encode($responseCreate, JSON_PRETTY_PRINT));
        $reponseGetAllLboards =json_decode($api->getAllLeaderboard(),true);
        $this->assertEquals("ok", $reponseGetAllLboards['status'], "Failed to get all leaderboards!".PHP_EOL. json_encode($reponseGetAllLboards, JSON_PRETTY_PRINT));
        $key = array_search($name2, array_column($reponseGetAllLboards['data'][0]['leaderboards'], "name"));
        $this->assertNotEquals(false, $key, "Failed to found leaderboard on list!");
        $this->assertTrue(isset($reponseGetAllLboards['data'][0]['leaderboards'][$key]['id']), "Leaderboard id is not set!");
        $leaderboardId = $reponseGetAllLboards['data'][0]['leaderboards'][$key]['id'];
        $responseDelete = json_decode($api->deleteLeaderboard(array("leaderboard-id" => $leaderboardId)),true);
        $this->assertEquals("ok", $responseDelete['status'], "Failed to delete leaderboard!".PHP_EOL. json_encode($responseDelete, JSON_PRETTY_PRINT));
        $responseGet = json_decode($api->getLeaderboard(array("leaderboard-id" => $leaderboardId)),true);
        $this->assertEquals("fail", $responseGet['status'], "Deleted leaderboard can be get!".PHP_EOL. json_encode($responseGet, JSON_PRETTY_PRINT));
    }
}