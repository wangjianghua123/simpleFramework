<?php
class XHProf
{

    public function __construct()
    {
        // start profiling
        $XHPROF_ROOT = "./xhprof";
        include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
        include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
    }

    public function beginProf()
    {
        xhprof_enable();
		echo 1111;die;
        register_shutdown_function('XHProf::endProf');
    }

    public function endProf()
    {
        $xhprof_data = xhprof_disable();
		dump($xhprof_data);
        $xhprof_runs = new XHProfRuns_Default();
        $run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_foo");
		echo $run_id;
        echo "<a href=\"http://http://www.gittest.com/xhprof/xhprof_html/index.php?run=$run_id&source=xhprof_foo\">性能分析结果</a>";
    }

}
