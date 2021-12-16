<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
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
        return $this->success(['name' => config('app.name')]);
    }

    public function settings(Request $request)
    {
        // return $request->all();

        foreach ($request->all() as $setting) {
            if ($setting['name'] == 'NO_CHARGE') {
                // remove any existing percent charge
                $oldP = Setting::whereName('FIXED_CHARGE')->first();
                $oldF = Setting::whereName('PERCENT_CHARGE')->first();
                if ($oldP) {
                    $oldP->delete();
                }
                if ($oldF) {
                    $oldF->delete();
                }
            }

            if ($setting['name'] == 'FIXED_CHARGE') {
                // remove any existing percent charge
                $old = Setting::whereName('PERCENT_CHARGE')->first();
                $oldN = Setting::whereName('NO_CHARGE')->first();
                if ($old) {
                    $old->delete();
                }
                if ($oldN) {
                    $oldN->delete();
                }
            }
            if ($setting['name'] == 'PERCENT_CHARGE') {
                // remove any existing fixed charge
                $old = Setting::whereName('FIXED_CHARGE');
                if ($old) {
                    $old->delete();
                }
                $oldN = Setting::whereName('NO_CHARGE')->first();
                if ($oldN) {
                    $oldN->delete();
                }
            }
            Setting::set($setting['name'], $setting['value'], $setting['type']);
        }
        return $this->success();
    }

    /**
     * @OA\Get(
     *     path="/admin/settings/index",
     *     summary="Get all settings",
     *     tags={"Admin settings"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Settings retrieved successfully",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="User does not have the permission to perform this action",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $settings = Setting::all();
        return $this->success($settings);
    }
}
