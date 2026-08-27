@extends('admin.layouts.app')

@section('title', 'Categories')
@section('content')
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h3 style="font-size:16px;">All Categories ({{ $categories->total() }})</h3>
            <div style="display:flex;gap:8px;">
                <form method="GET" style="display:flex;gap:8px;">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search categories..." style="padding:8px;border:1px solid #d1d5db;border-radius:6px;">
                    <button class="btn btn-secondary">Search</button>
                </form>
                <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Add Category</a>
            </div>
        </div>
        @if($categories->isEmpty())
            <p class="muted">No categories found.</p>
        @else
        <table>
            <thead><tr><th>Image</th><th>Name</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td>@if($category->image)<img class="img-thumb" src="{{ asset('storage/'.$category->image) }}" alt="">@else<span class="muted">—</span>@endif</td>
                    <td>{{ $category->name }}</td>
                    <td>{{ $category->products_count }}</td>
                    <td>@if($category->status === 'active')<span class="badge badge-success">Active</span>@else<span class="badge badge-danger">Inactive</span>@endif</td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary btn-sm">Edit</a>
                            <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Delete this category?');">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
