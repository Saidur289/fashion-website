{{-- Login form used on /login and in the header sign-in popup. $idp makes ids unique. --}}
<form method="post" action="{{ route('login.store') }}" novalidate>
    @csrf
    <input type="hidden" name="redirect" value="{{ $redirect ?? (session('url.intended') ?: '') }}">
    @error('login')<p class="alert bad" role="alert">{{ $message }}</p>@enderror
    <label class="fld">
        <span>Mobile number or email <i>*</i></span>
        <input name="login" value="{{ old('login') }}" required autocomplete="username" placeholder="01XXXXXXXXX" id="login-{{ $idp }}">
    </label>
    <label class="fld">
        <span>Password <i>*</i></span>
        <span class="pwd"><input name="password" type="password" required autocomplete="current-password"><button type="button" class="eye" aria-label="Show password" data-eye><x-icon name="eye" size="18" /></button></span>
    </label>
    <div class="lrow">
        <label class="chk"><input type="checkbox" name="remember" value="1" checked><span>Remember me</span></label>
        <a class="tlink" href="{{ route('password.request') }}">Lost password?</a>
    </div>
    <button class="btn blue wide" type="submit">Log in</button>
</form>
