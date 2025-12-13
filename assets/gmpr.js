(function () {
  "use strict";

  var STORAGE_KEY = "gmpr_roster_view"; // "inline" | "cards"
  var DEFAULT_POLL_INTERVAL = 2000;
  var DEFAULT_POLL_MAX = 30000;
  var REFRESH_THROTTLE_MS = 60000;

  function getStoredView() {
    try {
      var v = window.localStorage.getItem(STORAGE_KEY);
      // Backward compatibility: old value "table" maps to "inline".
      if (v === "table") return "inline";
      return v === "cards" ? "cards" : v === "inline" ? "inline" : null;
    } catch (e) {
      return null;
    }
  }

  function setStoredView(view) {
    try {
      window.localStorage.setItem(STORAGE_KEY, view);
    } catch (e) {
      // ignore
    }
  }

  function setView(wrapper, view) {
    wrapper.setAttribute("data-gmpr-view", view);

    var btnInline = wrapper.querySelector('[data-gmpr-view-btn="inline"]');
    var btnCards = wrapper.querySelector('[data-gmpr-view-btn="cards"]');
    if (btnInline) btnInline.setAttribute("aria-pressed", view === "inline" ? "true" : "false");
    if (btnCards) btnCards.setAttribute("aria-pressed", view === "cards" ? "true" : "false");
  }

  function initAvatars(wrapper) {
    var imgs = wrapper.querySelectorAll('img[data-gmpr-avatar="1"]');
    for (var i = 0; i < imgs.length; i++) {
      (function (img) {
        img.addEventListener("error", function () {
          var placeholder = img.getAttribute("data-gmpr-placeholder-src");
          if (!placeholder) return;
          if (img.getAttribute("data-gmpr-avatar-state") === "placeholder") return;
          img.setAttribute("data-gmpr-avatar-state", "placeholder");
          img.removeAttribute("srcset");
          img.src = placeholder;
        });
      })(imgs[i]);
    }
  }

  function initWrapper(wrapper) {
    var stored = getStoredView();
    var initial = stored || wrapper.getAttribute("data-gmpr-view") || "inline";
    initial = initial === "cards" ? "cards" : "inline";
    setView(wrapper, initial);

    var btns = wrapper.querySelectorAll("[data-gmpr-view-btn]");
    for (var i = 0; i < btns.length; i++) {
      btns[i].addEventListener("click", function (e) {
        e.preventDefault();
        var view = this.getAttribute("data-gmpr-view-btn");
        view = view === "cards" ? "cards" : "inline";
        setView(wrapper, view);
        setStoredView(view);
      });
    }

    initAvatars(wrapper);

    // Async refresh (stale-while-revalidate): trigger refresh then poll for updated cache.
    var async = wrapper.getAttribute("data-gmpr-async") === "1";
    var refreshNeeded = wrapper.getAttribute("data-gmpr-refresh-needed") === "1";
    if (async && refreshNeeded) {
      startAsyncRefresh(wrapper);
    }
  }

  function getRosterUrl() {
    if (window.gmprData && window.gmprData.rosterUrl) return window.gmprData.rosterUrl;
    // Fallback (pretty permalinks).
    return "/wp-json/gmpr/v1/roster";
  }

  function getRefreshUrl() {
    if (window.gmprData && window.gmprData.refreshUrl) return window.gmprData.refreshUrl;
    // Fallback (pretty permalinks).
    return "/wp-json/gmpr/v1/refresh";
  }

  function sleep(ms) {
    return new Promise(function (resolve) {
      setTimeout(resolve, ms);
    });
  }

  function ctxFromWrapper(wrapper) {
    return {
      region: wrapper.getAttribute("data-gmpr-region") || "",
      realm: wrapper.getAttribute("data-gmpr-realm") || "",
      guild: wrapper.getAttribute("data-gmpr-guild") || "",
      sig: wrapper.getAttribute("data-gmpr-sig") || "",
      fetchedAt: parseInt(wrapper.getAttribute("data-gmpr-fetched-at") || "0", 10) || 0,
    };
  }

  function refreshKey(ctx) {
    return "gmpr_refresh_attempt:" + ctx.region + "|" + ctx.realm + "|" + ctx.guild;
  }

  function canAttemptRefresh(ctx) {
    try {
      var k = refreshKey(ctx);
      var last = parseInt(window.sessionStorage.getItem(k) || "0", 10) || 0;
      return Date.now() - last > REFRESH_THROTTLE_MS;
    } catch (e) {
      return true;
    }
  }

  function markRefreshAttempt(ctx) {
    try {
      window.sessionStorage.setItem(refreshKey(ctx), String(Date.now()));
    } catch (e) {
      // ignore
    }
  }

  function findCurrentWrapper(ctx) {
    // Best-effort: locate the current wrapper instance after DOM replacement.
    return document.querySelector(
      '.gmpr[data-gmpr-roster][data-gmpr-region="' +
        cssEscape(ctx.region) +
        '"][data-gmpr-realm="' +
        cssEscape(ctx.realm) +
        '"][data-gmpr-guild="' +
        cssEscape(ctx.guild) +
        '"]'
    );
  }

  function cssEscape(s) {
    // Minimal escape for attribute selectors.
    return String(s).replace(/"/g, '\\"');
  }

  function buildQuery(params) {
    var parts = [];
    for (var k in params) {
      if (!Object.prototype.hasOwnProperty.call(params, k)) continue;
      parts.push(encodeURIComponent(k) + "=" + encodeURIComponent(params[k]));
    }
    return parts.join("&");
  }

  async function triggerRefresh(ctx) {
    var url = getRefreshUrl();
    try {
      await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          region: ctx.region,
          realm: ctx.realm,
          guild: ctx.guild,
          sig: ctx.sig,
        }),
        credentials: "same-origin",
      });
    } catch (e) {
      // ignore
    }
  }

  async function fetchRoster(ctx) {
    var base = getRosterUrl();
    var sep = base.indexOf("?") >= 0 ? "&" : "?";
    var url = base + sep + buildQuery({
        region: ctx.region,
        realm: ctx.realm,
        guild: ctx.guild,
        sig: ctx.sig,
      });
    var res = await fetch(url, { credentials: "same-origin" });
    if (!res.ok) return null;
    return await res.json();
  }

  function initAll(root) {
    var scope = root || document;
    var wrappers = scope.querySelectorAll(".gmpr[data-gmpr-roster]");
    for (var i = 0; i < wrappers.length; i++) {
      initWrapper(wrappers[i]);
    }
  }

  async function startAsyncRefresh(wrapper) {
    if (wrapper.getAttribute("data-gmpr-async-state") === "running") return;
    wrapper.setAttribute("data-gmpr-async-state", "running");

    var ctx = ctxFromWrapper(wrapper);
    if (!ctx.region || !ctx.realm || !ctx.guild || !ctx.sig) return;

    if (!canAttemptRefresh(ctx)) {
      return;
    }
    markRefreshAttempt(ctx);
    await triggerRefresh(ctx);

    var interval =
      (window.gmprData && window.gmprData.pollIntervalMs) || DEFAULT_POLL_INTERVAL;
    var max = (window.gmprData && window.gmprData.pollMaxMs) || DEFAULT_POLL_MAX;
    var deadline = Date.now() + max;

    while (Date.now() < deadline) {
      await sleep(interval);
      var payload = await fetchRoster(ctx);
      if (!payload || payload.status !== "ready") continue;

      var fetchedAt = payload.fetched_at || 0;
      if (!payload.html) continue;

      // If we don't have anything yet (cold start), render the first ready payload (stale or fresh),
      // then keep polling until we get a fresh payload to remove the stale notice.
      if (ctx.fetchedAt === 0 && fetchedAt > 0) {
        var w0 = findCurrentWrapper(ctx) || wrapper;
        w0.outerHTML = payload.html;
        initAll(document);
        ctx.fetchedAt = fetchedAt;
        // Continue polling: we want a fresh payload if possible.
        continue;
      }

      // Replace on a fresh payload. If fetchedAt did not change but the current wrapper is still marked
      // as needing refresh, we still replace to remove the stale notice.
      if (payload.is_stale === false) {
        var w = findCurrentWrapper(ctx) || wrapper;
        var stillNeeds = w && w.getAttribute("data-gmpr-refresh-needed") === "1";
        if (!stillNeeds && fetchedAt <= ctx.fetchedAt) {
          continue;
        }
        w.outerHTML = payload.html;
        initAll(document);
        return;
      }
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    initAll(document);
  });
})();


