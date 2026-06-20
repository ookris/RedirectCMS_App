<?php
/**
 * Partial: system oceniania postów (reakcje emoji)
 * Wymagane zmienne (wstrzykiwane przez index.php):
 *   $link           – tablica danych posta (musi zawierać 'id')
 *   $basePath       – prefiks ścieżki aplikacji
 *   $reactionCounts – array<string,int>  (happy|love|laugh|surprised|cry|anger => count)
 *   $myReaction     – ?string  (bieżąca reakcja użytkownika lub null)
 */

$_rxLinkId    = (int)($link['id'] ?? 0);
$_rxCounts    = $reactionCounts ?? ['happy'=>0,'love'=>0,'laugh'=>0,'surprised'=>0,'cry'=>0,'anger'=>0];
$_rxMine      = $myReaction ?? null;
$_rxBasePath  = $basePath ?? '';

$_rxLabels = [
    'happy'     => 'Super',
    'love'      => 'Kocham to',
    'laugh'     => 'Śmieszne',
    'surprised' => 'Wow',
    'cry'       => 'Smutne',
    'anger'     => 'Wkurzone',
];

/** Inline SVG per typ reakcji */
$_rxSvg = [
    'happy' => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 64 64"><circle fill="#FDCA47" cx="32" cy="32" r="30"/><path fill="#F9B700" d="M51.654 9.346A29.874 29.874 0 0 1 59 29c0 16.568-13.432 30-30 30a29.865 29.865 0 0 1-19.654-7.346C14.846 57.99 22.952 62 32 62c16.568 0 30-13.432 30-30 0-9.047-4.012-17.152-10.346-22.654z"/><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFE8BB" d="M6.418 20.5C5.302 24.242 13 11 25 6.084c5.834-2.391-13.832-1.5-18.582 14.416z"/><circle fill="#302C3B" cx="20.5" cy="26.592" r="5"/><circle fill="#302C3B" cx="43.5" cy="26.592" r="5"/><path fill="#302C3B" d="M44.584 40.279c-8.109 5.656-17.105 5.623-25.168 0-.971-.678-1.846.494-1.188 1.578 2.457 4.047 7.417 7.649 13.771 7.649 6.354 0 11.314-3.604 13.771-7.649.66-1.084-.215-2.253-1.186-1.578z"/></svg>',
    'love' => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 64 64"><circle fill="#FDCA47" cx="32" cy="32" r="30"/><path fill="#F9B700" d="M51.654 9.346A29.869 29.869 0 0 1 59 29c0 16.568-13.432 30-30 30a29.867 29.867 0 0 1-19.654-7.346C14.846 57.99 22.952 62 32 62c16.568 0 30-13.432 30-30 0-9.047-4.012-17.152-10.346-22.654z"/><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFE8BB" d="M6.418 20.5C5.302 24.242 13 11 25 6.084c5.834-2.391-13.832-1.5-18.582 14.416z"/><path fill="#E8A329" d="M4.02 21.156s-.125.328-.254.613c2.252 3.445 7.191 5.827 15.692 10.55 8.07-9.301 11.949-13.398 11.035-18.578-1.36-7.715-26.473 7.415-26.473 7.415z"/><path fill="#E81C27" d="M15.668 10.742C10.806 5.267.765 9.512 2.126 17.226 3.039 22.404 8.475 25.02 19.24 31c8.07-9.301 12.285-13.617 11.371-18.797-1.359-7.715-12.247-8.269-14.943-1.461z"/><path fill="#E8A329" d="M59.98 21.156l.239.648c-2.252 3.445-7.176 5.792-15.677 10.515-8.07-9.301-11.949-13.398-11.035-18.578 1.36-7.715 26.473 7.415 26.473 7.415z"/><path fill="#E81C27" d="M48.332 10.742c4.861-5.475 14.902-1.23 13.543 6.484-.914 5.178-6.35 7.794-17.115 13.774-8.07-9.301-12.285-13.617-11.371-18.797 1.359-7.715 12.247-8.269 14.943-1.461z"/><path fill="#302C3B" d="M51 37.789c0-.893-.529-2.01-2.037-2.297C45.092 34.756 39.373 34 32 34h-.002c-7.369 0-13.091.756-16.961 1.492-1.508.287-2.037 1.404-2.037 2.297C13 45.867 19.271 54 31.998 54H32c12.729 0 19-8.133 19-16.211z"/><path fill="#FFF" d="M46.504 38.037C43.996 37.66 38.688 37 31.999 37s-11.994.66-14.504 1.037c-1.479.221-1.568.76-1.466 1.52.062.451.156 1 .302 1.588.162.652.291.824 1.447.836 2.221.027 26.222.027 28.444 0 1.156-.012 1.281-.184 1.445-.836.145-.588.242-1.137.301-1.588.106-.76.012-1.299-1.464-1.52z"/></svg>',
    'laugh' => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 64 64"><circle fill="#FDCA47" cx="32" cy="32" r="30"/><path fill="#F9B700" d="M51.654 9.346A29.874 29.874 0 0 1 59 29c0 16.568-13.432 30-30 30a29.867 29.867 0 0 1-19.654-7.346C14.846 57.99 22.952 62 32 62c16.568 0 30-13.432 30-30 0-9.047-4.011-17.152-10.346-22.654z"/><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFE8BB" d="M6.418 20.5C5.302 24.242 13 11 25 6.084c5.834-2.391-13.832-1.5-18.582 14.416z"/><path fill="#302C3B" d="M51 37.789c0-.893-.529-2.01-2.037-2.297C45.092 34.756 39.373 34 32 34h-.002c-7.369 0-13.091.756-16.961 1.492-1.508.287-2.037 1.404-2.037 2.297C13 45.867 19.271 54 31.998 54H32c12.729 0 19-8.133 19-16.211z"/><path fill="#FFF" d="M46.504 38.037C43.996 37.66 38.688 37 31.999 37s-11.994.66-14.504 1.037c-1.479.221-1.568.76-1.466 1.52.062.451.156 1 .302 1.588.162.652.291.824 1.447.836 2.221.027 26.222.027 28.444 0 1.156-.012 1.281-.184 1.445-.836.145-.588.242-1.137.301-1.588.106-.76.012-1.299-1.464-1.52z"/><path fill="#302C3B" d="M49.561 16.486c.627-.221.991.866.6 1.158-4.6 3.41-7.391 6.28-7.391 6.28s3.871 1.438 8.731 4.71c.519.349-.258 1.631-.704 1.477-8.057-2.772-12.729-3.422-16.004-3.799-.482-.056-.674-.416-.348-.772 2.545-2.791 7.852-6.502 15.116-9.054zm-35.122-.001c-.628-.221-.991.867-.6 1.158 4.6 3.41 7.39 6.281 7.39 6.281s-3.872 1.438-8.73 4.709c-.52.349.257 1.631.705 1.477 8.056-2.773 12.728-3.422 16.003-3.8.483-.056.672-.416.348-.772-2.545-2.789-7.853-6.5-15.116-9.053z"/></svg>',
    'surprised' => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 64 64"><circle fill="#FDCA47" cx="32" cy="32" r="30"/><path fill="#F9B700" d="M51.654 9.346A29.869 29.869 0 0 1 59 29c0 16.568-13.432 30-30 30a29.867 29.867 0 0 1-19.654-7.346C14.846 57.99 22.951 62 32 62c16.568 0 30-13.432 30-30 0-9.047-4.012-17.152-10.346-22.654z"/><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFE8BB" d="M6.418 20.5C5.301 24.242 13 11 25 6.084c5.834-2.391-13.832-1.5-18.582 14.416z"/><circle fill="#302C3B" cx="32" cy="49" r="9"/><path fill="#FFF" d="M26 46c1.197-2.391 3.436-4 5.998-4 2.566 0 4.803 1.607 6.002 4H26z"/><path fill="#917524" d="M52.344 14.076c-4.158-3.201-10.314-4.814-13.779-3.832-.732.208-1.167 2.92-.535 2.826 4.854-.725 10.158.131 14.184 2.838.539.363.755-1.351.13-1.832zm-40.688.003c4.156-3.204 10.313-4.816 13.779-3.834.73.206 1.166 2.921.533 2.825-4.854-.723-10.157.131-14.182 2.838-.54.366-.756-1.349-.13-1.829z"/><path fill="#E8A329" d="M31 29c0 6.353-5.152 11.5-11.5 11.5C13.147 40.5 8 35.353 8 29c0-6.348 5.147-11.5 11.5-11.5C25.848 17.5 31 22.652 31 29z"/><circle fill="#E8A329" cx="44.5" cy="29" r="11.5"/><circle fill="#FFF" cx="19.5" cy="29" r="10"/><circle fill="#FFF" cx="44.5" cy="29" r="10"/><path fill="#302C3B" d="M24 29a4.501 4.501 0 1 1-9.002-.002A4.501 4.501 0 0 1 24 29zm25 0a4.501 4.501 0 1 1-9.002-.002A4.501 4.501 0 0 1 49 29z"/></svg>',
    'cry' => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 64 64"><circle fill="#FDCA47" cx="32" cy="32" r="30"/><path fill="#F9B700" d="M51.654 9.346C56.225 14.612 59 21.481 59 29c0 16.568-13.432 30-30 30a29.863 29.863 0 0 1-19.654-7.346C14.846 57.99 22.952 62 32 62c16.568 0 30-13.432 30-30 0-9.046-4.012-17.152-10.346-22.654z"/><path fill-rule="evenodd" clip-rule="evenodd" fill="#FFE8BB" d="M6.418 20.5C5.301 24.243 13 11 25 6.084c5.834-2.39-13.832-1.5-18.582 14.416z"/><path fill="#302C3B" d="M44.736 46c-1.395-3.594-4.779-6-12.738-6-7.955 0-11.342 2.406-12.734 6-.744 1.92.32 5 .32 5C22.74 60.219 26.9 51 32 51c5.083 0 9.259 9.219 12.414 0 0 0 1.066-3.08.322-5z"/><path fill="#FFF" d="M40.973 44.965c.072-.26-.004-.619-.17-.797 0 0-2.012-2.168-8.804-2.168-6.791 0-8.801 2.168-8.801 2.168-.164.178-.242.537-.172.797l.154.563c.072.259.307.472.524.472h16.591c.217 0 .453-.213.523-.473l.155-.562z"/><path fill="#0FB4D4" d="M44.432 60.469h6.834c8.201-9.916-1.543-20.025.898-29.799l-6.834 2.545c-3.15 9.49 7.303 17.338-.898 27.254z"/><path fill="#0FB4D4" d="M19.567 60.469h-6.834c-8.201-9.916 1.543-20.025-.898-29.799l6.834 2.545c3.15 9.49-7.303 17.338.898 27.254z"/><path fill="#302C3B" d="M35.914 30.275c4.213 7.953 12.695 7.951 16.91 0 .209-.4-.34-.58-1.01-1.035-4.225 3.32-11.059 3.013-14.891.002-.669.454-1.216.633-1.009 1.033zm-24.738 0c4.215 7.953 12.697 7.951 16.912 0 .207-.4-.34-.58-1.01-1.035-4.225 3.32-11.061 3.013-14.893.002-.669.454-1.216.633-1.009 1.033z"/><path fill="#917524" d="M11.454 25.169c5.242.237 11.211-1.973 13.488-4.765.477-.591-.721-3.061-1.184-2.622-3.561 3.379-8.396 5.719-13.248 5.811-.65.013.157 1.541.944 1.576zm41.087-.002c-5.242.238-11.209-1.971-13.486-4.764-.48-.588.721-3.062 1.184-2.621 3.561 3.376 8.396 5.719 13.246 5.81.652.012-.155 1.541-.944 1.575z"/></svg>',
    'anger' => '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 64 64"><circle fill="#FF4D4D" cx="32" cy="32" r="30"/><path fill="#E03636" d="M51.654 9.346A29.869 29.869 0 0 1 59 29c0 16.568-13.432 30-30 30a29.867 29.867 0 0 1-19.654-7.346C14.847 57.99 22.952 62 32 62c16.568 0 30-13.432 30-30 0-9.047-4.011-17.152-10.346-22.654z"/><path fill-rule="evenodd" clip-rule="evenodd" fill="#F89987" d="M6.418 20.5C5.302 24.242 13.001 11 25.001 6.084c5.833-2.391-13.833-1.5-18.583 14.416z"/><path fill="#1A1626" d="M40.988 49.665c-5.793-4.8-12.219-4.771-17.977 0-.693.573-1.318-.421-.848-1.339 1.754-3.435 5.299-6.492 9.836-6.492 4.539 0 8.082 3.058 9.836 6.492.472.918-.153 1.912-.847 1.339z"/><path fill="#FFF" d="M10.166 24.935c-1.548 4.728.646 9.975 5.27 12.128 4.615 2.154 10.039.467 12.668-3.746l-6.895-7.721-11.043-.661z"/><path fill="#1A1626" d="M14.24 25.775a5.825 5.825 0 1 0 10.558 4.924c.862-1.851-9.694-6.775-10.558-4.924z"/><path fill="#1A1626" d="M10.166 24.936c1.586-1.035 3.473-1.463 5.387-1.455 1.92.018 3.84.469 5.59 1.279 1.744.801 3.346 1.98 4.596 3.441 1.24 1.461 2.162 3.23 2.365 5.115-1.33-1.33-2.605-2.43-3.971-3.387a27.277 27.277 0 0 0-4.25-2.449 27.67 27.67 0 0 0-4.621-1.666c-1.616-.431-3.225-.697-5.096-.878z"/><path fill="#FFF" d="M53.834 24.935c1.547 4.728-.646 9.975-5.27 12.128-4.615 2.154-10.039.467-12.668-3.746l6.895-7.721 11.043-.661z"/><path fill="#1A1626" d="M49.76 25.775a5.825 5.825 0 1 1-10.558 4.924c-.862-1.851 9.694-6.775 10.558-4.924z"/><path fill="#1A1626" d="M53.834 24.936c-1.586-1.035-3.473-1.463-5.387-1.455-1.92.018-3.84.469-5.59 1.279-1.744.801-3.346 1.98-4.596 3.441-1.24 1.461-2.162 3.23-2.365 5.115 1.33-1.33 2.605-2.43 3.971-3.387a27.277 27.277 0 0 1 4.25-2.449 27.67 27.67 0 0 1 4.621-1.666c1.616-.431 3.225-.697 5.096-.878z"/></svg>',
];
?>

<!-- POST REACTIONS -->
<div class="post-reactions" id="post-reactions-<?= $_rxLinkId ?>">
  <p class="post-reactions__label">Jak oceniasz ten wpis?</p>
  <div class="post-reactions__row">
    <?php foreach ($_rxLabels as $_rxType => $_rxLabel): ?>
      <button type="button"
              class="post-reactions__btn<?= ($_rxMine === $_rxType) ? ' post-reactions__btn--active' : '' ?>"
              data-reaction="<?= htmlspecialchars($_rxType) ?>"
              data-link-id="<?= $_rxLinkId ?>"
              aria-label="<?= htmlspecialchars($_rxLabel) ?>"
              aria-pressed="<?= ($_rxMine === $_rxType) ? 'true' : 'false' ?>"
              title="<?= htmlspecialchars($_rxLabel) ?>">
        <span class="post-reactions__icon"><?= $_rxSvg[$_rxType] ?></span>
        <span class="post-reactions__count" data-type="<?= htmlspecialchars($_rxType) ?>"><?= (int)($_rxCounts[$_rxType] ?? 0) ?></span>
        <span class="post-reactions__name"><?= htmlspecialchars($_rxLabel) ?></span>
      </button>
    <?php endforeach; ?>
  </div>
  <p class="post-reactions__feedback" aria-live="polite"></p>
</div>

<?php static $_rxCssLoaded = false; if (!$_rxCssLoaded): $_rxCssLoaded = true; ?>
<script>
(function () {
  if (!document.querySelector('link[data-rx-css]')) {
    var l = document.createElement('link');
    l.rel = 'stylesheet';
    l.href = <?= json_encode($_rxBasePath . '/assets/post_react/post_react.css') ?>;
    l.setAttribute('data-rx-css', '1');
    document.head.appendChild(l);
  }
})();
</script>
<?php endif; ?>

<script>
(function () {
  var REACT_URL = <?= json_encode($_rxBasePath . '/__react') ?>;
  var container = document.getElementById('post-reactions-<?= $_rxLinkId ?>');
  if (!container) return;

  var feedback = container.querySelector('.post-reactions__feedback');
  var buttons  = container.querySelectorAll('.post-reactions__btn');

  function setFeedback(msg, ok) {
    feedback.textContent = msg;
    feedback.style.color = ok ? '#28a745' : '#dc3545';
    if (msg) setTimeout(function () { feedback.textContent = ''; }, 3000);
  }

  function updateCounts(counts) {
    container.querySelectorAll('.post-reactions__count').forEach(function (el) {
      var type = el.dataset.type;
      if (counts[type] !== undefined) el.textContent = counts[type];
    });
  }

  function setActive(type) {
    buttons.forEach(function (btn) {
      var active = btn.dataset.reaction === type;
      btn.classList.toggle('post-reactions__btn--active', active);
      btn.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }

  function setAllDisabled(state) {
    buttons.forEach(function (btn) { btn.disabled = state; });
  }

  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      var reaction = btn.dataset.reaction;
      var linkId   = parseInt(btn.dataset.linkId, 10);

      // Optimistic UI — zaznacz od razu
      var wasActive = btn.classList.contains('post-reactions__btn--active');
      setActive(wasActive ? null : reaction);
      setAllDisabled(true);

      fetch(REACT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ link_id: linkId, reaction_type: reaction })
      })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        if (data.ok) {
          updateCounts(data.counts);
          setActive(data.my_reaction || null);
          setFeedback(data.my_reaction ? 'Dziękujemy za ocenę!' : '', true);
        } else {
          // Cofnij optimistic UI
          setActive(wasActive ? reaction : null);
          setFeedback('Nie udało się zapisać oceny.', false);
        }
      })
      .catch(function () {
        setActive(wasActive ? reaction : null);
        setFeedback('Błąd połączenia.', false);
      })
      .finally(function () { setAllDisabled(false); });
    });
  });
})();
</script>
