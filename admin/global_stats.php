<?php
  $pageTitle = 'Statystyki globalne — RedirectCMS';
  $extraHead = '
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lipis/flag-icons@7.2.3/css/flag-icons.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>';
  require __DIR__ . '/static/head.php';
?>
<body class="bg-light">
  <?php require_once __DIR__ . '/static/navbar.php'; ?>

  <!-- Zawartość główna -->
  <div class="container-xxl py-5">
    <div class="row">
      <div class="col-12">
        <h2 class="mb-3">Statystyki globalne</h2>
        
        <?php if (!empty($refreshSuccess)): ?>
          <div class="alert bg-success-subtle border border-success text-success-emphasis alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($refreshSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="card border border-info mb-3">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
              <form method="get" action="<?= htmlspecialchars($basePath, ENT_QUOTES, 'UTF-8') ?>/admin/index.php" class="d-flex align-items-center gap-2">
                <input type="hidden" name="action" value="global_stats" />
                <label class="form-label mb-0" for="daysSelect">Zakres:</label>
                <select id="daysSelect" name="days" class="form-select form-select-sm" onchange="this.form.submit()">
                  <option value="7" <?= (isset($days) && $days === 7) ? 'selected' : '' ?>>7 dni</option>
                  <option value="30" <?= (!isset($days) || $days === 30) ? 'selected' : '' ?>>30 dni</option>
                  <option value="90" <?= (isset($days) && $days === 90) ? 'selected' : '' ?>>90 dni</option>
                </select>
                <div class="form-check ms-2 text-nowrap">
                  <input class="form-check-input" type="checkbox" value="1" id="excludeBots" name="exclude_bots" <?= (!empty($excludeBots)) ? 'checked' : '' ?> onchange="this.form.submit()">
                  <label class="form-check-label text-nowrap" for="excludeBots">Wyklucz boty</label>
                </div>
              </form>
              
              <div class="d-flex gap-2 align-items-center">
                <?php if (!empty($cacheInfo)): ?>
                    Cache: <?= date('Y-m-d H:i:s', strtotime($cacheInfo['updated_at'])) ?>
                     - <?= floor($cacheInfo['age_seconds'] / 60) ?> min temu
                <?php endif; ?>
                <form method="post" action="<?= $basePath ?>/admin/index.php?action=refresh_global_cache" class="d-inline">
                  <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>" />
                  <input type="hidden" name="days" value="<?= $days ?>" />
                  <input type="hidden" name="exclude_bots" value="<?= $excludeBots ? 1 : 0 ?>" />
                  <button type="submit" class="btn btn-sm btn-outline-primary" title="Odśwież statystyki">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                      <path fill-rule="evenodd" d="M8 3a5 5 0 1 0 4.546 2.914.5.5 0 0 1 .908-.417A6 6 0 1 1 8 2v1z"/>
                      <path d="M8 4.466V.534a.25.25 0 0 1 .41-.192l2.36 1.966c.12.1.12.284 0 .384L8.41 4.658A.25.25 0 0 1 8 4.466z"/>
                    </svg>
                    Odśwież cache
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
        <!-- Podsumowanie -->
        <div class="row g-3 mb-4">
          <div class="col-md-3">
            <div class="card border border-info h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Dzisiaj</h6>
                <h2 class="card-title mb-0"><?= number_format($stats['today']) ?></h2>
                <small class="text-muted">Unikalni: <?= number_format($stats['unique_today']) ?></small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border border-info h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Ostatnie 7 dni</h6>
                <h2 class="card-title mb-0"><?= number_format($stats['last_7_days']) ?></h2>
                <small class="text-muted">Unikalni: <?= number_format($stats['unique_last_7_days']) ?></small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border border-info h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Ostatnie 30 dni</h6>
                <h2 class="card-title mb-0"><?= number_format($stats['last_30_days']) ?></h2>
                <small class="text-muted">Unikalni: <?= number_format($stats['unique_last_30_days']) ?></small>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border border-info h-100 bg-info-subtle">
              <div class="card-body text-center">
                <h6 class="card-subtitle text-info mb-2">Łącznie</h6>
                <h2 class="card-title text-info mb-0"><?= number_format($stats['total_clicks']) ?></h2>
                <small class="text-info">Unikalni: <?= number_format($stats['unique_total']) ?></small>
              </div>
            </div>
          </div>
        </div>

        <!-- Top linki -->
        <div class="card border border-info mb-4">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">Top <?= count($stats['top_links']) ?> linki (ostatnie <?= $days ?> dni)</h5>
          </div>
          <div class="card-body">
            <?php if (empty($stats['top_links'])): ?>
              <p class="text-muted mb-0">Brak danych</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-hover mb-0">
                  <thead>
                    <tr>
                      <th>Tytuł (alias, gdy brak)</th>
                      <th>Docelowy URL</th>
                      <th class="text-end">Kliknięcia</th>
                      <th class="text-end">Akcje</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats['top_links'] as $link): ?>
                      <tr>
                        <?php $title = isset($link['page_title']) ? trim((string)$link['page_title']) : ''; ?>
                        <td>
                          <?php if ($title !== ''): ?>
                            <span class="fw-semibold">&laquo;<?= htmlspecialchars($title) ?>&raquo;</span>
                            <div><small class="text-muted"><code><?= htmlspecialchars($link['slug']) ?></code></small></div>
                          <?php else: ?>
                            <code><?= htmlspecialchars($link['slug']) ?></code>
                          <?php endif; ?>
                        </td>
                        <td>
                          <small class="text-truncate-inline max-w-300">
                            <?= htmlspecialchars($link['target_url']) ?>
                          </small>
                        </td>
                        <td class="text-end"><strong><?= number_format($link['clicks']) ?></strong></td>
                        <td class="text-end">
                          <a href="<?= $basePath ?>/admin/index.php?action=stats&id=<?= (int)$link['id'] ?>" class="btn btn-sm btn-primary">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                              <path d="M4 11H2v3h2v-3zm5-4H7v7h2V7zm5-5v12h-2V2h2zm-2-1a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h2a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1h-2zM6 7a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V7zm-5 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1v-3z"/>
                            </svg>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Wykres dzienny -->
        <div class="card border border-info mb-4">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">Kliknięcia w ostatnich <?= $days ?> dniach</h5>
          </div>
          <div class="card-body">
            <canvas id="dailyChart" class="chart-sm"></canvas>
          </div>
        </div>

        <div class="row">
          <!-- Urządzenia -->
          <div class="col-md-6 mb-4">
            <div class="card">
              <div class="card-header bg-info-subtle">
                <h5 class="mb-0">Urządzenia</h5>
              </div>
              <div class="card-body">
                <canvas id="deviceChart" class="chart-md"></canvas>
              </div>
            </div>
          </div>

          <!-- Przeglądarki -->
          <div class="col-md-6 mb-4">
            <div class="card">
              <div class="card-header bg-info-subtle">
                <h5 class="mb-0">Przeglądarki</h5>
              </div>
              <div class="card-body">
                <canvas id="browserChart" class="chart-md"></canvas>
              </div>
            </div>
          </div>
        </div>

        <!-- Rozkład godzinowy -->
        <div class="card border border-info mb-4">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">Rozkład godzinowy</h5>
          </div>
          <div class="card-body">
            <canvas id="hourlyChart" class="chart-md"></canvas>
          </div>
        </div>

        <!-- Programy afiliacyjne -->
        <?php if (!empty($affiliateProgramStats)): ?>
        <div class="card border border-info mb-4">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">Programy afiliacyjne</h5>
          </div>
          <div class="card-body">
            <canvas id="affiliateProgramChart" class="chart-md"></canvas>
          </div>
        </div>
        <?php endif; ?>

        <!-- Geolokalizacja: Kraje i Miasta -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card">
              <div class="card-header bg-info-subtle">
                <h5 class="mb-0">Top kraje</h5>
              </div>
              <div class="card-body">
                <?php if (empty($stats['by_country'])): ?>
                  <p class="text-muted mb-0">Brak danych geolokalizacji</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead>
                        <tr>
                          <th>Kraj</th>
                          <th class="text-end">Kliknięcia</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($stats['by_country'] as $item): ?>
                          <tr>
                            <td>
                              <?php if ($item['country'] !== 'Nieznany'): ?>
                                <span class="fi fi-<?= strtolower(htmlspecialchars($item['country'])) ?>" style="margin-right: 8px;"></span>
                                <strong><?= htmlspecialchars($item['country']) ?></strong>
                              <?php else: ?>
                                <span class="text-muted">Nieznany</span>
                              <?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format($item['count']) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card">
              <div class="card-header bg-info-subtle">
                <h5 class="mb-0">Top miasta</h5>
              </div>
              <div class="card-body">
                <?php if (empty($stats['by_city'])): ?>
                  <p class="text-muted mb-0">Brak danych geolokalizacji</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead>
                        <tr>
                          <th>Miasto</th>
                          <th class="text-end">Kliknięcia</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($stats['by_city'] as $item): ?>
                          <tr>
                            <td>
                              <?= htmlspecialchars($item['city']) ?>
                              <?php if (!empty($item['country_code']) && $item['country_code'] !== 'Nieznany'): ?>
                                <small class="text-muted">(<?= htmlspecialchars($item['country_code']) ?>)</small>
                              <?php endif; ?>
                            </td>
                            <td class="text-end"><?= number_format($item['count']) ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Domeny źródłowe -->
        <div class="card border border-info mb-4">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">Top domeny źródłowe</h5>
          </div>
          <div class="card-body">
            <?php if (empty($stats['by_referer_domain'])): ?>
              <p class="text-muted mb-0">Brak danych</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table table-sm mb-0">
                  <thead>
                    <tr>
                      <th>Domena</th>
                      <th class="text-end">Kliknięcia</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats['by_referer_domain'] as $item): ?>
                      <tr>
                        <td>
                          <?php if ($item['domain'] === 'Bezpośredni'): ?>
                            <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Bezpośredni</span>
                          <?php else: ?>
                            <small class="text-truncate-inline max-w-350"><?= htmlspecialchars($item['domain']) ?></small>
                          <?php endif; ?>
                        </td>
                        <td class="text-end"><strong><?= number_format($item['count']) ?></strong></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Heatmapa: dzień tygodnia × godzina -->
        <div class="card border border-info mb-4">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">Heatmapa aktywności (dzień × godzina) - Bieżący tydzień</h5>
          </div>
          <div class="card-body">
            <?php 
            $dayNames = [1 => 'Nd', 2 => 'Pn', 3 => 'Wt', 4 => 'Śr', 5 => 'Cz', 6 => 'Pt', 7 => 'Sob'];
            $heatmap = $stats['heatmap'] ?? [];
            $maxCount = 0;
            foreach ($heatmap as $day => $hours) {
              foreach ($hours as $count) {
                if ($count > $maxCount) $maxCount = $count;
              }
            }
            ?>
            <div class="table-responsive">
              <table class="table table-bordered table-sm heatmap-table">
                <thead>
                  <tr>
                    <th class="heatmap-hour">&nbsp;</th>
                    <?php for ($h = 0; $h < 24; $h++): ?>
                      <th class="text-center heatmap-hour"><?= $h ?></th>
                    <?php endfor; ?>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ([2, 3, 4, 5, 6, 7, 1] as $day): ?>
                    <tr>
                      <td class="fw-bold heatmap-day"><?= $dayNames[$day] ?></td>
                      <?php for ($hour = 0; $hour < 24; $hour++): 
                        $count = $heatmap[$day][$hour] ?? 0;
                        $intensity = $maxCount > 0 ? ($count / $maxCount) : 0;
                        $bgColor = 'rgba(30, 136, 229, ' . $intensity . ')';
                      ?>
                        <td class="text-center heatmap-cell" style="background-color: <?= $bgColor ?>;" title="<?= $dayNames[$day] ?> <?= $hour ?>:00 - <?= $count ?> kliknięć">
                          <?= $count > 0 ? $count : '' ?>
                        </td>
                      <?php endfor; ?>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  <?php require __DIR__ . '/static/footer.php'; ?>
  <script>
    // Wykres dzienny
    const dailyData = <?= json_encode($stats['daily_chart']) ?>;
    const dailyLabels = dailyData.map(d => d.date);
    const dailyCounts = dailyData.map(d => d.count);
    
    new Chart(document.getElementById('dailyChart'), {
      type: 'line',
      data: {
        labels: dailyLabels,
        datasets: [{
          label: 'Kliknięcia',
          data: dailyCounts,
          borderColor: 'rgb(52, 152, 219)',
          backgroundColor: 'rgba(52, 152, 219, 0.3)',
          tension: 0.1,
          fill: true
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // Wykres urządzeń
    const deviceData = <?= json_encode($stats['by_device']) ?>;
    const deviceLabels = deviceData.map(d => {
      const labels = {
        'desktop': 'Desktop',
        'mobile': 'Mobile',
        'tablet': 'Tablet',
        'bot': 'Bot'
      };
      return labels[d.device_type] || d.device_type;
    });
    const deviceCounts = deviceData.map(d => d.count);
    
    new Chart(document.getElementById('deviceChart'), {
      type: 'doughnut',
      data: {
        labels: deviceLabels,
        datasets: [{
          data: deviceCounts,
          borderColor: [
            'rgb(93, 180, 237)',
            'rgb(255, 99, 132)',
            'rgb(255, 206, 86)',
            'rgb(180, 242, 149)',
            'rgb(52, 152, 219)',
            'rgb(182, 93, 231)'
          ],
          borderWidth: 1,
          backgroundColor: [
            'rgba(93, 180, 237, 0.4)',
            'rgba(255, 99, 132, 0.4)',
            'rgba(255, 206, 86, 0.4)',
            'rgba(180, 242, 149, 0.4)',
            'rgba(52, 152, 219, 0.4)',
            'rgba(182, 93, 231, 0.4)'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Wykres godzinowy
    const hourlyData = <?= json_encode($stats['hourly_distribution']) ?>;
    const hourlyLabels = Object.keys(hourlyData).map(h => h + ':00');
    const hourlyCounts = Object.values(hourlyData);
    
    new Chart(document.getElementById('hourlyChart'), {
      type: 'bar',
      data: {
        labels: hourlyLabels,
        datasets: [{
          label: 'Kliknięcia',
          data: hourlyCounts,
          borderColor: 'rgb(52, 152, 219)',
          backgroundColor: 'rgba(52, 152, 219, 0.3)',
          borderWidth: 1,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // Wykres przeglądarek
    const browserData = <?= json_encode($stats['by_browser']) ?>;
    const browserLabels = browserData.map(b => b.browser);
    const browserCounts = browserData.map(b => b.count);
    new Chart(document.getElementById('browserChart'), {
      type: 'doughnut',
      data: {
        labels: browserLabels,
        datasets: [{
          data: browserCounts,
          borderColor: [
            'rgb(93, 180, 237)',
            'rgb(255, 99, 132)',
            'rgb(255, 206, 86)',
            'rgb(180, 242, 149)',
            'rgb(52, 152, 219)',
            'rgb(182, 93, 231)'
          ],
          borderWidth: 1,
          backgroundColor: [
            'rgba(93, 180, 237, 0.4)',
            'rgba(255, 99, 132, 0.4)',
            'rgba(255, 206, 86, 0.4)',
            'rgba(180, 242, 149, 0.4)',
            'rgba(52, 152, 219, 0.4)',
            'rgba(182, 93, 231, 0.4)'
          ]
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });

    // Wykres programów afiliacyjnych
    <?php if (!empty($affiliateProgramStats)): ?>
    const affiliateCtx = document.getElementById('affiliateProgramChart');
    if (affiliateCtx) {
      const affiliateLabels = <?= json_encode(array_column($affiliateProgramStats, 'name')) ?>;
      const affiliateClicks = <?= json_encode(array_column($affiliateProgramStats, 'click_count')) ?>;
      const affiliateUnique = <?= json_encode(array_column($affiliateProgramStats, 'unique_visitors')) ?>;
      const affiliateLinks = <?= json_encode(array_column($affiliateProgramStats, 'link_count')) ?>;

      new Chart(affiliateCtx, {
        type: 'bar',
        data: {
          labels: affiliateLabels,
          datasets: [
            {
              label: 'Kliknięcia',
              data: affiliateClicks,
              backgroundColor: 'rgba(25, 135, 84, 0.4)',
              borderColor: 'rgb(21, 148, 89)',
              borderWidth: 1,
              yAxisID: 'y'
            },
            {
              label: 'Unikalni użytkownicy',
              data: affiliateUnique,
              backgroundColor: 'rgba(13, 110, 253, 0.4)',
              borderColor: 'rgb(47, 114, 229)',
              borderWidth: 1,
              yAxisID: 'y'
            },
            {
              label: 'Liczba linków',
              data: affiliateLinks,
              backgroundColor: 'rgba(255, 193, 7, 0.4)',
              borderColor: 'rgb(255, 193, 7)',
              borderWidth: 1,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
              position: 'top'
            }
          },
          scales: {
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              beginAtZero: true,
              ticks: { precision: 0 },
              title: {
                display: true,
                text: 'Kliknięcia / Unikalni'
              }
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              beginAtZero: true,
              ticks: { precision: 0 },
              title: {
                display: true,
                text: 'Liczba linków'
              },
              grid: { drawOnChartArea: false }
            }
          }
        }
      });
    }
    <?php endif; ?>
  </script>
</body>
</html>
