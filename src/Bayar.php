<?php

namespace Laraditz\Bayar;

use Illuminate\Support\Manager;
use InvalidArgumentException;
use Laraditz\Bayar\Providers\AtomeProvider;

class Bayar extends Manager
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     *
     * @deprecated Will be removed in a future Socialite release.
     */
    protected $app;

    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Laraditz\Bayar\Providers\AbstractProvider
     */
    protected function createAtomeDriver()
    {
        $config = $this->config->get('services.atome');

        return $this->buildProvider(
            AtomeProvider::class,
            $config
        )->setDriver('atome');
    }

    /**
     * Build a Bayar provider instance.
     *
     * @param  string  $provider
     * @param  array  $config
     * @return \Laraditz\Bayar\Providers\AbstractProvider
     */
    public function buildProvider($provider, $config)
    {
        return new $provider($config);
    }


    /**
     * Get the default driver name.
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function getDefaultDriver()
    {
        throw new InvalidArgumentException('No Bayar driver was specified.');
    }
}
