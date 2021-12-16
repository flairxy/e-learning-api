<?php

namespace App\Traits;

use App\Models\Account\Role;
use App\Models\Auth\User;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

trait HandlesResponse
{

    public function success($data = [], $code = Response::HTTP_OK)
    {
        return \response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $data
        ], $code);
    }

    public function error($message, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return \response()->json([
            'status' => false,
            'message' => $message,
            'data' => []
        ], $code);

    }

    public function getPermissions($merchant)
    {
        /** @var User $user */
        $user = request()->user();
        /** @var Collection $roles */
        $roles = $user->roles()->with('permissions')
            ->where('merchant', !!$merchant)->get();

        $permissions = [];
        foreach ($roles as $role) {
            $permissions = array_merge($permissions, $role->permissions->pluck('key')->toArray());
        };

        return $permissions;
    }

    public function userHasPermission($key, $merchant = null)
    {
        $permissions = (array)$this->getPermissions($merchant);
        return in_array($key, $permissions);
    }

    public function validateUserHasPermission($key, $merchant = null)
    {
        $this->abortUnless($this->userHasPermission($key, $merchant), Response::HTTP_UNAUTHORIZED);
    }

    public function abortIf($boolean, $code, $message = '', $headers = [])
    {
        if ($boolean) {
            abort($code, $message, $headers);
        }
    }

    public function abortUnless($boolean, $code, $message = '', $headers = [])
    {
        if (!$boolean) {
            abort($code, $message, $headers);
        }
    }

}
