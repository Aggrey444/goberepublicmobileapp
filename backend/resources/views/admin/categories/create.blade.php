@extends('admin.layouts.app')

@section('title', 'Add Category')
@section('content')
    <div class="card" style="max-width:600px;">
        <h3 style="margin-bottom:16px;">Add Category</h3>
        @if ($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ old('name') }}" required></div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="4">{{ old('description') }}</textarea></div>
            <div class="form-group"><label>Status</label>
                <select name="status"><option value="active" @if(old('status','active')==='active')selected @endif>Active</option><option value="inactive" @if(old('status')==='inactive')selected @endif>Inactive</option></select>
            </div>
            <div class="form-group"><label>Image</label><input type="file" name="image" accept="image/*"></div>
            <button type="submit" class="btn btn-primary">Save Category</button>
        </form>
    </div>
@endsection
