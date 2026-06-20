<?php
  $displayTitle = !empty($link['page_title']) ? (string)$link['page_title'] : (string)$link['slug'];
  $pageTitle = 'Statystyki — ' . $displayTitle . ' — RedirectCMS';
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
        <h2 class="mb-3">Statystyki: 
          <?php if (!empty($link['page_title'])): ?>
            <span class="fw-semibold"><?= htmlspecialchars($link['page_title']) ?></span>
            <small class="text-muted ms-2"><code><?= htmlspecialchars($link['slug']) ?></code></small>
          <?php else: ?>
            <code><?= htmlspecialchars($link['slug']) ?></code>
          <?php endif; ?>
        </h2>
        
        <div class="d-flex justify-content-end align-items-center mb-2 flex-wrap gap-2">
          <form method="get" action="<?= $basePath ?>/admin/index.php" class="d-flex align-items-center gap-2">
            <input type="hidden" name="action" value="stats" />
            <input type="hidden" name="id" value="<?= (int)$link['id'] ?>" />
            <label class="form-label mb-0" for="daysSelect">Zakres:</label>
            <select id="daysSelect" name="days" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="7" <?= (isset($days) && $days === 7) ? 'selected' : '' ?>>7 dni</option>
              <option value="30" <?= (!isset($days) || $days === 30) ? 'selected' : '' ?>>30 dni</option>
              <option value="90" <?= (isset($days) && $days === 90) ? 'selected' : '' ?>>90 dni</option>
            </select>
            <div class="form-check ms-2" class="text-nowrap">
              <input class="form-check-input" type="checkbox" value="1" id="excludeBots" name="exclude_bots" <?= (!empty($excludeBots)) ? 'checked' : '' ?> onchange="this.form.submit()">
              <label class="form-check-label" for="excludeBots">Wyklucz boty</label>
            </div>
          </form>
          <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              Eksport
            </button>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="<?= $basePath ?>/admin/index.php?action=export_stats&id=<?= (int)$link['id'] ?>&type=daily&format=csv&days=<?= (int)$days ?>&exclude_bots=<?= !empty($excludeBots) ? 1 : 0 ?>">Dzienny (CSV)</a></li>
              <li><a class="dropdown-item" href="<?= $basePath ?>/admin/index.php?action=export_stats&id=<?= (int)$link['id'] ?>&type=referer&format=csv&days=<?= (int)$days ?>&exclude_bots=<?= !empty($excludeBots) ? 1 : 0 ?>">Źródła (CSV)</a></li>
              <li><a class="dropdown-item" href="<?= $basePath ?>/admin/index.php?action=export_stats&id=<?= (int)$link['id'] ?>&type=device&format=csv&days=<?= (int)$days ?>&exclude_bots=<?= !empty($excludeBots) ? 1 : 0 ?>">Urządzenia (CSV)</a></li>
              <li><a class="dropdown-item" href="<?= $basePath ?>/admin/index.php?action=export_stats&id=<?= (int)$link['id'] ?>&type=browser&format=csv&days=<?= (int)$days ?>&exclude_bots=<?= !empty($excludeBots) ? 1 : 0 ?>">Przeglądarki (CSV)</a></li>
              <li><a class="dropdown-item" href="<?= $basePath ?>/admin/index.php?action=export_stats&id=<?= (int)$link['id'] ?>&type=hourly&format=csv&days=<?= (int)$days ?>&exclude_bots=<?= !empty($excludeBots) ? 1 : 0 ?>">Godziny (CSV)</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="<?= $basePath ?>/admin/index.php?action=export_stats&id=<?= (int)$link['id'] ?>&type=daily&format=json&days=<?= (int)$days ?>&exclude_bots=<?= !empty($excludeBots) ? 1 : 0 ?>">Dzienny (JSON)</a></li>
            </ul>
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
                <?php 
                $delta = $stats['today'] - $stats['previous_today'];
                $percent = $stats['previous_today'] > 0 ? (($delta / $stats['previous_today']) * 100) : 0;
                $trend = $delta > 0 ? 'success' : ($delta < 0 ? 'danger' : 'secondary');
                $arrow = $delta > 0 ? '↑' : ($delta < 0 ? '↓' : '→');
                ?>
                <?php if ($delta != 0): ?>
                  <div class="mt-2">
                    <small class="text-<?= $trend ?>">
                      <?= $arrow ?> <?= $delta > 0 ? '+' : '' ?><?= number_format($delta) ?> (<?= number_format($percent, 1) ?>%)
                    </small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border border-info h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Ostatnie 7 dni</h6>
                <h2 class="card-title mb-0"><?= number_format($stats['last_7_days']) ?></h2>
                <small class="text-muted">Unikalni: <?= number_format($stats['unique_last_7_days']) ?></small>
                <?php 
                $delta = $stats['last_7_days'] - $stats['previous_7_days'];
                $percent = $stats['previous_7_days'] > 0 ? (($delta / $stats['previous_7_days']) * 100) : 0;
                $trend = $delta > 0 ? 'success' : ($delta < 0 ? 'danger' : 'secondary');
                $arrow = $delta > 0 ? '↑' : ($delta < 0 ? '↓' : '→');
                ?>
                <?php if ($delta != 0): ?>
                  <div class="mt-2">
                    <small class="text-<?= $trend ?>">
                      <?= $arrow ?> <?= $delta > 0 ? '+' : '' ?><?= number_format($delta) ?> (<?= number_format($percent, 1) ?>%)
                    </small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border border-info h-100">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2 text-muted">Ostatnie 30 dni</h6>
                <h2 class="card-title mb-0"><?= number_format($stats['last_30_days']) ?></h2>
                <small class="text-muted">Unikalni: <?= number_format($stats['unique_last_30_days']) ?></small>
                <?php 
                $delta = $stats['last_30_days'] - $stats['previous_30_days'];
                $percent = $stats['previous_30_days'] > 0 ? (($delta / $stats['previous_30_days']) * 100) : 0;
                $trend = $delta > 0 ? 'success' : ($delta < 0 ? 'danger' : 'secondary');
                $arrow = $delta > 0 ? '↑' : ($delta < 0 ? '↓' : '→');
                ?>
                <?php if ($delta != 0): ?>
                  <div class="mt-2">
                    <small class="text-<?= $trend ?>">
                      <?= $arrow ?> <?= $delta > 0 ? '+' : '' ?><?= number_format($delta) ?> (<?= number_format($percent, 1) ?>%)
                    </small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card border border-info h-100 bg-info-subtle">
              <div class="card-body text-center">
                <h6 class="card-subtitle mb-2">Łącznie</h6>
                <h2 class="card-title mb-0"><?= number_format($stats['total']) ?></h2>
                <small class="">Unikalni: <?= number_format($stats['unique_total']) ?></small>
              </div>
            </div>
          </div>
        </div>

        <!-- Wykres dzienny -->
        <div class="card border border-info mb-4">
          <div class="card-header bg-info-subtle">
            <h5 class="mb-0">Kliknięcia w ostatnich <?= isset($days) ? (int)$days : 30 ?> dniach</h5>
          </div>
          <div class="card-body">
            <canvas id="dailyChart" class="chart-sm"></canvas>
          </div>
        </div>

        <div class="row">
          <!-- Urządzenia -->
          <div class="col-md-6 mb-4">
            <div class="card border border-info">
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
            <div class="card border border-info">
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

        <!-- Geolokalizacja: Kraje i Miasta -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card border border-info">
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
            <div class="card border border-info">
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

        <!-- Domeny źródłowe -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="card border border-info">
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
                                <small class="text-truncate-inline max-w-250"><?= htmlspecialchars($item['domain']) ?></small>
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
          </div>

          <div class="col-md-6">
            <div class="card border border-info">
              <div class="card-header bg-info-subtle">
                <h5 class="mb-0">Pełne URL źródeł</h5>
              </div>
              <div class="card-body">
                <?php if (empty($stats['by_referer'])): ?>
                  <p class="text-muted mb-0">Brak danych</p>
                <?php else: ?>
                  <div class="table-responsive">
                    <table class="table table-sm mb-0">
                      <thead>
                        <tr>
                          <th>Źródło</th>
                          <th class="text-end">Kliknięcia</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php foreach ($stats['by_referer'] as $ref): ?>
                          <tr>
                            <td>
                              <?php if ($ref['referer'] === 'Bezpośredni'): ?>
                                <span class="badge bg-secondary-subtle border border-secondary text-secondary-emphasis">Bezpośredni</span>
                              <?php else: ?>
                                <small class="text-truncate-inline max-w-300"><?= htmlspecialchars($ref['referer']) ?></small>
                              <?php endif; ?>
                            </td>
                            <td class="text-end"><strong><?= number_format($ref['count']) ?></strong></td>
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
          borderWidth: 1
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
  </script>
</body>
</html>
