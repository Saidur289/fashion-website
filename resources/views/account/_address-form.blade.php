@php $v = fn ($k) => old($k, $a?->$k); @endphp
<form class="addr-form" method="post" action="{{ $action }}" novalidate>
    @csrf
    @if ($method !== 'POST') @method($method) @endif
    <div class="frow two">
        <label class="fld"><span>Label <small>(e.g. Home, Office)</small></span><input name="label" value="{{ $v('label') }}" maxlength="40"></label>
        <label class="fld"><span>Full name <i>*</i></span><input name="name" value="{{ $v('name') ?? auth()->user()->name }}" required>@error('name')<em>{{ $message }}</em>@enderror</label>
    </div>
    <div class="frow two">
        <label class="fld"><span>Mobile number <i>*</i></span><input name="phone" value="{{ $v('phone') ?? auth()->user()->phone }}" inputmode="tel" required>@error('phone')<em>{{ $message }}</em>@enderror</label>
        <label class="fld">
            <span>District <i>*</i></span>
            <span class="sel full">
                <select name="district" required>
                    <option value="">Choose…</option>
                    @foreach (config('districts') as $d)<option @selected($v('district') === $d)>{{ $d }}</option>@endforeach
                </select>
                <x-icon name="chevron-down" size="14" />
            </span>
            @error('district')<em>{{ $message }}</em>@enderror
        </label>
    </div>
    <label class="fld"><span>Area / Thana</span><input name="area" value="{{ $v('area') }}"></label>
    <label class="fld"><span>Full address <i>*</i></span><textarea name="address" rows="2" required>{{ $v('address') }}</textarea>@error('address')<em>{{ $message }}</em>@enderror</label>
    <label class="chk inline"><input type="checkbox" name="is_default" value="1" @checked($v('is_default'))><span>Use as my default address</span></label>
    <button class="btn orange sm" type="submit">Save address</button>
</form>
