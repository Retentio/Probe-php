<?php

namespace Retentio\Probe;

/**
 * This Probe is used to send your events to the Retent.io API
 *
 * @author Antoine Guiral <antoine@retent.io>
 */
class Probe {
    
    const ENDPOINT = 'http://tracker.retent.io/v1/';

    protected $_headers = ['Content-type: application/x-www-form-urlencoded;charset=UTF-8'];
    protected $_userAgent = 'Retentio Probe 1.0';
    
    private $application = [];
    private $user = [];
    private $event = [];
    
    
    public static $PROPERTY_BOOLEAN = "boolean";
    public static $PROPERTY_STRING = "string";
    public static $PROPERTY_NUMERIC = "numeric";
    public static $PROPERTY_DATE = "date";

    /**
     * 
     * @param int       $applicationId          Your application id (get it on the account management section on http://retent.io)
     * @param string    $applicationSecret      Your application secret key (get it on the account management section on http://retent.io)
     * @param mixed     $userId                   Something which identify your user. An Id is perfect, try to avoid email as the user can change it and it could affet your statistics.
     * @param int       $registeredAt           The unix timestamp of the registration date of the user on your app
     * @param string    $eventName                  The event you want to track. You should track the signin event.
     * @param int       $eventTimestamp              (optionnal) The unix timestamp of when the event is fired. If not set, we used the current time.
     */
    function __construct($applicationId, $applicationSecret, $userId, $registeredAt, $eventName = 'signin', $eventTimestamp = null) {
        $this->application['id'] = urlencode($applicationId);
        $this->application['secret'] = urlencode($applicationSecret);
        $this->user['id'] = urlencode($userId);
        $this->user['registeredAt'] = urlencode($registeredAt);
        $this->event['name'] = urlencode($eventName);
        $this->event['timestamp'] = urlencode(($eventTimestamp) ? $eventTimestamp : time());
    }
    
    /**
     * 
     * @param string    $email  If you want, you can send the user email.
     */
    public function setEmail($email = ''){
        if($email){
            $this->user['email'] = urlencode($email);
        }
    }
    
    /**
     * 
     * @param string    $avatar  If you want, you can send the user email.
     */
    public function setAvatar($avatar = ''){
        $this->user['avatar'] = urlencode($avatar);
    }
    /**
     * 
     * @param string    $username  If you want, you can send the user email.
     */
    public function setUsername($username = ''){
        $this->user['username'] = urlencode($username);
    }
    /**
     * 
     * @param string    $firstname  If you want, you can send the user email.
     */
    public function setFirstname($firstname = ''){
        $this->user['firstname'] = urlencode($firstname);
    }
    /**
     * 
     * @param string    $lastname  If you want, you can send the user email.
     */
    public function setLastname($lastname = ''){
        $this->user['lastname'] = urlencode($lastname);
    }
    
    /**
     * 
     * @param type $property
     * @param type $type
     * @param type $value
     */
    public function addUserProperty($property,$type,$value){
        $this->user[$property] = [
            "value" => urlencode($value),
            "type" => urlencode($type)
        ];
    }
    
    
    
    /**
     * 
     * @param int   $charge     If your event can be associated with a price, call this method to set it.
     */
    public function setCharge($charge = 0){
        $this->event['charge'] = urlencode($charge);
    }
    
    /**
     * 
     * @param type $property
     * @param type $type
     * @param type $value
     */
    public function addEventProperty($property,$type,$value){
        $this->event[$property] = [
            "value" => urlencode($value),
            "type" => urlencode($type)
        ];
    }
    

   

    /**
     * This method will send the event on a synch way
     * 
     * @throws RetentioException
     */
    public function send() {


        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, self::ENDPOINT);
        curl_setopt($ch, CURLOPT_POST, 6);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            "application" => $this->application,
            "user" => $this->user,
            "event" => $this->event,
            ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_headers);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->_userAgent);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        //execute post
        $r = curl_exec($ch);
        $error = curl_error($ch);
        if ($error) {
            throw new RetentioException('Tracker error : ' . $error);
        }
        curl_close($ch);
    }

    /**
     * This method will send the event on an asynch way
     */
    public function sendAsynch() {
        $post_string = http_build_query([
            "application" => $this->application,
            "user" => $this->user,
            "event" => $this->event,
            ]);

        $cmd = "curl -X POST -H '" . $this->_headers[0] . "'";
        $cmd.= " -d '" . $post_string . "' " . "'" . self::ENDPOINT . "'";

        $cmd .= " > /dev/null 2>&1 &";

        exec($cmd, $output, $exit);
        return $exit == 0;
    }

}

class RetentioException extends \Exception {
    
}

