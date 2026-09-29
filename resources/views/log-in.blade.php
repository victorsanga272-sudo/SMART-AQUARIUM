<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

@vite(['resources/css/log-in.css', 'resources/js/form-animate.js', 'resources/js/auth-validation.js','resources/css/app.css'])
</head>
<body>
    @php
        $rememberedEmail = request()->cookie('remember_email');
        $isHidden=true;
    @endphp
    <section class="log-user h-[100vh] w-[100%] md:w-[90%] lg:w-[70%] lg:h-[90%] grid grid-cols-1 md:grid-cols-2 relative">
    <div class="log-in-div rounded-none md:rounded-l-lg lg:rounded-l-[3%]">
    <form action="{{ route('vivo_users.login') }}" method="POST" class="log-in-form" novalidate>
    <h1>Log In</h1>
    @if (session('status'))
        <p role="status" class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">{{ session('status') }}</p>
    @endif
  @csrf
        <div class="form-group">
        <input type="text" id="login-identifier" name="identifier" value="{{ old('identifier', $rememberedEmail) }}" class="rounded-md" autocomplete="username" required>
        <label for="login-identifier">Username or Email</label>
        @error('email')
            <small class="field-error">{{ $message }}</small>
        @enderror
        </div>

        <div class="form-group">
        <input type="password" id="login-password" name="password"class="rounded-l-md"  required>
        <label for="login-password">Password</label>
         <button type="button" class="password-toggle" aria-label="Toggle Password Visibility">
            <i class="fa-regular fa-eye"></i>
        </button>
        @error('password')
            <small class="field-error">{{ $message }}</small>
        @enderror
        </div>

        <div class="form-options">
            <div class="remember-me">
            <input type="checkbox" id="remember-me" name="remember_me" value="1" {{ old('remember_me', !empty($rememberedEmail)) ? 'checked' : '' }}>
            <label for="remember-me">Remember Me</label>
            </div>
            <div class="forgot-password">
            <a href="forgot-password" class="forgot-password" id="forgot_password">Forgot Password?</a>
            </div>
        </div>
        <button type="submit" id="submit">LOG IN</button>
    </form>
</div>

    <div class="sign-in-div">
    <form action="{{ route('vivo_users.store') }}" method="POST" class="sign-up-form rounded-none md:rounded-r-lg lg:rounded-r-[3%] pt-10 lg:pt-0" novalidate>
        <h1>Sign Up</h1>
@csrf
<div class="social-signing">
    <span class="facebook-login"><i class="fab fa-facebook-f"></i></span>
    <span class="google-login"><i class="fab fa-google"></i></span>
    <span class="twitter-login"><i class="fab fa-twitter"></i></span>
    <span class="linkedin-login"><i class="fab fa-linkedin-in"></i></span>
</div>

<fieldset>
    <legend>Or</legend>
        <div class="form-group">
        <input type="text" id="new-username" name="username" value="{{ old('username') }}" required class="rounded-md">
        <label for="new-username">New Username</label>
        @error('username')
            <small class="field-error">{{ $message }}</small>
        @enderror
        </div>

        <div class="form-group">
        <input type="email" id="new-email" name="email" value="{{ old('email') }}" required class="rounded-md">
        <label for="new-email">New Email</label>
        @error('email')
            <small class="field-error">{{ $message }}</small>
        @enderror
        </div>

        <div class="form-group">
        <input type="password" id="new-password" name="password" required class="rounded-l-md">
        <label for="new-password">New Password</label>
        <button type="button" class="password-toggle" aria-label="Toggle Password Visibility">
            <i class="fa-regular fa-eye"></i>
        </button>
        @error('password')
            <small class="field-error">{{ $message }}</small>
        @enderror
         </div>

        <div class="form-group">
        <input type="password" id="new-password-confirm" name="password_confirmation" required class="rounded-l-md">
        <label for="new-password-confirm">Confirm New Password</label>
         <button type="button" class="password-toggle" aria-label="Toggle Password Visibility">
            <i class="fa-regular fa-eye"></i>
        </button>
        @error('password_confirmation')
            <small class="field-error">{{ $message }}</small>
        @enderror
         </div>

         <div class="terms">
         <input type="checkbox" id="terms" name="terms" required>
         <label for="terms">I agree to the <a href="/terms">Terms and Conditions</a></label>
         @error('terms')
             <small class="field-error">{{ $message }}</small>
         @enderror
         </div>

        <button type="submit">Register</button>
        </fieldset>
          </form>
  </div>

     <div class="log-in
           w-full md:w-[50%] lg:w-[50%]
        h-[31%] md:h-full lg:h-full
       py-8 bottom-0 rounded-tr-[0%] lg:rounded-none lg:rounded-l-[2.4%]" id="log-in">
        <h1 class="text-2xl font-bold mb-4">Welcome to ViVo</h1>
        <div class="text">
            <p>Upgrade your setup with the latest tech, smart gadgets, and premium 
                deals crafted for everyday innovation.</p>
        </div>
          <button class="login-button">Sign In</button>
    </div>

       <div class="register bottom-0 w-full h-[30%] rounded-tl-[0%] md:w-[50%]
        lg:w-[50%] md:h-full lg:h-full py-8  lg:rounded-none lg:rounded-r-[2.4%]" id="register">
        <h1 class="text-2xl font-bold mb-4">Welcome Back</h1>
        <span class="log-icon"><i></i></span>
        <div class="text">
            <p>Welcome back to your tech hub—discover innovative essentials, top brands,
                 and unbeatable offers for your next upgrade.</p>
        </div>
            <button type="button" class="register-button">Register</button>
  </div>
</section>

</body>
</html>