$(document).ready(function(){

    var cattegory = $("#cattegory").val();

    function loadLimitData()
    {
        var date = $("#date").val();
        var amount = $("#amount").val();
        var cattegory = $("#cattegory").val();
        var expenseId = null;
        if ($("button[type=submit]").attr("name")) expenseId = $("button[type=submit]").attr("name");
        var data = [{
            date: date,
            amount: amount,
            cattegory: cattegory,
            expenseId: expenseId
        }];

        $("#cattegory-limit").load('expense/get-limit', data[0]);
    }

    loadLimitData();

    $("#amount").change(function(){
        if (cattegory) loadLimitData();
    });
    $("#date").change(function(){
        if (cattegory) loadLimitData();
    });


    $("#cattegory").change(function(){
        cattegory = $("#cattegory").val();
        loadLimitData();
    });
});