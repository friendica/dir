<a class="hamburger mobile"><i class="fa fa-bars"></i></a>
<nav id="links">
    <div class="viewport">
        <a href="/home" class="mobile">Home</a>
        <a href="/servers">Servers</a>
        <a href="/stats">Stats</a>
        <a href="/help">Help</a>
    </div>
</nav>

<script type="text/javascript">
    jQuery('.hamburger').on('click', function(e){
        e.preventDefault();
        jQuery('nav#links').toggleClass('open');
    })
</script>