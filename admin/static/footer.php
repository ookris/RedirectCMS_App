</div><!-- /sb-content-wrapper -->
<script>
(function () {
  var sbToggle = document.getElementById('sb-toggle');
  var sbSidebar = document.getElementById('sb-sidebar');
  var sbOverlay = document.getElementById('sb-overlay');
  var sbWrapper = document.getElementById('sb-content-wrapper');
  if (!sbToggle) return;

  function isMobile() { return window.innerWidth <= 768; }

  // Przywróć stan sidebara z localStorage (tylko desktop)
  if (!isMobile() && localStorage.getItem('sbCollapsed') === '1') {
    sbSidebar.classList.add('sb-hidden');
    sbWrapper.classList.add('sb-expanded');
  }

  sbToggle.addEventListener('click', function () {
    if (isMobile()) {
      sbSidebar.classList.toggle('show');
      sbOverlay.classList.toggle('show');
    } else {
      var hidden = sbSidebar.classList.toggle('sb-hidden');
      sbWrapper.classList.toggle('sb-expanded');
      localStorage.setItem('sbCollapsed', hidden ? '1' : '0');
    }
  });

  sbOverlay.addEventListener('click', function () {
    sbSidebar.classList.remove('show');
    sbOverlay.classList.remove('show');
  });
}());
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){new bootstrap.Tooltip(el)});</script>
<script>
(function () {
  if (!window.rcmsLicenseBlocked) return;
  function showLicenseToast(msg) {
    var container = document.getElementById('rcms-lic-toast-cnt');
    if (!container) {
      container = document.createElement('div');
      container.id = 'rcms-lic-toast-cnt';
      container.className = 'position-fixed bottom-0 end-0 p-3';
      container.style.zIndex = '9998';
      document.body.appendChild(container);
    }
    var id = 'rlt-' + Date.now();
    var el = document.createElement('div');
    el.id = id;
    el.className = 'toast align-items-center text-bg-danger border-0 mb-2';
    el.setAttribute('role', 'alert');
    el.setAttribute('aria-live', 'assertive');
    el.setAttribute('aria-atomic', 'true');
    var flex = document.createElement('div');
    flex.className = 'd-flex';
    var body = document.createElement('div');
    body.className = 'toast-body';
    body.textContent = msg;
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn-close btn-close-white me-2 m-auto';
    btn.setAttribute('data-bs-dismiss', 'toast');
    btn.setAttribute('aria-label', 'Zamknij');
    flex.appendChild(body);
    flex.appendChild(btn);
    el.appendChild(flex);
    container.appendChild(el);
    var t = new bootstrap.Toast(el, {delay: 5000});
    t.show();
    el.addEventListener('hidden.bs.toast', function () { el.remove(); });
  }
  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-license-block]');
    if (!el) return;
    e.preventDefault();
    e.stopImmediatePropagation();
    showLicenseToast(el.getAttribute('data-license-block') || window.rcmsLicenseBlockMsg);
  }, true);
}());
</script>
