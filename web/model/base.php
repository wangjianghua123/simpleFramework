<?phpsession_start();!defined('IN_UC') && exit('Access Denied');class base {    var $time;    var $onlineip;    var $db;    var $view;    var $user = array();    var $settings = array();    var $cache = array();    var $app = array();    var $lang = array();    var $input = array();    var $defaulttpldir;    var $staticInfo;    var $basevar;    var $myavatar;    var $_id;    var $Sessid;    var $userinfo = array();    var $adverts_t = '';    var $adverts_j = '';    var $adverts_f = '';    var $adverts_r = '';    var $adverts_rend = '';    var $adverts_search_r = '';    var $adverts_fd = '';        function __construct() {        $this->defaulttpldir = UC_ROOT . './view/default';        $this->base();    }    /* session信息 */    function session($id) {        if (!$id)            return null;        return $_SESSION[$id];    }    /* 设置session信息 */    function setsession($id, $val) {        $_SESSION[$id] = $val;    }    /* 验证码信息 */    function sessioncode($id) {        if (!$id)            return null;        $r = $this->init_caches();        $r->select(1);        return $r->get('scode_' . $id . '_' . session_id());    }    /* 设置验证码信息 */    function setsessioncode($id, $val) {        $r = $this->init_caches();        $r->select(1);        return $r->setex('scode_' . $id . '_' . session_id(), 3600, $val);    }        /**     * 一键刷新职位信息     * @param type $id     * @return null     */    function sessionrefreshalljob($id) {        if (!$id)            return null;        $r = $this->init_caches();        $r->select(1);        return $r->get('refresh_' . $id);    }    /**     * 设置一键刷新职位信息     * @param type $id     * @param type $val     * @return type     */    function setsessionrefreshalljob($id, $val) {        $r = $this->init_caches();        $r->select(1);        return $r->setex('refresh_' . $id, 3600 * 24, $val);    }    /* 手机号验证码信息 */    function sessionphone($id) {        if (!$id)            return null;        $r = $this->init_caches();        $r->select(1);        return $r->get('sphone_' . $id . '_' . session_id());    }    /* 设置手机号验证码信息 */    function setsessionphone($id, $val) {        $r = $this->init_caches();        $r->select(1);        return $r->setex('sphone_' . $id . '_' . session_id(), 600, $val);    }    /* 删除手机号验证码信息 */    function delsessionphone($id) {        $r = $this->init_caches();        $r->select(1);        return $r->del('sphone_' . $id . '_' . session_id());    }    function init($id) {        $this->_id = $id;        //读取头部                         $this->basevar['title'] = "快就业";        $this->myavatar = $this->session('userhead');        if (file_exists(ROOT_PATH . $this->myavatar)) {            $this->basevar['avatar'] = $this->myavatar;        } else {            $this->basevar['avatar'] = '';        }    }    function getid() {        return $this->_id;    }    function base() {        //广告位获取        $adverts = S('advertcache');        $this->adverts_t = $adverts[8][0]; //首页横通广告图        $this->adverts_j = multi_array_sort($adverts[1], 'ordid'); //中间大焦点图        $this->adverts_f = multi_array_sort($adverts[2], 'ordid');//大焦点图下面4个小的        $this->adverts_r = multi_array_sort($adverts[3], 'ordid');//右边3个小的        $this->adverts_search_r = multi_array_sort($adverts[7], 'ordid');//搜索页右侧3个、咨询列表页右侧3个        $this->adverts_rend = multi_array_sort($adverts[13], 'ordid');//右边3个小的下方的一个        $this->adverts_fd = multi_array_sort($adverts[14], 'ordid');//浮动        $this->init_var();    }    function init_var() {        $this->time = time();        $cip = getenv('HTTP_CLIENT_IP');        $xip = getenv('HTTP_X_FORWARDED_FOR');        $rip = getenv('REMOTE_ADDR');        $srip = $_SERVER['REMOTE_ADDR'];        if ($cip && strcasecmp($cip, 'unknown')) {            $this->onlineip = $cip;        } elseif ($xip && strcasecmp($xip, 'unknown')) {            $this->onlineip = $xip;        } elseif ($rip && strcasecmp($rip, 'unknown')) {            $this->onlineip = $rip;        } elseif ($srip && strcasecmp($srip, 'unknown')) {            $this->onlineip = $srip;        }        preg_match("/[\d\.]{7,15}/", $this->onlineip, $match);        $this->onlineip = $match[0] ? $match[0] : 'unknown';        define('FORMHASH', $this->formhash());        $_GET['page'] = max(1, intval(getgpc('page')));        $this->lang = &$lang;    }    /* 初始化redis服务 php原生*/    function init_caches() {        static $caches;        if (!$caches) {            $caches = getRedis();        }        return $caches;    }            /* 初始化redis服务 走类库*/    function init_rediscaches() {        static $rediscaches;        if (!$rediscaches) {            require_once UC_ROOT.'command/cache/driver/RedisX.class.php';            $options['expire'] = -1;            $rediscaches = Cache::getInstance('RedisX',$options);        }        return $rediscaches;    }    function init_input($getagent = '') {        $input = getgpc('input', 'R');        if ($input) {            $input = $this->authcode($input, 'DECODE', $this->app['authkey']);            parse_str($input, $this->input);            $this->input = daddslashes($this->input, 1, TRUE);            $agent = $getagent ? $getagent : $this->input['agent'];            if (($getagent && $getagent != $this->input['agent']) || (!$getagent && md5($_SERVER['HTTP_USER_AGENT']) != $agent)) {                exit('Access denied for agent changed');            } elseif ($this->time - $this->input('time') > 3600) {                exit('Authorization has expired');            }        }        if (empty($this->input)) {            exit('Invalid input');        }    }    function init_app() {        $appid = intval(getgpc('appid'));        $appid && $this->app = $this->cache['apps'][$appid];    }    function init_user() {        if (isset($_COOKIE['uc_auth'])) {            @list($uid, $username, $agent) = explode('|', $this->authcode($_COOKIE['uc_auth'], 'DECODE', ($this->input ? $this->app['appauthkey'] : UC_KEY)));            if ($agent != md5($_SERVER['HTTP_USER_AGENT'])) {                $this->setcookie('uc_auth', '');            } else {                @$this->user['uid'] = $uid;                @$this->user['username'] = $username;            }        }    }    function init_template() {        $charset = UC_CHARSET;        require_once UC_ROOT . 'lib/template.class.php';        $this->view = new template();        $this->view->assign('dbhistories', init_db()->histories);        $this->view->assign('charset', $charset);        $this->view->assign('dbquerynum', init_db()->querynum);        $this->view->assign('user', $this->user);    }    function init_note() {        if ($this->note_exists() && !getgpc('inajax')) {            $this->load('note');            $_ENV['note']->send();        }    }    function init_mail() {        if ($this->mail_exists() && !getgpc('inajax')) {            $this->load('mail');            $_ENV['mail']->send();        }    }    /* 设置验证码 */    function authcode() {        $a = '1234567890';        for ($i = 0; $i < 5; $i++) {            $l.=substr($a, rand(0, 9), 1);        }        $this->setsessioncode('seccode', $l);        return $l;    }    /* 获取验证码 */    function getauthcode() {        return $this->sessioncode('seccode');    }    /**     *      * @global type $globe_exam     * @param type $action action     * @param type $var 数组变量     * @param type $con controllname     * @param type $p //?=dddd,可通过p来追加字符串     * @return type     */    function url($action, $var = array(), $con = false, $p = '') {        global $globe_exam;        $gets = getgpc();        $type = $globe_exam[$gets['type']][0];        $stra = '';        if (is_array($var)) {            $action || $action = 'index';            foreach ($var as $k => $v) {                if (trim($v) && trim($k))                    $stra.='/' . $k . '/' . $v;            }        }else {            $action = $action ? $action : '';            if ($var) {                $stra .= '/' . $var;            }        }        if (!$con)            $con = $this->getid();        $gang = $action ? '/' : '';        return ROOT_URL . '/' . $con . $gang . $action . $stra . $p . '/';    }    /**     *      * @param type $url 如果为数组 array(control,action,var)     */    function redirect($url) {        if (is_array($url)) {            $url = '/' . $this->url($url[1] ? $url[1] : 'index', $url[2], $url[0]);        }        header('Location: ' . $url, true, 302);        exit();    }    /**     *      * @param type $num     * @param type $action//没用了     * @param type $var//没用了     * @param type $curpage     * @param type $perpage     * @return string     */    function page($num, $curpage, $perpage = PAGESIZE, $tnum = 5, $v = array()) {        $curpage = (int) $curpage;        $curpage || $curpage = 1;        $pagernum = (int) (($num + $perpage - 1) / $perpage);        $gets = getgpc('', 'G');        $action = $gets['a'];        $var = array_diff_key($gets, array('m' => false, 'a' => false, 'page' => false, 'type' => false));        $var = $v;        $url = $this->url($action, $var, '');        if ($curpage > 1)            $previous = $url . 'page/' . ($curpage - 1);        if ($curpage < $pagernum)            $next = $url . 'page/' . ($curpage + 1);        $index = $url . 'page/1';        $end = $url . 'page/' . $pagernum;        $pagenav = $this->sapne($tnum, $curpage, $pagernum, $url);        if ($num):            $pagestr = '';            if ($previous)                $pagestr.='<a href="' . $index . '" class="oPageFirst" title="首页">&nbsp;</a><a href="' . $previous . '" class="oPagePrev" title="上一页">&nbsp;</a>';            else                $pagestr.='<a class="oPageFirst" title="首页">&nbsp;</a><a class="oPagePrev" title="上一页">&nbsp;</a>';            $pagestr.= $pagenav;            if (isset($next)):                $pagestr.='<a href="' . $next . '" class="oPageNext" title="下一页">&nbsp;</a><a href="' . $end . '" class="oPageLast">&nbsp;</a>';            else:                $pagestr.='<a  class="oPageNext" title="下一页">&nbsp;</a><a  class="oPageLast" title="尾页">&nbsp;</a>';            endif;            $pagestr.='';        endif;        return $pagestr;    }    //计算中间的数据       public function sapne($showlvtao, $page, $lastpg, $url) {        $o = $showlvtao; //中间页码表总长度，为奇数        $u = ceil($o / 2); //根据$o计算单侧页码宽度$u        $f = $page - $u; //根据当前页$currentPage和单侧宽度$u计算出第一页的起始数字        //str_replace('{p}',,$fn)//替换格式        if ($f < 0) {            $f = 0;        }//当第一页小于0时，赋值为0        $n = $lastpg; //总页数,20页        if ($n < 1) {            $n = 1;        }//当总数小于1时，赋值为1        if ($page == 1) {            $pagenav.='<span>1</span>';        } else {            $pagenav.="<a href='" . $url . "page/1'>1</a>";        }        ///////////////////////////////////////        for ($i = 1; $i <= $o; $i++) {            if ($n <= 1) {                break;            }//当总页数为1时            $c = $f + $i; //从第$c开始累加计算            if ($i == 1 && $c > 2) {                $pagenav.='&nbsp;&nbsp;…';            }            if ($c == 1) {                continue;            }            if ($c == $n) {                break;            }            if ($c == $page) {                $pagenav.='<span>' . $page . '</span>';            } else {                $pagenav.="<a href='" . $url . "page/" . $c . "'>$c</a>";            }            if ($i == $o && $c < $n - 1) {                $pagenav.='<font>&nbsp;&nbsp;…</font>';            }            if ($i > $n) {                break;            }//当总页数小于页码表长度时	        }        if ($n != 1) {            if ($page == $n && $n != 1) {                $pagenav.='<span>' . $n . '</span>';            } else {                $pagenav.="<a href='" . $url . "page/" . $n . "'>$n</a>";            }        }        return $pagenav;    }    /**     *      * @param type $num     * @param type $action//没用了     * @param type $var//没用了     * @param type $curpage     * @param type $perpage     * @return string     */    function page_seo($num, $curpage, $perpage = PAGESIZE, $tnum = 5, $s = array()) {        $curpage = (int) $curpage;        $curpage || $curpage = 1;        $str = "";        $pagernum = (int) (($num + $perpage - 1) / $perpage);        $gets = getgpc('', 'R');        $var = array_diff_key($gets, array('m' => false, 'a' => false, 'page' => false, 'type' => false));        foreach ($var as $k => $v) {            if (!in_array($k, $s) && $v) {                $str .= "&" . $k . "=" . $v;            }        }        $uri = strpos($_SERVER["REQUEST_URI"], '?');        if ($uri)            $uristr = trim(substr($_SERVER["REQUEST_URI"], 0, $uri), '/');        else            $uristr = trim($_SERVER["REQUEST_URI"], '/');        $url = trim(substr($uristr, strpos($uristr, "/"))) . '/';        if ($curpage > 1)            $previous = $url . '?page=' . ($curpage - 1) . $str;        if ($curpage < $pagernum)            $next = $url . '?page=' . ($curpage + 1) . $str;        $index = $url . '?page=1' . $str;        $end = $url . '?page=' . $pagernum . $str;        $pagenav = $this->sapne_seo($tnum, $curpage, $pagernum, $url, $str);        if ($num):            $pagestr = '';            if ($previous)                $pagestr.='<a href="' . $index . '" class="oPageFirst" title="首页">&nbsp;</a><a href="' . $previous . '" class="oPagePrev" title="上一页">&nbsp;</a>';            else                $pagestr.='<a class="oPageFirst" title="首页">&nbsp;</a><a class="oPagePrev" title="上一页">&nbsp;</a>';            $pagestr.= $pagenav;            if (isset($next)):                $pagestr.='<a href="' . $next . '" class="oPageNext" title="下一页">&nbsp;</a><a href="' . $end . '" class="oPageLast">&nbsp;</a>';            else:                $pagestr.='<a  class="oPageNext" title="下一页">&nbsp;</a><a  class="oPageLast" title="尾页">&nbsp;</a>';            endif;            $pagestr.='';        endif;        return $pagestr;    }    //计算中间的数据       public function sapne_seo($showlvtao, $page, $lastpg, $url, $str = "") {        $o = $showlvtao; //中间页码表总长度，为奇数        $u = ceil($o / 2); //根据$o计算单侧页码宽度$u        $f = $page - $u; //根据当前页$currentPage和单侧宽度$u计算出第一页的起始数字        //str_replace('{p}',,$fn)//替换格式        if ($f < 0) {            $f = 0;        }//当第一页小于0时，赋值为0        $n = $lastpg; //总页数,20页        if ($n < 1) {            $n = 1;        }//当总数小于1时，赋值为1        if ($page == 1) {            $pagenav.='<span>1</span>';        } else {            $pagenav.="<a href='" . $url . "?page=1" . $str . "'>1</a>";        }        ///////////////////////////////////////        for ($i = 1; $i <= $o; $i++) {            if ($n <= 1) {                break;            }//当总页数为1时            $c = $f + $i; //从第$c开始累加计算            if ($i == 1 && $c > 2) {                $pagenav.='&nbsp;&nbsp;…';            }            if ($c == 1) {                continue;            }            if ($c == $n) {                break;            }            if ($c == $page) {                $pagenav.='<span>' . $page . '</span>';            } else {                $pagenav.="<a href='" . $url . "?page=" . $c . $str . "'>$c</a>";            }            if ($i == $o && $c < $n - 1) {                $pagenav.='<font>&nbsp;&nbsp;…</font>';            }            if ($i > $n) {                break;            }//当总页数小于页码表长度时	        }        if ($n != 1) {            if ($page == $n && $n != 1) {                $pagenav.='<span>' . $n . '</span>';            } else {                $pagenav.="<a href='" . $url . "?page=" . $n . $str . "'>$n</a>";            }        }        return $pagenav;    }    /**     *  ajax分页实现     * @param type $ajax_func ajax分页方法     * @param type $num     * @param type $action//没用了     * @param type $var//没用了     * @param type $curpage     * @param type $perpage     * @return string     */    function pageAjax($ajax_func, $num, $curpage, $perpage = PAGESIZE, $tnum = 5, $v = array()) {        $curpage = (int) $curpage;        $curpage || $curpage = 1;        $pagernum = (int) (($num + $perpage - 1) / $perpage);        $gets = getgpc('', 'G');        $action = $gets['a'];        $var = array_diff_key($gets, array('m' => false, 'a' => false, 'page' => false, 'type' => false));        $var = $v;        $url = $this->url($action, $var, '');        if ($curpage > 1)            $previous = $curpage - 1;        if ($curpage < $pagernum)            $next = $curpage + 1;        $index = 1;        $end = $pagernum;        $pagenav = $this->sapneAjax($ajax_func, $tnum, $curpage, $pagernum);        if ($num):            $pagestr = '';            if ($previous)                $pagestr.='<a href="javascript:' . $ajax_func . '(' . $index . ')" class="oPageFirst" title="首页">&nbsp;</a><a href="javascript:' . $ajax_func . '(' . $previous . ')" class="oPagePrev" title="上一页">&nbsp;</a>';            else                $pagestr.='<a class="oPageFirst" title="首页">&nbsp;</a><a class="oPagePrev" title="上一页">&nbsp;</a>';            $pagestr.= $pagenav;            if (isset($next)):                $pagestr.='<a href="javascript:' . $ajax_func . '(' . $next . ')" class="oPageNext" title="下一页">&nbsp;</a><a href="javascript:' . $ajax_func . '(' . $end . ')" class="oPageLast" title="尾页">&nbsp;</a>';            else:                $pagestr.='<a  class="oPageNext" title="下一页">&nbsp;</a><a  class="oPageLast" title="尾页">&nbsp;</a>';            endif;            $pagestr.='';        endif;        return $pagestr;    }    //计算中间的数据   ajax专用    public function sapneAjax($ajax_func, $showlvtao, $page, $lastpg) {        $o = $showlvtao; //中间页码表总长度，为奇数        $u = ceil($o / 2); //根据$o计算单侧页码宽度$u        $f = $page - $u; //根据当前页$currentPage和单侧宽度$u计算出第一页的起始数字        //str_replace('{p}',,$fn)//替换格式        if ($f < 0) {            $f = 0;        }//当第一页小于0时，赋值为0        $n = $lastpg; //总页数,20页        if ($n < 1) {            $n = 1;        }//当总数小于1时，赋值为1        if ($page == 1) {            $pagenav.='<span>1</span>';        } else {            $pagenav.='<a href="javascript:' . $ajax_func . '(1)">1</a>';        }        ///////////////////////////////////////        for ($i = 1; $i <= $o; $i++) {            if ($n <= 1) {                break;            }//当总页数为1时            $c = $f + $i; //从第$c开始累加计算            if ($i == 1 && $c > 2) {                $pagenav.='&nbsp;&nbsp;…';            }            if ($c == 1) {                continue;            }            if ($c == $n) {                break;            }            if ($c == $page) {                $pagenav.='<span>' . $page . '</span>';            } else {                $pagenav.='<a href="javascript:' . $ajax_func . '(' . $c . ')">' . $c . '</a>';            }            if ($i == $o && $c < $n - 1) {                $pagenav.='<font>&nbsp;&nbsp;…</font>';            }            if ($i > $n) {                break;            }//当总页数小于页码表长度时	        }        if ($n != 1) {            if ($page == $n && $n != 1) {                $pagenav.='<span>' . $n . '</span>';            } else {                $pagenav.='<a href="javascript:' . $ajax_func . '(' . $n . ')">' . $n . '</a>';            }        }        return $pagenav;    }    function page_get_start($page, $ppp, $totalnum) {        $totalpage = ceil($totalnum / $ppp);        $page = max(1, min($totalpage, intval($page)));        return ($page - 1) * $ppp;    }    function load($model, $base = NULL, $release = '') {        $base = $base ? $base : $this;        static $_ENV;        if (empty($_ENV[$model])) {            $release = !$release ? RELEASE_ROOT : $release;            if (file_exists(UC_ROOT . $release . "model/$model.php")) {                require_once UC_ROOT . $release . "model/$model.php";            } else {                require_once UC_ROOT . "model/$model.php";            }            $c = $model . 'model';            $_ENV[$model] = new $c($base);        }        return $_ENV[$model];    }    function get_setting($k = array(), $decode = FALSE) {        return 0;    }    function set_setting($k, $v, $encode = FALSE) {        return 0;    }    function message($message, $redirect = '', $type = 0, $vars = array()) {        include_once UC_ROOT . 'view/default/messages.lang.php';        if (isset($lang[$message])) {            $message = $lang[$message] ? str_replace(array_keys($vars), array_values($vars), $lang[$message]) : $message;        }        $this->view->assign('message', $message);        $this->view->assign('redirect', $redirect);        if ($type == 0) {            $this->view->display('message');        } elseif ($type == 1) {            $this->view->display('message_client');        }        exit;    }    function formhash() {        return substr(md5(substr($this->time, 0, -4) . UC_KEY), 16);    }    function submitcheck() {        return @getgpc('formhash', 'P') == FORMHASH ? true : false;    }    function date($time, $type = 3) {        $format[] = $type & 2 ? (!empty($this->settings['dateformat']) ? $this->settings['dateformat'] : 'Y-n-j') : '';        $format[] = $type & 1 ? (!empty($this->settings['timeformat']) ? $this->settings['timeformat'] : 'H:i') : '';        return gmdate(implode(' ', $format), $time + $this->settings['timeoffset']);    }    function implode($arr) {        return "'" . implode("','", (array) $arr) . "'";    }    function set_home($uid, $dir = '.') {        $uid = sprintf("%09d", $uid);        $dir1 = substr($uid, 0, 3);        $dir2 = substr($uid, 3, 2);        $dir3 = substr($uid, 5, 2);        !is_dir($dir . '/' . $dir1) && mkdir($dir . '/' . $dir1, 0777);        !is_dir($dir . '/' . $dir1 . '/' . $dir2) && mkdir($dir . '/' . $dir1 . '/' . $dir2, 0777);        !is_dir($dir . '/' . $dir1 . '/' . $dir2 . '/' . $dir3) && mkdir($dir . '/' . $dir1 . '/' . $dir2 . '/' . $dir3, 0777);    }    function get_home($uid) {        $uid = sprintf("%09d", $uid);        $dir1 = substr($uid, 0, 3);        $dir2 = substr($uid, 3, 2);        $dir3 = substr($uid, 5, 2);        return $dir1 . '/' . $dir2 . '/' . $dir3;    }    function get_avatar($uid, $size = 'big', $type = '') {        $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';        $uid = abs(intval($uid));        $uid = sprintf("%09d", $uid);        $dir1 = substr($uid, 0, 3);        $dir2 = substr($uid, 3, 2);        $dir3 = substr($uid, 5, 2);        $typeadd = $type == 'real' ? '_real' : '';        return $dir1 . '/' . $dir2 . '/' . $dir3 . '/' . substr($uid, -2) . $typeadd . "_avatar_$size.jpg";    }    function &cache($cachefile) {        static $_CACHE = array();        if (!isset($_CACHE[$cachefile])) {            $cachepath = UC_DATADIR . './cache/' . $cachefile . '.php';            if (!file_exists($cachepath)) {                $this->load('cache');                $_ENV['cache']->updatedata($cachefile);            } else {                include_once $cachepath;            }        }        return $_CACHE[$cachefile];    }    function input($k) {        return isset($this->input[$k]) ? (is_array($this->input[$k]) ? $this->input[$k] : trim($this->input[$k])) : NULL;    }    function serialize($s, $htmlon = 0) {        if (file_exists(UC_ROOT . RELEASE_ROOT . './lib/xml.class.php')) {            include_once UC_ROOT . RELEASE_ROOT . './lib/xml.class.php';        } else {            include_once UC_ROOT . './lib/xml.class.php';        }        return xml_serialize($s, $htmlon);    }    function unserialize($s) {        if (file_exists(UC_ROOT . RELEASE_ROOT . './lib/xml.class.php')) {            include_once UC_ROOT . RELEASE_ROOT . './lib/xml.class.php';        } else {            include_once UC_ROOT . './lib/xml.class.php';        }        return xml_unserialize($s);    }    function cutstr($string, $length, $dot = ' ...') {        if (strlen($string) <= $length) {            return $string;        }        $string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);        $strcut = '';        if (strtolower(UC_CHARSET) == 'utf-8') {            $n = $tn = $noc = 0;            while ($n < strlen($string)) {                $t = ord($string[$n]);                if ($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {                    $tn = 1;                    $n++;                    $noc++;                } elseif (194 <= $t && $t <= 223) {                    $tn = 2;                    $n += 2;                    $noc += 2;                } elseif (224 <= $t && $t < 239) {                    $tn = 3;                    $n += 3;                    $noc += 2;                } elseif (240 <= $t && $t <= 247) {                    $tn = 4;                    $n += 4;                    $noc += 2;                } elseif (248 <= $t && $t <= 251) {                    $tn = 5;                    $n += 5;                    $noc += 2;                } elseif ($t == 252 || $t == 253) {                    $tn = 6;                    $n += 6;                    $noc += 2;                } else {                    $n++;                }                if ($noc >= $length) {                    break;                }            }            if ($noc > $length) {                $n -= $tn;            }            $strcut = substr($string, 0, $n);        } else {            for ($i = 0; $i < $length; $i++) {                $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];            }        }        $strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);        return $strcut . $dot;    }    function setcookie($key, $value, $life = 0, $httponly = false) {        (!defined('UC_COOKIEPATH')) && define('UC_COOKIEPATH', '/');        (!defined('UC_COOKIEDOMAIN')) && define('UC_COOKIEDOMAIN', '');        if ($value == '' || $life < 0) {            $value = '';            $life = -1;        }        $life = $life > 0 ? $this->time + $life : ($life < 0 ? $this->time - 31536000 : 0);        $path = $httponly && PHP_VERSION < '5.2.0' ? UC_COOKIEPATH . "; HttpOnly" : UC_COOKIEPATH;        $secure = $_SERVER['SERVER_PORT'] == 443 ? 1 : 0;        if (PHP_VERSION < '5.2.0') {            setcookie($key, $value, $life, $path, UC_COOKIEDOMAIN, $secure);        } else {            setcookie($key, $value, $life, $path, UC_COOKIEDOMAIN, $secure, $httponly);        }    }    function note_exists() {        return 0;    }    function mail_exists() {        return 0;    }    function dstripslashes($string) {        if (is_array($string)) {            foreach ($string as $key => $val) {                $string[$key] = $this->dstripslashes($val);            }        } else {            $string = stripslashes($string);        }        return $string;    }    function render($fname, $var) {        extract($var, EXTR_SKIP);        include $this->defaulttpldir . '/' . $this->_id . '/' . $fname . '.php';    }    function render1($fname, $var) {        extract($var, EXTR_SKIP);        include UC_ROOT . $fname . '.html';    }    function render2($fname, $var) {        extract($var, EXTR_SKIP);        include $this->defaulttpldir . '/' . $fname . '.php';    }    function renderPartial($fname, $var) {        // 页面缓存        ob_start();        ob_implicit_flush(0);        extract($var, EXTR_SKIP);        include $this->defaulttpldir . '/' . $this->_id . '/' . $fname . '.php';        $content = ob_get_clean();        return $content;    }    //获得大写数字    function getNumeral($index) {        $numeral = array('', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十');        $digit = array('', '十', '百', '千', '万');        $len = strlen($index);        for ($i = 0; $i < $len; $i++) {            $num = substr($index, $i, 1);            $string .= $numeral[$num] . $digit[$len - $i - 1];        }        return $string;    }    function setmeta($title, $keywords, $description) {        global $globe_exam;        $this->basevar['title'] = $title;        $this->basevar['keywords'] = $keywords;        $this->basevar['description'] = $description;    }    function getcontact($name) {        $contact = array(            'tel' => '400-091-0808',            'time' => '9:00 - 21:00',            'time2' => '服务时间：任何时候都欢迎<br>　　　　　您提出问题',            'email' => 'kefu@kjiuye.com',            'zxqq' => 'http://wpa.qq.com/msgrd?v=3&uin=3208534819&site=qq&menu=yes',            'zxname' => '答疑解惑',            'zglogo' => PUBLIC_URL . 'public/images/zg_logo.jpg'        );        if (!empty($name)) {            return $contact[$name];        } else {            return '';        }    }    /**     * 验证手机号码     * @param type $phone     * @return boolean     */    function checkphone($phone) {        if (preg_match("/^1[0-9][0-9]{9}$/", $phone)) {            return true;        } else {            return false;        }    }    /**     * 验证邮箱地址     * @param type $email     * @return boolean     */    function checkemail($email) {        if (preg_match("/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z\\-_\\.]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i", $email)) {            return true;        }else {            return false;        }    }    /**     * 将邮件地址多余部分替换为*     * @param type $email     * @return type     */    function replaceemailtostar($email) {        $strlen = strpos($email, '@');        $newemail = substr_replace($email, "*****", 3, $strlen - 3); //只显示前三位，@符号之前的都替换成*        return $newemail;    }    /**     * 发送邮件     * @param type $usertype 用户类型 1普通用户2企业用户     * @param type $istype 操作类型 1验证邮箱2修改邮箱     * @param type $email 邮箱地址     */    function sendemail($usertype, $istype, $email,$path,$subject = '验证邮箱 【快就业】') {        $message = $worktitle = $workbest = '';        $worktitle = '<div style="margin:0px 0;">';        if ($usertype == 1) {            //普通用户            $workbest = "祝您求职征途梦想成真！";            $worktitle .= '请点击以下链接验证您的邮箱地址，验证后';            $worktitle .= ($istype == 0) ? "就可以进行密码重置" : "就可以使用快就业的所有功能啦!";        } elseif ($usertype == 2) {            //企业用户            $workbest = "祝您工作顺利！";            $worktitle .= "请点击以下链接验证您的邮箱地址，验证后";            switch ($istype){                case 0:                    $worktitle .= "就可以进行密码重置";                    break;                case 4:                    $worktitle .= "就可以修改简历接收邮箱";                    break;                default :                    $worktitle .= "就可以免费发布职位啦！";            }        }        $worktitle .= '</div>';        $message = $worktitle . '<div style="word-break:break-all;word-wrap:break-word;"><a href="' . $path . '" target="_blank" style="color:#4c6c98;text-decoration:underline;">' . $path . '</a><br /></div>';        $message .= '<div style="margin:0px 0; color:#666;">（该链接在24小时内有效，24小时后需要重新获取）</div>';        $message .= '<div style="margin-top:20px;">如果以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。</div><br /><div style="margin:0px 0;">' . $workbest . '<br />快就业，就好业</div>';        $message .= '<div style="margin-top:20px; float:right">快就业团队<br/>' . date("Y-m-d") . '</div></td></tr></tbody></table>';        $issend = SetEmails($email, $message, $subject);        return $issend;    }        function sendemail_ujiuye($usertype, $istype, $email,$path,$subject = '验证邮箱 【优就业—优聘】') {        $message = $worktitle = $workbest = '';        $worktitle = '<div style="margin:0px 0;">';        if ($usertype == 1) {            //普通用户            $workbest = "祝您求职征途梦想成真！";            $worktitle .= '请点击以下链接验证您的邮箱地址，验证后';            $worktitle .= ($istype == 0) ? "就可以进行密码重置" : "就可以使用优聘的所有功能啦!";        } elseif ($usertype == 2) {            //企业用户            $workbest = "祝您工作顺利！";            $worktitle .= "请点击以下链接验证您的邮箱地址，验证后";            switch ($istype){                case 0:                    $worktitle .= "就可以进行密码重置";                    break;                case 4:                    $worktitle .= "就可以修改简历接收邮箱";                    break;                default :                    $worktitle .= "就可以免费发布职位啦！";            }        }        $worktitle .= '</div>';        $message = $worktitle . '<div style="word-break:break-all;word-wrap:break-word;"><a href="' . $path . '" target="_blank" style="color:#4c6c98;text-decoration:underline;">' . $path . '</a><br /></div>';        $message .= '<div style="margin:0px 0; color:#666;">（该链接在24小时内有效，24小时后需要重新获取）</div>';        $message .= '<div style="margin-top:20px;">如果以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。</div><br /><div style="margin:0px 0;">' . $workbest . '<br />拿高薪，优就业</div>';        $message .= '<div style="margin-top:20px; float:right">优聘团队<br/>' . date("Y-m-d") . '</div></td></tr></tbody></table>';        $issend = SetEmails($email, $message, $subject,'优聘');        return $issend;    }        function getcertstatus(){        //获取该企业HR的营业执照审核情况如果通过才能进行下一步操作        if($this->_uc_usertype == 2 && $this->_company_id == 0){            IS_AJAX && ajaxReturns(0,'请绑定公司后进行操作',0);            ShowMsg("请绑定公司后进行操作",$this->url('writeusertocompany','','userforcompany'));            die;        }else if($this->_uc_usertype == 1){            IS_AJAX && ajaxReturns(0,'对不起，您没有操作权限',0);            ShowMsg("对不起，您没有操作权限",$this->url('index','','resume'));            die;        }        $companymodel = $this->load("company");        $where = array('cid'=>$this->session('uc_company_id'));        $isrz = $companymodel->getcertbyarr($where,"verify");        if(empty($isrz)){            ShowMsg("对不起，您没有提交营业执照",$this->url('index',null,'certificate'));            die;        }else if($isrz['verify'] != 2){            ShowMsg("对不起，您公司的营业执照还未审核或者审核未通过",$this->url('index',array('showapply' => 0),'certificate'));            die;        }    }}?>