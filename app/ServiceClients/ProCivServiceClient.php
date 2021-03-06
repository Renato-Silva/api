<?php

declare(strict_types=1);

namespace VOSTPT\ServiceClients;

class ProCivServiceClient extends ServiceClient implements Contracts\ProCivServiceClient
{
    /**
     * {@inheritDoc}
     */
    public function buildUrl(string $path, array $parameters, string $method): string
    {
        return $this->getHostname($path);
    }

    /**
     * {@inheritDoc}
     */
    public function getMainOccurrences(): array
    {
        $response = $this->post('GetMainOccurrences', [
            'allData' => true,
        ], [
            'Content-Type' => 'application/json',
        ]);

        return $response['GetMainOccurrencesResult']['ArrayInfo'][0] ?? [
            'Data'  => [],
            'Total' => 0,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getOccurrenceHistory(): array
    {
        $response = $this->post('GetHistoryOccurrencesByLocation', [
            'allData' => true,
        ], [
            'Content-Type' => 'application/json',
        ]);

        return $response['GetHistoryOccurrencesByLocationResult']['ArrayInfo'][0] ?? [
            'Data'  => [],
            'Total' => 0,
        ];
    }
}
