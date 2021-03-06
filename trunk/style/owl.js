/* Main Owl library */
(function(window, document) {
    if (window.Owl) return;
    if (!String.prototype.trim) {
        String.prototype.trim = function() {
            return this.replace(/(^\s+|\s+$)/g, '');
        }
    }

    function isWin(o) {
        return o.toString() == window.toString();
    }

    // some test modern browser
    var arA = iF([].indexOf) ? (function(a, v) {
        if (a.indexOf(v) == -1) a.push(v);
    }) : (function(a, v, i) {
        if (!v.owl_flag) v.owl_flag = [];
        if (v.owl_flag[i]) return;
        v.owl_flag[i] = true;
        a.push(v);
    });

    // This is key main
    window.Owl = function(w) {
        if (arguments.length > 1) {
            return Owl.call(this, $.toArray(arguments));
        }

        w = w || [];

        if (iS(w)) {
            if (w.match(/^<([^\s>]+)\s?[^>]*>/i)) {
                var tag = Low(RegExp.$1),
                div = document.createElement("div"),
                spec = {
                    option: ['<select multiple="multiple">', '</select>'],
                    legend: ['<fieldset>', '</fieldset>'],
                    tbody: ['<table>', '</table>'],
                    tr: ['<table><tbody>', '</tbody></table>'],
                    td: ['<table><tbody><tr>', '</tr></tbody></table>'],
                    th: ['<table><tbody><tr>', '</tr></tbody></table>'],
                    colgroup: ['<table><tbody></tbody>', '</table>'],
                    area: ['<map>', '</map>']
                };

                if (spec[tag]) {
                    div.innerHTML = spec[tag][0] + w + spec[tag][1];
                    return $(div.getElementsByTagName(tag)[0]);
                } else {
                    div.innerHTML = w;
                }

                return $(div).child("*");
            }
            return DOMCONTENT_LOADED ? new $.DOM($.Selector(w, document)) : new QueueFn(w);
        } else if (/^1|9|11$/.test(w.nodeType) || isWin(w)) {
            return new $.DOM(w);
        } else if (iF(w)) {
            return onReady(w);
        } else if (isOwl(w)) {
            return w;
        } else if (isA(w) || 'length' in w) {
            var rs = [];
            for (var i = 0, ui = ID++; i < w.length; i++) {
                $d = Owl(w[i]);
                if ($d) {
                    for (var j = 0; j < $d.size(); j++) {
                        arA(rs, $d.k(j), ui);
                    }
                }
            }
            return new $.DOM(rs);
        }
        return null
    };

    var $ = Owl,
    ID = -1,
    TIME_INTERVAL = 13;
    $.version = "1.5";
    $.filter = {};
    $.makeClass = function(Class, obj) {
        Extend(Class.prototype, obj || {});
        return Class;
    };

    $.browser = {
        agent: navigator.userAgent
    };

    if ($.browser.agent.match(/\s(chrome|firefox|safari|msie)[\/\s]*([\d\.]+)[\s;]?/i)) {
        $.browser[Low(RegExp.$1)] = parseFloat(RegExp.$2);
    }

    /**
     *  DOM
     */
    var DOM = {
        shift: function(act, elem, target) {
            if (act == "append" && elem.tagName == "TR" && target.tagName == "TABLE") {
                var b = target.getElementsByTagName("tbody")[0];
                if (!b) {
                    b = document.createElement("tbody");
                    target.appendChild(b);
                }
                return b;
            }
            return target;
        },
        insert: function(act, html, elem) {
            var tag = {
                append: 0,
                before: 1,
                after: 1,
                first: 0,
                replace: 1
            },
            e = DOM.evalHTML(html, elem, tag[act]),
            a = e.nodes,
            i;
            switch (act) {
                case "append":

                    for (i = 0; i < a.length; i++) {
                        DOM.shift(act, a[i], elem).appendChild(a[i]);
                    }
                    break;

                case "before":
                    for (i = 0; i < a.length; i++) {
                        Parent(elem).insertBefore(a[i], elem);
                    }
                    break;

                case "after":
                    for (i = 0; i < a.length; i++) {
                        Parent(elem).insertBefore(a[i], elem.nextSibling);
                    }
                    break;

                case "first":
                    for (i = 0; i < a.length; i++) {
                        elem.insertBefore(a[i], DOM.shift(act, a[i], elem).firstChild);
                    }
                    break;
                case "replace":
                    for (i = 0; i < a.length; i++) {
                        Parent(elem).insertBefore(a[i], elem);
                    }
                    Parent(elem).removeChild(elem);
                    break;

                default:
            }

            for (i = 0; i < e.js.length; i++) {
                DOM.evalScript(e.js[i], elem);
            }
        },
        //parse html string to html normal and javascript
        evalString: function(html) {
            html = html.toString();
            var m, result = '',
            js = [],
            JS_SRC_REG = /^\<script([^<>]*)>([\s\S]*?)\<\/script>/i,
            JS_TEXT_REG = /^(?:(?:\<(textarea|style)[^<>]*?>[\s\S]*?\<\/\1>)|(?:\<!--?[\s\S]*?-->)|(?:\<\/[^\/\s<>]+>)|(?:\<((?!script)[^\/\s<>]+)([^<>]*)?(?:\/?>)?)|(?:[^<]+))+/i;

            while (html !== '') {
                if (!html.match(/\<script([^<>]*)>/i)) {
                    result += html;
                    break;
                }
                if (html.match(JS_SRC_REG)) {
                    html = RegExp.rightContext || '';
                    js.push({
                        src: RegExp.$1,
                        text: RegExp.$2
                    });
                } else if ( !! (m = html.match(JS_TEXT_REG))) {
                    html = RegExp.rightContext || '';
                    result += m[0];
                } else {
                    result += html;
                    break;
                }
            }

            return {
                html: result,
                js: js
            };
        },
        spec: {
            select: ['<select multiple="multiple">', '</select>', 0],
            fieldset: ['<fieldset>', '</fieldset>', 0],
            table: ['<table>', '</table>', 0],
            tbody: ['<table><tbody>', '</tbody></table>', 1],
            tr: ['<table><tbody><tr>', '</tr></tbody></table>', 2],
            colgroup: ['<table><tbody></tbody><colgroup>', '</colgroup></table>', 2],
            map: ['<map>', '</map>', 0]
        },
        parse: function(tag, html, doc) {
            tag = Low(tag);
            var div = doc.createElement("div");
            if (DOM.spec[tag]) {
                div.innerHTML = DOM.spec[tag][0] + html + DOM.spec[tag][1];
                div = div.getElementsByTagName('*')[DOM.spec[tag][2]];
            } else {
                div.innerHTML = html;
            }

            return $.toArray(div.childNodes);
        },
        evalHTML: function(html, elem, lv) {
            if (iF(html)) html = html.call(elem);
            if (html.nodeType == 1) {
                return {
                    nodes: [html],
                    js: []
                };
            }

            var rs = DOM.evalString(html),
            tag = "div";

            if (lv == 0) tag = elem.tagName;

            if (lv == 1 && Parent(elem)) tag = Parent(elem).tagName;

            return {
                nodes: DOM.parse(tag, rs.html, elem.ownerDocument),
                js: rs.js
            };
        },

        eval: iF(window.execScript) ? window.execScript : function(s) {
            eval.call(window, s);
        },

        evalScript: function(js, elem) {
            if (js.text) {
                try {
                    DOM.eval.call(window,js.text.replace(/^\s*<!(?:\[CDATA\[|\-\-)/i, ''));
                } catch (e) {
                    throw e.toString();
                }
            } else if (js.src && js.src.match(/src=(["'])([^\1]+)\1/i)) {

                var doc = elem.ownerDocument,
                head = doc.getElementsByTagName("head")[0] || doc.documentElement,
                s = doc.createElement("script");
                s.async = "async";
                s.src = RegExp.$2;
                head.insertBefore(s, head.firstChild);
            }
        }
    };

    /* SELECTOR */ (function() {
        RegExp.prototype.init = function(str) {
            var match = this.exec(str);
            if (RegExp.prototype.lastMatch === undefined && match) RegExp.lastMatch = match[0];
            // opera not support lastMatch
            return match && (match.index == 0) ? match : null;
        };
        // Only simple return a msg when selector error
        var ERROR = "DOM Selector systax error",
        UID = 0;
        var TAG = /(?:[\w\u00c0-\uFFFF_\-]|\\.|\*)+/,
        HASH = /#((?:[\w\u00c0-\uFFFF_\-]|\\.)+)/,
        CLASS = /\.([\w\u00c0-\uFFFF_\-]+)/,
        ATTR = /\[\s*((?:[\w\u00c0-\uFFFF_\-]|\\.)+)(?:\s*((?:\^|\$|\*|~|\||\!)?=)\s*((?:[\w\u00c0-\uFFFF_\-]+)|(?:(?:('|")[^\4]+(\4)))))?\s*\]/,
        PSEUDO = /:((?:[\w\u00c0-\uFFFF_\-]|\\.)+)(?:\(((?:\(.*\)|[^()]|\\\(|\\\))+)\))?/,
        SIMPLE = /(?:(?:\((?:\(.*?\)|[^()]|\\(|\\))+\))|(?:\[[^\]]+\])|[^\s\+>~])*/,
        PART = /(\s*[\s\+\>~]\s*)((?:(?:\([^()]+\))|(?:\[[^\]]+\])|[^\s\+>~])+)/,
        SYSTAX = /(?:\((?:\(.*?\)|[^()]|\\(|\\))+\)|\[[^\]]+\]|[^,\(\)\[\]])+(?=\s*,\s*)/;
        var Hawk = function(selector, root, max) {
            if (root === undefined) root = document;
            if (!root) return [];

            // check support querySelectorAll
            if (root.querySelectorAll && root.nodeType == 9) try {
                return root.querySelectorAll(selector);
            } catch (e) {}

            if (root.getElementsByClassName && selector.match(/^\.([\w\u00c0-\uFFFF_\-]+)$/i)) try {
                return root.getElementsByClassName(RegExp.$1);
            } catch (e) {}

            if (max === undefined) max = -1;
            // test for multiple selector
            if (SYSTAX.init(selector)) {
                var __selector = RegExp.lastMatch;
                if ((RegExp.rightContext != '') && RegExp.rightContext.match(/^\s*,\s*(?!\s)/)) {
                    var find = Hawk(RegExp.rightContext, root, -1),
                    found = Hawk(
                        __selector, root, max);
                    return Hawk.concat(find, found, max);
                } else {
                    throw ERROR;
                }
            }
            if (selector.match(/^body\s+(?![+>~])(.*)$/i) && root.body) return Hawk(RegExp.$1, root.body);
            if (selector.match(/^#((?:[\w\u00c0-\uFFFF_\-]|\\.)+)\s+(?![+>~])(.*)$/i) && root.getElementById) return Hawk(RegExp.$2, root.getElementById(RegExp.$1));
            var splitSelector = [],
            match;
            // start must is a simple
            if (SIMPLE.init(selector)) {
                selector = RegExp.rightContext;
                splitSelector.push(Hawk.evalDetail(RegExp.lastMatch));
            } else {
                throw ERROR;
            }
            while (selector.length)
                if (match = PART.init(selector)) {
                    selector = RegExp.rightContext;
                    splitSelector.push(match[1].replace(/\s+/g, ''));
                    splitSelector.push(Hawk.evalDetail(match[2]));
                } else {
                    throw ERROR;
                }
            return Hawk.searchEnd(splitSelector, root, max);
        };
        Hawk.searchEnd = function(splitSelector, root, max) {
            var last = splitSelector.pop(),
            html = root,
            elems = [];
            if (!last.TAG) last.TAG = [
                ["*"]
                ];
            if ((splitSelector[1] == '' || splitSelector[1] == ">") && splitSelector[0].HASH && root.getElementById) html = root.getElementById(splitSelector[0].HASH[0][0]);
            if (last.HASH && root.getElementById) {
                var obj = html.getElementById(last.HASH[0][0]);
                if (!obj) return [];
                elems.push(obj);
                if (!last.TAG && !last.ATTR && !last.CLASS && !last.PSEUDO && (last.HASH.length == 1 && (max === -1)) && !splitSelector.length) return elems;
            } else if (last.CLASS && root.getElementsByClassName) {
                elems = html.getElementsByClassName(last.CLASS[0][0]);
                if (!last.HASH && !last.ATTR && !last.PSEUDO && !last.TAG && !splitSelector.length && (max === -1)) return elems;
            } else if (last.TAG && last.TAG[0]) {
                // fragdocument doesn't has 'getElementsByTagName' but
                // selectorAll or childNodes
                if (html.getElementsByTagName) elems = html.getElementsByTagName(last.TAG[0][0]);
                else if (html.querySelectorAll) elems = html.html.querySelectorAll(last.TAG[0][0]);
                else elems = elems.childNodes || [];
                if (!last.HASH && !last.ATTR && !last.CLASS && !last.PSEUDO && !splitSelector.length && (max === -1)) return elems;
            }
            var result = [];
            if (splitSelector.length == 0) {
                for (var i = 0; i < elems.length; i++) {
                    if (max == result.length) break;
                    if (Hawk.satisfy(elems[i], last)) result.push(elems[i]);
                }
            } else {
                for (var i = 0; i < elems.length; i++) {
                    if (max == result.length) break;
                    if (Hawk.satisfy(elems[i], last)) {
                        var app = splitSelector.slice(0);
                        if (Hawk.COMBINEEND(elems[i], app.pop(), app.pop(), app, root)) result.push(elems[i]);
                    }
                }
            }
            return result;
        };
        Hawk.COMBINEEND = function(element, combine, detail, app, root) {
            var fn = arguments.callee,
            ui = UID++;
            switch (combine) {
                case ">":
                    var node = Parent(element);
                    if (!node.owl_qr) node.owl_qr = [];
                    if (node.owl_qr[ui] !== undefined) return node.owl_qr[ui];
                    if (!node || (!Hawk.contains(node, root) && (node !== root)) || !Hawk.satisfy(node, detail)) return node.owl_qr[ui] = false;
                    if (!app.length) return node.owl_qr[ui] = true;
                    return node.owl_qr[ui] = fn(node, app.pop(), app.pop(), app, root);
                case "+":
                    var node = Hawk.pre(element);
                    if (node && !node.owl_qr) node.owl_qr = [];
                    if (node && (node.owl_qr[ui] !== undefined)) return node.owl_qr[ui];
                    if (!node || !Hawk.contains(node, root) || !Hawk.satisfy(node, detail)) {
                        if (node) node.owl_qr[ui] = false;
                        return false;
                    }
                    if (!app.length) return node.owl_qr[ui] = true;
                    return node.owl_qr[ui] = fn(node, app.pop(), app.pop(), app, root);
                case "~":
                    var node = element;
                    while (node = Hawk.pre(node)) {
                        if (!node.owl_qr) node.owl_qr = [];
                        if (node.owl_qr[ui] !== undefined) return node.owl_qr[ui];
                        if (Hawk.satisfy(node, detail) && Hawk.contains(node, root)) if (!app.length) {
                            node.owl_qr[ui] = true;
                            return true;
                        } else {
                            var appz = app.slice(0);
                            if (fn(node, app.pop(), app.pop(), app, root)) {
                                node.owl_qr[ui] = true;
                                return true;
                            }
                        }
                        node.owl_qr[ui] = false;
                    }
                    return false;
                case '':
                    var node = element;
                    while (node = Parent(node)) {
                        if (!node.owl_qr) node.owl_qr = [];
                        if (node.owl_qr[ui] !== undefined) return node.owl_qr[ui];
                        if (Hawk.satisfy(node, detail) && (Hawk.contains(node, root) || node == root)) if (!app.length) {
                            return node.owl_qr[ui] = true;
                        } else {
                            var appz = app.slice(0);
                            if (fn(node, appz.pop(), appz.pop(), appz, root)) return node.owl_qr[ui] = true;
                        }
                        node.owl_qr[ui] = false;
                    }
                    return false;
                default:
                    throw ERROR;
            }
        };
        Hawk.satisfy = function(elem, details) {
            if (!elem || elem.nodeType != 1) return false;
            if (details && details.nodeType == 1) return details === elem;
            for (var x in details) {
                for (var j = 0; j < details[x].length; j++)
                    if (!Hawk.check[x].apply(elem, details[x][j])) return false;
            }
            return true;
        };
        Hawk.evalDetail = function(simple) {
            var details = {},
            match;
            if (TAG.init(simple)) {
                simple = RegExp.rightContext;
                if (!details.TAG) details.TAG = [];
                details.TAG.push([RegExp.lastMatch.replace(/\\/g, '').toUpperCase()]);
            }
            while (simple != '') {
                if (HASH.init(simple)) {
                    simple = RegExp.rightContext;
                    if (!details.HASH) details.HASH = [];
                    details.HASH.push([RegExp.$1.replace(/\\/g, '')]);
                } else if (CLASS.init(simple)) {
                    simple = RegExp.rightContext;
                    if (!details.CLASS) details.CLASS = [];
                    details.CLASS.push([RegExp.$1.replace(/\\/g, '')]);
                } else if (match = ATTR.init(simple)) {
                    simple = RegExp.rightContext;
                    if (match[1]) match[1] = match[1].replace(/\\/g, '');
                    if (match[2]) match[2] = match[2].replace(/\s+/g, '');
                    if (match[3]) match[3] = match[3].replace(/^(['|"])(.*)(\1)$/, "$2");
                    if (!details.ATTR) details.ATTR = [];
                    details.ATTR.push([match[1], match[2], match[3]]);
                } else if (match = PSEUDO.init(simple)) {
                    if (!details.PSEUDO) details.PSEUDO = [];
                    simple = RegExp.rightContext;
                    if (match[1]) match[1] = match[1].replace(/\\/g, '');
                    if (match[2]) match[2] = match[2].replace(/^(['|"])(.*)(\1)$/, "$2");
                    if (match[1] == "not") {
                        var text = match[2] + ",",
                        detail = [];
                        while (SYSTAX.init(text)) {
                            text = RegExp.rightContext;
                            if (match[1] == "has") detail.push(RegExp.lastMatch);
                            else detail.push(Hawk.evalDetail(RegExp.lastMatch));
                            if (text.match(/^\s*,\s*(?!\s)/)) text = RegExp.rightContext;
                            else throw ERROR;
                        }
                        if (text) throw ERROR;
                        details.PSEUDO.push([match[1], detail]);
                        continue;
                    }
                    if (match[1] == "nth-last-child" || match[1] == "nth-child" || match[1] == "nth-of-type" || match[1] == "nth-last-of-type") match[2] = Hawk.nth(match[2] || '');
                    details.PSEUDO.push([match[1], match[2]]);
                } else {
                    throw ERROR + ": " + simple;
                }
            }
            return details;
        };
        Hawk.check = {
            TAG: function(tag) {
                if (tag == '' || tag == "*") return true;
                return this.tagName == tag;
            },
            HASH: function(id) {
                return this.id == id;
            },
            CLASS: function(name) {
                return (" " + this.className + " ").indexOf(" " + name + " ") > -1;
            },
            ATTR: function(attr, opera, test) {
                var value = Hawk.attr(this, attr);
                if (value == null) return opera == "!=";
                if (!opera) return true;
                switch (opera) {
                    case "!=":
                        return value !== test;
                    case "=":
                        return value === test;
                    case "*=":
                        return value.indexOf(test) > -1;
                    case "^=":
                        return value.indexOf(test) == 0;
                    case "$=":
                        return value.lastIndexOf(test) + test.length == value.length;
                    case "|=":
                        return (value === test) || (value.substr(0, value.length + 1) === (test + "-"));
                    case "~=":
                        return (value.match(/\s/i) == null) && ((" " + value + " ").indexOf(" " + test + " ") > -1);
                    default:
                        return false;
                }
            },
            PSEUDO: function(pseudo, argums) {
                if (argums == '' || argums === undefined) {
                    if (Hawk.SIMPLEPSEUDO[pseudo]) {
                        return Hawk.SIMPLEPSEUDO[pseudo](this);
                    } else {
                        throw ERROR;
                    }
                } else {
                    if (Hawk.FUNCPSEUDO[pseudo]) {
                        return Hawk.FUNCPSEUDO[pseudo](this, argums);
                    } else {
                        throw ERROR;
                    }
                }
                return true;
            }
        };

        // test is nodeA among nodeB , nodeA must is a element
        Hawk.contains = function(nodeA, nodeB) {
            if (!nodeB || !nodeA || nodeA.nodeType != 1) return false;
            // fix if nodeB is document
            if (nodeB.nodeType == 9) return arguments.callee(nodeA, nodeB.documentElement);
            return nodeB.contains ? (nodeA !== nodeB) && nodeB.contains(nodeA) : nodeB.compareDocumentPosition ? !! (nodeB.compareDocumentPosition(nodeA) & 16) : false;
        };

        (function() {
            // choose the best way for some method of array
            var div = document.createElement("div");
            div.className = "f";
            Hawk.attr = div.getAttribute("class") === "f" ?
            function(elem, attr) {
                return elem.getAttribute(attr, 2);
            } : function(elem, attr) {
                switch (attr) {
                    case 'class':
                        return elem.className;
                    case 'style':
                        return elem.style.cssText;
                    case 'for':
                        return elem.htmlFor;
                    case 'name':
                        return elem.name;
                    default:
                        return elem.getAttribute(attr, 2);
                }
            };
            div = null;
        // free memory
        })();

        Hawk.next = function(elem) {
            var node = elem;
            while (node = (node.nextElementSibling || node.nextSibling))
                if (node.nodeType == 1) return node;
            return null;
        };

        Hawk.pre = function(elem) {
            var node = elem;
            while (node = (node.previousElementSibling || node.previousSibling))
                if (node.nodeType == 1) return node;
            return null;
        };
        /*---------------------------------------------------------------------------------*/
        // SUPPORT SIMPLE PSEUDO & PSEUDO FUNCTION
        /*---------------------------------------------------------------------------------*/
        Hawk.SIMPLEPSEUDO = {
            "first-child": function(e) {
                return Parent(e) && !Hawk.pre(e);
            },
            "last-child": function(e) {
                return Parent(e) && !Hawk.next(e);
            },
            "only-child": function(e) {
                return Parent(e) && !Hawk.pre(e) && !Hawk.next(e);
            },
            "first-of-type": function(e) {
                return Parent(e) && !Hawk.preType(e);
            },
            "last-of-type": function(e) {
                return Parent(e) && !Hawk.nextType(e);
            },
            "only-of-type": function(e) {
                return Parent(e) && !Hawk.preType(e) && !Hawk.nextType(e);
            },
            enabled: function(e) {
                return !e.disabled;
            },
            disabled: function(e) {
                return e.disabled;
            },
            checked: function(e) {
                return e.checked;
            },
            indeterminate: function(e) {
                return e.indeterminate;
            },
            target: function(e) {
                return location.hash && (e.id == location.hash.slice(1));
            },
            visible: function(e) {
                return e.offsetWidth > 0 && e.offsetHeight > 0;
            },
            hidden: function(e) {
                return !Hawk.SIMPLEPSEUDO.visible(e)
            },
            input: function(e) {
                return /input|select|textarea|button/i.test(e.nodeName);
            }
        };
        Hawk.FUNCPSEUDO = {
            contains: function(e, text) {
                return (e.innerText || e.textContent || '').indexOf(text) > -1;
            },
            has: function(e, str) {
                return Hawk(str, e, 1) > 0;
            },
            not: function(e, details) {
                for (var i = 0; i < details.length; i++)
                    if (Hawk.satisfy(e, details[i])) return false;
                return true;
            },
            filter: function(e, fc) {
                var f = $.filter[fc];
                return iF(f) ? !! f.call(e) : false;
            },
            'nth-child': function(e, pos) {
                var parent = Parent(e),
                index = Hawk.checkOrder(e, "begin");
                if (!parent) return false;
                return pos[0] == 1 ? (pos[1] == index) : (((index - pos[1]) % pos[0] == 0) && (index - pos[1] > 0));
            },
            'nth-last-child': function(e, express) {
                var parent = Parent(e),
                index = Hawk.checkOrder(e, "last");
                if (!parent) return false;
                return pos[0] == 1 ? (pos[1] == index) : (((index - pos[1]) % pos[0] == 0) && (index - pos[1] > 0));
            },
            'nth-of-type': function(e, pos) {
                var parent = Parent(e),
                index = Hawk.checkType(e, "begin");
                if (!parent) return false;
                return pos[0] == 1 ? (pos[1] == index) : (((index - pos[1]) % pos[0] == 0) && (index - pos[1] > 0));
            },
            'nth-last-of-type': function(e, pos) {
                var parent = Parent(e),
                index = Hawk.checkType(e, "end");
                if (!parent) return false;
                return pos[0] == 1 ? (pos[1] == index) : (((index - pos[1]) % pos[0] == 0) && (index - pos[1] > 0));
            }
        };
        Hawk.nth = function(express) {
            express = express.replace(/even/, "2n").replace(/odd/, "2n+1").replace(/\s+/g, '');
            if (express == '') throw ERROR;
            if (express.match(/^(?:([\-\+]?\d+)?n)?([\-\+]?\d+)?$/)) return [parseInt(RegExp.$1) || 1, parseInt(RegExp.$2) || 0];
            throw ERROR;
        };
        Hawk.checkOrder = function(e, type) {
            try {
                var all = Parent(e).children;
                var index = Hawk.indexOf.call(all, e) + 1;
                return type == "begin" ? index : (all.length - index);
            } catch (e) {
                var i = 0,
                node = e;
                if (type == "begin") while (node = Hawk.pre(node)) {
                    i++;
                    if (node === e) break;
                }
                if (type == "last") while (node = Hawk.next(node)) {
                    i++;
                    if (node === e) break;
                }
                return i;
            }
        };
        Hawk.checkType = function(e, type) {
            var i = 0,
            node = e;
            if (type == "begin") while (node = Hawk.pre(node)) {
                if (node.tagName == e.tagName) i++;
                if (node === e) break;
            }
            if (type == "last") while (node = Hawk.next(node)) {
                if (node.tagName == e.tagName) i++;
                if (node === e) break;
            }
            return i;
        };
        Hawk.toArray = function(list) {
            try {
                return Array.prototype.slice.call(list, 0);
            } catch (e) {
                var result = [];
                for (var i = 0; i < list.length; i++)
                    result.push(list[i]);
                return result;
            }
        };
        Hawk.indexOf = function(elem) {
            var length = this.length;
            for (var i = 0; i < length; i++) {
                var current = this[i];
                if (!(typeof(current) === 'undefined') || i in this) {
                    if (current === elem) return i;
                }
            }
            return -1;
        };
        if (Array.prototype.indexOf) {
            try {
                Array.prototype.indexOf.call(document.documentElement.childNodes, document.documentElement.firstChild);
                Hawk.indexOf = Array.prototype.indexOf;
            } catch (e) {}
        }
        Hawk.concat = function() {
            var result = [],
            ui = UID++;
            for (var i = 0; i < arguments.length; i++)
                for (var j = 0; j < arguments[i].length; j++) {
                    if (!arguments[i][j].owl_qr) arguments[i][j].owl_qr = [];
                    if (!arguments[i][j].owl_qr[ui]) {
                        result.push(arguments[i][j]);
                        arguments[i][j].owl_qr[ui] = true;
                    }
                }
            return result;
        };
        $.Selector = Hawk;
    })();

    /*
     * Owl use a lib function store in $.DOM
     */
    $.toArray = $.Selector.toArray;
    $.test = function(simple, elem) {
        return $.Selector.satisfy(elem, $.Selector.evalDetail(simple));
    };
    $.DOM = function(nodes) {
        this.nodes = isDOM(nodes) ? [1, nodes].slice(1) : $.toArray(nodes);
        var a = $.toArray(arguments);
        if (a.length > 1) {
            a.shift();
            while (a.length)
                this.add(a.shift());
        }
    };

    // Basic extend
    function extDOM(a, fn, no_over) {
        var f = arguments.callee;
        if (arguments.length > 1) {
            if (!$([])[a] || !no_over) $.DOM.prototype[a] = fn;
        } else {
            for (var x in a)
                f(x, a[x]);
        }
    };

    // basic method
    extDOM({
        each: function(fn) {
            var elem = this.nodes,
            size = elem.length;
            for (var i = 0; i < elem.length; i++) {
                if (isWin(elem[i]) || (elem[i] && (elem[i].nodeType == 1 || elem[i].nodeType == 9 || elem[i].nodeType == 11))) {
                    if (fn.call(elem[i], i, size, this) === false) break;
                }
            }
            return this;
        },
        eachElement: function(fn) {
            return this.each(function(i, z, a) {
                if (this.nodeType == 1) return fn.call(this, i, z, a);
            });
        },
        val: function(fn) {
            if (arguments.length == 0) fn = function() {
                return this.value;
            };
            var value = null;
            this.each(function(i, z) {
                if (this) value = fn.call(this, i, z);
                return false;
            });
            return value;
        },
        k: function(i) {
            return this.nodes[i < 0 ? this.nodes.length + i : i] || null;
        },
        size: function() {
            return this.nodes.length;
        },
        slice: function() {
            return $($.toArray(this.nodes).slice.apply(this.nodes, $.toArray(arguments)));
        },
        p: function(i) {
            return this.reset(this.nodes[i]);
        },
        add: function(str) {
            if (!str) return this;
            if (iS(str)) str = $.Selector(str);
            else if (isDOM(str)) str = [1, str].slice(1);
            else if (isOwl(str)) str = str.nodes;
            this.nodes = $.Selector.concat(this.nodes, str);
            return this;
        },
        reset: function(arr) {
            return $(arr);
        },
        clone: function(bool) {
            return this.eachElement(function(elem, i) {
                this.nodes[i] = elem.cloneNode(bool);
            });
        },
        setAttr: function(name, value) {
            var a = name;
            if (arguments.length > 1) {
                a = {};
                a[name] = value;
            }
            return this.eachElement(function() {
                for (var x in a) {
                    switch (x) {
                        case "class":
                            this.className = a[x] == null ? '' : a[x];
                            break;
                        case "for":
                            this.htmlFor = a[x] == null ? '' : a[x];
                            break;
                        default:
                    }
                    try{
                        this[a[x] === null ? 'removeAttribute' : 'setAttribute'](x, a[x], 2);
                    }catch(e){
                        throw new Exception(e + ':' + x );
                    }
                }
            });
        },
        removeAttr: function(name) {
            return this.setAttr(name, null);
        },
        getAttr: function(name) {
            return this.val(function() {
                return $.Selector.attr(this, name);
            });
        },
        attr: function() {
            var arg = arguments;
            return iS(arg[0]) && arg.length == 1 ? this.getAttr(arg[0]) : this.setAttr.apply(this, $.toArray(arg));
        },
        set: function(name, value) {
            var obj;
            if (arguments.length >= 2) {
                obj = {};
                obj[name] = value;
            } else {
                obj = name;
            }
            return this.eachElement(function() {
                for (var x in obj) {
                    this[x] = obj[x];
                }
                return true;
            });
        },
        get: function(p) {
            return this.k(0) ? this.k(0)[p] : undefined;
        }
    });

    // cache & data
    var DATA_DOM = {},
    DATA_ID = 0;
    extDOM({
        setData: function(data, value) {
            return this.each(function() {
                if (iF(this.setUserData)) {
                    this.setUserData(data, value, null);
                } else {
                    if (!DATA_DOM[data]) DATA_DOM[data] = [];
                    for (var i = 0; i < DATA_DOM[data].length; i++)
                        if (DATA_DOM[data][i][0] === this) return DATA_DOM[data][i][1] = value;
                    DATA_DOM[data].push([this, value]);
                }
            });
        },
        getData: function(data) {
            return this.val(function() {
                if (this.getUserData) return this.getUserData(data);
                if (!DATA_DOM[data]) return null;
                for (var i = 0; i < DATA_DOM[data].length; i++)
                    if (DATA_DOM[data][i][0] === this) return DATA_DOM[data][i][1];
                return null;
            });
        },
        data: function() {
            return this[arguments.length == 1 && iS(arguments[0]) ? 'getData' : 'setData'].apply(this, $.toArray(arguments));
        }
    });

    // search selector
    extDOM({
        find: function(expr) {
            var r = [];
            this.each(function() {
                r = r.concat($.toArray($.Selector(expr, this)));
            });
            return $(r);
        },
        parent: function(expr) {
            var r = [];
            return this.eachElement(function() {
                var node = this,
                i = 0;
                if (expr === undefined) expr = "*";
                while (node = Parent(node)) {
                    if (!isE(node)) return;
                    if (i++ === expr) {
                        r.push(node);
                        return false;
                    }
                    if ($.test(expr, node)) r.push(node);
                }
            }).reset(r);
        },
        child: function(expr) {
            var r = [];
            if (expr === undefined) expr = "*";
            return this.each(

                function() {
                    var i = 0;
                    var elems = this.tagName == "TABLE" ? this.rows : (this.children || this.childNodes);
                    Loop(elems, function(node) {
                        if (!isE(node)) return;
                        if (i++ === expr) {
                            r.push(node);
                            return false;
                        }
                        if ($.test(expr, node)) r.push(node);
                    });
                }).reset(r);
        },
        next: function(expr) {
            var r = [];
            if (expr === undefined) expr = "*";
            return this.eachElement(function() {
                var node = this,
                i = 0;
                while (node = $.Selector.next(node)) {
                    if (i++ === expr) {
                        r.push(node);
                        return false;
                    }
                    if ($.test(expr, node)) r.push(node);
                }
            }).reset(r);
        },
        pre: function(expr) {
            var r = [];
            if (expr === undefined) expr = "*";
            return this.eachElement(function() {
                var node = this,
                i = 0;
                while (node = $.Selector.pre(node)) {
                    if (i++ === expr) {
                        r.push(node);
                        return false;
                    }
                    if ($.test(expr, node)) r.push(node);
                }
            }).reset(r);
        },
        is: function(expr) {
            return this.size() > 0 ? $.test(expr, this.k(0)) : false;
        },
        filter: function(expr) {
            var r = [];
            return this.eachElement(function() {
                if ($.test(expr, this)) r.push(this);
            });
            return $(r);
        },
        // update action
        remove: function() {
            return this.eachElement(function() {
                if (Parent(this)) Parent(this).removeChild(this);
            });
        },
        empty: function(str) {
            return str === undefined ? this.eachElement(function() {
                while (this.firstChild)
                    this.removeChild(this.firstChild);
            }) : this.eachElement(function() {
                $(this).find(str).remove();
            });
        },
        wrap: function() {
            return this.val(function() {
                if (elem.outerHTML !== undefined) return elem.outerHTML;
                var e = elem.ownerDocument.createElement('html');
                e.appendChild(elem.cloneNode(true));
                return e.innerHTML;
            });
        },
        htm: function(txt) {
            return arguments.length == 0 ? this.val(function() {
                return this.innerHTML;
            }) : this.eachElement(function(i) {
                if (isInput(this)) this.value = txt;
                else $(this).empty().append(txt);
            });
        }
    });

    Each(["after", "append", "first", "before", "replace"], function(act) {
        $.DOM.prototype[act] = function(html) {
            return this.eachElement(function() {
                var e = this;
                if (isA(html)) {
                    Each(html, function(a) {
                        $(this)[act](a)
                    });
                } else if (isOwl(html)) {
                    html.eachElement(function() {
                        DOM.insert(act, this, e);
                    });
                } else {
                    DOM.insert(act, html, e);
                }
            });
        };
        $.DOM.prototype[act + "To"] = function(html) {
            var container = $(html);
            return this.eachElement(function() {
                container[act](this);
            });
        };
    });

    // css & style
    // I make a object contains some special properties
    $.crossCSS = {
        'float': {
            get: function(elem) {
                var style = Create(elem.tagName).style,
                value = '';
                Each(["float", "cssFloat", "styleFloat"], function(p) {
                    if (p in style) {
                        value = getCss(elem, p);
                        return false;
                    }
                });
                return value;
            },
            set: function(elem, value) {
                var style = Create(elem.tagName).style;
                Each(["float", "cssFloat", "styleFloat"], function(pro) {
                    if (pro in style) elem.style[pro] = value;
                });
            }
        },
        opacity: {
            get: function(elem) {
                var text = basicGetCss(elem, 'filter');
                return /opacity=(\d+)/i.test(text) ? (parseInt(RegExp.$1) || 0) / 100 : basicGetCss(elem, "opacity") == '' ? 1 : basicGetCss(
                    elem, "opacity");
            },
            set: function(elem, value) {
                value = Math.max(Math.min(value, 1), 0);
                if ('opacity' in elem.style) elem.style.opacity = value;
                else elem.style.filter = "alpha(opacity=" + parseFloat(value) * 100 + ")";
            }
        },
        margin: {
            get: function(elem) {
                return (basicGetCss(elem, "margin-top") || "0px") + " " + (basicGetCss(elem, "margin-right") || "0px") + " " + (basicGetCss(elem, "margin-bottom") || "0px") + " " + (basicGetCss(elem, "margin-left") || "0px");
            }
        },
        padding: {
            get: function(elem) {
                return (basicGetCss(elem, "padding-top") || "0px") + " " + (basicGetCss(elem, "padding-right") || "0px") + " " + (basicGetCss(elem, "padding-bottom") || "0px") + " " + (basicGetCss(elem, "padding-left") || "0px");
            }
        },
        'border-radius': {
            set: function(elem, value) {
                var effigy = Create(elem.tagName).style;
                Loop(["borderRadius", "OBorderRadius", "MozBorderRadius", "WebkitBorderRadius"], function(p) {
                    if (p in effigy) {
                        elem.style[p] = value;
                        return false;
                    }
                });
            }
        },
        'box-shadow': {
            set: function(elem, value) {
                var effigy = Create(elem.tagName).style;
                Loop(["boxShadow", "OBoxShadow", "MozBoxShadow", "WebkitBoxShadow"], function(p) {
                    if (p in effigy) {
                        elem.style[p] = value;
                        return false;
                    }
                });
            }
        },
        'text-shadow': {
            set: function(elem, value) {
                var effigy = Create(elem.tagName).style;
                Loop(["textShadow", "OTextShadow", "MozTextShadow", "WebkitTextShadow"], function(p) {
                    if (p in effigy) {
                        elem.style[p] = value;
                        return false;
                    }
                });
            }
        },
        rotate: {
            set: function(elem, value) {
                var effigy = Create(elem.tagName).style;
                Loop(["transform", "OTransform", "MozTransform", "WebkitTransform"], function(p) {
                    if (p in effigy) {
                        elem.style[p] = "rotate(" + value + "deg)";
                        return false;
                    }
                });
            },
            get: function(elem) {
                var effigy = Create(elem.tagName).style;
                Loop(["transform", "OTransform", "MozTransform", "WebkitTransform"], function(p) {
                    if (p in effigy) {
                        var value = basicGetCss(elem, Low(p.replace(/([A-Z])/g, "-$1")));
                        if (value.match(/deg\((\d+)\)/i)) {
                            return RegExp.$1;
                        }
                        return 0;
                    }
                    return 0;
                });
            }
        },
        'scroll-left': {
            set: function(elem, value) {
                elem.scrollLeft = value;
            },
            get: function(elem) {
                return elem.scrollLeft
            }
        },
        'scroll-top': {
            set: function(elem, value) {
                elem.scrollTop = value;
            },
            get: function(elem) {
                return elem.scrollTop
            }
        }
    };

    // fix position-x/y on firefox
    function getBgPosition(elem) {
        return (basicGetCss(elem, 'background-position').match(/([^\s]+)\s*?([^\s]+)/i)) ? {
            x: RegExp.$1,
            y: RegExp.$2
        } : {
            x: 0,
            y: 0
        };
    }

    function setBgPosition(elem, pos, value) {
        if (pos in elem.style) return elem.style['backgroundPosition' + upper(pos)] = value;
        var org = getBgPosition(elem);
        org[pos] = value;
        elem.style.backgroundPosition = org['x'] + ' ' + org['y'];
    }

    Each(['x', 'y'], function(pos) {
        $.crossCSS['background-position-' + pos] = {
            set: function(elem, value) {
                setBgPosition(elem, pos, value);
            },
            get: function(elem) {
                return getBgPosition(elem)[pos];
            }
        };
    });

    function basicGetCss(elem, pro) {
        if (elem.nodeType != 1) return;

        var cap = pro.replace(/-([a-z])/g, function(m, n) {
            return Up(n);
        });
        var lower = Low(pro.replace(/([A-Z])/g, "-$1"));
        var value = iS(elem.style[cap]) ? elem.style[cap] : '';
        if (value == '') {
            try {
                value = elem.currentStyle[cap];
            } catch (e) {
                try {
                    value = window.getComputedStyle(elem, null).getPropertyValue(lower);
                } catch (e) {
                    try {
                        value = document.defaultView.getComputedStyle(elem, null)[lower];
                    } catch (e) {}
                }
            }
        }
        // fix default some style properties
        value = iS(value) && value != "auto" && value != "inherit" ? value : '';
        if (/^(margin|padding|border)-(top|right|bottom|left)(-width)?$/i.test(lower) && value === '') value = "0px";
        return value;
    }

    // this function is may read all style-property
    // if property-need-read is multiple then result return always a
    // query-object
    // example: getCss("color,border,font-size")
    function getCss(elem, pro) {
        var result = {},
        first;
        Loop(
            iS(pro) ? pro.split(/\s*,\s*/i) : pro, function(name) {
                if ((name in $.crossCSS) && $.crossCSS[name].get) {
                    result[name] = (first = $.crossCSS[name].get(elem));
                } else {
                    result[Low(name.replace(/[A-Z]/g, "-$1"))] = (first = basicGetCss(
                        elem, name));
                }
            });
        if (iS(pro) && !/,/i.test(pro)) return first;
        return new Query(":", ";").Import(result);
    }

    function setCss(elem, css, value) {
        // ex: setCss(elem,'width','100px');
        if (value !== undefined) {
            var _css = {};
            _css[css] = value;
            css = _css;
        }

        var Q = new Query(':', ';').Import(css);

        for (var x in Q.Data) {
            var name = x.trim().replace(/-(\w)/gi, function(a, b) {
                return Up(b);
            }),
            value = (Q.Data[x] + '').trim().replace(/\!important$/i, '');

            // if value == '' equal remove style property;
            if (value == '') {
                try {
                    elem.style.removeProperty(name);
                } catch (e) {
                    elem.style[name] = '';
                }
                continue;
            }

            if (x in $.crossCSS && iF($.crossCSS[x].set)) $.crossCSS[x.trim()].set(elem, value);
            else elem.style[name] = value;

            // special style-peroperties
            if (name.match(/\w+UserTextSelect/i)) {
                elem.setAttribute('unselectable', value == 'none' ? 'on' : 'off');
            }
        }
    }

    var clt = document.createElement('div');
    var hasClass = clt.classList ? (function(e, s) {
        return e.classList.contains(s);
    }) : (function(e, s) {
        return e.className.trim().indexOf(s) > -1;
    });

    var removeClass = clt.classList ? (function(e, s) {
        return e.classList.remove(s);
    }) : (function(e, s) {
        var a = e.className.trim().split(/\s+/),
        st = [];
        for (var x in a) {
            if (s != a[x]) {
                st.push(a[x]);
            }
        }
        e.className = st.join(" ");
    });

    var addClass = clt.classList ? (function(e, s) {
        return e.classList.add(s);
    }) : (function(e, s) {
        if (hasClass(e, s)) return;
        e.className += (e.className == '' ? '' : " ") + s;
    });
    clt = null;

    // extend for $.DOM
    extDOM({
        style: function() {
            return this.css.apply(this, toA(arguments));
        },
        css: function(obj) {
            return (iS(obj) && !/:/.test(obj)) ? this.val(function() {
                return getCss(this, obj);
            }) : this.eachElement(function() {
                return setCss(this, obj);
            });
        },
        hasClass: function(s) {
            return this.val(function() {
                var a = s.split(/\s+/i);
                for (var x in a)
                    if (!hasClass(this, a[x])) return false;
                return true;
            });
        },
        addClass: function(s) {
            return this.eachElement(function() {
                var a = s.split(/\s+/i);
                for (var x in a)
                    addClass(this, a[x]);
            });
        },
        removeClass: function(s) {
            return this.eachElement(function() {
                var a = s.split(/\s+/i);
                for (var x in a)
                    removeClass(this, a[x]);
            });
        },
        toggleClass: function(s, i) {
            var l = arguments.length;
            return this.eachElement(function() {
                i = l > 1 ? i : !hasClass(this, s);
                (i ? addClass : removeClass)(this, s);
            });
        }
    });

    // animate manager
    // note: owl's animate is different with other classic library
    // Owl create a css array for every effect like (
    // ["width:1px","width:2px",...] )
    // Then run setInterval set style for element from header until footer of
    // array
    // So "speed" represent for time and transformation
    var ANIMATE = {},
    NON_ANIMATE = {},
    IA = 0,
    IB = 0;

    // raf detect support
    var requestAnimationFrame = window.requestAnimationFrame || window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame;
    /*
     * var cancelAnimationFrame = window.cancelAnimationFrame ||
     * window.webkitCancelAnimationFrame || window.mozCancelAnimationFrame ||
     * window.oCancelAnimationFrame || window.msCancelAnimationFrame;
     */

    // Animate is a object
    var Animate = $.makeClass(

        function(elem, css_begin, css, css_end, maxCount, options) {
            /*
         * css={[start,end,unit,easing],..} note: for a nonabled
         * animate css properties easing must be return end
         */
            this.elem = elem;

            // cs store
            this.css_begin = Extend({}, css_begin || {});
            this.css = Extend({}, css);
            this.css_end = Extend({}, css_end || {});

            this.options = Extend({
                data: {},
                // store some thing
                start: function() {},
                callback: function() {},
                step: function() {}
            }, options || {});

            this.id = IA++;

            ANIMATE[this.id] = this;

            // remove this of window.setInterval
            var obj = this,
            fc = this.fx;
            this.fx = function() {
                fc.call(obj);
            };

            // total step animation
            this.maxCount = maxCount;

            // duration should keep
            this.duration = maxCount * TIME_INTERVAL;

            // current step
            this.count = 0;

            this.TIME = TIME_INTERVAL;

            this.running = false;

            this.start();
        }, {
            start: function() {
                this.isFinish = false;
                if (requestAnimationFrame) {
                    this.fx();
                    var a = this;
                    requestAnimationFrame(function() {
                        if (!a.isFinish) {
                            a.start();
                        }
                    });
                } else {
                    this.fi = window.setInterval(this.fx, this.TIME);
                }
            },
            stop: function() {
                if (requestAnimationFrame) {
                    this.isFinish = true;
                } else {
                    clearInterval(this.fi);
                }
            },
            remove: function(last, call) {
                this.busy();
                if (requestAnimationFrame) {
                    this.isFinish = true;
                } else {
                    clearInterval(this.fi);
                }

                if (last) {
                    for (var x in this.css) {
                        $(this.elem).css(x + ":" + this.css[x][1]);
                    }
                }

                if (call) {
                    this.options.callback(this.elem);
                }
                delete ANIMATE[this.id];
            },
            busy: function() {
                this._busy = true;
            },
            free: function() {
                this._busy = false;
            },
            fx: function() {
                var e = this,
                elem = e.elem;
                if (e._busy) return;

                e.busy();

                if (!elem) return e.remove();

                // if start css
                if (e.count == 0) {
                    $(elem).css(e.css_begin);
                    e.options.start.call(elem, e.count, e.maxCount);
                }

                if (e.count < e.maxCount) {
                    e.count++;

                    // run step callback
                    e.options.step.call(elem, e.count, e.maxCount);

                    // intergate update css
                    for (var x in e.css) {
                        var start = e.css[x][0],
                        end = e.css[x][1],
                        easing = e.css[x][2],
                        duration = e.css[x][3];

                        if (x in Animate.css) {

                            setCss(elem, x, Animate.css[x](start, end, easing, duration, e.count));

                        } else if (end.match(/^(-?\d+(?:\.\d+)?)(\D*)?$/i)) {
                            end = parseFloat(RegExp.$1);
                            unit = RegExp.$2 || '';
                            var value, mat = start.match(new RegExp('(-?\\d+(?:\\.\\d+)?)' + unit, 'i'));
                            if (e.count == e.maxCount || !mat) {
                                value = end;
                            } else {
                                start = parseFloat(mat[1]);
                                value = easing(e.count * TIME_INTERVAL, start, end - start, duration);
                                value = (unit.match(/^px$/i) || (x == 'z-index')) ? Math.round(value) : value.toFixed(2);
                            }
                            setCss(elem, x, value + unit);
                        } else {
                            setCss(elem, x, end);
                        }
                    }
                    e.free();
                } else {
                    e.remove();
                    $(elem).css(e.css_end);
                    e.options.callback.call(elem);
                }
            }
        });

    // support for style animation
    Animate.css = {};
    Each(
        ["backgroundColor", "borderColor", "borderLeftColor", "borderRightColor", "borderTopColor", "borderBottomColor", "outlineColor"], function(name) {
            Animate.css[name] = function(start, end, easing, duration, i) {
                var a = getRGB(start),
                b = getRGB(end);

                // value should convert
                var r = easing(i * TIME_INTERVAL, a.R, b.R - a.R, duration),
                g = easing(
                    i * TIME_INTERVAL, a.B, b.B - a.B, duration);
                b = easing(i * TIME_INTERVAL, a.G, b.G - a.G, duration);

                return "rgb(" + Math.min(255, Math.max(0, Math.floor(r))) + "," + Math.min(255, Math.max(0, Math.floor(g))) + "," + Math.min(255, Math.max(0, Math.floor(b))) + ")";
            };
        });

    Animate.css.opacity = function(start, end, easing, duration, i) {
        start = parseFloat(start);
        if (isNaN(start)) start = 1;
        var value = easing(i * TIME_INTERVAL, start, end - start, duration);
        return Math.round(value * 100) / 100;
    };

    // Apply animate
    extDOM({
        animate: function(css_to, settings) {
            if (arguments.length == 0) return this;
            if (arguments.length == 1) {
                settings = arguments[0];
                css_to = arguments[0] || {};
            }

            // Convert standar options
            var options = Extend({
                start: function() {},
                step: function() {},
                callback: function() {},
                duration: 200,
                easing: 'swing'
            }, settings || {});

            // Store to speed up js
            var ware = new Query(':', ';').Import(css_to).Data,
            duration = options.duration;

            // A new animate allways append
            return this.callback(function() {
                var w = Extend({}, ware),
                elem = this,
                k = Math.round(duration / TIME_INTERVAL),
                css = {},
                css_begin = {},
                css_end = {};

                for (var p in w) {
                    var x = p.trim(),
                    start, end, easing = $.Easing[options[x + '-easing']] || $.Easing[options.easing],
                    set_start = false,
                    set_end = false;

                    // css properties set for start value
                    if (x.match(/^_(.*)$/i)) {
                        x = RegExp.$1;
                        set_start = true;
                    }

                    // css properties set for end value
                    if (x.match(/^\$(.*)$/i)) {
                        x = RegExp.$1;
                        set_end = true;
                    }

                    start = $(elem).css(x) + '';

                    // fix box-model and default-value of css
                    if (x == "width" && start == '') start = $.fixModel(this).W + "px";
                    if (x == "height" && start == '') start = $.fixModel(this).H + "px";
                    if (x == "opacity" && x == '') start = 1;
                    // assign end value
                    end = w[p].toString().trim();

                    if (end.match(/^([+\-*\/%])=(\d+(?:\.\d+)?)$/i)) {
                        var j = RegExp.$1,
                        t = parseFloat(RegExp.$2);
                        end = start.replace(/-?\d+(\.\d+)?/gi, function(n) {
                            switch (j) {
                                case "+":
                                    return Math.round(t + parseFloat(n));
                                case "-":
                                    return Math.round(-t + parseFloat(n));
                                case "*":
                                    return Math.round(t * parseFloat(n));
                                case "/":
                                    return Math.round(parseFloat(n) / t);
                                case "%":
                                    return Math.round(t * parseFloat(n) / 100);
                                default:
                                    return n;
                            }
                        });
                    }

                    if (set_start) {
                        css_begin[x] = end;
                        continue;
                    }

                    if (set_end) {
                        css_end[x] = end;
                        continue;
                    }

                    css[x] = [start, end, easing, duration];

                }
                new Animate(this, css_begin, css, css_end, k, options);
            });
        }
    });

    $.Easing = {
        linear: function(t, b, c, d) {
            return b + (t / d) * c;
        },
        swing: function(t, b, c, d) {
            return b + (0.5 - Math.cos((t / d) * Math.PI) / 2) * c;
        }
    };

    extDOM({
        stop: function(_f, _l) {
            return this.eachElement(function() {
                for (var x in ANIMATE) {
                    var ef = ANIMATE[x];
                    if (ef && ef.elem == this) {
                        if (arguments.length == 0) {
                            ef.stop();
                        } else {
                            ef.remove(_f, _l);
                        }
                    }
                }
            });
        },
        back: function(id) {
            return this.eachElement(function() {
                for (var x in ANIMATE) {
                    var ef = ANIMATE[x];
                    if (ef && ef.elem == this) {
                        ef.start();
                    }
                }
            });
        },
        callback: function(fn, id) {
            return this.eachElement(function() {
                var check = false;
                for (var x in ANIMATE) {
                    var ef = ANIMATE[x];
                    if (ef.elem === this && (id === undefined || id === ef.options.id)) {
                        var g = toF(ef.options.callback);
                        ef.options.callback = function() {
                            g.call(this);
                            $(this).callback(fn);
                        };
                        check = true;
                    }
                }
                if (!check) fn.call(this);
            });
        },
        effect: function(id) {
            var elem = this.nodes[0];
            if (!elem) return null;
            for (var x in ANIMATE) {
                var a = ANIMATE[x];
                if (elem === a.elem && (id === undefined || id === a.options.id)) return a;
            }
            return null;
        },
        visible: function() {
            return getCss(this.nodes[0], 'display') != 'none';
        },
        show: function(speed, options) {
            options = options || {};
            if (iF(options)) {
                var fn = options;
                options = {
                    callback: fn
                };
            }

            options.id = '_show';
            options.duration = speed || 0;
            options.easing = options.easing || 'swing';

            return this.eachElement(function() {

                var oldCss = {},
                css = {},
                initCss = {},
                effect = $(
                    this).effect("_hide");

                // cross a display element
                if ($(this).visible() && !effect) return true;

                if (effect) {
                    // stop animate _show and get last css from callback
                    oldCss = effect.options.oldCss;
                    var width = oldCss.$width,
                    height = oldCss.$height,
                    opacity = oldCss.$opacity,
                    offset;

                    $(this).stop(false, false);

                    if (!width.match(/^\d+(?:.\d+)?px$/i)) {
                        offset = $.fixModel(this);
                        css.width = offset.W + 'px';
                    }

                    if (!height.match(/^\d+(?:.\d+)?px$/i)) {
                        css.height = (offset || $.fixModel(this)).H + 'px';
                    }

                    css = {
                        height: height,
                        width: width,
                        opacity: opacity || 1
                    };
                } else {
                    var width = getCss(this, 'width'),
                    height = getCss(
                        this, 'height'),
                    opacity = getCss(this, 'opacity'),
                    overflow = getCss(this, 'overflow'),
                    display = normalView(this, 'display', 'none', 'block'),
                    offset;

                    oldCss = {
                        $width: width,
                        $height: height,
                        $opacity: opacity,
                        $overflow: overflow,
                        $display: display
                    };

                    // end css
                    css = Extend(css, {
                        height: height,
                        width: width,
                        opacity: opacity || 1
                    });

                    if (!width.match(/^\d+(?:.\d+)?px$/i)) {
                        offset = $.fixModel(this);
                        css.width = offset.W + 'px';
                    }

                    if (!height.match(/^\d+(?:.\d+)?px$/i)) {
                        css.height = (offset || $.fixModel(this)).H + 'px';
                    }

                    initCss = {
                        width: '1px',
                        height: '1px',
                        opacity: 0,
                        overflow: 'hidden'
                    };
                }

                // return some fixed value of style
                css = Extend(css, oldCss);

                // direction
                if (!effect) Loop(['width', 'height'], function(p) {
                    if (options[p] === false) {
                        delete css[p];
                        delete initCss[p];
                    }
                });

                // need start small if none
                $(this).css(initCss);

                css._display = oldCss.$display;

                // stograte oldCss
                options.oldCss = oldCss;

                $(this).animate(css, options);

            });
        },
        hide: function(speed, options) {

            options = options || {};
            if (iF(options)) {
                var fn = options;
                options = {
                    callback: fn
                };
            }

            options.id = '_hide';
            options.duration = speed || 0;
            options.easing = options.easing || 'swing';

            return this.eachElement(function() {
                if ($(this).effect("_hide")) return true;

                var oldCss = {},
                effect = $(this).effect("_show"),
                css = {
                    height: '1px',
                    width: '1px',
                    opacity: '0',
                    _overflow: 'hidden'
                };

                // cross a display element
                if (!$(this).visible() && !effect) return true;

                if (effect) {
                    // stop animate _show and get last css from callback
                    oldCss = effect.options.oldCss;
                    $(this).stop(false, false);
                } else {
                    var width = getCss(this, 'width'),
                    height = getCss(
                        this, 'height'),
                    opacity = getCss(this, 'opacity'),
                    overflow = getCss(this, 'overflow'),
                    display = getCss(this, 'display');

                    oldCss = {
                        $width: width,
                        $height: height,
                        $opacity: opacity,
                        $overflow: overflow,
                        $display: display
                    };

                    var offset;

                    if (!width.match(/^\d+(?:.\d+)?px$/i)) {
                        offset = $.fixModel(this);
                        css._width = offset.W + 'px';
                    }

                    if (!height.match(/^\d+(?:.\d+)?px$/i)) {
                        css._height = (offset || $.fixModel(this)).H + 'px';
                    }

                }

                css = Extend(css, oldCss);

                // direction
                Loop(['width', 'height'], function(p) {
                    if (options[p] === false) {
                        delete css[p];
                        delete css['$' + p];
                    }
                });

                css.$display = 'none';

                // stograte oldCss
                options.oldCss = oldCss;

                $(this).animate(css, options);

            });
        },
        toggle: function(show, hide) {
            if (isN(show)) show = [show];
            if (isN(hide)) hide = [hide];

            return this.eachElement(function() {
                var ef = $(this).effect("_hide"),
                none = getCss(this, "display") == "none";
                if (ef || none) {
                    $(this).show.apply($(this), show);
                } else {
                    $(this).hide.apply($(this), hide);
                }
            });
        },
        fadeOut: function(speed, options) {
            options = options || {};
            options.width = false;
            options.height = false;
            return $(this).hide(speed, options)
        },
        fadeIn: function(speed, options) {
            options = options || {};
            options.width = false;
            options.height = false;
            return $(this).show(speed, options)
        },
        slideUp: function(speed, options) {
            options = options || {};
            options.width = false;
            return $(this).hide(speed, options)
        },
        slideDown: function(speed, options) {
            options = options || {};
            options.width = false;
            return $(this).show(speed, options)
        }

    });

    /*
     * Color
     */
    var COLOR = {
        black: [0, 0, 0],
        silver: [192, 192, 192],
        gray: [128, 128, 128],
        white: [255, 255, 255],
        maroon: [128, 0, 0],
        red: [255, 0, 0],
        purple: [128, 0, 128],
        fuchsia: [255, 0, 255],
        green: [0, 128, 0],
        lime: [0, 255, 0],
        olive: [128, 128, 0],
        yellow: [255, 255, 0],
        navy: [0, 0, 128],
        blue: [0, 0, 255],
        teal: [0, 128, 128],
        aqua: [0, 255, 255],
        transparent: [255, 255, 255]
    };
    // almost browser automaticly convert color-value to rgb
    function convertColor(name) {
        var div = document.createElement("div");
        try {
            div.style.color = name;
        } catch (e) {
            return name;
        }
        return div.style.color;
    };
    if (!convertColor("#fffccc").match(/^rgb\(.+\)$/i)) {
        convert_color = function(name) {
            var m;
            if ( !! (m = name.match(/^#([0-9a-f])([0-9a-f])([0-9a-f])$/i))) return {
                R: parseInt("0x" + m[1] + m[1]),
                G: parseInt("0x" + m[2] + m[2]),
                B: parseInt("0x" + m[3] + m[3])
            };
            if ( !! (m = name.match(/^#([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/i))) return {
                R: parseInt("0x" + m[1]),
                G: parseInt("0x" + m[3]),
                B: parseInt("0x" + m[2])
            };
            return name;
        };
    }

    function getRGB(color) {
        color = convertColor(color);
        var m;
        if ( !! (m = color.replace(/\s/gi, '')).match(/^rgb\((\d+)(%)?,(\d+)(%)?,(\d+)(%)?\)$/i)) {

            return {
                R: parseInt(m[1]) * (m[2] == '%' ? 2.55 : 1),
                G: parseInt(m[3]) * (m[4] == '%' ? 2.55 : 1),
                B: parseInt(m[5]) * (m[6] == '%' ? 2.55 : 1)
            };
        }
        color = Low(color);

        // if color is basic name
        if (COLOR[color]) {
            var n = COLOR[color];
            return {
                R: n[0],
                B: n[1],
                G: n[2]
            };
        }

        return color;
    }

    $.fixModel = function(elem) {
        var r = Real(elem),
        dim = diff(elem);
        return {
            W: Math.max(r.offsetWidth - dim.h, 0),
            H: Math.max(r.offsetHeight - dim.v, 0)
        };
    };

    // get minus dim from boxmodel not support
    function diff(elem, p) {
        var div = Create("div"),
        djv = Create("div");
        $("body").append(div);
        div.style.cssText = $(elem).css(
            (!p ? "border-top-width,border-left-width," + "border-right-width,border-bottom-width," + "border-right-style,border-left-style,border-top-style,border-bottom-style," + "border-right-color,border-left-color,border-top-color,border-bottom-color," : '') + "padding-left,padding-right,padding-top,padding-bottom").Compact().Export();
        div.appendChild(djv);
        // div on IE must has content for offsetHeight !=0
        djv.innerHTML = "<a>.</a>";
        var r = {
            h: div.offsetWidth - djv.offsetWidth,
            v: div.offsetHeight - djv.offsetHeight
        };
        $(div).remove();
        return r;
    }

    function normalView(elem, css, banned, fix) {
        var div = $.Create(elem.tagName);
        document.body.appendChild(div);
        var value = basicGetCss(div, css);
        document.body.removeChild(div);
        return value == banned ? fix : value;
    }
    // get dimension & position even if element don't
    function Real(elem) {
        var noneElems = [],
        node = elem;
        if (elem.offsetHeight == 0 && elem.offsetWidth == 0) while (node && node.tagName != "BODY") {
            if (basicGetCss(node, "display") == "none") {
                node.style.display = normalView(node, 'display', 'none', 'block');
                noneElems.push(node);
            }
            node = Parent(node);
        }
        var result = {
            top: elem.offsetTop,
            left: elem.offsetLeft,
            offsetWidth: elem.offsetWidth,
            offsetHeight: elem.offsetHeight,
            clientWidth: elem.clientWidth,
            clientHeight: elem.clientHeight,
            scrollWidth: elem.scrollWidth,
            scrollHeight: elem.scrollHeight,
            scrollTop: elem.scrollTop,
            scrollLeft: elem.scrollLeft
        };
        $(noneElems).css("display:none");
        return result;
    }
    $.Real = Real;
    // offset cross standar web page
    $.offset = function(elem) {
        var a = [],
        div = Create("div"),
        node = elem;
        // smart test cross body's standar
        $(div).appendTo("body").css("display:block;position:absolute;top:0;left:0;border:none;margin:0");
        var left = -div.offsetLeft,
        top = -div.offsetTop;
        $(div).remove();
        while (node) {
            var r = Real(node);
            if (!r || node === document.documentElement || node === document.body) break;
            left += r.left;
            top += r.top;
            node = node.offsetParent;
        }
        // fix firefox
        Loop([document.body, document.documentElement], function(w) {
            if (w.offsetTop < 0) top += (w === document.documentElement ? 1 : -2) * w.offsetTop;
            if (w.offsetLeft < 0) left += (w === document.documentElement ? 1 : -2) * w.offsetLeft;
        });
        // ok now 100% exactly
        return {
            left: left,
            top: top
        };
    };

    /* methods for dimmension &position of elem */
    $.getSize = function(elem, name, css, unit) {
        css = css || [];
        var size = elem[name] || Real(elem)[name];
        for (var i; i < css.length; i++) {
            size += (unit || 1) * parseInt($(elem).css(css[i])) || 0;
        }
        return size;
    };


    extDOM({
        offset: function() {
            return this.val(function() {
                return $.offset(this);
            });
        },
        left: function() {
            return this.val(function() {
                return $.offset(this).left;
            });
        },
        top: function() {
            return this.val(function() {
                return $.offset(this).top;
            });
        },
        width: function() {
            return this.val(function() {
                if (isWin(this)) return this.innerWidth || screen.width;
                return $.getSize(this, 'offsetWidth');
            });
        },
        height: function() {
            return this.val(function() {
                if (isWin(this)) return this.innerHeight || screen.height;
                return $.getSize(this, 'offsetHeight');
            });
        },
        outerWidth: function() {
            return this.val(function() {
                if (isWin(this)) return this.outerWidth || screen.width;
                return $.getSize(this, 'scrollWidth');
            });
        },
        outerHeight: function() {
            return this.val(function() {
                if (isWin(this)) return this.outerHeight || screen.height;
                return $.getSize(this, 'scrollHeight');
            });
        },
        innerWidth: function() {
            return this.val(function() {
                if (isWin(this)) return this.innerWidth || screen.width;
                return $.getSize(this, 'scrollWidth', ["padding-left", "padding-right", "border-right-width", "border-left-width"], -1);
            });
        },
        innerHeight: function() {
            return this.val(function() {
                if (isWin(this)) return this.innerHeight || screen.height;
                return $.getSize(this, 'scrollHeight', ["padding-top", "padding-bottom", "border-top-width", "border-bottom-width"], -1);
            });
        }
    });

    /*---------------------------------------------------------------------------*/
    // event
    /*---------------------------------------------------------------------------*/

    var EVENT_BASIC_LIST = [],
    EVENT_SPECIAL_LIST = [],
    EVENT_COUNT = 0;
    $.Event = {
        add: function(elem, type, fn, id, is_special) {
            var callback = function(e) {
                // not fix if callback doesn't use event
                if (!fn.toString().split("{")[0].replace(/\s+/i, '').match(/\(\)/i)) {
                    var event = $.Event.fix(e || window.event, elem);
                    fn.call(elem, event);
                } else {
                    fn.call(elem);
                }
            },
            i = EVENT_COUNT++;
            if (type in EVENT_SPECIAL) {
                EVENT_SPECIAL_LIST[i] = [elem, type, callback, fn, id];
                Each(EVENT_SPECIAL[type], function(_f, name) {
                    var back = (EVENT_SPECIAL_LIST[i][2][name] = _f(fn));
                    $.Event.add(elem, name, back, id, true);
                });
                return elem;
            }
            EVENT_BASIC_LIST[i] = [elem, type, callback, fn, id, is_special || false];
            if (elem.addEventListener) {
                elem.addEventListener(type, EVENT_BASIC_LIST[i][2], false);
            } else if (elem.attachEvent) {
                elem.attachEvent("on" + type, EVENT_BASIC_LIST[i][2]);
            }
            return elem;
        },
        remove: function(elem, e_name, fn_or_id, is_special) {
            is_special = is_special || false;
            var fd = fn_or_id === undefined ? "*" : fn_or_id;

            if (e_name in EVENT_SPECIAL) {
                Each(
                    EVENT_SPECIAL_LIST, function(e, i) {
                        //cross if not match elem
                        if (e[0] !== elem && elem != "*") return;

                        //cross if not match name
                        if (e_name !== e[1] && e_name != "*") return;

                        //cross if not match function
                        if (iF(fd) && fd != e[3]) return;

                        //cross if not id
                        if (iS(fd) && e[4] != fd && fd != "*") return;

                        var list_f = e[2];

                        for (var x in list_f)
                            $.Event.remove(e[0], x, e[2][x], fd, true);

                        delete EVENT_SPECIAL_LIST[i];

                    });
                return;
            }
            // find from cache basic event
            Each(
                EVENT_BASIC_LIST, function(e, i) {
                    //cross if not match elem
                    if (e[0] !== elem && elem != "*") return;

                    //cross if not match name
                    if (e_name !== e[1] && e_name != "*") return;
                    //cross if not match function
                    if (iF(fd) && fd != e[3]) return;

                    //cross if not id
                    if (iS(fd) && e[4] != fd && fd != "*") return;
                    if (e[5] == is_special) {

                        if (e[0].removeEventListener) {
                            e[0].removeEventListener(e_name, e[2], false);
                        } else if (elem.detachEvent) {
                            e[0].detachEvent("on" + e_name, e[2]);
                        }
                        delete EVENT_BASIC_LIST[i];
                    }
                });
        },
        trigger: function(elem, type) {
            Each(EVENT_BASIC_LIST, function(event) {
                if (event[0] == elem && Low(type) == event[1]) event[2].call(elem, window.event || event, true);
            });
        },
        fix: function(event, elem) {
            if (!event) return undefined;

            // extend event with much properties also may make code slower
            var E = {};
            // store for original event
            E.originalEvent = event;
            // type of event , continue edit in some special event
            E.type = event.type;
            E.ctrlKey = event.ctrlKey;
            E.shiftKey = event.shiftKey;

            // on ie return undefined for keypress
            E.which = event.keyCode || event.which || event.charCode;
            E.keyCode = E.which;
            E.charCode = E.which;
            // target
            E.target = event.target || event.srcElement;
            if (E.target && E.target.nodeType == 3) E.target = Parent(E.target);
            // only for mouse event
            E.relatedTarget = event.relatedTarget !== undefined ? event.relatedTarget : (event.fromElement != event.target ? event.toElement : event.fromElement); // always is element
            E.currentTarget = elem;
            E.stopPropagation = function() {
                if (event.stopPropagation) event.stopPropagation();
                event.cancelBubble = true;
            };
            // extend for mousewheel
            // opera always is bad
            if (event.type === "mousewheel" || event.type == "DOMMouseScroll") {
                E.delta = event.detail || event.wheelDelta || 0;
                E.detail = E.delta;
                var d = 'wheelDelta' in event ? -1 : 1;
                if (E.delta != 0) E.delta = d * E.delta / Math.abs(E.delta);
            }
            E.preventDefault = function() {
                if (event.preventDefault) event.preventDefault();
                event.returnValue = false;
            };

            // require elem is element , != W
            if (!isE(elem) && (elem.nodeType != 9)) return E;

            // fix firefox
            if (!elem.ownerDocument && elem.nodeType != 9) return E;

            var doc = elem.ownerDocument || elem,
            html = doc.documentElement || {},
            body = doc.body || {};

            if ('pageX' in event) {
                E.pageX = event.pageX;
                E.pageY = event.pageY;
            } else if ('clientX' in event) {
                function get(dim) {
                    var value = basicGetCss(body, dim);
                    return $.unit.border[value] || parseInt(value) || 0;
                }
                E.pageX = event.clientX + (body.scrollLeft || html.scrollLeft) - get("border-left-width");
                E.pageY = event.clientY + (body.scrollTop || html.scrollTop) - get("border-top-width");
            }

            // client mouse deppend on body margin ->fix
            E.clientX = E.pageX - (body.scrollLeft || html.scrollLeft || 0);
            E.clientY = E.pageY - (body.scrollTop || html.scrollTop || 0);
            if (elem.nodeType != 1) return E;
            var off = $.offset(elem);

            // contain border and padding
            E.offsetX = E.pageX - off.left;
            E.offsetY = E.pageY - off.top;
            E.offsetBottom = elem.offsetHeight - E.offsetY;
            E.offsetRight = elem.offsetWidth - E.offsetX;
            return E;
        }
    };

    //add some key map
    Extend($.Event, {
        KEY_BACKSPACE: 8,
        KEY_TAB: 9,
        KEY_ENTER: 13,
        KEY_ESC: 27,
        KEY_LEFT: 37,
        KEY_UP: 38,
        KEY_RIGHT: 39,
        KEY_DOWN: 40,
        KEY_DELETE: 46,
        KEY_HOME: 36,
        KEY_END: 35,
        KEY_PAGEUP: 33,
        KEY_PAGEDOWN: 34,
        KEY_INSERT: 45
    });

    var EVENT_BASIC = ['scroll', 'resize', 'click', 'dblclick', 'menucontext', 'focus', 'blur', 'select', 'submit', 'reset', 'change', 'mouseover', 'mouseout', 'mousemove', 'dragover', 'dragenter', 'dragleave', 'mousedown', 'mouseup', 'drag', 'dragend', 'dragstart', 'selectstart', 'drop', 'keyup', 'keydown', 'keypress', 'load', 'unload', 'error', 'abort'],
    EVENT_SPECIAL = {};

    // fix mouseenter mouseleave
    if (document.documentElement && ((document.documentElement.onmouseenter === null) || iF(document.documentElement.onmouseenter))) {
        EVENT_BASIC.push("mouseenter");
        EVENT_BASIC.push("mouseleave");
    } else {
        Extend(EVENT_SPECIAL, {
            mouseenter: {
                mouseover: function(fn) {
                    return function(e) {
                        e.currentType = "mouseover";
                        e.type = "mouseenter";
                        if (!$.Contains(e.relatedTarget, this) && this !== e.relatedTarget) fn.call(this, e);
                    };
                }
            },
            mouseleave: {
                mouseout: function(fn) {
                    return function(e) {
                        e.currentType = "mouseout";
                        e.type = "mouseleave";
                        if (!$.Contains(e.relatedTarget, this) && (this != e.relatedTarget)) {
                            fn.call(this, e);
                        }
                    };
                }
            }
        });
    }

    // fix mousewheel
    if ((window.onmousewheel !== undefined) || (document.onmousewheel !== undefined)) {
        EVENT_BASIC.push("mousewheel");
    } else {
        EVENT_SPECIAL.mousewheel = {
            DOMMouseScroll: function(fn) {
                return function(e) {
                    e.type = "mousewheel";
                    return fn.call(this, e);
                };
            }
        };
    }

    (function() {
        var list = EVENT_BASIC.slice(0);
        for (var x in EVENT_SPECIAL) {
            list.push(x);
        }

        Loop(list, function(event) {
            $.DOM.prototype['on' + event.replace(/^./, function(m) {
                return Up(m);
            })] = function(fn, id) {
                var n = id !== undefined && id !== "*" ? event + ":" + id : event;
                return this.each(function() {
                    $(this).addEvent(n, fn);
                });
            };
            $.DOM.prototype['remove' + event.replace(/^./, function(m) {
                return Up(m);
            })] = function(fn_or_id) {
                var n = iS(fn_or_id) && fn_or_id !== "*" ? event + ":" + fn_or_id : event;
                return this.each(function() {
                    $(this).removeEvent(n, fn_or_id);
                });
            };
        });
    })();

    extDOM({
        addEvent: function(name, fn) {
            if (!iS(name)) {
                for (var x in name)
                    this.addEvent(x, name[x], fn);
                return this;
                i
            }
            return this.each(function() {
                var s = name.split(/\s+/i);
                for (var i = 0; i < s.length; i++) {
                    if (s[i].match(/([a-z0-9]+):([^:]+)/i)) {
                        $.Event.add(this, RegExp.$1, fn, RegExp.$2);
                    } else {
                        $.Event.add(this, s[i], fn);
                    }
                }
            });
        },
        removeEvent: function(name, fn) {
            if (!iS(name)) {
                for (var x in name)
                    this.removeEvent(x, name[x], fn);
                return this;
            }

            return this.each(function() {
                var s = name.split(/\s+/i);
                for (var i = 0; i < s.length; i++) {
                    if (s[i].match(/([a-z0-9]+):([^:]+)/i)) {
                        $.Event.remove(this, RegExp.$1, RegExp.$2);
                    } else {
                        $.Event.remove(this, s[i], fn);
                    }
                }
            });
        },
        event: function() {
            return this.addEvent.call(this, $.toArray(arguments));
        },
        trigger: function(type) {
            return this.each(function() {
                $.Event.trigger(this, type);
            });
        },
        // fix for cache with image
        ready: function(fn) {
            return this.eachElement(function() {
                if (this.tagName == "IMG") {
                    if (is_loaded(this)) {
                        fn.call(this, true);
                        return;
                    } else {
                        $(this).addEvent({
                            load: function() {
                                fn.call(this, true);
                            },
                            error: function() {
                                fn.call(this, false);
                            }
                        });
                    }
                } else {
                    var iImg = this.getElementsByTagName('img'),
                    self = this;
                    if (iImg.length === 0) fn.call(this);
                    for (var i = 0, l = iImg.length; i < l; i++) {
                        if (iImg[i] && !is_loaded(iImg[i])) {
                            $(iImg[i]).ready(function() {
                                $(this).ready(function() {
                                    fn.call(self);
                                });
                            });
                            return;
                        }
                    }
                    fn.call(self);
                }
            });
        }
    });

    // test complete of img
    // ie return undefined for img just create and return true or false fo img error
    // opera also, other browser return true for img completely load and false
    // for img error
    var is_loaded = function(e) {
        return e.tagName == "IMG" && e.complete === true;
    };

    (function() {
        if (!document.body || !document.body.appendChild) {
            var fc = arguments.callee;
            return setTimeout(function() {
                fc();
            }, 5);
        }
        var img = document.createElement('IMG');
        document.body.appendChild(img);
        img.src = 'http://';
        // IE treat with a error img is complete === false
        if (img.complete === false && 'readyState' in img) is_loaded = function(e) {
            return e.tagName == "IMG" && (e.complete === true || e.complete === false);
        };

        document.body.removeChild(img);
    })();

    // text translate
    // get all textnode of elem
    function allText(element) {
        var text = [];
        Loop(element.childNodes, function(node) {
            if (node.nodeType == 3) text.push(node);
            if (node.nodeType == 1) text = text.concat(allText(node));
        });
        return text;
    } // replace global
    function Replace(pat, join, text, count) {
        if (!isN(count)) {
            return text.replace(pat, join);
        } else if (count > 0) {
            try {
                pat.global = false;
            } catch (e) {}
            var c = 0,
            m, s = text,
            cc = '';
            while (m = s.match(pat)) {
                cc += m[0].replace(pat, join);
                s = s.substr(m.index + m[0].length);
                if (c++ == count) {
                    cc += s;
                    break;
                }
            }
        }
    }

    extDOM({
        text: function(text) {
            if (iS(text)) return this.empty().append(document.createTextNode(text));
            return this.val(function() {
                return this.textContent || this.innerText || this.text || '';
            });
        },
        translate: function(patten, fn) {
            return this.replaceText(patten, fn, false, count);
        },
        replaceText: function(patten, fn, html, count) {
            var trans = [];
            if (isA(patten)) trans = patten;
            else trans[0] = [patten, fn, count];
            return this.eachElement(function() {
                Loop(allText(this), function(node) {
                    if (html) {
                        var text = node.nodeValue.replace(/\</gi, "&lt;").replace(/>/gi, "&gt;"),
                        span = Create("span");
                        Parent(node).replaceChild(span, node);
                        for (var j = 0; j < trans.length; j++)
                            text = Replace(trans[j][0], trans[j][1], text, trans[j][2]);
                        $(span).replace(text);
                    } else {
                        for (var j = 0; j < trans.length; j++)
                            node.nodeValue = Replace(trans[j][0], trans[j][1], node.nodeValue, trans[j][2]);
                    }
                });
            });
        },
        /* Selection */
        getPointer: function() {
            return this.val(function() {
                var doc = this.ownerDocument || this,
                elem = this;
                if (this.selectionStart) {
                    return {
                        start: elem.selectionStart,
                        end: elem.selectionEnd
                    };
                }

                if (!doc.selection) return {
                    start: 0,
                    end: 0
                };

                var r = doc.selection.createRange();
                var c = r.duplicate();
                c.moveToElementText(elem);
                c.setEndPoint("EndToEnd", r);
                return {
                    start: c.text.length - r.text.length,
                    end: c.text.length
                };
            });
        },
        // slect text in element
        select: function(win) {
            return this.eachElement(function() {
                win = win || window;
                var d = this.ownerDocument || this;
                if (d.body.createTextRange) {
                    var r = d.body.createTextRange();
                    r.moveToElementText(this);
                    r.select();
                } else if (win.getSelection) {
                    var s = win.getSelection();

                    try {
                        s.setBaseAndExtent(this, 0, this, 1);
                    } catch (e) {
                        var r = d.createRange();
                        r.selectNodeContents(this);
                        s.removeAllRanges();
                        s.addRange(r);
                    }
                }
                return false;
            });
        },
        // slect in textarea
        selectText: function(start, end) {
            return arguments.length > 0 ? this.eachElement(function() {
                if (end < start) start = end;
                if (end == -1) end = (this.value || $(this).text()).length;
                if (this.setSelectionRange) {
                    this.focus();
                    this.setSelectionRange(start, end);
                } else if (this.createTextRange) {
                    var range = elem.createTextRange();
                    range.collapse(true);
                    range.moveEnd('character', end);
                    range.moveStart('character', start);
                    range.select();
                }
                return false;
            }) : this.val(function() {
                var d = this.ownerDocument || this;
                if (d.selection && d.selection.createRange) {
                    return d.selection.createRange().text;
                } else if ('getSelection' in this) {
                    return this.getSelection() + '';
                }
                return '';
            });
        },
        /*
         * Return a object of request in form
         */
        query: function() {
            return this.val(function() {
                var data = {};

                if (this.tagName == 'FORM') {
                    for (var i = 0; i < this.elements.length; i++) {
                        var a = this.elements[i];
                        if (a.disabled) {
                            continue;
                        }

                        if (a.type == "submit") {
                            if (a.is_clicked) {
                                data[a.name] = a.value;
                            }
                            continue;
                        }

                        if (((a.type == "checkbox" || a.type == "radio") && a.checked) || (a.type != "checkbox" && a.type != "radio")) {
                            if (a.name.match(/^(.*)\[\]$/i)) {
                                // element get value in array
                                var j = 0,
                                name = RegExp.$1;
                                while (j > -1) {
                                    if (!((name + "[" + j + "]") in data)) {
                                        data[name + "[" + j + "]"] = a.value;
                                        break;
                                    }
                                    j++;
                                }
                            } else {
                                data[a.name] = a.value;
                            }
                        }
                    }
                }
                // keep here for the future
                return data;
            });
        },
        queryString: function() {
            return new $.Query('=', '&').Import(this.query()).Export();
        },
        submitAjax: function(options) {
            return this.val(function() {
                if (this.tagName != 'FORM') return null;

                options = Extend({
                    init: function() {},
                    filter: function(v) {
                        return v;
                    }
                }, options || {});

                options.init.call(this);

                // get info
                var url = $(this).getAttr('action'),
                method = $(this).getAttr('method') || 'get',
                data = $(this).query();

                for (var x in data)
                    data[x] = options.filter.call(this, data[x], x);

                options.type = method;
                options.data = $.Extend(data, options.data || {});
                return $.Ajax(url, options);
            });
        },
        onSubmitAjax: function(options) {
            var elem = this,
            $submit = $(this).find("input[type=submit],button[type=submit]");
            $(this).onSubmit(function(event) {
                $(this).submitAjax(options);
                event.preventDefault();
                event.stopPropagation();
            }, 'owl-ajax-submit');

            $submit.onClick(function() {
                $submit.set('is_clicked', false);
                this.is_clicked = true;
            }, 'owl-track-clicked');
        }
    });

    /*---------------------------------------------------------------------------*/
    // Core Function
    /*---------------------------------------------------------------------------*/
    // basic
    function iS(s) {
        return typeof s === "string";
    }

    function iF(f) {
        try {
            return (typeof f === "function") && ('call' in f);
        } catch (e) {
            return false;
        }
    }

    function isA(a) {
        return a && (a.constructor == Array);
    }

    function isE(e) {
        return e && (e.nodeType == 1);
    }

    function isDOM(e) {
        return e && (isWin(e) || (e.nodeType && !! (/^(?:1|9|11)$/).test(e.nodeType.toString())));
    }

    function isInput(e) {
        return e && (/^TEXTAREA|INPUT$/).test(e.tagName + '');
    }

    function isOwl(a) {
        return a && (a.constructor == $.DOM);
    }

    function isN(n) {
        return (n != NaN) && typeof n === "number";
    }

    function indexOf(e, arr) {
        try {
            return arr.indexOf(e);
        } catch (e) {
            for (var i = 0; i < arr.length; i++)
                if (arr[i] === e) return i;
            return -1;
        }
    }

    function Up(s) {
        return s.toString().toUpperCase();
    }

    function Low(s) {
        return s.toString().toLowerCase();
    }

    function F() {}

    function toF(f) {
        return iF(f) ? f : new Function;
    }

    function nocache(url) {
        return url += (url.indexOf("?") > -1 ? "&" : "?") + new Date().getTime();
    }

    function fixUTF8(s) {
        return s.toString().replace(/./g, function(m) {
            return (m.charCodeAt(0) > 127) ? ("&#" + m.charCodeAt(0) + ";") : m;
        });
    }

    function Create(tag, doc) {
        return tag ? (doc || document).createElement(tag) : (doc || document).createDocumentFragment();
    }

    function Parent(e) {
        return e.parentNode;
    }
    $.safe = function(s) {
        return s.replace(/(\[|\]|\(|\)|\<|\?|\{|\}|\*|\.|\||\+|\$|\^)/gi, "\\$1");
    };
    $.matchArray = function(expr, str) {
        try {
            expr.global = false;
        } catch (e) {}
        var arr = [],
        mat;
        while (mat = str.match(expr)) {
            str = RegExp.rightContext;
            arr.push(mat);
        }
        return arr;
    };

    // loop & each
    function Loop(arr, fn) {
        for (var i = 0; i < arr.length; i++) {
            if (fn.call(arr, arr[i], i) === false) break;
        }
    }

    function Each(obj, fn) {
        for (var x in obj)
            if (fn.call(obj, obj[x], x) === false) break;
    }

    function Extend(obj, pros, filter) {
        filter = filter ||
        function(a) {
            return pros[a];
        };
        if (pros === undefined) {
            obj = $;
            pros = obj;
        }
        for (var x in pros)
            obj[x] = filter(x);
        return obj;
    }

    /*
     * Query object
     */
    var Query = $.makeClass(

        function(start, end) {
            this.S = start;
            this.F = end;
            this.Data = {};
        }, {
            Import: function(str) {
                if (iS(str)) {
                    var p = $.safe(this.S),
                    q = $.safe(this.F),
                    g1 = "(?:[^" + p + "]" + ")",
                    g2 = "(?:[^" + q + "]" + ")",
                    r = new RegExp("(" + g1 + "+)" + p + "(" + g2 + "*)(?:" + q + "|$)", "i"),
                    fo = $.matchArray(r, str);
                    for (var i = 0; i < fo.length; i++)
                        this.Data['' + fo[i][1]] = fo[i][2];
                } else {
                    for (var x in str)
                        this.Data[x] = str[x];
                }
                return this;
            },
            Compact: function() {
                for (var x in this.Data)
                    if (this.Data[x] === undefined || this.Data[x] === null || this.Data[x] === '') delete this.Data[x];
                return this;
            },
            Export: function(fn) {
                var str = [];
                fn = fn || (function(s) {
                    return s;
                });
                for (var x in this.Data)
                    str.push(x + this.S + fn(this.Data[x], x));
                return str.join(this.F);
            }
        });

    /* Ajax */
    var _AJAX = [],
    XHR = $.makeClass(function() {
        try {
            this.request = new XMLHttpRequest();
        } catch (e) {
            try {
                this.request = new ActiveXObject('Msxml2.XMLHTTP');
            } catch (e) {
                try {
                    this.request = new ActiveXObject('Microsoft.XMLHTTP');
                } catch (e) {
                    this.request = false;
                }
            }
        }
        this.index = _AJAX.push(this.request) - 1;
        this.active = true;
    }, {
        getRequest: function() {
            return this.request;
        },
        stop: function() {
            _AJAX[this.index] = null;
            this.active = false;
            if (this.request) {
                this.request.abort();
            }
        }
    });

    $.Ajax = function(url, opts) {
        if (iF(opts)) opts = {
            success: opts
        };
        // clean and setup request
        opts.data = opts.data || {};
        // accept element
        opts.url = url;
        // cache for url - ability very small for wrong
        if (opts.cache === false) url = nocache(url);
        // now we create a new request and return it
        var ajax = new XHR(),
        xhr = ajax.getRequest();

        ajax.options = opts;

        if (xhr === false) return null;
        // readychange
        xhr.onreadystatechange = function() {
            if (!_AJAX[ajax.index]) {
                return;
            }

            toF(opts.process).call(xhr);
            switch (parseInt(xhr.readyState)) {
                case 0:
                    break;
                case 1:
                    toF(opts.create).call(xhr);
                    break;
                case 2:
                    toF(opts.sended).call(xhr);
                    break;
                case 3:
                    toF(opts.loaded).call(xhr);
                    break;
                case 4:
                    ajax.active = false;
                    toF(opts.complete).call(xhr);
                    switch (parseInt(xhr.status)) {
                        case 200:
                            toF(opts.success).call(xhr, xhr.responseText);
                            break;
                        default:
                            toF(opts.error).call(xhr, xhr.statusText);
                    }
                    break;
                default:
                    return;
            }
        };
        var async = opts.async || true,
        data = new Query("=", "&").Import(opts.data || {}).Export(function(s) {
            return (encodeURIComponent || fixUTF8)(s);
        });
        // case method
        try {
            if (opts.type && (Low(opts.type) == "post")) {
                if (opts.proxy) url += opts.proxy + (encodeURIComponent || escape)(url);
                xhr.open("POST", url, async);
                xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
                xhr.send(data);
            } else {
                if (data) url += (url.match(/\?/i) ? '&' : '?') + data;
                if (opts.proxy) url += opts.proxy + (encodeURIComponent || escape)(url);
                xhr.open("GET", url, true);
                xhr.send(null);
            }
        } catch (e) {}
        return ajax;
    };

    /*  COOKIE */
    $.Cookie = {
        set: function(name, value, time) {
            time = (time || 0) * 1000 * 60 * 60 * 24;
            var expires = new Date((new Date()).getTime() + time).toGMTString();
            document.cookie = name + "=" + escapse(value) + ";expires=" + expires
        },
        remove: function(name) {
            document.cookie = name + "=;expires=Thus,01-Jan-1970 00:00:00 GMT"
        },
        get: function(name) {
            var data = new Query("=", ";").Import((document.cookie || '').replace(/;$/i, '').replace(/;\s+/gi, ";"));
            if (name in data.Data) return unescape(data.Data[name]);
            return null
        }
    };

    /* Event for dom ready */
    var DOMCONTENT_LOADED = false,
    FN_READY = [];

    function onReady(fn) {
        if (iF(fn)) FN_READY.push(fn);
        while (DOMCONTENT_LOADED && FN_READY.length)
            FN_READY.shift().call(window, $);
    }

    (function() {
        var ready = function(e) {
            if (DOMCONTENT_LOADED !== true) {
                DOMCONTENT_LOADED = true;
                onReady();
            }
        };

        // stop function if loaded
        if (DOMCONTENT_LOADED !== true) {
            try {
                $(document).addEvent("DOMContentLoaded", ready, false);
            } catch (e) {
                $(window).onLoad(ready);
            }

            // enought to work for chrome ,ie ,safari
            if (/loaded|complete/i.test(document.readyState) || (document.body && /loaded|complete/i.test(document.body.readyState))) {
                ready();
            } else if (document.readyState !== undefined) {
                setTimeout(arguments.callee, 0);
            }
        }
    })();

    /*---------------------------------------------------------------------------*/
    // test some features of browser
    /*---------------------------------------------------------------------------*/
    $.unit = {
        border: {
            thin: 1,
            medium: 3,
            thick: 5
        },
        tag: {}
    };

    // copy some ui functions
    $.Extend = Extend;
    $.isArray = isA;
    $.Create = Create;
    $.Loop = Loop;
    $.Each = Each;
    $.Query = Query;
    $.extendDOM = extDOM;
    $.Contains = $.Selector.contains;
    $._$ = window.$;
    window.$ = Owl;

    /*
     * Auto move call from Owl Object if dom have not been ready yet
     * */
    var STORE_ACTION = [],
    QueueFn = function(_str) {
        var str = _str;
        this.id = STORE_ACTION.push(function() {
            return $(str);
        }) - 1;
        return this;
    };

    Extend(QueueFn.prototype, new $.DOM([]), function(act) {
        return function() {
            var fc = STORE_ACTION[this.id],
            params = $.toArray(arguments);
            STORE_ACTION[this.id] = function() {
                var obj = fc();
                return obj[act].apply(obj, params);
            };
            return this;
        };
    });

    $(function() {
        Loop(STORE_ACTION, function(act) {
            act();
        });
    });

    $(window).onUnload(function() {
        ANIMATE = [];
    });
})(window, document);