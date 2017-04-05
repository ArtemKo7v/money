{if !empty($aMonthData)}
<div class="blackBorder">
    <table class="monthDetails" cellspacing="0" cellpadding="0">
        <tr>
            <th colspan="16">{$aMonthData.month} {$aMonthData.year}</th>
        </tr>
        <tr>
            <th colspan="2" rowspan="2">Дата</th>
            <th rowspan="2">Остаток по плану</th>
            <th rowspan="2">Фактческий остаток</th>
            <th colspan="{$aMonthData['plan-maxlen']*2}">Плановые операции</th>
            <th colspan="{$aMonthData['data-maxlen']*2}">Фактические операции</th>
        </tr>
        <tr>
            {for $i=1 to $aMonthData['plan-maxlen']}<th>Описание</th><th>Сумма</th>{/for}
            {for $i=1 to $aMonthData['data-maxlen']}<th>Описание</th><th>Сумма</th>{/for}
        </tr>
    {foreach from=$aMonthData.dates item="row"}
    <tr class="dayrow dow{$row.dayOfWeek}"  data-json="{$row.JSON|escape}" data-date="{$aMonthData.year}-{"%02d"|sprintf:$aMonthData.monthNumber}-{"%02d"|sprintf:$row.date}">
        <td class="dow">{$row.dayName}</td>
        <td class="date">{$row.date}</td>
        <td class="planBalance">{$row['plan-balance']|string_format:"%.2f"|replace:".00":""}</td>
        <td class="realBalance">{$row['data-balance']|string_format:"%.2f"|replace:".00":""}</td>
        {if !empty($row.plan)}
            {foreach from=$row.plan key="cat" item="dataRow"}
                <td class="planTitle filled">{$cat}</td>
                <td class="planValue {if $dataRow.total<0}loss{/if}">{$dataRow.total}</td>
            {/foreach}
        {/if}
        {for $i=1 to ($aMonthData['plan-maxlen'] - count($row.plan))}
            <td class="planTitle">&nbsp;</td>
            <td class="planValue">&nbsp;</td>
        {/for}
        {if !empty($row.data)}
            {foreach from=$row.data key="cat" item="dataRow"}
                <td class="realTitle filled">{$cat}</td>
                <td class="realValue {if $dataRow.total<0}loss{/if}">{$dataRow.total}</td>
            {/foreach}
        {/if}
        {for $i=1 to ($aMonthData['data-maxlen'] - count($row.data))}
            <td class="realTitle">&nbsp;</td>
            <td class="realValue">&nbsp;</td>
        {/for}
    </tr>
    {/foreach}
    </table>
</div>
<script>
    MyBills.MonthReport.init();
</script>
{/if}