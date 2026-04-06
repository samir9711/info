<?php

namespace App\Facades\Services\CourseFinancialTransaction;

use Illuminate\Support\Facades\Facade;

class CourseFinancialTransactionFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CourseFinancialTransactionService';
    }
}