<?php

namespace App\DataTables;

use App\Models\ApproveAttendance;
use App\Models\Attachment;
use App\Models\AttendanceType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class AttachmentsDataTable extends DataTable
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
            ->addColumn('effectivity', function ($row) {
                return Carbon::parse($row->start_date)->format('F j, Y'). '-' .Carbon::parse($row->end_date)->format('F j, Y'); // Changed format to Month Name Day, Year

            })

            ->editColumn('attendance_type', function ($row) {
                $attendanceType = AttendanceType::where('id', $row->attendance_type)->first();
                return $attendanceType->type ?? '';
                
            })

            ->editColumn('remarks', function ($row) {
                return $row->remarks ?? '';
                
            })

            // ->addColumn('attachment', function ($row) {
            //     $attachments = json_decode($row->files, true);
            //     if (!is_array($attachments)) {
            //         return '';
            //     }
                
            //     return implode(', ', array_map(function($file) {
            //         if (isset($file['file'])) {
            //             $filename = substr($file['file'], 0, -10);
            //             $originalName = $file['file_name'];
            //             $downloadUrl = route('attachment.download', ['filename' => $filename]);
            //             return '<a href="' . $downloadUrl . '" class="underline italic font-semibold">' . ($file['file_name'] ?? 'Download') . '</a>';
            //         }
            //         return '';
            //     }, $attachments));
            // })

            ->addColumn('attachment', function ($row) {
                $attachments = json_decode($row->files, true);
                if (!is_array($attachments)) {
                    return '';
                }

                $processedFiles = [];
                $uniqueFileNames = [];

                foreach ($attachments as $dateGroup) {
                    if (isset($dateGroup['files']) && is_array($dateGroup['files'])) {
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
                                $uniqueFileNames[] = $originalName;
                            }
                        }
                    }
                }
            
                return json_encode($processedFiles);
            })
    

            ->editColumn('file_name', function ($row) {
                return $row->file_name ?? '';
                
            })
            ->editColumn('created_at', function ($row) {
                return $row->created_at ?? '';
                
            })
            ->addColumn('edit', function ($row) {
                return '<button type="button" class="px-2 py-2 border border-orange-500 bg-white text-orange-500 rounded-lg hover:bg-orange-100 transition-colors text-sm" data-id="' . $row->id . '">Edit</button>';

                
            })
            ->rawColumns(['attachment', 'edit'])
            ;
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(ApproveAttendance $model): QueryBuilder
    {
        $query = $model->newQuery()->with('attendance_type');

        $startDate = request('start_date');
        $endDate = request('end_date');
        $user_id = Auth::user()->id;

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
                    ->setTableId('attachmentsTable')
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
            Column::make('effectivity'),
            Column::make('attendance_type'),
            Column::make('attachment'),
            Column::make('remarks'),
            Column::make('created_at'),
            Column::make('edit'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Attachments_' . date('YmdHis');
    }
}
