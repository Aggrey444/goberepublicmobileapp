@extends('admin.layouts.app')

@section('title', 'Add Admin User')
@section('content')
    <div class="card" style="max-width:600px;">
        <h3 style="margin-bottom:16px;">Add Admin User</h3>
        @if ($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ old('name') }}" required></div>
            <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <div class="form-group"><label>Confirm Password</label><input type="password" name="password_confirmation" required></div>
            <div class="form-group"><label>Role</label>
                <select name="role"><option value="STAFF" @if(old('role')==='STAFF')selected @endif>Staff</option><option value="ADMIN" @if(old('role')==='ADMIN')selected @endif>Admin</option><option value="SUPER_ADMIN" @if(old('role')==='SUPER_ADMIN')selected @endif>Super Admin</option></select>
            </div>
            <div class="form-group"><label>Status</label>
                <select name="status"><option value="active" @if(old('status','active')==='active')selected @endif>Active</option><option value="inactive" @if(old('status')==='inactive')selected @endif>Inactive</option></select>
            </div>
            <button type="submit" class="btn btn-primary">Create Admin User</button>
        </form>
    </div>
@endsection
