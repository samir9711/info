<?php

namespace App\Facades\Services\CompanyRecommendedCourse;

use Illuminate\Support\Facades\Facade;

class CompanyRecommendedCourseFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'CompanyRecommendedCourseService';
    }
}