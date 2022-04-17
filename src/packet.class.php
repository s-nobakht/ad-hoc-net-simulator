<?php
/*
 * ===========================================
 * Ad-hoc|wireless sensor network simulator.
 * Developed by: Saeid.S.Nobakht
 * ===========================================
 */

// This file defines the specifications and fields of a packet.

class  Packet{
    /* Member variables */    
    var $type;                    // The packet type could be one from this set: {"RREQ", "RREP", "DATA"}
    var $srcAddr;                 // source node identifier
    var $dstAddr;                 // destination node identifier
    var $seqNumber;               // packet sequence number
    var $hopCount;                // number of hops
    var $pathWeight;              // the weight of the targeted path
    var $pathReq;                 // the weight of requested path
    var $path;                    // A path is a list of nodes
    
    // constructor
    function __construct($type="RREQ",$srcAddr=0,$dstAddr=0,$seqNumber=0,$hopCount=1,$pathWeight=0,$pathReq=0,$path=array()){
        global $logGen;
        $this->type = $type;
        $this->srcAddr = $srcAddr;
        $this->dstAddr = $dstAddr;
        $this->seqNumber = $seqNumber;
        $this->hopCount = $hopCount;
        $this->pathWeight = $pathWeight;
        $this->pathReq = $pathReq;
        $this->path = $path;                
        $logGen->saveLine("Packet Created, Type='".$this->type."', SRC=".$this->srcAddr.",DST=".$this->dstAddr);
        echo "Packet Created, Type='".$this->type."', SRC=".$this->srcAddr.",DST=".$this->dstAddr."<br>\n";

    }
    
    /* Member functions */
    
}


?>