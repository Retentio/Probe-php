<?php

namespace Retentio\Probe;

/**
 * This Probe is used to send your events to the Retent.io API
 *
 * @author Antoine Guiral <antoine@retent.io>
 */
class Probe {
    
    const ENDPOINT = 'http://tracker.retent.io/';

    protected $_headers = ['Content-type: application/x-www-form-urlencoded;charset=UTF-8'];
    protected $_userAgent = 'Retentio Probe 1.0';
    
    private $data = [];

    /**
     * 
     * @param int       $applicationId          Your application id (get it on the account management section on http://retent.io)
     * @param string    $applicationSecret      Your application secret key (get it on the account management section on http://retent.io)
     * @param mixed     $user                   Something which identify your user. An Id is perfect, try to avoid email as the user can change it and it could affet your statistics.
     * @param int       $registeredAt           The unix timestamp of the registration date of the user on your app
     * @param string    $event                  The event you want to track. You should track the signin event.
     * @param int       $eventTime              (optionnal) The unix timestamp of when the event is fired. If not set, we used the current time.
     */
    function __construct($applicationId, $applicationSecret, $user, $registeredAt, $event = 'signin', $eventTime = null) {
        $this->data['app'] = urlencode($applicationId);
        $this->data['secret'] = urlencode($applicationSecret);
        $this->data['user'] = urlencode($user);
        $this->data['registered_at'] = urlencode($registeredAt);
        $this->data['event'] = urlencode($event);
        $this->data['event_time'] = urlencode(($eventTime) ? $eventTime : time());
    }
    
    /**
     * 
     * @param int   $charge     If your event can be associated with a price, call this method to set it.
     */
    public function setCharge($charge = 0){
        $this->data['charge'] = urlencode($charge);
    }
    
    /**
     * 
     * @param string    $email  If you want, you can send the user email. Currently not used (01/02/2013).
     */
    public function setEmail($email = ''){
        $this->data['email'] = urlencode($email);
    }

    /**
     * 
     * @return string The concatened string of all the params
     */
    private function toStringData() {
        $fields = '';
        //url-ify the data for the POST
        foreach ($this->data as $key => $value) {
            $fields .= $key . '=' . $value . '&';
        }
        rtrim($fields, '&');
        return $fields;
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
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->toStringData());
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
        $post_string = $this->toStringData();

        $parts = parse_url(self::ENDPOINT);

        $out = "POST " . $parts['path'] . " HTTP/1.1\r\n";
        $out.= "Host: " . $parts['host'] . "\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: " . strlen($post_string) . "\r\n";
        $out.= "Connection: Close\r\n\r\n";
        if (isset($post_string)){
            $out.= $post_string;
            $fp = @fsockopen($parts['host'], isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
            if($fp){
                fwrite($fp, $out);
                fclose($fp);
            }
        }
        
        
            
    }

}

class RetentioException extends Exception {
    
}

?>
