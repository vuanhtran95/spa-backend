<?php

namespace App\Helper;


class Translation
{
    public static $UNAUTHORIZED = "UNAUTHORIZED";

    public static $USER_CREATED = "USER_CREATED";
    public static $USER_UPDATED = "USER_UPDATED";
    public static $USER_UPDATE_FAILURE = "USER_UPDATE_FAILURE";

    public static $SERVICE_CREATED = "SERVICE_CREATED";
    public static $SERVICE_CREATE_ERROR = "SERVICE_CREATE_ERROR";
    public static $SERVICE_UPDATED = "SERVICE_UPDATED";
    public static $SERVICE_UPDATED_FAILURE = "SERVICE_UPDATED_FAILURE";

    public static $GET_ALL_ROLE_SUCCESS = "GET_ALL_ROLE_SUCCESS";
    public static $NO_ROLE_FOUND = "NO_ROLE_FOUND";

    public static $GET_SINGLE_USER_SUCCESS = "GET_SINGLE_USER_SUCCESS";
    public static $GET_ALL_USER_SUCCESS = "GET_ALL_USER_SUCCESS";
    public static $NO_USER_FOUND = "NO_USER_FOUND";
    public static $USERNAME_EXIST = "USERNAME_EXIST";

    public static $GET_ALL_SERVICE_SUCCESS = "GET_ALL_SERVICE_SUCCESS";
    public static $NO_SERVICE_FOUND = "NO_SERVICE_FOUND";

    public static $DELETE_USER_SUCCESS = "DELETE_USER_SUCCESS";
    public static $DELETE_USER_FAILURE = "DELETE_USER_FAILURE";

    public static $SYSTEM_ERROR = "SYSTEM_ERROR";
}
