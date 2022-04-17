<?php

/*
 * ===========================================
 * Ad-hoc|wireless sensor network simulator.
 * Developed by: Saeid.S.Nobakht
 * ===========================================
 */


//This file defines the specifications of a node of a network.
// Also, the functions for receiving packets and generating responses are implemented here.

require_once "packet.class.php";                // each node could send or receive packets


class  Node{
    /* Member variables */
    var $x;                                     // the X value in a 2D space
    var $y;                                     // the Y value in a 2D space
    var $probability;                           // the probability of each node on the path
    var $range;                                 // the range that node could send or receive data from its neighbors
    var $id;                                    // the identifier of node (starts from 1)
    var $neighbors;                             // the list or table of node's neighbors
    var $requestTable;                          // the received request table
    var $stats;                                 // the stats for each node
    //var $type;
    
    // the constructor
    function __construct($x=-1,$y=-1,$probability=0,$range=0,$id=-1,$neighbors=array()){
        $this->x = $x;
        $this->y = $y;
        $this->probability = $probability;
        $this->range = $range;
        $this->id = $id;
        $this->neighbors = $neighbors;
        $this->requestTable = array();
        $this->stats = 0;
        //$this->type = "Regular";    
    }
    
    function createNotification(){
        global $logGen;
        $logGen->saveLine("Node $this->id Created at x=$this->x,y=$this->y");
        echo "Node $this->id Created at x=$this->x,y=$this->y<br>\n";
    }
    
    function createPacket($destination, $reqWeight){
        $packetForSend = new Packet("RREQ",$this->id,$destination,1,1,0,$reqWeight,array());
    }
    
    function packetReceived($packetInfo,$packetIndex){
        global $attackerNode1,$attackerNode2,$totalAttacks,$attackerNodesId,$totalHops,$logGen,$totalPackets,$packetQueue, $nodeQueue, $simulationQuantom, $nodeResponseQueue, $nodeResponseTimer;
        $totalHops++;
        if($packetInfo['pck']->type == "RREQ"){
            $logGen->saveLine("Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]");  // zakhire ettelaate baste dar file khorooji
            echo "Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]<br>\n";          // namayeshe ettelaate baste 
            
            // analyzing packet structure
            if($packetInfo['pck']->dstAddr==$this->id){
                if(!in_array($packetInfo['pck'],$this->requestTable)){
                    array_push($this->requestTable, $packetInfo['pck']);
                    $nodeResponseQueue[strval($this->id)] = $nodeResponseTimer;
                }
            }
            elseif(in_array($this->id,$packetInfo['pck']->path)){
                //$logGen->saveLine("Packet Already Exist, Loop Detected and Skipped !");
                //echo "Packet Already Exist, Loop Detected and Skipped !<br>\n";
            }                
            else{
                if(in_array($this->id,$attackerNodesId)){
                    $attackerNode1 = $attackerNodesId[0];
                    $attackerNode2 = $attackerNodesId[1];
                    if(!in_array($this->id,$packetInfo['pck']->path)){
                        if($this->id == $attackerNode1){
                            if(in_array($attackerNode2, $packetInfo['pck']->path)){
                                $nodePosition = array_search($attackerNode2, $packetInfo['pck']->path);
                                for($k=$nodePosition+1; $k<count($packetInfo['pck']->path); $k++){
                                    array_pop($packetInfo['pck']->path);
                                    $packetInfo['pck']->hopCount--;
                                }
                                $totalAttacks['all']++;
                            }
                            else{
                                $packetInfo['pck']->hopCount++;
                            }                
                        }
                        else{
                            if(in_array($attackerNode1, $packetInfo['pck']->path)){
                                $nodePosition = array_search($attackerNode1, $packetInfo['pck']->path);
                                for($k=$nodePosition+1; $k<count($packetInfo['pck']->path); $k++){
                                    array_pop($packetInfo['pck']->path);
                                    $packetInfo['pck']->hopCount--;
                                }
                                $totalAttacks['all']++;
                            }
                            else{
                                $packetInfo['pck']->hopCount++;
                            }
                        }
                    }
                    
                }

                array_push($packetInfo['pck']->path, $this->id);
                //$packetInfo['pck']->pathWeight += $packetInfo['pck']->probability*$packetInfo['pck']->pathReq;
                $packetInfo['pck']->pathWeight += $this->probability*100;
                $packetSrc = $packetInfo['src'];
                foreach($this->neighbors as $neighborKey => $neighborVal){
                    if($packetSrc==$neighborVal[0] || $packetInfo['pck']->srcAddr==$neighborVal[0]){
                        continue;
                    }
                    elseif(in_array($neighborVal[0], $packetInfo['pck']->path)){
                        continue;
                    }
                    else{
                        $newPacket = new Packet("RREQ",$packetInfo['pck']->srcAddr,$packetInfo['pck']->dstAddr,0,$packetInfo['pck']->hopCount,$packetInfo['pck']->pathWeight,$packetInfo['pck']->pathReq,$packetInfo['pck']->path);
                        $totalPackets['RREQ']++;
                        $packetInfo['src'] = $this->id;
                        $packetInfo['dst'] = $neighborVal[0];
                        $counter = $neighborVal[2]/$simulationQuantom;
                        $packetInfo['cnt'] = $counter;
                        $packetInfo['pck'] = $newPacket;
                        array_push($packetQueue, $packetInfo);
                        $logGen->saveLine("Packet Sent, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]");
                        echo "Packet Sent, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]<br>\n";
                    }
                }                            
            }
            unset($packetQueue[$packetIndex]);
        }
        elseif($packetInfo['pck']->type == "RREP"){
            if(count($packetInfo['pck']->path)==0 && $this->id != $packetInfo['pck']->srcAddr){
                $logGen->saveLine("Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]");
                echo "Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]<br>\n";
                $nextNodeVal = $packetInfo['pck']->srcAddr;             
                $packetInfo['pck']->hopCount++;
                $packetInfo['pck']->pathWeight += $this->probability*100;
                $packetInfo['src'] = $this->id;
                $packetInfo['dst'] = $nextNodeVal;
                $packetInfo['cnt'] = sqrt(($this->x-$nodeQueue[$nextNodeVal-1]->x)*($this->x-$nodeQueue[$nextNodeVal-1]->x)+($this->y-$nodeQueue[$nextNodeVal-1]->y)*($this->y-$nodeQueue[$nextNodeVal-1]->y))/$simulationQuantom;
                $totalPackets['RREP']++;
                array_push($packetQueue, $packetInfo);
            }
            elseif($this->id != $packetInfo['pck']->srcAddr && $this->id != $packetInfo['pck']->dstAddr){
                $logGen->saveLine("Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]");
                echo "Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr.",PATH=[".implode(" ",$packetInfo['pck']->path)."]<br>\n";
                $nextNodeVal = array_pop($packetInfo['pck']->path);             
                $packetInfo['pck']->hopCount++;
                $packetInfo['pck']->pathWeight += $this->probability*100;
                $packetInfo['src'] = $this->id;
                $packetInfo['dst'] = $nextNodeVal;
                $packetInfo['cnt'] = sqrt(($this->x-$nodeQueue[$nextNodeVal-1]->x)*($this->x-$nodeQueue[$nextNodeVal-1]->x)+($this->y-$nodeQueue[$nextNodeVal-1]->y)*($this->y-$nodeQueue[$nextNodeVal-1]->y))/$simulationQuantom;
                $totalPackets['RREP']++;
                array_push($packetQueue, $packetInfo);
            }
            elseif($this->id == $packetInfo['pck']->srcAddr){
                $logGen->saveLine("Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr);
                echo "Packet Received, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr."<br>\n";
                $logGen->saveLine("Node ".$this->id." Received Response from Destination Node ".$packetInfo['pck']->dstAddr);
                echo "Node ".$this->id." Received Response from Destination Node ".$packetInfo['pck']->dstAddr."<br>\n";
            }
            unset($packetQueue[$packetIndex]);
            
            
        }
    }
    
    function sendResponse($responseVal,$responseKey){
        global $totalAttacks,$attackerNode1,$attackerNode2,$totalRequests,$totalHops,$totalPackets,$packetQueue,$nodeResponseQueue,$logGen,$nodeQueue,$simulationQuantom;
        $minValidWeightVal = 100000;
        $minValidWeightKey = -1;        
        foreach($this->requestTable as $reqKey => $reqVal){
            $totalRequests['all']++;
            if($reqVal->pathWeight >= $reqVal->pathReq){
                $totalRequests['accepted']++;
                if($reqVal->pathWeight < $minValidWeightVal){
                    $minValidWeightVal = $reqVal->pathWeight;
                    $minValidWeightKey = $reqKey;
                }
            }
        }
        if($minValidWeightKey != -1){
            $logGen->saveLine("Found Path from ".$reqVal->srcAddr." to ".$reqVal->dstAddr." with Weight ".$reqVal->pathWeight);
            echo "Found Path from ".$reqVal->srcAddr." to ".$reqVal->dstAddr." with Weight ".$reqVal->pathWeight."<br>\n";
            
            $returnPathInfo = $this->requestTable[$minValidWeightKey];
            if(is_array($returnPathInfo->path)){
                if(in_array($attackerNode1,$returnPathInfo->path)&&in_array($attackerNode2,$returnPathInfo->path)){
                    $totalAttacks['success']++;
                }    
            }
            $lastPathNode = $nodeQueue[array_pop($returnPathInfo->path)-1];
            $newPacket = new Packet("RREP", $returnPathInfo->srcAddr, $returnPathInfo->dstAddr,0,1,$this->probability*100,$returnPathInfo->pathReq,$returnPathInfo->path);
            $totalPackets['RREP']++;
            $counter = sqrt(($this->x-$lastPathNode->x)*($this->x-$lastPathNode->x)+($this->y-$lastPathNode->y)*($this->y-$lastPathNode->y))/$simulationQuantom;
            $packetInfo = array("src"=>$this->id,"dst"=>$lastPathNode->id,"pck"=>$newPacket,"cnt"=>$counter);
            array_push($packetQueue,$packetInfo);
            
        }
        else{
            $logGen->saveLine("No Valid Path from ".$reqVal->srcAddr." to ".$reqVal->dstAddr." with Weight ".$reqVal->pathReq);
            echo "No Valid Path from ".$reqVal->srcAddr." to ".$reqVal->dstAddr." with Weight ".$reqVal->pathReq."<br>\n";
        }
        unset($nodeResponseQueue[$responseKey]);
    }
}


?>