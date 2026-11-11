<?php
function sh($c){return trim((string)shell_exec($c));}
$fm='/opt/psa/admin/bin/filemng';
$user='bmcars.bahman.ir_vzb4o368qoo';
$root='/var/www/vhosts/bahman.ir/httpdocs/templates/bahman';

// --- 1) config: load product company field ---
$cfgPath=$root.'/config.php';
$cfg=file_get_contents($cfgPath);
$oldProd = <<<'OLD'
            $product = new CmsContent();
            $product
                ->setType('product')
                ->setFields("title")
                ->setCount(-1);
            $product_list = $product->execute();
            unset($product);

            $smarty->assign('agency', $visibleAgencies);
OLD;
$newProd = <<<'NEW'
            $product = new CmsContent();
            $product
                ->setType('product')
                ->setFields("title,company")
                ->setCount(-1);
            $product_list = $product->execute();
            unset($product);
            if (!is_array($product_list)) {
                $product_list = [];
            }
            $product_list = bahman_cms_sort_by_list_priority($product_list);

            $smarty->assign('agency', $visibleAgencies);
NEW;
if (strpos($cfg,'setFields("title,company")')!==false && strpos($cfg,"case 'agency':")!==false) {
  // may already be patched in agency block - check nearby
  if (strpos($cfg,$newProd)!==false || preg_match('/case \'agency\':[\s\S]*?setFields\("title,company"\)/',$cfg)) {
    echo "CFG_PROD_ALREADY\n";
  } elseif (strpos($cfg,$oldProd)!==false) {
    $cfg=str_replace($oldProd,$newProd,$cfg);
    echo "CFG_PROD_PATCHED\n";
  } else {
    echo "CFG_PROD_MISS\n";
  }
} elseif (strpos($cfg,$oldProd)!==false) {
  $cfg=str_replace($oldProd,$newProd,$cfg);
  echo "CFG_PROD_PATCHED\n";
} else {
  echo "CFG_PROD_MISS\n";
}

// --- 2) agency.tpl: data-company on product options ---
$tplPath=$root.'/agency.tpl';
$tpl=file_get_contents($tplPath);
$oldOpt = <<<'OLD'
                                                    {if isset($product) and !empty($product)}
                                                        {foreach $product as $prod}
                                                            <option value="{$prod.id}">{$prod.values.title|default: ''}</option>
                                                        {/foreach}
                                                    {/if}
OLD;
$newOpt = <<<'NEW'
                                                    {if isset($product) and !empty($product)}
                                                        {foreach $product as $prod}
                                                            <option value="{$prod.id}" data-company="{$prod.values.company|default:''|escape:'html'}">{$prod.values.title|default: ''}</option>
                                                        {/foreach}
                                                    {/if}
NEW;
if (strpos($tpl,'data-company="{$prod.values.company')!==false) {
  echo "TPL_ALREADY\n";
} elseif (strpos($tpl,$oldOpt)!==false) {
  $tpl=str_replace($oldOpt,$newOpt,$tpl);
  $tmp='/tmp/agency.tpl.productfilter';
  file_put_contents($tmp,$tpl);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($tplPath).' 2>&1');
  echo "TPL_PATCHED\n";
} else {
  echo "TPL_MISS\n";
  if (preg_match('/filter-product[\s\S]{0,400}/',$tpl,$m)) echo $m[0]."\n";
}

// --- 3) Replace filter IIFE in agents_bahman.js ---
$jsPath=$root.'/assets/agents_bahman.js';
$js=file_get_contents($jsPath);
$marker='(function(){const e=document.getElementById("agencyFilterForm");';
$pos=strpos($js,$marker);
if ($pos===false) {
  echo "JS_IIFE_MISS\n";
} else {
  $head=substr($js,0,$pos);
  $filterJs = <<<'JS'
(function () {
  const form = document.getElementById("agencyFilterForm");
  if (!form) return;

  const companySelect = document.getElementById("filter-company");
  const provinceSelect = document.getElementById("filter-province");
  const citySelect = document.getElementById("filter-city");
  const productSelect = document.getElementById("filter-product");
  const keywordInput = document.getElementById("filter-keyword");
  const results = document.getElementById("agency-results");

  let provinceCities = {};
  try {
    const el = document.getElementById("agency-province-cities");
    provinceCities = el ? JSON.parse(el.textContent || "{}") : {};
  } catch (err) {
    provinceCities = {};
  }

  const normalize = I;

  function makeOption(value, text) {
    const opt = document.createElement("option");
    opt.value = value;
    opt.textContent = text;
    return opt;
  }

  function updateCities() {
    if (!citySelect || !provinceSelect) return;
    citySelect.innerHTML = "";
    const province = provinceSelect.value;
    let cities = province && provinceCities[province] ? Object.values(provinceCities[province]) : null;
    if (!cities && province) {
      const norm = normalize(province);
      for (const [key, value] of Object.entries(provinceCities)) {
        if (normalize(key) === norm) {
          cities = Object.values(value);
          break;
        }
      }
    }
    if (!province || !cities) {
      const opt = makeOption("", "ابتدا استان را انتخاب کنید");
      citySelect.appendChild(opt);
      citySelect.disabled = true;
      B.refresh(citySelect);
      return;
    }
    citySelect.disabled = false;
    citySelect.appendChild(makeOption("", "همه شهرها"));
    cities
      .sort((a, b) => String(a).localeCompare(String(b), "fa"))
      .forEach((city) => citySelect.appendChild(makeOption(city, city)));
    B.refresh(citySelect);
  }

  function csvHas(value, needle) {
    if (!needle) return true;
    return String(value || "")
      .split(",")
      .map((part) => part.trim())
      .includes(needle);
  }

  // Snapshot product options once (with data-company from CMS product.company).
  const allProductOptions = productSelect
    ? Array.from(productSelect.options).map((opt) => ({
        value: opt.value,
        text: opt.textContent || "",
        company: opt.getAttribute("data-company") || "",
      }))
    : [];

  function updateProducts() {
    if (!productSelect) return;
    const companyId = companySelect ? String(companySelect.value || "").trim() : "";
    const previous = productSelect.value;
    productSelect.innerHTML = "";

    allProductOptions.forEach((item) => {
      if (!item.value) {
        productSelect.appendChild(makeOption("", item.text || "همه محصولات"));
        return;
      }
      if (companyId && !csvHas(item.company, companyId)) return;
      const opt = makeOption(item.value, item.text);
      if (item.company) opt.setAttribute("data-company", item.company);
      productSelect.appendChild(opt);
    });

    const stillValid = Array.from(productSelect.options).some((opt) => opt.value === previous);
    productSelect.value = stillValid ? previous : "";
    B.refresh(productSelect);
  }

  function applyFilters() {
    const companyId = normalize(companySelect ? companySelect.value : "");
    const province = normalize(provinceSelect ? provinceSelect.value : "");
    const city = normalize(citySelect ? citySelect.value : "");
    const productId = normalize(productSelect ? productSelect.value : "");
    const keyword = normalize(keywordInput ? keywordInput.value : "");
    let firstVisibleTab = null;

    document.querySelectorAll(".tab-pane").forEach((pane) => {
      let visibleCount = 0;
      pane.querySelectorAll(".accordion-item").forEach((item) => {
        const itemCompany = item.dataset.company || "";
        const itemProduct = item.dataset.product || "";
        const itemProvince = normalize(item.dataset.province || "");
        const itemCity = normalize(item.dataset.city || "");
        const itemTitle = normalize(item.dataset.title || "");
        const itemCode = normalize(item.dataset.code || "");
        const match =
          (!companyId || csvHas(itemCompany, companyId)) &&
          (!province || itemProvince === province) &&
          (!city || itemCity === city) &&
          (!productId || csvHas(itemProduct, productId)) &&
          (!keyword || itemTitle.includes(keyword) || itemCode.includes(keyword));
        item.style.display = match ? "" : "none";
        if (match) visibleCount += 1;
      });
      const empty = pane.querySelector(".no-result-message");
      if (empty) empty.style.display = visibleCount === 0 ? "" : "none";
      if (visibleCount > 0 && !firstVisibleTab) {
        firstVisibleTab = pane.id.replace("tab-", "");
      }
    });

    if (companyId && window.activateTab) {
      window.activateTab("company" + companyId);
    } else if (firstVisibleTab && window.activateTab) {
      window.activateTab(firstVisibleTab);
    }
    if (window.reinitAccordionsForDynamicContent) {
      window.reinitAccordionsForDynamicContent();
    }
    if (results) {
      window.scrollTo({
        top: results.getBoundingClientRect().top + window.pageYOffset - 100,
        behavior: "smooth",
      });
    }
  }

  if (provinceSelect) provinceSelect.addEventListener("change", updateCities);
  if (companySelect) {
    companySelect.addEventListener("change", () => {
      updateProducts();
    });
  }
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    applyFilters();
  });

  updateCities();
  updateProducts();
  initAgencySearchSelects();
})();
JS;
  $newJs=$head.$filterJs;
  $tmp='/tmp/agents_bahman.js.productfilter';
  file_put_contents($tmp,$newJs);
  sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($jsPath).' 2>&1');
  echo "JS_PATCHED bytes=".filesize($jsPath)."\n";
}

$tmp='/tmp/config.php.agencyproduct';
file_put_contents($tmp,$cfg);
$phpBin='/opt/plesk/php/8.3/bin/php';
echo "lint=".sh(escapeshellarg($phpBin).' -l '.escapeshellarg($tmp))."\n";
sh($fm.' '.escapeshellarg($user).' cp '.escapeshellarg($tmp).' '.escapeshellarg($cfgPath).' 2>&1');
echo "cfg_deployed\n";

foreach (glob('/var/www/vhosts/bahman.ir/httpdocs/templates_c/*agency*') ?: [] as $f) @unlink($f);
@touch($jsPath);

$page=sh('curl -s --max-time 45 -H "Host: bahman.ir" "http://172.20.14.76/?include=agency&pf='.time().'"');
echo "page_len=".strlen($page)." fatal=".(stripos($page,'Fatal error')!==false?'1':'0')."\n";
if (preg_match('/id="filter-product"[^>]*>(.*?)<\/select>/s',$page,$m)) {
  preg_match_all('/<option([^>]*)>([^<]*)<\/option>/',$m[1],$opts,PREG_SET_ORDER);
  echo "product_options=".count($opts)."\n";
  $withCompany=0; $sample=[];
  foreach ($opts as $o) {
    if (preg_match('/data-company="([^"]*)"/',$o[1],$dm) && $dm[1]!=='') {
      $withCompany++;
      if (count($sample)<6) $sample[]=$dm[1].'|'.$o[2];
    }
  }
  echo "with_data_company=$withCompany\n";
  echo "samples=".json_encode($sample, JSON_UNESCAPED_UNICODE)."\n";
}
$jsLive=file_get_contents($jsPath);
echo "js_has_updateProducts=".(strpos($jsLive,'updateProducts')!==false?'1':'0')."\n";
echo "js_has_allProductOptions=".(strpos($jsLive,'allProductOptions')!==false?'1':'0')."\n";
echo "DONE\n";
