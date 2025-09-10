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
use App\Models\SignalBit\RejectIn;
use App\Models\SignalBit\RejectInDetail;
use App\Models\SignalBit\RejectInDetailPosition;
use App\Models\SignalBit\RejectOut;
use App\Models\SignalBit\RejectOutDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
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
    public $scannedRejectIn;
    public $rejectInOutputType;
    public $rejectInOutputTypeModal;
    public $rejectInTimeModal;
    public $rejectInWorksheetModal;
    public $rejectInStyleModal;
    public $rejectInColorModal;
    public $rejectInQuality;
    public $rejectInGrade;
    public $rejectInLineModal;
    public $rejectInSizeModal;
    public $rejectInTypeModal;
    public $rejectInAreaModal;

    // Reject In Filter
    public $rejectInFilterKode;
    public $rejectInFilterWaktu;
    public $rejectInFilterLine;
    public $rejectInFilterMasterPlan;
    public $rejectInFilterSize;
    public $rejectInFilterType;

    // Reject Detail
    public $rejectDetails;

    // Reject OUT
    public $rejectOutTanggal;
    public $rejectOutNoTransaksi;
    public $rejectOutTujuan;
    public $rejectOutLine;
    public $rejectOutStatus;

    // Reject IN OUT
    public $rejectInOutShowPage;
    public $rejectInOutFrom;
    public $rejectInOutTo;
    public $rejectInOutSearch;
    public $rejectInOutOutputType;

    // Types and Areas
    public $defectTypes;
    public $defectAreas;

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
        $this->rejectInSearch = null;
        $this->rejectInSelectedList = null;
        $this->rejectInListAllChecked = null;

        // Reject Out
        $this->rejectOutSelectedList = [];
        $this->rejectOutTanggal = date("Y-m-d");
        $this->rejectOutNoTransaksi = null;
        $this->rejectOutTujuan = "gudang";
        $this->rejectOutLine = "";
        $this->rejectOutStatus = "";

        // Reject QR
        $this->scannedRejectIn = null;

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

    // REJECT IN
    public function updatingRejectInSearch()
    {
        $this->resetPage("rejectInPage");
    }

    public function updatingRejectInFilterWaktu()
    {
        $this->resetPage("rejectInPage");
    }

    public function updatingRejectInFilterMasterPlan()
    {
        $this->resetPage("rejectInPage");
    }

    public function updatingRejectInFilterSize()
    {
        $this->resetPage("rejectInPage");
    }

    public function preSubmitRejectIn()
    {
        if ($this->scannedRejectIn) {
            $scannedReject = null;

            // Check Reject
            if ($this->rejectInOutputType == "all") {
                $scannedRejectQc = DB::table("output_rejects")->selectRaw("
                    output_rejects.id,
                    output_rejects.updated_at,
                    output_rejects.kode_numbering,
                    output_rejects.so_det_id,
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_rejects.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'qc'"));
                })->
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
                        output_defect_types.id as defect_type_id,
                        output_defect_types.defect_type,
                        output_defect_areas.id as defect_area_id,
                        output_defect_areas.defect_area,
                        master_plan.id master_plan_id,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size,
                        userpassword.username,
                        output_reject_in.id defect_in_id,
                        'qcf' output_type
                    ")->
                    leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                    leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                    leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                    leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                    leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                    leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                    leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_check_finishing.defect_area_id")->
                    leftJoin("output_reject_in", function ($join) {
                        $join->on("output_reject_in.id", "=", "output_check_finishing.id");
                        $join->on("output_reject_in.output_type", "=", DB::raw("'qcf'"));
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
                            output_defect_types.id as defect_type_id,
                            output_defect_types.defect_type,
                            output_defect_areas.id as defect_area_id,
                            output_defect_areas.defect_area,
                            master_plan.id master_plan_id,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color,
                            so_det.size,
                            userpassword.username,
                            output_reject_in.id defect_in_id,
                            'packing' output_type
                        ")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                        leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                        leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                        leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects_packing.reject_area_id")->
                        leftJoin("output_reject_in", function ($join) {
                            $join->on("output_reject_in.id", "=", "output_rejects_packing.id");
                            $join->on("output_reject_in.output_type", "=", DB::raw("'packing'"));
                        })->
                        whereNotNull("output_rejects_packing.id")->
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
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in.id defect_in_id,
                    'packing' output_type
                ")->
                leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects_packing.reject_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_rejects_packing.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'packing'"));
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
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in.id defect_in_id,
                    'qcf' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_check_finishing.defect_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_check_finishing.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'qcf'"));
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
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    master_plan.id master_plan_id,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    userpassword.username,
                    output_reject_in.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_rejects.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'qc'"));
                })->
                where("output_rejects.kode_numbering", $this->scannedRejectIn)->
                first();
            }

            if ($scannedReject) {

                // Check Reject In Out
                $rejectInOut = RejectIn::where("reject_id", $scannedReject->id)->where("output_type", $scannedReject->output_type)->first();

                if (!$rejectInOut) {

                    // Set Modal Form Value
                    $this->rejectInOutputTypeModal = $scannedReject->output_type;
                    $this->rejectInTimeModal = $scannedReject->updated_at;
                    $this->rejectInLineModal = $scannedReject->username;
                    $this->rejectInWorksheetModal = $scannedReject->ws;
                    $this->rejectInStyleModal = $scannedReject->style;
                    $this->rejectInColorModal = $scannedReject->color;
                    $this->rejectInSizeModal = $scannedReject->size;
                    $this->rejectInTypeModal = $scannedReject->defect_type;
                    $this->rejectInAreaModal = $scannedReject->defect_area;
                    $this->rejectInMasterPlanOutput = $scannedReject->master_plan_id;

                    // Open Modal
                    $this->emit('showModal', 'reject', 'regular');
                } else {
                    $this->emit('alert', 'warning', "QR sudah discan.");
                }
            } else {
                $this->emit('alert', 'error', "Reject dengan QR '".$this->scannedRejectIn."' tidak ditemukan di 'QC REJECT'.");
            }
        } else {
            $this->emit('alert', 'error', "QR tidak sesuai.");
        }
    }

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

    public function validateRejectInQuality()
    {
        if ($this->rejectInQuality == "rejected") {
            $validate = true;
            if ($this->rejectInGrade) {
                for ($i = 0; $i < count($this->rejectDetails); $i++) {
                    if ($this->rejectDetails[$i]["reject_type"] && $this->rejectDetails[$i]["reject_area"]) {
                        if ($this->rejectDetails[$i]["reject_area_x"] != 0 || $this->rejectDetails[$i]["reject_area_y"] != 0) {
                            // Fine
                        } else {
                            $validate = false;
                            $this->emit('addInvalid', ['select-reject-area-position-'.$i]);
                            $this->emit('alert', 'error', "Harap tentukan posisi reject area.");
                        }
                    } else {
                        $validate = false;
                        $this->emit('addInvalid', ['reject-area-select2-'.$i, 'reject-type-select2-'.$i]);
                        $this->emit('alert', 'error', "Harap tentukan defect type dan defect area.");
                    }
                }
            }

            return $validate;
        }

        return true;
    }

    public function submitRejectIn()
    {
        if ($this->scannedRejectIn) {
            $scannedReject = null;

            // Check Reject
            if ($this->rejectInOutputType == "all") {
                $scannedRejectQc = DB::table("output_rejects")->selectRaw("
                    output_rejects.id,
                    output_rejects.reject_status,
                    output_rejects.kode_numbering,
                    output_rejects.reject_area_x,
                    output_rejects.reject_area_y,
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    so_det.id as so_det_id,
                    master_plan.id as master_plan_id,
                    userpassword.username,
                    userpassword.line_id,
                    output_reject_in.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_rejects.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'qc'"));
                })->
                where("output_rejects.kode_numbering", $this->scannedRejectIn)->
                first();

                if ($scannedRejectQc) {
                    $scannedReject = $scannedRejectQc;
                } else {
                    $scannedRejectQcf = OutputFinishing::selectRaw("
                        output_check_finishing.id,
                        output_check_finishing.status as reject_status,
                        output_check_finishing.kode_numbering,
                        output_check_finishing.defect_area_x as reject_area_x,
                        output_check_finishing.defect_area_y as reject_area_y,
                        output_defect_types.id as defect_type_id,
                        output_defect_types.defect_type,
                        output_defect_areas.id as defect_area_id,
                        output_defect_areas.defect_area,
                        act_costing.kpno ws,
                        act_costing.styleno style,
                        so_det.color,
                        so_det.size,
                        so_det.id as so_det_id,
                        master_plan.id as master_plan_id,
                        userpassword.username,
                        userpassword.line_id,
                        output_reject_in.id defect_in_id,
                        'qcf' output_type
                    ")->
                    leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                    leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                    leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                    leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                    leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                    leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                    leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_check_finishing.defect_area_id")->
                    leftJoin("output_reject_in", function ($join) {
                        $join->on("output_reject_in.id", "=", "output_check_finishing.id");
                        $join->on("output_reject_in.output_type", "=", DB::raw("'qcf'"));
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
                            output_rejects_packing.reject_area_x,
                            output_rejects_packing.reject_area_y,
                            output_defect_types.id as defect_type_id,
                            output_defect_types.defect_type,
                            output_defect_areas.id as defect_area_id,
                            output_defect_areas.defect_area,
                            act_costing.kpno ws,
                            act_costing.styleno style,
                            so_det.color,
                            so_det.size,
                            so_det.id as so_det_id,
                            master_plan.id as master_plan_id,
                            userpassword.username,
                            userpassword.line_id,
                            output_reject_in.id defect_in_id,
                            'packing' output_type
                        ")->
                        leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                        leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                        leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                        leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                        leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                        leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects_packing.reject_area_id")->
                        leftJoin("output_reject_in", function ($join) {
                            $join->on("output_reject_in.id", "=", "output_rejects_packing.id");
                            $join->on("output_reject_in.output_type", "=", DB::raw("'packing'"));
                        })->
                        whereNotNull("output_rejects_packing.id")->
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
                    output_rejects_packing.reject_area_x,
                    output_rejects_packing.reject_area_y,
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    so_det.id as so_det_id,
                    master_plan.id as master_plan_id,
                    userpassword.username,
                    userpassword.line_id,
                    output_reject_in.id defect_in_id,
                    'packing' output_type
                ")->
                leftJoin("userpassword", "userpassword.username", "=", "output_rejects_packing.created_by")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects_packing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects_packing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects_packing.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects_packing.reject_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_rejects_packing.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'packing'"));
                })->
                whereNotNull("output_rejects_packing.id")->
                where("output_rejects_packing.kode_numbering", $this->scannedRejectIn)->
                first();
            } else if ($this->rejectInOutputType == "qcf") {
                $scannedReject = OutputFinishing::selectRaw("
                    output_check_finishing.id,
                    output_check_finishing.status as reject_status,
                    output_check_finishing.kode_numbering,
                    output_check_finishing.defect_area_x as reject_area_x,
                    output_check_finishing.defect_area_y as reject_area_y,
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    so_det.id as so_det_id,
                    master_plan.id as master_plan_id,
                    userpassword.username,
                    userpassword.line_id,
                    output_reject_in.id defect_in_id,
                    'qcf' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_check_finishing.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_check_finishing.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_check_finishing.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_check_finishing.defect_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_check_finishing.defect_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_check_finishing.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'qcf'"));
                })->
                where("output_check_finishing.status", "reject")->
                where("output_check_finishing.kode_numbering", $this->scannedRejectIn)->
                first();
            } else {
                $scannedReject = DB::table("output_rejects")->selectRaw("
                    output_rejects.id,
                    output_rejects.reject_status,
                    output_rejects.kode_numbering,
                    output_rejects.reject_area_x,
                    output_rejects.reject_area_y,
                    output_defect_types.id as defect_type_id,
                    output_defect_types.defect_type,
                    output_defect_areas.id as defect_area_id,
                    output_defect_areas.defect_area,
                    act_costing.kpno ws,
                    act_costing.styleno style,
                    so_det.color,
                    so_det.size,
                    so_det.id as so_det_id,
                    master_plan.id as master_plan_id,
                    userpassword.username,
                    userpassword.line_id,
                    output_reject_in.id defect_in_id,
                    'qc' output_type
                ")->
                leftJoin("user_sb_wip", "user_sb_wip.id", "=", "output_rejects.created_by")->
                leftJoin("userpassword", "userpassword.line_id", "=", "user_sb_wip.line_id")->
                leftJoin("so_det", "so_det.id", "=", "output_rejects.so_det_id")->
                leftJoin("master_plan", "master_plan.id", "=", "output_rejects.master_plan_id")->
                leftJoin("act_costing", "act_costing.id", "=", "master_plan.id_ws")->
                leftJoin("output_defect_types", "output_defect_types.id", "=", "output_rejects.reject_type_id")->
                leftJoin("output_defect_areas", "output_defect_areas.id", "=", "output_rejects.reject_area_id")->
                leftJoin("output_reject_in", function ($join) {
                    $join->on("output_reject_in.id", "=", "output_rejects.id");
                    $join->on("output_reject_in.output_type", "=", DB::raw("'qc'"));
                })->
                where("output_rejects.kode_numbering", $this->scannedRejectIn)->
                first();
            }

            if ($scannedReject) {

                // Check Reject In Out
                $rejectInOut = RejectIn::where("reject_id", $scannedReject->id)->where("output_type", $scannedReject->output_type)->first();

                if (!$rejectInOut) {
                    // Check Reject In Quality Input
                    if ($this->rejectInQuality) {

                        // Validate Reject In Grade
                        if ($this->rejectInGrade || $this->rejectInQuality == 'reworked') {

                            // Validate Reject In Quality
                            if ($this->validateRejectInQuality()) {

                                // Create Reject In
                                $createRejectIn = RejectIn::create([
                                    "reject_id" => $scannedReject->id,
                                    "so_det_id" => $scannedReject->so_det_id,
                                    "master_plan_id" => $scannedReject->master_plan_id,
                                    "line_id" => $scannedReject->line_id,
                                    "kode_numbering" => $scannedReject->kode_numbering,
                                    "type" => $scannedReject->reject_status,
                                    "output_type" => $scannedReject->output_type,
                                    "reject_type_id" => $scannedReject->defect_type_id,
                                    "reject_area_id" => $scannedReject->defect_area_id,
                                    "reject_area_x" => $scannedReject->reject_area_x,
                                    "reject_area_y" => $scannedReject->reject_area_y,
                                    "status" => $this->rejectInQuality,
                                    "grade" => $this->rejectInGrade,
                                    "process" => "wip",
                                    "created_by" => Auth::user()->line_id,
                                    "created_by_username" => Auth::user()->username,
                                    "created_at" => Carbon::now(),
                                    "updated_at" => Carbon::now(),
                                    "reworked_at" => null
                                ]);

                                // Hook
                                switch ($this->rejectInQuality) {
                                    case "rejected" :
                                        // Create Reject In Detail
                                        if ($this->rejectDetails && count($this->rejectDetails) > 0) {
                                            for ($i = 0; $i < count($this->rejectDetails); $i++) {
                                                $createRejectInDetail = RejectInDetail::create([
                                                    "reject_in_id" => $createRejectIn->id,
                                                    "reject_type_id" => $this->rejectDetails[$i]["reject_type"],
                                                    "reject_area_id" => $this->rejectDetails[$i]["reject_area"],
                                                    "reject_area_x" => $this->rejectDetails[$i]["reject_area_x"],
                                                    "reject_area_y" => $this->rejectDetails[$i]["reject_area_y"],
                                                ]);

                                                if ($createRejectInDetail) {
                                                    $createRejectInDetailPosition = RejectInDetailPosition::create([
                                                        "reject_in_detail_id" => $createRejectInDetail->id,
                                                        "reject_area_x" => $this->rejectDetails[$i]["reject_area_x"],
                                                        "reject_area_y" => $this->rejectDetails[$i]["reject_area_y"],
                                                    ]);
                                                }
                                            }
                                        } else {
                                            $this->emit('alert', 'error', "Harap tentukan defect type & defect area.");
                                        }

                                        break;
                                    case "reworked" :
                                        // Undo Reject
                                        if ($scannedReject->output_type == "qc" || $scannedReject->output_type == "packing") {
                                            $rejectTable = "";
                                            $defectTable = "";
                                            $undoTable = "";
                                            switch ($scannedReject->output_type) {
                                                case 'qc' :
                                                    $rejectTable = "output_rejects";
                                                    $defectTable = "output_defects";
                                                    $undoTable = "output_undo";

                                                    break;
                                                case 'packing' :
                                                    $rejectTable = "output_rejects_packing";
                                                    $defectTable = "output_defects_packing";
                                                    $undoTable = "output_undo_packing";

                                                    break;
                                            }

                                            $currentReject = DB::table($rejectTable)->where("id", $scannedReject->id)->first();
                                            if ($currentReject) {
                                                if ($currentReject->defect_id > 0) {
                                                    DB::table($defectTable)->where("id", $currentReject->defect_id)->update(["defect_status" => "defect"]);
                                                }

                                                $deleteReject = DB::table($rejectTable)->where("id", $currentReject->id)->delete();

                                                // Log Undo
                                                if ($deleteReject) {
                                                    DB::table($undoTable)->insert([
                                                        'master_plan_id' => $currentReject->master_plan_id,
                                                        'so_det_id' => $currentReject->so_det_id,
                                                        'output_defect_id' => $currentReject->defect_id,
                                                        'output_reject_id' => $currentReject->id,
                                                        'kode_numbering' => $currentReject->kode_numbering,
                                                        'keterangan' => 'reject',
                                                        'defect_type_id' => $currentReject->reject_type_id,
                                                        'defect_area_id' => $currentReject->reject_area_id,
                                                        'defect_area_x' => $currentReject->reject_area_x,
                                                        'defect_area_y' => $currentReject->reject_area_y,
                                                        'created_by' => $currentReject->created_by,
                                                        'undo_by' => Auth::user()->line_id,
                                                        'created_at' => Carbon::now(),
                                                        'updated_at' => Carbon::now()
                                                    ]);
                                                }
                                            }
                                        }

                                        break;
                                    default :
                                        $this->emit('alert', 'error', "Terjadi kesalahan.");
                                        break;
                                }

                                // After
                                if ($createRejectIn) {
                                    // Alert
                                    $this->emit('alert', 'success', "REJECT '".$scannedReject->defect_type."' dengan KODE '".$this->scannedRejectIn."' berhasil masuk ke 'QC REJECT'");
                                    $this->emit('hideModal', 'reject', 'regular');
                                    $this->emit('clearRejectModal');
                                    $this->emit('removeInvalid');

                                    // Clear Form
                                    $this->scannedRejectIn = null;
                                    $this->rejectInTimeModal = null;
                                    $this->rejectInOutputTypeModal = null;
                                    $this->rejectInLineModal = null;
                                    $this->rejectInWorksheetModal = null;
                                    $this->rejectInStyleModal = null;
                                    $this->rejectInColorModal = null;
                                    $this->rejectInSizeModal = null;
                                    $this->rejectInTypeModal = null;
                                    $this->rejectInAreaModal = null;
                                    $this->rejectInQuality = null;
                                    $this->rejectInGrade = null;

                                    $this->resetRejectDetails();
                                } else {
                                    $this->emit('alert', 'error', "Terjadi kesalahan.");
                                }
                            }
                        } else {
                            $this->emit('addInvalid', ['reject-grade']);
                            $this->emit('alert', 'error', "Harap Isi Grade Quality.");
                        }
                    } else {
                        $this->emit('addInvalid', ['reject-quality']);
                        $this->emit('alert', 'error', "Harap tentukan hasil Quality Check.");
                    }
                } else {
                    $this->emit('alert', 'warning', "QR sudah discan.");
                }
            } else {
                $this->emit('alert', 'error', "Reject dengan QR '".$this->scannedRejectIn."' tidak ditemukan.");
            }
        } else {
            $this->emit('alert', 'error', "QR tidak sesuai.");
        }

        $this->emit('qrInputFocus', $this->mode);
    }

    // REJECT OUT
    public function showRejectAreaImage($productTypeImage, $x, $y)
    {
        $this->productTypeImage = $productTypeImage;
        $this->defectPositionX = $x;
        $this->defectPositionY = $y;

        $this->emit('showRejectAreaImage', $this->productTypeImage, $this->defectPositionX, $this->defectPositionY);
    }

    public function hideRejectAreaImage()
    {
        $this->productTypeImage = null;
        $this->defectPositionX = null;
        $this->defectPositionY = null;
    }

    // Add Reject Out Selected List
    public function addRejectOutSelectedList($item)
    {
        array_push($this->rejectOutSelectedList, $item);
    }

    // Remove Reject Out Selected List
    public function removeRejectOutSelectedList($item)
    {
        $this->rejectOutSelectedList = array_filter(
            $this->rejectOutSelectedList,
            function ($data) use ($item) {
                return $data['kode_numbering'] != $item['kode_numbering'];
            }
        );
    }

    public function setRejectOutTujuan($val)
    {
        $this->rejectOutTujuan = $val;
    }

    public function setRejectOutLine($val)
    {
        $this->rejectOutLine = $val;
    }

    public function sendRejectOut() {
        if ($this->rejectOutSelectedList && count($this->rejectOutSelectedList) > 0) {
            // Create Reject Out Parent
            $rejectOut = RejectOut::create([
                "tanggal" => $this->rejectOutTanggal,
                "no_transaksi" => $this->rejectOutNoTransaksi,
                "tujuan" => ($this->rejectOutStatus == 'reworked' ? $this->rejectOutLine : $this->rejectOutTujuan),
                "created_by" => Auth::user()->line_id,
                "created_by_username" => Auth::user()->username
            ]);

            if ($rejectOut) {
                // Create Reject Out Detail
                $rejectOutBatch = Str::uuid();
                $rejectOutDetailArr = [];
                foreach ($this->rejectOutSelectedList as $reject) {
                    array_push($rejectOutDetailArr, [
                        "reject_in_id" => $reject['id'],
                        "reject_out_id" => $rejectOut->id,
                        "batch" => $rejectOutBatch,
                        "created_by" => Auth::user()->line_id,
                        "created_by_username" => Auth::user()->username,
                        "created_at" => Carbon::now(),
                        "updated_at" => Carbon::now(),
                    ]);
                }

                $createRejectOutDetail = RejectOutDetail::insert($rejectOutDetailArr);

                if ($createRejectOutDetail) {
                    // Update Reject In Process
                    $rejectInIds = DB::table("output_reject_out_detail")->where("batch", $rejectOutBatch)->pluck("reject_in_id")->toArray();
                    RejectIn::whereIn("id", $rejectInIds)->update([
                        "process" => "sent"
                    ]);

                    $this->rejectOutSelectedList = [];
                    $this->rejectOutTanggal = date("Y-m-d");
                    $this->rejectOutNoTransaksi = null;
                    $this->rejectOutTujuan = "gudang";
                    $this->rejectOutLine = null;
                    $this->rejectOutStatus = null;

                    $this->emit('alert', 'success', count($rejectInIds)." reject berhasil di kirim.");

                    $this->emit('refreshRejectOutNumber');
                } else {
                    $this->emit('alert', 'error', "Terjadi kesalahan.");
                }
            } else {
                $this->emit('alert', 'error',  "Terjadi kesalahan.");
            }
        } else {
            $this->emit('alert', 'error',  "Harap pilih reject out.");
        }
    }

    public function render()
    {
        $this->loadingMasterPlan = false;

        $this->lines = UserPassword::where("Groupp", "SEWING")->orderBy("line_id", "asc")->get();

        // Reject IN List
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
            leftJoin("output_reject_in", function($join) {
                $join->on("output_reject_in.reject_id", "=", "output_rejects_packing.id");
                $join->on("output_reject_in.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            whereNotNull("output_rejects_packing.kode_numbering")->
            whereNull("output_reject_in.id")->
            whereRaw("output_rejects_packing.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y")."-12-31 23:59:59'");
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
            if ($this->rejectInDate) {
                $rejectInPackingQuery->where("master_plan.tgl_plan", ">=", date("Y-m-d", strtotime(date("Y-m-d")." -30 days")) );
            }
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
            leftJoin("output_reject_in", function($join) {
                $join->on("output_reject_in.reject_id", "=", "output_check_finishing.id");
                $join->on("output_reject_in.output_type", "=", DB::raw("'qcf'"));
            })->
            whereNotNull("master_plan.id")->
            whereNotNull("output_check_finishing.kode_numbering")->
            whereNull("output_reject_in.id")->
            whereRaw("output_check_finishing.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y")."'");
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
            if ($this->rejectInDate) {
                $rejectInQcfQuery->where("master_plan.tgl_plan", ">=", date("Y-m-d", strtotime(date("Y-m-d")." -30 days")) );
            }
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
            leftJoin("output_reject_in", function($join) {
                $join->on("output_reject_in.reject_id", "=", "output_rejects.id");
                $join->on("output_reject_in.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            whereNotNull("output_rejects.kode_numbering")->
            whereNull("output_reject_in.id")->
            whereRaw("output_rejects.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y")."-12-31 23:59:59'");
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
            if ($this->rejectInDate) {
                $rejectInQcQuery->where("master_plan.tgl_plan", ">=", date("Y-m-d", strtotime(date("Y-m-d")." -30 days")) );
            }
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

            $rejectInUnion = $rejectInQc->unionAll($rejectInQcf)->unionAll($rejectInPacking);

            $rejectInQuery = DB::query()->fromSub($rejectInUnion, 'rejects');
                if ($this->rejectInFilterKode) {
                    $rejectInQuery->where("kode_numbering", "like", "%".$this->rejectInFilterKode."%");
                }
                if ($this->rejectInFilterWaktu) {
                    $rejectInQuery->where("reject_time", "like", "%".$this->rejectInFilterWaktu."%");
                }
                if ($this->rejectInFilterLine) {
                    $rejectInQuery->where("sewing_line", "like", "%".str_replace(" ", "_", $this->rejectInFilterLine)."%");
                }
                if ($this->rejectInFilterMasterPlan) {
                    $rejectInQuery->whereRaw("(
                        ws LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                        style LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                        color LIKE '%".$this->rejectInFilterMasterPlan."%'
                    )");
                }
                if ($this->rejectInFilterSize) {
                    $rejectInQuery->where("size", "like", "%".$this->rejectInFilterSize."%");
                }
                if ($this->rejectInFilterType) {
                    $rejectInQuery->where("defect_type", "like", "%".$this->rejectInFilterType."%");
                }
            $rejectIn = $rejectInQuery;

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
            leftJoin("output_reject_in", function($join) {
                $join->on("output_reject_in.reject_id", "=", "output_rejects_packing.id");
                $join->on("output_reject_in.output_type", "=", DB::raw("'packing'"));
            })->
            whereNotNull("master_plan.id")->
            whereNotNull("output_rejects_packing.kode_numbering")->
            whereNull("output_reject_in.id")->
            whereRaw("output_rejects_packing.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y")."-12-31 23:59:59'");
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
            if ($this->rejectInDate) {
                $rejectInQuery->where("master_plan.tgl_plan", ">=", date("Y-m-d", strtotime(date("Y-m-d")." -30 days")) );
            }
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
            if ($this->rejectInFilterKode) {
                $rejectInQuery->where("output_rejects_packing.kode_numbering", "like", "%".$this->rejectInFilterKode."%");
            }
            if ($this->rejectInFilterWaktu) {
                $rejectInQuery->where("output_rejects_packing.updated_at", "like", "%".$this->rejectInFilterWaktu."%");
            }
            if ($this->rejectInFilterLine) {
                $rejectInQuery->where("master_plan.sewing_line", "like", "%".str_replace(" ", "_", $this->rejectInFilterLine)."%");
            }
            if ($this->rejectInFilterMasterPlan) {
                $rejectInQuery->whereRaw("(
                    act_costing.kpno LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                    so_det.color LIKE '%".$this->rejectInFilterMasterPlan."%'
                )");
            }
            if ($this->rejectInFilterSize) {
                $rejectInQuery->where("so_det.size", "like", "%".$this->rejectInFilterSize."%");
            }
            if ($this->rejectInFilterType) {
                $rejectInQuery->where("output_defect_types.defect_type", "like", "%".$this->rejectInFilterType."%");
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
            leftJoin("output_reject_in", function($join) {
                $join->on("output_reject_in.reject_id", "=", "output_check_finishing.id");
                $join->on("output_reject_in.output_type", "=", DB::raw("'qcf'"));
            })->
            whereNotNull("master_plan.id")->
            whereNotNull("output_check_finishing.kode_numbering")->
            where("output_check_finishing.status", "reject")->
            whereNull("output_reject_in.id")->
            whereRaw("output_check_finishing.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y")."-12-31 23:59:59'");
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
            if ($this->rejectInFilterKode) {
                $rejectInQuery->where("output_check_finishing.kode_numbering", "like", "%".$this->rejectInFilterKode."%");
            }
            if ($this->rejectInFilterWaktu) {
                $rejectInQuery->where("output_check_finishing.updated_at", "like", "%".$this->rejectInFilterWaktu."%");
            }
            if ($this->rejectInFilterLine) {
                $rejectInQuery->where("master_plan.sewing_line", "like", "%".str_replace(" ", "_", $this->rejectInFilterLine)."%");
            }
            if ($this->rejectInFilterMasterPlan) {
                $rejectInQuery->whereRaw("(
                    act_costing.kpno LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                    so_det.color LIKE '%".$this->rejectInFilterMasterPlan."%'
                )");
            }
            if ($this->rejectInFilterSize) {
                $rejectInQuery->where("so_det.size", "like", "%".$this->rejectInFilterSize."%");
            }
            if ($this->rejectInFilterType) {
                $rejectInQuery->where("output_defect_types.defect_type", "like", "%".$this->rejectInFilterType."%");
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
            leftJoin("output_reject_in", function($join) {
                $join->on("output_reject_in.reject_id", "=", "output_rejects.id");
                $join->on("output_reject_in.output_type", "=", DB::raw("'qc'"));
            })->
            whereNotNull("master_plan.id")->
            whereNotNull("output_rejects.kode_numbering")->
            whereNull("output_reject_in.id")->
            whereRaw("output_rejects.updated_at between '".date("Y")."-01-01 00:00:00' and '".date("Y")."-12-31 23:59:59'");
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
            if ($this->rejectInDate) {
                $rejectInQuery->where("master_plan.tgl_plan", ">=", date("Y-m-d", strtotime(date("Y-m-d")." -30 days")) );
            }
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
            if ($this->rejectInFilterKode) {
                $rejectInQuery->where("output_rejects.kode_numbering", "like", "%".$this->rejectInFilterKode."%");
            }
            if ($this->rejectInFilterWaktu) {
                $rejectInQuery->where("output_rejects.updated_at", "like", "%".$this->rejectInFilterWaktu."%");
            }
            if ($this->rejectInFilterLine) {
                $rejectInQuery->where("master_plan.sewing_line", "like", "%".str_replace(" ", "_", $this->rejectInFilterLine)."%");
            }
            if ($this->rejectInFilterMasterPlan) {
                $rejectInQuery->whereRaw("(
                    act_costing.kpno LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                    act_costing.styleno LIKE '%".$this->rejectInFilterMasterPlan."%' OR
                    so_det.color LIKE '%".$this->rejectInFilterMasterPlan."%'
                )");
            }
            if ($this->rejectInFilterSize) {
                $rejectInQuery->where("so_det.size", "like", "%".$this->rejectInFilterSize."%");
            }
            if ($this->rejectInFilterType) {
                $rejectInQuery->where("output_defect_types.defect_type", "like", "%".$this->rejectInFilterType."%");
            }
            $rejectIn = $rejectInQuery->
                groupBy("master_plan.sewing_line", "master_plan.id", "output_defect_types.id", "output_rejects.so_det_id", "output_rejects.kode_numbering");
        }

        $rejectInTotal = $rejectIn->get()->sum("reject_qty");
        $rejectInList = $rejectIn->
            orderBy("reject_time", "desc")->
            orderBy("sewing_line")->
            orderBy("id_ws")->
            orderBy("color")->
            orderBy("defect_type")->
            orderBy("so_det_id")->
            orderBy("output_type")->
            paginate(100, ['*'], 'rejectInPage');

        // All Defect Summary
        $rejectDaily = RejectIn::selectRaw("
                DATE(output_reject_in.created_at) tanggal,
                SUM(CASE WHEN (CASE WHEN output_reject_in.output_type = 'packing' THEN 1 ELSE (CASE WHEN output_reject_in.output_type = 'qcf' THEN 1 ELSE 1 END) END) IS NOT NULL THEN 1 ELSE 0 END) total_in,
                SUM(CASE WHEN (CASE WHEN output_reject_in.output_type = 'packing' THEN 1 ELSE (CASE WHEN output_reject_in.output_type = 'qcf' THEN 1 ELSE 1 END) END) IS NOT NULL AND output_reject_in.status = 'rejected' THEN 1 ELSE 0 END) total_reject,
                SUM(CASE WHEN (CASE WHEN output_reject_in.output_type = 'packing' THEN 1 ELSE (CASE WHEN output_reject_in.output_type = 'qcf' THEN 1 ELSE 1 END) END) IS NOT NULL AND output_reject_in.status = 'reworked' THEN 1 ELSE 0 END) total_good
            ")->
            leftJoin("output_rejects", "output_rejects.id", "=", "output_reject_in.reject_id")->
            leftJoin("output_rejects_packing", "output_rejects_packing.id", "=", "output_reject_in.reject_id")->
            leftJoin("output_check_finishing", "output_check_finishing.id", "=", "output_reject_in.reject_id")->
            whereBetween("output_reject_in.created_at", [$this->rejectInOutFrom." 00:00:00", $this->rejectInOutTo." 23:59:59"])->
            groupByRaw("DATE(output_reject_in.created_at)")->
            orderByRaw("DATE(output_reject_in.created_at) desc")->
            get();

        $rejectTotal = $rejectDaily->sum("total_in");

        // Defect types
        $this->defectTypes = DB::table("output_defect_types")->leftJoin(DB::raw("(select reject_type_id, count(id) total_reject from output_rejects where updated_at between '".date("Y-m-d", strtotime(date("Y-m-d").' -10 days'))." 00:00:00' and '".date("Y-m-d")." 23:59:59' group by reject_type_id) as rejects"), "rejects.reject_type_id", "=", "output_defect_types.id")->whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('total_reject', 'desc')->orderBy('defect_type')->get();

        // Defect areas
        $this->defectAreas = DB::table("output_defect_areas")->leftJoin(DB::raw("(select reject_area_id, count(id) total_reject from output_rejects where updated_at between '".date("Y-m-d", strtotime(date("Y-m-d").' -10 days'))." 00:00:00' and '".date("Y-m-d")." 23:59:59' group by reject_area_id) as rejects"), "rejects.reject_area_id", "=", "output_defect_areas.id")->whereRaw("(hidden IS NULL OR hidden != 'Y')")->orderBy('total_reject', 'desc')->orderBy('defect_area')->get();

        return view('livewire.reject-in-out', [
            "rejectInList" => $rejectInList,
            "totalRejectIn" => $rejectInTotal,
            "totalRejectInOut" => $rejectTotal
        ]);
    }

    public function refreshComponent()
    {
        $this->emit('$refresh');
    }
}
