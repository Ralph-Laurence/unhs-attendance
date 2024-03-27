let auditUpdateDetailsModal;
let auditDeleteDetailsModal;
let auditCreateDetailsModal;

let recordFilters = {};
let recordFiltersState = {};
let filtersContainer;

var auditsPage = (function ()
{
    let eventBus;
    let tablePageLen;

    const EV_VIEW_DETAIL_AUDIT_CREATED = 'onViewAuditCreatedEvent';
    const EV_VIEW_DETAIL_AUDIT_UPDATED = 'onViewAuditUpdatedEvent';
    const EV_VIEW_DETAIL_AUDIT_DELETED = 'onViewAuditDeletedEvent';

    let dataTable;
    const DATA_TABLE_SELECTOR = '#records-table';
    
    // State Flags
    let dataTable_isFirstDraw = true;

    let addFilterOptions = function() {

        let _use = 0;

        return {
            'getValue' : ()     => _use,
            'setValue' : (u)    => _use = u,
            'reset'    : ()     => _use = 0,
        }
    };

    //============================
    // Initialization
    //============================

    var initialize = function ()
    {
        filtersContainer = new mdb.Dropdown('.filter-options-dialog');

        eventBus = addModuleEventBus();
        auditUpdateDetailsModal = to_auditTrailsDetailsUpdate('#audit-details-update');
        auditDeleteDetailsModal = to_auditTrailsDetailsDelete('#audit-details-delete');
        auditCreateDetailsModal = to_auditTrailsDetailsCreate('#audit-details-create');

        recordFilters = {
            'timeFrom'      : to_timepicker('#input-time-from'),
            'timeTo'        : to_timepicker('#input-time-to'),
            'date'          : to_datepicker('#input-date-filter'),
            'user'          : to_droplist('#input-user-filter'),
            'action'        : to_droplist('#input-action-filter'),
            'affected'      : to_droplist('#input-affected-filter'),
            'searchTerm'    : to_textfield('#search-filter'),
            'fullTime'      : to_checkbox('#input-time-inclusive'),
            'useFilter'     : addFilterOptions()
        };

        bindTableDataSource();
    };

    //============================
    // Event Handling
    //============================

    var handleEvents = function ()
    {
        $(document).on('click', `${DATA_TABLE_SELECTOR} .btn-view`, function () 
        {
            let row = $(this).closest('tr');

            if (!row)
            {
                alertModal.showWarn(GenericMessages.ROW_ACTION_FAIL);
                return;
            }

            viewAuditDetails(row);
        });

        bindTracingCollapsible('#audit-details-create');
        bindTracingCollapsible('#audit-details-delete');
        bindTracingCollapsible('#audit-details-update');

        eventBus.subscribe(EV_VIEW_DETAIL_AUDIT_CREATED, (dataset) => {

            auditCreateDetailsModal.presentData(dataset);
            auditCreateDetailsModal.show();
        });

        eventBus.subscribe(EV_VIEW_DETAIL_AUDIT_UPDATED, (dataset) => {
            
            auditUpdateDetailsModal.presentData(dataset);
            auditUpdateDetailsModal.show();
        });

        eventBus.subscribe(EV_VIEW_DETAIL_AUDIT_DELETED, (dataset) => {

            auditDeleteDetailsModal.presentData(dataset);
            auditDeleteDetailsModal.show();
        });

        $('.filter-options-dialog .btn-clear').on('click',   () => applyFilters(false));
        $('.filter-options-dialog .btn-apply').on('click',   () => applyFilters(true));
        $('.filter-options-dialog .btn-close').on('click',   () => cancelFilter());
        $("#filters-dropdown-button").on('hide.bs.dropdown', () => cancelFilter());

        recordFilters.fullTime.changed = () => {

            let state = recordFilters.fullTime.getValue();
 
            if (state == 'on')
            {
                recordFilters.timeFrom.disable();
                recordFilters.timeTo.disable();
                return;
            }

            recordFilters.timeFrom.enable();
            recordFilters.timeTo.enable();
        };

    };

    function bindTracingCollapsible(parentModal)
    {
        let selector = `${parentModal} .tracing-details`;
        let tracingCollapsible = $(selector);
        let text = $(`${parentModal} .btn-collapsible .text`);
        let icon = $(`${parentModal} .btn-collapsible .icon`);

        tracingCollapsible.on('hide.mdb.collapse', () => {
            text.text('See More');

            if (!icon.hasClass('fa-chevron-down'))
            {
                icon.addClass('fa-chevron-down')
                    .removeClass('fa-chevron-up');
            }
        });

        tracingCollapsible.on('shown.mdb.collapse', () => {
            text.text('See Less');

            if (!icon.hasClass('fa-chevron-up'))
            {
                icon.addClass('fa-chevron-up')
                    .removeClass('fa-chevron-down');
            }
        });
        
        console.warn(`el -> ${selector}; cnt -> ${tracingCollapsible.length}`);

        let tracingCollapse  = new mdb.Collapse(tracingCollapsible, {
            toggle: false
        });

        $(parentModal).on('hidden.mdb.modal', () => tracingCollapse.hide());
    }

    let viewAuditDetails = function(row)
    {
        let key = $(row).find('.row-actions').data('record-key');

        $.ajax({
            url     : $(DATA_TABLE_SELECTOR).data('src-view-audit'),
            type    : 'POST',
            data    : {
                '_token': getCsrfToken(),
                'rowKey': key,
            },
            success: function(response) 
            {
                if (!response)
                {
                    alertModal.showDanger(GenericMessages.XHR_SERVER_NO_REPLY);
                    return;
                }

                response = JSON.parse(response);

                if (response.code != 0)
                {
                    alertModal.showDanger(response.message);
                    return;
                }

                switch(response.dataset.action)
                {
                    case 'created':
                        eventBus.publish(EV_VIEW_DETAIL_AUDIT_CREATED, response.dataset);
                        break;

                    case 'updated':
                        eventBus.publish(EV_VIEW_DETAIL_AUDIT_UPDATED, response.dataset);
                        break;

                    case 'deleted':
                        eventBus.publish(EV_VIEW_DETAIL_AUDIT_DELETED, response.dataset);
                        break;
                }
            },
            error: function (xhr, status, error) {
                console.warn(xhr.responseText);
            }
        });

    };

    function applyFilters(applyFilter)
    {
        // Do not apply filter if param is invalid
        if (applyFilter && (typeof applyFilter !== 'boolean'))
            return;

        // Clear the filters ...
        if (applyFilter === false)
        {
            // Reset the filters to default value, then hide the indicators
            Object.values(recordFilters).forEach(f => f.reset());
            //$('.filter-indicators').addClass('d-hidden');
        }
        else
        {
            recordFilters.useFilter.setValue(1);
        }
        // else
        // {
        //     // Show filter indicators
        //     $('.filter-indicators').removeClass('d-hidden');
        // }

        // Execute the record filters
        bindTableDataSource();

        // Update the filter indicator texts
        // $('.lbl-month-filter').text(recordFilters.month.getText());
        // $('.lbl-role-filter').text(recordFilters.role.getText());
        // $('.lbl-leave-filter').text(recordFilters.leave.getText());
        // $('.lbl-status-filter').text(recordFilters.status.getText());

        filtersContainer.hide();
    }

    function cancelFilter() 
    {
        // Clear the state of the filters in the dropdown of filters
        // only when we actually did a filter
        if (recordFilters.useFilter.getValue() == 0)
        {
            Object.values(recordFilters).forEach(f => f.reset());
        }
        // Restore the filter states
        else
        {
            for (let key in recordFiltersState)
            {
                if ( !(key in recordFilters) )
                    continue;
                
                let state = recordFiltersState[key];

                if (!state)
                {
                    recordFilters[key].reset();
                    continue;
                }

                recordFilters[key].setValue(state);
            }

        }

        filtersContainer.hide();
    }

    let columnDefinitions = [
        // First Column -> Record Counter
        {
            width: '80px',
            className: 'record-counter text-truncate position-sticky start-0 sticky-cell',
            name: 'record-number',
            data: null,
            render: function (data, type, row, meta)
            {
                return meta.row + 1;
            }
        },
        {
            className: 'td-date text-truncate',
            width: '120px',
            data: 'date',
        },
        {
            className: 'td-time text-truncate',
            width: '120px',
            data: 'time',
        },
        {
            className: 'td-employee-name text-truncate',
            width: '180px',
            data: 'adminname',
            name: 'adminname',
            defaultContent: ''
        },
        {
            className: 'td-action text-truncate',
            width: '120px',
            data: 'action',
            render: function(data, type, row) {
                
                let html =
                `<div class="action-badge ${row.action_icon} px-2 py-1 w-100">
                    <i class="fas me-1 fasicon"></i>
                    <span class="label text-capitalize text-sm">${data}</span>
                </div>`;

                return html;
            }
        },
        {
            className: 'td-affected text-truncate td-120',
            width: '150px',
            data: 'affected',
        },
        {
            className: 'td-desc text-truncate text-14',
            width: '250px',
            data: 'description',
        },
        {
            className: 'td-view-detail text-center px-1 position-sticky end-0 z-100 sticky-cell',
            width: '80px',
            data: null,
            render: function(data, type, row) 
            {
                let html = 
                `<div class="row-actions unstyled-buttons" data-record-key="${data.id}">
                    <div class="loader d-none"></div>
                    <button class="btn btn-sm btn-view px-2 btn-link text-primary-dark text-capitalize rounded-3"
                        type="button">View
                    </button>
                </div>`;

                return html;
            }
        }
    ];

    function bindTableDataSource()
    {
        let options = {
            "deferRender": true,
            'searching': false,
            'ordering': false,
            'autoWidth': true,
            'scrollX': true,
            'sScrollXInner': "80%",
            'columns': columnDefinitions,
            'drawCallback': function (settings) 
            {
                // dataTable_isFirstDraw is when the "Loading..." was first shown.
                // We need to show the alert message only when it is not on first draw
                // and when rows are empty
                if (dataTable_isFirstDraw)
                {
                    dataTable_isFirstDraw = false;
                    return;
                }

                var api = this.api();

                if (api.rows().count() === 0)
                {
                    return;
                }

                updateRowEntryNumbers(api);

                // Highlight the newly added / updated row

                // $('.simplebar-content-wrapper').scrollTop($('body').height());
                if ('newRowInstance' in dataTable && dataTable.newRowInstance !== null)
                {
                    if (!dataTable.newRowInstance.node())
                        return;

                    let rowNode = dataTable.newRowInstance.node();

                    scrollRowToView(rowNode, {
                        'afterScroll': function () 
                        {
                            setTimeout(function () 
                            {
                                flashRow(rowNode, () => dataTable.newRowInstance = null);
                            }, 800);
                        }
                    });
                }
            },
            ajax: {
                url: $(DATA_TABLE_SELECTOR).data('src-datasource'),
                type: 'POST',
                dataSrc: function (json) 
                {
                    if (!json)
                        return null;

                    if ('data' in json && json.data.length == 0)
                    {
                        snackbar.showInfo('No records to show');
                    }

                    // Display Messages By Error Codes
                    if ('code' in json) 
                    {
                        if (json.code == -1) 
                        {
                            alertModal.showDanger(json.message);
                            return [];
                        }
                    }
                    // After AJAX response, reenable the control buttons
                    //enableControlButtons();

                    return json.data;
                },
                data: function () 
                {
                    let dataToSend = {
                        '_token': getCsrfToken(),
                    };

                    for (let key in recordFilters)
                    {
                        let filterItem  = recordFilters[key].getValue();
                        dataToSend[key] = filterItem;

                        recordFiltersState[key] = filterItem;
                    }

                    return dataToSend;
                },
                error: function(xhr, error, thrown) {
                    // Handle your error here
                    alertModal.showDanger('Unable to load the records because an unknown error has occurred. Please contact the support team.');
                }
            }
        };
        // If an instance of datatable has already been created,
        // reload its data source with given url instead
        if (dataTable != null)
        {
            dataTable.ajax.reload();
            redrawPageLenControls(dataTable);
            return;
        }

        // Initialize datatable if not yet created
        dataTable = $(DATA_TABLE_SELECTOR).DataTable(options);
        redrawPageLenControls(dataTable);
    }

    function redrawPageLenControls(datatable)
    {
        if (tablePageLen)
            tablePageLen = null;

        tablePageLen = to_lengthpager('#table-page-len', datatable);
    }
    //     // If an instance of datatable has already been created,
    //     // reload its data source with given url instead
    //     if (dataTable != null)
    //     {
    //         dataTable.ajax.reload();
    //         return;
    //     }

    //     // Initialize datatable if not yet created
    //     dataTable = $(DATA_TABLE_SELECTOR).DataTable(options);
    // }
    //============================
    // Business Logic
    //============================

    return {
        init    : initialize,
        handle  : handleEvents,
    };

})();

$(document).ready(function ()
{
    auditsPage.init();
    auditsPage.handle();
});
