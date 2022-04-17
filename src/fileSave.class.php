<?php
/*
 * ===========================================
 * Ad-hoc|wireless sensor network simulator.
 * Developed by: Saeid.S.Nobakht
 * ===========================================
 */


// This class is for storing results and managing output files.

class fileSave
{
    var $filePath;                         // output path for saving download link files
    var $lineLimit;                        // maximum number of lines in each file, 0 = No Limit
    var $sizeLimit;                        // maximum size of each file (bytes), 0 = No Limit
    var $fileName;                         // prefix of each file (before number), for example : 'name_1.txt'
    private $fileHandler;                  // PRIVATE : this is for handling output files
    private $fileNumber;                   // PRIVATE : this varibale saves number of output file in any time (start from 1)
    private $lineNumber;                   // PRIVATE : this varibale saves number of lines in current output file ( 0 to (lineLimit)-1)
    
    // constructor of class
    // initialize variable
    // crawl output file directory, find latest file, check limits and finally append last file or 
    // create new one
    function __construct($outputFilePath="./", $fileLineLimit=0, $fileSizeLimit=0, $fileNamePrefix="output")
    {                
        $this->filePath   = $outputFilePath;      // set file path
        $this->lineLimit  = $fileLineLimit;       // set number of lines limit
        $this->sizeLimit  = $fileSizeLimit;       // set numbers of bytes limit (size limit)
        $this->fileName   = $fileNamePrefix;      // set file common names (prefix before numbers)

        $filesList = scandir($this->filePath);    // create a list of existing files in directory
        $lastFileNumber = count($filesList)-2;    // remove to redundant values from array count result and get number of files
        $lastFileName = $this->fileName."_".$lastFileNumber.".txt";
        
        // check for existing log file with our configured pattern
        // if file(s) exist, we check size and line limitations
        // if file does not exist, we create new one with our pattern
        if(file_exists($this->filePath.$lastFileName))
        {
            // calculate numbers of lines of log file
            $currentFileLines = $this->getFileLines($this->filePath.$lastFileName);
            
            // check size limit
            if($this->sizeLimit != 0)
            {                
                if(filesize($this->filePath.$lastFileName) >= $this->sizeLimit)
                {
                    $this->fileNumber = $lastFileNumber + 1;
                    $this->lineNumber = 0;
                }
                else
                {
                    $this->fileNumber = $lastFileNumber;
                    $this->lineNumber = $currentFileLines;
                }
            }
            else  // dont check size limit
            {
                $this->fileNumber = $lastFileNumber;
            }
            
            // check line limit            
            if($this->lineLimit != 0)
            {                            
                if($currentFileLines >= $this->lineLimit)
                {
                    $this->fileNumber = $lastFileNumber + 1;
                    $this->lineNumber = 0;
                }
                else
                {
                    $this->fileNumber = $lastFileNumber;
                    $this->lineNumber = $currentFileLines;
                }
            }
            else  // dont check size limit
            {
                $this->fileNumber = $lastFileNumber;
            }                                
        }
        else  // file does not exist
        {
            $this->fileNumber = 1;
            $this->lineNumber = 0;
        }
     
        // open log file, file is ready to use
        $this->fileHandler = fopen($this->filePath.$this->fileName."_".$this->fileNumber.".txt", 'a') or die("can't open file");        
    
    }// end of constructor
    
    // destructor of class
    // close output file before exit
    function __destruct() 
    {
        fclose($this->fileHandler);
    }
    
    
    // this function takes a string and save it to output file (as last line of file)
    function saveLine($logString)
    {             
        // check line limit    
        if($this->lineLimit != 0)
        {
            if($this->lineNumber >= $this->lineLimit)
            {
                $this->lineNumber = 1;
                $this->fileNumber++;
                fclose($this->fileHandler);            
                $this->fileHandler = fopen($this->filePath.$this->fileName."_".$this->fileNumber.".txt", 'a') or die("can't open file");                
            }
            else
            {
                $this->lineNumber++;
            }    
        }
        else
        {
            $this->lineNumber++;
        }
        
        
        // check size limit    
        if($this->sizeLimit != 0)
        {
            if(filesize($this->filePath.$this->fileName."_".$this->fileNumber.".txt") >= $this->sizeLimit)
            {
                $this->lineNumber = 1;
                $this->fileNumber++;
                fclose($this->fileHandler);            
                $this->fileHandler = fopen($this->filePath.$this->fileName."_".$this->fileNumber.".txt", 'a') or die("can't open file");                
            }
            else
            {
                $this->lineNumber++;
            }    
        }
        else
        {
            $this->lineNumber++;
        }
            
        fwrite($this->fileHandler, $logString."\r\n");
              
    }// end of function
        

    // this function opens a file and returns number its line
    // this function uses less memory, because does not load
    // whole of file.
    function getFileLines($filePath="")
    {    
        if($filePath=="")
            $filePath = $this->filePath;
        $linecount = 0;
        $handle = fopen($filePath, "r");
        while(!feof($handle)){
          $line = fgets($handle);
          $linecount++;
        }
        fclose($handle);
        return $linecount;
    }


    // this function opens a file and returns number its line
    // this function is good for files which each has large
    // length lines (ex: 2Gb in each line !).
    function getLargeFileLines($filePath="")
    {    
        if($filePath=="")
            $filePath = $this->filePath;
        $linecount = 0;
        $handle = fopen($filePath, "r");
        while(!feof($handle)){
          $line = fgets($handle, 4096);
          $linecount = $linecount + substr_count($line, PHP_EOL);
        }
        fclose($handle);
        return $linecount;
    }


    // this function used to get first line of a file
    // this also is suitable for large files
    function getFirstLine($filePath="")
    {
        if($filePath=="")
            $filePath = $this->filePath;
        $f = fopen($filePath, 'r');
        $line = fgets($f);
        fclose($f);
    }
     
    
}// end of class




?>