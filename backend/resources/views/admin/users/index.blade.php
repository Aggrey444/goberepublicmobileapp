@extends('admin.layouts.app')

@section('title', 'Admin Users')
@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:16px;">Admin Users ({{ $users->total() }})</h3>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">Add Admin User</a>
        </div>
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><span class="badge badge-info">{{ $user->role }}</span></td>
                    <td>@if($user->status === 'active')<span class="badge badge-success">Active</span>@else<span class="badge badge-danger">Inactive</span>@endif</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $users->links() }}</div>
    </div>
@endsection
