<div class="addFormStatus">
    <div class="row">
        <div class="col-xs-offset-1 col-sm-offset-4 col-xs-10 col-sm-4">
            <div class="alert alert-success">Сохранено</div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-offset-1 col-sm-offset-4 col-xs-10 col-sm-4 text-center">
            <a class="btn btn-lg btn-success" href="/add">Добавить еще одну запись</a><br />
            <a class="btn btn-lg btn-success" href="/month">К текущему месяцу</a><br />
            <a class="btn btn-lg btn-success" href="/">Вернуться на главную</a>
        </div>
    </div>
</div>
<div class="addForm">
    <div class="row">
        <div class="col-xs-12 col-sm-4"></div>
        <div class="col-xs-12 col-sm-4 fieldControl modeButtons">
            <button id="expense" class="btn btn-lg btn-info">Расход</button>
            <button id="income" class="btn btn-lg btn-link">Поступление</button>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4 col-sm-4 fieldTitle">Дата:</div>
        <div class="col-xs-8 col-sm-8 fieldControl">
            <input type="date" id="date" class="input-lg" value="{if $date}{$date}{else}{$smarty.now|date_format:"%Y-%m-%d"}{/if}">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-4 col-sm-4 fieldTitle visible-xs">В план:</div>
        <div class="col-xs-4 col-sm-4 fieldTitle hidden-xs">Добавить в план:</div>
        <div class="col-xs-8 col-sm-8 fieldControl">
            <div id="add_to_plan_border">
                <input type="checkbox" id="add_to_plan" value="1" class="input-lg">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-4 fieldTitle">Категория:</div>
        <div class="col-xs-12 col-sm-8 fieldControl">
            <select name="category_id" class="input-lg categoryIncome">
                <option value="0" style="color:#aaa">Не указана</option>
                {foreach from=$aIncomeCategories item="category"}
                    <option value="{$category.id}" data-expense="{$category.expense}">{$category.name}</option>
                {/foreach}
            </select>
            <select name="category_id" class="input-lg categoryExpense">
                <option value="0" style="color:#aaa">Не указана</option>
                {foreach from=$aExpenseCategories item="category"}
                    <option value="{$category.id}" data-expense="{$category.expense}">{$category.name}</option>
                {/foreach}
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-4 fieldTitle">Сумма:</div>
        <div class="col-xs-12 col-sm-8 fieldControl">
            <input type="number" id="amount" class="input-lg expense" value="0.00">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-4 fieldTitle">Назначение:</div>
        <div class="col-xs-12 col-sm-8 fieldControl">
            <input id="title" class="input-lg" placeholder="Опционально">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-4 fieldTitle">Описание:</div>
        <div class="col-xs-12 col-sm-8 fieldControl">
            <input id="description" class="input-lg" placeholder="Опционально">
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-4"></div>
        <div class="col-xs-12 col-sm-8 fieldControl">
            <button id="submit" class="btn btn-lg btn-success">Добавить</button>
        </div>
    </div>
</div>
{literal}
<script>
    var expense = true;
    $('#expense').click(function(){
        if($(this).hasClass('btn-link')){
            $(this).removeClass('btn-link');
            $(this).addClass('btn-info');
            $('#income').addClass('btn-link');
            $('#income').removeClass('btn-info');
            $('.categoryIncome').hide();
            $('.categoryExpense').show();
            $('#amount').addClass('expense');
            expense = true;
        }
    });
    $('#income').click(function(){
        if($(this).hasClass('btn-link')){
            $(this).removeClass('btn-link');
            $(this).addClass('btn-info');
            $('#expense').addClass('btn-link');
            $('#expense').removeClass('btn-info');
            $('#amount').removeClass('expense');
            $('.categoryIncome').show();
            $('.categoryExpense').hide();
            expense = false;
        }
    });
    $('#submit').click(function(){
        var categoryId = parseInt($('select[name=category_id]:visible').val());
        var amount = parseFloat($('#amount').val());
        if(!categoryId){
            alert('Выберите категорию!');
            return;
        }
        if(!amount){
            alert('Сумма не может быть нулевой!');
            return;            
        }
        // @todo: check date
        var date = $('#date').val();
        var title = $('#title').val();
        var description = $('#description').val();
        amount = Math.abs(parseFloat(amount));
        if(expense){
            amount = -amount;
        }
        $.post('/add', {
            date: date,
            category: categoryId,
            amount: amount,
            title: title,
            description: description,
            to_plan: $('#add_to_plan:checked').length
        }, function(data){
            if('OK' === data){
                $('.addForm').hide();
                $('.addFormStatus').show();
            }
        })
    });
</script>
{/literal}