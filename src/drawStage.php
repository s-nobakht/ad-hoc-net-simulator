<?php
/*
 * ===========================================
 * Ad-hoc|wireless sensor network simulator.
 * Developed by: Saeid.S.Nobakht
 * ===========================================
 */



// This program reads the stored node information and displays it graphically in the browser.
require_once "node.class.php";

$dataFilePath = "stage.data";                                   // nodes' data file path
$nodeQueue = array();
$nodeQueue = unserialize(file_get_contents($dataFilePath));     // read data file

$xVals = array();                                               // initialize X values
$yVals = array();                                               // initialize Y values
foreach($nodeQueue as $nodeKey=>$nodeVal){                      // calculate (x,y) coordination for all nodes
    array_push($xVals, $nodeVal->x*10);
    array_push($yVals, $nodeVal->y*10);
}

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Stage</title>    
    </head>
    <body>
        <canvas id="stage" width="1000" height="1000" style="border:1px solid #FFFFFF;">
            Canvas Test !
        </canvas>
        <script type="text/javascript">
            var showRangeOfNodes = 1;               // if set to 1, borders of nodes will shown
            var showLabelOfNodes = 1;               // if set to 1, numbers of nodes will shown
            function calcPointsCirc( cx,cy, rad, dashLength)
            {
                var n = rad/dashLength,
                    alpha = Math.PI * 2 / n,
                    pointObj = {},
                    points = [],
                    i = -1;

                while( i < n )
                {
                    var theta = alpha * i,
                        theta2 = alpha * (i+1);

                    points.push({x : (Math.cos(theta) * rad) + cx, y : (Math.sin(theta) * rad) + cy, ex : (Math.cos(theta2) * rad) + cx, ey : (Math.sin(theta2) * rad) + cy});
                    i+=2;
                }              
                return points;            
            }
            var canvas = document.getElementById("stage");
            var ctx = canvas.getContext("2d");            
            var xx = <?php echo "[".implode(",",$xVals)."];"; ?>
            var yy = <?php echo "[".implode(",",$yVals)."];"; ?>
            
            for (i = 0; i < xx.length; i++) {
                ctx.beginPath();
                ctx.arc(xx[i],yy[i],5,0,2*Math.PI);
                ctx.stroke();
                ctx.fillStyle = "red";
                ctx.fill();
                
                if(showLabelOfNodes){
                    ctx.font = "15px Consolas";
                    ctx.fillStyle = "black";
                    ctx.fillText(i+1,xx[i]-5,yy[i]-5);
                    
                }
                
                if(showRangeOfNodes){
                    var pointArray= calcPointsCirc(xx[i],yy[i],50, 1);
                    //ctx.strokeStyle = "rgb(0,0,200)";
                    ctx.strokeStyle = 'hsl(' + 360 * Math.random() + ', 50%, 50%)';
                    ctx.beginPath();

                    for(p = 0; p < pointArray.length; p++){
                        ctx.moveTo(pointArray[p].x, pointArray[p].y);
                        ctx.lineTo(pointArray[p].ex, pointArray[p].ey);
                        ctx.stroke();
                    }

                    ctx.closePath();    
                }
            }
            
        </script>
    </body>
</html>

<?php


?>