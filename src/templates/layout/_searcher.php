<form action="/search" method="get" class="search-form">
    <div class="search-wrapper">
        <input class="search-field" type="text" name="query" placeholder="<?php echo $query ? $query : 'Search your friends'; ?>" tabindex="1" autofocus />
        <input class="reset" type="reset" value="&#xf00d;" tabindex="3" />
        <input class="search" type="submit" value="Search" tabindex="2" />
    </div>
</form>
