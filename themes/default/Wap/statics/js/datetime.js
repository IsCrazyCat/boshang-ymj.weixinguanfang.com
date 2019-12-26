/*jslint eqeq: true, plusplus: true, undef: true, sloppy: true, vars: true, forin: true */
/*jslint eqeq: true, plusplus: true, undef: true, sloppy: true, vars: true, forin: true, nomen: true */
/*!
 * Mobiscroll v2.8.3
 * http://mobiscroll.com
 *
 * Copyright 2010-2013, Acid Media
 * Licensed under the MIT license.
 *
 */
(function ($) {

    function testProps(props) {
        var i;
        for (i in props) {
            if (mod[props[i]] !== undefined) {
                return true;
            }
        }
        return false;
    }

    function testPrefix() {
        var prefixes = ['Webkit', 'Moz', 'O', 'ms'],
            p;

        for (p in prefixes) {
            if (testProps([prefixes[p] + 'Transform'])) {
                return '-' + prefixes[p].toLowerCase() + '-';
            }
        }
        return '';
    }

    function getCoord(e, c) {
        var org = e.originalEvent,
            ct = e.changedTouches;
        return ct || (org && org.changedTouches) ? (org ? org.changedTouches[0]['page' + c] : ct[0]['page' + c]) : e['page' + c];
    }

    function init(that, options, args) {
        var ret = that;

        // Init
        if (typeof options === 'object') {
            return that.each(function () {
                if (!this.id) {
                    this.id = 'mobiscroll' + (++id);
                }
                if (instances[this.id]) {
                    instances[this.id].destroy();
                }
                new $.mobiscroll.classes[options.component || 'Scroller'](this, options);
            });
        }

        // Method call
        if (typeof options === 'string') {
            that.each(function () {
                var r,
                    inst = instances[this.id];

                if (inst && inst[options]) {
                    r = inst[options].apply(this, Array.prototype.slice.call(args, 1));
                    if (r !== undefined) {
                        ret = r;
                        return false;
                    }
                }
            });
        }

        return ret;
    }

    var id = +new Date,
        instances = {},
        extend = $.extend,
        mod = document.createElement('modernizr').style,
        has3d = testProps(['perspectiveProperty', 'WebkitPerspective', 'MozPerspective', 'OPerspective', 'msPerspective']),
        prefix = testPrefix(),
        pr = prefix.replace(/^\-/, '').replace(/\-$/, '').replace('moz', 'Moz');

    $.fn.mobiscroll = function (method) {
        extend(this, $.mobiscroll.components);
        return init(this, method, arguments);
    };

    $.mobiscroll = $.mobiscroll || {
        util: {
            prefix: prefix,
            jsPrefix: pr,
            has3d: has3d,
            getCoord: getCoord
        },
        presets: {},
        themes: {},
        i18n: {},
        instances: instances,
        classes: {},
        components: {},
        presetShort: function (name, c) {
            this.components[name] = function (s) {
                return init(this, extend(s, { component: c, preset: name }), arguments);
            };
        }
    };

    $.scroller = $.scroller || $.mobiscroll;
    $.fn.scroller = $.fn.scroller || $.fn.mobiscroll;

})(jQuery);
/*jslint eqeq: true, plusplus: true, undef: true, sloppy: true, vars: true, forin: true, nomen: true */
(function ($) {

    $.mobiscroll.classes.Scroller = function (elem, settings) {
        var m,
            hi,
            v,
            dw,
            persp,
            overlay,
            ww, // Window width
            wh, // Window height
            mw, // Modal width
            mh, // Modal height
            lock,
            anim,
            theme,
            lang,
            click,
            hasButtons,
            scrollable,
            moved,
            start,
            startTime,
            stop,
            p,
            min,
            max,
            modal,
            target,
            index,
            timer,
            readOnly,
            preventChange,
            preventPos,
            wndw,
            doc,
            buttons,
            btn,
            that = this,
            e = elem,
            elm = $(e),
            s = extend({}, defaults),
            pres = {},
            iv = {},
            pos = {},
            pixels = {},
            wheels = [],
            elmList = [],
            input = elm.is('input'),
            visible = false,
            onStart = function (e) {
                // Scroll start
                if (testTouch(e) && !move && !click && !btn && !isReadOnly(this)) {
                    // Prevent touch highlight
                    e.preventDefault();

                    move = true;
                    scrollable = s.mode != 'clickpick';
                    target = $('.dw-ul', this);
                    setGlobals(target);
                    moved = iv[index] !== undefined; // Don't allow tap, if still moving
                    p = moved ? getCurrentPosition(target) : pos[index];
                    start = getCoord(e, 'Y');
                    startTime = new Date();
                    stop = start;
                    scroll(target, index, p, 0.001);

                    if (scrollable) {
                        target.closest('.dwwl').addClass('dwa');
                    }

                    $(document).on(MOVE_EVENT, onMove).on(END_EVENT, onEnd);
                }
            },
            onMove = function (e) {
                if (scrollable) {
                    // Prevent scroll
                    e.preventDefault();
                    e.stopPropagation();
                    stop = getCoord(e, 'Y');
                    scroll(target, index, constrain(p + (start - stop) / hi, min - 1, max + 1));
                }
                if (start !== stop) {
                    moved = true;
                }
            },
            onEnd = function (e) {
                var time = new Date() - startTime,
                    val = constrain(p + (start - stop) / hi, min - 1, max + 1),
                    speed,
                    dist,
                    tindex,
                    ttop = target.offset().top;

                if (time < 300) {
                    speed = (stop - start) / time;
                    dist = (speed * speed) / s.speedUnit;
                    if (stop - start < 0) {
                        dist = -dist;
                    }
                } else {
                    dist = stop - start;
                }

                tindex = Math.round(p - dist / hi);

                if (!dist && !moved) { // this is a "tap"
                    var idx = Math.floor((stop - ttop) / hi),
                        li = $($('.dw-li', target)[idx]),
                        hl = scrollable;
                    if (event('onValueTap', [li]) !== false) {
                        tindex = idx;
                    } else {
                        hl = true;
                    }

                    if (hl) {
                        li.addClass('dw-hl'); // Highlight
                        setTimeout(function () {
                            li.removeClass('dw-hl');
                        }, 200);
                    }
                }

                if (scrollable) {
                    calc(target, tindex, 0, true, Math.round(val));
                }

                move = false;
                target = null;

                $(document).off(MOVE_EVENT, onMove).off(END_EVENT, onEnd);
            },
            onBtnStart = function (e) {
                if (btn) {
                    btn.removeClass('dwb-a');
                }
                btn = $(this);
                $(document).on(END_EVENT, onBtnEnd);
                // Active button
                if (!btn.hasClass('dwb-d') && !btn.hasClass('dwb-nhl')) {
                    btn.addClass('dwb-a');
                }
                // +/- buttons
                if (btn.hasClass('dwwb')) {
                    if (testTouch(e)) {
                        step(e, btn.closest('.dwwl'), btn.hasClass('dwwbp') ? plus : minus);
                    }
                }
            },
            onBtnEnd = function (e) {
                if (click) {
                    clearInterval(timer);
                    click = false;
                }
                if (btn) {
                    btn.removeClass('dwb-a');
                    btn = null;
                }
                $(document).off(END_EVENT, onBtnEnd);
            },
            onKeyDown = function (e) {
                if (e.keyCode == 38) { // up
                    step(e, $(this), minus);
                } else if (e.keyCode == 40) { // down
                    step(e, $(this), plus);
                }
            },
            onKeyUp = function (e) {
                if (click) {
                    clearInterval(timer);
                    click = false;
                }
            },
            onScroll = function (e) {
                if (!isReadOnly(this)) {
                    e.preventDefault();
                    e = e.originalEvent || e;
                    var delta = e.wheelDelta ? (e.wheelDelta / 120) : (e.detail ? (-e.detail / 3) : 0),
                        t = $('.dw-ul', this);

                    setGlobals(t);
                    calc(t, Math.round(pos[index] - delta), delta < 0 ? 1 : 2);
                }
            };

        // Private functions

        function step(e, w, func) {
            e.stopPropagation();
            e.preventDefault();
            if (!click && !isReadOnly(w) && !w.hasClass('dwa')) {
                click = true;
                // + Button
                var t = w.find('.dw-ul');

                setGlobals(t);
                clearInterval(timer);
                timer = setInterval(function () { func(t); }, s.delay);
                func(t);
            }
        }

        function isReadOnly(wh) {
            if ($.isArray(s.readonly)) {
                var i = $('.dwwl', dw).index(wh);
                return s.readonly[i];
            }
            return s.readonly;
        }

        function generateWheelItems(i) {
            var html = '<div class="dw-bf">',
                ww = wheels[i],
                w = ww.values ? ww : convert(ww),
                l = 1,
                labels = w.labels || [],
                values = w.values,
                keys = w.keys || values;

            $.each(values, function (j, v) {
                if (l % 20 == 0) {
                    html += '</div><div class="dw-bf">';
                }
                html += '<div role="option" aria-selected="false" class="dw-li dw-v" data-val="' + keys[j] + '"' + (labels[j] ? ' aria-label="' + labels[j] + '"' : '') + ' style="height:' + hi + 'px;line-height:' + hi + 'px;"><div class="dw-i">' + v + '</div></div>';
                l++;
            });

            html += '</div>';
            return html;
        }

        function setGlobals(t) {
            min = $('.dw-li', t).index($('.dw-v', t).eq(0));
            max = $('.dw-li', t).index($('.dw-v', t).eq(-1));
            index = $('.dw-ul', dw).index(t);
        }

        function formatHeader(v) {
            var t = s.headerText;
            return t ? (typeof t === 'function' ? t.call(e, v) : t.replace(/\{value\}/i, v)) : '';
        }

        function read() {
            that.temp = that.values ? that.values.slice(0) : s.parseValue(elm.val() || '', that);
            setVal();
        }

        function getCurrentPosition(t) {
            var style = window.getComputedStyle ? getComputedStyle(t[0]) : t[0].style,
                matrix,
                px;

            if (has3d) {
                $.each(['t', 'webkitT', 'MozT', 'OT', 'msT'], function (i, v) {
                    if (style[v + 'ransform'] !== undefined) {
                        matrix = style[v + 'ransform'];
                        return false;
                    }
                });
                matrix = matrix.split(')')[0].split(', ');
                px = matrix[13] || matrix[5];
            } else {
                px = style.top.replace('px', '');
            }

            return Math.round(m - (px / hi));
        }

        function ready(t, i) {
            clearTimeout(iv[i]);
            delete iv[i];
            t.closest('.dwwl').removeClass('dwa');
        }

        function scroll(t, index, val, time, active) {
            var px = (m - val) * hi,
                style = t[0].style,
                i;

            if (px == pixels[index] && iv[index]) {
                return;
            }

            if (time && px != pixels[index]) {
                // Trigger animation start event
                event('onAnimStart', [dw, index, time]);
            }

            pixels[index] = px;

            style[pr + 'Transition'] = 'all ' + (time ? time.toFixed(3) : 0) + 's ease-out';

            if (has3d) {
                style[pr + 'Transform'] = 'translate3d(0,' + px + 'px,0)';
            } else {
                style.top = px + 'px';
            }

            if (iv[index]) {
                ready(t, index);
            }

            if (time && active) {
                t.closest('.dwwl').addClass('dwa');
                iv[index] = setTimeout(function () {
                    ready(t, index);
                }, time * 1000);
            }

            pos[index] = val;
        }

        function getValid(val, t, dir) {
            var cell = $('.dw-li[data-val="' + val + '"]', t),
                cells = $('.dw-li', t),
                v = cells.index(cell),
                l = cells.length;

            // Scroll to a valid cell
            if (!cell.hasClass('dw-v')) {
                var cell1 = cell,
                    cell2 = cell,
                    dist1 = 0,
                    dist2 = 0;

                while (v - dist1 >= 0 && !cell1.hasClass('dw-v')) {
                    dist1++;
                    cell1 = cells.eq(v - dist1);
                }

                while (v + dist2 < l && !cell2.hasClass('dw-v')) {
                    dist2++;
                    cell2 = cells.eq(v + dist2);
                }

                // If we have direction (+/- or mouse wheel), the distance does not count
                if (((dist2 < dist1 && dist2 && dir !== 2) || !dist1 || (v - dist1 < 0) || dir == 1) && cell2.hasClass('dw-v')) {
                    cell = cell2;
                    v = v + dist2;
                } else {
                    cell = cell1;
                    v = v - dist1;
                }
            }

            return {
                cell: cell,
                v: v,
                val: cell.attr('data-val')
            };
        }

        function scrollToPos(time, index, manual, dir, active) {
            // Call validation event
            if (event('validate', [dw, index, time, dir]) !== false) {
                // Set scrollers to position
                $('.dw-ul', dw).each(function (i) {
                    var t = $(this),
                        sc = i == index || index === undefined,
                        res = getValid(that.temp[i], t, dir),
                        cell = res.cell;

                    if (!(cell.hasClass('dw-sel')) || sc) {
                        // Set valid value
                        that.temp[i] = res.val;

                        if (!s.multiple) {
                            $('.dw-sel', t).removeAttr('aria-selected');
                            cell.attr('aria-selected', 'true');
                        }

                        // Add selected class to cell
                        $('.dw-sel', t).removeClass('dw-sel');
                        cell.addClass('dw-sel');

                        // Scroll to position
                        scroll(t, i, res.v, sc ? time : 0.1, sc ? active : false);
                    }
                });

                // Reformat value if validation changed something
                v = s.formatResult(that.temp);
                if (that.live) {
                    setVal(manual, 0, true);
                }

                $('.dwv', dw).html(formatHeader(v));

                if (manual) {
                    event('onChange', [v]);
                }
            }

        }

        function event(name, args) {
            var ret;
            args.push(that);
            $.each([theme, pres, settings], function (i, v) {
                if (v && v[name]) { // Call preset event
                    ret = v[name].apply(e, args);
                }
            });
            return ret;
        }

        function calc(t, val, dir, anim, orig) {
            val = constrain(val, min, max);

            var cell = $('.dw-li', t).eq(val),
                o = orig === undefined ? val : orig,
                active = orig !== undefined,
                idx = index,
                time = anim ? (val == o ? 0.1 : Math.abs((val - o) * s.timeUnit)) : 0;

            // Set selected scroller value
            that.temp[idx] = cell.attr('data-val');

            scroll(t, idx, val, time, active);

            setTimeout(function () {
                // Validate
                scrollToPos(time, idx, true, dir, active);
            }, 10);
        }

        function plus(t) {
            var val = pos[index] + 1;
            calc(t, val > max ? min : val, 1, true);
        }

        function minus(t) {
            var val = pos[index] - 1;
            calc(t, val < min ? max : val, 2, true);
        }

        function setVal(fill, time, noscroll, temp, manual) {
            if (visible && !noscroll) {
                scrollToPos(time, undefined, manual);
            }

            v = s.formatResult(that.temp);

            if (!temp) {
                that.values = that.temp.slice(0);
                that.val = v;
            }

            if (fill && input) {
                preventChange = true;
                elm.val(v).change();
            }
        }

        function attachPosition(ev, checkLock) {
            var debounce;
            wndw.on(ev, function (e) {
                clearTimeout(debounce);
                debounce = setTimeout(function () {
                    if ((lock && checkLock) || !checkLock) {
                        that.position(!checkLock);
                    }
                }, 200);
            });
        }

        // Public functions

        /**
        * Positions the scroller on the screen.
        */
        that.position = function (check) {

            if (!modal || preventPos || (ww === persp.width() && wh === (wndw[0].innerHeight || wndw.innerHeight()) && check) || (event('onPosition', [dw]) === false)) {
                return;
            }

            var w,
                l,
                t,
                aw, // anchor width
                ah, // anchor height
                ap, // anchor position
                at, // anchor top
                al, // anchor left
                arr, // arrow
                arrw, // arrow width
                arrl, // arrow left
                dh,
                scroll,
                totalw = 0,
                minw = 0,
                sl = wndw.scrollLeft(),
                st = wndw.scrollTop(),
                wr = $('.dwwr', dw),
                d = $('.dw', dw),
                css = {},
                anchor = s.anchor === undefined ? elm : s.anchor;

            ww = persp.width(); // To get the width without scrollbar
            wh = wndw[0].innerHeight || wndw.innerHeight();

            if (/modal|bubble/.test(s.display)) {
                $('.dwc', dw).each(function () {
                    w = $(this).outerWidth(true);
                    totalw += w;
                    minw = (w > minw) ? w : minw;
                });
                w = totalw > ww ? minw : totalw;
                wr.width(w).css('white-space', totalw > ww ? '' : 'nowrap');
            }

            mw = d.outerWidth();
            mh = d.outerHeight(true);
            lock = mh <= wh && mw <= ww;

            that.scrollLock = lock;

            if (s.display == 'modal') {
                l = (ww - mw) / 2;
                t = st + (wh - mh) / 2;
            } else if (s.display == 'bubble') {
                scroll = true;
                arr = $('.dw-arrw-i', dw);
                ap = anchor.offset();
                at = Math.abs($(s.context).offset().top - ap.top);
                al = Math.abs($(s.context).offset().left - ap.left);

                // horizontal positioning
                aw = anchor.outerWidth();
                ah = anchor.outerHeight();
                l = constrain(al - (d.outerWidth(true) - aw) / 2 - sl, 3, ww - mw - 3);

                // vertical positioning
                t = at - mh; // above the input
                if ((t < st) || (at > st + wh)) { // if doesn't fit above or the input is out of the screen
                    d.removeClass('dw-bubble-top').addClass('dw-bubble-bottom');
                    t = at + ah; // below the input
                } else {
                    d.removeClass('dw-bubble-bottom').addClass('dw-bubble-top');
                }

                // Calculate Arrow position
                arrw = arr.outerWidth();
                arrl = constrain(al + aw / 2 - (l + (mw - arrw) / 2) - sl, 0, arrw);

                // Limit Arrow position
                $('.dw-arr', dw).css({ left: arrl });
            } else {
                css.width = '100%';
                if (s.display == 'top') {
                    t = st;
                } else if (s.display == 'bottom') {
                    t = st + wh - mh;
                }
            }

            css.top = t < 0 ? 0 : t;
            css.left = l;
            d.css(css);

            // If top + modal height > doc height, increase doc height
            persp.height(0);
            dh = Math.max(t + mh, s.context == 'body' ? $(document).height() : doc.scrollHeight);
            persp.css({ height: dh, left: sl });

            // Scroll needed
            if (scroll && ((t + mh > st + wh) || (at > st + wh))) {
                preventPos = true;
                setTimeout(function () { preventPos = false; }, 300);
                wndw.scrollTop(Math.min(t + mh - wh, dh - wh));
            }
        };

        /**
        * Enables the scroller and the associated input.
        */
        that.enable = function () {
            s.disabled = false;
            if (input) {
                elm.prop('disabled', false);
            }
        };

        /**
        * Disables the scroller and the associated input.
        */
        that.disable = function () {
            s.disabled = true;
            if (input) {
                elm.prop('disabled', true);
            }
        };

        /**
        * Gets the selected wheel values, formats it, and set the value of the scroller instance.
        * If input parameter is true, populates the associated input element.
        * @param {Array} values Wheel values.
        * @param {Boolean} [fill=false] Also set the value of the associated input element.
        * @param {Number} [time=0] Animation time
        * @param {Boolean} [temp=false] If true, then only set the temporary value.(only scroll there but not set the value)
        */
        that.setValue = function (values, fill, time, temp) {
            that.temp = $.isArray(values) ? values.slice(0) : s.parseValue.call(e, values + '', that);
            setVal(fill, time, false, temp, fill);
        };

        /**
        * Return the selected wheel values.
        */
        that.getValue = function () {
            return that.values;
        };

        /**
        * Return selected values, if in multiselect mode.
        */
        that.getValues = function () {
            var ret = [],
                i;

            for (i in that._selectedValues) {
                ret.push(that._selectedValues[i]);
            }
            return ret;
        };

        /**
        * Changes the values of a wheel, and scrolls to the correct position
        * @param {Array} idx Indexes of the wheels to change.
        * @param {Number} [time=0] Animation time when scrolling to the selected value on the new wheel.
        * @param {Boolean} [manual=false] Indicates that the change was triggered by the user or from code.
        */
        that.changeWheel = function (idx, time, manual) {
            if (dw) {
                var i = 0,
                    nr = idx.length;

                $.each(s.wheels, function (j, wg) {
                    $.each(wg, function (k, w) {
                        if ($.inArray(i, idx) > -1) {
                            wheels[i] = w;
                            $('.dw-ul', dw).eq(i).html(generateWheelItems(i));
                            nr--;
                            if (!nr) {
                                that.position();
                                scrollToPos(time, undefined, manual);
                                return false;
                            }
                        }
                        i++;
                    });
                    if (!nr) {
                        return false;
                    }
                });
            }
        };

        /**
        * Return true if the scroller is currently visible.
        */
        that.isVisible = function () {
            return visible;
        };

        /**
        * Attach tap event to the given element.
        */
        that.tap = function (el, handler) {
            var startX,
                startY;

            if (s.tap) {
                el.on('touchstart.dw mousedown.dw', function (e) {
                    e.preventDefault();
                    startX = getCoord(e, 'X');
                    startY = getCoord(e, 'Y');
                }).on('touchend.dw', function (e) {
                    // If movement is less than 20px, fire the click event handler
                    if (Math.abs(getCoord(e, 'X') - startX) < 20 && Math.abs(getCoord(e, 'Y') - startY) < 20) {
                        handler.call(this, e);
                    }
                    setTap();
                });
            }

            el.on('click.dw', function (e) {
                if (!tap) {
                    // If handler was not called on touchend, call it on click;
                    handler.call(this, e);
                }
                e.preventDefault();
            });

        };

        /**
        * Shows the scroller instance.
        * @param {Boolean} prevAnim - Prevent animation if true
        */
        that.show = function (prevAnim) {
            if (s.disabled || visible) {
                return;
            }

            if (s.display == 'top') {
                anim = 'slidedown';
            }

            if (s.display == 'bottom') {
                anim = 'slideup';
            }

            // Parse value from input
            read();

            event('onBeforeShow', []);

            // Create wheels
            var lbl,
                l = 0,
                mAnim = '';

            if (anim && !prevAnim) {
                mAnim = 'dw-' + anim + ' dw-in';
            }

            // Create wheels containers
            var html = '<div role="dialog" class="' + s.theme + ' dw-' + s.display + (prefix ? ' dw' + prefix.replace(/\-$/, '') : '') + (hasButtons ? '' : ' dw-nobtn') + '">' + (!modal ? '<div class="dw dwbg dwi">' : '<div class="dw-persp"><div class="dwo"></div><div class="dw dwbg ' + mAnim + '"><div class="dw-arrw"><div class="dw-arrw-i"><div class="dw-arr"></div></div></div>') + '<div class="dwwr"><div aria-live="assertive" class="dwv' + (s.headerText ? '' : ' dw-hidden') + '"></div><div class="dwcc">',
                isMinw = $.isArray(s.minWidth),
                isMaxw = $.isArray(s.maxWidth),
                isFixw = $.isArray(s.fixedWidth);

            $.each(s.wheels, function (i, wg) { // Wheel groups
                html += '<div class="dwc' + (s.mode != 'scroller' ? ' dwpm' : ' dwsc') + (s.showLabel ? '' : ' dwhl') + '"><div class="dwwc dwrc"><table cellpadding="0" cellspacing="0"><tr>';
                $.each(wg, function (j, w) { // Wheels
                    wheels[l] = w;
                    lbl = w.label !== undefined ? w.label : j;
                    html += '<td><div class="dwwl dwrc dwwl' + l + '">' + (s.mode != 'scroller' ? '<a href="#" tabindex="-1" class="dwb-e dwwb dwwbp" style="height:' + hi + 'px;line-height:' + hi + 'px;"><span>+</span></a><a href="#" tabindex="-1" class="dwb-e dwwb dwwbm" style="height:' + hi + 'px;line-height:' + hi + 'px;"><span>&ndash;</span></a>' : '') + '<div class="dwl">' + lbl + '</div><div tabindex="0" aria-live="off" aria-label="' + lbl + '" role="listbox" class="dwww"><div class="dww" style="height:' + (s.rows * hi) + 'px;' + (s.fixedWidth ? ('width:' + (isFixw ? s.fixedWidth[l] : s.fixedWidth) + 'px;') : (s.minWidth ? ('min-width:' + (isMinw ? s.minWidth[l] : s.minWidth) + 'px;') : 'min-width:' + s.width + 'px;') + (s.maxWidth ? ('max-width:' + (isMaxw ? s.maxWidth[l] : s.maxWidth) + 'px;') : '')) + '"><div class="dw-ul">';
                    // Create wheel values
                    html += generateWheelItems(l);
                    html += '</div><div class="dwwol"></div></div><div class="dwwo"></div></div><div class="dwwol"></div></div></td>';
                    l++;
                });


                html += '</tr></table></div></div>';
            });

            html += '</div>';

            if (modal && hasButtons) {
                html += '<div class="dwbc">';
                $.each(buttons, function (i, b) {
                    b = (typeof b === 'string') ? that.buttons[b] : b;
                    html += '<span' + (s.btnWidth ? ' style="width:' + (100 / buttons.length) + '%"' : '') + ' class="dwbw ' + b.css + '"><a href="#" class="dwb dwb' + i + ' dwb-e" role="button">' + b.text + '</a></span>';
                });
                html += '</div>';
            }
            html += (modal ? '</div>' : '') + '</div></div></div>';

            dw = $(html);
            persp = $('.dw-persp', dw);
            overlay = $('.dwo', dw);

            scrollToPos();

            event('onMarkupReady', [dw]);

            // Show
            if (modal) {

                dw.appendTo(s.context);
                if (anim && !prevAnim) {
                    dw.addClass('dw-trans');
                    // Remove animation class
                    setTimeout(function () {
                        dw.removeClass('dw-trans').find('.dw').removeClass(mAnim);
                    }, 350);
                }
            } else if (elm.is('div')) {
                elm.html(dw);
            } else {
                dw.insertAfter(elm);
            }

            event('onMarkupInserted', [dw]);

            visible = true;

            if (modal) {
                // Enter / ESC
                $(window).on('keydown.dw', function (e) {
                    if (e.keyCode == 13) {
                        that.select();
                    } else if (e.keyCode == 27) {
                        that.cancel();
                    }
                });

                // Prevent scroll if not specified otherwise
                if (s.scrollLock) {
                    dw.on('touchmove', function (e) {
                        if (lock) {
                            e.preventDefault();
                        }
                    });
                }

                // Disable inputs to prevent bleed through (Android bug) and set autocomplete to off (for Firefox)
                $('input,select,button', doc).each(function () {
                    if (!this.disabled) {
                        if ($(this).attr('autocomplete')) {
                            $(this).data('autocomplete', $(this).attr('autocomplete'));
                        }
                        $(this).addClass('dwtd').prop('disabled', true).attr('autocomplete', 'off');
                    }
                });

                // Set position
                that.position();
                attachPosition('orientationchange.dw resize.dw', false);
                attachPosition('scroll.dw', true);
            }

            // Events
            dw.on('DOMMouseScroll mousewheel', '.dwwl', onScroll)
                .on('keydown', '.dwwl', onKeyDown)
                .on('keyup', '.dwwl', onKeyUp)
                .on('selectstart mousedown', prevdef) // Prevents blue highlight on Android and text selection in IE
                .on('click', '.dwb-e', prevdef)
                .on('touchend', function () { if (s.tap) { setTap(); } })
                .on('keydown', '.dwb-e', function (e) {
                    if (e.keyCode == 32) { // Space
                        e.preventDefault();
                        e.stopPropagation();
                        $(this).click();
                    }
                });

            setTimeout(function () {
                // Init buttons
                $.each(buttons, function (i, b) {
                    that.tap($('.dwb' + i, dw), function (e) {
                        b = (typeof b === 'string') ? that.buttons[b] : b;
                        b.handler.call(this, e, that);
                    });
                });

                if (s.closeOnOverlay) {
                    that.tap(overlay, function () {
                        that.cancel();
                    });
                }

                dw.on(START_EVENT, '.dwwl', onStart).on(START_EVENT, '.dwb-e', onBtnStart);

            }, 300);

            event('onShow', [dw, v]);
        };

        /**
        * Hides the scroller instance.
        */
        that.hide = function (prevAnim, btn, force) {
            // If onClose handler returns false, prevent hide
            if (!visible || (!force && event('onClose', [v, btn]) === false)) {
                return;
            }

            // Re-enable temporary disabled fields
            $('.dwtd', doc).each(function () {
                $(this).prop('disabled', false).removeClass('dwtd');
                if ($(this).data('autocomplete')) {
                    $(this).attr('autocomplete', $(this).data('autocomplete'));
                } else {
                    $(this).removeAttr('autocomplete');
                }
            });

            // Hide wheels and overlay
            if (dw) {
                var doAnim = modal && anim && !prevAnim;
                if (doAnim) {
                    dw.addClass('dw-trans').find('.dw').addClass('dw-' + anim + ' dw-out');
                }
                if (prevAnim) {
                    dw.remove();
                } else {
                    setTimeout(function () {
                        dw.remove();
                        if (activeElm) {
                            preventShow = true;
                            activeElm.focus();
                        }
                    }, doAnim ? 350 : 1);
                }

                // Stop positioning on window resize
                wndw.off('.dw');
            }

            pixels = {};
            visible = false;
        };

        /**
        * Set button handler.
        */
        that.select = function () {
            if (that.hide(false, 'set') !== false) {
                setVal(true, 0, true);
                event('onSelect', [that.val]);
            }
        };

        /**
        * Show mobiscroll on focus and click event of the parameter.
        * @param {jQuery} elm - Events will be attached to this element.
        * @param {Function} [beforeShow=undefined] - Optional function to execute before showing mobiscroll.
        */
        that.attachShow = function (elm, beforeShow) {
            elmList.push(elm);
            if (s.display !== 'inline') {
                elm.on((s.showOnFocus ? 'focus.dw' : '') + (s.showOnTap ? ' click.dw' : ''), function (ev) {
                    if ((ev.type !== 'focus' || (ev.type === 'focus' && !preventShow)) && !tap) {
                        if (beforeShow) {
                            beforeShow();
                        }
                        activeElm = elm;
                        that.show();
                    }
                    setTimeout(function () {
                        preventShow = false;
                    }, 300); // With jQuery < 1.9 focus is fired twice in IE
                });
            }
        };

        /**
        * Cancel and hide the scroller instance.
        */
        that.cancel = function () {
            if (that.hide(false, 'cancel') !== false) {
                event('onCancel', [that.val]);
            }
        };

        /**
        * Scroller initialization.
        */
        that.init = function (ss) {
            // Get theme defaults
            theme = ms.themes[ss.theme || s.theme];

            // Get language defaults
            lang = ms.i18n[ss.lang || s.lang];

            extend(settings, ss); // Update original user settings

            event('onThemeLoad', [lang, settings]);

            extend(s, theme, lang, settings);

            // Add default buttons
            s.buttons = s.buttons || ['确定', '取消'];

            // Hide header text in inline mode by default
            s.headerText = s.headerText === undefined ? (s.display !== 'inline' ? '{value}' : false) : s.headerText;

            that.settings = s;

            // Unbind all events (if re-init)
            elm.off('.dw');

            var preset = ms.presets[s.preset];

            if (preset) {
                pres = preset.call(e, that);
                extend(s, pres, settings); // Load preset settings
            }

            // Set private members
            m = Math.floor(s.rows / 2);
            hi = s.height;
            anim = s.animate;
            modal = s.display !== 'inline';
            buttons = s.buttons;
            wndw = $(s.context == 'body' ? window : s.context);
            doc = $(s.context)[0];

            if (!s.setText) {
                buttons.splice($.inArray('set', buttons), 1);
            }
            if (!s.cancelText) {
                buttons.splice($.inArray('cancel', buttons), 1);
            }
            if (s.button3) {
                buttons.splice($.inArray('set', buttons) + 1, 0, { text: s.button3Text, handler: s.button3 });
            }

            that.context = wndw;
            that.live = !modal || ($.inArray('set', buttons) == -1);
            that.buttons.set = { text: s.setText, css: 'dwb-s', handler: that.select };
            that.buttons.cancel = { text: (that.live) ? s.closeText : s.cancelText, css: 'dwb-c', handler: that.cancel };
            that.buttons.clear = { text: s.clearText, css: 'dwb-cl', handler: function () {
                that.trigger('onClear', [dw]);
                elm.val('');
                if (!that.live) {
                    that.hide();
                }
            }};

            hasButtons = buttons.length > 0;

            if (visible) {
                that.hide(true, false, true);
            }

            if (modal) {
                read();
                if (input) {
                    // Set element readonly, save original state
                    if (readOnly === undefined) {
                        readOnly = e.readOnly;
                    }
                    e.readOnly = true;
                }
                that.attachShow(elm);
            } else {
                that.show();
            }

            if (input) {
                elm.on('change.dw', function () {
                    if (!preventChange) {
                        that.setValue(elm.val(), false, 0.2);
                    }
                    preventChange = false;
                });
            }
        };

        /**
        * Sets one ore more options.
        */
        that.option = function (opt, value) {
            var obj = {};
            if (typeof opt === 'object') {
                obj = opt;
            } else {
                obj[opt] = value;
            }
            that.init(obj);
        };

        /**
        * Destroys the mobiscroll instance.
        */
        that.destroy = function () {
            that.hide(true, false, true);
            // Remove all events from elements
            $.each(elmList, function (i, v) {
                v.off('.dw');
            });
            // Remove events from window
            $(window).off('.dwa');
            // Reset original readonly state
            if (input) {
                e.readOnly = readOnly;
            }
            // Delete scroller instance
            delete instances[e.id];
            event('onDestroy', []);
        };

        /**
        * Returns the mobiscroll instance.
        */
        that.getInst = function () {
            return that;
        };

        /**
        * Returns the closest valid cell.
        */
        that.getValidCell = getValid;

        /**
        * Triggers a mobiscroll event.
        */
        that.trigger = event;

        instances[e.id] = that;

        that.values = null;
        that.val = null;
        that.temp = null;
        that.buttons = {};
        that._selectedValues = {};

        that.init(settings);
    }

    function testTouch(e) {
        if (e.type === 'touchstart') {
            touch = true;
        } else if (touch) {
            touch = false;
            return false;
        }
        return true;
    }

    function setTap() {
        tap = true;
        setTimeout(function () {
            tap = false;
        }, 300);
    }

    function constrain(val, min, max) {
        return Math.max(min, Math.min(val, max));
    }

    function convert(w) {
        var ret = {
            values: [],
            keys: []
        };
        $.each(w, function (k, v) {
            ret.keys.push(k);
            ret.values.push(v);
        });
        return ret;
    }

    var activeElm,
        move,
        tap,
        touch,
        preventShow,
        ms = $.mobiscroll,
        instances = ms.instances,
        util = ms.util,
        prefix = util.prefix,
        pr = util.jsPrefix,
        has3d = util.has3d,
        getCoord = util.getCoord,
        empty = function () {},
        prevdef = function (e) { e.preventDefault(); },
        extend = $.extend,
        START_EVENT = 'touchstart mousedown',
        MOVE_EVENT = 'touchmove mousemove',
        END_EVENT = 'touchend mouseup',
        defaults = {
            // Options
            width: 70,
            height: 40,
            rows: 3,
            delay: 300,
            disabled: false,
            readonly: false,
            closeOnOverlay: true,
            showOnFocus: true,
            showOnTap: true,
            showLabel: true,
            wheels: [],
            theme: 'sense-ui',
            selectedText: ' Selected',
            closeText: 'Close',
            display: 'modal',
            mode: 'scroller',
            preset: '',
            lang: 'zh',
            setText: '确定',
            cancelText: '取消',
            clearText: 'Clear',
            context: 'body',
            scrollLock: true,
            tap: true,
            btnWidth: true,
            speedUnit: 0.0012,
            timeUnit: 0.1,
            formatResult: function (d) {
                return d.join(' ');
            },
            parseValue: function (value, inst) {
                var val = value.split(' '),
                    ret = [],
                    i = 0,
                    keys;

                $.each(inst.settings.wheels, function (j, wg) {
                    $.each(wg, function (k, w) {
                        w = w.values ? w : convert(w);
                        keys = w.keys || w.values;
                        if ($.inArray(val[i], keys) !== -1) {
                            ret.push(val[i]);
                        } else {
                            ret.push(keys[0]);
                        }
                        i++;
                    });
                });
                return ret;
            }
        };

    // Prevent re-show on window focus
    $(window).on('focus', function () {
        if (activeElm) {
            preventShow = true;
        }
    });

    $(document).on('mouseover mouseup mousedown click', function (e) { // Prevent standard behaviour on body click
        if (tap) {
            e.stopPropagation();
            e.preventDefault();
            return false;
        }
    });

    $.mobiscroll.setDefaults = function (o) {
        extend(defaults, o);
    };

})(jQuery);
(function ($) {

    var ms = $.mobiscroll,
            date = new Date(),
            defaults = {
                dateFormat: 'mm/dd/yy',
                dateOrder: 'mmddy',
                timeWheels: 'hhiiA',
                timeFormat: 'hh:ii A',
                startYear: date.getFullYear() - 100,
                endYear: date.getFullYear() + 1,
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                monthNamesShort: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                dayNames: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'],
                dayNamesShort: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                shortYearCutoff: '+10',
                monthText: 'Month',
                dayText: 'Day',
                yearText: 'Year',
                hourText: 'Hours',
                minuteText: 'Minutes',
                secText: 'Seconds',
                ampmText: '&nbsp;',
                nowText: 'Now',
                showNow: false,
                stepHour: 1,
                stepMinute: 1,
                stepSecond: 1,
                separator: ' '
            },
    /**
     * @class Mobiscroll.datetime
     * @extends Mobiscroll
     * Mobiscroll Datetime component
     */
    preset = function (inst) {
        var that = $(this),
                html5def = {},
                format;
        // Force format for html5 date inputs (experimental)
        if (that.is('input')) {
            switch (that.attr('type')) {
                case 'date':
                    format = 'yy-mm-dd';
                    break;
                case 'datetime':
                    format = 'yy-mm-ddTHH:ii:ssZ';
                    break;
                case 'datetime-local':
                    format = 'yy-mm-ddTHH:ii:ss';
                    break;
                case 'month':
                    format = 'yy-mm';
                    html5def.dateOrder = 'mmyy';
                    break;
                case 'time':
                    format = 'HH:ii:ss';
                    break;
            }
            // Check for min/max attributes
            var min = that.attr('min'),
                    max = that.attr('max');
            if (min) {
                html5def.minDate = ms.parseDate(format, min);
            }
            if (max) {
                html5def.maxDate = ms.parseDate(format, max);
            }
        }

        // Set year-month-day order
        var i,
                k,
                keys,
                values,
                wg,
                start,
                end,
                invalid,
                hasTime,
                orig = $.extend({}, inst.settings),
                s = $.extend(inst.settings, defaults, html5def, orig),
                offset = 0,
                wheels = [],
                ord = [],
                o = {},
                f = {y: 'getFullYear', m: 'getMonth', d: 'getDate', h: getHour, i: getMinute, s: getSecond, a: getAmPm},
        p = s.preset,
                dord = s.dateOrder,
                tord = s.timeWheels,
                regen = dord.match(/D/),
                ampm = tord.match(/a/i),
                hampm = tord.match(/h/),
                hformat = p == 'datetime' ? s.dateFormat + s.separator + s.timeFormat : p == 'time' ? s.timeFormat : s.dateFormat,
                defd = new Date(),
                stepH = s.stepHour,
                stepM = s.stepMinute,
                stepS = s.stepSecond,
                mind = s.minDate || new Date(s.startYear, 0, 1),
                maxd = s.maxDate || new Date(s.endYear, 11, 31, 23, 59, 59);

        format = format || hformat;

        if (p.match(/date/i)) {

            // Determine the order of year, month, day wheels
            $.each(['y', 'm', 'd'], function (j, v) {
                i = dord.search(new RegExp(v, 'i'));
                if (i > -1) {
                    ord.push({o: i, v: v});
                }
            });
            ord.sort(function (a, b) {
                return a.o > b.o ? 1 : -1;
            });
            $.each(ord, function (i, v) {
                o[v.v] = i;
            });

            wg = [];
            for (k = 0; k < 3; k++) {
                if (k == o.y) {
                    offset++;
                    values = [];
                    keys = [];
                    start = mind.getFullYear();
                    end = maxd.getFullYear();
                    for (i = start; i <= end; i++) {
                        keys.push(i);
                        values.push(dord.match(/yy/i) ? i : (i + '').substr(2, 2));
                    }
                    addWheel(wg, keys, values, s.yearText);
                } else if (k == o.m) {
                    offset++;
                    values = [];
                    keys = [];
                    for (i = 0; i < 12; i++) {
                        var str = dord.replace(/[dy]/gi, '').replace(/mm/, i < 9 ? '0' + (i + 1) : i + 1).replace(/m/, (i + 1));
                        keys.push(i);
                        values.push(str.match(/MM/) ? str.replace(/MM/, '<span class="dw-mon">' + s.monthNames[i] + '</span>') : str.replace(/M/, '<span class="dw-mon">' + s.monthNamesShort[i] + '</span>'));
                    }
                    addWheel(wg, keys, values, s.monthText);
                } else if (k == o.d) {
                    offset++;
                    values = [];
                    keys = [];
                    for (i = 1; i < 32; i++) {
                        keys.push(i);
                        values.push(dord.match(/dd/i) && i < 10 ? '0' + i : i);
                    }
                    addWheel(wg, keys, values, s.dayText);
                }
            }
            wheels.push(wg);
        }

        if (p.match(/time/i)) {
            hasTime = true;

            // Determine the order of hours, minutes, seconds wheels
            ord = [];
            $.each(['h', 'i', 's', 'a'], function (i, v) {
                i = tord.search(new RegExp(v, 'i'));
                if (i > -1) {
                    ord.push({o: i, v: v});
                }
            });
            ord.sort(function (a, b) {
                return a.o > b.o ? 1 : -1;
            });
            $.each(ord, function (i, v) {
                o[v.v] = offset + i;
            });

            wg = [];
            for (k = offset; k < offset + 4; k++) {
                if (k == o.h) {
                    offset++;
                    values = [];
                    keys = [];
                    for (i = 0; i < (hampm ? 12 : 24); i += stepH) {
                        keys.push(i);
                        values.push(hampm && i == 0 ? 12 : tord.match(/hh/i) && i < 10 ? '0' + i : i);
                    }
                    addWheel(wg, keys, values, s.hourText);
                } else if (k == o.i) {
                    offset++;
                    values = [];
                    keys = [];
                    for (i = 0; i < 60; i += stepM) {
                        keys.push(i);
                        values.push(tord.match(/ii/) && i < 10 ? '0' + i : i);
                    }
                    addWheel(wg, keys, values, s.minuteText);
                } else if (k == o.s) {
                    offset++;
                    values = [];
                    keys = [];
                    for (i = 0; i < 60; i += stepS) {
                        keys.push(i);
                        values.push(tord.match(/ss/) && i < 10 ? '0' + i : i);
                    }
                    addWheel(wg, keys, values, s.secText);
                } else if (k == o.a) {
                    offset++;
                    var upper = tord.match(/A/);
                    addWheel(wg, [0, 1], upper ? ['AM', 'PM'] : ['am', 'pm'], s.ampmText);
                }
            }

            wheels.push(wg);
        }

        function get(d, i, def) {
            if (o[i] !== undefined) {
                return +d[o[i]];
            }
            if (def !== undefined) {
                return def;
            }
            return defd[f[i]] ? defd[f[i]]() : f[i](defd);
        }

        function addWheel(wg, k, v, lbl) {
            wg.push({
                values: v,
                keys: k,
                label: lbl
            });
        }

        function step(v, st) {
            return Math.floor(v / st) * st;
        }

        function getHour(d) {
            var hour = d.getHours();
            hour = hampm && hour >= 12 ? hour - 12 : hour;
            return step(hour, stepH);
        }

        function getMinute(d) {
            return step(d.getMinutes(), stepM);
        }

        function getSecond(d) {
            return step(d.getSeconds(), stepS);
        }

        function getAmPm(d) {
            return ampm && d.getHours() > 11 ? 1 : 0;
        }

        function getDate(d) {
            var hour = get(d, 'h', 0);
            return new Date(get(d, 'y'), get(d, 'm'), get(d, 'd', 1), get(d, 'a') ? hour + 12 : hour, get(d, 'i', 0), get(d, 's', 0));
        }

        function getIndex(t, v) {
            return $('.dw-li', t).index($('.dw-li[data-val="' + v + '"]', t));
        }

        function getValidIndex(t, v, max, add) {
            if (v < 0) {
                return 0;
            }
            if (v > max) {
                return $('.dw-li', t).length;
            }
            return getIndex(t, v) + add;
        }

        // Extended methods
        // ---

        /**
         * Sets the selected date
         *
         * @param {Date} d Date to select.
         * @param {Boolean} [fill=false] Also set the value of the associated input element. Default is true.
         * @param {Number} [time=0] Animation time to scroll to the selected date.
         * @param {Boolean} [temp=false] Set temporary value only.
         * @param {Boolean} [manual=false] Indicates that the action was triggered by the user or from code.
         */
        inst.setDate = function (d, fill, time, temp) {
            var i;

            // Set wheels
            for (i in o) {
                inst.temp[o[i]] = d[f[i]] ? d[f[i]]() : f[i](d);
            }
            inst.setValue(inst.temp, fill, time, temp);
        };

        /**
         * Returns the currently selected date.
         *
         * @param {Boolean} [temp=false] If true, return the currently shown date on the picker, otherwise the last selected one.
         * @return {Date}
         */
        inst.getDate = function (temp) {
            return getDate(temp ? inst.temp : inst.values);
        };

        inst.convert = function (obj) {
            var x = obj;

            if (!$.isArray(obj)) { // Convert from old format
                x = [];
                $.each(obj, function (i, o) {
                    $.each(o, function (j, o) {
                        if (i === 'daysOfWeek') {
                            if (o.d) {
                                o.d = 'w' + o.d;
                            } else {
                                o = 'w' + o;
                            }
                        }
                        x.push(o);
                    });
                });
            }

            return x;
        };

        inst.format = hformat;
        inst.buttons.now = {text: s.nowText, css: 'dwb-n', handler: function () {
                inst.setDate(new Date(), false, 0.3, true, true);
            }};

        if (s.showNow) {
            s.buttons.splice($.inArray('set', s.buttons) + 1, 0, 'now');
        }

        invalid = s.invalid ? inst.convert(s.invalid) : false;

        // ---

        return {
            wheels: wheels,
            headerText: s.headerText ? function (v) {
                return ms.formatDate(hformat, getDate(inst.temp), s);
            } : false,
            formatResult: function (d) {
                return ms.formatDate(format, getDate(d), s);
            },
            parseValue: function (val) {
                var d = ms.parseDate(format, val, s),
                        i,
                        result = [];

                // Set wheels
                for (i in o) {
                    result[o[i]] = d[f[i]] ? d[f[i]]() : f[i](d);
                }
                return result;
            },
            validate: function (dw, i, time, dir) {
                var temp = inst.temp, //.slice(0),
                        mins = {y: mind.getFullYear(), m: 0, d: 1, h: 0, i: 0, s: 0, a: 0},
                maxs = {y: maxd.getFullYear(), m: 11, d: 31, h: step(hampm ? 11 : 23, stepH), i: step(59, stepM), s: step(59, stepS), a: 1},
                steps = {h: stepH, i: stepM, s: stepS, a: 1},
                y = get(temp, 'y'),
                        m = get(temp, 'm'),
                        minprop = true,
                        maxprop = true;

                $.each(['y', 'm', 'd', 'a', 'h', 'i', 's'], function (x, i) {
                    if (o[i] !== undefined) {
                        var min = mins[i],
                                max = maxs[i],
                                maxdays = 31,
                                val = get(temp, i),
                                t = $('.dw-ul', dw).eq(o[i]);

                        if (i == 'd') {
                            maxdays = 32 - new Date(y, m, 32).getDate();
                            max = maxdays;
                            if (regen) {
                                $('.dw-li', t).each(function () {
                                    var that = $(this),
                                            d = that.data('val'),
                                            w = new Date(y, m, d).getDay(),
                                            str = dord.replace(/[my]/gi, '').replace(/dd/, d < 10 ? '0' + d : d).replace(/d/, d);
                                    $('.dw-i', that).html(str.match(/DD/) ? str.replace(/DD/, '<span class="dw-day">' + s.dayNames[w] + '</span>') : str.replace(/D/, '<span class="dw-day">' + s.dayNamesShort[w] + '</span>'));
                                });
                            }
                        }
                        if (minprop && mind) {
                            min = mind[f[i]] ? mind[f[i]]() : f[i](mind);
                        }
                        if (maxprop && maxd) {
                            max = maxd[f[i]] ? maxd[f[i]]() : f[i](maxd);
                        }
                        if (i != 'y') {
                            var i1 = getIndex(t, min),
                                    i2 = getIndex(t, max);
                            $('.dw-li', t).removeClass('dw-v').slice(i1, i2 + 1).addClass('dw-v');
                            if (i == 'd') { // Hide days not in month
                                $('.dw-li', t).removeClass('dw-h').slice(maxdays).addClass('dw-h');
                            }
                        }
                        if (val < min) {
                            val = min;
                        }
                        if (val > max) {
                            val = max;
                        }
                        if (minprop) {
                            minprop = val == min;
                        }
                        if (maxprop) {
                            maxprop = val == max;
                        }
                        // Disable some days
                        if (invalid && i == 'd') {
                            var d, j, k, v,
                                    first = new Date(y, m, 1).getDay(),
                                    idx = [];

                            for (j = 0; j < invalid.length; j++) {
                                d = invalid[j];
                                v = d + '';
                                if (!d.start) {
                                    if (d.getTime) { // Exact date
                                        if (d.getFullYear() == y && d.getMonth() == m) {
                                            idx.push(d.getDate() - 1);
                                        }
                                    } else if (!v.match(/w/i)) { // Day of month
                                        v = v.split('/');
                                        if (v[1]) {
                                            if (v[0] - 1 == m) {
                                                idx.push(v[1] - 1);
                                            }
                                        } else {
                                            idx.push(v[0] - 1);
                                        }
                                    } else { // Day of week
                                        v = +v.replace('w', '');
                                        for (k = v - first; k < maxdays; k += 7) {
                                            if (k >= 0) {
                                                idx.push(k);
                                            }
                                        }
                                    }
                                }
                            }
                            $.each(idx, function (i, v) {
                                $('.dw-li', t).eq(v).removeClass('dw-v');
                            });

                            val = inst.getValidCell(val, t, dir).val;
                        }

                        // Set modified value
                        temp[o[i]] = val;
                    }
                });

                // Invalid times
                if (hasTime && invalid) {

                    var dd, v, val, str, parts1, parts2, j, v1, v2, i1, i2, prop1, prop2, target, add, remove,
                            spec = {},
                            d = get(temp, 'd'),
                            day = new Date(y, m, d),
                            w = ['a', 'h', 'i', 's'];

                    $.each(invalid, function (i, obj) {
                        if (obj.start) {
                            obj.apply = false;
                            dd = obj.d;
                            v = dd + '';
                            str = v.split('/');
                            if (dd && ((dd.getTime && y == dd.getFullYear() && m == dd.getMonth() && d == dd.getDate()) || // Exact date
                                    (!v.match(/w/i) && ((str[1] && d == str[1] && m == str[0] - 1) || (!str[1] && d == str[0]))) || // Day of month
                                    (v.match(/w/i) && day.getDay() == +v.replace('w', '')) // Day of week
                                    )) {
                                obj.apply = true;
                                spec[day] = true; // Prevent applying generic rule on day, if specific exists
                            }
                        }
                    });

                    $.each(invalid, function (i, obj) {
                        if (obj.start && (obj.apply || (!obj.d && !spec[day]))) {

                            parts1 = obj.start.split(':');
                            parts2 = obj.end.split(':');

                            for (j = 0; j < 3; j++) {
                                if (parts1[j] === undefined) {
                                    parts1[j] = 0;
                                }
                                if (parts2[j] === undefined) {
                                    parts2[j] = 59;
                                }
                                parts1[j] = +parts1[j];
                                parts2[j] = +parts2[j];
                            }

                            parts1.unshift(parts1[0] > 11 ? 1 : 0);
                            parts2.unshift(parts2[0] > 11 ? 1 : 0);

                            if (hampm) {
                                if (parts1[1] >= 12) {
                                    parts1[1] = parts1[1] - 12;
                                }

                                if (parts2[1] >= 12) {
                                    parts2[1] = parts2[1] - 12;
                                }
                            }

                            prop1 = true;
                            prop2 = true;
                            $.each(w, function (i, v) {
                                if (o[v] !== undefined) {
                                    val = get(temp, v);
                                    add = 0;
                                    remove = 0;
                                    i1 = 0;
                                    i2 = undefined;
                                    target = $('.dw-ul', dw).eq(o[v]);

                                    // Look ahead if next wheels should be disabled completely
                                    for (j = i + 1; j < 4; j++) {
                                        if (parts1[j] > 0) {
                                            add = steps[v];
                                        }
                                        if (parts2[j] < maxs[w[j]]) {
                                            remove = steps[v];
                                        }
                                    }

                                    // Calculate min and max values
                                    v1 = step(parts1[i] + add, steps[v]);
                                    v2 = step(parts2[i] - remove, steps[v]);

                                    if (prop1) {
                                        i1 = getValidIndex(target, v1, maxs[v], 0);
                                    }

                                    if (prop2) {
                                        i2 = getValidIndex(target, v2, maxs[v], 1);
                                    }

                                    // Disable values
                                    if (prop1 || prop2) {
                                        $('.dw-li', target).slice(i1, i2).removeClass('dw-v');
                                    }

                                    // Get valid value
                                    val = inst.getValidCell(val, target, dir).val;

                                    prop1 = prop1 && val == step(parts1[i], steps[v]);
                                    prop2 = prop2 && val == step(parts2[i], steps[v]);

                                    // Set modified value
                                    temp[o[v]] = val;
                                }
                            });
                        }
                    });
                }
            }
        };
    };

    $.each(['date', 'time', 'datetime'], function (i, v) {
        ms.presets[v] = preset;
        ms.presetShort(v);
    });

    /**
     * Format a date into a string value with a specified format.
     * @param {String} format Output format.
     * @param {Date} date Date to format.
     * @param {Object} [settings={}] Settings.
     * @return {String} Returns the formatted date string.
     */
    ms.formatDate = function (format, date, settings) {
        if (!date) {
            return null;
        }
        var s = $.extend({}, defaults, settings),
                look = function (m) { // Check whether a format character is doubled
                    var n = 0;
                    while (i + 1 < format.length && format.charAt(i + 1) == m) {
                        n++;
                        i++;
                    }
                    return n;
                },
                f1 = function (m, val, len) { // Format a number, with leading zero if necessary
                    var n = '' + val;
                    if (look(m)) {
                        while (n.length < len) {
                            n = '0' + n;
                        }
                    }
                    return n;
                },
                f2 = function (m, val, s, l) { // Format a name, short or long as requested
                    return (look(m) ? l[val] : s[val]);
                },
                i,
                output = '',
                literal = false;

        for (i = 0; i < format.length; i++) {
            if (literal) {
                if (format.charAt(i) == "'" && !look("'")) {
                    literal = false;
                } else {
                    output += format.charAt(i);
                }
            } else {
                switch (format.charAt(i)) {
                    case 'd':
                        output += f1('d', date.getDate(), 2);
                        break;
                    case 'D':
                        output += f2('D', date.getDay(), s.dayNamesShort, s.dayNames);
                        break;
                    case 'o':
                        output += f1('o', (date.getTime() - new Date(date.getFullYear(), 0, 0).getTime()) / 86400000, 3);
                        break;
                    case 'm':
                        output += f1('m', date.getMonth() + 1, 2);
                        break;
                    case 'M':
                        output += f2('M', date.getMonth(), s.monthNamesShort, s.monthNames);
                        break;
                    case 'y':
                        output += (look('y') ? date.getFullYear() : (date.getYear() % 100 < 10 ? '0' : '') + date.getYear() % 100);
                        break;
                    case 'h':
                        var h = date.getHours();
                        output += f1('h', (h > 12 ? (h - 12) : (h == 0 ? 12 : h)), 2);
                        break;
                    case 'H':
                        output += f1('H', date.getHours(), 2);
                        break;
                    case 'i':
                        output += f1('i', date.getMinutes(), 2);
                        break;
                    case 's':
                        output += f1('s', date.getSeconds(), 2);
                        break;
                    case 'a':
                        output += date.getHours() > 11 ? 'pm' : 'am';
                        break;
                    case 'A':
                        output += date.getHours() > 11 ? 'PM' : 'AM';
                        break;
                    case "'":
                        if (look("'")) {
                            output += "'";
                        } else {
                            literal = true;
                        }
                        break;
                    default:
                        output += format.charAt(i);
                }
            }
        }
        return output;
    };

    /**
     * Extract a date from a string value with a specified format.
     * @param {String} format Input format.
     * @param {String} value String to parse.
     * @param {Object} [settings={}] Settings.
     * @return {Date} Returns the extracted date.
     */
    ms.parseDate = function (format, value, settings) {
        var s = $.extend({}, defaults, settings),
                def = s.defaultValue || new Date();

        if (!format || !value) {
            return def;
        }

        value = (typeof value == 'object' ? value.toString() : value + '');

        var shortYearCutoff = s.shortYearCutoff,
                year = def.getFullYear(),
                month = def.getMonth() + 1,
                day = def.getDate(),
                doy = -1,
                hours = def.getHours(),
                minutes = def.getMinutes(),
                seconds = 0, //def.getSeconds(),
                ampm = -1,
                literal = false, // Check whether a format character is doubled
                lookAhead = function (match) {
                    var matches = (iFormat + 1 < format.length && format.charAt(iFormat + 1) == match);
                    if (matches) {
                        iFormat++;
                    }
                    return matches;
                },
                getNumber = function (match) { // Extract a number from the string value
                    lookAhead(match);
                    var size = (match == '@' ? 14 : (match == '!' ? 20 : (match == 'y' ? 4 : (match == 'o' ? 3 : 2)))),
                            digits = new RegExp('^\\d{1,' + size + '}'),
                            num = value.substr(iValue).match(digits);

                    if (!num) {
                        return 0;
                    }
                    iValue += num[0].length;
                    return parseInt(num[0], 10);
                },
                getName = function (match, s, l) { // Extract a name from the string value and convert to an index
                    var names = (lookAhead(match) ? l : s),
                            i;

                    for (i = 0; i < names.length; i++) {
                        if (value.substr(iValue, names[i].length).toLowerCase() == names[i].toLowerCase()) {
                            iValue += names[i].length;
                            return i + 1;
                        }
                    }
                    return 0;
                },
                checkLiteral = function () {
                    iValue++;
                },
                iValue = 0,
                iFormat;

        for (iFormat = 0; iFormat < format.length; iFormat++) {
            if (literal) {
                if (format.charAt(iFormat) == "'" && !lookAhead("'")) {
                    literal = false;
                } else {
                    checkLiteral();
                }
            } else {
                switch (format.charAt(iFormat)) {
                    case 'd':
                        day = getNumber('d');
                        break;
                    case 'D':
                        getName('D', s.dayNamesShort, s.dayNames);
                        break;
                    case 'o':
                        doy = getNumber('o');
                        break;
                    case 'm':
                        month = getNumber('m');
                        break;
                    case 'M':
                        month = getName('M', s.monthNamesShort, s.monthNames);
                        break;
                    case 'y':
                        year = getNumber('y');
                        break;
                    case 'H':
                        hours = getNumber('H');
                        break;
                    case 'h':
                        hours = getNumber('h');
                        break;
                    case 'i':
                        minutes = getNumber('i');
                        break;
                    case 's':
                        seconds = getNumber('s');
                        break;
                    case 'a':
                        ampm = getName('a', ['am', 'pm'], ['am', 'pm']) - 1;
                        break;
                    case 'A':
                        ampm = getName('A', ['am', 'pm'], ['am', 'pm']) - 1;
                        break;
                    case "'":
                        if (lookAhead("'")) {
                            checkLiteral();
                        } else {
                            literal = true;
                        }
                        break;
                    default:
                        checkLiteral();
                }
            }
        }
        if (year < 100) {
            year += new Date().getFullYear() - new Date().getFullYear() % 100 +
                    (year <= (typeof shortYearCutoff != 'string' ? shortYearCutoff : new Date().getFullYear() % 100 + parseInt(shortYearCutoff, 10)) ? 0 : -100);
        }
        if (doy > -1) {
            month = 1;
            day = doy;
            do {
                var dim = 32 - new Date(year, month - 1, 32).getDate();
                if (day <= dim) {
                    break;
                }
                month++;
                day -= dim;
            } while (true);
        }
        hours = (ampm == -1) ? hours : ((ampm && hours < 12) ? (hours + 12) : (!ampm && hours == 12 ? 0 : hours));
        var date = new Date(year, month - 1, day, hours, minutes, seconds);
        if (date.getFullYear() != year || date.getMonth() + 1 != month || date.getDate() != day) {
            return def; // Invalid date
        }
        return date;
    };

})(jQuery);
(function ($) {
    var theme = {
        dateOrder: 'Mddyy',
        mode: 'mixed',
        rows: 5,
        minWidth: 70,
        height: 36,
        showLabel: false,
        useShortLabels: true
    };

    $.mobiscroll.themes['android-ics'] = theme;
    $.mobiscroll.themes['android-ics light'] = theme;

})(jQuery);


(function (a) {
    var b = {weekText: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"], daysCount: 15, };
    a.mobiscroll.presets.datehour = function (p) {
        var v = a.extend({}, p.settings), x = a.extend(p.settings, b, v), t = a(this);
        var l = new Date();
        var o = [];
        var e = {label: "日期", keys: [], values: [], };
        var w = {label: "时间", keys: [], values: [], };
        for (var k = 0; k < x.daysCount; k++) {
            var j = l.valueOf();
            j = j + k * 24 * 60 * 60 * 1000;
            j = new Date(j);
            var u = j.getFullYear();
            var g = j.getMonth() + 1;
            var r = j.getDate();
            var f = g + "月" + r + "日&nbsp;" + x.weekText[j.getDay()];
            if (g <= 9) {
                g = "0" + g
            }
            if (r <= 9) {
                r = "0" + r
            }
            var q = u + "-" + g + "-" + r;
            e.keys.push(q);
            if (k == 0) {
                f = "今天"
            } else {
                if (k == 1) {
                    f = "明天"
                }
            }
            e.values.push(f)
        }
        for (var n = 0; n <= 23; n++) {
            if (n <= 9) {
                n = "0" + n
            }
            w.keys.push(n + ":00", n + ":30");
        }
        for (var i = 0; i < w.keys.length; i++) {
            w.values.push(w.keys[i])
        }
        var c = [];
        c.push(e);
        c.push(w);
        o.push(c);
        return {wheels: o, parseValue: function (I, D) {
                console.info("parseValue:" + I);
                var B = new RegExp(/[0-9]{4}-[0-9]{2}-[0-9]{2}[" "]{1}[0-9]{2}[:]{1}[0-9]{2}/);
                if (I == null || I == "" || !B.test(I)) {
                    var H = new Date();
                    var C = H.getHours();
                    var s = H.getMinutes();
                    if (C >= 0 && C < 8 || (C == 8 && s == 0)) {
                        C = 10
                    } else {
                        if (C >= 8 && C < 17 || (C == 17 && s == 0)) {
                            if (s == 0) {
                                C = C + 2
                            } else {
                                C = C + 3
                            }
                        } else {
                            H = H.valueOf();
                            H = H + 24 * 60 * 60 * 1000;
                            H = new Date(H);
                            C = 10
                        }
                    }
                    if (C > 19) {
                        C = 19
                    }
                    var G = H.getFullYear();
                    var z = H.getMonth() + 1;
                    var F = H.getDate();
                    z = z <= 9 ? "0" + z : z;
                    F = F <= 9 ? "0" + F : F;
                    C = C <= 9 ? "0" + C : C;
                    I = G + "-" + z + "-" + F + " " + C + ":00";
                    console.info("defaultValue:" + I)
                }
                var h = I.split(" "), E = [], A = 0, J;
                a.each(D.settings.wheels, function (i, d) {
                    a.each(d, function (y, m) {
                        m = m.values ? m : convert(m);
                        J = m.keys || m.values;
                        if (a.inArray(h[A], J) !== -1) {
                            E.push(h[A])
                        } else {
                            E.push(J[0])
                        }
                        A++
                    })
                });
                return E
            }, validate: function (C, I) {
                var N = new Date();
                var s = N.getHours();
                var h = N.getMinutes();
                if (s >= 0 && s < 8 || (s == 8 && h == 0)) {
                    s = 10
                } else {
                    if (s >= 8 && s < 17 || (s == 17 && h == 0)) {
                        if (h == 0) {
                            s = s + 2
                        } else {
                            s = s + 2
                        }
                    } else {
                        N = N.valueOf();
                        N = N + 24 * 60 * 60 * 1000;
                        N = new Date(N);
                        s = 8
                    }
                }
                if (s > 19) {
                    s = 19
                }
                var B = N.getFullYear();
                var G = N.getMonth() + 1;
                var K = N.getDate();
                G = G <= 9 ? "0" + G : G;
                K = K <= 9 ? "0" + K : K;
                s = s <= 9 ? "0" + s : s;
                var E = B + "-" + G + "-" + K;
                var L = a(".dw-ul", C).eq(0);
                var D = a(".dw-ul", C).eq(1);
                var O = a(".dw-li", L).index(a('.dw-li[data-val="' + E + '"]', L)), M = a(".dw-li", L).size();
                a(".dw-li", L).removeClass("dw-v").slice(O, M).addClass("dw-v");
                var A = s + ":00", z = "18:00";
                var J = p.temp;
                if (J[0] != E) {
                    A = "08:00"
                } else {
                    if (N.getHours() > 20 || (N.getHours() == 20 && h > 0)) {
                        A = "10:00"
                    }
                }
                var H = a(".dw-li", D).index(a('.dw-li[data-val="' + A + '"]', D)), F = a(".dw-li", D).index(a('.dw-li[data-val="' + z + '"]', D));
                a(".dw-li", D).removeClass("dw-v").slice(H, F + 1).addClass("dw-v")
            }, }
    };
    a.mobiscroll.presetShort("datehour")
})(jQuery);

$(document).ready(function () {

    var dateScroll = function () {
        
        var date = new Date();
        var curr = new Date().getFullYear(),
                d = date.getDate(),
                m = date.getMonth();
        $('.svctime').scroller('destroy').scroller({
            preset: 'datehour',
            minDate: new Date(curr, m, d, 8, 00),
            maxDate: new Date(curr, m, d + 7),
            invalid: [{d: new Date(), start: '00:00', end: (date.getHours() + 2) + ':' + date.getMinutes()}],
            theme: "android-ics light",
            mode: "scroller",
            lang: 'zh',
            display: "bottom",
            animate: "slideup",
            stepMinute: 30,
            dateOrder: 'MMDdd',
            timeWheels: 'HHii',
            rows: 3
        });
    }
    dateScroll();//时间选择控件

});