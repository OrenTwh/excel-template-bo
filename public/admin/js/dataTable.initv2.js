document.addEventListener('DOMContentLoaded', function() {
    dt_table = $(dt_table_name).DataTable({
        language: dt_table_config.language,
        autoHeight: true,
        layout: {
            topStart: {
                buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5']
            }
        },
        ajax: {
            type: 'POST',
            url: dt_table_config.ajax.url,
            data: dt_table_config.ajax.data,
            dataSrc: dt_table_config.ajax.dataSrc,
            error: function (xhr, error, code) {
                console.log(xhr);
                console.log(error);
                console.log(code);
            },
        },
        lengthMenu: [5, 10, 25, 50, 100],
        pageLength: 10, 
        responsive: true,
        processing: true,
        serverSide: true,
        order: dt_table_config.order,
        ordering: true,
        scrollX: true,
        searchCols: dt_table_config.searchCols ? dt_table_config.searchCols : [],
        columns: dt_table_config.columns,
        columnDefs: dt_table_config.columnDefs,
        searching: false,
        dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'l>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>",
        buttons: [
            {
                extend: 'copyHtml5',
                text: '<i class="fa fa-copy"></i>',
                className: 'btn btn-light',
                titleAttr: 'Copy to clipboard',
                action: function (e, dt, button, config) {
                    let $button = $(e.currentTarget);
                    $button.addClass('processing');
                    exportAllData(dt, 'copy', config, $button);
                },
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
            {
                extend: 'excelHtml5',
                text: '<i class="fa fa-file-excel"></i>',
                className: 'btn btn-success',
                titleAttr: 'Export to Excel',
                action: function (e, dt, button, config) {
                    let $button = $(e.currentTarget);
                    $button.addClass('processing');
                    exportAllData(dt, 'excel', config, $button);
                },
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
            {
                extend: 'csvHtml5',
                text: '<i class="fa fa-file-csv"></i>',
                className: 'btn btn-info',
                titleAttr: 'Export to CSV',
                action: function (e, dt, button, config) {
                    let $button = $(e.currentTarget);
                    $button.addClass('processing');
                    exportAllData(dt, 'csv', config, $button);
                },
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="fa fa-file-pdf"></i>',
                className: 'btn btn-danger',
                titleAttr: 'Export to PDF',
                action: function (e, dt, button, config) {
                    let $button = $(e.currentTarget);
                    $button.addClass('processing');
                    exportAllData(dt, 'pdf', config, $button);
                },
                exportOptions: {
                    columns: ':not(:last-child)'
                }
            },
        ],
        footerCallback: function (row, data, start, end, display) {
            var api = this.api();
            var total = api
                .column(3, { page: 'current' })
                .data()
                .reduce(function (a, b) {
                    return parseFloat(a) + parseFloat(b);
                }, 0);

            $(api.column(3).footer()).html('Total: ' + total.toFixed(2));
        },
        createdRow: function (row) {
            $(row).addClass('nk-tb-item');
        },
        initComplete: function () {
            const exportCheckbox = `
                <div class="my-3">
                    <input type="checkbox" id="exportSelected" name="exportSelected">
                    <label for="exportSelected" class="ms-1">Export ONLY selected rows</label>
                </div>
                <div class="my-2">
                    <input type="checkbox" id="exportAllData" name="exportAllData" checked>
                    <label for="exportAllData" class="ms-1">Export ALL data (not just current page)</label>
                </div>
                <div class="my-2 text-muted small">
                    <i class="fa fa-info-circle"></i> Max 1,000 rows for full export. Filter data if needed.
                </div>
            `;
            $('.dt-buttons').append(exportCheckbox);
            $(dt_table_name + '_filter').remove();

            let rawName = dt_table_name.replace('#', '');
            let lengthSelect2 = $('.dataTables_length select');
            lengthSelect2.addClass('custom-dropdown');
        },
        drawCallback: function (response) {
            if (response.json.subTotal != undefined) {
                if (Array.isArray(response.json.subTotal)) {
                    $.each(response.json.subTotal, function (i, v) {
                        $('.dataTables_scrollFoot .subtotal').eq(i).html(v);
                        $('.dataTables_scrollFoot .grandtotal').eq(i).html(response.json.grandTotal[i]);
                    });
                }
            }
        },
    });

    // Function to export all data
    function exportAllData(dt, format, config, $button) {
        let exportOnlySelected = $("#exportSelected").is(":checked");
        let exportAll = $("#exportAllData").is(":checked");

        // Check if any rows are selected
        let selectedCount = $('.select-row:checked').length;

        // Show loading indicator
        let loadingMsg = $('<div class="export-loading" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.3); z-index: 9999;"><i class="fa fa-spinner fa-spin"></i> Preparing export...</div>');
        $('body').append(loadingMsg);

        if (exportOnlySelected || selectedCount > 0) {
            // Priority 1: Export selected rows if any are checked
            exportSelectedRows(dt, format, config, loadingMsg, $button);
        } else if (exportAll) {
            // Priority 2: Export all data with 1,000 row limit check
            checkRowCountAndExport(dt, format, config, loadingMsg, $button);
        } else {
            // Export current page only
            exportCurrentPage(dt, format, config, loadingMsg, $button);
        }
    }

    // Export selected rows only
    function exportSelectedRows(dt, format, config, loadingMsg, $button) {
        let selectedData = [];
        $('.select-row:checked').each(function() {
            let row = $(this).closest('tr');
            let rowData = dt.row(row).data();
            selectedData.push(rowData);
        });

        if (selectedData.length === 0) {
            loadingMsg.remove();
            $button.removeClass('processing');
            alert('No rows selected!');
            return;
        }

        performExport(dt, selectedData, format, config, loadingMsg, $button);
    }

    // Export current page
    function exportCurrentPage(dt, format, config, loadingMsg, $button) {
        let pageData = dt.rows({ page: 'current' }).data().toArray();
        performExport(dt, pageData, format, config, loadingMsg, $button);
    }

    // Check row count before export (1,000 row limit)
    function checkRowCountAndExport(dt, format, config, loadingMsg, $button) {
        // Get total filtered records count from server
        let ajaxData = {
            start: 0,
            length: 1,
            draw: 1
        };

        // Add current search/filter parameters
        window['columns'].forEach(function(v, i) {
            if (v.type != 'default') {
                ajaxData[v.id] = window[v.id];
            }
        });

        // Merge with original ajax data
        $.extend(ajaxData, dt_table_config.ajax.data);

        $.ajax({
            url: dt_table_config.ajax.url,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                let totalRecords = response.recordsFiltered || response.recordsTotal || 0;

                if (totalRecords > 1000) {
                    loadingMsg.remove();
                    $button.removeClass('processing');
                    alert('Maximum 1,000 rows can be selected, please filter your data before exporting.\n\nCurrent filtered records: ' + totalRecords);
                    return;
                }

                // Proceed with export
                fetchAllDataAndExport(dt, format, config, loadingMsg, $button);
            },
            error: function(xhr, error, code) {
                loadingMsg.remove();
                $button.removeClass('processing');
                console.error('Error checking data count:', error);
                alert('Error checking data count. Please try again.');
            }
        });
    }

    // Fetch all data from server
    function fetchAllDataAndExport(dt, format, config, loadingMsg, $button) {
        // Get current filters and parameters
        let ajaxData = {
            start: 0,
            length: -1, // Request all records
            draw: 1
        };

        // Add current search/filter parameters
        window['columns'].forEach(function(v, i) {
            if (v.type != 'default') {
                ajaxData[v.id] = window[v.id];
            }
        });

        // Add order parameters
        let order = dt.order();
        if (order.length > 0) {
            ajaxData['order[0][column]'] = order[0][0];
            ajaxData['order[0][dir]'] = order[0][1];
        }

        // Merge with original ajax data
        $.extend(ajaxData, dt_table_config.ajax.data);

        // Fetch all data
        $.ajax({
            url: dt_table_config.ajax.url,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                let allData = response[dt_table_config.ajax.dataSrc];
                
                if (!allData || allData.length === 0) {
                    loadingMsg.remove();
                    $button.removeClass('processing');
                    alert('No data to export!');
                    return;
                }

                performExport(dt, allData, format, config, loadingMsg, $button);
            },
            error: function(xhr, error, code) {
                loadingMsg.remove();
                $button.removeClass('processing');
                console.error('Export error:', error);
                alert('Error fetching data for export. Please try again.');
            }
        });
    }

    // Perform the actual export
    function performExport(dt, data, format, config, loadingMsg, $button) {
        console.log('Performing export with', data.length, 'rows');
        
        // Create a temporary table with all data
        let tempTable = $('<table id="temp-export-table" style="display:none;"></table>');
        $('body').append(tempTable);

        // Clone column definitions (exclude checkbox and action columns)
        let columns = dt_table_config.columns.slice(1, -1); // Skip first (checkbox) and last (actions)
        let columnDefs = dt_table_config.columnDefs.filter(function(def) {
            // Exclude columnDefs for checkbox (target 0) and actions (last column)
            return def.targets > 0 && def.targets < (dt_table_config.columns.length - 1);
        }).map(function(def) {
            // Adjust target indices since we're removing the first column
            return {
                ...def,
                targets: def.targets - 1
            };
        });

        // Create button configuration based on format
        let buttonConfig;
        if (format === 'copy') {
            buttonConfig = {
                extend: 'copyHtml5',
                exportOptions: {
                    columns: ':visible'
                }
            };
        } else if (format === 'excel') {
            buttonConfig = {
                extend: 'excelHtml5',
                filename: 'export_' + new Date().getTime(),
                exportOptions: {
                    columns: ':visible'
                }
            };
        } else if (format === 'csv') {
            buttonConfig = {
                extend: 'csvHtml5',
                filename: 'export_' + new Date().getTime(),
                exportOptions: {
                    columns: ':visible'
                }
            };
        } else if (format === 'pdf') {
            buttonConfig = {
                extend: 'pdfHtml5',
                filename: 'export_' + new Date().getTime(),
                orientation: 'landscape',
                pageSize: 'A4',
                exportOptions: {
                    columns: ':visible'
                }
            };
        }

        // Initialize temporary DataTable with buttons
        let tempDt = tempTable.DataTable({
            data: data,
            columns: columns,
            columnDefs: columnDefs,
            paging: false,
            searching: false,
            ordering: false,
            dom: 'Bfrtip',
            buttons: [buttonConfig]
        });

        // Trigger the export
        setTimeout(function() {
            tempDt.button(0).trigger();
            
            // Clean up after export completes
            setTimeout(function() {
                tempDt.destroy();
                tempTable.remove();
                loadingMsg.remove(); // Remove loading indicator
                $button.removeClass('processing'); // Remove processing class from button
            }, 500);
        }, 100);
    }

    function positionDropdown(event) {
        console.log(event)
        var dropdown = document.querySelector('.dropdown-menu');
        var trigger = event.target;
        var rect = trigger.getBoundingClientRect();
        var dropdownWidth = dropdown.offsetWidth;
        var dropdownHeight = dropdown.offsetHeight;
        
        var top = rect.top + window.pageYOffset + rect.height;
        var left = rect.left + window.pageXOffset;
      
        var viewportWidth = window.innerWidth;
        var viewportHeight = window.innerHeight;
      
        if (left + dropdownWidth > viewportWidth) {
          left = viewportWidth - dropdownWidth;
        }
      
        if (top + dropdownHeight > viewportHeight) {
          top = rect.top + window.pageYOffset - dropdownHeight;
        }
      
        dropdown.style.top = top + 'px';
        dropdown.style.left = left + 'px';
    }

    document.querySelector('.dropdown-toggle').addEventListener('click', positionDropdown);
  
    $(dt_table_name).on('shown.bs.dropdown', function (event) {
        setTimeout(() => {
            const scrollBody = $('.dt-scroll-body');
            const dropdown = $(event.target).closest('.dropdown').find('.dropdown-menu');
            const dropdownOffset = dropdown.offset();
            const scrollBodyOffset = scrollBody.offset();
    
            if (dropdownOffset && scrollBodyOffset) {
                const dropdownPosition = dropdownOffset.top - scrollBodyOffset.top + scrollBody.scrollTop();
    
                console.log('Dropdown Offset Top:', dropdownOffset.top);
                console.log('ScrollBody Offset Top:', scrollBodyOffset.top);
                console.log('ScrollBody ScrollTop:', scrollBody.scrollTop());
                console.log('Calculated Dropdown Position:', dropdownPosition);
    
                scrollBody.scrollTop(dropdownPosition);
            } else {
                console.error('Offsets not found for Dropdown or ScrollBody');
            }
        }, 10);
    });
    
   
   $(dt_table_name).on('hide.bs.dropdown', function () {
        $('.dt-scroll-body').css("overflow-y", "auto");
   })   

    $(dt_table_name).on('page.dt length.dt order.dt search.dt', function() {
        table_no = dt_table.page.info().page * dt_table.page.info().length;
    });

    $(dt_table_name).on('preXhr.dt', function(e, settings, data) {
        
        window['columns'].forEach(function(v, i) {
            if (v.type != 'default') {
                data[v.id] = window[v.id];
            }
        });
    });

    $('.listing-filter > input').on('keydown keypress', function(e) {

        let that = $(this);
        clearTimeout(timeout);
        timeout = setTimeout(function(){
            window[that.data('id')] = that.val();
            console.log(window[that.data('id')]);
            dt_table.draw();
        }, 500);
    });

    $('.listing-filter > select').on('change', function() {

        let that = $(this);
        window[that.data('id')] = that.val();
        dt_table.draw();
    });

    $('.dt-export').click(function() {
        let sort = dt_table.order(),
            url = 'order[0][column]='+sort[0][0]+'&order[0][dir]='+sort[0][1];

        window['columns'].forEach(function(v, i) {
            if (v.type != 'default') {
                if (v.type == 'checkbox') {
                    let checkboxValue = [];
                    $.each($('*[data-id="trxtype"]'), function(i, v) {
                        if ($(v).is(':checked')) {
                            checkboxValue.push($(v).val());
                        }
                    });
                    url += ('&' + v.id + '=' + checkboxValue.join(','));
                } else {
                    url += ('&' + v.id + '=' + $('#' + v.id).val());
                }
            }
        });

        const urlParams = new URL(exportPath);
        let newExportPath = urlParams.origin + urlParams.pathname;

        if (urlParams.search != '') {
            url += urlParams.search.replace('?', '&');
        }

        window.location.href = newExportPath + '?' + url;
    });

});