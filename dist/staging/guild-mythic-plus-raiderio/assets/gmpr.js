(function () {
  "use strict";

  var STORAGE_KEY = "gmpr_roster_view"; // "inline" | "cards"

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
  }

  document.addEventListener("DOMContentLoaded", function () {
    var wrappers = document.querySelectorAll(".gmpr[data-gmpr-roster]");
    for (var i = 0; i < wrappers.length; i++) {
      initWrapper(wrappers[i]);
    }
  });
})();


