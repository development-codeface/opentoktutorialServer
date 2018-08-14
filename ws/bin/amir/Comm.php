<?php
namespace Amir;

/**
 * Description of Comm
 *
 * @author Amir <amirsanni@gmail.com>
 * @date 26-Oct-2016
 */


use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Comm implements MessageComponentInterface {
    protected $clients;
    private $rooms;
    private $liveuser;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->rooms    = [];
        $this->liveuser = [];
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection
        echo "connection opened!!!";
        $this->clients->attach($conn);
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param ConnectionInterface $from
     * @param type $msg
     */
    public function onMessage(ConnectionInterface $from, $msg) {
        echo "message: {$msg}\n";
        $data = json_decode($msg);
        $action = $data->action;
        $room = isset($data->room) ? $data->room : "";
        if($action == 'subscribe'){
            echo "action: ".$data->userid;
            $this->liveuser[$data->userid] = $from;
            $from->send(json_encode(['action'=>'subscribe','userName'=>$data->userid,'userList'=>$this->liveuser]));
            $this->newUserLogedIn($from);     
        }else if($action == 'initiateVideoCall'){
            echo "Recived initate call !!!!";
            $client = $this->liveuser[$data->touser];
            $msg_to_send = json_encode(['action'=>'incomeVideoCall','userName'=>$data->userid,'sessionId'=>$data->sessionId]);
            $client->send($msg_to_send);
        }else if($action == 'endcall'){
            $client = $this->liveuser[$data->touser];
            $msg_to_send = json_encode(['action'=>'endcall','userName'=>$data->userid,'sessionId'=>$data->sessionId]);
            $client->send($msg_to_send);
        }else if($action =='callRejected'){
            $client = $this->liveuser[$data->touser];
            $msg_to_send = json_encode(['action'=>'callRejected','userName'=>$data->userid,'sessionId'=>$data->sessionId]);
            $client->send($msg_to_send);
        }else if($action =='startCall'){
            $client = $this->liveuser[$data->touser];
            $msg_to_send = json_encode(['action'=>'startCall','userName'=>$data->userid,'sessionId'=>$data->sessionId]);
            $client->send($msg_to_send);
        }
    }
    
    public function newUserLogedIn($from) { 
        echo ("send entered ....");
        $msg_to_broadcast = json_encode(['action'=>'newSub', 'userList'=>$this->liveuser]);
        
        //notify user that someone has joined room
        foreach($this->liveuser as $client){
            if ($client !== $from) {
                echo ("send subscribed ....");
                $client->send($msg_to_broadcast);
            }
        }
    }
    
    

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove connection
        $this->clients->detach($conn);
        echo "connection closed!!!!";
        if(count($this->liveuser)){
            foreach($this->liveuser as $key=>$ratchet_conn){
                if($ratchet_conn == $conn){
                    unset($this->liveuser[$key]);
                }
            }
        }
    }

    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param ConnectionInterface $conn
     * @param \Exception $e
     */
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
    
    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    /**
     * 
     * @param type $room
     * @param type $from
     */
    private function notifyUsersOfConnection($room, $from){
                        
        echo "User subscribed to room ".$room ."\n";

        $msg_to_broadcast = json_encode(['action'=>'newSub', 'room'=>$room]);

        //notify user that someone has joined room
        foreach($this->rooms[$room] as $client){
            if ($client !== $from) {
                $client->send($msg_to_broadcast);
            }
        }
    }
    
    /*
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    ********************************************************************************************************************************
    */
    
    private function notifyUsersOfDisconnection($room, $from){
        $msg_to_broadcast = json_encode(['action'=>'imOffline', 'room'=>$room]);

        //notify user that remote has left the room
        foreach($this->rooms[$room] as $client){
            if ($client !== $from) {
                $client->send($msg_to_broadcast);
            }
        }
    }
}
