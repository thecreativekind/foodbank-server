<?php

namespace App\Http\Controllers\Api;

use App\Bank;
use Illuminate\Http\Request;
use App\Jobs\LogSkillRequest;
use App\Http\Controllers\Controller;

class BankController extends Controller
{
    /**
     * @var
     */
    protected $bankId;

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Request $request)
    {
        $bank = Bank::where('name', 'LIKE', "%$request->name%")->get();

        if ($bank->count() == 0) {
            $status = 200;
            $data = ['data' => $bank->first()];
            $this->bankId = $bank->first()->id;
        } else {
            $status = 400;
            $data = ['errors' => [
                'status' => 400,
                'source' => ['pointer' => $request->name],
                'title' => 'Foodbank unknown',
                'detail' => 'The foodbank you requested is not registered with this service.'
            ]];
        }

        dispatch(new LogSkillRequest($this->bankId, $request->all()));

        return response($data, $status);
    }
}
