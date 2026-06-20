<?php
  $pageTitle = 'Wygląd — RedirectCMS';
  $extraHead = '<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.css" />';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>


  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-10">

        <?php if (!empty($successMsg)): ?>
          <div class="alert border border-success bg-success-subtle text-success-emphasis fade show" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-check-circle me-2" viewBox="0 0 16 16">
              <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
              <path d="m10.97 4.97-.02.022-3.473 4.425-2.093-2.094a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-1.071-1.05"/>
            </svg>
            Ustawienia wyglądu zostały zapisane.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <div class="card shadow-sm">
          <div class="card-header bg-info text-white">
            <h2 class="mb-0">
              <svg xmlns="http://www.w3.org/2000/svg" width="34" height="34" fill="currentColor" class="bi bi-palette me-2" viewBox="0 0 16 16">
                <path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M5.5 7a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                <path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8m-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.417.845-.578 1.272-.618.404-.038.812.026 1.16.104.343.077.702.186 1.025.284l.028.008c.346.105.658.199.953.266.653.148.904.083.991.024C14.717 9.38 15 9.161 15 8a7 7 0 1 0-7 7"/>
              </svg>
              Wygląd
            </h2>
          </div>
          <div class="card-body">
            <form method="post" action="<?= $basePath ?>/admin/index.php?action=appearance" id="appearanceForm">
              <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
              <input type="hidden" name="sidebar_widgets_json" id="sidebarWidgetsJson" value="<?= htmlspecialchars(json_encode($sidebarWidgets)) ?>">

              <!-- Szablon bloga -->
              <h5 class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-brush me-2" viewBox="0 0 16 16">
                  <path d="M15.825.12a.5.5 0 0 1 .132.584c-1.53 3.43-4.743 8.17-7.095 10.64a6.1 6.1 0 0 1-2.373 1.534c-.018.227-.06.538-.16.868-.201.659-.667 1.479-1.708 1.74a8.1 8.1 0 0 1-3.078.132 3.7 3.7 0 0 1-.23-.02 3 3 0 0 1-.097-.025c-.073-.01-.147-.025-.215-.045-.976-.2-1.613-.7-1.93-1.34-.322-.64-.32-1.393-.32-1.393L0 13a5.7 5.7 0 0 0 1.098-.228A4.8 4.8 0 0 0 2.3 12.2c.37-.33.72-.64 1.09-.95a4.7 4.7 0 0 1 1.77-.85c.17-.04.33-.07.5-.08l.01-.01c.28-.31.57-.62.86-.93a20 20 0 0 1 2.41-2.09c2.38-1.78 5.29-3.36 7.13-4.59.15-.09.26-.18.35-.26.1-.08.16-.12.16-.12z"/>
                </svg>
                Szablon bloga
              </h5>

              <?php if (!empty($availableThemes)): ?>
                <div class="mb-4">
                  <label for="blog_theme" class="form-label">Aktywny szablon</label>
                  <select class="form-select" id="blog_theme" name="blog_theme">
                    <?php foreach ($availableThemes as $themeKey => $themeData): ?>
                      <option value="<?= htmlspecialchars($themeKey) ?>" <?= $themeKey === $blogTheme ? 'selected' : '' ?>>
                        <?= htmlspecialchars($themeData['name'] ?? $themeKey) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <div class="form-text">Zmiana szablonu resetuje kolory do wartości domyślnych nowego szablonu.</div>
                </div>
              <?php endif; ?>

              <hr class="my-4" />

              <!-- Ustawienia bloga -->
              <h5 class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-journal-text me-2" viewBox="0 0 16 16">
                  <path d="M5 10.5a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0-2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                  <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2"/>
                  <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1z"/>
                </svg>
                Ustawienia bloga
              </h5>
              <p class="text-muted small mb-3">Mają zastosowanie gdy tryb strony głównej jest ustawiony na <strong>Blog</strong>.</p>

              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label for="blog_posts_per_page" class="form-label">Wpisów na stronę</label>
                  <input type="number" class="form-control" id="blog_posts_per_page" name="blog_posts_per_page"
                         value="<?= (int)($blogPostsPerPage ?? 10) ?>" min="1" max="50">
                  <div class="form-text">Domyślnie: 10</div>
                </div>
                <div class="col-md-4">
                  <label for="blog_description_length" class="form-label">Długość opisu (znaki)</label>
                  <input type="number" class="form-control" id="blog_description_length" name="blog_description_length"
                         value="<?= (int)($blogDescLength ?? 200) ?>" min="50" max="1000">
                  <div class="form-text">Domyślnie: 200</div>
                </div>
                <div class="col-md-4 d-flex align-items-end pb-1">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="blog_show_images" name="blog_show_images" value="1"
                           <?= !empty($blogShowImages) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="blog_show_images">Wyświetlaj miniaturki</label>
                    <div class="form-text">Obrazki Open Graph jako miniatury kart</div>
                  </div>
                </div>
              </div>

              <h6 class="mb-3 text-secondary">Slider</h6>
              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label for="slider_count" class="form-label">Liczba slajdów</label>
                  <input type="number" class="form-control" id="slider_count" name="slider_count"
                         value="<?= (int)($sliderCount ?? 3) ?>" min="1" max="5">
                  <div class="form-text">1–5 slajdów</div>
                </div>
                <div class="col-md-4">
                  <label for="slider_type" class="form-label">Rodzaj slajdów</label>
                  <select class="form-select" id="slider_type" name="slider_type" onchange="toggleSliderDays()">
                    <option value="newest"  <?= ($sliderType ?? 'newest') === 'newest'  ? 'selected' : '' ?>>Najnowsze wpisy</option>
                    <option value="random"  <?= ($sliderType ?? 'newest') === 'random'  ? 'selected' : '' ?>>Losowe wpisy</option>
                    <option value="popular" <?= ($sliderType ?? 'newest') === 'popular' ? 'selected' : '' ?>>Najpopularniejsze</option>
                  </select>
                </div>
                <div class="col-md-4" id="slider-days-wrap" style="<?= ($sliderType ?? 'newest') !== 'popular' ? 'display:none' : '' ?>">
                  <label for="slider_days" class="form-label">Okres popularności</label>
                  <select class="form-select" id="slider_days" name="slider_days">
                    <option value="0"  <?= (int)($sliderDays ?? 0) === 0  ? 'selected' : '' ?>>Cały czas</option>
                    <option value="7"  <?= (int)($sliderDays ?? 0) === 7  ? 'selected' : '' ?>>Ostatnie 7 dni</option>
                    <option value="30" <?= (int)($sliderDays ?? 0) === 30 ? 'selected' : '' ?>>Ostatnie 30 dni</option>
                  </select>
                  <div class="form-text">Dla trybu popularnych</div>
                </div>
              </div>

              <hr class="my-4" />

              <!-- Kolory szablonu -->
              <h5 class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-palette me-2" viewBox="0 0 16 16">
                  <path d="M8 5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3m4 3a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3M5 6.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0m.5 6.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3"/>
                  <path d="M16 8c0 3.15-1.866 2.585-3.567 2.07C11.42 9.763 10.465 9.473 10 10c-.603.683-.475 1.819-.351 2.92C9.826 14.495 9.996 16 8 16a8 8 0 1 1 8-8m-8 7c.611 0 .654-.171.655-.176.078-.146.124-.464.07-1.119-.014-.168-.037-.37-.061-.591-.052-.464-.112-1.005-.118-1.462-.01-.707.083-1.61.704-2.314.369-.42.845-.57 1.342-.571.499 0 1.002.127 1.51.278l.08.023c.482.137.964.276 1.448.276.612 0 .654-.171.655-.176C15.947 15.963 8.03 15.963 8 15m0-15a7 7 0 1 0 0 14A7 7 0 0 0 8 0"/>
                </svg>
                Kolory szablonu
              </h5>

              <?php if (!empty($themeColors)): ?>
                <div class="alert bg-info-subtle border border-info text-info-emphasis mb-3">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                  </svg>
                  Kolory aktywnego szablonu: <strong><?= htmlspecialchars($availableThemes[$blogTheme]['name'] ?? $blogTheme) ?></strong>.
                  Zmiany kolorów widoczne są natychmiast na blogu po zapisaniu.
                </div>

                <div class="row g-3 mb-3">
                  <?php foreach ($themeColors as $color): ?>
                    <div class="col-sm-6 col-lg-4">
                      <label class="form-label small fw-medium"><?= htmlspecialchars($color['label']) ?></label>
                      <div class="d-flex align-items-center gap-2">
                        <input type="text"
                               class="form-control coloris-input"
                               data-coloris
                               name="theme_color_<?= htmlspecialchars($color['key']) ?>"
                               id="theme_color_<?= htmlspecialchars($color['key']) ?>"
                               value="<?= htmlspecialchars($savedThemeColors[$color['key']] ?? $color['default']) ?>"
                               placeholder="<?= htmlspecialchars($color['default']) ?>"
                               autocomplete="off">
                        <button type="button" class="btn btn-sm btn-outline-secondary theme-color-reset flex-shrink-0"
                                data-target="theme_color_<?= htmlspecialchars($color['key']) ?>"
                                data-default="<?= htmlspecialchars($color['default']) ?>"
                                title="Przywróć domyślny">
                          <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-arrow-counterclockwise" viewBox="0 0 16 16">
                            <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/>
                            <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div class="mb-3">
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="resetAllColors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-counterclockwise me-1" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M8 3a5 5 0 1 1-4.546 2.914.5.5 0 0 0-.908-.417A6 6 0 1 0 8 2z"/>
                      <path d="M8 4.466V.534a.25.25 0 0 0-.41-.192L5.23 2.308a.25.25 0 0 0 0 .384l2.36 1.966A.25.25 0 0 0 8 4.466"/>
                    </svg>
                    Przywróć wszystkie kolory do domyślnych
                  </button>
                </div>

              <?php else: ?>
                <div class="alert bg-secondary-subtle border border-secondary text-secondary-emphasis">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-2" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16"/>
                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0"/>
                  </svg>
                  Aktywny szablon nie obsługuje personalizacji kolorów lub nie zdefiniował żadnych kolorów w <code>theme.json</code>.
                </div>
              <?php endif; ?>

              <hr class="my-4" />

              <!-- Strona kontaktowa -->
              <h5 class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-envelope-at me-2" viewBox="0 0 16 16">
                  <path d="M2 2a2 2 0 0 0-2 2v8.01A2 2 0 0 0 2 14h5.5a.5.5 0 0 0 0-1H2a1 1 0 0 1-.966-.741l5.64-3.471L8 9.583l7-4.2V8.5a.5.5 0 0 0 1 0V4a2 2 0 0 0-2-2zm3.708 6.208L1 11.105V5.383zM1 4.217V4a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v.217l-7 4.2z"/>
                  <path d="M14.247 14.269c1.01 0 1.587-.857 1.587-2.025v-.21C15.834 10.43 14.64 9 12.52 9h-.035C10.42 9 9 10.36 9 12.432v.214C9 14.82 10.438 16 12.358 16h.044c.594 0 1.018-.074 1.237-.175v-.73c-.245.11-.673.18-1.18.18h-.044c-1.334 0-2.571-.788-2.571-2.655v-.157c0-1.657 1.058-2.724 2.64-2.724h.04c1.535 0 2.484 1.05 2.484 2.326v.118c0 .975-.324 1.39-.639 1.39-.232 0-.41-.148-.41-.42v-2.19h-.906v.569h-.03c-.084-.298-.368-.63-.954-.63-.778 0-1.259.555-1.259 1.4v.528c0 .892.49 1.434 1.26 1.434.471 0 .896-.227 1.014-.643h.043c.118.42.617.648 1.12.648m-2.453-1.588v-.227c0-.546.227-.791.573-.791.297 0 .572.192.572.708v.367c0 .573-.253.744-.564.744-.354 0-.581-.215-.581-.8Z"/>
                </svg>
                Strona kontaktowa
              </h5>

              <div class="mb-3">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" role="switch" id="contact_page_enabled" name="contact_page_enabled"
                         <?= !empty($contactPageEnabled) ? 'checked' : '' ?>>
                  <label class="form-check-label" for="contact_page_enabled">
                    Włącz stronę kontaktową (<code><?= htmlspecialchars($basePath) ?>/?page=contact</code>)
                  </label>
                </div>
                <div class="form-text">Udostępnij formularz kontaktowy dla odwiedzających Twojego bloga</div>
              </div>

              <div class="mb-3">
                <label for="contact_page_email" class="form-label">Adres e-mail odbiorcy wiadomości</label>
                <input type="email" class="form-control" id="contact_page_email" name="contact_page_email"
                       value="<?= htmlspecialchars($contactPageEmail) ?>"
                       placeholder="np. kontakt@mojastrona.pl">
                <div class="form-text">Na ten adres będą wysyłane wiadomości z formularza kontaktowego</div>
              </div>

              <div class="mb-3">
                <label for="contact_page_intro" class="form-label">Tekst wprowadzający</label>
                <textarea class="form-control" id="contact_page_intro" name="contact_page_intro" rows="3"
                          placeholder="np. Wszelkie pytania i problemy można zgłaszać pisząc na adres kontakt@mojastrona.pl lub za pomocą poniższego formularza."><?= htmlspecialchars($contactPageIntro) ?></textarea>
                <div class="form-text">Tekst wyświetlany nad formularzem kontaktowym na stronie publicznej</div>
              </div>

              <hr class="my-4" />

              <!-- Profile społecznościowe -->
              <h5 class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-share me-2" viewBox="0 0 16 16">
                  <path d="M13.5 1a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3M11 2.5a2.5 2.5 0 1 1 .603 1.628l-6.718 3.12a2.5 2.5 0 0 1 0 1.504l6.718 3.12a2.5 2.5 0 1 1-.488.876l-6.718-3.12a2.5 2.5 0 1 1 0-3.256l6.718-3.12A2.5 2.5 0 0 1 11 2.5m-8.5 4a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m11 5.5a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3"/>
                </svg>
                Profile społecznościowe
              </h5>
              <div class="form-text mb-3">Linki używane w widgecie sidebara i stopce szablonu. Pozostaw puste aby ukryć.</div>

              <?php
                $socialNetworks = [
                  'facebook'  => ['label' => 'Facebook',   'placeholder' => 'https://facebook.com/twojaprofil',   'icon' => 'bi-facebook'],
                  'instagram' => ['label' => 'Instagram',  'placeholder' => 'https://instagram.com/twojaprofil',  'icon' => 'bi-instagram'],
                  'twitter'   => ['label' => 'Twitter / X','placeholder' => 'https://x.com/twojaprofil',          'icon' => 'bi-twitter-x'],
                  'youtube'   => ['label' => 'YouTube',    'placeholder' => 'https://youtube.com/@twojkanał',     'icon' => 'bi-youtube'],
                  'tiktok'    => ['label' => 'TikTok',     'placeholder' => 'https://tiktok.com/@twojaprofil',    'icon' => 'bi-tiktok'],
                  'linkedin'  => ['label' => 'LinkedIn',   'placeholder' => 'https://linkedin.com/in/twojaprofil','icon' => 'bi-linkedin'],
                  'pinterest' => ['label' => 'Pinterest',  'placeholder' => 'https://pinterest.com/twojaprofil',  'icon' => 'bi-pinterest'],
                ];
              ?>
              <div class="row g-3">
                <?php foreach ($socialNetworks as $key => $net): ?>
                  <div class="col-sm-6">
                    <label for="social_<?= $key ?>" class="form-label small fw-medium">
                      <i class="bi <?= $net['icon'] ?> me-1"></i><?= $net['label'] ?>
                    </label>
                    <input type="url" class="form-control form-control-sm" id="social_<?= $key ?>" name="social_<?= $key ?>"
                           value="<?= htmlspecialchars($socialLinks[$key] ?? '') ?>"
                           placeholder="<?= htmlspecialchars($net['placeholder']) ?>">
                  </div>
                <?php endforeach; ?>
                <div class="col-sm-6">
                  <label for="social_custom_url" class="form-label small fw-medium">
                    <i class="bi bi-globe2 me-1"></i>Własna strona / inny link
                  </label>
                  <input type="url" class="form-control form-control-sm" id="social_custom_url" name="social_custom_url"
                         value="<?= htmlspecialchars($socialLinks['custom_url'] ?? '') ?>"
                         placeholder="https://mojastrona.pl">
                </div>
              </div>

              <hr class="my-4" />

              <!-- Rozmiary obrazków -->
              <h5 class="mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-aspect-ratio me-2" viewBox="0 0 16 16">
                  <path d="M0 3.5A1.5 1.5 0 0 1 1.5 2h13A1.5 1.5 0 0 1 16 3.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 12.5zM1.5 3a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5z"/>
                  <path d="M2 4.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1H3v2.5a.5.5 0 0 1-1 0zm12 7a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1 0-1H13V8.5a.5.5 0 0 1 1 0z"/>
                </svg>
                Wymagane rozmiary obrazków
              </h5>

              <?php if (!empty($themeImageSizes)): ?>
                <p class="text-muted small mb-3">
                  Szablon <strong><?= htmlspecialchars($availableThemes[$blogTheme]['name'] ?? $blogTheme) ?></strong> wymaga następujących rozmiarów obrazków.
                  Użyj przycisku poniżej, aby wygenerować przycięte warianty dla wszystkich istniejących obrazków.
                </p>
                <div class="row g-3 mb-3">
                  <?php foreach ($themeImageSizes as $sizeKey => $size): ?>
                    <div class="col-sm-6 col-lg-3">
                      <div class="card border h-100">
                        <div class="card-body p-3 text-center">
                          <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-image text-muted mb-2" viewBox="0 0 16 16">
                            <path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/>
                            <path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/>
                          </svg>
                          <div class="fw-medium small"><?= htmlspecialchars($size['label']) ?></div>
                          <div class="text-muted small mt-1"><?= (int)$size['width'] ?> × <?= (int)$size['height'] ?>px</div>
                          <span class="badge <?= $size['crop'] ? 'bg-warning text-dark' : 'bg-secondary' ?> mt-1">
                            <?= $size['crop'] ? 'Przycinany' : 'Dopasowywany' ?>
                          </span>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <div class="d-flex align-items-center gap-3 mt-2">
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="regenerateImagesBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-clockwise me-1" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/>
                      <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/>
                    </svg>
                    Regeneruj miniatury
                  </button>
                  <span id="regenerateImagesResult" class="small"></span>
                </div>
              <?php else: ?>
                <div class="alert bg-secondary-subtle border border-secondary text-secondary-emphasis">
                  Aktywny szablon nie definiuje wymaganych rozmiarów obrazków.
                </div>
              <?php endif; ?>

              <?php if ($themeSuportsSidebar): ?>
              <hr class="my-4" />

              <!-- Sidebar -->
              <h5 class="mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-layout-sidebar-reverse me-2" viewBox="0 0 16 16">
                  <path d="M16 3a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2zm-5-1v12H2a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zm1 0h2a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1h-2z"/>
                </svg>
                Sidebar
              </h5>
              <p class="text-muted small mb-3">Skonfiguruj widgety wyświetlane w bocznym panelu szablonu.</p>

              <div id="sidebarWidgetsList">
                <?php foreach ($sidebarWidgets as $idx => $widget): ?>
                  <div class="card mb-3 sidebar-widget-item" data-index="<?= $idx ?>">
                    <div class="card-header d-flex justify-content-between align-items-center py-2">
                      <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary widget-type-badge"><?= htmlspecialchars($widget['type'] ?? '') ?></span>
                        <span class="fw-medium widget-title-display"><?= htmlspecialchars($widget['title'] ?? '') ?></span>
                      </div>
                      <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary widget-move-up" title="Przesuń wyżej">↑</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary widget-move-down" title="Przesuń niżej">↓</button>
                        <button type="button" class="btn btn-sm btn-outline-danger widget-remove" title="Usuń widget">×</button>
                      </div>
                    </div>
                    <div class="card-body py-2">
                      <?php include __DIR__ . '/partials/sidebar_widget_fields.php'; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="mb-3">
                <label class="form-label fw-medium small">Dodaj widget:</label>
                <div class="d-flex flex-wrap gap-2">
                  <?php
                    $widgetTypes = [
                      'popular_posts' => 'Popularne wpisy',
                      'random_posts'  => 'Losowe wpisy',
                      'tag_cloud'     => 'Chmurka tagów',
                      'categories'    => 'Kategorie',
                      'social_links'  => 'Social media',
                      'custom_html'   => 'Własny HTML',
                    ];
                  ?>
                  <?php foreach ($widgetTypes as $type => $label): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary add-widget-btn" data-type="<?= $type ?>">
                      + <?= $label ?>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>

              <?php endif; // themeSuportsSidebar ?>

              <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4 pt-3 border-top">
                <a href="<?= $basePath ?>/admin/index.php" class="btn btn-secondary">Anuluj</a>
                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script src="https://cdn.jsdelivr.net/gh/mdbassit/Coloris@latest/dist/coloris.min.js"></script>
  <script>
  function toggleSliderDays() {
    const type = document.getElementById('slider_type');
    const wrap = document.getElementById('slider-days-wrap');
    if (type && wrap) wrap.style.display = type.value === 'popular' ? '' : 'none';
  }

  document.addEventListener('DOMContentLoaded', function() {

    // ===== COLORIS INIT =====
    if (typeof Coloris !== 'undefined') {
      Coloris({
        el: '.coloris-input',
        theme: 'light',
        margin: 8,
        borderRadius: 6,
        format: 'hex',
        closeButton: true,
        closeLabel: 'Zamknij',
        clearLabel: 'Wyczyść',
        swatches: [
          '#ffffff', '#f8f9fa', '#dee2e6', '#adb5bd',
          '#6c757d', '#495057', '#343a40', '#000000',
          '#6f42c1', '#7952b3', '#e83e8c', '#fd7e14',
          '#ffc107', '#198754', '#0dcaf0', '#0d6efd',
          '#005f73', '#0a9396', '#94d2bd', '#e9d8a6',
          '#ee9b00', '#ca6702', '#bb3e03', '#9b2226'
        ]
      });
    }

    // Reset pojedynczego koloru do domyślnego
    document.querySelectorAll('.theme-color-reset').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const targetId = this.dataset.target;
        const defaultVal = this.dataset.default;
        const input = document.getElementById(targetId);
        if (input) {
          input.value = defaultVal;
          // Poinformuj Coloris o zmianie
          input.dispatchEvent(new Event('input', { bubbles: true }));
          input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      });
    });

    // Reset wszystkich kolorów
    const resetAllBtn = document.getElementById('resetAllColors');
    if (resetAllBtn) {
      resetAllBtn.addEventListener('click', function() {
        document.querySelectorAll('.theme-color-reset').forEach(btn => btn.click());
      });
    }

    // ===== REGENERATE IMAGES =====
    const regenerateBtn = document.getElementById('regenerateImagesBtn');
    if (regenerateBtn) {
      regenerateBtn.addEventListener('click', async function() {
        const resultSpan = document.getElementById('regenerateImagesResult');
        regenerateBtn.disabled = true;
        regenerateBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Przetwarzanie...';
        if (resultSpan) resultSpan.textContent = '';
        try {
          const formData = new FormData();
          formData.append('csrf', document.querySelector('input[name="csrf"]').value);
          const basePath = '<?= $basePath ?>';
          const res = await fetch(basePath + '/admin/index.php?action=regenerateImages', { method: 'POST', body: formData });
          const data = await res.json();
          if (resultSpan) {
            resultSpan.className = 'small ' + (data.success ? 'text-success' : 'text-danger');
            resultSpan.textContent = data.message;
          }
        } catch(err) {
          if (resultSpan) { resultSpan.className = 'small text-danger'; resultSpan.textContent = 'Błąd: ' + err.message; }
        } finally {
          regenerateBtn.disabled = false;
          regenerateBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-arrow-clockwise me-1" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2z"/><path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466"/></svg>Regeneruj miniatury';
        }
      });
    }

    // ===== SIDEBAR WIDGET MANAGEMENT =====
    function escAttr(s) {
      return String(s ?? '').replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }
    function escHtml(s) {
      return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    function serializeSidebarWidgets() {
      const jsonField = document.getElementById('sidebarWidgetsJson');
      if (!jsonField) return;
      const widgets = [];
      document.querySelectorAll('#sidebarWidgetsList .sidebar-widget-item').forEach(function(card) {
        const widget = {};
        card.querySelectorAll('[data-field]').forEach(function(el) {
          if (el.type === 'checkbox') {
            widget[el.dataset.field] = el.checked ? 1 : 0;
          } else {
            widget[el.dataset.field] = el.value;
          }
        });
        widgets.push(widget);
      });
      jsonField.value = JSON.stringify(widgets);
    }

    const widgetFieldsHtml = {
      popular_posts: function(d) {
        d = Object.assign({ count: 5, period: 'all', category_id: 0 }, d || {});
        const periods = { all: 'Wszystkie czasy', today: 'Dziś', '7d': 'Ostatnie 7 dni', '30d': 'Ostatnie 30 dni' };
        const periodOptions = Object.entries(periods).map(([v, l]) =>
          `<option value="${v}" ${d.period == v ? 'selected' : ''}>${escHtml(l)}</option>`
        ).join('');
        return `<div class="row g-2 mt-0">
          <div class="col-sm-4"><label class="form-label form-label-sm">Liczba wpisów</label>
            <input type="number" class="form-control form-control-sm" data-field="count" value="${escAttr(d.count)}" min="1" max="10"></div>
          <div class="col-sm-4"><label class="form-label form-label-sm">Okres</label>
            <select class="form-select form-select-sm" data-field="period">${periodOptions}</select></div>
          <div class="col-sm-4"><label class="form-label form-label-sm">ID kategorii <span class="text-muted">(0=wszystkie)</span></label>
            <input type="number" class="form-control form-control-sm" data-field="category_id" value="${escAttr(d.category_id)}" min="0"></div>
        </div>`;
      },
      random_posts: function(d) {
        d = Object.assign({ count: 5 }, d || {});
        return `<div class="row g-2 mt-0"><div class="col-sm-4"><label class="form-label form-label-sm">Liczba wpisów</label>
          <input type="number" class="form-control form-control-sm" data-field="count" value="${escAttr(d.count)}" min="1" max="10"></div></div>`;
      },
      tag_cloud: function(d) {
        d = Object.assign({ max_tags: 30 }, d || {});
        return `<div class="row g-2 mt-0"><div class="col-sm-4"><label class="form-label form-label-sm">Maks. liczba tagów</label>
          <input type="number" class="form-control form-control-sm" data-field="max_tags" value="${escAttr(d.max_tags)}" min="5" max="100"></div></div>`;
      },
      categories: function(d) {
        d = Object.assign({ show_count: 1 }, d || {});
        return `<div class="form-check mt-1"><input class="form-check-input" type="checkbox" data-field="show_count" ${d.show_count ? 'checked' : ''}>
          <label class="form-check-label small">Pokaż licznik wpisów obok kategorii</label></div>`;
      },
      social_links: function() {
        return `<p class="text-muted small mb-0">Wyświetla linki social media skonfigurowane powyżej.</p>`;
      },
      custom_html: function(d) {
        d = Object.assign({ html: '' }, d || {});
        return `<div class="mb-1"><label class="form-label form-label-sm">Zawartość HTML</label>
          <textarea class="form-control form-control-sm font-monospace" data-field="html" rows="4" placeholder="<p>Własny HTML...</p>">${escHtml(d.html)}</textarea></div>`;
      }
    };

    const widgetLabels = {
      popular_posts: 'Popularne wpisy', random_posts: 'Losowe wpisy',
      tag_cloud: 'Chmurka tagów', categories: 'Kategorie',
      social_links: 'Social media', custom_html: 'Własny HTML'
    };

    function buildWidgetCard(type, data) {
      data = data || {};
      const card = document.createElement('div');
      card.className = 'card mb-3 sidebar-widget-item';
      const extra = widgetFieldsHtml[type] ? widgetFieldsHtml[type](data) : '';
      card.innerHTML = `
        <div class="card-header d-flex justify-content-between align-items-center py-2">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-secondary widget-type-badge">${escHtml(type)}</span>
            <span class="fw-medium widget-title-display">${escHtml(data.title || '')}</span>
          </div>
          <div class="d-flex gap-1">
            <button type="button" class="btn btn-sm btn-outline-secondary widget-move-up" title="Przesuń wyżej">↑</button>
            <button type="button" class="btn btn-sm btn-outline-secondary widget-move-down" title="Przesuń niżej">↓</button>
            <button type="button" class="btn btn-sm btn-outline-danger widget-remove" title="Usuń widget">×</button>
          </div>
        </div>
        <div class="card-body py-2">
          <input type="hidden" data-field="type" value="${escAttr(type)}">
          <div class="mb-2">
            <label class="form-label form-label-sm fw-medium">Tytuł widgetu</label>
            <input type="text" class="form-control form-control-sm" data-field="title"
                   value="${escAttr(data.title || '')}" placeholder="Tytuł widgetu">
          </div>
          ${extra}
        </div>`;
      return card;
    }

    const widgetsList = document.getElementById('sidebarWidgetsList');
    if (widgetsList) {
      widgetsList.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.widget-remove');
        if (removeBtn) { removeBtn.closest('.sidebar-widget-item').remove(); return; }
        const upBtn = e.target.closest('.widget-move-up');
        if (upBtn) {
          const card = upBtn.closest('.sidebar-widget-item');
          const prev = card.previousElementSibling;
          if (prev) widgetsList.insertBefore(card, prev);
          return;
        }
        const downBtn = e.target.closest('.widget-move-down');
        if (downBtn) {
          const card = downBtn.closest('.sidebar-widget-item');
          const next = card.nextElementSibling;
          if (next) widgetsList.insertBefore(next, card);
          return;
        }
      });

      widgetsList.addEventListener('input', function(e) {
        const titleInput = e.target.closest('[data-field="title"]');
        if (titleInput) {
          const card = titleInput.closest('.sidebar-widget-item');
          const display = card ? card.querySelector('.widget-title-display') : null;
          if (display) display.textContent = titleInput.value;
        }
      });
    }

    document.querySelectorAll('.add-widget-btn').forEach(function(btn) {
      btn.addEventListener('click', function() {
        const type = this.dataset.type;
        const list = document.getElementById('sidebarWidgetsList');
        if (!list) return;
        const card = buildWidgetCard(type, { title: widgetLabels[type] || type });
        list.appendChild(card);
      });
    });

    document.getElementById('appearanceForm')?.addEventListener('submit', function() {
      serializeSidebarWidgets();
    });
    // ===== END SIDEBAR WIDGET MANAGEMENT =====

  });
  </script>
</body>
</html>
