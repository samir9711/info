<?php

namespace App\Http\Controllers\QuizAttempt;

use App\Facades\Services\QuizAttempt\QuizAttemptFacade;
use App\Http\Controllers\Controller;
use App\Http\Controllers\FatherCrudController;
use App\Http\Requests\Model\StoreQuizAttemptRequest;
use Illuminate\Http\Request;

class QuizAttemptController extends FatherCrudController
{
    protected function setVariables() : void {
        $this->key = "quiz_attempt";
        $this->service = QuizAttemptFacade::class;
        $this->createRequest = StoreQuizAttemptRequest::class;
        $this->updateRequest = StoreQuizAttemptRequest::class;
    }

    public function submit(Request $request) {
        try {
            $req = app($this->createRequest);
            $user = $request->user();

            $result = $this->service::submit($req, $user);

            return $this->apiResponse([$this->key => $result], true, null, 200);
        } catch (\Exception $e) {
            return $this->handleException($e);
        }
    }
}
