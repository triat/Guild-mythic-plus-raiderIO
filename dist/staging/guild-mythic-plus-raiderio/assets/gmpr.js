(function () {
  "use strict";

  var DEFAULT_POLL_INTERVAL = 2000;
  var DEFAULT_POLL_MAX = 30000;
  var REFRESH_THROTTLE_MS = 60000;

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

  function initExpandCollapse(wrapper) {
    var headers = wrapper.querySelectorAll(".gmpr-profile-header[role='button']");
    for (var i = 0; i < headers.length; i++) {
      (function (header) {
        // Click handler
        header.addEventListener("click", function (e) {
          // Don't toggle if clicking on the profile link
          if (e.target.closest(".gmpr-profile-link")) {
            return;
          }
          toggleExpand(header);
        });

        // Keyboard handler (Enter and Space)
        header.addEventListener("keydown", function (e) {
          if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            toggleExpand(header);
          }
        });
      })(headers[i]);
    }

    // Prevent profile links from triggering expand
    var links = wrapper.querySelectorAll(".gmpr-profile-link");
    for (var j = 0; j < links.length; j++) {
      links[j].addEventListener("click", function (e) {
        e.stopPropagation();
      });
    }
  }

  function toggleExpand(header) {
    var card = header.closest(".gmpr-profile-card");
    if (!card) return;

    var isExpanded = card.classList.contains("expanded");
    card.classList.toggle("expanded");

    // Update aria-expanded
    header.setAttribute("aria-expanded", isExpanded ? "false" : "true");
  }

  function initFilters(wrapper) {
    var roleSelect = wrapper.querySelector('#gmpr-filter-role');
    var nameInput = wrapper.querySelector('#gmpr-filter-name');
    var scoreMinInput = wrapper.querySelector('#gmpr-filter-score-min');
    var scoreMaxInput = wrapper.querySelector('#gmpr-filter-score-max');
    var sortSelect = wrapper.querySelector('#gmpr-sort-by');
    var clearBtn = wrapper.querySelector('#gmpr-clear-filters');
    var resultsCount = wrapper.querySelector('#gmpr-results-count');
    var filterEmpty = wrapper.querySelector('#gmpr-filter-empty');

    if (!roleSelect || !nameInput || !scoreMinInput || !scoreMaxInput || !sortSelect || !clearBtn) {
      return; // Filters not present in this wrapper
    }

    // Store original order of cards to restore when clearing sort
    var container = wrapper.querySelector('.gmpr-roster-list');
    var originalOrder = container ? Array.from(container.querySelectorAll('.gmpr-profile-card')) : [];

    var filterState = {
      role: 'all',
      nameSearch: '',
      scoreMin: 0,
      scoreMax: 999999,
      sortBy: 'none'
    };

    function applyFilters() {
      var cards = wrapper.querySelectorAll('.gmpr-profile-card');
      var visibleCount = 0;
      var totalCount = cards.length;

      cards.forEach(function(card) {
        var role = card.getAttribute('data-role') || '';
        var name = card.getAttribute('data-name') || '';
        var score = parseInt(card.getAttribute('data-score') || '0', 10);

        var visible = true;

        // Role filter (empty roles only show when "all" is selected)
        if (filterState.role !== 'all' && role !== filterState.role) {
          visible = false;
        }

        // Name search
        if (filterState.nameSearch && !name.includes(filterState.nameSearch.toLowerCase())) {
          visible = false;
        }

        // Score range
        if (score < filterState.scoreMin || score > filterState.scoreMax) {
          visible = false;
        }

        card.style.display = visible ? '' : 'none';
        if (visible) visibleCount++;
      });

      applySorting();
      updateResultsCount(visibleCount, totalCount);

      // Show empty state if no results
      if (filterEmpty) {
        filterEmpty.style.display = visibleCount === 0 ? 'block' : 'none';
      }
    }

    function applySorting() {
      var container = wrapper.querySelector('.gmpr-roster-list');
      if (!container) return;

      if (filterState.sortBy === 'none') {
        // Restore original order
        originalOrder.forEach(function(card) {
          container.appendChild(card);
        });
        return;
      }

      var cards = Array.from(container.querySelectorAll('.gmpr-profile-card'));
      var visibleCards = cards.filter(function(card) {
        return card.style.display !== 'none';
      });

      visibleCards.sort(function(a, b) {
        if (filterState.sortBy.startsWith('name-')) {
          var nameA = a.getAttribute('data-name') || '';
          var nameB = b.getAttribute('data-name') || '';
          var result = nameA.localeCompare(nameB);
          return filterState.sortBy === 'name-asc' ? result : -result;
        }

        if (filterState.sortBy.startsWith('score-')) {
          var scoreA = parseInt(a.getAttribute('data-score') || '0', 10);
          var scoreB = parseInt(b.getAttribute('data-score') || '0', 10);
          var result = scoreA - scoreB;
          return filterState.sortBy === 'score-asc' ? result : -result;
        }

        return 0;
      });

      // Re-append in sorted order
      visibleCards.forEach(function(card) {
        container.appendChild(card);
      });
    }

    function updateResultsCount(visible, total) {
      if (!resultsCount) return;
      if (visible === total) {
        resultsCount.textContent = 'Showing all ' + total + ' characters';
      } else {
        resultsCount.textContent = 'Showing ' + visible + ' of ' + total + ' characters';
      }
    }

    function clearFilters() {
      filterState.role = 'all';
      filterState.nameSearch = '';
      filterState.scoreMin = 0;
      filterState.scoreMax = 999999;
      filterState.sortBy = 'none';

      roleSelect.value = 'all';
      nameInput.value = '';
      scoreMinInput.value = '';
      scoreMaxInput.value = '';
      sortSelect.value = 'none';

      applyFilters();
    }

    // Event listeners
    roleSelect.addEventListener('change', function() {
      filterState.role = roleSelect.value;
      applyFilters();
    });

    nameInput.addEventListener('input', function() {
      filterState.nameSearch = nameInput.value;
      applyFilters();
    });

    scoreMinInput.addEventListener('input', function() {
      filterState.scoreMin = parseInt(scoreMinInput.value || '0', 10);
      applyFilters();
    });

    scoreMaxInput.addEventListener('input', function() {
      filterState.scoreMax = parseInt(scoreMaxInput.value || '999999', 10);
      applyFilters();
    });

    sortSelect.addEventListener('change', function() {
      filterState.sortBy = sortSelect.value;
      applyFilters();
    });

    clearBtn.addEventListener('click', clearFilters);

    // Initialize results count
    var cards = wrapper.querySelectorAll('.gmpr-profile-card');
    updateResultsCount(cards.length, cards.length);
  }

  function initWrapper(wrapper) {
    initAvatars(wrapper);
    initExpandCollapse(wrapper);
    initFilters(wrapper);

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
