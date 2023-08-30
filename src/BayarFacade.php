<?php

namespace Laraditz\Bayar;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laraditz\Bayar\Skeleton\SkeletonClass
 */
class BayarFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bayar';
    }
}
