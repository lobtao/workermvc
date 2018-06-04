<?php
/**
 * Created by lobtao.
 */

namespace workermvc\server;

use Workerman\Lib\Timer;
use workermvc\Config;

class FileMonitor extends BaseServer {
    protected $worker;
    protected $socket = '';
    protected $protocol = '';
    protected $hostname = '';
    protected $hostport = '';
    protected $count = 1;
    protected $name = "FileMonitor";

    private $last_mtime;

    /**
     * @param \Workerman\Worker $worker
     */
    public function onWorkerStart($worker) {
        $this->last_mtime = time();
        $worker->reloadable = true;

        $monitor_dir = realpath(APP_PATH . '..');
        // watch files only in daemon mode
        $dirs = Config::get('worker.debug_dirs');
        foreach ($dirs as $dir)
            // chek mtime of files per second
            Timer::add(1, [$this, 'check_files_change'], array($dir));
    }

    // check files func
    function check_files_change($monitor_dir) {
        // recursive traversal directory
        $dir_iterator = new \RecursiveDirectoryIterator($monitor_dir);
        $iterator = new \RecursiveIteratorIterator($dir_iterator);
        foreach ($iterator as $file) {
            // only check php files
            if (pathinfo($file, PATHINFO_EXTENSION) != 'php') {
                continue;
            }
            // check mtime
            if ($this->last_mtime < $file->getMTime()) {
                echo $file . " update and reload\n";
                // send SIGUSR1 signal to master process for reload
                posix_kill(posix_getppid(), SIGUSR1);
                $this->last_mtime = $file->getMTime();
                break;
            }
        }
    }
}
