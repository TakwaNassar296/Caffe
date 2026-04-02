<x-filament-panels::page>
 
<link rel="stylesheet" href="{{ asset('css/styles.css') }}">

    <div class="cards-grid">
        @foreach ($branches as $item)
            <div class="card">
                <img src="{{ $item->image ? asset('storage/' . $item->image) : 'https://via.placeholder.com/400x200' }}" alt="صورة الفرع">
                <div class="container">
                    <h4><b>اسم الفرع: {{ $item->name }}</b></h4>              
                    <p><strong>نطاق العمل:</strong> {{ $item->scope_work }}</p>
                    <p><strong>رقم الهاتف:</strong> {{ $item->phone_number }}</p>
                    <p><strong>عدد الطلبات:</strong> {{ $item->orders_count ?? 0 }}</p>
                </div>
                <a href="{{ url('/admin/branches/' . $item->id) }}" class="btn-account">
                    عرض حسابات الفرع
                </a>
            </div>
        @endforeach
    </div>
</x-filament-panels::page>
