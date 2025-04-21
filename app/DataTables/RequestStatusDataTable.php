<?php

namespace App\DataTables;

use App\Models\RequestDTR;
use App\Models\RequestStatus;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class RequestStatusDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->setRowId('id')

            ->addColumn('subject', function ($row) {
                return $row->subject ?? '';
            })

            ->addColumn('status', function ($row) {
                return $row->status ?? '';
            })

            ->addColumn('approver_id', function ($row) {
                return $row->approver_id ?? '';
            })

            ->addColumn('attachment', function ($row) {
                return $row->attachment ?? '';
            })

            ->addColumn('created_at', function ($row) {
                return $row->created_at ?? '';
            })  

            ->addColumn('updated_at', function ($row) {
                return $row->updated_at ?? '';
            });

            
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(RequestDTR $model): QueryBuilder
    {
        return $model->newQuery();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('settingTable')
                    ->columns($this->getColumns())
                    ->ajax(
                        ['data' => 'function(d) {
                            
                            d.start_date = $("#startDate").val();
                            d.end_date = $("#endDate").val();
                        }'
                    ])
                    
                    ->dom('rtip')
                    ->ordering(false) // Disable sorting
                    ->pageLength(10) // Show 10 rows by default
                    ->lengthMenu([[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]]); // Allow multiple options for rows
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id'),
            Column::make('subject'),
            Column::make('status'),
            Column::make('approver_id'),
            Column::make('attachment'),
            Column::make('created_at'),
            Column::make('updated_at'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'RequestStatus_' . date('YmdHis');
    }
}
