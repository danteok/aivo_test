<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Client;
use function GuzzleHttp\json_decode;
use GuzzleHttp\Exception\RequestException;

require '../../vendor/autoload.php';

$app = new \Slim\App;

// Set your Spotify credentials here
define('SPOTIFY_API_CLIENT_ID', '0a372a82df7a40e3b7f0ca238ceed70d');
define('SPOTIFY_API_CLIENT_SECRET', '4ae69aab046642aabe12687227d01527');

$app->get('/api/v1/albums', function (Request $request, Response $response, array $args) {
    try {
        if (empty($_GET['q'])) {
            throw new Exception('Name artist param is required');
        }
        $query = $_GET['q'];
    } catch (Exception $ex) {
        $responseApi = [
            'status' => 'failed',
            'error' => $ex->getMessage()
        ];
        return $response->withJson($responseApi);
    }


    // Obtengo Token
    try {
        $client = new Client;

        $responseToken = $client->request('POST', 'https://accounts.spotify.com/api/token', [
            'form_params' => ["grant_type" => "client_credentials"],
            'headers' => ['Authorization' => 'Basic ' . base64_encode(SPOTIFY_API_CLIENT_ID . ':' . SPOTIFY_API_CLIENT_SECRET)]
                ]
        );
        $responseToken = json_decode($responseToken->getBody());
        $token = $responseToken->access_token;
    } catch (RequestException $exception) {
        $responseApi = [
            'status' => 'failed',
            'error' => $exception->getMessage()
        ];
        return $response->withJson($responseApi);
    }

    // Una query puede tener varios artistas, ej: Gonzalez
    try {
        $client = new Client;
        $responseArtist = $client->request('GET', 'https://api.spotify.com/v1/search?q=' . $query . '&type=artist', [
            'headers' => ['Authorization' => 'Bearer  ' . $token]
                ]
        );
        $responseArtist = json_decode($responseArtist->getBody());
    } catch (RequestException $exception) {
        $responseApi = [
            'status' => 'failed',
            'error' => $exception->getMessage()
        ];
        return $response->withJson($responseApi);
    }

    $artists = $responseArtist->artists;

    $responseApi = [];

    // Traigo todos los albumes de/del artista/s de la query anterior
    foreach ($artists->items as $artist) {
        try {
            $client = new Client;
            $responseAlbums = $client->request('GET', 'https://api.spotify.com/v1/artists/' . $artist->id . '/albums', [
                'headers' => ['Authorization' => 'Bearer ' . $token, 'Accept' => 'application/json', 'Content-Type' => 'application/json']
                    ]
            );
            $responseAlbums = json_decode($responseAlbums->getBody());
            foreach ($responseAlbums->items as $item) {
                $responseApi[] = [
                    'name' => $item->name,
                    // released date in format dd-mm-YYYY
                    'released' => date('d-m-Y', strtotime($item->release_date)),
                    'tracks' => $item->total_tracks,
                    'cover' => [
                        'height' => $item->images{0}->height,
                        'width' => $item->images{0}->width,
                        'url' => $item->images{0}->url,
                    ]
                ];
            }
        } catch (RequestException $exception) {
            $responseApi = [
                'status' => 'failed',
                'error' => $exception->getMessage()
            ];
            return $response->withJson($responseApi);
        }
    }

    return $response->withJson([
                'status' => 'ok',
                'albums' => $responseApi
    ]);
});


$app->run();
