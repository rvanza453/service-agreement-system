<x-serviceagreementsystem::layouts.master :title="'Login - SAS'">
    <style>
        /* Elegant Reset */
        .main-content { margin-left: 0 !important; }
        .sidebar, .top-bar { display: none !important; }

        .login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            background-image: 
                radial-gradient(at 0% 0%, hsla(210,100%,98%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(215,25%,90%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(210,100%,98%,1) 0, transparent 50%);
            overflow: hidden;
            position: relative;
        }

        .login-container {
            position: relative;
            margin-top: 50px; /* Space for Minion on top */
        }

        /* PROFESSIONAL CARD */
        .login-card {
            width: 440px;
            background: #ffffff;
            border-radius: 24px;
            padding: 50px;
            box-shadow: 
                0 10px 15px -3px rgba(0, 0, 0, 0.04),
                0 20px 25px -5px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
            z-index: 20;
        }

        /* MINION V2 (SITTING ON TOP) */
        .minion-sitting {
            position: absolute;
            top: -110px; /* Sit on the edge */
            right: 40px;
            z-index: 10;
            width: 110px;
            height: 120px;
            perspective: 800px;
        }

        .minion-body {
            width: 85px;
            height: 125px;
            background: #ffdb00;
            border-radius: 45px 45px 15px 15px;
            position: relative;
            box-shadow: inset -8px -8px 15px rgba(0,0,0,0.1);
            transform-style: preserve-3d;
            transition: transform 0.1s ease-out;
        }

        /* Hair Tufts */
        .hair-tuft {
            position: absolute;
            top: -12px;
            left: 50%;
            width: 2px;
            height: 15px;
            background: #333;
            transform: translateX(-50%);
        }
        .hair-tuft::before, .hair-tuft::after {
            content: '';
            position: absolute;
            width: 2px;
            height: 12px;
            background: #333;
            top: 2px;
        }
        .hair-tuft::before { transform: rotate(-25deg); left: -4px; }
        .hair-tuft::after { transform: rotate(25deg); right: -4px; }

        /* Goggles */
        .goggles {
            position: absolute;
            top: 25px;
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 2px;
        }
        .goggle-strap {
            position: absolute;
            top: 20px;
            width: 100%;
            height: 8px;
            background: #333;
            z-index: -1;
        }
        .goggle-frame {
            width: 42px;
            height: 42px;
            background: #94a3b8;
            border: 3px solid #64748b;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 3px 5px rgba(0,0,0,0.2);
        }
        .eye {
            width: 30px;
            height: 30px;
            background: #fff;
            border-radius: 50%;
            position: relative;
            overflow: hidden;
            /* Blinking animation */
            animation: blink 4s infinite;
        }
        @keyframes blink {
            0%, 95%, 100% { height: 30px; margin-top: 0; }
            97% { height: 2px; margin-top: 14px; }
        }
        .pupil {
            width: 11px;
            height: 11px;
            background: #333;
            border-radius: 50%;
            position: absolute;
            top: 50%;
            left: 50%;
            margin-top: -5.5px;
            margin-left: -5.5px;
        }

        /* Overalls V2 */
        .overalls {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 35px;
            background: #2b568e;
            border-radius: 0 0 15px 15px;
        }
        .pocket {
            position: absolute;
            top: 5px;
            left: 50%;
            width: 20px;
            height: 20px;
            background: #1e3a8a;
            border-radius: 0 0 10px 10px;
            transform: translateX(-50%);
        }
        .pocket::after {
            content: 'G'; /* Gru logo vibe */
            font-size: 8px;
            color: #fff;
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            font-weight: 900;
        }

        /* Arms & Gloves */
        .arm {
            position: absolute;
            top: 70px;
            width: 10px;
            height: 30px;
            background: #ffdb00;
            left: -8px;
            transform: rotate(15deg);
            border-radius: 5px;
        }
        .arm.right { left: auto; right: -8px; transform: rotate(-15deg); }
        .glove {
            position: absolute;
            bottom: -5px;
            width: 12px;
            height: 12px;
            background: #333;
            border-radius: 4px;
        }

        /* LEG SITTING */
        .leg-sitting {
            position: absolute;
            bottom: -15px;
            width: 15px;
            height: 20px;
            background: #2b568e;
            left: 15px;
            border-radius: 0 0 5px 5px;
        }
        .leg-sitting.right { left: auto; right: 15px; }
        .shoe {
            position: absolute;
            bottom: -5px;
            width: 20px;
            height: 10px;
            background: #333;
            border-radius: 10px 10px 0 0;
            left: -2px;
        }

        /* ANIMATIONS */
        @keyframes happy-jump {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }
        .animate-jump {
            animation: happy-jump 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes cheering {
            0%, 100% { transform: scale(1) rotate(0); }
            25% { transform: scale(1.1) rotate(-5deg); }
            75% { transform: scale(1.1) rotate(5deg); }
        }
        .animate-cheer {
            animation: cheering 0.4s infinite;
        }

        /* FORM STYLES */
        .login-header { margin-bottom: 35px; }
        .login-title { font-size: 26px; font-weight: 800; color: #1e293b; margin-bottom: 6px; }
        .login-subtitle { color: #64748b; font-size: 14px; }

        .form-group { margin-bottom: 22px; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
        .form-control { width: 100%; padding: 13px 16px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; font-size: 15px; transition: all 0.2s; }
        .form-control:focus { outline: none; border-color: #2b568e; background: #fff; box-shadow: 0 0 0 4px rgba(43, 86, 142, 0.1); }

        .btn-login { width: 100%; padding: 14px; background: #1e293b; color: #fff; border: none; border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .btn-login:hover { background: #0f172a; transform: translateY(-1px); }

        .alert-error { background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 13px; }

        @media (max-width: 500px) {
            .login-card { width: 90vw; padding: 30px; }
            .minion-sitting { right: 20px; scale: 0.8; top: -90px; }
        }
    </style>

    <div class="login-page">
        <div class="login-container">
            {{-- MINION V2 SITTING ON CARD --}}
            <div class="minion-sitting" id="minionContainer" style="cursor: pointer;" title="Klik aku!">
                <div class="hair-tuft"></div>
                <div class="minion-body" id="minionBody">
                    <div class="goggle-strap"></div>
                    <div class="goggles">
                        <div class="goggle-frame"><div class="eye"><div class="pupil"></div></div></div>
                        <div class="goggle-frame"><div class="eye"><div class="pupil"></div></div></div>
                    </div>
                    <div class="overalls">
                        <div class="pocket"></div>
                    </div>
                    <div class="arm">
                        <div class="glove"></div>
                    </div>
                    <div class="arm right">
                        <div class="glove"></div>
                    </div>
                    <div class="leg-sitting">
                        <div class="shoe"></div>
                    </div>
                    <div class="leg-sitting right">
                        <div class="shoe"></div>
                    </div>
                </div>
            </div>

            <div class="login-card">
                <div class="login-header">
                    <h1 class="login-title">Selamat Datang</h1>
                    <p class="login-subtitle">Akses portal ERP dan pilih modul yang dibutuhkan.</p>
                </div>

                @if($errors->any())
                    <div class="alert-error">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('login.submit') }}" method="POST" id="loginForm">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="emailField" class="form-control" value="{{ old('email') }}" placeholder="user@sas.test" required autofocus>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" id="passwordField" class="form-control" placeholder="••••••••" required>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: flex; align-items: center; gap: 8px; font-size: 14px; color: #64748b; cursor: pointer;">
                            <input type="checkbox" name="remember" style="width: 16px; height: 16px; accent-color: #2b568e;">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="btn-login" id="loginBtn">
                        <span id="btnText">Login Sekarang</span>
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const minionContainer = document.getElementById('minionContainer');
            const minionBody = document.getElementById('minionBody');
            const pupils = document.querySelectorAll('.pupil');
            const emailField = document.getElementById('emailField');
            const passwordField = document.getElementById('passwordField');
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');

            let isInteracting = false;
            let focusTarget = null;

            // --- 1. Minion Click (Jump) ---
            minionContainer.addEventListener('click', () => {
                minionBody.classList.add('animate-jump');
                setTimeout(() => minionBody.classList.remove('animate-jump'), 500);
            });

            // --- 2. Input Focus (Eye Lock) ---
            const handleFocus = (e) => {
                isInteracting = true;
                focusTarget = e.target;
            };

            const handleBlur = () => {
                isInteracting = false;
                focusTarget = null;
            };

            emailField.addEventListener('focus', handleFocus);
            emailField.addEventListener('blur', handleBlur);
            passwordField.addEventListener('focus', handleFocus);
            passwordField.addEventListener('blur', handleBlur);

            // --- 3. Login Click / Submit (Cheer) ---
            loginForm.addEventListener('submit', () => {
                minionBody.classList.add('animate-cheer');
                document.getElementById('btnText').innerText = 'Harap Tunggu...';
            });

            // --- Mouse Move Logic ---
            document.addEventListener('mousemove', (e) => {
                const x = e.clientX;
                const y = e.clientY;

                let targetX = x;
                let targetY = y;

                // Adjust target if focusing on input
                if (isInteracting && focusTarget) {
                    const rect = focusTarget.getBoundingClientRect();
                    targetX = rect.left + rect.width / 2;
                    targetY = rect.top + rect.height / 2;
                }

                // Pupil Eye Tracking
                pupils.forEach(pupil => {
                    const rect = pupil.parentElement.getBoundingClientRect();
                    const center = { x: rect.left + rect.width / 2, y: rect.top + rect.height / 2 };
                    const angle = Math.atan2(targetY - center.y, targetX - center.x);
                    const force = Math.min(Math.hypot(targetX - center.x, targetY - center.y) / 12, 10);
                    
                    pupil.style.transform = `translate(${Math.cos(angle) * force}px, ${Math.sin(angle) * force}px)`;
                });

                // Head Tilt Physics
                const mRect = minionBody.getBoundingClientRect();
                const mCenter = { x: mRect.left + mRect.width / 2, y: mRect.top + mRect.height / 2 };
                
                const rx = (mCenter.y - targetY) / 50; 
                const ry = (targetX - mCenter.x) / 50;
                const limit = isInteracting ? 15 : 12; // Tilt more when curious
                
                minionBody.style.transform = `rotateX(${Math.max(-limit, Math.min(limit, rx))}deg) rotateY(${Math.max(-limit, Math.min(limit, ry))}deg)`;
            });
        });
    </script>
    @endpush
</x-serviceagreementsystem::layouts.master>
