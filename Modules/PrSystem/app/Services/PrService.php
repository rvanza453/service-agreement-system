<?php

namespace Modules\PrSystem\Services;

use Modules\PrSystem\Enums\PrStatus;
use Modules\PrSystem\Models\PurchaseRequest;
use Modules\PrSystem\Models\PrItem;
use Modules\PrSystem\Models\PrApproval;
use Modules\PrSystem\Models\ApproverConfig;
use Modules\PrSystem\Models\Department;
use Modules\PrSystem\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PrService
{
    protected $fonnteService;

    public function __construct(FonnteService $fonnteService = null)
    {
        $this->fonnteService = $fonnteService ?: new FonnteService();
    }

    public function createPr(array $data, array $items)
    {
        return DB::transaction(function () use ($data, $items) {
            // 1. Generate PR Number
            $prNumber = self::generatePrNumber($data['department_id'], $data['request_date']);

            // 2. Create PR Record
            $initialStatus = PrStatus::PENDING->value;

            $pr = PurchaseRequest::create([
                'user_id' => auth()->id(),
                'department_id' => $data['department_id'],
                'sub_department_id' => $data['sub_department_id'] ?? null,
                'pr_number' => $prNumber,
                'request_date' => $data['request_date'],
                'description' => $data['description'],
                'status' => $initialStatus,

                'attachment_path' => $data['attachment_path'] ?? null,
            ]);

            // 3. Create Items
            $totalCost = 0;
            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price_estimation'];
                
                $productName = $item['item_name'] ?? null;
                $unit = $item['unit'] ?? null;
                
                if (!empty($item['product_id'])) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $productName = $product->name;
                        $unit = $product->unit;
                    }
                }

                $pr->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'job_id' => $item['job_id'] ?? null,
                    'item_name' => $productName,
                    'specification' => $item['specification'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit' => $unit,
                    'price_estimation' => $item['price_estimation'],
                    'subtotal' => $subtotal,
                    'manual_category' => $item['manual_category'] ?? null,
                    'url_link' => $item['url_link'] ?? null,
                ]);
                $totalCost += $subtotal;
            }

            $pr->update(['total_estimated_cost' => $totalCost]);

            // 4. Generate Initial Approvals
            $this->generateApprovals($pr);
            
            return $pr;
        });
    }

    public static function generatePrNumber($departmentId, $date)
    {
        $dept = Department::with('site')->find($departmentId);
        $timestamp = strtotime($date);
        $year = date('Y', $timestamp);
        $month = date('n', $timestamp);
        $siteName = strtoupper($dept->site->name ?? 'HO');
        $isSsm = $siteName === 'SSM';

        if ($isSsm) {
            // SSM: counter per department within the year
            $lastPr = PurchaseRequest::whereYear('created_at', $year)
                ->where('department_id', $departmentId)
                ->orderBy('id', 'desc')
                ->first();
        } else {
            // Non-SSM: counter per site within the year
            $lastPr = PurchaseRequest::whereYear('created_at', $year)
                ->whereHas('department', fn($q) => $q->where('site_id', $dept->site_id))
                ->orderBy('id', 'desc')
                ->first();
        }

        $count = 1;
        if ($lastPr) {
            if (str_starts_with($lastPr->pr_number, 'PR/')) {
                // Old Format: PR/CODE/YEAR/MONTH/XXXX
                $lastNumber = intval(substr($lastPr->pr_number, -4));
            } else {
                // New Format: XXXX/CODE/ROMAN/YEAR
                $parts = explode('/', $lastPr->pr_number);
                $lastNumber = intval($parts[0]);
            }
            $count = $lastNumber + 1;
        }

        $romanMonth = self::getRomanMonth($month);

        if ($isSsm) {
            // SSM format: XXXX/COA-SSM/ROMAN_MONTH/YEAR
            $deptSiteCode = $dept->coa . '-' . $siteName;
            return sprintf("%04d/%s/%s/%s", $count, $deptSiteCode, $romanMonth, $year);
        } else {
            // Non-SSM format: XXXX/SITE/ROMAN_MONTH/YEAR
            return sprintf("%04d/%s/%s/%s", $count, $siteName, $romanMonth, $year);
        }
    }

    public function startApprovals(PurchaseRequest $pr)
    {
        // 1. Generate Approvals
        $this->generateApprovals($pr);

        // 2. Scheduled Notification will handle the alert
        Log::info('Approvals generated for PR ' . $pr->id);
    }

    private function generateApprovals(PurchaseRequest $pr)
    {
        $approverConfigs = ApproverConfig::where('department_id', $pr->department_id)
            ->orderBy('level')
            ->get();

        $maxLevel = 0;

        foreach ($approverConfigs as $config) {
            $pr->approvals()->create([
                'approver_id' => $config->user_id,
                'level' => $config->level,
                'role_name' => $config->role_name,
                'status' => PrStatus::PENDING->value,
            ]);
            $maxLevel = max($maxLevel, $config->level);
        }

        // Check for Global Approvals (HO)
        if ($pr->department->use_global_approval) {
            $siteId = $pr->department->site_id;
            
            // Fetch Global Approvers applicable for this site
            // Logic: Include Global (site_id is null) AND Site Specific (site_id matches)
            $globalApprovers = \Modules\PrSystem\Models\GlobalApproverConfig::where(function($q) use ($siteId) {
                    $q->whereNull('site_id')
                      ->orWhere('site_id', $siteId);
                })
                ->orderBy('level')
                ->get();
            
            foreach ($globalApprovers as $globalConfig) {
                $newLevel = $maxLevel + $globalConfig->level;

                $pr->approvals()->create([
                    'approver_id' => $globalConfig->user_id,
                    'level' => $newLevel,
                    'role_name' => $globalConfig->role_name,
                    'status' => PrStatus::PENDING->value,
                ]);
            }
        }
    }

    public static function getRomanMonth($month)
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $map[$month] ?? 'I';
    }
}
