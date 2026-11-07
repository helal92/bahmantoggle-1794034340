{strip}
<!doctype html>
<html lang="fa" dir="rtl">

<head>
    {include file="./includes/header_include.tpl"}
    <link rel="stylesheet" crossorigin href="{$admin_setting.template_url}/assets/product_list.css?v={$template_version}">
</head>

<body>
    <div class="overlay"></div>

    <a href="#" id="go-to-top">
        <i class="icon icon-arrow-up2"></i>
    </a>

    <header class="header">
        {include file="./includes/header.tpl"}
    </header>

    <main class="product-list-page{if isset($filter_branch_title) and $filter_branch_title neq ''} is-branch-filtered{/if}" id="product-list-page">
        <div class="list_products">
            <div class="product-list-toolbar">
                {if isset($filter_branch_title) and $filter_branch_title neq ''}
                    <div class="product-list-branch" role="status" aria-label="فیلتر شرکت">
                        <div class="container">
                            <div class="product-list-branch__inner">
                                <p class="product-list-branch__text">
                                    <span class="product-list-branch__eyebrow">محصولات</span>
                                    <span class="product-list-branch__name">{$filter_branch_title|escape:'html'}</span>
                                </p>
                                <a href="{$admin_setting.site_url}/product" class="product-list-branch__clear">همه محصولات</a>
                            </div>
                        </div>
                    </div>
                {/if}

                <div class="brands_list">
                    <div class="container">
                        <div class="card2">
                            <div class="right" role="tablist" aria-label="دسته‌بندی محصولات">
                                {if !empty($product_category)}
                                    {$conter = 1}
                                    {foreach $product_category as $item}
                                        {if isset($item.values.title) and $item.values.title neq '' and isset($item.child_table) and isset($item.child_table.product_category) and !empty($item.child_table.product_category)}
                                            <div class="item {if $conter eq 1}active{/if}" role="presentation">
                                                <a href="#product-list-category{$item.id}" class="link" role="tab" aria-selected="{if $conter eq 1}true{else}false{/if}">{$item.values.title}</a>
                                            </div>
                                            {$conter = $conter + 1}
                                        {/if}
                                    {/foreach}
                                {/if}
                            </div>
                            <div class="product-list-search">
                                <label class="product-list-search__field">
                                    <span class="product-list-search__icon" aria-hidden="true">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                            <circle cx="11" cy="11" r="6.5" stroke="currentColor" stroke-width="1.7"/>
                                            <path d="M16.2 16.2L21 21" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>
                                        </svg>
                                    </span>
                                    <input type="search" class="product-list-search__input" data-product-search placeholder="جستجوی محصول..." autocomplete="off" enterkeyhint="search">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="cars_list">
                <div class="container">
                    <div class="card3">
                        {if !empty($product_category)}
                            {foreach $product_category as $item}
                                {if isset($item.values.title) and $item.values.title neq '' and isset($item.child_table) and isset($item.child_table.product_category) and !empty($item.child_table.product_category)}
                                    <section class="item" id="product-list-category{$item.id}" aria-labelledby="product-list-title{$item.id}">
                                        <h2 class="title" id="product-list-title{$item.id}">{$item.values.title}</h2>
                                        <div class="box_cars">
                                            {$car_i = 0}
                                            {foreach $item.child_table.product_category as $car}
                                                {$car_i = $car_i + 1}
                                                <div class="car" data-product-card data-product-name="{$car.values.title|default:''|escape:'html'}">
                                                    <div class="top">
                                                        <a href="{$admin_setting.site_url}/product/{$car.id}" class="link">
                                                            <div class="box_image">
                                                                {if $car_i <= 6}
                                                                    <img src="{$admin_setting.file_url}/thumb/{$car.values.image}/300" class="img" alt="{$car.values.title|default: ''}" loading="lazy" width="300" height="140">
                                                                {else}
                                                                    <img data-src="{$admin_setting.file_url}/thumb/{$car.values.image}/300" class="img" alt="{$car.values.title|default: ''}" loading="lazy" width="300" height="140">
                                                                {/if}
                                                            </div>
                                                            <h3 class="model_name">{$car.values.title|default: ''}</h3>
                                                        </a>
                                                        <div class="price"></div>
                                                    </div>
                                                    <div class="cta">
                                                        <div class="build"></div>
                                                        <div class="nearby"></div>
                                                    </div>
                                                </div>
                                            {/foreach}
                                        </div>
                                    </section>
                                {/if}
                            {/foreach}
                        {/if}
                    </div>
                    <p class="product-list-empty" data-product-empty hidden>محصولی با این عبارت پیدا نشد.</p>
                </div>
            </div>
        </div>
    </main>

    <footer class="footer_bahman">
        {include file="./includes/footer.tpl"}
    </footer>

    {include file="./includes/footer_include.tpl"}
    <script defer src="{$admin_setting.template_url}/assets/product-list.js?v={$template_version}"></script>

</body>

</html>
{/strip}
