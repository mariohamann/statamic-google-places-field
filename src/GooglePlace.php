<?php

namespace Plugrbase\GooglePlacesField;

use Exception;
use SKAgarwal\GoogleApi\PlacesApi;

class GooglePlace
{
    /**
     * The PlacesApi object
     *
     * @var SKAgarwal\GoogleApi\PlacesApi
     */
    protected $placeApi;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        if (!config()->has('statamic.google_place_field.gmap_api_key') || !config('statamic.google_place_field.gmap_api_key')) {
            return 'Please add a Google Maps API key in config [statamic.google_place_field.gmap_api_key]';
        }

        $this->placeApi = new PlacesApi(config('statamic.google_place_field.gmap_api_key'));
    }

    /**
     * Get place data.
     *
     * @param string $place
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function getData(string $place)
    {
        if ($placeData = $this->findPlace($place)) {
            return isset($placeData[0]['place_id']) ? $this->placeApi->placeDetails($placeData[0]['place_id']) : null;
        }

        try {
            return $this->placeApi->placeDetails($place);
        } catch (Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * Find a place from search string.
     *
     * @param string $input
     *
     * @return \Illuminate\Support\Collection|null
     */
    public function findPlace($input)
    {
        $places = $this->placeApi
            ->withHeaders(['Accept-Language' => 'de'])
            ->findPlace($input, 'textquery', ['language=de']);

        if (!$places->get('candidates')->count()) {
            return null;
        }

        return collect(array_values($places->get('candidates')->all()));
    }
}
