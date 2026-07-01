<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Certificate Verification — Techsoul</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#f0f7fa',
                            100: '#e1eff5',
                            500: '#0d4a70',
                            600: '#0b3f60',
                            700: '#09344f',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between">

    <!-- Header / Brand -->
    <header class="bg-white border-b border-slate-200 py-4 shadow-xs">
        <div class="max-w-4xl mx-auto px-4 flex justify-between items-center">
            @php
                $companyLogo = ($intern && $intern->company && $intern->company->logo)
                    ? $intern->company->logo
                    : \App\Models\Setting::get('company_logo');
                $companyName = ($intern && $intern->company && $intern->company->name)
                    ? $intern->company->name
                    : \App\Models\Setting::get('company_name', 'Techsoul');
            @endphp
            <div class="flex items-center">
                @if($companyLogo)
                    <img src="{{ asset('storage/' . $companyLogo) }}" alt="{{ $companyName }}" class="h-6 w-auto object-contain" style="max-height: 24px;">
                @else
                    <span class="text-xl font-black text-brand-500 tracking-wider">{{ strtoupper($companyName) }}</span>
                @endif
            </div>
            <a href="https://teamtechsoul.com/" class="text-sm font-medium text-slate-500 hover:text-brand-500 transition-colors">
                <i class="bi bi-house-door-fill mr-1"></i> Home
            </a>
        </div>
    </header>

    <!-- Main Content Card -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="max-w-xl w-full">
            @if($intern)
                <!-- Genuine Verification -->
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden transform transition-all duration-300 hover:scale-[1.01]">
                    
                    <!-- Card Header / Status -->
                    <div class="bg-emerald-500 p-6 text-center text-white relative">
                        <!-- Decorative Ring -->
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                        
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-3 backdrop-blur-xs border border-white/30">
                            <i class="bi bi-patch-check-fill text-3xl"></i>
                        </div>
                        <h1 class="text-xl font-bold uppercase tracking-wider">Genuineness Verified</h1>
                        <p class="text-sm text-emerald-50 opacity-90 mt-1">This certificate is authentic and officially issued by Techsoul</p>
                    </div>

                    <!-- Certificate Details -->
                    <div class="p-6 sm:p-8 space-y-6">
                        
                        <!-- Intern Identity Header -->
                        <div class="text-center pb-6 border-b border-slate-100 flex flex-col items-center justify-center">
                            @if($intern->photo)
                                <div class="mb-3">
                                    <img src="{{ asset('storage/' . $intern->photo) }}" alt="{{ $intern->name }}" class="w-24 h-24 rounded-full object-cover shadow-md border-2 border-slate-200">
                                </div>
                            @else
                                <div class="mb-3 inline-flex items-center justify-center w-24 h-24 rounded-full bg-slate-100 border-2 border-slate-200 text-slate-400">
                                    <i class="bi bi-person text-5xl"></i>
                                </div>
                            @endif
                            <h2 class="text-2xl font-extrabold text-slate-900 mb-1">{{ $intern->name }}</h2>
                            <p class="text-sm font-medium text-brand-500 uppercase tracking-widest">{{ $intern->designation->name ?? 'Intern' }}</p>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-y-5 gap-x-4 text-sm">
                            
                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Intern ID</span>
                                <span class="font-bold text-slate-800">TSLB-INT-{{ str_pad(2541 + $intern->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Certificate No</span>
                                <span class="font-mono font-bold text-brand-500">{{ $intern->certificate_code }}</span>
                            </div>

                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Department</span>
                                <span class="font-semibold text-slate-700">{{ $intern->department->name ?? 'N/A' }}</span>
                            </div>

                            <div>
                                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</span>
                                <span class="inline-flex items-center gap-1.5 font-bold uppercase text-xs px-2.5 py-0.5 rounded-full 
                                    {{ $intern->status === 'completed' ? 'bg-brand-50 text-brand-500 border border-brand-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $intern->status === 'completed' ? 'bg-brand-500' : 'bg-emerald-500' }}"></span>
                                    {{ $intern->status }}
                                </span>
                            </div>

                            <div class="col-span-2 p-4 bg-slate-50 rounded-2xl border border-slate-100 grid grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Joining Date</span>
                                    <span class="font-bold text-slate-700">{{ $intern->joining_date->format('d M Y') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">Completion Date</span>
                                    <span class="font-bold text-slate-700">{{ $intern->end_date->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Bottom Notice -->
                        <div class="bg-blue-50/50 border border-blue-100 rounded-2xl p-4 text-xs text-blue-700 flex gap-2.5 items-start">
                            <i class="bi bi-info-circle-fill text-sm mt-0.5 flex-shrink-0"></i>
                            <div>
                                <span class="font-bold block mb-0.5">Verification Information:</span>
                                This record matches our secure database. For any inquiries regarding this credentials, please contact us at <a href="mailto:hr@techsou.support" class="font-bold underline hover:text-blue-800">hr@techsou.support</a>.
                            </div>
                        </div>

                    </div>
                </div>
            @else
                <!-- Invalid Verification -->
                <div class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden text-center transform transition-all duration-300 hover:scale-[1.01]">
                    
                    <!-- Header -->
                    <div class="bg-rose-500 p-8 text-white relative">
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                        <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-white/10 rounded-full blur-xl"></div>
                        
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-3 border border-white/30">
                            <i class="bi bi-shield-slash-fill text-3xl"></i>
                        </div>
                        <h1 class="text-xl font-bold uppercase tracking-wider">Verification Failed</h1>
                        <p class="text-sm text-rose-50 opacity-90 mt-1">This certificate could not be authenticated</p>
                    </div>

                    <!-- Details -->
                    <div class="p-6 sm:p-8 space-y-6">
                        
                        <div class="text-slate-600 text-sm">
                            <p class="mb-4">The certificate code <code class="bg-slate-100 text-rose-500 font-mono font-bold px-2 py-1 rounded text-xs">{{ $displayCode }}</code> does not match any record in our system.</p>
                            <p class="text-xs text-slate-400">Please verify that you have entered or scanned the correct URL. Forged or modified documents are not recognized by Techsoul Cyber Solutions.</p>
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex flex-col gap-2">
                            <a href="mailto:hr@techsou.support" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 px-4 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-envelope"></i> Contact Support
                            </a>
                            <a href="https://teamtechsoul.com/" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold py-3 px-4 rounded-xl text-sm transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-arrow-left"></i> Go to Portal
                            </a>
                        </div>

                    </div>
                </div>
            @endif
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-400">
        <div class="max-w-4xl mx-auto px-4 space-y-2">
            <p class="font-bold text-slate-600">Techsoul Cyber Solutions</p>
            <p>Thapasya, Infopark, Kakkanad, Kochi, Kerala, Pin - 682030</p>
            <p>&copy; {{ date('Y') }} Techsoul. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
