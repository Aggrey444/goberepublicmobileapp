@extends('admin.layouts.app')

@section('title', 'Edit Category')
@section('content')
    <div class="card" style="max-width:600px;">
        <h3 style="margin-bottom:16px;">Edit Category: {{ $category->name }}</h3>
        @if ($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ old('name', $category->name) }}" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="4">{{ old('description', $category->description) }}</textarea></div>
            <div class="form-group"><label>Status</label>
                <select name="status"><option value="active" @if($category->status==='active')selected @endif>Active</option><option value="inactive" @if($category->status==='inactive')selected @endif>Inactive</option></select>
            </div>
            <div class="form-group">
                <label>Image</label>
                @if($category->image)<div style="margin-bottom:8px;"><img class="img-thumb" style="width:80px;height:80px;" src="{{ asset('storage/'.$category->image) }}" alt=""></div>@endif
                <input type="file" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Update Category</button>
        </form>
    </div>
@endsection
