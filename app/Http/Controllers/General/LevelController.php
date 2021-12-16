<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
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


    /**
     * @OA\Get(
     *     path="/levels",
     *     summary="Get all levels ",
     *     tags={"Level"},
     *     @OA\Response(
     *         response="200",
     *         description="Levels retrieved successfully",
     *         @OA\JsonContent()
     *     )
     * )
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $levels = Level::all();
        return $this->success($levels);
    }

    /**
     * @OA\Post(
     *     path="/levels/create",
     *     summary="Create Level",
     *     tags={"Level"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Level name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Level created successfully",
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
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string'],
        ]);

        Level::create($request->all());
        return $this->success();
    }



    /**
     * @OA\Post(
     *     path="/levels/update",
     *     summary="Update Level",
     *     tags={"Level"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Level id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Level name",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Level updated successfully",
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
    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'string'],
        ]);
        Level::findOrFail($request->id)->update($request->all());
        return $this->success();
    }


    /**
     * @OA\Delete(
     *     path="/levels/delete/{id}",
     *     summary="Delete Level",
     *     tags={"Level"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         description="Api token, prefixed with 'Bearer' and a space. e.g Bearer 77e1c83b-7bb0-437b-bc50-a7a58e5660ac",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Level id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Level deleted successfully",
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
    public function destroy($id)
    {
        Level::findOrFail($id)->delete();
        return $this->success();
    }
}
