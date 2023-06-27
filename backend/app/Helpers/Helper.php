<?php


namespace App\Helpers;


use phpDocumentor\Reflection\Types\Boolean;
use phpDocumentor\Reflection\Types\Null_;

class Helper {

    private static $salt = 12012022;

    /**
     * To mask the id of the user
     *
     * @param Int $userId
     *
     * @return String|Boolean
     * <p>Return False if user id is not a number<p>
     */
    public static function maskUserId($userId) {
        if (is_numeric($userId) === FALSE) {
            return FALSE;
        }
        $userId = $userId + self::$salt;

        return base64_encode($userId);
    }

    /**
     * To unmask the id of the user
     *
     * @param String $userId
     *
     * @return INT|Null
     * <p>Returns int if correct id is found</p>
     * <p>Returns NULL if invalid id is found</p>
     */
    public static function unMaskUserId($userId) {
        $userId = base64_decode($userId);
        if (is_numeric($userId)) {
            return $userId - self::$salt;
        }

        return NULL;
    }

}