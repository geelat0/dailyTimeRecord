<?php

namespace App\DataTables;

use App\Models\Setting;
use App\Models\ShiftSchedule;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class SettingDataTable extends DataTable
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
            ->addColumn('filed_on', function ($row) {
                return $row->created_at->format('F j, Y') ?? ''; // Changed format to Month Name Day, Year
                
            })
            ->addColumn('effectivity', function ($row) {
                return Carbon::parse($row->start_date)->format('F j, Y'). '-' .Carbon::parse($row->end_date)->format('F j, Y'); // Changed format to Month Name Day, Year

            })
            ->addColumn('schedule', function ($row) {
                $amTimeIn = $row->shift->am_time_in ?  Carbon::parse($row->shift->am_time_in)->format('h:i A') : ''; // Format AM time in
                $amTimeOut = $row->shift->am_time_out ? Carbon::parse($row->shift->am_time_out)->format('h:i A'): ''; // Format AM time out
                $pmTimeIn = $row->shift->pm_time_in ? Carbon::parse($row->shift->pm_time_in)->format('h:i A') : ''; // Format PM time in
                $pmTimeOut = $row->shift->pm_time_out ? Carbon::parse($row->shift->pm_time_out)->format('h:i A') : ''; // Format PM time out
                return "{$amTimeIn} - {$amTimeOut} {$pmTimeIn} - {$pmTimeOut}"; // Return formatted time range
                
            })
            ->addColumn('shift_name', function ($row) {
                return $row->shift->shift_name ?? '';
            })

            ->addColumn('shift_id', function ($row) {
                return $row->shift->id ?? '';
            })
            
            ->editColumn('start_date', function ($row) {
                return $row->start_date ? Carbon::parse($row->start_date)->format('m/d/Y') : '';
            })
            ->editColumn('end_date', function ($row) {
                return $row->end_date ? Carbon::parse($row->end_date)->format('m/d/Y') : '';
            })

            ->editColumn('am_time_in', function ($row) {
                return $row->shift->am_time_in ? Carbon::parse($row->shift->am_time_in)->format('h:i A') : '';
            })
            ->editColumn('am_time_out', function ($row) {
                return $row->shift->am_time_out ? Carbon::parse($row->shift->am_time_out)->format('h:i A') : '';
            })
            ->editColumn('pm_time_in', function ($row) {
                return $row->shift->pm_time_in ? Carbon::parse($row->shift->pm_time_in)->format('h:i A') : '';
            })
            ->editColumn('pm_time_out', function ($row) {
                return $row->shift->pm_time_out ? Carbon::parse($row->shift->pm_time_out)->format('h:i A') : '';
            })

            ->editColumn('remarks', function ($row) {
                return $row->remarks ?? '';
                
            })
            ->editColumn('status', function ($row) {
                return $row->status ?? '';
                
            })
            ->addColumn('action', function ($row) {
                return '<div class="flex space-x-2 gap-2">
                            <button type="button" 
                            class="px-2 py-2 border border-orange-500 bg-white text-orange-500 rounded-lg hover:bg-orange-100 transition-colors text-xs"
                            >Edit</button>
                        </div>';
                
            })
            ->rawColumns(['action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ShiftSchedule $model): QueryBuilder
    {

        $query = $model->newQuery();
        $startDate = request('start_date');
        $endDate = request('end_date');
        $user_id = 1;

        if (!$startDate || !$endDate) {
            $startDate = now()->startOfYear()->format('Y-m-d');
            $endDate = now()->endOfYear()->format('Y-m-d');
        }

        $query->where('user_id', $user_id)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->whereBetween('end_date', [$startDate, $endDate])
            ->orderBy('created_at', 'desc'); // Order by the latest

        return $query;
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
            Column::make('id')->hidden(),
            Column::make('filed_on'),
            Column::make('effectivity'),
            Column::make('schedule'),
            Column::make('remarks'),
            Column::make('status'),
            Column::make('action'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Setting_' . date('YmdHis');
    }
}
