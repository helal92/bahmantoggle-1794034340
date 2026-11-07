{strip}
{* {$legal_requirements = ''}
{$sales_tracking_status = ''}
{$sales_notices = ''}
{if isset($sales_page) and isset($sales_page.child.content_section) and !empty($sales_page.child.content_section)}
    {foreach $sales_page.child.content_section as $item}
        {if isset($item.values) and !empty($item.values) and isset($item.values.section_key) and $item.values.section_key neq ''}
            {if $item.values.section_key eq 'legal_requirements'}
                {$legal_requirements = $item.values}
            {else if $item.values.section_key eq 'sales_tracking_status'}
                {$sales_tracking_status = $item.values}
            {else if $item.values.section_key eq 'sales_notices'}
                {$sales_notices = $item.values}
            {/if}
        {/if}
    {/foreach}
{/if} *}
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    {include file="./includes/header_include.tpl"}
</head>

<body>
    <div class="overlay"></div>

    <a href="#" id="go-to-top">
        <i class="icon icon-arrow-up2"></i>
    </a>

    <header class="header">
        {include file="./includes/header.tpl"}
    </header>

    <div style="display: flex; flex-direction: column; gap: 70px; width: 100%;">

        {if !empty($sales_page)}
            <section class="hero-selas">
                <img src="/sales-img/{$sales_page.values.banner}_1920.jpg" class="img" alt="{$sales_page.values.title}" width="1920" height="480" decoding="async" fetchpriority="high">
                <div class="text-box">
                    <p class="title container">صفحه فروش</p>
                </div>
            </section>
        {/if}

        {if !empty($sales_active)}
            <section class="slider_car container">
                {* {if !empty($sales_active)}
                    {foreach $sales_active as $item}
                        <div class="top d-flex align-items-center justify-content-center">
                            <div class="box_img">
                                <img src="{$admin_setting.file_url}/thumb/{$item.values.image}/960" class="img" alt="{$item.values.title}">
                            </div>
                            <div class="content d-flex flex-column">
                                <h2 class="title">{$item.values.title|default: ''}</h2>
                                <div class="desc d-flex flex-column">
                                    <p>از {$item.values.start_date|default: ''} تا {$item.values.end_date|default: ''}</p>
                                    {if isset($item.values.code) and $item.values.code neq ''}
                                        <p>کد بخشنامه: {$item.values.code}</p>
                                    {/if}
                                    {if isset($item.values.desc) and $item.values.desc neq ''}
                                        <p>{$item.values.desc}</p>
                                    {/if}
                                </div>
                                <a href="{$admin_setting.file_url}/{$item.values.attachemnt}" target="_blank" class="link">مشاهده بخشنامه فروش</a>
                            </div>
                        </div>
                    {/foreach}
                {/if} *}
                {if !empty($company_all) and !empty($sales_active)}
                    <div class="top">
                        <h2 class="title">بخشنامه‌های فروش فعال</h2>
                        <div class="Brands">
                            {$conter = 1}
                            {foreach $company_all as $item}
                                {if isset($sales_active[$item.id])}
                                    <p class="Item {if $conter eq 1}active{/if}" tab_content_title="item{$item.id}">
                                        {$item.values.title}
                                    </p>
                                    {$conter = $conter + 1}
                                {/if}
                            {/foreach}
                        </div>
                    </div>

                    {$conter = 1}
                    {foreach $company_all as $item}
                        {if isset($sales_active[$item.id])}
                            <div class="carousel-wrap {if $conter eq 1}active{/if}" tab_content_item="item{$item.id}">
                                <div class="swiper ProductCategory_Slider">
                                    <div class="navigation prev">
                                        <i class="icon icon-arrow_right"></i>
                                    </div>

                                    <div class="swiper-wrapper">
                                        {foreach $sales_active[$item.id] as $pro}
                                            <div class="swiper-slide">
                                                <div class="Item">
                                                    <img class="image"
                                                        src="/sales-img/{$pro.values.image}_600.png"
                                                        alt="{$pro.values.title|default: ''}"
                                                        width="600"
                                                        height="400"
                                                        loading="lazy"
                                                        decoding="async"
                                                        data-title="{$pro.values.title|default: ''|escape:'html'}"
                                                        data-desc="{$pro.values.desc|default: ''|escape:'html'}"
                                                        {if isset($pro.values.attachemnt) and $pro.values.attachemnt neq ''}data-show-link="{$admin_setting.file_url}/{$pro.values.attachemnt}"{/if}
                                                        {if isset($pro.values.product) and $pro.values.product neq ''}data-product-link="{$admin_setting.site_url}/product/{$pro.values.product}"{/if}
                                                        {if isset($pro.values.sales_link) and $pro.values.sales_link neq ''}data-buy-link="{$pro.values.sales_link}"{/if}>
                                                </div>
                                            </div>
                                        {/foreach}
                                    </div>

                                    <div class="navigation next">
                                        <i class="icon icon-arrow_left"></i>
                                    </div>
                                </div>
                            </div>
                            {$conter = $conter + 1}
                        {/if}
                    {/foreach}

                    <div class="info">
                        <div class="text_box">
                            <h3 id="product_slider_title" class="Title"></h3>
                            <p id="product_slider_desc" class="desc"></p>
                        </div>
                        <div class="box_btn">
                            <a id="product_slider_product_link" class="Link2" href="#" style="display: none;">صفحه محصول</a>
                            <a id="product_slider_link" class="Link2" href="#" style="display: none;">مشاهده بخشنامه</a>
                            <a id="product_slider_link2" class="Link2" href="#" target="_blank" style="display: none;">خرید خودرو</a>
                        </div>
                    </div>
                {/if}
            </section>
        {/if}

        <section class="accordion-section  d-flex flex-column container">
            {if !empty($sales_inactive) and !empty($sales_inactive_company_order)}
                <div class="accordion">
                    <button type="button" class="accordion_header d-flex align-items-center justify-content-between">
                        <span class="accordion_title">آرشیو بخشنامه‌های فروش (غیر فعال)</span>
                        <span class="accordion_icon">
                            <i class="icon icon-arrow_down"></i>
                        </span>
                    </button>
                    <div class="accordion_content d-flex flex-column">
                        <div class="sales-inactive">
                            {foreach $sales_inactive_company_order as $company}
                                {if isset($sales_inactive[$company.id])}
                                    <div class="accordion sales-inactive-company">
                                        <button type="button" class="accordion_header d-flex align-items-center">
                                            <span class="accordion_title">{$company.values.title|default: ''}</span>
                                            {if isset($company.inactive_count) and $company.inactive_count neq ''}
                                                <span class="sales-inactive-company__count" title="تعداد بخشنامه‌ها">{$company.inactive_count|default:0}</span>
                                            {/if}
                                            <span class="accordion_icon">
                                                <i class="icon icon-arrow_down"></i>
                                            </span>
                                        </button>
                                        <div class="accordion_content">
                                            <ul class="sales-inactive-list">
                                                {foreach $sales_inactive[$company.id] as $item}
                                                    <li class="sales-inactive-list__row">
                                                        <a class="sales-inactive-item" href="{$admin_setting.file_url}/{$item.values.attachemnt}" target="_blank" rel="noreferrer noopener">
                                                            <span class="sales-inactive-item__body">
                                                                <span class="sales-inactive-item__title">{$item.values.title|default: ''}</span>
                                                                <span class="sales-inactive-item__meta">
                                                                    {if isset($item.values.code) and $item.values.code neq ''}
                                                                        <span class="sales-inactive-item__code">{$item.values.code}</span>
                                                                    {/if}
                                                                    {if isset($item.values.start_date) and $item.values.start_date neq ''}
                                                                        <span class="sales-inactive-item__date">{$item.values.start_date|date_only}</span>
                                                                    {/if}
                                                                </span>
                                                            </span>
                                                        </a>
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        </div>
                                    </div>
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
            {/if}
            {if isset($sales_page) and isset($sales_page.child.content_section) and !empty($sales_page.child.content_section)}
                {foreach $sales_page.child.content_section as $item}
                    {if isset($item.values) and !empty($item.values)}
                        {assign var="links" value=$item.values.link|json_decode}
                        <div class="accordion">
                            <button class="accordion_header d-flex align-items-center justify-content-between">
                                <span class="accordion_title">{$item.values.title|default: ''}</span>
                                <span class="accordion_icon">
                                    <i class="icon icon-arrow_down"></i>
                                </span>
                            </button>
                            <div class="accordion_content d-flex flex-column">
                                {if !empty($links)}
                                    {foreach $links as $item}
                                        {if isset($item.title) and $item.title neq ''}
                                            {$link = ''}
                                            {if isset($item.attachment) and $item.attachment neq ''}
                                                {$link = $admin_setting.file_url|cat:'/'|cat:$item.attachment}
                                            {else if isset($item.href) and $item.href neq ''}
                                                {$link = $item.href}
                                            {/if}
                                            <a href="{$link}" target="_blank" rel="noreferrer noopener">{$item.title}</a>
                                        {/if}
                                    {/foreach}
                                {/if}
                            </div>
                        </div>
                    {/if}
                {/foreach}
            {/if}


            {* {if !empty($sales_inactive)}
                <div class="accordion">
                    <button class="accordion_header d-flex align-items-center justify-content-between">
                        <span class="accordion_title">آرشیو بخشنامه‌های فروش (غیر فعال)</span>
                        <span class="accordion_icon">
                            <i class="icon icon-arrow_down"></i>
                        </span>
                    </button>
                    <div class="accordion_content d-flex flex-column">
                        {foreach $sales_inactive as $item}
                            <a href="{$admin_setting.file_url}/{$item.values.attachemnt}" target="_blank" rel="noreferrer noopener">
                                {$item.values.title|default: ''} &nbsp;
                                - {$item.values.start_date|default: ''} &nbsp;
                                {if isset($item.values.code) and $item.values.code neq ''}- کد بخشنامه: {$item.values.code}{/if}
                            </a>
                        {/foreach}
                    </div>
                </div>
            {/if}

            {if !empty($legal_requirements)}
                {assign var="links" value=$legal_requirements.link|json_decode}
                <div class="accordion">
                    <button class="accordion_header d-flex align-items-center justify-content-between">
                        <span class="accordion_title">{$legal_requirements.title|default: ''}</span>
                        <span class="accordion_icon">
                            <i class="icon icon-arrow_down"></i>
                        </span>
                    </button>
                    <div class="accordion_content d-flex flex-column">
                        {if !empty($links)}
                            {foreach $links as $item}
                                {$link = ''}
                                {if isset($item.attachment) and $item.attachment neq ''}
                                    {$link = $admin_setting.file_url|cat:'/'|cat:$item.attachment}
                                {else if isset($item.href) and $item.href neq ''}
                                    {$link = $item.href}
                                {/if}
                                <a href="{$link}" target="_blank" rel="noreferrer noopener">{$item.title}</a>
                            {/foreach}
                        {/if}
                    </div>
                </div>
            {/if}

            {if !empty($sales_notices)}
                {assign var="links" value=$sales_notices.link|json_decode}
                <div class="accordion">
                    <button class="accordion_header d-flex align-items-center justify-content-between">
                        <span class="accordion_title">{$sales_notices.title|default: ''}</span>
                        <span class="accordion_icon">
                            <i class="icon icon-arrow_down"></i>
                        </span>
                    </button>
                    <div class="accordion_content d-flex flex-column">
                        {if !empty($links)}
                            {foreach $links as $item}
                                {$link = ''}
                                {if isset($item.attachment) and $item.attachment neq ''}
                                    {$link = $admin_setting.file_url|cat:'/'|cat:$item.attachment}
                                {else if isset($item.href) and $item.href neq ''}
                                    {$link = $item.href}
                                {/if}
                                <a href="{$link}" target="_blank" rel="noreferrer noopener">{$item.title}</a>
                            {/foreach}
                        {/if}
                    </div>
                </div>
            {/if}

            {if !empty($sales_tracking_status)}
                {assign var="links" value=$sales_tracking_status.link|json_decode}
                <div class="accordion">
                    <button class="accordion_header d-flex align-items-center justify-content-between">
                        <span class="accordion_title">{$sales_tracking_status.title|default: ''}</span>
                        <span class="accordion_icon">
                            <i class="icon icon-arrow_down"></i>
                        </span>
                    </button>
                    <div class="accordion_content d-flex flex-column">
                        {if !empty($links)}
                            {foreach $links as $item}
                                {$link = ''}
                                {if isset($item.attachment) and $item.attachment neq ''}
                                    {$link = $admin_setting.file_url|cat:'/'|cat:$item.attachment}
                                {else if isset($item.href) and $item.href neq ''}
                                    {$link = $item.href}
                                {/if}
                                <a href="{$link}" target="_blank" rel="noreferrer noopener">{$item.title}</a>
                            {/foreach}
                        {/if}
                    </div>
                </div>
            {/if} *}
        </section>

    </div>

    <footer class="footer_bahman">
        {include file="./includes/footer.tpl"}
    </footer>
    {include file="./includes/footer_include.tpl"}

</body>

</html>
{/strip}