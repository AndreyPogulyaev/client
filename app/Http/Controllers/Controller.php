<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Graze\GuzzleHttp\JsonRpc\Client;
use Illuminate\Http\Request;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Show the profile for a given user.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $client = Client::factory('http://localhost/weather_history/public/rpc');
        
        $tempByDate = false;
        $validated = $request->validate([
            'date' => 'date',
        ]);

        if (count($validated)) {
            $response = $client->send($client->request(1, 'weather.getByDate', $validated));
            $body = json_decode($response->getBody());
            if (property_exists($body, 'result')) {
                $tempByDate = "Температура {$body->result->temp} градусов";
            } else {
                $tempByDate = 'Температура не найдена';
            }
        }

        $historyResponse = $client->send($client->request(1, 'weather.getHistory', ["lastDays" => "30"]));
        $bodyHistory = json_decode($historyResponse->getBody());

        $itemsHistory = (property_exists($bodyHistory, 'result')) ? $bodyHistory->result->items : [];

        return view('welcome', [
            'items' => $itemsHistory,
            'tempByDate' => $tempByDate
        ]);
    }
}
