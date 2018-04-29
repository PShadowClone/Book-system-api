<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = "notifications";
    protected $primaryKey = 'id';
    protected $fillable = ['content', 'type', 'type_all', 'admin_id', 'to', 'created_at', 'updated_at'];
    protected $with = ['admin'];

    /**
     *
     * get admin's object who sent a notification
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    /**
     *
     * get client's object who has got notifications before
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'to', 'id');
    }

    /**
     *
     * get driver's object who has got notifications before
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'to', 'id');
    }

    /**
     *
     * get library's object who has got notifications before
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function library()
    {
        return $this->belongsTo(Library::class, 'to', 'id');
    }


    /**
     *
     * interface (function) for pushing notification (systems deals with helpers)
     *
     * this function is connected with (notify) helper that handels all notification in system
     *
     * this function is decomposed into multi-functions
     *  1. notificationBody
     *  2. notificationHeader
     *  3. fireNotification
     *
     * @throws \Exception
     * @param $token
     * @param $title
     * @param $content
     * @param $body
     * @param $type
     * @param int $badge
     * @param $tagName
     * @return array
     */
    public static function notify($token, $title, $content, $body, $type, $badge = 1, $tagName = 'data')
    {

        $baseData = [
            'body' => $body,
            'title' => $title,
            'subtitle' => trans('lang.alert'),
            "badge" => $badge,
            "content_available" => true,
            "priority" => "high",
            'type' => $type,
            $tagName => $content,
            "sound" => "default"

        ];
        $notification = $baseData; // firebase notification body
        $data = $baseData; // firebase notification background body


        $fields = self::notificationBody($token, $notification, $data);
        $header = self::notificationHeader();
            $result = self::fireNotification($header, $fields);
        return $result;
    }


    /**
     *
     * contains notification's body
     *
     * @param $token
     * @param $notification
     * @param $data
     * @return array
     */

    private static function notificationBody($token, $notification, $data)
    {

        $fields = array
        (
            'notification' => $notification,
            'data' => $data

        );
        if (is_array($token)) {
            $fields['registration_ids'] = $token;
        } else {
            $fields['to'] = $token;
        }
        return $fields;
    }

    /**
     *
     *  contains all notification's header's settings
     *
     * @return array
     */
    private static function notificationHeader()
    {
        $headers = array
        (
            'Authorization: key=' . env('FIREBASE_KEY'),
            'Content-Type: application/json'
        );

        return $headers;

    }

    /**
     *
     * fire notification throw firebase service using REST URL
     *
     * @throws \Exception
     * @param $headers
     * @param $fields
     * @return array
     */
    private static function fireNotification($headers, $fields)
    {
        #Send Reponse To FireBase Server
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env('FIREBASE_URL'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        curl_close($ch);

        $resultContent = json_decode($result);
        if($resultContent && $resultContent->failure == '1')
            throw new \Exception(FIREBASE_ERROR);
        return $resultContent;
    }
}
