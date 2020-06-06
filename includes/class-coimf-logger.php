<?php

namespace Coimf {

class LogLevel extends Enum {
    public const TRACE  = 0;
    public const DEBUG  = 1;
    public const INFO   = 2;
    public const WARN   = 3;
    public const ERROR   = 3;
}

class Logger {

// Public

    /**
     * @param integer $aLevel Describes the level of verbosity of this logger instance
     */
    public function __construct( string $aLogGroup ) {
        $this->mLogGroup = $aLogGroup;
    }

    public static function sLog( string $aLogGroup, int $aLogLevel = 2  ) {
        $vLogger = new self( $aLogGroup );
        $vMessages = func_get_args();
        // removing the first two arguments
        array_shift( $vMessages );
        array_shift( $vMessages );

        $vLogger->log( $aLogLevel, ...$vMessages );
    }

    public function log( $aLogLevel = 2 ) {
        $vLogDirectory = wp_upload_dir()["basedir"] . "/" . COIMF_NAME . "/" . $this->mLogGroup;

        if ( ! file_exists( $vLogDirectory ) ) {
            wp_mkdir_p( $vLogDirectory );
        }

        $vLogFilePath = $vLogDirectory . "/" . $this->generateFileName();
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
    private function generateLogLine( $aLogLevel = 2, array $aMessages = [] ) : string {
        $vDate = $this->generateDateString( $this->mLogLineTimestampFormat );
        $vLevel = $aLogLevel;
        $vMessage = "";

        if ( count( $aMessages ) == 0 ) {
            $vMessage = "";
        } else {
            $vMessage = implode( $this->mLogLineMessagesImplodeGlue, $aMessages );
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
        return date( $aTimestampFormat, time() );
    }

    private function openFile( $aPath, $aMode = "r" ) {

        if ( is_dir( $aPath ) ) {
            throw new \Exception( "\Coimf\Logger::openFile(): file is a directory" );
        }

        $vHandle = fopen( $aPath, $aMode );

        if ( !$vHandle ) {
            throw new \Exception( "\Coimf\Logger::openFile(): could not open file" );
        }

        if ( ! file_exists( $aPath ) ) {
            fwrite( $vHandle, "" );
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
    private string $mLogFileFormat = "%1\$s%2\$s";

    /** The extension of the file */
    private string $mLogFileExtension = ".log";

    /**
     * Timestamp format.
     * E.g.: 17 May 2019 at 16 hours, 04 minutes and 12 seconds
     * Result: 17/05/2019,16:04:12
     */
    private string $mLogFileTimestampFormat = "d-m-y";

    /**
     * How the log line will be generated.
     * %1$s is the date timestamp
     * %2$d is the log level
     * %3$s is log text
     */
    private string $mLogLineFormat = "[%1\$s] %2\$d %3\$s";

    /** The timestamp that is going to be shown on each log line */
    private string $mLogLineTimestampFormat = "d/m/y,H:i:s";

    private string $mLogGroup;

    /**
     * How each parameter is going to be glued to each other when multiple
     * values are being passed to the debuggerss
    */
    private string $mLogLineMessagesImplodeGlue = " ";

    /** End line character for each log line */
    private string $mLogLineEndLine = "\n";

}

}
