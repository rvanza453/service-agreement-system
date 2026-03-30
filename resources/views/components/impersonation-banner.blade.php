@if(session()->has('impersonate_admin_id'))
    <style>
        body { padding-top: 54px !important; }
    </style>
    <div style="position:fixed;top:0;left:0;right:0;z-index:9999;background:#f59e0b;color:#fff;padding:10px 14px;box-shadow:0 1px 6px rgba(0,0,0,0.15);">
        <div style="max-width:1320px;margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
            <div style="font-weight:700;font-size:13px;">Impersonation aktif: Anda login sebagai {{ auth()->user()->name }}</div>
            <form action="{{ route('users.leave-impersonate') }}" method="POST" style="margin:0;">
                @csrf
                <button type="submit" style="border:0;background:#fff;color:#b45309;font-weight:700;font-size:12px;padding:6px 10px;border-radius:8px;cursor:pointer;">
                    Leave Impersonation
                </button>
            </form>
        </div>
    </div>
@endif
