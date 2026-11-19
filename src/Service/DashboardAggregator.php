<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\DashboardReport;
use App\Dto\GithubProfile;
use App\Dto\SourceResult;
use App\Dto\WeatherReport;
use App\Exception\ProviderException;
use App\Provider\GithubProfileProviderInterface;
use App\Provider\WeatherProviderInterface;

final class DashboardAggregator
{
    public function __construct(
        private readonly GithubProfileProviderInterface $github,
        private readonly WeatherProviderInterface $weather,
    ) {
    }

    public function aggregate(string $username, float $latitude, float $longitude): DashboardReport
    {
        return new DashboardReport(
            github: $this->collect(fn (): GithubProfile => $this->github->fetchProfile($username)),
            weather: $this->collect(fn (): WeatherReport => $this->weather->fetchCurrent($latitude, $longitude)),
        );
    }

    /**
     * @param callable(): (GithubProfile|WeatherReport) $loader
     */
    private function collect(callable $loader): SourceResult
    {
        try {
            $data = $loader();
        } catch (ProviderException) {
            return new SourceResult('unavailable', null);
        }

        return new SourceResult($data->stale ? 'stale' : 'ok', $data);
    }
}
