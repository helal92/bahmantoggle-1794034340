{strip}
<div class="container">
    <div class="card">
        <div class="main_menu">
            <div class="box_menu">
                {if isset($has_active_sale) and $has_active_sale}
                    <a href="{$admin_setting.site_url}/sales" class="btn_menu btn_menu--sale-beam">
                        <span class="btn_menu__ring" aria-hidden="true"></span>
                        <span class="btn_menu__inner">
                            <span class="btn_menu__label">فروش فعال</span>
                        </span>
                    </a>
                {/if}
                <ul class="menu" id="mobile_meno">
                    <li class="item" id="item">
                        <span class="link">محصولات
                            <i class="icon icon-arrow_down" id="arrow_down"></i>
                        </span>
                    </li>
                    {if isset($menu.header_menu) and !empty($menu.header_menu[0].links)}
                        {foreach $menu.header_menu[0].links as $item}
                            {$link = ''}
                            {$target = ''}
                            {if isset($item.attachment) and $item.attachment neq ''}
                                {$link = $admin_setting.file_url|cat:'/'|cat:$item.attachment}
                                {$target = 'target="_blank"'}
                            {else if isset($item.href) and $item.href neq ''}
                                {$link = $item.href}
                            {/if}
                            <li class="item">
                                <a href="{$link}" {$target} class="link">{$item.title}</a>
                            </li>
                        {/foreach}
                    {/if}
                </ul>

                <span class="header-search-divider" aria-hidden="true"></span>

                <form class="search" name="searchForm" method="GET" action="{$admin_setting.site_url}/search" aria-label="جستجو در سایت">
                    <input type="text" name="q" class="search-input" placeholder="جستجو..." aria-label="متن جستجو" autocomplete="off" />
                    <button type="button" class="search-close" aria-label="بستن جستجو">
                        <i class="icon icon-close"></i>
                    </button>
                    <button type="submit" class="search-btn" aria-label="جستجو">
                        <i class="icon icon-search"></i>
                        <span class="search-text">جستجو</span>
                    </button>
                    <ul class="suggestions" role="listbox" aria-label="پیشنهادات جستجو"></ul>
                </form>
            </div>

            <a href="{$admin_setting.site_url}" class="logo">
                <img src="{$admin_setting.template_url}/images/logo/bahman-logo-white.png" class="img_logo" alt="گروه بهمن">
            </a>

            <label class="burger" for="burger" id="hamberger_menu">
                <input type="checkbox" id="burger">
                <span></span>
                <span></span>
                <span></span>
            </label>
        </div>

        <div class="submenu" data-product-menu-url="{$admin_setting.site_url}/product-menu">
            <div class="card" id="product-menu-root" aria-live="polite">
                {* Products load on first click/hover via product-menu-lazy.js *}
            </div>
        </div>
    </div>
</div>
{/strip}