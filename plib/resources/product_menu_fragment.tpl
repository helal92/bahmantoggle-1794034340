{strip}
<div class="brands_list">
    <div class="container">
        <div class="card2">
            <div class="right">
                {if !empty($product_category)}
                    {$conter = 1}
                    {foreach $product_category as $item}
                        {if isset($item.values.title) and $item.values.title neq '' and isset($item.child_table) and isset($item.child_table.product_category) !empty($item.child_table.product_category)}
                            <div class="item {if $conter eq 1}active{/if}">
                                <a href="#product-category{$item.id}" class="link">{$item.values.title}</a>
                            </div>
                            {$conter = $conter + 1}
                        {/if}
                    {/foreach}
                {/if}
            </div>
            <div class="left">
                <i class="icon icon-close" id="icon_close"></i>
            </div>
        </div>
    </div>
</div>

<div class="cars_list">
    <div class="container">
        <div class="card3">
            {if !empty($product_category)}
                {foreach $product_category as $item}
                    {if isset($item.values.title) and $item.values.title neq '' and isset($item.child_table) and isset($item.child_table.product_category) !empty($item.child_table.product_category)}
                        <div class="item" id="product-category{$item.id}">
                            <h2 class="title">{$item.values.title}</h2>
                            <div class="box_cars">
                                {$car_i = 0}
                                {foreach $item.child_table.product_category as $car}
                                    {$car_i = $car_i + 1}
                                    <div class="car">
                                        <div class="top">
                                            <a href="{$admin_setting.site_url}/product/{$car.id}" class="link">
                                                <div class="box_image">
                                                    {if $car_i <= 6}
                                                        <img src="{$admin_setting.file_url}/thumb/{$car.values.image}/300" class="img" alt="{$car.values.title}" loading="lazy" decoding="async" width="300" height="162">
                                                    {else}
                                                        <img data-src="{$admin_setting.file_url}/thumb/{$car.values.image}/300" class="img" alt="{$car.values.title}" loading="lazy" decoding="async" width="300" height="162">
                                                    {/if}
                                                </div>
                                                <h3 class="model_name">{$car.values.title}</h3>
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
                        </div>
                    {/if}
                {/foreach}
            {/if}
        </div>
    </div>
</div>
{/strip}
