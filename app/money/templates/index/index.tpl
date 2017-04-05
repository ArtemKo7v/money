{if !$oAuth->isLoggedIn()}
<form class="loginForm" onsubmit="login(); return false;">
    <div class="row">
        <div class="col-xs-12 text-right">
            <span class="loginFormHeader">Существующий пользователь</span>
        </div>
    </div>
    <div class="row loginFormError">
        <div class="col-xs-12 text-right">
            <span class="alert alert-danger">Ошибка авторизации!</span>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 text-right">
            <input id="username" class="input-lg" placeholder="E-Mail" autocomplete="0">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 text-right">
            <input type="password" id="password" class="input-lg" placeholder="Пароль" autocomplete="0">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 text-right">
            <button type="submit" class="btn btn-lg btn-success">Войти</button>
        </div>
    </div>
</form>
{literal}
<script>
    function login(){
        var user = $('#username').val();
        var pass = $('#password').val();
        $('.loginFormError').hide();
        $.post('/login', {username: user, password: pass}, function(data){
            if('OK' == data){
                document.location.reload();
            }else{
                $('.loginFormError').slideDown();
            }
        });
    }
</script>
{/literal}
{else}
{/if}