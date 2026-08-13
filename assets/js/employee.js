$(document).ready(function () {

    /* ==========================================================
       EMPLOYEE DATATABLE
    ========================================================== */

    if ($("#employeeTable").length) {
        initDataTable("#employeeTable", {
            order: [[0, "asc"]],
            pageLength: 5,
			lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            language: {
                searchPlaceholder: "Search employees...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ employees",
                zeroRecords: "No employees found.",
                emptyTable: "No employee records available.",
                infoEmpty: "Showing 0 to 0 of 0 employees",
                infoFiltered: "(filtered from _MAX_ total employees)"
            }
        });
    }

    /* ==========================================================
       DELETE EMPLOYEE
    ========================================================== */

    $(document).on("click", ".deleteEmployee", function () {

        let deleteUrl = $(this).data("url");

        Swal.fire({
            title: "Delete Employee?",
            text: "This employee will be moved to deleted records.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"
        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({

                url: deleteUrl,
                type: "POST",
                dataType: "json",

                success: function (response) {

                    if (response.status) {

                        Swal.fire({

                            icon: "success",
                            title: "Deleted!",
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });

                    } else {

                        Swal.fire({

                            icon: "error",
                            title: "Error",
                            text: response.message

                        });

                    }

                },

                error: function () {

                    Swal.fire({

                        icon: "error",
                        title: "Server Error",
                        text: "Something went wrong."

                    });

                }

            });

        });

    });

    /* ==========================================================
       DELETE DEPARTMENT
    ========================================================== */

    $(document).on("click", ".deleteDepartment", function () {

        let deleteUrl = $(this).data("url");

        Swal.fire({

            title: "Delete Department?",
            text: "This department will be deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"

        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({

                url: deleteUrl,
                type: "POST",
                dataType: "json",

                success: function (response) {

                    if (response.status) {

                        Swal.fire({

                            icon: "success",
                            title: "Deleted!",
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });

                    } else {

                        Swal.fire({

                            icon: "error",
                            title: "Cannot Delete",
                            text: response.message

                        });

                    }

                },

                error: function () {

                    Swal.fire({

                        icon: "error",
                        title: "Server Error",
                        text: "Something went wrong."

                    });

                }

            });

        });

    });

    /* ==========================================================
       DELETE USER
    ========================================================== */

    $(document).on("click", ".deleteUser", function () {

        let deleteUrl = $(this).data("url");

        Swal.fire({

            title: "Delete User?",
            text: "This user will be permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Delete",
            cancelButtonText: "Cancel"

        }).then((result) => {

            if (!result.isConfirmed) return;

            $.ajax({

                url: deleteUrl,
                type: "POST",
                dataType: "json",

                success: function (response) {

                    if (response.status) {

                        Swal.fire({

                            icon: "success",
                            title: "Deleted!",
                            text: response.message,
                            timer: 1200,
                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });

                    } else {

                        Swal.fire({

                            icon: "error",
                            title: "Error",
                            text: response.message

                        });

                    }

                },

                error: function () {

                    Swal.fire({

                        icon: "error",
                        title: "Server Error",
                        text: "Something went wrong."

                    });

                }

            });

        });

    });

});