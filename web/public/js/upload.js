/**
 * 上传图片
 * @type type
 */
window.myresumeCommon = window.myresumeCommon || {
    // 请求路径
    requestTargets: {
        //上传头像
        photoUpload: '/resume/uploadPhoto.json'
    },
    /**
     * 默认配置
     */
    config: {
        // 用户上传头像的selector的大小
        userPhotoSelector: {
            width: 250,
            height: 250
        },
        cutLogoImage: {
            width: 360,
            height: 360,
            bgColor: '#ccc',
            enableRotation: false,
            enableZoom: true,
            selector: {
                w: 170,
                h: 170,
                showPositionsOnDrag: false,
                showDimetionsOnDrag: false,
                centered: true,
                bgInfoLayer: '#fff',
                borderColor: '#02d1a1',
                animated: false,
                maxWidth: 358,
                maxHeight: 358,
                borderColorHover: '#02d1a1'
            },
            image: {
                source: '',
                // 在上传完图片后，这个必须要设置，width height
                width: 0,
                height: 0,
                minZoom: 10,
                maxZoom: 300
            }
        },
        cutImage: {
            width: 360,
            height: 360,
            bgColor: '#ccc',
            enableRotation: false,
            enableZoom: true,
            selector: {
                w: 250,
                h: 250,
                showPositionsOnDrag: false,
                showDimetionsOnDrag: false,
                centered: true,
                bgInfoLayer: '#fff',
                borderColor: '#02d1a1',
                animated: false,
                maxWidth: 358,
                maxHeight: 358,
                borderColorHover: '#02d1a1'
            },
            image: {
                source: '',
                // 在上传完图片后，这个必须要设置，width height
                width: 0,
                height: 0,
                minZoom: 10,
                maxZoom: 300
            }
        }
    },
    // 一些工具方法
    utils: {
        imageUpload: function(targetInput, targetUrl, success, fail) {
            targetInput = $(targetInput);
            var inputId = targetInput.attr('id');
            var hint = targetInput.attr('title');
            // var dataType = 'json';
            var dataType = 'text';
            var params = {};
            this.AllowExt = '.jpg,.gif,.jpeg,.png,.pjpeg';
            this.FileExt = targetInput.val().substr(targetInput.val().lastIndexOf(".")).toLowerCase();
            if (this.AllowExt != 0 && this.AllowExt.indexOf(this.FileExt) == -1)//judge file format
            {
                errorTips(hint);
                $("input[type = 'file']").val("");
            } else {
                $.ajaxFileUpload({
                    url: targetUrl,
                    secureuri: false,
                    fileElementId: inputId,
                    data: params,
                    dataType: dataType,
                    // "content":{"srcImageW":650,"srcImageH":346,"uploadPath":"upload/workPic/d7f6a8cb0fd5473193a3ffd021a54838.png"}
                    success: function(rs) {
                        if (dataType == 'text')
                            rs = $.parseJSON(rs);
                        if (rs.success) {
                            success && success(rs.content, inputId);
                        }
                        else {
                            fail && fail(1);
                            errorTips(hint, "错误提示");
                        }
                    },
                    error: function(data) {
                        fail && fail(data);
                        errorTips("支持jpg、jpeg、gif、png格式，文件小于10M", "错误提示");
                    }
                });
            }
        },
        unset: function(unsets) {
            $.each(unsets, function(index, item) {
                unsets[ index ] = null;
            });
        },
        /**
         * 对目标字符串进行格式化
         * @name baidu.string.format
         * @function
         * @grammar baidu.string.format(source, opts)
         * @param {string} source 目标字符串
         * @param {Object|string...} opts 提供相应数据的对象或多个字符串
         * @remark
         *
         * @shortcut format
         * @meta standard
         *
         * opts参数为“Object”时，替换目标字符串中的#{property name}部分。<br>
         * opts为“string...”时，替换目标字符串中的#{0}、#{1}...部分。
         *
         * @returns {string} 格式化后的字符串
         */
        strFormat: function(source, opts) {
            source = String(source);
            var data = Array.prototype.slice.call(arguments, 1), toString = Object.prototype.toString;
            if (data.length) {
                data = data.length == 1 ?
                        /* ie 下 Object.prototype.toString.call(null) == '[object Object]' */
                                (opts !== null && (/\[object Array\]|\[object Object\]/.test(toString.call(opts))) ? opts : data)
                                : data;
                        return source.replace(/#\{(.+?)\}/g, function(match, key) {
                            var replacer = data[key];
                            // chrome 下 typeof /a/ == 'function'
                            if ('[object Function]' == toString.call(replacer)) {
                                replacer = replacer(key);
                            }
                            return ('undefined' == typeof replacer ? '' : replacer);
                        });
                    }
                    return source;
                },
                // 请求器
                requester: function(params, callback) {
                    params.dataType = params.dataType || 'json';
                    params.type = params.type || 'POST';
                    params.data = params.data || {};
                    // 放置token
                    // params.data.resubmitToken = $.trim( $( '#resubmitToken' ).val() );
                    params.data.resubmitToken = globals.token;
                    $.ajax(params).done(function(response) {
                        // 设置token
                        if (null != response.resubmitToken && '' != response.resubmitToken) {
                            globals.token = response.resubmitToken;
                        }
                        callback && callback(response);
                    });
                },
                /**
                 * 增加'http://'的url前缀
                 * @param {string} prefixes 需要增加的前缀
                 * @param {string} v        原值
                 * @param {function} set    回调函数
                 * @use   x.addHttpPrefix('http://|https://', 'www.lagou.com', function(newV){
                 me.setValue(newV);
                 });
                 */
                addHttpPrefix: function(prefixes, v, set) {
                    prefixes = prefixes.split('|');
                    var defaultPrefix = prefixes[0];
                    for (var i = 0, len = prefixes.length; i < len; i++) {
                        if (prefixes[i] === v.substring(0, prefixes[i].length))
                            return;
                    }
                    set(defaultPrefix + v);
                },
                /**
                 * 显示错误提示，并且自动隐藏
                 * @param  {object} target 目标元素
                 * @param  {string} hint     提示语
                 * @param  {number} delay    默认2000
                 */
                errorTips: function(target, hint, delay) {
                    // var target = $( selector );
                    delay = delay || 2000;

                    if (target.data('errortipspending') == 1)
                        return;

                    // 显示
                    target.text(hint);
                    target.show();
                    target.data('errortipspending', 1);
                    window.setTimeout(function() {
                        target.hide();
                        target.data('errortipspending', 0);
                    }, delay);

                },
                /**
                 * 创建节流函数（即，一个函数可能在短时间内执行好几遍，为了
                 * 节约性能，这个函数可以解决这个问题，例如onscroll事件的触发等等）
                 * 
                 * @param {Function} method 需要节流的函数 
                 * @param {Array} args 传入参数列表
                 * @param {Object} context 执行上线文
                 * @param {Number} delay 执行delay
                 * @return {undefined}
                 */
                throttle: function(method, args, context, delay) {
                    context = context == undefined ? null : context;
                    method.tId && clearTimeout(method.tId);
                    method.tId = setTimeout(function() {
                        method.apply(context, args);
                    }, (delay ? delay : 140));
                },
                /**
                 * 实时监听input输入框的值变化
                 * 
                 * @param {HTMLElement} input 需要监听的input元素, jQuery包装后的元素
                 * @param {Function} callback 回调函数，会把当前值传入
                 * @return {undefined}
                 */
                inputerListener: function(input, callback) {
                    var delay = 0;
                    if ("onpropertychange" in input[0]
                            && ($.browser.ie && (parseInt($.browser.version <= 8)))) { //ie7,8完美支持，ie9不支持
                        input.bind('propertychange', function(e) {
                            e.originalEvent.propertyName == 'value'
                                    && myresumeCommon.utils.throttle(callback, [input.val()], delay);
                        });
                    }
                    else if ($.browser.ie && ($.browser.version == 9)) {
                        var timer;
                        var oldV = input.val();
                        input.bind('focus', function() {
                            timer = window.setInterval(function() {
                                var newV = input.val();
                                if (newV == oldV)
                                    return;
                                // 值发生变化
                                oldV = newV;
                                // 回调函数
                                myresumeCommon.utils.throttle(callback, [oldV], delay);
                            }, 50);
                        });
                        input.bind('blur', function() {
                            window.clearInterval(timer);
                            timer = undefined;
                        });
                    }
                    else {
                        // 火狐、chrome完美支持
                        input.bind('input', function(e) {
                            myresumeCommon.utils.throttle(callback, [input.val()], delay);
                        });
                    }
                }

            }
        };
var cookieName = "paiRecTip";
//点击关闭，则设置cookie;
var btnClose = $('#pai_top_tip').find('.tip_close');
btnClose.on('click', function() {
    $('#pai_top_tip').hide();
    $.cookie(cookieName, '0', {expires: 365});
});
