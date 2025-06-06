<?php

namespace App\DataTables;

use App\Models\AttendanceType;
use App\Models\TimeEntry;
use App\Models\TimeSheet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class TimeSheetDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {

        $data = (new EloquentDataTable($query))
        ->editColumn('id', function ($row) {
            return $row->id ?? '';
        })
        ->addColumn('Day', function ($row) {
            if ($row->date) {
                $date = Carbon::parse($row->date);
            } else {
                $date = Carbon::parse($row->temp_date);
            }

            return '<div class="flex flex-col">
                        <span class="text-lg font-bold">' . $date->format('D') . '</span>
                        <span class="text-sm">' . $date->format('M d') . '</span>
                    </div>';
            
        })
        ->editColumn('schedule', function ($row) {
            $amTimeIn = $row->shiftSchedule->shift->am_time_in ?? '';
            $amTimeOut = $row->shiftSchedule->shift->am_time_out ?? '';
            $pmTimeIn = $row->shiftSchedule->shift->pm_time_in ?? '';
            $pmTimeOut = $row->shiftSchedule->shift->pm_time_out ?? '';
            return $amTimeIn. ' - ' . $amTimeOut. ' ' . $pmTimeIn. ' - ' . $pmTimeOut;
        })
        ->editColumn('am_time_in', function ($row) {
            return $row->am_time_in ? Carbon::parse($row->am_time_in)->format('h:i A') : '';
        })
        ->editColumn('pm_time_out', function ($row) {
            return $row->pm_time_out ? Carbon::parse($row->pm_time_out)->format('h:i A') : '';
        })
        ->editColumn('am_time_out', function ($row) {
            return $row->am_time_out ? Carbon::parse($row->am_time_out)->format('h:i A') : '';
        })
        ->editColumn('pm_time_in', function ($row) {
            return $row->pm_time_in ? Carbon::parse($row->pm_time_in)->format('h:i A') : '';
        })
        ->editColumn('rendered_hours', function ($row) {
            return $row->rendered_hours ?  Carbon::parse($row->rendered_hours)->format('H:i') : '00:00';
        })
        ->editColumn('excess_minutes', function ($row) {
            return $row->excess_minutes ?  Carbon::parse($row->excess_minutes)->format('H:i') : '00:00';
        })
        ->editColumn('late_hours', function ($row) {
            return $row->late_hours ?  Carbon::parse($row->late_hours)->format('H:i') : '00:00';
        })
        ->editColumn('undertime_minutes', function ($row) {
            return $row->undertime_minutes ?  Carbon::parse($row->undertime_minutes)->format('H:i') : '00:00';
        })
        ->editColumn('remarks', function ($row) {
            $day = $row->date ? Carbon::parse($row->date)->format('D') : Carbon::parse($row->temp_date)->format('D');
            if ($day == 'Sat' || $day == 'Sun') {
            return '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-hidden focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 pointer-events-none bg-red-100 text-red-500">Rest Day</span>';
            }
            
            return $row->remarks ? '<span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-hidden focus:ring-2 focus:ring-ring focus:ring-offset-2 border-transparent hover:bg-primary/80 pointer-events-none bg-blue-100 text-black-500">' . $row->remarks . '</span>' : '';
        })
        ->addColumn('Edit', function ($row) {
            $date = $row->date ?? $row->temp_date;
            $am_time_in = $row->am_time_in ? Carbon::parse($row->am_time_in)->format('H:i') : '';
            $am_time_out = $row->am_time_in ? Carbon::parse($row->am_time_out)->format('H:i') : '';
            $pm_time_in = $row->pm_time_in ? Carbon::parse($row->pm_time_in)->format('H:i') : '';
            $pm_time_out = $row->pm_time_out ? Carbon::parse($row->pm_time_out)->format('H:i') : '';
            return '<button type="button" class="cursor-pointer text-orange-500 underline italic edit-time-entry" data-id="' . $row->id . '" data-am-time-in="' .  $am_time_in . '" data-am-time-out="' . $am_time_out . '" data-pm-time-in="' . $pm_time_in . '" data-pm-time-out="' . $pm_time_out  . '" data-date="' . $date . '">Edit</button>';
        })

        ->addColumn('attendance_type', function ($row) {

            $attendanceType = AttendanceType::find($row->attendance_type);
            if ($attendanceType) {
                return $attendanceType->type;
            }
        })

        ->addColumn('attachment', function ($row) {
            $attachments = json_decode($row->files, true);
            if (!is_array($attachments)) {
                return '';
            }

            $processedFiles = [];
            $uniqueFileNames = [];
            $tempDate = $row->temp_date; // Match with temp_date

            foreach ($attachments as $dateGroup) {
                if (isset($dateGroup['date']) && $dateGroup['date'] === $tempDate && isset($dateGroup['files']) && is_array($dateGroup['files'])) {
                    foreach ($dateGroup['files'] as $file) {
                        $originalName = $file['file_name'];
                        if (!in_array($originalName, $uniqueFileNames)) {
                            $filename = substr($file['file'], 0, -10);
                            $downloadUrl = route('attachment.download', ['filename' => $filename]);
                            $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
                            
                            $processedFiles[] = [
                                'date' => $dateGroup['date'],
                                'file_url' => $downloadUrl,
                                'file_name' => $originalName,
                                'file_type' => $fileExtension,
                                'is_image' => in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg']),
                            ];
                            $uniqueFileNames[] = $originalName; // Track unique file names
                        }
                    }
                }
            }
        
            return json_encode($processedFiles);
        })

        ->rawColumns(['Day', 'Edit', 'remarks', 'attachment']);

        return $data;

    }

    /**
     * Get the query source of dataTable.
     */
    public function query(TimeEntry $model): QueryBuilder
    {
        $startDate = request('start_date');
        $endDate = request('end_date');
        $user_id = Auth::user()->id;

        if (!$startDate || !$endDate) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }

        $dates = [];
        $currentDate = Carbon::parse($startDate);

        while ($currentDate->format('Y-m-d') <= $endDate) {
            $dates[] = [
                'day' => $currentDate->day,
                'date' => $currentDate->format('Y-m-d'),
            ];
            $currentDate->addDay();
        }

        $tempDatesQuery = implode(' UNION ALL SELECT ', array_map(function($date) {
            return "'{$date['day']}' as day, '{$date['date']}' as date";
        }, $dates));

        $query = $model->newQuery()
            ->select(
                'temp_dates.date as temp_date',
                'temp_dates.day as temp_day',
                DB::raw('MAX(time_entries.am_time_in) as am_time_in'),
                DB::raw('MAX(time_entries.am_time_out) as am_time_out'),
                DB::raw('MAX(time_entries.pm_time_in) as pm_time_in'),
                DB::raw('MAX(time_entries.pm_time_out) as pm_time_out'),
                DB::raw('MAX(time_entries.rendered_hours) as rendered_hours'),
                DB::raw('MAX(time_entries.excess_minutes) as excess_minutes'),
                DB::raw('MAX(time_entries.late_hours) as late_hours'),
                DB::raw('MAX(time_entries.undertime_minutes) as undertime_minutes'),
                DB::raw('MAX(time_entries.remarks) as remarks'),
                DB::raw('MAX(approved_attendance.files) as files'),
                DB::raw('MAX(approved_attendance.attendance_type) as attendance_type')
            )
            ->from(DB::raw("(SELECT $tempDatesQuery) as temp_dates"))
            ->leftJoin('time_entries', function ($join) use ($user_id) {
                $join->on(DB::raw('DATE(time_entries.date)'), '=', 'temp_dates.date')
                     ->where('time_entries.user_id', $user_id);
            })
            ->leftJoin('approved_attendance', function ($join) use ($user_id) {
                $join->on(DB::raw('DATE_FORMAT(temp_dates.date, "%Y-%m-%d")'), '>=', 'approved_attendance.start_date')
                     ->on(DB::raw('DATE_FORMAT(temp_dates.date, "%Y-%m-%d")'), '<=', 'approved_attendance.end_date')
                     ->where('approved_attendance.user_id', $user_id);
            })
            ->whereBetween('temp_dates.date', [$startDate, $endDate])
            ->groupBy('temp_dates.date', 'temp_dates.day') // Group by date and day
            ->orderBy('temp_dates.date', 'asc');

        return $query;
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('timesheetTable')
                    ->columns($this->getColumns())
                    ->ajax(
                        ['data' => 'function(d) {
                            
                            d.start_date = $("#startDate").val();
                            d.end_date = $("#endDate").val();
                        }'
                    ])
                    
                    ->dom('rtip')
                    ->ordering(false) // Disable sorting
                    ->pageLength(31); // Show 10 rows by default
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->hidden(),
            Column::make('Day'),
            // Column::make('schedule'),
            Column::make('am_time_in'),
            Column::make('am_time_out'),
            Column::make('pm_time_in'),
            Column::make('pm_time_out'),
            Column::make('rendered_hours')->title('Rendered'),
            Column::make('excess_minutes')->title('Excess'),
            Column::make('late_hours')->title('Late'),
            Column::make('undertime_minutes')->title('Undertime'),
            Column::make('remarks'),
            Column::make('attachment'),
            Column::make('attendance_type'),
            Column::make('Edit'),
            // Column::make('Action'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'TimeSheet_' . date('YmdHis');
    }
}
