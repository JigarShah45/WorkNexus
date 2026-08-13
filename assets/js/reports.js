$(document).ready(function () {
    // ==========================================
    // Employee Report Table
    // ==========================================
    if ($("#employeeReportTable").length) {
        initReportDataTable("#employeeReportTable", {
            order: [[0, "desc"]],
            language: {
                searchPlaceholder: "Search employees...",
                info: "Showing _START_ to _END_ of _TOTAL_ employees"
            }
        });
    }

    // ==========================================
    // Active Employee Report Table
    // ==========================================
    if ($('#activeEmployeeTable').length) {
        initReportDataTable('#activeEmployeeTable', {
            language: {
                searchPlaceholder: "Search active employees...",
                info: "Showing _START_ to _END_ of _TOTAL_ active employees"
            }
        });
    }
    
    // ==========================================
    // Inactive Employee Report Table
    // ==========================================
    if ($('#inactiveEmployeeTable').length) {
        initReportDataTable('#inactiveEmployeeTable', {
            language: {
                searchPlaceholder: "Search inactive employees...",
                info: "Showing _START_ to _END_ of _TOTAL_ inactive employees"
            }
        });
    }

    // ==========================================
    // Salary Report Table
    // ==========================================
    if ($('#salaryReportTable').length) {
        initReportDataTable('#salaryReportTable', {
            language: {
                searchPlaceholder: "Search salary records...",
                info: "Showing _START_ to _END_ of _TOTAL_ salary records"
            }
        });
    }

    // ==========================================
    // Department Report Table
    // ==========================================
    if ($("#departmentReportTable").length) {
        initReportDataTable("#departmentReportTable", {
            order: [[0, "desc"]],
            language: {
                searchPlaceholder: "Search departments...",
                info: "Showing _START_ to _END_ of _TOTAL_ departments"
            }
        });
    }
});
