<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Models\Unsubscribe;
use Illuminate\Http\Request;
use phpDocumentor\Reflection\Types\Boolean;

class UnsubscribeController extends Controller {

    /**
     * To save the user id for which we don't have to send any email
     *
     * @param Request $request
     *
     * @return bool|String
     * <p>Return String if user unsubscribed successfully</p>
     * <p>else return false</p>
     */
    public function store(Request $request) {


        if ($request->has('userId') === FALSE) {
            return FALSE;
        }

        $userId = Helper::unMaskUserId($request->userId);

        if (is_null($userId)) {
            return FALSE;
        }


        Unsubscribe::updateOrCreate([
            'user_id' => $userId,
        ], [
            'user_id' => $userId,
        ]);

        return view('pages.unsubscribe');
    }

    /**
     * To get the user id of all the unsubscribed users
     * @return object
     * <p>Return object containing the list of all the user id</p>
     */
    public function index(): object {
        return Unsubscribe::all()->pluck('user_id');
    }

    /**
     * Check if the user has unsubscribed from the email or not
     *
     * @param int $userId
     *
     * @return bool
     * <p>Return False, if we don't need to send the email</p>
     * <p>Return True, if we have to send the email</p>
     */
    public function wantToSendTheEmail($userId): bool {
        if (in_array($userId, $this->index()->toArray()) === TRUE) {
            return FALSE;
        }

        return TRUE;
    }

}
