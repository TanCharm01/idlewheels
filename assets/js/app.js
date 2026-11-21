(function () {
  "use strict";

  var DAY = 86400000;
  function toKey(d) { return d.toISOString().slice(0, 10); }
  function parse(k) { return new Date(k + "T00:00:00Z"); }
  function addDays(k, n) { return toKey(new Date(parse(k).getTime() + n * DAY)); }

  // keep the return date after the pick-up date
  document.querySelectorAll("[data-date-pair]").forEach(function (form) {
    var from = form.querySelector("[data-from]");
    var to = form.querySelector("[data-to]");
    if (!from || !to) return;
    from.addEventListener("change", function () {
      if (!from.value) return;
      var next = addDays(from.value, 1);
      to.min = next;
      if (!to.value || to.value <= from.value) to.value = next;
      to.dispatchEvent(new Event("change", { bubbles: true }));
    });
  });

  // booking form: price estimate + check for booked days
  document.querySelectorAll("[data-booking]").forEach(function (form) {
    var cfg = JSON.parse(form.getAttribute("data-booking"));
    var held = {};
    cfg.held.forEach(function (k) { held[k] = true; });
    var from = form.querySelector("[data-from]");
    var to = form.querySelector("[data-to]");
    var box = form.querySelector("[data-estimate]");
    var err = form.querySelector("[data-date-error]");
    var submit = form.querySelector("button");
    var fmt = function (n) { return cfg.currency + " " + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ","); };

    function update() {
      var problem = "";
      var days = 0;
      if (from.value && to.value) {
        days = Math.round((parse(to.value) - parse(from.value)) / DAY);
        if (days <= 0) problem = "Choose a return date after the pick-up date.";
        else if (days < cfg.minDays || days > cfg.maxDays) problem = "This car can be rented for " + cfg.minDays + " to " + cfg.maxDays + " days.";
        else {
          for (var k = from.value; k < to.value; k = addDays(k, 1)) {
            if (held[k]) { problem = "This car is already booked on " + k + ". Choose other dates."; break; }
          }
        }
      }
      err.textContent = problem;
      err.classList.toggle("hidden", !problem);
      submit.disabled = !!problem;
      if (!days || problem) { box.classList.add("hidden"); box.classList.remove("grid"); return; }

      var rental = days * cfg.pricePerDay;
      var extras = 0;
      cfg.addons.forEach(function (a) {
        var input = form.querySelector('[data-addon="' + a.id + '"]');
        var q;
        if (input.type === "checkbox") {
          // yes/no extra: a ticked per-day extra covers the whole rental
          q = input.checked ? (a.perDay ? days : 1) : 0;
        } else {
          q = Math.max(0, Math.min(parseInt(input.value || "0", 10) || 0, a.max, a.perDay ? days : a.max));
        }
        extras += q * a.price;
      });
      var fee = Math.round((rental + extras) * cfg.feePercent) / 100;
      box.querySelector("[data-rental-label]").textContent = fmt(cfg.pricePerDay) + " × " + days + (days === 1 ? " day" : " days");
      box.querySelector("[data-rental]").textContent = fmt(rental);
      box.querySelector("[data-extras]").textContent = fmt(extras);
      box.querySelector("[data-fee]").textContent = fmt(fee);
      box.querySelector("[data-total]").textContent = fmt(rental + extras + fee);
      box.classList.remove("hidden");
      box.classList.add("grid");
    }
    form.addEventListener("input", update);
    form.addEventListener("change", update);
    update();

    // availability calendar
    var cal = document.querySelector("[data-calendar]");
    if (!cal) return;
    var today = new Date();
    var month = new Date(Date.UTC(today.getFullYear(), today.getMonth(), 1));
    var todayKey = toKey(new Date(Date.UTC(today.getFullYear(), today.getMonth(), today.getDate())));
    function render() {
      var y = month.getUTCFullYear(), m = month.getUTCMonth();
      var days = new Date(Date.UTC(y, m + 1, 0)).getUTCDate();
      var lead = (new Date(Date.UTC(y, m, 1)).getUTCDay() + 6) % 7;
      var isCurrent = y === today.getFullYear() && m === today.getMonth();
      var html = '<div class="mb-4 flex items-center justify-between">' +
        '<button type="button" class="cal-nav" data-step="-1" ' + (isCurrent ? "disabled" : "") + ' aria-label="Previous month">‹</button>' +
        '<p class="font-semibold">' + month.toLocaleDateString(undefined, { month: "long", year: "numeric", timeZone: "UTC" }) + "</p>" +
        '<button type="button" class="cal-nav" data-step="1" aria-label="Next month">›</button></div>' +
        '<div class="grid grid-cols-7 gap-1 text-center text-xs text-muted-foreground">' +
        ["Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"].map(function (d) { return "<span>" + d + "</span>"; }).join("") + "</div>" +
        '<div class="mt-1 grid grid-cols-7 gap-1 text-center text-sm">';
      for (var i = 0; i < lead; i++) html += "<span></span>";
      for (var d = 1; d <= days; d++) {
        var k = toKey(new Date(Date.UTC(y, m, d)));
        var cls = k < todayKey ? "cal-past" : held[k] ? "cal-held" : "cal-free";
        html += '<span class="cal-day ' + cls + '" title="' + (cls === "cal-held" ? "Booked" : cls === "cal-free" ? "Available" : "") + '">' + d + "</span>";
      }
      html += '</div><p class="mt-4 flex gap-4 text-xs text-muted-foreground"><span><span class="cal-day cal-free inline-block w-6">1</span> Available</span><span><span class="cal-day cal-held inline-block w-6">1</span> Booked</span></p>';
      cal.innerHTML = html;
    }
    cal.addEventListener("click", function (e) {
      var b = e.target.closest("[data-step]");
      if (!b || b.disabled) return;
      month = new Date(Date.UTC(month.getUTCFullYear(), month.getUTCMonth() + parseInt(b.getAttribute("data-step"), 10), 1));
      render();
    });
    render();
  });

  // faq search
  var search = document.querySelector("[data-faq-search]");
  if (search) {
    search.addEventListener("input", function () {
      var q = search.value.trim().toLowerCase();
      document.querySelectorAll("[data-faq-group]").forEach(function (g) {
        var any = false;
        g.querySelectorAll("[data-faq-item]").forEach(function (item) {
          var hit = !q || item.textContent.toLowerCase().indexOf(q) !== -1;
          item.classList.toggle("hidden", !hit);
          if (hit) any = true;
          if (q && hit) item.open = true;
        });
        g.classList.toggle("hidden", !any);
      });
    });
  }

  // show/hide buttons
  document.querySelectorAll("[data-toggle]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var el = document.querySelector(btn.getAttribute("data-toggle"));
      if (!el) return;
      var open = el.classList.toggle("hidden") === false;
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
  });

  // eye button on every password field to show what's being typed
  var EYE = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
  var EYE_OFF = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 19c-6.5 0-10-7-10-7a18.4 18.4 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.1 9.1 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/><path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>';
  document.querySelectorAll('input[type="password"]').forEach(function (input) {
    var wrap = document.createElement("span");
    wrap.className = "pw-wrap";
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    var btn = document.createElement("button");
    btn.type = "button";
    btn.className = "pw-toggle";
    function render(shown) {
      btn.innerHTML = shown ? EYE_OFF : EYE;
      btn.setAttribute("aria-label", shown ? "Hide password" : "Show password");
      btn.setAttribute("aria-pressed", shown ? "true" : "false");
      btn.title = shown ? "Hide password" : "Show password";
    }
    render(false);
    btn.addEventListener("click", function (ev) {
      ev.preventDefault();
      var show = input.type === "password";
      input.type = show ? "text" : "password";
      render(show);
      input.focus();
      var end = input.value.length;
      try { input.setSelectionRange(end, end); } catch (e) {}
    });
    wrap.appendChild(btn);
  });
  // switch back to hidden before sending, so browsers still treat it as a password
  document.querySelectorAll("form").forEach(function (form) {
    form.addEventListener("submit", function () {
      form.querySelectorAll(".pw-wrap input").forEach(function (i) { i.type = "password"; });
    });
  });
})();
