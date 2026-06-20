<?php /* Shared <head> resources: Google Fonts + Bootstrap CSS + theme CSS + custom styles.
 * Dołączany wewnątrz <head> przez home.php / post.php / page.php / contact.php
 * Zmienne dostępne z kontekstu wywołania: $themeCss, $homeHeaderCode
 */ ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;0,800;1,400;1,700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
<?php if (!empty($lightboxEnabled)): ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.4/css/lightbox.min.css">
<?php endif; ?>
<?php echo $themeCss ?? ''; ?>
<?php if (!empty($homeHeaderCode)) echo $homeHeaderCode; ?>
<style>
/* ============================================================
   CLASSIC BLOG — RedirectCMS Theme v1.0
   Bootstrap 5.3.8 + Playfair Display + Inter (Google Fonts)
   ============================================================ */

:root {
  --clr-primary:    var(--theme-primary,     #c0392b);
  --clr-navy:       var(--theme-header_bg,   #1c2331);
  --clr-accent:     var(--theme-accent,      #e67e22);
  --clr-body-bg:    var(--theme-body_bg,     #f5f5f0);
  --clr-footer-bg:  var(--theme-footer_bg,   #1c2331);
  --clr-footer-txt: var(--theme-footer_text, #9aa5b4);
  --clr-card:       #ffffff;
  --clr-text:       #1a1a2e;
  --clr-muted:      #6c757d;
  --clr-border:     #e8e4dd;
  --ff-serif:       'Playfair Display', Georgia, 'Times New Roman', serif;
  --ff-sans:        'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  --radius:         8px;
  --shadow-sm:      0 1px 6px rgba(0,0,0,.07);
  --shadow:         0 4px 16px rgba(0,0,0,.10);
  --shadow-lg:      0 10px 36px rgba(0,0,0,.14);
  --transition:     .22s ease;
}

/* ---- BASE ---- */
*, *::before, *::after { box-sizing: border-box; }
body {
  background: var(--clr-body-bg);
  font-family: var(--ff-sans);
  color: var(--clr-text);
  font-size: 16px;
  line-height: 1.7;
}
h1, h2, h3, h4, h5, h6 {
  font-family: var(--ff-serif);
  font-weight: 700;
  line-height: 1.3;
  color: var(--clr-text);
}
a { text-decoration: none; color: inherit; }
img { max-width: 100%; height: auto; }

/* ---- TOP BAR ---- */
.top-bar {
  background: var(--clr-navy);
  color: rgba(255,255,255,.55);
  font-size: .76rem;
  padding: 7px 0;
  border-bottom: 1px solid rgba(255,255,255,.07);
}
.top-bar .top-bar-date { letter-spacing: .03em; }
.top-bar-social { display: flex; align-items: center; gap: 2px; }
.top-bar-social a {
  display: inline-flex; align-items: center; justify-content: center;
  width: 26px; height: 26px; border-radius: 50%;
  color: rgba(255,255,255,.5);
  transition: color var(--transition), background var(--transition);
}
.top-bar-social a:hover { color: #fff; background: rgba(255,255,255,.1); }

/* ---- HEADER ---- */
.site-header {
  background: var(--clr-navy);
  color: #fff;
  padding: 20px 0;
  border-bottom: 3px solid var(--clr-primary);
}
.site-header a { color: #fff; }
.site-logo { height: 52px; width: auto; object-fit: contain; }
.site-branding .site-title {
  font-family: var(--ff-serif);
  font-size: 1.85rem;
  font-weight: 800;
  letter-spacing: -.5px;
  line-height: 1.15;
  color: #fff;
}
.site-branding .site-title:hover { opacity: .9; }
.site-branding .site-subtitle {
  font-size: .82rem;
  color: rgba(255,255,255,.55);
  margin-top: 3px;
  letter-spacing: .02em;
}

/* ---- NAV ---- */
.site-nav {
  background: #fff;
  position: sticky;
  top: 0;
  z-index: 200;
  box-shadow: 0 2px 10px rgba(0,0,0,.08);
  border-bottom: 3px solid var(--clr-primary);
}
.site-nav .navbar-toggler {
  border: none;
  padding: 6px 10px;
  color: #333;
}
.site-nav .navbar-toggler:focus { box-shadow: none; }
.site-nav .nav-link {
  color: #333;
  font-size: .82rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  padding: 16px 15px;
  position: relative;
  transition: color var(--transition);
}
.site-nav .nav-link::after {
  content: '';
  position: absolute;
  bottom: -3px; left: 15px; right: 15px;
  height: 3px;
  background: var(--clr-primary);
  transform: scaleX(0);
  transition: transform var(--transition);
}
.site-nav .nav-link:hover,
.site-nav .nav-link.active { color: var(--clr-primary); }
.site-nav .nav-link:hover::after,
.site-nav .nav-link.active::after { transform: scaleX(1); }

/* Nav search */
.nav-search-form { position: relative; }
.nav-search-form input {
  border: 1.5px solid #e0ddd8;
  border-radius: 20px;
  padding: 6px 14px 6px 34px;
  font-size: .82rem;
  width: 180px;
  transition: border-color var(--transition), width var(--transition);
  background: #faf8f5;
}
.nav-search-form input:focus {
  border-color: var(--clr-primary);
  width: 220px;
  outline: none;
  background: #fff;
}
.nav-search-icon {
  position: absolute; left: 11px; top: 50%;
  transform: translateY(-50%);
  color: #aaa;
  pointer-events: none;
}

/* ---- HERO SLIDER ---- */
.hero-slider { background: var(--clr-navy); position: relative; }
.hero-slide {
  position: relative;
  height: 500px;
  overflow: hidden;
}
@media (max-width: 991px) { .hero-slide { height: 380px; } }
@media (max-width: 575px) { .hero-slide { height: 300px; } }

.hero-slide-bg {
  position: absolute; inset: 0;
  background-size: cover;
  background-position: center;
  transition: transform 7s ease;
}
.carousel-item.active .hero-slide-bg { transform: scale(1.05); }

.hero-slide-overlay {
  position: absolute; inset: 0;
  background: linear-gradient(
    100deg,
    rgba(0,0,0,.78) 0%,
    rgba(0,0,0,.5)  55%,
    rgba(0,0,0,.15) 100%
  );
}
.hero-slide-content {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  padding: 44px 48px;
  color: #fff;
}
@media (max-width: 767px) { .hero-slide-content { padding: 24px 20px; } }

.hero-category-badge {
  display: inline-block;
  background: var(--clr-primary);
  color: #fff;
  font-size: .68rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .1em;
  padding: 4px 12px;
  border-radius: 3px;
  margin-bottom: 14px;
}
.hero-slide-title {
  font-family: var(--ff-serif);
  font-size: 2.3rem;
  font-weight: 800;
  line-height: 1.2;
  color: #fff;
  margin: 0 0 14px;
  max-width: 640px;
  text-shadow: 0 2px 12px rgba(0,0,0,.4);
}
@media (min-width: 992px) { .hero-slide-title { font-size: 2.8rem; } }
@media (max-width: 575px) { .hero-slide-title { font-size: 1.4rem; } }

.hero-slide-excerpt {
  font-size: .9rem;
  color: rgba(255,255,255,.82);
  max-width: 540px;
  margin-bottom: 22px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.6;
}
@media (max-width: 575px) { .hero-slide-excerpt { display: none; } }

.hero-slide-btn {
  display: inline-flex; align-items: center; gap: 7px;
  background: var(--clr-primary);
  color: #fff;
  border: 2px solid var(--clr-primary);
  padding: 9px 22px;
  border-radius: var(--radius);
  font-size: .82rem;
  font-weight: 700;
  letter-spacing: .03em;
  transition: background var(--transition), transform var(--transition);
}
.hero-slide-btn:hover {
  background: transparent;
  color: #fff;
  transform: translateX(4px);
}
.hero-slide-meta {
  font-size: .74rem;
  color: rgba(255,255,255,.55);
  margin-top: 10px;
}
.carousel-indicators [data-bs-target] {
  width: 28px; height: 3px;
  border-radius: 2px;
  background: rgba(255,255,255,.4);
  border: none;
  transition: background var(--transition), width var(--transition);
}
.carousel-indicators .active {
  background: var(--clr-primary);
  width: 40px;
}
.carousel-control-prev,
.carousel-control-next { width: 52px; opacity: .6; }
.carousel-control-prev:hover,
.carousel-control-next:hover { opacity: 1; }
.carousel-control-prev-icon,
.carousel-control-next-icon { width: 20px; height: 20px; }

/* ---- SECTION HEADING ---- */
.section-heading {
  font-family: var(--ff-serif);
  font-size: 1.35rem;
  font-weight: 700;
  color: var(--clr-text);
  padding-bottom: 12px;
  margin-bottom: 28px;
  border-bottom: 2px solid var(--clr-border);
  position: relative;
}
.section-heading::after {
  content: '';
  position: absolute;
  left: 0; bottom: -2px;
  width: 44px; height: 2px;
  background: var(--clr-primary);
}

/* ---- FEATURED CARD (duże, poziome) ---- */
.featured-card {
  display: flex;
  background: var(--clr-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  overflow: hidden;
  margin-bottom: 24px;
  transition: box-shadow var(--transition);
}
.featured-card:hover { box-shadow: var(--shadow-lg); }
.featured-card-img-wrap {
  flex: 0 0 52%;
  position: relative;
  overflow: hidden;
  min-height: 260px;
}
.featured-card-img-wrap img {
  width: 100%; height: 100%;
  object-fit: cover;
  transition: transform var(--transition);
}
.featured-card:hover .featured-card-img-wrap img { transform: scale(1.04); }
.featured-card-placeholder {
  width: 100%; height: 100%; min-height: 260px;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--ff-serif);
  font-size: 5rem; font-weight: 700;
}
.featured-card-body {
  flex: 1;
  padding: 28px 26px;
  display: flex;
  flex-direction: column;
}
@media (max-width: 640px) {
  .featured-card { flex-direction: column; }
  .featured-card-img-wrap { flex: none; min-height: 220px; }
}

/* ---- POST CARDS (siatka) ---- */
.post-card {
  background: var(--clr-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  overflow: hidden;
  height: 100%;
  display: flex;
  flex-direction: column;
  transition: transform var(--transition), box-shadow var(--transition);
}
.post-card:hover { transform: translateY(-5px); box-shadow: var(--shadow); }
.post-card-img    { width: 100%; height: 200px; object-fit: cover; }
.post-card-placeholder {
  height: 200px;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--ff-serif);
  font-size: 4rem; font-weight: 700;
}
.post-card-body {
  padding: 18px;
  flex: 1;
  display: flex;
  flex-direction: column;
}
.post-card-title {
  font-family: var(--ff-serif);
  font-size: 1.05rem;
  font-weight: 700;
  line-height: 1.4;
  margin-bottom: 8px;
}
.post-card-title a { color: var(--clr-text); transition: color var(--transition); }
.post-card-title a:hover { color: var(--clr-primary); }
.post-card-excerpt {
  font-size: .875rem;
  color: #555;
  line-height: 1.65;
  flex-grow: 1;
}
.post-card-meta {
  font-size: .73rem;
  color: var(--clr-muted);
  margin-top: auto;
  padding-top: 12px;
  border-top: 1px solid #f0ece5;
  display: flex;
  align-items: center;
  gap: 12px;
}
.post-card-meta svg { vertical-align: -2px; }
.read-more {
  font-size: .8rem;
  font-weight: 700;
  color: var(--clr-primary);
  letter-spacing: .02em;
  transition: opacity var(--transition);
}
.read-more:hover { opacity: .75; }

/* ---- CATEGORY BADGE ---- */
.cat-badge {
  display: inline-block;
  padding: 2px 9px;
  border-radius: 3px;
  font-size: .67rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: .06em;
  color: #fff;
}

/* ---- FILTER BAR ---- */
.filter-bar {
  background: #fff;
  padding: 10px 0;
  border-bottom: 1px solid var(--clr-border);
}
.filter-pill {
  display: inline-flex; align-items: center; gap: 4px;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: .75rem;
  font-weight: 600;
  border: 1.5px solid transparent;
  text-decoration: none;
  transition: all var(--transition);
  white-space: nowrap;
}
.filter-pill.fp-all          { background: var(--clr-primary); color: #fff; }
.filter-pill.fp-all.inactive { background: #f0ede8; color: #555; border-color: #ddd9d1; }
.filter-pill.fp-tag          { color: #666; border-color: #ddd9d1; }
.filter-pill.fp-tag:hover    { border-color: var(--clr-accent); color: var(--clr-accent); }
.filter-pill.fp-tag.active   { background: var(--clr-accent); color: #fff; border-color: var(--clr-accent); }

/* ---- SIDEBAR ---- */
.sidebar-widget {
  background: var(--clr-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  margin-bottom: 24px;
  overflow: hidden;
}
.sidebar-widget-header {
  padding: 13px 18px;
  border-bottom: 1px solid var(--clr-border);
}
.sidebar-widget-title {
  font-family: var(--ff-serif);
  font-size: .98rem;
  font-weight: 700;
  margin: 0;
  padding-left: 11px;
  border-left: 3px solid var(--clr-primary);
}
.sidebar-widget-body { padding: 16px 18px; }

/* Sidebar search */
.sidebar-search-row { display: flex; gap: 6px; }
.sidebar-search-row input {
  flex: 1;
  border: 1.5px solid var(--clr-border);
  border-radius: var(--radius);
  padding: 8px 12px;
  font-size: .85rem;
  background: #faf8f5;
}
.sidebar-search-row input:focus {
  border-color: var(--clr-primary);
  outline: none;
  background: #fff;
}
.sidebar-search-btn {
  background: var(--clr-primary);
  color: #fff;
  border: none;
  border-radius: var(--radius);
  padding: 8px 14px;
  cursor: pointer;
  transition: background var(--transition);
}
.sidebar-search-btn:hover { background: #a93226; }

/* Popular posts */
.sidebar-post {
  display: flex;
  gap: 11px;
  padding: 10px 0;
  border-bottom: 1px solid #f5f2ed;
  text-decoration: none;
  color: var(--clr-text);
  align-items: flex-start;
}
.sidebar-post:last-child  { border-bottom: none; padding-bottom: 0; }
.sidebar-post:first-child { padding-top: 0; }
.sidebar-post:hover .sidebar-post-title { color: var(--clr-primary); }
.sidebar-post-thumb {
  width: 68px; height: 50px;
  border-radius: 5px;
  object-fit: cover;
  flex-shrink: 0;
}
.sidebar-post-thumb-placeholder {
  width: 68px; height: 50px;
  border-radius: 5px;
  background: #ede9e2;
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--ff-serif);
  font-size: 1.2rem; font-weight: 700;
  color: #aaa;
}
.sidebar-post-title {
  font-size: .84rem;
  font-weight: 600;
  line-height: 1.35;
  transition: color var(--transition);
}
.sidebar-post-meta {
  font-size: .7rem;
  color: var(--clr-muted);
  margin-top: 3px;
}

/* Categories */
.sidebar-cat {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #f5f2ed;
  text-decoration: none;
  color: var(--clr-text);
  font-size: .875rem;
  transition: color var(--transition), padding-left var(--transition);
}
.sidebar-cat:last-child { border-bottom: none; }
.sidebar-cat:hover { color: var(--clr-primary); padding-left: 4px; }
.sidebar-cat-left { display: flex; align-items: center; gap: 8px; }
.sidebar-cat-dot  { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
.sidebar-cat-count {
  background: #f0ede8;
  color: var(--clr-muted);
  font-size: .68rem;
  font-weight: 600;
  padding: 2px 7px;
  border-radius: 10px;
}

/* Tag cloud */
.tag-cloud { display: flex; flex-wrap: wrap; gap: 6px; }
.tag-pill {
  display: inline-block;
  padding: 4px 11px;
  border: 1.5px solid #ddd9d1;
  border-radius: 20px;
  font-size: .77rem;
  color: #555;
  text-decoration: none;
  transition: all var(--transition);
}
.tag-pill:hover {
  background: var(--clr-primary);
  border-color: var(--clr-primary);
  color: #fff;
}

/* Social links in sidebar */
.sidebar-social-links { display: flex; flex-wrap: wrap; gap: 8px; }
.sidebar-social-link {
  display: inline-flex; align-items: center; justify-content: center;
  width: 36px; height: 36px;
  border: 1.5px solid var(--clr-border);
  border-radius: 50%;
  color: #555;
  transition: all var(--transition);
}
.sidebar-social-link:hover {
  background: var(--clr-primary);
  border-color: var(--clr-primary);
  color: #fff;
}

/* ---- PAGINATION ---- */
.pagination-wrap { margin-top: 32px; display: flex; justify-content: center; }
.pagination .page-link {
  color: var(--clr-primary);
  border-color: var(--clr-border);
  font-size: .88rem;
}
.pagination .page-item.active .page-link {
  background: var(--clr-primary);
  border-color: var(--clr-primary);
  color: #fff;
}
.pagination .page-link:hover {
  color: #fff;
  background: var(--clr-primary);
  border-color: var(--clr-primary);
}

/* ---- FOOTER ---- */
.site-footer {
  background: var(--clr-footer-bg);
  color: var(--clr-footer-txt);
  padding: 56px 0 0;
  margin-top: 64px;
}
.site-footer a { color: var(--clr-footer-txt); text-decoration: none; }
.site-footer a:hover { color: #fff; }

.footer-widget-title {
  font-family: var(--ff-serif);
  font-size: 1rem;
  font-weight: 700;
  color: #fff;
  margin-bottom: 18px;
  padding-bottom: 10px;
  border-bottom: 1px solid rgba(255,255,255,.1);
  position: relative;
}
.footer-widget-title::after {
  content: '';
  position: absolute;
  left: 0; bottom: -1px;
  width: 32px; height: 2px;
  background: var(--clr-primary);
}
.footer-about-text { font-size: .84rem; line-height: 1.75; opacity: .75; }

.footer-recent-post {
  display: flex;
  gap: 10px;
  align-items: flex-start;
  padding: 9px 0;
  border-bottom: 1px solid rgba(255,255,255,.07);
  text-decoration: none;
  color: var(--clr-footer-txt);
  transition: color var(--transition);
}
.footer-recent-post:last-child { border-bottom: none; }
.footer-recent-post:hover { color: #fff; }
.footer-recent-thumb {
  width: 52px; height: 40px;
  object-fit: cover;
  border-radius: 4px;
  flex-shrink: 0;
  opacity: .8;
}
.footer-recent-thumb-placeholder {
  width: 52px; height: 40px;
  border-radius: 4px;
  background: rgba(255,255,255,.08);
  flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.1rem;
  font-family: var(--ff-serif); font-weight: 700;
  color: rgba(255,255,255,.3);
}
.footer-recent-title { font-size: .82rem; line-height: 1.3; }
.footer-recent-date  { font-size: .68rem; opacity: .5; margin-top: 3px; }

.footer-cat-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 6px 0;
  font-size: .84rem;
  color: var(--clr-footer-txt);
  text-decoration: none;
  border-bottom: 1px solid rgba(255,255,255,.06);
  transition: color var(--transition), padding-left var(--transition);
}
.footer-cat-link:last-child { border-bottom: none; }
.footer-cat-link:hover { color: #fff; padding-left: 4px; }
.footer-cat-dot {
  width: 8px; height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
  opacity: .7;
}

.footer-link-item {
  display: block;
  padding: 6px 0;
  font-size: .84rem;
  color: var(--clr-footer-txt);
  text-decoration: none;
  border-bottom: 1px solid rgba(255,255,255,.06);
  transition: color var(--transition), padding-left var(--transition);
}
.footer-link-item:last-child { border-bottom: none; }
.footer-link-item:hover { color: #fff; padding-left: 5px; }
.footer-link-item::before { content: '›  '; opacity: .45; }

.footer-social-bar { display: flex; gap: 8px; margin-top: 18px; }
.footer-social-icon {
  display: inline-flex; align-items: center; justify-content: center;
  width: 34px; height: 34px;
  border: 1px solid rgba(255,255,255,.18);
  border-radius: 50%;
  color: var(--clr-footer-txt);
  transition: all var(--transition);
}
.footer-social-icon:hover {
  background: var(--clr-primary);
  border-color: var(--clr-primary);
  color: #fff;
}

.footer-bottom {
  background: rgba(0,0,0,.22);
  margin-top: 44px;
  padding: 14px 0;
  font-size: .76rem;
  border-top: 1px solid rgba(255,255,255,.06);
}
.footer-bottom a { color: var(--clr-footer-txt); }
.footer-bottom a:hover { color: #fff; }

/* ---- POST PAGE ---- */
.post-breadcrumb-bar {
  background: #fff;
  border-bottom: 1px solid var(--clr-border);
  padding: 10px 0;
}
.breadcrumb { margin: 0; }
.breadcrumb-item a { color: var(--clr-primary); font-size: .82rem; }
.breadcrumb-item.active { color: var(--clr-muted); font-size: .82rem; }

.post-hero {
  width: 100%;
  max-height: 460px;
  object-fit: cover;
  border-radius: var(--radius);
  box-shadow: var(--shadow);
  margin-bottom: 28px;
}
.post-article {
  background: var(--clr-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  padding: 40px;
}
@media (max-width: 575px) { .post-article { padding: 22px; } }

.post-article > h1 { font-size: 1.95rem; }
@media (max-width: 575px) { .post-article > h1 { font-size: 1.5rem; } }

.post-lead {
  font-size: 1.02rem;
  color: #444;
  font-style: italic;
  line-height: 1.75;
  border-left: 3px solid var(--clr-primary);
  padding-left: 16px;
  margin: 16px 0 24px;
}
.post-meta-bar {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 14px;
  font-size: .78rem;
  color: var(--clr-muted);
  padding: 14px 0;
  border-top: 1px solid var(--clr-border);
  border-bottom: 1px solid var(--clr-border);
  margin: 14px 0 28px;
}
.post-meta-bar svg { vertical-align: -2px; }

.post-body {
  font-size: .975rem;
  line-height: 1.85;
  color: #333;
}
.post-body img   { max-width: 100%; border-radius: 6px; margin: 8px 0; }
.post-body a     { color: var(--clr-primary); }
.post-body h2    { font-size: 1.5rem; margin-top: 2em; }
.post-body h3    { font-size: 1.2rem; margin-top: 1.6em; }
.post-body p     { margin-bottom: 1.2em; }
.post-body blockquote {
  border-left: 3px solid var(--clr-primary);
  padding: 12px 20px;
  background: #faf8f5;
  border-radius: 0 var(--radius) var(--radius) 0;
  color: #555;
  font-style: italic;
  margin: 1.5em 0;
}

.post-tags   { margin-top: 26px; padding-top: 18px; border-top: 1px solid var(--clr-border); }
.post-share  {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid var(--clr-border);
  display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
}
.post-back-btn {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 9px 18px;
  border: 1.5px solid var(--clr-border);
  color: #555;
  border-radius: var(--radius);
  font-size: .85rem;
  font-weight: 600;
  transition: all var(--transition);
}
.post-back-btn:hover { border-color: var(--clr-primary); color: var(--clr-primary); }

/* Gallery */
.gallery-grid  { display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 10px; margin: 24px 0; }
.gallery-thumb-wrap { border-radius: 6px; overflow: hidden; aspect-ratio: 4/3; display: block; }
.gallery-thumb-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform var(--transition); }
.gallery-thumb-wrap:hover img { transform: scale(1.07); }

.gallery-viewer { margin: 24px 0; }
.gallery-main-img { width: 100%; border-radius: 8px; aspect-ratio: 16/9; object-fit: cover; margin-bottom: 10px; }
.gallery-thumbs { display: flex; gap: 8px; flex-wrap: wrap; }
.gallery-thumb-btn {
  cursor: pointer; border: 2px solid transparent; border-radius: 5px;
  overflow: hidden; padding: 0; background: none;
  width: 70px; height: 52px;
}
.gallery-thumb-btn img { width: 100%; height: 100%; object-fit: cover; }
.gallery-thumb-btn.active { border-color: var(--clr-primary); }

/* Related posts */
.related-section { margin-top: 48px; }
.related-card {
  background: var(--clr-card);
  border-radius: var(--radius);
  overflow: hidden;
  box-shadow: var(--shadow-sm);
  height: 100%;
  transition: transform var(--transition), box-shadow var(--transition);
}
.related-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); }
.related-card img { width: 100%; height: 150px; object-fit: cover; }
.related-card-placeholder {
  height: 150px;
  display: flex; align-items: center; justify-content: center;
  font-family: var(--ff-serif);
  font-size: 3rem; font-weight: 700;
}
.related-card-body { padding: 14px; }
.related-card-title {
  font-family: var(--ff-serif);
  font-size: .9rem;
  font-weight: 700;
  color: var(--clr-text);
  line-height: 1.35;
  transition: color var(--transition);
}
.related-card-title:hover { color: var(--clr-primary); }

/* ---- PAGE / CONTACT ---- */
.page-content {
  background: var(--clr-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  padding: 40px;
}
@media (max-width: 575px) { .page-content { padding: 22px; } }
.page-content img { max-width: 100%; border-radius: 6px; }
.page-content a   { color: var(--clr-primary); }
.page-content h1  { font-size: 2rem; margin-bottom: 1.5rem; }

.contact-card {
  background: var(--clr-card);
  border-radius: var(--radius);
  box-shadow: var(--shadow-sm);
  padding: 40px;
}
@media (max-width: 575px) { .contact-card { padding: 22px; } }
.contact-card .form-control {
  border: 1.5px solid var(--clr-border);
  border-radius: var(--radius);
  padding: 10px 14px;
  background: #faf8f5;
}
.contact-card .form-control:focus {
  border-color: var(--clr-primary);
  box-shadow: 0 0 0 3px rgba(192,57,43,.1);
  background: #fff;
}
.btn-contact {
  background: var(--clr-primary);
  color: #fff;
  border: none;
  padding: 11px 28px;
  border-radius: var(--radius);
  font-weight: 700;
  font-size: .9rem;
  transition: background var(--transition), transform var(--transition);
}
.btn-contact:hover { background: #a93226; transform: translateY(-1px); color: #fff; }

/* ---- ACTIVE FILTER INFO ---- */
.active-filter-bar {
  background: #fffbeb;
  border-bottom: 1px solid #fef08a;
  padding: 8px 0;
  font-size: .84rem;
}

/* ---- EMPTY STATE ---- */
.empty-state { text-align: center; padding: 60px 20px; color: var(--clr-muted); }
.empty-state svg { opacity: .2; display: block; margin: 0 auto 16px; }
</style>
