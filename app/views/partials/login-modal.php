<!-- Shared "Login" popup matching live site -->
<div class="login_popup" id="hdrLoginPopup" style="display:none;">
  <span id="hdrCloseLoginPopup" style="position:absolute; top:18px; right:24px; font-size:26px; line-height:1; cursor:pointer; color:#333; z-index:2;">&times;</span>

  <div class="mobile_context" id="hdrMobileStep">
    <div class="verify_mvo">
      <img src="<?= \App\Core\View::asset('img/refixel-full-logo.png') ?>" alt="REFIXEL Home Services">
    </div>
    <h2>Enter your phone number</h2>
    <p>A secure verification code will be sent to your <span>registered mobile number.</span></p>
    <div class="form-group">
      <span><img src="<?= \App\Core\View::asset('img/ind.png') ?>" alt="+91"> +91 </span>
      <input type="tel" id="hdrLoginMobile" class="form-control verification" placeholder="Enter your phone number" maxlength="10">
    </div>
    <button type="button" id="hdrSendOtpBtn" class="continue_btn">Get OTP</button>
    <div class="text-center mt-2">
      <a href="<?= \App\Core\View::url('/login') ?>" class="small font-weight-bold" style="color: #f25b29;">Or Sign In with Password / Admin</a>
    </div>
    <h6><i class="fa fa-lock"></i> By continuing, you agree to our <a href="<?= \App\Core\View::url('/terms') ?>" target="_blank">T&C</a> and <a href="<?= \App\Core\View::url('/privacy') ?>" target="_blank">Privacy Policy</a>.</h6>
  </div>

  <div class="otp_phonenumber" id="hdrOtpStep" style="display:none;">
    <div class="verify_mvo">
      <img src="<?= \App\Core\View::asset('img/refixel-full-logo.png') ?>" alt="REFIXEL Home Services">
    </div>
    <h4>Phone Number Verification</h4>
    <p>
      Enter the 4-digit verification code sent to your mobile number<br>
      <span id="hdrShowOtpMobile">+91-XXXXXXXXXX</span>
      <a href="javascript:void(0)" id="hdrEditMobileBtn">Edit</a>
    </p>
    <div class="steps_verification">
      <input type="tel" maxlength="1" class="form-control numbers_otp hdr_otp_digit">
      <input type="tel" maxlength="1" class="form-control numbers_otp hdr_otp_digit">
      <input type="tel" maxlength="1" class="form-control numbers_otp hdr_otp_digit">
      <input type="tel" maxlength="1" class="form-control numbers_otp hdr_otp_digit">
    </div>
    <a href="javascript:void(0)" class="resend_opt" id="hdrResendOtpBtn"><i class="fa fa-repeat"></i> Resend OTP</a>
    <h6><i class="fa fa-lock" aria-hidden="true"></i> <span>We've sent you a 4-digit OTP on your registered mobile number</span></h6>
    <button type="button" id="hdrVerifyOtpBtn" class="continue_btn">Next</button>
  </div>
</div>
