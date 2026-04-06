<?php

namespace App\Http\Controllers\CourseFinancialTransaction;

use App\Facades\Services\CourseFinancialTransaction\CourseFinancialTransactionFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreCourseFinancialTransactionRequest;
use App\Services\Model\CourseFinancialTransaction\CourseFinancialTransactionService;
use Illuminate\Http\Request;

class CourseFinancialTransactionController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "course_financial_transaction";
        $this->service = CourseFinancialTransactionFacade::class;
        $this->createRequest = StoreCourseFinancialTransactionRequest::class;
        $this->updateRequest = StoreCourseFinancialTransactionRequest::class;
    }

    public function myTransactions(Request $request)
    {
        try {
            return $this->apiResponse(
                app(CourseFinancialTransactionService::class)->myInstructorTransactions($request)
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }

    public function settle(Request $request)
    {
        try {
            return $this->apiResponse(
                app(CourseFinancialTransactionService::class)->settle($request)
            );
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
