var DD = new Date();
var Y = DD.getFullYear();
var M = DD.getMonth() + 1;
var D = DD.getDate();
document.write(Y + '/', M + '/', D);
var H = DD.getHours()
var MM = DD.getMinutes();
var S = DD.getSeconds();
document.write(H + ':', MM + ':', S);
function draw() {
  var cv = document.getElementById('cv');
  if (cv.getContext) {
    var ctx = cv.getContext('2d');
    var v = [];
    var sec = 1, rate = 1, xx = 1280, yy = 800, hi = 30, lo = 0, on1 = 20, off1 = 21;
    var y = yy / (hi - lo) | 0;
    var DD = new Date();
    var M, D, pD, H, MM, p;
    DD.setTime(DD.getTime() - 0);
    ctx.lineWidth = 1;
    for (i = xx; i > 0; i -= 60) {
      ctx.beginPath();
      ctx.moveTo(i, 0);
      ctx.lineTo(i, yy);
      ctx.stroke();
      pD = D; D = DD.getDate();
      H = ('0' + DD.getHours()).slice(-2);
      MM = ('0' + DD.getMinutes()).slice(-2);
      ctx.fillText(H + ':' + MM, i - 10, yy + 10);
      if (D < pD) {
        M = DD.getMonth() + 1;
        ctx.fillText(M + '/' + D, i + 50, yy + 20);
      }
      DD.setSeconds(DD.getSeconds() - 60 * sec * rate);
    }
    for (i = lo; i <= hi; i += 5) {
      p = yy - (i - lo) * y;
      if (i % 10) {
        ctx.strokeStyle = 'gray';
      } else {
        ctx.strokeStyle = 'black';
      }
      ctx.beginPath();
      ctx.moveTo(0, p);
      ctx.lineTo(xx, p);
      ctx.stroke();
      ctx.fillText(i, 0, p + 10);
    }
    var c, col, col0, c0 = true;
    var c9 = true;
    var color_on = ['blue', 'red', 'green', 'orange', 'cyan'];
    var color_off = ['darkblue', 'darkred', 'darkgreen', 'darkorange', 'darkcyan'];
    for (j = 0; j < 5; j++) {
      var y9 = Math.max.apply(null, v[j]);
      var y0 = y9;
      for (var i = 0; i < v[j].length; i++) {
        if (v[j][i] && v[j][i] < y0) {
          y0 = v[j][i];
        }
      };
      ctx.strokeStyle = color_on[j];
      ctx.beginPath();
      ctx.moveTo(0, yy - v[j][0] / 256 * yy);
      for (i = 1; i < xx; i++) {
        p = yy - v[j][i] / 256 * yy;
        c = v[j][i] * (hi - lo) / 256 + lo;
        if (v[j][i] && v[j][i - 1]) {
          ctx.lineTo(i, p);
          col0 = col;
          if (on1 < off1) {
            if (c > off1) {
              col = color_off[j];
            }
            if (c < on1) {
              col = color_on[j];
            }
          }
          if (on1 > off1) {
            if (c > on1) {
              col = color_on[j];
            }
            if (c < off1) {
              col = color_off[j];
            }
          }
          if (col != col0) {
            ctx.stroke();
            ctx.strokeStyle = col;
            ctx.beginPath();
            ctx.moveTo(i, p);
          }
        } else {
          ctx.moveTo(i, p);
        }
        if (v[j][i] == y0 && c0) {
          ctx.fillText((c, i, p - 10));
          c0 = false;
        }
        if (v[j][i] == y9 && c9) {
          ctx.fillText(c, i, p + 10);
          c9 = false;
        }
        ctx.stroke();
      }
    }
  }
}