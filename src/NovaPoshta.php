<?php

class NovaPoshta
{
    private string $api;
    private int $limit;
    private string $language;

    public function __construct(string $api, string $language = 'UA', int $limit = 20)
    {
        $this->api = $api;
        $this->language = $language;
        $this->limit = $limit;
    }

    public function setLanguage(string $language): void
    {
        $this->language = $language;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function setApi(string $api): void
    {
        $this->api = $api;
    }

    public function send($data)
    {
        $json = json_encode($data);
        $ch = curl_init();
        $url = 'https://api.novaposhta.ua/v2.0/json/';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["content-type: application/json"]);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $responseJson = curl_exec($ch);
        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch));
        }
        $response = json_decode($responseJson);
        curl_close($ch);

        if (!$response || !$response->success) {
            throw new Exception('API request failed.');
        }

        return $response->data;
    }

    public function getCity(string $city = '', ?string $ref = null): array
    {
        $query = [
            'apiKey' => $this->api,
            'modelName' => "Address",
            'calledMethod' => "getCities",
            'methodProperties' => [
                'FindByString' => $city,
                'Limit' => $this->limit,
            ],
        ];

        if ($ref !== null) {
            $query['methodProperties']['Ref'] = $ref;
        }

        $response = $this->send((object)$query);

        return collect($response)->map(function ($city) {
            $description = 'Description' . $this->language;
            return [
                'ref' => $city->Ref,
                'cityName' => $city->$description,
            ];
        })->toArray();
    }
}
