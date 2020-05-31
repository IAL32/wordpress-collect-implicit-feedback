<?php

class Coimf_Logger {

// Public

    /**
     * @param integer $aLevel Describes the level of verbosity of this logger instance
     */
    public function __construct( $aLogGroup, $aTime = false ) {
        $this->mLogDirectory = wp_get_upload_dir() . '/' . $aLogGroup;
        if ( !$aTime ) {
            $this->mTime = time();
        } else {
            $this->mTime = $aTime;
        }
    }

    public function log( $aLogLevel = 2 ) {
        $vLogFilePath = $this->generateFileName();
        $vHandle = $this->openFile( $vLogFilePath, "a+" );
        $vMessages = func_get_args();
        // removing the first argument, which is just $aLogLevel
        array_shift( $vMessages );
        $vLogLine = $this->generateLogLine( $aLogLevel, $vMessages );

        $this->writeLineToFileAndClose( $vHandle, $vLogLine );
    }

// Getters and setters

    public function setLogFileFormat( $aLogFileFormat ) {
        $this->mLogFileFormat = $aLogFileFormat;
    }

    public function setLogFileExtension( $aLogFileExtension ) {
        $this->mLogFileExtension = $aLogFileExtension;
    }

    public function setLogFileTimestampFormat( $aTimestampFormat ) {
        $this->mLogFileTimestampFormat = $aTimestampFormat;
    }
    
    public function setLogLineFormat( $aLogLineFormat ) {
        $this->mLogLineFormat = $aLogLineFormat;
    }

    public function setLogLineTImestampFormat( $aLogLineTimestampFormat ) {
        $this->mLogLineTimestampFormat = $aLogLineTimestampFormat;
    }

    public function setLogLineMessagesImplodeGlue( $aLogLineMessagesImplodeGlue ) {
        $this->mLogLineMessagesImplodeGlue = $aLogLineMessagesImplodeGlue;
    }

    public function setLogLineEndLine( $aLogLineEndLine ) {
        $this->mLogLineEndLine = $aLogLineEndLine;
    }

// Private

    /**
     * This function will take an indefinite amount of arguments and will
     * glue them together to generate a log line.
     */
    private function generateLogLine( $aLogLevel = 2, $aMessages ) : string {
        $vDate = $this->generateDateString( $this->mLogLineTimestampFormat );
        $vLevel = $aLogLevel;
        $vMessages = [];
        $vMessage = "";

        if ( count( $aMessages ) == 0 ) {
            $vMessage = "";
        } else {
            $vMessage = implode( $this->mLogLineMessagesImplodeGlue, $vMessages );
        }
        return sprintf( $this->mLogLineFormat, $vDate, $vLevel, $vMessage );
    }

    /**
     * Creates a filename for the 
     */
    private function generateFileName() : string {
        $vDate = $this->generateDateString( $this->mLogFileTimestampFormat );
        return sprintf( $this->mLogFileFormat, $vDate, $this->mLogFileExtension );
    }

    private function generateDateString( $aTimestampFormat ) : string {
        return date( $aTimestampFormat, $this->mTime );
    }

    private function openFile( $aPath, $aMode = "r" ) {

        if ( !file_exists( $aPath ) ) {
            throw new Exception( "Coimf_Logger::openFile(): file does not exist" );
        }

        if ( is_dir( $aPath ) ) {
            throw new Exception( "Coimf_Logger::openFile(): file is a directory" );
        }

        $vHandle = fopen( $aPath, $aMode );

        if ( !$vHandle ) {
            throw new Exception( "Coimf_Logger::openFile(): could not open file" );
        }

        return $vHandle;
    }

    private function writeLineToFile( $aHandle, $aContent ) {
        fwrite( $aHandle, $aContent );
        fwrite( $aHandle, $this->mLogLineEndLine );
    }

    private function writeLineToFileAndClose( $aHandle, $aContent ) {
        $this->writeLineToFile( $aHandle, $aContent );
        $this->closeFile( $aHandle );
    }

    private function closeFile( $aHandle ) {
        fclose( $aHandle );
    }

    /**
     * How the filename is going to be named.
     * %1$s is the filename, no extension
     * %2$s the second is the file extension
     */
    private $mLogFileFormat = '%1$s.%2s$s';

    /** The extension of the file */
    private $mLogFileExtension = ".log";

    /**
     * Timestamp format.
     * E.g.: 17 May 2019 at 16 hours, 04 minutes and 12 seconds
     * Result: 17/05/2019,16:04:12
     */
    private $mLogFileTimestampFormat = "d-m-y";

    /** The directory where the log files will be stored */
    private $mLogDirectory;

    /**
     * How the log line will be generated.
     * %1$s is the date timestamp
     * %2$d is the log level
     * %3$s is log text
     */
    private $mLogLineFormat = '[%1$s] %2$d %3$s';

    /** The timestamp that is going to be shown on each log line */
    private $mLogLineTimestampFormat = "d/m/y,H:i:s";

    /**
     * How each parameter is going to be glued to each other when multiple
     * values are being passed to the debuggerss
    */
    private $mLogLineMessagesImplodeGlue = " ";

    /** End line character for each log line */
    private $mLogLineEndLine = "\n";

    /** The current timestamp */
    private $mTime;
}
