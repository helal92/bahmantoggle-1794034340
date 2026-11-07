{strip}
{$section_1 = ''}
{$section_5 = ''}
{$section_6 = []}
{if isset($index_page) and isset($index_page.child.content_section) and !empty($index_page.child.content_section)}
    {foreach $index_page.child.content_section as $item}
        {if isset($item.values) and !empty($item.values) and isset($item.values.section_key) and $item.values.section_key neq ''}
            {if $item.values.section_key eq 'section-1'}
                {$section_1 = $item.values}
            {else if $item.values.section_key eq 'section-5'}
                {$section_5 = $item.values}
            {else if $item.values.section_key eq 'section-6'}
                {$section_6[] = $item.values}
            {/if}
        {/if}
    {/foreach}
{/if}
<!doctype html>
<html lang="fa" dir="rtl">

    <head>
        {include file="./includes/header_include.tpl"}
        <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/latest-news.css?v={$template_version}">
    </head>

    <body>
        <div class="overlay"></div>

        <a href="#" id="go-to-top">
            <i class="icon icon-arrow-up2"></i>
        </a>

        <header class="header">
            {include file="./includes/header.tpl"}
        </header>

        <div class="home-stack">
            {if $section_1 neq ''}
                {assign var="links" value=$section_1.link|json_decode}
                <section class="hero">
                    <div class="swiper heroSwiper">
                        <div class="swiper-wrapper">
                            {$hero_i = 0}
                            {foreach $links as $item}
                                {if (isset($item.image) and $item.image neq '') or (isset($item.image2) and $item.image2 neq '')}
                                    <div class="swiper-slide item_slider">
                                        {if isset($item.image) && $item.image neq ''}
                                            {if isset($item.image2) and $item.image2 neq ''}
                                                <img class="hero-media-fallback" src="{$admin_setting.file_url}/thumb/{$item.image2}/1280" alt="{$item.content|default:$item.title|default:''|escape:'html'}" decoding="async"{if $hero_i eq 0} fetchpriority="high"{else} loading="lazy"{/if} onerror="this.remove()">
                                            {/if}
                                            <video class="hero-media{if isset($item.image2) and $item.image2 neq ''} has-fallback{/if}" muted playsinline webkit-playsinline loop preload="none"{if isset($item.image2) and $item.image2 neq ''} poster="{$admin_setting.file_url}/thumb/{$item.image2}/1280"{/if} data-src="{$admin_setting.file_url}/{$item.image}"{if $hero_i eq 0} src="{$admin_setting.file_url}/{$item.image}" autoplay{/if}>
                                            </video>
                                        {else if isset($item.image2) && $item.image2 neq ''}
                                            <img class="hero-media" src="{$admin_setting.file_url}/thumb/{$item.image2}/1280" srcset="{$admin_setting.file_url}/thumb/{$item.image2}/768 768w, {$admin_setting.file_url}/thumb/{$item.image2}/1280 1280w, {$admin_setting.file_url}/thumb/{$item.image2}/1920 1920w" sizes="100vw" alt="{$item.title|default: ''}" decoding="async"{if $hero_i eq 0} fetchpriority="high"{else} loading="lazy"{/if} />
                                        {/if}

                                        <div class="box">
                                            <div class="container">
                                                <div class="content">
                                                    {if $item.title neq ''}
                                                        <a href="{$item.href|default: $item.file|default: ''}"
                                                            class="btn_show_product">{$item.title|default: ''}</a>
                                                    {/if}
                                                    <h1 class="name">{$item.content|default: ''}</h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {$hero_i = $hero_i + 1}
                                {/if}
                            {/foreach}
                        </div>

                        <div class="pagination_slider">
                            <div class="bottom">
                                <div class="swiper-pagination"></div>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="hero-video-toggle" aria-label="توقف اسلایدر" aria-pressed="true">
                        <svg class="hero-video-toggle__progress" viewBox="0 0 44 44" aria-hidden="true">
                            <circle class="hero-video-toggle__progress-track" cx="22" cy="22" r="19.5"></circle>
                            <circle class="hero-video-toggle__progress-bar" cx="22" cy="22" r="19.5"></circle>
                        </svg>
                        <svg class="hero-video-toggle__icon hero-video-toggle__icon--pause" viewBox="0 0 24 24" aria-hidden="true">
                            <rect x="6" y="5" width="4" height="14" rx="1"></rect>
                            <rect x="14" y="5" width="4" height="14" rx="1"></rect>
                        </svg>
                        <svg class="hero-video-toggle__icon hero-video-toggle__icon--play" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M8 5.5v13l11-6.5z"></path>
                        </svg>
                    </button>
                </section>
            {/if}

            {* Product carousel: filled client-side from /product-menu.json (keeps homepage HTML light) *}
            <section class="slider_car container" id="home-product-slider"
                data-menu-json="{$admin_setting.site_url}/product-menu-data"
                data-file-url="{$admin_setting.file_url}"
                data-site-url="{$admin_setting.site_url}"
                hidden>
                <div class="top">
                    <div class="Brands-wrap">
                        <div class="Brands" id="home-product-brands"></div>
                        <a href="{$admin_setting.site_url}/product" class="Item btn_all_product">همه محصولات</a>
                    </div>
                </div>
                <div id="home-product-carousels"></div>
                <div class="info">
                    <div class="text_box">
                        <h3 id="product_slider_title" class="Title"></h3>
                        <p id="product_slider_desc" class="desc"></p>
                    </div>
                    <div class="box_btn">
                        <a id="product_slider_link" class="Link" href="#">مشاهده محصول</a>
                        <a id="product_slider_link2" class="Link2" href="#"></a>
                    </div>
                </div>
            </section>

            <div>
                <section class="news-container news-container--cards">
                    <div class="news-header container">
                        <h2 class="news-title">جدیدترین‌ها</h2>

                        <div class="news-tabs">
                            {$conter = 1}
                            {if !empty($blog_category)}
                                {foreach $blog_category as $item}
                                    {if isset($item.values.title) and $item.values.title neq '' and isset($item.child_table) and isset($item.child_table.blog_category) !empty($item.child_table.blog_category)}
                                        <span class="tab-item {if $conter eq 1}active{/if}" data-news-tab="item{$item.id}">{$item.values.title}</span>
                                        {$conter = $conter + 1}
                                    {/if}
                                {/foreach}
                            {/if}
                            {if !empty($videos)}
                                <span class="tab-item" data-news-tab="item-video">فیلم‌ها</span>
                            {/if}
                            {if !empty($home_events)}
                                <span class="tab-item" data-news-tab="item-event">رویدادها</span>
                            {/if}
                            {if !empty($home_blogs)}
                                <span class="tab-item" data-news-tab="item-blog">بلاگ</span>
                            {/if}
                        </div>
                    </div>

                    {$conter = 1}
                    {if !empty($blog_category)}
                        {foreach $blog_category as $item}
                            {if isset($item.values.title) and $item.values.title neq '' and isset($item.child_table) and isset($item.child_table.blog_category) !empty($item.child_table.blog_category)}
                                <div class="news-tab-content {if $conter eq 1}active{/if}" data-news-content="item{$item.id}">
                                    <div class="swiper newsSwiper news-swiper container">
                                        <div class="swiper-wrapper">
                                            {foreach $item.child_table.blog_category as $blog}
                                                {assign var="news_href" value="`$admin_setting.site_url`/post/`$blog.id`"}
                                                {assign var="news_target" value=""}
                                                {assign var="news_rel" value=""}
                                                {if isset($blog.values.aparat_link) and $blog.values.aparat_link neq ''}
                                                    {assign var="news_href" value=$blog.values.aparat_link}
                                                    {assign var="news_target" value='target="_blank"'}
                                                    {assign var="news_rel" value='rel="noopener noreferrer"'}
                                                {/if}
                                                <div class="swiper-slide">
                                                    <article class="news-card">
                                                        <a href="{$news_href}" {$news_target} {$news_rel} class="news-card__media">
                                                            <img src="{$admin_setting.file_url}/thumb/{$blog.values.image}/960" class="img" alt="{$blog.values.title|escape:'html'}" loading="lazy">
                                                        </a>
                                                        <div class="news-card__body">
                                                            <h3 class="news-card__title">
                                                                <a href="{$news_href}" {$news_target} {$news_rel} title="{$blog.values.title|escape:'html'}">{$blog.values.title|default: ''}</a>
                                                            </h3>
                                                            {* {assign var="news_lead" value=$blog.values.short_desc|default:''}
                                                            {if $news_lead neq ''}
                                                                <p class="news-card__desc">{$news_lead|strip_tags}</p>
                                                            {/if} *}
                                                            <a href="{$news_href}" {$news_target} {$news_rel} class="news-card__btn">مشاهده</a>
                                                        </div>
                                                    </article>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </div>
                                    <div class="container news-view-all-wrap">
                                        <a href="{$admin_setting.site_url}/article" class="news-view-all">مشاهده همه اخبار</a>
                                    </div>
                                </div>
                                {$conter = $conter + 1}
                            {/if}
                        {/foreach}
                    {/if}
                    {if !empty($videos)}
                        <div class="news-tab-content" data-news-content="item-video">
                            <div class="swiper newsSwiper news-swiper container">
                                <div class="swiper-wrapper">
                                    {foreach $videos as $video}
                                        {if isset($video.values.title) and $video.values.title neq ''}
                                            {assign var="news_href" value="`$admin_setting.site_url`/video/`$video.id`"}
                                            {assign var="news_target" value=""}
                                            {assign var="news_rel" value=""}
                                            {if isset($video.values.aparat_link) and $video.values.aparat_link neq ''}
                                                {assign var="news_href" value=$video.values.aparat_link}
                                                {assign var="news_target" value='target="_blank"'}
                                                {assign var="news_rel" value='rel="noopener noreferrer"'}
                                            {/if}
                                            <div class="swiper-slide">
                                                <article class="news-card">
                                                    <a href="{$news_href}" {$news_target} {$news_rel} class="news-card__media">
                                                        {if isset($video.values.image) and $video.values.image neq ''}
                                                            <img src="{$admin_setting.file_url}/thumb/{$video.values.image}/960" class="img" alt="{$video.values.title|escape:'html'}" loading="lazy">
                                                        {/if}
                                                    </a>
                                                    <div class="news-card__body">
                                                        <h3 class="news-card__title">
                                                            <a href="{$news_href}" {$news_target} {$news_rel} title="{$video.values.title|escape:'html'}">{$video.values.title|default: ''}</a>
                                                        </h3>
                                                        <a href="{$news_href}" {$news_target} {$news_rel} class="news-card__btn">مشاهده</a>
                                                    </div>
                                                </article>
                                            </div>
                                        {/if}
                                    {/foreach}
                                </div>
                            </div>
                            <div class="container news-view-all-wrap">
                                <a href="{$admin_setting.site_url}/video" class="news-view-all">مشاهده همه فیلم‌ها</a>
                            </div>
                        </div>
                    {/if}
                    {if !empty($home_events)}
                        <div class="news-tab-content" data-news-content="item-event">
                            <div class="swiper newsSwiper news-swiper container">
                                <div class="swiper-wrapper">
                                    {foreach $home_events as $event}
                                        {if isset($event.values.title) and $event.values.title neq ''}
                                            {assign var="event_href" value="`$admin_setting.site_url`/event/`$event.id`"}
                                            <div class="swiper-slide">
                                                <article class="news-card">
                                                    <a href="{$event_href}" class="news-card__media">
                                                        {if isset($event.values.image) and $event.values.image neq ''}
                                                            <img src="{$admin_setting.file_url}/thumb/{$event.values.image}/960" class="img" alt="{$event.values.title|escape:'html'}" loading="lazy">
                                                        {/if}
                                                    </a>
                                                    <div class="news-card__body">
                                                        <h3 class="news-card__title">
                                                            <a href="{$event_href}" title="{$event.values.title|escape:'html'}">{$event.values.title|default: ''}</a>
                                                        </h3>
                                                        <a href="{$event_href}" class="news-card__btn">مشاهده</a>
                                                    </div>
                                                </article>
                                            </div>
                                        {/if}
                                    {/foreach}
                                </div>
                            </div>
                            <div class="container news-view-all-wrap">
                                <a href="{$admin_setting.site_url}/event" class="news-view-all">مشاهده همه رویدادها</a>
                            </div>
                        </div>
                    {/if}
                    {if !empty($home_blogs)}
                        <div class="news-tab-content" data-news-content="item-blog">
                            <div class="swiper newsSwiper news-swiper container">
                                <div class="swiper-wrapper">
                                    {foreach $home_blogs as $blog}
                                        {if isset($blog.values.title) and $blog.values.title neq ''}
                                            {assign var="blog_href" value="`$admin_setting.site_url`/blog/`$blog.id`"}
                                            <div class="swiper-slide">
                                                <article class="news-card">
                                                    <a href="{$blog_href}" class="news-card__media">
                                                        {if isset($blog.values.image) and $blog.values.image neq ''}
                                                            <img src="{$admin_setting.file_url}/thumb/{$blog.values.image}/960" class="img" alt="{$blog.values.title|escape:'html'}" loading="lazy">
                                                        {/if}
                                                    </a>
                                                    <div class="news-card__body">
                                                        <h3 class="news-card__title">
                                                            <a href="{$blog_href}" title="{$blog.values.title|escape:'html'}">{$blog.values.title|default: ''}</a>
                                                        </h3>
                                                        <a href="{$blog_href}" class="news-card__btn">مشاهده</a>
                                                    </div>
                                                </article>
                                            </div>
                                        {/if}
                                    {/foreach}
                                </div>
                            </div>
                            <div class="container news-view-all-wrap">
                                <a href="{$admin_setting.site_url}/blog" class="news-view-all">مشاهده همه مطالب</a>
                            </div>
                        </div>
                    {/if}
                </section>

                <!-- <a href="#" class="btn_turn">نوبت دهی اینترنتی</a> -->

                {if $section_5 neq ''}
                    <section class="shopping_tool_tiles">
                        <div class="card">
                            <div class="offers_carousel">
                                {assign var="links" value=$section_5.link|json_decode}
                                {if !empty($links)}
                                    {foreach $links as $item}
                                        {if $item@index gt 0 and $item.title neq ''}
                                            {assign var="tile_sub_title" value=$item.sub_title|default:''}
                                            {assign var="tile_sub_href" value=$item.sub_href|default:''}
                                            {assign var="tile_sub_video" value=''}
                                            {if $tile_sub_title eq '' and ($item.title eq 'نوبت دهی پذیرش' or $item.title eq 'نوبت‌دهی پذیرش')}
                                                {assign var="tile_sub_title" value='آموزش نوبت‌دهی'}
                                                {if $tile_sub_href eq ''}
                                                    {assign var="tile_sub_href" value="`$admin_setting.file_url`/5a3caf8f-bb3b-11f1-ad46-005056b972d8"}
                                                    {assign var="tile_sub_video" value=$tile_sub_href}
                                                {/if}
                                            {/if}
                                            {if $tile_sub_href eq '' and $tile_sub_title neq ''}
                                                {assign var="tile_sub_href" value='#'}
                                            {/if}
                                            <div class="item">
                                                <a href="{if !empty($item) and $item.href neq ''}{$item.href}{/if}" class="item-link">
                                                    <img src="{$admin_setting.file_url}/thumb/{$item.image}/100" class="img_icon"
                                                        alt="{$item.title|default: ''}" loading="lazy" decoding="async">
                                                    <p class="text">
                                                        {$item.title|default: ''}
                                                        <i class="icon icon-arrow_left"></i>
                                                    </p>
                                                </a>
                                                {if $tile_sub_title neq ''}
                                                    {if $tile_sub_video neq ''}
                                                        <a href="{$tile_sub_video|escape:'html'}" class="item-sublink" data-home-video="{$tile_sub_video|escape:'html'}" data-home-video-title="{$tile_sub_title|escape:'html'}" target="_blank" rel="noopener">{$tile_sub_title}</a>
                                                    {else}
                                                        <a href="{$tile_sub_href}" class="item-sublink">{$tile_sub_title}</a>
                                                    {/if}
                                                {/if}
                                            </div>
                                        {/if}
                                    {/foreach}
                                {/if}
                            </div>
                            <div class="box_image">
                                <img src="{$admin_setting.file_url}/thumb/{$section_5.image}/960" class="img"
                                    alt="{$section_5.title|default: ''}" loading="lazy" decoding="async">
                                <div class="content">
                                    <p class="text">{$section_5.title|default: ''}</p>
                                    <h2 class="title">{$section_5.subtitle|default: ''}</h2>
                                    <a href="{$links[0].href|default: ''}" class="link">{$links[0].title|default: ''}</a>
                                </div>
                            </div>
                        </div>
                    </section>
                {/if}
            </div>

            {if !empty($section_6)}
                {$feature_cols = $section_6|@count}
                {if $feature_cols < 1}{$feature_cols = 1}{/if}
                <section class="feature_highlights" style="padding-block: 8px; --feature-cols: {$feature_cols};">
                    <div class="card">
                        {foreach $section_6 as $item}
                            {$feature_title = $item.title|default:''}
                            {$feature_subtitle = $item.subtitle|default:''}
                            {$feature_image = $item.image|default:''}
                            {$links = []}
                            {if isset($item.link) and $item.link neq ''}
                                {$decoded_links = $item.link|json_decode}
                                {if is_array($decoded_links)}{$links = $decoded_links}{/if}
                            {/if}
                            {$feature_link_title = ''}
                            {$feature_link_href = ''}
                            {if isset($links[0]) and is_array($links[0])}
                                {$feature_link_title = $links[0].title|default:''}
                                {$feature_link_href = $links[0].href|default:''}
                            {/if}
                            <div class="feature_wrapper" style="padding-block: 60px;">
                                <div class="top">
                                    {if $feature_image neq ''}
                                        <div class="box_image">
                                            <img src="{$admin_setting.file_url}/thumb/{$feature_image}/450" class="img" alt="{$feature_title|escape:'html'}" loading="lazy" decoding="async">
                                        </div>
                                    {/if}
                                    {if $feature_title neq '' or $feature_subtitle neq ''}
                                        <div class="content">
                                            {if $feature_title neq ''}
                                                <h2 class="title">{$feature_title|escape:'html'}</h2>
                                            {/if}
                                            {if $feature_subtitle neq ''}
                                                <p class="desc">{$feature_subtitle|escape:'html'}</p>
                                            {/if}
                                        </div>
                                    {/if}
                                </div>
                                {if $feature_link_title neq '' and $feature_link_href neq ''}
                                    <a href="{$feature_link_href|escape:'html'}" class="link">{$feature_link_title|escape:'html'}</a>
                                {/if}
                            </div>
                        {/foreach}
                    </div>
                </section>
            {/if}


            {if !empty($company)}
                <section class="brands">
                    <div class="brand-carousel container" data-brand-carousel>
                        <button type="button" class="brand-carousel__nav brand-carousel__nav--prev" aria-label="اسلاید قبلی">
                            <i class="icon icon-arrow_right"></i>
                        </button>

                        <div class="brand-carousel__viewport">
                            <div class="brand-carousel__track">
                                {foreach $company as $item}
                                    <a href="{$admin_setting.site_url}/brand/{$item.id}" class="brand-carousel__item">
                                        <img src="{$admin_setting.file_url}/thumb/{$item.values.image}/70" class="img" alt="{$item.values.title|escape:'html'}" loading="lazy" decoding="async" width="70" height="70">
                                        <p class="title">{$item.values.title}</p>
                                    </a>
                                {/foreach}
                            </div>
                        </div>

                        <button type="button" class="brand-carousel__nav brand-carousel__nav--next" aria-label="اسلاید بعدی">
                            <i class="icon icon-arrow_left"></i>
                        </button>
                    </div>
                </section>
            {/if}

        </div>

        <footer class="footer_bahman footer_bahman--flush">
            {include file="./includes/footer.tpl"}
        </footer>

        <div class="home-video-modal" id="homeVideoModal" hidden>
            <div class="home-video-modal__backdrop" data-home-video-close></div>
            <div class="home-video-modal__panel" role="dialog" aria-modal="true" aria-labelledby="homeVideoModalTitle">
                <div class="home-video-modal__head">
                    <h2 class="home-video-modal__title" id="homeVideoModalTitle"></h2>
                    <button type="button" class="home-video-modal__close" data-home-video-close aria-label="بستن">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/></svg>
                    </button>
                </div>
                <video class="home-video-modal__video" controls playsinline preload="none"></video>
            </div>
        </div>

        {include file="./includes/footer_include.tpl"}
        <script defer src="{$admin_setting.template_url}/assets/home-video-modal.js?v={$template_version}"></script>
    </body>

</html>
{/strip}