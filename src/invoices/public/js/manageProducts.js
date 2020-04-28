$(function () {


    $body = $('body');
    $body.on('click', 'button[data-action="addProduct"]', function () {
        var countries = $.ajax({type: "GET", url: "/countries/get", async: false}).responseJSON;
        console.log(countries);
        var countriesHTML = '';
        countries.forEach(function(data) {
            countriesHTML += '<option value="' + data['currency'] + '">' + data['currency'] + "</option>"
        });
        $lastElement = $('table[data-model="products"] tbody tr:last');
        var lastNumber = $lastElement ? $lastElement.children().first().text() : 0;
        $template = $('<tr><td><p>' + (parseInt(lastNumber) + 1) + '</p></td>' + "\n" +
            '<td><input class="form-control" name="invoice[products][' + lastNumber + '][name]"/></td>' + "\n" +
            '<td><input class="form-control" name="invoice[products][' + lastNumber + '][quantity]"/></td>' + "\n" +
            '<td><input class="form-control" name="invoice[products][' + lastNumber + '][priceNett][amount]"/></td>' + "\n" +
            '<td><input class="form-control" name="invoice[products][' + lastNumber + '][vatRate]"/></td>' + "\n" +
            '<td><input class="form-control" class="form-control" disabled/></td>' + "\n" +
            '<td><select class="form-control" name="invoice[products][' + lastNumber + '][priceNett][currency]">\n' +
            countriesHTML +
            '</select></td>' + "\n" +
            '<td><button type="button" data-action="removeProduct" class="btn btn-danger btn-sm">-</button>' + "<br />\n" +
            '<button type="button" data-action="addProduct" class="btn btn-success btn-sm">+</button></td></tr>'+ "\n");
        $lastElement.after($template);
    });

    $body.on('click', 'button[data-action="removeProduct"]', function() {
        $parent = $(this).parent().parent();
        $parent.nextAll().each(function(i, obj) {
            $tr = $(obj);
            $numberElement = $tr.children().first();
            var newNumber = parseInt($numberElement.text()) - 1;
            $numberElement.text(newNumber);
            $tr.children().each(function(i, obj) {
                $element = $(obj).children().first();
                var name = $element.attr('name');
                if (undefined !== name) {
                    var pattern = new RegExp('[0-9]');
                    $element.attr('name', name.replace(pattern, newNumber - 1));
                }
            });
        });
        $parent.remove();
    });


});
