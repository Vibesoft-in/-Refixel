<?php
$privacyEmail = \App\Models\Setting::get('company_email', \App\Models\Setting::get('support_email', 'contact@refixel.com'));
?>
<div class="container py-5 my-3">

  <div class="card p-4 p-md-5 border-0 shadow-sm" style="border-radius: 16px; background: #ffffff;">
    <h1 class="font-weight-bold mb-3" style="font-size: 32px; color: #1a1a1a;">Privacy Policy</h1>
    <p class="text-muted small mb-4">Prepared in line with the Digital Personal Data Protection Act, 2023 (India) • Last Updated: October 21, 2026</p>

    <div style="font-size: 15px; line-height: 1.8; color: #444;">
      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">1. Introduction & Scope</h4>
      <p>REFIXEL ("we", "our", "us") values your privacy and is committed to protecting your personal data in accordance with the Digital Personal Data Protection (DPDP) Act, 2023 of India and applicable telecommunications regulations.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">2. Information We Collect</h4>
      <p>To schedule and fulfill service requests, we collect:</p>
      <ul>
        <li><strong>Identity & Contact:</strong> Full name, verified mobile number, email address.</li>
        <li><strong>Service Location:</strong> Physical address, flat/floor details, city, pincode, and optional GPS geolocation.</li>
        <li><strong>Job Details:</strong> Photos of service area or issue uploaded by customer, booking notes, preferred service dates.</li>
        <li><strong>Transactional Records:</strong> Invoices, payment gateway reference IDs (we do not store credit card/CVV numbers).</li>
        <li><strong>Device & Technical Data:</strong> IP address, browser, device type and basic usage logs.</li>
        <li><strong>Communications:</strong> your messages and calls with our support team (chat, WhatsApp, phone).</li>
        <li><strong>Service Partner Data (for technician applicants):</strong> KYC and identity documents, address proof, trade certificates and bank details.</li>
      </ul>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">3. How We Use Your Data</h4>
      <p>Your data is strictly utilized to:</p>
      <ul>
        <li>Dispatch verified technicians to your address for service execution.</li>
        <li>Send OTP verification, booking updates, invoice receipts, and technician tracking notifications.</li>
        <li>Prevent fraudulent access, rate-limit brute force attacks, and maintain operational logs.</li>
        <li>Comply with statutory GST and accounting laws under Indian regulations.</li>
      </ul>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">4. Data Sharing & Third Parties</h4>
      <p>We do NOT sell or rent your personal information to third-party advertisers. Information is disclosed solely to the assigned service technician (limited to your address, phone, and booking requirements during the active service window) and verified payment/messaging infrastructure providers (e.g. Razorpay, MSG91). Other service providers who process data on our behalf include email delivery providers, WhatsApp/Meta messaging, Google Maps (for location services), and hosting and cloud providers. Each is required to protect your data. We may also disclose data where required by law.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">5. Data Retention & Your Rights</h4>
      <p>You have the right under the DPDP Act to request access to your personal data, rectify inaccuracies, or request account erasure by emailing <a href="mailto:<?= \App\Core\View::e($privacyEmail) ?>" style="color:#f25b29;"><?= \App\Core\View::e($privacyEmail) ?></a>.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">6. Consent & Withdrawal</h4>
      <p>We process your data based on your consent and to provide the service you requested. You can withdraw your consent at any time by emailing <a href="mailto:<?= \App\Core\View::e($privacyEmail) ?>" style="color:#f25b29;"><?= \App\Core\View::e($privacyEmail) ?></a>. Withdrawal does not affect processing already done, and we may be unable to provide services after you withdraw.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">7. Data Retention Periods</h4>
      <p>OTPs are deleted within [FILL: e.g. 10 minutes] of use or expiry. Booking and invoice records are kept for the period required by tax and accounting law. Support chats and logs are kept for [FILL: e.g. 12 months]. Inactive accounts may be deleted after [FILL: e.g. 36 months] following notice to you. When data is no longer needed, it is deleted or anonymised.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">8. Security</h4>
      <p>We use encryption in transit, access controls and password hashing, and limit staff access to what is needed. No system is completely secure. If a data breach affects you, we will inform you and the relevant authorities as required by law.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">9. Cookies & Analytics</h4>
      <p>We use essential cookies for login authentication, cart sessions and site security. You can control cookies through your browser settings. [FILL: list any analytics or advertising tools used, e.g. Google Analytics, or write "We currently do not use advertising cookies or third-party tracking pixels."]</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">10. Children</h4>
      <p>Our services are not intended for persons under 18 years of age. We do not knowingly collect children's data. If you believe a child has provided us data, contact <a href="mailto:<?= \App\Core\View::e($privacyEmail) ?>" style="color:#f25b29;"><?= \App\Core\View::e($privacyEmail) ?></a> and we will delete it.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">11. Technician Access to Your Data</h4>
      <p>Technicians can see your address and phone number only for the active booking. They must not store, share or use this data for any other purpose. Please report any misuse to us.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">12. Photos & Call Recordings</h4>
      <p>Photos you upload and photos taken by technicians are used for the job, quality checks and dispute resolution. Support calls may be recorded for quality and training, and we will tell you at the start of the call.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">13. Your Rights & Grievance Officer</h4>
      <p>You have the right to access, correct and erase your personal data, withdraw consent, seek grievance redressal, and nominate another person to exercise your rights in the event of death or incapacity. We respond within 30 days.<br>
      Grievance Officer: [FILL: full name] | Email: <a href="mailto:<?= \App\Core\View::e($privacyEmail) ?>" style="color:#f25b29;"><?= \App\Core\View::e($privacyEmail) ?></a> | Address: Jaspur - Kashipur Road (in front of BSV Girls Degree College), Jaspur, Uttarakhand 244712.</p>

      <h4 class="font-weight-bold mt-4" style="color: #0a1c33;">14. Cross-border Processing & Policy Changes</h4>
      <p>Some service providers (such as email, cloud and messaging) may process data outside India. We may update this policy from time to time and will show the latest date at the top. For material changes we will notify you.</p>
    </div>
  </div>
</div>
