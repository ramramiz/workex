<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Certificate of Internship - {{ $intern->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 0;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
            color: #1e293b;
            box-sizing: border-box;
            position: relative;
            height: 100%;
        }
        .page-container {
            width: 210mm;
            height: 297mm;
            position: relative;
            box-sizing: border-box;
            background-color: #ffffff;
            overflow: hidden;
        }

        /* ----- LETTERHEAD HEADER ----- */
        .lh-header {
            position: absolute;
            top: 40px;
            right: 50px;
            text-align: right;
        }
        .lh-logo-text {
            font-size: 32px;
            font-weight: 800;
            color: #0d4a70;
            letter-spacing: 2px;
            margin: 0;
            font-family: 'Arial Black', Gadget, sans-serif;
        }

        /* ----- LETTERHEAD WATERMARK ----- */
        .lh-watermark {
            position: absolute;
            right: -170px;
            top: 360px;
            font-size: 70px;
            font-weight: 900;
            color: #e5edf3;
            letter-spacing: 16px;
            transform: rotate(90deg);
            transform-origin: 50% 50%;
            text-transform: uppercase;
            opacity: 0.8;
            white-space: nowrap;
            z-index: 1;
        }

        /* ----- CERTIFICATE CONTENT ----- */
        .cert-content {
            position: absolute;
            top: 170px;
            left: 55px;
            right: 140px; /* leave extra space for the right watermark */
            z-index: 10;
        }
        
        .cert-header-title {
            text-align: center;
            margin-bottom: 25px;
        }
        .cert-title {
            font-size: 20px;
            font-weight: bold;
            color: #0d4a70;
            letter-spacing: 1.5px;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            font-family: 'Georgia', 'Times New Roman', serif;
        }
        .cert-subtitle {
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            letter-spacing: 2px;
            margin: 0;
            text-transform: uppercase;
        }

        /* Meta Table (No. and Date) */
        .cert-meta-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .cert-meta-table td {
            font-size: 12px;
            color: #334155;
            padding: 2px 0;
        }
        .cert-meta-val {
            font-weight: 700;
            color: #0f172a;
        }

        .cert-concern-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #0d4a70;
            text-decoration: underline;
            margin-bottom: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .cert-description {
            font-size: 12.5px;
            line-height: 1.7;
            color: #334155;
            margin-bottom: 18px;
            text-align: justify;
        }
        .cert-highlight {
            font-weight: 700;
            color: #0f172a;
        }

        /* ----- SIGNATURES & SEAL ----- */
        .cert-footer-table {
            width: 100%;
            margin-top: 35px;
            border-collapse: collapse;
        }
        .cert-footer-table td {
            vertical-align: bottom;
        }
        
        .sig-block {
            text-align: left;
        }
        .sig-for {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 45px;
        }
        .sig-line {
            border-top: 1.5px solid #cbd5e1;
            margin-bottom: 6px;
            width: 180px;
        }
        .sig-value {
            font-size: 13px;
            font-weight: 700;
            color: #1e293b;
        }
        .sig-title {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
        }

        .seal-block {
            text-align: right;
            padding-right: 15px;
        }
        .seal-container {
            display: inline-block;
        }
        .seal {
            width: 75px;
            height: 75px;
            border-radius: 50%;
            border: 2px double #0d4a70;
            background-color: #fcfbf7;
            position: relative;
        }
        .seal-inner {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: 1px dashed #0d4a70;
            position: absolute;
            top: 4px;
            left: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .seal-text {
            font-size: 8px;
            font-weight: bold;
            color: #0d4a70;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        /* ----- LETTERHEAD FOOTER ----- */
        .lh-footer-wrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            box-sizing: border-box;
            background-color: #ffffff;
            z-index: 100;
        }
        .lh-footer-line {
            border-top: 1.5px solid #0d4a70;
            margin: 0 45px 12px 45px;
        }
        .lh-footer-content {
            padding: 0 45px 15px 45px;
            overflow: hidden;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .lh-footer-col-left {
            width: 50%;
            float: left;
            text-align: left;
        }
        .lh-footer-col-right {
            width: 50%;
            float: right;
            text-align: right;
        }
        
        .lh-footer-company {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .lh-footer-text {
            font-size: 9px;
            color: #475569;
            line-height: 1.4;
            text-transform: uppercase;
        }
        .lh-footer-contact {
            font-size: 10px;
            color: #1e293b;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .lh-footer-link {
            font-size: 10px;
            color: #475569;
            line-height: 1.4;
            font-weight: 500;
        }

        .lh-footer-strip {
            height: 12px;
            background-color: #0d4a70;
            width: 100%;
        }
    </style>
</head>
<body>
    @php
        // Compute internship duration dynamically in days (inclusive of start and end date)
        $start = \Carbon\Carbon::parse($intern->joining_date);
        $end = \Carbon\Carbon::parse($intern->end_date);
        $days = $start->diffInDays($end) + 1;
        $durationText = $days . ' Day' . ($days > 1 ? 's' : '');
    @endphp

    <div class="page-container">
        <!-- Watermark -->
        <div class="lh-watermark">TECHSOUL</div>

        <!-- Header -->
        <div class="lh-header">
            <h1 class="lh-logo-text">TECHSOUL</h1>
        </div>

        <!-- Content -->
        <div class="cert-content">
            <div class="cert-header-title">
                <h2 class="cert-title">Internship Completion Certificate</h2>
                <div class="cert-subtitle">Techsoul</div>
            </div>

            <!-- Meta Data (Certificate No. & Date) -->
            <table class="cert-meta-table">
                <tr>
                    <td style="text-align: left;">
                        Certificate No.: <span class="cert-meta-val">{{ $intern->certificate_code }}</span>
                    </td>
                    <td style="text-align: right;">
                        Date: <span class="cert-meta-val">{{ now()->format('d / m / Y') }}</span>
                    </td>
                </tr>
            </table>

            <div class="cert-concern-title">To Whomsoever It May Concern</div>

            <div class="cert-description">
                This is to certify that <span class="cert-highlight">Mr./Ms. {{ $intern->name }}</span>, bearing <span class="cert-highlight">Intern ID: TSLB-INT-{{ str_pad(2541 + $intern->id, 6, '0', STR_PAD_LEFT) }}</span>, has successfully completed a <span class="cert-highlight">{{ $durationText }} Internship</span> as a <span class="cert-highlight">{{ $intern->designation->name ?? 'Intern' }}</span> at <span class="cert-highlight">Techsoul</span> from <span class="cert-highlight">{{ $intern->joining_date->format('d/m/Y') }}</span> to <span class="cert-highlight">{{ $intern->end_date->format('d/m/Y') }}</span>.
            </div>

            <div class="cert-description">
                During the internship period, the intern worked under the guidance of our technical team and actively participated in assigned projects and day-to-day operations. The intern gained practical exposure to industry standards, professional work environments, and real-time project execution.
            </div>

            <div class="cert-description">
                Throughout the internship, <span class="cert-highlight">Mr./Ms. {{ $intern->name }}</span> demonstrated sincerity, dedication, willingness to learn, professional conduct, and the ability to work both independently and as part of a team. The intern successfully completed the assigned responsibilities to our satisfaction.
            </div>

            <div class="cert-description">
                We appreciate the intern's contribution to the organization and wish them every success in their future academic and professional endeavors.
            </div>

            <!-- Footer Signatures & Seal -->
            <table class="cert-footer-table">
                <tr>
                    <td style="width: 30%; text-align: left; vertical-align: bottom;">
                        @if(!empty($qrCodeBase64))
                            <div style="margin-bottom: 5px;">
                                <img src="{{ $qrCodeBase64 }}" style="width: 70px; height: 70px; border: 1px solid #e2e8f0; padding: 2px;">
                            </div>
                            <div class="sig-title" style="font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px;">Scan to Verify</div>
                        @endif
                    </td>
                    <td style="width: 30%; text-align: center; vertical-align: bottom;">
                        <div class="seal-block" style="text-align: center; padding: 0;">
                            <div class="seal-container" style="display: inline-block;">
                                <div class="seal">
                                    <div class="seal-inner">
                                        <div class="seal-text">Techsoul<br>Cyber<br>Solutions</div>
                                    </div>
                                </div>
                                <div class="sig-title" style="margin-top: 5px; text-align: center;">Company Seal</div>
                            </div>
                        </div>
                    </td>
                    <td style="width: 40%; text-align: right; vertical-align: bottom;">
                        <div class="sig-block" style="text-align: right;">
                            <div class="sig-for" style="margin-bottom: 35px;">For Techsoul</div>
                            <div class="sig-line" style="margin-left: auto; width: 180px;"></div>
                            <div class="sig-value">Ramiz Odayappurath</div>
                            <div class="sig-title">Managing Director</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Footer -->
        <div class="lh-footer-wrapper">
            <div class="lh-footer-line"></div>
            <div class="lh-footer-content">
                <div class="lh-footer-col-left">
                    <div class="lh-footer-company">Techsoul Cyber Solutions</div>
                    <div class="lh-footer-text">
                        TECHSOUL CYBER SOLUTIONS<br>
                        THAPASYA, INFOPARK, KAKKANAD, KOCHI<br>
                        KERALA, Pin - 682030
                    </div>
                </div>
                <div class="lh-footer-col-right">
                    <div class="lh-footer-contact">+91 8848 787 656<br>+91 9061 252 408</div>
                    <div class="lh-footer-link">
                        hr@techsou.support<br>
                        www.teamtechsoul.com
                    </div>
                </div>
            </div>
            <div class="lh-footer-strip"></div>
        </div>
    </div>
</body>
</html>
