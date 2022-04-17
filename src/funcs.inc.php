<?php

/*
 * ===========================================
 * Ad-hoc|wireless sensor network simulator.
 * Developed by: Saeid.S.Nobakht
 * ===========================================
 */


// starting packet routing
function routePacket($src, $dst, $reqWeight){
    global $packetQueue,$totalPackets,$logGen;
    global $nodeQueue,$totalHops;
    global $simulationQuantom;
    foreach($src->neighbors as $neighborKey => $neighborVal){                                               // for all neighbors
        $newPacket = new Packet("RREQ",$src->id,$dst->id,0,1,0,$reqWeight,array());    // create a "RREQ" packet
        $totalPackets['RREQ']++;                                                                            // increase number of all "RREQ" packets by 1
        $counter = $neighborVal[2]/$simulationQuantom;                                                      // calculate distance and counter
        $packetInfo = array("src"=>$src->id,"dst"=>$neighborVal[0],"pck"=>$newPacket,"cnt"=>$counter);      // prepare packet for simulation queue
        array_push($packetQueue,$packetInfo);                                                         // add packet into simulation queue
        $logGen->saveLine("Packet Sent, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr); // save result of creating a packet into output file (log)
        echo "Packet Sent, ".$packetInfo['src']." => ".$packetInfo['dst'].", Type='".$packetInfo['pck']->type."', SRC=".$packetInfo['pck']->srcAddr.",DST=".$packetInfo['pck']->dstAddr."<br>\n";      // show result of creating a packet

    }
}

// check the random generated number be unique
function checkRandomNumber($newNode){
    global $nodeQueue;
    foreach($nodeQueue as $nodeKey=>$nodeVal){
        if($nodeVal->x==$newNode->x && $nodeVal->y==$newNode->y){
            return false;
        }
    }
    return true;
}

// creating nodes manually (did not use on test final scenarios)
function generateNodesManually(){
    global $nodePositions;
    global $totalHops,$stageWidth,$stageHeight,$nodesRange,$initialNodeNumber,$nodeQueue,$nodeResponseQueue,$nodeCounter,$simulationQuantom,$nodeResponseTimer,$dataFilePath;    
    $noNodes = count($nodeQueue);
    $nodeCounter = $noNodes;
    // initialize nodes in stage manually
    for($i=$noNodes; $nodeCounter<$initialNodeNumber; $i++){    
        if(count($nodeQueue)==0){
            $newNode = new Node($nodePositions[$i][0], $nodePositions[$i][1],1,$nodesRange,$nodeCounter+1);
            if(!checkRandomNumber($newNode)){
                continue;
            }
            array_push($nodeQueue, $newNode);
            $newNode->createNotification();
            $nodeCounter++;
            continue;
        }
        else{
            $newNode = new Node($nodePositions[$i][0], $nodePositions[$i][1],0,$nodesRange,-1);
            if(!checkRandomNumber($newNode)){
                continue;
            }
            $maxProbabilityVal = 0;
            $maxProbabilityKey = -1;
            $nodeAddedFlag = 0;
            foreach($nodeQueue as $nodeKey => $nodeVal){        
                $distance = sqrt(($newNode->x - $nodeVal->x)*($newNode->x - $nodeVal->x)+($newNode->y - $nodeVal->y)*($newNode->y - $nodeVal->y));
                
                if($distance <= $nodesRange){
                    if($nodeAddedFlag == 0){
                        $nodeCounter++;
                        $newNode->id = $nodeCounter;                
                        $nodeAddedFlag = 1;
                    }
                    
                    array_push($newNode->neighbors,array($nodeVal->id, $nodeVal->probability,$distance));
                    array_push($nodeQueue[$nodeKey]->neighbors,array($newNode->id, $newNode->probability,$distance));
                    if($nodeVal->probability > $maxProbabilityVal){
                        $maxProbabilityVal = $nodeVal->probability;
                        $maxProbabilityKey = $nodeKey;
                    }
                }
            }
            if($maxProbabilityKey!=-1){
                $nodeQueue[$maxProbabilityKey]->probability /= 2;
                $newNode->probability = $nodeQueue[$maxProbabilityKey]->probability;
                array_push($nodeQueue, $newNode);
                $newNode->createNotification();
            }    
            
            foreach($nodeQueue as $nodeKey => $nodeVal){
                for($j=0; $j<count($nodeQueue[$nodeKey]->neighbors); $j++) {
                    $t1 = $nodeQueue[$nodeKey]->neighbors[$j][0]-1;
                    $nodeQueue[$nodeKey]->neighbors[$j][1] = $nodeQueue[$nodeQueue[$nodeKey]->neighbors[$j][0]-1]->probability;
                }
            }    
            //processAloneNodes($nodeQueue,0);        
        }        
    }
    

    $nodeQueueStr = serialize($nodeQueue);        
    file_put_contents("stageM.data", $nodeQueueStr);    // save result of manually creation of a node
    return $nodeQueue;                                          // return resulted queue of nodes creation
}

// this function generates nodes randomly and save created stage
function generatNodesRandomly(){
    global $totalHops,$stageWidth,$stageHeight,$nodesRange,$initialNodeNumber,$nodeQueue,$nodeResponseQueue,$nodeCounter,$simulationQuantom,$nodeResponseTimer,$dataFilePath;    
    $noNodes = count($nodeQueue);                               // get number of current nodes
    $nodeCounter = $noNodes;                                    // set number of nodes
    // initialize nodes in stage randomely
    for($i=$noNodes; $nodeCounter<$initialNodeNumber; $i++){    // adding needed nodes
        if(count($nodeQueue)==0){                               // if nodes queue is empty, then create a Parent node with probability 1.
            $newNode = new Node(mt_rand(0,$stageWidth), mt_rand(0,$stageHeight),1,$nodesRange,$nodeCounter+1);
            if(!checkRandomNumber($newNode)){                   // check uniqueness of randomly generated numbers
                continue;
            }
            array_push($nodeQueue, $newNode);             // add new node to queue
            $newNode->createNotification();                      // notify creation of new node
            $nodeCounter++;
            continue;
        }
        else{
            $newNode = new Node(mt_rand(0,$stageWidth), mt_rand(0,$stageHeight),0,$nodesRange,-1);  // if a node already exists, create a node with temporary probability of zero.
            if(!checkRandomNumber($newNode)){                    // check uniqueness of randomly generated numbers
                continue;
            }
            $maxProbabilityVal = 0;
            $maxProbabilityKey = -1;
            $nodeAddedFlag = 0;
            foreach($nodeQueue as $nodeKey => $nodeVal){         // check the new node is near to which node
                $distance = sqrt(($newNode->x - $nodeVal->x)*($newNode->x - $nodeVal->x)+($newNode->y - $nodeVal->y)*($newNode->y - $nodeVal->y)); // calculate distance to previous nodes
                
                if($distance <= $nodesRange){                    // if the new node is under the areas of previous nodes
                    if($nodeAddedFlag == 0){                     // the node can be added to network
                        $nodeCounter++;
                        $newNode->id = $nodeCounter;             // save node identifier
                        $nodeAddedFlag = 1;                      // the node is adding to network, thus there is no need to create extra node that will be under the borders
                    }
                    
                    array_push($newNode->neighbors,array($nodeVal->id, $nodeVal->probability,$distance));       // for each node that is under the range, add it to neighbors list
                    array_push($nodeQueue[$nodeKey]->neighbors,array($newNode->id, $newNode->probability,$distance));  // save calculated distance to list
                    if($nodeVal->probability > $maxProbabilityVal){                                             // find maximum probability among neighbors
                        $maxProbabilityVal = $nodeVal->probability;
                        $maxProbabilityKey = $nodeKey;
                    }
                }
            }
            if($maxProbabilityKey!=-1){                                              // for the maximum probability tha was found
                $nodeQueue[$maxProbabilityKey]->probability /= 2;                    // divide the probability of nodes to sum of them
                $newNode->probability = $nodeQueue[$maxProbabilityKey]->probability;
                array_push($nodeQueue, $newNode);                              // add node to nodes queue
                $newNode->createNotification();                                      // notify creation of new node
            }    
            
            foreach($nodeQueue as $nodeKey => $nodeVal){                              // update the probabilty of this node for all nodes that are neighbors of it
                for($j=0; $j<count($nodeQueue[$nodeKey]->neighbors); $j++) {
                    $t1 = $nodeQueue[$nodeKey]->neighbors[$j][0]-1;
                    $nodeQueue[$nodeKey]->neighbors[$j][1] = $nodeQueue[$nodeQueue[$nodeKey]->neighbors[$j][0]-1]->probability;
                }
            }    
            //processAloneNodes($nodeQueue,0);        
        }        
    }
    
    return $nodeQueue;                                                                 // return queue of created nodes
}


// this function is used for creating network when the CONNECTIVITY condition will not exist.
// (for our tests, we did not use it)
function processAloneNodes(&$nodeQueue, $nodeIndex)
{
    global $nodesRange;
    $haveChangeFlag = 0;
    //foreach($nodeQueue as $nodeKey => $nodeVal){                    
    for($i=$nodeIndex; $i<count($nodeQueue); $i++){
        $nodeKey = $i;
        $nodeVal = $nodeQueue[$i];        
        if($nodeVal->probability ==0){
            $maxProbabilityVal = 0;
            $maxProbabilityKey = -1;
            foreach($nodeQueue as $nKey => $nVal){                        
                if($nodeKey!=$nKey && $nVal->probability!=0){
                    $distance = sqrt(($nodeVal->x - $nVal->x)*($nodeVal->x - $nVal->x)+($nodeVal->y - $nVal->y)*($nodeVal->y - $nVal->y));
                    if($distance <= $nodesRange){
                        array_push($nodeQueue[$i]->neighbors,array($nodeQueue[$nKey]->id, $nodeQueue[$nKey]->probability));
                        array_push($nodeQueue[$nKey]->neighbors,array($nodeQueue[$i]->id, $nodeQueue[$i]->probability));
                        $haveChangeFlag = 1;
                        if($nVal->probability > $maxProbabilityVal){
                            $maxProbabilityVal = $nVal->probability;
                            $maxProbabilityKey = $nKey;
                        }
                    }
                }
            }
            if($haveChangeFlag==1){
                $nodeQueue[$maxProbabilityKey]->probability /= 2; 
                $nodeQueue[$i]->probability = $nodeQueue[$maxProbabilityKey]->probability;
                $maxProbabilityVal = $nodeVal->probability;
                $maxProbabilityKey = $nodeKey;    
                $haveChangeFlag = 0;
                if($i<count($nodeQueue)-1){
                    processAloneNodes($nodeQueue, $i+1);
                    break;
                }
                else{
                    continue;
                }                
            }            
        }        
    }
}
 




?>