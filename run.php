<?php

require "vendor/autoload.php";

use Symfony\Component\Process\Process;

class Logger{
	protected $last_status = 1;
	protected $last_success = 0;
	protected $is_verbose = false;

	public function __construct($verbose = false){
		$this->is_verbose = $verbose;
	}

	public function parse($msg){

		$now = date('Y-m-d H:i:s');
		$now_time = time();
		
		if($this->last_success == 0){
			$this->last_success = $now_time;
		}
		if(strpos($msg, "timeout")){
			if($this->is_verbose || $this->last_status == 1){
				echo "[$now] TIMEOUT " . ($now_time - $this->last_success) . "\n";
			}

			$this->last_status = 0;
		}elseif (strpos($msg, "time=")) {
			if($this->is_verbose && $this->last_status == 1){
				echo "[$now] OK \n";
			}elseif($this->last_status == 0){
				echo "[$now] TIMEOUT " . ($now_time - $this->last_success) . " => OK \n";
			}

			$this->last_success = $now_time;
			$this->last_status = 1;
		}
	}

}

$logger = new Logger(0);
$process = new Process(['ping','google.com']);
$process->setTimeout(0);
// $process->setTty(true);
// $process->run(function($line){
// 	var_dump($line);
// });

$process->setPty(true);
$process->start();
$process->wait(function($type, $buffer) use ($process, $logger) {
    if ($type == \Symfony\Component\Process\Process::ERR) {
        fwrite(STDERR, $buffer);
    }
    if ($type == \Symfony\Component\Process\Process::OUT) {
        $logger->parse($buffer);
    }
});
