<?php

/*
 * ===========================================
 * Ad-hoc|wireless sensor network simulator.
 * Developed by: Saeid.S.Nobakht
 * ===========================================
 */


//================================= Includes
//require_once "mixim2_3/src/base/group.h";     // include MiXim if needed //TODO
require_once "src/node.class.php";              // include node object
require_once "src/fileSave.class.php";          // include file saving handler object
require_once "src/funcs.inc.php";               // include needed functions

//================================= Configurations
$stageWidth  = 100;                 // the width limitation for distribution of nodes
$stageHeight = 100;                 // the height limitation for distribution of nodes
$nodesRange  = 5;                   // the range of send/receive for each node
$attackerNodesId = array(3,6);      // the attacker nodes
$initialNodeNumber = 15;            // initial number of nodes
$requestedWeight = 10;              // requested weight
$simulationQuantom = 1;             // the time unit for simulation
$nodeResponseTimer = 50;            // the maximum time that a packet waits at destination to other packets arrive from other paths
$dataFilePath = "sample-data/stage.data";       // the node distribution and location on the scene
$loadDataFlag = 1;                  // loading nodes' data flag, 1=load nodes' data from file
$saveDataFlag = 0;                  // saving nodes' data flag, 1=save nodes' data changes into file
$nodePositionData = "sample-data/points.data";  // the coordination of nodes for the manual locating situation


//================================= Initializing
$nodeQueue = array();               // nodes' queue
$nodePositions = array();           // 2D nodes positions list
$nodeResponseQueue = array();       // a queue for nodes' response timers
$packetQueue = array();             // packets' queue
$nodeCounter = 0;                   // node counter
$simulationFlag = 1;                // the main flag of executing simulation
$totalTimeCounter = 0;              // the total time of simulation
$totalPackets['RREQ'] = 0;          // total number of "RREQ" packets counter
$totalPackets['RREP'] = 0;          // total number of "RREP" packets counter
$totalHops = 0;                     // total number of hops
$totalRequests['accepted'] = 0;     // total number of all accepted routing requests (greater than requested weight)
$totalRequests['all'] = 0;          // total number of all received requests (valid + invalid)
$totalAttacks['success'] = 0;       // total number of all successful attacks
$totalAttacks['all'] = 0;           // total number of all attacks (successful + failed)
$attackerNode1 = -1;                // identifier of attacker node 1
$attackerNode2 = -1;                // identifier of attacker node 2
$logGen = new fileSave();           // an object for handling output file


//================================= Load or Generate Nodes
if(file_exists($dataFilePath)){
    if($loadDataFlag==1 && $saveDataFlag==1){
        $nodeQueue = unserialize(file_get_contents($dataFilePath));
        $noNodes = count($nodeQueue);
        $nodeQueue = generatNodesRandomly();
        $nodeQueueStr = serialize($nodeQueue);
        unlink($dataFilePath);
        file_put_contents($dataFilePath, $nodeQueueStr);
    }
    elseif($saveDataFlag==1){
        unlink($dataFilePath);
        $nodeQueue = array();
        $nodeQueue = generatNodesRandomly();
        $nodeQueueStr = serialize($nodeQueue);
        file_put_contents($dataFilePath, $nodeQueueStr);
    }
    elseif($loadDataFlag==1){
        $nodeQueue = array();
        $nodeQueue = unserialize(file_get_contents($dataFilePath));
        $noNodes = count($nodeQueue);
        if($noNodes>=$initialNodeNumber){
            $nodeQueue = array();
            $noNodes = count($nodeQueue);
            $tmp = explode("\r\n", file_get_contents($nodePositionData));
            for($i=0;$i<$initialNodeNumber;$i++){
                $cordinates = explode(",",$tmp[$i]);
                array_push($nodePositions,$cordinates);
            }
            $nodeQueue = generateNodesManually();
            
            /*
            $nodeQueue = array_slice($nodeQueue, 0, $initialNodeNumber);            
            foreach($nodeQueue as $nodeKey=>$nodeVal){
                $neighborsLength = count($nodeVal->neighbors);
                for($i=0;$i<$neighborsLength;$i++){
                    if($nodeVal->neighbors[$i][0]>$initialNodeNumber){
                        unset($nodeQueue[$nodeKey]->neighbors[$i]);
                    }
                }
                $nodeQueue[$nodeKey]->neighbors = array_values($nodeQueue[$nodeKey]->neighbors);
            }
            */
        }
        $noNodes = count($nodeQueue);
    }
    else{
        die("Check Load/Save Data Flags !");
    }
}
else{
    $nodeQueue = generatNodesRandomly();
    $nodeQueueStr = serialize($nodeQueue);
    file_put_contents($dataFilePath, $nodeQueueStr);
}


//================================= Randomly Select Source & Destination Node
//$node1 = $nodeQueue[mt_rand(0,$initialNodeNumber-1)];
//$node2 = $nodeQueue[mt_rand(0,$initialNodeNumber-1)];

//================================= Manualy Select Source & Destination Node
$node1 = $nodeQueue[1]; // node with id=2
$node2 = $nodeQueue[7]; // node with id=8


//================================= Randomly Select Source & Destination Node
routePacket($node1, $node2, $requestedWeight);

//================================= Main Loop of Simulation
while($simulationFlag){
    foreach($packetQueue as $packetKey => $packetVal){
        /*
        if($packetVal['pck']->type=="RREP"){
            echo "Nothing";
        }
        */
        if($packetVal['cnt']<=0){
            $nodeQueue[$packetVal['dst']-1]->packetReceived($packetVal,$packetKey);
        }
        else{
            $packetQueue[$packetKey]['cnt']--;
        }
    }
    //$packetQueue = array_values($packetQueue);
    foreach($nodeResponseQueue as $respKey=>$respVal){
        if($respVal<=0){
            $nodeQueue[intval($respKey)-1]->sendResponse($respVal,$respKey);
            unset($nodeResponseQueue[$respKey]);
        }
        else{
            $nodeResponseQueue[$respKey]--;
        }
    }
    
    $totalTimeCounter++;
        
    
    // simulation stop condition    
    if(count($packetQueue)==0 && count($nodeResponseQueue)==0){
        $simulationFlag = 0;
    }
}


// Show results of simulation
echo "=================================================================\n";
echo "Number of Nodes: \t\t\t\t".$initialNodeNumber."<br>\n";
echo "Source Node: \t\t\t\t\t".$node1->id."<br>\n";
echo "Destination Node: \t\t\t\t".$node2->id."<br>\n";
echo "Requested Weight: \t\t\t\t".$requestedWeight."<br>\n";
echo "Total 'RREQ' Packets: \t\t\t\t".$totalPackets['RREQ']."<br>\n";
echo "Total 'RREP' Packets: \t\t\t\t".$totalPackets['RREP']."<br>\n";
echo "Total Hops: \t\t\t\t\t".$totalHops."<br>\n";
echo "Total Valid Received Requests to Node ".$node2->id.":\t".$totalRequests['accepted']."<br>\n";
echo "Total Received Requests to Node ".$node2->id.":\t\t".$totalRequests['all']."<br>\n";
echo "Total Successful Attacks (".$attackerNode1.",".$attackerNode2."):\t\t\t".$totalAttacks['success']."<br>\n";
echo "Total Attacks (".$attackerNode1.",".$attackerNode2."):\t\t\t\t".$totalAttacks['all']."<br>\n";
echo "=================================================================\n";
echo "Running Finished !";






?>