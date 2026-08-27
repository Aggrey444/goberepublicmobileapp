@extends('admin.layouts.app')

@section('title', 'Edit Product')
@section('content')
    <div class="card" style="max-width:600px;">
        <h3 style="margin-bottom:16px;">Edit Product: {{ $product->name }}</h3>
        @if ($errors->any())
            <div class="alert alert-error" style="margin-bottom:16px;">
                @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
            </div>
        @endif
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf @method('PATCH')
            <div class="form-group"><label>Name</label><input type="text" name="name" value="{{ old('name', $product->name) }}" required></div>
            <div class="form-group"><label>Category</label>
                <select name="category_id">
                    <option value="">No category</option>
                    @foreach($categories as $c)<option value="{{ $c->id }}" @if(old('category_id',$product->category_id)==$c->id)selected @endif>{{ $c->name }}</option>@endforeach
                </select>
            </div>
            <div class="form-group"><label>Description</label><textarea name="description" rows="4">{{ old('description', $product->description) }}</textarea></div>
            <div class="form-group"><label>Price (&#8358;)</label><input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required></div>
            <div class="form-group"><label>Status</label>
                <select name="status"><option value="active" @if($product->status==='active')selected @endif>Active</option><option value="inactive" @if($product->status==='inactive')selected @endif>Inactive</option></select>
            </div>
            <div class="form-group">
                <label>Image</label>
                @if($product->image)<div style="margin-bottom:8px;"><img class="img-thumb" style="width:80px;height:80px;" src="{{ asset('storage/'.$product->image) }}" alt=""></div>@endif
                <input type="file" name="image" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary">Update Product</button>
        </form>
    </div>
@endsection
