<?php

namespace Modules\QcComplaintSystem\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\ServiceAgreementSystem\Models\Block;
use Modules\ServiceAgreementSystem\Models\Department;
use Modules\ServiceAgreementSystem\Models\SubDepartment;

class QcFinding extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_REVIEW = 'in_review';
    public const STATUS_CLOSED = 'closed';

    public const URGENCY_LOW = 'low';
    public const URGENCY_MEDIUM = 'medium';
    public const URGENCY_HIGH = 'high';

    public const SOURCE_QC_SITE = 'qc_site';
    public const SOURCE_WORKER_DIRECT = 'worker_direct';
    public const SOURCE_SELF = 'self';

    protected $fillable = [
        'finding_number',
        'finding_date',
        'title',
        'description',
        'finding_photo_path',
        'source_type',
        'department_id',
        'sub_department_id',
        'block_id',
        'location',
        'urgency',
        'status',
        'reporter_user_id',
        'reporter_name',
        'pic_user_id',
        'pic_user_ids',
        'created_by',
        'updated_by',
        'completion_note',
        'completion_photo_path',
        'completion_submitted_by',
        'completion_submitted_at',
        'completion_approved_by',
        'completion_approved_at',
        'completion_approval_note',
        'completion_rejected_note',
        'needs_resubmission',
        'kategori',
        'sub_kategori',
        'kategori_code',
        'closed_at',
        'finding_attachments',
    ];

    protected $casts = [
        'finding_date' => 'date',
        'completion_submitted_at' => 'datetime',
        'completion_approved_at' => 'datetime',
        'needs_resubmission' => 'boolean',
        'closed_at' => 'datetime',
        'finding_attachments' => 'array',
        'pic_user_ids' => 'array',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function subDepartment(): BelongsTo
    {
        return $this->belongsTo(SubDepartment::class);
    }

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function completionSubmitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completion_submitted_by');
    }

    public function completionApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completion_approved_by');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(QcFindingApprovalStep::class, 'qc_finding_id')->orderBy('level');
    }

    public function completionEvidences(): HasMany
    {
        return $this->hasMany(QcFindingCompletionEvidence::class, 'qc_finding_id')->latest('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(QcFindingComment::class, 'qc_finding_id')->orderBy('created_at', 'desc');
    }

    public function mainComments(): HasMany
    {
        return $this->hasMany(QcFindingComment::class, 'qc_finding_id')
            ->whereNull('parent_comment_id')
            ->orderBy('created_at', 'desc');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    public function hasPendingCompletionApproval(): bool
    {
        return $this->status === self::STATUS_IN_REVIEW
            && !is_null($this->completion_submitted_at)
            && !$this->needs_resubmission
            && is_null($this->completion_approved_at);
    }

    public function currentPendingApprovalStep(): ?QcFindingApprovalStep
    {
        return $this->approvalSteps
            ->where('status', QcFindingApprovalStep::STATUS_PENDING)
            ->sortBy('level')
            ->first();
    }

    public static function statusOptions(): array
    {
        return [self::STATUS_OPEN, self::STATUS_IN_REVIEW, self::STATUS_CLOSED];
    }

    public static function urgencyOptions(): array
    {
        return [self::URGENCY_LOW, self::URGENCY_MEDIUM, self::URGENCY_HIGH];
    }

    public static function urgencyLabel(?string $urgency): string
    {
        return match (strtolower((string) $urgency)) {
            'low' => 'Prioritas Rendah',
            'medium', 'normal' => 'Prioritas Sedang',
            'high', 'hight' => 'Prioritas Tinggi',
            default => 'Prioritas -',
        };
    }

    public static function sourceOptions(): array
    {
        return [self::SOURCE_SELF, self::SOURCE_WORKER_DIRECT];
    }

    public static function categoryOptions(): array
    {
        return array_keys(self::categoryHierarchy());
    }

    public static function categoryHierarchy(): array
    {
        return [
            'panen' => [
                'label' => 'Panen',
                'code' => 'PNN',
                'subs' => [
                    'berondolan_tinggal_segar' => ['label' => 'Berondolan Tinggal Segar', 'code' => 'PNN-01'],
                    'berondolan_tinggal_busuk' => ['label' => 'Berondolan Tinggal Busuk', 'code' => 'PNN-02'],
                    'buah_tinggal_segar' => ['label' => 'Buah Tinggal Segar', 'code' => 'PNN-03'],
                    'buah_tinggal_busuk' => ['label' => 'Buah Tinggal Busuk', 'code' => 'PNN-04'],
                    'pelepah_sengklek' => ['label' => 'Pelepah Sengklek', 'code' => 'PNN-05'],
                ]
            ],
            'perawatan' => [
                'label' => 'Perawatan',
                'code' => 'RWT',
                'subs' => [
                    'piringan_kotor' => ['label' => 'Piringan Kotor', 'code' => 'RWT-01'],
                    'pasar_pikul_tertutup' => ['label' => 'Pasar Pikul Tertutup', 'code' => 'RWT-02'],
                ]
            ],
            'pemupukan' => [
                'label' => 'Pemupukan',
                'code' => 'PUP',
                'subs' => [
                    'aplikasi_tidak_standar' => ['label' => 'Aplikasi Tidak Standar', 'code' => 'PUP-01'],
                    'pupuk_menggumpal' => ['label' => 'Pupuk Menggumpal didiamkan', 'code' => 'PUP-02'],
                ]
            ],
            'pengangkutan' => [
                'label' => 'Pengangkutan',
                'code' => 'ANG',
                'subs' => [
                    'pengangkutan_langsung' => ['label' => 'Pengangkutan Langsung', 'code' => 'ANG-01'],
                    'pengangkutan_tidak_langsung' => ['label' => 'Pengangkutan Tidak Langsung', 'code' => 'ANG-02'],
                ]
            ],
            'infrastruktur' => [
                'label' => 'Infrastruktur',
                'code' => 'INF',
                'subs' => [
                    'jalan_rusak' => ['label' => 'Jalan Rusak/Berlubang', 'code' => 'INF-01'],
                    'titi_panen_rusak' => ['label' => 'Titi Panen Rusak', 'code' => 'INF-02'],
                ]
            ],'grading' => [
                'label' => 'Grading',
                'code' => 'GRD',
                'subs' => [
                    'grading_tidak_standar' => ['label' => 'Grading Tidak Standar', 'code' => 'GRD-01'],
                ]
            ],
            'ESG' => [
                'label' => 'ESG',
                'code' => 'ESG',
                'subs' => [
                    'jangkos' => ['label' => 'Jangkos', 'code' => 'ESG-01'],
                    'limbah' => ['label' => 'Limbah', 'code' => 'ESG-02'],
                    'sosial' => ['label' => 'Sosial', 'code' => 'ESG-03'],
                ]
            ],
            'lainnya' => [
                'label' => 'Lainnya',
                'code' => 'OTH',
                'subs' => []
            ],
        ];
    }
}
