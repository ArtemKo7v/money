{if $oAuth->isLoggedIn()}
<nav class="navbar navbar-fixed-top">
    <div id="navbar">
        <ul>
            <li {if $action eq "index"}class="active"{/if}>
                <a href="/">
                    <span class="visible-xs"><i class="fa fa-bolt"></i></span>
                    <span class="hidden-xs">{$oLocales->get("menu.top.dashboard")}</span>
                </a>
            </li>
            <li {if $action eq "add"}class="active"{/if}>
                <a href="/add">
                    <span class="visible-xs"><i class="fa fa-plus-square" title="Добавить"></i></span>
                    <span class="hidden-xs">{$oLocales->get("menu.top.add")}</span>
                </a>
            </li>
            <li {if $action eq "month"}class="active"{/if}>
                <a href="/month">
                    <span class="visible-xs"><i class="fa fa-calendar"></i></span>
                    <span class="hidden-xs">{$oLocales->get("menu.top.report")}</span>
                </a>
            </li>
        </ul>
        <ul class="navbar-right">
            {assign var="user" value=$oAuth->getUser()}
            <li class="separator"></li>
            <li class="textLabel">
                <span class="visible-xs">THB <font color="green">{$lastBalance}</font></span>
                <span class="hidden-xs">{$oLocales->get("menu.top.balance")}: THB <font color="green">{$lastBalance}</font></span>
            </li>
            <li class="separator"></li>
            <li>
                <a href="/">
                    <span class="visible-xs"><i class="fa fa-user"></i></span>
                    <span class="hidden-xs">{$user.login}</span>
                </a>
            </li>
            <li>
                <a href="#" onclick="logout();" title="{$oLocales->get("menu.top.exit")}">
                    <i class="fa fa-sign-out"></i>
                </a>
            </li>
        </ul>
    </div>
</nav>
{/if}
{literal}
<script>
function logout(){
    $.get('/logout', {}, function(){
        document.location.href = '/';
    });
}
</script>
{/literal}