{strip}
<script defer src="{$admin_setting.template_url}/assets/product-menu-lazy.js?v={$template_version}"></script>
<script defer src="{$admin_setting.template_url}/assets/header-search.js?v={$template_version}"></script>
{if isset($has_active_sale) and $has_active_sale}
<script defer src="{$admin_setting.template_url}/assets/active-sale-btn.js?v={$template_version}"></script>
{/if}
<link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/index2.js?v={$template_version}">
<link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/hero-bahman.js?v={$template_version}">
<link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/footer-bahman.js?v={$template_version}">
<link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/navigation.js?v={$template_version}">
{if isset($smarty.get.include) and $smarty.get.include eq 'index'}
    <script defer src="{$admin_setting.template_url}/assets/hero-video-control.js?v={$template_version}"></script>
    <script defer src="{$admin_setting.template_url}/assets/hero-media-fix.js?v={$template_version}"></script>
    <script type="module" crossorigin src="{$admin_setting.template_url}/assets/index.js?v={$template_version}"></script>
    <link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/slider_car-bahman.js?v={$template_version}">
{else if isset($smarty.get.include) and $smarty.get.include eq 'show_product'}
    <script type="module" crossorigin src="{$admin_setting.template_url}/assets/product.js?v={$template_version}"></script>
    <script defer src="{$admin_setting.template_url}/assets/hero-media-fix.js?v={$template_version}"></script>
    <script defer src="{$admin_setting.template_url}/assets/product-gallery.js?v={$template_version}"></script>
    <link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/fancybox.js?v={$template_version}">
    <link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/circular2.js?v={$template_version}">
{else if isset($smarty.get.include) and ($smarty.get.include eq 'brand' or $smarty.get.include eq 'emdad')}
    <script type="module" crossorigin src="{$admin_setting.template_url}/assets/footer-bahman.js?v={$template_version}"></script>
    <script defer src="{$admin_setting.template_url}/assets/product-gallery.js?v={$template_version}"></script>
    <script defer src="{$admin_setting.template_url}/assets/fancybox.js?v={$template_version}"></script>
    <script defer src="{$admin_setting.template_url}/assets/circular2.js?v={$template_version}"></script>
    <script defer src="{$admin_setting.template_url}/assets/company-page.js?v={$template_version}"></script>
{else if isset($smarty.get.include) and $smarty.get.include eq 'showroom'}
    {* showroom.js owns the product slider — do not load slider_car-bahman via 404.js *}
    <script type="module" crossorigin src="{$admin_setting.template_url}/assets/footer-bahman.js?v={$template_version}"></script>
{else if (isset($is_404) and $is_404) or (isset($smarty.get.include) and ($smarty.get.include eq 'sales' or $smarty.get.include eq 'after_sales' or $smarty.get.include eq 'show_page' or $smarty.get.include eq 'about_us' or $smarty.get.include eq 'contact_us' or $smarty.get.include eq 'agency_request' or $smarty.get.include eq 'product' or $smarty.get.include eq 'article' or $smarty.get.include eq 'show_article' or $smarty.get.include eq 'blog' or $smarty.get.include eq 'show_blog' or $smarty.get.include eq 'event' or $smarty.get.include eq 'show_event' or $smarty.get.include eq 'video' or $smarty.get.include eq 'show_video' or $smarty.get.include eq 'search' or $smarty.get.include eq 'chc' or $smarty.get.include eq '404'))}
    <script type="module" crossorigin src="{$admin_setting.template_url}/assets/404.js?v={$template_version}"></script>
    <link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/slider_car-bahman.js?v={$template_version}">
    <link rel="modulepreload" crossorigin href="{$admin_setting.template_url}/assets/circular2.js?v={$template_version}">
{else}
    {* Fallback: header submenu/burger for any page missing a dedicated bundle *}
    <script type="module" crossorigin src="{$admin_setting.template_url}/assets/footer-bahman.js?v={$template_version}"></script>
{/if}
<!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="https://matomo.baharcms.ir/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '2']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->
{/strip}