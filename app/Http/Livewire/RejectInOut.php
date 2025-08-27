<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\SignalBit\UserPassword;
use App\Models\SignalBit\MasterPlan;
use App\Models\SignalBit\DefectType;
use App\Models\SignalBit\DefectArea;
use App\Models\SignalBit\Defect;
use App\Models\SignalBit\DefectPacking;
use App\Models\SignalBit\Reject;
use App\Models\SignalBit\RejectPacking;
use App\Models\SignalBit\OutputFinishing;
use App\Models\SignalBit\RejectInOut as RejectInOutModel;
use App\Models\SignalBit\RejectInDetail;
use App\Models\SignalBit\RejectInDetailPosition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Livewire\WithPagination;
use DB;

class RejectInOut extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $date;

    public $lines;
    public $orders;

    // Reject IN
    public $rejectInShowPage;
    public $rejectInDate;
    public $rejectInLine;
    public $rejectInQty;
    public $rejectInOutputType;
    public $rejectInTimeModal;
    public $rejectInWorksheetModal;
    public $rejectInStyleModal;
    public $rejectInColorModal;
    public $rejectInQuality;
    public $rejectInDateModal;
    public $rejectInLineModal;
    public $rejectInMasterPlanModal;
    public $rejectInSizeModal;
    public $rejectInTypeModal;
    public $rejectInAreaModal;
    public $rejectInQtyModal;

    // Reject OUT
    public $rejectOutShowPage;
    public $rejectOutDate;
    public $rejectOutLine;
    public $rejectOutQty;
    public $rejectOutOutputType;

    public $rejectOutDateModal;
    public $rejectOutOutputModal;
    public $rejectOutLineModal;
    public $rejectOutMasterPlanModal;
    public $rejectOutSizeModal;
    public $rejectOutTypeModal;
    public $rejectOutAreaModal;
    public $rejectOutQtyModal;

    // Reject Master Plan
    public $rejectInMasterPlanOutput;
    public $rejectOutMasterPlanOutput;

    public $rejectInSelectedMasterPlan;
    public $rejectInSelectedSize;
    public $rejectInSelectedType;
    public $rejectInSelectedArea;

    public $rejectOutSelectedMasterPlan;
    public $rejectOutSelectedSize;
    public $rejectOutSelectedType;
    public $rejectOutSelectedArea;

    // Reject IN OUT
    public $rejectInOutShowPage;
    public $rejectInOutFrom;
    public $rejectInOutTo;
    public $rejectInOutSearch;
    public $rejectInOutOutputType;

    // Types and Areas
    public $defectTypes;
    public $defectAreas;

    // Reject Detail
    public $rejectDetails;

    // Reject QR
    public $scannedRejectIn;

    public $scannedRejectOut;

    public $mode;

    public $productTypeImage;
    public $rejectPositionX;
    public $rejectPositionY;

    public $loadingMasterPlan;

    public $baseUrl;

    public $listeners = [
        'setDate' => 'setDate',
        'hideRejectAreaImageClear' => 'hideRejectAreaImage',
        'setRejectAreaPosition' => 'setRejectAreaPosition',
    ];

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->mode = 'in';
        $this->lines = null;
        $this->orders = null;

        // Reject In init value
        $this->rejectInList = null;
        $this->rejectInShowPage = 10;
        $this->rejectInOutputType = 'all';
        $this->rejectInDate = date('Y-m-d');
        $this->rejectInLine = null;
        $this->rejectInMasterPlan = null;
        $this->rejectInSelectedMasterPlan = null;
        $this->rejectInSelectedSize = null;
        $this->rejectInSelectedType = null;
        $this->rejectInSelectedArea = null;
        $this->rejectInMasterPlanOutput = null;
        $this->rejectInSelectedList = [];
        $this->rejectInSearch = null;
        $this->rejectInListAllChecked = null;

        // Reject Out init value
        $this->rejectOutList = null;
        $this->rejectOutShowPage = 10;
        $this->rejectOutOutputType = 'all';
        $this->rejectOutDate = date('Y-m-d');
        $this->rejectOutLine = null;
        $this->rejectOutMasterPlan = null;
        $this->rejectOutSelectedMasterPlan = null;
        $this->rejectOutSelectedSize = null;
        $this->rejectOutSelectedType = null;
        $this->rejectOutSelectedArea = null;
        $this->rejectOutMasterPlanOutput = null;
        $this->rejectOutSelectedList = [];
        $this->rejectOutSearch = null;
        $this->rejectOutListAllChecked = false;

        // Reject QR
        $this->scannedRejectIn = null;
        $this->scannedRejectOut = null;

        // Reject In Out
        $this->rejectInOutShowPage = 10;
        $this->rejectInOutFrom = date("Y-m-d", strtotime("-7 days"));
        $this->rejectInOutTo = date("Y-m-d");

        $this->productTypeImage = null;
        $this->rejectPositionX = null;
        $this->rejectPositionY = null;

        $this->loadingMasterPlan = false;
        $this->baseUrl = url('/');

        // Reject Detail
        $this->defectTypes = [];
        $this->defectAreas = [];
        $this->rejectDetails = [
            [
                "reject_status" => null,
                "reject_type" => null,
                "reject_area" => null,
                "reject_area_x" => 0,
                "reject_area_y" => 0,
            ],
        ];

        $this->emit("qrInputFocus", "in");
    }

    public function changeMode($mode)
    {
        $this->mode = $mode;

        $this->emit('qrInputFocus', $mode);
    }

    // public function updatingRejectInSearch()
    // {
    //     $this->resetPage("rejectInPage");
    // }

    // public function updatingRejectOutSearch()
    // {
    //     $this->resetPage("rejectOutPage");
    // }

    // public function updatedPaginators($page, $pageName) {
    //     if ($this->rejectInListAllChecked == true) {
    //         $this->selectAllRejectIn();
    //     }

    //     if ($this->rejectOutListAllChecked == true) {
    //         $this->selectAllRejectOut();
    //     }
    // }

    public function addRejectDetail()
    {
        array_push(
            $this->rejectDetails,
            [
                "reject_status" => null,
                "reject_type" => null,
                "reject_area" => null,
                "reject_area_x" => 0,
                "reject_area_y" => 0,
            ]
        );

        $this->emit('reinitSelect2');
    }

    public function removeRejectDetail()
    {
        array_pop($this->rejectDetails);

        $this->emit('reinitSelect2');
    }

    public function resetRejectDetails()
    {
        $this->rejectDetails = [
            [
                "reject_status" => null,
                "reject_type" => null,
                "reject_area" => null,
                "reject_area_x" => 0,
                "reject_area_y" => 0,
            ]
        ];

        $this->emit('reinitSelect2');
    }

    public function preSubmitRejectIn()
    {
        if ($this->scannedRejectIn) {
            $scannedReject = null;

            if ($this->rejectInOutputType == "all") {
                $scannedRejectQc = DB::table("output_rejects")->selectRaw("
                    output_rejects.id,
                    output_rejects.updated_at,
                    output_rejects.kode_numbering,
                    output_rejects.so_det_id,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_rejects.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'qc'"));
                })->
                whereNotNull("output_rejects.id")->
                where("output_rejects.kode_numbering", $this->scannedRejectIn)->
                first();

                if ($scannedRejectQc) {
                    $scannedReject = $scannedRejectQc;
                } else {
                    $scannedRejectQcf = OutputFinishing::selectRaw("
                        output_check_finishing.id,
                        output_check_finishing.updated_at,
                        output_check_finishing.kode_numbering,
                        output_check_finishing.so_det_id,
                        output_defect_types.defect_type,
                        output_defect_areas.defect_area,
                        master_plan.id master_plan_id,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size,
                        userpassword.username,
                        output_reject_in_out.id defect_in_id,
                        'qcf' output_type
                    ")->
                    leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                    leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                    leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                    leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                    leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                    leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                    leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_check_finishing.defect_area_id")->
                    leftJoin("output_reject_in_out", function ($join) {
                        $join->on("output_reject_in_out.id", "=", "output_check_finishing.id");
                        $join->on("output_reject_in_out.output_type", "=", DB::raw("'qcf'"));
                    })->
                    where("output_check_finishing.status", "reject")->
                    where("output_check_finishing.kode_numbering", $this->scannedRejectIn)->
                    first();

                    if ($scannedRejectQcf) {
                        $scannedReject = $scannedRejectQcf;
                    } else {
                        $scannedRejectPacking = DB::table("output_rejects_packing")->selectRaw("
                            output_rejects_packing.id,
                            output_rejects_packing.updated_at,
                            output_rejects_packing.kode_numbering,
                            output_rejects_packing.so_det_id,
                            output_defect_types.defect_type,
                            output_defect_areas.defect_area,
                            master_plan.id master_plan_id,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color,
                            so_det.size,
                            userpassword.username,
                            output_reject_in_out.id defect_in_id,
                            'packing' output_type
                        ")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                        leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                        leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                        leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects_packing.reject_area_id")->
                        leftJoin("output_reject_in_out", function ($join) {
                            $join->on("output_reject_in_out.id", "=", "output_rejects_packing.id");
                            $join->on("output_reject_in_out.output_type", "=", DB::raw("'packing'"));
                        })->
                        whereNotNull("output_rejects_packing.id")->
                        where("output_rejects_packing.reject_status", "defect")->
                        where("output_rejects_packing.kode_numbering", $this->scannedRejectIn)->
                        first();

                        if ($scannedRejectPacking) {
                            $scannedReject = $scannedRejectPacking;
                        }
                    }
                }
            } else if ($this->rejectInOutputType == "packing") {
                $scannedReject = DB::table("output_rejects_packing")->selectRaw("
                    output_rejects_packing.id,
                    output_rejects_packing.updated_at,
                    output_rejects_packing.kode_numbering,
                    output_rejects_packing.so_det_id,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'packing' output_type
                ")->
                leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects_packing.reject_area_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_rejects_packing.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'packing'"));
                })->
                whereNotNull("output_rejects_packing.id")->
                where("output_rejects_packing.kode_numbering", $this->scannedRejectIn)->
                first();
            } else if ($this->rejectInOutputType == "qcf") {
                $scannedReject = OutputFinishing::selectRaw("
                    output_check_finishing.id,
                    output_check_finishing.updated_at,
                    output_check_finishing.kode_numbering,
                    output_check_finishing.so_det_id,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'qcf' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_check_finishing.defect_area_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_check_finishing.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'qcf'"));
                })->
                where("output_check_finishing.status", "reject")->
                where("output_check_finishing.kode_numbering", $this->scannedRejectIn)->
                first();
            } else {
                $scannedReject = DB::table("output_rejects")->selectRaw("
                    output_rejects.id,
                    output_rejects.updated_at,
                    output_rejects.kode_numbering,
                    output_rejects.so_det_id,
                    output_defect_types.defect_type,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_rejects.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'qc'"));
                })->
                whereNotNull("output_rejects.id")->
                where("output_rejects.kode_numbering", $this->scannedRejectIn)->
                first();
            }

            if ($scannedReject) {
                $rejectInOut = RejectInOutModel::where("reject_id", $scannedReject->id)->where("output_type", $scannedReject->output_type)->first();

                if (!$rejectInOut) {
                    $this->rejectInOutputType = $scannedReject->output_type;
                    $this->rejectInTimeModal = $scannedReject->updated_at;
                    $this->rejectInLineModal = $scannedReject->username;
                    $this->rejectInWorksheetModal = $scannedReject->ws;
                    $this->rejectInStyleModal = $scannedReject->style;
                    $this->rejectInColorModal = $scannedReject->color;
                    $this->rejectInSizeModal = $scannedReject->size;
                    $this->rejectInTypeModal = $scannedReject->defect_type;
                    $this->rejectInAreaModal = $scannedReject->defect_area;
                    $this->rejectInMasterPlanOutput = $scannedReject->master_plan_id;
                    $this->rejectInQualityModal = null;

                    $this->emit('showModal', 'reject', 'regular');
                } else {
                    $this->emit('alert', 'warning', "QR sudah discan.");
                }
            } else {
                $this->emit('alert', 'error', "Defect dengan QR '".$this->scannedRejectIn."' tidak ditemukan di 'QC REJECT'.");
            }
        } else {
            $this->emit('alert', 'error', "QR tidak sesuai.");
        }
    }

    public function selectRejectAreaPosition($i)
    {
        $masterPlan = MasterPlan::select('gambar')->find($this->rejectInMasterPlanOutput);

        if ($masterPlan) {
            $x = 0;
            $y = 0;
            if ($this->rejectDetails && count($this->rejectDetails) > 0) {
                if ($this->rejectDetails[$i]) {
                    $x = $this->rejectDetails[$i]["reject_area_x"];
                    $y = $this->rejectDetails[$i]["reject_area_y"];
                }
            }
            $this->emit('showSelectRejectArea', $masterPlan->gambar, $x, $y, $i);
        } else {
            $this->emit('alert', 'error', 'Harap pilih tipe produk terlebih dahulu');
        }
    }

    public function setRejectAreaPosition($x, $y, $i)
    {
        $this->rejectAreaPositionX = $x;
        $this->rejectAreaPositionY = $y;

        if ($this->rejectDetails && count($this->rejectDetails) > 0) {
            if ($this->rejectDetails[$i]) {
                $this->rejectDetails[$i]["reject_area_x"] = $x;
                $this->rejectDetails[$i]["reject_area_y"] = $y;
            }
        }
    }

    public function setRejectType($rejectType, $i)
    {
        if ($this->rejectDetails && count($this->rejectDetails) > 0) {
            if ($this->rejectDetails[$i]) {
                $this->rejectDetails[$i]["reject_type"] = $rejectType;
            }
        }
    }

    public function setRejectArea($rejectArea, $i)
    {
        if ($this->rejectDetails && count($this->rejectDetails) > 0) {
            if ($this->rejectDetails[$i]) {
                $this->rejectDetails[$i]["reject_area"] = $rejectArea;
            }
        }
    }

    public function submitRejectIn()
    {
        if ($this->scannedRejectIn) {
            $scannedReject = null;

            if ($this->rejectInOutputType == "all") {
                $scannedRejectQc = DB::table("output_rejects")->selectRaw("
                    output_rejects.id,
                    output_rejects.reject_status,
                    output_rejects.kode_numbering,
                    output_rejects.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_rejects.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'qc'"));
                })->
                whereNotNull("output_rejects.id")->
                where("output_rejects.kode_numbering", $this->scannedRejectIn)->
                first();

                if ($scannedRejectQc) {
                    $scannedReject = $scannedRejectQc;
                } else {
                    $scannedRejectQcf = OutputFinishing::selectRaw("
                        output_check_finishing.id,
                        output_check_finishing.reject_status,
                        output_check_finishing.kode_numbering,
                        output_check_finishing.so_det_id,
                        output_defect_types.defect_type,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size,
                        userpassword.username,
                        output_reject_in_out.id defect_in_id,
                        'qcf' output_type
                    ")->
                    leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                    leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                    leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                    leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                    leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                    leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                    leftJoin("output_reject_in_out", function ($join) {
                        $join->on("output_reject_in_out.id", "=", "output_check_finishing.id");
                        $join->on("output_reject_in_out.output_type", "=", DB::raw("'qcf'"));
                    })->
                    where("output_check_finishing.status", "reject")->
                    where("output_check_finishing.kode_numbering", $this->scannedRejectIn)->
                    first();

                    if ($scannedRejectQcf) {
                        $scannedReject = $scannedRejectQcf;
                    } else {
                        $scannedRejectPacking = DB::table("output_rejects_packing")->selectRaw("
                            output_rejects_packing.id,
                            output_rejects_packing.reject_status,
                            output_rejects_packing.kode_numbering,
                            output_rejects_packing.so_det_id,
                            output_defect_types.defect_type,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color,
                            so_det.size,
                            userpassword.username,
                            output_reject_in_out.id defect_in_id,
                            'packing' output_type
                        ")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                        leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                        leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                        leftJoin("output_reject_in_out", function ($join) {
                            $join->on("output_reject_in_out.id", "=", "output_rejects_packing.id");
                            $join->on("output_reject_in_out.output_type", "=", DB::raw("'packing'"));
                        })->
                        whereNotNull("output_rejects_packing.id")->
                        where("output_rejects_packing.reject_status", "defect")->
                        where("output_rejects_packing.kode_numbering", $this->scannedRejectIn)->
                        first();

                        if ($scannedRejectPacking) {
                            $scannedReject = $scannedRejectPacking;
                        }
                    }
                }
            } else if ($this->rejectInOutputType == "packing") {
                $scannedReject = DB::table("output_rejects_packing")->selectRaw("
                    output_rejects_packing.id,
                    output_rejects_packing.reject_status,
                    output_rejects_packing.kode_numbering,
                    output_rejects_packing.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'packing' output_type
                ")->
                leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_rejects_packing.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'packing'"));
                })->
                whereNotNull("output_rejects_packing.id")->
                where("output_rejects_packing.kode_numbering", $this->scannedRejectIn)->
                first();
            } else if ($this->rejectInOutputType == "qcf") {
                $scannedReject = OutputFinishing::selectRaw("
                    output_check_finishing.id,
                    output_check_finishing.reject_status,
                    output_check_finishing.kode_numbering,
                    output_check_finishing.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'qcf' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_check_finishing.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'qcf'"));
                })->
                where("output_check_finishing.status", "reject")->
                where("output_check_finishing.kode_numbering", $this->scannedRejectIn)->
                first();
            } else {
                $scannedReject = DB::table("output_rejects")->selectRaw("
                    output_rejects.id,
                    output_rejects.reject_status,
                    output_rejects.kode_numbering,
                    output_rejects.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_reject_in_out", function ($join) {
                    $join->on("output_reject_in_out.id", "=", "output_rejects.id");
                    $join->on("output_reject_in_out.output_type", "=", DB::raw("'qc'"));
                })->
                whereNotNull("output_rejects.id")->
                where("output_rejects.kode_numbering", $this->scannedRejectIn)->
                first();
            }

            if ($scannedReject) {
                $rejectInOut = RejectInOutModel::where("reject_id", $scannedReject->id)->where("output_type", $scannedReject->output_type)->first();

                if (!$rejectInOut) {
                    if ($this->rejectInQuality) {
                        // Reject In
                        $createRejectInOut = RejectInOutModel::create([
                            "reject_id" => $scannedReject->id,
                            "kode_numbering" => $scannedReject->kode_numbering,
                            "type" => $scannedReject->reject_status,
                            "output_type" => $scannedReject->output_type,
                            "status" => $this->rejectInQuality,
                            "created_by" => Auth::user()->line_id,
                            "created_by_username" => Auth::user()->username,
                            "created_at" => Carbon::now(),
                            "updated_at" => Carbon::now(),
                            "reworked_at" => null
                        ]);

                        // Hook
                        switch ($this->rejectInQuality) {
                            case "rejected" :
                                // Reject In Detail
                                if ($this->rejectDetails && count($this->rejectDetails) > 0) {
                                    for ($i = 0; $i < count($this->rejectDetails); $i++) {
                                        $createRejectInDetail = RejectInDetail::create([
                                            "reject_in_id" => $createRejectInOut->id,
                                            "reject_type_id" => $this->rejectDetails[$i]["reject_type"],
                                            "reject_area_id" => $this->rejectDetails[$i]["reject_area"],
                                        ]);

                                        if ($createRejectInDetail) {
                                            $createRejectInDetailPosition = RejectInDetailPosition::create([
                                                "reject_in_detail_id" => $createRejectInDetail->id,
                                            ]);
                                        }
                                    }
                                }

                                break;
                            case "reworked" :
                                $currentReject = Reject::where("id", $scannedReject->id)->first();
                                if ($currentReject && $currentReject->defect_id > 0) {
                                    Defect::where("id", $currentReject->defect_id)->update(["defect_status" => "defect"]);
                                }
                                $currentReject->delete();
                                break;
                            default :
                                $this->emit('alert', 'warning', "Terjadi kesalahan.");
                                break;
                        }

                        // After
                        if ($createRejectInOut) {
                            $this->scannedRejectIn = null;
                            $this->rejectInTimeModal = null;
                            $this->rejectInOutputType = null;
                            $this->rejectInLineModal = null;
                            $this->rejectInWorksheetModal = null;
                            $this->rejectInStyleModal = null;
                            $this->rejectInColorModal = null;
                            $this->rejectInSizeModal = null;
                            $this->rejectInTypeModal = null;
                            $this->rejectInAreaModal = null;
                            $this->rejectInQuality = null;

                            $this->resetRejectDetails();

                            $this->emit('alert', 'success', "REJECT '".$scannedReject->defect_type."' dengan KODE '".$this->scannedRejectIn."' berhasil masuk ke 'QC REJECT'");
                            $this->emit('hideModal', 'reject', 'regular');
                        } else {
                            $this->emit('alert', 'error', "Terjadi kesalahan.");
                        }
                    } else {
                        $this->emit('alert', 'error', "Harap tentukan hasil Quality Check.");
                    }
                } else {
                    $this->emit('alert', 'warning', "QR sudah discan.");
                }
            } else {
                $this->emit('alert', 'error', "Reject dengan QR '".$this->scannedRejectIn."' tidak ditemukan di 'QC REJECT'.");
            }
        } else {
            $this->emit('alert', 'error', "QR tidak sesuai.");
        }

        $this->emit('qrInputFocus', $this->mode);
    }

    public function submitRejectOut()
    {
        if ($this->scannedRejectOut) {
            if ($this->rejectOutOutputType == "all" ) {
                $scannedReject = RejectInOutModel::selectRaw("
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) id,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.kode_numbering ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.kode_numbering ELSE output_rejects.kode_numbering END) END) kode_numbering,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.so_det_id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.so_det_id ELSE output_rejects.so_det_id END) END) so_det_id,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) defect_type,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) ws,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.color ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.color ELSE so_det.color END) END) color,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) size,
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN userpassword_packing.username ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN userpassword_finish.username ELSE userpassword.username END) END) username,
                    output_reject_in_out.output_type
                ")->
                // Defect
                leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("so", "so.id", "=", "so_det.id_so")->
                leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                // Reject Packing
                leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in_out.reject_id")->
                leftJoin("output_defect_types as output_defect_types_packing", "output_defect_types_packing.id", "=", "output_rejects_packing.reject_type_id")->
                leftJoin("output_defect_areas as output_defect_areas_packing", "output_defect_areas_packing.id", "=", "output_rejects_packing.reject_area_id")->
                leftJoin("so_det as so_det_packing", "so_det_packing.id", "=", "output_rejects_packing.so_det_id")->
                leftJoin("so as so_packing", "so_packing.id", "=", "so_det_packing.id_so")->
                leftJoin("act_costing as act_costing_packing", "act_costing_packing.id", "=", "so_packing.id_cost")->
                leftJoin("master_plan as master_plan_packing", "master_plan_packing.id", "=", "output_rejects_packing.master_plan_id")->
                leftJoin("userpassword as userpassword_packing", "userpassword.username", "=", "output_rejects_packing.created_by")->
                // Reject Finishing
                leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in_out.reject_id")->
                leftJoin("output_defect_types as output_defect_types_finish", "output_defect_types_finish.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("output_defect_areas as output_defect_areas_finish", "output_defect_areas_finish.id", "=", "output_check_finishing.defect_area_id")->
                leftJoin("so_det as so_det_finish", "so_det_finish.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("so as so_finish", "so_finish.id", "=", "so_det_finish.id_so")->
                leftJoin("act_costing as act_costing_finish", "act_costing_finish.id", "=", "so_finish.id_cost")->
                leftJoin("master_plan as master_plan_finish", "master_plan_finish.id", "=", "output_check_finishing.master_plan_id")->
                leftJoin("userpassword as userpassword_finish", "userpassword.username", "=", "output_check_finishing.created_by")->
                // Conditional
                where("output_reject_in_out.status", "defect")->
                whereRaw("(CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.kode_numbering ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.kode_numbering ELSE output_rejects.kode_numbering END) END) = '".$this->scannedRejectOut."'")->
                first();
            } else {
                $scannedReject = RejectInOutModel::selectRaw("
                    output_rejects.id,
                    output_rejects.kode_numbering,
                    output_rejects.so_det_id,
                    output_defect_types.defect_type,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in_out.output_type
                ")->
                leftJoin(($this->rejectOutOutputType == 'packing' ? 'output_rejects_packing' : ($this->rejectOutOutputType == 'qcf' ? 'output_check_finishing' : 'output_rejects'))." as output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                where("output_reject_in_out.status", "defect")->
                where("output_reject_in_out.output_type", $this->rejectOutOutputType)->
                where("output_rejects.kode_numbering", $this->scannedRejectOut)->
                first();
            }

            if ($scannedReject) {
                $rejectInOut = RejectInOutModel::where("reject_id", $scannedReject->id)->where("output_type", $scannedReject->output_type)->first();

                if ($rejectInOut) {
                    if ($rejectInOut->status == "defect") {
                        $updateDefectInOut = RejectInOutModel::where("reject_id", $scannedReject->id)->update([
                            "status" => "reworked",
                            "created_by" => Auth::user()->username,
                            "updated_at" => Carbon::now(),
                            "reworked_at" => Carbon::now()
                        ]);

                        if ($updateDefectInOut) {
                            $this->emit('alert', 'success', "DEFECT '".$scannedReject->defect_type."' dengan KODE '".$this->scannedRejectOut."' berhasil dikeluarkan dari 'QC REJECT'");
                        } else {
                            $this->emit('alert', 'error', "Terjadi kesalahan.");
                        }
                    } else {
                        $this->emit('alert', 'warning', "QR sudah discan di OUT.");
                    }
                } else {
                    $this->emit('alert', 'error', "DEFECT '".$scannedReject->defect_type."' dengan QR '".$this->scannedRejectOut."' tidak/belum masuk 'QC REJECT'.");
                }
            } else {
                $this->emit('alert', 'error', "DEFECT dengan QR '".$this->scannedRejectOut."' tidak ditemukan di 'QC REJECT'.");
            }
        } else {
            $this->emit('alert', 'error', "QR tidak sesuai.");
        }

        $this->scannedRejectOut = null;
        $this->emit('qrInputFocus', $this->mode);
    }

    public function showDefectAreaImage($productTypeImage, $x, $y)
    {
        $this->productTypeImage = $productTypeImage;
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;

        $this->emit('showDefectAreaImage', $this->productTypeImage, $this->defectPositionX, $this->defectPositionY);
    }

    public function hideDefectAreaImage()
    {
        $this->productTypeImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    public function render()
    {
        $this->loadingMasterPlan = false;

        $this->lines = UserPassword::where("Groupp", "SEWING")->orderBy("line_id", "asc")->get();

        if ($this->rejectInOutputType == 'all') {
            // Reject Packing
            $rejectInPackingQuery = DB::table("output_rejects_packing")->selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_rejects_packing.kode_numbering,
                output_rejects_packing.reject_type_id,
                output_defect_types.defect_type,
                output_rejects_packing.so_det_id,
                output_rejects_packing.updated_at as reject_time,
                so_det.size,
                'packing' output_type,
                COUNT(output_rejects_packing.id) reject_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
            leftJoin("output_reject_in_out", function($join) {
                $join->on("output_reject_in_out.reject_id", "=", "output_rejects_packing.id");
                $join->on("output_reject_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_rejects_packing.reject_status", "defect")->
            whereNull("output_reject_in_out.id")->
            whereRaw("YEAR(output_rejects_packing.updated_at) = '".date("Y")."'");
            if ($this->rejectInSearch) {
                $rejectInPackingQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectInSearch."%' OR
                    so_det.size LIKE '%".$this->rejectInSearch."%' OR
                    output_rejects_packing.kode_numbering LIKE '%".$this->rejectInSearch."%'
                )");
            }
            // if ($this->rejectInDate) {
            //     $rejectInPackingQuery->where("master_plan.tgl_plan", $this->rejectInDate);
            // }
            if ($this->rejectInLine) {
                $rejectInPackingQuery->where("master_plan.sewing_line", $this->rejectInLine);
            }
            if ($this->rejectInSelectedMasterPlan) {
                $rejectInPackingQuery->where("master_plan.id", $this->rejectInSelectedMasterPlan);
            }
            if ($this->rejectInSelectedSize) {
                $rejectInPackingQuery->where("output_rejects_packing.so_det_id", $this->rejectInSelectedSize);
            }
            if ($this->rejectInSelectedType) {
                $rejectInPackingQuery->where("output_rejects_packing.reject_type_id", $this->rejectInSelectedType);
            }
            $rejectInPacking = $rejectInPackingQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_rejects_packing.so_det_id", "output_rejects_packing.kode_numbering");

            // Reject In QCF
            $rejectInQcfQuery = OutputFinishing::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_check_finishing.kode_numbering,
                output_check_finishing.defect_type_id,
                output_defect_types.defect_type,
                output_check_finishing.so_det_id,
                output_check_finishing.updated_at as reject_time,
                so_det.size,
                'qcf' output_type,
                COUNT(output_check_finishing.id) reject_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
            leftJoin("output_reject_in_out", function($join) {
                $join->on("output_reject_in_out.reject_id", "=", "output_check_finishing.id");
                $join->on("output_reject_in_out.output_type", "=", DB::raw("'qcf'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_check_finishing.status", "reject")->
            whereNull("output_reject_in_out.id")->
            whereRaw("YEAR(output_check_finishing.updated_at) = '".date("Y")."'");
            if ($this->rejectInSearch) {
                $rejectInQcfQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectInSearch."%' OR
                    so_det.size LIKE '%".$this->rejectInSearch."%' OR
                    output_check_finishing.kode_numbering LIKE '%".$this->rejectInSearch."%'
                )");
            }
            // if ($this->rejectInDate) {
            //     $rejectInQcfQuery->where("master_plan.tgl_plan", $this->rejectInDate);
            // }
            if ($this->rejectInLine) {
                $rejectInQcfQuery->where("master_plan.sewing_line", $this->rejectInLine);
            }
            if ($this->rejectInSelectedMasterPlan) {
                $rejectInQcfQuery->where("master_plan.id", $this->rejectInSelectedMasterPlan);
            }
            if ($this->rejectInSelectedSize) {
                $rejectInQcfQuery->where("output_check_finishing.so_det_id", $this->rejectInSelectedSize);
            }
            if ($this->rejectInSelectedType) {
                $rejectInQcfQuery->where("output_check_finishing.defect_type_id", $this->rejectInSelectedType);
            }
            $rejectInQcf = $rejectInQcfQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_check_finishing.so_det_id", "output_check_finishing.kode_numbering");

            $rejectInQcQuery = DB::table("output_rejects")->selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_rejects.kode_numbering,
                output_rejects.reject_type_id,
                output_defect_types.defect_type,
                output_rejects.so_det_id,
                output_rejects.updated_at as reject_time,
                so_det.size,
                'qc' output_type,
                COUNT(output_rejects.id) reject_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            leftJoin("output_reject_in_out", function($join) {
                $join->on("output_reject_in_out.reject_id", "=", "output_rejects.id");
                $join->on("output_reject_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            whereNull("output_reject_in_out.id")->
            whereRaw("YEAR(output_rejects.updated_at) = '".date("Y")."'");
            if ($this->rejectInSearch) {
                $rejectInQcQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectInSearch."%' OR
                    so_det.size LIKE '%".$this->rejectInSearch."%' OR
                    output_rejects.kode_numbering LIKE '%".$this->rejectInSearch."%'
                )");
            }
            // if ($this->rejectInDate) {
            //     $rejectInQcQuery->where("master_plan.tgl_plan", $this->rejectInDate);
            // }
            if ($this->rejectInLine) {
                $rejectInQcQuery->where("master_plan.sewing_line", $this->rejectInLine);
            }
            if ($this->rejectInSelectedMasterPlan) {
                $rejectInQcQuery->where("master_plan.id", $this->rejectInSelectedMasterPlan);
            }
            if ($this->rejectInSelectedSize) {
                $rejectInQcQuery->where("output_rejects.so_det_id", $this->rejectInSelectedSize);
            }
            if ($this->rejectInSelectedType) {
                $rejectInQcQuery->where("output_rejects.reject_type_id", $this->rejectInSelectedType);
            }
            $rejectInQc = $rejectInQcQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_rejects.so_det_id", "output_rejects.kode_numbering");

            $rejectIn = $rejectInQc->unionAll($rejectInQcf)->unionAll($rejectInPacking);

        } else if ($this->rejectInOutputType == 'packing') {
            $rejectInQuery = DB::table("output_rejects_packing")->selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_rejects_packing.kode_numbering,
                output_rejects_packing.reject_type_id,
                output_defect_types.defect_type,
                output_rejects_packing.so_det_id,
                output_rejects_packing.updated_at as reject_time,
                so_det.size,
                'packing' output_type,
                COUNT(output_rejects_packing.id) reject_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
            leftJoin("output_reject_in_out", function($join) {
                $join->on("output_reject_in_out.reject_id", "=", "output_rejects_packing.id");
                $join->on("output_reject_in_out.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_rejects_packing.reject_status", "defect")->
            whereNull("output_reject_in_out.id")->
            whereRaw("YEAR(output_rejects_packing.updated_at) = '".date("Y")."'");
            if ($this->rejectInSearch) {
                $rejectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectInSearch."%' OR
                    so_det.size LIKE '%".$this->rejectInSearch."%' OR
                    output_rejects_packing.kode_numbering LIKE '%".$this->rejectInSearch."%'
                )");
            }
            // if ($this->rejectInDate) {
            //     $rejectInQuery->where("master_plan.tgl_plan", $this->rejectInDate);
            // }
            if ($this->rejectInLine) {
                $rejectInQuery->where("master_plan.sewing_line", $this->rejectInLine);
            }
            if ($this->rejectInSelectedMasterPlan) {
                $rejectInQuery->where("master_plan.id", $this->rejectInSelectedMasterPlan);
            }
            if ($this->rejectInSelectedSize) {
                $rejectInQuery->where("output_rejects_packing.so_det_id", $this->rejectInSelectedSize);
            }
            if ($this->rejectInSelectedType) {
                $rejectInQuery->where("output_rejects_packing.reject_type_id", $this->rejectInSelectedType);
            }
            $rejectIn = $rejectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_rejects_packing.so_det_id", "output_rejects_packing.kode_numbering");
        } else if ($this->rejectInOutputType == 'qcf') {
            $rejectInQuery = OutputFinishing::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_check_finishing.kode_numbering,
                output_check_finishing.defect_type_id,
                output_defect_types.defect_type,
                output_check_finishing.so_det_id,
                output_check_finishing.updated_at as reject_time,
                so_det.size,
                'qcf' output_type,
                COUNT(output_check_finishing.id) reject_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
            leftJoin("output_reject_in_out", function($join) {
                $join->on("output_reject_in_out.reject_id", "=", "output_check_finishing.id");
                $join->on("output_reject_in_out.output_type", "=", DB::raw("'qcf'"));
            })->
            whereNotNull("master_plan.id")->
            where("output_check_finishing.status", "reject")->
            whereNull("output_reject_in_out.id")->
            whereRaw("YEAR(output_check_finishing.updated_at) = '".date("Y")."'");
            if ($this->rejectInSearch) {
                $rejectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectInSearch."%' OR
                    so_det.size LIKE '%".$this->rejectInSearch."%' OR
                    output_check_finishing.kode_numbering LIKE '%".$this->rejectInSearch."%'
                )");
            }
            if ($this->rejectInDate) {
                $rejectInQuery->where("master_plan.tgl_plan", $this->rejectInDate);
            }
            if ($this->rejectInLine) {
                $rejectInQuery->where("master_plan.sewing_line", $this->rejectInLine);
            }
            if ($this->rejectInSelectedMasterPlan) {
                $rejectInQuery->where("master_plan.id", $this->rejectInSelectedMasterPlan);
            }
            if ($this->rejectInSelectedSize) {
                $rejectInQuery->where("output_check_finishing.so_det_id", $this->rejectInSelectedSize);
            }
            if ($this->rejectInSelectedType) {
                $rejectInQuery->where("output_check_finishing.defect_type_id", $this->rejectInSelectedType);
            }
            $rejectIn = $rejectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_check_finishing.so_det_id", "output_check_finishing.kode_numbering");
        } else {
            $rejectInQuery = DB::table("output_rejects")->selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_rejects.kode_numbering,
                output_rejects.reject_type_id,
                output_defect_types.defect_type,
                output_rejects.so_det_id,
                output_rejects.updated_at as reject_time,
                so_det.size,
                'qc' output_type,
                COUNT(output_rejects.id) reject_qty
            ")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            leftJoin("output_reject_in_out", function($join) {
                $join->on("output_reject_in_out.reject_id", "=", "output_rejects.id");
                $join->on("output_reject_in_out.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            whereNull("output_reject_in_out.id")->
            whereRaw("YEAR(output_rejects.updated_at) = '".date("Y")."'");
            if ($this->rejectInSearch) {
                $rejectInQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectInSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectInSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectInSearch."%' OR
                    so_det.size LIKE '%".$this->rejectInSearch."%' OR
                    output_rejects.kode_numbering LIKE '%".$this->rejectInSearch."%'
                )");
            }
            // if ($this->rejectInDate) {
            //     $rejectInQuery->where("master_plan.tgl_plan", $this->rejectInDate);
            // }
            if ($this->rejectInLine) {
                $rejectInQuery->where("master_plan.sewing_line", $this->rejectInLine);
            }
            if ($this->rejectInSelectedMasterPlan) {
                $rejectInQuery->where("master_plan.id", $this->rejectInSelectedMasterPlan);
            }
            if ($this->rejectInSelectedSize) {
                $rejectInQuery->where("output_rejects.so_det_id", $this->rejectInSelectedSize);
            }
            if ($this->rejectInSelectedType) {
                $rejectInQuery->where("output_rejects.reject_type_id", $this->rejectInSelectedType);
            }
            $rejectIn = $rejectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_rejects.so_det_id", "output_rejects.kode_numbering");
        }

        $this->rejectInList = $rejectIn->
            orderBy("reject_time", "desc")->
            orderBy("sewing_line")->
            orderBy("id_ws")->
            orderBy("color")->
            orderBy("defect_type")->
            orderBy("so_det_id")->
            orderBy("output_type")->
            limit(100)->
            get();

        if ($this->rejectOutOutputType == "all" ) {
            $rejectOutQuery = RejectInOutModel::selectRaw("
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.id ELSE master_plan.id END) END) master_plan_id,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.id_ws ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.id_ws ELSE master_plan.id_ws END) END) id_ws,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) sewing_line,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) as ws,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) style,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.color ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.color ELSE master_plan.color END) END) color,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.reject_type_id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.defect_type_id ELSE output_rejects.reject_type_id END) END) defect_type_id,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) defect_type,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.so_det_id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.so_det_id ELSE output_rejects.so_det_id END) END) so_det_id,
                output_reject_in_out.kode_numbering,
                output_reject_in_out.output_type,
                output_reject_in_out.updated_at reject_time,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) size,
                (CASE WHEN output_reject_in_out.output_type = 'packing' THEN COUNT(output_rejects_packing.id) ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN COUNT(output_check_finishing.id) ELSE COUNT(output_rejects.id) END) END) reject_qty
            ")->
            // Defect
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("so", "so.id", "=", "so_det.id_so")->
            leftJoin("act_costing", "act_costing.id", "=", "so.id_cost")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
            leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
            // Reject Packing
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types as output_defect_types_packing", "output_defect_types_packing.id", "=", "output_rejects_packing.reject_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_packing", "output_defect_areas_packing.id", "=", "output_rejects_packing.reject_area_id")->
            leftJoin("so_det as so_det_packing", "so_det_packing.id", "=", "output_rejects_packing.so_det_id")->
            leftJoin("so as so_packing", "so_packing.id", "=", "so_det_packing.id_so")->
            leftJoin("act_costing as act_costing_packing", "act_costing_packing.id", "=", "so_packing.id_cost")->
            leftJoin("master_plan as master_plan_packing", "master_plan_packing.id", "=", "output_rejects_packing.master_plan_id")->
            leftJoin("userpassword as userpassword_packing", "userpassword.username", "=", "output_rejects_packing.created_by")->
            // Reject Finishing
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_defect_types as output_defect_types_finish", "output_defect_types_finish.id", "=", "output_check_finishing.defect_type_id")->
            leftJoin("output_defect_areas as output_defect_areas_finish", "output_defect_areas_finish.id", "=", "output_check_finishing.defect_area_id")->
            leftJoin("so_det as so_det_finish", "so_det_finish.id", "=", "output_check_finishing.so_det_id")->
            leftJoin("so as so_finish", "so_finish.id", "=", "so_det_finish.id_so")->
            leftJoin("act_costing as act_costing_finish", "act_costing_finish.id", "=", "so_finish.id_cost")->
            leftJoin("master_plan as master_plan_finish", "master_plan_finish.id", "=", "output_check_finishing.master_plan_id")->
            leftJoin("userpassword as userpassword_finish", "userpassword.username", "=", "output_check_finishing.created_by")->
            // Conditional
            whereRaw("(CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) IS NOT NULL ")->
            where("output_reject_in_out.status", "defect")->
            where("output_reject_in_out.type", Auth::user()->Groupp)->
            whereRaw("YEAR(output_reject_in_out.created_at) = '".date("Y")."'");
            if ($this->rejectOutSearch) {
                $rejectOutQuery->whereRaw("(
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.tgl_plan ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.tgl_plan ELSE master_plan.tgl_plan END) END) LIKE '%".$this->rejectOutSearch."%' OR
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) LIKE '%".$this->rejectOutSearch."%' OR
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.kpno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.kpno ELSE act_costing.kpno END) END) LIKE '%".$this->rejectOutSearch."%' OR
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN act_costing_packing.styleno ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN act_costing_finish.styleno ELSE act_costing.styleno END) END) LIKE '%".$this->rejectOutSearch."%' OR
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.color ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.color ELSE master_plan.color END) END) LIKE '%".$this->rejectOutSearch."%' OR
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_defect_types_packing.defect_type ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_defect_types_finish.defect_type ELSE output_defect_types.defect_type END) END) LIKE '%".$this->rejectOutSearch."%' OR
                    (CASE WHEN output_reject_in_out.output_type = 'packing' THEN so_det_packing.size ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN so_det_finish.size ELSE so_det.size END) END) LIKE '%".$this->rejectOutSearch."%' OR
                    output_reject_in_out.kode_numbering LIKE '%".$this->rejectOutSearch."%'
                )");
            }
            // if ($this->rejectOutDate) {
            //     $rejectOutQuery->whereBetween("output_reject_in_out.updated_at", [$this->rejectOutDate." 00:00:00", $this->rejectOutDate." 23:59:59"]);
            // }
            if ($this->rejectOutLine) {
                $rejectOutQuery->whereRaw("(CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.sewing_line ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.sewing_line ELSE master_plan.sewing_line END) END) = '".$this->rejectOutLine."'");
            }
            if ($this->rejectOutSelectedMasterPlan) {
                $rejectOutQuery->whereRaw("(CASE WHEN output_reject_in_out.output_type = 'packing' THEN master_plan_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN master_plan_finish.id ELSE master_plan.id END) END) = '".$this->rejectOutSelectedMasterPlan."'");
            }
            if ($this->rejectOutSelectedSize) {
                $rejectOutQuery->whereRaw("(CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.so_det_id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.so_det_id ELSE output_rejects.so_det_id END) END) = '".$this->rejectOutSelectedSize."'");
            }
            if ($this->rejectOutSelectedType) {
                $rejectOutQuery->whereRaw("(CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.reject_type_id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.defect_type_id ELSE output_rejects.reject_type_id END) END) = '".$this->rejectOutSelectedType."'");
            };
        } else {
            $rejectOutQuery = RejectInOutModel::selectRaw("
                master_plan.id master_plan_id,
                master_plan.id_ws,
                master_plan.sewing_line,
                act_costing.kpno as ws,
                act_costing.styleno as style,
                master_plan.color as color,
                output_rejects.reject_type_id,
                output_defect_types.defect_type,
                output_rejects.so_det_id,
                output_reject_in_out.kode_numbering,
                output_reject_in_out.output_type,
                output_reject_in_out.updated_at as reject_time,
                so_det.size,
                COUNT(output_reject_in_out.id) reject_qty
            ")->
            leftJoin(($this->rejectOutOutputType == 'packing' ? 'output_rejects_packing' : ($this->rejectOutOutputType == 'qcf' ? 'output_check_finishing' : 'output_rejects'))." as output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
            leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
            leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
            leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
            whereNotNull("output_rejects.id")->
            where("output_reject_in_out.status", "defect")->
            where("output_reject_in_out.output_type", $this->rejectOutOutputType)->
            where("output_reject_in_out.type", Auth::user()->Groupp)->
            whereRaw("YEAR(output_reject_in_out.created_at) = '".date("Y")."'");
            if ($this->rejectOutSearch) {
                $rejectOutQuery->whereRaw("(
                    master_plan.tgl_plan LIKE '%".$this->rejectOutSearch."%' OR
                    master_plan.sewing_line LIKE '%".$this->rejectOutSearch."%' OR
                    act_costing.kpno LIKE '%".$this->rejectOutSearch."%' OR
                    act_costing.styleno LIKE '%".$this->rejectOutSearch."%' OR
                    master_plan.color LIKE '%".$this->rejectOutSearch."%' OR
                    output_defect_types.defect_type LIKE '%".$this->rejectOutSearch."%' OR
                    so_det.size LIKE '%".$this->rejectOutSearch."%' OR
                    output_reject_in_out.kode_numbering LIKE '%".$this->rejectOutSearch."%'
                )");
            }
            // if ($this->rejectOutDate) {
            //     $rejectOutQuery->whereBetween("output_reject_in_out.updated_at", [$this->rejectOutDate." 00:00:00", $this->rejectOutDate." 23:59:59"]);
            // }
            if ($this->rejectOutLine) {
                $rejectOutQuery->where("master_plan.sewing_line", $this->rejectOutLine);
            }
            if ($this->rejectOutSelectedMasterPlan) {
                $rejectOutQuery->where("master_plan.id", $this->rejectOutSelectedMasterPlan);
            }
            if ($this->rejectOutSelectedSize) {
                $rejectOutQuery->where("output_rejects.so_det_id", $this->rejectOutSelectedSize);
            }
            if ($this->rejectOutSelectedType) {
                $rejectOutQuery->where("output_rejects.reject_type_id", $this->rejectOutSelectedType);
            };
        }

        $this->rejectOutList = $rejectOutQuery->
            groupBy("output_reject_in_out.id")->
            orderBy("output_reject_in_out.updated_at", "desc")->
            limit(100)->
            get();

        // All Defect
        $rejectDaily = RejectInOutModel::selectRaw("
                DATE(output_reject_in_out.created_at) tanggal,
                SUM(CASE WHEN (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) IS NOT NULL THEN 1 ELSE 0 END) total_in,
                SUM(CASE WHEN (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) IS NOT NULL AND output_reject_in_out.status = 'defect' THEN 1 ELSE 0 END) total_process,
                SUM(CASE WHEN (CASE WHEN output_reject_in_out.output_type = 'packing' THEN output_rejects_packing.id ELSE (CASE WHEN output_reject_in_out.output_type = 'qcf' THEN output_check_finishing.id ELSE output_rejects.id END) END) IS NOT NULL AND output_reject_in_out.status = 'reworked' THEN 1 ELSE 0 END) total_out
            ")->
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in_out.reject_id")->
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in_out.reject_id")->
            where("output_reject_in_out.type", strtolower(Auth::user()->Groupp))->
            whereBetween("output_reject_in_out.created_at", [$this->rejectInOutFrom." 00:00:00", $this->rejectInOutTo." 23:59:59"])->
            groupByRaw("DATE(output_reject_in_out.created_at)")->
            get();

        $rejectTotal = $rejectDaily->sum("total_in");

        // Defect types
        $this->defectTypes = DefectType::whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('defect_type')->get();

        // Defect areas
        $this->defectAreas = DefectArea::whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('defect_area')->get();

        return view('livewire.reject-in-out', [
            "totalRejectIn" => $this->rejectInList->sum("reject_qty"),
            "totalRejectOut" => $this->rejectOutList->sum("reject_qty"),
            "totalRejectInOut" => $rejectTotal
        ]);
    }

    public function refreshComponent()
    {
        $this->emit('$refresh');
    }
}
