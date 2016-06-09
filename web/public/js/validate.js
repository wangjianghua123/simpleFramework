!
function(e) {
    e.extend(e.fn, {
        validate: function(t) {
            if (this.length) {
                var n = e.data(this[0], "validator");
                if (n) return n;
                if (n = new e.validator(t, this[0]), e.data(this[0], "validator", n), n.settings.onsubmit) this.validateDelegate(":submit", "click", 
                function(t) {
                    if (n.settings.submitHandler) n.submitButton = t.target;
                    if (e(t.target).hasClass("cancel")) n.cancelSubmit = !0
                }),
                this.submit(function(t) {
                    function i() {
                        var i;
                        if (n.settings.submitHandler) {
                            if (n.submitButton) i = e("<input type='hidden'/>").attr("name", n.submitButton.name).val(n.submitButton.value).appendTo(n.currentForm);
                            if (n.settings.submitHandler.call(n, n.currentForm, t), n.submitButton) i.remove();
                            return ! 1
                        }
                        return ! 0
                    }
                    if (void 0 != window.tinyMCE) tinyMCE.triggerSave();
                    if (n.settings.debug) t.preventDefault();
                    if (n.cancelSubmit) return n.cancelSubmit = !1,
                    i();
                    if (n.form()) if (n.pendingRequest) return n.formSubmitted = !0,
                    !1;
                    else return i();
                    else return n.focusInvalid(),
                    !1
                });
                return n
            } else if (t && t.debug && window.console) console.warn("nothing selected, can't validate, returning nothing")
        },
        valid: function() {
            if (e(this[0]).is("form")) return this.validate().form();
            else {
                var t = !0,
                n = e(this[0].form).validate();
                return this.each(function() {
                    t &= n.element(this)
                }),
                t
            }
        },
        removeAttrs: function(t) {
            var n = {},
            i = this;
            return e.each(t.split(/\s/), 
            function(e, t) {
                n[t] = i.attr(t),
                i.removeAttr(t)
            }),
            n
        },
        rules: function(t, n) {
            var i = this[0];
            if (t) {
                var r = e.data(i.form, "validator").settings,
                o = r.rules,
                a = e.validator.staticRules(i);
                switch (t) {
                case "add":
                    if (e.extend(a, e.validator.normalizeRule(n)), o[i.name] = a, n.messages) r.messages[i.name] = e.extend(r.messages[i.name], n.messages);
                    break;
                case "remove":
                    if (!n) return delete o[i.name],
                    a;
                    var s = {};
                    return e.each(n.split(/\s/), 
                    function(e, t) {
                        s[t] = a[t],
                        delete a[t]
                    }),
                    s
                }
            }
            var l = e.validator.normalizeRules(e.extend({},
            e.validator.metadataRules(i), e.validator.classRules(i), e.validator.attributeRules(i), e.validator.staticRules(i)), i);
            if (l.required) {
                var u = l.required;
                delete l.required,
                l = e.extend({
                    required: u
                },
                l)
            }
            return l
        }
    }),
    e.extend(e.expr[":"], {
        blank: function(t) {
            return ! e.trim("" + t.value)
        },
        filled: function(t) {
            return !! e.trim("" + t.value)
        },
        unchecked: function(e) {
            return ! e.checked
        }
    }),
    e.validator = function(t, n) {
        this.settings = e.extend(!0, {},
        e.validator.defaults, t),
        this.currentForm = n,
        this.init()
    },
    e.validator.format = function(t, n) {
        if (1 === arguments.length) return function() {
            var n = e.makeArray(arguments);
            return n.unshift(t),
            e.validator.format.apply(this, n)
        };
        if (arguments.length > 2 && n.constructor !== Array) n = e.makeArray(arguments).slice(1);
        if (n.constructor !== Array) n = [n];
        return e.each(n, 
        function(e, n) {
            t = t.replace(new RegExp("\\{" + e + "\\}", "g"), n)
        }),
        t
    },
    e.extend(e.validator, {
        defaults: {
            messages: {},
            groups: {},
            rules: {},
            errorClass: "error",
            validClass: "valid",
            errorElement: "span",
            focusInvalid: !0,
            errorContainer: e([]),
            errorLabelContainer: e([]),
            onsubmit: !0,
            ignore: "",
            ignoreTitle: !1,
            onfocusin: function(e) {
                if (this.lastActive = e, this.settings.focusCleanup && !this.blockFocusCleanup) {
                    if (this.settings.unhighlight) this.settings.unhighlight.call(this, e, this.settings.errorClass, this.settings.validClass);
                    this.addWrapper(this.errorsFor(e)).hide()
                }
            },
            onfocusout: function(e) {
                if (!this.checkable(e) && (e.name in this.submitted || !this.optional(e))) this.element(e)
            },
            onkeyup: function(e, t) {
                if (9 != t.which || "" !== this.elementValue(e)) if (e.name in this.submitted || e === this.lastActive) this.element(e)
            },
            onclick: function(e) {
                if (e.name in this.submitted) this.element(e);
                else if (e.parentNode.name in this.submitted) this.element(e.parentNode)
            },
            highlight: function(t, n, i) {
                if ("radio" === t.type) this.findByName(t.name).addClass(n).removeClass(i);
                else e(t).addClass(n).removeClass(i)
            },
            unhighlight: function(t, n, i) {
                if ("radio" === t.type) this.findByName(t.name).removeClass(n).addClass(i);
                else e(t).removeClass(n).addClass(i)
            }
        },
        setDefaults: function(t) {
            e.extend(e.validator.defaults, t)
        },
        messages: {
            required: "This field is required.",
            remote: "Please fix this field.",
            email: "Please enter a valid email address.",
            url: "Please enter a valid URL.",
            date: "Please enter a valid date.",
            dateISO: "Please enter a valid date (ISO).",
            number: "Please enter a valid number.",
            digits: "Please enter only digits.",
            creditcard: "Please enter a valid credit card number.",
            equalTo: "Please enter the same value again.",
            maxlength: e.validator.format("Please enter no more than {0} characters."),
            minlength: e.validator.format("Please enter at least {0} characters."),
            rangelength: e.validator.format("Please enter a value between {0} and {1} characters long."),
            range: e.validator.format("Please enter a value between {0} and {1}."),
            max: e.validator.format("Please enter a value less than or equal to {0}."),
            min: e.validator.format("Please enter a value greater than or equal to {0}.")
        },
        autoCreateRanges: !1,
        prototype: {
            init: function() {
                function t(t) {
                    var n = e.data(this[0].form, "validator"),
                    i = "on" + t.type.replace(/^validate/, "");
                    if (n.settings[i]) n.settings[i].call(n, this[0], t)
                }
                this.labelContainer = e(this.settings.errorLabelContainer),
                this.errorContext = this.labelContainer.length && this.labelContainer || e(this.currentForm),
                this.containers = e(this.settings.errorContainer).add(this.settings.errorLabelContainer),
                this.submitted = {},
                this.valueCache = {},
                this.pendingRequest = 0,
                this.pending = {},
                this.invalid = {},
                this.reset();
                var n = this.groups = {};
                e.each(this.settings.groups, 
                function(t, i) {
                    e.each(i.split(/\s/), 
                    function(e, i) {
                        n[i] = t
                    })
                });
                var i = this.settings.rules;
                if (e.each(i, 
                function(t, n) {
                    i[t] = e.validator.normalizeRule(n)
                }), e(this.currentForm).validateDelegate(":text, [type='password'], [type='file'], select, textarea, [type='number'], [type='search'] ,[type='tel'], [type='url'], [type='email'], [type='datetime'], [type='date'], [type='month'], [type='week'], [type='time'], [type='datetime-local'], [type='range'], [type='color'] ", "focusin focusout keyup", t).validateDelegate("[type='radio'], [type='checkbox'], select, option", "click", t), this.settings.invalidHandler) e(this.currentForm).bind("invalid-form.validate", this.settings.invalidHandler)
            },
            form: function() {
                if (this.checkForm(), e.extend(this.submitted, this.errorMap), this.invalid = e.extend({},
                this.errorMap), !this.valid()) e(this.currentForm).triggerHandler("invalid-form", [this]);
                return this.showErrors(),
                this.valid()
            },
            checkForm: function() {
                this.prepareForm();
                for (var e = 0, t = this.currentElements = this.elements(); t[e]; e++) if (void 0 != this.findByName(t[e].name).length && this.findByName(t[e].name).length > 1) for (var n = 0; n < this.findByName(t[e].name).length; n++) this.check(this.findByName(t[e].name)[n]);
                else this.check(t[e]);
                return this.valid()
            },
            element: function(t) {
                t = this.validationTargetFor(this.clean(t)),
                this.lastElement = t,
                this.prepareElement(t),
                this.currentElements = e(t);
                var n = this.check(t) !== !1;
                if (n) delete this.invalid[t.name];
                else this.invalid[t.name] = !0;
                if (!this.numberOfInvalids()) this.toHide = this.toHide.add(this.containers);
                return this.showErrors(),
                n
            },
            showErrors: function(t) {
                if (t) {
                    e.extend(this.errorMap, t),
                    this.errorList = [];
                    for (var n in t) this.errorList.push({
                        message: t[n],
                        element: this.findByName(n)[0]
                    });
                    this.successList = e.grep(this.successList, 
                    function(e) {
                        return ! (e.name in t)
                    })
                }
                if (this.settings.showErrors) this.settings.showErrors.call(this, this.errorMap, this.errorList);
                else this.defaultShowErrors()
            },
            resetForm: function() {
                if (e.fn.resetForm) e(this.currentForm).resetForm();
                this.submitted = {},
                this.lastElement = null,
                this.prepareForm(),
                this.hideErrors(),
                this.elements().removeClass(this.settings.errorClass).removeData("previousValue")
            },
            numberOfInvalids: function() {
                return this.objectLength(this.invalid)
            },
            objectLength: function(e) {
                var t = 0;
                for (var n in e) t++;
                return t
            },
            hideErrors: function() {
                this.addWrapper(this.toHide).hide()
            },
            valid: function() {
                return 0 === this.size()
            },
            size: function() {
                return this.errorList.length
            },
            focusInvalid: function() {
                if (this.settings.focusInvalid) try {
                    e(this.findLastActive() || this.errorList.length && this.errorList[0].element || []).filter(":visible").focus().trigger("focusin")
                } catch(t) {}
            },
            findLastActive: function() {
                var t = this.lastActive;
                return t && 1 === e.grep(this.errorList, 
                function(e) {
                    return e.element.name === t.name
                }).length && t
            },
            elements: function() {
                var t = this,
                n = {};
                return e(this.currentForm).find("input, select, textarea").not(":submit, :reset, :image, [disabled]").not(this.settings.ignore).filter(function() {
                    if (!this.name && t.settings.debug && window.console) console.error("%o has no name assigned", this);
                    if (this.name in n || !t.objectLength(e(this).rules())) return ! 1;
                    else return n[this.name] = !0,
                    !0
                })
            },
            clean: function(t) {
                return e(t)[0]
            },
            errors: function() {
                var t = this.settings.errorClass.replace(" ", ".");
                return e(this.settings.errorElement + "." + t, this.errorContext)
            },
            reset: function() {
                this.successList = [],
                this.errorList = [],
                this.errorMap = {},
                this.toShow = e([]),
                this.toHide = e([]),
                this.currentElements = e([])
            },
            prepareForm: function() {
                this.reset(),
                this.toHide = this.errors().add(this.containers)
            },
            prepareElement: function(e) {
                this.reset(),
                this.toHide = this.errorsFor(e)
            },
            elementValue: function(t) {
                var n = e(t).attr("type"),
                i = e(t).val();
                if ("radio" === n || "checkbox" === n) return e('input[name="' + e(t).attr("name") + '"]:checked').val();
                if ("string" == typeof i) return i.replace(/\r/g, "");
                else return i
            },
            check: function(t) {
                t = this.validationTargetFor(this.clean(t));
                var n,
                i = e(t).rules(),
                r = !1,
                o = this.elementValue(t);
                for (var a in i) {
                    var s = {
                        method: a,
                        parameters: i[a]
                    };
                    try {
                        if (n = e.validator.methods[a].call(this, o, t, s.parameters), "dependency-mismatch" === n) {
                            r = !0;
                            continue
                        }
                        if (r = !1, "pending" === n) return void(this.toHide = this.toHide.not(this.errorsFor(t)));
                        if (!n) return this.formatAndAdd(t, s),
                        !1
                    } catch(l) {
                        if (this.settings.debug && window.console) console.log("exception occured when checking element " + t.id + ", check the '" + s.method + "' method", l);
                        throw l
                    }
                }
                if (!r) {
                    if (this.objectLength(i)) this.successList.push(t);
                    return ! 0
                }
            },
            customMetaMessage: function(t, n) {
                if (e.metadata) {
                    var i = this.settings.meta ? e(t).metadata()[this.settings.meta] : e(t).metadata();
                    return i && i.messages && i.messages[n]
                }
            },
            customDataMessage: function(t, n) {
                return e(t).data("msg-" + n.toLowerCase()) || t.attributes && e(t).attr("data-msg-" + n.toLowerCase())
            },
            customMessage: function(e, t) {
                var n = this.settings.messages[e];
                return n && (n.constructor === String ? n: n[t])
            },
            findDefined: function() {
                for (var e = 0; e < arguments.length; e++) if (void 0 !== arguments[e]) return arguments[e];
                return void 0
            },
            defaultMessage: function(t, n) {
                return this.findDefined(this.customMessage(t.name, n), this.customDataMessage(t, n), this.customMetaMessage(t, n), !this.settings.ignoreTitle && t.title || void 0, e.validator.messages[n], "<strong>Warning: No message defined for " + t.name + "</strong>")
            },
            formatAndAdd: function(t, n) {
                var i = this.defaultMessage(t, n.method),
                r = /\$?\{(\d+)\}/g;
                if ("function" == typeof i) i = i.call(this, n.parameters, t);
                else if (r.test(i)) i = e.validator.format(i.replace(r, "{$1}"), n.parameters);
                this.errorList.push({
                    message: i,
                    element: t
                }),
                this.errorMap[t.name] = i,
                this.submitted[t.name] = i
            },
            addWrapper: function(e) {
                if (this.settings.wrapper) e = e.add(e.parent(this.settings.wrapper));
                return e
            },
            defaultShowErrors: function() {
                var e,
                t;
                for (e = 0; this.errorList[e]; e++) {
                    var n = this.errorList[e];
                    if (this.settings.highlight) this.settings.highlight.call(this, n.element, this.settings.errorClass, this.settings.validClass);
                    this.showLabel(n.element, n.message)
                }
                if (this.errorList.length) this.toShow = this.toShow.add(this.containers);
                if (this.settings.success) for (e = 0; this.successList[e]; e++) this.showLabel(this.successList[e]);
                if (this.settings.unhighlight) for (e = 0, t = this.validElements(); t[e]; e++) this.settings.unhighlight.call(this, t[e], this.settings.errorClass, this.settings.validClass);
                this.toHide = this.toHide.not(this.toShow),
                this.hideErrors(),
                this.addWrapper(this.toShow).show()
            },
            validElements: function() {
                return this.currentElements.not(this.invalidElements())
            },
            invalidElements: function() {
                return e(this.errorList).map(function() {
                    return this.element
                })
            },
            showLabel: function(t, n) {
                var i = this.errorsFor(t);
                if (i.length) {
                    if (i.removeClass(this.settings.validClass).addClass(this.settings.errorClass), i.attr("generated")) i.html(n)
                } else {
                    if (i = e("<" + this.settings.errorElement + "/>").attr({
                        "for": this.idOrName(t),
                        generated: !0
                    }).addClass(this.settings.errorClass).html(n || ""), this.settings.wrapper) i = i.hide().show().wrap("<" + this.settings.wrapper + "/>").parent();
                    if (!this.labelContainer.append(i).length) if (this.settings.errorPlacement) this.settings.errorPlacement(i, e(t));
                    else i.insertAfter(e(t))
                }
                if (!n && this.settings.success) if (i.text("").append('<em class="error_arrow"></em>'), "string" == typeof this.settings.success) i.addClass(this.settings.success);
                else this.settings.success(i, t);
                this.toShow = this.toShow.add(i)
            },
            errorsFor: function(t) {
                var n = this.idOrName(t);
                return this.errors().filter(function() {
                    return e(this).attr("for") === n
                })
            },
            idOrName: function(e) {
                return this.groups[e.name] || (this.checkable(e) ? e.name: e.id || e.name)
            },
            validationTargetFor: function(e) {
                if (this.checkable(e)) e = this.findByName(e.name).not(this.settings.ignore)[0];
                return e
            },
            checkable: function(e) {
                return /radio|checkbox/i.test(e.type)
            },
            findByName: function(t) {
                return e(this.currentForm).find('[name="' + t + '"]')
            },
            getLength: function(t, n) {
                switch (n.nodeName.toLowerCase()) {
                case "select":
                    return e("option:selected", n).length;
                case "input":
                    if (this.checkable(n)) return this.findByName(n.name).filter(":checked").length
                }
                return t.length
            },
            depend: function(e, t) {
                return this.dependTypes[typeof e] ? this.dependTypes[typeof e](e, t) : !0
            },
            dependTypes: {
                "boolean": function(e) {
                    return e
                },
                string: function(t, n) {
                    return !! e(t, n.form).length
                },
                "function": function(e, t) {
                    return e(t)
                }
            },
            optional: function(t) {
                var n = this.elementValue(t);
                return ! e.validator.methods.required.call(this, n, t) && "dependency-mismatch"
            },
            startRequest: function(e) {
                if (!this.pending[e.name]) this.pendingRequest++,
                this.pending[e.name] = !0
            },
            stopRequest: function(t, n) {
                if (this.pendingRequest--, this.pendingRequest < 0) this.pendingRequest = 0;
                if (delete this.pending[t.name], n && 0 === this.pendingRequest && this.formSubmitted && this.form()) e(this.currentForm).submit(),
                this.formSubmitted = !1;
                else if (!n && 0 === this.pendingRequest && this.formSubmitted) e(this.currentForm).triggerHandler("invalid-form", [this]),
                this.formSubmitted = !1
            },
            previousValue: function(t) {
                return e.data(t, "previousValue") || e.data(t, "previousValue", {
                    old: null,
                    valid: !0,
                    message: this.defaultMessage(t, "remote")
                })
            }
        },
        classRuleSettings: {
            required: {
                required: !0
            },
            email: {
                email: !0
            },
            url: {
                url: !0
            },
            date: {
                date: !0
            },
            dateISO: {
                dateISO: !0
            },
            number: {
                number: !0
            },
            digits: {
                digits: !0
            },
            creditcard: {
                creditcard: !0
            }
        },
        addClassRules: function(t, n) {
            if (t.constructor === String) this.classRuleSettings[t] = n;
            else e.extend(this.classRuleSettings, t)
        },
        classRules: function(t) {
            var n = {},
            i = e(t).attr("class");
            if (i) e.each(i.split(" "), 
            function() {
                if (this in e.validator.classRuleSettings) e.extend(n, e.validator.classRuleSettings[this])
            });
            return n
        },
        attributeRules: function(t) {
            var n = {},
            i = e(t);
            for (var r in e.validator.methods) {
                var o;
                if ("required" === r) {
                    if (o = i.get(0).getAttribute(r), "" === o) o = !0;
                    o = !!o
                } else o = i.attr(r);
                if (o) n[r] = o;
                else if (i[0].getAttribute("type") === r) n[r] = !0
            }
            if (n.maxlength && /-1|2147483647|524288/.test(n.maxlength)) delete n.maxlength;
            return n
        },
        metadataRules: function(t) {
            if (!e.metadata) return {};
            var n = e.data(t.form, "validator").settings.meta;
            return n ? e(t).metadata()[n] : e(t).metadata()
        },
        staticRules: function(t) {
            var n = {},
            i = e.data(t.form, "validator");
            if (i.settings.rules) n = e.validator.normalizeRule(i.settings.rules[t.name]) || {};
            return n
        },
        normalizeRules: function(t, n) {
            if (e.each(t, 
            function(i, r) {
                if (r === !1) return void delete t[i];
                if (r.param || r.depends) {
                    var o = !0;
                    switch (typeof r.depends) {
                    case "string":
                        o = !!e(r.depends, n.form).length;
                        break;
                    case "function":
                        o = r.depends.call(n, n)
                    }
                    if (o) t[i] = void 0 !== r.param ? r.param: !0;
                    else delete t[i]
                }
            }), e.each(t, 
            function(i, r) {
                t[i] = e.isFunction(r) ? r(n) : r
            }), e.each(["minlength", "maxlength", "min", "max"], 
            function() {
                if (t[this]) t[this] = Number(t[this])
            }), e.each(["rangelength", "range"], 
            function() {
                if (t[this]) t[this] = [Number(t[this][0]), Number(t[this][1])]
            }), e.validator.autoCreateRanges) {
                if (t.min && t.max) t.range = [t.min, t.max],
                delete t.min,
                delete t.max;
                if (t.minlength && t.maxlength) t.rangelength = [t.minlength, t.maxlength],
                delete t.minlength,
                delete t.maxlength
            }
            if (t.messages) delete t.messages;
            return t
        },
        normalizeRule: function(t) {
            if ("string" == typeof t) {
                var n = {};
                e.each(t.split(/\s/), 
                function() {
                    n[this] = !0
                }),
                t = n
            }
            return t
        },
        addMethod: function(t, n, i) {
            if (e.validator.methods[t] = n, e.validator.messages[t] = void 0 !== i ? i: e.validator.messages[t], n.length < 3) e.validator.addClassRules(t, e.validator.normalizeRule(t))
        },
        methods: {
            required: function(t, n, i) {
                if (!this.depend(i, n)) return "dependency-mismatch";
                if ("select" === n.nodeName.toLowerCase()) {
                    var r = e(n).val();
                    return r && r.length > 0
                }
                if (this.checkable(n)) return this.getLength(t, n) > 0;
                if (t == e(n).attr("placeholder")) t = "";
                return e.trim(t).length > 0
            },
            remote: function(t, n, i) {
                if (this.optional(n)) return "dependency-mismatch";
                var r = this.previousValue(n);
                if (!this.settings.messages[n.name]) this.settings.messages[n.name] = {};
                if (r.originalMessage = this.settings.messages[n.name].remote, this.settings.messages[n.name].remote = r.message, i = "string" == typeof i && {
                    url: i
                } || i, this.pending[n.name]) return "pending";
                if (r.old === t) return r.valid;
                r.old = t;
                var o = this;
                this.startRequest(n);
                var a = {};
                return a[n.name] = t,
                e.ajax(e.extend(!0, {
                    url: i,
                    mode: "abort",
                    port: "validate" + n.name,
                    dataType: "json",
                    data: a,
                    success: function(i) {
                        o.settings.messages[n.name].remote = r.originalMessage;
                        var a = i === !0 || "true" === i;
                        if (a) {
                            var s = o.formSubmitted;
                            o.prepareElement(n),
                            o.formSubmitted = s,
                            o.successList.push(n),
                            delete o.invalid[n.name],
                            o.showErrors()
                        } else {
                            var l = {},
                            u = i || o.defaultMessage(n, "remote");
                            l[n.name] = r.message = e.isFunction(u) ? u(t) : u,
                            o.invalid[n.name] = !0,
                            o.showErrors(l)
                        }
                        r.valid = a,
                        o.stopRequest(n, a)
                    }
                },
                i)),
                "pending"
            },
            minlength: function(t, n, i) {
                var r = e.isArray(t) ? t.length: this.getLength(e.trim(t), n);
                return this.optional(n) || r >= i
            },
            maxlength: function(t, n, i) {
                var r = e.isArray(t) ? t.length: this.getLength(e.trim(t), n);
                return this.optional(n) || i >= r
            },
            rangelength: function(t, n, i) {
                var r = e.isArray(t) ? t.length: this.getLength(e.trim(t), n);
                return this.optional(n) || r >= i[0] && r <= i[1]
            },
            min: function(e, t, n) {
                return this.optional(t) || e >= n
            },
            max: function(e, t, n) {
                return this.optional(t) || n >= e
            },
            range: function(e, t, n) {
                return this.optional(t) || e >= n[0] && e <= n[1]
            },
            email: function(t, n) {
                return t = e.trim(t),
                this.optional(n) || /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))$/i.test(t)
            },
            url: function(e, t) {
                return this.optional(t) || /^((https?|ftp):\/\/)?(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(e)
            },
            date: function(e, t) {
                return this.optional(t) || !/Invalid|NaN/.test(new Date(e))
            },
            dateISO: function(e, t) {
                return this.optional(t) || /^\d{4}[\/\-]\d{1,2}[\/\-]\d{1,2}$/.test(e)
            },
            number: function(e, t) {
                return this.optional(t) || /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(e)
            },
            digits: function(e, t) {
                return this.optional(t) || /^\d+$/.test(e)
            },
            creditcard: function(e, t) {
                if (this.optional(t)) return "dependency-mismatch";
                if (/[^0-9 \-]+/.test(e)) return ! 1;
                var n = 0,
                i = 0,
                r = !1;
                e = e.replace(/\D/g, "");
                for (var o = e.length - 1; o >= 0; o--) {
                    var a = e.charAt(o);
                    if (i = parseInt(a, 10), r) if ((i *= 2) > 9) i -= 9;
                    n += i,
                    r = !r
                }
                return n % 10 === 0
            },
            equalTo: function(t, n, i) {
                var r = e(i);
                if (this.settings.onfocusout) r.unbind(".validate-equalTo").bind("blur.validate-equalTo", 
                function() {
                    e(n).valid()
                });
                return t === r.val()
            }
        }
    }),
    e.format = e.validator.format
} (jQuery),
function(e) {
    var t = {};
    if (e.ajaxPrefilter) e.ajaxPrefilter(function(e, n, i) {
        var r = e.port;
        if ("abort" === e.mode) {
            if (t[r]) t[r].abort();
            t[r] = i
        }
    });
    else {
        var n = e.ajax;
        e.ajax = function(i) {
            var r = ("mode" in i ? i: e.ajaxSettings).mode,
            o = ("port" in i ? i: e.ajaxSettings).port;
            if ("abort" === r) {
                if (t[o]) t[o].abort();
                return t[o] = n.apply(this, arguments)
            }
            return n.apply(this, arguments)
        }
    }
} (jQuery),
function(e) {
    if (!jQuery.event.special.focusin && !jQuery.event.special.focusout && document.addEventListener) e.each({
        focus: "focusin",
        blur: "focusout"
    },
    function(t, n) {
        function i(t) {
            return t = e.event.fix(t),
            t.type = n,
            e.event.handle.call(this, t)
        }
        e.event.special[n] = {
            setup: function() {
                this.addEventListener(t, i, !0)
            },
            teardown: function() {
                this.removeEventListener(t, i, !0)
            },
            handler: function(t) {
                var i = arguments;
                return i[0] = e.event.fix(t),
                i[0].type = n,
                e.event.handle.apply(this, i)
            }
        }
    });
    e.extend(e.fn, {
        validateDelegate: function(t, n, i) {
            return this.bind(n, 
            function(n) {
                var r = e(n.target);
                if (r.is(t)) return i.apply(r, arguments);
                else return void 0
            })
        }
    })
} (jQuery);