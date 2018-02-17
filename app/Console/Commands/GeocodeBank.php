<?php

namespace App\Console\Commands;

use App\Bank;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class GeocodeBank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'banks:geocode';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the full address and geocoordinates of the foodbank';

    /**
     * @var string
     */
    protected $baseUrl = 'https://maps.googleapis.com/maps/api/geocode/json?address=';

    /**
     * @var array
     */
    protected $result;

    /**
     * @var
     */
    protected $client;

    /**
     * Create a new command instance.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        parent::__construct();
        $this->client = new $client;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            Bank::whereNull('latitude')->get()->each(function ($bank) {
                $this->line($bank->name);
                $this->requestGeocode($bank);
                if ($this->result) {
                    $bank->update($this->address());
                    $this->result = null;
                }
            });
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            Log::error($e->getMessage());
        }
    }

    /**
     * @param $bank
     */
    private function requestGeocode($bank)
    {
        if ( ! Cache::has("geocode.$bank->slug")) {
            $res = $this->client->get($this->baseUrl . $bank->address . '+uk&key=' . env('GOOGLE_GEOCODING_API_KEY'));
            $res = json_decode($res->getBody()->getContents());

            if ($res->status == 'OK') {
                Cache::forever("geocode.$bank->slug", $res->results[0]);
            }
        }

        $this->result = Cache::get("geocode.$bank->slug");
    }

    /**
     * @return array
     */
    private function address()
    {
        return [
            'add1' => $this->streetAddress(),
            'add3' => $this->filter('locality'),
            'town' => $this->filter('postal_town'),
            'county' => $this->filter('administrative_area_level_2'),
            'postcode' => $this->filter('postal_code'),
            'latitude' => $this->result->geometry->location->lat,
            'longitude' => $this->result->geometry->location->lng,
        ];
    }

    /**
     * @return string
     */
    private function streetAddress()
    {
        $text = '';

        foreach (['premise', 'street_number', 'route'] as $el) {
            $val = trim($this->filter($el));
            $val != '' ? $text .= $val : '';
            if ($el == 'premise' && $val != '') {
                $text .= ',';
            }

            if ($text != '') {
                $text .= ' ';;
            }
        }

        return trim($text);
    }

    /**
     * @param $el
     * @return string
     */
    public function filter($el)
    {
        foreach ($this->result->address_components as $e) {
            foreach ($e->types as $type) {
                if ($type == $el) {
                    return $e->long_name;
                }
            }
        }

        return '';
    }
}
