<?php

if (!function_exists('project_name')) {
    function project_name()
    {
        return env('PROJECT_NAME');
    }
}

if (!function_exists('destroy_cache')) {
    function destroy_cache()
    {
        \App\Http\Controllers\HelperController::removeCache();
    }
}

if (!function_exists('success_message')) {
    function success_message($message)
    {
        \App\Http\Controllers\HelperController::message($message);
    }
}
if (!function_exists('error_message')) {
    function error_message($message)
    {
        \App\Http\Controllers\HelperController::message($message, 2);
    }
}
if (!function_exists('sessioned_title')) {
    function sessioned_title($title)
    {
        \App\Http\Controllers\HelperController::sessioned_title($title);
    }
}
if (!function_exists('areas')) {
    function areas($id = null)
    {
        return \App\Http\Controllers\HelperController::areas($id);
    }
}
if (!function_exists('cities')) {
    function cities($id = null)
    {
        return \App\Http\Controllers\HelperController::cities($id);
    }
}
if (!function_exists('quarters')) {
    function quarters($id = null)
    {
        return \App\Http\Controllers\HelperController::quarters($id);
    }
}
if (!function_exists('image_upload')) {
    function image_upload($image, $folder = 'pic')
    {
        return \App\Http\Controllers\HelperController::upload_image($image, $folder);
    }
}

if (!function_exists('image_update')) {
    function image_update($old_image, $new_image, $folder = 'pic')
    {
        try {
            return \App\Http\Controllers\HelperController::update_image($old_image, $new_image, $folder);
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}

if (!function_exists('libraries')) {
    function libraries($id = null)
    {
        return \App\Http\Controllers\HelperController::libraries($id);
    }
}

if (!function_exists('categories')) {
    function categories($id = null)
    {
        return \App\Http\Controllers\HelperController::categories($id);
    }
}

if (!function_exists('request_status')) {
    function request_status($status = null)
    {
        return \App\Http\Controllers\HelperController::request_status($status);
    }
}


if (!function_exists('books')) {
    function books($vars = null)
    {
        return \App\Http\Controllers\HelperController::books($vars);
    }
}

if (!function_exists('clients')) {
    function clients($id = null)
    {
        return \App\Http\Controllers\HelperController::clients($id);
    }
}

if (!function_exists('drivers')) {
    function drivers($id = null)
    {
        return \App\Http\Controllers\HelperController::drivers($id);
    }
}


if (!function_exists('notify')) {
    function notify($token, $title, $content, $body, $type, $badge = 1, $tagName = 'data')
    {
        return \App\Http\Controllers\HelperController::notify($token, $title, $content, $body, $type, $badge, $tagName);
    }
}

if (!function_exists('system_discount')) {
    function system_discount()
    {
        return \App\Http\Controllers\HelperController::lastSettingDiscount();
    }
}

if (!function_exists('send_email')) {
    function send_email($email, $title, $subject, $data = array())
    {
        return \App\Http\Controllers\HelperController::sendEmail($email, $title, $subject, $data);
    }
}

if (!function_exists('success')) {
    function success($data, $status = 200, $result_number = 1)
    {
        return \App\Http\Controllers\HelperController::success($data, $status, $result_number);
    }
}

if (!function_exists('error')) {
    function error($data, $status = 500, $result_number = 1)
    {
        return \App\Http\Controllers\HelperController::error($data, $status, $result_number);
    }
}

if (!function_exists('getDistance')) {
    function getDistance($source = LOCATION_LAT . "," . LOCATION_LONG, $destination = LOCATION_LAT . "," . LOCATION_LONG)
    {
        return \App\Http\Controllers\HelperController::getDistance($source, $destination);
    }
}

