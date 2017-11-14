// Application object
var MyBills = {
    init: function(){
        MyBills.initDialogs();
    },

    initDialogs: function(){
        $(document).on('click', '.dialogBackground:visible', function(){
            if(MyBills.lastOpenDialog){
                MyBills.lastOpenDialog.close();
            }
        });

        $(document).keydown(function(e){
            if(e.keyCode == 27){
                if(MyBills.lastOpenDialog){
                    MyBills.lastOpenDialog.close();
                }
            }
        });        
    }
};

MyBills.MonthReport = {
    // Initialize report events
    init: function(){
        $('.dayrow').click(function(){
            var data = JSON.parse($(this).attr('data-json'));
            if(data){
                MyBills.MonthReport.openDetailsPopup(data);
            }            
        });
        $('.dayrow').each(function(){
            var addElement = $('<div><div><i class="fa fa-plus"></i></div></div>');
            addElement.addClass('addNewTransaction');
            addElement.click(function(){
                document.location.href = '/add?date=' + $(this).parents('.dayrow:eq(0)').attr('data-date');
            })
            $(this).find('.realValue:last').append(addElement);
        });
        $('.dayrow').mouseover(function(){
            $('.addNewTransaction').hide();
            $(this).find('.addNewTransaction').show();
        });
    },

    openDetailsPopup: function(data){
        /*
         
        {
            "date": 2,
            "dayOfWeek": "Sun",
            "dayName": "Вс",
            "plan": [],
            "plan-change": 0,
            "plan-balance": "22100.00",
            "data": {
                "Красота": {
                    "items": [
                        {
                            "title": "Test",
                            "value": "-100.00"
                        }
                    ],
                    "total": -100
                }
            },
            "data-change": -100,
            "data-balance": 21900
        }

        var res = '';
        for(var i=0; i<data.length; i++){
            res = res + data[i].title + ': ' + data[i].value + "\n";
        }
        */
        console.log(JSON.stringify(data, null, 4));

        var res = $('<div>');
        res.addClass('dayDetails');

        var content = $('<div>');
        content.addClass('dialogWindowContent');

        var plan = $('<div>');
        var real = $('<div>');

        for(var category in data.data){
            var cat = data.data[category];

            var catRow = $('<div>');
            catRow.addClass('row categoryRow');

            var catTitle = $('<div>');
            catTitle.addClass('col-xs-6 categoryTitle');
            catTitle.text(category);

            var catTotal = $('<div>');
            catTotal.addClass('col-xs-4 text-right categoryTotal');
            catTotal.text(cat.total);
            catTotal.addClass((parseInt(cat.total) < 0) ? 'text-danger' : 'text-success');

            catRow.append(catTitle);
            catRow.append(catTotal);
            real.append(catRow);

            if((cat.items.length > 1) || ((cat.items.length == 1) && ((cat.items[0].title != category) || cat.items[0].details))){
                for(var i=0; i<cat.items.length; i++){
                    var item = cat.items[i];

                    var row = $('<div>');
                    row.addClass('row');

                    var title = $('<div>');
                    title.addClass('col-xs-offset-1 col-xs-5');
                    title.text(item.title);

                    if(item.details){
                        var details = $('<div>');
                        details.addClass('itemDetails');
                        details.text(item.details);
                        title.append(details);
                    }

                    var value = $('<div>');
                    value.addClass('col-xs-4 text-right');
                    value.text(item.value);
                    value.addClass((parseInt(item.value) < 0) ? 'text-danger' : 'text-success');

                    var icons = $('<div>');
                    icons.addClass('col-xs-2 text-right');
                    icons.append('<i class="fa fa-pencil">');
                    icons.append('<i class="fa fa-times">');

                    row.append(title);
                    row.append(value);
                    row.append(icons);

                    real.append(row);
                }
            }
        }
        
        content.append(plan);
        content.append(real);

        res.append(content);

        console.log(res);

        new MyBills.dialog(res);
    }
};

MyBills.dialog = function(el, options){
    var win = $(el);
    var obj = MyBills.lastOpenDialog = this;
    var options = options || {};

    this.open = function(){
        if(!win.hasClass('dialogWindow')){
            win.addClass('dialogWindow');
        }
        if(!win.parent().length){
            $('body').append(win);
            obj.removable = true;
        }
        // Add background
        if(!$('.dialogBackground:visible').length){
            $('.dialogBackground').remove();
            var bg = $('<div>');
            bg.addClass('dialogBackground');
            $('body').append(bg);
        }
        win.show();
        if(win.find('.dialogWindowContent').length){
            var contentHeight = win.find('.dialogWindowContent').height();
            var windowHeight = win.height();
            if((windowHeight - contentHeight) > 50){
                win.css('max-height', (contentHeight + 50) + 'px');
            }
        }
    };

    this.close = function(){
        win.hide();
        $('.dialogBackground').remove();
        if(obj.removable){
            win.remove();
        }
        MyBills.lastOpenDialog = false;
    }

    if(('undefined' === typeof(options.autoOpen)) || options.autoOpen){
        this.open();
    }
}

$(document).ready(function(){
    MyBills.init();
})