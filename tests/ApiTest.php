<?php
declare(strict_types=1);

use Example\Api\Api;
use Example\Utility\Random;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase
{
    private array $platforms = [1,2,3,4,5,6,7,8];
    private array $versions = ["1.0","1.1","1.2"];
    private array $regions = ["lt", "en", "us", "fr"];

    /* Test Case 1 */
    public function testPLayerChangesNickname(){
        foreach ($this->platforms as $platform){
            foreach ($this->versions as $version){
                $api = new Api();
                $name = "Device". Random::generateCharacters(10);
                $response = $api->openSession([
                    "name" => $name,
                    "platform" => $platform,
                    "version" => $version,
                    "region" => "us"
                ]);
                $response = json_decode($response, true);

                $playerId = $response['data'][0]['player-id'];
                $nick = "Nick". Random::generateCharacters(10);

                $responseSetNick = $api->setNick([
                    "player-id" => $playerId,
                    "nickname" => $nick
                ]);

                $responseSetNick = json_decode($responseSetNick, true);
                $this->assertEquals("ok", $responseSetNick['status'], "Failed to change nickname!".PHP_EOL. json_encode($responseSetNick, JSON_PRETTY_PRINT));
            }
        }
    }

    /* Test Case 2 */
    public function testPLayerChangesNicknameWhilePlayingTournament(){
        $platforms = [5,8];
        foreach ($platforms as $platform){
            foreach ($this->versions as $version){
                foreach ($this->regions as $region){
                    $api = new Api();
                    $name = "Device". Random::generateCharacters(10);
                    $response = $api->openSession([
                        "name" => $name,
                        "platform" => $platform,
                        "version" => $version,
                        "region" => $region
                    ]);
                    $response = json_decode($response, true);

                    $playerId = $response['data'][0]['player-id'];
                    $sessionId = $response['data'][0]['session-id'];

                    $leaderboardName =  "Leaderboard". Random::generateCharacters(10);
                    $responseCreate = $api->createLeaderboard([
                        "flag" => "score",
                        "audience" => [],
                        "name" => $leaderboardName
                    ]);
                    $responseCreate = json_decode($responseCreate, true);
                    $this->assertEquals("ok", $responseCreate['status'], "Failed to create leaderboard!".PHP_EOL. json_encode($responseCreate, JSON_PRETTY_PRINT));

                    $responseGetAllLeaderboard = json_decode($api->getAllLeaderboard(), true);
                    $this->assertEquals("ok", $responseGetAllLeaderboard['status'], "Failed to get all leaderboards!".PHP_EOL. json_encode($responseGetAllLeaderboard, JSON_PRETTY_PRINT));
                    $leaderboardKey = array_search($leaderboardName, array_column($responseGetAllLeaderboard['data'][0]['leaderboards'], "name"));
                    $leaderboardId = $responseGetAllLeaderboard['data'][0]['leaderboards'][$leaderboardKey]['id'];

                    $responseRegister = $api->registerLeaderboard([
                        "player-id" => $playerId,
                        "session-id" => $sessionId,
                        "leaderboard-id" => $leaderboardId
                    ]);
                    $responseRegister = json_decode($responseRegister, true);
                    $this->assertEquals("ok", $responseRegister['status'], "Failed to register player in leaderboard!".PHP_EOL. json_encode($responseRegister, JSON_PRETTY_PRINT));

                    $nick = "Nick". Random::generateCharacters(10);
                    $responseSetNick = $api->setNick([
                        "player-id" => $playerId,
                        "nickname" => $nick
                    ]);
                    $responseSetNick = json_decode($responseSetNick, true);
                    $this->assertEquals("ok", $responseSetNick['status'], "Failed to change nickname!".PHP_EOL. json_encode($responseSetNick, JSON_PRETTY_PRINT));

                    $responseGetPlayer = $api->getPlayer([
                        "player-id" => $playerId
                    ]);
                    $responseGetPlayer = json_decode($responseGetPlayer, true);
                    $this->assertEquals("ok", $responseGetPlayer['status'], "Failed to get player data!".PHP_EOL. json_encode($responseGetPlayer, JSON_PRETTY_PRINT));
                    $this->assertEquals($nick, $responseGetPlayer['data'][0]['nick'], "Failed to change nickname!");
                    $this->assertEquals($sessionId, $responseGetPlayer['data'][0]['session-id'], "Failed to change nickname without changing session-id!");
                }
            }
        }
    }

    /* Test case 3 */
    public function testDeletingLeaderboardsWhilePlayingTournament(){
        $platforms = [5,8];

        foreach ($platforms as $platform){
            $api = new Api();
            $name = "Device". Random::generateCharacters(10);
            $response = $api->openSession([
                "name" => $name,
                "platform" => $platform,
                "version" => "1.0",
                "region" => 'us'
            ]);
            $response = json_decode($response, true);

            $playerId = $response['data'][0]['player-id'];
            $sessionId = $response['data'][0]['session-id'];

            $leaderboardName =  "Leaderboard". Random::generateCharacters(10);
            $responseCreate = $api->createLeaderboard([
                "flag" => "score",
                "audience" => [],
                "name" => $leaderboardName
            ]);
            $responseCreate = json_decode($responseCreate, true);
            $this->assertEquals("ok", $responseCreate['status'], "Failed to create leaderboard!".PHP_EOL. json_encode($responseCreate, JSON_PRETTY_PRINT));

            $responseGetAllLeaderboard = json_decode($api->getAllLeaderboard(), true);
            $this->assertEquals("ok", $responseGetAllLeaderboard['status'], "Failed to get all leaderboards!".PHP_EOL. json_encode($responseGetAllLeaderboard, JSON_PRETTY_PRINT));
            $leaderboardKey = array_search($leaderboardName, array_column($responseGetAllLeaderboard['data'][0]['leaderboards'], "name"));
            $leaderboardId = $responseGetAllLeaderboard['data'][0]['leaderboards'][$leaderboardKey]['id'];

            $responseRegister = $api->registerLeaderboard([
                "player-id" => $playerId,
                "session-id" => $sessionId,
                "leaderboard-id" => $leaderboardId
            ]);
            $responseRegister = json_decode($responseRegister, true);
            $this->assertEquals("ok", $responseRegister['status'], "Failed to register player in leaderboard!".PHP_EOL. json_encode($responseRegister, JSON_PRETTY_PRINT));

            $responseDeleteLeaderboard = $api->deleteLeaderboard([
                "leaderboard-id" => $leaderboardId
            ]);
            $responseDeleteLeaderboard = json_decode($responseDeleteLeaderboard, true);
            $this->assertEquals("ok", $responseDeleteLeaderboard['status'], "Failed to delete a leaderboard!".PHP_EOL. json_encode($responseDeleteLeaderboard, JSON_PRETTY_PRINT));

            $responseGetPlayer = $api->getPlayer([
                "player-id" => $playerId
            ]);
            $responseGetPlayer = json_decode($responseGetPlayer, true);
            $this->assertEquals("ok", $responseGetPlayer['status'], "Failed to get player data!".PHP_EOL. json_encode($responseGetPlayer, JSON_PRETTY_PRINT));
            $this->assertEquals($sessionId, $responseGetPlayer['data'][0]['session-id'], "Failed to change nickname without changing session-id!");
        }

    }

    /* Test Case 4 */
    public function testPlayerTokenUpdateWhenRefreshingPlayer(){
        foreach ($this->platforms as $platform){
            foreach ($this->versions as $version){
                foreach ($this->regions as $region){
                    $api = new Api();
                    $name = "Device". Random::generateCharacters(10);
                    $response = $api->openSession([
                        "name" => $name,
                        "platform" => $platform,
                        "version" => $version,
                        "region" => $region
                    ]);
                    $response = json_decode($response, true);

                    $playerId = $response['data'][0]['player-id'];
                    $sessionId = $response['data'][0]['session-id'];

                    $responseRefresh = $api->refresh([
                        "player-id" => $playerId,
                        "session-id" => $sessionId
                    ]);
                    $responseRefresh = json_decode($responseRefresh, true);
                    $this->assertEquals("ok", $responseRefresh['status'], "Failed to refresh session!".PHP_EOL. json_encode($responseRefresh, JSON_PRETTY_PRINT));

                    $responseGetPlayer = $api->getPlayer([
                        "player-id" => $playerId
                    ]);
                    $responseGetPlayer = json_decode($responseGetPlayer, true);
                    $this->assertNotEquals($sessionId, $responseGetPlayer['data'][0]['session-id']);
                }
            }
        }
    }

    /* Test Case 5 */
    public function testPlayerTokenExistsWhenClosingSession(){
        $api = new Api();
        $name = "Device". Random::generateCharacters(10);
        $response = $api->openSession([
            "name" => $name,
            "platform" => 3,
            "version" => "1.2",
            "region" => "fr"
        ]);
        $response = json_decode($response, true);

        $playerId = $response['data'][0]['player-id'];
        $sessionId = $response['data'][0]['session-id'];

        $responseClose = $api->closeSession([
            "player-id" => $playerId,
            "session-id" => $sessionId
        ]);
        $responseClose = json_decode($responseClose, true);

        $this->assertEquals("ok", $responseClose['status'], "Failed to close session!".PHP_EOL. json_encode($responseClose, JSON_PRETTY_PRINT));

        $responseGetPlayer = $api->getPlayer([
            "player-id" => $playerId
        ]);
        $responseGetPlayer = json_decode($responseGetPlayer, true);

        $this->assertEmpty($responseGetPlayer['data'][0]['session-id']);
    }

    /* Test Case 6 */
    public function testCreatePlayerWhenPlatformDoesNotExist(){
        foreach ($this->versions as $version){
            foreach ($this->regions as $region){
                $api = new Api();
                $name = "Device". Random::generateCharacters(10);
                $response = $api->openSession([
                    "name" => $name,
                    "platform" => 9,
                    "version" => $version,
                    "region" => $region
                ]);
                $response = json_decode($response, true);

                $this->assertEquals("fail", $response['status'], "Successfully opened session for player, when it shouldn't!".PHP_EOL. json_encode($response, JSON_PRETTY_PRINT));
                $this->assertFalse(isset($response['data'][0]['player-id']), "Player id is set!");
            }
        }
    }

}