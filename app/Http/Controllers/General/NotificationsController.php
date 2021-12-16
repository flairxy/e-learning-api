<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Category;
use App\Notifications\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class NotificationsController extends Controller
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

    public function index(Request $request)
    {
        $user = User::findOrFail($request->id);
        $updatedData = [];
        foreach ($user->notifications as $notification) {

            $data = $notification->data;
            $sender = User::whereId($notification->sender)->first();
            if ($sender == null) {
                $data['sender'] = $notification->sender;
            } else {

                $data['sender'] = $sender->first_name . ' ' . $sender->last_name;
            }
            $data['read_at'] = $notification->read_at;
            $data['id'] = $notification->id;
            $data['date'] = $notification->created_at->toFormattedDateString();

            array_push($updatedData, $data);
        }
        return $this->success($updatedData);
    }

    // public function create(Request $request)
    // {

    //     $users = User::whereIn('id', $request->users)->get();
    //     $sender = User::findOrFail($request->id);

    //     Notification::send($users, new Message($request->body, $sender->id));
    //     return $this->success();
    // }

    // public function sent(Request $request)
    // {
    //     $user = User::whereId($request->id)->first();
    //     $notifications = Notification::whereSender($user->id)->get();
    //     $updatedData = [];
    //     foreach ($notifications as $notification) {
    //         $notifications->makeHidden(['sender', 'notifiable_id']);
    //         $notification->time = $notification->created_at->toFormattedDateString();
    //         $notification->data = json_decode($notification->data);

    //         array_push($updatedData, $notification);
    //     }
    //     return $this->success($updatedData);
    // }

    public function update(Request $request)
    {
        $user = User::findOrFail($request->user);
        $user->unreadNotifications->find($request->id)->markAsRead();
        return $this->success();
    }

    // public function destroy(Request $request)
    // {
    //     $messages = Notification::whereIn('id', $request->id)->get();
    //     foreach ($messages as $message) {

    //         $message->delete();
    //     }
    //     return $this->success();
    // }
}
