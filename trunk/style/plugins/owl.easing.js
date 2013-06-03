/* Easing library */
Owl.Extend(Owl.Easing, {
    inQuad: function(a, b, c, d) {
        return c * (a /= d) * a + b;
    },
    outQuad: function(a, b, c, d) {
        return -c * (a /= d) * (a - 2) + b;
    },
    inOutQuad: function(a, b, c, d) {
        if ((a /= d / 2) < 1) return c / 2 * a * a + b;
        return -c / 2 * (--a * (a - 2) - 1) + b;
    },
    inCubic: function(a, b, c, d) {
        return c * (a /= d) * a * a + b;
    },
    outCubic: function(a, b, c, d) {
        return c * ((a = a / d - 1) * a * a + 1) + b;
    },
    inOutCubic: function(a, b, c, d) {
        if ((a /= d / 2) < 1) return c / 2 * a * a * a + b;
        return c / 2 * ((a -= 2) * a * a + 2) + b;
    },
    inQuart: function(a, b, c, d) {
        return c * (a /= d) * a * a * a + b;
    },
    outQuart: function(a, b, c, d) {
        return -c * ((a = a / d - 1) * a * a * a - 1) + b;
    },
    inOutQuart: function(a, b, c, d) {
        if ((a /= d / 2) < 1) return c / 2 * a * a * a * a + b;
        return -c / 2 * ((a -= 2) * a * a * a - 2) + b;
    },
    inQuint: function(a, b, c, d) {
        return c * (a /= d) * a * a * a * a + b;
    },
    outQuint: function(a, b, c, d) {
        return c * ((a = a / d - 1) * a * a * a * a + 1) + b;
    },
    inOutQuint: function(a, b, c, d) {
        if ((a /= d / 2) < 1) return c / 2 * a * a * a * a * a + b;
        return c / 2 * ((a -= 2) * a * a * a * a + 2) + b;
    },
    inSine: function(a, b, c, d) {
        return -c * Math.cos(a / d * (Math.PI / 2)) + c + b;
    },
    outSine: function(a, b, c, d) {
        return c * Math.sin(a / d * (Math.PI / 2)) + b;
    },
    inOutSine: function(a, b, c, d) {
        return -c / 2 * (Math.cos(Math.PI * a / d) - 1) + b;
    },
    inExpo: function(a, b, c, d) {
        return a == 0 ? b : c * Math.pow(2, 10 * (a / d - 1)) + b;
    },
    outExpo: function(a, b, c, d) {
        return a == d ? b + c : c * (-Math.pow(2, -10 * a / d) + 1) + b;
    },
    inOutExpo: function(a, b, c, d) {
        if (a == 0) return b;
        if (a == d) return b + c;
        if ((a /= d / 2) < 1) return c / 2 * Math.pow(2, 10 * (a - 1)) + b;
        return c / 2 * (-Math.pow(2, -10 * --a) + 2) + b;
    },
    inCirc: function(a, b, c, d) {
        return -c * (Math.sqrt(1 - (a /= d) * a) - 1) + b;
    },
    outCirc: function(a, b, c, d) {
        return c * Math.sqrt(1 - (a = a / d - 1) * a) + b;
    },
    inOutCirc: function(a, b, c, d) {
        if ((a /= d / 2) < 1) return -c / 2 * (Math.sqrt(1 - a * a) - 1) + b;
        return c / 2 * (Math.sqrt(1 - (a -= 2) * a) + 1) + b;
    },
    inElastic: function(a, b, c, d) {
        var e = 1.70158;
        var f = 0;
        var g = c;
        if (a == 0) return b;
        if ((a /= d) == 1) return b + c;
        if (!f) f = d * .3;
        if (g < Math.abs(c)) {
            g = c;
            var e = f / 4;
        } else var e = f / (2 * Math.PI) * Math.asin(c / g);
        return -(g * Math.pow(2, 10 * (a -= 1)) * Math.sin((a * d - e) * 2 * Math.PI / f)) + b;
    },
    outElastic: function(a, b, c, d) {
        var e = 1.70158;
        var f = 0;
        var g = c;
        if (a == 0) return b;
        if ((a /= d) == 1) return b + c;
        if (!f) f = d * .3;
        if (g < Math.abs(c)) {
            g = c;
            var e = f / 4;
        } else var e = f / (2 * Math.PI) * Math.asin(c / g);
        return g * Math.pow(2, -10 * a) * Math.sin((a * d - e) * 2 * Math.PI / f) + c + b;
    },
    inOutElastic: function(a, b, c, d) {
        var e = 1.70158;
        var f = 0;
        var g = c;
        if (a == 0) return b;
        if ((a /= d / 2) == 2) return b + c;
        if (!f) f = d * .3 * 1.5;
        if (g < Math.abs(c)) {
            g = c;
            var e = f / 4;
        } else var e = f / (2 * Math.PI) * Math.asin(c / g);
        if (a < 1) return -.5 * g * Math.pow(2, 10 * (a -= 1)) * Math.sin((a * d - e) * 2 * Math.PI / f) + b;
        return g * Math.pow(2, -10 * (a -= 1)) * Math.sin((a * d - e) * 2 * Math.PI / f) * .5 + c + b;
    },
    inBack: function(a, b, c, d, e) {
        if (e == undefined) e = 1.70158;
        return c * (a /= d) * a * ((e + 1) * a - e) + b;
    },
    outBack: function(a, b, c, d, e) {
        if (e == undefined) e = 1.70158;
        return c * ((a = a / d - 1) * a * ((e + 1) * a + e) + 1) + b;
    },
    inOutBack: function(a, b, c, d, e) {
        if (e == undefined) e = 1.70158;
        if ((a /= d / 2) < 1) return c / 2 * a * a * (((e *= 1.525) + 1) * a - e) + b;
        return c / 2 * ((a -= 2) * a * (((e *= 1.525) + 1) * a + e) + 2) + b;
    },
    inBounce: function(a, b, c, d) {
        return c - Owl.Easing.outBounce(d - a, 0, c, d) + b;
    },
    outBounce: function(a, b, c, d) {
        if ((a /= d) < 1 / 2.75) {
            return c * 7.5625 * a * a + b;
        } else if (a < 2 / 2.75) {
            return c * (7.5625 * (a -= 1.5 / 2.75) * a + .75) + b;
        } else if (a < 2.5 / 2.75) {
            return c * (7.5625 * (a -= 2.25 / 2.75) * a + .9375) + b;
        } else {
            return c * (7.5625 * (a -= 2.625 / 2.75) * a + .984375) + b;
        }
    },
    inOutBounce: function(a, b, c, d) {
        if (a < d / 2) return Owl.Easing.inBounce(a * 2, 0, c, d) * .5 + b;
        return Owl.Easing.outBounce(a * 2 - d, 0, c, d) * .5 + c * .5 + b;
    }
});