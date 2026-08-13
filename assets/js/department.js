$(function () {

    if ($("#departmentTable").length) {

        initDataTable("#departmentTable", {

            pageLength: 5,

            lengthMenu: [
                [5,10,25,50,-1],
                [5,10,25,50,"All"]
            ],

            order: [[0,"asc"]],

            language: {
                searchPlaceholder: "Search departments...",
                info: "Showing _START_ to _END_ of _TOTAL_ departments"
            }

        });

    }

});