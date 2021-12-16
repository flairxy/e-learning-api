<?php

namespace App\Http\Controllers;

use Illuminate\Support\Env;

class ExampleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }


    public function ping()
    {
        return $this->success([
            'name' => config('app.name')
        ]);
    }
}
