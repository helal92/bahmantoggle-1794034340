<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';
$jsPath=$root.'/assets/brand-slider.js';
$js = <<<'JS'
export function initBrandCarousel() {
  const root = document.querySelector("[data-brand-carousel]");
  if (!root || root.dataset.initialized === "true") return;

  const viewport = root.querySelector(".brand-carousel__viewport");
  const track = root.querySelector(".brand-carousel__track");
  const prevBtn = root.querySelector(".brand-carousel__nav--prev");
  const nextBtn = root.querySelector(".brand-carousel__nav--next");

  if (!viewport || !track || !prevBtn || !nextBtn) return;

  const originals = [...track.querySelectorAll(".brand-carousel__item")];
  const count = originals.length;
  if (count === 0) return;

  root.dataset.initialized = "true";

  const FALLBACK_STRIDE = 190;

  const cloneItem = (item) => {
    const node = item.cloneNode(true);
    node.removeAttribute("id");
    node.querySelectorAll("img").forEach((img) => {
      img.loading = "eager";
      img.decoding = "async";
    });
    return node;
  };

  const buildTrack = () => {
    track.innerHTML = "";
    const vw = viewport.clientWidth || root.clientWidth || 1200;
    const loops = Math.max(5, Math.ceil(vw / (FALLBACK_STRIDE * count)) + 3);
    for (let i = 0; i < loops; i += 1) {
      originals.forEach((item) => track.appendChild(cloneItem(item)));
    }
    return loops;
  };

  let loops = buildTrack();
  let index = count * Math.floor(loops / 2);
  let stride = FALLBACK_STRIDE;
  let animating = false;
  let autoplayTimer = null;

  const items = () => [...track.querySelectorAll(".brand-carousel__item")];

  const measure = () => {
    const list = items();
    if (!list.length) {
      stride = FALLBACK_STRIDE;
      return false;
    }
    if (list.length < 2) {
      const w = list[0].getBoundingClientRect().width;
      stride = w > 1 ? w : FALLBACK_STRIDE;
      return w > 1;
    }
    const a = list[0].getBoundingClientRect();
    const b = list[1].getBoundingClientRect();
    const byLeft = Math.abs(b.left - a.left);
    const byRight = Math.abs(a.right - b.right);
    const w = Math.max(byLeft, byRight, a.width);
    if (w > 1) {
      stride = w;
      return true;
    }
    stride = FALLBACK_STRIDE;
    return false;
  };

  const getOffset = () => index * stride;

  const applyTransform = (animate) => {
    track.style.transition = animate
      ? "transform 0.55s cubic-bezier(0.4, 0, 0.2, 1)"
      : "none";
    track.style.transform = `translate3d(${-getOffset()}px, 0, 0)`;
  };

  const normalize = () => {
    if (count <= 1) return;
    const minIndex = count;
    const maxIndex = count * (loops - 1) - 1;
    if (index > maxIndex) {
      index -= count;
      applyTransform(false);
    } else if (index < minIndex) {
      index += count;
      applyTransform(false);
    }
  };

  const move = (step) => {
    if (animating || count <= 1) return;
    animating = true;
    index += step;
    applyTransform(true);
  };

  track.addEventListener("transitionend", (event) => {
    if (event.propertyName !== "transform") return;
    normalize();
    animating = false;
  });

  const restartAutoplay = () => {
    if (count <= 1) return;
    clearInterval(autoplayTimer);
    autoplayTimer = setInterval(() => move(1), 3500);
  };

  prevBtn.addEventListener("click", () => {
    move(-1);
    restartAutoplay();
  });

  nextBtn.addEventListener("click", () => {
    move(1);
    restartAutoplay();
  });

  let touchStartX = 0;
  viewport.addEventListener(
    "touchstart",
    (event) => {
      touchStartX = event.changedTouches[0].screenX;
    },
    { passive: true }
  );

  viewport.addEventListener(
    "touchend",
    (event) => {
      const delta = event.changedTouches[0].screenX - touchStartX;
      if (Math.abs(delta) < 40) return;
      move(delta > 0 ? -1 : 1);
      restartAutoplay();
    },
    { passive: true }
  );

  const refresh = () => {
    if (!viewport.clientWidth) return;
    const prevMod = index % count;
    loops = buildTrack();
    index = count * Math.floor(loops / 2) + prevMod;
    measure();
    applyTransform(false);
  };

  const syncLayout = () => {
    if (!viewport.clientWidth) return false;
    const ok = measure();
    applyTransform(false);
    return ok;
  };

  let tries = 0;
  const boot = () => {
    tries += 1;
    const ok = syncLayout();
    if ((!ok || tries < 2) && tries < 20) {
      requestAnimationFrame(boot);
      return;
    }
    restartAutoplay();
  };

  measure();
  applyTransform(false);
  requestAnimationFrame(boot);

  if (document.fonts && document.fonts.ready) {
    document.fonts.ready.then(() => {
      refresh();
      restartAutoplay();
    }).catch(() => {});
  }

  window.addEventListener("load", () => {
    refresh();
    restartAutoplay();
  });

  window.addEventListener("resize", refresh);

  if (typeof ResizeObserver === "function") {
    const ro = new ResizeObserver(() => refresh());
    ro.observe(viewport);
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initBrandCarousel);
} else {
  initBrandCarousel();
}

JS;
$tmp='/tmp/brand-slider.js.deploy';
file_put_contents($tmp,$js);
echo sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($jsPath).' 2>&1')."\n";
echo "js_bytes=".filesize($jsPath)."\n";
$live=file_get_contents($jsPath);
echo "has_RO=".(strpos($live,'ResizeObserver')!==false?'1':'0')." has_fonts=".(strpos($live,'document.fonts')!==false?'1':'0')."\n";

$hdr=$root.'/includes/header_include.tpl';
$h=file_get_contents($hdr);
$old = '<link rel="stylesheet" media="print" onload="this.onload=null;this.media=\'all\'" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">';
$new = '<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">';
if (strpos($h, 'assets/brand-slider.css') === false) {
  echo "HDR_NO_BRAND_CSS\n";
} elseif (strpos($h, 'media="print"') !== false && preg_match('#<link[^>]+brand-slider\.css[^>]*>#', $h, $m)) {
  $tag = $m[0];
  if (strpos($tag, 'media="print"') !== false) {
    $repl = '<link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/brand-slider.css?v={$template_version}">';
    $h2 = str_replace($tag, $repl, $h);
    $tmp='/tmp/header_include.tpl.brand';
    file_put_contents($tmp,$h2);
    sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($hdr).' 2>&1');
    echo "HDR_PATCHED\n";
  } else {
    echo "HDR_TAG_OK\n";
  }
} else {
  echo "HDR_ALREADY_OR_OK\n";
  if (preg_match('#<link[^>]+brand-slider\.css[^>]*>#', $h, $m)) echo "tag=".$m[0]."\n";
}

$tmpDir=rtrim(sys_get_temp_dir(), '/');
foreach (glob($tmpDir.'/bahman_*.html') ?: [] as $f) { @unlink($f); echo "rm ".basename($f)."\n"; }
foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*index*') ?: [] as $f) @unlink($f);

$page=sh('curl -s --max-time 25 -H "Host: bahman.ir" "http://172.20.14.76/?bcfix='.time().'"');
if (preg_match('#<link[^>]+brand-slider\.css[^>]*>#', $page, $m)) {
  echo "css_tag=".$m[0]."\n";
  echo "blocking=".(strpos($m[0],'media="print"')===false?'1':'0')."\n";
}
$jsLive=sh('curl -s --max-time 15 -H "Host: bahman.ir" "http://172.20.14.76/templates/bahman/assets/brand-slider.js?nocache='.time().'"');
echo "live_js_has_RO=".(strpos($jsLive,'ResizeObserver')!==false?'1':'0')." live_len=".strlen($jsLive)."\n";
echo "DONE\n";
