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
        var plan = $('<div>');
        var real = $('<div>');

        for(var category in data.data){
            for(var i=0; i<data.data[category].items.length; i++){
                var item = data.data[category].items[i];

                var row = $('<div>');
                row.addClass('row');

                var title = $('<div>');
                title.addClass('col-xs-5');
                title.text(item.title);

                var value = $('<div>');
                value.addClass('col-xs-4 text-right');
                value.text(item.value);

                value.addClass((parseInt(item.value) < 0) ? 'text-danger' : 'text-success');

                row.append(title);
                row.append(value);
                real.append(row);
            }
        }
        
        res.append(plan);
        res.append(real);

        console.log(res.html());

        new MyBills.dialog(res.html());
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